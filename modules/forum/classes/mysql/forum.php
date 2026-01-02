<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage forum
 * @copyright 2001-2025 Bitrix
 */

global $REL_FPATH;
$REL_FPATH = COption::GetOptionString("forum", "REL_FPATH", "");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/classes/general/forum.php");

class CForum extends CAllForum
{

}
