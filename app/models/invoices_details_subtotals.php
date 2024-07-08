<?php

namespace models;

use database\DBView;

class invoices_details_subtotals extends DBView {
    protected string $name = 'InvoicesDetails_Subtotals';

    protected string $create_stmt = 'CREATE VIEW InvoicesDetails_Subtotals as
        select
	        InvoicesDetails.*,
	        (Qty * Price) as SubTotal
        from
	        InvoicesDetails
    ';
}