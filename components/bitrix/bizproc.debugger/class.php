<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class BizprocDebuggerComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['shouldShowDebugger'] = false;

		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			return $this->includeComponentTemplate();
		}

		$cachedSession = \Bitrix\Bizproc\Debugger\Session\Manager::getCachedSession();
		$userId = (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());
		if (!$cachedSession || !$cachedSession->isStartedByUser($userId))
		{
			return $this->includeComponentTemplate();
		}

		$session = \Bitrix\Bizproc\Debugger\Session\Manager::getActiveSession();
		if (!$session)
		{
			return $this->includeComponentTemplate();
		}

		$fixedDocument = $session->getFixedDocument();
		$documentSigned =
			$fixedDocument
				? $fixedDocument->getSignedDocument()
				: CBPDocument::signParameters([$session->getParameterDocumentType(), $session->getDocumentCategoryId()])
		;

		$this->arResult['shouldShowDebugger'] = true;
		$this->arResult['session'] = $session->toArray();
		$this->arResult['documentSigned'] = $documentSigned;

		return $this->includeComponentTemplate();
	}
}
