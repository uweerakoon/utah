<?php

namespace Info;

class User
{
    //public ;
    //protected ;
    //private ;

    private $cost = 13;

    public function __construct(\Info\db $db, $user = null)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
        $this->user = $user;
    }

    public function userForm($user, $new = true, $admin_use = false, $install = false, $ajax = false, $refresh_function = null)
    {
        /**
         *  Generates the default user add form.
         */

        $group_sql = "SELECT agency_id as id, agency as label FROM agencies ORDER BY agency;";

        $ctls = array(
            'email'=>array('type'=>'text','label'=>'Email','value'=>$this->email),
            'password'=>array('type'=>'password','label'=>'Password','value'=>''),
            'verify_password'=>array('type'=>'password','label'=>'Verify Password','value'=>''),
            'full_name'=>array('type'=>'text','label'=>'Full Name','value'=>$this->full_name),
            'phone'=>array('type'=>'text','label'=>'Phone','value'=>$this->phone),
            'address'=>array('type'=>'text','label'=>'Address','value'=>$this->address),
            'address2'=>array('type'=>'text','label'=>'Address Line 2','value'=>$this->address_b),
            'city'=>array('type'=>'text','label'=>'City','value'=>$this->city),
            'state'=>array('type'=>'text','label'=>'State','value'=>$this->state),
            'zip'=>array('type'=>'text','label'=>'Zip Code','value'=>$this->zip),
            'active'=>array('type'=>'boolean','label'=>'Is Active','value'=>$this->active)
        );

        if ($new == true) {
            $title = "Register";
        } else {
            $title = "Edit Profile";
        }

        if ($admin_use == true) {
            $title = "Add User";
            $type_sql = "SELECT user_level_id as id, user_level_name as label FROM user_levels WHERE user_level_id > 0;";
            $ctls['agency_id'] = array('type'=>'combobox','label'=>'User Agency','sql'=>$group_sql,'fcol'=>'id','display'=>'label','allownull'=>true);
            $ctls['district_id'] = array('type'=>'combobox','label'=>'User District(s)','table'=>'districts','fcol'=>'district_id','display'=>'district','allownull'=>true,'multiselect'=>true);
            $ctls['level_id'] = array('type'=>'combobox','label'=>'User Type','sql'=>$type_sql,'fcol'=>'id','display'=>'label');
        }

        if ($install == true) {
            $title = "";
            $ctls['level_id'] = array('type'=>'hidden2','value'=>3);
        }

        if ($ajax == true) {
            $html = mkForm(array('id'=>'insert_form','controls'=>$ctls,'title'=>$title,'cancel'=>true,'onclick'=>"insert_record('users','user_id', $refresh_function)"));
        } else {
            $html = mkForm(array('controls'=>$ctls,'title'=>$title,'cancel'=>true));
        }

        return $html;
    }

    public function adminForm($user_id)
    {
        /**
         *  Generates the default user add form.
         */

        $title = "Add New User";
        $onclick = "User.saveNew()";

        if (!is_null($user_id)) {
          $title = "Edit User";
          $user = $this->get($user_id);
          $onclick = "User.adminUpdate($user_id)";
          $districts = mm_values(array('ptable'=>'users','stable'=>'districts','mmtable'=>'user_districts','pcol'=>'user_id','scol'=>'district_id','sdisplay'=>'district','pvalue'=>$user_id));
        }
        $group_sql = "SELECT agency_id as id, agency as label FROM agencies ORDER BY agency;";
        $type_sql = "SELECT user_level_id as id, user_level_name as label FROM user_levels WHERE user_level_id > 0;";

        $ctls = array(
            'email'=>array('type'=>'text','label'=>'Email','value'=>$user['email']),
            'password'=>array('type'=>'password','label'=>'Password','value'=>'00000000'),
            'verify_password'=>array('type'=>'password','label'=>'Verify Password','value'=>'00000000'),
            'full_name'=>array('type'=>'text','label'=>'Full Name','value'=>$user['full_name']),
            'phone'=>array('type'=>'text','label'=>'Phone','value'=>$user['phone']),
            'address'=>array('type'=>'text','label'=>'Address','value'=>$user['address']),
            'address_b'=>array('type'=>'text','label'=>'Address Line 2','value'=>$user['address_b']),
            'city'=>array('type'=>'text','label'=>'City','value'=>$user['city']),
            'state'=>array('type'=>'text','label'=>'State','value'=>$user['state']),
            'zip'=>array('type'=>'text','label'=>'Zip Code','value'=>$user['zip']),
            'active'=>array('type'=>'boolean','label'=>'Is Active','value'=>$user['active']),
            'agency_id'=>array('type'=>'combobox','label'=>'User Agency','sql'=>$group_sql,'fcol'=>'id','display'=>'label','allownull'=>true,'value'=>$user['agency_id']),
            'district_id'=>array('type'=>'combobox','label'=>'User District(s)','table'=>'districts','fcol'=>'district_id','display'=>'district','allownull'=>true,'multiselect'=>true,'value'=>$districts),
            'level_id'=>array('type'=>'combobox','label'=>'User Type','sql'=>$type_sql,'fcol'=>'id','display'=>'label','value'=>$user['level_id'])
        );

        $html = mkForm(array('id'=>'admin_form','controls'=>$ctls,'title'=>$title,'cancel'=>true,'onclick'=>$onclick));

        return $html;
    }

    public function profileForm($user_id)
    {
        /**
         *  Profile Page User Form
         */

        $user = $this->get($user_id, false);

        $form_id = "profile_form";
        $title = "User Profile";

        $ctls = array(
            'password'=>array('type'=>'password','label'=>'Password','value'=>'00000000'),
            'verify_password'=>array('type'=>'password','label'=>'Verify Password','value'=>'00000000'),
            'email'=>array('type'=>'text','label'=>'Email','value'=>$user['email']),
            'full_name'=>array('type'=>'text','label'=>'Full Name','value'=>$user['full_name']),
            'phone'=>array('type'=>'text','label'=>'Phone','value'=>$user['phone']),
            'address'=>array('type'=>'text','label'=>'Address','value'=>$user['address']),
            'address_b'=>array('type'=>'text','label'=>'Address Cont','value'=>$user['address_b']),
            'city'=>array('type'=>'text','label'=>'City','value'=>$user['city']),
            'state'=>array('type'=>'text','label'=>'State','value'=>$user['state']),
            'zip'=>array('type'=>'text','label'=>'Zip','value'=>$user['zip']),
        );

        $html = mkForm(array('id'=>$form_id,'controls'=>$ctls,'title'=>$title,'cancel'=>true,'onclick'=>"User.profileUpdate($user_id)"));

        $html .= "<script type=\"text/javascript\">
            Validate.attachToForm(false, '#{$form_id}');
        </script>";

        return $html;
    }

    public function adminUpdate($user, $user_id)
    {
        /**
         *  profileUpdate
         *
         *  @param array  $user         the arguments array from the form.
         *  @param int    $user_id      the user_id to save.
         */

        if ($user['password'] != '00000000' && $user['verify_password'] != '00000000') {
            if ($user['password'] != $user['verify_password']) {
                //  Passwords do not match, warning message.
                $result['error'] = true;
                $result['detail'] = "The specified passwords do not match.";
                return $result;
            } else {
                if (strlen($user['password']) <= 8) {
                    // The users password is not satisfactory.
                    $result['error'] = true;
                    $result['detail'] = "The password must be longer than 8 characters.";
                    return $result;
                } else {
                    //  Passwords match, and are acceptable. Update.
                    $reset_success = $this->resetPassword($user_id, $user['password']);
                    if (!$reset_success) {
                      $result['error'] = true;
                      $result['detail'] = "The password could not be reset. Please try again or try another password.";
                      return $result;
                    }
                }
            }
        }

        $update_sql = $this->pdo->prepare("UPDATE users
            SET full_name = ?, email = ?, phone = ?, address = ?, address_b = ?, city = ?, state = ?, zip = ?, active = ?, agency_id = ?, level_id = ?
            WHERE user_id = ?");
        $update_sql = execute_bound($update_sql, array($user['full_name'], $user['email'], $user['phone'], $user['address'], $user['address_b'],
            $user['city'], $user['state'], $user['zip'], $user['active'], $user['agency_id'], $user['level_id'], $user_id));

        // Delete old user_districts many-many
        $district_delete = $this->pdo->prepare("DELETE FROM user_districts WHERE user_id = ?");
        $district_delete = execute_bound($district_delete, array($user_id));

        // Insert update user_districts
        foreach ($user['district_id'] as $value) {
            $district_update = $this->pdo->prepare("INSERT INTO user_districts (user_id, district_id) VALUES (?, ?);");
            $district_update = execute_bound($district_update, array($user_id, $value));
        }

        return array(
          "success"=>true,
          "detail"=>"The user was updated."
        );
    }

    public function profileUpdate($user, $user_id)
    {
        /**
         *  profileUpdate
         *
         *  @param array  $user         the arguments array from the form.
         *  @param int    $user_id      the user_id to save.
         */

        if ($user['password'] != '00000000' && $user['verify_password'] != '00000000') {
            if ($user['password'] != $user['verify_password']) {
                //  Passwords do not match, warning message.
                $html = status_message("The specified passwords do not match.", "error");
            } else {
                if (strlen($user['password']) <= 8) {
                    // The users password is not satisfactory.
                    $result['message'] = status_message("The password must be longer than 8 characters.", "error");
                    return $result;
                } else {
                    //  Passwords match, and are acceptable. Update.
                    $result = $this->resetPassword($user_id, $user['password']);
                }
            }
        }

        $update_sql = $this->pdo->prepare("UPDATE users
            SET full_name = ?, email = ?, phone = ?, address = ?, address_b = ?, city = ?, state = ?, zip = ?
            WHERE user_id = ?");
        $update_sql = execute_bound($update_sql, array($user['full_name'], $user['email'], $user['phone'], $user['address'], $user['address_b'],
            $user['city'], $user['state'], $user['zip'], $_SESSION['user']['id']));

        return $html;
    }

    public function saveUser()
    {
        /**
         *  LEGACY
         *  Saves a new user to the database using PHP password techniques.
         */

        // Extract the new user arguments.
        $email = $this->user['email'];
        $password = $this->user['password'];
        $full_name = $this->user['full_name'];
        $phone = $this->user['phone'];
        $level_id = $this->user['level_id'];
        $agency_id = $this->user['agency_id'];
        //$district_id = $this->user['district_id'];

        if (empty($this->user['email']) || empty($this->user['password']) ||  empty($this->user['full_name'])) {
            $result['error'] = true;
            $result["detail"] = "Please make sure the new user's email, full name, and new password are supplied.";
            return $result;
        }

        if ($this->user['password'] != $this->user['verify_password']) {
            $result['error'] = true;
            $result["detail"] = "The supplied passwords did not match.";
            return $result;
        }

        // Check if the email exists.
        $sql = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ?;");
        $sql->execute(array($email));
        if ($sql->rowCount()>0) {
            // A user has been found.
            $result['error'] = true;
            $result['detail'] = "A user with that email already exists.";
            return $result;
        }

        // Check the password
        if (strlen($password) <= 8) {
            // The users password is not satisfactory.
            $result['error'] = true;
            $result['detail'] = "The password must be longer than 8 characters.";
            return $result;
        }

        //if (isset($this->user['address2']) && isset($this->user['city']) && isset($this->user['state']) && isset($this->user['zip'])) {
        //    // Assumes all the address inputs are broken out, so combine them into a html block.
        //    $address = $this->combineAddressFields(
        //        $this->user['address'],
        //        $this->user['address2'],
        //        $this->user['city'],
        //        $this->user['state'],
        //        $this->user['zip']
        //    );
        //} else {
        //    // Assumes address is already combined.
        //    $address = $this->user['address'];
        //}

        // Generate a user salt and hash the password.
        $cost = $this->cost; // This is the hash performance cost. Default = 10. Higher requires more resource, but provides better protection.
        //$salt = strtr(base64_encode(random_bytes(16)), '+', '.');
        $salt = strtr(\base64_encode(\mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.'); // Generate random salt for this user.
        $salt = sprintf("$2a$%02d$", $cost) . $salt; // Specify the salt algorithm ($2a$ = blowfish), includes cost.
        $hash = crypt($password, $salt); // This is the backwards compatible (if php 5.5+ password_hash() condenses).

        // Insert the new user into the database.
        $sql = $this->pdo->prepare(
            "INSERT INTO users (password, salt, email, full_name, address, address_b, city, state, zip, phone, level_id, agency_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);"
        );
        $sql->execute(array($hash, $salt, $email, $full_name, $address, $address2, $city, $state, $zip, $phone, $level_id, $agency_id));
        if ($sql->rowCount()>0) {
            $result['count'] = $sql->rowCount();
            $result['message'] = status_message("The user has been added.", "success");
            $user_id = fetch_one("SELECT user_id FROM users WHERE email = '$email';");
            $notify_sql = $this->pdo->prepare("INSERT INTO user_notifications (user_id, notification_id) VALUES (?, ?);");
            $notify_sql->execute(array($user_id, 9));
        } else {
            $result['error'] = true;
            $result['message'] = status_message("An unknown error has occured, the user has not been added.", "error");
        }

        //foreach ($district_id as $value) {
        //    $district_sql = $this->pdo->prepare("INSERT INTO user_districts (user_id, district_id) VALUES (?, ?);");
        //    $district_sql->execute(array($user_id, $value));
        //}

        return $result;
    }

    public function groupForm($values)
    {
        /**
         *  Allows association of user to one or more districts
         */

        $title = "Add User(s) To District(s)";

        $ctls = array(
            'users'=>array('type'=>'combobox','label'=>'User(s)','fcol'=>'user_id','table'=>'users','display'=>'full_name','multiselect'=>true),
            'districts'=>array('type'=>'combobox','label'=>'District(s)','fcol'=>'district_id','table'=>'districts','display'=>'district','multiselect'=>true,'value'=>$values),
        );

        $html = mkForm(array('controls'=>$ctls,'id'=>'user_group_form','title'=>$title,'onclick'=>'addUserGroup()','cancel'=>true));

        return $html;
    }

    public function saveUserGroup($user_id, $district_id)
    {
        /**
         *  Saves a new user group to the database.
         */

        // Check if the group exists.
        $sql = $this->pdo->prepare("SELECT group_id FROM groups WHERE user_id = ? AND district_id = ?;");
        $sql->execute(array($user_id, $district_id));
        if ($sql->rowCount()>0) {
            // A user has been found.
            $result['error'] = true;
            $result['message'] = status_message("The user is already a member of that district", "error");
            return $result;
        }

        $sql = $this->pdo->prepare(
            "INSERT INTO groups (district_id, user_id)
            VALUES (?, ?);"
        );
        $sql->execute(array($district_id, $user_id));

        if ($sql->rowCount()>0) {
            $result['count'] = $sql->rowCount();
            $result['message'] = status_message("The user has been added to the district.", "success");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The user has not been added to the district.", "error");
        }

        return $result;
    }

    private function checkCredentials($email, $password)
    {
        /**
         *  Checks the users credentials against the db. If valid, true is returned.
         */

        $sql = $this->pdo->prepare("SELECT salt FROM users WHERE email=?;");
        $sql->execute(array($email));
        if ($sql->rowCount() > 0) {
            $result = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        // Hash the password for checking.
        $hash = crypt($password, $result['salt']);

        $sql = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ? AND password = ?;");
        $sql->execute(array($email, $hash));
        if ($sql->rowCount() > 0) {
            $result = $sql->fetch(\PDO::FETCH_ASSOC);
            return $result['user_id'];
        }
        return false;
    }

    public function loginForm()
    {
        /**
         * Constructs the html for a login form.
         */

        $ctls = array(
            'email'=>array('type'=>'text','label'=>'Email'),
            'password'=>array('type'=>'password','label'=>'Password')
        );

        $html = mkForm(array('controls'=>$ctls, 'title'=>'Login'));

        // The add a reset link.
        $html .= "<a href=\"reset.php\">Reset Password</a>";

        return $html;
    }

    public function login($email, $password)
    {
        /**
         *
         */

        if ($user_id = $this->checkCredentials($email, $password)) {
            // The user is valid, construct a the object
            if ($this->active($user_id)) {
                $_SESSION['user'] = $this->getUserInfo($user_id);
                $result['error'] = false;
            } else {
                $result['error'] = true;
                $result['message'] = status_message("Your user is inactive. Please contact the Utah smoke manager.", "error");
            }
        } else {
            $result['error'] = true;
            $result['message'] = status_message("Your password or email is invalid.", "error");
        }

        return $result;
    }

    public function warning($user_id)
    {
      $has_burns = $this->has_burns($user_id);

      if ($has_burns) {
        $cannot_delete = "<br><h5>Cannot Delete</h5>
          <p>The user you are trying to delete currently has
          burn forms in the system. Either change the owner for those burns
          or deactivate the user instead.</p>";
      }

      $user = fetch_row(
        "SELECT full_name, email, active
        FROM users WHERE user_id = ?",
        array($user_id)
      );

      $id_string = "an unnamed User";
      if (!empty($user['full_name']) && !empty($user['email'])) {
        $id_string = "<strong>{$user['full_name']}</strong> ({$user['email']})";
      } else if (!empty($user['full_name']) && empty($user['email'])) {
        $id_string = "<strong>{$user['full_name']}</strong>";
      } else if (empty($user['full_name']) && !empty($user['email'])) {
        $id_string = "<strong>{$user['email']}</strong>";
      }

      $body = "<div>
        <p>You are about to delete {$id_string}</p>
        {$cannot_delete}
      </div>";

      if (!$has_burns) {
        $body .= "
          <button class=\"btn btn-danger btn-block\" onclick=\"User.delete({$user_id}, false)\">Delete {$user['full_name']}</button>";
      }

      $body .= "<button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Cancel</button>";

      return array(
        "title"=>"Confirm Delete User",
        "content"=>$body
      );
    }

    public function has_burns($user_id) {
      $burn_projects = fetch_one(
        "SELECT COUNT(*) FROM burn_projects WHERE added_by = ?", array($user_id));
      $pre_burns = fetch_one(
        "SELECT COUNT(*) FROM pre_burns WHERE added_by = ?", array($user_id));
      $burns = fetch_one(
        "SELECT COUNT(*) FROM burns WHERE added_by = ?", array($user_id));
      $accomplishments = fetch_one(
        "SELECT COUNT(*) FROM accomplishments WHERE added_by = ?", array($user_id));
      $documentation = fetch_one(
        "SELECT COUNT(*) FROM documentation WHERE added_by = ?", array($user_id));

      if ($burn_projects > 0 || $pre_burns > 0 || $burns > 0
        || $accomplishments > 0 || $documentation > 0) {
        return true;
      }
      return false;
    }

    public function delete($user_id) {
      if ($this->has_burns($user_id)) {
        return array(
          "error"=>true,
          "message"=>"cannot_delete",
          "detail"=>"The user currently has burns in the system and cannot be deleted."
        );
      }

      $delete = $this->pdo->prepare(
          "DELETE FROM users WHERE user_id = ?;");
      $delete->execute(array($user_id));

      if ($delete->rowCount() > 0) {
        return array(
          "success"=>true,
          "message"=>"user_deleted",
          "detail"=>"The user has been deleted."
        );
      }
      return array(
        "error"=>true,
        "message"=>"cannot_delete",
        "detail"=>"An unknown error has occurred and the user cannot be deleted."
      );
    }

    public function active($user_id)
    {
        return fetch_one(
          "SELECT active FROM users WHERE user_id = ?", $user_id);
    }

    public function resetPassword($user_id, $password)
    {
        /**
         *  Reset the password.
         */

        $cost = 13; // This is the hash performance cost. Default = 10. Higher requires more resource, but provides better protection.
        //$salt = strtr(base64_encode(random_bytes(16)), '+', '.');
        $salt = strtr(\base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.'); // Generate random salt for this user.
        $salt = sprintf("$2a$%02d$", $cost) . $salt; // Specify the salt algorithm ($2a$ = blowfish), includes cost.
        $hash = crypt($password, $salt); // This is the backwards compatible (if php 5.5+ password_hash() condenses).

        $update = $this->pdo->prepare("UPDATE users SET password = ?, salt = ?, password_update = ? WHERE user_id = ?;");
        $update->execute(array($hash, $salt, now(), $user_id));
        if ($update->rowCount() > 0) {
            return true;
        }
        return false;
    }

    private function getUserInfo($user_id)
    {
        /**
         *  Queries all user information from the database
         */

        $district_sql = $this->pdo->prepare("SELECT d.district_id as id, d.district FROM districts d JOIN user_districts u ON(u.district_id = d.district_id) WHERE u.user_id = ?");
        $district_sql->execute(array(intval($user_id)));
        if ($district_sql->rowCount() > 0) {
            $district_array = $district_sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        $office_sql = $this->pdo->prepare("SELECT o.office_id as id, o.office FROM offices o JOIN user_offices u ON(u.office_id = o.office_id) WHERE u.user_id = ?");
        $office_sql->execute(array(intval($user_id)));
        if ($office_sql->rowCount() > 0) {
            $office_array = $office_sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        $sql = $this->pdo->prepare("SELECT user_id as id, email, full_name, address, phone, level_id, agency_id FROM users WHERE user_id = ?");
        $sql->execute(array(intval($user_id)));
        if ($sql->rowCount() > 0) {
            $user_array = $sql->fetchAll(\PDO::FETCH_ASSOC);
            $user_array[0]['districts'] = $district_array;
            $user_array[0]['offices'] = $office_array;
            return $user_array[0];
        }

        return false;
    }

    public function checkUserPermissions($user_id, $type, $action)
    {
        /**
         *  Returns true if the user can access this type, false if not.
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @param string $type     ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
         *  @param string $action   ('read', 'write')
         *  @return bool            true if permission is allow, false if not.
         */

        $level_id = fetch_one("SELECT level_id FROM users WHERE user_id = ?", $user_id);
        $rw_array = $this->getReadWrite($level_id);

        return $rw_array[$type][$action];
    }

    public function checkFunctionPermissions($user_id, $types, $action, $toggle)
    {
        /**
         *  Returns true if the user can access this type, false if not.
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @param string $types    array of all ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
         *  @param string $action   ('read', 'write')
         *  @return bool            true if permission is allow, false if not.
         */

        $result['any'] = false;
        $result['deny'] = true;

        foreach ($types as $type) {
            $result[$type] = $this->checkUserPermissions($user_id, $type, $action);
            if ($result[$type] && $result['any'] == false) {
                $result['any'] = true;
            }
            if ($result[$type] && $result['deny'] == true) {
                $result['deny'] = false;
            }
        }

        if ($result['deny']) {
            $action = $toggle == 'interface' ? "onclick=\"Interface.toggle();\"": "";
            $action = $toggle == 'modal' ? "onclick=\"cancel_modal();\"": "";
            $result['message'] = "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\" $action><span aria-hidden=\"true\">&times;</span></button>
                    <strong>Permissions Error:</strong> You do not have permission to complete his action.
                </div>";
        }

        return $result;
    }

    public function checkFunctionPermissionsAll($user_id, $types, $toggle)
    {
        /**
         *  Returns true if the user can access this type, false if not.
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @param string $types    array of all ('public', 'user', 'user_district', 'user_agency', 'admin', 'admin_final', 'system')
         *  @param string $action   ('read', 'write')
         *  @return bool            true if permission is allow, false if not.
         */

        $result['read']['any'] = false;
        $result['read']['deny'] = true;
        $result['write']['any'] = false;
        $result['write']['deny'] = true;

        foreach ($types as $type) {
            $result['read'][$type] = $this->checkUserPermissions($user_id,  $type, 'read');
            if ($result['read'][$type] && $result['read']['any'] == false) {
                $result['read']['any'] = true;
            }
            if ($result['read'][$type] && $result['read']['deny'] == true) {
                $result['read']['deny'] = false;
            }
            $result['write'][$type] = $this->checkUserPermissions($user_id,  $type, 'write');
            if ($result['write'][$type] && $result['write']['any'] == false) {
                $result['write']['any'] = true;
            }
            if ($result['write'][$type] && $result['write']['deny'] == true) {
                $result['write']['deny'] = false;
            }
        }

        if ($result['read']['deny'] || $result['write']['deny']) {
            $action = $toggle == 'interface' ? "onclick=\"Interface.toggle();\"": "";
            $action = $toggle == 'modal' ? "onclick=\"cancel_modal();\"": "";
            $result['message'] = "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\" $action><span aria-hidden=\"true\">&times;</span></button>
                    <strong>Permissions Error:</strong> You do not have permission to complete his action.
                </div>";
        }

        return $result;
    }

    private function getReadWrite($level_id)
    {
        /**
         *  Returns the page type read write array for that level_id
         */

        switch ($level_id) {
            case 1:
                // $level_id = 1, user
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>false),
                    'user_agency'=>array('read'=>false,'write'=>false),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 2:
                // $level_id = 2, user_district review.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>false),
                    'user_district'=>array('read'=>true,'write'=>false),
                    'user_agency'=>array('read'=>false,'write'=>false),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 3:
                // $level_id = 3, user_district administrator.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>false,'write'=>false),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 4:
                // $level_id = 4, user_agency review.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>false),
                    'user_district'=>array('read'=>true,'write'=>false),
                    'user_agency'=>array('read'=>true,'write'=>false),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 5:
                // $level_id = 5, user_district administrator.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>true,'write'=>true),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 6:
                // $level_id = 6, daq review.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>true,'write'=>true),
                    'admin'=>array('read'=>true,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 7:
                // $level_id = 7, daq director.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>true,'write'=>true),
                    'admin'=>array('read'=>true,'write'=>false),
                    'admin_final'=>array('read'=>true,'write'=>true),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 8:
                // $level_id = 8, daq administrator.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>true),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>true,'write'=>true),
                    'admin'=>array('read'=>true,'write'=>true),
                    'admin_final'=>array('read'=>true,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
            case 9:
                // $level_id = 9, system administrator.
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>true),
                    'user'=>array('read'=>true,'write'=>true),
                    'user_district'=>array('read'=>true,'write'=>true),
                    'user_agency'=>array('read'=>true,'write'=>true),
                    'admin'=>array('read'=>true,'write'=>true),
                    'admin_final'=>array('read'=>true,'write'=>true),
                    'system'=>array('read'=>true,'write'=>true),
                );
                break;
            default:
                // $level_id = 0 or 'undefined'
                $rw_array = array(
                    'public'=>array('read'=>true,'write'=>false),
                    'user'=>array('read'=>false,'write'=>false),
                    'user_district'=>array('read'=>false,'write'=>false),
                    'user_agency'=>array('read'=>false,'write'=>false),
                    'admin'=>array('read'=>false,'write'=>false),
                    'admin_final'=>array('read'=>false,'write'=>false),
                    'system'=>array('read'=>false,'write'=>false),
                );
                break;
        }

        return $rw_array;
    }

    public function get($user_id, $full = true, $safe = true)
    {
        /**
         *  Get the User
         *
         *  @param int $user_id         the users id (typically $_SESSION['user']['id']).
         *  @param boolean $full        if true, retrieve all associated table information (agency, districts).
         *  @param boolean $safe        if false, retieve salt & password as well.
         *
         *  @return (array|boolean)     if user is found, the user array, else false.
         */

        if ($safe) {
            $user = fetch_row("SELECT user_id, agency_id, level_id, full_name, email, address, address_b, city, state, zip, phone, active, password_update FROM users WHERE user_id = ?", $user_id);
        } else {
            $user = fetch_row("SELECT * FROM users WHERE user_id = ?", $user_id);
        }

        if ($full) {
            $user['agency'] = $this->getUserAgency($user_id, true);
            $user['districts'] = $this->getUserDistricts($user_id, 'php', true);
        }

        if (!$user['error']) {
            return $user;
        } else {
            return false;
        }
    }

    public function getBlocks($user_id)
    {
        /**
         *  Get the User in standard HTML blocks
         *
         *  @param int $user_id         the users id (typically $_SESSION['user']['id']).
         */

        $user = $this->get($user_id);

        $block['password_update'] = "<span><strong>Password Last Updated:</strong><br>";
        if (is_null($user['password_update'])) {
            $block['password_update'] .= "<span class=\"text-danger\">Never <span data-toggle=\"tooltip\" title=\"Consider Updating Your Password\" class=\"glyphicon glyphicon-question-sign\"></span></span>";
        } else {
            $now = new \DateTime(now());
            $update = new \DateTime($user['password_update']);
            $interval = $update->diff($now);
            if ($interval->format('%a') > 90) {
                $block['password_update'] .= "<span class=\"text-danger\">{$interval->format('%a days')} <span data-toggle=\"tooltip\" title=\"Consider Updating Your Password\" class=\"glyphicon glyphicon-question-sign\"></span></span>";
            } elseif ($interval->format('%a') <= 0) {
                $block['password_update'] .= "Updated today";
            } else {
                $block['password_update'] .= $interval->format('%a days');
            }
        }
        $block['password_update'] .= "</span>";

        if (!empty($user['email'])) {
            $block['email'] = "<a href=\"mailto:#\">{$user['email']}</a>";
        }

        if (!empty($user['address'])) {
            $address = $this->viewAddress($user['address'], $user['address_b'], $user['city'], $user['state'], $user['zip']);
            $url_addr = str_replace(array(" ","\n","<br>"), "+", $address);
            $block['address_href'] = "https://www.google.com/maps/place/$url_addr";
            $block['address'] = str_replace("\n", "<br>", $address);
        }

        if (!empty($user['phone'])) {
            $block['phone'] = "<abbr title=\"Phone\">P:</abbr> {$user['phone']}";
        }

        $a_length = count($user['agency']);

        if ($a_length > 0) {
            if ($a_length > 1) {
                $block['agency_title'] = "Agencies";
                $hr = "<hr>";
            } else {
                $block['agency_title'] = "Agency";
            }

            for ($i = 0; $i < $d_length; $i++) {
                if ($i == $d_length - 1) {
                    $addr_style = "style=\"margin-bottom: 0px\"";
                    $hr = "";
                }

                $a_address = str_replace("\n", "<br>", $user['agency'][$i]['address']);
                $a_url_addr = str_replace(array(" ","\n","<br>"), "+", $user['agency'][$i]['address']);
                $a_address_href = "https://www.google.com/maps/place/$d_url_addr";

                $block['agency'] .= "<address $addr_style>
                        <strong>{$user['agency'][$i]['agency']}</strong><br>
                        <a href=\"{$d_address_href}\" style=\"color: #000\">
                            {$d_address}<br>
                        </a>
                        <abbr title=\"id\">ID:</abbr> <strong>{$user['agency'][$i]['identifier']}</strong><br>";

                $block['agency'] .= "</address>$hr";
            }
        }

        $d_length = count($user['districts']);

        if ($d_length > 0) {
            if ($d_length > 1) {
                $block['district_title'] = "Units";
                $hr = "<hr>";
            } else {
                $block['district_title'] = "Unit";
            }

            for ($i = 0; $i < $d_length; $i++) {
                if ($i == $d_length - 1) {
                    $addr_style = "style=\"margin-bottom: 0px\"";
                    $hr = "";
                }

                $d_address = str_replace("\n", "<br>", $user['districts'][$i]['address']);
                $d_url_addr = str_replace(array(" ","\n","<br>"), "+", $user['districts'][$i]['address']);
                $d_address_href = "https://www.google.com/maps/place/$d_url_addr";

                $block['districts'] .= "<address $addr_style>
                        <strong>{$user['districts'][$i]['district']}</strong><br>
                        <a href=\"{$d_address_href}\" style=\"color: #000\">
                            {$d_address}<br>
                        </a>
                        <abbr title=\"id\">ID:</abbr> <strong>{$user['districts'][$i]['identifier']}</strong><br>";

                if (!empty($user['districts'][$i]['old_identifier'])) {
                    $block['districts'] .= "<abbr title=\"legacy_id\">Legacy ID:</abbr> <strong>{$user['districts'][$i]['old_identifier']}</strong>";
                }

                $block['districts'] .= "</address>$hr";
            }
        }

        return $block;
    }

    public function getUserAgency($user_id, $type = 'php', $full = false)
    {
        /**
         *
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @return int agency_id            true if permission is allow, false if not.
         */

        if ($full) {
            $query = fetch_row("SELECT * FROM agencies WHERE agency_id IN(SELECT agency_id FROM users WHERE user_id = ?)", $user_id);
        } else {
            $query = fetch_row("SELECT agency_id, agency, abbreviation FROM agencies WHERE agency_id IN(SELECT agency_id FROM users WHERE user_id = ?)", $user_id);
        }

        if ($type == 'sql') {
            if (count($query) > 1) {
                $agency = "IN(". implodeAssoc($query, 'agency_id', true) . ")";
            } else {
                $agency = "= ".implodeAssoc($query, 'agency_id', true);
            }
        } elseif ($type == 'json') {
            $agency = json_encode($query, true);
        } else {
            $agency = $query;
        }

        if (!$query['error']) {
            return $agency;
        } else {
            return false;
        }
    }

    public function getUserDistricts($user_id, $type = 'php', $full = false)
    {
        /**
         *
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @return array int       array of district_ids.
         */

        if ($full) {
            $query = fetch_assoc("SELECT d.* FROM districts d JOIN user_districts u ON(d.district_id = u.district_id) WHERE user_id = ? ORDER BY district;", $user_id);
        } else {
            $query = fetch_assoc("SELECT d.district_id, district, identifier FROM districts d JOIN user_districts u ON(d.district_id = u.district_id) WHERE user_id = ? ORDER BY district;", $user_id);
        }

        if ($type == 'sql') {
            if (count($query) > 1) {
                $districts = "IN(". implodeAssoc($query, 'district_id') . ")";
            } else {
                $districts = "= ".implodeAssoc($query, 'district_id');
            }
        } elseif ($type == 'json') {
            $districts = json_encode($query, true);
        } else {
            $districts = $query;
        }

        if (!$query['error']) {
            return $districts;
        } else {
            return false;
        }
    }

    public function getUserDistrictsIds($user_id)
    {
        /**
         *
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @return array int       array of district_ids.
         */

        $query = fetch_assoc("SELECT d.district_id FROM districts d JOIN user_districts u ON(d.district_id = u.district_id) WHERE user_id = ?", $user_id);

        if (!$query['error']) {
            $districts = array();

            foreach ($query as $value) {
                array_push($districts, $value['district_id']);
            }

            return $districts;
        } else {
            return false;
        }
    }

    public function getUserOffices($user_id, $type = 'php')
    {
        /**
         *
         *
         *  @param int $user_id     the users id (typically $_SESSION['user']['id'])
         *  @return array int       array of office_ids.
         */

        $query = fetch_assoc("SELECT o.office_id, office, identifier FROM offices o JOIN user_offices u ON(o.office_id = u.office_id) WHERE user_id = ?", $user_id);

        if ($type == 'sql') {
            if (count($query) > 1) {
                $offices = "IN(". implodeAssoc($query, 'district_id') . ")";
            } else {
                $offices = "= ".implodeAssoc($query, 'district_id');
            }
        } elseif ($type == 'json') {
            $offices = json_encode($query, true);
        } else {
            $offices = $query;
        }

        if (!$query['error']) {
            return $offices;
        } else {
            return false;
        }
    }

    public function districtToggle($user_id, $value)
    {
        /**
         *  Constructs a $_GET district toggle.
         */

        $districts = $this->getUserDistricts($user_id);

        if (!isset($value)) {
            $value = 0;
        }

        if (!$districts) {
            $html = status_message("Your account is not associated with any districts. Please contact Utah.gov.", "error");
        } else {
            $ctls = array(
                'district_id'=>array('label'=>'Filter by district','type'=>'dropdown','value'=>$value,'sql'=>$sql,'fcol'=>'district_id',
                    'display'=>'district','onchange'=>'go_to_district(key)','class'=>'btn btn-sm btn-default',
                    'include_all'=>true,'all_label'=>'All')
            );

            $html = "<div class=\"pull-right\" style=\"margin-bottom: 8px;\">".mkFieldset(array('controls'=>$ctls,'suppress_submit'=>true,'suppress_legend'=>true,'class'=>'pull-right'))."</div>";
        }

        return $html;
    }

    public function countDistricts($user_id)
    {
        /**
         *  Returns the number of districts associated with a user.
         */

        return count($this->getUserDistricts($user_id));
    }

    public function hasAgency($user_id)
    {
        /**
         *  Checks if the user is in an agency.
         */

        $agency = $this->getUserAgency($user_id);
        if ($agency['agency_id'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isInDistrict($user_id, $district_id)
    {
        /**
         *  Returns true if the user is in the district.
         */

        $sql = fetch_one("SELECT district_id FROM user_districts WHERE user_id = ? AND district_id = ?", array($user_id, $district_id));
        if ($sql > 0) {
            return true;
        } else {
            return false;
        }
    }

    //  Miscellaneous Helpers
    //_________________________

    public function viewAddress($address1, $address2, $city, $state, $zip)
    {
        if(isset($address2) || isset($city) || isset($state) || isset($zip)) {
            $html = "$address1<br>$address2<br>$city, $state $zip";
        } else {
            $html = $address1;
        }

        return $html;
    }

    public function combineAddressFields($address1, $address2, $city, $state, $zip)
    {
        if ($address2 == "") {
            $html = "$address1
$city, $state $zip";
        } else {
            $html = "$address1
$address2
$city, $state $zip";
        }

        return $html;
    }

    private function getHashCost()
    {
        /**
         *  Determines what hashing cost the server can support with the the specified time target (in seconds).
         */

        $cost = 8; // Start cost, we will go up form here (8 is just below recommended default).
        $timeTarget = 0.5; // Calculation time cost in seconds, we want to determine how long hashing takes. Higher is more secure but slower.

        do {
            $cost++;
            $start = microtime(true);
            $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.'); // Generate random salt for this user.
            $salt = sprintf("$2a$%02d$", $cost) . $salt; // Specify the salt algorithm ($2a$ = blowfish), includes cost.
            $hash = crypt($password, $salt); // This is the backwards compatible (if php 5.5+ password_hash() condenses).
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }
}
