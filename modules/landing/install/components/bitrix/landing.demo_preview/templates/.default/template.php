<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'ui.buttons.icons', 'ui.alerts', 'ui.progressbar',]);

\CJSCore::init(array('landing_master'));
\CJSCore::init('loader');
\Bitrix\Main\Page\Asset::getInstance()->addJs(
	'/bitrix/js/landing/utils.js'
);

\Bitrix\Main\Page\Asset::getInstance()->addJs(
	'/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js'
);

\Bitrix\Landing\Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$colors = $arResult['COLORS'];
$themeCurr = $arResult['THEME_CURRENT'] ? $arResult['THEME_CURRENT'] : null;
$themeSite = $arResult['THEME_SITE'] ? $arResult['THEME_SITE'] : $arResult['THEME_CURRENT'];
$template = $arResult['TEMPLATE'];

if (!$template)
{
	\showError(Loc::getMessage('LANDING_404_ERROR'));
	return;
}


$createStore = false;
$externalImport = ($arResult['TEMPLATE']['ID'] === 'store-instagram/mainpage' && Loader::includeModule('crm'));
$externalImportPath = '';
if ($externalImport)
{
	$externalImportPath = (string)\Bitrix\Main\Config\Option::get('crm', 'path_to_order_import_instagram');
	if (empty($externalImportPath))
	{
		$externalImport = false;
	}
}
if (!$externalImport)
{
	$createStore = ($arParams['SITE_ID'] <= 0 && $template['TYPE'] == 'STORE');
}

if ($createStore)
{
	$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriSelect->addParams(array(
		'stepper' => 'store',
		'param' => isset($template['DATA']['parent'])
			? $template['DATA']['parent']
			: $template['ID'],
		'sessid' => bitrix_sessid()
	));
}
else
{
	$uriData = array(
		'action' => 'select',
		'param' => isset($template['DATA']['parent'])
			? $template['DATA']['parent']
			: $template['ID'],
		'sessid' => bitrix_sessid()
	);
	if ($externalImport)
	{
		//TODO: change to method from \Bitrix\Crm\Order\Import\Instagram - get section XML_ID
		$uriData['additional'] = array('section' => 'instagram');
	}
	$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriSelect->addParams($uriData);
	unset($uriData);
}

$importUrl = '';

// removed dependency from crm instagram feature
/** @see \Bitrix\Crm\Order\Import\Instagram::isSiteTemplateImportable */
if ($externalImport)
{
	$uriCreate = new \Bitrix\Main\Web\Uri($externalImportPath);

	$params = [
		'create_url' => $uriSelect->getUri(),
	];

	if ($request->get('IFRAME') === 'Y')
	{
		$params['IFRAME'] = 'Y';
		$params['IFRAME_TYPE'] = 'SIDE_SLIDER';
	}

	$uriCreate->addParams($params);

	$importUrl = $uriCreate->getUri();
}
?>
<div class="landing-template-preview-body">
    <div class="landing-template-preview">
        <div class="preview-container">
            <div class="preview-left">
                <div class="preview-desktop">
                    <div class="preview-desktop-body">
                        <div class="preview-desktop-body-image">
							<?if ($template['URL_PREVIEW']):?>
                            <iframe src="<?= \htmlspecialcharsbx($template['URL_PREVIEW']);?>" class="preview-desktop-body-preview-frame"></iframe>
							<?endif;?>
                        </div>
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

					<?if ($template['URL_PREVIEW']):?>
                    <div class="landing-template-preview-settings">
                        <div class="landing-template-preview-header">
							<?= Loc::getMessage('LANDING_TPL_HEADER_COLOR');?>
                        </div>
						<div class="landing-template-preview-palette" data-name="theme">
							<?foreach ($colors as $code => $color):
								if (!isset($color['base']) || $color['base'] !== true)
								{
									continue;
								}
								?>
                                <div data-value="<?= $code;?>" data-src="<?= \htmlspecialcharsbx($template['URL_PREVIEW']);?><?
								?><?= strpos($template['URL_PREVIEW'], '?') === false ? '?' : '&amp;';?>theme=<?= $code;?>" <?
								?>class="landing-template-preview-palette-item<?= $themeCurr == $code ? ' active' : '';?>" <?
									 ?>style="background-color: <?= $color['color'];?>;"><span></span></div>
							<?endforeach;?>
						</div>

						<? // add USE SITE COLOR setting only for adding page in exist site?>
						<? if ($arParams['SITE_ID']): ?>
							<div class="landing-template-preview-sitecolor">
								<div class="landing-template-preview-palette-sitecolor" data-name="theme_use_site">
									<div data-value="<?= $themeSite; ?>"
										 data-src="<?= \htmlspecialcharsbx($template['URL_PREVIEW']); ?><?
										 ?><?= strpos($template['URL_PREVIEW'],
											 '?') === false ? '?' : '&amp;'; ?>theme=<?= $themeSite; ?>"
										 class="landing-template-preview-palette-item landing-template-preview-palette-item-sitecolor <?=($themeCurr == 'USE_SITE') ? 'active' : ''?>"
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
			if ($externalImport)
			{
				?>
				<a href="<?=$importUrl;?>"
						class="ui-btn ui-btn-success landing-template-preview-create-by-import"
						data-create-url="<?=$uriSelect->getUri();?>"
						title="<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>">
					<?=Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>
				</a>
				<span href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
						title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>" style="display: none;">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</span>
				<?
			}
			elseif ($createStore)
			{
				?>
				<span data-href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
				   title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</span>
				<?
			}
			else
			{
				?>
				<a href="<?= $uriSelect->getUri(); ?>" class="ui-btn ui-btn-success landing-template-preview-create"
				   title="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE'); ?>
				</a href="<?= $uriSelect->getUri(); ?>">
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
		createStore: <?=($createStore ? 'true' : 'false'); ?>,
		messages: {
			LANDING_LOADER_WAIT: "<?= \CUtil::jsEscape(Loc::getMessage('LANDING_LOADER_WAIT'));?>"
		}
	});
	var previewBlock = document.querySelector(".landing-template-preview-info");

	if(previewBlock.dataset.editable) {
		new BX.Landing.EditTitleForm(BX("landing-template-preview-title"), 300, true);
		new BX.Landing.EditTitleForm(BX("landing-template-preview-description-text"), 0, true);
	}

	<?
	if (!$createStore)
	{
	?>
	BX.ready(function(){
		new BX.Landing.SaveBtn(document.querySelector(".landing-template-preview-create"));
	});
	<?
	}
	?>
</script>
<?endif;?>