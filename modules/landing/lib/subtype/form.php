<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Crm\Integration\UserConsent;
use Bitrix\Crm\Settings\LeadSettings;
use Bitrix\Crm\UI\Webpack;
use Bitrix\Crm\WebForm;
use Bitrix\Landing\Block;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Site;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialservices\ApClient;

Loc::loadMessages(__FILE__);

/**
 * Subtype for blocks with CRM-forms
 * @package Bitrix\Landing\Subtype
 */
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
	protected const REGEXP_FORM_ID_INLINE = '/data-b24form=["\']#crmFormInline(?<id>[\d]+)["\']/i';

	public const INLINE_MARKER_PREFIX = '#crmFormInline';
	public const POPUP_MARKER_PREFIX = '#crmFormPopup';

	protected const AVAILABLE_FORM_FIELDS = [
		'ID',
		'NAME',
		'SECURITY_CODE',
		'IS_CALLBACK_FORM',
		'ACTIVE',
		'XML_ID',
	];

	/**
	 * Replace form markers in block, put true scripts. Run on publication action
	 * @param string $content - content of block
	 * @return string - replaced content
	 */
	public static function prepareFormsToPublication(string $content): string
	{
		if (!self::isCrm() && Manager::isB24Connector())
		{
			$content = self::replaceFormMarkers($content);
		}
		return $content;
	}

	/**
	 * Replace form markers in block, put true scripts. Run on view in public mode
	 * @param string $content - content of block
	 * @return string - replaced content
	 */
	public static function prepareFormsToView(string $content): string
	{
		if (self::isCrm())
		{
			$content = self::replaceFormMarkers($content);
		}
		return $content;
	}

	/**
	 * Replaces and returns all #crmForm-link to the popup codes or in inline forms
	 * For CP - every hit (cached), for SMN - on public
	 * @param string $content Some content.
	 * @return string
	 */
	protected static function replaceFormMarkers(string $content): string
	{
		$replace = preg_replace_callback(
			'/(?<pre><a[^>]+href=|data-b24form=)["\']#crmForm(?<type>Inline|Popup)(?<id>[\d]+)["\']/i',
			static function ($matches)
			{
				if (
					!($forms = self::getForms())
					|| !array_key_exists($matches['id'], $forms)
				)
				{
					return $matches[0];
				}
				$form = $forms[$matches['id']];

				if (strtolower($matches['type']) === 'inline')
				{
					$param = "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}";

					return $matches['pre'] . "\"{$param}\"";
				}

				if (strtolower($matches['type']) === 'popup')
				{
					$script = "<script data-b24-form=\"click/{$matches['id']}/{$form['SECURITY_CODE']}\" data-skip-moving=\"true\">
								(function(w,d,u){
									var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/180000|0);
									var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
								})(window,document,'{$form['URL']}');
							</script>";

					return $script . $matches['pre'] . "\"#\" onclick=\"BX.PreventDefault();\"";
				}

				return $matches[0];
			},
			$content
		);

		return $replace ?? $content;
	}

	/**
	 * Clears cache all sites with blocks.
	 * @return void
	 */
	public static function clearCache(): void
	{
		$sites = [];
		$res = BlockTable::getList([
			'select' => [
				'SITE_ID' => 'LANDING.SITE_ID'
			],
			'filter' => [
				'=LANDING.ACTIVE' => 'Y',
				'=LANDING.SITE.ACTIVE' => 'Y',
				'=PUBLIC' => 'Y',
				'=DELETED' => 'N',
				'CONTENT' => '%bitrix24forms%'
			],
			'group' => [
				'LANDING.SITE_ID'
			]
		]);
		while ($row = $res->fetch())
		{
			if (!in_array($row['SITE_ID'], $sites))
			{
				$sites[] = $row['SITE_ID'];
			}
		}

		foreach ($sites as $site)
		{
			Site::update($site, [
				'DATE_MODIFY' => false
			]);
		}
	}

	/**
	 * Check if b24 or box portal
	 * @return bool
	 */
	protected static function isCrm(): bool
	{
		return Loader::includeModule('crm');
	}

	/**
	 * Gets web forms in system.
	 * @param bool $force - if true - get forms forcibly w/o cache
	 * @return array
	 */
	public static function getForms(bool $force = false): array
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

	/**
	 * Find just one form by ID. Return array of form fields, or empty array if not found
	 * @return array
	*/
	public static function getFormById(int $id): array
	{
		$forms = self::getFormsByFilter(['ID' => $id]);

		return !empty($forms) ? array_shift($forms) : [];
	}

	/**
	 * Find only callback forms. Return array of form arrays, or empty array if not found
	 * @return array
	 */
	public static function getCallbackForms(): array
	{
		return self::getFormsByFilter(['IS_CALLBACK_FORM' => 'Y', 'ACTIVE' => 'Y']);
	}

	protected static function getFormsByFilter(array $filter): array
	{
		$filter = array_filter(
			$filter,
			static function ($key)
			{
				return in_array($key, self::AVAILABLE_FORM_FIELDS, true);
			},
			ARRAY_FILTER_USE_KEY
		);
		$forms = [];

		if (self::isCrm())
		{
			$forms = self::getFormsForPortal($filter);
		}
		elseif (Manager::isB24Connector())
		{
			foreach (self::getFormsViaConnector() as $form)
			{
				$filtred = true;
				foreach ($filter as $key => $value)
				{
					if (!$form[$key] || $form[$key] !== $value)
					{
						$filtred = false;
						break;
					}
				}
				if($filtred)
				{
					$forms[$form['ID']] = $form;
				}
			}
		}

		return $forms;
	}

	protected static function getFormsForPortal(array $filter = []): array
	{
		$res = Webform\Internals\FormTable::getList([
			'select' => self::AVAILABLE_FORM_FIELDS,
			'filter' => $filter,
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
				'value' => self::INLINE_MARKER_PREFIX . $form['ID'],
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
	protected static function getAttrs(): array
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
	 * @param Block|null $block Block instance.
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

		// add callbacks
		$manifest['callbacks'] = [
			'afterAdd' => function (Block &$block)
			{
				$dom = $block->getDom();
				if (!($node = $dom->querySelector(self::SELECTOR_FORM_NODE)))
				{
					return;
				}

				$attrsToSet = [self::ATTR_FORM_EMBED => ''];
				if (!self::isCrm())
				{
					$attrsToSet[self::ATTR_FORM_FROM_CONNECTOR] = 'Y';
				}

				// if block copy - not update params
				if (
					($attrsExists = $node->getAttributes())
					&& $attrsExists[self::ATTR_FORM_PARAMS]
					&& $formParamsExists = $attrsExists[self::ATTR_FORM_PARAMS]->getValue()
				)
				{
					$attrsToSet[self::ATTR_FORM_PARAMS] = $formParamsExists;
				}
				else
				{
					$forms = self::getForms();
					$forms = self::prepareFormsToAttrs($forms);

					if (!empty($forms))
					{
						self::setFormIdParam(
							$block,
							str_replace(self::INLINE_MARKER_PREFIX, '', $forms[0]['value'])
						);
					}
				}

				// preload alert
				$node->setInnerHTML(
					'<div class="g-landing-alert">'
					. Loc::getMessage('LANDING_BLOCK_WEBFORM_PRELOADER')
					. '</div>'
				);
				$block->saveContent($dom->saveHTML());

				// save
				$block->setAttributes([self::SELECTOR_FORM_NODE => $attrsToSet]);
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

	/**
	 * @param int|array $landingIds - int or [int] of landing IDs
	 * @return array of all block with CRM-forms at this page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getLandingFormBlocks($landingIds): array
	{
		if (empty($landingIds))
		{
			return [];
		}

		if (!is_array($landingIds))
		{
			$landingIds = [$landingIds];
		}

		return BlockTable::getList([
			'select' => ['ID', 'LID'],
			'filter' => [
				'=LID' => $landingIds,
				'=DELETED' => 'N',
				'CONTENT' => '%data-b24form=%',
			],
		])->fetchAll();
	}

	/**
	 * Return CRM-form ID from block, if exists. Else return null;
	 * @param int $blockId
	 * @return int|null
	 */
	public static function getFormByBlock(int $blockId): ?int
	{
		$block = new Block($blockId);
		if(preg_match(self::REGEXP_FORM_ID_INLINE, $block->getContent(), $matches))
		{
			return (int)$matches[1];
		}
		return null;
	}

	/**
	 * Save form params in block for current form
	 * @param int $blockId - from landing block table
	 * @param int $formId - from webform table
	 */
	public static function setFormIdToBlock(int $blockId, int $formId): void
	{
		$block = new Block($blockId);
		// on form create need forced get forms!
		$forms = self::getForms(true);
		if (array_key_exists($formId, $forms))
		{
			self::setFormIdParam($block, $formId);
			$block->save();
		}
	}

	/**
	 * Encapsulates the form params save logic
	 * @param Block $block
	 * @param int $formId - from webform table
	 */
	protected static function setFormIdParam(Block $block, int $formId): void
	{
		$forms = self::getForms();
		if (($form = $forms[$formId]))
		{
			$newParam = $block->isPublic() && !self::isCrm() && Manager::isB24Connector()
				? "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}"
				: self::INLINE_MARKER_PREFIX . $form['ID'];

			$block->setAttributes([
				self::SELECTOR_FORM_NODE => [self::ATTR_FORM_PARAMS => $newParam],
			]);
		}
	}

	/**
	 * @param Block $block
	 * @param string $xmlId
	 */
	public static function setSpecialFormToBlock(Block $block, string $xmlId): void
	{
		// todo: if not crm or connector - break
		if (($formData = self::getSpecialFormsData()[$xmlId]))
		{
			$formId = null;
			foreach(self::getForms() as $form)
			{
				if(
					array_key_exists('XML_ID', $form)
					&& $form['XML_ID'] === $xmlId
				)
				{
					$formId = $form['ID'];
					break;
				}
			}

			if(!$formId)
			{// todo: find xmlid in getForms
				if (self::isCrm())
				{
					$form = new WebForm\Form;
					$formData['BUTTON_CAPTION'] = $form->getButtonCaption();
					$form->merge($formData);
					$form->save();
					if(!$form->hasErrors())
					{
						$formId = $form->getId();
					}
				}
				elseif (Manager::isB24Connector())
				{
					// todo: rest
				}
			}

			if ($formId)
			{
				self::setFormIdParam($block, $formId);
				$block->save();
			}
		}
	}

	protected static function getSpecialFormsData(): array
	{
		// todo: clear comments
		// todo: params from \Bitrix\Crm\WebForm\Preset::addForm
		$data = [
			'crm_preset_store_v3' => [
				'XML_ID' => 'crm_preset_store_v3',
				'NAME' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_NAME'),
				'IS_SYSTEM' => 'N',
				'ACTIVE' => 'Y',
				// 'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_CAPTION'),
				// 'DESCRIPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_DESC'),
				'RESULT_SUCCESS_TEXT' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_RESULT_SUCCESS'),
				'RESULT_FAILURE_TEXT' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_RESULT_FAILURE'),
				// 'ENTITY_SCHEME' => (string)(self::isLeadEnabled() ? Entity::ENUM_ENTITY_SCHEME_LEAD : Entity::ENUM_ENTITY_SCHEME_DEAL),
				'COPYRIGHT_REMOVED' => 'N',
				'IS_PAY' => 'N',
				'FORM_SETTINGS' => [
					'DEAL_DC_ENABLED' => 'Y',
				],
				'BUTTON_CAPTION' => '',
				'FIELDS' => [
					[
						'TYPE' => 'string',
						// 'CODE' => self::isLeadEnabled() ? 'LEAD_NAME' : 'CONTACT_NAME',
						'CODE' => 'CONTACT_NAME',
						'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_FIELD_NAME'),
						'SORT' => 100,
						'REQUIRED' => 'N',
						'MULTIPLE' => 'N',
						'PLACEHOLDER' => '',
					],
					[
						'TYPE' => 'phone',
						// 'CODE' => self::isLeadEnabled() ? 'LEAD_PHONE' : 'CONTACT_PHONE',
						'CODE' => 'CONTACT_PHONE',
						'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_FIELD_PHONE'),
						'SORT' => 200,
						'REQUIRED' => 'N',
						'MULTIPLE' => 'N',
						'PLACEHOLDER' => '',
					],
					[
						'TYPE' => 'text',
						// 'CODE' => self::isLeadEnabled() ? 'LEAD_COMMENTS' : 'DEAL_COMMENTS',
						'CODE' => 'DEAL_COMMENTS',
						'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_FIELD_COMMENT'),
						'SORT' => 300,
						'REQUIRED' => 'N',
						'MULTIPLE' => 'N',
						'PLACEHOLDER' => '',
					],
				],
			],
		];

		if (self::isCrm())
		{
			$isLeadEnabled = static function()
			{
				return LeadSettings::getCurrent()->isEnabled();
			};

			$getCurrentUserId = static function()
			{
				static $userId = null;
				if($userId === null)
				{
					global $USER;
					$userId = (is_object($USER) && $USER->GetID()) ? $USER->GetID() : 1;
				}

				return $userId;
			};

			foreach($data as $id => $form)
			{
				$data[$id]['DUPLICATE_MODE'] = WebForm\ResultEntity::DUPLICATE_CONTROL_MODE_MERGE;
				$data[$id]['ENTITY_SCHEME'] = (string)($isLeadEnabled()
					? WebForm\Entity::ENUM_ENTITY_SCHEME_LEAD
					: WebForm\Entity::ENUM_ENTITY_SCHEME_DEAL)
				;
				$data[$id]['ACTIVE_CHANGE_BY'] = $getCurrentUserId();
				$data[$id]['ASSIGNED_BY_ID'] = $getCurrentUserId();

				$agreementId = UserConsent::getDefaultAgreementId();
				$data[$id]['USE_LICENCE'] = $agreementId ? 'Y': 'N';
				if ($agreementId)
				{
					$data[$id]['LICENCE_BUTTON_IS_CHECKED'] = 'Y';
					$data[$id]['AGREEMENT_ID'] = $agreementId;
				}

				if($isLeadEnabled())
				{
					foreach ($data[$id]['FIELDS'] as $key => $field)
					{
						$field['CODE'] = str_replace(['CONTACT', 'DEAL'], 'LEAD', $field['CODE']);
						$data[$id]['FIELDS'][$key] = $field;
					}
				}
			}
		}
		// else
		// {
			// todo: is connector - get by rest
		// }

		return $data;
	}

	/**
	 * Find old forms blocks and update to embed format
	 * @param int $landingId
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

	/**
	 * Migrate from old form to new embed, adjust block params, remove old style nodes
	 * @param Block $block
	 */
	protected static function updateBlockToEmbed(Block $block): void
	{
		// check if update needed
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

		// update
		$forms = self::getForms();
		if (array_key_exists($formParams[0], $forms))
		{
			$form = $forms[$formParams[0]];
			self::setFormIdParam($block, $form['ID']);
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

		if (($oldStyleNode = $dom->querySelector(self::SELECTOR_OLD_STYLE_NODE)))
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
