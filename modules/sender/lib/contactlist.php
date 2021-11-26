<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type as MainType;

Loc::loadMessages(__FILE__);

/**
 * Class ContactListTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ContactList_Query query()
 * @method static EO_ContactList_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ContactList_Result getById($id)
 * @method static EO_ContactList_Result getList(array $parameters = array())
 * @method static EO_ContactList_Entity getEntity()
 * @method static \Bitrix\Sender\EO_ContactList createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_ContactList_Collection createCollection()
 * @method static \Bitrix\Sender\EO_ContactList wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_ContactList_Collection wakeUpCollection($rows)
 */
class ContactListTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_contact_list';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CONTACT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'LIST_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'LIST' => array(
				'data_type' => 'Bitrix\Sender\ListTable',
				'reference' => array('=this.LIST_ID' => 'ref.ID'),
			),
			'CONTACT' => array(
				'data_type' => 'Bitrix\Sender\ContactTable',
				'reference' => array('=this.CONTACT_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Add if not exist.
	 *
	 * @param integer $contactId Contact ID.
	 * @param integer $listId List ID.
	 * @return bool
	 */
	public static function addIfNotExist($contactId, $listId)
	{
		$result = false;
		$arPrimary = array('CONTACT_ID' => $contactId, 'LIST_ID' => $listId);
		if( !($arList = static::getRowById($arPrimary) ))
		{
			$resultAdd = static::add($arPrimary);
			if ($resultAdd->isSuccess())
			{
				$result = true;
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}
	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}