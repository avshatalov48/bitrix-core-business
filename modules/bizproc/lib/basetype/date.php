<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Date
 * @package Bitrix\Bizproc\BaseType
 */
class Date extends Base
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::DATE;
	}

	/**
	 * Normalize single value.
	 *
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @return mixed Normalized value
	 */
	public static function toSingleValue(FieldType $fieldType, $value)
	{
		if (is_array($value))
		{
			reset($value);
			$value = current($value);
		}
		return $value;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return null|mixed
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();
		switch ($type)
		{
			case FieldType::DOUBLE:
			case FieldType::INT:
				$value = $value? (int)strtotime($value) : 0;
				break;
			case FieldType::DATE:
			case FieldType::DATETIME:
			case FieldType::STRING:
			case FieldType::TEXT:
				$value = (string) $value;
				if ($value)
				{
					if ($type == FieldType::DATE)
						$format = \FORMAT_DATE;
					elseif ($type == FieldType::DATETIME)
						$format = \FORMAT_DATETIME;
					else
						$format = static::getType() == FieldType::DATE ? \FORMAT_DATE : \FORMAT_DATETIME;

					if (\CheckDateTime($value, $format))
					{
						$value = date(Type\Date::convertFormatToPhp($format), \MakeTimeStamp($value, $format));
					}
					else
					{
						$value = date(Type\Date::convertFormatToPhp($format), strtotime($value));
					}
				}
				break;
			case FieldType::TIME:
				if ($value instanceof Value\Date)
				{
					$systemObject = $value->toSystemObject();
					$value = new \Bitrix\Bizproc\BaseType\Value\Time(
						$systemObject->format(\Bitrix\Bizproc\BaseType\Value\Time::getFormat()),
						$value->getOffset()
					);
				}

				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * Return conversion map for current type.
	 * @return array Map.
	 */
	public static function getConversionMap()
	{
		return array(
			array(
				FieldType::DOUBLE,
				FieldType::INT,
				FieldType::DATE,
				FieldType::DATETIME,
				FieldType::STRING,
				FieldType::TEXT
			)
		);
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param mixed $value
	 * @param bool $allowSelection
	 * @param int $renderMode
	 * @return string
	 */
	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$name = static::generateControlName($field);
		$value = static::internalizeValue($fieldType, 'Renderer', $value);
		$offset = ($value instanceof Value\Date) ? $value->getOffset() : 0;

		$className = static::generateControlClassName($fieldType, $field);
		$renderResult = '';
		$isPublicControl = $renderMode & FieldType::RENDER_MODE_PUBLIC;

		if ($isPublicControl && $allowSelection)
		{
			$selectorAttributes = sprintf(
				'data-role="inline-selector-target" data-selector-type="%s" data-property="%s" ',
				htmlspecialcharsbx($fieldType->getType()),
				htmlspecialcharsbx(Main\Web\Json::encode($fieldType->getProperty()))
			);

			$renderResult = sprintf(
				'<input name="%s" type="text" class="%s" value="%s" placeholder="%s" %s/>',
				htmlspecialcharsbx($name),
				htmlspecialcharsbx($className),
				htmlspecialcharsbx($value),
				htmlspecialcharsbx($fieldType->getDescription()),
				$selectorAttributes
			);
		}
		elseif ($renderMode & FieldType::RENDER_MODE_MOBILE)
		{
			$renderResult = '<div><input type="hidden" value="'
				.htmlspecialcharsbx($value).'" data-type="'
				.htmlspecialcharsbx(static::getType()).'" name="'.htmlspecialcharsbx($name).'"/>'
				.'<a href="#" onclick="return BX.BizProcMobile.showDatePicker(this, event);">'
				.($value? htmlspecialcharsbx($value) : Loc::getMessage('BPDT_DATE_MOBILE_SELECT')).'</a></div>';
		}
		else
		{
			\CJSCore::Init(['popup', 'date']);
			$renderResult = sprintf(
				'<input type="text" name="%s" value="%s" class="%s"/>'
						. '<img src="/bitrix/js/main/core/images/calendar-icon.gif" alt="calendar" class="calendar-icon" '
						. 'onclick="BX.calendar({node:this, field: this.previousSibling, bTime: %s, bHideTime: %s});" '
						. 'onmouseover="BX.addClass(this, \'calendar-icon-hover\');" '
						. 'onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"/>',
				htmlspecialcharsbx($name),
				htmlspecialcharsbx($value),
				$isPublicControl ? htmlspecialcharsbx($className) : '',
				static::getType() == FieldType::DATETIME ? 'true' : 'false',
				static::getType() == FieldType::DATETIME ? 'false' : 'true'
			);

			$tzName = 'tz_'.$name;
			$zones = self::getZones();

			if (!$offset && $renderMode & FieldType::RENDER_MODE_PUBLIC)
			{
				$offset = 'current';
			}

			$tzClassName = 'bizproc-type-control-date-lc';
			if ($fieldType->isMultiple())
			{
				$tzClassName .= ' bizproc-type-control-date-lc-multiple';
			}
			if (!$isPublicControl)
			{
				$tzClassName = '';
			}

			$renderResult .= '<select name="'.htmlspecialcharsbx($tzName).'" class="'.$tzClassName.'">';
			foreach ($zones as $zone)
			{
				$selected = ($offset && $offset === $zone['offset']) ? 'selected' : '';
				$renderResult .= '<option value="'.htmlspecialcharsbx($zone['value']).'" '.$selected.'>'
					.htmlspecialcharsbx($zone['text']).'</option>';
			}
			$renderResult .= '</select>';

			if ($fieldType->isMultiple())
			{
				$settings = $fieldType->getSettings();
				$settings['timezones'] = $zones;
				$fieldType->setSettings($settings);
			}
		}

		return $renderResult;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$allowSelectionOrig = $allowSelection;
		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$allowSelection = false;
		}

		$value = static::toSingleValue($fieldType, $value);
		$selectorValue = null;

		if ($allowSelection && \CBPActivity::isExpression($value))
		{
			$selectorValue = $value;
			$value = null;
		}

		$renderResult = static::renderControl($fieldType, $field, $value, $allowSelectionOrig, $renderMode);

		if ($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
	}

	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$allowSelectionOrig = $allowSelection;
		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$allowSelection = false;
		}

		if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
		{
			$value = array($value);
		}

		$selectorValue = null;
		if ($allowSelection)
		{
			foreach ($value as $k => $v)
			{
				if (\CBPActivity::isExpression($v))
				{
					$selectorValue = $v;
					unset($value[$k]);
				}
			}
			$value = array_values($value);
		}

		if (empty($value))
		{
			$value[] = null;
		}

		$controls = [];

		foreach ($value as $k => $v)
		{
			$singleField = $field;
			$singleField['Index'] = $k;
			$controls[] = static::renderControl(
				$fieldType,
				$singleField,
				$v,
				$allowSelectionOrig,
				$renderMode
			);
		}

		if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
		{
			$renderResult = static::renderPublicMultipleWrapper($fieldType, $field, $controls);
		}
		else
		{
			$renderResult = static::wrapCloneableControls($controls, static::generateControlName($field));
		}

		if ($allowSelection)
		{
			$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
		}

		return $renderResult;
	}


	/**
	 * @param int $renderMode Control render mode.
	 * @return bool
	 */
	public static function canRenderControl($renderMode)
	{
		return true;
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param array $request
	 * @return null|string|Type\Date
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);

		if ($value !== null && is_string($value) && $value <> '')
		{
			if (\CBPActivity::isExpression($value))
				return $value;

			$format = static::getType() == FieldType::DATETIME ? \FORMAT_DATETIME : \FORMAT_DATE;
			if(!\CheckDateTime($value, $format))
			{
				$value = null;
				static::addError(array(
					'code' => 'ErrorValue',
					'message' => Loc::getMessage('BPDT_DATE_INVALID'),
					'parameter' => static::generateControlName($field),
				));
			}
			else
			{
				$tzOffset = self::extractOffset($field, $request);
				$value = (static::getType() == FieldType::DATETIME) ?
					new Value\DateTime($value, $tzOffset) : new Value\Date($value, $tzOffset);

				//have to serialize in design time.
				$value = $value->serialize();
			}
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	private static function extractOffset(array $field, array $request)
	{
		$tzName = 'tz_'.$field['Field'];
		$tz = isset($request[$tzName]) ? $request[$tzName] : null;
		if (is_array($tz))
		{
			$tz = isset($field['Index']) ? $tz[$field['Index']] : $tz[0];
		}

		if ($tz === 'current')
		{
			return \CTimeZone::GetOffset();
		}
		elseif ($tz)
		{
			$localTime = new \DateTime();
			$localOffset = $localTime->getOffset();

			$userTime = new \DateTime(null, new \DateTimeZone($tz));
			$userOffset = $userTime->getOffset();

			return $userOffset - $localOffset;
		}

		return 0;
	}

	/**
	 * Get formats list.
	 * @return array
	 */
	public static function getFormats()
	{
		$formats = parent::getFormats();
		$formats['server'] = [
			'callable'  => 'formatValueServer',
			'separator' => ', ',
		];

		$formats['author'] = $formats['responsible'] = [
			'callable'  => 'formatValueAuthor',
			'separator' => ', ',
		];

		return $formats;
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueServer(FieldType $fieldType, $value)
	{
		if ($value instanceof Value\Date)
		{
			return date($value->getFormat(), $value->getTimestamp());
		}

		return $value;
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueAuthor(FieldType $fieldType, $value)
	{
		if ($value instanceof Value\Date)
		{
			$documentId = $fieldType->getDocumentId();

			if ($documentId)
			{
				$userId = \CBPHelper::ExtractUsers(['author', 'responsible'], $documentId, true);
				$offset = $userId ? \CTimeZone::GetOffset($userId, true) : 0;

				$value = new Value\DateTime($value->getTimestamp(), $offset);
			}

			return (string) $value;
		}

		return $value;
	}

	public static function internalizeValue(FieldType $fieldType, $context, $value)
	{
		if ($value && is_string($value))
		{
			$offset = \CTimeZone::GetOffset();
			try
			{
				$obj = (static::getType() === FieldType::DATE)
					? new Value\Date($value, $offset)
					: new Value\DateTime($value, $offset);
				//set value if everything is ok
				if ($obj->getTimestamp() !== null)
				{
					$value = $obj;
				}
			}
			catch(Main\ObjectException $e)
			{
			}
		}
		else if ($value instanceof Type\Date)
		{
			return (static::getType() === FieldType::DATE)
				? Value\Date::fromSystemObject($value)
				: Value\DateTime::fromSystemObject($value);
		}

		return $value;
	}

	public static function externalizeValue(FieldType $fieldType, $context, $value)
	{
		//serialized date string
		if (is_string($value) && preg_match('#(.+)\s\[([0-9\-]+)\]#', $value))
		{
			$value = static::internalizeValue($fieldType, $context, $value);
		}

		if ($value instanceof Value\Date)
		{
			return $context === 'rest' ? $value->toSystemObject()->format('c') : (string) $value->toSystemObject();
		}

		if (is_string($value) && $context === 'rest')
		{
			return date('c', strtotime($value));
		}

		return $value;
	}

	private static function getZones()
	{
		$serverOffset = (new \DateTime())->getOffset();

		$timezones = [];
		$exclude = ["Etc/", "GMT", "UTC", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU"];
		foreach (\DateTimeZone::listIdentifiers() as $tz)
		{
			foreach ($exclude as $ex)
				if (mb_strpos($tz, $ex) === 0)
					continue 2;
			try
			{
				$dateTimeZone = new \DateTimeZone($tz);
				$timezones[$tz] = ['timezone_id' => $tz, 'offset' => $dateTimeZone->getOffset(new \DateTime("now", $dateTimeZone))];
			} catch (\Exception $e)
			{
			}
		}

		uasort($timezones, function ($a, $b)
		{
			if ($a['offset'] == $b['offset'])
				return strcmp($a['timezone_id'], $b['timezone_id']);

			return ($a['offset'] < $b['offset'] ? -1 : 1);
		});

		$result = [
			['value' => '', 'text' => Loc::getMessage('BPDT_DATE_SERVER_TZ'), 'offset' => 0],
			['value' => 'current', 'text' => Loc::getMessage('BPDT_DATE_CURRENT_TZ'), 'offset' => 'current']
		];
		foreach ($timezones as $z)
		{
			$result[] = [
				'value' => $z['timezone_id'],
				'text' => '(UTC'.($z['offset'] <> 0 ? ' '.($z['offset'] < 0 ? '-' : '+').sprintf("%02d", ($h = floor(abs($z['offset']) / 3600))).':'.sprintf("%02d", abs($z['offset']) / 60 - $h * 60) : '').') '.$z['timezone_id'],
				'offset' => $z['offset'] - $serverOffset
			];
		}

		return $result;
	}

	public static function compareValues($valueA, $valueB)
	{
		$valueA = \CBPHelper::makeTimestamp($valueA);
		$valueB = \CBPHelper::makeTimestamp($valueB);

		return parent::compareValues($valueA, $valueB);
	}
}