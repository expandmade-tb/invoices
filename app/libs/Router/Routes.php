<?php

namespace Router;

class Routes {
    public static string $defaultNamespace = 'controller\\';
    public static string $defaultHome = '/';
	public static string $defaultMethod = 'index';

    public static array $routes = [
        '/' => 'GET|POST|Home',
        'init' => ['GET|Init', 'index'],
        'customers' => ['GET|POST|Customers', 'index,add,edit,delete,grid,clear'],
        'products' => ['GET|POST|Products', 'index,add,edit,delete,grid,clear'],
        'invoices' => ['GET|POST|Invoices', 'index,add,edit,delete,grid,clear,print'],
        'InvoicesDetails' => ['GET|POST|InvoicesDetails', 'index,add,edit,delete,grid,selectinvoice,show'],
        'currencies' => ['GET|POST|Currencies', 'index,add,edit,delete,grid,clear'],
        'auth' => ['GET|POST|Auth', 'index,unlock,logout,register,ajax_client_id'],
        'users' => ['GET|POST|Users', 'index,add,edit,delete,grid,clear,show'],
        'upgrade' => ['GET|POST|Upgrade', 'index'],
        'settings' => ['GET|POST|Settings', 'index'],
        'clientRequests' => 'GET|clientRequests',
    ];

    public static array $auth_exceptions = [
        'init' => ['GET|Init'],
    ];
}  