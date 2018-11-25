<?php

namespace Manager;

// include("project.php");

class Burn
{

    private $min_user_level = 2;
    private $db_table_id = 10;

    private $day_iso_color = "#FFF500";
    private $night_iso_color = "#0040FF";

    private $del_status_id = array(1,3);
    private $edit_status_id = array(1,2,3);

    public $draft_id = 1;
    public $under_review_id = 2;
    public $revision_requested_id = 3;
    public $pending_approval_id = 4;
    public $approved_id = 5;
    public $disapproved_id = 6;

    // Status info array
    protected $status = array(
        'draft'=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'101','class'=>'warning'),
        'under_review'=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'102','zindex'=>'102','class'=>'warning'),
        'revision_requested'=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'103','class'=>'danger'),
        'pending_approval'=>array('id'=>4,'title'=>'Pending Approval','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'102','zindex'=>'102','class'=>'warning'),
        'approved'=>array('id'=>5,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'105','class'=>'success'),
        'disapproved'=>array('id'=>6,'title'=>'Disapproved','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'106','class'=>'danger')
    );

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->burn_form_id = 'burn_form';
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
            $cond = "WHERE b.burn_project_id = $burn_project_id";
        }

        if ($permissions['write']['admin']) {
            // No agency requirement for admin.
            $pre_cond = "";
        } else {
            $pre_cond = "WHERE b.agency_id ".$agency;
        }

        if ($type == 'edit') {
            if ($permissions['write']['admin']) {
                $cond = "";
            } elseif ($permissions['write']['user_agency']) {
                $cond = "AND b.agency_id ".$agency;
            } elseif ($permissions['write']['user_district']) {
                $cond = "AND b.district_id ".$districts;
            } elseif ($permissions['write']['user']) {
                $cond = "AND b.added_by = $user_id
                    AND b.district_id ".$districts;
            }
        } elseif ($type == 'view') {
            if ($permissions['read']['user_agency']) {
                $cond = "AND b.agency_id ".$agency;
            } elseif ($permissions['read']['user'] || $permissions['read']['user_district']) {
                $cond = "AND b.added_by != $user_id
                    AND b.district_id ".$districts;
            }
        }

        $new_function = "Burn.newForm($burn_project_id)";

        $sql = "SELECT b.burn_id, b.start_date as \"Start Date\", b.end_date as \"End Date\",
          a.agency as \"Agency\", CONCAT('<a href=\"project.php?detail=true&id=', b.burn_project_id ,'\">', bp.project_name, '</a>') as \"Burn Project Name\",
          b.manager_name as \"Manager\",  b.manager_number as \"Manager Number\",
          CONCAT('<span class=\"', s.class ,'\" onclick=\"Burn.submitForm(', b.burn_id, ')\">', s.name ,'</span>') as \"Form Status\",
          COALESCE(CONCAT('<span class=\"', c.class ,'\" data-toggle=\"tooltip\" title=\"Click to Check\" onclick=\"Burn.validate(', b.burn_id , ')\">', c.name ,'</span>'),'<span class=\"label label-default\">N/A</span>')  as \"Form Completeness\",
          CONCAT('<span class=\"label label-default\">', u.full_name, '</span>') as \"Added By\"
          FROM burns b
          JOIN pre_burns pb ON(b.pre_burn_id = pb.pre_burn_id)
          JOIN burn_projects bp ON(b.burn_project_id = bp.burn_project_id)
          JOIN agencies a ON (b.agency_id = a.agency_id)
          LEFT JOIN burn_statuses s ON (b.status_id = s.status_id)
          LEFT JOIN burn_completeness c ON(b.completeness_id = c.completeness_id)
          JOIN users u ON (b.added_by = u.user_id)
          $pre_cond
          $cond
          ORDER BY b.added_on";

        if ($type == 'edit' && $permissions['write']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'burns','pkey'=>'burn_id'
                ,'include_delete'=>true,'delete_function'=>'Burn.deleteConfirmation'
                ,'include_view'=>true,'view_href'=>'?burn=true&id=@@'
                ,'edit_function'=>'Burn.editConfirmation','new_function'=>$new_function
                ,'no_results_message'=>'There are no editable burn requests associated with your user. An approved pre-burn request is required to submit a burn request.'
                ,'no_results_class'=>'info'));
        } elseif ($type == 'view' && $permissions['read']['any']) {
            $table = show(array('sql'=>$sql,'paginate'=>true,'table'=>'burns','pkey'=>'burn_id','include_edit'=>false
                ,'include_delete'=>false
                ,'no_results_message'=>'There are no viewable burn requests associated with your district(s).'
                ,'no_results_class'=>'info'));
        }

        $html = $table['html'];

        return $html;
    }

    public function form($page, $burn_id = null, $pre_burn_id = null)
    {
        /**
         *  Constructs the Burn form.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        // Defaults
        $sm_numbers = array(1,2,3,4,5,6,7,8,9,10,11);

        // If a Burn is specified. Use its values (Edit/Update scenario).
        // This will extract a $burn_project_id as well (running on the next if)!
        // Extract some default values from the burn plan.
        if (isset($pre_burn_id)) {
            $temp_pre_burn = new \Manager\PreBurn($this->db);
            $pre_burn = $temp_pre_burn->get($pre_burn_id);
            $burn_project_id = $pre_burn['burn_project_id'];
            $location = $pre_burn['location'];
            $district_id = $pre_burn['district_id'];
            $manager_name = $pre_burn['manager_name'];
            $manager_number = $pre_burn['manager_number'];
            $manager_cell = $pre_burn['manager_cell'];
        }

        if (isset($burn_id)) {
            $burn = $this->get($burn_id);
            extract($burn);
        }

        if ($status_id > 5) {
            return status_message("The Burn Request has a status that prevents it from being edited", "info");
        }

        if ($page == 1) {
            $title = "Form 4: Burn Request <small>1/2</small>";

            $fieldset_id = $this->burn_form_id . "_fs1";

            $ctls = array(
                'burn_project_id'=>array('type'=>'hidden2','value'=>$burn_project_id),
                'pre_burn_id'=>array('type'=>'hidden2','value'=>$pre_burn_id),
                'district_id'=>array('type'=>'hidden2','value'=>$district_id),
                'location'=>array('type'=>'marker','label'=>'Location (Defaults to Pre-Burn)','value'=>$location,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_name'=>array('type'=>'text','label'=>'Burn Manager Name','value'=>$manager_name,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_number'=>array('type'=>'text','label'=>'Burn Manager Number','value'=>$manager_number,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_cell'=>array('type'=>'text','label'=>'Burn Manager Cell','value'=>$manager_cell,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'airshed_id'=>array('type'=>'combobox','label'=>'Airshed','table'=>'airsheds','fcol'=>'airshed_id','display'=>'name','value'=>$airshed_id,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'modify_id'=>array('type'=>'combobox','allow_null'=>true,'value'=>$modify_id,'table'=>'burn_modifications','fcol'=>'modification_id','display'=>'description','label'=>'Modified Because (if Modified)','enable_help'=>true,'table_id'=>$this->db_table_id),
            );
        } elseif ($page == 2) {
            $title = "Form 4: Burn Request <small>2/2</small>";

            $fieldset_id = $this->burn_form_id . "_fs2";

            $ctls = array(
                'request_acres'=>array('type'=>'text','label'=>'Burn Acres Requested','value'=>$request_acres,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'start_date'=>array('type'=>'date','label'=>'Multi-Day Burn - Start Date','value'=>$start_date,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'end_date'=>array('type'=>'date','label'=>'Multi-Day Burn - End Date','value'=>$end_date,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'daily_acres'=>array('type'=>'text','label'=>'Estimated Daily Acres','value'=>$daily_acres,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'comments'=>array('type'=>'memo','label'=>'Comments','value'=>$comments,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'pm_sampler_model'=>array('type'=>'text','label'=>'Particulate Sampler Model Number (if Used)','value'=>$pm_sampler_model,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'pm_sampler_id'=>array('type'=>'text','label'=>'Particulate Sampler Identification Number (if Used)','value'=>$pm_sampler_id,'enable_help'=>true,'table_id'=>$this->db_table_id)
            );

            $append = "<style>
                .date-input-error {
                    color: #EC4B4B;
                    border: 2px dotted #EC4B4B;
                }
                .date-overlap-error {
                    color: #EC4B4B;
                    border: 2px dotted #EC4B4B;
                }
                .date-error-icon {
                    color: #EC4B4B;
                    position: absolute;
                    top: 6px;
                    left: 76%;
                }
            </style>
            <script>
                $('[name=\"my[start_date]\"]').change(function () {
                    checkDateError();
                });
                $('[name=\"my[end_date]\"]').change(function () {
                    checkDateError();
                });
                function checkDateError() {
                    // This method uses UTC (dates do not reflect timezone);
                    var pre_burn_id = parseInt($('[name=\"my[pre_burn_id]\"]').val());
                    var ms_7days = 604800000;
                    var start_date = $('[name=\"my[start_date]\"]').val();
                    var end_date = $('[name=\"my[end_date]\"]').val();
                    var interval = new Date(end_date) - new Date(start_date);

                    Burn.checkOverlap(pre_burn_id, start_date, end_date);

                    var intervalError = false;
                    if (interval > ms_7days) {
                        intervalError = true;
                    }
                    toggleDateError(intervalError);
                }
                function toggleDateError(exceedance) {
                  if (exceedance) {
                    $('[name=\"my[start_date]\"]').addClass('date-input-error');
                    $('[name=\"my[end_date]\"]').addClass('date-input-error');
                    $('[name=\"my[start_date]\"]').parent().append('<span class=\"date-length-error-message date-error-icon\"><span style=\"font-size: 11px;\">Longer than 7 Days <i style=\"margin-left: 2px;\" class=\"glyphicon glyphicon-warning-sign\"></i></span>');
                    $('[name=\"my[end_date]\"]').parent().append('<span class=\"date-length-error-message date-error-icon\"><span style=\"font-size: 11px;\">Longer than 7 Days <i style=\"margin-left: 2px;\" class=\"glyphicon glyphicon-warning-sign\"></i></span>');
                    return;
                  }
                  $('[name=\"my[start_date]\"]').removeClass('date-input-error');
                  $('[name=\"my[end_date]\"]').removeClass('date-input-error');
                  $('.date-length-error-message').remove();
                  return;
                }
            </script>";
        }

        if ($page == 1) {
            $html .= mkForm(array('id'=>$this->burn_form_id,'controls'=>$ctls,'title'=>$title,'suppress_submit'=>true,'fieldset_id'=>$fieldset_id));
        } else {
            $html .= mkFieldset(array('controls'=>$ctls,'title'=>$title,'id'=>$fieldset_id,'append'=>$append));
        }

        return $html;
    }

    public function receptorForm($origin)
    {

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        //$title = "Burn Request <small>1/3</small>";

        global $map_center;

        $targetId = 'receptors_pad';

        $ctls = array(
            'name'=>array('type'=>'text','label'=>'Receptor Name','value'=>$name),
            'receptor_location'=>array('type'=>'marker','label'=>'Location','input_type'=>'dist-deg','origin'=>$origin),
        );

        $html .= mkForm(array('theme'=>'modal','id'=>'form-receptor','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"Burn.addReceptor('{$targetId}')\">Add Receptor</button>
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

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency','admin'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        if ($permissions['admin']) {
            $cond = "";
        } elseif ($permissions['user_agency']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)";
        } elseif ($permissions['user_district']) {
            $cond = "WHERE b.agency_id IN(SELECT agency_id FROM users WHERE user_id = $user_id)
                AND b.district_id ".$districts;
        } elseif ($permissions['user']) {
            $cond = "WHERE b.added_by = $user_id
                AND b.district_id ".$districts;
        }

        // $cont .= 'AND b.status_id = 4';

        // Select burn_projects from those agencies with approved status and no active burn project.
        $select = fetch_assoc(
            "SELECT b.burn_project_id, project_name, project_number
            FROM burn_projects b
            $cond;");

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
                This agency has no approved burn projects with an associated approved pre-burn plan. Burns requests can only be drafted for approved burn projects with approved pre-burns.
                </div>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";

            foreach ($select as $value) {
                $small_font = "";
                if (strlen($value['project_name']) >= 24) {
                    $small_font = "style=\"font-size: 10px;\"";
                }
                $html .= "<button class=\"btn btn-default btn-block\" $small_font onclick=\"Burn.newForm(".$value['burn_project_id'].")\">".$value['project_name']." - ".$value['project_number']."</button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function preBurnSelector($user_id, $burn_project_id)
    {
        /**
         *  Construct a button list of valid burn plans for Burn submittal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        // //$pb_cond = "AND pb.status_id = ?";
        // $pb_status_id = 4;
        //
        // // Select burn_projects and pre-burns from the user agency with approved status.
        // $select = fetch_assoc(
        //     "SELECT MAX(pb.pre_burn_id) as pre_burn_id, MAX(pb.year) as year, bp.burn_project_id, bp.project_name, bp.project_number
        //     FROM pre_burns pb
        //     JOIN burn_projects bp ON(pb.burn_project_id = bp.burn_project_id)
        //     WHERE pb.burn_project_id = ?
        //     $pb_cond
        //     GROUP BY pb.burn_project_id;",
        //     array($burn_project_id)
        // );

        $select = fetch_assoc(
          "SELECT pre_burn_id, pb.year, pb.burn_project_id, bp.project_name, bp.project_number
          FROM pre_burns pb
          JOIN burn_projects bp ON(pb.burn_project_id = bp.burn_project_id)
          WHERE year IN(
            SELECT MAX(year)
            FROM pre_burns
            WHERE burn_project_id = ?
          )
          AND pb.burn_project_id = ?",
          array($burn_project_id, $burn_project_id)
        );

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
                This agency has no approved and active pre-burn plans. Burn requests can only be drafted for active and approved pre-burn plans.
                </div>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";

            foreach ($select as $value) {
                $small_font = "style=\"font-size: 12px;\"";
                $html .= "<button class=\"btn btn-default btn-block\" $small_font onclick=\"Burn.newForm(".$value['burn_project_id'].", ".$value['pre_burn_id'].")\">".$value['project_number']." - Pre-Burn Active For: <strong>".$value['year']."</strong></button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function ownerChangeForm($burn_id)
    {
        /**
         *  Change Ownership Form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT COALESCE(submitted_by, updated_by, added_by) as user_id, agency_id FROM burns WHERE burn_id = ?", $burn_id);
        $user_sql = "SELECT user_id, email, full_name FROM users;";
        $district_sql = "SELECT district_id, CONCAT(identifier, ' - ', district) as name FROM districts;";

        $ctls = array(
            'user_id'=>array('type'=>'combobox','label'=>'New Burn Owner','fcol'=>'user_id','display'=>'email','sql'=>$user_sql,'value'=>$burn['user_id']),
            'district_id'=>array('type'=>'combobox','label'=>'New Designation','fcol'=>'district_id','display'=>'name','sql'=>$district_sql,'value'=>$burn['district_id'])
        );

        $html = mkForm(array('theme'=>'modal','id'=>'owner-change-form','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
            <div class=\"btn-group\">
              <button class=\"btn btn-default\" onclick=\"Burn.ownerChange({$burn_id})\">Change Owner</button>
            </div>
            <div class=\"btn-group\">
              <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Cancel</button>
            </div>
          </div>";

        return $html;
    }

    public function ownerChange($burn_id, $user_id, $district_id)
    {
        /**
         *  Change The Burn Owner
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $status_id = $this->getStatus($burn_id);
        $agency_id = fetch_one("SELECT agency_id FROM users WHERE user_id = ?", $user_id);

        if ($status_id['status_id'] >= $this->approved_id) {
            $change = $this->pdo->prepare("UPDATE burns SET added_by = ?, updated_by = ?, submitted_by = ?, agency_id = ?, district_id = ? WHERE burn_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $user_id, $agency_id, $district_id, $burn_id));
        } else {
            $change = $this->pdo->prepare("UPDATE burns SET added_by = ?, updated_by = ?, agency_id = ?, district_id = ? WHERE burn_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $agency_id, $district_id, $burn_id));
        }

        if ($change->rowCount() > 0) {
            $html = status_message("The burn owner has successfully been changed.", "success");
        } else {
            $html = status_message("The burn owner change was not successful.", "error");
        }

        return null;
    }

    public function submittalForm($burn_id)
    {
        /**
         *  Creates the html block to change a burn plans status.
         *  E.g.: Draft, Submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $burn = fetch_row("SELECT status_id, burn_project_id, pre_burn_id FROM burns WHERE burn_id = ?;", $burn_id);
        $pre_burn = fetch_row("SELECT status_id FROM pre_burns WHERE pre_burn_id = ?", $burn['pre_burn_id']);
        $burn_project = fetch_row("SELECT status_id FROM burn_projects WHERE burn_project_id = ?", $burn['burn_project_id']);

        $validate = $this->validateRequired($burn_id);
        $valid = $validate['valid'];

        if ($burn_project['status_id'] < 4) {
            $html = "<div>
                <p class=\"text-center\">The Burn Project must be approved before this Burn Request can be submitted for approval.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($pre_burn['status_id'] < 4) {
            $html = "<div>
                <p class=\"text-center\">The Pre-Burn must be approved before this Burn Request can be submitted for approval.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] >= $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn request was already processed.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->pending_approval_id) {
            $html = "<div>
                <p class=\"text-center\">The burn request is now pending final review. Please check back for final approval or disapproval.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->revision_requested_id) {
            if ($valid) {
                if ($this->reviewCheck($burn_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The burn request is valid and has been revised since the last request for revision. To ensure minimal processing time, please make sure the revision addresses all review comments before re-submitting to Utah.gov.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-success btn-block\" onclick=\"Burn.submitToUtah($burn_id)\">Re-submit <strong>$burn_name</strong> to Utah.gov</button>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The burn request has not been revised since the last request for revision. Please revise the burn according to latest review comment.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            } else {
                if ($this->reviewCheck($burn_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The burn request has been revised since the last request for revision but is not valid.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The burn request has not been revised since the last request for revision and is not valid.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            }
        } elseif ($burn['status_id'] == $this->under_review_id) {
            $html = "<div>
                    <p class=\"text-center\">The burn request is currently being reviewed by Utah.gov. Please check back for any requested revisions, or the plans approval.</p>
                    <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            if ($valid) {
                $html = "<div>
                    <p class=\"text-center\">The draft is completed and can be submitted to Utah.gov.</p>
                    <button class=\"btn btn-success btn-block\" onclick=\"Burn.submitToUtah($burn_id)\">Submit <strong>$burn_name</strong> to Utah.gov</button>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            } else {
                $html = "<div>
                        <p class=\"text-center\">The burn request is not completed. Please ensure all required fields are filled in.</p>
                        <a href=\"?burn=true&id=$burn_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            }
        }

        return $html;
    }

    public function submitUtah($burn_id)
    {
        /**
         *  Determine if the burn is valid, and change it to submitted/pending.
         *  Add a valid burn number when submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $valid = $this->validateRequired($burn_id);
        $valid = $valid['valid'];
        $now = now();
        $submitted_by = $_SESSION['user']['id'];

        // Check if its submitted already
        if ($valid == true) {
            $status = $this->getStatus($burn_id);
            $status_id = $status['status_id'];
        }

        // Not submitted, and valid. Submit to Utah.gov:
        if ($valid == true && in_array($status_id, array($this->draft_id,$this->revision_requested_id))) {
            // The burn plan is valid. Change its status to "Under Review"
            $last_submitted_by = fetch_one("SELECT submitted_by FROM burns WHERE burn_id = ?;", $burn_id);
            if(!empty($last_submitted_by)) {
                $submitted_by = $last_submitted_by;
            }
            $update_sql = $this->pdo->prepare("UPDATE burns SET status_id = ?, submitted_on = ?, submitted_by = ? WHERE burn_id = ?;");
            $update_sql->execute(array($this->under_review_id, $now, $submitted_by, $burn_id));
            if ($update_sql->rowCount() > 0) {
                $result['message'] = status_message("The Burn Request has been submitted to Utah.gov.", "success");

                $notify = new \Info\Notify($this->db);
                $notify->burnSubmitted($burn_id);
            } else {
                $result['message'] = status_message("The Burn Request is valid, but failed to submit.", "error");
            }
        } elseif (in_array($status_id, array($this->under_review_id, $this->pending_approval_id, $this->approved_id, $this->disapproved_id))) {
            $result['message'] = status_message("The Burn Request was already submitted.", "warning");
        } else {
            $result['message'] = status_message("The Burn Request must be Validated before submitting.", "error");
        }

        return $result;
    }

    public function editApproved($burn_id)
    {
        /**
         *  Produce the edit confirmation HTML for previously approved plans. Forwards to toDraft($burn_project_id).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT b.status_id, a.status_id as accomplishment_status_id
            FROM burns b
            LEFT JOIN (
                SELECT burn_id, accomplishment_id, status_id
                FROM accomplishments
            ) a ON(a.burn_id = b.burn_id)
            WHERE b.burn_id = ?", array($burn_id));

        if (isset($burn['accomplishment_status_id'])) {
            $html = "<div>
                <p class=\"text-center\">An accomplishment has already been saved for this burn indicating the burn has taken place.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn request has recieved final approval.Please note the draft project must be re-submitted to Utah.gov for approval.</p>
                <button class=\"btn btn-warning btn-block\" onclick=\"Burn.toDraft($burn_id)\">Change to Draft</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">The burn request is pending approval and must be changed to a draft before editing. Please note the draft project must be re-submitted to Utah.gov for approval.</p>
                <button class=\"btn btn-warning btn-block\" onclick=\"Burn.toDraft($burn_id)\">Change to Draft</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        }

        return $html;
    }

    public function toDraft($burn_id)
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

        $to_draft = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?;");
        $to_draft->execute(array($this->draft_id, $burn_id));

        if ($to_draft->rowCount() > 0) {
            $result['message'] = status_message("The burn request was converted to draft.", "success");

            if ($now > $threshold) {
                //$notify = new \Info\Notify($this->db);
                //$notify->lateAnnual($burn_project_id);
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn request was not successfully converted to draft", "success");
        }

        return $result;
    }

    public function toolbar($page, $burn_id)
    {
        /**
         *   Produces the standard burn plan form toolbar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        if (isset($burn_id)) {
            $status_id = fetch_one("SELECT status_id FROM burns WHERE burn_id = ?;", $burn_id);
        }

        if ($status_id >= $this->approved_id) {
            return "";
        }

        $toolbar_class = $this->burn_form_id . "_tb";
        $btn_class = "btn-sm btn-default";

        if (isset($burn_id)) {
            $c_burn_id = ", $burn_id";
            $save_function = "Burn.update($burn_id)";
        } else {
            $save_function = "Burn.save()";
        }

        if ($page == 1) {
            $html = "<div class=\"$toolbar_class pull-right btn-group\">
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"Burn.showForm(2$c_burn_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>
                <!-- <button class=\"btn $btn_class\" onclick=\"Burn.submitUtah($burn_id)\">Submit</button> -->
            </div>";
        } elseif ($page == 2) {
            $html = "<button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"Burn.showForm(1$c_burn_id)\">Back</button>
                <!-- <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Forward</button> -->
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>
                <button class=\"btn $btn_class\" onclick=\"Burn.submitForm($burn_id)\">Submit</button>"
                ;
        }

        return $html;
    }

    public function save($burn)
    {
        /**
         *  Save a Burn request.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $added_by = $_SESSION['user']['id'];
        $added_on = now();
        $agency_id = $_SESSION['user']['agency_id'];
        $status_id = $this->draft_id;

        extract(prepare_values($burn));

        $insert_sql = $this->pdo->prepare(
            "INSERT INTO burns (burn_project_id, pre_burn_id, agency_id, district_id, added_by, added_on, location, manager_name, manager_number, manager_cell, airshed_id, modify_id, request_acres, start_date, end_date, daily_acres, comments, pm_sampler_model, pm_sampler_id, status_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_sql = execute_bound($insert_sql, array($burn_project_id, $pre_burn_id, $agency_id, $district_id, $added_by, $added_on, $location, $manager_name, $manager_number, $manager_cell, $airshed_id, $modify_id, $request_acres, $start_date, $end_date, $daily_acres, $comments, $pm_sampler_model, $pm_sampler_id, $status_id));

        if ($insert_sql->rowCount() > 0) {
            $success_message .= "The Burn Request was saved. ";
        } else {
            $result['error'] = true;
            $error_message .= "The Burn Request could not be saved. ";
        }

        $burn_id = fetch_one("SELECT burn_id FROM burns WHERE pre_burn_id = ? AND added_by = ? AND added_on = ?;", array($pre_burn_id, $added_by, $added_on));

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        $this->validateRequired($burn_id);

        return $result;
    }

    public function update($burn, $burn_id)
    {
        /**
         *  Update the Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        // Extract the old values
        $original = $this->get($burn_id);
        extract($original);

        // Overwrite variables with updated.
        extract(prepare_values($burn));

        // Update the Burn.
        $burn_sql = $this->pdo->prepare(
            "UPDATE burns SET updated_by = ?, updated_on = ?, location = ?, manager_name = ?, manager_number = ?, manager_cell = ?, airshed_id = ?, modify_id = ?, request_acres = ?, start_date = ?, end_date = ?, daily_acres = ?, comments = ?, pm_sampler_model = ?, pm_sampler_id = ? WHERE burn_id = ?;"
        );
        $burn_sql->execute(array($updated_by, $updated_on, $location, $manager_name, $manager_number, $manager_cell, $airshed_id, $modify_id, $request_acres, $start_date, $end_date, $daily_acres, $comments, $pm_sampler_model, $pm_sampler_id, $burn_id));

        //// Delete previous liners.
        //$many = $this->deleteManyMany($burn_id);

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message("The burn request was updated.", "success");
        }

        $this->validateRequired($burn_id);

        return $result;
    }

    public function checkPreBurnRevision($pre_burn_id, $revision_id)
    {
        /**
         *  Updates the associated pre-burn revision_id status. This is not used.
         */

        $return = array('success'=>false,'message'=>'Burn modification error. Pre-Burn not updated');
        $mod = fetch_row("SELECT * FROM burn_modifications WHERE modification_id = ?;", $modification_id);

        if ($mod['force_rewrite'] == true) {
            switch ($mod['modification_id']) {
                case 1:
                    // Burn has not been modified.

                    break;
                case 2:
                    // Non-smoke elements were revised.

                    break;
                case 3:
                    // Smoke elements were revised.

                    break;
                default:
                    # code...
                    break;
            }
        }

        return $return;
    }

    public function deleteConfirmation($burn_id)
    {
        /**
         *  Return the delete confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn = fetch_row("SELECT CAST(COALESCE(submitted_on, added_on) AS date) as submitted_on, start_date, end_date, burn_project_id, status_id FROM burns WHERE burn_id = ?;", $burn_id);
        $burn_project = fetch_row("SELECT project_name, project_number FROM burn_projects WHERE burn_project_id = ?;", $burn['burn_project_id']);

        if ($burn['status_id'] == $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Burn request is approved and cannot be deleted. Please contact Utah.gov if you would like to cancel this burn.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->disapproved_id) {
            $html = "<div>
                <p class=\"text-center\">The Burn request is disapproved. You may delete the burn or leave it for archiving purposes.</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"Burn.deleteRecord($burn_id)\">Delete <strong>".$burn_project['burn_number']." - ".$burn['start_date']." - ".$burn['end_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete <strong>".$burn_project['project_number']." - ".$burn_project['project_name']."</strong>?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"Burn.deleteRecord($burn_id)\">Delete <strong>".$burn_project['project_number']." - ".$burn['start_date']." - ".$burn['end_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function delete($burn_id)
    {
        /**
         *  Delete the Burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn_sql = $this->pdo->prepare("DELETE FROM burns WHERE burn_id = ?;");
        $burn_sql->execute(array($burn_id));
        if ($burn_sql->rowCount() > 0 && $many) {
            $result['message'] = status_message("The burn request was deleted.", "success");
        } elseif ($burn_sql->rowCount() > 0 && $liners == false) {
            $result['error'] = true;
            $result['message'] = status_message("The burn request was deleted, but associated liners were not!", "error");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn request was not deleted.", "error");
        }

        return $result;
    }

    public function checkOverlap()
    {
        /**
         *  Checks if any burn requests overlap with the form start_date, end_date
         */

        $args = array('pre_burn_id'=>null,'start_date'=>null,'end_date'=>null);
        extract(merge_args(func_get_args(), $args));

        $result = false;

        if (empty($end_date)) {
          $end_date = $start_date;
        }

        if (empty($start_date)) {
          $start_date = $end_date;
        }

        $overlaps = fetch_one(
          "SELECT COUNT(burn_id) as overlaps
          FROM burns
          WHERE pre_burn_id = ?
          AND (
            ? BETWEEN start_date AND end_date
            OR ? BETWEEN start_date AND end_date
          );",
          array($pre_burn_id, $start_date, $end_date)
        );

        if ($overlaps > 0) {
          $result = true;
        }

        return json_encode($result);
    }

    protected function reviewCheck($burn_id)
    {
        /**
         *  Check if the burn plan was updated since the last review.
         */

        $review_last_updated = fetch_one("SELECT MAX(last_burn_update) FROM burn_reviews WHERE burn_id = $burn_id;");
        $last_updated = fetch_one("SELECT updated_on FROM burns WHERE burn_id = $burn_id;");

        if ($last_updated > $review_last_updated) {
            return true;
        } else {
            return false;
        }
    }

    public function get($burn_id)
    {
        /**
         *  Get a Burn.
         */

        // Get the daily request info.
        $burn = fetch_row("SELECT * FROM burns WHERE burn_id = $burn_id;");
        $result = $burn;

        // Get basic burn plan info.
        $burn_project = fetch_row("SELECT burn_project_id, project_name, project_number, location FROM burn_projects WHERE burn_project_id = (SELECT burn_project_id FROM burns WHERE burn_id = ?);", $burn_id);
        $result['burn_project'] = $burn_project;

        return $result;
    }

    public function overviewPage()
    {
        /**
         *
         */

        $args = array('burn_project_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        if (isset($burn_project_id)) {
            $edit_table = $this->show(array('type'=>'edit','burn_project_id'=>$burn_project_id));
            $view_table = $this->show(array('type'=>'view','burn_project_id'=>$burn_project_id));
            $map = $this->getAllMap(array('burn_project_id'=>$burn_project_id));
            $app_burn_project_id = ', '.$burn_project_id;
            $return_link = "<a href=\"/manager/burn.php\">Return to Overview</a>";
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
                <h3>Overview <small>Form 4: Burn Requests</small></h3>
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

        if ($_SESSION['user']['level_id'] >= $this->min_user_level) {
            $html['main'] .= "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <div class=\"pull-right\">
                        <button class=\"btn btn-sm btn-default\" onclick=\"Burn.newForm($app_burn_project_id)\">New Burn Request</button>
                    </div>
                </div>
            </div>";
        }

        return $html;
    }

    public function detailPage($burn_id)
    {

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkBurnPermissions($_SESSION['user']['id'], $burn_id, $permissions);

        // Get the Burn.
        $burn = $this->get($burn_id);

        if ($burn_permissions['allow']) {
            // Construct the title.
            if (isset($burn['burn_project']['project_name']) && isset($burn['burn_project']['project_number'])) {
              $title = $burn['burn_project']['project_name']." / ".$burn['burn_project']['project_number']." / <small>".$burn['start_date']." to ".$burn['end_date']."</small>";
            } elseif (isset($burn['burn_project']['project_name'])) {
              $title = $burn['burn_project']['project_name']." / <small>".$burn['start_date']." to ".$burn['end_date']."</small>";
            } else {
              $title = "Burn Request";
            }

            // Statics
            $return_link = "<a href=\"/manager/burn.php\">Return to Overview</a>";

            if (in_array($burn['status_id'], $this->edit_status_id) && $burn_permissions['write']) {
              $submit_text = "Re-submit";
              if ($burn['status_id'] < $this->revision_requested_id) {
                $submit_text = "Submit";
              }

              $toolbar = "<div class=\"btn-group pull-right\">
                <button class=\"btn btn-sm btn-default\" onclick=\"Burn.submitForm($burn_id)\">$submit_text</button>
                <button class=\"btn btn-sm btn-default\" onclick=\"Burn.editConfirmation($burn_id)\">Edit Burn Request</button>
                <a href=\"/pdf/burn.php?id={$burn_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
              </div>";
            } else {
              $toolbar = "<div class=\"btn-group pull-right\">
                <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                <button class=\"btn btn-sm btn-default\" disabled>Edit Burn Request</button>
                <a href=\"/pdf/burn.php?id={$burn_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
              </div>";
            }

            // Get HTML blocks.
            $status = $this->getStatusLabel($burn_id);
            $map = $this->getMap($burn_id);
            $table = $this->tablifyFields($burn_id);
            $sidebar = $toolbar;
            $sidebar .= $this->getContacts($burn_id);
            $sidebar .= $this->getConditions($burn_id);
            $sidebar .= $this->getReviews($burn_id);
            $sidebar .= $this->getUploads($burn_id);

            // Construct the HTML array.
            $html['header'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <span class=\"pull-right\">
                        $return_link
                        $status
                    </span>
                    <h3>".$title."</h3>
                </div>
            </div>";

            $html['main'] = "<div class=\"row\">
                <div class=\"col-sm-8\">
                    <h4>Form 4: Burn Request Info</h4>
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

    public function pdfPage($burn_id)
    {

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkBurnPermissions($_SESSION['user']['id'], $burn_id, $permissions);

        if ($burn_permissions['allow']) {
            // Get the Burn.
            $burn = $this->get($burn_id);

            // Get HTML blocks
            $table = $this->tablifyFields($burn_id, true);
            $contacts = $this->getContacts($burn_id);
            $conditions = $this->getConditions($burn_id);
            $reviews = $this->getReviews($burn_id);

            // Static fields.
            $project_name = $burn['burn_project']['project_name'];
            $project_number = $burn['burn_project']['project_number'];

            // Build the map.
            $location = str_replace(array('(',')',' '), '', $burn['location']);
            $color = str_replace('#', '0x', $this->retrieveStatus($burn['status_id'])['color']);
            $label = substr($this->retrieveStatus($burn['status_id'])['title'], 0, 1);
            $static_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=14&center=$location&markers=color:$color%7Clabel:$label%7C$location";

            // Get HTML blocks.
            $html = "
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"50%\">
                        <col width=\"49%\">
                        <tr style=\"border: 0.15em solid black;\">
                            <td style=\"width: 50%\">Form 4: Burn - Active: <strong>{$burn['start_date']} - {$burn['end_date']}</strong></td>
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

    public function getStatusLabel($burn_id)
    {
        $status = fetch_row(
            "SELECT description, class, name FROM burn_statuses
            WHERE status_id IN (SELECT status_id FROM burns WHERE burn_id = $burn_id);"
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

    public function tablifyFields($burn_id, $pdf = false)
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

        $burn = $this->get($burn_id);

        $title = "Burn Information";
        $value_array = $burn;
        $fields_array = array(
          'airshed_id','modified','modify_id','request_acres',
          'start_date','end_date','daily_acres','comments','pm_sampler_model',
          'pm_sampler_id','expired');

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
            $html .= "<tr><td $i_style>".$reference['title']."</td><td $i_style>".$value."</td><td>".$help."</td></tr>";
          }
        }

        $html .= "</tbody>
          </table>";

        return $html;
    }

    public function validateRequired($burn_id)
    {
        /**
         *  Validates a saved burn for required fields.
         */

        // The missing value count.
        $count = 0;
        $missing_msg = "The following required fields are missing in this Burn Request:<br><br>";

        // Get the burn.
        $burn = $this->get($burn_id);

        // Check the base values.
        $base_required = array('location'=>'Location',
            'manager_name'=>'Manager Name','manager_number'=>'Manager Number','manager_cell'=>'Manager Cell',
            'airshed_id'=>'Airshed','modify_id'=>'Burn Modified','request_acres'=>'Requested Acres',
            'start_date'=>'Start Date','end_date'=>'End Date'
        );

        foreach ($base_required as $key => $value) {
            if (is_null($burn[$key])) {
                $count++;
                $missing_msg .= "No ".$value."<br>";
            }
        }

        // Update the burn plan with its completeness status.
        $update_sql = $this->pdo->prepare("UPDATE burns SET completeness_id = ? WHERE burn_id = ?");

        if ($count == 0) {
          // Update to valid. No missing was counted.
          $update_sql->execute(array(2, $burn_id));
          $result['valid'] = true;
          $result['message'] = modal_message("All required Burn Request info is filled out.", "success").
          "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
          $update_sql->execute(array(1, $burn_id));
          $result['valid'] = false;
          $result['message'] = modal_message($missing_msg, "error").
          "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        }

        return $result;
    }

    public function getStatus($burn_id)
    {
        /**
         *  Get the status id of the burn.
         */

        $result = array(
          'allow_delete'=>false,
          'allow_edit'=>false
        );
        $result['status_id'] = fetch_one("SELECT status_id FROM burns WHERE burn_id = ?;", $burn_id);

        if (in_array($result['status_id'], $this->del_status_id)) {
            $result['allow_delete'] = true;
        }

        if (in_array($result['status_id'], $this->edit_status_id)) {
            $result['allow_edit'] = true;
        }

        return $result;
    }

    public function getMap($burn_id)
    {
        /**
         *  Builds a boundary & marker map for a single Burn.
         */

        $zoom_to_fit = true;
        $control_title = "Zoom to Burn Request";

        $burn = $this->get($burn_id);
        $marker = $burn['location'];
        $day_iso = json_decode($burn['day_iso'], true);
        $night_iso = json_decode($burn['night_iso'], true);
        $burn_status = $this->retrieveStatus($burn['status_id']);

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
            $markers = fetch_assoc("SELECT b.burn_id, b.status_id, b.location, CONCAT(bp.project_number, ': ', b.start_date, ' to ', b.end_date) as name, b.added_by FROM burns b JOIN burn_projects bp ON(b.burn_project_id = bp.burn_project_id) WHERE b.burn_project_id IN(SELECT burn_project_id FROM burn_projects WHERE agency_id = $agency_id);");
            $zoom_to_fit = false;
        } elseif (isset($burn_project_id)) {
            $burn = fetch_row("SELECT burn_project_id, status_id, location FROM burn_projects WHERE burn_project_id = $burn_project_id;");
            $markers = fetch_assoc("SELECT burn_id, status_id, location, added_by FROM burns WHERE burn_project_id = $burn_project_id;");
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
                            window.location='/manager/burn.php?burn=true&id='+marker.id;return false;
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

    protected function getContacts($burn_id)
    {
        /**
         *  Constructs the contacts display div for a Burn.
         */

        $submitter = fetch_row(
            "SELECT u.full_name, u.email, u.phone, a.agency
            FROM burns db
            JOIN users u ON(db.submitted_by = u.user_id)
            JOIN agencies a ON(u.agency_id = a.agency_id)
            WHERE db.burn_id = ?
            "
        , $burn_id);

        $contact = fetch_row("SELECT manager_name, manager_number FROM burns WHERE burn_id = ?", $burn_id);

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
                            <p>Burn Manager</p>
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

    protected function getReviews($burn_id)
    {
        /**
         *  Constructs the reviews display div & table for a given burn plan.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency','admin','admin_final','system'));

        if ($full = true) {
            $com_cond = "r.comment, '</a>'";
        } else {
            $com_cond = "LEFT(r.comment, 47), '...</a>'";
        }

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    <h4>Reviews</h4>
                    <hr>";

        if ($permissions['write']['admin']) {
            $pre_sql = "r.burn_review_id, ";
        }

        $sql = "SELECT $pre_sql COALESCE(CONCAT(u.full_name, '<br><small><span class=\"label label-default\">Edited By</span></small>'), a.full_name) as \"Reviewer\", CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnProject.reviewDetail(', r.burn_review_id ,')\">', $com_cond) as \"Excerpt\", CONCAT('<small>', COALESCE(r.updated_on, r.added_on), '</small>') as \"Edited\"
        FROM burn_reviews r
        JOIN users a ON (r.added_by = a.user_id)
        LEFT JOIN users u ON (r.updated_by = u.user_id)
        WHERE burn_id = $burn_id";

        if ($permissions['write']['admin']) {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn Request.',
                'no_results_class'=>'info','pkey'=>'burn_review_id','table'=>'burn_reviews','include_edit'=>true,'include_delete'=>false,
                'edit_function'=>'BurnReview.editReviewForm'));
        } else {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn Request.',
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
            "SELECT d.start_date, b.burn_name, b.burn_number
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
                    <a class=\"btn btn-default\" href=\"mailto:".$review['email']."?subject=Burn Review - ".$burn['start_date']." - ".$burn['burn_number']." ".$burn['burn_name']."\" role=\"button\">Email Reviewer</a>
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

        $admin = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system_admin'), 'write');

        $html = "<div class=\"\" style=\"margin: 15px 0px;\">
                    <h4>Notes and Conditions</h4>
                    <hr>";

        $sql = "SELECT burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"Burn.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
        FROM burn_conditions c
        JOIN users u ON (c.added_by = u.user_id)
        WHERE burn_id = $burn_id";

        if ($admin['any']) {
            $sql = "SELECT burn_condition_id, CONCAT('<a style=\"cursor: pointer\" onclick=\"Burn.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
                FROM burn_conditions c
                JOIN users u ON (c.added_by = u.user_id)
                WHERE burn_id = $burn_id";

            $table = show(array('sql'=>$sql,'include_edit'=>true,'edit_function'=>'BurnReview.conditionEdit',
                'table'=>'burn_conditions','pkey'=>'burn_condition_id','include_delete'=>false,
                'no_results_message'=>'There are currently no conditions associated with this Burn request.',
                'no_results_class'=>'info'));
        } else {
            $sql = "SELECT CONCAT('<a style=\"cursor: pointer\" onclick=\"Burn.conditionDetail(', c.burn_condition_id ,')\">', LEFT(c.comment, 47), '...</a>') as \"Excerpt\", u.full_name as \"Approver\"
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
            "SELECT d.start_date, b.project_name, b.project_number, d.acres_treated
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
                    <a class=\"btn btn-default\" href=\"mailto:".$condition['email']."?subject=Burn Review - ".$burn['start_date']." - ".$burn['project_number']." ".$burn['project_name']."\" role=\"button\">Email Approver</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    protected function getUploads($burn_id)
    {
        /**
         *  Constructs the uploads HTML block.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));

        $uploads = fetch_assoc(
            "SELECT f.*
            FROM burn_files b
            JOIN files f ON (b.file_id = f.file_id)
            WHERE b.burn_id = $burn_id
            ORDER BY added_on;"
        );

        if ($permissions['write']['any']) {
            $toolbar = "<div class=\"btn-group pull-right\">
                    <button onclick=\"Uploader.form('burns',$burn_id)\" class=\"btn btn-sm btn-default\">Upload</button>
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
                        <span data-toggle=\"tooltip\" style=\"cursor: pointer\" title=\"File Information\" onclick=\"File.info({$value['file_id']})\" class=\"glyphicon glyphicon-info-si\"></span>
                        <span data-toggle=\"tooltip\" style=\"cursor: pointer\" title=\"Delete File\" onclick=\"File.deleteConfirmation({$value['file_id']})\" class=\"glyphicon glyphicon-remove-circle\"></span>
                    </span></div>";
            }
        } else {
            $html .= status_message("There are currently no uploads associated with this burn request.", "info");
        }

        $html .= "</div>";

        return $html;
    }

    private function checkBurnPermissions($user_id, $burn_id, $permissions)
    {
        /**
         *  Return what the user can do with this burn project. (read, write)
         */

        $read = false;
        $write = false;

        $burn = fetch_row("SELECT added_by, district_id, agency_id FROM burns WHERE burn_id = ?", $burn_id);
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

    public function retrieveStatus($status_id) {
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

    public $value_map = array(
        'burn_project_id'=>array('display'=>false,'title'=>'Burn Project Id',),
        'pre_burn_id'=>array('display'=>false,'title'=>'Pre-Burn Id',),
        'location'=>array('display'=>false,'title'=>'Location',),
        'manager_name'=>array('display'=>false,'title'=>'Manager Name',),
        'manager_number'=>array('display'=>false,'title'=>'Manager Number',),
        'manager_cell'=>array('display'=>false,'title'=>'Manager Cell',),
        'airshed_id'=>array('display'=>true,'title'=>'Airshed','sql'=>'SELECT name FROM airsheds WHERE airshed_id = '),
        'modify_id'=>array('display'=>true,'title'=>'Modify Reason','sql'=>'SELECT description FROM burn_modifications WHERE modification_id = '),
        'request_acres'=>array('display'=>true,'title'=>'Requested Acres',),
        'start_date'=>array('display'=>true,'title'=>'Multi-Day Start Date',),
        'end_date'=>array('display'=>true,'title'=>'Multi-Day End Date',),
        'daily_acres'=>array('display'=>true,'title'=>'Estimated Daily Acres (if Multi-Day)',),
        'comments'=>array('display'=>true,'title'=>'Comments',),
        'pm_sampler_model'=>array('display'=>true,'title'=>'Particulate Matter Sampler Model (if Used)',),
        'pm_sampler_id'=>array('display'=>true,'title'=>'Particulate Matter Sampler Identification (if Used)',),
        'expired'=>array('display'=>true,'title'=>'The Burn is scheduled for more than 14 days past today and has expired.','boolean'=>true),
    );
}
