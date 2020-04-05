<?php
namespace Bitrix\Main\Numerator\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Numerator\Generator\Contract\Sequenceable;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\NumberGeneratorFactory;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

/**
 * Class NumeratorTable
 * @package Bitrix\Main\Numerator\Model
 */
class NumeratorTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_numerator';
	}

	/**
	 * @return array
	 * @throws SystemException
	 */
	public static function getMap()
	{
		return [
			new IntegerField('ID', [
				'primary'      => true,
				'autocomplete' => true,
			]),
			new StringField('NAME', [
				'required' => true,
			]),
			new StringField('TEMPLATE', [
				'required' => true,
			]),
			new StringField('SETTINGS', [
				'required' => true,
			]),
			new StringField('TYPE', [
				'default_value' => 'DEFAULT',
			]),
			new DatetimeField('CREATED_AT'),
			new IntegerField('CREATED_BY'),
			new DatetimeField('UPDATED_AT'),
			new IntegerField('UPDATED_BY'),
		];
	}

	/**
	 * @return int|null
	 */
	private static function getCurrentUserId()
	{
		global $USER;
		$userId = 0;
		if ($USER && is_object($USER) && $USER->isAuthorized())
		{
			$userId = $USER->getID();
		}
		return $userId;
	}

	/**
	 * @param $type
	 * @param $sort
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function getNumeratorList($type, $sort)
	{
		$filter = ['=TYPE' => $type];
		if ($type == 'ALL')
		{
			$filter = [];
		}
		$params = [
			'select' => ['id' => 'ID', 'name' => 'NAME', 'template' => 'TEMPLATE', 'type' => 'TYPE',],
			'filter' => $filter,
		];
		if ($sort)
		{
			$params['order'] = $sort;
		}
		return NumeratorTable::getList($params)->fetchAll();
	}

	/**
	 * @param $numeratorId
	 * @param $numeratorFields
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function saveNumerator($numeratorId, $numeratorFields)
	{
		$fields = [
			'NAME'       => $numeratorFields['NAME'],
			'TEMPLATE'   => $numeratorFields['TEMPLATE'],
			'TYPE'       => $numeratorFields['TYPE'],
			'SETTINGS'   => Json::encode($numeratorFields['SETTINGS']),
			'UPDATED_AT' => new DateTime(),
			'UPDATED_BY' => static::getCurrentUserId(),
		];
		if ($numeratorId)
		{
			return NumeratorTable::update($numeratorId, $fields);
		}
		$fields['CREATED_AT'] = new DateTime();
		$fields['CREATED_BY'] = static::getCurrentUserId();
		return NumeratorTable::add($fields);
	}

	/**
	 * @param $numeratorId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws SystemException
	 */
	public static function loadSettings($numeratorId)
	{
		$numerator = static::getList([
			'select' => ['*',],
			'filter' => ['=ID' => $numeratorId],
		])->fetch();

		if ($numerator)
		{
			$result = [];
			$result[Numerator::getType()] = [
				'idFromDb' => $numerator['ID'],
				'name'     => $numerator['NAME'],
				'template' => $numerator['TEMPLATE'],
				'type'     => $numerator['TYPE'],
			];
			$numeratorGenerators = Json::decode($numerator['SETTINGS']);
			$numberGeneratorFactory = new NumberGeneratorFactory();
			foreach ($numeratorGenerators as $generatorType => $numeratorGenerator)
			{
				$class = $numberGeneratorFactory->getClassByType($generatorType);
				if (in_array(Sequenceable::class, class_implements($class)))
				{
					$numeratorGenerators[$generatorType]['numeratorId'] = $numeratorId;
				}
			}
			return array_merge($result, $numeratorGenerators);
		}

		return $numerator;
	}
}