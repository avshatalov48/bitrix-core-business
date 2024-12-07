<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Crm\WebForm\Preset;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Main\Loader;
use \Bitrix\Main\SiteTemplateTable;
use \Bitrix\Main\UserConsent\Consent;
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
Block::getRepository();
$arParams['TYPE'] = isset($arParams['TYPE']) ? $arParams['TYPE'] : '';
$arParams['STRICT_TYPE'] = isset($arParams['STRICT_TYPE']) ? $arParams['STRICT_TYPE'] : 'N';

Manager::setPageTitle(
	Loc::getMessage('LANDING_CMP_TITLE')
);

if (!\Bitrix\Landing\Site\Type::isEnabled($arParams['TYPE']))
{
	Showerror(Loc::getMessage('LANDING_CMP_TYPE_IS_NOT_ENABLED'));
	return;
}

\Bitrix\Landing\Site\Type::setScope(
	$arParams['TYPE']
);

// check rights
\Bitrix\Landing\Role::checkRequiredRoles();
if (Loader::includeModule('bitrix24'))
{
	if (
		Manager::getOption('temp_permission_admin_only')
		&& !\CBitrix24::isPortalAdmin(Manager::getUserId())
	)
	{
		Manager::getApplication()->showAuthForm(
			Loc::getMessage('LANDING_CMP_ACCESS_DENIED2')
		);
		return;
	}
}
if (!Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['menu24'], null, true))
{
	Manager::getApplication()->showAuthForm(
		Loc::getMessage('LANDING_CMP_ACCESS_DENIED2')
	);
	return;
}

// preset paths and sef (.parameters.php)
$defaultUrlTemplates404 = array(
	'sites' => '',
	'site_show' => 'site/#site_show#/',
	'site_edit' => 'site/edit/#site_edit#/',
	'site_design' => 'site/design/#site_edit#/',
	'site_settings' => 'site/settings/#site_edit#/',
	'site_master' => 'site/master/#site_edit#/',
	'site_contacts' => 'site/contacts/#site_edit#/',
	'site_domain' => 'site/domain/#site_edit#/',
	'site_domain_switch' => 'site/domain_switch/#site_edit#/',
	'site_cookies' => 'site/cookies/#site_edit#/',
	'landing_edit' => 'site/#site_show#/edit/#landing_edit#/',
	'landing_design' => 'site/#site_show#/design/#landing_edit#/',
	'landing_view' => 'site/#site_show#/view/#landing_edit#/',
	'landing_settings' => 'site/#site_show#/settings/#landing_edit#/',
	'domains' => 'domains/',
	'domain_edit' => 'domain/edit/#domain_edit#/',
	'roles' => 'roles/',
	'notes' => 'notes/',
	'role_edit' => 'role/edit/#role_edit#/',
	'folder_edit' => 'folder/edit/#folder_edit#/',
);
$urlTpls = array(
	'sites' => array(),
	'site_show' => array('site_show'),
	'site_edit' => array('site_edit'),
	'site_settings' => array('site_edit'),
	'site_design' => array('site_edit'),
	'site_master' => array('site_edit'),
	'site_contacts' => array('site_edit'),
	'site_domain' => array('site_edit'),
	'site_domain_switch' => array('site_edit'),
	'site_cookies' => array('site_edit'),
	'landing_edit' => array('landing_edit', 'site_show'),
	'landing_design' => array('landing_edit', 'site_show'),
	'landing_view' => array('landing_edit', 'site_show'),
	'landing_settings' => array('landing_edit', 'site_show'),
	'domains' => array(),
	'domain_edit' => array('domain_edit'),
	'roles' => array(),
	'notes' => array(),
	'role_edit' => array('role_edit'),
	'folder_edit' => array('folder_edit'),
);

// init vars
$variables = array();
$componentPage = '';
$curPage = '';
$request = Application::getInstance()->getContext()->getRequest();
$uriString = $request->getRequestUri();
$uriPage = $request->getRequestedPage();
$landingTypes = \Bitrix\Landing\Site::getTypes();

// template vars
$arResult['AGREEMENT'] = array();
$arResult['AGREEMENT_ACCEPTED'] = false;
$arResult['CHECK_FEATURE_PERM'] = \Bitrix\Landing\Restriction\Manager::isAllowed('limit_sites_access_permissions');
$arParams['ACTION_FOLDER'] = isset($arParams['ACTION_FOLDER']) ? $arParams['ACTION_FOLDER'] : 'folderId';
$arParams['SEF_MODE'] = isset($arParams['SEF_MODE']) ? $arParams['SEF_MODE'] : 'Y';
$arParams['SEF_FOLDER'] = isset($arParams['SEF_FOLDER']) ? $arParams['SEF_FOLDER'] : '/';
$arParams['SEF_URL_TEMPLATES'] = isset($arParams['SEF_URL_TEMPLATES']) ? $arParams['SEF_URL_TEMPLATES'] : array();
$arParams['VARIABLE_ALIASES'] = isset($arParams['VARIABLE_ALIASES']) ? $arParams['VARIABLE_ALIASES'] : array();
$arParams['TILE_LANDING_MODE'] = isset($arParams['TILE_LANDING_MODE']) ? $arParams['TILE_LANDING_MODE'] : 'edit';
$arParams['TILE_SITE_MODE'] = isset($arParams['TILE_SITE_MODE']) ? $arParams['TILE_SITE_MODE'] : 'list';
$arParams['EDIT_FULL_PUBLICATION'] = isset($arParams['EDIT_FULL_PUBLICATION']) ? $arParams['EDIT_FULL_PUBLICATION'] : 'N';
$arParams['EDIT_PANEL_LIGHT_MODE'] = isset($arParams['EDIT_PANEL_LIGHT_MODE']) ? $arParams['EDIT_PANEL_LIGHT_MODE'] : 'N';
$arParams['EDIT_DONT_LEAVE_FRAME'] = isset($arParams['EDIT_DONT_LEAVE_FRAME']) ? $arParams['EDIT_DONT_LEAVE_FRAME'] : 'N';
$arParams['REOPEN_LOCATION_IN_SLIDER'] = isset($arParams['REOPEN_LOCATION_IN_SLIDER']) ? $arParams['REOPEN_LOCATION_IN_SLIDER'] : 'N';
$arParams['DRAFT_MODE'] = isset($arParams['DRAFT_MODE']) ? $arParams['DRAFT_MODE'] : 'N';
foreach ($defaultUrlTemplates404 as $pageCode => $pagePath)
{
	if (!isset($arParams['SEF_URL_TEMPLATES'][$pageCode]))
	{
		$arParams['SEF_URL_TEMPLATES'][$pageCode] = $pagePath;
	}
}
if (!$arParams['TYPE'] ||  !isset($landingTypes[$arParams['TYPE']]))
{
	$arParams['TYPE'] = \Bitrix\Landing\Site::getDefaultType();
}
if (!isset($arParams['SHOW_MENU']))
{
	$arParams['SHOW_MENU'] = ($arParams['TYPE'] == 'STORE') ? 'N' : 'Y';
}

// sef / not sef modes
if ($arParams['SEF_MODE'] == 'Y')
{
	$defaultVariableAliases404 = [];
	$componentVariables = [];
	// resolve variables, values and template page
	$urlTemplates = \CComponentEngine::makeComponentUrlTemplates(
		$defaultUrlTemplates404,
		$arParams['SEF_URL_TEMPLATES']
	);
	$variableAliases = \CComponentEngine::makeComponentVariableAliases(
		$defaultVariableAliases404,
		$arParams['VARIABLE_ALIASES']
	);
	$componentPage = \CComponentEngine::parseComponentPath(
		$arParams['SEF_FOLDER'],
		$urlTemplates,
		$variables
	);
	\CComponentEngine::initComponentVariables(
		$componentPage,
		$componentVariables,
		$variableAliases,
		$variables
	);
	// build urls by rules
	foreach ($urlTpls as $code => $var)
	{
		$arParams['PAGE_URL_'.mb_strtoupper($code)] = $arParams['SEF_FOLDER'] . $urlTemplates[$code];
	}
}
else
{
	// default variable aliases
	$defaultVariableAliases = [
		'page' => 'page'
	];
	foreach ($urlTpls as $key => $vars)
	{
		foreach ($vars as $var)
		{
			$defaultVariableAliases[$var] = $var;
		}
	}
	// resolve variables and values
	$variableAliases = \CComponentEngine::makeComponentVariableAliases(
		$defaultVariableAliases,
		$arParams['VARIABLE_ALIASES']
	);
	\CComponentEngine::initComponentVariables(
		false,
		$defaultVariableAliases,
		$variableAliases,
		$variables
	);
	// resolve template page
	if (isset($variables['page']) && isset($urlTpls[$variables['page']]))
	{
		$componentPage = $variables['page'];
		if (!$defaultUrlTemplates404[$componentPage])
		{
			$componentPage = '';
		}
	}
	// build urls by rules
	foreach ($urlTpls as $code => $vars)
	{
		$paramCode = 'PAGE_URL_' . mb_strtoupper($code);
		$uri = new Uri($uriPage);
		$uri->addParams(['page' => $code]);

		foreach ($vars as $var)
		{
			if (isset($defaultVariableAliases[$var]))
			{
				$uri->addParams([$var => '#' . $var . '#']);
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
			'=SITE_ID' => SITE_ID,
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
		if ($componentPage == 'landing_view')
		{
			\localRedirect(Manager::getApplication()->getCurPage());
		}
	}
}

// rights
$arResult['ACCESS_PAGE_NEW'] = 'N';
$arResult['ACCESS_SITE_NEW'] = 'N';
$arResult['ACCESS_SITE_SETTINGS'] = 'N';
$rights = Rights::getOperationsForSite(0);
if (
	isset($arResult['VARS']['site_show']) &&
	$arResult['VARS']['site_show']
)
{
	$arResult['ACCESS_PAGE_NEW'] = Rights::hasAccessForSite(
			$arResult['VARS']['site_show'],
			Rights::ACCESS_TYPES['edit']
		)
		? 'Y' : 'N';
	$arResult['ACCESS_SITE_SETTINGS'] = in_array(Rights::ACCESS_TYPES['sett'], $rights)
		? 'Y' : 'N';
}
else
{
	$arResult['ACCESS_SITE_NEW'] = (
		Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['create']) &&
		in_array(Rights::ACCESS_TYPES['edit'], $rights)
	)
		? 'Y' : 'N';
}

// disable domain's pages in the cloud
if ($componentPage == 'domains' || $componentPage == 'domain_edit')
{
	$componentPage = '';
}

// only AGREEMENTS below

if (
	$request->get('landing_mode') ||
	!Manager::isB24()
)
{
	$this->IncludeComponentTemplate($componentPage);
	return;
}

$currentLang = LANGUAGE_ID;
$currentZone = Manager::getZone();
$agreementCode = 'landing_agreement';
$agreementsId = array();
$agreements = array(
	'en' => array(),
	$currentLang => array()
);
$virtualLangs = array(
	'ua' => 'ru',
	'by' => 'ru',
	'kz' => 'ru'
);

if (isset($agreements['es']))
{
	$virtualLangs['la'] = 'es';
}

// lang zone is in CIS
$cis = $currentZone == 'by' || $currentZone == 'kz';

// actual from lang-file
foreach ($agreements as $lng => $item)
{
	if ($cis)
	{
		$mess = Loc::loadLanguageFile(
			__DIR__ . '/component_' . $currentZone . '.php',
			'ru'
		);
	}
	else
	{
		$mess = Loc::loadLanguageFile(__FILE__, $lng);
	}
	if ($mess)
	{
		$agreements[$lng] = array(
			'ID' => 0,
			'NAME' => isset($mess['LANDING_CMP_AGREEMENT_NAME'])
						? $mess['LANDING_CMP_AGREEMENT_NAME']
						: '',
			'TEXT' => isset($mess['LANDING_CMP_AGREEMENT_TEXT4'])
						? $mess['LANDING_CMP_AGREEMENT_TEXT4']
						: '',
			'LANGUAGE_ID' => $lng
		);
	}
}

// check actual agreements
$needToUpdate = Manager::getOption('user_agreement_version') <
				Manager::USER_AGREEMENT_VERSION;
if ($needToUpdate)
{
	Manager::setOption(
		'user_agreement_version',
		Manager::USER_AGREEMENT_VERSION
	);
}

// current from database (actualize in db)
$res = AgreementTable::getList(array(
	'select' => array(
		'ID',
		'NAME',
		'TEXT' => 'AGREEMENT_TEXT',
		'LANGUAGE_ID',
		'LABEL_TEXT'
	),
	'filter' => array(
		'=ACTIVE' => 'Y',
		'=CODE' => $agreementCode,
		'=LANGUAGE_ID' => array_keys($agreements)
	)
));
while ($row = $res->fetch())
{
	if ($needToUpdate)
	{
		AgreementTable::delete($row['ID']);
		continue;
	}

	$agreementsId[] = $row['ID'];
	$actual = $agreements[$row['LANGUAGE_ID']];
	if (
		$row['NAME'] != $actual['NAME'] ||
		$row['TEXT'] != $actual['TEXT']
	)
	{
		AgreementTable::update($row['ID'], [
			'NAME' => $actual['NAME'],
			'AGREEMENT_TEXT' => $actual['TEXT'],
			'LABEL_TEXT' => Loc::getMessage('LANDING_CMP_AGREEMENT_LABEL'),
			'IS_AGREEMENT_TEXT_HTML' => 'Y'
		]);
	}
	else if (!$row['LABEL_TEXT'])
	{
		AgreementTable::update($row['ID'], [
			'LABEL_TEXT' => Loc::getMessage('LANDING_CMP_AGREEMENT_LABEL'),
			'IS_AGREEMENT_TEXT_HTML' => 'Y'
		]);
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
			'AGREEMENT_TEXT' => $agreement['TEXT'],
			'LABEL_TEXT' => Loc::getMessage('LANDING_CMP_AGREEMENT_LABEL'),
			'IS_AGREEMENT_TEXT_HTML' => 'Y'
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
	$redirectIfUnAccept = true;
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
	$redirectIfUnAccept = false;
	$arResult['AGREEMENT_ACCEPTED'] = true;
}

// accept
if (
	$request->get('action') == 'accept_agreement' &&
	!empty($arResult['AGREEMENT']) &&
	check_bitrix_sessid()
)
{
	Consent::addByContext(
		$arResult['AGREEMENT']['ID']
	);
	LocalRedirect($uriString);
}

// if not accept and don't exist agreement
if (
	isset($redirectIfUnAccept) &&
	$redirectIfUnAccept === true
)
{
	LocalRedirect(SITE_DIR, true);
}

if (!empty($arParams['PAGE_URL_SITE_SHOW']))
{
	$url = Manager::getOption('tmp_last_show_url', '');
	if ($url !== $arParams['PAGE_URL_SITE_SHOW'])
	{
		Manager::setOption(
			'tmp_last_show_url',
			$arParams['PAGE_URL_SITE_SHOW']
		);
	}
}

$this->IncludeComponentTemplate($componentPage);