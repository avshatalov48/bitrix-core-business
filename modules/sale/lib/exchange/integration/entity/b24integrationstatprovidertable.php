<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Main;

/**
 * Class B24IntegrationStatProviderTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_B24IntegrationStatProvider_Query query()
 * @method static EO_B24IntegrationStatProvider_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_B24IntegrationStatProvider_Result getById($id)
 * @method static EO_B24IntegrationStatProvider_Result getList(array $parameters = array())
 * @method static EO_B24IntegrationStatProvider_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationStatProvider createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationStatProvider_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationStatProvider wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24IntegrationStatProvider_Collection wakeUpCollection($rows)
 */
class B24IntegrationStatProviderTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_b24integration_stat_provider';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return [
			new Main\Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Main\Entity\StringField('NAME', [ 'required' => true]),
			new Main\Entity\StringField('EXTERNAL_SERVER_HOST', [ 'required' => true]),
			new Main\Entity\StringField("XML_ID", ['required' => true]),
			new Main\Entity\DatetimeField('TIMESTAMP_X', ['default_value' => new Main\Type\DateTime()]),
			new Main\Entity\TextField('SETTINGS',['serialized' => true])
		];
	}
}