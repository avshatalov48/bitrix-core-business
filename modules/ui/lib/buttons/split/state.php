<?php

namespace Bitrix\UI\Buttons\Split;

use Bitrix\UI\Buttons;

abstract class State extends Buttons\State
{
	const MAIN_HOVER    = "ui-btn-main-hover";
	const MENU_HOVER    = "ui-btn-menu-hover";
	const MAIN_ACTIVE   = "ui-btn-main-active";
	const MENU_ACTIVE   = "ui-btn-menu-active";
	const MAIN_DISABLED = "ui-btn-main-disabled";
	const MENU_DISABLED = "ui-btn-menu-disabled";
}