<?
namespace Bitrix\Sale\Delivery\Packing;

/**
 * Class Packer
 * @package Bitrix\Sale\Delivery\Packing
 */
class Packer
{
	/**
	 * Returns Dimensions of space filled by boxes
	 * @param array $boxesSizes
	 * @return array
	 * todo: optimize
	 */
	public static function countMinContainerSize(array $boxesSizes)
	{
		if(count($boxesSizes) == 0)
			return array(0,0,0);

		if(count($boxesSizes) == 1)
			return current($boxesSizes);

		$container = new Container();

		foreach($boxesSizes as $box)
			$container->addBox($box);
			
		return $container->getFilledDimensions();
	}
}
