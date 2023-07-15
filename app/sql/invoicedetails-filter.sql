SELECT
	InvoicesDetails.*,
	Products.Item as Item,
	Products.Descriptioin as Description,
	Products.Price as StdPrice
FROM
	InvoicesDetails
LEFT JOIN 
	Products on Products.ProductId = InvoicesDetails.InvoiceDetailId
WHERE
	InvoiceId = ?