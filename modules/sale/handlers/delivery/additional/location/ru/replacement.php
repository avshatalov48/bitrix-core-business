<?
namespace Sale\Handlers\Delivery\Additional\Location;

class Replacement
{
	public static function getLocalityTypes()
	{
		return array(
			'ПОСЁЛОК ГОРОДСКОГО ТИПА' => array('ПГТ'),
			'ПОСЁЛОК' => array('П', 'ПОС', 'ПОСЕЛОК'),
			'АУЛ' => array('АУЛ'),
			'СЕЛО' => array('СЕЛО', 'C'),
			'ХУТОР' => array('ХУТОР', 'Х'),
			'ДЕРЕВНЯ' => array('ДЕРЕВНЯ', 'Д', 'ДЕР'),
			'СТАНИЦА' => array('СТАНИЦА', 'СТ-ЦА', 'СТАН'),
			'СНТ' => array(),
			'ДАЧНЫЙ ПОСЁЛОК' => array(),
			'РАБОЧИЙ ПОСЁЛОК' => array(),
			'НАСЕЛЁННЫЙ ПУНКТ' => array(),
			'МИКРОРАЙОН' => array(),
			'СЛОБОДА' => array(),
			'ЖИЛРАЙОН' => array(),
			'ЖЕЛЕЗНОДОРОЖНАЯ СТАНЦИЯ' => array(),
			'ПОЧТОВОЕ ОТДЕЛЕНИЕ' => array(),
			'СЕЛЬСКОЕ ПОСЕЛЕНИЕ' => array(),
			'МЕСТЕЧКО' => array(),
			'СЕЛЬСОВЕТ' => array()
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

	public static function getRegionExceptions()
	{
		return array(
			'ЧУВАШИЯ' => 'ЧУВАШСКАЯ',
			'МОСКВА' => 'МОСКОВСКАЯ ОБЛАСТЬ',
			'САНКТ-ПЕТЕРБУРГ' => 'ЛЕНИНГРАДСКАЯ ОБЛАСТЬ',
			'УДМУРТИЯ' => 'УДМУРТСКАЯ'
		);
	}

	public static function getDistrictTypes()
	{
		return array(
			'РАЙОН' => array('Р-Н', 'Р-ОН')
		);
	}

	public static function getNameRussia()
	{
		return 'РОССИЯ';
	}

	public static function getCountryName()
	{
		return self::getNameRussia();
	}
}