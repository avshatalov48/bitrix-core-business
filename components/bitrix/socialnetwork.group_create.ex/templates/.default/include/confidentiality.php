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

if (
	empty($arResult['TAB'])
	&& !empty($arResult['ConfidentialityTypes'])
)
{
	$confidentialityTypeCode = \Bitrix\Socialnetwork\Helper\Workgroup::getConfidentialityTypeCodeByParams([
		'typesList' => $arResult['ProjectTypes'],
		'fields' => $arResult['POST'],
	]);

	$projectSwitchClassList = [
		'socialnetwork-group-create-ex__content-block',
		'--space-bottom',
	];

	?>
	<div class="socialnetwork-group-create-ex__content-title --without-margin"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_SLIDE_CONFIDENTIALITY_TITLE_1')) ?></div>
	<div class="<?= implode(' ', $projectSwitchClassList) ?>">
		<div class="socialnetwork-group-create-ex__text --m ui-ctl-label-text"><?= Loc::getMessage('SONET_GCE_T_SLIDE_CONFIDENTIALITY_SUBTITLE')?></div>
	</div>
	<div class="socialnetwork-group-create-ex__content-wrapper socialnetwork-group-create-ex__type-confidentiality-wrapper">
		<?php
		foreach ($arResult['ConfidentialityTypes'] as $code => $type)
		{
			$classList = [
				'socialnetwork-group-create-ex__group-selector',
			];
			if ($confidentialityTypeCode === $code)
			{
				$classList[] = '--active';
			}

			?>
			<div class="<?= implode(' ', $classList)?>" data-bx-confidentiality-type="<?= htmlspecialcharsbx($code) ?>">
				<div class="socialnetwork-group-create-ex__group-selector--logo --access-<?= htmlspecialcharsbx($code) ?>"></div>
				<div class="socialnetwork-group-create-ex__group-selector--container">
					<div class="socialnetwork-group-create-ex__group-selector--title"><?= htmlspecialcharsEx($type['NAME']) ?></div>
					<div class="socialnetwork-group-create-ex__group-selector--description"><?= htmlspecialcharsEx($type['DESCRIPTION']) ?></div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
