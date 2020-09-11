<?

use Bitrix\Fileman\Block;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration\Crm\Connectors\Helper;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Security;
use Bitrix\Sender\TemplateTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::IncludeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderMessageEditorMailComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'MESSAGE';
		$this->arParams['VALUE'] = isset($this->arParams['~VALUE']) ? $this->arParams['~VALUE'] : '';
		if (!isset($this->arParams['~VALUE']))
		{
			$this->arParams['~VALUE'] = htmlspecialcharsback($this->arParams['VALUE']);
		}

		$this->arParams['HAS_USER_ACCESS'] = isset($this->arParams['HAS_USER_ACCESS'])
			?
			(bool) $this->arParams['HAS_USER_ACCESS']
			:
			Security\User::current()->canEditPhp();

		$this->arParams['USE_LIGHT_TEXT_EDITOR'] = isset($this->arParams['USE_LIGHT_TEXT_EDITOR'])
			?
			(bool) $this->arParams['USE_LIGHT_TEXT_EDITOR']
			:
			(!Security\User::current()->canEditPhp() && !Security\User::current()->canUseLpa());

		$this->arParams['SITE'] = isset($this->arParams['SITE']) ? $this->arParams['SITE'] : $this->getSiteId();
		$this->arParams['CHARSET'] = isset($this->arParams['CHARSET']) ? $this->arParams['CHARSET'] : '';
		$this->arParams['CONTENT_URL'] = isset($this->arParams['CONTENT_URL']) ? $this->arParams['CONTENT_URL'] : '';

		$this->arParams['TEMPLATE_TYPE'] = isset($this->arParams['TEMPLATE_TYPE']) ? $this->arParams['TEMPLATE_TYPE'] : null;
		$this->arParams['TEMPLATE_ID'] = isset($this->arParams['TEMPLATE_ID']) ? $this->arParams['TEMPLATE_ID'] : null;

		$this->arParams['IS_TEMPLATE_MODE'] = isset($this->arParams['IS_TEMPLATE_MODE']) ? (bool) $this->arParams['IS_TEMPLATE_MODE'] : true;
		$this->arParams['IS_TRIGGER'] = isset($this->arParams['IS_TRIGGER']) ? (bool) $this->arParams['IS_TRIGGER'] :
			false;

		if (!isset($this->arParams['PERSONALIZE_LIST']) || !is_array($this->arParams['PERSONALIZE_LIST']))
		{
			$this->arParams['PERSONALIZE_LIST'] = array();
		}
	}

	protected function prepareResult()
	{
		Loader::includeModule('fileman');

		/*
		\CJSCore::RegisterExt("sender_editor", Array(
			"js" => array("/bitrix/js/sender/editor/htmleditor.js"),
			"rel" => array()
		));
		\CJSCore::Init(array("sender_editor"));
		*/
		$this->arParams['~VALUE'] = Block\Content\SliceConverter::sanitize($this->arParams['~VALUE']);

		// personalize tags
		if (!empty($this->arParams['PERSONALIZE_LIST']))
		{
			PostingRecipientTable::setPersonalizeList($this->arParams['PERSONALIZE_LIST']);
		}

		$this->arResult['PERSONALIZE_LIST'] = array_merge(
			Helper::getPersonalizeFieldsFromConnectors($this->arParams['IS_TRIGGER']),
			PostingRecipientTable::getPersonalizeList()
		);

		// template use
		$this->arResult['TEMPLATE_USED'] = false;
		$this->arResult['DISPLAY_BLOCK_EDITOR'] = false;
		if ($this->arParams['TEMPLATE_TYPE'] && $this->arParams['TEMPLATE_ID'])
		{
			$isEmptyTemplate = $this->arParams['TEMPLATE_TYPE'] === 'BASE' && $this->arParams['TEMPLATE_ID'] === 'empty';
			$this->arResult['TEMPLATE_USED'] = !$isEmptyTemplate;
		}

		if (TemplateTable::isContentForBlockEditor($this->arParams['~VALUE']))
		{
			$this->arResult['DISPLAY_BLOCK_EDITOR'] = !$this->arParams['IS_TEMPLATE_MODE'];
		}
		elseif (Block\Content\SliceConverter::isValid($this->arParams['~VALUE']))
		{
			$this->arResult['DISPLAY_BLOCK_EDITOR'] = $this->arResult['TEMPLATE_USED'];
		}

		$url = '';
		if($this->arResult['DISPLAY_BLOCK_EDITOR'])
		{
			if($this->arResult['TEMPLATE_USED'])
			{
				$url = CommonAjax\ActionGetTemplate::getRequestingUri(
					$this->getPath() . '/ajax.php',
					array(
						'template_type' => $this->arParams['TEMPLATE_TYPE'],
						'template_id' => $this->arParams['TEMPLATE_ID']
					)
				);
			}
			else
			{
				$url = $this->arParams['CONTENT_URL'];
			}
		}

		$controllerUri = $this->getPath() . '/ajax.php';
		$saveFileUrl = Controller\Manager::getActionRequestingUri('saveFile', array(), $controllerUri);
		$previewUrl = CommonAjax\ActionPreview::getRequestingUri($controllerUri);
		$this->arResult['INPUT_ID'] = 'bxed_' . $this->arParams['INPUT_NAME'];

		$this->arResult['~BLOCK_EDITOR'] = Block\EditorMail::show(array(
			'id' => $this->arParams['INPUT_NAME'],
			'charset' => $this->arParams['CHARSET'],
			'site' => $this->arParams['SITE'],
			'own_result_id' => $this->arResult['INPUT_ID'],
			'url' => $url,
			'previewUrl' => $previewUrl,
			'saveFileUrl' => $saveFileUrl,
			'templateType' => $this->arParams['TEMPLATE_TYPE'],
			'templateId' => $this->arParams['TEMPLATE_ID'],
			'isTemplateMode' => $this->arParams['IS_TEMPLATE_MODE'],
			'isUserHavePhpAccess' => $this->arParams['HAS_USER_ACCESS'],
			'useLightTextEditor' => $this->arParams['USE_LIGHT_TEXT_EDITOR'],
		));

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
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
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