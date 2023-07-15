 select
   Invoices.*,
	Customers.Name as Customer_Name
 from 
    Invoices
 left join
    Customers on Customers.CustomerId = Invoices.CustomerId