<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

final class SocialnetworkLogEntry extends CBitrixComponent
{
	public static function formatStubEvent($arFields, $arParams)
	{
		$arResult = array(
			'HAS_COMMENTS' => 'N',
			'EVENT' => $arFields,
			'EVENT_FORMATTED' => array(
				'TITLE' => '',
				'TITLE_24' => '',
				'URL' => '',
				"MESSAGE" => '',
				"SHORT_MESSAGE" => '',
				"IS_IMPORTANT" => false,
				"STUB" => true
			)
		);
		$arResult["ENTITY"]["FORMATTED"]["NAME"] = '';
		$arResult["ENTITY"]["FORMATTED"]["URL"] = '';
		$arResult['AVATAR_SRC'] = CSocNetLog::FormatEvent_CreateAvatar($arFields, $arParams, 'CREATED_BY');

		$arFieldsTooltip = array(
			'ID' => $arFields['USER_ID'],
			'NAME' => $arFields['~CREATED_BY_NAME'],
			'LAST_NAME' => $arFields['~CREATED_BY_LAST_NAME'],
			'SECOND_NAME' => $arFields['~CREATED_BY_SECOND_NAME'],
			'LOGIN' => $arFields['~CREATED_BY_LOGIN'],
		);
		$arResult['CREATED_BY']['TOOLTIP_FIELDS'] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);

		return $arResult;
	}

	public function executeComponent()
	{
		return $this->__includeComponent();
	}
}