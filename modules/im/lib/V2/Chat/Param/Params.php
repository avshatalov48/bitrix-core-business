<?php

namespace Bitrix\Im\V2\Chat\Param;

use Bitrix\Im\Model\ChatParamTable;
use Bitrix\Im\Model\EO_ChatParam;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Param>
 */
class Params extends Registry
{
	public const EVENT_CHAT_PARAM_INIT = 'OnChatParamInit';
	public const
		IS_COPILOT = 'IS_COPILOT',
		COPILOT_ROLES = 'COPILOT_ROLES',
		COPILOT_MAIN_ROLE = 'COPILOT_MAIN_ROLE'
	;
	public const CHAT_PARAMS = [
		self::IS_COPILOT,
		self::COPILOT_ROLES,
		self::COPILOT_MAIN_ROLE,
	];

	protected int $chatId;
	protected string $paramName = '';
	protected bool $isCreated = false;
	protected array $droppedItems = [];
	protected static ?array $eventParams = null;

	private const CACHE_TTL = 18144000;
	private static ?array $instance = [];

	private function __construct(object|array $array = [], int $flags = 0, string $iteratorClass = "ArrayIterator")
	{
		parent::__construct($array, $flags, $iteratorClass);
	}

	public static function getInstance(int $chatId): Params
	{
		if (isset(self::$instance[$chatId]))
		{
			return self::$instance[$chatId];
		}

		self::$instance[$chatId] = new static();

		return self::$instance[$chatId]->setChatId($chatId)->getParams();
	}

	protected function createParam($paramName): Param
	{
		$eventParam = $this->createEventParam($paramName);
		if (isset($eventParam))
		{
			return $eventParam;
		}

		switch ($paramName)
		{
			case (self::IS_COPILOT):
				return (new Param())->setType(Param::TYPE_BOOL);

			case (self::COPILOT_MAIN_ROLE):
				return (new Param())->setType(Param::TYPE_STRING);

			default:
				return (new Param())->setType(Param::TYPE_STRING);
		}
	}

	protected function createEventParam(string $paramName): ?Param
	{
		foreach (self::$eventParams as $name => $data)
		{
			if ($name === $paramName)
			{
				if (isset($data['type']) && $this->isValidType((string)$data['type']))
				{
					return (new Param())->setType($data['type']);
				}

				if (
					Loader::includeModule($data['moduleId'])
					&& class_exists($data['className'])
					&& is_subclass_of($data['className'], Param::class)
				)
				{
					return (new $data['className']);
				}
			}
		}

		return null;
	}

	protected function load($source): Params
	{
		if (self::$eventParams === null)
		{
			$this->getParamsByEvent();
		}

		if (is_array($source))
		{
			$this->initByArray($source);
		}
		elseif ($source instanceof ORM\Objectify\Collection)
		{
			$this->initByEntitiesCollection($source);
		}
		elseif ($source instanceof ORM\Objectify\EntityObject)
		{
			$this->initByDataEntity($source);
		}

		$this->isLoaded = true;

		return $this;
	}

	protected function isValidType(string $type): bool
	{
		return in_array($type, Param::PARAM_TYPES, true);
	}

	protected function getParamsByEvent(): void
	{
		$allParams = [];

		$event = new Event('im', self::EVENT_CHAT_PARAM_INIT);
		$event->send();
		$resultList = $event->getResults();

		foreach ($resultList as $eventResult)
		{
			if ($eventResult->getType() === EventResult::SUCCESS)
			{
				$params = $eventResult->getParameters();

				if (is_array($params))
				{
					foreach ($params as $paramName => $paramData)
					{
						$allParams[$paramName] = $paramData;
					}
				}
			}
		}

		self::$eventParams = $allParams;
	}

	public static function loadWithoutChat(array $source): self
	{
		$params = new self;
		$source = $params->filterParamData($source);

		foreach ($source as $key => $item)
		{
			if (!isset($item['CHAT_ID']))
			{
				$source[$key]['CHAT_ID'] = 0;
			}
		}

		$params->setChatId(0);
		$params->setIsCreated(true);

		return $params->load($source);
	}

	public function saveWithNewChatId(int $chatId): Result
	{
		$this->setChatId($chatId);
		foreach ($this as $item)
		{
			$item->setChatId($chatId);
		}

		$this->setIsCreated(false);

		return $this->save();
	}

	protected function getParams(): Params
	{
		$chatId = $this->getChatId();

		$cache = Application::getInstance()->getCache();
		if ($cache->initCache(self::CACHE_TTL, $this->getCacheId(), $this->getCacheDir()))
		{
			$params = $cache->getVars();
		}
		else
		{
			$params = Param::getDataClass()::query()
				->setSelect(['ID', 'CHAT_ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'])
				->where('CHAT_ID', '=', $chatId)
				->fetchAll()
				;

			$cache->startDataCache();
			$cache->endDataCache($params);
		}

		$this->load($params);

		return $this;
	}

	protected function initByEntitiesCollection(ORM\Objectify\Collection $entitiesCollection): Params
	{
		/** @var EO_ChatParam $entity */
		foreach ($entitiesCollection as $entity)
		{
			$paramName = $entity->getParamName();
			if (!parent::offsetExists($paramName))
			{
				$this[$paramName] = $this->createParam($paramName);
			}

			$item = $this[$paramName];

			$entity->setChatId($this->getChatId());
			$item->load($entity);
		}

		return $this;
	}

	protected function initByArray(array $items): Params
	{
		foreach ($items as $entityId => $entity)
		{
			if (is_array($entity) && isset($entity['PARAM_NAME']))
			{
				$paramName = $entity['PARAM_NAME'];
				if (!parent::offsetExists($paramName))
				{
					$this[$paramName] = $this->createParam($paramName);
				}

				$item = $this[$paramName];

				$entity['CHAT_ID'] = $this->getChatId();
				$item->load($entity);
			}
		}

		return $this;
	}

	protected function initByDataEntity(ORM\Objectify\EntityObject $entity): Params
	{
		$paramName = $entity->getParamName();
		if (!parent::offsetExists($paramName))
		{
			$this[$paramName] = $this->createParam($paramName);
		}

		$item = $this[$paramName];

		$entity->setChatId($this->getChatId());
		$item->load($entity);

		return $this;
	}

	protected function save(): Result
	{
		$result = new Result;

//		/** @var EO_MessageParam_Collection $dataEntityCollection */
		$entityCollectionClass = Param::getDataClass()::getCollectionClass();
		$dataEntityCollection = new $entityCollectionClass;

		$dropIds = [];

		$itemKeyToUnset = [];
		foreach ($this as $item)
		{
			if ($item->isDeleted())
			{
				$itemKeyToUnset[] = $item->getName();
				continue;
			}
			if (!$item->hasValue())
			{
				continue;
			}

			if ($item instanceof Param)
			{
				if (!$item->isChanged())
				{
					continue;
				}

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
		}
		$this->unsetByKeys($itemKeyToUnset);

		foreach ($this->droppedItems as $item)
		{
			if ($item instanceof Param)
			{
				if ($item->getPrimaryId())
				{
					$dropIds[] = $item->getPrimaryId();
				}
			}
		}

		$saveResult = $dataEntityCollection->save(true);
		if ($saveResult->isSuccess())
		{
			if (!empty($dropIds))
			{
				ChatParamTable::deleteBatch(['=ID' => $dropIds]);
			}

			self::cleanCache($this->chatId);

			$this->droppedItems = [];
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	public function addParamByName(string $paramName, mixed $paramValue): Result
	{
		$result = new Result();

		if (!$this->isValidParamName($paramName))
		{
			return $result;
		}

		$this->load([['CHAT_ID' => $this->getChatId(), 'PARAM_NAME' => $paramName, 'PARAM_VALUE' => $paramValue]]);

		if ($this->get($paramName) !== null)
		{
			$this->get($paramName)->markChanged();
		}

		return $this->save();
	}

	public function addParamByArray(?array $chatParams): Result
	{
		$result = new Result();

		if (!isset($chatParams))
		{
			return $result;
		}

		$chatParams = $this->filterParamData($chatParams);

		$addParams = [];
		foreach ($chatParams as $chatParam)
		{
			if (!isset($chatParam['PARAM_NAME']) || !isset($chatParam['PARAM_VALUE']))
			{
				continue;
			}

			switch ($chatParam['PARAM_NAME'])
			{
				default:
					$addParams[] = [
						'CHAT_ID' => $this->getChatId(),
						'PARAM_NAME' => $chatParam['PARAM_NAME'] ?? null,
						'PARAM_VALUE' => $chatParam['PARAM_VALUE'] ?? null,
					];
			}
		}

		$this->load($addParams);

		foreach ($addParams as $param)
		{
			if ($this->get($param['PARAM_NAME']) !== null)
			{
				$this->get($param['PARAM_NAME'])->markChanged();
			}
		}

		return $this->save();
	}

	public function addParamByObject(Param $param): Result
	{
		if ($param->getName() !== null)
		{
			$this[$param->getName()] = $param;
			$param->setChatId($this->getChatId());

			return $this->save();
		}

		return (new Result())->addError((new ParamError(ParamError::EMPTY_PARAM_NAME)));
	}

	public function isValidParamName(string $paramName): bool
	{
		foreach (self::$eventParams as $name => $param)
		{
			if ($paramName === $name)
			{
				return true;
			}
		}

		return in_array($paramName, self::CHAT_PARAMS, true);
	}

	protected function filterParamData(array $chatParams): array
	{
		foreach ($chatParams as $key => $chatParam)
		{
			if (!is_array($chatParam))
			{
				unset($chatParams[$key]);
				continue;
			}

			if (!isset($chatParam['PARAM_NAME']) || !isset($chatParam['PARAM_VALUE']))
			{
				unset($chatParams[$key]);
			}
		}

		return $chatParams;
	}

	public function updateParam(string $paramName, $paramValue): Result
	{
		if ($this->offsetExists($paramName))
		{
			$param = $this->offsetGet($paramName);

			if ($param instanceof Param)
			{
				$param->setValue($paramValue);
				return $this->save();
			}
		}

		return (new Result())->addError((new ParamError(ParamError::EMPTY_PARAM)));
	}

	/**
	 * Drops all message params.
	 */
	public function deleteAll(): Params
	{
		$keysToUnset = [];
		foreach ($this as $key => $item)
		{
			$keysToUnset[$key] = $key;
		}
		$this->unsetByKeys($keysToUnset);

		if ($this->getChatId())
		{
			$filter = [
				'=CHAT_ID' => $this->getChatId(),
			];

			ChatParamTable::deleteBatch($filter);
		}

		$this->droppedItems = [];
		$this->isLoaded = false;

		self::cleanCache($this->getChatId());

		return $this;
	}

	public function deleteParam(string $paramName): Params
	{
		if (!$this->offsetExists($paramName))
		{
			return $this;
		}

		$this->unsetByKeys([$paramName]);

		if ($this->getChatId())
		{
			$filter = [
				'=CHAT_ID' => $this->getChatId(),
				'=PARAM_NAME' => $paramName,
			];

			ChatParamTable::deleteBatch($filter);
		}

		self::cleanCache($this->getChatId());

		return $this;
	}

	public function get(string $paramName): ?Param
	{
		return $this[$paramName] ?? null;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function setChatId(int $chatId): self
	{
		$this->chatId = $chatId;
		foreach ($this as $param)
		{
			$param->setChatId($this->chatId);
		}

		return $this;
	}

	public function isCreated(): bool
	{
		return $this->isCreated;
	}
	public function setIsCreated(bool $isCreated): void
	{
		$this->isCreated = $isCreated;
	}

	public function toRestFormat(): array
	{
		$result = [];
		foreach ($this as $paramName => $param)
		{
			if ($param->hasValue() && !$param->isHidden())
			{
				$result[$paramName] = $param->toRestFormat();
			}
		}

		return $result;
	}

	private function getCacheId(): string
	{
		return "chat_params_{$this->chatId}";
	}

	private function getCacheDir(): string
	{
		return static::getCacheDirByChatId($this->chatId);
	}

	private static function getCacheDirByChatId(int $chatId): string
	{
		$cacheSubDir = $chatId % 100;

		return "/bx/imc/chatdata/params/1/{$cacheSubDir}/{$chatId}";
	}

	public static function cleanCache(int $chatId): void
	{
		Application::getInstance()->getCache()->cleanDir(static::getCacheDirByChatId($chatId));
	}
}