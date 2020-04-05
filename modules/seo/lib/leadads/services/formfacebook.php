<?

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\LeadAds\Form;
use Bitrix\Seo\LeadAds\Result;

class FormFacebook extends Form
{
	const TYPE_CODE = 'facebook';

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

	public function add(array $data)
	{
		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#create-forms
		$locale = isset($data['LOCALE']) ? $data['LOCALE'] : $this->getLocaleByLanguageId();
		$questions = $data['FIELDS'];
		$privacyPolicy = array(
			'url' => $data['PRIVACY_POLICY_URL']
		);

		$privacyPolicy['url'] = 'http://www.1c-bitrix.ru/download/files/manuals/ru/privacy.htm';
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
	 * @param string $id ID.
	 * @return Result
	 */
	public function getResult($id)
	{
		// https://developers.facebook.com/docs/marketing-api/guides/lead-ads/create/v2.9#readingforms
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => $id,
			'fields' => array()
		));

		$result = new Result();
		$responseData = $response->getData();
		if (!isset($responseData['id']) || !$responseData['id'])
		{
			return $result;
		}
		if (!isset($responseData['field_data']) || !is_array($responseData['field_data']) || !$responseData['field_data'])
		{
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