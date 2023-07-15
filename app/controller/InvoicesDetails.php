<?php

namespace controller;

use dbgrid\DbCrud;
use helper\Session;
use models\invoices_details_model;
use models\products_model;
use Router\Router;

class InvoicesDetails extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();
        $this->data['js_files'][] = JAVASCRIPT.'/livesearch.js';
       
        $this->crud = new DbCrud(new invoices_details_model());
        $this->crud->grid_show = '';
        $this->crud->grid_search = '';
        $this->crud->form_delete = '';
        $this->crud->limit = 15;
        $this->crud->callbackInsert([$this, 'onInsert']);
        $this->crud->fields('ProductId,Qty,Price');
        $this->crud->fieldValue('Qty', 1);
        $this->crud->setSearchRelation('ProductId', 'Products', 'Item');
        $this->crud->fieldTitles('ProductId','Product');
        $this->crud->fieldPlaceholder('Price', 'leave blank to accept original product price');
    }

    private function filter() : void {
        $invoice_id = Session::instance()->get('invoicedetails', -1);
        $this->crud->gridSQL( $this->crud->model()->getSQL('invoicedetails-crud-filter'), [$invoice_id]);
        $url = Router::instance()->url();
        $this->crud->grid_title = "Items for <a href=\"$url/invoices/edit/$invoice_id\">Invoice no. $invoice_id</a>"; // gets back to the invoice
    }
    
    public function onInsert(array $data) : void {
        $invoice_id = Session::instance()->get('invoicedetails', -1);

        if ( empty($data['Price']) ) {
            $products = new products_model();
            $result = $products->find($data['ProductId']??'');
            $data['Price'] = $result['Price'];
        }

        $data['InvoiceId'] = $invoice_id;
        $this->crud->model()->insert($data);
    }

    public function index () : void {
        $this->filter();
        $this->grid(1);
    }

    public function selectinvoice(int $invoice_id) : void {
        Session::instance()->set('invoicedetails', $invoice_id);
        $this->index();
    }

    public function add() : void {
        $this->filter();
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud');
    }

    public function edit(string $id) : void {
        $this->filter();
        $this->data['dbgrid'] = $this->crud->form('edit', $id);
        $this->view('Crud');
    }

    public function delete(string $id) : void {
        $result = $this->crud->delete($id);
        $this->filter();

        if ( $result === false )
            $this->data['dbgrid'] = $this->crud->grid();
        else
            $this->data['dbgrid'] = $this->crud->grid($result);
        
        $this->view('Crud');
    }

    public function grid(int $page) : void {
        $this->filter();
        $this->data['dbgrid'] = $this->crud->grid($page);
        $this->view('Crud');
    }
}