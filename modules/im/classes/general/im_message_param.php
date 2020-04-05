<?
use Bitrix\Im as IM;
use Bitrix\Main\UrlPreview as UrlPreview;

class CIMMessageParam
{
	public static function Set($messageId, $params = Array())
	{
		$messageId = intval($messageId);
		if(!(is_array($params) || is_null($params)) || $messageId <= 0)
			return false;

		if (is_null($params) || count($params) <= 0)
		{
			return self::DeleteAll($messageId);
		}

		$default = self::GetDefault();

		$arToDelete = array();
		foreach ($params as $key => $val)
		{
			if (isset($default[$key]) && $default[$key] == $val)
			{
				$arToDelete[$key] = array(
					"=MESSAGE_ID" => $messageId,
					"=PARAM_NAME" => $key,
				);
			}
		}

		$arToInsert = array();
		foreach($params as $k1 => $v1)
		{
			$name = substr(trim($k1), 0, 100);
			if(strlen($name))
			{
				if(is_object($v1) && $v1 instanceof \Bitrix\Im\Bot\Keyboard)
				{
					$v1 = array($v1);
				}
				else if(is_object($v1) && $v1 instanceof \Bitrix\Im\Bot\ContextMenu)
				{
					$v1 = array($v1);
				}
				else if(is_object($v1) && $v1 instanceof CIMMessageParamAttach)
				{
					$v1 = array($v1);
				}
				else if(is_object($v1) && $v1 instanceof \Bitrix\Main\Type\DateTime)
				{
					$v1 = array($v1->getTimestamp());
				}
				else if(is_array($v1) && \Bitrix\Main\Type\Collection::isAssociative($v1))
				{
					$v1 = array($v1);
				}
				else if (!is_array($v1))
				{
					$v1 = array($v1);
				}

				if (empty($v1))
				{
					$arToDelete[$name] = array(
						"=MESSAGE_ID" => $messageId,
						"=PARAM_NAME" => $name,
					);
				}
				else
				{
					foreach($v1 as $v2)
					{
						if (is_array($v2))
						{
							$value = \Bitrix\Main\Web\Json::encode($v2);
							if(strlen($value) > 0 && strlen($value) < 60000)
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => isset($v2['ID'])? $v2['ID']: time(),
									"PARAM_JSON" => $value,
								);
							}
						}
						else if(is_object($v2) && ($v2 instanceof \Bitrix\Im\Bot\Keyboard || $v2 instanceof \Bitrix\Im\Bot\ContextMenu))
						{
							$value = $v2->getJson();
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => "",
									"PARAM_JSON" => $value,
								);
							}
						}
						else if(is_object($v2) && $v2 instanceof CIMMessageParamAttach)
						{
							$value = $v2->GetJSON();
							$valueArray = $v2->GetArray();
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => $valueArray['ID'],
									"PARAM_JSON" => $value,
								);
							}
						}
						else
						{
							$value = substr(trim($v2), 0, 100);
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => $value,
								);
							}
						}
					}
				}
			}
		}

		if(!empty($arToInsert))
		{
			$messageParameters = IM\Model\MessageParamTable::getList(array(
				'select' => array('ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'),
				'filter' => array(
					'=MESSAGE_ID' => $messageId,
				),
			));
			while($ar = $messageParameters->fetch())
			{
				if (strlen($ar['PARAM_JSON']))
				{
					$key = md5($ar["PARAM_NAME"].$ar["PARAM_JSON"]);
				}
				else
				{
					$key = md5($ar["PARAM_NAME"].$ar["PARAM_VALUE"]);
				}
				if(array_key_exists($key, $arToInsert))
				{
					unset($arToInsert[$key]);
				}
				else if (isset($params[$ar["PARAM_NAME"]]))
				{
					IM\Model\MessageParamTable::delete($ar['ID']);
				}
			}
		}

		foreach($arToInsert as $parameterInfo)
		{
			IM\Model\MessageParamTable::add($parameterInfo);
		}

		foreach($arToDelete as $filter)
		{
			$messageParameters = IM\Model\MessageParamTable::getList(array(
				'select' => array('ID'),
				'filter' => $filter,
			));
			while ($parameterInfo = $messageParameters->fetch())
			{
				IM\Model\MessageParamTable::delete($parameterInfo['ID']);
			}
		}

		self::UpdateTimestamp($messageId);

		return true;
	}

	public static function UpdateTimestamp($messageId, $chatId = 0)
	{
		$messageId = intval($messageId);
		$chatId = intval($chatId);

		if ($chatId <= 0)
		{
			$message = \Bitrix\Im\Model\MessageTable::getById($messageId)->fetch();
			if ($message)
			{
				$chatId = $message['CHAT_ID'];
			}
		}
		if ($chatId <= 0)
		{
			return false;
		}

		$dateNow = new \Bitrix\Main\Type\DateTime();
		$timestamp = str_pad($chatId, 11, '0', STR_PAD_LEFT).' '.$dateNow->format('Y-m-d H:i:s');

		$orm = IM\Model\MessageParamTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=MESSAGE_ID' => $messageId, '=PARAM_NAME' => 'TS'),
		));
		if ($tsParam = $orm->fetch())
		{
			IM\Model\MessageParamTable::update($tsParam['ID'], array('PARAM_VALUE' => $timestamp));
		}
		else
		{
			IM\Model\MessageParamTable::add(array('MESSAGE_ID' => $messageId, 'PARAM_NAME' => 'TS', 'PARAM_VALUE' => $timestamp));
		}

		return true;
	}

	public static function SendPull($messageId, $sendExtraParams = true)
	{
		global $DB;

		if (!CModule::IncludeModule('pull'))
			return false;

		$messageId = intval($messageId);

		$sql = "
			SELECT C.ID CHAT_ID, C.TYPE MESSAGE_TYPE, M.AUTHOR_ID, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID
			FROM b_im_message M INNER JOIN b_im_chat C ON M.CHAT_ID = C.ID
			WHERE M.ID = ".$messageId."
		";
		$messageData = $DB->Query($sql)->Fetch();
		if (!$messageData)
			return false;

		$arPullMessage = Array(
			'id' => $messageId,
			'type' => $messageData['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE? 'private': 'chat',
		);

		$relations = CIMMessenger::GetRelationById($messageId);

		if ($messageData['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$arFields['FROM_USER_ID'] = $messageData['AUTHOR_ID'];
			foreach ($relations as $rel)
			{
				if ($rel['USER_ID'] != $messageData['AUTHOR_ID'])
					$arFields['TO_USER_ID'] = $rel['USER_ID'];
			}

			$arPullMessage['fromUserId'] = $arFields['FROM_USER_ID'];
			$arPullMessage['toUserId'] = $arFields['TO_USER_ID'];
		}
		else
		{
			$arPullMessage['chatId'] = $messageData['CHAT_ID'];
			$arPullMessage['senderId'] = $messageData['AUTHOR_ID'];

			if ($messageData['CHAT_ENTITY_TYPE'] == 'LINES')
			{
				foreach ($relations as $rel)
				{
					if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($relations[$rel["USER_ID"]]);
					}
				}
			}
		}

		$arMessages[$messageId] = Array();
		$params = CIMMessageParam::Get(Array($messageId), false, $sendExtraParams === true);
		$arMessages[$messageId]['params'] = $params[$messageId];

		if (is_array($sendExtraParams) && !empty($sendExtraParams))
		{
			$arDefault = CIMMessageParam::GetDefault();
			foreach($sendExtraParams as $key)
			{
				if (!isset($arMessages[$messageId]['params'][$key]))
				{
					$arMessages[$messageId]['params'][$key] = $arDefault[$key];
				}
			}
		}

		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
		$arPullMessage['params'] = CIMMessenger::PrepareParamsForPull($arMessages[$messageId]['params']);

		\Bitrix\Pull\Event::add(array_keys($relations), Array(
			'module_id' => 'im',
			'command' => 'messageParamsUpdate',
			'params' => $arPullMessage,
			'extra' => Array(
				'im_revision' => IM_REVISION,
				'im_revision_mobile' => IM_REVISION_MOBILE,
			),
		));

		if ($messageData['MESSAGE_TYPE'] == IM_MESSAGE_OPEN || $messageData['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$messageData['CHAT_ID'], Array(
				'module_id' => 'im',
				'command' => 'messageParamsUpdate',
				'params' => $arPullMessage,
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public static function DeleteAll($messageId)
	{
		$messageId = intval($messageId);
		if ($messageId <= 0)
			return false;

		$messageParameters = IM\Model\MessageParamTable::getList(array(
			'select' => array('ID', 'PARAM_NAME'),
			'filter' => array(
				'=MESSAGE_ID' => $messageId,
			),
		));
		while ($parameterInfo = $messageParameters->fetch())
		{
			if ($parameterInfo['PARAM_NAME'] == 'TS')
				continue;

			IM\Model\MessageParamTable::delete($parameterInfo['ID']);
		}

		self::UpdateTimestamp($messageId);

		return true;
	}

	public static function DeleteByParam($paramName, $paramValue)
	{
		if (strlen($paramName) <= 0 || strlen($paramValue) <= 0 || $paramValue == 'TS')
		{
			return false;
		}

		$messageParameters = IM\Model\MessageParamTable::getList(array(
			'select' => array('ID', 'MESSAGE_ID'),
			'filter' => array(
				'=PARAM_NAME' => $paramName,
				'=PARAM_VALUE' => $paramValue,
			),
		));
		while ($parameterInfo = $messageParameters->fetch())
		{
			if ($parameterInfo['PARAM_NAME'] == 'TS')
				continue;

			IM\Model\MessageParamTable::delete($parameterInfo['ID']);
			self::UpdateTimestamp($parameterInfo['MESSAGE_ID']);
		}

		return true;
	}

	public static function Get($messageId, $paramName = false, $withDefault = false)
	{
		$arResult = array();
		if (is_array($messageId))
		{
			if (!empty($messageId))
			{
				foreach ($messageId as $key => $value)
				{
					$messageId[$key] = intval($value);
					$arResult[$messageId[$key]] = Array();
				}
			}
			else
			{
				return $arResult;
			}
		}
		else
		{
			$messageId = intval($messageId);
			if ($messageId <= 0)
			{
				return false;
			}
			$arResult[$messageId] = Array();
		}

		$filter = array(
			'=MESSAGE_ID' => $messageId,
		);
		if ($paramName && strlen($paramName) > 0)
		{
			$filter['=PARAM_NAME'] = $paramName;
		}
		$messageParameters = IM\Model\MessageParamTable::getList(array(
			'select' => array('ID', 'MESSAGE_ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'),
			'filter' => $filter,
		));
		while($ar = $messageParameters->fetch())
		{
			if (strlen($ar["PARAM_JSON"]))
			{
				$value = \Bitrix\Main\Web\Json::decode($ar["PARAM_JSON"]);
			}
			else
			{
				$value = $ar["PARAM_VALUE"];
			}
			if (in_array($ar["PARAM_NAME"], Array('KEYBOARD', 'MENU')))
			{
				$arResult[$ar["MESSAGE_ID"]][$ar["PARAM_NAME"]] = $value;
			}
			else
			{
				$arResult[$ar["MESSAGE_ID"]][$ar["PARAM_NAME"]][] = $value;
			}
		}

		if (is_array($messageId))
		{
			foreach ($messageId as $key)
			{
				$arResult[$key] = self::PrepareValues($arResult[$key], $withDefault);
			}
		}
		else
		{
			$arResult = self::PrepareValues($arResult[$messageId], $withDefault);
		}

		if ($paramName)
		{
			$arResult = isset($arResult[$paramName])? $arResult[$paramName]: null;
		}

		return $arResult;
	}

	public static function GetMessageIdByParam($paramName, $paramValue, $chatId = null)
	{
		$arResult = Array();
		if (strlen($paramName) <= 0 || strlen($paramValue) <= 0)
		{
			return $arResult;
		}
		$filter = array(
			'=PARAM_NAME' => $paramName,
			'=PARAM_VALUE' => $paramValue,
		);
		if ($chatId)
		{
			$filter['=MESSAGE.CHAT_ID'] = $chatId;
		}

		$messageParameters = IM\Model\MessageParamTable::getList(array(
			'select' => array('MESSAGE_ID'),
			'filter' => $filter,
		));
		while($ar = $messageParameters->fetch())
		{
			$arResult[] = $ar["MESSAGE_ID"];
		}

		return $arResult;
	}

	public static function PrepareValues($values, $withDefault = false)
	{
		$arValues = Array();

		$arDefault = self::GetDefault();
		foreach($values as $key => $value)
		{
			if (in_array($key, Array('IS_DELETED', 'IS_EDITED', 'CAN_ANSWER', 'IMOL_QUOTE_MSG', 'SENDING', 'URL_ONLY')))
			{
				$arValues[$key] = in_array($value[0], Array('Y', 'N'))? $value[0]: $arDefault[$key];
			}
			else if (in_array($key, Array('KEYBOARD_UID')))
			{
				$arValues[$key] = intval($value);
			}
			else if (in_array($key, Array('CHAT_ID', 'CHAT_MESSAGE', 'IMOL_VOTE_SID', 'IMOL_VOTE_USER', 'IMOL_VOTE_HEAD', 'SENDING_TS', 'IMOL_SID')))
			{
				$arValues[$key] = intval($value[0]);
			}
			else if (in_array($key, Array('CHAT_LAST_DATE')))
			{
				if (is_object($value[0]) && $value[0] instanceof \Bitrix\Main\Type\DateTime)
				{
					$arValues[$key] = $value[0];
				}
				else
				{
					$arValues[$key] = \Bitrix\Main\Type\DateTime::createFromTimestamp(intval($value[0]));
				}
			}
			else if ($key == 'DATE_TEXT')
			{
				if (is_array($value) && !empty($value))
				{
					foreach ($value as $k => $v)
					{
						$arValues[$key][$k] = htmlspecialcharsbx($v);
					}
				}
				else if (!is_array($value))
				{
					$arValues[$key] = htmlspecialcharsbx($value);
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'CHAT_USER' || $key == 'DATE_TS' || $key == 'FILE_ID' || $key == 'LIKE'  || $key == 'FAVORITE' || $key == 'KEYBOARD_ACTION' || $key == 'URL_ID' || $key == 'LINK_ACTIVE')
			{
				if (is_array($value) && !empty($value))
				{
					foreach ($value as $k => $v)
					{
						$arValues[$key][$k] = intval($v);
					}
				}
				else if (!is_array($value) && intval($value) > 0)
				{
					$arValues[$key] = intval($value);
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'CONNECTOR_MID')
			{
				if (is_array($value) && !empty($value))
				{
					foreach ($value as $k => $v)
					{
						$arValues[$key][$k] = $v;
					}
				}
				else if (!is_array($value) && strlen($value) > 0)
				{
					$arValues[$key] = $value;
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'ATTACH')
			{
				if (isset($value))
				{
					$arValues[$key] = CIMMessageParamAttach::PrepareAttach($value);
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'CLASS' || $key == 'IMOL_VOTE' || $key == 'IMOL_VOTE_TEXT' ||  $key == 'IMOL_VOTE_LIKE' ||  $key == 'IMOL_VOTE_DISLIKE' ||  $key == 'IMOL_FORM')
			{
				$arValues[$key] = isset($value[0])? $value[0]: '';
			}
			else if ($key == 'CONNECTOR_MID')
			{
				$arValues[$key] = $value;
			}
			else if ($key == 'NAME')
			{
				$arValues[$key] = isset($value[0])? htmlspecialcharsbx($value[0]): $arDefault[$key];
			}
			else if ($key == 'USER_ID')
			{
				$arValues[$key] = isset($value[0])? intval($value[0]): $arDefault[$key];
			}
			else if ($key == 'AVATAR')
			{
				if (isset($value))
				{
					$arFileTmp = \CFile::ResizeImageGet(
						$value[0],
						array('width' => 100, 'height' => 100),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true
					);
					$arValues[$key] = empty($arFileTmp['src'])? '': $arFileTmp['src'];
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if (isset($arDefault[$key]))
			{
				$arValues[$key] = $value;
			}
		}

		if ($withDefault)
		{
			foreach($arDefault as $key => $value)
			{
				if (!isset($arValues[$key]))
				{
					$arValues[$key] = $value;
				}
			}
		}
		else
		{
			foreach($arDefault as $key => $value)
			{
				if (isset($arValues[$key]) && $arValues[$key] == $value)
				{
					unset($arValues[$key]);
				}
			}
		}

		return $arValues;
	}

	public static function GetDefault()
	{
		$arDefault = Array(
			'CODE' => '',
			'FAVORITE' => Array(),
			'LIKE' => Array(),
			'FILE_ID' => Array(),
			'URL_ID' => Array(),
			'URL_ONLY' => 'N',
			'ATTACH' => Array(),
			'LINK_ACTIVE' => Array(),
			'MENU' => 'N',
			'KEYBOARD' => 'N',
			'KEYBOARD_UID' => 0,
			'CONNECTOR_MID' => Array(),
			'IS_ERROR' => 'N',
			'IS_DELIVERED' => 'Y',
			'IS_DELETED' => 'N',
			'IS_EDITED' => 'N',
			'SENDING' => 'N',
			'SENDING_TS' => 0,
			'CAN_ANSWER' => 'N',
			'CLASS' => '',
			'USER_ID' => '',
			'NAME' => '',
			'AVATAR' => '',
			'CHAT_ID' => 0,
			'CHAT_MESSAGE' => 0,
			'CHAT_LAST_DATE' => '',
			'CHAT_USER' => Array(),
			'DATE_TEXT' => Array(),
			'DATE_TS' => Array(),
			'IMOL_VOTE' => '',
			'IMOL_VOTE_TEXT' => '',
			'IMOL_VOTE_LIKE' => '',
			'IMOL_VOTE_DISLIKE' => '',
			'IMOL_VOTE_SID' => '',
			'IMOL_VOTE_USER' => '',
			'IMOL_VOTE_HEAD' => '',
			'IMOL_QUOTE_MSG' => 'N',
			'IMOL_SID' => 0,
			'IMOL_FORM' => '',
		);

		return $arDefault;
	}
}


class CIMMessageParamAttach
{
	const NORMAL = "#aac337";
	const ATTENTION = "#e8a441";
	const PROBLEM = "#df532d";
	const TRANSPARENT = "TRANSPARENT";
	const CHAT = "CHAT";

	private $result = Array();

	function __construct($id = null, $color = null)
	{
		$this->result['ID'] = $id? $id: time();
		$this->result['BLOCKS'] = Array();

		if ($color == self::TRANSPARENT)
		{
			$this->result['COLOR'] = 'transparent';
		}
		else if ($color != self::CHAT)
		{
			if (!$color || !preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $color))
			{
				$color = Bitrix\Im\Color::getRandomColor();
			}
			$this->result['COLOR'] = $color;
		}
	}

	public function AddUser($params)
	{
		$add = Array();
		if (!isset($params['NAME']) || strlen(trim($params['NAME'])) <= 0)
			return false;

		$add['NAME'] = htmlspecialcharsbx(trim($params['NAME']));
		$add['AVATAR_TYPE'] = 'USER';

		if (isset($params['NETWORK_ID']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(substr($params['NETWORK_ID'], 0,1)).intval(substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0)
		{
			$add['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0)
		{
			$add['CHAT_ID'] = intval($params['CHAT_ID']);
			$add['AVATAR_TYPE'] = 'CHAT';
		}
		else if (isset($params['BOT_ID']) && intval($params['BOT_ID']) > 0)
		{
			$add['BOT_ID'] = intval($params['BOT_ID']);
			$add['AVATAR_TYPE'] = 'BOT';
		}
		else if (isset($params['LINK']) && preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			$add['LINK'] = htmlspecialcharsbx($params['LINK']);
		}

		if (isset($params['AVATAR']) && preg_match('#^(?:/|https?://)#', $params['AVATAR']))
		{
			$add['AVATAR'] = htmlspecialcharsbx($params['AVATAR']);
		}

		if (isset($params['AVATAR_TYPE']) && in_array($params['AVATAR_TYPE'], Array('CHAT', 'USER', 'BOT')))
		{
			$add['AVATAR_TYPE'] = $params['AVATAR_TYPE'];
		}

		$this->result['BLOCKS'][]['USER'] = Array($add);

		return true;
	}

	public function AddChat($params)
	{
		$params['AVATAR_TYPE'] = 'CHAT';
		return $this->AddUser($params);
	}

	public function AddBot($params)
	{
		$params['AVATAR_TYPE'] = 'BOT';
		return $this->AddUser($params);
	}

	public function AddLink($params)
	{
		$add = Array();

		if (isset($params['NETWORK_ID']) && isset($params['NAME']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(substr($params['NETWORK_ID'], 0,1)).intval(substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0 && isset($params['NAME']))
		{
			$add['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0 && isset($params['NAME']))
		{
			$add['CHAT_ID'] = intval($params['CHAT_ID']);
		}
		else if (!isset($params['LINK']) || isset($params['LINK']) && !preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			return false;
		}

		if (isset($params['NAME']))
		{
			$add['NAME'] = htmlspecialcharsbx(trim($params['NAME']));
		}
		if (isset($params['LINK']))
		{
			$add['LINK'] = htmlspecialcharsbx($params['LINK']);
		}

		if (isset($params['DESC']))
		{
			$params['DESC'] = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', trim($params['DESC'])));
			$add['DESC'] = str_replace(array('#BR#', '[br]', '[BR]'), '<br/>', $params['DESC']);
		}

		if (isset($params['HTML']))
		{
			$sanitizer = new CBXSanitizer();
			$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
			$sanitizer->ApplyHtmlSpecChars(false);

			$add['HTML'] = $sanitizer->SanitizeHtml($params['HTML']);
		}
		else if (isset($params['PREVIEW']) && preg_match('#^(?:/|https?://)#', $params['PREVIEW']))
		{
			$add['PREVIEW'] = htmlspecialcharsbx($params['PREVIEW']);
		}

		$this->result['BLOCKS'][]['LINK'] = Array($add);

		return true;
	}

	public function AddRichLink($params)
	{
		$add = Array();

		if (isset($params['NETWORK_ID']) && isset($params['NAME']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(substr($params['NETWORK_ID'], 0,1)).intval(substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0 && isset($params['NAME']))
		{
			$add['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0 && isset($params['NAME']))
		{
			$add['CHAT_ID'] = intval($params['CHAT_ID']);
		}
		else if (!isset($params['LINK']) || isset($params['LINK']) && !preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			return false;
		}

		if (isset($params['NAME']))
		{
			$add['NAME'] = htmlspecialcharsbx(trim($params['NAME']));
		}
		if (isset($params['LINK']))
		{
			$add['LINK'] = htmlspecialcharsbx($params['LINK']);
		}

		if (isset($params['DESC']))
		{
			$params['DESC'] = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', trim($params['DESC'])));
			$add['DESC'] = str_replace(array('#BR#', '[br]', '[BR]'), '<br/>', $params['DESC']);
		}

		if (isset($params['HTML']))
		{
			$sanitizer = new CBXSanitizer();
			$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
			$sanitizer->ApplyHtmlSpecChars(false);

			$add['HTML'] = $sanitizer->SanitizeHtml($params['HTML']);
		}
		else if (isset($params['PREVIEW']) && preg_match('#^(?:/|https?://)#', $params['PREVIEW']))
		{
			$add['PREVIEW'] = htmlspecialcharsbx($params['PREVIEW']);
		}

		$this->result['BLOCKS'][]['RICH_LINK'] = Array($add);

		return true;
	}

	public function AddHtml($html)
	{
		if (!isset($html))
			return false;

		$sanitizer = new CBXSanitizer();
		$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_LOW);
		$sanitizer->ApplyHtmlSpecChars(false);

		$html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);

		$this->result['BLOCKS'][]['HTML'] = $sanitizer->SanitizeHtml($html);

		return true;
	}

	public function AddMessage($message)
	{
		$message = trim($message);
		if (strlen($message) <= 0)
			return false;

		$message = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', $message));

		$this->result['BLOCKS'][]['MESSAGE'] = $message;

		return true;
	}

	public function AddGrid($params)
	{
		$add = Array();

		foreach ($params as $grid)
		{
			$result = Array();

			if ($grid['DISPLAY'] != 'LINE')
			{
				if (!isset($grid['NAME']) || strlen(trim($grid['NAME'])) <= 0)
					continue;
			}

			if (isset($grid['DISPLAY']) && in_array($grid['DISPLAY'], Array('BLOCK', 'ROW', 'LINE', 'COLUMN')))
			{
				if ($grid['DISPLAY'] == 'COLUMN')
				{
					$grid['DISPLAY'] = 'ROW';
				}
				$result['DISPLAY'] = $grid['DISPLAY'];
			}
			else
			{
				$result['DISPLAY'] = 'BLOCK';
			}

			$result['NAME'] = htmlspecialcharsbx(trim($grid['NAME']));

			$result['VALUE'] = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', trim($grid['VALUE'])));

			if (preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $grid['COLOR']))
			{
				$result['COLOR'] = $grid['COLOR'];
			}
			if (isset($grid['WIDTH']) && intval($grid['WIDTH']) > 0)
			{
				$result['WIDTH'] = intval($grid['WIDTH']);
			}
			if (isset($grid['HEIGHT']) && intval($grid['HEIGHT']) > 0)
			{
				$result['HEIGHT'] = intval($grid['HEIGHT']);
			}
			if (isset($grid['USER_ID']) && intval($grid['USER_ID']) > 0)
			{
				$result['USER_ID'] = intval($grid['USER_ID']);
			}
			if (isset($grid['CHAT_ID']) && intval($grid['CHAT_ID']) > 0)
			{
				$result['CHAT_ID'] = intval($grid['CHAT_ID']);
			}
			if (isset($grid['LINK']) && preg_match('#^(?:/|https?://)#', $grid['LINK']))
			{
				$result['LINK'] = htmlspecialcharsbx($grid['LINK']);
			}

			$add[] = $result;
		}
		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['GRID'] = $add;

		return true;
	}

	public function AddImages($params)
	{
		$add = Array();

		foreach ($params as $images)
		{
			$result = Array();

			if (!isset($images['LINK']) || isset($images['LINK']) && !preg_match('#^(?:/|https?://)#', $images['LINK']))
				continue;

			if (isset($images['NAME']) && strlen(trim($images['NAME'])) > 0)
			{
				$result['NAME'] = htmlspecialcharsbx(trim($images['NAME']));
			}

			$result['LINK'] = htmlspecialcharsbx($images['LINK']);

			if (isset($images['PREVIEW']) && preg_match('#^(?:/|https?://)#', $images['PREVIEW']))
			{
				$result['PREVIEW'] = htmlspecialcharsbx($images['PREVIEW']);
			}

			$add[] = $result;
		}

		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['IMAGE'] = $add;

		return true;
	}

	public function AddFiles($params)
	{
		$add = Array();

		foreach ($params as $files)
		{
			$result = Array();

			if (!isset($files['LINK']) || isset($files['LINK']) && !preg_match('#^(?:/|https?://)#', $files['LINK']))
				continue;

			$result['LINK'] = htmlspecialcharsbx($files['LINK']);

			if (isset($files['NAME']) && strlen(trim($files['NAME'])) > 0)
			{
				$result['NAME'] = htmlspecialcharsbx(trim($files['NAME']));
			}

			if (isset($files['SIZE']) && intval($files['SIZE']) > 0)
			{
				$result['SIZE'] = intval($files['SIZE']);
			}

			$add[] = $result;
		}

		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['FILE'] = $add;

		return true;
	}

	public function AddDelimiter($params = Array())
	{
		$add = Array();

		$add['SIZE'] = isset($params['SIZE'])? intval($params['SIZE']): 0;
		if ($add['SIZE'] <= 0)
		{
			$add['SIZE'] = 200;
		}

		if (preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $params['COLOR']))
		{
			$add['COLOR'] = $params['COLOR'];
		}
		else
		{
			$add['COLOR'] = '#c6c6c6';
		}

		$this->result['BLOCKS'][]['DELIMITER'] = $add;
	}

	private static function decodeBbCode($message)
	{
		return \Bitrix\Im\Text::parse($message, Array('SAFE' => 'N'));
	}

	public static function GetAttachByJson($array)
	{
		if (is_string($array))
		{
			$array = \CUtil::JsObjectToPhp($array);
		}
		if (!is_array($array))
		{
			return null;
		}

		$id = null;
		$color = \CIMMessageParamAttach::CHAT;
		$attach = null;

		if (isset($array['BLOCKS']))
		{
			$blocks = $array['BLOCKS'];

			if (isset($array['ID']))
			{
				$id = $array['ID'];
			}
			if (isset($array['COLOR']))
			{
				$color = $array['COLOR'];
			}
		}
		else
		{
			$blocks = $array;
		}

		$attach = new CIMMessageParamAttach($id, $color);
		foreach ($blocks as $data)
		{
			if (isset($data['USER']))
			{
				if (is_array($data['USER']) && !\Bitrix\Main\Type\Collection::isAssociative($data['USER']))
				{
					foreach ($data['USER'] as $dataItem)
					{
						$attach->AddUser($dataItem);
					}
				}
				else
				{
					$attach->AddUser($data['USER']);
				}
			}
			else if (isset($data['LINK']))
			{
				if (is_array($data['LINK']) && !\Bitrix\Main\Type\Collection::isAssociative($data['LINK']))
				{
					foreach ($data['LINK'] as $dataItem)
					{
						$attach->AddLink($dataItem);
					}
				}
				else
				{
					$attach->AddLink($data['LINK']);
				}
			}
			else if (isset($data['RICH_LINK']))
			{
				if (is_array($data['RICH_LINK']) && !\Bitrix\Main\Type\Collection::isAssociative($data['RICH_LINK']))
				{
					foreach ($data['RICH_LINK'] as $dataItem)
					{
						$attach->AddRichLink($dataItem);
					}
				}
				else
				{
					$attach->AddRichLink($data['RICH_LINK']);
				}
			}
			else if (isset($data['MESSAGE']))
			{
				$attach->AddMessage($data['MESSAGE']);
			}
			else if (isset($data['GRID']))
			{
				$attach->AddGrid($data['GRID']);
			}
			else if (isset($data['IMAGE']))
			{
				if (is_array($data['IMAGE']) && \Bitrix\Main\Type\Collection::isAssociative($data['IMAGE']))
				{
					$data['IMAGE'] = Array($data['IMAGE']);
				}
				$attach->AddImages($data['IMAGE']);
			}
			else if (isset($data['FILE']))
			{
				if (is_array($data['FILE']) && \Bitrix\Main\Type\Collection::isAssociative($data['FILE']))
				{
					$data['FILE'] = Array($data['FILE']);
				}
				$attach->AddFiles($data['FILE']);
			}
			else if (isset($data['DELIMITER']))
			{
				$attach->AddDelimiter($data['DELIMITER']);
			}
		}

		return $attach->IsEmpty()? null: $attach;
	}

	public static function PrepareAttach($attach)
	{
		if (!is_array($attach))
			return $attach;

		$isCollection = true;
		if(\Bitrix\Main\Type\Collection::isAssociative($attach))
		{
			$isCollection = false;
			$attach = array($attach);
		}

		foreach ($attach as $attachId => $attachValue)
		{
			if (isset($attachValue['BLOCKS']))
			{
				foreach ($attachValue['BLOCKS'] as $blockId => $block)
				{
					if (isset($block['GRID']))
					{
						foreach ($block['GRID'] as $key => $value)
						{
							$attach[$attachId]['BLOCKS'][$blockId]['GRID'][$key]['VALUE'] = self::decodeBbCode($value['VALUE']);
						}
					}
					else if (isset($block['MESSAGE']))
					{
						$attach[$attachId]['BLOCKS'][$blockId]['MESSAGE'] = self::decodeBbCode($block['MESSAGE']);
					}
				}
			}
		}

		return $isCollection? $attach: $attach[0];
	}

	public function IsEmpty()
	{
		return empty($this->result['BLOCKS']);
	}

	public function IsAllowSize()
	{
		return $this->GetJSON()? true: false;
	}

	public function SetId($id)
	{
		$this->result['ID'] = $id;
		return true;
	}

	public function GetId()
	{
		return $this->result['ID'];
	}

	public function GetArray()
	{
		return $this->result;
	}

	public function GetJSON()
	{
		$result = \Bitrix\Main\Web\Json::encode($this->result);
		return strlen($result) < 60000? $result: "";
	}
}

class CIMMessageLink
{
	private $result = false;
	private $message = "";
	private $attach = Array();
	private $urlId = Array();
	private $staticUrl = Array();

	public function prepareInsert($text)
	{
		$this->message = $text;

		$parser = new CTextParser();
		$parser->allow = array("ANCHOR" => "Y", "USER" => "N",  "NL2BR" => "N", "HTML" => "Y", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "N", "VIDEO" => "N", "TABLE" => "N", "ALIGN" => "N");

		$convertedText = preg_replace('#\-{54}.+?\-{54}#s', "xxx", $this->message);
		$convertedText = $parser->convertText($convertedText);

		preg_replace_callback('#<a\s+href="(?P<URL>[^"]+?)".+?>(?P<TEXT>.+?)</a>#', array($this, "prepareUrlObjects"), $convertedText, 1);

		return $this->result();
	}

	private function prepareUrlObjects($params)
	{
		$params['URL'] = htmlspecialcharsback($params['URL']);

		$linkParam = UrlPreview\UrlPreview::getMetadataAndHtmlByUrl($params['URL'], true, false);
		if (!$linkParam)
			return '[URL='.$params['URL'].']'.$params['TEXT'].'[/URL]';


		$attach = self::formatAttach($linkParam);
		if (!$attach)
		{
			return '[URL='.$params['URL'].']'.$params['TEXT'].'[/URL]';
		}

		$this->attach[$linkParam['ID']] = $attach;
		$this->urlId[$linkParam['ID']] = $linkParam['ID'];

		if ($linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_STATIC)
		{
			$this->staticUrl[] = $params['URL'];

			if (substr($params['URL'], -1) == '/')
			{
				$this->staticUrl[] = substr($params['URL'], 0, -1);
			}
		}

		$this->result = true;

		return '[URL='.$params['URL'].']'.$params['TEXT'].'[/URL]';
	}

	public static function prepareShow($arMessages, $params)
	{
		$arUrl = Array();
		foreach ($params as $messageId => $param)
		{
			if (isset($param['URL_ID']))
			{
				foreach ($param['URL_ID'] as $urlId)
				{
					$urlId = intval($urlId);
					if ($urlId > 0)
					{
						$arUrl[$urlId] = $urlId;
					}
				}
			}
		}

		if (!empty($arUrl))
		{
			$arAttachUrl = self::getAttachments($arUrl, true);
			if (!empty($arAttachUrl))
			{
				foreach ($params as $messageId => $param)
				{
					if (isset($param['URL_ID']))
					{
						foreach ($param['URL_ID'] as $urlId)
						{
							if (isset($arAttachUrl[$urlId]))
							{
								$arMessages[$messageId]['params']['ATTACH'][] = $arAttachUrl[$urlId];
							}
						}
					}
				}
			}
		}

		return $arMessages;
	}

	public static function getAttachments($id, $typeArray = false)
	{
		$attachArray = Array();

		if (is_array($id))
		{
			foreach ($id as $key => $value)
			{
				$id[$key] = intval($value);
			}
		}
		else
		{
			$id = array(intval($id));
		}

		if ($params = UrlPreview\UrlPreview::getMetadataAndHtmlByIds($id))
		{
			foreach ($params as $id => $linkParam)
			{
				if ($attach = self::formatAttach($linkParam))
				{
					$attachArray[$id] = $typeArray? $attach->GetArray(): $attach;
				}
			}
		}

		return $attachArray;
	}

	public static function formatAttach($linkParam)
	{
		$attach = null;
		if ($linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_STATIC)
		{
			if ($linkParam['EXTRA']['PEER_IP_PRIVATE'] && IM\User::getInstance()->isExtranet())
			{
				return $attach;
			}
			if (intval($linkParam['IMAGE_ID']) > 0)
			{
				$image = CFile::ResizeImageGet(
					$linkParam['IMAGE_ID'],
					array('width' => 450, 'height' => 120),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false,
					false,
					true
				);
				$linkParam['IMAGE_ID'] = empty($image['src'])? '': $image['src'];
			}
			else if (strlen($linkParam['IMAGE']) > 0)
			{
				$linkParam['IMAGE_ID'] = $linkParam['IMAGE'];
			}
			else
			{
				$linkParam['IMAGE_ID'] = '';
			}

			$attach = new CIMMessageParamAttach($linkParam['ID'], CIMMessageParamAttach::TRANSPARENT);
			$attach->AddRichLink(Array(
				"NAME" => $linkParam['TITLE'],
				"DESC" => $linkParam['DESCRIPTION'],
				"LINK" => $linkParam['URL'],
				"PREVIEW" => $linkParam['IMAGE_ID']
			));
		}
		else if ($linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_DYNAMIC)
		{
			$attach = UrlPreview\UrlPreview::getImAttach($linkParam['URL'], true);
			if ($attach && $attach instanceof CIMMessageParamAttach)
			{
				$attach->SetId($linkParam['ID']);
			}
		}
		return $attach;
	}

	private function isLinkOnly()
	{
		$message = $this->message;
		foreach ($this->staticUrl as $url)
		{
			$message = str_replace($url, '', $message);
		}
		$message = trim($message);

		return empty($message);
	}

	public function result()
	{
		return Array(
			'RESULT' => $this->result,
			'MESSAGE' => $this->message,
			'MESSAGE_IS_LINK' => $this->isLinkOnly(),
			'URL_ID' => array_values($this->urlId),
			'ATTACH' => array_values($this->attach),
		);
	}
}

?>