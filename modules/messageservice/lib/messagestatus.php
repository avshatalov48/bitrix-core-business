<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MessageStatus
{
	//local queue statuses
	public const PENDING = 0;
	public const ERROR = 1;
	public const EXCEPTION = 2;
	public const DEFERRED = 3;

	//external service statuses
	public const ACCEPTED = 10;
	public const QUEUED = 11;
	public const SENDING = 12;
	public const SENT = 13;
	public const DELIVERED = 14;
	public const UNDELIVERED = 15;
	public const FAILED = 16;
	public const READ = 17;

	public const UNKNOWN = 64;

	public const SEMANTIC_PROCESS = 'process';
	public const SEMANTIC_SUCCESS = 'success';
	public const SEMANTIC_FAILURE = 'failure';

	public static function getDescriptions(?string $language = null): array
	{
		return [
			static::PENDING => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING', null, $language),
			static::ERROR => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_ERROR', null, $language),
			static::EXCEPTION => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_EXCEPTION', null, $language),
			static::DEFERRED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_DEFERRED',null,  $language),

			static::ACCEPTED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING', null, $language),
			static::QUEUED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING', null, $language),
			static::SENDING => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING', null, $language),
			static::SENT => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENT', null, $language),
			static::DELIVERED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_DELIVERED', null, $language),
			static::UNDELIVERED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_UNDELIVERED', null, $language),
			static::FAILED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_FAILED', null, $language),
			static::READ => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_READ', null, $language),
			static::UNKNOWN => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING', null, $language),
		];
	}

	public static function getSemantics(): array
	{
		return [
			static::PENDING => static::SEMANTIC_PROCESS,
			static::ERROR => static::SEMANTIC_FAILURE,
			static::EXCEPTION => static::SEMANTIC_FAILURE,
			static::DEFERRED => static::SEMANTIC_PROCESS,

			static::ACCEPTED => static::SEMANTIC_PROCESS,
			static::QUEUED => static::SEMANTIC_PROCESS,
			static::SENDING => static::SEMANTIC_PROCESS,
			static::SENT => static::SEMANTIC_SUCCESS,
			static::DELIVERED => static::SEMANTIC_SUCCESS,
			static::UNDELIVERED => static::SEMANTIC_FAILURE,
			static::FAILED => static::SEMANTIC_FAILURE,
			static::READ => static::SEMANTIC_SUCCESS,
			static::UNKNOWN => static::SEMANTIC_PROCESS,
		];
	}
}