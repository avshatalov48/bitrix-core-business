<?php
namespace Bitrix\Iblock\LandingSource;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Landing,
	Bitrix\Landing\Node;

Loc::loadMessages(__FILE__);

class Element
{
	const SELECTOR_ENTITY = 'element';

	const FIELD_ALLOWED_SELECT = 0x0001;
	const FIELD_ALLOWED_ORDER = 0x0002;
	const FIELD_ALLOWED_ALL = self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_ORDER;

	protected static $catalogIncluded = null;

	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onBuildSourceListHandler(Main\Event $event)
	{
		/** @var Landing\Source\Selector $selector */
		$selector = $event->getParameter('SELECTOR');

		$iblockList = [];
		$catalogs = [];
		$restrictions = $selector->getModuleRestrictions('iblock');
		if (!empty($restrictions))
		{
			$getCatalogs = $selector->checkSiteMode([Landing\Source\Selector::SITE_MODE_STORE]);
			if (!empty($restrictions['IBLOCK_ID']) && is_array($restrictions['IBLOCK_ID']))
			{
				$catalogs = static::getCatalogs($restrictions['IBLOCK_ID']);
				if (!empty($catalogs))
					$catalogs = array_fill_keys($catalogs, true);

				$filter = ['ID' => $restrictions['IBLOCK_ID']];
				if (!empty($restrictions['SITE_ID']))
				{
					$filter['SITE_ID'] = $restrictions['SITE_ID'];
				}
				$iterator = \CIBlock::GetList(['SORT' => 'ASC'], $filter, false);
				while ($row = $iterator->Fetch())
				{
					$row['ID'] = (int)$row['ID'];
					if (!$getCatalogs && isset($catalogs[$row['ID']]))
					{
						continue;
					}
					$iblockList[] = [
						'ID' => $row['ID'],
						'TITLE' => $row['NAME']
					];
				}
				unset($row, $iterator);
				unset($filter);
			}
		}
		unset($restrictions);
		if (empty($iblockList))
		{
			return new Main\EventResult(Main\EventResult::ERROR, null, 'iblock');
		}

		$elementFields = static::getElementFields();
		$productFields = static::getProductFields();

		$result = [];
		foreach ($iblockList as $iblock)
		{
			$sourceId = self::SELECTOR_ENTITY.$iblock['ID'];
			$path = $selector->getSourceFilterBaseUri('iblock', $sourceId);
			$action = $selector->getResultAction();

			switch ($action['TYPE'])
			{
				case Landing\Source\Selector::ACTION_TYPE_EVENT:
					$resultActionType = Iblock\Component\Selector\Entity::RESULT_ACTION_TYPE_EVENT;
					break;
				case Landing\Source\Selector::ACTION_TYPE_SLIDER:
					$resultActionType = Iblock\Component\Selector\Entity::RESULT_ACTION_TYPE_SLIDER;
					break;
				default:
					$resultActionType = '';
					break;
			}
			if ($resultActionType === '')
				continue;

			$actionList = $selector->getDefaultLinkActions();
			$fields = $elementFields;
			if (isset($catalogs[$iblock['ID']]))
			{
				$fields = array_merge($fields, $productFields);
				$actionList = array_merge($actionList, static::getProductActionList());
			}

			$properties = static::getIblockProperties($iblock['ID']);
			if (!empty($properties))
				$fields = array_merge($fields, $properties);
			unset($properties);

			$fields = array_merge($fields, static::getLinkDefinition($actionList));
			unset($actionList);

			$dataSettings = [
				'ORDER' => static::getOrderFields($fields),
				'FIELDS' => static::getShowedFields($fields)
			];

			$parameters = [
				'GRID_ID' => 'iblock'.$sourceId.'_selector',
				'BASE_LINK' => $path->getUri(),
				'IBLOCK_ID' => $iblock['ID'],
				'MULTIPLE_SELECT' => 'N',
				'USE_MODE' => Iblock\Component\Selector\Entity::MODE_SLIDER,
				'RESULT_ACTION_TYPE' => $resultActionType,
				'RESULT_ACTION_NAME' => $action['NAME'],
				'RESULT_DATA_TYPE' => Iblock\Component\Selector\Entity::RESULT_DATA_TYPE_FILTER,
				'RESULT_DATA_SET_LIST' => [],
				'PAGETITLE_FILTER' => 'Y',
				'SIMPLE_PRODUCTS' => 'Y'
			];

			$row = [
				'SOURCE_ID' => $sourceId,
				'TITLE' => $iblock['TITLE'],
				'TYPE' => Landing\Source\Selector::SOURCE_TYPE_COMPONENT,
				'SETTINGS' => [
					'COMPONENT_NAME' => 'bitrix:iblock.selector.landing',
					'COMPONENT_TEMPLATE_NAME' => '.default',
					'COMPONENT_PARAMS' => $parameters,
					'WRAPPER' => [
						'USE_PADDING' => false,
						'PLAIN_VIEW' => false,
						'USE_UI_TOOLBAR' => 'N'
					]
				],
				'SOURCE_FILTER' => [
					'IBLOCK_ID' => $iblock['ID'],
					'ACTIVE_DATE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'R'
				],
				'DATA_SETTINGS' => $dataSettings,
				'DATA_LOADER' => '\Bitrix\Iblock\LandingSource\DataLoader'
			];
			$result[] = $row;
		}
		unset($sourceId, $path, $action, $dataSettings, $parameters, $row, $iblock);
		unset($iblockList);

		unset($selector);

		return new Main\EventResult(Main\EventResult::SUCCESS, $result, 'iblock');
	}

	/**
	 * @param array $iblocks
	 * @return array
	 */
	protected static function getCatalogs(array $iblocks = [])
	{
		$result = [];
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$filter = [];
			if (!empty($iblocks))
				$filter['@IBLOCK_ID'] = $iblocks;

			$iterator = Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID'],
				'filter' => $filter
			]);
			while ($row = $iterator->fetch())
				$result[] = (int)$row['IBLOCK_ID'];
			unset($row, $iterator, $filter);
		}
		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getElementFields()
	{
		$result = [];

		$result['ID'] = [
			'ID' => 'ID',
			'NAME' => 'ID',
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_ORDER
		];
		$result['NAME'] = [
			'ID' => 'NAME',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_NAME'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_ALL
		];
		$result['PREVIEW_TEXT'] = [
			'ID' => 'PREVIEW_TEXT',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_PREVIEW_TEXT'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT
		];
		$result['PREVIEW_PICTURE'] = [
			'ID' => 'PREVIEW_PICTURE',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_PREVIEW_PICTURE'),
			'TYPE' => Node\Type::IMAGE,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT
		];
		$result['DETAIL_TEXT'] = [
			'ID' => 'DETAIL_TEXT',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_DETAIL_TEXT'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT
		];
		$result['DETAIL_PICTURE'] = [
			'ID' => 'DETAIL_PICTURE',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_DETAIL_PICTURE'),
			'TYPE' => Node\Type::IMAGE,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT
		];
		$result['SORT'] = [
			'ID' => 'SORT',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_FIELD_SORT'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_ORDER
		];

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getProductFields()
	{
		$result = [];

		$result['AVAILABLE'] = [
			'ID' => 'AVAILABLE',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_PRODUCT_FIELD_AVAILABLE'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_ALL
		];
/*		$result['PRICE'] = [
			'ID' => 'PRICE',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_PRODUCT_FIELD_PRICE'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_ALL
		]; */
/*		$result['WEIGHT'] = [
			'ID' => 'WEIGHT',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_PRODUCT_FIELD_WEIGHT'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT
		];
		$result['SIZES'] = [
			'ID' => 'SIZES',
			'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_PRODUCT_FIELD_SIZES'),
			'TYPE' => Node\Type::TEXT,
			'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			'COMPILE' => [
				'FIELDS' => ['LENGTH', 'WIDTH', 'HEIGHT'],
				'SEPARATOR' => ' * '
			]
		]; */

		return $result;
	}

	/**
	 * @param int $iblockId
	 * @return array
	 */
	protected static function getIblockProperties($iblockId)
	{
		$result = [];

		$listCodes = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes($iblockId);
		$detailCodes = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes($iblockId);
		if (empty($listCodes) && empty($detailCodes))
			return $result;

		$allCodes = array_unique(array_merge($listCodes, $detailCodes));
		$listCodes = array_fill_keys($listCodes, true);
		$detailCodes = array_fill_keys($detailCodes, true);

		$iterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID', 'IBLOCK_ID', 'NAME', 'SORT', 'PROPERTY_TYPE',
				'MULTIPLE', 'FILE_TYPE',
				'USER_TYPE', 'USER_TYPE_SETTINGS_LIST'
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'@ID' => $allCodes,
				'=ACTIVE' => 'Y'
			],
			'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['ID'];
			$index = 'PROPERTY_'.$row['ID'];
			$titleCode = 'IBLOCK_LANDING_SOURCE_ELEMENT_PROPERTY_TITLE';
			if (!isset($listCodes[$id]) || !isset($detailCodes[$id]))
			{
				$titleCode = (isset($listCodes[$id])
					? 'IBLOCK_LANDING_SOURCE_ELEMENT_PROPERTY_LIST_TITLE'
					: 'IBLOCK_LANDING_SOURCE_ELEMENT_PROPERTY_DETAIL_TITLE'
				);
			}
			$title =  Loc::getMessage(
				$titleCode,
				['#NAME#' => $row['NAME']]
			);

			switch ($row['PROPERTY_TYPE'])
			{
				case Iblock\PropertyTable::TYPE_FILE:
					if (self::checkImageProperty($row))
					{
						$result[$index] = [
							'ID' => $index,
							'NAME' => $title,
							'TYPE' => Node\Type::IMAGE,
							'ALLOWED' => self::FIELD_ALLOWED_SELECT
						];
					}
					break;
				default:
					$result[$index] = [
						'ID' => $index,
						'NAME' => $title,
						'TYPE' => Node\Type::TEXT,
						'ALLOWED' => ($row['MULTIPLE'] == 'Y' ? self::FIELD_ALLOWED_SELECT : self::FIELD_ALLOWED_ALL)
					];
					break;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getProductActionList()
	{
		return [
			[
				'type' => 'buy',
				'name' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_ACTION_TITLE_BUY')
			],
			[
				'type' => 'addToCart',
				'name' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_ACTION_TITLE_ADD_TO_CART')
			]
		];
	}

	/**
	 * @param array $actions
	 * @return array
	 */
	protected static function getLinkDefinition(array $actions)
	{
		return [
			'LINK' => [
				'ID' => 'LINK',
				'NAME' => Loc::getMessage('IBLOCK_LANDING_SOURCE_ELEMENT_ACTIONS'),
				'TYPE' => Node\Type::LINK,
				'ACTIONS' => $actions,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT
			]
		];
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	protected static function getOrderFields(array $fields)
	{
		$result = [];

		foreach ($fields as $row)
		{
			if (($row['ALLOWED'] & self::FIELD_ALLOWED_ORDER) == 0)
				continue;
			$result[] = [
				'ID' => $row['ID'],
				'NAME' => $row['NAME']
			];
		}
		unset($row);

		return $result;
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	protected static function getShowedFields(array $fields)
	{
		$result = [];

		foreach ($fields as $row)
		{
			if (($row['ALLOWED'] & self::FIELD_ALLOWED_SELECT) == 0)
				continue;
			$item = $row;
			unset($item['ALLOWED']);
			$result[] = $item;
		}
		unset($item, $row);

		return $result;
	}

	/**
	 * @param array $property
	 * @return bool
	 */
	protected static function checkImageProperty(array $property)
	{
		if (empty($property['FILE_TYPE']))
			return false;
		$property['FILE_TYPE'] = mb_strtolower(str_replace(' ', '', trim($property['FILE_TYPE'])));
		if (empty($property['FILE_TYPE']))
			return false;
		$rawFileTypes = explode(',', $property['FILE_TYPE']);
		if (empty($rawFileTypes))
			return false;
		$rawFileTypes = array_fill_keys($rawFileTypes, true);
		if (
			!isset($rawFileTypes['jpg'])
			&& !isset($rawFileTypes['gif'])
			&& !isset($rawFileTypes['png'])
			&& !isset($rawFileTypes['jpeg'])
		)
			return false;
		return true;
	}
}