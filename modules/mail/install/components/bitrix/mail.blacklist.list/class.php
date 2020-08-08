<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Mail\BlacklistTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Mail\Blacklist\ItemType;

Loc::loadMessages(__FILE__);

/**
 */
class MailBlacklistListComponent extends CBitrixComponent implements Controllerable
{
	protected $gridId = 'MAIL_BLACKLIST_LIST';
	protected $filterId = 'MAIL_BLACKLIST_LIST';
	private $userId = 0;
	private $errorCollection;

	/** @inheritdoc */
	public function __construct(CBitrixComponent $component = null)
	{
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
		parent::__construct($component);
	}

	/** @inheritdoc */
	public function executeComponent()
	{
		if (!$this->runBeforeAction())
		{
			return;
		}
		$this->userId = Main\Engine\CurrentUser::get()->getId();
		$this->arResult['IFRAME'] = $this->arParams['IFRAME'] == 'Y' || $this->request->get('IFRAME') == 'Y' ? 'Y' : 'N';
		$this->arResult['CAN_DELETE'] = $this->isUserAdmin();
		$this->arResult['USER_ID'] = $this->userId;
		$this->arResult['GRID_ID'] = $this->gridId;
		$this->arResult['FILTER_ID'] = $this->filterId;

		$this->processGridActions($this->arResult['GRID_ID']);

		$this->arResult['HEADERS'] = [
			['id' => 'EMAIL', 'name' => Loc::getMessage('MAIL_BLACKLIST_LIST_COLUMN_EMAIL'), 'sort' => 'EMAIL', 'default' => true, 'editable' => false],
			['id' => 'IS_FOR_ALL_USERS', 'name' => Loc::getMessage('MAIL_BLACKLIST_LIST_COLUMN_IS_FOR_ALL_USERS'), 'sort' => 'IS_FOR_ALL_USERS', 'default' => true, 'editable' => false],
		];

		$gridOptions = new \Bitrix\Main\Grid\Options($this->arResult['GRID_ID']);
		$gridSorting = $gridOptions->getSorting(
			[
				'sort' => ['ID' => 'asc'],
				'vars' => ['by' => 'by', 'order' => 'order'],
			]
		);
		$this->arResult['SORT'] = $gridSorting['sort'];
		$this->arResult['SORT_VARS'] = $gridSorting['vars'];

		$this->arResult['FILTER'] =
			[
				[
					'id' => "TYPE", 'name' => Loc::getMessage('MAIL_BLACKLIST_LIST_FILTER_TYPE'),
					'type' => 'list',
					'items' => [
						ItemType::DOMAIN => Loc::getMessage('MAIL_BLACKLIST_LIST_FILTER_DOMAIN_TITLE'),
						ItemType::EMAIL => Loc::getMessage('MAIL_BLACKLIST_LIST_FILTER_MAIL_TITLE'),
					],
					'params' => ['multiple' => 'N'],
					'default' => true,
				],
			];

		$blacklistMails = $this->getBlacklistMails();
		$this->makeRows($blacklistMails);

		if ($this->request->getPost('hasAjaxDeleteError'))
		{
			$this->addError(Loc::getMessage('MAIL_BLACKLIST_LIST_INTERNAL_AJAX_DELETE_ERROR'));
		}
		$this->arResult['isForAllUsers'] = $this->isUserAdmin();

		$this->includeComponentTemplate();
	}


	/**
	 * @param \Bitrix\Mail\EO_Blacklist_Collection $mails
	 */
	private function makeRows($mails)
	{
		$count = 0;
		$items = [];
		$userMailboxes = array_keys(\Bitrix\Mail\MailboxTable::getUserMailboxes());
		foreach ($mails as $blacklistMail)
		{
			$fields['~ID'] = $blacklistMail->getId();
			$fields['ID'] = intval($blacklistMail->getId());

			$fields['~EMAIL'] = $blacklistMail->getItemValue();
			$fields['EMAIL'] = htmlspecialcharsbx($blacklistMail->getItemValue());

			$fields['~IS_FOR_ALL_USERS'] = $this->isAddressForAllUsers($blacklistMail) ? Loc::getMessage('MAIL_BLACKLIST_LIST_IS_FOR_ALL_USERS') : '';
			$fields['IS_FOR_ALL_USERS'] = $fields['~IS_FOR_ALL_USERS'];

			if ($this->isAddressForAllUsers($blacklistMail))
			{
				$fields['CAN_DELETE'] = $this->isUserAdmin();
			}
			else
			{
				$canDeleteAddressByUser = $blacklistMail->getUserId() > 0 && $blacklistMail->getUserId() == Main\Engine\CurrentUser::get()->getId();
				$canDeleteAddressByMailbox = $blacklistMail->getMailboxId() > 0 && in_array((int)$blacklistMail->getMailboxId(), $userMailboxes, true);
				$fields['CAN_DELETE'] = $canDeleteAddressByUser || $canDeleteAddressByMailbox;
			}
			$items[] = $fields;
			$count++;
		}
		$this->arResult['ROWS_COUNT'] = $count;

		$this->arResult['ITEMS'] = &$items;
	}

	/**
	 * @return bool
	 */
	private function isUserAdmin()
	{
		global $USER;

		if (!(is_object($USER) && $USER->IsAuthorized()))
		{
			return false;
		}

		return (bool)($USER->isAdmin() || $USER->canDoOperation('bitrix24_config'));
	}

	/**
	 * @return \Bitrix\Mail\EO_Blacklist_Collection|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getBlacklistMails()
	{
		if (!$this->userId)
		{
			return null;
		}

		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId);
		$gridFilter = $filterOptions->getFilter($this->arResult['FILTER']);

		$mailsQuery = \Bitrix\Mail\BlacklistTable::getUserAddressesListQuery($this->userId);
		if (isset($gridFilter['FIND']) && $gridFilter['FIND'])
		{
			$mailsQuery = $mailsQuery
				->where([['ITEM_VALUE', 'like', "%{$gridFilter['FIND']}%",]]);
		}
		if (isset($gridFilter['TYPE']) && in_array($gridFilter['TYPE'], [ItemType::EMAIL, ItemType::DOMAIN]))
		{
			$mailsQuery = $mailsQuery
				->where('ITEM_TYPE', $gridFilter['TYPE']);
		}

		if (!empty($this->arResult['SORT']))
		{
			if (isset($this->arResult['SORT']['EMAIL']))
			{
				$mailsQuery = $mailsQuery
					->addOrder('ITEM_VALUE', $this->arResult['SORT']['EMAIL'] == 'desc' ? 'DESC' : 'ASC');
			}
			if (isset($this->arResult['SORT']['IS_FOR_ALL_USERS']))
			{
				$mailsQuery = $mailsQuery
					->addOrder('USER_ID', $this->arResult['SORT']['IS_FOR_ALL_USERS'] == 'desc' ? 'DESC' : 'ASC');
			}
		}

		$mails = $mailsQuery
			->exec()
			->fetchCollection();

		return $mails;
	}

	private function processDelete()
	{
		$request = $this->request;
		if (!$request->getPost('ID'))
		{
			return;
		}
		foreach ($request->getPost('ID') as $emailId)
		{
			$result = $this->deleteEmailAddressById($emailId);
			if (!$result->isSuccess())
			{
				$emailEntity = $result->getData();
				$email = $emailEntity ? $emailEntity['ITEM_VALUE'] : '';
				$this->addError(Loc::getMessage('MAIL_BLACKLIST_LIST_DELETE_ERROR', ['#EMAIL#' => $email]));
				return;
			}
		}
	}

	/**
	 * @param $errorMessage
	 */
	private function addError($errorMessage)
	{
		$this->arResult["MESSAGES"][] = [
			"TYPE" => \Bitrix\Main\Grid\MessageType::ERROR,
			"TITLE" => Loc::getMessage('MAIL_BLACKLIST_LIST_INTERNAL_ERROR_TITLE'),
			"TEXT" => $errorMessage,
		];
	}

	/**
	 * @param $gridId
	 */
	private function processGridActions($gridId)
	{
		$postAction = 'action_button_' . $gridId;
		if ($this->request->isPost() && $this->request->getPost($postAction) && check_bitrix_sessid())
		{
			if ($this->request->getPost($postAction) == 'delete')
			{
				$this->processDelete();
			}
		}
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @param $emails
	 * @param bool $isForAllUsers
	 * @return void|array
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addMailsAction($emails, $isForAllUsers = false)
	{
		if (!($this->runBeforeAction() && check_bitrix_sessid()))
		{
			return;
		}
		if ($isForAllUsers && !$this->isUserAdmin())
		{
			$isForAllUsers = false;
		}
		if (!empty($emails))
		{
			$blacklistMails = $this->sanitizeEmails($emails);
			BlacklistTable::addMailsBatch($blacklistMails,
				$isForAllUsers ? 0 : \Bitrix\Main\Engine\CurrentUser::get()->getId()
			);
		}
		return;
	}

	/**
	 * @param $id
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function deleteAction($id)
	{
		if (!($id && $this->runBeforeAction() && check_bitrix_sessid()))
		{
			return;
		}
		$result = $this->deleteEmailAddressById($id);
		if (!$result->isSuccess())
		{
			$this->errorCollection->add([new Main\Error('MAIL_BLACKLIST_LIST_INTERNAL_ERROR_TITLE')]);
		}
	}

	private function runBeforeAction()
	{
		global $APPLICATION, $USER;
		if (!Loader::includeModule('mail'))
		{
			return false;
		}
		if (!(is_object($USER) && $USER->IsAuthorized()))
		{
			$APPLICATION->AuthForm('');
			return false;
		}
		return true;
	}

	private function deleteEmailAddressById($id)
	{
		$result = new Main\Result();
		$email = \Bitrix\Mail\BlacklistTable::getById($id)->fetchObject();
		if (!$email)
		{
			return $result->addError(new Main\Error(''));
		}
		if ($this->isAddressForAllUsers($email) && !$this->isUserAdmin())
		{
			return $result->addError(new Main\Error(''));
		}
		$result->setData([$email]);
		if ($email->getUserId() > 0 && $email->getUserId() != \Bitrix\Main\Engine\CurrentUser::get()->getId())
		{
			return $result->addError(new Main\Error(''));
		}
		if ($email->getMailboxId() > 0)
		{
			$mailbox = \Bitrix\Mail\MailboxTable::getUserMailbox($email->getMailboxId());
			if (!$mailbox)
			{
				return $result->addError(new Main\Error(''));
			}
		}
		$deleteResult = \Bitrix\Mail\BlacklistTable::delete($id);
		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}
		return $result;
	}

	/**
	 * @param $emails
	 * @return array
	 */
	private function sanitizeEmails($emails)
	{
		$blacklist = preg_split('/[\r\n,;]+/', $emails);
		foreach ($blacklist as $index => $email)
		{
			$email = ltrim($email, " \t\n\r\0\x0b@");
			$email = rtrim($email);
			$blacklist[$index] = null;
			if ($this->checkMail($email))
			{
				$blacklist[$index] = $email;
			}
		}

		return array_unique(array_filter($blacklist));
	}

	private function checkMail($email)
	{
		$mailToCheck = $email;
		if (mb_strpos($email, '@') === false)
		{
			$mailToCheck = sprintf('email@%s', $email);
		}
		$address = new Main\Mail\Address();
		$address->setCheckingPunycode(true);
		$address->set($mailToCheck);
		return $address->validate();
	}

	/**
	 * @param \Bitrix\Mail\Internals\Entity\BlacklistEmail $email
	 * @return bool
	 */
	private function isAddressForAllUsers($email)
	{
		return $email->getUserId() == 0 && $email->getMailboxId() == 0;
	}
}