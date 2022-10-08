<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Catalog\Component\SkuTree;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Integration\Iblock\BrandProperty;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Uri;
use Bitrix\SalesCenter\Integration\LandingManager;

use function GuzzleHttp\Psr7\str;

class FacebookProductProcessor
{
	public const RETAILER_ID_PREFIX = 'bitrix24-product-';

	/** @var \Bitrix\Catalog\Component\SkuTree */
	private ?SkuTree $skuTree = null;

	public function __construct(IblockInfo $iblockInfo)
	{
		if ($iblockInfo->canHaveSku())
		{
			$this->skuTree = new SkuTree($iblockInfo);
		}
	}

	public function validate(BaseSku $sku): Result
	{
		$result = new Result();

		if ($sku->isNew())
		{
			$result->addError(new Error(sprintf(
				'New product "%s" must be saved.', $sku->getName()
			)));
		}

		$brandProperty = $sku->getParent()->getPropertyCollection()->findByCode(BrandProperty::PROPERTY_CODE);
		if (!$brandProperty || $brandProperty->getPropertyValueCollection()->isEmpty())
		{
			$result->addError(new Error(
				sprintf('Product "%s"(%s) has no brands.', $sku->getParent()->getName(), $sku->getParent()->getId() ?? 'new'),
				0,
				[
					'productId' => $sku->getParent()->getId(),
					'field' => 'brand',
				]
			));
		}

		if ($sku->getFrontImageCollection()->isEmpty())
		{
			$result->addError(new Error(
				sprintf('Product "%s"(%s) has no images.', $sku->getName(), $sku->getId() ?? 'new'),
				0,
				[
					'productId' => $sku->getId(),
					'field' => 'image',
				]
			));
		}

		if (!$sku->getPriceCollection()->hasBasePrice())
		{
			$result->addError(new Error(
				sprintf('Product "%s"(%s) has no prices.', $sku->getName(), $sku->getId() ?? 'new'),
				0,
				[
					'productId' => $sku->getId(),
					'field' => 'price',
				]
			));
		}

		return $result;
	}

	public function prepare(BaseSku $sku): Result
	{
		$result = new Result();

		if ($sku->isSimple())
		{
			$result->setData($this->prepareSimpleProduct($sku));
		}
		else
		{
			$result->setData($this->prepareVariation($sku));
		}

		return $result;
	}

	private function prepareSimpleProduct(BaseSku $sku): array
	{
		$fields = $this->getBaseFields($sku);

		return [$sku->getId() => $fields];
	}

	private function prepareVariation(BaseSku $sku): array
	{
		$fields = $this->getBaseFields($sku);
		$skuParent = $sku->getParent();

		if ($this->skuTree && $skuParent)
		{
			$skuTree = $this->skuTree->load([$skuParent->getId()])[$skuParent->getId()];
			$skuFields = $this->getSkuFields($sku, $skuTree);
			if (!empty($skuFields))
			{
				$fields['data'] += $skuFields;
			}
		}

		return [$sku->getId() => $fields];
	}

	public function getEntityRetailerId(string $id): string
	{
		return self::RETAILER_ID_PREFIX . $id;
	}

	public function getProductIdByRetailerId(string $retailerId): string
	{
		return str_replace(self::RETAILER_ID_PREFIX, '', $retailerId);
	}

	private function getProductRetailerId(BaseIblockElementEntity $entity): string
	{
		if ($parent = $entity->getParent())
		{
			return $this->getEntityRetailerId($parent->getId());
		}

		return $this->getEntityRetailerId($entity->getId());
	}

	private function getBaseFields(BaseSku $variation): array
	{
		return [
			'method' => 'UPDATE',
			'retailer_id' => self::RETAILER_ID_PREFIX . $variation->getId(),
			'data' => [
				'name' => $this->getProductName($variation),
				'description' => $this->getProductDescription($variation),
				'retailer_product_group_id' => $this->getProductRetailerId($variation),
				'url' => $this->getProductUrl($variation),
				'image_url' => $this->getProductImageUrl($variation),
				// 'category' => 't-shirts',
				// todo GTIN/MPN/brand
				'brand' => $this->getProductBrand($variation),
				'condition' => 'new',
				'availability' => 'in stock',
				'price' => $this->getProductPrice($variation),
				'currency' => $this->getProductCurrency($variation),
			],
		];
	}

	private function getSkuFields(BaseSku $variation, array $skuTree): array
	{
		$offerTree = $this->getOfferTree($variation->getId(), $skuTree);
		if (empty($offerTree) || empty($offerTree['DISPLAY_PROPERTIES']))
		{
			return [];
		}

		$fields = [];

		[$color, $size, $custom] = $this->extractVariants($offerTree);

		if (!empty($color))
		{
			$fields['color'] = $color;
		}

		if (!empty($size))
		{
			$fields['size'] = $size;
		}

		if (!empty($custom))
		{
			$fields['additional_variant_attributes'] = $custom;
		}

		return $fields;
	}

	private function getOfferTree(int $variationId, array $skuTree): ?array
	{
		foreach ($skuTree['OFFERS'] as $offer)
		{
			if ((int)$offer['ID'] === $variationId)
			{
				return $offer;
			}
		}

		return null;
	}

	private function extractVariants(array $offerTree): array
	{
		$color = null;
		$size = null;
		$custom = [];

		foreach ($offerTree['DISPLAY_PROPERTIES'] as $code => $property)
		{
			if ($color === null && $code === 'COLOR_REF')
			{
				$color = $property['DISPLAY_VALUE'];
			}
			elseif ($size === null && ($code === 'SIZES_CLOTHES' || $code === 'SIZES_SHOES'))
			{
				$size = $property['DISPLAY_VALUE'];
			}
			else
			{
				$custom[$property['NAME']] = $property['DISPLAY_VALUE'];
			}
		}

		return [$color, $size, $custom];
	}

	private function getProductName(BaseSku $variation): string
	{
		return $this->truncateSentence($variation->getName(), 100);
	}

	private function getProductDescription(BaseSku $variation): string
	{
		$description = $variation->getField('DETAIL_TEXT');
		if ($description)
		{
			if ($variation->getField('DETAIL_TEXT_TYPE') === 'html')
			{
				$description = HTMLToTxt($description);
			}

			return $this->truncateSentence($description, 5000);
		}

		$description = $variation->getField('PREVIEW_TEXT');
		if ($description)
		{
			if ($variation->getField('PREVIEW_TEXT_TYPE') === 'html')
			{
				$description = HTMLToTxt($description);
			}

			return $this->truncateSentence($description, 5000);
		}

		return $variation->getName();
	}

	private function getProductImageUrl(BaseSku $product): string
	{
		/** @var \Bitrix\Catalog\v2\Image\BaseImage $image */
		$image = $product->getFrontImageCollection()->getFirst();
		if ($image && $image->getSource())
		{
			return $this->getCurrentUri($image->getSource());
		}

		return '';
	}

	private function getCurrentUri(string $path): string
	{
		if (strpos($path, 'http') === 0)
		{
			return $path;
		}

		$server = Context::getCurrent()->getServer();
		$request = Context::getCurrent()->getRequest();

		$uri = $request->isHttps() ? 'https://' : 'http://';
		$uri .= $server->getServerName();
		$uri .= (
			(int)$server->getServerPort() === 80
			|| ($server->get('HTTPS') && (int)$server->getServerPort() === 443)
		)
			? ''
			: ':' . $server->getServerPort();
		$uri .= $path;

		return (new Uri($uri))->getUri();
	}

	private function getProductPrice(BaseSku $entity): string
	{
		$basePrice = $entity->getPriceCollection()->findBasePrice();
		if ($basePrice)
		{
			return $basePrice->getPrice() * 100;
		}

		return 0;
	}

	private function getProductCurrency(BaseSku $entity): string
	{
		$basePrice = $entity->getPriceCollection()->findBasePrice();
		if ($basePrice)
		{
			return $basePrice->getCurrency();
		}

		return \CCrmCurrency::GetDefaultCurrencyID();
	}

	private function truncateSentence(string $sentence, int $limit): string
	{
		if ($limit <= 0)
		{
			return $sentence;
		}

		if (mb_strlen($sentence) <= $limit)
		{
			return $sentence;
		}

		return mb_substr($sentence, 0, mb_strrpos(mb_substr($sentence, 0, $limit + 1), ' '));
	}

	private function getProductUrl(BaseSku $variation): string
	{
		if (
			!Loader::includeModule('salescenter')
			|| !Loader::includeModule('landing'))
		{
			return '';
		}

		$product = $variation->getParent();
		if ($product)
		{
			$siteUrlInfo = LandingManager::getInstance()->getCollectionPublicUrlInfo();
			$siteUrl = $siteUrlInfo['url'] ?? null;
			$productDetailUrl = \Bitrix\Landing\PublicAction\Utils::getIblockURL($product->getId(), 'detail');

			if ($siteUrl && $productDetailUrl)
			{
				return str_replace('#system_catalog', $siteUrl, $productDetailUrl);
			}
		}

		return '';
	}

	private function getProductBrand(BaseSku $variation): string
	{
		/** @var BaseProduct $product */
		$product = $variation->getParent();
		if ($product)
		{
			$brandProperty = $product->getPropertyCollection()->findByCode(BrandProperty::PROPERTY_CODE);
			if ($brandProperty)
			{
				$names = $this->getBrandNames($brandProperty);

				return implode(', ', $names);
			}
		}

		return '';
	}

	private function getBrandNames(Property $property): array
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return [];
		}

		$userTypeSettings = $property->getSetting('USER_TYPE_SETTINGS');
		$tableName = $userTypeSettings['TABLE_NAME'] ?? '';

		if (empty($tableName))
		{
			return [];
		}

		$brandValues = $property->getPropertyValueCollection()->toArray();
		if (empty($brandValues))
		{
			return [];
		}

		$brandValues = array_column($brandValues, null, 'VALUE');

		return array_intersect_key($this->loadBrandNamesMap($tableName), $brandValues);
	}

	private function loadBrandNamesMap(string $tableName): array
	{
		static $nameMap = [];

		if (!isset($nameMap[$tableName]))
		{
			$tableMap = [];

			$highLoadBlock = HighloadBlockTable::getList([
				'filter' => ['=TABLE_NAME' => $tableName],
			])
				->fetch()
			;
			if (!empty($highLoadBlock))
			{
				$entity = HighloadBlockTable::compileEntity($highLoadBlock);
				$entityDataClass = $entity->getDataClass();
				$directoryData = $entityDataClass::getList();
				while ($element = $directoryData->fetch())
				{
					$tableMap[$element['UF_XML_ID']] = $element['UF_NAME'];
				}
			}

			$nameMap[$tableName] = $tableMap;
		}

		return $nameMap[$tableName];
	}
}