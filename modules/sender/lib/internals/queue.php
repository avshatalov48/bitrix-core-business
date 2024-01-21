<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;

use Bitrix\Sender\Internals\Model\QueueTable;

Loc::loadMessages(__FILE__);

/**
 * Class Queue
 *
 * @package Bitrix\Sender\Internals
 */
class Queue
{
	protected $list = [];
	protected $lastItem = null;
	protected $previousStack = [];
	protected $isLastItemRestored = false;
	protected $type = null;
	protected $id = null;

	protected $isWorkTimeCheckEnabled = false;
	protected $isUserCheckEnabled = false;
	protected $isAutoSaveEnabled = true;

	/**
	 * Queue constructor.
	 *
	 * @param string $type Type.
	 * @param string $id ID.
	 * @param array $list List.
	 */
	public function __construct($type, $id, array $list = [])
	{
		$this->type = $type;
		$this->setId($id);
		$this->setValues($list);
	}

	/**
	 * Save last item automatically.
	 *
	 * @return $this
	 */
	public function disableAutoSave()
	{
		$this->isAutoSaveEnabled = false;
		return $this;
	}

	/**
	 * Enable work time checking.
	 *
	 * @return $this
	 */
	public function enableWorkTimeCheck()
	{
		$this->isWorkTimeCheckEnabled = true;
		return $this;
	}

	/**
	 * Return true if user checking enabled.
	 *
	 * @return $this
	 */
	public function enableUserCheck()
	{
		$this->isUserCheckEnabled = true;
		return $this;
	}

	/**
	 * Return true if work time checking enabled.
	 *
	 * @return bool
	 */
	public function isWorkTimeCheckEnabled()
	{
		return $this->isWorkTimeCheckEnabled;
	}

	/**
	 * Get ID.
	 *
	 * @return null|string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param null|string $id
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Set list.
	 *
	 * @param array $list List.
	 * @return $this
	 */
	public function setValues(array $list)
	{
		$this->list = $list;
		$this->previousStack = [];
		return $this;
	}

	/**
	 * Get list.
	 *
	 * @return array
	 */
	public function getValues()
	{
		return $this->list;
	}

	/**
	 * Remove data from DB by type and ID.
	 *
	 * @return $this
	 */
	public function delete()
	{
		QueueTable::delete(['ENTITY_TYPE' => $this->type, 'ENTITY_ID' => $this->id]);
		return $this;
	}

	/**
	 * Return true if wirk time is supported.
	 *
	 * @return bool
	 */
	public static function isSupportedWorkTime()
	{
		return ModuleManager::isModuleInstalled('timeman');
	}

	/**
	 * Get last used item from list.
	 *
	 * @return null|string
	 */
	public function current()
	{
		if (!$this->isLastItemRestored)
		{
			$this->restore();
			$this->isLastItemRestored = true;
		}

		return $this->lastItem;
	}

	/**
	 * Save last item to DB.
	 *
	 * @return $this
	 */
	public function save()
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$item = $this->current();

		if ($item)
		{
			$insert = [
				'ENTITY_TYPE' => $this->type,
				'ENTITY_ID' => $this->id,
				'LAST_ITEM' => $item,
			];
			$update = [
				'LAST_ITEM' => $item,
			];
			$tableName = QueueTable::getTableName();
			$queries = $sqlHelper->prepareMerge($tableName, QueueTable::getConflictFields(), $insert, $update);
			Application::getConnection()->query($queries[0]);
		}
		else
		{
			$this->delete();
		}

		return $this;
	}

	/**
	 * Restore last item from DB.
	 *
	 * @return $this
	 */
	public function restore()
	{
		$row = QueueTable::getRow([
			'select' => ['LAST_ITEM'],
			'filter' => ['=ENTITY_TYPE' => $this->type, '=ENTITY_ID' => $this->id]
		]);
		$this->setLastItem($row ? $row['LAST_ITEM'] : null);

		return $this;
	}

	/**
	 * Return next item from list.
	 * Save item to DB if $isAutoSaveEnabled is true.
	 * Check item as User if $isUserCheckEnabled is true.
	 * Check item for work time if $isWorkTimeCheckEnabled is true.
	 *
	 * @return string|null
	 */
	public function next()
	{
		if (count($this->list) == 0)
		{
			return null;
		}

		$nextItem = null;
		$reservedItem = null;
		$list = $this->getStack();
		foreach ($list as $item)
		{
			if ($this->isUserCheckEnabled && !$this->checkUser($item))
			{
				continue;
			}

			if ($this->isWorkTimeCheckEnabled && !$this->checkUserWorkTime($item))
			{
				if (!$reservedItem)
				{
					$reservedItem = $item;
				}

				continue;
			}

			$nextItem = $item;
			break;
		}

		if (!$nextItem)
		{
			$nextItem = $reservedItem ? $reservedItem : $list[0];
		}

		$this->setLastItem($nextItem);

		if ($this->isAutoSaveEnabled)
		{
			$this->save();
		}

		return $nextItem;
	}

	/**
	 * Return previous used item.
	 * Stack of previous items is limited by 3 values.
	 *
	 * @return string|null
	 */
	public function previous()
	{
		if (count($this->previousStack) === 0)
		{
			$this->isLastItemRestored = false;
			$this->lastItem = null;
		}
		else
		{
			$this->lastItem = array_pop($this->previousStack);
		}

		return $this->lastItem;
	}

	protected function setLastItem($item)
	{
		if ($this->lastItem)
		{
			if (count($this->previousStack) >= 3)
			{
				array_shift($this->previousStack);
			}
			array_push($this->previousStack, $this->lastItem);
		}
		$this->lastItem = $item;

		return $this;
	}

	protected function getStack()
	{
		if (!$this->current() || !in_array($this->current(), $this->list))
		{
			return $this->list;
		}

		$lastPosition = array_search($this->current(), $this->list);
		$lastPosition++;
		if ($lastPosition >= count($this->list))
		{
			$lastPosition = 0;
		}
		$list = array_slice($this->list, $lastPosition);
		if ($lastPosition > 0)
		{
			$list = array_merge(
				$list,
				array_slice($this->list, 0, $lastPosition)
			);
		}

		return $list;
	}

	protected static function checkUser($userId)
	{
		if (!is_numeric($userId))
		{
			return false;
		}

		$row = UserTable::getRowById($userId);
		return is_array($row);
	}

	protected static function checkUserWorkTime($userId)
	{
		if (!self::isSupportedWorkTime())
		{
			return true;
		}

		if (!Loader::includeModule('timeman'))
		{
			return true;
		}

		$timeManUser = new \CTimeManUser($userId);
		$timeManSettings = $timeManUser->GetSettings(Array('UF_TIMEMAN'));
		if (!$timeManSettings['UF_TIMEMAN'])
		{
			$result = true;
		}
		else
		{
			$timeManUser->GetCurrentInfo(true); // need for reload cache

			if ($timeManUser->State() == 'OPENED')
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
		}

		return $result;
	}
}
