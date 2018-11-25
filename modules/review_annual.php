<?php

namespace Manager;

class AnnualReview extends BurnProject
{
    
    public function __construct(\Info\db $db, $user)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
    }

    public function reviewTable()
    {
        /**
         *  Get all reviewable (Under Review, Revision Requested)
         */

        $year = date('Y');

        $args = array();
        extract(merge_args(func_get_args(), $args));

        $html = "<hr>";

        $sql = "SELECT 
        b.burn_number as \"Burn Number\", 
        CONCAT('<a href=\"/review/burn.php?burn=true&id=', b.burn_plan_id ,'\">' , b.burn_name , '</a>') as \"Burn Name\", 
        b.submitted_on as \"Submitted\", a.agency as \"Agency\", d.district as \"District\", 
        CONCAT('<span class=\"', s.class, '\">', s.name, '</span>') as \"Status\",
        CONCAT('<strong>', r.year, '</strong>') as \"Most Recent Registration\",
        IF(r.year = Year(CURDATE()), '<strong class=\"text-success\">Active</strong>', '<strong>Inactive</strong>') as \"Active for $year\"
        FROM burn_plans b
        JOIN districts d ON(b.district_id = d.district_id)
        JOIN agencies a ON (d.agency_id = a.agency_id)
        JOIN burn_plan_statuses s ON(b.status_id = s.status_id)
        JOIN users u ON (b.submitted_by = u.user_id)
        JOIN (
            SELECT r.*
            FROM annual_registration r
            INNER JOIN
                (SELECT burn_plan_id, MAX(year) as max_year
                FROM annual_registration
                GROUP BY burn_plan_id) br
            ON (r.burn_plan_id = br.burn_plan_id)
            AND r.year = br.max_year
        ) r ON(b.burn_plan_id = r.burn_plan_id)
        WHERE b.status_id > 3
        ORDER BY r.year;";

        $table = show(array('sql'=>$sql,'paginate'=>true,'table_class'=>'table table-micro','sort_column'=>2));
        $html .= $table['html'];
        $this->datatable = $table['datatable'];
        $this->table_id = $table['id'];

        return $html;
    }

    public function sidebar()
    {
     
        $html = "<hr><div style=\"border-bottom: 1px solid #e4e4e4;\">";

        $html .= $this->agencyFilter();

        $html .= $this->yearFilter();

        $html .= $this->activeFilter();

        $html .= "</div>";

        return $html;
    }

    private function agencyFilter()
    {

        $agencies = fetch_assoc_offset("SELECT agency as title, 'inverse' as class FROM agencies ORDER BY agency;");

        $html = label_filter(array('object'=>$this->datatable,'column'=>3,'function_name'=>'FilterAg',
            'wrapper_class'=>'filter_agency','selector'=>'agency','title'=>'Agencies',
            'info_array'=>$agencies));

        return $html;
    }

    private function yearFilter($selected)
    {
        /**
         *  Make the years filter.
         */

        $start_year = 2010;
        $current_year = date('Y') + 5;

        $years = array($current_year);

        for ($i = 1; $i <= ($current_year - $start_year); $i++) {
            $append = $current_year - $i;
            array_push($years, $append);
        }

        $html = label_filter(array('object'=>$this->datatable,'column'=>6,'function_name'=>'FilterYr',
            'wrapper_class'=>'filter_year','selector'=>'year','title'=>'Year',
            'selected'=>array(0),'info_array'=>$years));

        return $html;
    }

    private function activeFilter($selected)
    {
        /**
         *  Make the active filter.
         */

        $filters = array("Active", "Inactive");

        $html = label_filter(array('object'=>$this->datatable,'column'=>7,'function_name'=>'FilterAt',
            'wrapper_class'=>'filter_act','selector'=>'act','title'=>'Year','info_array'=>$filters));

        return $html;
    }
}
