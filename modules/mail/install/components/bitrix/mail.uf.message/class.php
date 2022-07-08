<?php

use Bitrix\Main;
use Bitrix\Main\Security;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('mail');

class CMailUfMessageComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER;

		$message = Mail\MailMessageTable::getList(array(
			'select' => array(
				'*',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
			),
			'filter' => array(
				'=ID' => (int) $this->arParams['MESSAGE_ID'],
			),
		))->fetch();

		if (empty($message))
		{
			if ('view' == $this->arParams['MODE'])
			{
				$this->includeComponentTemplate('empty');
			}
			else
			{
				$this->includeComponentTemplate('error');
			}

			return;
		}

		$userField = $this->arParams['USER_FIELD'];
		if ($userField['ENTITY_VALUE_ID'] > 0)
		{
			$access = Mail\Internals\MessageAccessTable::getList(array(
				'filter' => array(
					'=MAILBOX_ID' => $message['MAILBOX_ID'],
					'=MESSAGE_ID' => $message['ID'],
					'=ENTITY_UF_ID' => $userField['ID'],
					'=ENTITY_ID' => $userField['ENTITY_VALUE_ID'],
				),
				'limit' => 1,
			))->fetch();
		}

		if (empty($access))
		{
			if (!Mail\MailboxTable::getUserMailbox($message['MAILBOX_ID']))
			{
				$this->includeComponentTemplate('denied');
				return;
			}
		}

		Mail\Helper\Message::prepare($message);

		if (!empty($access))
		{
			$signer = new Security\Sign\Signer(new Security\Sign\HmacAlgorithm('md5'));

			$message['__href'] = \CHTTP::urlAddParams(
				$message['__href'],
				array(
					'mail_uf_message_token' => sprintf(
						'%s:%s',
						$access['TOKEN'],
						$signer->getSignature($access['SECRET'], sprintf('user%u', $USER->getId()))
					),
				),
				array(
					'encode' => true,
				)
			);
		}

		$message['__thread_new'] = 0;

		$newCount = Mail\MailMessageTable::getList(array(
			'runtime' => array(
				new Main\Entity\ReferenceField(
					'MESSAGE_UID',
					'Bitrix\Mail\MailMessageUidTable',
					array(
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID'         => 'ref.MESSAGE_ID',
					),
					array(
						'join_type' => 'INNER',
					)
				),
				new Main\Entity\ReferenceField(
					'CLOSURE',
					Mail\Internals\MessageClosureTable::class,
					array(
						'=this.ID' => 'ref.MESSAGE_ID',
					)
				),
			),
			'select' => array(
				new Main\Entity\ExpressionField('NEW_COUNT', 'COUNT(DISTINCT %s)', 'ID'),
			),
			'filter' => array(
				'=MAILBOX_ID' => $message['MAILBOX_ID'],
				'=CLOSURE.PARENT_ID' => $message['ID'],
				'!@MESSAGE_UID.IS_SEEN' => array('Y', 'S'), // @TODO: index?
			),
		))->fetch();

		if (!empty($newCount) && $newCount['NEW_COUNT'] > 0)
		{
			$message['__thread_new'] = (int) $newCount['NEW_COUNT'];
		}

		if (empty($message['BODY']) && !empty($message['BODY_HTML']))
		{
			$message['BODY'] = html_entity_decode(
				htmlToTxt($message['BODY_HTML']),
				ENT_QUOTES | ENT_HTML401,
				LANG_CHARSET
			);
		}

		$this->arResult['MESSAGE'] = $message;

		$this->includeComponentTemplate();
	}

}
