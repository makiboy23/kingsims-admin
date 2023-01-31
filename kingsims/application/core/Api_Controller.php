<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CMS_Controller class
 * Base controller ?
 *
 * @author Marknel Pineda
 */
class Api_Controller extends MX_Controller {
	protected
		$_limit = 10,
		$_today = "",
		$_base_controller = "api",
		$_base_session = "session";

	protected
		$_upload_path = FCPATH . UPLOAD_PATH,
		$_ssl_method = "AES-128-ECB";

	protected
		$_oauth_bridge_parent_id = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize all configs, helpers, libraries from parent
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		$this->_today = date("Y-m-d H:i:s");

		header('Content-Type: application/json');

		$this->init();
		$this->after_init();
	}

	public function init() {
		// $this->validate_parent_auth(); // post only
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->global_validate_token();
		}
	}

	public function after_init() {
		// default validate api master
	}

	public function global_validate_token() {
		$this->load->library("oauth2");
		$this->oauth2->get_resource();
	}

	public function validate_api_master() {
		$this->load->model("api/oauth_clients_model", "clients");

		$token_row = $this->get_token();
		$client_id = $token_row->client_id;

		$inner_joints = array(
			array(
				"table_name"	=> "oauth_bridges",
				"condition"		=> "oauth_bridges.oauth_bridge_id = oauth_clients.oauth_bridge_id"
			),
			array(
				"table_name"	=> "api_accounts",
				"condition"		=> "api_accounts.oauth_bridge_id = oauth_bridges.oauth_bridge_id"
			)
		);

		$where = array(
			'client_id' => $client_id
		);

		$row = $this->clients->_datum(
			array(
				'api_accounts.oauth_bridge_id as "oauth_bridge_parent_id"'
			),
			$inner_joints,
			$where
		)->row();

		if ($row == "") {
			echo json_encode(
				array(
					'error'			=> true,
					'error_message'	=> "Invalid API token!",
					'timestamp'		=> $this->_today
				)
			);
			die();
		}

		return $row;
	}

	public function validate_parent_auth() {
		$this->global_validate_token();
	}

	public function get_url_post() {
		parse_str($_SERVER['QUERY_STRING'], $get); 
		
		if (!is_countable($get)) {
			return array();
		}

		if (count($get) == 0) {
			return array();
		}

		return filter_var_array_sanitize($get);
	}

	public function get_post() {
		$post = json_decode($this->input->raw_input_stream, true);

		if (!is_countable($post)) {
			return array();
		}

		if (count($post) == 0) {
			return array();
		}

		return filter_var_array_sanitize($post);
	}

	public function JSON_POST() {
		$content_type = $this->input->get_request_header('Content-Type', TRUE);
		$json = "application/json";
		
		if (preg_match("/\bjson\b/", $content_type ?? '')) {
			return true;
		}

		return false;
	}

	public function get_pagination_offset($page = 1, $limit = 10, $num_rows = 10) {
		$page 	= ($page < 1 ? 1 : $page);
		$offset = ($page - 1) * $limit;
		$offset = ($offset >= $num_rows && $page == 1 ? 0 : $offset);
		return $offset;
	}

	public function generate_oauth1_header_url($url, $consumer_key, $consumer_secret, $method = 'GET', $timestamp = '', $nonce = '', $signature_method = 'HMAC-SHA1', $version = '1.0') {
		if ($nonce == '') {
			$nonce	= hash('sha512', generate_code(11));
		}

		if ($timestamp == '') {
			$timestamp 	= round(microtime(true) * 1000);
		}

		$base = $method
        . '&' . rawurlencode($url)
        . '&' . rawurlencode('oauth_consumer_key=' . $consumer_key)
		. rawurlencode('&oauth_nonce=' . $nonce)
        . rawurlencode('&oauth_signature_method=' . $signature_method)
        . rawurlencode('&oauth_timestamp=' . $timestamp)
        . rawurlencode('&oauth_version=' . $version);

		$key = rawurlencode($consumer_secret) . '&' . rawurlencode("");
		$signature = rawurlencode(base64_encode(hash_hmac('SHA1', $base, $key, true)));

		$curl_url = $url . "?"
		. "oauth_consumer_key={$consumer_key}&"
		. "oauth_signature_method={$signature_method}&"
		. "oauth_timestamp={$timestamp}&"
		. "oauth_nonce={$nonce}&"
		. "oauth_version={$version}&"
		. "oauth_signature={$signature}";

		return $curl_url;
	}

	public function update_signal_status($signal_id, $status, $page_info = "") {
		$data = array(
			'signal_status' 			=> $status,
			'signal_page_info'			=> $page_info,
			'signal_datetime_updated'	=> $this->_today
		);

		$this->shopify_signal_status->update(
			$signal_id,
			$data
		);
	}
}
