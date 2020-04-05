<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class GroupTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(100) mandatory
 * <li> BASE bool optional default 'N'
 * <li> SORT int optional default 100
 * <li> XML_ID string(255) optional
 * <li> TIMESTAMP_X datetime optional
 * <li> MODIFIED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> LANG reference to {@link \Bitrix\Catalog\GroupLang}
 * <li> CURRENT_LANG reference to {@link \Bitrix\Catalog\GroupLang} with current lang (LANGUAGE_ID)
 * </ul>
 *
 * @package Bitrix\Catalog
 **/

class GroupTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new ORM\Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('GROUP_ENTITY_ID_FIELD'),
			)),
			'NAME' => new ORM\Fields\StringField('NAME', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('GROUP_ENTITY_NAME_FIELD'),
			)),
			'BASE' => new ORM\Fields\BooleanField('BASE', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('GROUP_ENTITY_BASE_FIELD'),
			)),
			'SORT' => new ORM\Fields\IntegerField('SORT', array(
				'title' => Loc::getMessage('GROUP_ENTITY_SORT_FIELD'),
			)),
			'XML_ID' => new ORM\Fields\StringField('XML_ID', array(
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('GROUP_ENTITY_XML_ID_FIELD'),
			)),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField('TIMESTAMP_X', array(
				'title' => Loc::getMessage('GROUP_ENTITY_TIMESTAMP_X_FIELD'),
				'default_value' => function()
				{
					return new Main\Type\DateTime();
				}
			)),
			'MODIFIED_BY' => new ORM\Fields\IntegerField('MODIFIED_BY', array(
				'title' => Loc::getMessage('GROUP_ENTITY_MODIFIED_BY_FIELD'),
			)),
			'DATE_CREATE' => new ORM\Fields\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('GROUP_ENTITY_DATE_CREATE_FIELD'),
				'default_value' => function()
				{
					return new Main\Type\DateTime();
				}
			)),
			'CREATED_BY' => new ORM\Fields\IntegerField('CREATED_BY', array(
				'title' => Loc::getMessage('GROUP_ENTITY_CREATED_BY_FIELD'),
			)),
			'CREATED_BY_USER' => new ORM\Fields\Relations\Reference(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.CREATED_BY' => 'ref.ID')
			),
			'MODIFIED_BY_USER' => new ORM\Fields\Relations\Reference(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.MODIFIED_BY' => 'ref.ID')
			),
			'LANG' => new ORM\Fields\Relations\Reference(
				'LANG',
				'\Bitrix\Catalog\GroupLang',
				array('=this.ID' => 'ref.CATALOG_GROUP_ID')
			),
			'CURRENT_LANG' => new ORM\Fields\Relations\Reference(
				'CURRENT_LANG',
				'\Bitrix\Catalog\GroupLang',
				array(
					'=this.ID' => 'ref.CATALOG_GROUP_ID',
					'=ref.LANG' => new Main\DB\SqlExpression('?', LANGUAGE_ID)
				),
				array('join_type' => 'LEFT')
			)
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 100),
		);
	}
	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 255),
		);
	}
}