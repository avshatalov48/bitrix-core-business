<?php

namespace Bitrix\UI\Avatar\Controller;

use Bitrix\Main;
use Bitrix\Rest;
use Bitrix\UI;
use Bitrix\UI\Avatar;
use \Bitrix\Main\Engine\Response;
use \Bitrix\Main\UI\PageNavigation;

class Mask extends Main\Engine\Controller
{
	public function getSystemListAction(PageNavigation $pageNavigation, Main\Engine\CurrentUser $currentUser): Response\DataType\Page
	{
		return static::getList(
			Avatar\Model\ItemTable::query()
				->setFilter([
					'=OWNER_TYPE' => [
						Avatar\Mask\Owner\System::class,
						Avatar\Mask\Owner\RestApp::class
					],
				])
			,
			$pageNavigation,
			$currentUser
		);
	}

	public function getUserListAction(PageNavigation $pageNavigation, Main\Engine\CurrentUser $currentUser): Response\DataType\Page
	{
		return static::getList(
			Avatar\Model\ItemTable::query()
				->setFilter([
					'=OWNER_TYPE' => Avatar\Mask\Owner\User::class,
					'=OWNER_ID' => $currentUser->getId()
				])
			,
			$pageNavigation,
			$currentUser
		);
	}

	public function getSharedListAction(PageNavigation $pageNavigation, Main\Engine\CurrentUser $currentUser): Response\DataType\Page
	{
		return static::getList(
			Avatar\Model\ItemTable::query()
				->setFilter([
					'=SHARED_FOR.USER_ACCESS.USER_ID' => $currentUser->getId(),
					'=OWNER_TYPE' => Avatar\Mask\Owner\User::class,
					'!=OWNER_ID' => $currentUser->getId()
				])
				->setDistinct()
				->addOrder('SHARED_FOR.ID', 'DESC')
			,
			$pageNavigation,
			$currentUser
		);
	}

	public function getRecentlyUsedListAction(PageNavigation $pageNavigation, Main\Engine\CurrentUser $currentUser): Response\DataType\Page
	{
		return static::getList(
			Avatar\Model\ItemTable::query()
				->setSelect(['ID', 'FILE_ID', 'TITLE', 'DESCRIPTION', 'SORT'])
				->setOrder(['RECENTLY_USED_BY.ID' => 'DESC', 'ID' => 'DESC'])
				->setFilter([
					'=RECENTLY_USED_BY.USER_ID' => $currentUser->getId()
				])
				->addOrder('RECENTLY_USED_BY.ID', 'DESC')
				->setDistinct()
			,
			$pageNavigation,
			$currentUser
		);
	}

	protected function getList(
		Main\ORM\Query\Query $query,
		PageNavigation $pageNavigation,
		Main\Engine\CurrentUser $currentUser
	): Response\DataType\Page
	{
		if (count($query->getSelect()) <= 0)
		{
			$query
				->setSelect(['ID', 'GROUP_ID', 'FILE_ID', 'TITLE', 'DESCRIPTION', 'SORT'])
				->setOrder(['GROUP_ID' => 'ASC', 'SORT' => 'ASC', 'ID' => 'DESC'])
			;
		}
		$dbRes = $query
			->setLimit($pageNavigation->getLimit())
			->setOffset($pageNavigation->getOffset())
			->setCacheTtl(86400)
			->exec();
		$result = [];
		while (($res = $dbRes->fetch()) && $res)
		{
			if ($file = \CFile::GetFileArray($res['FILE_ID']))
			{
				$groupId = (int) ($res['GROUP_ID'] ?? 0);
				if (!isset($result[$groupId]))
				{
					$result[$groupId] = ['items' => []];
				}
				$result[$groupId]['items'][] = [
					'id' => $res['ID'],
					'title' => $res['TITLE'],
					'description' => $res['DESCRIPTION'],
					'src' => $file['SRC'],
					//TODO сделать метод load по всем выбранным данным, чтобы больше в БД не ходить
					'editable' => Avatar\Mask\Item::getInstance($res['ID'])->isEditableBy(
						Avatar\Mask\Consumer::createFromId($currentUser->getId())
					)
				];
			}
		}

		$groupIds = array_keys($result);
		if (array_sum($groupIds) > 0)
		{
			$dbRes = Avatar\Model\GroupTable::getList([
				'select' => [
					'ID', 'TITLE'
				],
				'filter' => [
					'=ID' => $groupIds,
				],
				'order' => [
					'SORT' => 'ASC',
					'ID' => 'ASC'
				],
				'cache' => [
					'ttl' => 86400
				]
			]);
			while ($res = $dbRes->fetch())
			{
				if (isset($result[$res['ID']]))
				{
					$result[$res['ID']]['title'] = $res['TITLE'];
					$result[$res['ID']]['id'] = $res['ID'];
				}
			}
		}

		return new Response\DataType\Page('groupedItems', array_values($result), null);
	}

	public function getMaskAccessCodeAction(int $id, Main\Engine\CurrentUser $currentUser): Response\AjaxJson
	{
		$result = $this->checkEditability($id, $currentUser);
		if ($result->isSuccess())
		{
			['item' => $item] = $result->getData();
			return Response\AjaxJson::createSuccess(
				['accessCode' => Main\UI\EntitySelector\Converter::convertFromFinderCodes($item->getAccessCode())]
			);
		}
		return Response\AjaxJson::createDenied();
	}

	protected function checkEditability(int $id, Main\Engine\CurrentUser $currentUser): Main\Result
	{
		$result = new Main\Result();
		$consumer = Avatar\Mask\Consumer::createFromId($currentUser->getId());

		if (!($item = Avatar\Mask\Item::getInstance($id)))
		{
			$result->addError(new Main\Error("Mask with id {$id} is not found.", 'Not found.'));
		}
		elseif (!$item->isEditableBy($consumer))
		{
			$result->addError(new Main\Error("Mask with id {$id} is not editable.", 'Access denied.'));
		}
		else
		{
			$result->setData(['item' => $item]);
		}
		return $result;
	}

	public function saveAction($id, $title, $accessCode, $file, Main\Engine\CurrentUser $currentUser): Response\AjaxJson
	{
		$destCodesList = Main\UI\EntitySelector\Converter::convertToFinderCodes($accessCode);
		$file = ($file['changed'] === 'Y' ? $this->getRequest()->getFile('file') : null);
		$id = intval($id); //can be null
		if ($id > 0)
		{
			$result = $this->checkEditability($id, $currentUser);
			if ($result->isSuccess())
			{
				['item' => $item] = $result->getData();
				/* @var Avatar\Mask\Item $item*/
				$result = $item->update([
					'TITLE' => $title,
					'ACCESS_CODE' => $destCodesList
					] + (!empty($file) ? [
					'FILE' => $file] : [])
				);
			}
		}
		else
		{
			$result = Avatar\Mask\Item::create(
				new Avatar\Mask\Owner\User($currentUser->getId()),
				$file,
				[
					'TITLE' => $title,
					'ACCESS_CODE' => $destCodesList
				]
			);
			if ($result->isSuccess())
			{
				$id = $result->getId();
			}
		}
		if ($result->isSuccess())
		{
			$responsePage = static::getList(
				Avatar\Model\ItemTable::query()
					->setFilter([
						'=ID' => $id
					])
				,
				(new PageNavigation('justBuffNav'))->setPageSize(1),
				$currentUser
			);
			if (($groupedItems = $responsePage->getItems())
				&& ($itemsFromOneGroup = reset($groupedItems))
				&& isset($itemsFromOneGroup['items'])
				&& ($itemData = reset($itemsFromOneGroup['items']))
			)
			{
				$result = $this->checkEditability($id, $currentUser);
				if ($result->isSuccess())
				{
					['item' => $item] = $result->getData();
					$itemData['accessCode'] = Main\UI\EntitySelector\Converter::convertFromFinderCodes($item->getAccessCode());
				}
				return Response\AjaxJson::createSuccess(
					$itemData
				);
			}
		}

		return Response\AjaxJson::createError($result->getErrorCollection());
	}

	public function deleteAction(int $id, Main\Engine\CurrentUser $currentUser): Response\AjaxJson
	{
		$result = $this->checkEditability($id, $currentUser);
		if ($result->isSuccess())
		{
			/* @var Avatar\Mask\Item $item*/
			['item' => $item] = $result->getData();
			$item->delete();
		}
		if ($result->isSuccess())
		{
			return Response\AjaxJson::createSuccess(
				$result
			);
		}
		return Response\AjaxJson::createError($result->getErrorCollection());
	}

	public function getMaskInitialInfoAction($recentlyUsedListSize, PageNavigation $pageNavigation, Main\Engine\CurrentUser $currentUser)
	{
		$pageNav = new PageNavigation('recentlyUsedListSize');
		$pageNav->setPageSize($recentlyUsedListSize);

		return new Response\DataType\Page('initialInfo', [
			'recentlyUsedItems' => static::getRecentlyUsedListAction($pageNav, $currentUser)->getItems(),
			'systemItems' => static::getSystemListAction($pageNavigation, $currentUser)->getItems(),
			'myOwnItems' => static::getUserListAction($pageNavigation, $currentUser)->getItems(),
			'sharedItems' => static::getSharedListAction($pageNavigation, $currentUser)->getItems(),
			'restMarketInfo' => Main\Loader::includeModule('rest') ? [
				'available' => 'Y',
				'exportUrl' => Rest\Configuration\Helper::getInstance()->enabledZipMod() ? Rest\Marketplace\Url::getConfigurationExportElementUrl(
					UI\Integration\Rest\MaskManifest::CODE,
					$currentUser->getId()
				) : null,
				'importUrl' => Rest\Marketplace\Url::getConfigurationImportManifestUrl(
					UI\Integration\Rest\MaskManifest::CODE
				),
				'marketUrl' => Rest\Marketplace\Url::getCategoryUrl('user_frame')
			] : [
				'available' => 'N',
			]
		], null);
	}

	public function useRecentlyAction(int $id, Main\Engine\CurrentUser $currentUser): Response\AjaxJson
	{
		$consumer = Avatar\Mask\Consumer::createFromId($currentUser->getId());
		if (Avatar\Mask\Item::getInstance($id)->isReadableBy($consumer))
		{
			$consumer->useRecentlyMaskId($id);
		}
		return Response\AjaxJson::createSuccess();
	}

	public function cleanUpAction(Main\Engine\CurrentUser $currentUser): Response\AjaxJson
	{
		(new Avatar\Mask\Owner\User($currentUser->getId()))->delete();
		return Response\AjaxJson::createSuccess();
	}
}
