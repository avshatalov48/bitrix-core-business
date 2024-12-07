<?php

namespace Bitrix\Main;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class SiteTemplateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SiteTemplate_Query query()
 * @method static EO_SiteTemplate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SiteTemplate_Result getById($id)
 * @method static EO_SiteTemplate_Result getList(array $parameters = [])
 * @method static EO_SiteTemplate_Entity getEntity()
 * @method static \Bitrix\Main\EO_SiteTemplate createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_SiteTemplate_Collection createCollection()
 * @method static \Bitrix\Main\EO_SiteTemplate wakeUpObject($row)
 * @method static \Bitrix\Main\EO_SiteTemplate_Collection wakeUpCollection($rows)
 */
class SiteTemplateTable extends Entity\DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_site_template';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'CONDITION' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'TEMPLATE' => array(
				'data_type' => 'string'
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
			(new Fields\ExpressionField(
				'EMPTY_CONDITION',
				"CASE
					WHEN %s IS NULL OR %s = '' THEN 1
					ELSE 2
				END",
				['CONDITION', 'CONDITION']
			)),
		);
	}
}
