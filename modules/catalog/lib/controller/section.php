<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Section extends Controller
{
	//region Actions
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * @param PageNavigation $pageNavigation
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param bool $__calculateTotalCount
	 * @return Page|null
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): ?Page
	{
		$r = $this->checkPermissionIBlockSectionList($filter['IBLOCK_ID']);
		if ($r->isSuccess())
		{
			$result = [];

			$select = empty($select) ? ['*'] : $select;
			$order = empty($order) ? ['ID'=>'ASC'] : $order;

			if (isset($filter['IBLOCK_SECTION_ID']))
			{
				$filter['SECTION_ID'] = $filter['IBLOCK_SECTION_ID'];
				unset($filter['IBLOCK_SECTION_ID']);
			}

			$r = \CIBlockSection::GetList(
				$order,
				$filter,
				false,
				$select,
				self::getNavData($pageNavigation->getOffset())
			);
			while ($l = $r->fetch())
			{
				$result[] = $l;
			}
			unset($l, $r);

			return new Page(
				$this->getServiceListName(),
				$result,
				$__calculateTotalCount ? $this->getCount($filter) : 0);
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}

	}

	public function getAction($id)
	{
		$result = $this->checkPermissionIBlockSectionGet($id);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$row = $this->get($id);
		if (!is_array($row))
		{
			$this->addErrorEntityNotExists();

			return null;
		}

		return [
			$this->getServiceItemName() => $row,
		];
	}

	public function addAction($fields)
	{
		$r = $this->checkPermissionIBlockSectionAdd($fields['IBLOCK_ID']);

		$id = 0;
		if ($r->isSuccess())
		{
			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockSectionSectionBindUpdate($fields['IBLOCK_SECTION_ID']);

				if (!$r->isSuccess())
				{
					$this->addErrors($r->getErrors());

					return null;
				}
			}

			$r = $this->addValidate($fields);

			if ($r->isSuccess())
			{
				$section = new \CIBlockSection();
				$connection = Application::getConnection();
				$connection->startTransaction();
				try
				{
					$id = $section->Add($fields);
					$error = $section->getLastError();
				}
				catch (SqlQueryException)
				{
					$error = 'Internal error adding section. Try adding again.';
				}
				if ($error !== '')
				{
					$connection->rollbackTransaction();
					$r->addError(new Error($error, 200700300000));
				}
				else
				{
					$connection->commitTransaction();
				}
				unset(
					$error,
					$section,
				);
			}
		}

		if ($r->isSuccess())
		{
			return [$this->getServiceItemName() => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function updateAction($id, array $fields)
	{
		$r = $this->exists($id);

		if ($r->isSuccess())
		{
			$r = $this->checkPermissionIBlockSectionUpdate($id);

			if (!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());

				return null;
			}

			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockSectionSectionBindUpdate($fields['IBLOCK_SECTION_ID']);

				if (!$r->isSuccess())
				{
					$this->addErrors($r->getErrors());

					return null;
				}
			}

			$r = $this->updateValidate($fields + ['ID' => $id]);

			if ($r->isSuccess())
			{
				$section = new \CIBlockSection();
				$connection = Application::getConnection();
				$connection->startTransaction();
				try
				{
					$section->Update($id, $fields);
					$error = $section->getLastError();
				}
				catch (SqlQueryException)
				{
					$error = 'Internal error updating section. Try updating again.';
				}
				if ($error !== '')
				{
					$connection->rollbackTransaction();
					$r->addError(new Error($error, 200700300010));
				}
				else
				{
					$connection->commitTransaction();
				}
				unset(
					$error,
					$section,
				);
			}
		}

		if ($r->isSuccess())
		{
			return [$this->getServiceItemName() => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function deleteAction($id)
	{
		$r = $this->checkPermissionIBlockSectionDelete($id);

		if ($r->isSuccess())
		{
			$r = $this->exists($id);

			if ($r->isSuccess())
			{
				$connection = Application::getConnection();
				$connection->startTransaction();
				try
				{
					if (!\CIBlockSection::Delete($id))
					{
						if ($ex = self::getApplication()->GetException())
						{
							$r->addError(new Error($ex->GetString(), $ex->GetId()));
						}
						else
						{
							$r->addError(new Error('delete section error', 200700300020));
						}
					}
				}
				catch (SqlQueryException)
				{
					$r->addError(new Error('Internal error deleting section. Try deleting again.', 200700300020));
				}
				if ($r->isSuccess())
				{
					$connection->commitTransaction();
				}
				else
				{
					$connection->rollbackTransaction();
				}
			}
		}

		if ($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}
	//endregion

	protected function exists($id)
	{
		$result = new Result();

		$iterator = \CIBlockSection::GetList(
			[],
			['ID' => (int)$id],
			false,
			['ID']
		);
		$row = $iterator->Fetch();
		unset($iterator);
		if (empty($row))
		{
			$result->addError($this->getErrorEntityNotExists());
		}

		return $result;
	}

	protected function get($id)
	{
		return \CIBlockSection::GetByID($id)->Fetch();
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::SECTION_ENTITY_NOT_EXISTS;
	}

	protected function addValidate($fields): Result
	{
		$r = new Result();

		if (isset($fields['CODE']))
		{
			$isCodeUnique = $this->isCodeUnique($fields);

			if (!$isCodeUnique->isSuccess())
			{
				$r->addError($isCodeUnique->getErrors()[0]);
			}
		}

		return $r;
	}

	protected function updateValidate($fields): Result
	{
		$r = new Result();

		if (isset($fields['CODE']))
		{
			$isCodeUnique = $this->isCodeUnique($fields);

			if (!$isCodeUnique->isSuccess())
			{
				$r->addError($isCodeUnique->getErrors()[0]);
			}
		}

		return $r;
	}

	private function isCodeUnique(array $fields): Result
	{
		$r = new Result();

		$iblock = \CIBlock::GetArrayByID($fields['IBLOCK_ID']);

		if (isset($iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']))
		{
			if (
				$iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['TRANSLITERATION'] === 'Y'
				&& $iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE'] === 'Y'
			)
			{
				$filter = [
					'=IBLOCK_ID' => $fields['IBLOCK_ID'],
					'=CODE' => $fields['CODE'],
				];

				if (isset($fields['ID']))
				{
					$filter['!=ID'] = $fields['ID'];
				}

				$existsResult = $this->existsByFilter($filter);

				if ($existsResult->isSuccess())
				{
					$r->addError($this->getErrorDublicateFieldCode());
				}
			}
		}

		return $r;
	}

	private function getErrorDublicateFieldCode(): Error
	{
		return new Error('Duplicate entry for key [code]', 200700300040);
	}

	protected function getEntityTable(): DataManager
	{
		return new SectionTable();
	}

	private function getCount(array $filter): \Closure
	{
		return function() use ($filter)
		{
			return \CIBlockSection::GetCount($filter);
		};
	}

	//region checkPermissionController
	protected function checkModifyPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !$this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}

		return $r;
	}
	//endregion

	//region checkPermissionIBlock
	protected function checkPermissionIBlockSectionAdd($iblockId)
	{
		return $this->checkPermissionIBlockSectionModify($iblockId, 0);
	}

	protected function checkPermissionIBlockSectionUpdate($sectionId)
	{
		$iblockId = $this->getIBlockBySectionId($sectionId);

		return $this->checkPermissionIBlockSectionModify($iblockId, $sectionId);
	}

	protected function checkPermissionIBlockSectionModify($iblockId, $sectionId)
	{
		$r = new Result();

		$iblock = \CIBlock::GetArrayByID($iblockId);
		$isBadIblock = false;

		if ($iblock)
		{
			$isBadIblock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_EDIT);
		}
		else
		{
			$r->addError(new Error('Iblock is not exists', 200700300050));
		}

		if ($isBadIblock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}

		return $r;
	}

	protected function checkPermissionIBlockSectionSectionBindModify($iblockId, $iblockSectionId)
	{
		$r = new Result();

		$iblock = \CIBlock::GetArrayByID($iblockId);
		$isBadIblock = false;

		if ($iblock)
		{
			$isBadIblock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $iblockSectionId, self::IBLOCK_SECTION_SECTION_BIND); //access update
		}
		else
		{
			$r->addError(new Error('Iblock is not exists', 200700300050));
		}

		if ($isBadIblock)
		{
			$r->addError(new Error('Access Denied', 200040300050));
		}

		return $r;
	}

	protected function checkPermissionIBlockSectionSectionBindUpdate($iblockSectionId)
	{
		$iblockId = $this->getIBlockBySectionId($iblockSectionId);

		return $this->checkPermissionIBlockSectionModify($iblockId, $iblockSectionId);
	}

	protected function checkPermissionIBlockSectionDelete($sectionId)
	{
		$r = new Result();
		$iblockId = \CIBlockElement::GetIBlockByID($sectionId);
		$isBadIblock = !\CIBlockElementRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_SECTION_DELETE); //access delete

		if ($isBadIblock)
		{
			$r->addError(new Error('Access Denied', 200040300050));
		}

		return $r;
	}

	protected function checkPermissionIBlockSectionGet($sectionId)
	{
		$r = new Result();

		$iblockId = $this->getIBlockBySectionId($sectionId);
		$isBadIblock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_SECTION_READ);

		if ($isBadIblock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}

		return $r;
	}

	protected function checkPermissionIBlockSectionList($iblockId)
	{
		$r = new Result();

		$iblock = \CIBlock::GetArrayByID($iblockId);
		$isBadIblock = false;

		if ($iblock)
		{
			$isBadIblock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_READ);
		}
		else
		{
			$r->addError(new Error('Iblock is not exists', 200700300050));
		}

		if ($isBadIblock)
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}

		return $r;
	}

	protected function getIBlockBySectionId($id)
	{
		$iblockId = 0;

		$section = \CIBlockSection::GetByID($id);
		if ($res = $section->GetNext())
		{
			$iblockId = $res["IBLOCK_ID"];
		}

		return $iblockId;
	}
	//endregion
}
