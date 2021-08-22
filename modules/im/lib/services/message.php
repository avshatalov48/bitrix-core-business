<?php declare(strict_types=1);

namespace Bitrix\Im\Services;

use Bitrix\Main;

/**
 * Message service.
 *
 * @package Bitrix\Im\Services
 */
class Message
{
	/** @var bool */
	private $isEnabled;

	public function __construct()
	{
		$this->isEnabled = \Bitrix\Main\Loader::includeModule('im');
	}

	/**
	 * Returns message params.
	 *
	 * @param int $messageId Message id.
	 *
	 * @return array|null
	 */
	public function getMessage(int $messageId): ?array
	{
		if ($this->isEnabled)
		{
			return \CIMMessenger::GetById($messageId);
		}

		return null;
	}
}
