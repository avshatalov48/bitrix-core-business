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

if (
	empty($arResult['TAB'])
	|| $arResult['TAB'] === 'edit'
)
{
	if (count($arResult['Subjects'] ?? []) === 1)
	{
		$arKeysTmp = array_keys($arResult['Subjects']);
		?><input type="hidden" name="GROUP_SUBJECT_ID" value="<?= (int)$arKeysTmp[0] ?>"><?php
	}
	else
	{
		?>
		<div class="socialnetwork-group-create-ex__content-block socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?>">
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_SUBJECT') ?></div>
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_SUBJECT_PROJECT') ?></div>
			<div class="socialnetwork-group-create-ex__content-block --space-bottom">
				<?php

				$defaultKey = (
					isset($arResult['Subjects'][($arResult['POST']['SUBJECT_ID'] ?? 0)])
						? ($arResult['POST']['SUBJECT_ID'] ?? 0)
						: array_key_first($arResult['Subjects'] ?? [])
				);
				$defaultValue = $arResult['Subjects'][$defaultKey];
				?>
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100 ui-ctl-dropdown"
					 data-role="soc-net-dropdown"
					 data-items="<?= htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arResult['Subjects'])) ?>"
					 data-value="<?= htmlspecialcharsbx($arResult['POST']['SUBJECT_ID'] ?? 0) ?>"
					 data-sonet-control-id="project-theme">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<div class="ui-ctl-element"><?= htmlspecialcharsEx($defaultValue) ?></div>
					<input type="hidden" name="GROUP_SUBJECT_ID" value="<?= htmlspecialcharsbx($defaultKey) ?>">
				</div>
			</div>
		</div>
		<?php
	}
}
