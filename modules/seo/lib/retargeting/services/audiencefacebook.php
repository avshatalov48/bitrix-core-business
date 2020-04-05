<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Audience;
use Bitrix\Main\Web\Json;

class AudienceFacebook extends Audience
{
	const TYPE_CODE = 'facebook';

	const MAX_CONTACTS_PER_PACKET = 10000;
	const MIN_CONTACTS_FOR_ACTIVATING = 50;
	const URL_AUDIENCE_LIST = 'https://business.facebook.com/adsmanager/audiences';

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
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'name' => $data['NAME'],
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
				$contact = hash('sha256', $contact);

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

	public function importContacts($audienceId, array $contacts = array(), array $options)
	{
		return $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.contacts.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $audienceId,
				'contacts' => Json::encode(
					$this->prepareContacts($contacts)
				)
			)
		));
	}

	public function removeContacts($audienceId, array $contacts = array(), array $options)
	{
		return $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.contacts.remove',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $audienceId,
				'contacts' => Json::encode(
					$this->prepareContacts($contacts)
				)
			)
		));
	}

	public function getList()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.list',
			'parameters' => array(
				'accountId' => $this->accountId
			)
		));
		$data = $response->getData();
		$data = array_values(array_filter($data, function ($item) {
			return ($item['subtype'] == 'CUSTOM'); // only CUSTOM type (list of clients) is supported
		}));
		$response->setData($data);
		return $response;
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
			'FIELDS' => ['AUDIENCE_SIZE', 'AUDIENCE_REGION'],
			'SIZES' => $sizes,
		];
	}

	public function createLookalike($sourceAudienceId, array $options)
	{
		$result = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.lookalike.add',
			'parameters' => array(
				'accountId' => $this->accountId,
				'audienceId' => $sourceAudienceId,
				'options' => $options
			)
		));
		if ($result->isSuccess())
		{
			$result->setId($result->getData()['id']);
		}
		return $result;
	}
}