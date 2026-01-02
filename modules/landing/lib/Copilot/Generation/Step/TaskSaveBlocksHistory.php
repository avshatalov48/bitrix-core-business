<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\History;
use Bitrix\Landing\Rights;

class TaskSaveBlocksHistory extends TaskStep
{
	/**
	 * Save content of all blocks before scenario processing
	 *
	 * @return bool Returns true upon successful execution and configuration of blocks.
	 * @throws GenerationException
	 */
	public function execute(): bool
	{
		parent::execute();

		$contentsBefore = $this->generation->getData(TaskPresaveBlocksHistory::DATA_KEY);
		if (
			!is_array($contentsBefore)
			|| empty($contentsBefore)
		)
		{
			return true;
		}

		Rights::setGlobalOff();
		$historyState = History::isActive();
		History::activate();

		if ($this->saveHistory($contentsBefore))
		{
			$this->generation->deleteData(TaskPresaveBlocksHistory::DATA_KEY);
		}

		Rights::setGlobalOn();
		if (!$historyState)
		{
			History::deactivate();
		}

		return true;
	}

	private function saveHistory(array $contentsBefore): bool
	{
		$landing = $this->siteData->getLandingInstance();
		if (!$landing)
		{
			return false;
		}

		$history = new History($landing->getId(), History::ENTITY_TYPE_LANDING);

		foreach ($contentsBefore as $blockId => $contentBefore)
		{
			$block = $landing->getBlockById($blockId);
			if (!$block)
			{
				continue;
			}

			$history->push('UPDATE_CONTENT', [
				'block' => $blockId,
				'contentBefore' => $contentBefore,
				'contentAfter' => $block->getContent(),
			]);
		}

		return true;
	}
}