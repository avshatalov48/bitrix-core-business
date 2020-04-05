<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class Form
{
	protected static function isB24Connector()
	{
		return \Bitrix\Main\Loader::includeModule('b24connector') &&
			\Bitrix\Main\Loader::includeModule('socialservices');
	}
	
	/**
	 * Gets web forms in system.
	 * @return array
	 */
	protected static function getForms()
	{
		static $forms = null;
		
		if ($forms !== null)
		{
			return $forms;
		}

		$forms = array();

		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			$res = \Bitrix\Crm\WebForm\Internals\FormTable::getList(array(
				'select' => array(
					'ID',
					'NAME',
					'SECURITY_CODE',
					'IS_CALLBACK_FORM',
				),
				'filter' => array(
					'ACTIVE' => 'Y',
				),
				'order' => array(
					'ID' => 'DESC',
				),
			));
			while ($row = $res->fetch())
			{
				$forms[] = $row;
			}
			$forms = self::prepareForms($forms);
		}
		elseif (self::isB24Connector())
		{
			$client = \Bitrix\Socialservices\ApClient::init();
			if ($client)
			{
				$res = $client->call('crm.webform.list');
				if (isset($res['result']) && is_array($res['result']))
				{
					$forms = self::prepareForms($res['result']);
				}
			}
		}
		
		return $forms;
	}
	
	
	/**
	 * Move callback form to end.
	 * @param array $forms Forms array.
	 * @return array
	 */
	protected static function prepareForms($forms)
	{
		$formsCallback = array();
		$formsAll = array();
		
		foreach ($forms as $form)
		{
			if ($form['IS_CALLBACK_FORM'] == 'Y')
			{
				$formsCallback[] = array(
					'name' => $form['NAME'],
					'value' => $form['ID'] . '|' . $form['SECURITY_CODE'],
				);
			}
			else
			{
				$formsAll[] = array(
					'name' => $form['NAME'],
					'value' => $form['ID'] . '|' . $form['SECURITY_CODE'],
				);
			}
		}
		
		return array_merge($formsAll, $formsCallback);
	}
	
	/**
	 * Gets attrs for form.
	 * @return array
	 */
	protected static function getAttrs()
	{
		static $attrs = NULL;
		if ($attrs !== NULL)
		{
			return $attrs;
		}

		// get from CRM or via connector
		$forms = self::getForms();
		$attrs = array();

		// create data-attributes list
		if (!empty($forms))
		{
			// portal domain
			$attrs[] = array(
				'attribute' => 'data-b24form-original-domain',
				'hidden' => true,
			);
			// get forms list
			$attrs[] = array(
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
				'attribute' => 'data-b24form',
				'items' => $forms,
				'type' => 'list',
			);
			// show header
			$attrs[] = array(
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_SHOW_HEADER'),
				'attribute' => 'data-b24form-show-header',
				'type' => 'list',
				'items' => array(
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_SHOW_HEADER_Y'),
						'value' => 'Y',
					),
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_SHOW_HEADER_N'),
						'value' => 'N',
					),
				),
			);
			// use custom css
			$attrs[] = array(
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE'),
				'attribute' => 'data-b24form-use-style',
				'type' => 'list',
				'items' => array(
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE_Y'),
						'value' => 'Y',
					),
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE_N'),
						'value' => 'N',
					),
				),
			);
		}
		// no form - no settings, just message for user
		else
		{
			// todo: alert for user or empty selector
			// portal or SMN with b24connector
			if (Manager::isB24() || self::isB24Connector())
			{
				$attrs[] = array(
					'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
					'attribute' => 'data-b24form',
					'type' => 'list',
					'items' => array(
						array(
							'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
							'value' => false,
						),
					),
				);
			}
			// siteman
			else
			{
				// todo: no select, just text
				$attrs[] = array(
					'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
					'attribute' => 'data-b24form',
					'type' => 'list',
					'items' => array(
						array(
							'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
							'value' => false,
						),
					),
				
				);
			}
		}
		
		return $attrs;
	}
	
	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, \Bitrix\Landing\Block $block = null, array $params = array())
	{
		// add extension
		if (
			!isset($manifest['assets']) ||
			!is_array($manifest['assets'])
		)
		{
			$manifest['assets'] = array();
		}
		if (
			!isset($manifest['assets']['ext']) ||
			!is_array($manifest['assets']['ext'])
		)
		{
			$manifest['assets']['ext'] = array();
		}
		if (!in_array('landing_form', $manifest['assets']['ext']))
		{
			$manifest['assets']['ext'][] = 'landing_form';
		}

		// if no forms
		if (empty(self::getForms()))
		{
			$manifest['requiredUserAction'] = array(
				'header' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
				'description' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM_BUS')
			);
		}

		// add callbacks
		$manifest['callbacks'] = array(
			'afterAdd' => function (\Bitrix\Landing\Block &$block)
			{
				$forms = self::getForms();
				if (!empty($forms))
				{
					$attrsToSet = array(
						'data-b24form' => $forms[0]['value'],
						'data-b24form-original-domain' => str_replace(
							':' . $_SERVER['SERVER_PORT'],
							'',
							$_SERVER['HTTP_HOST']
						),
					);
					
					// if use b24 connector - need get portal url
					if (self::isB24Connector())
					{
						$client = \Bitrix\Socialservices\ApClient::init();
						$connection = $client->getConnection();
						$attrsToSet['data-b24form-original-domain'] = $connection['DOMAIN'];
					}
					
					$block->setAttributes(array(
						'.bitrix24forms' => $attrsToSet
					));
					$block->save();
				}
			},
		);
		
		// add attrs
		$manifest['attrs'] = array(
			'.bitrix24forms' => self::getAttrs(),
		);
		
		return $manifest;
	}
}
