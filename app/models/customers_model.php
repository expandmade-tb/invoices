<?php

namespace models;

use database\DBTable;
use database\DbDDL;

class customers_model extends DBTable {
    protected string $name = 'Customers';

    public function DDL() : DbDDL {
		return DbDDL::table($this->name)
			->integer('CustomerId', true, true)
			->text('Name', 128, true)
			->text('Adress', 512, true)
			->text('Email')
			->text('Phone');
    }
}