<?php
namespace Bitrix\Sale\Integration\Numerator;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\DynamicConfigurable;
use Bitrix\Main\Numerator\Generator\NumberGenerator;
use Bitrix\Sale\Registry;

/**
 * Class OrderIdNumberGenerator
 * @package Bitrix\Sale\Integration\Numerator
 */
class OrderIdNumberGenerator extends NumberGenerator implements DynamicConfigurable
{
	protected $orderId;

	const TEMPLATE_WORD_ORDER_ID = "ORDER_ID";

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_ORDER_ID),
		];
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_ORDER_ID)
			=> Loc::getMessage('BITRIX_SALE_INTEGRATION_NUMERATOR_ORDERIDNUMBERGENERATOR_WORD_ORDER_ID'),
		];
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		if (!is_null($this->orderId))
		{
			return str_replace(self::getPatternFor(static::TEMPLATE_WORD_ORDER_ID), $this->orderId, $template);
		}
		return $template;
	}

	/**
	 * @param array $config
	 */
	public function setDynamicConfig($config)
	{
		if (is_array($config) && array_key_exists('ORDER_ID', $config))
		{
			$this->orderId = $config['ORDER_ID'];
		}
	}
}