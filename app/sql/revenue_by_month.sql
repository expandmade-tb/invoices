select
	Year,
	Month,
	Sum(Total) as Total
from
	Invoices_Totals
where
	Year >= ? and Year <= ? and
	Month >= ? and Month <= ?
group by
	Year, Month
order by
	Year, Month