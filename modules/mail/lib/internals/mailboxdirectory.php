<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Mail\Internals\Entity\MailboxDirectory;
use Bitrix\Main\ORM\Data\DataManager;

class MailboxDirectoryTable extends DataManager
{
	const ACTIVE = 1;
	const INACTIVE = 0;

	const TYPE_INCOME = 'IS_INCOME';
	const TYPE_OUTCOME = 'IS_OUTCOME';
	const TYPE_TRASH = 'IS_TRASH';
	const TYPE_SPAM = 'IS_SPAM';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox_dir';
	}

	public static function getObjectClass()
	{
		return MailboxDirectory::class;
	}

	public static function getMap()
	{
		return [
			'ID'            => [
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			],
			'MAILBOX_ID'    => [
				'data_type' => 'integer',
			],
			'NAME'          => [
				'data_type' => 'string',
				'required'  => true
			],
			'PATH'          => [
				'data_type' => 'string',
				'required'  => true
			],
			'FLAGS'         => [
				'data_type' => 'string',
			],
			'DELIMITER'     => [
				'data_type' => 'string',
			],
			'DIR_MD5'       => [
				'data_type' => 'string',
			],
			'LEVEL'         => [
				'data_type' => 'integer',
			],
			'MESSAGE_COUNT' => [
				'data_type' => 'integer',
			],
			'PARENT_ID'     => [
				'data_type' => 'integer',
			],
			'ROOT_ID'       => [
				'data_type' => 'integer',
			],
			'IS_SYNC'       => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_DISABLED'   => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_INCOME'     => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_OUTCOME'    => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_DRAFT'      => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_TRASH'      => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'IS_SPAM'       => [
				'data_type' => 'integer',
				'values'    => [self::ACTIVE, self::INACTIVE],
			],
			'SYNC_TIME'     => [
				'data_type' => 'integer',
			],
			'SYNC_LOCK'     => [
				'data_type' => 'integer',
			]
		];
	}
}
