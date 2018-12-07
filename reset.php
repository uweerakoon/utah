<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Reset Password / Utah.gov</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/modify_records.js"></script>
    <script src="js/reset.js"></script>
    <style type="text/css">
        body {
            margin-top: 100px;
        }
    </style>
  </head>
  <body>
    <div class="container">
<?php
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/modules/database.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/modules/user.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/.control.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/checklogin.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/library.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/html_library.php';
include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/form_functions.php';

$host = 'http://smokemgt.utah.gov';

if (!isset($pdo)) {
    global $db;
    $pdo = $db->get_connection();
}

if (isset($_POST['rrs'])) {
    /**
     *  Process emails for a password reset
     */

    extract($_POST['my']);
    $client_ip = $_SERVER['REMOTE_ADDR'];
    
    // Convert all emails to lower case.
    $email = strtolower($email);
    $email_chk = strtolower($email_chk);
    
    // Validate Email & Password Confirm Fields.
    if ($email != $email_chk) {
        echo status_message("Your email confirmation didn't match. Please make sure it is correct and try again.", "error");
        $error=true;
    } else {
        $user_sql = $pdo->prepare("SELECT user_id FROM users WHERE email = ?;");
        $user_sql->execute(array($email));        
        
        $user_id = $user_sql->fetch(PDO::FETCH_ASSOC);
        
        if ($user_id['user_id'] < 1) {
            echo status_message("Your email was not found. Please make sure it is correct when you enter it again.", 'error');
            $error=true;
        }
    }

    if (!$error) {
        /**
         * No error is detected, process the reset email and reset table insert.
         */

        // Create reset row data
        $now = now();
        $expires = date('Y-m-d H:i:s', strtotime($now . ' + 1 day'));
        $token = md5(uniqid(mt_rand(), true));

        // Insert reset data.
        $tsql = $pdo->prepare("INSERT INTO reset (user_id, submitted_on, expires, token) VALUES (?, ?, ?, ?);");
        $tsql->execute(array($user_id['user_id'], $now, $expires, $token));
        
        // Send email
        $recipient = $email;
        $subject = "Utah.gov Smoke Management System - Your Password Reset Request.";
        $message = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
                    <html xmlns=\"http://www.w3.org/1999/xhtml\">
                    <head>
                    </head>
                    <body>
                    Utah.gov received a password reset request from: <strong>$client_ip</strong>.<br>
                    To reset your password please click <strong><a href=\"$host/reset.php?rpi=$token\" style=\"color\">Reset Password</strong></a>.
                    <br><br>
                    If you did not submit a password reset request, please let us know by email at <a href=\"mailto:automation@airsci.com\">automation@airsci.com</a>.
                    </body>
                    </html>";
        $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                    'From: Utah.gov Mailer' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
        
        mail($recipient, $subject, $message, $headers);
        
        echo status_message("Your password reset request has been submitted and emailed to you. Please click the link found in the email before it expires in 24 hours.", "success");
    }
}

/**
 *  Check and insert the new Password.
 */

if (isset($_POST['pwc'])) {
    extract($_POST['my']);

    if ($new_password != $new_password_chk) {
        // Passwords do not match.
        echo status_message("Your password confirmation didn't match. Please make sure your new passwords match when you enter them again.", 'error');
    } elseif ($new_password == $new_password_chk) {
        // Update the user's password and deactivate the token.
        $user = new \Info\User($db);
        $reset_result = $user->resetPassword($user_id, $new_password);

        if ($reset_result) {
            // Insert reset data.
            $tsql = $pdo->prepare("UPDATE reset SET successful = true WHERE user_id = ? AND token = ?;");
            $tsql->execute(array($user_id['user_id'], $token));
    
            echo status_message('The password was successfully reset <a href="login.php">Return to Login.</a>', 'success');    
        } else {
            echo status_message('The password was not updated.', 'error');    
        }
    }
} else {

    /**
     *  Process a token to allow the user to reset the password.
     */
    
    if (isset($_GET['rpi'])) {
        // Check Token
    
        $token = $_GET['rpi'];
        $now = now();
    
        $rpi_sql = "SELECT user_id FROM reset WHERE token = ? AND expires > ? AND successful = '0'";

        $user_id = fetch_one($rpi_sql, array($token, $now));
    
        //  Check RPI token in database.
        if ($user_id > 0) {
            $email = fetch_one("SELECT email FROM users WHERE user_id = $user_id;");
        
            echo "<div class=\"row\">
                    <div class=\"col-sm-6 col-sm-offset-3\">
                    <h3>Reset Account Password <span style=\"font-size:15px;color:rgb(95, 180, 95)\"></span></h3>
                    <hr>
                    </div>
                    </div>
                    <div class=\"row\">
                    <div class=\"col-sm-3 col-sm-offset-3\">
                    <p class=\"muted\" style=\"font-size:13px\">Please reset your password for:</p>
                    <p style=\"font-size:13px\"><strong>$email</strong><p>
                    </div>
                    <div class=\"col-sm-3\">";
                    
            $ctlr=array('user_id'=>array('type'=>'hidden2','value'=>$user_id),
                        'token'=>array('type'=>'hidden2','value'=>$token),
                        'new_password'=>array('type'=>'password','label'=>'New Password'),
                        'new_password_chk'=>array('type'=>'password','label'=>'Confirm New Password'));
            echo mkForm(array('controls'=>$ctlr,'suppress_legend'=>true,'button_class'=>'btn btn-success',
                'theme'=>'modal','submit'=>'pwc','action'=>"reset.php?rpi=$token"));
            
        } else {
        
            // $rpi token was incorrect, or expired.
            echo "<div class=\"row\">
                    <div class=\"col-sm-6 col-sm-offset-3\">
                    <h3>Reset Account Password <span style=\"font-size:15px;color:rgb(255, 60, 68)\"></span></h3>
                    <hr>
                    </div>
                    </div>
                    <div class=\"row\">
                    <div class=\"col-sm-6 col-sm-offset-3\">
                    <p style=\"font-size:13px\">Your password reset request is invalid. If you submitted the request more than 24 hours ago, it will have expired. If so please resubmit your request here <strong><a style=\"color: rgb(255,60,68)\" href=\"reset.php\">Reset Password</a></strong>.</p>
                    </div>
                    <div class=\"col-sm-3\">";
        }
    } else {
        
        // No $_GET (Default) View - Submit a new reset request.
        echo "<div class=\"row\">
                <div class=\"col-sm-6 col-sm-offset-3\">
                <h3>Reset Account Password <span style=\"font-size:15px;color:rgb(255, 60, 68)\"></span></h3>
                <hr>
                </div>
                </div><div class=\"row\">
                <div class=\"col-sm-3 col-sm-offset-3\">
                <p style=\"font-size:13px\">This form will send a password reset link to your registered email. Please enter your email and click \"Send Reset\" to submit the request.</p>
                </div>
                <div class=\"col-sm-3\">
                ";
                
        $ctls=array('email'=>array('type'=>'textbox','label'=>'Email'),
                    'email_chk'=>array('type'=>'textbox','label'=>'Confirm Email'));
                
        echo mkForm(array('controls'=>$ctls, 'label'=>'Send Reset', 'button_class'=>'btn btn-danger',
            'submit'=>'rrs','theme'=>'modal','suppress_legend'=>true));
    }
}


echo "</div>
    </div>";
?>
    </div>
  </body>
</html>