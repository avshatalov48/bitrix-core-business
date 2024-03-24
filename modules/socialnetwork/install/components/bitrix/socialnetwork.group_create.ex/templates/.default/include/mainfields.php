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
/** @var boolean $isProject */
/** @var boolean $isScrumProject */

Loc::loadMessages(__FILE__);

?>
<script>
	BX.message({
		SONET_GCE_T_NAME3: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_NAME3')) ?>',
		SONET_GCE_T_NAME3_PROJECT: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_NAME3_PROJECT')) ?>',
		SONET_GCE_T_NAME3_SCRUM: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_NAME3_SCRUM')) ?>',
	});
</script>
<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_NAME3')) ?></div>
<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_NAME3_PROJECT')) ?></div>
	<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_NAME3_SCRUM')) ?></div>
<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="GROUP_NAME_wrapper">
	<input
		id="GROUP_NAME_input"
		name="GROUP_NAME"
		type="text"
		class="ui-ctl-element"
		placeholder="<?= htmlspecialcharsbx($isProject ? Loc::getMessage('SONET_GCE_T_NAME3_PROJECT') : Loc::getMessage('SONET_GCE_T_NAME3')) ?>"
		value="<?= ((string) ($arResult['POST']['NAME'] ?? '') !== '' ? $arResult['POST']['NAME'] : '') ?>"
	>
</div>
<?php

$descriptionExpandable = (
	(string) ($arResult['POST']['DESCRIPTION'] ?? '') === ''
	&& \Bitrix\Main\Context::getCurrent()->getRequest()->get('focus') !== 'description'
);

if ($descriptionExpandable)
{
	?>
	<div class="socialnetwork-group-create-ex__content-block">
		<div class="ui-ctl ui-ctl-file-link">
			<div class="ui-ctl-label-text" data-role="socialnetwork-group-create-ex__expandable" for="expandable-id-1" data-sonet-control-id="add-description"><?= Loc::getMessage('SONET_GCE_T_DESCRIPTION_SWITCHER') ?></div>
		</div>
	</div>
	<?php
}

$classList = [];
$classList[] = (
	$descriptionExpandable
		? 'socialnetwork-group-create-ex__content-expandable'
		: 'socialnetwork-group-create-ex__content-expanded'
);
?>
<div class="socialnetwork-group-create-ex__content-block">
	<div id="expandable-id-1" class="<?= implode(' ', $classList) ?>">
		<div class="socialnetwork-group-create-ex__content-expandable--wrapper">
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION')) ?></div>
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION_PROJECT')) ?></div>
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION_SCRUM')) ?></div>
			<div class="ui-ctl ui-ctl-textarea ui-ctl-resize-y">
				<textarea
					id="GROUP_DESCRIPTION_input"
					name="GROUP_DESCRIPTION"
					class="ui-ctl-element"
				><?=
					((string) ($arResult['POST']['DESCRIPTION'] ?? '') !== ''
						? $arResult['POST']['DESCRIPTION']
						: ''
					) ?></textarea>
			</div>
			<div class="socialnetwork-group-create-ex__text --xs ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION_LABEL')) ?></div>
			<div class="socialnetwork-group-create-ex__text --xs ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION_LABEL_PROJECT')) ?></div>
			<div class="socialnetwork-group-create-ex__text --xs ui-ctl-label-text socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_DESCRIPTION_LABEL_SCRUM')) ?></div>
		</div>
	</div>
</div>
<?php
