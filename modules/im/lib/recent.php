<?php
namespace Bitrix\Im;

use Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Recent
{
	const CACHE_TOKEN_TTL = 2592000; // 1 month

	public static function get($userId = null, $options = Array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$cacheEnabled = true;
		if ($options['LAST_UPDATE'] instanceof \Bitrix\Main\Type\DateTime)
		{
			$cacheEnabled = false;
		}
		else
		{
			unset($options['LAST_UPDATE']);
		}

		$result = Array();
		$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator();

		$colorEnabled = Color::isEnabled();
		$cacheId = 'im_recent_d7_v4_'.$userId.'_'.$colorEnabled.'_'.($isOperator? 1: 0);
		$cachePath = '/bx/imc/recent'.\Bitrix\Im\Common::getCacheUserPostfix($userId);

		if ($cacheEnabled)
		{
			$cache = \Bitrix\Main\Application::getInstance()->getCache();
			$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
		}

		if($cacheEnabled && $cache->initCache(self::CACHE_TOKEN_TTL, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else
		{
			$generalChatId = \CIMChat::GetGeneralChatId();

			$select = Array(
				'*',
				'COUNTER' => 'RELATION.COUNTER',
				'MESSAGE_ID' => 'MESSAGE.ID',
				'MESSAGE_AUTHOR_ID' => 'MESSAGE.AUTHOR_ID',
				'MESSAGE_TEXT' => 'MESSAGE.MESSAGE',
				'MESSAGE_FILE' => 'FILE.ID',
				'MESSAGE_DATE' => 'MESSAGE.DATE_CREATE',
				'MESSAGE_ATTACH' => 'ATTACH.ID',
				'RELATION_USER_ID' => 'RELATION.USER_ID',
				'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
				'CHAT_ID' => 'CHAT.ID',
				'CHAT_TITLE' => 'CHAT.TITLE',
				'CHAT_TYPE' => 'CHAT.TYPE',
				'CHAT_AVATAR' => 'CHAT.AVATAR',
				'CHAT_LAST_MESSAGE_STATUS' => 'CHAT.LAST_MESSAGE_STATUS',
				'CHAT_AUTHOR_ID' => 'CHAT.AUTHOR_ID',
				'CHAT_EXTRANET' => 'CHAT.EXTRANET',
				'CHAT_COLOR' => 'CHAT.COLOR',
				'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
				'CHAT_ENTITY_ID' => 'CHAT.ENTITY_ID',
				'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
				'CHAT_ENTITY_DATA_2' => 'CHAT.ENTITY_DATA_2',
				'CHAT_ENTITY_DATA_3' => 'CHAT.ENTITY_DATA_3',
				'CHAT_DATE_CREATE' => 'CHAT.DATE_CREATE',
			);
			if ($isOperator)
			{
				$select['LINES_ID'] = 'LINES.ID';
				$select['LINES_STATUS'] = 'LINES.STATUS';
			}

			$runtime = Array(
				new \Bitrix\Main\Entity\ReferenceField(
					'ATTACH',
					'\Bitrix\Im\Model\MessageParamTable',
					array("=ref.MESSAGE_ID" => "this.ITEM_MID", "ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "ATTACH")),
					array("join_type"=>"LEFT")
				),
				new \Bitrix\Main\Entity\ReferenceField(
					'FILE',
					'\Bitrix\Im\Model\MessageParamTable',
					array("=ref.MESSAGE_ID" => "this.ITEM_MID", "ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "FILE_ID")),
					array("join_type"=>"LEFT")
				)
			);
			if ($isOperator)
			{
				$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
					'LINES',
					'\Bitrix\ImOpenlines\Model\SessionTable',
					array(">this.ITEM_OLID" => new \Bitrix\Main\DB\SqlExpression("0"), "=ref.ID" => "this.ITEM_OLID"),
					array("join_type"=>"LEFT")
				);
			}

			$filter = Array('=USER_ID' => $userId);

			if ($options['LAST_UPDATE'])
			{
				$filter['>=DATE_UPDATE'] = $options['LAST_UPDATE'];
			}
			else
			{
				$filter['>=DATE_UPDATE'] = (new \Bitrix\Main\Type\DateTime())->add('-30 days');
			}

			$orm = \Bitrix\Im\Model\RecentTable::getList(Array(
				'select' => $select,
				'filter' => $filter,
				'runtime' => $runtime,
			));
			while ($row = $orm->fetch())
			{
				$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
				$id = $isUser? (int)$row['ITEM_ID']: 'chat'.$row['ITEM_ID'];

				if ($result[$id])
				{
					continue;
				}

				if (!$isUser && (!$row['MESSAGE_ID'] || !$row['RELATION_USER_ID'] || !$row['CHAT_ID']))
				{
					continue;
				}

				$item = Array(
					'ID' => $id,
					'TYPE' => $isUser? 'user': 'chat',
					'AVATAR' => Array(),
					'TITLE' => Array(),
					'MESSAGE' => Array(
						'ID' => (int)$row['ITEM_MID'],
						'TEXT' => str_replace("\n", " ", Text::removeBbCodes($row['MESSAGE_TEXT'], $row['MESSAGE_FILE'] > 0, $row['MESSAGE_ATTACH'] > 0)),
						'FILE' => $row['MESSAGE_FILE'] > 0? true: false,
						'AUTHOR_ID' =>  (int)$row['MESSAGE_AUTHOR_ID'],
						'ATTACH' => $row['MESSAGE_ATTACH'] > 0? true: false,
						'DATE' => $row['MESSAGE_DATE'] > 0? $row['MESSAGE_DATE']: $row['DATE_UPDATE'],
						'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
					),
					'COUNTER' => (int)$row['COUNTER'],
					'PINNED' => $row['PINNED'] == 'Y',
				);

				if ($row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE)
				{
					$item['USER'] = Array(
						'ID' => (int)$row['ITEM_ID'],
					);
				}
				else
				{
					$avatar = \CIMChat::GetAvatarImage($row['CHAT_AVATAR'], 100, false);
					$color = strlen($row['CHAT_COLOR']) > 0? Color::getColor($row['CHAT_COLOR']): Color::getColorByNumber($row['ITEM_ID']);
					$chatType = \Bitrix\Im\Chat::getType($row);

					if ($generalChatId == $row['ITEM_ID'])
					{
						$row["CHAT_ENTITY_TYPE"] = 'GENERAL';
					}

					$muteList = Array();
					if ($row['RELATION_NOTIFY_BLOCK'] == 'Y')
					{
						$muteList = Array($row['RELATION_USER_ID'] => true);
					}

					$item['AVATAR'] = Array(
						'URL' => $avatar,
						'COLOR' => $color
					);
					$item['TITLE'] = $row['CHAT_TITLE'];
					$item['CHAT'] = Array(
						'ID' => (int)$row['ITEM_CID'],
						'NAME' => $row['CHAT_TITLE'],
						'OWNER' => (int)$row['CHAT_AUTHOR_ID'],
						'EXTRANET' => $row['CHAT_EXTRANET'] == 'Y',
						'AVATAR' => $avatar,
						'COLOR' => $color,
						'TYPE' => $chatType,
						'ENTITY_TYPE' => (string)$row['CHAT_ENTITY_TYPE'],
						'ENTITY_ID' => (string)$row['CHAT_ENTITY_ID'],
						'ENTITY_DATA_1' => (string)$row['CHAT_ENTITY_DATA_1'],
						'ENTITY_DATA_2' => (string)$row['CHAT_ENTITY_DATA_2'],
						'ENTITY_DATA_3' => (string)$row['CHAT_ENTITY_DATA_3'],
						'MUTE_LIST' => $muteList,
						'DATE_CREATE' => $row['CHAT_DATE_CREATE'],
						'MESSAGE_TYPE' => $row["CHAT_TYPE"],
					);
					if ($row["CHAT_ENTITY_TYPE"] == 'LINES' && $isOperator)
					{
						$item['LINES'] = Array(
							'ID' => (int)$row['LINES_ID'],
							'STATUS' => (int)$row['LINES_STATUS'],
						);
					}
					$item['USER'] = Array(
						'ID' => (int)$row['MESSAGE_AUTHOR_ID'],
					);
				}

				$result[$id] = $item;
			}

			if ($cacheEnabled)
			{
				$taggedCache->startTagCache($cachePath);
				$taggedCache->registerTag("USER_NAME");
				$taggedCache->endTagCache();

				$cache->startDataCache();
				$cache->endDataCache($result);
			}
		}

		foreach ($result as $id => $item)
		{
			if ($options['SKIP_OPENLINES'] == 'Y')
			{
				if ($item['TYPE'] == 'chat' && $item['CHAT']['TYPE'] == 'lines')
				{
					unset($result[$id]);
					continue;
				}
			}
			if ($options['SKIP_CHAT'] == 'Y')
			{
				if ($item['TYPE'] == 'chat' && $item['CHAT']['TYPE'] != 'lines')
				{
					unset($result[$id]);
					continue;
				}
			}
			if ($options['SKIP_DIALOG'] == 'Y')
			{
				if ($item['TYPE'] == 'user')
				{
					unset($result[$id]);
					continue;
				}
			}

			if ($item['USER']['ID'] > 0)
			{
				$user = User::getInstance($item['USER']['ID'])->getArray();
				if (!$user)
				{
					$user = Array('ID' => 0);
				}
				else if ($item['TYPE'] == 'user')
				{
					$item['AVATAR'] = Array(
						'URL' => $user['AVATAR'],
						'COLOR' => $user['COLOR']
					);
					$item['TITLE'] = $user['NAME'];
				}

				$item['USER'] = $user;

				if ($item['MESSAGE']['ID'] == 0)
				{
					$item['MESSAGE']['TEXT'] = $user['WORK_POSITION'];
				}

				$result[$id] = $item;
			}
		}

		$result = array_values($result);
		\Bitrix\Main\Type\Collection::sortByColumn(
			$result,
			array('MESSAGE' => SORT_DESC, 'ID' => SORT_DESC),
			Array(
				'ID' => function($row)
				{
					return $row;
				},
				'MESSAGE' => function($row)
				{
					return $row['DATE'] instanceof \Bitrix\Main\Type\DateTime? $row['DATE']->getTimeStamp(): 0;
				},
			)
		);

		if ($options['JSON'])
		{
			foreach ($result as $index => $item)
			{
				foreach ($item as $key => $value)
				{
					if ($value instanceof \Bitrix\Main\Type\DateTime)
					{
						$item[$key] = date('c', $value->getTimestamp());
					}
					else if (is_array($value))
					{
						foreach ($value as $subKey => $subValue)
						{
							if ($subValue instanceof \Bitrix\Main\Type\DateTime)
							{
								$value[$subKey] = date('c', $subValue->getTimestamp());
							}
							else if (is_string($subValue) && $subValue && in_array($subKey, Array('URL', 'AVATAR')) && strpos($subValue, 'http') !== 0)
							{
								$value[$subKey] = \Bitrix\Im\Common::getPublicDomain().$subValue;
							}
							else if (is_array($subValue))
							{
								$value[$subKey] = array_change_key_case($subValue, CASE_LOWER);
							}
						}
						$item[$key] = array_change_key_case($value, CASE_LOWER);
					}
				}
				$result[$index] = array_change_key_case($item, CASE_LOWER);
			}
		}

		return $result;
	}

	public static function getUser($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		$user = User::getInstance($userId);

		$result = Array(
			'ID' => $userId,
			'NAME' => $user->getFullName(false),
			'FIRST_NAME' => $user->getName(false),
			'LAST_NAME' => $user->getLastName(false),
			'WORK_POSITION' => $user->getWorkPosition(false),
			'COLOR' => $user->getColor(),
			'AVATAR' => $user->getAvatar(),
			'GENDER' => $user->getGender(),
			'BIRTHDAY' => (string)$user->getBirthday(),
			'EXTRANET' => $user->isExtranet(),
			'NETWORK' => $user->isNetwork(),
			'BOT' => $user->isBot(),
			'CONNECTOR' => $user->isConnector(),
			'EXTERNAL_AUTH_ID' => $user->getExternalAuthId(),
			'STATUS' => $user->getStatus(),
			'IDLE' => $user->getIdle(),
			'LAST_ACTIVITY_DATE' => $user->getLastActivityDate(),
			'MOBILE_LAST_DATE' => $user->getMobileLastDate(),
			'ABSENT' => $user->isAbsent(),
		);

		return $result;
	}

	public static function pin($dialogId, $pin, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$pin = $pin === true? 'Y': 'N';

		$id = $dialogId;
		if (substr($dialogId, 0, 4) == 'chat')
		{
			$itemTypes = \Bitrix\Im\Chat::getTypes();
			$id = substr($dialogId, 4);
		}
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
		}

		$element = \Bitrix\Im\Model\RecentTable::getList(Array(
			'select' => Array('USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'PINNED'),
			'filter' => Array(
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $itemTypes,
				'=ITEM_ID' => $id
			)
		))->fetch();
		if (!$element)
		{
			return false;
		}
		if ($element['PINNED'] == $pin)
		{
			return true;
		}

		\Bitrix\Im\Model\RecentTable::update(Array(
			'USER_ID' => $element['USER_ID'],
			'ITEM_TYPE' => $element['ITEM_TYPE'],
			'ITEM_ID' => $element['ITEM_ID'],
		), array(
			'PINNED' => $pin,
			'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime()
		));

		self::clearCache($element['USER_ID']);

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");
		if ($pullInclude)
		{
			\Bitrix\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'command' => 'chatPin',
				'expiry' => 3600,
				'params' => Array(
					'dialogId' => $dialogId,
					'active' => $pin == 'Y'
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function hide($dialogId, $userId = null)
	{
		return \CIMContactList::DialogHide($dialogId, $userId);
	}

	/**
	 * @param $dialogId
	 * @param null $userId
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function show($dialogId, $userId = null)
	{
		$result = false;
		$userId = Common::getUserId($userId);

		if ($userId)
		{
			$relation = Chat::makeRelationShow($dialogId, $userId);

			if (!empty($relation['ID']))
			{
				$recent = Array(
					'ENTITY_ID' => $dialogId,
					'MESSAGE_ID' => $relation['UNREAD_ID'],
					'CHAT_TYPE' => $relation['MESSAGE_TYPE'],
					'USER_ID' => $userId,
					'CHAT_ID' => $dialogId,
					'RELATION_ID' => $relation['ID']
				);

				if ($relation['PARAMS']['SESSION_ID'])
				{
					$recent['SESSION_ID'] = $relation['PARAMS']['SESSION_ID'];
				}

				$result = \CIMContactList::SetRecent($recent);

				$pullInclude = \Bitrix\Main\Loader::includeModule("pull");

				if ($pullInclude)
				{
					$chat = \CIMChat::GetChatData(
						array(
							'ID' =>$dialogId,
							'USE_CACHE' => 'N',
						)
					);

					$imMessage = new \CIMMessage($userId);
					$message = $imMessage->GetMessage($relation['LAST_ID']);

					if (!empty($chat))
					{
						$pullParams = Array(
							'module_id' => 'im',
							'command' => 'chatShow',
							'params' => \CIMMessage::GetFormatMessage(
								Array(
									 'ID' => $relation['LAST_ID'],
									 'CHAT_ID' => $dialogId,
									 'TO_CHAT_ID' => $dialogId,
									 'FROM_USER_ID' => $message['AUTHOR_ID'],
									 'SYSTEM' => 'Y',
									 'MESSAGE' => $message['MESSAGE'],
									 'DATE_CREATE' => time(),
									 //'PARAMS' => self::PrepareParamsForPull($arFields['PARAMS']),
									 //'FILES' => $arFields['FILES'],
									 'NOTIFY' => true,
									 'COUNTER' => 1
								 )
							),
							'extra' => \Bitrix\Im\Common::getPullExtra()
						);
						$result = \Bitrix\Pull\Event::add($userId, $pullParams);
					}
				}
			}
		}

		return $result;
	}

	public static function clearCache($userId = null)
	{
		$cache = Application::getInstance()->getCache();
		$cache->cleanDir('/bx/imc/recent'.($userId? Common::getCacheUserPostfix($userId): ''));
	}
}