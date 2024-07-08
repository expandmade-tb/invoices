<?php

/**
 * Home Controller
 * Version 1.0.1
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

namespace controller;

use EasyPHPCharts\Chart;
use models\invoices_totals;

class Home extends BaseController {
    function __construct() {
        parent::__construct();
        $this->html_compress = false;
        $this->data['css_files'][] = STYLESHEET.'/chart.css';
    }

    private function past_revenue() {
        $current_year = date('Y', time());
        $current_month = date('m', time());
        $datestart = strtotime("-3 month", time());
        $start_year = date('Y', $datestart);
        $start_month = date('m', $datestart);
        $dbview = new invoices_totals();
        $data = $dbview->limit(3)->findAll($dbview->getSQL('revenue_by_month'), [$start_year, $current_year, $start_month, $current_month]); 
        $data_array = [];
        $legend_array = [];

        foreach ($data as $key => $value) {
            $data_array[] = $value["Total"]??0;
            $legend_array[] = ($value["Year"]??'') .'/'. ($value["Month"]??'');
        }

        if ( !empty($data) ) {
            $barChart = new Chart('bar', 'revenue');
            $barChart->set('data', $data_array);
            $barChart->set('legend', $legend_array);
            $barChart->set('legendData', ['past 3 month revenue']);
            $barChart->set('displayLegend', true);
            $this->data['revenue_chart'] = $barChart->returnFullHTML();
        }
        else
            $this->data['revenue_chart'] = '';
    }

    private function open_invoices() {
        $dbview = new invoices_totals();
        $data = $dbview->findAll($dbview->getSQL('open_invoices'), []); 
    
        $this->data['open_invoices_count'] = $data[0]["No_Of_Invoices"]??'0';
        $this->data['open_invoices_amt'] = $data[0]["Total_Amount"]??'0';

    }

    public function index () : void {
        $this->past_revenue();
        $this->open_invoices();
        $this->view('Home');
    }

}