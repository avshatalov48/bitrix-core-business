<?php

CModule::AddAutoloadClasses('sender', array(

		"bitrix\\sender\\contactlisttable" => "lib/contact.php",
		"bitrix\\sender\\listtable" => "lib/contact.php",

		"bitrix\\sender\\groupconnectortable" => "lib/group.php",

		"bitrix\\sender\\mailinggrouptable" => "lib/mailing.php",
		"Bitrix\\Sender\\MailingSubscriptionTable" => "lib/mailing.php",

		"bitrix\\sender\\postingrecipienttable" => "lib/posting.php",
		"bitrix\\sender\\postingreadtable" => "lib/posting.php",
		"bitrix\\sender\\postingclicktable" => "lib/posting.php",
		"bitrix\\sender\\postingunsubtable" => "lib/posting.php",
));


\CJSCore::RegisterExt("sender_admin", Array(
	"js" =>    "/bitrix/js/sender/admin.js",
	"lang" =>    "/bitrix/modules/sender/lang/" . LANGUAGE_ID . "/js_admin.php",
	"rel" =>   array()
));

CJSCore::RegisterExt('sender_stat', array(
	'js' => array(
		'/bitrix/js/main/amcharts/3.3/amcharts.js',
		'/bitrix/js/main/amcharts/3.3/serial.js',
		'/bitrix/js/main/amcharts/3.3/themes/light.js',
		'/bitrix/js/sender/heatmap/script.js',
		'/bitrix/js/sender/stat/script.js'
	),
	'css' => array(
		'/bitrix/js/sender/stat/style.css'
	),
	'rel' => array('core', 'ajax', 'date')
));