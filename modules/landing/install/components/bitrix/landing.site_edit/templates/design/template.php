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
	UI\Extension::load("ui.layout-form");
	Ui\Extension::load("ui.forms");
	Ui\Extension::load("ui.hint");
	UI\Extension::load("landing.settingsform.designpreview");
	UI\Extension::load('landing.settingsform.colorpickertheme');
}
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/design/landing-forms.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/design/landing-forms.js');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'landing-slider-frame-popup'
);

$this->getComponent()->initAPIKeys();

// view-functions
include 'template_class.php';
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

<script type="text/javascript">
	BX.ready(function() {
		var editComponent = new BX.Landing.EditComponent();
		var successSave = <?= CUtil::PhpToJSObject($arParams['SUCCESS_SAVE']) ?>;
		top.window['landingSettingsSaved'] = false;
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
		BX.Landing.Env.createInstance({
			params: { type: '<?= $arParams['TYPE'] ?>' }
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

<form
	action="<?= htmlspecialcharsbx($uriSave->getUri()) ?>"
	method="post"
	class="ui-form ui-form-section landing-design-form"
	id="landing-site-design-form"
>
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="fields[SAVE_FORM]" value="Y"/>
	<input type="hidden" name="fields[TYPE]" value="<?= $row['TYPE']['CURRENT'] ?>"/>
	<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'] ?>"/>
	<input type="hidden" name="fields[TITLE]" value="<?= $row['TITLE']['CURRENT'] ?>"/>
	<input type="hidden" name="fields[LANDING_ID_INDEX]" value="<?= $row['LANDING_ID_INDEX']['CURRENT'];?>" />
	<input type="hidden" name="fields[TPL_ID]" value="<?= $row['TPL_ID']['CURRENT'] ?>"/>

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
								<div id="set-colors"
									 class="landing-template-palette landing-template-preview-themes landing-template-landing-palette"
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
									<a id="link-all-colors" onclick="showAllColors()" class="landing-template-button">
										<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_OTHER_COLORS') ?>
									</a>
								</div>

								<?php if ($arResult['ALLOWED_HOOK']): ?>
									<div class="landing-color-container">
										<div class="landing-template-preview-site-custom-color"
											 data-name="theme_custom_color">
											<div id="colorpicker-theme" class="landing-template-site-color-item">
												<?php
												$themeHookFields['THEME_COLOR']->viewForm([
													'class' => 'ui-input ui-input-color',
													'id' => 'colorpicker',
													'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
													'additional' => 'hidden',
												]);
												?>
											</div>
											<script>
												var allColors = <?= CUtil::PhpToJSObject($arResult['PREPARE_COLORS']['allColors']) ?>;
												var currentColor = <?= CUtil::PhpToJSObject($arResult['CURRENT_COLORS']['currentColor']) ?>;
												BX.ready(function()
												{
													this.corporateColor = new BX.Landing.ColorPickerTheme(
														BX('colorpicker-theme'),
														allColors,
														currentColor,
													);
												});
											</script>
										</div>
										<div class="landing-template-header landing-template-header-site-color">
											<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_MY_COLOR') ?>
										</div>
									</div>
								<?php else: ?>
									<label id="theme-slider" for="theme-slider">
										<div class="landing-color-container cursor-pointer">
											<div class="" data-name="theme_custom_color">
												<div id="colorpicker-theme"
													 data-value="<?= $arResult['LAST_CUSTOM_COLOR'] ?? LandingSiteEditComponent::COLOR_PICKER_COLOR ?>"
													 style="background-color: <?= $arResult['LAST_CUSTOM_COLOR'] ?? LandingSiteEditComponent::COLOR_PICKER_COLOR ?>"
													 class="landing-template-site-color-item"
												>
													<div hidden class="ui-colorpicker-color-js" style="background-color: <?= LandingSiteEditComponent::COLOR_PICKER_COLOR_RGB ?>;"></div>
													<input hidden
														   data-code="THEME_COLOR"
														   name="fields[ADDITIONAL_FIELDS][THEME_COLOR]"
														   id="colorpicker"
														   type="text"
														   readonly
														   class="ui-input ui-input-color landing-colorpicker-inp-js"
													>
													<div hidden class="ui-colorpicker-clear"></div>
												</div>
											</div>
											<div class="landing-template-header landing-template-header-site-color">
												<?= Loc::getMessage('LANDING_SITE_DSGN_TPL_MY_COLOR') ?>
												<?= Restriction\Manager::getLockIcon($arResult['SLIDER_CODE'], ['theme-slider']) ?>
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
		<?php $fontFields = $hooks['FONTS']->getPageFields(); ?>
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
						$template->showField('THEMEFONTS_COLOR', $pageFields['THEMEFONTS_COLOR']); ?>
						<script type="text/javascript">
							var paramsColor = {
								defaultColor: <?=CUtil::PhpToJSObject($colorMain)?>,
							}
							BX.ready(function ()
							{
								this.textColor = new BX.Landing.ColorPicker(BX('field-themefonts_color'), paramsColor);
							});
						</script>
					<?php endif; ?>
					<?php
					if (isset($pageFields['THEMEFONTS_CODE']))
					{
						$template->showField('THEMEFONTS_CODE', $pageFields['THEMEFONTS_CODE']);
					}
					if (isset($pageFields['THEMEFONTS_SIZE']))
					{
						$template->showField('THEMEFONTS_SIZE', $pageFields['THEMEFONTS_SIZE']);
					}
					if (isset($pageFields['THEMEFONTS_FONT_WEIGHT']))
					{
						$template->showField('THEMEFONTS_FONT_WEIGHT', $pageFields['THEMEFONTS_FONT_WEIGHT']);
					}
					if (isset($pageFields['THEMEFONTS_LINE_HEIGHT']))
					{
						$template->showField('THEMEFONTS_LINE_HEIGHT', $pageFields['THEMEFONTS_LINE_HEIGHT']);
					}
					?>
				</div>
				<div class="ui-form-row-group">
					<?php if (isset($pageFields['THEMEFONTS_COLOR_H'])): ?>
						<?php
						$template->showField('THEMEFONTS_COLOR_H', $pageFields['THEMEFONTS_COLOR_H']); ?>
						<script type="text/javascript">
							var paramsColorH = {
								defaultColor: <?=CUtil::PhpToJSObject($colorTitle)?>,
							}
							BX.ready(function ()
							{
								this.hColor = new BX.Landing.ColorPicker(BX('field-themefonts_color_h'), paramsColorH);
							});
						</script>
					<?php endif; ?>
					<?php
					if (isset($pageFields['THEMEFONTS_CODE_H']))
					{
						$template->showField('THEMEFONTS_CODE_H', $pageFields['THEMEFONTS_CODE_H']);
					}
					if (isset($pageFields['THEMEFONTS_FONT_WEIGHT_H']))
					{
						$template->showField('THEMEFONTS_FONT_WEIGHT_H', $pageFields['THEMEFONTS_FONT_WEIGHT_H']);
					}
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- BG -->
	<?php if (isset($hooks['BACKGROUND'])): ?>
		<?php $pageFields = $hooks['BACKGROUND']->getPageFields(); ?>
		<div class="ui-form-row last-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_SITE_DSGN_TPL_ADDITIONAL_BG') ?></div>
			</div>
			<div class="ui-form-content">
				<?php $template->showFieldTitle('BACKGROUND'); ?>
				<div class="ui-form-row-hidden">
					<div class="ui-form-row-group">
						<!--Picture-->
						<?php if (isset($pageFields['BACKGROUND_PICTURE'])): ?>
							<?php
							$template->showField('BACKGROUND_PICTURE', $pageFields['BACKGROUND_PICTURE']);
							$template->showPictureJS(
								$pageFields['BACKGROUND_PICTURE'],
								'',
								[
									'imgId' => 'landing-form-background-field',
									'width' => 2000,
									'height' => 2000,
									'uploadParams' => $row['ID']['CURRENT'] ? [
										'action' => 'Site::uploadFile',
										'id' => $row['ID']['CURRENT'],
									] : [],
								]
							);
							?>
							<div id="landing-form-background-field" class="landing-background-field ui-ctl-w100"></div>
						<?php endif; ?>

						<!--Position-->
						<?php
						if (isset($pageFields['BACKGROUND_POSITION']))
						{
							$template->showField('BACKGROUND_POSITION', $pageFields['BACKGROUND_POSITION']);
						}
						?>

						<!--Color-->
						<?php if (isset($pageFields['BACKGROUND_COLOR'])): ?>
							<?php $template->showField('BACKGROUND_COLOR', $pageFields['BACKGROUND_COLOR']); ?>
							<script type="text/javascript">
								var paramsBgColor = {
									defaultColor: <?=CUtil::PhpToJSObject(LandingSiteEditComponent::COLOR_PICKER_DEFAULT_BG_COLOR)?>,
								}
								BX.ready(function() {
									this.bgColor = new BX.Landing.ColorPicker(BX('field-background_color'), paramsBgColor);
								});
							</script>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!--BUTTONS-->
	<?php
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
		$buttonCancel['ID'] = 'action-close';
		$buttonCancel['LINK'] = '#';
	}
	$APPLICATION->IncludeComponent(
		'bitrix:ui.button.panel',
		'',
		['BUTTONS' => [$buttonSave, $buttonCancel]]
	);
	?>

</form>

<script type="text/javascript">
	BX.ready(function() {
		new BX.UI.LayoutForm();

		BX.UI.Hint.init(BX('landing-site-design-form'));

		<?php $themeFontsFields = $arResult['HOOKS']['THEMEFONTS']->getFields(); ?>

		new BX.Landing.SettingsForm.DesignPreview(
			BX('landing-site-design-form'),
			{
				theme: {
					use: {
						control: BX('checkbox-theme-use'),
					},
					baseColors: {
						control: BX('set-colors'),
					},
					corporateColor: {
						control: this.corporateColor,
					},
				},

				typo: {
					use: {
						control: BX('checkbox-themefonts-use'),
					},
					textColor: {
						control: this.textColor,
					},
					textFont: {
						control: BX('field-themefonts_code'),
						defaultValue: '<?= $themeFontsFields['CODE']->getValue() ?>',
					},
					textSize: {
						control: BX('field-themefonts_size'),
						defaultValue: '<?= $themeFontsFields['SIZE']->getValue() ?>',
					},
					textWeight: {
						control: BX('field-themefonts_font_weight'),
						defaultValue: '<?= $themeFontsFields['FONT_WEIGHT']->getValue() ?>',
					},
					textLineHeight: {
						control: BX('field-themefonts_line_height'),
						defaultValue: '<?= $themeFontsFields['LINE_HEIGHT']->getValue() ?>',
					},
					hColor: {
						control: this.hColor,
					},
					hFont: {
						control: BX('field-themefonts_code_h'),
						defaultValue: '<?= $themeFontsFields['CODE_H']->getValue() ?>',
					},
					hWeight: {
						control: BX('field-themefonts_font_weight_h'),
						defaultValue: '<?= $themeFontsFields['FONT_WEIGHT_H']->getValue() ?>',
					},
				},

				background: {
					use: {
						control: BX('checkbox-background-use'),
					},
					field: {
						control: BX('landing-form-background-field'),
					},
					image: {
						control: this.image,
					},
					position: {
						control: BX('field-background_position'),
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
			}
		);
	});
</script>

<script type="text/javascript">
	BX.Landing.TemplatePreviewInstance = BX.Landing.ColorPalette.getInstance();
</script>