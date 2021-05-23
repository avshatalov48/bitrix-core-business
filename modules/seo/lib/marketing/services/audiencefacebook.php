<?
namespace Bitrix\Seo\Marketing\Services;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Marketing\Audience;

class AudienceFacebook extends Audience
{
	const TYPE_CODE = 'facebook';

	const ENUM_CONTACT_TYPE_EMAIL = 'email'; // email
	const ENUM_CONTACT_TYPE_PHONE = 'phone'; // phone
	const ENUM_CONTACT_TYPE_IDFA_GAID = 'idfa_gaid'; // IDFA (Identifier For Advertising) or device ID (Android ID and UDID on iOS)
	const ENUM_CONTACT_TYPE_INTERNAL_ID = 'int'; // int

	const MAX_CONTACTS_PER_PACKET = 10000;
	const MIN_CONTACTS_FOR_ACTIVATING = 50;

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

	public function getList()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.audience.list',
			'parameters' => array(
				'accountId' => $this->accountId
			)
		));
		if ($response->isSuccess())
		{
			$result = [];
			while($data = $response->fetch())
			{
				$result[] = $data;
			}
			return $result;
		}

		return null;
	}

	public function add(array $data)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.audience.create',
			'parameters' => $data
		));

		if ($response->isSuccess())
		{
			return $response->getData();
		}

		return null;
	}

	protected function importContacts($audienceId, array $contacts = [], array $options)
	{
		return $this->getRequest()->send(array(
			'methodName' => 'marketing.audience.contacts.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audience_id' => $this->accountId,
				'contacts' => Json::encode(
					$this->prepareContacts($contacts)
				),
			)
		));
	}

	protected function removeContacts($audienceId, array $contacts = [], array $options)
	{
		return $this->getRequest()->send(array(
			'methodName' => 'marketing.audience.contacts.remove',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $audienceId,
				'contacts' => Json::encode(
					$this->prepareContacts($contacts)
				)
			)
		));
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
				$contact = hash('sha256', $contact);

				switch ($contactType)
				{
					case self::ENUM_CONTACT_TYPE_EMAIL:
						$data[$contactType.'_SHA256'][] = $contact;
						break;

					case self::ENUM_CONTACT_TYPE_PHONE:
						$data[$contactType.'_SHA256'][] = $contact;
						break;
				}
			}
		}

		return $data;
	}
}