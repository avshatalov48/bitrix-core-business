<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	die();
}

const NO_AGENT_CHECK = true;
const STOP_STATISTICS = true;
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const DisableEventsCheck = true;

const URL_BUILDER_TYPE = 'INVENTORY';
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/admin/iblock_element_edit.php');
