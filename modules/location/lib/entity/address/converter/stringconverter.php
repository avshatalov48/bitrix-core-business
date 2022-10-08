<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Format;
use Bitrix\Main\ArgumentOutOfRangeException;

/**
 * Class StringConverter
 * @package Bitrix\Location\Entity\Address\Converter
 */
final class StringConverter
{
	public const STRATEGY_TYPE_TEMPLATE = 'template';
	public const STRATEGY_TYPE_TEMPLATE_COMMA = 'template_comma';
	public const STRATEGY_TYPE_TEMPLATE_NL = 'template_nl';
	public const STRATEGY_TYPE_TEMPLATE_BR = 'template_br';
	public const STRATEGY_TYPE_FIELD_SORT = 'field_sort';
	public const STRATEGY_TYPE_FIELD_TYPE = 'field_type';

	public const CONTENT_TYPE_HTML = 'html';
	public const CONTENT_TYPE_TEXT = 'text';

	/**
	 * Convert address to string with given format
	 *
	 * @param Address $address
	 * @param Format $format
	 * @param string $strategyType
	 * @param string $contentType
	 * @return string
	 * @throws ArgumentOutOfRangeException
	 */
	public static function convertToString(Address $address, Format $format, string $strategyType, string $contentType): string
	{
		if($strategyType === self::STRATEGY_TYPE_TEMPLATE
			|| $strategyType === self::STRATEGY_TYPE_TEMPLATE_COMMA
			|| $strategyType === self::STRATEGY_TYPE_TEMPLATE_NL
			|| $strategyType === self::STRATEGY_TYPE_TEMPLATE_BR
		)
		{
			$delimiter = null;

			switch ($strategyType)
			{
				case self::STRATEGY_TYPE_TEMPLATE_COMMA:
					$delimiter = ', ';
					break;
				case self::STRATEGY_TYPE_TEMPLATE_NL:
					$delimiter = "\n";
					break;
				case self::STRATEGY_TYPE_TEMPLATE_BR:
					$delimiter = '<br />';
					break;
			}

			$result = self::convertToStringTemplate(
				$address,
				$format->getTemplate(),
				$contentType,
				$delimiter,
				$format
			);
		}
		elseif($strategyType === self::STRATEGY_TYPE_FIELD_SORT)
		{
			$result = self::convertToStringByField($address, $format, $contentType);
		}
		elseif($strategyType === self::STRATEGY_TYPE_FIELD_TYPE)
		{
			$fieldSorter = static function(Format\Field $a, Format\Field $b): int
			{
				$aType = $a->getType();
				$bType = $b->getType();

				if($aType === 0)
				{
					$result = -1;
				}
				elseif ($bType === 0)
				{
					$result = 1;
				}
				else
				{
					$result = $aType - $bType;
				}

				return $result;
			};

			$result = self::convertToStringByField($address, $format, $contentType, $fieldSorter);
		}
		else
		{
			throw new ArgumentOutOfRangeException('strategyType');
		}

		return $result;
	}

	/**
	 * Convert if format has template
	 *
	 * @param Address $address
	 * @param Format\Template $template
	 * @param string $contentType
	 * @param string|null $delimiter
	 * @param Format|null $format
	 * @return string
	 */
	public static function convertToStringTemplate(
		Address $address,
		Format\Template $template,
		string $contentType,
		string $delimiter = null,
		Format $format = null
	): string
	{
		$needHtmlEncode = ($contentType === self::CONTENT_TYPE_HTML);

		if ($delimiter === null)
		{
			$delimiter = $needHtmlEncode ? '<br />' : "\n";
		}

		$templateConverter = new StringTemplateConverter(
			$template->getTemplate(),
			$delimiter,
			$needHtmlEncode,
			$format
		);

		return $templateConverter->convert($address);
	}

	/**
	 * Convert if format has not template
	 *
	 * @param Address $address
	 * @param Format $format
	 * @param string $contentType
	 * @param callable|null $fieldSorter
	 * @return string
	 */
	protected static function convertToStringByField(Address $address, Format $format, string $contentType, callable $fieldSorter = null): string
	{
		$result = '';
		$fields = array_values($format->getFieldCollection()->getItems());

		if($fieldSorter !== null)
		{
			usort($fields, $fieldSorter);
		}

		foreach($fields as $field)
		{
			$fieldValue = $address->getFieldValue($field->getType());

			if($fieldValue === null)
			{
				continue;
			}

			if($contentType === self::CONTENT_TYPE_HTML)
			{
				$fieldValue = htmlspecialcharsbx($fieldValue);
			}

			if($result !== '')
			{
				$result .= $format->getDelimiter();
			}

			$result .= $fieldValue;
		}

		return $result;
	}
}