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
		$keyString = 'oodash::'.md5(__FILE__) . '::' . json_encode($params); // make it unique per install

		/*foreach ($params as $piece) {
			$keyString .= (is_array($piece) ? implode('::', $piece) : $piece) . '::';
		}*/

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
		if(true)
		{
			$httpClient = new GuzzleHttp\Client([
					'proxy' => 'localhost:8888', // by default, Charles runs on localhost port 8888
					'verify' => false, // otherwise HTTPS requests will fail.
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
			$date_start = 'today';
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
			'ga:totalEvents',
			array(
				'dimensions' => 'ga:eventLabel',
				'sort' => '-ga:totalEvents',
				'filters' => $filters,
				'max-results' => $limit ? 25 : 10000
			));

		return $res->getRows();
	}

	public function get_post_stats_by_channel($url, $date_start = null, $date_end = null, $best = true)
	{
		if(is_null($date_start)) {
			$date_start = 'today';
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
			'ga:uniqueEvents, ga:totalEvents',
			array(
				'dimensions' => 'ga:channelGrouping',
				'sort' => ($best ? '-' : '') . 'ga:uniqueEvents',
				'filters' => 'ga:eventCategory==Author;ga:eventLabel=='.$url
			)
		);

		return $res->getRows();
	}

	/*public function get_posts_stats($date_start = 'today', $date_end = 'today')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEvents,ga:totalEvents',
			array(
				'dimensions' => 'ga:eventLabel',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}*/

	public function get_authors_stats($date_start = 'today', $date_end = 'today')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEvents,ga:totalEvents',
			array(
				'dimensions' => 'ga:eventAction',
				'sort' => '-ga:uniqueEvents',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}

	public function get_profile_stats($search_param = null, $date_start = 'today', $date_end = '30daysAgo')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

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

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEvents,ga:totalEvents',
			array(
				//'dimensions' => '',
				//'sort' => '',
				'filters' => $filters
			)
		);

		return $res->getRows();
	}

	public function get_graph_data($date_start = 'today', $date_end = 'today', $post_url = null)
	{
		$dimension = 'ga:date';
		if($date_start == $date_end)
		{
			$dimension = 'ga:hour';
		}

		$addFilters = '';
		if(!empty($post_url))
		{
			$addFilters .= ';ga:eventLabel=='.$post_url;
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
		if(($val = $this->ci->cache->get($key)) !== FALSE)
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