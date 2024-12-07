<?php
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const DisableEventsCheck = true;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main;
use Bitrix\Catalog;

if (!\Bitrix\Main\Loader::includeModule('catalog'))
{
	return;
}

global $APPLICATION;

$request = Main\Context::getCurrent()->getRequest();

if (!$request->isPost() || !check_bitrix_sessid() || !Catalog\v2\AgentContract\AccessController::check())
{
	return;
}

$action = $request->get('ACTION');
if ($action === 'SAVE')
{
	$id = $request->get('ACTION_ENTITY_ID');
	$title = $request->get('TITLE');
	if (!$title)
	{
		return;
	}

	$fields = [
		'TITLE' => $title,
	];
	$result = Catalog\v2\AgentContract\Manager::update($id, $fields);
}
elseif($action === 'RENDER_IMAGE_INPUT')
{
	$contractId = (int)$request->get('ACTION_ENTITY_ID');
	$fieldName = $request->get('FIELD_NAME') ?? '';

	if ($fieldName !== '')
	{
		if ($contractId > 0)
		{
			$files = Catalog\AgentContractFileTable::getList([
				'select' => ['FILE_ID'],
				'filter' => ['=CONTRACT_ID' => $contractId]
			])->fetchAll();
			$value = array_column($files, 'FILE_ID');
		}
		else
		{
			$value = [];
		}

		Header('Content-Type: text/html; charset=' . LANG_CHARSET);
		$APPLICATION->ShowAjaxHead();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'',
			array(
				'MODULE_ID' => 'catalog',
				'MAX_FILE_SIZE' => \CUtil::Unformat(ini_get('upload_max_filesize')),
				'MULTIPLE'=> 'Y',
				'ALLOW_UPLOAD' => $request->get('ALLOW_UPLOAD') ?? 'N',
				'CONTROL_ID' => mb_strtolower($fieldName) . '_uploader',
				'INPUT_NAME' => $fieldName,
				'INPUT_NAME_UNSAVED' => $fieldName . '_tmp',
				'INPUT_VALUE' => $value
			),
		);
	}
}

$contractorsProvider = Bitrix\Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
	Bitrix\Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
);
if ($contractorsProvider)
{
	$contractorsProvider::processDocumentCardAjaxActions($action);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
die();