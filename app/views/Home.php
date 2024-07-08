<?php include 'header.php';?>
<script src="/js/ChartJS.min.js"></script>

<!-- Page content-->
<?php echo $revenue_chart ?>
<br>
<h5 style="margin-left: 20px;"> <?php echo $open_invoices_count ?> unpaid invoices</h5>
<h5 style="margin-left: 20px;"> <?php echo $open_invoices_amt ?> total amount of unpaid invoices</h5>

<?php include 'footer.php';?>