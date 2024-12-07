<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Address\FieldType;
use Bitrix\Location\Entity\Format;

final class StringTemplateConverter
{
	private const STR_DELIMITER_PLACEHOLDER = '#S#';
	private const REGEX_COMMA_AMONG_EMPTY_SPACE = '\\s*,\\s*';
	private const REGEX_GROUP_DELIMITER = '(\\"([^"\\\\]*|\\\\"|\\\\\\\\|\\\\)*")';
	private const REGEX_GROUP_FIELD_TEXT = self::REGEX_GROUP_DELIMITER;
	private const REGEX_GROUP_FIELD_NAME = '([a-zA-Z][a-zA-Z_0-9]*(:(NU|UN|N|U))?)';
	private const REGEX_GROUP_FIELD_LIST_END = '\\s*\\]';
	private const REGEX_GROUP_END = self::REGEX_GROUP_FIELD_LIST_END;
	private const REGEX_PART_FROM_DELIMITER_TO_FIELD_LIST = '\\s*,\\s*\\[\\s*';
	private const REGEX_GROUP_PART_BEFORE_FIELDS =
		'(([^\\[\\\\]|\\\\\\[|\\\\\\\\)*)(\\[\\s*)("([^"\\\\]*|\\\\"|\\\\\\\\|\\\\)*")\\s*,\\s*\\[\\s*';

	private const ERR_PARSE_GROUP_START_POSITION = 1100;
	private const ERR_PARSE_GROUP_START = 1110;
	private const ERR_PARSE_GROUP_DELIMITER = 1120;
	private const ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST = 1130;
	private const ERR_PARSE_GROUP_FIELD_TEXT = 1140;
	private const ERR_PARSE_GROUP_FIELD_NAME = 1150;
	private const ERR_PARSE_GROUP_FIELD = 1160;
	private const ERR_PARSE_GROUP_FIELD_LIST = 1170;
	private const ERR_PARSE_GROUP_FIELD_LIST_DELIMITER = 1180;
	private const ERR_PARSE_GROUP_FIELD_LIST_END = 1190;
	private const ERR_PARSE_GROUP_END = 1200;
	private const ERR_PARSE_GROUP = 1210;

	/** @var string */
	private $template = '';

	/** @var string $delimiter */
	private  $delimiter;

	/** @var bool $htmlEncode */
	private  $htmlEncode;

	/** @var Format|null */
	private $format;

	public function __construct(string $template, string $delimiter, bool $htmlEncode, Format $format = null)
	{
		$this->template = $template;
		$this->delimiter = $delimiter;
		$this->htmlEncode = $htmlEncode;
		$this->format = $format;
	}

	private function getErrorCodes(): array
	{
		static $errorMap = null;

		if ($errorMap !== null)
		{
			return $errorMap;
		}

		$errorMap = [];
		$refClass = new \ReflectionClass(__CLASS__);
		foreach ($refClass->getConstants() as $name => $value)
		{
			if (substr($name, 0, 4) === 'ERR_')
			{
				$errorMap[constant("self::{$name}")] = $name;
			}
		}

		return $errorMap;
	}

	private function getErrorsText(array $context): string
	{
		$result = '';

		$errCodes = $this->getErrorCodes();
		foreach ($context['error']['errors'] as $errInfo)
		{
			$result .= "Error: {$errInfo['position']}, {$errCodes[$errInfo['code']]}" . PHP_EOL;
			if (!empty($errInfo['info']) && is_array($errInfo['info']))
			{
				$needHeader = true;
				foreach($errInfo['info'] as $paramName => $paramValue)
				{
					$needPrint = false;
					if (is_string($paramValue))
					{
						$paramValue = "\"{$paramValue}\"";
						$needPrint = true;
					}
					elseif (is_int($paramValue) || is_double($paramValue))
					{
						$needPrint = true;
					}
					elseif (is_bool($paramValue))
					{
						$paramValue = $paramValue ? 'true' : 'false';
						$needPrint = true;
					}
					elseif (is_array($paramValue))
					{
						$paramValue = '[...]';
						$needPrint = true;
					}
					elseif (is_object($paramValue))
					{
						$paramValue = '{...}';
						$needPrint = true;
					}
					if ($needPrint)
					{
						if ($needHeader)
						{
							$result .= "  Error info:" . PHP_EOL;
							$needHeader = false;
						}
						$result .= "    {$paramName}: {$paramValue}" . PHP_EOL;
					}
				}
			}
		}

		$result .= 'Template: "' . str_replace(["\n", "\""], ['\\n', '\\"'], $context['template']) . '"'
			. PHP_EOL . PHP_EOL;

		return $result;
	}

	private function createContext(): array
	{
		return [
			'level' => 0,
			'position' => 0,
			'template' => '',
			'address' => null,
			'info' => [],
			'hasError' => false,
			'error' => [
				'code' => 0,
				'position' => 0,
				'errors' => [],
				'info' => [],
			],
		];
	}

	private function clearContextInfo(array $context): array
	{
		$context['info'] = [];

		return $context;
	}

	private function clearContextError(array $context): array
	{
		$context['hasError'] = false;
		$context['error'] = [
			'code' => 0,
			'position' => 0,
			'errors' => [],
			'info' => [],
		];

		return $context;
	}

	private function clearContextInfoAndError(array $context): array
	{
		$context = $this->clearContextInfo($context);
		$context = $this->clearContextError($context);

		return $context;
	}

	private function unescapeText(string $text): string
	{
		$result = '';

		$length = strlen($text);

		for ($i = 0; $i < $length; $i++)
		{
			if ($text[$i] === '\\')
			{
				if (($length - $i) > 1)
				{
					$result .= $text[++$i];
				}
			}
			else
			{
				$result .= $text[$i];
			}
		}

		return $result;
	}

	private function parseGroupDelimiter(array $context): array
	{
		// Capturing the group's separator
		$delimiterStartPosition = $context['position'];
		//                [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^^
		if (preg_match(
				'/' . self::REGEX_GROUP_DELIMITER . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$delimiterStartPosition
			)
			&& $matches[0][1] === $delimiterStartPosition
		)
		{
			$context['info'] = [
				'position' => $delimiterStartPosition,
				'end' => $delimiterStartPosition + strlen($matches[0][0]),
				'value' => $this->unescapeText(
					substr(
						$context['template'],
						$delimiterStartPosition + 1,
						strlen($matches[1][0]) - 2
					)
				),
			];
			$context['position'] = $context['info']['end'];
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_DELIMITER, $delimiterStartPosition);
		}

		return $context;
	}

	private function parseFieldText(array $context): array
	{
		$textBlockStartPosition = $context['position'];
		$matches = null;
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                         ^^^^^^
		if (preg_match(
				'/' . self::REGEX_GROUP_FIELD_TEXT . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $textBlockStartPosition
		)
		{
			$context['info'] = [
				'type' => 'text',
				'position' => $textBlockStartPosition,
				'end' => $textBlockStartPosition + strlen($matches[0][0]),
				'value' => $this->unescapeText(
					substr(
						$context['template'],
						$textBlockStartPosition + 1,
						strlen($matches[1][0]) - 2
					)
				),
			];
			$context['position'] = $context['info']['end'];
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_FIELD_TEXT, $textBlockStartPosition);
		}

		return $context;
	}

	private function splitFieldName(string $fieldName): array
	{
		$fieldParts = explode(':', $fieldName);
		$fieldName = $fieldParts[0] ?? '';
		$fieldModifiers = $fieldParts[1] ?? '';
		if (!is_string($fieldModifiers))
		{
			$fieldModifiers = '';
		}

		return [$fieldName, $fieldModifiers];
	}

	/**
	 * @param string $fieldType
	 * @return bool
	 */
	private function isTemplateForFieldExists(string $fieldName): bool
	{
		return $this->format && $this->format->getTemplate($fieldName) !== null;
	}

	/**
	 * @param string $fieldName
	 * @param Address $address
	 * @return string|null
	 */
	private function getFieldValueByTemplate(string $fieldName, Address $address): ?string
	{
		if(!$this->isTemplateForFieldExists($fieldName))
		{
			return null;
		}

		$template = $this->format->getTemplate($fieldName)->getTemplate();
		$templateConverter = new StringTemplateConverter(
			$template,
			$this->delimiter,
			$this->htmlEncode,
			$this->format
		);

		return $templateConverter->convert($address);
	}

	private function getAlterFieldValue(Address $address, int $fieldType): string
	{
		$localityValue = $address->getFieldValue(FieldType::LOCALITY);
		$localityValue = is_string($localityValue) ? $localityValue : '';
		$result = $address->getFieldValue($fieldType);
		$result = is_string($result) ? $result : '';
		if ($result !== '' && $localityValue !== '')
		{
			$localityValueUpper = mb_strtoupper($localityValue);
			$localityValueUpperLength = mb_strlen($localityValueUpper);
			$targetValueUpper = mb_strtoupper($result);
			$targetValueUpperLength = mb_strlen($targetValueUpper);
			if ($targetValueUpperLength >= $localityValueUpperLength)
			{
				$targetValueSubstr = mb_substr(
					$targetValueUpper,
					$targetValueUpperLength - $localityValueUpperLength
				);
				if ($localityValueUpper === $targetValueSubstr)
				{
					$result = '';
				}
			}
		}

		return $result;
	}

	private function getAddressFieldValue(Address $address, string $fieldName, string $fieldModifiers): string
	{
		$result = '';

		if (FieldType::isTypeExist($fieldName))
		{
			$addressFieldType = constant(FieldType::class.'::'.$fieldName);

			if ($fieldName === 'ADM_LEVEL_1' || $fieldName === 'ADM_LEVEL_2')
			{
				// Scratch "Province & Region by Locality"
				$result = $this->getAlterFieldValue($address, $addressFieldType);
			}
			else
			{
				$result = $address->getFieldValue($addressFieldType);
			}

			if ($result === null)
			{
				$result = $this->getFieldValueByTemplate($fieldName, $address);
			}
		}
		if (!is_string($result))
		{
			$result = '';
		}
		if ($result !== '')
		{
			if (strpos($fieldModifiers, 'N') !== false)
			{
				$result = str_replace(["\r\n", "\n", "\r"], '#S#', $result);
			}
			if (strpos($fieldModifiers, 'U') !== false)
			{
				$result = mb_strtoupper($result);
			}
		}

		return $result;
	}

	private function parseFieldName(array $context): array
	{
		$fieldNameStartPosition = $context['position'];
		$matches = null;
		//          [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for  ^^^^^^^^^^^^^^^^
		if ($context['address'] instanceof Address
			&& preg_match(
				'/' . self::REGEX_GROUP_FIELD_NAME . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $fieldNameStartPosition
		)
		{
			$context['position'] = $fieldNameStartPosition + strlen($matches[0][0]);
			list($fieldName, $fieldModifiers) = $this->splitFieldName($matches[0][0]);
			$fieldValue = $this->getAddressFieldValue($context['address'], $fieldName, $fieldModifiers);
			$context['info'] = [
				'type' => 'field',
				'position' => $fieldNameStartPosition,
				'end' => $context['position'],
				'modifiers' => $fieldModifiers,
				'name' => $fieldName,
				'value' => $fieldValue,
			];
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_FIELD_NAME, $fieldNameStartPosition);
		}

		return $context;
	}

	private function parseFieldListDelimiter(array $context): array
	{
		$markerStartPosition = $context['position'];
		$matches = null;
		// [", ", [ADDRESS_LINE_1:N , ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for         ^^^
		if (preg_match(
				'/' . self::REGEX_COMMA_AMONG_EMPTY_SPACE . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $markerStartPosition
		)
		{
			$context['position'] = $markerStartPosition + strlen($matches[0][0]);
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_FIELD_LIST_DELIMITER, $markerStartPosition);
		}

		return $context;
	}

	private function parseFieldListEnd(array $context): array
	{
		$markerStartPosition = $context['position'];
		$matches = null;
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                                                    ^
		if (preg_match(
				'/' . self::REGEX_GROUP_FIELD_LIST_END . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $markerStartPosition
		)
		{
			$context['position'] = $markerStartPosition + strlen($matches[0][0]);
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_FIELD_LIST_END, $markerStartPosition);
		}

		return $context;
	}

	private function parseField(array $context): array
	{
		$fieldInfo = [];
		$fieldStartPosition = $context['position'];
		$errors = [];

		// Checking for the presence of a text block
		$context = $this->parseFieldText($context);

		if ($context['hasError'])
		{
			$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
			$context = $this->clearContextInfoAndError($context);
			// Checking for the presence of a field name
			$context = $this->parseFieldName($context);
		}

		if ($context['hasError'])
		{
			$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
			$context = $this->clearContextInfoAndError($context);
			// Checking for the presence of a nested group
			$context = $this->parseGroup($context);
			if ($context['hasError'])
			{
				$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
			}
			else if ($context['info']['position'] > $fieldStartPosition)
			{
				// Group found beyond the expected position
				$this->addContextError($context, self::ERR_PARSE_GROUP_START_POSITION, $fieldStartPosition);
				$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
			}
		}

		if (!$context['hasError'])
		{
			$fieldInfo = $context['info'];
			$fieldInfo['isFieldListEnd'] = false;
			$context = $this->clearContextInfo($context);

			// Checking for the presence of a field separator
			$context = $this->parseFieldListDelimiter($context);

			if ($context['hasError'])
			{
				$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
				$context = $this->clearContextInfoAndError($context);
				// Checking for the presence of the end sign of the field list
				$context = $this->parseFieldListEnd($context);
				if ($context['hasError'])
				{
					$this->unshiftError($errors, $context['error']['code'], $context['error']['position']);
				}
				else
				{
					$fieldInfo['isFieldListEnd'] = true;
				}
			}
		}

		if ($context['hasError'])
		{
			$this->unshiftError($errors,  self::ERR_PARSE_GROUP_FIELD, $fieldStartPosition);
			$this->addContextErrors($context, $errors);
		}
		else
		{
			$context['info'] = $fieldInfo;
		}

		return $context;
	}

	private function parseGroupFieldListStart(array $context): array
	{
		$fieldListStartPosition = $context['position'];
		$fieldValues = [];
		$matches = null;
		//            [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^
		if (preg_match(
				'/' . self::REGEX_PART_FROM_DELIMITER_TO_FIELD_LIST . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $fieldListStartPosition
		)
		{
			$context['position'] = $matches[0][1] + strlen($matches[0][0]);
			$isFieldListEnd = false;
			while (!($context['hasError'] || $isFieldListEnd))
			{
				$context = $this->parseField($context);
				if (!$context['hasError'])
				{
					$isFieldListEnd = (
						isset($context['info']['isFieldListEnd'])
						&& $context['info']['isFieldListEnd']
					);
					if ($context['info']['value'] !== '')
					{
						$fieldValues[] = $context['info']['value'];
					}
					$context = $this->clearContextInfo($context);
				}
			}

			if (!$context['hasError'])
			{
				$context['info'] = ['fieldValues' => $fieldValues];
			}
		}
		else
		{
			$this->addContextError(
				$context,
				self::ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST,
				$fieldListStartPosition
			);
		}

		if ($context['hasError'])
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_FIELD_LIST, $fieldListStartPosition);
		}

		return $context;
	}

	private function parseGroupStart(array $context): array
	{
		$matches = null;
		//                 [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^^^^^^
		if (preg_match(
				'/' . self::REGEX_GROUP_PART_BEFORE_FIELDS . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
		)
		{
			$context['info']['groupStartPosition'] = $matches[3][1];
			$context['info']['groupDelimiterStartPosition'] = $matches[4][1];
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_START, $context['position']);
		}

		return $context;
	}

	private function parseGroupEnd(array $context): array
	{
		$markerStartPosition = $context['position'];
		$matches = null;
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                                                     ^
		if (preg_match(
				'/' . self::REGEX_GROUP_END . '/msu',
				$context['template'],
				$matches,
				PREG_OFFSET_CAPTURE,
				$context['position']
			)
			&& $matches[0][1] === $markerStartPosition
		)
		{
			$context['position'] = $markerStartPosition + strlen($matches[0][0]);
		}
		else
		{
			$this->addContextError($context, self::ERR_PARSE_GROUP_END, $markerStartPosition);
		}

		return $context;
	}

	private function parseGroup(array $context): array
	{
		$startSearchPosition = $context['position'];
		$groupStartPosition = 0;
		$delimiterValue = '';
		$fieldValues = [];

		$context['level']++;

		// Checking for the presence of a start of a group
		$context = $this->parseGroupStart($context);

		if (!$context['hasError'])
		{
			// Found a sign of the beginning of a group
			$groupStartPosition = $context['info']['groupStartPosition'];
			$context['position'] = $context['info']['groupDelimiterStartPosition'];
			$context = $this->clearContextInfo($context);
			$context = $this->parseGroupDelimiter($context);
		}

		if (!$context['hasError'])
		{
			// The value of the group separator was got
			$delimiterValue = $context['info']['value'];
			$context = $this->clearContextInfo($context);
			$context = $this->parseGroupFieldListStart($context);
		}

		if (!$context['hasError'])
		{
			// The values of the field list was got
			$fieldValues = $context['info']['fieldValues'];
			$context = $this->clearContextInfo($context);
			$context = $this->parseGroupEnd($context);
		}

		if (!$context['hasError'])
		{
			// The sign of the end of the group is received, the assembly of the group value.
			$context['info'] = [
				'type' => 'group',
				'position' => $groupStartPosition,
				'end' => $context['position'],
				'value' => implode(
					$delimiterValue,
					array_unique(
						$fieldValues // Kremlin,Moscow,Moscow,Russia,103132 -> Kremlin,Moscow,Russia,103132
					)
				),
			];
		}

		$context['level']--;

		if ($context['hasError'])
		{
			$this->addContextError(
				$context,
				self::ERR_PARSE_GROUP,
				$startSearchPosition,
				['groupStartPosition' => $groupStartPosition]
			);
		}

		return $context;
	}

	private function appendTextBlock(array &$blocks, int $position, string $value)
	{
		$lastBlock = end($blocks);
		$lastBlockIndex = key($blocks);
		if (is_array($lastBlock) && $lastBlock['type'] === 'text')
		{
			$blocks[$lastBlockIndex]['value'] .= $value;
			$blocks[$lastBlockIndex]['length'] += strlen($value);
		}
		else
		{
			$blocks[] = [
				'type' => 'text',
				'position' => $position,
				'length' => strlen($value),
				'value' => $value,
			];
		}
	}

	private function appendGroupBlock(array &$blocks, int $position, string $value)
	{
		$blocks[] = [
			'type' => 'group',
			'position' => $position,
			'length' => strlen($value),
			'value' => $value,
		];
	}

	private function unshiftError(array &$errors, int $code, int $position, array $info = null)
	{
		array_unshift(
			$errors,
			[
				'code' => $code,
				'position' => $position,
				'info' => (!empty($info) && is_array($info)) ? $info : [],
			]
		);
	}

	private function addContextError(array &$context, int $code, int $position, array $info = null)
	{
		$context['hasError'] = true;
		$context['error']['code'] = $code;
		$context['error']['position'] = $position;
		$context['error']['info'] = (!empty($info) && is_array($info)) ? $info : [];
		$this->unshiftError($context['error']['errors'], $code, $position, $info);
	}

	private function addContextErrors(array &$context, array $errors, array $info = null)
	{
		$context['hasError'] = true;
		$context['error']['code'] = $errors[0]['code'];
		$context['error']['position'] = $errors[0]['position'];
		$context['error']['info'] = (!empty($info) && is_array($info)) ? $info : [];
		array_splice($context['error']['errors'], 0, 0, $errors);
	}

	private function parseBlocks(array $context): array
	{
		/* Variable for debug only
		errorDisplayed = false;
		*/

		$blocks = [];

		$templateLength = strlen($context['template']);
		while ($context['position'] < $templateLength)
		{
			$blockStartPosition = $context['position'];
			$context = $this->parseGroup($context);
			if ($context['hasError'])
			{
				// Debug info
				/*if (!$errorDisplayed)
				{
					echo str_replace(PHP_EOL, '<br />', htmlspecialcharsbx($this->getErrorsText($context)));
					$errorDisplayed = true;
				}*/

				$errorInfo = $context['error']['info'];
				if (!empty($errorInfo)
					&& is_array($errorInfo)
					&& isset($errorInfo['groupStartPosition'])
					&& $errorInfo['groupStartPosition'] > $blockStartPosition)
				{
					$blockLength = $errorInfo['groupStartPosition'] - $blockStartPosition + 1;
				}
				else
				{
					$blockLength = 1;
				}

				$this->appendTextBlock(
					$blocks,
					$context['error']['position'],
					substr($context['template'], $blockStartPosition, $blockLength)
				);
				$context = $this->clearContextInfoAndError($context);
				$context['position'] = $blockStartPosition + $blockLength;
			}
			else
			{
				$groupStartPosition = $context['info']['position'];
				if ($groupStartPosition > $blockStartPosition)
				{
					$this->appendTextBlock(
						$blocks,
						$blockStartPosition,
						substr(
							$context['template'],
							$blockStartPosition,
							$groupStartPosition - $blockStartPosition
						)
					);
				}

				if ($context['info']['value'] !== '')
				{
					$this->appendGroupBlock(
						$blocks,
						$groupStartPosition,
						$context['info']['value']
					);
				}

				$context = $this->clearContextInfo($context);
			}
		}

		if (!$context['hasError'])
		{
			$context['info'] = ['blocks' => $blocks];
		}

		return $context;
	}

	public function convert(Address $address): string
	{
		$result = '';

		$context = $this->createContext();
		$context['template'] = $this->template;
		$context['address'] = $address;

		$context = $this->parseBlocks($context);

		if (!$context['hasError'])
		{
			foreach ($context['info']['blocks'] as $block)
			{
				if ($block['type'] === 'text')
				{
					$result .= $this->unescapeText($block['value']);
				}
				else
				{
					$result .= $block['value'];
				}
			}
		}

		if ($result !== '')
		{
			$result = explode(self::STR_DELIMITER_PLACEHOLDER, $result);
			$result = array_values(
				array_filter($result, function ($part) {
					return ($part !== '');
				})
			);
			if ($this->htmlEncode && !empty($result) && is_array($result))
			{
				array_walk($result, function (&$part) {
					$part = htmlspecialcharsbx($part);
				});
			}

			$result = implode($this->delimiter, $result);
		}

		return $result;
	}
}
