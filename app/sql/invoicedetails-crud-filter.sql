 select
	InvoicesDetails.*,
	Products.Item as Item,
	Products.Descriptioin as Description,
	Products.Price as StdPrice
 from 
    InvoicesDetails
LEFT JOIN 
	Products on Products.ProductId = InvoicesDetails.InvoiceDetailId
 where 
	InvoiceId = ?
 order by
   InvoiceDetailId   