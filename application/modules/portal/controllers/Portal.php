<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends MY_Controller {
	function __construct()
	{
		parent::__construct();

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");
	}

	private function _process_date(&$data)
	{
		$date_from = $this->input->get("date_from");
		$date_to = $this->input->get("date_to");

		if(empty($date_from) && empty($date_to) && !empty($_SESSION['date_from']))
		{
			$date_from = $_SESSION['date_from'];
			$date_to = isset($_SESSION['date_to']) ? $_SESSION['date_to'] : null;
		}
		else
		{
			$_SESSION['date_from'] = $date_from;
			$_SESSION['date_to'] = $date_to;
		}

		if($date_from != NULL)
		{
			$date_from = strtolower($date_from);
			switch($date_from)
			{
				case "today":
				case "yesterday":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
					$date_to = new DateTime($date_from);
					$date_from = clone $date_to;
					break;
				case "7days":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-6 days');
					break;
				case "30days":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
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
							$data['params']['date_from'] = $date_from;
							$data['params']['date_to'] = $date_to;
							$date_from = DateTime::createFromFormat("m-d-Y", $date_from);
							$date_to = DateTime::createFromFormat("m-d-Y", $date_to);
							break;
						}
					}
					$data['date_selected'] = '';
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

		$data['date_from_ymd'] = $date_from->format('Y-m-d');
		$data['date_to_ymd'] = $date_to->format('Y-m-d');
		$data['date_from'] = $date_from->format("M j, Y");
		if($data['date_from_ymd'] == $data['date_to_ymd'])
		{
			$data['date_to'] = "";
		}
		else
		{
			$data['date_to'] = $date_to->format("M j, Y");
		}
	}

	function index()
	{
		$this->load->library("google_php_client", $this->user_company);

		$this->parser->data['active_menu_posts'] = TRUE;

		$data = array(
			"page_title" => "Welcome!",
			"params" => array()
		);

		$search_param = array();

		$post_search = $this->input->get("search");
		if(!empty($post_search))
		{
			$data['post_search'] = $post_search;
			$data['uri_search'] = "search=".$post_search;
			$data['params']['search'] = $post_search;
			$search_param['search'] = $post_search;
		}

		$author = $this->input->get('author_name');
		if(!empty($author))
		{
			$data['author_name'] = $author;
			$data['uri_author'] = "author_name=".$author;
			$data['params']['author_name'] = $author;
			$search_param['author'] = $author;
		}

		$page = $this->input->get('page');
		if($page != null && preg_match('/^[0-9]+$/i', $page))
		{
			$page = str_replace("page", "", strtolower($page));
			$page--;
			if($page < 0)
			{
				$page = 0;
			}
		}
		else
			$page = 0;
		$data['params']['page'] = $page;
		$search_param['page'] = $page;

		$sort = $this->input->get('sort');
		if(isset($sort))
		{
			if($sort == 'sessions') {
				$sort = 'uniqueEvents';
			} elseif($sort == '-sessions') {
				$sort = '-uniqueEvents';
			} elseif($sort == 'pageviews') {
				$sort = 'totalEvents';
			} elseif($sort == '-pageviews') {
				$sort = '-totalEvents';
			}
		}
		else
		{
			$sort = '-uniqueEvents';
		}
		if($sort == '-uniqueEvents') {
			$data['sort_sessions'] = 'sessions';
			$data['sort_pageviews'] = '-pageviews';
		} elseif($sort == 'uniqueEvents') {
			$data['sort_sessions'] = '-sessions';
			$data['sort_pageviews'] = '-pageviews';
		} elseif($sort == '-totalEvents') {
			$data['sort_sessions'] = '-sessions';
			$data['sort_pageviews'] = 'pageviews';
		} elseif($sort == 'totalEvents') {
			$data['sort_sessions'] = '-sessions';
			$data['sort_pageviews'] = '-pageviews';
		}
		$data['sort_sessions_down'] = $sort == '-uniqueEvents';
		$data['sort_sessions_up'] = $sort == 'uniqueEvents';
		$data['sort_pageviews_down'] = $sort == '-totalEvents';
		$data['sort_pageviews_up'] = $sort == 'totalEvents';

		$this->_process_date($data);

		$this->load->model("Post_model", "post");

		$this->user = $this->ion_auth->user()->row();

		if($this->ion_auth->is_manager())
		{
			$data["is_admin"] = TRUE;
		}
		else
		{
			$search_param['author'] = $this->user->author_name;
		}

		//$day_diff = $date_to->diff($date_from)->days + 1;
		//$date_from_compare = clone $date_from;
		//$date_from_compare->modify("-".$day_diff." days")->format("Y-m-d");

		$res = $this->google_php_client->get_stats($search_param, $data['date_to_ymd'], $data['date_from_ymd'], 'eventLabel', $sort, 10);
		$rows = $res->getRows();

		$data['results_count'] = (1 + $page * 10)." - ".min($res['totalResults'], (1 + $page) * 10)." of ".$res['totalResults'];
		$more_available = !empty($res['nextLink']);
		//print_r($res);

		$rows_prev = array();
		//$rows_prev = Post_model::get_posts($search_param, $date_from->modify('-1 days')->format("Y-m-d"), $date_from->modify("-".$day_diff." days")->format("Y-m-d"), FALSE, FALSE);
		//$rows_prev = array_column((array)$rows_prev, 'total_pageviews', 'url');

		$data['rows'] = array();
		foreach($rows as $index => $row)
		{
			$prev = isset($rows_prev[$row[0]]) ? $rows_prev[$row[0]] : 0;
			if($prev && $row->total_pageviews - $prev)
			{
				$prev = round(100*($row->total_pageviews - $prev)/$prev, 1);
			}

			$post = $this->post->get_by('url', $row[0]);
			$ar = array(
				"post_id" => $post->post_id,
				"n" => $page*10 + $index + 1,
				"image" => $post->image,
				"url" => $post->url,
				"title" => $post->title,
				"sessions" => number_format($row[1]),
				"pageviews" => number_format($row[2]),
				"date_published" => date('M j, Y', strtotime($post->date_published)),
				"up_down_text" => $prev ? $prev."%" : "",
				'author' => $post->author
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

		$data['last_updated'] = date(DATE_RFC2822);

		//Total Stats
		$data['totals'] = array('pageviews' => 0, 'sessions' => 0);

		$rows = null;
		try {
			$rows = $this->google_php_client->get_stats($search_param, $data['date_to_ymd'], $data['date_from_ymd'])->getRows();
		}
		catch(Exception $e) {
		}
		if($rows)
		{
			$data['totals']['sessions'] = number_format($rows[0][0]);
			$data['totals']['pageviews'] = number_format($rows[0][1]);
		}

		$query = $data['params'];
		$data['prev_link'] = $page == 0 ? "" : "/portal/?".http_build_query($query);
		$query['page']++;
		$data['portal_link'] = http_build_query($query);
		$query['page']++;
		$data['next_link'] = $more_available ? "/portal/?".http_build_query($query) : "";

		$this->parser->parse("portal/home", $data);
	}

	function post()
	{
		$this->load->library("google_php_client", $this->user_company);

		$this->parser->data['active_menu_posts'] = TRUE;

		$data = array(
			"page_title" => "Welcome!",
			"params" => array()
		);

		$post_id = $this->input->get('post_id');
		if(empty($post_id) || !preg_match('/^[0-9]+$/i', $post_id))
		{
			redirect('/portal/');
		}

		$data['params']['post_id'] = $post_id;

		$page = $this->input->get('page');
		if($page != null && preg_match('/^[0-9]+$/i', $page))
		{
			$page = str_replace("page", "", strtolower($page));
			$page--;
		}
		else
			$page = 0;
		$data['params']['page'] = $page;

		$this->_process_date($data);

		$this->load->model("Post_model", "post");

		$this->user = $this->ion_auth->user()->row();

		$post = $this->post->get($post_id);
		$data['post_id'] = $post->post_id;
		$data['post_title'] = $post->title;
		$data['post_url'] = $post->url;
		$data['post_author'] = $post->author;
		$data['post_thumb'] = $post->image;
		$data['post_date'] = date("F jS, Y", strtotime($post->date_published));

		$data['uri_post_url'] = 'url='.$post->url;

		$rows = $this->google_php_client->get_post_stats_by_channel($post->url, $data['date_to_ymd'], $data['date_from_ymd']);
		$data['rows'] = array();
		foreach($rows as $index => $row)
		{
			$ar = array(
				"n" => $index+1,
				"source" => $row[0],
				"sessions" => number_format($row[1]),
				"pageviews" => number_format($row[2])
			);

			$data['rows'][] = $ar;
		}

		$data['last_updated'] = date(DATE_RFC2822);

		$data['totals'] = array('pageviews' => 0, 'sessions' => 0);
		$rows = $this->google_php_client->get_stats(array('post_url' => $data['post_url']), $data['date_to_ymd'], $data['date_from_ymd'])->getRows();
		if($rows)
		{
			$data['totals']['sessions'] = number_format($rows[0][0]);
			$data['totals']['pageviews'] = number_format($rows[0][1]);
		}

		$query = $data['params'];
		unset($query['page']);
		$data['param_link'] = http_build_query($query);

		$this->parser->parse("portal/post", $data);
	}

	function authors()
	{
		if(!$this->ion_auth->is_manager())
		{
			redirect('/portal/');
		}

		$this->parser->data['active_menu_authors'] = TRUE;

		$this->load->library("google_php_client", $this->user_company);

		$data = array(
			"page_title" => "Author Stats!",
			"params" => array()
		);

		$this->_process_date($data);

		$this->load->model("Post_model", "post");

		$this->user = $this->ion_auth->user()->row();

		$rows = $this->google_php_client->get_authors_stats($data['date_to_ymd'], $data['date_from_ymd']);

		$data['rows'] = array();
		foreach($rows as $index => $row)
		{
			$ar = array(
				"n" => $index+1,
				"author" => $row[0],
				"sessions" => number_format($row[1]),
				"pageviews" => number_format($row[2])
			);

			$data['rows'][] = $ar;
		}

		$data['last_updated'] = date(DATE_RFC2822);

		//Total Stats
		$data['totals'] = array('pageviews' => 0, 'sessions' => 0);
		$rows = null;
		try {
			$rows = $this->google_php_client->get_stats(array(), $data['date_to_ymd'], $data['date_from_ymd'])->getRows();
		}
		catch(Exception $e) {
		}
		if($rows)
		{
			$data['totals']['sessions'] = number_format($rows[0][0]);
			$data['totals']['pageviews'] = number_format($rows[0][1]);
		}


		$this->parser->parse("portal/authors", $data);
	}


	/*function _author()
	{
		$this->parser->data['active_menu_posts'] = TRUE;

		$data = array(
			"page_title" => "Welcome!",
			"params" => array()
		);

		$author = $this->input->get('author_name');
		if(empty($author))
		{
			redirect('/portal/');
		}
		$data['params']['author_name'] = $author;

		$page = $this->input->get('page');
		if($page != null && preg_match('/^[0-9]+$/i', $page))
		{
			$page = str_replace("page", "", strtolower($page));
			$page--;
		}
		else
			$page = 0;
		$data['params']['page'] = $page;

		$date_from = $this->input->get("date_from");
		$date_to = $this->input->get("date_to");

		if($date_from != NULL)
		{
			$date_from = strtolower($date_from);
			switch($date_from)
			{
				case "today":
				case "yesterday":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
					$date_to = new DateTime($date_from);
					$date_from = clone $date_to;
					break;
				case "7days":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-6 days');
					break;
				case "30days":
					$data['date_selected'] = $date_from;
					$data['params']['date_from'] = $date_from;
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
							$data['params']['date_from'] = $date_from;
							$data['params']['date_to'] = $date_to;
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

		if($this->ion_auth->is_manager())
		{
			$data["is_admin"] = TRUE;
		}
		$company_id = array($this->user_company->company_id, $author);

		$rows = Post_model::get_posts($company_id, $date_to, $date_from, TRUE, TRUE, $page);
		$day_diff = $date_to->diff($date_from)->days;
		$rows_prev = Post_model::get_posts($company_id, $date_from->modify('-1 days')->format("Y-m-d"), $date_from->modify("-".$day_diff." days")->format("Y-m-d"), FALSE, FALSE);
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
				"post_id" => $row->post_id,
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
		$data['totals'] = array('pageviews' => 0, 'sessions' => 0, 'posts' => number_format($count), 'all_posts' => number_format($count_all));

		$this->load->library("google_php_client", $this->user_company);
		$rows = $this->google_php_client->get_profile_stats($data['date_to_ymd'], $data['date_from_ymd']);
		if($rows)
		{
			$data['totals']['sessions'] = number_format($rows[0][0]);
			$data['totals']['pageviews'] = number_format($rows[0][1]);
		}

		$query = $data['params'];
		$data['prev_link'] = $page == 0 ? "" : "/portal/author/?".http_build_query($query);
		$query['page']++;
		$data['portal_link'] = http_build_query($query);
		$query['page']++;
		$data['next_link'] = "/portal/author/?".http_build_query($query);

		unset($query['page']);
		$data['date_link'] = http_build_query($query);

		$this->parser->parse("portal/author", $data);
	}*/

	function connect()
	{
		$this->parser->data['active_menu_data'] = TRUE;

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
		$this->parser->data['active_menu_data'] = TRUE;

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
		$this->parser->data['active_menu_data'] = TRUE;

		$data = array('page_title' => 'GA Code Generation');

		$this->parser->parse("portal/ga_code", $data);
	}

	function invite()
	{
		$this->parser->data['active_menu_users'] = TRUE;

		if(!$this->ion_auth->is_manager())
		{
			redirect('/portal/');
		}

		$this->load->model("Ion_auth_model");

		$data = array('page_title' => 'User Invitation');
		$this->input->get('done') && $data['message'] = 'Invite has been sent to '.htmlentities($this->input->get('done'));

		$data['active_users'] = array();
		$data['invited_users'] = array();

		$users = $this->Ion_auth_model->select('id,email,created_on,active,first_name,last_name')
			->where('company', $this->user_company->company_id)->where('id <> '.$this->user->id)
			->users()->result();

		foreach($users as $user)
		{
			$user->created_on_format = date("H:i:s m/d/Y", $user->created_on);
			$user->role = $this->ion_auth->is_manager($user->id) ? "Manager" : "Author";
			$user->tracker = $this->ion_auth->is_manager($user->id) ? "" : "Author";
			$this->ion_auth->is_admin($user->id) && $user->protected = TRUE;

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
		$this->form_validation->set_rules('firstname', 'First Name', 'required');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('manager', 'Role', 'required');

		if($this->input->post('manager') !== '1') {
			$this->form_validation->set_rules('author', 'Tracker Name', 'required');
		}


		if($this->form_validation->run() == FALSE)
		{
			$data = array('page_title' => 'User Invitation');
			$data['errors'] = validation_errors('<li>', '</li>');

			$data['firstname'] = set_value('firstname');
			$data['lastname'] = set_value('lastname');
			$data['email'] = set_value('email');
			$data['position'] = set_value('position');
			$data['manager'] = set_value('manager', '1');

			$this->load->model("post_model", "posts");
			$data['names'] = $this->posts->get_authors($this->user_company->company_id);

			$this->parser->parse("portal/invite_user", $data);
		}
		else
		{
			$this->load->library("ion_auth");
			$this->ion_auth->invite(set_value('email'),
				array(
					'first_name' => $this->input->post('firstname'),
					'last_name' => $this->input->post('lastname'),
					'position' => $this->input->post('position'),
					'company' => $this->user_company->company_id,
					'author_name' => $this->input->post('author')
				),
				array($this->input->post('manager') ? '2' : '3'),
				trim($this->input->post('custom_message')));
			redirect('/portal/invite?done='.urlencode(set_value('email')));
		}
	}

	function cancel($id = null)
	{
		if(is_null($id))
		{
			redirect('/portal/invite/');
		}

		if($this->ion_auth->is_manager())
		{
			$this->ion_auth->delete_user($id);
			redirect('/portal/invite/');
		}
		else
		{
			redirect('/portal/');
		}
	}
}