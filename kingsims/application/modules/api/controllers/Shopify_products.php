<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shopify_products extends Api_Controller {
	public function init() {}

	public function after_init() {
		$this->load->model("api/Shopify_products_model", "shopify_products");
		$this->load->model("api/Benchmark_products_model", "benchmark_products");
		$this->load->model("api/Benchmark_product_preview_groups_model", "benchmark_product_preview_groups");
		$this->load->model("api/Shopify_signal_status_model", "shopify_signal_status");
	}

	public function index() {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			exit;
		}

		$post = $this->get_url_post();
		
		if (!isset($post[0])) {
			exit;
		}

		if ($post[0] != SSKEY) {
			exit;
		}

		sleep(1); // to make sure the safe to the limitation of shopify

		// check if shopify signal status is ready
		$datum = $this->shopify_signal_status->_datum(
			array('*'),
			array(),
			array(
				'signal_id' 	=> 1,
				'signal_status' => 0 //ready
			)
		)->row();
		
		if ($datum == "") {
			// signal status setup
			$this->output->set_status_header(204);
			exit;
		}

		// -------------------------------------
		// update signal status to processing
		$signal_id = $datum->signal_id;

		$this->update_signal_status(
			$signal_id,
			1 // processing
		);

		$page_info = $datum->signal_page_info;

		$query = array(
			'limit' 	=> 250,
			'page_info' => $page_info,
			'rel'		=> 'next'
		);

		$response = $this->rest_api(
			SHOPIFY_TOKEN,
			"johnstonjewelers.myshopify.com",
			"/admin/api/2022-04/products.json",
			$query,
			"GET"
		);

		// get headers
		$headers		= $response['headers'];

		// get data
		$data			= $response['data'];

		$has_nextpage 	= isset($headers['Link']);

		// get shopify url pagination
		if ($has_nextpage) {
			$next_page_url 			= str_btwn($headers['Link'], '<', '>');
			$next_page_url_params 	= parse_url($next_page_url);
			parse_str($next_page_url_params['query'], $value);
			$page_info 				= $value['page_info'];
			
			$rel = explode(";", $headers['Link']);
			$rel = str_btwn($rel[1], '"', '"');

			if ($rel != 'next') {
				// end of the pagination
				$has_nextpage 	= false;
				$page_info 		= "";
			}
		}

		if (is_json($data)) {
			// decode response from API
			$data = json_decode($data, true);

			if (isset($data['products'])) {
				// process data
				$this->do_saving($data['products']);
			}
		}

		// if has next page set status to ready else completed
		$signal_status = $has_nextpage ? 0 : 2;

		$this->update_signal_status(
			$signal_id,
			$signal_status,
			$page_info
		);
	}

	public function create() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->output->set_status_header(401);
			exit;
		}

		$post = $this->get_url_post();
		
		if (!isset($post[0])) {
			exit;
		}

		if ($post[0] != SSKEY) {
			exit;
		}

		$total_rows = $this->shopify_products->get_count();

		if ($total_rows == 0) {
			// impossible for 2nd time run - no product found in shoppifyDB
			exit;
		}
		
		// check if shopify signal status is ready
		$datum = $this->shopify_signal_status->_datum(
			array('*'),
			array(),
			array(
				'signal_id' 	=> 1,
				'signal_status' => 2 //completed
			)
		)->row();
		
		if ($datum == "") {
			// signal status setup
			$this->output->set_status_header(204);
			exit;
		}

		sleep(1);

		$row = $this->benchmark_product_preview_groups->_datum(
			array('*'), 
			array(), 
			array(
				'preview_status' => 0 // ready
			), 
			array(), 
			array(),
			array(), 
			array(),
			array(),
			array(
				'filter_by'	=> 'preview_id',
				'sort_by'	=> 'ASC'
			), 
			$limit = 1)->row();

		if ($row == "") {
			exit;
		}

		// get variants
		$variants_results = $this->benchmark_products->_data(
			array('*'),
			array(),
			array(
				'preview_id' => $row->preview_id
			)
		);

		if (empty($variants_results)) {
			exit;
		}

		// check if already in the shopify skip
		$row_shopify = $this->shopify_products->_datum(
			array('*'),
			array(),
			array(
				'preview_id' => $row->preview_id
			)
		)->row();

		if ($row_shopify != "") {
			$this->benchmark_product_preview_groups->update(
				$row->preview_id,
				array(
					'preview_status' => 2 // completed
				)
			);
			exit;
		}

		$this->benchmark_product_preview_groups->update(
			$row->preview_id,
			array(
				'preview_status' => 1 // processing
			)
		);

		$preview_id = "preview-" . $row->preview_id;

		$variants 	= array();
		$options 	= array();

		$values1 	= array();
		$values2 	= array();

		$image_urls	= array();
		$images		= array();

		foreach ($variants_results as $key => $value) {
			$option1 = $value['width'];
			$option2 = $value['size'];
			
			$variants[] = array(
				'option1' 	=> $option1,
				'option2' 	=> $option2,
				"price" 	=> $value['price'],
				'sku'		=> $value['sku']
			);

			if (!in_array($option1, $values1)) {
				$values1[] = $option1;
			}

			if (!in_array($option2, $values2)) {
				$values2[] = $option2;
			}

			$images_json = $value['images'];
			if (!is_json($images_json)) {
				continue;
			}
	
			// decode response from API
			$image_arr = json_decode($images_json, true);

			foreach ($image_arr as $key_image => $arr) {
				if (isset($arr['url'])) {
					if (!in_array($arr['url'], $image_urls)) {
						$image_urls[] = $arr['url'];
					}
				}
			}
		}

		foreach ($image_urls as $key_url => $url) {
			$images[] = array(
				'src' => $url
			);
		}

		$brand_tag 	= $row->preview_brand;
		$brand_tag	= strtolower($brand_tag);
		$brand_tag	= str_replace(" ", "-", $brand_tag);

		$product = array(
			'title' 		=> $row->preview_title,
			'body_html'		=> $row->preview_description,
			'product_type'	=> $row->preview_brand,
			'vendor'		=> 'JohnstonJewelers',
			'published'		=> true,
			'tags'			=> array(
				$brand_tag,
				$preview_id,
				'benchmark-item',
				'benchmark-update'
			),
			'variants'		=> $variants,
			'options'		=> array(
				array(
					'name' 	=> 'Width',
					'values'=> $values1
				),
				array(
					'name' 	=> 'Size',
					'values'=> $values2
				)
			)
		);

		if (count($images) != 0) {
			$product = array_merge(
				$product,
				array(
					'images' => $images
				)
			);
		}

		$query = array(
			'product' => $product
		);

		$response = $this->rest_api(
			SHOPIFY_TOKEN,
			"johnstonjewelers.myshopify.com",
			"/admin/api/2022-04/products.json",
			$query,
			"POST"
		);

		// get data
		$data			= $response['data'];

		// print_r($data);

		if (is_json($data)) {
			// decode response from API
			$data = json_decode($data, true);

			if (isset($data['product'])) {
				// failed to create
				$this->benchmark_product_preview_groups->update(
					$row->preview_id,
					array(
						'preview_status' => 2 // completed
					)
				);
				exit;
			}
		}

		// success to create
		$this->benchmark_product_preview_groups->update(
			$row->preview_id,
			array(
				'preview_status' => 0 // ready
			)
		);
	}

	private function do_saving($data) {
		foreach ($data as $key => $value) {

			if (empty($value['id'])) {
				continue;
			}

			$preview_id 	= "";
			$update_status	= 0; // enable update

			$tags		= empty($value['tags']) ? '' : $value['tags'];
			$tags		= $tags != "" ? explode(",", $tags) : array();

			foreach ($tags as $t => $tag) {
				$_tag = trim($tag);

				if (strpos($_tag, 'preview-') === 0) {
					$preview_tag 	= explode("-", $_tag);
					if (count($preview_tag) == 2) {
						$preview_id		= $preview_tag[1]; // get id
					}
				}

				if ($tag == "benchmark-update") {
					$update_status = 1; // enable update
				}
			}

			if ($preview_id != "") {
				$data = array(
					'id' 				=> $value['id'],
					'title'				=> empty($value['title']) ? '' : $value['title'],
					'created_at'		=> empty($value['created_at']) ? '' : $value['created_at'],
					'tags'				=> empty($value['tags']) ? '' : $value['tags'],
					'preview_id'		=> $preview_id,
					'update_status'		=> $update_status,
					'datetime_updated'	=> $this->_today
				);

				$this->shopify_products->replace($data);

				// update benchmark groupings status if preview exist to completed because its already in shopifyDB
				$this->benchmark_product_preview_groups->update(
					$preview_id,
					array(
						'preview_status' => 2 // completed
					)
				);
			}
		}
	}

	private function rest_api($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
		$url = "https://" . $shop . $api_endpoint;
		if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);
		
		// $curl = curl_init($url);
		// curl_setopt($curl, CURLOPT_HEADER, TRUE);
		// curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		// curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		// curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		// curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		// curl_setopt($curl, CURLOPT_VERBOSE, true);
	
		// $request_headers[] = "";
		// $headers[] = "Content-Type: application/json";

		// if ($method == 'POST') {
		// 	$headers[] = "Cookie: request_method=POST";
		// }

		// if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
		// curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	
		// if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
		// 	if (is_array($query)) $query = json_encode($query);
		// 	curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
		// }
		
		// $response = curl_exec($curl);

		$curl = curl_init();

		$options = array();

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $token,
				'Content-Type: application/json'
			)
		);

		if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
			if (is_array($query)) $query = json_encode($query);
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HEADER => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $query,
				CURLOPT_HTTPHEADER => array(
				  'X-Shopify-Access-Token: ' . $token,
				  'Content-Type: application/json',
				  'Cookie: request_method=POST'
				),
			));
		}

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);

		$error_number = curl_errno($curl);
		$error_message = curl_error($curl);

		// Then, after your curl_exec call:
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		curl_close($curl);

		if ($error_number) {
			return $error_message;
		} else {
	
			// $parts = explode("\r\n\r\nHTTP/", $response);
			// $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
			// list($headers, $body) = explode("\r\n\r\n", $parts, 2);

			$headers = get_curl_headers($header);
	
			return array('headers' => $headers, 'data' => $body);
	
		}
	}
}
