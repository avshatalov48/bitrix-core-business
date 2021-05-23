<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentDescription = array(
	'NAME' => getMessage('LANDING_CMP_NAME'),
	'DESCRIPTION' => getMessage('LANDING_CMP_DESCRIPTION'),
	'SORT' => 5,
	'COMPLEX' => 'Y',
	'PATH' => array(
		'ID' => 'landing',
		'NAME' => getMessage('LANDING_CMP_NAMESPACE_NAME')
	)
);