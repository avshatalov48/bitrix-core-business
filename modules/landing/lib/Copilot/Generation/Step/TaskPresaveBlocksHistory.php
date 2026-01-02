<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\History;
use Bitrix\Landing\Rights;

class TaskPresaveBlocksHistory extends TaskStep
{
	public const DATA_KEY = 'blocks_contents_before';

	/**
	 * Save content of all blocks before scenario processing
	 *
	 * @return bool Returns true upon successful execution and configuration of blocks.
	 * @throws GenerationException
	 */
	public function execute(): bool
	{
		parent::execute();

		Rights::setGlobalOff();
		$historyState = History::isActive();
		History::deactivate();

		$blockContents = $this->getBlockContents();
		if (!empty($blockContents))
		{
			$this->generation->setData(self::DATA_KEY, $blockContents);
		}

		Rights::setGlobalOn();
		if ($historyState)
		{
			History::activate();
		}

		return true;
	}

	private function getBlockContents(): array
	{
		$blockContents = [];

		$landing = $this->siteData->getLandingInstance();
		if (!$landing)
		{
			return $blockContents;
		}

		foreach ($this->siteData->getBlocks() as $blockData)
		{
			$blockId = $blockData->getId();
			if (!$blockId || $blockId <= 0)
			{
				continue;
			}

			$block = $landing->getBlockById($blockId);
			if (!$block)
			{
				continue;
			}

			$blockContents[$blockId] = $block->getContent();
		}

		return $blockContents;
	}
}