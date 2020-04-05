<?php

namespace Bitrix\Main\Test\Webpacker;

use Bitrix\Main\Web\WebPacker;

/**
 * Class Simple
 *
 * @package Bitrix\Crm\UI\Webpacker
 */
class Simple
{
	/**
	 * Print script.
	 *
	 * @return void
	 */
	public static function printScript()
	{
		$content = (new WebPacker\Builder())
			->addExtension('ui.webpacker.example.simple')
			->stringify();

		echo '<script>' . ($content) .  '</script>';
	}
}