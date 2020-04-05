<?php
namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ExecuteException
 * Throw various errors occuring during vk-export
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class ExecuteException extends SystemException
{
	protected $errCode;
	protected $method;
	protected $needLink = false;
	const API_DESC_PAGE = 'https://new.vk.com/dev/errors';


	public function __construct($message = "", $errCode = "", $method = "", \Exception $previous = NULL)
	{
		parent::__construct($message, 0, '', 0, $previous);
		$this->errCode = $errCode;
		$this->method = $method;
	}

	/**
	 * Return formatted message for showing
	 *
	 * @return string
	 */
	public function getFullMessage()
	{
		$newMessage = Loc::getMessage("VK_ERRORS_INTRO") . "\n";

		if ($this->errCode)
			$newMessage .= Loc::getMessage("VK_ERROR_CODE") . ": \"" . $this->errCode . "\". ";

		$newMessage .= Loc::getMessage("VK_ERROR_TEXT") . ": \"" . $this->message . "\".";

		if ($this->method)
			$newMessage .= " " . Loc::getMessage("VK_ERROR_IN_METHOD") . ": " . $this->method . ".";

//		only for vk api errors
		if ($this->errCode)
			$newMessage .= "\n" . Loc::getMessage("VK_ERROR_ERRORS_INFO") . self::API_DESC_PAGE;

		return $newMessage;
	}
}
