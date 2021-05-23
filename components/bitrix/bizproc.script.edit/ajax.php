<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main;
use Bitrix\Main\Engine\Response\AjaxJson;

class BizprocScriptEditAjaxController extends Main\Engine\Controller
{
	protected function init()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			throw new Main\SystemException('Module "bizproc" is not installed.');
		}

		parent::init();
	}

	public function saveScriptAction()
	{
		$params = $this->getUnsignedParameters();
		$postList = $this->getRequest()->getPostList();
		$documentType = \CBPDocument::unSignDocumentType($postList->get('documentType'));

		if (!$documentType)
		{
			return false;
		}

		$id = (int)$params['SCRIPT_ID'];
		$userId = $this->getCurrentUser()->getId();

		$canWrite =
			($id > 0)
			? \Bitrix\Bizproc\Script\Manager::canUserEditScript($id, $userId)
			: \Bitrix\Bizproc\Script\Manager::canUserCreateScript($documentType, $userId)
		;

		if (!$canWrite)
		{
			return false;
		}

		$scriptFields = $postList->toArray();
		if (is_string($scriptFields['robotsTemplate']))
		{
			$scriptFields['robotsTemplate'] = $this->fromPostJson($scriptFields['robotsTemplate']);
		}

		$result = \Bitrix\Bizproc\Script\Manager::saveScript(
			$id, $documentType, $scriptFields,
			$this->getCurrentUser()->getId()
		);

		return $result;
	}

	private function fromPostJson(string $json): array
	{
		if (!defined('BX_UTF'))
		{
			$json = Main\Text\Encoding::convertEncoding($json, LANG_CHARSET, 'UTF-8');
		}

		return Main\Web\Json::decode($json);
	}
}