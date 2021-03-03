<?php
namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class Param
{
	protected $component;
	protected $request;

	public function __construct($params)
	{
		if (!empty($params['component']))
		{
			$this->component = $params['component'];
		}

		if (!empty($params['request']))
		{
			$this->request = $params['request'];
		}
		else
		{
			$this->request = Util::getRequest();
		}
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getComponent()
	{
		return $this->component;
	}

	public function prepareDateFilterParams(&$componentParams)
	{
		$request = $this->getRequest();

		if ($request->get('flt_date_datesel') === null)
		{
			$componentParams['LOG_DATE_FROM'] = ($request->get('flt_date_from') <> '' ? trim($request->get('flt_date_from')) : '');
			$componentParams['LOG_DATE_TO'] = ($request->get('flt_date_to') <> '' ? trim($request->get('flt_date_to')) : '');
		}
		elseif ($request->get('flt_date_datesel') <> '')
		{
			$day = date('w');
			if($day == 0)
			{
				$day = 7;
			}
			switch($request->get('flt_date_datesel'))
			{
				case 'today':
					$componentParams['LOG_DATE_FROM'] = $componentParams['LOG_DATE_TO'] = convertTimeStamp();
					break;
				case 'yesterday':
					$componentParams['LOG_DATE_FROM'] = $componentParams['LOG_DATE_TO'] = convertTimeStamp(time()-86400);
					break;
				case 'week':
					$componentParams['LOG_DATE_FROM'] = convertTimeStamp(time()-($day-1)*86400);
					$componentParams['LOG_DATE_TO'] = convertTimeStamp(time()+(7-$day)*86400);
					break;
				case 'week_ago':
					$componentParams['LOG_DATE_FROM'] = convertTimeStamp(time()-($day-1+7)*86400);
					$componentParams['LOG_DATE_TO'] = convertTimeStamp(time()-($day)*86400);
					break;
				case 'month':
					$componentParams['LOG_DATE_FROM'] = convertTimeStamp(mktime(0, 0, 0, date('n'), 1));
					$componentParams['LOG_DATE_TO'] = convertTimeStamp(mktime(0, 0, 0, date('n')+1, 0));
					break;
				case 'month_ago':
					$componentParams['LOG_DATE_FROM'] = convertTimeStamp(mktime(0, 0, 0, date('n')-1, 1));
					$componentParams['LOG_DATE_TO'] = convertTimeStamp(mktime(0, 0, 0, date('n'), 0));
					break;
				case 'days':
					$componentParams['LOG_DATE_FROM'] = convertTimeStamp(time() - (int)$request->get('flt_date_days')*86400);
					$componentParams['LOG_DATE_TO'] = '';
					break;
				case 'exact':
					$componentParams['LOG_DATE_FROM'] = $componentParams['LOG_DATE_TO'] = $request->get('flt_date_from');
					break;
				case 'after':
					$componentParams['LOG_DATE_FROM'] = $request->get('flt_date_from');
					$componentParams['LOG_DATE_TO'] = '';
					break;
				case 'before':
					$componentParams['LOG_DATE_FROM'] = '';
					$componentParams['LOG_DATE_TO'] = $request->get('flt_date_to');
					break;
				case 'interval':
					$componentParams['LOG_DATE_FROM'] = $request->get('flt_date_from');
					$componentParams['LOG_DATE_TO'] = $request->get('flt_date_to');
					break;
			}
		}
		else
		{
			$componentParams['LOG_DATE_FROM'] = $componentParams['LOG_DATE_TO'] = '';
		}
	}

	public function prepareRatingParams(&$componentParams)
	{
		\CRatingsComponentsMain::getShowRating($componentParams);
		if (
			!isset($componentParams['RATING_TYPE'])
			|| $componentParams['RATING_TYPE'] == ''
		)
		{
			$componentParams['RATING_TYPE'] = Option::get('main', 'rating_vote_template', (Option::get('main', 'rating_vote_type', 'standart') === 'like'? 'like': 'standart'));
		}
		switch ($componentParams['RATING_TYPE'])
		{
			case 'like_graphic':
				$componentParams['RATING_TYPE'] = 'like';
				break;
			case 'standart':
				$componentParams['RATING_TYPE'] = 'standart_text';
				break;
			default:
		}
	}

	public function prepareRequestVarParams(&$componentParams)
	{
		Util::checkEmptyParamString($componentParams, 'USER_VAR', 'user_id');
		Util::checkEmptyParamString($componentParams, 'GROUP_VAR', 'group_id');
		Util::checkEmptyParamString($componentParams, 'PAGE_VAR', 'page');
	}

	public function prepareRequestParams(&$componentParams)
	{
		$request = $this->getRequest();

		Util::checkEmptyParamInteger($componentParams, 'GROUP_ID', 0);
		if ($componentParams['GROUP_ID'] <= 0)
		{
			if (
				!empty($request->get('TO_CODE'))
				&& !empty($request->get('TO_CODE')['SG'])
				&& is_array($request->get('TO_CODE')['SG'])
			)
			{
				preg_match('/^SG(\d+)$/', $request->get('TO_CODE')['SG'][0], $matches);
				if (!empty($matches))
				{
					$componentParams['GROUP_ID'] = $matches[1];
				}
			}
			elseif (!empty($request->get('flt_group_id')))
			{
				$componentParams['GROUP_ID'] = intval($request->get('flt_group_id'));
			}
		}

		if (empty($componentParams['DESTINATION']))
		{
			$componentParams['DESTINATION'] = [];
			if (
				!empty($request->get('TO_CODE'))
				&& is_array($request->get('TO_CODE'))
			)
			{
				foreach($request->get('TO_CODE') as $codeGroup => $codeList)
				{
					if (is_array($codeList))
					{
						foreach($codeList as $key => $code)
						{
							$componentParams['DESTINATION'][] = $code;
						}
					}
				}
			}
		}

		$componentParams['ENTITY_TYPE'] = '';
		$componentParams['TO_USER_ID'] = 0;

		if ($componentParams['GROUP_ID'] > 0)
		{
			$componentParams['ENTITY_TYPE'] = SONET_ENTITY_GROUP;
		}
		else
		{
			if (
				!empty($request->get('TO_CODE'))
				&& is_array($request->get('TO_CODE'))
				&& !empty($request->get('TO_CODE')['U'])
				&& is_array($request->get('TO_CODE')['U'])
			)
			{
				preg_match('/^U(\d+)$/', $request->get('TO_CODE')['U'][0], $matches);
				if (!empty($matches))
				{
					$componentParams['TO_USER_ID'] = intval($matches[1]);
				}
			}
			else
			{
				$componentParams['TO_USER_ID'] = intval($request->get('flt_to_user_id'));
			}
		}

		if (
			$componentParams['ENTITY_TYPE'] == ''
			&& $request->get('flt_entity_type') <> ''
		)
		{
			$componentParams['ENTITY_TYPE'] = trim($request->get('flt_entity_type'));
		}

		Util::checkEmptyParamInteger($componentParams, 'USER_ID', 0);
		if (
			$componentParams['USER_ID'] <= 0
			&& !empty($request->get('flt_user_id'))
		)
		{
			$componentParams['USER_ID'] = intval($request->get('flt_user_id'));
		}

		$componentParams['CREATED_BY_ID'] = 0;

		if (
			!empty($request->get('CREATED_BY_CODE'))
			&& is_array($request->get('CREATED_BY_CODE'))
			&& !empty($request->get('CREATED_BY_CODE')['U'])
			&& is_array($request->get('CREATED_BY_CODE')['U'])
		)
		{
			preg_match('/^U(\d+)$/', $request->get('CREATED_BY_CODE')['U'][0], $matches);
			if (!empty($matches))
			{
				$componentParams['CREATED_BY_ID'] = intval($matches[1]);
			}
		}
		elseif (!empty($request->get('flt_created_by_id')))
		{
			$createdByIdValue = $request->get('flt_created_by_id');
			if (is_array($createdByIdValue))
			{
				$createdByIdValue = $createdByIdValue[0];
			}

			if (!is_array($createdByIdValue))
			{
				if (preg_match('/^(\d+)$/', $createdByIdValue, $matches))
				{
					$componentParams['CREATED_BY_ID'] = $createdByIdValue;
				}
				else
				{
					$userList = \CSocNetUser::searchUser($createdByIdValue, false);
					if (
						is_array($userList)
						&& !empty($userList)
					)
					{
						$componentParams['CREATED_BY_ID'] = key($userList);
					}
				}
			}
		}

		$componentParams['TAG'] = ($request->get('TAG') ? trim($request->get('TAG')) : '');
		$componentParams['FIND'] = ($request->get('FIND') ? trim($request->get('FIND')) : '');
	}

	public function prepareCommentsParams(&$componentParams)
	{
		$componentParams['USE_COMMENTS'] = (
			isset($componentParams['USE_COMMENTS'])
				? $componentParams['USE_COMMENTS']
				: 'N'
		);
		Util::checkEmptyParamInteger($componentParams, 'COMMENTS_IN_EVENT', 3);
	}

	public function prepareDestinationParams(&$componentParams)
	{
		Util::checkEmptyParamInteger($componentParams, 'DESTINATION_LIMIT', 100);
		Util::checkEmptyParamInteger($componentParams, 'DESTINATION_LIMIT_SHOW', 3);
	}

	public function prepareCommentPropertyParams(&$componentParams)
	{
		$componentParams['COMMENT_PROPERTY'] = [ 'UF_SONET_COM_FILE', 'UF_SONET_COM_URL_PRV' ];
		if (
			ModuleManager::isModuleInstalled('webdav')
			|| ModuleManager::isModuleInstalled('disk')
		)
		{
			$componentParams['COMMENT_PROPERTY'][] = 'UF_SONET_COM_DOC';
		}
	}

	public function prepareDateTimeFormatParams(&$componentParams)
	{
		\CSocNetLogComponent::processDateTimeFormatParams($componentParams);
	}

	public function prepareCounterParams(&$componentParams)
	{
		$request = $this->getRequest();

		$componentParams['SET_LOG_COUNTER'] = (
			$componentParams['SHOW_UNREAD'] == 'Y'
			&& (
				empty($request->get('logajax'))
				|| $request->get('RELOAD') == 'Y'
			)
			&& (
				empty($request->get('action'))
				|| $request->get('action') != 'SBPE_get_full_form'
			)
			&& (
				empty($request->get('startVideoRecorder'))
				|| $request->get('startVideoRecorder') != 'Y'
			)
				? 'Y'
				: 'N'
		);
	}

	public function preparePageParams(&$componentParams)
	{
		$componentParams['SET_LOG_PAGE_CACHE'] = (
			$componentParams['LOG_ID'] <= 0
			&& $componentParams['MODE'] != 'LANDING'
				? 'Y'
				: 'N'
		);
	}

	public function prepareParentParams(&$componentParams)
	{
		$parentParams = $this->getComponent()->getParent()->arParams;

		Util::checkEmptyParamInteger($componentParams, 'BLOG_IMAGE_MAX_WIDTH', intval($parentParams['BLOG_IMAGE_MAX_WIDTH']));
		Util::checkEmptyParamInteger($componentParams, 'BLOG_IMAGE_MAX_HEIGHT', intval($parentParams['BLOG_IMAGE_MAX_HEIGHT']));
		Util::checkEmptyParamString($componentParams, 'BLOG_COMMENT_ALLOW_IMAGE_UPLOAD', trim($parentParams['BLOG_COMMENT_ALLOW_IMAGE_UPLOAD']));
		Util::checkEmptyParamString($componentParams, 'BLOG_ALLOW_POST_CODE', trim($parentParams['BLOG_ALLOW_POST_CODE']));
		Util::checkEmptyParamString($componentParams, 'BLOG_COMMENT_ALLOW_VIDEO', trim($parentParams['BLOG_COMMENT_ALLOW_VIDEO']));

		$componentParams['BLOG_GROUP_ID'] = intval($parentParams['BLOG_GROUP_ID']);
		$componentParams['BLOG_USE_CUT'] = (isset($parentParams['BLOG_USE_CUT']) ? trim($parentParams['BLOG_USE_CUT']) : (isset($componentParams['BLOG_USE_CUT']) ? trim($componentParams['BLOG_USE_CUT']) : ''));
		$componentParams['PHOTO_USER_IBLOCK_TYPE'] = trim($parentParams['PHOTO_USER_IBLOCK_TYPE']);
		$componentParams['PHOTO_USER_IBLOCK_ID'] = intval($parentParams['PHOTO_USER_IBLOCK_ID']);
		$componentParams['PHOTO_GROUP_IBLOCK_TYPE'] = trim($parentParams['PHOTO_GROUP_IBLOCK_TYPE']);
		$componentParams['PHOTO_GROUP_IBLOCK_ID'] = intval($parentParams['PHOTO_GROUP_IBLOCK_ID']);
		$componentParams['PHOTO_MAX_VOTE'] = intval($parentParams['PHOTO_MAX_VOTE']);
		$componentParams['PHOTO_USE_COMMENTS'] = trim($parentParams['PHOTO_USE_COMMENTS']);
		$componentParams['PHOTO_COMMENTS_TYPE'] = trim($parentParams['PHOTO_COMMENTS_TYPE']);
		$componentParams['PHOTO_FORUM_ID'] = intval($parentParams['PHOTO_FORUM_ID']);
		$componentParams['PHOTO_BLOG_URL'] = trim($parentParams['PHOTO_BLOG_URL']);
		$componentParams['PHOTO_USE_CAPTCHA'] = trim($parentParams['PHOTO_USE_CAPTCHA']);
		$componentParams['PHOTO_COUNT'] = intval($parentParams['LOG_PHOTO_COUNT']);
		$componentParams['PHOTO_THUMBNAIL_SIZE'] = intval($parentParams['LOG_PHOTO_THUMBNAIL_SIZE']);
		$componentParams['FORUM_ID'] = intval($parentParams['FORUM_ID']);
	}

	public function prepareParent2Params(&$componentParams)
	{
		$parent2Params = $this->getComponent()->getParent()->getParent()->arParams;

		Util::checkEmptyParamInteger($componentParams, 'BLOG_IMAGE_MAX_WIDTH', intval($parent2Params['BLOG_IMAGE_MAX_WIDTH']));
		Util::checkEmptyParamInteger($componentParams, 'BLOG_IMAGE_MAX_HEIGHT', intval($parent2Params['BLOG_IMAGE_MAX_HEIGHT']));
		Util::checkEmptyParamString($componentParams, 'BLOG_COMMENT_ALLOW_IMAGE_UPLOAD', trim($parent2Params['BLOG_COMMENT_ALLOW_IMAGE_UPLOAD']));
		Util::checkEmptyParamString($componentParams, 'BLOG_ALLOW_POST_CODE', trim($parent2Params['BLOG_ALLOW_POST_CODE']));
		Util::checkEmptyParamString($componentParams, 'BLOG_COMMENT_ALLOW_VIDEO', trim($parent2Params['BLOG_COMMENT_ALLOW_VIDEO']));
		Util::checkEmptyParamInteger($componentParams, 'BLOG_GROUP_ID', intval($parent2Params['BLOG_GROUP_ID']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_USER_IBLOCK_TYPE', trim($parent2Params['PHOTO_USER_IBLOCK_TYPE']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_USER_IBLOCK_ID', intval($parent2Params['PHOTO_USER_IBLOCK_ID']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_GROUP_IBLOCK_TYPE', trim($parent2Params['PHOTO_GROUP_IBLOCK_TYPE']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_GROUP_IBLOCK_ID', intval($parent2Params['PHOTO_GROUP_IBLOCK_ID']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_MAX_VOTE', intval($parent2Params['PHOTO_MAX_VOTE']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_USE_COMMENTS', trim($parent2Params['PHOTO_USE_COMMENTS']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_COMMENTS_TYPE', trim($parent2Params['PHOTO_COMMENTS_TYPE']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_FORUM_ID', intval($parent2Params['PHOTO_FORUM_ID']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_BLOG_URL', trim($parent2Params['PHOTO_BLOG_URL']));
		Util::checkEmptyParamString($componentParams, 'PHOTO_USE_CAPTCHA', trim($parent2Params['PHOTO_USE_CAPTCHA']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_COUNT', intval($parent2Params['LOG_PHOTO_COUNT']));
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_THUMBNAIL_SIZE', intval($parent2Params['LOG_PHOTO_THUMBNAIL_SIZE']));
		Util::checkEmptyParamInteger($componentParams, 'FORUM_ID', intval($parent2Params['FORUM_ID']));

		$componentParams['BLOG_USE_CUT'] = (isset($parent2Params['BLOG_USE_CUT']) ? trim($parent2Params['BLOG_USE_CUT']) : (isset($componentParams['BLOG_USE_CUT']) ? trim($componentParams['BLOG_USE_CUT']) : ''));
	}

	public function preparePageTitleParams(&$componentParams)
	{
		Util::checkEmptyParamString($componentParams, 'SET_TITLE', 'N');
		Util::checkEmptyParamString($componentParams, 'SET_NAV_CHAIN', '');
	}

	public function prepareBehaviourParams(&$componentParams)
	{
		global $USER;

		if (
			$USER->isAuthorized()
			|| $componentParams['AUTH'] === 'Y'
		)
		{
			$presetFilterId = $this->getComponent()->getPresetFilterIdValue();
			$presetFilterTopId = $this->getComponent()->getPresetFilterTopIdValue();
			$request = $this->getRequest();

			if(isset($componentParams['DISPLAY']))
			{
				$componentParams['SHOW_UNREAD'] = 'N';
				$componentParams['SHOW_REFRESH'] = 'N';
				$componentParams['SHOW_EVENT_ID_FILTER'] = 'N';

				if (
					in_array($componentParams['DISPLAY'], [ 'mine', 'forme' ])
					|| $componentParams['DISPLAY'] > 0 // ???
				)
				{
					$componentParams['SET_LOG_COUNTER'] = 'N';
					$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
				}
				elseif ($componentParams['DISPLAY'] === 'my')
				{
					$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
				}
			}

			if (empty($componentParams['DESTINATION']))
			{
				if ($componentParams['GROUP_ID'] > 0)
				{
					$componentParams['SET_LOG_PAGE_CACHE'] = 'Y';
					$componentParams['USE_FOLLOW'] = 'N';
					$componentParams['SET_LOG_COUNTER'] = 'N';
					$componentParams['SHOW_UNREAD'] = 'N';
				}
				elseif ($componentParams['TO_USER_ID'] > 0)
				{
					$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
					$componentParams['USE_FOLLOW'] = 'N';
				}
				elseif (
					$componentParams['TAG'] <> ''
					|| $componentParams['FIND'] <> ''
				)
				{
					$componentParams['SET_LOG_COUNTER'] = 'N';
					$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
					$componentParams['SHOW_UNREAD'] = 'N';
					$componentParams['USE_FOLLOW'] = 'N';
				}
			}

			if (
				(
					isset($componentParams['!EXACT_EVENT_ID'])
					&& $componentParams['!EXACT_EVENT_ID'] <> ''
				)
				|| (
					isset($componentParams['EXACT_EVENT_ID'])
					&& $componentParams['EXACT_EVENT_ID'] <> ''
				)
				|| (
					is_array($componentParams['EVENT_ID'])
					&& !in_array('all', $componentParams['EVENT_ID'])
				)
				|| (
					!is_array($componentParams['EVENT_ID'])
					&& $componentParams['EVENT_ID'] <> ''
				)
				|| $presetFilterId === 'extranet'
				|| $componentParams['CREATED_BY_ID'] > 0
				|| (
					isset($componentParams['LOG_DATE_FROM'])
					&& $componentParams['LOG_DATE_FROM'] <> ''
					&& makeTimeStamp($componentParams['LOG_DATE_FROM'], \CSite::getDateFormat('SHORT')) < time() + \CTimeZone::getOffset()
				)
				|| (
					isset($componentParams['LOG_DATE_TO'])
					&& $componentParams['LOG_DATE_TO'] <> ''
					&& makeTimeStamp($componentParams['LOG_DATE_TO'], \CSite::getDateFormat('SHORT')) < time() + \CTimeZone::getOffset()
				)
			)
			{
				$componentParams['SET_LOG_COUNTER'] = 'N';
				$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
				$componentParams['SHOW_UNREAD'] = 'N';
				$componentParams['USE_FOLLOW'] = 'N';
			}

			if ($componentParams['IS_CRM'] === 'Y')
			{
				Util::checkEmptyParamString($componentParams, 'CRM_ENTITY_TYPE', '');
				Util::checkEmptyParamInteger($componentParams, 'CRM_ENTITY_ID', 0);

				if ($componentParams['CRM_ENTITY_TYPE'] <> '')
				{
					$componentParams['SET_LOG_COUNTER'] = 'N';
					$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
					$componentParams['SHOW_UNREAD'] = 'N';
				}
				elseif ($presetFilterTopId)
				{
					$componentParams['SET_LOG_COUNTER'] = 'N';
					$componentParams['SHOW_UNREAD'] = 'N';
				}
				$componentParams['CRM_EXTENDED_MODE'] = (isset($componentParams['CRM_EXTENDED_MODE']) && $componentParams['CRM_EXTENDED_MODE'] === 'Y' ? 'Y' : 'N');
			}

			if ($componentParams['LOG_CNT'] > 0)
			{
				$componentParams['SHOW_NAV_STRING'] = 'N';
				$componentParams['SHOW_REFRESH'] = 'N';
			}

			if (
				(
					!isset($componentParams['USE_FAVORITES'])
					|| $componentParams['USE_FAVORITES'] !== 'N'
				)
				&& isset($componentParams['FAVORITES'])
				&& $componentParams['FAVORITES'] === 'Y'
			)
			{
				$componentParams['SET_LOG_COUNTER'] = 'N';
				$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
				$componentParams['SHOW_UNREAD'] = 'N';
			}

			if ((int)$request->get('pagesize') > 0)
			{
				$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
			}
		}
	}

	public function processPresetFilterParams(&$componentParams)
	{
		global $USER;

		$request = $this->getRequest();

		$presetFilterTopId = $this->getComponent()->getPresetFilterTopIdValue();
		$presetFilterId = $this->getComponent()->getPresetFilterIdValue();
		$commentsNeeded = $this->getComponent()->getCommentsNeededValue();

		if(
			$request->get('preset_filter_top_id') <> ''
			&& $request->get('preset_filter_top_id') !== 'clearall'
		)
		{
			$presetFilterTopId = $request->get('preset_filter_top_id');
		}
		elseif (
			isset($componentParams['preset_filter_top_id'])
			&& $componentParams['preset_filter_top_id'] <> ''
			&& $componentParams['preset_filter_top_id'] !== 'clearall'
		) // from nextPage ajax request
		{
			$presetFilterTopId = $componentParams['preset_filter_top_id'];
		}

		if(
			$request->get('preset_filter_id') <> ''
			&& $request->get('preset_filter_id') !== 'clearall'
		)
		{
			$presetFilterId = $request->get('preset_filter_id');
		}
		elseif (
			isset($componentParams['preset_filter_id'])
			&& $componentParams['preset_filter_id'] <> ''
			&& $componentParams['preset_filter_id'] !== 'clearall'
		) // from nextPage ajax request
		{
			$presetFilterId = $componentParams['preset_filter_id'];
		}

		$presetFiltersOptions = $presetFiltersList = false;
		if (
			$USER->isAuthorized()
			&& $componentParams['SHOW_EVENT_ID_FILTER'] !== 'N'
		)
		{
			$presetFiltersOptions = \CUserOptions::getOption('socialnetwork', '~log_filter_'.SITE_ID);
			if (!is_array($presetFiltersOptions))
			{
				$presetFiltersOptions = \CUserOptions::getOption('socialnetwork', '~log_filter');
			}
		}

		if (
			is_array($presetFiltersOptions)
			&& $componentParams['SHOW_EVENT_ID_FILTER'] !== 'N'
			&& $componentParams['IS_CRM'] !== 'Y'
		)
		{
			if($request->get('preset_filter_id') <> '')
			{
				\CUserOptions::deleteOption('socialnetwork', '~log_'.$componentParams['ENTITY_TYPE'].'_'.($componentParams['ENTITY_TYPE'] == SONET_ENTITY_GROUP ? $componentParams['GROUP_ID'] : $componentParams['USER_ID']));
			}

			$presetFiltersList = \CSocNetLogComponent::convertPresetToFilters($presetFiltersOptions, $componentParams);

			// to filter component
			$livefeedFilterHandler = new FilterHandler([
				'filterItems' => $presetFiltersList
			]);
			AddEventHandler('socialnetwork', 'OnBeforeSonetLogFilterFill', [ $livefeedFilterHandler, 'OnBeforeSonetLogFilterFill' ]);
		}

		if (
			$componentParams['IS_CRM'] === 'Y'
			&& Loader::includeModule('crm')
			&& isset($componentParams['CRM_ENTITY_TYPE'])
		)
		{
			$liveFeedFilter = new \CCrmLiveFeedFilter([ 'EntityTypeID' => \CCrmLiveFeedEntity::resolveEntityTypeID($componentParams['CRM_ENTITY_TYPE']) ]);
			AddEventHandler('socialnetwork', 'OnSonetLogFilterProcess', [ $liveFeedFilter, 'OnSonetLogFilterProcess' ]);
		}

		$presetTopFiltersList = [];
		if (!is_array($presetFiltersList))
		{
			$presetFiltersList = [];
		}

		$res = GetModuleEvents('socialnetwork', 'OnSonetLogFilterProcess');
		while ($eventFields = $res->fetch())
		{
			$eventResult = ExecuteModuleEventEx($eventFields, [ $presetFilterTopId, $presetFilterId, $presetTopFiltersList, $presetFiltersList ]);
			if (is_array($eventResult))
			{
				if (isset($eventResult['GET_COMMENTS']))
				{
					$commentsNeeded = $eventResult['GET_COMMENTS'];
				}
				if (isset($eventResult['PARAMS']) && is_array($eventResult['PARAMS']))
				{
					foreach($eventResult['PARAMS'] as $key => $value)
					{
						$componentParams[$key] = $value;
					}
				}
			}
		}

		if ($componentParams['SHOW_EVENT_ID_FILTER'] !== 'N')
		{
			$eventResult = \CSocNetLogComponent::onSonetLogFilterProcess($presetFilterTopId, $presetFilterId, $presetTopFiltersList, $presetFiltersList);
			if (is_array($eventResult))
			{
				if (isset($eventResult['GET_COMMENTS']))
				{
					$commentsNeeded = $eventResult['GET_COMMENTS'];
				}
				if (isset($eventResult['PARAMS']) && is_array($eventResult['PARAMS']))
				{
					foreach($eventResult['PARAMS'] as $key => $value)
					{
						$componentParams[$key] = $value;
					}
				}
			}
		}

		$this->getComponent()->setPresetFilterTopIdValue($presetFilterTopId);
		$this->getComponent()->setPresetFilterIdValue($presetFilterId);
		$this->getComponent()->setCommentsNeededValue($commentsNeeded);
	}

	public function prepareFollowParams(&$componentParams)
	{
		global $USER;

		if(
			(
				defined('DisableSonetLogFollow')
				&& DisableSonetLogFollow === true
			)
			|| !$USER->isAuthorized()
			|| (
				isset($componentParams['DISPLAY'])
				&& in_array($componentParams['DISPLAY'], [ 'my', 'mine', 'forme' ])
			)
		)
		{
			$componentParams['USE_FOLLOW'] = 'N';
		}
		elseif (
			!isset($componentParams['USE_FOLLOW'])
			|| $componentParams['USE_FOLLOW'] == ''
		)
		{
			$componentParams['USE_FOLLOW'] = 'Y';
		}
	}

	public function prepareModeParams(&$componentParams)
	{
		if (
			!empty($componentParams['PUBLIC_MODE'])
			&& $componentParams['PUBLIC_MODE'] === 'Y'
		)
		{
			$componentParams['MODE'] = 'PUB';
		}

		if (!empty($componentParams['MODE']))
		{
			if ($componentParams['MODE'] === 'LANDING')
			{
				$componentParams['HIDE_EDIT_FORM'] = 'Y';
				$componentParams['SHOW_RATING'] = 'N';
				$componentParams['USE_TASKS'] = 'N';
				$componentParams['SHOW_EVENT_ID_FILTER'] = 'N';
				$componentParams['USE_FAVORITES'] = 'N';
				$componentParams['SHOW_NAV_STRING'] = 'N';
				$componentParams['SET_LOG_PAGE_CACHE'] = 'N';
				$componentParams['EVENT_ID'] = 'blog_post';
			}
			elseif ($componentParams['MODE'] === 'PUB')
			{
				$componentParams['PUBLIC_MODE'] = 'Y';
			}
		}
		else
		{
			$componentParams['MODE'] = 'STANDARD';
			$componentParams['USE_TASKS'] = (ModuleManager::isModuleInstalled('tasks') ? 'Y' : 'N');
		}
	}

	public function prepareAvatarParams(&$componentParams)
	{
		Util::checkEmptyParamInteger($componentParams, 'AVATAR_SIZE_COMMON', 100);
		Util::checkEmptyParamInteger($componentParams, 'AVATAR_SIZE', 100);
		Util::checkEmptyParamInteger($componentParams, 'AVATAR_SIZE_COMMENT', 100);
	}

	public function prepareNameTemplateParams(&$componentParams)
	{
		Util::checkEmptyParamString($componentParams, 'NAME_TEMPLATE', \CSite::getNameFormat());

		$componentParams['NAME_TEMPLATE_WO_NOBR'] = str_replace(
			[ '#NOBR#', '#/NOBR#' ],
			'',
			$componentParams['NAME_TEMPLATE']
		);
		$componentParams['NAME_TEMPLATE'] = $componentParams['NAME_TEMPLATE_WO_NOBR'];
	}
}
?>