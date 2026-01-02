<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot;

use Bitrix\Landing;
use Bitrix\Landing\Agent;
use Bitrix\Landing\Copilot\Connector\Chat\ChatBotMessageDto;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Scenario\IScenario;
use Bitrix\Landing\Copilot\Generation\Scenario\Scenarist;
use Bitrix\Landing\Copilot\Generation\Timer;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Model\GenerationsTable;
use Bitrix\Landing\Metrika;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\Web\Json;

/**
 * This class is responsible for generating site content using various managers and connectors.
 * It manages the generation process, scenario execution, metrika analytics, and error handling.
 */
class Generation
{
	private const EVENT_ERROR = 'onCopilotError';
	private const EVENT_TIMER = 'onCopilotTimeIsOver';
	private const EVENT_GENERATION_CREATE = 'onGenerationCreate';
	private const EVENT_GENERATION_ERROR = 'onGenerationError';
	private const EVENT_GENERATION_FINISH = 'onGenerationFinish';

	private const DATA_METRIKA_KEY = 'metrikaFields';

	private int $id;
	private ?int $chatId = null;
	private IScenario $scenario;
	private Data\Site $siteData;
	private ?array $data = null;
	private int $step;
	private int $authorId;

	private Scenarist $scenarist;
	private Timer $timer;
	private ?Generation\Event $event = null;
	private ?Metrika\FieldsDto $metrikaFields = null;
	private Metrika\MetrikaProviderParamService $metrikaProviderParamService;


	/**
	 * Generation constructor.
	 * Initializes authorId, siteData, and timer.
	 */
	public function __construct()
	{
		$this->authorId = Landing\Manager::getUserId();

		$this->siteData = new Data\Site();
		$this->timer = new Timer();

		$this->metrikaProviderParamService = new Metrika\MetrikaProviderParamService();
	}

	/**
	 * Returns the ID of the user who created the site.
	 *
	 * @return int
	 */
	public function getAuthorId(): int
	{
		return $this->authorId;
	}

	/**
	 * Sets the chat ID if generation is started by chat.
	 *
	 * @param int $chatId Chat ID.
	 *
	 * @return Generation
	 */
	public function setChatId(int $chatId): self
	{
		if ($chatId > 0)
		{
			$this->chatId = $chatId;
		}

		return $this;
	}

	/**
	 * Gets the chat ID if generation is started by chat.
	 *
	 * @return int|null
	 */
	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	/**
	 * Sets the scenario for this generation.
	 *
	 * @param IScenario $scenario
	 *
	 * @return Generation
	 */
	public function setScenario(IScenario $scenario): self
	{
		$this->scenario = $scenario;

		return $this;
	}

	/**
	 * Gets the scenario for this generation.
	 *
	 * @return IScenario|null
	 */
	public function getScenario(): ?IScenario
	{
		return $this->scenario ?? null;
	}

	/**
	 * Sets the site data for this generation.
	 *
	 * @param Data\Site $siteData
	 *
	 * @return Generation
	 */
	public function setSiteData(Data\Site $siteData): self
	{
		$this->siteData = $siteData;

		return $this;
	}

	/**
	 * Gets the site data for this generation.
	 *
	 * @return Data\Site
	 */
	public function getSiteData(): Data\Site
	{
		return $this->siteData;
	}

	/**
	 * Gets custom data for this generation.
	 *
	 * @param string|null $key Optional key to retrieve a specific value.
	 *
	 * @return mixed|null Returns the value for the given key, all data if key is null, or null if not set.
	 */
	public function getData(?string $key = null): mixed
	{
		if (!isset($key))
		{
			return $this->data;
		}

		return $this->data[$key] ?? null;
	}

	/**
	 * Sets custom data for this generation.
	 *
	 * @param string $key Data key.
	 * @param mixed $data Data value.
	 *
	 * @return void
	 */
	public function setData(string $key, mixed $data): void
	{
		if (!isset($this->data))
		{
			$this->data = [];
		}

		$this->data[$key] = $data;
	}

	/**
	 * Deletes a key from the custom data.
	 *
	 * @param string $key Data key to delete.
	 *
	 * @return void
	 */
	public function deleteData(string $key): void
	{
		if (is_array($this->data) && array_key_exists($key, $this->data))
		{
			unset($this->data[$key]);
		}
	}

	/**
	 * Sets the wishes for the site data.
	 *
	 * @param Data\Wishes $wishes
	 *
	 * @return Generation
	 */
	public function setWishes(Data\Wishes $wishes): self
	{
		$this->siteData->setWishes($wishes);

		return $this;
	}

	/**
	 * Gets the generation ID.
	 *
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id ?? null;
	}

	/**
	 * Gets the current step of the generation.
	 *
	 * @return int|null
	 */
	public function getStep(): ?int
	{
		return $this->step ?? null;
	}

	/**
	 * Gets the timer object for this generation.
	 *
	 * @return Timer
	 */
	public function getTimer(): Timer
	{
		return $this->timer;
	}

	/**
	 * Sets metrika fields for analytics and stores them in custom data.
	 *
	 * @param Metrika\FieldsDto $fields
	 *
	 * @return Generation
	 */
	public function setMetrikaFields(Metrika\FieldsDto $fields): self
	{
		$this->metrikaFields = $fields;
		$this->setData(self::DATA_METRIKA_KEY, $this->metrikaFields->toArray());

		return $this;
	}

	/**
	 * Gets metrika fields for analytics.
	 *
	 * @return Metrika\FieldsDto|null
	 */
	private function getMetrikaFields(): ?Metrika\FieldsDto
	{
		$saved =
			$this->getData(self::DATA_METRIKA_KEY)
				? Metrika\FieldsDto::fromArray($this->getData(self::DATA_METRIKA_KEY))
				: null
		;
		if (!$saved)
		{
			return $this->metrikaFields;
		}

		return $saved->addFields($this->metrikaFields);
	}

	/**
	 * Gets the Metrika analytics object for the given event.
	 *
	 * @param Metrika\Events $event
	 *
	 * @return Metrika\Metrika
	 */
	public function getMetrika(Metrika\Events $event): Metrika\Metrika
	{
		$metrika = new Metrika\Metrika(
			$this->scenario->getAnalyticCategory(),
			$event,
			Metrika\Tools::ai
		);

		$this->metrikaProviderParamService->setParams($metrika, $event);

		$fields = $this->getMetrikaFields();
		if (isset($fields))
		{
			foreach ($fields->params ?? [] as $param)
			{
				if (count($param) === 3)
				{
					$metrika->setParam(
						(int)$param[0],
						(string)$param[1],
						(string)$param[2],
					);
				}
			}

			if (isset($fields->subSection))
			{
				$metrika->setSubSection($this->metrikaFields->subSection);
			}
		}

		return $metrika;
	}

	/**
	 * Tries to find an existing generation by ID and initializes this instance with its data.
	 *
	 * @param int $generationId Generation ID.
	 *
	 * @return bool True if found and initialized, false otherwise.
	 */
	public function initById(int $generationId): bool
	{
		if ($generationId <= 0)
		{
			return false;
		}

		$filter =
			Query::filter()
				->where('ID', '=', $generationId)
		;

		return $this->initExists($filter);
	}

	/**
	 * Tries to find an existing generation by site ID and scenario, and initializes this instance with its data.
	 *
	 * @param int $siteId Site ID.
	 * @param IScenario $scenario Scenario instance.
	 *
	 * @return bool True if found and initialized, false otherwise.
	 */
	public function initBySiteId(int $siteId, IScenario $scenario): bool
	{
		$filter =
			Query::filter()
				->where('SCENARIO', '=', $scenario::class)
				->where('SITE_ID', '=', $siteId)
		;

		return $this->initExists($filter);
	}

	/**
	 * Initializes this instance with data from an existing generation matching the filter.
	 * @param Filter\ConditionTree $filter ORM filter for the query.
	 *
	 * @return bool True if found and initialized, false otherwise.
	 */
	private function initExists(Filter\ConditionTree $filter): bool
	{
		$generation = GenerationsTable::query()
			->where($filter)
			->setSelect(['ID', 'SCENARIO', 'STEP', 'CHAT_ID', 'SITE_DATA', 'DATA', 'CREATED_BY_ID'])
			->fetch()
		;

		if (!$generation)
		{
			return false;
		}

		$this->id = (int)$generation['ID'];
		$this->authorId = (int)$generation['CREATED_BY_ID'];

		if (isset($generation['STEP']))
		{
			$this->step = (int)$generation['STEP'];
		}

		if (isset($generation['CHAT_ID']))
		{
			$this->setChatId((int)$generation['CHAT_ID']);
		}

		if (!class_exists($generation['SCENARIO']))
		{
			return false;
		}
		$this->setScenario(new ($generation['SCENARIO'])());

		if (
			isset($generation['SITE_DATA'])
			&& is_array($generation['SITE_DATA'])
		)
		{
			$this->siteData = Data\Site::fromArray($generation['SITE_DATA']);
		}

		if (
			is_array($generation['DATA'])
			&& !empty($generation['DATA'])
		)
		{
			$this->data = $generation['DATA'];
		}

		if (!$this->initScenarist())
		{
			return false;
		}

		return true;
	}

	/**
	 * Runs the generation process.
	 *
	 * @return bool False if an error occurs while executing, true otherwise.
	 */
	public function execute(): bool
	{
		if (!isset($this->id) && !$this->save())
		{
			return false;
		}

		$connection = Application::getConnection();
		if (!$connection->lock($this->getLockName()))
		{
			$this->setAgent();

			return false;
		}

		if (
			!$this->isExecutable()
			|| !$this->initScenarist()
		)
		{
			return false;
		}

		$this->timer->start();
		$this->deleteAgent();

		try
		{
			$this->scenarist
				->onStepChange(fn(int $newStep) => $this->step = $newStep)
				->onSave(fn() => $this->save())
				->execute()
			;

			if ($this->scenarist->isFinished())
			{
				$this->onFinish();
			}
		}
		catch (GenerationException $e)
		{
			$this->sendMetrikaError($e);
			$this->scenario->getChatbot()?->sendErrorMessage(new ChatBotMessageDto(
				$this->getChatId() ?? 0,
				$this->id,
				$e->getCodeObject(),
				$e->getParams(),
			));
			$this->getEvent()->send(self::EVENT_GENERATION_ERROR);

			return false;
		}
		catch (\RuntimeException $e)
		{
			$this->setAgent();
			$this->getEvent()->send(self::EVENT_TIMER);

			return false;
		}
		catch (\Exception $e)
		{
			$this->getEvent()->sendError(
				self::EVENT_ERROR,
				$e->getMessage(),
			);

			return false;
		}

		$connection->unlock($this->getLockName());

		return true;
	}

	/**
	 * Checks if the generation is executable (all required properties are set).
	 *
	 * @return bool
	 */
	private function isExecutable(): bool
	{
		return isset(
			$this->id,
			$this->siteData,
			$this->scenario,
		);
	}

	/**
	 * Gets the lock name for this generation.
	 *
	 * @return string
	 */
	private function getLockName(): string
	{
		return 'landing_copilot_generation_' . ($this->id ?? 0);
	}

	/**
	 * Schedules an agent to retry execution.
	 *
	 * @return void
	 */
	private function setAgent(): void
	{
		Agent::addUniqueAgent('executeGeneration', [$this->id], 60, 10);
	}

	/**
	 * Removes the scheduled agent for this generation.
	 *
	 * @return void
	 */
	private function deleteAgent(): void
	{
		Agent::deleteUniqueAgent('executeGeneration', [$this->id]);
	}

	/**
	 * Finishes the generation process and marks it as completed.
	 *
	 * @return void
	 */
	public function finish(): void
	{
		if (!$this->initScenarist())
		{
			return;
		}

		$this->scenarist->finish();
		$this->onFinish();
	}

	/**
	 * Checks if the scenario has executed all steps.
	 *
	 * @return bool
	 */
	public function isFinished(): bool
	{
		if (!$this->initScenarist())
		{
			return false;
		}

		return $this->scenarist->isFinished();
	}

	/**
	 * Handles actions to perform when the generation is finished.
	 *
	 * @return void
	 */
	private function onFinish(): void
	{
		$this->getEvent()->send(self::EVENT_GENERATION_FINISH);
	}

	/**
	 * Checks if at least one scenario step has an error and was not executed.
	 *
	 * @return bool
	 */
	public function isError(): bool
	{
		if (!$this->initScenarist())
		{
			return true;
		}

		return $this->scenarist->isError();
	}

	/**
	 * Prepares the scenario to restart generation after an error by clearing errors.
	 *
	 * @return $this
	 */
	public function clearErrors(): self
	{
		if ($this->initScenarist())
		{
			$this->scenarist->clearErrors();
		}

		return $this;
	}

	/**
	 * Initializes the Scenarist object for this generation.
	 *
	 * @return bool True if successfully initialized, false otherwise.
	 */
	private function initScenarist(): bool
	{
		if (!isset(
			$this->id,
			$this->scenario,
			$this->siteData
		))
		{
			return false;
		}

		if (!isset($this->scenarist))
		{
			$this->scenarist = new Scenarist(
				$this->scenario,
				$this,
			);
		}

		return true;
	}

	/**
	 * Saves the current generation state to the database.
	 *
	 * @return bool True on success, false otherwise.
	 */
	private function save(): bool
	{
		if (!isset(
			$this->scenario,
			$this->siteData,
		))
		{
			return false;
		}

		$fields = [
			'SCENARIO' => $this->scenario::class,
			'STEP' => $this->step ?? null,
			'CHAT_ID' => $this->getChatId(),
			'SITE_ID' => $this->siteData->getSiteId(),
			'SITE_DATA' => $this->siteData->toArray(),
			'DATA' => $this->getData(),
			'CREATED_BY_ID' => $this->getAuthorId(),
		];

		if (isset($this->id) && $this->id)
		{
			$res = GenerationsTable::update($this->id, $fields);
			if ($res->isSuccess())
			{
				return true;
			}
		}
		else
		{
			$res = GenerationsTable::add($fields);
			if ($res->isSuccess())
			{
				$this->id = $res->getId();
				$this->getEvent()->send(self::EVENT_GENERATION_CREATE);

				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the event object for sending front-end and back-end events.
	 *
	 * @return Generation\Event
	 */
	public function getEvent(): Generation\Event
	{
		if (!$this->event)
		{
			$this->event = new Generation\Event($this);
		}

		if (isset($this->siteData))
		{
			$this->event
				->setSiteId($this->siteData->getSiteId())
				->setLandingId($this->siteData->getLandingId())
			;
		}

		return $this->event;
	}

	/**
	 * Checks if a generation with the given ID exists.
	 *
	 * @param int $id Generation ID.
	 *
	 * @return bool True if exists, false otherwise.
	 */
	public static function checkExists(int $id): bool
	{
		static $generations = [];
		if (!isset($generations[$id]))
		{
			$generation = GenerationsTable::query()
				->where('ID', '=', $id)
				->fetch()
			;

			$generations[$id] = (bool)$generation;
		}

		return $generations[$id];
	}

	/**
	 * Gets block data for the given blocks as a JSON string.
	 *
	 * @param array $blocks Array of block objects.
	 *
	 * @return string JSON-encoded block data, or empty string on error.
	 */
	public function getBlocksData(array $blocks): string
	{
		$siteData = $this->getSiteData();
		$imagesSet = $siteData->getImagesSet();

		$blocksData = [];
		foreach ($blocks as $block)
		{
			$blockData = [
				'id' => $block->getId(),
				'anchor' => $block->getAnchor($block->getId()),
				'images' => $imagesSet[$block->getId()] ?? [],
			];
			$blocksData[] = $blockData;
		}
		try
		{
			$blockDataEncoded = Json::encode($blocksData);
		}
		catch (ArgumentException $e)
		{
			$blockDataEncoded = '';
		}

		return $blockDataEncoded;
	}

	/**
	 * Sends metrika analytics for an error during generation.
	 *
	 * @param GenerationException $e
	 *
	 * @return void
	 */
	private function sendMetrikaError(GenerationException $e): void
	{
		$errorCode = $e->getErrorCode();
		/**
		 * @var Metrika\Statuses $status
		 */
		$status = match ($errorCode)
		{
			GenerationErrors::requestQuotaExceeded => $e->getParams()['metrikaStatus'] ?? Metrika\Statuses::ErrorB24,
			GenerationErrors::restrictedRequest => Metrika\Statuses::ErrorContentPolicy,
			GenerationErrors::notExistResponse,
			GenerationErrors::notFullyResponse,
			GenerationErrors::notCorrectResponse => Metrika\Statuses::ErrorProvider,
			default => Metrika\Statuses::ErrorB24,
		};

		$step = $this->scenarist->getCurrentStep();
		$analyticEvent = $step?->step->getAnalyticEvent() ?? Metrika\Events::unknown;

		$metrika = $this->getMetrika($analyticEvent);
		$metrika->setStatus($status);
		$metrika->send();
	}
}