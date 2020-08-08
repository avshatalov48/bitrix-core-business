<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MessageAccessTable extends Entity\DataManager
{
	const ENTITY_TYPE_NO_BIND = 'NO_BIND';
	const ENTITY_TYPE_TASKS_TASK = 'TASKS_TASK';
	const ENTITY_TYPE_CRM_ACTIVITY = 'CRM_ACTIVITY';
	const ENTITY_TYPE_BLOG_POST = 'BLOG_POST';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_access';
	}

	public static function getMap()
	{
		return array(
			'TOKEN' => array(
				'data_type' => 'string',
				'primary'   => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'ENTITY_UF_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'SECRET' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'OPTIONS' => array(
				'data_type'  => 'text',
				'serialized' => true,
			),
			new Entity\ReferenceField(
				'CRM_ACTIVITY',
				'\Bitrix\Crm\ActivityTable',
				array(
					'=this.ENTITY_TYPE' => array('?s', self::ENTITY_TYPE_CRM_ACTIVITY),
					'=this.ENTITY_ID' => 'ref.ID',
				)
			),
		);
	}

}
