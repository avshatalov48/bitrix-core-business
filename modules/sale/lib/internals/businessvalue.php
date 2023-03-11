<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class BusinessValueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BusinessValue_Query query()
 * @method static EO_BusinessValue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BusinessValue_Result getById($id)
 * @method static EO_BusinessValue_Result getList(array $parameters = array())
 * @method static EO_BusinessValue_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValue createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValue_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValue wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValue_Collection wakeUpCollection($rows)
 */
class BusinessValueTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_bizval';
	}

	const COMMON_PERSON_TYPE_ID = 0;
	const COMMON_CONSUMER_KEY = 'COMMON';

	public static function getMap()
	{
		return array(

			new Main\Entity\StringField('CODE_KEY', array(
				'primary' => true,
				'size' => 50,
			)),

			new Main\Entity\StringField('CONSUMER_KEY', array(
				'primary' => true,
				'size' => 50,
				'save_data_modification' => function ()
				{
					return array(
						function ($value)
						{
							return $value ?: BusinessValueTable::COMMON_CONSUMER_KEY;
						}
					);
				},
				'fetch_data_modification' => function ()
				{
					return array(
						function ($value)
						{
							return $value == BusinessValueTable::COMMON_CONSUMER_KEY ? null : $value;
						}
					);
				}
			)),

			new Main\Entity\IntegerField('PERSON_TYPE_ID', array(
				'primary' => true,
				'size' => 50,
				'save_data_modification' => function ()
				{
					return array(
						function ($value)
						{
							return $value ?: BusinessValueTable::COMMON_PERSON_TYPE_ID;
						}
					);
				},
				'fetch_data_modification' => function ()
				{
					return array(
						function ($value)
						{
							return $value == BusinessValueTable::COMMON_PERSON_TYPE_ID ? null : (int) $value;
						}
					);
				}
			)),

			new Main\Entity\StringField('PROVIDER_KEY', array(
				'required' => true,
				'size' => 50,
			)),

			new Main\Entity\StringField('PROVIDER_VALUE'),

		);
	}

	/**
	 * Deletes all business values by CODE_KEY (including COMMON)
	 *
	 * @param string $codeKey
	 * @return void
	 */
	public static function deleteByCodeKey(string $codeKey): void
	{
		$businessValueIterator = static::getList([
			'filter' => [
				'=CODE_KEY' => $codeKey,
			],
		]);
		while ($businessValue = $businessValueIterator->fetch())
		{
			if ($businessValue['PROVIDER_KEY'] === 'FILE')
			{
				\CFile::Delete($businessValue['PROVIDER_VALUE']);
			}

			static::delete([
				'CODE_KEY' => $businessValue['CODE_KEY'],
				'CONSUMER_KEY' => $businessValue['CONSUMER_KEY'] ?? static::COMMON_CONSUMER_KEY,
				'PERSON_TYPE_ID' => $businessValue['PERSON_TYPE_ID'] ?? static::COMMON_PERSON_TYPE_ID,
			]);
		}
	}
}
