<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(dirname(__FILE__) . '/template.php');

ob_start();
?>
<script type="text/javascript">
	BX.message({
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
if ($request->get('IFRAME') == 'Y')
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
	else
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

	$APPLICATION->IncludeComponent(
		'bitrix:landing.filter',
		'.default',
		array(
			'FILTER_TYPE' => $this->getPageName() == 'site_show'
							? 'LANDING'
							: 'SITE',
			'SETTING_LINK' => ($arResult['VARS']['site_show'] > 0)
								? str_replace(
									'#site_edit#',
									$arResult['VARS']['site_show'],
									$arParams['PAGE_URL_SITE_EDIT']
								)
								: '',
			'BUTTONS' => array(
				array(
					'LINK' => $link,
					'TITLE' => $title
				)
			),
			'TYPE' => $arParams['TYPE'],
			'FOLDER_SITE_ID' => !$folderId ? $arResult['VARS']['site_show'] : 0
		),
		$this->__component
	);
}

if (
	$request->get('agreement') == 'Y' &&
	!$request->get('landing_mode') &&
	\Bitrix\Landing\Manager::isB24()
)
{
	include __DIR__ . '/popups/agreement.php';
}