<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;
use \Bitrix\Landing\Manager;

if ($this->getComponent()->request('switch') == 'Y' && !$arResult['ERRORS'])
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
Extension::load(['ui.common', 'ui.alerts']);

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

\LandingFilterComponent::setExternalFilter('!ID', $arParams['SITE_ID']);
$actionUrl = $this->getComponent()->getUri([
	'action' => 'switch',
	'sessid' => bitrix_sessid(),
	'param' => '__id__',
	'switch' => 'Y'
]);
?>
<div class="landing-domain-switch">
	<?if ($arParams['MODE'] == 'DELETE_GIFT'):?>
		<div class="landing-domain-switch-block">
			<div class="landing-domain-switch-title"><?= Loc::getMessage('LANDING_TPL_GIFT_HEADER_DELETE');?></div>
			<div class="landing-domain-switch-text"><?= Loc::getMessage('LANDING_TPL_GIFT_TEXT');?></div>
		</div>

	<?elseif ($arParams['MODE'] == 'CHANGE_GIFT'):?>
		<div class="landing-domain-switch-block">
			<div class="landing-domain-switch-title"><?= Loc::getMessage('LANDING_TPL_GIFT_HEADER_CHANGE');?></div>
			<div class="landing-domain-switch-text"><?= Loc::getMessage('LANDING_TPL_GIFT_TEXT');?></div>
		</div>
	<?endif;?>

	<?$return = $APPLICATION->IncludeComponent(
		'bitrix:landing.sites',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'DRAFT_MODE' => 'Y',
			'OVER_TITLE' => Loc::getMessage('LANDING_TPL_SELECT'),
			'PAGE_URL_SITE' => str_replace('__id__', '#site_show#', $actionUrl)
		),
		$component
	);?>

	<?if (!$return['SITES']):?>
		<?= Loc::getMessage('LANDING_TPL_NO_SITES');?>
	<?endif;?>

</div>

<?Manager::setPageTitle(Loc::getMessage('LANDING_TPL_SWITCH_TITLE'));?>
