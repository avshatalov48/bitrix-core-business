<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Mail\MailboxDirectory;
use Bitrix\Main\Text\Emoji;

class MailboxDirectoryStorage
{
	private $mailboxId = null;
	private $data = [];

	public function __construct($mailboxId)
	{
		$this->mailboxId = $mailboxId;

		$this->init();
	}

	public function init()
	{
		$items = MailboxDirectory::fetchAll($this->mailboxId);

		$this->set($items);
	}

	public function set(array $items)
	{
		$this->group($items);
	}

	public function get(string $key, $default = null)
	{
		return $this->getData($key, $default);
	}

	public function getByHash(string $key)
	{
		$list = $this->get('hashed', []);
		return isset($list[$key]) ? $list[$key] : null;
	}

	public function getByPath(string $key)
	{
		$key = Emoji::decode($key);
		$list = $this->get('all', []);
		return isset($list[$key]) ? $list[$key] : null;
	}

	private function has(string $key)
	{
		return isset($this->data[$key]);
	}

	private function remove(string $key)
	{
		if (isset($this->data[$key]))
		{
			unset($this->data[$key]);
		}
	}

	private function reset()
	{
		$this->data = [];
	}

	private function getData(string $key, $default = null)
	{
		return $this->has($key) ? $this->data[$key] : $default;
	}

	private function setData($name, $value)
	{
		$this->data[$name] = $value;
	}

	private function group($items)
	{
		$all = [];
		$income = [];
		$outcome = [];
		$spam = [];
		$trash = [];
		$draft = [];
		$hashed = [];

		foreach ($items as $item)
		{
			$all[$item->getPath()] = $item;
			$hashed[$item->getDirMd5()] = $item;

			if ($item->isIncome())
			{
				$income[] = $item;
			}

			if ($item->isOutcome())
			{
				$outcome[] = $item;
			}

			if ($item->isSpam())
			{
				$spam[] = $item;
			}

			if ($item->isDraft())
			{
				$draft[] = $item;
			}

			if ($item->isTrash())
			{
				$trash[] = $item;
			}
		}

		$this->reset();
		$this->setData('all', $all);
		$this->setData('income', $income);
		$this->setData('outcome', $outcome);
		$this->setData('spam', $spam);
		$this->setData('trash', $trash);
		$this->setData('draft', $draft);
		$this->setData('hashed', $hashed);
	}
}
