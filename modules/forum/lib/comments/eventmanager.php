<?php

namespace Bitrix\Forum\Comments;
use \Bitrix\Main\Loader;
class EventManager
{
	public static function init()
	{
		if (IsModuleInstalled("iblock"))
		{
			AddEventHandler("forum", "onAfterMessageAdd", array(__CLASS__, "updateIBlockPropertyAfterAddingMessage"));
			AddEventHandler("forum", "onMessageModerate", array(__CLASS__, "updateIBlockProperty"));
			AddEventHandler("forum", "onAfterMessageDelete", array(__CLASS__, "updateIBlockPropertyAfterDeletingMessage"));
		}
		AddEventHandler("forum", "onMessageIsIndexed", array(__CLASS__, "onMessageIsIndexed"));
	}

	public static function updateIBlockPropertyAfterAddingMessage($ID, $arFields, $arTopic = array())
	{
		if ($ID > 0 && $arFields["PARAM1"] != "IB" && $arFields["APPROVED"] == "Y")
		{
			self::updateIBlockProperty($ID, "SHOW", $arFields, $arTopic);
		}
	}

	public static function updateIBlockPropertyAfterDeletingMessage($ID, $arFields)
	{
		if ($ID > 0 && $arFields["PARAM1"] != "IB" && $arFields["APPROVED"] == "Y")
		{
			self::updateIBlockProperty($ID, "HIDE", $arFields);
		}
	}

	public static function updateIBlockProperty($ID, $TYPE, $arMessage, $arTopic = array())
	{
		if ($ID > 0 && $arMessage["PARAM1"] != "IB" && IsModuleInstalled("iblock"))
		{
			$arTopic = (empty($arTopic) ? \CForumTopic::GetByID($arMessage["TOPIC_ID"]) : $arTopic);
			if (!empty($arTopic) && $arTopic["XML_ID"] == "IBLOCK_".$arMessage["PARAM2"] && \CModule::IncludeModule("iblock"))
			{
				\CIBlockElement::SetPropertyValuesEx($arMessage["PARAM2"], 0, array(
					"FORUM_MESSAGE_CNT" => array(
						"VALUE" => \CForumMessage::GetList(array(), array("TOPIC_ID" => $arMessage["TOPIC_ID"], "APPROVED" => "Y", "!PARAM1" => "IB"), true),
						"DESCRIPTION" => "",
					)
				));
			}
		}
	}

	/**
	 * Event before indexing message.
	 * @param integer $id Message ID.
	 * @param array $message Message data.
	 * @param array &$index Search index array.
	 * @return boolean
	 */
	public static function onMessageIsIndexed($id, array $message, array &$index)
	{
		if (!empty($message["PARAM1"]) && !empty($message["PARAM2"]))
			return false;

		if (isset($message["XML_ID"]) && !empty($message["XML_ID"]))
		{
			if (
				($protoEntity = Entity::getEntityByXmlId($message["XML_ID"])) &&
				(!is_null($protoEntity)) &&
				(empty($protoEntity["moduleId"]) || Loader::includeModule($protoEntity["moduleId"])) &&
				is_callable(array($protoEntity["className"], "onmessageisindexed"))
			)
			{
				$b = call_user_func_array(
					array($protoEntity["className"], "onmessageisindexed"),
					array($id, $message, &$index)
				);
				if ($b === false)
					return false;
			}
		}
		return true;
	}
}