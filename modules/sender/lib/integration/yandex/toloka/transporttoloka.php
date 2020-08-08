<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Integration\Yandex\Toloka;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Message;
use Bitrix\Sender\Recipient\Type;
use Bitrix\Sender\Transport\iBase;

Loc::loadMessages(__FILE__);

/**
 * Class TransportToloka
 */
class TransportToloka implements iBase
{
	const CODE = 'toloka';

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_YANDEX_TOLOKA');
	}

	/**
	 * @inheritDoc
	 */
	public function getCode()
	{
		return self::CODE;
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return array(Type::EMAIL);
	}

	/**
	 * @inheritDoc
	 */
	public function loadConfiguration()
	{
		// TODO: Implement loadConfiguration() method.
	}

	/**
	 * @inheritDoc
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		// TODO: Implement saveConfiguration() method.
	}

	/**
	 * @inheritDoc
	 */
	public function start()
	{
		// TODO: Implement start() method.
	}

	/**
	 * @inheritDoc
	 */
	public function send(Message\Adapter $message)
	{
		// TODO: Implement send() method.
	}

	/**
	 * @inheritDoc
	 */
	public function end()
	{
		// TODO: Implement end() method.
	}
}