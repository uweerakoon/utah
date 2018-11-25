<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Page class object (initializes class functions).
$index = new \Page\Index($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "form":
        if (isset($_POST['index_id'])) {
            echo $index->form($_POST['index_id']);
        } else {
            echo $index->form();
        }
        break;
    case "save":
        $args = blah_decode($_POST['args']);
        echo $index->save($args);
        break;
    case "update":
        $args = blah_decode($_POST['args']);
        echo $index->update($_POST['index_id'], $args);
        break;
    case "form-levels":
        if (isset($_POST['index_level_id'])) {
            echo $index->formLevels($_POST['index_level_id']);
        } else {
            echo $index->formLevels();
        }
        break;
    case "save-levels":
        $args = blah_decode($_POST['args']);
        echo $index->saveLevels($args);
        break;
    case "update-levels":
        $args = blah_decode($_POST['args']);
        echo $index->updateLevels($_POST['index_level_id'], $args);
        break;
    case "form-team-fires":
        if (isset($_POST['gbcc_team_fire_id'])) {
            echo $index->formTeamFires($_POST['gbcc_team_fire_id']);
        } else {
            echo $index->formTeamFires();
        }
        break;
    case "save-team-fires":
        $args = blah_decode($_POST['args']);
        echo $index->saveTeamFires($args);
        break;
    case "update-team-fires":
        $args = blah_decode($_POST['args']);
        echo $index->updateTeamFires($_POST['gbcc_team_fire_id'], $args);
        break;
    case "form-large-fires":
        if (isset($_POST['gbcc_large_fire_id'])) {
            echo $index->formLargeFires($_POST['gbcc_large_fire_id']);
        } else {
            echo $index->formLargeFires();
        }
        break;
    case "save-large-fires":
        $args = blah_decode($_POST['args']);
        echo $index->saveLargeFires($args);
        break;
    case "update-large-fires":
        $args = blah_decode($_POST['args']);
        echo $index->updateLargeFires($_POST['gbcc_large_fire_id'], $args);
        break;
    default:
        echo status_message("The home page manager action was not found.", "error");
        break;
}
