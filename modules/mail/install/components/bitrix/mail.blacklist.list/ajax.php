<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Mail\BlacklistTable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Bitrix\Main;

Main\Localization\Loc::loadLanguageFile(__FILE__);

/**
 * Class MailBlacklistAjaxController
 * @package Bitrix\Main\Controller
 */
class MailBlacklistAjaxController extends Main\Engine\Controller
{
	/**
	 * @return array
	 */
	public function getPopupContentAction()
	{
		ob_start();
		$isForAllUsers = $this->isUserAdmin();
		include __DIR__ . '/templates/.default/popup_content.php';
		$html = ob_get_clean();
		return ['html' => $html];
	}

	/**@inheritdoc */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$configureActions['addMails'] = [
			'prefilters' => array_merge(
				parent::getDefaultPreFilters(),
				[
					new ActionFilter\Authentication(),
					new ActionFilter\Csrf(),
					new ActionFilter\HttpMethod(['POST']),
				]
			),
		];
		return $configureActions;
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
		if (!Loader::includeModule('mail'))
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
				$isForAllUsers ? 0 : $this->getCurrentUser()->getId()
			);
		}

		return;
	}

	/**
	 * @param $id
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function deleteAction($id)
	{
		if (!Loader::includeModule('mail'))
		{
			return;
		}
		if (!$id)
		{
			return;
		}

		$email = \Bitrix\Mail\BlacklistTable::getById($id)->fetch();
		if (!$email)
		{
			return;
		}
		if ($email['USER_ID'] == 0 && !$this->isUserAdmin())
		{
			return;
		}
		if ($email['USER_ID'] != $this->getCurrentUser()->getId())
		{
			return;
		}
		$result = \Bitrix\Mail\BlacklistTable::delete($id);
		if (!$result->isSuccess())
		{
			$this->errorCollection->add([new Main\Error('MAIL_BLACKLIST_LIST_INTERNAL_ERROR_TITLE')]);
		}
	}

	/**
	 * @param $emails
	 * @return array
	 */
	private function sanitizeEmails($emails)
	{
		$blacklist = preg_split('/[\r\n,;]+/', $emails);
		foreach ($blacklist as $i => $email)
		{
			$email = ltrim($email, " \t\n\r\0\x0b@");
			$email = rtrim($email);
			$blacklist[$i] = null;
			if (strpos($email, '@') === false)
			{
				if (check_email(sprintf('email@%s', $email)))
				{
					$blacklist[$i] = $email;
				}
			}
			else
			{
				if (check_email($email))
				{
					$blacklist[$i] = $email;
				}
			}
		}

		return array_unique(array_filter($blacklist));
	}

	/**
	 * @return bool
	 */
	private function isUserAdmin()
	{
		global $USER;
		return $USER->isAdmin() && $USER->canDoOperation('bitrix24_config');
	}
}