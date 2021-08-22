<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MeasureRatioTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * <li> RATIO double mandatory default 1
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MeasureRatio_Query query()
 * @method static EO_MeasureRatio_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MeasureRatio_Result getById($id)
 * @method static EO_MeasureRatio_Result getList(array $parameters = array())
 * @method static EO_MeasureRatio_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_MeasureRatio createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_MeasureRatio_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_MeasureRatio wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_MeasureRatio_Collection wakeUpCollection($rows)
 */

class MeasureRatioTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_measure_ratio';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('MEASURE_RATIO_ENTITY_ID_FIELD')
			)),
			'PRODUCT_ID' => new Main\Entity\IntegerField('PRODUCT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('MEASURE_RATIO_ENTITY_PRODUCT_ID_FIELD')
			)),
			'RATIO' => new Main\Entity\FloatField('RATIO', array(
				'required' => true,
				'title' => Loc::getMessage('MEASURE_RATIO_ENTITY_RATIO_FIELD')
			)),
			'IS_DEFAULT' => new Main\Entity\BooleanField('IS_DEFAULT', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('MEASURE_RATIO_ENTITY_IS_DEFAULT_FIELD')
			)),
			'PRODUCT' => new Main\Entity\ReferenceField(
				'PRODUCT',
				'\Bitrix\Catalog\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
		);
	}

	/**
	 * Return ratio for product list.
	 *
	 * @param array|int $product			Product id list.
	 * @return array|bool
	 * @throws Main\ArgumentException
	 */
	public static function getCurrentRatio($product)
	{
		if (!is_array($product))
			$product = array($product);
		Main\Type\Collection::normalizeArrayValuesByInt($product, true);
		if (empty($product))
			return false;

		$result = array_fill_keys($product, 1);
		$ratioRows = array_chunk($product, 500);
		foreach ($ratioRows as $row)
		{
			$ratioIterator = self::getList(array(
				'select' => array('PRODUCT_ID', 'RATIO'),
				'filter' => array('@PRODUCT_ID' => $row, '=IS_DEFAULT' => 'Y')
			));
			while ($ratio = $ratioIterator->fetch())
			{
				$ratio['PRODUCT_ID'] = (int)$ratio['PRODUCT_ID'];
				$ratioInt = (int)$ratio['RATIO'];
				$ratioFloat = (float)$ratio['RATIO'];
				$ratioResult  = ($ratioFloat > $ratioInt ? $ratioFloat : $ratioInt);
				if ($ratioResult < CATALOG_VALUE_EPSILON)
					continue;
				$result[$ratio['PRODUCT_ID']] = $ratioResult;
			}
			unset($module, $moduleIterator);
		}
		unset($row, $ratioRows);
		return $result;
	}

	/**
	 * Delete all rows for product.
	 * @internal
	 *
	 * @param int $id       Product id.
	 * @return void
	 */
	public static function deleteByProduct($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return;

		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('PRODUCT_ID').' = '.$id
		);
		unset($helper, $conn);
	}
}