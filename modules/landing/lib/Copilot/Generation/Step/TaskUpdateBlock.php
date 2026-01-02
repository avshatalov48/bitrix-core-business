<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Hook\Page\Fonts;
use Bitrix\Landing\Landing;
use Bitrix\Landing\PublicAction\Block;
use Bitrix\Landing\Rights;

class TaskUpdateBlock extends TaskStep
{
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
		$landingInstance = $this->siteData->getLandingInstance();
		if ($landingInstance !== null)
		{
			$this->landingInstance = $landingInstance;
			$this->landingId = $this->landingInstance->getId();
			$this->updateBlocks();
		}
		Rights::setGlobalOn();

		return true;
	}

	/**
	 * Configures blocks for a given landing instance.
	 *
	 * @return void
	 */
	protected function updateBlocks(): void
	{
		foreach ($this->siteData->getBlocks() as $block)
		{
			$this->updateBlock($block);
		}
	}

	/**
	 * Configures a single block within a landing instance.
	 *
	 * @param Data\Block $blockData Data for configuring the block.
	 *
	 * @return void
	 */
	private function updateBlock(Data\Block $blockData): void
	{
		$blockId = $blockData->getId();

		if (!$blockId || $blockId <= 0)
		{
			return;
		}

		$blockInstance = $this->landingInstance->getBlockById($blockId);
		$blockNodes = $blockData->getNodes();
		if ($blockInstance && $blockNodes)
		{
			$nodesArray = [];
			foreach ($blockNodes as $blockNode)
			{
				$code = $blockNode->getCode();
				$nodesArray[$code] = $blockNode->getValues();
			}
			$blockInstance->updateNodes($nodesArray);
			$blockInstance->save();

			$this->updateBlockStyles($blockInstance, $blockData);
		}
	}

	private function updateBlockStyles(\Bitrix\Landing\Block $blockInstance, Data\Block $blockData): void
	{
		$styles = [];

		$styles = $this->updateBackgroundStyles($styles, $blockData, $blockInstance);
		$styles = $this->updateNodeStyles($styles, $blockData, $blockInstance);

		$this->applyStylesToBlock($blockData, $styles);
	}

	private function updateBackgroundStyles(
		array $styles,
		Data\Block $blockData,
		\Bitrix\Landing\Block $blockInstance
	): array
	{
		$updatedStyles = $styles;
		$blockStyles = $blockData->getStyles();

		$styleManifest = $blockInstance->getManifest()['style'] ?? [];
		$typesWithBackground = [
			'background',
			'block-default',
			'block-default-background',
			'block-default-background-height-vh',
			'block-border',
		];

		if (
			isset($styleManifest['block']['type'])
			&& !empty(
			array_intersect(
				$typesWithBackground,
				$styleManifest['block']['type']
			)
			)
		)
		{
			$wrapperSelector = '#block' . $blockInstance->getId();
			$blockContent = $blockInstance->getContent();
			$wrapperSelectorClasses = explode(' ', Data\Block\Operator::extractWrapperClasses($blockContent));

			if (
				isset($blockStyles['background']) && $blockStyles['background'] !== ''
				&& !in_array('g-bg-image', $wrapperSelectorClasses, true)
			)
			{
				$preparedSelectorClasses = [];
				$bgStyleClass = 'g-bg';
				foreach ($wrapperSelectorClasses as $class)
				{
					if (
						$class === $bgStyleClass
						|| (!str_starts_with($class, 'g-bg-') && !str_starts_with($class, 'g-theme-'))
					)
					{
						$preparedSelectorClasses[] = $class;
					}
				}
				if (!in_array($bgStyleClass, $preparedSelectorClasses, true))
				{
					$preparedSelectorClasses[] = $bgStyleClass;
				}
				$updatedStyles[$wrapperSelector] = [
					'style' => ['--bg' => $blockStyles['background']],
					'classList' => $preparedSelectorClasses,
				];
			}
		}

		return $updatedStyles;
	}

	private function updateNodeStyles(
		array $styles,
		Data\Block $blockData,
		\Bitrix\Landing\Block $blockInstance
	): array
	{
		$blockStyles = $blockData->getStyles();

		$styleManifest = $blockInstance->getManifest()['style'] ?? [];
		$nodesWithProperty = [
			'color' => [],
			'fontFamily' => [],
		];

		if (isset($styleManifest['nodes']))
		{
			foreach ($styleManifest['nodes'] as $codeNode => $styleNode)
			{
				$nodesWithProperty = $this->processStyleNode(
					$styleNode,
					$blockStyles,
					$codeNode,
					$nodesWithProperty
				);
			}
		}

		$updatedStyles = $this->applyColorStyles($styles, $blockData, $nodesWithProperty['color'], $blockInstance);

		return $this->applyFontFamilyStyles(
			$updatedStyles,
			$blockData,
			$nodesWithProperty['fontFamily'],
			$blockInstance
		);
	}

	private function processStyleNode(
		array $styleNode,
		array $blockStyles,
		string $codeNode,
		array $nodesWithProperty,
	): array
	{
		$updatedNodesWithProperty = $nodesWithProperty;

		if (is_string($styleNode['type']))
		{
			$styleNode['type'] = [$styleNode['type']];
		}

		if (!is_array($styleNode['type']))
		{
			return $updatedNodesWithProperty;
		}

		if (
			(isset($blockStyles['textsColor']) && $blockStyles['textsColor'] !== '')
			|| (isset($blockStyles['headersColor']) && $blockStyles['headersColor'] !== '')
		)
		{
			$typesWithColor = ['color', 'typo', 'typo-link', 'button'];
			if (!empty(array_intersect($typesWithColor, $styleNode['type'])))
			{
				$updatedNodesWithProperty['color'][] = $codeNode;
			}
		}

		if (
			(isset($blockStyles['textsFontName']) && $blockStyles['textsFontName'] !== '')
			|| (isset($blockStyles['headersFontName']) && $blockStyles['headersFontName'] !== '')
		)
		{
			$typesWithFontFamily = ['color', 'typo', 'typo-link', 'button'];
			if (!empty(array_intersect($typesWithFontFamily, $styleNode['type'])))
			{
				$updatedNodesWithProperty['fontFamily'][] = $codeNode;
			}
		}

		return $updatedNodesWithProperty;
	}

	private function applyColorStyles(
		array $styles,
		Data\Block $blockData,
		array $nodesWithPropertyColor,
		\Bitrix\Landing\Block $blockInstance
	): array
	{
		$updatedStyles = $styles;
		if (empty($nodesWithPropertyColor))
		{
			return $updatedStyles;
		}

		$blockStyles = $blockData->getStyles();

		$styleNodesSelectorClasses = [];

		$blockContent = $blockInstance->getContent();
		foreach ($nodesWithPropertyColor as $codeNode)
		{
			$styleNodesSelectorClasses = $this->prepareNodeSelectorClasses(
				$styleNodesSelectorClasses,
				$codeNode,
				$blockContent,
				'g-color',
				['g-color-'],
				[],
			);
			$updatedStyles[$codeNode]['style']['--color'] = $blockStyles['textsColor'];
			$updatedStyles[$codeNode]['classList'] = $styleNodesSelectorClasses[$codeNode];
		}

		return $updatedStyles;
	}

	private function applyFontFamilyStyles(
		array $styles,
		Data\Block $blockData,
		array $nodesWithPropertyFontFamily,
		\Bitrix\Landing\Block $blockInstance
	): array
	{
		$updatedStyles = $styles;
		if (empty($nodesWithPropertyFontFamily))
		{
			return $updatedStyles;
		}

		$blockStyles = $blockData->getStyles();

		$styleNodesSelectorClasses = [];
		$blockContent = $blockInstance->getContent();
		foreach ($nodesWithPropertyFontFamily as $codeNode)
		{
			$styleNodesSelectorClasses = $this->prepareNodeSelectorClasses(
				$styleNodesSelectorClasses,
				$codeNode,
				$blockContent,
				'g-font-' . strtolower(str_replace(' ', '-', $blockStyles['textsFontName'])),
				['g-font-'],
				['g-font-size-', 'g-font-weight-', 'g-font-style-']
			);
			$updatedStyles[$codeNode]['affect'][] = 'font-family';
			$updatedStyles[$codeNode]['classList'] = $styleNodesSelectorClasses[$codeNode];
		}

		$content = Fonts::generateFontTags($blockStyles['textsFontName']);
		$landingId = $blockInstance->getLandingId();
		\Bitrix\Landing\PublicAction\Landing::updateHead($landingId, $content);

		return $updatedStyles;
	}

	private function prepareNodeSelectorClasses(
		array $styleNodesSelectorClasses,
		string $codeNode,
		string $blockContent,
		string $classToAdd,
		array $setPrefixToRemove,
		array $setPrefixNotRemove
	): array
	{
		$preparedStyleNodesSelectorClasses = $styleNodesSelectorClasses;

		if (!isset($preparedStyleNodesSelectorClasses[$codeNode]))
		{
			$preparedStyleNodesSelectorClasses[$codeNode] = explode(
				' ',
				Data\Block\Operator::extractNodeClasses(
					$codeNode,
					$blockContent
				)
			);
		}
		$preparedSelectorClasses = [];

		foreach ($preparedStyleNodesSelectorClasses[$codeNode] as $class)
		{
			if (
				is_string($class)
				&& (
					$class === $classToAdd
					|| !$this->canRemoveClass($class, $setPrefixToRemove, $setPrefixNotRemove)
				)
			)
			{
				$preparedSelectorClasses[] = $class;
			}
		}
		if (!in_array($classToAdd, $preparedSelectorClasses, true))
		{
			$preparedSelectorClasses[] = $classToAdd;
		}

		$preparedStyleNodesSelectorClasses[$codeNode] = $preparedSelectorClasses;

		return $preparedStyleNodesSelectorClasses;
	}

	private function applyStylesToBlock(Data\Block $blockData, array $styles): void
	{
		$blockId = $blockData->getId();

		if (!$blockId)
		{
			return;
		}

		$blockInstance = $this->landingInstance->getBlockById($blockId);
		if ($blockInstance)
		{
			Block::updateStyles($this->landingId, $blockInstance->getId(), $styles, true);
		}
	}

	protected function canRemoveClass(string $class, array $prefixes, array $prefixesNotRemove): bool
	{
		foreach ($prefixes as $prefix)
		{
			if (!str_starts_with($class, $prefix))
			{
				continue;
			}

			foreach ($prefixesNotRemove as $prefixeNotRemove)
			{
				if (str_starts_with($class, $prefixeNotRemove))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}
}