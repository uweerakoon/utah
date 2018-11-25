<?php

namespace Manager;

class PreBurn
{
    private $db_table_id = 8;
    private $min_user_level = 2;
    private $help_toggle = "<button data-toggle=\"tooltip\" data-title=\"Toggle Help\" class=\"btn btn-sm btn-default\" onclick=\"UtahHelp.toggleAll()\"><i class=\"glyphicon glyphicon-info-sign\"></i></button>";

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

    protected $renewal_status = array(
        'original'=>array('id'=>1),
        'revised'=>array('id'=>2),
        'yearly_renewal'=>array('id'=>3),
        'general_modification'=>array('id'=>4),
        'smoke_modification'=>array('id'=>5),
    );

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->pre_burn_form_id = 'pre_burn_form';
    }

    public function show()
    {
        /**
         * Shows the table list of all Pre-Burn requests associated with the burn_project.
         */

        $args = array('type'=>null,'burn_project_id'=>null,'user_id'=>$_SESSION['user']['id'],'agency_id'=>$_SESSION['user']['agency_id']);
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
            $cond .= "AND p.burn_project_id = $burn_project_id";
        }

        if ($permissions['write']['admin']) {
            // No agency requirement for admin.
            $pre_cond = "";
        } else {
            $pre_cond = "WHERE p.agency_id ".$agency;
        }

        if ($type == 'edit') {
            if ($permissions['write']['admin']) {
                $cond = "";
            } elseif ($permissions['write']['user_agency']) {
                $cond = "AND p.agency_id ".$agency;
            } elseif ($permissions['write']['user_district']) {
                $cond = "AND p.district_id ".$districts;
            } elseif ($permissions['write']['user']) {
                $cond = "AND p.added_by = $user_id
                    AND p.district_id ".$districts;
            }
        } elseif ($type == 'view') {
            if ($permissions['read']['user_agency']) {
                $cond = "AND p.agency_id ".$agency;
            } elseif ($permissions['read']['user'] || $permissions['read']['user_district']) {
                $cond = "AND p.added_by != $user_id
                    AND p.district_id ".$districts;
            }
        }

        $new_function = "PreBurn.newForm($burn_project_id)";

        $sql = "SELECT p.pre_burn_id, IF(p.active = 0, 'Historic Version', p.year) as \"Active Year\",
        a.agency as \"Agency\", CONCAT('<a href=\"project.php?detail=true&id=', b.burn_project_id ,'\">', b.project_name, '</a>') as \"Burn Project Name\",
        p.manager_name as \"Manager\",  p.manager_number as \"Manager Number\",
        CONCAT('<span class=\"', s.class ,'\" onclick=\"PreBurn.submitForm(', p.pre_burn_id, ')\">', s.name ,'</span>') as \"Form Status\",
        CONCAT('<span class=\"', r.class ,'\" onclick=\"PreBurn.revisionForm(', p.pre_burn_id, ')\">', r.name ,'</span>') as \"Form Revision Status\",
        COALESCE(CONCAT('<span class=\"', c.class ,'\" data-toggle=\"tooltip\" title=\"Click to Check\" onclick=\"PreBurn.validate(', p.pre_burn_id , ')\">', c.name ,'</span>'),'<span class=\"label label-default\">N/A</span>') as \"Form Completeness\",
        CONCAT('<span class=\"label label-default\">', u.full_name, '</span>') as \"Added By\"
        FROM pre_burns p
        JOIN burn_projects b ON(p.burn_project_id = b.burn_project_id)
        JOIN agencies a ON (b.agency_id = a.agency_id)
        JOIN pre_burn_statuses s ON (p.status_id = s.status_id)
        LEFT JOIN pre_burn_revisions r ON(p.revision_id = r.revision_id)
        LEFT JOIN pre_burn_completeness c ON(p.completeness_id = c.completeness_id)
        JOIN users u ON (p.added_by = u.user_id)
        $pre_cond
        $cond
        ORDER BY p.added_on";

        if ($type == 'edit' && $permissions['write']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'pre_burns','pkey'=>'pre_burn_id'
                ,'include_delete'=>true,'delete_function'=>'PreBurn.deleteConfirmation'
                ,'include_view'=>true,'view_href'=>'?pre_burn=true&id=@@'
                ,'edit_function'=>'PreBurn.editConfirmation','new_function'=>$new_function
                ,'no_results_message'=>'There are no editable pre-burn requests associated with your user. An approved burn project is required to submit pre-burn requests.'
                ,'no_results_class'=>'info','sort_direction'=>'asc'));
        } elseif ($type == 'view' && $permissions['read']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'pre_burns','pkey'=>'pre_burn_id','include_edit'=>false,'include_delete'=>false
                ,'include_view'=>true,'view_href'=>'?pre_burn=true&id=@@'
                ,'no_results_message'=>'There are no viewable pre-burn requests associated with your district(s).'
                ,'no_results_class'=>'info'));
        }

        $html = $table['html'];

        return $html;
    }

    public function form($page, $pre_burn_id = null, $burn_project_id = null)
    {
        /**
         *  Constructs the PreBurn form.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','interface');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        // Defaults
        $sm_numbers = array(1,2,3,4,5,6,7,8,9,10,11);
        $year = date('Y');

        // If a Pre-Burn is specified. Use its values (Edit/Update scenario).
        // This will extract a $burn_project_id as well (running on the next if)!
        // Extract some default values from the burn plan.
        if (isset($pre_burn_id)) {
            $pre_burn = $this->get($pre_burn_id);
            $burn_project_id = $pre_burn['burn_project_id'];
        }

        if (isset($burn_project_id)) {
            $temp_project = new \Manager\BurnProject($this->db);
            $burn_project = $temp_project->get($burn_project_id);
            $location = $burn_project['location'];
            $district_id = $burn_project['district_id'];
        }

        if (isset($pre_burn_id)) {
            extract($pre_burn);
            $burn_objectives = mm_values(array('ptable'=>'pre_burns','stable'=>'pre_burn_objective_presets','mmtable'=>'pre_burn_objectives','pcol'=>'pre_burn_id','scol'=>'pre_burn_objective_preset_id','sdisplay'=>'name','pvalue'=>$pre_burn_id));
        }

        if ($status_id > 5) {
            return status_message("The Pre-Burn has a status that prevents it from being edited", "info");
        }

        if ($page == 1) {
            $title = "Form 3: Pre-Burn Request <small>1/5</small>";

            $fieldset_id = $this->pre_burn_form_id . "_fs1";

            $ctls = array(
                'district_id'=>array('type'=>'hidden2','value'=>$district_id),
                'burn_project_id'=>array('type'=>'hidden2','value'=>$burn_project_id),
                'location'=>array('type'=>'marker','label'=>'Center Marker','value'=>$location,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'year'=>array('type'=>'text','label'=>'Year','value'=>$year,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'acres'=>array('type'=>'text','label'=>'This Years Acres','value'=>$acres,'enable_help'=>true,'table_id'=>$this->dt_table_id),
                'manager_name'=>array('type'=>'text','label'=>'Pre-Burn Manager Name','value'=>$manager_name,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_number'=>array('type'=>'text','label'=>'Pre-Burn Manager Number','value'=>$manager_number,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        } elseif ($page == 2) {
            $title = "Form 3: Pre-Burn Request <small>2/5</small>";

            $fieldset_id = $this->pre_burn_form_id . "_fs2";

            $ctls = array(
                'manager_cell'=>array('type'=>'text','label'=>'Pre-Burn Manager Cell','value'=>$manager_cell,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'burn_objectives'=>array('type'=>'combobox','label'=>'Burn Objective(s)','value'=>$burn_objectives,'table'=>'pre_burn_objective_presets','fcol'=>'pre_burn_objective_preset_id','display'=>'name','multiselect'=>true,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'receptors'=>array('type'=>'related','label'=>'Sensitive Receptors','title'=>'Receptor','onclick'=>"PreBurn.receptorForm(".$burn_project_id.")",'value_sql'=>'SELECT * FROM pre_burn_receptors WHERE pre_burn_id = ?;','value_executors'=>array($pre_burn_id),'display_js'=>'PreBurn.addReceptor'),
                'avoidance'=>array('type'=>'boolean','value'=>$avoidance,'label'=>'Planned Smoke Mitigation Method - Avoidance','enable_help'=>true,'table_id'=>$this->db_table_id),
                'dilution'=>array('type'=>'boolean','value'=>$dilution,'label'=>'Planned Smoke Mitigation Method - Dilution','enable_help'=>true,'table_id'=>$this->db_table_id),
                'primary_ert_id'=>array('type'=>'combobox','label'=>'Primary Emission Reduction Technique (ERT)','value'=>$primary_ert_id,'table'=>'emission_reduction_techniques','fcol'=>'emission_reduction_technique_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'alternate_primary_ert'=>array('type'=>'text','label'=>'Primary Emission Reduction Technique (if Other)','value'=>$alternate_primary_ert,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'primary_ert_pct'=>array('type'=>'text','label'=>'Primary ERT Percentage','value'=>$primary_ert_pct,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        } elseif ($page == 3) {
            $title = "Form 3: Pre-Burn Request <small>3/5</small>";

            $fieldset_id = $this->pre_burn_form_id . "_fs3";

            $ctls = array(
                'secondary_ert_id'=>array('type'=>'combobox','label'=>'Secondary ERT (ERT)','value'=>$secondary_ert_id,'table'=>'emission_reduction_techniques','fcol'=>'emission_reduction_technique_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id,'allownull'=>true),
                'alternate_secondary_ert'=>array('type'=>'text','label'=>'Secondary Emission Reduction Technique (if Other)','value'=>$alternate_secondary_ert,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'dispersion_model_id'=>array('type'=>'combobox','label'=>'Smoke Dispersion Model','value'=>$dispersion_model_id,'enable_help'=>true,'table_id'=>$this->db_table_id,'table'=>'dispersion_models','fcol'=>'dispersion_model_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'alternate_dispersion_model'=>array('type'=>'text','label'=>'Alternate Dispersion Model (if Other)','value'=>$alternate_dispersion_model,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'pm_min'=>array('type'=>'text','label'=>'Total Particulate Matter (PM) - Min (Tons)','value'=>$pm_min,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'pm_max'=>array('type'=>'text','label'=>'Total Particulate Matter (PM) - Max (Tons)','value'=>$pm_max,'enable_help'=>true,'table_id'=>$this->db_table_id),
             );
        } elseif ($page == 4) {
            $title = "Form 3: Pre-Burn Request <small>4/5</small>";

            $fieldset_id = $this->pre_burn_form_id . "_fs4";

            $marker_js = "$(\"[name='my[location]']\").val();";

            $ctls = array(
                'day_iso'=>array('type'=>'wind','label'=>'Anticipated Daytime Wind Flow','value'=>$day_iso,'marker'=>$location,'marker_js'=>$marker_js,'color'=>$this->day_iso_color,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        } elseif ($page == 5) {
            $title = "Form 3: Pre-Burn Request <small>5/5</small>";

            $fieldset_id = $this->pre_burn_form_id . "_fs5";

            $marker_js = "$(\"[name='my[location]']\").val();";

            $ctls = array(
                'night_iso'=>array('type'=>'wind','label'=>'Anticipated Nighttime Wind Flow','value'=>$night_iso,'marker'=>$location,'marker_js'=>$marker_js,'color'=>$this->night_iso_color,'enable_help'=>true,'table_id'=>$this->db_table_id)
            );
        }

        if ($page == 1) {
            $html .= mkForm(array('id'=>$this->pre_burn_form_id,'controls'=>$ctls,'title'=>$title,'suppress_submit'=>true,'fieldset_id'=>$fieldset_id));
        } else {
            $html .= mkFieldset(array('controls'=>$ctls,'title'=>$title,'id'=>$fieldset_id,'append'=>$append));
        }

        return $html;
    }

    public function receptorForm($origin, $burn_project_id)
    {
        //$title = "Pre-Burn Request <small>1/3</small>";

        global $map_center;

        //if (isset($burn_project_id)) {
        //    $receptors = fetch_assoc("SELECT * FROM `pre_burn_receptors` WHERE `pre_burn_id` IN(SELECT `pre_burn_id` FROM `pre_burns` WHERE `burn_project_id` = ?);", $burn_project_id);
        //}
//
        //echo code($receptors);

        $targetId = 'receptors_pad';

        $ctls = array(
            //'history'=>array('type'=>'combobox','Previous Receptors'),
            'name'=>array('type'=>'text','label'=>'Sensitive Receptor Name','value'=>$name),
            'receptor_location'=>array('type'=>'marker','label'=>'Location','input_type'=>'dist-deg','origin'=>$origin,'secondary_icon_url'=>'/images/receptor.png'),
        );

        $html .= mkForm(array('theme'=>'modal','id'=>'form-receptor','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
            <div class=\"btn-group\">
              <button class=\"btn btn-default\" onclick=\"PreBurn.addReceptor('{$targetId}')\">Add Receptor</button>
            </div>
            <div class=\"btn-group\">
              <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
            </div>
          </div>";

        return $html;
    }

    public function burnSelector($user_id)
    {
        /**
         *  Construct a button list of valid burn plans for Pre-Burn submittal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency','admin'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        if($permissions['admin']) {
            $cond = "";
        } elseif ($permissions['user_agency']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)";
        } elseif ($permissions['user_district']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)
                AND b.district_id ".$districts.";";
        } elseif ($permissions['user']) {
            $cond = "WHERE b.added_by = $user_id
                AND b.district_id ".$districts.";";
        }

        // $cont .= 'AND b.status_id = 4';

        // Select burn_projects from those agencies with approved status and no active burn project.
        $select = fetch_assoc(
            "SELECT b.burn_project_id, project_name, project_number
            FROM burn_projects b
            LEFT JOIN (
                SELECT burn_project_id, MAX(year)
                FROM pre_burns
                WHERE active = '1'
                GROUP BY burn_project_id
            ) p ON(p.burn_project_id = b.burn_project_id)
            $cond");

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
            This agency has no approved burn projects that do not have an active pre-burn. Pre-Burns can only be drafted for approved burn projects without active pre-burns.
            </div>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-y: scroll\">";

            foreach ($select as $value) {
                $small_font = "";
                if (strlen($value['project_name']) >= 24) {
                    $small_font = "style=\"font-size: 10px;\"";
                }
                $html .= "<button class=\"btn btn-default btn-block\" $small_font onclick=\"PreBurn.newForm(".$value['burn_project_id'].")\">".$value['project_name']." - ".$value['project_number']."</button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function submittalForm($pre_burn_id)
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

        $burn = fetch_row("SELECT status_id, burn_project_id FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);
        $burn_project = fetch_row("SELECT status_id FROM burn_projects WHERE burn_project_id = ?;", $burn['burn_project_id']);

        $validate = $this->validateRequired($pre_burn_id);
        $valid = $validate['valid'];

        if ($burn_project['status_id'] < 4) {
            $html = "<div>
                <p class=\"text-center\">The Burn Project must be approved before this Pre-Burn can be submitted for approval.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revisionForm($pre_burn_id)\">Revise Pre-Burn</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] >= $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Pre-Burn Request was already approved. You may revise it using the button below.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revisionForm($pre_burn_id)\">Revise Pre-Burn</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->revision_requested_id) {
            if ($valid) {
                if ($this->reviewCheck($pre_burn_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The  Pre-Burn Request is valid and has been revised since the last request for revision. To ensure minimal processing time, please make sure the revision addresses all review comments before re-submitting to Utah.gov.</p>
                        <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Plan Details</a>
                        <button class=\"btn btn-success btn-block\" onclick=\"PreBurn.submitToUtah($pre_burn_id)\">Re-submit <strong>$burn_name</strong> to Utah.gov</button>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Pre-Burn Request has not been revised since the last request for revision. Please revise the burn according to latest review comment.</p>
                        <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            } else {
                if ($this->reviewCheck($pre_burn_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The Pre-Burn Request has been revised since the last request for revision but is not valid.</p>
                        <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Pre-Burn Request has not been revised since the last request for revision and is not valid.</p>
                        <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            }
        } elseif ($burn['status_id'] == $this->under_review_id) {
            $html = "<div>
                    <p class=\"text-center\">The Pre-Burn Request is currently being reviewed by Utah.gov. Please check back for any requested revisions, or the plans approval.</p>
                    <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            if ($valid) {
                $html = "<div>
                    <p class=\"text-center\">The draft is completed and can be submitted to Utah.gov.</p>
                    <button class=\"btn btn-success btn-block\" onclick=\"PreBurn.submitToUtah($pre_burn_id)\">Submit <strong>$burn_name</strong> to Utah.gov</button>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            } else {
                $html = "<div>
                        <p class=\"text-center\">The Pre-Burn Request is not completed. Please ensure all required fields are filled in.</p>
                        <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            }

        }

        return $html;
    }

    public function ownerChangeForm($pre_burn_id)
    {
        /**
         *  Change Ownership Form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT COALESCE(submitted_by, updated_by, added_by) as user_id, agency_id FROM pre_burns WHERE pre_burn_id = ?", $pre_burn_id);
        $user_sql = "SELECT user_id, email, full_name FROM users;";
        $district_sql = "SELECT district_id, CONCAT(identifier, ' - ', district) as name FROM districts;";

        $ctls = array(
            'user_id'=>array('type'=>'combobox','label'=>'New Pre-Burn Owner','fcol'=>'user_id','display'=>'email','sql'=>$user_sql,'value'=>$burn['user_id']),
            'district_id'=>array('type'=>'combobox','label'=>'New Designation','fcol'=>'district_id','display'=>'name','sql'=>$district_sql,'value'=>$burn['district_id'])
        );

        $html = mkForm(array('theme'=>'modal','id'=>'owner-change-form','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"PreBurn.ownerChange({$pre_burn_id})\">Change Owner</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>
                </div>";

        return $html;
    }

    public function ownerChange($pre_burn_id, $user_id, $district_id)
    {
        /**
         *  Change The Burn Owner
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $status_id = $this->getStatus($pre_burn_id);
        $agency_id = fetch_one("SELECT agency_id FROM users WHERE user_id = ?", $user_id);

        if ($status_id['status_id'] >= $this->approved_id) {
            $change = $this->pdo->prepare("UPDATE pre_burns SET added_by = ?, updated_by = ?, submitted_by = ?, agency_id = ?, district_id = ? WHERE pre_burn_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $user_id, $agency_id, $district_id, $pre_burn_id));
        } else {
            $change = $this->pdo->prepare("UPDATE pre_burns SET added_by = ?, updated_by = ?, agency_id = ?, district_id = ? WHERE pre_burn_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $agency_id, $district_id, $pre_burn_id));
        }

        if ($change->rowCount() > 0) {
            $html = status_message("The pre-burn owner has successfully been changed.", "success");
        } else {
            $html = status_message("The pre-burn owner change was not successful.", "error");
        }

        return null;
    }

    public function submitUtah($pre_burn_id)
    {
        /**
         *  Determine if the burn is valid, and change it to submitted/pending.
         *  Add a valid burn number when submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $valid = $this->validateRequired($pre_burn_id);
        $valid = $valid['valid'];
        $now = now();
        $submitted_by = $_SESSION['user']['id'];

        // Check if its submitted already
        if ($valid == true) {
            $status = $this->getStatus($pre_burn_id);
            $status_id = $status['status_id'];
        }

        // Not submitted, and valid. Submit to Utah.gov:
        if ($valid == true && in_array($status_id, array(1,3))) {
            // The burn plan is valid. Change its status to "Under Review"
            $last_submitted_by = fetch_one("SELECT submitted_by FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);
            if(!empty($last_submitted_by)) {
                $submitted_by = $last_submitted_by;
            }
            $update_sql = $this->pdo->prepare("UPDATE pre_burns SET status_id = ?, submitted_on = ?, submitted_by = ? WHERE pre_burn_id = ?;");
            $update_sql->execute(array(2, $now, $submitted_by, $pre_burn_id));
            if ($update_sql->rowCount() > 0) {
                $result['message'] = status_message("The Pre-Burn Request has been submitted to Utah.gov.", "success");

                $notify = new \Info\Notify($this->db);
                $notify->preBurnSubmitted($pre_burn_id);
            } else {
                $result['message'] = status_message("The Pre-Burn Request is valid, but failed to submit.", "error");
            }
        } elseif (in_array($status_id, array(2,4,5,6,7))) {
            $result['message'] = status_message("The Pre-Burn Request was already submitted.", "warning");
        } else {
            $result['message'] = status_message("The Pre-Burn Request must be Validated before submitting.", "error");
        }

        return $result;
    }

    public function editApproved($pre_burn_id)
    {
        /**
         *  Produce the edit confirmation HTML for previously approved plans. Forwards to toDraft($burn_project_id).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $html = "<div>
            <p class=\"text-center\">The pre-burn is approved and must be changed to a draft before editing. Please note the draft project must be re-submitted to Utah.gov for approval.</p>
            <button class=\"btn btn-warning btn-block\" onclick=\"PreBurn.toDraft($pre_burn_id)\">Change to Draft</button>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    public function toDraft($pre_burn_id)
    {
        /**
         *  Convert a plan to draft status.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $now = new \DateTime(date("Y-m-d"));
        $threshold = new \DateTime(date('Y-m-d', strtotime('April-1')));

        $to_draft = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?;");
        $to_draft->execute(array($this->draft_id, $burn_project_id));

        if ($to_draft->rowCount() > 0) {
            $result['message'] = status_message("The Burn Project was converted to draft.", "success");

            if ($now > $threshold) {
                //$notify = new \Info\Notify($this->db);
                //$notify->lateAnnual($burn_project_id);
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn project was not successfully converted to draft", "success");
        }

        return $result;
    }

    public function revisionForm($pre_burn_id)
    {
        /**
         *  Creates the html block to revise & renew the burn.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $pre_burn = fetch_row("SELECT revision_id, status_id, active, year FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);
        $later = fetch_one("SELECT COALESCE(MAX(pre_burn_id), 0) as later FROM pre_burns WHERE burn_project_id IN(SELECT burn_project_id FROM pre_burns WHERE pre_burn_id = ?) AND year > ?;", array($pre_burn_id, $pre_burn['year']));

        $validate = $this->validateRequired($pre_burn_id);
        $valid = $validate['valid'];

        $current_year = Date('Y');

        if ($valid && $pre_burn['status_id'] == 4 && $pre_burn['active'] == true) {
            if ($pre_burn['year'] < $current_year) {

                // Year is behind current, allow yearly renewal.
                $buttons .= "<button class=\"btn btn-default btn-block\" onclick=\"PreBurn.renew($pre_burn_id)\">Renew for Current Year - <strong>{$current_year}</strong></button>";
            }

            $buttons .= "<button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revise($pre_burn_id, 'general')\">Revise General Elements Only</button>
                <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revise($pre_burn_id, 'smoke')\">Revise Smoke Elements Only</button>
                <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revise($pre_burn_id, 'all')\">Revise General & Smoke Elements</button>";

            $html = "<div>
                    $buttons
                    <div style=\"margin: 10px 0px 10px 0px; font-size: 11px;\"><i class=\"glyphicon glyphicon-info-sign\"></i> If the Pre-Burn request is revised a new pre-burn will be saved. In this case the new pre-burn will become active and take precidence over the original.</div>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } elseif($valid && $pre_burn['status_id'] == 4 && $later == 0) {
            $html = "<div>
                    <p class=\"text-center\">The Pre-Burn request is historical but later pre-burns do not exist.</p>
                    <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.renew($pre_burn_id)\">Renew for Current Year - <strong>{$current_year}</strong></button>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            $html = "<div>
                    <p class=\"text-center\">The Pre-Burn request must not be historical and must be complete and approved before revising or renewing.</p>
                    <a href=\"?pre_burn=true&id=$pre_burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Pre-Burn Request Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        }

        return $html;
    }

    public function renew($pre_burn_id)
    {
        /**
         *  Renew the pre-burn for current year.
         *  Since nothing has changed, no need to duplicate.
         */

        $current_year = Date('Y');

        $update_sql = $this->pdo->prepare("UPDATE pre_burns SET active = ?, year = ? WHERE pre_burn_id = ?");
        $update_sql = execute_bound($update_sql, array(true, $current_year, $pre_burn_id));
        if ($update_sql->rowCount() > 0) {
            $result = status_message("The Pre-Burn has been renewed for {$current_year}.", "success");

            $notify = new \Info\Notify($this->db);
            $notify->preBurnRenewed($pre_burn_id);
        } else {
            $result = status_message("The Pre-Burn was not successfully renewed for {$current_year}.", "error");
        }

        return $result;
    }

    public function revise($pre_burn_id, $type)
    {
        /**
         *  Revise the pre-burn.
         *  Duplicates, creates history link, and opens edit form on new.
         */

        $dup_result = $this->duplicate($pre_burn_id);
        $new_pre_burn_id = $dup_result['pre_burn_id'];

        $deactivate_sql = $this->pdo->prepare("UPDATE pre_burns SET active = ? WHERE pre_burn_id = ?");
        $deactivate_sql = execute_bound($deactivate_sql, array(false, $pre_burn_id));
        if ($deactivate_sql->rowCount() == 0) {
            $result['error'] = true;
        }

        switch ($type) {
            case 'general':
                // General elements revised only.
                $revision_id = 3;
                break;
            case 'smoke':
                // Smoke elements revised only.
                $revision_id = 4;
                break;
            default:
                // Assumes 'all', both smoke and non-smoke revised.
                $revision_id = 5;
                break;
        }

        $revision_sql = $this->pdo->prepare("UPDATE pre_burns SET revision_id = ?, active = ? WHERE pre_burn_id = ?");
        $revision_sql = execute_bound($revision_sql, array($revision_id, true, $new_pre_burn_id));
        if ($revision_sql->rowCount() > 0) {
            $result['message'] = status_message("The Pre-Burn has been duplicated.", "success");

            $notify = new \Info\Notify($this->db);
            $notify->preBurnRevised($pre_burn_id);
        } else {
            $result['message'] = status_message("The Pre-Burn has failed to duplicate.", "error");
            $result['error'] = true;
        }

        $result['pre_burn_id'] = $new_pre_burn_id;

        return $result;
    }

    public function toolbar($page, $pre_burn_id)
    {
        /**
         *   Produces the standard burn plan form toolbar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            exit;
        }

        if (isset($pre_burn_id)) {
            $status_id = fetch_one("SELECT status_id FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);
        }

        if ($status_id > $this->revision_requested_id) {
            return "";
        }

        $toolbar_class = $this->pre_burn_form_id . "_tb";
        $btn_class = "btn-sm btn-default";

        if (isset($pre_burn_id)) {
            $c_pre_burn_id = ", $pre_burn_id";
            $save_function = "PreBurn.update($pre_burn_id)";
        } else {
            $save_function = "PreBurn.save()";
        }

        if ($page == 1) {
            $html = "<div class=\"$toolbar_class pull-right btn-group\">
                ".$this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(2$c_pre_burn_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>
            </div>";
        } elseif ($page == 2) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(1$c_pre_burn_id)\">Back</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(3$c_pre_burn_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        } elseif ($page == 3) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(2$c_pre_burn_id)\">Back</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(4$c_pre_burn_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        } elseif ($page == 4) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(3$c_pre_burn_id)\">Back</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(5$c_pre_burn_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        } elseif ($page == 5) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"PreBurn.showForm(4$c_pre_burn_id)\">Back</button>
                <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        }

        return $html;
    }

    public function save($pre_burn)
    {
        /**
         *  Save a Pre-Burn request.
         */

        $permissions = checkFunctionPermissions(
          $_SESSION['user']['id'],
          array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        if ($pre_burn['year'] == date('Y')) {
            $active = True;
        } else {
            $active = False;
        }

        extract(prepare_values($pre_burn));

        $added_by = $_SESSION['user']['id'];
        $added_on = now();
        $agency_id = $_SESSION['user']['agency_id'];
        $status_id = 1;
        $revision_id = 1;
        $secondary_ert_id = $secondary_ert_id == "null"? null: $secondary_ert_id;

        $insert_sql = $this->pdo->prepare(
          "INSERT INTO pre_burns (burn_project_id, agency_id, district_id,
            year, acres, added_by, added_on, location, manager_name,
            manager_number, manager_cell, avoidance, dilution,
            primary_ert_id, alternate_primary_ert, primary_ert_pct,
            secondary_ert_id, alternate_secondary_ert, dispersion_model_id,
            alternate_dispersion_model, pm_min, pm_max, day_iso, night_iso,
            status_id, active, revision_id)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_sql = execute_bound($insert_sql, array($burn_project_id,
          $agency_id, $district_id, $year, $acres, $added_by, $added_on,
          $location, $manager_name, $manager_number, $manager_cell,
          $avoidance, $dilution, $primary_ert_id, $alternate_primary_ert,
          $primary_ert_pct, $secondary_ert_id, $alternate_secondary_ert,
          $dispersion_model_id, $alternate_dispersion_model, $pm_min, $pm_max,
          $day_iso, $night_iso, $status_id, $active, $revision_id)
        );

        if ($insert_sql->rowCount() > 0) {
            $success_message .= "The Pre-Burn Request was saved. ";
        } else {
            $result['error'] = true;
            $error_message .= "The Pre-Burn Request could not be saved. ";
        }

        $pre_burn_id = fetch_one("SELECT pre_burn_id
          FROM pre_burns
          WHERE burn_project_id = ?
          AND added_by = ?
          AND added_on = ?;",
          array($burn_project_id, $added_by, $added_on)
        );
        $result['pre_burn_id'] = $pre_burn_id;

        // Insert burn_objectives if specified.
        if (is_array($burn_objectives)) {
            foreach ($burn_objectives as $value) {
                if (is_array($value)) {
                    // For duplicate function.
                    $value = $value['pre_burn_objective_preset_id'];
                }
                $burn_objective_sql = $this->pdo->prepare("INSERT INTO pre_burn_objectives (pre_burn_id, pre_burn_objective_preset_id) VALUES (?, ?);");
                $burn_objective_sql = execute_bound($burn_objective_sql, array($pre_burn_id, $value));

                if ($burn_objective_sql->rowCount() == 0) {
                    $result['error'] = true;
                    $error_message .= "One or more Burn Objectives failed to save. ";
                }
            }
        }

        // Insert receptors if specified.
        if (is_array($receptors)) {
            foreach ($receptors as $value) {
                if (!is_array($value)) {
                    // For duplicate function.
                    $value = json_decode($value, true);
                }
                $receptor_sql = $this->pdo->prepare("INSERT INTO pre_burn_receptors (pre_burn_id, name, location, miles, degrees) VALUES (?, ?, ?, ?, ?);");
                $receptor_sql = execute_bound($receptor_sql, array($pre_burn_id, $value['name'], $value['location'], $value['miles'], $value['degrees']));

                if ($receptor_sql->rowCount() == 0) {
                    $result['error'] = true;
                    $error_message .= "One or more Sensitive Receptors failed to save. ";
                }
            }
        }

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        $this->validateRequired($pre_burn_id);

        return $result;
    }

    public function update($pre_burn, $pre_burn_id)
    {
        /**
         *  Update the Pre-Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Extract the old values
        $original = $this->get($pre_burn_id);
        extract($original);

        // Overwrite variables with updated.
        $updated_by = $_SESSION['user']['id'];
        $updated_on = now();

        extract(prepare_values($pre_burn));

        // Update the Pre-Burn.
        $pre_burn_sql = $this->pdo->prepare(
          "UPDATE pre_burns SET year = ?, acres = ?, updated_by = ?,
            updated_on = ?, location = ?, manager_name = ?, manager_number = ?,
            manager_cell = ?, avoidance = ?, dilution = ?, primary_ert_id = ?,
            alternate_primary_ert = ?, primary_ert_pct = ?,
            secondary_ert_id = ?, alternate_secondary_ert = ?,
            dispersion_model_id = ?, alternate_dispersion_model = ?,
            pm_min = ?, pm_max = ?, day_iso = ?, night_iso = ?
          WHERE pre_burn_id = ?;"
        );
        $pre_burn_sql->execute(
          array($year, $acres, $updated_by, $updated_on, $location,
          $manager_name, $manager_number, $manager_cell, $avoidance, $dilution,
          $primary_ert_id, $alternate_primary_ert, $primary_ert_pct,
          $secondary_ert_id, $alternate_secondary_ert, $dispersion_model_id,
          $alternate_dispersion_model, $pm_min, $pm_max, $day_iso, $night_iso,
          $pre_burn_id)
        );

        // Delete previous liners.
        $many = $this->deleteManyMany($pre_burn_id);

        // Insert burn_objectives if specified.
        if (is_array($burn_objectives)) {
          foreach ($burn_objectives as $value) {
            if (is_array($value)) {
              // For duplicate function.
              $value = $value['pre_burn_objective_preset_id'];
            }
            $burn_objective_sql = $this->pdo->prepare("INSERT INTO pre_burn_objectives (pre_burn_id, pre_burn_objective_preset_id) VALUES (?, ?);");
            $burn_objective_sql = execute_bound($burn_objective_sql, array($pre_burn_id, $value));

            if ($burn_objective_sql->rowCount() == 0) {
              $result['error'] = true;
              $error_message .= "One or more Burn Objectives failed to save. ";
            }
          }
        }

        // Insert receptors if specified.
        if (is_array($receptors)) {
          foreach ($receptors as $value) {
            if (!is_array($value)) {
              // For duplicate function.
              $value = json_decode($value, true);
            }
            $receptor_sql = $this->pdo->prepare("INSERT INTO pre_burn_receptors (pre_burn_id, name, location, miles, degrees) VALUES (?, ?, ?, ?, ?);");
            $receptor_sql = execute_bound($receptor_sql, array($pre_burn_id, $value['name'], $value['location'], $value['miles'], $value['degrees']));

            if ($receptor_sql->rowCount() == 0) {
              $result['error'] = true;
              $error_message .= "One or more Sensitive Receptors failed to save. ";
            }
          }
        }

        if ($result['error'] == true) {
          $result['message'] = status_message($error_message, "error");
        } else {
          $result['message'] = status_message("The Pre-Burn Request was updated.", "success");
        }

        $this->validateRequired($pre_burn_id);

        return $result;
    }

    public function duplicate($pre_burn_id)
    {
        /**
         *  Duplicate an existing pre-burn (this is used when revisions are done)
         */

        $pre_burn = $this->get($pre_burn_id);

        $result = $this->save($pre_burn);

        return $result;
    }

    public function deleteConfirmation($pre_burn_id)
    {
        /**
         *  Return the delete confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn = fetch_row("SELECT CAST(COALESCE(submitted_on, added_on) AS date) as submitted_on, burn_project_id, status_id FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);
        $burn_project = fetch_row("SELECT project_name, project_number FROM burn_projects WHERE burn_project_id = ?;", $burn['burn_project_id']);

        if ($burn['status_id'] == $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Pre-Burn request approved and cannot be deleted. Please contact Utah.gov if you would like to cancel this burn.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->disapproved_id) {
            $html = "<div>
                <p class=\"text-center\">The Pre-Burn request is disapproved. You may delete the burn or leave it for archiving purposes.</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"PreBurn.deleteRecord($pre_burn_id)\">Delete <strong>".$burn_project['burn_number']." - ".$burn['year']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete <strong>".$burn_project['project_name']." - ".$burn['year']."</strong>?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"PreBurn.deleteRecord($pre_burn_id)\">Delete <strong>".$burn_project['project_number']." - ".$burn['year']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function delete($pre_burn_id)
    {
        /**
         *  Delete the Pre-Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Delete the many.
        $many = $this->deleteManyMany($pre_burn_id);

        $pre_burn_sql = $this->pdo->prepare("DELETE FROM pre_burns WHERE pre_burn_id = ?;");
        $pre_burn_sql->execute(array($pre_burn_id));
        if ($pre_burn_sql->rowCount() > 0 && $many) {
            $result['message'] = status_message("The Pre-Burn Request was deleted.", "success");
        } elseif ($pre_burn_sql->rowCount() > 0 && $liners == false) {
            $result['error'] = true;
            $result['message'] = status_message("The Pre-Burn Request was deleted, but associated liners were not!", "error");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Pre-Burn was not deleted.", "error");
        }

        return $result;
    }

    private function deleteManyMany($pre_burn_id)
    {
        /**
         *  Delete associated many to many (liners).
         *  (Cascade for Update as well).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Objectives

        $select_obj = $this->pdo->query("SELECT pre_burn_objective_id FROM pre_burn_objectives WHERE pre_burn_id = $pre_burn_id;");

        $delete_obj = $this->pdo->prepare("DELETE FROM pre_burn_objectives WHERE pre_burn_id = ?;");
        $delete_obj->execute(array($pre_burn_id));

        // Receptors

        $select_rec = $this->pdo->query("SELECT pre_burn_receptor_id FROM pre_burn_receptors WHERE pre_burn_id = $pre_burn_id;");

        $delete_rec = $this->pdo->prepare("DELETE FROM pre_burn_receptors WHERE pre_burn_id = ?;");
        $delete_rec->execute(array($pre_burn_id));
        if ($delete_obj->rowCount() > 0 && $select_obj->rowCount() > 0 && $delete_rec->rowCount() > 0 && $select_rec->rowCount() > 0) {
            // All originally selected rows are now deleted.
            return true;
        }

        return false;
    }

    protected function reviewCheck($pre_burn_id)
    {
        /**
         *  Check if the burn plan was updated since the last review.
         */

        $review_last_updated = fetch_one("SELECT MAX(last_burn_update) FROM pre_burn_reviews WHERE pre_burn_id = $pre_burn_id;");
        $last_updated = fetch_one("SELECT updated_on FROM pre_burns WHERE pre_burn_id = $pre_burn_id;");

        if ($last_updated > $review_last_updated) {
            return true;
        } else {
            return false;
        }
    }

    public function get($pre_burn_id)
    {
        /**
         *  Get a Pre-Burn.
         */

        // Get the pre-burn plan info.
        $pre_burn = fetch_row(
          "SELECT * FROM pre_burns WHERE pre_burn_id = $pre_burn_id;");
        $result = $pre_burn;

        // Get the associated objectives.
        $pre_burn_objectives = fetch_assoc(
          "SELECT po.pre_burn_objective_preset_id, o.name
          FROM pre_burn_objectives po
          JOIN pre_burn_objective_presets o ON(
            po.pre_burn_objective_preset_id = o.pre_burn_objective_preset_id)
          WHERE pre_burn_id = ?;",
          $pre_burn_id
        );
        if (!$pre_burn_objectives['error']) {
          $result['burn_objectives'] = $pre_burn_objectives;
        }

        // Get the associated receptors.
        $receptors = fetch_assoc(
          "SELECT * FROM pre_burn_receptors WHERE pre_burn_id = ?;",
          $pre_burn_id
        );
        if (!$receptors['error']) {
            $result['receptors'] = $receptors;
        }

        // Get basic burn project info.
        $burn_project = fetch_row(
          "SELECT burn_project_id, project_name, project_number, location
          FROM burn_projects
          WHERE burn_project_id = (
            SELECT burn_project_id FROM pre_burns WHERE pre_burn_id = ?
          );", $pre_burn_id);
        if (!$burn_project['error']) {
            $result['burn_project'] = $burn_project;
        }

        return $result;
    }

    public function overviewPage()
    {
        /**
         *
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
            $return_link = "<a href=\"/manager/pre_burn.php\">Return to Overview</a>";
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
                <h3>Overview <small>Form 3: Pre-Burn Requests</small></h3>
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

        if ($permissions['write']['deny']) {
            $html['main'] .= "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <div class=\"pull-right\">
                        <button class=\"btn btn-sm btn-default\" onclick=\"PreBurn.newForm($app_burn_project_id)\">New Pre-Burn</button>
                    </div>
                </div>
            </div>";
        }

        return $html;
    }

    public function detailPage($pre_burn_id)
    {
        /**
         *  Pre-Burn Detail Page
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkPreBurnPermissions($_SESSION['user']['id'], $pre_burn_id, $permissions);

        // Get the Pre-Burn.
        $pre_burn = $this->get($pre_burn_id);

        if ($burn_permissions['allow']) {

            // Construct the title.
            if (isset($pre_burn['burn_project']['project_name']) && isset($pre_burn['burn_project']['project_number'])) {
                $title = $pre_burn['burn_project']['project_name'] ." / ".$pre_burn['burn_project']['project_number'];
            } elseif (isset($pre_burn['burn_project']['project_name'])) {
                $title = $pre_burn['burn_project']['project_name'];
            } else {
                $title = "Burn Plan";
            }

            // Statics
            $return_link = "<a href=\"/manager/pre_burn.php\">Return to Overview</a>";

            if (in_array($pre_burn['status_id'], $this->edit_status_id) && $burn_permissions['write']) {
                if ($pre_burn['status_id'] < $this->revision_requested_id) {
                    $submit_text = "Submit";
                } else {
                    $submit_text = "Re-submit";
                }

                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"PreBurn.submitForm($pre_burn_id)\">$submit_text</button>
                    <button class=\"btn btn-sm btn-default\" onclick=\"PreBurn.editConfirmation($pre_burn_id)\">Edit Pre-Burn Request</button>
                    <a href=\"/pdf/pre_burn.php?id={$pre_burn_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            } elseif ($pre_burn['status_id'] == $this->approved_id) {
                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                    <button class=\"btn btn-sm btn-default\" onclick=\"PreBurn.revisionForm($pre_burn_id)\">Renew or Revise Request</button>
                    <a href=\"/pdf/pre_burn.php?id={$pre_burn_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            } else {
                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                    <button class=\"btn btn-sm btn-default\" disabled>Renew or Revise Request</button>
                    <a href=\"/pdf/pre_burn.php?id={$pre_burn_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            }

            // Get HTML blocks.
            $status = $this->getStatusLabel($pre_burn_id);
            $map = $this->getMap($pre_burn_id);
            $table = $this->tablifyFields($pre_burn_id);
            $sidebar = $toolbar;
            $sidebar .= $this->getContacts($pre_burn_id);
            $sidebar .= $this->getConditions($pre_burn_id);
            $sidebar .= $this->getReviews($pre_burn_id);
            $sidebar .= $this->getUploads($pre_burn_id);

            // Construct the HTML array.
            $html['header'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <span class=\"pull-right\">
                        $return_link
                        $status
                    </span>
                    <h3>".$title." <small>Form 3: Pre-Burn Request</small></h3>
                </div>
            </div>";

            $html['main'] = "<div class=\"row\">
                <div class=\"col-sm-8\">
                    <h4>Form 3: Pre-Burn Request Info</h4>
                    <hr>
                    $map
                    <br>
                    $table
                </div>
                <div class=\"col-sm-4\">
                    $sidebar
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

    public function pdfPage($pre_burn_id)
    {

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkPreBurnPermissions($_SESSION['user']['id'], $pre_burn_id, $permissions);

        if ($burn_permissions['allow']) {
            // Get the Pre-Burn.
            $pre_burn = $this->get($pre_burn_id);

            // Get HTML blocks
            $table = $this->tablifyFields($pre_burn_id, true);
            $contacts = $this->getContacts($pre_burn_id);
            $conditions = $this->getConditions($pre_burn_id);
            $reviews = $this->getReviews($pre_burn_id);

            // Static fields.
            $project_name = $pre_burn['burn_project']['project_name'];
            $project_number = $pre_burn['burn_project']['project_number'];
            $manager_name = $pre_burn['manager_name'];
            $manager_number = $pre_burn['manager_number'];

            // Build the map.
            $location = str_replace(array('(',')',' '), '', $pre_burn['location']);
            $color = str_replace('#', '0x', $this->retrieveStatus($pre_burn['status_id'])['color']);
            $label = substr($this->retrieveStatus($pre_burn['status_id'])['title'], 0, 1);
            $static_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=14&center=$location&markers=color:$color%7Clabel:$label%7C$location";

            $receptor_images = "<strong>Sensitive Receptors:</strong><br><table>";
            foreach ($pre_burn['receptors'] as $value) {
                $location = str_replace(array('(',')',' '), '', $value['location']);
                $color = '0xff0700';
                $receptor_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=14&center=$location&markers=color:$color%7Clabel:$label%7C$location";
                $receptor_images .= "<tr><td style=\"width: 33%\">
                        <strong>{$value['name']}</strong><br>
                        Distance: {$value['miles']} (miles)<br>
                        Direction: {$value['degrees']} (degrees)
                    </td>
                    <td style=\"width: 66%\"><img width=\"20%\" src=\"$receptor_map\"/></td></tr>";
            }
            $receptor_images .= "</table>";

            // Get HTML blocks.
            $html = "
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"50%\">
                        <col width=\"49%\">
                        <tr style=\"border: 0.15em solid black;\">
                            <td style=\"width: 50%\">Form 3: Pre-Burn - Active: <strong>{$pre_burn['year']}</strong> $label</td>
                            <td style=\"width: 50%\">Project: <strong>$project_number</strong> - $project_name</td>
                        </tr>
                        <tr style=\"border: 0.15em solid black; padding: 0.3em;\">
                            <td style=\"width: 71%\">
                                $table
                            </td>
                            <td style=\"width:28%\">
                                <strong>Location:</strong><br>
                                <img width=\"28%\" src=\"$static_map\"/>
                                <br>
                                <br>
                                $receptor_images
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

    public function getStatusLabel($pre_burn_id)
    {
        $status = fetch_row(
            "SELECT description, class, name FROM pre_burn_statuses
            WHERE status_id IN (SELECT status_id FROM pre_burns WHERE pre_burn_id = $pre_burn_id);"
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    public function noEditWarning($pre_burn_id)
    {
        /**
         *  Produce the no edit warning HTML. Only fires when a pre-burn cannot be edited.
         */

        $html = "<div>
            <p class=\"text-center\">The pre-burn is approved and must be revised to edit.</p>
            <button class=\"btn btn-default btn-block\" onclick=\"PreBurn.revisionForm($pre_burn_id)\">Revise Pre-Burn</button>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    protected function tablifyFields($pre_burn_id, $pdf = false)
    {
        /**
         *
         */

        if ($pdf) {
            $style = "style=\"width: 100%\"";
            $i_style = "style=\"width: 59%\"";
            $colspaces = "<col width=\"60%\">
                <col width=\"40%\">
                <col width=\"0%\">";
        } else {
            $v_title = "Value";
            $style = "";
            $colspaces = "<col width=\"60%\">
                <col width=\"47%\">
                <col width=\"12%\">";
        }

        $pre_burn = $this->get($pre_burn_id);

        $title = "Pre-Burn Information";
        $value_array = $pre_burn;
        $fields_array = array('year','acres','manager_name','manager_number','manager_cell','burn_objectives',
            'receptors','psm_label','avoidance','dilution','primary_ert_id','alternate_primary_ert',
            'primary_ert_pct','secondary_ert_id','alternate_secondary_ert','dispersion_model_id',
            'alternate_dispersion_model','pm_min','pm_max');

        $html = "<table $style class=\"table table-responsive table-condensed\">
            $colspaces
            <thead>
            <tr><th>$title</th><th>$v_title</th><th></th></tr>
            </thead>
            <tbody>";

        foreach ($fields_array as $key) {

            $reference = $this->value_map[$key];
            $value = $value_array[$key];

            if ($reference['multiselect'] == true) {
                $reference['pvalue'] = $pre_burn_id;
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
                $html .= "<tr><td $i_style>".$reference['title']."</td><td $i_style>".$value."</td><td>".$help."</td></tr>";
            }
        }

        $html .= "</tbody>
        </table>";

        return $html;
    }

    public function validateRequired($pre_burn_id)
    {
        /**
         *  Validates a saved burn for required fields.
         */

        // The missing value count.
        $count = 0;
        $missing_msg = "The following required fields are missing in this Pre-Burn Request:<br><br>";

        // Get the burn.
        $pre_burn = $this->get($pre_burn_id);

        // Check the base values.
        $base_required = array('year'=>'Ignition Date','location'=>'Location',
            'manager_name'=>'Manager Name','manager_number'=>'Manager Number','manager_cell'=>'Manager Cell',
            'avoidance'=>'Planned Smoke Mitigation Method - Avoidance','dilution'=>'Planned Smoke Mitigation Method - Dilution',
            'primary_ert_id'=>'Primary ERT','primary_ert_pct'=>'Primary ERT Percentage',
            'night_iso'=>'Nighttime Wind Dispersion',
        );

        foreach ($base_required as $key => $value) {
            if (is_null($pre_burn[$key])) {
                $count++;
                $missing_msg .= "No ".$value."<br>";
            }
        }

        if ($pre_burn['burn_objectives'] == false) {
            if (is_null($pre_burn['burn_objectives'])) {
                $count++;
                $missing_msg .= "No Burn Objectives.<br>";
            }
        }

        // Update the burn plan with its completeness status.
        $update_sql = $this->pdo->prepare("UPDATE pre_burns SET completeness_id = ? WHERE pre_burn_id = $pre_burn_id");

        if ($count == 0) {
            // Update to valid. No missing was counted.
            $update_sql->execute(array(2));
            $result['valid'] = true;
            $result['message'] = modal_message("All required Pre-Burn Request info is filled out.", "success").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
            $update_sql->execute(array(1));
            $result['valid'] = false;
            $result['message'] = modal_message($missing_msg, "error").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        }

        return $result;
    }

    public function getStatus($pre_burn_id)
    {
        /**
         *  Get the status id of the burn.
         */

        $result['status_id'] = fetch_one("SELECT status_id FROM pre_burns WHERE pre_burn_id = ?;", $pre_burn_id);

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

    protected function getMap($pre_burn_id)
    {
        /**
         *  Builds a boundary & marker map for a single Pre-Burn.
         */

        $zoom_to_fit = true;
        $control_title = "Zoom to Pre-Burn request";

        $pre_burn = $this->get($pre_burn_id);
        $marker = $pre_burn['location'];
        $day_iso = json_decode($pre_burn['day_iso'], true);
        $night_iso = json_decode($pre_burn['night_iso'], true);
        $receptors = $pre_burn['receptors'];
        $pre_burn_status = $this->retrieveStatus($pre_burn['status_id']);

        global $map_center;

        if ($zoom_to_fit) {
            $zoom = "var bounds = new google.maps.LatLngBounds();";

            $zoom_ctl = "map.fitBounds(bounds);";
        } else {
            $zoom_ctl = "map.setZoom(11);
                  map.panTo(marker.position);";
        }

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
                $zoom_ctl
              });

            }";

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
                            fillColor: '".$pre_burn_status['color']."',
                            fillOpacity: 1
                        },
                    });
                }

                marker.setMap(map)
            ";

            if ($zoom_to_fit) {
                $zoom .= "bounds.extend(marker_center);";
            }
        } else {
            $center = "zoom: 10,
                center: new google.maps.LatLng($map_center),";
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

            if ($zoom_to_fit) {
                $zoom .= "var day_iso_path = day_iso.getPath().getArray();
                for(var i = 0; i < day_iso_path.length; i++) {
                    bounds.extend(day_iso_path[i]);
                }";
            }
        }

        if (!empty($night_iso)) {
            $color = $this->night_iso_color;
            $night_iso = "var night_iso = new isosceles(map, marker, {$night_iso['initDeg']}, {$night_iso['finalDeg']}, {$night_iso['amplitude']}, '{$color}')";
            if ($zoom_to_fit) {
                $zoom .= "var night_iso_path = night_iso.getPath().getArray();
                for(var i = 0; i < night_iso_path.length; i++) {
                    bounds.extend(night_iso_path[i]);
                }";
            }
        }

        if (!empty($receptors) && $receptors['error'] != true) {
            $receptors_length = count($receptors);

            $lat_lng_arr = "var receptorsLatLng = [\n";
            for ($i=0; $i < $receptors_length; $i++) {
                $receptor = $receptors[$i];
                $receptor['location'] = str_replace(array('(',')'), '', $receptor['location']);

                if ($i == ($receptors_length - 1)) {
                    $lat_lng_arr .= "[new google.maps.LatLng({$receptor['location']}), '{$receptor['name']}', '{$receptor['miles']}', '{$receptor['degrees']}']\n";
                } else {
                    $lat_lng_arr .= "[new google.maps.LatLng({$receptor['location']}), '{$receptor['name']}', '{$receptor['miles']}', '{$receptor['degrees']}'],\n";
                }
            }
            $lat_lng_arr .= "];";

            $receptor_js .= "var receptorIcon = {
                    url: '/images/receptor.png'
                };

                $lat_lng_arr

                function addReceptors(map, locations) {
                    var receptors = [];

                    for (var i = 0; i < locations.length; i++) {
                        receptors.push(new google.maps.Marker({
                            position: locations[i][0],
                            map: map,
                            draggable: false,
                            title: locations[i][1] + ', Dist: ' + locations[i][2] + ', Deg: ' + locations[i][3],
                            icon: receptorIcon
                        }));
                    }

                    return receptors;
                }

                var receptors = addReceptors(map, receptorsLatLng);
            ";

            if ($zoom_to_fit) {
                $zoom .= "for(var i = 0; i < receptors.length; i++) {
                    bounds.extend(receptors[i].getPosition());
                }";
            }
        }

        if ($zoom_to_fit) {
            $zoom .= "map.fitBounds(bounds);";
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

                $day_iso

                $night_iso

                $receptor_js

                $zoom

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
            $markers = fetch_assoc("SELECT p.pre_burn_id, p.status_id, p.location, CONCAT(b.project_number, ': active for ', p.year) as name, p.added_by FROM pre_burns p JOIN burn_projects b ON(b.burn_project_id = p.burn_project_id) WHERE p.burn_project_id IN(SELECT burn_project_id FROM burn_projects WHERE agency_id = $agency_id) AND p.active = true;");
            $zoom_to_fit = false;
        } elseif (isset($burn_project_id)) {
            $burn = fetch_row("SELECT burn_project_id, status_id, location FROM burn_projects WHERE burn_project_id = $burn_project_id;");
            $markers = fetch_assoc("SELECT pre_burn_id, status_id, location, added_by FROM pre_burns WHERE burn_project_id = $burn_project_id AND p.active = true;");
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
            $marker_arr = "var PreBurns = [\n ";
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
                $marker_arr .= "[".$value['pre_burn_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$value['name']."', '".$marker_status['color']."', ".$edit.",'".str_replace(" ", "_", strtolower($marker_status['title']))."']$comma\n ";
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
                            window.location='/manager/pre_burn.php?pre_burn=true&id='+marker.id;return false;
                        });
                    }
                }

                setMarkers(map, PreBurns);
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
            <div>
                <p><small>Map only displays active Pre-Burns. To see historical versions please use the table views.</small></p>
            </div>
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
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    protected function getContacts($pre_burn_id)
    {
        /**
         *  Constructs the contacts display div for a Pre-Burn.
         */

        $submitter = fetch_row(
            "SELECT u.full_name, u.email, u.phone, a.agency
            FROM pre_burns db
            JOIN users u ON(db.submitted_by = u.user_id)
            JOIN agencies a ON(u.agency_id = a.agency_id)
            WHERE db.pre_burn_id = ?
            "
        , $pre_burn_id);

        $contact = fetch_row("SELECT manager_name, manager_number FROM pre_burns WHERE pre_burn_id = ?", $pre_burn_id);

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
                            <p>Pre-Burn Manager</p>
                            <h5>".$contact['manager_name']."</h5>
                        </div>
                        <div class=\"contact-right\">
                            <p class=\"district\">Phone Number</p>
                            <a href=\"tel:".$contact['manager_number']."\">".$contact['manager_number']."</a>
                        </div>
                    </div>";

        $html .= "</div>";

        return $html;
    }

    protected function getReviews($pre_burn_id, $full = false)
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
            $pre_sql = "r.pre_burn_review_id, ";
        }

        $sql = "SELECT $pre_sql COALESCE(CONCAT(u.full_name, '<br><small><span class=\"label label-default\">Edited By</span></small>'), a.full_name) as \"Reviewer\", CONCAT('<a style=\"cursor: pointer\" onclick=\"PreBurn.reviewDetail(', r.pre_burn_review_id ,')\">', $com_cond '...</a>') as \"Excerpt\", CONCAT('<small>', COALESCE(r.updated_on, r.added_on), '</small>') as \"Edited\"
        FROM pre_burn_reviews r
        JOIN users a ON (r.added_by = a.user_id)
        LEFT JOIN users u ON (r.updated_by = u.user_id)
        WHERE pre_burn_id = $pre_burn_id";

        if ($admin['any']) {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Pre-Burn request.',
                'no_results_class'=>'info','pkey'=>'pre_burn_review_id','table'=>'pre_burn_reviews','include_edit'=>true,'include_delete'=>false,
                'edit_function'=>'PreBurnReview.editReviewForm'));
        } else {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Pre-Burn request.',
                'no_results_class'=>'info'));
        }

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
            "SELECT r.pre_burn_id, u.email, u.full_name, DATE_FORMAT(r.added_on, '%Y-%m-%e %l:%i %p') as added_on, r.comment
            FROM pre_burn_reviews r
            JOIN users u ON(r.added_by = u.user_id)
            WHERE r.pre_burn_review_id = $review_id;"
        );

        $pre_burn = fetch_row(
            "SELECT p.year, b.project_name, b.project_number
            FROM pre_burns p
            JOIN burn_projects b ON(p.burn_project_id = b.burn_project_id)
            WHERE pre_burn_id = ".$review['pre_burn_id'].";"
        );

        $html = "<div>
            <p><strong>".$review['full_name'].": </strong>".$review['comment']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$review['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$review['email']."?subject=Pre-Burn Review - ".$pre_burn['year']." - ".$pre_burn['burn_number']." ".$pre_burn['burn_name']."\" role=\"button\">Email Reviewer</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    protected function getConditions($pre_burn_id)
    {
        /**
         *  Constructs the reviews display div & table for a given burn plan.
         */

        $admin = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system_admin'), 'write');

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    <h4>Notes and Conditions</h4>
                    <hr>";

        $sql = "SELECT pre_burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"PreBurn.conditionDetail(', c.pre_burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
        FROM pre_burn_conditions c
        JOIN users u ON (c.added_by = u.user_id)
        WHERE pre_burn_id = $pre_burn_id";

        if ($admin['any']) {
            $sql = "SELECT pre_burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"PreBurn.conditionDetail(', c.pre_burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
                FROM pre_burn_conditions c
                JOIN users u ON (c.added_by = u.user_id)
                WHERE pre_burn_id = $pre_burn_id";

            $table = show(array('sql'=>$sql,'include_edit'=>true,'edit_function'=>'PreBurnReview.conditionEdit',
                'table'=>'pre_burn_conditions','pkey'=>'pre_burn_condition_id','include_delete'=>false,
                'no_results_message'=>'There are currently no conditions associated with this Pre-Burn request.',
                'no_results_class'=>'info'));
        } else {
            $sql = "SELECT CONCAT('<a style=\"cursor: pointer\" onclick=\"PreBurn.conditionDetail(', c.pre_burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
                FROM pre_burn_conditions c
                JOIN users u ON (c.added_by = u.user_id)
                WHERE pre_burn_id = $pre_burn_id";

            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no conditions associated with this Pre-Burn request.',
                'no_results_class'=>'info'));
        }

        //$table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no notes/conditions associated with this Pre-Burn request.',
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
            "SELECT c.pre_burn_id, u.email, u.full_name, DATE_FORMAT(c.added_on, '%Y-%m-%e %l:%i %p') as added_on, c.comment
            FROM pre_burn_conditions c
            JOIN users u ON(c.added_by = u.user_id)
            WHERE c.pre_burn_condition_id = $condition_id;"
        );

        $pre_burn = fetch_row(
            "SELECT d.year, b.project_name, b.project_number
            FROM pre_burns d
            JOIN burn_projects b ON(d.burn_project_id = b.burn_project_id)
            WHERE pre_burn_id = ".$condition['pre_burn_id'].";"
        );

        $html = "<div>
            <p><strong>".$condition['full_name'].": </strong>".$condition['comment']."</p>
            <p><strong></strong><h4><span class=\"label label-danger\"></span></h4></p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$condition['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$condition['email']."?subject=Pre-Burn Review - ".$pre_burn['year']." - ".$pre_burn['project_name']." ".$pre_burn['project_number']."\" role=\"button\">Email Approver</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    protected function getUploads($pre_burn_id)
    {
        /**
         *  Constructs the uploads HTML block.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));

        $uploads = fetch_assoc(
            "SELECT f.*
            FROM pre_burn_files b
            JOIN files f ON (b.file_id = f.file_id)
            WHERE b.pre_burn_id = $pre_burn_id
            ORDER BY added_on;"
        );

        if ($permissions['write']['any']) {
            $toolbar = "<div class=\"btn-group pull-right\">
                    <button onclick=\"Uploader.form('pre_burns',$pre_burn_id)\" class=\"btn btn-sm btn-default\">Upload</button>
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
            $html .= status_message("There are currently no uploads associated with this burn plan.", "info");
        }

        $html .= "</div>";

        return $html;
    }

    private function checkPreBurnPermissions($user_id, $pre_burn_id, $permissions)
    {
        /**
         *  Return what the user can do with this burn project. (read, write)
         */

        $read = false;
        $write = false;

        $burn = fetch_row("SELECT added_by, district_id, agency_id FROM pre_burns WHERE pre_burn_id = ?", $pre_burn_id);
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

        $allow = (!$write && !$read) ? false : true ;

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
        'pre_burn_id'=>array('display'=>false,'title'=>'Pre-Burn Id'),
        'burn_project_id'=>array('display'=>false,'title'=>'Burn Project Id'),
        'location'=>array('display'=>false,'title'=>'Location'),
        'year'=>array('display'=>true,'title'=>'Active Year'),
        'acres'=>array('display'=>true,'title'=>'This Year\'s Acres'),
        'manager_name'=>array('display'=>true,'title'=>'Manager Name'),
        'manager_number'=>array('display'=>true,'title'=>'Manager Number'),
        'manager_cell'=>array('display'=>true,'title'=>'Manager Cell'),
        'burn_objectives'=>array('display'=>true,'title'=>'Burn Objectives','multiselect'=>true,'stable'=>'pre_burn_objective_presets','mmtable'=>'pre_burn_objectives','pcol'=>'pre_burn_id','scol'=>'pre_burn_objective_preset_id','sdisplay'=>'name','label_class'=>'label-minimum'),
        'receptors'=>array('display'=>false,'title'=>'Sensitive Receptors'),
        'psm_label'=>array('display'=>false,'title'=>'Planned Smoke Mitigation Method(s)'),
        'avoidance'=>array('display'=>true,'title'=>'Planned Smoke Mitigation Method - Avoidance','boolean'=>true),
        'dilution'=>array('display'=>true,'title'=>'Planned Smoke Mitigation Method - Dilution','boolean'=>true),
        'primary_ert_id'=>array('display'=>true,'title'=>'Primary Emission Reduction Technique (ERT)','sql'=>'SELECT name FROM emission_reduction_techniques WHERE emission_reduction_technique_id = '),
        'alternate_primary_ert'=>array('display'=>true,'title'=>'Alternate Primary Emission Reduction Technique (if Other)'),
        'primary_ert_pct'=>array('display'=>true,'title'=>'Primary Emission Reduction Technique Percentage'),
        'secondary_ert_id'=>array('display'=>true,'title'=>'Secondary Emission Reduction Technique (ERT)','sql'=>'SELECT name FROM emission_reduction_techniques WHERE emission_reduction_technique_id = '),
        'alternate_secondary_ert'=>array('display'=>true,'title'=>'Alternate Secondary Emission Reduction Technique (if Other)'),
        'dispersion_model_id'=>array('display'=>true,'title'=>'Smoke Dispersion Model','sql'=>'SELECT name FROM dispersion_models WHERE dispersion_model_id = '),
        'alternate_dispersion_model'=>array('display'=>true,'title'=>'Alternate Dispersion Model (if Other)'),
        'pm_min'=>array('display'=>true,'title'=>'Particulate Matter (PM) - Minimum (Tons)'),
        'pm_max'=>array('display'=>true,'title'=>'Particulate Matter (PM) - Maximum (Tons)'),
        'day_iso'=>array('display'=>false,'title'=>'Daylight Wind Flow'),
        'night_iso'=>array('display'=>false,'title'=>'Nighttime Wind Flow')
    );

}
