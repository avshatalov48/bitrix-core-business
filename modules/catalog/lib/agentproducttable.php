<?php

namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class AgentProductTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CONTRACT_ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AgentProduct_Query query()
 * @method static EO_AgentProduct_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_AgentProduct_Result getById($id)
 * @method static EO_AgentProduct_Result getList(array $parameters = [])
 * @method static EO_AgentProduct_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_AgentProduct createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_AgentProduct_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_AgentProduct wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_AgentProduct_Collection wakeUpCollection($rows)
 */

class AgentProductTable extends DataManager
{
	public const PRODUCT_TYPE_PRODUCT = 'PRODUCT';
	public const PRODUCT_TYPE_SECTION = 'SECTION';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_agent_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('CATALOG_AGENT_PRODUCT_ENTITY_ID_FIELD'),
				]
			),
			'CONTRACT_ID' => new IntegerField(
				'CONTRACT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('CATALOG_AGENT_PRODUCT_ENTITY_CONTRACT_ID_FIELD'),
				]
			),
			'CONTRACT' => new Reference(
				'CONTRACT',
				AgentContractTable::class,
				Join::on('this.CONTRACT_ID', 'ref.ID')
			),
			'PRODUCT_ID' => new IntegerField(
				'PRODUCT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('CATALOG_AGENT_PRODUCT_ENTITY_PRODUCT_ID_FIELD'),
				]
			),
			'PRODUCT_TYPE' => new EnumField(
				'PRODUCT_TYPE',
				[
					'required' => true,
					'values' => [self::PRODUCT_TYPE_PRODUCT, self::PRODUCT_TYPE_SECTION],
					'validation' => function()
					{
						return[
							new LengthValidator(null, 8),
						];
					},
					'title' => Loc::getMessage('AGENT_PRODUCT_ENTITY_PRODUCT_TYPE_FIELD'),
				]
			),
		];
	}
}