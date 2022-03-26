<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $isScrumProject */
/** @var int $scrumMasterId */

Loc::loadMessages(__FILE__);

?>
<script>
	BX.message({
		SONET_GCE_T_CHANGE_SCRUM_MASTER: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER')) ?>',
		SONET_GCE_T_CHANGE_SCRUM_MASTER_MORE: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER_MORE')) ?>',
	});
</script>
<div class="socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>" style="margin-bottom: 15px">
	<div class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_SCRUM_MASTER') ?></div>
	<div>
		<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="SCRUM_MASTER_selector"></div>
	</div>
	<div id="SCRUM_MASTER_CODE_container"><?php
		if ($scrumMasterId > 0)
		{
			?>
			<input type="hidden" name="SCRUM_MASTER_CODE" value="<?= 'U' . $scrumMasterId ?>">
			<?php
		}
	?></div>
</div>
<?php
