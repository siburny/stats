<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends CI_Controller {
	private $user = NULL;
	private $user_company = NULL;

	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->lang->load('auth');
		$this->load->model("Company_model", "company");

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
	}

	function _remap()
	{
		$method = func_get_arg(0);
		if (method_exists($this, $method))
		{
			call_user_func_array(array($this, $method), func_get_arg(1));
		}
		else
		{
			$args = array_merge(array($method), func_get_arg(1));
			call_user_func_array(array($this, 'index'), $args);
		}
	}

	function index($page = "page1", $date_from = NULL, $date_to = NULL)
	{
		$data = array(
			"page_title" => "Welcome!"
		);

		if(preg_match('/^page[0-9]+$/i', $page))
		{
			$page = str_replace("page", "", strtolower($page));
			$page--;
		}
		else
			$page = 0;
		
		$date_link = "";
		if($date_from != NULL)
		{
			$date_from = strtolower($date_from);
			switch($date_from)
			{
				case "today":
				case "yesterday":
					$data['date_selected'] = $date_from;
					$date_link = $date_from."/";
					$date_to = new DateTime($date_from);
					$date_from = clone $date_to;
					break;
				case "7days":
					$data['date_selected'] = $date_from;
					$date_link = $date_from."/";
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-6 days');
					break;
				case "30days":
					$data['date_selected'] = $date_from;
					$date_link = $date_from."/";
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-29 days');
					break;
				default:
					if($date_to != NULL)
					{
						if(preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_from) && preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_to))
						{
							$data['date_selected'] = "custom";
							$data['date_from_input'] = $date_from;
							$data['date_to_input'] = $date_to;
							$date_link = $date_from.'/'.$date_to.'/';
							$date_from = DateTime::createFromFormat("m-d-Y", $date_from);
							$date_to = DateTime::createFromFormat("m-d-Y", $date_to);
							break;
						}
					}
					$data['date_selected'] = "";
					$date_to = NULL;
					break;
			}
		}
		if($date_to == NULL)
		{
			$date_to = (new DateTime());
			$date_from = clone $date_to;
			$date_from->modify('-29 days');
		}
		$data['date_from'] = $date_from->format("M j, Y");
		$data['date_to'] = $date_to->format("M j, Y");
		$data['date_from_ymd'] = $date_from->format('Y-m-d');
		$data['date_to_ymd'] = $date_to->format('Y-m-d');

		$this->load->model("Post_model", "post");
		
		$this->user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}

		$rows = Post_model::get_posts($this->user_company->company_id, $date_to, $date_from, TRUE, TRUE, $page);
		$day_diff = $date_to->diff($date_from)->days;
		$rows_prev = Post_model::get_posts($this->user_company->company_id, $date_from->modify('-1 days')->format("Y-m-d"), $date_from->modify("-".$day_diff." days")->format("Y-m-d"), FALSE, FALSE);
		$rows_prev = array_column((array)$rows_prev, 'total_pageviews', 'url');

		$data['rows'] = array();
		foreach($rows as $index => $row)
		{
			$prev = isset($rows_prev[$row->url]) ? $rows_prev[$row->url] : 0;
			if($prev && $row->total_pageviews - $prev)
			{
				$prev = round(100*($row->total_pageviews - $prev)/$prev, 1);
			}

			$ar = array(
				"n" => $page*10 + $index + 1,
				"image" => $row->image,
				"url" => $row->url,
				"title" => $row->title,
				"sessions" => $row->total_pageviews,
				"date_published" => date('M j, Y', strtotime($row->date_published)),
				"up_down_text" => $prev ? $prev."%" : "",
				'author' => $row->author
			);

			if($prev > 0)
			{
				$ar["up_arrow"] = TRUE;
			}
			elseif($prev < 0)
			{
				$ar["down_arrow"] = TRUE;
			}
			$data['rows'][] = $ar;
		}

		$rows = $this->db->from('post_stats')->
			select_max('date_updated')->get()->result_array();
		$data['last_updated'] = $rows[0]['date_updated'];
		
		//Total Stats
		$count = $this->db->from('posts')->
			where('company_id', $this->user_company->company_id)->
			where('date_published >=', $data['date_from_ymd'])->
			where('date_published <=', $data['date_to_ymd'])->
			count_all_results();
		$count_all = $this->db->from('posts')->
			where('company_id', $this->user_company->company_id)->
			count_all_results();
		$data['totals'] = array('pageviews' => 0, 'sessions' => 0, 'engaged_minutes' => 0, 'posts' => number_format($count), 'all_posts' => number_format($count_all));

		$this->load->library("google_php_client", $this->user_company);
		$rows = $this->google_php_client->get_profile_stats($data['date_to_ymd'], $data['date_from_ymd']);
		if($rows)
		{
			$data['totals']['sessions'] = number_format($rows[0][0]);
			$data['totals']['engaged_minutes'] = number_format($rows[0][0] * $rows[0][1] / 60);
			$data['totals']['pageviews'] = number_format($rows[0][2]);
		}

		$data['date_link'] = $date_link;
		$data['prev_link'] = $page == 0 ? "" : "/portal/page".$page."/".$date_link;
		$data['next_link'] = "/portal/page".($page+2)."/".$date_link;

		$this->parser->parse("portal/home", $data);
	}
	
	function connect()
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!"
		);

		if(is_null($this->user_company->ga_token))
		{
			$data["status"] = "Not connected [<a href='/portal/oauth2/'>CONNECT</a>]";
		}
		elseif(!is_null($this->user_company->view_id))
		{
			$data["status"] = "Connected! [<a href='/portal/connect_view/'>Change GA View</a>]";
		}
		else
		{
			redirect('/portal/connect_view/');
		}
			
		$this->parser->parse("portal/connect", $data);
	}

	function connect_view($account = NULL, $property = NULL, $view = NULL)
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!",
			'token' => array()
		);
		
		$this->load->library("google_php_client", $this->user_company);
		$client = $this->google_php_client->get_client();
		$analytics = new Google_Service_Analytics($client);

		if(is_null($account))
		{
			$data['selection'] = 'Account';
			$accounts = $analytics->management_accounts->listManagementAccounts();
			if (count($accounts->getItems()) > 0) {
				$items = $accounts->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No accounts found for this user.';
			}
		}
		elseif(is_null($property))
		{
			$data['selection'] = 'Property';
			$properties = $analytics->management_webproperties->listManagementWebproperties($account);
			if (count($properties->getItems()) > 0) {
				$items = $properties->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$account."/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No properties found for this account.';
			}
		}
		elseif(is_null($view))
		{
			$data['selection'] = 'View';
			$views = $analytics->management_profiles->listManagementProfiles($account, $property);
			if (count($views->getItems()) > 0) {
				$items = $views->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$account."/".$property."/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No views found for this property.';
			}
		}
		else
		{
			$this->user_company->view_id = $view;
			$this->company->update($this->user_company->company_id, array("view_id" => $view));
			$data['done'] = TRUE;
		}
		
		$data["hasTokens"] = count($data['token']) > 0;
		$this->parser->parse("portal/connect_view", $data);
	}
	
	function oauth2()
	{
		$this->load->library("google_php_client", $this->user_company);
		$client = $this->google_php_client->get_client();
		
		$code = $this->input->get('code');
		if (is_null($code)) {
			$auth_url = $client->createAuthUrl();
			redirect($auth_url);
		} else {
			$client->authenticate($this->input->get('code'));
			$token = $client->getAccessToken();
			$this->company->update($this->user_company->company_id, array("ga_token" => $token));
			
			redirect('/portal/connect/');
		}
	}

	function ga_code()
	{
		$data = array('page_title' => 'GA Code Generation');

		$this->parser->parse("portal/ga_code", $data);
	}

	function invite()
	{
		$this->load->model("Ion_auth_model");
		$this->load->library("ion_auth");

		$data = array('page_title' => 'User Invitation');

		$data['active_users'] = array();
		$data['invited_users'] = array();

		$users = $this->Ion_auth_model->select('id,email,created_on,active,first_name,last_name')->where('company', $this->user_company->company_id)->users()->result();
		foreach($users as $user)
		{
			$user->created_on_format = date("H:i:s m/d/Y", $user->created_on);
			$user->role = $this->ion_auth->in_group("manager", $user->id) ? "Manager" : "Author";
			$user->tracker = $this->ion_auth->in_group("manager", $user->id) ? "" : "Author";

			if($user->active)
			{
				$data['active_users'] = $user;
			}
			else
			{
				$data['invited_users'] = $user;
			}
		}

		$this->parser->parse("portal/invite", $data);
	}

	function invite_user()
	{
		$data = array('page_title' => 'User Invitation');

		$this->form_validation->set_rules('firstname', 'First Name', 'required');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('manager', 'Role', 'required');
		
		if($this->form_validation->run() == FALSE)
		{
			$data['errors'] = validation_errors('<li>', '</li>');

			$data['firstname'] = set_value('firstname');
			$data['lastname'] = set_value('lastname');
			$data['email'] = set_value('email');
			$data['position'] = set_value('position');

			$this->parser->parse("portal/invite_user", $data);
		}
		else
		{
		}
	}
}