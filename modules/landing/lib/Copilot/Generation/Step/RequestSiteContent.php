<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Converter;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Markers;
use Bitrix\Landing\Copilot\Generation\PromptGenerator;
use Bitrix\Landing\Copilot\Generation\PromptTemplateProvider;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Metrika;
use Bitrix\Main\Web;
use Exception;

class RequestSiteContent extends RequestSingle
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

	/**
	 * @inheritdoc
	 */
	protected function getPrompt(): Prompt
	{
		$prompt = new Prompt('landing_ai_content');
		$prompt->setMarkers(Markers::getSiteContentPromptMarkers($this->siteData));

		return $prompt;
	}

	/**
	 * @inheritdoc
	 */
	protected function applyResponse(): bool
	{
		$result = $this->request->getResult();

		if ($result)
		{
			Converter\Json::initSiteContent($this->siteData, $result);
		}

		self::prepareImgNodesData($this->siteData);

		return true;
	}

	/**
	 * Verifies the completeness of the response data based on a predefined schema.
	 *
	 * This method checks the decoded JSON schema blocks from markers, verifying:
	 * - The block count matches the schema.
	 * - Sufficient titles exist in the menu.
	 * - The node count in each block aligns with the schema.
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

		$prompt = $this->getPrompt();
		$markers = $prompt->getMarkers();

		if (isset($markers['json_schema_blocks']))
		{
			$jsonSchemaBlocks = $markers['json_schema_blocks'];
			try
			{
				$jsonSchemaBlocksArray = Web\Json::decode($jsonSchemaBlocks);
			}
			catch (Exception)
			{
				throw new GenerationException(
					GenerationErrors::notCorrectResponse,
					"Response is not decoded.",
				);
			}
		}

		if (isset($result, $jsonSchemaBlocksArray))
		{
			//check blocks amount
			if (isset($jsonSchemaBlocksArray['blocks'], $result['blocks']))
			{
				$countBlocksInRequest = count($jsonSchemaBlocksArray['blocks']);
				$countBlocksInResult = count($result['blocks']);
				$diffCount = $countBlocksInRequest - $countBlocksInResult;
				if ($diffCount < 0 || $diffCount > 2)
				{
					throw new GenerationException(
						GenerationErrors::notFullyResponse,
						"The number of blocks does not match the expected range.",
					);
				}
			}
			else
			{
				throw new GenerationException(
					GenerationErrors::notFullyResponse,
					"Not exist blocks.",
				);
			}

			//check amount title for menu
			$filteredResultBlocks = array_filter($result['blocks'], static function ($block)
			{
				return isset($block['titleInMenu']) && $block['titleInMenu'] !== '';
			});
			$countTitlesInResult = count($filteredResultBlocks);
			if ($countTitlesInResult < 4)
			{
				throw new GenerationException(
					GenerationErrors::notFullyResponse,
					"Not enough titles in the menu.",
				);
			}

			//check amount nodes
			if (isset($diffCount) && $diffCount === 0)
			{
				$blocksCount = count($result['blocks']);
				for ($i = 0; $i < $blocksCount - 1; $i++)
				{
					$countBlockNodesInRequest = count($jsonSchemaBlocksArray['blocks'][$i]['nodes']);
					$countBlockNodesInResult = count($result['blocks'][$i]['nodes']);
					$diffCount = $countBlockNodesInRequest - $countBlockNodesInResult;
					if ($diffCount < -1 || $diffCount > 1)
					{
						throw new GenerationException(
							GenerationErrors::notFullyResponse,
							"The number of nodes in block $i does not match the expected range.",
						);
					}
				}
			}
		}
	}

	protected static function prepareImgNodesData(Site $siteData): void
	{
		foreach ($siteData->getBlocks() as $blockData)
		{
			if ($blockData->isSeparator() || $blockData->isMenu())
			{
				continue;
			}

			foreach ($blockData->getNodes() as $nodeData)
			{
				if ($nodeData->getType() !== NodeType::Img)
				{
					continue;
				}

				if (method_exists($nodeData, 'getGenderData') && !empty($nodeData->getGenderData()))
				{
					continue;
				}

				$countPromptTexts = 0;
				$promptTexts = $nodeData->getPromptTexts();
				if (is_array($promptTexts))
				{
					$countPromptTexts = count($promptTexts);
				}

				$countPlaceholders = 0;
				$placeholders = $nodeData->getPlaceholders();
				if (is_array($placeholders))
				{
					$countPlaceholders = count($placeholders);
				}

				if ($countPromptTexts !== $countPlaceholders)
				{
					$diffCount = $countPlaceholders - $countPromptTexts;
					if ($diffCount > 0)
					{
						for ($i = 0; $i < $diffCount; $i++)
						{
							$promptTexts[] = '';
						}
					}
				}

				$updatedPromptTexts = [];
				foreach ($promptTexts as $promptText)
				{
					if (is_string($promptText) && $promptText !== '')
					{
						$updatedPromptTexts[] = $promptText;
					}
					else
					{
						$promptGenerator = new PromptGenerator(new PromptTemplateProvider(), $siteData);
						$updatedPromptTexts[] = $promptGenerator->getRandomReservePromptText();
					}
				}
				$nodeData->setPromptTexts($updatedPromptTexts);
			}
		}
	}
}