<?php

namespace Bitrix\Bizproc\BaseType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class Time extends Base
{
	public static function getType()
	{
		return FieldType::TIME;
	}

	public static function internalizeValue(FieldType $fieldType, $context, $value)
	{
		$format = Value\Time::getFormat();
		$offset = \CTimeZone::GetOffset();

		if ($value && is_string($value))
		{
			return new Value\Time($value, $offset);
		}

		if ($value instanceof \Bitrix\Main\Type\Date)
		{
			return new Value\Time($value->format($format), $offset);
		}

		if (\CBPActivity::isExpression($value))
		{
			return $value;
		}

		return null;
	}

	public static function getFormats()
	{
		$formats = parent::getFormats();
		$formats['server'] = [
			'callable' => 'formatValueServer',
			'separator' => ', ',
		];
		$formats['responsible'] = [
			'callable' => 'formatValueResponsible',
			'separator' => ', ',
		];
		$formats['author'] = $formats['responsible'];

		return $formats;
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
				return new Value\Time($value, \CTimeZone::GetOffset());
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
		$value = (string)static::internalizeValue($fieldType, 'Renderer', $value);
		$className = static::generateControlClassName($fieldType, $field);

		$renderResult = '<select name="' . htmlspecialcharsbx($name) . '" class="' . htmlspecialcharsbx($className) . '">';
		$renderResult .= '<option value="">' . Loc::getMessage('BPDT_TIME_NOT_SET') . '</option>';
		$format = \Bitrix\Bizproc\BaseType\Value\Time::getFormat();
		for ($hour = 0; $hour < 24; $hour++)
		{
			$time = (new \Bitrix\Main\Type\DateTime())->setTime($hour, 0)->format($format);

			$selected = ($value === $time) ? ' selected' : '';
			$timeHtml = htmlspecialcharsbx($time);
			$renderResult .= '<option value="' . $timeHtml . '"' . $selected . '>' . $timeHtml . '</option>';

			$time = (new \Bitrix\Main\Type\DateTime())->setTime($hour, 30)->format($format);
			$selected = ($value === $time) ? ' selected' : '';
			$timeHtml = htmlspecialcharsbx($time);
			$renderResult .= '<option value="' . $timeHtml . '"' . $selected . '>' . $timeHtml . '</option>';

		}
		$renderResult .= '</select>';

		return $renderResult;
	}

	protected static function getResponsibleOffset(FieldType $fieldType): int
	{
		$documentId = $fieldType->getDocumentId();
		$userId = $documentId ? \CBPHelper::ExtractUsers(['author', 'responsible'], $documentId, true) : null;

		return $userId ? \CTimeZone::GetOffset($userId, true) : 0;
	}
}