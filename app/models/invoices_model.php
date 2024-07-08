<?php

namespace models;

use database\DBTable;
use database\DbDDL;
use helper\Helper;

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
			->integer("Printed")
			->integer("Canceled")
			->foreign_key('Currency', 'Currencies', 'Currency')
			->foreign_key('CustomerId', 'Customers', 'CustomerId');
    }

	public function printed(int $id) : bool {
        if ( Helper::transient('print_constraint') != 'true')
			return false;

		$result = $this->find($id);

		if ( $result === false)
			return false;

		if ( empty($result['Printed']))
			return false;
		else
			return true;
	}
}