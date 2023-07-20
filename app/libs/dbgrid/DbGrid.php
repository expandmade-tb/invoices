<?php

/**
 * Grid for database tables
 * Version 1.1.1
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

namespace dbgrid;

use database\DBView;
use Exception;
use helper\Helper;
use helper\Session;

class DbGrid {
    public string $grid_search = '<form class="d-flex method="post"> <input class="form-control" type="search" name="search" value="[:search]" placeholder="Search" aria-label="Search"><a href="[:script_name]/clear" style="margin: 0 10px 0 -20px; display: inline-block;" title="Clear Search">x</a> <input type="submit" name="search_submit" value="Search" id="search_submit" class="btn btn-primary"/> </form>';
    public string $grid_title = '';

    public int $limit = 100;                        // sql selection limit for one page
    public string $grid_sql = '';                   // sql from grid selection
    public ?array $grid_sql_params = null;          // params for grid sql
    public string $date_fmt = 'Y-m-d';              // the output format for dates
    public string $time_fmt = 'G:i';                // the output format for time
    public bool $use_sessions = false;              // use session or cookies
    public bool $show_titles = true;

    protected DBView $table;                        // database table we are using
    protected string $echo_data = '';               // data to be returned instead of echo
    protected array $field_titles = [];             // titles / headers for fields
    protected array $field_types = [];              // the field types to deal with
    protected array $grid_fields = [];              // fields to show in grid
    protected array $field_align = [];              // how to align the value
    protected array $search_fields = [];            // searchable fields
    protected array $callback_fields = [];          // fields to callback to format values
    protected string $grid_info = '';
    protected array $uri = [];


    public function __construct(DBView $table) {
        $this->table = $table;
        $this->grid_title = $table->name();
        $this->uri = $this->current_uri();
        $this->fields($this->table->fieldlist());

        // initialze basic datatypes
        foreach (explode(',',$this->table->fieldlist()) as $key => $field ) {
            switch ($this->table->fields($field)['type']) {
                case 'INTEGER':
                    $this->fieldType($field, 'integer');
                    break;
                case 'REAL':
                    $this->fieldType($field, 'numeric');
                    break;
                case 'NUMERIC':
                    $this->fieldType($field, 'numeric');
                    break;
                default:
                    $this->fieldType($field, 'text');
                    break;
           }
        }
    }
    
    public function fields (string $fields) : DbGrid {
        $this->gridFields($fields);
        $this->field_titles = [];
        $this->fieldTitles($fields);
        return $this;
    }

    public function gridFields (string $fields) : DbGrid {
        $this->grid_fields = array_map('trim',explode(',', $fields));
        return $this;
    }

    public function searchFields (string $fields) : DbGrid {
        $this->search_fields = array_map('trim',explode(',', $fields));
        return $this;
    }

    public function fieldTitles (string $fields, string $titles='') : DbGrid {
        $afields = explode(',', $fields);

        if ( empty($titles) ) {
            foreach ($afields as $key => $field) {
                $atitles[$key] = ucwords(str_replace(['_', '-', '.'], ' ', $field));
            }
        }
        else
            $atitles = explode(',', $titles);

        $c1 = count($afields);
        $c2 = count($atitles);
        
        if ( $c1 != $c2 )
            throw new Exception("mismatch of fields($c1) and titles($c2)");

        $this->field_titles = array_merge($this->field_titles, array_combine($afields, $atitles));
        return $this;
    }

    public function fieldType (string $field, string $type, string $valuelist='', int $rows=2, int $cols=40) : DbGrid {
        if ( !in_array($type, ['text', 'integer', 'numeric', 'checkbox', 'select', 'date', 'datetext', 'datetime', 'datalist', 'textarea', 'timetext','grid']) )
            throw new Exception("unsupported field type $type");
            
        if ( in_array($type, ['grid','textarea']) )
            $this->field_types[$field] = ['type'=>$type, 'values'=>$valuelist, 'rows'=>$rows, 'cols'=>$cols];
        else
            $this->field_types[$field] = ['type'=>$type, 'values'=>$valuelist]; 

        return $this;
    }

    public function fieldAlign(string $field, string $align='left') : DbGrid {
        $this->field_align[$field] = $align;
        return $this;
    }

    public function formatField(string $field, callable $callable) : DbGrid {
        $this->callback_fields[$field] = $callable;
        return $this;
    }

    public function gridSQL (string $sql, ?array $params=null ) : DbGrid {
        $this->grid_sql = $sql;
        $this->grid_sql_params = $params;
        return $this;
    }

    public function grid (int $page=1) : string {
        $this->set_session_data('last_page', strval($page));
        $this->show_grid($page);
        return '<div id="dbv-container">'.$this->echo_data.'</div>';
    }

    public function clear() : void {
        $this->remove_session_data('search');
        $to = '/'.$this->uri['class'];
        Helper::redirect("$to/grid/1");
    }

    public function model() : DBView {
        return $this->table;
    }

    public function rowcount () : int {
        return $this->table->count($this->grid_sql, $this->grid_sql_params);
    }

    protected function show_grid(int $page) : void {
        $offset = ($page - 1) * $this->limit;

        // headerbar
        $this->echo_data .= $this->headerbar();
        $this->echo_data .= $this->grid_info;

        $this->gridSearch();
        $total_rows = $this->rowcount();

        $this->gridSearch();
        $data = $this->table->limit($this->limit)->offset($offset)->findAll($this->grid_sql, $this->grid_sql_params);

        $total_pages = intval(ceil( ($total_rows / $this->limit) ));
        $uri = '/'.$this->uri['class'];

        // --> start grid table 
        $this->echo_data .= '<table class="table table-bordered table-hover dbv-table">';

        // --> grid table header titles
        if ( $this->show_titles ) {
            $this->echo_data .= '<thead>';

            foreach ($this->grid_fields as $key => $field) {
                $marker = (in_array($field, $this->search_fields) === true) ? '*' : '';
                $align = $this->field_align[$field]??'';

                if ( empty($align) )
                    $style = '';
                else
                    $style = "style=\"text-align: $align\"";

                $title = $this->field_titles[$field]??'';
                $this->echo_data .= "<th $style>$title$marker</th>";
            }

            $this->echo_data .= '</thead>';
        }

        // <-- grid table header titles

        foreach ($data as $row => $column) {
            // --> grid table rows
            $this->echo_data .= '<tr>';

            foreach ($this->grid_fields as $key => $field) {
                $value = $column[$field];

                switch ($this->field_types[$field]['type']??'') {
                    case 'date':
                    case 'datetext':
                        if ( !empty($value) )
                            $value = date($this->date_fmt, $value);
                            
                        break;
                    case 'timetext':
                        if ( !empty($value) )
                            $value = gmdate($this->time_fmt, $value);
                            
                        break;
                    case 'datetime':
                        if ( !empty($value) )
                            $value = date($this->date_fmt.' '.$this->time_fmt, $value);

                        break;
                    case 'checkbox':
                        // booleans can be stored in db as 0|1, false|true, off|on, -|+, no|yes etc
                        // where array[0] represents false, array[1] represents true
                        $values = explode(',',$this->field_types[$field]['values']);

                        if ( array_search($value, $values) == 1)
                            $checked = 'checked';
                        else
                            $checked = '';
    
                        $value = '<input type="checkbox" class="form-check-input" '.$checked.' readonly >';
                        break;
                }

                if ( isset($this->callback_fields[$field]) )
                    $value = call_user_func($this->callback_fields[$field], 'grid', $value, $column);

                $align = $this->field_align[$field]??'';

                if ( empty($align) )
                    $style = '';
                else
                    $style = "style=\"text-align: $align\"";
    
                $this->echo_data .= "<td $style>$value</td>";
            }

            $this->echo_data .= '</tr>';
            // <-- grid table rows
        }
 
        // <-- end gridtable
        $this->echo_data .= '</table>';

        // footerbar
        $this->echo_data .= $this->footerbar($page, $total_pages);
    }

    protected function current_uri() : array {
        $result = parse_url(urldecode($_SERVER['REQUEST_URI']));
        $path = substr($result["path"]??'', 1);
        $uriSegments = explode("/", $path);
        $class = $uriSegments[0]??'';
        $method = $uriSegments[1]??'';
        $uri = $class.'/'.$method;
        $id = $uriSegments[2]??'';
        $query = $result['query']??'';
        return ['path'=>$path,'uri'=>$uri, 'class'=>$class,'method'=>$method,'id'=>$id,'query'=>$query];
    }

    protected function gridSearch() : bool {
        if ( empty( $this->grid_search) )
            return false;

        if ( empty( $this->search_fields) )
            return false;

        $search = $this->get_session_data('search');
        $value = "%$search%";

        foreach ($this->search_fields as $key => $field) 
            $this->table->where("coalesce($field, '')", $value, 'like', 'or');

        return true;
    }
    
    protected function headerbar() : string {  
        $html = '';

        if ( !empty($this->grid_title) )
            $html .= '<h4 class="dbv-title">'.$this->grid_title.'</h4>';

        if ( empty($this->grid_search) )
            return $html;

        if ( isset ($_REQUEST["search_submit"]) ) {
            $search = $_REQUEST["search"]??'';
            $this->set_session_data('search', $search);
            $to = '/'.$this->uri['class'];
            Helper::redirect("$to/grid/1");
            return '';
        }
        else 
            $search = $this->get_session_data('search');

        $uri = '/'.$this->uri['class'];
        $html .= '<table class="table table-bordered table-hover dbv-headerbar"><tr>';

        if ( !empty($this->grid_search) ) {
            $this->grid_search = str_replace(['[:script_name]','[:search]'], [$uri, $search], $this->grid_search);
            $html .= '<td>'.$this->grid_search.'</td>';
        }
    
        $html .= '</tr></table>';

        return $html;
    }

    protected function footerbar(int $current_page, int $total_pages, int $max_pages=5) :string {
        $html = '<div class="dbv-footerbar">';
        $c = $current_page - 1;

        if ( $c < 1 )
            $c = 1;

        $min = (intdiv($c, $max_pages) * $max_pages) + 1;  
        $max = (intdiv(($c + 5), $max_pages) * $max_pages) + 1;  

        if ( $max > $total_pages )
            $max = $total_pages + 1;

        $uri = '/'.$this->uri['class'];
        $html .= '<nav aria-label="Page navigation"><ul class="pagination">';

        if ( $min > $max_pages ) {
            $page = $min - 1;
            $link = $uri . "/grid/1";
            $html .= '<li class="page-item"><a class="page-link" href="'.$link.'">First</a></li>';
            $link = $uri . "/grid/$page";
            $html .= '<li class="page-item"><a class="page-link" href="'.$link.'">Previous</a></li>&nbsp';
        }

        for ($i=$min; $i < $max; $i++) { 
            $link = $uri . "/grid/$i";

            if ( $i == $current_page )
                $html .= '<li class="page-item active" aria-current="page"><a class="page-link" href="'.$link.'">'.$i.'</a></li> ';
            else 
                $html .= '<li class="page-item"><a class="page-link" href="'.$link.'">'.$i.'</a></li>';
        }

        if ( $max < $total_pages ) {
            $link = $uri . "/grid/$max";
            $html .= '&nbsp<li class="page-item"><a class="page-link" href="'.$link.'">Next</a></li>';
            $link = $uri . "/grid/$total_pages";
            $html .= '<li class="page-item"><a class="page-link" href="'.$link.'">Last</a></li>';
        }

        $html .= '</ul></nav></div>';
        return $html;
    }

    protected function get_session_data(string $key) : string|null {
        $id = md5($this->uri['class'].'-'.$key);

        if ( $this->use_sessions )
            return Session::instance()->get($id);
        else
            return $_COOKIE[$id]??null;
    }

    protected function remove_session_data(string $key) : void {
        $id = md5($this->uri['class'].'-'.$key);

        if ( $this->use_sessions )
            Session::instance()->remove($id);
        else {
            setcookie($id, '', -1, '/');
            unset($_COOKIE[$id]);
        }
    }

    protected function set_session_data(string $key, string $value) : void {
        $id = md5($this->uri['class'].'-'.$key);

        if ( $this->use_sessions )
            Session::instance()->set($id, $value);
        else {
            $options = array (
                'expires' => 0,
                'path' => '/',
                'secure' => true,  
                'httponly' => true,  
                'samesite' => 'Strict'
                );

            setcookie($id, $value, $options);
        }
    }
}