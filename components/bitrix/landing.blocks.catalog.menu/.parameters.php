<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$sections = array();

if (\Bitrix\Main\Loader::includeModule('landing'))
{
	if (
		\Bitrix\Main\Loader::includeModule('iblock') &&
		($params = \Bitrix\Landing\Node\Component::getIblockParams())
	)
	{
		$filter = array(
			'IBLOCK_ID' => $params['id']
		);
		$res = \CIBlockSection::getTreeList(array(
			'IBLOCK_ID' => $params['id']
		), array(
			'ID', 'NAME', 'DEPTH_LEVEL'
		));
		while ($row = $res->fetch())
		{
			$sections[$row['ID']] = (str_repeat(' . ', $row['DEPTH_LEVEL'] - 1)) . $row['NAME'];
		}
	}
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'AVAILABLE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_AVAILABLE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $sections
		)
	)
);
