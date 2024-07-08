<?php

namespace controller;

use Formbuilder\Formbuilder;
use helper\Helper;

class Settings extends BaseController {

    function __construct() {
        parent::__construct();
    }

    private function get_value(mixed $field, mixed $default=null) : mixed {
        $result = Helper::transient($field);

        if ( $result === false )
            if ( !is_null($default) )
                return $default;
            else
                return '';
        else
            return $result;
    }

    public function index() {
        $form = new Formbuilder('settings-form',['wrapper'=>'bootstrap-h-sm', 'string'=>'enctype="multipart/form-data"']);
        $form->text('company_name', ['value'=>$this->get_value('company_name')]);
        $form->textarea('company_adress', ['value'=>$this->get_value('company_adress'), 'rows'=>4, 'cols'=>40]);
        $form->text('company_email', ['value'=>$this->get_value('company_email')])->rule('email');
        $form->text('invoice_footer', ['value'=>$this->get_value('invoice_footer')]);
        $form->text('default_currency', ['value'=>$this->get_value('default_currency','USD')]);
        $form->number('default_tax', ['value'=>$this->get_value('default_tax', 0), 'min'=>0]);
        $form->checkbox('print_constraint', ['label'=>'Printed invoices cannot be changed', 'checked'=>$this->get_value('print_constraint', 'true') == 'true']);
        $form->html('<br>')->button('submit', '<i class="bi bi-check-circle"></i> save', '', 'submit');
 
        if ( $form->submitted() ) {
            $data = $form->validate('company_name,company_adress,company_email,invoice_footer,default_currency,default_tax,print_constraint');

            if ( $data === false ) // caused by csrf check, honypot or timer check
                $form->message('something went wrong');

            if ( $form->ok() ) {
                Helper::transient('company_name', $data['company_name']);
                Helper::transient('company_adress', $data['company_adress']);
                Helper::transient('company_email', $data['company_email']);
                Helper::transient('invoice_footer', $data['invoice_footer']);
                Helper::transient('default_currency', $data['default_currency']);
                Helper::transient('default_tax', $data['default_tax']);
                Helper::transient('print_constraint', is_null($data['print_constraint']) ? 'false' : 'true');
            }
        }

        $this->data['form'] = $form->render();
        $this->view('Form');
    }
}