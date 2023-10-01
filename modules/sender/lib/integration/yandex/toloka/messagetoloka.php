<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Message;
use Bitrix\Sender\Message\iBase;
use Bitrix\Sender\Message\iToloka;

Loc::loadMessages(__FILE__);

class MessageToloka implements iBase, iToloka
{
	const CODE = 'toloka';
	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/**
	 * MessageBase constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_YANDEX_TOLOKA');
	}

	/**
	 * @inheritDoc
	 */
	public function getSupportedTransports()
	{
		return [static::CODE];
	}

	/**
	 * Load configuration.
	 *
	 * @param integer|null $id ID.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration($id = null)
	{
		if (!$this->configuration->hasOptions())
		{
			$this->setConfigurationOptions();
		}
		//
		$self = $this;
		$filterParams = $this->configuration->getOption('FILTER');

		if ($filterParams)
		{
			$filterParams->setView(
				function() use ($self)
				{
					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:main.ui.filter",
						"",
						[
							"FILTER_ID"      => "toloka-filter-connector",
							"FILTER"         => $self->getUiFilterFields(),
							"FILTER_PRESETS" => [],
							"DISABLE_SEARCH" => true,
							"ENABLE_LABEL"   => true,
						]
					);

					return ob_get_clean();
				}
			);
		}

		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);

		return $this->configuration;
	}

	protected function setConfigurationOptions()
	{
		$this->configuration->setArrayOptions(
			[
				[
					'type'         => 'string',
					'code'         => 'PROJECT_ID',
					'name'         => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_PROJECT'),
					'required'     => false,
					'show_in_list' => false,
					'value'        => '',
				],
				[
					'type'         => 'string',
					'code'         => 'POOL_ID',
					'name'         => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_POOL'),
					'required'     => false,
					'show_in_list' => false,
					'value'        => '',
				],
				[
					'type'         => 'string',
					'code'         => 'TASK_SUITE_ID',
					'name'         => '',
					'required'     => false,
					'show_in_list' => false,
					'value'        => '',
				],
				[
					'type'         => 'text',
					'code'         => 'INSTRUCTION',
					'name'         => Loc::getMessage('SENDER_TOLOKA_PROJECT_INSTRUCTION'),
					'required'     => true,
					'show_in_list' => true,
					'max_length'   => 2048,
					'value'        => '',
				],
				[
					'type'         => 'text',
					'code'         => 'DESCRIPTION',
					'name'         => Loc::getMessage('SENDER_TOLOKA_PROJECT_DESCRIPTION'),
					'required'     => true,
					'max_length'   => 2048,
					'show_in_list' => true,
					'value'        => '',
				],
				[
					'type'         => 'text',
					'code'         => 'TASKS',
					'name'         => Loc::getMessage('SENDER_TOLOKA_TASKS'),
					'required'     => true,
					'show_in_list' => true,
					'value'        => '',
				],
				[
					'type'         => 'number',
					'code'         => 'PRICE',
					'name'         => Loc::getMessage('SENDER_TOLOKA_PRICE'),
					'required'     => true,
					'show_in_list' => true,
					'value'        => '0.07',
					'hint'         => Loc::getMessage("SENDER_TOLOKA_PRICE_HINT")

				],
				[
					'type'         => 'checkbox',
					'code'         => 'ADULT_CONTENT',
					'name'         => Loc::getMessage('SENDER_TOLOKA_ADULT_CONTENT'),
					'required'     => false,
					'show_in_list' => true,
					'value'        => '',
				],
				[
					'type'         => 'number',
					'code'         => 'OVERLAP',
					'name'         => Loc::getMessage('SENDER_TOLOKA_MAX_POEOPLE_COUNT'),
					'required'     => true,
					'show_in_list' => true,
					'value'        => '100',
				],
				[
					'type'         => 'datetime',
					'code'         => 'EXPIRE_IN',
					'name'         => Loc::getMessage('SENDER_TOLOKA_POOL_EXPIRE_IN'),
					'required'     => true,
					'show_in_list' => true,
					'value'        => '',
				],
				[
					'type'         => 'string',
					'code'         => 'FILTER',
					'name'         => Loc::getMessage('SENDER_CONFIG_FILTER'),
					'required'     => false,
					'show_in_list' => true,
				],
			]
		);

		$list = [
			[
				'type'  => 'template-type',
				'code'  => 'TEMPLATE_TYPE',
				'name'  => 'Template type',
				'value' => '',
			],
			[
				'type'  => 'template-id',
				'code'  => 'TEMPLATE_ID',
				'name'  => 'Template id',
				'value' => '',
			],
		];

		foreach ($list as $optionData)
		{
			$optionData = $optionData + [
					'type'     => 'string',
					'name'     => '',
					'internal' => true,
				];
			$this->configuration->addOption(new Message\ConfigurationOption($optionData));
		}
	}

	public function getUiFilterFields()
	{
		$list = [
			[
				'id'      => 'REGION_BY_PHONE',
				"name"    => Loc::getMessage('SEDNER_INTEGRATION_TOLOKA_FILTER_REGION_PHONE'),
				'params'  => ['multiple' => 'Y'],
				'type'    => 'list',
				"default" => true
			],
			[
				'id'      => 'REGION_BY_IP',
				"name"    => Loc::getMessage('SEDNER_INTEGRATION_TOLOKA_FILTER_REGION_IP'),
				'params'  => ['multiple' => 'Y'],
				'type'    => 'list',
				"default" => true
			]
		];

		return $list;
	}

	/**
	 * @inheritDoc
	 */
	public function getCode()
	{
		return self::CODE;
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		$config = $configuration;
		$projectId = $config->getOption('PROJECT_ID')
			->getValue();
		$poolId = $config->getOption('POOL_ID')
			->getValue();

		return Entity\Message::create()
			->setCode($this->getCode())
			->saveConfiguration($this->configuration);
	}

	/**
	 * @inheritDoc
	 */
	public function copyConfiguration($id)
	{
	}
}
