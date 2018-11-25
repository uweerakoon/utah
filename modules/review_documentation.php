<?php

namespace Manager;

class BurnDocumentationReview extends BurnDocumentation
{

    private $var;
    private $review_form_id;
    private $main_url = "/review/documentation.php";
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

        $sql = "SELECT d.documentation_id as id, b.project_number as \"Project\", 
        CONCAT('<a href=\"/review/documentation.php?detail=true&id=', d.documentation_id ,'\">' , d.observation_date , '</a>') as \"Observation Date\", 
        d.submitted_on as \"Submitted On\", ag.agency as \"Agency\",
        CONCAT('<span class=\"', s.class, '\">', s.name, '</span>') as \"Status\",
        us.full_name as \"Submitted By\"
        FROM documentation d
        JOIN burn_projects b ON (b.burn_project_id = d.burn_project_id)
        JOIN agencies ag ON (d.agency_id = ag.agency_id)
        JOIN documentation_statuses s ON(d.status_id = s.status_id)
        JOIN users us ON (d.submitted_by = us.user_id)
        WHERE d.status_id > 1
        ORDER BY d.submitted_on;";

        $table = show(array('sql'=>$sql,'paginate'=>true,'table_class'=>'table table-micro',
            'pkey'=>'documentation_id','hidden_id'=>true,'id_col'=>true,'include_edit'=>false));
        $html .= $table['html'];
        $this->datatable = $table['datatable'];
        $this->table_id = $table['id'];

        return $html;
    }

    public function sidebar()
    {
     
        $html = "<hr><div style=\"border-bottom: 1px solid #e4e4e4;\">";

        //$html .= $this->dateFilter();

        $html .= $this->agencyFilter();

        //$html .= $this->yearFilter();

        $html .= $this->statusFilter();

        $html .= "</div>";

        return $html;
    }

    public function reviewForm($documentation_id)
    {
        /**
         *  Add a review item.
         */

        $status_id = fetch_one("SELECT status_id FROM documentation WHERE documentation_id = ?", $documentation_id);

        $ctls = array(
            'documentation_id'=>array('type'=>'hidden2','value'=>$documentation_id),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.'),
            'html'=>array('type'=>html,'value'=>'<strong>New Status:</strong>'),
            'status_id'=>array('type'=>'combobox','label'=>"",'value'=>$status_id,'table'=>'documentation_statuses','fcol'=>'status_id','display'=>'name')
        );

        $html = mkForm(array('onclick'=>'BurnDocumentationReview.save('.$documentation_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function editReviewForm($documentation_review_id)
    {
        /**
         *  Add a review item.
         */

        $review = fetch_row("SELECT * FROM documentation_reviews WHERE documentation_id = ?", $documentation_review_id);
        extract($review);

        $ctls = array(
            'documentation_id'=>array('type'=>'hidden2','value'=>$documentation_id),
            'html'=>array('type'=>html,'value'=>'<div style="margin: 0px 0px 10px 0px; font-size: 11px;"><i class="glyphicon glyphicon-info-sign"></i> Burn Request status cannot be changed while editing a previously submitted review.</div>'),
            'comment'=>array('type'=>'memo','with_label'=>false,'placeholder'=>'Review Comment.','value'=>$comment),
        );

        $html = mkForm(array('onclick'=>'BurnDocumentationReview.update('.$documentation_review_id.')','controls'=>$ctls,'id'=>'review_form','cancel'=>'true',
            'suppress_legend'=>true,'theme'=>'modal'));

        return $html;
    }

    public function reviewSave($review)
    {
        /**
         *  Extract the review form data. Insert then return errors.
         */

        extract($review);

        // If the status has changed update it.
        if ($actual_status != $status_id) {
            $burn_plan_sql = $this->pdo->prepare("UPDATE documentation SET status_id = ? WHERE documentation_id = ?");
            $burn_plan_sql->execute(array($status_id, $documentation_id));
            if ($burn_plan_sql->rowCount() > 0) {
                $success_message .= "The accomplishment status has been updated. ";
            } else {
                $result['error'] = true;
                $error_message .= "The accomplishment request status was not updated. ";
            }
        }

        // Get additional fields for the burn review.
        $added_by = $_SESSION['user']['id'];
        $now = now();
        $last_burn_update = fetch_one("SELECT updated_on FROM documentation WHERE documentation_id = ?", $documentation_id);

        // Get some validation specs.
        $valid = $this->validateReviewSave($review);
        $actual_status = $this->getStatus($burn_id);

        // Insert the review comment.
        $insert_sql = $this->pdo->prepare("INSERT INTO documentation_reviews (documentation_id, added_by, added_on, last_burn_update, comment) VALUES (?, ?, ?, ?, ?)");
        $insert_sql->execute(array($documentation_id, $added_by, $now, $last_burn_update, $comment));
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

    public function reviewUpdate($review, $documentation_review_id)
    {
        /**
         *  Extract the review form data. Update then return errors.
         */

        extract($review);

        $updated_by = $_SESSION['user']['id'];
        $last_burn_update = fetch_one("SELECT updated_on FROM documentation WHERE documentation_id = ?;", $documentation_id);

        // Insert the review comment.
        $update_sql = $this->pdo->prepare("UPDATE `documentation_reviews` SET updated_by = ?, last_burn_update = ?, comment = ? WHERE documentation_review_id = ?;");
        $update_sql = execute_bound($update_sql, array($updated_by, $last_burn_update, $comment, $documentation_review_id));
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

    private function validateReviewSave($review)
    {
        /**
         *  This function confirms the review can be saved.
         */

        // Defaults
        $invalid_actual_statuses = array(1,4);
        $available_statuses = array(2,3);

        extract($review);

        $actual_status = $this->getStatus($burn_id);

        // Check the old and new status.
        if (in_array($actual_status, $invalid_actual_statuses)) {
            // The status is not reviewable.
            $result['valid'] = false;
            $error_message .= "The burn is not reviewable according to its status. ";
        } elseif (in_array($actual_status, $available_statuses)) {
            // The status is under review.
            //$error_message .= "The burn is reviewable according to its status. ";
        }

        return $result;
    }

    public function approveForm($documentation_id)
    {
        /**
         *  Approve a burn plan.
         */

        $accomplishment = fetch_row("SELECT location, status_id FROM documentation WHERE documentation_id = ?;", $documentation_id);

        if (in_array($accomplishment['status_id'], array($this->under_review_id))) {
            // Under Review, it can be approved.
            $html = "<div>
                <button class=\"btn btn-success btn-block\" onclick=\"BurnDocumentationReview.approve($documentation_id)\">Approve <strong>".$daily['location']."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>
            </div>";
        } elseif ($accomplishment['status_id'] == $this->revision_requested_id) {
            // Revision Requested. Check for edit.
            if ($this->reviewCheck($documentation_id)) {
                $message = "The burn plan has been edited since the last review but not re-submitted.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnDocumentationReview.notify($documentation_id)\">Notify Submitter to Resubmit</button>";
            } else {
                $message = "The burn plan has not been edited since the last review.";
                $button = "<button class=\"btn btn-default btn-block\" onclick=\"BurnDocumentationReview.notify($documentation_id)\">Notify Submitter to Revise</button>";
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

    public function approveBurn($documentation_id)
    {
        /**
         *  Final Approve a burn plan.
         */
       
        $approve_sql = $this->pdo->prepare("UPDATE documentation SET status_id = ? WHERE documentation_id = ?");
        $approve_sql = execute_bound($approve_sql, array($this->approved_id, $documentation_id));
        if ($approve_sql->rowCount() > 0) {
            $result['message'] = status_message("The accomplishment is approved.", "success");
            
            // Notify.
            //$notify = new \Info\Notify($this->db);
            //$notify->burnerFinalApproval($burn_id);
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The accomplishment was not successfully approved.", "error");
        }
        return $result;
    }
    
    private function dateFilter()
    {
        $html = date_filter(array('object'=>$this->datatable,'column'=>2,'title'=>'Date Filter'));

        return $html;
    }

    private function agencyFilter()
    {

        $agencies = fetch_assoc_offset("SELECT agency as title FROM agencies ORDER BY agency;");

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

        $start_year = 2010;
        $current_year = date('Y') + 5;

        $years = array($current_year);

        for ($i = 1; $i <= ($current_year - $start_year); $i++) {
            $append = $current_year - $i;
            array_push($years, $append);
        }

        $html = label_filter(array('object'=>$this->datatable,'column'=>2,'function_name'=>'FilterYr',
            'wrapper_class'=>'filter_year','selector'=>'year','title'=>'Year',
            'selected'=>array(0),'info_array'=>$years));

        return $html;
    }

    private function statusFilter($selected)
    {
        /**
         *  Produces the datatables label filter for statues.
         */
        
        // Status info array
        $info = array(
            1=>array('id'=>1,'title'=>'Draft','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','class'=>'warning'),
            2=>array('id'=>2,'title'=>'Under Review','color'=>'#f0ad4e','opacity'=>'0.5','zindex'=>'101','zindex'=>'102','class'=>'warning'),
            3=>array('id'=>3,'title'=>'Revision Requested','color'=>'#d9534f','opacity'=>'0.05','zindex'=>'103','class'=>'danger'),
            4=>array('id'=>4,'title'=>'Approved','color'=>'#5cb85c','opacity'=>'0.75','zindex'=>'105','class'=>'success'),
            5=>array('id'=>5,'title'=>'Disapproved','color'=>'#d9534f','opacity'=>'0.75','zindex'=>'105','class'=>'danger')
        );
        unset($info[1]);

        $html = label_filter(array('object'=>$this->datatable,'column'=>5,'function_name'=>'FilterSt',
            'wrapper_class'=>'filter_status','selector'=>'status','title'=>'Statuses',
            'selected'=>array(2, 3),'info_array'=>$info));

        return $html;
    }

    private function modifySelected()
    {
        /** 
         *  Modify a group of burns
         */        

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','system_admin'), 'write');
        
        if ($permissions['deny']) {
            $html = "";
        } else {
            $html = "<div class=\"dt_filter_section\">
                <strong>Toolbar</strong>
                <br>
                ";
    
            $html .= "<div style=\"font-size: 12px;\">
                <a onclick=\"BurnDocumentationReview.approveAll(selected)\" style=\"cursor: pointer\">Approve Selection</a><br>
                </div>";
    
            $html .= "</div>";
        }

        return $html;
    }


    public function reviewPage($documentation_id)
    {
        /**
         *  Construct a review html block.
         *  This is for a specific burn id (passed to $_GET on the page)
         */

        $permissions = checkFunctionPermissions($_SESSION['user']['id'], array('admin','admin_final','system_admin'), 'write');

        // Get the daily burn.
        $documentation = $this->get($documentation_id);

        // Construct the title.
        if (isset($documentation['burn_project']['project_name']) && isset($documentation['burn_project']['burn_project'])) {
            $title = "Burn Observation / ".$documentation['observation_date']." <small>".$documentation['burn_project']['project_number']." - ".$documentation['burn_project']['project_name']."</small>";
        } elseif (isset($documentation['observation_date'])) {
            $title = "Burn Observation / ".$documentation['observation_date'];
        } else {
            $title = "Burn Observation";
        }

        if ($documentation['status_id'] >= $this->approved_id || $permissions['deny']) {
            $upper_toolbar = "<div class=\"btn-group pull-right\">
                <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentation.ownerChangeForm($documentation_id)\">Change Owner</btn>
            </div>";
            //$review_toolbar = "<div class=\"btn-group pull-right\">
            //        <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentationReview.reviewForm($documentation_id)\">Add Review</btn>
            //    </div>";
            $conditions_toolbar = "";
        } else {
            $upper_toolbar = "<div class=\"btn-group pull-right\">
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentation.ownerChangeForm($documentation_id)\">Change Owner</btn>
                    <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentationReview.approveForm($documentation_id)\">Approve</btn> 
                </div>";
            //$review_toolbar = "<div class=\"btn-group pull-right\">
            //        <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentationReview.reviewForm($documentation_id)\">Add Review</btn>
            //    </div>";
            //$conditions_toolbar = "<div class=\"btn-group pull-right\">
            //        <btn class=\"btn btn-sm btn-default\" onclick=\"BurnDocumentationReview.conditionForm($documentation_id)\">Add Note</btn>
            //    </div>";
        }

        $status = $this->getStatusLabel($documentation_id);
        $map = $this->getMap($documentation_id);
        $table = $this->tablifyFields($documentation_id);
        $sidebar = $upper_toolbar;
        $sidebar .= $this->getContacts($documentation_id);
        //$sidebar .= $this->getConditions($documentation_id);
        //$sidebar .= $review_toolbar;
        //$sidebar .= $this->getReviews($documentation_id);
        $sidebar .= $this->getUploads($documentation_id);
    
        $observations = $this->getObservations($documentation_id);
        $return_href = $this->mainUrl();

        // Construct the header.
        $html = "<div class=\"row\">
            <div class=\"col-sm-12\">
                <span class=\"pull-right\">
                    $return_href
                    $status
                </span>
                <h3 class=\"\">$title</h3>
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

        $html .= "</div>
               $table
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-block\"></div>
                    $sidebar
                    $observations
                </div>  
            </div>
            ";

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

    public function getStatus($documentation_id)
    {
        /**
         *  Constructs the status bar for the header..
         */

        $status = fetch_row(
            "SELECT s.class, s.name, s.description 
            FROM documentation d
            JOIN documentation_statuses s ON(d.status_id = s.status_id)
            WHERE d.documentation_id = ?", $documentation_id
        );

        $html = "<h4><div title=\"".$status['description']."\" class=\"".$status['class']."\">".$status['name']."</span></h4>";

        return $html;
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

        $markers = fetch_assoc("SELECT d.documentation_id, d.location, d.status_id, ag.agency FROM documentation d JOIN agencies ag ON(d.agency_id = ag.agency_id) WHERE status_id > {$this->draft_id}");
        $zoom_to_fit = false;

        global $map_center;

        if ($zoom_to_fit == true) {
            $zoom = "map.fitBounds(bounds);";
        } else {
            $zoom = "";
        }

        if ($burn['error'] == false && !empty($burn)) {
            $center = "zoom: 6, 
                center: new google.maps.LatLng($map_center),";
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
            $marker_arr = "var documentation = [\n ";
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
                $marker_arr .= "[".$value['burn_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$marker_status['title']."', '".$marker_status['color']."', '".$value['status_id']."', '".$value['location']."', '".$value['agency']."']$comma\n ";
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
                                ignition: marker[6],
                                ignition2: marker[6],
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
                                ignition: marker[6],
                                ignition2: marker[6],
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

                setMarkers(map, documentation)
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
