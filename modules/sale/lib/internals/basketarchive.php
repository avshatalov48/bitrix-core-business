<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class BasketArchiveTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BasketArchive_Query query()
 * @method static EO_BasketArchive_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_BasketArchive_Result getById($id)
 * @method static EO_BasketArchive_Result getList(array $parameters = [])
 * @method static EO_BasketArchive_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BasketArchive createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BasketArchive_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BasketArchive wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BasketArchive_Collection wakeUpCollection($rows)
 */
class BasketArchiveTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_basket_archive';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ID',
				array(
					'autocomplete' => true,
					'primary' => true,
				)
			),
			
			new Main\Entity\IntegerField(
				'ARCHIVE_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField(
				'PRODUCT_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField('PRODUCT_PRICE_ID'),

			new Main\Entity\StringField(
				'NAME',
				array(
					'size' => 255,
					'required' => true,
				)
			),


			new Main\Entity\FloatField(
				'PRICE',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\StringField('MODULE'),

			new Main\Entity\FloatField(
				'QUANTITY',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\FloatField(
				'WEIGHT',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\StringField(
				'CURRENCY',
				array(
					'required' => true,
					'size' => 3
				)
			),

			new Main\Entity\StringField(
				'PRODUCT_XML_ID',
				array(
					'size' => 100
				)
			),

			new Main\Entity\StringField(
				'MEASURE_NAME',
				array(
					'size' => 50
				)
			),

			new Main\Entity\IntegerField('TYPE'),

			new Main\Entity\IntegerField('SET_PARENT_ID'),

			new Main\Entity\IntegerField('MEASURE_CODE'),

			new Main\Entity\DatetimeField('DATE_INSERT'),
			
			new Main\Entity\StringField('BASKET_DATA'),

			new Main\Entity\ReferenceField(
				'BASKET_PACKED',
				'Bitrix\Sale\Internals\BasketArchivePacked',
				array('=this.ID' => 'ref.BASKET_ARCHIVE_ID'),
				array('join_type' => 'INNER')
			)
		);
	}

	/**
	 * Adds row to entity table
	 *
	 * @param array $data An array with fields like
	 * 	array(
	 * 		"fields" => array(
	 * 			"FIELD1" => "value1",
	 * 			"FIELD2" => "value2",
	 * 		),
	 * 		"auth_context" => \Bitrix\Main\Authentication\Context object
	 *	)
	 *	or just a plain array of fields.
	 *
	 * @return Main\Entity\AddResult Contains ID of inserted row
	 *
	 * @throws \Exception
	 */
	public static function add(array $data)
	{
		$basketData = $data['BASKET_DATA'];
		unset($data['BASKET_DATA']);

		$result = parent::add($data);

		if ($result->isSuccess())
		{
			BasketArchivePackedTable::add(array(
				"BASKET_ARCHIVE_ID" => $result->getId(),
				"BASKET_DATA" => $basketData
			));
		}

		return $result;
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return Main\Entity\DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		$result = parent::delete($primary);

		if ($result->isSuccess())
		{
			$checkOrderData = BasketArchivePackedTable::getById($primary);
			if ($checkOrderData->fetch())
			{
				BasketArchivePackedTable::delete($primary);
			}
		}

		return $result;
	}
}