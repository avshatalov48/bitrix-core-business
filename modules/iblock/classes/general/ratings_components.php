<?php

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iblock/general/ratings_components.php");

class CRatingsComponentsIBlock
{	
	public static function OnGetRatingContentOwner($arParams)
	{
		if ($arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT')
		{
			$arItem = CIBlockElement::getList(array(), array('ID' => intval($arParams['ENTITY_ID'])), false, false, array('CREATED_BY'));
			if($ar = $arItem->fetch())
				return $ar['CREATED_BY'];
			else
				return 0;
		}
		elseif ($arParams['ENTITY_TYPE_ID'] == 'IBLOCK_SECTION')
		{
			$arItem = CIBlockSection::getList(array(), array('ID' => intval($arParams['ENTITY_ID'])), false, false, array('CREATED_BY'));
			if($ar = $arItem->fetch())
				return $ar['CREATED_BY'];
			else
				return 0;
		}

		return false;
	}
}
