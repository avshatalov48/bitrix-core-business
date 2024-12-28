<?php

namespace Bitrix\Im\V2\Analytics\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\Engine\Response\Converter;

abstract class Event
{
	protected AnalyticsEvent $event;
	protected Chat $chat;
	protected ?string $type = null;
	protected ?string $section = null;
	protected ?string $subSection = null;
	protected ?string $element = null;
	protected ?string $status = null;
	protected ?string $p1 = null;
	protected ?string $p2 = null;
	protected ?string $p3 = null;
	protected ?string $p4 = null;
	protected ?string $p5 = null;

	public function __construct(string $eventName, Chat $chat)
	{
		$this->chat = $chat;
		$this->event = new AnalyticsEvent($eventName, $this->getTool(), $this->getCategory($eventName));
		$this->setDefaultParams();
	}

	public function getEvent(): AnalyticsEvent
	{
		return $this->event;
	}

	abstract protected function getTool(): string;

	abstract protected function getCategory(string $eventName): string;

	protected function setDefaultParams(): self
	{
		return $this;
	}

	protected function convertUnderscore(string $string): string
	{
		return (new Converter(Converter::TO_CAMEL | Converter::LC_FIRST))->process($string);
	}

	public function send(): void
	{
		if ($this->type !== null)
		{
		$this->event->setType($this->type);
		}
		if ($this->section !== null)
		{
			$this->event->setSection($this->section);
		}
		if ($this->subSection !== null)
		{
			$this->event->setSubSection($this->subSection);
		}
		if ($this->element !== null)
		{
			$this->event->setElement($this->element);
		}
		if ($this->status !== null)
		{
			$this->event->setStatus($this->status);
		}
		if ($this->p1 !== null)
		{
			$this->event->setP1($this->p1);
		}
		if ($this->p2 !== null)
		{
			$this->event->setP2($this->p2);
		}
		if ($this->p3 !== null)
		{
			$this->event->setP3($this->p3);
		}
		if ($this->p4 !== null)
		{
			$this->event->setP4($this->p4);
		}
		if ($this->p5 !== null)
		{
			$this->event->setP5($this->p5);
		}

		$this->event->send();
	}

	public function setEvent(AnalyticsEvent $event): self
	{
		$this->event = $event;
		return $this;
	}

	public function setChat(Chat $chat): self
	{
		$this->chat = $chat;
		return $this;
	}

	public function setType(?string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function setSection(?string $section): self
	{
		$this->section = $section;
		return $this;
	}

	public function setSubSection(?string $subSection): self
	{
		$this->subSection = $subSection;
		return $this;
	}

	public function setElement(?string $element): self
	{
		$this->element = $element;
		return $this;
	}

	public function setStatus(?string $status): self
	{
		$this->status = $status;
		return $this;
	}

	public function setP1(?string $p1): self
	{
		$this->p1 = $p1;
		return $this;
	}

	public function setP2(?string $p2): self
	{
		$this->p2 = $p2;
		return $this;
	}

	public function setP3(?string $p3): self
	{
		$this->p3 = $p3;
		return $this;
	}

	public function setP4(?string $p4): self
	{
		$this->p4 = $p4;
		return $this;
	}

	public function setP5(?string $p5): self
	{
		$this->p5 = $p5;
		return $this;
	}
}
