<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MessageUserType
{

	const USER_TYPE_ID = 'mail_message';

	public static function getUserTypeDescription()
	{
		return array(
			'USER_TYPE_ID'  => static::USER_TYPE_ID,
			'CLASS_NAME'    => __CLASS__,
			'DESCRIPTION'   => Loc::getMessage('MAIL_MESSAGE_USER_TYPE_NAME'),
			'BASE_TYPE'     => 'int',
			'VIEW_CALLBACK' => array(__CLASS__, 'getPublicView'),
			'EDIT_CALLBACK' => array(__CLASS__, 'getPublicEdit'),
			'onBeforeSave'  => array(__CLASS__, 'onBeforeSave'),
			'onDelete'      => array(__CLASS__, 'onDelete'),
		);
	}

	public static function getDbColumnType($userField)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	public static function getPublicView($userField, $params)
	{
		return static::getPublicHtml($userField, $params, 'view');
	}

	public static function getPublicEdit($userField, $params)
	{
		return static::getPublicHtml($userField, $params, 'edit');
	}

	protected static function getPublicHtml($userField, $params, $mode = 'view')
	{
		global $APPLICATION;

		if (!in_array($mode, array('view', 'edit')))
		{
			$mode = 'view';
		}

		ob_start();

		foreach ((array) $userField['VALUE'] as $item)
		{
			$APPLICATION->includeComponent(
				'bitrix:mail.uf.message', '',
				array(
					'USER_FIELD' => $userField,
					'MESSAGE_ID' => (int) $item,
					'MODE' => $mode,
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
		}

		return ob_get_clean();
	}

	public static function onBeforeSaveAll($userField, $mailIds, $userId)
	{
		$previousMailsIds = (array) $userField['VALUE'];
		if ($previousMailsIds !== false)
		{
			$result = static::deleteList([
				'!@MESSAGE_ID' => $mailIds,
				'=ENTITY_UF_ID' => $userField['ID'],
				'=ENTITY_TYPE' => $userField['ENTITY_ID'],
				'=ENTITY_ID' => $userField['VALUE_ID'],
			]);
		}
		$newMailIdsToSave = array_diff($mailIds, $previousMailsIds);
		foreach ($newMailIdsToSave as $mailMessageId)
		{
			static::onBeforeSave($userField, $mailMessageId, $userId);
		}
	}

	public static function onBeforeSave($userField, $mailMessageId, $userId)
	{
		$previousMailsIds = (array) $userField['VALUE'];
		if (!in_array($mailMessageId, $previousMailsIds))
		{
			$message = MailMessageTable::getList([
				'select' => [
					'ID', 'MAILBOX_ID',
				],
				'filter' => [
					'=ID' => $mailMessageId,
				],
			])->fetch();

			if (MailboxTable::getUserMailbox($message['MAILBOX_ID'], $userId))
			{
				if ($userField['VALUE'] !== false && $userField['MULTIPLE'] === 'N')
				{
					$result = static::deleteList([
						'!=MESSAGE_ID' => $mailMessageId,
						'=ENTITY_UF_ID' => $userField['ID'],
						'=ENTITY_TYPE' => $userField['ENTITY_ID'],
						'=ENTITY_ID' => $userField['VALUE_ID'],
					]);
				}
				Internals\MessageAccessTable::add([
					'TOKEN' => md5(sprintf(
						'%u:%u:%u:%s:%s:%u',
						time(),
						$message['MAILBOX_ID'],
						$mailMessageId,
						$userField['ENTITY_ID'],
						$userField['ID'],
						$userField['VALUE_ID']
					)),
					'MAILBOX_ID' => $message['MAILBOX_ID'],
					'MESSAGE_ID' => $mailMessageId,
					'ENTITY_UF_ID' => $userField['ID'],
					'ENTITY_TYPE' => $userField['ENTITY_ID'],
					'ENTITY_ID' => $userField['VALUE_ID'],
					'SECRET' => bin2hex(Main\Security\Random::getBytes(16)),
					'OPTIONS' => [],
				]);
				static::sendEntityCreatedEvents($message, $userField);
			}
		}

		return $mailMessageId;
	}

	public static function onDelete($userField, $messageId)
	{
		$result = static::deleteList([
			'=MESSAGE_ID' => $messageId,
			'=ENTITY_UF_ID' => $userField['ID'],
			'=ENTITY_TYPE' => $userField['ENTITY_ID'],
			'=ENTITY_ID' => $userField['ENTITY_VALUE_ID'],
		]);
	}

	private static function deleteList($filter)
	{
		$entity = Internals\MessageAccessTable::getEntity();
		$connection = $entity->getConnection();
		return $connection->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Main\Entity\Query::buildFilterSql(
				$entity,
				$filter
			)
		));
	}

	private static function sendEntityCreatedEvents($message, $userField)
	{
		if (Main\Loader::includeModule('pull'))
		{
			$bindingEntityLink = '';

			if ($userField['ENTITY_ID'] === MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY
				&& Loader::includeModule('crm'))
			{
				$bindingEntity = \Bitrix\Crm\ActivityTable::query()
					->addSelect('OWNER_TYPE_ID')
					->addSelect('OWNER_ID')
					->where('ID', $userField['VALUE_ID'])
					->exec()
					->fetch();

				if ($bindingEntity)
				{
					$bindingEntityLink = \CCrmOwnerType::GetEntityShowPath($bindingEntity['OWNER_TYPE_ID'], $bindingEntity['OWNER_ID']);
				}
			}

			if ($userField['ENTITY_ID'] === MessageAccessTable::ENTITY_TYPE_TASKS_TASK
				&& Loader::includeModule('tasks'))
			{
				$bindingEntity = \Bitrix\Tasks\Internals\TaskTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=ID' => $userField['VALUE_ID'],
					],
					'limit' => 1,
				])->fetch();

				if ($bindingEntity)
				{
					global $USER;
					$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', '/company/personal/', SITE_ID);
					$bindingEntityLink = \CComponentEngine::makePathFromTemplate(
						\Bitrix\Main\Config\Option::get(
							'tasks',
							'paths_task_user_action',
							$userPage . 'user/#user_id#/tasks/task/#action#/#task_id#/',
							SITE_ID
						),
						[
							'user_id' => $USER->getId(),
							'action' => 'view',
							'task_id' => $bindingEntity['ID'],
						]
					);
				}
			}

			if ($userField['ENTITY_ID'] === MessageAccessTable::ENTITY_TYPE_BLOG_POST
				&& Loader::includeModule('blog'))
			{
				$bindingEntity = \Bitrix\Blog\PostTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=ID' => $userField['VALUE_ID'],
					],
					'limit' => 1,
				])->fetch();

				if ($bindingEntity)
				{
					global $USER;
					$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', '/company/personal/', SITE_ID);
					$bindingEntityLink = \CComponentEngine::makePathFromTemplate(
						$userPage . 'user/#user_id#/blog/#post_id#/',
						[
							'user_id' => $USER->getId(),
							'post_id' => $bindingEntity['ID'],
						]
					);
				}
			}

			\CPullWatch::addToStack(
				'mail_mailbox_' . $message['MAILBOX_ID'],
				[
					'module_id' => 'mail',
					'command' => 'messageBindingCreated',
					'params' => [
						'messageId' => $message['ID'],
						'mailboxId' => $message['MAILBOX_ID'],
						'entityType' => $userField['ENTITY_ID'],
						'entityId' => $userField['VALUE_ID'],
						'bindingEntityLink' => $bindingEntityLink,
					],
				]
			);
		}
		$event = new Main\Event(
			'mail',
			'onBeforeUserFieldSave',
			[
				'mailbox_id' => $message['MAILBOX_ID'],
				'message_id' => $message['ID'],
				'entity_uf_id' => $userField['ID'],
				'entity_type' => $userField['ENTITY_ID'],
				'entity_id' => $userField['VALUE_ID'],
			]
		);
		$event->send();
	}
}