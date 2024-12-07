<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Data;

use Bitrix\Sale\Location;
use Bitrix\Sale\Location\Admin\LocationHelper;

Loc::loadMessages(__FILE__);

class CBitrixLocationSelectorSearchComponent extends CBitrixComponent
{
	const START_SEARCH_LEN = 2;
	const COMPONENT_CACHE_DIR = '/sale/location/links';

	/**
	 * Fatal error list. Any fatal error makes useless further execution of a component code.
	 * In most cases, there will be only one error in a list according to the scheme "one shot - one dead body"
	 *
	 * @var array Array of fatal errors.
	 */

	protected $errors = array();

	/**
	 * Contains some valuable info from $_REQUEST
	 *
	 * @var object request info
	 */
	protected $request = array();

	/**
	 * Gathered options that are required
	 *
	 * @var string[] options
	 */
	protected $options = array();

	protected $dbResult = array();

	protected $filterBySite = false;

	protected $currentCache = false;

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @return bool
	 */
	protected function checkRequiredModules()
	{
		if (!static::includeRequiredModules())
		{
			$this->errors['FATAL'][] = Loc::getMessage("SALE_SLS_SALE_MODULE_NOT_INSTALL");
		}

		return true;
	}

	protected static function includeRequiredModules(): bool
	{
		return Loader::includeModule('sale');
	}

	/**
	 * Function checks if user have basic permissions to launch the component
	 * @return void
	 */
	protected function checkPermissions()
	{
		$result = true;

		if(!LocationHelper::checkLocationEnabled())
		{
			$this->errors['FATAL'][] = 'Locations were disabled or data has not been converted';
			$result = false;
		}

		return $result;
	}

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $arParams List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($arParams)
	{
		if (!is_array($arParams))
		{
			$arParams = [];
		}

		$arParams['ID'] = (int)($arParams['ID'] ?? 0);

		self::tryParseString($arParams['CODE']);
		self::tryParseString($arParams['INPUT_NAME'], 'LOCATION');
		self::tryParseWhiteList($arParams['PROVIDE_LINK_BY'], array('id', 'code'));
		$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 0);

		// filter
		$arParams['EXCLUDE_SUBTREE'] = (int)($arParams['EXCLUDE_SUBTREE'] ?? 0); // hidden parameter (from child components)
		$arParams['SEARCH_BY_PRIMARY'] = ($arParams['SEARCH_BY_PRIMARY'] ?? 'N') === 'Y'; // hidden parameter

		// which site it is
		$arParams = $this->prepareSiteFilterParams($arParams);

		// the code below should not be here, is should belong to a template
		self::tryParseString($arParams['RANDOM_TAG']);
		self::tryParseString($arParams['JS_CONTROL_GLOBAL_ID']);
		self::tryParseString($arParams['JS_CONTROL_DEFERRED_INIT']);
		self::tryParseStringStrict($arParams['JS_CALLBACK']);

		return $arParams;
	}

	protected function prepareSiteFilterParams(array $params): array
	{
		$request = Main\Context::getCurrent()->getRequest();
		$adminSection = $request->isAdminSection();

		if ($adminSection)
		{
			$params['FILTER_BY_SITE'] ??= 'N';
			$params['FILTER_SITE_ID'] ??= '';
			if ($params['FILTER_SITE_ID'] === 'current')
			{
				$params['FILTER_SITE_ID'] = '';
			}

			if ($params['FILTER_SITE_ID'] === '')
			{
				$params['FILTER_BY_SITE'] = 'N';
			}
		}

		$params['FILTER_BY_SITE'] = ($params['FILTER_BY_SITE'] ?? 'N') === 'Y';
		$params['FILTER_SITE_ID'] = (string)($params['FILTER_SITE_ID'] ?? '');
		if ($params['FILTER_BY_SITE'])
		{
			if (empty($params['FILTER_SITE_ID']) || $params['FILTER_SITE_ID'] === 'current')
			{
				$params['FILTER_SITE_ID'] = SITE_ID; // not for admin page
			}
			else
			{
				$params['FILTER_SITE_ID'] = mb_substr(
					self::tryParseStringStrict($params['FILTER_SITE_ID']),
					0,
					2
				);
			}
		}
		else
		{
			$params['FILTER_SITE_ID'] = '';
		}

		$params['SHOW_DEFAULT_LOCATIONS'] = ($params['SHOW_DEFAULT_LOCATIONS'] ?? 'N') === 'Y';

		return $params;
	}

	/**
	 * Function processes and corrects $_REQUEST. Everyting about $_REQUEST lies here.
	 * @return void
	 */
	protected function processRequest()
	{
	}

	protected function checkParameters()
	{
		if ($this->arParams['FILTER_BY_SITE'])
		{
			$g = Location\SiteLocationTable::checkLinkUsage($this->arParams['FILTER_SITE_ID'], Location\Connector::DB_LOCATION_FLAG);
			$l = Location\SiteLocationTable::checkLinkUsage($this->arParams['FILTER_SITE_ID'], Location\Connector::DB_GROUP_FLAG);

			// check also for link existence
			if($g || $l)
				$this->filterBySite = true;
		}

		return true;
	}

	/**
	 * @deprecated
	 *
	 * Function forces 'Y'/'N' value to boolean
	 * @param mixed $fld Field value
	 * @return string parsed value
	 */
	public static function tryParseBoolean(&$fld)
	{
		$fld = $fld == 'Y';
		return $fld;
	}

	/**
	 * @deprecated
	 *
	 * Function processes parameter value by white list, if gets null, passes the first value in white list
	 * @param mixed $fld Field value
	 * @param array $list Whitelist
	 * @return string parsed value
	 */
	public static function tryParseWhiteList(&$fld, $list = array())
	{
		if(!in_array($fld, $list))
			$fld = current($list);

		return $fld;
	}

	/**
	 * @deprecated
	 *
	 * Function reduces input value to integer type, and, if gets null, passes the default value
	 * @param mixed $fld Field value
	 * @param int $default Default value
	 * @param int $allowZero Allows zero-value of the parameter
	 * @return int Parsed value
	 */
	public static function tryParseInt(&$fld, $default = false, $allowZero = false)
	{
		$fld = intval($fld);
		if(!$allowZero && !$fld && $default !== false)
			$fld = $default;

		return $fld;
	}

	/**
	 * @deprecated
	 *
	 * Function processes string value and, if gets null, passes the default value to it
	 * @param mixed $fld Field value
	 * @param string $default Default value
	 * @return string parsed value
	 */
	public static function tryParseString(&$fld, $default = false)
	{
		$fld = trim((string)$fld);
		if(!mb_strlen($fld) && $default !== false)
			$fld = $default;

		$fld = htmlspecialcharsbx($fld);

		return $fld;
	}

	/**
	 * @deprecated
	 *
	 * Function processes string value and, if gets null, passes the default value to it
	 * @param mixed $fld Field value
	 * @param string $default Default value
	 * @return string parsed value
	 */
	public static function tryParseStringStrict(&$fld, $default = false)
	{
		$fld = trim((string)$fld);
		if(!mb_strlen($fld) && $default !== false)
			$fld = $default;

		$fld = preg_replace('#[^a-z0-9_-]#i', '', $fld);

		return $fld;
	}

	/**
	 * Fetches all required data from database. Everyting that connected with data fetch lies here.
	 * @return void
	 */
	protected function obtainData()
	{
		$this->obtainNonCachedData();

		// obtain cached data
		if ($this->startCache(static::getCacheDependences()))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache(static::COMPONENT_CACHE_DIR);

			try
			{
				$cachedData = array();
				$this->obtainCachedData($cachedData);
			}
			catch (Exception $e)
			{
				$CACHE_MANAGER->AbortTagCache();
				$this->abortCache();
				throw $e;
			}

			$CACHE_MANAGER->RegisterTag(LocationHelper::LOCATION_LINK_DATA_CACHE_TAG);
			$CACHE_MANAGER->EndTagCache();

			$this->endCache($cachedData);
		}
		else
			$cachedData = $this->getCacheData();

		$this->dbResult = array_merge($this->dbResult, $cachedData);

		$this->obtainCacheDependentData();
	}

	// at this point we can make descision to interrupt component execution, based on some conditions
	protected function obtainNonCachedData()
	{
		// fetch selected location data
		$this->obtainDataLocation();
	}

	// here we pick up cached data
	protected function obtainCachedData(&$cachedData)
	{
		$this->obtainDataLocationTypes($cachedData);
		$this->obtainDataConnectLocations($cachedData);
		$this->obtainDataDefaultLocations($cachedData);
	}

	// here we pick up data that depends on what cached data we currently have
	protected function obtainCacheDependentData()
	{
		$this->obtainDataAdditional();
	}

	protected function obtainDataLocation()
	{
		$parameters = $this->getLocationListParameters();

		$this->dbResult['PATH'] = array();
		$this->dbResult['LOCATION'] = array();
		$this->dbResult['PRECACHED_POOL'] = array();

		$toBeFound = false;
		$res = false;
		try
		{
			if($this->arParams['ID'])
			{
				$toBeFound = true;
				$res = Location\LocationTable::getPathToNode($this->arParams['ID'], $parameters);
			}
			elseif(mb_strlen($this->arParams['CODE']))
			{
				$toBeFound = true;
				$res = Location\LocationTable::getPathToNodeByCode($this->arParams['CODE'], $parameters);
			}

			if($res)
			{
				$res->addReplacedAliases(array('LNAME' => 'NAME'));

				while($item = $res->Fetch())
				{
					$this->dbResult['PATH'][intval($item['ID'])] = $this->forceToType($item);
					$this->dbResult['PRECACHED_POOL'][$item['PARENT_ID']][$item['ID']] = $item;
				}

				end($this->dbResult['PATH']);
				$this->dbResult['LOCATION'] = current($this->dbResult['PATH']);
			}
		}
		catch(Main\SystemException $e)
		{
		}

		if(empty($this->dbResult['PATH']) && $toBeFound)
			$this->errors['NONFATAL'][] = Loc::getMessage('SALE_SLS_SELECTED_NODE_NOT_FOUND');
	}

	protected function getLocationListParameters()
	{
		return array(
			'select' => array_merge($this->getNodeSelectFields(), array(
				'LNAME' => 'NAME.NAME',
				'SHORT_NAME' => 'NAME.SHORT_NAME',
				'LEFT_MARGIN',
				'RIGHT_MARGIN',
			)),
			'filter' => array(
				'=NAME.LANGUAGE_ID' => LANGUAGE_ID
			)
		);
	}

	protected function obtainDataLocationTypes(&$cachedData)
	{
		$result = [];
		$res = Location\TypeTable::getList([
			'select' => [
				'ID',
				'CODE',
				'SORT',
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		]);
		while($item = $res->fetch())
		{
			$id = (int)$item['ID'];
			$result[$id] = [
				'CODE' => $item['CODE'],
			];
		}
		unset($item, $res);

		$cachedData['TYPES'] = $result;
	}

	protected function obtainDataConnectLocations(&$cachedData)
	{
		$points = array();
		if($this->filterBySite)
		{
			$res = Location\SiteLocationTable::getConnectedLocations($this->arParams['FILTER_SITE_ID'], array('select' => array(
					'ID' => 'ID',
					'LEFT_MARGIN' => 'LEFT_MARGIN',
					'RIGHT_MARGIN' => 'RIGHT_MARGIN',
					'DEPTH_LEVEL',
					'PARENT_ID'
				)
			), array('GET_LINKED_THROUGH_GROUPS' => true));

			while($item = $res->fetch())
				$points[intval($item['ID'])] = $item;
		}

		$cachedData['TEMP']['CONNECTORS'] = $points;
	}

	/**
	 * Read some data from database, using cache
	 * @return void
	 */
	protected function obtainDataDefaultLocations(&$cachedData)
	{
		if(!$this->arParams['SHOW_DEFAULT_LOCATIONS'])
		{
			return;
		}

		$res = Location\DefaultSiteTable::getList(array(
			'filter' => array(
				'SITE_ID' => $this->arParams['FILTER_SITE_ID'],
				'LOCATION.NAME.LANGUAGE_ID' => LANGUAGE_ID
			),
			'order' => array(
				'SORT' => 'asc'
			),
			'select' => array(
				'CODE' => 'LOCATION.CODE',
				'ID' => 'LOCATION.ID',
				'PARENT_ID' => 'LOCATION.PARENT_ID',
				'TYPE_ID' => 'LOCATION.TYPE_ID',
				'LATITUDE' => 'LOCATION.LATITUDE',
				'LONGITUDE' => 'LOCATION.LONGITUDE',

				'NAME' => 'LOCATION.NAME.NAME',
				'SHORT_NAME' => 'LOCATION.NAME.SHORT_NAME',

				'LEFT_MARGIN' => 'LOCATION.LEFT_MARGIN',
				'RIGHT_MARGIN' => 'LOCATION.RIGHT_MARGIN'
			)
		));
		$defaults = array();
		while($item = $res->Fetch())
		{
			$defaults[$item['ID']] = $item;
		}

		if($this->filterBySite && !empty($defaults))
		{
			// check default locations to be REALLY connected with a site

			$linkTypeMap = Location\SiteLocationTable::getLinkStatusForMultipleNodes($defaults, $this->arParams['FILTER_SITE_ID'], $cachedData['TEMP']['CONNECTORS']);

			foreach($defaults as $id => $default)
			{
				if(!in_array($linkTypeMap[$id], array(Location\Connector::LSTAT_IS_CONNECTOR, Location\Connector::LSTAT_BELOW_CONNECTOR)))
					unset($defaults[$id]);
			}
		}

		foreach($defaults as &$default)
		{
			unset($default['LEFT_MARGIN']);
			unset($default['RIGHT_MARGIN']);
		}

		$cachedData['DEFAULT_LOCATIONS'] = $defaults;
	}

	protected function obtainDataAdditional()
	{
		if ($this->filterBySite && !empty($this->dbResult['LOCATION']['ID']))
		{
			$linkTypeMap = Location\SiteLocationTable::getLinkStatusForMultipleNodes(
				[
					$this->dbResult['LOCATION'],
				],
				$this->arParams['FILTER_SITE_ID'],
				$this->dbResult['TEMP']['CONNECTORS']
			);

			if (!in_array(
				$linkTypeMap[$this->dbResult['LOCATION']['ID']],
				[
					Location\Connector::LSTAT_IS_CONNECTOR,
					Location\Connector::LSTAT_BELOW_CONNECTOR,
				]
			))
			{
				$this->dbResult['PATH'] = [];
				$this->dbResult['LOCATION'] = [];
				$this->errors['NONFATAL'][] = Loc::getMessage('SALE_SLS_SELECTED_NODE_NOT_FOUND');
			}
		}
	}

	protected function getCacheDependences()
	{
		return array(
					static::getClassName().
					self::getStrForVariable($this->arParams['FILTER_BY_SITE']), // $this->filterBySite ???
					self::getStrForVariable($this->arParams['FILTER_SITE_ID']),
					self::getStrForVariable($this->arParams['SHOW_DEFAULT_LOCATIONS']),
					LANGUAGE_ID
				);
	}

	protected static function getStrForVariable($val)
	{
		return ':'.($val ? strval($val) : '0').':';
	}

	protected static function getNodeSelectFields()
	{
		// todo: here we can have a comoponent paramter which allows to select what fields to choose to display

		return array(
			'ID',
			'CODE',
			'SORT',
			'PARENT_ID',
			'LONGITUDE',
			'LATITUDE',
			'TYPE_ID',
			//'NAME' => 'NAME.NAME'
		);
	}

	protected static function forceToType($node)
	{
		$node['ID'] = intval($node['ID']);
		$node['PARENT_ID'] = intval($node['PARENT_ID']);

		if(isset($node['SORT']))
			$node['SORT'] = intval($node['SORT']);

		if(isset($node['LONGITUDE']))
			$node['LONGITUDE'] = floatval($node['LONGITUDE']);

		if(isset($node['LATITUDE']))
			$node['LATITUDE'] = floatval($node['LATITUDE']);

		if(isset($node['CHILD_CNT']))
			$node['CHILD_CNT'] = intval($node['CHILD_CNT']);

		return $node;
	}

	/**
	 * Move data read from database to a specially formatted $arResult
	 * @return void
	 */
	protected function formatResult()
	{
		unset($this->dbResult['TEMP']);
		$this->arResult =& $this->dbResult;
		$this->arResult['ERRORS'] =& $this->errors;

		if(!empty($this->arResult['LOCATION']) && is_array($this->arResult['LOCATION']))
		{
			if($this->arParams['PROVIDE_LINK_BY'] == 'code')
			{
				$this->arResult['VALUE'] = $this->arResult['LOCATION']['CODE'];
			}
			else
			{
				$this->arResult['VALUE'] = $this->arResult['LOCATION']['ID'];
			}
		}
		else
			$this->arResult['VALUE'] = '';
	}

	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		if($this->checkRequiredModules() && $this->checkPermissions() && $this->checkParameters())
		{
			$this->processRequest();
			$this->obtainData();
		}

		$this->formatResult();

		$this->includeComponentTemplate();
	}

	//////////////////////////////////////////
	// static functions for external usage

	protected static $allowedLocationFields = array(

		// entity
		'ID' => 'i',
		'CODE' => 's',
		'TYPE_ID' => 'i',
		'SORT' => 'i',
		'LEFT_MARGIN' => 'i',
		'RIGHT_MARGIN' => 'i',
		'PARENT_ID' => 'i',
		'CNT' => 'i',
		'CHILD_CNT' => 'i',
		'IS_PARENT' => 'i',
		'NAME.NAME' => 's',
		'NAME.LANGUAGE_ID' => 's',
		'GROUPLOCATION.LOCATION_GROUP_ID' => 'i',
		'LONGITUDE' => 's',
		'LATITUDE' => 's',

		// special (filter only)
		'PHRASE' => 's',
		'SITE_ID' => 's'
	);

	protected static $allowedAdditionals = array(
		'PATH' => true
	);

	protected static function processSearchRequestV2CheckQuery($parameters)
	{
		// strict check of what came in request

		// only select, filter, PAGE and PAGE_SIZE allowed in $parameters
		$safe = array(
			'select' => isset($parameters['select']) ? $parameters['select'] : array('ID')
		);
		if(isset($parameters['filter']))
			$safe['filter'] = $parameters['filter'];
		if(isset($parameters['additionals']))
			$safe['additionals'] = $parameters['additionals'];
		if(isset($parameters['PAGE']))
			$safe['PAGE'] = intval($parameters['PAGE']);
		if(isset($parameters['PAGE_SIZE']))
			$safe['PAGE_SIZE'] = intval($parameters['PAGE_SIZE']);
		if(isset($parameters['order']))
			$safe['order'] = $parameters['order'];

		$parameters = $safe;

		if(!isset($parameters['filter']))
		{
			$parameters['filter'] = [];
		}
		if(!isset($parameters['additionals']))
		{
			$parameters['additionals'] = [];
		}

		// check select
		if(!is_array($parameters['select']))
			throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

		foreach($parameters['select'] as $alias => $field)
		{
			if(!isset(static::$allowedLocationFields[$field]))
				throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

			// only simple aliasing allowed: numbers or words
			if(((string) $alias !== (string) intval($alias)) && !preg_match('#^[a-zA-Z0-9_]+$#', $alias))
				throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));
		}

		// check filter
		if(isset($parameters['filter']) && !is_array($parameters['filter']))
			throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

		//if(!isset($parameters['filter']['=PARENT_ID']) && !isset($parameters['filter']['=PHRASE']))
		//	throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

		// phrase should be string and longer than static::START_SEARCH_LEN
		if(isset($parameters['filter']['=PHRASE']) && (!is_string($parameters['filter']['=PHRASE']) || mb_strlen($parameters['filter']['=PHRASE']) < static::START_SEARCH_LEN))
			throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));


		foreach($parameters['filter'] as $field => $value)
		{
			// only '=' modifier allowed
			$field = preg_replace('#^=#', '', $field);

			if(!isset(static::$allowedLocationFields[$field]))
				throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

			if(!is_array($value))
				$value = array($value);

			// check value
			$type = static::$allowedLocationFields[$field];

			foreach($value as $item)
			{
				if(
					($type == 'i' && !((string) $item === (string) intval($item)))
					||
					($type == 's' && !is_string($item))
				)
					throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));
			}
		}

		// check limitation, if searching by PHRASE
		if((isset($parameters['filter']['PHRASE']) || isset($parameters['filter']['=PHRASE'])) && (!isset($parameters['PAGE_SIZE']) || !isset($parameters['PAGE'])))
			throw new Main\ArgumentException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

		return $parameters;
	}

	protected static function processSearchRequestV2AfterSearchFormatResult(&$data, $parameters)
	{
		if(is_array($data['ITEMS']))
		{
			$selectFlip = array_flip($parameters['select']);

			// drop unwanted data in the result
			foreach($data['ITEMS'] as &$item)
			{
				foreach($item as $fld => $value)
				{
					if(isset(static::$allowedAdditionals[$fld])) // it`s a special field
						continue;

					if(isset($parameters['select'][$fld])) // it`s an alias
						continue;

					if(isset($selectFlip[$fld]) && ((string) $selectFlip[$fld] === (string) intval($selectFlip[$fld]))) // it`s an un-aliased field
						continue;

					unset($item[$fld]);
				}
			}
		}
	}

	protected static function processSearchRequestV2ModifyParameters($parameters)
	{
		// drop data that should not go to find()
		$clean = $parameters;

		// don't get NAME if there is PATH, kz we`ll get NAMES after all
		if (
			isset($clean['select'])
			&& is_array($clean['select'])
			&& isset($parameters['additionals'])
			&& is_array($parameters['additionals'])
			&& in_array('PATH', $parameters['additionals'])
		)
		{
			//unset($clean['filter']['=NAME.LANGUAGE_ID']);
			//unset($clean['filter']['NAME.LANGUAGE_ID']);

			foreach ($clean['select'] as $alias => $field)
			{
				if ($field === 'NAME.NAME')
				{
					unset($clean['select'][$alias]);
				}
			}
		}

		if (
			isset($clean['filter']['=GROUPLOCATION.LOCATION_GROUP_ID'])
			&& (int)$clean['filter']['=GROUPLOCATION.LOCATION_GROUP_ID'] > 0
		)
		{
			$clean['runtime'] ??= [];
			$clean['runtime']['GROUPLOCATION'] = [
				'data_type' => 'Bitrix\Sale\Location\GroupLocationTable',
				'reference' => [
					'=this.ID' => 'ref.LOCATION_ID',
				],
			];
		}

		unset($clean['additionals']);

		$clean['select'] ??= [];
		$clean['select'][] = 'LEFT_MARGIN';
		$clean['select'][] = 'RIGHT_MARGIN';
		$clean['select'][] = 'ID';

		return $clean;
	}

	protected static function processSearchRequestV2GetFinderBehaviour()
	{
		return [];
	}

	public static function processSearchRequestV2($parameters)
	{
		static::includeRequiredModules();
		$parameters = static::processSearchRequestV2CheckQuery($parameters);

		// map page & page_size => limit & offset
		$pageSize = (int)($parameters['PAGE_SIZE'] ?? null);
		if ($pageSize > 0)
		{
			$page = (int)($parameters['PAGE'] ?? null);

			$parameters['limit'] = $pageSize;
			$parameters['offset'] = ($page ? $page * $pageSize : 0);
		}

		$parameters['select'][] = 'CHILD_CNT';

		unset($parameters['PAGE_SIZE']);
		unset($parameters['PAGE']);

		// do request
		$data = array(
			'ITEMS' => array(),
			'ETC' => array()
		);

		$result = Location\Search\Finder::find(
			static::processSearchRequestV2ModifyParameters($parameters),
			static::processSearchRequestV2GetFinderBehaviour()
		);

		while($item = $result->fetch())
		{
			// hack to repair ORM
			if(!isset($item['ID']))
				$item['ID'] = $item['VALUE'];

			if ((int)($item['CHILD_CNT'] ?? 0) > 0)
			{
				$item['IS_PARENT'] = true;
			}

			unset($item['CHILD_CNT']);
			$data['ITEMS'][] = $item;
		}

		static::processSearchRequestV2GetAdditional($data, $parameters);
		$parameters['select'][] = 'IS_PARENT';
		static::processSearchRequestV2AfterSearchFormatResult($data, $parameters);

		return $data;
	}

	protected static function processSearchRequestV2GetNameAlias($parameters)
	{
		if(is_array($parameters['select']))
		{
			foreach($parameters['select'] as $alias => $field)
			{
				if($field == 'NAME.NAME')
				{
					if((string) $alias === (string) intval($alias)) // numeric alias
						return 'NAME';
					else
						return $alias;
				}
			}
		}

		return false;
	}

	protected static function getPathNodesSelect()
	{
		return array('ID', 'DISPLAY' => 'NAME.NAME');
	}

	protected static function getPathToNodesV2($list, $parameters = array())
	{
		$select = static::getPathNodesSelect();
		if(is_array($parameters['select']))
		{
			$select = $parameters['select'];

			// orm wont return a correct result in case of array("ID", "VALUE" => "ID") select passed, so orm fix required
			unset($select['VALUE']);
			$select[] = 'ID';
		}

		$result = array(
			'ITEM_NAMES' => array(),
			'PATH_ITEMS' => array()
		);

		try
		{
			$res = Location\LocationTable::getPathToMultipleNodes(
				$list,
				array(
					'select' => $select,
					'filter' => array('=NAME.LANGUAGE_ID' => (string) $parameters['filter']['=NAME.LANGUAGE_ID'] != '' ? $parameters['filter']['=NAME.LANGUAGE_ID'] : LANGUAGE_ID)
				)
			);
		}
		catch(\Bitrix\Main\ArgumentException $e) // in case of database damage this will be thrown
		{
			LocationHelper::informAdminLocationDatabaseFailure();
			return $result;
		}

		$pathItems = array();
		while($path = $res->fetch())
		{
			// format path as required for JSON responce
			$chain = array();
			$itemId = false;

			$i = -1;
			foreach($path['PATH'] as $id => $pItem)
			{
				$i++;

				if(!$i) // we dont need for an item itself in the path chain
				{
					$itemId = $id;
					$result['ITEM_NAMES'][$id] = $pItem['DISPLAY'];
					continue;
				}

				// orm fix, return everything back
				if(isset($pItem['ID']))
					$pItem['VALUE'] = intval($pItem['ID']);

				$chain[] = intval($pItem['VALUE']);
				$id = $pItem['VALUE'];
				unset($pItem['ID']);

				$pathItems[$id] = $pItem;
			}

			$result['PATH'][$itemId] = $chain;
		}

		$result['PATH_ITEMS'] = $pathItems;

		return $result;
	}

	protected static function processSearchRequestV2GetAdditionalPathNodes(&$data, $parameters)
	{
		$pathes = static::getPathToNodesV2($data['ITEMS'], $parameters);
		$nameAlias = static::processSearchRequestV2GetNameAlias($parameters);

		foreach($data['ITEMS'] as $i => &$item)
		{
			if(empty($pathes['ITEM_NAMES']) || empty($pathes['ITEM_NAMES'][$item['ID']]))
			{
				// we got empty ITEM_NAMES. This is not a normal case, item without a name is not sutable for displaying. Skip.
				unset($data['ITEMS'][$i]);
				continue;
			}

			$item['PATH'] = $pathes['PATH'][$item['ID']];

			if(isset($pathes['ITEM_NAMES'][$item['ID']]) && $nameAlias !== false)
			{
				$item[$nameAlias] = $pathes['ITEM_NAMES'][$item['ID']];
			}
		}

		$data['ETC']['PATH_ITEMS'] = $pathes['PATH_ITEMS'];
	}

	protected static function processSearchRequestV2GetAdditional(&$data, $parameters)
	{
		if(!empty($data['ITEMS']))
		{
			if(is_array($parameters['additionals']) && in_array('PATH', $parameters['additionals']))
			{
				// show path to each found node
				static::processSearchRequestV2GetAdditionalPathNodes($data, $parameters);
			}
		}

		return $data;
	}

	protected static function getClassName()
	{
		return __CLASS__;
	}

	////////////////////////
	// Cache functions
	////////////////////////
	/**
	 * Function checks if cacheing is enabled in component parameters
	 * @return boolean
	 */
	final protected function getCacheNeed()
	{
		return	intval($this->arParams['CACHE_TIME']) > 0 &&
				$this->arParams['CACHE_TYPE'] != 'N' &&
				(!(defined("ADMIN_SECTION") && ADMIN_SECTION == true)) &&
				Config\Option::get("main", "component_cache_on", "Y") == "Y";
	}

	/**
	 * Function perform start of cache process, if needed
	 * @param mixed[]|string $cacheId An optional addition for cache key
	 * @return boolean True, if cache content needs to be generated, false if cache is valid and can be read
	 */
	final protected function startCache($cacheId = array())
	{
		if(!$this->getCacheNeed())
			return true;

		$this->currentCache = Data\Cache::createInstance();

		return $this->currentCache->startDataCache(intval($this->arParams['CACHE_TIME']), $this->getCacheKey($cacheId), static::COMPONENT_CACHE_DIR);
	}

	/**
	 * Function perform start of cache process, if needed
	 * @throws Main\SystemException
	 * @param mixed[] $data Data to be stored in the cache
	 * @return void
	 */
	final protected function endCache($data = false)
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->endDataCache($data);
		$this->currentCache = null;
	}

	/**
	 * Function discard cache generation
	 * @throws Main\SystemException
	 * @return void
	 */
	final protected function abortCache()
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->abortDataCache();
		$this->currentCache = null;
	}

	/**
	 * Function return data stored in cache
	 * @throws Main\SystemException
	 * @return void|mixed[] Data from cache
	 */
	final protected function getCacheData()
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		return $this->currentCache->getVars();
	}

	/**
	 * Function leaves the ability to modify cache key in future.
	 * @return string Cache key to be used in CPHPCache()
	 */
	final protected function getCacheKey($cacheId = array())
	{
		if(!is_array($cacheId))
			$cacheId = array((string) $cacheId);

		$cacheId['SITE_ID'] = SITE_ID;
		$cacheId['LANGUAGE_ID'] = LANGUAGE_ID;

		// if there are two or more caches with the same id, but with different cache_time, make them separate
		$cacheId['CACHE_TIME'] = (int)$this->arParams['CACHE_TIME'];

		if(defined("SITE_TEMPLATE_ID"))
			$cacheId['SITE_TEMPLATE_ID'] = SITE_TEMPLATE_ID;

		return implode('|', $cacheId);
	}

	////////////////////////////////////////////////////////
	// DEPRECATED methods to support DEPRECATED query method

	//////////////////////////////////////////
	// static functions for external usage

	/**
	 * @deprecated
	 */
	protected static function getPathToNodes($list)
	{
		$res = Location\LocationTable::getPathToMultipleNodes(
			$list,
			array(
				'select' => array('ID', 'LNAME' => 'NAME.NAME'),
				'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)
			)
		);

		$pathNames = array();
		$result = array();

		while($path = $res->fetch())
		{
			// format path as required for JSON responce
			$chain = array();
			$itemId = false;

			$i = -1;
			foreach($path['PATH'] as $id => $pItem)
			{
				$i++;

				if(!$i) // we dont need for an item itself in the path chain
				{
					$itemId = $id;
					continue;
				}

				$pathNames[$pItem['ID']] = $pItem['LNAME'];
				$chain[] = intval($pItem['ID']);
			}

			$result['PATH'][$itemId] = $chain;
		}

		$result['PATH_NAMES'] = $pathNames;

		return $result;
	}

	/**
	 * @deprecated
	 */
	public static function processSearchRequest()
	{
		static::includeRequiredModules();

		$parameters = static::processSearchGetParameters();

		$result = static::processSearchGetList($parameters);
		$result = static::processSearchGetAdditional($result);

		// drop unwanted data
		foreach($result['ITEMS'] as &$item)
		{
			unset($item['LEFT_MARGIN']);
			unset($item['RIGHT_MARGIN']);
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetParameters()
	{
		$parameters = array(
			'select' => static::processSearchGetSelect(),
			'filter' => static::processSearchGetFilter()
		);

		if($pageSize = intval($_REQUEST['PAGE_SIZE']))
		{
			$page = intval($_REQUEST['PAGE']);

			$parameters['limit'] = $pageSize;
			$parameters['offset'] = ($page ? $page * $pageSize : 0);
		}

		return $parameters;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchRequestGetLang()
	{
		return $_REQUEST['BEHAVIOUR']['LANGUAGE_ID'] <> ''? $_REQUEST['BEHAVIOUR']['LANGUAGE_ID'] : LANGUAGE_ID;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetFilter()
	{
		if(!isset($_REQUEST['FILTER']['PARENT_ID']) && !isset($_REQUEST['FILTER']['QUERY'])) // ddos protection
			throw new Main\SystemException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

		$rFilter = $_REQUEST['FILTER'];
		$langId = static::processSearchRequestGetLang();

		// filter

		$filter = array(
			'LANGUAGE_ID' => $langId
		);

		if($rFilter['SITE_ID'] <> '')
		{
			$filter['SITE_ID'] = $rFilter['SITE_ID'];
		}

		if($_REQUEST['BEHAVIOUR']['EXPECT_EXACT'])
		{
			###################################
			# exact search
			###################################

			// EXPECT_EXACT assumes presence of QUERY key in query. in this case QUERY is being treated as an exact value for ID
			if($rFilter['QUERY'] == '')
				throw new Main\SystemException(Loc::getMessage('SALE_SLS_BAD_QUERY'));

			$filter['ID'] = intval($rFilter['QUERY']);
		}
		else
		{
			###################################
			# Non-exact search, there could be a set of matched elements
			###################################

			if($rFilter['QUERY'] <> '')
			{
				if(mb_strlen($rFilter['QUERY']) >= static::START_SEARCH_LEN)
				{
					$filter['NAME'] = $rFilter['QUERY'];
				}

				if($_REQUEST['BEHAVIOUR']['SEARCH_BY_PRIMARY'])
				{
					$filter['PRIMARY'] = $rFilter['QUERY'];
				}
			}

			if(isset($rFilter['PARENT_ID']) && intval($rFilter['PARENT_ID']) >= 0)
				$filter['PARENT_ID'] = intval($rFilter['PARENT_ID']);

			if($typeId = intval($rFilter['TYPE_ID']))
				$filter['TYPE_ID'] = $typeId;
		}

		return $filter;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetSelect()
	{
		$select = array();
		if($_REQUEST['SHOW']['CHILD_EXISTENCE'])
			$select[] = 'CHILD_CNT';

		if($_REQUEST['SHOW']['TYPE_ID'])
			$select[] = 'TYPE_ID';

		return $select;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetList($parameters)
	{
		$res = Location\LocationTable::getListFast($parameters);

		$result = array();
		while($item = $res->fetch())
			$result[] = $item;

		return $result;
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetAdditionalPathNodes(&$data)
	{
		if($_REQUEST['SHOW']['PATH'] && is_array($data['ITEMS']))
		{
			$pathes = static::getPathToNodes($data['ITEMS']);

			foreach($data['ITEMS'] as &$item)
				$item['PATH'] = $pathes['PATH'][$item['ID']];

			$data['ETC']['PATH_NAMES'] = $pathes['PATH_NAMES'];
		}
	}

	/**
	 * @deprecated
	 */
	protected static function processSearchGetAdditional($result)
	{
		$data = array(
			'ITEMS' => $result,
			'ETC' => array()
		);

		if(!empty($result))
		{
			###################################
			# Additional extras
			###################################

			// show path to each found node
			static::processSearchGetAdditionalPathNodes($data);

			// show item count based on filter, but only when the first page called (it may be useful for calculating client-side pager)
			if(intval($_REQUEST['PAGE_SIZE']) && !intval($_REQUEST['PAGE']) && $_REQUEST['SHOW']['ITEM_COUNT'])
			{
				$filter = static::processSearchGetFilter();
				// spike! spike! spike!
				// we hope we can make filter compatible with ORM by replacing getListFast with smth cleverer, but for now we got only option:
				unset($filter['LANGUAGE_ID']);

				$res = Location\LocationTable::getList(array(
					'runtime' => array(
						'CNT' => array(
							'data_type' => 'integer',
							'expression' => array('COUNT(*)')
						)
					),
					'select' => array('CNT'),
					'filter' => $filter
				))->fetch();

				$data['ETC']['TOTAL_ITEM_COUNT'] = intval($res['CNT']);
			}
		}

		return $data;
	}

	/**
	 * @deprecated
	 */
	protected static function isNonemptyArray($arr)
	{
		return is_array($arr) && !empty($arr);
	}
}
