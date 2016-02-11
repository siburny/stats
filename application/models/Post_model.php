<?php

require_once('MY_Model.php');

class Post_model extends MY_Model
{
	protected $primary_key = 'post_id';
	protected $_table = 'posts';
	//public $before_create = array( 'created_at', 'updated_at' );
	//public $before_update = array( 'updated_at' );

	static function get_post($url)
	{
		require_once(APPPATH.'third_party/querypath-3.0.4/src/qp.php');
		libxml_use_internal_errors(true);
		$qp = htmlqp($url);
		$data = array('url' => $url);
		
		//title
		$title = $qp->find("meta[property='og:title']");
		if($title->count())
		{
			$data['title'] = $title->attr('content');
		}
		else
		{
			$title = $qp->find("meta[property='twitter:title']");
			if($title->count())
			{
				$data['title'] = $title->attr('content');
			}
			else
			{
				$data['title'] = $qp->find("title")->text();
			}
		}
		
		//image
		$image = $qp->find("meta[property='og:image']");
		if($image->count())
		{
			$data['image'] = $image->attr('content');
		}
		else
		{
			$image = $qp->find("meta[property='twitter:image:src']");
			if($image->count())
			{
				$data['image'] = $image->attr('content');
			}
		}
		
		//save images
		if($data['image'])
		{
			$original_url = $data['image'];
			$md5 = substr(md5($original_url.mt_rand()), 0, 12);
			
			$local_dir = substr($md5, 0, 2).DIRECTORY_SEPARATOR.substr($md5, 2, 2).DIRECTORY_SEPARATOR;
			$local = $local_dir.substr($md5, 4);
			$url = substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4);
			
			$path = $original_url;
			$qpos = strpos($path, "?"); 
			if ($qpos!==false) $path = substr($path, 0, $qpos); 
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			if($extension != "")
			{
				$local .= ".".$extension;
				$url .= ".".$extension;
			}

			$local = FCPATH."images".DIRECTORY_SEPARATOR ."cache".DIRECTORY_SEPARATOR.$local;
			$local_dir = FCPATH."images".DIRECTORY_SEPARATOR ."cache".DIRECTORY_SEPARATOR.$local_dir;
			$url = "/images/cache/".$url;
			
			!is_dir($local_dir) && mkdir($local_dir, 0777, TRUE);
			
			copy($original_url, $local);
			if(file_exists($local))
			{
				$data['image'] = $url;
			}
			else
			{
				unset($data['image']);
			}
		}

		//Date
		$date = $qp->find("meta[property='article:published_time']");
		if($date->count() > 1)
		{
			$data['date_published'] = $date->attr('content');
		}
		else
		{
			$date = $qp->find('time');
			if($date->count() == 1)
			{
				if($date->attr('datetime'))
				{
					$data['date_published'] = $date->attr('datetime');
				}
				else
				{
					$data['date_published'] = $date->text();
				}
			}
			else
			{
				$date = $qp->find('article time');
				if($date->count() == 1)
				{
					if($date->attr('datetime'))
					{
						$data['date_published'] = $date->attr('datetime');
					}
					else
					{
						$data['date_published'] = $date->text();
					}
				}
				else
				{
					$date = $qp->find('article header time');
					if($date->count() == 1)
					{
						if($date->attr('datetime'))
						{
							$data['date_published'] = $date->attr('datetime');
						}
						else
						{
							$data['date_published'] = $date->text();
						}
					}
					else
					{
					}
				}
			}
		}

		$author = $qp->find(".author");
		if($author->count() == 1)
		{
			$data['author'] = $author->text();
		}

		if(!isset($data['author']))
		{
			$author = $qp->find("meta[name=author]");
			if($author->count() == 1)
			{
				$data['author'] = $author->attr('content');
			}
		}

		if(!isset($data['author']))
		{
			$author = $qp->find("*[class*='author']");
			
			$classes = [];
			foreach($author->get() as $el)
			{
				$classes[] = $el->getAttribute('class');
			}
			$classes = array_count_values(array_map('strtolower', $classes));
			
			foreach($classes as $class => $count)
			{
				if($count == 1)
				{
					$data['author'] = $qp->find('.'.str_replace(' ', '.', $class))->text();
					break;
				}
			}
		}

		return $data;
	}

	static function get_posts($company_id, $start = null, $end = null, $objects = TRUE)
	{
		$ci = &get_instance();
		if($start == null)
		{
			$today = new DateTime();
			$start = $today->format('Y-m-d');
		}
		else
		{
			$today = new DateTime($start);
		}
		if($end == null)
		{
			$end = $today->modify('-6 days')->format('Y-m-d');
		}
		elseif(is_numeric($end))
		{
			$end = $today->modify('-'.$end.' days')->format('Y-m-d');
		}

		$res = $ci->db->query("
SELECT `posts`.*, sum(sessions) as total_sessions, sum(pageviews) as total_pageviews 
FROM `posts`
LEFT JOIN `post_stats` ON `posts`.`post_id` = `post_stats`.`post_id`
WHERE `posts`.`company_id` = ?
AND `date` >= ?
AND `date` <= ?
GROUP BY posts.post_id
ORDER BY total_sessions DESC
LIMIT 0, 10
		", array($company_id, $end, $start));

		if($objects)
		{
			return $res->result_object();
		}
		else
		{
			return $res->result_array();
		}
	}
}
