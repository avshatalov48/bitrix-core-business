<?php
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);

class CDiskPdfComponent extends \CBitrixComponent
{
	const DEFAULT_WIDTH = 900;
	const DEFAULT_HEIGHT = 600;

	public function onPrepareComponentParams($arParams)
	{
		$arParams['VIEWER_ID'] = $arParams['VIEWER_ID'];

		return $arParams;
	}

	public function executeComponent()
	{
		if(empty($this->arParams['PATH']))
		{
			ShowError('Path is empty');
			return;
		}

		if (isset($this->arParams['VIEWER_ID']) && strlen($this->arParams['VIEWER_ID']) > 0)
		{
			$this->arResult['ID'] = $this->arParams['VIEWER_ID'];
		}
		else
		{
			$this->arResult['ID'] = "bx_pdfjs_".$this->getComponentId();
		}

		$this->arResult['PATH'] = $this->arParams['PATH'];

		$this->arResult['TITLE'] = GetMessage('PDF_JS_DEFAULT_TITLE');
		if(isset($this->arParams['TITLE']) && !empty($this->arParams['TITLE']))
		{
			$this->arResult['TITLE'] = $this->arParams['TITLE'];
		}

		if(intval($this->arParams['WIDTH']) > 0)
		{
			$this->arResult['WIDTH'] = intval($this->arParams['WIDTH']);
		}
		else
		{
			$this->arResult['WIDTH'] = self::DEFAULT_WIDTH;
		}

		if(intval($this->arParams['HEIGHT']) > 0)
		{
			$this->arResult['HEIGHT'] = intval($this->arParams['HEIGHT']);
		}
		else
		{
			$this->arResult['HEIGHT'] = self::DEFAULT_HEIGHT;
		}

		$this->arResult['IFRAME'] = false;
		if($this->arParams['IFRAME'] === 'Y')
		{
			$this->arResult['IFRAME'] = true;
		}
		else
		{
			if($this->arParams['PRINT_URL'])
			{
				$this->arResult['PRINT_URL'] = $this->arParams['PRINT_URL'];
			}
		}

		if($this->arParams['PRINT'] === 'Y')
		{
			$this->arResult['PRINT'] = true;
		}

		$this->arResult['CSS_FILES'] = array(CUtil::GetAdditionalFileURL($this->__path.'/pdfjs/viewer.css'),);
		$this->arResult['JS_FILES'] = array(
			CUtil::GetAdditionalFileURL($this->__path.'/pdfjs/pdf.js'),
			CUtil::GetAdditionalFileURL($this->__path.'/pdfjs/l10n.js'),
			CUtil::GetAdditionalFileURL($this->__path.'/pdfjs/pdf_viewer.js')
		);
		$this->arResult['LOCALE_FILES'] = $this->__path.'/pdfjs/locale/locale.properties';
		$this->arResult['PATH_TO_WORKER'] = CUtil::GetAdditionalFileURL($this->__path.'/pdfjs/pdf.worker.js');

		$this->includeComponentTemplate();
	}

	public function getComponentId ()
	{
		return substr(md5(serialize($this->arParams)), 10).$this->randString();
	}
}