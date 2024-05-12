<?
#############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2013 Bitrix			#
# https://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
#############################################
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