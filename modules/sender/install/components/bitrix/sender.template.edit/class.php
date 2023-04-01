<?

use Bitrix\Fileman;
use Bitrix\Main\Context;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderTemplateEditComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Template $entityTemplate */
	protected $entityTemplate;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? (int) $this->arParams['ID'] : 0;
		$this->arParams['ID'] = $this->arParams['ID'] ? $this->arParams['ID'] : (int) $this->request->get('ID');

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;

		$this->canEdit();
	}

	protected function preparePost()
	{
		$content = $this->request->getPostList()->getRaw('CONTENT');
		$content = Security\Sanitizer::sanitizeHtml($content, $this->entityTemplate->get('CONTENT'));
		$data = Array(
			"CONTENT"	=> $content,
			"NAME"	=> $this->request->get('NAME'),
		);

		$this->entityTemplate->mergeData($data)->save();
		$this->errors->add($this->entityTemplate->getErrors());

		if ($this->errors->isEmpty())
		{
			$path = str_replace('#id#', $this->entityTemplate->getId(), $this->arParams['PATH_TO_EDIT']);
			$uri = new Uri($path);
			if ($this->request->get('IFRAME') == 'Y')
			{
				$uri->addParams(array('IFRAME' => 'Y'));
				$uri->addParams(array('IS_SAVED' => 'Y'));
			}
			$path = $uri->getLocator();

			LocalRedirect($path);
		}
	}

	protected function prepareResult()
	{
		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$GLOBALS['APPLICATION']->SetTitle(
				$this->arParams['ID'] > 0
					?
					Loc::getMessage('SENDER_COMP_TEMPLATE_EDIT_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_COMP_TEMPLATE_EDIT_TITLE_ADD')
			);
		}

		if (!Security\Access::getInstance()->canModifyTemplates())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->entityTemplate = new Entity\Template($this->arParams['ID']);
		$this->arResult['ROW'] = $this->entityTemplate->getData();

		$content = $this->arResult['ROW']['CONTENT'];
		Loader::includeModule('fileman');
		if (Fileman\Block\Editor::isContentSupported($content))
		{
			$content = Fileman\Block\Editor::getHtmlForEditor($content);
			$this->arResult['ROW']['CONTENT'] = $content;
		}

		if (!($this->arResult['ROW']['TYPE'] ?? false))
		{
			$this->arResult['ROW']['TYPE'] = Message\ConfigurationOption::TYPE_MAIL_EDITOR;
		}

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		$this->arResult['ROW']['CONTENT_URL'] = QueryController\Manager::getActionRequestingUri(
			CommonAjax\ActionGetTemplate::NAME,
			array(
				'template_type' => 'USER',
				'template_id' => $this->arResult['ROW']['ID'] ?? null
			),
			$this->getPath() . '/ajax.php'
		);

		$this->arResult['USE_TEMPLATES'] = true;
		$this->arResult['SHOW_TEMPLATE_SELECTOR'] = empty($this->arResult['ROW']['CONTENT']);

		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';

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
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_TEMPLATE_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_TEMPLATE_VIEW;
	}
}