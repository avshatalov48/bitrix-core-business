<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Mail\Internals\Entity\MailboxDirectory;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * Class MailboxDirectoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailboxDirectory_Query query()
 * @method static EO_MailboxDirectory_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailboxDirectory_Result getById($id)
 * @method static EO_MailboxDirectory_Result getList(array $parameters = array())
 * @method static EO_MailboxDirectory_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\Entity\MailboxDirectory createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\Entity\MailboxDirectory wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection wakeUpCollection($rows)
 */
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
				'required'  => true,
				'fetch_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getFetchModificator']
			],
			'PATH'          => [
				'data_type' => 'string',
				'required'  => true,
				'fetch_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getFetchModificator']
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
