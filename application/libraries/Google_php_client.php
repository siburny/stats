<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google_php_client
{
	private $ci;
	private $config;
	private $user_company;
	public $queries = 0;

	public function __construct($user_company = null)
	{
		require_once APPPATH.'third_party/google-api-php-client-2.1.1/vendor/autoload.php';

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

	public function get_post_stats($url, $date_start = 'today', $date_end = '30daysAgo')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEvents',
			array(
				'dimensions' => 'ga:channelGrouping',
				'sort' => '-ga:uniqueEvents',
				'filters' => 'ga:eventCategory==Author;ga:eventLabel=='.$url,
				'max-results' => 25
			)
		);

		return $res->getRows();
	}

	public function get_posts_stats($date_start = 'today', $date_end = 'today')
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
	}

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

	public function get_profile_stats($date_start = 'today', $date_end = '30daysAgo')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_end,
			$date_start,
			'ga:uniqueEvents,ga:avgSessionDuration,ga:totalEvents',
			array(
				//'dimensions' => '',
				//'sort' => '',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}

	public function get_posts_stats_by_hour($date_start = 'today')
	{
		$client = $this->get_client();
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $this->user_company->view_id,
			$date_start,
			$date_start,
			'ga:totalEvents',
			array(
				'dimensions' => 'ga:hour',
				'sort' => 'ga:hour',
				'filters' => 'ga:eventCategory==Author'
			)
		);

		return $res->getRows();
	}
}