<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MainMailFormComponent extends CBitrixComponent
{
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

		$this->arParams['FIELDS'] = $this->arParams['~FIELDS'];
		$this->arParams['FIELDS_EXT'] = $this->arParams['~FIELDS_EXT'] ?? '';
		$this->arParams['BUTTONS'] = $this->arParams['~BUTTONS'];

		if (empty($this->arParams['FORM_ID']) || !trim($this->arParams['FORM_ID']))
			$this->arParams['FORM_ID'] = sprintf('%s%04x', hash('crc32b', microtime()), rand(0, 0xffff));

		$this->prepareFields();
		$this->prepareEditor();
		$this->prepareButtons();

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
					$field['signatures'] = $this->loadSignatures($field['mailboxes']);
				}

				$defaultMailbox = reset($field['mailboxes']);
				$value = empty($field['required']) ? null : $defaultMailbox['formated'];

				if (check_email($field['value']))
				{
					$email = $field['value'];
					if (preg_match('/.*?[<\[\(](.+?)[>\]\)].*/i', $email, $matches))
						$email = mb_strtolower(trim($matches[1]));

					foreach ($field['mailboxes'] as $item)
					{
						if ($item['email'] == $email)
						{
							$value = (!empty($field['isFormatted']) && $item['formated'])
								? $item['formated'] : $field['value'];
							break;
						}
					}
				}

				$field['value'] = $value;

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
					$editor['value'] = preg_replace(
						sprintf('/bxacid:%u/', $fileId),
						(new Uri($diskUrlManager->getUrlForShowFile($objects[$fileId])))
							->addParams(['__bxacid' => $fileId])
							->getUri(),
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
		$signatures = [];

		if(!empty($mailboxes) && \Bitrix\Main\Loader::includeModule('mail') && class_exists('\\Bitrix\\Mail\\Internals\\UserSignatureTable'))
		{
			$signatureList = \Bitrix\Mail\Internals\UserSignatureTable::getList([
				'order' => ['ID' => 'desc'],
				'select' => ['SIGNATURE', 'SENDER'],
				'filter' => [
					'USER_ID' => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
				]
			]);
			while($signature = $signatureList->fetch())
			{
				if(!isset($signatures[$signature['SENDER']]))
				{
					$signatures[$signature['SENDER']] = $signature['SIGNATURE'];
				}
			}
		}

		return $signatures;
	}
}
