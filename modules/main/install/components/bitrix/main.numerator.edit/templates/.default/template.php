<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.alerts',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.hint',
]);

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
	<?= htmlspecialcharsbx($arParams['CSS_WRAP_CLASS'] ?? ''); ?>"
	>
		<? if (!$arResult['IS_HIDE_PAGE_TITLE']): ?>
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
		<? endif; ?>

		<div class="main-numerator-edit-wrap" data-role="numerator-container">
			<?php
			if ($arResult['WITHOUT_FORM']):
			?>
			<div data-role="numerator-edit-form">
			<?php
			else:
			?>
			<form action="" method="post" data-role="numerator-edit-form">
			<?php
			endif;
				foreach ($arResult['numeratorSettingsFields'][Numerator::getType()] as $setting) : ?>
					<? $attributeName = htmlspecialcharsbx(Numerator::getType() . '[' . $setting['settingName'] . ']'); ?>
					<? if ($setting['type'] == 'hidden'): ?>
						<input type="hidden"
							   name="<?= $attributeName ?>"
							   value="<?= htmlspecialcharsbx($setting['value']); ?>"
							   data-role="numerator-hidden-<?= htmlspecialcharsbx($setting['settingName']); ?>-input">
						<? continue; ?>
					<? endif; ?>
					<div class="main-numerator-edit-box">
						<div class="main-numerator-edit-caption"><?= $setting['title']; ?></div>
						<? if ($setting['settingName'] == 'template'): ?>
							<div class="main-numerator-edit-tooltip main-numerator-edit-tooltip-big"
								data-role="help-article-toggle"></div>
							<div class="main-numerator-edit-template main-numerator-edit-input"
								 contenteditable="true" role="textbox" aria-multiline="false"
								 data-name="<?= $attributeName; ?>"
								 data-role="numerator-template-input"
								 data-value="<?= htmlspecialcharsbx($setting['value']); ?>"
							></div>
						<div class="main-numerator-edit-word-btn-wrapper" data-role="numerator-edit-word-btn-wrapper">
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
								   value="<?= htmlspecialcharsbx($setting['value'])?>"
								   class="main-numerator-edit-input"
								   data-role="numerator-<?= htmlspecialcharsbx($setting['settingName']); ?>-input"
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
								<? $attributeName = htmlspecialcharsbx($settingsTypeName . '[' . $setting['settingName'] . ']'); ?>
								<? if ($setting['type'] == 'hidden'): ?>
									<input type="hidden"
										   name="<?= $attributeName ?>"
										   value="<?= htmlspecialcharsbx($setting['value']); ?>"
										   data-role="numerator-hidden-<?= htmlspecialcharsbx($setting['settingName']); ?>-input">
									<? continue; ?>
								<? endif; ?>
								<? if ($setting['settingName'] == 'currentNumberForSequence'): ?>
									<? if (isset($setting['value'])): ?>
										<div class="main-numerator-edit-caption">
											<?= Loc::getMessage('NUMERATOR_EDIT_TITLE_BITRIX_MAIN_SEQUENTNUMBERGENERATOR_NEXT_NUMBER').' - '. htmlspecialcharsbx($setting['value']); ?>
										</div>
									<? endif; ?>
									<div class="main-numerator-edit-field-wrap">
										<div class="main-numerator-edit-caption main-numerator-edit-link"
											 data-role="numerator-set-next-number-toggle">
											<?= $setting['toggleTitle']; ?>
										</div>
									</div>
								<? endif; ?>
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
											<div class="main-numerator-edit-tooltip"
											   data-role="help-article-toggle"></div>
										</div>
									</div>
								<? elseif (in_array($setting['type'], ['string', 'int'])): ?>
									<?php
									$extraCssClass = '';
									if (in_array($setting['settingName'], ['padString', 'length'], true))
									{
										$extraCssClass .= ' main-numerator-edit-field-wrap-half ';
									}
									?>
									<div class="main-numerator-edit-field-wrap <?php echo $extraCssClass; ?>"
											data-role="<?= htmlspecialcharsbx($setting['settingName']); ?>-wrapper"
									>
										<div class="main-numerator-edit-caption"><?= htmlspecialcharsbx($setting['title']); ?>
											<? if ($setting['settingName'] === 'padString'): ?>
												<span class="ui-hint" data-hint="<?php echo htmlspecialcharsbx(Loc::getMessage('NUMERATOR_EDIT_FORM_PAD_STRING_HINT')); ?>"></span>
											<? endif; ?>
										</div>
										<input type="<?= $setting['type'] == 'string' ? 'text' : 'number'; ?>"
											   class="main-numerator-edit-input "
											   value="<?= htmlspecialcharsbx($setting['value'])?>"
											   name="<?= $attributeName; ?>"
										>
									</div>
								<? elseif (in_array($setting['type'], ['linkToggle'])): ?>
									<div class="main-numerator-edit-control-box">
										<div class="main-numerator-edit-caption main-numerator-edit-link"
											 data-role="numerator-<?= htmlspecialcharsbx($setting['settingName']); ?>"
										>
											<?= htmlspecialcharsbx($setting['title']); ?>
										</div>
									</div>
								<? elseif (in_array($setting['type'], ['array'])): ?>
									<div class="main-numerator-edit-field-wrap"
										 data-role="numerator-<?= htmlspecialcharsbx($setting['settingName']); ?>"
									>
										<div class="main-numerator-edit-caption"><?= htmlspecialcharsbx($setting['title']); ?></div>
										<select class="main-numerator-edit-select"
												name="<?= $attributeName; ?>"
												data-role="numerator-<?= htmlspecialcharsbx($setting['settingName']); ?>-select"
										>
											<? foreach ($setting['values'] as $attributeSettings) : ?>
												<option value="<?= htmlspecialcharsbx($attributeSettings['value']); ?>"
													<? if ($setting['value'] == $attributeSettings['value']): ?> selected <? endif; ?>
												>
													<?= htmlspecialcharsbx($attributeSettings['title']) ?>
												</option>
											<? endforeach; ?>
										</select>
									</div>
								<? endif; ?>
							<? endforeach; ?>
						</div>
					<? endforeach; ?>
				</div>
				<? if (!$arResult['isEmbedMode']): ?>
					<div class="main-numerator-edit-buttons">
						<div class="main-numerator-edit-buttons-inner">
							<button class="ui-btn ui-btn-md ui-btn-success main-numerator-edit-btn-save" data-role="btn-save"><?= Loc::getMessage('NUMERATOR_EDIT_BTN_SAVE'); ?></button>
							<button class="ui-btn ui-btn-md ui-btn-light main-numerator-edit-btn-cancel" data-role="btn-cancel"><?= Loc::getMessage('NUMERATOR_EDIT_BTN_CANCEL'); ?></button>
						</div>
					</div>
				<? endif; ?>

				<script>
					BX.ready(function ()
					{
						new BX.Numerator({
							errors: {
								emptyField: "<?= CUtil::JSEscape(Loc::getMessage('NUMERATOR_EDIT_FORM_EMPTY_FIELD_ERROR'))?>"
							},
							isSlider: "<?= CUtil::JSEscape($arResult['IS_SLIDER'])?>",
							isEdit: "<?= CUtil::JSEscape($arResult['IS_EDIT'])?>",
							isMultipleSequences: "<?= CUtil::JSEscape($arResult['isMultipleSequences'])?>",
							defaultDelimiter: '/'
						});
					});
				</script>
			<?php
			if ($arResult['WITHOUT_FORM']):
				?>
				</div>
				<?php
			else:
				?>
				</form>
				<?php
			endif;
			?>
		</div>
	</div>
<? if ($arResult['IS_SLIDER'])
{
	?>
	</body>
	</html>
<? } ?>