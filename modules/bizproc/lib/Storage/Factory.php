<?php

namespace Bitrix\Bizproc\Storage;

class Factory
{
	private static $instance;

	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getActivityStorage(\CBPActivity $activity): ActivityStorage
	{
		$tplId = $activity->getWorkflowTemplateId();
		$name = $activity->getName();

		return ActivityStorage::getInstance($tplId, $name);
	}

	public function onAfterTemplateDelete(int $id)
	{
		ActivityStorage::onAfterTemplateDelete($id);
	}

	private function __construct()
	{
	}

	private function __clone()
	{
	}
}
