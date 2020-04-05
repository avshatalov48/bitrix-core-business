<?php

CModule::AddAutoloadClasses('sender', array(

	"bitrix\\sender\\groupconnectortable" => "lib/group.php",

	"bitrix\\sender\\mailinggrouptable" => "lib/mailing.php",
	"Bitrix\\Sender\\MailingSubscriptionTable" => "lib/mailing.php",

	"bitrix\\sender\\postingrecipienttable" => "lib/posting.php",
	"bitrix\\sender\\postingreadtable" => "lib/posting.php",
	"bitrix\\sender\\postingclicktable" => "lib/posting.php",
	"bitrix\\sender\\postingunsubtable" => "lib/posting.php",
));