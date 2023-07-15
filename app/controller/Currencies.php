<?php

namespace controller;

use dbgrid\DbCrud;
use Exception;
use models\currencies_model;

class Currencies extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();
        
        $this->crud = new DbCrud(new currencies_model());
        $this->crud->grid_show = '';
        $this->crud->grid_delete = '';
        $this->crud->limit = 15;
        $this->crud->callbackInsert([$this, 'onInsert']);
        $this->crud->setContstraints('Currency', 'Invoices', 'Currency');
    }

    public function OnInsert(array $data) : void {
        $id = strtoupper($data['Currency']);
        $result = $this->crud->model()->find($id);

        if ( $result !== false )
            throw new Exception("Currency alread in use !");
        
        $data['Currency'] = $id;
        $result = $this->crud->model()->insert($data);
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