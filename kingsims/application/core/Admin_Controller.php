<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CMS_Controller class
 * Base controller ?
 *
 * @author Marknel Pineda
 */
class Admin_Controller extends Admin_Core_Controller {
	private 
		$_stylesheets = array(),
		$_scripts = array();

	protected
		$_account = null;

	protected
		$_oauth_bridge_id = null,
		$_oauth_bridge_parent_id = null;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize all configs, helpers, libraries from parent
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		$this->_today = date("Y-m-d H:i:s");

		$this->validate_login();
		$this->setup_nav_sidebar_menu();
		$this->after_init();
	}

	public function validate_login() {
		$login_url = base_url() . "login";

        $controller = strtolower(get_controller());
		if(empty($this->session->userdata("{$this->_base_session}")) && $controller != 'login' ) {
            $this->session->unset_userdata("{$this->_base_session}");
			$this->session->sess_destroy();
            redirect($login_url);
        } else if(!empty($this->session->userdata("{$this->_base_session}"))) {
			$this->_account = $this->session->userdata("{$this->_base_session}");

			// $this->_oauth_bridge_id 		= $this->_account['oauth_bridge_id'];
			// $this->_oauth_bridge_parent_id 	= $this->_account['oauth_bridge_parent_id'];
		}
	}

	/**
	 * Generate Menu UI
	 *
	 */
	private function setup_nav_sidebar_menu() {
		$this->_data['logout_url'] = base_url() . "logout";

		$menu_items = array();

		$menu_items[] = array(
			'menu_id'			=> 'dashboard',
			'menu_title'		=> 'Dashboard',
			'menu_url'			=> 	base_url(),
			'menu_controller'	=> 'dashboard',
			'menu_icon'			=> 'view-dashboard',
			// 'menu_sub_items'	=> array(
			// 	array(
			// 		'menu_title'		=> 'Sub Menu Sample',
			// 		'menu_url'			=> 	base_url(),
			// 		'menu_controller'	=> 'dashboard',
			// 	)
			// )
		);


		$menu_items[] = array(
			'menu_id'			=> 'esims',
			'menu_title'		=> 'eSims',
			'menu_url'			=> 	base_url() . "esims",
			'menu_controller'	=> 'esims',
			'menu_icon'			=> 'view-dashboard',
		);

		$menu_items[] = array(
			'menu_id'			=> 'products',
			'menu_title'		=> 'Products',
			'menu_url'			=> 	base_url() . "products",
			'menu_controller'	=> 'products',
			'menu_icon'			=> 'view-dashboard',
		);

		$menu_items[] = array(
			'menu_id'			=> 'admin-accounts',
			'menu_title'		=> 'Admin Accounts',
			'menu_url'			=> 	base_url() . "admin-accounts",
			'menu_controller'	=> 'admin_accounts',
			'menu_icon'			=> 'view-dashboard',
		);

		$this->_data['nav_sidebar_menu'] = $this->generate_sidebar_items($menu_items);
	}
}
