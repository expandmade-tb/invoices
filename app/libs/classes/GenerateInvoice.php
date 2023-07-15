<?php

namespace classes;

use helper\Helper;
use InvoicePrinter\InvoicePrinter;
use models\invoices_details_model;
use models\invoices_model;

class GenerateInvoice { 
    public static function generate(int $id) : bool|string {
        // get invoice data
        $invoice_model = new invoices_model();
        $invoice_data = $invoice_model->findFirst($invoice_model->getSQL('invoices-filter'), [$id]);

        if ( $invoice_data === false )
            return false;

        // get invoice detail data
        $invoices_details_model = new invoices_details_model();
        $invoice_details_data = $invoices_details_model->findAll($invoices_details_model->getSQL('invoicedetails-filter'), [$id]);

        if ( $invoice_details_data === false )
            return false;

        // create the pdf
        $pdf = new InvoicePrinter(InvoicePrinter::INVOICE_SIZE_A4, $invoice_data['Currency'].' ');
        $img = realpath(BASEPATH.'/public/'.IMAGES.'/logo.jpg');

        $pdf->setLogo($img);
        $pdf->setColor("#007fff");
        $pdf->setType("Invoice");
        $pdf->setReference("INV-$id");
        $pdf->setDate(date('M dS Y', $invoice_data["Invoice_Date"]));   //Billing Date

        if ( !empty($invoice_data["Due_Date"]) )
            $pdf->setDue(date('M dS Y', $invoice_data["Due_Date"]));    // Due Date

        // setting the billing from data
        $billing_from = explode('&#13;&#10;', Helper::transient('company_adress'));
        $billing_name = Helper::transient('company_name');
        $pdf->setFrom(array_merge([$billing_name], $billing_from));

        // setting the billing to data
        $billing_name = $invoice_data["Billing_Name"];
        $billing_to = explode('&#13;&#10;', $invoice_data["Billing_Adress"]);
        $pdf->setTo(array_merge([$billing_name], $billing_to));

        // detail items
        $total = 0;
        $total_tax = 0;
        $tax = $invoice_data["Tax"];

        foreach ($invoice_details_data as $key => $value) {
            $pos = $key + 1;
            $item = $value["Item"];
            $description = $value["Description"];
            $qty = $value["Qty"];
            $price = $value["Price"];
            $subtotal = $qty * $price;
            $total =+ $subtotal;

            $pdf->addItem
            (
                $item,              // item
                $description,       // description
                $qty,               // quantitiy
                "",                 // tax
                $price,             // price
                "",                 // discount
                $subtotal           // subtotal
            );
        }

        $pdf->addTotal("Total", $total);

        if ( !empty($tax) ) {
            $total_tax = $total * ($tax / 100);
            $pdf->addTotal("VAT $tax%", $total_tax);
        }

        $pdf->addTotal("Total due", $total + $total_tax, true);

        if ( !empty($invoice_data['Instructions']) )
            $pdf->addParagraph($invoice_data['Instructions']);
  
        if ( !empty(Helper::transient('invoice_footer')) )
            $pdf->setFooternote(Helper::transient('invoice_footer'));

        $name = Helper::env('storage_location')."/invoices/INV$id.pdf";
        $pdf->render($name, 'F'); 
        
        return $name;
        
    }
}