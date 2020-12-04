<?php


namespace Bitrix\Calendar\ICal\Parser;


class ParserManager
{
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var array
	 */
	private $timezones;
	/**
	 * @var array
	 */
	private $events = [];
	/**
	 * @var array
	 */
	private $components = [];
	/**
	 * @var string
	 */
	private $method = '';

	public static function getInstance(string $data): ParserManager
	{
		return new self($data);
	}

	public function __construct(string $data = '')
	{
		$this->data = $data;
	}

	public function parseData(): ParserManager
	{
		try
		{
			$parser = Parser::getInstance($this->data);
			$this->components = $parser->handleData()->getComponents();
		}
		catch(\Exception $e)
		{
		}

		return $this;
	}

	public function handleComponents(): ParserManager
	{
		$components = $this->components;

		foreach ($components as $component)
		{
			if ($component instanceof Calendar)
			{
				$this->events = $component->handleEvents()->getEvents();
				$this->method = $component->getMethod();
			}
			elseif ($component instanceof Event)
			{
				$this->events[] = $component->getContent();
			}
		}

		return $this;
	}

	public function getComponents(): array
	{
		return $this->components;
	}

	public function getEvents()
	{
		return $this->events;
	}

	public function getProcessedEvents()
	{
		return $this->parseData()
			->handleComponents()
			->getEvents();
	}

	public function getMethod()
	{
		return $this->method;
	}
}