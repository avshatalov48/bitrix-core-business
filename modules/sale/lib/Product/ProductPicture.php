<?php

namespace Bitrix\Sale\Product;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;

final class ProductPicture
{
	private const CODE_PREVIEW_PICTURE = 'PREVIEW_PICTURE';
	private const CODE_DETAIL_PICTURE = 'DETAIL_PICTURE';
	private const CODE_MORE_PHOTO = \CIBlockPropertyTools::CODE_MORE_PHOTO;

	public static function get(array $productIdList): ?array
	{
		if (!Loader::includeModule('iblock'))
		{
			return null;
		}

		if (empty($productIdList))
		{
			return null;
		}

		$result = [];
		$productListToGetMorePhoto = [];

		$iblockElementList = \CIBlockElement::GetList(
			[],
			[
				'ID' => $productIdList,
			],
			false,
			false,
			[
				'ID',
				'IBLOCK_ID',
				ProductPicture::CODE_PREVIEW_PICTURE,
				ProductPicture::CODE_DETAIL_PICTURE,
			],
		);

		while ($product = $iblockElementList->Fetch())
		{
			$productId = (int)$product['ID'];

			if (!empty($product[ProductPicture::CODE_PREVIEW_PICTURE]))
			{
				$result[$productId] = (int)$product[ProductPicture::CODE_PREVIEW_PICTURE];
			}
			else if (!empty($product[ProductPicture::CODE_DETAIL_PICTURE]))
			{
				$result[$productId] = (int)$product[ProductPicture::CODE_DETAIL_PICTURE];
			}
			else
			{
				$productIblockId = (int)$product['IBLOCK_ID'];
				$productListToGetMorePhoto[$productIblockId] ??= [];
				$productListToGetMorePhoto[$productIblockId][] = $productId;
			}
		}

		if (!empty($productListToGetMorePhoto))
		{
			$productMorePhoto = ProductPicture::getMorePhoto($productListToGetMorePhoto);
			foreach ($productMorePhoto as $productId => $productPhotos)
			{
				$result[$productId] = array_shift($productPhotos);
			}
		}

		return $result;
	}

	private static function getMorePhoto(array $productList): array
	{
		$result = [];

		foreach ($productList as $iblockId => $productIdList)
		{
			$rawPropertyResult = [];
			\CIBlockElement::GetPropertyValuesArray(
				$rawPropertyResult,
				$iblockId,
				[
					'ID' => $productIdList,
				],
				[
					'CODE' => ProductPicture::CODE_MORE_PHOTO,
				],
				[
					'GET_RAW_DATA' => 'Y',
				],
			);

			foreach ($rawPropertyResult as $productId => $productProperty)
			{
				$productId = (int)$productId;

				if (!empty($productProperty[ProductPicture::CODE_MORE_PHOTO]))
				{
					$photoValues = $productProperty[ProductPicture::CODE_MORE_PHOTO]['VALUE'];

					if (empty($photoValues))
					{
						continue;
					}

					Collection::normalizeArrayValuesByInt($photoValues, false);

					$result[$productId] = $photoValues;
				}
			}
		}

		return $result;
	}
}
