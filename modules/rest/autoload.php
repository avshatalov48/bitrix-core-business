<?php

CModule::AddAutoloadClasses(
	"rest",
	array(
		"CRestServer" => "classes/general/rest.php",
		"CRestUtil" => "classes/general/rest_util.php",
		"CRestEvent" => "classes/general/rest_event.php",
		"CRestEventCallback" => "classes/general/rest_event.php",
		"CRestEventSession" => "classes/general/rest_event.php",
		"IRestService" => "classes/general/rest.php",
		"CRestProvider" => "classes/general/rest_provider.php",
		"CBitrixRestEntity" => "classes/general/restentity.php",
		"CRestServerBatchItem" => "classes/general/rest.php",
		"rest" => "install/index.php",
	)
);