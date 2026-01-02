<?php

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\Delete\DisappearService;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;

class MessagesAutoDeleteDelay extends AbstractOption
{
	public const NAME = 'messagesAutoDeleteDelay';
	public const DB_NAME = 'MESSAGES_AUTO_DELETE_DELAY';

	/**
	 * @see Chat::getDefaultMessagesAutoDeleteDelay()
	 */
	public const DEFAULT_VALUE = '';

	/**
	 * @see DisappearService::TIME_WHITELIST
	 */
	public const ALLOWED_VALUES = [
		self::DEFAULT_VALUE,
		'0',
		'1',
		'24',
		'168',
		'720',
	];

	#[InArray(self::ALLOWED_VALUES)]
	protected string $value;

	protected bool $needUpdate = false;

	public function __construct(string $value)
	{
		$this->checkNeedUpdate($value);
		parent::__construct(static::DB_NAME, $value);
	}

	protected function applyImplementation(Collab $collab): Result
	{
		if (!$this->needUpdate)
		{
			return new Result();
		}

		return \Bitrix\Socialnetwork\Collab\Integration\IM\Chat::updateMessagesAutoDelete(
			$collab->getChatId(),
			(int)$this->getValue()
		);
	}

	protected function checkNeedUpdate(string $value): void
	{
		if ($value !== self::DEFAULT_VALUE)
		{
			$this->needUpdate = true;
		}
	}
}