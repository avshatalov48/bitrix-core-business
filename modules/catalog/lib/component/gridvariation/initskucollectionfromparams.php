<?php

namespace Bitrix\Catalog\Component\GridVariation;

use Bitrix\Catalog\Component\GridServiceForm;
use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\v2\Barcode\Barcode;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Catalog\v2\Sku\SkuCollection;

trait InitSkuCollectionFromParams
{
	/**
	 * Filling sku fields from rows array.
	 *
	 * @param SkuCollection $skuCollection
	 * @param array[] $rows each item must contain the fields specified in the form settings.
	 * @param bool $isServiceForm
	 *
	 * @see \Bitrix\Catalog\Component\GridServiceForm
	 * @see \Bitrix\Catalog\Component\GridVariationForm
	 *
	 * @return void
	 */
	protected function initFieldsSkuCollectionItems(SkuCollection $skuCollection, array $rows, bool $isServiceForm): void
	{
		if (empty($rows))
		{
			return;
		}

		foreach ($rows as $id => $row)
		{
			if (!is_array($row))
			{
				continue;
			}

			$isNew = (string)(int)$id !== (string)$id; // available exist: 564; new ids: oX03gdf, 000000001450000000
			$sku =
				$isNew
					? $skuCollection->create()
					: $skuCollection->findById((int)$id)
			;
			if (!($sku instanceof BaseSku))
			{
				continue;
			}


			$form = $isServiceForm ? new GridServiceForm($sku) : new GridVariationForm($sku);
			$fields = $form->prepareFieldsValues($row);

			$sku->setFields($fields);

			if (isset($fields['PROPERTIES']))
			{
				$sku->getPropertyCollection()->setValues($fields['PROPERTIES']);
			}

			if (isset($fields['PRICES']))
			{
				$sku->getPriceCollection()->setValues($fields['PRICES']);
			}

			if (isset($fields['MEASURE']))
			{
				$sku->getMeasureRatioCollection()->setDefault($fields['MEASURE']);
			}

			if (isset($fields['BARCODES']))
			{
				/**
				 * @var Barcode[] $existBarcodes
				 */
				$existBarcodes = [];

				foreach ($sku->getBarcodeCollection() as $barcode)
				{
					$code = $barcode->getBarcode();
					if ($code)
					{
						$existBarcodes[$code] = $barcode;
					}
				}

				foreach ($fields['BARCODES'] as $code)
				{
					if (empty($code))
					{
						continue;
					}

					$existBarcode = $existBarcodes[$code] ?? null;
					if ($existBarcode instanceof Barcode)
					{
						unset($existBarcodes[$code]);
					}
					else
					{
						$sku->getBarcodeCollection()->create()->setBarcode($code);
					}
				}

				if ($existBarcodes)
				{
					$sku->getBarcodeCollection()->remove(...$existBarcodes);
				}
			}
		}
	}
}
