<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Result;

class Base
{
	const DIRECTORY_PAGE = '/rest/';

	protected $query;
	protected $page;
	protected $directory;

	private $token;

	public function __construct()
	{
		$this->query = new Fields();

		$this->initialize(new Integration\App\IntegrationB24());
	}

	public function setField($name, $value)
	{
		$this->query->set($name, $value);
		return $this;
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
	public function setDirectory($directory)
	{
		$this->directory = $directory;
		return $this;
	}
	public function setPageByType($type)
	{
		$registry = Registry::getRegistry();
		$page = isset($registry[$type]) ? $registry[$type]:null;

		if(is_null($page))
		{
			throw new ArgumentException("Unsupported cmd type: {$type}");
		}

		$this->setPage($page);
		return $this;
	}
	public function setPage($page)
	{
		$this->page = $page;
		return $this;
	}
	public function build()
	{
		$uri = new Uri($this->buildDirectoryPage());
		return $uri
			->addParams($this->getFieldsValues())
			->getUri();
	}
	public function call()
	{
		$r = new Result();
		try
		{
			$response = (new Integration\Rest\Client\TokenClient($this->token))
				->call(
					$this->buildDirectoryPage(), $this->getFieldsValues());
		}
		catch (\Exception $exception)
		{
			return $r->addError(new Error("Error: ".$exception->getMessage()));
		}

		if (isset($response["error"]))
		{
			return $r->addError(new Error(
				"Server Error: ".$response["error_description"]." (".$response["error"].")"
			));
		}
		else if (!isset($response["result"]))
		{
			return $r->addError(new Error("Wrong Server Response."));
		}

		return $r->setData(['DATA'=>$response]);
	}
	public function fill()
	{
		return $this;
	}

	protected function buildDirectoryPage()
	{
		return $this->directory.$this->page;
	}

	protected function initialize(Integration\App\Base $application)
	{
		$this->token = static::getAppToken($application->getCode());
	}

	private static function getAppToken($guid)
	{
		if (!is_string($guid) || empty($guid))
		{
			return null;
		}

		$token = Integration\Entity\B24integrationTokenTable::getList([
			"select" => ["*"],
			"filter" => ["=GUID" => $guid]
		])->fetchObject();

		return $token ? $token : null;
	}
}