<?php
namespace Bitrix\Sale\Exchange\Integration\Entity;


use Bitrix\Main;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\Integration\Entity;

/**
 * Class B24integrationTokenTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_B24integrationToken_Query query()
 * @method static EO_B24integrationToken_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_B24integrationToken_Result getById($id)
 * @method static EO_B24integrationToken_Result getList(array $parameters = array())
 * @method static EO_B24integrationToken_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\Token createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationToken_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\Token wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Integration\Entity\EO_B24integrationToken_Collection wakeUpCollection($rows)
 */
class B24integrationTokenTable extends Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_b24integration_token';
	}

	public static function getObjectClass()
	{
		return Entity\Token::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return [
			new Fields\IntegerField("ID", [
				"primary" => true,
				"autocomplete" => true
			]),
			new Fields\StringField("GUID", [
				"required" => false
			]),
			new Fields\StringField("ACCESS_TOKEN", [
				"required" => true
			]),
			new Fields\StringField("REFRESH_TOKEN", [
				"required" => true
			]),
			new Fields\StringField("REST_ENDPOINT", [
				"required" => true
			]),
			new Fields\StringField("PORTAL_ID", [
				"required" => true
			]),
			new Fields\DatetimeField("CREATED", [
				"required" => true,
				"default_value" => new DateTime()
			]),
			new Fields\DatetimeField("CHANGED", [
				"required" => true,
				"default_value" => new DateTime()
			]),
			new Fields\DatetimeField("EXPIRES", [
				"required" => true,
				"default_value" => (new DateTime())->add("1 hour")
			])
		];
	}
}