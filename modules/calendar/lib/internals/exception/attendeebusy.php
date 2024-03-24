<?php

namespace Bitrix\Calendar\Internals\Exception;

class AttendeeBusy extends \Exception
{
	private string $attendeeName = '';
	private array $busyUsersList = [];

	public function getAttendeeName(): string
	{
		return $this->attendeeName;
	}

	public function getBusyUsersList(): array
	{
		return $this->busyUsersList;
	}

	public function setAttendeeName(string $attendeeName): self
	{
		$this->attendeeName = $attendeeName;
		return $this;
	}

	public function setBusyUsersList(array $list): self
	{
		$this->busyUsersList = $list;
		return $this;
	}
}
