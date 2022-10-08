<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . ' no-background no-all-paddings');
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\UI\Extension;

if ($this->getComponent()->request('close') == 'Y' && !$arResult['ERRORS'])
{
	?>
	<script>
		if (typeof top.BX.SidePanel !== 'undefined')
		{
			setTimeout(function() {
				top.BX.SidePanel.Instance.close();
			}, 300);
		}
	</script>
	<?
}

// load
Loc::loadMessages(__FILE__);
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));
Extension::load(['ui.hint', 'ui.alerts', 'ui.dialogs.messagebox', 'ui.link', 'ui.fonts.opensans']);

// errors
if ($arResult['ERRORS'])
{
	?><div class="ui-alert ui-alert-danger"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?></div><?
}
if ($arResult['FATAL'])
{
	return;
}

// uri
$uriSave = new \Bitrix\Main\Web\Uri(
	\htmlspecialcharsback(POST_FORM_ACTION_URI)
);
$uriSave->addParams([
	'close' => 'Y'
]);

// help link
$helpUrl = \Bitrix\Landing\Help::getHelpUrl('COOKIES_EDIT');
if ($helpUrl)
{
	$this->setViewTarget('inside_pagetitle');
	?><a class="landing-help-link" href="<?= $helpUrl;?>">
		<?= Loc::getMessage('LANDING_TPL_HELP_LINK');?>
		<span data-hint="<?= Loc::getMessage('LANDING_TPL_HELP_LINK_HINT');?>" class="ui-hint"></span>
	</a><?
	$this->endViewTarget();
}

$idRand1 = randString(5);
?>

<div class="landing-agreement">
	<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post">
		<input type="hidden" name="action" value="save">
		<?= bitrix_sessid_post();?>
		<?foreach (['SYSTEM', 'CUSTOM'] as $agreementType):?>
			<div class="landing-agreement-wrapper<?if ($agreementType == 'CUSTOM'){?> landing-agreement-wrapper-custom<?}?>">
				<div class="landing-agreement-title"><?= Loc::getMessage('LANDING_TPL_TITLE_' . $agreementType);?></div>
				<?if ($agreementType == 'CUSTOM' && $arResult['SITE_INCLUDES_SCRIPT']):?>
					<div class="ui-alert ui-alert-warning landing-agreement-warning">
						<span class="ui-alert-message">
							<?= Loc::getMessage('LANDING_TPL_HOOK_COOKIES_SCRIPT_WARN');?>
						</span>
						<span class="ui-alert-close-btn" id="landing-agreement-warning-close"></span>
					</div>
				<?endif;?>
				<?
				foreach ($arResult['AGREEMENTS'][$agreementType] as $id => $agreement)
				{
					include 'bbform.php';
				}
				?>
				<?if ($agreementType == 'CUSTOM'):?>
					<div class="landing-agreement-new-custom" id="landing-agreement-new-<?= $idRand1;?>"></div>
					<span class="landing-agreement-add ui-link ui-link-dashed"><?= Loc::getMessage('LANDING_TPL_NEW_COOKIES');?></span>
				<?endif;?>
			</div>
		<?endforeach;?>
		<div class="landing-edit-footer-fixed pinable-block">
			<div class="landing-form-footer-container">
				<button type="submit" class="ui-btn ui-btn-success"  name="submit"  value="<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>
				</button>
				<a class="ui-btn ui-btn-md ui-btn-link" href="#">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
				</a>
			</div>
	</form>
</div>

<script>
	BX.ready(function()
	{
		new BX.Landing.SiteCookies({
			classNameAgreementBlock: 'landing-agreement-block',
			classNameEditIcon: 'landing-agreement-edit',
			classNameAgreementDelete: 'landing-agreement-delete',
			classNameAgreementAdd: 'landing-agreement-add',
			classCloseWarningIcon: 'landing-agreement-warning-close',
			classInputBlock: 'landing-agreement-input-block',
			classEditTitle: 'landing-agreement-cookies-name-edit',
			classBlockAreaShow: 'landing-agreement-block-inner-show',
			idAgreementNew: 'landing-agreement-new-<?= $idRand1;?>',
			bbFormAjaxPath: '/bitrix/components/bitrix/landing.site_cookies/ajax.form.php',
			messages: {
				removeAlertTitle: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ALERT_REMOVE_TITLE'))?>',
				removeAlertText: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ALERT_REMOVE_TEXT'))?>'
			}
		});
	});
</script>