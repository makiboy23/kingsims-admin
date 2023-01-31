<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transatel_esims extends Admin_Controller {
	public function after_init() {
		$this->set_scripts_and_styles();

		$this->load->model('admin/transatel_esims_model', 'esims');
	}

	public function index($page = 1) {
		$this->_data['title']			= "Transatel eSims";
		$this->_data['add_label']		= "New eSim";
		$this->_data['add_url']			= base_url() . "esims/new";

		$actions = array(
			'update'
		);

		$select = array(
			'esim_id as id',
			'esim_id as "eSim ID"',
			'esim_number as "eSim No."',
			'esim_status as "Status"'
		);

		$where = array();

		$total_rows = $this->esims->get_count(
			$where
		);
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->esims->get_data($select, $where, array(), array(), array('filter'=>'esim_datetime_added', 'sort'=>'DESC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 2);
		$this->set_template("esims/list", $this->_data);
	}

	public function new() {
		$this->_data['title']			= "New eSim";
		$this->_data['form_url']		= base_url() . "esims/new";
		$this->_data['notification'] 	= $this->session->flashdata('notification');

		if ($_POST) {

			$this->_data['post'] = $_POST;

			if ($this->form_validation->run('add')) {

			}
		}

		end:
		$this->set_template("esims/form", $this->_data);
	}
}
