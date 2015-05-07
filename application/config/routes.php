<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/
$route['default_controller'] = "user";
$route['index'] = "user/user/index";
$route['login'] = "user/user/login";
$route['serviceAuth'] = "user/user/serviceAuth";
$route['reset_pass'] = "user/reset_pass";
$route['forgotpassword'] = "user/user/forgotpassword";
$route['logout'] = "user/user/logout";
$route['chksession1'] = "user/user/chksession1";
$route['register'] = "user/user/register";
$route['home'] = "user/user/home";
$route['dashboard'] = "user/user/dashboard";
$route['verification'] = 'user/user/verification';
$route['search'] = 'user/user/search';
$route['fb'] = "user/user/fb";
$route['search_result_page'] = 'user/user/search_result_page';
$route['user_mgmt'] = 'user/user/user_mgmt';
$route['my_profile'] = 'user/user/user_add_edit';
$route['user_add_edit'] = 'user/user/user_add_edit';
$route['autocomplete'] = 'user/user/autocomplete';
$route['search_result'] = 'user/user/search_result';
$route['system_setting'] = 'user/user/system_setting';
$route['select_city'] = 'user/select_city';
$route['checkContact'] = 'user/checkContact';

$route['category_mgmt'] = 'category/category_mgmt';
$route['category_add_edit'] = 'category/category_add_edit';
$route['groups'] = 'group/groups';
$route['group_add_edit'] = 'group/group_add_edit';

$route['locations'] = 'location/locations';
//$route['createlocation'] = 'location/createlocation';
$route['createlocation'] = 'location/createlocation';
$route['urlNameExit'] = 'location/urlName';
$route['search_location'] = 'location/search_location';
$route['search_sub_location'] = 'location/search_sub_location';
$route['similar_location'] = 'location/similar_location';
//$route['newLocationInsert'] = 'location/newLocationInsert';


//$route['mail_mgmt'] = 'email_management/mail_mgmt';
//$route['semail_add_edit'] = 'email_management/semail_add_edit';
//$route['semail_add_edit_action'] = 'email_management/semail_add_edit_action';

//$route['my_profile'] = 'my_profile/my_profile';
//$route['my_profile_edit'] = 'my_profile/my_profile_edit';
//$route['my_profile_action'] = 'my_profile/my_profile_action';
//$route['changePassword'] = 'my_profile/changePassword';

//$route['page_content_mgmt'] = 'page_content/page_content_mgmt';
//$route['page_content_add_edit'] = 'page_content/page_content_add_edit';
//$route['page_content_action'] = 'page_content/page_content_action';

$route['inbox'] = 'setting/inbox';
$route['country_mgmt'] = 'setting/country_mgmt';
$route['country_add_edit'] = 'setting/country_add_edit';
//$route['country_action'] = 'setting/country_action';

$route['contact-us.html'] = 'content/contact_us';
$route['faq.html'] = 'content/faq';

$route['404_override'] = '';
$route['forbidden'] = 'content/forbidden';

// Backend Routes
//$route['admin'] = "welcome/welcome/index";
//$route['admin/(.*)'] = 'admin/$1';

require_once(BASEPATH . 'database/DB' . EXT);
$db = getDBObject();
$static_pages = $db->select("vPageCode, vUrl, vPageTitle")->where('eStatus','Active')->get('page_settings')->result_array();
foreach($static_pages as $i=>$route_arr){
    $route[$route_arr['vUrl']] = "content/content/staticpage/".$route_arr['vPageCode'];
}

$domain_pages = $db->select("vUrl")->get('domain')->result_array();
foreach($domain_pages as $i=>$route_arr){
    $route[$route_arr['vUrl']] = "location/location_detail/".$route_arr['vUrl'];
}
/* End of file routes.php */
/* Location: ./application/config/routes.php */
