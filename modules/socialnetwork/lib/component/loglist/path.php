<?php

namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Main\Config\Option;

class Path
{
	public $component = null;
	public $request = null;

	protected $folderUsers = '';
	protected $folderWorkgroups = '';
	protected $pathToUserBlogPost = '';
	protected $pathToLogEntry = '';
	protected $pathToMessagesChat = '';
	protected $pathToVideoCall = '';
	protected $pathToSmile = '';

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

	public function setFolderUsersValue($value = ''): void
	{
		$this->folderUsers = $value;
	}
	public function getFolderUsersValue(): string
	{
		return $this->folderUsers;
	}

	public function setFolderWorkgroupsValue($value = ''): void
	{
		$this->folderWorkgroups = $value;
	}
	public function getFolderWorkgroupsValue(): string
	{
		return $this->folderWorkgroups;
	}

	public function preparePathParams(&$componentParams): array
	{
		$result = [];

		$extranetSite = $this->getComponent()->getExtranetSiteValue();

		$result['folderUsers'] = Option::get('socialnetwork', 'user_page', false, SITE_ID);
		$result['folderUsers'] = ($result['folderUsers'] ?: ($extranetSite ? SITE_DIR.'contacts/personal/' : SITE_DIR.'company/personal/'));

		$result['folderWorkgroups'] = Option::get('socialnetwork', 'workgroups_page', false, SITE_ID);
		$result['folderWorkgroups'] = ($result['folderWorkgroups'] ?: SITE_DIR.'workgroups/');

		$result['pathToUserBlogPost'] = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page');
		$result['pathToUserBlogPost'] = ($result['pathToUserBlogPost'] ?: $result['folderUsers'].'user/#user_id#/blog/#post_id#/');

		$result['pathToLogEntry'] = Option::get('socialnetwork', 'log_entry_page', false, SITE_ID);
		$result['pathToLogEntry'] = ($result['pathToLogEntry'] ?: $result['folderUsers'].'personal/log/#log_id#/');

		$result['pathToMessagesChat'] = Option::get('main', 'TOOLTIP_PATH_TO_MESSAGES_CHAT', false, SITE_ID);
		$result['pathToMessagesChat']  = ($result['pathToMessagesChat'] ?: $result['folderUsers'].'messages/chat/#user_id#/');

		$result['pathToVideoCall'] = Option::get('main', 'TOOLTIP_PATH_TO_VIDEO_CALL', false, SITE_ID);
		$result['pathToVideoCall'] = ($result['pathToVideoCall'] ?: $result['folderUsers'].'video/#user_id#/');

		$result['pathToSmile'] = Option::get('socialnetwork', 'smile_page', false, SITE_ID);
		$result['pathToSmile'] = ($result['pathToSmile'] ?: '/bitrix/images/socialnetwork/smile/');

		$pathToUser = Option::get('main', 'TOOLTIP_PATH_TO_USER', false, SITE_ID);
		$pathToUser = ($pathToUser ?: $result['folderUsers'].'user/#user_id#/');

		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER', $pathToUser);
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_MICROBLOG', $result['folderUsers'].'user/#user_id#/blog/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_BLOG_POST', $result['pathToUserBlogPost']);
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_BLOG_POST_EDIT', $result['folderUsers'].'user/#user_id#/blog/edit/#post_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_BLOG_POST_IMPORTANT', $result['folderUsers'].'user/#user_id#/blog/important/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP', $result['folderWorkgroups'].'group/#group_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP_MICROBLOG', $result['folderWorkgroups'].'group/#group_id#/blog/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP_BLOG_POST', $result['folderWorkgroups'].'group/#group_id#/blog/#post_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_LOG_ENTRY', $result['pathToLogEntry']);
		Util::checkEmptyParamString($componentParams, 'PATH_TO_MESSAGES_CHAT', $result['pathToMessagesChat']);
		Util::checkEmptyParamString($componentParams, 'PATH_TO_VIDEO_CALL', $result['pathToVideoCall']);
		Util::checkEmptyParamString($componentParams, 'PATH_TO_SMILE', $result['pathToSmile']);

		$componentParams['PATH_TO_USER_MICROBLOG_POST'] = $componentParams['PATH_TO_USER_BLOG_POST'];
		$componentParams['PATH_TO_GROUP_MICROBLOG_POST'] = $componentParams['PATH_TO_GROUP_BLOG_POST'];

		return $result;
	}

	public function setPaths(&$params): void
	{
		$pathResult = $this->preparePathParams($params);
		$this->setFolderUsersValue($pathResult['folderUsers']);
		$this->setFolderWorkgroupsValue($pathResult['folderWorkgroups']);

		$this->pathToUserBlogPost = $pathResult['pathToUserBlogPost'];
		$this->pathToLogEntry = $pathResult['pathToLogEntry'];
		$this->pathToMessagesChat = $pathResult['pathToMessagesChat'];
		$this->pathToVideoCall = $pathResult['pathToVideoCall'];
		$this->pathToSmile = $pathResult['pathToSmile'];
	}
}
