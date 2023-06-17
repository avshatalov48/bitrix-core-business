<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Section extends Controller
{
	//region Actions
	public function getFieldsAction(): array
	{
		return ['SECTION' => $this->getViewFields()];
	}

	/**
	 * @param $select
	 * @param $filter
	 * @param $order
	 * @param PageNavigation $pageNavigation
	 * @return Page|null
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): ?Page
	{
		$r = $this->checkPermissionIBlockSectionList($filter['IBLOCK_ID']);
		if($r->isSuccess())
		{
			$result = [];

			$select = empty($select)? ['*']:$select;
			$order = empty($order)? ['ID'=>'ASC']:$order;

			if (isset($filter['IBLOCK_SECTION_ID']))
			{
				$filter['SECTION_ID'] = $filter['IBLOCK_SECTION_ID'];
				unset($filter['IBLOCK_SECTION_ID']);
			}

			$r = \CIBlockSection::GetList($order, $filter, false, $select, self::getNavData($pageNavigation->getOffset()));
			while ($l = $r->fetch())
				$result[] = $l;

			return new Page('SECTIONS', $result, function() use ($filter)
			{
				return \CIBlockSection::GetCount($filter);
			});
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}

	}

	public function getAction($id)
	{
		$r = $this->checkPermissionIBlockSectionGet($id);
		if($r->isSuccess())
		{
			$r = $this->exists($id);
			if($r->isSuccess())
			{
				return ['SECTION'=>$this->get($id)];
			}
			else
			{
				$this->addErrors($r->getErrors());
				return null;
			}
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function addAction($fields)
	{
		$r = $this->checkPermissionIBlockSectionAdd($fields['IBLOCK_ID']);
		if($r->isSuccess())
		{
			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockSectionSectionBindUpdate($fields['IBLOCK_SECTION_ID']);
			}
		}

		if($r->isSuccess())
		{
			$id = 0;
			$section = new \CIBlockSection();

			$r = $this->addValidate($fields);
			if($r->isSuccess())
			{
				$id = $section->Add($fields);
				if($section->LAST_ERROR<>'')
				{
					$r->addError(new Error($section->LAST_ERROR));
				}
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['SECTION'=>$this->get($id)];
		}
	}

	public function updateAction($id, array $fields)
	{
		$r = $this->checkPermissionIBlockSectionUpdate($id);
		if($r->isSuccess())
		{
			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockSectionSectionBindUpdate($fields['IBLOCK_SECTION_ID']);
			}
		}

		if($r->isSuccess())
		{
			$section = new \CIBlockSection();

			$r = $this->exists($id);
			if($r->isSuccess())
			{
				$r = $this->updateValidate($fields+['ID'=>$id]);
				if($r->isSuccess())
				{
					$section->Update($id, $fields);
					if($section->LAST_ERROR<>'')
					{
						$r->addError(new Error($section->LAST_ERROR));
					}
				}
			}
		}

		if($r->isSuccess())
		{
			return ['SECTION'=>$this->get($id)];
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
		if($r->isSuccess())
		{
			$r = $this->exists($id);
			if($r->isSuccess())
			{
				if (!\CIBlockSection::Delete($id))
				{
					if ($ex = self::getApplication()->GetException())
						$r->addError(new Error($ex->GetString(), $ex->GetId()));
					else
						$r->addError(new Error('delete section error'));
				}
			}
		}

		if($r->isSuccess())
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
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Section is not exists'));

		return $r;
	}

	protected function get($id)
	{
		return \CIBlockSection::GetByID($id)->Fetch();
	}

	protected function addValidate($fields)
	{
		return new Result();
	}

	protected function updateValidate($fields)
	{
		return new Result();
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
			$r->addError(new Error('Access Denied', 200040300010));
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

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_EDIT);
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}
		return $r;
	}

	protected function checkPermissionIBlockSectionSectionBindModify($iblockId, $iblockSectionId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $iblockSectionId, self::IBLOCK_SECTION_SECTION_BIND); //access update
		else
			$bBadBlock = true;

		if($bBadBlock)
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
		$bBadBlock = !\CIBlockElementRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_SECTION_DELETE); //access delete

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300050));
		}

		return $r;
	}

	protected function checkPermissionIBlockSectionGet($sectionId)
	{
		$r = new Result();

		$iblockId = $this->getIBlockBySectionId($sectionId);
		$arIBlock = \CIBlock::GetArrayByID($iblockId);

		if($arIBlock)
			$bBadBlock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $sectionId, self::IBLOCK_SECTION_READ);
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}
		return $r;
	}

	protected function checkPermissionIBlockSectionList($iblockId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_READ);
		else
			$bBadBlock = true;

		if($bBadBlock)
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
