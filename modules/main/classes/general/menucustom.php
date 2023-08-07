<?php

class CMenuCustom
{
	static $instance;
	var $arItems = array();

	protected function __construct()
	{
	}

	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function AddItem($type="left", $arItem=array())
	{
		if (empty($arItem))
			return;

		if (!array_key_exists("TEXT", $arItem) || trim($arItem["TEXT"]) == '')
			return;

		if (!array_key_exists("LINK", $arItem) || trim($arItem["LINK"]) == '')
			$arItem["LINK"] = "";

		if (!array_key_exists("SELECTED", $arItem))
			$arItem["SELECTED"] = false;

		if (!array_key_exists("PERMISSION", $arItem))
			$arItem["PERMISSION"] = "R";

		if (!array_key_exists("DEPTH_LEVEL", $arItem))
			$arItem["DEPTH_LEVEL"] = 1;

		if (!array_key_exists("IS_PARENT", $arItem))
			$arItem["IS_PARENT"] = false;

		$this->arItems[$type][] = array(
			"TEXT" => $arItem["TEXT"],
			"LINK" => $arItem["LINK"],
			"SELECTED" => $arItem["SELECTED"],
			"PERMISSION" => $arItem["PERMISSION"],
			"DEPTH_LEVEL" => $arItem["DEPTH_LEVEL"],
			"IS_PARENT" => $arItem["IS_PARENT"],
		);
	}

	public function GetItems($type="left")
	{
		if (array_key_exists($type, $this->arItems))
			return $this->arItems[$type];
		else
			return false;
	}
}
