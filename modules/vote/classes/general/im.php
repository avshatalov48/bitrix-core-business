<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2025 Bitrix
 */

IncludeModuleLangFile(__FILE__);

class CVoteNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return array(
			"vote" => array(
				"voting" => Array(
					"NAME" => GetMessage('V_VOTING'),
				)
			)
		);
	}
}
?>