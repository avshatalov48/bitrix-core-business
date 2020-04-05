<?php

namespace Bitrix\Main\Engine\Response;


use Bitrix\Main\Type\Contract;
use Bitrix\Main;
use Bitrix\Main\Type\DateTime;

class Json extends Main\HttpResponse
{
	protected $data;
	protected $jsonEncodingOptions = 0;

	public function __construct($data = null, $options = 0)
	{
		parent::__construct();

		$this->jsonEncodingOptions = $options;
		$this->setData($data);
	}

	public function setData($data)
	{
		//todo It's a crutch. While we are supporting php 5.3 we have to keep this code.
		//When minimal version is php 5.5 we can implement JsonSerializable in DateTime, Uri and remove this code.
		$data = $this->processData($data);

		if ($data instanceof \JsonSerializable)
		{
			$this->data = Main\Web\Json::encode($data->jsonSerialize(), $this->jsonEncodingOptions);
		}
		elseif ($data instanceof Contract\Jsonable)
		{
			$this->data = $data->toJson($this->jsonEncodingOptions);
		}
		elseif ($data instanceof Contract\Arrayable)
		{
			$this->data = Main\Web\Json::encode($data->toArray(), $this->jsonEncodingOptions);
		}
		else
		{
			$this->data = Main\Web\Json::encode($data, $this->jsonEncodingOptions);
		}

		return $this->setContent($this->data);
	}

	private function processData($data)
	{
		if ($data instanceof \JsonSerializable)
		{
			$data = $data->jsonSerialize();
		}
		elseif ($data instanceof Contract\Jsonable)
		{
			$data = $data->toJson($this->jsonEncodingOptions);
		}
		elseif ($data instanceof Contract\Arrayable)
		{
			$data = $data->toArray();
		}

		if ($data instanceof DateTime)
		{
			return date('c' , $data->getTimestamp());
		}

		if ($data instanceof Main\Type\Date)
		{
			/** @see \CRestUtil::ConvertDate */
			return date('c', makeTimeStamp($data, FORMAT_DATE) + date("Z"));
		}

		if ($data instanceof Main\Web\Uri)
		{
			return $data->getUri();
		}

		if ($data instanceof Main\UI\PageNavigation)
		{
			return array(
				'currentPage' => $data->getCurrentPage(),
				'pageSize' => $data->getPageSize(),
				'recordCount' => $data->getRecordCount(),
			);
		}

		if (is_array($data) || $data instanceof \Traversable)
		{
			foreach ($data as $key => $item)
			{
				$data[$key] = $this->processData($item);
			}
		}

		return $data;
	}

	public function send()
	{
		$this->addHeader('Content-Type', 'application/json; charset=UTF-8');
		parent::send();
	}
}