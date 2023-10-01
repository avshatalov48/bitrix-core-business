<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class BusinessValuePersonDomainTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BusinessValuePersonDomain_Query query()
 * @method static EO_BusinessValuePersonDomain_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_BusinessValuePersonDomain_Result getById($id)
 * @method static EO_BusinessValuePersonDomain_Result getList(array $parameters = [])
 * @method static EO_BusinessValuePersonDomain_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValuePersonDomain createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValuePersonDomain_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValuePersonDomain wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValuePersonDomain_Collection wakeUpCollection($rows)
 */
class BusinessValuePersonDomainTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_bizval_persondomain';
	}

	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField('PERSON_TYPE_ID', array('primary' => true)),
			new Main\Entity\StringField ('DOMAIN', array('primary' => true, 'size' => 1)),

			new Main\Entity\ReferenceField('PERSON_TYPE_REFERENCE', 'Bitrix\Sale\Internals\PersonTypeTable',
				array('=this.PERSON_TYPE_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
		);
	}

	public static function deleteByPersonTypeId(int $personTypeId) : Main\ORM\Data\DeleteResult
	{
		$result = new Main\ORM\Data\DeleteResult();

		$dbRes = static::getList([
			'select' => ['PERSON_TYPE_ID', 'DOMAIN'],
			'filter' => [
				'=PERSON_TYPE_ID' => $personTypeId
			]
		]);

		while ($item = $dbRes->fetch())
		{
			$r = static::delete([
				'PERSON_TYPE_ID' => $item['PERSON_TYPE_ID'],
				'DOMAIN' => $item['DOMAIN'],
			]);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}
}
