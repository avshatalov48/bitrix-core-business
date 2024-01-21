<?php

namespace Bitrix\Seo\Sitemap;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\Sitemap\Internals\JobTable;
use Bitrix\Seo\Sitemap\Internals\SitemapTable;
use Bitrix\Seo\Sitemap\Type\Step;

Loc::loadMessages(__DIR__ . '/../../admin/seo_sitemap.php');

/**
 * Control process of sitemap generation. Multistep, reload, agent etc
 */
class Job
{
	protected const AGENT_FUNCTION = 'doJobAgent';
	protected const AGENT_INTERVAL = 1;
	protected const AGENT_DELAY = 60;

	protected const LOCK_MAX_INTERVAL = 300;

	/**
	 * Progressbar width
	 */
	protected const PROGRESS_WIDTH = 500;

	/**
	 * Statuses of current job
	 */
	public const STATUS_REGISTER = 'R';
	public const STATUS_PROCESS = 'P';
	public const STATUS_FINISH = 'F';
	public const STATUS_ERROR = 'E';

	/**
	 * ID of Job row in table
	 * @return void
	 */
	protected int $id;

	/**
	 * For which sitemap this job
	 * @var int
	 */
	protected int $sitemapId;

	/**
	 * Parameters for init generator
	 */
	protected int $step;
	protected array $state;
	protected string $status;
	protected string $statusMessage = '';

	/**
	 * Job properties
	 */
	protected bool $isLocked = false;
	protected ?DateTime $dateModify;

	/**
	 * Create job for sitemap by table
	 * @throws SystemException
	 */
	protected function __construct(int $sitemapId)
	{
		$this->sitemapId = $sitemapId;

		// init from table
		$job = self::getDataBySitemap($sitemapId);
		if ($job)
		{
			$this->id = (int)$job['ID'];
			$this->status = $job['STATUS'];
			$this->statusMessage = $job['STATUS_MESSAGE'];
			$this->step = (int)$job['STEP'];
			$this->state = $job['STATE'] ?? [];

			$this->isLocked = $job['RUNNING'] === 'Y';
			$this->dateModify = $job['DATE_MODIFY'] ? new DateTime($job['DATE_MODIFY']) : null;

			if (!self::checkSitemapExists($sitemapId))
			{
				$this->finish();

				throw new SystemException('Sitemap for current job is not exists.');
			}
		}
		else
		{
			throw new SystemException('Job for current sitemap is not exists.');
		}
	}

	/**
	 * Try to find sitemap by ID
	 * @param int $sitemapId ID of sitemap
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected static function checkSitemapExists(int $sitemapId): bool
	{
		$sitemap = SitemapTable::query()
			->setSelect(['ID'])
			->where('ID', $sitemapId)
			->exec()
			->fetch()
		;

		return (bool)$sitemap;
	}

	/**
	 * Find job for sitemap, If find - return object, null if not
	 * @param int $sitemapId Id of map.
	 * @return Job|null
	 */
	public static function findJob(int $sitemapId): ?Job
	{
		try
		{
			$job = self::getDataBySitemap($sitemapId);

			return $job ? new self($sitemapId) : null;
		}
		catch (SystemException $e)
		{
			return null;
		}
	}

	/**
	 * Register new job or get existing
	 * @param int $sitemapId Id of map.
	 * @return Job|null
	 * @throws SystemException
	 */
	public static function addJob(int $sitemapId): ?Job
	{
		$exists = self::getDataBySitemap($sitemapId);
		if ($exists)
		{
			return new self($sitemapId);
		}

		$res = JobTable::add(
			[
				'SITEMAP_ID' => $sitemapId,
				'RUNNING' => 'N',
				'STATUS' => Job::STATUS_REGISTER,
				'STATUS_MESSAGE' => '',
				'STEP' => Step::getFirstStep(),
				'STATE' => [],
			]
		);

		if ($res->isSuccess())
		{
			return new self($sitemapId);
		}

		return null;
	}

	/**
	 * Find existing job in DB, return data if exists
	 * @param int $sitemapId
	 * @return array|null
	 */
	protected static function getDataBySitemap(int $sitemapId): ?array
	{
		if ($sitemapId > 0)
		{
			$job = JobTable::query()
				->setSelect(['ID', 'RUNNING', 'STATUS', 'STATUS_MESSAGE', 'STEP', 'STATE', 'DATE_MODIFY'])
				->where('SITEMAP_ID', $sitemapId)
				->exec()
				->fetch()
			;

			return $job ?: null;
		}

		return null;
	}

	/**
	 * Create agent for automatic re-generate sitemap in background.
	 * @param int $sitemapId Id of map.
	 * @return bool
	 */
	public static function markToRegenerate(int $sitemapId): bool
	{
		try
		{
			if ($sitemapId > 0)
			{
				$existsJob = self::findJob($sitemapId);
				if (!$existsJob)
				{
					self::addJob($sitemapId);
				}

				self::deleteAgent($sitemapId);

				return (bool)self::addAgent($sitemapId);
			}
		}
		catch (SystemException $e)
		{
			return false;
		}

		return false;
	}

	public static function clearBySitemap(int $sitemapId)
	{
		$job = self::findJob($sitemapId);
		$job?->finish();
	}

	/**
	 * Check if agent exists for current job
	 * @param int $sitemapId
	 * @return array|null - data of agent, if found
	 */
	protected static function findAgent(int $sitemapId): ?array
	{
		$funcName = self::getAgentName($sitemapId);
		$res = \CAgent::getList(
			[],
			[
				'MODULE_ID' => 'seo',
				'NAME' => $funcName,
			]
		);
		$exists = $res->Fetch();

		return $exists ?: null;
	}

	/**
	 * Add agent for regenerate sitemap
	 * @param int $sitemapId
	 * @return int - id of new agent
	 */
	protected static function addAgent(int $sitemapId): int
	{
		$funcName = self::getAgentName($sitemapId);
		$nextExec = \ConvertTimeStamp(time() + \CTimeZone::GetOffset() + self::AGENT_DELAY, "FULL");

		return \CAgent::addAgent(
			$funcName,
			'seo',
			'N',
			self::AGENT_INTERVAL,
			'',
			'Y',
			$nextExec
		);
	}

	/**
	 * Find existing agent for sitemap, delete if find
	 * @param int $sitemapId
	 * @return bool
	 */
	protected static function deleteAgent(int $sitemapId): bool
	{
		$agent = self::findAgent($sitemapId);
		if ($agent && $agent['RUNNING'] === 'N')
		{
			return \CAgent::Delete($agent['ID']);
		}

		return true;
	}

	protected static function getAgentName(int $sitemapId): string
	{
		return __CLASS__ . '::' . self::AGENT_FUNCTION . '(' . $sitemapId . ');';
	}

	/**
	 * Method for run agent
	 * @param int $sitemapId Id of map.
	 * @return string
	 */
	public static function doJobAgent(int $sitemapId): string
	{
		$job = self::findJob($sitemapId);

		if ($job)
		{
			$result = $job->doStep();
			if ($result->isSuccess())
			{
				return self::getAgentName($sitemapId);
			}
		}

		return '';
	}

	/**
	 * Run one step of generation
	 * @return Result
	 */
	public function doStep(): Result
	{
		$result = new Result();

		// skip if job running now
		if (
			$this->checkLock()
			|| !$this->lock()
		)
		{
			return $result;
		}

		$generator =
			(new Generator($this->sitemapId))
				->setStep($this->step)
				->setState($this->state)
		;
		if ($generator->run())
		{
			$this->state = $generator->getState();
			$this->statusMessage = $generator->getStatusMessage();
			$this->step = $generator->getStep();

			if ($this->step <= Step::STEPS[Step::STEP_INIT])
			{
				$this->status = self::STATUS_REGISTER;
			}
			elseif ($this->step >= Step::STEPS[Step::STEP_INDEX])
			{
				$this->status = self::STATUS_FINISH;
				$this->finish();
			}
			else
			{
				$this->status = self::STATUS_PROCESS;
			}

			$this->save();
		}
		$this->unlock();

		return $result;
	}

	protected function lock(): bool
	{
		$res = JobTable::update(
			$this->id,
			[
				'RUNNING' => 'Y',
			]
		);
		if ($res->isSuccess())
		{
			$this->isLocked = true;

			return true;
		}

		return false;
	}

	protected function unlock(): bool
	{
		$res = JobTable::update(
			$this->id,
			[
				'RUNNING' => 'N',
			]
		);

		if ($res->isSuccess())
		{
			$this->isLocked = false;

			return true;
		}

		return false;
	}

	protected function checkLock(): bool
	{
		if ($this->isLocked)
		{
			if ($this->dateModify)
			{
				$secondsDiff = (new DateTime())->getDiff($this->dateModify)->s;
				if ($secondsDiff > self::LOCK_MAX_INTERVAL)
				{
					return !$this->unlock();
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Save current params in table
	 * @return bool
	 */
	protected function save(): bool
	{
		$res = JobTable::update(
			$this->id,
			[
				'STATUS' => $this->status,
				'STATUS_MESSAGE' => $this->statusMessage,
				'STATE' => $this->state,
				'STEP' => $this->step,
			]
		);

		return $res->isSuccess();
	}

	/**
	 * When job is done - do finish operations
	 * @return bool
	 */
	protected function finish(): bool
	{
		self::deleteAgent($this->sitemapId);

		$res = JobTable::delete(
			$this->id
		);

		return $res->isSuccess();
	}

	/**
	 * Get data od generation process
	 * @return array
	 */
	public function getData(): array
	{
		return [
			'status' => $this->status,
			'statusMessage' => $this->statusMessage,
			'formattedStatusMessage' => $this->getFormattedStatusMessage(),
			'step' => $this->step,
		];
	}

	protected function getFormattedStatusMessage(): string
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');

		$title = Loc::getMessage('SEO_SITEMAP_RUN_TITLE') . " (ID {$this->sitemapId})";
		if ($this->step < Step::getLastStep())
		{
			$msg = new \CAdminMessage([
				"TYPE" => "PROGRESS",
				"HTML" => true,
				"MESSAGE" => $title,
				"DETAILS" => "#PROGRESS_BAR#<div style=\"width: "
					. self::PROGRESS_WIDTH
					. "px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-top: 20px;\">"
					. Converter::getHtmlConverter()->encode($this->statusMessage)
					. "</div>",
				"PROGRESS_TOTAL" => 100,
				"PROGRESS_VALUE" => $this->step,
				"PROGRESS_TEMPLATE" => '#PROGRESS_PERCENT#',
				"PROGRESS_WIDTH" => self::PROGRESS_WIDTH,
			]);
		}
		else
		{
			$msg = new \CAdminMessage([
				"TYPE" => "OK",
				"MESSAGE" => $title,
				"DETAILS" => $this->statusMessage,
			]);
		}

		return $msg->show();
	}
}
