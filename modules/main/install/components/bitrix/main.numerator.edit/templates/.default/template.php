<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");
if ($arResult['IS_SLIDER'])
{
	\CJSCore::init("sidepanel");
	$APPLICATION->RestartBuffer();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<? $APPLICATION->ShowHead(); ?>
	</head>
	<body>
<? } ?>
	<div class="<?= $arResult['IS_SLIDER'] ? 'main-numerator-edit-slider' : '' ?>
	<?= htmlspecialcharsbx(isset($arParams['CSS_WRAP_CLASS']) ? $arParams['CSS_WRAP_CLASS'] : ''); ?>">
		<div class="main-numerator-edit-title">
			<div class="pagetitle-wrap">
				<div class="pagetitle-inner-container">
					<div class="pagetitle">
						<span class="pagetitle-item "><?
							?><?= $arResult['IS_EDIT']
								? Loc::getMessage('NUMERATOR_EDIT_UPDATE_PAGE_TITLE')
								: Loc::getMessage('NUMERATOR_EDIT_CREATE_PAGE_TITLE');
						?></span>
					</div>
				</div>
			</div>
		</div>

		<div class="main-numerator-edit-wrap">
			<form action="" method="post" data-role="numerator-edit-form">
				<? foreach ($arResult['numeratorSettingsFields'][Numerator::getType()] as $setting) : ?>
					<? $attributeName = htmlspecialcharsbx(Numerator::getType() . '[' . $setting['settingName'] . ']'); ?>
					<? if ($setting['type'] == 'hidden'): ?>
						<input type="hidden"
							   name="<?= $attributeName ?>"
							   value="<?= htmlspecialcharsbx($setting['value']); ?>"
							   data-role="numerator-hidden-<?= htmlspecialcharsbx($setting['settingName']); ?>-input">
						<? continue; ?>
					<? endif; ?>
					<div class="main-numerator-edit-box"
						<? if ($setting['settingName'] == 'name'
								&& $arResult['HIDE_NUMERATOR_NAME']): ?>
							style="display: none"
						<? endif; ?>>
						<div class="main-numerator-edit-caption"><?= $setting['title']; ?></div>
						<? if ($setting['settingName'] == 'template'): ?>
							<div class="main-numerator-edit-template main-numerator-edit-input"
								 contenteditable="true" role="textbox" aria-multiline="false"
								 data-name="<?= $attributeName; ?>"
								 data-role="numerator-template-input"
								 data-value="<?= htmlspecialcharsbx($setting['value']); ?>"
							></div>
							<a href="<?= htmlspecialcharsbx(Loc::getMessage('MAIN_NUMERATOR_EDIT_HELP_ARTICLE')); ?>"
							   class="main-numerator-edit-tooltip main-numerator-edit-tooltip-big"></a>
						<div class="main-numerator-edit-word-btn-wrapper">
							<? foreach ($arResult['numeratorTemplateWords'] as $type => $numeratorTemplateWords) : ?>
								<? foreach ($numeratorTemplateWords as $wordCode => $numeratorTemplateWordTitle) : ?>
									<button class="main-numerator-edit-template-word-btn" href="#"
											data-role="numerator-template-word-btn"
											data-type="<?= htmlspecialcharsbx($type); ?>"
											data-word="<?= htmlspecialcharsbx($wordCode); ?>">
										<?= htmlspecialcharsbx($numeratorTemplateWordTitle); ?><?
										?></button>
								<? endforeach; ?>
							<? endforeach; ?>
						</div>
						<? else: ?>
							<input type="<?= $setting['type'] == 'string' ? 'text' : 'number'; ?>"
								   value="<?= htmlspecialcharsbx($setting['value'] ? $setting['value'] : $this->getComponent()->getDefaultValueFromSettings($setting)); ?>"
								   class="main-numerator-edit-input"
								<? if ($setting['settingName'] == 'name'): ?>
									data-role="numerator-name-input"
								<? endif; ?>
								   name="<?= $attributeName; ?>"
							>
						<? endif; ?>
					</div>
				<? endforeach; ?>
				<div class="">
					<? foreach ($arResult['numeratorSettingsFields'] as $settingsTypeName => $settings) : ?>
						<? if ($settingsTypeName == Numerator::getType())
						{
							continue;
						} ?>
						<div class="main-numerator-edit-hide"
							 data-role="settings-type-<?= htmlspecialcharsbx($settingsTypeName); ?>">
							<? foreach ($settings as $setting) : ?>
								<? if ($setting['settingName'] == 'isDirectNumeration'
									   && $arResult['HIDE_IS_DIRECT_NUMERATION']): ?>
									<? continue; ?>
								<? endif; ?>
								<? $attributeName = htmlspecialcharsbx($settingsTypeName . '[' . $setting['settingName'] . ']'); ?>
								<? if (in_array($setting['type'], ['boolean'])): ?>
									<div class="main-numerator-edit-field-wrap">
										<div class="main-numerator-edit-label-box">
											<label class="main-numerator-edit-label" for="checkbox<?= htmlspecialcharsbx($setting['settingName']); ?>">
												<input type="hidden" name="<?= $attributeName; ?>" value="0">
												<input id="checkbox<?= htmlspecialcharsbx($setting['settingName']); ?>"
													   <? if ($setting['value']): ?>checked<? endif; ?>
													   class="main-numerator-edit-checkbox"
													   type="checkbox"
													   name="<?= $attributeName; ?>"
													   value="1">
												<div class="main-numerator-edit-caption"><?= htmlspecialcharsbx($setting['title']); ?></div>
											</label>
											<a href="<?= htmlspecialcharsbx(Loc::getMessage('MAIN_NUMERATOR_EDIT_HELP_ARTICLE')); ?>"
											   class="main-numerator-edit-tooltip"></a>
										</div>
									</div>
								<? elseif (in_array($setting['type'], ['string', 'int'])): ?>
									<div class="main-numerator-edit-field-wrap">
										<div class="main-numerator-edit-caption"><?= htmlspecialcharsbx($setting['title']); ?></div>
										<input type="<?= $setting['type'] == 'string' ? 'text' : 'number'; ?>"
											   class="main-numerator-edit-input "
											   value="<?= htmlspecialcharsbx($setting['value'] ? $setting['value'] : $this->getComponent()->getDefaultValueFromSettings($setting)); ?>"
											   name="<?= $attributeName; ?>"
										>
									</div>
								<? elseif (in_array($setting['type'], ['array'])): ?>
									<? if ($setting['settingName'] == 'timezone'):?>
										<div class="main-numerator-edit-field-wrap <?= $setting['value'] ? '' : 'main-numerator-edit-hide'; ?>"
											data-role="numerator-timezones">
									<? else: ?>
										<div class="main-numerator-edit-field-wrap">
									<? endif; ?>
										<div class="main-numerator-edit-caption"><?= htmlspecialcharsbx($setting['title']); ?></div>
										<select class="main-numerator-edit-select" name="<?= $attributeName; ?>"
											<?= ($setting['settingName'] == 'periodicBy') ? 'data-role="numerator-period-select"': '' ?>>
											<? foreach ($setting['values'] as $attributeSettings) : ?>

												<option value="<?= htmlspecialcharsbx($attributeSettings['value']); ?>"
													<? if ($setting['value'] == $attributeSettings['value']): ?> selected <? endif; ?>
												>
													<?= htmlspecialcharsbx($setting['settingName'] == 'periodicBy' ? $attributeSettings['title'] : $attributeSettings['settingName']) ?>
												</option>
											<? endforeach; ?>
										</select>
										<? if ($setting['settingName'] == 'periodicBy'): ?>
										<div class="main-numerator-edit-control-box">
											<div class="main-numerator-edit-caption main-numerator-edit-link"
											data-role="numerator-timezone-toggle"><?= Loc::getMessage('NUMERATOR_EDIT_TIMEZONE_LINK'); ?></div>
										</div>
										<? endif; ?>
									</div>
								<? endif; ?>
							<? endforeach; ?>
						</div>
					<? endforeach; ?>
				</div>
				<div class="main-numerator-edit-buttons">
					<div class="main-numerator-edit-buttons-inner">
						<button class="ui-btn ui-btn-md ui-btn-success main-numerator-edit-btn-save" data-role="btn-save"><?= Loc::getMessage('NUMERATOR_EDIT_BTN_SAVE'); ?></button>
						<button class="ui-btn ui-btn-md ui-btn-light main-numerator-edit-btn-cancel" data-role="btn-cancel"><?= Loc::getMessage('NUMERATOR_EDIT_BTN_CANCEL'); ?></button>
					</div>
				</div>

				<script>
					BX.ready(function ()
					{
						new BX.Numerator({
							errors: {
								emptyField: "<?= CUtil::JSEscape(Loc::getMessage('NUMERATOR_EDIT_FORM_EMPTY_FIELD_ERROR'))?>"
							},
							isSlider: "<?= CUtil::JSEscape($arResult['IS_SLIDER'])?>",
							defaultDelimiter: '/'
						});
					});
				</script>
			</form>
		</div>
	</div>
<? if ($arResult['IS_SLIDER'])
{
	?>
	</body>
	</html>
<? } ?>