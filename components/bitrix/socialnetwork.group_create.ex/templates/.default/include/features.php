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
<div class="socialnetwork-group-create-ex__content-block --space-bottom--xl" id="additional-block-features">
	<div class="socialnetwork-group-create-ex__content-title --without-margin"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_TAB_2')) ?></div>
	<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_FEATURES_DESCRIPTION')) ?>&nbsp;<span class="ui-hint" data-hint="<?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_FEATURES_HINT')) ?>"><span class="ui-hint-icon"></span></span></div>
	<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_FEATURES_DESCRIPTION_PROJECT')) ?>&nbsp;<span class="ui-hint" data-hint="<?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_FEATURES_HINT')) ?>"><span class="ui-hint-icon"></span></span></div>
	<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_FEATURES_DESCRIPTION_SCRUM')) ?>&nbsp;<span class="ui-hint" data-hint="<?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_FEATURES_HINT')) ?>"><span class="ui-hint-icon"></span></span></div>
	<div class="socialnetwork-group-create-ex__project-instruments">
		<?php
		foreach ($arResult['POST']['FEATURES'] as $feature => $featureData)
		{
			$customTitle = false;
			$featureTitle = (string)(
				!empty($arResult['arSocNetFeaturesSettings'][$feature]['title'])
				&& (string)$arResult['arSocNetFeaturesSettings'][$feature]['title'] !== ''
					? $arResult['arSocNetFeaturesSettings'][$feature]['title']
					: Loc::getMessage('SONET_FEATURES_' . $feature . '_GROUP')
			);
			$featureTitleOriginal = $featureTitle;

			if ($featureTitle === '')
			{
				$featureTitle = Loc::getMessage('SONET_FEATURES_' . $feature);
				$featureTitleOriginal = $featureTitle;
			}

			if ((string)$arResult['POST']['FEATURES'][$feature]['FeatureName'] !== '')
			{
				$customTitle = ((string)$arResult['POST']['FEATURES'][$feature]['FeatureName'] !== $featureTitle);
				$featureTitle = (string)$arResult['POST']['FEATURES'][$feature]['FeatureName'];
			}

			$featureActive = ($featureData['Active'] ? 'Y' : 'N');

			if (
				$feature === 'search'
				&& SITE_TEMPLATE_ID === 'bitrix24'
			)
			{
				?><input type="hidden" name="<?= $feature ?>_active"  value="<?= $featureActive ?>">
				<input type="hidden" name="<?= $feature ?>_name" value="<?= ($customTitle ? $featureTitle : '') ?>"><?php
			}
			else
			{
				$setByOptions = (isset($arParams['PROJECT_OPTIONS']['features'][$feature]));
				$disabled = $setByOptions;
				$checked = (
					$setByOptions
						? ($arParams['PROJECT_OPTIONS']['features'][$feature] !== false && $arParams['PROJECT_OPTIONS']['features'][$feature] !== 'false')
						: $featureData['Active']
				);

				$classList = [
					'socialnetwork-group-create-ex__project-instruments--item',
				];

				if ($customTitle)
				{
					$classList[] = '--custom-value';
				}

				?>
				<div class="<?= implode(' ', $classList )?>">
					<label class="ui-ctl ui-ctl-checkbox ui-ctl-inline">
						<input name="<?= htmlspecialcharsbx($feature) ?>_active" type="checkbox" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
						<?php
						if ($disabled)
						{
							?><input name="<?= htmlspecialcharsbx($feature) ?>_active" type="hidden" value="<?= ($checked ? 'Y' : '')?>" ><?php
						}
						?>
						<div class="ui-ctl-label-text" data-role="feature-label"><?= htmlspecialcharsEx($featureTitleOriginal) ?></div>
					</label>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
						<input name="<?= htmlspecialcharsbx($feature) ?>_name" type="text" class="ui-ctl-element" data-role="feature-input-text" value="<?= ($customTitle ? htmlspecialcharsbx($featureTitle) : '') ?>">
					</div>
					<div class="socialnetwork-group-create-ex__project-instruments--icon-action --edit"></div>
					<div class="socialnetwork-group-create-ex__project-instruments--icon-action --revert"></div>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
<?php
