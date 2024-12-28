<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Add;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetAllowed;
use CSocNetFeatures;
use CSocNetFeaturesPerms;

class AddFeatureHandler implements AddHandlerInterface
{
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$features = $command->getFeatures()->getValue();

		$activeSetFeatures = [];

		foreach ($features as $featureName => $isActive)
		{
			$featureId = CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$entity->getId(),
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

		$permissions = $command->getPermissions()->getValue()?? [];
		if (empty($permissions))
		{
			return $handlerResult;
		}

		foreach ($activeSetFeatures as $featureName => $featureId)
		{
			$operations = $permissions[$featureName];
			if (empty($operations))
			{
				continue;
			}

			foreach ($operations as $operationName => $operationValue)
			{
				$operationId = CSocNetFeaturesPerms::SetPerm(
					$featureId,
					$operationName,
					$operationValue
				);

				if (!$operationId)
				{
					$handlerResult->addApplicationError();
				}
			}
		}

		return $handlerResult;
	}
}