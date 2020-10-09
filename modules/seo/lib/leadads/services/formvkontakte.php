<?

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;

use Bitrix\Seo\LeadAds;
use Bitrix\Seo\WebHook;
use Bitrix\Seo\Retargeting;

/**
 * Class FormVkontakte
 *
 * @package Bitrix\Seo\LeadAds\Services
 */
class FormVkontakte extends LeadAds\Form
{
	const TYPE_CODE = LeadAds\Service::TYPE_VKONTAKTE;

	const URL_FORM_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	const USE_GROUP_AUTH = true;

	protected static $fieldKeyPrefix = 'b24-seo-ads-';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'LOCALE' => 'LOCALE',
	);

	protected function getAuthParameters()
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
	public static function convertField(LeadAds\Field $field)
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
				function ($option)
				{
					$key = $option['key'];
					if (is_numeric($key))
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

	protected static function getFieldMapper()
	{
		static $mapper = null;
		if ($mapper === null)
		{
			$map = [
				['CRM_NAME' => 'NAME', 'ADS_NAME' => 'first_name'],
				['CRM_NAME' => 'LAST_NAME', 'ADS_NAME' => 'last_name'],
				['CRM_NAME' => 'EMAIL', 'ADS_NAME' => 'email'],
				['CRM_NAME' => 'PHONE', 'ADS_NAME' => 'phone_number'],
			];
			$mapper = new LeadAds\Mapper($map);
		}

		return $mapper;
	}

	public function add(array $data)
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
			'method' => 'POST',
			'endpoint' => 'leadForms.create',
			'fields' => array(
				'group_id' => $this->accountId,
				'active' => 1,
				'name' => self::encodeString($data['NAME'], 100),
				'title' => self::encodeString($data['TITLE'] ?: ' ', 60),
				'description' => self::encodeString($data['DESCRIPTION'] ?: ' ', 600),
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
	 * Unlink
	 *
	 * @param string $id ID.
	 * @return bool
	 */
	public function unlink($id)
	{
		return static::removeFormWebHook($this->accountId);
	}

	protected function registerGroupWebHook()
	{
		$response = new Retargeting\Services\ResponseVkontakte();
		$secretKey = Random::getString(32);

		$confirmationCode = $this->getCallbackConfirmationCode();
		if (!$confirmationCode)
		{
			$response->addError(new Error('Can not get confirmation code for Callback server.'));
			return $response;
		}
		$isRegistered = $this->registerFormWebHook(
			$this->accountId,
			[
				'SECURITY_CODE' => $secretKey,
				'CONFIRMATION_CODE' => $confirmationCode,
			]
		);
		if (!$isRegistered)
		{
			$response->addError(new Error('Can not register Form web hook.'));
			return $response;
		}

		if (!$this->addCallbackServer($secretKey, $response))
		{
			$response->addError(new Error('Can not add callback server.'));
		}

		return $response;
	}

	protected function addCallbackServer($secretKey, Retargeting\Response $response)
	{
		$row = LeadAds\Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
				'=GROUP_ID' => $this->accountId
			]
		]);
		if (!$row)
		{
			return null;
		}

		if (!empty($row['CALLBACK_SERVER_ID']))
		{
			return $row['CALLBACK_SERVER_ID'];
		}

		$serverResponse = $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'groups.addCallbackServer',
			'fields' => [
				'access_token' => $this->getGroupAuthAdapter()->getToken(),
				'group_id' => $this->accountId,
				'url' => 'https://cloud-adv.bitrix.info/register/index.php?' // untitled.php?test=1
					. '&code=' . LeadAds\Service::getEngineCode(static::TYPE_CODE)
					. '&action=web_hook',
				'title' => 'Bitrix24 CRM',
				'secret_key' => $secretKey,
			]
		]);
		$responseData = $serverResponse->getData();
		$serverId = empty($responseData['server_id']) ? null : $responseData['server_id'];
		if ($serverId)
		{
			LeadAds\Internals\CallbackSubscriptionTable::update(
				$row['ID'],
				['CALLBACK_SERVER_ID' => $serverId]
			);

			if (!$this->setCallbackSettings($serverId))
			{
				$response->addError(new Error('Can not set Callback server settings.'));
			}
		}
		else
		{
			$response->addError(new Error('Can not add Callback server.'));
		}

		return $serverId;
	}

	protected function deleteCallbackServer($groupId)
	{
		$row = LeadAds\Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
				'=GROUP_ID' => $groupId
			]
		]);
		if (!$row || empty($row['CALLBACK_SERVER_ID']))
		{
			return;
		}

		$this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'groups.deleteCallbackServer',
			'fields' => [
				'access_token' => $this->getGroupAuthAdapter()->getToken(),
				'group_id' => $groupId,
				'server_id' => $row['CALLBACK_SERVER_ID'],
			]
		]);

		return;
	}

	protected function getCallbackConfirmationCode()
	{
		$response = $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'groups.getCallbackConfirmationCode',
			'fields' => [
				'access_token' => $this->getGroupAuthAdapter()->getToken(),
				'group_id' => $this->accountId,
			]
		]);
		$responseData = $response->getData();
		return empty($responseData['code']) ? null : $responseData['code'];
	}

	protected function setCallbackSettings($serverId, $catchLeads = true)
	{
		$response = $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'groups.setCallbackSettings',
			'fields' => [
				'access_token' => $this->getGroupAuthAdapter()->getToken(),
				'group_id' => $this->accountId,
				'server_id' => $serverId,
				'lead_forms_new' => $catchLeads ? 1 : 0,
			]
		]);
		$responseData = $response->getData();
		return $responseData ? true : false;
	}

	protected function encodeString($text, $length = 60)
	{
		$text = Encoding::convertEncoding(
			$text,
			Context::getCurrent()->getCulture()->getCharset(),
			'UTF-8'
		);

		return mb_substr($text, 0, $length);
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

	/**
	 * Get list.
	 *
	 * @return \Bitrix\Seo\Retargeting\Response
	 */
	public function getList()
	{
		return null;
	}

	/**
	 * Get result.
	 *
	 * @param WebHook\Payload\LeadItem $item Payload item instance.
	 * @return LeadAds\Result
	 */
	public function getResult(WebHook\Payload\LeadItem $item)
	{
		$result = new LeadAds\Result();
		$mapper = static::getFieldMapper();

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

			$fieldName = $mapper->getCrmName($key);
			$fieldName = $fieldName ? 'LEAD_' . $fieldName : $key;
			$result->addFieldValues($fieldName, $values);
		}

		return $result;
	}

	/**
	 * UnRegister group.
	 *
	 * @param string $groupId Group ID.
	 * @return bool
	 */
	public function unRegisterGroup($groupId)
	{
		$this->deleteCallbackServer($groupId);

		return parent::unRegisterGroup($groupId);
	}
}