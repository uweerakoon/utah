<?php
function checklogin()
{
    /*
     *  Forwards to user login,
     *  authenticates user,
     *  calls appropriate html functions.
     */

    $args = array(
      'page_check'=>true,
      'admin'=>false,
      'title'=>'Smoke / Smoke',
      'extra_js'=>null,
      'extra_header'=>null,
      'public'=>false,
      'api'=>null,
      'less'=>false,
      'suppress_errors'=>false,
      'suppress_nav'=>false,
    );
    extract(merge_args(func_get_args(), $args));
    global $db, $user_id, $user_name, $user_level, $map_center;

    $root_path = "/var/www/utah";

    $html = array();
    $map_center = '39.383892, -111.683608';

    // Start the session.
    session_start();

    // Get the current page request information.
    $lastpage = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $needle = ltrim($_SERVER['REQUEST_URI'], '/');

    // Append the lastpage to the $_SESSION['history'].
    if (isset($_SESSION['history'])) {
        if ($lastpage != $_SESSION['history'][0]) {
            array_unshift($_SESSION['history'], $lastpage);
        }
    } else {
        $_SESSION['history'] = array($lastpage);
    }

    // Create a public user $_SESSION if the page is public & the user isnt logged in.
    if ($public == true && $_SESSION['user']['level_id'] < 1) {
        $_SESSION['user']['level_id'] = 0;
    }

    // Retrieve $_SESSION & associate libraries.
    if (isset($_SESSION['user'])) {
        // Pull user information.
        $user = array(
            'id'=>$_SESSION['user']['id'],
            'name'=>$_SESSION['user']['name'],
            'level_id'=>$_SESSION['user']['level_id'],
            'email'=>$_SESSION['user']['email'],
            'agency_id'=>$_SESSION['user']['agency_id'],
            'districts'=>$_SESSION['user']['districts'],
            'offices'=>$_SESSION['user']['offices']
        );

        // Define general use function library paths.
        $library_path = "{$root_path}/library.php";
        $form_path = "{$root_path}/form_functions.php";
        $html_path = "{$root_path}/html_library.php";

        // Include libraries.
        include $library_path;
        include $form_path;
        include $html_path;

        // Include the application modules.
        $modules_path = "{$root_path}/modules";
        foreach (glob("{$modules_path}/*.php") as $filename) {
            include_once $filename;
        }

        // Include control list.
        require_once '/var/www/utah/.control.php';

        // Handling for root (/) without index displayed.
        if ($_SERVER['REQUEST_URI'] == "/") {
            // We are at the index, make the needle index.php
            $needle = "index.php";
        }

        // Trim the $_GET variables.
        $index = stripos($needle, '?');
        if (!empty($index)) {
            $needle = substr($needle, 0, $index);
        }

        // Get the page's minimum required level. If user level is less, forward to login.
        $page_result = fetch_row("SELECT min_page_user_level, min_read_permission FROM pages WHERE page_url = ?;", $needle);
        if (!is_null($page_result['min_page_user_level'])) {
            $page_level = $page_result['min_page_user_level'];
        } else {
            // Assume its an admin level page if its not listed. Prevents unauthorized access to improperly configured pages.
            if ($page_check == true) {
                $page_level = 3;
            }
            if ($suppress_errors == false) {
                echo status_message('This page must be added to pages, or forward to 404.', 'error');
            }
        }

        // Check permissions.
        $permissions = returnUserPermissions($_SESSION['user']['id'], $page_result['min_read_permission'], 'read');

        if ($permissions['exit'] && $page_check) {
            $protocol = get_protocol();
            $server = $_SERVER['HTTP_HOST'];
            $href = $protocol."://".$server;

            echo  "<!DOCTYPE html>
                <html lang=\"en\">
                <head>
                <meta charset=\"utf-8\">
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                <title>$title</title>
                <link rel=\"shortcut icon\" href=\"/favicon.ico\">
                <link rel=\"icon\" href=\"/favicon.ico\" type=\"image/x-icon\">
                <link href=\"/css/style.css\" rel=\"stylesheet\" type=\"text/css\">
                <script src=\"/js/jquery.min.js\" type=\"text/javascript\"></script>
                <script src=\"/js/bootstrap.min.js\" type=\"text/javascript\"></script>
                <script src=\"/js/respond.min.js\" type=\"text/javascript\"></script>
                </head>
                <body>
                <div class=\"container\">
                <div class=\"row\">";

            //echo $permissions['message'];
            echo status_message('Your user does not have permission to view this page.', 'error');
            echo "<p><a href=\"{$href}\">Return to Home</a></p>";

            exit;
        }

        // Break out the page.
        if ($needle == '/') {
            header('location: index.php');
        }
    } else {
        // User is not logged in and the page is not public.
        // Redirect to login.php.
        $protocol = get_protocol();
        $server = $_SERVER['HTTP_HOST'];
        if ($needle != 'login.php') {
            header("location: $protocol://$server/login.php");
        }
    }

    // Get the the page path (URI) as an array.
    $lnk = explode("/", $needle);

    // Set thec
    if (count($lnk) > 1) {
        $link = '../';
    } else {
        $link = '';
    }

    // Construct the header.
    if ($suppress_nav == false) {
        $html['header'] = navbar($title, $link, $_SESSION['user']['level_id'], $less, $api, $extra_js);
    } else {
        $html['header'] = html_head($title, $link, $less, $api, $extra_js);
    }

    return $html['header'];
}

function echoapi()
{
    /**
     * Constructs the Google Maps API reference.
     */

    // Specify a default API key, this should be changed for billable projects
    $apikey = "AIzaSyDmlnWKI60J2KE3CP6XfqwemnJCjQkCMVU";    // Airsciences standard browser key.

    // Script references.
    return "<script src=\"https://maps.googleapis.com/maps/api/js?key=$apikey&libraries=geometry\" type=\"text/javascript\"></script>";
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

function page_info()
{
    $page_id = 1;

    $sql = "SELECT title, min_user_level,
        FROM info.pages
        WHERE page_id = $page_id;";

    $page = array(
        'title'=>'Derp',
    );

    return $page;
}

function html_head($title, $link = '', $less = false, $api = null, $extra_js = null)
{
    /**
     * Produces the administrative (Utah.gov) page header.
     */

    $root_path = "/var/www/utah";

    // Google maps API.
    $maps_script = echoapi();
    $html_head_file = file_get_contents("{$root_path}/head.html");

    $html = "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
    <meta charset=\"utf-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>$title</title>
    {$maps_script}
    {$html_head_file}
    <script src=\"/tinymce/tinymce.min.js\" type=\"text/javascript\"></script>
    $extra_js
    <script>
      /** Google Analytics */
    </script>
    <script>
      var UtahHelp = new UtahHelp();
      var Uploader = new Upload();
      var File = new FileManager();
      var Notify = new Notify();
      var Interface = new Interface();

      $(document).on('mouseenter','[data-toggle=\"tooltip\"]', function() {
        $(this).tooltip({
          container:'body',
          html:true,
          trigger:'manual'
        }).tooltip('show')
      }).on('mouseleave','[data-toggle=\"tooltip\"]', function() {
        $(this).tooltip('destroy')
      });

      $(document).ready( function() {
        $('[data-toggle=\"popover\"]').popover({
          container:'body',
          html:true,
          trigger:'click',
          placement: 'top'
        });
        Interface.status();
      });

      $('.modal').on('shown', function () {
        google.maps.event.trigger(map, \"resize\");
      });
    </script>
    </head>
    <body>";

    return $html;
}

function navbar($title, $link, $user_level, $less, $api, $extra_js)
{
    /**
     * Produces the page navbar, gets pages by user level and constructs a navbar per the users level.
     * @user_level: the current users level
     * $sql will only get pages with user level or less.
     */

    global $db;

    $html = html_head($title, $link, $less, $api, $extra_js);

    $public_pages = fetch_assoc("SELECT * FROM pages WHERE min_page_user_level = 0 ORDER BY menu_order;");
    $user_pages = fetch_assoc("SELECT * FROM pages WHERE min_page_user_level = 1 ORDER BY menu_order;");
    $admin_pages = fetch_assoc("SELECT * FROM pages WHERE min_page_user_level = 6 ORDER BY menu_order;");

    $html .= "<nav class=\"navbar navbar-default navbar-fixed-top\" role=\"navigation\">
    <div class=\"container\">
      <div class=\"navbar-header\">
        <button  type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#nav-collapse\">
          <span class=\"sr-only\">Toggle navigation</span>
          <span class=\"icon-bar\"></span>
          <span class=\"icon-bar\"></span>
          <span class=\"icon-bar\"></span>
        </button>
        <a class=\"navbar-brand\" href=\"#\" style=\"padding: 13px;\"><img style=\"height: 26px; display: inline;\" src=\"/favicon.ico\"> Utah SMS</a>
      </div>

      <div class=\"collapse navbar-collapse\" id=\"nav-collapse\">
        <ul class=\"nav navbar-nav navbar-right\">";

    if (isset($_SESSION['user']['full_name'])) {
        $full_name = $_SESSION['user']['full_name'];
        $html .= "<li><a href=\"/profile.php\"><small>".$full_name."</small></a></li>";
    }

        /**
     *  Notification list
     *  Login and logout trigger, (user_level reflects login state).
     */
    if ($user_level > 0) {
        $notify = new \Info\Notify($db);
        $list = $notify->navList($_SESSION['user']['id']);
        $html .= $notify->getNavUl($_SESSION['user']['id']);
        $html .= "<li><a href=\"/logout.php\">Logout</a></li>";
    } else {
        $list = "";
        $html .= "<li><a href=\"/login.php\"><strong><big>Login</big> here to access online forms</strong></a></li>";
    }

    /** For Public, User, & Admin */
    if ($user_level >= 0) {
        foreach ($public_pages as $key => $value) {
            $html .= "<li><a href=\"/".$value['page_url']."\">".$value['page_href_name']."</a></li>";
        }
    }

    /** Make the Registered User Dropdown */
    if ($user_level >= 1 && $user_level <= 5 || $user_level >= 8) {
        foreach ($user_pages as $key => $value) {
            if ($value['menu_order'] < 6 && $value['menu_order'] >= 1) {
	            $user_dropdown_html .= "<li><a href=\"/".$value['page_url']."\">".$value['page_href_name']."</a></li>";
            } elseif ($value['menu_order'] >= 1) {
	            $html .= "<li><a href=\"/".$value['page_url']."\">".$value['page_href_name']."</a></li>";
            }
        }

        if ($user_level >= 1 && $user_level <= 5) {
            $menu_prepend = "Review";
        } else {
            $menu_prepend = "Edit";
        }

        $html .= "<li class=\"dropdown\">
          <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$menu_prepend} Burn Forms <b class=\"caret\"></b></a>
			  <ul class=\"dropdown-menu\">";

        $html .= $user_dropdown_html;

        $html .= "</ul>
          </li>";
    }

    /** Make the Admin Dropdown */
    if ($user_level >= 6) {
        foreach ($admin_pages as $key => $value) {
            if ($value['menu_order'] < 6 && $value['menu_order'] >= 0) {
                $review_dropdown_html .= "<li><a href=\"/".$value['page_url']."\">".$value['page_href_name']."</a></li>";
            } elseif ($value['menu_order'] >= 6 && $value['menu_order'] < 10) {
                $admin_dropdown_html .= "<li><a href=\"/".$value['page_url']."\">".$value['page_href_name']."</a></li>";
            } elseif ($value['menu_order'] == 10 && in_array($user_level, array(6, 7))) {

            }
        }

        /** Reports for non user level users */
        if (in_array($user_level, array(6, 7))) {
            $reports = $user_pages[count($user_pages) - 1];
            $html .= "<li><a href=\"/".$reports['page_url']."\">".$reports['page_href_name']."</a></li>";
        }

        $html .= "<li class=\"dropdown\">
          <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Review Submittals <b class=\"caret\"></b></a>
          <ul class=\"dropdown-menu\">";

		    $html .= $review_dropdown_html;

        $html .= "</ul>
          </li>";

        $html .= "<li class=\"dropdown\">
          <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Admin <b class=\"caret\"></b></a>
            <ul class=\"dropdown-menu\">";

        $html .= $admin_dropdown_html;

        $html .= "</ul>
          </li>";
    }



    // Close the <nav>
    $html .= "</ul>
        </div>
    </nav>";

    // The notifications menu
    $html .= $list;

    return $html;
}

function public_footer()
{
    /**
     * Produces the public and user page footer.
     */

    $html = "";

    return $html;
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
