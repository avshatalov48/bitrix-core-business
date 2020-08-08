<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;

\Bitrix\Landing\Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

// extensions, css, js
Extension::load([
	'ui.buttons', 'ui.buttons.icons', 'ui.alerts', 'ui.progressbar'
]);
\CJSCore::init([
	'landing_master', 'loader'
]);
\Bitrix\Main\Page\Asset::getInstance()->addJs(
	'/bitrix/js/landing/utils.js'
);
\Bitrix\Main\Page\Asset::getInstance()->addJs(
	'/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js'
);

// vars
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$colors = $arResult['COLORS'];
$themeCurr = $arResult['THEME_CURRENT'] ? $arResult['THEME_CURRENT'] : null;
$themeSite = $arResult['THEME_SITE'] ? $arResult['THEME_SITE'] : $arResult['THEME_CURRENT'];
$template = $arResult['TEMPLATE'];
$siteGroup = $arResult['SITE_GROUP'];

if (!$template)
{
	\showError(Loc::getMessage('LANDING_404_ERROR'));
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
	$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriSelect->addParams([
		'stepper' => 'store',
		'param' => $template['DATA']['parent'] ?? $template['ID'],
		'sessid' => bitrix_sessid()
	]);
}
else
{
	$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriSelect->addParams([
		'action' => 'select',
		'no_redirect' => ($request->get('no_redirect') == 'Y') ? 'Y' : 'N',
		'param' => isset($template['DATA']['parent'])
			? $template['DATA']['parent']
			: $template['ID'],
		'sessid' => bitrix_sessid()
	]);
}
?>
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
            </div>
            <div class="preview-right">
                <div class="landing-template-preview-info" data-editable="true">
                    <div class="pagetitle-wrap">
                        <div class="pagetitle-inner-container">
                            <div class="pagetitle landing-template-preview-title" id="landing-template-preview-title">
								<span id="pagetitle" class="landing-template-preview-edit-title ui-editable-field-label-js">
									<?= \htmlspecialcharsbx($template['TITLE']);?>
								</span>
								<input type="text" data-name="title" class="landing-template-preview-input-title landing-template-preview-edit-input ui-editable-field-input-js" value="<?= \htmlspecialcharsbx($template['TITLE']);?>" style="display: none;">
								<span class="landing-template-preview-edit-btn ui-title-input-btn-js"></span>
                            </div>
                        </div>
                    </div>

                    <div class="landing-template-preview-description">
                        <p id="landing-template-preview-description-text">
							<span class="ui-editable-field-label-js"><?= \htmlspecialcharsbx($template['DESCRIPTION']);?></span>
							<span class="landing-template-preview-edit-btn ui-title-input-btn-js"></span>
							<textarea data-name="description" class="landing-template-preview-input-description landing-template-preview-edit-textarea ui-editable-field-input-js" style="display: none;"><?= \htmlspecialcharsbx($template['DESCRIPTION']);?></textarea>
						</p>
						<span class="landing-template-preview-notice"><?= Loc::getMessage('LANDING_PREVIEW_NOTICE'); ?></span>
                    </div>
	
					<?if ($siteGroup):?>
						<div class="landing-template-preview-header">
							<?= Loc::getMessage('LANDING_TPL_HEADER_SITE_GROUP');?>
						</div>
						<div class="landing-template-preview-palette landing-template-preview-site-group"
							 data-name="param">
							<?foreach ($siteGroup as $i => $site):?>
								<div data-base-url="<?= $site['url'];?>"
									 data-value="<?= $site['code'];?>"
									 class="landing-template-preview-palette-item landing-template-preview-site-group-item <?= $i++ == 0 ? 'active' : '';?>"
									 style="background-color: <?= $site['color']; ?>;"><span></span></div>
							<?endforeach;?>
						</div>
					<?endif;?>

					<?if ($template['URL_PREVIEW']):?>
						<div hidden class="landing-template-preview-base-url"
							 data-base-url="<?= \htmlspecialcharsbx($template['URL_PREVIEW']);?>"></div>
						<div class="landing-template-preview-settings">
							<div class="landing-template-preview-header">
								<?= Loc::getMessage('LANDING_TPL_HEADER_COLOR');?>
							</div>
							<div class="landing-template-preview-palette landing-template-preview-themes" data-name="theme">
								<?foreach ($colors as $code => $color):
									if (!isset($color['base']) || $color['base'] !== true)
									{
										continue;
									}
									?>
									<div data-value="<?= $code;?>" data-theme="<?= $code;?>"
										 class="landing-template-preview-palette-item landing-template-preview-themes-item <?= $themeCurr == $code ? 'active' : '';?>"
										 style="background-color: <?= $color['color'];?>;"><span></span></div>
								<?endforeach;?>
							</div>
	
							<? // add USE SITE COLOR setting only for adding page in exist site?>
							<? if ($arParams['SITE_ID']): ?>
								<div class="landing-template-preview-sitecolor-container">
									<div class="landing-template-preview-sitecolor" data-name="theme_use_site">
										<div data-value="<?= $themeSite; ?>" data-theme="<?= $themeSite;?>"
											 class="landing-template-preview-palette-item landing-template-preview-sitecolor-item <?=($themeCurr == 'USE_SITE') ? 'active' : ''?>"
											 style="background-color: <?= $colors[$themeSite]['color'];?>"><span></span>
										</div>
									</div>
									<div class="landing-template-preview-header landing-template-preview-header-sitecolor">
										&mdash;&nbsp;<?= Loc::getMessage('LANDING_TPL_COLOR_USE_SITE'); ?>
									</div>
	
								</div>
							<? endif; ?>
						</div>
					<? endif; ?>
	
					
                </div>
            </div>
        </div>

        <div class="<?if ($request->get('IFRAME') == 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
            <div class="landing-form-footer-container">
			<?
			if (!empty($arResult['EXTERNAL_IMPORT']))
			{
				?>
				<span class="ui-btn ui-btn-success landing-template-preview-create-by-import"
						<?if (isset($arResult['EXTERNAL_IMPORT']['href'])){?>onclick="BX.SidePanel.Instance.open('<?=\CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['href'])?>', {width: 1028})"<?}?>
				   		<?if (isset($arResult['EXTERNAL_IMPORT']['onclick'])){?>onclick="<?=\CUtil::jsEscape($arResult['EXTERNAL_IMPORT']['onclick'])?>"<?}?>
				   		data-slider-ignore-autobinding="true"
						title="<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>">
					<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>
				</span>
				<span href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
						title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>"
					  	data-slider-ignore-autobinding="true"
					  	style="display: none;">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</span>
				<?
			}
			elseif ($createStore)
			{
				?>
				<span data-href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
				   		title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>"
					  	data-slider-ignore-autobinding="true">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</span>
				<?
			}
			else
			{
				?>
				<a href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
						title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>"
				   		data-slider-ignore-autobinding="true">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</a>
				<?
			}
			?>
			<span class="ui-btn ui-btn-md ui-btn-link landing-template-preview-close">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
				</span>
            </div>
        </div>
    </div>
</div>

<?if ($template['URL_PREVIEW']):?>
<script type="text/javascript">
	// Force init template preview layout
	BX.Landing.TemplatePreviewInstance = BX.Landing.TemplatePreview.getInstance({
		createStore: <?= ($createStore ? 'true' : 'false');?>,
		disableClickHandler: <?=(isset($arResult['EXTERNAL_IMPORT']['onclick']) ? 'true' : 'false');?>,
		messages: {
			LANDING_LOADER_WAIT: "<?= \CUtil::jsEscape(Loc::getMessage('LANDING_LOADER_WAIT'));?>"
		},
		disableStoreRedirect: <?= ($arParams['DISABLE_REDIRECT'] == 'Y') ? 'true' : 'false';?>
	});
	var previewBlock = document.querySelector(".landing-template-preview-info");

	if(previewBlock.dataset.editable) {
		new BX.Landing.EditTitleForm(BX("landing-template-preview-title"), 300, true);
		new BX.Landing.EditTitleForm(BX("landing-template-preview-description-text"), 0, true);
	}

	<?if (!$createStore):?>
	BX.ready(function(){
		new BX.Landing.SaveBtn(document.querySelector(".landing-template-preview-create"));
	});
	<?endif;?>
</script>
<?endif;?>