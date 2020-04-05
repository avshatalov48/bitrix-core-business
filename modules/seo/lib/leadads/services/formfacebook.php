<?

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;

use Bitrix\Seo\LeadAds;
use Bitrix\Seo\WebHook;

class FormFacebook extends LeadAds\Form
{
	const TYPE_CODE = LeadAds\Service::TYPE_FACEBOOK;

	const URL_FORM_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'LOCALE' => 'LOCALE',
	);

	protected function getLocaleByLanguageId($languageId = null)
	{
		if (!$languageId)
		{
			$languageId = Context::getCurrent()->getLanguage();
		}

		switch ($languageId)
		{
			case 'en':
				$locale = 'en_US';
				break;

			case 'ru':
			case 'kz':
			case 'ua':
			case 'by':
				$locale = 'ru_RU';
				break;

			case 'pl':
			case 'fr':
			case 'it':
			case 'tr':
			case 'de':
			case 'es':
				$locale = strtolower($languageId) . '_' . strtoupper($languageId);
				break;

			case 'la':
				$locale = 'es_LA';
				break;

			case 'br': // Brazilian
				$locale = 'pt_BR';
				break;

			case 'sc': // simplified Chinese
				$locale = 'zh_CN';
				break;

			case 'tc': // traditional Chinese
				$locale = 'zh_TW';
				break;

			default:
				$locale = 'en_US';
				break;
		}

		return $locale;
	}

	/**
	 * Convert field.
	 *
	 * @param LeadAds\Field $field Field.
	 * @return array
	 */
	public static function convertField(LeadAds\Field $field)
	{
		$map = [
			['CRM_NAME' => 'COMPANY_NAME', 'ADS_NAME' => 'COMPANY_NAME'],
			['CRM_NAME' => 'NAME', 'ADS_NAME' => 'FIRST_NAME'],
			['CRM_NAME' => 'LAST_NAME', 'ADS_NAME' => 'LAST_NAME'],
			['CRM_NAME' => 'EMAIL', 'ADS_NAME' => 'EMAIL'],
			['CRM_NAME' => 'PHONE', 'ADS_NAME' => 'PHONE'],
		];
		$mapper = new LeadAds\Mapper($map);
		$adsName = $mapper->getAdsName($field->getName());
		if ($adsName)
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
				function ($option)
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
	public function add(array $data)
	{
		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#create-forms
		$locale = isset($data['LOCALE']) ? $data['LOCALE'] : $this->getLocaleByLanguageId();
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


		$account = $this->service->getAccount(static::TYPE_CODE);
		/** @var AccountFacebook $account */
		$accountData = $account->getRowById($this->accountId);

		$requestParameters = array(
			'method' => 'POST',
			'endpoint' => $this->accountId . '/leadgen_forms',
			'fields' => array(
				'access_token' => $accountData['ACCESS_TOKEN'],
				'name' => Encoding::convertEncoding(
					$data['NAME'],
					Context::getCurrent()->getCulture()->getCharset(),
					'UTF-8'
				),
				'privacy_policy' => Json::encode($privacyPolicy),
				'follow_up_action_url' => $data['SUCCESS_URL'],
				'locale' => strtoupper($locale),
				'context_card' => Json::encode($contextCard),
				'questions' => Json::encode($questions)
			)
		);
		$response = $this->getRequest()->send($requestParameters);

		$responseData = $response->getData();
		if (isset($responseData['id']))
		{
			if (!$this->subscribeAppToPageEvents($accountData['ACCESS_TOKEN']))
			{
				$response->addError(new Error('Can not subscribe App to Page events.'));
				return $response;
			}

			if(!static::registerFormWebHook($responseData['id']))
			{
				$response->addError(new Error('Can not register Form web hook.'));
				return $response;
			}

			$response->setId($responseData['id']);
		}

		return $response;
	}

	/**
	 * Unlink.
	 *
	 * @param string $id ID.
	 * @return bool
	 */
	public function unlink($id)
	{
		return static::removeFormWebHook($id);
	}

	protected function subscribeAppToPageEvents($pageAccessToken)
	{
		$response = $this->getRequest()->send(array(
			'method' => 'POST',
			'endpoint' => $this->accountId . '/subscribed_apps',
			'fields' => array(
				'access_token' => $pageAccessToken
			)
		));
		return $response->isSuccess();
	}

	public function getList()
	{
		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#readingforms

		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => $this->accountId . '/leadgen_forms',
			'fields' => array(
				//'fields' => 'id,name,approximate_count'
			)
		));

		return $response;
	}

	/**
	 * Get result.
	 *
	 * @param WebHook\Payload\LeadItem $item Payload item instance.
	 * @return LeadAds\Result
	 */
	public function getResult(WebHook\Payload\LeadItem $item)
	{
		$id = $item->getLeadId();
		$result = new LeadAds\Result();

		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#readingforms
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => $id,
			'fields' => array()
		));
		if (!$response->isSuccess())
		{
			foreach ($response->getErrors() as $error)
			{
				$result->addError(new Error('Can not retrieve result. ' . $error->getMessage()));
			}

			return $result;
		}

		$responseData = $response->getData();
		if (!$responseData)
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

		$result->setId($id);
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
}