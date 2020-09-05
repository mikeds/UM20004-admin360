<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients extends Admin_Controller {

	public function after_init() {
		$this->set_scripts_and_styles();

		$this->load->model('admin/tms_admins_model', 'admins');
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

		$total_rows = $this->admins->get_count();
		$offset = $this->get_pagination_offset($page, $this->_limit, $total_rows);
	    $results = $this->admins->get_data($select, array(), array(), array(), array('filter'=>'tms_admin_date_added', 'sort'=>'ASC'), $offset, $this->_limit);

		$this->_data['listing'] = $this->table_listing('', $results, $total_rows, $offset, $this->_limit, $actions, 4);
		$this->_data['title']  = "Clients";
		$this->set_template("clients/list", $this->_data);
	}
}
