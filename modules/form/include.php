<?

global $DB, $MESS, $APPLICATION;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/errors.php");

define('FORM_CRM_DEFAULT_PATH', '/crm/configs/import/lead.php');

$DBType = mb_strtolower($DB->type);

CModule::AddAutoloadClasses(
	"form",
	array(
		// compability classes
		"CForm_old" => "classes/general/form_cform_old.php",
		"CFormResult_old" => "classes/general/form_cformresult_old.php",
		"CFormOutput_old" => "classes/general/form_cformoutput_old.php",

		// main classes
		"CAllForm" => "classes/general/form_callform.php",
		"CAllFormAnswer" => "classes/general/form_callformanswer.php",
		"CAllFormField" => "classes/general/form_callformfield.php",
		"CAllFormOutput" => "classes/general/form_callformoutput.php",
		"CAllFormResult" => "classes/general/form_callformresult.php",
		"CAllFormStatus" => "classes/general/form_callformstatus.php",
		"CAllFormValidator" => "classes/general/form_callformvalidator.php",
		"CAllFormCrm" => "classes/general/form_callformcrm.php",

		// API classes
		"CForm" => "classes/mysql/form_cform.php",
		"CFormAnswer" => "classes/mysql/form_cformanswer.php",
		"CFormField" => "classes/mysql/form_cformfield.php",
		"CFormOutput" => "classes/mysql/form_cformoutput.php",
		"CFormResult" => "classes/mysql/form_cformresult.php",
		"CFormStatus" => "classes/mysql/form_cformstatus.php",
		"CFormValidator" => "classes/mysql/form_cformvalidator.php",

		"CFormCrm" => "classes/mysql/form_cformcrm.php",
		"CFormCrmSender" => "classes/mysql/form_cformcrm.php",

		// event handlers
		"CFormEventHandlers" => "events.php",
		"Bitrix\\Form\\SenderEventHandler" => "lib/senderconnector.php",
		"Bitrix\\Form\\SenderConnectorForm" => "lib/senderconnector.php",
	)
);

// set event handlers
AddEventHandler('form', 'onAfterResultAdd', array('CFormEventHandlers', 'sendOnAfterResultStatusChange'));
AddEventHandler('form', 'onAfterResultStatusChange', array('CFormEventHandlers', 'sendOnAfterResultStatusChange'));

// append core form field validators
$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/validators";
$handle = opendir($path);
if ($handle)
{
	while(($filename = readdir($handle)) !== false)
	{
		if($filename == "." || $filename == "..")
			continue;

		if (!is_dir($path."/".$filename) && mb_substr($filename, 0, 4) == "val_")
		{
			require_once($path."/".$filename);
		}
	}
}