<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\PromptGenerator;
use Bitrix\Landing\Copilot\Generation\PromptTemplateProvider;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Data\Block\Collector;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Model\RequestToEntitiesTable;
use Bitrix\Landing\Landing;
use Bitrix\Landing\PublicAction\Block;
use Bitrix\Landing\Site;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Metrika;

class TaskCreateBlocks extends TaskStep
{
	/**
	 * Default padding applied to the block.
	 *
	 * @var string
	 */
	private const DEFAULT_PADDING = '50';

	/**
	 * Padding applied at the bottom of the title.
	 *
	 * @var string
	 */
	private const TITLE_PADDING_BOTTOM = '15';

	/**
	 * Padding applied at the top of the block following the title.
	 *
	 * @var string
	 */
	private const BLOCK_AFTER_TITLE_PADDING_TOP = '15';

	private const EVENT_NAME = 'onBlocksCreated';

	/**
	 * The landing instance containing the block.
	 *
	 * @var Landing
	 */
	protected Landing $landingInstance;

	/**
	 * The landing id containing the block.
	 *
	 * @var int
	 */
	protected int $landingId;

	/**
	 * Executes the main task of the current process.
	 *
	 * @return bool Returns true upon successful execution and configuration of blocks.
	 */
	public function execute(): bool
	{
		parent::execute();

		Rights::setGlobalOff();
		$this->landingInstance = $this->siteData->getLandingInstance();
		$this->landingId = $this->landingInstance->getId();
		$this->addBlocks();

		$metrika = new Metrika\Metrika(
			Metrika\Categories::Site,
			Metrika\Events::createTemplate,
		);
		$siteId = $this->siteData->getSiteId() ?? 0;
		$metrika->setType(Metrika\Types::ai)
			->setParam(3, 'siteId', (string)$siteId)
			->send()
		;

		$metrikaParams = new Metrika\FieldsDto(
			subSection: 'from_ai',
			element: 'auto',
		);
		Site::publication($this->landingInstance->getSiteId(), true, $metrikaParams);
		Rights::setGlobalOn();

		$this->updateRequestEntityInTable();

		$this->sendMessage();

		return true;
	}

	/**
	 * Determines if the process is finished based on block IDs.
	 *
	 * @return bool True if all blocks have non-zero IDs, false otherwise.
	 */
	public function isFinished(): bool
	{
		foreach ($this->siteData->getBlocks() as $block)
		{
			if ($block->getId() === 0)
			{
				throw new GenerationException(
					GenerationErrors::dataValidation,
					"Block not exist correct id.",
				);
			}
		}

		return true;
	}

	/**
	 * Send a message to the chat that the site has been created.
	 *
	 * @return void
	 */
	protected function sendMessage(): void
	{
		$this->generation->getScenario()?->getChatbot()?->sendGenerationEndMessage(
			new Connector\Chat\ChatBotMessageDto($this->generation->getChatId(), $this->generation->getId())
		);
	}

	/**
	 * Add blocks for a given landing instance.
	 *
	 * @return void
	 */
	protected function addBlocks(): void
	{
		foreach ($this->siteData->getBlocks() as $block)
		{
			$this->addBlock($block);
		}
		$this->configureMenuBlock();

		$this->getEvent()->send(self::EVENT_NAME);
	}

	/**
	 * Configures the menu block by updating its link node with menu data.
	 *
	 * This method retrieves the menu block from site data, uses the Collector class to get menu link nodes,
	 * and updates them if a valid block is found.
	 */
	protected function configureMenuBlock(): void
	{
		$menuBlock = $this->siteData->getMenuBlock();

		if (!$menuBlock)
		{
			return;
		}

		$blockInstance = $this->landingInstance->getBlockById($menuBlock->getId());

		if (!$blockInstance)
		{
			return;
		}

		$linkItemsNodeCode = Collector::getMenuItemsNodeCode($menuBlock->getCode());
		$linkCardsNodeCode = Collector::getMenuCardsNodeCode($menuBlock->getCode());
		$blockInstance->updateCards($this->getMenuCardsData($linkItemsNodeCode, $linkCardsNodeCode));
		$blockInstance->save();
	}

	/**
	 * Add a block within a landing instance.
	 *
	 * @param Data\Block $blockData Data for configuring the block.
	 *
	 * @return void
	 */
	private function addBlock(Data\Block $blockData): void
	{
		$blockCode = $blockData->getCode();

		if ($blockCode)
		{
			$blockId = $this->landingInstance->addBlock($blockCode, [
				'SORT' => 0,
				'PUBLIC' => 'N',
			]);
			$blockInstance = $this->landingInstance->getBlockById($blockId);
			$blockData->setId($blockId);

			$blockNodes = $blockData->getNodes();
			if ($blockInstance)
			{
				if ($blockNodes)
				{
					$nodesArray = [];
					foreach ($blockNodes as $blockNode)
					{
						$code = $blockNode->getCode();
						$nodesArray[$code] = $blockNode->getValues();
					}
					$blockInstance->updateNodes($nodesArray);
					$blockInstance->save();
				}

				$wrapperSelectorClasses = explode(" ", Data\Block\Operator::extractWrapperClasses($blockInstance->getContent()));
				$blockData->setWrapperClasses($wrapperSelectorClasses);

				$this->updateBlockStyles($blockInstance, $blockData);
			}
		}
	}

	/**
	 * Constructs menu cards data for the given node and card codes.
	 *
	 * @param string $nodeCode The node code for menu links.
	 * @param string $cardCode The card code for menu cards.
	 *
	 * @return array The structured menu data ready to be applied to blocks.
	 */
	private function getMenuCardsData(string $nodeCode, string $cardCode): array
	{
		$blocks = $this->siteData->getBlocks();
		$menuData = [];
		$count = 0;
		foreach ($blocks as $block)
		{
			$blockMenuTitle = $block->getMenuTitle();
			if ($blockMenuTitle)
			{
				$menuData['source'][] = [
					'value' => (string)$count,
					'type' => 'card',
				];
				$codeKey = sprintf('%s@%d', $nodeCode, $count);
				$menuDataItem = [
					$codeKey => [
						'text' => $blockMenuTitle,
						'href' => sprintf('block:#block%d', $block->getId()),
						'target' => '_self',
					],
				];
				$menuData['values'][] = $menuDataItem;
				$count++;
			}
		}

		return [$cardCode => $menuData];
	}

	/**
	 * Updates the styles of a block within a landing instance.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData Data for updating the block styles.
	 *
	 * @return void
	 */
	private function updateBlockStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$this->updateStylesForBlockWithSpecificSection($blockInstance, $blockData);

		//background styles
		$this->updateBackgroundColorForWrapperNode($blockInstance, $blockData);
		$this->updateImageNodesEditingInStyle($blockInstance, $blockData);

		//color styles for nodes in block with 'primary' bg type
		$this->updateColorStylesForNodesAtBlockWithPrimaryBgType($blockInstance, $blockData);

		//by properties
		$this->updatePaddingStyles($blockInstance, $blockData);
		$this->updateTitleStyles($blockInstance, $blockData);
	}

	/**
	 * Updates the title styles for a given block within a landing page.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance containing the title nodes to update.
	 * @param Data\Block $blockData The data object providing context and code for the block.
	 *
	 * @return void
	 */
	private function updateTitleStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$titleNodes = Collector::getTitleNodes($blockData->getCode());
		if (count($titleNodes) > 0)
		{
			$selector = $titleNodes[0];
			$classList = [];
			if (
				preg_match(
					'/class="([^"]*' . substr($selector, 1) . '[^"]*)"/',
					$blockInstance->getContent(),
					$matches
				)
			)
			{
				$classList = explode(" ", $matches[1]);
			}

			$filteredClassList = array_filter($classList, static function($className) {
				return !str_starts_with($className, 'u-heading-v2-');
			});
			$filteredClassList = array_values($filteredClassList);
			$filteredClassList[] = $this->siteData->getTitleStyleClass();

			$styles = [
				$selector => [
					'classList' => $filteredClassList,
				],
			];

			Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
		}
	}

	/**
	 * Update the styles for specific block section.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData The data for styles.
	 *
	 * @return void
	 */
	private function updateStylesForBlockWithSpecificSection(
		\Bitrix\Landing\Block $blockInstance,
		Data\Block $blockData
	): void
	{
		if ($blockData->isSeparator())
		{
			$this->updateSeparatorStyles($blockInstance, $blockData);
		}
		elseif ($blockData->isMenu())
		{
			$this->updateMenuStyles($blockInstance, $blockData);
		}
	}

	/**
	 * Update the styles based on background types.
	 *
	 * This method applies specific background styles if the background type 'primary' or 'transparent'.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData The data for styles.
	 *
	 * @return void
	 */
	private function updateBackgroundColorForWrapperNode(
		\Bitrix\Landing\Block $blockInstance,
		Data\Block $blockData
	): void
	{
		$bgType = $blockData->getBgType();
		if ($bgType === 'primary' || $bgType === 'transparent')
		{
			$wrapperSelector = '#block' . $blockInstance->getId();
			$this->updateBackgroundColorStyles($blockInstance, $blockData, $wrapperSelector);
		}
	}

	/**
	 * Updates image nodes in block , which editing in style.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData The data for styles, containing nodes.
	 *
	 * @return void
	 */
	private function updateImageNodesEditingInStyle(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		foreach ($blockData->getNodes() as $node)
		{
			if (
				$node->getType() === Data\Type\NodeType::Img
				&& method_exists($node, 'isEditInStyle')
				&& $node->isEditInStyle()
			)
			{
				$values = $node->getValues();
				if (!empty($values))
				{
					$firstValue = array_shift($values);
					if (isset($firstValue['src']))
					{
						$this->updateBackgroundImageStyles(
							$blockInstance,
							$blockData,
							$node->getCode(),
							$firstValue['src']
						);
					}
				}
			}
		}
	}

	/**
	 * Updates the styles for separator blocks.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData Data for updating the block styles.
	 *
	 * @return void
	 */
	private function updateSeparatorStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$selector = '#block' . $blockInstance->getId();
		$selectorClasses = $blockData->getWrapperClasses();
		$bgType = $blockData->getBgType();
		$firstColor = '';
		$secondColor = '';
		if ($bgType === 'primaryToTransparent')
		{
			$firstColor = 'var(--primary)';
			$secondColor = 'hsla(0, 0%, 100%, 0)';
		}
		if ($bgType === 'transparentToPrimary')
		{
			$firstColor = 'hsla(0, 0%, 100%, 0)';
			$secondColor = 'var(--primary)';
		}

		$separatorsHeight = Collector::getSeparatorsHeight();
		$code = $blockData->getCode();
		if (isset($separatorsHeight[$code]['heightClass']))
		{
			$filteredClasses = array_filter($selectorClasses, static function($class) {
				return !preg_match('/g-height-\d+--md/', $class);
			});
			$selectorClasses = array_values($filteredClasses);
			$selectorClasses[] = $separatorsHeight[$code]['heightClass'];
		}

		$styles = [
			$selector => [
				'style' => [
					'--fill-first' => $firstColor,
					'--fill-second' => $secondColor,
				],
				'classList' => $selectorClasses,
			],
		];
		$blockData->setWrapperClasses($selectorClasses);

		Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
	}

	/**
	 * Updates the background image styles for block image nodes editing in style.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData Data for updating the block styles.
	 * @param string $selector The selector.
	 * @param string $imageBackgroundLink Default image link
	 *
	 * @return void
	 */
	private function updateBackgroundImageStyles(
		\Bitrix\Landing\Block $blockInstance,
		Data\Block $blockData,
		string $selector,
		string $imageBackgroundLink = ''
	): void
	{
		$isWrapper = false;
		$preparedSelectorClasses = [];

		if (
			str_starts_with($selector, '.')
			&& in_array(substr($selector, 1), $blockData->getWrapperClasses(), true)
		)
		{
			$isWrapper = true;
		}
		if ($isWrapper)
		{
			$selector = '#block' . $blockInstance->getId();
			$selectorClasses = $blockData->getWrapperClasses();
		}
		else
		{
			$blockContent = $blockInstance->getContent();
			$selectorClasses = explode(' ', Data\Block\Operator::extractNodeClasses($selector, $blockContent));
		}
		$selectorClasses[] = 'g-bg-image';
		$url = 'url("' . $imageBackgroundLink . '")';
		$wrapperStyle = [
			'background-image' => '',
			'--bg-url' => $url,
			'--bg-url-2x' => $url,
			'--bg-overlay' => 'hsla(0, 0%, 0%, 0.5)',
			'--bg-size' => 'cover',
			'--bg-attachment' => 'scroll',
		];
		foreach ($selectorClasses as $class)
		{
			if (
				$class === 'g-bg-image'
				|| (!str_starts_with($class, 'g-bg') && !str_starts_with($class, 'g-theme-'))
			)
			{
				$preparedSelectorClasses[] = $class;
			}
		}

		$styles = [
			$selector => [
				'style' => $wrapperStyle,
				'classList' => $preparedSelectorClasses,
			],
		];
		if ($isWrapper)
		{
			$blockData->setWrapperClasses($preparedSelectorClasses);
		}

		Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
	}

	/**
	 * Updates the background color styles for blocks.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance to update.
	 * @param Data\Block $blockData Data for updating the block styles.
	 * @param string $selector The selector.
	 *
	 * @return void
	 */
	private function updateBackgroundColorStyles(
		\Bitrix\Landing\Block $blockInstance,
		Data\Block $blockData,
		string $selector,
	): void
	{
		$preparedSelectorClasses = [];
		$selectorClasses = $blockData->getWrapperClasses();
		$selectorClasses[] = 'g-bg';
		$bgType = $blockData->getBgType();
		$bgStyle = '';
		if ($bgType === 'transparent')
		{
			$bgStyle = 'hsla(0, 0%, 100%, 0)';
		}
		if ($bgType === 'primary')
		{
			$bgStyle = 'var(--primary)';
		}
		$wrapperStyle = [
			'--bg' => $bgStyle,
		];
		foreach ($selectorClasses as $class)
		{
			if (
				$class === 'g-bg'
				|| (!str_starts_with($class, 'g-bg-') && !str_starts_with($class, 'g-theme-'))
			)
			{
				$preparedSelectorClasses[] = $class;
			}
		}

		$styles = [
			$selector => [
				'style' => $wrapperStyle,
				'classList' => $preparedSelectorClasses,
			],
		];

		$blockData->setWrapperClasses($preparedSelectorClasses);

		Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
	}

	/**
	 * Updates the padding styles for a given block within a landing page.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance whose padding styles are to be updated.
	 * @param Data\Block $blockData The data object that includes section and background type information of the block.
	 *
	 * @return void
	 */
	private function updatePaddingStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$selectorClasses = $blockData->getWrapperClasses();
		$bgType = $blockData->getBgType();
		if (
			$bgType !== 'image'
			&& !$blockData->isMenu()
			&& !$blockData->isSeparator()
			&& !$blockData->isCover()
		)
		{
			$filteredClasses = array_filter($selectorClasses, static function($class) {
				return !preg_match('/g-(pb|pt)-\d+/', $class);
			});
			$selectorClasses = array_values($filteredClasses);

			$paddingTopClass = 'g-pt-' . self::DEFAULT_PADDING;
			$paddingBottomClass = 'g-pb-' . self::DEFAULT_PADDING;
			if ($blockData->isTitle())
			{
				$paddingBottomClass = 'g-pb-' . self::TITLE_PADDING_BOTTOM;
			}
			else
			{
				$blocks = $this->siteData->getBlocks();
				foreach ($blocks as $block)
				{
					if ($block === $blockData)
					{
						break;
					}
					$previousBlock = $block;
				}

				if (isset($previousBlock) && $previousBlock->getSection() === 'title')
				{
					$paddingTopClass = 'g-pt-' . self::BLOCK_AFTER_TITLE_PADDING_TOP;
				}
			}
			$selectorClasses[] = $paddingTopClass;
			$selectorClasses[] = $paddingBottomClass;

			$selector = '#block' . $blockInstance->getId();
			$styles = [
				$selector => [
					'classList' => $selectorClasses,
				],
			];
			$blockData->setWrapperClasses($selectorClasses);

			Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
		}
	}

	/**
	 * Updates the menu styles for a block within a landing page based on its background type.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance whose navbar styles are to be updated.
	 * @param Data\Block $blockData The data object providing background type information for the block.
	 *
	 * @return void
	 */
	private function updateMenuStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$blockContent = $blockInstance->getContent();
		$classList = [];
		if (preg_match('/class="([^"]*navbar [^"]*)"/', $blockContent, $matches))
		{
			$classList = explode(" ", $matches[1]);
		}
		if (count($classList) > 0)
		{
			$style = [];
			$bgType = $blockData->getBgType();
			$colors = $this->siteData->getColors();
			switch ($bgType)
			{
				case 'transparent':
					$classList[] = 'u-navbar-color';
					$style['--navbar-color'] = 'var(--theme-color-title)';
					break;
				case 'primary':
					$classList[] = 'u-navbar-color';
					$classList[] = 'u-navbar-color--hover';
					$style['--navbar-color'] = $colors->headersTheme;
					$style['--navbar-color--hover'] = $colors->textsTheme;
					break;
			}
			$styles = [
				'.navbar' => [
					'style' => $style,
					'classList' => $classList,
				],
			];

			Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
		}
	}

	/**
	 * Updates the styles of a block with a primary background on a page.
	 *
	 * @param \Bitrix\Landing\Block $blockInstance The block instance whose styles are being updated.
	 * @param Data\Block $blockData The data object containing code and style information for the block.
	 *
	 * @return void
	 */
	private function updateColorStylesForNodesAtBlockWithPrimaryBgType(
		\Bitrix\Landing\Block $blockInstance,
		Data\Block $blockData
	): void
	{
		if ($blockData->getBgType() !== 'primary')
		{
			return;
		}

		$blockWithPrimaryBg = Collector::getStylesDataForBlockWithPrimaryBg($blockData->getCode());
		if (!$blockWithPrimaryBg)
		{
			return;
		}

		$styles = [];
		$colors = $this->siteData->getColors();
		$blockContent = $blockInstance->getContent();
		foreach ($blockWithPrimaryBg as $styleNodeName => $styleNodeValues)
		{
			$properties = $styleNodeValues['property'];
			$styleValues = $styleNodeValues['styleValue'];
			$count = 0;
			foreach ($styleValues as $styleValue)
			{
				if ($styleValue === 'colorsHeadersTheme')
				{
					$value = $colors->headersTheme;
				}
				elseif ($styleValue === 'colorsTextsTheme')
				{
					$value = $colors->textsTheme;
				}
				else
				{
					$value = $styleValue;
				}
				$styles[$styleNodeName]['style'][$properties[$count]] = $value;
				$styles[$styleNodeName]['classList'][] = Data\Block\Operator::extractNodeClasses($styleNodeName, $blockContent);
				$count++;
			}
		}

		Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
	}

	private function updateRequestEntityInTable(): void
	{
		$blockId = 0;
		$imgNodesUsesPreviewImage = Collector::getImgNodesUsePreviewImage();
		foreach ($this->siteData->getBlocks() as $block)
		{
			$codeBlock = $block->getCode();
			foreach ($block->getNodes() as $node)
			{
				$codeNode = $node->getCode();
				if (
					isset($imgNodesUsesPreviewImage[$codeBlock])
					&& in_array($codeNode, $imgNodesUsesPreviewImage[$codeBlock])
				)
				{
					$blockId = $block->getId();

					$promptGenerator = new PromptGenerator(new PromptTemplateProvider(), $this->siteData);
					$promptTexts = $promptGenerator->getUpdatedPromptTexts((array)$this->siteData->getPreviewImagePromptText());
					$node->setPromptTexts($promptTexts);

					break 2;
				}
			}
		}

		$requestIds = [];
		foreach($this->generation->getScenario()?->getMap() as $stepId => $step)
		{
			if ($stepId >= $this->stepId)
			{
				break;
			}

			$requests = Request::getByGeneration($this->generation->getId(), $stepId);
			foreach ($requests as $request)
			{
				$requestIds[] = $request->getId();
			}
		}

		if (!empty($requestIds))
		{
			$items = RequestToEntitiesTable::getList([
				'select' => ['ID'],
				'filter' => [
					'REQUEST_ID' => $requestIds,
					'LANDING_ID' => false,
				],
			]);

			while ($item = $items->fetch())
			{
				RequestToEntitiesTable::update(
					$item['ID'],
					[
						'LANDING_ID' => $this->siteData->getLandingId(),
						'BLOCK_ID' => $blockId,
					],
				);
			}
		}
	}
}