<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Block;

use Bitrix\Landing\Block;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Type\NodeType;

/**
 * Class Collector
 *
 * Responsible for generating a set of blocks for a site.
 */
class Collector
{
	public const SECTIONS = [
		'MENU' => 'menu',
		'COVER' => 'cover',
		'TITLE' => 'title',
		'TEXT_IMAGE' => 'textImage',
		'TARIFFS' => 'tariffs',
		'TEAM' => 'team',
		'FEEDBACK' => 'feedback',
		'FORM' => 'crmForm',
		'SEPARATORS' => 'separators',
	];

	private const BG_TYPES = [
		'TRANSPARENT' => 'transparent',
		'PRIMARY' => 'primary',
		'IMAGE' => 'image',
	];

	private const BG_SEPARATOR_TYPES = [
		'TYPE_1' => 'transparentToPrimary',
		'TYPE_2' => 'primaryToTransparent',
	];

	private const JUST_ONCE_SECTIONS = [
		self::SECTIONS['TARIFFS'],
		self::SECTIONS['TEAM'],
	];

	private const SEPARATOR_CREATION_PROBABILITY = 70;

	/**
	 * @var Data\Block[] random blocks list (without nodes data)
	 */
	private array $blocks = [];

	/**
	 * @var array sections, added in list
	 */
	private array $addedSections = [];

	/**
	 * Generates a set of blocks for a site.
	 *
	 * Initializes the blocks array and adds sections in a specific order.
	 * Some sections are added conditionally based on random chance.
	 * Ensures inclusion of menu, cover, and form sections.
	 * Randomly selects additional sections from predefined groups for variety.
	 * Applies background types and adds separators between sections.
	 *
	 * @return Data\Block[] Array of blocks data.
	 */
	public function getBlocks(): array
	{
		if (!empty($this->blocks))
		{
			return $this->blocks;
		}

		$this->addRandomBlock(self::SECTIONS['MENU']);
		$this->addRandomBlock(self::SECTIONS['COVER']);
		$this->addRandomBlock(self::SECTIONS['TEXT_IMAGE']);
		$this->addRandomBlock(self::SECTIONS['TEXT_IMAGE']);
		$this->addRandomBlock($this->getRandomFromSections([
			self::SECTIONS['TARIFFS'],
			self::SECTIONS['TEXT_IMAGE'],
			self::SECTIONS['TEAM'],
		]));
		$this->addRandomBlock($this->getRandomFromSections([
			self::SECTIONS['TEXT_IMAGE'],
			self::SECTIONS['TARIFFS'],
			self::SECTIONS['FEEDBACK'],
			self::SECTIONS['TEAM'],
		]));
		$this->addRandomBlock(self::SECTIONS['FORM']);

		$this->finalize();

		return $this->blocks;
	}

	public static function getSeparatorsHeight(): array
	{
		return [
			'26.2.separator' => [
				'heightClass' => 'g-height-70--md',
			],
			'26.3.separator' => [
				'heightClass' => 'g-height-70--md',
			],
			'26.4.separator' => [
				'heightClass' => 'g-height-70--md',
			],
			'26.5.separator' => [
				'heightClass' => 'g-height-70--md',
			],
		];
	}

	/**
	 * Retrieves style data for a specific block with a primary background.
	 *
	 * @param string $codeBlock Identifier for the block.
	 *
	 * @return array|null Returns an array of styles if found, or null otherwise.
	 */
	public static function getStylesDataForBlockWithPrimaryBg(string $codeBlock): ?array
	{
		$sharedStyles = [
			'title' => [
				'property' => ['--color'],
				'styleValue' => ['colorsHeadersTheme'],
			],
			'text' => [
				'property' => ['--color'],
				'styleValue' => ['colorsTextsTheme'],
			],
			'border' => [
				'property' => ['--border-color'],
				'styleValue' => ['colorsTextsTheme'],
			],
		];

		$blocksStylesData = [
			'04.7.one_col_fix_with_title_and_text_2' => [
				'.landing-block-node-inner' => $sharedStyles['border'],
				'.landing-block-node-subtitle' => $sharedStyles['text'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'04.1.one_col_fix_with_title' => [
				'.landing-block-node-inner' => $sharedStyles['border'],
				'.landing-block-node-subtitle' => $sharedStyles['text'],
				'.landing-block-node-title' => $sharedStyles['title'],
			],
			'08.4.fix_title_and_text' => [
				'.landing-block-node-title' => [
					'property' => ['--color', '--border-color'],
					'styleValue' => ['colorsHeadersTheme', 'colorsTextsTheme'],
				],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'27.one_col_fix_title_and_text_2' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'27.3.one_col_fix_title' => [
				'.landing-block-node-title' => $sharedStyles['title'],
			],
			'20.3.four_cols_fix_img_title_text' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'20.2.three_cols_fix_img_title_text' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'31.4.two_cols_img_text_fix' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'31.3.two_cols_text_img_fix' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'19.2.features_with_img' => [
				'.landing-block-node-card-icon-border' => $sharedStyles['title'],
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
				'.landing-block-node-card-title' => $sharedStyles['title'],
				'.landing-block-node-card-text' => $sharedStyles['text'],
			],
			'19.2.features_with_img_right' => [
				'.landing-block-node-card-icon-border' => $sharedStyles['title'],
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
				'.landing-block-node-card-title' => $sharedStyles['title'],
				'.landing-block-node-card-text' => $sharedStyles['text'],
			],
			'09.1.two_cols_fix_text_and_image_slider_2' => [
				'.landing-block-node-header' => $sharedStyles['border'],
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'09.1.two_cols_fix_text_and_image_slider' => [
				'.landing-block-node-header' => $sharedStyles['border'],
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'18.two_cols_fix_img_text_button' => [
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
				'.landing-block-node-button' => [
					'property' => ['--color', '--color-hover', '--button-color'],
					'styleValue' => ['colorsTextsTheme', 'var(--primary)', 'colorsTextsTheme'],
				],
			],
			'19.4.features_with_img' => [
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'19.4.features_with_img_left' => [
				'.landing-block-node-subtitle' => $sharedStyles['title'],
				'.landing-block-node-title' => $sharedStyles['title'],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'11.2.three_cols_fix_tariffs_with_img' => [
				'.landing-block-node-title' => [
					'property' => ['--color', '--border-color'],
					'styleValue' => ['colorsHeadersTheme', 'colorsHeadersTheme'],
				],
				'.landing-block-node-text' => $sharedStyles['text'],
			],
			'28.personal_slider_2' => [
				'.landing-block-node-person-name' => $sharedStyles['title'],
				'.landing-block-node-person-post' => $sharedStyles['title'],
				'.landing-block-node-person-text' => $sharedStyles['text'],
				'.landing-block-node-person-link' => $sharedStyles['text'],
			],
			'44.6.two_columns_with_peoples' => [
				'.landing-block-node-card-name' => $sharedStyles['title'],
				'.landing-block-node-card-post' => $sharedStyles['title'],
				'.landing-block-node-card-text' => $sharedStyles['text'],
			],
		];

		return $blocksStylesData[$codeBlock] ?? null;
	}

	public static function getMenuItemsNodeCode($code): ?string
	{
		$menuBlocksData = [
			'0.menu_14_music' => '.landing-block-node-menu-list-item-link',
			'0.menu_19_travel' => '.landing-block-node-menu-list-item-link',
			'0.menu_05' => '.landing-block-node-menu-list-item-link',
		];

		return $menuBlocksData[$code] ?? null;
	}

	public static function getMenuCardsNodeCode($code): ?string
	{
		$menuBlocksData = [
			'0.menu_14_music' => '.landing-block-node-menu-list-item',
			'0.menu_19_travel' => '.landing-block-node-menu-list-item',
			'0.menu_05' => '.landing-block-node-menu-list-item',
		];

		return $menuBlocksData[$code] ?? null;
	}

	public static function getAvatarNodes($codeBlock): array
	{
		$blocksWithAvatarNodes = [
			'20.4.text_3cols_with_bottom_information' => [
				'imgNodeCode' => '.landing-block-node-author-img',
				'textNodeCode' => '.landing-block-node-author',
			],
			'23.big_carousel_blocks' => [
				'imgNodeCode' => '.landing-block-node-img',
				'textNodeCode' => '.landing-block-node-title',
			],
			'28.personal_slider_2' => [
				'imgNodeCode' => '.landing-block-node-person-photo',
				'textNodeCode' => '.landing-block-node-person-name',
			],
			'28.2.team' => [
				'imgNodeCode' => '.landing-block-node-employee-photo',
				'textNodeCode' => '.landing-block-node-employee-name',
			],
			'43.3.cover_with_feedback' => [
				'imgNodeCode' => '.landing-block-node-card-photo',
				'textNodeCode' => '.landing-block-node-card-name',
			],
			'43.5.cover_with_feedback_2' => [
				'imgNodeCode' => '.landing-block-node-card-photo',
				'textNodeCode' => '.landing-block-node-card-name',
			],
			'44.6.two_columns_with_peoples' => [
				'imgNodeCode' => '.landing-block-node-card-photo',
				'textNodeCode' => '.landing-block-node-card-name',
			],
		];

		return $blocksWithAvatarNodes[$codeBlock] ?? [];
	}

	public static function getTitleNodes($codeBlock): ?array
	{
		$blocksTitlesNodes = [
			'04.1.one_col_fix_with_title' => [
				'.landing-block-node-inner',
			],
			'04.7.one_col_fix_with_title_and_text_2' => [
				'.landing-block-node-inner',
			],
			'08.4.fix_title_and_text' => [
				'.landing-block-node-title',
			],
			'09.1.two_cols_fix_text_and_image_slider' => [
				'.landing-block-node-header',
			],
			'09.1.two_cols_fix_text_and_image_slider_2' => [
				'.landing-block-node-header',
			],
			'11.2.three_cols_fix_tariffs_with_img' => [
				'.landing-block-node-title',
			],
			'27.one_col_fix_title_and_text_2' => [
				'.landing-block-node-title',
			],
			'27.3.one_col_fix_title' => [
				'.landing-block-node-title',
			],
			'28.2.team' => [
				'.landing-block-node-title',
			],
		];

		return $blocksTitlesNodes[$codeBlock] ?? [];
	}

	public static function getImgNodesUsePreviewImage(): array
	{
		return [
			'01.big_with_text_3' => [
				'.landing-block-node-img',
			],
			'01.big_with_text_3_1' => [
				'.landing-block-node-img',
			],
			'41.3.announcement_with_slider' => [
				'.landing-block-node-bgimg',
			],
		];
	}

	private function finalize(): void
	{
		$this->pasteTitleBlocks();
		$this->applyBgTypeToNextBlock();
		$this->pasteSeparatorBlocks();
	}

	/**
	 * Adds a block to the blocks array.
	 *
	 * @param string $section The section of the block.
	 *
	 * @return void
	 */
	private function addRandomBlock(string $section): void
	{
		$allowedBlocks = $this->getAllowedBlocks();

		if (!$this->isValidSection($section, $allowedBlocks))
		{
			return;
		}

		$code = $this->getRandomElement(array_keys($allowedBlocks[$section]));
		$bgType = $this->getRandomElement($allowedBlocks[$section][$code]['bgType']);

		if (!$code || !$bgType)
		{
			return;
		}

		$block = self::createBlock($code, $section, $bgType);

		$this->blocks[] = $block;
		$this->addedSections[] = $section;
	}

	private function isValidSection(string $section, array $allowedBlocks): bool
	{
		if (!isset($allowedBlocks[$section]))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get a random variant from an array.
	 *
	 * @param array $variants
	 *
	 * @return ?string
	 */
	private function getRandomElement(array $variants): ?string
	{
		if (empty($variants))
		{
			return null;
		}

		return $variants[array_rand($variants)];
	}

	private static function createBlock(string $code, string $section, string $bgType): Data\Block
	{
		$block = new Data\Block($code, $section, $bgType);
		self::createNodesInBlock($block);

		return $block;
	}

	private static function createNodesInBlock(Data\Block $block): void
	{
		$blockCode = $block->getCode();
		$manifest = Block::getManifestFile($blockCode);
		if (!isset($manifest['nodes']))
		{
			return;
		}

		$nodes = $manifest['nodes'];
		$blockContent = Block::getContentFromRepository($blockCode);

		foreach ($nodes as $code => $node)
		{
			$nodeCode = ltrim($code, '.');
			$countElements = substr_count($blockContent, $nodeCode . ' ');
			$countElements += substr_count($blockContent, $nodeCode . '"');
			$node['placeholders'] = array_fill(0, $countElements, '');
			if (in_array($code, self::getAvatarNodes($blockCode), true))
			{
				$node['isAvatarNode'] = true;
			}
			if ($node['type'] === 'img' && !$block->isMenu())
			{
				$dataImages = Operator::getSizeDataImagesBySelector($code, $blockContent);
				$node['sizeData'] = $dataImages;
				$defaultSrcData = Operator::getDefaultSrc($dataImages);
				$node['defaultSrc'] = $defaultSrcData['src'];
				if ($node['create2xByDefault'] === false)
				{
					$node['defaultSrc2x'] = $node['defaultSrc'];
				}
				else
				{
					$node['defaultSrc2x'] = $defaultSrcData['src2x'];
				}
			}

			$type = NodeType::from($node['type'] ?? '');
			$createdNode = Node::create($type, $code, $node);
			if ($createdNode)
			{
				$block->addNode($createdNode);
			}
		}
	}

	/**
	 * Adds separator blocks between existing blocks based on background types.
	 *
	 * @return void
	 */
	private function pasteSeparatorBlocks(): void
	{
		$separatorsGroup = $this->getRandomSeparatorsGroup($this->getAllowedBlocks());

		if (!$separatorsGroup)
		{
			return;
		}

		$blocksListWithSeparators = [];
		$previousBgType = null;
		$previousSectionIsMenu = false;

		foreach ($this->blocks as $block)
		{
			$currentBgType = $block->getBgType();
			$randomSeparatorProbability = rand(1, 100);

			if (
				$randomSeparatorProbability <= self::SEPARATOR_CREATION_PROBABILITY
				&& !$previousSectionIsMenu
				&& $this->shouldAddSeparator($previousBgType, $currentBgType)
			)
			{
				$blocksListWithSeparators[] = $this->createSeparatorBlock($separatorsGroup, $currentBgType);
			}

			$blocksListWithSeparators[] = $block;
			$previousBgType = $currentBgType;
			$previousSectionIsMenu = $block->isMenu();
		}

		$this->blocks = $blocksListWithSeparators;
	}

	/**
	 * Adds title blocks before blocks which have not title.
	 *
	 * @return void
	 */
	private function pasteTitleBlocks(): void
	{
		$allowedBlocks = $this->getAllowedBlocks();
		$blocksListWithSeparators = [];
		foreach ($this->blocks as $block)
		{
			if ($this->isNeedTitles($block->getCode()))
			{
				$code = $this->getRandomElement(array_keys($allowedBlocks[self::SECTIONS['TITLE']]));
				$preferredBgType = $block->getBgType();
				if (in_array($preferredBgType, $allowedBlocks[self::SECTIONS['TITLE']][$code]['bgType'], true))
				{
					$bgType = $preferredBgType;
				}
				else
				{
					$bgType = $this->getRandomElement($allowedBlocks[self::SECTIONS['TITLE']][$code]['bgType']);
				}
				$blocksListWithSeparators[] = $this->createTitleBlock($code, $bgType);
			}
			$blocksListWithSeparators[] = $block;
		}
		$this->blocks = $blocksListWithSeparators;
	}

	/**
	 * Determines if a separator block should be added between two blocks.
	 *
	 * @param string|null $previousBgType
	 * @param string $currentBgType
	 *
	 * @return bool
	 */
	private function shouldAddSeparator(?string $previousBgType, string $currentBgType): bool
	{
		return ($currentBgType === self::BG_TYPES['PRIMARY'] && $previousBgType === self::BG_TYPES['TRANSPARENT'])
			|| ($currentBgType === self::BG_TYPES['TRANSPARENT'] && $previousBgType === self::BG_TYPES['PRIMARY']);
	}

	/**
	 * Creates a new separator block based on the current background type.
	 *
	 * @param array $separatorsGroup
	 * @param string $currentBgType
	 *
	 * @return Data\Block
	 */
	private function createSeparatorBlock(array $separatorsGroup, string $currentBgType): Data\Block
	{
		$separatorCode = $separatorsGroup[array_rand($separatorsGroup)];
		$bgTypeIsPrimary = $currentBgType === self::BG_TYPES['PRIMARY'];
		$separatorBg = $bgTypeIsPrimary ? self::BG_SEPARATOR_TYPES['TYPE_1'] : self::BG_SEPARATOR_TYPES['TYPE_2'];

		$separatorBlock = new Data\Block($separatorCode, self::SECTIONS['SEPARATORS'], $separatorBg);
		$nodes = Block::getManifestFile($separatorCode)['nodes'];

		foreach ($nodes as $key => $node)
		{
			if (isset($node['type']))
			{
				$type = NodeType::from($node['type']);
				$node = Node::create($type, $key, $node);
				if ($node)
				{
					$separatorBlock->addNode($node);
				}
			}
		}

		return $separatorBlock;
	}

	/**
	 * Creates a new title block.
	 *
	 * @param string $code
	 * @param string $bgType
	 *
	 * @return Data\Block
	 */
	private function createTitleBlock(string $code, string $bgType): Data\Block
	{
		return self::createBlock($code, self::SECTIONS['TITLE'], $bgType);
	}

	/**
	 * Selects a random group of separators.
	 *
	 * @param array $allowedBlocks
	 *
	 * @return ?array
	 */
	private function getRandomSeparatorsGroup(array $allowedBlocks): ?array
	{
		if (empty($allowedBlocks[self::SECTIONS['SEPARATORS']]))
		{
			return null;
		}

		return $allowedBlocks[self::SECTIONS['SEPARATORS']][array_rand($allowedBlocks[self::SECTIONS['SEPARATORS']])];
	}

	/**
	 * Applies the background type of block to the next block if conditions are met.
	 *
	 * Iterates over all blocks and applies the background type to the next block
	 * if the conditions specified in canApplyBgTypeToNextBlock are satisfied.
	 */
	private function applyBgTypeToNextBlock(): void
	{
		$blocksCount = count($this->blocks);
		foreach ($this->blocks as $index => $block)
		{
			if ($this->canApplyBgTypeToNextBlock($block, $index, $blocksCount))
			{
				$this->setBgTypeToNextBlock($block, $index);
			}
		}
	}

	/**
	 * Determines if the background type of the current block can be applied to the next block.
	 *
	 * @param Data\Block $block The current block being evaluated.
	 * @param int $index The index of the current block in the blocks array.
	 * @param int $blocksCount The total number of blocks.
	 *
	 * @return bool True if the background type can be applied to the next block, false otherwise.
	 */
	private function canApplyBgTypeToNextBlock(Data\Block $block, int $index, int $blocksCount): bool
	{
		$allowedBlocks = $this->getAllowedBlocks();
		if (
			$index + 1 > $blocksCount
			|| (
				$block->getSection() !== self::SECTIONS['TITLE']
				&& $block->getCode() !== '41.3.announcement_with_slider'
			)
		)
		{
			return false;
		}

		if (isset($this->blocks[$index + 1]))
		{
			$nextBlock = $this->blocks[$index + 1];
			$nextSection = $nextBlock->getSection();
			$nextCode = $nextBlock->getCode();

			return isset($allowedBlocks[$nextSection][$nextCode]['bgType'])
				&& in_array($block->getBgType(), $allowedBlocks[$nextSection][$nextCode]['bgType'], true);
		}

		return false;
	}

	/**
	 * Sets the background type of the next block to match the current block's background type.
	 *
	 * @param Data\Block $block The current block whose background type is being applied.
	 * @param int $index The index of the current block in the blocks array.
	 */
	private function setBgTypeToNextBlock(Data\Block $block, int $index): void
	{
		$nextBlock = $this->blocks[$index + 1];
		$nextBlock->setBgType($block->getBgType());
	}

	/**
	 * Get random from sections array. Control uniqueness for special sections
	 *
	 * @param array $sections The array of sections to choose from.
	 *
	 * @return string
	 */
	private function getRandomFromSections(array $sections): string
	{
		$sections = array_filter(
			$sections, function($section) {
			if (!in_array($section, self::JUST_ONCE_SECTIONS, true))
			{
				return true;
			}

			return !in_array($section, $this->addedSections, true);
		}
		);

		return $sections[array_rand($sections)];
	}

	/**
	 * Retrieves the allowed blocks with their respective background types.
	 *
	 * @return array Array of allowed blocks with background types.
	 */
	private function getAllowedBlocks(): array
	{
		$typeGroups = [
			'notImage' => [self::BG_TYPES['TRANSPARENT'], self::BG_TYPES['PRIMARY']],
		];

		return [
			self::SECTIONS['MENU'] => [
				'0.menu_05' => [
					'bgType' => $typeGroups['notImage'],
				],
				'0.menu_14_music' => [
					'bgType' => $typeGroups['notImage'],
				],
				'0.menu_19_travel' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['COVER'] => [
				'01.big_with_text_3' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'01.big_with_text_3_1' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'41.3.announcement_with_slider' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['TITLE'] => [
				'04.1.one_col_fix_with_title' => [
					'bgType' => $typeGroups['notImage'],
				],
				'04.7.one_col_fix_with_title_and_text_2' => [
					'bgType' => $typeGroups['notImage'],
				],
				'08.4.fix_title_and_text' => [
					'bgType' => $typeGroups['notImage'],
				],
				'27.one_col_fix_title_and_text_2' => [
					'bgType' => $typeGroups['notImage'],
				],
				'27.3.one_col_fix_title' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['TEXT_IMAGE'] => [
				'01.big_with_text_2' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'09.1.two_cols_fix_text_and_image_slider' => [
					'bgType' => $typeGroups['notImage'],
				],
				'09.1.two_cols_fix_text_and_image_slider_2' => [
					'bgType' => $typeGroups['notImage'],
				],
				'18.two_cols_fix_img_text_button' => [
					'bgType' => $typeGroups['notImage'],
				],
				'19.2.features_with_img' => [
					'bgType' => $typeGroups['notImage'],
				],
				'19.2.features_with_img_right' => [
					'bgType' => $typeGroups['notImage'],
				],
				'19.4.features_with_img' => [
					'bgType' => $typeGroups['notImage'],
				],
				'19.4.features_with_img_left' => [
					'bgType' => $typeGroups['notImage'],
				],
				'20.2.three_cols_fix_img_title_text' => [
					'bgType' => $typeGroups['notImage'],
				],
				'20.3.four_cols_fix_img_title_text' => [
					'bgType' => $typeGroups['notImage'],
				],
				'20.4.text_3cols_with_bottom_information' => [
					'bgType' => $typeGroups['notImage'],
				],
				'31.3.two_cols_text_img_fix' => [
					'bgType' => $typeGroups['notImage'],
				],
				'31.4.two_cols_img_text_fix' => [
					'bgType' => $typeGroups['notImage'],
				],
				'32.3.img_grid_1_2cols_1' => [
					'bgType' => $typeGroups['notImage'],
				],
				'32.5.img_grid_3cols_1' => [
					'bgType' => $typeGroups['notImage'],
				],
				'32.10.img_grid_2cols_3' => [
					'bgType' => $typeGroups['notImage'],
				],
				'32.11.img_grid_4cols_4' => [
					'bgType' => $typeGroups['notImage'],
				],
				'32.15.img_one_big_full' => [
					'bgType' => $typeGroups['notImage'],
				],
				'40.2.two_cols_carousel' => [
					'bgType' => $typeGroups['notImage'],
				],
				'40.6.two_img_top_bottom' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['FEEDBACK'] => [
				'23.big_carousel_blocks' => [
					'bgType' => $typeGroups['notImage'],
				],
				'43.3.cover_with_feedback' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'43.5.cover_with_feedback_2' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
			],
			self::SECTIONS['TARIFFS'] => [
				'11.2.three_cols_fix_tariffs_with_img' => [
					'bgType' => $typeGroups['notImage'],
				],
				'44.7.three_columns_with_img_and_price' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['TEAM'] => [
				'28.personal_slider_2' => [
					'bgType' => $typeGroups['notImage'],
				],
				'28.2.team' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'44.6.two_columns_with_peoples' => [
					'bgType' => $typeGroups['notImage'],
				],
			],
			self::SECTIONS['FORM'] => [
				'33.1.form_1_transparent_black_left_text' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'33.2.form_1_transparent_black_right_text' => [
					'bgType' => [self::BG_TYPES['IMAGE']],
				],
				'33.10.form_2_light_left_text' => [
					'bgType' => [self::BG_TYPES['TRANSPARENT']],
				],
				'33.12.form_2_light_right_text' => [
					'bgType' => [self::BG_TYPES['TRANSPARENT']],
				],
			],
			self::SECTIONS['SEPARATORS'] => [
				['26.2.separator', '26.3.separator'],
				['26.4.separator', '26.5.separator'],
				['26.6.separator_waves'],
			],
		];
	}

	private function isNeedTitles($codeBlock): bool
	{
		$blockCodesWhichNeedTitles = [
			'20.2.three_cols_fix_img_title_text',
			'20.3.four_cols_fix_img_title_text',
			'20.4.text_3cols_with_bottom_information',
			'32.3.img_grid_1_2cols_1',
			'32.5.img_grid_3cols_1',
			'32.10.img_grid_2cols_3',
			'32.11.img_grid_4cols_4',
			'32.15.img_one_big_full',
			'40.2.two_cols_carousel',
			'40.6.two_img_top_bottom',
			'23.big_carousel_blocks',
			'44.7.three_columns_with_img_and_price',
			'28.personal_slider_2',
			'44.6.two_columns_with_peoples',
		];

		if (in_array($codeBlock, $blockCodesWhichNeedTitles, true))
		{
			return true;
		}

		return false;
	}
}