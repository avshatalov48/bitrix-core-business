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
			'domains' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_DOMAIN'),
				'DEFAULT' => 'domains'
			),
			'roles' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_ROLES'),
				'DEFAULT' => 'rights'
			),
			'role_edit' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_ROLE_EDIT'),
				'DEFAULT' => 'right_edit'
			),
			'sites' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_SITES'),
				'DEFAULT' => 'sites'
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
			'landing_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_LANDING_EDIT'),
				'DEFAULT' => 'site/#site_show#/edit/#landing_edit#/',
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
				'VARIABLES' => array('domains')
			),
			'domain_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_DOMAIN_EDIT'),
				'DEFAULT' => 'domain/edit/#domain_edit#/',
				'VARIABLES' => array('domain_edit')
			),
			'roles' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_ROLES'),
				'DEFAULT' => 'roles/',
				'VARIABLES' => array('roles')
			),
			'role_edit' => array(
				'NAME' => getMessage('LANDING_CMP_PAR_SM_ROLE_EDIT'),
				'DEFAULT' => 'role/edit/#role_edit#/',
				'VARIABLES' => array('role_edit')
			)
		)
	)
);

if (!\Bitrix\Landing\Manager::isB24())
{
	unset($arComponentParameters['PARAMETERS']['SHOW_MENU']);
}