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
|	https://codeigniter.com/userguide3/general/routing.html
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
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


// Auth Routes
$route['auth/login']['POST'] = 'auth/login';
$route['auth/register']['POST'] = 'auth/register';
$route['auth/check']['GET'] = 'auth/check';
$route['auth/logout']['POST'] = 'auth/logout';

// Profile Routes
$route['profile']['GET'] = 'profile/index';
$route['profile/update_profile']['POST'] = 'profile/update_profile';

// Transactions Routes
$route['transactions']['GET'] = 'transactions/index';
$route['transactions/create']['POST'] = 'transactions/create';
$route['transactions/edit/(:any)']['POST'] = 'transactions/edit/$1'; // Changed to POST to match frontend
$route['transactions/delete/(:any)']['DELETE'] = 'transactions/delete/$1';
$route['transactions/search']['POST'] = 'transactions/search';

// Dashboard Routes
$route['dashboard']['GET'] = 'dashboard/index';
$route['dashboard/get_chart_data']['GET'] = 'dashboard/get_chart_data';

// Categories Routes
$route['categories']['GET'] = 'categories/index';
$route['categories/create']['POST'] = 'categories/create';
$route['categories/edit/(:any)']['POST'] = 'categories/edit/$1'; // Changed to POST to match frontend
$route['categories/delete/(:any)']['DELETE'] = 'categories/delete/$1';
$route['categories/search']['POST'] = 'categories/search';


