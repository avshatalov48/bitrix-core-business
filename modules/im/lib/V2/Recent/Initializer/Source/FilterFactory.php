<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserCollaber;
use Bitrix\Im\V2\Recent\Initializer\Source;
use Bitrix\Im\V2\Recent\Initializer\Source\Filter\CollabersOnly;
use Bitrix\Im\V2\Recent\Initializer\Source\Filter\Identity;
use Bitrix\Im\V2\Recent\Initializer\Source\Filter\Nothing;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Im\V2\Recent\Initializer\StageType;

class FilterFactory
{
	private static self $instance;

	private function __construct()
	{
	}

	public static function getInstance(): static
	{
		static::$instance ??= new static();

		return static::$instance;
	}

	public function get(Source $source, int $targetId): Filter
	{
		if ($this->isSourceCollab($source::getType()))
		{
			return $this->getForCollab($source->getStage()::getType(), $targetId);
		}

		return new Identity();
	}

	private function isSourceCollab(SourceType $sourceType): bool
	{
		return $sourceType === SourceType::Collab || $sourceType === SourceType::Collabs;
	}

	private function getForCollab(StageType $stageType, int $targetId): Filter
	{
		if (!$this->isCollaber($targetId))
		{
			return match ($stageType)
			{
				StageType::Target => new Nothing(),
				StageType::Other => new CollabersOnly(),
			};
		}

		return match ($stageType)
		{
			StageType::Target => new Identity(),
			StageType::Other => new CollabersOnly(),
		};
	}

	private function isCollaber(int $targetId): bool
	{
		return User::getInstance($targetId) instanceof UserCollaber;
	}
}