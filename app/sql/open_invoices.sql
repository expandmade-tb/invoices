select 
	count(*) No_Of_Invoices,
	sum(Total) Total_Amount
from 
	Invoices_Totals
where 
	Payed_Date ISNULL