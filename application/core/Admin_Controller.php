<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CMS_Controller class
 * Base controller ?
 *
 * @author Marknel Pineda
 */
class Admin_Controller extends Global_Controller {

	private 
		$_stylesheets = array(),
		$_scripts = array();
	
	protected
		$_base_controller = "admin",
		$_base_session = "session",
		$_data = array(), // shared data with child controller
		$_limit = 20,
		$_account = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize all configs, helpers, libraries from parent
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		$this->_today = date("Y-m-d H:i:s");

		$this->validate_login();
		$this->setup_account();
		$this->setup_nav_sidebar_menu();
		$this->after_init();
	}

	private function setup_account() {
		$this->load->model("admin/accounts_model", "accounts");
		$account = $this->session->userdata("{$this->_base_session}");

		if (!isset($account['id'])) {
			redirect(base_url() . "logout");
		}

		$id = $account['id'];

		$row = $this->accounts->get_datum(
			'',
			array(
				'account_number' => $id,
				'account_status' => 1 // activated
			)
		)->row();

		if ($row == "") {
			redirect(base_url() . "logout");
		}

		$this->_account = $row;
	}

	public function validate_login() {
		$login_url = base_url() . "login";

        $controller = strtolower(get_controller());
		if(empty($this->session->userdata("{$this->_base_session}")) && $controller != 'login' ) {
            $this->session->unset_userdata("{$this->_base_session}");
			$this->session->sess_destroy();
            redirect($login_url);
        } else if(!empty($this->session->userdata("{$this->_base_session}"))) {
			$member_session = $this->session->userdata("{$this->_base_session}");
		}
	}

	public function create_wallet_address($account_number, $bridge_id, $oauth_bridge_parent_id) {
		$this->load->model('admin/wallet_addresses_model', 'wallet_addresses');

		// add address
		$wallet_address = $this->generate_code(
			array(
				'account_number' 				=> $account_number,
				'oauth_bridge_id'				=> $bridge_id,
				'wallet_address_date_created'	=> $this->_today,
				'admin_oauth_bridge_id'			=> $oauth_bridge_parent_id
			)
		); 

		// create wallet address
		$this->wallet_addresses->insert(
			array(
				'wallet_address' 				=> $wallet_address,
				'wallet_balance'				=> openssl_encrypt(0, $this->_ssl_method, getenv("BPKEY")),
				'wallet_hold_balance'			=> openssl_encrypt(0, $this->_ssl_method, getenv("BPKEY")),
				'oauth_bridge_id'				=> $bridge_id,
				'wallet_address_date_created'	=> $this->_today
			)
		);
	}

	public function create_token_auth($account_number, $bridge_id) {
		$this->load->model('admin/oauth_clients_model', 'oauth_clients');

		// create api token
		$this->oauth_clients->insert(
			array(
				'client_id' 		=> $bridge_id,
				'client_secret'		=> $this->generate_code(
					array(
						'account_number'	=> $account_number,
						'date_added'		=> $this->_today,
						'oauth_bridge_id'	=> $bridge_id
					)
				),
				'oauth_bridge_id'	=> $bridge_id,
				'client_date_added'	=> $this->_today
			)
		);
	}

	public function validate_username($type, $username, $id = "") {
		$flag = false;

		$this->load->model('admin/accounts_model', 'accounts');
		$this->load->model('admin/tms_admin_accounts_model', 'admin_accounts');
		$this->load->model('admin/merchant_accounts_model', 'merchant_accounts');

		$ccount_row = $this->admin_accounts->get_datum(
			'',
			array(
				'account_username' => $username
			)
		)->row();

		if ($ccount_row != "") {
			$acc_id = $ccount_row->account_number;

			if ($type == "admin" && $id != "") {
				if ($acc_id == $id) {
					$flag = false;
				} else {
					$flag = true;
					goto end;
				}
			} else {
				$flag = true;
				goto end;
			}
		}

		$tms_admin_account_row = $this->admin_accounts->get_datum(
			'',
			array(
				'account_username' => $username
			)
		)->row();

		if ($tms_admin_account_row != "") {
			$acc_id = $tms_admin_account_row->account_number;

			if ($type == "tms_admin" && $id != "") {
				if ($acc_id == $id) {
					$flag = false;
				} else {
					$flag = true;
					goto end;
				}
			} else {
				$flag = true;
				goto end;
			}
		}

		$merchant_account_row = $this->merchant_accounts->get_datum(
			'',
			array(
				'account_username' => $username
			)
		)->row();

		if ($merchant_account_row != "") {
			$acc_id = $merchant_account_row->account_number;

			if ($type == "merchant" && $id != "") {
				if ($acc_id == $id) {
					$flag = false;
				} else {
					$flag = true;
					goto end;
				}
			} else {
				$flag = true;
				goto end;
			}
		}

		end:

		return $flag;
	}

	public function generate_code($data) {
		$json = json_encode($data);
		return hash_hmac("sha256", $json, getenv("SYSKEY"));
	}

	public function generate_image_gallery($images_data) {
		$content = "";
		
		foreach ($images_data as $key => $image_datum) {
			$index = $key + 1;
			$id = $image_datum['id'];
			$base64_image = $image_datum['base64_image'];
			$confirmation_delete_url = base_url() . "marketplace/products/confirmation-remove/image-{$id}";
$content .= <<<HTML
			<tr>
				<th scope="row">$index</th>
				<td><img src="data:image/png;base64,{$base64_image}" class="img-thumbnail"></td>
				<td>
					<div class="row">
						<div class="col-xl-12">
							<div class="form-group">
								<a href="{$confirmation_delete_url}" class="btn btn-block btn-warning" title="Update" role="button">
									<span class="mdi mdi-delete">REMOVE</span>
								</a>
							</div>
						</div>
					</div>
				</td>
			</tr>
HTML;
		}

$HTML = <<<HTML
			<table class="table table-dark">
				<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Image</th>
						<th scope="col">Action</th>
					</tr>
				</thead>
				<tbody class="image-gallery">
					$content
				</tbody>
			</table>
HTML;

		return $HTML;
	}

	public function upload_files($files, $title, $file_size_limit = 20, $allowed_types = "jpg|jpeg|JPG|JPEG|PNG|png") {
		$upload_path = "{$this->_upload_path}/images";
        $config = array(
            'upload_path'   => $upload_path,
            'allowed_types' => $allowed_types,
            'overwrite'     => 1,                       
        );

        $this->load->library('upload', $config);

        $items = array();

		$error_images = array();

		// validate first the file size 20M limit per image
		foreach ($files['name'] as $key => $file) {
			$file_size = $files['size'][$key];

			if ($file_size > ($file_size_limit * MB)) {
				$error_images[] = $files['name'][$key];
			}
		}

		if (!empty($error_images)) {
			return array(
				'error' => true,
				'error_message' => "Image(s) is/are exceeded 20MB size.",
				'error_images' => $error_images,
			);
		}

		$error_upload = array();
		$data = array();

        foreach ($files['name'] as $key => $file) {
            $_FILES['files[]']['name']= $files['name'][$key];
            $_FILES['files[]']['type']= $files['type'][$key];
            $_FILES['files[]']['tmp_name']= $files['tmp_name'][$key];
            $_FILES['files[]']['error']= $files['error'][$key];
            $_FILES['files[]']['size']= $files['size'][$key];
			

			$ext = explode(".", $file);
			$ext = isset($ext[count($ext) - 1]) ? $ext[count($ext) - 1] : ""; 

			$today = strtotime($this->_today);
			$image_id = "{$title}_{$key}_{$today}";
            $file_name =  "{$image_id}.{$ext}";

            $items[] = $file_name;

            $config['file_name'] = $file_name;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('files[]')) {
				$this->upload->data();

				// get file uploaded
				$full_path 		= "{$upload_path}/{$file_name}";
				$filecontent 	= file_get_contents($full_path);

				// update image save base64
				$data[] = array(
					'image_id' => $image_id,
					'base64_image' => rtrim(base64_encode($filecontent))
				);

				// delete uploaded image
				if(file_exists($full_path)){
					unlink($full_path);
				}
            } else {
				$error_upload[] = array(
					'error_image' => $files['name'][$key],
					'error_message' => $this->upload->display_errors()
				);
            }
        }

		return empty($error_upload) ? 
			array(
				'results' => $data
			): 
			array(
				'error' => true,
				'error_data' => $error_upload
			);
    }

	public function resize_image($image_source, $new_width = 2048) {
		$this->load->library('image_lib');

		$image_resize_config =  array(
			'image_library'   => 'gd2',
			'source_image'    =>  $image_source,
			'maintain_ratio'  =>  true,
			'width'           =>  $new_width,
			// 'height'          =>  300,
		);

		$this->image_lib->clear();
		$this->image_lib->initialize($image_resize_config);
		$this->image_lib->resize();
		return file_get_contents($image_source);
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
			'menu_id'			=> 'accounts',
			'menu_title'		=> 'Accounts',
			'menu_url'			=> 	base_url() . "accounts",
			'menu_controller'	=> 'accounts',
			'menu_icon'			=> 'view-dashboard',
		);

		$menu_items[] = array(
			'menu_id'			=> 'clients',
			'menu_title'		=> 'Clients',
			'menu_url'			=> 	base_url() . "clients",
			'menu_controller'	=> 'clients',
			'menu_icon'			=> 'view-dashboard',
		);

		$menu_items[] = array(
			'menu_id'			=> 'client-accounts',
			'menu_title'		=> 'Client Accounts',
			'menu_url'			=> 	base_url() . "client-accounts",
			'menu_controller'	=> 'client_accounts',
			'menu_icon'			=> 'view-dashboard',
		);

		$this->_data['nav_sidebar_menu'] = $this->generate_sidebar_items($menu_items);
	}

	public function set_scripts_and_styles() {
		$this->add_styles(base_url() . "assets/frameworks/majestic-admin/vendors/mdi/css/materialdesignicons.min.css", true);
		$this->add_styles(base_url() . "assets/frameworks/majestic-admin/vendors/base/vendor.bundle.base.css", true);
		$this->add_styles(base_url() . "assets/frameworks/majestic-admin/css/style.css", true);
		$this->add_styles(base_url() . "assets/{$this->_base_controller}/css/style.css", true);

		// inject:js
		$this->add_scripts(base_url() . "assets/frameworks/majestic-admin/vendors/base/vendor.bundle.base.js", true);
		$this->add_scripts(base_url() . "assets/frameworks/majestic-admin/js/off-canvas.js", true);
		$this->add_scripts(base_url() . "assets/frameworks/majestic-admin/js/hoverable-collapse.js", true);
		$this->add_scripts(base_url() . "assets/frameworks/majestic-admin/js/template.js", true);
		// endinject

		$this->add_scripts(base_url() . "assets/{$this->_base_controller}/js/scripts.js", true);
	}
}
