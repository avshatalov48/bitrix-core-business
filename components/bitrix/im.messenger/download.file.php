<?
define("IM_AJAX_INIT", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
define("STOP_STATISTICS", true);

if(isset($_GET['action']) && $_GET['action'] == 'showFile')
{
	define('BX_SECURITY_SESSION_READONLY', true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule('disk'))
{
	die;
}

if($_GET['action'] == 'showFile')
{
	if ($_GET['preview'] == 'Y')
	{
		$_GET['width'] = 500;
		$_GET['height'] = 500;
		$_GET['signature'] = \Bitrix\Disk\Security\ParameterSigner::getImageSignature($_GET['fileId'], $_GET['width'], $_GET['height']);

	}
	else
	{
		unset($_GET['width']);
		unset($_GET['height']);
	}
	unset($_GET['exact']);
}
else
{
	$_GET['action'] = 'downloadFile';
}

class ImagePreviewSizeFilter implements Bitrix\Main\Type\IRequestFilter
{
	/**
	 * @param array $values
	 * @return array
	 */
	public function filter(array $values)
	{
		if($values['get']['action'] == 'showFile')
		{
			if($values['get']['preview'] == 'Y')
			{
				$values['get']['width'] = 500;
				$values['get']['height'] = 500;
				$values['get']['signature'] = \Bitrix\Disk\Security\ParameterSigner::getImageSignature(
					$values['get']['fileId'], $values['get']['width'], $values['get']['height']
				);
			}
			else
			{
				unset($values['get']['width'], $values['get']['height']);
			}
			unset($values['get']['exact']);
		}
		else
		{
			$values['get']['action'] = 'downloadFile';
		}

		return array(
			'get' => $values['get'],
		);
	}
}
\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->addFilter(new ImagePreviewSizeFilter);

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	LocalRedirect(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri());
}

$controller = new \Bitrix\Disk\DownloadController();
$controller->setActionName($_GET['action'])->exec();

CMain::FinalActions();
die();