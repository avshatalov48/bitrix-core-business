<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB;
use Bitrix\Main\Error;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\FileTable;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model\LetterSegmentTable;
use Bitrix\Sender\Internals\Model\LetterTable;
use Bitrix\Sender\Internals\Model\MessageFieldTable;
use Bitrix\Sender\Message as MainMessage;
use Bitrix\Sender\Posting;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Security;
use Bitrix\Sender\Templates;
use Bitrix\Sender\TemplateTable;

Loc::loadMessages(__FILE__);

class Letter extends Base
{
	/** @var null|array $postingData Posting data. */
	protected $postingData = null;

	/** @var  MainMessage\Adapter $message Message. */
	protected $message;

	/** @var null|array $messagesCache Created messages by type
	 */
	protected $messagesCache = [];

	/** @var  Dispatch\Duration $duration Duration. */
	protected $duration;

	/** @var  Dispatch\Method $method Method. */
	protected $method;

	/** @var  Dispatch\State $state State. */
	protected $state;

	/** @var  Posting\Counter $counter Counter. */
	protected $counter;

	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	protected static function getFilterFields()
	{
		return array(
			array(
				'CODE' => 'IS_ADS',
				'VALUE' => 'N',
				'FILTER' => '=IS_ADS'
			),
		);
	}

	/**
	 * Get data manager.
	 *
	 * @return \Bitrix\Main\Entity\DataManager
	 */
	public static function getDataClass()
	{
		return LetterTable::getEntity()->getDataClass();
	}

	/**
	 * Get list.
	 *
	 * @param array $parameters Parameters.
	 * @return DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		if (!isset($parameters['select']))
		{
			$parameters['select'] = static::getDefaultSelectFields();
		}
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = array();
		}

		foreach (static::getFilterFields() as $field)
		{
			if (!$field['FILTER'])
			{
				continue;
			}

			if (isset($parameters['filter'][$field['FILTER']]))
			{
				$current = $parameters['filter'][$field['FILTER']];
				if (is_array($field['VALUE']))
				{
					if (!is_array($current) && in_array($current, $field['VALUE']))
					{
						continue;
					}
				}
			}

			$parameters['filter'][$field['FILTER']] = $field['VALUE'];
		}

		return LetterTable::getList($parameters);
	}

	public static function getDefaultSelectFields()
	{
		return array(
			'*',
			'SITE_ID' => 'CAMPAIGN.SITE_ID',
			'CAMPAIGN_ACTIVE' => 'CAMPAIGN.ACTIVE',

			'DATE_SEND' => 'CURRENT_POSTING.DATE_SEND',
			'DATE_PAUSE' => 'CURRENT_POSTING.DATE_PAUSE',
			'DATE_SENT' => 'CURRENT_POSTING.DATE_SENT',

			'COUNT_SEND_ALL' => 'CURRENT_POSTING.COUNT_SEND_ALL',
			'COUNT_SEND_NONE' => 'CURRENT_POSTING.COUNT_SEND_NONE',
			'COUNT_SEND_ERROR' => 'CURRENT_POSTING.COUNT_SEND_ERROR',
			'COUNT_SEND_SUCCESS' => 'CURRENT_POSTING.COUNT_SEND_SUCCESS',
			'COUNT_SEND_DENY' => 'CURRENT_POSTING.COUNT_SEND_DENY',

			'USER_NAME' => 'CREATED_BY_USER.NAME',
			'USER_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME',
			'USER_ID' => 'CREATED_BY',
		);
	}
	/**
	 * Get list with message fields
	 * @param array $parameters Getlist params.
	 * @return DB\ArrayResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getListWithMessageFields(array $parameters = array())
	{
		$result = [];
		$messageIds = [];
		$list = static::getList($parameters);
		while ($item = $list->fetch())
		{
			$result[] = $item;
			if ($item['MESSAGE_ID'])
			{
				$messageIds[] = $item['MESSAGE_ID'];
			}
		}
		if ($messageIds)
		{
			$messageFields = [];
			$rows = MessageFieldTable::getList(['filter' => ['=MESSAGE_ID' => $messageIds]]);
			while ($messageField = $rows->fetch())
			{
				$messageFields[$messageField['MESSAGE_ID']][] = $messageField;
			}
			foreach ($result as $key => $item)
			{
				if ($messageFields[$item['MESSAGE_ID']])
				{
					$result[$key]['MESSAGE_FIELDS'] = $messageFields[$item['MESSAGE_ID']];
				}
			}
		}
		$dbResult = new \Bitrix\Main\DB\ArrayResult($result);
		$dbResult->setCount($list->getCount());
		return $dbResult;
	}

	/**
	 * Create instance by ID.
	 *
	 * @param integer|null $id ID.
	 * @param array $messageCodes Message codes.
	 * @return static|Letter|Ad|Rc|null
	 */
	public static function createInstanceById($id = null, array $messageCodes = [])
	{
		$code = null;
		if ($id)
		{
			$row = LetterTable::getRow([
				'select' => ['MESSAGE_CODE'],
				'filter' => ['=ID' => $id],
			]);
			if ($row)
			{
				$code = $row['MESSAGE_CODE'];
			}
			else
			{
				$id = null;
			}
		}

		$instance = self::createInstanceByCode($code, $messageCodes);
		if (!$instance)
		{
			return null;
		}

		if ($id)
		{
			$instance->load($id);
		}
		elseif ($instance)
		{
			$instance->set('MESSAGE_CODE', $code);
		}

		return $instance;
	}

	/**
	 * Create instance by array data.
	 *
	 * @param array $data Data.
	 * @param array $messageCodes Message codes.
	 * @return static|Letter|Ad|Rc|null
	 */
	public static function createInstanceByArray(array $data, array $messageCodes = [])
	{
		$code = empty($data['MESSAGE_CODE']) ? null : $data['MESSAGE_CODE'];
		$instance = self::createInstanceByCode($code, $messageCodes);
		$instance->loadByArray($data);

		return $instance;
	}

	/**
	 * Create instance by posting ID.
	 *
	 * @param integer $postingId Posting ID.
	 * @return static|Ad|null
	 */
	public static function createInstanceByPostingId($postingId)
	{
		$row = LetterTable::getList(array(
			'select' => array('ID', 'IS_ADS'),
			'filter' => array('=POSTING_ID' => $postingId),
			'limit' => 1
		))->fetch();
		if (!$row)
		{
			return new static();
		}

		if ($row['IS_ADS'] === 'Y')
		{
			return new Ad($row['ID']);
		}
		else
		{
			return new static($row['ID']);
		}
	}

	/**
	 * Create instance by contact ID.
	 *
	 * @param integer $contactId Contact ID.
	 * @param array $messageCodes Message codes.
	 * @return static|Ad|null
	 */
	public static function createInstanceByContactId($contactId, array $messageCodes = [])
	{
		$typeId = Contact::create($contactId)->get('TYPE_ID') ?: Recipient\Type::EMAIL;
		switch ($typeId)
		{
			case Recipient\Type::EMAIL:
				$code = MainMessage\iBase::CODE_MAIL;
				break;
			case Recipient\Type::PHONE:
				$code = MainMessage\iBase::CODE_SMS;
				break;
			default:
				return null;
		}

		return self::createInstanceByCode($code, $messageCodes);
	}

	protected static function createInstanceByCode($code = null, array $messageCodes = [])
	{
		if (!$code && empty($messageCodes))
		{
			return null;
		}

		if (!$code)
		{
			$code = current($messageCodes);
		}

		if (empty($messageCodes))
		{
			$messageCodes = [$code];
		}

		if (!in_array($code, $messageCodes))
		{
			return null;
		}

		try
		{
			$message = MainMessage\Adapter::create($code);
		} catch (ArgumentException $e)
		{
			return null;
		}

		if ($message->isAds() || $message->isMarketing())
		{
			$instance = new Ad();
		}
		elseif ($message->isReturnCustomer())
		{
			$instance = new Rc();
		}
		elseif ($message->isMailing())
		{
			$instance = new Letter();
		}
		else
		{
			$instance = new Toloka();
		}

		return $instance;
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return array(
			'TITLE' => '',
			'MESSAGE_ID' => '',
			'MESSAGE_CODE' => MainMessage\Adapter::CODE_MAIL,
			'SEGMENTS_INCLUDE' => array(),
			'SEGMENTS_EXCLUDE' => array(),
		);
	}

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	protected function saveData($id, array $data)
	{
		if(!$this->getMessage()->isAvailable())
		{
			$this->addError(Loc::getMessage('SENDER_ENTITY_LETTER_ERROR_NOT_AVAILABLE'));
			return $id;
		}

		$segmentsInclude = $data['SEGMENTS_INCLUDE'];
		$segmentsExclude = $data['SEGMENTS_EXCLUDE'];

		foreach (static::getFilterFields() as $field)
		{
			if (!$field['CODE'])
			{
				continue;
			}

			if (is_array($field['VALUE']))
			{
				if (empty($data[$field['CODE']]) || !in_array($data[$field['CODE']], $field['VALUE']))
				{
					$data[$field['CODE']] = current($field['VALUE']);
				}
			}
			else
			{
				$data[$field['CODE']] = $field['VALUE'];
			}
		}
		$this->filterDataByEntityFields(LetterTable::getEntity(), $data);

		$initialId = $id;
		$previousData = $id ? LetterTable::getRowById($id) : null;
		$previousData = $previousData ?: array();

		// segment check
		if(!is_array($segmentsInclude) || count($segmentsInclude) == 0)
		{
			if (
				(
					isset($data['NOT_USE_SEGMENTS'])
					&& !$data['NOT_USE_SEGMENTS']
				)
				&& $data['IS_TRIGGER'] <> 'Y'
				&& $previousData['IS_TRIGGER'] <> 'Y'
			)
			{
				$this->addError(Loc::getMessage('SENDER_ENTITY_LETTER_ERROR_NO_SEGMENTS'));
				return $id;
			}
		}
		$segmentsExclude = is_array($segmentsExclude) ? $segmentsExclude : array();

		// campaign setting
		if (!isset($data['CAMPAIGN_ID']))
		{
			$data['CAMPAIGN_ID'] = Campaign::getDefaultId(SITE_ID);
			$this->set('CAMPAIGN_ID', $data['CAMPAIGN_ID']);
		}

		// parent letter setting for triggers
		if (!$id && $data['IS_TRIGGER'] === 'Y')
		{
			if (empty($data['PARENT_ID']))
			{
				$previousLetter = (new Chain)->load($data['CAMPAIGN_ID'])->getLast();
				if ($previousLetter && $previousLetter->getId() != $this->getId())
				{
					$data['PARENT_ID'] = $previousLetter->getId();
				}
			}

			if (!isset($data['TIME_SHIFT']))
			{
				$data['TIME_SHIFT'] = 1440;
			}

			$data['STATUS'] = Dispatch\State::WAITING;
			$data['REITERATE'] = 'Y';
		}


		if ($this->filterDataByChanging($data, $previousData))
		{
			$id = $this->saveByEntity(LetterTable::getEntity(), $id, $data);
		}

		if ($this->canChangeSegments())
		{
			$this->saveDataSegments($id, $segmentsInclude, $segmentsExclude);

			$data['DATE_UPDATE'] = new DateTime();
			$this->saveByEntity(LetterTable::getEntity(), $id, $data);
		}

		if ($this->hasErrors())
		{
			return $id;
		}

		// update template use count
		$this->updateTemplateUseCount($data, $previousData);

		// change status for init recipients
		if (!$initialId && !$this->isTrigger())
		{
			$this->setId($id)->getState()->init();
		}

		return $id;
	}

	protected function prepareSearchContent()
	{
		$content = $this->getSearchBuilder()->getContent();
		$content->addUserById($this->get('CREATED_BY'));
		$content->addText($this->get('TITLE'));
		$config = $this->getMessage()->getConfiguration();

		foreach ($config->getOptions() as $option)
		{
			$value = $option->getValue();
			if (!$value)
			{
				continue;
			}

			switch ($option->getType())
			{
				case $option::TYPE_EMAIL:
					$content->addEmail($value);
					break;

				case $option::TYPE_HTML:
				case $option::TYPE_MAIL_EDITOR:
					$content->addHtmlLayout($value);
					break;

				case $option::TYPE_TEXT:
				case $option::TYPE_STRING:
				case $option::TYPE_PRESET_STRING:
				case $option::TYPE_SMS_EDITOR:
					$content->addText($value);
					break;
			}
		}

		return $this;
	}

	protected function saveDataSegments($id, array $segmentsInclude, array $segmentsExclude)
	{
		$segmentsExclude = array_unique($segmentsExclude);
		$segmentsInclude = array_unique($segmentsInclude);
		$segmentsInclude = array_diff($segmentsInclude, $segmentsExclude);

		$segmentsList = array(
			array(
				'list' => $segmentsExclude,
				'include' => false
			),
			array(
				'list' => $segmentsInclude,
				'include' => true
			),
		);

		$oldSegments = $this->loadDataSegments($id);
		$letter = LetterTable::getById($id)->fetch();
		LetterSegmentTable::deleteList(array('LETTER_ID' => $id));

		$isChanged = false;
		$dataToInsert = [];
		foreach ($segmentsList as $segments)
		{
			if(empty($segments['list']))
			{
				continue;
			}

			$typeCode = $segments['include'] ? 'INCLUDE' : 'EXCLUDE';
			$list = [];
			foreach ($segments['list'] as $segment)
			{
				$list[] = ['DATE_UPDATE' => $letter['DATE_UPDATE'], 'ID' => $segment];
				$dataToInsert[] = array(
					'LETTER_ID' => $id,
					'SEGMENT_ID' => $segment,
					'INCLUDE' => $segments['include'],
				);
			}

			$newest = self::getArrayDiffNewest($list, $oldSegments[$typeCode]);
			$removed = self::getArrayDiffRemoved($list, $oldSegments[$typeCode]);

			if (count($newest) === 0 && count($removed) === 0)
			{
				continue;
			}

			if (count($newest) > 0)
			{
				Segment::updateUseCounters($newest, $segments['include']);
			}

			$isChanged = true;
		}
		if(!empty($dataToInsert))
		{
			LetterSegmentTable::addMulti($dataToInsert);
		}

		if ($isChanged && $this->getId() && $this->get('POSTING_ID'))
		{
			Posting\Builder::create()
				->run($this->get('POSTING_ID'), false);
		}
	}

	private static function getArrayDiffNewest(array $current, array $old)
	{
		return array_udiff($current, $old, function($first, $second)
		{
			return $first['DATE_UPDATE'] < $second['DATE_UPDATE'] || $first['ID'] != $second['ID'];
		});
	}

	private static function getArrayDiffRemoved(array $current, array $old)
	{
		return self::getArrayDiffNewest($old, $current);
	}

	protected function updateTemplateUseCount(array $data, array $previousData)
	{
		if (!isset($data['TEMPLATE_TYPE']) || !isset($data['TEMPLATE_ID']))
		{
			return false;
		}

		if (Templates\Type::getCode(Templates\Type::USER) !== $data['TEMPLATE_TYPE'])
		{
			return false;
		}
		if (
			isset($previousData['TEMPLATE_ID'])
			&& $data['TEMPLATE_ID'] === $previousData['TEMPLATE_ID']
			&& $data['TEMPLATE_TYPE'] === $previousData['TEMPLATE_TYPE']
		)
		{
			return false;
		}

		return TemplateTable::incUseCount($data['TEMPLATE_ID']);
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	public function loadData($id)
	{
		$data = static::getList(array(
			'filter' => array(
				'=ID' => $id
			)
		))->fetch();
		if (!is_array($data))
		{
			return null;
		}

		$segments = $this->loadDataSegments($id);
		foreach ($segments as $typeCode => $list)
		{
			foreach($list as $item)
			{
				$data["SEGMENTS_$typeCode"][] = $item['ID'];
			}
		}

		return $data;
	}

	/**
	 * Return true if it have statistics.
	 *
	 * @return bool
	 */
	public function hasStatistics()
	{
		return (
			$this->getState()->wasStartedSending()
			&&
			!$this->getState()->isPlanned()
			&&
			$this->getMessage()->hasStatistics()
		);
	}

	/**
	 * Return true if can change segments.
	 *
	 * @return bool
	 */
	public function canChangeSegments()
	{
		return !$this->getState()->wasPostingBuilt();
	}

	/**
	 * Load segments data.
	 *
	 * @param integer $id ID.
	 * @return array
	 */
	public function loadDataSegments($id)
	{
		$data = array('INCLUDE' => array(), 'EXCLUDE' => array());
		$segments = LetterSegmentTable::getList(array(
			'select' => ['INCLUDE', 'LETTER_ID', 'SEGMENT_ID', 'DATE_UPDATE' => 'SEGMENT.DATE_UPDATE'],
			'filter'=>array(
				'=LETTER_ID'=> $id
			)
		));
		foreach($segments as $segment)
		{
			if ($segment['INCLUDE'])
			{
				$data['INCLUDE'][] =
					[ 'ID' => $segment['SEGMENT_ID'], 'DATE_UPDATE' => $segment['DATE_UPDATE']];
			}
			else
			{
				$data['EXCLUDE'][] =
					[ 'ID' => $segment['SEGMENT_ID'], 'DATE_UPDATE' => $segment['DATE_UPDATE']];
			}
		}

		return $data;
	}

	/**
	 * Return true if can change template.
	 *
	 * @return bool
	 */
	public function canChangeTemplate()
	{
		if ($this->getState()->isFinished())
		{
			return false;
		}

		//return $this->getMessage()->getCode() !== MainMessage\Adapter::CODE_MAIL;
		return true;
	}

	/**
	 * Get Message instance.
	 *
	 * @return MainMessage\Adapter
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getMessage()
	{
		$messageCode = $this->get('MESSAGE_CODE') ?: MainMessage\Adapter::CODE_MAIL;
		$messageId = $this->get('MESSAGE_ID') ?: null;

		$messageFields = [];
		if (isset($this->data['MESSAGE_FIELDS']) && $this->data['MESSAGE_FIELDS'])
		{
			foreach ($this->data['MESSAGE_FIELDS'] as $field)
			{
				$messageFields[$field['CODE']] = $field['VALUE'];
			}
		}
		if ($this->messagesCache && isset($this->messagesCache[$messageCode]))
		{
			$this->message = $this->messagesCache[$messageCode];
			if ($messageFields)
			{
				$this->message->setConfigurationData($messageFields);
			}
			return $this->message;
		}

		$this->message = MainMessage\Adapter::create($messageCode);
		$createdById = $this->get('CREATED_BY') ?: Security\User::current()->getId();
		$this->message->getConfiguration()->set('LETTER_CREATED_BY_ID', $createdById);
		$this->message->setSiteId($this->get('SITE_ID'));
		if ($messageFields)
		{
			$this->message->setConfigurationData($messageFields);
		}
		$this->message->loadConfiguration($messageId);

		$this->messagesCache[$messageCode] = $this->message;

		return $this->message;
	}

	/**
	 * Is support heat map.
	 *
	 * @return bool
	 */
	public function isSupportHeatMap()
	{
		return $this->getMessage()->getCode() == MainMessage\Adapter::CODE_MAIL;
	}

	/**
	 * Is support reiterate run.
	 *
	 * @return bool
	 */
	public function isSupportReiterate()
	{
		if (in_array($this->getMessage()->getCode(), ['rc_lead', 'rc_deal']))
		{
			return true;
		}

		return !Integration\Bitrix24\Service::isPortal();
	}

	/**
	 * Get campaign ID.
	 *
	 * @return mixed
	 */
	public function getCampaignId()
	{
		return $this->get('CAMPAIGN_ID');
	}

	/**
	 * Get duration instance.
	 *
	 * @return Dispatch\Duration
	 */
	public function getDuration()
	{
		if ($this->duration)
		{
			return $this->duration;
		}

		$this->duration = new Dispatch\Duration($this);

		return $this->duration;
	}

	/**
	 * Get state instance.
	 *
	 * @return Dispatch\State
	 */
	public function getState()
	{
		if ($this->state)
		{
			return $this->state;
		}

		$this->state = new Dispatch\State($this);

		return $this->state;
	}

	/**
	 * Get method instance.
	 *
	 * @return Dispatch\Method
	 */
	public function getMethod()
	{
		if ($this->method)
		{
			return $this->method;
		}

		$this->method = new Dispatch\Method($this);

		return $this->method;
	}

	/**
	 * Get counter instance.
	 *
	 * @return Posting\Counter
	 */
	public function getCounter()
	{
		if ($this->counter)
		{
			return $this->counter;
		}

		$this->counter = new Posting\Counter($this);

		return $this->counter;
	}

	/**
	 * Get Tester instance.
	 *
	 * @return MainMessage\Tester
	 */
	protected function getTester()
	{
		return $this->getMessage()->getTester();
	}

	/**
	 * Is reiterate letter.
	 *
	 * @return bool
	 */
	public function isReiterate()
	{
		return $this->get('REITERATE') === 'Y';
	}

	/**
	 * Is trigger letter.
	 *
	 * @return bool
	 */
	public function isTrigger()
	{
		return $this->get('IS_TRIGGER') === 'Y';
	}

	/**
	 * Is support testing.
	 *
	 * @return bool
	 */
	public function isSupportTesting()
	{
		return $this->getTester()->isSupport();
	}

	/**
	 * Remove.
	 *
	 * @return bool
	 */
	public function remove()
	{
		return $this->removeByEntity(LetterTable::getEntity(), $this->getId());
	}

	/**
	 * Remove by letter ID.
	 *
	 * @param integer $id Letter ID.
	 * @return bool
	 */
	public static function removeById($id)
	{
		return static::create()->removeByEntity(LetterTable::getEntity(), $id);
	}

	/**
	 * Copy.
	 *
	 * @return integer|null
	 */
	public function copy()
	{
		$configurationId = $this->getMessage()->getConfiguration()->getId();
		if (!$configurationId)
		{
			return null;
		}

		$result = $this->getMessage()->copyConfiguration($configurationId);
		if (!$result->isSuccess() || !$result->getId())
		{
			return null;
		}

		$data = array(
			'CAMPAIGN_ID' => $this->get('CAMPAIGN_ID'),
			'MESSAGE_CODE' => $this->get('MESSAGE_CODE'),
			'MESSAGE_ID' => $result->getId(),
			'REITERATE' => $this->get('REITERATE'),
			'TEMPLATE_TYPE' => $this->get('TEMPLATE_TYPE'),
			'TEMPLATE_ID' => $this->get('TEMPLATE_ID'),
			'CREATED_BY' => $this->getUser()->getId(),
			'UPDATED_BY' => $this->getUser()->getId(),
			'IS_TRIGGER' => $this->get('IS_TRIGGER'),
			'TITLE' => Loc::getMessage('SENDER_ENTITY_LETTER_COPY_PREFIX') . ' ' . $this->get('TITLE'),
			'SEGMENTS_INCLUDE' => $this->get('SEGMENTS_INCLUDE'),
			'SEGMENTS_EXCLUDE' => $this->get('SEGMENTS_EXCLUDE'),
		);
		$instance = static::create()->mergeData($data);
		$instance->save();
		$this->getErrorCollection()->add($instance->getErrors());

		if (!is_null($this->getMessage()->getConfiguration()->get('MESSAGE')))
		{
			FileTable::syncFiles(
				$instance->getId(),
				0,
				$this->getMessage()->getConfiguration()->get('MESSAGE')
			);
		}

		return $instance->getId();
	}

	/**
	 * Send test message to recipient.
	 *
	 * @param array $codes Recipient codes.
	 * @param array $parameters Parameters.
	 * @return Result
	 */
	public function test(array $codes, array $parameters = array())
	{
		return $this->getTester()->send($codes, $parameters);
	}

	/**
	 * Plan sending.
	 *
	 * @param Date $date Date.
	 * @return bool
	 */
	public function plan(Date $date)
	{
		try
		{
			return $this->getState()->plan($date);
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Wait sending.
	 *
	 * @return bool
	 */
	public function wait()
	{
		if ($this->isTrigger())
		{
			try
			{
				return $this->getState()->wait();
			}
			catch (InvalidOperationException $exception)
			{
				$this->errors->setError(new Error($exception->getMessage()));
				return false;
			}
		}

		if (!$this->isReiterate())
		{
			$this->errors->setError(new Error('Entity is not reiterate.'));
			return false;
		}

		try
		{
			$scheduleTime = Dispatch\MethodSchedule::parseTimesOfDay($this->get('TIMES_OF_DAY'));
			$scheduleMonths = Dispatch\MethodSchedule::parseMonthsOfYear($this->get('MONTHS_OF_YEAR'));
			$scheduleWeekDays = Dispatch\MethodSchedule::parseDaysOfWeek($this->get('DAYS_OF_WEEK'));
			$scheduleMonthDays = Dispatch\MethodSchedule::parseDaysOfMonth($this->get('DAYS_OF_MONTH'));
			$method = (new Dispatch\MethodSchedule($this))
				->setMonthsOfYear($scheduleMonths)
				->setDaysOfMonth($scheduleMonthDays)
				->setDaysOfWeek($scheduleWeekDays)
				->setTime($scheduleTime[0], $scheduleTime[1]);
			$this->getState()->wait($method);
			return true;
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Send.
	 *
	 * @return bool
	 */
	public function send()
	{
		try
		{
			return $this->getState()->send();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Send errors.
	 *
	 * @return bool
	 */
	public function sendErrors()
	{
		try
		{
			return $this->getState()->sendErrors();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Stop.
	 *
	 * @return bool
	 */
	public function stop()
	{
		try
		{
			return $this->getState()->stop();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Halt.
	 *
	 * @return bool
	 */
	public function halt()
	{
		try
		{
			return $this->getState()->halt();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Resume.
	 *
	 * @return bool
	 */
	public function resume()
	{
		try
		{
			return $this->getState()->resume();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Pause.
	 *
	 * @return bool
	 */
	public function pause()
	{
		try
		{
			return $this->getState()->pause();
		}
		catch (InvalidOperationException $exception)
		{
			$this->errors->setError(new Error($exception->getMessage()));
			return false;
		}
	}

	/**
	 * Get last posting data.
	 *
	 * @return array
	 */
	public function getLastPostingData()
	{
		$defaults = array();

		if (!$this->getId())
		{
			return $defaults;
		}

		if ($this->postingData !== null)
		{
			return $this->postingData;
		}

		$this->postingData = $defaults;
		$postingFilter = array(
			'=ID' => $this->getId(),
			//'!POSTING.DATE_SENT' => null
		);

		$postings = static::getList(array(
			'select' => array(
				'POSTING_ID',
				'LETTER_ID' => 'ID',
				'CAMPAIGN_ID',
				'TITLE' => 'TITLE',
				'MAILING_NAME' => 'CAMPAIGN.NAME',
				'DATE_SENT' => 'POSTING.DATE_SENT',
				'COUNT_SEND_ERROR' => 'POSTING.COUNT_SEND_ERROR',
				'CREATED_BY' => 'CREATED_BY',
				'CREATED_BY_NAME' => 'CREATED_BY_USER.NAME',
				'CREATED_BY_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME',
				'CREATED_BY_SECOND_NAME' => 'CREATED_BY_USER.SECOND_NAME',
				'CREATED_BY_LOGIN' => 'CREATED_BY_USER.LOGIN',
				'CREATED_BY_TITLE' => 'CREATED_BY_USER.TITLE',
			),
			'filter' => $postingFilter,
			'limit' => 1,
			'order' => array('POSTING.DATE_SENT' => 'DESC', 'POSTING.DATE_CREATE' => 'DESC'),
		));
		if ($postingData = $postings->fetch())
		{
			$this->postingData = $postingData + $this->postingData;
		}

		return $this->postingData;
	}
}