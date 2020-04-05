<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpResponse;

Loc::loadMessages(__FILE__);

/**
 * Class AnswerHtml
 * @package Bitrix\Sender\Internals\QueryController
 */
class ContentHtml extends Content
{
	/**
	 * @var string $html Html.
	 */
	protected $html;

	/**
	 * @param string $html Html.
	 */
	public function set($html)
	{
		$this->html = $html;
	}

	/**
	 * @return string
	 */
	public function toText()
	{
		if ($this->errors->isEmpty())
		{
			return $this->html;
		}
		else
		{
			return implode('<br>', $this->getErrorMessages());
		}

	}
}