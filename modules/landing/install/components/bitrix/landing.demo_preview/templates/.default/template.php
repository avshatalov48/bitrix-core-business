<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;

Extension::load('ui.buttons');
Extension::load('ui.buttons.icons');
Extension::load('ui.alerts');

\CJSCore::init('loader');
\Bitrix\Main\Page\Asset::getInstance()->addJs(
	'/bitrix/js/landing/utils.js'
);

$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE'));
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$curUrl = $request->getRequestUri();
$colors = $arResult['COLORS'];
$template = $arResult['TEMPLATE'];
if (isset($template['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE']))
{
	$themeCurr = $template['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE'];
	if (isset($colors[$themeCurr]))
	{
		$colors[$themeCurr]['base'] = true;
	}
}
else
{
	$themeCurr = array_shift(array_keys($colors));
}

if (!$template)
{
	\showError(Loc::getMessage('LANDING_404_ERROR'));
	return;
}

$uriSelect = new \Bitrix\Main\Web\Uri($curUrl);
$uriSelect->addParams(array(
	'action' => 'select',
	'param'=> isset($template['DATA']['parent'])
				? $template['DATA']['parent']
				: $template['ID'],
	'sessid' => bitrix_sessid()
));
$uriSelect->deleteParams(array(
	'tpl'
));
?>
<div class="landing-template-preview-body">
    <div class="landing-template-preview">
        <div class="preview-container">
            <div class="preview-left">
                <div class="preview-desktop">
                    <div class="preview-desktop-body">
                        <div class="preview-desktop-body-image">
                            <iframe src="<?= \htmlspecialcharsbx($template['URL_PREVIEW']);?>" class="preview-desktop-body-preview-frame"></iframe>
                        </div>
                        <div class="preview-desktop-body-loader-container"></div>
                    </div>
                </div>
            </div>
            <div class="preview-right">
                <div class="landing-template-preview-info">
                    <div class="pagetitle-wrap">
                        <div class="pagetitle-inner-container">
                            <div class="pagetitle">
							<span id="pagetitle" class="pagetitle-item">
								<?= \htmlspecialcharsbx($template['TITLE']);?>
							</span>
                            </div>
                        </div>
                    </div>

                    <div class="landing-template-preview-description">
                        <p><?= \htmlspecialcharsbx($template['DESCRIPTION']);?></p>
                    </div>

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
                                <div data-value="<?= $code;?>" data-src="<?= \htmlspecialcharsbx($template['URL_PREVIEW']);?>&amp;theme=<?= $code;?>" <?
								?>class="landing-template-preview-palette-item<?= $themeCurr == $code ? ' active' : '';?>" <?
									 ?>style="background-color: <?= $color['color'];?>;"><span></span></div>
							<?endforeach;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="<?if ($request->get('IFRAME') == 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
            <div class="landing-form-footer-container">
			<a href="<?= $uriSelect->getUri();?>" class="ui-btn ui-btn-success landing-template-preview-create" value="<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CREATE');?>
                </a href="<?= $uriSelect->getUri();?>">
			<span class="ui-btn ui-btn-md ui-btn-link landing-template-preview-close">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
                </span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	// Force init template preview layout
	BX.Landing.TemplatePreview.getInstance();
</script>