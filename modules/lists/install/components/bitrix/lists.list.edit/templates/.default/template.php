<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

\Bitrix\Main\Loader::includeModule('ui');

Bitrix\Main\UI\Extension::load(["lists", "popup", "ui.buttons", "ui.notification", "ui.dialogs.messagebox"]);

$jsClass = 'ListsEditClass_' . $arResult['RAND_STRING'];
if ($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$processes = true;
	$typeTranslation = '_PROCESS';
}
else
{
	$processes = false;
	$typeTranslation = '';
}

$listAction = [];
if ($arResult["IBLOCK_ID"])
{
	$listAction[] = [
		"text" => GetMessage("CT_BLLE_TOOLBAR_DELETE" . $typeTranslation),
		"onclick" => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['" . $jsClass . "'].deleteIblock('" . CUtil::JSEscape("form_" . $arResult["FORM_ID"]) . "', '" .
			GetMessage("CT_BLLE_TOOLBAR_DELETE_WARNING" . $typeTranslation) . "')"
		),
	];

	if (!$processes && IsModuleInstalled('intranet') && !$arParams["SOCNET_GROUP_ID"])
	{
		$listAction[] = [
			"text" => GetMessage("CT_BLLE_TOOLBAR_MIGRATE_PROCESSES"),
			"onclick" => new \Bitrix\UI\Buttons\JsCode(
				"BX.Lists['"
				. $jsClass
				. "'].migrateList('"
				. CUtil::JSEscape("form_" . $arResult["FORM_ID"])
				. "', '" . GetMessage("CT_BLLE_TOOLBAR_MIGRATE_WARNING_PROCESS") . "')"
			),
		];
	}

	$listAction[] = [
		"text" => GetMessage("CT_BLLE_TOOLBAR_LIST_COPY" . $typeTranslation),
		"onclick" => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['" . $jsClass . "'].copyIblock()"
		),
	];

	$listAction[] = [
		"text" => GetMessage("CT_BLLE_TOOLBAR_FIELDS_TITLE" . $typeTranslation),
		"href" => $arResult["LIST_FIELDS_URL"],
	];
}

if (!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

if ($arResult["IBLOCK_ID"])
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
			'link' => $arResult["LIST_URL"],
			'color' => \Bitrix\UI\Buttons\Color::LINK,
			'text' => GetMessage("CT_BLLE_TOOLBAR_RETURN_LIST_ELEMENT"),
			'classList' => ['lists-list-back'],
		]
	);
}

if ($listAction)
{
	$settingsButton = new Bitrix\UI\Buttons\SettingsButton([
		'menu' => [
			'items' => $listAction,
		],
	]);
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($settingsButton);
}

ob_start();
IBlockShowRights(
/*$entity_type=*/ 'iblock',
	/*$iblock_id=*/ $arResult["IBLOCK_ID"],
	/*$id=*/ $arResult["IBLOCK_ID"],
	/*$section_title=*/ "",
	/*$variable_name=*/ "RIGHTS",
	/*$arPossibleRights=*/ $arResult["TASKS"],
	/*$arActualRights=*/ $arResult["RIGHTS"],
	/*$bDefault=*/ true,
	/*$bForceInherited=*/ false
);
$rights_html = ob_get_clean();

$rights_fields = [
	[
		"id" => "RIGHTS",
		"name" => GetMessage("CT_BLLE_ACCESS_RIGHTS"),
		"type" => "custom",
		"colspan" => true,
		"value" => $rights_html,
	],
];

$custom_html = '<input type="hidden" name="action" id="action" value="">';

$arTab1 = [
	"id" => "tab1",
	"name" => GetMessage("CT_BLLE_TAB_EDIT"),
	"title" => GetMessage("CT_BLLE_TAB_EDIT_TITLE" . $typeTranslation),
	"icon" => "",
	"fields" => [
		["id" => "NAME", "name" => GetMessage("CT_BLLE_FIELD_NAME" . $typeTranslation), "required" => true],
		[
			"id" => "DESCRIPTION",
			"name" => GetMessage("CT_BLLE_FIELD_DESCRIPTION" . $typeTranslation),
			"type" => "textarea",
		],
		["id" => "SORT", "name" => GetMessage("CT_BLLE_FIELD_SORT"), "params" => ["size" => 5]],
		["id" => "PICTURE", "name" => GetMessage("CT_BLLE_FIELD_PICTURE"), "type" => "file"],
	],
];
if ($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	if (isset($arResult["FORM_DATA"]["BIZPROC"]))
	{
		$arTab1["fields"][] = [
			"id" => "BIZPROC",
			"type" => "custom",
			"value" => '<input type="hidden" name="BIZPROC" value="Y">',
		];
	}
}
else
{
	if (isset($arResult["FORM_DATA"]["BIZPROC"]))
	{
		$arTab1["fields"][] = [
			"id" => "BIZPROC",
			"name" => GetMessage("CT_BLLE_FIELD_BIZPROC"),
			"type" => "checkbox",
		];
	}
}

$arTab1["fields"][] = [
	"id" => "LOCK_FEATURE",
	"name" => GetMessage("CT_BLLE_FIELD_LOCK"),
	"type" => "checkbox",
];

$backUrl = $arResult["IBLOCK"] ? $arResult["~LIST_URL"] : $arResult["~LISTS_URL"];

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.form",
	"",
	[
		"FORM_ID" => $arResult["FORM_ID"],
		"TABS" => [
			$arTab1,
			[
				"id" => "tab2",
				"name" => GetMessage("CT_BLLE_TAB_MESSAGES"),
				"title" => GetMessage("CT_BLLE_TAB_MESSAGES_TITLE" . $typeTranslation),
				"icon" => "",
				"fields" => [
					["id" => "ELEMENTS_NAME", "name" => GetMessage("CT_BLLE_FIELD_ELEMENTS_NAME")],
					["id" => "ELEMENT_NAME", "name" => GetMessage("CT_BLLE_FIELD_ELEMENT_NAME")],
					["id" => "ELEMENT_ADD", "name" => GetMessage("CT_BLLE_FIELD_ELEMENT_ADD")],
					["id" => "ELEMENT_EDIT", "name" => GetMessage("CT_BLLE_FIELD_ELEMENT_EDIT")],
					["id" => "ELEMENT_DELETE", "name" => GetMessage("CT_BLLE_FIELD_ELEMENT_DELETE")],
					["id" => "SECTIONS_NAME", "name" => GetMessage("CT_BLLE_FIELD_SECTIONS_NAME")],
					["id" => "SECTION_NAME", "name" => GetMessage("CT_BLLE_FIELD_SECTION_NAME")],
					["id" => "SECTION_ADD", "name" => GetMessage("CT_BLLE_FIELD_SECTION_ADD")],
					["id" => "SECTION_EDIT", "name" => GetMessage("CT_BLLE_FIELD_SECTION_EDIT")],
					["id" => "SECTION_DELETE", "name" => GetMessage("CT_BLLE_FIELD_SECTION_DELETE")],
				],
			],
			[
				"id" => "tab3",
				"name" => GetMessage("CT_BLLE_TAB_ACCESS"),
				"title" => GetMessage("CT_BLLE_TAB_ACCESS_TITLE" . $typeTranslation),
				"icon" => "",
				"fields" => $rights_fields,
			],
		],
		"BUTTONS" => ["back_url" => $backUrl, "custom_html" => $custom_html],
		"DATA" => $arResult["FORM_DATA"],
		"SHOW_SETTINGS" => "N",
		"THEME_GRID_ID" => $arResult["GRID_ID"],
	],
	$component, ["HIDE_ICONS" => "Y"]
);

$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
?>

<script type="text/javascript">
	BX(function()
	{
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsEditClass({
			randomString: '<?=$arResult['RAND_STRING']?>',
			iblockTypeId: '<?=$arParams["IBLOCK_TYPE_ID"]?>',
			iblockId: '<?=$arResult["IBLOCK_ID"]?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			listsUrl: '<?=CUtil::JSEscape($arResult["LISTS_URL"])?>',
			listTemplateEditUrl: '<?=$arParams["LIST_EDIT_URL"]?>',
			listElementUrl: '<?=htmlspecialcharsbx($arParams["LIST_ELEMENT_URL"])?>'
		});

		BX.message({
			CT_BLLE_TOOLBAR_LIST_COPY_BUTTON_TITLE: '<?=GetMessageJS("CT_BLLE_TOOLBAR_LIST_COPY_BUTTON_TITLE")?>',
			CT_BLLE_MIGRATE_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_TITLE")?>',
			CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_MIGRATE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_CANCEL_BUTTON")?>',
			CT_BLLE_DELETE_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_TITLE")?>',
			CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_CANCEL_BUTTON")?>',
			CT_BLLE_COPY_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_TITLE")?>',
			CT_BLLE_COPY_POPUP_CONTENT: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_CONTENT")?>',
			CT_BLLE_COPY_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_COPY_POPUP_COPIED_SUCCESS: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_COPIED_SUCCESS"
				. $typeTranslation)?>',
			CT_BLLE_COPY_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_CANCEL_BUTTON")?>'
		});
	});
</script>