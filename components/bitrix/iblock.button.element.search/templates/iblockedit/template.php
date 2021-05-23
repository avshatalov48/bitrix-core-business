<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	?><input class="mes-button" type="button" value="<?php
		echo ('' != $arParams['BUTTON_CAPTION'] ? $arParams['BUTTON_CAPTION'] : '...');
	?>" title="<?php
		echo ('' != $arParams['BUTTON_TITLE'] ? $arParams['BUTTON_TITLE'] : '');
	?>" onClick="<?
		$url = "/bitrix/admin/iblock_element_search.php"
			."?lang=".urlencode($arParams['~LANG'])
			."&IBLOCK_ID=".urlencode($arParams["IBLOCK_ID"])
			."&n=&k="
			.('Y' == $arParams['MULTIPLE']? "&m=y": "")
			."&lookup=".urlencode($arParams['ONSELECT'])
		;
		echo htmlspecialcharsbx("jsUtils.OpenWindow('".CUtil::JSEscape($url)."', 600, 500);");
	?>">
