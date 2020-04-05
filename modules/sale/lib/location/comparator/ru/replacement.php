<?
namespace Bitrix\Sale\Location\Comparator;

class Replacement
{
	public static function getLocalityTypes()
	{
		return array(
			'РАБОЧИЙ ПОСЁЛОК' => array(),
			'ПОСЁЛОК ГОРОДСКОГО ТИПА' => array('ПГТ'),
			'ПОСЁЛОК' => array('П', 'ПОС', 'ПОСЕЛОК'),
			'АУЛ' => array(),
			'СЕЛО' => array('C'),
			'ХУТОР' => array('Х'),
			'ДЕРЕВНЯ' => array('Д', 'ДЕР'),
			'СТАНИЦА' => array('СТ-ЦА', 'СТАН')
		);
	}

	public static function getRegionTypes()
	{
		return array(
			'ОБЛАСТЬ' => array('ОБЛ'),
			'АВТОНОМНЫЙ ОКРУГ' => array('АО', 'АВТ ОКРУГ'),
			'РЕСПУБЛИКА' => array('РЕСП')
		);
	}

	public static function getRegionVariants()
	{
		return array(
			'ЧУВАШИЯ' => 'ЧУВАШСКАЯ',
			'МОСКВА' => 'МОСКОВСКАЯ ОБЛАСТЬ',
			'САНКТ-ПЕТЕРБУРГ' => 'ЛЕНИНГРАДСКАЯ ОБЛАСТЬ',
			'УДМУРТИЯ' => 'УДМУРТСКАЯ',
			'САХА /ЯКУТИЯ/ РЕСП' => 'РЕСПУБЛИКА САХА (ЯКУТИЯ)',
			'ХАНТЫ-МАНСИЙСКИЙ АВТОНОМНЫЙ ОКРУГ - ЮГРА АО' => 'ХАНТЫ-МАНСИЙСКИЙ АВТОНОМНЫЙ ОКРУГ',
			'ЕВРЕЙСКАЯ АОБЛ' => 'ЕВРЕЙСКАЯ АВТОНОМНАЯ ОБЛАСТЬ'
		);
	}

	public static function getCountryVariants()
	{
		return array(
			'РФ' => 'РОССИЯ',
			'РОССИЙСКАЯ ФЕДЕРАЦИЯ' => 'РОССИЯ'
		);
	}

	public static function isCountryRussia($countryName)
	{
		return in_array(
			ToUpper(
				trim(
					$countryName
				)
			),
			array(
				'РФ',
				'РОССИЙСКАЯ ФЕДЕРАЦИЯ',
				'РОССИЯ'
			)
		);
	}

	public static function getDistrictTypes()
	{
		return array(
			'РАЙОН' => array('Р-Н', 'Р-ОН')
		);
	}

	public static function changeYoE($string)
	{
		return str_replace('Ё', 'Е', $string);
	}
}