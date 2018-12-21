<?php

namespace Manager;

class Accomplishment
{
    private $db_table_id = 11;
    private $district_id;
    private $form_id;
    private $del_status_id = array(1,2,3);
    private $edit_status_id = array(1,2,3);
    private $min_user_level = 2;

    protected $draft_id = 1;
    protected $under_review_id = 2;
    protected $revision_requested_id = 3;
    protected $approved_id = 4;

    // Status info array
    protected $status = array(
        'draft'=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','class'=>'warning'),
        'under_review'=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','zindex'=>'102','class'=>'warning'),
        'revision_requested'=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.05','zindex'=>'103','class'=>'danger'),
        'approved'=>array('id'=>4,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'105','class'=>'success')
    );

    protected $sub_status = array(
        'completed'=>array(),
        'not_completed'=>array(),
        'postponed'=>array()
    );

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->burn = $burn;
        $this->form_id = 'accomplishment_form';
    }

    public function show()
    {
        /**
         *  Build the table.
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
            $cond = "WHERE a.burn_project_id = $burn_project_id";
        }

        if ($permissions['write']['admin']) {
            // No agency requirement for admin.
            $pre_cond = "";
        } else {
            $pre_cond = "WHERE a.agency_id ".$agency;
        }

        if ($type == 'edit') {
            if ($permissions['write']['admin']) {
                $cond = "";
            } elseif ($permissions['write']['user_agency']) {
                $cond = "AND a.agency_id ".$agency;
            } elseif ($permissions['write']['user_district']) {
                $cond = "AND a.district_id ".$districts;
            } elseif ($permissions['write']['user']) {
                $cond = "AND a.added_by = $user_id
                    AND a.district_id ".$districts;
            }
        } elseif ($type == 'view') {
            if ($permissions['read']['user_agency']) {
                $cond = "AND a.agency_id ".$agency;
            } elseif ($permissions['read']['user'] || $permissions['read']['user_district']) {
                $cond = "AND a.added_by != $user_id
                    AND a.district_id ".$districts;
            }
        }

        $sql = "SELECT a.accomplishment_id,
          CONCAT('<a href=\"/manager/project.php?detail=true&id=', b.burn_project_id,'\">', b.project_name, '</a>') as \"Burn Project\",
          b.project_number as \"Burn Project Number\",
          CONCAT(br.start_date, ' to ', br.end_date) as \"Burn's Requested Dates\",
          CONCAT(DATE(a.start_datetime), ' to ', DATE(a.end_datetime)) as \"Accomplishment Ignition Dates\",
          a.manager_name as \"Manager Name\", a.manager_number  as \"Manager Number\",
          CONCAT('<span class=\"', s.class ,'\" onclick=\"Accomplishment.submitForm(', a.accomplishment_id , ')\">', s.name ,'</span>') as \"Form Status\",
          COALESCE(CONCAT('<span class=\"', c.class ,'\" data-toggle=\"tooltip\" title=\"Click to Check\" onclick=\"Accomplishment.validate(', a.accomplishment_id , ')\">', c.name ,'</span>'),'<span class=\"label label-default\">N/A</span>') as \"Form Completeness\",
          CONCAT('<span  class=\"label label-default\">', u.full_name, '</span>') as \"By\"
          FROM accomplishments a
          JOIN burn_projects b ON(a.burn_project_id = b.burn_project_id)
          JOIN burns br ON(a.burn_id = br.burn_id)
          JOIN accomplishment_completeness c ON(a.completeness_id = c.completeness_id)
          JOIN accomplishment_statuses s ON (a.status_id = s.status_id)
          JOIN users u ON(a.added_by = u.user_id)
          $pre_cond
          $cond
          ORDER BY a.location";

        $new_function = "Accomplishment.newForm($district_id)";

        if ($type == 'edit' && $permissions['write']['any']) {
            $table = show(array('sql'=>$sql,'pkey'=>'accomplishment_id','table'=>'accomplishments',
              'include_delete'=>true,'delete_function'=>'Accomplishment.deleteConfirmation',
              'include_view'=>true,'view_href'=>'?detail=true&id=@@',
              'edit_function'=>'Accomplishment.editConfirmation','new_function'=>$new_function,'paginate'=>true,
              'no_results_message'=>'There are no editable burn accomplishments associated with your user. An approved burn request is required to submit accomplishments.',
              'no_results_class'=>'info'));
        } elseif ($type == 'view' && $permissions['read']['any']) {
            $table = show(array('sql'=>$sql,'pkey'=>'accomplishment_id','table'=>'accomplishments',
              'include_view'=>true,'view_href'=>'?detail=true&id=@@',
              'include_edit'=>false,'paginate'=>true,'include_delete'=>false,
              'no_results_message'=>'There are no viewable burn accomplishments associated with your district(s).',
              'no_results_class'=>'info'));
        }

        $html = $table['html'];

        return $html;
    }

    public function toolbar($page, $burn_id, $accomplishment_id)
    {
        /**
         *   Produces the standard accomplishment form toolbar.
         */


        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $toolbar_class = $this->form_id . "_tb";
        $btn_class = "btn-sm btn-default";

        if (isset($burn_id)) {
            $c_burn_id = ", $burn_id";
        }

        if (isset($accomplishment_id)) {
            $c_accomplishment_id = ", $accomplishment_id";
            $save_function = "Accomplishment.update($accomplishment_id)";
            $saveUtah_function = "Accomplishment.updateUtah($accomplishment_id)";
        } else {
            $save_function = "Accomplishment.save()";
            $saveUtah_function = "Accomplishment.saveUtah()";
        }

        if ($page == 1) {
            $html = "<div class=\"$toolbar_class pull-right btn-group\">
              <button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
              <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Back</button>
              <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(2".$c_burn_id."{$c_accomplishment_id})\">Forward</button>
              <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>
            </div>";
        // } elseif ($page == 2) {
        //     $html = "<button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
        //       <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(1".$c_burn_id."{$c_accomplishment_id})\">Back</button>
        //       <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(3".$c_burn_id."{$c_accomplishment_id})\">Forward</button>
        //       <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        // } elseif ($page == 3) {
        //     $html = "<button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
        //       <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(2".$c_burn_id."{$c_accomplishment_id})\">Back</button>
        //       <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(4".$c_burn_id."{$c_accomplishment_id})\">Forward</button>
        //       <button class=\"btn $btn_class\" onclick=\"$save_function\">Save</button>";
        } elseif ($page == 2) {
            $html = "<button class=\"btn $btn_class\" onclick=\"cancel_form(true)\">Cancel</button>
              <button class=\"btn $btn_class\" onclick=\"Accomplishment.showForm(1".$c_burn_id."{$c_accomplishment_id})\">Back</button>
              <button class=\"btn $btn_class\" disabled=\"disabled\" onclick=\"\">Forward</button>
              <button class=\"btn $btn_class\" onclick=\"$save_function\">Save Draft</button>
              <button class=\"btn $btn_class\" onclick=\"$saveUtah_function\">Submit</button>
              <!--<button class=\"btn $btn_class\" onclick=\"Accomplishment.submitForm($accomplishment_id)\">Submit</button>-->";
        }

        return $html;
    }

    public function form($page, $burn_id, $accomplishment_id)
    {
        /**
         *  Produces the basic accomplishment HTML form.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','interface');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $state_id = 2;
        $agency_id = $_SESSION['user']['agency_id'];

        // Get some burn plan information.
        if (isset($burn_id)) {
            $temp_pre_burn = new \Manager\PreBurn($this->db);
            $temp_burn = new \Manager\Burn($this->db);

            $burn = $temp_burn->get($burn_id);
            $burn_id = $burn['burn_id'];
            $pre_burn_id = $burn['pre_burn_id'];
            $location = $burn['location'];
            $district_id = $burn['district_id'];
            $burn_project_id = $burn['burn_project_id'];

            $pre_burn = $temp_pre_burn->get($pre_burn_id);
            $primary_ert_id = $pre_burn['primary_ert_id'];
            $alternate_primary_ert = $pre_burn['alternate_primary_ert'];
            $primary_ert_pct = $pre_burn['primary_ert_pct'];
            $secondary_ert_id = $pre_burn['secondary_ert_id'];
            $alternate_secondary_ert = $pre_burn['alternate_secondary_ert'];
            $manager_name = $pre_burn['manager_name'];
            $manager_number = $pre_burn['manager_number'];
            $manager_cell = $pre_burn['manager_cell'];
        }

        if (isset($accomplishment_id)) {
            $values = $this->get($accomplishment_id);
            extract($values);
        }

        if ($page == 1) {
            $title = "Burn Accomplishment (1/2)";

            $fieldset_id = $this->form_id . "_fs1";

            $ctls = array(
                'agency_id'=>array('type'=>'hidden2','value'=>$agency_id),
                'district_id'=>array('type'=>'hidden2','value'=>$district_id),
                'burn_project_id'=>array('type'=>'hidden2','label'=>'Burn Project Id','value'=>$burn_project_id),
                'pre_burn_id'=>array('type'=>'hidden2','label'=>'Pre-Burn Id','value'=>$pre_burn_id),
                'burn_id'=>array('type'=>'hidden2','label'=>'Burn Id','value'=>$burn_id),
                'location'=>array('type'=>'hidden2','label'=>'Location','value'=>$location),
                'clearing_index'=>array('type'=>'textbox','label'=>'Clearing Index Was','value'=>$clearing_index,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'state_id'=>array('type'=>'combobox','label'=>'Burn Completeness','value'=>$state_id,'enable_help'=>true,'table_id'=>$this->db_table_id,'table'=>'accomplishment_states','fcol'=>'accomplishment_state_id','display'=>array('type','description')),
                'state_comment'=>array('type'=>'memo','label'=>'Comments','value'=>$state_comment,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'resume_date'=>array('type'=>'date','label'=>'Burn Resume Date (if Postponed)','value'=>$resume_date,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'wfu_updates'=>array('type'=>'boolean','label'=>'WFU','value'=>/*$wfu_updates*/false,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'wfu_remarks'=>array('type'=>'hidden2','label'=>'WFU Remarks','value'=>$wfu_remarks,'enable_help'=>true,'table_id'=>$this->db_table_id),
        //     );

        //     $append = "";
        // } elseif ($page == 2) {

        //     $title = "Burn Accomplishment (2/4)";

        //     $fieldset_id = $this->form_id . "_fs2";

        //     $ctls = array(
                'black_acres_change'=>array('type'=>'textbox','label'=>'New black acres','value'=>/*$black_acres_change*/0,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'total_year_acres'=>array('type'=>'hidden2','label'=>'Total Calendar year acres to date','value'=>/*$total_year_acres*/0,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'total_project_acres'=>array('type'=>'hidden2','label'=>'Total Project acres to date','value'=>/*$total_project_acres*/0,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_name'=>array('type'=>'hidden2','label'=>'Burn Manager Name','value'=>$manager_name,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_number'=>array('type'=>'hidden2','label'=>'Burn Manager Number','value'=>$manager_number,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_cell'=>array('type'=>'hidden2','label'=>'Burn Manager Cell','value'=>$manager_cell,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'manager_fax'=>array('type'=>'hidden2','label'=>'Burn Manager Fax','value'=>$manager_cell,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'start_datetime'=>array('type'=>'datetime','label'=>'Burn Start Date/Time','value'=>date("Y-m").'-'.(date("d")-1).' 09:00','enable_help'=>true,'table_id'=>$this->db_table_id),
                'end_datetime'=>array('type'=>'datetime','label'=>'Burn End Date/Time','value'=>date("Y-m").'-'.(date("d")-1).' 16:00','enable_help'=>true,'table_id'=>$this->db_table_id),
        //     );

        //     $append = "";
        // } elseif ($page == 3) {

        //     $title = "Burn Accomplishment (3/4)";

        //     $custom_fs = $this->fuelsFieldset($fuels);

        //     $append = "";
        // } elseif ($page == 4) {

        //     $title = "Burn Accomplishment (4/4)";

        //     $fieldset_id = $this->form_id . "_fs4";

        //     $ctls = array(
                'public_interest_id'=>array('type'=>'combobox','label'=>'Public interest regarding smoke','value'=>$public_interest_id,'table'=>'interest_levels','fcol'=>'interest_level_id','display'=>'name','order'=>'interest_level_id','enable_help'=>true,'table_id'=>$this->db_table_id),
                'day_vent_id'=>array('type'=>'combobox','label'=>'Daytime ventilation','value'=>'Good','table'=>'daytime_ventilations','fcol'=>'daytime_ventilation_id','display'=>'name','order'=>'daytime_ventilation_id','enable_help'=>true,'table_id'=>$this->db_table_id),
                'night_smoke_id'=>array('type'=>'combobox','label'=>'Nighttime ventilation','value'=>$night_smoke_id,'table'=>'nighttime_smoke','fcol'=>'nighttime_smoke_id','display'=>'name','order'=>'nighttime_smoke_id','enable_help'=>true,'table_id'=>$this->db_table_id),
                'swr_plan_met'=>array('type'=>'boolean','label'=>'Smoke Management Prescription/WFIP/Resource Benefit Plan met','value'=>true,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'primary_ert_id'=>array('type'=>'combobox','label'=>'Primary Emission Reduction Technique (ERT)','value'=>$primary_ert_id,'table'=>'emission_reduction_techniques','fcol'=>'emission_reduction_technique_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id),
                'alternate_primary_ert'=>array('type'=>'hidden2','label'=>'Primary Emission Reduction Technique (if Other)','value'=>$alternate_primary_ert,'enable_help'=>true,'table_id'=>$this->db_table_id),
                'primary_ert_pct'=>array('type'=>'text','label'=>'Primary ERT Percentage','value'=>$primary_ert_pct,'enable_help'=>true,'field_id'=>95),
                'secondary_ert_id'=>array('type'=>'combobox','label'=>'Secondary ERT (ERT)','value'=>$secondary_ert_id,'table'=>'emission_reduction_techniques','fcol'=>'emission_reduction_technique_id','display'=>'name','enable_help'=>true,'table_id'=>$this->db_table_id,'allownull'=>true),
                'alternate_secondary_ert'=>array('type'=>'hidden2','label'=>'Secondary Emission Reduction Technique (if Other)','value'=>$alternate_secondary_ert,'enable_help'=>true,'table_id'=>$this->db_table_id),
            );

            $append = "";
        } elseif ($page == 2) {
            $title = "Burn Accomplishment (2/2)";

            //$fieldset_id = $this->form_id . "_fs4";
             $custom_fs = $this->fuelsFieldset($fuels);

             $append = "";
            }




        if ($page == 1) {
            $html .= mkForm(array('id'=>$this->form_id,'controls'=>$ctls,'title'=>$title,'suppress_submit'=>true,'fieldset_id'=>$fieldset_id));
        } elseif ($page == 2) {
            $html .= $custom_fs;
        } else {
            $html .= mkFieldset(array('controls'=>$ctls,'title'=>$title,'id'=>$fieldset_id,'append'=>$append));
        }

        return $html;
    }

    private function fuelsFieldset($accomplishment_fuels)
    {
        /**
         *  Constructs the fuels table form fieldset.
         *  jQuery calcs off 'Black Acres'.change() only.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','interface');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $fieldset_id = $this->form_id . "_fs3";

        $fuels = fetch_assoc(
            "SELECT f.fuel_id, t.nffl_model, t.fuel_type, t.ton_per_acre, f.ef
            FROM fuels f
            JOIN fuel_types t ON(f.fuel_type_id = t.fuel_type_id)
            WHERE f.show_on_form = ?
            AND f.is_active = ?
            ORDER BY f.fuel_id;", array(true, true)
        );

        $fieldset = "<fieldset id=\"$fieldset_id\">
            <table class=\"table table-responsive table-condensed\">
                <col width=\"7.5%\">
                <col width=\"27.5%\">
                <col width=\"10%\">
                <col width=\"15%\">
                <col width=\"10%\">
                <col width=\"20%\">
                <col width=\"10%\">
                <thead>
                    <tr>
                        <th>NFFL Model</th>
                        <th>Description</th>
                        <th>Black Acres</th>
                        <th>Tons of Fuel Consumed/Acre (T/A)</th>
                        <th>Total Tons Consumed</th>
                        <th>Emission Coefficient (T PM/T of Fuel)</th>
                        <th>Total Tons PM</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($fuels as $key => $value) {
            $rid = "ffsr".uniqid();
            $input_base = str_replace(array(" ","/","\\"), "-", strtolower($value['fuel_type']))."-".$value['fuel_id'];

            extract($value);
            extract($this->getFuelById($accomplishment_fuels, $value['fuel_id']));

            if (empty($ton_per_acre)) {
                $ton_per_acre = $value['ton_per_acre'];
            }

            $rows .= "<tr id=\"{$rid}\">
                    <td>{$nffl_model}</td>
                    <td>{$fuel_type}</td>
                    <td><input class=\"form-control\" id=\"{$rid}_ba\" name=\"my[{$input_base}][ba]\" placeholder=\"Acres\" value=\"$black_acres\"></td>
                    <td><input class=\"form-control\" id=\"{$rid}_tpa\" name=\"my[{$input_base}][tpa]\" placeholder=\"Tons\" value=\"$ton_per_acre\"></td>
                    <td><input class=\"form-control\" id=\"{$rid}_tcons\" name=\"my[{$input_base}][tcons]\" placeholder=\"Tons\" value=\"$total_tons\" readonly></td>
                    <td id=\"{$rid}_ef\">{$ef}</td>
                    <td><input class=\"form-control\" id=\"{$rid}_te\" name=\"my[{$input_base}][te]\" placeholder=\"Tons\" value=\"$tons_emitted\" readonly></td>
                </tr>";

            $rscripts .= "<script>
                    function {$rid}_calc(variable) {
                        var ba = parseFloat($('#{$rid}_ba').val());
                        var tpa = parseFloat($('#{$rid}_tpa').val());
                        var tcons = parseFloat($('#{$rid}_tcons').val());
                        var ef = parseFloat($('#{$rid}_ef').text());
                        var te = parseFloat($('#{$rid}_te').val());

                        var nTcons = ba * tpa;
                        var nTpm = nTcons * ef;

                        if(variable == 'ba' || variable == 'tpa') {
                            if (tcons != nTcons) {
                                if (isNaN(nTcons)) {
                                    $('#{$rid}_tcons').val(0)
                                } else {
                                    $('#{$rid}_tcons').val(nTcons.toFixed(3));
                                }
                            }

                            if (te != nTpm) {
                                if (isNaN(nTpm)) {
                                    $('#{$rid}_te').val(0);
                                } else {
                                    $('#{$rid}_te').val(nTpm.toFixed(3));
                                }
                            }
                        }
                    }

                    $('#{$rid}_ba').change(function() {
                        {$rid}_calc('ba');
                    });

                    $('#{$rid}_tpa').change(function() {
                        {$rid}_calc('tpa');
                    });
                </script>";

        }

        $fieldset .= $rows;

        $fieldset .= "</tbody>";

        // Totals Row (unnecessary add-on feature)

        //$trow = "<tfoot>
        //            <td id=\"ffsr_totals\">
        //                <td>Totals</td>
        //                <td>All Fuel Types</td>
        //                <td id=\"ffsr_total_ba\"></td>
        //                <td id=\"ffsr_total_tpa\"></td>
        //                <td id=\"ffsr_total_tcons\"></td>
        //                <td id=\"ffsr_total_ef\"></td>
        //                <td id=\"ffsr_total_te\"></td>
        //            </tr>
        //        </tfoot>";

        //$tscript = "<script>
        //
        //    </script>";

        $fieldset .= "</table>";

        $fieldset .= $rscripts;

        $fieldset .= $tscript;

        return $fieldset;
    }

    private function getFuelById($accomplishment_fuels, $fuel_id)
    {
        /**
         *  Find the correct accomplishment.
         */

        foreach ($accomplishment_fuels as $value) {
            if ($value['fuel_id'] == $fuel_id) {
                return $value;
            }
        }
    }

    public function burnProjectSelector()
    {
        /**
         *  Construct a button list of valid burn plans for Burn submittal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency','admin'),'write','interface');
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
            "SELECT burn_project_id, project_name, project_number
            FROM burn_projects b
            $cond status_id = 4
            AND burn_project_id IN(SELECT burn_project_id FROM burns GROUP BY burn_project_id) ORDER BY project_name;");

        if ($select['error'] == true) {
            $html = "<div class=\"alert alert-danger\">
                This agency has no approved burn projects. Burn accomplishments can only be drafted for approved burn projects.
                </div>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        } else {
            $html = "<div style=\"min-height: 36px; max-height: 800px; overflow-x: scroll\">";

            foreach ($select as $value) {
                $small_font = "";
                if (strlen($value['project_name']) >= 24) {
                    $small_font = "style=\"font-size: 10px;\"";
                }
                $html .= "<button class=\"btn btn-default btn-block\" $small_font onclick=\"Accomplishment.newForm(".$value['burn_project_id'].")\">".$value['project_name']." - ".$value['project_number']."</button>";
            }

            $html .= "</div>";
        }

        return $html;
    }

    public function burnSelector($burn_project_id)
    {
        /**
         *    Returns HTML for the burn plan selection modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','interface');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $burns = fetch_assoc(
            "SELECT b.burn_id, b.start_date, b.end_date, b.location
            FROM burns b
            LEFT JOIN accomplishments a ON (b.burn_id = a.burn_id)
            WHERE b.status_id = 5
            AND b.burn_project_id = ? ORDER BY b.start_date DESC;", $burn_project_id
        );
        $burn_project = fetch_row("SELECT project_name, project_number FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        $html = "<div style=\"min-height: 36px; max-height: 400px; overflow-x: scroll\">";

        if ($burns['error']) {
            $html .= "<p class=\"text-center\">This approved burn plan has no approved burn requests that aren't completed. Burn accomplishments can only be submitted for approved burn requests.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
            $html .= "<p class=\"text-center\">Select a burn request by its date range. Only approved burn requests for the <strong>".$burn_project['project_number']."</strong> - ".$burn_project['project_name']." are specified.</p>";
            foreach ($burns as $value) {
                $html .= "<button class=\"btn btn-default btn-block\" onclick=\"Accomplishment.newForm(".$burn_project_id.", ".$value['burn_id'].")\"><strong>Burn:</strong> ".$value['start_date']." - ".$value['end_date']."</button>";
            }
            $html .= "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";
        }

        $html .= "</div>";

        return $html;
    }

    public function submittalForm($accomplishment_id, $use_Close = TRUE)
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

        $status_id = fetch_one("SELECT status_id FROM accomplishments WHERE accomplishment_id = ?;", $accomplishment_id);

        $validate = $this->validateRequired($accomplishment_id);
        $valid = $validate['valid'];

        if ($status_id == $this->approved_id) {
            $html = "<div>
                <p class=\"text-center\">The Accomplishment has already been approved.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } elseif ($status_id== $this->revision_requested_id) {
            if ($valid) {
                if ($this->reviewCheck($accomplishment_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The Burn Request Accomplishment is valid and has been revised since the last request for revision. To ensure minimal processing time, please make sure the revision addresses all review comments before re-submitting to Utah.gov.</p>
                        <a href=\"?burn=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-success btn-block\" onclick=\"Accomplishment.submitToUtah($accomplishment_id)\">Re-submit <strong>$burn_name</strong> to Utah.gov</button>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Burn Request Accomplishment has not been revised since the last request for revision. Please revise the burn according to latest review comment.</p>
                        <a href=\"?detail=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            } else {
                if ($this->reviewCheck($accomplishment_id)) {
                    $html = "<div>
                        <p class=\"text-center\">The Burn Request Accomplishment has been revised since the last request for revision but is not valid.</p>
                        <a href=\"?burn=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                } else {
                    $html = "<div>
                        <p class=\"text-center\">The Burn Request Accomplishment has not been revised since the last request for revision and is not valid.</p>
                        <a href=\"?burn=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                        <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>";
                }
            }
        } elseif ($status_id == $this->under_review_id) {
            $html = "<div>
                    <p class=\"text-center\">The Burn Request Accomplishment is currently being reviewed by Utah.gov. Please check back for any requested revisions, or the plans approval.</p>
                    <a href=\"?burn=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>
                    <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
        } else {
            if ($valid) {
                $html = "<div>
                    <p class=\"text-center\">The draft Accomplishment is complete and can be submitted to Utah.gov.</p>
                    <button class=\"btn btn-success btn-block\" onclick=\"Accomplishment.submitToUtah($accomplishment_id)\">Submit <strong>$burn_name</strong> to Utah.gov</button>";
            } else {
                $html = "<div>
                        <p class=\"text-center\">The Burn Request Accomplishment is not complete. Please ensure all required fields are filled in.</p>
                        <a href=\"?burn=true&id=$accomplishment_id\" role=\"button\" class=\"btn btn-default btn-block\">View Burn Request Details</a>";
            }
            if($use_Close) {
                $html .= "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
                </div>";
            }
            else {
                $html .= "</div>";
            }

        }

        return $html;
    }

    public function submitUtah($accomplishment_id)
    {
        /**
         *  Determine if the accomplishment is valid, and change it to submitted/pending.
         *  Add a valid burn number when submitted.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write','modal');
        if ($permissions['deny']) {
            echo $permissions['message'];
            exit;
        }

        $valid = $this->validateRequired($accomplishment_id);
        $valid = $valid['valid'];
        $now = now();
        $submitted_by = $_SESSION['user']['id'];

        // Check if its submitted already
        if ($valid == true) {
            $status = $this->getStatus($accomplishment_id);
            $status_id = $status['status_id'];
        }

        // Not submitted, and valid. Submit to Utah.gov:
        if ($valid == true && in_array($status_id, array($this->draft_id,$this->revision_requested_id))) {
            // The accomplishment is valid. Change its status to "Under Review"
            $last_submitted_by = fetch_one("SELECT submitted_by FROM accomplishments WHERE accomplishment_id = ?;", $accomplishment_id);
            if(!empty($last_submitted_by)) {
                $submitted_by = $last_submitted_by;
            }
            $update_sql = $this->pdo->prepare("UPDATE accomplishments SET status_id = ?, submitted_on = ?, submitted_by = ? WHERE accomplishment_id = ?;");
            $update_sql->execute(array($this->under_review_id, $now, $submitted_by, $accomplishment_id));
            if ($update_sql->rowCount() > 0) {
                $result['message'] = status_message("The Burn Request Accomplishment has been submitted to Utah.gov.", "success");

                $notify = new \Info\Notify($this->db);
                $notify->accomplishmentSubmitted($accomplishment_id);
            } else {
                $result['message'] = status_message("The Burn Request Accomplishment is valid, but failed to submit.", "error");
            }
        } elseif (in_array($status_id, array($this->under_review_id, $this->completed_id))) {
            $result['message'] = status_message("The Burn Request Accomplishment was already submitted.", "warning");
        } else {
            $result['message'] = status_message("The Burn Request Accomplishment must be validated before submitting.", "error");
        }

        return $result;
    }

    public function overviewPage()
    {

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        if ($permissions['read']['deny']) {
            exit;
        }

        $args = array('burn_project_id'=>null,'district_id'=>null,'agency_id'=>$_SESSION['user']['agency_id']);
        extract(merge_args(func_get_args(), $args));

        if (isset($burn_project_id)) {
            $edit_table = $this->show(array('type'=>'edit','burn_project_id'=>$burn_project_id));
            $view_table = $this->show(array('type'=>'view','burn_project_id'=>$burn_project_id));
            $map = $this->getAllMap(array('burn_project_id'=>$burn_project_id));
            $return_link = "<a href=\"/manager/accomplishment.php\">Return to Overview</a>";
            $burn = ", ".$burn_project_id;
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
                <h3>Overview <small>Form 5: Burn Accomplishments</small></h3>
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
                        <button class=\"btn btn-sm btn-default\" onclick=\"Accomplishment.newForm($district_id $burn)\">New Accomplishment</button>
                    </div>
                </div>
            </div>";
        }

        return $html;
    }

    public function detailPage($accomplishment_id)
    {
        /**
         *  Constructs a Burn Plan Detail Page.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $burn_permissions = $this->checkAccomplishmentPermissions($_SESSION['user']['id'], $accomplishment_id, $permissions);

        // Statics
        $return_link = "<a href=\"/manager/accomplishment.php\">Return to Overview</a>";

        if ($burn_permissions['allow']) {
            // Get the burn.
            $accomplishment = $this->get($accomplishment_id);

            // Construct the title.
            if (isset($accomplishment['burn_project']['project_name']) && isset($accomplishment['burn_project']['project_number'])) {
                $title = $accomplishment['burn_project']['project_name']." / ".$accomplishment['burn_project']['project_number']." / <small>".$accomplishment['request_dates']['start_date']." to ". $accomplishment['request_dates']['end_date']."</small>";
            } elseif (isset($accomplishment['burn_project']['project_name']) ) {
                $title = $accomplishment['burn_project']['project_name']." / <small>".$accomplishment['request_dates']['start_date']." to ". $accomplishment['request_dates']['end_date']."</small>";
            } else {
                $title = "Accomplishment";
            }

            if (in_array($accomplishment['status_id'], $this->edit_status_id) && $burn_permissions['write']) {
                if ($accomplishment['status_id'] == $this->under_review_id) {
                    $submit_text = "";
                } elseif ($accomplishment['status_id'] <= $this->revision_requested_id) {
                    $submit_text = "<button class=\"btn btn-sm btn-default\" onclick=\"Accomplishment.submitForm($accomplishment_id)\">Submit</button>";
                } else {
                    $submit_text = "<button class=\"btn btn-sm btn-default\" onclick=\"Accomplishment.submitForm($accomplishment_id)\">Re-submit</button>";
                }

                $toolbar = "<div class=\"btn-group pull-right\">
                        $submit_text
                        <button class=\"btn btn-sm btn-default\" onclick=\"Accomplishment.editConfirmation($accomplishment_id)\">Edit Accomplishment</button>
                        <a href=\"/pdf/accomplishment.php?id={$accomplishment_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                    </div>";
            } else {
                $toolbar = "<div class=\"btn-group pull-right\">
                        <button class=\"btn btn-sm btn-default\" disabled>Submit</button>
                        <button class=\"btn btn-sm btn-default\" disabled>Edit Accomplishment</button>
                        <a href=\"/pdf/accomplishment.php?id={$accomplishment_id}\"<button class=\"btn btn-sm btn-default\">PDF</button></a>
                    </div>";
            }

            // Get HTML blocks.
            $status = $this->getStatusLabel($accomplishment);
            $map = $this->getMap($accomplishment);
            $unit = $this->tablifyFields($accomplishment, 'unit_info');
            $fuels = $this->tablifyFuels($accomplishment_id);
            $contacts = $this->getContacts($accomplishment_id);
            $uploads = $this->getUploads($accomplishment_id);
            $reviews = $this->getReviews($accomplishment_id);

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
                    <h4>Accomplishment Info</h4>
                    <hr>
                    $map
                    <br>
                    $unit
                </div>
                <div class=\"col-sm-4\">
                    $toolbar
                    $contacts
                    $reviews
                    $uploads
                </div>
            </div>
            <div class=\"row\">
                <div class=\"col-sm-12\">
                    <hr>
                    <h4>Accomplishment Fuels</h4>
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

    public function pdfPage($accomplishment_id)
    {
        /**
         *  Generate the Details Page in an mPDF compatible manner
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));
        $accomp_permissions = $this->checkAccomplishmentPermissions($_SESSION['user']['id'], $accomplishment_id, $permissions);

        if ($accomp_permissions['allow']) {
            // Get the Burn.
            $accomplishment = $this->get($accomplishment_id);

            // Get HTML blocks
            $table = $this->tablifyFields($accomplishment, 'unit_info', true);
            $fuels = $this->tablifyFuels($accomplishment_id, true);
            $contacts = $this->getContacts($accomplishment_id);
            $reviews = $this->getReviews($accomplishment_id);

            // Static fields.
            $project_name = $accomplishment['burn_project']['project_name'];
            $project_number = $accomplishment['burn_project']['project_number'];

            // Build the map.
            $location = str_replace(array('(',')',' '), '', $accomplishment['location']);
            $color = str_replace('#', '0x', $this->retrieveStatus($accomplishment['status_id'])['color']);
            $label = substr($this->retrieveStatus($accomplishment['status_id'])['title'], 0, 1);
            $static_map = "http://maps.googleapis.com/maps/api/staticmap?size=640x360&zoom=14&center=$location&markers=color:$color%7Clabel:$label%7C$location";

            // Get HTML blocks.
            $html = "
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"50%\">
                        <col width=\"49%\">
                        <tr style=\"border: 0.15em solid black;\">
                            <td style=\"width: 50%\">Form 5: Burn Accomplishment - Active: <strong>{$accomplishment['start_datetime']} - {$accomplishment['end_datetime']}</strong></td>
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
                    <table style=\"width: 100%; vertical-align: top; font-size: 9pt;\">
                        <col width=\"99%\">
                        <tr style=\"\">
                            <td style=\"width: 99%;\">
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

    public function save($accomplishment)
    {
        /**
         *  Submits an accomplishment.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }
        
        $accomplishment_id = $this->saveAccomplishment($accomplishment);
        if($accomplishment_id >= 0) {
            $result['message'] = status_message("The burn accomplishment was saved.", "success");
        }
        else {
            $result['error'] = true;
            $result['message'] = status_message("The burn accomplishment failed to save, please try again.", "error");
        }

        $this->validateRequired($accomplishment_id);
        return $result;
    }
    
    public function saveUtah($accomplishment)
    {
        /**
         *  Submits to Utah.gov an accomplishment.
         */
        
        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }
        
        $accomplishment_id = $this->saveAccomplishment($accomplishment);
        if($accomplishment_id == -1) {
            $result['error'] = true;
            $result['message'] = status_message("The burn accomplishment failed to save, please try again.", "error");
            return $result;
        }
        
        $this->validateRequired($accomplishment_id);
        $html = $this->submittalForm($accomplishment_id, FALSE);
        return $html;
    }
    
    private function saveAccomplishment($accomplishment) {
        // Defaults
        $added_by = $_SESSION['user']['id'];
        $added_on = now();
        $status_id = 1;
        
        // Extract the broadcast data.
        extract(prepare_values($accomplishment));
        
        // Save the Accomplishment
        $accomplishment_sql = $this->pdo->prepare(
            "
            INSERT INTO accomplishments (agency_id, district_id, burn_project_id, pre_burn_id, burn_id, added_on, added_by, location, clearing_index, state_id, state_comment, resume_date, wfu_updates, wfu_remarks, black_acres_change, total_year_acres, total_project_acres, manager_name, manager_number, manager_cell, manager_fax, start_datetime, end_datetime, public_interest_id, day_vent_id, night_smoke_id, swr_plan_met, primary_ert_id, alternate_primary_ert, primary_ert_pct, secondary_ert_id, alternate_secondary_ert, status_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            "
            );
        $accomplishment_sql = execute_bound($accomplishment_sql, array($agency_id, $district_id, $burn_project_id, $pre_burn_id, $burn_id, $added_on, $added_by, $location, $clearing_index, $state_id, $state_comment, $resume_date, $wfu_updates, $wfu_remarks, $black_acres_change, $total_year_acres, $total_project_acres, $manager_name, $manager_number, $manager_cell, $manager_fax, $start_datetime, $end_datetime, $public_interest_id, $day_vent_id, $night_smoke_id, $swr_plan_met, $primary_ert_id, $alternate_primary_ert, $primary_ert_pct, $secondary_ert_id, $alternate_secondary_ert, $status_id));
        if ($accomplishment_sql->rowCount() > 0) {
            $get_accomplishment_sql = $this->pdo->prepare("SELECT accomplishment_id FROM accomplishments WHERE added_on = ? AND added_by = ? AND burn_id = ?;");
            $get_accomplishment_sql->execute(array($added_on, $added_by, $burn_id));
            if ($get_accomplishment_sql->rowCount() > 0) {
                $accomplishment_id = $get_accomplishment_sql->fetchColumn(0);
            }
        } else {
            $accomplishment_id = -1;
        }
        
        $fuel_keys = fetch_assoc(
            "SELECT fuel_id, CONCAT(fuel_type, '-', fuel_id) as fuel_type, t.fuel_type_id
            FROM fuel_types t
            JOIN fuels f ON(f.fuel_type_id = t.fuel_type_id)
            WHERE show_on_form = true
            AND is_active = true
            ORDER BY fuel_id;"
            );
        
        foreach($fuel_keys as $value) {
            $form_key = str_replace(array(" ","/","\\"), "-", strtolower($value['fuel_type']));
            $fuel_id = $value['fuel_id'];
            
            extract($accomplishment[$form_key]);
            
            $fuel_sql = $this->pdo->prepare("INSERT INTO accomplishment_fuels (accomplishment_id, fuel_id, black_acres, ton_per_acre, total_tons, tons_emitted) VALUES (?, ?, ?, ?, ?, ?);");
            $fuel_sql = execute_bound($fuel_sql, array($accomplishment_id, $fuel_id, $ba, $tpa, $tcons, $te));
            
            /** Non-form fuel inserts. **/
            $hidden_fuels = fetch_assoc("SELECT * FROM fuels WHERE fuel_type_id = ? AND is_active = true and show_on_form = false;", $value['fuel_type_id']);
            
            if (!$hidden_fuels['error']) {
                foreach ($hidden_fuels as $hfuel) {
                    $hte = $tcons * $hfuel['ef'];
                    
                    $hfuel_sql = $this->pdo->prepare("INSERT INTO accomplishment_fuels (accomplishment_id, fuel_id, black_acres, ton_per_acre, total_tons, tons_emitted) VALUES (?, ?, ?, ?, ?, ?);");
                    $hfuel_sql = execute_bound($fuel_sql, array($accomplishment_id, $hfuel['fuel_id'], $ba, $tpa, $tcons, $hte));
                }
            }
        }
        return $accomplishment_id;
    }

    public function ownerChangeForm($accomplishment_id)
    {
        /**
         *  Change Ownership Form
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $burn = fetch_row("SELECT COALESCE(submitted_by, updated_by, added_by) as user_id, agency_id FROM accomplishments WHERE accomplishment_id = ?", $accomplishment_id);
        $user_sql = "SELECT user_id, email, full_name FROM users;";
        $district_sql = "SELECT district_id, CONCAT(identifier, ' - ', district) as name FROM districts;";

        $ctls = array(
            'user_id'=>array('type'=>'combobox','label'=>'New Accomplishment Owner','fcol'=>'user_id','display'=>'email','sql'=>$user_sql,'value'=>$burn['user_id']),
            'district_id'=>array('type'=>'combobox','label'=>'New Designation','fcol'=>'district_id','display'=>'name','sql'=>$district_sql,'value'=>$burn['district_id'])
        );

        $html = mkForm(array('theme'=>'modal','id'=>'owner-change-form','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));

        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"Accomplishment.ownerChange({$accomplishment_id})\">Change Owner</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Cancel</button>
                    </div>
                </div>";

        return $html;
    }

    public function ownerChange($accomplishment_id, $user_id, $district_id)
    {
        /**
         *  Change The Burn Owner
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system'), 'write', 'modal');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $status_id = $this->getStatus($accomplishment_id);
        $agency_id = fetch_one("SELECT agency_id FROM users WHERE user_id = ?", $user_id);

        if ($status_id['status_id'] >= $this->approved_id) {
            $change = $this->pdo->prepare("UPDATE accomplishments SET added_by = ?, updated_by = ?, submitted_by = ?, agency_id = ?, district_id = ? WHERE accomplishment_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $user_id, $agency_id, $district_id, $accomplishment_id));
        } else {
            $change = $this->pdo->prepare("UPDATE accomplishments SET added_by = ?, updated_by = ?, agency_id = ?, district_id = ? WHERE accomplishment_id = ?");
            $change = execute_bound($change, array($user_id, $user_id, $agency_id, $district_id, $accomplishment_id));
        }

        if ($change->rowCount() > 0) {
            $html = status_message("The burn owner has successfully been changed.", "success");
        } else {
            $html = status_message("The burn owner change was not successful.", "error");
        }

        return null;
    }

    public function deleteConfirmation($accomplishment_id)
    {
        /**
         *  Return the delete confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn = fetch_row("SELECT a.burn_project_id,
          COALESCE(DATE(a.start_datetime), b.start_date) as start_date,
          COALESCE(DATE(a.end_datetime), b.end_date) as end_date,
          a.status_id
          FROM accomplishments a
          JOIN burns b ON(a.burn_id = b.burn_id)
          WHERE accomplishment_id = ?",
          $accomplishment_id);
        $burn_project = fetch_row(
          "SELECT project_name, project_number
          FROM burn_projects WHERE burn_project_id = ?;",
          $burn['burn_project_id']);

        if ($burn['status_id'] > 1) {
            $html = "<div>
                <p class=\"text-center\">The burn request is completed and cannot be deleted.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete <strong>{$burn_project['project_number']} - {$burn['start_date']} to {$burn['end_date']}</strong>?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"Accomplishment.deleteRecord($accomplishment_id)\">Delete Accomplishment</button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }
    
    public function updateUtah($accomplishment, $accomplishment_id)
    {
        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }
        
        $this->update($accomplishment, $accomplishment_id);
        $html = $this->submittalForm($accomplishment_id, FALSE);
        return $html;
    }

    public function update($accomplishment, $accomplishment_id)
    {
        /**
         *  Update the burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $updated_by = $_SESSION['user']['id'];

        // Extract the old values
        $original = $this->get($accomplishment_id);
        extract($original);

        // Overwrite variables with updated.
        extract(prepare_values($accomplishment));

        // Update the burn.
        $accomplishment_sql = $this->pdo->prepare(
            "UPDATE accomplishments SET updated_by = ?, location = ?,
            clearing_index = ?, state_id = ?, state_comment = ?,
            resume_date = ?, wfu_updates = ?, wfu_remarks = ?,
            black_acres_change = ?, total_year_acres = ?,
            total_project_acres = ?, manager_name = ?, manager_number = ?,
            manager_cell = ?, manager_fax = ?, start_datetime = ?,
            end_datetime = ?, public_interest_id = ?, day_vent_id = ?,
            night_smoke_id = ?, swr_plan_met = ?, primary_ert_id = ?,
            alternate_primary_ert = ?, primary_ert_pct = ?,
            secondary_ert_id = ?, alternate_secondary_ert = ?
             WHERE accomplishment_id = ?;"
        );
        $accomplishment_sql = execute_bound(
          $accomplishment_sql,
          array($updated_by, $location, $clearing_index, $state_id,
          $state_comment, $resume_date, $wfu_updates, $wfu_remarks,
          $black_acres_change, $total_year_acres, $total_project_acres,
          $manager_name, $manager_number, $manager_cell, $manager_fax,
          $start_datetime, $end_datetime, $public_interest_id, $day_vent_id,
          $night_smoke_id, $swr_plan_met, $primary_ert_id,
          $alternate_primary_ert, $primary_ert_pct, $secondary_ert_id,
          $alternate_secondary_ert, $accomplishment_id)
        );

        $fuel_keys = fetch_assoc(
            "SELECT fuel_id, CONCAT(fuel_type, '-', fuel_id) as fuel_type,
            t.fuel_type_id
            FROM fuel_types t
            JOIN fuels f ON(f.fuel_type_id = t.fuel_type_id)
            WHERE show_on_form = true
            AND is_active = true
            ORDER BY fuel_id;"
        );

        $insert_count = 0;

        if (array_key_assoc_list_exists_str($accomplishment, $fuel_keys, 'fuel_type')) {
            // Delete previous fuels.
            $many = $this->deleteManyMany($accomplishment_id);

            foreach($fuel_keys as $value) {
                $form_key = str_replace(array(" ","/","\\"), "-", strtolower($value['fuel_type']));
                $fuel_id = $value['fuel_id'];

                extract($accomplishment[$form_key]);

                $fuel_sql = $this->pdo->prepare("INSERT INTO accomplishment_fuels (accomplishment_id, fuel_id, black_acres, ton_per_acre, total_tons, tons_emitted) VALUES (?, ?, ?, ?, ?, ?);");
                $fuel_sql = execute_bound($fuel_sql, array($accomplishment_id, $fuel_id, $ba, $tpa, $tcons, $te));

                /** Non-form fuel inserts. **/
                $hidden_fuels = fetch_assoc("SELECT * FROM fuels WHERE fuel_type_id = ? AND is_active = true AND show_on_form = false;", $value['fuel_type_id']);

                if (!$hidden_fuels['error']) {
                    foreach ($hidden_fuels as $hfuel) {
                        $hte = $tcons * $hfuel['ef'];

                        $hfuel_sql = $this->pdo->prepare("INSERT INTO accomplishment_fuels (accomplishment_id, fuel_id, black_acres, ton_per_acre, total_tons, tons_emitted) VALUES (?, ?, ?, ?, ?, ?);");
                        $hfuel_sql = execute_bound($fuel_sql, array($accomplishment_id, $hfuel['fuel_id'], $ba, $tpa, $tcons, $hte));
                        $insert_count++;
                    }
                }
            }
        }

        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message("The Burn Request was updated.", "success");
        }

        $this->validateRequired($accomplishment_id);

        return $result;
    }

    public function editConfirmation($accomplishment_id)
    {
        /**
         *  Return the edit confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $html = "<div>
            <p class=\"text-center\">The burn request is a status that cannot be edited.</p>
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
        </div>";

        return $html;
    }

    public function delete($accomplishment_id)
    {
        /**
         *  Delete the burn and all associated many-many
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        // Delete the erts.
        $many = $this->deleteManyMany($accomplishment_id);

        $accomplishment_sql = $this->pdo->prepare("DELETE FROM accomplishments WHERE accomplishment_id = ?;");
        $accomplishment_sql->execute(array($accomplishment_id));
        if ($accomplishment_sql->rowCount() > 0 && $many) {
            $result['message'] = status_message("The Accomplishment was deleted.", "success");
        } elseif ($accomplishment_sql->rowCount() > 0 && $many == false) {
            $result['error'] = true;
            $result['message'] = status_message("The Accomplishment was deleted, but associated emission reduction techniques were not!", "error");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Accomplishment was not deleted.", "error");
        }

        return $result;
    }

    private function deleteManyMany($accomplishment_id)
    {
        /**
         *  Delete associated many to many (liners).
         *  (Cascade for Update as well).
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $select = fetch_one("SELECT SUM(accomplishment_fuel_id) FROM accomplishment_fuels WHERE accomplishment_id = ?;", $accomplishment_id);

        $delete = $this->pdo->prepare("DELETE FROM accomplishment_fuels WHERE accomplishment_id = ?;");
        $delete->execute(array($accomplishment_id));
        if ($delete->rowCount() > 0 && $select['error'] != true) {
            // All originally selected rows are now deleted.
            return true;
        }

        return false;
    }

    public function completeForm($accomplishment_id)
    {
        /**
         *  Return the complete confirmation modal.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn = fetch_row("SELECT burn_project_id, location, status_id FROM accomplishments WHERE accomplishment_id = $accomplishment_id");
        $burn_project = fetch_row("SELECT burn_number FROM burn_projects WHERE burn_project_id = ".$burn['burn_project_id'].";");

        if ($burn['status_id'] > 1) {
            $html = "<div>
                <p class=\"text-center\">The burn request already completed.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">Are you sure you want to mark this accomplishment as completed? This will officially submit it, allowing no further edits.</p>
                <button class=\"btn btn-success btn-block\" onclick=\"Accomplishment.complete($accomplishment_id)\">Complete <strong>".$burn_project['burn_number']." - ".$burn['location']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function complete($accomplishment_id)
    {
        /**
         *  Mark the accomplishment as completed.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'),'write');
        if ($permissions['deny']) {
            exit;
        }

        $burn_id = fetch_one("SELECT burn_id FROM accomplishments WHERE accomplishment_id = $accomplishment_id");
        $valid = fetch_one("SELECT completeness_id FROM accomplishments WHERE accomplishment_id = $accomplishment_id");

        if ($valid > 1) {
            $complete_id = 2;
            $completed_by = $_SESSION['user']['id'];
            $now = now();

            $complete = $this->pdo->prepare("UPDATE accomplishments SET completed_by = ?, completed_on = ?, status_id = ? WHERE accomplishment_id = ?;");
            $complete->execute(array($completed_by, $now, $complete_id, $accomplishment_id));

            $dbcomplete = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?;");
            $dbcomplete->execute(array(7, $burn_id));

            if ($complete->rowCount() > 0) {
                $result['message'] = status_message("The Accomplishment is now marked as completed.", "success");
            } else {
                $result['error'] = true;
                $result['message'] = status_message("The Accomplishment was not marked as completed.", "error");
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The Accomplishment does not have all required fields.", "error");
        }


        return $result;
    }

    public function validateRequired($accomplishment_id)
    {
        /**
         *  Validates a saved burn for required fields.
         */

        // The missing value count.
        $count = 0;
        $missing_msg = "The following required fields are missing in this Accomplishment:<br><br>";

        // Get the burn.
        $accomplishment = $this->get($accomplishment_id);

        // Check if the burn many-many fields have at least one record.
        if (!is_array($accomplishment['fuels'])) {
            $count++;
            $missing_msg .= "No associated fuels<br>";
        }

        // Check the base values.
        $accomplishment_required = array('clearing_index'=>'Clearing Index','state_id'=>'Burn Completeness',
            'wfu_updates'=>'WFU Update Solution','black_acres_change'=>'Updated Black Acres Change',
            'total_year_acres'=>'Total Acres Year to Date','total_project_acres'=>'Total Project Acres',
            'manager_name'=>'Manager Name','manager_number'=>'Manager Number','manager_cell'=>'Manager Cell',
            'manager_fax'=>'Manager Fax','start_datetime'=>'Burn Start Date/Time','end_datetime'=>'Burn End Date/Time',
            'public_interest_id'=>'Public Interest Level','day_vent_id'=>'Daytime Ventilation Status',
            'night_smoke_id'=>'Nighttime Smoke Status','swr_plan_met'=>'Smoke Management Prescription/WFIP/Resource Plan',
            'primary_ert_id'=>'Primary ERT','primary_ert_pct'=>'Primary ERT Percentage'/*,'secondary_ert_id'=>'Secondary ERT'*/
        );

        foreach ($accomplishment_required as $key => $value) {
            if (is_null($accomplishment[$key])) {
                $count++;
                $missing_msg .= "No ".$value."<br>";
            }
        }

        // Update the burn plan with its completeness status.
        $update_sql = $this->pdo->prepare("UPDATE accomplishments SET completeness_id = ? WHERE accomplishment_id = $accomplishment_id");

        if ($count == 0) {
            // Update to valid. No missing was counted.
            $update_sql->execute(array(2));
            $result['valid'] = true;
            $result['message'] = modal_message("All required Accomplishment info is filled out.", "success").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        } else {
            $update_sql->execute(array(1));
            $result['valid'] = false;
            $result['message'] = modal_message($missing_msg, "error").
            "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>";
        }

        return $result;
    }

    protected function tablifyFields($accomplishment, $section, $pdf = false)
    {
        /**
         *  Makes a table rows of the fields list.
         *  Assumes a default get burn plan array.
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

        $accomplishment_id = $accomplishment['accomplishment_id'];

        if ($section == 'unit_info') {
            $title = "Unit Information";
            $value_array = $accomplishment;
            $fields_array = array('location', 'clearing_index', 'state_id', 'state_comment', 'resume_date',
                'wfu_updates', 'wfu_remarks', 'black_acres_change', 'total_year_acres', 'total_project_acres',
                'start_datetime', 'end_datetime', 'public_interest_id', 'day_vent_id', 'night_smoke_id',
                'swr_plan_met', 'primary_ert_id', 'alternate_primary_ert', 'primary_ert_pct', 'secondary_ert_id',
                'alternate_secondary_ert'
            );
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

            if ($value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
                $value = "Not Specified";
            }

            if ($reference['boolean'] == true) {
                if ($value < 1) {
                    $value = "False";
                } else {
                    $value = "True";
                }
            }

            if (!isset($reference['field_id']) && isset($value)) {
                $reference['field_id'] = fetch_one("SELECT field_id FROM fields WHERE table_id = ? AND `column` = ?", array($this->db_table_id, $key));
            }

            if (isset($reference['sql']) && isset($value)) {
                $value = fetch_one($reference['sql'] . "?", $value);
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

    protected function tablifyFuels($accomplishment_id, $pdf = false)
    {
        /**
         *  Returns fuels table.
         */

        $sql = "SELECT nffl_model as \"NFFL Model\", t.fuel_type as \"Fuel Type\", ROUND(COALESCE(a.black_acres, 0), 3) as \"Black Acres\",
            ROUND(COALESCE(a.ton_per_acre, 0), 3) as \"Tons of Fuel Consumed/Acre (T/A)\", ROUND(COALESCE(a.total_tons, 0), 3) as \"Total Tons Consumed\",
            ROUND(COALESCE(f.ef, 0), 3) as \"Emission Coefficient (T PM/T of Fuel)\", ROUND(COALESCE(a.tons_emitted, 0), 3) as \"Total Tons PM\"
            FROM accomplishment_fuels a
            JOIN fuels f ON(a.fuel_id = f.fuel_id)
            JOIN fuel_types t ON(f.fuel_type_id = t.fuel_type_id)
            WHERE a.accomplishment_id = $accomplishment_id
            AND f.show_on_form = '1'
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
            $html = show(array('sql'=>$sql,'pkey'=>'accomplishment_id',
                'no_results_message'=>'There are no fuels associated with this accomplishment.',
                'no_results_class'=>'info'));
        }

        return $html;
    }

    protected function getContacts($accomplishment_id)
    {
        /**
         *  Constructs the contacts display div for a burn.
         */

        $submitter = fetch_row(
            "SELECT u.full_name, u.email, u.phone, ag.agency
            FROM accomplishments a
            JOIN users u ON(a.submitted_by = u.user_id)
            JOIN agencies ag ON(u.agency_id = ag.agency_id)
            WHERE a.accomplishment_id = ?
            ", $accomplishment_id
        );

        $contact = fetch_row("SELECT manager_name, manager_number, manager_cell, manager_fax FROM accomplishments WHERE accomplishment_id = ?", $accomplishment_id);

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
                            <p>Primary Manager</p>
                            <h5>".$contact['manager_name']."</h5>
                            <p class=\"district\">Phone Number</p>
                            <a href=\"tel:".$contact['manager_number']."\">".$contact['manager_number']."</a>
                        </div>
                        <div class=\"contact-right\">
                            <p class=\"district\">Phone Cell</p>
                            <a href=\"tel:".$contact['manager_cell']."\">".$contact['manager_cell']."</a>
                            <p class=\"district\">Phone Fax</p>
                            <a href=\"tel:".$contact['manager_fax']."\">".$contact['manager_fax']."</a>
                        </div>
                    </div>";

        $html .= "</div>";

        return $html;
    }

    protected function getMap($accomplishment)
    {
        /**
         *  Builds a boundary & marker map for a single burn.
         */

        $zoom_to_fit = true;
        $control_title = "Zoom to burn request";

        $marker = fetch_one("SELECT b.location FROM burns b JOIN accomplishments a ON(b.burn_id = a.burn_id) WHERE a.accomplishment_id = ?", $accomplishment['accomplishment_id']);
        $status = $this->retrieveStatus($accomplishment['status_id']);

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
                            fillColor: '".$status['color']."',
                            fillOpacity: 1
                        },
                    });
                }

                marker.setMap(map)
            ";
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

        global $map_center;

        $status_icons = $this->status_icons;
        $user_id = $_SESSION['user']['id'];

        if (isset($burn_project_id)) {
            $markers = fetch_assoc("SELECT a.accomplishment_id, b.location, a.status_id, a.added_by FROM accomplishments a JOIN burns b ON(a.burn_id = b.burn_id) WHERE a.burn_project_id = ?;", $burn_project_id);
            $zoom_to_fit = true;
        } else {
            $markers = fetch_assoc("SELECT a.accomplishment_id, b.location, a.status_id, CONCAT(bp.project_number, ': Started: ', a.start_datetime) as name, a.added_by FROM accomplishments a JOIN burns b ON(a.burn_id = b.burn_id) JOIN burn_projects bp ON(bp.burn_project_id = a.burn_project_id) WHERE a.burn_project_id IN(SELECT burn_project_id FROM burn_projects WHERE agency_id = ?);", $agency_id);
            $zoom_to_fit = false;
        }

        $center = "zoom: 6,
            center: new google.maps.LatLng($map_center),";

        if ($markers['error'] == false) {
            // Construct the Marker array.
            $marker_arr = "var dailyBurns = [\n ";
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
                $marker_arr .= "[".$value['accomplishment_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$value['name']."', '".$marker_status['color']."', ".$edit.",'".str_replace(" ", "_", strtolower($marker_status['title']))."']$comma\n ";
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
                            window.location='/manager/accomplishment.php?detail=true&id='+marker.id;return false;
                        });
                    }
                }

                setMarkers(map, dailyBurns)
            ";
        } else {
            $marker = "";
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

                var Overlay = new Overlay();
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }

    protected function getStatusLabel($accomplishment)
    {
        /**
         *  Constructs the status label for a detail page.
         */

        $status_id = $accomplishment['status_id'];

        $status = fetch_row(
            "SELECT description, class, name FROM accomplishment_statuses
            WHERE status_id = ?;", $status_id
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    public function getStatus($accomplishment_id)
    {
        /**
         *  Get the status id of the burn.
         */

        $result['status_id'] = fetch_one("SELECT status_id FROM accomplishments WHERE accomplishment_id = ?;", $accomplishment_id);

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

    public function get($accomplishment_id)
    {
        /**
         *  Gets the full burn.
         */

        // Get accomplishment info.
        $values = fetch_row(
          "SELECT * FROM accomplishments WHERE accomplishment_id = ?;",
          $accomplishment_id
        );
        $result = $values;

        $result['request_dates'] = fetch_row(
          "SELECT DATE(start_datetime) as start_date, DATE(end_datetime) as end_date
          FROM accomplishments WHERE accomplishment_id = ?",
          $accomplishment_id
        );

        // Get burn project info.
        $result['burn_project'] = fetch_row(
          "SELECT burn_project_id, project_name, project_number
          FROM burn_projects
          WHERE burn_project_id = (
            SELECT burn_project_id FROM accomplishments WHERE accomplishment_id = ?
          );", $accomplishment_id
        );

        // Get fuels info.
        $result['fuels'] = fetch_assoc(
          "SELECT * FROM accomplishment_fuels WHERE accomplishment_id = ?;",
          $accomplishment_id
        );

        return $result;
    }

    private function checkPermission($user_id, $accomplishment_id)
    {
        $auth = fetch_one(
            "SELECT u.user_id
            FROM accomplishments a
            JOIN agencies ag ON(a.agency_id = ag.agency_id)
            JOIN users u ON(u.agency_id = ag.agency_id)
            WHERE a.accomplishment_id = ?
            AND u.user_id = ?;"
        , array($accomplishment_id, $user_id));

        if ($auth == $user_id) {
            return true;
        }

        return false;
    }

    protected function getUploads($accomplishment_id)
    {
        /**
         *  Constructs the uploads HTML block.
         */

        $permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency'));

        $uploads = fetch_assoc(
            "SELECT f.*
            FROM accomplishment_files b
            JOIN files f ON (b.file_id = f.file_id)
            WHERE b.accomplishment_id = $accomplishment_id
            ORDER BY added_on;"
        );

        if ($permissions['write']['any']) {
            $toolbar = "<div class=\"btn-group pull-right\">
                <button onclick=\"Uploader.form('accomplishments',$accomplishment_id)\" class=\"btn btn-sm btn-default\">Upload</button>
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

    protected function getReviews($accomplishment_id, $full = false)
    {
        /**
         *  Constructs the reviews display div & table for a given Burn Project.
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
            $pre_sql = "r.accomplishment_review_id, ";
        }

        $sql = "SELECT $pre_sql COALESCE(CONCAT(u.full_name, '<br><small><span class=\"label label-default\">Edited By</span></small>'), a.full_name) as \"Reviewer\", CONCAT('<a style=\"cursor: pointer\" onclick=\"Accomplishment.reviewDetail(', r.accomplishment_review_id ,')\">', $com_cond) as \"Excerpt\", CONCAT('<small>', COALESCE(r.updated_on, r.added_on), '</small>') as \"Edited\"
        FROM accomplishment_reviews r
        JOIN users a ON (r.added_by = a.user_id)
        LEFT JOIN users u ON (r.updated_by = u.user_id)
        WHERE accomplishment_id = $accomplishment_id";

        if ($admin['any']) {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this burn accomplishment.',
                'no_results_class'=>'info','pkey'=>'accomplishment_review_id','table'=>'accomplishment_reviews','include_edit'=>true,'include_delete'=>false,
                'edit_function'=>'AccomplishmentReview.editReviewForm'));
        } else {
            $table = show(array('sql'=>$sql,'no_results_message'=>'There are currently no reviews associated with this burn accomplishment.',
                'no_results_class'=>'info'));
        }

        $html .= $table['html'];

        $html .= "</div>";

        return $html;
    }

    public function reviewDetail($review_id)
    {
        /**
         *  Get the review detail.
         */

        $review = fetch_row(
            "SELECT r.accomplishment_id, u.email, u.full_name, DATE_FORMAT(r.added_on, '%Y-%m-%e %l:%i %p') as added_on, r.comment
            FROM accomplishment_reviews r
            JOIN users u ON(r.added_by = u.user_id)
            WHERE r.accomplishment_review_id = $review_id;"
        );

        $burn = fetch_row(
            "SELECT project_name, project_number
            FROM burn_projects
            WHERE burn_project_id IN(SELECT burn_project_id FROM accomplishments WHERE accomplishment_id = ".$review['accomplishment_id'].");"
        );

        $html = "<div>
            <p><strong>".$review['full_name'].": </strong>".$review['comment']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$review['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"mailto:".$review['email']."?subject=Burn Review - ".$burn['location']." - ".$burn['burn_number']." ".$burn['burn_name']."\" role=\"button\">Email Reviewer</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

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

    private function checkAccomplishmentPermissions($user_id, $accomplishment_id, $permissions)
    {
        /**
         *  Return what the user can do with this burn project. (read, write)
         */

        $read = false;
        $write = false;

        $burn = fetch_row("SELECT added_by, district_id, agency_id FROM accomplishments WHERE accomplishment_id = ?", $accomplishment_id);
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
    protected function reviewCheck($accomplishment_id)
    {
        /**
         *  Check if the accomplihsment was updated since the last review.
         */

        $review_last_updated = fetch_one("SELECT MAX(last_burn_update) FROM accomplishment_reviews WHERE accomplishment_id = $accomplishment_id;");
        $last_updated = fetch_one("SELECT updated_on FROM accomplishments WHERE accomplishment_id = $accomplishment_id;");

        if ($last_updated > $review_last_updated) {
            return true;
        } else {
            return false;
        }
    }

    protected $value_map = array(
        'accomplishment_id'=>array('display'=>false,'title'=>'Accomplishment Id'),
        'burn_project_id'=>array('display'=>false,'title'=>'Burn Project Id'),
        'burn_id'=>array('display'=>false,'title'=>'Burn Id'),
        'submitted_on'=>array('display'=>false,'title'=>'Submitted On'),
        'location'=>array('display'=>false,'title'=>'Location'),
        'clearing_index'=>array('display'=>true,'title'=>'Clearing Index'),
        'state_id'=>array('display'=>true,'title'=>'Burn Completeness','sql'=>"SELECT CONCAT(type, ' - ', description) as display FROM accomplishment_states WHERE accomplishment_state_id = "),
        'state_comment'=>array('display'=>true,'title'=>'Burn Completeness Comment'),
        'resume_date'=>array('display'=>true,'title'=>'Resume Date (if Postponed)'),
        'wfu_updates'=>array('display'=>true,'title'=>'WFU, will send in updates with >50 acres are consumed day previous.','boolean'=>true),
        'wfu_remarks'=>array('display'=>true,'title'=>'WFU Remarks'),
        'black_acres_change'=>array('display'=>true,'title'=>'New black acre change since last report'),
        'total_year_acres'=>array('display'=>true,'title'=>'Total Calendar year acres to date'),
        'total_project_acres'=>array('display'=>true,'title'=>'Total Project acres to date'),
        'manager_name'=>array('display'=>false,'title'=>'Manager Name'),
        'manager_number'=>array('display'=>false,'title'=>'Manager Number'),
        'manager_cell'=>array('display'=>false,'title'=>'Manager Cell'),
        'manager_fax'=>array('display'=>false,'title'=>'Manager Fax'),
        'start_datetime'=>array('display'=>true,'title'=>'Burn Start Date/Time'),
        'end_datetime'=>array('display'=>true,'title'=>'Burn End Date/Time'),
        'public_interest_id'=>array('display'=>true,'title'=>'Public Interest','sql'=>'SELECT name FROM interest_levels WHERE interest_level_id = '),
        'day_vent_id'=>array('display'=>true,'title'=>'Daytime Ventilation','sql'=>'SELECT name FROM daytime_ventilations WHERE daytime_ventilation_id = '),
        'night_smoke_id'=>array('display'=>true,'title'=>'Nightime Smoke','sql'=>'SELECT name FROM nighttime_smoke WHERE nighttime_smoke_id = '),
        'swr_plan_met'=>array('display'=>true,'title'=>'Smoke Management Prescription/WFIP/Resource Benefit Plan met','boolean'=>true),
        'primary_ert_id'=>array('display'=>true,'title'=>'Primary Emission Reduction Technique (ERT)','sql'=>'SELECT name FROM emission_reduction_techniques WHERE emission_reduction_technique_id = '),
        'alternate_primary_ert'=>array('display'=>true,'title'=>'Alternate Primary Emission Reduction Technique (if Other)'),
        'primary_ert_pct'=>array('display'=>true,'title'=>'Primary Emission Reduction Technique Percentage'),
        'secondary_ert_id'=>array('display'=>true,'title'=>'Secondary Emission Reduction Technique (ERT)','sql'=>'SELECT name FROM emission_reduction_techniques WHERE emission_reduction_technique_id = '),
        'alternate_secondary_ert'=>array('display'=>true,'title'=>'Alternate Secondary Emission Reduction Technique (if Other)'),
    );

}
