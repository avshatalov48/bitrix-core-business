<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Converter;

use Bitrix\Landing\Copilot\Data\Block;
use Bitrix\Landing\Copilot\Data\Block\Collector;
use Bitrix\Landing\Copilot\Data\Block\Operator;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Generation\PromptGenerator;
use Bitrix\Landing\Copilot\Generation\PromptTemplateProvider;
use Bitrix\Main\Web;
use Exception;

/**
 * Provide data transfer between json arrays and data objects
 */
class Json
{
	/**
	 * Retrieves site data and chat data, and encodes it into a JSON string.
	 *
	 * @return string JSON encoded string of site and chat data, or false on failure.
	 */
	public static function getSiteJsonString(Site $siteData, string $requestCode): string
	{
		$wishesData = $siteData->getWishes()->toArray();
		$siteStructureJson = [];
		switch ($requestCode)
		{
			case 'siteData':
				$siteStructureJson['siteConfig'] = $wishesData;
				break;

			case 'siteContent':
				$siteStructureJson['siteConfig'] = $wishesData;
				$siteStructureJson['blocks'] = self::filterNodesByType(
					self::prepareBlocks($siteData->getBlocks()),
					[NodeType::Text->value, NodeType::Link->value, NodeType::Icon->value, NodeType::Img->value],
				);
				break;
		}

		try
		{
			$encodedString =  Web\Json::encode($siteStructureJson, JSON_UNESCAPED_UNICODE);
			if (!is_string($encodedString))
			{
				$encodedString = '';
			}
		}
		catch (Exception)
		{
			$encodedString = '';
		}

		return $encodedString;
	}

	/**
	 * Retrieves block data, and encodes it into a JSON string.
	 *
	 * @param Block[] $blocks An array of Block objects.
	 *
	 * @return string JSON encoded string of block data, or empty string on failure.
	 */
	public static function getBlockJsonString(array $blocks): string
	{
		$siteStructureJson = [];
		foreach ($blocks as $block)
		{
			$siteStructureJson['blocks'][] = self::getBlockArrayWithContent($block);
		}

		try
		{
			$encodedString =  Web\Json::encode($siteStructureJson, JSON_UNESCAPED_UNICODE);
			if (!is_string($encodedString))
			{
				$encodedString = '';
			}
		}
		catch (Exception)
		{
			$encodedString = '';
		}

		return $encodedString;
	}

	/**
	 * Prepares a node based on its type, for use in a prepared block.
	 *
	 * @param Node $node An object representing a block node.
	 *
	 * @return array|null An associative array representing the prepared node or null if not applicable.
	 */
	protected static function prepareNode(Node $node): ?array
	{
		$type = $node->getType();
		if ($type === null)
		{
			return null;
		}

		$preparedNode = [
			'code' => $node->getCode(),
			'type' => $type->value,
			'numberElements' => count($node->getPlaceholders()),
		];
		if ($node->isAvatarNode())
		{
			if ($type === NodeType::Img)
			{
				return null;
			}

			$preparedNode['isName'] = true;
		}
		if (method_exists($node, 'isEditInStyle'))
		{
			$preparedNode['editInStyle'] = $node->isEditInStyle();
		}
		if ($type === NodeType::Img)
		{
			$preparedNode['promptTexts'] = $node->getPromptTexts();
		}

		return $preparedNode;
	}

	/**
	 * Initializes site blocks using data from a JSON response.
	 *
	 * @param Site $siteData The site data object containing blocks to be initialized.
	 * @param array $json An associative array with block configuration data from the response.
	 *
	 * @return void
	 */
	public static function initSiteBlocks(Site $siteData, array $json): void
	{
		$isCorrectBlocksAmount = Operator::checkBlocksAmount($siteData, $json);
		if (!$isCorrectBlocksAmount)
		{
			return;
		}

		$blocksFromResponse = $json['blocks'];
		$blocksData = $siteData->getBlocks();
		$count = 0;
		foreach ($blocksData as $blockData)
		{
			if ($blockData->isSeparator() || $blockData->isMenu())
			{
				continue;
			}

			if (isset($blocksFromResponse[$count]))
			{
				$blockDataFromResponse = $blocksFromResponse[$count];

				if (isset($blockDataFromResponse['code']))
				{
					$codeBlockFromResponse = $blockDataFromResponse['code'];
					$codeBlockForRequest = $blockData->getCode();
					if ($codeBlockFromResponse === $codeBlockForRequest)
					{
						if (isset($blockDataFromResponse['titleInMenu']))
						{
							if (isset($previousBlockData))
							{
								if ($previousBlockData->getSection() !== 'title')
								{
									$blockData->setMenuTitle($blockDataFromResponse['titleInMenu']);
								}
							}
							else
							{
								$blockData->setMenuTitle($blockDataFromResponse['titleInMenu']);
							}
						}
						self::checkNodes($blockDataFromResponse, $blockData);
					}
				}
			}

			$count++;
			$previousBlockData = $blockData;
		}
	}

	public static function initSiteContent(Site $siteData, array $json): void
	{
		self::initSiteBlocks($siteData, $json);
		self::saveImgNodesDataFromResponse($siteData, $json);
	}

	/**
	 * Initializes site blocks using data from a JSON response.
	 *
	 * @param Site $siteData The site data object containing blocks to be initialized.
	 * @param array $json An associative array with block configuration data from the response.
	 *
	 * @return void
	 */
	public static function initSiteBlock(Site $siteData, array $json): void
	{
		$blocksFromResponse = $json['blocks'];
		$blocksData = $siteData->getBlocks();
		$count = 0;
		foreach ($blocksData as $blockData)
		{
			if (isset($blocksFromResponse[$count]))
			{
				$blockDataFromResponse = $blocksFromResponse[$count];

				self::checkNodes($blockDataFromResponse, $blockData);

				if (isset($blockDataFromResponse['styles']))
				{
					$blockData->setStyles($blockDataFromResponse['styles']);
				}

				$count++;
			}
		}
	}

	/**
	 * Initializes image nodes for a site using response data.
	 *
	 * @param Site $siteData The site data object containing blocks to be initialized.
	 * @param array $result An associative array with image node response data to be applied to the site.
	 *
	 * @return void
	 */
	public static function saveImgNodesDataFromResponse(Site $siteData, array $result): void
	{
		if (
			!is_array($result['blocks'])
			|| empty($result['blocks'])
		)
		{
			return;
		}

		$blocksFromResponse = $result['blocks'];
		$count = 0;
		foreach ($siteData->getBlocks() as $blockData)
		{
			if ($blockData->isSeparator() || $blockData->isMenu())
			{
				continue;
			}

			$blockId = $blockData->getId();
			$blockResponse = null;
			if ($blockId === 0 && isset($blocksFromResponse[$count]))
			{
				$blockResponse = $blocksFromResponse[$count];
			}

			if ($blockId > 0 )
			{
				foreach ($blocksFromResponse as $blockFromResponse)
				{
					if ($blockFromResponse['id'] === $blockId)
					{
						$blockResponse = $blockFromResponse;
						break;
					}
				}
			}

			if (
				!is_array($blockResponse)
				|| !isset($blockResponse['nodes'])
				|| !is_array($blockResponse['nodes'])
			)
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

				$promptTexts = [];
				foreach ($blockResponse['nodes'] as $code => $nodeResponse)
				{
					if ($code !== $nodeData->getCode())
					{
						continue;
					}

					$promptTexts = $nodeResponse;
				}

				if (
					!empty($promptTexts)
					&& (count($nodeData->getPlaceholders()) === count($promptTexts))
				)
				{
					if (Operator::isNeedCheckDataFromResponse())
					{
						$promptTexts = Operator::prepareDataFromResponse($promptTexts);
					}
					$promptGenerator = new PromptGenerator(new PromptTemplateProvider(), $siteData, $nodeData);
					$promptTexts = $promptGenerator->getUpdatedPromptTexts((array)$promptTexts);
					$nodeData->addPromptTexts($promptTexts);
				}
			}

			$count++;
		}
	}

	/**
	 * Compresses a JSON string by replacing long class names with shorter aliases.
	 *
	 * @param string $json The JSON string containing class names to be compressed.
	 *
	 * @return string The JSON string with compressed class names.
	 */
	public static function compressJsonString(string $json): string
	{
		$replacements = [
			".landing-block-node-" => ".lbn-",
			".landing-block-card-" => ".lbc-",
			".landing-block-" => ".lb-",
			"-linkcontact-" => "-lc-",
		];

		return str_replace(array_keys($replacements), array_values($replacements), $json);
	}

	/**
	 * Expands a compressed JSON string by replacing short class name aliases with their full names.
	 *
	 * @param string $json The compressed JSON string containing abbreviated class names.
	 *
	 * @return string The JSON string with expanded class names.
	 */
	public static function expandJsonString(string $json): string
	{
		$replacements = [
			".lbn-" => ".landing-block-node-",
			".lbc-" => ".landing-block-card-",
			".lb-" => ".landing-block-",
			"-lc-" => "-linkcontact-",
		];

		return str_replace(array_keys($replacements), array_values($replacements), $json);
	}

	/**
	 * Generates a JSON skeleton string for site data responses with placeholder values.
	 *
	 * @return string A JSON-encoded skeleton string for site data with placeholders, or an empty string on error.
	 */
	public static function getSkeletonForSiteDataResponse(): string
	{
		$siteData = [
			"isAllowedRequest" => '',
			"titles" => [
				"site" => "",
				"page" => "",
			],
			"description" => [
				"page" => "",
			],
			"keywords" => [
				"page" => "",
			],
			"images" => [
				"siteTopicEng" => "",
				"styleEng" => "",
				"imgPromptEng" => "",
			],
			"fonts" => [
				"headers" => ["name" => ""],
				"texts" => ["name" => ""],
			],
			"colors" => [
				"theme" => ["hex" => "", "name" => ""],
				"background" => ["hex" => "", "name" => ""],
				"headerContrastOnBackground" => ["hex" => "", "name" => ""],
				"textContrastOnBackground" => ["hex" => "", "name" => ""],
				"headerContrastOnTheme" => ["hex" => "", "name" => ""],
				"textContrastOnTheme" => ["hex" => "", "name" => ""],
			],
		];

		$jsonSkeletonForResponse = [
			'siteData' => $siteData,
		];

		try
		{
			$encodedJsonSkeletonString = Web\Json::encode($jsonSkeletonForResponse, JSON_UNESCAPED_UNICODE);
			if (!is_string($encodedJsonSkeletonString))
			{
				$encodedJsonSkeletonString = '';
			}
		}
		catch (Exception)
		{
			$encodedJsonSkeletonString = '';
		}

		return $encodedJsonSkeletonString;
	}

	/**
	 * Generates a JSON skeleton string for block content responses with placeholder values.
	 *
	 * @param string $jsonDataForRequest The JSON string encoding block data to be used for skeleton creation.
	 *
	 * @return string The JSON-encoded skeleton string with placeholders, or an empty string on error.
	 */
	public static function getSkeletonForBlocksContentResponse(string $jsonDataForRequest): string
	{
		try
		{
			$jsonDataForRequestDecoded = Web\Json::decode($jsonDataForRequest);
		}
		catch (Exception)
		{
			return '';
		}

		$blocksSkeleton = [];
		$previousBlockSection = null;
		foreach ($jsonDataForRequestDecoded['blocks'] as $block)
		{
			$blockSkeleton = [];
			$blockSkeleton['code'] = $block['code'];
			if ($previousBlockSection !== 'title')
			{
				$blockSkeleton['titleInMenu'] = '';
			}
			$blockNodes = $block['nodes'];
			foreach ($blockNodes as $blockNode)
			{
				$nodeCode = $blockNode['code'];
				$nodeElements = $blockNode['numberElements'];
				if (isset($blockNode['isName']) && $blockNode['isName'] === true)
				{
					$blockSkeleton['nodes'][$nodeCode] = array_fill(0, $nodeElements, ['name' => "", 'gender' => ""]);
				}
				else
				{
					$blockSkeleton['nodes'][$nodeCode] = array_fill(0, $nodeElements, "");
				}
			}
			$previousBlockSection = $block['section'];
			$blocksSkeleton[] = $blockSkeleton;
		}

		$jsonSkeletonForResponse = [
			'blocks' => $blocksSkeleton,
		];

		try
		{
			$encodedJsonSkeletonString = Web\Json::encode($jsonSkeletonForResponse, JSON_UNESCAPED_UNICODE);
			if (!is_string($encodedJsonSkeletonString))
			{
				$encodedJsonSkeletonString = '';
			}
		}
		catch (Exception)
		{
			$encodedJsonSkeletonString = '';
		}

		return $encodedJsonSkeletonString;
	}

	//todo: add phpdoc
	public static function getSkeletonForBlockContentResponse(string $jsonDataForRequest): string
	{
		try
		{
			$jsonDataForRequestDecoded = Web\Json::decode($jsonDataForRequest);
		}
		catch (Exception)
		{
			return '';
		}

		$blocksSkeleton = [];
		foreach ($jsonDataForRequestDecoded['blocks'] as $block)
		{
			$blockSkeleton = [];
			$blockSkeleton['id'] = $block['id'];
			$blockSkeleton['code'] = $block['code'];
			$blockNodes = $block['nodes'];
			foreach ($blockNodes as $blockNode)
			{
				$nodeCode = $blockNode['code'];
				if ($blockNode['type'] === 'img')
				{
					$nodeElements = $blockNode['numberElements'];
				}
				else
				{
					$nodeElements = count($blockNode['values']);
				}
				$blockSkeleton['nodes'][$nodeCode] = array_fill(0, $nodeElements, "");
			}
			//styles
			$blockSkeleton['styles'] = [
				'background' => '',
				'textsColor' => '',
				'headersColor' => '',
				'textsFontName' => '',
				'headersFontName' => '',
			];
			$blocksSkeleton[] = $blockSkeleton;
		}

		$jsonSkeletonForResponse = [
			'blocks' => $blocksSkeleton,
			"isAllowedRequest" => '',
		];

		try
		{
			$encodedJsonSkeletonString = Web\Json::encode($jsonSkeletonForResponse, JSON_UNESCAPED_UNICODE);
			if (!is_string($encodedJsonSkeletonString))
			{
				$encodedJsonSkeletonString = '';
			}
		}
		catch (Exception)
		{
			$encodedJsonSkeletonString = '';
		}

		return $encodedJsonSkeletonString;
	}

	/**
	 * Filters nodes within blocks based on specified node types.
	 *
	 * @param array $blocks An array of blocks, each containing nodes to be filtered.
	 * @param array $filterTypes An array of node types to retain in the filtered blocks.
	 *
	 * @return array The array of blocks containing only nodes of the specified types.
	 */
	private static function filterNodesByType(array $blocks, array $filterTypes): array
	{
		$blocksWithFilteredNodes = [];
		foreach ($blocks as $block)
		{
			$preparedBlockNodes = [];
			foreach ($block['nodes'] as $node)
			{
				if (in_array($node['type'], $filterTypes, true))
				{
					$preparedBlockNodes[] = $node;
				}
			}
			$block['nodes'] = $preparedBlockNodes;
			$blocksWithFilteredNodes[] = $block;
		}

		return $blocksWithFilteredNodes;
	}

	/**
	 * Prepares block data for JSON encoding by filtering and processing blocks.
	 *
	 * @param array $blocksDataArray Array of block data to prepare.
	 *
	 * @return array Prepared block data, ready for JSON encoding.
	 */
	private static function prepareBlocks(array $blocksDataArray): array
	{
		$filteredBlocks = self::filterBlocks($blocksDataArray);

		return self::processFilteredBlocks($filteredBlocks);
	}

	/**
	 * Filters out unnecessary blocks, removing those in the "separators" section.
	 *
	 * @param array $blocksDataArray Array of block data to filter.
	 *
	 * @return array Filtered blocks, excluding unwanted sections.
	 */
	private static function filterBlocks(array $blocksDataArray): array
	{
		$filteredBlocks = [];
		foreach ($blocksDataArray as $blockData)
		{
			if (!$blockData->isSeparator() && !$blockData->isMenu())
			{
				$filteredBlocks[] = [
					'code' => $blockData->getCode(),
					'section' => $blockData->getSection(),
					'bgType' => $blockData->getBgType(),
					'nodes' => $blockData->getNodes(),
					'id' => $blockData->getId(),
				];
			}
		}

		return $filteredBlocks;
	}

	/**
	 * Processes the filtered blocks to prepare them for JSON encoding.
	 *
	 * @param array $filteredBlocks Array of filtered block data.
	 *
	 * @return array Array of processed, prepared block data.
	 */
	private static function processFilteredBlocks(array $filteredBlocks): array
	{
		$preparedBlocksList = [];
		foreach ($filteredBlocks as $block)
		{
			$preparedBlock = self::prepareBlock($block);
			$preparedBlocksList[] = $preparedBlock;
		}

		return $preparedBlocksList;
	}

	/**
	 * Prepares a single block by setting its code, section, and processing its nodes.
	 *
	 * @param array $block An associative array representing a single block.
	 *
	 * @return array Prepared block containing its processed nodes.
	 */
	private static function prepareBlock(array $block): array
	{
		$preparedBlock = [
			'id' => $block['id'],
			'code' => $block['code'],
			'section' => $block['section'],
		];

		$preparedBlock['nodes'] = [];
		foreach ($block['nodes'] as $node)
		{
			$preparedNode = self::prepareNode($node);
			if ($preparedNode !== null)
			{
				$preparedBlock['nodes'][] = $preparedNode;
			}
		}

		return $preparedBlock;
	}

	/**
	 * Checks the nodes within a block against response data to ensure data consistency.
	 *
	 * @param array $blockDataFromResponse An associative array of nodes data from the response for the given block.
	 * @param Block $blockData The block data object containing nodes to be validated.
	 *
	 * @return void
	 */
	private static function checkNodes(array $blockDataFromResponse, Block $blockData): void
	{
		$avatarImgNodeCode = Collector::getAvatarNodes($blockData->getCode())['imgNodeCode'] ?? null;
		$nodes = $blockData->getNodes();
		foreach ($nodes as $node)
		{
			if ($node->getType() === NodeType::Img)
			{
				continue;
			}
			$nodeCode = $node->getCode();
			$nodeDataFromResponse = $blockDataFromResponse['nodes'][$nodeCode] ?? null;

			if (is_array($nodeDataFromResponse) && Operator::isNeedCheckDataFromResponse())
			{
				$nodeDataFromResponse = Operator::prepareDataFromResponse($nodeDataFromResponse);
			}
			$values = [];
			$gendersData = null;
			foreach ($nodeDataFromResponse as $nodeDataFromResponseItem)
			{
				if (is_string($nodeDataFromResponseItem))
				{
					$values[] = $nodeDataFromResponseItem;
				}
				if (is_array($nodeDataFromResponseItem))
				{
					if (isset($nodeDataFromResponseItem['name']))
					{
						$values[] = $nodeDataFromResponseItem['name'];
					}
					if (isset($nodeDataFromResponseItem['gender']))
					{
						$gendersData[] = $nodeDataFromResponseItem['gender'];
					}
				}
			}
			$node->setData($values);
			if ($gendersData && method_exists($node, 'setGenderData'))
			{
				$node->setGenderData($gendersData);
				if ($avatarImgNodeCode)
				{
					foreach ($nodes as $nodeData)
					{
						if (
							$nodeData->getCode() === $avatarImgNodeCode
							&& $nodeData->isAvatarNode()
							&& method_exists($nodeData, 'setGenderData')
							&& method_exists($nodeData, 'setSrc')
							&& method_exists($nodeData, 'setDefaultSrc')
						)
						{
							$nodeData->setGenderData($gendersData);
							$nodeData->setDefaultSrc(Operator::getDefaultAvatarLinks($gendersData));
							$nodeData->setSrc(Operator::getAvatarLinks($gendersData));
							break;
						}
					}
				}
			}
		}
	}

	//todo: add phpdoc and typing
	private static function getBlockArrayWithContent($block): array
	{
		$preparedBlock = [
			'id' => $block->getId(),
			'code' => $block->getCode(),
		];
		$blockNodes = $block->getNodes();
		$preparedBlock['nodes'] = [];
		foreach ($blockNodes as $node)
		{
			$type = $node->getType()->value;
			$nodeData = [
				'type' => $type,
				'code' => $node->getCode(),
			];
			if ($type === 'text')
			{
				$nodeData['values'] = $node->getValues();
			}
			if ($type === 'icon')
			{
				$classLists = [];
				$values = $node->getValues();
				foreach ($values as $value)
				{
					$classListArray = [];
					$classList = $value['classList'];
					if (is_string($classList))
					{
						$classListArray = explode(' ', $classList);
						$classListArray = array_filter($classListArray, static function($value) {
							return $value !== null && str_starts_with($value, 'fa');
						});
						$classListArray = implode(' ', $classListArray);
					}
					$classLists[] = $classListArray;
				}
				$nodeData['values'] = $classLists;
			}
			if ($type === 'link')
			{
				$texts = [];
				$values = $node->getValues();
				foreach ($values as $value)
				{
					$texts[] = $value['text'];
				}
				$nodeData['values'] = $texts;
			}
			if ($type === 'img')
			{
				$nodeData['numberElements'] = count($node->getPlaceholders());
			}
			$preparedBlock['nodes'][] = $nodeData;
		}

		return $preparedBlock;
	}

	/**
	 * Filters blocks by removing nodes with non-empty 'promptTexts' and clears 'promptTexts' from filtered nodes.
	 *
	 * @param array $blocksWithImgNodes An array of blocks, each containing a 'nodes' key with an array of nodes.
	 * @return array The filtered array of blocks, each with only nodes that had empty 'promptTexts'.
	 */
	protected static function filterNodesByEmptyPromptTexts(array $blocksWithImgNodes): array
	{
		foreach ($blocksWithImgNodes as $key => $block)
		{
			if (isset($block['nodes']))
			{
				$filteredNodes = array_filter($block['nodes'], static function($node) {
					return empty($node['promptTexts']);
				});
				if (count($filteredNodes) > 0)
				{
					foreach ($filteredNodes as $nodeKey => $filteredNode)
					{
						unset($filteredNode['promptTexts']);
						$filteredNodeWithoutPromptTextKey = $filteredNode;
						$filteredNodes[$nodeKey] = $filteredNodeWithoutPromptTextKey;
					}
					$blocksWithImgNodes[$key]['nodes'] = $filteredNodes;
				}
				else
				{
					unset($blocksWithImgNodes[$key]);
				}
			}
		}

		return $blocksWithImgNodes;
	}
}