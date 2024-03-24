<?php
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	'subscribe',
	[
		'Bitrix\\Subscribe\\SenderConnectorSubscriber' => 'lib/senderconnector.php',
		'Bitrix\\Subscribe\\SenderEventHandler' => 'lib/senderconnector.php',
		'CMailTools' => 'classes/general/mailtools.php',
		'CPosting' => 'classes/general/posting.php',
		'CPostingTemplate' => 'classes/general/template.php',
		'CRubric' => 'classes/general/rubric.php',
		'CSubscription' => 'classes/general/subscription.php',
		'subscribe' => 'install/index.php',
	]
);
