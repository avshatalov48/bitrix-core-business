<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 */
global $APPLICATION;

if(is_array($arParams["PROPERTIES"]))
{
	foreach($arParams["PROPERTIES"] as $val)
	{
		if(isset($val['USER_TYPE_ID']) && $val['USER_TYPE_ID'] == 'url_preview')
		{
			$arParams['urlPreviewId'] = $val['ELEMENT_ID'] ?? 'url_preview_'.$this->randString();
			ob_start();
			$APPLICATION->IncludeComponent(
				"bitrix:system.field.edit",
				"url_preview",
				array(
					"arUserField" => $val,
					'urlPreviewId' => $arParams['urlPreviewId'],
					'STYLE' => $val['STYLE'] ?? ''
				)
			);
			$arParams['URL_PREVIEW_HTML'] = ob_get_clean();
			break;
		}
	}
}
