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

/**
 * Class QueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Queue_Query query()
 * @method static EO_Queue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Queue_Result getById($id)
 * @method static EO_Queue_Result getList(array $parameters = array())
 * @method static EO_Queue_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_Queue createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_Queue_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_Queue wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_Queue_Collection wakeUpCollection($rows)
 */
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
