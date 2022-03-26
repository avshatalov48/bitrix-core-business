<?php

namespace Bitrix\Mail;

class MailPullSchema
{
	public static function OnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "mail",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}