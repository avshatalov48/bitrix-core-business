<?php

declare(strict_types=1);

namespace Bitrix\Main\Analytics;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

final class AnalyticsEvent
{
	public const STATUS_SUCCESS = 'success';
	public const STATUS_ERROR = 'error';
	public const STATUS_ATTEMPT = 'attempt';
	public const STATUS_CANCEL = 'cancel';

	private ?string $section;
	private ?string $subSection;
	private ?string $element;
	private ?string $type;
	private ?string $p1;
	private ?string $p2;
	private ?string $p3;
	private ?string $p4;
	private ?string $p5;
	/** @var string  */
	private string $status = self::STATUS_SUCCESS;
	private ?int $userId;
	private string $userAgent;
	private string $host;
	private string $dbname;

	private bool $isInvalid = false;

	public function __construct(
		private readonly string $event,
		private readonly string $tool,
		private readonly string $category,
	)
	{
		$userAgent = Context::getCurrent()?->getRequest()->getUserAgent();
		if ($userAgent && \is_string($userAgent))
		{
			$this->setUserAgent($userAgent);
		}

		$httpHost = Context::getCurrent()?->getServer()->getHttpHost();
		if ($httpHost && \is_string($httpHost))
		{
			$this->setHost($httpHost);
		}

		$dbname = \defined('BX24_DB_NAME') ? BX24_DB_NAME : null;
		if ($dbname && \is_string($dbname))
		{
			$this->setDbName($dbname);
		}
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function setSection(string $section): self
	{
		$this->section = $section;

		return $this;
	}

	public function setSubSection(string $subSection): self
	{
		$this->subSection = $subSection;

		return $this;
	}

	public function setElement(string $element): self
	{
		$this->element = $element;

		return $this;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @throws ArgumentException
	 */
	public function setP1(string $p1): self
	{
		$this->validatePField($p1);
		$this->p1 = $p1;

		return $this;
	}

	/**
	 * @throws ArgumentException
	 */
	public function setP2(string $p2): self
	{
		$this->validatePField($p2);
		$this->p2 = $p2;

		return $this;
	}

	/**
	 * @throws ArgumentException
	 */
	public function setP3(string $p3): self
	{
		$this->validatePField($p3);
		$this->p3 = $p3;

		return $this;
	}

	/**
	 * @throws ArgumentException
	 */
	public function setP4(string $p4): self
	{
		$this->validatePField($p4);
		$this->p4 = $p4;

		return $this;
	}

	/**
	 * @throws ArgumentException
	 */
	public function setP5(string $p5): self
	{
		$this->validatePField($p5);
		$this->p5 = $p5;

		return $this;
	}

	public function setStatus(string $status): self
	{
		$this->status = $status;

		return $this;
	}

	public function setUserAgent(string $userAgent): self
	{
		$this->userAgent = $userAgent;

		return $this;
	}

	public function setHost(string $host): self
	{
		$this->host = $host;

		return $this;
	}

	public function setDbName(string $dbname): self
	{
		$this->dbname = $dbname;

		return $this;
	}

	public function markAsSuccess(): self
	{
	    return $this->setStatus(self::STATUS_SUCCESS);
	}

	public function markAsCanceled(): self
	{
	    return $this->setStatus(self::STATUS_CANCEL);
	}

	public function markAsError(): self
	{
	    return $this->setStatus(self::STATUS_ERROR);
	}

	public function markAsAttempt(): self
	{
	    return $this->setStatus(self::STATUS_ATTEMPT);
	}

	/**
	 * @throws ArgumentException
	 */
	private function validatePField(string $value): void
	{
		$invalidValue = substr_count($value, '_') > 1;
		if ($invalidValue)
		{
			$this->isInvalid = true;
		}

		if ($invalidValue && $this->isDevMode())
		{
			throw new ArgumentException('Value for p{1-5} field must contain a single underscore.');
		}
	}

	/**
	 * @throws ArgumentException
	 */
	private function validateRequiredFields(): void
	{
		if (empty($this->event) || empty($this->tool) || empty($this->category))
		{
			$this->isInvalid = true;

			if ($this->isDevMode())
			{
				throw new ArgumentException('Event, tool and category fields are required and should be filled.');
			}
		}
	}

	private function isDevMode(): bool
	{
		$exceptionHandling = Configuration::getValue('exception_handling');

		return !empty($exceptionHandling['debug']);
	}

	public function exportToArray(): array
	{
		return [
			'event' => $this->event,
			'tool' => $this->tool,
			'category' => $this->category,
			'section' => $this->section ?? null,
			'subSection' => $this->subSection ?? null,
			'element' => $this->element ?? null,
			'type' => $this->type ?? null,
			'p1' => $this->p1 ?? null,
			'p2' => $this->p2 ?? null,
			'p3' => $this->p3 ?? null,
			'p4' => $this->p4 ?? null,
			'p5' => $this->p5 ?? null,
			'status' => $this->status ?? null,
			'userAgent' => $this->userAgent ?? null,
		];
	}

	/**
	 * @throws ArgumentException
	 */
	public function send(): void
	{
		if (!\defined('ANALYTICS_V2_FILENAME') || !is_writable(ANALYTICS_V2_FILENAME))
		{
			return;
		}

		$this->validateRequiredFields();
		if ($this->isInvalid)
		{
			return;
		}

		if (!isset($this->userId) && !\defined('BX_CHECK_AGENT_START') && ($GLOBALS['USER'] instanceof \CUser))
		{
			$this->userId = (int)$GLOBALS['USER']->getId();
		}

		$data = $this->buildLogData();
		if ($this->isDevMode())
		{
			$this->triggerDebugEvent($data);
		}

		$jsonData = Json::encode($data, JSON_UNESCAPED_UNICODE);

		$fp = @fopen(ANALYTICS_V2_FILENAME, "ab");
		if ($fp && flock($fp, LOCK_EX))
		{
			@fwrite($fp, $jsonData . PHP_EOL);
			@fflush($fp);
			@flock($fp, LOCK_UN);
			@fclose($fp);
		}
	}

	private function triggerDebugEvent(array $data): void
	{
		$event = new Event('main', 'OnAnalyticsEvent', ['analyticsEvent' => $this, 'eventData' => $data]);
		$event->send();
	}

	private function buildLogData(): array
	{
		$data = [
			'date' => date('Y-m-d H:i:s'),
			'host' => $this->host ?? null,
			'dbname' => $this->dbname ?? null,
			'userId' => $this->userId ?? 0,
			'event' => $this->exportToArray(),
		];

		if (Loader::includeModule('bitrix24'))
		{
			$data['license'] = \CBitrix24::getLicenseType();
		}

		return $data;
	}
}