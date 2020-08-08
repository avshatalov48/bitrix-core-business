<?
if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CUtil::InitJSCore(array('popup'));

$this->setFrameMode(true);

$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/socialnetwork/sonet-iframe-popup.js");
?>
<script type="text/javascript">

if (typeof(window["<?=$arParams["IFRAME_POPUP_VAR_NAME"]?>"]) == 'undefined')
{
	BX.message({
		SONET_GROUP_NAME : '<?=CUtil::JSEscape(GetMessage("SONET_GROUP_NAME"))?>',
		SONET_GROUP_TITLE_EDIT : '<?=CUtil::JSEscape(GetMessage("SONET_GROUP_TITLE_EDIT"))?>',
		SONET_GROUP_TITLE_INVITE : '<?=CUtil::JSEscape(GetMessage("SONET_GROUP_TITLE_INVITE"))?>',
		SONET_GROUP_TITLE_CREATE : '<?=CUtil::JSEscape(GetMessage("SONET_GROUP_TITLE_CREATE"))?>'
	});

	BX.ready(function() {
		window["<?=$arParams["IFRAME_POPUP_VAR_NAME"]?>"] = new BX.SonetIFramePopup({
			width: 635,
			height: <?=(IsModuleInstalled("extranet") ? "650" : "550")?>,
			pathToView: "<?=CUtil::JSEscape($arParams["PATH_TO_GROUP"])?>",
			pathToCreate: "<?=CUtil::JSEscape($arParams["PATH_TO_GROUP_CREATE"])?>",
			pathToEdit: "<?=CUtil::JSEscape($arParams["PATH_TO_GROUP_EDIT"])?>",
			pathToInvite: "<?=CUtil::JSEscape($arParams["PATH_TO_GROUP_INVITE"])?>",
			events: {
				<? if ($arParams["ON_BEFORE_SHOW"] <> ''):?>
				onBeforeShow: <?=CUtil::JSEscape($arParams["ON_BEFORE_SHOW"])?>,
				<?endif?>
				<?if ($arParams["ON_AFTER_SHOW"] <> ''):?>
				onAfterShow: <?=CUtil::JSEscape($arParams["ON_AFTER_SHOW"])?>,
				<?endif?>
				<?if ($arParams["ON_BEFORE_HIDE"] <> ''):?>
				onBeforeHide: <?=CUtil::JSEscape($arParams["ON_BEFORE_SHOW"])?>,
				<?endif?>
				<?if ($arParams["ON_AFTER_HIDE"] <> ''):?>
				onAfterHide: <?=CUtil::JSEscape($arParams["ON_AFTER_SHOW"])?>,
				<?endif?>
				onGroupAdded: <?=CUtil::JSEscape($arParams["ON_GROUP_ADDED"])?>,
				onGroupChanged: <?=CUtil::JSEscape($arParams["ON_GROUP_CHANGED"])?>,
				onGroupDeleted: <?=CUtil::JSEscape($arParams["ON_GROUP_DELETED"])?>
			}
			<?if ($arParams["GROUPS_LIST"] && is_array($arParams["GROUPS_LIST"])):?>
				,
				groupsList: <?=CUtil::PhpToJSObject($arParams["GROUPS_LIST"])?>
			<?endif?>
		});
	});
}
</script>