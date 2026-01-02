<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Data\Node\Img;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type\RequestEntityDto;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Copilot\Generation\Type\RequestEntities;
use Bitrix\Landing\Copilot\Model\RequestToEntitiesTable;
use Bitrix\Landing\Metrika;
use Bitrix\Main\ORM\Query\Query;

class RequestImages extends RequestMultiple
{
	public function __construct()
	{
		parent::__construct();
		if (class_exists(self::getConnectorClass()))
		{
			$this->connector = new (self::getConnectorClass())();
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function getConnectorClass(): string
	{
		return AI\Image::class;
	}

	protected const EVENT_NAME = 'onCopilotImageCreate';

	/**
	 * @inheritdoc
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto
	{
		return new RequestQuotaDto(
			self::getConnectorClass(),
			self::getImagesQuota($siteData)
		);
	}

	public function getAnalyticEvent(): ?Metrika\Events
	{
		return Metrika\Events::imagesGeneration;
	}

	/**
	 * Get not avatar images count, check all blocks and all nodes
	 *
	 * @param Site $siteData
	 *
	 * @return int not avatar image count in all blocks
	 */
	private static function getImagesQuota(Site $siteData): int
	{
		$imageCount = 0;

		foreach ($siteData->getBlocks() as $block)
		{
			if ($block->isMenu())
			{
				continue;
			}
			foreach ($block->getNodes() as $node)
			{
				if (
					$node->getType() === NodeType::Img
					&& !$node->isAvatarNode()
				)
				{
					$placeholders = $node->getPlaceholders();
					$imageCount += count($placeholders);
				}
			}
		}

		return $imageCount;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntitiesToRequest(): array
	{
		if (!isset($this->siteData))
		{
			return [];
		}

		if (!empty($this->entities))
		{
			return $this->entities;
		}

		$filter = Query::filter()->logic('or');
		$landingId = $this->siteData->getLandingId();

		foreach ($this->siteData->getBlocks() as $block)
		{
			foreach ($block->getNodes() as $node)
			{
				if (
					$node->getType() === NodeType::Img
					&& !empty($node->getPromptTexts())
				)
				{
					if (
						!$landingId
						|| !$block->getId()
						|| !$node->getCode()
					)
					{
						continue;
					}

					foreach ($node->getPromptTexts() as $position => $prompt)
					{
						if (empty($prompt))
						{
							continue;
						}

						$key = self::createEntityKey($landingId, $block->getId(), $node->getCode(), $position);
						$entity = new RequestEntityDto(
							$landingId,
							$block->getId(),
							$node->getCode(),
							$position,
							$prompt,
						);
						$this->entities[$key] = $entity;
						$filter->where(
							Query::filter()
								->where('LANDING_ID', '=', $landingId)
								->where('BLOCK_ID', '=', $block->getId())
								->where('NODE_CODE', '=', $node->getCode())
								->where('POSITION', '=', $position)
						);
					}
				}
			}
		}

		// mark sended requests
		if (!empty($this->entities))
		{
			$generationId = $this->generation->getId();
			$res = RequestToEntitiesTable::query()
				->setSelect([
					'REQUEST_ID',
					'LANDING_ID',
					'BLOCK_ID',
					'NODE_CODE',
					'POSITION',
				])
				->where($filter)
				->where('STEP_REF.GENERATION_ID', '=', $generationId)
				->where('REQUEST_REF.DELETED', '=', 'N')
				//todo: exist request in step RequestPreviewImage
				// ->where('STEP_REF.STEP', '=', $this->stepId)
				->exec()
			;
			$rows = $res->fetchAll();

			foreach ($rows as $row)
			{
				$key = self::createEntityKey(
					(int)$row['LANDING_ID'],
					(int)$row['BLOCK_ID'],
					$row['NODE_CODE'],
					(int)$row['POSITION'],
				);
				if (isset($this->entities[$key]))
				{
					$this->entities[$key]->requestId = (int)$row['REQUEST_ID'];
				}
			}
		}

		return $this->entities;
	}

	protected static function createEntityKey(
		int $landingId,
		int $blockId,
		string $nodeCode,
		int $position,
	): string
	{
		return "l{$landingId}_b{$blockId}_n{$nodeCode}_p{$position}";
	}

	protected function sendRequests(): bool
	{
		if (!isset($this->siteData, $this->stepId))
		{
			return false;
		}

		foreach ($this->getEntitiesToRequest() as $entity)
		{
			if (
				!isset($entity->landingId)
				|| !isset($entity->blockId)
				|| !isset($entity->nodeCode)
				|| !isset($entity->position)
				|| !isset($entity->prompt)
			)
			{
				continue;
			}

			if (isset($entity->requestId))
			{
				continue;
			}

			$timer = $this->generation->getTimer();
			if (!$timer->check())
			{
				throw new \RuntimeException("The maximum execution time has been reached, step {$this->stepId} was aborted");
			}

			$prompt = new Prompt($entity->prompt);
			// todo: add size and ratio
			$prompt->setMarkers(['format' => 'square']);

			$request = new Request($this->generation->getId(), $this->stepId);
			if ($request->send($prompt, $this->connector))
			{
				$this->requests[$request->getId()] = $request;
				$entity->requestId = $request->getId();

				RequestToEntitiesTable::add([
					'REQUEST_ID' => $request->getId(),
					'ENTITY_TYPE' => RequestEntities::Image->value,
					'LANDING_ID' => $entity->landingId,
					'BLOCK_ID' => $entity->blockId,
					'NODE_CODE' => $entity->nodeCode,
					'POSITION' => $entity->position,
				]);
			}
		}

		return true;
	}

	protected function applyResponses(): bool
	{
		$changed = false;

		$landingId = $this->siteData->getLandingId();
		foreach ($this->requests as $request)
		{
			if (
				!$request->isReceived()
				|| $request->isApplied()
			)
			{
				continue;
			}

			$timer = $this->generation->getTimer();
			if (!$timer->check())
			{
				throw new \RuntimeException("The maximum execution time has been reached, step {$this->stepId} was aborted");
			}

			// todo: collect filter in foreaech and get all relations in one query
			$relation = RequestToEntitiesTable::query()
				->setSelect(['*'])
				->where('REQUEST_ID', '=', $request->getId())
				->where('LANDING_ID', '=', $landingId)
				->where('ENTITY_TYPE', '=', RequestEntities::Image->value)
				->exec()
				->fetch()
			;
			if (!$relation)
			{
				continue;
			}

			$blockId = (int)$relation['BLOCK_ID'];
			$nodeCode = $relation['NODE_CODE'];
			$position = (int)($relation['POSITION'] ?? 0);

			foreach ($this->siteData->getBlocks([$blockId]) as $blockData)
			{
				foreach ($blockData->getNodes([$nodeCode]) as $nodeData)
				{
					if (empty($request->getError()))
					{
						if ($this->applyNode($nodeData, $position, $request))
						{
							$request->setApplied();
							$changed = true;
						}
					}
					else
					{
						if ($this->fixNode($nodeData, $position))
						{
							$request->setApplied();
						}
					}
				}
			}
		}

		return $changed;
	}

	private function applyNode(Node $nodeData, int $position, Request $request): bool
	{
		if (
			$nodeData->getType() === NodeType::Img
			&& empty($nodeData->getGenderData())
		)
		{
			/**
			 * @var Img $nodeData
			 */
			$result = $request->getResult();
			$url = is_array($result) ? array_shift($result) : $result;

			if (
				$nodeData
					->setImageFromPath((string)$url, $position)
					->toLanding($position)
			)
			{
				$values = $nodeData->getValues($position);
				$this->getEvent()->send(
					self::EVENT_NAME,
					[
						'blockId' => (string)$nodeData->getParentBlock()?->getId(),
						'selector' => $nodeData->getCode(),
						'position' => $position,
						'value' => !empty($values) ? array_shift($values) : [],
						'isEditInStyle' => $nodeData->isEditInStyle(),
					],
				);

				return true;
			}
		}

		return false;
	}

	/**
	 * If node has empty request - try to get value from it node or from other blocks
	 * @param Node $node
	 * @param int $position
	 * @return bool
	 */
	private function fixNode(Node $node, int $position): bool
	{
		if (
			$node->getType() !== NodeType::Img
			|| !empty($node->getGenderData())
		)
		{
			return false;
		}

		$findInNode = function (Node $node): ?array
		{
			if (
				$node->getType() !== NodeType::Img
				|| !empty($node->getGenderData())
			)
			{
				return null;
			}

			foreach ($node->getValues() as $value)
			{
				if (isset($value['id'], $value['id2x'], $value['src'], $value['src2x']))
				{
					return $value;
				}
			}

			return null;
		};

		/**
		 * @var Img $node
		 */

		$selectedValue = $findInNode($node);

		// find value in other blocks in site
		if (
			!$selectedValue
			&& $node->getParentBlock()->getParentSite()
		)
		{
			foreach ($node->getParentBlock()->getParentSite()->getBlocks() as $block)
			{
				foreach ($block->getNodes() as $otherNode)
				{
					$selectedValue = $findInNode($otherNode);
					if ($selectedValue)
					{
						break 2;
					}
				}
			}
		}

		if (!$selectedValue)
		{
			return false;
		}

		if (
			$node
				->setValue($selectedValue, $position)
				->toLanding($position)
		)
		{
			$this->getEvent()->send(
				self::EVENT_NAME,
				[
					'blockId' => (string)$node->getParentBlock()?->getId(),
					'selector' => $node->getCode(),
					'position' => $position,
					'value' => !empty($selectedValue) ? $selectedValue : [],
					'isEditInStyle' => $node->isEditInStyle(),
				],
			);

			return true;
		}

		return false;
	}
}