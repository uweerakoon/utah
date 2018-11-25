<?php

namespace Manager;

class BurnReview extends Burn
{

    private $var;
    private $review_form_id;
    private $main_url = "/review/burn.php";
    private $datatable;
    private $table_id;

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->burn = $burn;
        $this->review_form_id = 'bp_review_form';
    }

    public function reviewTable()
    {
        /**
         *  Get all reviewable (Under Review, Revision Requested)
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','admin_final','system_admin'), 'read');
        if ($permissions['deny']) {
            echo($permissions['message']);
            exit;
        }

        $args = array();
        extract(merge_args(func_get_args(), $args));

        $html = "<hr>";

        $sql = "SELECT CONCAT('dtRow_', pb.burn_id) as id, b.project_number as \"Burn Project\",
        CONCAT('<a href=\"/review/burn.php?burn=true&id=', pb.burn_id ,'\">' , pb.start_date , '</a>') as \"Start Date\", end_date as \"End Date\",
        pb.submitted_on as \"Submitted\", a.agency as \"Agency\",
        COALESCE(r.added_on, 'Never') as \"Last Reviewed\",
        COALESCE(r.full_name, 'N/A') as \"Reviewed By\",
        CONCAT('<span class=\"', s.class, '\">', s.name, '</span>') as \"Status\",
        us.full_name as \"Submitted By\"
        FROM burns pb
        JOIN burn_projects b ON (b.burn_project_id = pb.burn_project_id)
        JOIN agencies a ON (b.agency_id = a.agency_id)
        LEFT JOIN (
            SELECT r.*, u.full_name
            FROM burn_reviews r
            INNER JOIN
                 (SELECT burn_id, MAX(added_on) as max_added_on
                FROM burn_reviews
                GROUP BY burn_id) br
            ON (r.burn_id = br.burn_id)
            JOIN users u ON (r.added_by = u.user_id)
            AND r.added_on = br.max_added_on
        ) r ON(pb.burn_id = r.burn_id)
        JOIN burn_statuses s ON(pb.status_id = s.status_id)
        JOIN users us ON (pb.submitted_by = us.user_id)
        WHERE pb.status_id > 1
        ORDER BY pb.submitted_on;";

        $table = show(array('sql'=>$sql,'paginate'=>true,'table_class'=>'table table-micro','hidden_id'=>true,'id_col'=>true,'include_edit'=>false));
        $html .= $table['html'];
        $this->datatable = $table['datatable'];
        $this->table_id = $table['id'];

        return $html;
    }

    public function sidebar()
    {

        $html = "<hr><div style=\"border-bottom: 1px solid #e4e4e4;\">";

        $html .= $this->agencyFilter();

        $html .= $this->dateFilter();

        $html .= $this->yearFilter();

        $html .= $this->statusFilter();

        $html .= $this->modifySelected();

        $html .= "</div>";

        return $html;
    }

    private function agencyFilter()
    {

        $agencies = fetch_assoc_offset("SELECT agency as title FROM agencies ORDER BY agency DESC;");

        $html = label_filter(array('object'=>$this->datatable,'column'=>5,'function_name'=>'FilterAg',
            'wrapper_class'=>'filter_agency','selector'=>'agency','title'=>'Agencies',
            'info_array'=>$agencies,'map'=>true,'fieldname'=>'agency','max_height'=>true));

        return $html;
    }

    private function yearFilter($selected)
    {
        /**
         *  Make the years filter.
         */

        $start_year = 2015;
        $current_year = date('Y') + 1;

        $years = array($current_year);

        for ($i = 1; $i <= ($current_year - $start_year); $i++) {
            $append = $current_year - $i;
            array_push($years, $append);
        }

        $html = label_filter(array('object'=>$this->datatable,'column'=>2,'function_name'=>'FilterYr',
            'wrapper_class'=>'filter_year','selector'=>'year','title'=>'Year',
            'selected'=>array(0,1),'info_array'=>$years,'map'=>true,'fieldname'=>'start'));

        return $html;
    }

    private function dateFilter()
    {
        $html = date_filter(array('object'=>$this->datatable,'column'=>2,'title'=>'Date Filter',
            'map'=>true,'fieldname'=>'start2'));

        return $html;
    }

    private function statusFilter($selected)
    {
        /**
         *  Produces the datatables label filter for statues.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','admin_final','system_admin'), 'write');

        if ($permissions['admin_final']) {
            $selected = array(2,4);
        } else {
            $selected = array(1,2,4);
        }

        // Status info array
        $info = array(
            1=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'101','class'=>'warning'),
            2=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'102','zindex'=>'102','class'=>'warning'),
            3=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'103','class'=>'danger'),
            4=>array('id'=>4,'title'=>'Pending Approval','color'=>'#f0ad4e','opacity'=>'0.75','zindex'=>'104','class'=>'warning'),
            5=>array('id'=>5,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'105','class'=>'success'),
            6=>array('id'=>6,'title'=>'Disapproved','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'106','class'=>'danger')
        );
        unset($info[1]);

        $html = label_filter(array('object'=>$this->datatable,'column'=>8,'function_name'=>'FilterSt',
            'wrapper_class'=>'filter_status','selector'=>'status','title'=>'Statuses',
            'selected'=>$selected,'info_array'=>$info,'map'=>true,'fieldname'=>'status'));

        return $html;
    }

    private function modifySelected()
    {
        /**
         *  Modify status on a group of burns
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','admin_final','system_admin'), 'write');

        if ($permissions['deny']) {
            $html = "";
        } elseif ($permissions['admin_final']) {
            $html = "<div class=\"dt_filter_section\">
                <strong>Toolbar</strong>
                <br>
                ";

            $html .= "<div style=\"font-size: 12px;\">
                <a onclick=\"BurnReview.approveAll(selected)\" style=\"cursor: pointer\">Approve Selection</a><br>
                <a onclick=\"BurnReview.disapproveAll(selected)\" style=\"cursor: pointer\">Disapprove Selection</a><br>
                </div>";

            $html .= "</div>";
        } else {
            $html = "<div class=\"dt_filter_section\">
                <strong>Toolbar</strong>
                <br>
                ";

            $html .= "<div style=\"font-size: 12px;\">
                <a onclick=\"BurnReview.preApproveAll(selected)\" style=\"cursor: pointer\">Submit Selection for Final Approval</a><br>
                </div>";

            $html .= "</div>";
        }

        return $html;
    }

    public function reviewPage($burn_id)
    {
        /**
         *  Construct a review html block.
         *  This is for a specific burn id (passed to $_GET on the page)
         */

        // Get the daily burn.
        $burn = $this->get($burn_id);

        // Construct the title.
        if (isset($burn['burn_project']['project_name']) && isset($burn['burn_project']['project_number'])) {
          $title = $burn['burn_project']['project_name']." / ".$burn['burn_project']['project_number']." / <small>".$burn['start_date']." to ".$burn['end_date']."</small>";
        } elseif (isset($burn['burn_project']['project_name'])) {
          $title = $burn['burn_project']['project_name']." / <small>".$burn['start_date']." to ".$burn['end_date']."</small>";
        } else {
          $title = "Burn Request";
        }

        $map = $this->getMap($burn_id);
        $title_status = $this->getStatusLabel($burn_id);
        $return_href = $this->mainUrl();

        // Construct the header.
        $html = "<div class=\"row\">
            <div class=\"col-sm-12\" style=\"margin-bottom: 8px;\">
                <span class=\"pull-right\">
                    $return_href
                    $title_status
                </span>
                <h3>$title</h3>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-sm-8\">
                <h4>Details</h4>
                <hr>";

        // Construct the map.
        $html .= "<div>
            $map
        </div>";

        // Construct the data table.
        $html .= "<div style=\"margin-top: 15px; padding-left: 0px\" class=\"col-sm-12\">";

        $html .= $this->tablifyFields($burn_id);

        $html .= "</div>
            </div>
            <div class=\"col-sm-4\">";

        $html .= $this->getSidebar($burn_id);

        $html .= "</div>
            </div>";

        return $html;
    }

    public function approvePDF()
    {
        /**
         *
         */

        $pre_approved = fetch_assoc(
            "SELECT d.start_date, d.sm_unit_number, b.burn_name, b.burn_number,
            COALESCE(c.acres, acres_treated, 0) as approved_acres, d.location, c.comment
            FROM burns d
            JOIN burn_plans b ON (d.burn_plan_id = b.burn_plan_id)
            LEFT JOIN burn_conditions c ON (d.burn_id = c.burn_id)
            WHERE d.status_id = 4
            ORDER BY b.burn_number;"
        );

        $title = "Pre Approved For: ";
        $date = date('Y-m-d');

        $html = "<div style=\"width: 100%;\"><table style=\"width: 100%; vertical-align: top\">
            <tbody>
                <tr><td style=\"font-size:9pt width:85%\"><strong>Pending Pre-Approvals</strong><br><small style=\"font-size: 6pt\">$date</small></td><td style=\"width: 15%\"><img style=\"height:32pt;\" src=\"../images/Utah.gov-logo.png\"></td></tr>
            </tbody>
            </table>
            <br><br>
            <h5><strong>Daily Burns</strong></h5>
            ";

        if ($pre_approved['error'] == true) {
            $html .= "<tr><p><strong>There are no pending pre-approved burns.</strong></p></tr>";
        } else {
            $html .= "<table class=\"table-pdf table-bordered\" style=\"font-size: 9pt; text-align: center\">
            <thead>
            <tr><th>Smoke Unit #</th><th>Burn Name</th><th>Burn Number</th><th>Ignition Date</th><th>Approved Acres</th><th>Location (lat, long)</th><th>Notes</th></tr>
            </thead>
            <tbody>";

            foreach ($pre_approved as $value) {
                $location = explode(',', $value['location']);
                $location = str_replace(array('(',')'), '', $location);
                $location[0] = round($location[0], 2);
                $location[1] = round($location[1], 2);
                $location = '('.$location[0].', '.$location[1].')';

                $html .= "<tr><td>{$value['sm_unit_number']}</td><td>{$value['burn_name']}</td><td>{$value['burn_number']}</td><td>{$value['start_date']}</td><td>{$value['approved_acres']}</td><td>{$location}</td><td style=\"font-size: 8pt\">{$value['comment']}</td></tr>";
            }

            $html .= "</tbody>
                </table>";
        }

        $html .= "
        <p style=\"font-size: 7pt\">Pre-approved daily burns are pending final approval from the Governors office. Please submit the signed sheet to Utah.gov Fax 602-771-2366.</p>
        <br>
        </div>
        <div style=\"position: absolute: bottom: 0; left: 0mm; width: 100%\">
            <h6><strong>Final Approval</strong></h6>
            <table class=\"table-pdf table-bordered\" style=\"width: 60%; font-size: 9pt; text-align: center\">
                <thead>
                    <tr><th>Print Name</th><th>Signature</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <tr style=\"height: 24pt\"><td style=\"height: 24pt\"><br></td><td><br></td><td><br></td></tr>
                </tbody>
            </table>
        </div>";

        return $html;
    }

    public function mainUrl($text = "Return to Overview", $type = "a")
    {
        /**
         *  Generates the return url (back to root review/burn).
         */

        $url = $this->main_url;

        if ($type = "btn") {
            $html = "<a href=\"$url\">$text</a>";
        } else {
            $html = "<a href=\"$url\"><btn class=\"btn btn-sm btn-default\">$text</button></a>";
        }

        return $html;
    }

    public function getStatus($burn_id)
    {
        /**
         *  Constructs the status bar for the header..
         */

        $status = fetch_row(
            "SELECT s.class, s.name, s.description
            FROM burns d
            JOIN burn_statuses s ON(d.status_id = s.status_id)
            WHERE d.burn_id = $burn_id"
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    public function getSidebar($burn_id)
    {
        /**
         *  Construct the side bar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','admin_final','system_admin'), 'write');

        $contacts = $this->getContacts($burn_id);
        $reviews = $this->getReviews($burn_id);
        $conditions = $this->getConditions($burn_id);
        $uploads = $this->getUploads($burn_id);

        $status = fetch_one("SELECT status_id FROM burns WHERE burn_id = $burn_id;");

        if ($status >= $this->approved_id && $permissions['any']) {
            $upper_toolbar = "<div class=\"btn-group pull-right\">
                <btn class=\"btn btn-sm btn-default\" onclick=\"Burn.ownerChangeForm($burn_id)\">Change Owner</btn>
            </div>";
            $review_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.reviewForm($burn_id)\">Add Review</btn>
                </div>";
            $conditions_toolbar = "";
        } elseif ($status == $this->pending_approval_id && $permissions['admin_final']) {
            $upper_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"Burn.ownerChangeForm($burn_id)\">Change Owner</btn>
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.approveForm($burn_id)\">Approve</btn>
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.disapproveForm($burn_id)\">Disapprove</btn>
                </div>";
            $review_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.reviewForm($burn_id)\">Add Review</btn>
                </div>";
            $conditions_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.conditionForm($burn_id)\">Add Note</btn>
                </div>";
        } elseif ($status < $this->pending_approval_id && $permissions['admin']) {
            $upper_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.preApproveForm($burn_id)\">Submit for Final Approval</btn>
                </div>";
            $review_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.reviewForm($burn_id)\">Add Review</btn>
                </div>";
            $conditions_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnReview.conditionForm($burn_id)\">Add Note</btn>
                </div>";
        }

        $html = "<div class=\"form-block\"></div>
                $upper_toolbar
                $contacts
                $review_toolbar
                $reviews
                $conditions_toolbar
                $conditions
                $uploads";

        return $html;
    }

    public function reviewForm($burn_id)
    {
        /**
         *  Add a review item.
         */

        $status_id = fetch_one("SELECT status_id FROM burns WHERE burn_id = ?", $burn_id);

        $ctls = array(
            'burn_id'=>array('type'=>'hidden2','value'=>$burn_id),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.'),
            'html'=>array('type'=>html,'value'=>'<strong>New Status:</strong>'),
            'status_id'=>array('type'=>'combobox','label'=>"",'value'=>$status_id,'table'=>'burn_statuses','fcol'=>'status_id','display'=>'name')
        );

        $html = mkForm(array('onclick'=>'BurnReview.save('.$burn_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function editReviewForm($burn_review_id)
    {
        /**
         *  Add a review item.
         */

        $review = fetch_row("SELECT * FROM burn_reviews WHERE burn_review_id = ?", $burn_review_id);
        extract($review);

        $ctls = array(
            'burn_id'=>array('type'=>'hidden2','value'=>$burn_id),
            'html'=>array('type'=>html,'value'=>'<div style="margin: 0px 0px 10px 0px; font-size: 11px;"><i class="glyphicon glyphicon-info-sign"></i> Burn Request status cannot be changed while editing a previously submitted review.</div>'),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.','value'=>$comment),
        );

        $html = mkForm(array('onclick'=>'BurnReview.update('.$burn_review_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function conditionEdit($burn_condition_id)
    {
        /**
         *  Edit a conditional approval item.
         */

        $burn_id = fetch_one("SELECT burn_id FROM burn_conditions WHERE burn_condition_id = $burn_condition_id;");

        return $this->conditionForm($burn_id, $burn_condition_id);
    }

    public function conditionForm($burn_id, $burn_condition_id)
    {
        /**
         *  Add a conditional approval item.
         */

        if (isset($burn_condition_id)) {
            $condition = fetch_row("SELECT burn_condition_id, comment, acres FROM burn_conditions WHERE burn_condition_id = ?;", $burn_condition_id);
            $burn_condition_id = $condition['burn_condition_id'];
            $comment = $condition['comment'];
            $acres = $condition['acres'];
            $dbc_id = null;
        } else {
            $burn_condition_id = null;
            $dbc_id = fetch_one("SELECT burn_condition_id FROM burn_conditions WHERE burn_id = ?;", $burn_id);
        }

        if (is_null($dbc_id)) {
            $status_id = 5;

            $ctls = array(
                'burn_condition_id'=>array('type'=>'hidden2','value'=>$burn_condition_id),
                'burn_id'=>array('type'=>'hidden2','value'=>$burn_id),
                'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Conditional Approval Comment.','value'=>$comment),
                'acres'=>array('type'=>'text','placeholder'=>'Conditional Acres','value'=>$acres),
                'status_id'=>array('type'=>'hidden2','value'=>$status_id)
            );

            $html = mkForm(array('onclick'=>'BurnReview.saveCondition('.$burn_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
                'suppress_legend'=>true,'theme'=>'modal'));
        } else {
            $html = "<div>
                <p class=\"text-center\">The daily burn has already been conditionally approved.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function approveForm($burn_id)
    {
        /**
         *  Approve a burn plan.
         */

        $daily = fetch_row("SELECT start_date, status_id FROM burns WHERE burn_id = ?;", $burn_id);

        if (in_array($daily['status_id'], array(2,4))) {
            // Under Review, it can be approved.
            $html = "<div>
                <button class=\"btn btn-success btn-block\" onclick=\"BurnReview.approve($burn_id)\">Approve <strong>".$daily['start_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } elseif ($daily['status_id'] == 3) {
            // Revision Requested. Check for edit.
            if ($this->reviewCheck($burn_id)) {
                $message = "The burn plan has been edited since the last review but not re-submitted.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Resubmit</button>";
            } else {
                $message = "The burn plan has not been edited since the last review.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Revise</button>";
            }

            $html = "<div>
                <p class=\"text-center\">$message</p>
                $button
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">The burn plan cannot be approved from its current status.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function approveBurn($burn_id)
    {
        /**
         *  Final Approve a burn plan.
         */

        $approve_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
        $approve_sql = execute_bound($approve_sql, array($this->approved_id, $burn_id));
        if ($approve_sql->rowCount() > 0) {
            $result['message'] = status_message("The burn request is approved.", "success");

            // Notify.
            $notify = new \Info\Notify($this->db);
            $notify->burnFinalApproved($burn_id);
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn request was not successfully approved.", "error");
        }
        return $result;
    }

    public function preApproveForm($burn_id)
    {
        /**
         *  Approve a burn plan.
         */

        $burn = fetch_row("SELECT start_date, status_id FROM burns WHERE burn_id = ?;", $burn_id);

        if ($burn['status_id'] == $this->under_review_id) {
            // Under Review, it can be approved.
            $html = "<div>
                <button class=\"btn btn-success btn-block\" onclick=\"BurnReview.preApprove($burn_id)\">Pre-approve <strong>".$burn['start_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } elseif ($burn['status_id'] == $this->revision_requested_id) {
            // Revision Requested. Check for edit.
            if ($this->reviewCheck($burn_id)) {
                $message = "The burn request has been edited since the last review but not re-submitted.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Resubmit</button>";
            } else {
                $message = "The burn request has not been edited since the last review.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Revise</button>";
            }

            $html = "<div>
                <p class=\"text-center\">$message</p>
                $button
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">The burn request has already been pre-approved.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function preApproveBurn($burn_id)
    {
        /**
         *  Pre-approve a burn request.
         */

        $current = fetch_one("SELECT status_id FROM burns WHERE burn_id = ?", $burn_id);

        if ($current < $this->pending_approval_id) {
            $pre_approve_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
            $pre_approve_sql = execute_bound($pre_approve_sql, array($this->pending_approval_id, $burn_id));
            if ($pre_approve_sql->rowCount() > 0) {
                $result['message'] = status_message("The burn request form is pre-approved.", "success");
            } else {
                $result['error'] = true;
                $result['message'] = status_message("The burn request form was not successfully pre-approved.", "error");
            }

            $notify = new \Info\Notify($this->db);
            $notify->burnToDirector($burn_id);
        } else {
            $result['message'] = status_message("The burn request has is already past this stage.", "error");
        }

        return $result;
    }

    public function disapproveForm($burn_id)
    {
        /**
         *  Disapprove burn request form.
         */

        $daily = fetch_row("SELECT start_date, status_id FROM burns WHERE burn_id = ?;", $burn_id);

        if (in_array($daily['status_id'], array(2,4))) {
            // Under Review, it can be approved.
            $html = "<div>
                <button class=\"btn btn-danger btn-block\" onclick=\"BurnReview.disapprove($burn_id)\">Disapprove <strong>".$daily['start_date']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } elseif ($daily['status_id'] == 3) {
            // Revision Requested. Check for edit.
            if ($this->reviewCheck($burn_id)) {
                $message = "The burn plan has been edited since the last review but not re-submitted.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Resubmit</button>";
            } else {
                $message = "The burn plan has not been edited since the last review.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnReview.notify($burn_id)\">Notify Submitter to Revise</button>";
            }

            $html = "<div>
                <p class=\"text-center\">$message</p>
                $button
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } else {
            $html = "<div>
                <p class=\"text-center\">The burn plan cannot be disapproved from its current status.</p>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function disapproveBurn($burn_id)
    {
        /**
         *  Disapprove a burn plan.
         */

        $approve_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
        $approve_sql->execute(array($this->disapprove_id, $burn_id));
        if ($approve_sql->rowCount() > 0) {
            $result['message'] = status_message("The burn request is disapproved.", "success");

            //$notify = new \Info\Notify($this->db);
            //$notify->burnerDisapproval($burn_id);
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn request was not successfully approved.", "error");
        }
        return $result;
    }

    public function batchApprove($selected)
    {
        /**
         *  Approve a burn plan.
         */

        $approve_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
        $approve_sql->execute(array($this->approved_id, $burn_id));
        if ($approve_sql->rowCount() > 0) {
            $result['message'] = status_message("The daily burn request is approved.", "success");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The daily burn request was not successfully approved.", "error");
        }
        return $result;
    }

    private function validateReviewSave($review)
    {
        /**
         *  This function confirms the review can be saved.
         */

        // Defaults
        $invalid_actual_statuses = array(1,3,4,5,6);
        $available_statuses = array(3,4);

        extract($review);

        //$valid = $this->validateRequired($burn_id);
        //$valid = $valid['valid'];
        $valid = true;

        if ($valid == false) {
            $result['valid'] = false;
            $error_message .= "The burn doesn't include all valid fields. ";
        }

        $actual_status = $this->getStatus($burn_id);

        // Check the old and new status.
        if (in_array($actual_status, $invalid_actual_statuses)) {
            // The status is not reviewable.
            $result['valid'] = false;
            $error_message .= "The burn is not reviewable according to its status. ";
        } elseif ($status_id == 2) {
            // The status is under review.

        }

        return $result;
    }

    public function conditionSave($condition)
    {


        // Get additional fields for the burn review.
        $added_by = $_SESSION['user']['id'];
        $now = now();

        extract($condition);

        if (empty($burn_condition_id)) {
            $burn_plan_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
            $burn_plan_sql->execute(array($status_id, $burn_id));
            if ($burn_plan_sql->rowCount() > 0) {
                $success_message .= "The burn request status has been updated. ";
            } else {
                $result['error'] = true;
                $error_message .= "The burn request status was not updated. ";
            }

            // Insert the condition comment & acres.
            $insert_sql = $this->pdo->prepare("INSERT INTO burn_conditions (burn_id, added_by, added_on, last_burn_update, comment, acres) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_sql->execute(array($burn_id, $added_by, $now, $last_burn_update, $comment, $acres));
            if ($insert_sql->rowCount() > 0) {
                $success_message .= "Conditional approval has been submitted. ";
            } else {
                $result['error'] = true;
                $error_message .= "Conditional approval failed to save. Please try again. ";
            }
        } else {
            $update_sql = $this->pdo->prepare("UPDATE burn_conditions SET comment = ?, acres = ? WHERE burn_condition_id = ?;");
            $update_sql->execute(array($comment, $acres, $burn_condition_id));
            if ($update_sql->rowCount() > 0) {
                $success_message .= "Conditional approval has been updated. ";
            } else {
                $result['error'] = true;
                $error_message .= "Conditional approval failed to update. Please try again. ";
            }
        }

        // Construct the error message.
        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        return $result;
    }

    public function reviewSave($review)
    {
        /**
         *  Extract the review form data. Insert then return errors.
         */

        extract($review);

        // If the status has changed update it.
        if ($actual_status != $status_id) {
            $burn_plan_sql = $this->pdo->prepare("UPDATE burns SET status_id = ? WHERE burn_id = ?");
            $burn_plan_sql->execute(array($status_id, $burn_id));
            if ($burn_plan_sql->rowCount() > 0) {
                $success_message .= "The daily burn request status has been updated. ";
            } else {
                $result['error'] = true;
                $error_message .= "The daily burn request status was not updated. ";
            }
        }

        // Get additional fields for the burn review.
        $added_by = $_SESSION['user']['id'];
        $now = now();
        $last_burn_update = fetch_one("SELECT updated_on FROM burns WHERE burn_id = ?", $burn_id);

        // Get some validation specs.
        $valid = $this->validateReviewSave($review);
        $actual_status = $this->getStatus($burn_id);

        // Insert the review comment.
        $insert_sql = $this->pdo->prepare("INSERT INTO burn_reviews (burn_id, added_by, added_on, last_burn_update, comment) VALUES (?, ?, ?, ?, ?)");
        $insert_sql->execute(array($burn_id, $added_by, $now, $last_burn_update, $comment));
        if ($insert_sql->rowCount() > 0) {
            $success_message .= "Review has been submitted. ";
        } else {
            $result['error'] = true;
            $error_message .= "The review failed to save. Please try again. ";
        }

        // Construct the error message.
        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        return $result;
    }

    public function reviewUpdate($review, $burn_review_id)
    {
        /**
         *  Extract the review form data. Update then return errors.
         */

        extract($review);

        $updated_by = $_SESSION['user']['id'];
        $last_burn_update = fetch_one("SELECT updated_on FROM burns WHERE burn_id = ?;", $burn_id);

        // Insert the review comment.
        $update_sql = $this->pdo->prepare("UPDATE `burn_reviews` SET updated_by = ?, last_burn_update = ?, comment = ? WHERE burn_review_id = ?;");
        $update_sql = execute_bound($update_sql, array($updated_by, $last_burn_update, $comment, $burn_review_id));
        if ($update_sql->rowCount() > 0) {
            $success_message .= "Review has been updated. ";
        } else {
            $result['error'] = true;
            $error_message .= "The review failed to update. Please try again. ";
        }

        // Construct the error message.
        if ($result['error'] == true) {
            $result['message'] = status_message($error_message, "error");
        } else {
            $result['message'] = status_message($success_message, "success");
        }

        return $result;
    }

    public function statusForm()
    {
        /**
         *  Generates the change status bar.
         */

        $html = "<button class=\"btn btn-sm btn\"></button>";

        return $html;
    }

    public function getReviewMap()
    {
        /**
         *  The All Map display.
         */

        $markers = fetch_assoc("SELECT p.burn_id, p.location, p.status_id, p.start_date, a.agency FROM burns p JOIN agencies a ON(p.agency_id = a.agency_id) WHERE status_id > 1");
        $zoom_to_fit = false;

        global $map_center;

        if ($zoom_to_fit == true) {
            $zoom = "map.fitBounds(bounds);";
        } else {
            $zoom = "";
        }

        if ($burn['error'] == false && !empty($burn)) {
            $center = "zoom: 6,
                center: new google.maps.LatLng(34.4,-111.8),";
            $latlng = explode(' ', str_replace(array("(", ")", ","), "", $burn['boundary']));
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
            center: new google.maps.LatLng($map_center),";
            $bounds = "";
        }

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
                $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $value['location']));
                $marker_status = $this->retrieveStatus($value['status_id']);
                $marker_arr .= "[".$value['burn_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$marker_status['title']."', '".$marker_status['color']."', '".$value['status_id']."', '".$value['start_date']."', '".$value['agency']."']$comma\n ";
            }

            $marker_arr .= "];\n";

            // Append it to the function.
            $marker = "
                $marker_arr

                var statuses = [];
                var selected = [];
                var allMarkers = [];

                function setMarkers(map, markers) {
                    for (var i = 0; i < markers.length; i++) {
                        var marker = markers[i];
                        var myLatLng = new google.maps.LatLng(marker[1], marker[2]);

                        if (checkLegacy()) {
                            allMarkers[i] = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                status: marker[3],
                                start: marker[6],
                                start2: marker[6],
                                id: marker[0],
                                status_id: parseInt(marker[5]),
                                show: true,
                            });
                        } else {
                            allMarkers[i] = new google.maps.Marker({
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
                                status: marker[3],
                                start: marker[6],
                                start2: marker[6],
                                agency: marker[7],
                                id: marker[0],
                                status_id: parseInt(marker[5]),
                                show: true,
                            });
                        }

                        bindMarkers(map, allMarkers[i]);
                    }

                    function bindMarkers(map, marker)
                    {
                        google.maps.event.addListener(marker, 'click', function() {
                            if (selected.indexOf(marker.id) > -1) {
                                selected.remove(marker.id)

                                var icon = marker.getIcon();

                                icon.strokeColor = '#333';
                                icon.strokeWeight = 1;

                                marker.setIcon(icon);

                                $('#dtRow_'+marker.id).removeClass('row-highlighted');
                            } else {
                                selected.push(marker.id);

                                var icon = marker.getIcon();

                                icon.strokeColor = '#fff';
                                icon.strokeWeight = 2;

                                marker.setIcon(icon);

                                $('#dtRow_'+marker.id).addClass('row-highlighted');
                            }
                        });
                    }
                }

                setMarkers(map, dailyBurns)
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
            <hr>
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

                var gm = new gmFilter(map, allMarkers);

                var Overlay = new Overlay();
                Overlay.setControls(map);

            </script>
            ";

        return $html;
    }
}
