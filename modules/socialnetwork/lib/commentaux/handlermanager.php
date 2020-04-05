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

	protected function buildHandlerList()
	{
		/** @var Share $shareClass */
		$shareClass = Share::className();
		/** @var CreateTask $createTaskClass */
		$createTaskClass = CreateTask::className();
		/** @var FileVersion $fileVersionClass */
		$fileVersionClass = FileVersion::className();

		$this->handlerListByPostText = array(
			$shareClass::getPostText() => $shareClass,
			$createTaskClass::getPostText() => $createTaskClass,
			$fileVersionClass::getPostText() => $fileVersionClass
		);
		$this->handlerListByType = array(
			$shareClass::getType() => $shareClass,
			$createTaskClass::getType() => $createTaskClass,
			$fileVersionClass::getType() => $fileVersionClass
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