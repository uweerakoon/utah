<?php
namespace Info;

class Notify
{

    // Toggle variable to prevent email if the server isn't configured to do so. This should be true.

    private $email_enabled = true;
    private $host = 'http://smokemgt.utah.gov';

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
    }

    public function getNavUl($user_id)
    {
        $count = $this->getUserUnreadCount($user_id);

        if ($count > 0) {
            $counter = "<span id=\"notify-upper-count\" class=\"label label-danger\" style=\"position: absolute;top: 8px;right: 2px;\">$count</span>";
        }

        $html = "<li><a style=\"cursor: pointer;\" onclick=\"Notify.toggle()\" class=\"glyphicon glyphicon-inbox\"></a>$counter</li>";

        return $html;
    }

    public function navList($user_id)
    {

        $notify = $this->getUserUnread($user_id);
        $count = count($notify);

        //if ($count > 0) {
        //    $counter = "<span class=\"label label-danger pull-right\">$count</span>";
        //}

        $message = "<a class=\"list-group-item\">$counter<h6 style=\"margin:0px;\">Notifications</h6></a><div class=\"notify-sub\">";

        if (is_array($notify)) {
            $m_class = "list-group";

            foreach ($notify as $value) {
                $elapsed = $this->elapsed(strtotime($value['sent']));

                if (strlen($value['message']) > 96) {
                    $value['message'] = substr($value['message'], 0, 96) . "...";
                    $no_read = "readable=\"false\"";
                } else {
                    $no_read = "readable=\"true\"";
                }

                $cleaned = strip_tags($value['message']);
                $cont = "<p style=\"cursor:pointer\" onclick=\"Notify.detail({$value['id']})\" class=\"list-group-item-text\">{$cleaned}</p>";

                $message .= "<a $no_read id=\"notify_{$value['id']}\" class=\"list-group-item\">
                      <span class=\"notify-tag label label-minimum pull-right\">{$elapsed}</span>
                      <span class=\"glyphicon glyphicon-ok pull-right\" style=\"cursor:pointer; margin-right: 2px; color: #d4d4d4\" onclick=\"Notify.markAsRead({$value['id']});\"></span>
                      <h6 style=\"cursor:pointer\" onclick=\"Notify.detail({$value['id']})\" class=\"list-group-item-heading\">{$value['title']}</h6>
                      $cont
                    </a>";
            }
        } else {
            $m_class = "";
            $message .= modal_message("You have no unread notifications", "success");
            $message .= "<a href=\"notifications.php\">Notification Manager</a>";
        }

        $message .= "</div>";

        $html = "<div id=\"notify-div\" class=\"notify-dropdown $m_class\" style=\"display: none\">
                    $message
                </div>";

        return $html;
    }

    public function getDetail($notify_id)
    {
        $notify = fetch_row("SELECT * FROM notification_log WHERE notification_log_id = $notify_id");

        $html = "<div>
            <p>".$notify['message']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$notify['sent']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a onclick=\"Notify.markAsRead({$notify_id}); hide_modal();\" class=\"btn btn-default\">Mark as Read</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        $array = array('notify_id'=>$notify['notification_log_id'],'title'=>'<h5 style="margin: 0px">'.$notify['title'].'</h5>','html'=>$html);

        return json_encode($array, true);
    }



    public function getUserUnreadCount($user_id)
    {
        /**
         *  Count unread messages for the user.
         */

        $count_sql = $this->pdo->prepare("SELECT notification_log_id FROM notification_log WHERE `read` = 0 AND user_id = $user_id");
        $count_sql->execute(array($user_id));

        return $count_sql->rowCount();
    }

    private function getUserUnread($user_id)
    {
        /**
         *  Retrieve all unread notifications.
         */

        $notify = fetch_assoc(
            "SELECT notification_log_id as id, title, message, sent FROM notification_log WHERE `read` = 0 AND user_id = $user_id ORDER BY sent;"
        );

        if ($notify['error'] == true) {
            return false;
        }

        return $notify;
    }

    public function read($notify_id)
    {
        /**
         *  Mark a specific local message as read.
         */

        $read_sql = $this->pdo->prepare("UPDATE notification_log SET `read` = ? WHERE notification_log_id = ?;");
        $read_sql->execute(array(1, $notify_id));
        if ($read_sql->rowCount() >0) {
            $result = status_message("The notification has been marked read.", "success");
        } else {
            $result['error'] = true;
            $result['message'] = status_message("The notification has not been marked read.", "error");
        }

        return $result;
    }

    public function burnToDirector($burn_id)
    {
        /**
         *  Notification for Director that Burn Request (Form 4 is ready for Final Approval)
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.request_acres, b.start_date, b.end_date, u.full_name, u.email
            FROM burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(b.submitted_by = u.user_id)
            WHERE b.burn_id = ?", $burn_id
        );

        $today = today();

        /**
         *  UPDATE THIS TO DIRECTOR LEVEL = 7
         */

        $directors = fetch_assoc("SELECT user_id FROM users WHERE level_id IN(7,9) AND active = true");

        $notification_id = 1;
        $config = $this->getNotificationConfig($notification_id, $directors);

        $link = "<a href=\"{$this->host}/review/burn.php?burn=true&id={$burn_id}\">{$this->host}/review/burn.php?burn=true&id={$burn_id}</a>";

        $title = "{$burn['project_number']}: {$burn['start_date']}-{$burn['end_date']} Ready For Final Approval";
        $message = "{$burn['project_number']}: {$burn['start_date']}-{$burn['end_date']} by {$burn['email']} requesting {$burn['request_acres']} acres has been submitted for final approval. {$link}.";

        $this->send($config, $message, $title);
    }

    public function preBurnRenewed($pre_burn_id)
    {
        /**
         *   Notification for admin that a Pre-Burn has been renewed
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.year, b.submitted_on, u.full_name, u.email
            FROM pre_burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(b.submitted_by = u.user_id)
            WHERE b.pre_burn_id = ?
            AND b.active = true", $pre_burn_id
        );

        /**
         *  UPDATE THIS TO APPROPRIATE LEVEL
         */

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id = 9 AND active = true");

        $notification_id = 2;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}\">{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}</a>";

        $title = "{$burn['project_number']}: Form 3 Pre-Burn - Has been renewed for {$burn['year']}";
        $message = "{$burn['project_number']}: {$burn['year']} by {$burn['email']} originally submitted on {$burn['submitted_on']} has been renewed for {$burn['year']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function preBurnRevised($pre_burn_id)
    {
        /**
         *  Notification for admin that a Pre-Burn has been revised
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.year, b.submitted_on, r.name, r.description, u.full_name, u.email
            FROM pre_burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN pre_burn_revisions r ON(b.revision_id = r.revision_id)
            JOIN users u ON(b.submitted_by = u.user_id)
            WHERE b.pre_burn_id = ?
            AND b.active = true", $pre_burn_id
        );

        $today = today();

        /**
         *  UPDATE THIS TO APPROPRIATE LEVEL
         */

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id = 9 AND active = true");

        $notification_id = 3;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}\">{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}</a>";

        $title = "{$burn['project_number']}: Form 3 Pre-Burn - Has been revised with year {$burn['year']}";
        $message = "{$burn['project_number']}: {$burn['year']} by {$burn['email']} originally submitted on {$burn['submitted_on']} has been revised.
        {$burn['description']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function burnProjectSubmitted($burn_project_id)
    {
        /**
         *  Notification for admin that a Burn Project has been submitted
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, p.submitted_on, u.full_name, u.email
            FROM burn_projects p
            JOIN users u ON(p.submitted_by = u.user_id)
            WHERE p.burn_project_id = ?", $burn_project_id
        );

        $today = today();

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id >= 6 AND active = true");

        $notification_id = 4;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/project.php?detail=true&id={$burn_project_id}\">{$this->host}/review/project.php?detail=true&id={$burn_project_id}</a>";

        $title = "{$burn['project_number']}: Form 2 Burn Project - Has been submitted for review";
        $message = "{$burn['project_number']} by {$burn['email']} has been submitted at {$burn['submitted_on']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function preBurnSubmitted($pre_burn_id)
    {
        /**
         *  Notification for admin that a Pre-Burn has been submitted
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.submitted_on, b.year, u.full_name, u.email
            FROM pre_burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(p.submitted_by = u.user_id)
            WHERE b.pre_burn_id = ?", $pre_burn_id
        );

        $today = today();

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id >= 6 AND active = true");

        $notification_id = 5;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}\">{$this->host}/review/pre_burn.php?pre_burn=true&id={$pre_burn_id}</a>";

        $title = "{$burn['project_number']}: Form 3 Pre-Burn - Has been submitted for review";
        $message = "{$burn['project_number']}, Pre-Burn for {$burn['year']} by {$burn['email']} has been submitted at {$burn['submitted_on']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function burnSubmitted($burn_id)
    {
        /**
         *  Notification for admin that a Burn has been submitted
         */

        

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.submitted_on, b.start_date, b.end_date, u.full_name, u.email
            FROM burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(p.submitted_by = u.user_id)
            WHERE b.burn_id = ?", $burn_id
        );

        $today = today();

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id >= 6 AND active = true");

        $notification_id = 6;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/burn.php?burn=true&id={$burn_id}\">{$this->host}/review/burn.php?burn=true&id={$burn_id}</a>";

        $title = "{$burn['project_number']}: Form 4 Burn - Has been submitted for review";
        $message = "{$burn['project_number']}, Burn for {$burn['start_date']} through {$burn['end_date']} by {$burn['email']} has been submitted at {$burn['submitted_on']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function accomplishmentSubmitted($accomplishment_id)
    {
        /**
         *  Notification for admin that a Accomplishment has been submitted
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.submitted_on, b.start_datetime, b.end_datetime, u.full_name, u.email
            FROM accomplishments b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(p.submitted_by = u.user_id)
            WHERE b.accomplishment_id = ?", $accomplishment_id
        );

        $today = today();

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id >= 6 AND active = true");

        $notification_id = 7;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/accomplishment.php?detail=true&id={$accomplishment_id}\">{$this->host}/review/accomplishment.php?detail=true&id={$accomplishment_id}</a>";

        $title = "{$burn['project_number']}: Form 5 Accomplishment - Has been submitted for review";
        $message = "{$burn['project_number']}, Accomplishment for {$burn['start_datetime']} through {$burn['end_datetime']} by {$burn['email']} has been submitted at {$burn['submitted_on']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function documentationSubmitted($documentation_id)
    {
        /**
         *  Notification for admin that a Documentation has been submitted
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.submitted_on, b.observation_date, u.full_name, u.email
            FROM documentation b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            JOIN users u ON(p.submitted_by = u.user_id)
            WHERE b.documentation_id = ?", $documentation_id
        );

        $today = today();

        $users = fetch_assoc("SELECT user_id FROM users WHERE level_id >= 6 AND active = true");

        $notification_id = 8;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/review/documentation.php?detail=true&id={$documentation_id}\">{$this->host}/review/documentation.php?detail=true&id={$documentation_id}</a>";

        $title = "{$burn['project_number']}: Form 9 Documentation - Has been submitted for review";
        $message = "{$burn['project_number']}, Documentation for {$burn['observation_date']} by {$burn['email']} has been submitted at {$burn['submitted_on']}. {$link}.";

        $this->send($config, $message, $title);
    }

    public function burnFinalApproved($burn_id)
    {
        /**
         *  Notification for admin that a Burn has recieved final approval
         */

        $burn = fetch_row(
            "SELECT p.project_number, p.project_name, b.submitted_on, b.start_date, b.end_date
            FROM burns b
            JOIN burn_projects p ON(b.burn_project_id = p.burn_project_id)
            WHERE b.burn_id = ?", $burn_id
        );

        $condition = fetch_row(
            "SELECT c.comment, a.full_name, a.email, c.added_on
            FROM burn_conditions c
            JOIN users a ON(c.added_by = a.user_id)
            WHERE c.burn_id = ?;", $burn_id
        );

        $today = today();

        $users = fetch_assoc(
            "SELECT u.user_id
            FROM users u
            JOIN burns b ON(u.user_id = b.added_by)
            WHERE u.level_id >= 1
            AND u.active = true
            AND b.burn_id = ?", $burn_id
        );

        $notification_id = 9;
        $config = $this->getNotificationConfig($notification_id, $users);

        $link = "<a href=\"{$this->host}/manager/burn.php?burn=true&id={$burn_id}\">{$this->host}/manager/burn.php?burn=true&id={$burn_id}</a>";

        if(isset($condition['comment'])) {
            $cmessage = "Conditional approval: {$condition['comment']} (by, {$condition['email']}).";
        }

        $title = "{$burn['project_number']}: Form 4 Burn - Has recieved final approval";
        $message = "{$burn['project_number']}, Burn for {$burn['start_date']} through {$burn['end_date']} has recieved final approval authorizing the burn. {$cmessage} {$link}.";

        $this->send($config, $message, $title);
    }

    private function getNotificationConfig($notification_id, $users)
    {
        /**
         *  Retrieve notification configuration.
         *  This automatically checks user level too. To prevent manually misconfigured users from receiving
         *  unauthorized messages.
         */

        // Get notification info
        $notification = fetch_row("SELECT notification_id, function_name, email, local, min_user_level FROM notifications WHERE notification_id = $notification_id;");
        $level = $notification['min_user_level'];

        // Get all associated users.
        $notify_users = fetch_assoc("SELECT n.user_id, u.email FROM user_notifications n JOIN users u ON(n.user_id = u.user_id) WHERE notification_id = $notification_id AND level_id >= $level");

        $users = $this->filterUsers($notify_users, $users);

        return array('notification'=>$notification,'users'=>$users);
    }

    private function send($config, $message, $title)
    {
        /**
         *  Send a notification set.
         */

        $users = $config['users'];
        $now = now();

        foreach ($users as $user) {
            // Send the notifications locally.
            if ($config['notification']['local']) {
                $notify_sql = $this->pdo->prepare("INSERT INTO notification_log (title, message, notification_id, user_id, sent) VALUES (?, ?, ?, ?, ?);");
                $notify_sql->execute(array($title, $message, $config['notification']['notification_id'], $user['user_id'], now()));
            }

            // Send the notification by email.
            if ($config['notification']['email']) {
                $recipient = $user['email'];
                $subject = $title;
                $message = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/       DTD/xhtml1-strict.dtd\">
                            <html xmlns=\"http://www.w3.org/1999/xhtml\">
                            <head>
                            </head>
                            <body>
                            $message
                            <br>
                            <br>
                            <small>If you have any questions about this notification, please let us know by email at <a href=\"mailto:pcorrigan@fs.fed.us\">pcorrigan@fs.fed.us</a>.
                            </body>
                            </html>";
                $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                            'From: Utah Smoke Management Mailer' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                if ($this->email_enabled) {
                    mail($recipient, $subject, $message, $headers);
                    mail('8014401350@vtext.com', $subject, '');
                }
            }
        }
    }

    private function filterUsers($notify_users, $filter_users)
    {
        /**
         *  Filter the notify users by filter users.
         */

        $final = array();

        // Convert the filter users assoc to a basic.
        $filter = array();
        foreach ($filter_users as $val) {
            array_push($filter, $val['user_id']);
        }

        foreach ($notify_users as $key => $val) {
            if (in_array($val['user_id'], $filter)) {
                array_push($final, $val);
            } else {
                continue;
            }
        }

        return $final;
    }

    private function elapsed($time)
    {
        /**
         *  Time elapsed since.
         */

        $time = time() - $time;

        $tokens = array (
            31536000 => 'yr',
            2592000 => 'mon',
            604800 => 'wk',
            86400 => 'day',
            3600 => 'hr',
            60 => 'min',
            1 => 'sec'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) {
                continue;
            } else {
                $numberOfUnits = floor($time / $unit);
                return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
            }
        }
    }
}
