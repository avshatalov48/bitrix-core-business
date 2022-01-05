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

Loc::loadMessages(__FILE__);

$projectSwitchClassList = [
	'socialnetwork-group-create-ex__content-block',
	'--space-bottom',
	'socialnetwork-group-create-ex__create--switch-project'
];
if ($isProject)
{
	$projectSwitchClassList[] = '--project';
}

?>
<div class="<?= implode(' ', $projectSwitchClassList) ?>">
	<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_PARAMS_PROJECT_DATE')) ?></div>
	<div class="socialnetwork-group-create-ex__content-wrapper --white-space">
		<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
			<span class="main-ui-control main-ui-date main-grid-panel-date">
				<span class="main-ui-date-button"></span>
				<input type="text" name="PROJECT_DATE_START" autocomplete="off" data-time="" class="main-ui-control-input main-ui-date-input" value="<?= (!empty($arResult['POST']['PROJECT_DATE_START']) ? ConvertTimeStamp(MakeTimeStamp($arResult['POST']['PROJECT_DATE_START'])) : '') ?>">
				<div class="main-ui-control-value-delete<?= (empty($arResult["POST"]["PROJECT_DATE_START"]) ? " main-ui-hide" : "") ?>">
					<span class="main-ui-control-value-delete-item"></span>
				</div>
			</span>
		</div>
		<div class="socialnetwork-group-create-ex__date-between"></div>
		<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
			<span class="main-ui-control main-ui-date main-grid-panel-date">
				<span class="main-ui-date-button"></span>
				<input type="text" name="PROJECT_DATE_FINISH" autocomplete="off" data-time="" class="main-ui-control-input main-ui-date-input" value="<?= (!empty($arResult['POST']['PROJECT_DATE_FINISH']) ? ConvertTimeStamp(MakeTimeStamp($arResult['POST']['PROJECT_DATE_FINISH'])) : '') ?>">
				<div class="main-ui-control-value-delete<?= (empty($arResult["POST"]["PROJECT_DATE_FINISH"]) ? " main-ui-hide" : "") ?>">
					<span class="main-ui-control-value-delete-item"></span>
				</div>
			</span>
		</div>
	</div>
</div>
<?php
