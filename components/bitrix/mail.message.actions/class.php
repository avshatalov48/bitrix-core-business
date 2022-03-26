<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

Main\Loader::includeModule('mail');

class CMailMessageActionsComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER;

		$message = false;

		if (!empty($this->arParams['MESSAGE']))
		{
			$message = $this->arParams['MESSAGE'];
		}
		else if (!empty($this->arParams['MESSAGE_ID']))
		{
			$message = Mail\MailMessageTable::getList(array(
				'runtime' => array(
					new Main\ORM\Fields\Relations\Reference(
						'MESSAGE_ACCESS',
						Mail\Internals\MessageAccessTable::class,
						array(
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.ID' => 'ref.MESSAGE_ID',
						)
					),
				),
				'select' => array(
					'ID',
					'MAILBOX_ID',
					'SUBJECT',
					new Main\ORM\Fields\ExpressionField(
						'BIND',
						'GROUP_CONCAT(DISTINCT CONCAT(%s, "-", %s))',
						array(
							'MESSAGE_ACCESS.ENTITY_TYPE',
							'MESSAGE_ACCESS.ENTITY_ID',
						)
					),
				),
				'filter' => array(
					'=ID' => (int) $this->arParams['MESSAGE_ID'],
				),
			))->fetch();

			if (!empty($message))
			{
				$message['BIND'] = explode(',', $message['BIND']);
			}
		}

		if (empty($message))
		{
			$this->includeComponentTemplate('disabled');
			return;
		}

		$accessModel = Mail\MessageAccess::createByMessageId($message['ID'], (int)$USER->GetID());
		if (!$accessModel->canModifyMessage())
		{
			$this->includeComponentTemplate('disabled');
			return;
		}

		if (!Mail\Helper\Message::hasAccess($message))
		{
			$this->includeComponentTemplate('disabled');
			return;
		}

		$this->arResult['MESSAGE'] = $message;

		$userPage = Main\Config\Option::get('socialnetwork', 'user_page', '/company/personal/', SITE_ID);

		if (empty($this->arParams['PATH_TO_USER_TASKS_TASK']))
		{
			$this->arParams['PATH_TO_USER_TASKS_TASK'] = Main\Config\Option::get(
				'tasks',
				'paths_task_user_action',
				$userPage . 'user/#user_id#/tasks/task/#action#/#task_id#/',
				SITE_ID
			);
		}

		if (empty($this->arParams['PATH_TO_USER_BLOG_POST_EDIT']))
		{
			$this->arParams['PATH_TO_USER_BLOG_POST_EDIT'] = $userPage . 'user/#user_id#/blog/edit/post/#post_id#/';
		}

		$this->arParams['PATH_TO_USER_TASKS_TASK'] = \CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_USER_TASKS_TASK'],
			array('user_id' => $USER->getId())
		);

		$this->arParams['PATH_TO_USER_BLOG_POST_EDIT'] = \CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_USER_BLOG_POST_EDIT'],
			array('user_id' => $USER->getId())
		);

		$this->arParams['CRM_AVAILABLE'] = Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();

		$this->includeComponentTemplate();
	}

}
