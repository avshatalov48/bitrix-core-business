<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

if ($arParams["USE_KEYWORDS"] === "Y")
{
	?>
	<script>
	BX.message({
		SONET_GCE_T_TAG_ADD: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_TAG_ADD')) ?>',
		SONET_GCE_T_KEYWORDS_ADD_TAG: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_KEYWORDS_ADD_TAG')) ?>',
		SONET_GCE_T_TAG_SEARCH_FAILED: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_TAG_SEARCH_FAILED')) ?>',
		SONET_GCE_T_TAG_SEARCH_ADD_HINT: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_TAG_SEARCH_ADD_HINT')) ?>',
		SONET_GCE_T_TAG_SEARCH_ADD_FOOTER_LABEL: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_TAG_SEARCH_ADD_FOOTER_LABEL')) ?>',
	});
	</script>
	<?php

	?><div class="socialnetwork-group-create-ex__content-block --space-bottom">
		<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_KEYWORDS')) ?></div>
		<div id="group-tags-bind-node"></div>
		<input
			type="hidden"
			name="GROUP_KEYWORDS"
			id="GROUP_KEYWORDS"
			value="<?= htmlspecialcharsbx($arResult['POST']['KEYWORDS'] ?? '') ?>"
		>
	</div><?php


}
