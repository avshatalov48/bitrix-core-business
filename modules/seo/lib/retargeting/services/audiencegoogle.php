<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Audience;

class AudienceGoogle extends Audience
{
	const TYPE_CODE = 'google';

	const MAX_CONTACTS_PER_PACKET = 0;
	const MIN_CONTACTS_FOR_ACTIVATING = 5000;
	const URL_AUDIENCE_LIST = 'https://adwords.google.com/mcm/Mcm#uls.uls&app=mcm.audp';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'SUPPORTED_CONTACT_TYPES' => array(
			self::ENUM_CONTACT_TYPE_EMAIL,
			self::ENUM_CONTACT_TYPE_IDFA_GAID
		),
	);

	public static function normalizeListRow(array $row)
	{
		$map = array(
			'email' => self::ENUM_CONTACT_TYPE_EMAIL,
		);
		static::$listRowMap['SUPPORTED_CONTACT_TYPES'] = array($map[$row['CONTENT_TYPE']]);
		return parent::normalizeListRow($row);
	}

	public static function isSupportMultiTypeContacts()
	{
		return true;
	}

	public static function isSupportAccount()
	{
		return true;
	}

	public static function isSupportRemoveContacts()
	{
		return true;
	}

	public function add(array $data)
	{
		$response = $this->request->send(array(
			'methodName' => 'audience.add',
			'parameters' => array(
				'ACCOUNT_ID' => $this->accountId,
				'NAME' => $data['NAME'],
				'DESCRIPTION' => ''
			)
		));

		$responseData = $response->getData();
		if (isset($responseData['id']))
		{
			$response->setId($responseData['id']);
		}

		return $response;
	}

	protected function prepareContacts(array $contacts = array(), $type = null)
	{
		if ($type && isset($contacts[$type]))
		{
			return $contacts[$type];
		}

		$data = array();
		foreach (static::$listRowMap['SUPPORTED_CONTACT_TYPES'] as $contactType)
		{
			if (!isset($contacts[$contactType]) || !is_array($contacts[$contactType]))
			{
				continue;
			}

			$data = $contacts[$contactType];
		}

		return $data;
	}

	public function importContacts($audienceId, array $contacts = array(), array $options)
	{
		$response = $this->request->send(array(
			'methodName' => 'audience.importcontacts',
			'parameters' => array(
				'ACCOUNT_ID' => $this->accountId,
				'AUDIENCE_ID' => $audienceId,
				'LIST' => $this->prepareContacts($contacts, $options['type'])
			)
		));

		return $response;
	}

	public function removeContacts($audienceId, array $contacts = array(), array $options)
	{
		$response = $this->request->send(array(
			'methodName' => 'audience.removecontacts',
			'parameters' => array(
				'ACCOUNT_ID' => $this->accountId,
				'AUDIENCE_ID' => $audienceId,
				'LIST' => $this->prepareContacts($contacts, $options['type'])
			)
		));

		return $response;
	}

	public function getList()
	{
		$response = $this->request->send(array(
			'methodName' => 'audience.list',
			'parameters' => array(
				'ACCOUNT_ID' => $this->accountId,
			)
		));

		return $response;
	}

	public function getAudienceIdFromRow(array $row = null)
	{
		$key = 'ID';
		return (is_array($row) && $row[$key]) ? $row[$key] : null;
	}
}