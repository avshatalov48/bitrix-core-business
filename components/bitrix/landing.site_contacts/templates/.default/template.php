<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);
\Bitrix\Main\UI\Extension::load('ui.forms');
\Bitrix\Main\UI\Extension::load('ui.common');


/** @var array $arParams */
/** @var array $arResult */
/** @var \LandingSiteContactsComponent $component */

Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));

// saving and close
if ($component->request('save') == 'Y' && !$arResult['ERRORS'])
{
	?>
	<script>
		if (typeof top.BX.SidePanel !== 'undefined')
		{
			setTimeout(function() {
				top.BX.SidePanel.Instance.close();
				top.BX.onCustomEvent('BX.Landing.Filter:apply');
			}, 300);
		}
	</script>
	<?
}

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

Extension::load(['sidepanel']);
$contacts = $arResult['CRM_CONTACTS'];
$contactsRaw = $arResult['CRM_CONTACTS_RAW'];
?>

<div class="landing-site-contacts">
	<form method="post" action="<?= \htmlspecialcharsbx($component->getUri(['save' => 'Y']))?>">
		<input type="hidden" name="save" value="Y" />
		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="IFRAME" value="<?= $component->request('IFRAME') == 'Y' ? 'Y' : 'N';?>" />
		<?= bitrix_sessid_post();?>

		<div class="landing-site-contacts__section">
			<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_TPL_FORM_INPUT_NAME');?><?if ($contactsRaw['COMPANY'] !== $contacts['COMPANY']):?> <span for="COMPANY" class="landing-site-contacts__return" data-role="landing-site-contacts__return" data-raw="<?= \htmlspecialcharsbx($contactsRaw['COMPANY']);?>"></span><?endif;?></div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input type="text" name="COMPANY" class="ui-ctl-element" value="<?= \htmlspecialcharsbx($contacts['COMPANY'] ?? '');?>" placeholder="<?= Loc::getMessage('LANDING_TPL_PLACEHOLDER_COMPANY');?>" />
			</div>
		</div>

		<div class="landing-site-contacts__section">
			<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_TPL_FORM_INPUT_PHONE');?><?if ($contactsRaw['PHONE'] !== $contacts['PHONE']):?> <span for="PHONE" class="landing-site-contacts__return" data-role="landing-site-contacts__return" data-raw="<?= \htmlspecialcharsbx($contactsRaw['PHONE']);?>"></span><?endif;?></div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input type="text" name="PHONE" class="ui-ctl-element" value="<?= \htmlspecialcharsbx($contacts['PHONE'] ?? '');?>" placeholder="<?= Loc::getMessage('LANDING_TPL_PLACEHOLDER_PHONE');?>" data-role="landing-site-contacts__phone"/>
			</div>
		</div>

		<?if ($contactsRaw['ID']):?>
		<div class="landing-site-contacts__section">
			<a class="landing-site-contacts__link" href="<?= SITE_DIR?>crm/company/details/<?= $contactsRaw['ID'];?>/">
				<?= Loc::getMessage('LANDING_TPL_REQ_CHANGE');?>
			</a>
		</div>
		<?endif;?>

		<button type="submit" id="landing-master-next" class="ui-btn ui-btn-primary">
			<?= Loc::getMessage('LANDING_TPL_FORM_SAVE');?>
		</button>
	</form>
</div>

<script>
	BX.ready(function() {
		var nodesHint = document.body.querySelectorAll('[data-role="landing-site-contacts__return"]');

		if (nodesHint.length > 0)
		{
			for (var i = 0; i < nodesHint.length; i++)
			{
				nodesHint[i].addEventListener('click', function() {
					var attributeTarget = this.getAttribute('for');
					var inputNode = document.body.querySelector('[name="' + attributeTarget + '"]');
					inputNode.value = this.getAttribute('data-raw');
					this.classList.add('--hide');
				});
			}
		}
	});
</script>