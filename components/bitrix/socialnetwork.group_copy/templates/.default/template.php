<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.common",
	"ui.forms",
	"ui.buttons",
	"ui.notification",
	"ui.alerts",
	"date",
	'uploader',
]);

CJSCore::init(["date"]);

$messages = Loc::loadLanguageFile(__FILE__);

$formContainerId = "social-group-copy-form-container";

$customContainerClass = "social-group-copy-custom-container";
$imageFieldContainerId = "social-group-copy-group-image-container";
$ownerFieldContainerId = "social-group-copy-group-owner-container";
$helpersFieldContainerId = "social-group-copy-group-helpers-container";
$tagsFieldContainerId = "social-group-copy-group-tags-container";

$isProject = $arResult["IS_PROJECT"];
$isExtranet = $arResult["EXTRANET"];
$isExtranetGroup = $arResult["IS_EXTRANET_GROUP"];
$isExtranetInstalled = ($arResult["EXTRANET_INSTALLED"] ? "Y" : "N");
$isIntranetInstalled = ($arResult["INTRANET_INSTALLED"] ? "Y" : "N");
$isLandingInstalled = ($arResult["LANDING_INSTALLED"] ? "Y" : "N");
?>

<div id="<?=$formContainerId?>" class="social-group-copy-form-container"></div>

<div class="social-group-copy-button-container">
<?php
	$copyButtonId = "social-group-copy-button";
	$copyButton = "<span class=\"ui-btn ui-btn-success ui-btn-md\" id=\"".$copyButtonId."\">".
		Loc::getMessage("SGCG_BUTTON_ACCEPT")."</span>";
	$cancelButtonId = "social-group-cancel-button";
	$cancelButton = "<span class=\"ui-btn ui-btn-link\" id=\"".$cancelButtonId."\">".
		Loc::getMessage("SGCG_BUTTON_CANCEL")."</span>";

	$APPLICATION->includeComponent("bitrix:ui.button.panel", "", [
		"BUTTONS" => [
			["type" => "custom", "layout" => $copyButton],
			["type" => "custom", "layout" => $cancelButton],
		]
	]);
?>
</div>

<div id="<?=$imageFieldContainerId?>" class="<?=$customContainerClass?> social-group-copy-link-upload-inner">
<?php
$APPLICATION->includeComponent("bitrix:main.file.input", ".default", array(
	"INPUT_NAME" => "image_id",
	"INPUT_NAME_UNSAVED" => "image_id_unsaved",
	"CONTROL_ID" => "image_id",
	"INPUT_VALUE" => $arResult["GROUP"]["IMAGE_ID"],
	"MULTIPLE" => "N",
	"ALLOW_UPLOAD" => "I",
	"INPUT_CAPTION" => Loc::getMessage("SGCG_UPLOAD_IMAGE"),
	"SHOW_AVATAR_EDITOR" => "Y",
	"ENABLE_CAMERA" => "N"
));
?>
</div>

<div id="<?=$ownerFieldContainerId?>" class="<?=$customContainerClass?>">
<?php
$APPLICATION->includeComponent(
	"bitrix:main.user.selector",
	"",
	[
		"ID" => "group-copy-owner",
		"INPUT_NAME" => "owner",
		"LIST" => ["U".$arResult["GROUP"]["OWNER_ID"]],
		"USE_SYMBOLIC_ID" => true,
		"BUTTON_SELECT_CAPTION" => Loc::getMessage("SGCG_OWNER_SELECT_CAPTION"),
		"API_VERSION" => 3,
		"SELECTOR_OPTIONS" => [
			"userSearchArea" => ($arResult["EXTRANET_INSTALLED"] ? "I" : false),
			"contextCode" => "U",
			"context" => "INVITE_OWNER",
		]
	]
);
?>
</div>

<div id="<?=$helpersFieldContainerId?>" class="<?=$customContainerClass?>">
<?php
$moderatorsList = [];
foreach ($arResult["GROUP"]["MODERATOR_IDS"] as $moderatorId)
{
	$moderatorsList["U".$moderatorId] = "users";
}
$APPLICATION->includeComponent(
	"bitrix:main.user.selector",
	"",
	[
		"ID" => "group-copy-helpers",
		"INPUT_NAME" => "moderators[]",
		"LIST" => $moderatorsList,
		"USE_SYMBOLIC_ID" => true,
		"BUTTON_SELECT_CAPTION" => ($arResult["INTRANET_INSTALLED"] ?
			Loc::getMessage("SGCG_EMPLOYEE_SELECT_CAPTION") : Loc::getMessage("SGCG_USER_SELECT_CAPTION")),
		"BUTTON_SELECT_CAPTION_MORE" => Loc::getMessage("SGCG_HELPERS_SELECT_CAPTION_MORE"),
		"API_VERSION" => 3,
		"SELECTOR_OPTIONS" => [
			"contextCode" => "U",
			"context" => "INVITE_MODERATORS",
		]
	]
);
?>
</div>

<div id="<?=$tagsFieldContainerId?>" class="<?=$customContainerClass?>">
<?php
$tags = explode(",", $arResult["GROUP"]["KEYWORDS"]);
$tags = array_map(
	function ($tag)
	{
		return [
			"id" => $tag,
			"name" => $tag,
			"data" => []
		];
	},
	$tags
);
$APPLICATION->includeComponent(
	"bitrix:ui.tile.selector",
	"",
	[
		"ID" => "tags-list",
		"INPUT_NAME"=> "keywords",
		"MULTIPLE" => true,
		"LIST" => $tags,
		"CAN_REMOVE_TILES" => true,
		"SHOW_BUTTON_SELECT" => true,
		"SHOW_BUTTON_ADD" => false,
		"BUTTON_SELECT_CAPTION" => Loc::getMessage("SGCG_OPTIONS_TAGS_SELECTOR_CAPTION_MORE"),
		"BUTTON_SELECT_CAPTION_MORE" => Loc::getMessage("SGCG_OPTIONS_TAGS_SELECTOR_CAPTION_MORE"),
	]
);
?>
</div>

<script>
	BX.ready(function() {
		BX.message(<?=Json::encode($messages)?>);
		new BX.Socialnetwork.CopyingManager({
			signedParameters: "<?=$this->getComponent()->getSignedParameters()?>",
			formContainerId: "<?=$formContainerId?>",
			isProject: "<?=$isProject?>",
			isExtranet: "<?=$isExtranet?>",
			isExtranetGroup: "<?=$isExtranetGroup?>",
			isExtranetInstalled: "<?=$isExtranetInstalled?>",
			isIntranetInstalled: "<?=$isIntranetInstalled?>",
			groupData: <?=Json::encode($arResult["GROUP"])?>,
			imageFieldContainerId: "<?=$imageFieldContainerId?>",
			ownerFieldContainerId: "<?=$ownerFieldContainerId?>",
			helpersFieldContainerId: "<?=$helpersFieldContainerId?>",
			isLandingInstalled: "<?=$isLandingInstalled?>",
			tagsFieldContainerId: "<?=$tagsFieldContainerId?>",
			copyButtonId: "<?=$copyButtonId?>",
			cancelButtonId: "<?=$cancelButtonId?>"
		});
	});
</script>