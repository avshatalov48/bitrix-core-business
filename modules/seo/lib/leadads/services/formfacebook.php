<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\SystemException;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\Paginator;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting\Services\ResponseFacebook;
use Bitrix\Seo\LeadAds\Response\FormResponse;
use Bitrix\Seo\LeadAds\Response\Builder\FacebookFormBuilder;
use Bitrix\Seo\WebHook;

class FormFacebook extends LeadAds\Form
{
	public const TYPE_CODE = LeadAds\Service::TYPE_FACEBOOK;

	public const URL_FORM_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	public const FIELD_MAP = [
		['CRM_NAME' => LeadAds\Field::TYPE_COMPANY_NAME, 'ADS_NAME' => 'COMPANY_NAME'],
		['CRM_NAME' => LeadAds\Field::TYPE_NAME, 'ADS_NAME' => 'FIRST_NAME'],
		['CRM_NAME' => LeadAds\Field::TYPE_LAST_NAME, 'ADS_NAME' => 'LAST_NAME'],
		['CRM_NAME' => LeadAds\Field::TYPE_EMAIL, 'ADS_NAME' => 'EMAIL'],
		['CRM_NAME' => LeadAds\Field::TYPE_PHONE, 'ADS_NAME' => 'PHONE'],
		['CRM_NAME' => LeadAds\Field::TYPE_WORK_EMAIL, 'ADS_NAME' => 'WORK_EMAIL'],
		['CRM_NAME' => LeadAds\Field::TYPE_WORK_PHONE, 'ADS_NAME' => 'WORK_PHONE_NUMBER'],
		['CRM_NAME' => LeadAds\Field::TYPE_JOB_TITLE, 'ADS_NAME' => 'JOB_TITLE'],
		['CRM_NAME' => LeadAds\Field::TYPE_MILITARY_STATUS, 'ADS_NAME' => 'MILITARY_STATUS'],
		['CRM_NAME' => LeadAds\Field::TYPE_MARITIAL_STATUS, 'ADS_NAME' => 'MARITIAL_STATUS'],
		['CRM_NAME' => LeadAds\Field::TYPE_GENDER, 'ADS_NAME' => 'GENDER'],
		['CRM_NAME' => LeadAds\Field::TYPE_BIRTHDAY, 'ADS_NAME' => 'DOB'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_COUNTRY, 'ADS_NAME' => 'COUNTRY'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_STATE, 'ADS_NAME' => 'STATE'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_CITY, 'ADS_NAME' => 'CITY'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_STREET_ADDRESS, 'ADS_NAME' => 'STREET_ADDRESS'],
		['CRM_NAME' => LeadAds\Field::TYPE_FULL_NAME, 'ADS_NAME' => 'FULL_NAME'],
		['CRM_NAME' => LeadAds\Field::TYPE_LOCATION_ZIP, 'ADS_NAME' => 'ZIP'],
		['CRM_NAME' => LeadAds\Field::TYPE_RELATIONSHIP_STATUS, 'ADS_NAME' => 'RELATIONSHIP_STATUS'],
		['CRM_NAME' => LeadAds\Field::TYPE_CPF, 'ADS_NAME' => 'ID_CPF'],
		['CRM_NAME' => LeadAds\Field::TYPE_DNI_ARGENTINA, 'ADS_NAME' => 'ID_AR_DNI'],
		['CRM_NAME' => LeadAds\Field::TYPE_DNI_PERU, 'ADS_NAME' => 'ID_PE_DNI'],
		['CRM_NAME' => LeadAds\Field::TYPE_RUT, 'ADS_NAME' => 'ID_CL_RUT'],
		['CRM_NAME' => LeadAds\Field::TYPE_CC, 'ADS_NAME' => 'ID_CO_CC'],
		['CRM_NAME' => LeadAds\Field::TYPE_CI, 'ADS_NAME' => 'ID_EC_CI'],
		['CRM_NAME' => LeadAds\Field::TYPE_DATE_TIME, 'ADS_NAME' => 'DATE_TIME'],
	];

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'LOCALE' => 'LOCALE',
	);

	protected function getLocaleByLanguageId($languageId = null): string
	{
		switch ($languageId = $languageId ?? Context::getCurrent()->getLanguage())
		{
			case 'ru':
			case 'kz':
			case 'ua':
			case 'by':
				return 'ru_RU';
			case 'pl':
			case 'fr':
			case 'it':
			case 'tr':
			case 'de':
			case 'es':
				return mb_strtolower($languageId).'_'.mb_strtoupper($languageId);
			case 'la':
				return 'es_LA';
			case 'br': // Brazilian
				return 'pt_BR';
			case 'sc': // simplified Chinese
				return 'zh_CN';
			case 'tc': // traditional Chinese
				return 'zh_TW';
			default:
				return'en_US';
		}
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

		if ($adsName = $mapper->getAdsName($field->getName()))
		{
			return ['type' => $adsName, 'key' => $field->getKey()];
		}

		$item = [
			'type' => 'CUSTOM',
			'label' => $field->getLabel(),
			'key' => $field->getKey()
		];
		if (!empty($field->getOptions()))
		{
			$item['options'] = array_map(
				static function ($option)
				{
					return [
						'value' => $option['label'],
						'key' => $option['key']
					];
				},
				$field->getOptions()
			);
		}

		return $item;
	}

	/**
	 * Add.
	 *
	 * @param array $data Data.
	 * @return \Bitrix\Seo\Retargeting\Response
	 */
	public function add(array $data) : Response
	{
		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#create-forms
		$locale = $data['LOCALE'] ?? $this->getLocaleByLanguageId();
		$questions = static::convertFields($data['FIELDS']);
		$privacyPolicy = array(
			'url' => $data['PRIVACY_POLICY_URL']
		);

		$privacyPolicy['url'] = self::getPrivacyPolicyUrl();
		$contextCard = [
			'style' => 'PARAGRAPH_STYLE',
			'content' => [' '],
			'button_text' => $data['BUTTON_CAPTION']
		];
		if ($data['TITLE'])
		{
			$contextCard['title'] = $data['TITLE'];
		}
		if ($data['DESCRIPTION'])
		{
			$contextCard['content'] = [$data['DESCRIPTION']];
		}
		elseif ($data['TITLE'])
		{
			$contextCard['content'] = [$data['TITLE']];
		}

		$response = $this->getRequest()->send([
			'methodName' => 'leadads.form.create',
			'parameters' => [
				'page_id'=> $this->accountId,
				'params' => [
					'name' => $data['NAME'],
					'privacy_policy' => $privacyPolicy,
					'follow_up_action_url' => $data['SUCCESS_URL'],
					'locale' => mb_strtoupper($locale),
					'context_card' => $contextCard,
					'questions' => $questions
				]
			]
		]);

		if (!$response->isSuccess() || !$formId = $response->getData()["id"] ?? null)
		{
			return $response;
		}
		$response->setId($formId);

		$subscribeResult = $this->subscribeAppToPageEvents();
		if (!$subscribeResult->isSuccess())
		{
			$response->addError(new Error('Can not subscribe App to Page events.'));
			return $response;
		}

		if(!$this->registerFormWebHook($formId))
		{
			$response->addError(new Error('Can not register Form web hook.'));
			return $response;
		}

		return $response;
	}

	/**
	 * Unlink.
	 *
	 * @param string $id ID.
	 *
	 * @return bool
	 */
	public function unlink(string $id) : bool
	{
		return $this->removeFormWebHook($id);
	}

	protected function subscribeAppToPageEvents(): Retargeting\Services\ResponseFacebook
	{
		return $this->getRequest()->send(array(
			'methodName' => 'leadads.event.subscribe',
			'parameters' => [
				'page_id' => $this->accountId,
				'params' => [
					'subscribed_fields' => ['leadgen'],
				]
			]
		));
	}


	public function getList() : LeadAds\Response\FormResponse
	{
		$paginator = new Paginator(
			$this->getRequest(),
			array(
				'methodName' => 'leadads.form.list',
				'parameters' => [
					'page_id' => $this->accountId,
					'fields' => [
						'privacy_policy_url',
						'id',
						'context_card',
						'name',
						'status',
						'thank_you_page',
						'follow_up_action_url',
						'tracking_parameters',
						'questions'
					],
					'params' => [
						'limit' => 50
					]
				]
			)
		);

		return new FormResponse(
			new FacebookFormBuilder($this::getFieldMapper()),
			...iterator_to_array($paginator)
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

		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#readingforms
		$response = $this->getRequest()->send(array(
			'methodName' => 'leadads.lead.get',
			'parameters' => [
				'lead_id' => $item->getLeadId()
			]
		));

		if (!$response->isSuccess())
		{
			foreach ($response->getErrors() as $error)
			{
				$result->addError(new Error('Can not retrieve result. ' . $error->getMessage()));
			}

			return $result;
		}

		if (!$responseData = $response->getData())
		{
			$result->addError(new Error('Can not retrieve result. Empty data.'));

			return $result;
		}

		if (!isset($responseData['id']) || !$responseData['id'])
		{
			$result->addError(new Error('Can not retrieve result. Empty `id`.'));

			return $result;
		}

		if (!isset($responseData['field_data']) || !is_array($responseData['field_data']) || !$responseData['field_data'])
		{
			$result->addError(new Error('Can not retrieve result. Empty `field_data`.'));

			return $result;
		}

		$result->setId($item->getLeadId());
		foreach ($responseData['field_data'] as $field)
		{
			if (!isset($field['name']) || !$field['name'])
			{
				continue;
			}

			if (!isset($field['values']) || !$field['values'])
			{
				continue;
			}

			if (!is_array($field['values']))
			{
				$field['values'] = array($field['values']);
			}

			$result->addFieldValues($field['name'], $field['values']);
		}

		return $result;
	}


	/**
	 * @inheritDoc
	 *
	 * @params int|string|mixed $formId
	 *
	 * @throws ArgumentException|SystemException
	 */
	public function getForm($formId) : LeadAds\Response\FormResponse
	{
		/**@var ResponseFacebook $response*/
		$response = $this->getRequest()->send([
			'methodName' => 'leadads.form.get',
			'parameters' => [
				'page_id' => $this->accountId,
				'form_id' => $formId,
				'fields' => [
					'privacy_policy_url',
					'id',
					'context_card',
					'name',
					'status',
					'thank_you_page',
					'follow_up_action_url',
					'tracking_parameters',
					'questions'
				],
			]
		]);

		return new FormResponse(
			new FacebookFormBuilder($this::getFieldMapper()),
			$response
		);
	}

	/**
	 * @param string|int|mixed $formId ads-form Id.
	 *
	 * @return Retargeting\Response
	 */
	public function register($formId): Retargeting\Response
	{
		if (!isset($formId))
		{
			return (new Retargeting\Services\ResponseFacebook())
				->addError(new Error('Facebook lead ads form register: Empty formId.'))
			;
		}
		if (!isset($this->accountId))
		{
			return (new Retargeting\Services\ResponseFacebook())
				->addError(new Error('Facebook lead ads form register: Empty accountId.'))
			;
		}

		$subscribeResult = $this->subscribeAppToPageEvents();
		if (!$subscribeResult->isSuccess())
		{
			return $subscribeResult;
		}

		$result = new Retargeting\Services\ResponseFacebook();
		if (!$this->registerFormWebHook($formId))
		{
			$result->addError(new Error('Can not register Form web hook.'));
		}

		return $result;
	}
}
