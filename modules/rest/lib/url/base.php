<?php

namespace Bitrix\Rest\Url;

use \Bitrix\Main\Web\Uri;

class Base
{
	protected $directory = '';
	protected $pages = [];

	final public static function getInstance()
	{
		static $instance = null;

		if (null === $instance)
		{
			$instance = new static();
		}
		return $instance;
	}

	protected function getDir()
	{
		return SITE_DIR.$this->directory;
	}

	protected function getReplaced(string $url, $replace = null, $subject = null)
	{
		if (!is_null($replace) && !is_null($subject))
		{
			$url = str_replace($replace, $subject, $url);
		}

		return $url;
	}

	protected function addParams($url, $params)
	{
		if (is_array($params))
		{
			$uri = new Uri($url);
			$uri->addParams($params);
			$url = $uri->getUri();
		}

		return $url;
	}

	protected function getUrl($page, $replace = null, $subject = null, $query = null)
	{
		$url = null;
		if(array_key_exists($page, $this->pages))
		{
			$url = $this->getDir().$this->pages[$page];

			if (!is_null($replace) && !is_null($subject))
			{
				$url = $this->getReplaced($url, $replace, $subject);
			}
			$url = $this->getReplaced($url, '//', '/');

			if(is_array($query))
			{
				$url = $this->addParams($url, $query);
			}
		}

		return $url;
	}

}