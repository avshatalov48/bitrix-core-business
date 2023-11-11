<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Uri;

Loader::requireModule('iblock');

/**
 * @method ProductSettings getSettings()
 */
final class MorePhotoAssembler extends FieldAssembler
{
	private array $entities;

	public function __construct(array $columnIds, ProductSettings $settings)
	{
		parent::__construct($columnIds, $settings);

		$this->preloadResources();
	}

	/**
	 * Preload resources.
	 *
	 * It is always called, even if empty list of products.
	 * It is necessary for correct display in case of filtering.
	 *
	 * Example, an empty list with a filter, the filter was reset - the products appeared, but the resources were not loaded.
	 *
	 * @return void
	 */
	private function preloadResources(): void
	{
		Asset::getInstance()->addJs('/bitrix/components/bitrix/ui.image.input/templates/.default/script.js');
		Asset::getInstance()->addCss('/bitrix/components/bitrix/ui.image.input/templates/.default/style.css');
	}

	private function getIblockId(): int
	{
		return $this->getSettings()->getIblockId();
	}

	private function getOffersIblockId(): ?int
	{
		return $this->getSettings()->getOffersIblockId();
	}

	#region override

	public function prepareRows(array $rowList): array
	{
		if (empty($this->getColumnIds()))
		{
			return $rowList;
		}

		$this->loadEntities($rowList);

		foreach ($rowList as &$row)
		{
			$id = (int)($row['data']['ID'] ?? 0);
			$type = $row['data']['ROW_TYPE'] ?? null;
			/**
			 * @var BaseProduct|BaseSku $entity
			 */
			$entity = $this->entities[$id] ?? null;

			$row['columns'] ??= [];

			foreach ($this->getColumnIds() as $columnId)
			{
				if ($id === 0 || $type !== RowType::ELEMENT || !isset($entity))
				{
					$row['data'][$columnId] = null;
					$row['columns'][$columnId] = null;
				}
				elseif ($this->getSettings()->isExcelMode())
				{
					$imagesSrc = [];
					foreach ($entity->getFrontImageCollection() as $image)
					{
						$uri = new Uri($image->getSource());
						$imagesSrc[] = (string)$uri->toAbsolute();
					}

					$row['data'][$columnId] = join(', ', $imagesSrc);
					$row['columns'][$columnId] = join(', ', $imagesSrc);
				}
				else
				{
					$imageInput = new ImageInput($entity);
					//$imageInput->disableAutoSaving();
					$html = $imageInput->getFormattedField();

					$row['data']['~' . $columnId] = $html['input'];
					$row['columns'][$columnId] = $html['preview'];
				}
			}
		}

		return $rowList;
	}

	protected function prepareColumn($value)
	{
		return $value;
	}

	#endregion override

	private function loadEntities(array $rowList): void
	{
		$this->entities = [];

		$productToOfferId = $this->getSettings()->getSelectedProductOfferIds();
		$offerToProductId = array_flip($productToOfferId);

		$ids = [];
		foreach ($rowList as $row)
		{
			$id = (int)($row['data']['ID'] ?? 0);
			if ($id === 0)
			{
				continue;
			}

			$type = $row['data']['ROW_TYPE'] ?? null;
			if ($type !== RowType::ELEMENT)
			{
				continue;
			}

			// replace `product id` to `offer id` to display correct offer image.
			if (isset($productToOfferId[$id]))
			{
				$id = $productToOfferId[$id];
			}

			$ids[] = $id;
		}

		if (empty($ids))
		{
			return;
		}

		$repository = ServiceContainer::getProductRepository($this->getIblockId());
		if (isset($repository))
		{
			$items = $repository->getEntitiesBy([
				'filter' => [
					'ID' => $ids,
				],
			]);
			foreach ($items as $item)
			{
				/**
				 * @var BaseProduct $item
				 */

				$this->entities[$item->getId()] = $item;
			}
		}

		$offersIblockId = $this->getOffersIblockId();
		$repository =
			isset($offersIblockId)
				? ServiceContainer::getSkuRepository($offersIblockId)
				: null
		;
		if (isset($repository))
		{
			$items = $repository->getEntitiesBy([
				'filter' => [
					'ID' => $ids,
				],
			]);
			foreach ($items as $item)
			{
				/**
				 * @var BaseSku $item
				 */

				$productId = $offerToProductId[$item->getId()];
				$this->entities[$productId] = $item;
			}
		}
	}
}
