<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog\Item;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\PostTable;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\Livefeed;

Loc::loadMessages(__FILE__);

class Post
{
	public $id = false;

	private $perm;
	private $permByOpenGroup;
	private $fields;

	public function __construct()
	{
		$this->perm = Permissions::DENY;
		$this->permByOpenGroup = false;
		$this->fields = array();
		$this->id = false;
	}

	public static function getById($postId = 0, $params = array())
	{
		static $cachedFields = array();

		$postItem = false;
		$postId = intval($postId);

		$useStaticCache = (
			!empty($params['USE_STATIC_CACHE'])
			&& $params['USE_STATIC_CACHE'] === true
		);

		if ($postId > 0)
		{
			$postItem = new Post;
			$postFields = array();

			if (
				$useStaticCache
				&& isset($cachedFields[$postId])
			)
			{
				$postFields = $cachedFields[$postId];
			}
			else
			{
				$select = array('*', 'UF_BLOG_POST_URL_PRV');

				if (
					\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
					&& Main\ModuleManager::isModuleInstalled('disk')
				)
				{
					$select[] = 'UF_BLOG_POST_FILE';
				}

				if (
					Loader::includeModule('vote')
					&& Main\ModuleManager::isModuleInstalled('socialnetwork')
				)
				{
					$select[] = 'UF_BLOG_POST_VOTE';
				}

				$res = PostTable::getList(array(
					'filter' => array('=ID' => $postId),
					'select' => $select
				));
				if ($fields = $res->fetch())
				{
					$postFields = $fields;

					if ($postFields['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$postFields['DATE_CREATE'] = $postFields['DATE_CREATE']->toString();
					}
					if ($postFields['DATE_PUBLISH'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$postFields['DATE_PUBLISH'] = $postFields['DATE_PUBLISH']->toString();
					}
				}

				$cachedFields[$postId] = $postFields;
			}

			$postItem->setFields($postFields);
			if (
				isset($postFields['ID'])
				&& intval($postFields['ID']) > 0
			)
			{
				$postItem->setId(intval($postFields['ID']));
			}
		}

		return $postItem;
	}

	public function setFields($fields = array())
	{
		$this->fields = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setId($id = 0)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSonetPerms($params = array())
	{
		global $USER;

		static $cache, $userAccessCodeCache;

		if (!Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException('Socialnetwork module not installed.');
		}

		$fields = $this->getFields();
		if (
			empty($fields)
			|| empty($fields['ID'])
			|| intval($fields['ID']) <= 0
		)
		{
			throw new Main\SystemException('Empty post.');
		}

		$cacheId = md5($fields['ID'].serialize($params));

		if (!empty($cache[$cacheId]))
		{
			return $cache[$cacheId];
		}

		$currentUser = false;
		$userId = (isset($params["USER_ID"]) ? intval($params["USER_ID"]) : 0);
		if($userId <= 0)
		{
			$userId = intval($USER->getId());
			$currentUser = true;
		}

		$perms = Permissions::DENY;
		$permsAvailable = array_keys($GLOBALS["AR_BLOG_PERMS"]);

		if($currentUser)
		{
			if (\CSocNetUser::isCurrentUserModuleAdmin())
			{
				$perms = $permsAvailable[count($permsAvailable) - 1];
			}
		}
		elseif(\CSocNetUser::isUserModuleAdmin($userId))
		{
			$perms = $permsAvailable[count($permsAvailable) - 1];
		}

		if(
			isset($fields["AUTHOR_ID"])
			&& $fields["AUTHOR_ID"] == $userId
		)
		{
			$perms = Permissions::FULL;
		}

		$openedWorkgroupsList = [];
		$readByOpenSonetGroup = false;
		$alreadyFound = false;

		if ($perms <= Permissions::DENY)
		{
			$permsList = \CBlogPost::getSocNetPerms($fields['ID']);

			if (
				$userId > 0
				&& Main\ModuleManager::isModuleInstalled('mail')
			) // check for email authorization users
			{
				$select = array("ID", "EXTERNAL_AUTH_ID");
				if (Main\ModuleManager::isModuleInstalled('intranet'))
				{
					$select[] = "UF_DEPARTMENT";
				}
				$res = Main\UserTable::getList(array(
					'filter' => array(
						"=ID" => $userId
					),
					'select' => $select
				));

				if($userFields = $res->fetch())
				{
					if ($userFields["EXTERNAL_AUTH_ID"] == 'email')
					{
						$alreadyFound = true;
						$perms = (isset($permsList["U"]) && isset($permsList["U"][$userId]) ? Permissions::READ : Permissions::DENY);
					}
					elseif (
						isset($params['PUBLIC']) && $params['PUBLIC']
						&& isset($userFields["UF_DEPARTMENT"]) // intranet installed
						&& (
							!is_array($userFields["UF_DEPARTMENT"])
							|| empty($userFields["UF_DEPARTMENT"])
							|| intval($userFields["UF_DEPARTMENT"][0]) <= 0
						)
						&& Loader::includeModule('extranet')
						&& ($extranetSiteId = \CExtranet::getExtranetSiteID()) // for extranet users in public section
					)
					{
						if (isset($params['LOG_ID']) && intval($params['LOG_ID']) > 0)
						{
							$postSiteList = array();
							$res = \CSocNetLog::getSite(intval($params['LOG_ID']));
							while ($logSite = $res->fetch())
							{
								$postSiteList[] = $logSite["LID"];
							}

							if (!in_array($extranetSiteId, $postSiteList))
							{
								$alreadyFound = true;
								$perms = Permissions::DENY;
							}
						}
						else
						{
							$alreadyFound = true;
							$perms = Permissions::DENY;
						}
					}
				}
				else
				{
					$alreadyFound = true;
					$perms = Permissions::DENY;
				}
			}

			$entityList = array();

			if (!$alreadyFound)
			{
				if (!empty($userAccessCodeCache[$userId]))
				{
					$entityList = $userAccessCodeCache[$userId];
				}
				else
				{
					$codeList = \CAccess::getUserCodesArray($userId);
					foreach($codeList as $code)
					{
						if (
							preg_match('/^DR([0-9]+)/', $code, $match)
							|| preg_match('/^D([0-9]+)/', $code, $match)
							|| preg_match('/^IU([0-9]+)/', $code, $match)
						)
						{
							$entityList["DR"][$code] = $code;
						}
						elseif (preg_match('/^SG([0-9]+)_([A-Z])/', $code, $match))
						{
							$entityList["SG"][$match[1]][$match[2]] = $match[2];
						}
					}
					$userAccessCodeCache[$userId] = $entityList;
				}

				foreach($permsList as $key => $value)
				{
					foreach($value as $id => $p)
					{
						if(!is_array($p))
						{
							$p = array();
						}

						if($userId > 0 && $key == "U" && $userId == $id)
						{
							$perms = (
								in_array("US".$userId, $p) // author
									? Permissions::FULL
									: Permissions::WRITE
							);
							break;
						}

						if(in_array("G2", $p))
						{
							$perms = Permissions::WRITE;
							break;
						}

						if($userId > 0 && in_array("AU", $p))
						{
							$perms = Permissions::WRITE;
							break;
						}

						if($key == "SG")
						{
							if(!empty($entityList["SG"][$id]))
							{
								foreach($entityList["SG"][$id] as $sonetGroupId)
								{
									if(in_array("SG".$id."_".$sonetGroupId, $p))
									{
										$perms = Permissions::READ;
										break;
									}
								}
							}
						}

						if($key == "DR" && !empty($entityList["DR"]))
						{
							if(in_array("DR".$id, $entityList["DR"]))
							{
								$perms = Permissions::WRITE;
								break;
							}
						}
					}

					if($perms > Permissions::DENY)
					{
						break;
					}
				}

				if (
					$perms <= Permissions::READ
					&& !empty($permsList['SG'])
				) // check open sonet groups
				{
					foreach ($permsList['SG'] as $sonetGroupPermList)
					{
						if (empty($sonetGroupPermList))
						{
							continue;
						}

						foreach ($sonetGroupPermList as $sonetGroupPerm)
						{
							if (!preg_match('/^OSG(\d+)_'.(!$userId ? SONET_ROLES_ALL : SONET_ROLES_AUTHORIZED).'$/', $sonetGroupPerm, $matches))
							{
								continue;
							}
							$openedWorkgroupsList[] = (int)$matches[1];
						}
					}

					if (
						!empty($openedWorkgroupsList)
						&& Loader::includeModule('socialnetwork')
						&& \Bitrix\Socialnetwork\Helper\Workgroup::checkAnyOpened($openedWorkgroupsList)
					)
					{
						$perms = Permissions::READ;
						$readByOpenSonetGroup = true;
					}
				}

				if (
					isset($params['CHECK_FULL_PERMS'])
					&& $params['CHECK_FULL_PERMS']
					&& $perms < Permissions::FULL
				)
				{
					$sonetGroupIdList = Array();
					if(!empty($permsList["SG"]))
					{
						foreach($permsList["SG"] as $groupId => $val)
						{
							if(!empty($entityList["SG"][$groupId]))
							{
								$sonetGroupIdList[] = $groupId;
							}
						}
					}

					if(!empty($sonetGroupIdList))
					{
						$operationList = array("full_post", "moderate_post", "write_post", "premoderate_post");
						foreach($operationList as $operation)
						{
							if($perms > Permissions::READ)
							{
								break;
							}

							$sonetGroupPermList = \CSocNetFeaturesPerms::getOperationPerm(SONET_ENTITY_GROUP, $sonetGroupIdList, "blog", $operation);
							if(is_array($sonetGroupPermList))
							{
								foreach($sonetGroupPermList as $groupId => $role)
								{
									if (in_array($role, $entityList["SG"][$groupId]))
									{
										switch($operation)
										{
											case "full_post":
												$perms = Permissions::FULL;
												break;
											case "moderate_post":
												$perms = Permissions::MODERATE;
												break;
											case "write_post":
												$perms = Permissions::WRITE;
												break;
											case "premoderate_post":
												$perms = Permissions::PREMODERATE;
												break;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$cache[$cacheId] = $result = [
			'PERM' => $perms,
			'READ_BY_OSG' => $readByOpenSonetGroup,
		];

		return $result;
	}

	/**
	 * Detect tags in data array.
	 * @param array $fields Data array.
	 * @return array
	 */
	public function detectTags()
	{
		static $parser = null;

		$result = array();

		$fields = $this->getFields();
		$searchFields = array('DETAIL_TEXT');
		if (
			!isset($fields['MICRO'])
			|| $fields['MICRO'] != 'Y'
		)
		{
			$searchFields[] = 'TITLE';
		}

		foreach ($searchFields as $fieldCode)
		{
			if (
				empty($fieldCode)
				|| empty($fields[$fieldCode])
			)
			{
				continue;
			}

			if ($parser === null)
			{
				$parser = new \CTextParser();
			}

			$result = array_merge($result, $parser->detectTags($fields[$fieldCode]));
		}

		return array_unique($result);
	}

	public function getTags()
	{
		$result = array();

		$fields = $this->getFields();

		if (
			empty($fields)
			|| empty($fields['ID'])
			|| intval($fields['ID']) <= 0
		)
		{
			return false;
		}

		$res = \CBlogPostCategory::getList(
			array(),
			array(
				'POST_ID' => $fields['ID']
			),
			false,
			false,
			array('NAME')
		);

		while ($category = $res->fetch())
		{
			$result[] = $category['NAME'];
		}

		return $result;
	}

	/**
	 * Returns log entry Id of a blog post
	 * @return int|boolean
	 */
	public function getLogId(array $params = [])
	{
		$result = false;

		$postId = $this->getId();
		if ($postId <= 0)
		{
			return $result;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$provider = Livefeed\Provider::init(array(
			'ENTITY_TYPE' => Livefeed\Provider::DATA_ENTITY_TYPE_BLOG_POST,
			'ENTITY_ID' => $postId,
		));
		if (!$provider)
		{
			return $result;
		}

		return $provider->getLogId($params);
	}

	/**
	 * Deactivates log entry of a blog post
	 * @param int $postId
	 * @return boolean
	 */
	public function deactivateLogEntry()
	{
		$result = false;

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$logId = $this->getLogId();
		if (intval($logId) <= 0)
		{
			return $result;
		}

		LogTable::update($logId, [
			'INACTIVE' => 'Y'
		]);

		return true;
	}

	/**
	 * Activates log entry of a blog post
	 * @param int $postId
	 * @return boolean
	 */
	public function activateLogEntry()
	{
		$result = false;

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$logId = $this->getLogId([
			'inactive' => true
		]);
		if (intval($logId) <= 0)
		{
			return $result;
		}

		$currentDateTime = new \Bitrix\Main\DB\SqlExpression(\Bitrix\Main\Application::getInstance()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction());
		LogTable::update($logId, [
			'INACTIVE' => 'N',
			'LOG_DATE' => $currentDateTime,
			'LOG_UPDATE' => $currentDateTime
		]);

		return true;
	}
}
