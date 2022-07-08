<?php

namespace Bitrix\Mail\Internals\Entity;

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Mail\MailboxDirectory as MailboxDirectoryManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Text\Emoji;
use JsonSerializable;

class MailboxDirectory extends \Bitrix\Mail\Internals\EO_MailboxDirectory implements JsonSerializable
{
	private $children = [];

	public function isSync()
	{
		if ((int)$this->getIsSync() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isDisabled()
	{
		if ((int)$this->getIsDisabled() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isHiddenSystemFolder()
	{
		if($this->isDisabled())
		{
			if(in_array($this->getPath(),[
				'[Gmail]',
			]))
			{
				return true;
			}
		}

		return false;
	}

	public function isSpam()
	{
		if ((int)$this->getIsSpam() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isTrash()
	{
		if ((int)$this->getIsTrash() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isDraft()
	{
		if ((int)$this->getIsDraft() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isOutcome()
	{
		if ((int)$this->getIsOutcome() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function isInvisibleToCounters()
	{
		if($this->isTrash() || $this->isSpam() || $this->isDraft() || $this->isOutcome())
		{
			return true;
		}

		return false;
	}

	public function isIncome()
	{
		if ((int)$this->getIsIncome() === (int)MailboxDirectoryTable::ACTIVE)
		{
			return true;
		}

		return false;
	}

	public function hasChildren()
	{
		return !empty($this->children);
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function addChild($dir)
	{
		$this->children[] = $dir;

		return $this;
	}

	public function getCountChildren()
	{
		$count = 0;

		foreach ($this->children as $child)
		{
			$count++;

			if ($child->hasChildren())
			{
				$count += $child->getCountChildren();
			}
		}

		return $count;
	}

	public function getCountSyncChildren()
	{
		$count = 0;

		foreach ($this->children as $child)
		{
			if ($child->isSync())
			{
				$count++;
			}

			$count += $child->getCountSyncChildren();
		}

		return $count;
	}

	public function getFormattedName()
	{
		if ($this->getLevel() === 1)
		{
			return $this->getName();
		}

		$path = explode($this->getDelimiter(), $this->getPath());

		return join(' / ', $path);
	}

	public function getPath($emojiEncode = false)
	{
		if(!$emojiEncode)
		{
			return parent::getPath();
		}
		return Emoji::encode(parent::getPath());
	}

	public function getName()
	{
		$name = $this->sysGetValue('NAME');
		$level = $this->getLevel();

		if (mb_strtolower($name) == 'inbox' && $level === 1)
		{
			return Loc::getMessage('MAIL_CLIENT_INBOX_ALIAS');
		}

		return $name;
	}

	public function isSyncLock()
	{
		if ($this->getSyncLock() > time() - Mailbox::getTimeout())
		{
			return true;
		}

		return false;
	}

	public function startSyncLock()
	{
		$this->setSyncLock(time());

		if (MailboxDirectoryManager::setSyncLock($this->getId(), $this->getSyncLock()) > 0)
		{
			return true;
		}

		return false;
	}

	public function stopSyncLock()
	{
		$this->unsetSyncLock();
		$this->setSyncLock(null);

		$this->save();
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize()
	{
		return [
			'ID'             => $this->getId(),
			'MAILBOX_ID'     => $this->getMailboxId(),
			'NAME'           => $this->getName(),
			'FORMATTED_NAME' => $this->getFormattedName(),
			'PATH'           => $this->getPath(),
			'FLAGS'          => $this->getFlags(),
			'DELIMITER'      => $this->getDelimiter(),
			'DIR_MD5'        => $this->getDirMd5(),
			'LEVEL'          => $this->getLevel(),
			'IS_DISABLED'    => $this->isDisabled(),
			'CHILDREN'       => Json::decode(Json::encode($this->getChildren()))
		];
	}
}
