<?php

define("BX_ROOT", "/bitrix");

if (!empty($_SERVER["BX_PERSONAL_ROOT"]))
{
	define("BX_PERSONAL_ROOT", $_SERVER["BX_PERSONAL_ROOT"]);
}
else
{
	define("BX_PERSONAL_ROOT", BX_ROOT);
}
