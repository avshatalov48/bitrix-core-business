<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model\Helper;

use Bitrix\Main\Text\Emoji;

/**
 * Class EmojiDataProcessor provides methods to encode and decode emoji data.
 */
class EmojiDataProcessor
{
	/**
	 * Recursively decodes emoji entities in a given array.
	 *
	 * @param array|null $dataArray The input array containing strings with encoded emoji entities.
	 *
	 * @return array|null Decoded array with emoji entities replaced by their corresponding characters, or null.
	 */
	public static function decode(?array $dataArray): ?array
	{
		if (!is_array($dataArray))
		{
			return null;
		}

		foreach ($dataArray as $key => $value)
		{
			if (is_string($value))
			{
				$dataArray[$key] = Emoji::decode($value);
			}
			elseif (is_array($value))
			{
				$dataArray[$key] = self::decode($value);
			}
		}

		return $dataArray;
	}

	/**
	 * Encodes emoji characters in a serialized data string.
	 *
	 * @param string $serializedData Serialized data containing strings with emoji characters.
	 *
	 * @return string Serialized data with emoji characters replaced by their encoded entities.
	 */
	public static function encode(string $serializedData): string
	{
		$dataArray = unserialize($serializedData, ['allowed_classes' => false]);
		if ($dataArray === false)
		{
			return $serializedData;
		}
		$encodedDataArray = self::encodeArray($dataArray);

		return serialize($encodedDataArray);
	}

	/**
	 * Recursively encodes emoji characters in a given array.
	 *
	 * @param array|null $dataArray The input array containing strings with emoji characters.
	 *
	 * @return array|null Encoded array with emoji characters replaced by their encoded entities, or null.
	 */
	private static function encodeArray(?array $dataArray): ?array
	{
		if (!is_array($dataArray))
		{
			return null;
		}

		foreach ($dataArray as $key => $value)
		{
			if (is_string($value))
			{
				$dataArray[$key] = Emoji::encode($value);
			}
			elseif (is_array($value))
			{
				$dataArray[$key] = self::encodeArray($value);
			}
		}

		return $dataArray;
	}
}