<?php

namespace controller;

use database\DBTable;
use Formbuilder\StatelessCSRF;
use helper\helper;

class livesearch extends BaseController {

    private function validate_token(string $token) : bool {
        $csrf_generator = new StatelessCSRF(helper::env('app_secret', 'empty_secret'));
        $csrf_generator->setGlueData('ip', $_SERVER['REMOTE_ADDR']);
        $csrf_generator->setGlueData('user-agent', $_SERVER['HTTP_USER_AGENT']);            
        $result = $csrf_generator->validate(helper::env('app_identifier','empty_identifier'), $token, time());
        return $result;
    }

    private function filter ($input) {
        // check if ajax is used
        if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') 
            return false;

        // check if correct token is send
        if ( empty($_SERVER["HTTP_AJAX_REQUEST_TOKEN"]) )
            return false;

        if ( $this->validate_token($_SERVER["HTTP_AJAX_REQUEST_TOKEN"]) === false )
            return false;

        return $input;
    }

    public function index() {
        echo "nothing here";
    }

    public function Customers($input) {
        if ( $this->filter($input) === false ) {
            echo 'doing it wrong';
            return;           
        }

        $table = new DBTable('Customers');
        $results = $table->where('Name',$input.'%', 'like')->limit(6)->findColumn('Name');

        foreach ($results as $key => $value) 
            echo '<li class="dropdown-item" onclick="livesearchSelect(this);">'.$value.'</li>';
    }

    public function Products($input) {
        if ( $this->filter($input) === false ) {
            echo 'doing it wrong';
            return;           
        }

        $table = new DBTable('Products');
        $results = $table->where('Item',$input.'%', 'like')->limit(10)->findColumn('Item');

        foreach ($results as $key => $value) 
            echo '<li class="dropdown-item" onclick="livesearchSelect(this);">'.$value.'</li>';
    }

    public function ProductByItem() {
        if ( $this->filter('') === false ) {
            echo 'doing it wrong';
            return;           
        }

        $item = $_GET['item']??'';

        if (empty($item) )
            return;

        $table = new DBTable('Products');
        $result = $table->where('Item', $item)->findFirst();
        echo $result['Price']??'';
    }
}