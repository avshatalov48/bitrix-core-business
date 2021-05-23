<?php

use Bitrix\Main;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\MessageType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MainMailConfirmComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER;

		if (!is_object($USER) || !$USER->isAuthorized())
			return array();

		if (!empty($this->arParams['CONFIRM_CODE']))
		{
			$this->includeComponentTemplate('confirm_code');
			return;
		}
		$this->prepareActionUrl();
		$this->prepareGridParams();
		$this->preparePost();

		$this->arParams['USER_FULL_NAME'] = static::getUserNameFormated();
		$this->arParams['MAILBOXES'] = static::prepareMailboxes();
		if(!empty($this->arParams['ADDITIONAL_SENDERS']))
		{
			$this->prepareAdditionalSenders();
		}
		$this->arParams['IS_SMTP_AVAILABLE'] = Main\ModuleManager::isModuleInstalled('bitrix24');
		$this->arParams['IS_ADMIN'] = Main\Loader::includeModule('bitrix24')
			? \CBitrix24::isPortalAdmin($USER->getId())
			: $USER->isAdmin();

		$this->includeComponentTemplate();

		return $this->arParams['MAILBOXES'];
	}
	public function prepareActionUrl()
	{
		 $this->arParams['ACTION_URL'] = $this->getPath() . '/ajax.php';
	}
	public static function prepareMailboxes()
	{
		return Main\Mail\Sender::prepareUserMailboxes();
	}

	public static function prepareMailboxesFormated()
	{
		static $mailboxesFormated;

		if (!is_null($mailboxesFormated))
			return $mailboxesFormated;

		$mailboxesFormated = array();

		foreach (static::prepareMailboxes() as $item)
			$mailboxesFormated[] = $item['formated'];

		return $mailboxesFormated;
	}

	protected static function getUserNameFormated()
	{
		global $USER;

		static $userNameFormated;

		if (!is_null($userNameFormated))
			return $userNameFormated;

		$userNameFormated = '';

		if (!is_object($USER) || !$USER->isAuthorized())
			return $userNameFormated;

		$userNameFormated = \CUser::formatName(
			\CSite::getNameFormat(),
			\Bitrix\Main\UserTable::getList(array(
				'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO'),
				'filter' => array('=ID' => $USER->getId()),
			))->fetch(),
			true, false
		);

		return $userNameFormated;
	}

	protected static function extractEmail($email)
	{
		$email = trim($email);
		if (preg_match('/.*?[<\[\(](.+?)[>\]\)].*/i', $email, $matches))
			$email = $matches[1];

		return $email;
	}
	protected function getGridColumns()
	{
		return array(
			array(
				"id" => "id",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "name",
				"name" =>  Loc::getMessage('MAIN_MAIL_CONFIRM_UI_GRID_NAME_COLUMN'),
				"sort" => "NAME",
				"default" => true
			),
			array(
				"id" => "email",
				"name" => Loc::getMessage('MAIN_MAIL_CONFIRM_UI_GRID_EMAIL_COLUMN'),
				"sort" => "NAME",
				"default" => true
			),

		);
	}
	protected function prepareGridParams()
	{
		$this->arParams['GRID_ID'] = $this->arParams['GRID_ID']??'MAIN_MAIL_CONFIRM_GRID';
		$this->arParams['COLUMNS'] = $this->arParams['COLUMNS']??$this->getGridColumns();;
	}
	protected function addMessage($type,$title,$message)
	{
		$this->arResult['MESSAGES'][] = [
			'TYPE'=> $type,
			'TITTLE'=> $title,
			'TEXT' => $message
		];
	}
	protected function preparePost()
	{
		global $USER;
		if ($this->request->isPost() && check_bitrix_sessid())
		{
			$ids = $this->request->get('ID');
			$action = $this->request->get('action_button_' . $this->arParams['GRID_ID']);

			switch ($action)
			{
				case 'delete':
					$isAdmin = Main\Loader::includeModule('bitrix24') ?
						\CBitrix24::isPortalAdmin($USER->getId()) :
						$USER->isAdmin();

					$items = Main\Mail\Internal\SenderTable::getList(array(
						'filter' => array(
							'@ID' => $ids,
						),
					))->fetchAll();

					if (empty($items))
					{
						return;
					}

					foreach ($items as $item)
					{
						if ($USER->getId() != $item['USER_ID'] && !($item['IS_PUBLIC'] && $isAdmin))
						{
							unset($this->arResult['DELETED']);
							$this->addMessage(MessageType::ERROR,
								Loc::getMessage('MAIN_MAIL_CONFIRM_POST_DELETE_ACCESS_ERROR_TITTLE'),
								Loc::getMessage('MAIN_MAIL_CONFIRM_POST_DELETE_ACCESS_ERROR'));
							return;
						}
						$this->arResult['DELETED'][] = $item['ID'];

					}

					Main\Mail\Sender::delete($this->arResult['DELETED']);
					break;

				default:
					break;
			}
		}
	}
	protected function prepareAdditionalSenders()
	{
		if(is_iterable($this->arParams['ADDITIONAL_SENDERS']))
		{
			$address = new Address();
			foreach ($this->arParams['ADDITIONAL_SENDERS'] as $sender)
			{
				if(is_string($sender['email']) && is_string($sender['name']) && isset($sender['id']))
				{
					$formated = $address->setEmail($sender['email'])
							->setName($sender['name'])
							->get();
					if(!$formated)
					{
						continue;
					}
					$this->arParams['MAILBOXES'][] = array(
						'email'=>$sender['email'],
						'name'=>$sender['name'],
						'formated'=>$formated,
						'id'=>base64_encode($formated),
						'can_delete'=>false,
					);
				}
			}
		}
	}


}
