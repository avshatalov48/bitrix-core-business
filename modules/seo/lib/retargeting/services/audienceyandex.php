<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use \Bitrix\Seo\Retargeting\Audience;
use Bitrix\Seo\Retargeting\Response;

class AudienceYandex extends Audience
{
	const TYPE_CODE = 'yandex';

	const MAX_CONTACTS_PER_PACKET = 0;
	const MIN_CONTACTS_FOR_ACTIVATING = 1000;
	const URL_AUDIENCE_LIST = 'https://audience.yandex.ru/';

	const NEW_AUDIENCE_FAKE_ID = -1;
	const UPDATE_AUDIENCE_TIMEOUT = 60;

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
		return false;
	}

	/**
	 * @param array $data Data.
	 * @return \Bitrix\Seo\Retargeting\Response
	 * @deprecated Not supported by Yandex
	 * @throws NotImplementedException
	 */
	public function add(array $data)
	{
		throw new NotImplementedException('Method `AudienceYandex::Add` not implemented.');
	}

	/**
	 * Create contacts list in proper format.
	 * @param array $contacts Contacts.
	 * @param bool $hashed Should result be hashed.
	 * @param string $type Type (email|phone).
	 * @return string
	 */
	protected function prepareContacts(array $contacts = array(), $hashed = false, $type = '')
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

	/**
	 * @param string $audienceId Audience id.
	 * @param array $contacts Contacts.
	 * @param array $options Options.
	 * @return Result|\Bitrix\Seo\Retargeting\Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function importContacts($audienceId, array $contacts = array(), array $options)
	{
		$createNewAudience = ($audienceId == static::NEW_AUDIENCE_FAKE_ID);
		$audienceData = $this->getById($audienceId);
		if (!$audienceData)
		{
			$result = new Result();
			$result->addError(new Error('Audience '.$audienceId.' not found'));
			return $result;
		}

		$hashed = (bool)$audienceData['HASHED'];
		$payload = $this->prepareContacts($contacts, $hashed, $options['type']);

		if ($createNewAudience)
		{
			$name = $options['audienceName'] ?: Loc::getMessage('SEO_RETARGETING_SERVICE_AUDIENCE_NAME_TEMPLATE', ['#DATE#' => FormatDate('j F')]);
			$response = $this->getRequest()->send(array(
				'methodName' => 'retargeting.audience.add',
				'parameters' => array(
					'name' => $name,
					'hashed' => $hashed ? 1 : 0,
					'contacts' => $payload
				),
				'timeout' => static::UPDATE_AUDIENCE_TIMEOUT
			));
		}
		else
		{
			$response = $this->getRequest()->send(array(
				'methodName' => 'retargeting.audience.contacts.rewrite',
				'parameters' => array(
					'audienceId' => $audienceId,
					'contacts' => $payload
				),
				'timeout' => static::UPDATE_AUDIENCE_TIMEOUT
			));
		}

		return $response;
	}

	/**
	 * Remove contacts from audience
	 * @param string $audienceId Audience id.
	 * @param array $contacts Contacts.
	 * @param array $options Options.
	 * @deprecated Not supported by Yandex anymore
	 * @return \Bitrix\Seo\Retargeting\Response|null
	 */
	public function removeContacts($audienceId, array $contacts = array(), array $options)
	{
		$response = Response::create(static::TYPE_CODE);
		return $response;
	}

	/**
	 * Audiences list
	 * @return \Bitrix\Seo\Retargeting\Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getList()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.audience.list',
			'parameters' => array()
		));
		$data = $response->getData();
		if (is_array($data['segments']))
		{
			$data = array_values(array_filter($data['segments'], function ($item) {
				return (
					$item['type'] == 'uploading' && // based on uploaded data
					$item['content_type'] == 'crm' && // Data from crm
					$item['status'] != 'is_processed' // Can't use segments which are processed right now
				);
			}));
		}
		else
		{
			$data = [];
		}

		$data = $this->addNewAudienceValue($data);
		$response->setData($data);
		return $response;
	}

	/**
	 * "New audience" value in audiences list
	 * @param array $data Audiences list.
	 * @return array
	 */
	protected function addNewAudienceValue($data)
	{
		array_unshift($data, [
			'name' => Loc::getMessage("SEO_RETARGETING_SERVICE_AUDIENCE_YANDEX_ADD"),
			'id' => static::NEW_AUDIENCE_FAKE_ID,
			'valid_unique_quantity' => '',
			'matched_quantity' => '',
			'status' => '',
			'hashed' => false,
		]);
		return $data;
	}
}