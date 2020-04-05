<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\WebForm\Helper;

Loc::loadMessages(__FILE__);

class QueueTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sender_queue';
	}

	public static function getMap()
	{
		return array(
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'LAST_ITEM' => array(
				'data_type' => 'string',
				'required' => true,
			),
		);
	}
}
