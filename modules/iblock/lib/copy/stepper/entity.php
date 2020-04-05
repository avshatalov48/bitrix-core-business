<?php
namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Iblock\Copy\Implement\Element as ElementImplementer;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Update\Stepper;

abstract class Entity extends Stepper
{
	protected static $moduleId = "iblock";

	protected function getContainerCollection($elementIds, array $sectionsRatio, array $enumRatio, $targetIblockId = 0)
	{
		$containerCollection = new ContainerCollection();

		foreach ($elementIds as $elementId)
		{
			$container = new Container($elementId);
			$dictionary = new Dictionary(
				[
					"targetIblockId" => $targetIblockId,
					"enumRatio" => $enumRatio,
					"sectionsRatio" => $sectionsRatio
				]
			);
			$container->setDictionary($dictionary);

			$containerCollection[] = $container;
		}

		return $containerCollection;
	}

	protected function getElementCopier()
	{
		$elementImplementer = new ElementImplementer();
		return new EntityCopier($elementImplementer);
	}
}