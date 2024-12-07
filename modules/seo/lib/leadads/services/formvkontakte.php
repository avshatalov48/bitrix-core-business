<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Service;
use Bitrix\Seo\WebHook;
use Bitrix\Seo\LeadAds\Response\FormResponse;
use Bitrix\Seo\LeadAds\Response\Builder\VkontakteFormBuilder;


/**
 * Class FormVkontakte
 *
 * @package Bitrix\Seo\LeadAds\Services
 */
class FormVkontakte extends LeadAds\Form
{
	public const TYPE_CODE = LeadAds\Service::TYPE_VKONTAKTE;

	public const LIMIT_DEFAULT = 20;
	public const LIMIT_MAX = 50;
	protected const STATUS_FORM_ACTIVE = 1;

	public const URL_FORM_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	public const USE_GROUP_AUTH = true;
	public const FIELD_MAP = [
		['CRM_NAME' => LeadAds\Field::TYPE_NAME, 'ADS_NAME' => 'first_name'],
		['CRM_NAME' => LeadAds\Field::TYPE_LAST_NAME, 'ADS_NAME' => 'last_name'],
		['CRM_NAME' => LeadAds\Field::TYPE_EMAIL, 'ADS_NAME' => 'email'],
		['CRM_NAME' => LeadAds\Field::TYPE_PHONE, 'ADS_NAME' => 'phone'],
		['CRM_NAME' => LeadAds\Field::TYPE_BIRTHDAY, 'ADS_NAME'=>'birth_date'],
		['CRM_NAME' => LeadAds\Field::TYPE_AGE, 'ADS_NAME'=>'age'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_FULL, 'ADS_NAME'=>'location'],
		['CRM_NAME' => LeadAds\Field::TYPE_PATRONYMIC_NAME, 'ADS_NAME'=>'patronymic_name'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_COUNTRY, 'ADS_NAME'=>'country'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_CITY, 'ADS_NAME'=>'city'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_STREET_ADDRESS, 'ADS_NAME'=>'address'],
		['CRM_NAME' => LeadAds\Field::TYPE_INPUT, 'ADS_NAME'=>'question'],
	];

	protected static $fieldKeyPrefix = 'b24-seo-ads-';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'LOCALE' => 'LOCALE',
	);

	protected function getAuthParameters() : array
	{
		$row = LeadAds\Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
			]
		]);

		return [
			'URL_PARAMETERS' => ['group_ids' => $row['GROUP_ID']]
		];
	}

	/**
	 * Convert field.
	 *
	 * @param LeadAds\Field $field Field.
	 * @return array
	 */
	public static function convertField(LeadAds\Field $field) : array
	{
		$mapper = static::getFieldMapper();
		$adsName = $mapper->getAdsName($field->getName());
		if ($adsName)
		{
			return [
				'type' => $adsName,
				//'key' => $field->getKey()
			];
		}

		$item = [
			'type' => $field->getType(),
			'key' => $field->getKey(),
			'label' => $field->getLabel(),
		];

		if (!empty($field->getOptions()))
		{
			$item['options'] = array_map(
				static function ($option)
				{
					if (is_numeric($key = $option['key']))
					{
						$key = static::$fieldKeyPrefix . $key;
					}

					return [
						'label' => $option['label'],
						'key' => $key
					];
				},
				$field->getOptions()
			);
		}

		return $item;
	}

	/**
	 * @param array $data
	 *
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws SystemException
	 */
	public function add(array $data) : Response
	{
		return new Retargeting\Services\ResponseVkontakte();
	}

	/**
	 * @throws ArgumentNullException
	 * @throws SystemException
	 */
	public function getForm($formId) : LeadAds\Response\FormResponse
	{
		/**@var Retargeting\Services\ResponseVkontakte $response */
		$response = $this->getRequest()->send([
			'methodName'=>'leadads.form.get',
			'parameters'=>[
				'form_id'=> (int) $formId,
				'group_id'=> (int) $this->accountId,
			]
		]);

		return new FormResponse(
			new VkontakteFormBuilder($this::getFieldMapper()),
			$response
		);
	}

	/**
	 * Unlink
	 *
	 * @param string $id ID.
	 *
	 * @return bool
	 */
	public function unlink(string $id) : bool
	{
		return $this->removeFormWebHook($this->accountId);
	}

	protected function registerGroupWebHook(): Retargeting\Services\ResponseVkontakte
	{
		$response = new Retargeting\Services\ResponseVkontakte();

		$confirmationCodeResponse = $this->getCallbackConfirmationCode();
		if ($confirmationCodeResponse instanceof Error)
		{
			$response->addError(
				$confirmationCodeResponse
			);

			return $response;
		}

		$isRegistered = $this->registerFormWebHook(
			$this->accountId,
			array(
				'SECURITY_CODE' => $secretKey = Random::getString(32),
				'CONFIRMATION_CODE' => $confirmationCodeResponse,
			)
		);

		if (!$isRegistered)
		{
			$response->addError(new Error('Can not register Form web hook.'));

			return $response;
		}

		$callbackServiceResponse = $this->addCallbackServer($secretKey);
		if ($callbackServiceResponse instanceof Error)
		{
			$response->addError($callbackServiceResponse);
		}

		return $response;
	}

	/**
	 * @param string $secretKey
	 *
	 * @return Error|mixed
	 */
	protected function addCallbackServer(string $secretKey)
	{

		$row = LeadAds\Internals\CallbackSubscriptionTable::getRow(
			array(
				'select' => ['ID','CALLBACK_SERVER_ID'],
				'filter' => [
					'=TYPE' => static::TYPE_CODE,
					'=GROUP_ID' => $this->accountId
				]
			)
		);

		if (!$row)
		{
			return new Error("Group is not registred.");
		}

		if (!empty($row['CALLBACK_SERVER_ID']))
		{
			return $row['CALLBACK_SERVER_ID'];
		}

		$responseSetCallbackSettings = $this->getRequest()->send([
			'methodName' => 'leadads.callback.server.settings.set',
			'parameters' => [
				'group_id' => $this->accountId,
				'lead_forms_new' => 1,
			]
		]);

		if (!$responseSetCallbackSettings->isSuccess() || 1 !== current($responseSetCallbackSettings->getData()))
		{
			return new Error('Can not set Callback server settings.');
		}

		return true;
	}

	protected function deleteCallbackServer($groupId): void
	{
		$row = LeadAds\Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
				'=GROUP_ID' => $groupId
			]
		]);

		if ($row && !empty($row['CALLBACK_SERVER_ID']))
		{
			$this->getRequest()->send([
				'methodName' => 'leadads.callback.server.delete',
				'parameters' => [
					'group_id' => $groupId,
					'server_id' => $row['CALLBACK_SERVER_ID'],
				]
			]);
		}
	}

	/**
	 * @return string|Error
	 * @throws SystemException
	 */
	protected function getCallbackConfirmationCode()
	{
		$response = $this->getRequest()->send([
			'methodName' => 'leadads.callback.server.code.get',
			'parameters' => [
				'group_id' => $this->accountId,
			]
		]);

		if (!$response->isSuccess())
		{
			return new Error('Can not get confirmation code for Callback server.');
		}

		$responseData = $response->getData();

		return empty($responseData['code'])
				? new Error('Can not get confirmation code for Callback server.')
				: $responseData['code']
			;
	}

	/**
	 * Get list.
	 *
	 * @return FormResponse
	 * @throws SystemException
	 */
	public function getList() : FormResponse
	{
		$limit = self::LIMIT_DEFAULT;
		$offset = 0;
		$result = [];
		/**@var Retargeting\Services\ResponseVkontakte $response */
		while (true)
		{
			$response = $this->getRequest()->send([
				'methodName' => 'leadads.form.list',
				'parameters' => [
					'limit' => $limit,
					'offset' => $offset,
				]
			]);
			$items = array_filter($response->getData(), fn ($item) => ((int)$item['status'] === self::STATUS_FORM_ACTIVE));
			$result = array_merge($result, $items);

			if (count($response->getData()) < $limit)
			{
				$response->setData($result);
				break;
			}

			if ($limit < self::LIMIT_MAX)
			{
				$limit = self::LIMIT_MAX;
			}

			$offset += count($response->getData());
		}

		return new FormResponse(
			new VkontakteFormBuilder($this::getFieldMapper()),
			$response
		);

	}

	/**
	 * Get result.
	 *
	 * @param WebHook\Payload\LeadItem $item Payload item instance.
	 * @return LeadAds\Result
	 */
	public function getResult(WebHook\Payload\LeadItem $item) : LeadAds\Result
	{
		$result = new LeadAds\Result();
		$result->setId($item->getLeadId());
		foreach ($item->getAnswers() as $key => $values)
		{
			foreach ($values as $index => $value)
			{
				if (mb_strpos($value, static::$fieldKeyPrefix) !== 0)
				{
					continue;
				}

				$values[$index] = mb_substr($value, mb_strlen(static::$fieldKeyPrefix));
			}

			$result->addFieldValues($key, $values);
		}

		return $result;
	}

	/**
	 * UnRegister group.
	 *
	 * @param string $groupId Group ID.
	 *
	 * @return bool
	 */
	public function unRegisterGroup(string $groupId) : bool
	{
		$this->deleteCallbackServer($groupId);

		return parent::unRegisterGroup($groupId);
	}

	/**
	 * @param string $formId ads-form ID
	 *
	 * @return Retargeting\Response
	 */
	public function register($formId): Retargeting\Response
	{
		if (!isset($formId))
		{
			return (new Retargeting\Services\ResponseVkontakte())
				->addError(new Error('VK lead ads form register: Empty formId.'))
			;
		}
		if (!isset($this->accountId))
		{
			return (new Retargeting\Services\ResponseVkontakte())
				->addError(new Error('VK lead ads form register: Empty accountId.'))
			;
		}

		$isRegister = $this->registerForm($formId);
		if (!$isRegister)
		{
			return (new Retargeting\Services\ResponseVkontakte())
				->addError(new Error('VK lead ads form register: Empty formId.'))
			;
		}

		return new Retargeting\Services\ResponseVkontakte();
	}

	public function loadLeads($formId): Result
	{
		if (!isset($formId) || !isset($this->accountId))
		{
			return (new Result())
				->addError(new Error('Can not load leads'));
		}

		$limit = self::LIMIT_DEFAULT;
		$response = $this->loadLeadsByForm($formId,
			[
				'limit' => $limit,
				'offset' => 0,
			]
		);

		if (!$response->isSuccess())
		{
			return (new Result())
				->addError(new Error('Can not load leads'));
		}

		$leads = $response->getData();
		if (count($leads) === $limit)
		{
			$limit = self::LIMIT_MAX;
			while (true)
			{
				$response = $this->loadLeadsByForm($formId, [
					'limit' => $limit,
					'offset' => count($leads),
				]);

				if (!$response->isSuccess())
				{
					break;
				}

				$leads = array_merge($leads, $response->getData());

				if (count($response->getData()) < $limit)
				{
					break;
				}
			}
		}

		return (new Result())->setData($leads);
	}

	protected function loadLeadsByForm(int $formId, array $params): Response
	{
		$response = $this->getRequest()->send([
			'methodName' => 'leadads.form.leads.load',
			'parameters' => [
				'form_id' => $formId,
				'limit' => $params['limit'],
				'offset' => $params['offset'],
			]
		]);

		if ($response->isSuccess())
		{
			return $response;
		}

		return new Retargeting\Services\ResponseVkontakte();
	}

	protected function deleteLinkForm($formId)
	{

	}
}
