<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	//path to component
	$PathToComponent = $this->GetFolder();

	//display playlist
	$arResult['NO_PLAY_LIST'] = $arResult['ELEMENT_CNT']<2;
	//logo
	if(!$arParams["LOGO"])
		$arParams["LOGO"] = $PathToComponent.'/images/logo.png';
	//Set Selected Element Detail Image
	if(!$arResult['SELECTED_ELEMENT']['VALUES']['DETAIL_PICTURE'])
		$arResult['SELECTED_ELEMENT']['VALUES']['DETAIL_PICTURE'] = $PathToComponent.'/images/default_big.png';

	//prepare list and 1st Item
	$FirstItem = false;
	$strPlayList = '';
	$strPlayList .='
		jsPublicTVCollector.list['.$arResult['PREFIX'].'] =
		[';
	$i = 0;
	foreach ($arResult['SECTIONS'] as $keySection=>$valSection)
	{
		$strPlayList .= "
			{
				Id: '".$keySection."',
				Name: '".__CIBlockTV::Prepare($valSection['NAME'])."',
				Depth: '0',
				Items:
				[";
		$j = 0;
		foreach ($valSection['ELEMENTS'] as $keyElement=>$ValElement)
		{
			if(!$ValElement['PREVIEW_PICTURE'])
				$ValElement['PREVIEW_PICTURE'] = $PathToComponent.'/images/default_small.png';
			if(!$ValElement['DETAIL_PICTURE'])
				$ValElement['DETAIL_PICTURE'] = $PathToComponent.'/images/default_big.png';

			if(!$FirstItem)
			{
				$FirstItem = $ValElement;
				$FirstItem['JS_SECTION'] = $i;
				$FirstItem['JS_ITEM'] = 0;
			}

			$arButtons = CIBlock::GetPanelButtons($ValElement["IBLOCK_ID"], $ValElement["ID"], $valSection["ID"], array("SECTION_BUTTONS"=>false));

			$strPlayList .="
					{
						Id: ".$keyElement.",
						Name: '".__CIBlockTV::Prepare($ValElement['NAME'])."',
						Description: '".__CIBlockTV::Prepare($ValElement['PREVIEW_TEXT'])."',
						SmallImage: '".$ValElement['PREVIEW_PICTURE']."',
						BigImage: '".$ValElement['DETAIL_PICTURE']."',
						Duration: '".__CIBlockTV::Prepare($ValElement['DURATION'])."',
						File: '".__CIBlockTV::Prepare($ValElement['FILE'])."',
						Size: '".$ValElement['FILE_SIZE']."',
						Type: '".$ValElement['TYPE']."',
						Action: '".CUtil::JSEscape($arButtons["edit"]["edit_element"]["ACTION"])."'
					}".((++$j<count($valSection['ELEMENTS']))?',':'');
		}
		$strPlayList .='
				]
			}'.((++$i<count($arResult['SECTIONS']))?',':'');
	}
	$strPlayList .='
		];';

	if($arParams["STAT_EVENT"] || $arParams["SHOW_COUNTER_EVENT"])
	{
		foreach($arResult["RAW_FILES"] as $path => $arFile)
			$strPlayList .= "\njsPublicTVCollector.files['".__CIBlockTV::Prepare($path)."'] = ".$arFile["ID"].";\n";
	}

	$arResult['LIST'] = $strPlayList;
	$arResult['FIRST_ITEM'] = $FirstItem;
	$arResult['CORRECTION']["FLV"] = 24;
	$arResult['CORRECTION']["WMV"] = 20;
	//wmv + 20
	//flv + 24
?>