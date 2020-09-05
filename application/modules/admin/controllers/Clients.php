<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients extends Admin_Controller {

	public function after_init() {
		$this->set_scripts_and_styles();

		$this->load->model('admin/oauth_bridges_model', 'bridges');
		$this->load->model('admin/tms_admins_model', 'clients');
	}

	public function index($page = 1) {
		$this->_data['add_label']= "New Client";
		$this->_data['add_url']	 = base_url() . "clients/new";

		$select = array(
			'tms_admin_number as id',
			'tms_admin_status as "Status"',
			'tms_admin_code as "Client Code"',
			'tms_admin_name as "Client Name"',
			'tms_admin_date_added as "Date Account"'
		);

		$actions = array(
			'update'
		);

		$total_rows = $this->clients->get_count();
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->clients->get_data($select, array(), array(), array(), array('filter'=>'tms_admin_date_added', 'sort'=>'ASC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 4);
		$this->_data['title']  = "Clients";
		$this->set_template("clients/list", $this->_data);
	}

	public function new() {
		$oauth_bridge_parent_id = $this->_account->oauth_bridge_id;

		$this->_data['form_url']		= base_url() . "clients/new";
		$this->_data['notification'] 	= $this->session->flashdata('notification');

		if ($_POST) {
			if ($this->form_validation->run('validate')) {
				$code = $this->input->post("code");
				$name = $this->input->post("name");

				$account_number = $this->generate_code(
					array(
						'oauth_bridge_parent_id' 	=> $oauth_bridge_parent_id,
						'tms_admin_date_added'		=> "{$this->_today}",
						'tms_admin_code'			=> $code
					)
				);

				$bridge_id = $this->generate_code(
					array(
						'tms_admin_number' 		=> $account_number,
						'tms_admin_date_added'	=> "{$this->_today}",
						'oauth_bridge_parent_id'=> $oauth_bridge_parent_id
					)
				);

				// do insert bridge id
				$this->bridges->insert(
					array(
						'oauth_bridge_id' 			=> $bridge_id,
						'oauth_bridge_parent_id'	=> $oauth_bridge_parent_id,
						'oauth_bridge_date_added'	=> "{$this->_today}"
					)
				);

				$insert_data = array(
					'tms_admin_number'		=> $account_number,
					'tms_admin_name'		=> $name,
					'tms_admin_code'		=> $code,
					'tms_admin_date_added'	=> $this->_today,
					'oauth_bridge_id'		=> $bridge_id,
				);

				$this->clients->insert(
					$insert_data
				);

				// create wallet address
				$this->create_wallet_address($account_number, $bridge_id, $oauth_bridge_parent_id);

				// create token auth for api
				$this->create_token_auth($account_number, $bridge_id);

				$this->session->set_flashdata('notification', $this->generate_notification('success', 'Successfully Added!'));
				redirect($this->_data['form_url']);
			}
		}

		$this->_data['title']  = "New Client";
		$this->set_template("clients/form", $this->_data);
	}
}
