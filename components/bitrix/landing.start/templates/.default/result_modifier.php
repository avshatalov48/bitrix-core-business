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
		localRedirect($arParams['PAGE_URL_SITES']);
	}
}
if ($session->has('LANDING_OPEN_SIDE_PANEL'))
{
	?>
	<script>
		BX.ready(function()
		{
			BX.SidePanel.Instance.open('<?= \CUtil::JSEscape($session['LANDING_OPEN_SIDE_PANEL'])?>', {allowChangeHistory: false});
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

Loc::loadMessages(dirname(__FILE__) . '/template.php');

\Bitrix\Main\UI\Extension::load(['ajax', 'landing_master']);
$disableFrame = $this->getPageName() == 'landing_view';

ob_start();
?>
<script type="text/javascript">
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
	<script type="text/javascript">
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
elseif (in_array($this->getPageName(), array('template', 'site_show')))
{
	$link = '';
	$title = '';

	if ($this->getPageName() == 'site_show')
	{
		if ($arResult['ACCESS_PAGE_NEW'] == 'Y')
		{
			$title = Loc::getMessage('LANDING_TPL_ADD_PAGE');
			$link = $arParams['PAGE_URL_LANDING_EDIT'];
			$link = str_replace(
				array('#site_show#', '#landing_edit#'),
				array($arResult['VARS']['site_show'], 0),
				$link);

			$folderId = $request->get($arParams['ACTION_FOLDER']);
			if ($folderId)
			{
				$link = new \Bitrix\Main\Web\Uri($link);
				$link->addParams(array(
					$arParams['ACTION_FOLDER'] => $folderId
				));
				$link = $link->getUri();
			}
		}
	}
	else if ($arResult['ACCESS_SITE_NEW'] == 'Y')
	{
		$title = Loc::getMessage('LANDING_TPL_ADD_SITE_' . $arParams['TYPE']);
		$link = $arParams['PAGE_URL_SITE_EDIT'];
		$link = str_replace('#site_edit#', 0, $link);
		if (!$title)
		{
			$title = Loc::getMessage('LANDING_TPL_ADD_SITE');
		}
	}

	$folderId = $request->get($arParams['ACTION_FOLDER']);

	// settings menu
	$settingsLink = [];
	if (
		$arResult['VARS']['site_show'] > 0 &&
		$arResult['ACCESS_SITE_SETTINGS'] == 'Y'
	)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_SETTING_SITE'),
			'LINK' => $linkSett = str_replace(
				'#site_edit#',
				$arResult['VARS']['site_show'],
				$arParams['PAGE_URL_SITE_EDIT']
			)
		];
		if ($arParams['TYPE'] == 'STORE')
		{
			$uriSettCatalog = new \Bitrix\Main\Web\Uri($linkSett);
			$uriSettCatalog->addParams(['tpl' => 'catalog']);
			$settingsLink[] = [
				'TITLE' => Loc::getMessage('LANDING_TPL_SETTING_CATALOG'),
				'LINK' => $uriSettCatalog->getUri()
			];
			unset($linkSett, $uriSettCatalog);
		}
	}
	// add site import button
	else if ($arResult['ACCESS_SITE_NEW'] == 'Y')
	{
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

	if (
		$arResult['VARS']['site_show'] <= 0 &&
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

	if ($folderId)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_FOLDER_EDIT'),
			'LINK' => str_replace('#folder_edit#', $folderId, $arParams['PAGE_URL_FOLDER_EDIT'])
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
			'BUTTONS' => ($link && $title)
							? array(
								array(
									'LINK' => $link,
									'TITLE' => $title
								)
							)
							: array(),
			'TYPE' => $arParams['TYPE'],
			'DRAFT_MODE' => $arParams['DRAFT_MODE'],
			'FOLDER_ID' => $folderId,
			'FOLDER_SITE_ID' => $arResult['VARS']['site_show']
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
	<script type="text/javascript">
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