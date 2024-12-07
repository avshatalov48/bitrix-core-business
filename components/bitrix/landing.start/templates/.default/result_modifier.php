<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

$context = Application::getInstance()->getContext();
$session = Application::getInstance()->getSession();
$request = $context->getRequest();

// some pages we should open only in slider
if ($request->get('IFRAME') !== 'Y' && $context->getServer()->getRequestMethod() === 'GET')
{
	if (!in_array($this->getPageName(), ['template', 'sites', 'site_show', 'landing_view', 'roles', 'role_edit', 'notes']))
	{
		$session->set('LANDING_OPEN_SIDE_PANEL', Application::getInstance()->getContext()->getRequest()->getRequestUri());
		//when opening link to create page in existing site
		if (($arResult['VARS']['site_show'] ?? 0) > 0 && $arResult['VARS']['landing_edit'] === '0' && $this->getPageName() === 'landing_edit')
		{
			localRedirect('/sites/site/' . $arResult['VARS']['site_show'] . '/');
		}
		localRedirect($arParams['PAGE_URL_SITES']);
	}
}
if ($session->has('LANDING_OPEN_SIDE_PANEL'))
{
	?>
	<script>
		BX.ready(function()
		{
			<?php if (preg_match('/width=([\d]+)/', $session['LANDING_OPEN_SIDE_PANEL'], $matches)):?>
			BX.SidePanel.Instance.open('<?= \CUtil::JSEscape($session['LANDING_OPEN_SIDE_PANEL'])?>', {allowChangeHistory: false, width: <?= $matches[0]?>});
			<?php else:?>
			BX.SidePanel.Instance.open('<?= \CUtil::JSEscape($session['LANDING_OPEN_SIDE_PANEL'])?>', {allowChangeHistory: false});
			<?php endif?>
		});
	</script>
	<?
	$session->remove('LANDING_OPEN_SIDE_PANEL');
}

// special design for next sliders
if (in_array($this->getPageName(), ['site_domain', 'site_domain_switch', 'site_cookies', 'notes']))
{
	\Bitrix\Landing\Manager::getApplication()->restartBuffer();
	return;
}

Loc::loadMessages(__DIR__ . '/template.php');

\Bitrix\Main\UI\Extension::load(['ajax', 'landing_master', 'bitrix24.phoneverify']);
$disableFrame = $this->getPageName() == 'landing_view';

ob_start();
?>
<script>
	BX.message({
		LANDING_TPL_JS_PAY_TARIFF_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_JS_PAY_TARIFF_TITLE'));?>',
		LANDING_TPL_JS_PAY_TARIFF: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_JS_PAY_TARIFF'));?>'
	});
</script>
<?
\Bitrix\Main\Page\Asset::getInstance()->addString(ob_get_contents());
ob_end_clean();

// prepare links
if ($arParams['SEF_MODE'] != 'Y')
{
	foreach ($arParams['VARIABLE_ALIASES'] as $k => $v)
	{
		$majorVars = array('PAGE_URL_SITE_SHOW', 'PAGE_URL_SITE_EDIT',
							'PAGE_URL_LANDING_EDIT', 'PAGE_URL_LANDING_VIEW',
							'PAGE_URL_DOMAIN_EDIT');
		foreach ($majorVars as $code)
		{
			$arParams[$code] = str_replace(
				'#' . $v . '#',
				'#' . $k . '#',
				$arParams[$code]
			);
		}
	}
}

// iframe header
if ($request->get('IFRAME') == 'Y' && !$disableFrame)
{
	\Bitrix\Landing\Manager::getApplication()->restartBuffer();
	include 'slider_header.php';
}
elseif ($request->get('IFRAME') == 'N')
{
	$request = $context->getRequest();
	$redirect = new \Bitrix\Main\Web\Uri($request->getRequestUri());
	$redirect->deleteParams(array(
		'IFRAME'
	));
	?>
	<script>
		window.top.location.href = "<?= \CUtil::JSEscape($redirect->getUri());?>";
	</script>
	<?
	include 'slider_footer.php';
	\CMain::finalActions();
	die();
}
// ajax
elseif ($request->get('IS_AJAX') == 'Y')
{
	\Bitrix\Landing\Manager::getApplication()->restartBuffer();
}
// add filter and action button

elseif (in_array($this->getPageName(), ['template', 'site_show']))
{
	$link = '';
	$title = '';

	/**
	 * @return LandingBaseComponent
	 */
	$getComponent = function () use ($arParams, $arResult) {
		$componentClass = \CBitrixComponent::includeComponentClass('bitrix:landing.base');
		$component = new $componentClass;
		$component->arParams['TYPE'] = $arParams['TYPE'];
		$component->arParams['PAGE_URL_LANDING_EDIT'] = $arParams['PAGE_URL_LANDING_EDIT'];
		$component->arParams['PAGE_URL_SITE_EDIT'] = $arParams['PAGE_URL_SITE_EDIT'];
		$component->arParams['ACTION_FOLDER'] = $arParams['ACTION_FOLDER'];
		$component->arParams['SITE_ID'] = $arResult['VARS']['site_show'] ?? 0;

		return $component;
	};

	if (
		$this->getPageName() == 'site_show'
		&& $arResult['ACCESS_PAGE_NEW'] == 'Y'
	)
	{
		$link = $getComponent()->getUrlAdd(false, [
			'context_section' => 'pages_list',
			'context_element' => 'top_button',
		]);
		$title = Loc::getMessage('LANDING_TPL_ADD_PAGE');
	}
	else if ($arResult['ACCESS_SITE_NEW'] == 'Y')
	{
		$link = $getComponent()->getUrlAdd(true, [
			'context_section' => 'site_list',
			'context_element' => 'top_button',
		]);
		$title = Loc::getMessage('LANDING_TPL_ADD_SITE_2');
	}

	$folderId = $request->get($arParams['ACTION_FOLDER']);

	// settings menu
	$settingsLink = [];
	if (
		($arResult['VARS']['site_show'] ?? 0) &&
		$arResult['ACCESS_SITE_SETTINGS'] == 'Y'
	)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_SETTING'),
			'LINK' => str_replace(
				'#site_edit#',
				$arResult['VARS']['site_show'],
				$arParams['PAGE_URL_SITE_SETTINGS']
			)
		];
	}
	// add site import button
	else if ($arResult['ACCESS_SITE_NEW'] == 'Y')
	{
		$importUrl = \Bitrix\Landing\Transfer\Import\Site::getUrl(
			$arParams['TYPE']
		);
		if ($importUrl)
		{
			$settingsLink[] = ['TITLE' => '', 'LINK' => ''];
			$settingsLink[] = [
				'TITLE' => Loc::getMessage('LANDING_TPL_IMPORT_SITE_' . $arParams['TYPE']),
				'LINK' => $importUrl
			];
		}
	}
	// add rights button
	if (\Bitrix\Landing\Rights::isAdmin())
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_MENU_RIGHTS'),
			'LINK' => $arParams['PAGE_URL_ROLES'],
			'DATASET' => [
				'skipSlider' => true
			],
		];
	}

	if (
		($arResult['VARS']['site_show'] ?? 0) <= 0 &&
		(LANGUAGE_ID === 'ru' || LANGUAGE_ID === 'ua') &&
		($arParams['TYPE'] == 'PAGE' || $arParams['TYPE'] == 'STORE') &&
		!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') &&
		\Bitrix\Main\ModuleManager::isModuleInstalled('sale')
	)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_DEV_SITE'),
			'LINK' => '/bitrix/components/bitrix/sale.bsm.site.master/slider.php'
		];
	}

	if ($this->getPageName() !== 'site_show')
	{
		if (count($settingsLink) > 0)
		{
			$settingsLink[] = ['TITLE' => '', 'LINK' => '', 'DELIMITER' => true];
		}

		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_MENU_AGREEMENT'),
			'LINK' => 'javascript:landingAgreementPopup()',
			'DATASET' => [
				'skipSlider' => true
			],
		];
	}

	if ($folderId)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_FOLDER_EDIT'),
			'LINK' => str_replace('#folder_edit#', $folderId, $arParams['PAGE_URL_FOLDER_EDIT'])
		];
	}

	$buttons = [];
	if ($link && $title)
	{
		$button = [
			'LINK' => $link,
			'TITLE' => $title,
		];
		if (
			$arParams['TYPE'] === 'STORE'
			&& $arResult['ACCESS_SITE_NEW'] == 'Y'
			&& \Bitrix\Main\Loader::includeModule('catalog')
			&& \Bitrix\Catalog\Config\State::isExternalCatalog()
		)
		{
			$button['DISABLED'] = true;
		}
		$buttons = [
			$button
		];
	}
	$APPLICATION->IncludeComponent(
		'bitrix:landing.filter',
		'.default',
		array(
			'FILTER_TYPE' => $this->getPageName() == 'site_show'
							? 'LANDING'
							: 'SITE',
			'SETTING_LINK' => $settingsLink,
			'BUTTONS' => $buttons,
			'TYPE' => $arParams['TYPE'],
			'DRAFT_MODE' => $arParams['DRAFT_MODE'],
			'FOLDER_ID' => $folderId,
			'FOLDER_SITE_ID' => $arResult['VARS']['site_show'] ?? 0
		),
		$this->__component
	);

	unset($settingsLink);
}

include __DIR__ . '/popups/agreement.php';

if (
	$request->get('agreement') == 'Y' &&
	!$request->get('landing_mode') &&
	\Bitrix\Landing\Manager::isB24()
)
{
	?>
	<script>
		BX.ready(function()
		{
			if (typeof landingAgreementPopup !== 'undefined')
			{
				landingAgreementPopup();
			}
		});
	</script>
	<?
}

// backward compatibility
if ($arResult['AGREEMENT_ACCEPTED'])
{
	$arResult['AGREEMENT'] = [];
}