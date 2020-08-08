<?
IncludeModuleLangFile(__FILE__);

global $DB;
$db_type = mb_strtolower($DB->type);
CModule::AddAutoloadClasses(
	"subscribe",
	array(
		"CRubric" => "classes/general/rubric.php",
		"CSubscription" => "classes/".$db_type."/subscription.php",
		"CPosting" => "classes/".$db_type."/posting.php",
		"CPostingTemplate" => "classes/general/template.php",
		"CMailTools" => "classes/general/posting.php",
		"subscribe" => "install/index.php",
		"Bitrix\\Subscribe\\SenderEventHandler" => "lib/senderconnector.php",
		"Bitrix\\Subscribe\\SenderConnectorSubscriber" => "lib/senderconnector.php",
	)
);
?>