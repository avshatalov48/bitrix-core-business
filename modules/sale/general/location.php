<?php

/**
 * The entire class was marked as deprecated.
 * It will be removed from future releases. Do not rely on this code.
 *
 * @access private
 * @deprecated
 */

use Bitrix\Main;
use Bitrix\Main\DB;
use Bitrix\Main\Config;
use Bitrix\Main\Entity;
use Bitrix\Sale\Location;
use Bitrix\Sale\Delivery;
use Bitrix\Main\Localization;

IncludeModuleLangFile(__FILE__);

class CAllSaleLocation
{
	const LOC2_M_OPT = 					'sale_locationpro_migrated';
	const LOC2_DEBUG_MODE_OPT = 		'location2_debug_mode';

	const SELF_ENTITY_NAME = 			'Bitrix\Sale\Location\Location';
	const NAME_ENTITY_NAME = 			'Bitrix\Sale\Location\Name\Location';
	const DEFAULT_SITE_ENTITY_NAME = 	'Bitrix\Sale\Location\DefaultSite';

	const ORIGIN_NAME_LANGUAGE_ID = 	'en';
	const ZIP_EXT_SERVICE_CODE =		'ZIP';

	const MODIFIER_SEARCH_R = 			'#^((!|\+)?(>=|>|<=|<|@|~|%|=)?)#';
	const KEY_PARSE_R = 				'#^(!|\+)?(>=|>|<=|<|@|~|%|=)?(.+)#';
	const LEADING_TILDA_SEARCH_R = 		'#^~#';

	/////////////////////////////////////////////
	// enable this when you want to turn on the new functionality

	public static function isLocationProEnabled()
	{
		return self::isLocationProMigrated();
	}

	public static function locationProEnable()
	{
		self::locationProSetMigrated();
	}

	public static function locationProDisable()
	{
		self::locationProSetRolledBack();
	}

	/////////////////////////////////////////////
	// enable this when you had done the migration

	public static function isLocationProMigrated()
	{
		return Config\Option::get('sale', self::LOC2_M_OPT, '') == 'Y';
	}

	public static function locationProSetMigrated()
	{
		Config\Option::set('sale', self::LOC2_M_OPT, 'Y', '');
	}

	public static function locationProSetRolledBack()
	{
		Config\Option::set('sale', self::LOC2_M_OPT, 'N', '');
	}

	// very temporal code
	public static function locationProCheckEnabled()
	{
		if(!self::isLocationProEnabled())
		{
			if($_REQUEST['l2switch'] == 'ON')
			{
				CSaleLocation::locationProEnable();
				return true;
			}
			
			?>
			<form action="" method="post">
				Location 2.0 were disabled.&nbsp;<button name="l2switch" value="ON">Enable</button>
			</form>
			<?
			return false;
		}
		return true;
	}

	// very temporal code
	public static function locationProControlForm()
	{
		?>

		<?if($_REQUEST['l2migrated']):?>
			<?if($_REQUEST['l2migrated'] == 'ON'):?>
				<?self::locationProSetMigrated();?>
			<?else:?>
				<?self::locationProSetRolledBack();?>
			<?endif?>
		<?endif?>

		<?if($_REQUEST['l2switch']):?>
			<?if($_REQUEST['l2switch'] == 'ON'):?>
				<?self::locationProEnable();?>
			<?else:?>
				<?self::locationProDisable();?>
			<?endif?>
		<?endif?>

		<?if($_REQUEST['l2debug']):?>
			<?if($_REQUEST['l2debug'] == 'ON'):?>
				<?self::locationProDebugEnable();?>
			<?else:?>
				<?self::locationProDebugDisable();?>
			<?endif?>
		<?endif?>

		<?$l2enabled = self::isLocationProEnabled();?>
		<?$l2migrated = self::isLocationProMigrated();?>
		<?$l2debug = self::isLocationProInDebug();?>

		<form action="" method="post">
			Migration: <br />
			<button name="l2migrated" value="<?=($l2migrated ? 'OFF' : 'ON')?>"><?=($l2migrated ? 'Go Down' : 'Go Up')?></button>
		</form>

		<form action="" method="post">
			Location 2.0: <br />
			<button name="l2switch" value="<?=($l2enabled ? 'OFF' : 'ON')?>"><?=($l2enabled ? 'Turn OFF' : 'Turn ON')?></button>
		</form>

		<form action="" method="post">
			Debug mode: <br />
			<button name="l2debug" value="<?=($l2debug ? 'OFF' : 'ON')?>"><?=($l2debug ? 'Turn OFF' : 'Turn ON')?></button>
		</form>

		<?
	}

	// for old admin pages the following function should be used
	// (for the new ones - direct call of sale.location.selector.*)
	public static function proxySaleAjaxLocationsComponent($parameters = array(), $additionalParams = array(), $template = '', $hideIcons = true, $wrapNewComponentWith = false)
	{
		global $APPLICATION;

		if(self::isLocationProEnabled())
		{
			if(!is_array($additionalParams))
				$additionalParams = array();

			$parametersProxed = array_merge(array(
				"ID" => $parameters["LOCATION_VALUE"],
				"CODE" => '',
				"INPUT_NAME" => $parameters["CITY_INPUT_NAME"],
				"CACHE_TYPE" => "A",
				"CACHE_TIME" => "36000000",
				"PROVIDE_LINK_BY" => "id",
				"SEARCH_BY_PRIMARY" => "N",
				"SHOW_DEFAULT_LOCATIONS" => "N",
				//"JS_CALLBACK" => "submitFormProxy",
				//"JS_CONTROL_DEFERRED_INIT" => "soa_deferred"
			), $additionalParams);

			if(mb_strlen($parameters['SITE_ID']) || !defined('ADMIN_SECTION') || ADMIN_SECTION != 'Y')
			{
				$parametersProxed["FILTER_BY_SITE"] = "Y";
				$parametersProxed["FILTER_SITE_ID"] = $parameters['SITE_ID'] <> ''? $parameters['SITE_ID'] : SITE_ID;
			}

			if($wrapNewComponentWith <> '')
			{
				print('<div class="'.$wrapNewComponentWith.'">');
			}

			if($template == '')
				$appearance = \Bitrix\Sale\Location\Admin\Helper::getWidgetAppearance();
			else
			{
				$appearance = 'steps';

				if($template == 'popup' || $template == 'search')
					$appearance = 'search';
			}

			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:sale.location.selector.".$appearance,
				"",
				$parametersProxed,
				null,
				array('HIDE_ICONS' => $hideIcons ? 'Y' : 'N')
			);

			if($wrapNewComponentWith)
				print('</div>');
		}
		else
		{
			$GLOBALS["APPLICATION"]->IncludeComponent(
				'bitrix:sale.ajax.locations',
				$template,
				$parameters,
				null,
				array('HIDE_ICONS' => $hideIcons ? 'Y' : 'N')
			);
		}
	}

	/////////////////////////////////////////////
	// enable this when you want to get debugging

	public static function isLocationProInDebug()
	{
		return Config\Option::get('sale', self::LOC2_DEBUG_MODE_OPT, '') == 'Y';
	}

	public static function locationProDebugEnable()
	{
		Config\Option::set('sale', self::LOC2_DEBUG_MODE_OPT, 'Y', '');
	}

	public static function locationProDebugDisable()
	{
		Config\Option::set('sale', self::LOC2_DEBUG_MODE_OPT, 'N', '');
	}

	/////////////////////////////////////////////
	/////////////////////////////////////////////
	/////////////////////////////////////////////

	public static function getLocationIDbyCODE($code)
	{
		if(CSaleLocation::isLocationProMigrated() && mb_strlen($code))
		{
			$item = Location\LocationTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'=CODE' => $code
				)
			))->fetch();

			if(empty($item))
				return 0;

			return $item['ID'];
		}

		return $code;
	}

	public static function getLocationCODEbyID($id)
	{
		if(CSaleLocation::isLocationProMigrated() && intval($id))
		{
			// we must convert ID to CODE
			$item = Location\LocationTable::getList(array('filter' => array('=ID' => $id), 'select' => array('ID', 'CODE')))->fetch();

			if(empty($item))
				return '';

			return $item['CODE'];
		}

		return $id;
	}

	public static function checkIsCode($id)
	{
		return ((string) $id !== (string) intval($id));
	}

	public static function tryTranslateIDToCode($id)
	{
		if(!CSaleLocation::isLocationProMigrated())
			return $id;

		$id = (string) $id;

		if($id != '' && $id === (string) intval($id))
		{
			// ID came, need to translate to CODE and store
			$location = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=ID' => $id), 'select' => array('ID', 'CODE')))->fetch();
			if((string) $location['CODE'] != '')
				return $location['CODE'];
		}

		return $id;
	}

	public static function checkLocationIsAboveCity($locationId)
	{
		if($locationId <> '')
		{
			$tail = CSaleLocation::getLocationCityTail();
			if(!empty($tail))
			{
				$location = Location\LocationTable::getList(array('select' => array('TYPE_ID'), 'filter' => array(
					'LOGIC' => 'OR',
					array('=ID' => intval($locationId)),
					array('=CODE' => $locationId)
				)))->fetch();

				if(!isset($tail[$location['TYPE_ID']])) // is not a city and not a descendant of it
				{
					return true;
				}
			}
		}

		return false;
	}

	public static function getLocationCityTail()
	{
		static $tail;

		if($tail == null)
		{
			$tail = array();

			// spike to emulate previous logic of "city" field
			$res = Location\TypeTable::getList(array('select' => array('CODE', 'ID'), 'order' => array('SORT' => 'asc')));
			$types = array();
			$start = false;
			while($item = $res->fetch())
			{
				if($item['CODE'] == 'CITY')
					$start = true;

				if($start)
					$tail[$item['ID']] = true;
			}
		}

		return $tail;
	}

	private static function getLanguages()
	{
		$langs = array();
		$res = Localization\LanguageTable::getList();
		while($item = $res->fetch())
			$langs[] = $item['LID'];

		return $langs;
	}

	public static function getSites()
	{
		$sites = array();
		$res = Main\SiteTable::getList();
		while($item = $res->fetch())
			$sites[] = $item['LID'];

		return $sites;
	}

	public static function getTypes()
	{
		static $types;

		if($types == null)
		{
			$types = array();
			$res = Location\TypeTable::getList();
			while($item = $res->fetch())
				$types[$item['CODE']] = $item['ID'];
		}

		return $types;
	}

	private static function getZipId()
	{
		$res = Location\ExternalServiceTable::getList(array('filter' => array('=CODE' => self::ZIP_EXT_SERVICE_CODE), 'limit' => 1))->fetch();

		if($res)
			return $res['ID'];

		return false;
	}

	private static function refineFieldsForSaveCRC($id, $arFields)
	{
		if(!is_array($arFields[self::ORIGIN_NAME_LANGUAGE_ID]))
		{
			// make default names as en-names
			$arFields[self::ORIGIN_NAME_LANGUAGE_ID] = array(
				'LID' => self::ORIGIN_NAME_LANGUAGE_ID,
				'NAME' => $arFields['NAME'],
				'SHORT_NAME' => $arFields['SHORT_NAME']
			);
		}

		$names = array();
		foreach(self::getLanguages() as $lid)
		{
			if(is_array($arFields[$lid]))
			{
				unset($arFields[$lid]['LID']);
				$names[$lid] = $arFields[$lid];
			}
		}

		return array(
			'ID' => $id, // array should not be empty
			'NAME' => $names
		);
	}

	private static function getLocationIdByCountryId($legacyId)
	{
		$res = $item = Location\LocationTable::getList(array(
			'filter' => array(
				'=TYPE.CODE' => 'COUNTRY',
				'=COUNTRY_ID' => intval($legacyId),
				'=REGION_ID' => false,
				'=CITY_ID' => false
			),
			'select' => array(
				'ID'
			)
		))->fetch();

		return $res['ID'];
	}

	private static function getLocationIdByCityId($legacyId)
	{
		$res = Location\LocationTable::getList(array(
			'filter' => array(
				'=TYPE.CODE' => 'CITY',
				'=CITY_ID' => intval($legacyId),
			),
			'select' => array(
				'ID'
			)
		))->fetch();

		return $res['ID'];
	}

	private static function getLocationIdByRegionId($legacyId)
	{
		$res = Location\LocationTable::getList(array(
			'filter' => array(
				'=TYPE.CODE' => 'REGION',
				'=REGION_ID' => intval($legacyId),
				'=CITY_ID' => false
			),
			'select' => array(
				'ID'
			)
		))->fetch();

		return $res['ID'];
	}

	private static function checkLangPresenceInSelect($type, $fields)
	{
		return in_array($type.'_NAME_', $fields, true) || in_array($type.'_SHORT_NAME', $fields, true) || in_array($type.'_NAME', $fields, true) || in_array($type.'_NAME_LANG', $fields, true) || in_array($type.'_LID', $fields, true);
	}

	private static function getTypeValueToStore($type, $arFields)
	{
		if(isset($arFields[$type]) && is_array($arFields[$type]) && !empty($arFields[$type]))
			return $arFields[$type];

		if(isset($arFields[$type.'_ID']) && intval($arFields[$type.'_ID']))
			return intval($arFields[$type.'_ID']);

		return false;
	}

	protected static function checkIsRealInt($val)
	{
		return ((string) intval($val) === (string) $val);
	}

	protected static function GetLocationTypeList($typeCode = '', $arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		global $DB;
		$arSqlSearch = Array();

		if(!in_array($typeCode, array('COUNTRY', 'REGION', 'CITY')))
		{
			$res = new CDBResult();
			$res->InitFromArray(array());
			return $res;
		}

		$types = self::getTypes();
		if(!isset($types[$typeCode]))
		{
			$res = new CDBResult();
			$res->InitFromArray(array());
			return $res;
		}

		$arSqlSearch[] = "L.TYPE_ID = '".intval($types[$typeCode])."'";

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$joinLCO = false;
		$joinLRE = false;

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val == '') continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
				case "ID":

					if(self::checkIsRealInt($val)) // real int
					{
						$field = 'ID';
						$val = intval($val);
					}
					else
					{
						$field = 'CODE';
						$val = $DB->ForSql($val);
					}

					$arSqlSearch[] = "L.".$field." ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "NAME":
					$arSqlSearch[] = "LLN.NAME ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "COUNTRY_ID":

					if(self::checkIsRealInt($val)) // real int
					{
						$val = intval($val);
						$arSqlSearch[] = "L.COUNTRY_ID ".($bInvert?"<>":"=")." '".$val."' ";
					}
					else
					{
						$val = $DB->ForSql($val);
						$joinLCO = true;
						$arSqlSearch[] = "LCO.CODE ".($bInvert?"<>":"=")." '".$val."' ";
					}

					break;

				case "REGION_ID":

					if(self::checkIsRealInt($val)) // real int
					{
						$val = intval($val);
						$arSqlSearch[] = "L.REGION_ID ".($bInvert?"<>":"=")." '".$val."' ";
					}
					else
					{
						$val = $DB->ForSql($val);
						$joinLRE = true;
						$arSqlSearch[] = "LRE.CODE ".($bInvert?"<>":"=")." '".$val."' ";
					}

					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		/*
		L - location table
		LN - language-independent
		LLN - language-dependent
		*/

		$strSql =
			"SELECT L.ID as ID, L.CODE as CODE, LN.NAME as NAME_ORIG, LLN.SHORT_NAME as SHORT_NAME, LLN.NAME as NAME, L.SORT as SORT, ".
			"	CASE WHEN LLN.LOCATION_ID IS NULL THEN LN.NAME ELSE LLN.NAME END as NAME_LANG ".
			"FROM b_sale_location L ".
			"	LEFT JOIN b_sale_loc_name LN ON (L.ID = LN.LOCATION_ID AND LN.LANGUAGE_ID = 'en') ".
			"	LEFT JOIN b_sale_loc_name LLN ON (L.ID = LLN.LOCATION_ID AND LLN.LANGUAGE_ID = '".$DB->ForSql($strLang, 2)."') ".

			($joinLCO ? 
				" INNER JOIN b_sale_location LCO ON (L.COUNTRY_ID = LCO.ID) " :
				"").

			($joinLRE ? 
				" INNER JOIN b_sale_location LRE ON (L.REGION_ID = LRE.ID) " :
				"").

			"	WHERE 1 = 1 ".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "SORT") $arSqlOrder[] = " L.SORT ".$order;
			elseif ($by == "ID") $arSqlOrder[] = " L.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " LN.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " LN.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " LLN.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	public static function checkLocationIdExists($id)
	{
		$res = Location\LocationTable::getById($id)->fetch();

		return intval($res['ID']);
	}

	public static function checkLocationCodeExists($code)
	{
		$res = Location\LocationTable::getByCode($code)->fetch();

		return intval($res['ID']);
	}

	public static function getFreeId($type)
	{
		$class = self::SELF_ENTITY_NAME.'Table';
		$item = $class::getList(array('select' => array($type.'_ID'), 'limit' => 1, 'order' => array($type.'_ID' => 'desc')))->fetch();

		$fromLocTable = intval($item[$type.'_ID']);
	
		$res = self::GetLocationTypeList($type, array('ID' => 'desc'))->fetch();
		$fromTypeTable = $res['ID'];

		return max($fromLocTable, $fromTypeTable) + 1;
	}

	public static function getTypeJOINCondition($ctx = 'this')
	{
		static $types;

		if($types == null)
		{
			$types = self::getTypes();
		}

		return array(
			'LOGIC' => 'OR',
			array('='.$ctx.'.TYPE_ID' => array('?', $types['COUNTRY'])),
			array('='.$ctx.'.TYPE_ID' => array('?', $types['REGION'])),
			array('='.$ctx.'.TYPE_ID' => array('?', $types['CITY'])),
		);
	}

	public static function getTypeFilterCondition()
	{
		static $types;

		if($types == null)
		{
			$types = self::getTypes();
		}

		return array($types['COUNTRY'], $types['REGION'], $types['CITY']);
	}

	protected static $allowedOps = array(
		'='
	);

	protected static function parseFilter($filter = array())
	{
		$result = array();
		if(is_array($filter))
		{
			foreach($filter as $k => $v)
			{
				$found = array();
				preg_match(self::KEY_PARSE_R, $k, $found);

				if($found[3] <> '')
				{
					$f = array(
						'NOT' => $found[1] == '!',
						'OP' => !mb_strlen($found[2])? '=' : $found[2],
						'VALUE' => $v
					);

					if(in_array($f['OP'], static::$allowedOps))
					{
						$result[$found[3]] = $f;
					}
				}
			}
		}

		return $result;
	}

	public static function getDenormalizedLocationList($entityName, $arFilter = array())
	{
		$class = 				$entityName.'Table';
		$linkField = 			$class::getLinkField();
		$typeField = 			$class::getTypeField();
		$locationLinkField = 	$class::getLocationLinkField();
		//$useGroups = 			$class::getUseGroups();
		$table = 				$class::getTableName();
		$useCodes = 			$class::getUseCodes();
		$locationTable = 		Location\LocationTable::getTableName();
		$groupTable = 			Location\GroupTable::getTableName();

		$locFlag = 				Location\Connector::DB_LOCATION_FLAG;
		$grpFlag = 				Location\Connector::DB_GROUP_FLAG;

		$filter = self::parseFilter($arFilter);

		$where = array();

		if(intval($filter[$linkField]['VALUE']))
			$where[] = 'DL.'.$linkField.' '.($filter[$linkField]['NOT'] ? '!' : '').$filter[$linkField]['OP']." '".intval($filter[$linkField]['VALUE'])."'";

		if(intval($filter['LOCATION_ID']['VALUE']))
			$where[] = 'LINK.ID '.($filter['LOCATION_ID']['NOT'] ? '!' : '').$filter['LOCATION_ID']['OP']." '".intval($filter['LOCATION_ID']['VALUE'])."'";

		if($filter[$typeField]['VALUE'] == Location\Connector::DB_LOCATION_FLAG || $filter[$typeField]['VALUE'] == Location\Connector::DB_GROUP_FLAG)
			$where[] = 'DL.'.$typeField.' '.($filter[$typeField]['NOT'] ? '!' : '').$filter[$typeField]['OP']." '".$filter[$typeField]['VALUE']."'";

		$sql = "

			select distinct
				DL.".$linkField.",
				LINK.ID as LOCATION_ID,
				DL.".$typeField."
			from 
				".$table." DL
			inner join 
				".$locationTable." L on DL.".$typeField." = '".$locFlag."' and DL.".$locationLinkField." = L.".($useCodes ? 'CODE' : 'ID')."
			inner join
				".$locationTable." LINK on LINK.LEFT_MARGIN >= L.LEFT_MARGIN and LINK.RIGHT_MARGIN <= L.RIGHT_MARGIN

			".(!empty($where) ? 'where '.implode(' and ', $where) : '')."

			union

			select
				DL.".$linkField.",
				LINK.ID as LOCATION_ID,
				DL.".$typeField."
			from 
				".$table." DL
			inner join 
				".$groupTable." LINK on DL.".$typeField." = '".$grpFlag."' and DL.".$locationLinkField." = LINK.".($useCodes ? 'CODE' : 'ID')."

			".(!empty($where) ? 'where '.implode(' and ', $where) : '')."
		";

		global $DB;

		return $DB->query($sql);
	}

	/////////////////////////////////////////////
	/////////////////////////////////////////////
	/////////////////////////////////////////////
	// old api works in the old manner only when sale::isLocationProMigrated() returns false

	public static function GetLocationString($locationId, $siteId = SITE_ID, $langId = LANGUAGE_ID)
	{
		$locationString = '';

		if(!\Bitrix\Sale\SalesZone::checkLocationId($locationId, $siteId))
			$locationId = 0;

		$countryId = $regionId = $cityId = 0;
		if ($locationId > 0)
		{
			if ($arLocation = CSaleLocation::GetByID($locationId))
			{
				$countryId = $arLocation["COUNTRY_ID"];
				$regionId = $arLocation["REGION_ID"];
				$cityId = $arLocation["CITY_ID"];
			}
		}

		//check in location city
		$bEmptyCity = "N";
		$arCityFilter = array("!CITY_ID" => "NULL", ">CITY_ID" => "0");
		if ($countryId > 0)
			$arCityFilter["COUNTRY_ID"] = $countryId;
		$rsLocCount = CSaleLocation::GetList(array(), $arCityFilter, false, false, array("ID"));
		if (!$rsLocCount->Fetch())
			$bEmptyCity = "Y";

		//check in location region
		$bEmptyRegion = "N";
		$arRegionFilter = array("!REGION_ID" => "NULL", ">REGION_ID" => "0");
		if ($countryId > 0 && $regionId > 0)
			$arRegionFilter["COUNTRY_ID"] = $countryId;
		if ($regionId > 0)
			$arRegionFilter["REGION_ID"] = $regionId;
		$rsLocCount = CSaleLocation::GetList(array(), $arRegionFilter, false, false, array("ID"));
		if (!$rsLocCount->Fetch())
			$bEmptyRegion = "Y";

		//check if exist another city
		if ($bEmptyCity == "Y" && $bEmptyRegion == "Y")
		{
			$arCityFilter = array("!CITY_ID" => "NULL", ">CITY_ID" => "0");
			$rsLocCount = CSaleLocation::GetList(array(), $arCityFilter, false, false, array("ID"));
			if ($rsLocCount->Fetch())
				$bEmptyCity = "N";
		}

		//location value
		if ($locationId > 0 )
		{
			if ($arLocation = CSaleLocation::GetByID($locationId))
			{
				if ($bEmptyRegion == "Y" && $bEmptyCity == "Y")
					$countryId = $locationId;
				else
					$countryId = $arLocation["COUNTRY_ID"];

				if ($bEmptyCity == "Y")
					$regionId = $arLocation["ID"];
				else
					$regionId = $arLocation["REGION_ID"];

				$cityId = $locationId;
			}
		}

		//select country
		$arCountryList = array();

		if ($bEmptyRegion == "Y" && $bEmptyCity == "Y")
			$rsCountryList = CSaleLocation::GetList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), array("LID" => $langId), false, false, array("ID", "COUNTRY_ID", "COUNTRY_NAME_LANG"));
		else
			$rsCountryList = CSaleLocation::GetCountryList(array("SORT" => "ASC", "NAME_LANG" => "ASC"));

		while ($arCountry = $rsCountryList->GetNext())
		{
			if(!\Bitrix\Sale\SalesZone::checkCountryId($arCountry["ID"], $siteId))
				continue;

			if ($bEmptyRegion == "Y" && $bEmptyCity == "Y")
				$arCountry["NAME_LANG"] = $arCountry["COUNTRY_NAME_LANG"];

			$arCountryList[] = $arCountry;
			if ($arCountry["ID"] == $countryId && $arCountry["NAME_LANG"] <> '')
				$locationString .= $arCountry["NAME_LANG"];
		}

		if (count($arCountryList) <= 0)
			$arCountryList = array();
		elseif (count($arCountryList) == 1)
			$countryId = $arCountryList[0]["ID"];

		//select region
		$arRegionList = array();
		if ($countryId > 0 || count($arCountryList) <= 0)
		{
			$arRegionFilter = array("LID" => $langId, "!REGION_ID" => "NULL", "!REGION_ID" => "0");
			if ($countryId > 0)
				$arRegionFilter["COUNTRY_ID"] = intval($countryId);

			if ($bEmptyCity == "Y")
				$rsRegionList = CSaleLocation::GetList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), $arRegionFilter, false, false, array("ID", "REGION_ID", "REGION_NAME_LANG"));
			else
				$rsRegionList = CSaleLocation::GetRegionList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), $arRegionFilter);

			while ($arRegion = $rsRegionList->GetNext())
			{
				if(!\Bitrix\Sale\SalesZone::checkRegionId($arRegion["ID"], $siteId))
					continue;

				if ($bEmptyCity == "Y")
					$arRegion["NAME_LANG"] = $arRegion["REGION_NAME_LANG"];

				$arRegionList[] = $arRegion;
				if ($arRegion["ID"] == $regionId && $arRegion["NAME_LANG"] <> '')
					$locationString = $arRegion["NAME_LANG"].", ".$locationString;
			}
		}
		if (count($arRegionList) <= 0)
			$arRegionList = array();
		elseif (count($arRegionList) == 1)
			$regionId = $arRegionList[0]["ID"];

		//select city
		$arCityList = array();
		if (
			$bEmptyCity == "N"
			&& ((count($arCountryList) > 0 && count($arRegionList) > 0 && $countryId > 0 && $regionId > 0)
				|| (count($arCountryList) <= 0 && count($arRegionList) > 0 && $regionId > 0)
				|| (count($arCountryList) > 0 && count($arRegionList) <= 0 && $countryId > 0)
				|| (count($arCountryList) <= 0 && count($arRegionList) <= 0))
		)
		{
			$arCityFilter = array("LID" => $langId);
			if ($countryId > 0)
				$arCityFilter["COUNTRY_ID"] = $countryId;
			if ($regionId > 0)
				$arCityFilter["REGION_ID"] = $regionId;

			$rsLocationsList = CSaleLocation::GetList(
				array(
					"SORT" => "ASC",
					"COUNTRY_NAME_LANG" => "ASC",
					"CITY_NAME_LANG" => "ASC"
				),
				$arCityFilter,
				false,
				false,
				array(
					"ID", "CITY_ID", "CITY_NAME"
				)
			);

			while ($arCity = $rsLocationsList->GetNext())
			{
				if(!\Bitrix\Sale\SalesZone::checkCityId($arCity["CITY_ID"], $siteId))
					continue;

				$arCityList[] = array(
					"ID" => $arCity["ID"],
					"CITY_ID" => $arCity['CITY_ID'],
					"CITY_NAME" => $arCity["CITY_NAME"],
				);
				if ($arCity["ID"] == $cityId)
					$locationString = ($arCity["CITY_NAME"] <> '' ? $arCity["CITY_NAME"].", " : "").$locationString;
			}//end while
		}

		return $locationString;
	}

	/////////////////////////////////////////////

	public static function CountryCheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '') return false;

		/*
		$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ((is_set($arFields[$arLang["LID"]], "NAME") || $ACTION=="ADD") && strlen($arFields[$arLang["LID"]]["NAME"])<=0) return false;
		}
		*/

		return True;
	}

	public static function UpdateCountry($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);

		if ($ID <= 0 || !CSaleLocation::CountryCheckFields("UPDATE", $arFields))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeCountryUpdate", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		//////////////////////////////////////

		if(self::isLocationProMigrated())
		{
			try
			{
				// get location id by country id
				$locId = self::getLocationIdByCountryId($ID);

				if(!$locId)
					return false;

				$res = Location\LocationTable::updateExtended(
					$locId,
					self::refineFieldsForSaveCRC($locId, $arFields),
					array('REBALANCE' => false)
				);

				if($res->isSuccess())
					return $ID;

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			$strUpdate = $DB->PrepareUpdate("b_sale_location_country", $arFields);
			$strSql = "UPDATE b_sale_location_country SET ".$strUpdate." WHERE ID = ".$ID."";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				if ($arCntLang = CSaleLocation::GetCountryLangByID($ID, $arLang["LID"]))
				{
					$strUpdate = $DB->PrepareUpdate("b_sale_location_country_lang", $arFields[$arLang["LID"]]);
					$strSql = "UPDATE b_sale_location_country_lang SET ".$strUpdate." WHERE ID = ".$arCntLang["ID"]."";
				}
				else
				{
					$arInsert = $DB->PrepareInsert("b_sale_location_country_lang", $arFields[$arLang["LID"]]);
					$strSql =
						"INSERT INTO b_sale_location_country_lang(COUNTRY_ID, ".$arInsert[0].") ".
						"VALUES(".$ID.", ".$arInsert[1].")";
				}
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		//////////////////////////////////////

		foreach (GetModuleEvents("sale", "OnCountryUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function DeleteCountry($ID)
	{
		global $DB;
		$ID = intval($ID);

		foreach (GetModuleEvents("sale", "OnBeforeCountryDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				/*
				$locId = self::getLocationIdByCountryId($ID);

				if(!$locId)
					return false;

				// just "unlink" it, so CSaleLocation::GetCountryByID() wont able to find it
				$res = Location\LocationTable::update($locId, array(
					'COUNTRY_ID' => ''
				));

				return $res->isSuccess();
				*/
				return true;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		
		// and also drop old records, if any
		$DB->Query("DELETE FROM b_sale_location_country_lang WHERE COUNTRY_ID = ".$ID."", true);
		$bDelete = $DB->Query("DELETE FROM b_sale_location_country WHERE ID = ".$ID."", true);

		foreach (GetModuleEvents("sale", "OnCountryDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return $bDelete;
	}

	public static function GetCountryByID($ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'=TYPE.CODE' => 'COUNTRY',
						'=ID' => intval($ID),
						'=NAME.LANGUAGE_ID' => self::ORIGIN_NAME_LANGUAGE_ID
					),
					'select' => array(
						'ID',
						'LNAME' => 'NAME.NAME',
						'SHORT_NAME' => 'NAME.SHORT_NAME'
					)
				));
				$res->addReplacedAliases(array('LNAME' => 'NAME'));

				$item = $res->fetch();

				if($item)
					return $item;

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$ID = intval($ID);
			$strSql =
				"SELECT * ".
				"FROM b_sale_location_country ".
				"WHERE ID = ".$ID." ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;

		}
	}

	public static function GetCountryLangByID($ID, $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'=TYPE.CODE' => 'COUNTRY',
						'=ID' => intval($ID),
						'=NAME.LANGUAGE_ID' => trim($strLang)
					),
					'select' => array(
						'ID',
						'ID_' => 'NAME.ID',
						'LID' => 'NAME.LANGUAGE_ID',
						'LNAME' => 'NAME.NAME',
						'SHORT_NAME' => 'NAME.SHORT_NAME',
					)
				));

				$item = $res->fetch();

				if($item)
				{
					return array(
						'ID' => $item['ID_'],
						'COUNTRY_ID' => $item['ID'],
						'LID' => $item['LID'],
						'NAME' => $item['LNAME'],
						'SHORT_NAME' => $item['SHORT_NAME'],
					);
				}

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{

			global $DB;

			$ID = intval($ID);
			$strLang = Trim($strLang);

			$strSql =
				"SELECT * ".
				"FROM b_sale_location_country_lang ".
				"WHERE COUNTRY_ID = ".$ID." ".
				"	AND LID = '".$DB->ForSql($strLang, 2)."' ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;

		}
	}

	/////////////////////////////////////////////
	
	public static function RegionCheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '') return false;

		return True;
	}

	public static function UpdateRegion($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);

		if ($ID <= 0 || !CSaleLocation::RegionCheckFields("UPDATE", $arFields))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeRegionUpdate", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				$locId = self::getLocationIdByRegionId($ID);

				if(!$locId)
					return false;

				$res = Location\LocationTable::updateExtended(
					$locId,
					self::refineFieldsForSaveCRC($locId, $arFields),
					array('REBALANCE' => false)
				);

				if($res->isSuccess())
					return $ID;

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			$strUpdate = $DB->PrepareUpdate("b_sale_location_region", $arFields);
			$strSql = "UPDATE b_sale_location_region SET ".$strUpdate." WHERE ID = ".$ID."";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				if ($arCntLang = CSaleLocation::GetRegionLangByID($ID, $arLang["LID"]))
				{
					$strUpdate = $DB->PrepareUpdate("b_sale_location_region_lang", $arFields[$arLang["LID"]]);

					$strSql = "UPDATE b_sale_location_region_lang SET ".$strUpdate." WHERE ID = ".$arCntLang["ID"]."";
				}
				else
				{
					$arInsert = $DB->PrepareInsert("b_sale_location_region_lang", $arFields[$arLang["LID"]]);
					$strSql =
						"INSERT INTO b_sale_location_region_lang(REGION_ID, ".$arInsert[0].") ".
						"VALUES(".$ID.", ".$arInsert[1].")";
				}
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		foreach (GetModuleEvents("sale", "OnRegionUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function DeleteRegion($ID)
	{
		// there is no such entity in terms of location 2.0, so... just delete old entity

		global $DB;
		$ID = intval($ID);

		foreach (GetModuleEvents("sale", "OnBeforeRegionDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				/*
				$locId = self::getLocationIdByRegionId($ID);

				if(!$locId)
					return false;

				// just "unlink" it, so CSaleLocation::GetCityByID() wont able to find it
				$res = Location\LocationTable::update($locId, array(
					'REGION_ID' => ''
				));

				return $res->isSuccess();
				*/
				return true;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		
		// and also drop old records, if any
		$DB->Query("DELETE FROM b_sale_location_region_lang WHERE REGION_ID = ".$ID."", true);
		$bDelete = $DB->Query("DELETE FROM b_sale_location_region WHERE ID = ".$ID."", true);

		foreach (GetModuleEvents("sale", "OnRegionDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return $bDelete;
	}

	public static function GetRegionByID($ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'=TYPE.CODE' => 'REGION',
						'=ID' => intval($ID),
						'NAME.LANGUAGE_ID' => self::ORIGIN_NAME_LANGUAGE_ID
					),
					'select' => array(
						'ID',
						'LNAME' => 'NAME.NAME',
						'SHORT_NAME' => 'NAME.SHORT_NAME',
					)
				));
				$res->addReplacedAliases(array('LNAME' => 'NAME'));

				$item = $res->fetch();

				if($item)
					return $item;

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$ID = intval($ID);
			$strSql =
				"SELECT * ".
				"FROM b_sale_location_region ".
				"WHERE ID = ".$ID." ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;
		}
	}

	public static function GetRegionLangByID($ID, $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'=TYPE.CODE' => 'REGION',
						'=ID' => intval($ID),
						'NAME.LANGUAGE_ID' => trim($strLang)
					),
					'select' => array(
						'ID',
						'ID_' => 'NAME.ID',
						'LID' => 'NAME.LANGUAGE_ID',
						'LNAME' => 'NAME.NAME',
						'SHORT_NAME' => 'NAME.SHORT_NAME',
					)
				));
				$res->addReplacedAliases(array('LNAME' => 'NAME'));

				$item = $res->fetch();

				if($item)
				{
					return array(
						'ID' => $item['ID_'],
						'REGION_ID' => $item['ID'],
						'LID' => $item['LID'],
						'NAME' => $item['LNAME'],
						'SHORT_NAME' => $item['SHORT_NAME'],
					);
				}

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$ID = intval($ID);
			$strLang = Trim($strLang);

			$strSql =
				"SELECT * ".
				"FROM b_sale_location_region_lang ".
				"WHERE REGION_ID = ".$ID." ".
				" AND LID = '".$DB->ForSql($strLang, 2)."' ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;
		}
	}

	/////////////////////////////////////////////

	public static function CityCheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '') return false;

		/*
		$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ((is_set($arFields[$arLang["LID"]], "NAME") || $ACTION=="ADD") && strlen($arFields[$arLang["LID"]]["NAME"])<=0) return false;
		}
		*/

		return True;
	}

	public static function UpdateCity($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);

		if ($ID <= 0 || !CSaleLocation::CityCheckFields("UPDATE", $arFields))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeCityUpdate", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				$locId = self::getLocationIdByCityId($ID);

				if(!$locId)
					return false;

				$res = Location\LocationTable::updateExtended(
					$locId,
					self::refineFieldsForSaveCRC($locId, $arFields),
					array('REBALANCE' => false)
				);

				if($res->isSuccess())
					return $ID;

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			$strUpdate = $DB->PrepareUpdate("b_sale_location_city", $arFields);
			$strSql = "UPDATE b_sale_location_city SET ".$strUpdate." WHERE ID = ".$ID."";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				if ($arCntLang = CSaleLocation::GetCityLangByID($ID, $arLang["LID"]))
				{
					$strUpdate = $DB->PrepareUpdate("b_sale_location_city_lang", $arFields[$arLang["LID"]]);
					$strSql = "UPDATE b_sale_location_city_lang SET ".$strUpdate." WHERE ID = ".$arCntLang["ID"]."";
				}
				else
				{
					$arInsert = $DB->PrepareInsert("b_sale_location_city_lang", $arFields[$arLang["LID"]]);
					$strSql =
						"INSERT INTO b_sale_location_city_lang(CITY_ID, ".$arInsert[0].") ".
						"VALUES(".$ID.", ".$arInsert[1].")";
				}
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		foreach (GetModuleEvents("sale", "OnCityUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function DeleteCity($ID)
	{
		// there is no such entity in terms of location 2.0, so... just delete old entity

		global $DB;
		$ID = intval($ID);

		foreach (GetModuleEvents("sale", "OnBeforeCityDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				/*
				$locId = self::getLocationIdByCityId($ID);

				if(!$locId)
					return false;

				// just "unlink" it, so CSaleLocation::GetCityByID() wont able to find it
				$res = Location\LocationTable::update($locId, array(
					'CITY_ID' => ''
				));

				return $res->isSuccess();
				*/

				return true;
			}
			catch(Exception $e)
			{
				return false;
			}
		}

		// and also drop old records, if any
		$DB->Query("DELETE FROM b_sale_location_city_lang WHERE CITY_ID = ".$ID."", true);
		$bDelete = $DB->Query("DELETE FROM b_sale_location_city WHERE ID = ".$ID."", true);

		foreach (GetModuleEvents("sale", "OnCityDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return $bDelete;
	}

	public static function GetCityByID($ID)
	{
		if(self::isLocationProMigrated())
		{
			static $regionId;

			try
			{
				if(!$regionId)
				{
					$params = array('select' => array());
					$res = \Bitrix\Sale\Location\TypeTable::getList(array('filter' => array('=CODE' => 'REGION'), 'select' => array('ID')))->fetch();

					if($res['ID'])
					{
						$regionId = $res['ID'];
					}
				}

				if($regionId)
				{
					$params['runtime'] = array(
						'P' => array(
							'data_type' => '\Bitrix\Sale\Location\Location',
							'reference' => array(
								'<=ref.LEFT_MARGIN' => 'this.LEFT_MARGIN',
								'>=ref.RIGHT_MARGIN' => 'this.RIGHT_MARGIN',
								'=ref.TYPE_ID' => array('?', $regionId)
							),
							'join_type' => "left"
						)
					);

					$params['select']['PREGION_ID'] = 'P.ID';
				}

				$params['filter'] = array(
					'=TYPE.CODE' => 'CITY',
					'=ID' => intval($ID),
					'=NAME.LANGUAGE_ID' => 'en',
				);

				$params['select'] = array_merge($params['select'], array(
					'ID',
					'LNAME' => 'NAME.NAME',
					'SHORT_NAME' => 'NAME.SHORT_NAME',
				));

				$res = \Bitrix\Sale\Location\LocationTable::getList($params);
				$res->addReplacedAliases(array('LNAME' => 'NAME', 'PREGION_ID' => 'REGION_ID'));

				$item = $res->fetch();

				if($item)
				{
					return $item;
				}

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$ID = intval($ID);
			$strSql =
				"SELECT * ".
				"FROM b_sale_location_city ".
				"WHERE ID = ".$ID." ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;
		}
	}

	public static function GetCityLangByID($ID, $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'=TYPE.CODE' => 'CITY',
						'=ID' => intval($ID),
						'NAME.LANGUAGE_ID' => trim($strLang)
					),
					'select' => array(
						'ID',
						'ID_' => 'NAME.ID',
						'LID' => 'NAME.LANGUAGE_ID',
						'LNAME' => 'NAME.NAME',
						'SHORT_NAME' => 'NAME.SHORT_NAME',
					)
				));
				$res->addReplacedAliases(array('LNAME' => 'NAME'));

				$item = $res->fetch();

				if($item)
				{
					return array(
						'ID' => $item['ID_'],
						'CITY_ID' => $item['ID'],
						'LID' => $item['LID'],
						'NAME' => $item['LNAME'],
						'SHORT_NAME' => $item['SHORT_NAME'],
					);
				}

				return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$ID = intval($ID);
			$strLang = Trim($strLang);

			$strSql =
				"SELECT * ".
				"FROM b_sale_location_city_lang ".
				"WHERE CITY_ID = ".$ID." ".
				"	AND LID = '".$DB->ForSql($strLang, 2)."' ";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				return $res;
			}
			return False;
		}
	}

	/////////////////////////////////////////////

	private static function processOrderForGetList($arOrder, $fieldMap = array(), $fieldProxy = array())
	{
		if(is_array($arOrder))
		{
			$arOrderParsed = array();
			$fieldProxy = array_flip($fieldProxy);

			// parse order and stirp away unknown fields
			$arOrder = array_change_key_case($arOrder, CASE_UPPER);
			foreach($arOrder as $fld => $direction)
			{
				$direction = ToUpper($direction);

				if($direction != 'ASC' && $direction != 'DESC')
					continue;

				if(isset($fieldProxy[$fld]))
					$fld = $fieldProxy[$fld];

				if(!isset($fieldMap[$fld]))
					continue;

				$fld = $fieldMap[$fld];

				$arOrderParsed[$fld] = $direction;
			}

			return $arOrderParsed;
		}
		else
			return false;
	}

	public static function processFilterForGetList($arFilter, $fieldMap = array(), $fieldProxy = array(), $query = null)
	{
		if(is_array($arFilter))
		{
			$filterFieldsClean = array();
			$arFilterParsed = array();
			$pseudoFields = array();
			$fieldProxy = array_flip($fieldProxy);

			$dbConnection = Main\HttpApplication::getConnection();
			$dbHelper = $dbConnection->getSqlHelper();

			// parse filter and stirp away unknown fields
			$arFilter = array_change_key_case($arFilter, CASE_UPPER);
			foreach($arFilter as $fld => $value)
			{
				$fld = trim($fld);
				$fld = preg_replace(self::LEADING_TILDA_SEARCH_R, '', $fld);

				$found = array();
				preg_match(self::MODIFIER_SEARCH_R, $fld, $found);

				$modifier = $found[1] <> ''? $found[1] : '';
				$fldClean = preg_replace(self::MODIFIER_SEARCH_R, '', $fld);

				if(isset($fieldProxy[$fldClean]))
					$fldClean = $fieldProxy[$fldClean];

				if(!isset($fieldMap[$fldClean]))
					continue;

				$fldClean = $fieldMap[$fldClean];

				if($modifier == '+') // ORM does not understand + modifier in a filter
				{
					if($query != null)
					{
						// $value could be array or scalar

						if(is_array($value))
						{
							foreach($value as $i => $v)
							{
								$value[$i] = "'".$dbHelper->forSql($v)."'";
							}
							$value = 'in ('.implode(', ', $value).')';
						}
						else
							$value = '= '.$dbHelper->forSql($value);

						$query->registerRuntimeField('PLUS_'.$fldClean, array(
							'data_type' => 'integer',
							'expression' => array(
								"case when ((%s ".$value.") or (%s is null) or (%s = 0)) then 1 else 0 end",
								$fldClean,
								$fldClean,
								$fldClean
							)
						));
						$arFilterParsed['=PLUS_'.$fldClean] = 1;
					}

					continue;
				}

				$arFilterParsed[$modifier.$fldClean] = $value;
				$filterFieldsClean[] = $fldClean;
			}

			return array($arFilterParsed, $filterFieldsClean, $pseudoFields);
		}
		else
			return array(false, array(), array());
	}

	public static function processSelectForGetList($arSelectFields, $fieldMap)
	{
		$selectFields = array();
		if(is_array($arSelectFields) && !empty($arSelectFields) && !in_array('*', $arSelectFields, true))
		{
			$arSelectFields = array_map("strtoupper", $arSelectFields);
			foreach($arSelectFields as $fld)
			{
				if(isset($fieldMap[$fld])) // legal one
					$selectFields[$fld] = $fieldMap[$fld];
			}
		}
		else
			$selectFields = $fieldMap;

		$select = array();
		foreach($selectFields as $fldFrom => $fldTo)
		{
			if($fldFrom == $fldTo)
				$select[] = $fldTo;
			else
				$select[$fldFrom] = $fldTo;
		}

		return $select;
	}

	private static function proxyFieldsInResult($res, $fieldProxy = array())
	{
		if(!is_array($fieldProxy) || empty($fieldProxy))
			return $res;

		$result = array(); 
		while($item = $res->fetch())
		{
			$pItem = array();
			foreach($item as $k => $v)
			{
				if(isset($fieldProxy[$k]))
					$pItem[$fieldProxy[$k]] = $v;
				else
					$pItem[$k] = $v;
			}

			$result[] = $pItem;
		}

		return new DB\ArrayResult($result);
	}

	private static function stripModifiers($filter)
	{
		if(is_array($filter) && !empty($filter))
		{
			$result = array();
			foreach($filter as $k => $v)
			{
				$k = preg_replace(self::MODIFIER_SEARCH_R, '', $k);
				$result[$k] = $v;
			}

			return $result;
		}

		return $filter;
	}

	/////////////////////////////////////////////

	public static function getFilterForGetList($arFilter)
	{
		if(self::isLocationProMigrated())
		{
			$types = self::getTypes();

			$arFilter['TYPE_ID'] = array();

			if(intval($types['COUNTRY']))
				$arFilter['TYPE_ID'][] = intval($types['COUNTRY']);

			if(intval($types['REGION']))
				$arFilter['TYPE_ID'][] = intval($types['REGION']);

			if(intval($types['CITY']))
				$arFilter['TYPE_ID'][] = intval($types['CITY']);
		}

		return $arFilter;
	}

	public static function getFieldMapForGetList($arFilter)
	{
		global $DB;

		$additionalFilter = "";
		if (isset($arFilter["LID"]) || $arFilter["LID"] <> '')
		{

			if(self::isLocationProMigrated())
			{
				$additionalFilterLCL = " AND COL.LANGUAGE_ID = '".$DB->ForSql($arFilter["LID"], 2)."'";
				$additionalFilterLRL = " AND REL.LANGUAGE_ID = '".$DB->ForSql($arFilter["LID"], 2)."'";
				$additionalFilterLGL = " AND CIL.LANGUAGE_ID = '".$DB->ForSql($arFilter["LID"], 2)."'";
			}
			else
			{
				$additionalFilterLCL = " AND LCL.LID = '".$DB->ForSql($arFilter["LID"], 2)."'";
				$additionalFilterLRL = " AND LRL.LID = '".$DB->ForSql($arFilter["LID"], 2)."'";
				$additionalFilterLGL = " AND LGL.LID = '".$DB->ForSql($arFilter["LID"], 2)."'";
			}
		}
		if(self::isLocationProMigrated())
		{
			$tableDefault = \Bitrix\Sale\Location\DefaultSiteTable::getTableName();

			// FIELDS -->
			$arFields = array(

					"LOC_DEFAULT" => array("FIELD" => "CASE WHEN DEF.LOCATION_CODE IS NULL THEN 'N' ELSE 'Y' END", "TYPE" => "string", "FROM" => "LEFT JOIN ".$tableDefault." DEF ON (DEF.LOCATION_CODE = L.CODE)"),

					/*
					COO = COuntry Original
					COL = Country Language
					REO = REgion Original
					REL = REgion Language
					CIO = CIty Original
					CIL = CIty Language
					*/

					// lang-independent
					"COUNTRY_NAME_ORIG" => array("FIELD" => "COO.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COO ON (L.COUNTRY_ID = COO.LOCATION_ID and COO.LANGUAGE_ID = 'en')"),
					"COUNTRY_SHORT_NAME" => array("FIELD" => "COO.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COO ON (L.COUNTRY_ID = COO.LOCATION_ID and COO.LANGUAGE_ID = 'en')"),

					// lang-dependent
					"COUNTRY_NAME" => array("FIELD" => "COL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COL ON (L.COUNTRY_ID = COL.LOCATION_ID".$additionalFilterLCL.")"),
					"COUNTRY_LID" => array("FIELD" => "COL.LANGUAGE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COL ON (L.COUNTRY_ID = COL.LOCATION_ID".$additionalFilterLCL.")"),
					"COUNTRY_NAME_LANG" => array("FIELD" => "COL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COL ON (L.COUNTRY_ID = COL.LOCATION_ID".$additionalFilterLCL.")"),
					"COUNTRY_SHORT_NAME_LANG" => array("FIELD" => "COL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COL ON (L.COUNTRY_ID = COL.LOCATION_ID".$additionalFilterLCL.")"),


					// lang-independent
					"CITY_NAME_ORIG" => array("FIELD" => "CIO.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIO ON (L.CITY_ID = CIO.LOCATION_ID and CIO.LANGUAGE_ID = 'en')"),
					"CITY_SHORT_NAME" => array("FIELD" => "CIO.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIO ON (L.CITY_ID = CIO.LOCATION_ID and CIO.LANGUAGE_ID = 'en')"),

					// lang-dependent
					"CITY_NAME" => array("FIELD" => "CIL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIL ON (L.CITY_ID = CIL.LOCATION_ID".$additionalFilterLGL.")"),
					"CITY_LID" => array("FIELD" => "CIL.LANGUAGE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIL ON (L.CITY_ID = CIL.LOCATION_ID".$additionalFilterLGL.")"),
					"CITY_NAME_LANG" => array("FIELD" => "CIL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIL ON (L.CITY_ID = CIL.LOCATION_ID".$additionalFilterLGL.")"),
					"CITY_SHORT_NAME_LANG" => array("FIELD" => "CIL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIL ON (L.CITY_ID = CIL.LOCATION_ID".$additionalFilterLGL.")"),


					// lang-independent
					"REGION_NAME_ORIG" => array("FIELD" => "REO.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REO ON (L.REGION_ID = REO.LOCATION_ID and REO.LANGUAGE_ID = 'en')"),
					"REGION_SHORT_NAME" => array("FIELD" => "REO.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REO ON (L.REGION_ID = REO.LOCATION_ID and REO.LANGUAGE_ID = 'en')"),

					// lang-dependent
					"REGION_NAME" => array("FIELD" => "REL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REL ON (L.REGION_ID = REL.LOCATION_ID".$additionalFilterLRL.")"),
					"REGION_LID" => array("FIELD" => "REL.LANGUAGE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REL ON (L.REGION_ID = REL.LOCATION_ID".$additionalFilterLRL.")"),
					"REGION_NAME_LANG" => array("FIELD" => "REL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REL ON (L.REGION_ID = REL.LOCATION_ID".$additionalFilterLRL.")"),
					"REGION_SHORT_NAME_LANG" => array("FIELD" => "REL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REL ON (L.REGION_ID = REL.LOCATION_ID".$additionalFilterLRL.")"),

					"COUNTRY" => array("FIELD" => "COL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name COL ON (L.COUNTRY_ID = COL.LOCATION_ID".$additionalFilterLCL.")"),
					"CITY" => array("FIELD" => "CIL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name CIL ON (L.CITY_ID = CIL.LOCATION_ID".$additionalFilterLGL.")"),
					"REGION" => array("FIELD" => "REL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_loc_name REL ON (L.REGION_ID = REL.LOCATION_ID".$additionalFilterLRL.")"),

					"TYPE_ID" => array("FIELD" => "L.TYPE_ID", "TYPE" => "int")
				);
		}
		else
		{

			// FIELDS -->
			$arFields = array(

					"LOC_DEFAULT" => array("FIELD" => "L.LOC_DEFAULT", "TYPE" => "string"),

					"COUNTRY_NAME_ORIG" => array("FIELD" => "LC.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID)"),
					"COUNTRY_SHORT_NAME" => array("FIELD" => "LC.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID)"),

					"CITY_NAME_ORIG" => array("FIELD" => "LG.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city LG ON (L.CITY_ID = LG.ID)"),
					"CITY_SHORT_NAME" => array("FIELD" => "LG.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city LG ON (L.CITY_ID = LG.ID)"),
				
					"REGION_NAME_ORIG" => array("FIELD" => "LR.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region LR ON (L.REGION_ID = LR.ID)"),
					"REGION_SHORT_NAME" => array("FIELD" => "LR.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region LR ON (L.REGION_ID = LR.ID)"),
				
					"COUNTRY_LID" => array("FIELD" => "LCL.LID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country_lang LCL ON (L.COUNTRY_ID = LCL.COUNTRY_ID".$additionalFilterLCL.")"),
					"COUNTRY_NAME" => array("FIELD" => "LCL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country_lang LCL ON (L.COUNTRY_ID = LCL.COUNTRY_ID".$additionalFilterLCL.")"),
					"COUNTRY_NAME_LANG" => array("FIELD" => "LCL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country_lang LCL ON (L.COUNTRY_ID = LCL.COUNTRY_ID".$additionalFilterLCL.")"),
					"COUNTRY_SHORT_NAME_LANG" => array("FIELD" => "LCL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country_lang LCL ON (L.COUNTRY_ID = LCL.COUNTRY_ID".$additionalFilterLCL.")"),

					"REGION_LID" => array("FIELD" => "LRL.LID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region_lang LRL ON (L.REGION_ID = LRL.REGION_ID".$additionalFilterLRL.")"),
					"REGION_NAME" => array("FIELD" => "LRL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region_lang LRL ON (L.REGION_ID = LRL.REGION_ID".$additionalFilterLRL.")"),
					"REGION_NAME_LANG" => array("FIELD" => "LRL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region_lang LRL ON (L.REGION_ID = LRL.REGION_ID".$additionalFilterLRL.")"),
					"REGION_SHORT_NAME_LANG" => array("FIELD" => "LRL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region_lang LRL ON (L.REGION_ID = LRL.REGION_ID".$additionalFilterLRL.")"),
				
					"CITY_LID" => array("FIELD" => "LGL.LID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city_lang LGL ON (L.CITY_ID = LGL.CITY_ID".$additionalFilterLGL.")"),
					"CITY_NAME" => array("FIELD" => "LGL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city_lang LGL ON (L.CITY_ID = LGL.CITY_ID".$additionalFilterLGL.")"),
					"CITY_NAME_LANG" => array("FIELD" => "LGL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city_lang LGL ON (L.CITY_ID = LGL.CITY_ID".$additionalFilterLGL.")"),
					"CITY_SHORT_NAME_LANG" => array("FIELD" => "LGL.SHORT_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city_lang LGL ON (L.CITY_ID = LGL.CITY_ID".$additionalFilterLGL.")"),

					"COUNTRY" => array("FIELD" => "LCL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_country_lang LCL ON (L.COUNTRY_ID = LCL.COUNTRY_ID".$additionalFilterLCL.")"),
					"CITY" => array("FIELD" => "LGL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_city_lang LGL ON (L.CITY_ID = LGL.CITY_ID".$additionalFilterLGL.")"),
					"REGION" => array("FIELD" => "LRL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_location_region_lang LRL ON (L.REGION_ID = LRL.REGION_ID".$additionalFilterLRL.")")
				);

		}

		return array_merge(array(
			"ID" => array("FIELD" => "L.ID", "TYPE" => "int"),
			"CODE" => array("FIELD" => "L.CODE", "TYPE" => "string"),
			"COUNTRY_ID" => array("FIELD" => "L.COUNTRY_ID", "TYPE" => "int"),
			"CITY_ID" => array("FIELD" => "L.CITY_ID", "TYPE" => "int"),
			"REGION_ID" => array("FIELD" => "L.REGION_ID", "TYPE" => "int"),
			"SORT" => array("FIELD" => "L.SORT", "TYPE" => "int")
		), $arFields);
	}

	protected static function getNameOfParentOfType($item, $typeCode, $strLang = LANGUAGE_ID)
	{
		/*
		$item = Location\LocationTable::getList(array('filter' => array(
			'<=LEFT_MARGIN' => $item['LEFT_MARGIN'],
			'>=RIGHT_MARGIN' => $item['RIGHT_MARGIN'],
			'=TYPE.CODE' => $typeCode,
			'=NAME.LANGUAGE_ID' => $strLang
		), 'select' => array(
			ToUpper($typeCode).'_NAME' => 'NAME.NAME',
			ToUpper($typeCode).'_NAME_ORIG' => 'NAME.NAME',
			ToUpper($typeCode).'_SHORT_NAME' => 'NAME.SHORT_NAME',
			ToUpper($typeCode).'_NAME_LANG' => 'NAME.LANGUAGE_ID'
		)))->fetch();
		*/

		global $DB;

		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$types = self::getTypes();
		$typeCode = ToUpper($dbHelper->forSql($typeCode));
		$strLang = mb_substr($dbHelper->forSql($strLang), 0, 2);

		$mappedTypes = array("'".intval($types[$typeCode])."'");

		if($typeCode == 'CITY' && intval($types['VILLAGE'])) // VILLAGE is also can be CITY in the old terms
		{
			$mappedTypes[] = "'".intval($types['VILLAGE'])."'";
		}

		$query = "
			select 
				N.NAME as ".$typeCode."_NAME,
				N.SHORT_NAME as ".$typeCode."_SHORT_NAME,
				L.ID as ".$typeCode."_ID
			from 
				b_sale_loc_name N
				inner join b_sale_location L on 
					N.LOCATION_ID = L.ID 
					and
					N.LANGUAGE_ID = '".$strLang."'
					and
					L.LEFT_MARGIN <= '".intval($item['LEFT_MARGIN'])."'
					and
					L.RIGHT_MARGIN >= '".intval($item['RIGHT_MARGIN'])."'
					and
					L.TYPE_ID in (".implode(', ', $mappedTypes).")
		";

		$item = $DB->query($query)->fetch();

		$item[$typeCode.'_NAME_ORIG'] = $item[$typeCode.'_NAME'];
		$item[$typeCode.'_NAME_LANG'] = $item[$typeCode.'_NAME'];

		if(!is_array($item))
			return array();

		return $item;
	}

	protected static $city2RegionMap = array(
		'0000073738' => '0000028025', // moscow => moskovskaya oblast
		'0000103664' => '0000028043', // stpetersburg => leningradskaya oblast
	);

	protected static $specialCities = array('MOSCOW', 'STPETERSBURG');

	public static function GetByIDForLegacyDelivery($primary, $strLang = LANGUAGE_ID)
	{
		$primary = trim($primary);
		$regLoc = false;

		$loc = CSaleLocation::GetByID($primary, $strLang);

		if(!intval($loc['REGION_ID'])) // no region
		{
			if(isset(self::$city2RegionMap[(string) $primary])) // got CODE and this code in The List
			{
				$regLoc = CSaleLocation::GetByID(self::$city2RegionMap[(string) $primary], $strLang);
			}
			elseif(!empty($loc['CODE']) &&  !empty(self::$city2RegionMap[(string) $loc['CODE']]))
			{
				$regLoc = CSaleLocation::GetByID(self::$city2RegionMap[(string) $loc['CODE']], $strLang);
			}
			elseif((string) $loc['CITY_NAME_LANG'] != '') // search by name
			{
				$name = ToUpper(trim($loc['CITY_NAME_LANG']));
				$regionName = false;

				foreach(self::$specialCities as $city)
				{
					if($name == ToUpper(GetMessage('CITY_'.$city)))
						$regionName = GetMessage('REGION_'.$city);
				}

				if($regionName !== false)
				{
					$regLoc = CSaleLocation::GetList(
						false, 
						array('~REGION_NAME_LANG' => $regionName.'%', 'LID' => $strLang), 
						false, 
						array('nTopCount' => 1), 
						array('REGION_ID', 'REGION_NAME', 'REGION_SHORT_NAME', 'REGION_NAME_ORIG', 'REGION_NAME_LANG')
					)->fetch();
				}
			}
		}

		if($regLoc !== false)
		{
			if(intval($regLoc['REGION_ID']))
			{
				$loc['REGION_ID'] = 		$regLoc['REGION_ID'];
				$loc['REGION_NAME'] = 		$regLoc['REGION_NAME'];
				$loc['REGION_SHORT_NAME'] = $regLoc['REGION_SHORT_NAME'];
				$loc['REGION_NAME_ORIG'] = 	$regLoc['REGION_NAME_ORIG'];
				$loc['REGION_NAME_LANG'] = 	$regLoc['REGION_NAME_LANG'];
			}
		}

		return $loc;
	}

	public static function GetByID($primary, $strLang = LANGUAGE_ID)
	{
		if($primary == '')
			return false;

		try
		{
			// try code
			$item = Location\LocationTable::getList(array('filter' => array(
				array('=CODE' => $primary)
			), 'select' => 
				array('ID', 'SORT', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'CODE')
			))->fetch();

			if(!is_array($item))
			{
				// try id
				$item = Location\LocationTable::getList(array('filter' => array(
					array('=ID' => $primary)
				), 'select' => 
					array('ID', 'SORT', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'CODE')
				))->fetch();
			}
		}
		catch(\Exception $e)
		{
			return false;
		}

		if(!is_array($item) || !intval($item['ID']))
			return false;

		$country = self::getNameOfParentOfType($item, 'COUNTRY', $strLang);
		$region = self::getNameOfParentOfType($item, 'REGION', $strLang);
		$city = self::getNameOfParentOfType($item, 'CITY', $strLang);

		$item = array_merge($item, $country, $region, $city);

		unset($item['LEFT_MARGIN']);
		unset($item['RIGHT_MARGIN']);

		return $item;
	}

	public static function LocationCheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intval($arFields["SORT"])<=0) $arFields["SORT"] = 100;
		if (is_set($arFields, "COUNTRY_ID")) $arFields["COUNTRY_ID"] = intval($arFields["COUNTRY_ID"]);
		if (is_set($arFields, "CITY_ID")) $arFields["CITY_ID"] = intval($arFields["CITY_ID"]);

		return True;
	}

	public static function UpdateLocation($ID, $arFields)
	{
		// it seems that method is okay... we probably want to move region and city as it set in $arFields, but then we`ll have to adjsut the rest of locations

		global $DB;

		$ID = intval($ID);

		if ($ID <= 0 || !CSaleLocation::LocationCheckFields("UPDATE", $arFields))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeLocationUpdate", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_location", $arFields);
		$strSql = "UPDATE b_sale_location SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		foreach (GetModuleEvents("sale", "OnLocationUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if (is_set($arFields, "CHANGE_COUNTRY") && $arFields["CHANGE_COUNTRY"]!="Y")
			$arFields["CHANGE_COUNTRY"] = "N";
		if (is_set($arFields, "WITHOUT_CITY") && $arFields["WITHOUT_CITY"]!="Y")
			$arFields["WITHOUT_CITY"] = "N";

		if (is_set($arFields, "COUNTRY_ID"))
			$arFields["COUNTRY_ID"] = trim($arFields["COUNTRY_ID"]);
		//	$arFields["COUNTRY_ID"] = IntVal($arFields["COUNTRY_ID"]);

		if (is_set($arFields, "CHANGE_COUNTRY") && $arFields["CHANGE_COUNTRY"]=="Y"
			&& (!is_set($arFields, "COUNTRY_ID") || $arFields["COUNTRY_ID"]<=0))
			return false;

		return True;
	}

	private static function AddLocationPart($creat, $type, $parent, $sort)
	{
		$langs = self::getLanguages();
		$types = self::getTypes();

		$creatFlds = array();
		if(is_numeric($creat))
		{

			// check whether location exists...
			$res = Location\LocationTable::getList(array('filter' => array('='.$type.'_ID' => $creat, 'TYPE_ID' => $types[$type]), 'select' => array('ID'), 'limit' => 1))->fetch();
			if($res['ID'])
			{
				$parent = intval($res['ID']);
			}
			else
			{
				if($type == 'COUNTRY')
					$res = self::GetCountryByID($creat); //!!!
				elseif($type == 'REGION')
					$res = self::GetRegionByID($creat); //!!!
				elseif($type == 'CITY')
					$res = self::GetCityByID($creat); //!!!

				if(!$res) // no such type exists, smth strange
					throw new Exception('No such '.$type);

				// create location using type found
				$creatFlds[$type.'_ID'] = $res['ID'];

				$creatFlds['NAME'] = array();
				foreach($langs as $lid)
				{
					if($type == 'COUNTRY')
						$name = self::GetCountryLangByID($res['ID'], $lid); //!!!
					elseif($type == 'REGION')
						$name = self::GetRegionLangByID($res['ID'], $lid); //!!!
					elseif($type == 'CITY')
						$name = self::GetCityLangByID($res['ID'], $lid); //!!!

					$creatFlds['NAME'][$lid] = array(
						'NAME' => $name['NAME'],
						'SHORT_NAME' => $name['SHORT_NAME']
					);
				}
			}
		}
		elseif(is_array($creat)) // should create type
		{
			$creatFlds[$type.'_ID'] = self::getFreeId($type);
			$creatFlds['NAME'] = array();

			foreach($creat as $lid => $name)
			{
				$creatFlds['NAME'][$lid] = array(
					'NAME' => $name['NAME'],
					'SHORT_NAME' => $name['SHORT_NAME']
				);
			}
		}

		if(!empty($creatFlds))
		{
			$creatFlds['PARENT_ID'] = $parent;
			$creatFlds['TYPE_ID'] = $types[$type];

			$creatFlds['CODE'] = rand(999,99999999);

			if($sort !== false)
				$creatFlds['SORT'] = $sort;

			$res = Location\LocationTable::add($creatFlds);
			if(!$res->isSuccess())
				throw new Exception('Cannot add location');

			$parent = $res->getId();
			Location\LocationTable::update($parent, array('CODE' => $parent));
		}

		return $parent;
	}

	public static function Add($arFields)
	{
		global $DB;

		if (!CSaleLocation::CheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				$country = self::getTypeValueToStore('COUNTRY', $arFields);
				$region = self::getTypeValueToStore('REGION', $arFields);
				$city = self::getTypeValueToStore('CITY', $arFields);

				// Let`s treat a location 1.0 structure as a static structure where you can not move nodes up\down 
				// along a tree by passing just IDs in triplets like (COUNTRY_ID, REGION_ID, CITY_ID).
				// Then parse out some meaningless situations to preserve tree integrity:

				// you cann not add existing region to a non-existing country
				if($region && is_numeric($region) && $country && is_array($country))
					throw new Exception;

				// you cann not add existing city to a non-existing country
				if($city && is_numeric($city) && $country && is_array($country))
					throw new Exception;

				// you cann not add existing city to a non-existing region
				if($city && is_numeric($city) && $region && is_array($region))
					throw new Exception;

				$parent = 0;
				$sort = isset($arFields['SORT']) ? intval($arFields['SORT']) : false;

				if($country)
				{
					$parent = self::AddLocationPart($country, 'COUNTRY', $parent, $sort);
				}

				if($region)
				{
					$parent = self::AddLocationPart($region, 'REGION', $parent, $sort);
				}

				if($city)
				{
					$parent = self::AddLocationPart($city, 'CITY', $parent, $sort);
				}

				return $parent;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{

			if ((!is_set($arFields, "COUNTRY_ID") || intval($arFields["COUNTRY_ID"])<=0) && $arFields["COUNTRY_ID"] <> '')
			{
				$arFields["COUNTRY_ID"] = CSaleLocation::AddCountry($arFields["COUNTRY"]);
				if (intval($arFields["COUNTRY_ID"])<=0) return false;

				if ($arFields["WITHOUT_CITY"]!="Y" && $arFields["REGION_ID"] == '')
				{
					UnSet($arFields["CITY_ID"]);
					CSaleLocation::AddLocation($arFields);
				}
			}

			if ($arFields["REGION_ID"] <= 0 && $arFields["REGION_ID"] != "")
			{
				$arFields["REGION_ID"] = CSaleLocation::AddRegion($arFields["REGION"]);
				if (intval($arFields["REGION_ID"])<=0) return false;

				if ($arFields["WITHOUT_CITY"] != "Y")
				{
					//$arFieldsTmp = $arFields;
					UnSet($arFields["CITY_ID"]);
					CSaleLocation::AddLocation($arFields);
				}
			}
			elseif ($arFields["REGION_ID"] == '')
			{
				UnSet($arFields["REGION_ID"]);
			}

			if ($arFields["WITHOUT_CITY"]!="Y")
			{
				if (intval($arFields["REGION_ID"]) > 0)
					$arFields["CITY"]["REGION_ID"] = $arFields["REGION_ID"];
				$arFields["CITY_ID"] = CSaleLocation::AddCity($arFields["CITY"]);
				if (intval($arFields["CITY_ID"])<=0) return false;
			}
			else
			{
				UnSet($arFields["CITY_ID"]);
			}

			$ID = CSaleLocation::AddLocation($arFields);

			return $ID;

		}
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		if (!CSaleLocation::CheckFields("UPDATE", $arFields)) return false;

		if (!($arLocRes = CSaleLocation::GetByID($ID, LANGUAGE_ID))) return false;

		if(self::isLocationProMigrated())
		{
			try
			{
				// only partial support: name update functionality

				if(($arFields["CHANGE_COUNTRY"]=="Y" || intval($arFields["COUNTRY_ID"])) && is_array($arFields["COUNTRY"]))
					CSaleLocation::UpdateCountry($arFields["COUNTRY_ID"], $arFields["COUNTRY"]);

				if(intval($arFields["REGION_ID"]) && is_array($arFields["REGION"]))
					CSaleLocation::UpdateRegion($arFields["REGION_ID"], $arFields["REGION"]);

				if(intval($arFields["CITY_ID"]) && is_array($arFields["CITY"]))
					CSaleLocation::UpdateCity($arFields["CITY_ID"], $arFields["CITY"]);
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			if ((!is_set($arFields, "COUNTRY_ID") || intval($arFields["COUNTRY_ID"])<=0) && $arFields["COUNTRY_ID"] != "")
			{
				$arFields["COUNTRY_ID"] = CSaleLocation::AddCountry($arFields["COUNTRY"]);
				if (intval($arFields["COUNTRY_ID"])<=0) return false;

				UnSet($arFields["CITY_ID"]);
				UnSet($arFields["REGION_ID"]);
				CSaleLocation::AddLocation($arFields);
			}
			elseif ($arFields["CHANGE_COUNTRY"]=="Y" || $arFields["COUNTRY_ID"] == "")
			{
				CSaleLocation::UpdateCountry($arFields["COUNTRY_ID"], $arFields["COUNTRY"]);
			}

			//city
			if ($arFields["WITHOUT_CITY"]!="Y")
			{
				if (intval($arLocRes["CITY_ID"])>0)
				{
					CSaleLocation::UpdateCity(intval($arLocRes["CITY_ID"]), $arFields["CITY"]);
				}
				else
				{
					$arFields["CITY_ID"] = CSaleLocation::AddCity($arFields["CITY"]);
					if (intval($arFields["CITY_ID"])<=0) return false;
				}
			}
			else
			{
				CSaleLocation::DeleteCity($arLocRes["CITY_ID"]);
				$arFields["CITY_ID"] = false;
			}

			//region
			if (intval($arFields["REGION_ID"])>0)
			{
				CSaleLocation::UpdateRegion(intval($arLocRes["REGION_ID"]), $arFields["REGION"]);
			}
			elseif ($arFields["REGION_ID"] == 0 && $arFields["REGION_ID"] != '')
			{
				$db_res = CSaleLocation::GetRegionList(array("ID" => "DESC"), array("NAME" => $arFields["REGION"][LANGUAGE_ID]["NAME"]));
				$arRegion = $db_res->Fetch();

				if (count($arRegion) > 1)
					$arFields["REGION_ID"] = $arRegion["ID"];
				else
				{
					$arFields["REGION_ID"] = CSaleLocation::AddRegion($arFields["REGION"]);
					if (intval($arFields["REGION_ID"])<=0)
						return false;

					$arFieldsTmp = $arFields;
					UnSet($arFieldsTmp["CITY_ID"]);
					CSaleLocation::AddLocation($arFieldsTmp);
				}
			}
			elseif ($arFields["REGION_ID"] == '')
			{
				//CSaleLocation::DeleteRegion($arLocRes["REGION_ID"]);
				$arFields["REGION_ID"] = 0;
			}
			else
			{
				UnSet($arFields["REGION_ID"]);
			}

			CSaleLocation::UpdateLocation($ID, $arFields);

			return $ID;
		}
	}

	// ???
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		if (!($arLocRes = CSaleLocation::GetByID($ID, LANGUAGE_ID)))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeLocationDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		if (intval($arLocRes["CITY_ID"]) > 0)
			CSaleLocation::DeleteCity($arLocRes["CITY_ID"]);

		$bDelCountry = True;
		$db_res = CSaleLocation::GetList(
				array("SORT" => "ASC"),
				array("COUNTRY_ID" => $arLocRes["COUNTRY_ID"], "!ID"=>$ID),
				LANGUAGE_ID
			);
		if ($db_res->Fetch())
			$bDelCountry = false;

		if ($bDelCountry && intval($arLocRes["COUNTRY_ID"]) > 0)
			CSaleLocation::DeleteCountry($arLocRes["COUNTRY_ID"]);

		$bDelRegion = True;
		$db_res = CSaleLocation::GetList(
				array("SORT" => "ASC"),
				array("REGION_ID" => $arLocRes["REGION_ID"], "!ID"=>$ID),
				LANGUAGE_ID
			);
		if ($db_res->Fetch())
			$bDelRegion = false;

		if ($bDelRegion && intval($arLocRes["REGION_ID"]) > 0)
			CSaleLocation::DeleteRegion($arLocRes["REGION_ID"]);

		$DB->Query("DELETE FROM b_sale_location2location_group WHERE LOCATION_ID = ".$ID."", true);
		$DB->Query("DELETE FROM b_sale_delivery2location WHERE LOCATION_ID = ".$ID." AND LOCATION_TYPE = 'L'", true);
		$DB->Query("DELETE FROM b_sale_location_zip WHERE LOCATION_ID = ".$ID."", true);

		if(self::isLocationProMigrated())
		{
			try
			{
				$res = Location\LocationTable::delete($ID); // the whole subtree will be deleted
				if(!$res->isSuccess())
					return false;

				$bDelete = true;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			$bDelete = $DB->Query("DELETE FROM b_sale_location WHERE ID = ".$ID."", true);
		}

		foreach (GetModuleEvents("sale", "OnLocationDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return $bDelete;
	}

	public static function OnLangDelete($strLang)
	{
		global $DB;

		if(self::isLocationProMigrated())
		{
			$DB->Query("DELETE FROM ".Location\Name\LocationTable::getTableName()." WHERE LANGUAGE_ID = '".$DB->ForSql($strLang)."'", true);
			$DB->Query("DELETE FROM ".Location\Name\TypeTable::getTableName()." WHERE LANGUAGE_ID = '".$DB->ForSql($strLang)."'", true);
			$DB->Query("DELETE FROM ".Location\Name\GroupTable::getTableName()." WHERE LANGUAGE_ID = '".$DB->ForSql($strLang)."'", true);
		}

		$DB->Query("DELETE FROM b_sale_location_city_lang WHERE LID = '".$DB->ForSql($strLang)."'", true);
		$DB->Query("DELETE FROM b_sale_location_country_lang WHERE LID = '".$DB->ForSql($strLang)."'", true);

		return true;
	}

	public static function DeleteAll()
	{
		global $DB;

		foreach (GetModuleEvents("sale", "OnBeforeLocationDeleteAll", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent)===false)
				return false;

		if(self::isLocationProMigrated())
		{
			//main
			$DB->Query("DELETE FROM ".Location\LocationTable::getTableName());
			$DB->Query("DELETE FROM ".Location\GroupTable::getTableName());
			$DB->Query("DELETE FROM ".Location\TypeTable::getTableName());

			//names
			$DB->Query("DELETE FROM ".Location\Name\LocationTable::getTableName());
			$DB->Query("DELETE FROM ".Location\Name\GroupTable::getTableName());
			$DB->Query("DELETE FROM ".Location\Name\TypeTable::getTableName());

			//links
			$DB->Query("DELETE FROM ".Location\GroupLocationTable::getTableName());
			$DB->Query("DELETE FROM ".Location\SiteLocationTable::getTableName());
			$DB->Query("DELETE FROM ".Delivery\DeliveryLocationTable::getTableName());
			
			//other
			$DB->Query("DELETE FROM ".Location\DefaultSiteTable::getTableName());
			$DB->Query("DELETE FROM ".Location\ExternalTable::getTableName());
			$DB->Query("DELETE FROM ".Location\ExternalServiceTable::getTableName());
		}

		$DB->Query("DELETE FROM b_sale_location2location_group");
		$DB->Query("DELETE FROM b_sale_location_group_lang");
		$DB->Query("DELETE FROM b_sale_location_group");

		$DB->Query("DELETE FROM b_sale_delivery2location");
		$DB->Query("DELETE FROM b_sale_location");

		$DB->Query("DELETE FROM b_sale_location_city_lang");
		$DB->Query("DELETE FROM b_sale_location_city");

		$DB->Query("DELETE FROM b_sale_location_country_lang");
		$DB->Query("DELETE FROM b_sale_location_country");

		$DB->Query("DELETE FROM b_sale_location_region_lang");
		$DB->Query("DELETE FROM b_sale_location_region");

		$DB->Query("DELETE FROM b_sale_location_zip");

		foreach (GetModuleEvents("sale", "OnLocationDeleteAll", true) as $arEvent)
			ExecuteModuleEventEx($arEvent);

	}

	public static function GetLocationZIP($location)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				if(!intval($location))
					throw new Exception();

				return Location\ExternalTable::getList(array(
					'filter' => array(
						'=SERVICE.CODE' => self::ZIP_EXT_SERVICE_CODE,
						'=LOCATION_ID' => $location
					),
					'select' => array(
						'ID',
						'ZIP' => 'XML_ID'
					)
				));
			}
			catch(Exception $e)
			{
				return new DB\ArrayResult(array());
			}
		}
		else
		{
			global $DB;

			return $DB->Query("SELECT ZIP FROM b_sale_location_zip WHERE LOCATION_ID='".$DB->ForSql($location)."'");
		}
	}

	public static function GetByZIP($zip)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				if($zip == '')
					throw new Exception();

				$res = Location\ExternalTable::getList(array(
					'filter' => array(
						'=SERVICE.CODE' => self::ZIP_EXT_SERVICE_CODE,
						'=XML_ID' => $zip
					),
					'select' => array(
						'LOCATION_ID',
					),
					'limit' => 1
				));

				if($item = $res->fetch())
					return self::GetByID($item['LOCATION_ID']);
				else
					return false;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		else
		{
			global $DB;

			$dbRes = $DB->Query('SELECT LOCATION_ID FROM b_sale_location_zip WHERE ZIP=\''.$DB->ForSql($zip).'\'');
			if ($arRes = $dbRes->Fetch())
				return CSaleLocation::GetByID($arRes['LOCATION_ID']);
			else
				return false;
		}
	}

	public static function ClearLocationZIP($location)
	{
		global $DB;

		if(self::isLocationProMigrated())
		{
			$DB->Query("DELETE FROM ".Location\ExternalTable::getTableName()." WHERE LOCATION_ID='".$DB->ForSql($location)."'");
		}
		else
		{
			$query = "DELETE FROM b_sale_location_zip WHERE LOCATION_ID='".$DB->ForSql($location)."'";
			$DB->Query($query);

			return;
		}
	}

	public static function ClearAllLocationZIP()
	{
		global $DB;

		if(self::isLocationProMigrated())
		{
			$DB->Query("DELETE FROM ".Location\ExternalTable::getTableName());
		}
		else
		{
			$DB->Query("DELETE FROM b_sale_location_zip");
		}
	}

	public static function AddLocationZIP($location, $ZIP, $bSync = false)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				if($bSync)
				{
					$res = Location\ExternalTable::getList(array(
						'filter' => array(
							'=SERVICE.CODE' => self::ZIP_EXT_SERVICE_CODE,
							'=XML_ID' => $ZIP,
							'=LOCATION_ID' => $location
						),
						'select' => array(
							'ID',
						),
						'limit' => 1
					))->fetch();

					if($res)
					{
						if(!Location\ExternalTable::update($res['ID'], array(
							'LOCATION_ID' => $location,
							'XML_ID' => $ZIP
						))->isSuccess())
						{
							$bSync = false;
						}
					}
				}

				if(!$bSync)
				{
					$zipId = self::getZipId();
					if($zipId)
					{
						Location\ExternalTable::add(array(
							'LOCATION_ID' => $location,
							'XML_ID' => $ZIP,
							'SERVICE_ID' => $zipId
						));
					}
				}
			}
			catch(Exception $e)
			{
				return new DB\ArrayResult(array());
			}
		}
		else
		{
			global $DB;

			$arInsert = array(
				"LOCATION_ID" => intval($location),
				"ZIP" => intval($ZIP),
			);

			if ($bSync)
			{
				$cnt = $DB->Update(
					'b_sale_location_zip',
					$arInsert,
					"WHERE LOCATION_ID='".$arInsert["LOCATION_ID"]."' AND ZIP='".$arInsert["ZIP"]."'"
				);

				if ($cnt <= 0)
				{
					$bSync = false;
				}
			}

			if (!$bSync)
			{
				$DB->Insert('b_sale_location_zip', $arInsert);
			}

			return;
		}
	}

	public static function SetLocationZIP($location, $arZipList)
	{
		global $DB;

		if (is_array($arZipList))
		{
			CSaleLocation::ClearLocationZIP($location);

			$arInsert = array(
				"LOCATION_ID" => "'".$DB->ForSql($location)."'",
				"ZIP" => '',
			);

			foreach ($arZipList as $ZIP)
			{
				if ($ZIP <> '')
					self::AddLocationZIP($location, $ZIP);
			}
		}

		return;
	}

	public static function GetRegionsIdsByNames($arRegNames, $countryId = false)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				$types = self::getTypes();
				$query = new Entity\Query(self::SELF_ENTITY_NAME);

				$fieldMap = array(

					'RID' => 'REGION_ID',

					'RNAME' => 'NAME.NAME',
					'RSHORT_NAME' => 'NAME.SHORT_NAME'
				);

				$selectFields = $fieldMap;
				$filterFields = array(
					array(
						'LOGIC' => 'OR',
						'RNAME' => $arRegNames,
						'RSHORT_NAME' => $arRegNames,
					),
					'=TYPE_ID' => $types['REGION'],
					'!=REGION_ID' => '0'
				);
				
				if($countryId = intval($countryId))
					$filterFields['=COUNTRY_ID'] = $countryId;

				// order
				$orderFields = array(
					'RNAME' => 'asc',
					'RSHORT_NAME' => 'asc'
				);

				// group
				$groupFields = array(
					'RID'
				);

				$nameJoinCondition = array(
					'=this.ID' => 'ref.LOCATION_ID',
				);
				if($strLang <> '')
				{
					$nameJoinCondition['=ref.LANGUAGE_ID'] = array('?', $strLang);
				}

				$query->registerRuntimeField(
					'NAME',
					array(
						'data_type' => self::NAME_ENTITY_NAME,
						'reference' => $nameJoinCondition,
						'join_type' => 'left'
					)
				);

				$query->setSelect($selectFields);
				$query->setFilter($filterFields);
				$query->setOrder($orderFields);
				$query->setGroup($groupFields);

				$result = array();

				$res = $query->exec();
				while($item = $res->fetch())
				{
					$result[$item['RNAME'] <> ''? $item['RNAME'] : $item['RSHORT_NAME']] = $item['RID'];
				}

				return $result;
			}
			catch(Exception $e)
			{
				return array();
			}
		}
		else
		{
			global $DB;
			$arResult = array();
			$arWhere = array();
			$arQueryFields = array('RL.NAME', 'RL.SHORT_NAME');

			if(is_array($arRegNames))
			{
				foreach ($arRegNames as $regName)
				{
					$regName = $DB->ForSql($regName);
					foreach ($arQueryFields as $field)
						$arWhere[] = $field." LIKE '".$regName."'";
				}

				if (count($arWhere) > 0)
				{
					$strWhere = implode(' OR ', $arWhere);

					$query = "	SELECT RL.REGION_ID, RL.NAME, RL.SHORT_NAME
								FROM b_sale_location_region_lang RL ";

					if ($countryId)
					{
						$strWhere = 'L.COUNTRY_ID=\''.intval($countryId).'\' AND ('.$strWhere.')';
						$query .= "LEFT JOIN b_sale_location L ON L.REGION_ID=RL.REGION_ID ";
					}

					$query .= "WHERE ".$strWhere;
					$query .= " GROUP BY RL.REGION_ID";
					$query .= " ORDER BY RL.NAME, RL.SHORT_NAME";

					$dbList = $DB->Query($query);

					$arRegionsLang = array();

					while($arRegion = $dbList->Fetch())
					{
						if($arRegion["NAME"] <> '')
							$idx = $arRegion["NAME"];
						else
							$idx = $arRegion["SHORT_NAME"];

						$arResult[$idx] = $arRegion["REGION_ID"];
					}
				}
			}

			return $arResult;

		}
	}

	public static function GetRegionsNamesByIds($arIds, $lang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
		{
			try
			{
				if(!is_array($arIds) || empty($arIds))
					throw new Exception();

				$arIds = array_unique($arIds);
				$parsedList = array();
				foreach($arIds as $id)
				{
					if(intval($id))
						$parsedList[] = intval($id);
				}

				if($lang == '')
					$lang = LANGUAGE_ID;

				$arResult = array();
				if(!empty($parsedList))
				{
					$res = self::GetLocationTypeList(
						'REGION',
						array('NAME' => 'asc', 'SHORT_NAME' => 'asc'),
						array('ID' => $parsedList),
						$lang
					);

					while($arRegion = $res->fetch())
					{
						$arResult[$arRegion["ID"]] = $arRegion["NAME"] <> '' ? $arRegion["NAME"] : $arRegion["SHORT_NAME"];
					}
				}

				return $arResult;
			}
			catch(Exception $e)
			{
				return array();
			}
		}
		else
		{
			global $DB;
			$arResult = array();
			$arWhere = array();

			if ('' == $lang)
				$lang = LANGUAGE_ID;

			if(!empty($arIds) && is_array($arIds))
			{
				foreach ($arIds as $id)
				{
					if(intval($id) > 0)
						$arWhere[] = intval($id);
				}

				if (!empty($arWhere))
				{
					$query = "select RL.REGION_ID, RL.NAME, RL.SHORT_NAME from b_sale_location_region_lang RL";
					$query .= " where REGION_ID IN(".implode(',', $arWhere).") and RL.LID='".$DB->ForSql($lang, 2)."'";
					$query .= " order by RL.NAME, RL.SHORT_NAME";

					$dbList = $DB->Query($query);

					while($arRegion = $dbList->Fetch())
						$arResult[$arRegion["REGION_ID"]] = $arRegion["NAME"] <> '' ? $arRegion["NAME"] : $arRegion["SHORT_NAME"];
				}
			}

			return $arResult;
		}
	}

	// location import is overwritten, and it is enabled when self::isLocationProMigrated() == true, so no proxy provided for the obsolete methods below

	public static function _GetZIPImportStats()
	{
		global $DB;

		$query = "SELECT COUNT(*) AS CNT, COUNT(DISTINCT LOCATION_ID) AS CITY_CNT FROM b_sale_location_zip";
		$rsStats = $DB->Query($query);
		$arStat = $rsStats->Fetch();

		return $arStat;
	}

	public static function _GetCityImport($arCityName, $country_id = false)
	{
		global $DB;

		$arQueryFields = array('LCL.NAME', 'LCL.SHORT_NAME');

		$arWhere = array();
		foreach ($arCityName as $city_name)
		{
			$city_name = $DB->ForSql($city_name);
			foreach ($arQueryFields as $field)
			{
				if ($field <> '')
					$arWhere[] = $field."='".$city_name."'";
			}
		}

		if (count($arWhere) <= 0) return false;
		$strWhere = implode(' OR ', $arWhere);

		if ($country_id)
		{
			$strWhere = 'L.COUNTRY_ID=\''.intval($country_id).'\' AND ('.$strWhere.')';
		}

		$query = "
SELECT L.ID, L.CITY_ID
FROM b_sale_location L
LEFT JOIN b_sale_location_city_lang LCL ON L.CITY_ID=LCL.CITY_ID
WHERE ".$strWhere;

		$dbList = $DB->Query($query);

		if ($arCity = $dbList->Fetch())
			return $arCity;
		else
			return false;
	}

	protected static function AddLocationUnattached($typeCode, $names = array())
	{
		static $types;

		if($types == null)
		{
			$types = self::getTypes();
		}

		if(!intval($types[$typeCode])) // no such type
		{
			return false;
		}

		foreach (GetModuleEvents('sale', 'OnBefore'.ucfirst(ToLower($typeCode)).'Add', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($names))===false)
			{
				return false;
			}
		}

		if(!is_array($names))
		{
			$names = array();
		}

		$name = $names['NAME'];
		$shortName = $names['SHORT_NAME'];

		unset($names['NAME']);
		unset($names['SHORT_NAME']);

		$fields = array();
		foreach($names as $lang => $n)
		{
			$fields['NAME'][$n['LID']] = array(
				'NAME' => $n['NAME'],
				'SHORT_NAME' => $n['SHORT_NAME']
			);
		}

		if(!isset($fields['NAME']['en']))
		{
			$fields['NAME']['en'] = array(
				'NAME' => $name,
				'SHORT_NAME' => $shortName
			);
		}

		$fields['CODE'] = 'randstr'.rand(999, 99999).rand(999, 99999).rand(999, 99999);
		$fields['TYPE_ID'] = $types[$typeCode];
		$fields['PARENT_ID'] = 0;

		$id = false;
		$res = \Bitrix\Sale\Location\LocationTable::add($fields);
		if($res->isSuccess())
		{
			$id = $res->getId();
			$uRes = \Bitrix\Sale\Location\LocationTable::update($id, array('CODE' => $id));

			foreach (GetModuleEvents('sale', 'On'.ucfirst(ToLower($typeCode)).'Add', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($id, $names));
			}
		}

		return $id;
	}

	protected static function RebindLocationTriplet($fields = array())
	{
		$country = intval($fields['COUNTRY_ID']);
		$region = intval($fields['REGION_ID']);
		$city = intval($fields['CITY_ID']);

		foreach (GetModuleEvents('sale', 'OnBeforeLocationAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($fields))===false)
			{
				return false;
			}
		}

		if($region && $country) // set country as PARENT_ID for region
		{
			$uRes = \Bitrix\Sale\Location\LocationTable::update($region, array('PARENT_ID' => $country));
		}
		if($city)
		{
			if($region) // set region as PARENT_ID for city
			{
				$uRes = \Bitrix\Sale\Location\LocationTable::update($city, array('PARENT_ID' => $region));
			}
			elseif($country) // set country as PARENT_ID for city
			{
				$uRes = \Bitrix\Sale\Location\LocationTable::update($city, array('PARENT_ID' => $country));
			}
		}

		if(intval($fields['SORT']))
		{
			$loc2Update = $city ? $city : ($region ? $region : ($country ? $country : false));
			if($loc2Update)
			{
				$uRes = \Bitrix\Sale\Location\LocationTable::update($loc2Update, array('SORT' => $fields['SORT']));
			}
		}

		foreach (GetModuleEvents('sale', 'OnLocationAdd', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($loc2Update, $fields));
		}

		return $loc2Update;
	}
}
