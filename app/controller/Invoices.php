<?php

namespace controller;

use classes\GenerateInvoice;
use dbgrid\DbCrud;
use models\invoices_model;
use helper\Helper;

class Invoices extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();

        $this->crud = new DbCrud(new invoices_model());
        $this->crud->grid_show = '<a class="btn btn-success btn-sm" href="[:script_name]/print/[:identifier]" role="button"><i class="bi bi-file-pdf"></i> Print</a>';
        $this->crud->grid_delete = '';
        $this->crud->limit = 15;
        $this->crud->gridSQL( $this->crud->model()->getSQL('invoices-crud') );
        $this->crud->gridFields('InvoiceId,Customer_Name,Invoice_Date,Due_Date,Payed_Date,Billing_Name');
        $this->crud->addFields('CustomerId,Invoice_Date,Due_Date,Currency,Tax,Billing_Name,Billing_Adress,Billing_Email,Instructions');
        $this->crud->searchFields('Customer_Name,Invoice_Date,Due_Date,Payed_Date');
        $this->crud->editFields('InvoiceId,Canceled,CustomerId,Invoice_Date,Due_Date,Payed_Date,Printed,Currency,Tax,Billing_Name,Billing_Adress,Billing_Email,Instructions');
        $this->crud->fieldTitles('InvoiceId,CustomerId,Customer_Name','Invoice No,Customer,Customer');
        $this->crud->readonlyFields('Printed');

        $this->crud->fieldType('Invoice_Date', 'datetext');
        $this->crud->fieldType('Due_Date', 'datetext');
        $this->crud->fieldType('Payed_Date', 'datetext');
        $this->crud->fieldType('Printed', 'datetext');
        $this->crud->fieldType('Billing_Adress', 'textarea', '', 4);
        $this->crud->fieldType('Canceled', 'checkbox');

        $this->crud->fieldPlaceholder('Billing_Name', 'leave blank to use customers name');
        $this->crud->fieldPlaceholder('Billing_Adress', 'leave blank to use customers adress');
        $this->crud->fieldPlaceholder('Billing_Email', 'leave blank to use customers email');
        $this->crud->fieldPlaceholder('Instructions', 'instructions to appear on the invoice');

        $this->crud->setRule('Billing_Email','email');
        
        $this->crud->fieldValue('Invoice_Date', time());
        
        $value = Helper::transient('Currency');
        $this->crud->fieldValue('Currency', $value !== false ? $value : 'USD');
        
        $value = Helper::transient('Tax');
        $this->crud->fieldValue('Tax', $value !== false ? $value : 0);
        
        $this->crud->setRelation('Currency', 'Currency', 'Currencies');
        $this->crud->setRelation('CustomerId', 'Name', 'Customers');
        $this->crud->setContstraints('InvoiceId', 'InvoicesDetails', 'InvoiceId');
        $this->crud->linkedTable('InvoicesDetails', 'Invoice Items', 'selectinvoice');
    }

    public function index () : void {
       $this->grid(1);
    }

    public function add() : void {
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud');
    }

    public function edit(string $id) : void {
        $invoice = (object) $this->crud->model();

        if ( $invoice->printed($id) )
            $this->crud->readonlyFields('InvoiceId,CustomerId,Invoice_Date,Due_Date,Payed_Date,Currency,Tax,Billing_Name,Billing_Adress,Billing_Email,Instructions,Printed');

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

    public function print(int $id) : void {
        $invoice = (object) $this->crud->model();

        if ( ! $invoice->printed($id) ) {
            $file = GenerateInvoice::generate($id);

            if ( $file === false )
                exit();
 
            $invoice->update($id, ['Printed'=>time(), 'Invoice_PDF'=>$file]);
        }
        else {
            $result = $invoice->find($id);

            if ($result === false)
                exit();

            $file = $result['Invoice_PDF'];
        }
            
        try {
            header("Content-type: application/pdf");
            header("Content-Length: " . filesize($file));
            header('Content-Disposition: attachment; filename="' . "invoice$id.pdf" . '"');
            header('Cache-Control: private');
            
            readfile($file);
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        exit();
   }
}