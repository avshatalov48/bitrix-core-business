<?
namespace Bitrix\Sale\Delivery\Pecom;

class Replacement
{
	public static function getRegionExceptions()
	{
		return array(
			'МОСКВА' => 'МОСКОВСКАЯ ОБЛАСТЬ',
			'САНКТ-ПЕТЕРБУРГ' => 'ЛЕНИНГРАДСКАЯ ОБЛАСТЬ',
		);
	}

	public static function getDistrictMark()
	{
		return 'Р\-Н';
	}
}