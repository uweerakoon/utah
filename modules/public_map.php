<?php

namespace publicZone;

class publicMap
{

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->public_form_id = 'public_map';
    }

    public function detailPage($burn_id)
    {
        /**
         *  The Public Detail Page
         */

        $burn_manager = new \Manager\Burn($this->db);

        // Get the Burn.
        $burn = $burn_manager->get($burn_id);

        // Construct the title.
        if (isset($burn['burn_project']['project_name']) && isset($burn['burn_project']['project_number'])) {
            $title = $burn['burn_project']['project_name']." / ".$burn['burn_project']['project_number']." - ".$burn['start_date'];
        } elseif (isset($burn['burn_project']['project_name'])) {
            $title = $burn['burn_project']['project_name']." - ".$burn['start_date'];
        } else {
            $title = "Burn Request";
        }
    
        // Statics
        $return_link = "<a class=\"pull-right\" href=\"/map.php\">Return to Map</a>";
    
        // Get HTML blocks.
        $status = $burn_manager->getStatusLabel($burn_id);
        $map = $burn_manager->getMap($burn_id);
        $table = $this->tablifyFields($burn_id);
        
        // Construct the HTML array.
        $html['header'] = "<div class=\"row\">
            <div class=\"col-sm-12\">
                <span class=\"pull-right\">
                    $return_link
                    <br>
                    $status
                </span>
                <h3>".$title." <small>Burn Request</small></h3>
            </div>
        </div>";
    
        $html['main'] = "<div class=\"row\">  
            <div class=\"col-sm-12\">
                <h4>Form 4: Burn Request Info</h4>
                <hr>
                $map
                <br>
                $table
            </div>
        </div>";
        
        return $html;
    }

    public function tablifyFields($burn_id)
    {
        /**
         *
         */

        $burn_manager = new \Manager\Burn($this->db);

        // Get the Burn.
        $burn = $burn_manager->get($burn_id);

        $v_title = "Value";
        $style = "";
        $colspaces = "<col width=\"60%\">
            <col width=\"47%\">
            <col width=\"12%\">";

        $title = "Burn Information";
        $value_array = $burn;
        $fields_array = array('airshed_id','request_acres',
            'start_date','end_date','daily_acres','pm_sampler_model','pm_sampler_id');

        $html = "<table $style class=\"table table-responsive table-condensed\">
            $colspaces
            <thead>
            <tr><th>$title</th><th>$v_title</th><th></th></tr>
            </thead>
            <tbody>";

        foreach ($fields_array as $key) {

            $reference = $burn_manager->value_map[$key];
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

	public function filterForm()
	{
        /**
         *  Map Filter Modal Form. 
         */

        $start_date = date('Y-m-d', strtotime("$today -1 week"));
        $end_date = today();
        $status_id = array(5);

        if (isset($_SESSION['public_map'])) {
            extract(json_decode($_SESSION['public_map'], true));
        }       

		$ctls = array(
			'start_date'=>array('type'=>'date','label'=>'Starting Date','value'=>$start_date),
			'end_date'=>array('type'=>'date','label'=>'Ending Date','value'=>$end_date),
            'status_id'=>array('type'=>'combobox','label'=>'Burn Statuses','sql'=>'SELECT status_id, name FROM burn_statuses WHERE status_id IN(2,3,5)','display'=>'name','fcol'=>'status_id','multiselect'=>true,
                'value'=>$status_id)
		);
       
        $html .= mkForm(array('theme'=>'modal','id'=>'map_fitler','controls'=>$ctls,'title'=>null,'suppress_submit'=>true,'suppress_legend'=>true));
    
        $html .= "<div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"PublicMap.filter()\">Filter Map</button>
                    </div>
                    <div class=\"btn-group\">
                        <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                    </div>
                </div>";

		return $html;
	}

    public function getMarkers()
    {
        /**
         *
         */
        
        $burn_manager = new \Manager\Burn($this->db);
        $args = array('start_date'=>date('Y-m-d', strtotime("$today -1 week")),'end_date'=>today(),'status_id'=>array($burn_manager->approved_id));
        $args = merge_args(func_get_args(), $args);
        extract($args);

        if (is_array($status_id)) {
            $status_id = implode(',', $status_id);
        }

        $markers = fetch_assoc(
            "SELECT b.burn_id, b.status_id, b.location, 
            CONCAT(bp.project_number, ': ', b.start_date, ' to ', b.end_date, 
                ', ', 'Acres Requested: ', b.request_acres) as name 
            FROM burns b JOIN burn_projects bp ON(b.burn_project_id = bp.burn_project_id)
            WHERE b.status_id IN($status_id)
            AND (b.start_date BETWEEN ? AND ?
            OR b.end_date BETWEEN ? AND ?)
            AND expired = FALSE
            LIMIT 2000;", 
            array($start_date, $end_date, $start_date, $end_date)
        );

        $array = array();

        foreach ($markers as $value) {
            $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $value['location']));
            $marker_status = $burn_manager->retrieveStatus($value['status_id']);
            array_push($array, array($value['burn_id'], $marker_latlng[0], $marker_latlng[1], $value['name'], $marker_status['color']));
        }

        // Update the $_SESSION to include the args.
        $_SESSION['public_map'] = json_encode($args, true);

        if($markers['error']) {
            return null;
        } else {
            return json_encode(array('data'=>$array,'json'=>$args), true);
        }        
    }

    public function map()
    {
        /**
         *  The Public Map display.
         */

        $burn_manager = new \Manager\Burn($this->db);

        $args = array('start_date'=>date('Y-m-d', strtotime("$today -3 week")),'end_date'=>today(),'status_id'=>array($burn_manager->approved_id));
        $args = merge_args(func_get_args(), $args);
        extract($args);

        if (isset($_SESSION['public_map'])) {
            extract(json_decode($_SESSION['public_map'], true));
        }

        if (is_array($status_id)) {
            $status_id = implode(',', $status_id);
        }

        $map_center = "39.545043281652774, -111.62867635937499";
        $status_icons = $this->status_icons;
        $center = "zoom: 7,
        center: new google.maps.LatLng({$map_center}),";
        $bounds = "";        
        
        $markers = fetch_assoc(
            "SELECT b.burn_id, b.status_id, b.location, 
            CONCAT(bp.project_number, ': ', b.start_date, ' to ', b.end_date, 
                ', ', 'Acres Requested: ', b.request_acres) as name 
            FROM burns b JOIN burn_projects bp ON(b.burn_project_id = bp.burn_project_id)
            WHERE b.status_id IN($status_id)
            AND (b.start_date BETWEEN ? AND ?
            OR b.end_date BETWEEN ? AND ?)
            AND expired = FALSE
            LIMIT 2000;", 
            array($start_date, $end_date, $start_date, $end_date)
        );

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
                $marker_status = $burn_manager->retrieveStatus($value['status_id']);
                $marker_arr .= "[".$value['burn_id'].", ".$marker_latlng[0].", ".$marker_latlng[1].", '".$value['name']."', '".$marker_status['color']."']$comma\n ";
            }
            $marker_arr .= "];\n";
        } else {
            $marker_arr = "var Burns = [];";
        }
    
        // Append it to the function.
        $marker = "
            $marker_arr
                
            function setMarkers(map, markers) {
                var gmarkers = [];

                for (var i = 0; i < markers.length; i++) {
                    var marker = markers[i];
                    var myLatLng = new google.maps.LatLng(marker[1], marker[2]);
                    
                    if (checkLegacy()) {
                        gmarkers[i] = new google.maps.Marker({
                            position: myLatLng,
                            map: map,
                            title: marker[3],
                            id: marker[0]
                        });
                    } else {
                        gmarkers[i] = new google.maps.Marker({
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

                    bindMarkers(map, gmarkers[i]);                    
                }
                
                function bindMarkers(map, marker)
                {
                    google.maps.event.addListener(marker, 'click', function() {
                        window.location='/map.php?detail=true&id='+marker.id;return false;
                    });
                }

                return gmarkers;
            }
        ";
    
        $html = "
            <style>
                .map-canvas {height:612px;}
    
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
                
                var Overlay = new Overlay();
                    
                var markers = setMarkers(map, Burns);
                Overlay.setControls(map);               

            </script>
            ";
    
        return $html;
    }
}
