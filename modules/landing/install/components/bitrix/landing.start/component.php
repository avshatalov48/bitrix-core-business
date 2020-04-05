<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Crm\WebForm\Preset;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Main\Loader;
use \Bitrix\Main\SiteTemplateTable;
use \Bitrix\Main\UserConsent\Agreement;
use \Bitrix\Main\UserConsent\Internals\AgreementTable;
use \Bitrix\Main\UserConsent\Internals\ConsentTable;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('landing'))
{
	Showerror(Loc::getMessage('LANDING_CMP_MODULE_NOT_INSTALLED'));
	return;
}

// something about crm
if (Loader::includeModule('crm'))
{
	// set webform presets
	if (Preset::checkVersion())
	{
		$preset = new Preset();
		$preset->install();
	}
	// install demo data for crm
	if (!CAllCrmInvoice::installExternalEntities())
	{
		Showerror(Loc::getMessage('LANDING_CMP_MODULE_NOT_INSTALLED_CRM'));
		return;
	}
}

// refresh block repo
\Bitrix\Landing\Manager::getRestPath();
\Bitrix\Landing\Block::getRepository();

// check rights
if (Loader::includeModule('bitrix24'))
{
	if (
		Manager::getOption('temp_permission_admin_only')
		&& !\CBitrix24::isPortalAdmin(Manager::getUserId())
	)
	{
		Manager::setPageTitle(
			Loc::getMessage('LANDING_CMP_TITLE')
		);
		Manager::getApplication()->showAuthForm(
			Loc::getMessage('LANDING_CMP_ACCESS_DENIED2')
		);
		return;
	}
}
else
{
	if (Manager::getApplication()->getGroupRight('landing') < 'W')
	{
		Manager::setPageTitle(
			Loc::getMessage('LANDING_CMP_TITLE')
		);
		Manager::getApplication()->showAuthForm(
			Loc::getMessage('LANDING_CMP_ACCESS_DENIED2')
		);
		return;
	}
}

$defaultUrlTemplates404 = array(
	'sites' => '',
	'site_show' => 'site/#site_show#/',
	'site_edit' => 'site/edit/#site_edit#/',
	'landing_edit' => 'site/#site_show#/edit/#landing_edit#/',
	'landing_view' => 'site/#site_show#/view/#landing_edit#/',
	'domains' => 'domains/',
	'domain_edit' => 'domain/edit/#domain_edit#/'
);
$defaultVariableAliases = array(
	'site_show' => 'site_show',
	'site_edit' => 'site_edit',
	'landing_edit' => 'landing_edit',
	'landing_view' => 'landing_view',
	'domain_edit' => 'domain_edit',
	'domains' => 'domains'
);
$varToTpl = array(
	'domains' => 'domains',
	'landing_edit' => 'landing_edit',
	'landing_view' => 'landing_view',
	'site_show' => 'site_show',
	'site_edit' => 'site_edit',
	'domain_edit' => 'domain_edit'
);
$utlTpls = array(
	'sites' => array(),
	'site_show' => array('site_show'),
	'site_edit' => array('site_edit'),
	'landing_edit' => array('landing_edit', 'site_show'),
	'landing_view' => array('landing_edit', 'site_show'),
	'domains' => array(),
	'domain_edit' => array('domain_edit')
);


$variables = array();
$componentPage = '';
$curPage = '';
$request = Application::getInstance()->getContext()->getRequest();
$uriString = $request->getRequestUri();
$landingTypes = \Bitrix\Landing\Site::getTypes();

$arResult['AGREEMENT'] = array();
$arParams['ACTION_FOLDER'] = isset($arParams['ACTION_FOLDER']) ? $arParams['ACTION_FOLDER'] : 'folderId';
$arParams['SEF_MODE'] = isset($arParams['SEF_MODE']) ? $arParams['SEF_MODE'] : 'Y';
$arParams['SEF_FOLDER'] = isset($arParams['SEF_FOLDER']) ? $arParams['SEF_FOLDER'] : '/';
$arParams['SEF_URL_TEMPLATES'] = isset($arParams['SEF_URL_TEMPLATES']) ? $arParams['SEF_URL_TEMPLATES'] : array();
$arParams['VARIABLE_ALIASES'] = isset($arParams['VARIABLE_ALIASES']) ? $arParams['VARIABLE_ALIASES'] : array();

foreach ($defaultUrlTemplates404 as $pageCode => $pagePath)
{
	if (!isset($arParams['SEF_URL_TEMPLATES'][$pageCode]))
	{
		$arParams['SEF_URL_TEMPLATES'][$pageCode] = $pagePath;
	}
}

// site types
if (
	!isset($arParams['TYPE']) ||
	!isset($landingTypes[$arParams['TYPE']])
)
{
	$arParams['TYPE'] = \Bitrix\Landing\Site::getDefaultType();
}

// sef / not sef modes
if ($arParams['SEF_MODE'] == 'Y')
{
	$defaultVariableAliases404 = array();
	$componentVariables = array();

	$urlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
		$defaultUrlTemplates404,
		$arParams['SEF_URL_TEMPLATES']
	);
	$variableAliases = \CComponentEngine::MakeComponentVariableAliases(
		$defaultVariableAliases404,
		$arParams['VARIABLE_ALIASES']
	);
	$componentPage = \CComponentEngine::ParseComponentPath(
		$arParams['SEF_FOLDER'],
		$urlTemplates,
		$variables
	);

	\CComponentEngine::InitComponentVariables(
		$componentPage,
		$componentVariables,
		$variableAliases,
		$variables
	);

	// build urls by rules
	foreach ($utlTpls as $code => $var)
	{
		$arParams['PAGE_URL_' . strtoupper($code)] = $arParams['SEF_FOLDER'] . $urlTemplates[$code];
	}
}
else
{
	$componentVariables = array();
	foreach ($defaultVariableAliases as $var)
	{
		$componentVariables[] = isset($arParams['VARIABLE_ALIASES'][$var])
								? $arParams['VARIABLE_ALIASES'][$var]
								: $var;
	}

	$variableAliases = \CComponentEngine::MakeComponentVariableAliases(
		$defaultVariableAliases,
		$arParams['VARIABLE_ALIASES']
	);

	\CComponentEngine::InitComponentVariables(
		false,
		$componentVariables,
		$variableAliases,
		$variables
	);

	foreach ($varToTpl as $var => $tpl)
	{
		if (isset($variables[$var]))
		{
			$componentPage = $tpl;
			break;
		}
	}

	// vars for clear from url
	$deleteUrl = array();
	foreach ($utlTpls as $code => $var)
	{
		if (empty($var))
		{
			$deleteUrl[] = isset($arParams['VARIABLE_ALIASES'][$code])
							? $arParams['VARIABLE_ALIASES'][$code]
							: $code;
		}
		else
		{
			foreach ($var as $v)
			{
				$deleteUrl[] = isset($arParams['VARIABLE_ALIASES'][$v])
								? $arParams['VARIABLE_ALIASES'][$v]
								: $v;
			}
		}
	}
	// build urls by rules
	foreach ($utlTpls as $code => $var)
	{
		$paramCode = 'PAGE_URL_' . strtoupper($code);
		$uri = new Uri($uriString);
		$uri->deleteParams($deleteUrl);
		if (empty($var))
		{
			if (isset($arParams['VARIABLE_ALIASES'][$code]))
			{
				$code = $arParams['VARIABLE_ALIASES'][$code];
			}
			$uri->addParams(array(
				$code => 'Y'
			));
		}
		else
		{
			foreach ($var as $v)
			{
				if (isset($arParams['VARIABLE_ALIASES'][$v]))
				{
					$v = $arParams['VARIABLE_ALIASES'][$v];
				}
				$uri->addParams(array(
					$v => '#' . $v . '#'
				));
			}
		}
		$arParams[$paramCode] = urldecode($uri->getUri());
	}
}

$arResult['VARS'] = $variables;

// check rules for templates
if (
	$arParams['SEF_MODE'] == 'Y' &&
	isset($arParams['PAGE_URL_LANDING_VIEW'])
)
{
	$condition = $arParams['PAGE_URL_LANDING_VIEW'];
	$condition = str_replace(
		array('#site_show#', '#landing_edit#'),
		'[\\d]+',
		$condition
	);
	$condition = 'preg_match(\'#' . $condition . '#\', ' .
				 '$GLOBALS[\'APPLICATION\']->GetCurPage(0))';
	$res = SiteTemplateTable::getList(array(
		'select' => array(
			'ID'
		),
		'filter' => array(
			'SITE_ID' => SITE_ID,
			'=CONDITION' => $condition
		)
	));
	if (!$res->fetch())
	{
		SiteTemplateTable::add(array(
			'TEMPLATE' => Manager::getTemplateId(SITE_ID),
			'SITE_ID' => SITE_ID,
			'SORT' => 500,
			'CONDITION' => $condition
		));
		Manager::getCacheManager()->clean('b_site_template');
		\localRedirect(Manager::getApplication()->getCurPage());
	}
}

// disable domain's pages in the cloud
if (
	($componentPage == 'domains' || $componentPage == 'domain_edit') &&
	\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
)
{
	$componentPage = '';
}

// only AGREEMENTS below

if (
	$request->get('landing_mode') ||
	!\Bitrix\Landing\Manager::isB24()
)
{
	$this->IncludeComponentTemplate($componentPage);
	return;
}

$currentLang = LANGUAGE_ID;
$agreementCode = 'landing_agreement';
$agreementsId = array();
$agreements = array(
	'ru' => array(),
	'es' => array(),
	'en' => array(),
	$currentLang => array()
);
$virtualLangs = array(
	'ua' => 'ru',
	'by' => 'ru',
	'kz' => 'ru',
	'la' => 'es'
);

// actual from lang-file
foreach ($agreements as $lng => $item)
{
	if (file_exists(__DIR__ . '/lang/' . $lng . '/component.php'))
	{
		include __DIR__ . '/lang/' . $lng . '/component.php';
		$agreements[$lng] = array(
			'ID' => 0,
			'NAME' => isset($MESS['LANDING_CMP_AGREEMENT_NAME'])
						? $MESS['LANDING_CMP_AGREEMENT_NAME']
						: '',
			'TEXT' => isset($MESS['LANDING_CMP_AGREEMENT_TEXT'])
						? $MESS['LANDING_CMP_AGREEMENT_TEXT']
						: '',
			'LANGUAGE_ID' => $lng,
		);
	}
}

// current from database (actualize in db)
$res = AgreementTable::getList(array(
	'select' => array(
		'ID',
		'NAME',
		'TEXT' => 'AGREEMENT_TEXT',
		'LANGUAGE_ID'
	),
	'filter' => array(
		'=ACTIVE' => 'Y',
		'=CODE' => $agreementCode,
		'=LANGUAGE_ID' => array_keys($agreements)
	)
));
while ($row = $res->fetch())
{
	$agreementsId[] = $row['ID'];
	$actual = $agreements[$row['LANGUAGE_ID']];
	if (
		$row['NAME'] != $actual['NAME'] ||
		$row['TEXT'] != $actual['TEXT']
	)
	{
		AgreementTable::update($row['ID'], array(
			'NAME' => $actual['NAME'],
			'AGREEMENT_TEXT' => $actual['TEXT']
		));
	}
	$agreements[$row['LANGUAGE_ID']]['ID'] = $row['ID'];
}

// add new to db
foreach ($agreements as $lng => $agreement)
{
	if (!$agreement['ID'])
	{
		$res = AgreementTable::add(array(
			'CODE' => $agreementCode,
			'LANGUAGE_ID' => $lng,
			'TYPE' => Agreement::TYPE_CUSTOM,
			'NAME' => $agreement['NAME'],
			'AGREEMENT_TEXT' => $agreement['TEXT']
		));
		if ($res->isSuccess())
		{
			$agreements[$lng]['ID'] = $res->getId();
		}
	}
}

if (
	!empty($agreements[$currentLang]) &&
	$agreements[$currentLang]['ID']
)
{
	$arResult['AGREEMENT'] = $agreements[$currentLang];
}
elseif (
	isset($virtualLangs[$currentLang]) &&
	!empty($agreements[$virtualLangs[$currentLang]]) &&
	$agreements[$virtualLangs[$currentLang]]['ID']
)
{
	$arResult['AGREEMENT'] = $agreements[$virtualLangs[$currentLang]];
}
elseif (
	!empty($agreements['en']) &&
	$agreements['en']['ID']
)
{
	$arResult['AGREEMENT'] = $agreements['en'];
}
else
{
	$redirectIfUnAcept = true;
}

// check accepted
$res = ConsentTable::getList(array(
	'filter' => array(
		'USER_ID' => Manager::getUserId(),
		'AGREEMENT_ID' => $agreementsId
	)
));
if ($res->fetch())
{
	$redirectIfUnAcept = false;
	$arResult['AGREEMENT'] = array();
}

// accept
if (
	$request->get('action') == 'accept_agreement' &&
	!empty($arResult['AGREEMENT']) &&
	check_bitrix_sessid()
)
{
	\Bitrix\Main\UserConsent\Consent::addByContext(
		$arResult['AGREEMENT']['ID']
	);
	LocalRedirect($uriString);
}

// if not accept and don't exist agreement
if (
	isset($redirectIfUnAcept) &&
	$redirectIfUnAcept === true
)
{
	LocalRedirect(SITE_DIR, true);
}

$this->IncludeComponentTemplate($componentPage);