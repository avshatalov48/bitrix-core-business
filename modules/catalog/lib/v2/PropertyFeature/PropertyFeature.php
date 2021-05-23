<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\HasSettingsTrait;

/**
 * Class PropertyFeature
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyFeature extends BaseEntity
{
	use HasSettingsTrait;

	public function getFeatureId(): string
	{
		return $this->getSetting('FEATURE_ID');
	}

	public function isEnabled(): bool
	{
		return $this->getSetting('IS_ENABLED') === 'Y';
	}

	public function getModule(): string
	{
		return $this->getSetting('MODULE_ID');
	}
}