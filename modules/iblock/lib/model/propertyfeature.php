<?php
namespace Bitrix\Iblock\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class PropertyFeature
{
	const EVENT_ID_FEATURE_LIST = 'OnPropertyFeatureBuildList'; // event name for build feature list

	const FEATURE_ID_LIST_PAGE_SHOW = 'LIST_PAGE_SHOW'; // show property in element list
	const FEATURE_ID_DETAIL_PAGE_SHOW = 'DETAIL_PAGE_SHOW'; // detail page show property

	/**
	 * Add features for new property. Do not check features in database.
	 *
	 * @param int $propertyId	Property id.
	 * @param array $features	Feature list.
	 * @return Main\Entity\Result
	 */
	public static function addFeatures($propertyId, array $features)
	{
		$result = new Main\Entity\Result();

		$propertyId = (int)$propertyId;
		if ($propertyId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_BAD_PROPERTY_ID')
			));
			return $result;
		}

		if (empty($features))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_EMPTY_FEATURE_LIST')
			));
			return $result;
		}
		$features = self::checkFeatureList($features);
		if ($features === null)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_BAD_FEATURE_LIST')
			));
			return $result;
		}

		foreach ($features as $row)
		{
			$row['PROPERTY_ID'] = $propertyId;
			$internalResult = Iblock\PropertyFeatureTable::add($row);
			if (!$internalResult->isSuccess())
			{
				$result->addErrors($internalResult->getErrors());
				return $result;
			}
		}
		unset($internalResult, $row);

		return $result;
	}

	public static function updateFeatures($propertyId, array $features)
	{
		$result = new Main\Entity\Result();

		$propertyId = (int)$propertyId;
		if ($propertyId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_BAD_PROPERTY_ID')
			));
			return $result;
		}

		return $result;
	}

	/**
	 * Upsert property features.
	 *
	 * @param int $propertyId	Property id.
	 * @param array $features	Feature list.
	 * @return Main\Entity\Result
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function setFeatures($propertyId, array $features)
	{
		$result = new Main\Entity\Result();

		$propertyId = (int)$propertyId;
		if ($propertyId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_BAD_PROPERTY_ID')
			));
			return $result;
		}

		if (empty($features))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_EMPTY_FEATURE_LIST')
			));
			return $result;
		}
		$features = self::checkFeatureList($features);
		if ($features === null)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('PROPERTY_FEATURE_ERR_BAD_FEATURE_LIST')
			));
			return $result;
		}

		$currentList = [];
		$idList = [];
		$iterator = Iblock\PropertyFeatureTable::getList([
			'select' => ['*'],
			'filter' => ['=PROPERTY_ID' => $propertyId]
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$currentList[self::getIndex($row)] = $row;
			$idList[$row['ID']] = $row['ID'];
		}
		unset($row, $iterator);

		foreach ($features as $index => $row)
		{
			if (isset($currentList[$index]))
			{
				$internalResult = Iblock\PropertyFeatureTable::update(
					$currentList[$index]['ID'],
					['IS_ENABLED' => $row['IS_ENABLED']]
				);
				if ($internalResult->isSuccess())
					unset($idList[$currentList[$index]['ID']]);
			}
			else
			{
				$row['PROPERTY_ID'] = $propertyId;
				$internalResult = Iblock\PropertyFeatureTable::add($row);
			}
			if (!$internalResult->isSuccess())
			{
				$result->addErrors($internalResult->getErrors());
				return $result;
			}
		}
		unset($internalResult, $index, $row);
		unset($currentList);

		if (!empty($idList))
		{
			$conn = Main\Application::getConnection();
			$helper = $conn->getSqlHelper();
			$query = 'delete from '.$helper->quote(Iblock\PropertyFeatureTable::getTableName()).
				' where '.$helper->quote('PROPERTY_ID').' = '.$propertyId.
				' and '.$helper->quote('ID').' in ('.implode(',', $idList).')';
			$conn->queryExecute($query);
			unset($query, $helper, $conn);
		}
		unset($idList);

		return $result;
	}

	/**
	 * Returns verified list of features for add, update or set.
	 *
	 * @param array $list	Raw features.
	 * @return array|null
	 */
	protected static function checkFeatureList(array $list)
	{
		if (empty($list))
			return null;

		$result = [];
		foreach ($list as $rawRow)
		{
			$row = self::checkFeature($rawRow);
			if ($row === null)
				return null;
			$result[self::getIndex($row)] = $row;
		}
		unset($rawRow);

		return $result;
	}

	/**
	 * Checks feature parameters. Returns normalized data or null.
	 *
	 * @param array $feature	Raw feature parameters.
	 * @return array|null
	 */
	protected static function checkFeature(array $feature)
	{
		if (empty($feature))
			return null;
		if (!isset($feature['MODULE_ID']))
			return null;
		$feature['MODULE_ID'] = trim((string)$feature['MODULE_ID']);
		if ($feature['MODULE_ID'] === '')
			return null;
		if (!isset($feature['FEATURE_ID']))
			return null;
		$feature['FEATURE_ID'] = trim((string)$feature['FEATURE_ID']);
		if ($feature['FEATURE_ID'] === '')
			return null;
		if (!isset($feature['IS_ENABLED']))
			return null;
		$feature['IS_ENABLED'] = trim((string)$feature['IS_ENABLED']);
		if ($feature['IS_ENABLED'] !== 'Y' && $feature['IS_ENABLED'] !== 'N')
			return null;

		return [
			'MODULE_ID' => $feature['MODULE_ID'],
			'FEATURE_ID' => $feature['FEATURE_ID'],
			'IS_ENABLED' => $feature['IS_ENABLED']
		];
	}

	/**
	 * Returns unique feature index for search.
	 *
	 * @param array $feature	Normalize feature parameters.
	 * @return string
	 */
	public static function getIndex(array $feature)
	{
		return $feature['MODULE_ID'].':'.$feature['FEATURE_ID'];
	}

	/**
	 * Build a list of available features for a property.
	 *
	 * @param array $property		Property description.
	 * @param array $description	Additional description.
	 * @return array
	 */
	public static function getPropertyFeatureList(array $property, array $description = [])
	{
		$result = self::getIblockFeatureList();

		$event = new Main\Event(
			'iblock',
			__CLASS__.'::'.self::EVENT_ID_FEATURE_LIST,
			['property' => $property, 'description' => $description]
		);
		$event->send();
		foreach($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() !== Main\EventResult::SUCCESS)
				continue;
			$list = $eventResult->getParameters();
			if (empty($list) || !is_array($list))
				continue;
			foreach ($list as $item)
			{
				if (empty($item) || !is_array($item))
					continue;
				$item = self::prepareFeatureDescription($item);
				if (empty($item))
					continue;
				$result[] = $item;
			}
			unset($item, $list);
		}
		unset($eventResult);

		return $result;
	}

	/**
	 * Returns iblock properties identifiers (ID or CODE), showed in element list.
	 *
	 * @param int $iblockId			Iblock identifier.
	 * @param array $parameters		Options.
	 * 	keys are case sensitive:
	 *		<ul>
	 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
	 *		</ul>
	 * @return array|null
	 */
	public static function getListPageShowPropertyCodes($iblockId, array $parameters = [])
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return null;

		return self::getFilteredPropertyCodes(
			$iblockId,
			[
				'=MODULE_ID' => 'iblock',
				'=FEATURE_ID' => self::FEATURE_ID_LIST_PAGE_SHOW
			],
			$parameters
		);
	}

	/**
	 * Returns iblock properties identifiers (ID or CODE), showed on detail element page.
	 *
	 * @param int $iblockId			Iblock identifier.
	 * @param array $parameters		Options.
	 * 	keys are case sensitive:
	 *		<ul>
	 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
	 *		</ul>
	 * @return array|null
	 */
	public static function getDetailPageShowProperties($iblockId, array $parameters = [])
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return null;

		return self::getFilteredPropertyCodes(
			$iblockId,
			[
				'=MODULE_ID' => 'iblock',
				'=FEATURE_ID' => self::FEATURE_ID_DETAIL_PAGE_SHOW
			],
			$parameters
		);
	}

	/**
	 * Internal method for getting the list of features by filter (within one information block).
	 *
	 * @param int $iblockId			Iblock identifier.
	 * @param array $filter			Feature filter.
	 * @param array $parameters		Options.
	 * 	keys are case sensitive:
	 *		<ul>
	 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
	 *		</ul>
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function getFilteredPropertyCodes($iblockId, array $filter, array $parameters = [])
	{
		if ((string)Main\Config\Option::get('iblock', 'property_features_enabled') !== 'Y')
			return null;

		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return null;
		if (!isset($filter['=MODULE_ID']) || !isset($filter['=FEATURE_ID']))
			return null;

		$getCode = (isset($parameters['CODE']) && $parameters['CODE'] == 'Y');

		$result = [];
		$filter['=PROPERTY.IBLOCK_ID'] = $iblockId;
		$filter['=PROPERTY.ACTIVE'] = 'Y';
		$filter['=IS_ENABLED'] = 'Y';
		$iterator = Iblock\PropertyFeatureTable::getList([
			'select' => [
				'IBLOCK_PROPERTY_ID' => 'PROPERTY.ID',
				'IBLOCK_PROPERTY_CODE' => 'PROPERTY.CODE',
				'IBLOCK_PROPERTY_SORT' => 'PROPERTY.SORT'
			],
			'filter' => $filter,
			'order' => ['IBLOCK_PROPERTY_SORT' => 'ASC', 'IBLOCK_PROPERTY_ID' => 'ASC']
		]);
		while ($row = $iterator->fetch())
			$result[(int)$row['IBLOCK_PROPERTY_ID']] = self::getPropertyCode(
				[
					'ID' => $row['IBLOCK_PROPERTY_ID'],
					'CODE' => $row['IBLOCK_PROPERTY_CODE']
				],
				$getCode
			);
		unset($row, $iterator);
		unset($filter, $getCode);

		return (!empty($result) ? array_values($result) : null);
	}

	/**
	 * Returns property identifier. Internal method.
	 *
	 * @param array $property	Property description (the ID and CODE fields are definitely needed)
	 * @param bool $getCode		if true, returns property code or ID (if CODE is empty). Other - returns ID.
	 * @return int|string
	 */
	protected static function getPropertyCode(array $property, $getCode = false)
	{
		if ($getCode)
		{
			$code = (string)$property['CODE'];
			return ($code !== '' ? $code : $property['ID']);
		}
		else
		{
			return $property['ID'];
		}
	}

	/**
	 * Check and normalize feature description. Internal method.
	 *
	 * @param array $row	Feature description.
	 * @return array
	 */
	private static function prepareFeatureDescription(array $row)
	{
		if (empty($row))
			return [];
		if (
			!isset($row['MODULE_ID'])
			|| !isset($row['FEATURE_ID'])
			|| !isset($row['FEATURE_NAME'])
		)
			return [];

		return [
			'MODULE_ID' => $row['MODULE_ID'],
			'FEATURE_ID' => $row['FEATURE_ID'],
			'FEATURE_NAME' => $row['FEATURE_NAME']
		];
	}

	/**
	 * Returns a list of features available to any information block.
	 * Used when building a list of features for an information block property.
	 *
	 * @return array
	 */
	private static function getIblockFeatureList()
	{
		return [
			[
				'MODULE_ID' => 'iblock',
				'FEATURE_ID' => self::FEATURE_ID_LIST_PAGE_SHOW,
				'FEATURE_NAME' => Loc::getMessage('PROPERTY_FEATURE_NAME_LIST_PAGE_SHOW')
			],
			[
				'MODULE_ID' => 'iblock',
				'FEATURE_ID' => self::FEATURE_ID_DETAIL_PAGE_SHOW,
				'FEATURE_NAME' => Loc::getMessage('PROPERTY_FEATURE_NAME_DETAIL_PAGE_SHOW')
			]
		];
	}

	/**
	 * Returns three if the feature engine is enabled.
	 *
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isEnabledFeatures()
	{
		return ((string)Main\Config\Option::get('iblock', 'property_features_enabled') == 'Y');
	}

	/**
	 * Returns true, if property features exist.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isPropertyFeaturesExist()
	{
		$featureCount = (int)Iblock\PropertyFeatureTable::getCount([], ['ttl' => 86400]);
		return ($featureCount > 0);
	}
}