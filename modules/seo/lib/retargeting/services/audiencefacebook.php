<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Audience;
use Bitrix\Main\Web\Json;

class AudienceFacebook extends Audience
{
	const TYPE_CODE = 'facebook';

	const MAX_CONTACTS_PER_PACKET = 10000;
	const MIN_CONTACTS_FOR_ACTIVATING = 50;
	const URL_AUDIENCE_LIST = 'https://www.facebook.com/ads/manager/audiences/manage/';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'COUNT_VALID' => 'APPROXIMATE_COUNT',
		'COUNT_MATCHED' => 'APPROXIMATE_COUNT',
		'SUPPORTED_CONTACT_TYPES' => array(
			self::ENUM_CONTACT_TYPE_EMAIL,
			self::ENUM_CONTACT_TYPE_PHONE,
			self::ENUM_CONTACT_TYPE_IDFA_GAID,
			self::ENUM_CONTACT_TYPE_INTERNAL_ID
		),
	);

	public function add(array $data)
	{
		// https://developers.facebook.com/docs/marketing-api/reference/custom-audience/#Creating

		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'act_' . $data['ACCOUNT_ID'] . '/customaudiences',
			'fields' => array(
				'name' => $data['NAME'],
				'subtype' => 'CUSTOM',
				'description' => $data['DESCRIPTION'],
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

			$contactsCount = count($contacts[$contactType]);
			for ($i = 0; $i < $contactsCount; $i++)
			{
				$contact = $contacts[$contactType][$i];
				//$contact = hash('sha256', $contacts[$i]);

				switch ($contactType)
				{
					case self::ENUM_CONTACT_TYPE_EMAIL:
						$data[] = array($contact, '');
						break;

					case self::ENUM_CONTACT_TYPE_PHONE:
						$data[] = array('', $contact);
						break;
				}
			}
		}

		return array(
			'schema' =>  array('EMAIL', 'PHONE'), //$schema == 'PHONE' ? 'PHONE_MD5' : 'EMAIL_MD5',
			'data' => $data
		);
	}

	protected function changeUsers($method = 'POST', $schema = 'EMAIL', $audienceId, array $contacts = array())
	{
		$response = $this->getRequest()->send(array(
			'method' => $method == 'DELETE' ? 'DELETE' : 'POST',
			'endpoint' => $audienceId . '/users',
			'fields' => array(
				'payload' => Json::encode(
					$this->prepareContacts($contacts)
				),
			)
		));

		return $response;
	}

	public function importContacts($audienceId, array $contacts = array(), array $options)
	{
		// https://developers.facebook.com/docs/marketing-api/reference/custom-audience/users/#Updating
		return $this->changeUsers('POST', 'EMAIL', $audienceId, $contacts);
	}

	public function removeContacts($audienceId, array $contacts = array(), array $options)
	{
		// https://developers.facebook.com/docs/marketing-api/reference/custom-audience/users/#Deleting
		return $this->changeUsers('DELETE', 'EMAIL', $audienceId, $contacts);
	}

	public function getList()
	{
		// https://developers.facebook.com/docs/marketing-api/reference/ad-account/customaudiences/#Reading
		// https://developers.facebook.com/docs/marketing-api/reference/custom-audience/
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'act_' . $this->accountId . '/customaudiences',
			'fields' => array(
				'fields' => 'id,name,approximate_count'
			)
		));

		return $response;
	}
}