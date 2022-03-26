<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Mail;
use Bitrix\Mail\Helper\MailboxDirectoryHelper;
use Bitrix\Mail\MailboxDirectory;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__DIR__ . '/../mail.client/class.php');

Loader::includeModule('mail');

class CMailClientConfigDirsComponent extends CBitrixComponent implements Controllerable, Errorable
{
	/** @var  ErrorCollection */
	protected $errorCollection;

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		$this->errorCollection = new Main\ErrorCollection();

		return [];
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$APPLICATION->setTitle(Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_TITLE'));

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');
			return;
		}

		$request = Context::getCurrent()->getRequest();
		$mailboxId = (int)$request->getQuery('mailboxId');

		if (!$mailboxId)
		{
			showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return;
		}

		$mailboxHelper = Mail\Helper\Mailbox::createInstance($mailboxId, false);
		if (!$mailboxHelper)
		{
			LocalRedirect('/mail');
		}

		$mailboxHelper->cacheDirs();

		$mailboxDirsHelper = $mailboxHelper->getDirsHelper();

		$this->arResult['DIRS'] = $mailboxDirsHelper->buildTreeDirs();
		$this->arResult['MAX_LEVEL'] = 1;
		$this->arResult['OUTCOME'] = $mailboxDirsHelper->getOutcome();
		$this->arResult['TRASH'] = $mailboxDirsHelper->getTrash();
		$this->arResult['SPAM'] = $mailboxDirsHelper->getSpam();
		$this->arResult['MAILBOX_ID'] = $mailboxId;
		$this->arResult['MAX_LEVEL_DIRS'] = MailboxDirectoryHelper::getMaxLevelDirs();

		ob_start();
		$this->includeComponentTemplate('dirs');
		$this->arResult['DIRS_TREE'] = ob_get_clean();

		$this->includeComponentTemplate();
	}

	public function saveAction()
	{
		$request = Context::getCurrent()->getRequest();

		$mailboxId = (int)$request->getPost("mailboxId");
		$dirs = (array)$request->getPost("dirs");
		$dirsTypes = (array)$request->getPost("dirsTypes");

		if (!$mailboxId || (empty($dirs) && empty($dirsTypes)))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
			return false;
		}

		global $USER;

		if (!Mail\Helper\Message::isMailboxOwner($mailboxId, $USER->GetID()))
		{
			$this->errorCollection[] = new Error('access denied');
			return false;
		}

		$mailboxDirsHelper = new MailboxDirectoryHelper($mailboxId);
		$mailboxDirsHelper->toggleSyncDirs($dirs);
		$mailboxDirsHelper->saveDirsTypes($dirsTypes);

		return [];
	}

	public function levelAction()
	{
		$request = Context::getCurrent()->getRequest();

		$mailboxId = (int)$request->getPost("mailboxId");
		$dir = (array)$request->getPost("dir");

		if (!$mailboxId || empty($dir) || empty($dir['dirMd5']))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));

			return false;
		}

		global $USER;

		if (!Mail\Helper\Message::isMailboxOwner($mailboxId, $USER->GetID()))
		{
			$this->errorCollection[] = new Error('access denied');
			return false;
		}

		$parent = MailboxDirectory::fetchOneByMailboxIdAndHash($mailboxId, $dir['dirMd5']);

		if ($parent == null)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('MAIL_CLIENT_MAILBOX_NOT_FOUND'));

			return false;
		}

		if ($parent->getLevel() >= MailboxDirectoryHelper::getMaxLevelDirs())
		{
			$this->errorCollection[] = new Error(Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_MAX_LEVEL_DIRS'));

			return false;
		}

		$mailboxDirsHelper = new MailboxDirectoryHelper($mailboxId);

		if (!$mailboxDirsHelper->syncChildren($parent))
		{
			$this->errorCollection = $mailboxDirsHelper->getErrors();

			return false;
		}

		$dirs = $mailboxDirsHelper->getAllLevelByParentId($parent);
		$mailboxDirsHelper->setDirs($dirs);

		$this->arResult['DIRS'] = $mailboxDirsHelper->buildTreeDirs();
		$this->arResult['MAX_LEVEL'] = 1;

		ob_start();
		$this->includeComponentTemplate('dirs');

		return ['dirs' => $this->arResult['DIRS'], 'html' => ob_get_clean()];
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}
