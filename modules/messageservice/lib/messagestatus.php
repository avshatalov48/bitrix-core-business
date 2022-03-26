<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MessageStatus
{
	//local queue statuses
	const PENDING = 0;
	const ERROR = 1;
	const EXCEPTION = 2;
	const DEFERRED = 3;

	//external service statuses
	const ACCEPTED = 10;
	const QUEUED = 11;
	const SENDING = 12;
	const SENT = 13;
	const DELIVERED = 14;
	const UNDELIVERED = 15;
	const FAILED = 16;
	const READ = 17;

	const UNKNOWN = 64;

	const SEMANTIC_PROCESS = 'process';
	const SEMANTIC_SUCCESS = 'success';
	const SEMANTIC_FAILURE = 'failure';

	public static function getDescriptions()
	{
		return array(
			static::PENDING => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING'),
			static::ERROR => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_ERROR'),
			static::EXCEPTION => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_EXCEPTION'),
			static::DEFERRED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_DEFERRED'),

			static::ACCEPTED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING'),
			static::QUEUED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING'),
			static::SENDING => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING'),
			static::SENT => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENT'),
			static::DELIVERED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_DELIVERED'),
			static::UNDELIVERED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_UNDELIVERED'),
			static::FAILED => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_FAILED'),
			static::READ => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_READ'),
			static::UNKNOWN => Loc::getMessage('MESSAGESERVICE_MESSAGESTATUS_SENDING'),
		);
	}

	public static function getSemantics()
	{
		return array(
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
		);
	}
}