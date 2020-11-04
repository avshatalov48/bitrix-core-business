<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Main;

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