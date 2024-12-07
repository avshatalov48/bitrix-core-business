<?php
namespace Bitrix\Landing\Components\LandingEdit;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var CMain $APPLICATION */
/** @var LandingSiteEditComponent $component */

use Bitrix\Landing\Manager;
use Bitrix\Landing\Restriction;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Application;
use CJSCore;
use CMain;
use CUtil;
use LandingSiteEditComponent;
use function htmlspecialcharsback;
use function htmlspecialcharsbx;

Loc::loadMessages(__FILE__);
?>
<?php if ($arResult['ERRORS']): ?>
	<div class="landing-message-label error">
	<?php
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?>
	</div>
<?php endif ?>
<?php
if ($arResult['FATAL'])
{
	return;
}

// vars
$row = $arResult['SITE'];
$hooks = $arResult['HOOKS'];
$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$colorMain = LandingSiteEditComponent::COLOR_PICKER_DEFAULT_COLOR_TEXT;
$colorTitle = LandingSiteEditComponent::COLOR_PICKER_DEFAULT_COLOR_TEXT;
$isAjax = $component->isAjax();

// title
if ($arParams['SITE_ID'])
{
	Manager::setPageTitle($component->getMessageType('LANDING_SITE_DSGN_TITLE'));
}
else
{
	Manager::setPageTitle($component->getMessageType('LANDING_TPL_TITLE_ADD'));
}

// assets
CJSCore::init(
	[
		'color_picker',
		'landing_master',
		'action_dialog',
		'access',
		'sidepanel',
	]
);
if (Loader::includeModule('ui'))
{
	Ui\Extension::load('ui.buttons');
	Ui\Extension::load('ui.layout-form');
	Ui\Extension::load('ui.forms');
	Ui\Extension::load('ui.hint');
	Ui\Extension::load('landing.settingsform.designpreview');
	Ui\Extension::load('landing.settingsform.colorpickertheme');
}
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/script.js');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'landing-slider-frame-popup'
);

$this->getComponent()->initAPIKeys();

// view-functions
include Manager::getDocRoot() . '/bitrix/components/bitrix/landing.site_edit/templates/.default/template_class.php';
$template = new Template($arResult);

// some url
$uriSave = new Uri(htmlspecialcharsback(POST_FORM_ACTION_URI));
$uriSave->addParams(
	[
		'action' => 'save',
	]
);
$uriDomain = new Uri(
	str_replace('#site_edit#', $row['ID']['CURRENT'], $arParams['PAGE_URL_SITE_DOMAIN'])
);
$uriDomain->addParams(
	[
		'tab' => '__tab__',
		'IFRAME' => 'Y',
	]
);
?>

<script>
	BX.ready(function() {
		const editComponent = new BX.Landing.EditComponent('<?= $template->getFieldId('ACTION_CLOSE') ?>');
		const successSave = <?= CUtil::PhpToJSObject($arParams['SUCCESS_SAVE']) ?>;
		if (successSave)
		{
			top.window['landingSettingsSaved'] = true;
			top.BX.onCustomEvent('BX.Landing.Filter:apply');
			editComponent.actionClose();
			if (typeof top.BX.Landing.UI !== 'undefined' && typeof top.BX.Landing.UI.Tool !== 'undefined')
			{
				top.BX.Landing.UI.Tool.ActionDialog.getInstance().close();
			}
		}
		else
		{
			top.window['landingSettingsSaved'] = false;
		}
		BX.Landing.Env.createInstance({
			site_id: '<?= $row['ID']['CURRENT'] ?>',
			params: {
				type: '<?= $arParams['TYPE'] ?>',
			},
		});
	});
</script>

<?php
if ($arParams['SUCCESS_SAVE'])
{
	if ($request->get('IFRAME') !== 'Y')
	{
		$this->getComponent()->refresh([], ['action']);
	}
	return;
}
?>

<div class="landing-form-wrapper">
	<form
		action="<?= htmlspecialcharsbx($uriSave->getUri()) ?>"
		method="post"
		class="landing-form landing-design-form"
		id="landing-site-design-form"
	>
		<?= bitrix_sessid_post() ?>
		<input type="hidden" name="fields[SAVE_FORM]" value="Y"/>
		<input type="hidden" name="fields[TYPE]" value="<?= $row['TYPE']['CURRENT'] ?>"/>
		<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'] ?>"/>
		<input type="hidden" name="fields[TITLE]" value="<?= $row['TITLE']['CURRENT'] ?>"/>
		<input type="hidden" name="fields[LANDING_ID_INDEX]" value="<?= $row['LANDING_ID_INDEX']['CURRENT'];?>" />
		<input type="hidden" name="fields[TPL_ID]" value="<?= $row['TPL_ID']['CURRENT'] ?>"/>
		<input type="hidden" name="fields[LANG]" value="<?= $row['LANG']['CURRENT']?>"/>

		<div class="ui-form ui-form-section">
			<!--Theme color-->
			<?php if (isset($hooks['THEME'])): ?>
				<?php
				$themeHookFields = $hooks['THEME']->getPageFields();
				if (isset($themeHookFields['THEME_CODE'])): ?>
					<!-- theme color -->
					<div class="ui-form-row">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text"><?= $themeHookFields['THEME_CODE']->getLabel() ?></div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row-group">
								<div class="ui-ctl ui-ctl-textbox">
									<?php if (isset($themeHookFields['THEME_COLOR'])): ?>
										<div id="<?= $template->getFieldId('ALL_COLORS') ?>"
											 class="landing-template-palette"
											 data-name="theme"
										>
											<?php foreach ($arResult['PREPARE_COLORS']['allColors'] as $color): ?>
												<div data-value="<?= $color ?>"
													 data-metrika24="Color::BaseSet"
													 data-metrika24value="<?= trim($color, '#') ?>"
													<?= (in_array($color, $arResult['PREPARE_COLORS']['startColors'], true)) || ($arResult['CURRENT_THEME'] === $color) ? '' : 'hidden' ?>
													 class="landing-template-palette-item bitrix24-metrika landing-template-preview-themes-item <?= ($arResult['CURRENT_THEME'] === $color) && (in_array($color, $arResult['PREPARE_COLORS']['allColors'], true)) ? 'active' : '' ?>"
													 style="background-color: <?= $color ?>"></div>
											<?php endforeach; ?>
											<a
												onclick="showAllColors(this, BX('<?= $template->getFieldId('ALL_COLORS') ?>'))"
												class="landing-template-all-colors-button"
											>
												<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_OTHER_COLORS') ?>
											</a>
										</div>

										<script>
											BX.ready(function ()
											{
												new BX.Landing.ColorPalette(
													BX('<?= $template->getFieldId('ALL_COLORS') ?>'),
													<?= ('BX(\'' . $template->getFieldId('COLORPICKER_THEME') . '\')') ?>
												);
											});
										</script>

										<?php if ($arResult['ALLOWED_HOOK']): ?>
											<div class="landing-color-container landing-color-allowed">
												<div class="landing-template-preview-site-custom-color">
													<div
														id="<?= $template->getFieldId('COLORPICKER_THEME') ?>"
														class="landing-template-site-color-item"
													>
														<?php
														$themeHookFields['THEME_COLOR']->viewForm([
															'class' => 'ui-input ui-input-color',
															'id' => $template->getFieldId('THEME_COLOR'),
															'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
															'additional' => 'hidden',
														]);
														?>
													</div>
													<script>
														BX.ready(function()
														{
															this.corporateColor = new BX.Landing.ColorPickerTheme(
																BX('<?= $template->getFieldId('COLORPICKER_THEME') ?>'),
																<?= CUtil::PhpToJSObject($arResult['PREPARE_COLORS']['allColors']) ?>,
																<?= CUtil::PhpToJSObject($arResult['CURRENT_COLORS']['currentColor']) ?>,
															);
														});
													</script>
												</div>
												<div class="landing-color-title">
													<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_MY_COLOR') ?>
												</div>
											</div>
										<?php else: ?>
											<label
												id="<?= $template->getFieldId('COLORPICKER_THEME') ?>"
												for="<?= $template->getFieldId('COLORPICKER_THEME') ?>"
												class="landing-color-label"
											>
												<div class="landing-color-container cursor-pointer">
													<div class="" data-name="theme_custom_color">
														<div
															 data-value="<?= $arResult['LAST_CUSTOM_COLOR'] ?? LandingSiteEditComponent::COLOR_PICKER_COLOR ?>"
															 style="background-color: <?= $arResult['LAST_CUSTOM_COLOR'] ?? LandingSiteEditComponent::COLOR_PICKER_COLOR ?>"
															 class="landing-template-site-color-item"
														>
															<div hidden class="ui-colorpicker-color-js" style="background-color: <?= LandingSiteEditComponent::COLOR_PICKER_COLOR_RGB ?>;"></div>
															<input hidden
																   data-code="THEME_COLOR"
																   name="fields[ADDITIONAL_FIELDS][THEME_COLOR]"
																   type="text"
																   readonly
																   class="ui-input ui-input-color landing-colorpicker-inp-js"
															>
															<div hidden class="ui-colorpicker-clear"></div>
														</div>
													</div>
													<div class="landing-color-title">
														<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_MY_COLOR') ?>
														<?= Restriction\Manager::getLockIcon($arResult['SLIDER_CODE'], [$template->getFieldId('COLORPICKER_THEME')]) ?>
													</div>
												</div>
											</label>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!--Typo -->
			<?php
			if (isset($hooks['THEMEFONTS'])): ?>
				<?php $pageFields = $hooks['THEMEFONTS']->getPageFields(); ?>
				<div class="ui-form-row">
					<div class="ui-form-label">
						<div class="ui-ctl-label-text">
							<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_FONTS_PAGE') ?>
						</div>
					</div>
					<div class="ui-form-content">
						<div class="ui-form-row-group">
							<?php if (isset($pageFields['THEMEFONTS_COLOR'])): ?>
								<?php
								foreach ($arResult['COLORS'] as $colorItem)
								{
									if ($arResult['CURRENT_THEME'] === $colorItem['color'])
									{
										if (isset($colorItem['main']))
										{
											$colorMain = $colorItem['main'];
											$colorTitle = $colorMain;
										}
										if (isset($colorItem['colorTitle']))
										{
											$colorTitle = $colorItem['colorTitle'];
										}
									}
								}
								if (!$colorMain)
								{
									$colorMain = LandingSiteEditComponent::COLOR_PICKER_DEFAULT_COLOR_TEXT;
									if (!$colorTitle)
									{
										$colorTitle = $colorMain;
									}
								}
								$template->showField($pageFields['THEMEFONTS_COLOR'], [
									'title' => true,
									'needWrapper' => true,
									'readonly' => true,
								]); ?>
								<script>
									var paramsColor = {
										defaultColor: <?=CUtil::PhpToJSObject($colorMain)?>,
									}
									BX.ready(function ()
									{
										this.textColor = new BX.Landing.ColorPicker(
											BX('<?= $template->getFieldId('THEMEFONTS_COLOR') ?>'),
											paramsColor
										);
									});
								</script>
							<?php endif; ?>
							<?php
							if (isset($pageFields['THEMEFONTS_CODE']))
							{
								$template->showField($pageFields['THEMEFONTS_CODE'], [
									'title' => true,
									'needWrapper' => true,
									'readonly' => true,
								]);
							}
							if (isset($pageFields['THEMEFONTS_SIZE']))
							{
								$template->showField($pageFields['THEMEFONTS_SIZE'], [
									'title' => true,
									'needWrapper' => true,
								]);
							}
							if (isset($pageFields['THEMEFONTS_FONT_WEIGHT']))
							{
								$template->showField($pageFields['THEMEFONTS_FONT_WEIGHT'], [
									'title' => true,
									'needWrapper' => true,
								]);
							}
							if (isset($pageFields['THEMEFONTS_LINE_HEIGHT']))
							{
								$template->showField($pageFields['THEMEFONTS_LINE_HEIGHT'], [
									'title' => true,
									'needWrapper' => true,
								]);
							}
							?>
						</div>
						<div class="ui-form-row-group">
							<?php if (isset($pageFields['THEMEFONTS_COLOR_H'])): ?>
								<?php
								$template->showField($pageFields['THEMEFONTS_COLOR_H'], [
									'title' => true,
									'needWrapper' => true,
									'readonly' => true,
								]); ?>
								<script>
									var paramsColorH = {
										defaultColor: <?=CUtil::PhpToJSObject($colorTitle)?>,
									}
									BX.ready(function ()
									{
										this.hColor = new BX.Landing.ColorPicker(
											BX('<?= $template->getFieldId('THEMEFONTS_COLOR_H') ?>'),
											paramsColorH
										);
									});
								</script>
							<?php endif; ?>
							<?php
							if (isset($pageFields['THEMEFONTS_CODE_H']))
							{
								$template->showField($pageFields['THEMEFONTS_CODE_H'], [
									'title' => true,
									'needWrapper' => true,
									'readonly' => true,
								]);
							}
							if (isset($pageFields['THEMEFONTS_FONT_WEIGHT_H']))
							{
								$template->showField($pageFields['THEMEFONTS_FONT_WEIGHT_H'], [
									'title' => true,
									'needWrapper' => true,
								]);
							}
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- BG -->
			<?php if (isset($hooks['BACKGROUND'])): ?>
				<?php $pageFields = $hooks['BACKGROUND']->getPageFields(); ?>
				<?php if ($arParams['TYPE'] === 'KNOWLEDGE'):?>
					<div class="ui-form-row last-row">
				<?php else: ?>
					<div class="ui-form-row">
				<?php endif; ?>
						<div class="ui-form-label">
							<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_SITE_DSGN_TPL_ADDITIONAL_BG') ?></div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-label" data-form-row-hidden>
								<?php $template->showField($pageFields['BACKGROUND_USE'], ['title' => true]);?>
							</div>
							<div class="ui-form-row-hidden">
								<div class="ui-form-row-group">
									<!--Picture-->
									<?php if (isset($pageFields['BACKGROUND_PICTURE'])): ?>
										<?php
										$template->showPictureJS(
											$pageFields['BACKGROUND_PICTURE'],
											'',
											[
												'width' => 1920,
												'height' => 1920,
												'uploadParams' => $row['ID']['CURRENT'] ? [
													'action' => 'Site::uploadFile',
													'id' => $row['ID']['CURRENT'],
												] : [],
											]
										);
										?>
									<?php endif; ?>

									<!--Position-->
									<?php
									if (isset($pageFields['BACKGROUND_POSITION']))
									{
										$template->showField($pageFields['BACKGROUND_POSITION'], [
											'title' => true,
											'needWrapper' => true,
										]);
									}
									?>

									<!--Color-->
									<?php if (isset($pageFields['BACKGROUND_COLOR'])): ?>
										<?php $template->showField($pageFields['BACKGROUND_COLOR'], [
											'title' => true,
											'needWrapper' => true,
											'readonly' => true,
										]); ?>
										<script>
											var paramsBgColor = {
												defaultColor: <?=CUtil::PhpToJSObject(LandingSiteEditComponent::COLOR_PICKER_DEFAULT_BG_COLOR)?>,
											}
											BX.ready(function() {
												this.bgColor = new BX.Landing.ColorPicker(
													BX('<?= $template->getFieldId('BACKGROUND_COLOR') ?>'),
													paramsBgColor
												);
											});
										</script>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php if ($arParams['TYPE'] !== 'KNOWLEDGE'):?>
					<div class="ui-form-row last-row">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_SITE_DSGN_TPL_ADDITIONAL_TRANSITION_BG') ?></div>
						</div>
						<div class="ui-form-content">
							<!--Color page transition-->
							<?php $transitionFields = $hooks['TRANSITION']->getPageFields(); ?>
							<?php if (isset($transitionFields['TRANSITION_COLOR'])): ?>
								<?php $template->showField($transitionFields['TRANSITION_COLOR'], [
									'title' => true,
									'needWrapper' => true,
									'readonly' => true,
								]); ?>
								<script>
									var paramsTransitionBgColor = {
										defaultColor: <?=CUtil::PhpToJSObject(LandingSiteEditComponent::COLOR_PICKER_DEFAULT_BG_COLOR)?>,
									}
									BX.ready(function() {
										this.transitionColor = new BX.Landing.ColorPicker(
											BX('<?= $template->getFieldId('TRANSITION_COLOR') ?>'),
											paramsTransitionBgColor
										);
									});
								</script>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!--BUTTONS-->
			<?php
			// for complex component landing.settings not need buttons. If isAjax will be incorrect - need add other flag for landgin.settings
			if (!$isAjax)
			{
				$buttonSave = [
					'TYPE' => 'save',
					'ID' => 'landing-save-btn',
					'NAME' => 'submit',
					'CAPTION' => Loc::getMessage('LANDING_DSGN_TPL_BUTTON_' . ($arParams['LANDING_ID'] ? 'SAVE' : 'ADD')),
					'VALUE' => Loc::getMessage('LANDING_DSGN_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD')),
				];
				$buttonCancel = [
					'TYPE' => 'cancel',
					'CAPTION' => Loc::getMessage('LANDING_DSGN_TPL_BUTTON_CANCEL'),
					'LINK' => $arParams['PAGE_URL_LANDINGS'],
				];
				if ($request->get('IFRAME') === 'Y')
				{
					$buttonCancel['ID'] = $template->getFieldId('ACTION_CLOSE');
					$buttonCancel['LINK'] = '#';
				}
				$APPLICATION->IncludeComponent(
					'bitrix:ui.button.panel',
					'',
					['BUTTONS' => [$buttonSave, $buttonCancel]]
				);
			}
			?>
		</div>
	</form>
</div>

<script>
	BX.ready(function() {
		new BX.UI.LayoutForm({container: BX('landing-site-design-form')});

		BX.UI.Hint.init(BX('landing-site-design-form'));

		<?php $themeFontsFields = $arResult['HOOKS']['THEMEFONTS']->getFields(); ?>

		new BX.Landing.SettingsForm.DesignPreview(
			BX('landing-site-design-form'),
			{
				theme: {
					use: {
						control: BX('<?= $template->getFieldId('THEME_USE') ?>'),
					},
					baseColors: {
						control: BX('<?= $template->getFieldId('ALL_COLORS') ?>'),
					},
					corporateColor: {
						control: this.corporateColor,
					},
				},

				typo: {
					use: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_USE') ?>'),
					},
					textColor: {
						control: this.textColor,
					},
					textFont: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_CODE') ?>'),
						defaultValue: '<?= $themeFontsFields['CODE']->getValue() ?>',
					},
					textSize: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_SIZE') ?>'),
						defaultValue: '<?= $themeFontsFields['SIZE']->getValue() ?>',
					},
					textWeight: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_FONT_WEIGHT') ?>'),
						defaultValue: '<?= $themeFontsFields['FONT_WEIGHT']->getValue() ?>',
					},
					textLineHeight: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_LINE_HEIGHT') ?>'),
						defaultValue: '<?= $themeFontsFields['LINE_HEIGHT']->getValue() ?>',
					},
					hColor: {
						control: this.hColor,
					},
					hFont: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_CODE_H') ?>'),
						defaultValue: '<?= $themeFontsFields['CODE_H']->getValue() ?>',
					},
					hWeight: {
						control: BX('<?= $template->getFieldId('THEMEFONTS_FONT_WEIGHT_H') ?>'),
						defaultValue: '<?= $themeFontsFields['FONT_WEIGHT_H']->getValue() ?>',
					},
				},

				background: {
					use: {
						control: BX('<?= $template->getFieldId('BACKGROUND_USE') ?>'),
					},
					field: {
						control: BX('<?= $template->getFieldId('BACKGROUND_PICTURE_FORM') ?>'),
					},
					image: {
						control: this.image,
					},
					position: {
						control: BX('<?= $template->getFieldId('BACKGROUND_POSITION') ?>'),
					},
					color: {
						control: this.bgColor,
					},
				}
			},
			{
				title: <?=CUtil::PhpToJSObject(Loc::getMessage('LANDING_SITE_FORM_TITLE_2'))?>,
				subtitle: <?=CUtil::PhpToJSObject(Loc::getMessage('LANDING_SITE_FORM_SUBTITLE'))?>,
				text1: <?=CUtil::PhpToJSObject(Loc::getMessage(
					'LANDING_SITE_FORM_TEXT_1',
					[
						'#LINK1#' => '<a href="#" class="landing-design-preview-link">',
						'#LINK2#' => '</a>',
					]
				))?>,
				text2: <?=CUtil::PhpToJSObject(Loc::getMessage('LANDING_SITE_FORM_TEXT_2'))?>,
				button: <?=CUtil::PhpToJSObject(Loc::getMessage('LANDING_SITE_FORM_BUTTON'))?>,
			},
			'<?= $template->getFieldId('DESIGN_PREVIEW', false, 'element') ?>',
		);
	});
</script>
