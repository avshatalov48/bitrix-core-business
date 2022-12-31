<?php

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class MessageLookalikeYandex extends MessageLookalike
{
	public const CODE = self::CODE_ADS_LOOKALIKE_YANDEX;

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_NAME_ADS_LOOKALIKE_YA');
	}

	/**
	 * @return void
	 */
	protected function setConfigurationOptions()
	{
		$this->configuration->setArrayOptions([
			[
				'type' => 'title',
				'code' => 'AUDIENCE_NAME',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_AUDIENCE_TITLE'),
				'required' => true,
			],
			[
				'type' => 'string',
				'code' => 'AUDIENCE_LOOKALIKE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_ACCOUNT_ID'),
				'required' => true,
			],
			[
				'type' => 'string',
				'code' => 'CLIENT_ID',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_CLIENT_ID'),
				'required' => true,
			],
			[
				'type' => 'string',
				'code' => 'ACCOUNT_ID',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_ACCOUNT_ID'),
				'required' => false,
			],
			[
				'type' => 'string',
				'code' => 'AUDIENCE_ID',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_AUDIENCE_ID'),
				'required' => true,
			],
			[
				'type' => 'bool',
				'code' => 'DEVICE_DISTRIBUTION',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_DEVICE_DISTRIBUTION'),
				'required' => false,
			],
			[
				'type' => 'bool',
				'code' => 'GEO_DISTRIBUTION',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_GEO_DISTRIBUTION'),
				'required' => false,
			],
		]);
	}

	/**
	 * @return Result
	 */
	public function onBeforeStart(): \Bitrix\Main\Result
	{
		return new Result();
	}

	/**
	 * @return array
	 */
	public function getLookalikeOptions(): array
	{
		return [
			'name' => $this->configuration->get('AUDIENCE_NAME'),
			'lookalike_value' => $this->configuration->get('AUDIENCE_LOOKALIKE'),
			'maintain_device_distribution' => $this->configuration->get('DEVICE_DISTRIBUTION'),
			'maintain_geo_distribution' => $this->configuration->get('GEO_DISTRIBUTION'),
		];
	}
}