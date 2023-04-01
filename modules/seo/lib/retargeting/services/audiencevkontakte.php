<?php

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Seo\Retargeting\AdsAudience;
use \Bitrix\Seo\Retargeting\Audience;
use \Bitrix\Seo\Retargeting\Response;

class AudienceVkontakte extends Audience
{
	const TYPE_CODE = 'vkontakte';

	const MAX_CONTACTS_PER_PACKET = 1000;
	const MIN_CONTACTS_FOR_ACTIVATING = 20;
	const URL_AUDIENCE_LIST = 'https://vk.com/ads?act=retargeting';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'COUNT_VALID' => 'AUDIENCE_COUNT',
		'COUNT_MATCHED' => 'AUDIENCE_COUNT',
		'SUPPORTED_CONTACT_TYPES' => array(
			self::ENUM_CONTACT_TYPE_EMAIL,
			self::ENUM_CONTACT_TYPE_PHONE,
			self::ENUM_CONTACT_TYPE_IDFA_GAID,
			self::ENUM_CONTACT_TYPE_INTERNAL_ID
		),
	);

	public static function isSupportRemoveContacts()
	{
		return false;
	}

	public function add(array $data)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'name' => $data['NAME'],
				'description' => ''
			)
		));

		$responseData = $response->getData();
		if (isset($responseData['id']))
		{
			$response->setId($responseData['id']);
		}

		return $response;
	}

	protected function prepareContacts(array $contacts = array())
	{
		$data = array();
		foreach (static::$listRowMap['SUPPORTED_CONTACT_TYPES'] as $contactType)
		{
			if (!isset($contacts[$contactType]))
			{
				continue;
			}

			$data[] = implode(',', $contacts[$contactType]);
		}

		return implode(',', $data);
	}

	public function importContacts($audienceId, array $contacts, array $options)
	{

		return $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.contacts.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $audienceId,
				'contacts' => $this->prepareContacts($contacts)
			)
		));
	}

	public function removeContacts($audienceId, array $contacts, array $options)
	{
		$response = Response::create(static::TYPE_CODE);
		return $response;
	}

	public function getList()
	{
		return $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.list',
			'parameters' => array(
				'accountId' => $this->accountId
			)
		));
	}

	public static function isSupportAddAudience()
	{
		return true;
	}

	public function getLookalikeAudiencesParams()
	{
		$sizes = [];
		for ($i=1; $i<10;$i++)
		{
			$sizes[$i] = $i;
		}
		return [
			'FIELDS' => ['AUDIENCE_SIZE'],
			'SIZES' => $sizes
		];
	}

	public function createLookalike($sourceAudienceId, array $options)
	{
		$result = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.lookalike.request.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $sourceAudienceId,
			)
		));
		if (!$result->isSuccess())
		{
			return $result;
		}

		$data = $result->getData();
		if ($data['request_id'])
		{
			$result->setId($data['request_id']);
			$this->addLookalikeAudienceAgent($data['request_id'], $options['AUDIENCE_SIZE']);
		}
		return $result;
	}


	protected function addLookalikeAudienceAgent($audienceRequestId, $audienceSize)
	{
		\CAgent::AddAgent($this->getLookalikeAudienceAgentName($audienceRequestId, $audienceSize), 'seo', 'N', 60);
	}

	protected function getLookalikeAudienceAgentName($audienceRequestId, $audienceSize)
	{
		$clientId = $this->service->getClientId();
		$clientId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$clientId);
		$accountId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$this->accountId);
		$audienceRequestId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$audienceRequestId);
		$audienceSize = (int)$audienceSize;

		return __CLASS__ . '::processLookalikeAudienceAgent("'.$clientId.'", "'.$accountId.'", "' . $audienceRequestId . '", "'.$audienceSize.'");';
	}

	public static function processLookalikeAudienceAgent($clientId, $accountId, $audienceRequestId, $audienceSize)
	{
		$service = AdsAudience::getService();
		$service->setClientId($clientId);

		$audience = new static($accountId);
		$audience->setService($service);

		$result = $audience->getRequest()->send(array(
			'methodName' => 'retargeting.audience.lookalike.request.get',
			'parameters' => array(
				'accountId' => $accountId,
				'requestId' => $audienceRequestId,
			)
		));
		if (!$result->isSuccess())
		{
			return '';
		}

		$data = $result->getData();
		$audienceRequest = array_filter($data['items'],
			function ($item) use ($audienceRequestId)
			{
				return $audienceRequestId == $item['id'];
			}
		);

		if (empty($audienceRequest))
		{
			return '';
		}

		$audienceRequest = array_shift($audienceRequest);

		if ($audienceRequest['status'] == 'search_in_progress') // not processed yet
		{
			return $audience->getLookalikeAudienceAgentName($audienceRequestId, $audienceSize);
		}
		if ($audienceRequest['status'] == 'search_done') // processing complete
		{
			$audience->getRequest()->send(array(
				'methodName' => 'retargeting.audience.lookalike.add',
				'parameters' => array(
					'accountId' => $accountId,
					'requestId' => $audienceRequestId,
					'level' => $audienceSize
				)
			));
		}
		return '';
	}
}