<?php

namespace Bitrix\UI\Timeline;

use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;
use Bitrix\Rpa\Driver;

class Comment
{
	protected $filesUserFieldEntityId;
	protected $filesUserFieldName;

	public function __construct(string $filesUserFieldEntityId, string $filesUserFieldName)
	{
		$this->filesUserFieldEntityId = $filesUserFieldEntityId;
		$this->filesUserFieldName = $filesUserFieldName;
	}

	public function getVisualEditorResponse(string $name, int $id = 0, string $text = ''): Component
	{
		$formId = 'ui-timeline-comment-'.$name;

		$fileFields = $this->getFileUserFields($id);
		$isUploadFilesAvailable = isset($fileFields[$this->filesUserFieldName]);

		$params = [
			'SELECTOR_VERSION' => 2,
			'FORM_ID' => $formId,
			'SHOW_MORE' => 'N',
			'PARSER' => [
				'Bold', 'Italic', 'Underline', 'Strike',
				'ForeColor', 'FontList', 'FontSizeList', 'RemoveFormat',
				'Quote', 'Code', 'InsertCut',
				'CreateLink', 'Image', 'Table', 'Justify',
				'InsertOrderedList', 'InsertUnorderedList',
				'SmileList', 'Source', 'UploadImage', 'InputVideo', 'MentionUser'
			],
			'BUTTONS' => [
				($isUploadFilesAvailable ? 'UploadImage' : null),
				'CreateLink',
				'InputVideo',
				'Quote',
				'MentionUser'
			],
			'TEXT' => [
				'NAME' => 'MESSAGE',
				'VALUE' => $text,
				'HEIGHT' => '120px'
			],
			'LHE' => [
				'id' => $name,
				'documentCSS' => 'body {color:#434343;background:#F7FBE9}',
				'jsObjName' => $name,
				'width' => '100%',
				'minBodyWidth' => '100%',
				'normalBodyWidth' => '100%',
				'height' => 100,
				'minBodyHeight' => 100,
				'showTaskbars' => false,
				'showNodeNavi' => false,
				'autoResize' => true,
				'autoResizeOffset' => 50,
				'bbCode' => true,
				'saveOnBlur' => false,
				'bAllowPhp' => false,
				'lazyLoad' => true,
				'limitPhpAccess' => false,
				'setFocusAfterShow' => true,
				'askBeforeUnloadPage' => false,
				'useFileDialogs' => false,
				'controlsMap' => [
					['id' => 'Bold',  'compact' => true, 'sort' => 10],
					['id' => 'Italic',  'compact' => true, 'sort' => 20],
					['id' => 'Underline',  'compact' => true, 'sort' => 30],
					['id' => 'Strikeout',  'compact' => true, 'sort' => 40],
					['id' => 'RemoveFormat',  'compact' => true, 'sort' => 50],
					['id' => 'Color',  'compact' => true, 'sort' => 60],
					['id' => 'FontSelector',  'compact' => false, 'sort' => 70],
					['id' => 'FontSize',  'compact' => false, 'sort' => 80],
					['separator' => true, 'compact' => false, 'sort' => 90],
					['id' => 'OrderedList',  'compact' => true, 'sort' => 100],
					['id' => 'UnorderedList',  'compact' => true, 'sort' => 110],
					['id' => 'AlignList', 'compact' => false, 'sort' => 120],
					['separator' => true, 'compact' => false, 'sort' => 130],
					['id' => 'InsertLink',  'compact' => true, 'sort' => 140, 'wrap' => 'bx-b-link-'.$formId],
					['id' => 'InsertImage',  'compact' => false, 'sort' => 150],
					['id' => 'InsertVideo',  'compact' => true, 'sort' => 160, 'wrap' => 'bx-b-video-'.$formId],
					['id' => 'InsertTable',  'compact' => false, 'sort' => 170],
					['id' => 'Code',  'compact' => true, 'sort' => 180],
					['id' => 'Quote',  'compact' => true, 'sort' => 190, 'wrap' => 'bx-b-quote-'.$formId],
					['separator' => true, 'compact' => false, 'sort' => 200],
					['id' => 'BbCode',  'compact' => true, 'sort' => 220],
					['id' => 'More',  'compact' => true, 'sort' => 230],
				],
			],
			'USE_CLIENT_DATABASE' => 'Y',
			'FILES' => [
				'VALUE' => [],
				'DEL_LINK' => '',
				'SHOW' => 'N'
			],
			'UPLOAD_FILE' => $isUploadFilesAvailable,
			'UPLOAD_FILE_PARAMS' => ['width' => 400, 'height' => 400],
			'UPLOAD_WEBDAV_ELEMENT' => $fileFields[$this->filesUserFieldName] ?? false,
		];

		return new Component('bitrix:main.post.form', '', $params);
	}

	public function saveFiles(int $id, array $files): bool
	{
		$manager = $this->getUserFieldManager();
		if ($manager instanceof \CUserTypeManager)
		{
			$data = [
				$this->filesUserFieldName => $files,
			];
			if ($manager->CheckFields($this->filesUserFieldEntityId, $id, $data))
			{
				return (bool) $manager->Update($this->filesUserFieldEntityId, $id, $data);
			}
		}

		return false;
	}

	public function getFileUserFields(int $id = 0): array
	{
		$manager = $this->getUserFieldManager();

		if($manager && ModuleManager::isModuleInstalled('disk'))
		{
			$fileFields = $manager->GetUserFields($this->filesUserFieldEntityId, $id);
			if(isset($fileFields[$this->filesUserFieldName]))
			{
				$fileFields[$this->filesUserFieldName]['~EDIT_FORM_LABEL'] = $this->filesUserFieldName;
				$fileFields[$this->filesUserFieldName]['TAG'] = 'DOCUMENT ID';
			}

			return $fileFields;
		}

		return [];
	}

	public function getFilesContentResponse(int $id): ?Component
	{
		$fileFields = $this->getFileUserFields($id);
		if($fileFields && !empty($fileFields[$this->filesUserFieldName]['VALUE']))
		{
			return new Component('bitrix:system.field.view',
				$fileFields[$this->filesUserFieldName]["USER_TYPE"]["USER_TYPE_ID"],
				[
					"PUBLIC_MODE" => false,
					"ENABLE_AUTO_BINDING_VIEWER" => true,
					"LAZYLOAD" => 'Y',
					'arUserField' => $fileFields[$this->filesUserFieldName],
				]
			);
		}

		return null;
	}

	public function sendMentions(
		int $id,
		int $fromUserId,
		string $text,
		string $message,
		array $previouslyMentionedUserIds = []
	): array
	{
		$messageIds = [];

		$parser = new CommentParser();
		$mentionedUserIds = $parser->getMentionedUserIds($text);
		$mentionedUserIds = array_filter(
			$mentionedUserIds,
			static function($userId) use ($fromUserId, $previouslyMentionedUserIds) {
				$userId = (int)$userId;
				return (
					$userId !== $fromUserId
					&& !in_array($userId, $previouslyMentionedUserIds, true)
				);
			}
		);

		if(empty($mentionedUserIds))
		{
			return $messageIds;
		}

		if(!Loader::includeModule('im'))
		{
			return $messageIds;
		}

		foreach ($mentionedUserIds as $userId)
		{
			$userId = (int)$userId;

			$messageIds[] = \CIMNotify::Add([
				'TO_USER_ID' => $userId,
				'FROM_USER_ID' => $fromUserId,
				'NOTIFY_TYPE' => IM_NOTIFY_FROM,
				'NOTIFY_MODULE' => Driver::MODULE_ID,
				'NOTIFY_TAG' => 'RPA|MESSAGE_TIMELINE_MENTION|' . $id,
				'NOTIFY_MESSAGE' => $message,
			]);
		}

		return $messageIds;
	}

	public function getUserGenderSuffix(int $userId): string
	{
		$userData = UserTable::getList([
			'select' => ['PERSONAL_GENDER'],
			'filter' => [
				'=ID' => $userId,
			],
			'limit' => 1,
		])->fetch();
		if($userData && !empty($userData['PERSONAL_GENDER']))
		{
			return '_'.$userData['PERSONAL_GENDER'];
		}

		return '';
	}

	protected function getUserFieldManager(): ?\CUserTypeManager
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER;
	}
}