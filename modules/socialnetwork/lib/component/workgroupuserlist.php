<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Helper;

class WorkgroupUserList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	public const AJAX_ACTION_SET_OWNER = 'setOwner';
	public const AJAX_ACTION_SET_MODERATOR = 'setModerator';
	public const AJAX_ACTION_REMOVE_MODERATOR = 'removeModerator';
	public const AJAX_ACTION_EXCLUDE = 'exclude';
	public const AJAX_ACTION_DELETE_OUTGOING_REQUEST = 'deleteOutgoingRequest';

	/** @var ErrorCollection errorCollection */
	protected $errorCollection = null;

	public function configureActions()
	{
		return [
		];
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	/**
	 * Adds error to error collection.
	 * @param Error $error Error.
	 *
	 * @return $this
	 */
	protected function addError(Error $error)
	{
		$this->errorCollection[] = $error;

		return $this;
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

	protected function printErrors(): void
	{
		foreach ($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	public static function getNameFormattedValue(array $params = []): string
	{
		static $nameTemplate = null;

		$result = '';

		$userFields = ($params['FIELDS'] ?? []);

		$path = ($params['PATH'] ?? '');

		if (empty($userFields))
		{
			return $result;
		}

		if ($nameTemplate === null)
		{
			$nameTemplate = \CSite::getNameFormat();
		}

		$result = \CUser::formatName($nameTemplate, $userFields, true, true);

		if (
			$result !== ''
			&& $path !== ''
		)
		{
			$result = '<a href="'.htmlspecialcharsbx(str_replace([ '#ID#', '#USER_ID#', '#user_id#' ], $userFields['ID'], $path)).'">'.$result.'</a>';
		}

		return $result;
	}

	public static function getPhotoValue(array $params = []): string
	{
		$result = '<div class="intranet-user-list-userpic ui-icon ui-icon-common-user"><i></i></div>';

		$userFields = ($params['FIELDS'] ?? []);
//		$path = (isset($params['PATH']) ? $params['PATH'] : '');

		if (empty($userFields))
		{
			return $result;
		}

		$personalPhoto = $userFields['PERSONAL_PHOTO'];
		if (empty($personalPhoto))
		{
			switch ($userFields['PERSONAL_GENDER'])
			{
				case 'M':
					$suffix = 'male';
					break;
				case 'F':
					$suffix = 'female';
					break;
				default:
					$suffix = 'unknown';
			}
			$personalPhoto = Option::get('socialnetwork', 'default_user_picture_' . $suffix, false, SITE_ID);
		}

		if (empty($personalPhoto))
		{
			return $result;
		}

		$file = \CFile::getFileArray($personalPhoto);
		if (!empty($file))
		{
			$fileResized = \CFile::resizeImageGet(
				$file,
				[
					'width' => 100,
					'height' => 100,
				],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);

			$result = "<div class=\"intranet-user-list-userpic ui-icon ui-icon-common-user\"><i style=\"background-image: url('" . $fileResized['src'] . "'); background-size: cover\"></i></div>";
		}

		return $result;
	}

	public static function getActions(array $params = []): array
	{
		$result = [];
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$result[] = 'view_profile';
		}

		$relation = $params['RELATION'];
		$groupId = (int)$params['GROUP_ID'];

		if (Helper\Workgroup::canSetOwner([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = 'set_owner';
		}

		if (Helper\Workgroup::canSetModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = 'set_moderator';
		}
		elseif (Helper\Workgroup::canRemoveModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = 'remove_moderator';
		}

		if (Helper\Workgroup::canDeleteOutgoingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = 'delete_outgoing_request';
		}
		elseif (Helper\Workgroup::canExclude([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = 'exclude';
		}

		return $result;
	}

	public function reinviteUserAction(array $params = [])
	{
		$result = false;

		return $result;
	}

	public static function getDepartmentValue(array $params = []): string
	{
		return (
			Loader::includeModule('intranet')
				? \Bitrix\Intranet\Component\UserList::getDepartmentValue($params)
				: ''
		);
	}

}
