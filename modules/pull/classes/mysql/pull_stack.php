<?php
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/pull/classes/general/pull_stack.php");

class CPullStack extends CAllPullStack
{
	public static function CheckExpireAgent()
	{
		return "";
	}
}
