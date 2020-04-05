<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Sender\Internals\Model\LetterTable;
use Bitrix\Sender\Message\EventResult;
use Bitrix\Sender\Message\iBeforeAfter;
use Bitrix\Sender\Message\iLookalikeAds;
use Bitrix\Seo\Retargeting\AdsAudience;
use Bitrix\Seo\Retargeting\Service;

/**
 * Class MessageLookalike
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
abstract class MessageLookalike extends MessageBase implements iLookalikeAds, iBeforeAfter
{
	public function onBeforeStart():\Bitrix\Main\Result
	{
		$result = new Result();
		$config = $this->loadConfiguration();

		$letter = LetterTable::getList([
			'filter' => ['=MESSAGE_ID' => $config->getId()],
			'select' => ['ID', 'TITLE']
		])->fetch();

		$service = AdsAudience::getService();
		$service->setClientId($config->get('CLIENT_ID'));

		$audienceId = AdsAudience::addAudience($this->getAdsType(), $config->get('ACCOUNT_ID'), $letter['TITLE']);
		if ($audienceId)
		{
			$config->set('AUDIENCE_ID', $audienceId);
			$this->saveConfiguration($this->configuration);
		}
		else
		{
			$result->addErrors(array_map(
				function ($errorMessage)
				{
					return new Error($errorMessage);
				},
				AdsAudience::getErrors())
			);
		}

		return $result;
	}

	public function onAfterEnd():\Bitrix\Main\Result
	{
		$result = new EventResult();

		$config = $this->configuration;

		$service = AdsAudience::getService();
		$service->setClientId($config->get('CLIENT_ID'));

		$audience = Service::getAudience($this->getAdsType());
		if ($audience->isQueueProcessed('sender:'.$config->getId()))
		{
			$audienceId = AdsAudience::addLookalikeAudience($this->getAdsType(), $config->get('ACCOUNT_ID'), $config->get('AUDIENCE_ID'), $this->getLookalikeOptions());
			if (!$audienceId)
			{
				$result->addErrors(array_map(
						function ($errorMessage)
						{
							return new Error($errorMessage);
						},
						AdsAudience::getErrors())
				);
				$result->setSuccess(true);
			}
		}
		else
		{
			$result->setSuccess(false); // disallow finishing the posting
		}
		return $result;
	}

	abstract public function getLookalikeOptions();
}