<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Landing\Copilot\Generation;
use Bitrix\Main\Loader;
use Bitrix\Pull;

/**
 * Manage front and backend events
 */
class Event
{
	private Generation $generation;
	private ?int $siteId = null;
	private ?int $landingId = null;

	private string $name = '';
	private ?array $params = null;
	private ?string $error = null;

	/**
	 * @param Generation $generation
	 */
	public function __construct(Generation $generation)
	{
		$this->generation = $generation;
	}

	/**
	 * If event contains site - set ID
	 * @param int|null $siteId
	 * @return $this
	 */
	public function setSiteId(?int $siteId): self
	{
		$this->siteId = $siteId;

		return $this;
	}

	/**
	 * If event contains landing - set ID
	 * @param int|null $landingId
	 * @return $this
	 */
	public function setLandingId(?int $landingId): self
	{
		$this->landingId = $landingId;

		return $this;
	}

	/**
	 * Send front end backend events with assigned params
	 * @param string $eventName - like onEventName
	 * @param array|null $params - optional params for event
	 * @return void
	 */
	public function send(string $eventName, ?array $params = null): void
	{
		$this->name = $eventName;
		$this->params = $params;
		$this->error = null;

		$this->sendBackendEvent();
		$this->sendFrontendEvent();
	}

	/**
	 * Send events with error
	 * @param string $eventName - like onEventName
	 * @param string $error - error text/name
	 * @return void
	 */
	public function sendError(string $eventName, string $error): void
	{
		$this->name = $eventName;
		$this->params = null;
		$this->error = $error;

		$this->sendBackendEvent();
		$this->sendFrontendEvent();
	}

	private function sendBackendEvent(): void
	{
		$event = new \Bitrix\Main\Event(
			'landing',
			$this->name,
			$this->getEventParams(),
		);
		$event->send();
	}

	private function sendFrontendEvent(): void
	{
		if (Loader::includeModule('pull'))
		{
			$command = 'LandingCopilotGeneration:' . $this->name;
			Pull\Event::add(
				$this->generation->getAuthorId(),
				[
					'module_id' => 'landing',
					'command' => $command,
					'params' => $this->getEventParams(),
				]
			);

			Pull\Event::send();
		}
	}

	private function getEventParams(): array
	{
		$params = [
			'generationId' => $this->generation->getId(),
		];

		if ($this->siteId)
		{
			$params['siteId'] = $this->siteId;
		}

		if ($this->landingId)
		{
			$params['landingId'] = $this->landingId;
		}

		if ($this->params)
		{
			$params['params'] = $this->params;
		}

		if ($this->error)
		{
			$params['error'] = $this->error;
		}

		return $params;
	}
}