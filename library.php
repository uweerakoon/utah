<?php

// Basic Functions
//______________________

function fix_boolean($cnd)
{
    if (!is_null($cnd)) {
        $cnd = ($cnd===true || $cnd==='true');
    }
    return $cnd;
}

function prepare_values($form)
{
  if (!is_array($form)) {
    return null;
  }

  /** Null Empty Values */
  foreach ($form as $key => $value) {
    if ($value == "" || $value == "null"
      || $value == "YYYY-MM-DD HH:MM"
      || $value == "YYYY-MM-DD") {
      $form[$key] = null;
    }
  }
  return $form;
}

// Database Functions
//______________________

function execute_bound(PDOStatement $prepared, array $values)
{
    /**
     *  Performs PDO->execute with data type consideration. E.g. NULL value handling.
     */

    $count = 1;

    foreach ($values as $value) {
        if ($value == "") {
            $value = null;
        }
        $type = gettype($value);

        switch ($type) {
            case 'NULL':
                $prepared->bindValue($count, $value, PDO::PARAM_NULL);
                break;
            case 'boolean':
                $prepared->bindValue($count, $value, PDO::PARAM_BOOL);
                break;
            case 'integer':
                $prepared->bindValue($count, $value, PDO::PARAM_INT);
                break;
            case 'string':
                $prepared->bindValue($count, $value, PDO::PARAM_STR);
                break;
            default:
                $prepared->bindValue($count, $value, PDO::PARAM_STMT);
                break;
        }

        $count++;
    }

    $prepared->execute();

    return $prepared;
}

function fetch_assoc($sql, $values = null, $pdo = null)
{
    if (empty($values) && strpos($sql, '$') > 0) {
        echo status_message("Database Error: Insecure Query Executed!<br>$sql", "error");
    }

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    if (!is_array($values)) {
        $values = array($values);
    }

    $query = $pdo->prepare($sql);
    execute_bound($query, $values);

    if ($query->rowCount() > 0) {
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $result['error'] = true;
      $result['message'] = "no_data";
      $result['detail'] = "Query found no data!";
    }

    return $result;
}

function fetch_assoc_offset($sql, $values, $pdo = null)
{
    if (empty($values) && strpos($sql, '$') > 0) {
        echo status_message("Database Error: Insecure Query Executed!<br>$sql", "error");
    }

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    if (!is_array($values)) {
        $values = array($values);
    }

    $query = $pdo->prepare($sql);
    execute_bound($query, $values);

    if ($query->rowCount() > 0) {
        $intermediate = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($intermediate as $key => $value) {
            $result[$key+1] = $value;
        }
    } else {
      $result['error'] = true;
      $result['message'] = "no_data";
      $result['detail'] = "Query found no data!";
    }

    return $result;
}

function fetch_row($sql, $values = null, $pdo = null)
{
    if (empty($values) && strpos($sql, '$') > 0) {
        echo status_message("Database Error: Insecure Query Executed!<br>$sql", "error");
    }

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    if (!is_array($values)) {
        $values = array($values);
    }

    $query = $pdo->prepare($sql);
    execute_bound($query, $values);

    if ($query->rowCount() > 0) {
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $result['error'] = true;
        $result['message'] = "no_data";
        $result['detail'] = "Query found no data!";
    }

    return $result[0];
}

function fetch_one($sql, $values = null, $pdo = null)
{
    if (empty($values) && strpos($sql, '$') > 0) {
        echo status_message("Database Error: Insecure Query Executed!<br>$sql", "error");
    }

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    if (!is_array($values)) {
        $values = array($values);
    }

    $query = $pdo->prepare($sql);
    $query->execute($values);

    if ($query->rowCount() > 0) {
        $result = $query->fetchColumn();
    } else {
        $result = null;
    }

    return $result;
}

function now()
{
    /**
     *  Returns a db formatted timestamp string for the current time/now.
     *  Same as PostgreSQL now().
     */

    $now = date("Y-m-d H:i:s");

    return $now;
}

function today()
{
    /**
     *  Returns a db formatted date string for the current date/now.
     */

    $today = date("Y-m-d");

    return $today;
}

// Table Specific Update Tools
//_________________________________

function update_form($table, $pkey, $id, $refresh_function)
{
    /**
     *  Constructs a default update form for a known table (requires fields table info).
     *
     */

    $table_id = fetch_one("SELECT db_table_id FROM db_tables WHERE db_table = ?", $table);

    $ctls = array();
    $title = "Update ". format_title($table);

    // Get the inputs.
    $input_types = "SELECT f.column, d.input, f.sql, f.primary_key, f.allow_null, f.multiselect, f.ptable,
    f.stable, f.mmtable, f.pcol, f.scol, f.sdisplay
    FROM fields f
    JOIN datatypes d ON (f.datatype = d.datatype_id)
    WHERE f.table_id = '$table_id'
    ORDER BY f.column";

    $inputs = fetch_assoc($input_types);

    // Get the values.
    $value_sql = "SELECT * FROM `$table` WHERE $pkey = $id;";
    $values = fetch_assoc($value_sql);

    // Construct the controls array.
    foreach ($inputs as $key => $input) {

        $primary_key = $input['primary_key'];
        $type = $input['input'];
        $field = $input['column'];
        $value = $values[0][$field];
        $sql = $input['sql'];
        $allow_null = $input['allow_null'];

        if (in_array($field, array('added_by','added_on',$p))) {
            continue;
        }

        // Multiselect Items
        $multiselect = $input['multiselect'];
        $mmtable = $input['mmtable'];
        $stable = $input['stable'];
        $ptable = $input['ptable'];
        $pcol = $input['pcol'];
        $scol = $input['scol'];
        $sdisplay = $input['sdisplay'];

        // Defaults for added/updated by/on
        if ($primary_key || in_array($field, array('updated_by','updated_on'))) {
            $type = 'hidden2';
        }

        if (in_array($field, array('updated_by'))) {
            $ctls[$field]['value'] = $_SESSION['user']['id'];
        }

        if (in_array($field, array('updated_by'))) {
            $ctls[$field]['value'] = now();
        }

        // Add to row to the controls array.
        $ctls[$field] = array('type'=>$type,'label'=>format_label($field),'value'=>$value);

        if ($allow_null == 1 && $type == 'combobox') {
            $ctls[$field]['allownull'] = true;
        }

        if (!is_null($sql)) {
            $ctls[$field]['sql'] = $sql;
            $ctls[$field]['fcol'] = 'id';
            $ctls[$field]['display'] = 'label';
        }

        if ($multiselect == true) {
            $ctls[$field]['multiselect'] = true;
            $ctls[$field]['value'] = mm_values(array('ptable'=>$ptable,'stable'=>$stable,'mmtable'=>$mmtable,
                'pcol'=>$pcol,'scol'=>$scol,'sdisplay'=>$sdisplay,'pvalue'=>$id));
        }
    }

    $html = mkForm(array('id'=>'edit_form','controls'=>$ctls,'cancel'=>true,'title'=>$title,'onclick'=>"edit_record('$table','$pkey', $id, $refresh_function)"));

    return $html;
}

function update_sql($table, $pkey, $id, $args)
{
    /**
     *  Constructs SQL and runs the update statement. Works with update_form and uses the same field logic.
     */

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    $table_id = fetch_one("SELECT db_table_id FROM db_tables WHERE db_table = ?", $table);

    // Setup default query items.
    $user_id = $_SESSION['user']['id'];
    $now = date("Y-m-d H:i:s");

    // Extract & remove the primary_key & id.
    $primary_id = $args[$pkey];
    unset($args[$pkey]);

    // The args keys.
    $keys = array_keys($args);

    // Create the many to many args array.
    $many_args = array();

    // Splits the array by standard or many-many fields.
    for ($i=0; $i < count($args); $i++) {
        if (is_array($args[$keys[$i]])) {
            $many_args[$keys[$i]] = $args[$keys[$i]];
            unset($args[$keys[$i]]);
            array_push($rm_keys, $i);
        }
    }

    // Remove added rows from $args
    for ($i=0; $i < count($args); $i++) {
        if (in_array($keys[$i], array('added_by','added_on'))) {
            unset($args[$keys[$i]]);
        }
    }

    // Reset the keys based off of the filtered arrays.
    $keys = array_keys($args);
    $many_keys = array_keys($many_args);

    // Run the many-many
    for ($i=0; $i < count($many_args); $i++) {
        // This item is an array itself. Therefore its a couplet spec.
        $field = $many_keys[$i];
        $specs = fetch_assoc(
            "SELECT ptable, stable, mmtable, pcol, scol, sdisplay
            FROM fields WHERE multiselect = true AND `table_id` = $table_id AND `column` = '$field';"
        );
        extract($specs[0]);
        $mm_id = rtrim($mmtable, "s")."_id";

        $delete_sql = $pdo->prepare("DELETE FROM `$mmtable` WHERE `$pcol` = ?");
        $delete_sql->execute(array($id));

        foreach ($many_args[$many_keys[$i]] as $value) {
            $run_sql = $pdo->prepare("INSERT INTO `$mmtable` (`$pcol`, `$scol`) VALUES (?, ?)");
            $run_sql->execute(array($id, $value));
        }
    }

    // Build the standard args.
    $sql = "UPDATE `$table` SET ";

    for ($i=0; $i < count($args); $i++) {
        if ($i == count($args) - 1) {
            $sql .= "`".$keys[$i]."` = ? ";
        } else {
            $sql .= "`".$keys[$i]."` = ?, ";
        }
    }

    $sql .= "
    WHERE $pkey = ?;";

    if (in_array(array('updated_by', 'updated_on'), $keys)) {
        $args['updated_by'] = $_SESSION['user']['id'];
        $args['updated_on'] = now();
    }

    $values = array_values($args);
    array_push($values, $primary_id);

    $update_sql = $pdo->prepare($sql);
    $update_sql->execute($values);

    $result['message'] = status_message("The record was updated.", "success");

    return $result;
}



// Table Specific Insert Tools
//_________________________________

function insert_form($table, $pkey, $refresh_function)
{
    /**
     *  Constructs a default insert form for a known table (requires fields table info).
     *
     */

    $table_id = fetch_one("SELECT db_table_id FROM db_tables WHERE db_table = ?", $table);

    $ctls = array();
    $title = "New ". format_title($table);

    if ($table == 'users') {
        // Special case for users (because of password handling).
        global $db;

        $temp_user = new \Info\User($db);
        $html = $temp_user->userForm($temp_user, true, true, false, true, $refresh_function);
    } else {
        // Get the inputs.
        $input_types = "SELECT f.column, d.input, f.sql, f.primary_key, f.allow_null, f.multiselect, f.ptable,
        f.stable, f.mmtable, f.pcol, f.scol, f.sdisplay
        FROM fields f
        JOIN datatypes d ON (f.datatype = d.datatype_id)
        WHERE f.table_id = '$table_id'
        AND f.primary_key = 0
        ORDER BY f.column";

        $inputs = fetch_assoc($input_types);

        // Construct the controls array.
        foreach ($inputs as $key => $input) {
            $type = $input['input'];
            $field = $input['column'];
            $sql = $input['sql'];
            $allow_null = $input['allow_null'];

            // Multiselect Items
            $multiselect = $input['multiselect'];
            $mmtable = $input['mmtable'];
            $stable = $input['stable'];
            $ptable = $input['ptable'];
            $pcol = $input['pcol'];
            $scol = $input['scol'];
            $sdisplay = $input['sdisplay'];

            // Add to row to the controls array.
            $ctls[$field] = array('type'=>$type,'label'=>format_label($field));

            // Defaults for added/updated by/on
            if (in_array($field, array('added_by','added_on','updated_by','updated_on'))) {
                $ctls[$field]['type'] = 'hidden2';
            }

            if (in_array($field, array('added_by','updated_by'))) {
                $ctls[$field]['value'] = $_SESSION['user']['id'];
            }

            if (in_array($field, array('added_on','updated_on'))) {
                $ctls[$field]['value'] = now();
            }

            if ($allow_null == 1 && $type == 'combobox') {
                $ctls[$field]['allownull'] = true;
            }

            if (!is_null($sql)) {
                $ctls[$field]['sql'] = $sql;
                $ctls[$field]['fcol'] = 'id';
                $ctls[$field]['display'] = 'label';
            }

            if ($multiselect == true) {
                $ctls[$field]['multiselect'] = true;
                $ctls[$field]['value'] = mm_values(array('ptable'=>$ptable,'stable'=>$stable,'mmtable'=>$mmtable,
                    'pcol'=>$pcol,'scol'=>$scol,'sdisplay'=>$sdisplay,'pvalue'=>$id));
            }
        }

        $html = mkForm(array('id'=>'insert_form','controls'=>$ctls,'cancel'=>true,'title'=>$title,'onclick'=>"insert_record('$table','$pkey', $refresh_function)"));
    }

    return $html;
}

function insert_sql($table, $pkey, $args)
{
    /**
     *  Constructs SQL and runs the update statement. Works with update_form and uses the same field logic.
     *
     */

    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    $table_id = fetch_one("SELECT db_table_id FROM db_tables WHERE db_table = ?", $table);

    // The args keys.
    $keys = array_keys($args);

    // Create the many to many args array.
    $many_args = array();

    // Splits the array by standard or many-many fields.
    for ($i=0; $i < count($args); $i++) {
        if (is_array($args[$keys[$i]])) {
            $many_args[$keys[$i]] = $args[$keys[$i]];
            unset($args[$keys[$i]]);
            array_push($rm_keys, $i);
        }
    }

    // Reset the keys based off of the filtered arrays.
    $keys = array_keys($args);
    $many_keys = array_keys($many_args);

    for ($i=0; $i < count($many_args); $i++) {
        // This item is an array itself. Therefore its a couplet spec.
        $field = $many_keys[$i];
        $specs = fetch_assoc(
            "SELECT ptable, stable, mmtable, pcol, scol, sdisplay
            FROM fields WHERE multiselect = true AND `table_id` = $table_id AND `column` = '$field';"
        );
        extract($specs[0]);
        $mm_id = rtrim($mmtable, "s")."_id";

        foreach ($many_args[$many_keys[$i]] as $value) {
            $run_sql = $pdo->prepare("INSERT INTO $mmtable (`$pcol`, `$scol`) VALUES (?, ?)");
            $run_sql->execute(array($id, $value));
        }
    }

    // Construct the sql.
    $sql = "INSERT INTO `$table` (`".implode("`, `", $keys)."`) VALUES (";

    for ($i=0; $i < count($args); $i++) {
        if ($i == (count($args) - 1)) {
            $sql .= "? ";
        } else {
            $sql .= "?, ";
        }
    }

    $sql .= ");";

    $values = array_values($args);

    $update_sql = $pdo->prepare($sql);
    $update_sql->execute($values);
    if ($update_sql->rowCount() > 0) {
        $result['message'] = status_message("The record was added.", "success");
    } else {
        $result['error'] = true;
        $result['message'] = status_message("The record was not added.", "error");
    }

    return $result;
}

// Table Specific Delete Tools
//_________________________________

function delete_sql($table, $pkey, $id)
{
    /**
     *  Constructs SQL and runs the delete statement.
     */

    $table_id = fetch_one("SELECT db_table_id FROM db_tables WHERE db_table = ?", $table);

    // Get the DB & make a pdo.
    if (!isset($pdo)) {
        global $db;
        $pdo = $db->get_connection();
    }

    // Get any many to many fields.
    $mm_fields = fetch_assoc(
        "SELECT mmtable
        FROM fields WHERE multiselect = true AND `table_id` = '$table_id';"
    );

    // Perform a delete for each one.
    if ($mm_fields['error'] != true) {
        foreach ($mm_fields as $value) {
            $delete_sql = $pdo->prepare("DELETE FROM ".$value['mmtable']." WHERE $pkey = ?");
            $delete_sql->execute(array($id));
        }
    }

    // Delete the main row.
    $delete = $pdo->prepare("DELETE FROM $table WHERE $pkey = ?;");
    $delete->execute(array($id));

    $result['message'] = status_message("The record has been deleted.", "success");

    return $result;
}

function format_label($label)
{
    /**
     *
     */
    $label = str_replace("_id", "", $label);

    $label = str_replace("_", " ", $label);

    $label = ucfirst($label);

    return $label;

}

function format_title($table)
{
    /**
     *
     */

    $title = rtrim(ucfirst($table), "s");
    if (substr($title, -2, 2) == "ie") {
        $title = substr($title, 0, -2) . "y";
    }
    $title = str_replace("_", " ", $title);

    return $title;
}

function format_table_name($table)
{
    /**
     *
     */

    $title = rtrim(ucfirst($table), "s");
    if (substr($title, -2, 2) == "ie") {
        $title = substr($title, 0, -2) . "y";
    }

    return $title;
}

function unzip($zip_file, $dir)
{
    $zip = new ZipArchive;
    // trim any directory sep
    $dir = rtrim($dir, DIRECTORY_SEPARATOR);
    $fls = array();
    // open it
    if ($zip->open($zip_file) === true) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            // get the file name
            $zpath = $zip->getNameIndex($i);
             // check the file extension
            $ext = pathinfo($zpath, PATHINFO_EXTENSION);
            // if its another zip file (crazy people)
            // we need to extract that as well
            if ($ext=="zip") {
                //unzip locally and then run this again
                $local_dir = dirname($zip_file);
                // full new path
                $new_path = $local_dir . DIRECTORY_SEPARATOR .  $zpath;
                //echo $new_path." : zip file \n";
                $zip->extractTo($local_dir, array($zpath));
                // and then run again
                //array_push($fls,unzip($new_path,$dir));
                $arr = unzip($new_path, $dir);
                foreach ($arr as $pth) {
                    $fls[count($fls)+1] = $pth;
                }
                // and finally delete this file
                unlink($new_path);
            } else {
                // full new path
                $new_path = $dir . DIRECTORY_SEPARATOR .  $zpath;
                // extract the file/directory
                $zip->extractTo($dir, array($zpath));
                //array_push($fls,$new_path);
                $fls[count($fls)+1] = $new_path;
                //echo $new_path." register file \n";
                // register the file
            }// end if zip
        }// for each file in zipped folder
        //$zip->extractTo($dir);
        $zip->close();
    }// end if open
    return $fls;
}

function get_csv($sql)
{
    /**
     *  Construct the CSV from a query.
     */

    $result = fetch_assoc($sql);
    $html = "";

    if ($result['error'] == true || count($result) == 0) {
        // if there is no result show the error
        echo status_message("No data was found and the .CSV was not generated", "error");
    } else {
        $keys = array_keys($result[0]);

        for ($i=0; $i < count($keys); $i++) {
            $key = format_label($keys[$i]);
            $html .=  "$key,";
        }

        // Include the linebreak
        $html = rtrim($html, ",");
        $html .= "\n";

        // loop through each of the columns
        foreach ($result as $key => $value) {
            foreach ($value as $sub_key => $sub_value) {
                $sub_value = str_replace(',', ';', $sub_value);
                $html .= "$sub_value,";
            }

            // Include the linebreak
            $html = rtrim($html, ",");
            $html .= "\n";
        }
    }

    return($html);
}

function array_list_assoc_group($array, $key) {
    /**
     *  Makes a simple array of an associative array subkey.
     */

    $new_array = array();

    foreach ($array as $itr_value) {
        array_push($new_array, $itr_value[$key]);
    }

    return $new_array;
}

function array_key_assoc_list_exists($haystack_array, $needle_assoc_array, $assoc_key) {
    /**
     *  Searches the haystack_array for matching values from the associative haystack key group.
     */

    $needle_list = array_list_assoc_group($needle_assoc_array, $assoc_key);

    foreach($needle_list as $sub_key) {
        echo code($sub_key);
        if(array_key_exists($sub_key, $haystack_array)) {
            return true;
        }
    }

    return false;
}

function array_key_assoc_list_exists_str($haystack_array, $needle_assoc_array, $assoc_key) {
    /**
     *  Searches the haystack_array for matching values from the associative haystack key group.
     */

    $needle_list = array_list_assoc_group($needle_assoc_array, $assoc_key);

    foreach($needle_list as $sub_key) {
        $sub_key = strtolower(str_replace(" ", "-", $sub_key));
        if(array_key_exists($sub_key, $haystack_array)) {
            return true;
        }
    }

    return false;
}

function implodeAssoc($array, $l1_value, $row = false)
{
    /**
     *  Comma implodes an associative array with 1 level.
     */

    $count = 0;

    if($row) {
        $string = $array[$l1_value];
    } else {
        foreach ($array as $value) {
            if ($count == 0) {
                $string = $value[$l1_value];
            } else {
                $string .= ', '.$value[$l1_value];
            }
            $count++;
        }
    }

    return $string;
}

function checkUserPermissions($user_id, $type, $action)
{
    /**
     *  Returns true if the user can access this type, false if not. (Port for $user->checkUserPermissions)
     *
     *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
     *  @param string $type     ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
     *  @param string $action   ('read', 'write')
     *  @return bool            true if permission is allow, false if not.
     */

    global $db;
    $user = new \Info\User($db);

    return $user->checkUserPermissions($user_id, $type, $action);
}

function returnUserPermissions($user_id, $type, $action)
{
    /**
     *  Returns permissions array with function action and error message.
     *
     *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
     *  @param string $type     ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
     *  @param string $action   ('read', 'write')
     *  @return array           'exit': should the function exit. 'message': associated error, if any.
     */

    $grant = checkUserPermissions($user_id, $type, $action);

    if ($grant) {
        $permissions = array('exit'=>false,'message'=>null);
    } else {
        $permissions = array('exit'=>true,'message'=>status_message('You\'re user does not have permission to complete this action', 'error'));
    }

    return $permissions;
}

function checkFunctionPermissions($user_id, $types, $action, $toggle = null)
{
    /**
     *  Returns true if the user can access this type, false if not. (Port for $user->checkUserPermissions)
     *
     *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
     *  @param string $type     ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
     *  @param string $action   ('read', 'write')
     *  @return array
     */

    global $db;
    $user = new \Info\User($db);

    return $user->checkFunctionPermissions($user_id, $types, $action, $toggle);
}

function checkFunctionPermissionsAll($user_id, $types, $toggle = null)
{
    /**
     *  Returns true if the user can access this type, false if not. (Port for $user->checkUserPermissions)
     *
     *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
     *  @param string $type     ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
     *  @return array
     */

    global $db;
    $user = new \Info\User($db);

    return $user->checkFunctionPermissionsAll($user_id, $types, $toggle);
}

function form_json_decode($json)
{
    /**
     *  Properly Decodes a form functions created $_POST['my'] and specifically the multiselect array
     */

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
        } else {
            // otherwise assume its data
            // and place it in an array
            // check if its another array
            //debug($vlu);
            $nme = preg_replace(array("/my\[/","/\]$/"), "", $vlu['name']);
            $keys = explode("][", $nme);
            // check the size of the array
            // only handles 2
            if (count($keys)===1) {
                $data[$keys[0]]=$vlu['value'];
            } else {
                if (empty($keys[1])) {
                  $data[$keys[0]][]=$vlu['value'];
                } else {
                  $data[$keys[0]][$keys[1]]=$vlu['value'];
                }
          }
        }
    }
    return($data);
}
