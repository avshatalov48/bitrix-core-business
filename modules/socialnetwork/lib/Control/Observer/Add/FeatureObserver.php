<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Observer\Add;

use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Observer\ObserverInterface;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetAllowed;
use CSocNetFeatures;

class FeatureObserver implements ObserverInterface
{
	public function update(AbstractCommand $command, Workgroup $entity): void
	{
		if (!$command instanceof AddCommand)
		{
			return;
		}

		$allowedFeatures = $this->command->features ?? array_keys(CSocNetAllowed::getAllowedFeatures());
		$inactiveFeaturesList = ['forum', 'photo', 'search', 'group_lists', 'wiki'];
		$inactiveFeaturesList[] = 'files'; // tmp!!!

		$features = [];
		foreach ($allowedFeatures as $featureName)
		{
			$features[$featureName] = !in_array($featureName, $inactiveFeaturesList, true);
		}

		foreach ($features as $featureName => $isActive)
		{
			CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$entity->getId(),
				$featureName,
				$isActive,
			);
		}
	}
}