<?php
if (
	isset($_GET['template']) &&
	preg_match('/^[a-z0-9_]+$/i', $_GET['template'])
)
{
	define('SITE_TEMPLATE_ID', $_GET['template']);
}
else
{
	define('SITE_TEMPLATE_ID', 'landing24');
}
if (
	isset($_GET['site']) &&
	preg_match('/^[a-z0-9_]+$/i', $_GET['site'])
)
{
	define('SITE_ID', $_GET['site']);
}

define('B24CONNECTOR_SKIP', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Landing;

\Bitrix\Main\Loader::includeModule('landing');

// vars
$request = Application::getInstance()->getContext()->getRequest();
$application = \Bitrix\Landing\Manager::getApplication();
$site = $request->get('site');
$template = $request->get('template');
$landingId = $request->get('id');
define('SMN_SITE_ID', $site);

// check rights
if ($application->getGroupRight('landing') < 'W')
{
	$application->authForm(Loc::getMessage('ACCESS_DENIED'));
}

// redirect from frame
if ($request->get('IFRAME') == 'N')
{
	$context = \Bitrix\Main\Application::getInstance()->getContext();
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
	include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/landing.start/templates/.default/slider_header.php';
	\CMain::finalActions();
	die();
}

// get info about site
$res = Landing::getList(array(
	'select' => array(
		'ID',
		'SITE_ID',
		'SITE_TYPE' => 'SITE.TYPE'
	),
	'filter' => array(
		'ID' => $landingId
	)
));
if ($landing = $res->fetch())
{
	// paths
	$mainPage = 'landing_site.php?lang=' . LANGUAGE_ID . '&site=' . $site;
	$mainPageLogo = 'landing_site.php?lang=' . LANGUAGE_ID . '&logo&site=' . $site;
	$landingsPage = 'landing_site.php?lang=' . LANGUAGE_ID . '&siteId=#site_show#' . ($site ? '&site=' . $site : '');
	$editPage = $landingsPage . '&cmp=landing_edit&id=#landing_edit#';
	$editSite = $landingsPage . '&cmp=site_edit';
	$editSite .= ($template ? '&template=' . $template : '');
	$viewPage ='landing_view.php?lang=' . LANGUAGE_ID . '&id=#landing_edit#'.  ($site ? '&site=' . $site : '');
	$viewPage .= ($template ? '&template=' . $template : '');

	$replace = array(
		'#site_show#' => $landing['SITE_ID'],
		'#landing_edit#' => $landing['ID']
	);

	$APPLICATION->IncludeComponent(
		'bitrix:landing.landing_view',
		'.default',
		array(
			'TYPE' => 'SMN',
			'SITE_ID' => $landing['SITE_ID'],
			'LANDING_ID' => $landingId,
			'PAGE_URL_LANDINGS' => str_replace(array_keys($replace), $replace, $landingsPage),
			'PAGE_URL_LANDING_EDIT' => str_replace(array_keys($replace), $replace, $editPage),
			'PAGE_URL_SITE_EDIT' => str_replace(array_keys($replace), $replace, $editSite),
			'PAGE_URL_URL_SITES' => $mainPageLogo,
			'PARAMS' => array(
				'sef_url' => array(
					'landing_edit' => $editPage,
					'landing_view' => $viewPage,
					'site_show' => $landingsPage,
					'site_edit' => str_replace('#site_show#', '#site_edit#', $editSite)
				)
			)
		),
		false
	);
}


require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');