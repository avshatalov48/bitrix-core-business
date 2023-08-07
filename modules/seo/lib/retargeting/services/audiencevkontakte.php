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
	const URL_AUDIENCE_LIST = 'https://ads.vk.com/hq/audience/user_lists';

	const ENUM_CONTACT_TYPE_IDFA_GAID = 'gaid'; // IDFA (Identifier For Advertising) or device ID (Android ID and UDID on iOS)
	const ENUM_CONTACT_TYPE_INTERNAL_ID = 'vk';

	protected const USER_LIST_TYPE_HUMAN = 'human';

	protected static $listRowMap = [
		'ID' => 'ID',
		'NAME' => 'NAME',
		'COUNT_VALID' => 'AUDIENCE_COUNT',
		'COUNT_MATCHED' => 'AUDIENCE_COUNT',
		'SUPPORTED_CONTACT_TYPES' => [
			self::ENUM_CONTACT_TYPE_EMAIL,
			self::ENUM_CONTACT_TYPE_PHONE,
			self::ENUM_CONTACT_TYPE_IDFA_GAID,
			self::ENUM_CONTACT_TYPE_INTERNAL_ID
		],
	];

	public static function isSupportRemoveContacts()
	{
		return false;
	}

	public function add(array $data)
	{
		$response = $this->getRequest()->send([
			'methodName' => 'retargeting.audience.add',
			'parameters' => [
				'name' => $data['NAME'],
				'contacts' => [[
					'email' => 'example@example.com',
				]],
			]
		]);

		$responseData = $response->getData();
		if (isset($responseData['id']))
		{
			$response->setId($responseData['id']);
		}

		return $response;
	}

	protected function prepareContacts(array $contacts = [])
	{
		$data = [];
		foreach (static::$listRowMap['SUPPORTED_CONTACT_TYPES'] as $contactType)
		{
			if (!isset($contacts[$contactType]))
			{
				continue;
			}
			foreach ($contacts[$contactType] as $contact)
			{
				$data[] = [$contactType => $contact];
			}
		}

		return $data;
	}

	public function importContacts($audienceId, array $contacts, array $options)
	{
		return $this->getRequest()->send([
			'methodName' => 'retargeting.audience.contacts.add',
			'parameters' => [
				'audienceId' => $audienceId,
				'contacts' => $this->prepareContacts($contacts)
			]
		]);
	}

	public function removeContacts($audienceId, array $contacts, array $options)
	{
		return Response::create(static::TYPE_CODE);
	}

	public function getList()
	{
		$result = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.list',
			'parameters' => array(
				'accountId' => $this->accountId
			)
		));

		if ($result->isSuccess())
		{
			$list = [];
			$data = $result->getData();

			if (is_array($data['items']))
			{
				$list = array_values(array_filter($data['items'], function ($item) {
					return $item['type'] === self::USER_LIST_TYPE_HUMAN;
				}));
			}
			$result->setData($list);
		}

		return $result;
	}

	public static function isSupportAddAudience()
	{
		return true;
	}

	public function getLookalikeAudiencesParams()
	{
		return [];
		// $sizes = [];
		// for ($i=1; $i<10;$i++)
		// {
		// 	$sizes[$i] = $i;
		// }
		// return [
		// 	'FIELDS' => ['AUDIENCE_SIZE'],
		// 	'SIZES' => $sizes
		// ];
	}
	public static function isSupportCreateLookalikeFromSegments(): bool
	{
		return false;
	}

	// public function createLookalike($sourceAudienceId, array $options)
	// {
	// 	$result = $this->getRequest()->send(array(
	// 		'methodName' => 'retargeting.audience.lookalike.request.add',
	// 		'parameters' => array(
	// 			'accountId' => $this->accountId,
	// 			'audienceId' => $sourceAudienceId,
	// 		)
	// 	));
	// 	if (!$result->isSuccess())
	// 	{
	// 		return $result;
	// 	}
	//
	// 	$data = $result->getData();
	// 	if ($data['request_id'])
	// 	{
	// 		$result->setId($data['request_id']);
	// 		$this->addLookalikeAudienceAgent($data['request_id'], $options['AUDIENCE_SIZE']);
	// 	}
	// 	return $result;
	// }


	protected function addLookalikeAudienceAgent($audienceRequestId, $audienceSize)
	{
		// \CAgent::AddAgent($this->getLookalikeAudienceAgentName($audienceRequestId, $audienceSize), 'seo', 'N', 60);
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
