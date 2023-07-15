<?php

/**
 * Users Maintenance Controller
 * Version 1.1.2
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

namespace controller;

use dbgrid\DbCrud;
use helper\CryptStr;
use helper\Helper;
use helper\Session;
use models\users_model;

class Users extends BaseController {
    private DbCrud $crud;

    function __construct() {
        parent::__construct();
        
        $this->crud = new DbCrud(new users_model());
        $this->crud->grid_delete = '';
        $this->crud->grid_show = '';
        $this->crud->addFields('Name,Mail,AccessControl,ValidUntil');
        $this->crud->editFields('UserId,ClientId,Name,Mail,AccessControl,ValidUntil');
        $this->crud->gridFields('UserId,Name,Mail,ValidUntil');
        $this->crud->readonlyFields('UserId,ClientId');
        $this->crud->fieldType('ValidUntil', 'datetext');
        $this->crud->fieldPlaceholder('ValidUntil', 'YYYY-MM-DD');
        $this->crud->searchFields('Name,Mail');

        $this->crud->fieldTitles(
            'Name,Mail,AccessControl,ValidUntil,ClientId,UserId',
            'Name,Mail,Access Control, Valid until,Client ID, User ID'
        );

        $this->crud->callbackInsert([$this, 'callback_insert']);
        $this->crud->callbackUpdate([$this, 'callback_update']);
        $this->crud->formatField('Mail', [$this, 'callback_formatMail']);
    }

    public function callback_update(mixed $id, array $data) : bool {
        $data['Mail'] = CryptStr::instance(Helper::env('app_secret'))->encrypt($data['Mail']); // encrypt Mail
        return $this->crud->model()->update($id, $data);
    }

    public function callback_formatMail (string $source, string $value) : string {
        if ( empty($value) )
            return $value;
            
        if ( ctype_xdigit($value) ) {
            $result = CryptStr::instance(Helper::env('app_secret'))->decrypt($value); // decrypt Mail

            if ( $result === false )
                return $value;
            else
                return $result;
        }
        else
            return $value;
    }

    public function callback_insert(array $data) : void {
        $userid = uniqid();
        $keycode = bin2hex(random_bytes(32));
        $clientid = bin2hex(random_bytes(16));
        $location = Helper::env('tmp_location');
        $aname = strtolower(str_replace(' ','_', Helper::env('app_title')));
        $uname = strtolower(str_replace(' ','_', $data['Name']));

        // create and save keyfile
        $filename = "$location/$aname-$uname.key";
        $result = json_encode(['user_id'=>$userid,'key_code'=>$keycode]);
        $cdata = CryptStr::instance(Helper::env('app_secret'))->encrypt($result === false ? '' : $result);
        file_put_contents($filename, Auth::KEY_HEADER.$cdata);

        // save the current user data
        $data['UserId'] = $userid;
        $data['ClientId'] = $clientid;
        $data['KeyCode'] = password_hash($keycode,  PASSWORD_DEFAULT); // hash Keycode
        $data['Mail'] = CryptStr::instance(Helper::env('app_secret'))->encrypt($data['Mail']); // encrypt Mail
        $this->crud->model()->insert($data);
        Auth::create_registration($data);
    }

    public function index () : void {
       $this->grid(1);
    }

    public function add() : void {
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud');
    }

    public function edit(string $id) : void {
        $this->data['dbgrid'] = $this->crud->form('edit', $id);
        $this->view('Crud');
    }

    public function delete(string $id) : void {
        if ( $id == Session::instance()->get('user_id', 'unkwonw') ) 
            $this->data['dbgrid'] = $this->crud->form('edit', $id, 'you can NOT delete yourself');
        else {
            $this->crud->delete($id);
            $this->data['dbgrid'] = $this->crud->grid();
        }

        $this->view('Crud');
    }

    public function grid(int $page) : void {
        $this->data['dbgrid'] = $this->crud->grid($page);
        $this->view('Crud');
    }

    public function clear() : void {
        $this->crud->clear();
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud');
    }
}