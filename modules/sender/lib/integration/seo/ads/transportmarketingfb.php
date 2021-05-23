<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Message;
use Bitrix\Sender\Recipient;
use Bitrix\Seo\Marketing;
use Bitrix\Seo\Marketing\AdsAudience;
use Bitrix\Seo\Marketing\AdsAudienceConfig;

Loc::loadMessages(__FILE__);

/**
 * Class TransportFb
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class TransportMarketingFb extends TransportBase
{
	const CODE = 'facebook';
	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_PAUSED = 'PAUSED';
	const STATUS_ARCHIVED = 'ARCHIVED';
	const STATUS_DELETED = 'DELETED';

	const SEND_STATES = [
		'AUDIENCE_CREATED' => 'AUDIENCE_CREATED',
		'AUDIENCE_UPLOADING' => 'AUDIENCE_UPLOADING',
		'AUDIENCE_UPLOADED'=>'AUDIENCE_UPLOADED'
	];

	/** @var AdsAudienceConfig $adsConfig Ads config. */
	protected $adsConfig;


	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;
	
	public function getName()
	{
		return 'Ads';
	}

	/**
	 * Transport constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}
	
	public function getCode()
	{
		return static::CODE;
	}

	public function getSupportedRecipientTypes()
	{
		return array(Recipient\Type::EMAIL, Recipient\Type::PHONE);
	}

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
		$authAdapter = Marketing\Service::getAuthAdapter(self::CODE);
		if (!$authAdapter->hasAuth())
		{
			return false;
		}

		$this->adsConfig = new Marketing\AdsAudienceConfig();

		return true;
	}

	public function send(Message\Adapter $message)
	{
		$config = $message->getConfiguration();
		$clientId = $config->get('CLIENT_ID');
		$accountId = $config->get('ACCOUNT_ID');
		$duration = $config->get('DURATION');
		$audienceId = $config->get('AUDIENCE_ID');
		$status = $config->get('STATUS');

		$service = Marketing\Configurator::getService();
		$service->setClientId($clientId);

		if(!$message->getRecipientCode())
		{
			$status = $config->set('STATUS', self::SEND_STATES['AUDIENCE_UPLOADED']);
			$message->saveConfiguration($config);
		}

		if (!$audienceId && $message->getRecipientCode())
		{
			$audiences = Marketing\Configurator::createAudience(
				Marketing\Services\AdCampaignFacebook::TYPE_CODE,
				[
					'accountId' => $accountId,
					'duration' => $duration
				]
			);
			$config->set('AUDIENCE_ID', $audiences['audienceId']);
			$config->set('AUDIENCE_PHONE_ID', $audiences['phoneAudienceId']);
			$config->set('AUDIENCE_EMAIL_ID', $audiences['emailAudienceId']);

			$status = $config->set('STATUS', self::SEND_STATES['AUDIENCE_CREATED']);
			$message->saveConfiguration($config);
		}

		if($status && in_array($status, [self::SEND_STATES['AUDIENCE_CREATED'],
				self::SEND_STATES['AUDIENCE_UPLOADING']]))
		{
			return parent::send($message);
		}
	}

	protected function addToAudience($clientId, $contacts)
	{
		$service = AdsAudience::getService();
		$service->setClientId($clientId);
		AdsAudience::useQueue();
		$this->adsConfig->type = Marketing\Services\AdCampaignFacebook::TYPE_CODE;

		return AdsAudience::addToAudience($this->adsConfig, $contacts);
	}

	public function end()
	{
	}
}