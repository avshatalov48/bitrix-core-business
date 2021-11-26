<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender;

Loc::loadMessages(__FILE__);

/**
 * Class GroupContactTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupContact_Query query()
 * @method static EO_GroupContact_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupContact_Result getById($id)
 * @method static EO_GroupContact_Result getList(array $parameters = array())
 * @method static EO_GroupContact_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection wakeUpCollection($rows)
 */
class GroupContactTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_contact';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
			),
			'CONTACT' => array(
				'data_type' => Sender\ContactTable::class,
				'reference' => array('=this.CONTACT_ID' => 'ref.ID'),
			),
			'CONTACT_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true,
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true,
			),

			'CNT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 0,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Delete counters by group ID.
	 *
	 * @param int $groupId Group ID.
	 * @return bool
	 */
	public static function deleteByGroupId($groupId)
	{
		$items = static::getList([
			'select' => ['ID', 'GROUP_ID', 'TYPE_ID'],
			'filter' => ['=GROUP_ID' => $groupId]
		]);
		foreach ($items as $primary)
		{
			$result = static::delete($primary);
			if (!$result->isSuccess())
			{
				return false;
			}
		}

		return true;
	}
}