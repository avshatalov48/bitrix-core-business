<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Restriction\ToolAvailabilityManager;

$availabilityManager = ToolAvailabilityManager::getInstance();

print $availabilityManager->getInventoryManagementStubContent();
