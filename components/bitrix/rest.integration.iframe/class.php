<?

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Application;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class RestIntegratorsIndexComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!check_bitrix_sessid())
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_INTEGRATION_IFRAME_ERROR_ACCESS_DENIED')));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH'] = $this->arParams['PATH'] ?? '';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] === 'Y' : true;
	}

	protected function preparePost()
	{

	}

	protected function getParseUrl($url)
	{
		$uri = new Uri(urldecode ($url));

		return [
			'QUERY' => $uri->getQuery(),
			'PATH' => $uri->getPath(),
			'DOMAIN' => $uri->getScheme() . '://' . $uri->getHost(),
		];
	}
	protected function prepareResult()
	{
		$queryData = $this->getParseUrl(htmlspecialcharsback($this->arParams['~PATH']));
		if ($this->arParams['SET_TITLE'])
		{
			/**@var \CAllMain */
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('REST_INTEGRATION_IFRAME_TITLE'));
		}
		$result['ERROR'] = false;
		$context = Application::getInstance()->getContext();
		$server = $context->getServer();
		$isHttps = $context->getRequest()->isHttps();
		$result['DOMAIN'] = ($isHttps ? 'https' : 'http') . '://';
		$result['DOMAIN'] .= preg_replace("/:" . (int) $server->getServerPort() . "$/", "", $server->getHttpHost());

		$isIframe = $context->getRequest()->getQuery('IFRAME');
		$result['IS_IFRAME'] = $isIframe === 'Y';

		if ($queryData['DOMAIN'] === $result['DOMAIN'])
		{
			$curl = new HttpClient(
				[
					'disableSslVerification' => $isHttps
				]
			);

			$result['RESPONSE'] =  $curl->post($queryData['DOMAIN'] . $queryData['PATH'], $queryData['QUERY']);
			try
			{
				$result['JSON_RESULT'] = Json::decode($result['RESPONSE']);
			}
			catch (ArgumentException $e)
			{
				$result['XML_RESULT'] = $result['RESPONSE'];
			}
		}
		else
		{
			$result['ERROR'] = 'WRONG_DOMAIN';
			$result['ERROR_TEXT'] = Loc::getMessage(
				'REST_INTEGRATION_WRONG_DOMAIN_ERROR',
				[
					'#DOMAIN#' => $result['DOMAIN']
				]
			);
		}

		$this->arResult = $result;

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('rest'))
		{
			$this->errors->setError(new Error('Module `rest` is not installed.'));
			$this->printErrors();

			return;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();

			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();

			return;
		}

		$this->includeComponentTemplate();
	}
}