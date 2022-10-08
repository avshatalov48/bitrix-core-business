<?php

namespace Bitrix\Location\Entity\Format\Converter;

use Bitrix\Location\Entity\Format;
use Bitrix\Location\Entity\Format\Field;

/**
 * Class ArrayConverter
 * @package Bitrix\Location\Entity\Format\Converter
 * @internal
 */
final class ArrayConverter
{
	/**
	 * Convert Format to Array
	 *
	 * @param Format $format
	 * @return array
	 */
	public static function convertToArray(Format $format): array
	{
		return [
			'code' => $format->getCode(),
			'name' => $format->getName(),
			'description' => $format->getDescription(),
			'delimiter' => $format->getDelimiter(),
			'languageId' => $format->getLanguageId(),
			'templateCollection' => self::convertTemplateCollectionToArray(
				$format->getTemplateCollection()
			),
			'fieldCollection' => self::convertFieldCollectionToArray(
				$format->getFieldCollection()
			),
			'fieldForUnRecognized' => $format->getFieldForUnRecognized()
		];
	}

	/**
	 * @param Format\TemplateCollection $templateCollection
	 * @return array
	 */
	private static function convertTemplateCollectionToArray(Format\TemplateCollection $templateCollection): array
	{
		$result = [];

		/** @var Format\Template $template */
		foreach ($templateCollection as $template)
		{
			$result[$template->getType()] = $template->getTemplate();
		}

		return $result;
	}

	/**
	 * @param Format\FieldCollection $fieldCollection
	 * @return array
	 */
	private static function convertFieldCollectionToArray(Format\FieldCollection $fieldCollection): array
	{
		$result = [];

		/** @var Field $field */
		foreach ($fieldCollection as $field)
		{
			$result[] = [
				'sort' => $field->getSort(),
				'type' => $field->getType(),
				'name' => $field->getName(),
				'description' => $field->getDescription()
			];
		}

		return $result;
	}

	/**
	 * Convert Array to Format
	 *
	 * @param array $data
	 * @param string $languageId
	 * @return Format
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertFromArray(array $data, string $languageId): Format
	{
		$result = (new Format($languageId))
			->setName((string)$data['name'])
			->setDescription((string)$data['description'])
			->setDelimiter((string)$data['delimiter'])
			->setCode((string)$data['code'])
			->setFieldForUnRecognized($data['fieldForUnRecognized']);

		foreach ($data['fieldCollection'] as $field)
		{
			$result->getFieldCollection()->addItem(
				(new Format\Field((int)$field['type']))
					->setName((string)$field['name'])
					->setDescription((string)$field['description'])
					->setSort((int)$field['sort'])
			);
		}

		foreach ($data['templateCollection'] as $type => $template)
		{
			$result->getTemplateCollection()->addItem(
				new Format\Template($type, $template)
			);
		}

		return $result;
	}
}