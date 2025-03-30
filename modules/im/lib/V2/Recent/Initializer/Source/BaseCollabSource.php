<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\Stage;
use Bitrix\Im\V2\Recent\Initializer\StageType;

abstract class BaseCollabSource extends BaseSource
{
	public function __construct(int $targetId, ?int $sourceId, Stage $stage)
	{
		parent::__construct($targetId, $sourceId, $stage);
		if ($this->stage::getType() === StageType::Target)
		{
			$this->stage->setGapTime(Stage::MIN_GAP_TIME);
		}
	}

	public function setIsFirstInit(bool $flag): static
	{
		if ($flag && $this->stage::getType() === StageType::Target)
		{
			// We set the time gap to 60 seconds here to account for the initial loading of the "recent".
			// When a guest joins a collab, their join message might be sent to the collab chat
			// before the "recent" is fully initialized. This increased gap ensures proper order.
			$this->stage->setGapTime(Stage::GAP_TIME);
		}

		return parent::setIsFirstInit($flag);
	}

	protected function isResultAffectedByStage(): bool
	{
		return true;
	}
}