<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Error;
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
		'STATUS' => 'STATUS',
		'SUPPORTED_CONTACT_TYPES' => array(
			self::ENUM_CONTACT_TYPE_PHONE,
			self::ENUM_CONTACT_TYPE_EMAIL
		),
	);

	public static function normalizeListRow(array $row)
	{
		$map = array(
			//'email' => [self::ENUM_CONTACT_TYPE_EMAIL],
			//'phone' => [self::ENUM_CONTACT_TYPE_PHONE],
			'crm' => [
				self::ENUM_CONTACT_TYPE_PHONE,
				self::ENUM_CONTACT_TYPE_EMAIL
			],
		);
		static::$listRowMap['SUPPORTED_CONTACT_TYPES'] = $map[$row['CONTENT_TYPE']];
		return parent::normalizeListRow($row);
	}

	public static function isSupportMultiTypeContacts()
	{
		return true;
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

	protected function prepareContacts(array $contacts = array(), $hashed = false, $type, array $audienceData)
	{
		// filter by type
		if ($type)
		{
			$contacts = [
				$type => isset($contacts[$type]) ? $contacts[$type] : []
			];
		}

		// prepare data
		$eol = "\r\n";
		$separator = ",";
		$types = array_keys($contacts);
		$typeCount = count($types);
		$result = implode($separator, $types);
		foreach ($types as $index => $contactType)
		{
			foreach ($contacts[$contactType] as $contact)
			{
				$contact = $hashed ? md5($contact) : $contact;
				$data = array_fill(0, $typeCount, "");
				$data[$index] = $contact;
				$result .= $eol . implode($separator, $data);
			}
		}

		return $result;
	}

	protected function changeContacts($method = 'ADD', $audienceId, array $contacts = array(), $type)
	{
		$audienceData = $this->getById($audienceId);
		if (!$audienceData)
		{
			return new ResponseYandex();
		}
		if ($audienceData['STATUS'] === 'is_processed')
		{
			$response = new ResponseYandex();
			$response->addError(new Error('Cant update processed segment.'));
			return $response;
		}

		$hashed = (bool) $audienceData['HASHED'];

		// https://tech.yandex.ru/audience/doc/segments/modifyuploadingdata-docpage/
		$modificationType = $method == 'DELETE' ? 'subtraction' : 'addition';

		$response = $this->getRequest()->send(array(
			'method' => 'POST',
			'endpoint' => 'segment/' . $audienceId . '/modify_data?modification_type=' . $modificationType,
			'fields' => array(
				'file' => array(
					'filename' => 'data.csv',
					'contentType' => 'application/octet-stream',
					'content' => $this->prepareContacts($contacts, $hashed, $type, $audienceData)
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
				if (!isset($row['CONTENT_TYPE']) || $row['CONTENT_TYPE'] != 'crm')
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