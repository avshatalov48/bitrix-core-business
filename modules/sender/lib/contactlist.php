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
use Bitrix\Main\Type as MainType;

Loc::loadMessages(__FILE__);

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
}