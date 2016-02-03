<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google_php_client
{
	private $ci;
	private $config;

	public function __construct()
	{
		require_once APPPATH.'third_party/google-api-php-client-1.1.6/src/Google/autoload.php';

		$this->ci = &get_instance();
		$this->ci->config->load("google_client", TRUE);
		$this->config = $this->ci->config->item("google_client");
	}

	public function get_client($token = null)
	{
		$client = new Google_Client();
		$client->setClientId($this->config['CLIENT_ID']);
		$client->setClientSecret($this->config['CLIENT_SECRET']);
		$client->setRedirectUri(site_url("portal/oauth2/"));
		$client->setAccessType("offline");
		$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
		if(!is_null($token))
		{
			$client->setAccessToken($token);
		}
		return $client;
	}

	public function get_posts($user_company)
	{
		$client = $this->get_client($user_company->ga_token);
		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
			'ga:' . $user_company->view_id,
			'30daysAgo',
			'today',
			'ga:totalEvents',
			array(
				'dimensions' => 'ga:eventAction,ga:eventLabel',
				'sort' => '-ga:totalEvents',
				'max-results' => 25
			));

		return $res->getRows();
	}
}