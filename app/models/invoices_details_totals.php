<?php

namespace models;

use database\DBView;

class invoices_details_totals extends DBView {
    protected string $name = 'InvoicesDetails_Totals';

    protected string $create_stmt = 'CREATE VIEW InvoicesDetails_Totals as
        select
            InvoiceId,
            sum(Qty * Price) as Total
        from
            InvoicesDetails
        group by
            InvoiceId
    ';
}