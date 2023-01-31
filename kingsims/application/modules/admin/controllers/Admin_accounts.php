<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_accounts extends Admin_Controller {
	public function after_init() {
		$this->set_scripts_and_styles();

		$this->load->model('admin/admin_accounts_model', 'admin_accounts');
	}

	public function index($page = 1) {
		$this->_data['title']			= "Admin Accounts";
		$this->_data['add_label']		= "New Admin Account";
		$this->_data['add_url']			= base_url() . "admin-accounts/new";

		$actions = array(
			'update'
		);

		$select = array(
			'account_no as id',
			'account_no as "Account No."',
			'account_username as Username',
			'account_fname as "First Name"',
			'account_mname as "Middle Name"',
			'account_lname as "Last Name"',
			'account_status as "Status"'
		);

		$where = array(
			// 'account_status' => 1
		);

		$total_rows = $this->admin_accounts->get_count(
			$where
		);
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->admin_accounts->get_data($select, $where, array(), array(), array('filter'=>'account_datetime_added', 'sort'=>'DESC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 2);
		$this->set_template("admin_accounts/list", $this->_data);
	}

	public function new() {
		$this->_data['title']			= "New Admin Account";
		$this->_data['form_url']		= base_url() . "admin-accounts/new";
		$this->_data['notification'] 	= $this->session->flashdata('notification');
		// $this->_data["is_update"]	= true;

		if ($_POST) {

			$this->_data['post'] = $_POST;

			if ($this->form_validation->run('add')) {
				$username		= $this->input->post("username");
				$first_name		= $this->input->post("first-name");
				$middle_name	= $this->input->post("middle-name");
				$last_name		= $this->input->post("last-name");
				
				$username		= strtolower($username);

				$password		= $this->input->post("password");
				$repeat_password= $this->input->post("repeat-password");

				// validate password
				if ($password != $repeat_password) {
					$this->_data['notification'] = $this->generate_notification('danger', 'Password not match!');
					goto end;
				}

				$row = $this->admin_accounts->_datum(
					array('*'),
					array(),
					array(
						'account_username'	=> $username
					)
				)->row();
				
				if ($row != "") {
					$this->_data['notification'] = $this->generate_notification('danger', 'Username already exist!');
					goto end;
				}

				$this->admin_accounts->insert(
					array(
						'account_username'		=> $username,
						'account_password'		=> hash("sha256", $password),
						'account_fname'			=> $first_name,
						'account_mname'			=> $middle_name,
						'account_lname'			=> $last_name,
						'account_datetime_added'=> $this->_today,
						'account_status'		=> 1
					)
				);

				$this->session->set_flashdata('notification', $this->generate_notification('success', 'Successfully Added!'));
				redirect($this->_data['form_url'], 'refresh');
			}
		}

		end:
		$this->set_template("admin_accounts/form", $this->_data);
	}

	public function update($id) {
		$this->_data['title']			= "Update Admin Account";
		$this->_data['form_url']		= base_url() . "admin-accounts/update/{$id}";
		$this->_data['notification'] 	= $this->session->flashdata('notification');
		$this->_data["is_update"]		= true;

		$where = array(
			'account_no' => $id
		);

		$inner_joints = array();

		$row = $this->admin_accounts->get_datum(
			'',
			$where,
			array(),
			$inner_joints
		)->row();

		if ($row == "") {
			redirect(base_url() . "admin-accounts");
		}

		$this->_data['post'] = array(
			'username'				=> $row->account_username,
			'first-name' 			=> $row->account_fname,
			'middle-name' 			=> $row->account_mname,
			'last-name' 			=> $row->account_lname,
			'status'				=> $row->account_status == 1 ? "checked" : ""
		);

		if ($_POST) {

			$this->_data['post'] = $_POST;

			if ($this->form_validation->run('edit')) {
				$first_name		= $this->input->post("first-name");
				$middle_name	= $this->input->post("middle-name");
				$last_name		= $this->input->post("last-name");
				$status			= $this->input->post("status");
				
				$password		= $this->input->post("password");
				$repeat_password= $this->input->post("repeat-password");

				$this->_data['post'] = array_merge(
					array(
						'status' => $row->account_status == 1 ? "checked" : ""
					)
				);

				// validate password
				if ($password != "" || $repeat_password != "") {
					if ($password != $repeat_password) {
						$this->session->set_flashdata('notification', $this->generate_notification('danger', 'Password not match!'));
						redirect($this->_data['form_url']);
					}
				}

				$password_data = array();

				if (($password == $repeat_password) && ($password != "" || $repeat_password != "")) { 
					$password_data = array(
						'account_password'		=> hash("sha256", $password)
					);
				}

				$this->admin_accounts->update(
					$id,
					array_merge(
						array(
							'account_fname'			=> $first_name,
							'account_mname'			=> $middle_name,
							'account_lname'			=> $last_name,
							'account_datetime_added'=> $this->_today,
							'account_status'		=> $status == 1 ? 1 : 0
						),
						$password_data
					)
				);

				$this->session->set_flashdata('notification', $this->generate_notification('success', 'Successfully Added!'));
				redirect($this->_data['form_url'], 'refresh');
			}
		}

		end:
		$this->set_template("admin_accounts/form", $this->_data);
	}
}
