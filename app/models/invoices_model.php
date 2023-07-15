<?php

namespace models;

use database\DBTable;
use database\DbDDL;

class invoices_model extends DBTable {
    protected string $name = 'Invoices';

    public function DDL() : DbDDL {
		return DbDDL::table($this->name)
			->integer('InvoiceId', true, true)
			->integer('Invoice_Date', true)
			->integer('Due_Date')
			->integer('Payed_Date')
			->text('Currency', true)
			->real('Tax')
			->integer('CustomerId', true)
			->text('Billing_Name')
			->text('Billing_Adress', 512)
			->text('Billing_Email')
			->text('Instructions')
			->text('Invoice_PDF')
			->foreign_key('Currency', 'Currencies', 'Currency')
			->foreign_key('CustomerId', 'Customers', 'CustomerId');
    }
}