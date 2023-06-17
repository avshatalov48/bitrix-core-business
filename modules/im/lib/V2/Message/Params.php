<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Main\ORM;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\EO_MessageParam;
use Bitrix\Im\Model\EO_MessageParam_Collection;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;

/**
 * @method MessageParameter next()
 * @method MessageParameter current()
 * @method MessageParameter offsetGet($offset)
 */
class Params extends Registry
{
	public const EVENT_MESSAGE_PARAM_TYPE_INIT = 'OnMessageParamTypesInit';

	public const
		TS = 'TS',
		FILE_ID = 'FILE_ID',
		ATTACH = 'ATTACH',
		MENU = 'MENU',
		KEYBOARD = 'KEYBOARD',
		KEYBOARD_UID = 'KEYBOARD_UID',
		IS_DELETED = 'IS_DELETED',
		IS_ERROR = 'IS_ERROR',
		IS_DELIVERED = 'IS_DELIVERED',
		IS_EDITED = 'IS_EDITED',
		IS_PINNED = 'IS_PINNED',
		CAN_ANSWER = 'CAN_ANSWER',
		URL_ONLY = 'URL_ONLY',
		LARGE_FONT = 'LARGE_FONT',
		SENDING = 'SENDING',
		SENDING_TS = 'SENDING_TS',
		USER_ID = 'USER_ID',
		AVATAR = 'AVATAR',
		NAME = 'NAME',
		NOTIFY = 'NOTIFY',
		CODE = 'CODE',
		TYPE = 'TYPE',
		COMPONENT_ID = 'COMPONENT_ID',
		STYLE_CLASS = 'CLASS',
		CALL_ID = 'CALL_ID',
		CHAT_ID = 'CHAT_ID',
		CHAT_MESSAGE = 'CHAT_MESSAGE',
		CHAT_USER = 'CHAT_USER',
		DATE_TS = 'DATE_TS',
		LIKE = 'LIKE',
		FAVORITE = 'FAVORITE',
		KEYBOARD_ACTION = 'KEYBOARD_ACTION',
		URL_ID = 'URL_ID',
		LINK_ACTIVE = 'LINK_ACTIVE',
		USERS = 'USERS',
		CHAT_LAST_DATE = 'CHAT_LAST_DATE',
		DATE_TEXT = 'DATE_TEXT',
		IS_ROBOT_MESSAGE = 'IS_ROBOT_MESSAGE',
		FORWARD_ID = 'FORWARD_ID',
		FORWARD_CHAT_ID = 'FORWARD_CHAT_ID',
		FORWARD_TITLE = 'FORWARD_TITLE',
		FORWARD_USER_ID = 'FORWARD_USER_ID',
		REPLY_ID = 'REPLY_ID',
		BETA = 'BETA'
	;

	//todo: Move it into CRM module
	public const
		CRM_FORM_FILLED = 'CRM_FORM_FILLED',
		CRM_FORM_ID = 'CRM_FORM_ID',
		CRM_FORM_SEC = 'CRM_FORM_SEC'
	;

	protected static bool $typeLoaded = false;

	protected bool $isLoaded = false;

	protected static array $typeMap = [
		self::TS => [
			'type' => Param::TYPE_STRING,
		],
		self::FILE_ID => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::IS_DELETED => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::IS_ERROR => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::IS_DELIVERED => [
			'type' => Param::TYPE_BOOL,
			'default' => true,
		],
		self::IS_EDITED => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::IS_PINNED => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::CAN_ANSWER => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::URL_ONLY => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::LARGE_FONT => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::ATTACH => [
			'className' => \Bitrix\Im\V2\Message\Param\AttachArray::class,
			'classItem' => \Bitrix\Im\V2\Message\Param\Attach::class,
			'type' => Param::TYPE_JSON,
		],
		self::MENU => [
			'className' => \Bitrix\Im\V2\Message\Param\Menu::class,
			'type' => Param::TYPE_JSON,
		],
		self::KEYBOARD  => [
			'className' => \Bitrix\Im\V2\Message\Param\Keyboard::class,
			'type' => Param::TYPE_JSON,
		],
		self::KEYBOARD_UID => [
			'type' => Param::TYPE_INT,
			'default' => 0,
		],
		self::SENDING => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::SENDING_TS => [
			'type' => Param::TYPE_INT,
			'default' => 0,
		],
		// Message User
		self::USER_ID => [
			'type' => Param::TYPE_INT,
		],
		self::AVATAR => [
			'className' => \Bitrix\Im\V2\Message\Param\UserAvatar::class,
			'type' => Param::TYPE_INT,
		],
		self::NAME => [
			'className' => \Bitrix\Im\V2\Message\Param\UserName::class,
			'type' => Param::TYPE_STRING,
		],
		// Disable notification for message
		self::NOTIFY => [
			'type' => Param::TYPE_BOOL,
		],
		self::CODE => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
		self::TYPE => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
		self::COMPONENT_ID => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
		self::STYLE_CLASS => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
		self::CALL_ID => [
			'type' => Param::TYPE_INT,
			'default' => 0,
		],
		self::CHAT_ID => [
			'type' => Param::TYPE_INT,
			'default' => 0,
		],
		self::CHAT_MESSAGE => [
			'type' => Param::TYPE_INT,
			'default' => 0,
		],
		self::CHAT_USER => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::DATE_TS => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::LIKE => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::FAVORITE => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::KEYBOARD_ACTION => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::URL_ID => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::LINK_ACTIVE => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::USERS => [
			'type' => Param::TYPE_INT_ARRAY,
		],
		self::CHAT_LAST_DATE => [
			'type' => Param::TYPE_DATE_TIME,
		],
		self::DATE_TEXT => [
			'className' => \Bitrix\Im\V2\Message\Param\TextDate::class,
			'type' => Param::TYPE_STRING_ARRAY,
		],
		self::IS_ROBOT_MESSAGE => [
			'type' => Param::TYPE_BOOL,
		],
		self::FORWARD_ID => [
			'type' => Param::TYPE_INT,
		],
		self::FORWARD_CHAT_ID => [
			'type' => Param::TYPE_INT,
		],
		self::FORWARD_TITLE => [
			'type' => Param::TYPE_STRING,
		],
		self::FORWARD_USER_ID => [
			'type' => Param::TYPE_INT,
		],
		self::REPLY_ID => [
			'type' => Param::TYPE_INT,
		],
		self::BETA => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],

		//todo: Move it into CRM module
		self::CRM_FORM_FILLED => [
			'type' => Param::TYPE_BOOL,
			'default' => false,
		],
		self::CRM_FORM_ID => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
		self::CRM_FORM_SEC => [
			'type' => Param::TYPE_STRING,
			'default' => '',
		],
	];


	protected ?int $messageId = null;

	/** @var array<string, MessageParameter> */
	protected array $droppedItems = [];

	//region Types

	/**
	 * Returns message parameter description.
	 * @param string $paramName
	 * @return array<string: type, string: className, mixed: default, callable: saveValueFilter, callable: loadValueFilter>
	 */
	public static function getType(string $paramName): array
	{
		self::initTypes();

		if (isset(self::$typeMap[$paramName]))
		{
			$type = self::$typeMap[$paramName];
			if (!isset($type['className']))
			{
				if ($type['type'] == Param::TYPE_INT_ARRAY || $type['type'] == Param::TYPE_STRING_ARRAY)
				{
					$type['className'] = ParamArray::class;
				}
				elseif ($type['type'] == Param::TYPE_DATE_TIME)
				{
					$type['className'] = Param\DateTime::class;
				}
				else
				{
					$type['className'] = Param::class;
				}
			}
		}
		else
		{
			$type = [
				'className' => Param::class,
				'type' => Param::TYPE_STRING,
			];
		}

		return $type;
	}

	/**
	 * Add new message parameter description.
	 * @param string $paramName
	 * @param array $description
	 * @return void
	 */
	public static function addType(string $paramName, array $description): void
	{
		self::$typeMap[$paramName] = $description;
	}

	/**
	 * Loads specific modules message parameter types from .settings.php.
	 * @return void
	 */
	public static function initTypes(): void
	{
		if (!self::$typeLoaded)
		{
			$event = new Event('im', self::EVENT_MESSAGE_PARAM_TYPE_INIT);
			$event->send();
			$resultList = $event->getResults();

			foreach ($resultList as $eventResult)
			{
				if ($eventResult->getType() === EventResult::SUCCESS)
				{
					$settings = $eventResult->getParameters();
					if (is_array($settings))
					{
						foreach ($settings as $paramName => $description)
						{
							if (
								is_array($description)
								&& (
									!empty($description['type'])
									|| !empty($description['className'])
								)
							)
							{
								self::addType($paramName, $description);
							}
						}
					}
				}
			}
		}

		self::$typeLoaded = true;
	}

	//endregion

	//region Loaders

	/**
	 * Tells true if paraams have been loaded from DB.
	 * @return bool
	 */
	public function isLoaded(): bool
	{
		return $this->isLoaded;
	}

	/**
	 * @param array|ORM\Objectify\Collection|ORM\Objectify\EntityObject|EO_MessageParam_Collection|EO_MessageParam $source
	 * @return Result
	 */
	public function load($source): Result
	{
		if (is_array($source))
		{
			$result = $this->initByArray($source);
		}
		elseif ($source instanceof ORM\Objectify\Collection)
		{
			$result = $this->initByEntitiesCollection($source);
		}
		elseif ($source instanceof ORM\Objectify\EntityObject)
		{
			$result = $this->initByDataEntity($source);
		}
		else
		{
			$result = (new Result)->addError(new Error(Error::NOT_FOUND));
		}

		if ($result->isSuccess())
		{
			foreach ($this as $param)
			{
				if ($param->getMessageId())
				{
					$this->setMessageId($param->getMessageId());
					break;
				}
			}

			$this->isLoaded = true;
		}

		return $result;
	}

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function loadByMessage(Message $message): Result
	{
		if ($message->getMessageId())
		{
			return $this->loadByMessageId($message->getMessageId());
		}

		return new Result;
	}

	/**
	 * @param int $messageId
	 * @return Result
	 */
	public function loadByMessageId(int $messageId): Result
	{
		if ($messageId > 0)
		{
			$this->setMessageId($messageId);

			$collection = Param::getDataClass()::query()
				->setSelect(['ID', 'MESSAGE_ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'])
				->where('MESSAGE_ID', '=', $messageId)
				->fetchCollection()
			;

			return $this->initByEntitiesCollection($collection);
		}

		return new Result;
	}

	/**
	 * @param ORM\Objectify\Collection $entitiesCollection
	 * @return Result
	 */
	protected function initByEntitiesCollection(ORM\Objectify\Collection $entitiesCollection): Result
	{
		/** @var EO_MessageParam $entity */
		foreach ($entitiesCollection as $entity)
		{
			$paramName = $entity->getParamName();
			if (!parent::offsetExists($paramName))
			{
				$this[$paramName] = self::create($paramName);
			}

			$item = $this[$paramName];
			if ($item instanceof ParamArray)
			{
				$type = self::getType($paramName);
				if (isset($type['classItem']))
				{
					$classItem = $type['classItem'];
					$item->add(new $classItem($entity));
				}
				else
				{
					$item->add(new Param($entity));
				}
			}
			else
			{
				$item->load($entity);
			}
		}

		$this->isLoaded = true;

		return new Result();
	}

	/**
	 * @param array $items
	 * @return Result
	 */
	protected function initByArray(array $items): Result
	{
		foreach ($items as $entityId => $entity)
		{
			if (isset($entity['PARAM_NAME']))
			{
				$paramName = $entity['PARAM_NAME'];
				if (!parent::offsetExists($paramName))
				{
					$this[$paramName] = self::create($paramName);
				}

				$item = $this[$paramName];
				if ($item instanceof ParamArray)
				{
					$type = self::getType($paramName);
					if (isset($type['classItem']))
					{
						$classItem = $type['classItem'];
						$item->add(new $classItem($entity));
					}
					else
					{
						$item->add(new Param($entity));
					}
				}
				else
				{
					$item->load($entity);
				}
			}
			else
			{
				if (!parent::offsetExists($entityId))
				{
					$this[$entityId] = self::create($entityId);
				}

				$this[$entityId]->setValue($entity);
			}
		}

		return new Result();
	}

	/**
	 * @param ORM\Objectify\EntityObject $entity
	 * @return Result
	 */
	protected function initByDataEntity(ORM\Objectify\EntityObject $entity): Result
	{
		$paramName = $entity->getParamName();
		if (!parent::offsetExists($paramName))
		{
			$this[$paramName] = self::create($paramName);
		}

		$item = $this[$paramName];
		if ($item instanceof ParamArray)
		{
			$type = self::getType($paramName);
			if (isset($type['classItem']))
			{
				$classItem = $type['classItem'];
				$item->add(new $classItem($entity));
			}
			else
			{
				$item->add(new Param($entity));
			}
		}
		else
		{
			$item->load($entity);
		}

		return new Result();
	}

	//endregion

	//region Save

	/**
	 * Saves changes.
	 * @return Result
	 */
	public function save(): Result
	{
		$result = new Result;

		/** @var EO_MessageParam_Collection $dataEntityCollection */
		$entityCollectionClass = Param::getDataClass()::getCollectionClass();
		$dataEntityCollection = new $entityCollectionClass;

		$dropIds = [];

		foreach ($this as $item)
		{
			if ($item->isDeleted())
			{
				unset($this[$item->getName()]);
				continue;
			}
			if (!$item->hasValue())
			{
				continue;
			}

			if ($item instanceof Param)
			{
				$prepareResult = $item->prepareFields();
				if ($prepareResult->isSuccess())
				{
					if ($item->isChanged())
					{
						$dataEntityCollection->add($item->getDataEntity());
						$item->markChanged(false);
					}
				}
				else
				{
					$result->addErrors($prepareResult->getErrors());
				}
			}
			elseif ($item instanceof ParamArray)
			{
				foreach ($item as $subItem)
				{
					if ($subItem->isDeleted())
					{
						if ($subItem->getPrimaryId())
						{
							$dropIds[] = $subItem->getPrimaryId();
						}
					}
					else
					{
						$prepareResult = $subItem->prepareFields();
						if ($prepareResult->isSuccess())
						{
							if ($subItem->isChanged())
							{
								$dataEntityCollection->add($subItem->getDataEntity());
								$subItem->markChanged(false);
							}
						}
						else
						{
							$result->addErrors($prepareResult->getErrors());
						}
					}
				}
			}
		}

		foreach ($this->droppedItems as $item)
		{
			if ($item instanceof Param)
			{
				if ($item->getPrimaryId())
				{
					$dropIds[] = $item->getPrimaryId();
				}
			}
			elseif ($item instanceof ParamArray)
			{
				foreach ($item as $subItem)
				{
					if ($subItem->getPrimaryId())
					{
						$dropIds[] = $subItem->getPrimaryId();
					}
				}
			}
		}

		$saveResult = $dataEntityCollection->save(true);
		if ($saveResult->isSuccess())
		{
			if (!empty($dropIds))
			{
				MessageParamTable::deleteBatch(['=ID' => $dropIds]);
			}

			$this->droppedItems = [];
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	/**
	 * Drops all message params.
	 *
	 * @param bool $deleteWithTs
	 * @return Result
	 */
	public function delete(bool $deleteWithTs = false): Result
	{
		$result = new Result;

		foreach ($this as $item)
		{
			unset($this[$item->getName()]);
		}

		if ($this->getMessageId())
		{
			$filter = [
				'=MESSAGE_ID' => $this->getMessageId(),
			];
			if (!$deleteWithTs)
			{
				$filter['!=PARAM_NAME'] = self::TS;
			}
			MessageParamTable::deleteBatch($filter);
		}

		$this->droppedItems = [];
		$this->isLoaded = false;

		return $result;
	}

	//endregion

	//region Params get/set

	/**
	 * Returns instance of Parameter.
	 *
	 * @param string $paramName
	 * @return MessageParameter
	 */
	public static function create(string $paramName): MessageParameter
	{
		$type = self::getType($paramName);

		if ($type['className'])
		{
			$paramClass = $type['className'];
		}
		else
		{
			$paramClass = Param::class;
		}

		return (new $paramClass)
			->setName($paramName)
			->setType($type['type'] ?? Param::TYPE_STRING)
		;
	}

	/**
	 * @param string $paramName
	 * @return bool
	 */
	public function isSet(string $paramName): bool
	{
		return isset($this[$paramName]);
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		if (
			!parent::offsetExists($offset)
			&& !$this->isLoaded()
			&& $this->getMessageId()
		)
		{
			// lazyload
			$this->loadByMessageId($this->getMessageId());
		}

		return parent::offsetExists($offset);
	}

	/**
	 * @param string $paramName
	 * @return MessageParameter
	 */
	public function get(string $paramName): MessageParameter
	{
		if (!isset($this[$paramName]))// lazyload
		{
			$this[$paramName] = self::create($paramName);
		}

		return $this[$paramName];
	}

	/**
	 * @param string $paramName
	 * @param mixed $parameter
	 * @return self
	 */
	public function set(string $paramName, $parameter): self
	{
		if ($parameter instanceof MessageParameter)
		{
			$this[$paramName] = $parameter;
		}
		else
		{
			$this[$paramName] = self::create($paramName);
			$this[$paramName]->load($parameter);
		}

		if ($this->getMessageId())
		{
			$this[$paramName]->setMessageId($this->getMessageId());
		}

		return $this;
	}

	/**
	 * @param mixed $parameter
	 * @return self
	 */
	public function add(MessageParameter $parameter): self
	{
		$this[$parameter->getName()] = $parameter;

		if ($this->getMessageId())
		{
			$parameter->setMessageId($this->getMessageId());
		}

		return $this;
	}

	/**
	 * Alias to add method.
	 *
	 * @param string $offset
	 * @param MessageParameter $entry
	 * @return void
	 * @throws ArgumentTypeException
	 */
	public function offsetSet($offset, $entry): void
	{
		if (!($entry instanceof MessageParameter))
		{
			$entryClass = \get_class($entry);
			throw new ArgumentTypeException("Entry is instance of {$entryClass}, but collection support MessageParameter");
		}

		if ($this->getMessageId())
		{
			$entry->setMessageId($this->getMessageId());
		}

		parent::offsetSet($offset, $entry);
	}

	/**
	 * @param string $paramName
	 * @return self
	 */
	public function remove(string $paramName = ''): self
	{
		if (empty($paramName))
		{
			foreach ($this as $paramName => $param)
			{
				unset($this[$paramName]);
			}
			$this->isLoaded = true;
		}
		else
		{
			unset($this[$paramName]);
		}

		return $this;
	}

	/**
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
		if (parent::offsetExists($offset))
		{
			$this[$offset]->markDrop();
			$this->droppedItems[] = $this[$offset];
		}

		parent::offsetUnset($offset);
	}

	/**
	 * @param int $messageId
	 * @return $this
	 */
	public function setMessageId(int $messageId): self
	{
		$this->messageId = $messageId;
		foreach ($this as $param)
		{
			$param->setMessageId($this->messageId);
		}

		return $this;
	}

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	//endregion

	/**
	 * @return array<string, string|array>
	 */
	public function toRestFormat(): array
	{
		$result = [];
		foreach ($this as $paramName => $param)
		{
			if ($param->hasValue())
			{
				$result[$paramName] = $param->toRestFormat();
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function toPullFormat(): array
	{
		$result = [];
		foreach ($this as $paramName => $param)
		{
			if ($param->hasValue())
			{
				$result[$paramName] = $param->toPullFormat();
			}
		}

		return $result;
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function fill(array $values): self
	{
		foreach ($values as $paramName => $value)
		{
			$this->get($paramName)->setValue($value);
		}

		return $this;
	}
}