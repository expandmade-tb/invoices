<?php

namespace database;

/**
 * DDL creator class
 * Version 1.0.1
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

class DbDDL {
    private static ?DbDDL $instance = null;
    private string $table;
    private string $primary_key;
    private array $fields;
    private array $foreign_keys;
    private array $unique;

    protected function __construct(string $table) {
        $this->table = $table;
    }

    public static function table (string $table) : DbDDL {
        self::$instance = new DbDDL($table);
        return self::$instance;
    }
  
    public function integer(string $name,  bool $not_null=false, bool $auto_increment=false, bool $unique=false) : DbDDL {
        $this->fields[$name] = ['type'=>'integer', 'not_null'=>$not_null, 'auto_increment'=>$auto_increment, 'unique'=>$unique];
        return $this;
    }

    public function text(string $name, int $size=255, bool $not_null=false, bool $unique=false) : DbDDL {
        $this->fields[$name] = ['type'=>'text', 'size'=> $size,'not_null'=>$not_null, 'auto_increment'=>false, 'unique'=>$unique];
        return $this;
    }

    public function real(string $name, bool $not_null=false) : DbDDL {
        $this->fields[$name] = ['type'=>'real', 'not_null'=>$not_null, 'auto_increment'=>false, 'unique'=>false];
        return $this;
    }

    public function blob(string $name, bool $not_null=false) : DbDDL {
        $this->fields[$name] = ['type'=>'blob', 'not_null'=>$not_null, 'auto_increment'=>false, 'unique'=>false];
        return $this;
    }

    public function unique(string $fields) : DbDDL {
        $this->unique[] = $fields;
        return $this;
    }

    public function primary_key(string $fields) : DbDDL {
        $this->primary_key = $fields;
        return $this;
    }

    public function foreign_key(string $fields, string $parent_table, string|array $primary_key) : DbDDL {
        $this->foreign_keys[$fields] = ['parent_table'=>$parent_table, 'primary_key'=>$primary_key];
        return $this;
    }

    public function createFlat() : string {
        $sql = '';

        foreach ($this->fields as $field => $values) {
            $type = strtoupper($values['type']);

            if ( !empty($this->primary_key && $this->primary_key == $field) )
                $sql .= "{$field} {$type} PRIMARY_KEY, ";
            else
                $sql .= "{$field} {$type}, ";
        }

        $sql = substr($sql, 0, -2).')';
        return $sql;
    }

    public function createMSQ() : string {
        $sql = "create table $this->table (";

        foreach ($this->fields as $field => $values) {
            switch ($values['type']) {
                case 'integer':
                    $type='INT';
                    break;
                case 'text':
                    $size = $values['size'];
                    $type="VARCHAR($size)";
                    break;
                case 'real':
                    $type='FLOAT';
                    break;
                case 'blob':
                    $type='BLOB';
                    break;
                default:
                    $type='INT';
                    break;
            }

            $not_null = $values['not_null'] === true ? ' NOT NULL' : '';

            if ( $values['auto_increment'] === true ) {
                $auto_increment = ' AUTO_INCREMENT';
                $this->primary_key($field);
            }
            else
                $auto_increment = '';

            if ( $values['unique'] === true )
                $this->unique($field);

            $sql .= "{$field} {$type}{$not_null}{$auto_increment}, ";
        }

        if ( !empty($this->primary_key) )
            $sql .= "PRIMARY KEY($this->primary_key), ";

        if ( !empty($this->unique) )
            foreach ($this->unique as $key => $value)
                $sql .= "UNIQUE($value), ";

        if ( !empty($this->foreign_keys) )
            foreach ($this->foreign_keys as $key => $value) {
                $parent_table = $value['parent_table'];
                $parent_pk = $value['primary_key'];
                $sql .= "FOREIGN KEY($key) REFERENCES $parent_table ($parent_pk), ";
            }
            
        $sql = substr($sql, 0, -2).')';
        return $sql;
    }

    public function createSQ3() : string {
        $sql = "create table $this->table (";

        foreach ($this->fields as $field => $values) {
            $type = strtoupper($values['type']);
            $not_null = $values['not_null'] === true ? ' NOT NULL' : '';
            $auto_increment = $values['auto_increment'] === true ? ' PRIMARY KEY AUTOINCREMENT' : '';
            $unique = $values['unique'] === true && empty($auto_increment) ? ' UNIQUE' : '';
            $sql .= "{$field} {$type}{$not_null}{$auto_increment}{$unique}, ";
        }

        if ( !empty($this->primary_key) )
            $sql .= "PRIMARY KEY($this->primary_key), ";

        if ( !empty($this->unique) )
            foreach ($this->unique as $key => $value)
                $sql .= "UNIQUE($value), ";

        if ( !empty($this->foreign_keys) )
            foreach ($this->foreign_keys as $key => $value) {
                $parent_table = $value['parent_table'];
                $parent_pk = $value['primary_key'];
                $sql .= "FOREIGN KEY($key) REFERENCES $parent_table ($parent_pk), ";
            }
            
        $sql = substr($sql, 0, -2).')';
        return $sql;
    }

}