<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ExportedProductQueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ExportedProductQueue_Query query()
 * @method static EO_ExportedProductQueue_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ExportedProductQueue_Result getById($id)
 * @method static EO_ExportedProductQueue_Result getList(array $parameters = [])
 * @method static EO_ExportedProductQueue_Entity getEntity()
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection createCollection()
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue wakeUpObject($row)
 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection wakeUpCollection($rows)
 */
class ExportedProductQueueTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_catalog_exported_product_queue';
	}

	public static function getMap()
	{
		return [
			'QUEUE_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'PRODUCT_IDS' => [
				'data_type' => 'string',
				'required' => false,
			],
		];
	}
}
