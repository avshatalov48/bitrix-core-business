<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class BizprocDebuggerComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			$this->arResult['shouldShowDebugger'] = false;

			return $this->includeComponentTemplate();
		}

		$cachedSession = \Bitrix\Bizproc\Debugger\Session\Manager::getCachedSession();
		$userId = (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());
		if (!$cachedSession || !$cachedSession->isStartedByUser($userId))
		{
			$this->arResult['shouldShowDebugger'] = false;

			return $this->includeComponentTemplate();
		}

		$session = \Bitrix\Bizproc\Debugger\Session\Manager::getActiveSession();
		if (!$session)
		{
			$this->arResult['shouldShowDebugger'] = false;

			return $this->includeComponentTemplate();
		}

		$fixedDocument = $session->getFixedDocument();
		$documentSigned =
			$fixedDocument
				? $fixedDocument->getSignedDocument()
				: CBPDocument::signParameters([$session->getParameterDocumentType(), $session->getDocumentCategoryId()])
		;

		$this->arResult = [
			'shouldShowDebugger' => true,
			'session' => $session->toArray(),
			'documentSigned' => $documentSigned,
		];

		return $this->includeComponentTemplate();
	}
}