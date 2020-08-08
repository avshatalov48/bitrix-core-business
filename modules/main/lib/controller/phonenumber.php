<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Controller;

use Bitrix\Main;

class PhoneNumber extends Main\Engine\Controller
{
	public function getCountriesAction()
	{
		return GetCountries();
	}

	public function configureActions()
	{
		return [
			'getCountries' => [
				'-prefilters' => [
					Main\Engine\ActionFilter\Authentication::class,
				],
			],
		];
	}
}