<?php
function mkForm()
{
    $args=array("controls"=>null,"submit"=>"conditions","label"=>"Submit","suppress_submit"=>false,
            "action"=>null, "form_class"=>"newform","theme"=>"mid","include_help"=>true,
            "onclick"=>"","id"=>"form","button_class"=>"btn btn-small btn-primary",'title'=>'',
            'details'=>'','cancel'=>false,'fieldset_id'=>"fs".uniqid(),'suppress_legend'=>false,
            'interface'=>true,'target'=>null);
    extract(merge_args(func_get_args(),$args));

    $userid=$_SESSION['userid'];
    $bad = array(34);
    if (!in_array($userid, $bad)) {
        $disabled='disabled';
    } else {
        $disabled='';
    }
    if (!$action) {
        $action=$_SERVER['SCRIPT_NAME'];
    }
    //$submit_label=$submit;

    // Form Definitions
    //

    if (!$form_style) {
        $form_style = "";
    }

    switch ($theme) {
        case 'thin':
            $wrapper_class = "col-sm-12";
            $form_class = "";
            $form_dims = array('outer'=>'','inner'=>'','label'=>'','input'=>'','cols'=>false);
            break;
        case 'mid':
            $wrapper_class = "col-sm-12";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-8','label'=>'col-sm-4 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'wide':
            $wrapper_class = "col-sm-12";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'column':
            $wrapper_class = "col-sm-12";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>true);
            break;
        case 'collapse':
            $wrapper_class = "col-sm-12";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'modal':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group-min','inner'=>'','label'=>'control-label','input'=>'form-control','cols'=>false);
            break;
    }

    // Construct Submit Button
    //

    if ($cancel) {
        if ($interface) {
            $interface_trigger = 'true';
        } else {
            $interface_trigger = 'false';
        }
        $cancel_btn = "<button class=\"btn btn-small btn-danger\" onclick=\"cancel_form($interface_trigger); cancel_modal(); return false;\">Cancel</button>";
    } else {
        $cancel_btn = "";
    }

    $submit_inner = "class=\"".$form_dims['inner']."\"";
    $submit_outer = "class=\"".$form_dims['outer']."\"";

    if (empty($onclick)) {
        //if ($theme == 'modal') {
        //    $submit = "<div class=\"modal-footer\">\n
        //                <a href=\"#\" data-dismiss=\"modal\" class=\"btn\">Close</a>\n
        //                <input class=\"$button_class\" type=\"submit\" value=\"$label\" name=\"$submit\" onclick=\"$onclick\"/>\n
        //                $cancel_btn
        //                </div>";
        //} else {
            $submit = "<div $submit_outer>\n
                        <div $submit_inner>\n
                        <input class=\"$button_class\" type=\"submit\" value=\"$label\" name=\"$submit\" onclick=\"$onclick\"/>\n
                        $cancel_btn
                        </div>\n
                        </div>\n";
        //}
    } else {
        //if ($theme == 'modal') {
        //    $submit = "<div class=\"modal-footer\">\n
        //                <a href=\"#\" data-dismiss=\"modal\" class=\"btn\">Close</a>\n
        //                <button class=\"$button_class\"  onclick=\"$onclick\" type=\"button\">$label</button>\n
        //                $cancel_btn
        //                </div>";
        //} else {
            $submit = "<div $submit_outer>\n
                        <div $submit_inner>\n
                        <button class=\"$button_class\"  onclick=\"$onclick\" type=\"button\">$label</button>\n
                        $cancel_btn
                        </div>\n
                        </div>\n";
        //}
    }
    if ($suppress_submit) {
        $submit = "";
    }

    // Begin Form Construction
    //
    if ($suppress_legend) {
        $form_title = "";
    } else {
        $form_title = "<legend>$title</legend>";
    }

    //echo array_search("help",$controls);
    if ($theme=="horizontal") {
      $ctl_header="";
      $ctl_footer="";
    } else {
      $ctl_header="<div>";
      $ctl_footer="</div>";
    }

    //if (!in_array_r("help",$controls) & $include_help) {
    //  //$page = $_SERVER['REQUEST_URI'] . ":$submit_label";
    //  //$html.="<div class=\"basic\">" . GetControlByType('help',array('type'=>'help','page'=>$page)) . "</div>";
    //}

    $html_inner = "";
    $outer_row = "";

    foreach ($controls as $ctl=>$vlu) {
        if ($vlu['group']) {
            $group = true;

            // Wraps columns in spanned div
            if ($form_dims['cols'] === true) {
                $col_wrap_head = "<div class=\"span6\">\n";
                $col_wrap_foot = "</div>\n";
                $outer_row = " class=\"row-fluid\" ";
            }

            $div_id = uniqid("col");
            $legend = $vlu['label'];

            if ($theme == 'collapse') {
                $collapse_trigger = "<a class=\"trigger-collapse pull-right inline\" data-toggle=\"collapse\" data-target=\"#$div_id\"><i class=\"icon-chevron-down\" style=\"vertical-align: middle\" ></i></a>";
            } else {
                $collapse_trigger = "";
            }

            if ($vlu['collapsed'] === true) {
                $group_attr = " style=\"overflow: hidden; height:0;\"";
            } else {
                $group_attr = " class=\"in\" style=\"overflow: hidden;\"";
            }

            $html_inner.="$col_wrap_head
                    <fieldset>\n";

            if ($suppress_legend == false) {
                $html_inner.="<legend>$legend $collapse_trigger</legend>\n";
            }

            $html_inner.="<div id=\"$div_id\" $group_attr >";

            foreach ($vlu as $ctl2=>$vlu2) {
                if ($vlu['type']=="") {
                    //$html.=$ctl_header;
                    foreach ($vlu2 as $ctl3=>$vlu3) {
                        $vlu3['form_dims'] = $form_dims;
                        $html_inner.=  GetCtl2($ctl3,$vlu3);
                    }
                    //$html.=$ctl_footer;
                } else {
                    $vlu2['form_dims'] = $form_dims;
                    $html_inner.= $ctl_header . GetCtl2($ctl2,$vlu2) . $ctl_footer;
                }
            }
            $html_inner.="
                    </div>\n
                    </fieldset>\n
                    $col_wrap_foot";
        } else {

            if ($vlu['type']=="") {
                $html_inner.=$ctl_header;
                foreach ($vlu as $ctl2=>$vlu2) {
                    $vlu2['form_dims'] = $form_dims;
                    $html.=  GetCtl2($ctl2,$vlu2);
                }
                $html_inner.=$ctl_footer;
            } else {
                $vlu['form_dims'] = $form_dims;
                $html_inner.= $ctl_header . GetCtl2($ctl,$vlu) . $ctl_footer;
            }
        }
    }

    $collapse_div_id = uniqid("form");

    if ($group === true) {
        $form_title = "<h3 style=\"font-weight: 400\">$title</h3>\n";
        $fieldset_head = "";
        $fieldset_foot = "";
    } else {
        $fieldset_head = "<fieldset id=\"$fieldset_id\">";
        $fieldset_foot = "</fieldset>";
        if ($theme == "collapse") {
            $form_title = "<legend>$title <a class=\"trigger-collapse pull-right inline\" data-toggle=\"collapse\" data-target=\"#$collapse_div_id\"><i class=\"icon-chevron-down\" style=\"vertical-align: middle\" ></i></a></legend>";
            $attr = " class=\"in\" style=\"overflow: hidden;\"";
        } else {
            if ($suppress_legend == false) {
                $form_title = "<legend>$title</legend>\n";
            }
            $attr = "";
        }
    }

    if ($theme == 'modal') {
        $form_style = "style=\"margin: 0px;\"";
    }

    if (!is_null($target)) {
        $target_spec = " target=\"{$target}\"";
    }

    $html = "<div class=\"$wrapper_class\">\n
                    <form class=\"$form_class\" enctype=\"multipart/form-data\" action=\"$action\" $form_style method=\"post\" id=\"$id\" $target_spec>\n
                    $fieldset_head
                    $form_title
                    $details
                    <div $outer_row id=\"$collapse_div_id\" $attr>";

    $html .= $html_inner;

    $html .= "</div>\n
              $fieldset_foot
              $submit\n
            </form>\n
            </div>";

    return($html);
}

function mkFieldset()
{
    $args=array("controls"=>null,"submit"=>"conditions","label"=>"Submit","form_class"=>"newform","theme"=>"mid","include_help"=>true,
            "onclick"=>"","id"=>"fs".uniqid(),"button_class"=>"btn btn-small btn-primary",'title'=>'','details'=>'','cancel'=>false,
            'append'=>null,'prepend'=>null,'suppress_legend'=>false);
    extract(merge_args(func_get_args(),$args));

    $userid=$_SESSION['userid'];
    $bad = array(34);
    if (!in_array($userid, $bad)) {
        $disabled='disabled';
    } else {
        $disabled='';
    }
    if (!$action) {
        $action=$_SERVER['SCRIPT_NAME'];
    }
    //$submit_label=$submit;

    // Form Definitions
    //

    if (!$form_style) {
        $form_style = "";
    }

    switch ($theme) {
        case 'thin':
            $wrapper_class = "";
            $form_class = "";
            $form_dims = array('outer'=>'','inner'=>'','label'=>'','input'=>'','cols'=>false);
            break;
        case 'mid':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-8','label'=>'col-sm-4 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'wide':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'column':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>true);
            break;
        case 'collapse':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group','inner'=>'col-sm-10','label'=>'col-sm-2 control-label','input'=>'form-control','cols'=>false);
            break;
        case 'modal':
            $wrapper_class = "";
            $form_class = "form-horizontal";
            $form_dims = array('outer'=>'form-group-min','inner'=>'col-sm-10','label'=>'control-label','input'=>'form-control','cols'=>false);
            break;
    }

    // Begin Fieldset Construction
    //

    if ($suppress_legend == true) {
        $fieldset_title = "";
    } else {
        $fieldset_title = "<legend>$title</legend>";
    }

    //echo array_search("help",$controls);
    if ($theme=="horizontal") {
      $ctl_header="";
      $ctl_footer="";
    } else {
      $ctl_header="<div>";
      $ctl_footer="</div>";
    }

    //if (!in_array_r("help",$controls) & $include_help) {
    //  //$page = $_SERVER['REQUEST_URI'] . ":$submit_label";
    //  //$html.="<div class=\"basic\">" . GetControlByType('help',array('type'=>'help','page'=>$page)) . "</div>";
    //}

    $html_inner = "";
    $outer_row = "";

    foreach ($controls as $ctl=>$vlu) {
        if ($vlu['group']) {
            $group = true;

            // Wraps columns in spanned div
            if ($form_dims['cols'] === true) {
                $col_wrap_head = "<div class=\"span6\">\n";
                $col_wrap_foot = "</div>\n";
                $outer_row = " class=\"row-fluid\" ";
            }

            $div_id = uniqid("col");
            $legend = $vlu['label'];

            if ($theme == 'collapse') {
                $collapse_trigger = "<a class=\"trigger-collapse pull-right inline\" data-toggle=\"collapse\" data-target=\"#$div_id\"><i class=\"icon-chevron-down\" style=\"vertical-align: middle\" ></i></a>";
            } else {
                $collapse_trigger = "";
            }

            if ($vlu['collapsed'] === true) {
                $group_attr = " style=\"overflow: hidden; height:0;\"";
            } else {
                $group_attr = " class=\"in\" style=\"overflow: hidden;\"";
            }

            $html_inner.="$col_wrap_head\n
                    <legend>$legend $collapse_trigger</legend>\n
                    <div id=\"$div_id\" $group_attr >";
            foreach ($vlu as $ctl2=>$vlu2) {
                if ($vlu['type']=="") {
                    //$html.=$ctl_header;
                    foreach ($vlu2 as $ctl3=>$vlu3) {
                        $vlu3['form_dims'] = $form_dims;
                        $html_inner.=  GetCtl2($ctl3,$vlu3);
                    }
                    //$html.=$ctl_footer;
                } else {
                    $vlu2['form_dims'] = $form_dims;
                    $html_inner.= $ctl_header . GetCtl2($ctl2,$vlu2) . $ctl_footer;
                }
            }
            $html_inner.="
                    </div>\n
                    $col_wrap_foot";
        } else {

            if ($vlu['type']=="") {
                $html_inner.=$ctl_header;
                foreach ($vlu as $ctl2=>$vlu2) {
                    $vlu2['form_dims'] = $form_dims;
                    $html.=  GetCtl2($ctl2,$vlu2);
                }
                $html_inner.=$ctl_footer;
            } else {
                $vlu['form_dims'] = $form_dims;
                $html_inner.= $ctl_header . GetCtl2($ctl,$vlu) . $ctl_footer;
            }
        }
    }

    $collapse_div_id = uniqid("form");

    if ($group === true) {
        $form_title = "<h3 style=\"font-weight: 400\">$title</h3>\n";
    } else {
        if ($theme == "collapse") {
            $form_title = "<legend>$title <a class=\"trigger-collapse pull-right inline\" data-toggle=\"collapse\" data-target=\"#$collapse_div_id\"><i class=\"icon-chevron-down\" style=\"vertical-align: middle\" ></i></a></legend>";
            $attr = " class=\"in\" style=\"overflow: hidden;\"";
        } else {
            $form_title = "<legend>$title</legend>\n";
            $attr = "";
        }
    }

    if ($theme == 'modal') {
        $form_style = "style=\"margin: 0px;\"";
    }

    $html = "<fieldset id=\"$id\">
                    $prepend
                    $fieldset_title
                    $details
                    <div $outer_row id=\"$collapse_div_id\" $attr >";

    $html .= $html_inner;

    $html .= "</div>\n
            $append
            </fieldset>";

    return($html);
}

function GetCtl2($name,$ctl)
{
  //debug($ctl);

  $type=$ctl['type'] ?: 'default';

  //$fkey=$ctl['fkey'] ?: $name;
  $value=$ctl['value'];
  //$name=$ctl['name'];
  $label = label_maybe_empty_string($ctl['label'], $name);
  $table=$ctl['table'];
  $display=$ctl['display'] ?: $name;
  $sql=$ctl['sql'];
  $allownull=$ctl['allownull'];
  $include_all=$ctl['include_all'];
  $include_new = $ctl['include_new'];// ?: true;
  $onchange=$ctl['onchange'];
  $id=$ctl['id'] ?: $name;
  $dir=$ctl['dir'];
  $page=$ctl['page'];
  $fcol=$ctl['fcol'] ?: $name;
  $init=$ctl['init'] ?: '';
  $size=$ctl['size'];
  $schema=$ctl['schema'];
  $multiselect=$ctl['multiselect'];
  //$read_only = $ctl['read_only'] ?: false;

switch ($type) {
case "boolean":
  $ctl['col']=$name;
  //$sql="select unnest(ARRAY['t','f']) as value,unnest(ARRAY['true','false']) as display;";
  $array = array('0'=>'True','-1'=>'False');
  $ctl['array']=$array;
  $html=mkComboboxHTML($ctl);
  break;
case 'default':
  //debug('here');
  $html=GetControl($name, $value, false, $schema, $ctl);
  break;
//case "hidden":
//  $ctl['col']=$name;
//  $html=mkHiddenLookupHTML($ctl);
//  break;
case "list":
  //debug($sql);
  $html=listHTML($sql);
  break;
case "hidden":
    $ctl['col']=$name;
    if(empty($fcol))$fcol=$value;
    $sql="SELECT '$value' as display, '$fcol' as value;";
    $ctl['fcol']='value';
    $ctl['display']='display';
    $ctl['sql']=$sql;
    $ctl['readonly']=true;
    $html=mkComboboxHTML($ctl);
    $html.=mkHiddenHTML($name,$value);
    break;
case "hidden2":
  $html=mkHiddenHTML($name,$value);
  break;
case "clean":
  $html=cleanHTML($value);
  break;
case "date":
  $ctl['col']=$name;
  $html=mkDateHTML($ctl);
  break;
case "boundary":
  $ctl['col']=$name;
  $html=mkBoundaryHTML($ctl);
  break;
case "marker":
  $ctl['col']=$name;
  $html=mkMarkerHTML($ctl);
  break;
case "wind":
  $ctl['col']=$name;
  $html=mkWindHTML($ctl);
  break;
case "related":
  $ctl['col']=$name;
  $html=mkRelatedHTML($ctl);
  break;
case "checklist":
  $html=checklistHTML($name,$value,$sql);
  break;
case "checkbox":
  $ctl['col']=$name;
  $html=mkCheckboxHTML($ctl);
  break;
case "dropdown":
  $ctl['col']=$name;
  $html=mkDropdown($ctl);
  break;
case "datetime":
  $ctl['col']=$name;
  $html=mkDatetimeHTML($ctl);
  break;
case "combobox_old":
  $html=comboboxHTML($name,$value,$table,$display,$sql,$fcol,$allownull,$onchange,$id,$label,$include_all,$size);
  break;
case "combobox":
  $ctl['col']=$name;
  $html=mkComboboxHTML($ctl);
  break;
case "memo":
  $ctl['col']=$name;
  $html=mkTextareaHTML($ctl);
  break;
case "rich_memo":
  //$rich_text_array = array('col'=>$name,'value'=>$value,'label'=>$label,'with_label'=>true,'form_dims'=>$form_dims);
  $ctl['col']=$name;
  $html=mkRichtextHTML($ctl);
  break;
case "password":
  $ctl['col']=$name;
  $html=mkPasswordHTML($ctl);
  break;
case "slider":
  // add the col to the array and just pass the whole array
  $ctl['col']=$name;
  $html=mkSliderHTML($ctl);
  break;
case "interval":
  // add the col to the array and just pass the whole array
  $ctl['col']=$name;
  $html=mkIntervalHTML($ctl);
  break;
case "meta":
  $html = "<input type=\"hidden\" name=\"coldefs\" value=\"$value\">\n";
  break;
case "file":
  $html = mkFileHTML($ctl);
  break;
case "directory":
  $ctl['col']=$name;
  $html = mkDirectoryHTML($ctl);
  break;
case "html":
    $html = $value;
    break;
case "info":
    $html = infoHTML($value);
    break;
case "help":
    $html = helpHTML($page);
    break;
case "submit":
  $ctl['col']=$name;
  $html=mkSubmitHTML($ctl);
  break;
default:
    $ctl['col']=$name;
    $html = mkTextboxHTML($ctl);
}

  return($html);
}

function formDims($form_dims=null, $class=null, $label_class=null)
{
    if (isset($form_dims)) {
        $outer_class = "class=\"".$form_dims['outer']."\"";
        $inner_class = "class=\"".$form_dims['inner']."\"";
        $label_class_n = "class=\"".$form_dims['label']."\"";
        $input_class = "class=\"".$form_dims['input']."\"";
    } else {
        $outer_class = "";
        $inner_class = "";
        $label_class = "";
        $input_class = "";
    }

    if (!empty($class)) {
      $input_class = "class=\"".$class."\"";
    }

    if (!empty($label_class)) {
      $label_class_n = "class=\"".$label_class."\"";
    }

    $array = array('outer_class'=>$outer_class,
                    'inner_class'=>$inner_class,
                    'label_class'=>$label_class_n,
                    'input_class'=>$input_class);

    return $array;
}

function mkTextboxHTML()
{
  $args=array('col'=>null,'value'=>null,'label'=>null,'with_label'=>true,
          'name'=>null,'placeholder'=>null, 'class'=>'','form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));

  extract(formDims($form_dims,$class,$label_class));

  $html = "<div $outer_class>\n";

  if (is_null($field_id) && $enable_help) {
    $field_id = getInputFieldId($col, $table_id);
  }

  $help = getInputHelp($enable_help, $field_id);

  if (!empty($label)) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  } elseif (empty($placeholder)) {
    $placeholder = $col;
  }

  if (!empty($placeholder)) {
    $placeholder = "placeholder=\"$placeholder\"";
  }

 $name=$name ?: "my[$col]";

  $html .= "<div $inner_class>\n
            <input $input_class id=\"$col\" name=\"$name\" type=\"text\" $placeholder value=\"$value\" title=\"Your title here\"/>\n";

  $html .= "</div>\n
            </div>\n";

  return($html);
}

function mkComboboxHTML()
{
    $defaults = array('col'=>null,'value'=>null,'table'=>null,'display'=>null,'sql'=>null,
        'readonly'=>false, 'include_new'=>false,'fcol'=>null,'allownull'=>false,'onchange'=>null,
        'id'=>null,'label'=>null,'include_all'=>false,'size'=>null,'multiselect'=>false,'class'=>'',
        'form_dims'=>null,'array'=>null,'enable_help'=>false,'field_id'=>null,'table_id'=>null,'order'=>null);

    // havent implemented read-only yet, not sure if I want to do it here or somewhere else
    // in otherwords, if readly only I think I should use the lookup value and create span
    extract(merge_args(func_get_args(), $defaults));

    extract(formDims($form_dims, $class, $label_class));

    $html = "<div $outer_class>\n";

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

    // temporary fix
    $lookuptable = $table;
    $query = $sql;

    if (is_array($display)) {
        $i = 1;
        $displaycolumn = "CONCAT(";
        foreach ($display as $display_value) {
            if ($i != sizeof($display)) {
                $displaycolumn .= $display_value.", ' - ',";
            } else {
                $displaycolumn .= $display_value;
            }
            $i++;
        }
        $displaycolumn .= ") as display";
        $order_by = 'display';
        $dcol = 'display';
    } else {
        $displaycolumn = $display;
        $order_by = $display;
        $dcol = $display;
    }

    if (!is_null($order)) {
        $order_by = $order;
    }

    $with_label = !($label==='');
    $label = $label ?: $col;
    if (empty($fcol)) {
        $fcol=$col;
    }
    if ($with_label) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
    }
/// the hairy join
  $idcolumn = $fcol;
  // in order to make the code compatible for both multi and single select
  // we should convert non-array values to an array
  if (is_array($value)) {
    $selectedvalue = $value;
  } else {
    $selectedvalue = array('0'=>$value);
  }
  $fieldname = "my[$col]";
  $include_null=$allownull;
  //$readonly=false;
  //$include_all=false;

  if (is_null($fieldname)) {
    $fieldname = $idcolumn;
  }
  if (is_null($query)) {
    $query = "SELECT $idcolumn, $displaycolumn FROM $lookuptable ORDER BY $order_by";
  } else {
    $query = str_replace(array("\r","\n")," ", $query);
  }

  if ($readonly) {
    $disabled="disabled=\"disabled\"";
  } else {
    $disabled="";
  }
  // do we want it to behave more like a dropdown list
  if (!empty($size)) {
    $size="size=\"$size\"";
  }
  //do we want to be able to select multiple fields (best used with size)
  if ($multiselect) {
    $multiple = 'multiple';
    $multibracket='[]';
  } else {
    $multiple = '';
    $multibracket='';
  }

  if (!is_null($id)) {
    $html .= "<div $inner_class>\n
                <select $input_class $multiple name=\"$fieldname$multibracket\" onchange=\"$onchange\" id=\"$id\" $disabled $size>\n";
  } else {
    $html .= "<div $inner_class>\n
                <select $input_class $multiple name=\"$fieldname$multibracket\" onchange=\"$onchange\" $disabled $size>\n";
  }

  // debug($include_all,false);

  if ($include_all) {
    $html .= "\t<option value=\"\">All</option>\n";
  }

  if ($include_null) {
    $html .= "\t<option value=\"null\">None</option>\n";
  }

  if (is_null($array)) {
    $result = fetch_assoc($query);
    if ($result['error'] != true) {
    foreach ($result as $key => $value) {
      $vlu = $value[strtolower($idcolumn)];
      $display = $value[strtolower($dcol)];
      if (in_array($vlu, $selectedvalue)) {
        $html .= "\t<option value=\"$vlu\" SELECTED>$display</option>\n";
      } else {
        $html .= "\t<option value=\"$vlu\" >$display</option>\n";
      }
    }
  }
  } else {
    $result = $array;
    foreach ($result as $key => $value) {
      $vlu = $key + 1;
      $display = $value;
      if (in_array($vlu, $selectedvalue)) {
        $html .= "\t<option value=\"$vlu\" SELECTED>$display</option>\n";
      } else {
        $html .= "\t<option value=\"$vlu\" >$display</option>\n";
      }
    }
  }

  $html .= "</select>\n";
  $html .= "</div>\n
            </div>\n";
  // add the new link, so long as we have table specified
  // even if we skip the table part and use an sql statement, if we want this
  // to work we need to supply table also
  //if ($include_new == true & !empty($lookuptable)) {
  //  // need to remove the order by from the query
  //  $idx = strpos(strtolower($query),"order by");
  //  if ($idx !== false) {
  //    $query = substr($query,0,$idx);
  //  }
  //  $html .= "<a class=\"clear_link\" onclick=\"new_dropdown('$lookuptable','$idcolumn','$displaycolumn', '$id', '$query'); return false\">new</a>";
  //};

  return($html);

}

function mkBoundaryHTML()
{
    $defaults=array('col'=>null,'value'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>null,'placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null,'zoom_to_fit'=>false);

    global $map_center;

    extract(merge_args(func_get_args(),$defaults));
    if (!empty($placeholder)) {
       $placeholder = "placeholder=\"$placeholder\"";
     }
    $id = $col;

    global $map_center;

    extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

     $html = "<div $outer_class>\n";

     if ($zoom_to_fit == true) {
        $zoom = "map.fitBounds(bounds)";
     } else {
        $zoom = "";
     }

     if (!empty($label)) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
     } elseif (empty($placeholder)) {
        $placeholder = $col;
     }

    // convert the value to json
    // remove '(),
    if (!empty($value)) {
        // update value

        $center = "zoom: 6,
        center: new google.maps.LatLng($map_center),";
        $latlng = explode(' ',str_replace(array("(",")",","),"",$value));
        $json_str = "{ne:{lat:".$latlng[2].",lon:".$latlng[3]."},sw:{lat:".$latlng[0].",lon:".$latlng[1]."}}";

        $bounds = "
            // extend it using my two points
            var latlng = $json_str

            var bounds = new google.maps.LatLngBounds(
              new google.maps.LatLng(latlng.sw.lat,latlng.sw.lon),
              new google.maps.LatLng(latlng.ne.lat,latlng.ne.lon)
            );

            var rectangle = new google.maps.Rectangle({
              bounds: bounds,
              editable: true,
              draggable: true
            });

            rectangle.setMap(map);";

    } else {
        $center = "zoom: 6,
            center: new google.maps.LatLng($map_center),";
        $bounds = "";
    }

    //debug($json_str);
    $html .= "
        <style>
            #map$id {
                   $style
                   }

            .map-canvas {height:348px;}

            div.stations2 svg {
            position: absolute;
            }
        </style>
        <div $inner_class>
        <div class=\"mapFormWrap\">
        <div class=\"map-canvas\" id=\"map$id\"></div>
        </div>
        </div>
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

            $zoom

            google.maps.event.addListener(rectangle, 'bounds_changed', function() {
              $('#$col').val(rectangle.getBounds())
              console.log(rectangle.getBounds())
            })

        </script>
        <input id=\"$col\" name=\"my[$col]\" $placeholder type=\"hidden\" value=\"$value\"/>\n";

  return($html);
}

function mkMarkerHTML()
{
    $defaults=array('col'=>null,'value'=>null,'boundary'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>null,'placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null,'zoom_to_fit'=>false,'show_inputs'=>true,'input_type'=>null,
          'origin'=>null,'secondary_icon_url'=>null);

    global $map_center;

    extract(merge_args(func_get_args(), $defaults));
    if (!empty($placeholder)) {
        $placeholder = "placeholder=\"$placeholder\"";
    }
    $id = uniqid();

    extract(formDims($form_dims, $class, $label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

    $html = "<div $outer_class>\n";

    if (!empty($label)) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
    } elseif (empty($placeholder)) {
        $placeholder = $col;
    }

    if (!empty($boundary)) {
        if ($zoom_to_fit == true) {
            $zoom = "map_{$id}.fitBounds(bounds)";
        } else {
            $zoom = "";
        }
        // update value
        $center = "zoom: 6,
            center: new google.maps.LatLng($map_center),";
        $center_min = "new google.maps.LatLng($map_center)";
        $latlng = explode(' ', str_replace(array("(",")",","), "", $boundary));
        $json_str = "{ne:{lat:".$latlng[2].",lon:".$latlng[3]."},sw:{lat:".$latlng[0].",lon:".$latlng[1]."}}";

        $bounds = "
        // extend it using my two points
        var latlng = $json_str

        var bounds = new google.maps.LatLngBounds(
          new google.maps.LatLng(latlng.sw.lat,latlng.sw.lon),
          new google.maps.LatLng(latlng.ne.lat,latlng.ne.lon)
        );

        var rectangle_{$id} = new google.maps.Rectangle({
          strokeColor: '#000',
          strokeOpacity: 0.9,
          strokeWeight: 1,
          fillColor: '#000',
          fillOpacity: 0.1,
          bounds: bounds,
        });

        rectangle.setMap(map_{$id});";
    } else {
        $bounds = "";
    }

    if (!empty($origin) && $input_type == 'dist-deg')  {
        if ($zoom_to_fit == true) {
            //$zoom = "";
        } else {
            //$zoom = "";
        }

        $origin_latlng = explode(' ', str_replace(array("(",")",","), "", $origin));
        $origin_json_str = "{lat:".$origin_latlng[0].",lon:".$origin_latlng[1]."}";

        $pre_origin = "var origin_{$id}_latlng = $origin_json_str
            var origin_{$id}_center = new google.maps.LatLng(origin_{$id}_latlng .lat, origin_{$id}_latlng.lon);";

        $origin = "
            var originMarker_{$id} = new google.maps.Marker({
                position: origin_{$id}_center,
                draggable: false,
            });

            originMarker_{$id}.setMap(map_{$id})
        ";
    }

    if (!empty($secondary_icon_url)) {
        $icon = "var secondaryIcon = {
                    url: '$secondary_icon_url'
                }";
        $icon_linker = "icon: secondaryIcon";
    } else {
        $icon_linker = "";
    }

    if (!empty($value)) {
        $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $value));
        $marker_json_str = "{lat:".$marker_latlng[0].",lon:".$marker_latlng[1]."}";

        $pre = "var marker_{$id}_latlng = $marker_json_str
            var marker_{$id}_center = new google.maps.LatLng(marker_{$id}_latlng.lat, marker_{$id}_latlng.lon);";

        $marker = "
            $icon

            var marker_{$id} = new google.maps.Marker({
                position: marker_{$id}_center,
                draggable: true,
                $icon_linker
            });

            marker_{$id}.setMap(map_{$id})
        ";
    } else {
        $marker = "
            $icon

            var marker_{$id}_center = new google.maps.LatLng($map_center);

            var marker_{$id} = new google.maps.Marker({
                position: marker_{$id}_center,
                draggable: true,
                $icon_linker
            });

            marker_{$id}.setMap(map_$id)
        ";
    }

    if (!empty($origin)) {
        $center = "zoom: 6,
            center: origin_{$id}_center,";
        $center_min = "origin_{$id}_center";
    } else if (!empty($value)) {
        $center = "zoom: 11,
            center: marker_{$id}_center,";
        $center_min = "marker_{$id}_center";
    } else {
        $center = "zoom: 6,
            center: new google.maps.LatLng($map_center),";
        $center_min = "new google.maps.LatLng($map_center)";
    }

    if ($inner_class == "class=\"\"") {
        // For modal map centering.
        $resize_call = "
            $(function () {
                console.log(\"map_{$id} idle pre-trigger\")

                google.maps.event.addListenerOnce(map_{$id}, 'idle', function(){
                    console.log(\"map_{$id} is idle\");
                    google.maps.event.trigger(map_{$id}, 'resize');
                    map_{$id}.setCenter($center_min);
                });
            });";
    }

     //debug($json_str);
      $html .= "
        <style>
            #map$id {
                $style
            }

            .map-canvas {height:348px;}

            div.stations2 svg {
                position: absolute;
            }
        </style>
        <div $inner_class>
        <div class=\"mapFormWrap\">
        <div class=\"map-canvas\" id=\"map$id\"></div>
        </div>
        </div>
        </div>
        <script>
            $pre

            $pre_origin

            var map_{$id} = new google.maps.Map(document.getElementById('map$id'), {
                $center
                mapTypeId: google.maps.MapTypeId.TERRAIN,
                panControl: false,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                scrollwheel: false
            });

            $bounds

            $zoom

            $marker

            $origin

            var OverlayTwo = new OverlayI();
            OverlayTwo.setControls(map_{$id});

            var positionInit = marker_{$id}.getPosition()
            $('#{$col}').val(positionInit)
            $('#{$col}_lat').val(Math.round10(positionInit.lat(), -3))
            $('#{$col}_lon').val(Math.round10(positionInit.lng(), -3))

            google.maps.event.addListener(marker_{$id}, 'position_changed', function() {
                var position = marker_{$id}.getPosition()
                $('#{$col}').val(position)
                $('#{$col}_lat').val(Math.round10(position.lat(), -3))
                $('#{$col}_lon').val(Math.round10(position.lng(), -3))

                if (typeof originMarker_{$id} != 'undefined') {
                    var deg = Math.round10(google.maps.geometry.spherical.computeHeading(originMarker_{$id}.getPosition(), marker_{$id}.getPosition()), -3);
                    if (deg < 0) {
                        deg = (360-Math.abs(deg));
                    }
                    $('#{$col}_deg').val(deg);
                    $('#{$col}_dist').val(Math.round10((google.maps.geometry.spherical.computeDistanceBetween(originMarker_{$id}.getPosition(), marker_{$id}.getPosition())/1609.34), -3));
                }
            })

            $resize_call
        </script>
        ";

  if ($show_inputs) {
        if ($inner_class == "class=\"\"") {
            $intermediate_class = "col-sm-12";
        } else {
            $intermediate_class = "col-sm-offset-4 col-sm-8";
        }

        if ($input_type == 'dist-deg') {
            /**
             *   Required origin marker (calculates distance and bearing from the origin).
             */

            $html .= "<div class=\"$intermediate_class\" style=\"\">
                <div class=\"inline\">
                    <div class=\"form-group\" style=\"width: 50%; display: inline-block; margin-left: -5px;\">
                        <label>Distance from Origin (Mi)</label>
                        <input class=\"form-control\" name=\"{$col}_dist\" id=\"{$col}_dist\" placeholder=\"Distance\" type=\"text\" value=\"$distance\">
                    </div>
                    <div class=\"form-group\" style=\"width: 50%; display: inline-block; margin-left: 30px;\">
                        <label>Degrees from North (Deg)</label>
                        <input class=\"form-control\" name=\"{$col}_deg\" id=\"{$col}_deg\" placeholder=\"Degrees\" type=\"text\" value=\"$degrees\">
                    </div>
                </div>
            </div>
            <script>
                function {$col}_smapdb(origin) {
                    var distance = $('#{$col}_dist').val();
                    var degrees = $('#{$col}_deg').val();

                    var new_position = google.maps.geometry.spherical.computeOffset(originMarker_{$id}.getPosition(), (distance * 1609.34), degrees);
                    marker_{$id}.setPosition(new_position);
                }

                $('#{$col}_dist').change(function() {
                    {$col}_smapdb(originMarker_{$id});
                })

                $('#{$col}_deg').change(function() {
                    {$col}_smapdb(originMarker_{$id});
                })
            </script>";
        } else {
            /**
             *   Simple Lat, Long
             */

            $html .= "<div class=\"$intermediate_class\" style=\"\">
                <div class=\"inline\">
                    <div class=\"form-group\" style=\"width: 50%; display: inline-block; margin-left: -5px;\">
                        <label>Latitude</label>
                        <input class=\"form-control\" name=\"{$col}_lat\" id=\"{$col}_lat\" placeholder=\"Latitude\" type=\"text\" value=\"$lat\">
                    </div>
                    <div class=\"form-group\" style=\"width: 50%; display: inline-block; margin-left: 30px;\">
                        <label>Longitude</label>
                        <input class=\"form-control\" name=\"{$col}_lon\" id=\"{$col}_lon\" placeholder=\"Longitude\" type=\"text\" value=\"$lon\">
                    </div>
                </div>
            </div>
            <script>
                function {$col}_smap() {
                    var lat = $('#{$col}_lat').val();
                    var lon = $('#{$col}_lon').val();

                    var new_center = new google.maps.LatLng(lat, lon);
                    marker_{$id}.setPosition(new_center);
                }

                $('#{$col}_lat').change(function() {
                    {$col}_smap();
                })

                $('#{$col}_lon').change(function() {
                    {$col}_smap();
                })
            </script>";
        }
  }

  $html .= "<input id=\"$col\" name=\"my[$col]\" $placeholder type=\"hidden\" value=\"$value\"/>\n";

  return($html);
}

function mkWindHTML()
{
    $defaults=array('col'=>null,'location_value'=>null,'value'=>null,'boundary'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>null,'placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null,'zoom_to_fit'=>false,'show_inputs'=>true,'color'=>'#GGGGGG',
          'marker_js'=>null);

    global $map_center;

    extract(merge_args(func_get_args(), $defaults));
    if (!empty($placeholder)) {
        $placeholder = "placeholder=\"$placeholder\"";
    }
    $id = $col;

    extract(formDims($form_dims, $class, $label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

    $html = "<div $outer_class>\n";

    if (!is_null($marker_js)) {
        $marker_live_sync = 'markerLocation = ' . $marker_js .
            "markerLocation = markerLocation.replace('(','');
             markerLocation = markerLocation.replace(')','');
             mLocArray = markerLocation.split(',');
             markerLocation = {lat: parseFloat(mLocArray[0]),lng: parseFloat(mLocArray[1])};";

        $pre = "$marker_live_sync;";

        $center = "zoom: 11,
            center: new google.maps.LatLng(markerLocation.lat, markerLocation.lng),";

        $marker = "
            var marker_center_$id = new google.maps.LatLng(markerLocation.lat, markerLocation.lng);

            var marker_$id = new google.maps.Marker({
                position: marker_center_$id
            });

            marker_$id.setMap(map_$id)
        ";
    }

    if (!empty($label)) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
    } elseif (empty($placeholder)) {
        $placeholder = $col;
    }

    $valueArray = json_decode($value, true);
    if (is_array($valueArray)) {
      $iso_js = "iso_$id = new isosceles(map_$id, marker_$id, {$valueArray['initDeg']}, {$valueArray['finalDeg']}, {$valueArray['amplitude']}, '$color');";
    }

    if (!empty($location_value)) {
        $marker_latlng = explode(' ', str_replace(array("(",")",","), "", $location_value));
        $marker_json_str = "{lat:".$marker_latlng[0].",lon:".$marker_latlng[1]."}";

        $pre = "var marker_latlng_$id = $marker_json_str
                var marker_center_$id = new google.maps.LatLng(marker_latlng_$id.lat, marker_latlng_$id.lon);";

        $marker = "
            var marker_$id = new google.maps.Marker({
                position: marker_center_$id
            });

            marker_$id.setMap(map_$id)
        ";

        $center = "zoom: 15,
            center: marker_center,";
    } elseif (is_null($marker_js)) {
        $center = "zoom: 11,
            center: new google.maps.LatLng($map_center),";
        $marker = "
            var marker_center_$id = new google.maps.LatLng($map_center);

            var marker_$id = new google.maps.Marker({
                position: marker_center_$id
            });

            marker_$id.setMap(map_$id)
        ";
    }

    //debug($json_str);
    $html .= "
        <style>
            #map_$id {
                $style
            }

            .map-canvas {height:348px;}

            div.stations2 svg {
                position: absolute;
            }
        </style>
        <div $inner_class>
        <div class=\"mapFormWrap\">
        <div class=\"map-canvas\" id=\"map$id\"></div>
        </div>
        </div>
        </div>
        <script>
            $pre

            var map_$id = new google.maps.Map(document.getElementById('map$id'), {
                $center
                mapTypeId: google.maps.MapTypeId.TERRAIN,
                panControl: false,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                scrollwheel: false
            });

            google.maps.event.trigger(map_$id,'resize');

            $bounds

            $zoom

            $marker

            $iso_js

            //var positionInit = marker_$id.getPosition()
            //$('#$col').val(positionInit)
            //$('#{$col}_lat').val(Math.round10(positionInit.lat(), -3))
            //$('#{$col}_lon').val(Math.round10(positionInit.lng(), -3))

            google.maps.event.addListener(marker_$id, 'position_changed', function() {
                var position = marker_$id.getPosition()
                $('#$col').val(position)
                $('#{$col}_lat').val(Math.round10(position.lat(), -3))
                $('#{$col}_lon').val(Math.round10(position.lng(), -3))
            })

        </script>
        ";

    $html .= "<div class=\"col-sm-offset-4 col-sm-8\" style=\"\">
            <div class=\"inline\">
                <div class=\"form-group\" style=\"width: 32%; display: inline-block; margin-left: -5px;\">
                    <label class=\"\">Wind Speed (MPH)</label>
                    <input class=\"form-control\" id=\"{$col}_speed\" placeholder=\"Wind Speed (MPH)\" type=\"text\" value=\"{$valueArray['amplitude']}\">
                </div>
                <div class=\"form-group\" style=\"width: 32%; display: inline-block; margin-left: 30px;\">
                    <label class=\"\">Min Degree</label>
                    <input class=\"form-control\" id=\"{$col}_init_deg\" placeholder=\"Minimum Degrees\" type=\"text\" value=\"{$valueArray['initDeg']}\">
                </div>
                <div class=\"form-group\" style=\"width: 33%; display: inline-block; margin-left: 30px;\">
                    <label class=\"\">Max Degree</label>
                    <input class=\"form-control\" id=\"{$col}_final_deg\" placeholder=\"Maximum Degrees\" type=\"text\" value=\"{$valueArray['finalDeg']}\">
                </div>
            </div>
        </div>
        <script>
            function {$col}_smap(marker_$id, iso_$id) {
                var speed = parseFloat($('#{$col}_speed').val());
                var initDeg = parseFloat($('#{$col}_init_deg').val());
                var finalDeg = parseFloat($('#{$col}_final_deg').val());

                /** Correct Potentially Erroneous Values **/
                speed = ((isNaN(speed)) ? 0: speed);
                speed = ((speed <= 0) ? 0: speed);

                initDeg = ((isNaN(initDeg)) ? 0: initDeg);
                initDeg = ((initDeg > 360) ? 360: initDeg);
                initDeg = ((initDeg <= 0) ? 0: initDeg);

                finalDeg = ((isNaN(finalDeg)) ? 0: finalDeg);
                finalDeg = ((finalDeg > 360) ? 360: finalDeg);
                finalDeg = ((finalDeg <= 0) ? 0: finalDeg);

                JSONstr = JSON.stringify({initDeg: initDeg, finalDeg: finalDeg, amplitude: speed});
                $('#$col').val(JSONstr);

                $('#{$col}_speed').val(speed);
                $('#{$col}_init_deg').val(initDeg);
                $('#{$col}_final_deg').val(finalDeg);

                if (typeof iso_$id == 'object') {
                    var isoc = new isosceles();
                    new_path = isoc.generatePath(marker_$id, initDeg, finalDeg, speed);
                    iso_$id.setPath(new_path);
                } else {
                    iso_$id = new isosceles(map_$id, marker_$id, initDeg, finalDeg, speed, '$color');
                }

                /** Uncomment for user issue testing (e.g. buggy saves) **/
                //console.log(\"Daytime/Nighttime ISO: DEBUG:\");
                //if (typeof iso_$id.latLngs.j[0].j != 'undefined') {
                //    console.log(\"ISO has latlngs:\");
                //    console.log(iso_$id.latLngs.j[0].j);
                //} else {
                //    console.log(\"May not be type ISO.\");
                //}
                //console.log('Input Value:')
                //console.log($('#{$col}').val());

                return iso_$id
            }

            $('#{$col}_speed').change(function() {
                if (typeof iso_$id == 'object') {
                    console.log(\"Speed - Exists\");
                    {$col}_smap(marker_$id, iso_$id);
                } else {
                    console.log(\"Speed - Doesn't Exist\");
                    iso_$id = {$col}_smap(marker_$id);
                }
            })

            $('#{$col}_init_deg').change(function() {
                if (typeof iso_$id == 'object') {
                    console.log(\"InitDeg - Exists\");
                    {$col}_smap(marker_$id, iso_$id);
                } else {
                    console.log(\"InitDeg - Doesn't Exist\");
                    iso_$id = {$col}_smap(marker_$id);
                }
            })

            $('#{$col}_final_deg').change(function() {
                if (typeof iso_$id == 'object') {
                    console.log(\"FinalDeg - Exists\");
                    {$col}_smap(marker_$id, iso_$id);
                } else {
                    console.log(\"FinalDeg - Doesn't Exist\");
                    iso_$id = {$col}_smap(marker_$id);
                }
            })
        </script>";

  $html .= "<input id=\"$col\" name=\"my[$col]\" $placeholder type=\"hidden\" value='{$value}'/>\n";

  return($html);
}

function mkDateHTML()
{
  $defaults=array('col'=>null,'value'=>'YYYY-MM-DD','label'=>null,'name'=>null,
          'label_class'=>'','with_label'=>true,'class'=>'','placeholder'=>null,'id'=>uniqid(date),'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$defaults));

  extract(formDims($form_dims,$class,$label_class));

  if (is_null($field_id) && $enable_help) {
    $field_id = getInputFieldId($col, $table_id);
  }

  $help = getInputHelp($enable_help, $field_id);

  $html = "<div $outer_class>\n";

  if ($with_label) {
    $with_label = !($label==='');
  }
  // look for a placeholder, these look nicer than labels on some forms
  if (!empty($placeholder)) {
    $placeholder = "placeholder=\"$placeholder\"";
  }

  $label=$label ?: $col;
  $name=$name ?: "my[$col]";

  if ($with_label) {
    $html .= "<label $label_class for=\"$id\">$label $help</label>\n";
  } elseif (empty($placeholder)) {
    $placeholder = $col;
  }

  $custom_input_class = $form_dims['input'];

  $html .= "<div $inner_class>\n
            <input data-provide=\"datepicker\" data-date-format=\"yyyy-mm-dd\" type=\"text\" class=\"measure_date $custom_input_class\" name=\"$name\" $placeholder value=\"$value\" id=\"$id\">\n
            </div>\n
            </div>\n";

  return($html);
}

function mkDatetimeHTML()
{
  $defaults=array('col'=>null,'value'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>'','placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$defaults));

  extract(formDims($form_dims,$class,$label_class));

  if (is_null($field_id) && $enable_help) {
    $field_id = getInputFieldId($col, $table_id);
  }

  $help = getInputHelp($enable_help, $field_id);

  $input_class = "class=\"".$form_dims['input']." measure_datetime \"";

  $html = "<div $outer_class>\n";

  if (empty($value)) {
      $value = "YYYY-MM-DD HH:MM";
  }

  if ($with_label) {
    $with_label = !($label==='');
  }
  // look for a placeholder, these look nicer than labels on some forms
  if (!empty($placeholder)) {
    $placeholder = "placeholder=\"$placeholder\"";
  }
  $label=$label ?: $col;
  $name=$name ?: "my[$col]";
  if ($with_label) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  }
  $html .= "<div $inner_class>\n
            <input type=\"text\" $input_class $placeholder name=\"my[$col]\" value=\"$value\">
            </div>\n
            </div>\n";

  return($html);
}

function mkTextareaHTML()
{
/* Uses JQuery.ValidVal() to validate the input */
  $defaults=array('col'=>null,'value'=>null,'label'=>null,'label_class'=>null,
          'with_label'=>true,'class'=>'','rows'=>5,'placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$defaults));

  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);
  // look for a placeholder, these look nicer than labels on some forms
  if (!empty($placeholder)) {
    $placeholder = "placeholder=\"$placeholder\"";
  }

  $html = "<div $outer_class>\n";

  // check to see if we want a label
  if ($with_label) {
    $with_label = !($label==='');
  }
  $label=$label ?: $col;
  if ($with_label) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  }
  $html .= "<div $inner_class>\n
            <textarea name=\"my[$col]\" $placeholder $input_class rows=\"$rows\">".$value."</textarea>\n
            </div>\n
            </div>\n";

  return($html);
}

function mkPasswordHTML()
{
  $args=array('col'=>null,'value'=>null,'label'=>null,'with_label'=>true,
          'name'=>null,'placeholder'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));

  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);

 if (!empty($placeholder)) {
    $placeholder = "placeholder=\"$placeholder\"";
  }

  $html = "<div $outer_class>\n";

  if ($with_label) {
    $with_label = !($label==='');
  }
  $label=$label ?: $col;
  $name=$name ?: "my[$col]";
  if ($with_label) {
    $html .= "<label $label_class for=\"$name\">$label $help</label>\n";
  };
  $html .= "<div $inner_class>\n
            <input id=\"$col\" name=\"$name\"  $placeholder $input_class type=\"password\" value=\"$value\"/>\n
            </div>\n
            </div>\n";

  return($html);
}

function mkSliderHTML()
{
  $args=array('col'=>null,'id'=>null,'value'=>null,'label'=>null,'min'=>0,'max'=>100,
          'vertical'=>false,'tip'=>null,'fixed'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));

   extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

   $help = getInputHelp($enable_help, $field_id);

  $default = $min;
  if (!empty($fixed)) {
    if ($fixed=="max") {
      $lbl_extra = "&#8805 ";
      $default = $max;
    } else {
      $lbl_extra = "&#8804 ";
    }
    $fixed = "range: \"$fixed\",";
  }
  if ($vertical===true) {
    $orientation = "orientation: \"vertical\",";
  }
  if (!empty($tip)) {
    $tip = "title = \"$tip\"";
  }
if (empty($id)) {
  $id = $col . rand();
}
if (empty($value)) {
  $value = $default;
}

$id_sld = $id . "_slider";
//$value = array(20,30);
if (strpos($label, "@@")===false) {
  $label = $label . " @@";
}
    $html = "<div $outer_class>\n";

  $html .= "<script>
          $(function () {
             $( \"#$id_sld\" ).slider({
                       $orientation
                       $fixed
                       min: $min,
                       max: $max,
                       value: $value,
                       slide: function (event, ui) {
                         var lbl = \"$label\";
                         $(\"#$id\").val( ui.value );
                         $(\"#{$id}_lbl\").html( lbl.replace(\"@@\",\"$lbl_extra\" + ui.value) );
                        }
              });
          });
          </script>";

  //if (!empty($label) {
  $html .= "<label $label_class for=\"$col\" id=\"{$id}_lbl\">" . str_replace("@@",$lbl_extra . $value,$label) . "</label>\n";
    //}
  $html .= "<input type=\"hidden\" name=\"my[$col]\" id=\"$id\" value=\"$value\"/>";
  $html .= "<div $inner_class>\n
            <div $input_class id=\"$id_sld\" style=\"margin-top:8px;\" $tip></div>\n
            </div>\n
            </div>\n";

  return($html);
}

function mkIntervalHTML()
{
  $args=array('col'=>null,'id'=>null,'value'=>array('lower'=>null,'upper'=>null),'label'=>null,'min'=>0,'max'=>100,
         'vertical'=>false,'tip'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));

  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);

  if ($vertical===true) {
    $orientation = "orientation: \"vertical\",";
  }
  if (!empty($tip)) {
    $tip = "title = \"$tip\"";
  }
  if ($readonly===true) {
    $readonly = "readonly";
  }
if (empty($id)) {
  $id = $col . rand();
}
$id_sld = $id . "_slider";
//$value = array(20,30);

if (empty($value['lower'])) {
  $value['lower'] = $min;
}
if (empty($value['upper'])) {
  $value['upper'] = $max;
}

if (strpos($label, "@@")===false) {
  $label = $label . " @@";
}

$values = $value['lower'] . "," . $value['upper'];

    $html = "<div $outer_class>\n";

  $html .= "<script>
          $(function () {
             $( \"#$id_sld\" ).slider({
                       $orientation
                       range: true,
                       min: $min,
                       max: $max,
                       values: [$values],
                       slide: function (event, ui) {
                         var lbl = \"$label\";
                         $(\"#{$id}_lwr\").val( ui.values[0] );
                         $(\"#{$id}_upr\").val( ui.values[1] );
                         $(\"#{$id}_lbl\").html(lbl.replace(\"@@\",ui.values[0] + \" to \" + ui.values[1]));
                        }
              });
          });
          </script>";

    $v1 = $value['lower'];
    $v2 = $value['upper'];

  //if (!empty($label) {
    $html .= "<label $label_class for=\"$col\" id=\"{$id}_lbl\">" . str_replace("@@",$v1." to ".$v2,$label) . "</label>\n";
    //}

  $html .= "<input type=\"hidden\" name=\"my[$col][lower]\" id=\"{$id}_lwr\" value=\"$v1\" />";
  $html .= "<input type=\"hidden\" name=\"my[$col][upper]\" id=\"{$id}_upr\" value=\"$v2\"/>";
  $html .= "<div $inner_class>\n
            <div $input_class id=\"$id_sld\" style=\"margin-top:8px;\" $tip>
            </div>\n
            </div>\n
            </div>\n";

  return($html);
}

function mkCheckboxHTML()
{
  $defaults=array('col'=>null,'value'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>'','placeholder'=>null,'init'=>null,'form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
    extract(merge_args(func_get_args(),$defaults));

 extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

 $help = getInputHelp($enable_help, $field_id);

  $html = "<div $outer_class>\n";

  $checked="";
  if ($init == 't') {
    $checked = "checked=\"checked\"";
  }
  if (!empty($label)) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  }
  if (isset($value)) {
      $value = "value=\"$value\"";
  }

  // Note, value=\"$value\" is arrangement from beta

  $html .= "<div $inner_class>\n";
  $html .= "<input type=\"checkbox\" $input_class name=\"my[$col]\" $value $checked >\n
            </div>\n
            </div>\n";

  return($html);
}

function mkFileHTML()
{
  $defaults=array('col'=>null,'value'=>null,'label'=>null,'name'=>null,
          'label_class'=>null,'with_label'=>true,'class'=>'','form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null,'onchange'=>null);
  extract(merge_args(func_get_args(),$defaults));

  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);

  $html = "<div $outer_class>\n";

  if ($with_label) {
    $with_label = !($label==='');
  }
  $label=$label ?: $col;
  $name=$name ?: "my[$col]";
  if ($with_label) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  };
  $html .= "<div $inner_class>\n";
  $html .= "<input id=\"upload_file\" name=\"$name\" type=\"file\" value=\"$value\"/>\n
            <script>
                $onchange
            </script>
            </div>\n
            </div>\n";

  return($html);
}

function mkRichtextHTML()
{
    $args=array('col'=>null,'value'=>null,'label'=>null,'with_label'=>true,
          'name'=>null,'placeholder'=>null, 'class'=>'','form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
    extract(merge_args(func_get_args(), $args));

    extract(formDims($form_dims, $class, $label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

    $eid = uniqid("ed");

    if ($form_dims['input'] == "input-xlarge" or $form_dims['input'] == "") {
        //$btn_group_4 = "style=\"margin-left: 0px\"";
        $btn_group_4 = "";
    }

    if ($form_dims['input'] == "span10") {
        $editor_style = "style=\"nargin-left: 0px\"";
    }

    $script = "<script>
                    $(function() {

                    });
                </script>";

    $html = "<div $outer_class>\n";

    if (!empty($label)) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
    } elseif (empty($placeholder)) {
        $placeholder = $col;
    }

    $name=$name ?: "my[$col]";

    $custom_input_class = $form_dims['input'];

    if ($value) {
        $placeholder = $value;
    }

    $editor = "
            <textarea class=\"form-control editordiv\" name=\"my[$col]\" id=\"rta$col\" rows=\"10\" cols=\"80\">
                $value
            </textarea>
            <script>
                $(function() {
                    if ((tinyMCE != undefined) && (tinyMCE.activeEditor != undefined)) {
                        tinyMCE.activeEditor.remove();
                    }

                    tinymce.init({
                        menubar : false,
                        selector: \".editordiv\",
                        style_formats: [
                            {title: \"Headers\", items: [
                                {title: \"Header 3\", format: \"h3\"},
                                {title: \"Header 4\", format: \"h4\"},
                                {title: \"Header 5\", format: \"h5\"},
                                {title: \"Header 6\", format: \"h6\"},
                                {title: \"Horizontal Rule\", block: \"hr\"},
                            ]},
                            {title: \"Text\", items: [
                                {title: \"Bold\", icon: \"bold\", format: \"bold\"},
                                {title: \"Italic\", icon: \"italic\", format: \"italic\"},
                                {title: \"Underline\", icon: \"underline\", format: \"underline\"},
                                {title: \"Strikethrough\", icon: \"strikethrough\", format: \"strikethrough\"},
                                {title: \"Superscript\", icon: \"superscript\", format: \"superscript\"},
                                {title: \"Subscript\", icon: \"subscript\", format: \"subscript\"}
                            ]}
                        ]
                    });

                    if ((tinyMCE != undefined) && (tinyMCE.activeEditor != undefined)) {
                        tinyMCE.activeEditor.setContent($('.editordiv').text());
                    }
                });
            </script>";

    $html .= "$script\n";

    $html .= "<div $inner_class>\n";

    $html .= $toolbar;
    $html .= $editor;
    $html .= "</div>\n
                </div>\n";

    return $html;
}

function mkDirectoryHTML()
{
     $args = array('col','value','dir','label','allownull'=>true, 'with_label' => true,
        'pattern'=>null,'recursive'=>true,'include_all'=>false,'size'=>null,
        'multiselect'=>false,'id'=>null,'class'=>'',
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));
  $fieldname = "my[$col]";

  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);

  $html = "<div $outer_class>\n";

  //  debug("here");
  if (empty($id)) {
    $id = "dir_$col";
  }
  if ($with_label) {
    $with_label = !($label==='');
  }
  if ($with_label) {
    $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
  }
  // check for multiselect
  //do we want to be able to select multiple fields (best used with size)
   $html .= "<div $inner_class>\n";

  if ($multiselect) {
    $multiple = 'multiple';
    $multibracket='[]';
    if (!empty($size)) {
      $size="size=\"$size\"";
    } else {
      $size="";
    }
  } else {
    $multiple = '';
    $multibracket='';
    $size="";
  }
  // check the returned value
  if (is_array($value)) {
    $selectedvalue=$value;
  } else {
    $selectedvalue=array('0'=>$value);
  }
  $html .= "<select $multiple $size name=\"$fieldname$multibracket\" id=\"$id\" $input_class>\n";
  if ($allownull) {
    $html .= "\t<option value=\"$null_text\">$null_text</option>\n";
  }
  // get the files
  $files=directorytoarray($dir,$recursive,"/".$pattern."/i");

  foreach ($files as $file=>$fpath) {
    if (in_array($fpath,$selectedvalue)) {
      $html .= "\t<option value=\"$fpath\" SELECTED>$file</option>\n";
    } else {
      $html .= "\t<option value=\"$fpath\">$file</option>\n";
    }
  }
  $html.="</select>";
  $html.="</div>\n
            </div>\n";

  return($html);
}

function mkSubmitHTML()
{
  $args=array('value'=>null,'label'=>null,'class'=>'','form_dims'=>null,
          'enable_help'=>false,'field_id'=>null,'table_id'=>null);
  extract(merge_args(func_get_args(),$args));
  extract(formDims($form_dims,$class,$label_class));

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

  $help = getInputHelp($enable_help, $field_id);
  $input_class="class=\"btn ".$form_dims['input']."\"";
  $html = "<div $outer_class>\n
            <label $label_class for=\"$col\">$label $help</label>\n
            <div $inner_class>\n
            <input $input_class type=\"submit\" name=\"$col\" value=\"$value\">
            </div>\n
            </div>\n";

  return($html);
}

function label_maybe_empty_string($a_label,$alternate)
{
  if ($a_label === '') {
    $label = '';
  } else {
    $label= $a_label ?: $alternate;
  }

  return $label;
}

function mkHiddenHTML($col,$value)
{
  $html.= "<input id=\"$col\" type=\"hidden\" name=\"my[$col]\" value=\"$value\"/>\n";

  return($html);
}

function blah_decode($json){
  $arr = json_decode($json,true);
  $bad = array('my[',']');

  foreach($arr as $vlu){
    if($vlu['name']=='coldefs'){
      // if this is the table information pull it out
      $key = $vlu["value"];
      // decode and unserialize
      $obj=urldecode($key);
      $obj=unserialize($obj);
      $data['coldefs']=$obj;
    }else{
      // otherwise assume its data
      // and place it in an array
      // check if its another array
      //debug($vlu);
      $nme = preg_replace(array("/my\[/","/\]$/"),"",$vlu['name']);
      $keys = explode("][",$nme);
      // check the size of the array
      // only handles 2
      if(count($keys)===1){
  $data[$keys[0]]=$vlu['value'];
      }else{
  if(empty($keys[1])){
    $data[$keys[0]][]=$vlu['value'];
  }else{
    $data[$keys[0]][$keys[1]]=$vlu['value'];
  }
      }
    }
  }
  return($data);
}

function mkDropdown(){
  $defaults = array('col'=>NULL,'value'=>NULL,'table'=>NULL,'display'=>NULL,'sql'=>NULL,'items'=>Null,
            'read_only'=>false, 'include_new'=>true,'fcol'=>NULL,'onchange'=>NULL,'onclick'=>NULL,
            'id'=>NULL,'label'=>NULL,'include_all'=>FALSE,'group_class'=>'btn-group','class'=>'btn btn-default',
            "tip"=>'','tip_field'=>NULL,'dependent_value'=>NULL,"in"=>NULL,'direction'=>'');
  extract(merge_args(func_get_args(),$defaults));
    // temporary fix
  $lookuptable = $table;
  $displaycolumn = $display;
  $query = $sql;

  //  debug(func_get_args(),false);

  $with_label = !($label==='');
  $label = $label ?: $col;
  if(empty($fcol)){
    $fcol=$col;
  }
  if($with_label == true){
    $html = "<label for=\"$col\">$label</label>\n";
  }
  /// the hairy join
  $idcolumn = $fcol;
  // in order to make the code compatible for both multi and single select
  // we shoul convert non-array values to an array
  $selectedvalue=$value;
  $fieldname = "my[$col]";
  //$include_null=$allownull;
  $readonly=FALSE;
  //$include_all=FALSE;
  //debug($include_null,false);

  if (is_null($fieldname)){
    $fieldname = $idcolumn;
  }

  if(!empty($in)){
    $fieldname = "my[$in][$col]";
  }else{
    $fieldname = "my[$col]";
  }

  if (!is_null($items)){
    // use the item array to build the statement, at this point we dont really need postgres
    // but I just want to make this easier to keep updated
    $displaycolumn = "lbl";
    $query = "SELECT unnest(ARRAY[$$".implode('$$,$$',array_keys($items))."$$]) as $idcolumn,
unnest(ARRAY[$$".implode('$$,$$',$items)."$$]) as lbl";
    //debug($query);
  }else if(is_null($query)){
    if(!empty($tip_field)){
      $tip_column = ",$tip_field";
    }else{
      $tip_column="";
    }
    $query = "SELECT $idcolumn,$displaycolumn $tip_column FROM $lookuptable ORDER BY $displaycolumn";
  }else{
    $query = str_replace(array("\r","\n")," ", $query);
  }
  // look for a dependent value
  if(!empty($dependent_value)){
    $query = str_replace('@@',$dependent_value, $query);
  }
  //debug($query,false);
  $result = fetch_assoc($query);

  if(is_null($id)){
    $id = "cmb".rand();
  }

  if($include_all){
    if($include_all!==true){
      $all_label=$include_all;
    }
    $part_b .= "\t<li><a href=\"#\" onclick=\"update_$id('0','$all_label')\">$all_label</a></li>\n";
  }

  if(!empty($onclick)){
    $main_onclick = "onclick=\"$onclick\"";
  }
  //debug($selectedvalue);
  $label = Null;
  foreach ($result as $key => $row)
    {
      $vlu=$row[strtolower($idcolumn)];
      $display=$row[strtolower($displaycolumn)];
      if(!is_null($tip_field)){
    $otip = $row[strtolower($tip_field)];
      }else{
    $otip = "";
      }
      if (empty($label)||$vlu==$selectedvalue){
        $label = $display;
      } elseif ($value == 0) {
        $label = $all_label;
      }
      $part_b .= "\t<li><a href=\"#\" onclick=\"update_$id('$vlu','$display')\">$display</a></li>\n";
    }

    $caret = "fa-caret-down";

  if($direction=='up'){
    $dropup = "dropup";
    $caret = "fa-caret-up";
  }
  $part_a = "
<script>
function update_$id(key,display){
$('#{$id}').val(key)
$('#{$id}_label').html(display)
$onchange
}
</script>
<div class=\"$group_class $dropup\">

 <button type=\"button\" $main_onclick class=\"$class\">
  <span id=\"{$id}_label\">$label</span>
  </button>
<button type=\"button\" class=\"$class dropdown-toggle\" data-toggle=\"dropdown\">
    <span class=\"caret\"></span>
</button>
  <ul class=\"dropdown-menu mega-dropdown\" role=\"menu\">";

  $html .= $part_a.$part_b."</ul>\n</div>\n";
  // add the hidden text box
  $html .= "<input type=\"hidden\" name=\"$fieldname\" id=\"{$id}\" value=\"$selectedvalue\" />";

  return($html);
}

function mkRelatedHTML()
{
    $defaults = array('col'=>NULL,'value'=>NULL,'value_sql'=>NULL,'value_executors'=>null,
        'display_js'=>null,'items'=>Null,'read_only'=>false,'fcol'=>NULL,'onchange'=>NULL,'onclick'=>NULL,
        'id'=>NULL,'label'=>NULL,'include_all'=>FALSE,'group_class'=>'btn-group','class'=>'btn btn-default',
        'title'=>NULL,'enable_help'=>false,'table_id'=>null,'field_id'=>null);
    extract(merge_args(func_get_args(),$defaults));

    extract(formDims($form_dims, $class, $label_class));

    $html = "<div $outer_class>\n";

    if (is_null($field_id) && $enable_help) {
        $field_id = getInputFieldId($col, $table_id);
    }

    $help = getInputHelp($enable_help, $field_id);

    if (!empty($value_sql)) {
        $values = fetch_assoc($value_sql, $value_executors);
        $insert_js = "";
        if ($values['error'] != true) {
            foreach($values as $value) {
                $json = json_encode($value, true);
                $insert_js .= "{$display_js}('{$col}_pad', {$json});\n";
            }
        }
    }

    if (!empty($label)) {
        $html .= "<label $label_class for=\"$col\">$label $help</label>\n";
    } elseif (empty($placeholder)) {
        $placeholder = $col;
    }

    $name=$name ?: "my[$col]";

    $html .= "<div $inner_class>";

    $help = getInputHelp($enable_help, $field_id);

    $html .= "
        <div class=\"btn-group\">
            <button class=\"btn btn-default\" type=\"button\" onclick=\"$onclick; return false;\"><span class=\"glyphicon glyphicon-plus\"></i></button>
            <button class=\"btn btn-default\" type=\"button\" onclick=\"$onclick; return false;\">Add $title</button>
        </div>
        <div class=\"\" style=\"\">
            <div id=\"{$col}_pad\" class=\"list-group\"></div>
        </div>
        <script>
            $insert_js
        </script>";

    $html .= "</div>\n";

    return $html;
}

function getInputFieldId($column, $table_id)
{
    /**
     *  Gets a field_id.
     */

    global $db;

    $field_id = fetch_one(
        "
        SELECT field_id
        FROM fields f
        WHERE `column` = ?
        AND table_id = ?;
        ", array($column, $table_id)
    );

    if ($field_id > 0) {
        return $field_id;
    } else {
        return null;
    }
}

function getInputHelp($enable_help, $field_id)
{
    /**
     *  Get a help value manually off
     */

    global $db;

    // Check for reference_id
    if ($field_id == null) {
        return "";
    }

    if ($enable_help == true) {
        $form_help = new \Info\Help($db);
        $html = $form_help->showMessage($field_id);
    } else {
        $html = "";
    }

    return $html;
}

function getInputPopover($enable_help, $field_id)
{
    /**
     *  Get a help value manually off
     */

    global $db;

    // Check for reference_id
    if ($field_id == null) {
        return "";
    }

    if ($enable_help == true) {
        $form_help = new \Info\Help($db);
        $html = $form_help->showInfo($field_id);
    } else {
        $html = "";
    }

    return $html;
}
