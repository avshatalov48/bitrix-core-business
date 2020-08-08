<?php

use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

if(!defined("CACHED_b_iblock_type")) define("CACHED_b_iblock_type", 36000);
if(!defined("CACHED_b_iblock")) define("CACHED_b_iblock", 36000);
if(!defined("CACHED_b_iblock_bucket_size")) define("CACHED_b_iblock_bucket_size", 20);
if(!defined("CACHED_b_iblock_property_enum")) define("CACHED_b_iblock_property_enum", 36000);
if(!defined("CACHED_b_iblock_property_enum_bucket_size")) define("CACHED_b_iblock_property_enum_bucket_size", 100);

/** @global string $DBType */
global $DBType;

$arClasses = array(
	"iblock" => "install/index.php",
	"CIBlockPropertyResult" => "classes/general/iblockpropresult.php",
	"CIBlockResult" => "classes/general/iblockresult.php",
	"_CIBElement" => "classes/general/iblock_element.php",
	"CIBlockType" => "classes/general/iblocktype.php",
	"CAllIBlock" => "classes/general/iblock.php",
	"CIBlock" => "classes/".$DBType."/iblock.php",
	"CAllIBlockSection" => "classes/general/iblocksection.php",
	"CIBlockSection" => "classes/".$DBType."/iblocksection.php",
	"CAllIBlockProperty" => "classes/general/iblockproperty.php",
	"CIBlockPropertyEnum" => "classes/general/iblockpropertyenum.php",
	"CIBlockProperty" => "classes/".$DBType."/iblockproperty.php",
	"CAllIBlockElement" => "classes/general/iblockelement.php",
	"CIBlockElement" => "classes/".$DBType."/iblockelement.php",
	"CAllIBlockRSS" => "classes/general/iblockrss.php",
	"CIBlockRSS" => "classes/".$DBType."/iblockrss.php",
	"CIBlockPropertyDateTime" => "classes/general/prop_datetime.php",
	"CIBlockPropertyDate" => "classes/general/prop_date.php",
	"CIBlockPropertyXmlID" => "classes/general/prop_xmlid.php",
	"CIBlockPropertyFileMan" => "classes/general/prop_fileman.php",
	"CIBlockPropertyHTML" => "classes/general/prop_html.php",
	"CIBlockPropertyElementList" => "classes/general/prop_element_list.php",
	"CCMLResult" => "classes/general/cmlresult.php",
	"CIBlockXMLFile" => "classes/".$DBType."/cml2.php",
	"CIBlockCMLImport" => "classes/general/cml2.php",
	"CIBlockCMLExport" => "classes/general/cml2.php",
	"CIBlockFindTools" => "classes/general/comp_findtools.php",
	"CIBlockPriceTools" => "classes/general/comp_pricetools.php",
	"CIBlockParameters" => "classes/general/comp_parameters.php",
	"CIBlockFormatProperties" => "classes/general/comp_formatprops.php",
	"CIBlockSequence" => "classes/".$DBType."/iblocksequence.php",
	"CIBlockPropertySequence" => "classes/general/prop_seq.php",
	"CIBlockPropertyElementAutoComplete" => "classes/general/prop_element_auto.php",
	"CIBlockPropertySKU" => "classes/general/prop_element_sku.php",
	"CIBlockPropertySectionAutoComplete" => "classes/general/prop_section_auto.php",
	"CAllIBlockOffersTmp" => "classes/general/iblockoffers.php",
	"CIBlockOffersTmp" => "classes/".$DBType."/iblockoffers.php",
	"CEventIblock" => "classes/general/iblock_event_list.php",
	"CRatingsComponentsIBlock" => "classes/general/ratings_components.php",
	"CIBlockRights" => "classes/general/iblock_rights.php",
	"CIBlockSectionRights" => "classes/general/iblock_rights.php",
	"CIBlockElementRights" => "classes/general/iblock_rights.php",
	"CIBlockRightsStorage" => "classes/general/iblock_rights.php",
	"CIBlockPropertyTools" => "classes/general/iblockproptools.php",
	"CIBlockSectionPropertyLink" => "classes/general/section_property.php",
	"CIBlockXmlImport" => "classes/general/iblockxmlimport.php",
	'\Bitrix\Iblock\IblockFieldTable' => "lib/iblockfield.php",
	'\Bitrix\Iblock\IblockGroupTable' => "lib/iblockgroup.php",
	'\Bitrix\Iblock\IblockMessageTable' => "lib/iblockmessage.php",
	'\Bitrix\Iblock\IblockRssTable' => "lib/iblockrss.php",
	'\Bitrix\Iblock\IblockSiteTable' => "lib/iblocksite.php",
	'\Bitrix\Iblock\InheritedPropertyTable' => "lib/inheritedproperty.php",
	'\Bitrix\Iblock\PropertyEnumerationTable' => "lib/propertyenumeration.php",
	'\Bitrix\Iblock\PropertyFeatureTable' => 'lib/propertyfeature.php',
	'\Bitrix\Iblock\SequenceTable' => "lib/sequence.php",
	'\Bitrix\Iblock\SiteTable' => "lib/site.php",
	'\Bitrix\Iblock\TypeTable' => "lib/type.php",
	'\Bitrix\Iblock\TypeLanguageTable' => "lib/typelanguage.php",
	'\Bitrix\Iblock\BizprocType\UserTypeProperty' => "lib/bizproctype/usertypeproperty.php",
	'\Bitrix\Iblock\BizprocType\ECrm' => "lib/bizproctype/ecrm.php",
	'\Bitrix\Iblock\BizprocType\Money' => "lib/bizproctype/money.php",
	'\Bitrix\Iblock\BizprocType\UserTypePropertyDiskFile' => "lib/bizproctype/usertypepropertydiskfile.php",
	'\Bitrix\Iblock\BizprocType\UserTypePropertyElist' => "lib/bizproctype/usertypepropertyelist.php",
	'\Bitrix\Iblock\BizprocType\UserTypePropertyEmployee' => "lib/bizproctype/usertypepropertyemployee.php",
	'\Bitrix\Iblock\BizprocType\UserTypePropertyHtml' => "lib/bizproctype/usertypepropertyhtml.php",
	'\Bitrix\Iblock\Component\Base' => "lib/component/base.php",
	'\Bitrix\Iblock\Component\Element' => "lib/component/element.php",
	'\Bitrix\Iblock\Component\ElementList' => "lib/component/elementlist.php",
	'\Bitrix\Iblock\Component\Filters' => "lib/component/filters.php",
	'\Bitrix\Iblock\Component\Selector\Element' => "lib/component/selector/element.php",
	'\Bitrix\Iblock\Component\Selector\Entity' => "lib/component/selector/entity.php",
	'\Bitrix\Iblock\Component\Tools' => "lib/component/tools.php",
	'\Bitrix\Iblock\Grid\Panel\GroupAction' => "lib/grid/panel/groupaction.php",
	'\Bitrix\Iblock\Grid\ActionType' => "lib/grid/actiontype.php",
	'\Bitrix\Iblock\Helpers\Admin\Property' => "lib/helpers/admin/property.php",
	'\Bitrix\Iblock\Helpers\Filter\Property' => "lib/helpers/filter/property.php",
	'\Bitrix\Iblock\Helpers\Filter\PropertyManager' => "lib/helpers/filter/propertymanager.php",
	'\Bitrix\Iblock\InheritedProperty\BaseTemplate' => "lib/inheritedproperty/basetemplate.php",
	'\Bitrix\Iblock\InheritedProperty\BaseValues' => "lib/inheritedproperty/basevalues.php",
	'\Bitrix\Iblock\InheritedProperty\ElementTemplates' => "lib/inheritedproperty/elementtemplates.php",
	'\Bitrix\Iblock\InheritedProperty\ElementValues' => "lib/inheritedproperty/elementvalues.php",
	'\Bitrix\Iblock\InheritedProperty\IblockTemplates' => "lib/inheritedproperty/iblocktemplates.php",
	'\Bitrix\Iblock\InheritedProperty\IblockValues' => "lib/inheritedproperty/iblockvalues.php",
	'\Bitrix\Iblock\InheritedProperty\SectionTemplates' => "lib/inheritedproperty/sectiontemplates.php",
	'\Bitrix\Iblock\InheritedProperty\SectionValues' => "lib/inheritedproperty/sectionvalues.php",
	'\Bitrix\Iblock\InheritedProperty\ValuesQueue' => "lib/inheritedproperty/valuesqueue.php",
	'\Bitrix\Iblock\LandingSource\DataLoader' => "lib/landingsource/dataloader.php",
	'\Bitrix\Iblock\LandingSource\Element' => "lib/landingsource/element.php",
	'\Bitrix\Iblock\Model\PropertyFeature' => "lib/model/propertyfeature.php",
	'\Bitrix\Iblock\Model\Section' => "lib/model/section.php",
	'\Bitrix\Iblock\PropertyIndex\Dictionary' => "lib/propertyindex/dictionary.php",
	'\Bitrix\Iblock\PropertyIndex\Element' => "lib/propertyindex/element.php",
	'\Bitrix\Iblock\PropertyIndex\Facet' => "lib/propertyindex/facet.php",
	'\Bitrix\Iblock\PropertyIndex\Indexer' => "lib/propertyindex/indexer.php",
	'\Bitrix\Iblock\PropertyIndex\Manager' => "lib/propertyindex/manager.php",
	'\Bitrix\Iblock\PropertyIndex\QueryBuilder' => "lib/propertyindex/querybuilder.php",
	'\Bitrix\Iblock\PropertyIndex\Storage' => "lib/propertyindex/storage.php",
	'\Bitrix\Iblock\Template\Engine' => "lib/template/engine.php",
	'\Bitrix\Iblock\Template\NodeRoot' => "lib/template/engine.php",
	'\Bitrix\Iblock\Template\NodeText' => "lib/template/engine.php",
	'\Bitrix\Iblock\Template\NodeEntityField' => "lib/template/engine.php",
	'\Bitrix\Iblock\Template\NodeFunction' => "lib/template/engine.php",
	'\Bitrix\Iblock\Template\Helper' => "lib/template/helper.php",
	'\Bitrix\Iblock\Template\Entity\Base' => "lib/template/entity/base.php",
	'\Bitrix\Iblock\Template\Entity\LazyValueLoader' => "lib/template/entity/base.php",
	'\Bitrix\Iblock\Template\Entity\CatalogStore' => "lib/template/entity/catalogstore.php",
	'\Bitrix\Iblock\Template\Entity\Element' => "lib/template/entity/element.php",
	'\Bitrix\Iblock\Template\Entity\ElementCatalog' => "lib/template/entity/elementcatalog.php",
	'\Bitrix\Iblock\Template\Entity\ElementCatalogMeasure' => "lib/template/entity/elementcatalog.php",
	'\Bitrix\Iblock\Template\Entity\ElementCatalogStoreList' => "lib/template/entity/elementcatalog.php",
	'\Bitrix\Iblock\Template\Entity\ElementPrice' => "lib/template/entity/elementprice.php",
	'\Bitrix\Iblock\Template\Entity\ElementProperty' => "lib/template/entity/elementproperty.php",
	'\Bitrix\Iblock\Template\Entity\ElementPropertyUserField' => "lib/template/entity/elementproperty.php",
	'\Bitrix\Iblock\Template\Entity\ElementPropertyEnum' => "lib/template/entity/elementproperty.php",
	'\Bitrix\Iblock\Template\Entity\ElementPropertyElement' => "lib/template/entity/elementproperty.php",
	'\Bitrix\Iblock\Template\Entity\ElementPropertySection' => "lib/template/entity/elementproperty.php",
	'\Bitrix\Iblock\Template\Entity\ElementSku' => "lib/template/entity/elementsku.php",
	'\Bitrix\Iblock\Template\Entity\ElementSkuPrice' => "lib/template/entity/elementskuprice.php",
	'\Bitrix\Iblock\Template\Entity\ElementSkuProperty' => "lib/template/entity/elementskuproperty.php",
	'\Bitrix\Iblock\Template\Entity\Iblock' => "lib/template/entity/iblock.php",
	'\Bitrix\Iblock\Template\Entity\Section' => "lib/template/entity/section.php",
	'\Bitrix\Iblock\Template\Entity\SectionPath' => "lib/template/entity/sectionpath.php",
	'\Bitrix\Iblock\Template\Entity\SectionProperty' => "lib/template/entity/sectionproperty.php",
	'\Bitrix\Iblock\Template\Functions\Fabric' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionBase' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionUpper' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionLower' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionTranslit' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionConcat' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionLimit' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionContrast' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionMin' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionMax' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Template\Functions\FunctionDistinct' => "lib/template/functions/fabric.php",
	'\Bitrix\Iblock\Update\AdminFilterOption' => 'lib/update/adminfilteroption.php',
	'\Bitrix\Iblock\Update\AdminGridOption' => 'lib/update/admingridoption.php',
	'\Bitrix\Iblock\Url\AdminPage\BaseBuilder' => 'lib/url/adminpage/basebuilder.php',
	'\Bitrix\Iblock\Url\AdminPage\BuilderManager' => 'lib/url/adminpage/buildermanager.php',
	'\Bitrix\Iblock\Url\AdminPage\IblockBuilder' => 'lib/url/adminpage/iblockbuilder.php',
	'\Bitrix\Iblock\SenderEventHandler' => "lib/senderconnector.php",
	'\Bitrix\Iblock\SenderConnectorIblock' => "lib/senderconnector.php",
);
if (\Bitrix\Main\ModuleManager::isModuleInstalled('bizproc'))
	$arClasses["CIBlockDocument"] = "classes/general/iblockdocument.php";

Loader::registerAutoLoadClasses("iblock", $arClasses);

// orm autoloader
Loader::registerHandler([\Bitrix\Iblock\ORM\Loader::class, 'autoLoad']);

/**
 * Returns list of the information blocks of specified $type linked to the current site
 * including ELEMENT_CNT column which presents currently active elements.
 *
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which blocks will be returned.
 * @param int $cnt Maximum count of iblocks to be returned.
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockListWithCnt($type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort" => "asc"), $cnt = 0)
{
	return GetIBlockListLang(SITE_ID, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, true);
}
/**
 * Returns list of the information blocks of specified $type linked to the current site
 *
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which blocks will be returned.
 * @param int $cnt Maximum count of iblocks to be returned.
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockList($type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort" => "asc"), $cnt = 0)
{
	return GetIBlockListLang(SITE_ID, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt);
}
/**
 * Returns list of the information blocks of specified $type linked to the specified site
 *
 * @param string $lang Site identifier blocks linked to.
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which blocks will be returned.
 * @param int $cnt Maximum count of iblocks to be returned.
 * @param bool $bCountActive
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockListLang($lang, $type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("SORT" => "ASC"), $cnt = 0, $bCountActive = false)
{
	$arIDsInc = array();
	$arCODEsInc = array();
	if (is_array($arTypesInc))
	{
		foreach ($arTypesInc as $i)
		{
			if (intval($i) > 0)
				$arIDsInc[] = $i;
			else
				$arCODEsInc[] = $i;
		}
	}
	elseif (intval($arTypesInc) > 0)
	{
		$arIDsInc[] = $arTypesInc;
	}
	else
	{
		$arCODEsInc[] = $arTypesInc;
	}

	$arIDsExc = array();
	$arCODEsExc = array();
	if (is_array($arTypesExc))
	{
		foreach ($arTypesExc as $i)
		{
			if (intval($i) > 0)
				$arIDsExc[] = $i;
			else
				$arCODEsExc[] = $i;
		}
	}
	elseif (intval($arTypesExc) > 0)
	{
		$arIDsExc[] = $arTypesExc;
	}
	else
	{
		$arCODEsExc[] = $arTypesExc;
	}

	$res = CIBlock::GetList($arOrder, array(
		"type" => $type,
		"LID" => $lang,
		"ACTIVE" => "Y",
		"CNT_ACTIVE" => $bCountActive? "Y": "N",
		"ID" => $arIDsInc,
		"CODE" => $arCODEsInc,
		"!ID" => $arIDsExc,
		"!CODE" => $arCODEsExc,
	), $bCountActive);

	$dbr = new CIBlockResult($res);
	if ($cnt > 0)
		$dbr->NavStart($cnt);

	return $dbr;
}
/**
 * Returns an array with Information block fields or false if none found.
 * iblock have to be linked to the current site.
 *
 * @param int $ID Numeric identifier of the iblock
 * @param string $type Type of iblock restrict search to.
 *
 * @return array
 */
function GetIBlock($ID, $type = "")
{
	return GetIBlockLang(SITE_ID, $ID, $type);
}
/**
 * Returns an array with Information block fields or false if none found.
 * iblock have to be linked to the current site.
 *
 * @param string $lang Site identifier block linked to.
 * @param int $ID Numeric identifier of the iblock
 * @param string $type Type of iblock restrict search to.
 *
 * @return array
 */
function GetIBlockLang($lang, $ID, $type="")
{
	$res = CIBlock::GetList(array(), array(
		"ID" => intval($ID),
		"TYPE" => $type,
		"LID" => $lang,
		"ACTIVE" => "Y",
	));
	if ($res)
	{
		$res = new CIBlockResult($res);
		return $res->GetNext();
	}
	else
	{
		return false;
	}
}
/**
 * Returns a list of the currently active elements of specified information blocks.
 * Checks permissions by default.
 *
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which elements will be returned.
 * @param int $cnt Maximum count of elements to be returned.
 * @param array $arFilter Filter to be applied
 * @param array $arSelect Fields to return (all if empty or not supplied)
 * @param bool $arGroupBy Fields to group by (none grouping by default), overwrites $arSelect
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementListEx($type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort"=>"asc"), $cnt = 0, $arFilter = array(), $arSelect = array(), $arGroupBy = false)
{
	return GetIBlockElementListExLang(SITE_ID, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter, $arSelect, $arGroupBy);
}
/**
 * Returns count of the currently active elements of specified information blocks.
 * Checks permissions by default.
 *
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which elements will be returned.
 * @param int $cnt Maximum count of elements to be returned.
 * @param array $arFilter Filter to be applied
 *
 * @return int
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementCountEx($type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort"=>"asc"), $cnt = 0, $arFilter = array())
{
	return GetIBlockElementListExLang(SITE_ID, $type, $arTypesInc, $arTypesExc, $arOrder, 0, $arFilter, false, array());
}
/**
 * Returns count of the currently active elements of specified information blocks.
 * Checks permissions by default.
 *
 * @param string $lang Site identifier blocks linked to.
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which elements will be returned.
 * @param int $cnt Maximum count of elements to be returned.
 * @param array $arFilter Filter to be applied
 *
 * @return int
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementCountExLang($lang, $type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort"=>"asc"), $cnt = 0, $arFilter = array())
{
	return GetIBlockElementListExLang($lang, $type, $arTypesInc, $arTypesExc, $arOrder, 0, $arFilter, false, array());
}
/**
 * Returns a list of the currently active elements of specified information blocks.
 * Checks permissions by default.
 *
 * @param string $lang Site identifier blocks linked to.
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which elements will be returned.
 * @param int $cnt Maximum count of elements to be returned.
 * @param array $arFilter Filter to be applied
 * @param array $arSelect Fields to return (all if empty or not supplied)
 * @param bool $arGroupBy Fields to group by (none grouping by default), overwrites $arSelect
 *
 * @return CIBlockResult|int
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementListExLang($lang, $type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort"=>"asc"), $cnt = 0, $arFilter = array(), $arSelect = array(), $arGroupBy = false)
{
	$filter = _GetIBlockElementListExLang_tmp($lang, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter);

	if(is_array($cnt))
		$arNavParams = $cnt; //array("nPageSize"=>$cnt, "bShowAll"=>false);
	elseif($cnt > 0)
		$arNavParams = array("nTopCount"=>$cnt);
	else
		$arNavParams = false;

	return CIBlockElement::GetList($arOrder, $filter, $arGroupBy, $arNavParams, $arSelect);
}
/**
 * Makes filter for CIBlockElement::GetList. Internal function
 *
 * @param string $lang Site identifier blocks linked to.
 * @param string $type Information blocks type to get blocks from.
 * @param array|string|int $arTypesInc Information block ID or CODE or array of IDs or CODEs to get.
 * @param array|string|int $arTypesExc Information block ID or CODE or array of IDs or CODEs to exclude.
 * @param array $arOrder Order in which elements will be returned.
 * @param int $cnt Maximum count of elements to be returned.
 * @param array $arFilter Filter to be applied
 * @param array $arSelect Fields to return (all if empty or not supplied)
 *
 * @return array
 * @deprecated No longer used by internal code and not recommended.
 */
function _GetIBlockElementListExLang_tmp($lang, $type, $arTypesInc = array(), $arTypesExc = array(), $arOrder = array("sort" => "asc"), $cnt = 0, $arFilter = array(), $arSelect = array())
{
	$arIDsInc = array();
	$arCODEsInc = array();
	if (is_array($arTypesInc))
	{
		foreach ($arTypesInc as $i)
		{
			if (intval($i) > 0)
				$arIDsInc[] = $i;
			else
				$arCODEsInc[] = $i;
		}
	}
	elseif (intval($arTypesInc) > 0)
	{
		$arIDsInc[] = $arTypesInc;
	}
	elseif ($arTypesInc !== false)
	{
		$arCODEsInc[] = $arTypesInc;
	}

	$arIDsExc = array();
	$arCODEsExc = array();
	if (is_array($arTypesExc))
	{
		foreach ($arTypesExc as $i)
		{
			if (intval($i) > 0)
				$arIDsExc[] = $i;
			else
				$arCODEsExc[] = $i;
		}
	}
	elseif (intval($arTypesExc) > 0)
	{
		$arIDsExc[] = $arTypesExc;
	}
	elseif ($arTypesInc !== false)
	{
		$arCODEsExc[] = $arTypesExc;
	}

	$filter = array(
		"IBLOCK_ID" => $arIDsInc,
		"IBLOCK_LID" => $lang,
		"IBLOCK_ACTIVE" => "Y",
		"IBLOCK_CODE" => $arCODEsInc,
		"!IBLOCK_ID" => $arIDsExc,
		"!IBLOCK_CODE" => $arCODEsExc,
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	if ($type != false && $type <> '')
		$filter["IBLOCK_TYPE"] = $type;

	if (is_array($arFilter) && count($arFilter) > 0)
		$filter = array_merge($filter, $arFilter);

	return $filter;
}
/**
 * Returns number of active elements for given iblock.
 *
 * @param int $IBLOCK Information block ID.
 * @param bool $SECTION_ID Section ID.
 * @param array $arOrder Has no meaning here.
 * @param int $cnt Not used.
 *
 * @return int
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementCount($IBLOCK, $SECTION_ID = false, $arOrder = array("sort"=>"asc"), $cnt = 0)
{
	$filter = array(
		"IBLOCK_ID" => intval($IBLOCK),
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	if ($SECTION_ID !== false)
		$filter["SECTION_ID"] = intval($SECTION_ID);
	return CIBlockElement::GetList($arOrder, $filter, true);
}
/**
 * Return the list of the elements.
 *
 * @param int $IBLOCK Information block ID.
 * @param bool $SECTION_ID Section ID.
 * @param array $arOrder Has no meaning here.
 * @param int $cnt
 * @param array $arFilter
 * @param array $arSelect
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElementList($IBLOCK, $SECTION_ID = false, $arOrder = array("sort"=>"asc"), $cnt = 0, $arFilter = array(), $arSelect = array())
{
	$filter = array(
		"IBLOCK_ID" => intval($IBLOCK),
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	if ($SECTION_ID !== false)
		$filter["SECTION_ID"]=intval($SECTION_ID);

	if (is_array($arFilter) && !empty($arFilter))
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockElement::GetList($arOrder, $filter, false, false, $arSelect);
	if ($cnt > 0)
		$dbr->NavStart($cnt);

	return $dbr;
}
/**
 * Returns an array with element fields and PROPERTIES key containing element property values.
 * false when element not active or not exists.
 *
 * @param int $ID Identifier of the elements to be returned.
 * @param string $TYPE Information block type identifier to filter elements with.
 *
 * @return array|bool
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockElement($ID, $TYPE = "")
{
	$filter = array(
		"ID" => intval($ID),
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	if ($TYPE != "")
		$filter["IBLOCK_TYPE"] = $TYPE;

	$iblockElement = CIBlockElement::GetList(array(), $filter);
	if($obIBlockElement = $iblockElement->GetNextElement())
	{
		$arIBlockElement = $obIBlockElement->GetFields();
		if ($arIBlock = GetIBlock($arIBlockElement["IBLOCK_ID"], $TYPE))
		{
			$arIBlockElement["IBLOCK_ID"] = $arIBlock["ID"];
			$arIBlockElement["IBLOCK_NAME"] = $arIBlock["NAME"];
			$arIBlockElement["~IBLOCK_NAME"] = $arIBlock["~NAME"];
			$arIBlockElement["PROPERTIES"] = $obIBlockElement->GetProperties();
			return $arIBlockElement;
		}
	}
	return false;
}
/**
 * Returns list of sections of specified iblock including ELEMENT_CNT column.
 *
 * @param int $IBLOCK
 * @param bool|int $SECT_ID
 * @param array $arOrder
 * @param int $cnt
 * @param array $arFilter
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockSectionListWithCnt($IBLOCK, $SECT_ID = false, $arOrder = array("left_margin"=>"asc"), $cnt = 0, $arFilter = array())
{
	$filter = array(
		"IBLOCK_ID" => intval($IBLOCK),
		"ACTIVE" => "Y",
		"CNT_ACTIVE" => "Y",
	);
	if ($SECT_ID !== false)
		$filter["SECTION_ID"] = intval($SECT_ID);

	if (is_array($arFilter) && !empty($arFilter))
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockSection::GetList($arOrder, $filter, true);
	if($cnt > 0)
		$dbr->NavStart($cnt);

	return $dbr;
}
/**
 * Returns list of sections of specified iblock.
 *
 * @param int $IBLOCK
 * @param bool|int $SECT_ID
 * @param array $arOrder
 * @param int $cnt
 * @param array $arFilter
 *
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockSectionList($IBLOCK, $SECT_ID = false, $arOrder = array("left_margin"=>"asc"), $cnt = 0, $arFilter = array())
{
	$filter = array(
		"IBLOCK_ID" => intval($IBLOCK),
		"ACTIVE" => "Y",
		"IBLOCK_ACTIVE" => "Y",
	);
	if ($SECT_ID !== false)
		$filter["SECTION_ID"] = intval($SECT_ID);

	if(is_array($arFilter) && !empty($arFilter))
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockSection::GetList($arOrder, $filter);
	if ($cnt > 0)
		$dbr->NavStart($cnt);

	return $dbr;
}
/**
 * Returns an array with section fields if found. Else returns false.
 *
 * @param int $ID
 * @param string $TYPE
 *
 * @return array|bool
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockSection($ID, $TYPE = "")
{
	$ID = intval($ID);
	if($ID > 0)
	{
		$iblockSection = CIBlockSection::GetList(array(), array(
			"ID" => $ID,
			"ACTIVE" => "Y",
		));
		if($arIBlockSection = $iblockSection->GetNext())
		{
			if($arIBlock = GetIBlock($arIBlockSection["IBLOCK_ID"], $TYPE))
			{
				$arIBlockSection["IBLOCK_ID"] = $arIBlock["ID"];
				$arIBlockSection["IBLOCK_NAME"] = $arIBlock["NAME"];
				return $arIBlockSection;
			}
		}
	}
	return false;
}

/**
 * Returns path to the section.
 *
 * @param int $IBLOCK_ID
 * @param int $SECTION_ID
 * @return CIBlockResult
 * @deprecated No longer used by internal code and not recommended.
 */
function GetIBlockSectionPath($IBLOCK_ID, $SECTION_ID)
{
	return CIBlockSection::GetNavChain($IBLOCK_ID, $SECTION_ID);
}
/**
 * Converts xml string into recursive array
 *
 * @param string $data
 * @return array
 * @deprecated No longer used by internal code and not recommended.
 */
function xmlize_rss($data)
{
	$data = trim($data);
	$values = $index = $array = array();
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$i = 0;

	$tagName = $values[$i]['tag'];
	if (isset($values[$i]['attributes']))
		$array[$tagName]['@'] = $values[$i]['attributes'];
	else
		$array[$tagName]['@'] = array();

	$array[$tagName]["#"] = xml_depth_rss($values, $i);

	return $array;
}
/**
 * Helper function for xmlize_rss
 *
 * @param array $values
 * @param int $i
 * @return array
 * @deprecated No longer used by internal code and not recommended.
 */
function xml_depth_rss($values, &$i)
{
	$children = array();

	if (isset($values[$i]['value']))
		array_push($children, $values[$i]['value']);

	while (++$i < count($values))
	{
		switch ($values[$i]['type'])
		{
			case 'open':
				if (isset($values[$i]['tag']))
					$tagName = $values[$i]['tag'];
				else
					$tagName = '';

				if (isset($children[$tagName]))
					$size = sizeof($children[$tagName]);
				else
					$size = 0;

				if (isset($values[$i]['attributes']))
					$children[$tagName][$size]['@'] = $values[$i]["attributes"];

				$children[$tagName][$size]['#'] = xml_depth_rss($values, $i);
			break;

			case 'cdata':
				array_push($children, $values[$i]['value']);
			break;

			case 'complete':
				$tagName = $values[$i]['tag'];

				if(isset($children[$tagName]))
					$size = sizeof($children[$tagName]);
				else
					$size = 0;

				if(isset($values[$i]['value']))
					$children[$tagName][$size]["#"] = $values[$i]['value'];
				else
					$children[$tagName][$size]["#"] = '';

				if (isset($values[$i]['attributes']))
					$children[$tagName][$size]['@'] = $values[$i]['attributes'];
			break;

			case 'close':
				return $children;
			break;
		}

	}

	return $children;
}
/**
 * Returns html presenting a control of two drop boxes to choose iblock iblock from.
 *
 * @param int $IBLOCK_ID Selected iblock
 * @param string $strTypeName Name of the iblock type select
 * @param string $strIBlockName Name of the iblock name select
 * @param bool|array $arFilter Additional filter for iblock list
 * @param string $onChangeType Additional JS handler for type select
 * @param string $onChangeIBlock Additional JS handler for iblock select
 * @param string $strAddType Additional html inserted into type select
 * @param string $strAddIBlock Additional html inserted into iblock select
 * @return string
 */
function GetIBlockDropDownListEx($IBLOCK_ID, $strTypeName, $strIBlockName, $arFilter = false, $onChangeType = '', $onChangeIBlock = '', $strAddType = '', $strAddIBlock = '')
{
	$html = '';

	static $arTypesAll = array();
	static $arTypes = array();
	static $arIBlocks = array();

	if(!is_array($arFilter))
		$arFilter = array();
	if (!array_key_exists('MIN_PERMISSION',$arFilter) || trim($arFilter['MIN_PERMISSION']) == '')
		$arFilter["MIN_PERMISSION"] = "W";
	$filterId = md5(serialize($arFilter));

	if(!isset($arTypes[$filterId]))
	{
		$arTypes[$filterId] = array(0 => GetMessage("IBLOCK_CHOOSE_IBLOCK_TYPE"));
		$arIBlocks[$filterId] = array(0 => array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK")));

		$rsIBlocks = CIBlock::GetList(array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"), $arFilter);
		while($arIBlock = $rsIBlocks->Fetch())
		{
			$tmpIBLOCK_TYPE_ID = $arIBlock["IBLOCK_TYPE_ID"];
			if(!array_key_exists($tmpIBLOCK_TYPE_ID, $arTypesAll))
			{
				$arType = CIBlockType::GetByIDLang($tmpIBLOCK_TYPE_ID, LANG);
				$arTypesAll[$arType["~ID"]] = $arType["~NAME"]." [".$arType["~ID"]."]";
			}
			if(!array_key_exists($tmpIBLOCK_TYPE_ID, $arTypes[$filterId]))
			{
				$arTypes[$filterId][$tmpIBLOCK_TYPE_ID] = $arTypesAll[$tmpIBLOCK_TYPE_ID];
				$arIBlocks[$filterId][$tmpIBLOCK_TYPE_ID] = array(0 => GetMessage("IBLOCK_CHOOSE_IBLOCK"));
			}
			$arIBlocks[$filterId][$tmpIBLOCK_TYPE_ID][$arIBlock["ID"]] = $arIBlock["NAME"]." [".$arIBlock["ID"]."]";
		}

		$html .= '
		<script type="text/javascript">
		function OnType_'.$filterId.'_Changed(typeSelect, iblockSelectID)
		{
			var arIBlocks = '.CUtil::PhpToJSObject($arIBlocks[$filterId]).';
			var iblockSelect = BX(iblockSelectID);
			if(!!iblockSelect)
			{
				for(var i=iblockSelect.length-1; i >= 0; i--)
					iblockSelect.remove(i);
				for(var j in arIBlocks[typeSelect.value])
				{
					var newOption = new Option(arIBlocks[typeSelect.value][j], j, false, false);
					iblockSelect.options.add(newOption);
				}
			}
		}
		</script>
		';
	}

	$IBLOCK_TYPE = false;
	if($IBLOCK_ID > 0)
	{
		foreach($arIBlocks[$filterId] as $iblock_type_id => $iblocks)
		{
			if(array_key_exists($IBLOCK_ID, $iblocks))
			{
				$IBLOCK_TYPE = $iblock_type_id;
				break;
			}
		}
	}

	$htmlTypeName = htmlspecialcharsbx($strTypeName);
	$htmlIBlockName = htmlspecialcharsbx($strIBlockName);
	$onChangeType = 'OnType_'.$filterId.'_Changed(this, \''.CUtil::JSEscape($strIBlockName).'\');'.$onChangeType.';';
	$onChangeIBlock = trim($onChangeIBlock);

	$html .= '<select name="'.$htmlTypeName.'" id="'.$htmlTypeName.'" onchange="'.htmlspecialcharsbx($onChangeType).'" '.$strAddType.'>'."\n";
	foreach($arTypes[$filterId] as $key => $value)
	{
		if($IBLOCK_TYPE === false)
			$IBLOCK_TYPE = $key;
		$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($IBLOCK_TYPE===$key? ' selected': '').'>'.htmlspecialcharsEx($value).'</option>'."\n";
	}
	$html .= "</select>\n";
	$html .= "&nbsp;\n";
	$html .= '<select name="'.$htmlIBlockName.'" id="'.$htmlIBlockName.'"'.($onChangeIBlock != ''? ' onchange="'.htmlspecialcharsbx($onChangeIBlock).'"': '').' '.$strAddIBlock.'>'."\n";
	foreach($arIBlocks[$filterId][$IBLOCK_TYPE] as $key => $value)
	{
		$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($IBLOCK_ID==$key? ' selected': '').'>'.htmlspecialcharsEx($value).'</option>'."\n";
	}
	$html .= "</select>\n";

	return $html;
}
/**
 * Returns html presenting a control of two drop boxes to choose iblock iblock from.
 * All iblock permission check to be at least W
 *
 * @param int $IBLOCK_ID Selected iblock
 * @param string $strTypeName Name of the iblock type select
 * @param string $strIBlockName Name of the iblock name select
 * @param bool|array $arFilter Additional filter for iblock list
 * @param string $strAddType Additional html inserted into type select
 * @param string $strAddIBlock Additional html inserted into iblock select
 * @return string
 */
function GetIBlockDropDownList($IBLOCK_ID, $strTypeName, $strIBlockName, $arFilter = false, $strAddType = '', $strAddIBlock = '')
{
	if(!is_array($arFilter))
		$arFilter = array();
	$arFilter["MIN_PERMISSION"] = "W";

	return GetIBlockDropDownListEx($IBLOCK_ID, $strTypeName, $strIBlockName, $arFilter, '', '', $strAddType, $strAddIBlock);
}
/**
 * Imports an xml file into iblock. File may be an .tar.gz archive.
 *
 * @param string $file_name Name of the file to import
 * @param string $iblock_type IBlock type ID to import iblock to
 * @param string|array $site_id ID of the site or array of IDs to bind iblock to
 * @param string $section_action What to do with sections missed in the file. D - delete or A - deactivate.
 * @param string $element_action What to do with elements missed in the file. D - delete or A - deactivate.
 * @param bool $use_crc Whenever to use CRC check for optimizi=ation or force an update
 * @param bool $preview If true when use iblock settings to generate preview pictures from detail.
 * @param bool $sync If true uses alternative set of tables in order not to interfere with other import processes
 * @param bool $return_last_error If true will return string with error description in case of failure
 * @param bool $return_iblock_id If true will return iblock identifier (int) in case of success
 * @return bool|int|string
 */
function ImportXMLFile($file_name, $iblock_type="-", $site_id='', $section_action="D", $element_action="D", $use_crc=false, $preview=false, $sync=false, $return_last_error=false, $return_iblock_id=false)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;
	$ABS_FILE_NAME = false;

	if($file_name <> '')
	{
		if(
			file_exists($file_name)
			&& is_file($file_name)
			&& (
				mb_substr($file_name, -4) === ".xml"
				|| mb_substr($file_name, -7) === ".tar.gz"
			)
		)
		{
			$ABS_FILE_NAME = $file_name;
		}
		else
		{
			$filename = trim(str_replace("\\", "/", trim($file_name)), "/");
			$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
			if((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"))
			{
				$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$FILE_NAME;
			}
		}
	}

	if(!$ABS_FILE_NAME)
		return GetMessage("IBLOCK_XML2_FILE_ERROR");

	$WORK_DIR_NAME = mb_substr($ABS_FILE_NAME, 0, mb_strrpos($ABS_FILE_NAME, "/") + 1);

	if(mb_substr($ABS_FILE_NAME, -7) == ".tar.gz")
	{
		include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/tar_gz.php");
		$obArchiver = new CArchiver($ABS_FILE_NAME);
		if(!$obArchiver->ExtractFiles($WORK_DIR_NAME))
		{
			$strError = "";
			if(is_object($APPLICATION))
			{
				$arErrors = $obArchiver->GetErrors();
				if(count($arErrors))
				{
					foreach($arErrors as $error)
						$strError .= $error[1]."<br>";
				}
			}
			if($strError != "")
				return $strError;
			else
				return GetMessage("IBLOCK_XML2_FILE_ERROR");
		}
		$IMP_FILE_NAME = mb_substr($ABS_FILE_NAME, 0, -7).".xml";
	}
	else
	{
		$IMP_FILE_NAME = $ABS_FILE_NAME;
	}

	$fp = fopen($IMP_FILE_NAME, "rb");
	if(!$fp)
		return GetMessage("IBLOCK_XML2_FILE_ERROR");

	if($sync)
		$table_name = "b_xml_tree_sync";
	else
		$table_name = "b_xml_tree";

	$NS = array("STEP"=>0);

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview, false, false, false, $table_name);

	if($sync)
	{
		if(!$obCatalog->StartSession(bitrix_sessid()))
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");

		$obCatalog->ReadXMLToDatabase($fp, $NS, 0, 1024);

		$xml_root = $obCatalog->GetSessionRoot();
		$bUpdateIBlock = false;
	}
	else
	{
		$obCatalog->DropTemporaryTables();

		if(!$obCatalog->CreateTemporaryTables())
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");

		$obCatalog->ReadXMLToDatabase($fp, $NS, 0, 1024);

		if(!$obCatalog->IndexTemporaryTables())
			return GetMessage("IBLOCK_XML2_INDEX_ERROR");

		$xml_root = $obCatalog->GetRoot();
		$bUpdateIBlock = true;
	}

	fclose($fp);

	$result = $obCatalog->ImportMetaData($xml_root, $iblock_type, $site_id, $bUpdateIBlock);
	if($result !== true)
	{
		if($sync)
			$obCatalog->EndSession();
		return GetMessage("IBLOCK_XML2_METADATA_ERROR").implode("\n", $result);
	}

	$obCatalog->ImportSections();
	$obCatalog->DeactivateSections($section_action);
	$obCatalog->SectionsResort();

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview, false, false, false, $table_name);
	if($sync)
	{
		if(!$obCatalog->StartSession(bitrix_sessid()))
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");
	}
	$SECTION_MAP = false;
	$PRICES_MAP = false;
	$obCatalog->ReadCatalogData($SECTION_MAP, $PRICES_MAP);
	$obCatalog->ImportElements(time(), 0);
	$obCatalog->ImportProductSets();

	$obCatalog->DeactivateElement($element_action, time(), 0);
	if($sync)
		$obCatalog->EndSession();

	if($return_last_error)
	{
		if($obCatalog->LAST_ERROR <> '')
		{
			return $obCatalog->LAST_ERROR;
		}
	}

	if ($return_iblock_id)
		return intval($NS["IBLOCK_ID"]);
	else
		return true;
}
