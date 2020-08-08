<?php
namespace Bitrix\Sale\Exchange\Integration\Admin;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Internals\Fields;

abstract class LinkBase
{
	protected $query;
	protected $page;

	public function __construct()
	{
		$this->query = new Fields();
	}

	public function setRequestUri($url)
	{
		$uri = new Uri($url);
		return $this
			->setPage($uri->getPath())
			->setQuery($uri->getQuery());
	}

	public function setField($name, $value)
	{
		$this->query->set($name, $value);
		return $this;
	}
	public function getField($name)
	{
		return $this->query->get($name);
	}
	public function setFieldsValues($values)
	{
		if(is_array($values))
		{
			$this->query->setValues($values);
		}
		return $this;
	}
	public function getFieldsValues()
	{
		return $this->query->getValues();
	}
	public function setQuery($query)
	{
		$params = $this->parseParams($query);
		if(is_array($params))
		{
			foreach ($params as $name=>$value)
			{
				$this->query->set($name, $value);
			}
		}
		return $this;
	}

	public function build()
	{
		$uri = new Uri($this->page);
		return $uri
			->addParams($this->getFieldsValues())
			->getUri();
	}
	public function redirect()
	{
		LocalRedirect($this->build());
	}

	public function setPage($page)
	{
		$this->page = $page;
		return $this;
	}
	public function setPageByType($type)
	{
		$registry = Registry::getRegistry()[$this->getType()];
		$page = isset($registry[$type]) ? $registry[$type]:null;

		if(is_null($page))
		{
			throw new ArgumentException("Unsupported link type: {$type}");
		}

		$this->setPage($page);
		return $this;
	}
	public function getPage()
	{
		return $this->page;
	}

	protected function parseParams($data)
	{
		parse_str($data, $values);
		return $values;
	}

	abstract public function getType();

	/**
	 * @return $this
	 */
	abstract public function fill();//setFieldsValues($this->getDefaultFieldsValues())
}