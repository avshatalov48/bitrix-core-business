<?php

\Bitrix\Main\Loader::registerAutoLoadClasses("scale", array(
	"Bitrix\\Scale\\Logger" => "lib/logger.php",
	"Bitrix\\Scale\\ServerBxInfoException" => "lib/exceptions.php",
	"Bitrix\\Scale\\NeedMoreUserInfoException" => "lib/exceptions.php"
));
