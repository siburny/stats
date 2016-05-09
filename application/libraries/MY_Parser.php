<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * @author Ivan Tcholakov <ivantcholakov@gmail.com>, 2013-2016
 * @license The MIT License, http://opensource.org/licenses/MIT
 */

/*
// Sample class autoloading if you have no your own way.
if (!class_exists('Mustache_Autoloader', FALSE))
{
    require_once APPPATH.'third_party/Mustache/Autoloader.php';
    Mustache_Autoloader::register();
}
*/

class MY_Parser extends CI_Parser {

    protected $config;
    private $ci;
		public $data = array();

    function __construct()
    {
        $this->ci =& get_instance();

				if (!class_exists('Mustache_Autoloader', FALSE))
				{
						require_once APPPATH.'third_party/Mustache/Autoloader.php';
						Mustache_Autoloader::register();
				}

				define('MUSTACHE_CACHE', APPPATH.'cache/mustache/');
				file_exists(MUSTACHE_CACHE) OR @mkdir(MUSTACHE_CACHE, 0755, TRUE);
 				
        // Default configuration options.
        $this->config = array(
            'extension' => '.php',
            'cache' => MUSTACHE_CACHE,
            'cache_file_mode' => FILE_WRITE_MODE,
            'escape' => null,
            'charset' => null,
            'entity_flags' => ENT_COMPAT,
            'full_path' => FALSE,
        );

        if ($this->ci->config->load('parser_mustache', TRUE, TRUE))
        {
            $this->config = array_merge($this->config, $this->ci->config->item('parser_mustache'));
        }

        // Injecting configuration options directly.

        if (isset($this->_parent) && !empty($this->_parent->params) && is_array($this->_parent->params))
        {
            $this->config = array_merge($this->config, $this->_parent->params);

            if (array_key_exists('parser_driver', $this->config))
            {
                unset($this->config['parser_driver']);
            }
        }

        log_message('info', 'MY_Parser Class Initialized');
    }

    public function parse($template, $data = array(), $return = FALSE, $options = array())
    {
        if (!is_array($options))
        {
            $options = array();
        }

        $options = array_merge($this->config, $options);

        if (!isset($options['charset']) || trim($options['charset']) == '')
        {
            $options['charset'] = $this->ci->config->item('charset');
        }

        $options['charset'] = strtoupper($options['charset']);

        if (!is_array($data))
        {
            $data = array();
        }
				$data = array_merge($this->data, $data);

				$options['partials_loader'] = new Mustache_Loader_FilesystemLoader(APPPATH.'/views', array('extension' => '.php'));
        $parser = new Mustache_Engine($options);
				
				$template = $this->ci->load->view($template, null, TRUE);
        $template = $parser->render($template, $data);

				if( ! $return)
				{
					$this->ci->output->append_output($template);
					return "";
				}
			return $template;
		}

    public function parse_string($template, $data = array(), $return = FALSE, $options = array())
    {
        if (!is_array($options))
        {
            $options = array();
        }

        $options = array_merge($this->config, $options);

        if (!isset($options['charset']) || trim($options['charset']) == '')
        {
            $options['charset'] = $this->ci->config->item('charset');
        }

        $options['charset'] = strtoupper($options['charset']);

        if (!is_array($data))
        {
            $data = array();
        }
				$data = array_merge($this->data, $data);

        $parser = new Mustache_Engine($options);
        $template = $parser->render($template, $data);

				if( ! $return)
				{
					$this->ci->output->append_output($template);
					return "";
				}
			return $template;
    }
}
