<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'sidepanel',
]);

/** @var array $arParams */
/** @var array $arResult */

$template = null;
if ($arParams['AGREEMENTS'])
{
	ob_start()
	?>
	<div class="main-user-consent-selector-block" data-bx-user-consent-selector-block>
		<div class="main-user-consent-selector-block-name">
			<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_CHOOSE')?>:
		</div>
		<div class="main-user-consent-selector-block-input">
			<select class="main-user-consent-selector-block-input-item" data-bx-selector="">
				<option value=""><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEF_NOT_SELECTED') ?></option>
				<?php foreach ($arResult['LIST'] as $item):?>
					<option value="<?=htmlspecialcharsbx($item['ID'])?>">
						<?= htmlspecialcharsbx($item['NAME']) ?>
					</option>
				<?php endforeach; ?>
			</select>

			<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold"
				href="#" data-bx-link-edit="" data-bx-slider-href=""
				data-bx-slider-reload="1"
				data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_EDIT']) ?>"
			>
				<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_EDIT') ?>
			</a>
		</div>
		<div class="main-user-consent-selector-block-checkbox">
			<label class="ui-ctl ui-ctl-checkbox">
				<input
					type="hidden"
					value="N"
					data-bx-default-input
				>
				<input
					type="checkbox"
					checked
					class="ui-ctl-element"
					value="Y"
					data-bx-default-input
				/>
				<span class="ui-ctl-label-text"><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEFAULT') ?></span>
			</label>
		</div>
		<div class="main-user-consent-selector-block-checkbox">
			<label class="ui-ctl ui-ctl-checkbox">
				<input
					type="hidden"
					value="N"
					data-bx-required-input
				>
				<input
					type="checkbox"
					checked
					class="ui-ctl-element"
					value="Y"
					data-bx-required-input
				/>
				<span class="ui-ctl-label-text"><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_REQUIRED') ?></span>
			</label>
		</div>
		<div class="main-user-consent-selector-block-hint">
			<a class="main-user-consent-selector-block-link" href="#" data-bx-link-view="" data-bx-slider-href="" data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_CONSENT_LIST']) ?>">
				<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CONSENT') ?>
			</a>
		</div>
		<div class="main-user-consent-selector-block-delete">
		<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold" data-bx-delete="">
			<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_DELETE') ?>
		</a>
		</div>
	</div>
	<?php
	$template = ob_get_clean();
}
?>

<script>
	BX.ready(function () {
		new MainUserConsentSelectorManager(<?=\Bitrix\Main\Web\Json::encode([
			'actionRequestUrl' => $arParams['ACTION_REQUEST_URL'],
			'template' => $template,
			'ids' => $arParams['ID'],
			'inputName' => htmlspecialcharsbx($arParams['INPUT_NAME']),
		])?>);
	});
</script>
<div data-bx-user-consent-selector="" class="main-user-consent-selector-wrapper">
	<?php if ($arResult['DESCRIPTION']): ?>
	<div class="main-user-consent-selector-alert">
		<?= $arResult['DESCRIPTION'] ?>
	</div>
	<?php endif; ?>
	<div data-bx-user-consent-selector-blocks-container="">
		<?php if ($arParams['AGREEMENTS']): ?>
			<?php for ($index = 0; $index < count($arParams['AGREEMENTS']); $index++): ?>
				<div class="main-user-consent-selector-block" data-bx-user-consent-selector-block="">
					<div class="main-user-consent-selector-block-name">
						<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_CHOOSE')?>:
					</div>
					<div class="main-user-consent-selector-block-input">
						<select class="main-user-consent-selector-block-input-item" data-bx-selector="" name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) . "[$index][ID]" ?>">
							<option value=""><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEF_NOT_SELECTED') ?></option>
							<?php foreach ($arResult['LIST'] as $item): ?>
								<option value="<?=htmlspecialcharsbx($item['ID'])?>" <?= ($item['ID'] === (int)$arParams['AGREEMENTS'][$index]['ID'] ? 'selected' : '') ?>>
									<?= htmlspecialcharsbx($item['NAME']) ?>
								</option>
							<?php endforeach; ?>
						</select>

						<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold"
							href="#" data-bx-link-edit="" data-bx-slider-href=""
							data-bx-slider-reload="1"
							data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_EDIT']) ?>"
						>
							<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_EDIT') ?>
						</a>
					</div>
					<div class="main-user-consent-selector-block-checkbox">
						<label class="ui-ctl ui-ctl-checkbox">
							<input
								type="hidden"
								name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) . "[$index][CHECKED]" ?>"
								value="N"
								data-bx-default-input
							>
							<input
								type="checkbox"
								<?= $arParams['AGREEMENTS'][$index]['CHECKED'] === 'Y' ? 'checked' : '' ?>
								class="ui-ctl-element"
								name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) . "[$index][CHECKED]" ?>"
								value="Y"
								data-bx-default-input
							/>
							<span class="ui-ctl-label-text"><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEFAULT') ?></span>
						</label>
					</div>
					<div class="main-user-consent-selector-block-checkbox">
						<label class="ui-ctl ui-ctl-checkbox">
							<input
								type="hidden"
								name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) . "[$index][REQUIRED]" ?>"
								value="N"
								data-bx-required-input
							>
							<input
								type="checkbox"
								<?= $arParams['AGREEMENTS'][$index]['REQUIRED'] === 'Y' ? 'checked' : '' ?>
								class="ui-ctl-element"
								name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) . "[$index][REQUIRED]" ?>"
								value="Y"
								data-bx-required-input
							/>
							<span class="ui-ctl-label-text"><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_REQUIRED') ?></span>
						</label>
					</div>
					<div class="main-user-consent-selector-block-hint">
						<a class="main-user-consent-selector-block-link" href="#" data-bx-link-view="" data-bx-slider-href="" data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_CONSENT_LIST']) ?>">
							<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CONSENT') ?>
						</a>
					</div>
					<div class="main-user-consent-selector-block-delete">
						<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold" style="display: <?= count($arParams['AGREEMENTS']) >= 2 ? 'inline' : 'none' ?>" data-bx-delete="">
							<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_DELETE') ?>
						</a>
					</div>
				</div>
			<?php endfor; ?>
		<?php else: ?>
			<div class="main-user-consent-selector-block" data-bx-user-consent-selector-block="">
				<div class="main-user-consent-selector-block-name">
					<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_CHOOSE')?>:
				</div>
				<div class="main-user-consent-selector-block-input">
					<select class="main-user-consent-selector-block-input-item" data-bx-selector="" name="<?= htmlspecialcharsbx($arParams['INPUT_NAME']) ?>">
						<option value=""><?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEF_NOT_SELECTED') ?></option>
						<?php foreach ($arResult['LIST'] as $item): ?>
							<option value="<?=htmlspecialcharsbx($item['ID'])?>" <?=($item['ID'] === (int)$arParams['ID'] ? 'selected' : '')?>>
								<?= htmlspecialcharsbx($item['NAME']) ?>
							</option>
						<?php endforeach; ?>
					</select>

					<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold"
						href="#" data-bx-link-edit="" data-bx-slider-href=""
						data-bx-slider-reload="1"
						data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_EDIT']) ?>"
					>
						<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_EDIT') ?>
					</a>
				</div>
				<div class="main-user-consent-selector-block-hint">
					<a class="main-user-consent-selector-block-link" href="#" data-bx-link-view="" data-bx-slider-href="" data-bx-link-tmpl="<?= htmlspecialcharsbx($arParams['PATH_TO_CONSENT_LIST']) ?>">
						<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CONSENT') ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="main-user-consent-selector-footer">
		<?php if ($arParams['AGREEMENTS']): ?>
			<a class="main-user-consent-selector-block-link" data-bx-link-add="">
				<span class="main-user-consent-selector-block-plus-icon">&#43;</span>
				<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_ADD') ?>
			</a>
		<?php endif; ?>
		<a class="main-user-consent-selector-block-link" data-bx-slider-href="" data-bx-slider-reload="true" href="<?=htmlspecialcharsbx($arParams['PATH_TO_ADD'])?>">
			<span class="main-user-consent-selector-block-plus-icon">&#43;</span>
			<?= Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CREATE_1') ?>
		</a>
	</div>
</div>
