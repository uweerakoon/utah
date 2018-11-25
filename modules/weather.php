<?php

namespace Page;

class Weather
{
    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
    }

    public function display()
    {
        /**
         *  Gets the most recent weather entry (entries)
         */

        // Date range specs
        $tomorrow = date('Y-m-d', time() + 86400);
        $min_date = date('Y-m-d', time() - 2*86400);
        $today = date('Y-m-d');

        $html = "<h3>Weather <small>Today is $today</small></h3>
            <hr>
            ";

        $sql = "SELECT `date` as \"Date\", `html` as \"Weather\" FROM `weather` WHERE `date` BETWEEN '$min_date' AND '$tomorrow' ORDER BY `date` DESC";

        $table = show(array('sql'=>$sql,'no_results_message'=>'There are no current weather summaries.','no_results_class'=>'info'));

        $html .= $table['html'];

        $html .= "<br>";

        return $html;
    }
}
