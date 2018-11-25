<?php
/**
 * HTML generation library. Builds html general use HTML.
 */

function show()
{
    $args=array("db"=>null,"sql"=>null,"table"=>null,"pkey"=>null,"insertlike"=>false,"childlink"=>null,
            "childcolumn"=>null,"no_results_message"=>"The table did not query any results. <span class=\"pull-right\">$new_link</span>",
            "no_results_class"=>"error","editpage"=>null,"newpage"=>null,"table_class"=>'table','include_email'=>true,
            "tableid"=>null,'show_zeros'=>true,'format'=>true,'digits'=>'3','color_field'=>null,
            "links"=>null,"include_new"=>false,'new_defaults'=>null, "tooltips"=>array(),
            "id"=>rand(),'paginate'=>false,'new_function'=>null, "refresh_function"=>null,'include_header'=>true,'include_delete'=>null,'include_edit'=>true,
            "filter"=>null,'edit_criteria'=>array(),'rows'=>10,'edit_function'=>null,'delete_function'=>null,'sort_column'=>1,'sort_direction'=>'desc',
            'static_height'=>false,'height'=>'425','hidden_id'=>false,'id_col'=>false,'include_view'=>false,'view_function'=>null,'view_href'=>null,'pdf'=>false);

    // need to add option for a more custom update function. Right now the table will refresh itself
    // which works great for small tables but will be slow for large tables.
    // or we create a custom paginate??

    // get the arguments
    $nargs = func_get_args();
    // jsonify them
    $args_json = json_encode($nargs, true);
    // merge them
    extract(merge_args($nargs, $args));

    if (is_null($db)) {
        global $db;
    }

    if (is_null($table)) {
        $new_label = "New Record";
    } else {
        // Automatically generates the new record button label from the table name.
        $new_label = "New ". format_title($table);
    }

    $pdo = $db->get_connection();

    // debug($nargs['include_new']===false);
    //debug($include_new===false);

    $rnd = $id;//rand();

    $include_new = fix_boolean($include_new);
    $include_insert = fix_boolean($include_insert);
    $include_delete = fix_boolean($include_delete);
    $include_edit = fix_boolean($include_edit);

    //debug($include_delete);

    if (strpos($table_class, 'dataTable')!==false) {
        $paginate = true;
    }
    // tooltips is an array of columns that will NOT be shown but instead be the tooltip for
    // the first row?
    // in case we are not using permissions we can still use this page
    if (isset($_SESSION['user']['level_id'])) {
        $plevel=$_SESSION['user']['level_id'];
    } else {
        $plevel=0;
    }
    // now override for permissions
    if ($plevel >= 1 & $include_edit) {
        // no edit button
        $allow_edit = true;
    } else {
        $allow_edit = false;
    }

    if ($plevel >= 1 & $include_new !== false) {
        // no edit button
        $allow_insert = true;
    } else {
        $allow_insert = false;
    }

    if ($include_delete === true || ($plevel >= 2 & $include_delete !== false)) {
        // no edit button
        $allow_delete=true;
    } else {
        $allow_delete=false;
    }

    if (is_null($delete_function)) {
        $delete_function = "delete_record('$table','$pkey', @@, rt$rnd)";
    } else {
        $delete_function = $delete_function."(@@)";
    }

    if (is_null($edit_function)) {
        $edit_function = "edit_form('$table','$pkey', @@, 'rt$rnd')";
    } else {
        $edit_function = $edit_function."(@@)";
    }

    if (is_null($view_href) && is_null($view_function)) {
        $view_href= "#(@@)";
        $view_function = "";
    }

    if (!is_null($pkey) && !is_null($table) && $include_view) {
        $view_link = "<a class=\"clear_link\" href=\"$view_href\" onclick=\"$view_function\"><small>View</small></a> ";
    } else {
        $view_link = "";
    }

    // set up the new link
    if (($allow_insert  & !is_null($pkey) & !is_null($table) & (empty($include_new)||$include_new!==false)) || ($include_new  & !is_null($pkey) & !is_null($table))) {
        // if ($allow_insert & !is_null($pkey) & !is_null($table) & $include_new) {
        // check for defaults
        //debug($new_defaults);

        if (is_null($new_defaults)) {
            $nd="{}";
        } else {
            // loop through and build the javascript
            $nd = "{";
            //debug($new_defaults);
            foreach ($new_defaults as $key => $value) {
                $nd .= "$key: $value,";
            }
            $nd = rtrim($nd, ',')."}";
        }
        
        // save it for later
        if (is_null($new_function)) { // assume we will use ajax method
            $new_link = "<button class=\"btn btn-sm btn-default clear_link\"  onclick=\"insert_form('$table', '$pkey', 'rt$rnd'); return false;\">$new_label</button><br>\n";
        } else {
            // use the page that was passed, assuming all is set up??
            $new_link = "<button class=\"btn btn-sm btn-default clear_link\"  onclick=\"$new_function; return false;\">$new_label</button><br>\n";
        }
    } else {
        // if for any reason we want an empty string
        $new_link="";
    } // end set up the insert link

    // set up the delete link
    if ($allow_delete & !is_null($pkey) & !is_null($table)) {
        $delete_link = "<a class=\"clear_link\" href=\"#\" onclick=\"$delete_function\"><small>Delete</small></a> ";
    } else {
        $delete_link = "";
    }

    // set up the edit link so we can replace the id if needed
    if ($allow_edit & !is_null($pkey) & !is_null($table)) {
        // now check to see if a custom page was sent
        // in both cases is the link is not empty we will str_replace the @@ with the id
        $edit_label = "edit";
        // The editpage variable allows someone to pass a custom link to the show function and override the default edit link
        if (is_null($editpage)) {
            $edit_link = "<a class=\"clear_link\" href=\"#@@\" onclick=\"$edit_function\"><small>Edit</small></a> ";
        } else {
            $edit_link = "<a class=\"clear_link\" href=\"$editpage@@\"><img src=\"$edit_icon\" alt=\"edit\"></a>";
        }
    } else {
        // make it empty
        $edit_link="";
    }

    // finally we can get the results of the query
    // clean up the sql
    $sql = str_replace(array("\r","\n"), " ", $sql);
    // must remove order by
    $idx = strpos(strtolower($sql), "order by");
    if ($idx !== false) {
        $sql_link = substr($sql, 0, $idx);
    } else {
        $sql_link = $sql;
    }

    $page = $_SERVER['REQUEST_URI'];
    $fxName = "dtState";

    if (isset($_SESSION['user']['filter_state'][$page][$fxName])) {
        $sort_column = $_SESSION['user']['filter_state'][$page][$fxName]['column'];
        $sort_direction = $_SESSION['user']['filter_state'][$page][$fxName]['direction'];
    }

    if (!is_null($filter)) {
        $filter = "dt$rnd.fnFilter(\"$filter\")";
    } else {
        $filter="";
    }

    if ($static_height) {
        $h_cond = "\"sScrollY\": \"$height\",";
    } else {
        $h_cond = "";
    }

    if ($include_edit || $include_view) {
        $sort_defs = "\"aoColumnDefs\": [{
                 \"bSortable\": false,
                 \"aTargets\": [ 0 ]
            }],";
    }

    if ($hidden_id) {
        $column_defs = "\"columnDefs\": [{
                    \"targets\": [ 0 ],
                    \"visible\": false,
                    \"searchable\": true
                }],";
    } else {
        $column_defs = "";
    }

    if ($id_col) {
        $id_def = "\"fnCreatedRow\": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData[0]);
            }";
    }

    if ($paginate) {
        $dt_script ="
var dt$rnd = $('#tbl$rnd').dataTable({
    \"lengthChange\": false,
    \"bStateSave\": true,
    \"iDisplayLength\": $rows,
    $sort_defs
    \"order\": [[ $sort_column, \"$sort_direction\" ]],
    $h_cond
    $column_defs
    $id_def
});
$filter
";

        $tbl_style = "
                <style>
                #tbl$rnd{
                clear: both;
                 }
                </style>
                ";
    } else {
        $dt_script ="";
        $tbl_style = "";
    }
    //  var mysql$rnd = \"" . urlencode($sql_link) . "\";

    if (is_null($refresh_function)) {
        $refresh_function = "refresh_table('div$rnd',tbl$rnd);";
    }

    $sql_result = $pdo->query($sql);
    if ($sql_result->rowCount()>0) {
        $result = $sql_result->fetchall(PDO::FETCH_ASSOC);
    }

    $tbl_script = "
$tbl_style
<script >
var tbl$rnd = $args_json;
function rt$rnd() {
    $refresh_function
}
$dt_script

var dtState$rnd = new dtState('dtState$rnd')

$(function() {
    dtState$rnd.defineTable(dt$rnd);
});

$('#tbl$rnd th').click( function () {
    dtState$rnd.getTableParams();
    dtState$rnd.pushState();
})

</script>
";

    $html = "<div class=\"\" id=\"div$rnd\">";
    if (!$result) {
        // if there is no result show the error
        $html .= status_message($no_results_message, $no_results_class);
        $html .= $tbl_script . $new_link;
    } else if ($sql_result->rowCount() == 0) {
        $html .= "$no_results_message<br>" . $tbl_script . $new_link; //initialize
        // check to see if we want a new record link
        // must have proper level and table information
    } else {
        // we have some rows to report
        /* if (!empty($table)) { */
        /*   $unique_id=str_replace(".","_",$table) . "_tbl"; */
        /* } else { */
        /*   $unique_id="mytable_tbl"; */
        /* } */
        $html .= "<table id=\"tbl$rnd\" class=\"$table_class\"";
        // create a few fields that we will ignore
        $ignored_fields = array_merge(array($pkey, $color_field), $tooltips);

        if ($include_header) {
            $html.=">\n<thead><tr>";
            // if a pkey was specified we can add an edit link
            if (!empty($edit_link)||!empty($view_link)||!empty($delete_link)) {
                $html .= "<th>&nbsp</th>";
            }

            // not sure how I want to add this yet
            //if ($allow_insert & $insertlike) {$html .= "<th>&nbsp;</th>";}
            // now we can loop through the columns to make the table header
            foreach ($result[0] as $fieldname => $value) {
                //$fieldinfo = mysql_fetch_field($result,$int);
                // check to see if the field is the pkey
                if (!in_array($fieldname, $ignored_fields)) {
                    $html .= "<th>" . str_replace("_", "<br>", $fieldname) . "</th>";
                }
            }
            // next check to see if we have any link fields
            if (!is_null($links)) {
                // loop through them
                // debug($links);
                foreach ($links as $link) {
                    // debug($link);
                    $html .= "<th>" . $link['label'] . "</th>";
                }
            }
            $html .= "</tr></thead>\n<tbody>\n";
        }
        // done with the header
        //print_r($sql_result);
        $i = 0;
        // loop through the records for the rows
        foreach ($result as $key => $row) {
            // first thing we do is get the id to use for the whole row
            $i++; // counter for row id and row class
            if (is_null($pkey)) {
            // just in case we dont have the pkey we want to use something for the id
                $id=$i;
            } else { // we are going to use the pkey
                $id=$row[$pkey];
            }
            // apply some color if needed
            if (!empty($color_field)) {
                $color_tag = "style=\"background-color: " . $row[$color_field] ."\"";
            } else {
                // make sure we reset it if we dont need it
                $color_tag="";
            }
            // set up the row
            // check for tooltips
            $tooltip="";
            if (!is_null($tooltips)) {
                foreach ($tooltips as $tip) {
                    $tooltip.=$tip.": ".$row[$tip]."<br>";
                }
            }
            $html .= "<tr data-toggle=\"tooltip\" title=\"$tooltip\" class=\"row" . ($i % 2) . "\">";
            // now we update the edit link
            if (!empty($edit_link)||!empty($delete_link)||!empty($view_link)) {
                // start with this
                $allow_row_edit = true;
                // at this point we need to check for any edit criteria
                if (count($edit_criteria)>0) {
                    // now we have to loop through each key->value pair
                    foreach ($edit_criteria as $field => $value) {
                        if ($row[$field]!==(string) $value) {
                            $allow_row_edit = false;
                        }
                    //debug($row[$field]." must equal ".$value." = ".$allow_row_edit."<br>",false);
                    }
                }

                if ($allow_row_edit||!empty($view_link)) {
                    $html .= "<td id=$pkey$id><span style=\"\">";
                    $post_row = "</span></td>";
                }

                if (!empty($view_link)) {
                    $view_id = str_replace('@@', $id, $view_link);
                    $html .= "$view_id";
                }

                if ($allow_row_edit) {
                    $edit_id = str_replace('@@', $id, $edit_link);
                    // and then add it to the table as the first cell
                    $html .= "$edit_id";
                }

                if (!empty($delete_link)) {
                    $delete_id = str_replace('@@', $id, $delete_link);
                    // and then add it to the table as the first cell
                    $html .= "$delete_id";
                }

                $html .= $post_row;
            }
            // now we update the delete link
            // done with the first column
            // loop through each of the columns
            $col=1;
            foreach ($row as $key => $value) {
                $id_val = "id=" . $key . $id;
                if (!in_array($key, $ignored_fields)) {
                    if ($col==1 and !is_null($childlink)) {
                        $html .= "<td $id_val $color_tag><a href=\"$childlink" .
                            $row[$childcolumn] . "\">$value</a></td>";
                        //} elseif ($key == "emission_factor" or $key == "nox_ef" or $key=="pm_ef") {
                    } elseif (empty($value)&!$show_zeros) {
                        $html .= "<td $id_val $color_tag>--</td>";
                    } else {
                        $html .= "<td $id_val $color_tag>" . str_replace("\n", "<br>", $value) . "</td>";
                    }
                    $col++;
                }
            }
            // now we can create the actual link
            if (!is_null($links)) {
            // loop through them
            // debug($links);
                foreach ($links as $link) {
                    // build the link
                    // check to see if its a link or onclick
                    if (isset($link['onclick'])) {
                        //debug($link);
                        $url = "\t<td><a class=\"clear_link\" onclick=\"" .
                            $link['onclick'] . "; return false\">" . $link['display'] . "</a></td>";
                        foreach ($link['replace'] as $old => $col) {
                            $url = str_replace($old, $row[$col], $url);
                        }
                        $html .= $url;
                    } else {
                        $url = "<a href=\"" . $link['link'] . "?";
                        // now we can add the GET's
                        foreach ($link['get'] as $get => $value) {
                            $url .= "$get=" . $row[$value] ."&";
                        }
                        $url = rtrim($url, "&");
                        $url .= "\">" . $link['display'] . "</a>";
                        $url = "\t<td>$url</td>\n";
                        $html .= $url;
                    }
                }
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody></table>\n";
        // add the script
        $html .= $tbl_script;
        //$sql_url=urlencode($sql);
        $html .= "$new_link";
        // could update this to pass via json as well
        if ($include_email) {
            // also want to check to see if the jobs schema exists
            //$jobs = scalar("SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = 'jobs'");
            if ($jobs>0) {
                $html .= "<a class=\"clear_link\" onclick=\"download_data(mysql); return false\">email data</a><br>";
            }
        }
    }
    // close the div
    $html .= "</div>";

    $result = array(
        'html'=>$html,
        'id'=>'tbl'.$rnd,
        'datatable'=>'dt'.$rnd,
    );

    return($result);
}

function show_boundary($boundary, $zoom_to_fit = false, $control_title = 'Return to boundary')
{
    /**
     *  Non-editable boundary display.
     */

    $center_control = "
    function centerControl(controlDiv, map) {
    
      // Set CSS styles for the DIV containing the control
      // Setting padding to 5 px will offset the control
      // from the edge of the map
      controlDiv.style.padding = '5px';
    
      // Set CSS for the control border
      var controlUI = document.createElement('div');
      controlUI.style.backgroundColor = 'white';
      controlUI.style.borderStyle = 'solid';
      controlUI.style.borderWidth = '1px';
      controlUI.style.borderColor = 'rgba(0, 0, 0, 0.4)';
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
        map.fitBounds(bounds)
      });
    
    }";

    if ($zoom_to_fit == true) {
        $zoom = "map.fitBounds(bounds)";
    } else {
        $zoom = "";
    }

    if (!empty($boundary)) {
        $center = "zoom: 6, 
            center: new google.maps.LatLng(34.4,-111.8),";
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
          strokeColor: '#FF6300',
          strokeOpacity: 0.8,
          strokeWeight: 2,
          fillColor: '#FF6300',
          fillOpacity: 0.35,  
          bounds: bounds,
        });
    
        rectangle.setMap(map);";

    } else {
        $center = "zoom: 6,
        center: new google.maps.LatLng(34.4,-111.8),";
        $bounds = "";
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

            $zoom

            var Overlay = new Overlay();
            Overlay.setControls(map);

        </script>
        ";

    return $html;
}

function show_daily($value, $boundary, $zoom_to_fit = false, $control_title = 'Zoom to daily burn request')
{
    /**
     *  Non-editable boundary display.
     */

    $center_control = "
    function centerControl(controlDiv, map) {
    
      // Set CSS styles for the DIV containing the control
      // Setting padding to 5 px will offset the control
      // from the edge of the map
      controlDiv.style.padding = '5px';
    
      // Set CSS for the control border
      var controlUI = document.createElement('div');
      controlUI.style.backgroundColor = 'white';
      controlUI.style.borderStyle = 'solid';
      controlUI.style.borderWidth = '1px';
      controlUI.style.borderColor = 'rgba(0, 0, 0, 0.4)';
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
          map.setZoom(10);
          map.panTo(marker.position);
      });
    
    }";

    if ($zoom_to_fit == true) {
        $zoom = "map.setZoom(10);
                map.panTo(marker.position);";
    } else {
        $zoom = "";
    }

    if (!empty($boundary)) {
        $center = "zoom: 6, 
            center: new google.maps.LatLng(34.4,-111.8),";
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
        $center = "zoom: 6,
        center: new google.maps.LatLng(34.4,-111.8),";
        $bounds = "";
    }

    if (!empty($value)) {
        $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $value));
        $marker_json_str = "{lat:".$marker_latlng[0].",lon:".$marker_latlng[1]."}";

        $marker = "
            var marker_latlng = $marker_json_str

            var marker_center = new google.maps.LatLng(marker_latlng.lat, marker_latlng.lon);

            var marker = new google.maps.Marker({
                position: marker_center,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    strokeColor: '#000',
                    strokeOpacity: 0.8,
                    strokeWeight: 3,
                    fillColor: '#000',
                    fillOpacity: 0.35
                },
            });

            marker.setMap(map)
        ";
    } else {
        $center = "zoom: 6,
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

function mm_array()
{
    /**
     *  Gets an array of all of a many to many relationship.
     */

    $args = array('ptable'=>null,'stable'=>null,'mmtable'=>null,'pcol'=>null,'scol'=>null,'sdisplay'=>null,'pvalue'=>null);

    $nargs = func_get_args();
    $args_json = json_encode($nargs, true);
    extract(merge_args($nargs, $args));

    if (!is_null($pvalue)) {
        $sql = "SELECT s.$scol as id, s.$sdisplay as display 
        FROM $mmtable m
        JOIN $stable s ON(m.$scol = s.$scol)
        WHERE m.$pcol = $pvalue;";
    } else {
        $sql = "SELECT s.$scol as id, s.$sdisplay as display 
        FROM $mmtable m
        JOIN $stable s ON(m.$scol = s.$scol);";
    }

    $arr = fetch_assoc($sql);

    return $arr;
}

function mm_values()
{
    $args = array('ptable'=>null,'stable'=>null,'mmtable'=>null,'pcol'=>null,'scol'=>null,'sdisplay'=>null,'pvalue'=>null);

    $arr = mm_array(merge_args(func_get_args(), $args));
    $values = array();

    foreach ($arr as $value) {
        array_push($values, $value['id']);
    }

    return $values;
}

function mm_list()
{
    /**
     *  Constructs a comma separated string of couplet items (e.g. for use in html).
     */

    $args = array('ptable'=>null,'stable'=>null,'mmtable'=>null,'pcol'=>null,'scol'=>null,'sdisplay'=>null,'pvalue'=>null);

    $arr = mm_array(merge_args(func_get_args(), $args));

    $html = "";

    $i = 0;

    foreach ($arr as $key => $value) {
        $display = $value['display'];
        
        if (++$i === count($arr)) {
            $html .= $display;
        } else {
            $html .= $display . ", ";
        }
    }

    return $html;
}

function mm_label()
{
     /**
     *  Constructs a comma separated string of couplet items (e.g. for use in html).
     */

    $args = array('ptable'=>null,'stable'=>null,'mmtable'=>null,'pcol'=>null,
        'scol'=>null,'sdisplay'=>null,'label_class'=>'label-primary','pvalue'=>null);

    $nargs = func_get_args();
    $args_json = json_encode($nargs, true);
    extract(merge_args($nargs, $args));

    $arr = mm_array(merge_args($nargs, $args));

    $html = "<span>";

    if ($arr['error']) {
        $html .= "<span class=\"label $label_class\">N/A</span> ";
    } else {
        foreach ($arr as $key => $value) {
            $html .= "<span class=\"label $label_class\">" . $value['display'] . "</span> ";
        }
    }

    $html .= "</span>";

    return $html;
}

function status_message($message, $type = "")
{
    /**
     * Constructs a typical status message/warning.
     */

    if ($type == "info") {
            // An info message (blue).
            $class = "alert-info";
            $label = "Info:";
    } elseif ($type == "success") {
            // A success message (green).
            $class = "alert-success";
            $label = "Success:";
    } elseif ($type == "warning") {
            // A warning message (yellow).
            $class = "alert-warning";
            $label = "Warning!";
    } elseif ($type == "error") {
            // An error message (red).
            $class = "alert-danger";
            $label = "Error!";
    } else {
            // A non-descript message (clear/white).
            $class = "";
            $label = "";
    }

    $html = "<div class=\"alert $class\">
    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
    <strong>$label</strong> $message
    </div>";

    return $html;
}

function modal_message($message, $type = "")
{
    /**
     * Constructs a typical status message/warning.
     */

    if ($type == "info") {
            // An info message (blue).
            $class = "alert-info";
            $label = "Info:";
    } elseif ($type == "success") {
            // A success message (green).
            $class = "alert-success";
            $label = "Success:";
    } elseif ($type == "warning") {
            // A warning message (yellow).
            $class = "alert-warning";
            $label = "Warning!";
    } elseif ($type == "error") {
            // An error message (red).
            $class = "alert-danger";
            $label = "Error!";
    } else {
            // A non-descript message (clear/white).
            $class = "";
            $label = "";
    }

    $html = "<div class=\"alert $class\">
    <strong>$label</strong> $message
    </div>";

    return $html;
}

function code($code)
{
    /**
     * Constructed a <pre><code> wrapped html block to display code.
     */

    print_r("<pre><code>");
    print_r($code);
    print_r("</code></pre>");
}

function console($code)
{
    /**
     * An Immediately Invoked Console.log(code) pass;
     */

    $print_types = array('array','object','resource','string','unknown type');
    $triggered = date("T-H:i:s").substr((string)microtime(), 1, 8);

    $html = "<script type=\"text/javascript\">
            (function(){
                console.log('PHP-Console ($triggered):');";

        if (in_array(gettype($code), $print_types)) {
            $code = json_encode($code);
            $html .= "console.log($code);";
        } else{
            $html .= "console.log($code);";
        }

    $html .= "}());
        </script>";

    echo $html;
}

function date_filter()
{
    $args = array('object'=>null,'title'=>null,'column'=>null,
        'map'=>false,'fieldname'=>null);
    extract(merge_args(func_get_args(), $args));

    $current_date = date('Y-m-d');

    $page = $_SERVER['REQUEST_URI'];
    $fxName = 'dateFilter';

    if (isset($_SESSION['user']['filter_state'][$page][$fxName])) {
        $selected_date = $_SESSION['user']['filter_state'][$page][$fxName];
    }


    if ($map) {
        $gmap_register = "gm.registerFn($fxName, '$fieldname');";
        $gmap_oc = "gm.sync();";
    }

    $html = "<div class=\"dt_filter_section $wrapper_class\">
        <strong>$title</strong><small class=\"pull-right\"></small>
        <br>
        ";

    $html .= "<form>
        <input style=\"margin-top: 4px; display: inline-block\" type=\"text\" id=\"dtFilterDate\" data-provide=\"datepicker\" data-date-format=\"yyyy-mm-dd\" class=\"form-control input-sm\" placeholder=\"Specified Date\" value=\"$selected_date\">
    </form>
    <p style=\"margin-top: 4px\">
        <small class=\"pull-right\"><a style=\"cursor:pointer\" onclick=\"$fxName.enable();gm.sync();\">Enable</a>/<a style=\"cursor:pointer\" onclick=\"$fxName.disable();gm.sync();\">Disable</a></small>
    </p>";

    $html .= "<script>
        $fxName = new dateFilter($column);

        $(function() {
            $fxName.defineTable($object);
            $gmap_register
            $gmap_oc
        });
    </script>";

    $html .= "</div>";

    return $html;
}


function label_filter()
{
    $args = array('object'=>null,'title'=>null,'column'=>null,'function_name'=>null,
        'wrapper_class'=>null,'selector'=>"",'selected'=>null,'info_array'=>null,
        'map'=>false,'fieldname'=>null,'max_height'=>false);
    extract(merge_args(func_get_args(), $args));

    $page = $_SERVER['REQUEST_URI'];
    $fxName = "dt".$function_name;

    if (isset($_SESSION['user']['filter_state'][$page][$fxName])) {
        $selected = $_SESSION['user']['filter_state'][$page][$fxName];
    }

    $info = "[";
    $active = "[";

    $ref = "span[ref=\"".$selector."_";

    if (empty($selected)) {
        $all = true;
    }

    if ($map) {
        $gmap_register = "gm.registerFn($fxName, '$fieldname');";
        $gmap_oc = "gm.sync();";
    }

    //$reset = "<a>/</a><a style=\"cursor:pointer\" onclick=\"$fxName.reset();$gmap_oc\">Reset</a>";
    $reset = "";

    if ($max_height) {
        $mh_style = "style=\"max-height: 256px; overflow-y: scroll\"";
        $mh_title = "<span style=\"color: #999; font-size: 11px;\">Scroll</span>";
    }

    $html = "<div class=\"dt_filter_section $wrapper_class\" $mh_style>
    <strong>$title</strong> $mh_title<small class=\"pull-right\"><a style=\"cursor:pointer\" onclick=\"$fxName.all();$gmap_oc\">All/None</a>$reset</small>
    <br>
    ";

    // Gets the last row of the array.
    $test = end($info_array);

    $ilen = count($info_array);
    $i = 0;

    if (is_array($test) && isset($test['title'])) {
        // This is the full detail array. Includes (title & class);
        foreach ($info_array as $key => $value) {
            $id = $key;
            $reference = $selector."_".$key;
            
            if (++$i === $ilen) {
                $icomma = "";
            } else {
                $icomma = ",";
            }

            if (in_array($id, $selected) || $all == true) {
                if (isset($value['class'])) {
                    $class = "label-".$value['class'];
                } else {
                    $class = "label-inverse";
                }
                //echo code($fxName. " - " .$value['title']. ": ".$new_class);
                $active .= $id.", ";
            } else {
                $class = "label-disabled";
            }
            
            $new_class = $class;
    
            $info .= "{id: ".$id.",title: \"".$value['title']."\",spanClass:\"label-".$value['class']."\"}$icomma";
    
            $html .= "<span ref=\"$reference\" onclick=\"$fxName.toggle(".$id.");$gmap_oc\" style=\"cursor: pointer; max-width: 232px;\" class=\"label label-wrap ".$class."\">".$value['title']."</span>";
        }
    } else {
        // Assumed a simple array, just values which will be re-used for title.
        foreach ($info_array as $key => $value) {
            if (++$i === $ilen) {
                $icomma = "";
            } else {
                $icomma = ",";
            }

            $reference = $selector."_".$key;

            if (in_array($key, $selected) || $all == true) {
                $class = "label-inverse";
                $active .= $key.", ";
            } else {
                $class = "label-disabled";
            }

            $info .= "{id: ".$key.",title: \"".$value."\",spanClass: \"label-inverse\"}$icomma";

            $html .= "<span ref=\"$reference\" onclick=\"$fxName.toggle(".$key.");$gmap_oc\" style=\"cursor: pointer; max-width: 232px;\" class=\"label label-wrap ".$class."\">".$value."</span>";
        }
    }

    $info .= "]";
    $active .= "]";

    $active = str_replace(array(',]',', ]'), ']', $active);
    
    $html .= "<script>
        var info = $info;
        var active = $active;
        var table = [];

        var $fxName = new dtFilter($active, $active, $info, $column, '.$wrapper_class', '$ref', '$fxName');    

        $(function() {
            $fxName.defineTable($object);
            //$fxName.getState();
            $gmap_register
            $gmap_oc
        });

    </script>";

    $html .= "</div>";

    return $html;
}

function get_page_title($page)
{

}
