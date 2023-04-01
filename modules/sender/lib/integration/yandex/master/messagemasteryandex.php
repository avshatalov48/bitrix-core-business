<?php

namespace Bitrix\Sender\Integration\Yandex\Master;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Message\iBase;
use Bitrix\Sender\Message\iMasterYandex;

class MessageMasterYandex implements iBase, iMasterYandex
{
	public const CODE = self::CODE_MASTER_YANDEX;
	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return Loc::getMessage('SENDER_INTEGRATION_MASTER_YANDEX_MESSAGE_NAME');
	}

	/**
	 * @inheritDoc
	 */
	public function getCode(): string
	{
		return self::CODE;
	}

	/**
	 * @inheritDoc
	 */
	public function getSupportedTransports(): array
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function loadConfiguration($id = null)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function saveConfiguration(\Bitrix\Sender\Message\Configuration $configuration)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function copyConfiguration($id)
	{
	}
}