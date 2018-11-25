<?php

namespace Manager;

class BurnProjectReview extends BurnProject
{

    private $var;
    private $review_form_id;
    private $main_url = "/review/project.php";
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

        $sql = "SELECT b.burn_project_id as id, b.project_number as \"Burn Project\", 
        CONCAT('<a href=\"/review/project.php?detail=true&id=', b.burn_project_id ,'\">' , b.project_name , '</a>') as \"Name\", 
        b.submitted_on as \"Submitted\", a.agency as \"Agency\",/*
        pc: no need for reviewed on date column
        COALESCE( CONCAT('<span class=\"label label-default\">', r.added_on , '</span>'), 
            '<span class=\"label label-default\">None</span>') as \"Last Reviewed\",*/
        COALESCE( CONCAT('<span class=\"label label-default\">', r.full_name, '</span>' ), 
            '<span class=\"label label-default\">N/A</span>') as \"Reviewed By\",
        CONCAT('<span class=\"', s.class, '\">', s.name, '</span>') as \"Status\"
        FROM burn_projects b
        JOIN agencies a ON(b.agency_id = a.agency_id)
        LEFT JOIN (
            SELECT r.*, u.full_name
            FROM burn_project_reviews r
            INNER JOIN
                 (SELECT burn_project_id, MAX(added_on) as max_added_on
                FROM burn_project_reviews
                GROUP BY burn_project_id) br
            ON (r.burn_project_id = br.burn_project_id)
            JOIN users u ON (r.added_by = u.user_id)
            AND r.added_on = br.max_added_on
        ) r ON(b.burn_project_id = r.burn_project_id)
        JOIN burn_project_statuses s ON(b.status_id = s.status_id)
        WHERE b.status_id > 1
        ORDER BY submitted_on;";

        $table = show(array('sql'=>$sql,'paginate'=>true,'table_class'=>'table table-micro','sort_column'=>3,
            'pkey'=>'burn_project_id','hidden_id'=>true,'id_col'=>true,'include_edit'=>false));
        $html .= $table['html'];
        $this->datatable = $table['datatable'];
        $this->table_id = $table['id'];

        return $html;
    }

    public function sidebar()
    {
     
        $html = "<hr><div style=\"border-bottom: 1px solid #e4e4e4;\">";

        $html .= $this->agencyFilter();

        $html .= $this->yearFilter();

        $html .= $this->statusFilter();

        $html .= "</div>";

        return $html;
    }

    private function agencyFilter()
    {

        $agencies = fetch_assoc_offset("SELECT agency as title FROM agencies ORDER BY agency DESC;");

        $html = label_filter(array('object'=>$this->datatable,'column'=>4,'function_name'=>'FilterAg',
            'wrapper_class'=>'filter_agency','selector'=>'agency','title'=>'Agencies',
            'info_array'=>$agencies,'max_height'=>true));

        return $html;
    }

    private function yearFilter($selected)
    {
        /**
         *  Make the years filter.
         */

        $start_year = 2015;
        $current_year = date('Y') + 2;

        $years = array($current_year);

        for ($i = 1; $i <= ($current_year - $start_year); $i++) {
            $append = $current_year - $i;
            array_push($years, $append);
        }

        $html = label_filter(array('object'=>$this->datatable,'column'=>3,'function_name'=>'FilterYr',
            'wrapper_class'=>'filter_year','selector'=>'year','title'=>'Year',
            'selected'=>array(1, 2,),'info_array'=>$years));

        return $html;
    }

    private function statusFilter($selected)
    {
        /**
         *  Produces the datatables label filter for statues.
         */

        // Remove drafts from the default status list.
        $info = array(
            1=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','class'=>'warning'),
            2=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','zindex'=>'102','class'=>'warning'),
            3=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.05','zindex'=>'103','class'=>'danger'),
            4=>array('id'=>4,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'109','class'=>'success'),
            5=>array('id'=>5,'title'=>'Archived','color'=>'#525252','opacity'=>'0.75','zindex'=>'105','class'=>'inverse')
        );

        unset($info[1]);

        $html = label_filter(array('object'=>$this->datatable,'column'=>7,'function_name'=>'FilterSt',
            'wrapper_class'=>'filter_status','selector'=>'status','title'=>'Statuses',
            'selected'=>array(2, 3),'info_array'=>$info));

        return $html;
    }

    public function reviewPage($burn_project_id)
    {
        /**
         *  Construct a review html block.
         *  This is for a specific burn id (passed to $_GET on the page)
         */

        $burn_project = $this->get($burn_project_id);

        $boundary_html = $this->getMap($burn_project, true, "Return to burn");

        // Construct the map.
        $html = "<div>
            $boundary_html
        </div>";

        // Construct the data table.
        $html .= "<div style=\"margin-top: 15px; padding-left: 0px\" class=\"col-sm-12\">";
        
        $html .= $this->tablifyFields($burn_project, 'project_info');
        $html .= $this->tablifyFields($burn_project, 'detail_info');

        $html .= "
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

    public function getStatus($burn_project_id)
    {
        /**
         *  Constructs the status bar for the header..
         */

        $status = fetch_row(
            "SELECT s.class, s.name, s.description 
            FROM burn_projects b
            JOIN burn_project_statuses s ON(b.status_id = s.status_id)
            WHERE b.burn_project_id = $burn_project_id"
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
    }

    public function getSidebar($burn_project_id)
    {
        /**
         *  Construct the side bar.
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system_admin'), 'write');

        $contacts = $this->getContacts($burn_project_id);
        $reviews = $this->getReviews($burn_project_id);
        $uploads = $this->getUploads($burn_project_id);

        $status = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        $toolbar = "<div class=\"btn-group pull-right\">";
            //<btn class=\"btn btn-sm btn-default\" onclick=\"BurnProject.editConfirmation($burn_project_id)\">Edit</btn>
            //<btn class=\"btn btn-sm btn-default\" onclick=\"BurnProjectReview.deleteConfirmation($burn_project_id)\">Delete</btn>";
        if ($status > 3 && $permissions['any']) {
            $toolbar .= "<btn class=\"btn btn-sm btn-default\" onclick=\"BurnProject.ownerChangeForm($burn_project_id)\">Change Owner</btn>
                <btn class=\"btn btn-sm btn-default\" onclick=\"BurnProjectReview.reviewForm($burn_project_id)\">Add Review</btn>";
        } elseif ($status <= 3 && $permissions['any']) {
            $toolbar .= "<btn class=\"btn btn-sm btn-default\" onclick=\"BurnProject.ownerChangeForm($burn_project_id)\">Change Owner</btn>
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnProjectReview.reviewForm($burn_project_id)\">Add Review</btn>
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnProjectReview.approveForm($burn_project_id)\">Approve Plan</btn>";
        }
        $toolbar .= "</div>";

        $html = "<div class=\"col-sm-4\">
                <div class=\"form-block\"></div>
                $toolbar
                $contacts
                $reviews
                $uploads
            </div>";

        return $html;
    }

    public function reviewForm($burn_project_id)
    {
        /**
         *  Add a review item.
         */

        $status_id = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = ?", $burn_project_id);

        $ctls = array(
            'burn_project_id'=>array('type'=>'hidden2','value'=>$burn_project_id),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.'),
            'html'=>array('type'=>html,'value'=>'<strong>New Status:</strong>'),
            'status_id'=>array('type'=>'combobox','label'=>"",'value'=>$status_id,'table'=>'burn_project_statuses','fcol'=>'status_id','display'=>'name')
        );

        $html = mkForm(array('onclick'=>'BurnProjectReview.save('.$burn_project_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function editReviewForm($burn_project_review_id)
    {
        /**
         *  Revise a review item.
         */

        $review = fetch_row("SELECT * FROM burn_project_reviews WHERE burn_project_review_id = ?", $burn_project_review_id);
        extract($review);

        $ctls = array(
            'burn_project_id'=>array('type'=>'hidden2','value'=>$burn_project_id),
            'html'=>array('type'=>html,'value'=>'<div style="margin: 0px 0px 10px 0px; font-size: 11px;"><i class="glyphicon glyphicon-info-sign"></i> Burn project status cannot be changed while editing a previously submitted review.</div>'),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.','value'=>$comment),
        );

        $html = mkForm(array('onclick'=>'BurnProjectReview.update('.$burn_project_review_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function approveForm($burn_project_id)
    {
        /**
         *  Approve a burn plan.
         */

        $burn = fetch_row("SELECT project_name, project_number, status_id FROM burn_projects WHERE burn_project_id = $burn_project_id;");

        if ($burn['status_id'] == 2) {
            // Under Review, it can be approved.
            $html = "<div>
                <button class=\"btn btn-success btn-block\" onclick=\"BurnProjectReview.approve($burn_project_id)\">Approve <strong>".$burn['project_number']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } elseif ($burn['status_id'] == 3) {
            // Revision Requested. Check for edit.
            if ($this->reviewCheck($burn_project_id)) {
                $message = "The burn plan has been edited since the last review but not re-submitted.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnProjectReview.notify($burn_project_id)\">Notify Submitter to Resubmit</button>";
            } else {
                $message = "The burn plan has not been edited since the last review.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnProjectReview.notify($burn_project_id)\">Notify Submitter to Revise</button>";
            }

            $html = "<div>
                <p class=\"text-center\">$message</p>
                $button
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        }

        return $html;
    }

    public function approveBurnProject($burn_project_id)
    {
        /**
         *  Approve a burn plan.
         */

        $approved_status = 4;
       
        $approve_sql = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?");
        $approve_sql->execute(array($approved_status, $burn_project_id));
        if ($approve_sql->rowCount() > 0) {
            $result['message'] = status_message("The burn plan is approved.", "success");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The burn plan was not successfully approved.", "error");
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

        $valid = $this->validateRequired($burn_project_id);
        $valid = $valid['valid'];

        if ($valid == false) {
            $result['valid'] = false;
            $error_message .= "The burn doesn't include all valid fields. ";
        }

        $actual_status = $this->getStatus($burn_project_id);

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

    public function reviewSave($review)
    {
        /**
         *  Extract the review form data. Insert then return errors.
         */

        extract($review);

        $actual_status = fetch_one("SELECT status_id FROM burn_projects WHERE burn_project_id = ?", $burn_project_id);

        // If the status has changed update it.
        if ($actual_status != $status_id) {
            $burn_project_sql = $this->pdo->prepare("UPDATE burn_projects SET status_id = ? WHERE burn_project_id = ?");
            $burn_project_sql->execute(array($status_id, $burn_project_id));
            if ($burn_project_sql->rowCount() > 0) {
                $success_message .= "The burn plan's status has been updated. ";
            } else {
                $result['error'] = true;
                $error_message .= "The burn plan status was not updated. ";
            }
        }

        // Get additional fields for the burn review.
        $added_by = $_SESSION['user']['id'];
        $now = now();
        $last_burn_update = fetch_one("SELECT updated_on FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        // Get some validation specs.
        $valid = $this->validateReviewSave($review);
        $actual_status = $this->getStatus($burn_project_id);

        // Insert the review comment.
        $insert_sql = $this->pdo->prepare("INSERT INTO burn_project_reviews (burn_project_id, added_by, added_on, last_burn_update, comment) VALUES (?, ?, ?, ?, ?)");
        $insert_sql->execute(array($burn_project_id, $added_by, $now, $last_burn_update, $comment));
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

    public function reviewUpdate($review, $burn_project_review_id)
    {
        /**
         *  Extract the review form data. Update then return errors.
         */

        extract($review);

        $updated_by = $_SESSION['user']['id'];
        $last_burn_update = fetch_one("SELECT updated_on FROM burn_projects WHERE burn_project_id = ?;", $burn_project_id);

        // Insert the review comment.
        $update_sql = $this->pdo->prepare("UPDATE `burn_project_reviews` SET updated_by = ?, last_burn_update = ?, comment = ? WHERE burn_project_review_id = ?");
        $update_sql = execute_bound($update_sql, array($updated_by, $last_burn_update, $comment, $burn_project_review_id));
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
}
