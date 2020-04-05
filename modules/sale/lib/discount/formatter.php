<?php
namespace Bitrix\Sale\Discount;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class Formatter
{
	const TYPE_SIMPLE = 0x0001;
	const TYPE_VALUE = 0x0002;
	const TYPE_LIMIT_VALUE = 0x0004;
	const TYPE_FIXED = 0x0008;
	const TYPE_MAX_BOUND = 0x0010;
	const TYPE_SIMPLE_GIFT = 0x0020;

	const VALUE_TYPE_PERCENT = 'P';
	const VALUE_TYPE_CURRENCY = 'C';
	const VALUE_TYPE_SUMM = 'S';
	const VALUE_TYPE_SUMM_BASKET = 'B';

	const VALUE_ACTION_DISCOUNT = 'D';
	const VALUE_ACTION_EXTRA = 'E';
	const VALUE_ACTION_CUMULATIVE = 'A';

	const LIMIT_MAX = 'MAX';
	const LIMIT_MIN = 'MIN';

	private static $errors = array();

	/**
	 * Prepare action or result description.
	 *
	 * @param int $type					Action description type.
	 * @param array|string $data		Action description data.
	 * @return null|array
	 */
	public static function prepareRow($type, $data)
	{
		self::clearErrors();
		$process = true;
		$result = array();

		$type = (int)$type;

		if ($type != self::TYPE_SIMPLE)
		{
			if (empty($data) || !is_array($data))
			{
				$process = false;
				self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
			}
		}
		switch ($type)
		{
			case self::TYPE_SIMPLE:
				if (empty($data) || !is_string($data))
				{
					$process = false;
					self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
				}
				if ($process)
				{
					$result = array(
						'TYPE' => self::TYPE_SIMPLE,
						'DESCR' => $data
					);
				}
				break;
			case self::TYPE_LIMIT_VALUE:
			case self::TYPE_VALUE:
				if ($type == self::TYPE_LIMIT_VALUE)
				{
					if ($process)
					{
						if (!isset($data['LIMIT_TYPE']) || !isset($data['LIMIT_VALUE']) || !isset($data['LIMIT_UNIT']))
						{
							$process = false;
							self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
						}
						elseif ($data['LIMIT_TYPE'] != self::LIMIT_MAX && $data['LIMIT_TYPE'] != self::LIMIT_MIN)
						{
							$process = false;
							self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
						}
					}
					if ($process)
					{
						if ($data['VALUE_TYPE'] != self::VALUE_TYPE_PERCENT)
						{
							$process = false;
							self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
						}
					}
					if ($process)
					{
						$result['LIMIT_TYPE'] = $data['LIMIT_TYPE'];
						$result['LIMIT_VALUE'] = $data['LIMIT_VALUE'];
						$result['LIMIT_UNIT'] = $data['LIMIT_UNIT'];
					}
				}
				if ($process)
				{
					if (!isset($data['VALUE']) || !isset($data['VALUE_TYPE']))
					{
						$process = false;
						self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
					}
				}
				if ($process)
				{
					if (
						$data['VALUE_TYPE'] != self::VALUE_TYPE_PERCENT
						&& $data['VALUE_TYPE'] != self::VALUE_TYPE_CURRENCY
						&& $data['VALUE_TYPE'] != self::VALUE_TYPE_SUMM
						&& $data['VALUE_TYPE'] != self::VALUE_TYPE_SUMM_BASKET
					)
					{
						$process = false;
						self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
					}
					elseif (
						$data['VALUE_TYPE'] == self::VALUE_TYPE_CURRENCY
						|| $data['VALUE_TYPE'] == self::VALUE_TYPE_SUMM
						|| $data['VALUE_TYPE'] == self::VALUE_TYPE_SUMM_BASKET
					)
					{
						if (!isset($data['VALUE_UNIT']))
						{
							$process = false;
							self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
						}
					}
				}
				if ($process)
				{
					if (!isset($data['VALUE_ACTION']))
						$data['VALUE_ACTION'] = self::VALUE_ACTION_DISCOUNT;
					if (
						$data['VALUE_ACTION'] != self::VALUE_ACTION_DISCOUNT
						&& $data['VALUE_ACTION'] != self::VALUE_ACTION_EXTRA
						&& $data['VALUE_ACTION'] != self::VALUE_ACTION_CUMULATIVE
					)
					{
						$process = false;
						self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
					}
				}
				if ($process)
				{
					$result['TYPE'] = $type;
					$result['VALUE'] = $data['VALUE'];
					$result['VALUE_TYPE'] = $data['VALUE_TYPE'];
					$result['VALUE_ACTION'] = $data['VALUE_ACTION'];

					if (
						$data['VALUE_TYPE'] == self::VALUE_TYPE_CURRENCY
						|| $data['VALUE_TYPE'] == self::VALUE_TYPE_SUMM
						|| $data['VALUE_TYPE'] == self::VALUE_TYPE_SUMM_BASKET
					)
						$result['VALUE_UNIT'] = $data['VALUE_UNIT'];
					if (isset($data['RESULT_VALUE']) && isset($data['RESULT_UNIT']))
					{
						$result['RESULT_VALUE'] = (string)$data['RESULT_VALUE'];
						$result['RESULT_UNIT'] = $data['RESULT_UNIT'];
					}
				}
				break;
			case self::TYPE_FIXED:
				if ($process)
				{
					if (!isset($data['VALUE']) || !isset($data['VALUE_UNIT']))
					{
						$process = false;
						self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
					}
				}
				if ($process)
				{
					$result = array(
						'TYPE' => $type,
						'VALUE' => $data['VALUE'],
						'VALUE_UNIT' => $data['VALUE_UNIT']
					);
				}
				break;
			case self::TYPE_MAX_BOUND:
				if ($process)
				{
					if (!isset($data['VALUE']) || !isset($data['VALUE_UNIT']))
					{
						$process = false;
						self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
					}
				}
				if ($process)
				{
					$result = array(
						'TYPE' => $type,
						'VALUE' => $data['VALUE'],
						'VALUE_UNIT' => $data['VALUE_UNIT']
					);
					if (isset($data['RESULT_VALUE']) && isset($data['RESULT_UNIT']))
					{
						$result['RESULT_VALUE'] = (string)$data['RESULT_VALUE'];
						$result['RESULT_UNIT'] = $data['RESULT_UNIT'];
					}
				}
				break;
			case self::TYPE_SIMPLE_GIFT:
				$result = array(
					'TYPE' => self::TYPE_SIMPLE_GIFT
				);
				break;
			default:
				$process = false;
				self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_DESCR_BAD'));
				break;
		}

		return ($process ? $result : null);
	}

	/**
	 * Returns format action or result description.
	 *
	 * @param array $action		Action description.
	 * @return null|string
	 */
	public static function formatRow(array $action)
	{
		self::clearErrors();
		$result = null;
		if (!isset($action['TYPE']))
		{
			self::addError(Loc::getMessage('SALE_DISCOUNT_FORMATTER_ERR_FORMAT_TYPE_BAD'));
			return $result;
		}

		switch ($action['TYPE'])
		{
			case self::TYPE_SIMPLE:
				$result = $action['DESCR'];
				break;
			case self::TYPE_VALUE:
				if ($action['VALUE_TYPE'] == self::VALUE_TYPE_PERCENT)
				{
					$value = $action['VALUE'].'%';
					if (isset($action['RESULT_VALUE']) && isset($action['RESULT_UNIT']))
						$value .= ' ('.\CCurrencyLang::CurrencyFormat($action['RESULT_VALUE'], $action['RESULT_UNIT'], true).')';
				}
				else
				{
					if ($action['VALUE_TYPE'] == self::VALUE_TYPE_CURRENCY)
					{
						$value = \CCurrencyLang::CurrencyFormat($action['VALUE'], $action['VALUE_UNIT'], true);
					}
					else
					{
						$subMessageID = (
							$action['VALUE_TYPE'] == self::VALUE_TYPE_SUMM
							? 'SALE_DISCOUNT_FORMATTER_MESS_SUMM_FORMAT'
							: 'SALE_DISCOUNT_FORMATTER_MESS_SUMM_BASKET_FORMAT'
						);
						$value = Loc::getMessage(
							$subMessageID,
							array('#VALUE#' => \CCurrencyLang::CurrencyFormat($action['VALUE'], $action['VALUE_UNIT'], true))
						);
						unset($subMessageID);
					}
					if (isset($action['RESULT_VALUE']) && isset($action['RESULT_UNIT']) && $action['VALUE_UNIT'] != $action['RESULT_UNIT'])
						$value .= ' ('.\CCurrencyLang::CurrencyFormat($action['RESULT_VALUE'], $action['RESULT_UNIT'], true).')';
				}
				$messageId = 'SALE_DISCOUNT_FORMATTER_MESS_TYPE_DISCOUNT';
				if (isset($action['VALUE_ACTION']))
				{
					switch ($action['VALUE_ACTION'])
					{
						case self::VALUE_ACTION_EXTRA:
							$messageId = 'SALE_DISCOUNT_FORMATTER_MESS_TYPE_EXTRA';
							break;
						case self::VALUE_ACTION_CUMULATIVE:
							$messageId = 'SALE_DISCOUNT_FORMATTER_MESS_TYPE_CUMULATIVE';
							break;
					}
				}
				$result = Loc::getMessage($messageId, array('#VALUE#' => $value));
				unset($value, $messageId);
				break;
			case self::TYPE_LIMIT_VALUE:
				$messageId = (
					isset($action['LIMIT_TYPE']) && $action['LIMIT_TYPE'] == self::LIMIT_MIN
					? 'SALE_DISCOUNT_FORMATTER_MESS_LIMIT_MIN_FORMAT'
					: 'SALE_DISCOUNT_FORMATTER_MESS_LIMIT_MAX_FORMAT'
				);
				$value = Loc::getMessage(
					$messageId,
					array(
						'#PERCENT#' => $action['VALUE'].'%',
						'#LIMIT#' => \CCurrencyLang::CurrencyFormat($action['LIMIT_VALUE'], $action['LIMIT_UNIT'], true)
					)
				);
				if (isset($action['RESULT_VALUE']) && isset($action['RESULT_UNIT']))
					$value .= ' ('.\CCurrencyLang::CurrencyFormat($action['RESULT_VALUE'], $action['RESULT_UNIT'], true).')';
				$messageId = (
					isset($action['VALUE_ACTION']) && $action['VALUE_ACTION'] == self::VALUE_ACTION_EXTRA
					? 'SALE_DISCOUNT_FORMATTER_MESS_TYPE_EXTRA'
					: 'SALE_DISCOUNT_FORMATTER_MESS_TYPE_DISCOUNT'
				);
				$result = Loc::getMessage($messageId, array('#VALUE#' => $value));
				unset($value, $messageId);
				break;
			case self::TYPE_FIXED:
				$result = Loc::getMessage(
					'SALE_DISCOUNT_FORMATTER_MESS_FIXED_FORMAT',
					array('#VALUE#' => \CCurrencyLang::CurrencyFormat($action['VALUE'], $action['VALUE_UNIT'], true))
				);
				break;
			case self::TYPE_MAX_BOUND:
				$value = \CCurrencyLang::CurrencyFormat($action['VALUE'], $action['VALUE_UNIT'], true);
				if (isset($action['RESULT_VALUE']) && isset($action['RESULT_UNIT']))
					$value .= ' ('.\CCurrencyLang::CurrencyFormat($action['RESULT_VALUE'], $action['RESULT_UNIT'], true).')';
				else
					$value .= ' ('.$value.')';
				$result = Loc::getMessage(
					'SALE_DISCOUNT_FORMATTER_MESS_MAX_BOUND_FORMAT',
					array('#VALUE#' => $value)
				);
				unset($value);
				break;
			case self::TYPE_SIMPLE_GIFT:
				$result = Loc::getMessage('SALE_DISCOUNT_FORMATTER_MESS_SIMPLE_GIFT');
				break;
			default:
				break;
		}

		return $result;
	}

	/**
	 * Format discount result.
	 *
	 * @param array $actionList			Descriptions.
	 * @return array|null
	 */
	public static function formatList(array $actionList)
	{
		self::clearErrors();
		$result = array();
		if (!empty($actionList))
		{
			foreach ($actionList as $row)
			{
				if (!is_array($row))
					return null;
				$value = self::formatRow($row);
				if ($value === null)
					return null;
				$result[] = $value;
			}
			unset($value, $row);
		}
		return (empty($result) ? null: $result);
	}

	/**
	 * Clear formatter errors.
	 *
	 * @return void
	 */
	public static function clearErrors()
	{
		self::$errors = array();
	}

	/**
	 * Returns formatter errors.
	 *
	 * @return array
	 */
	public static function getErrors()
	{
		return self::$errors;
	}

	/**
	 * Add error.
	 *
	 * @param string $error		Error message.
	 * @return void
	 */
	private static function addError($error)
	{
		if ($error === '')
			return;
		self::$errors[] = $error;
	}
}