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
		$countries = GetCountries();
		$defaultCountry = Main\PhoneNumber\Parser::getDefaultCountry();
		usort($countries, function($a, $b) use ($defaultCountry)
		{
			return  ($a['CODE'] === $defaultCountry) ? -1 : $a['NAME'] <=> $b['NAME'];
		});
		return $countries;
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