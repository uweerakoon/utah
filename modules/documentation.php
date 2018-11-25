<?php

namespace Manager;

class BurnDocumentation
{

    private $db_table_id = 12;
    private $min_user_level = 2;

    private $day_iso_color = "#FFF500";
    private $night_iso_color = "#0040FF";

    private $del_status_id = array(1,3);
    private $edit_status_id = array(1,2,3);

    protected $draft_id = 1;
    protected $under_review_id = 2;
    protected $revision_requested_id = 3;
    protected $approved_id = 4;
    protected $disapproved_id = 5;

    // Status info array
    protected $status = array(
        'draft'=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'101','class'=>'warning'),
        'under_review'=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'102','zindex'=>'102','class'=>'warning'),
        'revision_requested'=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'103','class'=>'danger'),
        'approved'=>array('id'=>4,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'105','class'=>'success'),
        'disapproved'=>array('id'=>5,'title'=>'Disapproved','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'106','class'=>'danger')
    );

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->burn_form_id = 'documentation_form';
    }

    public function show()
    {
        /**
         * Shows the table list of all Burn requests associated with the burn_project.
         */

        $args = array('burn_project_id'=>null,'type'=>null,'user_id'=>$_SESSION['user']['id'],'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency','admin'));
        if ($permissions['deny']) {
            echo $permissions['message'];
            //exit;
        }

        $user = new \Info\User($this->db);
        $agency = $user->getUserAgency($_SESSION['user']['id'], 'sql');
        $districts = $user->getUserDistricts($_SESSION['user']['id'], 'sql');

        if (isset($burn_project_id)) {
            $cond = "WHERE d.burn_project_id = $burn_project_id";
        }

        if ($permissions['write']['admin']) {
            // No agency requirement for admin.
            $pre_cond = "";
        } else {
            $pre_cond = "WHERE d.agency_id ".$agency;
        }

        if ($type == 'edit') {
            if ($permissions['write']['admin']) {
                $cond = "";
            } elseif ($permissions['write']['user_agency']) {
                $cond = "AND d.agency_id ".$agency;
            } elseif ($permissions['write']['user_district']) {
                $cond = "AND d.district_id ".$districts;
            } elseif ($permissions['write']['user']) {
                $cond = "AND d.added_by = $user_id
                    AND d.district_id ".$districts;
            }
        } elseif ($type == 'view') {
            if ($permissions['read']['user_agency']) {
                $cond = "AND d.agency_id ".$agency;
            } elseif ($permissions['read']['user'] || $permissions['read']['user_district']) {
                $cond = "AND d.added_by != $user_id
                    AND d.district_id ".$districts;
            }
        }

        $new_function = "BurnDocumentation.newForm($burn_project_id)";

        $sql = "SELECT d.documentation_id, d.observation_date as \"Observation Date\",
        a.agency as \"Agency\", CONCAT('<a href=\"project.php?detail=true&id=', d.burn_project_id ,'\">', bp.project_name, '</a>') as \"Burn Project Name\",
        d.observer as \"Observer\", CONCAT('<span class=\"', s.class ,'\" onclick=\"BurnDocumentation.submitForm(', d.documentation_id, ')\">', s.name ,'</span>') as \"Form Status\",
        COALESCE(CONCAT('<span class=\"', c.class ,'\" data-toggle=\"tooltip\" title=\"Click to Check\" onclick=\"BurnDocumentation.validate(', d.documentation_id, ')\">', c.name ,'</span>'),'<span class=\"label label-default\">N/A</span>') as \"Form Completeness\",
        CONCAT('<span class=\"label label-default\">', u.full_name, '</span>') as \"Added By\"
        FROM documentation d
        JOIN burn_projects bp ON(d.burn_project_id = bp.burn_project_id)
        JOIN agencies a ON (d.agency_id = a.agency_id)
        LEFT JOIN documentation_statuses s ON (d.status_id = s.status_id)
        LEFT JOIN documentation_completeness c ON(d.completeness_id = c.completeness_id)
        JOIN users u ON (d.added_by = u.user_id)
        $cond
        ORDER BY d.added_on";

        if ($type == 'edit' && $permissions['write']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'burns','pkey'=>'documentation_id'
                ,'include_delete'=>true,'delete_function'=>'BurnDocumentation.deleteConfirmation'
                ,'include_view'=>true,'view_href'=>'?detail=true&id=@@'
                ,'edit_function'=>'BurnDocumentation.editConfirmation','new_function'=>$new_function
                ,'no_results_message'=>'There are no editable burn documentation forms associated with your user.'
                ,'no_results_class'=>'info'));
        } elseif ($type == 'view' && $permissions['read']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'burns','pkey'=>'documentation_id','include_edit'=>false
                ,'include_view'=>true,'view_href'=>'?detail=true&id=@@'
                ,'include_delete'=>false,'no_results_message'=>'There are no viewable burn documentation forms associated with your district(s).'
                ,'no_results_class'=>'info'));
        }

        $html = $table['html'];

        return $html;
    }

    public function form($page, $documentation_id = null, $accomplishment_id = null)
    {
        /**
         *  Constructs the Burn form.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','interface');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        // If a Burn is specified. Use its values (Edit/Update scenario).
        // This will extract a $burn_project_id as well (running on the next if)!
        // Extract some default values from the burn plan.
        if (isset($accomplishment_id)) {
            $temp_accomplishment = new \Manager\Accomplishment($this->db);
            $accomplishment = $temp_accomplishment->get($accomplishment_id);

            $burn_project_id = $accomplishment['burn_project_id'];
            $pre_burn_id = $accomplishment['pre_burn_id'];
            $burn_id = $accomplishment['burn_id'];
            $location = $accomplishment['location'];
            $district_id = $accomplishment['district_id'];
        }

        if (isset($documentation_id)) {
            $documentation = $this->get($documentation_id);
            extract($documentation);
        }

        if ($status_id > $this->approved_id) {
            return status_message("The burn documentation has a status that prevents it from being edited", "info");
        }

        if ($page == 1) {
            $title = "Form 9: Burn Documentation <small>1/2</small>";

            $fieldset_id = $this->burn_form_id . "_fs1";

            $ctls = array(
                'burn_project_id'=>array('type'=>'hidden2','value'=>$burn_project_id),
                'pre_burn_id'=>array('type'=>'hidden2','value'=>$pre_burn_id),
                'burn_id'=>array('type'=>'hidden2','value'=>$burn_id),
                'accomplishment_id'=>array('type'=>'hidden2','value'=>$accomplishment_id),
                'district_id'=>array('type'=>'hidden2','value'=>$district_id),
                'location'=>array('type'=>'marker','label'=>'Location (Defaults to Accomplishment)','value'=>$location,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'observation_date'=>array('type'=>'date','label'=>'Observation Date (Defaults to Accomplishment)','value'=>$observation_date,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'observer'=>array('type'=>'text','label'=>'Observer Name','value'=>$observer,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        } elseif ($page == 2) {
            $title = "Form 9: Burn Documentation <small>2/2</small>";

            $fieldset_id = $this->burn_form_id . "_fs2";

            $ctls = array(
                'start_time'=>array('type'=>'text','label'=>'Start Time (24-hr)','value'=>$start_time,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'end_time'=>array('type'=>'text','label'=>'End Time (24-hr)','value'=>$end_time,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'clearing_index_pred'=>array('type'=>'text','label'=>'Predicted Clearing Index','value'=>$clearing_index_pred,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'clearing_index_act'=>array('type'=>'text','label'=>'Actual Clearing Index','value'=>$clearing_index_act,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'observations'=>array('type'=>'related','label'=>'Observations','title'=>'Observation','onclick'=>"BurnDocumentation.observationForm()",'value_sql'=>'SELECT * FROM observations WHERE documentation_id = ?;','value_executors'=>array($documentation_id),'display_js'=>'BurnDocumentation.addObservation','enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        }

        if ($page == 1) {
            $html .= mkForm(array('id'=>$this->burn_form_id,'controls'=>$ctls,'title'=>$title,'suppress_submit'=>true,'fieldset_id'=>$fieldset_id));
        } else {
            $html .= mkFieldset(array('controls'=>$ctls,'title'=>$title,'id'=>$fieldset_id,'append'=>$append));
        }

        return $html;
    }

    public function observationForm($origin)
    {
        /**
         *  Observation sub-form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        //$title = "Burn documentation <small>1/3</small>";

        global $map_center;

        $targetId = 'observations_pad';

        $ctls = array(
            'photo'=>array('type'=>'text','label'=>'Observation Photo Filename','value'=>$photo),
            'time'=>array('type'=>'text','label'=>'Time Taken (24-hr)','value'=>$time),
            'column_height'=>array('type'=>'text','label'=>'Column Height (Ft)','value'=>$column_height),
            'directional_flow_id'=>array('type'=>'combobox','label'=>'Directional Flow','value'=>$directional_flow_id,'table'=>'directional_flows','pcol'=>'directional_flow_id','display'=>'name','order'=>'directional_flow_id'),
            'comments'=>array('type'=>'memo','label'=>'Comments','value'=>$comments)
        );

        $html .= mkForm(array('theme'=>'modal','id'=>'form-observation','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "
                <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"BurnDocumentation.addObservation('{$targetId}')\">Add Observation</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                    </div>
                </div>";

        return $html;
    }

    public function burnProjectSelector($user_id)
    {
        /**
         *  Construct a button list of valid burn plans for Burn submittal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency','admin'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $user_id = $_SESSION['user']['id'];
        $user = new \Info\User($this->db);
        $districts = $user->getUserDistricts($user_id, 'sql');

        if ($permissions['admin']) {
            $cond = "WHERE";
        } elseif ($permissions['user_agency']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)
                AND";
        } elseif ($permissions['user_district']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)
                AND b.district_id ".$districts."
                AND";
        } elseif ($permissions['user']) {
            $cond = "WHERE b.added_by = $user_id
                AND b.district_id ".$districts."
                AND";
        }

        // Select burn_projects from those districts with approved status.
        $select = fetch_assoc(
            "SELECT b.burn_project_id, b.project_name, b.project_number
            FROM burn_projects b
            JOIN accomplishments a ON(a.burn_project_id = b.burn_project_id)
            $cond b.status_id = 4
            GROUP BY b.burn_project_id;");

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
                This agency has no approved burn projects. Burns Requests can only be drafted for approved burn projects.
                </div>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";

            foreach ($select as $value) {
                $small_font = "";
                if (strlen($value['project_name']) >= 24) {
                    $small_font = "style=\"font-size: 10px;\"";
                }
                $html .= "<button class=\"btn btn-default btn-block\" $small_font onclick=\"BurnDocumentation.newForm(".$value['burn_project_id'].")\">".$value['project_name']." - ".$value['project_number']."</button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function accomplishmentSelector($user_id, $burn_project_id)
    {
        /**
         *  Construct a button list of valid burn plans for Burn submittal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $pb_status_id = $this->draft_id;

        // Select burn_projects and accomplishments from the user agency with approved status.
        $select = fetch_assoc(
            "SELECT a.accomplishment_id, bp.burn_project_id, bp.project_name,
            bp.project_number, DATE(a.start_datetime) as start_datetime,
            DATE(a.end_datetime) as end_datetime
            FROM accomplishments a
            JOIN burn_projects bp ON(a.burn_project_id = bp.burn_project_id)
            WHERE bp.burn_project_id = ?
            AND a.added_by = ?
            AND a.status_id >= ?
            GROUP BY a.accomplishment_id;",
            array($burn_project_id, $user_id, $pb_status_id)
        );

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
                This agency has no open or completed accomplishment forms for this project.
                </div>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";

            foreach ($select as $value) {
                $small_font = "";
                if (strlen($value['project_name']) >= 24) {
                    $small_font = "style=\"font-size: 10px;\"";
                }
                $html .= "<button class=\"btn btn-default btn-block\"
                  $small_font
                  onclick=\"BurnDocumentation.newForm({$value['burn_project_id']}, {$value['accomplishment_id']})\">
                    {$value['project_number']} -
                    <strong>{$value['start_datetime']} to {$value['end_datetime']}</strong></button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function ownerChangeForm($documentation_id)
    {
        /**
         *  Change Ownership Form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT COALESCE(submitted_by, updated_by, added_by) as user_id, agency_id FROM documentation WHERE documentation_id = ?", $documentation_id);
        $user_sql = "SELECT user_id, email, full_name FROM users;";
        $district_sql = "SELECT district_id, CONCAT(identifier, ' - ', district) as name FROM districts;";

        $ctls = array(
            'user_id'=>array('type'=>'combobox','label'=>'New Documentation Owner','fcol'=>'user_id','display'=>'email','sql'=>$user_sql,'value'=>$burn['user_id']),
            'district_id'=>array('type'=>'combobox','label'=>'New Designation','fcol'=>'district_id','display'=>'name','sql'=>$district_sql,'value'=>$burn['district_id'])
        );

        $html = mkForm(array('theme'=>'modal','id'=>'owner-change-form','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"BurnDocumentation.ownerChange({$documentation_id})\">Change Owner</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>
                </div>";

        return $html;
    }

    public function ownerChange($documentation_id, $user_id, $district_id)
    {
        /**
         *  Change The Burn Owner
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $status_id = $this->getStatus($documentation_id);
        $agency_id = fetch_one("SELECT agency_id FROM users WHERE user_id = ?", $user_id);

        if ($status_id['status_id'] >= $this->approved_id) {
            $change = $this->pdo->prepare("UPDATE documentation SET added_by = ?, updated_by = ?, submitted_by = ?, agency_id = ?, district_id = ? WHERE documentation_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $user_id, $agency_id, $district_id, $documentation_id));
        } else {
            $change = $this->pdo->prepare("UPDATE documentation SET added_by = ?, updated_by = ?, agency_id = ?, district_id = ? WHERE documentation_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $agency_id, $district_id, $documentation_id));
        }

        if ($change->rowCount() > 0) {
            $html = status_message("The burn documentation owner has successfully been changed.", "success");
        } else {
            $html = status_message("The burn documentation owner change was not successful.", "error");
        }

        return null;
    }

    public function submittalForm($documentation_id)
    {
        /**
         *  Creates the html block to change a burn plans status.
         *  E.g.: Draft, Submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $documentation = fetch_row("SELECT status_id FROM documentation WHERE documentation_id = ?;", $documentation_id);

        $validate = $this->validateRequired($documentation_id);
        $valid = $validate['valid'];

        if ($documentation['status_id'] >= $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Burn documentation was already processed.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($documentation['status_id'] == $this->pre_approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Burn documentation has been pre-approved. Please check back for final approval or disapproval.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($documentation['status_id'] == $this->revision_requested_id) {
            if ($valid) {
                if ($this->reviewCheck($documentation_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The Burn documentation is valid and has been revised since the last request for revision. To ensure minimal processing time, please make sure the revision addresses all review comments before re-submitting to Utah.gov.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                        <button class=\"btn btn-success btn-block\" onclick=\"BurnDocumentation.submitToUtah($documentation_id)\">Re-submit <strong>$burn_name</strong> to Utah.gov</button>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Burn documentation has not been revised since the last request for revision. Please revise the burn according to latest review comment.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            } else {
                if ($this->reviewCheck($documentation_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The Burn documentation has been revised since the last request for revision but is not valid.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Burn documentation has not been revised since the last request for revision and is not valid.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            }
        } elseif ($documentation['status_id'] == $this->under_review_id) {
            $html = "<div>
                    <p class=\"text-center\">The Burn documentation is currently being reviewed by Utah.gov. Please check back for any requested revisions, or the plans approval.</p>
                    <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            if ($valid) {
                $html = "<div>
                    <p class=\"text-center\">The draft is completed and can be submitted to Utah.gov.</p>
                    <button class=\"btn btn-success btn-block\" onclick=\"BurnDocumentation.submitToUtah($documentation_id)\">Submit <strong>$burn_name</strong> to Utah.gov</button>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            } else {
                $html = "<div>
                        <p class=\"text-center\">The Burn documentation is not completed. Please ensure all required fields are filled in.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn documentation Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            }

        }

        return $html;
    }

    public function submitUtah($documentation_id)
    {
        /**
         *  Determine if the burn is valid, and change it to submitted/pending.
         *  Add a valid burn number when submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $valid = $this->validateRequired($documentation_id);
        $valid = $valid['valid'];
        $now = now();
        $submitted_by = $_SESSION['user']['id'];

        // Check if its submitted already
        if ($valid == true) {
            $status = $this->getStatus($documentation_id);
            $status_id = $status['status_id'];
        }

        // Not submitted, and valid. Submit to Utah.gov:
        if ($valid == true && in_array($status_id, array($this->draft_id,$this->revision_requested_id))) {
            // The burn plan is valid. Change its status to "Under Review"
            $last_submitted_by = fetch_one("SELECT submitted_by FROM documentation WHERE documentation_id = ?;", $documentation_id);
            if(!empty($last_submitted_by)) {
                $submitted_by = $last_submitted_by;
            }
            $update_sql = $this->pdo->prepare("UPDATE documentation SET status_id = ?, submitted_on = ?, submitted_by = ? WHERE documentation_id = ?;");
            $update_sql->execute(array($this->under_review_id, $now, $submitted_by, $documentation_id));
            if ($update_sql->rowCount() > 0) {
                $result['message'] = status_message("The Burn documentation has been submitted to Utah.gov.", "success");

                $notify = new \Info\Notify($this->db);
                $notify->documentationSubmitted($documentation_id);
            } else {
                $result['message'] = status_message("The Burn documentation is valid, but failed to submit.", "error");
            }
        } elseif (in_array($status_id, array($this->under_review_id, $this->pre_approved_id, $this->approved_id, $this->disapproved_id))) {
            $result['message'] = status_message("The Burn documentation was already submitted.", "warning");
        } else {
            $result['message'] = status_message("The Burn documentation must be Validated before submitting.", "error");
        }

        return $result;
    }

    public function toolbar($page, $documentation_id)
    {
        /**
         *   Produces the standard burn plan form toolbar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        if (isset($burn_id)) {
            $status_id = fetch_one("SELECT status_id FROM documentation WHERE documentation_id = ?;", $documentation_id);
        }

        if ($status_id >= $this->approved_id) {
            return "";
        }

        $toolbar_class = $this->burn_form_id . "_tb";
        $btn_class = "btn-sm btn-default";

        if (isset($documentation_id)) {
            $c_documentation_id = ", $documentation_id";
            $save_function = "BurnDocumentation.update($documentation_id)";
        } else {
            $save_function = "BurnDocumentation.save()";
        }

        if ($page == 1) {
            $html = "<div class=\"$toolbar_class pull-right btn-group\">
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Back</button>
                <button class=\"btn $btn_class\" onclick=\"BurnDocumentation.showForm(2$c_documentation_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>
            </div>";
        } elseif ($page == 2) {
            $html = "<button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"BurnDocumentation.showForm(1$c_documentation_id)\">Back</button>
                <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        }

        return $html;
    }

    public function save($documentation)
    {
        /**
         *  Save a Burn request.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $added_by = $_SESSION['user']['id'];
        $added_on = now();
        $agency_id = $_SESSION['user']['agency_id'];
        $status_id = 1;

        extract(prepare_values($documentation));

        $insert_sql = $this->pdo->prepare(
            "INSERT INTO documentation (burn_project_id, pre_burn_id, burn_id, accomplishment_id, agency_id, district_id, added_by, added_on, location, observation_date, observer, start_time, end_time, clearing_index_pred, clearing_index_act, status_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_sql = execute_bound($insert_sql, array($burn_project_id, $pre_burn_id, $burn_id, $accomplishment_id, $agency_id, $district_id, $added_by, $added_on, $location, $observation_date, $observer, $start_time, $end_time, $clearing_index_pred, $clearing_index_act, $status_id));

        if ($insert_sql->rowCount() > 0) {
            $success_message .= "The burn documentation form was saved. ";
        } else {
            $result['error'] = true;
            $error_message .= "The burn documentation form could not be saved. ";
        }

        $documentation_id = fetch_one("SELECT documentation_id FROM documentation WHERE accomplishment_id = ? AND added_by = ? AND added_on = ?;", array($accomplishment_id, $added_by, $added_on));

        // Insert observation if specified.
        if (is_array($observations)) {
            foreach ($observations as $value) {
                $value = json_decode($value, true);
                $observation_sql = $this->pdo->prepare("INSERT INTO observations (documentation_id, added_by, added_on, photo, time, column_height, directional_flow_id, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                $observation_sql = execute_bound($observation_sql, array($documentation_id, $added_by, $added_on, $value['photo'], $value['time'], $value['column_height'], $value['directional_flow_id'], $value['comments']));

                if ($observation_sql->rowCount() == 0) {
                    $result['error'] = true;
                    $error_message .= "One or more observations failed to save. ";
                }
            }
        }

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        $this->validateRequired($documentation_id);

        return $result;
    }

    public function update($burn, $documentation_id)
    {
        /**
         *  Update the Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Extract the old values
        $original = $this->get($documentation_id);
        extract($original);

        // Overwrite variables with updated.
        extract(prepare_values($burn));

        // Update the BurnDocumentation.
        $burn_sql = $this->pdo->prepare(
            "UPDATE documentation SET updated_by = ?, updated_on = ?, location = ?, observation_date = ?, observer = ?, start_time = ?, end_time = ?, clearing_index_pred = ?, clearing_index_act = ? WHERE documentation_id = ?;"
        );
        $burn_sql->execute(array($updated_by, $updated_on, $location, $observation_date, $observer, $start_time, $end_time, $clearing_index_pred, $clearing_index_act, $documentation_id));

        // Delete previous liners.
        $many = $this->deleteManyMany($documentation_id);

        // Insert observation if specified.
        if (is_array($observations)) {
            foreach ($observations as $value) {
                $value = json_decode($value, true);
                $observation_sql = $this->pdo->prepare("INSERT INTO observations (documentation_id, added_by, added_on, photo, time, column_height, directional_flow_id, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                $observation_sql = execute_bound($observation_sql, array($documentation_id, $added_by, $added_on, $value['photo'], $value['time'], $value['column_height'], $value['directional_flow_id'], $value['comments']));

                if ($observation_sql->rowCount() == 0) {
                    $result['error'] = true;
                    $error_message .= "One or more observations failed to save. ";
                }
            }
        }

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message("The Burn documentation was updated.", "success");
        }

        $this->validateRequired($documentation_id);

        return $result;
    }

    public function deleteConfirmation($documentation_id)
    {
        /**
         *  Return the delete confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $documentation = fetch_row("SELECT CAST(COALESCE(submitted_on, added_on) AS date) as submitted_on, observation_date, burn_project_id, status_id FROM documentation WHERE documentation_id = ?;", $documentation_id);
        $burn_project = fetch_row("SELECT project_name, project_number FROM burn_projects WHERE burn_project_id = ?;", $documentation['burn_project_id']);

        if ($burn['status_id'] == $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn documentation is approved and cannot be deleted.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->disapproved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn documentation is disapproved. You may delete it now or leave it for archiving purposes.</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"BurnDocumentation.deleteRecord($documentation_id)\">Delete <strong>".$burn_project['burn_number']." - ".$documentation['observation_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete <strong>".$burn_project['project_name']." - ".$documentation['observation_date']."</strong>?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"BurnDocumentation.deleteRecord($documentation_id)\">Delete <strong>".$burn_project['project_number']." - ".$documentation['observation_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function delete($documentation_id)
    {
        /**
         *  Delete the Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Delete the many.
        $many = $this->deleteManyMany($documentation_id);

        $burn_sql = $this->pdo->prepare("DELETE FROM documentation WHERE documentation_id = ?;");
        $burn_sql->execute(array($documentation_id));
        if ($burn_sql->rowCount() > 0 && $many) {
            $result['message'] = status_message("The Burn documentation was deleted.", "success");
        } elseif ($burn_sql->rowCount() > 0 && $liners == false) {
            $result['error'] = true;
            $result['message'] = status_message("The Burn documentation was deleted, but associated liners were not!", "error");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Burn was not deleted.", "error");
        }

        return $result;
    }

    private function deleteManyMany($documentation_id)
    {
        /**
         *  Delete associated many to many (liners).
         *  (Cascade for Update as well).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Observations

        $select_obs = $this->pdo->query("SELECT observation_id FROM observations WHERE documentation_id = $documentation_id;");

        $delete_obs = $this->pdo->prepare("DELETE FROM observations WHERE documentation_id = ?;");
        $delete_obs->execute(array($documentation_id));

        if ($delete_obs->rowCount() > 0 && $select_obs->rowCount() > 0) {
            // All originally selected rows are now deleted.
            return true;
        }

        return false;
    }

    protected function reviewCheck($documentation_id)
    {
        /**
         *  Check if the burn plan was updated since the last review.
         */

        $review_last_updated = fetch_one("SELECT MAX(last_burn_update) FROM documentation_reviews WHERE documentation_id = $documentation_id;");
        $last_updated = fetch_one("SELECT updated_on FROM documentation WHERE documentation_id = $documentation_id;");

        if ($last_updated > $review_last_updated) {
            return true;
        } else {
            return false;
        }
    }

    public function get($documentation_id)
    {
        /**
         *  Get a BurnDocumentation.
         */

        // Get the documentation info.
        $documentation = fetch_row("SELECT * FROM documentation WHERE documentation_id = ?;", $documentation_id);
        $result = $documentation;

        // Get the associated observations.
        $observations = fetch_row("SELECT * FROM observations WHERE documentation_id = ?;", $documentation_id);
        if (!$observations['error']) {
            $result['observations'] = $observations;
        }

        // Get basic burn plan info.
        $burn_project = fetch_row("SELECT burn_project_id, project_name, project_number, location FROM burn_projects WHERE burn_project_id = (SELECT burn_project_id FROM documentation WHERE documentation_id = ?);", $documentation_id);
        if (!$burn_project['error']) {
            $result['burn_project'] = $burn_project;
        }

        return $result;
    }

    public function overviewPage()
    {
        /**
         *  Overview Tables & Map Page For BurnDocumentation.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        if ($permissions['read']['deny']) {
            exit;
        }

        $args = array('burn_project_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        if (isset($burn_project_id)) {
            $edit_table = $this->show(array('type'=>'edit','burn_project_id'=>$burn_project_id));
            $view_table = $this->show(array('type'=>'view','burn_project_id'=>$burn_project_id));
            $map = $this->getAllMap(array('burn_project_id'=>$burn_project_id));
            $app_burn_project_id = ', '.$burn_project_id;
            $return_link = "<a href=\"/manager/documentation.php\">Return to Overview</a>";
        } else {
            $edit_table = $this->show(array('type'=>'edit'));
            $view_table = $this->show(array('type'=>'view'));
            $map = $this->getAllMap();
            $return_link = "";
        }

        $html['header'] = "<div class=\"row\">
            <div class=\"col-sm-12\">
                <span class=\"pull-right\">
                    $return_link
                </span>
                <h3>Overview <small>Form 9: Burn Documentation</small></h3>
            </div>
        </div>";

        $html['edit_table'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    {$edit_table}
                </div>
            </div>";

        $html['view_table'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    {$view_table}
                </div>
            </div>";

        $html['map'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    {$map}
                </div>
            </div>";

        if ($permissions['write']['any']) {
            $html['main'] .= "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <div class=\"pull-right\">
                        <button class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentation.newForm($app_burn_project_id)\">New Burn Documentation Form</button>
                    </div>
                </div>
            </div>";
        }

        return $html;
    }

    public function detailPage($documentation_id)
    {

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkDocumentationPermissions($_SESSION['user']['id'], $documentation_id, $permissions);

        $return_link = "<a href=\"/manager/documentation.php\">Return to Overview</a>";

        if ($burn_permissions['allow']) {
            // Get the BurnDocumentation.
            $burn = $this->get($documentation_id);

            // Construct the title.
            if (isset($burn['burn_project']['project_number'])) {
                $title = $burn['burn_project']['project_number']." <small>Observed - ".$burn['observation_date']." - </small>";
            } else {
                $title = "Form 9: Burn Documentation";
            }

            if (in_array($burn['status_id'], $this->edit_status_id) && $burn_permissions['write']) {
                if ($burn['status_id'] < $this->revision_requested_id) {
                    $submit_text = "Submit";
                } else {
                    $submit_text = "Re-submit";
                }

                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentation.submitForm($documentation_id)\">$submit_text</button>
                    <button class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentation.editConfirmation($documentation_id)\">Edit Burn Documentation</button>
                    <a href=\"/pdf/documentation.php?id={$documentation_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            } else {
                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                    <button class=\"btn btn-sm btn-default\" disabled>Edit Burn Documentation</button>
                    <a href=\"/pdf/documentation.php?id={$documentation_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            }

            // Get HTML blocks.
            $status = $this->getStatusLabel($documentation_id);
            $map = $this->getMap($documentation_id);
            $table = $this->tablifyFields($documentation_id);
            $sidebar = $toolbar;
            $sidebar .= $this->getContacts($documentation_id);
            //$sidebar .= $this->getConditions($documentation_id);
            //$sidebar .= $this->getReviews($documentation_id);
            $sidebar .= $this->getUploads($documentation_id);

            $observations = $this->getObservations($documentation_id);

            // Construct the HTML array.
            $html['header'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <span class=\"pull-right\">
                        $return_link
                        $status
                    </span>
                    <h3>".$title." <small>Burn documentation</small></h3>
                </div>
            </div>";

            $html['main'] = "<div class=\"row\">
                <div class=\"col-sm-8\">
                    <h4>Form 9: Burn documentation Info</h4>
                    <hr>
                    $map
                    <br>
                    $table
                </div>
                <div class=\"col-sm-4\">
                    $sidebar
                    $observations
                </div>
            </div>";
        } else {
            $html['main'] = "<div>
                <h1 class=\"\" style=\"color: #bbb\">You do not have permission to view this page.</h1>
                $return_link
            </div>";
        }

        return $html;
    }

    public function pdfPage($documentation_id)
    {
        /**
         *  PDF Documentation Printout
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkDocumentationPermissions($_SESSION['user']['id'], $documentation_id, $permissions);

        if ($burn_permissions['allow']) {
            // Get the Pre-Burn.
            $documentation = $this->get($documentation_id);

            // Get HTML blocks
            $table = $this->tablifyFields($documentation_id, true);
            $contacts = $this->getContacts($documentation_id);
            //$conditions = $this->getConditions($documentation_id);
            //$reviews = $this->getReviews($documentation_id);
            $observations = $this->getObservations($documentation_id);

            // Static fields.
            $project_name = $documentation['burn_project']['project_name'];
            $project_number = $documentation['burn_project']['project_number'];
            $observer = $documentation['observer'];

            // Build the map.
            $location = str_replace(array('(',')',' '), '', $documentation['location']);
            $color = str_replace('#', '0x', $this->retrieveStatus($documentation['status_id'])['color']);
            $label = substr($this->retrieveStatus($documentation['status_id'])['title'], 0, 1);
            $static_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=14&center=$location&markers=color:$color%7Clabel:$label%7C$location";

            // Get HTML blocks.
            $html = "
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"50%\">
                        <col width=\"49%\">
                        <tr style=\"border: 0.15em solid black;\">
                            <td style=\"width: 50%\">Form 9: Burn Documentation: <strong>{$documentation['observation_date']}</strong></td>
                            <td style=\"width: 50%\">Project: <strong>$project_number</strong> - $project_name</td>
                        </tr>
                        <tr style=\"border: 0.15em solid black; padding: 0.3em;\">
                            <td style=\"width: 71%\">
                                $table
                                <br>
                                $observations
                            </td>
                            <td style=\"width:28%\">
                                <strong>Location:</strong><br>
                                <img width=\"28%\" src=\"$static_map\"/>
                            </td>
                        </tr>
                    </table>
                <pagebreak />
                <div class=\"col-sm-4\">
                    $contacts
                    $conditions
                    $reviews
                </div>
            </div>";
        } else {
            $html = "<div>
                <h1 class=\"\" style=\"color: #bbb\">You do not have permission to view this PDF.</h1>
            </div>";
        }

        return $html;
    }

    public function getStatusLabel($documentation_id)
    {
        $status = fetch_row(
            "SELECT description, class, name FROM documentation_statuses
            WHERE status_id IN (SELECT status_id FROM documentation WHERE documentation_id = $documentation_id);"
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    public function noEditWarning()
    {
        /**
         *  Produce the no edit warning HTML. Only fires when a burn plan cannot be edited.
         */

        $html = "<div>
            <p class=\"text-center\">The Burn is pre-approved, approved, or disapproved and cannot be edited.</p>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    protected function tablifyFields($documentation_id)
    {
        /**
         *
         */

        $documentation = $this->get($documentation_id);

        $title = "Burn Documentation Information";
        $value_array = $documentation;
        $fields_array = array('location','observation_date','observer','start_time','end_time',
            'clearing_index_pred','clearing_index_act','observations');

        $html = "<table class=\"table table-responsive table-condensed\">
            <col width=\"60%\">
            <col width=\"47%\">
            <col width=\"12%\">
            <thead>
            <tr><th>$title</th><th>Value</th><th></th></tr>
            </thead>
            <tbody>";

        foreach ($fields_array as $key) {

            $reference = $this->value_map[$key];
            $value = $value_array[$key];

            if ($reference['multiselect'] == true) {
                $reference['pvalue'] = $burn_id;
                $value = \mm_label($reference);
            } else {
                $value = $value_array[$key];
            }

            if (isset($reference['sql']) && isset($value)) {
                $value = fetch_one($reference['sql'] . $value);
            }

            if ($reference['boolean'] == true) {
                if ($value < 1) {
                    $value = "False";
                } else {
                    $value = "True";
                }
            }

            if (!isset($reference['field_id'])) {
                $reference['field_id'] = fetch_one("SELECT field_id FROM fields WHERE table_id = ? AND `column` = ?", array($this->db_table_id, $key));
            }

            if (isset($reference['field_id'])) {
                $help = getInputPopover(true, $reference['field_id']);
            }

            if ($reference['display']) {
                $html .= "<tr><td>".$reference['title']."</td><td>".$value."</td><td>".$help."</td></tr>";
            }
        }

        $html .= "</tbody>
        </table>";

        return $html;
    }

    public function validateRequired($documentation_id)
    {
        /**
         *  Validates a saved burn for required fields.
         */

        // The missing value count.
        $count = 0;
        $missing_msg = "The following required fields are missing in this Burn documentation:<br><br>";

        // Get the burn.
        $burn = $this->get($documentation_id);

        // Check the base values.
        $base_required = array('observer'=>'Observer','location'=>'Location','clearing_index_pred'=>'Predicted Clearing Index',
            'clearing_index_act'=>'Actual Clearing Index','observation_date'=>'Observation Date',
            'start_time'=>'Start Time','end_time'=>'End Time'
        );

        foreach ($base_required as $key => $value) {
            if (is_null($burn[$key])) {
                $count++;
                $missing_msg .= "No ".$value."<br>";
            }
        }

        // Update the burn plan with its completeness status.
        $update_sql = $this->pdo->prepare("UPDATE documentation SET completeness_id = ? WHERE documentation_id = ?");

        if ($count == 0) {
            // Update to valid. No missing was counted.
            $update_sql->execute(array(2, $documentation_id));
            $result['valid'] = true;
            $result['message'] = modal_message("All required burn documentation info is filled out.", "success").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
            $update_sql->execute(array(1, $documentation_id));
            $result['valid'] = false;
            $result['message'] = modal_message($missing_msg, "error").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        }

        return $result;
    }

    public function getStatus($documentation_id)
    {
        /**
         *  Get the status id of the burn.
         */

        $result = array();
        $result['status_id'] = fetch_one("SELECT status_id FROM documentation WHERE documentation_id = ?;", $documentation_id);

        if (in_array($result['status_id'], $this->del_status_id)) {
            $result['allow_delete'] = true;
        } else {
            $result['allow_delete'] = false;
        }

        if (in_array($result['status_id'], $this->edit_status_id)) {
            $result['allow_edit'] = true;
        } else {
            $result['allow_edit'] = false;
        }

        return $result;
    }

    protected function getMap($documentation_id)
    {
        /**
         *  Builds a boundary & marker map for a single BurnDocumentation.
         */

        $zoom_to_fit = true;
        $control_title = "Zoom to Burn request";

        $documentation = $this->get($documentation_id);
        $marker = $documentation['location'];
        $burn_status = $this->retrieveStatus($documentation['status_id']);

        global $map_center;

        $center_control = "
            function centerControl(controlDiv, map) {
              // Control Div CSS
              controlDiv.style.padding = '5px';

              // Control Border CSS
              var controlUI = document.createElement('div');
              controlUI.style.backgroundColor = 'white';
              controlUI.style.borderStyle = 'solid';
              controlUI.style.borderWidth = '1px';
              controlUI.style.borderColor = '#999999';
              controlUI.style.borderOpacity = '0.7';
              controlUI.style.borderRadius = '2px';
              controlUI.style.cursor = 'pointer';
              controlUI.style.textAlign = 'center';
              controlUI.title = 'Click to return to the boundary';
              controlDiv.appendChild(controlUI);

              // Set CSS for the control interior
              var controlText = document.createElement('div');
              controlText.style.fontFamily = '\"Helvetica Neue\",Helvetica,Arial,sans-serif';
              controlText.style.fontSize = '12px';
              controlText.style.paddingLeft = '6px';
              controlText.style.paddingRight = '6px';
              controlText.innerHTML = '<b>$control_title</b>';
              controlUI.appendChild(controlText);

              google.maps.event.addDomListener(controlUI, 'click', function() {
                  map.setZoom(11);
                  map.panTo(marker.position);
              });

            }";

        if ($zoom_to_fit == true) {
            $zoom = "map.setZoom(11);
                    map.panTo(marker.position);";
        } else {
            $zoom = "";
        }

        if (!empty($boundary)) {
            $center = "zoom: 6,
                center: new google.maps.LatLng($map_center),";
            $latlng = explode(' ', str_replace(array("(", ")", ","), "", $boundary));
            $json_str = "{ne:{lat:".$latlng[2].",lon:".$latlng[3]."},sw:{lat:".$latlng[0].",lon:".$latlng[1]."}}";

            $bounds = "
            // extend it using my two points
            var latlng = $json_str

            var bounds = new google.maps.LatLngBounds(
              new google.maps.LatLng(latlng.sw.lat,latlng.sw.lon),
              new google.maps.LatLng(latlng.ne.lat,latlng.ne.lon)
            );

            var rectangle = new google.maps.Rectangle({
              strokeColor: '#000',
              strokeOpacity: 0.4,
              strokeWeight: 2,
              fillColor: '#000',
              fillOpacity: 0.1,
              bounds: bounds,
            });

            rectangle.setMap(map);";

        } else {
            $center = "zoom: 10,
            center: new google.maps.LatLng($map_center),";
            $bounds = "";
        }

        if (!empty($marker)) {
            $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $marker));
            $marker_json_str = "{lat:".$marker_latlng[0].",lon:".$marker_latlng[1]."}";

            $marker = "
                var marker_latlng = $marker_json_str

                var marker_center = new google.maps.LatLng(marker_latlng.lat, marker_latlng.lon);

                if (checkLegacy()) {
                     var marker = new google.maps.Marker({
                         position: marker_center,
                     });
                 } else {
                    var marker = new google.maps.Marker({
                        position: marker_center,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 6,
                            strokeColor: '#333',
                            strokeOpacity: 1,
                            strokeWeight: 1,
                            fillColor: '".$burn_status['color']."',
                            fillOpacity: 1
                        },
                    });
                }

                marker.setMap(map)
            ";
        } else {
            $center = "zoom: 10,
                center: new google.maps.LatLng(34.4,-111.8),";
            $marker = "
                var marker_center = bounds.getCenter();

                var marker = new google.maps.Marker({
                    position: marker_center,
                    draggable: true,
                });

                marker.setMap(map)
            ";
        }

        if (!empty($day_iso)) {
            $color = $this->day_iso_color;
            $day_iso = "var day_iso = new isosceles(map, marker, {$day_iso['initDeg']}, {$day_iso['finalDeg']}, {$day_iso['amplitude']}, '{$color}')";
        }

        if (!empty($night_iso)) {
            $color = $this->night_iso_color;
            $night_iso = "var day_iso = new isosceles(map, marker, {$night_iso['initDeg']}, {$night_iso['finalDeg']}, {$night_iso['amplitude']}, '{$color}')";
        }

        $html = "
            <style>
                #map$id {
                    $style
                }

                .map-canvas {height:348px;}

                div.stations2 svg {
                    position: absolute;
                }
            </style>
            <div class=\"map-canvas\" id=\"map$id\"></div>
            <script>

                $center_control

                var map = new google.maps.Map(document.getElementById('map$id'), {
                    $center
                    mapTypeId: google.maps.MapTypeId.TERRAIN,
                    panControl: false,
                    zoomControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    scrollwheel: false
                });

                var homeControlDiv = document.createElement('div');
                var homeControl = new centerControl(homeControlDiv, map);

                homeControlDiv.index = 1;
                map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);

                google.maps.event.trigger(map,'resize');

                $bounds

                $marker

                $zoom

                $day_iso

                $night_iso

                var Overlay = new Overlay();
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    private function getAllMap()
    {
        /**
         *  The All Map display.
         */

        $args = array('burn_project_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        $status_icons = $this->status_icons;
        $user_id = $_SESSION['user']['id'];

        global $map_center;

        if (isset($agency_id)) {
            $markers = fetch_assoc("SELECT d.documentation_id, d.status_id, d.location, CONCAT(b.project_number, ': Observed: ', d.observation_date) as name, d.added_by FROM documentation d JOIN burn_projects b ON(d.burn_project_id = b.burn_project_id) WHERE d.burn_project_id IN(SELECT burn_project_id FROM burn_projects WHERE agency_id = $agency_id);");
            $zoom_to_fit = false;
        } elseif (isset($burn_project_id)) {
            $burn = fetch_row("SELECT burn_project_id, status_id, location FROM burn_projects WHERE burn_project_id = $burn_project_id;");
            $markers = fetch_assoc("SELECT documentation_id, status_id, location, added_by FROM documentation WHERE burn_project_id = $burn_project_id;");
            $zoom_to_fit = true;
        }

        if ($zoom_to_fit == true) {
            $zoom = "map.fitBounds(bounds);";
        } else {
            $zoom = "";
        }

        if ($burn['error'] == false && !empty($burn)) {
            $center = "zoom: 6,
                center: new google.maps.LatLng($map_center),";
            $latlng = explode(' ', str_replace(array("(", ")", ","), "", $burn['location']));
            $json_str = "{ne:{lat:".$latlng[2].",lon:".$latlng[3]."},sw:{lat:".$latlng[0].",lon:".$latlng[1]."}}";

            $bounds = "
            // extend it using my two points
            var latlng = $json_str

            var bounds = new google.maps.LatLngBounds(
              new google.maps.LatLng(latlng.sw.lat,latlng.sw.lon),
              new google.maps.LatLng(latlng.ne.lat,latlng.ne.lon)
            );

            var rectangle = new google.maps.Rectangle({
              strokeColor: '#000',
              strokeOpacity: 0.4,
              strokeWeight: 2,
              fillColor: '#000',
              fillOpacity: 0.1,
              bounds: bounds,
            });

            rectangle.setMap(map);";

        } else {
            $center = "zoom: 6,
            center: new google.maps.LatLng({$map_center}),";
            $bounds = "";
        }

        if ($markers['error'] == false) {
            // Construct the Marker array.
            $marker_arr = "var Burns = [\n ";
            $marker_len = count($markers);
            $i = 0;

            foreach ($markers as $value) {
                if (++$i === $marker_len) {
                    $comma = "";
                } else {
                    $comma = ",";
                }
                if ($value['added_by'] == $user_id) {
                    $edit = 'true';
                } else {
                    $edit = 'false';
                }
                $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $value['location']));
                $marker_status = $this->retrieveStatus($value['status_id']);
                $marker_arr .= "[".$value['burn_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$value['name']."', '".$marker_status['color']."', ".$edit.",'".str_replace(" ", "_", strtolower($marker_status['title']))."']$comma\n ";
            }

            $marker_arr .= "];\n";

            // Append it to the function.
            $marker = "
                $marker_arr

                function setMarkers(map, markers) {
                    for (var i = 0; i < markers.length; i++) {
                        var marker = markers[i];
                        var myLatLng = new google.maps.LatLng(marker[1], marker[2]);

                        if (checkLegacy()) {
                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                title: marker[3],
                                id: marker[0]
                            });
                        } else {
                            if (!marker[5]) {
                                var marker = new google.maps.Marker({
                                    position: myLatLng,
                                    map: map,
                                    icon: {
                                        url: '/images/ros_' + marker[6] + '.png',
                                        scale: 0.1
                                    },
                                    title: marker[3],
                                    id: marker[0]
                                });
                            } else {
                                var marker = new google.maps.Marker({
                                    position: myLatLng,
                                    map: map,
                                    icon: {
                                        path: google.maps.SymbolPath.CIRCLE,
                                        scale: 6,
                                        strokeColor: '#333',
                                        strokeOpacity: 1,
                                        strokeWeight: 1,
                                        fillColor: marker[4],
                                        fillOpacity: 1
                                    },
                                    title: marker[3],
                                    id: marker[0]
                                });
                            }
                        }

                        bindMarkers(map, marker);
                    }

                    function bindMarkers(map, marker)
                    {
                        google.maps.event.addListener(marker, 'click', function() {
                            window.location='/manager/documentation.php?burn=true&id='+marker.id;return false;
                        });
                    }
                }


            ";
        }

        $html = "
            <style>


                .map-canvas {height:348px;}

                div.stations2 svg {
                    position: absolute;
                }
            </style>
            <div class=\"map-canvas\" id=\"map$id\"></div>
            <script>

                var map = new google.maps.Map(document.getElementById('map$id'), {
                    $center
                    mapTypeId: google.maps.MapTypeId.TERRAIN,
                    panControl: false,
                    zoomControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    scrollwheel: false
                });

                google.maps.event.trigger(map,'resize');

                $bounds

                $marker

                $zoom

                var Overlay = new Overlay();

                setMarkers(map, Burns);
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    protected function getContacts($documentation_id)
    {
        /**
         *  Constructs the contacts display div for a BurnDocumentation.
         */

        $submitter = fetch_row(
            "SELECT u.full_name, u.email, u.phone, a.agency
            FROM documentation db
            JOIN users u ON(db.submitted_by = u.user_id)
            JOIN agencies a ON(u.agency_id = a.agency_id)
            WHERE db.documentation_id = ?
            "
        , $documentation_id);

        $contact = fetch_row("SELECT observer FROM documentation WHERE documentation_id = ?", $documentation_id);

        // Start the contact block div.
        $html = "<div style=\"margin: 0px 0px 15px 0px\" class=\"\">
                    <h4>Contacts</h4>
                    <hr>";

        // Add submitted by.
        $html .= "  <div class=\"contact-block\">
                        <div>
                            <p>Submitted by</p>
                            <h5>".$submitter['full_name']." <b class=\"caret\"></b></h5>
                            <a href=\"mailto:".$submitter['email']."\">".$submitter['email']."</a>
                        </div>
                        <div class=\"contact-right\">
                            <p class=\"agency\">".$submitter['agency']."</p>
                            <a href=\"tel:".$submitter['phone']."\">".$submitter['phone']."</a>
                        </div>
                    </div>";

        // Add the burn plan contact.
        $html .= "  <div class=\"contact-block\">
                        <div>
                            <p>Observer</p>
                            <h5>".$contact['observer']."</h5>
                        </div>
                    </div>";

        $html .= "</div>";

        return $html;
    }

    protected function getObservations($documentation_id)
    {
        /**
         *  Retrieves and builds HTML block for burn observations.
         */

        $observations = fetch_assoc(
            "SELECT a.full_name as added_by, o.added_on, u.full_name as updated_by, o.updated_on,
            s.full_name as submitted_by, o.submitted_on, o.photo, o.time, o.column_height, d.name as directional_flow,
            o.comments
            FROM observations o
            LEFT JOIN directional_flows d ON(o.directional_flow_id = d.directional_flow_id)
            LEFT JOIN users a ON(o.added_by = a.user_id)
            LEFT JOIN users u ON(o.updated_by = u.user_id)
            LEFT JOIN users s ON(o.submitted_by = s.user_id)
            WHERE o.documentation_id = ?
            ORDER BY o.time;", $documentation_id
        );


        $html = "
            <col width=\"12%\">
            ";

        foreach ($fields_array as $key) {
            if ($reference['display']) {
                $html .= "<tr><td>".$reference['title']."</td><td>".$value."</td><td>".$help."</td></tr>";
            }
        }

        $html .= "</tbody>
        </table>";

        $html = "<div class=\"\" style=\"padding: 15px 0px;\">
                    <h4>Observations</h4>
                    <hr>
                    <table class=\"table table-responsive table-condensed\" style=\"font-size:12px\">
                    <col width=\"15%\">
                    <col width=\"85%\">
                    <thead>
                    <tr><th>Time</th><th>Observation</th><th></th></tr>
                    </thead>
                    <tbody>";

        $count = 1;

        if (!$observations['error']) {
            foreach ($observations as $value) {
                $html .= "<tr>
                            <td>
                                <strong>{$value['time']}<strong>
                            </td>
                            <td>
                                <strong>Column Height:</strong> {$value['column_height']} Ft.<br>
                                <strong>Directional Flow:</strong> {$value['directional_flow']}<br>
                                <strong>Photo Name:</strong> {$value['photo']}<br>
                                <strong>Comment:</strong> {$value['comments']}</p>
                            </td>
                        </tr>";

                $count++;
            }
        } else {
            $html .= "<tr><td>
                        </td>
                        <td>
                            <strong>No Observations<strong>
                        </td>
                    </tr>";
        }

        $html .= "</tbody>
                </table>
            </div>";

        return $html;
    }

    protected function getReviews($burn_id)
    {
        /**
         *  Constructs the reviews display div & table for a given burn plan.
         */

        $admin = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system_admin'), 'write');

        if ($full = true) {
            $com_cond = "r.comment, '</a>'";
        } else {
            $com_cond = "LEFT(r.comment, 47), '...</a>'";
        }

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    <h4>Reviews</h4>
                    <hr>";

        if ($admin['any']) {
            $pre_sql = "r.burn_review_id, ";
        }

        $sql = "SELECT $pre_sql COALESCE(CONCAT(u.full_name, '<br><small><span class=\"label label-default\">Edited By</span></small>'), a.full_name) as \"Reviewer\", CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnProject.reviewDetail(', r.burn_review_id ,')\">', $com_cond) as \"Excerpt\", CONCAT('<small>', COALESCE(r.updated_on, r.added_on), '</small>') as \"Edited\"
        FROM burn_reviews r
        JOIN users a ON (r.added_by = a.user_id)
        LEFT JOIN users u ON (r.updated_by = u.user_id)
        WHERE burn_id = $burn_id";

        if ($admin['any']) {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn documentation.',
                'no_results_class'=>'info','pkey'=>'burn_review_id','table'=>'burn_reviews','include_edit'=>true,'include_delete'=>false,
                'edit_function'=>'BurnReview.editReviewForm'));
        } else {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn documentation.',
                'no_results_class'=>'info'));
        }

        //$table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn request.',
        //    'no_results_class'=>'info'));

        $html .= $table['html'];

        $html .= "</div>";

        return $html;
    }

    public function reviewDetail($review_id)
    {
        /**
         *
         */

        $review = fetch_row(
            "SELECT r.burn_id, u.email, u.full_name, DATE_FORMAT(r.added_on, '%Y-%m-%e %l:%i %p') as added_on, r.comment
            FROM burn_reviews r
            JOIN users u ON(r.added_by = u.user_id)
            WHERE r.burn_review_id = $review_id;"
        );

        $burn = fetch_row(
            "SELECT d.ignition_date, b.burn_name, b.burn_number
            FROM burns d
            JOIN burn_projects b ON(d.burn_project_id = b.burn_project_id)
            WHERE burn_id = ".$review['burn_id'].";"
        );

        $html = "<div>
            <p><strong>".$review['full_name'].": </strong>".$review['comment']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$review['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$review['email']."?subject=Burn Review - ".$burn['ignition_date']." - ".$burn['burn_number']." ".$burn['burn_name']."\" role=\"button\">Email Reviewer</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    protected function getConditions($burn_id)
    {
        /**
         *  Constructs the reviews display div & table for a given burn plan.
         */

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    <h4>Notes and Conditions</h4>
                    <hr>";

        $sql = "SELECT burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnDocumentation.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
        FROM burn_conditions c
        JOIN users u ON (c.added_by = u.user_id)
        WHERE burn_id = $burn_id";

        if ($_SESSION['user']['level_id'] > 2) {
            $sql = "SELECT burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnDocumentation.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
                FROM burn_conditions c
                JOIN users u ON (c.added_by = u.user_id)
                WHERE burn_id = $burn_id";

            $table = show(array('sql'=>$sql,'include_edit'=>true,'edit_function'=>'BurnReview.conditionEdit',
                'table'=>'burn_conditions','pkey'=>'burn_condition_id','include_delete'=>false,
                'no_results_message'=>'There are currently no conditions associated with this Burn request.',
                'no_results_class'=>'info'));
        } else {
            $sql = "SELECT CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnDocumentation.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
                FROM burn_conditions c
                JOIN users u ON (c.added_by = u.user_id)
                WHERE burn_id = $burn_id";

            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no conditions associated with this Burn request.',
                'no_results_class'=>'info'));
        }

        //$table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no notes/conditions associated with this Burn request.',
        //    'no_results_class'=>'info'));

        $html .= $table['html'];

        $html .= "</div>";

        return $html;
    }

    public function conditionDetail($condition_id)
    {
        /**
         *
         */

        $condition = fetch_row(
            "SELECT c.burn_id, u.email, u.full_name, DATE_FORMAT(c.added_on, '%Y-%m-%e %l:%i %p') as added_on, c.comment, c.acres
            FROM burn_conditions c
            JOIN users u ON(c.added_by = u.user_id)
            WHERE c.burn_condition_id = $condition_id;"
        );

        $burn = fetch_row(
            "SELECT d.ignition_date, b.project_name, b.project_number, d.acres_treated
            FROM burns d
            JOIN burn_projects b ON(d.burn_project_id = b.burn_project_id)
            WHERE burn_id = ".$condition['burn_id'].";"
        );

        $html = "<div>
            <p><strong>".$condition['full_name'].": </strong>".$condition['comment']."</p>
            <p><strong>Approved Acres: </strong><h4><span class=\"label label-danger\">".$condition['acres']." / ".$burn['acres_treated']."</span></h4></p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$condition['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$condition['email']."?subject=Burn Review - ".$burn['ignition_date']." - ".$burn['project_number']." ".$burn['project_name']."\" role=\"button\">Email Approver</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    protected function getUploads($documentation_id)
    {
        /**
         *  Constructs the uploads HTML block.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));

        $uploads = fetch_assoc(
            "SELECT f.*
            FROM documentation_files b
            JOIN files f ON (b.file_id = f.file_id)
            WHERE b.documentation_id = $documentation_id
            ORDER BY added_on;"
        );

        if ($permissions['write']['any']) {
            $toolbar = "<div class=\"btn-group pull-right\">
                    <button onclick=\"Uploader.form('documentation',$documentation_id)\" class=\"btn btn-sm btn-default\">Upload</button>
                </div>";
        }

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    $toolbar
                    <h4>Uploads</h4>
                    <hr>";

        if ($uploads['error'] == false) {
            foreach ($uploads as $value) {
                $file_name = end(explode('/', $value['path']));
                if ($value['size_kb'] > 1000) {
                    $file_size = round($value['size_kb']/1000, 2)." MB";
                } else {
                    $file_size = round($value['size_kb'], 2)." kB";
                }

                $html .= "<div class=\"file-block\">
                    <a href=\"/ajax/download.php?id={$value['file_id']}\">
                        <span class=\"glyphicon glyphicon-file file-icon\"></span>
                        <span class=\"file-name\">{$file_name} - {$file_size}</span>
                    </a>
                    <span class=\"pull-right\">
                        <span data-toggle=\"tooltip\" style=\"cursor: pointer\" title=\"File Information\" onclick=\"File.info({$value['file_id']})\" class=\"glyphicon glyphicon-info-sign\"></span>
                        <span data-toggle=\"tooltip\" style=\"cursor: pointer\" title=\"Delete File\" onclick=\"File.deleteConfirmation({$value['file_id']})\" class=\"glyphicon glyphicon-remove-circle\"></span>
                    </span></div>";
            }
        } else {
            $html .= status_message("There are currently no uploads associated with this burn request.", "info");
        }

        $html .= "</div>";

        return $html;
    }

    private function checkDocumentationPermissions($user_id, $documentation_id, $permissions)
    {
        /**
         *  Return what the user can do with this burn project. (read, write)
         */

        $read = false;
        $write = false;

        $burn = fetch_row("SELECT added_by, district_id, agency_id FROM documentation WHERE documentation_id = ?", $documentation_id);
        $user = new \Info\User($this->db);
        $agency_id = $user->getUserAgency($user_id);
        $districts = $user->getUserDistrictsIds($user_id);

        // Read Conditions
        if ($permissions['read']['user'] && $burn['added_by'] == $user_id) {
            $read = true;
        }
        if ($permissions['read']['user_agency'] && $burn['agency_id'] == $agency_id) {
            $read = true;
        }
        if ($permissions['read']['user_district'] && in_array($burn['district_id'], $districts)) {
            $read = true;
        }

        // Write Conditions
        if ($permissions['write']['user'] && $burn['added_by'] == $user_id) {
            $write = true;
        }
        if ($permissions['write']['user_agency'] && $burn['agency_id'] = $agency_id) {
            $write = true;
        }
        if ($permissions['write']['user_district'] && in_array($burn['district_id'], $districts)) {
            $write = true;
        }

        $allow = (!$write && !$read) ? false : true;

        $burn_permissions = array('read'=>$read, 'write'=>$write, 'allow'=>$allow);

        return $burn_permissions;
    }

    protected function retrieveStatus($status_id) {
        /**
         *  Search the status array by status_id.
         */

        foreach ($this->status as $key => $value) {
            if ($value['id'] == $status_id) {
                return $this->status[$key];
            }
        }

        return $false;
    }

    private $value_map = array(
        'burn_project_id'=>array('display'=>false,'title'=>'Burn Project Id'),
        'pre_burn_id'=>array('display'=>false,'title'=>'Pre-Burn Id'),
        'burn_id'=>array('display'=>false,'title'=>'Burn Id'),
        'accomplishment_id'=>array('display'=>false,'title'=>'Accomplishment Id'),
        'location'=>array('display'=>false,'title'=>'Location'),
        'observation_date'=>array('display'=>true,'title'=>'Observation Date'),
        'observer'=>array('display'=>false,'title'=>'Observer'),
        'start_time'=>array('display'=>true,'title'=>'Start Time'),
        'end_time'=>array('display'=>true,'title'=>'End Time'),
        'clearing_index_pred'=>array('display'=>true,'title'=>'Predicted Clearing Index'),
        'clearing_index_act'=>array('display'=>true,'title'=>'Actual Clearing Index'),
        'observations'=>array('display'=>false,'title'=>'Observations'),
    );
}
