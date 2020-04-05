<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/**
 * Class AnswerJson
 * @package Bitrix\Sender\Internals\QueryController
 */
class ContentJson extends Content
{
	protected $parameters = array();

	public function set(array $parameters)
	{
		$this->parameters = $parameters;
	}

	public function add($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	public function toText()
	{
		$default = array(
			'error' => !$this->errors->isEmpty(),
			'text' => implode('<br>', $this->getErrorMessages()),
		);
		return Json::encode($this->parameters + $default);
	}

	public function onFlush()
	{
		$this->response->addHeader('Content-Type', 'application/json; charset=UTF-8');
	}
}