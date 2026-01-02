<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Update;

use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetFeatures;

class UpdateFeatureHandler implements UpdateHandlerInterface
{
	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$features = $command->getFeatures()?->getValue() ?? [];

		$activeSetFeatures = [];

		foreach ($features as $featureName => $isActive)
		{
			$featureId = CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$command->getId(),
				$featureName,
				$isActive,
				false,
				['isCollab' => true]
			);

			if (!$featureId)
			{
				$handlerResult->addApplicationError(['ERROR_NO_FEATURE_ID']);
			}

			if ($isActive)
			{
				$activeSetFeatures[$featureName] = $featureId;
			}
		}

		if (!empty($features))
		{
			$handlerResult->setGroupChanged();
		}

		return $handlerResult;
	}
}