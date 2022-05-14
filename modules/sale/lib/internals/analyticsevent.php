<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\Type\DateTime;

/**
 * Class AnalyticsEventTable
 *
 * @package Bitrix\Sale\Internals
 */
class AnalyticsEventTable extends Main\Entity\DataManager
{
	/**
	 * Facebook Conversion API
	 */
	public const FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED = 'FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED';
	public const FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED = 'FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED';
	public const FACEBOOK_CONVERSION_EVENT_FIRED = 'FACEBOOK_CONVERSION_EVENT_FIRED';

	/**
	 * @inheritDoc
	 */
	public static function getTableName()
	{
		return 'b_sale_analytics_events';
	}

	/**
	 * @inheritDoc
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configureAutocomplete()
				->configurePrimary(),
			(new EnumField('CODE'))
				->configureRequired()
				->configureValues(static::getAvailableCodes()),
			(new DatetimeField('CREATED_AT'))
				->configureRequired()
				->configureDefaultValue(static function() {
					return new DateTime();
				}),
			(new ArrayField('PAYLOAD'))
				->configureSerializationPhp()
				->configureUnserializeCallback(function ($value) {
					return unserialize(
						$value,
						['allowed_classes' => false]
					);
				}),
		];
	}

	/**
	 * @return array|string[]
	 */
	private static function getAvailableCodes(): array
	{
		return [
			self::FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED,
			self::FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED,
			self::FACEBOOK_CONVERSION_EVENT_FIRED,
		];
	}
}
