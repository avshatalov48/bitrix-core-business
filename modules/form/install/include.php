<?
global $DB, $MESS, $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/errors.php");

$DBType = strtolower($DB->type);

CModule::AddAutoloadClasses(
	"form",
	array(
		// compability classes
		"CForm_old" 		=> "classes/general/form_cform_old.php",
		"CFormResult_old" 	=> "classes/general/form_cformresult_old.php",
		
		// main classes
		"CAllForm" 			=> "classes/general/form_callform.php",
		"CAllFormAnswer" 	=> "classes/general/form_callformanswer.php",
		"CAllFormField" 	=> "classes/general/form_callformfield.php",
		"CAllFormOutput" 	=> "classes/general/form_callformoutput.php",
		"CAllFormResult" 	=> "classes/general/form_callformresult.php",
		"CAllFormStatus" 	=> "classes/general/form_callformstatus.php",
		
		// API classes
		"CForm" 			=> "classes/".$DBType."/form_cform.php",
		"CFormAnswer" 		=> "classes/".$DBType."/form_cformanswer.php",
		"CFormField" 		=> "classes/".$DBType."/form_cformfield.php",
		"CFormOutput" 		=> "classes/".$DBType."/form_cformoutput.php",
		"CFormResult" 		=> "classes/".$DBType."/form_cformresult.php",
		"CFormStatus" 		=> "classes/".$DBType."/form_cformstatus.php",
	)
);
?>