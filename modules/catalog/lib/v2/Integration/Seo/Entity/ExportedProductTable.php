<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class ExportedProductTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ExportedProduct_Query query()
 * @method static EO_ExportedProduct_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ExportedProduct_Result getById($id)
 * @method static EO_ExportedProduct_Result getList(array $parameters = [])
 * @method static EO_ExportedProduct_Entity getEntity()
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection createCollection()
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct wakeUpObject($row)
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection wakeUpCollection($rows)
 */
class ExportedProductTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_catalog_exported_product';
	}

	public static function getObjectClass()
	{
		return ExportedProduct::class;
	}

	public static function getCollectionClass()
	{
		return ExportedProductCollection::class;
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'PRODUCT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'SERVICE_ID' => [
				'data_type' => 'string',
				'validation' => [__CLASS__, 'validateServiceId'],
				'required' => true,
			],
			'TIMESTAMP_X' => [
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => [__CLASS__, 'getCurrentDate'],
			],
			'ERROR' => [
				'data_type' => 'text',
				'required' => false,
			],
		];
	}

	public static function validateServiceId(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	public static function getCurrentDate(): DateTime
	{
		return new DateTime();
	}

	public static function deleteProduct(int $id): void
	{
		if ($id <= 0)
		{
			return;
		}

		$conn = \Bitrix\Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from ' . $helper->quote(self::getTableName())
			. ' where ' . $helper->quote('PRODUCT_ID') . ' = ' . $id
		);
		unset($helper, $conn);
	}

}
