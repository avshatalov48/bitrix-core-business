<?
/**
 * Form validator class
 *
 */

class CFormValidator extends CAllFormValidator 
{
	function err_mess()
	{
		$module_id = "form";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CFormValidator<br>File: ".__FILE__;
	}	
}
?>