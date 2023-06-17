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

if 	(
	empty($arResult['TAB'])
	|| $arResult['TAB'] === 'edit'
)
{
	if (
		($arResult['POST']['CLOSED'] ?? '') !== 'Y'
		&& !$arResult['bExtranet']
		&& !ModuleManager::isModuleInstalled('im')
	)
	{
		?>
		<div class="socialnetwork-group-create-ex__content-block">
			<div class="socialnetwork-group-create-ex__content-title"><?= Loc::getMessage('SONET_GCE_T_SPAM_PERMS') ?></div>
			<div class="socialnetwork-group-create-ex__content-block --space-bottom">
				<?php

				$defaultKey = (
				isset($arResult['SpamPerms'][$arResult['POST']['SPAM_PERMS']])
					? $arResult['POST']['SPAM_PERMS']
					: array_key_first($arResult['SpamPerms'])
				);
				$defaultValue = $arResult['SpamPerms'][$defaultKey];

				?>
				<div
					class="ui-ctl ui-ctl-after-icon ui-ctl-w100 ui-ctl-dropdown sgcp-flex-nonproject --nonproject"
					data-role="soc-net-dropdown"
					data-items="<?= htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arResult['SpamPerms'])) ?>"
					data-value="<?= htmlspecialcharsbx($arResult['POST']['SPAM_PERMS']) ?>"
				>
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<div class="ui-ctl-element"><?= htmlspecialcharsEx($defaultValue) ?></div>
					<input type="hidden" name="GROUP_SPAM_PERMS" value="<?= htmlspecialcharsbx($defaultKey) ?>">
				</div>
			</div>
		</div>
		<?php
	}
	else
	{
		?><input type="hidden" value="<?= $arResult['POST']['SPAM_PERMS'] ?>" name="GROUP_SPAM_PERMS"><?php
	}
}
