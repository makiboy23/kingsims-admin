<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route["default_controller"]                = "admin/Dashboard";

# ADMIN ACCOUNTS
$route["admin-accounts"]                    = "admin/Admin_accounts";
$route["admin-accounts/new"]                = "admin/Admin_accounts/new";
$route["admin-accounts/update/(:any)"]      = "admin/Admin_accounts/update/$1";

# PRODUCTS
$route["products"]                          = "admin/Products";
$route["products/new"]                      = "admin/Products/new";
$route["products/update/(:any)"]            = "admin/Products/update/$1";

# PRODUCTS
$route["esims"]                          = "admin/Transatel_esims";
$route["esims/new"]                      = "admin/Transatel_esims/new";
$route["esims/update/(:any)"]            = "admin/Transatel_esims/update/$1";

###### API ######

# API TOKEN
$route["token"]                             = "api/Token";

# API MANAGEMENT
$route["api-management"]                    = "admin/Api_management";
$route["api-management/new"]                = "admin/Api_management/new";
$route["api-management/update/(:any)"]      = "admin/Api_management/update/$1";

# LOGIN LOGOUT
$route["login"]                             = "public/login";
$route["logout"]                            = "public/logout";

$route['404_override']              = 'api/Error_404';
$route['translate_uri_dashes']      = FALSE;
























