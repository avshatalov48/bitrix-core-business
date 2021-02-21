<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Block;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Crm\WebForm\Internals\FormTable;
use Bitrix\Crm\UI\Webpack;
use Bitrix\Socialservices\ApClient;

Loc::loadMessages(__FILE__);

class Form
{
	protected const ATTR_FORM_PARAMS = 'data-b24form';
	protected const ATTR_FORM_EMBED = 'data-b24form-embed';
	protected const ATTR_FORM_STYLE = 'data-b24form-design';
	protected const ATTR_FORM_USE_STYLE = 'data-b24form-use-style';
	protected const ATTR_FORM_FROM_CONNECTOR = 'data-b24form-connector';
	protected const ATTR_FORM_OLD_DOMAIN = 'data-b24form-original-domain';
	protected const ATTR_FORM_OLD_HEADER = 'data-b24form-show-header';
	protected const SELECTOR_FORM_NODE = '.bitrix24forms';
	protected const SELECTOR_OLD_STYLE_NODE = '.landing-block-form-styles';
	protected const STYLE_SETTING = 'crm-form';
	protected const REGEXP_FORM_STYLE = '/data-b24form-design *= *[\'"](\{.+\})[\'"]/i';

	/**
	 * Check if b24 or box portal
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function isCrm(): bool
	{
		return Loader::includeModule('crm');
	}

	/**
	 * Gets web forms in system.
	 * @param bool $force - if true - get forms forcibly w/o cache
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function getForms(bool $force = false): array
	{
		static $forms = [];
		if ($forms && !$force)
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

		return $forms;
	}

	protected static function getFormsForPortal(): array
	{
		$res = FormTable::getList([
			'select' => [
				'ID',
				'NAME',
				'SECURITY_CODE',
				'IS_CALLBACK_FORM',
				'ACTIVE',
			],
			'order' => [
				'ID' => 'ASC',
			],
		]);

		$forms = [];
		while ($form = $res->fetch())
		{
			$form['ID'] = (int) $form['ID'];
			$webpack = Webpack\Form::instance($form['ID']);
			if (!$webpack->isBuilt())
			{
				$webpack->build();
				$webpack = Webpack\Form::instance($form['ID']);
			}
			$form['URL'] = $webpack->getEmbeddedFileUrl();
			$forms[$form['ID']] = $form;
		}

		return $forms;
	}

	protected static function getFormsViaConnector(): array
	{
		// todo: test on bus
		$forms = [];
		$client = ApClient::init();
		if ($client)
		{
			$res = $client->call('crm.webform.list', ['GET_INACTIVE' => 'Y']);
			if (isset($res['result']) && is_array($res['result']))
			{
				foreach($res['result'] as $form)
				{
					$form['ID'] = (int) $form['ID'];
					$forms[$form['ID']] = $form;
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
	protected static function prepareFormsToAttrs(array $forms): array
	{
		$sorted = [];
		foreach ($forms as $form)
		{
			if(array_key_exists('ACTIVE', $form) && $form['ACTIVE'] !== 'Y')
			{
				continue;
			}

			$item = [
				'name' => $form['NAME'],
				'value' => "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}",
			];

			if ($form['IS_CALLBACK_FORM'] === 'Y')
			{
				$sorted[] = $item;
			}
			else
			{
				array_unshift($sorted, $item);
			}
		}

		return $sorted;
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
		$forms = self::prepareFormsToAttrs($forms);

		$attrs = [
			$attrs[] = [
				'name' => 'Embed form flag',
				'attribute' => self::ATTR_FORM_EMBED,
				'type' => 'string',
				'hidden' => true,
			],
			[
				'name' => 'Form design',
				'attribute' => self::ATTR_FORM_STYLE,
				'type' => 'string',
				'hidden' => true,
			],
			[
				'name' => 'Form from connector flag',
				'attribute' => self::ATTR_FORM_FROM_CONNECTOR,
				'type' => 'string',
				'hidden' => true,
			],
		];

		if (!empty($forms))
		{
			// get forms list
			$attrs[] = [
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
				'attribute' => self::ATTR_FORM_PARAMS,
				'items' => $forms,
				'type' => 'list',
			];
			// show header
			// use custom design
			$attrs[] = [
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE'),
				'attribute' => self::ATTR_FORM_USE_STYLE,
				'type' => 'list',
				'items' => [
					[
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE_Y'),
						'value' => 'Y',
					],
					[
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_USE_STYLE_N'),
						'value' => 'N',
					],
				],
			];
		}
		// no form - no settings, just message for user
		else
		{
			// portal or SMN with b24connector
			if (Manager::isB24() || Manager::isB24Connector())
			{
				// todo:need alert?
				$attrs[] = [
					'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
					'attribute' => self::ATTR_FORM_PARAMS,
					'type' => 'list',
					'items' => [
						[
							'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
							'value' => false,
						],
					],
				];
			}
			// siteman
			else
			{
				// todo: need?
				$attrs[] = [
					'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
					'attribute' => self::ATTR_FORM_PARAMS,
					'type' => 'list',
					'items' => [
						[
							'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
							'value' => false,
						],
					],

				];
			}
		}

		return $attrs;
	}

	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, Block $block = null, array $params = []): array
	{
		// add extension
		if (!isset($manifest['assets']) || !is_array($manifest['assets']))
		{
			$manifest['assets'] = [];
		}
		if (!isset($manifest['assets']['ext']))
		{
			$manifest['assets']['ext'] = [];
		}
		if (!is_array($manifest['assets']['ext']))
		{
			$manifest['assets']['ext'] = [$manifest['assets']['ext']];
		}
		if (!in_array('landing_form', $manifest['assets']['ext'], true))
		{
			$manifest['assets']['ext'][] = 'landing_form';
		}

		// style setting
		if (!is_array($manifest['style']['block']) && !is_array($manifest['style']['nodes']))
		{
			$manifest['style'] = [
				'block' => [],
				'nodes' => $manifest['style'],
			];
		}
		$manifest['style']['nodes'][self::SELECTOR_FORM_NODE] = [
			'type' => self::STYLE_SETTING,
		];

		// // add settings link
		// if (!isset($manifest['block']) || !is_array($manifest['block']))
		// {
		// 	$manifest['block'] = [];
		// }

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
		$manifest['callbacks'] = [
			'afterAdd' => function (Block &$block)
			{
				$forms = self::getForms();
				$forms = self::prepareFormsToAttrs($forms);
				$attrsToSet = [self::ATTR_FORM_EMBED => ''];
				if (!empty($forms))
				{
					$attrsToSet[self::ATTR_FORM_PARAMS] = $forms[0]['value'];
				}
				// set SMN flag
				if (!self::isCrm())
				{
					$attrsToSet[self::ATTR_FORM_FROM_CONNECTOR] = 'Y';
				}

				$dom = $block->getDom();
				if ($node = $dom->querySelector(self::SELECTOR_FORM_NODE))
				{
					$attrsExists = $node->getAttributes();
					if (
						$attrsExists[self::ATTR_FORM_PARAMS]
						&& $formParamsExists = $attrsExists[self::ATTR_FORM_PARAMS]->getValue()
					)
					{
						$attrsToSet[self::ATTR_FORM_PARAMS] = $formParamsExists;
					}
					$node->setInnerHTML(
						'<div class="g-landing-alert">'
						. Loc::getMessage('LANDING_BLOCK_WEBFORM_PRELOADER')
						. '</div>'
					);
					$block->saveContent($dom->saveHTML());
				}

				$block->setAttributes([
					self::SELECTOR_FORM_NODE => $attrsToSet,
				]);
				$block->save();
			},
		];

		// add attrs
		if (
			!array_key_exists('attrs', $manifest)
			|| !is_array($manifest['attrs'])
		)
		{
			$manifest['attrs'] = [];
		}
		$manifest['attrs'][self::SELECTOR_FORM_NODE] = self::getAttrs();

		return $manifest;
	}

	public static function setFormIdToBlock(int $blockId, int $formId): void
	{
		$block = new Block($blockId);
		$forms = self::getForms(true);
		if (array_key_exists($formId, $forms))
		{
			$form = $forms[$formId];
			$newParam = "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}";
			$block->setAttributes([
				self::SELECTOR_FORM_NODE => [self::ATTR_FORM_PARAMS => $newParam],
			]);
			$block->save();
		}
	}

	/**
	 * Find old forms blocks and update to embed format
	 * @param int $landingId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function updateLandingToEmbedForms(int $landingId): void
	{
		$res = BlockTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'LID' => $landingId,
				'=DELETED' => 'N',
			],
		]);
		while ($row = $res->fetch())
		{
			$block = new Block($row['ID']);
			self::updateBlockToEmbed($block);
		}
	}

	protected static function updateBlockToEmbed(Block $block): void
	{
		$manifest = $block->getManifest();
		if (
			!$manifest['block']['subtype']
			|| (!is_array($manifest['block']['subtype']) && $manifest['block']['subtype'] !== 'form')
			|| (is_array($manifest['block']['subtype']) && !in_array('form', $manifest['block']['subtype'], true))
		)
		{
			return;
		}

		$dom = $block->getDom();
		if (
			!($resultNode = $dom->querySelector(self::SELECTOR_FORM_NODE))
			|| !($attrs = $resultNode->getAttributes())
			|| !array_key_exists(self::ATTR_FORM_PARAMS, $attrs))
		{
			return;
		}
		$formParams = explode('|', $attrs[self::ATTR_FORM_PARAMS]->getValue());
		if (count($formParams) !== 2 || !(int)$formParams[0])
		{
			return;
		}

		foreach (self::getForms() as $form)
		{
			if ($form['ID'] == $formParams[0])
			{
				$newParams = "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}";
				$resultNode->setAttribute(self::ATTR_FORM_PARAMS, $newParams);
				$resultNode->setAttribute(self::ATTR_FORM_EMBED, '');
				$resultNode->removeAttribute(self::ATTR_FORM_OLD_DOMAIN);
				$resultNode->removeAttribute(self::ATTR_FORM_OLD_HEADER);

				if (
					!array_key_exists(self::ATTR_FORM_STYLE, $attrs)
					|| !$attrs[self::ATTR_FORM_STYLE]->getValue()
				)
				{
					// find new styles
					$contentFromRepo = Block::getContentFromRepository($block->getCode());
					if (
						$contentFromRepo
						&& preg_match(self::REGEXP_FORM_STYLE, $contentFromRepo, $style)
					)
					{
						$resultNode->setAttribute(self::ATTR_FORM_STYLE, $style[1]);
					}
				}
			}
		}

		if ($oldStyleNode = $dom->querySelector(self::SELECTOR_OLD_STYLE_NODE))
		{
			$oldStyleNode->getParentNode()->removeChild($oldStyleNode);
		}

		$block->saveContent($dom->saveHTML());
		$block->save();
	}

	/**
	 * Get original domain for web-forms.
	 * @return string
	 * @deprecated
	 */
	public static function getOriginalFormDomain(): string
	{
		trigger_error(
			"Now using embedded forms, no need domain",
			E_USER_WARNING
		);

		return '';
	}
}
