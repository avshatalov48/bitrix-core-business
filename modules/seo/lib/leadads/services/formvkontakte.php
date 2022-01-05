<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
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

	public const URL_FORM_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	public const USE_GROUP_AUTH = true;
	public const FIELD_MAP = [
		['CRM_NAME' => LeadAds\Field::TYPE_NAME, 'ADS_NAME' => 'first_name'],
		['CRM_NAME' => LeadAds\Field::TYPE_LAST_NAME, 'ADS_NAME' => 'last_name'],
		['CRM_NAME' => LeadAds\Field::TYPE_EMAIL, 'ADS_NAME' => 'email'],
		['CRM_NAME' => LeadAds\Field::TYPE_PHONE, 'ADS_NAME' => 'phone_number'],
		['CRM_NAME' => LeadAds\Field::TYPE_BIRTHDAY, 'ADS_NAME'=>'birthday'],
		['CRM_NAME' => LeadAds\Field::TYPE_AGE, 'ADS_NAME'=>'age'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_FULL, 'ADS_NAME'=>'location'],
		['CRM_NAME' => LeadAds\Field::TYPE_PATRONYMIC_NAME, 'ADS_NAME'=>'patronymic_name'],
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
		$response = $this->registerGroupWebHook();
		if (!$response->isSuccess())
		{
			return $response;
		}
		// https://vk.com/dev/leadForms.create
		$questions = static::convertFields($data['FIELDS']);
		$privacyPolicy = self::getPrivacyPolicyUrl();
		$requestParameters = array(
			'methodName'=>'leadads.form.create',
			'parameters' => array(
				'group_id' => $this->accountId,
				'active' => 1,
				'name' => $this->encodeString($data['NAME'], 100),
				'title' => $this->encodeString($data['TITLE'] ?: ' ', 60),
				'description' => $this->encodeString($data['DESCRIPTION'] ?: ' ', 600),
				'policy_link_url' => mb_substr($privacyPolicy, 0, 200),
				'site_link_url' => mb_substr($data['SUCCESS_URL'], 0, 200),
				'questions' => Json::encode($questions)
			)
		);
		$response = $this->getRequest()->send($requestParameters);
		$responseData = $response->getData();
		if (!empty($responseData['form_id']))
		{
			$response->setId($responseData['form_id']);
		}

		return $response;
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

		$remoteServiceUrl = Service::SERVICE_URL;
		$webhookQueryParams = http_build_query(
			array(
				"code" => LeadAds\Service::getEngineCode(static::TYPE_CODE),
				"action" => "web_hook",
			)
		);
		$serverCreateResponse = $this->getRequest()->send([
			'methodName' => 'leadads.callback.server.add',
			'parameters' => [
				'group_id' => $this->accountId,
				'url' => "{$remoteServiceUrl}/register/index.php?{$webhookQueryParams}",
				'title' => 'Bitrix24 CRM',
				'secret_key' => $secretKey,
			]
		]);

		if (!$serverCreateResponse->isSuccess() || !$serverId = $serverCreateResponse->getData()['server_id'] ?? null)
		{
			return new Error('Can not add Callback server.');
		}

		$responseSetCallbackSettings = $this->getRequest()->send([
			'methodName' => 'leadads.callback.server.settings.set',
			'parameters' => [
				'group_id' => $this->accountId,
				'server_id' => $serverId,
				'lead_forms_new' => 1,
			]
		]);

		if (!$responseSetCallbackSettings->isSuccess() || 1 !== current($responseSetCallbackSettings->getData()))
		{
			return new Error('Can not set Callback server settings.');
		}

		$callbackSubscriptionDbResult = LeadAds\Internals\CallbackSubscriptionTable::update(
			$row['ID'],
			['CALLBACK_SERVER_ID' => $serverId]
		);

		if (!$callbackSubscriptionDbResult->isSuccess())
		{
			return new Error('Can not update Callback server table.');
		}


		return $serverId;
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

	protected function encodeString($text, $length = 60): string
	{
		$text = Encoding::convertEncoding(
			$text,
			Context::getCurrent()->getCulture()->getCharset(),
			'UTF-8'
		);

		return mb_substr($text, 0, $length);
	}

	/**
	 * Get list.
	 *
	 * @return FormResponse
	 * @throws SystemException
	 */
	public function getList() : FormResponse
	{
		/**@var Retargeting\Services\ResponseVkontakte $response*/
		$response = $this->getRequest()->send([
			'methodName'=>'leadads.form.list',
			'parameters'=>[
				'group_id' => $this->accountId,
			]
		]);

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
	 * @return bool
	 */
	public function register($formId) : bool
	{
		return isset($formId,$this->accountId) && $this->registerGroupWebHook()->isSuccess();
	}
}