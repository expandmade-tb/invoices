<?php

namespace models;

use database\DBTable;
use database\DbDDL;

class currencies_model extends DBTable {
    protected string $name = 'Currencies';

    public function DDL() : DbDDL {
		return DbDDL::table($this->name)
			->text('Currency', 3, true)
			->text('Description')
			->primary_key('Currency');
    }
}