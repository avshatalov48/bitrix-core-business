<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

class MailAddresser extends MailUser
{
	public function getFullName(): string
	{
		if ($this->name || $this->lastName)
		{
			return $this->name . ' ' . $this->lastName;
		}

		return $this->email;
	}
	public function getFullNameWithEmail(): string
	{
		if ($this->email)
		{
			return "{$this->getFullName()} ({$this->email})";
		}

		return $this->getFullName();
	}
}