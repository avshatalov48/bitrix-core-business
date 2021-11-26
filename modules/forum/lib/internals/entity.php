<?php

namespace Bitrix\Forum\Internals;

use Bitrix\Forum\ForumTable;
use Bitrix\Forum\Forum;
use Bitrix\Forum\Permission;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\SystemException;

abstract class Entity implements \ArrayAccess
{
	use \Bitrix\Forum\Internals\EntityBaseMethods;

	/** @var int */
	protected $authorId;
	/** @var Forum */
	protected $forum;
	/** @var array */
	protected $data;

	public function __construct($id)
	{
		$this->id = $id;
		if ($id <= 0)
		{
			throw new \Bitrix\Main\ArgumentNullException(static::class . " empty id.");
		}
		$this->init();

		$this->forum = new Forum($this->data["FORUM_ID"]);

		if ($this->authorId === null)
		{
			throw new \Bitrix\Main\ArgumentNullException("Author id must be defined.");
		}

		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
	}
	/**
	 * Init Entity data, Forum
	 * @return $this
	 */
	abstract protected function init();

	public function getId()
	{
		return $this->id;
	}

	public function getForumId()
	{
		return $this->forum->getId();
	}

	public function getAuthorId()
	{
		return $this->authorId;
	}
	// entity actions
	abstract public function edit(array $fields);
	abstract public function remove();
	abstract public static function create($parentObject, array $fields);

	// crud actions
	//abstract public static function add($parentObject, array $fields);
	abstract public static function update(int $id, array &$fields);
	abstract public static function delete(int $id);
}