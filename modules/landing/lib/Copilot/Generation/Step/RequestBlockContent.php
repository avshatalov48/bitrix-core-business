<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Converter;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Generation\Error;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Markers;
use Bitrix\Landing\Copilot\Generation\Type\Errors;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Metrika;
use Bitrix\Landing;
use Bitrix\Landing\Rights;

class RequestBlockContent extends RequestSingle
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
		return AI\Text::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto
	{
		return new RequestQuotaDto(self::getConnectorClass(), 1);
	}

	public function getAnalyticEvent(): ?Metrika\Events
	{
		return Metrika\Events::textsGeneration;
	}

	protected function getPrompt(): Prompt
	{
		$prompt = new Prompt('landing_ai_block_content');
		$prompt->setMarkers(Markers::getBlockContentPromptMarkers($this->siteData));

		return $prompt;
	}

	protected function applyResponse(): bool
	{
		$result = $this->request->getResult();

		if ($result)
		{
			$result = $this->prepareResult($result);

			Converter\Json::initSiteBlock($this->siteData, $result);
			Converter\Json::saveImgNodesDataFromResponse($this->siteData, $result);
			self::setDefaultSrcForImgNodes($this->siteData);
		}

		return true;
	}

	/**
	 * Verifies the completeness of the response data from a request.
	 *
	 * @return void
	 */
	public function verifyResponse(): void
	{
		$result = $this->request->getResult();

		if (!$result)
		{
			throw new GenerationException(GenerationErrors::notExistResponse);
		}

		if (
			!isset($result['isAllowedRequest'])
			|| $result['isAllowedRequest'] !== "yes"
		)
		{
			$this->request->saveError(Error::createError(Errors::requestNotAllowed));

			throw new GenerationException(GenerationErrors::restrictedRequest);
		}
	}

	/**
	 * Processes the result array to filter out empty elements from
	 * blocks and nodes, ensuring only non-empty elements remain.
	 *
	 * @param array $result The result array containing 'blocks' which is an array
	 * of blocks, each having 'nodes'.
	 *
	 * @return array The filtered result array with empty elements removed from blocks and nodes.
	 */
	protected function prepareResult(array $result): array
	{
		if (!isset($result['blocks']))
		{
			return [];
		}

		foreach ($result['blocks'] as &$block)
		{
			if (!isset($block['nodes']))
			{
				continue;
			}

			foreach ($block['nodes'] as &$node)
			{
				if (is_array($node))
				{
					$node = array_filter($node, static function($value) {
						return $value !== '';
					});
				}
				else
				{
					$node = [];
				}
			}
			unset($node);

			$block['nodes'] = array_filter($block['nodes'], static function($node) {
				return !empty($node);
			});
		}
		unset($block);

		return $result;
	}

	/**
	 * Sets the default src for image nodes within the blocks of a site.
	 *
	 * @param Site $siteData The site data object containing the blocks and nodes to be processed.
	 *
	 * @return void
	 */
	protected static function setDefaultSrcForImgNodes(Site $siteData): void
	{
		Rights::setGlobalOff();
		Landing\Landing::setEditMode();
		$landingInstance = Landing\Landing::createInstance($siteData->getLandingId());
		foreach ($siteData->getBlocks() as $blockData)
		{
			$blockId = $blockData->getId();
			$blockInstance = $landingInstance->getBlockById($blockId);
			if ($blockInstance)
			{
				$blockContent = $blockInstance->getContent();
			}

			foreach ($blockData->getNodes() as $nodeData)
			{
				if ($nodeData->getType() !== NodeType::Img)
				{
					continue;
				}

				$promptTexts = $nodeData->getPromptTexts();
				if (isset($blockContent) && $promptTexts)
				{
					if (
						method_exists($nodeData, 'setSrc')
						&& method_exists($nodeData, 'setDefaultSrc')
					)
					{
						$defaultSrcDataPrepared = [];
						$nodeDataValues = $nodeData->getValues();
						foreach ($nodeDataValues as $nodeDataValue)
						{
							$defaultSrcDataPrepared[] = [
								'src' => $nodeDataValue['defaultSrc'],
								'src2x' => $nodeDataValue['defaultSrc2x']
							];
						}
						$nodeData->setSrc($defaultSrcDataPrepared);
					}
				}
			}
		}
		Rights::setGlobalOn();
	}
}