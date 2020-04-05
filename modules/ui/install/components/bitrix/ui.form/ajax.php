<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\UI;

Main\Loader::includeModule('ui');

class CUIFormComponentAjaxController extends Main\Engine\Controller
{
	/** @var UI\Form\EntityEditorConfiguration|null  */
	protected static $configuration = null;

	protected static function getConfiguration()
	{
		if(self::$configuration === null)
		{
			self::$configuration = new UI\Form\EntityEditorConfiguration();
		}
		return self::$configuration;
	}

	public static function saveConfigurationAction($guid, array $config, array $params)
	{
		self::getConfiguration()->set($guid, $config, $params);
	}
	public static function resetConfigurationAction($guid, array $params)
	{
		self::getConfiguration()->reset($guid, $params);
	}

	public static function setScopeAction($guid, $scope)
	{
		self::getConfiguration()->setScope($guid, $scope);
	}

	public static function forceCommonScopeForAllAction($guid)
	{
		self::getConfiguration()->forceCommonScopeForAll($guid);
	}

	public static function renderImageInputAction($moduleId, $name, $value)
	{
		/*
		$component = new UI\Controller\Response\Entity\Component('bitrix:main.file.input');
		$component->setParameters(
			array(
				'MODULE_ID' => $moduleId,
				'MAX_FILE_SIZE' => 3145728,
				'MULTIPLE'=> 'N',
				'ALLOW_UPLOAD' => 'I',
				'SHOW_AVATAR_EDITOR' => 'Y',
				'ENABLE_CAMERA' => 'N',
				'CONTROL_ID' => strtolower($name).'_uploader',
				'INPUT_NAME' => $name,
				'INPUT_VALUE' => $value
			)
		);
		$component->setFunctionParameters(array('HIDE_ICONS' => 'Y'));
		return new UI\Controller\Response\Engine\Content($component);
		*/
	}
}