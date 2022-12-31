<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Message;
use Bitrix\Sender\Message\EventResult;
use Bitrix\Sender\Message\iMarketing;
use Bitrix\Sender\Message\Result;
use Bitrix\Seo\Marketing;

/**
 * Class MessageMarketingFb
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class MessageMarketingFb
	implements Message\iBase, Message\iMarketing, Message\iBeforeAfter
{
	const CODE = Message\iMarketing::CODE_FACEBOOK;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_PAUSED = 'PAUSED';
	const STATUS_ARCHIVED = 'ARCHIVED';
	const STATUS_DELETED = 'DELETED';

	/**
	 * MessageBase constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}
	/**
	 * Get name.
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_NAME_ADS_FACEBOOK');
	}

	protected function setConfigurationOptions()
	{
		$this->configuration->setArrayOptions(
			[
				[
					'type'     => 'string',
					'code'     => 'CLIENT_ID',
					'name'     => "",
					'required' => true,
				],
				[
					'type'     => 'title',
					'code'     => 'TITLE',
					'name'     => "",
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'ACCOUNT_ID',
					'name'     => Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_CONFIG_ACCOUNT_ID'),
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'INSTAGRAM_ACCOUNT_ID',
					'name'     => "",
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'PERMALINK',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'TARGET_URL',
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'AUDIENCE_ID',
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'BODY',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'BUDGET',
					'required' => true,
				],
				[
					'type'     => 'string',
					'code'     => 'DURATION',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'PAGE_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'AD_SET_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'CAMPAIGN_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'CREATIVE_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'ADS_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'INTERESTS',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'GENDERS',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'REGIONS',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'AGE_FROM',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'AGE_TO',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'MEDIA_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'INSTAGRAM_ACTOR_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'IMAGE_URL',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'AUDIENCE_PHONE_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'AUDIENCE_EMAIL_ID',
					'required' => false,
				],
				[
					'type'     => 'string',
					'code'     => 'STATUSs',
					'required' => false,
				]
			]
		);
	}
	public static function getAdsProvider($adsType, $clientId = null)
	{
		$service = Marketing\Configurator::getService();
		$service->setClientId($clientId);
		if($adsType === MessageMarketingInstagram::CODE)
		{
			$adsType = self::CODE;
		}
		$providers = Marketing\Configurator::getProviders([$adsType]);
		$isFound = false;
		$provider = array();
		foreach ($providers as $type => $provider)
		{
			if ($type == $adsType)
			{
				$isFound = true;
				break;
			}
		}

		if (!$isFound)
		{
			return null;
		}

		return $provider;
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

		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);


		$self = $this;
		$configuration = $this->configuration;
		$this->configuration->setView(
			function () use ($self, $configuration)
			{
				$containerNodeId = 'seo-ads-' . $configuration->getId();
				ob_start();

				$provider = static::getAdsProvider(
					$self->getAdsType(),
					$configuration->getOption('CLIENT_ID')->getValue()
				);

				$autoRemoveDays = $configuration->getOption('AUTO_REMOVE_DAY_NUMBER') ?
					$configuration->getOption('AUTO_REMOVE_DAY_NUMBER')->getValue() : null;

				$GLOBALS['APPLICATION']->IncludeComponent(
					'bitrix:seo.ads.builder',
					'',
					array(
						'INPUT_NAME_PREFIX' => 'CONFIGURATION_',
						'CONTAINER_NODE_ID' => $containerNodeId,
						'PROVIDER' => $provider,
						'SUBTYPE' => $self->getAdsType(),
						'ACCOUNT_ID' => $configuration->getOption('ACCOUNT_ID')->getValue(),
						'CLIENT_ID' => $configuration->getOption('CLIENT_ID')->getValue(),
						'AUTO_REMOVE_DAY_NUMBER' => $autoRemoveDays,
						'JS_DESTROY_EVENT_NAME' => '',
						'TITLE_NODE_SELECTOR' => '[data-role="letter-title"]',
						'HAS_ACCESS' => true
					)
				);

				$result = ob_get_clean();
				return $result;
			}
		);

		return $this->configuration;
	}

	public function getCode()
	{
		return static::CODE;
	}

	public function getSupportedTransports()
	{
		return array(static::CODE);
	}
	protected function getAdsType()
	{
		return static::CODE;
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
		$clientId = $config->getOption('CLIENT_ID')->getValue();
		$body = $config->getOption('BODY')->getValue();
		$targetUrl = $config->getOption('TARGET_URL')->getValue();
		$campaignName = $config->getOption('TITLE')->getValue();

		if (!$clientId)
		{
			$result = new Result();
			$result->addError(
				new Error(Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_ERROR_NO_CLIENT'))
			);

			return $result;
		}

		if(!filter_var($targetUrl, FILTER_VALIDATE_URL))
		{
			$result = new Result();
			$result->addError(
				new Error(Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_ERROR_NO_TARGET_URL'))
			);

			return $result;
		}

		$utmMarks = [
			['CODE' => 'utm_source', 'VALUE' => 'b24_sender_'.static::CODE],
			['CODE' => 'utm_medium', 'VALUE' => 'ads'],
			['CODE' => 'utm_campaign', 'VALUE' => $campaignName]
		];

		if (!mb_strpos($targetUrl, 'b24_sender_'.static::CODE))
		{
			$preparedMarks = [];
			foreach($utmMarks as $utmMark)
			{
				$preparedMarks[$utmMark['CODE']] = $utmMark['VALUE'];
			}

			$config->getOption('TARGET_URL')->setValue(sprintf('%s/?%s',$targetUrl, http_build_query($preparedMarks)));
		}

		return Entity\Message::create()
			->setCode($this->getCode())
			->setUtm($utmMarks)
			->saveConfiguration($this->configuration);
	}

	/**
	 * Remove configuration.
	 *
	 * @param integer $id ID.
	 * @return bool
	 */
	public function removeConfiguration($id)
	{
		$result = Entity\Message::removeById($id);
		return $result->isSuccess();
	}

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return \Bitrix\Main\Result|null
	 */
	public function copyConfiguration($id)
	{
		return Entity\Message::create()
			->setCode($this->getCode())
			->copyConfiguration($id);
	}

	public static function checkSelf($type)
	{
		return in_array($type, [iMarketing::CODE_FACEBOOK, iMarketing::CODE_INSTAGRAM]);
	}

	public function onBeforeStart()
	: \Bitrix\Main\Result
	{
		$result = new EventResult();
		$result->setSuccess(true);
		return $result;
	}

	public function onAfterEnd()
	: \Bitrix\Main\Result
	{
		$result = new EventResult();

		$config = $this->configuration;
		$clientId = $config->get('CLIENT_ID');
		$accountId = $config->get('ACCOUNT_ID');
		$instagramActorId = $config->get('INSTAGRAM_ACTOR_ID');
		$permalink = $config->get('PERMALINK');
		$targetUrl = $config->get('TARGET_URL');
		$campaignId = $config->get('CAMPAIGN_ID');
		$adSetId = $config->get('AD_SET_ID');
		$creativeId = $config->get('CREATIVE_ID');
		$body = $config->get('BODY');
		$budget = $config->get('BUDGET');
		$duration = $config->get('DURATION');
		$adsId = $config->get('ADS_ID');
		$pageId = $config->get('PAGE_ID');
		$title = $config->get('TITLE');
		$imageUrl = $config->get('IMAGE_URL');
		$audienceId = $config->get('AUDIENCE_ID');
		$mediaId = $config->get('MEDIA_ID');
		$phoneAudienceId = $config->get('AUDIENCE_PHONE_ID');
		$emailAudienceId = $config->get('AUDIENCE_EMAIL_ID');
		$ageFrom = $config->get('AGE_FROM');
		$ageTo = $config->get('AGE_TO');
		$genders = $config->get('GENDERS') ? Json::decode($config->get('GENDERS')) : [];
		$interests = $config->get('INTERESTS') ? Json::decode($config->get('INTERESTS')) : [];
		$regions = $config->get('REGIONS') ? Json::decode($config->get('REGIONS')) : [];
		$service = Marketing\Configurator::getService();
		$service->setClientId($clientId);

		$response = Marketing\Configurator::createCampaign(
			Marketing\Services\AdCampaignFacebook::TYPE_CODE,
			[
				'accountId'          => $accountId,
				'instagramAccountId' => $instagramActorId,
				'name'               => $title,
				'permalink'          => $permalink,
				'targetUrl'          => $targetUrl,
				'mediaId'            => $mediaId,
				'imageUrl'           => $imageUrl,
				'audience'           => $audienceId,
				'campaignId'         => $campaignId,
				'adSetId'            => $adSetId,
				'creativeId'         => $creativeId,
				'audienceId'         => $audienceId,
				'phoneAudienceId'    => $phoneAudienceId,
				'emailAudienceId'    => $emailAudienceId,
				'regions'            => $regions,
				'interests'          => $interests,
				'genders'            => $genders,
				'ageTo'              => $ageTo,
				'ageFrom'            => $ageFrom,
				'body'               => $body,
				'budget'             => ($budget?: 100) * 100,
				'duration'           => $duration,
				'type'               => static::CODE,
				'adsId'              => $adsId,
				'pageId'             => $pageId,
				'status'             => self::STATUS_ACTIVE
			]
		);

		$config->set('ADS_ID', $response['adsId']);
		$config->set('CREATIVE_ID', $response['creativeId']);
		$config->set('CAMPAIGN_ID', $response['campaignId']);
		$config->set('AD_SET_ID', $response['adSetId']);
		$config->set('STATUS', self::STATUS_ACTIVE);

		$this->saveConfiguration($config);
		$result->setSuccess(true);

		if (isset($response['RESULT']))
		{
			$responseResult = json_decode($response['RESULT'], true);
			$result->setSuccess(false);

			$errors[] = new \Bitrix\Main\Error($responseResult['error']['message']?? '');
			$result->addErrors($errors);

		}
		$result->setSuccess(true);
		return $result;
	}
}