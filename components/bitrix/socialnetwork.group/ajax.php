<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\WorkgroupTable;

class SocialnetworkGroupAjaxController extends \Bitrix\Main\Engine\Controller
{
	protected $groupId;

	public function __construct(\Bitrix\Main\Request $request = null)
	{
		parent::__construct($request);

		\Bitrix\Main\Loader::includeModule('socialnetwork');
	}

	protected function processBeforeAction(\Bitrix\Main\Engine\Action $action)
	{
		parent::processBeforeAction($action);

		if ($action->getName() !== 'loadPhoto')
		{
			return true;
		}

		if (!$this->getRequest()->isPost() || !$this->getRequest()->getPost('signedParameters'))
		{
			return false;
		}

		$parameters = $this->getUnsignedParameters();

		if (isset($parameters['GROUP_ID']))
		{
			$this->groupId = $parameters['GROUP_ID'];
		}
		else
		{
			return false;
		}

		return true;
	}

	public function getComponentAction(array $params = []): \Bitrix\Main\Engine\Response\Component
	{
		$componentParameters = ComponentHelper::getWorkgroupSliderMenuUnsignedParameters($this->getSourceParametersList());

		$componentResponse = new \Bitrix\Main\Engine\Response\Component(
			'bitrix:socialnetwork.group',
			($params['componentTemplate'] ?? ''),
			$componentParameters,
			[],
			[ 'PageTitle' ]
		);

		return $componentResponse;
	}

	public function loadPhotoAction()
	{
		if (
			!\Bitrix\Main\Loader::includeModule('socialnetwork')
			|| !\Bitrix\Socialnetwork\Helper\Workgroup\Access::canUpdate([
				'groupId' => $this->groupId,
			])
		)
		{
			$this->addError(new Error(Loc::getMessage('SOCIALNETWORK_GROUP_AJAX_NO_UPDATE_PERMS')));
			return false;
		}

		$workgroupData = WorkgroupTable::getList([
			'select' => [ 'ID', 'IMAGE_ID' ],
			'filter' => [
				'=ID' => $this->groupId,
			],
		])->fetch();

		$newPhotoFile = $this->getRequest()->getFile('newPhoto');

		if ($workgroupData['IMAGE_ID'])
		{
			$newPhotoFile['old_file'] = $workgroupData['IMAGE_ID'];
			$newPhotoFile['del'] = $workgroupData['IMAGE_ID'];
		}

		$res = \CSocNetGroup::update(
			$this->groupId,
			[ 'IMAGE_ID' => $newPhotoFile ],
			true,
			true,
			false
		);

		if (!$res)
		{
			$this->addError(new Error(Loc::getMessage('SOCIALNETWORK_GROUP_AJAX_FAILED')));
			return false;
		}

		$workgroupData = WorkgroupTable::getList([
			'select' => [ 'ID', 'IMAGE_ID' ],
			'filter' => [
				'=ID' => $this->groupId,
			],
		])->fetch();

		if ($workgroupData['IMAGE_ID'] > 0)
		{
			$file = \CFile::getFileArray($workgroupData['IMAGE_ID']);
			if ($file !== false)
			{
				$fileTmp = \CFile::resizeImageGet(
					$file,
					[ 'width' => 100, 'height' => 100 ],
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false,
					false,
					true
				);

				return [
					'imageSrc' => $fileTmp['src'],
				];
			}
		}

		return false;
	}

}
