<?php
namespace Bitrix\Im\Controller\Call;

use Bitrix\Disk\Controller\File,
	Bitrix\Main\Engine\Controller,
	Bitrix\Disk\Controller\Content,
	Bitrix\Main\Engine\ActionFilter,
	Bitrix\Im\Controller\Call\Filter;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Loader;

/**
 * "Proxy" controller to Disk controller actions.
 *
 * Class Disk
 * @package Bitrix\Im\Controller\Call
 */
class Disk extends Controller
{
	protected function getDefaultPreFilters(): array
	{
		return [
			new ActionFilter\HttpMethod(['POST']),
			new Filter\Authorization(),
			new Filter\DiskFolderAccessCheck(),
		];
	}

	public function configureActions(): array
	{
		return [
			'upload' => [
				'-prefilters' => [
					ActionFilter\Csrf::class,
					ActionFilter\Authentication::class
				],
			],
			'commit' => [
				'-prefilters' => [
					ActionFilter\Csrf::class,
					ActionFilter\Authentication::class
				],
			],
		];
	}

	protected function processBeforeAction(Action $action): bool
	{
		if (!Loader::includeModule('disk'))
		{
			return false;
		}

		return parent::processBeforeAction($action);
	}

	public function uploadAction($filename, $token = null)
	{
		$params = [
			'filename' => $filename,
			'token' => $token,
		];
		$this->setScope(Controller::SCOPE_REST);

		return $this->forward(new Content(), 'upload', $params);
	}

	public function commitAction($folderId, $filename, $contentId, $generateUniqueName = false)
	{
		$params = [
			'folderId' => $folderId,
			'filename' => $filename,
			'contentId' => $contentId,
			'generateUniqueName' => $generateUniqueName,
		];

		$this->setScope(Controller::SCOPE_REST);

		return $this->forward(new File(), 'createByContent', $params);
	}
}