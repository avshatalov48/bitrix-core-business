<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Control\Command\ValueObject\Features;
use CSocNetFeatures;

class SetFeaturesHandler extends AbstractHandler
{
	public function __construct(private readonly Features $features)
	{
	}

	public function execute(AbstractConverterCommand $command): Result
	{
		$featureValues = $this->features->getValue();

		foreach ($featureValues as $featureName => $isActive)
		{
			CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$command->getGroup()->getId(),
				$featureName,
				$isActive,
				false,
				['isCollab' => true]
			);
		}

		return new Result();
	}
}