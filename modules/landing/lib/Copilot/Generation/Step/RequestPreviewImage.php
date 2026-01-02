<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Data\Block\Collector;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type;
use Bitrix\Landing\Copilot\Generation\Type\RequestEntities;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Copilot\Model\RequestToEntitiesTable;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Rights;

class RequestPreviewImage extends RequestSingle
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

	/**
	 * @inheritdoc
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto
	{
		return new RequestQuotaDto(self::getConnectorClass(), 1);
	}

	/**
	 * @inheritdoc
	 */
	public function isAsync(): bool
	{
		return true;
	}

	protected const EVENT_NAME = 'onPreviewImageCreate';

	/**
	 * @inheritdoc
	 */
	protected function getEntityToRequest(): array
	{
		if (!isset($this->siteData))
		{
			return [];
		}

		static $entities = null;
		if (isset($entities))
		{
			return $entities;
		}

		$entities = [];

		$landingId = $this->siteData->getLandingId();
		$prompt = $this->siteData->getPreviewImagePromptText();

		$entity = [];
		$key = self::createEntityKey($landingId);
		$entity[$key] = [
			'landingId' => $landingId,
			'prompt' => $prompt,
		];

		// mark sent requests
		if (!empty($entity))
		{
			$generationId = $this->generation->getId();

			$res = RequestToEntitiesTable::query()
				->setSelect([
					'REQUEST_ID',
					'LANDING_ID',
				])
				->where('LANDING_ID', '=', $landingId)
				->where('STEP_REF.GENERATION_ID', '=', $generationId)
				->where('STEP_REF.STEP', '=', $this->stepId)
				->where('REQUEST_REF.DELETED', '=', 'N')
				->exec()
			;

			while ($row = $res->fetch())
			{
				$key = self::createEntityKey($row['LANDING_ID']);
				if (isset($entity[$key]))
				{
					$entity[$key]['requestId'] = (int)$row['REQUEST_ID'];
				}
			}
		}

		return $entity;
	}

	protected static function createEntityKey(int $landingId): string
	{
		return "l{$landingId}_preview";
	}

	protected function sendRequest(): bool
	{
		if (!isset($this->siteData, $this->stepId))
		{
			return false;
		}

		$entity = $this->getEntityToRequest();
		if (!isset($entity['landingId'], $entity['prompt']))
		{
			return false;
		}

		if (isset($entity['requestId']))
		{
			return false;
		}

		$prompt = new Prompt($entity['prompt']);
		$prompt->setMarkers(['format' => 'square']);

		$request = new Request($this->generation->getId(), $this->stepId);
		if ($request->send($prompt, $this->connector))
		{
			RequestToEntitiesTable::add([
				'REQUEST_ID' => $request->getId(),
				'ENTITY_TYPE' => Type\RequestEntities::Image->value,
				'LANDING_ID' => $entity['landingId'],
			]);
		}

		return true;
	}

	protected function applyResponse(): bool
	{
		$responseApplied = false;
		$previewSrc = null;
		$isNeedSetPreviewImageSrcToNode = false;

		$this->getEvent()->send(self::EVENT_NAME);

		if (empty($this->request->getError()))
		{
			$result = $this->request->getResult();

			if (isset($result[0]))
			{
				$previewSrc = $result[0];
				$isNeedSetPreviewImageSrcToNode = true;
			}

			$responseApplied = true;
		}

		if ($responseApplied === false)
		{
			$fixedPreview = $this->fixPreview();
			if ($fixedPreview !== null)
			{
				$previewSrc = $fixedPreview;
				$responseApplied = true;
			}
		}

		if ($previewSrc && $previewSrc !== $this->siteData->getPreviewImageSrc())
		{
			$this->siteData->setPreviewImageSrc($previewSrc);
			if ($isNeedSetPreviewImageSrcToNode)
			{
				$this->setPreviewImageSrcToNode();
			}

			$landingId = $this->siteData->getLandingId();
			if ($landingId > 0)
			{
				$additionalFields = [
					'METAOG_IMAGE' => $previewSrc,
				];
				Rights::setGlobalOff();
				Landing::saveAdditionalFields($this->siteData->getLandingId(), $additionalFields);
				Rights::setGlobalOn();
			}
		}

		return $responseApplied;
	}

	private function fixPreview(): ?string
	{
		$fixedPreviewSrc = null;
		foreach ($this->siteData->getBlocks() as $block)
		{
			foreach ($block->getNodes() as $node)
			{
				$previewSrc = $this->findSrcInNode($node);
				if ($previewSrc !== null)
				{
					$fixedPreviewSrc = $previewSrc;
					break 2;
				}
			}
		}

		return $fixedPreviewSrc;
	}

	private function findSrcInNode(Node $node): ?string
	{
		if (
			$node->getType() !== NodeType::Img
			|| (method_exists($node, 'getGenderData') && !empty($node->getGenderData()))
		)
		{
			return null;
		}

		$allowedAspectRatios = ['3by2', '16by9'];
		$sizeData = null;
		if (method_exists($node, 'getSizeData'))
		{
			$sizeData = $node->getSizeData();
		}

		if ($sizeData !== null)
		{
			$position = 0;
			foreach ($node->getValues() as $value)
			{
				if (
					isset($value['src'], $value['defaultSrc'], $sizeData[$position]['aspectRatio'])
					&& $value['src'] !== $value['defaultSrc']
					&& in_array($sizeData[$position]['aspectRatio'], $allowedAspectRatios, true)
				)
				{
					return $value['src'];
				}
				$position++;
			}
		}

		return null;
	}

	protected function getPrompt(): Prompt
	{
		$prompt = new Prompt($this->siteData->getPreviewImagePromptText());
		$prompt->setMarkers(['format' => 'landscape']);

		return $prompt;
	}

	/**
	 * Set preview image src to node within the site data.
	 *
	 * @return void
	 */
	protected function setPreviewImageSrcToNode(): void
	{
		$isFindFirstNode = false;
		$imgNodesUsesPreviewImage = Collector::getImgNodesUsePreviewImage();
		foreach ($this->siteData->getBlocks() as $block)
		{
			if ($isFindFirstNode)
			{
				break;
			}
			$codeBlock = $block->getCode();
			foreach ($block->getNodes() as $node)
			{
				$codeNode = $node->getCode();
				if (
					isset($imgNodesUsesPreviewImage[$codeBlock])
					&& in_array($codeNode, $imgNodesUsesPreviewImage[$codeBlock])
					&& method_exists($node, 'setImageFromPath')
				)
				{
					//todo: add 2x
					$previewImageSrc = $this->siteData->getPreviewImageSrc();
					if ($previewImageSrc === null)
					{
						return;
					}

					$position = 0;
					$node
						->setImageFromPath($previewImageSrc, $position)
						->toLanding($position);

					$isFindFirstNode = true;
					$blockId = $block->getId();

					$requestId = $this->request->getId();
					$existingRecord = RequestToEntitiesTable::getList([
						'filter' => [
							'REQUEST_ID' => $requestId,
							'ENTITY_TYPE' => RequestEntities::Image->value,
							'BLOCK_ID' => $blockId,
							'NODE_CODE' =>$codeNode,
							'POSITION' => $position,
						],
					])->fetch();

					if (!$existingRecord)
					{
						RequestToEntitiesTable::add([
							'REQUEST_ID' => $requestId,
							'ENTITY_TYPE' => RequestEntities::Image->value,
							'LANDING_ID' => null,
							'BLOCK_ID' => $blockId,
							'NODE_CODE' => $codeNode,
							'POSITION' => 0,
						]);
					}

					//if the block has already been created
					if ($blockId > 0)
					{
						$blockInstance = $this->siteData->getLandingInstance()?->getBlockById($blockId);
						$blockNodes = $block->getNodes();
						if ($blockInstance && $blockNodes)
						{
							$nodesArray[$codeNode] = $node->getValues();
							$blockInstance->updateNodes($nodesArray);
							$blockInstance->save();
						}
					}
					break;
				}
			}
		}
	}

	public function verifyResponse(): void
	{
	}
}