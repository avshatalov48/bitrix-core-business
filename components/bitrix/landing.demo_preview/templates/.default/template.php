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
use Bitrix\Landing\Site\Type;
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
	'ui.analytics',
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
$themeCurr = $arResult['THEME_CURRENT'] ?? null;
$themeSite = $arResult['THEME_SITE'] ?? null;
$template = $arResult['TEMPLATE'] ?? null;
$siteGroup = $arResult['SITE_GROUP'];
$hasAccessCreate = $arResult['RIGHTS_CREATE'] ?: null;
$marketSubscriptionNeeded = false;
if ($arResult['NEEDED_SUBSCRIPTION'] === true && !Client::isSubscriptionAvailable())
{
	$marketSubscriptionNeeded = true;
}

$sliderCode = Restriction\Hook::getRestrictionCodeByHookCode('THEME');
$allowed = Restriction\Manager::isAllowed($sliderCode);


if (!$template)
{
	showError(Loc::getMessage('LANDING_404_ERROR'));
	return;
}

// create store
$externalImport = !empty($arResult['EXTERNAL_IMPORT']);
$isCreateStore = !$externalImport &&
			   !$arResult['DISABLE_IMPORT'] &&
			   ($arParams['SITE_ID'] <= 0) &&
			   (in_array('STORE', (array) $template['TYPE']));

$isCreateMainpage = $arParams['TYPE'] === Type::SCOPE_CODE_MAINPAGE;

if ($isCreateStore)
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
	$host = Manager::getPreviewHost();
	$host = str_replace(['http://', 'https://'], '', $host);
	preg_match(
		"/{$host}\\/pub\\/site\\/(\\d+)/i",
		$template['PREVIEW_URL'] ?? '',
		$matches
	);
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
			<?php elseif ($isCreateMainpage) : ?>
				<span class="landing-ui-panel-top-logo-text landing-ui-panel-top-logo-text-mainpage"><?=Loc::getMessage('LANDING_TPL_HEADER_LOGO_MAINPAGE')?></span>
			<?php else:?>
				<span class="landing-ui-panel-top-logo-text">.<?=Loc::getMessage('LANDING_TPL_HEADER_LOGO_SITE')?></span>
			<?php endif;?>
		</div>
		<div class="landing-template-demo-preview-header-title">
			<?= htmlspecialcharsbx($template['TITLE'])?>
		</div>
		<div class="right-part">
			<?php if (!$isCreateMainpage): ?>
			<div class="mobile-view ui-btn ui-btn-light-border ui-btn-round">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_SHOW_IN_MOBILE')?>
			</div>
			<?php endif;?>
			<?php if (!$marketSubscriptionNeeded) : ?>
				<div class="create">
			<?php else : ?>
				<div class="create needed-market-subscription">
			<?php endif;?>
				<?php
				if (!$hasAccessCreate)
				{
					?>
					<span
						class="ui-btn ui-btn-success ui-btn-round ui-btn-disabled"
						data-hint="<?= Loc::getMessage('LANDING_TPL_HEADER_RIGHT_CREATE_HINT_MSGVER_1') ?>"
						data-hint-no-icon
					>
						<?php if(isset($arParams['REPLACE_LID']) && $arParams['REPLACE_LID'] !== 0) : ?>
							<?=Loc::getMessage('LANDING_TPL_BUTTON_REPLACE_PAGE') ?>
						<?php elseif ($arParams['SITE_ID'] !== 0) : ?>
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
						  style="display: none;"
					>
						<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE') ?>
					</a>
					<?php
				}
				elseif ($isCreateStore)
				{
					?>
					<span data-href="<?= $uriSelect->getUri() ?>" class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
						  title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_STORE') ?>"
						  data-slider-ignore-autobinding="true"
					>
						<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_STORE') ?>
					</span>
					<?php
				}
				elseif (isset($arParams['REPLACE_LID']) && $arParams['REPLACE_LID'] !== 0)
				{
					?>
					<a
						href="<?= $uriSelect->getUri() ?>"
						class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
						title="<?= Loc::getMessage('LANDING_TPL_BUTTON_REPLACE_PAGE') ?>"
						data-slider-ignore-autobinding="true"
					>
						<?= Loc::getMessage('LANDING_TPL_BUTTON_REPLACE_PAGE') ?>
					</a>
					<?php
				}
				elseif ($arParams['SITE_ID'] !== 0)
				{
					?>
					<a
						href="<?= $uriSelect->getUri() ?>"
						class="ui-btn ui-btn-success ui-btn-round landing-template-preview-create"
						title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE_PAGE') ?>"
						data-slider-ignore-autobinding="true"
					>
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
				<?php if ($isCreateMainpage):?>
					<div class="preview-desktop --main-page">
						<div class="preview-header">
							<div class="preview-header-left">
								<div class="preview-header-balloon"></div>
							</div>
							<div class="preview-header-right"></div>
						</div>
						<div class="preview-menu"></div>
						<div class="preview-desktop-body">
							<div class="preview-desktop-body-image"></div>
							<div class="preview-desktop-body-loader-container"></div>
						</div>
						<div class="preview-sidebar"></div>
					</div>
				<?php else: ?>
					<div class="preview-desktop">
						<div class="preview-desktop-body">
							<div class="preview-desktop-body-image"></div>
							<div class="preview-desktop-body-loader-container"></div>
						</div>
					</div>
				<?php endif;?>
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
			<div hidden class="preview-data">
					<input type="text" data-name="title" class="landing-template-preview-input-title" value="<?= htmlspecialcharsbx($template['TITLE']) ?>">
					<textarea data-name="description" class="landing-template-preview-input-description"><?= htmlspecialcharsbx($template['DESCRIPTION']) ?></textarea>

					<?php if ($siteGroup):?>
						<div class="landing-template-preview-site-group"
							 data-name="param">
							<?php foreach ($siteGroup as $i => $site):?>
								<div data-base-url="<?= $site['url']?>"
									 data-value="<?= $site['code']?>"
									 class="<?= $i++ === 0 ? 'active' : ''?>"
									 ></div>
							<?php endforeach;?>
						</div>
					<?php endif;?>

					<?php if ($template['URL_PREVIEW']):?>
					<div class="landing-template-preview-base-url" data-base-url="<?= htmlspecialcharsbx($template['URL_PREVIEW'])?>"></div>
					<div class="landing-template-preview-themes" data-name="theme">
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
								if ($themeCurr !== $code || $arParams['SITE_ID'])
								{
									continue;
								}
							?>
								<div
									data-value="<?= substr($color['color'], 1)?>"
									class="active"
									style="background-color: <?= $color['color'] ?>;"
								></div>
							<?php endforeach;?>
						</div>

						<?php if ($allowed): ?>
							<div data-name="theme_custom_color">
									<div id="colorpicker-theme">
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
						<?php else: ?>
							<label id="theme-slider" for="theme-slider"></label>
						<?php endif; ?>
						<?php
						// add USE SITE COLOR setting only for adding page in exist site
						if ($arParams['SITE_ID']): ?>
							<div class="landing-template-preview-site-color" data-name="theme_use_site">
								<div data-value="<?= substr(LandingSiteDemoPreviewComponent::BASE_COLOR,1) ?>"></div>
							</div>
						<?php endif; ?>
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
			elseif ($isCreateStore)
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
<script>
	// Force init template preview layout
	<?php
	$popupTextCode = 'LANDING_TPL_POPUP_TEXT';
	if ($isCreateStore)
	{
		$popupTextCode = 'LANDING_TPL_POPUP_TEXT_STORE';
	}
	elseif ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
	{
		$popupTextCode = 'LANDING_TPL_POPUP_TEXT_KB';
	}

	?>
	BX.Landing.TemplatePreviewInstance = BX.Landing.TemplatePreview.getInstance({
		createStore: <?= ($isCreateStore ? 'true' : 'false') ?>,
		createMainpage: <?= ($isCreateMainpage ? 'true' : 'false') ?>,
		isMainpageExists: <?= ($arParams['MAINPAGE_EXISTS'] ?? false) ? 'true' : 'false' ?>,
		disableClickHandler: <?=(isset($arResult['EXTERNAL_IMPORT']['onclick']) ? 'true' : 'false') ?>,
		messages: {
			LANDING_LOADER_WAIT: "<?= CUtil::jsEscape(Loc::getMessage('LANDING_LOADER_WAIT_MSGVER_1')) ?>",
			LANDING_TPL_POPUP_TITLE: "<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_BUTTON_SHOW_IN_MOBILE')) ?>",
			LANDING_TPL_POPUP_TEXT: "<?= CUtil::jsEscape(Loc::getMessage($popupTextCode)) ?>",
			LANDING_PREVIEW_MAINPAGE_MESSAGE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MAINPAGE_MESSAGE'));?>',
			LANDING_PREVIEW_MAINPAGE_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MAINPAGE_TITLE'));?>',
			LANDING_PREVIEW_MAINPAGE_BUTTON_OK_TEXT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MAINPAGE_BUTTON_OK_TEXT'));?>',
			LANDING_PREVIEW_MAINPAGE_BUTTON_CANCEL_TEXT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MAINPAGE_BUTTON_CANCEL_TEXT'));?>',
		},
		disableStoreRedirect: <?= ($arParams['DISABLE_REDIRECT'] === 'Y') ? 'true' : 'false' ?>,
		zipInstallPath: '<?= ($template['ZIP_ID'] ?? null) ? Url::getConfigurationImportZipUrl($template['ZIP_ID']) : '' ?>',
		appCode: '<?= $template['APP_CODE'] ?>',
		siteId: <?= ($arParams['SITE_ID'] > 0) ? $arParams['SITE_ID'] : 0 ?>,
		replaceLid: <?= $arParams['REPLACE_LID'] ?? 0 ?>,
		isCrmForm: '<?= $arParams['IS_CRM_FORM'] ?? 'N' ?>',
		context_section: '<?= isset($arParams['CONTEXT_SECTION']) ? CUtil::JSEscape($arParams['CONTEXT_SECTION']) : null ?>',
		context_element: '<?= isset($arParams['CONTEXT_ELEMENT']) ? CUtil::JSEscape($arParams['CONTEXT_ELEMENT']) : null ?>',
		langId: "<?= is_string($arParams['LANG_ID']) ? $arParams['LANG_ID'] : ''?>",
		folderId: <?= ($arResult['FOLDER_ID'] ?? 0 && $arResult['FOLDER_ID'] > 0) ? $arResult['FOLDER_ID'] : 0 ?>,
		adminSection: <?= $arParams['ADMIN_SECTION'] === 'Y' ? 'true' : 'false'?>,
		urlPreview: <?=CUtil::PhpToJSObject($template['URL_PREVIEW'])?>,
	});

	<?php if (!$isCreateStore):?>
	BX.ready(function(){
		new BX.Landing.SaveBtn(document.querySelector(".landing-template-preview-create"));
	});
	<?php endif;?>

	let templateAppCode;
	let analyticCategory;
	const type = '<?= $arParams['TYPE']?>';
	switch (type) {
		case 'MAINPAGE':
			analyticCategory = 'vibe';
			templateAppCode = '<?= $template['APP_CODE']?>';
			break;
		case 'PAGE':
			analyticCategory = 'site';
			templateAppCode = '<?= $template['APP_CODE']?>';
			break;
		case 'STORE':
			analyticCategory = 'store';
			templateAppCode = '<?= $arParams['CODE']?>';
			break;
		case 'KNOWLEDGE':
			analyticCategory = 'kb';
			templateAppCode = '<?= $arParams['CODE']?>';
			break;
	}
	templateAppCode = templateAppCode.replaceAll('_', '-')
	BX.UI.Analytics.sendData({
		tool: 'landing',
		category: analyticCategory,
		event: 'preview_template',
		p1: templateAppCode,
	});

	let createTemplateButton = document.querySelector('.landing-template-demo-preview-header .ui-btn-success');
	if (createTemplateButton)
	{
		let status = 'success';
		<?php if ($marketSubscriptionNeeded):?>
		status = 'error_market';
		<?php endif;?>
		createTemplateButton.onclick = function()
		{
			BX.UI.Analytics.sendData({
				tool: 'landing',
				category: analyticCategory,
				event: 'create_template',
				status,
				p1: templateAppCode,
			});
		};
	}

</script>

<script>
	BX.ready(function() {
		BX.UI.Hint.init(BX('ui-btn-disabled'));
	})
</script>
<?php endif;?>
