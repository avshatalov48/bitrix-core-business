<?php

namespace Bitrix\Im\V2\Settings\Preset;

use Bitrix\Im\Configuration\Configuration;
use Bitrix\Im\Model\OptionGroupTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Settings\CacheManager;
use Bitrix\Im\V2\Settings\Entity\General;
use Bitrix\Im\V2\Settings\Entity\Notify;

class Preset implements RestConvertible
{
	public const BIND_GENERAL = 'toGeneral';
	public const BIND_NOTIFY = 'toNotify';

	/** @var Preset[]  */
	private static array $instances = [];

	public ?Notify $notify = null;
	public ?General $general = null;

	private ?int $id = null;
	private ?int $sort = null;
	private ?string $name = null;
	private ?int $personalUserId = null;
	private bool $isExist = false;

	private function __construct()
	{
	}

	public static function getInstance(?int $id = null): Preset
	{
		if (isset(static::$instances[$id]))
		{
			return static::$instances[$id];
		}

		$instance = new static();
		if (!$id)
		{
			return $instance;
		}
		$loadResult = $instance->load($id);
		if (!$loadResult->isSuccess())
		{
			return $instance;
		}

		static::$instances[$id] = $instance;

		return $instance;
	}

	public static function getPersonal(int $userId): Preset
	{
		$instance = new static();

		$loadResult = $instance->loadByUserId($userId);
		if (!$loadResult->isSuccess())
		{
			return $instance;
		}

		self::$instances[$instance->id] = $instance;

		return $instance;
	}

	public static function getDefaultPreset(): Preset
	{
		$defaultPresetId = Configuration::getDefaultPresetId();

		return static::getInstance($defaultPresetId);
	}

	public function initPersonal(int $userId): Preset
	{
		$this->id = Configuration::createUserPreset($userId);
		$this->sort = Configuration::USER_PRESET_SORT;
		$this->personalUserId = $userId;

		$this->notify = new Notify($this->id);
		$this->general = new General($this->id);

		$this->general->fillDataBase();
		$this->notify->fillDataBase($this->general->isSimpleNotifySchema(), $this->general->getSimpleNotifyScheme());

		static::$instances[$this->id] = $this;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return int|null
	 */
	public function getSort(): ?int
	{
		return $this->sort;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return int|null
	 */
	public function getPersonalUserId(): ?int
	{
		return $this->personalUserId;
	}

	public function isPersonal(int $userId): bool
	{
		return $this->personalUserId === $userId;
	}

	public function isExist(): bool
	{
		return $this->isExist;
	}

	public static function getRestEntityName(): string
	{
		return 'preset';
	}

	/**
	 * @param int $userId
	 * @param array $bindingConfiguration
	 * @return Result
	 * @throws \Exception
	 */
	public function bindToUser(int $userId, array $bindingConfiguration): Result
	{
		$result = new Result();

		$binding = [];
		if (in_array(self::BIND_GENERAL, $bindingConfiguration, true))
		{
			$binding['GENERAL_GROUP_ID'] = $this->id;
		}
		if (in_array(self::BIND_NOTIFY, $bindingConfiguration, true))
		{
			$binding['NOTIFY_GROUP_ID'] = $this->id;
		}

		if (empty($binding))
		{
			return $result->addError(new PresetError(PresetError::BINDING_NOT_SPECIFIED));
		}

		$updateResult = OptionUserTable::update($userId, $binding);

		if (!$updateResult->isSuccess())
		{
			return $result->addErrors($updateResult->getErrors());
		}

		return $result->setResult(true);
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'sort' => $this->getSort(),
			'userId' => $this->getPersonalUserId(),
			'general' => $this->general->toRestFormat(),
			'notify' => $this->notify->toRestFormat(),
		];
	}

	/**
	 * @param int $presetId
	 * @return Result
	 */
	public function load(int $presetId): Result
	{
		$this->notify = new Notify($presetId);
		$this->general = new General($presetId);

		$result = $this->loadFromCache($presetId);
		if ($result->isSuccess())
		{
			$this->isExist = true;

			return $result;
		}

		$result = $this->loadFromDB($presetId);
		if ($result->isSuccess())
		{
			$this->isExist = true;

			return $result;
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return Result<bool>
	 */
	private function loadFromCache(int $id): Result
	{
		$result = new Result();
		$cache = CacheManager::getPresetCache($id);
		$presetValue = $cache->getValue();

		if (!empty($presetValue))
		{
			$this->id = $id;
			$this->name = $presetValue['name'] ?? null;
			$this->sort = $presetValue['sort'];
			$this->personalUserId = $presetValue['userId'] ?? null;
			$this->notify->load($presetValue['notify'] ?? []);
			$this->general->load($presetValue['general'] ?? []);

			return $result->setResult(true);
		}

		return $result->addError(new PresetError(PresetError::NOT_FOUND));
	}

	/**
	 * @param int $id
	 * @return Result<bool>
	 */
	private function loadFromDB(int $id): Result
	{
		$result = new Result();
		$query =
			OptionGroupTable::query()
				->setSelect(['ID', 'NAME', 'SORT', 'USER_ID'])
				->where('ID', $id)
				->setLimit(1)
		;

		$presetValue = $query->fetch();
		if ($presetValue === false)
		{
			return $result->addError(new PresetError(PresetError::NOT_FOUND));
		}

		$this->id = $id;
		$this->name = $presetValue['NAME'];
		$this->sort = $presetValue['SORT'];
		$this->personalUserId = $presetValue['USER_ID'];

		$this->saveInCache();

		return $result->setResult(true);
	}

	private function loadByUserId(int $userId): Result
	{
		$result = new Result();
		$query = OptionGroupTable::query()
			->setSelect(['ID', 'NAME', 'SORT'])
			->where('USER_ID', $userId)
			->setLimit(1)
		;
		$row = $query->fetch();
		if (!$row)
		{
			return $result->addError(new PresetError(PresetError::NOT_FOUND));
		}

		$this->id = $row['ID'];
		$this->sort = $row['SORT'];
		$this->name = $row['NAME'];
		$this->personalUserId = $userId;
		$this->isExist = true;
		$this->general = new General($this->id);
		$this->notify = new Notify($this->id);

		return $result->setResult(true);
	}

	private function saveInCache(): Preset
	{
		$cache = CacheManager::getPresetCache($this->id);
		$cache->setValue([
			'id' => $this->id,
			'name' => $this->name,
			'sort' => $this->sort,
			'userId' => $this->personalUserId,
			'notify' => $this->notify->getSettings()->getResult(),
			'general' => $this->general->getSettings()->getResult(),
		]);

		return $this;
	}
}