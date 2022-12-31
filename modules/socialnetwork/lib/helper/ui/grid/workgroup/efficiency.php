<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

class Efficiency
{
	public static function getEfficiencyValue(int $value = 0): string
	{
		return '<div class="sonet-ui-grid-percent">' . $value . '%</div>';
	}
}
