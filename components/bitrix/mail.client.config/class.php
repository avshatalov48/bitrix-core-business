<?php

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Helper\Mailbox\MailboxConnector;
use Bitrix\Main;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\Mail\Sender;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;
use Bitrix\Mail\Helper\LicenseManager;
use Bitrix\Main\Config\Configuration;
use Bitrix\Mail\MailServicesTable;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Main\Mail\Sender\UserSenderDataProvider;
use Bitrix\Main\Engine\CurrentUser;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__DIR__ . '/../mail.client/class.php');

\Bitrix\Main\Loader::includeModule('mail');

class CMailClientConfigComponent extends CBitrixComponent implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	private const NEGATIVE_ANSWER = 'N';
	private const POSITIVE_ANSWER = 'Y';
	private const DEFAULT_SEND_LIMIT = 250;

	public function configureActions()
	{
		$this->errorCollection = new Main\ErrorCollection();

		return array();
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');
			return;
		}

		$result = \Bitrix\Main\SiteTable::getList();
		while (($site = $result->fetch()) !== false)
		{
			\Bitrix\Mail\Internals\MailServiceInstaller::checkInstallComplete($site["LID"]);
		}

		switch ($this->arParams['VARIABLES']['act'])
		{
			case 'new':
				$this->editAction(true);
				break;
			case 'edit':
				$this->editAction(false);
				break;
			default:
				$this->defaultAction();
		}
	}

	protected function defaultAction()
	{
		global $APPLICATION;

		$APPLICATION->setTitle(Loc::getMessage('MAIL_CLIENT_CONFIG_TITLE'));

		$this->arResult['MAX_ALLOWED_CONNECTED_MAILBOXES'] = LicenseManager::getUserMailboxesLimit();
		$this->arResult['CAN_CONNECT_NEW_MAILBOX'] = MailboxConnector::canConnectNewMailbox();
		$this->arParams['DEFAULT_SEND_LIMIT'] = self::DEFAULT_SEND_LIMIT;

		$this->includeComponentTemplate();
	}

	protected function editAction($new = true)
	{
		global $APPLICATION, $USER;

		$APPLICATION->setTitle(Loc::getMessage($new ? 'MAIL_CLIENT_CONFIG_TITLE' : 'MAIL_CLIENT_CONFIG_EDIT_TITLE'));

		$this->setIsSmtpAvailable();

		if ($new)
		{
			if (!MailboxConnector::canConnectNewMailbox())
			{
				showError(Loc::getMessage('MAIL_CLIENT_DENIED'));
				return;
			}

			$serviceId = $_REQUEST['id'];
		}
		else
		{
			$mailbox = Mail\MailboxTable::getList(array(
				'filter' => array(
					'=ID' => $_REQUEST['id'],
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				),
			))->fetch();

			if (empty($mailbox))
			{
				showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
				return;
			}

			if ($USER->getId() != $mailbox['USER_ID'] && !$USER->isAdmin() && !$USER->canDoOperation('bitrix24_config'))
			{
				showError(Loc::getMessage('MAIL_CLIENT_DENIED'));
				return;
			}

			foreach (array($mailbox['EMAIL'], $mailbox['NAME'], $mailbox['LOGIN']) as $item)
			{
				$address = new \Bitrix\Main\Mail\Address($item);
				if ($address->validate())
				{
					$mailbox['EMAIL'] = $address->getEmail();
					break;
				}
			}

			if ($this->arParams['IS_SMTP_AVAILABLE'])
			{
				$senders = SenderTable::query()
					->setSelect(['ID', 'PARENT_MODULE_ID', 'OPTIONS'])
					->where('IS_CONFIRMED', true)
					->where('EMAIL', $mailbox['EMAIL'])
					->where('USER_ID', $mailbox['USER_ID'])
					->fetchAll()
				;

				foreach ($senders as $sender)
				{
					if (
						(!empty($sender['OPTIONS']['smtp']['server']) && empty($sender['OPTIONS']['smtp']['encrypted']))
						&& (
							$sender['PARENT_MODULE_ID'] === 'mail'
							|| ($sender['OPTIONS']['source'] === 'mail.client.config')
						)
					)
					{
						if ($sender['PARENT_MODULE_ID'] !== 'mail')
						{
							SenderTable::update(
								$sender['ID'],
								[
									'PARENT_MODULE_ID' => 'mail',
									'PARENT_ID' => $mailbox['ID']
								]
							);
						}

						$mailbox['__smtp'] = $sender['OPTIONS']['smtp'];
						break;
					}
				}
			}

			if (in_array('crm_connect', (array) $mailbox['OPTIONS']['flags']))
			{
				$mailbox['__crm'] = true;
			}

			$this->arParams['PASSWORD_PLACEHOLDER'] = '000000000000';

			$this->arParams['MAILBOX'] = $mailbox;

			$serviceId = $mailbox['SERVICE_ID'];
		}

		$this->arParams['IS_CALENDAR_AVAILABLE'] = \Bitrix\Main\Loader::includeModule('calendar');
		$this->arParams['IS_ICAL_CHECK'] = $mailbox['OPTIONS']['ical_access'] === self::POSITIVE_ANSWER;


		$res = Mail\MailServicesTable::getList(array(
			'filter' => array(
				'=ID' => $serviceId,
				'=SITE_ID' => SITE_ID,
			),
		));

		$this->arParams['SERVICE'] = array();
		if ($service = $res->fetch())
		{
			$this->arParams['SERVICE'] = [
				'active' => $service['ACTIVE'],
				'id' => $service['ID'],
				'type' => $service['SERVICE_TYPE'],
				'name' => $service['NAME'],
				'link' => $service['LINK'],
				'icon' => Mail\MailServicesTable::getIconSrc($service['NAME'], $service['ICON']),
				'server' => $service['SERVER'],
				'port' => $service['PORT'],
				'encryption' => $service['ENCRYPTION'],
				'upload_outgoing' => $service['UPLOAD_OUTGOING'],
			];
			$this->arParams['SERVICE'] = self::prepareMailServices([$this->arParams['SERVICE']])[0];

			$serviceSmtp = [];
			if(!empty($service['SMTP_SERVER']))
			{
				$serviceSmtp['server'] = $service['SMTP_SERVER'];
			}
			if(!empty($service['SMTP_PORT']))
			{
				$serviceSmtp['port'] = $service['SMTP_PORT'];
			}
			$serviceSmtp['login'] = ($service['SMTP_LOGIN_AS_IMAP'] === 'Y');
			$serviceSmtp['password'] = ($service['SMTP_PASSWORD_AS_IMAP'] === 'Y');
			$this->arParams['SERVICE']['smtp'] = $serviceSmtp;

		}
		else if ($new)
		{
			showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return;
		}

		if (!$new)
		{
			$this->arParams['SERVICE']['oauth'] = Mail\Helper\OAuth::getInstanceByMeta($mailbox['PASSWORD']);
			$this->arParams['SERVICE']['oauth_user'] = Mail\Helper\OAuth::getUserDataByMeta($mailbox['PASSWORD']);
			$this->arParams['SERVICE']['oauth_user']['email'] = $mailbox['EMAIL'];
		}

		if (empty($this->arParams['SERVICE']['oauth']))
		{
			if ($new || empty($mailbox['PASSWORD']))
			{
				$this->arParams['SERVICE']['oauth'] = Mail\MailServicesTable::getOAuthHelper($service);
			}
		}
		$this->arParams['SERVICE']['oauth_smtp_enabled'] = !empty($this->arParams['SERVICE']['oauth'])
			&& MailboxConnector::isOauthSmtpEnabled($this->arParams['SERVICE']['name'] ?? '');

		if (!empty($this->arParams['SERVICE']['oauth']) && empty($this->arParams['SERVICE']['oauth_smtp_enabled']))
		{
			$this->arParams['SERVICE']['smtp']['password'] = false;
		}

		$ownerId = $new ? $USER->getId() : $mailbox['USER_ID'];
		$access = array(
			'users' => array(
				sprintf('U%u', $ownerId) => $ownerId,
			),
			'department' => array(),
		);

		if (!$new)
		{
			$res = Mail\Internals\MailboxAccessTable::getList(array(
				'filter' => array(
					'=MAILBOX_ID' => $mailbox['ID'],
					'TASK_ID' => 0,
				)
			));

			while ($item = $res->fetch())
			{
				if (preg_match('/^(U|DR)(\d+)$/', $item['ACCESS_CODE'], $matches))
				{
					if ('U' == $matches[1])
					{
						$access['users'][$item['ACCESS_CODE']] = $matches[2];
					}
					else if ('DR' == $matches[1])
					{
						$access['department'][$item['ACCESS_CODE']] = array(
							'id' => $item['ACCESS_CODE'],
							'entityId' => $matches[2],
						);
					}
				}
			}
		}

		$res = Main\UserTable::getList(array(
			'filter' => array(
				'@ID' => array_values($access['users']),
			),
		));

		while ($item = $res->fetch())
		{
			$id = sprintf('U%u', $item['ID']);
			$access['users'][$id] = array(
				'id'       => $id,
				'entityId' => $item['ID'],
				'name'     => \CUser::formatName(\CSite::getNameFormat(), $item, true),
				'avatar'   => '',
				'desc'     => $item['WORK_POSITION'] ?: $item['PERSONAL_PROFESSION'] ?: '&nbsp;'
			);
		}

		$this->arParams['ACCESS_LIST'] = array_map(
			function ($list)
			{
				return array_filter($list, 'is_array');
			},
			$access
		);

		if (\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			$this->arParams['COMPANY_STRUCTURE'] = \CSocNetLogDestination::getStucture();
		}

		$this->arParams['CRM_AVAILABLE'] = false;
		if (Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled())
		{
			$this->arParams['CRM_AVAILABLE'] = $USER->isAdmin() || $USER->canDoOperation('bitrix24_config')
				|| \COption::getOptionString('intranet', 'allow_external_mail_crm', 'Y', SITE_ID) == 'Y';

			if ($this->arParams['CRM_AVAILABLE'])
			{
				$this->arParams['NEW_ENTITY_LIST'] = array(
					\CCrmOwnerType::LeadName    => \CCrmOwnerType::getDescription(\CCrmOwnerType::Lead),
					\CCrmOwnerType::ContactName => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact),
				);
				$this->arParams['DEFAULT_NEW_ENTITY_IN']  = \CCrmOwnerType::LeadName;
				$this->arParams['DEFAULT_NEW_ENTITY_OUT'] = \CCrmOwnerType::ContactName;

				$this->arParams['LEAD_SOURCE_LIST'] = \CCrmStatus::getStatusList('SOURCE');
				reset($this->arParams['LEAD_SOURCE_LIST']);
				$this->arParams['DEFAULT_LEAD_SOURCE'] = key($this->arParams['LEAD_SOURCE_LIST']);
				if (is_set($this->arParams['LEAD_SOURCE_LIST'], 'EMAIL'))
				{
					$this->arParams['DEFAULT_LEAD_SOURCE'] = 'EMAIL';
				}
				else if (is_set($this->arParams['LEAD_SOURCE_LIST'], 'OTHER'))
				{
					$this->arParams['DEFAULT_LEAD_SOURCE'] = 'OTHER';
				}

				if (!$new)
				{
					$options = $mailbox['OPTIONS'];

					if (!array_key_exists('flags', $options) || !is_array($options['flags']))
					{
						$options['flags'] = array();
					}

					if ($mailbox['__crm'])
					{
						// backward compatibility
						if (!array_intersect(array('crm_deny_new_lead', 'crm_deny_entity_in', 'crm_deny_entity_out'), $options['flags']))
						{
							$this->arParams['DEFAULT_NEW_ENTITY_IN'] = \CCrmOwnerType::LeadName;
							$this->arParams['DEFAULT_NEW_ENTITY_OUT'] = \CCrmOwnerType::LeadName;
						}
					}

					if (!empty($options['crm_new_entity_in']) && array_key_exists($options['crm_new_entity_in'], $this->arParams['NEW_ENTITY_LIST']))
					{
						$this->arParams['DEFAULT_NEW_ENTITY_IN'] = $options['crm_new_entity_in'];
					}
					if (!empty($options['crm_new_entity_out']) && array_key_exists($options['crm_new_entity_out'], $this->arParams['NEW_ENTITY_LIST']))
					{
						$this->arParams['DEFAULT_NEW_ENTITY_OUT'] = $options['crm_new_entity_out'];
					}

					if (!empty($options['crm_lead_source']) && array_key_exists($options['crm_lead_source'], $this->arParams['LEAD_SOURCE_LIST']))
					{
						$this->arParams['DEFAULT_LEAD_SOURCE'] = $options['crm_lead_source'];
					}

					if (!empty($options['crm_lead_resp']))
					{
						$this->arParams['CRM_QUEUE'] = \Bitrix\Main\UserTable::getList(array(
							'filter' => array(
								'ID' => $options['crm_lead_resp'],
							),
						))->fetchAll();

						$order = array_flip(array_values(array_unique($options['crm_lead_resp'])));
						usort($this->arParams['CRM_QUEUE'], function ($a, $b) use (&$order)
						{
							return isset($order[$a['ID']], $order[$b['ID']]) ? $order[$a['ID']]-$order[$b['ID']] : 0;
						});
					}

					$this->arParams['NEW_LEAD_FOR'] = is_array($options['crm_new_lead_for']) ? $options['crm_new_lead_for'] : array();
				}

				if (empty($this->arParams['CRM_QUEUE']))
				{
					$this->arParams['CRM_QUEUE'] = \Bitrix\Main\UserTable::getList(array(
						'filter' => array(
							'ID' => $new ? $USER->getId() : $mailbox['USER_ID'],
						),
					))->fetchAll();
				}
			}
		}
		$this->arResult['FORBIDDEN_TO_SHARE_MAILBOX'] = false;
		$sharedMailboxesLimit = LicenseManager::getSharedMailboxesLimit();
		if ($sharedMailboxesLimit >= 0)
		{
			$sharedMailboxesIds = Mail\Helper\Mailbox\SharedMailboxesManager::getSharedMailboxesIds();
			if (count($sharedMailboxesIds) >= $sharedMailboxesLimit
				&& (!empty($mailbox) ? (!in_array((int)$mailbox['ID'], $sharedMailboxesIds, true)) : true))
			{
				$this->arResult['FORBIDDEN_TO_SHARE_MAILBOX'] = true;
			}
		}
		if (!empty($mailbox))
		{
			$mailboxSyncManager = new Mail\Helper\Mailbox\MailboxSyncManager($mailbox['USER_ID']);
			$this->arResult['LAST_MAIL_CHECK_DATE'] = $mailboxSyncManager->getLastMailboxSyncTime($mailbox['ID']);
			$this->arResult['LAST_MAIL_CHECK_STATUS'] = $mailboxSyncManager->getLastMailboxSyncIsSuccessStatus($mailbox['ID']);
		}

		$this->arResult['MICROSOFT_SERVICE_NAMES'] = $this->getMicrosoftServiceNames();

		$this->arResult['IS_SMTP_SENDER_ADDED'] = ($_REQUEST['smtp'] ?? '') === 'Y';
		$this->arResult['LOCK_SMTP'] = $this->isSmtpSwitcherDisabled();

		$this->arParams['DEFAULT_SEND_LIMIT'] = self::DEFAULT_SEND_LIMIT;

		$this->arParams['SERVICE']['IS_SMTP_SWITCHER_CHECKED'] = $this->arResult['LOCK_SMTP'] === true || $this->isSmtpSwitcherChecked();
		$this->arParams['SENDER_NAME'] =  $this->getSenderName($mailbox['USERNAME'] ?? '', $mailbox['USER_ID'] ?? null);

		$this->includeComponentTemplate('edit');
	}

	public function checkAvailabilityEMailAction($serviceId,$email,$oauthUid)
	{
		$service = Mail\MailServicesTable::getList(array(
			'filter' => array(
				'=ID'          => $serviceId,
				'=ACTIVE'       => 'Y',
				'=SERVICE_TYPE' => 'imap',
			),
		))->fetch();

		if (!empty($service))
		{
			$mailbox = [
				'USE_TLS' => $service['ENCRYPTION'],
				'LOGIN' => $email,
				'SERVER' => $service['SERVER'],
				'PORT' => $service['PORT'],
			];

			if ($oauthHelper = Mail\MailServicesTable::getOAuthHelper($service))
			{
				$oauthHelper->getStoredToken($oauthUid);
				$mailbox['PASSWORD'] = $oauthHelper->buildMeta();

				if(\Bitrix\Mail\Helper::getImapUnseen($mailbox) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function setIsSmtpAvailable()
	{
		$defaultMailConfiguration = Configuration::getValue("smtp");
		$this->arParams['IS_SMTP_AVAILABLE'] = Main\ModuleManager::isModuleInstalled('bitrix24')
			|| $defaultMailConfiguration['enabled'];
	}

	public function saveAction($fields)
	{
		global $USER;

		$this->setIsSmtpAvailable();

		if (!empty($fields['site_id']))
		{
			$currentSite = \CSite::getById($fields['site_id'])->fetch();
		}

		if (empty($currentSite))
		{
			$this->error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
			return;
		}

		if (!empty($fields['service_id']))
		{
			$service = Mail\MailServicesTable::getList(array(
				'filter' => array(
					'=ID'          => $fields['service_id'],
					'=SERVICE_TYPE' => 'imap',
				),
			))->fetch();
		}

		if (empty($service) || $service['SITE_ID'] != $currentSite['LID'])
		{
			$this->error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
			return;
		}

		if ($fields['mailbox_id'] > 0)
		{
			$mailbox = Mail\MailboxTable::getList(array(
				'filter' => array(
					'=ID' => $fields['mailbox_id'],
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				),
			))->fetch();

			if ($USER->getId() != $mailbox['USER_ID'] && !$USER->isAdmin() && !$USER->canDoOperation('bitrix24_config'))
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_DENIED'));
				return;
			}

			if (!empty($mailbox))
			{
				if ($mailbox['SERVICE_ID'] != $service['ID'])
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
					return;
				}

				foreach (array($mailbox['EMAIL'], $mailbox['NAME'], $mailbox['LOGIN']) as $item)
				{
					$address = new \Bitrix\Main\Mail\Address($item);
					if ($address->validate())
					{
						$mailbox['EMAIL'] = $address->getEmail();
						break;
					}
				}
			}
		}

		if (empty($mailbox))
		{
			if ($service['ACTIVE'] !== 'Y')
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
				return;
			}
			if (!MailboxConnector::canConnectNewMailbox())
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_DENIED'));
				return;
			}

			$mailboxData = array(
				'SERVER'   => $service['SERVER'] ?: trim($fields['server_imap']),
				'PORT'     => $service['PORT'] ?: (int) $fields['port_imap'],
				'USE_TLS'  => $service['ENCRYPTION'] ?: $fields['ssl_imap'],
				'LINK'     => $service['LINK'] ?: trim($fields['link']),
				'EMAIL'    => trim($fields['email']),
				'NAME'     => trim($fields['name']),
				'USERNAME' => trim($fields['sender']),
				'LOGIN'    => $fields['login_imap'],
				'PASSWORD' => $fields['pass_imap'],
				'PERIOD_CHECK' => 60 * 24,
				'OPTIONS'  => array(
					'flags'     => array(),
					'sync_from' => time(),
					'crm_sync_from' => time(),
					'activateSync' => false,
				),
			);

			if ('N' == $service['UPLOAD_OUTGOING'] || empty($service['UPLOAD_OUTGOING']) && empty($fields['upload_outgoing']))
			{
				$mailboxData['OPTIONS']['flags'][] = 'deny_upload';
			}
		}
		else
		{
			$mailboxData = array(
				'SERVER'   => $service['SERVER'] ? $mailbox['SERVER'] : trim($fields['server_imap']),
				'PORT'     => $service['PORT'] ? $mailbox['PORT'] : (int) $fields['port_imap'],
				'USE_TLS'  => $service['ENCRYPTION'] ? $mailbox['USE_TLS'] : $fields['ssl_imap'],
				'LINK'     => $service['LINK'] ? $mailbox['LINK'] : trim($fields['link']),
				'EMAIL'    => $mailbox['EMAIL'] ?: trim($fields['email']),
				'NAME'     => trim($fields['name']),
				'USERNAME' => trim($fields['sender']),
				'LOGIN'    => $mailbox['LOGIN'],
				'PASSWORD' => $mailbox['PASSWORD'],
				'USER_ID' => $mailbox['USER_ID'],
				'PERIOD_CHECK' => 60 * 24,
				'OPTIONS'  => (array) $mailbox['OPTIONS'],
			);

			if ($fields['pass_imap'] <> '' && $fields['pass_imap'] != $fields['pass_placeholder'])
			{
				$mailboxData['PASSWORD'] = $fields['pass_imap'];
			}

			$mailboxData['OPTIONS']['flags'] = array_diff(
				(array) $mailboxData['OPTIONS']['flags'],
				array(
					'crm_preconnect', 'crm_connect', 'crm_public_bind',
					'crm_deny_new_lead', 'crm_deny_entity_in', 'crm_deny_entity_out', 'crm_deny_new_contact',
				)
			);

			if (empty($service['UPLOAD_OUTGOING']))
			{
				if (!empty($fields['upload_outgoing']))
				{
					$mailboxData['OPTIONS']['flags'] = array_diff($mailboxData['OPTIONS']['flags'], ['deny_upload']);
				}
				else if (!in_array('deny_upload', $mailboxData['OPTIONS']['flags']))
				{
					$mailboxData['OPTIONS']['flags'][] = 'deny_upload';
				}
			}
		}

		$mailboxData['OPTIONS']['name'] = $mailboxData['USERNAME'];

		$isOAuth = false;
		if ($fields['oauth_uid'])
		{
			if (!empty($mailbox) && 'S' == $fields['oauth_mode'])
			{
				$mailboxData['EMAIL'] = mb_strtolower(trim($mailbox['EMAIL']));
				$mailboxData['LOGIN'] = $mailboxData['EMAIL'];
			}
			else
			{
				if ($oauthHelper = Mail\MailServicesTable::getOAuthHelper($service))
				{
					$oauthHelper->getStoredToken($fields['oauth_uid']);

					$mailboxData['LOGIN'] = $mailboxData['EMAIL'];
					$mailboxData['PASSWORD'] = $oauthHelper->buildMeta();
					$isOAuth = true;
				}
			}

		}

		if (empty($mailbox['EMAIL']))
		{
			$address = new Main\Mail\Address($mailboxData['EMAIL']);
			if (!$address->validate())
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_BAD'));
				return;
			}

			$mailboxData['EMAIL'] = $address->getEmail();
		}

		if (empty($mailbox))
		{
			$mailbox = Mail\MailboxTable::getList(array(
				'filter' => array(
					'=EMAIL' => $mailboxData['EMAIL'],
					'=USER_ID' => $USER->getId(),
					'=ACTIVE' => 'Y',
					'=LID' => $currentSite['LID'],
				),
			))->fetch();

			if (!empty($mailbox))
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_EXISTS'));
				return;
			}
		}

		if (empty($mailboxData['NAME']))
		{
			$mailboxData['NAME'] = $mailboxData['EMAIL'];
		}

		if (!$service['SERVER'])
		{
			$regex = '/^(?:(?:http|https|ssl|tls|imap):\/\/)?((?:[a-z0-9](?:-*[a-z0-9])*\.?)+)$/i';
			if (!preg_match($regex, $mailboxData['SERVER'], $matches) && $matches[1] <> '')
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_SERVER_BAD'));
				return;
			}

			$mailboxData['SERVER'] = $matches[1];

			if (!MailboxConnector::isValidMailHost($mailboxData['SERVER']))
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_SERVER_BAD'));
				return;
			}
		}

		if (!$service['PORT'])
		{
			if ($mailboxData['PORT'] <= 0 || $mailboxData['PORT'] > 65535)
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_PORT_BAD'));
				return;
			}
		}

		if (!in_array($mailboxData['USE_TLS'], array('Y', 'S')))
		{
			$mailboxData['USE_TLS'] = 'N';
		}

		if (!$service['LINK'] && $mailboxData['LINK'])
		{
			$regex = '/^(https?:\/\/)?((?:[a-z0-9](?:-*[a-z0-9])*\.?)+)(:[0-9]+)?\/?(.*)/i';
			if (!(preg_match($regex, $mailboxData['LINK'], $matches) && $matches[2] <> ''))
			{
				$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_LINK_BAD'));
				return;
			}

			$mailboxData['LINK'] = $matches[0];
			if ($matches[1] == '')
			{
				$mailboxData['LINK'] = 'http://' . $mailboxData['LINK'];
			}
		}

		if (empty($mailbox))
		{
			if (array_key_exists('mail_connect_import_messages', $fields) && $fields['mail_connect_import_messages'] === 'Y' && array_key_exists('msg_max_age', $fields))
			{
				$maxAge = (int) $fields['msg_max_age'];
				$maxAgeLimit = LicenseManager::getSyncOldLimit();

				if ($maxAgeLimit > 0 && $maxAge > $maxAgeLimit)
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_MAX_AGE_ERROR'));
					return;
				}

				if ($maxAge < 0)
				{
					unset($mailboxData['OPTIONS']['sync_from']);
				}
				else
				{
					$mailboxData['OPTIONS']['sync_from'] = strtotime('today UTC 00:00'.sprintf('-%u days', $maxAge+1));
				}
			}
		}

		$unseen = Mail\Helper::getImapUnseen($mailboxData, 'inbox', $error, $errors);
		if ($unseen === false)
		{
			$this->error($errors instanceof Main\ErrorCollection ? $errors : $error, $isOAuth);
			return;
		}

		$isSmtpOauthEnabled = MailboxConnector::isOauthSmtpEnabled($service['NAME'] ?? '');
		$useSmtp = !empty($fields['use_smtp']) || ($isSmtpOauthEnabled && $this->isNotMicrosoftService($service));

		if ($this->arParams['IS_SMTP_AVAILABLE'] && !$useSmtp && !empty($mailbox))
		{
			self::deleteMailboxSender((int)$fields['mailbox_id'], $mailboxData['EMAIL']);
		}

		$senderFields = [];
		if ($this->arParams['IS_SMTP_AVAILABLE'] && $useSmtp) {
			$senderFields =[
				'NAME' => $mailboxData['USERNAME'],
				'EMAIL' => $mailboxData['EMAIL'],
				'USER_ID' => $mailboxData['USER_ID'] ?? CurrentUser::get()->getId(),
				'IS_CONFIRMED' => false,
				'IS_PUBLIC' => false,
			];

			$mailboxSender = SenderTable::query()
				->setSelect(['ID', 'OPTIONS'])
				->where('IS_CONFIRMED', true)
				->where('EMAIL', $mailboxData['EMAIL'])
				->where('PARENT_MODULE_ID', 'mail')
				->where('PARENT_ID', $fields['mailbox_id'])
				->setLimit(1)
				->fetchObject()
			;

			if($mailboxSender)
			{
				$senderFields['ID'] = $mailboxSender['ID'];
				$smtpConfirmed = $mailboxSender['OPTIONS']['smtp'];
			}
			else
			{
				$mailboxSenders = SenderTable::query()
					->setSelect(['ID', 'OPTIONS'])
					->where('IS_CONFIRMED', true)
					->where('EMAIL', $mailbox['EMAIL'])
					->where('USER_ID', $mailbox['USER_ID'])
					->fetchAll()
				;

				foreach ($mailboxSenders as $sender) {
					if (!empty($sender['OPTIONS']['smtp']['server']) && empty($sender['OPTIONS']['smtp']['encrypted'])) {
						$smtpConfirmed = $sender['OPTIONS']['smtp'];
					}

					if ($sender['OPTIONS']['source'] === 'mail.client.config') {
						SenderTable::update(
							$sender['ID'],
							[
								'PARENT_MODULE_ID' => 'mail',
								'PARENT_ID' => $mailbox['ID']
							]
						);

						$senderFields = $sender;
						$senderFields['IS_CONFIRMED'] = false;
						$senderFields['OPTIONS']['__replaces'] = $sender['ID'];

						unset($senderFields['ID']);

						if (!empty($smtpConfirmed))
						{
							break;
						}
					}
				}
			}
		}

		if (!empty($senderFields))
		{
			// don't check the sender name if it has not changed or is equal to the username
			if (
				!empty($senderFields['NAME'])
				&& $senderFields['NAME'] !== $this->getSenderName($mailbox['USERNAME'] ?? '', $mailbox['USER_ID'] ?? null)
			)
			{
				$checkResult = Main\Mail\Sender::checkSenderNameCharacters((string)$senderFields['NAME']);
				if (!$checkResult->isSuccess())
				{
					$this->error($checkResult->getErrorMessages()[0]);

					return null;
				}
			}

			$smtpConfig = array(
				'server'   => $service['SMTP_SERVER'] ?: trim($fields['server_smtp']),
				'port'     => $service['SMTP_PORT'] ?: (int) $fields['port_smtp'],
				'protocol' => ('Y' == ($service['SMTP_ENCRYPTION'] ?: $fields['ssl_smtp']) ? 'smtps' : 'smtp'),
				'login'    => $service['SMTP_LOGIN_AS_IMAP'] == 'Y' ? $mailboxData['LOGIN'] : $fields['login_smtp'],
				'password' => '',
				'limit' => $fields['use_limit_smtp'] === 'Y' ? max((int)($fields['limit_smtp'] ?? 0), 0) : null,
			);

			if (!empty($smtpConfirmed) && is_array($smtpConfirmed))
			{
				// server, port, protocol, login, password
				$smtpConfig = array_filter($smtpConfig) + $smtpConfirmed;
			}

			if ($service['SMTP_PASSWORD_AS_IMAP'] === 'Y' && (!$fields['oauth_uid'] || $isSmtpOauthEnabled))
			{
				$smtpConfig['password'] = $mailboxData['PASSWORD'];
				$smtpConfig['isOauth'] = !empty($fields['oauth_uid']) && $isSmtpOauthEnabled;
			}
			else if ($fields['pass_smtp'] <> '' && $fields['pass_smtp'] != $fields['pass_placeholder'])
			{
				if (preg_match('/^\^/', $fields['pass_smtp']))
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS_BAD_CARET'));
					return;
				}
				else if (preg_match('/\x00/', $fields['pass_smtp']))
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS_BAD_NULL'));
					return;
				}

				$smtpConfig['password'] = $fields['pass_smtp'];
				$smtpConfig['isOauth'] = !empty($fields['oauth_uid']) && $isSmtpOauthEnabled;
			}

			if (!$service['SMTP_SERVER'])
			{
				$regex = '/^(?:(?:http|https|ssl|tls|smtp):\/\/)?((?:[a-z0-9](?:-*[a-z0-9])*\.?)+)$/i';
				if (!preg_match($regex, $smtpConfig['server'], $matches) && $matches[1] <> '')
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_SERVER_BAD'));
					return;
				}

				$smtpConfig['server'] = $matches[1];

				if (!MailboxConnector::isValidMailHost($smtpConfig['server']))
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_SERVER_BAD'));
					return;
				}
			}

			if (!$service['SMTP_PORT'])
			{
				if ($smtpConfig['port'] <= 0 || $smtpConfig['port'] > 65535)
				{
					$this->error(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PORT_BAD'));
					return;
				}
			}

			$senderFields['OPTIONS']['smtp'] = $smtpConfig;

			if (!empty($smtpConfirmed))
			{
				$senderFields['IS_CONFIRMED'] = !array_diff(
					array('server', 'port', 'protocol', 'login', 'password', 'isOauth'),
					array_keys(array_intersect_assoc($smtpConfig, $smtpConfirmed))
				);
			}
		}

		$userPrincipalName = (string)($fields['user_principal_name'] ?? '');

		// we should check if smtp credentials is correct
		if (
			$this->arParams['IS_SMTP_AVAILABLE'] && $useSmtp
			&& $this->isSmtpInvalid($senderFields ?? [], $userPrincipalName, $isSmtpOauthEnabled)
		)
		{
			return false; // errors set in check method
		}

		if ($fields['use_crm'] == 'Y')
		{
			$crmAvailable = false;
			if (Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled())
			{
				$crmAvailable = $USER->isAdmin() || $USER->canDoOperation('bitrix24_config')
					|| \COption::getOptionString('intranet', 'allow_external_mail_crm', 'Y', SITE_ID) == 'Y';

				if ($crmAvailable)
				{
					$mailboxData['OPTIONS']['flags'][] = 'crm_connect';

					if ($fields['crm_sync_old'] == 'Y')
					{
						$maxAge = (int) $fields['crm_max_age'];
						if ($maxAge < 0)
						{
							unset($mailboxData['OPTIONS']['crm_sync_from']);
						}
						else
						{
							$mailboxData['OPTIONS']['crm_sync_from'] = strtotime(sprintf('-%u days', $maxAge));
						}
					}

					if ($fields['crm_public'] == 'Y')
					{
						$interval = (int) Main\Config\Option::get('mail', 'public_mailbox_sync_interval', 0);
						$mailboxData['PERIOD_CHECK'] = $interval > 0 ? $interval : 10;
						$mailboxData['OPTIONS']['flags'][] = 'crm_public_bind';
					}

					if ($fields['crm_allow_entity_in'] != 'Y')
					{
						$mailboxData['OPTIONS']['flags'][] = 'crm_deny_entity_in';
					}
					if ($fields['crm_allow_entity_out'] != 'Y')
					{
						$mailboxData['OPTIONS']['flags'][] = 'crm_deny_entity_out';
					}

					$newEntityList = array(\CCrmOwnerType::LeadName, \CCrmOwnerType::ContactName);
					if (!empty($fields['crm_entity_in']) && in_array($fields['crm_entity_in'], $newEntityList))
					{
						$mailboxData['OPTIONS']['crm_new_entity_in'] = $fields['crm_entity_in'];
					}
					if (!empty($fields['crm_entity_out']) && in_array($fields['crm_entity_out'], $newEntityList))
					{
						$mailboxData['OPTIONS']['crm_new_entity_out'] = $fields['crm_entity_out'];
					}

					if ($fields['crm_vcf'] != 'Y')
					{
						$mailboxData['OPTIONS']['flags'][] = 'crm_deny_new_contact';
					}

					$leadSourceList = \CCrmStatus::getStatusList('SOURCE');
					if (is_set($leadSourceList, $fields['crm_lead_source']))
					{
						$mailboxData['OPTIONS']['crm_lead_source'] = $fields['crm_lead_source'];
					}

					$mailboxData['OPTIONS']['crm_new_lead_for'] = array();
					if (!empty($fields['crm_new_lead_for']))
					{
						$newLeadFor = preg_split('/[\r\n,;]+/', $fields['crm_new_lead_for']);
						foreach ($newLeadFor as $i => $item)
						{
							$address = new Main\Mail\Address($item, ['checkingPunycode' => true]);

							$newLeadFor[$i] = $address->validate() ? $address->getEmail() : null;
						}

						$mailboxData['OPTIONS']['crm_new_lead_for'] = array_values(array_unique(array_filter($newLeadFor)));
					}

					$mailboxData['OPTIONS']['crm_lead_resp'] = array();
					$queueUsers = [];
					if (!empty($fields['crm_queue']))
					{
						$queueUsers = (!empty($fields['crm_queue']['U']) ? $fields['crm_queue']['U'] : $fields['crm_queue']);
					}
					if (!empty($queueUsers))
					{
						foreach ((array) $queueUsers as $item)
						{
							if (preg_match('/^U(\d+)$/i', trim($item), $matches))
								$mailboxData['OPTIONS']['crm_lead_resp'][] = (int) $matches[1];
						}
					}
					if (empty($mailboxData['OPTIONS']['crm_lead_resp']))
					{
						$mailboxData['OPTIONS']['crm_lead_resp'] = array(empty($mailbox) ? $USER->getId() : $mailbox['USER_ID']);
					}
				}
			}
		}

		if (Main\Loader::includeModule('calendar'))
		{
			if (!isset($fields['ical_access']))
			{
				$mailboxData['OPTIONS']['ical_access'] = self::NEGATIVE_ANSWER;
			}
			else if (($fields['ical_access'] === self::POSITIVE_ANSWER))
			{
				$mailboxData['OPTIONS']['ical_access'] = self::POSITIVE_ANSWER;
			}
		}

		$mailboxData['OPTIONS']['version'] = 6;

		if (empty($mailbox))
		{
			$mailboxData = array_merge([
				'LID'         => $currentSite['LID'],
				'ACTIVE'      => 'Y',
				'SERVICE_ID'  => $service['ID'],
				'SERVER_TYPE' => $service['SERVICE_TYPE'],
				'CHARSET'     => $currentSite['CHARSET'],
				'USER_ID'     => $USER->getId(),
				'SYNC_LOCK'   => time()
			], $mailboxData);

			$result = $mailboxId = \CMailbox::add($mailboxData);

			addEventToStatFile('mail', 'add_mailbox', $service['NAME'], ($result > 0 ? 'success' : 'failed'));
		}
		else
		{
			$result = \CMailbox::update($mailboxId = $mailbox['ID'], $mailboxData);
		}

		if (!($result > 0))
		{
			$this->error(Loc::getMessage('MAIL_CLIENT_SAVE_ERROR'));
			return;
		}

		if (!empty($senderFields))
		{
			$result = [];
			if (!empty($senderFields['ID']))
			{
				$updateResult = Main\Mail\Sender::updateSender($senderFields['ID'], $senderFields);
				if ($updateResult->isSuccess())
				{
					$result['confirmed'] = true;
				}
				else
				{
					$result['errors'] = $updateResult->getErrorCollection();
				}
			}
			else
			{
				$result = MailboxConnector::appendSender($senderFields, (string)($fields['user_principal_name'] ?? ''), (int)$mailboxId);
			}

			if (!empty($result['errors']) && $result['errors'] instanceof Main\ErrorCollection)
			{
				$this->error($result['errors'], $isSmtpOauthEnabled, true);
				return;
			}
			else if (!empty($result['error']))
			{
				$this->error($result['error'], $isSmtpOauthEnabled, true);
				return;
			}
			else if (empty($result['confirmed']))
			{
				$this->error('MAIL_CLIENT_CONFIG_SMTP_CONFIRM', $isSmtpOauthEnabled, true);
				return;
			}
		}

		$entity = Mail\Internals\MailboxAccessTable::getEntity();
		$entity->getConnection()->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$entity->getConnection()->getSqlHelper()->quote($entity->getDbTableName()),
			Main\Entity\Query::buildFilterSql(
				$entity,
				array(
					'=MAILBOX_ID' => $mailboxId,
				)
			)
		));

		$ownerAccessCode = 'U' . (empty($mailbox) ? $USER->getId() : $mailbox['USER_ID']);
		$access = array($ownerAccessCode);

		if (!empty($fields['access_dest']) && is_array($fields['access_dest']))
		{
			$access = array_merge(
				$access,
				array_filter(
					$fields['access_dest'],
					function ($item)
					{
						return preg_match('/^(DR|U)\d+$/i', trim($item));
					}
				)
			);
		}
		elseif (!empty($fields['access']) && is_array($fields['access'])) // old
		{
			foreach ($fields['access'] as $code => $list)
			{
				if (in_array($code, array('U', 'DR')) && is_array($list))
				{
					$access = array_merge(
						$access,
						array_filter(
							$list,
							function ($item) use (&$code)
							{
								return preg_match(sprintf('/^%s\d+$/i', preg_quote($code, '/')), trim($item));
							}
						)
					);
				}
			}
		}

		$sharedMailboxesLimit = LicenseManager::getSharedMailboxesLimit();
		if (count(array_unique($access)) > 1 && $sharedMailboxesLimit >= 0)
		{
			$alreadySharedMailboxesIds = Mail\Helper\Mailbox\SharedMailboxesManager::getSharedMailboxesIds();
			if (count($alreadySharedMailboxesIds) >= $sharedMailboxesLimit && !in_array($mailboxId, $alreadySharedMailboxesIds))
			{
				$access = array($ownerAccessCode);
			}
		}

		foreach (array_unique($access) as $item)
		{
			Mail\Internals\MailboxAccessTable::add(array(
				'MAILBOX_ID' => $mailboxId,
				'TASK_ID' => 0,
				'ACCESS_CODE' => $item,
			));
		}

		$mailboxHelper = Mailbox::createInstance($mailboxId);
		$mailboxHelper->cacheDirs();

		$res = Mail\MailFilterTable::getList(array(
			'select' => array(
				'ID',
			),
			'filter' => array(
				'=MAILBOX_ID'  => $mailboxId,
				'=ACTION_TYPE' => 'crm_imap'
			)
		));
		while ($filter = $res->fetch())
		{
			\CMailFilter::delete($filter['ID']);
		}

		if ($fields['use_crm'] == 'Y' && $crmAvailable)
		{
			$filterFields = array(
				'MAILBOX_ID'         => $mailboxId,
				'NAME'               => sprintf('CRM IMAP %u', $mailboxId),
				'ACTION_TYPE'        => 'crm_imap',
				'WHEN_MAIL_RECEIVED' => 'Y',
				'WHEN_MANUALLY_RUN'  => 'Y',
			);

			\CMailFilter::add($filterFields);

			// @TODO: process old messages
		}

		if (
			!empty($senderFields)
			&& strlen($senderFields['EMAIL'] ?? '') > 0
		)
		{
			$senderName = UserSenderDataProvider::getAddressInEmailAngleFormat(
				email:$senderFields['EMAIL'],
				senderName: $senderFields['NAME'],
				userId: $senderFields['USER_ID'],
			);
		}

		return [
			'id' => $mailboxId,
			'senderName' => $senderName ?? null,
		];
	}

	public function deleteAction($id)
	{
		global $USER;

		$mailbox = Mail\MailboxTable::getList(array(
			'filter' => array(
				'=ID' => $id,
				'=ACTIVE' => 'Y',
				'=SERVER_TYPE' => 'imap',
			),
		))->fetch();

		if (empty($mailbox))
		{
			$this->error(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return;
		}

		if ($USER->getId() != $mailbox['USER_ID'] && !$USER->isAdmin() && !$USER->canDoOperation('bitrix24_config'))
		{
			$this->error(Loc::getMessage('MAIL_CLIENT_DENIED'));
			return;
		}

		\CMailbox::update($mailbox['ID'], array('ACTIVE' => 'N'));
		self::deleteMailboxSender((int)$mailbox['ID'], $mailbox['EMAIL']);

		\CUserCounter::clear($USER->getId(), 'mail_unseen', $mailbox['LID']);
		$mailboxSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($mailbox['USER_ID']);
		$mailboxSyncManager->deleteSyncData($mailbox['ID']);

		\CAgent::addAgent(sprintf('Bitrix\Mail\Helper::deleteMailboxAgent(%u);', $mailbox['ID']), 'mail', 'N', 60);
	}

	protected function error($error, $isOAuth = false, $isSender = false)
	{
		if ($error instanceof Main\ErrorCollection)
		{
			$messages = array();
			$details  = array();

			foreach ($error as $item)
			{
				${$item->getCode() < 0 ? 'details' : 'messages'}[] = $item;
			}

			if (count($messages) == 1 && reset($messages)->getCode() == Mail\Imap::ERR_AUTH)
			{
				$authError = Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AUTH_ERR_EXT');
				if  ($isOAuth && Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AUTH_ERR_OAUTH'))
				{
					$authError = Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AUTH_ERR_OAUTH');
				}
				if  ($isOAuth && $isSender && Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AUTH_ERR_OAUTH_SMTP'))
				{
					$authError = Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AUTH_ERR_OAUTH_SMTP');
				}

				$messages = array(
					new Main\Error($authError, Mail\Imap::ERR_AUTH),
				);

				$moreDetailsSection = false;
			}
			else
			{
				$moreDetailsSection = true;
			}

			$reduce = function($error)
			{
				return $error->getMessage();
			};

			if($moreDetailsSection)
			{
				$this->errorCollection[] = new Main\Error(
					join(': ', array_map($reduce, $messages)),
					0,
					join(': ', array_map($reduce, $details))
				);
			}
			else
			{
				$this->errorCollection[] = new Main\Error(
					join(': ', array_map($reduce, $messages)),
					0,
				);
			}

		}
		else
		{
			$this->errorCollection[] = new Main\Error($error);
		}
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 *
	 * @param string $code Code of error.
	 * @return Main\Error
	 */
	final public function getErrorByCode($code): Main\Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Prepares mail services and their names for the mail providers showcase and connected mailbox settings page
	 *
	 * @param array|null $mailboxes
	 * @return array
	 */
	private static function prepareMailServices(?array $mailboxes = null): array
	{
		$mailboxes = $mailboxes ?? Mailbox::getServices();

		foreach ($mailboxes as &$mailbox)
		{
			$mailbox['serviceName'] = match ($mailbox['name']) {
				'aol' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_AOL'),
				'yahoo' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_YAHOO'),
				'icloud' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_ICLOUD'),
				'gmail' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_GMAIL'),
				'yandex' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_YANDEX'),
				'outlook.com' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_OUTLOOK'),
				'exchange', 'exchangeOnline' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_EXCHANGE'),
				'mail.ru' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_MAILRU'),
				'office365' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_OFFICE365'),
				'other' => Loc::getMessage('MAIL_MAILBOX_SERVICE_NAME_IMAP_MSGVER_1'),
				default => ucfirst($mailbox['name']),
			};
		}

		return $mailboxes;
	}

	private function isSmtpSwitcherChecked(): bool
	{
		$excludedServices = $this->getMicrosoftServiceNames();
		$mailbox = $this->arParams['MAILBOX'] ?? [];
		$service = $this->arParams['SERVICE'] ?? [];

		if (!empty($mailbox['__smtp']))
		{
			return true;
		}

		if (!empty($mailbox))
		{
			return false;
		}

		return !in_array($service['name'] ?? null, $excludedServices, true);
	}

	private function isSmtpSwitcherDisabled(): bool
	{

		if (!empty($this->arParams['MAILBOX']) && !$this->isSmtpSwitcherChecked())
		{
			return false;
		}

		if ($this->arResult['IS_SMTP_SENDER_ADDED'] ?? false)
		{
			return true;
		}

		if (
			isset($this->arParams['SERVICE']['oauth_smtp_enabled'])
			&& $this->arParams['SERVICE']['oauth_smtp_enabled'] === true
			&& $this->isNotMicrosoftService($this->arParams['SERVICE'] ?? [])
		)
		{
			return true;
		}

		return false;
	}

	private function isNotMicrosoftService(?array $service): bool
	{
		$serviceName = $service['NAME'] ?? $service['name'] ?? null;

		return !in_array($serviceName, $this->getMicrosoftServiceNames(), true);
	}

	private function getMicrosoftServiceNames(): array
	{
		return [
			'office365',
			'exchangeOnline',
			'outlook.com',
		];
	}

	private function getSenderName(string $name, ?int $userId = null): string
	{
		if (strlen($name) > 0)
		{
			return $name;
		}

		return Sender\UserSenderDataProvider::getUserFormattedName($userId) ?? '';
	}

	private static function deleteMailboxSender(int $mailboxId, string $email): void
	{
		$sender = SenderTable::query()
			->setSelect(['ID'])
			->where('IS_CONFIRMED', true)
			->where('PARENT_MODULE_ID', 'mail')
			->where('EMAIL', $email)
			->where('PARENT_ID', $mailboxId)
			->setLimit(1)
			->fetchObject()
		;

		if ($sender)
		{
			Main\Mail\Sender::delete([$sender['ID']]);
			Main\Mail\Sender::clearCustomSmtpCache($email);
		}
	}

	private function isSmtpInvalid(array $fields, string $principalName, bool $isOAuth): bool
	{
		if (empty($fields))
		{
			return false; // if we don't connect sender it's OK
		}

		if (empty($fields['OPTIONS']) || !is_array($fields['OPTIONS']))
		{
			$fields['OPTIONS'] = [];
		}

		Sender::checkEmail($fields, $error, $errors);

		if ($principalName)
		{
			$address = new Address($principalName);
			$currentSmtpLogin = $fields['OPTIONS']['smtp']['login'] ?? '';
			if ($currentSmtpLogin && $currentSmtpLogin !== $principalName && $address->validate())
			{
				// outlook workaround, sometimes SMTP auth only works with userPrincipalName
				$fields['OPTIONS']['smtp']['login'] = $principalName;
				// clean errors
				$error = null;
				$errors = null;
				Sender::checkEmail($fields, $error, $errors);
			}
		}

		if (!empty($errors) && $errors instanceof Main\ErrorCollection)
		{
			$this->error($errors, $isOAuth, true);

			return true;
		}
		else if (!empty($error))
		{
			$this->error($error, $isOAuth, true);

			return true;
		}

		return false;
	}

}
