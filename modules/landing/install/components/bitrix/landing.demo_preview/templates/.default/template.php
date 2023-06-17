<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var CMain $APPLICATION */
/** @var LandingEditComponent $component */

use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Landing\Restriction;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\Marketplace\Url;

Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

// extensions, css, js
Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.hint',
	'ui.alerts',
	'ui.progressbar',
	'ui.notification',
	'landing.settingsform.colorpickertheme',
	'landing.metrika',
	'main.qrcode',
]);

CJSCore::init([
	'landing_master', 'loader'
]);
Asset::getInstance()->addJs(
	'/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js'
);

// vars
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$colors = $arResult['COLORS'];
$themeCurr = $arResult['THEME_CURRENT'] ?: null;
$themeSite = $arResult['THEME_SITE'] ?: null;
$colorSite = $arResult['THEME_COLOR'] ?: $colors[$themeSite]['color'];
$template = $arResult['TEMPLATE'];
$siteGroup = $arResult['SITE_GROUP'];
$hasAccessCreate = $arResult['RIGHTS_CREATE'] ?: null;
$marketSubscriptionNeeded = false;
if ($arResult['NEEDED_SUBSCRIPTION'] === true && !Client::isSubscriptionAvailable())
{
	$marketSubscriptionNeeded = true;
}

$sliderCode = Restriction\Hook::getRestrictionCodeByHookCode('THEME');
$allowed = Restriction\Manager::isAllowed($sliderCode);

if ($colorSite)
{
	if ($colorSite[0] !== '#')
	{
		$colorSite = '#'.$colorSite;
	}
}
else
{
	$colorSite = LandingSiteDemoPreviewComponent::BASE_COLOR;
}


if (!$template)
{
	showError(Loc::getMessage('LANDING_404_ERROR'));
	return;
}

// create store
$externalImport = !empty($arResult['EXTERNAL_IMPORT']);
$createStore = !$externalImport &&
			   !$arResult['DISABLE_IMPORT'] &&
			   ($arParams['SITE_ID'] <= 0) &&
			   (in_array('STORE', (array) $template['TYPE']));

if ($createStore)
{
	$uriSelect = new Uri($arResult['CUR_URI']);
	$uriSelect->addParams([
		'stepper' => 'store',
		'param' => $template['DATA']['parent'] ?? $template['ID'],
		'sessid' => bitrix_sessid()
	]);
}
else
{
	$uriSelect = new Uri($arResult['CUR_URI']);
	preg_match('/preview.bitrix24.site\/pub\/site\/(\d+)/i', $template['PREVIEW_URL'] ?? '', $matches);
	$previewId = $matches[1] ?? 0;
	$uriSelect->addParams([
		'action' => 'select',
		'no_redirect' => ($request->get('no_redirect') === 'Y') ? 'Y' : 'N',
		'param' => $template['DATA']['parent'] ?? $template['ID'],
		'app_code' => $template['APP_CODE'],
		'title' => $template['TITLE'],
		'preview_id' => $previewId,
		'sessid' => bitrix_sessid()
	]);
}
?>

<div class="landing-template-demo-preview-header-container">
	<div class="landing-template-demo-preview-header landing-ui-panel-top">
		<div class="landing-template-demo-preview-header-logo">
			<span class="landing-ui-panel-top-logo-text"><?=Loc::getMessage('LANDING_TPL_HEADER_LOGO_BITRIX')?></span>
			<span class="landing-ui-panel-top-logo-color">24</span>
			<?php if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP'):?>
				<span class="landing-ui-panel-top-logo-text">.<?=Loc::getMessage('LANDING_TPL_HEADER_LOGO_KB')?></span>
			<?php else:?>
				<span class="landing-ui-panel-top-logo-text">.<?=Loc::getMessage('LANDING_TPL_HEADER_LOGO_SITE')?></span>
			<?php endif;?>
		</div>
		<div class="landing-template-demo-preview-header-title">
			<?= htmlspecialcharsbx($template['TITLE'])?>
		</div>
		<div class="right-part">
			<div class="mobile-view ui-btn ui-btn-light-border ui-btn-round">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_SHOW_IN_MOBILE')?>
			</div>
			<?php if (!$marketSubscriptionNeeded) : ?>
				<div class="create">
			<?php else : ?>
				<div class="create needed-market-subscription">
			<?php endif;?>
				<?php
				if (!$hasAccessCreate)
				{
					?>
					<span class="ui-btn ui-btn-success ui-btn-round ui-btn-disabled" data-hint="<?= Loc::getMessage('LANDING_TPL_HEADER_RIGHT_CREATE_HINT_MSGVER_1') ?>" data-hint-no-icon>
						<?php if ($arParams['SITE_ID'] !== 0) : ?>
							<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE_PAGE') ?>
						<?php else : ?>
							<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE_SITE') ?>
						<?php endif;?>
					</span>
					<?php
				}
				elseif (!empty($arResult['EXTERNAL_IMPORT']))
				{
					?>
					<span class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create-by-import"
						  <?php if (isset($arResult['EXTERNAL_IMPORT']['href'])){?>onclick="BX.SidePanel.Instance.open('<?=CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['href'])?>', {width: 1028})"<?}?>
						<?php if (isset($arResult['EXTERNAL_IMPORT']['onclick'])){?>onclick="<?= CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['onclick'])?>"<?}?>
						data-slider-ignore-autobinding="true"
						  title="<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>">
					<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</span>
					<a href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
						  title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>"
						  data-slider-ignore-autobinding="true"
						  style="display: none;">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</a>
					<?php
				}
				elseif ($createStore)
				{
					?>
					<span data-href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
						  title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_STORE') ?>"
						  data-slider-ignore-autobinding="true">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_STORE') ?>
				</span>
					<?php
				}
				elseif ($arParams['SITE_ID'] !== 0)
				{
					?>
					<a href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
					   title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_PAGE') ?>"
					   data-slider-ignore-autobinding="true">
						<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_PAGE') ?>
					</a>
					<?php
				}
				else
				{
					?>
					<a href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
					   title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_SITE') ?>"
					   data-slider-ignore-autobinding="true">
						<?php if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP'):?>
							<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_KB') ?>
						<?php else:?>
							<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_SITE') ?>
						<?php endif;?>
					</a>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<div class="landing-template-preview-body">
	<div class="landing-template-preview">
		<div class="preview-container">
			<div class="preview-left">
				<div class="preview-desktop">
					<div class="preview-desktop-body">
						<div class="preview-desktop-body-image"></div>
						<div class="preview-desktop-body-loader-container"></div>
					</div>
				</div>
				<div class="landing-popup-import">
					<div class="landing-popup-import-loader"></div>
					<div class="landing-popup-import-repeat hide">
						<div class="landing-popup-import-repeat-text">
							<?= Loc::getMessage('LANDING_TPL_POPUP_REPEAT_TEXT') ?>
						</div>
						<span class="landing-popup-import-repeat-button ui-btn ui-btn-light-border ui-btn-round">
							<?= Loc::getMessage('LANDING_TPL_POPUP_REPEAT_BUTTON') ?>
						</span>
					</div>
				</div>
			</div>
			<div class="preview-right">
				<div class="landing-template-preview-info" data-editable="true">
					<div class="pagetitle-wrap">
						<div class="pagetitle-inner-container">
							<div class="pagetitle landing-template-preview-title" id="landing-template-preview-title">
								<span id="pagetitle" class="landing-template-preview-edit-title landing-editable-field-label-js">
									<?= htmlspecialcharsbx($template['TITLE']) ?>
								</span>
								<input type="text" data-name="title" class="landing-template-preview-input-title landing-template-preview-edit-input landing-editable-field-input-js" value="<?= htmlspecialcharsbx($template['TITLE']) ?>" style="display: none;">
								<span class="landing-template-preview-edit-btn ui-title-input-btn-js"></span>
							</div>
						</div>
					</div>

					<div class="landing-template-preview-description">
						<p id="landing-template-preview-description-text">
							<span class="landing-editable-field-label-js"><?= htmlspecialcharsbx($template['DESCRIPTION']) ?></span>
							<span class="landing-template-preview-edit-btn ui-title-input-btn-js"></span>
							<textarea data-name="description" class="landing-template-preview-input-description landing-template-preview-edit-textarea landing-editable-field-input-js" style="display: none;"><?= htmlspecialcharsbx($template['DESCRIPTION']) ?></textarea>
						</p>
						<span class="landing-template-preview-notice"><?= Loc::getMessage('LANDING_PREVIEW_NOTICE') ?></span>
					</div>

					<?php if ($siteGroup):?>
						<div class="landing-template-preview-header">
							<?= Loc::getMessage('LANDING_TPL_HEADER_SITE_GROUP') ?>
						</div>
						<div class="landing-template-preview-palette landing-template-preview-site-group"
							 data-name="param">
							<?php foreach ($siteGroup as $i => $site):?>
								<div data-base-url="<?= $site['url']?>"
									 data-value="<?= $site['code']?>"
									 class="landing-template-preview-palette-item landing-template-preview-site-group-item <?= $i++ === 0 ? 'active' : ''?>"
									 style="background-color: <?= $site['color'] ?>;"><span></span></div>
							<?php endforeach;?>
						</div>
					<?php endif;?>

					<?php if ($template['URL_PREVIEW']):?>
					<div
						hidden class="landing-template-preview-base-url"
						data-base-url="<?= htmlspecialcharsbx($template['URL_PREVIEW'])?>"
					></div>
					<div id="landing-template-preview-settings" class="landing-template-preview-settings">
						<div class="landing-template-preview-header">
							<?= Loc::getMessage('LANDING_TPL_HEADER_COLOR')?>
						</div>
						<div class="landing-template-preview-palette landing-template-preview-themes" data-name="theme">
							<?php
							$allColors = [];
							foreach ($colors as $code => $color):
								if ($themeCurr === $color['color'])
								{
									$code = $color['color'];
									$color['base'] = true;
								}
								$allColors[] = $color['color'];
								if (
									!isset($color['base']) || $color['base'] !== true
									|| !LandingSiteDemoPreviewComponent::isHex($color['color'])
								)
								{
									continue;
								}
								?>
								<div
									data-value="<?= substr($color['color'], 1)?>"
									data-metrika24="Color::BaseSet"
									data-metrika24value="<?= trim($color['color'], '#')?>"
									class="landing-template-preview-palette-item bitrix24-metrika landing-template-preview-themes-item <?= ($themeCurr === $code && !$arParams['SITE_ID']) ? 'active' : ''?>"
									style="background-color: <?= $color['color'] ?>;"
								><span></span></div>
							<?php endforeach;?>
						</div>

						<?php if ($allowed): ?>
							<div class="landing-template-preview-setting-container">
								<div class="landing-demo-preview-custom-color" data-name="theme_custom_color">
									<div id="colorpicker-theme" class="landing-template-site-color-item">
										<?php
										$field = new Bitrix\Landing\Field\Text('');
										$field->viewForm([
															 'class' => 'ui-input ui-input-color',
															 'id' => 'colorpicker',
															 'name_format' => 'fields[ADDITIONAL_FIELDS][THEME_COLOR]',
															 'additional' => 'hidden',
														 ]);
										?>
									</div>
									<script>
										var allColors = <?=CUtil::PhpToJSObject($allColors)?>;
										var currentColor = '';
										BX.ready(function ()
										{
											new BX.Landing.ColorPickerTheme(
												BX('colorpicker-theme'),
												allColors,
												currentColor,
											);
										});
									</script>
								</div>
								<div class="landing-template-preview-header landing-template-preview-header-site-color">
									<?= Loc::getMessage('LANDING_TPL_MY_COLOR') ?>
								</div>
							</div>
						<?php else: ?>
							<label id="theme-slider" for="theme-slider">
								<div class="landing-template-preview-setting-container cursor-pointer">
									<div style="background-color: <?=LandingSiteDemoPreviewComponent::COLOR_PICKER_COLOR?>"
										 class="landing-template-preview-palette-item landing-template-site-color-item">
									</div>
									<div class="landing-template-preview-header landing-template-preview-header-site-color">
										<?php echo Loc::getMessage('LANDING_TPL_MY_COLOR');
										echo Restriction\Manager::getLockIcon(Restriction\Hook::getRestrictionCodeByHookCode('THEME'), ['theme-slider']); ?>
									</div>
								</div>
							</label>
						<?php endif; ?>
						<?php
						// add USE SITE COLOR setting only for adding page in exist site
						if ($arParams['SITE_ID']): ?>
							<div class="landing-template-preview-setting-container">
								<div class="landing-template-preview-site-color" data-name="theme_use_site">
									<div data-value="<?=(!$allowed && !(in_array($colorSite, $allColors, true))) ? substr(LandingSiteDemoPreviewComponent::BASE_COLOR,1) : substr($colorSite, 1)?>"
										 class="landing-template-preview-palette-item landing-template-site-color-item active"
										 style="background-color: <?=(!$allowed && !(in_array($colorSite, $allColors, true))) ? LandingSiteDemoPreviewComponent::BASE_COLOR : $colorSite?>"><span></span>
									</div>
								</div>
								<div class="landing-template-preview-header landing-template-preview-header-site-color">
									&mdash;&nbsp;<?= Loc::getMessage('LANDING_TPL_COLOR_USE_SITE') ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="<?if ($request->get('IFRAME') === 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
		<div class="landing-form-footer-container">
			<?php
			if (!empty($arResult['EXTERNAL_IMPORT']))
			{
				?>
				<span class="ui-btn ui-btn-success landing-template-preview-create-by-import"
					  <?php if (isset($arResult['EXTERNAL_IMPORT']['href'])){?>onclick="BX.SidePanel.Instance.open('<?=CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['href'])?>', {width: 1028})"<?}?>
						<?php if (isset($arResult['EXTERNAL_IMPORT']['onclick'])){?>onclick="<?= CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['onclick'])?>"<?}?>
						data-slider-ignore-autobinding="true"
					  title="<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>">
					<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</span>
				<span href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success landing-template-preview-create"
					  title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>"
					  data-slider-ignore-autobinding="true"
					  style="display: none;">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</span>
				<?php
			}
			elseif ($createStore)
			{
				?>
				<span data-href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success landing-template-preview-create"
					  title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>"
					  data-slider-ignore-autobinding="true">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</span>
				<?php
			}
			else
			{
				?>
				<a href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success landing-template-preview-create"
				   title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>"
				   data-slider-ignore-autobinding="true">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
				</a>
				<?php
			}
			?>
			<span class="ui-btn ui-btn-md ui-btn-link landing-template-preview-close">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL') ?>
				</span>
		</div>
	</div>
</div>

<?php if ($template['URL_PREVIEW']):?>
<script type="text/javascript">
	// Force init template preview layout
	<?php
	$popupTextCode = 'LANDING_TPL_POPUP_TEXT';
	if ($createStore)
	{
		$popupTextCode = 'LANDING_TPL_POPUP_TEXT_STORE';
	}
	elseif ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
	{
		$popupTextCode = 'LANDING_TPL_POPUP_TEXT_KB';
	}
	?>
	BX.Landing.TemplatePreviewInstance = BX.Landing.TemplatePreview.getInstance({
		createStore: <?= ($createStore ? 'true' : 'false') ?>,
		disableClickHandler: <?=(isset($arResult['EXTERNAL_IMPORT']['onclick']) ? 'true' : 'false') ?>,
		messages: {
			LANDING_LOADER_WAIT: "<?= CUtil::jsEscape(Loc::getMessage('LANDING_LOADER_WAIT_MSGVER_1')) ?>",
			LANDING_TPL_POPUP_TITLE: "<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_BUTTON_SHOW_IN_MOBILE')) ?>",
			LANDING_TPL_POPUP_TEXT: "<?= CUtil::jsEscape(Loc::getMessage($popupTextCode)) ?>",
		},
		disableStoreRedirect: <?= ($arParams['DISABLE_REDIRECT'] === 'Y') ? 'true' : 'false' ?>,
		zipInstallPath: '<?= ($template['ZIP_ID'] ?? null) ? Url::getConfigurationImportZipUrl($template['ZIP_ID']) : '' ?>',
		siteId: <?= ($arParams['SITE_ID'] > 0) ? $arParams['SITE_ID'] : 0 ?>,
		langId: "<?= is_string($arParams['LANG_ID']) ? $arParams['LANG_ID'] : ''?>",
		folderId: <?= ($arResult['FOLDER_ID'] ?? 0 && $arResult['FOLDER_ID'] > 0) ? $arResult['FOLDER_ID'] : 0 ?>,
		adminSection: <?= $arParams['ADMIN_SECTION'] === 'Y' ? 'true' : 'false'?>,
		urlPreview: <?=CUtil::PhpToJSObject($template['URL_PREVIEW'])?>,
	});
	var previewBlock = document.querySelector(".landing-template-preview-info");

	if(previewBlock.dataset.editable) {
		new BX.Landing.EditTitleForm(BX("landing-template-preview-title"), 300, true);
		new BX.Landing.EditTitleForm(BX("landing-template-preview-description-text"), 0, true);
	}

	<?php if (!$createStore):?>
	BX.ready(function(){
		new BX.Landing.SaveBtn(document.querySelector(".landing-template-preview-create"));
	});
	<?php endif;?>
</script>

<script type="text/javascript">
	BX.ready(function() {
		BX.UI.Hint.init(BX('ui-btn-disabled'));
	})
</script>
<?php endif;?>