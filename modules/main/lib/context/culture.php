<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Context;

use Bitrix\Main;

class Culture extends Main\Localization\EO_Culture
{
	public function getDateTimeFormat()
	{
		return $this->getFormatDatetime();
	}

	public function getDateFormat()
	{
		return $this->getFormatDate();
	}

	public function getNameFormat()
	{
		return $this->getFormatName();
	}
}
