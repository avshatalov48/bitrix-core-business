<?php

namespace Bitrix\Iblock\Grid\Row\Actions;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\Entity\ElementSettings;
use Bitrix\Iblock\Grid\Row\Actions\Item\ActivateElementItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\ClearCounterItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\CopyElementItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\CreateCodeItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\DeactivateElementItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\DeleteElementItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\DeleteSectionItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\DetailViewItem;
use Bitrix\Iblock\Grid\Row\Actions\Item\EditItem;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;
use Bitrix\Main\Grid\Row\Action\DataProvider;
use Bitrix\Main\Grid\Row\Action\Action;
use Bitrix\Main\Grid\Row\Action\SeparatorAction;

/**
 * @method ElementSettings getSettings()
 */
class ElementRowActionsProvider extends DataProvider
{
	private IblockRightsChecker $rights;

	public function __construct(ElementSettings $settings, IblockRightsChecker $rights)
	{
		parent::__construct($settings);

		$this->rights = $rights;
	}

	final protected function getIblockId(): int
	{
		return $this->getSettings()->getIblockId();
	}

	final protected function getIblockRightsChecker(): IblockRightsChecker
	{
		return $this->rights;
	}

	private function getUrlBuilder(): ?BaseBuilder
	{
		return $this->getSettings()->getUrlBuilder();
	}

	public function prepareActions(): array
	{
		$result = [
			new DetailViewItem(),
			new DeleteSectionItem($this->rights), // check by concrete section
		];

		if ($this->getIblockRightsChecker()->canEditElements())
		{
			array_push($result, ... [
				new EditItem(),
				new CopyElementItem(),
				new CreateCodeItem($this->getIblockId(), $this->rights),
				new DeactivateElementItem($this->getIblockId(), $this->rights),
				new ActivateElementItem($this->getIblockId(), $this->rights),
				new ClearCounterItem($this->getIblockId(), $this->rights),
			]);
		}

		if ($this->getIblockRightsChecker()->canDeleteElements())
		{
			$result[] = new DeleteElementItem($this->rights);
		}

		return $result;
	}

	public function prepareControls(array $rawFields): array
	{
		$isSection = isset($rawFields['ROW_TYPE']) && $rawFields['ROW_TYPE'] === 'S';
		if ($isSection)
		{
			$items = $this->getSectionItems($rawFields);
		}
		else
		{
			$items = $this->getElementItems($rawFields);
		}

		$result = [];

		foreach ($items as $item)
		{
			$config = $item->getControl($rawFields);
			if (isset($config))
			{
				$result[] = $config;
			}
		}

		return $result;
	}

	/**
	 * @param array $rawFields
	 *
	 * @return Action[]
	 */
	private function getSectionItems(array $rawFields): array
	{
		if (empty($rawFields['ID']))
		{
			return [];
		}
		$sectionId = (int)$rawFields['ID'];

		$result = [];

		if ($this->rights->canEditSection($sectionId))
		{
			$detailUrl = $this->getSectionEditUrl($sectionId);
			if (isset($detailUrl))
			{
				/**
				 * @var EditItem $item
				 */
				$item = $this->getActionById(EditItem::getId());
				if (isset($item))
				{
					$item->setUrl($detailUrl);
					$result[] = $item;
				}
			}

			self::appendIfNotNull(
				$result,
				$this->getActionById(CreateCodeItem::getId())
			);
		}

		if ($this->rights->canDeleteSection($sectionId))
		{
			self::appendIfNotNull(
				$result,
				$this->getActionById(DeleteSectionItem::getId())
			);
		}

		return $result;
	}

	/**
	 * @param array $rawFields
	 *
	 * @return Action[]
	 */
	private function getElementItems(array $rawFields): array
	{
		if (empty($rawFields['ID']))
		{
			return [];
		}
		$elementId = (int)$rawFields['ID'];

		$result = [];

		if ($this->rights->canEditElement($elementId))
		{
			$detailUrl = $this->getElementDetailViewUrl($elementId);
			if (isset($detailUrl))
			{
				/**
				 * @var DetailViewItem $item
				 */
				$item = $this->getActionById(DetailViewItem::getId());
				if (isset($item))
				{
					$item->setUrl($detailUrl);
					$result[] = $item;
				}
			}

			if (isset($rawFields['ACTIVE']) && $rawFields['ACTIVE'] === 'Y')
			{
				self::appendIfNotNull(
					$result,
					$this->getActionById(DeactivateElementItem::getId())
				);
			}
			else
			{
				self::appendIfNotNull(
					$result,
					$this->getActionById(ActivateElementItem::getId())
				);
			}

			self::appendIfNotNull(
				$result,
				$this->getActionById(CreateCodeItem::getId())
			);

			if (!empty($result))
			{
				$result[] = new SeparatorAction();
			}

			$item = $this->getActionById(ClearCounterItem::getId());
			if (isset($item))
			{
				$result[] = $item;
				$result[] = new SeparatorAction();
			}

			if ($this->rights->canAddElement($elementId))
			{
				$copyUrl = $this->getElementCopyUrl($elementId);
				if (isset($copyUrl))
				{
					/**
					 * @var CopyElementItem $item
					 */
					$item = $this->getActionById(CopyElementItem::getId());
					if (isset($item))
					{
						$item->setUrl($copyUrl);
						$result[] = $item;
					}
				}
			}
		}

		if ($this->rights->canDeleteElement($elementId))
		{
			$item = $this->getActionById(DeleteElementItem::getId());
			if (isset($item))
			{
				if (!empty($result))
				{
					$lastItem = $result[array_key_last($result)];
					if (($lastItem instanceof SeparatorAction) === false)
					{
						$result[] = new SeparatorAction();
					}
				}

				$result[] = $item;
			}
		}

		return $result;
	}

	private function getSectionEditUrl(int $sectionId): ?string
	{
		$urlBuilder = $this->getUrlBuilder();
		if (!isset($urlBuilder))
		{
			return null;
		}

		return $urlBuilder->getSectionDetailUrl($sectionId);
	}

	private function getElementDetailViewUrl(int $elementId): ?string
	{
		$urlBuilder = $this->getUrlBuilder();
		if (!isset($urlBuilder))
		{
			return null;
		}

		return $urlBuilder->getElementDetailUrl($elementId);
	}

	private function getElementCopyUrl(int $elementId): ?string
	{
		$urlBuilder = $this->getUrlBuilder();
		if (!isset($urlBuilder))
		{
			return null;
		}

		return $urlBuilder->getElementCopyUrl($elementId);
	}

	#region helpers

	private static function appendIfNotNull(array &$items, $item): void
	{
		if (isset($item))
		{
			$items[] = $item;
		}
	}

	#endregion helpers
}
