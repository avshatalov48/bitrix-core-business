<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';
$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['SEF_MODE']))
{
	$arParams['SEF_MODE'] = 'N';
	if ($arParams['IN_COMPLEX'] == 'Y')
		$arParams['SEF_MODE'] = $this->GetParent()->arResult['SEF_MODE'];
}

if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(strlen($arParams['PATH_TO_POST'])<=0)
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if(strlen($arParams['PATH_TO_CATEGORIES'])<=0)
{
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == "Y")
		$arParams['PATH_TO_CATEGORIES'] = $this->GetParent()->arResult['PATH_TO_CATEGORIES'];
}

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

if (empty($arParams['CATEGORY_COUNT']))
	$arParams['CATEGORY_COUNT'] = 20;


$sCategoryName = !empty($GLOBALS['q']) ? $GLOBALS['q'] :  '';

if(!empty($sCategoryName))
	$arResult['QUERY'] = htmlspecialcharsbx($sCategoryName);

$GLOBALS['arParams'] = $arParams;

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (empty($arParams['IBLOCK_ID']))
{
	ShowError(GetMessage('IBLOCK_NOT_ASSIGNED'));
	return;
}

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	if(!CModule::IncludeModule('socialnetwork'))
	{
		ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
		return;
	}
}

if (array_key_exists('SOCNET_GROUP_ID', $arParams) && empty($arParams['SOCNET_GROUP_ID']))
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	$iblock_id_tmp = CWikiSocnet::RecalcIBlockID($arParams["SOCNET_GROUP_ID"]);
	if ($iblock_id_tmp)
		$arParams['IBLOCK_ID'] = $iblock_id_tmp;

	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID']))
	{
		ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
		return;
	}
}

if (!CWikiUtils::IsReadable())
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}

//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks('service', $arParams);

// looking for a page without categories
/*$arFilter = array();
$arFilter = array(
	"IBLOCK_LID" => SITE_ID,
	"IBLOCK_ID" => $arParams['IBLOCK_ID'],
	"CHECK_PERMISSIONS" => 'N',
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
	"ACTIVE" => 'Y',
	"!NAME" => 'Category%',
	"SECTION_ID" => false
);
if (CWikiSocnet::IsSocNet())
	$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;
$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
$iUnsorted = 0;
while($arElement = $rsElement->GetNext())
	$iUnsorted++; */

$arParams['ELEMENT_NAME'] = rawurldecode($arParams['ELEMENT_NAME']);

$arFilter = Array();
$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
$arFilter['CHECK_PERMISSIONS'] = 'N';
$arFilter['GLOBAL_ACTIVE'] = 'Y';
$arFilter['ELEMENT_SUBSECTIONS'] = 'Y';
//$arFilter['ACTIVE_DATE'] = '';
$arFilter['CNT_ACTIVE'] = 'Y';
if (CWikiSocnet::IsSocNet())
{
	$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
	$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
}
if (!empty($sCategoryName))
	$arFilter['%NAME'] = $sCategoryName;

$dbList = CIBlockSection::GetList(Array('NAME'=>'ASC'), $arFilter, true);

$arResult['CATEGORIES'] = array();

$categories = new CWikiCategories;
$catParams = new CWikiCategoryParams;
$catParams->setPathTemplate($arParams['PATH_TO_CATEGORY']);

while($arCat = $dbList->GetNext())
{
	//fix: http://jabber.bx/view.php?id=26658
	if($arCat['ELEMENT_CNT']<=0)
	{
		CIBlockSection::Delete($arCat['ID'],false);
		continue;
	}

	$catParams->sName = $arCat['NAME'];
	$catParams->sTitle = $arCat['NAME'];
	$catParams->iItemsCount = $arCat['ELEMENT_CNT'];
	$catParams->bIsRed = 'Y';
	$catParams->createLinkFromTemplate();
	$categories->addItem($catParams);
}


//Pages without categories

$arElementFilter = array(
	"IBLOCK_ID" => $arParams['IBLOCK_ID'],
	"SECTION_ID" => CWikiSocnet::IsSocNet() ? CWikiSocnet::$iCatId : false,
	"INCLUDE_SUBSECTIONS" => "N",
	"ACTIVE" => "Y"
);

$noCatCount = CIBlockElement::GetList(Array(), $arElementFilter, array(), false, array("ID"));

$catParams->sName = GetMessage("WIKI_CATEGORY_NOCAT");
$catParams->sTitle = GetMessage("WIKI_CATEGORY_NOCAT_TITLE");
$catParams->iItemsCount = $noCatCount;
$catParams->bIsRed = 'N';
$catParams->createLinkFromTemplate();

if($noCatCount > 0)
	$categories->addItem($catParams);

$arElementFilter["INCLUDE_SUBSECTIONS"] = "Y";

if(!CWikiSocnet::IsSocNet())
	unset($arElementFilter["SECTION_ID"]);

$allCatCount = CIBlockElement::GetList(Array(), $arElementFilter, array(), false, array("ID"));

//All Pages in all categories
$catParams->sName = GetMessage("WIKI_CATEGORY_ALL");
$catParams->sTitle = GetMessage("WIKI_CATEGORY_ALL_TITLE");
$catParams->iItemsCount = $allCatCount;
$catParams->bIsRed = 'N';
$catParams->createLinkFromTemplate();

$categories->addItem($catParams);

//Anyone can add custom virtual category to the list
$rsHandlers = GetModuleEvents("wiki", "OnCategoryListCreate");
while($arHandler = $rsHandlers->Fetch())
	ExecuteModuleEventEx($arHandler, array(&$categories, $arParams['PATH_TO_CATEGORY']));

$arResult['CATEGORIES'] =$categories->GetItems();

sortByColumn($arResult['CATEGORIES'],"NAME");

//for pagination
$dbCatList = new CDBResult;
$dbCatList->InitFromArray($arResult['CATEGORIES']);
$arResult['CATEGORIES'] = array();
$dbCatList->NavStart($arParams['CATEGORY_COUNT'], false);

while($arCat = $dbCatList->GetNext())
	$arResult['CATEGORIES'][strtolower($arCat["NAME"])]=$arCat;

$arResult['DB_LIST'] = &$dbCatList;
$arCatName = $categories->getItemsNames();
$arCatNameExists = array();

if (!empty($arCatName))
{
	// checking the category on the "red link"
	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y',
		'NAME' => $arCatName
	);

	if (CWikiSocnet::IsSocNet())
		$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;

	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
	while($arElement = $rsElement->GetNext())
		$arCatNameExists[] = substr($arElement['NAME'], strpos($arElement['NAME'], ':') + 1);

	if (!empty($arCatNameExists))
	{
		foreach ($arCatNameExists as $sCatName)
		{
			$sCatNameLow = strtolower($sCatName);
			if (isset($arResult['CATEGORIES'][$sCatNameLow]))
			{
				$arResult['CATEGORIES'][$sCatNameLow]['IS_RED'] = 'N';
				// exclude the very category page
				if(!CWikiUtils::isVirtualCategoryExist($sCatName))
					$arResult['CATEGORIES'][$sCatNameLow]['CNT']--;
			}
		}
	}
}

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

$this->IncludeComponentTemplate();

unset($GLOBALS['arParams']);

?>