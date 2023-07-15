select 
	Invoices.InvoiceId,
	Invoices.Invoice_Date,
	Invoices.Due_Date,
	Invoices.Payed_Date,
	Invoices.Currency,
	Invoices.Tax,
	Invoices.CustomerId,
	Customers.*,
	ifnull(nullif(Invoices.Billing_Name,''), Customers.Name) as Billing_Name,
	ifnull(nullif(Invoices.Billing_Adress,''), Customers.Adress) as Billing_Adress,
	ifnull(nullif(Invoices.Billing_Email,''), Customers.Email) as Billing_Email
FROM
	Invoices
LEFT JOIN
	Customers on Customers.CustomerId = Invoices.CustomerId
WHERE
	InvoiceId = ?