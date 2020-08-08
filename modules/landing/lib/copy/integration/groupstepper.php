<?php
namespace Bitrix\Landing\Copy\Integration;

use Bitrix\Landing;
use Bitrix\Landing\Site\Type;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Update\Stepper;

class GroupStepper extends Stepper
{
	protected static $moduleId = 'landing';

	protected $queueName = 'LandingGroupQueue';
	protected $checkerName = 'LandingGroupChecker_';
	protected $baseName = 'LandingGroupStepper_';
	protected $errorName = 'LandingGroupError_';

	const LIMIT = 5;

	public function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		try
		{
			$queue = $this->getQueue();
			$this->setQueue($queue);
			$queueOption = $this->getOptionData($this->baseName);
			if (empty($queueOption))
			{
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}

			$executiveUserId = ($queueOption["executiveUserId"] ?: 0);

			$siteId = ($queueOption['siteId'] ?: 0);
			$copiedSiteId = ($queueOption['copiedSiteId'] ?: 0);

			Landing\Rights::setContextUserId($executiveUserId);

			Type::setScope(Type::SCOPE_CODE_GROUP);

			$copiedPageIds = $this->getPageIdsBySiteId($copiedSiteId);
			$offset = count($copiedPageIds);

			$pageIdsToCopy = $this->getPageIdsBySiteId($siteId);
			$count = count($pageIdsToCopy);
			$pageIdsToCopy = array_slice($pageIdsToCopy, $offset, self::LIMIT);

			if ($pageIdsToCopy)
			{
				$pageCopier = $this->getPageCopier($copiedSiteId);

				$containerCollection = new ContainerCollection();
				foreach ($pageIdsToCopy as $pageId)
				{
					$container = new Container($pageId);
					$containerCollection[] = $container;
				}
				$result = $pageCopier->copy($containerCollection);
				if (!$result->isSuccess())
				{
					$this->deleteQueueOption();
					return !$this->isQueueEmpty();
				}

				$this->saveCopiedMapIds($pageCopier, $queueOption, $result);

				$option['count'] = $count;
				$option['steps'] = $offset;
			}
			else
			{
				$this->onAfterCopy($queueOption);
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}

			return true;
		}
		catch (\Exception $exception)
		{
			$this->clearContextUserId();
			$this->writeToLog($exception);
			$this->deleteQueueOption();
			return false;
		}
	}

	private function getPageIdsBySiteId(int $siteId): array
	{
		$pageIds = [];
		$queryObject = Landing\Landing::getList([
			'select' => ['ID'],
			'filter' => ['SITE_ID' => $siteId],
		]);
		while ($page = $queryObject->fetch())
		{
			$pageIds[] = $page['ID'];
		}
		return $pageIds;
	}

	private function getPageCopier(int $copiedSiteId): EntityCopier
	{
		$pageCopyImplementer = new Landing\Copy\Implement\Landing();
		$pageCopyImplementer->setTargetSiteId($copiedSiteId);

		return new EntityCopier($pageCopyImplementer);
	}

	private function saveCopiedMapIds(EntityCopier $pageCopier, array $queueOption, Result $result): void
	{
		$pageMapIds = ($queueOption['pageMapIds'] ?: []);
		$blockMapIds = ($queueOption['blockMapIds'] ?: []);

		$pageMapIds = $pageCopier->getMapIdsByImplementer(
			Landing\Copy\Implement\Landing::class, $result->getData()
		) + $pageMapIds;
		$queueOption['pageMapIds'] = $pageMapIds;

		$blockMapIds = $pageCopier->getMapIdsByImplementer(
			'LandingBlocks', $result->getData()
		) + $blockMapIds;
		$queueOption['blockMapIds'] = $blockMapIds;

		$this->saveQueueOption($queueOption);
	}

	private function onAfterCopy(array $queueOption)
	{
		$siteId = ($queueOption['siteId'] ?: 0);
		$copiedSiteId = ($queueOption['copiedSiteId'] ?: 0);
		$pageMapIds = array_filter(($queueOption['pageMapIds'] ?: []));
		$blockMapIds = array_filter(($queueOption['blockMapIds'] ?: []));

		if ($pageMapIds)
		{
			$this->updateFolderIds($pageMapIds);
			$this->updateBlockIds($pageMapIds, $blockMapIds);

			$this->updateCopiedSite($siteId, $copiedSiteId, $pageMapIds);
		}

		$this->clearContextUserId();
	}

	private function clearContextUserId()
	{
		Landing\Rights::clearContextUserId();
	}

	private function updateFolderIds(array $pageMapIds): void
	{
		foreach ($pageMapIds as $pageId => $copiedPageId)
		{
			$copiedLandingInstance = Landing\Landing::createInstance($copiedPageId);
			$folderId = $copiedLandingInstance->getFolderId();
			if (array_key_exists($folderId, $pageMapIds))
			{
				Landing\Landing::update($pageId, [
					'FOLDER_ID' => $pageMapIds[$folderId],
				]);
			}
		}
	}

	private function updateBlockIds(array $pageMapIds, array $blockMapIds): void
	{
		foreach ($pageMapIds as $pageId => $copiedPageId)
		{
			$landingMapIds['#landing'.$pageId] = '#landing'.$copiedPageId;
			unset($landingMapIds[$pageId]);
		}
		foreach ($blockMapIds as $blockId => $copiedBlockId)
		{
			$blockMapIds['#block'.$blockId] = '#block'.$copiedBlockId;
			unset($blockMapIds[$blockId]);
		}

		foreach ($pageMapIds as $pageId => $copiedPageId)
		{
			$copiedLandingInstance = Landing\Landing::createInstance(mb_substr($copiedPageId, 8));
			foreach ($copiedLandingInstance->getBlocks() as $copiedBlock)
			{
				$content = $copiedBlock->getContent();
				$content = str_replace(
					array_keys($pageMapIds),
					array_values($pageMapIds),
					$content
				);
				$content = str_replace(
					array_keys($blockMapIds),
					array_values($blockMapIds),
					$content
				);
				$copiedBlock->saveContent($content);
				$copiedBlock->save();
			}
		}
	}

	private function updateCopiedSite(int $siteId, int $copiedSiteId, array $pageMapIds): void
	{
		$siteData = $this->getSiteData($siteId);
		$siteDataForUpdate = $this->getSiteDataForUpdateCopiedSite($siteData, $pageMapIds);
		if ($siteData['TPL_ID'])
		{
			$siteDataForUpdate['TPL_ID'] = $siteData['TPL_ID'];
			$this->copyTemplate($siteId, $copiedSiteId, $pageMapIds);
		}
		if ($siteDataForUpdate)
		{
			Landing\Site::update($copiedSiteId, $siteDataForUpdate);
		}
	}

	private function getSiteData(int $siteId): array
	{
		$queryObject = Landing\Site::getList(['filter' => ['ID' => $siteId]]);
		return (($siteData = $queryObject->fetch()) ? $siteData : []);
	}

	private function getSiteDataForUpdateCopiedSite(array $siteData, array $pageMapIds): array
	{
		$copiedSiteData = [];
		$codes = ['LANDING_ID_INDEX', 'LANDING_ID_404', 'LANDING_ID_503'];
		foreach ($codes as $code)
		{
			if ($siteData[$code] && isset($pageMapIds[$siteData[$code]]))
			{
				$copiedSiteData[$code] = $pageMapIds[$siteData[$code]];
			}
		}
		return $copiedSiteData;
	}

	private function copyTemplate(int $siteId, int $copiedSiteId, array $pageMapIds): void
	{
		if (($refs = Landing\TemplateRef::getForSite($siteId)))
		{
			foreach ($refs as $areaId => $oldId)
			{
				if (isset($pageMapIds[$oldId]))
				{
					$refs[$areaId] = $pageMapIds[$oldId];
				}
				else
				{
					unset($refs[$areaId]);
				}
			}
			if ($refs)
			{
				Landing\TemplateRef::setForSite($copiedSiteId, $refs);
			}
		}
	}

	protected function getQueue(): array
	{
		return $this->getOptionData($this->queueName);
	}

	protected function setQueue(array $queue): void
	{
		$queueId = (string) current($queue);
		$this->checkerName = (mb_strpos($this->checkerName, $queueId) === false ?
			$this->checkerName.$queueId : $this->checkerName);
		$this->baseName = (mb_strpos($this->baseName, $queueId) === false ?
			$this->baseName.$queueId : $this->baseName);
		$this->errorName = (mb_strpos($this->errorName, $queueId) === false ?
			$this->errorName.$queueId : $this->errorName);
	}

	protected function getQueueOption()
	{
		return $this->getOptionData($this->baseName);
	}

	protected function saveQueueOption(array $data)
	{
		Option::set(static::$moduleId, $this->baseName, serialize($data));
	}

	protected function deleteQueueOption()
	{
		$queue = $this->getQueue();
		$this->setQueue($queue);
		$this->deleteCurrentQueue($queue);
		Option::delete(static::$moduleId, ["name" => $this->checkerName]);
		Option::delete(static::$moduleId, ["name" => $this->baseName]);
	}

	protected function deleteCurrentQueue(array $queue): void
	{
		$queueId = current($queue);
		$currentPos = array_search($queueId, $queue);
		if ($currentPos !== false)
		{
			unset($queue[$currentPos]);
			Option::set(static::$moduleId, $this->queueName, serialize($queue));
		}
	}

	protected function isQueueEmpty()
	{
		$queue = $this->getOptionData($this->queueName);
		return empty($queue);
	}

	protected function getOptionData($optionName)
	{
		$option = Option::get(static::$moduleId, $optionName);
		$option = ($option !== "" ? unserialize($option) : []);
		return (is_array($option) ? $option : []);
	}

	protected function deleteOption($optionName)
	{
		Option::delete(static::$moduleId, ["name" => $optionName]);
	}
}