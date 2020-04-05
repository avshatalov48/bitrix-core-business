<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

Loc::loadMessages(__FILE__);

/**
 * Class Answer
 * @package Bitrix\Sender\Internals\QueryController
 */
abstract class Content
{
	const TYPE_JSON = 'json';
	const TYPE_HTML = 'html';

	/** @var ErrorCollection $errors Errors. */
	protected $errors;

	/** @var Response $response Response. */
	protected $response;

	public static function create (Response $response, $type)
	{
		switch ($type)
		{
			case Content::TYPE_HTML:
				return new ContentHtml($response);

			case Content::TYPE_JSON:
			default:
				return new ContentJson($response);
		}
	}

	/**
	 * Answer constructor.
	 * @param Response $response Response.
	 */
	public function __construct(Response $response)
	{
		$this->errors = new ErrorCollection;
		$this->response = $response;
	}

	/**
	 * On flush callback.
	 */
	public function onFlush()
	{

	}

	/**
	 * @return string
	 */
	abstract public function toText();

	/**
	 * @param $message
	 * @param null $code
	 */
	public function addError($message, $code = null)
	{
		$this->errors->setError(new Error($message, $code));
	}
	/**
	 * @param string|null $message Message.
	 */
	public function addPermissionError($message = null)
	{
		if (!$message)
		{
			$message = 'Access denied.';
		}

		$this->errors->setError(new Error($message, 0));
	}

	/**
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errors;
	}

	protected function getErrorMessages()
	{
		$list = array();
		foreach ($this->errors as $error)
		{
			/** @var Error $error Error. */
			$list[] = $error->getMessage();
		}
		return $list;
	}
}