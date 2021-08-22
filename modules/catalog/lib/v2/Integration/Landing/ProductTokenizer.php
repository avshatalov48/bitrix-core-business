<?php

namespace Bitrix\Catalog\v2\Integration\Landing;

use Bitrix\Main\Request;
use Bitrix\Main\Web\Uri;

final class ProductTokenizer
{
	private const PRODUCT_TOKEN = 'productToken';

	public static function encode(array $productIds): string
	{
		$productIds = array_unique(array_filter(array_map('intval', $productIds)));
		if (empty($productIds))
		{
			return '';
		}

		return self::encodeBase64Url(implode(',', $productIds));
	}

	public static function mixIntoUri(Uri $uri, array $productIds): Uri
	{
		$token = self::encode($productIds);
		if ($token === '')
		{
			return $uri;
		}

		return $uri->addParams([
			self::PRODUCT_TOKEN => self::encode($productIds),
		]);
	}

	public static function decode(string $token): array
	{
		if ($token === '')
		{
			return [];
		}

		$productIds = explode(',', self::decodeBase64Url($token));
		if (!is_array($productIds) || empty($productIds))
		{
			return [];
		}

		return array_unique(array_filter(array_map('intval', $productIds)));
	}

	public static function decodeFromRequest(Request $request): ?array
	{
		$token = $request->get(self::PRODUCT_TOKEN);
		if ($token !== null && is_string($token))
		{
			return self::decode($token);
		}

		return null;
	}

	public static function encodeBase64Url(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public static function decodeBase64Url(string $data): string
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
}