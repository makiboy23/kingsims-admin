<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Admin_Controller {
	public function after_init() {
		$this->set_scripts_and_styles();

		$this->load->model('admin/products_model', 'products');
	}

	public function index($page = 1) {
		$this->_data['title']			= "Products";
		$this->_data['add_label']		= "New Product";
		$this->_data['add_url']			= base_url() . "products/new";

		$actions = array(
			'update'
		);

		$select = array(
			'product_id as id',
			'product_id as "Product ID"',
			'product_title as "Product Title"',
			'product_status as "Status"'
		);

		$where = array(
			// 'product_status' => 1
		);

		$total_rows = $this->products->get_count(
			$where
		);
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->products->get_data($select, $where, array(), array(), array('filter'=>'product_datetime_added', 'sort'=>'DESC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 2);
		$this->set_template("products/list", $this->_data);
	}

	public function new() {
		$this->_data['title']			= "New Product";
		$this->_data['form_url']		= base_url() . "products/new";
		$this->_data['notification'] 	= $this->session->flashdata('notification');

		if ($_POST) {

			$this->_data['post'] = $_POST;

			if ($this->form_validation->run('add')) {}
		}

		end:
		$this->set_template("products/form", $this->_data);
	}
}
