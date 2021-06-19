<?php

/**
 * Form output class
 *
 */

class CFormOutput extends CAllFormOutput 
{
	public static function err_mess()
	{
		$module_id = "form";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CFormOutput<br>File: ".__FILE__;
	}	
	
	public function __construct()
	{
		parent::__construct();
	}
}
