<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $CACHE_MANAGER;

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

$arCache = array();
if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (mb_strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
	{
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
		$arCache['SOCNET_GROUP_ID'] = $arParams['SOCNET_GROUP_ID'];
	}
}

if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if($arParams['PATH_TO_POST_EDIT'] == '')
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if($arParams['PATH_TO_HISTORY'] == '')
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if($arParams['PATH_TO_HISTORY_DIFF'] == '')
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if($arParams['PATH_TO_DISCUSSION'] == '')
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if($arParams['PATH_TO_CATEGORIES'] == '')
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if($arParams['PATH_TO_USER'] == '')
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

if (empty($arParams['PAGES_COUNT']))
	$arParams['PAGES_COUNT'] = 100;

if (empty($arParams['COLUMN_COUNT']))
	$arParams['COLUMN_COUNT'] = 3;

$arNavParams = array(
	'nPageSize' => $arParams['PAGES_COUNT']
);
$arNavigation = CDBResult::GetNavParams($arNavParams);

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

if($this->StartResultCache(false, array($USER->GetGroups(), $arNavigation, $arCache), false))
{
	$arParams['ELEMENT_NAME'] = CWikiUtils::htmlspecialcharsback($arParams['ELEMENT_NAME']);
	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y'
	);

	if (empty($arParams['ELEMENT_NAME']))
		$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);

	$arResult['ELEMENT'] = array();
	$arResult['CATEGORIES'] = array();
	$arResult['PAGES'] = array();
	if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
	{
		$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
	}

	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag('wiki_'.$arParams['ELEMENT_ID']);
	$CACHE_MANAGER->EndTagCache();

	$SERVICE_NAME = '';

	if (CWikiUtils::IsCategoryPage($arParams['ELEMENT_NAME'], $SERVICE_NAME))
	{
		$arParams['ELEMENT_NAME'] = mb_strtolower(CWikiUtils::UnlocalizeCategoryName($arParams['ELEMENT_NAME']));
		$arResult['CUR_CAT']['NAME'] = $SERVICE_NAME;
		$arPagesFilter = array(
				'IBLOCK_ID' => $arParams['IBLOCK_ID'],
				'CHECK_PERMISSIONS' => 'N',
				'ACTIVE' => 'Y'
				);
		$arSort = array('XML_ID' => 'ASC');

		if($arParams['ELEMENT_NAME'] == mb_strtolower("category:".GetMessage('WIKI_CATEGORY_ALL'))) //All Pages from all categories
		{
			$arPagesFilter['INCLUDE_SUBSECTIONS'] = 'Y';

			if (CWikiSocnet::IsSocNet())
				$arPagesFilter['SECTION_ID'] = CWikiSocnet::$iCatId;

			$rsPagesElement = CIBlockElement::GetList($arSort, $arPagesFilter, false, false, Array());
		}
		elseif($arParams['ELEMENT_NAME'] == mb_strtolower("category:".GetMessage('WIKI_CATEGORY_NOCAT'))) //Pages without categories
		{
			$arPagesFilter['INCLUDE_SUBSECTIONS'] = 'N';

			if (CWikiSocnet::IsSocNet())
				$arPagesFilter['SECTION_ID'] = CWikiSocnet::$iCatId;
			else
				$arPagesFilter['SECTION_ID'] = 0;

			$rsPagesElement = CIBlockElement::GetList($arSort, $arPagesFilter, false, false, Array());
		}
		else
		{
			$arFilter = Array();
			$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
			$arFilter['NAME'] = $SERVICE_NAME;
			$arFilter['CHECK_PERMISSIONS'] = 'N';

			if (CWikiSocnet::IsSocNet())
			{
				$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
				$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
			}
			$rsCat = CIBlockSection::GetList(Array('NAME'=>'ASC'), $arFilter, true);
			$arCurCat = $rsCat->GetNext();
			if (!empty($arCurCat)) //usual category
			{
				$arResult['CUR_CAT'] = $arCurCat;
				$arFilter = Array();
				$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
				$arFilter['GLOBAL_ACTIVE'] = 'Y';
				$arFilter['CHECK_PERMISSIONS'] = 'N';
				$arFilter['CNT_ACTIVE'] = 'Y';
				$arFilter['DEPTH_LEVEL'] = $arCurCat['DEPTH_LEVEL'] + 1;
				$arFilter['>LEFT_BORDER'] = $arCurCat['LEFT_MARGIN'];
				$arFilter['<RIGHT_BORDER'] = $arCurCat['RIGHT_MARGIN'];

				$dbList = CIBlockSection::GetList(Array('NAME'=>'ASC'), $arFilter, true);

				$arCatName = array();
				$arCatNameExists = array();
				while($arCat = $dbList->GetNext())
				{
					$arCatName[] = 'category:'.$arCat['NAME'];
					$arResult['CATEGORIES'][mb_strtolower($arCat['NAME'])] = array(
						'TITLE' => $arCat['NAME'],
						'NAME' => $arCat['NAME'],
						'CNT' => $arCat['ELEMENT_CNT'],
						'IS_RED' => 'Y',
						'LINK' => CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORY'],
							array(
								'wiki_name' => 'category:'.rawurlencode($arCat['NAME']),
								'group_id' => CWikiSocnet::$iSocNetId
							)
						)
					);
				}

				if (!empty($arCatName))
				{
					// checking the category on the "red link"
					$arFilter = array(
						'IBLOCK_LID' => SITE_ID,
						'IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'CHECK_PERMISSIONS' => 'N',
						'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
						'ACTIVE' => 'Y',
						'NAME' => $arCatName
					);
					if (CWikiSocnet::IsSocNet())
						$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;

					$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
					while($arElement = $rsElement->GetNext())
						$arCatNameExists[] = mb_substr($arElement['NAME'], mb_strpos($arElement['NAME'], ':') + 1);

					if (!empty($arCatNameExists))
					{
						foreach ($arCatNameExists as $sCatName)
						{
							$sCatName = mb_strtolower($sCatName);
							if (isset($arResult['CATEGORIES'][$sCatName]))
								$arResult['CATEGORIES'][$sCatName]['IS_RED'] = 'N';
						}
					}
				}

				$arPagesFilter['SUBSECTION'] = $arCurCat['ID'];
				$rsPagesElement = CIBlockElement::GetList($arSort, $arPagesFilter, false, false, Array());
			}
		}

		//Anyone can build own pages list
		$rsHandlers = GetModuleEvents("wiki", "OnCategoryPagesListCreate");
		while($arHandler = $rsHandlers->Fetch())
			if($handlRes = ExecuteModuleEventEx($arHandler, array($arParams['ELEMENT_NAME'],$arParams['IBLOCK_ID'])))
			{
				$rsPagesElement = $handlRes;
				break;
			}

		if(isset($rsPagesElement) && $rsPagesElement)
		{
			$arPageNameExists = array();
			$rsPagesElement->NavStart($arParams['PAGES_COUNT'], false);
			$arResult['DB_LIST'] = &$rsPagesElement;

			while($arPage = $rsPagesElement->GetNext())
			{
				$sname = $arPage['NAME'];
				if (CWikiUtils::CheckServicePage($arPage['NAME'], $sname))
					continue ;

				$arResult['PAGES'][$arPage['NAME']] = array(
					'TITLE' => $arPage['NAME'],
					'NAME' => $sname,
					'IS_RED' => $arPage['ACTIVE'] == 'Y' ? 'N' : 'Y',
					'LINK' => CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'],
						array(
							'wiki_name' => rawurlencode($arPage['NAME']),
							'group_id' => CWikiSocnet::$iSocNetId
						)
					)
				);
			}

			$arResult['COLUMNS_COUNT'] = empty($arParams['COLUMNS_COUNT']) ? 1 : $arParams['COLUMNS_COUNT'];
			$arResult['PAGES_COUNT'] = empty($arParams['PAGES_COUNT']) ? 1 : $arParams['PAGES_COUNT'];

		}
		else
		{
			$arResult['COLUMNS_COUNT'] = 1;
			$arResult['PAGES_COUNT'] = 1;
		}
	}

	$this->IncludeComponentTemplate();
}

unset($GLOBALS['arParams']);

?>
