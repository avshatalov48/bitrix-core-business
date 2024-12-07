<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Text;
use Bitrix\Main\Localization\Loc;

/**
 * @method Rule offsetGet($offset)
 * @method Rule current()
 * @method Rule next()
 * @method Rule rewind()
 * @method getSessionTimeout()
 * @method getSessionIpMask()
 * @method getMaxStoreNum()
 * @method getStoreIpMask()
 * @method getStoreTimeout()
 * @method getCheckwordTimeout()
 * @method getPasswordLength()
 * @method getPasswordUppercase()
 * @method getPasswordLowercase()
 * @method getPasswordDigits()
 * @method getPasswordPunctuation()
 * @method getPasswordCheckWeak()
 * @method getPasswordCheckPolicy()
 * @method getPasswordChangeDays()
 * @method getPasswordUniqueCount()
 * @method getLoginAttempts()
 * @method getBlockLoginAttempts()
 * @method getBlockTime()
 * @property Rule[] $values
 */
class RulesCollection extends Type\Dictionary
{
	public const PRESET_DEFAULT = 0;
	public const PRESET_LOW = 1;
	public const PRESET_MIDDLE = 2;
	public const PRESET_HIGH = 3;

	/**
	 * RulesCollection constructor.
	 */
	public function __construct()
	{
		// Default policy
		$values = [
			'SESSION_TIMEOUT' => new LesserRule(Loc::getMessage('GP_SESSION_TIMEOUT'), ini_get('session.gc_maxlifetime') / 60),
			'SESSION_IP_MASK' => new IpMaskRule(Loc::getMessage('GP_SESSION_IP_MASK')),
			'MAX_STORE_NUM' => new LesserRule(Loc::getMessage('GP_MAX_STORE_NUM'), 10),
			'STORE_IP_MASK' => new IpMaskRule(Loc::getMessage('GP_STORE_IP_MASK')),
			'STORE_TIMEOUT' => new LesserRule(Loc::getMessage('GP_STORE_TIMEOUT'), 60*24*365),
			'CHECKWORD_TIMEOUT' => new LesserRule(Loc::getMessage('GP_CHECKWORD_TIMEOUT'), 60*24*2),
			'PASSWORD_LENGTH' => new GreaterRule(Loc::getMessage('GP_PASSWORD_LENGTH'), 6),
			'PASSWORD_UPPERCASE' => new BooleanRule(Loc::getMessage('GP_PASSWORD_UPPERCASE')),
			'PASSWORD_LOWERCASE' => new BooleanRule(Loc::getMessage('GP_PASSWORD_LOWERCASE')),
			'PASSWORD_DIGITS' => new BooleanRule(Loc::getMessage('GP_PASSWORD_DIGITS')),
			'PASSWORD_PUNCTUATION' => new BooleanRule(Loc::getMessage('GP_PASSWORD_PUNCTUATION', ['#SPECIAL_CHARS#' => \CUser::PASSWORD_SPECIAL_CHARS])),
			'PASSWORD_CHECK_WEAK' => new BooleanRule(Loc::getMessage('GP_PASSWORD_CHECK_WEAK')),
			'PASSWORD_CHECK_POLICY' => new BooleanRule(Loc::getMessage('GP_PASSWORD_CHECK_POLICY')),
			'PASSWORD_CHANGE_DAYS' => new LesserPositiveRule(Loc::getMessage('GP_PASSWORD_CHANGE_DAYS')),
			'PASSWORD_UNIQUE_COUNT' => new GreaterRule(Loc::getMessage('GP_PASSWORD_UNIQUE_COUNT')),
			'LOGIN_ATTEMPTS' => new LesserPositiveRule(Loc::getMessage('GP_LOGIN_ATTEMPTS')),
			'BLOCK_LOGIN_ATTEMPTS' => new LesserPositiveRule(Loc::getMessage('GP_BLOCK_LOGIN_ATTEMPTS')),
			'BLOCK_TIME' => new GreaterRule(Loc::getMessage('GP_BLOCK_TIME')),
		];

		parent::__construct($values);
	}

	/**
	 * Creates the collection of rules by a known preset.
	 * @param int $preset
	 * @return static
	 */
	public static function createByPreset($preset = self::PRESET_DEFAULT)
	{
		$policy = new static();

		if ($preset >= self::PRESET_LOW)
		{
			$policy['SESSION_TIMEOUT']->assignValue(30);
			$policy['STORE_IP_MASK']->assignValue('255.0.0.0');
			$policy['STORE_TIMEOUT']->assignValue(60*24*90);
		}
		if ($preset >= self::PRESET_MIDDLE)
		{
			$policy['SESSION_TIMEOUT']->assignValue(20);
			$policy['SESSION_IP_MASK']->assignValue('255.255.0.0');
			$policy['MAX_STORE_NUM']->assignValue(5);
			$policy['STORE_IP_MASK']->assignValue('255.255.0.0');
			$policy['STORE_TIMEOUT']->assignValue(60*24*30);
			$policy['CHECKWORD_TIMEOUT']->assignValue(60*24*1);
			$policy['PASSWORD_LENGTH']->assignValue(8);
			$policy['PASSWORD_UPPERCASE']->assignValue(true);
			$policy['PASSWORD_LOWERCASE']->assignValue(true);
			$policy['PASSWORD_DIGITS']->assignValue(true);
			$policy['PASSWORD_CHECK_WEAK']->assignValue(true);
			$policy['PASSWORD_CHANGE_DAYS']->assignValue(180);
			$policy['PASSWORD_UNIQUE_COUNT']->assignValue(1);
			$policy['LOGIN_ATTEMPTS']->assignValue(10);
		}
		if ($preset >= self::PRESET_HIGH)
		{
			$policy['SESSION_TIMEOUT']->assignValue(15);
			$policy['SESSION_IP_MASK']->assignValue('255.255.255.255');
			$policy['MAX_STORE_NUM']->assignValue(2);
			$policy['STORE_IP_MASK']->assignValue('255.255.255.255');
			$policy['STORE_TIMEOUT']->assignValue(60*24*7);
			$policy['CHECKWORD_TIMEOUT']->assignValue(60);
			$policy['PASSWORD_LENGTH']->assignValue(10);
			$policy['PASSWORD_PUNCTUATION']->assignValue(true);
			$policy['PASSWORD_CHECK_POLICY']->assignValue(true);
			$policy['PASSWORD_CHANGE_DAYS']->assignValue(90);
			$policy['PASSWORD_UNIQUE_COUNT']->assignValue(3);
			$policy['LOGIN_ATTEMPTS']->assignValue(3);
		}

		return $policy;
	}

	/**
	 * Retuns key => value pairs in old-fashioned style (compatibility).
	 * @return array
	 */
	public function getValues()
	{
		$result = [];

		foreach ($this->values as $code => $rule)
		{
			$value = $rule->getValue();
			if ($rule instanceof BooleanRule)
			{
				$result[$code] = ($value ? 'Y' : 'N');
			}
			else
			{
				$result[$code] = $value;
			}
		}

		return $result;
	}

	/**
	 * Compares two collections.
	 * @param RulesCollection $policy
	 * @return bool True if supplied policy is stronger than the current one.
	 */
	public function compare(RulesCollection $policy)
	{
		foreach ($this->values as $code => $rule)
		{
			if ($rule->compare($policy[$code]->getValue()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * It's magic...
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function __call($name, $arguments)
	{
		if (str_starts_with($name, "get"))
		{
			$ruleName = substr($name, 3);
			$ruleName = Text\StringHelper::camel2snake($ruleName);
			$ruleName = strtoupper($ruleName);

			if (isset($this->values[$ruleName]))
			{
				return $this->values[$ruleName]->getValue();
			}
		}

		throw new Main\SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $name, get_called_class()
		));
	}
}
