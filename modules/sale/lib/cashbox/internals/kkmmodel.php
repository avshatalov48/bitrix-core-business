<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Sale\Cashbox\Cashbox1C;

/**
 * Class KkmModelTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_KkmModel_Query query()
 * @method static EO_KkmModel_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_KkmModel_Result getById($id)
 * @method static EO_KkmModel_Result getList(array $parameters = [])
 * @method static EO_KkmModel_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_KkmModel createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_KkmModel_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_KkmModel wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_KkmModel_Collection wakeUpCollection($rows)
 */
class KkmModelTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_kkm_model';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'autocomplete' => true,
				'data_type' => 'integer',
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
		);
	}

	public static function delete($primary)
	{
		if ($primary == Cashbox1C::getId())
		{
			$cacheManager = Application::getInstance()->getManagedCache();
			$cacheManager->clean(Cashbox1C::CACHE_ID);
		}

		return parent::delete($primary);
	}
}
