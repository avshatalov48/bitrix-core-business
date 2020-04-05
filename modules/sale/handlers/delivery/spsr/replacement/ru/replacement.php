<?

namespace Sale\Handlers\Delivery\Spsr;

class Replacement
{
	public static function getVariants()
	{
		return array(
			'REGION' => array(
				'Ханты-Мансийский авт. округ-Югра' => 'ХАНТЫ-МАНСИЙСКИЙ АВТОНОМНЫЙ ОКРУГ',
				'Еврейская авт. обл.' => 'ЕВРЕЙСКАЯ АВТОНОМНАЯ ОБЛАСТЬ',
				'Чечня респ.' => 'ЧЕЧЕНСКАЯ РЕСПУБЛИКА'
			)
		);
	}
}