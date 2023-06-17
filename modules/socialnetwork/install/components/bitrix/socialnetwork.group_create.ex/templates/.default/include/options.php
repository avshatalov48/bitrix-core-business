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
	?>
	<div class="socialnetwork-group-create-ex__content-block --space-bottom--xl">
		<?php
		$classList = [ 'socialnetwork-group-create-ex__project-instruments' ];
		if (empty($arResult['TAB']))
		{
			$classList[] = 'socialnetwork-group-create-ex__create--switch-nonscrum';
			if ($isScrumProject)
			{
				$classList[] = '--scrum';
			}
		}
		?>
		<div class="<?= implode(' ', $classList) ?>">
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TITLE_TYPE') ?></div>
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?> socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TITLE_TYPE_PROJECT') ?></div>
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TITLE_TYPE_SCRUM') ?></div>
			<?php
			$labelClassList = [
				'ui-ctl',
				'ui-ctl-checkbox',
			];

			if (
				$arResult['bExtranet']
				|| (
					$arResult['USE_PRESETS'] === 'Y'
					&& empty($arResult['TAB'])
				)
			)
			{
				?><input type="hidden" value="<?= ($arResult['POST']['VISIBLE'] === 'Y') ? 'Y' : 'N' ?>" name="GROUP_VISIBLE" id="GROUP_VISIBLE"><?php
			}
			else
			{
				$checked = ($arResult['POST']['VISIBLE'] === 'Y');
				$disabled = ($arResult['POST']['IS_EXTRANET_GROUP'] === 'Y');

				?>
				<div class="socialnetwork-group-create-ex__project-instruments--item">
					<label class="<?= implode(' ', $labelClassList) ?>">
						<input type="checkbox" id="GROUP_VISIBLE" name="GROUP_VISIBLE" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_VIS2_HINT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_VIS2') ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_VIS2_HINT_PROJECT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_VIS2_PROJECT') ?></div>
					</label>
				</div>
				<?php
			}

			if (
				$arResult['bExtranet']
				|| (
					$arResult['USE_PRESETS'] === 'Y'
					&& empty($arResult['TAB'])
				)
			)
			{
				?>
				<input
					type="hidden"
					value="<?= (($arResult['POST']['OPENED'] ?? '') === 'Y') ? 'Y' : 'N' ?>"
					name="GROUP_OPENED"
					id="GROUP_OPENED"
				><?php
			}
			else
			{
				$checked = ($arResult['POST']['OPENED'] === 'Y');
				$disabled = ($arResult['POST']['IS_EXTRANET_GROUP'] === 'Y');

				?>
				<div class="socialnetwork-group-create-ex__project-instruments--item">
					<label class="<?= implode(' ', $labelClassList) ?>">
						<input type="checkbox" id="GROUP_OPENED" name="GROUP_OPENED" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_OPEN2_HINT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_OPEN2') ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_OPEN2_HINT_PROJECT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_OPEN2_PROJECT') ?></div>
					</label>
				</div>
				<?php
			}

			if ((int)$arResult['GROUP_ID'] > 0)
			{
				$checked = ($arResult['POST']['CLOSED'] === 'Y');

				?>
				<div class="socialnetwork-group-create-ex__project-instruments--item">
					<label class="<?= implode(' ', $labelClassList) ?>">
						<input type="checkbox" id="GROUP_CLOSED" name="GROUP_CLOSED" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?>>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_CLOSED2_HINT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_CLOSED2') ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>" title="<?= Loc::getMessage('SONET_GCE_T_PARAMS_CLOSED2_HINT_PROJECT') ?>"><?= Loc::getMessage('SONET_GCE_T_PARAMS_CLOSED2_PROJECT') ?></div>
					</label>
				</div>
				<?php
			}


			if ($arResult['intranetInstalled'])
			{
				$setByOptions = isset($arParams['PROJECT_OPTIONS']['project']);
				$disabled = ($setByOptions || $isScrumProject);
				$checked = (
					$setByOptions
						? $arParams['PROJECT_OPTIONS']['project'] === true || $arParams['PROJECT_OPTIONS']['project'] === 'true'
						: $isProject
				);

				if (
					$disabled
					|| (
						$arResult['USE_PRESETS'] === 'Y'
						&& empty($arResult['TAB'])
					)
				)
				{
					?><input type="hidden" id="GROUP_PROJECT" name="GROUP_PROJECT" value="<?= ($checked ? 'Y' : '') ?>"><?php
				}

				if (!(
					$arResult['USE_PRESETS'] === 'Y'
					&& empty($arResult['TAB'])
				))
				{
					?>
					<div class="socialnetwork-group-create-ex__project-instruments--item socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?>">
						<label class="<?= implode(' ', $labelClassList) ?>" <?= ($disabled ? 'style="pointer-events: none"' : '') ?>>
							<input type="checkbox" id="GROUP_PROJECT" name="GROUP_PROJECT" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
							<div class="ui-ctl-label-text"><?= Loc::getMessage('SONET_GCE_T_PARAMS_PROJECT') ?></div>
						</label>
					</div>
					<?php
				}
			}

			if ($arResult['landingInstalled'])
			{
				$setByOptions = isset($arParams['PROJECT_OPTIONS']['landing']);
				$disabled = $setByOptions;
				$checked = (
					$setByOptions
						? $arParams['PROJECT_OPTIONS']['landing'] === true || $arParams['PROJECT_OPTIONS']['landing'] === 'true'
						: ($arResult['POST']['LANDING'] ?? '') === 'Y'
				);

				if ($disabled)
				{
					?><input type="hidden" name="GROUP_LANDING" value="<?= ($checked ? 'Y' : '') ?>"><?php
				}

				?>
				<div class="socialnetwork-group-create-ex__project-instruments--item socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?>">
					<label class="<?= implode(' ', $labelClassList) ?>" <?= ($disabled ? 'style="pointer-events: none"' : '') ?>>
						<input type="checkbox" id="GROUP_LANDING" name="GROUP_LANDING" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
						<div class="ui-ctl-label-text"><?= Loc::getMessage('SONET_GCE_T_PARAMS_LANDING') ?></div>
					</label>
				</div>
				<?php
			}
			?>
		</div>
		<?php

		if ($arResult['bExtranetInstalled'])
		{
			$setByOptions = isset($arParams['PROJECT_OPTIONS']['extranet']);
			$disabled = $setByOptions;
				$checked = (
				$setByOptions
					? ($arParams['PROJECT_OPTIONS']['extranet'] === true || $arParams['PROJECT_OPTIONS']['extranet'] === 'true')
					: (($arResult["POST"]['IS_EXTRANET_GROUP'] ?? '') === 'Y')
			);

			if ($disabled)
			{
				?><input type="hidden" name="IS_EXTRANET_GROUP" value="<?= ($checked ? 'Y' : '') ?>"><?php
			}

			?>
			<div class="socialnetwork-group-create-ex__content-title --without-margin"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IS_EXTRANET_TITLE')) ?></div>
			<div class="socialnetwork-group-create-ex__project-instruments">
				<div class="socialnetwork-group-create-ex__project-instruments--item">
					<label class="<?= implode(' ', $labelClassList) ?>" <?= ($disabled ? 'style="pointer-events: none"' : '') ?>>
						<input type="checkbox" id="IS_EXTRANET_GROUP" name="IS_EXTRANET_GROUP" class="ui-ctl-element" value="Y" <?= ($checked ? 'checked' : '') ?> <?= ($disabled ? 'disabled' : '') ?>>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>" title="<?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_IS_EXTRANET_GROUP2_HINT')) ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IS_EXTRANET_GROUP3')) ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>" title="<?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_IS_EXTRANET_GROUP2_HINT_PROJECT')) ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IS_EXTRANET_GROUP3')) ?></div>
					</label>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
