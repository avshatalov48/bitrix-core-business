<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog;

use Bitrix\Im\Configuration\Manager;
use Bitrix\Im\Configuration\Notification;
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\ExpressionField;

Loc::loadMessages(__FILE__);

class Broadcast
{
	protected const ON_CNT = 5;
	protected const OFF_CNT = 5;

	protected const ON_PERIOD = 'P7D'; // 7 days
	protected const OFF_PERIOD = 'P7D'; // 7 days

	private static function getValue(): string
	{
		return Option::get('blog', 'log_notify_all', 'N');
	}

	private static function setValue($value = false): void
	{
		$value = ($value === true);
		Option::set('blog', 'log_notify_all', ($value ? 'Y' : 'N'));
	}

	private static function getOffModeRequested(): bool
	{
		return (Option::get('blog', 'log_notify_all_off_requested', false) === 'Y');
	}

	private static function getOnModeRequested(): bool
	{
		return (Option::get('blog', 'log_notify_all_on_requested', false) === 'Y');
	}

	private static function setOffModeRequested(): void
	{
		Option::set('blog', 'log_notify_all_off_requested', 'Y');
	}

	private static function setOnModeRequested(): void
	{
		Option::set('blog', 'log_notify_all_on_requested', 'Y');
	}

	private static function getCount($period): int
	{
		$counter = 0;

		$now = new \DateTime();

		$res = PostTable::getList([
			'order' => [],
			'filter' => [
				'=PostSocnetRights:POST.ENTITY' => 'G2',
				'=PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
				'>DATE_PUBLISH' => DateTime::createFromUserTime(DateTime::createFromUserTime($now->sub(new \DateInterval($period))->format(DateTime::getFormat()))),
			],
			'group' => [],
			'select' => [ 'CNT' ],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)'),
			],
			'data_doubling' => false,
		]);
		while ($ar = $res->fetch())
		{
			$counter = (int)$ar['CNT'];
		}

		return $counter;
	}

	private static function onModeNeeded(): bool
	{
		$counter = self::getCount(self::ON_PERIOD);

		return ($counter < self::ON_CNT);
	}

	private static function offModeNeeded(): bool
	{
		$counter = self::getCount(self::OFF_PERIOD);

		return ($counter > self::OFF_CNT);
	}

	public static function getData(): array
	{
		$result = [
			'cnt' => 0,
			'rate' => 0,
		];
		$value = Option::get('blog', 'log_notify_all_data', false);
		if ($value)
		{
			$value = unserialize($value, ['allowed_classes' => false]);
			if (
				is_array($value)
				&& isset($value['cnt'], $value['rate'])
			)
			{
				$result = [
					'cnt' => (int)$value['cnt'],
					'rate' => (int)$value['rate'],
				];
			}
		}

		return $result;
	}

	public static function setRequestedMode($value): void
	{
		$value = ($value === true);

		if ($value)
		{
			self::setOnModeRequested();
		}
		else
		{
			self::setOffModeRequested();
		}
	}

	public static function checkMode(): bool
	{
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$onModeRequested = self::getOnModeRequested();
			$offModeRequested = self::getOffModeRequested();
			$mode = self::getValue();

			if (
				$onModeRequested
				&& $offModeRequested
			)
			{
				return false;
			}

			if (
				$mode === 'N'
				&& !$onModeRequested
			)
			{
				if (self::onModeNeeded())
				{
					self::sendRequest(true);
				}

			}
			elseif (
				$mode === 'Y'
				&& !$offModeRequested
			)
			{
				if (self::offModeNeeded())
				{
					self::sendRequest(false);
				}
			}
		}

		return true;
	}

	private static function sendRequest($value, $siteId = SITE_ID): void
	{
		$value = ($value === true);

		if (Loader::includeModule('im'))
		{
			$str = ($value ? 'ON' : 'OFF');
			$tag = 'BLOG|BROADCAST_REQUEST|' . ($value ? 'ON' : 'OFF');

			$fields = [
				'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
				'NOTIFY_TYPE' => IM_NOTIFY_CONFIRM,
				'NOTIFY_MODULE' => 'blog',
				'NOTIFY_EVENT' => 'log_notify_all_request',
				'NOTIFY_SUB_TAG' => $tag,
				'NOTIFY_MESSAGE' => fn (?string $languageId = null) => Loc::getMessage('BLOG_BROADCAST_REQUEST_IM_MESSAGE_' . $str, null, $languageId),
				'NOTIFY_MESSAGE_OUT' => IM_MAIL_SKIP,
				'NOTIFY_BUTTONS' => [
					[
						'TITLE' => Loc::getMessage('BLOG_BROADCAST_REQUEST_IM_BUTTON_' . $str . '_Y'),
						'VALUE' => 'Y',
						'TYPE' => 'accept',
					],
					[
						'TITLE' => Loc::getMessage('BLOG_BROADCAST_REQUEST_IM_BUTTON_' . $str . '_N'),
						'VALUE' => 'N',
						'TYPE' => 'cancel',
					],
				]
			];

			$moduleAdminList = array_keys(\Bitrix\Socialnetwork\User::getModuleAdminList([ $siteId, false ]));
			foreach ($moduleAdminList as $userId)
			{
				$fields['TO_USER_ID'] = $userId;
				$fields['NOTIFY_TAG'] = $tag . '|' . $userId;

				\CIMNotify::add($fields);
			}
		}

		self::setRequestedMode($value);
	}

	public static function send($params): bool
	{
		if (
			!Loader::includeModule('intranet')
			|| !Loader::includeModule('pull')
		)
		{
			return false;
		}

		if (
			empty($params['ENTITY_TYPE'])
			|| $params['ENTITY_TYPE'] !== 'POST'
			|| empty($params['ENTITY_ID'])
			|| empty($params['AUTHOR_ID'])
			|| empty($params['URL'])
			|| empty($params['SOCNET_RIGHTS'])
			|| !is_array($params['SOCNET_RIGHTS'])
		)
		{
			return false;
		}

		if (empty($params['SITE_ID']))
		{
			$params['SITE_ID'] = SITE_ID;
		}

		$res = Main\UserTable::getList([
			'filter' => [
				'=ID' => (int)$params['AUTHOR_ID'],
			],
			'select' => [ 'ID', 'PERSONAL_GENDER', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN' ],
		]);

		if ($author = $res->fetch())
		{
			$author['NAME_FORMATTED'] = \CUser::formatName(\CSite::getNameFormat(null, $params['SITE_ID']), $author, true);
			switch($author['PERSONAL_GENDER'])
			{
				case 'M':
					$authorSuffix = '_M';
					break;
				case 'F':
					$authorSuffix = '_F';
					break;
				default:
					$authorSuffix = '';
			}
		}
		else
		{
			return false;
		}

		$params['SOCNET_RIGHTS'] = array_map(static function ($right) {
			return ($right === 'G2' ? 'UA' : $right);
		}, $params['SOCNET_RIGHTS']);

		if (
			!empty($params['SOCNET_RIGHTS_OLD'])
			&& is_array($params['SOCNET_RIGHTS_OLD'])
		)
		{
			$rightsOld = [];
			foreach ($params['SOCNET_RIGHTS_OLD'] as $entities)
			{
				foreach ($entities as $rightsList)
				{
					foreach ($rightsList as $right)
					{
						$rightsOld[] = ($right === 'G2' ? 'UA' : $right);
					}
				}
			}
			$params['SOCNET_RIGHTS'] = array_diff($params['SOCNET_RIGHTS'], $rightsOld);
		}

		$found = false;

		$userListParams = [
			'SKIP' => (int)$params['AUTHOR_ID'],
			'DEPARTMENTS' => [],
		];

		foreach ($params['SOCNET_RIGHTS'] as $right)
		{
			if (
				$right === 'UA'
				|| $right === 'G2'
			)
			{
				$userListParams['SITE_ID'] = $params['SITE_ID'];
				$found = true;
			}
			elseif (preg_match('/^DR(\d+)$/', $right, $matches))
			{
				$userListParams['DEPARTMENTS'][] = $matches[1];
				$found = true;
			}
		}

		if ($found)
		{
			$userList = \Bitrix\Intranet\Util::getEmployeesList($userListParams);
		}

		if (empty($userList))
		{
			return false;
		}

		if (
			$params['ENTITY_TYPE'] === 'POST'
			&& ($post = \CBlogPost::getById($params['ENTITY_ID']))
			&& !empty($post['PUBLISH_STATUS'])
			&& ($post['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
		)
		{
			$titleTmp = str_replace([ "\r\n", "\n" ], ' ', $post['TITLE']);
			$title = truncateText($titleTmp, 100);
			$titleEmail = ($post['MICRO'] !== 'Y' ? truncateText($titleTmp, 255) : '');

			$titleEmpty = (trim($title, " \t\n\r\0\x0B\xA0" ) === '');

			$message = Loc::getMessage(
				'BLOG_BROADCAST_PUSH_POST' . ($titleEmpty ? 'A' : '') . $authorSuffix,
				[
					'#author#' => $author['NAME_FORMATTED'],
					'#title#' => $title,
				]
			);

			$userIdList = array_keys($userList);
			if (
				!empty($params['EXCLUDE_USERS'])
				&& is_array($params['EXCLUDE_USERS'])
			)
			{
				$userIdList = array_diff($userIdList, $params['EXCLUDE_USERS']);
			}

			if (!empty($userIdList))
			{
				$userIdListPush = self::filterRecipients($userIdList, \CIMSettings::CLIENT_PUSH);

				\Bitrix\Pull\Push::add($userIdListPush, [
					'module_id' => 'blog',
					'push' => [
						'message' => $message,
						'params' => [
							'ACTION' => 'post',
							'TAG' => 'BLOG|POST|' . $params['ENTITY_ID']
						],
						'tag' => 'BLOG|POST|' . $params['ENTITY_ID'],
						'send_immediately' => 'Y',
					]
				]);

				$offlineUserIdList = [];

				$mailRecipients = self::filterRecipients($userIdList, \CIMSettings::CLIENT_MAIL);

				$deviceInfo = \CPushManager::getDeviceInfo($mailRecipients);
				if (is_array($deviceInfo))
				{
					foreach ($deviceInfo as $userId => $info)
					{
						if (in_array(
							$info['mode'],
							[
								\CPushManager::SEND_DEFERRED,
								\CPushManager::RECORD_NOT_FOUND,
							],
							true
						))
						{
							$offlineUserIdList[] = $userId;
						}
					}
				}

				if (!empty($offlineUserIdList))
				{
					$res = Main\UserTable::getList([
						'filter' => [
							'=SEND_EMAIL' => 'Y',
							'@ID' => $offlineUserIdList,
						],
						'runtime' => [
							new Main\Entity\ExpressionField('SEND_EMAIL', 'CASE WHEN LAST_ACTIVITY_DATE IS NOT NULL AND LAST_ACTIVITY_DATE > ' . Main\Application::getConnection()->getSqlHelper()->addSecondsToDateTime('-' . (60*60*24*90)) . " THEN 'Y' ELSE 'N' END"),
						],
						'select' => [ 'ID' ],
					]);

					$offlineUserIdList = [];
					while ($ar = $res->fetch())
					{
						$offlineUserIdList[] = $ar['ID'];
					}
				}

				if (!empty($offlineUserIdList))
				{
					$serverName = '';

					$res = \CSite::getByID($params['SITE_ID']);
					if ($site = $res->fetch())
					{
						$serverName = $site['SERVER_NAME'];
					}
					if (empty($serverName))
					{
						$serverName = (
							defined('SITE_SERVER_NAME')
							&& SITE_SERVER_NAME <> ''
								? SITE_SERVER_NAME
								: Option::get('main', 'server_name', $_SERVER['SERVER_NAME'])
						);
					}

					$serverName = (\CMain::isHTTPS() ? 'https' : 'http') . '://' . $serverName;

					$textEmail = $post['DETAIL_TEXT'];
					if ($post['DETAIL_TEXT_TYPE'] === 'html')
					{
						$textEmail = HTMLToTxt($textEmail);
					}

					$imageList = [];

					$parserBlog = new \blogTextParser();
					$textEmail = $parserBlog->convert4mail($textEmail, $imageList);

					foreach ($offlineUserIdList as $userId)
					{
						if (!empty($userList[$userId]['EMAIL']))
						{
							\CEvent::send(
								'BLOG_POST_BROADCAST',
								$params['SITE_ID'],
								[
									'EMAIL_TO' => (!empty($userList[$userId]['NAME_FORMATTED']) ? '' . $userList[$userId]['NAME_FORMATTED'] . ' <' . $userList[$userId]['EMAIL'] . '>' : $userList[$userId]['EMAIL']),
									'AUTHOR' => $author['NAME_FORMATTED'],
									'MESSAGE_TITLE' => $titleEmail,
									'MESSAGE_TEXT' => $textEmail,
									'MESSAGE_PATH' => $serverName . $params['URL'],
								]
							);
						}
					}
				}
			}
		}

		return false;
	}

	public function onBeforeConfirmNotify($module, $tag, $value, $params): bool
	{
		if ($module === 'blog')
		{
			$tagList = explode('|', $tag);
			if (
				count($tagList) === 4
				&& $tagList[1] === 'BROADCAST_REQUEST'
			)
			{
				$mode = $tagList[2];
				if (
					$value === 'Y'
					&& in_array($mode, [ 'ON', 'OFF' ])
				)
				{
					self::setValue($mode === 'ON');
					\CIMNotify::deleteBySubTag('BLOG|BROADCAST_REQUEST|' . $mode);
				}

				return true;
			}
		}

		return false;
	}

	public static function filterRecipients(array $usersId, string $notifyType): array
	{
		if (!Loader::includeModule('im'))
		{
			return $usersId;
		}

		if (Manager::isSettingsMigrated())
		{
			if ($notifyType === \CIMSettings::CLIENT_MAIL)
			{
				$notifyType = Notification::MAIL;
			}

			$notification = new Notification('blog', 'broadcast_post');

			return $notification->filterAllowedUsers($usersId, $notifyType);
		}

		foreach ($usersId as $key=> $userId)
		{
			if (!\CIMSettings::getNotifyAccess(
				$userId,
				'blog',
				'broadcast_post',
				$notifyType
			))
			{
				unset($usersId[$key]);
			}
		}

		return $usersId;
	}
}
