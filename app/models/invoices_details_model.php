<?php

namespace models;

use database\DBTable;
use database\DbDDL;

class invoices_details_model extends DBTable {
    protected string $name = 'InvoicesDetails';

    public function DDL() : DbDDL {
		return DbDDL::table($this->name)
			->integer('InvoiceDetailId', true, true)
			->integer('Qty', true)
			->real('Price')
			->integer('InvoiceId', true)
			->integer('ProductId', true)
			->foreign_key('InvoiceId', 'Invoices', 'InvoiceId')
			->foreign_key('ProductId', 'Products', 'ProductId');
    }
}