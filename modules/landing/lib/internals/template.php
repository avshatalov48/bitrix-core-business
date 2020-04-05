<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class TemplateTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_template';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE'),
				'default_value' => 'Y'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TITLE'),
				'required' => true,
				'fetch_data_modification' => function()
				{
					return array(
						function ($value)
						{
							if (substr($value, 0, 1) == '#')
							{
								$langCode = substr(substr($value, 1), 0, -1);
								$mess = Loc::getMessage('LANDING_TABLE_TPL_' . $langCode);
								if ($mess)
								{
									return $mess;
								}
								else
								{
									return $value;
								}
							}
							else
							{
								return $value;
							}
						}
					);
				}
			)),
			'SORT' => new Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SORT')
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'CONTENT' => new Entity\StringField('CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CONTENT')
			)),
			'AREA_COUNT' => new Entity\IntegerField('AREA_COUNT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_AREA_COUNT'),
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'CREATED_BY' => new Entity\ReferenceField(
				'CREATED_BY',
				'Bitrix\Main\UserTable',
				array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY' => new Entity\ReferenceField(
				'MODIFIED_BY',
				'Bitrix\Main\UserTable',
				array('=this.MODIFIED_BY_ID' => 'ref.ID')
			),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}
}