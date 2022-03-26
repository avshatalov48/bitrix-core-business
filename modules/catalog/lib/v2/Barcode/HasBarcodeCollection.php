<?php

namespace Bitrix\Catalog\v2\Barcode;

/**
 * Interface HasBarcodeCollection
 *
 * @package Bitrix\Catalog\v2\Barcode
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasBarcodeCollection
{
	public function getBarcodeCollection(): BarcodeCollection;

	public function setBarcodeCollection(BarcodeCollection $barcodeCollection);
}
