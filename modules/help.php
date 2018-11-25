<?php
namespace Info;

class Help
{

    private $help_id;
    private $info_icon = "glyphicon glyphicon-info-sign";
    private $info_tooltip_title = "Help";

    public function __construct(\Info\db $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->get_connection();
    }

    public function showInfo($field_id)
    {
        /**
         *  Constructs an icon based hover/popover help info.
         *  For use with input labels.
         */

        $value = $this->getField($field_id);
        
        $title = $value['title'];
        $body = $value['body'];

        if (isset($value['body'])) {
            $html = "<div class=\"help\">
                <i data-toggle=\"popover\" data-container=\"body\" data-title=\"$title\" data-content=\"$body\" style=\"cursor:pointer;\" class=\"".$this->info_icon."\"></i> 
            </div>";
        } else {
            $html = "";
        }
        
        return $html;
    }

    public function showMessage($field_id)
    {
        /**
         *  Constructs a visible help message for use with input labels.
         */

        $value = $this->getField($field_id);

        $title = $value['title'];
        $body = $value['body'];

        if (isset($value['body'])) {
            $html = "<div class=\"help\">
                <i data-toggle=\"tooltip\" data-title=\"".$this->info_tooltip_title."\" class=\"".$this->info_icon."\"></i> 
                <span>".$body."</span>
            </div>";
        } else {
            $html = "";
        }

        return $html;
    }

    public function showPage($help_id)
    {
        /**
         *  Constructs a full help page table, per help group.
         */

        $html = "";

        return $html;
    }

    public function pageHelpTable()
    {
        /**
         *  Gets all page help as a table.
         */

        $help_sql = "SELECT help_id, title as \"Help Title\", body as \"Content\", CONCAT('<a href=\"/', p.page_url , '\">', p.page_url, '</a>') as \"Associated Page\"
        FROM help h
        LEFT JOIN pages p ON (h.page_id = p.page_id)
        ORDER BY title;";
    
        $table = show(array('sql'=>$help_sql,'table'=>'help','pkey'=>'help_id','include_new'=>true,'include_delete'=>true,'paginate'=>true,'table_class'=>'table table-condensed'));

        $html = $table['html'];

        return $html;
    }

    public function fieldHelpTable()
    {
        /**
         *  Gets all page help as a table.
         */

        $help_sql = "SELECT fields_help_id, f.title as \"Help Title\", f.body as \"Content\", h.title as \"Page Group\", CONCAT('<a href=\"/', p.page_url , '\">', p.page_url, '</a>') as \"Associated Page\"
        FROM fields_help f
        LEFT JOIN help h ON(f.help_id = h.help_id)
        LEFT JOIN pages p ON (h.page_id = p.page_id)
        ORDER BY h.title, f.title;";
    
        $table = show(array('sql'=>$help_sql,'table'=>'fields_help','pkey'=>'fields_help_id','include_new'=>true,'include_delete'=>true,'paginate'=>true,'table_class'=>'table table-condensed'));

        $html = $table['html'];

        return $html;
    }
    
    public function tableTable()
    {
        /**
         *  Gets all fields as a table.
         *  For advanced only.
         */

        $help_sql = "SELECT db_table_id, db_table as \"Table\", pcol as \"Primary Key\"
        FROM db_tables
        ORDER BY db_table;";
    
        $table = show(array('sql'=>$help_sql,'table'=>'db_tables','pkey'=>'db_table_id','include_new'=>true,'include_delete'=>true,'paginate'=>true,'table_class'=>'table table-condensed'));

        $html = $table['html'];

        return $html;
    }

    public function fieldTable()
    {
        /**
         *  Gets all fields as a table.
         *  For advanced only.
         */

        $help_sql = "SELECT f.field_id, t.db_table as \"Table\", f.column as \"Column\", d.name as \"Type\", IF(f.primary_key, \"True\", \"False\") as \"Primary Key\",
        IF(f.allow_null, \"True\", \"False\") as \"Allow Null\", IF(f.multiselect, \"True\", \"False\") as \"Multiselect\"
        FROM fields f
        LEFT JOIN datatypes d ON(f.datatype = d.datatype_id)
        LEFT JOIN db_tables t ON(f.table_id = t.db_table_id)
        ORDER BY t.db_table, f.column;";
    
        $table = show(array('sql'=>$help_sql,'table'=>'fields','pkey'=>'field_id','include_new'=>true,'include_delete'=>true,'paginate'=>true,'table_class'=>'table table-condensed'));

        $html = $table['html'];

        return $html;
    }

    protected function getField($field_id)
    {
        /**
         *  Gets a field help array.
         */

        $array = fetch_assoc(
            "
            SELECT h.fields_help_id, h.help_id, h.field_id, h.reference_field_id, h.title, h.body 
            FROM fields_help h 
            JOIN fields f ON(h.field_id = f.field_id) 
            WHERE h.field_id = $field_id;
            "
        );

        if ($array[0]['reference_field_id'] > 0) {
            $array = fetch_assoc(
                "
                SELECT h.fields_help_id, h.help_id, h.field_id, h.reference_field_id, h.title, h.body 
                FROM fields_help h 
                JOIN fields f ON(h.field_id = f.field_id) 
                WHERE h.field_id = ".$array[0]['reference_field_id'].";
                "
            );
        }

        return $array[0];
    }

    protected function getFieldId($column, $table_id)
    {
        /**
         *  Gets a field_id.
         */

        $field_id = fetch_one(
            "
            SELECT field_id
            FROM fields f 
            WHERE `column` = ?
            AND `table_id` = ?;
            ", array($column, $table_id)
        );

        return $field_id;        
    }

    protected function getPage($help_id)
    {
        /**
         *  Gets a page help array.
         */

        // Get the page help.
        $page_array = fetch_assoc("SELECT * FROM help WHERE help_id = $help_id;");
        $array['page'] = $page_array[0];

        // Get all associated fields help.
        $fields_array = fetch_assoc("SELECT * FROM fields_help WHERE help_id = $help_id;");
        $array['fields'] = $fields_array;

        return $array;
    }
}
