<?php
namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);

class LogEntry extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	/** @var ErrorCollection errorCollection */
	protected $errorCollection;

	public function configureActions()
	{
		return [];
	}

	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	public static function getUserFieldsFMetaData()
	{
		global $USER_FIELD_MANAGER;
		static $arUFMeta;
		if (!$arUFMeta)
		{
			$arUFMeta = $USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT", 0, LANGUAGE_ID);
		}
		return $arUFMeta;
	}

	public static function getCommentsFullList(array $eventData, array &$params, array $options = [])
	{
		global $CACHE_MANAGER;

		$nTopCount = (isset($options['nTopCount']) && (int)$options['nTopCount'] > 0 ? (int)$options['nTopCount'] : 20);
		$timeZoneOffzet = (isset($options['timeZoneOffzet']) && (int)$options['timeZoneOffzet'] > 0 ? (int)$options['timeZoneOffzet'] : 0);
		$commentEvent = (isset($options['commentEvent']) && is_array($options['commentEvent']) ? $options['commentEvent'] : []);
		$commentProvider = (isset($options['commentProvider']) ? $options['commentProvider'] : false);

		$cacheTime = 31536000;

		if ($params['COMMENT_ID'] <= 0)
		{
			$cache = new \CPHPCache;
		}

		$cacheIdPartsList = [];
		$keysList = [
			'AVATAR_SIZE_COMMENT',
			'NAME_TEMPLATE',
			'NAME_TEMPLATE_WO_NOBR',
			'SHOW_LOGIN',
			'DATE_TIME_FORMAT',
			'PATH_TO_USER',
			'PATH_TO_GROUP',
			'PATH_TO_CONPANY_DEPARTMENT'
		];

		foreach($keysList as $paramKey)
		{
			$cacheIdPartsList[$paramKey] = (
				array_key_exists($paramKey, $params)
					? $params[$paramKey]
					: false
			);
		}

		$cacheId = 'log_comments_'.$params['LOG_ID'].'_'.md5(serialize($cacheIdPartsList)).'_'.SITE_TEMPLATE_ID.'_'.SITE_ID.'_'.LANGUAGE_ID.'_'.FORMAT_DATETIME.'_'.$timeZoneOffzet.'_'.$nTopCount;
		$cachePath = '/sonet/log/'.(int)((int)$params['LOG_ID'] / 1000)."/".$params['LOG_ID'].'/comments/';

		$result = [];

		if (
			is_object($cache)
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
		)
		{
			$cacheVariables = $cache->getVars();
			$result = $cacheVariables['COMMENTS_FULL_LIST'];

			if (!empty($cacheVariables['Assets']))
			{
				if (!empty($cacheVariables['Assets']['CSS']))
				{
					foreach($cacheVariables['Assets']['CSS'] as $cssFile)
					{
						Asset::getInstance()->addCss($cssFile);
					}
				}

				if (!empty($cacheVariables['Assets']['JS']))
				{
					foreach($cacheVariables["Assets"]['JS'] as $jsFile)
					{
						Asset::getInstance()->addJs($jsFile);
					}
				}
			}
		}
		else
		{
			if (is_object($cache))
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
			}

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->startTagCache($cachePath);
			}

			$filter = [
				'LOG_ID' => $params['LOG_ID']
			];

			if ($params['COMMENT_ID'] > 0)
			{
				$logCommentId = $params['COMMENT_ID'];
				if (!empty($commentEvent))
				{
					$res = \CSocNetLogComments::getList(
						[],
						[
							'EVENT_ID' => $commentEvent['EVENT_ID'],
							'SOURCE_ID' => $params['COMMENT_ID']
						],
						false,
						false,
						[ 'ID' ]
					);

					if ($logCommentFields = $res->fetch())
					{
						$logCommentId = $logCommentFields['ID'];
					}
				}

				$filter['>=ID'] = $logCommentId;
			}

			$select = [
				'ID', 'LOG_ID', 'SOURCE_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'USER_ID', 'EVENT_ID', 'LOG_DATE', 'MESSAGE', 'LOG_DATE_TS', 'TEXT_MESSAGE', 'URL', 'MODULE_ID',
				'GROUP_NAME', 'GROUP_OWNER_ID', 'GROUP_VISIBLE', 'GROUP_OPENED', 'GROUP_IMAGE_ID',
				'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO', 'USER_PERSONAL_GENDER',
				'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'CREATED_BY_SECOND_NAME', 'CREATED_BY_LOGIN', 'CREATED_BY_PERSONAL_PHOTO', 'CREATED_BY_PERSONAL_GENDER', 'CREATED_BY_EXTERNAL_AUTH_ID',
				'SHARE_DEST',
				'LOG_SITE_ID', 'LOG_SOURCE_ID',
				'RATING_TYPE_ID', 'RATING_ENTITY_ID',
				'UF_*'
			];

			$listParams = [
				'USE_SUBSCRIBE' => 'N',
				'CHECK_RIGHTS' => 'N'
			];

			$usetFieldsMetaData = self::getUserFieldsFMetaData();

			$navParams = (
				$params['COMMENT_ID'] <= 0
					? [ 'nTopCount' => $nTopCount ]
					: false
			);

			$assets = [
				'CSS' => [],
				'JS' => []
			];

			$res = \CSocNetLogComments::getList(
				[ 'LOG_DATE' => 'DESC', 'ID' => 'DESC' ], // revert then
				$filter,
				false,
				$navParams,
				$select,
				$listParams
			);

			if (
				!empty($eventData['EVENT_FORMATTED'])
				&& !empty($eventData['EVENT_FORMATTED']['DESTINATION'])
				&& is_array($eventData['EVENT_FORMATTED']['DESTINATION'])
			)
			{
				foreach($eventData['EVENT_FORMATTED']['DESTINATION'] as $destination)
				{
					if (!empty($destination['CRM_USER_ID']))
					{
						$params['ENTRY_HAS_CRM_USER'] = true;
						break;
					}
				}
			}

			$commentsList = $commentSourceIdList = [];
			while ($commentFields = $res->getNext())
			{
				if (!empty($commentFields['SHARE_DEST']))
				{
					$commentFields['SHARE_DEST'] = htmlspecialcharsback($commentFields['SHARE_DEST']);
				}

				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->registerTag('USER_NAME_'.(int)$commentFields['USER_ID']);
				}

				$commentFields['UF'] = $usetFieldsMetaData;
				foreach ($usetFieldsMetaData as $fieldName => $userFieldData)
				{
					if (array_key_exists($fieldName, $commentFields))
					{
						$commentFields['UF'][$fieldName]['VALUE'] = $commentFields[$fieldName];
						$commentFields["UF"][$fieldName]['ENTITY_VALUE_ID'] = $commentFields['ID'];
					}
				}
				$commentsList[] = $commentFields;
				if ((int)$commentFields['SOURCE_ID'] > 0)
				{
					$commentSourceIdList[] = (int)$commentFields['SOURCE_ID'];
				}
			}

			if (
				!empty($commentSourceIdList)
				&& !empty($commentProvider)
			)
			{
				$sourceAdditonalData = $commentProvider->getAdditionalData([
					'id' => $commentSourceIdList
				]);

				if (!empty($sourceAdditonalData))
				{
					foreach($commentsList as $key => $comment)
					{
						if (
							!empty($comment['SOURCE_ID'])
							&& isset($sourceAdditonalData[$comment['SOURCE_ID']])
						)
						{
							$commentsList[$key]['ADDITIONAL_DATA'] = $sourceAdditonalData[$comment['SOURCE_ID']];
						}
					}
				}
			}

			foreach($commentsList as $commentFields)
			{
				$result[] = self::getLogCommentRecord($commentFields, $params, $assets);
			}

			if (is_object($cache))
			{
				$cacheData = [
					'COMMENTS_FULL_LIST' => $result,
					'Assets' => $assets
				];
				$cache->endDataCache($cacheData);
				if(defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->endTagCache();
				}
			}
		}

		return $result;
	}

	public static function getLogCommentRecord(array $comment, array $params, array &$assets)
	{
		global $APPLICATION, $arExtranetUserID;

		$extranetUserIdList = $arExtranetUserID;

		static $userCache = array();

		// for the same post log_update - time only, if not - date and time
		$timestamp = makeTimeStamp(array_key_exists('LOG_DATE_FORMAT', $comment)
			? $comment['LOG_DATE_FORMAT']
			: $comment['LOG_DATE']
		);

		$timeFormated = formatDateFromDB($comment['LOG_DATE'],
			(
				mb_stripos($params['DATE_TIME_FORMAT'], 'a')
				|| (
					$params['DATE_TIME_FORMAT'] === 'FULL'
					&& isAmPmMode()
				) !== false
					? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'G:MI TT' : 'G:MI T')
					: 'HH:MI'
			)
		);

		$dateTimeFormated = formatDate(
			(!empty($params['DATE_TIME_FORMAT'])
				? ($params['DATE_TIME_FORMAT'] === 'FULL'
					? \CDatabase::dateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME))
					: $params['DATE_TIME_FORMAT']
				)
				: \CDatabase::dateFormatToPHP(FORMAT_DATETIME)
			),
			$timestamp
		);

		if (
			strcasecmp(LANGUAGE_ID, 'EN') !== 0
			&& strcasecmp(LANGUAGE_ID, 'DE') !== 0
		)
		{
			$dateTimeFormated = toLower($dateTimeFormated);
		}

		// strip current year
		if (
			!empty($params['DATE_TIME_FORMAT'])
			&& (
				$params['DATE_TIME_FORMAT'] === 'j F Y G:i'
				|| $params['DATE_TIME_FORMAT'] === 'j F Y g:i a'
			)
		)
		{
			$dateTimeFormated = ltrim($dateTimeFormated, '0');
			$currentYear = date('Y');
			$dateTimeFormated = str_replace(array('-'.$currentYear, '/'.$currentYear, ' '.$currentYear, '.'.$currentYear), '', $dateTimeFormated);
		}

		$path2Entity = (
			$comment["ENTITY_TYPE"] == SONET_ENTITY_GROUP
				? \CComponentEngine::MakePathFromTemplate($params['PATH_TO_GROUP'], [ 'group_id' => $comment['ENTITY_ID'] ])
				: \CComponentEngine::MakePathFromTemplate($params['PATH_TO_USER'], [ 'user_id' => $comment['ENTITY_ID'] ])
		);

		if ((int)$comment['USER_ID'] > 0)
		{
			$suffix = (
				is_array($extranetUserIdList)
				&& in_array($comment['USER_ID'], $extranetUserIdList)
					? Loc::getMessage('SONET_LOG_EXTRANET_SUFFIX')
					: ""
			);

			$userFields = [
				'NAME' => $comment['~CREATED_BY_NAME'],
				'LAST_NAME' => $comment['~CREATED_BY_LAST_NAME'],
				'SECOND_NAME' => $comment['~CREATED_BY_SECOND_NAME'],
				'LOGIN' => $comment['~CREATED_BY_LOGIN']
			];
			$useLogin = ($params["SHOW_LOGIN"] !== "N");
			$createdByFields = [
				'FORMATTED' => \CUser::formatName($params['NAME_TEMPLATE'], $userFields, $useLogin).$suffix,
				'URL' => \CComponentEngine::makePathFromTemplate($params['PATH_TO_USER'], [
					'user_id' => $comment['USER_ID'],
					'id' => $comment['USER_ID']
				])
			];

			$createdByFields['TOOLTIP_FIELDS'] = [
				'ID' => $comment['USER_ID'],
				'NAME' => $comment['~CREATED_BY_NAME'],
				'LAST_NAME' => $comment['~CREATED_BY_LAST_NAME'],
				'SECOND_NAME' => $comment['~CREATED_BY_SECOND_NAME'],
				'LOGIN' => $comment['~CREATED_BY_LOGIN'],
				'PERSONAL_GENDER' => $comment['~CREATED_BY_PERSONAL_GENDER'],
				'USE_THUMBNAIL_LIST' => 'N',
				'PATH_TO_SONET_MESSAGES_CHAT' => $params['PATH_TO_MESSAGES_CHAT'],
				'PATH_TO_SONET_USER_PROFILE' => $params['PATH_TO_USER'],
				'PATH_TO_VIDEO_CALL' => $params['PATH_TO_VIDEO_CALL'],
				'DATE_TIME_FORMAT' => $params['DATE_TIME_FORMAT'],
				'SHOW_YEAR' => $params['SHOW_YEAR'],
				'CACHE_TYPE' => $params['CACHE_TYPE'],
				'CACHE_TIME' => $params['CACHE_TIME'],
				'NAME_TEMPLATE' => $params['NAME_TEMPLATE'].$suffix,
				'SHOW_LOGIN' => $params['SHOW_LOGIN'],
				'PATH_TO_CONPANY_DEPARTMENT' => $params['PATH_TO_CONPANY_DEPARTMENT'],
				'INLINE' => 'Y',
				'EXTERNAL_AUTH_ID' => $comment['~CREATED_BY_EXTERNAL_AUTH_ID']
			];
			if (
				isset($params['ENTRY_HAS_CRM_USER'])
				&& $params['ENTRY_HAS_CRM_USER']
				&& ModuleManager::isModuleInstalled('crm')
			)
			{
				$userFields = [];

				if (isset($userCache[$comment['USER_ID']]))
				{
					$userFields = $userCache[$comment['USER_ID']];
				}
				else
				{
					$res = UserTable::getList([
						'filter' => [
							'ID' => (int)$comment['USER_ID']
						],
						'select' => [ 'ID', 'UF_USER_CRM_ENTITY' ]
					]);
					if ($userFields = $res->fetch())
					{
						$userCache[$userFields['ID']] = $userFields;
					}
				}

				if (!empty($userFields))
				{
					$createdByFields['TOOLTIP_FIELDS'] = array_merge($createdByFields['TOOLTIP_FIELDS'], $userFields);
				}
			}
		}
		else
		{
			$createdByFields = [
				'FORMATTED' => Loc::getMessage("SONET_C73_CREATED_BY_ANONYMOUS")
			];
		}

		$userFields = [
			'NAME' => $comment['~USER_NAME'],
			'LAST_NAME' => $comment['~USER_LAST_NAME'],
			'SECOND_NAME' => $comment['~USER_SECOND_NAME'],
			'LOGIN' => $comment['~USER_LOGIN']
		];

		$temporaryParams = $params;
		$temporaryParams['AVATAR_SIZE'] = (isset($params['AVATAR_SIZE_COMMON']) ? $params['AVATAR_SIZE_COMMON'] : $params['AVATAR_SIZE']);

		$commentEventFields = [
			'EVENT' => $comment,
			'LOG_DATE' => $comment['LOG_DATE'],
			'LOG_DATE_TS' => makeTimeStamp($comment['LOG_DATE']),
			'LOG_DATE_DAY' => convertTimeStamp(makeTimeStamp($comment['LOG_DATE']), 'SHORT'),
			'LOG_TIME_FORMAT' => $timeFormated,
			'LOG_DATETIME_FORMAT' => $dateTimeFormated,
			'TITLE_TEMPLATE' => '',
			'TITLE' => '',
			'TITLE_FORMAT' => '', // need to use url here
			'ENTITY_NAME' => (
				$comment["ENTITY_TYPE"] === SONET_ENTITY_GROUP
					? $comment["GROUP_NAME"]
					: \CUser::formatName($params['NAME_TEMPLATE'], $userFields, $useLogin)
			),
			'ENTITY_PATH' => $path2Entity,
			'CREATED_BY' => $createdByFields,
			'AVATAR_SRC' => \CSocNetLogTools::formatEvent_CreateAvatar($comment, $temporaryParams)
		];

		$commentEventData = \CSocNetLogTools::findLogCommentEventByID($comment['EVENT_ID']);
		$formattedFields = [];

		if (
			is_array($commentEventData)
			&& array_key_exists('CLASS_FORMAT', $commentEventData)
			&& array_key_exists('METHOD_FORMAT', $commentEventData)
		)
		{
			$logFields = (
				$params['USER_COMMENTS'] === "Y"
					? []
					: [
						'TITLE' => $comment['~LOG_TITLE'],
						'URL' => $comment['~LOG_URL'],
						'PARAMS' => $comment['~LOG_PARAMS']
					]
			);

			$formattedFields = call_user_func([ $commentEventData['CLASS_FORMAT'], $commentEventData['METHOD_FORMAT'] ], $comment, $params, false, $logFields);

			if ($params['USE_COMMENTS'] !== 'Y')
			{
				if (
					array_key_exists('CREATED_BY', $formattedFields)
					&& isset($formattedFields['CREATED_BY']['TOOLTIP_FIELDS'])
				)
				{
					$commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'] = $formattedFields['CREATED_BY']['TOOLTIP_FIELDS'];
				}
			}
		}

		$commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
			[
				'POST_TEXT' => $comment['MESSAGE'],
				'SHARE_DEST' => $comment['SHARE_DEST'],
				'SOURCE_ID' => (int)$comment['SOURCE_ID'],
				'EVENT_ID' => $comment['EVENT_ID'],
				'RATING_TYPE_ID' => $comment['RATING_TYPE_ID'],
			],
			[
				'eventId' => $comment['EVENT_ID']
			]
		);

		if ($commentAuxProvider)
		{
			$commentAuxProvider->setOptions(array(
				'suffix' => (!empty($params['COMMENT_ENTITY_SUFFIX']) ? $params['COMMENT_ENTITY_SUFFIX'] : ''),
				'logId' => $comment['LOG_ID'],
				'cache' => true
			));

			$formattedFields["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] = nl2br($commentAuxProvider->getText());
		}
		else
		{
			$message = (
				is_array($formattedFields)
				&& array_key_exists('EVENT_FORMATTED', $formattedFields)
				&& array_key_exists('MESSAGE', $formattedFields['EVENT_FORMATTED'])
					? $formattedFields['EVENT_FORMATTED']['MESSAGE']
					: $commentEventFields['EVENT']['MESSAGE']
			);

			if ($message <> '')
			{
				$formattedFields['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] = \CSocNetTextParser::closetags(htmlspecialcharsback($message));
			}
		}

		if (is_array($commentEventFields))
		{
			$formattedFields['EVENT_FORMATTED']['DATETIME'] = (
				$commentEventFields['LOG_DATE_DAY'] == convertTimeStamp()
					? $timeFormated
					: $dateTimeFormated
			);
			$commentEventFields['EVENT_FORMATTED'] = $formattedFields['EVENT_FORMATTED'];
			$commentEventFields['EVENT_FORMATTED']['URLPREVIEW'] = false;

			if (
				isset($comment['UF']['UF_SONET_COM_URL_PRV'])
				&& !empty($comment['UF']['UF_SONET_COM_URL_PRV']['VALUE'])
			)
			{
				$css = $APPLICATION->sPath2css;
				$js = $APPLICATION->arHeadScripts;

				$urlPreviewText = \Bitrix\Socialnetwork\ComponentHelper::getUrlPreviewContent($comment['UF']['UF_SONET_COM_URL_PRV'], array(
					'MOBILE' => 'N',
					'NAME_TEMPLATE' => $params['NAME_TEMPLATE'],
					'PATH_TO_USER' => $params['~PATH_TO_USER']
				));

				if (!empty($urlPreviewText))
				{
					$commentEventFields['EVENT_FORMATTED']['URLPREVIEW'] = true;
					$commentEventFields['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] .= $urlPreviewText;
				}

				$assets['CSS'] = array_merge($assets['CSS'], array_diff($APPLICATION->sPath2css, $css));
				$assets['JS'] = array_merge($assets['JS'], array_diff($APPLICATION->arHeadScripts, $js));

				unset($comment['UF']['UF_SONET_COM_URL_PRV']);
			}

			$commentEventFields['UF'] = $comment['UF'];

			if (
				isset($commentEventFields['EVENT_FORMATTED'])
				&& is_array($commentEventFields['EVENT_FORMATTED'])
			)
			{
				$fields2Cache = [
					'DATETIME',
					'MESSAGE',
					'FULL_MESSAGE_CUT',
					'ERROR_MSG',
					'URLPREVIEW',
				];
				foreach ($commentEventFields['EVENT_FORMATTED'] as $field => $value)
				{
					if (!in_array($field, $fields2Cache))
					{
						unset($commentEventFields['EVENT_FORMATTED'][$field]);
					}
				}
			}

			if (
				isset($commentEventFields['EVENT'])
				&& is_array($commentEventFields['EVENT'])
			)
			{
				if (!empty($commentEventFields["EVENT"]["URL"]))
				{
					$commentEventFields['EVENT']['URL'] = str_replace(
						'#GROUPS_PATH#',
						Option::get('socialnetwork', 'workgroups_page', '/workgroups/', SITE_ID),
						$commentEventFields['EVENT']['URL']
					);
				}

				$fields2Cache = [
					'ID',
					'SOURCE_ID',
					'EVENT_ID',
					'USER_ID',
					'LOG_DATE',
					'RATING_TYPE_ID',
					'RATING_ENTITY_ID',
					'URL',
					'SHARE_DEST'
				];

				if (
					isset($params['MAIL'])
					&& $params['MAIL'] === 'Y'
				)
				{
					$fields2Cache[] = 'MESSAGE';
				}

				foreach ($commentEventFields['EVENT'] as $field => $value)
				{
					if (!in_array($field, $fields2Cache))
					{
						unset($commentEventFields['EVENT'][$field]);
					}
				}
			}

			if (
				isset($commentEventFields['CREATED_BY'])
				&& is_array($commentEventFields['CREATED_BY'])
			)
			{
				$fields2Cache = [
					'TOOLTIP_FIELDS',
					'FORMATTED',
					'URL'
				];
				foreach ($commentEventFields['CREATED_BY'] as $field => $value)
				{
					if (!in_array($field, $fields2Cache))
					{
						unset($commentEventFields['CREATED_BY'][$field]);
					}
				}

				if (
					isset($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'])
					&& is_array($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'])
				)
				{
					$fields2Cache = [
						'ID',
						'PATH_TO_SONET_USER_PROFILE',
						'NAME',
						'LAST_NAME',
						'SECOND_NAME',
						'PERSONAL_GENDER',
						'LOGIN',
						'EMAIL',
						'EXTERNAL_AUTH_ID',
						'UF_USER_CRM_ENTITY',
						'UF_DEPARTMENT'
					];
					foreach ($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'] as $field => $value)
					{
						if (!in_array($field, $fields2Cache))
						{
							unset($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'][$field]);
						}
					}
				}
			}
		}

		foreach($commentEventFields['EVENT'] as $key => $value)
		{
			if (mb_strpos($key, '~') === 0)
			{
				unset($commentEventFields['EVENT'][$key]);
			}
		}

		return $commentEventFields;
	}
}

?>