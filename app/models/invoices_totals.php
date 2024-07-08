<?php

namespace models;

use database\DBView;

class invoices_totals extends DBView {
    protected string $name = 'Invoices_Totals';

    protected string $create_stmt = "CREATE VIEW Invoices_Totals as
        select 
            Invoices.*,
			InvoicesDetails_Totals.Total,
            cast(substr(Date(Invoice_Date, 'unixepoch'), 1, 4) as integer) as Year,
            cast(substr(Date(Invoice_Date, 'unixepoch'), 6, 2) as integer) as Month,
            cast(substr(Date(Invoice_Date, 'unixepoch'), 9, 2) as integer) as Day
        from
            Invoices
        left join 
            InvoicesDetails_Totals on Invoices.InvoiceId = InvoicesDetails_Totals.InvoiceId
    ";
}