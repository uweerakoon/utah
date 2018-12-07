<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login / Utah.gov</title>
        <?php
          $root_path = "/Users/udaraweerakoon/managedisaster/managedisastersource/utah";
          echo file_get_contents("{$root_path}/head.html"); ?>
        <script type="text/javascript">
          $(function() {
            if (window.location.pathname+window.location.search != '/login.php') {
              window.location.pathname = "login.php";
              console.log("Rerouting to login.");
            }
          });
        </script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4" style="padding-top: 100px">
                    <?php
                    session_start();

                    if (isset($_SESSION['user']['id'])) {
                        echo "User";
                        if (isset($_SESSION['history'])) {
                            $protocol = get_protocol();
                            $lastpage = end($_SESSION['history']);
                            if (strpos($lastpage, 'login.php') !== false) {
                                $lastpage = $_SESSION['history'][count($_SESSION['history'])-2];
                            }
                            header("location: $protocol://$lastpage");
                        } else {
                            header('location: index.php');
                        }
                    } else {
                        include_once 'modules/user.php';
                        include_once 'modules/database.php';
                        include_once '/Users/udaraweerakoon/managedisaster/managedisastersource/utah/.control.php';
                        include_once "{$root_path}/library.php";
                        include_once "{$root_path}/form_functions.php";

                        // Create a temporary user.
                        $tmp_user = new \Info\User($db);

                        if (isset($_POST['my'])) {
                            $email = $_POST['my']['email'];
                            $password = $_POST['my']['password'];

                            $result = $tmp_user->login($email, $password);

                            if ($result['error'] == true) {
                                echo $result['message'];
                            } else if ($result['error'] == false) {
                                if (isset($_SESSION['history'])) {
                                    $protocol = get_protocol();
                                    $lastpage = $_SESSION['history'][0];
                                    if (strpos($lastpage, 'login.php') !== false) {
                                        $lastpage = $_SESSION['history'][1];
                                    }
                                    header("location: $protocol://$lastpage");
                                } else {
                                    header('location: index.php');
                                }
                            }
                        } else {

                        }

                        echo $tmp_user->loginForm();
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
                            // A nondescript message (clear/white).
                            $class = "";
                            $label = "";
                        }

                        $html = "<div class=\"alert $class\">
                        <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
                        <strong>$label</strong> $message
                        </div>";

                        return $html;
                    }

                    function merge_args($args, $defaults = array())
                    {
                        /*
                         * Merges a functions defaults array, with arguments submitted at call.
                         */

                        $count = count($args);

                        if ($count > 1 || !is_array($args[0])) {
                            $dkeys = array_keys($defaults);

                            for ($i = 0; $i < $count; $i++) {
                                $ikey = $dkeys[$i];
                                $defaults[$ikey] = $args[$i];
                            }
                        } else {
                            $defaults = array_merge($defaults, $args[0]);
                        }

                        return($defaults);
                    }

                    function is_https()
                    {
                        /**
                         *  Check if the client is using https. If so return true.
                         */

                        if (isset($_SERVER['https']) && $_SERVER['HTTPS'] != 'off') {
                            return true;
                        }
                        return false;
                    }

                    function get_protocol()
                    {
                        /**
                         *  Return the server protocol (http or https).
                         */

                        if (is_https()) {
                            return 'https';
                        }
                        return 'http';
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>
