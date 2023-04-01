<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\Retargeting\Internals\QueueTable;

abstract class Audience extends BaseApiObject
{
	const TYPE_FACEBOOK = 'facebook';
	const TYPE_VKONTAKTE = 'vkontakte';
	const TYPE_MYCOM = 'mycom';
	const TYPE_YANDEX = 'yandex';
	const TYPE_GOOGLE = 'google';

	const ENUM_CONTACT_TYPE_EMAIL = 'email'; // email
	const ENUM_CONTACT_TYPE_PHONE = 'phone'; // phone
	const ENUM_CONTACT_TYPE_IDFA_GAID = 'idfa_gaid'; // IDFA (Identifier For Advertising) or device ID (Android ID and UDID on iOS)
	const ENUM_CONTACT_TYPE_INTERNAL_ID = 'int'; // internal social net id like Vk ID or Fb ID

	const MAX_CONTACTS_PER_PACKET = 100;
	const MIN_CONTACTS_FOR_ACTIVATING = 5000;
	const URL_AUDIENCE_LIST = '';

	protected $accountId;
	protected $audienceId;
	protected static $listRowMap = [
		'ID' => 'ID',
		'NAME' => 'NAME',
		'COUNT_VALID' => 'COUNT',
		'COUNT_MATCHED' => 'COUNT',
		'SUPPORTED_CONTACT_TYPES' => [
			self::ENUM_CONTACT_TYPE_EMAIL,
			self::ENUM_CONTACT_TYPE_PHONE,
			self::ENUM_CONTACT_TYPE_IDFA_GAID,
			self::ENUM_CONTACT_TYPE_INTERNAL_ID
		],
	];
	protected $isQueueModeEnabled = false;
	protected $isQueueAutoRemove = true;
	protected $queueDaysAutoRemove = 7;
	protected $emptyResponse = null;

	public function __construct($accountId = null)
	{
		$this->accountId = $accountId;
		parent::__construct();
	}

	public function setAccountId($accountId)
	{
		return $this->accountId = $accountId;
	}

	public static function normalizeEmail($email)
	{
		return trim(mb_strtolower($email));
	}

	public static function normalizePhone($phone)
	{
		return preg_replace("/[^\+0-9]/", '', $phone);
	}

	public static function isSupportMultiTypeContacts()
	{
		return true;
	}

	public static function isSupportAccount()
	{
		return true;
	}

	public static function isSupportAddAudience()
	{
		return false;
	}

	public static function isAddingRequireContacts()
	{
		return false;
	}

	public static function isSupportRemoveContacts()
	{
		return true;
	}

	public static function isSupportCreateLookalikeFromSegments(): bool
	{
		return true;
	}

	public function getLookalikeAudiencesParams()
	{
		return false;
	}

	public static function getUrlAudienceList()
	{
		return static::URL_AUDIENCE_LIST;
	}

	public static function getMaxContactsPerPacket()
	{
		return static::MAX_CONTACTS_PER_PACKET;
	}

	public static function getMinContactsForActivating()
	{
		return static::MIN_CONTACTS_FOR_ACTIVATING;
	}

	public function disableQueueAutoRemove()
	{
		$this->isQueueAutoRemove = false;
	}

	public function enableQueueAutoRemove($daysNumber = null)
	{
		$this->isQueueAutoRemove = true;
		if ($daysNumber)
		{
			$this->queueDaysAutoRemove = $daysNumber;
		}
	}

	public function disableQueueMode()
	{
		$this->isQueueModeEnabled = false;
	}

	public function enableQueueMode()
	{
		$this->isQueueModeEnabled = true;
	}

	public function isQueueModeEnabled()
	{
		return $this->isQueueModeEnabled;
	}

	public function getById($itemId)
	{
		$itemsResult = $this->getList();
		while($itemData = $itemsResult->fetch())
		{
			$itemData = $this->normalizeListRow($itemData);
			if ($itemData['ID'] == $itemId)
			{
				return $itemData;
			}
		}

		return null;
	}

	protected function normalizeContacts(array $contacts = array())
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
				if (empty($contacts[$contactType][$i]))
				{
					continue;
				}

				$contactPhone = null;
				$contact = $contacts[$contactType][$i];
				switch ($contactType)
				{
					case self::ENUM_CONTACT_TYPE_EMAIL:
						$contact = static::normalizeEmail($contact);
						break;

					case self::ENUM_CONTACT_TYPE_PHONE:
						$contact = static::normalizePhone($contact);
						if (mb_substr($contact, 0, 1) == '8' && mb_strlen($contact) > 8)
						{
							$contactPhone = '+7'.mb_substr($contact, 1);
						}
						break;
				}

				if ($contact)
				{
					$data[$contactType][] = $contact;
				}

				if ($contactPhone)
				{
					$data[$contactType][] = $contactPhone;
				}
			}
		}

		return $data;
	}

	protected function addToQueue($audienceId, $contacts, $options = [], $isRemove = false)
	{
		$dateAutoRemove = null;
		if ($this->isQueueAutoRemove && $this->queueDaysAutoRemove > 0)
		{
			$dateAutoRemove = new DateTime();
			$dateAutoRemove->add($this->queueDaysAutoRemove . ' DAY');
		}

		if ($isRemove)
		{
			$action = QueueTable::ACTION_REMOVE;
		}
		else if ($this->isQueueAutoRemove)
		{
			$action = QueueTable::ACTION_IMPORT_AND_AUTO_REMOVE;
		}
		else
		{
			$action = QueueTable::ACTION_IMPORT;
		}


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
				$resultDb = QueueTable::add(array(
					'TYPE' => static::TYPE_CODE,
					'ACCOUNT_ID' => $this->accountId,
					'CLIENT_ID' => $this->service instanceof IMultiClientService ? $this->service->getClientId() : null,
					'AUDIENCE_ID' => $audienceId,
					'PARENT_ID' => $options['parentId'] ?: null,
					'CONTACT_TYPE' => $contactType,
					'VALUE' => $contact,
					'ACTION' => $action,
					'DATE_AUTO_REMOVE' => $dateAutoRemove,
				));
				$resultDb->isSuccess();
			}
		}

		return true;
	}

	protected function deleteFromQueue($audienceId, $contacts)
	{
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
				$itemDb = QueueTable::getList(array(
					'select' => array('ID'),
					'filter' => array(
						'TYPE' => static::TYPE_CODE,
						'ACCOUNT_ID' => $this->accountId,
						'AUDIENCE_ID' => $audienceId,
						'CONTACT_TYPE' => $contactType,
						'VALUE' => $contact,
					)
				));
				while ($item = $itemDb->fetch())
				{
					$result = QueueTable::delete($item['ID']);
					$result->isSuccess();
				}
			}
		}

		return true;
	}

	/**
	 * @param $audienceId
	 * @param array $contacts
	 * @param array $options
	 * @return Response
	 */
	public function addContacts($audienceId, array $contacts, array $options)
	{
		$contacts = $this->normalizeContacts($contacts);
		if ($this->isQueueModeEnabled())
		{
			$this->addToQueue($audienceId, $contacts, $options, false);
			if ($this->emptyResponse === null)
			{
				$this->emptyResponse = Response::create(static::TYPE_CODE);
				$this->emptyResponse->setData(array());
			}

			return $this->emptyResponse;
		}
		else
		{
			return $this->importContacts($audienceId, $contacts, $options);
		}
	}

	/**
	 * @param $audienceId
	 * @param array $contacts
	 * @param array $options
	 * @return Response
	 */
	public function deleteContacts($audienceId, array $contacts, array $options)
	{
		if ($this->isQueueModeEnabled())
		{
			$this->addToQueue($audienceId, $contacts, $options, true);
			$response = Response::create(static::TYPE_CODE);
			$response->setData(array());
			return $response;
		}
		else
		{
			return $this->removeContacts($audienceId, $contacts, $options);
		}
	}

	/**
	 * Add.
	 *
	 * @param array $data Data.
	 * @return Response
	 */
	abstract public function add(array $data);

	/**
	 * Get list.
	 *
	 * @return Response
	 */
	abstract public function getList();


	/**
	 * Import contacts.
	 *
	 * @param string $audienceId Audience ID.
	 * @param array $contacts Contacts.
	 * @param array $options Options.
	 * @return Response
	 */
	abstract protected function importContacts($audienceId, array $contacts, array $options);


	/**
	 * Remove contacts.
	 *
	 * @param string $audienceId Audience ID.
	 * @param array $contacts Contacts.
	 * @param array $options Options.
	 * @return Response
	 */
	abstract protected function removeContacts($audienceId, array $contacts, array $options);

	public function createLookalike($sourceAudienceId, array $options)
	{
		throw new NotImplementedException('Method '.static::class.'::`addLookalike()` not implemented.');
	}

	public function isQueueProcessed($parentId)
	{
		return !QueueTable::getCount(['=PARENT_ID' => $parentId]);
	}
}