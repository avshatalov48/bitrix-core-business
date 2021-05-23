<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\PostFiles;
use Bitrix\Sender\Internals\QueryController as Controller;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = CommonAjax\ActionGetTemplate::get()
	->addChecker(CommonAjax\Checker::getViewLetterPermissionChecker());
$actions[] = CommonAjax\ActionPreview::get()
	->addChecker(CommonAjax\Checker::getViewLetterPermissionChecker());
$actions[] = Controller\Action::create('getDemoUnsubscribePage')
	->setRequestMethodGet()
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$content = $response->initContentHtml();
			$content->set('Demo unsubscribe page.');
		}
	)
	->addChecker(CommonAjax\Checker::getViewLetterPermissionChecker());
$actions[] = Controller\Action::create('saveFile')
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$result = array(
				'error' => false,
				'errorText' => '',
				'data' => array(
					'list' => array(),
				)
			);
			$fileList = array();

			//New from media library and file structure
			$isCheckedSuccess = false;
			$requestFiles = $request->getPost('NEW_FILE_EDITOR');
			if($requestFiles && is_array($requestFiles))
			{
				foreach($requestFiles as $index=>$value)
				{
					if(is_array($value))
					{
						$filePath = urldecode($value['tmp_name']);
					}
					else
					{
						continue;
					}

					$checkResult = PostFiles::checkAbsolutePath($filePath);

					if(is_null($checkResult))
					{
						continue;
					}
					$isCheckedSuccess = $checkResult['isSuccess'];

					if($isCheckedSuccess)
					{
						$io = \CBXVirtualIo::GetInstance();
						$fileList[$filePath] = \CFile::MakeFileArray($io->GetPhysicalName($checkResult['absPath']));
						if(isset($value['name']))
						{
							$fileList[$filePath]['name'] = $value['name'];
						}
					}
					else
					{
						$result['data']['list'][] = array(
							'tmp' => $filePath,
							'path' => ''
						);
					}
				}
			}


			foreach($fileList as $tmpFileName => $file)
			{
				$fid = \Bitrix\Sender\Internals\PostFiles::saveFile($file);
				if($fid > 0 && ($filePath = \CFile::GetPath($fid)) && $filePath <> '')
				{
					$result['data']['list'][] = array(
						'tmp' => $tmpFileName,
						'path' => $filePath
					);
				}
			}

			if (!$isCheckedSuccess && count($fileList) == 0)
			{
				$result['error'] = true;
				$result['errorText'] = GetMessage("ACCESS_DENIED");
			}

			$response->initContentJson()->set($result);
		}
	)
	->addChecker(CommonAjax\Checker::getModifyLetterPermissionChecker());

Controller\Listener::create()->setActions($actions)->run();