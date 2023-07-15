<?php

namespace controller;

use dbgrid\DbCrud;
use models\products_model;

class Products extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();
        
        $this->crud = new DbCrud(new products_model());
        $this->crud->grid_show = '';
        $this->crud->grid_delete = '';
        $this->crud->limit = 15;

        $this->crud->gridFields('ProductId,Item,Descriptioin,Price');
        $this->crud->addFields('Item,Descriptioin,Price');
        $this->crud->editFields('ProductId,Item,Descriptioin,Price');
        $this->crud->fieldTitles('ProductId','Product No');
        $this->crud->setContstraints('ProductId', 'InvoicesDetails', 'ProductId');
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