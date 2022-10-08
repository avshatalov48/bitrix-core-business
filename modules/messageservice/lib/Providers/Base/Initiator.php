<?php

namespace Bitrix\MessageService\Providers\Base;

abstract class Initiator implements \Bitrix\MessageService\Providers\Initiator
{
	public function getDefaultFrom(): ?string
	{
		$fromList = $this->getFromList();
		$from = isset($fromList[0]) ? $fromList[0]['id'] : null;
		//Try to find alphanumeric from
		foreach ($fromList as $item)
		{
			if (!preg_match('#^[0-9]+$#', $item['id']))
			{
				$from = $item['id'];
				break;
			}
		}
		return $from;
	}

	public function getFirstFromList()
	{
		$fromList = $this->getFromList();
		if (!is_array($fromList))
		{
			return null;
		}

		foreach ($fromList as $item)
		{
			if (isset($item['id']) && $item['id'])
			{
				return $item['id'];
			}
		}

		return null;
	}

	public function isCorrectFrom($from): bool
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ($from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}
}