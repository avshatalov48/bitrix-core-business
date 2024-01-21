<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Crm\Integration\UserConsent;
use Bitrix\Crm\Settings\LeadSettings;
use Bitrix\Crm\UI\Webpack;
use Bitrix\Crm\WebForm;
use Bitrix\Landing\History;
use Bitrix\Landing\Landing;
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

	private static array $errors = [];

	// region replaces for view and public

	/**
	 * Replace form markers in block, put true scripts. Run on publication action
	 * @param string $content - content of block
	 * @return string - replaced content
	 */
	public static function prepareFormsToPublication(string $content): string
	{
		// change - replace markers always, not only if connector
		return self::replaceFormMarkers($content);
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
			'/(?<pre><a[^>]+href=|data-b24form=)["\'](form:)?#crmForm(?<type>Inline|Popup)(?<id>[\d]+)["\']/i',
			static function ($matches)
			{
				$id = (int)$matches['id'];
				if (!$id)
				{
					return $matches[0];
				}

				$form = self::getFormById($id);
				if (!$form || !$form['URL'])
				{
					return $matches[0];
				}

				if (strtolower($matches['type']) === 'inline')
				{
					$param = "{$form['ID']}|{$form['SECURITY_CODE']}|{$form['URL']}";

					return $matches['pre'] . "\"{$param}\"";
				}

				if (strtolower($matches['type']) === 'popup')
				{
					$script = "<script data-b24-form=\"click/{$id}/{$form['SECURITY_CODE']}\" data-skip-moving=\"true\">
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

		$replace = $replace ?? $content;

		//replace link to form in data-pseudo-url
		$replace = preg_replace_callback(
			'/(?<pre><img|<i.*)data-pseudo-url="{.*(form:)?#crmForm(?<type>Inline|Popup)(?<id>[\d]+).*}"(?<pre2>.*>)/i',
			static function ($matches)
			{
				if (
					!(int)$matches['id']
					|| !($form = self::getFormById((int)$matches['id']))
				)
				{
					return $matches[0];
				}

				if (strtolower($matches['type']) === 'popup')
				{
					$script = "<script data-b24-form=\"click/{$matches['id']}/{$form['SECURITY_CODE']}\" data-skip-moving=\"true\">
								(function(w,d,u){
									var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/180000|0);
									var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
								})(window,document,'{$form['URL']}');
							</script>";

					//add class g-cursor-pointer
					preg_match_all('/(class="[^"]*)/i', $matches['pre'], $matchesPre);
					$matches['pre'] = str_replace($matchesPre[1][0], $matchesPre[1][0]. ' g-cursor-pointer', $matches['pre']);

					return $script . $matches['pre'] . ' '. $matches['pre2'];
				}

				return $matches[0];
			},
			$replace
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
		$res = BlockTable::getList(
			[
				'select' => [
					'SITE_ID' => 'LANDING.SITE_ID',
				],
				'filter' => [
					'=LANDING.ACTIVE' => 'Y',
					'=LANDING.SITE.ACTIVE' => 'Y',
					'=PUBLIC' => 'Y',
					'=DELETED' => 'N',
					'CONTENT' => '%bitrix24forms%',
				],
				'group' => [
					'LANDING.SITE_ID',
				],
			]
		);
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
	// endregion

	// region get forms
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
	 * Check if b24 or box portal
	 * @return bool
	 */
	protected static function isCrm(): bool
	{
		return Loader::includeModule('crm');
	}

	protected static function getFormsForPortal(array $filter = []): array
	{
		$res = Webform\Internals\FormTable::getDefaultTypeList(
			[
				'select' => self::AVAILABLE_FORM_FIELDS,
				'filter' => $filter,
				'order' => [
					'ID' => 'ASC',
				],
			]
		);

		$forms = [];
		while ($form = $res->fetch())
		{
			$form['ID'] = (int)$form['ID'];
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
				foreach ($res['result'] as $form)
				{
					$form['ID'] = (int)$form['ID'];
					$forms[$form['ID']] = $form;
				}
			}
			else if (isset($res['error']))
			{
				self::$errors[] = [
					'code' => $res['error'],
					'message' => $res['error_description'] ?? $res['error'],
				];
			}
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
				if ($filtred)
				{
					$forms[$form['ID']] = $form;
				}
			}
		}

		return $forms;
	}

	// endregion

	// region prepare manifest
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
		if (
			!isset($manifest['style']['block']) && !isset($manifest['style']['nodes'])
		)
		{
			$manifest['style'] = [
				'block' => ['type' => Block::DEFAULT_WRAPPER_STYLE],
				'nodes' => $manifest['style'] ?? [],
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
				$historyActivity = History::isActive();
				History::deactivate();

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
					&& isset($attrsExists[self::ATTR_FORM_PARAMS])
					&& $attrsExists[self::ATTR_FORM_PARAMS]
					&& $attrsExists[self::ATTR_FORM_PARAMS]->getValue()
				)
				{
					$attrsToSet[self::ATTR_FORM_PARAMS] = $attrsExists[self::ATTR_FORM_PARAMS]->getValue();
				}
				else
				{
					// try to get 1) default callback form 2) last added form 3) create new form
					$forms = self::getFormsByFilter([
						'XML_ID' => 'crm_preset_fb'
					]);
					$forms = self::prepareFormsToAttrs($forms);
					if (empty($forms))
					{
						$forms = self::getForms(true);  // force to preserve cycle when create form landing block
						$forms = self::prepareFormsToAttrs($forms);
						if (empty($forms))
						{
							$forms = self::createDefaultForm();
							$forms = self::prepareFormsToAttrs($forms);
						}
					}

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

				$historyActivity ? History::activate() : History::deactivate();
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

		// hard operation getAttrs is only FOR EDITOR, in public set fake array for saveAttributes later
		$manifest['attrs'][self::SELECTOR_FORM_NODE] =
			Landing::getEditMode()
				? self::getAttrs()
				: [['attribute' => self::ATTR_FORM_PARAMS]];

		return $manifest;
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
			$attrs[] = [
				'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM'),
				'attribute' => self::ATTR_FORM_PARAMS,
				'type' => 'list',
				'items' => !empty(self::$errors)
					? array_map(fn ($item) => ['name' => $item['message'], 'value' => false], self::$errors)
					: [[
						'name' => Loc::getMessage('LANDING_BLOCK_WEBFORM_NO_FORM'),
						'value' => false,
					]],
			];
		}

		return $attrs;
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
			if (array_key_exists('ACTIVE', $form) && $form['ACTIVE'] !== 'Y')
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
	// endregion

	// region actions with blocks and forms
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

		return BlockTable::getList(
			[
				'select' => ['ID', 'LID'],
				'filter' => [
					'=LID' => $landingIds,
					'=DELETED' => 'N',
					'CONTENT' => '%data-b24form=%',
				],
			]
		)->fetchAll()
			;
	}

	/**
	 * Return CRM-form ID from block, if exists. Else return null;
	 * @param int $blockId
	 * @return int|null
	 */
	public static function getFormByBlock(int $blockId): ?int
	{
		$block = new Block($blockId);
		if (preg_match(self::REGEXP_FORM_ID_INLINE, $block->getContent(), $matches))
		{
			return (int)$matches[1];
		}
		return null;
	}

	/**
	 * Save form params in block for current form
	 * @param int $blockId - from landing block table
	 * @param int $formId - from webform table
	 * @return bool - true if success, false if errors
	 */
	public static function setFormIdToBlock(int $blockId, int $formId): bool
	{
		$block = new Block($blockId);
		self::setFormIdParam($block, $formId);
		$block->save();

		return $block->getError()->isEmpty();
	}

	/**
	 * Encapsulates the form params save logic
	 * @param Block $block
	 * @param int $formId - from webform table
	 */
	protected static function setFormIdParam(Block $block, int $formId): void
	{
		if (($form = self::getFormById($formId)))
		{
			// todo: can add force public flag for replaces, when we know exactly that block is public
			$newParam = self::INLINE_MARKER_PREFIX . $form['ID'];

			$block->setAttributes([
				self::SELECTOR_FORM_NODE => [self::ATTR_FORM_PARAMS => $newParam],
			]);
		}
	}

	/**
	 * Create form with default params
	 * @return array - array with once item, fields equal getForms(). Or empty array if not created
	 */
	protected static function createDefaultForm(): array
	{
		if ($formId = self::createForm([]))
		{
			return self::getFormsByFilter(['ID' => $formId]);
		}

		return [];
	}

	/**
	 * @param array $formData
	 * @return int|null - id of created form or null if errors
	 */
	protected static function createForm(array $formData): ?int
	{
		if (self::isCrm())
		{
			$form = new WebForm\Form;

			$defaultData = WebForm\Preset::getById('crm_preset_cd');

			$defaultData['XML_ID'] = '';
			$defaultData['ACTIVE'] = 'Y';
			$defaultData['IS_SYSTEM'] = 'N';
			$defaultData['IS_CALLBACK_FORM'] = 'N';
			$defaultData['BUTTON_CAPTION'] = $form->getButtonCaption();

			$agreementId = UserConsent::getDefaultAgreementId();
			$defaultData['USE_LICENCE'] = $agreementId ? 'Y' : 'N';
			if ($agreementId)
			{
				$defaultData['LICENCE_BUTTON_IS_CHECKED'] = 'Y';
				$defaultData['AGREEMENT_ID'] = $agreementId;
			}

			$isLeadEnabled = LeadSettings::getCurrent()->isEnabled();
			$defaultData['ENTITY_SCHEME'] = (string)(
			$isLeadEnabled
				? WebForm\Entity::ENUM_ENTITY_SCHEME_LEAD
				: WebForm\Entity::ENUM_ENTITY_SCHEME_DEAL
			);

			$currentUserId = is_object($GLOBALS['USER']) ? $GLOBALS['USER']->getId() : null;
			$defaultData['ACTIVE_CHANGE_BY'] = $currentUserId;
			$defaultData['ASSIGNED_BY_ID'] = $currentUserId;

			$formData = array_merge($defaultData, $formData);
			$form->merge($formData);
			$form->save();

			return !$form->hasErrors() ? $form->getId() : null;
		}

		return null;
	}

	/**
	 * @param Block $block
	 * @param string $xmlId
	 */
	public static function setSpecialFormToBlock(Block $block, string $xmlId): void
	{
		if (($formData = self::getSpecialFormsData()[$xmlId]))
		{
			$formId = null;
			foreach (self::getForms() as $form)
			{
				if (
					array_key_exists('XML_ID', $form)
					&& $form['XML_ID'] === $xmlId
				)
				{
					$formId = $form['ID'];
					break;
				}
			}

			if (!$formId)
			{
				$formId = self::createForm($formData);
			}

			if ($formId)
			{
				self::setFormIdParam($block, $formId);
				$block->save();
			}
		}
	}

	protected static function getSpecialFormsData(): ?array
	{
		if (self::isCrm())
		{
			$data = [
				'crm_preset_store_v3' => [
					'XML_ID' => 'crm_preset_store_v3',
					'NAME' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_NAME'),
					'IS_SYSTEM' => 'N',
					'ACTIVE' => 'Y',
					'RESULT_SUCCESS_TEXT' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_RESULT_SUCCESS'),
					'RESULT_FAILURE_TEXT' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_RESULT_FAILURE'),
					'COPYRIGHT_REMOVED' => 'N',
					'IS_PAY' => 'N',
					'FORM_SETTINGS' => [
						'DEAL_DC_ENABLED' => 'Y',
					],
					'BUTTON_CAPTION' => '',
					'FIELDS' => [
						[
							'TYPE' => 'string',
							'CODE' => 'CONTACT_NAME',
							'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_FIELD_NAME'),
							'SORT' => 100,
							'REQUIRED' => 'N',
							'MULTIPLE' => 'N',
							'PLACEHOLDER' => '',
						],
						[
							'TYPE' => 'phone',
							'CODE' => 'CONTACT_PHONE',
							'CAPTION' => Loc::getMessage('LANDING_FORM_SPECIAL_STOREV3_FIELD_PHONE'),
							'SORT' => 200,
							'REQUIRED' => 'N',
							'MULTIPLE' => 'N',
							'PLACEHOLDER' => '',
						],
						[
							'TYPE' => 'text',
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

			$isLeadEnabled = LeadSettings::getCurrent()->isEnabled();

			foreach ($data as $id => $form)
			{
				if ($isLeadEnabled)
				{
					foreach ($data[$id]['FIELDS'] as $key => $field)
					{
						$field['CODE'] = str_replace(['CONTACT', 'DEAL'], 'LEAD', $field['CODE']);
						$data[$id]['FIELDS'][$key] = $field;
					}
				}
			}

			return $data;
		}

		return null;
	}

	// endregion

	// region update
	/**
	 * Find old forms blocks and update to embed format
	 * @param int $landingId
	 */
	public static function updateLandingToEmbedForms(int $landingId): void
	{
		$res = BlockTable::getList(
			[
				'select' => [
					'ID',
				],
				'filter' => [
					'LID' => $landingId,
					'=DELETED' => 'N',
				],
			]
		);
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
	// endregion
}
