<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Domain\Register;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;
use \Bitrix\Landing\Manager;

if ($this->getComponent()->request('save') == 'Y' && !$arResult['ERRORS'])
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
Extension::load([
	'ui.common', 'ui.alerts',
	'ui.forms', 'ui.buttons',
	'ui.dialogs.messagebox',
	'ui.info-helper', 'ui.hint'
]);
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));

// errors
if ($arResult['ERRORS'])
{
	?><div class="ui-alert ui-alert-danger" id="domain-error-alert"><?
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

// vars
$tab = $this->getComponent()->request('tab');

// uri
$uriSave = new \Bitrix\Main\Web\Uri(
	\htmlspecialcharsback(POST_FORM_ACTION_URI)
);
$uriSave->addParams([
	'save' => 'Y'
]);

// left panel
$menuItems = [
	'provider' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_FREE'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'provider'], ['save'])
		],
		'HELP_CODE' => 'DOMAIN_FREE'
	],
	'bitrix24' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_BITRIX24'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'bitrix24'], ['save'])
		],
		'HELP_CODE' => 'DOMAIN_BITRIX24'
	],
	'private' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_PRIVATE'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'private'], ['save'])
		],
		'HELP_CODE' => 'DOMAIN_EDIT'
	]
];
if (!$arResult['REGISTER']->enable())
{
	unset($menuItems['provider']);
}
if (!$tab)
{
	if ($arResult['B24_DOMAIN_NAME'])
	{
		$tab = 'bitrix24';
	}
	else if ($arResult['IS_FREE_DOMAIN'] != 'Y')
	{
		$tab = 'private';
	}
	else
	{
		$menuItemsKeys = array_keys($menuItems);
		$tab = array_shift($menuItemsKeys);
	}
}
if (isset($menuItems[$tab]))
{
	$menuItems[$tab]['ACTIVE'] = true;
}
$this->setViewTarget('left-panel');
$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrappermenu',
	'',
	[
		'ID' => 'landing-domain-left-menu',
		'ITEMS' => $menuItems,
		'TITLE' => Loc::getMessage('LANDING_TPL_TITLE_MENU')
	]
);
$this->endViewTarget();

// help link
if ($menuItems[$tab]['HELP_CODE'])
{
	$helpUrl = \Bitrix\Landing\Help::getHelpUrl(
		$menuItems[$tab]['HELP_CODE']
	);
	if ($helpUrl)
	{
		$this->setViewTarget('inside_pagetitle');
		?><a class="landing-domain-link" href="<?= $helpUrl;?>">
			<?= Loc::getMessage('LANDING_TPL_HELP_LINK');?>
			<span data-hint="<?= Loc::getMessage('LANDING_TPL_HELP_LINK_HINT');?>" class="ui-hint"></span>
		</a><?
		$this->endViewTarget();
	}
}

// content
if (isset($menuItems[$tab]))
{
	Manager::setPageTitle($menuItems[$tab]['NAME']);
	if ($tab == 'provider' && $arResult['IS_FREE_DOMAIN'])
	{
		if (Register::isDomainActive($arResult['DOMAIN_NAME']))
		{
			?>
			<div class="landing-domain-state landing-domain-state-success">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_AVAILABLE_TITLE');?></div>
				<div class="landing-domain-state-info">
					<span class="landing-domain-state-info-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_AVAILABLE_LABEL', ['#DOMAIN_NAME#' => $arResult['DOMAIN_NAME']]);?></span>
				</div>
				<div class="landing-domain-state-image">
					<div class="landing-domain-state-image-value"></div>
				</div>
			</div>
			<?
		}
		else
		{
			?>
			<div class="landing-domain-state landing-domain-state-wait">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_TITLE');?></div>
				<div class="landing-domain-state-info">
					<span class="landing-domain-state-info-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_LABEL', ['#DOMAIN_NAME#' => $arResult['DOMAIN_NAME']]);?></span>
				</div>
				<div class="landing-domain-state-image">
					<div class="landing-domain-state-image-value"></div>
				</div>
				<div class="landing-domain-state-notice"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_TEXT');?></div>
			</div>
			<?
		}
		return;
	}
	else if ($tab == 'provider' && !$arResult['FEATURE_FREE_AVAILABLE'] && $arResult['PROVIDER_SITES'])
	{
		$anotherSite = array_shift($arResult['PROVIDER_SITES']);
		$replace = [
			'#SITE_NAME#' => $anotherSite['TITLE'],
			'#DOMAIN_NAME#' => $anotherSite['DOMAIN_NAME']
		];
		?>
		<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post">
			<input type="hidden" name="action" value="switch">
			<input type="hidden" name="param" value="<?= $anotherSite['ID'];?>">
			<?= bitrix_sessid_post();?>
			<div class="landing-domain-state landing-domain-state-free">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_H1', $replace);?></div>
				<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_TEXT', $replace);?></div>
				<div class="landing-domain-state-image"></div>
				<div class="landing-domain-state-free-detail">
					<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_ALERT', $replace);?></div>
					<div class="landing-domain-state-detail-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_NOTICE');?></div>
				</div>
				<button type="submit" class="ui-btn ui-btn-light-border">
					<?= Loc::getMessage('LANDING_TPL_SWITCH');?>
				</button>
			</div>
		</form>
		<?
		return;
	}
	else if ($arResult['IS_FREE_DOMAIN'])
	{
		$APPLICATION->includeComponent(
			'bitrix:landing.site_domain_switch',
			'',
			array(
				'SITE_ID' => $arParams['SITE_ID'],
				'MODE' => 'CHANGE_GIFT'
			),
			$component
		);
		return;
	}

	$currentDomain = ($tab == 'provider') ? $arResult['REGISTER']->getPortalDomains() : [];

	if ($currentDomain)
	{
		$puny = new \CBXPunycode;
		$currentDomain = $puny->decode(array_shift($currentDomain));
		$replace = [
			'#DOMAIN_NAME#' => $currentDomain
		];
		?>
		<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post">
			<input type="hidden" name="action" value="SaveProvider">
			<input type="hidden" name="param" value="<?= $currentDomain;?>">
			<?= bitrix_sessid_post();?>
			<div class="landing-domain-state landing-domain-state-free">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_H1');?></div>
				<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_TEXT', $replace);?></div>
				<div class="landing-domain-state-image"></div>
				<div class="landing-domain-state-free-detail">
					<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_ALERT', $replace);?></div>
					<div class="landing-domain-state-detail-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_NOTICE');?></div>
				</div>
				<button type="submit" class="ui-btn ui-btn-light-border">
					<?= Loc::getMessage('LANDING_TPL_GET_FREE');?>
				</button>
			</div>
		</form>
		<?
		return;
	}
	?>
	<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post" class="ui-form ui-form-gray-padding">
		<input type="hidden" name="action" value="save<?= $tab;?>">
		<?= bitrix_sessid_post();?>
		<?
		include $tab . '.php';
		?>
	</form>
	<script>
		BX.ready(function()
		{
			BX.message({
				LANDING_TPL_ERROR_DOMAIN_EXIST: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST'));?>',
				LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED'));?>',
				LANDING_TPL_ERROR_DOMAIN_EMPTY: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));?>',
				LANDING_TPL_ALERT_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ALERT_TITLE'));?>',
				LANDING_TPL_DOMAIN_AVAILABLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_AVAILABLE'));?>',
				LANDING_TPL_ERROR_DOMAIN_INCORRECT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_INCORRECT'));?>',
				LANDING_TPL_ERROR_DOMAIN_CHECK_DASH: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH'));?>',
				LANDING_TPL_ERROR_DOMAIN_CHECK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK', ['#TLD#' => strtolower($arResult['TLD'][0])]));?>'
			});
		});
	</script>
	<?
}