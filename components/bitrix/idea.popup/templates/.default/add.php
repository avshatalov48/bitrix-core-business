<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 */
?>
<div id="idea-editor-container">
	<?$APPLICATION->IncludeComponent(
		"bitrix:idea.edit",
		"light",
		Array(
			"BLOG_URL"					=> $arParams["BLOG_URL"],
			"PATH_TO_POST"			=> $arParams["PATH_IDEA_POST"],
			"SET_TITLE"					=> "N",
			"SET_NAV_CHAIN"				=> "N",
			"POST_PROPERTY"				=> CIdeaManagment::getInstance()->GetUserFieldsArray(),
			"SMILES_COLS" 				=> $arParams["SMILES_COLS"],
			"SMILES_COUNT" 				=> 1,
			//"SHOW_LOGIN" 				=> $arParams["SHOW_LOGIN"],
			"EDITOR_RESIZABLE" => "N",
			"EDITOR_DEFAULT_HEIGHT" => "200",
			"POST_BIND_STATUS_DEFAULT" => $arParams["POST_BIND_STATUS_DEFAULT"],
			//"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"],
			//"USE_GOOGLE_CODE" => $arParams["USE_GOOGLE_CODE"],
			"AUTH_TEMPLATE" => $arParams["AUTH_TEMPLATE"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"FORGOT_PASSWORD_URL" => $arParams["FORGOT_PASSWORD_URL"],
			"REGISTER_URL" => $arParams["REGISTER_URL"]
		),
		$component
	);
	?>
</div>