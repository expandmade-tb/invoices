<?php

namespace controller;

/**
 * 
 * This controller just initializes the databae tables
 * existing tables wont be touched
 * 
 */

use models\transient_model;
use models\upgrades_model;
use models\users_model;
use models\sessions_model;
use models\currencies_model;
use models\customers_model;
use models\invoices_model;
use models\invoices_details_model;
use models\products_model;

class Init {
    public function index () : void {
        echo "creating models...";
        new transient_model();
        new upgrades_model();
        new users_model();
        new sessions_model();

        $currencies = new currencies_model();

        if ( $currencies->count() === 0 )
            $currencies->insert(['Currency'=>'USD', 'Description'=>'US Dollars']);

        $customers = new customers_model();

        if ( $customers->count() === 0 )
            $customers->insert(
                ['Name'=>'Example Customer Ltd.',
                 'Adress'=>'383 Large Trees Bvld.&#13;&#10;CA 39933 Little Creek',
                 'Email'=>'contact@example.com',
                 'Phone'=>'646 454 339 33',
                ]);

        $products = new products_model();

        if ( $products->count() === 0 )
            $products->insert(
                ['Item'=>'Example Product',
                 'Descriptioin'=>'Description of the example product',
                 'Price'=>'120.00'
                ]);

        new invoices_model();
        new invoices_details_model();        
        echo "<br>creating models succesfull";
    }
}