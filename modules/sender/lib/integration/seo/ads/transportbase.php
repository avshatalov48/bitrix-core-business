<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Seo\Retargeting;

use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Recipient;

/**
 * Class TransportBase
 * @package Bitrix\Sender\Integration\Seo\Ads;
 */
class TransportBase implements Transport\iBase
{
	const CODE = self::CODE_UNDEFINED;
	const CODE_ADS_VK = 'ads_vk';
	const CODE_ADS_FB = 'ads_fb';
	const CODE_ADS_YA = 'ads_ya';
	const CODE_ADS_GA = 'ads_ga';
	const CODE_ADS_LOOKALIKE_FB = 'ads_lookalike_fb';
	const CODE_ADS_LOOKALIKE_VK = 'ads_lookalike_vk';

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Retargeting\AdsAudienceConfig $adsConfig Ads config. */
	protected $adsConfig;

	/**
	 * Transport constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Ads';
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return static::CODE;
	}

	protected function getAdsType()
	{
		$map = Service::getTypeMap();
		return $map[$this->getCode()];
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return array(Recipient\Type::EMAIL, Recipient\Type::PHONE);
	}

	/**
	 * Get configuration.
	 *
	 * @return string
	 */
	public function loadConfiguration()
	{
		return $this->configuration;
	}

	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	public function start()
	{
		$authAdapter = Retargeting\Service::getAuthAdapter($this->getAdsType());
		if (!$authAdapter->hasAuth())
		{
			return false;
		}

		$this->adsConfig = new Retargeting\AdsAudienceConfig();

		return true;
	}

	public function send(Message\Adapter $message)
	{
		$config = $message->getConfiguration();
		$clientId = $config->get('CLIENT_ID');
		$audienceId = $config->get('AUDIENCE_ID');
		$audiencePhoneId = $config->get('AUDIENCE_PHONE_ID');
		$audienceEmailId = $config->get('AUDIENCE_EMAIL_ID');


		$adsContactType = null;
		switch (Recipient\Type::getId($message->getRecipientType()))
		{
			case Recipient\Type::EMAIL:
				$adsContactType = Retargeting\Audience::ENUM_CONTACT_TYPE_EMAIL;
				break;

			case Recipient\Type::PHONE:
				$adsContactType = Retargeting\Audience::ENUM_CONTACT_TYPE_PHONE;
				break;
		}


		$isSuccess = true;
		$audiences = array();
		if ($audienceId)
		{
			$audiences[] = array(
				'id' => $audienceId,
				'contactType' => $adsContactType
			);
		}
		if ($audiencePhoneId)
		{
			$audiences[] = array(
				'id' => $audiencePhoneId,
				'contactType' => Retargeting\Audience::ENUM_CONTACT_TYPE_PHONE
			);
		}
		if ($audienceEmailId)
		{
			$audiences[] = array(
				'id' => $audienceEmailId,
				'contactType' => Retargeting\Audience::ENUM_CONTACT_TYPE_EMAIL
			);
		}

		if (count($audiences) == 0)
		{
			$isSuccess = false;
		}

		if (!$isSuccess)
		{
			return false;
		}

		foreach ($audiences as $audience)
		{
			$this->adsConfig->accountId = $config->get('ACCOUNT_ID');
			$this->adsConfig->audienceId = $audience['id'];
			$this->adsConfig->contactType = $audience['contactType'];
			$this->adsConfig->type = $this->getAdsType();
			$this->adsConfig->autoRemoveDayNumber = $config->get('AUTO_REMOVE_DAY_NUMBER');
			$this->adsConfig->parentId = 'sender:'.$config->getId();

			if ($adsContactType !== $this->adsConfig->contactType)
			{
				continue;
			}

			$contacts[$adsContactType] = array($message->getRecipientCode());


			$service = Retargeting\AdsAudience::getService();
			$service->setClientId($clientId);
			Retargeting\AdsAudience::useQueue();
			$isSuccess = Retargeting\AdsAudience::addToAudience($this->adsConfig, $contacts);
		}

		return $isSuccess;
	}

	public function end()
	{

	}
}