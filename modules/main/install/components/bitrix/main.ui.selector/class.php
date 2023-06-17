<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\UI\Selector;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class CMainUiSelector extends CBitrixComponent
{
	protected function checkRequiredParams()
	{
		$errors = new \Bitrix\Main\ErrorCollection();

		foreach ($errors->toArray() as $key => $error)
		{
			ShowError($error);
		}

		return $errors->count() === 0;
	}

	protected function initParams()
	{
		if (empty($this->arParams['ITEMS_SELECTED']))
		{
			$this->arParams['ITEMS_SELECTED'] = array();
		}
		else
		{
			foreach($this->arParams['ITEMS_SELECTED'] as $key => $value)
			{
				if (preg_match('/^(\d+)$/', $key)) // numeric keys
				{
					unset($this->arParams['ITEMS_SELECTED'][$key]);
					$this->arParams['ITEMS_SELECTED'][$value] = Selector\Entities::getEntityType(array(
						'itemCode' => $value
					));
				}
			}
		}

		if (empty($this->arParams['ITEMS_UNDELETABLE']))
		{
			$this->arParams['ITEMS_UNDELETABLE'] = array();
		}

		if (empty($this->arParams['ITEMS_HIDDEN']))
		{
			$this->arParams['ITEMS_HIDDEN'] = array();
		}
		if (empty($this->arParams['CALLBACK']))
		{
			$this->arParams['CALLBACK'] = array();
		}
		if (empty($this->arParams['CONTAINER_ID']))
		{
			$this->arParams['CONTAINER_ID'] = false;
		}
		if (empty($this->arParams['BIND_ID']))
		{
			$this->arParams['BIND_ID'] = false;
		}
		if (
			empty($this->arParams['OPTIONS'])
			|| !is_array($this->arParams['OPTIONS'])
		)
		{
			$this->arParams['OPTIONS'] = array();
		}
	}

	protected function getEntities()
	{
		$this->arResult['ENTITIES'] = array();

		if (
			!empty($this->arParams['OPTIONS'])
			&& !empty($this->arParams['OPTIONS']['context'])
			&& !empty($this->arParams['ITEMS_SELECTED'])
		)
		{
			$this->arResult['ENTITIES'] = Selector\Entities::getList(array(
				'context' => $this->arParams['OPTIONS']['context'],
				'itemsSelected' => $this->arParams['ITEMS_SELECTED']
			));
		}
	}

	protected function prepareResult()
	{
		$this->getEntities();
	}

	public function executeComponent()
	{
		if ($this->checkRequiredParams())
		{
			$this->initParams();
			$this->prepareResult();

			$templatePage = $this->getTemplateName();
			if(
				empty($this->arParams['API_VERSION'])
				|| intval($this->arParams['API_VERSION']) < 2
			)
			{
				$this->setTemplateName('old');
			}
			$this->includeComponentTemplate();
		}
	}

	public static function executeComponentAjax(array $arParams = array(), array $behavior = array('DISPLAY' => true))
	{
		$result = $errorsArray = array();
		$assetHtml = "";

		if (check_bitrix_sessid())
		{
			$request = static::getRequest();

			$action = $request->get('action');
			$options = $request->get('options');
			$requestFields = $request->toArray();

			$resultOptions = array();
			$result = Selector\Actions::processAjax($action, $options, $requestFields);
			if (isset($result['dataOnly']))
			{
				unset($result['dataOnly']);
				$resultOptions['dataOnly'] = true;
			}

			static::displayAjax($result, $errorsArray, $assetHtml, $resultOptions);
		}
		else
		{
			$errorsArray[] = 'sessionError';
		}

		return array($result, $errorsArray);
	}

	protected static function getRequest()
	{
		CUtil::JSPostUnescape();
		$request = Context::getCurrent()->getRequest();
		$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

		return $request->getPostList();
	}

	protected static function displayAjax($data, $errors, $html, $options = array())
	{
		if (isset($options['dataOnly']))
		{
			$result = $data;
		}
		else
		{
			$result = array(
				'SUCCESS' => empty($errors),
				'ERROR' => $errors,
				'DATA' => $data,
				'ASSET' => $html,
			);
		}

		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		echo CUtil::PhpToJSObject($result);
	}

	public static function doFinalActions()
	{
		\CMain::finalActions();
		die();
	}
}