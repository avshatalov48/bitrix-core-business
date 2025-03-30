<?php

use Bitrix\Crm\Settings\ActivitySettings;
use Bitrix\Crm\Activity;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Address;
use Bitrix\Calendar;
use Bitrix\Crm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MainMailFormComponent extends CBitrixComponent implements Controllerable
{
	/**
	 * Cache for compatibility
	 *
	 * @var array
	 */
	private array $signatures;

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if(!isset($params['USE_SIGNATURES']) || $params['USE_SIGNATURES'] !== true)
		{
			$params['USE_SIGNATURES'] = false;
		}

		if(!isset($params['USE_CALENDAR_SHARING']) || $params['USE_CALENDAR_SHARING'] !== true)
		{
			$params['USE_CALENDAR_SHARING'] = false;
		}

		$params['VERSION'] = (isset($params['VERSION']) && intval($params['VERSION']) > 0 ? intval($params['VERSION']) : 1);
//		$params['VERSION'] = 1;

		if (
			!empty($params['FIELDS'])
			&& $params['VERSION'] < 2
		)
		{
			foreach($params['FIELDS'] as $fieldKey => $field)
			{
				if (!empty($field['selector']))
				{
					if (
						!empty($field['selector']['items'])
						&& !empty($field['selector']['items']['mailcontacts'])
					)
					{
						if (empty($field['selector']['items']['users']))
						{
							$field['selector']['items']['users'] = [];
							$field['selector']['items']['emails'] = [];
						}
						foreach($field['selector']['items']['mailcontacts'] as $itemKey => $item)
						{
							$newItemKey = preg_replace('/^MC(.*)/', '$1', $itemKey);
							$newItemKey = 'U'.md5($newItemKey);
							$item['id'] = $newItemKey;
							$field['selector']['items']['users'][$newItemKey] = $item;
							$field['selector']['items']['emails'][$newItemKey] = $item;
							unset($field['selector']['items']['emails'][$itemKey]);
						}
						unset($field['selector']['items']['mailcontacts']);
					}
					if (
						!empty($field['selector']['itemsLast'])
						&& !empty($field['selector']['itemsLast']['mailcontacts'])
					)
					{
						if (empty($field['selector']['itemsLast']['users']))
						{
							$field['selector']['itemsLast']['users'] = [];
							$field['selector']['itemsLast']['emails'] = [];
						}
						foreach($field['selector']['itemsLast']['mailcontacts'] as $itemKey => $value)
						{
							$newItemKey = preg_replace('/^MC(.*)/', '$1', $itemKey);
							$newItemKey = 'U'.md5($newItemKey);
							$field['selector']['itemsLast']['users'][$newItemKey] = $newItemKey;
							$field['selector']['itemsLast']['emails'][$newItemKey] = $newItemKey;
							unset($field['selector']['itemsLast']['mailcontacts'][$itemKey]);
							unset($field['selector']['itemsLast']['emails'][$itemKey]);
						}
						unset($field['selector']['itemsLast']['mailcontacts']);
					}
					if (!empty($field['selector']['itemsSelected']))
					{
						foreach($field['selector']['itemsSelected'] as $itemKey => $value)
						{
							if ($value == 'mailcontacts')
							{
								$newItemKey = preg_replace('/^MC(.*)/', '$1', $itemKey);
								$newItemKey = 'U'.md5($newItemKey);
								$field['selector']['itemsSelected'][$newItemKey] = 'users';
								unset($field['selector']['itemsSelected'][$itemKey]);
							}
						}
					}
				}
			}
		}

		$params['USER_CALENDAR_PATH'] = $this->getUserCalendarPath();
		$params['CALENDAR_SHARING_TOUR_ID'] = $this->getSharingCalendarTourId();

		$params['IS_SMTP_AVAILABLE'] = Loader::includeModule('bitrix24')
			|| Configuration::getValue('smtp')
		;

		$params['POST_FORM_BUTTONS'] = ['UploadImage', 'UploadFile', 'Copilot'];

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		\CModule::includeModule('socialnetwork');
		$extensionsList = [ 'admin_interface' ];
		if ($this->arParams['VERSION'] < 2)
		{
			$extensionsList[] = 'socnetlogdest';
		}
		\CJSCore::init($extensionsList);

		$this->arParams['OLD_RECIPIENTS_MODE'] = false;
		$this->arParams['OWNER_CATEGORY_ID'] = 0;

		if (Loader::includeModule('crm'))
		{
			$this->arParams['OLD_RECIPIENTS_MODE'] = ActivitySettings::getCurrent()->getEnableUnconnectedRecipients();

			if (!empty($this->arParams['OWNER_TYPE_ID']) && !empty($this->arParams['OWNER_ID']))
			{
				$this->arParams['OWNER_CATEGORY_ID'] = Activity\Mail\Message::getRecipientCategoryId((int) $this->arParams['OWNER_TYPE_ID'], (int) $this->arParams['OWNER_ID']);
			}
		}

		$this->arParams['FIELDS'] = $this->arParams['~FIELDS'];
		$this->arParams['FIELDS_EXT'] = $this->arParams['~FIELDS_EXT'] ?? '';
		$this->arParams['BUTTONS'] = $this->arParams['~BUTTONS'];

		if (empty($this->arParams['FORM_ID']) || !trim($this->arParams['FORM_ID']))
			$this->arParams['FORM_ID'] = sprintf('%s%04x', hash('crc32b', microtime()), rand(0, 0xffff));

		$this->prepareFields();
		$this->prepareEditor();
		$this->prepareButtons();
		$this->prepareCopilotParams($this->arParams['COPILOT_PARAMS'] ?? null);

		$this->includeComponentTemplate();
	}

	protected function prepareFields()
	{
		$this->arParams['EDITOR'] = array('type' => 'editor');
		$this->arParams['FILES']  = array('type' => 'files');

		foreach (array('FIELDS', 'FIELDS_EXT') as $set)
		{
			$fields = &$this->arParams[$set];
			$fields = !empty($fields) && is_array($fields) ? array_values($fields) : array();

			foreach ($fields as $k => $item)
			{
				$type = $item['type'] ?? null;
				if (in_array($type, array('editor', 'files')))
				{
					$this->arParams[mb_strtoupper($type)] = $item;
					unset($fields[$k]);
				}
			}
		}

		$this->arParams['FIELDS'][] = &$this->arParams['EDITOR'];
		$this->arParams['FIELDS'][] = &$this->arParams['FILES'];

		$presets = array(
			'from' => array(
				'type'        => 'from',
				'name'        => 'from',
				'title'       => Loc::getMessage('MAIN_MAIL_FORM_FROM_FIELD'),
				'placeholders' => array(
					'default' => Loc::getMessage('MAIN_MAIL_FORM_FROM_FIELD_HINT'),
					'required' => Loc::getMessage('MAIN_MAIL_FORM_FROM_FIELD_REQUIRED_HINT'),
				),
			),
			'rcpt' => array(
				'type'        => 'rcpt',
				'name'        => 'rcpt',
				'title'       => Loc::getMessage('MAIN_MAIL_FORM_TO_FIELD'),
				'placeholder' => Loc::getMessage('MAIN_MAIL_FORM_TO_FIELD_HINT'),
				'email'       => true,
				'multiple'    => true,
				'selector'    => array(
					'items' => array(
						'users'     => array(),
						'emails'    => array(),
						'companies' => array(),
						'contacts'  => array(),
						'deals'     => array(),
						'leads'     => array(),
					),
					'itemsLast' => array(
						'users'     => array(),
						'emails'    => array(),
						'crm'       => array(),
						'companies' => array(),
						'contacts'  => array(),
						'deals'     => array(),
						'leads'     => array(),
					),
					'itemsSelected' => array(),
					'destSort'      => array(),
				),
			),
		);

		if ($this->arParams['VERSION'] >= 2)
		{
			$presets['entity'] = array(
				'type'        => 'entity',
				'name'        => 'entity',
				'title'       => Loc::getMessage('MAIN_MAIL_FORM_TO_FIELD'),
				'placeholder' => Loc::getMessage('MAIN_MAIL_FORM_TO_FIELD_HINT'),
				'email'       => true,
				'multiple'    => true,
				'selector'    => array(
					'items' => array(
						'users'     => array(),
						'emails'    => array(),
						'companies' => array(),
						'contacts'  => array(),
						'deals'     => array(),
						'leads'     => array(),
					),
					'itemsLast' => array(
						'users'     => array(),
						'emails'    => array(),
						'crm'       => array(),
						'companies' => array(),
						'contacts'  => array(),
						'deals'     => array(),
						'leads'     => array(),
					),
					'itemsSelected' => array(),
					'destSort'      => array(),
				),
			);
		}

		foreach (array('FIELDS', 'FIELDS_EXT') as $set)
		{
			$fields = &$this->arParams[$set];
			$fields = !empty($fields) && is_array($fields) ? array_values($fields) : array();

			foreach ($fields as $k => $item)
			{
				if (!empty($item['type']) && array_key_exists($item['type'], $presets))
				{
					$params = $presets[$item['type']];

					$item = static::deepMerge($params, $item);
					$item['type'] = $params['type'];
				}

				$item['id'] = sprintf('%04x%02x', rand(0, 0xffff), $k+1);
				$this->prepareField($this->arParams['FORM_ID'], $item);

				$fields[$k] = $item;
			}
		}
	}

	protected function prepareField($formId, &$field)
	{
		if (!array_key_exists('placeholder', $field) && array_key_exists('placeholders', $field))
		{
			$field['placeholder'] = empty($field['required'])
				? $field['placeholders']['default']
				: $field['placeholders']['required'];
		}

		if (empty($field['type']) || !trim($field['type']))
			$field['type'] = 'text';

		if (empty($field['name']) || !trim($field['name']))
			$field['name'] = sprintf('main_mail_form[%s][]', $formId);

		if (empty($field['title']) || !trim($field['title']))
			$field['title'] = $field['name'];

		switch ($field['type'])
		{
			case 'list':
			{
				if (empty($field['list']) || !is_array($field['list']))
					$field['list'] = array();

				if (empty($field['value']) || !array_key_exists($field['value'], $field['list']))
					$field['value'] = null;

				if (empty($field['value']) && !empty($field['required']) && !empty($field['list']))
				{
					reset($field['list']);
					$field['value'] = key($field['list']);
				}
				break;
			}
			case 'from':
			{
				$field['mailboxes'] = \Bitrix\Main\Mail\Sender::prepareUserMailboxes();

				if($this->arParams['USE_SIGNATURES'])
				{
					$field = array_merge($field, $this->getSignaturesParams($field['mailboxes']));
				}

				if($this->arParams['USE_CALENDAR_SHARING'] && Loader::includeModule('calendar'))
				{
					$field['showCalendarSharingButton']  = true;
					$field['sharingFeatureLimitEnable'] = Calendar\Integration\Bitrix24Manager::isFeatureEnabled('calendar_sharing');
					$field['crmSharingFeatureLimitEnable'] = Calendar\Integration\Bitrix24Manager::isFeatureEnabled('crm_event_sharing');
					$field['showCalendarSharingTour'] = $this->isSharingCalendarTourAvailable();
				}

				$defaultMailbox = reset($field['mailboxes']);
				$defaultSender = empty($field['required']) ? '' : $defaultMailbox['formated'];
				$field['value'] = $this->getCurrentSender($field['value'] ?? '', $defaultSender, $field['mailboxes'] ?? []);

				break;
			}
		}
	}

	protected function prepareEditor()
	{
		$editor = &$this->arParams['EDITOR'];
		$files  = &$this->arParams['FILES'];

		if (!empty($editor['value']) && !empty($files['value']))
		{
			$itemsIds = array(
				'objects'  => array(),
				'attached' => array(),
			);
			foreach ($files['value'] as $item)
			{
				if (preg_match('/^(n?)(\d+)$/', trim($item), $matches))
				{
					$itemType = $matches[1] ? 'objects' : 'attached';
					$itemsIds[$itemType][$matches[2]] = $matches[0];
				}
			}

			$objects = array();

			if (!empty($itemsIds['objects']))
			{
				$filter = array('@ID' => array_keys($itemsIds['objects']));
				foreach (\Bitrix\Disk\File::getModelList(array('filter' => $filter)) as $object)
					$objects[$itemsIds['objects'][$object->getId()]] = $object;
			}

			if (!empty($itemsIds['attached']))
			{
				$diskUfManager = \Bitrix\Disk\Driver::getInstance()->getUserFieldManager();
				$diskUfManager->loadBatchAttachedObject($itemsIds['attached']);
				foreach ($itemsIds['attached'] as $objectId)
				{
					if ($attachedObject = $diskUfManager->getAttachedObjectById($objectId))
						$objects[$objectId] = $attachedObject->getFile();
				}
			}

			$diskUrlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();
			foreach ($files['value'] as $fileId)
			{
				if (array_key_exists($fileId, $objects))
				{
					$uri = (new Uri($diskUrlManager->getUrlUfController(
						'show',
						['attachedId' => $fileId]
					)))
						->addParams(['__bxacid' => $fileId])
						->getUri();

					$editor['value'] = preg_replace(
						sprintf('/bxacid:%u/', $fileId),
						$uri,
						$editor['value']
					);
				}
			}
		}
	}

	protected function prepareButtons()
	{
		$presets = array(
			'submit' => array(
				'title' => Loc::getMessage('MAIN_MAIL_FORM_SAVE_BTN'),
			),
			'cancel' => array(
				'title' => Loc::getMessage('MAIN_MAIL_FORM_CANCEL_BTN'),
			),
		);

		$buttons = &$this->arParams['BUTTONS'];
		$buttons = !empty($buttons) && is_array($buttons) ? $buttons : array();

		$buttons = array_merge(array('submit' => array()), $buttons);

		foreach ($buttons as $type => $item)
		{
			if (array_key_exists($type, $presets))
				$item = static::deepMerge($presets[$type], $item);

			if (empty($item['title']) || !trim($item['title']))
				$item['title'] = $type;

			$buttons[$type] = $item;
		}
	}

	private static function deepMerge(array &$base, array &$ext)
	{
		$result = array();

		foreach ($base as $k => $v)
			is_numeric($k) ? ($result[] = $v) : ($result[$k] = $v);

		foreach ($ext as $k => $v)
		{
			if (is_numeric($k))
			{
				$result[] = $v;
			}
			else
			{
				if (array_key_exists($k, $result) && is_array($result[$k]) && is_array($v))
					$v = static::deepMerge($result[$k], $v);

				$result[$k] = $v;
			}
		}

		return $result;
	}

	/**
	 * @param array $mailboxes
	 * @return array
	 */
	protected function loadSignatures(array $mailboxes)
	{
		if (!empty($mailboxes))
		{
			$onlyFirst = [];
			foreach ($this->getSignaturesFromDb() as $sender => $signatureFields)
			{
				if (!isset($onlyFirst[$sender]))
				{
					$onlyFirst[$sender] = $signatureFields;
				}
			}
			return $onlyFirst;
		}

		return [];
	}

	/**
	 * Get signatures from database
	 *
	 * @return array [sender => [signature,...],...]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getSignaturesFromDb(): array
	{
		if (!isset($this->signatures))
		{
			$signatures = [];
			if (
				\Bitrix\Main\Loader::includeModule('mail')
				&& class_exists('\\Bitrix\\Mail\\Internals\\UserSignatureTable')
			)
			{
				$signatureList = \Bitrix\Mail\Internals\UserSignatureTable::getList([
					'order' => ['ID' => 'desc'],
					'select' => ['SIGNATURE', 'SENDER'],
					'filter' => [
						'USER_ID' => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
					],
				]);
				while ($signature = $signatureList->fetch())
				{
					$signatures[$signature['SENDER']][] = [
						"list" => $this->getPreparedForTitleSignature((string)$signature['SIGNATURE']),
						"full" => $signature['SIGNATURE'],
					];
				}
			}
			$this->signatures = $signatures;
		}
		return $this->signatures;
	}

	/**
	 * Prepare string for correct display in title tag
	 *
	 * @param string $signature User signature text with html
	 *
	 * @return string
	 */
	private function getPreparedForTitleSignature(string $signature): string
	{
		$signature = mb_substr(strip_tags($signature), 0, 500);
		$signature = preg_replace("#\t#u", " ", $signature);
		$signature = preg_replace("#\n+#u", "\n", $signature);
		$signature = preg_replace("# +#u", " ", $signature);
		$signature = trim($signature);
		$encoding = "UTF-8";
		return html_entity_decode($signature, ENT_COMPAT, $encoding);
	}

	/**
	 * Get signatures related field params
	 *
	 * @param array $mailboxes Mailboxes array
	 *
	 * @return array
	 */
	private function getSignaturesParams(array $mailboxes): array
	{
		$signaturesUrl = (string)($this->arParams['PATH_TO_MAIL_SIGNATURES'] ?? '');
		$signaturesUrl = str_starts_with($signaturesUrl, '/') ? $signaturesUrl : '/mail/signatures';
		$params = [
			'signatures' => $this->loadSignatures($mailboxes), // compatibility
		];

		if (\Bitrix\Main\Loader::includeModule('mail'))
		{
			$params['allUserSignatures'] = empty($mailboxes) ? [] : $this->getSignaturesFromDb();
			$params['signatureSelectTitle'] = Loc::getMessage('MAIN_MAIL_FORM_EDITOR_SIGNATURE_SELECT');
			$params['signatureConfigureTitle'] = Loc::getMessage('MAIN_MAIL_FORM_EDITOR_SIGNATURE_CONFIGURE');
			$params['pathToMailSignatures'] = $signaturesUrl;
		}

		return $params;
	}

	/**
	 * Interface Controllable requirement
	 *
	 * @return array
	 */
	public function configureActions(): array
	{
		return [];
	}

	/**
	 * Get current user signatures from ajax action
	 *
	 * @return array
	 */
	public function signaturesAction(): array
	{
		return [
			'signatures' => $this->getSignaturesFromDb(),
		];
	}

	/**
	 * Get current user sharing link from ajax action
	 *
	 * @return array{isSharingFeatureEnabled: bool, sharingUrl?: string}
	 */
	public function getCalendarSharingLinkAction(string $entityType = null, int $entityId = null): array
	{
		if (!Loader::includeModule('calendar'))
		{
			return ['isSharingFeatureEnabled' => 'false'];
		}

		if (!Loader::includeModule('crm') || \CCrmOwnerType::DealName !== $entityType)
		{
			$sharing = new Calendar\Sharing\Sharing(CurrentUser::get()->getId());
			return [
				'isSharingFeatureEnabled' => $sharing->isEnabled(),
				'sharingUrl' => $sharing->getActiveLinkShortUrl(),
			];
		}

		$broker = Crm\Service\Container::getInstance()->getEntityBroker(\CCrmOwnerType::Deal);
		if (!$broker)
		{
			return ['isSharingFeatureEnabled' => false];
		}

		$deal = $broker->getById($entityId);
		if (!$deal)
		{
			return ['isSharingFeatureEnabled' => false];
		}

		$ownerId = $deal->getAssignedById();
		$crmDealLink = (new Calendar\Sharing\Link\Factory())->getCrmDealLink($entityId, $ownerId);
		if ($crmDealLink === null)
		{
			$crmDealLink = (new Calendar\Sharing\Link\Factory())->createCrmDealLink($ownerId, $entityId);
		}

		return [
			'isSharingFeatureEnabled' => true,
			'sharingUrl' => Calendar\Sharing\Helper::getShortUrl($crmDealLink->getUrl()),
		];
	}

	private function getSharingCalendarTourId(): string
	{
		return 'mail-start-calendar-sharing-tour';
	}

	private function isSharingCalendarTourAvailable(): bool
	{
		if (Loader::includeModule('calendar'))
		{
			return $this->isSharingCalendarTourAlreadySeen();
		}
		return false;
	}

	private function isSharingCalendarTourAlreadySeen(): bool
	{
		return \CUserOptions::GetOption('ui-tour', 'view_date_' . $this->getSharingCalendarTourId(), null) === null;
	}

	private function getUserCalendarPath(): string
	{
		if (Loader::includeModule('calendar'))
		{
			return \CCalendar::GetPathForCalendarEx(CurrentUser::get()->getId());
		}
		return '/';
	}

	/**
	 * @param array|null $copilotParams
	 * Array can contain fields: ['isCopilotEnabled', 'moduleId', 'contextId', 'category', 'invitationLineMode', 'contextParameters', 'isCopilotImageEnabled', 'isCopilotTextEnabled']
	 */
	private function prepareCopilotParams(?array $copilotParams = null): void
	{
		if (!$copilotParams || !isset($copilotParams['isCopilotEnabled']))
		{
			$this->arParams['IS_COPILOT_ENABLED'] = false;
			$this->arParams['IS_COPILOT_IMAGE_ENABLED'] = false;
			$this->arParams['IS_COPILOT_TEXT_ENABLED'] = false;

			return;
		}

		$this->arParams['IS_COPILOT_ENABLED'] = $copilotParams['isCopilotEnabled'];
		$this->arParams['COPILOT_PARAMS'] = [
			'moduleId' => $copilotParams['moduleId'] ?? 'main',
			'contextId' => $copilotParams['contextId'] ?? 'bxhtmled_copilot',
			'category' => $copilotParams['category'] ?? null,
			'invitationLineMode' => $copilotParams['invitationLineMode'] ?? 'eachLine',
			'contextParameters' => $copilotParams['contextParameters'] ?? [],
		];

		$this->arParams['IS_COPILOT_IMAGE_ENABLED'] = $copilotParams['isCopilotImageEnabled'] ?? false;
		$this->arParams['IS_COPILOT_TEXT_ENABLED'] = $copilotParams['isCopilotTextEnabled'] ?? false;
	}

	/**
	 * @param string $sender
	 * @param string|null $defaultSender
	 * @param list<array{name: ?string, email: string, formated: string}> $mailboxes
	 * @return string|null
	 */
	private function getCurrentSender(string $sender, ?string $defaultSender, array $mailboxes): ?string
	{
		if (empty($sender))
		{
			return null;
		}

		$currentSender = null;
		$address = new Address($sender);
		$email = $address->getEmail();

		if (empty($email))
		{
			return $defaultSender;
		}

		if (check_email($email))
		{
			foreach ($mailboxes as $item)
			{
				if ($item['email'] === $email)
				{
					if(empty($currentSender))
					{
						$currentSender = $item['formated'];
					}

					if($item['name'] === $address->getName())
					{
						return $item['formated'];
					}
				}
			}
		}

		return $currentSender ?? $defaultSender;
	}
}
