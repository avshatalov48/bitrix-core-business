<?php
namespace Bitrix\Landing\DataLoader;

use \Bitrix\Landing\Block\Cache;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat extends \Bitrix\Landing\Source\DataLoader
{
	/**
	 * Personal chat type.
	 */
	const CHAT_TYPE_PERSONAL = 'private';

	/**
	 * Group chat type.
	 */
	const CHAT_TYPE_GROUP = 'group';

	/**
	 * Prepares params and return specific init array.
	 * @return array
	 */
	protected function getInitData(): array
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return [];
		}

		$return = [];
		$blockSave = false;
		$type = null;
		$filter = $this->getSettingsValue('additional');
		/** @var \Bitrix\Landing\Block $block */
		$block = $this->getOptionsValue('block');
		$dom = $block->getDom();

		// chat type
		if (
			isset($filter['type']) &&
			is_string($filter['type'])
		)
		{
			$type = mb_strtolower(trim($filter['type']));
		}

		// ID of user chat or local ID of group chat
		if (
			isset($filter['attributeData']) &&
			is_string($filter['attributeData']) &&
			mb_strpos($filter['attributeData'], '@')
		)
		{
			[$attrSelector, $attrCode] = explode('@', $filter['attributeData']);
			$attrCode = mb_strtolower($attrCode);
			$resultNode = $dom->querySelector($attrSelector);
			if ($resultNode)
			{
				if ($type == self::CHAT_TYPE_PERSONAL)
				{
					$return['CHAT_ID'] = (int) $resultNode->getAttribute($attrCode);
					// by default we set current user id
					if (!$return['CHAT_ID'])
					{
						$return['CHAT_ID'] = Manager::getUserId();
						$resultNode->setAttribute($attrCode, $return['CHAT_ID']);
						$blockSave = true;
					}
				}
				else if ($type == self::CHAT_TYPE_GROUP)
				{
					$return['CHAT_ID'] = trim($resultNode->getAttribute($attrCode));
					// create new one
					if (preg_match('/[^\d]+/', $return['CHAT_ID']))
					{
						$chantEntity = json_decode(htmlspecialcharsback($return['CHAT_ID']), true);
						if (is_array($chantEntity))
						{
							if (isset($chantEntity['ID']))
							{
								$chatId = $chantEntity['ID'];
								unset($chantEntity['ID']);
								$res = \Bitrix\Landing\Chat\Chat::update(
									$chatId,
									$chantEntity
								);
							}
							else
							{
								$res = \Bitrix\Landing\Chat\Chat::add(
									$chantEntity
								);
							}
							if ($res->isSuccess())
							{
								$return['CHAT_ID'] = $res->getId();
								$resultNode->setAttribute(
									$attrCode, $return['CHAT_ID']
								);
								$blockSave = true;
							}
						}
					}
					else if (!$return['CHAT_ID'])
					{
						unset($return['CHAT_ID']);
					}
					$return['CHAT_ID'] = (int)$return['CHAT_ID'];
					if ($return['CHAT_ID'])
					{
						\Bitrix\Landing\Chat\Binding::bindingBlock(
							$return['CHAT_ID'], $block->getId()
						);
					}
				}
			}
		}

		// button title we recive from attribute too
		if (
			$return &&
			isset($filter['attributeButton']) &&
			is_string($filter['attributeButton']) &&
			mb_strpos($filter['attributeButton'], '@')
		)
		{
			[$attrSelector, $attrCode] = explode('@', $filter['attributeButton']);
			$attrCode = mb_strtolower($attrCode);
			$resultNode = $dom->querySelector($attrSelector);
			if ($resultNode)
			{
				$return['SEND_TITLE'] = $resultNode->getAttribute($attrCode);
				if (!$return['SEND_TITLE'])
				{
					$return['SEND_TITLE'] = Loc::getMessage('LANDING_SUBTYPE_BUTTON_SEND');
					$resultNode->setAttribute($attrCode, $return['SEND_TITLE']);
					$blockSave = true;
				}
			}
		}

		// chat type
		if ($return && $type)
		{
			$return['TYPE'] = $type;
		}

		// save attributes to the block if necessary
		if ($blockSave)
		{
			$block->saveContent($dom->saveHTML());
			$block->save();
		}

		return $return;
	}

	/**
	 * Returns user list for chat id.
	 * @param array $initData All init data.
	 * @return array
	 */
	protected function getUserList(array $initData): array
	{
		$data = [];
		$userFilter = ['=ACTIVE' => 'Y'];
		$chatId = $initData['CHAT_ID'];
		$chatType = $initData['TYPE'];
		$sendButton = [
			'href' => '#',
			'text' => $initData['SEND_TITLE']
		];

		// for private chat chat Id = user Id
		if ($chatType == $this::CHAT_TYPE_PERSONAL)
		{
			$sendButton['href'] = '#chat' . $chatId;
			$userFilter['ID'] = $chatId;
		}
		else if ($chatType == $this::CHAT_TYPE_GROUP)
		{
			$sendButton['href'] = '#join' . $chatId;
			$userFilter['ID'] = \Bitrix\Landing\Chat\Chat::getMembersId(
				$chatId
			);
			if (!$userFilter['ID'])
			{
				$userFilter['ID'] = -1;
			}
			//{"TITLE":"Test chat 666","AVATAR":55639,"ID":24}
		}
		else
		{
			return [];
		}

		// select users
		$res = \Bitrix\Main\UserTable::getList([
			'select' => [
				'ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME',
				'WORK_POSITION', 'PERSONAL_PHOTO'
			],
			'filter' => $userFilter
		]);
		while ($user = $res->fetch())
		{
			if (Cache::isCaching())
			{
				Manager::getCacheManager()->registerTag(
					'intranet_user_' . $user['ID']
				);
			}
			$data[] = [
				'ID' => $user['ID'],
				'WORK_POSITION' => \htmlspecialcharsbx($user['WORK_POSITION']),
				'NAME' => $name = \htmlspecialcharsbx(\CUser::formatName(
					\CSite::getNameFormat(),
					$user, true, false
				)),
				'AVATAR' => [
					'src' => \CIMChat::getAvatarImage($user['PERSONAL_PHOTO']),
					'alt' => $name
				],
				'SEND' => $sendButton
			];
		}

		return $data;
	}

	/**
	 * Gets data for dynamic blocks.
	 * @return array
	 */
	public function getElementListData()
	{
		$initData = $this->getInitData();
		if (!$initData)
		{
			return [];
		}

		return $this->getUserList($initData);
	}

	/**
	 * Gets data item of dynamic blocks.
	 * @param int $element Element's key.
	 * @return array
	 */
	public function getElementData($element)
	{
		return [[]];
	}
}