<?php

namespace Bitrix\Seo\Retargeting;

/**
 * Class AdsAudienceConfig.
 * @package Bitrix\Seo\Retargeting
 */
class AdsAudienceConfig
{
	/** @var  string $accountId Account ID. */
	public $accountId;

	/** @var  string $audienceId Audience ID. */
	public $audienceId;

	/** @var string|null $contactType Contact type.  */
	public $contactType = null;

	public $type = null;

	public $autoRemoveDayNumber = null;

	/**
	 * AdsAudienceConfig constructor.
	 *
	 * @param \stdClass|null $config
	 */
	public function __construct(\stdClass $config = null)
	{
		if (!$config)
		{
			return;
		}

		if ($config->accountId)
		{
			$this->accountId = $config->accountId;
		}
		if ($config->audienceId)
		{
			$this->audienceId = $config->audienceId;
		}
		if ($config->contactType)
		{
			$this->contactType = $config->contactType;
		}
		if ($config->type)
		{
			$this->type = $config->type;
		}
		if ($config->autoRemoveDayNumber)
		{
			$this->autoRemoveDayNumber = $config->autoRemoveDayNumber;
		}
	}
}