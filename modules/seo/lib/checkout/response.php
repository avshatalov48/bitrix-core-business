<?php

namespace Bitrix\Seo\Checkout;

use Bitrix\Main\Result;

/**
 * Class Response
 * @package Bitrix\Seo\Checkout
 */
abstract class Response extends Result
{
	const TYPE_CODE = '';

	protected $type;
	protected $responseText;

	/* @var Request|null */
	protected $request;

	/**
	 * Response constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = static::TYPE_CODE;
	}

	/**
	 * @param array $data
	 * @return Result|void
	 */
	public function setData(array $data)
	{
		parent::setData($data);
	}

	/**
	 * @param $responseText
	 */
	public function setResponseText($responseText)
	{
		$this->responseText = $responseText;
	}

	/**
	 * @return mixed
	 */
	public function getResponseText()
	{
		return $this->responseText;
	}

	/**
	 * @return Request|null
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param Request $request
	 * @return Request
	 */
	public function setRequest(Request $request)
	{
		return $this->request = $request;
	}

	/**
	 * @param $type
	 * @return static
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function create($type)
	{
		return Factory::create(get_called_class(), $type);
	}

	/**
	 * @param $data
	 */
	abstract public function parse($data);
}