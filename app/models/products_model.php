<?php

namespace models;

use database\DBTable;
use database\DbDDL;

class products_model extends DBTable {
    protected string $name = 'Products';

    public function DDL() : DbDDL {
		return DbDDL::table($this->name)
			->integer('ProductId', true, true)
			->text('Item', 255, true)
			->text('Descriptioin')
			->real('Price', true);
    }
}