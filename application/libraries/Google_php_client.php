<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google_php_client
{
	private $ci;
	private $config;
	private $user_company;
	public $queries = 0;

	public static function make_key($params = array())
	{
		$keyString = 'oodash::gacache::' . json_encode($params); // make it unique per install

		return $keyString;
	}

	public function __construct($user_company = null)
	{
		require_once APPPATH.'third_party/google-api-php-client-2.1.1/vendor/autoload.php';
		require_once APPPATH.'third_party/Stash-0.14.1/autoload.php';

		$this->ci = &get_instance();
		$this->ci->config->load("google_client", TRUE);
		$this->config = $this->ci->config->item("google_client");
		$this->user_company = $user_company;
	}

	public function set_user_company($user_company)
	{
		$this->user_company = $user_company;
	}

	public function get_client()
	{
		$client = new Google_Client();
		$client->setClientId($this->config['CLIENT_ID']);
		$client->setClientSecret($this->config['CLIENT_SECRET']);
		$client->setRedirectUri(site_url("portal/oauth2/"));
		$client->setAccessType("offline");
		$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

		if(!is_null($this->user_company) && !is_null($this->user_company->ga_token))
		{
			$client->setAccessToken($this->user_company->ga_token);
		}

		$cache = new Stash\Pool(new Stash\Driver\Apc);
		$client->setCache($cache);

		// FOR DEBUGGING ONLY
		if(0)
		{
			$httpClient = new GuzzleHttp\Client([
				'proxy' => 'localhost:8888',
				'verify' => false,
			]);
			$client->setHttpClient($httpClient);
		}

		$this->queries++;

		return $client;
	}

	public function get_posts($date_start = NULL, $date_end = NULL, $limit = TRUE)
	{
		if($date_start == NULL)
		{
			$date_start = 'yesterday';
		}
		if($date_end == NULL)
		{
			$date_end = '30daysAgo';
		}

		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:totalEvents',
			array(
				'dimensions' => 'ga:eventLabel',
				'sort' => '-ga:totalEvents',
				'filters' => 'ga:eventCategory==Author',
				'max-results' => $limit ? 25 : 10000
			));

		return $res->getRows();
	}

	public function get_posts_stats($search_param = null, $date_start = NULL, $date_end = NULL, $limit = TRUE)
	{
		if($date_start == NULL)
		{
			$date_start = 'yesterday';
		}
		if($date_end == NULL)
		{
			$date_end = '30daysAgo';
		}

		$filters = 'ga:eventCategory==Author';
		if(is_array($search_param))
		{
			if(!empty($search_param['post_url']))
			{
				$filters .= ';ga:eventLabel=='.$search_param['post_url'];
			}
			if(!empty($search_param['search']))
			{
				$filters .= ';ga:pageTitle=@'.$search_param['search'];
			}
		}

		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEventsTemporary,ga:totalEvents',
			array(
				'dimensions' => 'ga:eventLabel',
				'sort' => '-ga:uniqueEventsTemporary',
				'filters' => $filters,
				'max-results' => $limit ? (is_numeric($limit) ? $limit : 25) : 10000
			));

		return $res->getRows();
	}

	public function get_post_stats_by_channel($url, $date_start = null, $date_end = null, $best = true)
	{
		if(is_null($date_start)) {
			$date_start = 'yesterday';
		}
		if(is_null($date_end)) {
			$date_end = '30daysAgo';
		}

		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEventsTemporary, ga:totalEvents',
			array(
				'dimensions' => 'ga:channelGrouping',
				'sort' => ($best ? '-' : '') . 'ga:uniqueEventsTemporary',
				'filters' => 'ga:eventCategory==Author;ga:eventLabel=='.$url
			)
		);

		return $res->getRows();
	}

	/*public function get_posts_stats($date_start = 'yesterday', $date_end = 'yesterday')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEventsTemporary,ga:totalEvents',
			array(
				'dimensions' => 'ga:eventLabel',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}*/

	public function get_authors_stats($date_start = 'yesterday', $date_end = 'yesterday')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEventsTemporary,ga:totalEvents',
			array(
				'dimensions' => 'ga:eventAction',
				'sort' => '-ga:uniqueEventsTemporary',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}

	public function get_stats($search_param = null, $date_start = 'yesterday', $date_end = '30daysAgo', $dimension = null, $sort = null, $limit = null)
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$filters = 'ga:eventCategory==Author';
		$page = 0;
		if(is_array($search_param))
		{
			if(!empty($search_param['post_url']))
			{
				$filters .= ';ga:eventLabel=='.$search_param['post_url'];
			}
			if(!empty($search_param['search']))
			{
				$filters .= ';ga:pageTitle=@'.$search_param['search'];
			}
			if(!empty($search_param['author']))
			{
				$filters .= ';ga:eventAction=='.$search_param['author'];
			}
			if(!empty($search_param['page']))
			{
				$page = intval($search_param['page']);
			}
		}

		if(!is_null($dimension))
		{
			if($dimension == 'date')
			{
				if($date_start == $date_end)
				{
					$dimension = 'hour';
					$sort = 'hour';
				}
				else
				{
					$sort = $dimension;
				}
			}
			$dimension = 'ga:'.$dimension;
		}

		if(!is_null($sort))
		{
			if(substr($sort, 0, 1) === '-')
			{
				$sort = '-ga:'.substr($sort, 1);
			}
			else
			{
				$sort = 'ga:'.$sort;
			}
		}

		$params = array(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEventsTemporary,ga:totalEvents',
			array(
				'dimensions' => $dimension,
				'sort' => $sort,
				'filters' => $filters,
				'start-index' => $page * (is_numeric($limit) ? $limit : 0) + 1,
				'max-results' => $limit ? (is_numeric($limit) ? $limit : 25) : 10000
			)
		);

		$key = Google_php_client::make_key($params);
		if(($val = $this->ci->cache->get($key)) !== FALSE && FALSE)
		{
			return $val;
		}

		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);
		$res = call_user_func_array(array($analytics->data_ga, 'get'), $params);

		//print_r($res);

		//$data = $res->getRows();
		//$this->ci->cache->save($key, $data, 600);

		return $res;
	}

	public function get_graph_data($search_params = array(), $date_start = 'yesterday', $date_end = 'yesterday')
	{
		$dimension = 'ga:date';
		if($date_start == $date_end)
		{
			$dimension = 'ga:hour';
		}

		$addFilters = '';
		if(!empty($search_params['post_url']))
		{
			$addFilters .= ';ga:eventLabel=='.$search_params['post_url'];
		}
		if(!empty($search_params['search']))
		{
			$addFilters .= ';ga:eventLabel=='.$search_params['search'];
		}

		$params = array(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:totalEvents',
			array(
				'dimensions' => $dimension,
				'sort' => $dimension,
				'filters' => 'ga:eventCategory==Author'.$addFilters
			)
		);

		$key = Google_php_client::make_key($params);
		if(($val = $this->ci->cache->get($key)) !== FALSE && FALSE)
		{
			return $val;
		}

		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);
		$res = call_user_func_array(array($analytics->data_ga, 'get'), $params);

		$data = $res->getRows();
		$this->ci->cache->save($key, $data, 600);

		return $data;
	}
}