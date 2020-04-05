<?
if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CUtil::InitJSCore(array('popup'));
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/main/core/css/core_finder.css");

CUtil::InitJSCore(array('popup'));
?>
<script type="text/javascript">
	var <?php echo strlen($arParams["JS_OBJECT_NAME"]) ? CUtil::JSEscape($arParams["JS_OBJECT_NAME"]) : "groupsPopup"?>;
	var __bx_group_site_id = '<?=CUtil::JSEscape(SITE_ID)?>';

	BX.message({
		SONET_GROUP_TABS_LAST : "<?php echo GetMessage("SONET_GROUP_TABS_LAST")?>",
		SONET_GROUP_TABS_MY : "<?php echo GetMessage("SONET_GROUP_TABS_MY")?>",
		SONET_GROUP_TABS_SEARCH : "<?php echo GetMessage("SONET_GROUP_TABS_SEARCH")?>",
		SONET_GROUP_BUTTON_CLOSE : "<?php echo GetMessage("SONET_GROUP_BUTTON_CLOSE")?>"
	});

	BX.ready(function() {
		<?php echo strlen($arParams["JS_OBJECT_NAME"]) ? CUtil::JSEscape($arParams["JS_OBJECT_NAME"]) : "groupsPopup"?> = BX.GroupsPopup.create("sonet-group-popup-<?php echo RandString(8)?>", <?php if (strlen($arParams["BIND_ELEMENT"])):?>BX("<?php echo CUtil::JSEscape($arParams["BIND_ELEMENT"])?>")<?php else:?>null<?php endif?>, {
			lastGroups: <?php echo CUtil::PhpToJsObject($arResult["LAST_GROUPS"])?>,
			myGroups: <?php echo CUtil::PhpToJsObject($arResult["MY_GROUPS"])?>,
			selected: <?php echo CUtil::PhpToJsObject($arResult["SELECTED"])?>,
			<?php if (isset($arParams["FEATURES_PERMS"]) && sizeof($arParams["FEATURES_PERMS"]) == 2):?>
				featuresPerms: <?php echo CUtil::PhpToJsObject($arParams["FEATURES_PERMS"])?>,
			<?php endif?>
			<?php if (isset($arParams["SEARCH_INPUT"])):?>
				searchInput: BX("<?php echo CUtil::JSEscape($arParams["SEARCH_INPUT"])?>"),
			<?php endif?>
			events: {
				<?php if (strlen($arParams["ON_SELECT"])):?>onGroupSelect: <?php echo CUtil::JSEscape($arParams["ON_SELECT"])?><?php endif?>
			}
		});
	});
</script>
