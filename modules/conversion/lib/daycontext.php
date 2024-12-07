<?php

namespace Bitrix\Conversion;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Data\LocalStorage\SessionLocalStorage;
use Bitrix\Main\SiteTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Date;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;

final class DayContext extends Internals\BaseContext
{
	/** @var self $instance */
	private static self $instance;

	private static array $contextData;

	/**
	 * Add value to counter. If counter not exists set counter to value.
	 *
	 * @param Date $day Counter date.
	 * @param string $name Counter name.
	 * @param int|float $value Number to add.
	 * @return void
	 */
	public function addCounter($day, $name, $value = null): void
	{
		if (func_num_args() === 2)
		{
			$value = $name;
			$name = $day;
			$day = new Date();
		}

		$instance = self::getInstance();

		if ($this->getId() === null && $this === $instance)
		{
			$context = self::getContextData();
			$context['PENDING_COUNTERS'] ??= [];
			$context['PENDING_COUNTERS'][$name] ??= 0;
			$context['PENDING_COUNTERS'][$name] += (float)$value;
			self::setContextData($context);
			unset($context);
		}
		else
		{
			parent::addCounter($day, $name, $value);
		}
	}

	/**
	 * Add value to counter (once a day per person). If counter not exists set counter to value.
	 *
	 * @param string $name Counter name.
	 * @param int|float $value Number to add.
	 * @return void
	 */
	public function addDayCounter($name, $value): void
	{
		$instance = self::getInstance();

		if ($this->getId() === null && $this === $instance)
		{
			$context = self::getContextData();
			$context['PENDING_DAY_COUNTERS'] ??= [];
			$context['PENDING_DAY_COUNTERS'][$name] = (float)$value;
			self::setContextData($context);
			unset($context);
		}
		else
		{
			$context = self::getContextData();
			if (!in_array($name, $context['UNIQUE'], true))
			{
				$context['UNIQUE'][] = $name;
				self::setContextData($context);

				$this->addCounter(new Date(), $name, $value);
				$this->setCookie();
			}
			unset($context);
		}
	}

	/**
	 * Subtraction value from counter. If counter not exists does anything.
	 *
	 * @param Date $day
	 * @param string $name
	 * @param int|float $value
	 * @return void
	 */
	public function subDayCounter($day, $name, $value): void
	{
		$this->subCounter($day, $name, $value);

		// is today - clear session
		$isToday = $day instanceof Date && $day->format('dmY') === date('dmY');
		if ($isToday)
		{
			$context = self::getContextData();
			$i = array_search($name, $context['UNIQUE'], true);
			if ($i !== false)
			{
				unset($context['UNIQUE'][$i]);
				self::setContextData($context);
				$this->setCookie();
			}
		}
	}

	/**
	 * Add currency value to counter. If counter not exists set counter to value.
	 *
	 * @param string $name Counter name.
	 * @param int|float|string $value Numeric value.
	 * @param string $currency Currency code (eg: RUB).
	 * @return void
	 */
	public function addCurrencyCounter($name, $value, $currency): void
	{
		$this->addCounter(new Date(), $name, Utils::convertToBaseCurrency($value, $currency));
	}

	/**
	 * Subtraction currency value from counter
	 *
	 * @param Date $day
	 * @param string $name
	 * @param int|float $value
	 * @param string $currency
	 * @return void
	 */
	public function subCurrencyCounter($day, $name, $value, $currency): void
	{
		$this->subCounter($day, $name, Utils::convertToBaseCurrency($value, $currency));
	}

	/**
	 * Attach entity item to context.
	 *
	 * @param string $entity Entity type.
	 * @param string|int $item Entity item ID.
	 * @throws ArgumentTypeException
	 * @return void
	 */
	public function attachEntityItem($entity, $item): void
	{
		if (!is_string($entity))
		{
			throw new ArgumentTypeException('entity', 'string');
		}

		if (!is_scalar($item))
		{
			throw new ArgumentTypeException('item', 'scalar');
		}

		$instance = self::getInstance();

		if ($this->getId() === null && $this === $instance)
		{
			$context = self::getContextData();
			$context['PENDING_ENTITY_ITEMS'] ??= [];
			$context['PENDING_ENTITY_ITEMS'][$entity . ':' . $item] = [
				'ENTITY' => $entity,
				'ITEM' => $item,
			];
			self::setContextData($context);
			unset($context);
		}
		else
		{
			try
			{
				Internals\ContextEntityItemTable::add([
					'CONTEXT_ID' => $this->id,
					'ENTITY' => $entity,
					'ITEM' => $item,
				]);
			}
			catch (\Bitrix\Main\DB\SqlQueryException)
			{
			}
		}
	}

	/**
	 * Get context of attached entity item.
	 *
	 * @param string $entity Entity type.
	 * @param string|int $item Entity item ID.
	 * @return self
	 */
	public static function getEntityItemInstance($entity, $item): self
	{
		$instance = self::getInstance();

		$context = Internals\ContextEntityItemTable::getRow([
			'select' => [
				'CONTEXT_ID',
			],
			'filter' => [
				'=ENTITY' => $entity,
				'=ITEM' => $item,
			],
		]);

		$contextId = (int)(!empty($context['CONTEXT_ID']) ? $context['CONTEXT_ID'] : self::EMPTY_CONTEXT_ID);
		if ($contextId !== $instance->getId())
		{
			$instance = new self;
			$instance->setId($contextId);
		}

		return $instance;
	}

	/**
	 * Returns context for given Site
	 *
	 * @param string $siteId Site ID.
	 * @return self
	 */
	public static function getSiteInstance($siteId): self
	{
		$siteId = (string)$siteId;

		$instance = self::getInstance();

		if (preg_match('/[a-z0-9_]{2}/i', $siteId) && self::getSiteId() !== $siteId && \CSite::getById($siteId)->fetch())
		{
			$instance = new self;

			$eventManager = EventManager::getInstance();
			foreach ($eventManager->findEventHandlers('conversion', 'OnSetDayContextAttributes') as $handler)
			{
				ExecuteModuleEventEx(
					$handler,
					[
						$instance,
					]
				);
			}
			unset($eventManager);

			$instance->setAttribute('conversion_site', $siteId);
			$instance->save();
		}

		return $instance;
	}

	/**
	 * Get day context singleton instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			$instance = new self;

			$currentContext = self::getDataFromStorage();
			if ($currentContext === null)
			{
				$currentContext = self::getDataFromCookie();
			}
			if ($currentContext === null)
			{
				$currentContext = self::getDefaultData();
			}
			self::setContextData($currentContext);
			$instance->setId($currentContext['ID']);

			self::$instance = $instance;
		}

		return self::$instance;
	}

	/** @internal */
	private function setCookie(): void
	{
		//$session = self::$session;
		$session = self::getContextData();

		$cookie = new Main\Web\Cookie(
			self::getVarName(),
			Json::encode([
				'ID' => $session['ID'],
				'EXPIRE' => $session['EXPIRE'],
				'UNIQUE' => $session['UNIQUE'],
			]),
			strtotime('+1 year'),
			false
		);
		$cookie->setHttpOnly(false);

		Main\Context::getCurrent()->getResponse()->addCookie($cookie);
	}

	/** @internal */
	public static function saveInstance(): void
	{
		$instance = self::getInstance();

		if ($instance->getId() === null)
		{
			$eventManager = EventManager::getInstance();
			foreach ($eventManager->findEventHandlers('conversion', 'OnSetDayContextAttributes') as $handler)
			{
				ExecuteModuleEventEx(
					$handler,
					[
						$instance,
					]
				);
			}
			unset($eventManager);

			$instance->save();
		}

		$session = self::getContextData();
		$session['ID'] = $instance->getId();
		self::setContextData($session);
		$instance->setCookie();

		if (!empty($session['PENDING_COUNTERS']) && is_array($session['PENDING_COUNTERS']))
		{
			$date = new Date();
			foreach ($session['PENDING_COUNTERS'] as $name => $value)
			{
				$instance->addCounter($date, $name, $value);
			}
			unset($date);
		}

		if (!empty($session['PENDING_DAY_COUNTERS']) && is_array($session['PENDING_DAY_COUNTERS']))
		{
			foreach ($session['PENDING_DAY_COUNTERS'] as $name => $value)
			{
				$instance->addDayCounter($name, $value);
			}
		}

		if (!empty($session['PENDING_ENTITY_ITEMS']) && is_array($session['PENDING_ENTITY_ITEMS']))
		{
			foreach ($session['PENDING_ENTITY_ITEMS'] as $i)
			{
				$instance->attachEntityItem($i['ENTITY'], $i['ITEM']);
			}
		}
	}

	/** @internal */
	public static function getVarName()
	{
		static $name;

		if (!$name)
		{
			$name = 'BITRIX_CONVERSION_CONTEXT_' . self::getSiteId();
		}

		return $name;
	}

	/** @internal */
	public static function getSiteId()
	{
		static $siteId = null;

		if ($siteId === null)
		{
			if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
			{
				$row = SiteTable::getRow([
					'select' => [
						'ID',
						'DEF',
						'SORT',
					],
					'order'  => [
						'DEF' => 'DESC',
						'SORT' => 'ASC',
					],
					'cache' => [
						'ttl' => 86400,
					],
				]);
				if ($row)
				{
					$siteId = $row['ID'];
				}
			}
			else
			{
				$siteId = SITE_ID;
			}
		}

		return $siteId;
	}

	private static function getLocalStorage(): SessionLocalStorage
	{
		return Application::getInstance()->getLocalSession(self::getVarName());
	}

	private static function getDataFromStorage(): ?array
	{
		$storage = self::getLocalStorage();
		$data = $storage->getData();

		return self::checkStorageData($data) ? $data : null;
	}

	private static function setDataToStorage(array $data): void
	{
		$storage = self::getLocalStorage();
		$storage->setData($data);
	}

	private static function checkStorageData(mixed $data): bool
	{
		if (!is_array($data))
		{
			return false;
		}
		if (!is_int($data['ID'] ?? null))
		{
			return false;
		}
		if (($data['EXPIRE'] ?? null) !== self::getCurrentExpireValue())
		{
			return false;
		}
		if (!is_array($data['UNIQUE'] ?? null))
		{
			return false;
		}

		return true;
	}

	private static function getCurrentExpireValue(): int
	{
		$result = strtotime('today 23:59');

		return $result === false ? 0 : $result;
	}

	private static function getDefaultData(): array
	{
		return [
			'ID' => null,
			'EXPIRE' => self::getCurrentExpireValue(),
			'UNIQUE' => [],
		];
	}

	private static function getDataFromCookie(): ?array
	{
		$request = Main\Context::getCurrent()->getRequest();

		$cookie = $request->getCookie(self::getVarName());
		if ($cookie === null || $cookie === '')
		{
			return null;
		}
		try
		{
			$data = Json::decode($cookie);
		}
		catch (ArgumentException)
		{
			$data = null;
		}

		return self::checkCookieData($data) ? $data : null;
	}

	private static function checkCookieData(mixed $cookie): bool
	{
		if (!is_array($cookie))
		{
			return false;
		}
		if (!is_array($cookie['UNIQUE'] ?? null))
		{
			return false;
		}
		if (($cookie['EXPIRE'] ?? null) !== self::getCurrentExpireValue())
		{
			return false;
		}

		$id = $cookie['ID'] ?? null;
		if (!is_int($id))
		{
			return false;
		}
		if ($id === self::EMPTY_CONTEXT_ID)
		{
			return true;
		}

		$row = Internals\ContextTable::getRow([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ID' => $id,
			],
		]);

		return $row !== null;
	}

	private static function setContextData(array $data): void
	{
		self::$contextData = $data;
		self::setDataToStorage(self::$contextData);
	}

	private static function getContextData(): array
	{
		return self::$contextData;
	}
}
