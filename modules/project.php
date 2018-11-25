<?php

namespace Manager;

class BurnProject
{
    // Config vars
    private $db_table_id = 5;
    private $annual_datatable;
    private $help_toggle = "<button data-toggle=\"tooltip\" data-title=\"Toggle Help\" class=\"btn btn-sm btn-default\" onclick=\"UtahHelp.toggleAll()\"><i class=\"glyphicon glyphicon-info-sign\"></i></button>";

    // Private vars
    private $agency_id;
    private $project_form_id;

    // Status scenario arrays
    private $del_status_id = array(1,3,5);
    private $edit_status_id = array(1,2,3);
    private $approved_edit_id = array(4,5);
    private $register_id = array(4,5);

    // Status IDs.
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
        $this->burn = $burn;
        $this->burn_form_id = 'project_form';
    }

    /**
     *  Burn Project Form Toolbars.
     */

    public function toolbar($page, $burn_project_id, $agency_id = null)
    {
        /**
         *   Produces the standard Burn Project form toolbar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $toolbar_class = $this->burn_form_id . "_tb";
        $btn_class = "btn-sm btn-default";

        if (isset($burn_project_id)) {
            $c_burn_project_id = ", $burn_project_id";
            $save_function = "BurnProject.update($burn_project_id)";
        } else {
            $save_function = "BurnProject.save($agency_id)";
        }

        if ($page == 1) {
            $html = "<div class=\"$toolbar_class pull-right btn-group\">
                ".$this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"BurnProject.showForm(2$c_burn_project_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>
            </div>";
        } elseif ($page == 2) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"BurnProject.showForm(1$c_burn_project_id)\">Back</button>
                <button class=\"btn $btn_class\" onclick=\"BurnProject.showForm(3$c_burn_project_id)\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>";
        } elseif ($page == 3) {
            $html = $this->help_toggle."
                <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
                <button class=\"btn $btn_class\" onclick=\"BurnProject.showForm(2$c_burn_project_id)\">Back</button>
                <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Forward</button>
                <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>";
        }

        return $html;
    }

    /**
     *  Full Burn Project Form HTML Generators
     */

    public function form($page, $district_id = null, $burn_project_id = null)
    {
        /**
         *   Produces the standard Burn Project form.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'interface');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        if (is_null($district_id)) {
            $agency_id = $_SESSION['user']['agency_id'];
        } else {
            $agency_id = fetch_one("SELECT agency_id FROM districts WHERE district_id = ?", $district_id);
        }

        $fbps_fuel = array(1,2,3,4,5,6,7,8,9,10,11,12,13);

        if (isset($burn_project_id)) {
            $values = $this->get($burn_project_id);
            extract($values);
        } else {
            $project_number = $this->generateBurnNumber($district_id);
        }

        // Page based form arguments.
        if ($page == 1) {
            $title = "Form 2: Burn Project <small>1/3</small>";

            $fieldset_id = $this->burn_form_id . "_fs1";

            $ctls = array(
                'district_id'=>array('type'=>'hidden2','value'=>$district_id),
                'project_name'=>array('type'=>'text','label'=>'Burn Project Name','value'=>$project_name,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'project_number'=>array('type'=>'text','label'=>'Burn Project Number','value'=>$project_number,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'airshed_id'=>array('type'=>'combobox','label'=>'Airshed','table'=>'airsheds','fcol'=>'airshed_id','order'=>'airshed_id','display'=>'name','value'=>$airshed_id,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'location'=>array('type'=>'marker','label'=>'Burn Project Center Marker','value'=>$location,'enable_help'=>true,'table_id'=>$this->db_table_id,'zoom_to_fit'=>true),
            );

            $append = "";
        } elseif ($page == 2) {
            $title = "Form 2: Burn Project <small>2/3</small>";

            $fieldset_id = $this->burn_form_id . "_fs2";

            $ctls = array(
                'class_1'=>array('type'=>'boolean','label'=>'Class 1','value'=>$class_1,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'non_attainment'=>array('type'=>'boolean','label'=>'Non-Attainment','value'=>$non_attainment,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'de_minimis'=>array('type'=>'boolean','label'=>'De-Minimis','value'=>$de_minimis,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'project_acres'=>array('type'=>'text','label'=>'Project Acres','value'=>$project_acres,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'completion_year'=>array('type'=>'text','label'=>'Completion Year','value'=>$completion_year,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'black_acres_current'=>array('type'=>'text','label'=>'Planned Black Acres Current Year','value'=>$black_acres_current,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'elevation_low'=>array('type'=>'text','label'=>'Lowest Elevation (Ft)','value'=>$elevation_low,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'elevation_high'=>array('type'=>'text','label'=>'Highest Elevation (Ft)','value'=>$elevation_high,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'major_fbps_fuel'=>array('type'=>'combobox','label'=>'Major FBPS Fuel','array'=>$fbps_fuel,'value'=>$major_fbps_fuel,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'burn_type'=>array('type'=>'combobox','label'=>'Type of Burn','value'=>$burn_type,'table'=>'burn_pile_types','fcol'=>'burn_pile_type_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'number_of_piles'=>array('type'=>'text','label'=>'Number of Piles','value'=>$number_of_piles,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'first_burn'=>array('type'=>'date','label'=>'Earliest Burn Date','value'=>$first_burn,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );

            $append = "";
        } elseif ($page == 3) {
            $title = "Form 2: Burn Project <small>3/3</small>";

            $fieldset_id = $this->burn_form_id . "_fs3";

            $ctls = array(
                'duration'=>array('type'=>'text','label'=>'Burn Duration (Days)','value'=>$duration,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'ignition_method'=>array('type'=>'combobox','label'=>'Ignition Method','value'=>$ignition_method,'table'=>'ignition_methods','fcol'=>'ignition_method_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'other_ignition_method'=>array('type'=>'text','label'=>'Alternative Ignition Method (if Other Selected)', 'value'=>$other_ignition_method,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'county'=>array('type'=>'combobox','label'=>'County','value'=>$county,'table'=>'counties','fcol'=>'county_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'comment'=>array('type'=>'memo','label'=>'Comment','value'=>$comment,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );

            $append = "";
        }


        if ($page == 1) {
            $html .= mkForm(array('id'=>$this->burn_form_id,'controls'=>$ctls,'title'=>$title,'suppress_submit'=>true,'fieldset_id'=>$fieldset_id));
        } else {
            $html .= mkFieldset(array('controls'=>$ctls,'title'=>$title,'id'=>$fieldset_id,'append'=>$append));
        }

        return $html;
    }

    public function submittalForm($burn_project_id)
    {
        /**
         *  Creates the html block to change a Burn Projects status.
         *  E.g.: Draft, Submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT project_name, status_id FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        $validate = $this->validateRequired($burn_project_id);
        $valid = $validate['valid'];

        if ($burn['status_id'] >= $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn project has already been processed. You may return it to draft if edits need to be made. However, it must be resubmitted after editing.</p>
                <button class=\"btn btn-warning btn-block\" onclick=\"BurnProject.toDraft($burn_project_id)\">Change to Draft</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->revision_requested_id) {
            if ($valid) {
                if ($this->reviewCheck($burn_project_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The burn project is valid and has been revised since the last request for revision. To ensure minimal processing time, please make sure the revision addresses all review comments before re-submitting to Utah.gov.</p>
                        <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                        <button class=\"btn btn-success btn-block\" onclick=\"BurnProject.submitToUtah($burn_project_id)\">Re-submit <strong>$burn_name</strong> to Utah.gov</button>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The burn project has not been revised since the last request for revision. Please revise the project according to latest review comment.</p>
                        <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            } else {
                if ($this->reviewCheck($burn_project_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The burn project has been revised since the last request for revision but is not valid.</p>
                        <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The burn project has not been revised since the last request for revision and is not valid.</p>
                        <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            }
        } elseif ($burn['status_id'] == $this->under_review_id) {
            $html = "<div>
                    <p class=\"text-center\">The burn project is currently being reviewed by Utah.gov. Please check back for any requested revisions, or the plans approval.</p>
                    <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            if ($valid) {
                $html = "<div>
                    <p class=\"text-center\">The draft is completed and can be submitted to Utah.gov.</p>
                    <button class=\"btn btn-success btn-block\" onclick=\"BurnProject.submitToUtah($burn_project_id)\">Submit <strong>$burn_name</strong> to Utah.gov</button>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            } else {
                $html = "<div>
                        <p class=\"text-center\">The Burn Project is not completed. Please ensure all required fields are filled in.</p>
                        <a href=\"?detail=true&id=$burn_project_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Project Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            }

        }

        return $html;
    }

    public function ownerChangeForm($burn_project_id)
    {
        /**
         *  Change Ownership Form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT COALESCE(submitted_by, updated_by, added_by) as user_id, agency_id, district_id FROM burn_projects WHERE burn_project_id = ?", $burn_project_id);
        $user_sql = "SELECT user_id, email, full_name FROM users;";
        $district_sql = "SELECT district_id, CONCAT(identifier, ' - ', district) as name FROM districts;";

        $ctls = array(
            'user_id'=>array('type'=>'combobox','label'=>'New Project Owner','fcol'=>'user_id','display'=>'email','sql'=>$user_sql,'value'=>$burn['user_id']),
            'district_id'=>array('type'=>'combobox','label'=>'New Designation','fcol'=>'district_id','display'=>'name','sql'=>$district_sql,'value'=>$burn['district_id'])
        );

        $html = mkForm(array('theme'=>'modal','id'=>'owner-change-form','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"BurnProject.ownerChange({$burn_project_id})\">Change Owner</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>
                </div>";

        return $html;
    }

    public function ownerChange($burn_project_id, $user_id, $district_id)
    {
        /**
         *  Change The Burn Owner
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $status_id = $this->getStatus($burn_project_id);
        $agency_id = fetch_one("SELECT agency_id FROM users WHERE user_id = ?", $user_id);

        if ($status_id['status_id'] >= $this->approved_id) {
            $change = $this->pdo->prepare("UPDATE burn_projects SET added_by = ?, updated_by = ?, submitted_by = ?, agency_id = ?, district_id = ? WHERE burn_project_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $user_id, $agency_id, $district_id, $burn_project_id));
        } else {
            $change = $this->pdo->prepare("UPDATE burn_projects SET added_by = ?, updated_by = ?, agency_id = ?, district_id = ? WHERE burn_project_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $agency_id, $district_id, $burn_project_id));
        }

        if ($change->rowCount() > 0) {
            $html = status_message("The burn project owner has successfully been changed.", "success");
        } else {
            $html = status_message("The burn project owner change was not successful.", "error");
        }

        return null;
    }

    public function registerConfirmation($burn_project_id)
    {
        /**
         *  Returns true if the Burn Project has approved status.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $year = date('Y');

        $burn = fetch_row(
            "SELECT burn_name, status_id, year
            FROM burn_projects b
            LEFT JOIN (
                SELECT r.*
                FROM annual_registration r
                INNER JOIN
                    (SELECT burn_project_id, MAX(year) as max_year
                    FROM annual_registration
                    GROUP BY burn_project_id) br
                ON (r.burn_project_id = br.burn_project_id)
                AND r.year = br.max_year
            ) r ON(b.burn_project_id = r.burn_project_id)
            WHERE b.burn_project_id = $burn_project_id;"
        );

        if ($burn['year'] == $year) {
            // Already registered for this year.
            $html = "<div>
                        <p class=\"text-center\">The Burn Project is already registered for $year.</p>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } elseif (in_array($burn['status_id'], $this->register_id) && $burn['year'] != $year) {
            // Can be registered for this year.
            $html = "<div>
                        <p class=\"text-center\">The Burn Project is not registered for this year. Would you like to register it?</p>
                        <button onclick=\"BurnProject.register($burn_project_id)\" class=\"btn btn-success btn-block\">Register for $year</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            // Cannot be registered for this year.
            $html = "<div>
                        <p class=\"text-center\">The Burn Project is not approved or archived. Please ensure it is before registering.</p>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        }

        return $html;
    }

    public function registerSelect()
    {
        /**
         *  Select burns to register.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $args = array('district_id'=>null);
        extract(merge_args(func_get_args(), $args));

        $user_id = $_SESSION['user']['id'];
        $user = new \Info\User($this->db);

        $year = date('Y');

        if (isset($district_id)) {
            if ($user->isInDistrict($user_id, $district_id)) {
                $cond = "AND b.district_id = $district_id";
            } else {
                return status_message("Your user is not associated with the specified District.", "error");
            }
        } else {
            $cond = "AND b.district_id IN (SELECT district_id FROM user_districts WHERE user_id = $user_id)";
        }

        $plans = fetch_assoc(
            "SELECT b.burn_project_id, burn_number
            FROM burn_projects b
            LEFT JOIN annual_registration r ON(b.burn_project_id = r.burn_project_id)
            WHERE b.status_id IN(4,5)
            AND annual_registration_id IS NULL
            $cond
            OR r.year < ?", $year
        );

        $html = "<div>
                       <p class=\"text-center\">Select a Burn Project to register for $year.</p>";

        foreach ($plans as $value) {
            $html .= "<button class=\"btn btn-default btn-block\" onclick=\"BurnProject.registerConfirmation({$value['burn_project_id']})\">Register {$value['burn_number']}</button>";
        }

        $html .= "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";

        return $html;
    }

    public function register($burn_project_id)
    {
        /**
         *  Returns true if the Burn Project has approved status.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $now = new \DateTime(date("Y-m-d"));
        $threshold = new \DateTime(date('Y-m-d', strtotime('April-1')));

        if ($_SESSION['user']['level_id'] < $this->min_user_level) {
            exit;
        }

        $status_id = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        if ($status_id == 5) {
            // Convert the burn back to approved if it was archived.
            $status_sql = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?;");
            $status_sql->execute(array(4, $burn_project_id));
        }

        $year = date('Y');

        $burn_sql = $this->pdo->prepare("INSERT INTO annual_registration (burn_project_id, year) VALUES (?, ?);");
        $burn_sql->execute(array($burn_project_id, $year));
        if ($burn_sql->rowCount() > 0) {
            $result['message'] = status_message("The annual registration was processed.", "success");

            if ($now > $threshold) {
                //$notify = new \Info\Notify($this->db);
                //$notify->lateAnnualRegistration($burn_project_id);
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The annual registration was not successful.", "error");
        }

        return $result;
    }

    protected function reviewCheck($burn_project_id)
    {
        /**
         *  Check if the Burn Project was updated since the last review.
         */

        $review_last_updated = fetch_one("SELECT MAX(last_burn_update) FROM burn_project_reviews WHERE burn_project_id = ?;", $burn_project_id);
        $last_updated = fetch_one("SELECT updated_on FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        if ($last_updated > $review_last_updated) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Retrieve Burn Project
     */

    public function get($burn_project_id)
    {
        /**
         *  Gets the full burn.
         */

        // Get basic Burn Project info.
        $values = fetch_assoc("SELECT * FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        // Organize array.
        $project = $values[0];

        return $project;
    }

    /**
     *  Save Burn Project.
     */

    public function saveBurn($burn)
    {
        /**
         *  Submits a Burn Project form with new status. Will be in approvals listing.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        // Defaults
        $added_by = $_SESSION['user']['id'];
        $added_on = now();
        $agency_id = $_SESSION['user']['agency_id'];
        $district_id = 1;

        // Set the burn to "draft"
        $status_id = $this->status['draft']['id'];

        // Extract the burn project data.
        extract(prepare_values($burn));

        // Save the Burn Project
        $burn_project_sql = $this->pdo->prepare(
            "INSERT INTO burn_projects (agency_id, district_id, project_name, project_number, added_on, added_by,
              airshed_id, location, class_1, non_attainment, de_minimis, project_acres, completion_year,
              black_acres_current, elevation_low, elevation_high, major_fbps_fuel, burn_type, number_of_piles,
              first_burn, duration, ignition_method, other_ignition_method, county, comment, status_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            "
        );
        $burn_project_sql = execute_bound($burn_project_sql, array($agency_id, $district_id, $project_name,
            $project_number, $added_on, $added_by, $airshed_id, $location, $class_1, $non_attainment,
            $de_minimis, $project_acres, $completion_year, $black_acres_current, $elevation_low,
            $elevation_high, $major_fbps_fuel, $burn_type, $number_of_piles, $first_burn, $duration, $ignition_method,
            $other_ignition_method, $county, $comment, $status_id));
        if ($burn_project_sql->rowCount() > 0) {
            $burn_project_id = fetch_one("SELECT burn_project_id FROM burn_projects WHERE added_on = ? AND agency_id = ?;", array($added_on, $agency_id));
            $this->validateRequired($burn_project_id);
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Burn Project failed to save, please try again.", "error");
        }

        return $result;
    }

    /**
     *  Update Burn Project.
     */

    public function updateBurn($burn, $burn_project_id)
    {

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $updated_by = $_SESSION['user']['id'];

        // Get and extract the original burn information.
        $original = $this->get($burn_project_id);
        extract($original);

        // Extract the values to update.
        extract(prepare_values($burn));

        // Save the burn.
        $burn_project_sql = $this->pdo->prepare(
            "
            UPDATE burn_projects SET project_name = ?, project_number = ?, updated_by = ?,
            airshed_id = ?, location = ?, class_1 = ?, non_attainment = ?, de_minimis = ?, project_acres = ?,
            completion_year = ?, black_acres_current = ?, elevation_low = ?, elevation_high = ?, major_fbps_fuel = ?,
            burn_type = ?, number_of_piles = ?, first_burn = ?, duration = ?, ignition_method = ?,
            other_ignition_method = ?, county = ?, comment = ?
            WHERE burn_project_id = ?;
            "
        );

        $burn_project_sql = execute_bound($burn_project_sql, array($project_name, $project_number, $updated_by,
            $airshed_id, $location, $class_1, $non_attainment, $de_minimis, $project_acres,
            $completion_year, $black_acres_current, $elevation_low, $elevation_high, $major_fbps_fuel,
            $burn_type, $number_of_piles, $first_burn, $duration, $ignition_method, $other_ignition_method,
            $county, $comment, $burn_project_id));
        if ($burn_project_sql->rowCount() <= 0) {
            $result['error'] = true;
            $result['message'] = status_message("The burn failed to update, please try again.", "error");
        }

        return $result;
    }

    public function duplicateBurn($burn_project_id)
    {
        /**
         *  Duplicates a Burn Project. Used automatically when both Broadcast and Piled.
         *  Can be invoked manually in the future for easy plan creation.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        // Defaults
        $now = now();
        $added_by = $_SESSION['user']['id'];

        $burn_project = $this->get($burn_project_id);

        $this->saveBurn($burn_project);

        return true;
    }

    public function checkDuplicate($burn_project_id)
    {
        /**
         *  Check for a duplicate Burn Project.
         *  Returns true if a duplicate is found.
         */

        $original = $this->get($burn_project_id);
        $burn_project = $original['values']['base'];

        $check_sql = $this->pdo->prepare("SELECT burn_project_id FROM burn_projects WHERE burn_name = ? AND district_id = ? AND burn_project_id != ?");
        $check_sql->execute(array($burn_project['burn_name'], $burn_project['district_id'], $burn_project_id));
        if ($check_sql->rowCount() > 0) {
            // Duplicate exists
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Burn Project Delete & Support.
     */

    public function delete($burn_project_id)
    {
        /**
         *  Delete a Burn Project. Only works if the burn is a draft.
         *  There is going to be a separate delete for Utah.gov, where all associated records are removed (e.g. daily).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $check = $this->getStatus($burn_project_id);

        if ($check['allow_delete']) {
            $success = "The Burn Project was successfully deleted.<br><br>";
            $error = "The Burn Project failed to delete:<br><br>";

            // Delete main record
            $delete = $this->pdo->prepare("DELETE FROM burn_projects WHERE burn_project_id = ?");
            $delete->execute(array($burn_project_id));
            if ($delete->rowCount() > 0) {
                $success .= "The Burn Project was deleted.<br>";
            } else {
                $result['error'] = true;
                $error = "The Burn Project couldn't be deleted.<br>";
            }

            if ($result['error']) {
                $result['message'] = status_message($error.$mm_result['message'], "error");
            } else {
                $result['message'] = status_message($success, "success");
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Burn Project under review or active and cannot be deleted.", "error");
        }

        return $result;
    }

    public function deleteConfirmation($burn_project_id)
    {
        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT project_name, status_id FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        if ($burn['status_id'] == $this->approved_id ) {
            $html = "<div>
                <p class=\"text-center\">The burn project is approved and cannot be deleted. Please contact Utah.gov if you would like to cancel this burn.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($burn['status_id'] == $this->disapproved_id) {
            $html = "<div>
                <p class=\"text-center\">The burn project is disapproved. You may delete the burn or leave it for archiving purposes.</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"BurnProject.deleteRecord($burn_project_id)\">Delete <strong>".$burn['project_name']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete <strong>".$burn['project_name']."</strong>?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"BurnProject.deleteRecord($burn_project_id)\">Delete <strong>".$burn['project_name']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function editConfirmation()
    {
        /**
         *  Produce the edit confirmation HTML. Only fires when a Burn Project cannot be edited.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $html = "<div>
            <p class=\"text-center\">The burn project is under review or approved and cannot be edited.</p>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    public function editApproved($burn_project_id)
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
            <p class=\"text-center\">The burn project is approved and must be changed to a draft before editing. Please note the draft project must be re-submitted to Utah.gov for approval.</p>
            <button class=\"btn btn-warning btn-block\" onclick=\"BurnProject.toDraft($burn_project_id)\">Change to Draft</button>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    public function toDraft($burn_project_id)
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

    public function toArchive($burn_project_id)
    {
        /**
         *  Convert a plan to archive status.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        if ($this->checkApproved($burn_project_id)) {
            // The Burn Project is approved, modify its status.

            $to_draft = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?;");
            $to_draft->execute(array($this->archived_id, $burn_project_id));

            if ($to_draft->rowCount() > 0) {
                $result['message'] = status_message("The Burn Project was converted to archived.", "success");
            } else {
                $result['error'] = true;
                $result['message'] = status_message("The Burn Project was not successfully converted to archived", "error");
            }
        } else {
            // The Burn Project was not approved, don't allow a status change.
            $result['error'] = true;
            $result['message'] = status_message("The Burn Project was not approved and cannot be converted to archived", "error");
        }

        return $result;
    }

    public function toApproved($burn_project_id)
    {
        /**
         *  Convert an archived plan back to approved.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        if ($this->checkArchived($burn_project_id)) {
            // The Burn Project is approved, modify its status.

            $to_draft = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?;");
            $to_draft->execute(array($this->approved_id, $burn_project_id));

            if ($to_draft->rowCount() > 0) {
                $result['message'] = status_message("The burn project was reactivated as approved.", "success");
            } else {
                $result['error'] = true;
                $result['message'] = status_message("The burn project was not successfully converted to approved", "error");
            }
        } else {
            // The Burn Project was not approved, don't allow a status change.
            $result['error'] = true;
            $result['message'] = status_message("The burn project was not archived and cannot be converted to approved", "error");
        }

        return $result;
    }

    private function checkApproved($burn_project_id)
    {
        /**
         *  Returns true if the Burn Project has approved status.
         */

        $status = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = $burn_project_id");

        if ($status == $this->approved_id) {
            return true;
        }

        return false;
    }

    private function checkArchived($burn_project_id)
    {
        /**
         *  Returns true if the Burn Project has archived status.
         */

        $status = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = $burn_project_id");

        if ($status == $this->archived_id) {
            return true;
        }

        return false;
    }

    /**
     *  Validation & Submit to Utah.gov Functionality.
     */

    public function validateRequired($burn_project_id)
    {
        /**
         *  Validates a saved burn for required fields.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        // The missing value count.
        $count = 0;
        $missing_msg = "The following required fields are missing in this Burn Project:<br><br>";

        // Get the burn.
        $burn = $this->get($burn_project_id);

        // Check the base values.
        $base_required = array(
            'project_name'=>'Project Name',
            'project_number'=>'Project Number',
            'location'=>'Project Location',
            'airshed_id'=>'Airshed',
            'class_1'=>'Within Class 1',
            'non_attainment'=>'Within Non-Attainment',
            'de_minimis'=>'Within De-Minimis',
            'project_acres'=>'Project Acres',
            'completion_year'=>'Completion Year',
            'black_acres_current'=>'Black Acres Current Year',
            'elevation_low'=>'Lowest Elevation',
            'elevation_high'=>'Highest Elevation',
            'major_fbps_fuel'=>'Major FBPS Fuel Number',
            'first_burn'=>'First Burn Date',
            'duration'=>'Project Duration',
            'ignition_method'=>'Ignition Method',
            'county'=>'County'
        );

        foreach ($base_required as $key => $value) {
            if (is_null($burn[$key])) {
                $count++;
                $missing_msg .= "No ".$value."<br>";
            }
        }

        if ($burn['burn_type'] == 5) {
            if (!$burn['number_of_piles']) {
                $count++;
                $missing_msg .= "Burn type is piles, but no number of piles specified.<br>";
            }
        }

        // Update the Burn Project with its completeness status.
        $update_sql = $this->pdo->prepare("UPDATE burn_projects SET completeness_id = ? WHERE burn_project_id = ?;");

        if ($count == 0) {
            // Update to valid. No missing was counted.
            $update_sql->execute(array(2, $burn_project_id));
            $result['valid'] = true;
            $result['message'] = modal_message("All required Burn Project info is filled out.", "success").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
            $update_sql->execute(array(1, $burn_project_id));
            $result['valid'] = false;
            $result['message'] = modal_message($missing_msg, "error").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        }

        return $result;
    }

    public function submitUtah($burn_project_id)
    {
        /**
         *  Determine if the burn is valid, and change it to submitted/pending.
         *  Add a valid burn number when submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');
        if ($permissions['deny']) {
            exit;
        }

        $valid = $this->validateRequired($burn_project_id);
        $submitted_by = $_SESSION['user']['id'];
        $now = now();

        // Check if its submitted already
        if ($valid['valid'] == true) {
            $status = $this->getStatus($burn_project_id);
            $status_id = $status['status_id'];
        }

        // Not submitted, and valid. Submit to Utah.gov:
        if ($valid['valid'] == true && in_array($status_id, array(1,3))) {
            // The Burn Project is valid. Change its status to "Under Review"
            $last_submitted_by = fetch_one("SELECT submitted_by FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);
            if(!empty($last_submitted_by)) {
                $submitted_by = $last_submitted_by;
            }
            $update_sql = $this->pdo->prepare("UPDATE burn_projects SET status_id = ?, submitted_by = ?, submitted_on = ? WHERE burn_project_id = ?;");
            $update_sql->execute(array(2, $submitted_by, $now, $burn_project_id));
            if ($update_sql->rowCount() > 0) {
                $result['message'] = status_message("The Burn Project has been submitted to Utah.gov.", "success");

                // Notify Utah the Burn Project is Submitted
                $notify = new \Info\Notify($this->db);
                $notify->burnProjectSubmitted($burn_project_id);
            } else {
                $result['message'] = status_message("The Burn Project is valid, but failed to submit.", "error");
            }
        } elseif (in_array($status_id, array(2,4,5,6))) {
            $result['message'] = status_message("The Burn Project is already submitted.", "warning");
        } else {
            $result['message'] = status_message("The Burn Project must be Validated before submitting.", "error");
        }

        return $result;
    }

    /**
     *  Checks Functions.
     */

    public function checkGroup()
    {
        /**
         *  Confirms the user is in a group. Returns true if a group is specified.
         */

        $user_id = $_SESSION['user']['id'];

        $sql = $this->pdo->query("SELECT agency_id FROM users WHERE user_id = $user_id;");
        if ($sql->rowCount() > 0) {
            $result = $sql->fetchColumn();

            if ($result > 0) {
                return $result;
            }
            return false;
        } else {
            return false;
        }
    }

    private function checkPermission($user_id, $burn_project_id)
    {
        /**
         *   Check that the user has permission to view this burn.
         */

        $auth = fetch_one(
            "SELECT user_id
            FROM burn_projects
            WHERE burn_project_id = ?
            AND district_id IN(SELECT district_id FROM user_districts WHERE user_id = ?);"
        , $burn_project_id, $user_id);

        if ($auth == $user_id) {
            return true;
        }

        return false;
    }

    private function checkBurnPermissions($user_id, $burn_project_id, $permissions)
    {
        /**
         *  Return what the user can do with this burn project. (read, write)
         */

        $read = false;
        $write = false;

        $burn = fetch_row("SELECT added_by, district_id, agency_id FROM burn_projects WHERE burn_project_id = ?", $burn_project_id);
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

    public function getStatus($burn_project_id)
    {
        /**
         *  Get the status id of the burn.
         */

        $result['status_id'] = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = $burn_project_id;");

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

    /**
     *  Burn Number Generation & Support.
     */

    private function generateBurnNumber($agency_id, $burn_project_id = null)
    {
        /**
         *  Generates the a burn number for the specified agency.
         *  This function does not add Broadcast (B) or Piled (P). Those must be appended with Append_Burn_Number.
         */

        // Default values.
        $append = "";
        $current_year = date('y');
        $first_burn_number = $current_year."01";

        // Get the user's agency.  !!! This should get the user's unit, not agency.
        //$agency_abbrev = fetch_one("SELECT abbreviation FROM agencies WHERE agency_id = ?;", $agency_id);
        $agency_abbrev = fetch_one("SELECT identifier FROM districts WHERE district_id = ?;", $agency_id);

        // Get the last burn number. (Could do this by group)
        $last_burn_sql = $this->pdo->query(
            "SELECT project_number FROM burn_projects WHERE burn_project_id IN(
                SELECT MAX(burn_project_id)
                FROM burn_projects
                WHERE agency_id = $agency_id
                AND project_number LIKE '%{$agency_abbrev}%'
                AND status_id >= 1)
            AND agency_id = $agency_id;"
        );

        if ($last_burn_sql->rowCount() > 0) {
            // Get the last burn.
            $last_burn_name = $last_burn_sql->fetchColumn(0);

            // Trim the agency abbreviation, B, and P. (result is integer only).
            $replace = array($agency_abbrev);
            $last_burn_number = intval(str_replace($replace, "", $last_burn_name)) + 1;

            if (strlen($last_burn_number) < 2) {
                $last_burn_number = str_pad((string)$last_burn_number, 2, '0', STR_PAD_LEFT);
            }
        } else {
            // This is the first burn, give it the start number.
            $last_burn_number = $first_burn_number;
        }

        $project_number = strtoupper($agency_abbrev).$last_burn_number;

        return $project_number;
    }

    /**
     *  HTML Page Functions.
     */

    public function districtForm()
    {
        /**
         *  Produces a district pre-form step, if a district isn't selected for a burn plan.
         */
        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency','admin'), 'write');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        if ($permissions['admin']) {
            $districts = fetch_assoc("SELECT district_id, district, identifier, old_identifier FROM districts ORDER BY district;");
        } else {
            $user = new \Info\User($this->db);
            $districts = $user->getUserDistricts($_SESSION['user']['id']);
        }

        $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";
        foreach ($districts as $value) {
            if ($permissions['admin']) {
                if (empty($value['old_identifier'])){
                    $str = "{$value['identifier']} {$value['district']}";
                } else {
                    $str = "{$value['identifier']}-{$value['old_identifier']} {$value['district']}";
                }
            } else {
                $str = "{$value['district']}";
            }

            if (strlen($str) > 32 && strlen($str) <= 38) {
                $small = "style=\"font-size: 11px;\"";
            } elseif (strlen($str) > 38) {
                $small = "style=\"font-size: 11px; white-space: normal; word-wrap: break-word;\"";
            }

            $html .= "<button class=\"btn btn-default btn-block\" $small onclick=\"BurnProject.newForm(".$value['district_id'].")\">".$str."</button>";
        }
        $html .= "</div>";

        return $html;
    }

    public function overviewPage()
    {

        $args = array('agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        $edit_table = $this->show('edit');
        $view_table = $this->show('view');
        $map = $this->getAllMap();
        $return_link = "";

        $html['header'] = "<div class=\"row\">
            <div class=\"col-sm-12\">
                <span class=\"pull-right\">
                    $return_link
                </span>
                <h3>Overview <small>Form 2: Burn Projects</small></h3>
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
                        <button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.newForm()\">New Burn Project</button>
                    </div>
                </div>
            </div>";
        }

        return $html;
    }

    public function annualOverviewPage()
    {

        $args = array('district_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        if (isset($district_id)) {
            $table = $this->showRegistration(array('district_id'=>$district_id));
            //$map = $this->getAllMap(array('district_id'=>$district_id));
            $return_link = "";
        } else {
            $table = $this->showRegistration();
            //$map = $this->getAllMap();
            $return_link = "";
        }

        $table_html = $table['html'];
        $sidebar = $this->annualSidebar($table['datatable']);

        $html['header'] = "<div class=\"row\">
          <div class=\"col-sm-12\">
            <span class=\"pull-right\">
              $return_link
            </span>
            <h3>Annual Registration <small>Burn Projects</small></h3>
          </div>
        </div>";

        $html['main'] = "<div class=\"row\">
          <div class=\"col-sm-3\">
            $sidebar
          </div>
          <div class=\"col-sm-9\">
            <hr>
            $table_html
          </div>
        </div>";

        return $html;
    }

    public function detailPage($burn_project_id)
    {
        /**
         *  Constructs a Burn Project Detail Page.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkBurnPermissions($_SESSION['user']['id'], $burn_project_id, $permissions);

        // Statics
        $link = explode('?', $_SERVER['REQUEST_URI']);
        $return_link = "<a href=\"".$link[0]."\">Return to Overview</a>";

        if ($burn_permissions['allow']) {
            // Get the burn project.
            $burn_project = $this->get($burn_project_id);
            $agency_id = $burn_project['agency_id'];

            // Construct the title.
            if (isset($burn_project['project_name']) && isset($burn_project['project_number'])) {
                $title = $burn_project['project_name'] . " - " . $burn_project['project_number'];
            } elseif (isset($burn_project['project_name'])) {
                $title = $burn_project['project_name'];
            } else {
                $title = "Burn Project";
            }

            // Get HTML blocks.
            $status = $this->getStatusLabel($burn_project_id);
            $map = $this->getMap($burn_project);
            $unit = $this->tablifyFields($burn_project, 'project_info');
            $burn = $this->tablifyFields($burn_project, 'detail_info');
            $fuels = $this->tablifyFuels($burn_project_id);
            $contacts = $this->getContacts($burn_project_id);
            $reviews = $this->getReviews($burn_project_id);
            $uploads = $this->getUploads($burn_project_id);
            $black_acres = $this->getYearlyAcres($burn_project_id);

            if (in_array($burn_project['status_id'], $this->edit_status_id) && $burn_permissions['write']) {
                if ($burn_project['status_id'] < $this->revision_requested_id) {
                    $submit_text = "Submit";
                } else {
                    $submit_text = "Re-submit";
                }

                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.submitForm($burn_project_id)\">$submit_text</button>
                    <button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.editConfirmation($burn_project_id)\">Edit Burn Project</button>
                    <a href=\"/pdf/project.php?id={$burn_project_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            } else {
                $toolbar = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                    <button class=\"btn btn-sm btn-default\" disabled>Edit Burn Project</button>
                    <a href=\"/pdf/project.php?id={$burn_project_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                </div>";
            }

            //if ($burn_project['status_id'] == $this->approved_id && $burn_permissions['write']) {
            //    // The Burn Project is approved, and can be archived.
            //    $toolbar .= "<button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.editConfirmation($burn_project_id)\">Edit Burn Project</button>
            //        <button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.toArchive($burn_project_id)\">Archive Burn Project</button>";
            //}

            //if ($burn_project['status_id'] == $this->archived_id && $burn_permissions['write']) {
            //    // The Burn Project is approved, and can be archived.
            //    $toolbar .= "<button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.editConfirmation($burn_project_id)\">Edit Burn Project</button>
            //        <button class=\"btn btn-sm btn-default\" onclick=\"BurnProject.toApproved($burn_project_id)\">Return to Approved</button>";
            //}

            $html['header'] = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <span class=\"pull-right\">
                        $return_link
                        $status
                    </span>
                    <h3>".$title." <small>Burn Project</small></h3>
                </div>
            </div>";

            $html['main'] = "<div class=\"row\">
                <div class=\"col-sm-8\">
                    <h4>Form 2: Burn Project Info</h4>
                    <hr>
                    $map
                    <br>
                    $unit
                    $burn
                    $smoke
                    $broadcast
                    $piled
                </div>
                <div class=\"col-sm-4\">
                    $toolbar
                    $contacts
                    $reviews
                    $uploads
                    $black_acres
                </div>
            </div>
            </div>
            <div class=\"row\">
                <div class=\"col-sm-12\">
                    <hr>
                    <h4>Project Total Accomplished Fuels</h4>
                    <hr>
                    {$fuels['html']}
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

    public function pdfPage($burn_project_id)
    {
        /**
         *  Constructs a Burn Project Detail Page.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkBurnPermissions($_SESSION['user']['id'], $burn_project_id, $permissions);

        if ($burn_permissions['allow']) {
            // Get the daily burn.
            $burn_project = $this->get($burn_project_id);
            $district_id = $burn_project['district_id'];

            // Static fields.
            $project_name = $burn_project['project_name'];
            $project_number = $burn_project['project_number'];
            $contact_name = $burn_project['manager_name'];
            $contact_number = $burn_project['manager_number'];

            // Build the map.
            $location = str_replace(array('(',')',' '), '', $burn_project['location']);
            $color = str_replace('#', '0x', $this->retrieveStatus($burn_project['status_id'])['color']);
            $label = substr($this->retrieveStatus($burn_project['status_id'])['title'], 0, 1);
            $static_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=16&center=$location&markers=color:$color%7Clabel:$label%7C$location";

            // Get HTML blocks.
            $info = $this->tablifyFields($burn_project, 'project_info', true);
            $unit = $this->tablifyFields($burn_project, 'detail_info', true);
            $fuels = $this->tablifyFuels($burn_project_id, true);
            $contacts = $this->getContacts($burn_project_id);
            $reviews = $this->getReviews($burn_project_id, true);

            $html = "
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"50%\">
                        <col width=\"49%\">
                        <tr style=\"border: 0.15em solid black;\">
                            <td style=\"width: 50%\">Form 2: Burn Project</td>
                            <td style=\"width: 50%\"><strong>$project_number</strong> - $project_name</td>
                        </tr>
                        <tr style=\"border: 0.15em solid black; padding: 0.3em;\">
                            <td style=\"width: 50%\">
                                $info
                                <img width=\"28%\" src=\"$static_map\"/>
                            </td>
                            <td style=\"width: 50%\">
                                $unit
                            </td>
                        </tr>
                    </table>
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <tr style=\"\">
                            <td style=\"width: 99%\">
                                $fuels
                            </td>
                        </tr>
                    </table>
                <pagebreak />
                <div class=\"col-sm-4\">
                    $contacts
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

    /**
     *  HTML Min-Functions.
     */

    public function reviewDetail($review_id)
    {
        /**
         *  The review detail pages.
         */

        $review = fetch_row(
            "SELECT r.burn_project_id, u.email, u.full_name, DATE_FORMAT(r.added_on, '%Y-%m-%e %l:%i %p') as added_on, r.comment
            FROM burn_project_reviews r
            JOIN users u ON(r.added_by = u.user_id)
            WHERE r.burn_project_review_id = $review_id;"
        );

        $burn_project = fetch_row(
            "SELECT project_name, project_number
            FROM burn_projects
            WHERE burn_project_id = ".$review['burn_project_id'].";"
        );

        $html = "<div>
            <p><strong>".$review['full_name'].": </strong>".$review['comment']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$review['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$review['email']."?subject=Burn Project - ".$burn_project['project_name']." - ".$burn_project['project_number']."\" role=\"button\">Email Reviewer</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        return $html;
    }

    /**
     *  HTML Section Functions.
     */

    public function outstandingBurns()
    {
        /**
         *  Produces a burn table, revision as data tables update.
         */

        $args = array('burn_project_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        $user_id = $_SESSION['user']['id'];
        $user = new \Info\User($this->db);

        $sql = "SELECT db.ignition_date as \"Ignition Date\", COALESCE(c.acres, db.acres_treated) as \"Approved Acres\",
            CONCAT('<a href=\"/manager/project.php?detail=true&id=' , b.burn_project_id, '\">', b.burn_name, '</a>') as \"Burn Name\",
            COALESCE(b.burn_number) as \"Burn Number\", d.district as \"District\",
            CONCAT('<a href=\"/manager/accomplishment.php?form=true&dbid=', db.daily_burn_id ,'&dsid=', db.district_id, '\">Accomplish ', db.ignition_date, '</a>') as \"Accomplishments\"
            FROM burn_projects b
            JOIN districts d ON(b.district_id = d.district_id)
            JOIN agencies a ON (d.agency_id = a.agency_id)
            JOIN daily_burns db ON(db.burn_project_id = b.burn_project_id)
            LEFT JOIN accomplishments ac ON(db.daily_burn_id = ac.daily_burn_id)
            LEFT JOIN daily_burn_conditions c ON (db.daily_burn_id = c.daily_burn_id)
            JOIN users u ON (b.submitted_by = u.user_id)
            $cond
            AND db.status_id = 5
            AND ac.accomplishment_id IS NULL
            AND db.ignition_date < now()
            AND b.agency_id = $agency_id
            ORDER BY b.burn_number, db.ignition_date;";

        $table = show(array('sql'=>$sql,'include_delete'=>false,'paginate'=>true,'sort_column'=>0,'include_edit'=>false,'sort_direction'=>'asc',
            'no_results_message'=>'There are currently no outstanding accomplishment reports for your district(s).','no_results_class'=>'info'));

        $html = "<div class=\"col-sm-12\">
            <h3>Daily Burns Needing an Accomplishment Report</h3>
            <hr>";

        $html .= $table['html'];

        $html .= "</div>";

        return $html;
    }

    private function show($type)
    {
        /**
         *  Produces a burn table, revision as data tables update.
         */

        $args = array('burn_project_id'=>null,'user_id'=>$_SESSION['user']['id']);
        extract(merge_args(func_get_args(), $args));

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency','admin'));
        if ($permissions['deny']) {
            echo $permissions['message'];
            //exit;
        }

        $user = new \Info\User($this->db);
        $agency = $user->getUserAgency($_SESSION['user']['id'], 'sql');
        $districts = $user->getUserDistricts($_SESSION['user']['id'], 'sql');

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

        $sql = "SELECT b.burn_project_id, b.project_name as \"Project Name\",
            COALESCE(b.project_number, 'N/A') as \"Project Number\", a.agency as \"Agency\",
            CONCAT('<span class=\"', s.class ,'\" onclick=\"BurnProject.submitForm(', b.burn_project_id, ')\">', s.name ,'</span>') as \"Form Status\",
            COALESCE(CONCAT('<span class=\"', c.class ,'\" data-toggle=\"tooltip\" title=\"Click to Check\" onclick=\"BurnProject.validate(', b.burn_project_id , ')\">', c.name ,'</span>'),CONCAT('<span class=\"btn btn-default label label-default\">N/A</span>'))  as \"Form Completeness\",
            CONCAT('<span  class=\"label label-default\">', u.full_name, '</span>') as \"By\", d.identifier as \"Designation\"
            FROM burn_projects b
            JOIN agencies a ON(b.agency_id = a.agency_id)
            JOIN districts d ON(b.district_id = d.district_id)
            LEFT JOIN burn_project_statuses s ON(b.status_id = s.status_id)
            JOIN users u ON (b.added_by = u.user_id)
            LEFT JOIN burn_project_completeness c ON(b.completeness_id = c.completeness_id)
            $pre_cond
            $cond
            ORDER BY b.status_id DESC;";

        $new_function = "BurnProject.newForm($agency_id)";

        if ($type == 'edit' && $permissions['write']['any']) {
            $table = show(array('sql'=>$sql,'pkey'=>'burn_project_id','table'=>'burn_project',
                'include_delete'=>true,'delete_function'=>'BurnProject.deleteConfirmation',
                'include_view'=>true,'view_href'=>'?detail=true&id=@@',
                'edit_function'=>'BurnProject.editConfirmation','new_function'=>$new_function,'paginate'=>true,
                'no_results_message'=>'There are no editable burn projects associated with your user.','no_results_class'=>'info'));
        } elseif ($type == 'view' && $permissions['read']['any']) {
            $table = show(array('sql'=>$sql,'pkey'=>'burn_project_id','table'=>'burn_project','paginate'=>true,'include_edit'=>false,'include_delete'=>false,
                'include_view'=>true,'view_href'=>'?detail=true&id=@@',
                'no_results_message'=>'There are no viewable burn projects associated with your district(s).','no_results_class'=>'info'));
        }

        $html = $table['html'];

        return $html;
    }

    private function showRegistration()
    {
        /**
         *  Produces a burn table, revision as data tables update.
         */

        $args = array('burn_project_id'=>null,'district_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        $user_id = $_SESSION['user']['id'];
        $user = new \Info\User($this->db);

        if (isset($district_id)) {
            if ($user->isInDistrict($user_id, $district_id)) {
                $cond = "AND d.district_id = $district_id";
            } else {
                return status_message("Your user is not associated with the specified District.", "error");
            }
        } else {
            $cond = "AND d.district_id IN (SELECT district_id FROM user_districts WHERE user_id = $user_id)";
        }

        $sql = "SELECT
            CONCAT('<a href=\"?detail=true&id=' , b.burn_project_id, '\">', b.burn_name, '</a>') as \"Burn Name\",
            COALESCE(b.burn_number, 'N/A') as \"Burn Number\", d.district as \"District\",
            COALESCE(CONCAT('<span class=\"label label-primary\" ref=\"', bc.broadcast_id, '\">Broadcast</span>', ' <span class=\"label label-primary\" ref=\"', ps.piled_slash_id, '\">Piled Slash</span>'), CONCAT('<span class=\"label label-primary\" ref=\"', bc.broadcast_id, '\">Broadcast</span>'), CONCAT('<span class=\"label label-primary\" ref=\"', ps.piled_slash_id, '\">Piled Slash</span>'), '<span class=\"label label-default\">None</span>') as \"Fuel Type\",
            CONCAT('<span class=\"', s.class ,'\">', s.name ,'</span>') as \"Status\",
            COALESCE(CONCAT('<span data-toggle=\"tooltip\" title=\"Update Burn Registration\" onclick=\"BurnProject.registerConfirmation(', b.burn_project_id , ')\" class=\"btn btn-success label label-success\">', r.year, '</span>'), CONCAT('<span data-toggle=\"tooltip\" title=\"Register Burn\" class=\"btn btn-inverse label label-inverse\" onclick=\"BurnProject.registerConfirmation(', b.burn_project_id , ')\">Never</span>')) as \"Year\"
            FROM burn_projects b
            JOIN districts d ON(b.district_id = d.district_id)
            JOIN agencies a ON (d.agency_id = a.agency_id)
            JOIN burn_project_statuses s ON(b.status_id = s.status_id)
            JOIN users u ON (b.submitted_by = u.user_id)
            LEFT JOIN (
                SELECT r.*
                FROM annual_registration r
                INNER JOIN
                    (SELECT burn_project_id, MAX(year) as max_year
                    FROM annual_registration
                    GROUP BY burn_project_id) br
                ON (r.burn_project_id = br.burn_project_id)
                AND r.year = br.max_year
            ) r ON(b.burn_project_id = r.burn_project_id)
            LEFT JOIN burn_project_completeness c ON(b.completeness_id = c.completeness_id)
            LEFT JOIN broadcast bc ON (b.burn_project_id = bc.burn_project_id)
            LEFT JOIN piled_slash ps ON (b.burn_project_id = ps.burn_project_id)
            WHERE b.status_id > 3
            $cond
            ORDER BY b.status_id DESC;";

        $table = show(array('sql'=>$sql,'pkey'=>'burn_project_id','table'=>'burn_project','include_edit'=>false,
            'include_delete'=>false,'paginate'=>true,'include_new'=>false,'sort_column'=>5,'sort_direction'=>'asc',
            'no_results_message'=>'There are currently no Burn Projects associated with your district(s).','no_results_class'=>'info'));

        $html = $table['html'];

        return $table;
    }

    public function annualSidebar($datatable)
    {

        $year = date('Y');

        $html = "<hr><div style=\"border-bottom: 1px solid #e4e4e4;\">";

        $html .= $this->yearFilter($datatable);

        $html .= $this->statusFilter($datatable);

        $html .= "<div class=\"dt_filter_section\">
                <p><small>The most recent yearly registration is listed for a given Burn Project. Only Burn Projects with the current year of <strong>$year</strong> are registered.</small></p>
            </div>
            </div>";

        return $html;
    }

    private function yearFilter($datatable, $selected)
    {
        /**
         *  Make the years filter.
         */

        $start_year = 2010;
        $current_year = date('Y');

        $years = array(array('title'=>$current_year,'class'=>'success'));

        for ($i = 1; $i <= ($current_year - $start_year); $i++) {
            $append = array('title'=>$current_year - $i,'class'=>'success');
            array_push($years, $append);
        }

        array_push($years, array('title'=>'Never','class'=>'inverse'));

        $html = label_filter(array('object'=>$datatable,'column'=>5,'function_name'=>'FilterYr',
            'wrapper_class'=>'filter_year','selector'=>'year','title'=>'Year',
            'selected'=>array(0, key(array_slice($years, -1, 1, true))),'info_array'=>$years));

        return $html;
    }

    private function statusFilter($datatable, $selected)
    {
        /**
         *  Produces the datatables label filter for statues.
         */

        // Remove drafts from the default status list.
        $info = $this->status_html;
        unset($info[1]);
        unset($info[2]);
        unset($info[3]);

        $html = label_filter(array('object'=>$datatable,'column'=>4,'function_name'=>'FilterSt',
            'wrapper_class'=>'filter_status','selector'=>'status','title'=>'Statuses',
            'selected'=>array(4,5),'info_array'=>$info));

        return $html;
    }

    private function getAllMap()
    {
        /**
         *  The All Map display.
         */

        $args = array('agency_id'=>$_SESSION['user']['agency_id'],'user_id'=>$_SESSION['user']['id']);
        extract(merge_args(func_get_args(), $args));

        global $map_center;

        $markers = fetch_assoc("SELECT burn_project_id, location, CONCAT(project_number, ': ', project_name) as name, status_id, added_by, district_id FROM burn_projects WHERE district_id IN(SELECT district_id FROM user_districts WHERE user_id = ?);", $user_id);

        $center = "zoom: 6,
            center: new google.maps.LatLng($map_center),";

        if ($markers['error'] == false) {
            // Construct the Marker array.
            $marker_arr = "var burns = [\n ";
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
                $marker_arr .= "[".$value['burn_project_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$value['name']."', '".$marker_status['color']."', ".$edit.",'".str_replace(" ", "_", strtolower($marker_status['title']))."']$comma\n ";
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
                            window.location='/manager/project.php?detail=true&id='+marker.id;return false;
                        });
                    }
                }

                setMarkers(map, burns)
            ";
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

                $marker

                $zoom

                var Overlay = new Overlay();
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    protected function getMap($burn_project)
    {
        /**
         *  Builds a location & marker map for a single daily burn.
         */

        $zoom_to_fit = true;
        $control_title = "Zoom to project";
        global $map_center;

        $marker = $burn_project['location'];
        $status = $this->retrieveStatus($burn_project['status_id']);

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
              controlUI.title = 'Click to return to the location';
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

        $center = "zoom: 10,
            center: new google.maps.LatLng($map_center),";

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
                            fillColor: '".$status['color']."',
                            fillOpacity: 1
                        },
                    });
                }

                marker.setMap(map)
            ";
        } else {
            $center = "zoom: 10,
                center: new google.maps.LatLng({$this->map_center}),";
            $marker = "
                var marker_center = bounds.getCenter();

                var marker = new google.maps.Marker({
                    position: marker_center,
                    draggable: true,
                });

                marker.setMap(map)
            ";
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

                $marker

                $zoom

                var Overlay = new Overlay();
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    protected function tablifyFields($burn_project, $section, $pdf = false)
    {
        /**
         *  Makes a table rows of the fields list.
         *  Assumes a default get Burn Project array.
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

        $burn_project_id = $burn_project['burn_project_id'];

        if ($section == 'project_info') {
            $title = "Project Info";
            $value_array = $burn_project;
            $fields_array = array('project_name','project_number','airshed_id','class_1',
                'non_attainment','de_minimis');
        } elseif ($section == 'detail_info') {
            $title = "Details";
            $value_array = $burn_project;
            $fields_array = array('project_acres','completion_year','black_acres_current','elevation_low',
                'elevation_high','major_fbps_fuel','burn_type','number_of_piles','first_burn','duration',
                'ignition_method','other_ignition_method','county', 'comment');
        }

        $html = "<table $style class=\"table table-responsive table-condensed\">
            $colspaces
            <thead>
            <tr><th>$title</th><th>$v_title</th><th></th></tr>
            </thead>
            <tbody>";

        foreach ($fields_array as $key) {

            $reference = $this->value_map[$key];
            if ($reference['multiselect'] == true) {
                $reference['pvalue'] = $burn_project_id;
                $value = \mm_label($reference);
            } else {
                $value = $value_array[$key];
            }

            if ($reference['boolean'] == true) {
                if ($value < 1) {
                    $value = "False";
                } else {
                    $value = "True";
                }
            }

            if (isset($reference['sql']) && isset($value)) {
                $value = fetch_one($reference['sql'] . $value);
            }

            if (!isset($reference['field_id'])) {
                $reference['field_id'] = fetch_one("SELECT field_id FROM fields WHERE table_id = ? AND `column` = ?", array($this->db_table_id, $key));
            }

            if (isset($reference['field_id'])) {
                $help = getInputPopover(true, $reference['field_id']);
            }

            if ($reference['display']) {
                $html .= "<tr><td  $i_style>".$reference['title']."</td><td $i_style>".$value."</td><td>".$help."</td></tr>";
            }
        }

        $html .= "</tbody>
        </table>";

        return $html;
    }

    protected function tablifyFuels($burn_project_id, $pdf = false)
    {
        /**
         *  Returns fuels table.
         */

        $sql = "SELECT nffl_model as \"NFFL Model\", t.fuel_type as \"Fuel Type\", ROUND(COALESCE(SUM(a.black_acres), 0), 3) as \"Project Black Acres\",
            ROUND(COALESCE(AVG(t.ton_per_acre), 0), 3) as \"Mean Tons of Fuel Consumed/Acre (T/A)\", ROUND(COALESCE(SUM(a.total_tons), 0), 3) as \"Project Total Tons Consumed\",
            ROUND(COALESCE(AVG(f.ef), 0), 3) as \"Emission Coefficient (T PM/T of Fuel)\", ROUND(COALESCE(SUM(a.tons_emitted), 0), 3) as \"Project Total Tons PM\"
            FROM accomplishment_fuels a
            JOIN accomplishments m ON(a.accomplishment_id = m.accomplishment_id)
            JOIN fuels f ON(a.fuel_id = f.fuel_id)
            JOIN fuel_types t ON(f.fuel_type_id = t.fuel_type_id)
            WHERE a.accomplishment_id IN(SELECT accomplishment_id FROM accomplishments WHERE burn_project_id = $burn_project_id)
            AND f.show_on_form = '1'
            GROUP BY m.burn_project_id, nffl_model, t.fuel_type, description
            ORDER BY nffl_model;";

        if ($pdf) {
            $style = "style=\"width: 100%\"";
            $i_style = "style=\"width: 14%; border: 1px solid #ggg; text-align: center;\"";
            $colspaces = "<col width=\"14%\">
                <col width=\"14%\">
                <col width=\"14%\">
                <col width=\"14%\">
                <col width=\"14%\">
                <col width=\"14%\">
                <col width=\"14%\">";

            $results = fetch_assoc($sql);
            $keys = array_keys($results[0]);

            $html = "<table style=\"width: 100%;\" class=\"table table-responsive table-condensed\">
                $colspaces
                <tbody>
                <tr>";
            foreach ($keys as $key) {
                $html .= "<td $i_style>{$key}</td>";
            }
            $html .= "</tr>";

            foreach ($results as $result) {
                $html .= "<tr>";
                foreach($result as $value) {
                    $html .= "<td $i_style>{$value}</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody>
                </table>";
        } else {
            $html = show(array('sql'=>$sql,
                'no_results_message'=>'There are no fuels associated with this project.',
                'no_results_class'=>'info'));
        }

        return $html;
    }

    private function getStatusLabel($burn_project_id)
    {
        $status = fetch_row(
            "SELECT description, class, name FROM burn_project_statuses
            WHERE status_id IN (SELECT status_id FROM burn_projects WHERE burn_project_id = $burn_project_id);"
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    protected function getContacts($burn_project_id)
    {
        /**
         *  Constructs the contacts display div for a given Burn Project.
         */

        $submitter = fetch_row(
            "SELECT u.full_name, u.email, u.phone, a.agency
            FROM burn_projects b
            JOIN users u ON(b.submitted_by = u.user_id)
            JOIN agencies a ON(u.agency_id = a.agency_id)
            WHERE b.burn_project_id = ?
            "
        , $burn_project_id);

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
                            <p class=\"district\">".$submitter['district']."</p>
                            <a href=\"tel:".$submitter['phone']."\">".$submitter['phone']."</a>
                        </div>
                    </div>";

        $html .= "</div>";

        return $html;
    }

    protected function getReviews($burn_project_id, $full = false)
    {
        /**
         *  Constructs the reviews display div & table for a given Burn Project.
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
            $pre_sql = "r.burn_project_review_id, ";
        }

        $sql = "SELECT $pre_sql COALESCE(
          CONCAT(u.full_name, '<br><small><span class=\"label label-default\">Edited By</span></small>'), a.full_name) as \"Reviewer\",
          CONCAT('<a style=\"cursor: pointer\" onclick=\"BurnProject.reviewDetail(', r.burn_project_review_id ,')\">', $com_cond) as \"Excerpt\",
          CONCAT('<small>', COALESCE(r.updated_on, r.added_on), '</small>') as \"Edited\"
        FROM burn_project_reviews r
        JOIN users a ON (r.added_by = a.user_id)
        LEFT JOIN users u ON (r.updated_by = u.user_id)
        WHERE burn_project_id = $burn_project_id";

        if ($permissions['write']['admin']) {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn Project.',
                'no_results_class'=>'info','pkey'=>'burn_project_review_id','table'=>'burn_project_reviews','include_edit'=>true,'include_delete'=>false,
                'edit_function'=>'BurnProjectReview.editReviewForm'));
        } else {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this Burn Project.',
                'no_results_class'=>'info'));
        }

        $html .= $table['html'];

        $html .= "</div>";

        return $html;
    }

    protected function getUploads($burn_project_id)
    {
        /**
         *  Constructs the uploads HTML block.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));

        $uploads = fetch_assoc(
            "SELECT f.*
            FROM burn_project_files b
            JOIN files f ON (b.file_id = f.file_id)
            WHERE b.burn_project_id = $burn_project_id
            ORDER BY added_on;"
        );

        if ($permissions['write']['any']) {
            $toolbar = "<div class=\"btn-group pull-right\">
                    <button onclick=\"Uploader.form('burn_projects',$burn_project_id)\" class=\"btn btn-sm btn-default\">Upload</button>
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
            $html .= status_message("There are currently no uploads associated with this Burn Project.", "info");
        }

        $html .= "</div>";

        return $html;
    }

    public function getYearlyAcres($burn_project_id)
    {
        /**
         *  Construct a yearly black acres accomplished table.
         */

        $sql = "SELECT YEAR(a.start_datetime) as \"Year\", CONCAT(SUM(s1.black_acres), ' <small class=\"pull-right\"><strong>ACRES</strong></small>') as \"Black Acres\"
            FROM (
                SELECT AVG(a.black_acres) as black_acres, a.accomplishment_id
                FROM accomplishment_fuels a
                JOIN fuels f ON (a.fuel_id = f.fuel_id)
                JOIN fuel_types t ON(f.fuel_type_id = t.fuel_type_id)
                GROUP BY a.accomplishment_id, t.fuel_type_id
            ) s1
            JOIN accomplishments a ON(s1.accomplishment_id = a.accomplishment_id)
            WHERE a.burn_project_id = $burn_project_id
            AND YEAR(start_datetime) != 0
            GROUP BY YEAR(start_datetime)";

        $table = show(array('sql'=>$sql,'include_edit'=>false,'include_delete'=>false,'include_view'=>false,
            'no_results_message'=>'There are no accomplished black acres for this burn project.','no_results_class'=>'info'));

        $html = "<div class=\"\" style=\"margin: 30px 0px;\">
            $toolbar
            <h4>Yearly Accomplished Black Acres</h4>
            <hr>";

        $html .= $table['html'];

        return $html;
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

    protected $value_map = array(
        'burn_project_id'=>array('display'=>false,'title'=>'Burn Project Id'),
        'agency_id'=>array('display'=>true,'title'=>'Agency'),
        'submitted_by'=>array('display'=>true,'title'=>'Submitted By'),
        'project_name'=>array('display'=>true,'title'=>'Project Name'),
        'project_number'=>array('display'=>true,'title'=>'Project Number'),
        'location'=>array('display'=>false,'title'=>'Location'),
        'airshed_id'=>array('display'=>true,'title'=>'Airshed','sql'=>'SELECT `name` FROM `airsheds` WHERE `airshed_id` = '),
        'class_1'=>array('display'=>true,'title'=>'Within Class 1','boolean'=>true),
        'non_attainment'=>array('display'=>true,'title'=>'Within Non-Attainment','boolean'=>true),
        'de_minimis'=>array('display'=>true,'title'=>'De Minimis Rule','boolean'=>true),
        'project_acres'=>array('display'=>true,'title'=>'Total Project Acres'),
        'completion_year'=>array('display'=>true,'title'=>'Anticipated Completion Year'),
        'black_acres_current'=>array('display'=>true,'title'=>'Planned Black Acres Current Year'),
        'elevation_low'=>array('display'=>true,'title'=>'Lowest Elevation (Ft)'),
        'elevation_high'=>array('display'=>true,'title'=>'Highest Elevation (Ft)'),
        'major_fbps_fuel'=>array('display'=>true,'title'=>'Major FBPS Fuel 1-13'),
        'burn_type'=>array('display'=>true,'title'=>'Type of Burn','sql'=>'SELECT `name` FROM `burn_pile_types` WHERE `burn_pile_type_id` = '),
        'number_of_piles'=>array('display'=>true,'title'=>'Number of Piles'),
        'first_burn'=>array('display'=>true,'title'=>'Earliest Burn Date'),
        'duration'=>array('display'=>true,'title'=>'Burn Duration (Days)'),
        'ignition_method'=>array('display'=>true,'title'=>'Ignition Method','sql'=>'SELECT `name` FROM `ignition_methods` WHERE `ignition_method_id` = '),
        'other_ignition_method'=>array('display'=>true, 'title'=>'Alternative Ignition Method'),
        'county'=>array('display'=>true,'title'=>'County','sql'=>'SELECT `name` FROM `counties` WHERE `county_id` = '),
        'comment'=>array('display'=>true,'title'=>'Comment')
    );

}
