<?php

namespace Bitrix\Socialnetwork\CommentAux;

class HandlerManager
{
	protected $handlerListByPostText = array();
	protected $handlerListByType = array();

	public function __construct()
	{
		$this->buildHandlerList();
	}

	protected function buildHandlerList(): void
	{
		/** @var Share $shareClass */
		$shareClass = Share::className();
		/** @var CreateTask $createTaskClass */
		$createTaskClass = CreateTask::className();
		/** @var CreateEntity $createEntityClass */
		$createEntityClass = CreateEntity::className();
		/** @var FileVersion $fileVersionClass */
		$fileVersionClass = FileVersion::className();
		/** @var TaskInfo $taskInfoClass */
		$taskInfoClass = TaskInfo::className();

		$this->handlerListByPostText = array(
			$shareClass::getPostText() => $shareClass,
			$createTaskClass::getPostText() => $createTaskClass,
			$createEntityClass::getPostText() => $createEntityClass,
			$fileVersionClass::getPostText() => $fileVersionClass,
			$taskInfoClass::getPostText() => $taskInfoClass,
		);
		$this->handlerListByType = array(
			$shareClass::getType() => $shareClass,
			$createTaskClass::getType() => $createTaskClass,
			$createEntityClass::getType() => $createEntityClass,
			$fileVersionClass::getType() => $fileVersionClass,
			$taskInfoClass::getType() => $taskInfoClass,
		);
	}

	public function getHandlerByPostText($postText)
	{
		$handler = false;

		if(isset($this->handlerListByPostText[$postText]))
		{
			$handler = new $this->handlerListByPostText[$postText]();
		}

		return $handler;
	}

	public function getHandlerByType($type)
	{
		$handler = false;

		if(isset($this->handlerListByType[$type]))
		{
			$handler = new $this->handlerListByType[$type]();
		}

		return $handler;
	}
}
