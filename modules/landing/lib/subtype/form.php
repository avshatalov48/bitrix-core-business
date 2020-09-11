<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Loader;
use \Bitrix\Crm\WebForm\Internals\FormTable;
use \Bitrix\Socialservices\ApClient;

Loc::loadMessages(__FILE__);

class Form
{
	/**
	 * Check if b24 or box portal
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function isCrm()
	{
		return Loader::includeModule('crm');
	}
	
	/**
	 * Gets web forms in system.
	 * @return array
	 */
	protected static function getForms()
	{
		static $forms = array();
		if ($forms)
		{
			return $forms;
		}
		
		if (self::isCrm())
		{
			$forms = self::getFormsForPortal();
		}
		elseif (Manager::isB24Connector())
		{
			$forms = self::getFormsViaConnector();
		}
		$forms = self::prepareForms($forms);
		
		return $forms;
	}
	
	protected static function getFormsForPortal()
	{
		$forms = array();
		$res = FormTable::getList(array(
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
		
		return $forms;
	}
	
	protected static function getFormsViaConnector()
	{
		$forms = array();
		$client = ApClient::init();
		if ($client)
		{
			$res = $client->call('crm.webform.list');
			if (isset($res['result']) && is_array($res['result']))
			{
				$forms = $res['result'];
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
		static $attrs = [];
		if ($attrs)
		{
			return $attrs;
		}
		
		// get from CRM or via connector
		$forms = self::getForms();
		
		
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
			// portal or SMN with b24connector
			if (Manager::isB24() || Manager::isB24Connector())
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
				
				$attrs[] = array(
					'attribute' => 'data-b24form-connector',
					'hidden' => true,
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
		
		// add settings link
		if (
			!isset($manifest['block']) ||
			!is_array($manifest['block'])
		)
		{
			$manifest['block'] = array();
		}
		if (Manager::isB24())
		{
			$link = '/crm/webform/';
		}
		else if (Manager::isB24Connector())
		{
			$link = '/bitrix/admin/b24connector_crm_forms.php?lang=' . LANGUAGE_ID;
		}
		if (isset($link))
		{
			$manifest['block']['attrsFormDescription'] = '<a href="' . $link . '" target="_blank">' .
				Loc::getMessage('LANDING_BLOCK_FORM_CONFIG') .
				'</a>';
		}

		// if no forms - will be show alert in javascript form init

		// add callbacks
		$manifest['callbacks'] = array(
			'afterAdd' => function(\Bitrix\Landing\Block &$block)
			{
				$forms = self::getForms();
				$attrsToSet = [
					'data-b24form' => '',
					'data-b24form-original-domain' => '',
				];
				if (!empty($forms))
				{
					$attrsToSet['data-b24form'] = $forms[0]['value'];
					$attrsToSet['data-b24form-original-domain'] = self::getOriginalFormDomain();
					
					// When create preview sites on repo need set demo portal
					if ((defined('LANDING_IS_REPO') && LANDING_IS_REPO === true))
					{
						$attrsToSet['data-b24form'] = '1|n3j8e2';
						$attrsToSet['data-b24form-original-domain'] = 'https://landing.bitrix24.ru';
					}
				}
				
				// set BUS flag
				if (!self::isCrm())
				{
					$attrsToSet["data-b24form-connector"] = 'Y';
				}
				
				$block->setAttributes(array(
					'.bitrix24forms' => $attrsToSet,
				));
				$block->save();
			},
		);

		if(
			!array_key_exists('attrs', $manifest)
			|| !is_array($manifest['attrs'])
		)
		{
			$manifest['attrs'] = [];
		}
		$manifest['attrs']['.bitrix24forms'] = self::getAttrs();

		return $manifest;
	}

	/**
	 * Get original domain for web-forms.
	 * @return string
	 */
	public static function getOriginalFormDomain()
	{
		$formDomain = '';

		// if is b24 portal - use just them domain
		if (Manager::isB24())
		{
			$formDomain = (\CMain::IsHTTPS() ? 'https://' : 'http://') . str_replace(
					array('http://', 'http://', ':' . $_SERVER['SERVER_PORT']),
					'', $_SERVER['HTTP_HOST']
				);
		}
		// if use b24 connector - need get portal url
		else if (Manager::isB24Connector())
		{
			if ($client = ApClient::init())
			{
				$connection = $client->getConnection();
				$domain = parse_url($connection['ENDPOINT']);
				$formDomain = $domain['scheme'] . '://' . $domain['host'];
			}
		}
		
		return $formDomain;
	}
}
