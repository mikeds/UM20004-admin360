<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Merchant_accounts extends Admin_Controller {
	private
		$_admin_account_data = NULL;

	public function after_init() {
		$this->set_scripts_and_styles();
		$this->load->model('admin/merchants_model', 'merchants');
		$this->load->model('admin/merchant_accounts_model', 'merchant_accounts');
		$this->load->model('admin/oauth_bridges_model', 'bridges');

		$this->_admin_account_data = $this->get_account_data();
	}

	private function get_merchants() {
		$admin_account_data_results = $this->_admin_account_data['results'];
		$admin_oauth_bridge_id		= $admin_account_data_results['oauth_bridge_id'];

		$where = array(
			'oauth_bridge_parent_id' => $admin_oauth_bridge_id
		);

		$inner_joints = array(
			array(
				'table_name' 	=> 'oauth_bridges',
				'condition'		=> 'oauth_bridges.oauth_bridge_id = merchants.oauth_bridge_id'
			)
		);

		return $this->merchants->get_data(
			array(
				'merchant_number as id',
				'CONCAT(merchant_fname, " ", merchant_mname, " ", merchant_lname) as name',
			),
			$where,
			array(), 
			$inner_joints
		);
	}

	public function index($page = 1) {
		$admin_account_data_results = $this->_admin_account_data['results'];
		$admin_oauth_bridge_id		= $admin_account_data_results['oauth_bridge_id'];

		$this->_data['add_label']= "New Account";
		$this->_data['add_url']	 = base_url() . "merchant-accounts/new";

		$actions = array(
			'update'
		);

		$select = array(
			array(
				'account_number as id',
				'CONCAT(merchant_fname, " ", merchant_mname, " ", merchant_lname) as "Merchant"',
				'account_number as "Account No."',
				'account_username as "Username"',
				'account_fname as "Account First Name"',
				'account_mname as "Account Middle Name"',
				'account_lname as "Account Last Name"',
				'account_date_added as "Date Added"',
				'account_status as "Status"',
			)
		);

		$where = array(
			'oauth_bridge_parent_id' => $admin_oauth_bridge_id
		);

		$inner_joints = array(
			array(
				'table_name' 	=> 'oauth_bridges',
				'condition'		=> 'oauth_bridges.oauth_bridge_id = merchant_accounts.oauth_bridge_id'
			),
			array(
				'table_name' 	=> 'merchants',
				'condition'		=> 'merchants.merchant_number = merchant_accounts.merchant_number',
				'type'			=> 'left'
			),
		);

		$total_rows = $this->merchant_accounts->get_count(
			$where,
			array(),
			$inner_joints
		);
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->merchant_accounts->get_data($select, $where, array(), $inner_joints, array('filter'=>'account_date_added', 'sort'=>'DESC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 4);
		$this->_data['title']  = "Merchant Accounts";
		$this->set_template("merchant_accounts/list", $this->_data);
	}

	public function new() {
		$admin_account_data_results = $this->_admin_account_data['results'];
		$admin_oauth_bridge_id		= $admin_account_data_results['oauth_bridge_id'];

		$this->_data['form_url']		= base_url() . "merchant-accounts/new";
		$this->_data['notification'] 	= $this->session->flashdata('notification');

		$merchants = $this->get_merchants();

		$this->_data['merchants'] = $this->generate_selection(
			"merchant", 
			$merchants, 
			"", 
			"id", 
			"name", 
			false,
			"Select Merchant Name"
		);

		if ($_POST) {
			if ($this->form_validation->run('validate')) {
				$merchant_number	= $this->input->post("merchant");
				$username 			= $this->input->post("username");
				$password 			= $this->input->post("password");
				$repeat_password 	= $this->input->post("repeat-password");
				$fname				= $this->input->post("first-name");
				$mname				= $this->input->post("middle-name");
				$lname				= $this->input->post("last-name");

				if ($this->validate_username("merchant", $username)) {
					$this->session->set_flashdata('notification', $this->generate_notification('warning', 'Username Already Exist!'));
					redirect($this->_data['form_url']);
				}

				if ($password == "" || $repeat_password == "") {
					$this->session->set_flashdata('notification', $this->generate_notification('warning', 'Please fill-up password fields!'));
					redirect($this->_data['form_url']);
				}

				if ($password != $repeat_password) {
					$this->session->set_flashdata('notification', $this->generate_notification('warning', 'Password not the same!'));
					redirect($this->_data['form_url']);
				}

				$password = hash("sha256", $password);
				$account_number = $this->generate_account_number("M");

				$bridge_id = $this->generate_code(
					array(
						'account_number' 		=> $account_number,
						'account_date_added'	=> "{$this->_today}",
						'oauth_bridge_parent_id'	=> $admin_oauth_bridge_id
					)
				);

				// do insert bridge id
				$this->bridges->insert(
					array(
						'oauth_bridge_id' 			=> $bridge_id,
						'oauth_bridge_parent_id'	=> $admin_oauth_bridge_id,
						'oauth_bridge_date_added'	=> $this->_today
					)
				);

				$insert_data = array(
					'merchant_number'		=> $merchant_number,
					'account_number'		=> $account_number,
					'account_fname'			=> $fname,
					'account_mname'			=> $mname,
					'account_lname'			=> $lname,
					'account_username'		=> $username,
					'account_password'		=> $password,
					'account_date_added'	=> $this->_today,
					'oauth_bridge_id'		=> $bridge_id,
				);

				$this->merchant_accounts->insert(
					$insert_data
				);

				$this->session->set_flashdata('notification', $this->generate_notification('success', 'Successfully Added!'));
				redirect($this->_data['form_url']);
			}
		}

		$this->_data['title']  = "New Merchant Account";
		$this->set_template("merchant_accounts/form", $this->_data);
	}

	public function update($account_number) {
		$admin_account_data_results = $this->_admin_account_data['results'];
		$admin_oauth_bridge_id		= $admin_account_data_results['oauth_bridge_id'];

		$this->_data['form_url']		= base_url() . "merchant-accounts/update/{$account_number}";
		$this->_data['notification'] 	= $this->session->flashdata('notification');
		$this->_data['is_update'] 		= true;

		$where = array(
			'oauth_bridge_parent_id' 	=> $admin_oauth_bridge_id,
			'account_number' 			=> $account_number
		);

		$inner_joints = array(
			array(
				'table_name' 	=> 'oauth_bridges',
				'condition'		=> 'oauth_bridges.oauth_bridge_id = merchant_accounts.oauth_bridge_id'
			)
		);

		$account_row = $this->merchant_accounts->get_datum(
			'',
			$where,
			array(),
			$inner_joints
		)->row();

		if ($account_row == "") {
			redirect(base_url() . "merchant-accounts");
		}

		$this->_data['post'] = array(
			'first-name' 	=> $account_row->account_fname,
			'middle-name' 	=> $account_row->account_mname,
			'last-name' 	=> $account_row->account_lname,
			'username' 		=> $account_row->account_username,
			'status'		=> $account_row->account_status == 1 ? "checked" : ""
		);

		$merchants = $this->get_merchants();

		$this->_data['merchants'] = $this->generate_selection(
			"merchant",
			$merchants, 
			$account_row->merchant_number, 
			"id", 
			"name", 
			true
		);

		if ($_POST) {
			if ($this->form_validation->run('validate')) {
				$merchant_number	= $this->input->post("merchant");
				$username 			= $this->input->post("username");
				$password 			= $this->input->post("password");
				$repeat_password 	= $this->input->post("repeat-password");
				$fname				= $this->input->post("first-name");
				$mname				= $this->input->post("middle-name");
				$lname				= $this->input->post("last-name");
				$status				= $this->input->post("status");

				if ($this->validate_username("merchant", $username, $account_number)) {
					$this->session->set_flashdata('notification', $this->generate_notification('warning', 'Username Already Exist!'));
					redirect($this->_data['form_url']);
				}

				if ($password != "" || $repeat_password != "") {
					if ($password != $repeat_password) {
						$this->session->set_flashdata('notification', $this->generate_notification('warning', 'Password not the same!'));
						redirect($this->_data['form_url']);
					}
				}

				$update_data = array(
					'merchant_number'	=> $merchant_number,
					'account_fname'		=> $fname,
					'account_mname'		=> $mname,
					'account_lname'		=> $lname,
					'account_username'	=> $username,
					'account_status'	=> $status == 1 ? 1 : 0
				);

				if ($password != "") {
					$password = hash("sha256", $password);

					$update_data = array_merge(
						$update_data,
						array(
							'account_password' => $password
						)
					);
				}

				$this->merchant_accounts->update(
					$account_number,
					$update_data
				);

				$this->session->set_flashdata('notification', $this->generate_notification('success', 'Successfully Updated!'));
				redirect($this->_data['form_url']);
			}
		}

		$this->_data['title']  = "Update Merchant Account";
		$this->set_template("merchant_accounts/form", $this->_data);
	}
}
