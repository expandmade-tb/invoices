<?php

/**
 * Home Controller
 * Version 1.0.1
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

namespace controller;

class Home extends BaseController {
    function __construct() {
        parent::__construct();
    }

    public function index () : void {
        $this->view('Home');
    }
}