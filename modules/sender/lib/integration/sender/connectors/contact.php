<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\Connectors;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Connector;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Recipient\Type as RecipientType;

Loc::loadMessages(__FILE__);

class Contact extends Connector\BaseFilter
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SENDER_CONNECTOR_CONTACT_NAME1');
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return "contact_list";
	}

	/**
	 * Get data count by type.
	 *
	 * @return null|array
	 */
	public function getDataCountByType()
	{
		$listId = $this->getFieldValue('LIST_ID', null);
		if (!$listId)
		{
			return array();
		}

		$query = ContactTable::query();
		$query->addSelect('TYPE_ID');
		$query->addSelect(new Entity\ExpressionField('CNT', 'COUNT(TYPE_ID)'));
		$query->addFilter('=CONTACT_LIST.LIST_ID', $listId);
		$query->addGroup('TYPE_ID');
		$list = $query->exec();

		$result = array();
		foreach ($list as $item)
		{
			$typeName = RecipientType::getCode($item['TYPE_ID']);
			$result[$typeName] = $item['CNT'];
		}

		return $result;
	}

	/**
	 *
	 * @return \Bitrix\Main\DB\Result|array
	 */
	public function getData()
	{
		$listId = $this->getFieldValue('LIST_ID', null);
		if (!$listId)
		{
			return array();
		}

		$resultDb = ContactTable::getList(
			[
				'select' => ['NAME', 'TYPE_ID', 'CODE', 'USER_ID'],
				'filter' => [
					'=CONTACT_LIST.LIST_ID' => $listId
				]
			]
		);
		$resultDb->addFetchDataModifier(
			function ($data)
			{
				$row = array(
					'NAME' => $data['NAME'],
					'USER_ID' => $data['USER_ID'],
				);

				$key = RecipientType::getCode($data['TYPE_ID']);
				$row[$key] = $data['CODE'];

				return $row;
			}
		);

		return $resultDb;
	}

	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	public static function getUiFilterFields()
	{
		$list = array();

		$list[] = array(
			"id" => "LIST_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_SENDER_CONNECTOR_CONTACT_FILTER_LIST_ID'),
			"type" => "list",
			"items" => ListTable::getList(array('select' => array('VALUE' => 'ID', 'NAME')))->fetchAll(),
			"default" => true
		);

		return $list;
	}
}
