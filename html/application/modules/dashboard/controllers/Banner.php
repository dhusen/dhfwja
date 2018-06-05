<?php
defined('BASEPATH') OR exit('No direct script access allowed: Dashboard');
class Banner extends MY_Controller {
	public $is_editor = FALSE;
	public $error = FALSE, $error_msg = array();
	protected $DateObject;
	protected $email_vendor;
	function __construct() {
		parent::__construct();
		$this->load->helper('dashboard/dashboard_functions');
		$this->load->config('dashboard/base_dashboard');
		$this->base_dashboard = $this->config->item('base_dashboard');
		$this->email_vendor = (isset($this->base_dashboard['email_vendor']) ? $this->base_dashboard['email_vendor'] : '');
		$this->load->library('dashboard/Lib_authentication', $this->base_dashboard, 'authentication');
		$this->DateObject = $this->authentication->create_dateobject(ConstantConfig::$timezone, 'Y-m-d H:i:s', date('Y-m-d H:i:s'));
		$this->load->model('dashboard/Model_account', 'mod_account');
		$this->load->model('dashboard/Model_banner', 'mod_banner');
		$this->load->helper('security');
		$this->load->helper('form');
		$this->load->library('form_validation');
		if (($this->authentication->userdata != FALSE)) {
			if (in_array((int)$this->authentication->localdata['account_role'], base_config('editor_role'))) {
				$this->is_editor = TRUE;
			}
		}
	}
	private function accessDenied($collectData = null) {
		if (!isset($collectData)) {
			exit("This page is available if have collectData object.");
		}
		$collectData['page'] = 'error-access-denied';
		
		echo "<h1>Access Denied</h1>";
	}
	
	//=======================================================
	public function index() {
		$this->lists('home');
	}
	
	public function lists($banner_location = 'home') {
		$collectData = array(
			'banner_location'		=> (is_string($banner_location) ? strtolower($banner_location) : 'home'),
			'page'					=> 'banner-lists',
			'title'					=> 'Banner Slider',
			'base_path'				=> $this->imzers->base_path,
			'collect'				=> array(),
		);
		//================================
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) ? $collectData['search_text'] : '');
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		if (!$this->error) {
			$collectData['collect']['banner_location'] = $this->mod_banner->get_banner_location();
			$collectData['collect']['banner_location_data'] = $this->mod_banner->get_banner_location_by('location', $collectData['banner_location']);
			if (isset($collectData['collect']['banner_location_data']->location)) {
				try {
					$collectData['collect']['banner_items'] = array(
						'count'		=> $this->mod_banner->get_banner_slider_count_by('location', $collectData['collect']['banner_location_data']->location, $collectData['search_text']),
					);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while get all banner items on banner location: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			if (isset($collectData['collect']['banner_items']['count']->value)) {
				if ((int)$collectData['collect']['banner_items']['count']->value > 0) {
					$collectData['pagination'] = array(
						'page'		=> (isset($collectData['collect']['match']['params']['transaction']) ? $collectData['collect']['match']['params']['transaction'] : 1),
						'start'		=> 0,
					);
					$collectData['pagination']['page'] = (is_numeric($collectData['pagination']['page']) ? sprintf("%d", $collectData['pagination']['page']) : 1);
					if ($collectData['pagination']['page'] > 0) {
						$collectData['pagination']['page'] = (int)$collectData['pagination']['page'];
					} else {
						$collectData['pagination']['page'] = 1;
					}
					$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['banner_items']['count']->value);
				} else {
					$collectData['pagination'] = array(
						'page'		=> 1,
						'start'		=> 0,
					);
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/banner/lists/{$collectData['banner_location']}/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['banner_items']['count']->value, $collectData['pagination']['start']);
			try {
				$collectData['collect']['banner_items']['data'] = $this->mod_banner->get_banner_slider_data_by('location', $collectData['collect']['banner_location_data']->location, $collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'));
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error while get banner item data by banner-location with exception: {$ex->getMessage()}";
			}
		}
		//====== IF NOT ERROR
		if (!$this->error) {
			$this->load->view("{$this->imzers->base_path}/dashboard.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$error_to_show = "";
			foreach ($this->error_msg as $keval) {
				$error_to_show .= $keval;
			}
			$this->session->set_flashdata('action_message', $error_to_show);
			redirect(base_url($this->imzers->base_path . '/dashboard'));
			exit;
		}
		
	}
	function list_ajax($banner_location = 'home', $list_by = 'all') {
		$collectData = array(
			'banner_location'		=> (is_string($banner_location) ? strtolower($banner_location) : 'home'),
			'page'					=> 'banner-lists',
			'title'					=> 'Banner Slider',
			'base_path'				=> $this->imzers->base_path,
			'collect'				=> array(),
		);
		//================================
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) ? $collectData['search_text'] : '');
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		if (isset($list_by)) {
			$collectData['list_by'] = (is_string($list_by) ? strtolower($list_by) : 'all');
		} else {
			$collectData['list_by'] = 'all';
		}
		if (!$this->error) {
			$collectData['collect']['banner_location'] = $this->mod_banner->get_banner_location();
			$collectData['collect']['banner_location_data'] = $this->mod_banner->get_banner_location_by('location', $collectData['banner_location']);
			if (!isset($collectData['collect']['banner_location_data']->location)) {
				$this->error = true;
				$this->error_msg[] = "Banner locatione data not exists on database.";
			}
		}
		switch ($collectData['list_by']) {
			case 'active':
				$collectData['input_params'] = array(
					'banner_is_active'	=> 'Y',
				);
			break;
			case 'inactive':
				$collectData['input_params'] = array(
					'banner_is_active'	=> 'N',
				);
			break;
			case 'all':
			default:
				$collectData['input_params'] = array();
			break;
		}
		if (!$this->error) {
			$collectData['collect']['banner_items'] = $this->mod_banner->get_banner_slider_by('location', $collectData['collect']['banner_location_data']->location, $collectData['input_params']);
			$collectData['page'] = 'banner-lists-ajax';
			$this->load->view("{$this->imzers->base_path}/banner/banner-lists-ajax.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$error_to_show = "";
			foreach ($this->error_msg as $keval) {
				$error_to_show .= $keval;
			}
			$this->session->set_flashdata('action_message', $error_to_show);
			redirect(base_url($this->imzers->base_path . '/banner/lists'));
			exit;
		}
	}
	function add() {
		$collectData = array();
		$collectData['page'] = 'banner-add-slider';
		$collectData['title'] = 'Add Banner Slider';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//================================================================
		if (!$this->error) {
			$collectData['collect']['banner_location'] = $this->mod_banner->get_banner_location();
			if (count($collectData['collect']['banner_location'])) {
				foreach ($collectData['collect']['banner_location'] as $val) {
					$collectData['collect']['banner_location_data'] = $this->mod_banner->get_banner_location_by('location', $val->location);
				}
			}
			
			$this->load->view("{$this->imzers->base_path}/dashboard.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$error_to_show = "";
			foreach ($this->error_msg as $keval) {
				$error_to_show .= $keval;
			}
			$this->session->set_flashdata('action_message', $error_to_show);
			redirect(base_url($this->imzers->base_path . '/banner/lists'));
			exit;
		}
	}
	###############################################################################################################
	
	
	
	
	
	
	
	
	
	
	function additem() {
		$collectData = array();
		$collectData['page'] = 'menu-add-item';
		$collectData['title'] = 'Add Menu Item';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		} else {
			//================================================================
			$this->form_validation->set_rules('menu_title', 'Menu Title', 'required|max_length[64]|xss_clean');
			$this->form_validation->set_rules('menu_type', 'Menu Type', 'required|max_length[16]|trim|xss_clean');
			$this->form_validation->set_rules('menu_is_parent', 'Menu as Parent', 'required|max_length[1]|trim|xss_clean');
			$this->form_validation->set_rules('menu_order', 'Menu Ordering', 'numeric');
			$this->form_validation->set_rules('menu_is_active', 'Menu is Active', 'max_length[1]|trim|xss_clean');
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = "Form validation return error.";
				$collectData['collect']['form_validation'] = validation_errors('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>', '</div>');
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
				redirect(base_url("{$collectData['base_path']}/menu/add"));
				exit;
			} else {
				$collectData['query_params'] = array();
				$collectData['input_params'] = array(
					'menu_title' => $this->input->post('menu_title'),
					'menu_path' => $this->input->post('menu_path'),
					'menu_type' => $this->input->post('menu_type'),
					'menu_is_parent' => $this->input->post('menu_is_parent'),
					'menu_order' => $this->input->post('menu_order'),
					'menu_is_active' => $this->input->post('menu_is_active'),
				);
				$collectData['input_params']['menu_type'] = (is_string($collectData['input_params']['menu_type']) ? strtolower($collectData['input_params']['menu_type']) : 'top');
				try {
					$collectData['collect']['menu_type_data'] = $this->mod_menu->get_menu_type_by('code', $collectData['input_params']['menu_type']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while get menu-type-data by code: {$ex->getMessage()}";
				}
				if (!$this->error) {
					if (!isset($collectData['collect']['menu_type_data']->seq)) {
						$this->error = true;
						$this->error_msg[] = "Menu type data not exists on database.";
					} else {
						$collectData['menu_type'] = $collectData['collect']['menu_type_data']->type_code;
						$collectData['query_params']['menu_type'] = $collectData['collect']['menu_type_data']->seq;
					}
				}
				if (!$this->error) {
					if (is_string($collectData['input_params']['menu_is_parent'])) {
						if (!in_array($collectData['input_params']['menu_is_parent'], array('Y', 'N'))) {
							$collectData['query_params']['menu_parent'] = 0;
						} else {
							if ($collectData['input_params']['menu_is_parent'] === 'N') {
								$collectData['query_params']['menu_parent'] = 0;
								$collectData['query_params']['menu_is_parent'] = 'N';
							} else {
								$collectData['query_params']['menu_parent'] = 0; // Later ===========>
								$collectData['query_params']['menu_is_parent'] = 'Y'; // Later ===========>
							}
						}
					} else {
						$collectData['query_params']['menu_parent'] = 0;
					}
					if (is_string($collectData['input_params']['menu_is_active'])) {
						if (!in_array($collectData['input_params']['menu_is_active'], array('Y', 'N'))) {
							$collectData['query_params']['menu_is_active'] = 'N';
						} else {
							$collectData['query_params']['menu_is_active'] = $collectData['input_params']['menu_is_active'];
						}
					} else {
						$collectData['query_params']['menu_is_active'] = 'N';
					}
					if (is_numeric($collectData['input_params']['menu_order'])) {
						$collectData['query_params']['menu_order'] = (int)$collectData['input_params']['menu_order'];
					} else {
						$collectData['query_params']['menu_order'] = 0;
					}
					if (is_string($collectData['input_params']['menu_path'])) {
						$collectData['query_params']['menu_path'] = strtolower($collectData['input_params']['menu_path']);
					} else {
						$this->error = true;
						$this->error_msg[] = "Menu link path should be in string format.";
					}
				}
				if (!$this->error) {
					if (is_string($collectData['input_params']['menu_title'])) {
						$collectData['query_params']['menu_title'] = $this->imzers->safe_text_post($collectData['input_params']['menu_title'], 64);
						$collectData['query_params']['menu_slug'] = base_permalink($collectData['input_params']['menu_title']);
					} else {
						$this->error = true;
						$this->error_msg[] = "Menu title should be in string format.";
					}
				}
				if (!$this->error) {
					try {
						$collectData['menu_item_by_type'] = $this->mod_menu->get_menu_item_single_with_type_seq($collectData['query_params']['menu_type'], 'slug', $collectData['query_params']['menu_slug']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while checking menu-slug and menu-title with the same menu-type from database: {$ex->getMessage()}";
					}
				}
				if (!$this->error) {
					if ($collectData['menu_item_by_type'] != FALSE) {
						$this->error = true;
						$this->error_msg[] = "Menu item title on the menu-type already exists.";
					} else {
						$collectData['query_params']['menu_created_datetime'] = $this->DateObject->format('Y-m-d H:i:s');
						$collectData['query_params']['menu_edited_datetime'] = $this->DateObject->format('Y-m-d H:i:s');
						//==== Doing insert new menu with menu-type:
						try {
							$collectData['new_menu_item_seq'] = $this->mod_menu->insert_menu_item($collectData['query_params']);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Error exception while insert new menu item to menu-type with exception: {$ex->getMessage()}.";
						}
					}
				}
				if (!$this->error) {
					if ($collectData['new_menu_item_seq'] === 0) {
						$this->error = true;
						$this->error_msg[] = "Error while insert data to database: Isert menu item.";
					} else {
						//==== Redirect to Item Type Selected
						redirect(base_url($this->imzers->base_path . '/menu/lists/' . $collectData['collect']['menu_type_data']->type_code));
						exit;
					}
				}
				
				//======= ERROR PERSIST ========//
				if ($this->error) {
					echo "<pre>";
					print_r($this->error_msg);
				}
			}
			
		}
	}
	function edit($item_seq = 0) {
		$collectData = array();
		$collectData['page'] = 'menu-edit-item';
		$collectData['title'] = 'Edit Menu Item';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		} else {
			$collectData['item_seq'] = (is_numeric($item_seq) ? $item_seq : 0);
			try {
				$collectData['item_data'] = $this->mod_menu->get_menu_item_single_by('seq', $collectData['item_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot check item-data already exists or not.";
			}
		}
		//================================================================
		if (!$this->error) {
			if (!isset($collectData['item_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Item data not exists on database.";
			} else {
				$collectData['parent_menu_params'] = array(
					'menu_is_active' => 'Y',
					'menu_is_parent' => 'Y',
				);
				$collectData['collect']['menu_type'] = $this->mod_menu->get_menu_types();
				$collectData['collect']['menu_type_data'] = $this->mod_menu->get_menu_type_by('seq', $collectData['item_data']->menu_type);
				$collectData['collect']['menu_item_data'] = array(
					0 => $this->mod_menu->get_menu_item_single_by('seq', $collectData['item_data']->seq),
				);
				$collectData['collect']['parent_menu'] = $this->mod_menu->get_menu_item_by('menu_type', $collectData['item_data']->menu_type, $collectData['parent_menu_params']);
			}
		}
		if (!$this->error) {
			//====== IF NOT ERROR
			$collectData['page'] = 'menu-edit-item';
			$collectData['title'] = 'Edit Menu Item: ' . $collectData['item_data']->menu_title;
			$this->load->view("{$this->imzers->base_path}/dashboard.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$error_to_show = "";
			foreach ($this->error_msg as $keval) {
				$error_to_show .= $keval;
			}
			$this->session->set_flashdata('action_message', $error_to_show);
			redirect(base_url($this->imzers->base_path . '/menu/lists'));
			exit;
		}
	}
	function edititem($item_seq = 0) {
		$collectData = array();
		$collectData['page'] = 'menu-add-item';
		$collectData['title'] = 'Add Menu Item';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		} else {
			$collectData['item_seq'] = (is_numeric($item_seq) ? $item_seq : 0);
			try {
				$collectData['item_data'] = $this->mod_menu->get_menu_item_single_by('seq', $collectData['item_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot check item-data already exists or not.";
			}
			if (!$this->error) {
				if (!isset($collectData['item_data']->seq)) {
					$this->error = true;
					$this->error_msg[] = "Item data not exists on database.";
				}
			}
		}
		//================================================================
		if ($this->is_editor) {
			$this->form_validation->set_rules('menu_title', 'Menu Title', 'required|max_length[64]|xss_clean');
			$this->form_validation->set_rules('menu_type', 'Menu Type', 'required|max_length[16]|trim|xss_clean');
			$this->form_validation->set_rules('menu_is_parent', 'Menu as Parent', 'required|max_length[1]|trim|xss_clean');
			$this->form_validation->set_rules('menu_parent', 'Parent Menu', 'numeric|trim|xss_clean');
			$this->form_validation->set_rules('menu_order', 'Menu Ordering', 'numeric');
			$this->form_validation->set_rules('menu_is_active', 'Menu is Active', 'max_length[1]|trim|xss_clean');
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = "Form validation return error.";
				$collectData['collect']['form_validation'] = validation_errors('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>', '</div>');
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
				redirect(base_url("{$collectData['base_path']}/menu/edit/{$collectData['item_data']->seq}"));
				exit;
			}
			if ($this->form_validation->run() != FALSE) {
				$collectData['query_params'] = array();
				$collectData['input_params'] = array(
					'menu_title' => $this->input->post('menu_title'),
					'menu_path' => $this->input->post('menu_path'),
					'menu_type' => $this->input->post('menu_type'),
					'menu_is_parent' => $this->input->post('menu_is_parent'),
					'menu_parent' => $this->input->post('menu_parent'),
					'menu_order' => $this->input->post('menu_order'),
					'menu_is_active' => $this->input->post('menu_is_active'),
				);
				$collectData['input_params']['menu_type'] = (is_string($collectData['input_params']['menu_type']) ? strtolower($collectData['input_params']['menu_type']) : 'top');
				try {
					$collectData['collect']['menu_type_data'] = $this->mod_menu->get_menu_type_by('code', $collectData['input_params']['menu_type']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while get menu-type-data by code: {$ex->getMessage()}";
				}
				if (!$this->error) {
					if (!isset($collectData['collect']['menu_type_data']->seq)) {
						$this->error = true;
						$this->error_msg[] = "Menu type data not exists on database.";
					} else {
						$collectData['menu_type'] = $collectData['collect']['menu_type_data']->type_code;
						$collectData['query_params']['menu_type'] = $collectData['collect']['menu_type_data']->seq;
					}
				}
				if (!$this->error) {
					if (is_string($collectData['input_params']['menu_is_parent'])) {
						if (!in_array($collectData['input_params']['menu_is_parent'], array('Y', 'N'))) {
							$collectData['query_params']['menu_is_parent'] = 'N';
							$collectData['query_params']['menu_parent'] = 0;
						} else {
							if ($collectData['input_params']['menu_is_parent'] === 'N') {
								$collectData['query_params']['menu_is_parent'] = 'N';
							} else {
								$collectData['query_params']['menu_is_parent'] = 'Y';
							}
						}
						try {
							$collectData['menu_parent'] = $this->mod_menu->get_menu_item_single_by('seq', $collectData['input_params']['menu_parent']);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Error exception while fetch menu-parent-data selected: {$ex->getMessage()}";
						}
					} else {
						$this->error = true;
						$this->error_msg[] = "Menu is parent should be in string format.";
					}
				}
				if (!$this->error) {
					if (in_array($collectData['input_params']['menu_is_parent'], array('Y', 'N'))) {
						if (!isset($collectData['menu_parent']->seq)) {
							$collectData['query_params']['menu_parent'] = 0;
						} else {
							$collectData['query_params']['menu_parent'] = $collectData['menu_parent']->seq;
						}
					}
				}
				if (!$this->error) {
					if (is_string($collectData['input_params']['menu_is_active'])) {
						if (!in_array($collectData['input_params']['menu_is_active'], array('Y', 'N'))) {
							$collectData['query_params']['menu_is_active'] = 'N';
						} else {
							$collectData['query_params']['menu_is_active'] = $collectData['input_params']['menu_is_active'];
						}
					} else {
						$collectData['query_params']['menu_is_active'] = 'N';
					}
					if (is_numeric($collectData['input_params']['menu_order'])) {
						$collectData['query_params']['menu_order'] = (int)$collectData['input_params']['menu_order'];
					} else {
						$collectData['query_params']['menu_order'] = 0;
					}
					if (is_string($collectData['input_params']['menu_path'])) {
						$collectData['query_params']['menu_path'] = strtolower($collectData['input_params']['menu_path']);
					} else {
						$this->error = true;
						$this->error_msg[] = "Menu link path should be in string format.";
					}
				}
				if (!$this->error) {
					if (is_string($collectData['input_params']['menu_title'])) {
						$collectData['query_params']['menu_title'] = $this->imzers->safe_text_post($collectData['input_params']['menu_title'], 64);
						$collectData['query_params']['menu_slug'] = base_permalink($collectData['input_params']['menu_title']);
					} else {
						$this->error = true;
						$this->error_msg[] = "Menu title should be in string format.";
					}
				}
				if (!$this->error) {
					try {
						$collectData['menu_item_by_type'] = $this->mod_menu->get_menu_item_single_with_type_seq($collectData['query_params']['menu_type'], 'slug', $collectData['query_params']['menu_slug'], $collectData['item_data']->seq);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while checking menu-slug and menu-title with the same menu-type from database, exclude item-seq: {$ex->getMessage()}";
					}
				}
				if (!$this->error) {
					if ($collectData['menu_item_by_type'] != FALSE) {
						$this->error = true;
						$this->error_msg[] = "Menu item title on the menu-type already exists, please set another menu title.";
					} else {
						$collectData['query_params']['menu_edited_datetime'] = $this->DateObject->format('Y-m-d H:i:s');
						//==== Doing editing with menu-type:
						try {
							$collectData['edit_item_seq'] = $this->mod_menu->set_menu_item($collectData['item_data']->seq, $collectData['query_params']);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Error exception while editing menu item to menu-type with exception: {$ex->getMessage()}.";
						}
					}
				}
				if (!$this->error) {
					if ($collectData['edit_item_seq'] === 0) {
						$this->error = true;
						$this->error_msg[] = "Error while edit data to database: No affected rows.";
					} else {
						//==== Redirect to Item Menu Type
						redirect(base_url($this->imzers->base_path . '/menu/lists/' . $collectData['collect']['menu_type_data']->type_code));
						exit;
					}
				}
				//======= ERROR PERSIST ========//
				if ($this->error) {
					echo "<pre>";
					print_r($this->error_msg);
				}
			}
			
		}
	}
	function view($item_seq = 0) {
		$collectData = array();
		$collectData['page'] = 'menu-view-item';
		$collectData['title'] = 'View Menu Item';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		} else {
			$collectData['item_seq'] = (is_numeric($item_seq) ? $item_seq : 0);
			try {
				$collectData['collect']['menu_type'] = $this->mod_menu->get_menu_types();
				$collectData['collect']['menu_data'] = $this->mod_menu->get_menu_item_by('seq', $collectData['item_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get menu-item-data by item-seq.";
			}
		}
		//================================================================
		if (!$this->error) {
			//====== IF NOT ERROR
			$collectData['page'] = 'menu-view-item';
			$collectData['title'] = 'View Menu Item';
			$this->load->view("{$this->imzers->base_path}/dashboard.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$error_to_show = "";
			foreach ($this->error_msg as $keval) {
				$error_to_show .= $keval;
			}
			$this->session->set_flashdata('action_message', $error_to_show);
			redirect(base_url($this->imzers->base_path . '/menu/lists'));
			exit;
		}
	}
	
	#################################################################################################################

	function deleteaction($type = 'item') {
		$collectData = array();
		$collectData['page'] = 'addressbook-add';
		$collectData['title'] = 'Address Book';
		$collectData['base_path'] = $this->imzers->base_path;
		$collectData['collect'] = array();
		//================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['addressbook'] = array();
			if (!isset($collectData['collect']['userdata']['account_role'])) {
				$this->error = true;
				$this->error_msg[] = "System not detect account-role of logged-in user";
			}
			if (!$this->error) {
				if (!$this->is_user) {
					$this->error = true;
					$this->accessDenied($collectData);
				}
			}
			//================================
			$collectData['type'] = (is_string($type) ? strtolower($type) : 'item');
			$input_params = array();
			$user_params = array();
			//----
			$form_validation = TRUE;
			//----
			switch ($collectData['type']) {
				case 'group':
					$user_params['body'] = array(
						'seq' => (isset($this->crud['imzcustom']->php_input_request['body']['seq']) ? $this->crud['imzcustom']->php_input_request['body']['seq'] : 0),
						'parent_seq' => (isset($this->crud['imzcustom']->php_input_request['body']['parent_seq']) ? $this->crud['imzcustom']->php_input_request['body']['parent_seq'] : 0),
					);
					if (!is_numeric($user_params['body']['seq'])) {
						$user_params['body']['seq'] = 0;
					}
					if (!is_numeric($user_params['body']['parent_seq'])) {
						$user_params['body']['parent_seq'] = 0;
					}
					if ((strlen($user_params['body']['seq']) === 0) && (strlen($user_params['body']['parent_seq']) === 0)) {
						$form_validation = FALSE;
						$this->error_msg[] = "Input cannot be empty.";
					}
				break;
				case 'item':
				default:
					$user_params['body'] = array(
						'seq' => (isset($this->crud['imzcustom']->php_input_request['body']['seq']) ? $this->crud['imzcustom']->php_input_request['body']['seq'] : 0),
						'group_seq' => (isset($this->crud['imzcustom']->php_input_request['body']['group_seq']) ? $this->crud['imzcustom']->php_input_request['body']['group_seq'] : 0),
					);
					if (!is_numeric($user_params['body']['seq'])) {
						$user_params['body']['seq'] = 0;
					}
					if (!is_numeric($user_params['body']['group_seq'])) {
						$user_params['body']['group_seq'] = 0;
					}
					if ((strlen($user_params['body']['seq']) === 0) || (strlen($user_params['body']['group_seq']) === 0)) {
						$form_validation = FALSE;
						$this->error_msg[] = "Input cannot be empty.";
					}
				break;
			}
			//======================
			if (!$form_validation) {
				$this->imzers->session_data['error'] = TRUE;
				$error_string = "";
				if (count($this->error_msg) > 0) {
					foreach ($this->error_msg as $errorVal) {
						$error_string .= "- {$errorVal}";
					}
				}
				$this->imzers->session_data['action_message'] = $error_string;
				if ($collectData['type'] === 'item') {
					header('Location: ' . base_url("{$this->imzers->base_path}/index.php/addressbook/item/{$user_params['body']['seq']}"));
				} else {
					header('Location: ' . base_url("{$this->imzers->base_path}/index.php/addressbook/group/{$user_params['body']['seq']}"));
				}
				exit;
			} else {
				if (!$this->error) {
					switch ($collectData['type']) {
						case 'group':
							if ((int)$user_params['body']['parent_seq'] > 0) {
								$input_params['parent_seq'] = (int)$user_params['body']['parent_seq'];
							} else {
								$input_params['parent_seq'] = 0;
							}
							if ($input_params['parent_seq'] > 0) {
								if (!$this->mod_addressbook->check_group_parent_owner($input_params['parent_seq'], $this->authentication->localdata['seq'])) {
									$this->error = true;
									$this->error_msg[] = "Parent group is not your own, or sequence not really exists.";
								}
							}
						break;
						case 'item':
						default:
							if ((int)$user_params['body']['group_seq'] > 0) {
								$input_params['group_seq'] = (int)$user_params['body']['group_seq'];
							} else {
								$input_params['group_seq'] = 0;
							}
							if ($input_params['group_seq'] > 0) {
								if (!$this->mod_addressbook->check_group_parent_owner($input_params['group_seq'], $this->authentication->localdata['seq'])) {
									$this->error = true;
									$this->error_msg[] = "Group is not your own, or sequence not really exists.";
								}
							}
						break;
					}
				}
				if (!$this->error) {
					switch ($collectData['type']) {
						case 'group':
							$query_params = array(
								'group_parent_seq'		=> ((strlen($input_params['parent_seq']) > 0) ? $input_params['parent_seq'] : 0),
								'seq'					=> ((strlen($input_params['seq']) > 0) ? $input_params['seq'] : ''),
							);
						break;
						case 'item':
						default:
							$query_params = array(
								'group_seq'				=> ((strlen($input_params['group_seq']) > 0) ? $input_params['group_seq'] : 0),
								'seq'					=> ((strlen($input_params['seq']) > 0) ? $input_params['seq'] : ''),
							);
						break;
					}
				}
				
				
				
				
				
				
				
				
				
				
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/index.php/account/login"));
			exit;
		}
	}
}

















