<?php

namespace Bitrix\Bizproc\BaseType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

class Time extends Base
{
	public static function getType()
	{
		return FieldType::TIME;
	}

	public static function internalizeValue(FieldType $fieldType, $context, $value)
	{
		if (\CBPActivity::isExpression($value))
		{
			return $value;
		}

		if (is_string($value) && Value\Time::isCorrect($value))
		{
			return new Value\Time($value, \CTimeZone::GetOffset());
		}

		return null;
	}

	public static function getFormats()
	{
		$formats = [
			'server' => [
				'callable' => 'formatValueServer',
				'separator' => ', ',
			],
			'responsible' => [
				'callable' => 'formatValueResponsible',
				'separator' => ', ',
			],
		];
		$formats['author'] = $formats['responsible'];

		return array_merge(parent::getFormats(), $formats);
	}

	protected static function formatValueServer(FieldType $fieldType, $value)
	{
		if ($value instanceof Value\Time)
		{
			return $value->toServerTime()->format(Value\Time::getFormat());
		}

		return $value;
	}

	protected static function formatValueResponsible(FieldType $fieldType, $value)
	{
		if ($value instanceof Value\Time)
		{
			$offset = static::getResponsibleOffset($fieldType);

			return $value->toUserTime($offset)->format(Value\Time::getFormat());
		}

		return $value;
	}

	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);
		if (is_string($value) && !\CBPHelper::isEmptyValue($value))
		{
			if (\CBPActivity::isExpression($value))
			{
				return $value;
			}

			if (Value\Time::isCorrect($value))
			{
				$value = new Value\Time($value, \CTimeZone::GetOffset());

				return $value->serialize();
			}

			static::addError([
				'code' => 'ErrorValue',
				'message' => Loc::getMessage('BPDT_TIME_INVALID'),
			]);
		}

		return null;
	}

	public static function compareValues($valueA, $valueB)
	{
		$offset = \CTimeZone::GetOffset();
		$timestampA = (new Value\Time((string)$valueA, $offset))->getTimestamp();
		$timestampB = (new Value\Time((string)$valueB, $offset))->getTimestamp();

		return parent::compareValues($timestampA, $timestampB);
	}

	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$name = static::generateControlName($field);
		$value = static::internalizeValue($fieldType, 'Renderer', $value);
		if ($value instanceof Value\Time)
		{
			$value = $value->toSystemObject()->format(Value\Time::getRenderFormat());
		}
		$className = static::generateControlClassName($fieldType, $field);

		$renderResult = '<select name="' . htmlspecialcharsbx($name) . '" class="' . htmlspecialcharsbx($className) . '">';
		$renderResult .= '<option value="">' . Loc::getMessage('BPDT_TIME_NOT_SET') . '</option>';
		$format = Value\Time::getFormat();
		for ($hour = 0; $hour < 24; $hour++)
		{
			$time = (new \Bitrix\Main\Type\DateTime())->setTime($hour, 0);

			$selected =
				($value === $time->format(Value\Time::getRenderFormat()))
					? ' selected'
					: ''
			;
			$timeValue = htmlspecialcharsbx($time->format(Value\Time::getRenderFormat()));
			$timeText = htmlspecialcharsbx($time->format($format));
			$renderResult .= '<option value="' . $timeValue . '"' . $selected . '>' . $timeText . '</option>';

			$time = (new \Bitrix\Main\Type\DateTime())->setTime($hour, 30);
			$selected =
				($value === $time->format(Value\Time::getRenderFormat()))
					? ' selected'
					: ''
			;
			$timeValue = htmlspecialcharsbx($time->format(Value\Time::getRenderFormat()));
			$timeText = htmlspecialcharsbx($time->format($format));
			$renderResult .= '<option value="' . $timeValue . '"' . $selected . '>' . $timeText . '</option>';

		}
		$renderResult .= '</select>';

		return $renderResult;
	}

	protected static function getResponsibleOffset(FieldType $fieldType): int
	{
		$documentId = $fieldType->getDocumentId();
		$userId = $documentId ? \CBPHelper::extractFirstUser(['author', 'responsible'], $documentId) : null;

		return $userId ? \CTimeZone::GetOffset($userId, true) : 0;
	}

	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		$isCorrectValue = (
			$value instanceof Value\Time
			|| Value\Time::isCorrect((string)$value)
		);

		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();

		switch ($type)
		{
			case FieldType::BOOL:
				$result = $isCorrectValue;
				break;
			case FieldType::DATE:
			case FieldType::DATETIME:
				$culture = Application::getInstance()->getContext()->getCulture();
				$dateFormat =
					\Bitrix\Main\Type\DateTime::convertFormatToPhp($culture?->getDateFormat() ?? 'DD.MM.YYYY')
				;
				$dateTimeFormat =
					\Bitrix\Main\Type\DateTime::convertFormatToPhp($culture?->getDateTimeFormat() ?? 'DD.MM.YYYY HH:MI:SS')
				;

				$currentDate = new \Bitrix\Main\Type\DateTime();
				$date =
					$isCorrectValue
						? (new Value\Time((string)$value, \CTimeZone::GetOffset()))->toSystemObject()
						: new \Bitrix\Main\Type\DateTime()
				;
				$date->setDate($currentDate->format('Y'), $currentDate->format('m'), $currentDate->format('d'));

				$result = $date->format($type === FieldType::DATE ? $dateFormat : $dateTimeFormat);

				break;
			case FieldType::DOUBLE:
			case FieldType::INT:
				$result =
					$isCorrectValue
						? (new \Bitrix\Bizproc\BaseType\Value\Time((string)$value, \CTimeZone::GetOffset()))->getTimestamp()
						: 0
				;
				break;
			case FieldType::STRING:
			case FieldType::TEXT:
			case FieldType::TIME:
				$result =
					$isCorrectValue
						? (string)(new \Bitrix\Bizproc\BaseType\Value\Time((string)$value, \CTimeZone::GetOffset()))
						: ''
				;

				break;
			default:
				$result = null;
		}

		return $result;
	}
}
