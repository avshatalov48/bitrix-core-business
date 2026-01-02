<?php

namespace Bitrix\UI\Toolbar;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\UI\Filter\Theme;
use Bitrix\UI\Buttons\AirButtonStyle;
use Bitrix\UI\Buttons\BaseButton;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Size;
use Bitrix\UI\Buttons\Icon;

class Toolbar
{
	private $id;
	private $filter;
	private ?string $title = null;
	private bool $isMultiLineTitleEnabled = false;
	private ?string $beforeTitleBoxHtml = null;
	private $beforeTitleHtml;
	private $afterTitleHtml;
	private $underTitleHtml;
	private string $rightCustomHtml;
	private array $rightCustomHtmlOptions = [];
	private $titleMinWidth;
	private $titleMaxWidth;
	private bool $isTitleNoShrink = false;
	private $titleVisibility = true;
	private $favoriteStar = true;
	private bool $editableTitle = false;
	private string $defaultEditableTitle;
//	private string $editableTitleSelector;
	private ?array $copyLinkButtonParams = null;
	private $enabled = true;

	/**
	 * @param Button[] $buttons
	 */
	private $afterTitleButtons = [];
	/**
	 * @param Button[] $buttons
	 */
	private $buttons = [];
	private $filterButtons = [];
	private $options;

	/**
	 * Toolbar constructor.
	 *
	 * @param string $id
	 * @param array $options
	 */
	public function __construct($id, $options)
	{
		$this->id = $id;
		$this->options = $options;

		if (isset($this->options['filter']))
		{
			$this->addFilter($this->options['filter']);
		}
	}

	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	public function enable(): void
	{
		$this->enabled = true;
	}

	public function disable(): void
	{
		$this->enabled = false;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	public function getTitle($propertyName = false, $stripTags = false): string
	{
		if ($this->title !== null)
		{
			return $stripTags ? strip_tags($this->title) : $this->title;
		}

		$title = $GLOBALS['APPLICATION']->getTitle($propertyName, $stripTags);

		return is_string($title) ? $title : '';
	}

	public function hideTitle(): void
	{
		$this->titleVisibility = false;
	}

	public function hasTitle(): bool
	{
		return $this->titleVisibility;
	}

	public function addEditableTitle(string $defaultTitle = null): void
	{
		$this->defaultEditableTitle = $defaultTitle ?? '';
		$this->editableTitle = true;
	}

	public function getDefaultEditableTitle(): ?string
	{
		return $this->hasEditableTitle()
			? $this->defaultEditableTitle
			: null
		;
	}

//	public function getEditableTitleSelector(): ?string
//	{
//		return $this->hasEditableTitle()
//			? $this->editableTitleSelector
//			: null
//		;
//	}

	public function hasEditableTitle(): bool
	{
		return $this->editableTitle;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param array|Button $button
	 * @param string $location
	 * @see ButtonLocation
	 *
	 * @throws ArgumentTypeException
	 */
	public function addButton($button, $location = ButtonLocation::RIGHT)
	{
		if (is_array($button))
		{
			$button = new Button($button);
		}

		if ($this->hasAirDesign())
		{
			$button->setAirDesign(true);
			$button->setNoCaps(true);
			if ($button->getIcon() && $button->hasCollapsedIcon() === false)
			{
				// Set classname modifiers for air buttons
				$button->setIcon($button->getIcon());
			}

			if ($button->getStyle() === null && $button->getColor() !== null)
			{
				$button->setStyle($this->convertColorToAirButtonStyle($button->getColor()));
			}
			if ($location === ButtonLocation::RIGHT && $button->getSize() === null)
			{
				$button->setSize(Size::SMALL);
			}

			if (
				$location === ButtonLocation::AFTER_TITLE
				&& $button->getStyle() === AirButtonStyle::FILLED_SUCCESS
				&& $button->getIcon() === null
			)
			{
				$button->setIcon(Icon::ADD_M);
			}
		}

		if (!($button instanceof Button))
		{
			throw new ArgumentTypeException("button", Button::class);
		}

		if ($location === ButtonLocation::AFTER_FILTER)
		{
			$this->filterButtons[] = $button;
		}
		elseif($location === ButtonLocation::AFTER_TITLE)
		{
			$this->afterTitleButtons[] = $button;
		}
		else
		{
			$this->buttons[] = $button;
		}
	}

	public function deleteButtons(\Closure $closure)
	{
		foreach ($this->buttons as $i => $button)
		{
			if ($closure($button, ButtonLocation::RIGHT) === true)
			{
				unset($this->buttons[$i]);
			}
		}

		foreach ($this->filterButtons as $i => $button)
		{
			if ($closure($button, ButtonLocation::AFTER_FILTER) === true)
			{
				unset($this->filterButtons[$i]);
			}
		}

		foreach ($this->afterTitleButtons as $i => $button)
		{
			if ($closure($button, ButtonLocation::AFTER_TITLE) === true)
			{
				unset($this->afterTitleButtons[$i]);
			}
		}
	}

	public function shuffleButtons(\Closure $closure, $buttonLocation)
	{
		$buttonList = null;
		switch ($buttonLocation)
		{
			case ButtonLocation::RIGHT:
				$buttonList = $this->buttons;
				break;
			case ButtonLocation::AFTER_FILTER:
				$buttonList = $this->filterButtons;
				break;
		}

		if ($buttonList)
		{
			$buttonList = $closure($buttonList);
			if (!is_array($buttonList))
			{
				throw new ArgumentTypeException('buttonList', 'array');
			}

			switch ($buttonLocation)
			{
				case ButtonLocation::RIGHT:
					$this->buttons = $buttonList;
					break;
				case ButtonLocation::AFTER_FILTER:
					$this->filterButtons = $buttonList;
					break;
			}
		}
	}

	public function hasFavoriteStar()
	{
		return (bool)$this->favoriteStar;
	}

	public function addFavoriteStar()
	{
		$this->favoriteStar = true;

		return $this;
	}

	public function deleteFavoriteStar()
	{
		$this->favoriteStar = false;

		return $this;
	}

	public function addFilter(array $filterOptions = [])
	{
		ob_start();

		if ($this->hasAirDesign())
		{
			$updatedFilterOptions = [
				...$filterOptions,
				'THEME' => Theme::AIR,
			];

			if (isset($updatedFilterOptions['CONFIG']) === false)
			{
				$updatedFilterOptions['CONFIG'] = [];
			}

			$updatedFilterOptions['CONFIG']['AUTOFOCUS'] = false;

			$GLOBALS['APPLICATION']->includeComponent('bitrix:main.ui.filter', '', $updatedFilterOptions);
		}
		else
		{
			$GLOBALS['APPLICATION']->includeComponent('bitrix:main.ui.filter', '', $filterOptions);
		}

		$this->filter = ob_get_clean();
	}

	public function setFilter(string $filter)
	{
		$this->filter = $filter;
	}

	public function getFilter()
	{
		return $this->filter;
	}

	public function addBeforeTitleBoxHtml(string $html): void
	{
		$this->beforeTitleBoxHtml = $html;
	}

	public function getBeforeTitleBoxHtml(): ?string
	{
		return $this->beforeTitleBoxHtml;
	}

	public function addBeforeTitleHtml(string $html)
	{
		$this->beforeTitleHtml = $html;
	}

	public function getBeforeTitleHtml(): ?string
	{
		return $this->beforeTitleHtml;
	}

	public function addAfterTitleHtml(string $html)
	{
		$this->afterTitleHtml = $html;
	}

	public function getAfterTitleHtml(): ?string
	{
		return $this->afterTitleHtml;
	}

	public function addUnderTitleHtml(string $html)
	{
		$this->underTitleHtml = $html;
	}

	public function getUnderTitleHtml(): ?string
	{
		return $this->underTitleHtml;
	}

	public function addRightCustomHtml(string $html, array $options = []): void
	{
		$this->rightCustomHtml = $html;
		$this->rightCustomHtmlOptions = $options;
	}

	public function getRightCustomHtml(): string
	{
		return $this->rightCustomHtml ?? '';
	}

	public function getRightCustomHtmlOptions(): array
	{
		return $this->rightCustomHtmlOptions ?? [];
	}

	/**
	 * @param null|array{link?: string, successfulCopyMessage?: string, title?: string} $params
	 *
	 * @return void
	 */
	public function setCopyLinkButton(?array $params = []): void
	{
		if (is_array($params))
		{
			$this->copyLinkButtonParams = $params;
			$this->copyLinkButtonParams['active'] = true;
		}
		else
		{
			$this->copyLinkButtonParams = null;
		}
	}

	/**
	 * @return null|array{link: string, successfulCopyMessage?: string, title?: string}
	 */
	public function getCopyLinkButton(): ?array
	{
		return $this->copyLinkButtonParams;
	}

	/**
	 * @return BaseButton[]
	 */
	public function getButtons()
	{
		return array_merge($this->afterTitleButtons, $this->filterButtons, $this->buttons);
	}

	public function renderAfterTitleButtons()
	{
		return implode(array_map(function(Button $button) {
			return self::processButtonRender($button);
		}, $this->afterTitleButtons));
	}

	public function renderRightButtons()
	{
		return implode(array_map(function(Button $button) {
			return self::processButtonRender($button);
		}, $this->buttons));
	}

	public function renderAfterFilterButtons()
	{
		return implode(array_map(function(Button $button) {
			return self::processButtonRender($button);
		}, $this->filterButtons));
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function renderFilterRightButtons()
	{
		return $this->renderAfterFilterButtons();
	}

	protected function processButtonRender(Button $button)
	{
		$shouldAddThemeModifier = (bool)array_intersect($button->getClassList(), [
			'ui-btn-light-border',
			'ui-btn-light',
			'ui-btn-link',
		]) && $button->hasAirDesign() === false;

		if ($shouldAddThemeModifier)
		{
			$button->addClass('ui-btn-themes');
		}

		return $button->render(false);
	}

	public function setTitleMinWidth($width)
	{
		if (is_int($width) && $width > 0)
		{
			$this->titleMinWidth = $width;
		}
	}

	public function getTitleMinWidth()
	{
		return $this->titleMinWidth;
	}

	public function setTitleMaxWidth($width)
	{
		if (is_int($width) && $width > 0)
		{
			$this->titleMaxWidth = $width;
		}
	}

	public function setTitleNoShrink(bool $flag = true): void
	{
		$this->isTitleNoShrink = $flag;
	}

	public function isTitleNoShrink(): bool
	{
		return $this->isTitleNoShrink;
	}

	public function getTitleMaxWidth()
	{
		return $this->titleMaxWidth;
	}

	public function enableMultiLineTitle(): void
	{
		$this->isMultiLineTitleEnabled = true;
	}

	public function disableMultiLineTitle(): void
	{
		$this->isMultiLineTitleEnabled = false;
	}

	public function isMultiLineTitleEnabled(): bool
	{
		return $this->isMultiLineTitleEnabled;
	}

	/**
	 * @param string|null $color
	 * @return string|null
	 */
	protected function convertColorToAirButtonStyle(string $color = null): ?string
	{
		$map = [
			Color::DANGER => AirButtonStyle::FILLED_ALERT,
			Color::DANGER_LIGHT => AirButtonStyle::FILLED_ALERT,
			Color::DANGER_DARK => AirButtonStyle::FILLED_ALERT,
			Color::SUCCESS => AirButtonStyle::FILLED_SUCCESS,
			Color::SUCCESS_LIGHT => AirButtonStyle::FILLED_SUCCESS,
			Color::PRIMARY => AirButtonStyle::FILLED_SUCCESS,
			Color::PRIMARY_DARK => AirButtonStyle::FILLED_SUCCESS,
			Color::LIGHT_BORDER => AirButtonStyle::OUTLINE,
			Color::LINK => AirButtonStyle::OUTLINE,
			Color::SECONDARY => AirButtonStyle::TINTED,
			Color::LIGHT => AirButtonStyle::PLAIN_ACCENT,
		];

		return $map[$color] ?? null;
	}

	public function hasAirDesign(): bool
	{
		return defined('AIR_SITE_TEMPLATE');
	}
}
