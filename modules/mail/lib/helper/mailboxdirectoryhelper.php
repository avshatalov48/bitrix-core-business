<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Internals\MailboxDirectoryStorage;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Mail\MailboxDirectory;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Text\Emoji;

class MailboxDirectoryHelper
{
	private $mailboxId = null;
	private $storage = null;
	/** @var  ErrorCollection */
	private $errors = [];

	public function __construct($mailboxId)
	{
		$this->mailboxId = $mailboxId;
		$this->storage = new MailboxDirectoryStorage($mailboxId);
		$this->errors = new ErrorCollection();
	}

	public function getDirs()
	{
		return $this->storage->get('all', []);
	}

	public function setDirs(array $dirs)
	{
		$this->storage->set($dirs);
	}

	public function reloadDirs()
	{
		$this->storage->init();
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getDrafts()
	{
		$list = $this->storage->get('draft', []);

		return reset($list);
	}

	public function getIncome()
	{
		$list = $this->storage->get('income', []);

		return reset($list);
	}

	public function getOutcome()
	{
		$list = $this->storage->get('outcome', []);

		return reset($list);
	}

	public function getSpam()
	{
		$list = $this->storage->get('spam', []);

		return reset($list);
	}

	public function getTrash()
	{
		$list = $this->storage->get('trash', []);

		return reset($list);
	}

	public function getIncomePath($emojiEncode = false)
	{
		$dir = $this->getIncome();

		if ($dir != null)
		{
			return $dir->getPath($emojiEncode);
		}

		return null;
	}

	public function getOutcomePath($emojiEncode = false)
	{
		$dir = $this->getOutcome();

		if ($dir != null)
		{
			return $dir->getPath($emojiEncode);
		}

		return null;
	}

	public function getDraftsPath($emojiEncode = false)
	{
		$dir = $this->getDrafts();

		if ($dir != null)
		{
			return $dir->getPath($emojiEncode);
		}

		return null;
	}

	public function getSpamPath($emojiEncode = false)
	{
		$dir = $this->getSpam();

		if ($dir != null)
		{
			return $dir->getPath($emojiEncode);
		}

		return null;
	}

	public function getTrashPath($emojiEncode = false)
	{
		$dir = $this->getTrash();

		if ($dir != null)
		{
			return $dir->getPath($emojiEncode);
		}

		return null;
	}

	public function getDirPathByHash($hash)
	{
		$dir = $this->storage->getByHash($hash);

		if ($dir != null)
		{
			return $dir->getPath();
		}

		return null;
	}

	public function getDirByHash($hash)
	{
		$dir = $this->storage->getByHash($hash);

		if ($dir != null)
		{
			return $dir;
		}

		return null;
	}

	public function getDirPathByType($type)
	{
		switch ($type)
		{
			case MailboxDirectoryTable::TYPE_INCOME:
				return $this->getIncomePath();
				break;
			case MailboxDirectoryTable::TYPE_OUTCOME:
				return $this->getOutcomePath();
				break;
			case MailboxDirectoryTable::TYPE_SPAM:
				return $this->getSpamPath();
				break;
			case MailboxDirectoryTable::TYPE_TRASH:
				return $this->getTrashPath();
				break;
			default:
				return '';
		}
	}

	public function getDirByPath(?string $path)
	{
		if (!$path)
		{
			return null;
		}

		$dir = $this->storage->getByPath($path);

		if ($dir != null)
		{
			return $dir;
		}

		return null;
	}

	public function getSyncDirs()
	{
		return array_filter($this->getDirs(), function ($item)
		{
			return $item->isSync();
		});
	}

	public function getSyncDirsOrderByTime($excludeDirPath = null)
	{
		return array_filter($this->orderByTime($this->getSyncDirs()), function ($item) use ($excludeDirPath)
		{
			return $item->getPath() !== $excludeDirPath;
		});
	}

	public function getSyncDirsPath($emojiEncode = false)
	{
		$list = [];

		foreach ($this->getDirs() as $item)
		{
			if ($item->isSync())
			{
				$list[] = $item->getPath($emojiEncode);
			}
		}

		return $list;
	}

	public function getAllOneLevel()
	{
		$list = [];

		foreach ($this->getDirs() as $item)
		{
			if ((int)$item->getLevel() === 1)
			{
				$list[$item->getPath()] = $item;
			}
		}

		return $list;
	}

	private function orderByDefault($dirs)
	{
		usort($dirs, function ($a, $b)
		{
			$aSort = $this->getOrderByDefault($a);
			$bSort = $this->getOrderByDefault($b);

			if ($aSort === $bSort)
			{
				return 0;
			}

			return $aSort > $bSort ? 1 : -1;
		});

		return $dirs;
	}

	public function getOrderByDefault($dir)
	{
		return $dir->isIncome() ? 10 : ($dir->isOutcome() ? 2000 : ($dir->isTrash() ? 3000 : ($dir->isSpam() ? 4000 : ($dir->isDraft() ? 5000 : $dir->getLevel() * 100))));
	}

	private function orderByName($dirs)
	{
		usort($dirs, function ($a, $b)
		{
			$aSort = $a->getName();
			$bSort = $b->getName();

			if (
				$a->isSpam() ||
				$a->isTrash() ||
				$a->isDraft() ||
				$a->isOutcome()
			)
			{
				$aSort = 1000;
			}

			if (
				$b->isSpam() ||
				$b->isTrash() ||
				$b->isDraft() ||
				$b->isOutcome()
			)
			{
				$bSort = 1000;
			}

			if ($aSort === $bSort)
			{
				return 0;
			}

			return $aSort > $bSort ? 1 : -1;
		});

		return $dirs;
	}

	private function orderByTime($dirs)
	{
		usort($dirs, function ($a, $b)
		{
			$aSort = $a->getSyncTime() ?: ($a->isIncome() ? 100 : ($a->isOutcome() ? 200 : ($a->isTrash() ? 300 : ($a->isSpam() ? 400 : $a->getLevel() * 1000))));
			$bSort = $b->getSyncTime() ?: ($b->isIncome() ? 100 : ($b->isOutcome() ? 200 : ($b->isTrash() ? 300 : ($b->isSpam() ? 400 : $b->getLevel() * 1000))));

			if ($aSort === $bSort)
			{
				return 0;
			}

			return $aSort > $bSort ? 1 : -1;
		});

		return $dirs;
	}

	public function getLastSyncDirByDefault($excludeDirPath = null)
	{
		$list = $this->orderByDefault($this->getSyncDirs());
		$list = array_filter($list, function ($item) use ($excludeDirPath)
		{
			return $item->getPath() !== $excludeDirPath;
		});

		return end($list);
	}

	public function getCurrentSyncDirByTime()
	{
		$list = $this->orderByTime($this->getSyncDirs());

		return reset($list);
	}

	public function getCurrentSyncDirPositionByDefault(string $path, $excludeDirPath = null)
	{
		$list = $this->orderByDefault($this->getSyncDirs());
		$list = array_filter($list, function ($item) use ($excludeDirPath)
		{
			return $item->getPath() !== $excludeDirPath;
		});

		$getIndex = function ($list, $path)
		{
			foreach ($list as $index => $item)
			{
				if ($item->getPath() === $path)
				{
					return $index;
				}
			}

			return -1;
		};

		return $getIndex($list, $path);
	}

	public function removeDirsLikePath(array $dirs)
	{
		$removeRows = [];

		foreach ($dirs as $item)
		{
			$removeRows[] = [
				'=PATH' => $item->getPath(true),
			];
			//deleting subfolders
			$removeRows[] = [
				'%=PATH' => $item->getPath(true) . $item->getDelimiter() . '%',
			];
		}

		if (!empty($removeRows))
		{
			$removeRows = array_merge(['LOGIC' => 'OR'], $removeRows);

			$filter = array_merge([
				'LOGIC'       => 'AND',
				'=MAILBOX_ID' => $this->mailboxId,
			], [$removeRows]);

			MailboxDirectory::deleteList($filter);
		}
	}

	public function getDefaultDir()
	{
		$inboxDir = $this->getIncome();
		$sendDir = $this->getOutcome();
		$dirs = $this->getDirs();

		foreach ([$inboxDir, $sendDir] as $dir)
		{
			if ($dir != null && !$dir->isDisabled() && $dir->isSync())
			{
				return $dir;
			}
		}

		foreach ($dirs as $dir)
		{
			if (!$dir->isDisabled() && $dir->isSync())
			{
				return $dir;
			}
		}

		return '';
	}

	public function getDefaultDirPath($emojiEncode = false)
	{
		$dir = $this->getDefaultDir();

		if($dir !== '')
		{
			return $dir->getPath($emojiEncode);
		}

		return '';
	}

	public function buildTreeDirs()
	{
		$list = [];
		$result = [];
		$dirs = $this->getDirs();

		foreach ($dirs as $dir)
		{
			$list[$dir->getId()] = $dir;
		}

		foreach ($list as $id => $dir)
		{
			if (!empty($list[$dir->getParentId()]))
			{
				$list[$dir->getParentId()]->addChild($dir);
			}
			else
			{
				$result[$dir->getId()] = $dir;
			}
		}

		return $this->orderByDefault($result);
	}

	public function syncChildren($parent)
	{
		$pattern = sprintf('%s%s%%', $parent->getPath(), $parent->getDelimiter());
		$mailboxHelper = Mailbox::createInstance($this->mailboxId);
		$dirs = $mailboxHelper->listDirs($pattern);

		if ($dirs === false)
		{
			$this->errors = $mailboxHelper->getErrors();

			return false;
		}

		$dbDirs = $this->getOneLevelByParentId($parent);

		$params = [
			'level'     => $parent->getLevel() + 1,
			'parent_id' => $parent->getId(),
			'root_id'   => $parent->getRootId() ?: $parent->getId(),
			'is_sync'   => MailboxDirectoryTable::INACTIVE,
		];

		$dirs = array_map(function ($item) use ($params)
		{
			return array_merge($item, $params);
		}, $dirs);

		$this->addSyncDirs($dirs, $dbDirs);

		if (!empty($dbDirs))
		{
			$this->updateSyncDirs($dirs, $dbDirs);
			$this->removeSyncDirs($dirs, $dbDirs);
		}

		return true;
	}

	public function getOneLevelByParentId($parent)
	{
		return MailboxDirectory::fetchOneLevelByParentId(
			$this->mailboxId,
			$parent->getId(),
			$parent->getLevel() + 1
		);
	}

	public function getAllLevelByParentId($parent)
	{
		return MailboxDirectory::fetchAllLevelByParentId(
			$this->mailboxId,
			$parent->getPath(true) . $parent->getDelimiter() . '%',
			$parent->getLevel() + 1
		);
	}

	public function addSyncDirs($dirs, $dbDirs)
	{
		$diffDirs = array_diff_key($dirs, $dbDirs);

		$addRows = array_map(
			function ($dir)
			{
				if (!isset($dir['is_sync']))
				{
					$dir['is_sync'] = !preg_grep('/^ \x5c ( Drafts | Trash | Junk | Spam ) $/ix', $dir['flags']);
				}

				return [
					'MAILBOX_ID'  => $this->mailboxId,
					'NAME'        => Emoji::encode($dir['name']),
					'PATH'        => Emoji::encode($dir['path']),
					'LEVEL'       => isset($dir['level']) ? $dir['level'] : 1,
					'PARENT_ID'   => isset($dir['parent_id']) ? $dir['parent_id'] : null,
					'ROOT_ID'     => isset($dir['root_id']) ? $dir['root_id'] : null,
					'FLAGS'       => MailboxDirectoryHelper::getFlags($dir['flags']),
					'DELIMITER'   => $dir['delim'],
					'DIR_MD5'     => md5(Emoji::encode($dir['path'])),
					'IS_SYNC'     => $dir['is_sync'],
					'IS_INCOME'   => mb_strtoupper($dir['name']) === 'INBOX',
					'IS_OUTCOME'  => preg_grep('/^ \x5c Sent $/ix', $dir['flags']),
					'IS_DRAFT'    => preg_grep('/^ \x5c Drafts $/ix', $dir['flags']),
					'IS_TRASH'    => preg_grep('/^ \x5c Trash $/ix', $dir['flags']),
					'IS_SPAM'     => preg_grep('/^ \x5c ( Junk | Spam ) $/ix', $dir['flags']),
					'IS_DISABLED' => preg_grep('/^ \x5c Noselect $/ix', $dir['flags']),
				];
			},
			$diffDirs
		);

		if (!empty($addRows))
		{
			MailboxDirectory::addMulti($addRows, true);
		}
	}

	public function updateSyncDirs($dirs, $dbDirs)
	{
		$updateRows = array_udiff_assoc($dirs, $dbDirs, function ($a, $b)
		{
			$flagsA = MailboxDirectoryHelper::getFlags($a['flags']);
			$flagsB = $b->getFlags();

			$delimA = $a['delim'];
			$delimB = $b->getDelimiter();

			if ($flagsA !== $flagsB)
			{
				return $flagsA > $flagsB ? 1 : -1;
			}
			else if ($delimA !== $delimB)
			{
				return $delimA > $delimB ? 1 : -1;
			}

			return 0;
		});

		foreach ($updateRows as $row)
		{
			$dbDir = $this->getDirByPath(Emoji::encode($row['path']));

			if (!$dbDir)
			{
				continue;
			}

			MailboxDirectory::update(
				$dbDir->getId(),
				[
					'DELIMITER' => $row['delim'],
					'FLAGS' => MailboxDirectoryHelper::getFlags($row['flags']),
				]
			);
		}
	}

	public function removeSyncDirs($dirs, $dbDirs)
	{
		$diffDirs = array_diff_key($dbDirs, $dirs);

		if (!empty($diffDirs))
		{
			$this->removeDirsLikePath($diffDirs);
		}
	}

	public function toggleSyncDirs($dirs)
	{
		$enableRows = [];
		$disableRows = [];

		foreach ($dirs as $dir)
		{
			$hash = isset($dir['dirMd5']) ? $dir['dirMd5'] : null;
			$value = isset($dir['value']) ? intval($dir['value']) : 0;

			if (!$hash || !in_array($value, [MailboxDirectoryTable::ACTIVE, MailboxDirectoryTable::INACTIVE]))
			{
				continue;
			}

			if ($value === MailboxDirectoryTable::ACTIVE)
			{
				$enableRows[] = $hash;
			}
			else
			{
				$disableRows[] = $hash;
			}
		}

		if (!empty($enableRows))
		{
			MailboxDirectory::updateSyncDirs($enableRows, MailboxDirectoryTable::ACTIVE, $this->mailboxId);
		}

		if (!empty($disableRows))
		{
			MailboxDirectory::updateSyncDirs($disableRows, MailboxDirectoryTable::INACTIVE, $this->mailboxId);
		}

		$mailboxHelper = Mailbox::createInstance($this->mailboxId);
		$mailboxHelper->activateSync();
	}

	public function saveDirsTypes($dirs)
	{
		foreach ($dirs as $dir)
		{
			$type = !empty($dir['type']) ? $dir['type'] : null;
			$hash = !empty($dir['dirMd5']) ? $dir['dirMd5'] : null;

			if (!MailboxDirectoryHelper::isDirsTypes($type) || !$hash)
			{
				continue;
			}

			$result = MailboxDirectory::fetchOneByMailboxIdAndHash($this->mailboxId, $hash);

			if ($result != null)
			{
				MailboxDirectory::resetDirsTypes($this->mailboxId, $type);

				MailboxDirectory::update(
					$result->getId(),
					[
						$type => MailboxDirectoryTable::ACTIVE
					]
				);
			}
		}
	}

	public function syncDbDirs($dirs)
	{
		$dbDirs = $this->getAllOneLevel();

		$this->addSyncDirs($dirs, $dbDirs);
		$this->updateSyncDirs($dirs, $dbDirs);
		$this->removeSyncDirs($dirs, $dbDirs);
	}

	public function updateMessageCount($id, $count)
	{
		MailboxDirectory::updateMessageCount($id, $count);
	}

	public static function isDirsTypes($name)
	{
		if (in_array(
			$name,
			[
				MailboxDirectoryTable::TYPE_OUTCOME,
				MailboxDirectoryTable::TYPE_TRASH,
				MailboxDirectoryTable::TYPE_SPAM
			],
			true
		))
		{
			return true;
		}

		return false;
	}

	public static function getFlags(array $flags)
	{
		sort($flags);
		return implode(' ', $flags);
	}

	public static function getMaxLevelDirs()
	{
		return (int)\Bitrix\Main\Config\Option::get('mail', 'maxLevelDirs', 20);
	}

	public static function setMaxLevelDirs(int $val)
	{
		\Bitrix\Main\Config\Option::set('mail', 'maxLevelDirs', $val);
	}

	public static function getCurrentSyncDir()
	{
		return \Bitrix\Main\Config\Option::get('mail', 'currentSyncDir', '');
	}

	public static function setCurrentSyncDir(string $path)
	{
		\Bitrix\Main\Config\Option::set('mail', 'currentSyncDir', $path);
	}
}
