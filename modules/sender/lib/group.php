<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Sender\Internals\Model\GroupCounterTable;

Loc::loadMessages(__FILE__);

class GroupTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => function ()
				{
					return array(
						//new Entity\Validator\Unique
					);
				}
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_NAME')
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Type\DateTime(),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Type\DateTime(),
			),
			'DATE_USE' => array(
				'data_type' => 'datetime',
			),
			'DATE_USE_EXCLUDE' => array(
				'data_type' => 'datetime',
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'Y',
				'values' => array('N', 'Y'),
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'N',
				'values' => array('N', 'Y'),
			),
			'IS_SYSTEM' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'N',
				'values' => array('N', 'Y'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_DESCRIPTION'),
				'validation' => array(__CLASS__, 'validateDescription'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 100,
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_SORT')
			),
			'ADDRESS_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'USE_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'USE_COUNT_EXCLUDE' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'GROUP_CONNECTOR' => array(
				'data_type' => 'Bitrix\Sender\GroupConnectorTable',
				'reference' => array('=this.ID' => 'ref.GROUP_ID'),
			),
			'MAILING_GROUP' => array(
				'data_type' => 'Bitrix\Sender\MailingGroupTable',
				'reference' => array('=this.ID' => 'ref.GROUP_ID'),
			),
			'DEAL_CATEGORY' =>
				new ReferenceField(
					'DEAL_CATEGORY',
					GroupDealCategoryTable::class,
					[
						'=this.ID' => 'ref.GROUP_ID',
					],
					['join_type' => 'LEFT']
				)
		);
	}

	/**
	 * Returns validators for DESCRIPTION field.
	 *
	 * @return array
	 */
	public static function validateDescription()
	{
		return array(
			new Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('GROUP_ID' => $data['primary']['ID']);
		GroupConnectorTable::delete($primary);
		GroupCounterTable::delete($primary);

		return $result;
	}

	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;

		$data = $event->getParameters();
		if (array_key_exists('DATE_UPDATE', $data['fields']))
		{
			$data['fields']['DATE_UPDATE'] = new Type\DateTime();
			$result->modifyFields($data['fields']);
		}

		if (array_key_exists('CODE', $data['fields']) && is_null($data['fields']['CODE']))
		{
			$data['fields']['CODE'] = new SqlExpression('NULL');
			$result->modifyFields($data['fields']);
		}

		return $result;
	}
}


class GroupConnectorTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_connector';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'ENDPOINT' => array(
				'data_type' => 'string',
				'required' => true,
				'serialized' => true,
			),
			'ADDRESS_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
		);
	}
}



class GroupDealCategoryTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_deal_category';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'DEAL_CATEGORY_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}
}
