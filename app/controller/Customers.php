<?php

namespace controller;

use dbgrid\DbCrud;
use models\customers_model;

class Customers extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();
        
        $this->crud = new DbCrud(new customers_model());
        $this->crud->grid_show = '';
        $this->crud->grid_delete = '';
        $this->crud->limit = 15;

        $this->crud->gridFields('CustomerId,Name,Email,Phone');
        $this->crud->addFields('Name,Adress,Email,Phone');
        $this->crud->editFields('CustomerId,Name,Adress,Email,Phone');
        $this->crud->searchFields('Name,Email');
        $this->crud->fieldTitles('CustomerId','Customer No');
        $this->crud->fieldType('Adress', 'textarea', '', 4);
        $this->crud->setContstraints('CustomerId', 'Invoices', 'CustomerId');
    }

    public function index () : void {
       $this->grid(1);
    }

    public function add() : void {
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud');
    }

    public function edit(string $id) : void {
        $this->data['dbgrid'] = $this->crud->form('edit', $id);
        $this->view('Crud');
    }

    public function delete(string $id) : void {
        $result = $this->crud->delete($id);

        if ( $result === false )
            $this->data['dbgrid'] = $this->crud->grid();
        else
            $this->data['dbgrid'] = $this->crud->grid($result);
        
        $this->view('Crud');
    }

    public function grid(int $page) : void {
        $this->data['dbgrid'] = $this->crud->grid($page);
        $this->view('Crud');
    }

    public function clear() : void {
        $this->crud->clear();
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud');
    }

    public function show($id) : void {
        $this->data['dbgrid'] = $this->crud->form('show', $id);
        $this->view('Crud');
    }
}