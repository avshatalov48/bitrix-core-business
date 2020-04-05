<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\NotImplementedException;
use \Bitrix\Seo\Retargeting\Audience;

class AudienceYandex extends Audience
{
	const TYPE_CODE = 'yandex';

	const MAX_CONTACTS_PER_PACKET = 0;
	const MIN_CONTACTS_FOR_ACTIVATING = 1000;
	const URL_AUDIENCE_LIST = 'https://audience.yandex.ru/';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
		'COUNT_VALID' => 'VALID_UNIQUE_QUANTITY',
		'COUNT_MATCHED' => 'MATCHED_QUANTITY',
		'HASHED' => 'HASHED',
		'SUPPORTED_CONTACT_TYPES' => array(
			self::ENUM_CONTACT_TYPE_EMAIL
		),
	);

	public static function normalizeListRow(array $row)
	{
		$map = array(
			'email' => self::ENUM_CONTACT_TYPE_EMAIL,
			'phone' => self::ENUM_CONTACT_TYPE_PHONE
		);
		static::$listRowMap['SUPPORTED_CONTACT_TYPES'] = array($map[$row['CONTENT_TYPE']]);
		return parent::normalizeListRow($row);
	}

	public static function isSupportMultiTypeContacts()
	{
		return false;
	}

	public static function isSupportAccount()
	{
		return false;
	}

	public static function isAddingRequireContacts()
	{
		return true;
	}

	public static function isSupportRemoveContacts()
	{
		return true;
	}

	public function add(array $data)
	{
		throw new NotImplementedException('Method `AudienceYandex::Add` not implemented.');
	}

	protected function prepareContacts(array $contacts = array(), $hashed = false, $type)
	{
		$data = array();
		$contacts = isset($contacts[$type]) ? $contacts[$type] : array();
		$contactsCount = count($contacts);
		for ($i = 0; $i < $contactsCount; $i++)
		{
			$contact = $contacts[$i];
			$data[] = $hashed ? md5($contact) : $contact;
		}

		return implode("\r\n", $data);
	}

	protected function changeContacts($method = 'ADD', $audienceId, array $contacts = array(), $type)
	{
		$audienceData = $this->getById($audienceId);
		$hashed = (bool) $audienceData['HASHED'];

		// https://tech.yandex.ru/audience/doc/segments/modifyuploadingdata-docpage/
		$modificationType = $method == 'DELETE' ? 'subtraction' : 'addition';

		$response = $this->getRequest()->send(array(
			'method' => 'POST',
			'endpoint' => 'segment/' . $audienceId . '/modify_data?modification_type=' . $modificationType,
			'fields' => array(
				'file' => array(
					'filename' => 'data.tsv',
					'contentType' => 'application/octet-stream',
					'content' => $this->prepareContacts($contacts, $hashed, $type)
				)
			),
		));

		return $response;
	}

	public function importContacts($audienceId, array $contacts = array(), array $options)
	{
		$response = $this->changeContacts(
			'ADD',
			$audienceId,
			$contacts,
			$options['type']
		);

		return $response;
	}

	public function removeContacts($audienceId, array $contacts = array(), array $options)
	{
		$response = $this->changeContacts(
			'DELETE',
			$audienceId,
			$contacts,
			$options['type']
		);

		return $response;
	}

	public function getList()
	{
		// https://tech.yandex.ru/audience/doc/segments/segments-docpage/

		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'segments',
		));

		if ($response->isSuccess())
		{
			$filteredData = array();
			while($row = $response->fetch())
			{
				if ($row['TYPE'] != 'uploading') // we can upload only in auditory of this type
				{
					continue;
				}

				$filteredData[] = $row;
			}

			$response->setData($filteredData);
		}

		return $response;
	}

	public function getAudienceIdFromRow(array $row = null)
	{
		$key = 'ID';
		return (is_array($row) && $row[$key]) ? $row[$key] : null;
	}
}