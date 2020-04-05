<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_HTML_NAME'),
		'section' => array('other'),
		'html' => false,
	),
	'nodes' => array(
		'bitrix:landing.blocks.html' => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(
					'HTML_CODE' => array(
						'type' => 'html',
					),
				),
			),
		),
	),
);