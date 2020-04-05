<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\Model;

Loc::loadMessages(__FILE__);

/**
 * Class Chain
 * @package Bitrix\Sender\Entity
 */
class Chain
{
	/** @var Letter[] $letters */
	protected $letters = [];

	/** @var int[] $removeLetterList */
	protected $removeLetterList = [];

	/**
	 * Get list.
	 *
	 * @return Letter[]
	 */
	public function getList()
	{
		return $this->letters;
	}

	/**
	 * Get list.
	 *
	 * @return Letter|null
	 */
	public function getLast()
	{
		$letter = end($this->letters);
		reset($this->letters);

		return $letter;
	}

	/**
	 * Get letter.
	 *
	 * @param int $letterId Letter ID.
	 * @return Letter|null
	 */
	public function getLetter($letterId)
	{
		foreach ($this->letters as $letter)
		{
			if ($letter->getId() == $letterId)
			{
				return $letter;
			}
		}

		return null;
	}

	/**
	 * Add by ID.
	 *
	 * @param int $letterId Letter ID.
	 * @return $this
	 */
	public function addLetter($letterId)
	{
		if ($this->getLetter($letterId))
		{
			return $this;
		}

		$letter = self::createInstanceById($letterId);
		if ($letter)
		{
			$this->letters[] = $letter;
			$this->sort();
		}

		return $this;
	}

	/**
	 * shiftTime.
	 *
	 * @param int $letterId Letter ID.
	 * @param int $timeShift Time shift.
	 * @return $this
	 */
	public function shiftTime($letterId, $timeShift = 0)
	{
		$letter = $this->getLetter($letterId);
		if ($letter)
		{
			$letter->set('TIME_SHIFT', $timeShift);
		}

		return $this;
	}

	/**
	 * Remove.
	 *
	 * @param int $letterId Letter ID.
	 * @return $this
	 */
	public function removeLetter($letterId)
	{
		$list = [];
		foreach ($this->letters as $letter)
		{
			if ($letter->getId() == $letterId)
			{
				$this->removeLetterList[] = $letterId;
				continue;
			}

			$list[] = $letter;
		}
		$this->letters = $list;
		$this->sort();

		return $this;
	}

	/**
	 * Move up.
	 *
	 * @param int $letterId Letter ID.
	 * @return $this
	 */
	public function moveUp($letterId)
	{
		return $this->move($letterId, -1);
	}

	/**
	 * Move down.
	 *
	 * @param int $letterId Letter ID.
	 * @return $this
	 */
	public function moveDown($letterId)
	{
		return $this->move($letterId, 1);
	}

	/**
	 * Sort.
	 *
	 * @return $this
	 */
	public function sort()
	{
		$parentId = null;
		foreach ($this->letters as $letter)
		{
			$letter->set('PARENT_ID', $parentId);
			$parentId = $letter->getId();
		}

		return $this;
	}

	/**
	 * Save data.
	 *
	 * @return $this
	 */
	public function save()
	{
		foreach ($this->letters as $letter)
		{
			$letter->save();
		}

		foreach ($this->removeLetterList as $letterId)
		{
			Letter::removeById($letterId);
		}
		$this->removeLetterList = [];

		return $this;
	}

	/**
	 * Load data.
	 *
	 * @param integer $id Campaign ID.
	 * @return $this
	 */
	public function load($id)
	{
		$list = Model\LetterTable::getList([
			'select' => ['ID', 'PARENT_ID'],
			'filter' => ['=CAMPAIGN_ID' => $id],
		])->fetchAll();

		$limiter = 100;
		$parentId = null;
		while (--$limiter > 0)
		{
			$id = self::getIdByParentId($list, $parentId);
			if (!$id)
			{
				break;
			}

			$letter = self::createInstanceById($id);
			if (!$letter)
			{
				continue;
			}

			$this->letters[] = $letter;
			$parentId = $id;
		}

		return $this;
	}

	protected static function createInstanceById($id)
	{
		$letter = Letter::createInstanceById($id);
		if ($letter)
		{
			$letter->load($id);
		}

		return $letter->getId() ? $letter : null;
	}

	protected static function getIdByParentId(array $list, $parentId = null)
	{
		foreach ($list as $item)
		{
			if ($item['PARENT_ID'] == $parentId)
			{
				return $item['ID'];
				break;
			}
		}

		return null;
	}

	protected function move($letterId, $offset)
	{
		$letter = $this->getLetter($letterId);
		if (!$letter)
		{
			return $this;
		}

		$index = array_search($letter, $this->letters, true);
		$previousIndex = $index + $offset;
		if (!isset($this->letters[$previousIndex]))
		{
			return $this;
		}
		$previousLetter = $this->letters[$previousIndex];

		$this->letters[$previousIndex] = $letter;
		$this->letters[$index] = $previousLetter;

		$this->sort();

		return $this;
	}
}