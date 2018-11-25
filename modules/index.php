<?php

namespace Page;

class Index
{
    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
    }

    public function form($index_id)
    {
        if (isset($index_id)) {
            $index = fetch_row("SELECT `date`, `html` FROM `index` WHERE `index_id` = $index_id;");
            $onclick = "HomeManager.update($index_id)";
            extract($index);
        } else {
            $onclick = "HomeManager.submit()";
        }

        $ctls = array(
            'date'=>array('type'=>'date','label'=>'Display Date','value'=>$date),
            'comment'=>array('type'=>'rich_memo','label'=>'Content','value'=>$html)
        );

        $html = mkForm(array('controls'=>$ctls,'onclick'=>$onclick,'id'=>'home_form','cancel'=>true));

        return $html;
    }

    public function save($values)
    {
        extract($values);

        $added_by = $_SESSION['user']['id'];
        $added_on = now();

        if (isset($tinymce)) {
            $comment = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `index` WHERE `date` = '$date';");

        if ($exists == $date) {
            return status_message("A home page content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("INSERT INTO `index` (`added_by`, `added_on`, `date`, `html`) VALUES (?, ?, ?, ?)");
            $index_sql->execute(array($added_by, $added_on, $date, $comment));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The home page was successfully added.", "success");
            } else {
                $result = status_message("The home page was not succesfully added.", "error");
            }
        }

        return $result;
    }

    public function update($index_id, $values)
    {
        extract($values);

        $updated_by = $_SESSION['user']['id'];

        if (isset($tinymce)) {
            $comment = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `index` WHERE `date` = '$date' AND `index_id` <> $index_id;");

        if ($exists == $date) {
            return status_message("A different home page content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("UPDATE `index` SET `updated_by` = ?, `date` = ?, `html` = ? WHERE `index_id` = ?");
            $index_sql->execute(array($updated_by, $date, $comment, $index_id));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The home page was successfully updated.", "success");
            } else {
                $result = status_message("The home page was not succesfully updated.", "error");
            }
        }

        return $result;
    }

    public function getCurrent()
    {
        /**
         *  Display today's index body.
         */

        return $html;
    }

    public function display()
    {
        /**
         *  Display today's index body.
         */

        $date = date('Y-m-d');

        $data = fetch_row("SELECT date, html FROM `index` WHERE `date` = '$date'");

        if (empty($data['html'])) {
            $html = modal_message("There are no current news messages from Utah.gov for $date.", "info");
        } else {
            $html = "<div class=\"col-sm-12\">
                        {$data['html']}
                        <br>
                        <span class=\"pull-right label label-default\">For Today: {$data['date']}</span>
                    </div>";
        }

        return $html;
    }

    public function formLevels($index_level_id)
    {
        if (isset($index_level_id)) {
            $index = fetch_row("SELECT `date`, `egbc_level`, `national_level` FROM `index_levels` WHERE `index_level_id` = $index_level_id;");
            $onclick = "HomeManager.updateLevels($index_level_id)";
            extract($index);
        } else {
            $onclick = "HomeManager.submitLevels()";
        }

        $ctls = array(
            'date'=>array('type'=>'date','label'=>'Display Date','value'=>$date),
            'egbc_level'=>array('type'=>'text','label'=>'EGBC Preparedness Level','value'=>$egbc_level),
            'national_level'=>array('type'=>'text','label'=>'National Preparedness Level','value'=>$national_level),
        );

        $html = mkForm(array('controls'=>$ctls,'onclick'=>$onclick,'id'=>'home_form','cancel'=>true));

        return $html;
    }

    public function saveLevels($values)
    {
        extract($values);

        $added_by = $_SESSION['user']['id'];
        $added_on = now();

        $exists = fetch_one("SELECT `date` FROM `index_levels` WHERE `date` = '$date';");

        if ($exists == $date) {
            return status_message("A home page content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("INSERT INTO `index_levels` (`added_by`, `added_on`, `date`, `egbc_level`, `national_level`) VALUES (?, ?, ?, ?, ?)");
            $index_sql->execute(array($added_by, $added_on, $date, $egbc_level, $national_level));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The home page was successfully added.", "success");
            } else {
                $result = status_message("The home page was not succesfully added.", "error");
            }
        }

        return $result;
    }

    public function updateLevels($index_level_id, $values)
    {
        extract($values);

        $updated_by = $_SESSION['user']['id'];

        $exists = fetch_one("SELECT `date` FROM `index_levels` WHERE `date` = '$date' AND `index_level_id` <> $index_level_id;");

        if ($exists == $date) {
            return status_message("A different home page content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("UPDATE `index` SET `updated_by` = ?, `date` = ?, `egbc_level` = ?, `national_level` = ? WHERE `index_level_id` = ?");
            $index_sql->execute(array($updated_by, $date, $egbc_level, $national_level, $index_level_id));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The home page was successfully updated.", "success");
            } else {
                $result = status_message("The home page was not succesfully updated.", "error");
            }
        }

        return $result;
    }

    public function formTeamFires($gbcc_team_fire_id)
    {
        if (isset($gbcc_team_fire_id)) {
            $index = fetch_row("SELECT `date`, `html` FROM `gbcc_team_fires` WHERE `gbcc_team_fire_id` = $gbcc_team_fire_id;");
            $onclick = "HomeManager.updateTeamFires($gbcc_team_fire_id)";
            extract($index);
        } else {
            $onclick = "HomeManager.submitTeamFires()";
        }

        $ctls = array(
            'date'=>array('type'=>'date','label'=>'Display Date','value'=>$date),
            'html'=>array('type'=>'rich_memo','label'=>'Content','value'=>$html)
        );

        $html = mkForm(array('controls'=>$ctls,'onclick'=>$onclick,'id'=>'home_form','cancel'=>true));

        return $html;
    }

    public function saveTeamFires($values)
    {
        extract($values);

        $added_by = $_SESSION['user']['id'];
        $added_on = now();

        if (isset($tinymce)) {
            $html = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `gbcc_team_fires` WHERE `date` = '$date';");

        if ($exists == $date) {
            return status_message("A team fire content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("INSERT INTO `gbcc_team_fires` (`added_by`, `added_on`, `date`, `html`) VALUES (?, ?, ?, ?)");
            $index_sql->execute(array($added_by, $added_on, $date, $html));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The team fire comment was successfully added.", "success");
            } else {
                $result = status_message("The team fire comment was not succesfully added.", "error");
            }
        }

        return $result;
    }

    public function updateTeamFires($gbcc_team_fire_id, $values)
    {
        extract($values);

        $updated_by = $_SESSION['user']['id'];

        if (isset($tinymce)) {
            $html = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `gbcc_team_fires` WHERE `date` = '$date' AND `gbcc_team_fire_id` <> $gbcc_team_fire_id;");

        if ($exists == $date) {
            return status_message("A different fire content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("UPDATE `gbcc_team_fires` SET `updated_by` = ?, `date` = ?, `html` = ? WHERE `gbcc_team_fire_id` = ?");
            $index_sql->execute(array($updated_by, $date, $html, $gbcc_team_fire_id));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The fire comment was successfully updated.", "success");
            } else {
                $result = status_message("The fire comment was not succesfully updated.", "error");
            }
        }

        return $result;
    }

    public function formLargeFires($gbcc_large_fire_id)
    {
        if (isset($gbcc_large_fire_id)) {
            $index = fetch_row("SELECT `date`, `html` FROM `gbcc_large_fires` WHERE `gbcc_large_fire_id` = $gbcc_large_fire_id;");
            $onclick = "HomeManager.updateLargeFires($gbcc_large_fire_id)";
            extract($index);
        } else {
            $onclick = "HomeManager.submitLargeFires()";
        }

        $ctls = array(
            'date'=>array('type'=>'date','label'=>'Display Date','value'=>$date),
            'html'=>array('type'=>'rich_memo','label'=>'Content','value'=>$html)
        );

        $html = mkForm(array('controls'=>$ctls,'onclick'=>$onclick,'id'=>'home_form','cancel'=>true));

        return $html;
    }

    public function saveLargeFires($values)
    {
        extract($values);

        $added_by = $_SESSION['user']['id'];
        $added_on = now();

        if (isset($tinymce)) {
            $html = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `gbcc_large_fires` WHERE `date` = '$date';");

        if ($exists == $date) {
            return status_message("A team fire content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("INSERT INTO `gbcc_large_fires` (`added_by`, `added_on`, `date`, `html`) VALUES (?, ?, ?, ?)");
            $index_sql->execute(array($added_by, $added_on, $date, $html));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The team fire comment was successfully added.", "success");
            } else {
                $result = status_message("The team fire comment was not succesfully added.", "error");
            }
        }

        return $result;
    }

    public function updateLargeFires($gbcc_large_fire_id, $values)
    {
        extract($values);

        $updated_by = $_SESSION['user']['id'];

        if (isset($tinymce)) {
            $html = $tinymce;
        }

        $exists = fetch_one("SELECT `date` FROM `gbcc_large_fires` WHERE `date` = '$date' AND `gbcc_large_fire_id` <> $gbcc_large_fire_id;");

        if ($exists == $date) {
            return status_message("A different fire content block already exists for $date.", "error");
        } else {
            $index_sql = $this->pdo->prepare("UPDATE `gbcc_large_fires` SET `updated_by` = ?, `date` = ?, `html` = ? WHERE `gbcc_large_fire_id` = ?");
            $index_sql->execute(array($updated_by, $date, $html, $gbcc_large_fire_id));
            if ($index_sql->rowCount() > 0) {
                $result = status_message("The fire comment was successfully updated.", "success");
            } else {
                $result = status_message("The fire comment was not succesfully updated.", "error");
            }
        }

        return $result;
    }

    public function displayDaily()
    {
        /**
         *  Display today's daily burns.
         */

        $date = date('Y-m-d');

        $html = "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <h5 class=\"\">Preparedness Levels <small>Today is $date</small></h5>
                </div>
            </div>";

        $levels = fetch_row(
            "SELECT `date`, egbc_level, national_level
            FROM `index_levels`
            WHERE `date` IN(SELECT MAX(`date`) FROM `index_levels` WHERE `date` <= ?)"
        , $date);

        if (!$levels['error']) {
            $html .= "<div class=\"row\">
                    <div class=\"col-sm-6 text-center\">
                        <h4>GBCC</h4>
                        <h1 class=\"inverse-danger\" style=\"height: 80px; padding: 18px; border-radius: 6px;\">{$levels['egbc_level']}</h1>
                    </div>
                    <div class=\"col-sm-6 text-center\">
                        <h4>National</h4>
                        <h1 class=\"inverse-danger\" style=\"height: 80px; padding: 18px; border-radius: 6px;\">{$levels['national_level']}</h1>
                    </div>
                </div>";
        } else {
            $html .= modal_message("There are no preparedness levels for today.","");
        }

        $team_fires = fetch_row(
            "SELECT `date`, `html`
            FROM `gbcc_team_fires`
            WHERE `date` IN(SELECT MAX(`date`) FROM `gbcc_team_fires` WHERE `date` <= ?)"
        , $date);

        if (!$team_fires['error']) {
            $html .= "<hr>
                <div class=\"row\">
                    <div class=\"col-sm-12\">
                        <h5>GBCC Team Fires <small>From: {$team_fires['date']}</small></h5>
                        {$team_fires['html']}
                    </div>
                </div>";
        } else {
            $html .= modal_message("There are no GBCC team fires.","");
        }

        $large_fires = fetch_row(
            "SELECT `date`, `html`
            FROM `gbcc_large_fires`
            WHERE `date` IN(SELECT MAX(`date`) FROM `gbcc_large_fires` WHERE `date` <= ?)"
        , $date);

        if (!$large_fires['error']) {
            $html .= "
                <div class=\"row\">
                    <div class=\"col-sm-12\">
                        <h5>GBCC Large Fires <small>From: {$large_fires['date']}</small></h5>
                        {$large_fires['html']}
                    </div>
                </div>";
        } else {
            $html .= modal_message("There are no GBCC large fires.","");
        }

        return $html;
    }
}
