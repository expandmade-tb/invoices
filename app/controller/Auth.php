<?php

/**
 * Auth Controller
 * Version 1.4.0
 * Author: expandmade / TB
 * Author URI: https://expandmade.com
 */

namespace controller;

use Formbuilder\Formbuilder;
use Formbuilder\StatelessCSRF;
use helper\Helper;
use mail\Email;
use helper\Session;
use helper\CryptStr;
use helper\UrlVars;
use models\users_model;
use Router\Router;

class Auth extends BaseController {
    const LOCKOUT_TIME = 900;
    const MAX_FSIZE = 512;
    const KEY_HEADER = '2a498d';

    private string $user_id = '';

    function __construct() {
        $this->data['css_files'] = [
            STYLESHEET.'/styles.min.css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'            
        ];

        $this->data['js_files'] = [
            JAVASCRIPT.'/ident.min.js',
            "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        ];

        $this->data['icon'] = IMAGES.Helper::env('app_image');
        $this->data['title'] = Helper::env('app_title', 'Remote Tables');
        $this->data['dest'] = '/auth/ajax_client_id';
        $this->data['token'] = $this->create_token();;
    }
    
    // check if a lockout has been requested
    private function check_lockout() : void {
        $id = 'lockout-'.Session::instance()->get('client_id', Helper::get_ip_addr()); 
        
        if ( Helper::transient($id) !== false )
            die('goodbye');
    }

    // request / set a lockout for a specific ip 
    private function lockout(string $reason, int $time) :void {
        $id = 'lockout-'.Session::instance()->get('client_id', Helper::get_ip_addr()); 
        Helper::transient($id, $reason, $time);
        Helper::log('lockout: '.$reason);
        die('goodbye');
    }

    // render the complete input form
    private function render_form() : string  {
        header('Strict-Transport-Security: max-age=15768000; includeSubDomains; preload');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: Sameorigin');
        header('Referrer-Policy: same-origin');
  
        $form = new Formbuilder('auth', ['string'=>'enctype="multipart/form-data"']);
        $form->file('access_code',['label'=>'Code:','string'=>'required onchange="LimitFilesize(this, '.Auth::MAX_FSIZE.')"']);
        $form->html('<br>');
        $form->submit('submit');
        $form->check_timer = 2;

        if ( $form->submitted() ) {
            $field_list='access_code';
            $form->rule([$this,'check_access_code'], 'access_code');
            $form_data = $form->validate($field_list);

            if ( $form_data === false )
                $this->lockout('possible bot detected', Auth::LOCKOUT_TIME);
                
            if($form->ok()) { 
                Helper::logged_in(['user_id'=>$this->user_id]);
                Helper::clean_transient('lockout-%');

                if ( Helper::env('admin_cookie', false) )
                    setcookie('admin', strval(time()), 0, '/');

                exit();
            }
        }

        return $form->render();
    }

    private function create_token(int $lifetime=10) : string {
        $csrf_generator = new StatelessCSRF(Helper::env('app_secret', 'empty_secret'));
        $csrf_generator->setGlueData('ip', $_SERVER['REMOTE_ADDR']);
        $csrf_generator->setGlueData('user-agent', $_SERVER['HTTP_USER_AGENT']);            
        $token = $csrf_generator->getToken(Helper::env('app_identifier','empty_identifier'), time() + $lifetime );
        return $token;
    }

    private function validate_token(string $token) : bool {
        $csrf_generator = new StatelessCSRF(Helper::env('app_secret', 'empty_secret'));
        $csrf_generator->setGlueData('ip', $_SERVER['REMOTE_ADDR']);
        $csrf_generator->setGlueData('user-agent', $_SERVER['HTTP_USER_AGENT']);            
        $result = $csrf_generator->validate(Helper::env('app_identifier','empty_identifier'), $token, time());
        return $result;
    }

    // filters and validates ajax requests
    private function ajax_filter (string $input) : mixed {
        // check if ajax is used
        if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') 
            return false;
        
        // check if referer is used
        if ( !empty($_SERVER['HTTP_REFERER']) ) {
            $valid_url = Helper::url().'/'.strtolower(basename(__FILE__, '.php'));

            if ( strncmp($_SERVER['HTTP_REFERER'], $valid_url, strlen($valid_url)) != 0 ) 
                return false;
        }

        // check the origin
        if ( !empty($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != Helper::url() )
            return false;

            // check if correct token is send
        if ( empty($_SERVER["HTTP_AJAX_REQUEST_TOKEN"]) )
            return false;

        if ( $this->validate_token($_SERVER["HTTP_AJAX_REQUEST_TOKEN"]) === false )
            return false;

        // we just want an ID....
        return preg_replace("/[^a-zA-Z0-9]+/", "", $input);
    }
    
    /**
     * resets users client id to 'not verified' and creates a registration link
     * currently as a file in the temp dir, later may be mailed to user
     * 
     * @param array $user user data
     *
     * @return bool 
     */
    public static function create_registration(array $user) : bool {
        $users = new users_model();
        $result = $users->update($user['UserId'], ['ClientId'=>$user['ClientId'].'-not_verified']);

        if ( $result === false )
            return false;

        $location = Helper::env('tmp_location');
        $aname = strtolower(str_replace(' ','_', Helper::env('app_title')));
        $uname = strtolower(str_replace(' ','_', $user['Name']));
        $filename = "$location/$aname-$uname.html";
        $param = ['uid'=>$user['UserId'], 'expire'=>time()+86400];

        if ( Helper::env('debug', '') )
            Helper::log(['registration: ', $param]);

        $uv = new UrlVars();
        $regParam = $uv->set_header(Helper::env('app_identifier'), 86400)->set_secret(Helper::env('app_secret'))->encode($param, true);
        $href = Helper::url().'/'.Router::instance()->getAuth().'/register/'.$regParam;
        $contents = '<!DOCTYPE html><html><body><h1>Finish registration</h1><a href="'.$href.'">Register</a></body></html>';
        $result = file_put_contents($filename, $contents);

        $host = Helper::env('smtp_host','');

        if ( !empty($host) ) {
            $port = Helper::env('smtp_port', 587);
            $from = Helper::env('smtp_from', '');
            $fromname = Helper::env('smtp_fromname', '');
            $recipient = CryptStr::instance(Helper::env('app_secret'))->decrypt($user['Mail']);

            if ( $recipient === false )
                return false;

            $mail = new Email($host, intval($port));
            $mail->setProtocol(Helper::env('smtp_secure','tls'));
            $mail->setLogin(Helper::env('smtp_username'), Helper::env('smtp_password'));
            $mail->setFrom($from, $fromname);
            $mail->addTo($recipient);
            $mail->setSubject('New Client Id Request');
            $mail->setTextMessage($href);  
            $mail->addAttachment(str_replace('.html', '.key', $filename));
            return $mail->send();
        }

        return false;
    }
    
    // sets a session value "client_id" with the computed client id
    public function ajax_client_id(string $input) : void {
        if ( !empty(session::instance()->get('client_id')) )
            return;

        if ( ctype_xdigit($input) != true)
            return;

        if ( $this->ajax_filter($input) === false )
            return;           

        if ( Helper::env('debug', '') )
            Helper::log('client id set: '.$input);

        session::instance()->set('client_id', $input);
    }    

    /**
    * basically checks if:
    * - uploaded keyfile is valid
    * - uploaded keyfile doesnt exceed a size limit of Auth::Max_FSIZE
    * - uploaded keyfile has a valid format
    * - user access has expired
    * - user password matches keyfile
    * - user client id matches registered client id
    */
    public function check_access_code ( ?string $value ) : string  {
        if (empty($_FILES["access_code"]) )
            return 'you have to provide a key code file';

        if ( empty(session::instance()->get('client_id')) )
            return 'client id not retrieved';

        $filepath = $_FILES['access_code']['tmp_name'];
        $fileSize = filesize($filepath);

        if ($fileSize === 0)
            return 'the file is empty';

        if ($fileSize > Auth::MAX_FSIZE) // we do not need megabyte sized files
            $this->lockout('filesize too large', Auth::LOCKOUT_TIME);

        $strJsonFileContents = file_get_contents($filepath);

        if ( $strJsonFileContents === false )
            return 'file contents cannot be read';

        if ( substr($strJsonFileContents, 0, strlen(Auth::KEY_HEADER)) != Auth::KEY_HEADER )
            return 'invalid file format';

        $result = CryptStr::instance(Helper::env('app_secret'))->decrypt(substr($strJsonFileContents, strlen(Auth::KEY_HEADER)));

        if ( $result === false )
            return 'cannot decrypt file';
            
        $array = json_decode($result, true);   

        if ( $array === false )
            return 'file cannot be decoded';

        $key_code = Helper::env('key_code', 'invalid value');
        $file_key_code = $array["key_code"]??'unknown';
        $file_user_id =  $array["user_id"]??'unknown';
        $users = new users_model();

        if ( $users->count() == 0 ) // if no users at all, check for the environment key_code 
            
            if ( $file_key_code == $key_code )
                return '';
            else
                $this->lockout('invalid key code', Auth::LOCKOUT_TIME);

        $user = $users->find($file_user_id);

        if ( $user === false ) // we cant find the presented user
            $this->lockout('invalid user', Auth::LOCKOUT_TIME);

        if ( !empty($user['ValidUntil']) && $user['ValidUntil'] < time() ) // is the access still valid ?
            die('user access no longer valid');

        if ( password_verify($file_key_code, $user['KeyCode'] ) !== true ) // check if presented key code is the stored in db
            $this->lockout('invalid key code', Auth::LOCKOUT_TIME);

        if ( defined('REGISTER') ) { // register a new client id with the given key code
            $users->update($file_user_id, ['ClientId'=>session::instance()->get('client_id')]);
            $this->user_id = $file_user_id; // we have got a validated user...yeah
            return '';
        }

        if ( $user['ClientId'] != session::instance()->get('client_id') ) {

            if ( substr($user['ClientId'], -13) != '-not_verified' ) 
                $this->create_registration($user);
    
            $this->lockout('invalid client id: '.session::instance()->get('client_id'), 300);
        }

        $this->user_id = $file_user_id; // we have got a validated user...yeah
        return '';
    }

    /**
     * launches the login form
    */
    public function index () : void {
        $this->check_lockout();
        $this->data['form'] = $this->render_form();
        $this->view('Auth');
    }

    public function logout () : void {
        if ( Helper::env('admin_cookie', false) )
            setcookie('admin', strval(time()), -3600, '/');
            
        Helper::logged_out();
    }

    /**
     * unlocks a locked client id
     */
    public function unlock (string $param='') : void {
        if ( empty($param) )
            die('invalid request');

        if ( ctype_xdigit($param) != true)
            die('invalid request');

        $key_code = Helper::env('key_code', 'invalid value');

        if ( $key_code === $param ) {
            $id = 'lockout-'.session::instance()->get('client_id', Helper::get_ip_addr()); 
            Helper::transient($id, '', -1);
            die('done');
        }
    }

    /**
    *   registers a users new client id with the login form before the user can present his keyfile we are going to check:
    * - a valid user id
    * - the registration period has already passed
    * - the registration has been initiated
    */ 
    public function register(string $param='') : void {
        if ( empty($param) )
            die('invalid request - empty param');

        if ( ctype_xdigit($param) != true)
            die('invalid request - invalid param');

        $uv = new UrlVars();
        
        if ( $uv->set_header(Helper::env('app_identifier'), 86400)->set_secret(Helper::env('app_secret'))->decode($param) === false )
            die('invalid request - scrambled param');

        $user_id = $uv->get('uid','invalid user id');
        $expires = $uv->get('expire', '0');

        if ( $expires < time() )
            die('invalid request - expired');

        $users = new users_model();
        $user = $users->find($user_id);
        
        if ( $user === false)
            die('invalid request - invalid user');

        if ( substr($user['ClientId'], -13) != '-not_verified' ) 
            die('invalid request - already verified');
           
        define('REGISTER', $user['ClientId']);
        $this->data['form'] = $this->render_form();
        $this->view('Auth');
    }
}