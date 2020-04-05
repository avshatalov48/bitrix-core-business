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
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
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
			'sites' => Array(
				'NAME' => getMessage('LANDING_CMP_PAR_VAR_SITES'),
				'DEFAULT' => 'sites'
			),
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
			)
		)
	)
);
