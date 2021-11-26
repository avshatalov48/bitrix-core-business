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
use Bitrix\Main\DB\SqlExpression;

Loc::loadMessages(__FILE__);

/**
 * Class ListTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_List_Query query()
 * @method static EO_List_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_List_Result getById($id)
 * @method static EO_List_Result getList(array $parameters = array())
 * @method static EO_List_Entity getEntity()
 * @method static \Bitrix\Sender\EO_List createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_List_Collection createCollection()
 * @method static \Bitrix\Sender\EO_List wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_List_Collection wakeUpCollection($rows)
 */
class ListTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_list';
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
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('SENDER_ENTITY_LIST_FIELD_TITLE_CODE'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => Loc::getMessage(
					'SENDER_ENTITY_LIST_FIELD_NAME_PATTERN',
					['%date%' => Internals\PrettyDate::formatDate()]
				),
				'title' => Loc::getMessage('SENDER_ENTITY_LIST_FIELD_TITLE_NAME'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'default_value' => 100,
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_LIST_FIELD_TITLE_SORT'),
			),
			'CONTACT_LIST' => array(
				'data_type' => 'Bitrix\Sender\ContactListTable',
				'reference' => array('=this.ID' => 'ref.LIST_ID'),
			),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 60),
		);
	}

	/**
	 * On after delete.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('LIST_ID' => $data['primary']['ID']);
		ContactListTable::deleteList($primary);

		return $result;
	}

	/**
	 * Add if not exist.
	 *
	 * @param string $code Code.
	 * @param string $name Name.
	 * @return bool|int
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function addIfNotExist($code, $name)
	{
		$id = false;
		if( !($arList = static::getList(array('filter' => array('CODE' => $code)))->fetch() ))
		{
			$resultAdd = static::add(array('CODE' => $code, 'NAME' => $name));
			if ($resultAdd->isSuccess())
			{
				$id = $resultAdd->getId();
			}
		}
		else
		{
			$id = $arList['ID'];
		}

		return $id;
	}
}