<?php
namespace Bitrix\Main\Numerator\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\Sequenceable;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\NumberGeneratorFactory;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

/**
 * Class NumeratorTable
 * @package Bitrix\Main\Numerator\Model
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Numerator_Query query()
 * @method static EO_Numerator_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Numerator_Result getById($id)
 * @method static EO_Numerator_Result getList(array $parameters = [])
 * @method static EO_Numerator_Entity getEntity()
 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator_Collection createCollection()
 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator wakeUpObject($row)
 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator_Collection wakeUpCollection($rows)
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
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new StringField('NAME'))
				->configureRequired(true)
			,
			(new StringField('TEMPLATE'))
				->configureRequired(true)
			,
			(new StringField('SETTINGS'))
				->configureRequired(true)
			,
			(new StringField('TYPE'))
				->configureDefaultValue('DEFAULT')
			,
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
			'select' => ['*'],
			'filter' => $filter,
		];
		if ($sort)
		{
			$params['order'] = $sort;
		}
		$results = NumeratorTable::getList($params)->fetchAll();
		foreach ($results as &$numerator)
		{
			$numerator['id'] = $numerator['ID'];
			$numerator['name'] = $numerator['NAME'];
			$numerator['template'] = $numerator['TEMPLATE'];
			$numerator['type'] = $numerator['TYPE'];
		}
		return $results;
	}

	/**
	 * @param $numeratorId
	 * @param $numeratorFields
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
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
			if (!(Numerator::load($numeratorId)))
			{
				$result = new UpdateResult();
				$result->addError(new Error(Loc::getMessage('MAIN_NUMERATOR_EDIT_NUMERATOR_NOT_FOUND_ERROR')));
				return $result;
			}

			$updateRes = NumeratorTable::update($numeratorId, $fields);

			if ($updateRes->isSuccess())
			{
				$numerator = Numerator::load($numeratorId);
				$isNewNumSequential = $numerator->hasSequentialNumber();
				if (!$isNewNumSequential)
				{
					NumeratorSequenceTable::deleteByNumeratorId($numeratorId);
				}
			}
			return $updateRes;
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