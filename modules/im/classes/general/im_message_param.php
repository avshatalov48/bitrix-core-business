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
			$name = mb_substr(trim($k1), 0, 100);
			if($name <> '')
			{
				if(is_object($v1) && $v1 instanceof \Bitrix\Im\Bot\Keyboard)
				{
					$v1 = array($v1);
				}
				else
				{
					if(is_object($v1) && $v1 instanceof \Bitrix\Im\Bot\ContextMenu)
					{
						$v1 = array($v1);
					}
					else
					{
						if(is_object($v1) && $v1 instanceof CIMMessageParamAttach)
						{
							$v1 = array($v1);
						}
						else
						{
							if(is_object($v1) && $v1 instanceof \Bitrix\Main\Type\DateTime)
							{
								$v1 = array($v1->getTimestamp());
							}
							else
							{
								if(is_array($v1) && \Bitrix\Main\Type\Collection::isAssociative($v1))
								{
									$v1 = array($v1);
								}
								else
								{
									if(!is_array($v1))
									{
										$v1 = array($v1);
									}
								}
							}
						}
					}
				}

				if(empty($v1))
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
						if(is_array($v2))
						{
							$value = \Bitrix\Im\Common::jsonEncode($v2);
							if($value <> '' && mb_strlen($value) < 60000)
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => isset($v2['ID'])? $v2['ID'] : time(),
									"PARAM_JSON" => $value,
								);
							}
						}
						else
						{
							if(is_object($v2) && ($v2 instanceof \Bitrix\Im\Bot\Keyboard || $v2 instanceof \Bitrix\Im\Bot\ContextMenu))
							{
								$value = $v2->getJson();
								if($value <> '')
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
							else
							{
								if(is_object($v2) && $v2 instanceof CIMMessageParamAttach)
								{
									$value = $v2->GetJSON();
									$valueArray = $v2->GetArray();
									if($value <> '')
									{
										$description = $valueArray['DESCRIPTION'];
										if (mb_strlen($description) > 100)
										{
											$description = mb_substr($description, 0, 97) . '...';
										}
										$key = md5($name.$value);
										$arToInsert[$key] = array(
											"MESSAGE_ID" => $messageId,
											"PARAM_NAME" => $name,
											"PARAM_VALUE" => $description,
											"PARAM_JSON" => $value,
										);
									}
								}
								else
								{
									$value = mb_substr(trim($v2), 0, 100);
									if($value <> '')
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
				if ($ar['PARAM_JSON'] <> '')
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
			if (in_array($parameterInfo["PARAM_NAME"], Array('KEYBOARD', 'MENU', 'ATTACH', 'NAME',	'IMOL_VOTE_TEXT', 'IMOL_VOTE_LIKE', 'IMOL_VOTE_DISLIKE')))
			{
				$parameterInfo['PARAM_VALUE'] = \Bitrix\Im\Text::encodeEmoji($parameterInfo['PARAM_VALUE']);
			}

			if (isset($parameterInfo['PARAM_VALUE']) && mb_strlen($parameterInfo['PARAM_VALUE']) > 100)
			{
				$parameterInfo['PARAM_VALUE'] = mb_substr($parameterInfo['PARAM_VALUE'], 0, 97) . '...';
			}

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
			'id' => (int)$messageId,
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

			$arPullMessage['fromUserId'] = (int)$arFields['FROM_USER_ID'];
			$arPullMessage['toUserId'] = (int)$arFields['TO_USER_ID'];
			$arPullMessage['chatId'] = (int)$messageData['CHAT_ID'];
		}
		else
		{
			$arPullMessage['chatId'] = (int)$messageData['CHAT_ID'];
			$arPullMessage['senderId'] = (int)$messageData['AUTHOR_ID'];

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
			'extra' => \Bitrix\Im\Common::getPullExtra()
		));

		if ($messageData['MESSAGE_TYPE'] == IM_MESSAGE_OPEN || $messageData['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$messageData['CHAT_ID'], Array(
				'module_id' => 'im',
				'command' => 'messageParamsUpdate',
				'params' => $arPullMessage,
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function DeleteAll($messageId, $deleteWithTs = false)
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
			if (!$deleteWithTs && $parameterInfo['PARAM_NAME'] == 'TS')
				continue;

			IM\Model\MessageParamTable::delete($parameterInfo['ID']);
		}

		if (!$deleteWithTs)
		{
			self::UpdateTimestamp($messageId);
		}

		return true;
	}

	public static function DeleteByParam($paramName, $paramValue)
	{
		if ($paramName == '' || $paramValue == '' || $paramValue == 'TS')
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
		if ($paramName && $paramName <> '')
		{
			$filter['=PARAM_NAME'] = $paramName;
		}
		$messageParameters = IM\Model\MessageParamTable::getList(array(
			'select' => array('ID', 'MESSAGE_ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'),
			'filter' => $filter,
		));
		while($ar = $messageParameters->fetch())
		{
			if (in_array($ar["PARAM_NAME"], Array('KEYBOARD', 'MENU', 'ATTACH', 'NAME',	'IMOL_VOTE_TEXT', 'IMOL_VOTE_LIKE', 'IMOL_VOTE_DISLIKE', 'IMOL_COMMENT_HEAD')))
			{
				$ar['PARAM_VALUE'] = \Bitrix\Im\Text::decodeEmoji($ar['PARAM_VALUE']);
			}

			if ($ar["PARAM_JSON"] <> '')
			{
				try
				{
					$value = \Bitrix\Main\Web\Json::decode($ar["PARAM_JSON"]);
				}
				catch (\Bitrix\Main\SystemException $e)
				{
				}
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
		if ($paramName == '' || $paramValue == '')
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
			if (in_array($key, Array('IS_ERROR', 'IS_DELIVERED', 'IS_DELETED', 'BETA', 'IS_EDITED', 'CAN_ANSWER', 'IMOL_QUOTE_MSG', 'SENDING', 'URL_ONLY', 'LARGE_FONT', 'CRM_FORM_FILLED')))
			{
				$arValues[$key] = in_array($value[0], Array('Y', 'N'))? $value[0]: $arDefault[$key];
			}
			else if (in_array($key, Array('KEYBOARD_UID')))
			{
				$arValues[$key] = intval($value);
			}
			else if (in_array($key, Array('CALL_ID', 'CHAT_ID', 'CHAT_MESSAGE', 'IMOL_VOTE_SID', 'IMOL_VOTE_USER', 'IMOL_VOTE_HEAD', 'SENDING_TS', 'IMOL_SID')))
			{
				$arValues[$key] = intval($value[0]);
			}
			else if (in_array($key, Array('CHAT_LAST_DATE')))
			{
				if (is_object($value) && $value instanceof \Bitrix\Main\Type\DateTime)
				{
					$arValues[$key] = $value;
				}
				else if (is_object($value[0]) && $value[0] instanceof \Bitrix\Main\Type\DateTime)
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
			else if ($key == 'CHAT_USER' || $key == 'DATE_TS' || $key == 'FILE_ID' || $key == 'LIKE'  || $key == 'FAVORITE' || $key == 'KEYBOARD_ACTION' || $key == 'URL_ID' || $key == 'LINK_ACTIVE' || $key == 'USERS')
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
				else if (!is_array($value) && $value <> '')
				{
					$arValues[$key] = $value;
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'NOTIFY')
			{
				if ($value === 'N')
				{
					$arValues[$key] = $value;
				}
				else if (is_array($value))
				{
					if (empty($value) || count($value) === 1 && $value[0] === 'N')
					{
						$arValues[$key] = 'N';
					}
					else
					{
						foreach ($value as $k => $v)
						{
							$arValues[$key][$k] = intval($v);
						}
					}
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
			else if (
				$key == 'TYPE' ||
				$key == 'COMPONENT_ID' ||
				$key == 'CLASS' ||
				$key == 'IMOL_VOTE' ||
				$key == 'IMOL_VOTE_TEXT' ||
				$key == 'IMOL_VOTE_LIKE' ||
				$key == 'IMOL_VOTE_DISLIKE' ||
				$key == 'IMOL_FORM' ||
				$key == 'IMOL_COMMENT_HEAD' ||
				$key == 'IMOL_DATE_CLOSE_VOTE' ||
				$key == 'IMOL_TIME_LIMIT_VOTE' ||
				$key == 'CRM_FORM_ID' ||
				$key == 'CRM_FORM_SEC'
			)
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
					$arValues[$key] = CIMChat::GetAvatarImage($value[0], 200, false);
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
		$arDefault = [
			'TYPE' => '',
			'COMPONENT_ID' => '',
			'CODE' => '',
			'FAVORITE' => [],
			'LIKE' => [],
			'FILE_ID' => [],
			'URL_ID' => [],
			'URL_ONLY' => 'N',
			'ATTACH' => [],
			'LINK_ACTIVE' => [],
			'LARGE_FONT' => 'N',
			'NOTIFY' => 'Y',
			'MENU' => 'N',
			'KEYBOARD' => 'N',
			'KEYBOARD_UID' => 0,
			'CONNECTOR_MID' => [],
			'IS_ERROR' => 'N',
			'IS_DELIVERED' => 'Y',
			'IS_DELETED' => 'N',
			'IS_EDITED' => 'N',
			'BETA' => 'N',
			'SENDING' => 'N',
			'SENDING_TS' => 0,
			'CAN_ANSWER' => 'N',
			'IS_PINNED' => 'N',
			'CLASS' => '',
			'CALL_ID' => 0,
			'USER_ID' => '',
			'NAME' => '',
			'AVATAR' => '',
			'CHAT_ID' => 0,
			'CHAT_MESSAGE' => 0,
			'CHAT_LAST_DATE' => '',
			'CHAT_USER' => [],
			'DATE_TEXT' => [],
			'DATE_TS' => [],
			'IMOL_VOTE' => '',
			'IMOL_VOTE_TEXT' => '',
			'IMOL_VOTE_LIKE' => '',
			'IMOL_VOTE_DISLIKE' => '',
			'IMOL_VOTE_SID' => '',
			'IMOL_VOTE_USER' => '',
			'IMOL_VOTE_HEAD' => '',
			'IMOL_COMMENT_HEAD' => '',
			'IMOL_QUOTE_MSG' => 'N',
			'IMOL_SID' => 0,
			'IMOL_FORM' => '',
			'IMOL_DATE_CLOSE_VOTE' => '',
			'IMOL_TIME_LIMIT_VOTE' => '',
			'USERS' => [],
			'CRM_FORM_ID' => '',
			'CRM_FORM_SEC' => '',
			'CRM_FORM_FILLED' => 'N'
		];

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
	const FIRST_MESSAGE = 'FIRST_MESSAGE';
	const SKIP_MESSAGE = 'SKIP_MESSAGE';
	const TEXT_NODES_NAMES = ['NAME', 'LINK', 'MESSAGE', 'VALUE'];

	private $result = Array();

	function __construct($id = null, $color = null)
	{
		$this->result['ID'] = $id? $id: time();
		$this->result['BLOCKS'] = Array();
		$this->result['DESCRIPTION'] = '';

		$this->SetColor($color);
	}

	public function SetDescription($text)
	{
		$text = self::removeNewLine($text);
		$text = \Bitrix\Im\Text::convertHtmlToBbCode($text);
		$this->result['DESCRIPTION'] = trim($text);
	}

	public function SetColor($color = null)
	{
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
		if (!isset($params['NAME']) || trim($params['NAME']) == '')
			return false;

		$add['NAME'] = self::removeNewLine($params['NAME']);
		$add['AVATAR_TYPE'] = 'USER';

		if (isset($params['NETWORK_ID']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(mb_substr($params['NETWORK_ID'], 0, 1)).intval(mb_substr($params['NETWORK_ID'], 1));
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
			$add['LINK'] = $params['LINK'];
		}

		if (isset($params['AVATAR']) && preg_match('#^(?:/|https?://)#', $params['AVATAR']))
		{
			$add['AVATAR'] = $params['AVATAR'];
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
		$result = Array();

		if (isset($params['NETWORK_ID']) && isset($params['NAME']))
		{
			$result['NETWORK_ID'] = htmlspecialcharsbx(mb_substr($params['NETWORK_ID'], 0, 1)).intval(mb_substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0 && isset($params['NAME']))
		{
			$result['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0 && isset($params['NAME']))
		{
			$result['CHAT_ID'] = intval($params['CHAT_ID']);
		}
		else if (!isset($params['LINK']) || isset($params['LINK']) && !preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			return false;
		}

		if (isset($params['NAME']))
		{
			$result['NAME'] = self::removeNewLine(trim($params['NAME']));
		}
		if (isset($params['LINK']))
		{
			$result['LINK'] = $params['LINK'];
		}

		if (isset($params['DESC']))
		{
			$result['DESC'] = $params['DESC'];
		}

		if (isset($params['HTML']))
		{
			$result['HTML'] = $params['HTML'];
		}
		else if (isset($params['PREVIEW']) && preg_match('#^(?:/|https?://)#', $params['PREVIEW']))
		{
			$result['PREVIEW'] = $params['PREVIEW'];
			if (isset($params['WIDTH']) && intval($params['WIDTH']) > 0)
			{
				$result['WIDTH'] = intval($params['WIDTH']);
			}
			if (isset($params['HEIGHT']) && intval($params['HEIGHT']) > 0)
			{
				$result['HEIGHT'] = intval($params['HEIGHT']);
			}
		}

		$this->result['BLOCKS'][]['LINK'] = Array($result);

		return true;
	}

	public function AddRichLink($params)
	{
		$add = Array();

		if (isset($params['NETWORK_ID']) && isset($params['NAME']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(mb_substr($params['NETWORK_ID'], 0, 1)).intval(mb_substr($params['NETWORK_ID'], 1));
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
			$add['NAME'] = self::removeNewLine(trim($params['NAME']));
		}
		if (isset($params['LINK']))
		{
			$add['LINK'] = $params['LINK'];
		}

		if (isset($params['DESC']))
		{
			$add['DESC'] = self::removeNewLine(trim($params['DESC']));
		}

		if (isset($params['HTML']))
		{
			$add['HTML'] = self::removeNewLine(trim($params['HTML']));
		}

		if (isset($params['PREVIEW']) && preg_match('#^(?:/|https?://)#', $params['PREVIEW']))
		{
			$add['PREVIEW'] = $params['PREVIEW'];
		}
		else if (isset($params['EXTRA_IMAGE']) && preg_match('#^(?:/|https?://)#', $params['EXTRA_IMAGE']))
		{
			$add['EXTRA_IMAGE'] = $params['EXTRA_IMAGE'];
		}

		$this->result['BLOCKS'][]['RICH_LINK'] = Array($add);

		return true;
	}

	public function AddHtml($html)
	{
		if (!isset($html))
			return false;

		$html = \Bitrix\Im\Text::convertHtmlToBbCode($html);
		$this->result['BLOCKS'][]['HTML'] = trim($html);

		return true;
	}

	public function AddMessage($message, $asDescription = false)
	{
		$message = trim($message);
		if ($message == '')
			return false;

		$message = str_replace(['#BR#'], '[BR]', trim($message));

		if ($asDescription)
		{
			$this->result['DESCRIPTION'] = $message;
		}
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
				if (
					!isset($grid['NAME']) && !isset($grid['VALUE'])
					|| trim($grid['NAME']) == '' && trim($grid['VALUE']) == ''
				)
				{
					continue;
				}
			}

			if (isset($grid['DISPLAY']) && in_array($grid['DISPLAY'], Array('BLOCK', 'LINE', 'CARD', 'ROW', 'COLUMN', 'TABLE')))
			{
				if ($grid['DISPLAY'] == 'COLUMN')
				{
					$grid['DISPLAY'] = 'ROW';
				}
				if ($grid['DISPLAY'] == 'CARD')
				{
					$grid['DISPLAY'] = 'LINE';
				}
				$result['DISPLAY'] = $grid['DISPLAY'];
			}
			else
			{
				$result['DISPLAY'] = 'BLOCK';
			}

			$result['NAME'] = self::removeNewLine(trim($grid['NAME']));

			$result['VALUE'] = str_replace(['#BR#'], '[BR]', trim($grid['VALUE']));

			if (isset($grid['COLOR']) && preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $grid['COLOR']))
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
				$result['LINK'] = $grid['LINK'];
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

			if (isset($images['NAME']) && trim($images['NAME']) <> '')
			{
				$result['NAME'] = (trim($images['NAME']));
			}

			$result['LINK'] = $images['LINK'];

			if (isset($images['WIDTH']) && intval($images['WIDTH']) > 0)
			{
				$result['WIDTH'] = intval($images['WIDTH']);
			}
			if (isset($images['HEIGHT']) && intval($images['HEIGHT']) > 0)
			{
				$result['HEIGHT'] = intval($images['HEIGHT']);
			}

			if (isset($images['PREVIEW']) && preg_match('#^(?:/|https?://)#', $images['PREVIEW']))
			{
				$result['PREVIEW'] = $images['PREVIEW'];
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

			$result['LINK'] = $files['LINK'];

			if (isset($files['NAME']) && trim($files['NAME']) <> '')
			{
				$result['NAME'] = self::removeNewLine(trim($files['NAME']));
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

		if (isset($params['COLOR']) && preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $params['COLOR']))
		{
			$add['COLOR'] = $params['COLOR'];
		}

		$this->result['BLOCKS'][]['DELIMITER'] = $add;
	}

	private static function decodeBbCode($message)
	{
		return \Bitrix\Im\Text::parse($message, Array('SAFE' => 'N'));
	}

	private static function removeNewLine($text)
	{
		$text = preg_replace('/\R/'.BX_UTF_PCRE_MODIFIER, ' ', $text);
		return $text;
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

		$color = \CIMMessageParamAttach::CHAT;
		$attach = null;
		$description = '';

		if (isset($array['BLOCKS']))
		{
			$blocks = $array['BLOCKS'];

			if (isset($array['COLOR']))
			{
				$color = $array['COLOR'];
			}
			if (isset($array['DESCRIPTION']))
			{
				$description = $array['DESCRIPTION'];
			}
		}
		else
		{
			$blocks = $array;
		}

		$attach = new CIMMessageParamAttach();
		$attach->SetColor($color);
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
				if (is_array($data['MESSAGE']) && isset($data['MESSAGE']['TEXT']))
				{
					$attach->AddMessage($data['MESSAGE']['TEXT'], $data['MESSAGE']['AS_DESCRIPTION'] === 'Y');
				}
				else
				{
					$attach->AddMessage($data['MESSAGE']);
				}
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
		{
			return $attach;
		}

		$isCollection = true;
		if(\Bitrix\Main\Type\Collection::isAssociative($attach))
		{
			$isCollection = false;
			$attach = array($attach);
		}

		foreach ($attach as $attachKey => &$attachBody)
		{
			if (!is_array($attachBody))
			{
				// wrong ATTACH value like TS
				continue;
			}
			$findFirstMessage = false;
			$attachBody['DESCRIPTION'] ??= null;
			if ($attachBody['DESCRIPTION'] === self::FIRST_MESSAGE)
			{
				$attachBody['DESCRIPTION'] = '';
				$findFirstMessage = true;
			}

			if (isset($attachBody['BLOCKS']) && is_array($attachBody['BLOCKS']))
			{
				foreach ($attachBody['BLOCKS'] as &$block)
				{
					if (isset($block['HTML']))
					{
						$block['HTML'] = \Bitrix\Im\Text::convertHtmlToBbCode($block['HTML']);
					}
					else if (isset($block['MESSAGE']))
					{
						if ($findFirstMessage)
						{
							$attachBody['DESCRIPTION'] = $block['MESSAGE'];
							$findFirstMessage = false;
						}
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
		$result = \Bitrix\Im\Common::jsonEncode($this->result);
		return mb_strlen($result) < 60000? $result: "";
	}

	/**
	 * Recursively goes through attach nodes and gets all the text nodes for search indexing
	 * @param $attach
	 *
	 * @return array
	 */
	public static function GetTextForIndex($attach) : array
	{
		if($attach instanceof \CIMMessageParamAttach)
		{
			$attach = $attach->GetArray();
		}
		$textNodes = [];
		array_walk_recursive($attach, function($item, $key) use(&$textNodes){
			if(in_array($key, self::TEXT_NODES_NAMES))
			{
				$textNodes[] = $item;
			}
		});

		return $textNodes;
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

		$urls = \Bitrix\Im\V2\Entity\Url\UrlItem::getUrlsFromText($text);
		foreach ($urls as $url)
		{
			$this->prepareUrlObjects($url);
			break;
		}

		return $this->result();
	}

	private function prepareUrlObjects($url)
	{
		//$linkParam = UrlPreview\UrlPreview::getMetadataAndHtmlByUrl($url, true, false);
		$linkParam = (new IM\V2\Entity\Url\UrlItem($url))->getMetadata();
		if (empty($linkParam))
		{
			return false;
		}

		$attach = self::formatAttach($linkParam);
		if (!$attach)
		{
			return false;
		}
		$attach->SetDescription(\CIMMessageParamAttach::SKIP_MESSAGE);

		$this->attach[$linkParam['ID']] = $attach;
		$this->urlId[$linkParam['ID']] = $linkParam['ID'];

		if ($linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_STATIC)
		{
			$this->staticUrl[] = $url;

			if (mb_substr($url, -1) == '/')
			{
				$this->staticUrl[] = mb_substr($url, 0, -1);
			}
		}

		$this->result = true;

		return true;
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
								if (isset($arMessages[$messageId]['params']))
								{
									$arMessages[$messageId]['params']['ATTACH'][] = $arAttachUrl[$urlId];
								}
								else
								{
									$arMessages[$messageId]['PARAMS']['ATTACH'][] = $arAttachUrl[$urlId];
								}
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
		$typeLinkParam = $linkParam['TYPE'] ?? null;
		$extraImageLinkParam = $linkParam['EXTRA_IMAGE'] ?? null;

		if ($typeLinkParam == UrlPreview\UrlMetadataTable::TYPE_STATIC)
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
			else if ($linkParam['IMAGE'] <> '')
			{
				$linkParam['IMAGE_ID'] = $linkParam['IMAGE'];
			}
			else if (!empty($linkParam['EXTRA']['IMAGES']))
			{
				//we take only first extra image
				$linkParam['EXTRA_IMAGE'] = $linkParam['EXTRA']['IMAGES'][0];
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
				"PREVIEW" => $linkParam['IMAGE_ID'],
				"EXTRA_IMAGE" => $extraImageLinkParam,
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
