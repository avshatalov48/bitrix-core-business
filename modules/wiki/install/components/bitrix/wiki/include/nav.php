<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$sPageName = $arResult['ELEMENT']['NAME'] = CWikiUtils::htmlspecialcharsback($arResult['ELEMENT']['NAME'], false);
$sCatName = '';
if (CWikiUtils::IsCategoryPage($sPageName, $sCatName))
	$sPageName = preg_replace('/^category:/i'.BX_UTF_PCRE_MODIFIER, GetMessage('CATEGORY_NAME').':', $sPageName);

if (CWikiSocnet::IsSocNet())
{
	if (intval($arParams["SOCNET_GROUP_ID"]) > 0 && (empty($arParams['SET_TITLE']) || $arParams['SET_TITLE'] != 'N'	|| $this->GetParent()->arResult['SET_NAV_CHAIN'] == 'Y'))
	{
		$arGroup = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
		$arActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"]);
		$sFeatureName = (array_key_exists("wiki", $arActiveFeatures) && $arActiveFeatures["wiki"] <> '' ? $arActiveFeatures["wiki"] : GetMessage("WIKI_SOCNET_TAB"));
	}

	if (
		empty($arParams['SET_TITLE']) 
		|| $arParams['SET_TITLE'] != 'N'
	)
	{
		$strTitleShort = $sFeatureName.(!empty($sPageName) ? ": ".$sPageName : '');
		$strTitle = $arGroup["NAME"].": ".$strTitleShort;

		if ($arParams["HIDE_OWNER_IN_TITLE"] == "Y")
		{
			$APPLICATION->SetPageProperty("title", $strTitle);
			$APPLICATION->SetTitle($strTitleShort);
		}
		else
		{
			$APPLICATION->SetTitle($strTitle);
		}
	}

	if ($this->GetParent()->arResult['SET_NAV_CHAIN'] == 'Y')
	{
		$APPLICATION->AddChainItem($arGroup["NAME"],
			CComponentEngine::MakePathFromTemplate($this->GetParent()->arResult['PATH_TO_GROUP'], array('group_id' => CWikiSocnet::$iSocNetId))
		);

		$APPLICATION->AddChainItem($sFeatureName,
			CComponentEngine::MakePathFromTemplate($this->GetParent()->arResult['PATH_TO_GROUP_WIKI_INDEX'],
				array(
					'group_id' => CWikiSocnet::$iSocNetId,
					'wiki_name' => rawurlencode($arResult['ELEMENT']['NAME'])
				)
			)
		);
	}
}
else
{
	if ($arParams['IN_COMPLEX'] == 'Y')
	{
		$sNavItem = $this->GetParent()->arParams['NAV_ITEM'];
		$sSefFolder = $this->GetParent()->arParams['SEF_FOLDER'];
		if (!empty($sNavItem))
			$APPLICATION->AddChainItem($sNavItem, $sSefFolder);
	}

	if((!empty($arParams['INCLUDE_IBLOCK_INTO_CHAIN']) && $arParams['INCLUDE_IBLOCK_INTO_CHAIN'] == 'Y'))
	{
		$res = CIBlock::GetByID($arParams["IBLOCK_ID"]);
		if($arIBLOCK = $res->GetNext())
		{
			$arIBLOCK["~LIST_PAGE_URL"] = str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_TYPE_ID#", "#IBLOCK_ID#", "#IBLOCK_CODE#", "#IBLOCK_EXTERNAL_ID#", "#CODE#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arIBLOCK["IBLOCK_TYPE_ID"], $arIBLOCK["ID"], $arIBLOCK["CODE"], $arIBLOCK["EXTERNAL_ID"], $arIBLOCK["CODE"]),
				$arParams["IBLOCK_URL"] <> ''? trim($arParams["~IBLOCK_URL"]) : $arIBLOCK["~LIST_PAGE_URL"]
			);
			$arIBLOCK["~LIST_PAGE_URL"] = preg_replace("'/+'s", "/", $arIBLOCK["~LIST_PAGE_URL"]);
			$APPLICATION->AddChainItem($arIBLOCK['NAME'], $arIBLOCK['~LIST_PAGE_URL']);
		}
	}

	if (CWiki::GetDefaultPage($arParams['IBLOCK_ID']) != $arResult['ELEMENT']['NAME'])
	{
		if((!empty($arParams['ADD_SECTIONS_CHAIN']) && $arParams['ADD_SECTIONS_CHAIN'] == 'Y') && !empty($arResult['ELEMENT']['SECTIONS']))
		{
			$rsPath = CIBlockSection::GetNavChain($arParams["IBLOCK_ID"], $arResult['ELEMENT']['SECTIONS'][1]['ID']);
			while($arPath = $rsPath->GetNext())
				$APPLICATION->AddChainItem($arPath['NAME'], $arPath['~SECTION_PAGE_URL']);
		}
	}

	$arTitleOptions = null;
	if(!empty($arParams['SET_TITLE']) && $arParams['SET_TITLE'] == 'Y' && !empty($arResult['ELEMENT']['NAME']))
	{
		$APPLICATION->SetTitle($sPageName, $arTitleOptions);
		$APPLICATION->SetPageProperty('title', $sPageName, $arTitleOptions);
		$APPLICATION->AddChainItem(htmlspecialcharsbx($sPageName), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"],
			array(
				'wiki_name' => rawurlencode($arResult['ELEMENT']['NAME']),
				'group_id' => CWikiSocnet::$iSocNetId)
			)
		);
	}
}

?>