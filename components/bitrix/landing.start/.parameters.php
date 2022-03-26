<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (\Bitrix\Main\Loader::includeModule('landing'))
{
	$types = \Bitrix\Landing\Site::getTypes();
}
else
{
	$types = array();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'STRICT_TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_STRICT_TYPE'),
			'TYPE' => 'CHECKBOX'
		),
		'SHOW_MENU' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SHOW_MENU'),
			'TYPE' => 'CHECKBOX'
		),
		'REOPEN_LOCATION_IN_SLIDER' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_REOPEN_LOCATION_IN_SLIDER'),
			'TYPE' => 'CHECKBOX'
		),
		'TILE_LANDING_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TILE_LANDING_MODE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'edit',
			'VALUES' => [
				'edit' => getMessage('LANDING_CMP_PAR_TILE_LANDING_MODE_EDIT'),
				'view' => getMessage('LANDING_CMP_PAR_TILE_LANDING_MODE_VIEW')
			]
		),
		'TILE_SITE_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TILE_SITE_MODE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'list',
			'VALUES' => [
				'list' => getMessage('LANDING_CMP_PAR_TILE_SITE_MODE_LIST'),
				'view' => getMessage('LANDING_CMP_PAR_TILE_SITE_MODE_VIEW')
			]
		),
		'EDIT_FULL_PUBLICATION' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_EDIT_FULL_PUBLICATION'),
			'TYPE' => 'CHECKBOX'
		),
		'EDIT_PANEL_LIGHT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_EDIT_PANEL_LIGHT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'EDIT_DONT_LEAVE_FRAME' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_EDIT_DONT_LEAVE_FRAME'),
			'TYPE' => 'CHECKBOX'
		),
		'DRAFT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'VARIABLE_ALIASES' => Array(
			'page' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_PAGE'),
				'DEFAULT' => 'page'
			),
			'site_show' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_SITE'),
				'DEFAULT' => 'site_show'
			),
			'site_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_SITE_EDIT'),
				'DEFAULT' => 'site_edit'
			),
			'landing_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_LANDING_EDIT'),
				'DEFAULT' => 'landing_edit'
			),
			'landing_view' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_LANDING_VIEW'),
				'DEFAULT' => 'landing_view'
			),
			'domain_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_DOMAIN_EDIT'),
				'DEFAULT' => 'domain_edit'
			),
			'role_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_ROLE_EDIT'),
				'DEFAULT' => 'role_edit'
			),
			'folder_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_FOLDER_EDIT'),
				'DEFAULT' => 'folder_edit'
			)
		),
		'SEF_MODE' => Array(
			'sites' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITES'),
				'DEFAULT' => '',
				'VARIABLES' => array()
			),
			'site_show' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE'),
				'DEFAULT' => 'site/#site_show#/',
				'VARIABLES' => array('site_show')
			),
			'site_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_EDIT'),
				'DEFAULT' => 'site/edit/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_design' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_DESIGN'),
				'DEFAULT' => 'site/design/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_master' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_MASTER'),
				'DEFAULT' => 'site/master/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_contacts' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_CONTACTS'),
				'DEFAULT' => 'site/contacts/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_domain' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_DOMAIN'),
				'DEFAULT' => 'site/domain/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_domain_switch' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_DOMAIN_SWITCH'),
				'DEFAULT' => 'site/domain_switch/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'site_cookies' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_SITE_COOKIES'),
				'DEFAULT' => 'site/cookies/#site_edit#/',
				'VARIABLES' => array('site_edit')
			),
			'landing_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_LANDING_EDIT'),
				'DEFAULT' => 'site/#site_show#/edit/#landing_edit#/',
				'VARIABLES' => array('site_show', 'landing_edit')
			),
			'landing_design' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_LANDING_DESIGN'),
				'DEFAULT' => 'site/#site_show#/design/#landing_edit#/',
				'VARIABLES' => array('site_show', 'landing_edit')
			),
			'landing_view' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_LANDING_VIEW'),
				'DEFAULT' => 'site/#site_show#/view/#landing_edit#/',
				'VARIABLES' => array('site_show', 'landing_edit')
			),
			'domains' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_DOMAINS'),
				'DEFAULT' => 'domains/',
				'VARIABLES' => array()
			),
			'domain_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_DOMAIN_EDIT'),
				'DEFAULT' => 'domain/edit/#domain_edit#/',
				'VARIABLES' => array('domain_edit')
			),
			'roles' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_ROLES'),
				'DEFAULT' => 'roles/',
				'VARIABLES' => array()
			),
			'role_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_ROLE_EDIT'),
				'DEFAULT' => 'role/edit/#role_edit#/',
				'VARIABLES' => array('role_edit')
			),
			'folder_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_FOLDER_EDIT'),
				'DEFAULT' => 'folder/edit/#folder_edit#/',
				'VARIABLES' => array('folder_edit')
			)
		)
	)
);

if (!\Bitrix\Landing\Manager::isB24())
{
	unset($arComponentParameters['PARAMETERS']['SHOW_MENU']);
}