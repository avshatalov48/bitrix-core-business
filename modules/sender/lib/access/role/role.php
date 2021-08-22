<?php

namespace Bitrix\Sender\Access\Role;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class RoleTable
 *
 * @package Bitrix\Sender\Internals\Model\Role
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Role_Query query()
 * @method static EO_Role_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Role_Result getById($id)
 * @method static EO_Role_Result getList(array $parameters = array())
 * @method static EO_Role_Entity getEntity()
 * @method static \Bitrix\Sender\Access\Role\EO_Role createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Access\Role\EO_Role_Collection createCollection()
 * @method static \Bitrix\Sender\Access\Role\EO_Role wakeUpObject($row)
 * @method static \Bitrix\Sender\Access\Role\EO_Role_Collection wakeUpCollection($rows)
 */
class RoleTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sender_role';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 * @throws SystemException
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'DEAL_CATEGORY_ID' => new Entity\IntegerField('DEAL_CATEGORY_ID', array(
				'required' => true
			)),
			'NAME' => new Entity\StringField('NAME', array(
				'required' => true,
				'title' => Loc::getMessage('SENDER_INTERNALS_MODEL_ROLE_FIELD_NAME')
			)),
			'XML_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateXmlId'),
			)
		);
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function validateXmlId()
	{
		return [
			new Entity\Validator\Length(null, 255),
		];
	}
}