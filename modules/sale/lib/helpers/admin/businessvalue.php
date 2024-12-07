<?php

namespace Bitrix\Sale\Helpers\Admin;

use	Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Registry;

Loc::loadMessages(__FILE__);

final class BusinessValueControl
{
	private $name;
	private $consumerCodePersonMapping = array();
	private $errors = array();

	public function __construct($name)
	{
		$this->name = $name.'BizVal';
	}

	public function setMapFromPost()
	{
		if ($this->consumerCodePersonMapping)
			throw new SystemException('Map is already set from post!');

		if (isset($_POST[$this->name]['MAP']) && is_array($_POST[$this->name]['MAP']))
		{
			$_POST = Input\File::getPostWithFiles($_POST, $_FILES);
			$consumerCodePersonMapping = $_POST[$this->name]['MAP'];
			unset($_POST[$this->name]['MAP']);

			$errors = array();

			$consumers = BusinessValue::getConsumers();

			foreach ($consumerCodePersonMapping as $consumerKey => &$codePersonMapping)
			{
				if ($consumerKey === BusinessValueTable::COMMON_CONSUMER_KEY)
				{
					$consumerKey = null;
					$consumerCodePersonMapping[null] = &$codePersonMapping;
					unset($consumerCodePersonMapping[BusinessValueTable::COMMON_CONSUMER_KEY]);
				}

				if (! (($consumer = $consumers[$consumerKey]) && is_array($consumer) && is_array($codePersonMapping)))
				{
					unset($consumerCodePersonMapping[$consumerKey]);
					continue;
				}

				if (! (($codes = $consumer['CODES']) && is_array($codes)))
					$codes = array();

				$skipNewCodeSanitation = $consumer['SKIP_NEW_CODE_SANITATION'] ?? false;

				$sanitizeMapping = $consumer['SANITIZE_MAPPING'] ?? null;
				if (!is_callable($sanitizeMapping))
				{
					$sanitizeMapping = null;
				}

				$renderColumns = $consumer['RENDER_COLUMNS'] ?? null;
				if (
					(! $codes && ! $skipNewCodeSanitation)
					|| ($skipNewCodeSanitation && ! $sanitizeMapping)
					|| (is_callable($renderColumns) && ! $sanitizeMapping)
				)
				{
					unset($consumerCodePersonMapping[$consumerKey]);
					continue;
				}

				foreach ($codePersonMapping as $codeKey => &$personMapping)
				{
					$code = $codes[$codeKey];

					if (! (is_array($personMapping) && (is_array($code) || $skipNewCodeSanitation)))
					{
						unset($codePersonMapping[$codeKey]);
						continue;
					}

					foreach ($personMapping as $personTypeId => &$mapping)
					{
						if ($personTypeId === BusinessValueTable::COMMON_PERSON_TYPE_ID) // must be === coz 0 & null
						{
							$personTypeId = null;
							$personMapping[null] = &$mapping;
							unset($personMapping[BusinessValueTable::COMMON_PERSON_TYPE_ID]);
						}

						$codeDomains = $code['DOMAINS'] ?? null;
						$personType = self::$personTypes[$personTypeId] ?? null;

						if (! is_array($code) && $skipNewCodeSanitation)
						{
							//$skipNewCodeSanitation
						}
						elseif (! (is_array($code)
							&& (! isset($code['PERSON_TYPE_ID']) || $code['PERSON_TYPE_ID'] == $personTypeId)
							&& (! is_array($codeDomains) || in_array($personType['DOMAIN'], $codeDomains, true))
							&& is_array($mapping)))
						{
							unset($personMapping[$personTypeId]);
							continue;
						}

						// delete record
						if (
							!empty($mapping['DELETE'])
							|| empty($mapping['PROVIDER_KEY'])
							|| empty($mapping['PROVIDER_VALUE'])
						)
						{
							continue;
						}

						if ($sanitizeMapping)
						{
							if ($e = call_user_func_array($sanitizeMapping, array($codeKey, $personTypeId, &$mapping)))
								$errors[$consumerKey][$codeKey][$personTypeId] = $e;
						}
						elseif (isset($code['INPUT']) && is_array($code['INPUT']))
						{
							$mapping['PROVIDER_KEY'] = 'INPUT';

							if ($e = Input\Manager::getError($code['INPUT'], $mapping['PROVIDER_VALUE'] ?? []))
								$errors[$consumerKey][$codeKey][$personTypeId]['PROVIDER_VALUE'] = $e;
						}
						else
						{
							if ($e = self::sanitizeMapping($personTypeId, $mapping, $code['PROVIDERS'] ?? $consumer['PROVIDERS'] ?? []))
								$errors[$consumerKey][$codeKey][$personTypeId] = $e;
						}

						if (! $mapping)
						{
							unset($personMapping[$personTypeId]); // remove from post
						}
					}
				}
			}

			$this->consumerCodePersonMapping = $consumerCodePersonMapping;
			$this->errors = $errors;
		}

		return ! $this->errors;
	}

	/** @internal @deprecated */
	public static function sanitizeMapping($personTypeId, array &$mapping, array $providerKeys = null)
	{
		$error = array();

		if (($providerInput = self::getProviderInput($personTypeId, $providerKeys))
			&& ($providerValueInput = self::$personProviderValueInput[$personTypeId])
			&& ($valueInput = $providerValueInput[$mapping['PROVIDER_KEY']]))
		{
			if ($e = Input\Manager::getError($providerInput, $mapping['PROVIDER_KEY']))
				$error['PROVIDER_KEY'] = $e;
			else
				$mapping['PROVIDER_KEY'] = Input\Manager::getValue($providerInput, $mapping['PROVIDER_KEY']);

			if ($e = Input\Manager::getError($valueInput, $mapping['PROVIDER_VALUE'] ?? []))
				$error['PROVIDER_VALUE'] = $e;
			else
				$mapping['PROVIDER_VALUE'] = Input\Manager::getValue($valueInput, $mapping['PROVIDER_VALUE'] ?? []);
		}
		else
		{
			$mapping = array(); // remove from post
		}

		return $error;
	}

	public function changeConsumerKey($fromConsumerKey, $toConsumerKey)
	{
		BusinessValue::changeConsumerKey($fromConsumerKey, $toConsumerKey);

		if (isset($this->consumerCodePersonMapping[$fromConsumerKey]))
		{
			$this->consumerCodePersonMapping[$toConsumerKey] = $this->consumerCodePersonMapping[$fromConsumerKey];
			unset($this->consumerCodePersonMapping[$fromConsumerKey]);
		}
	}

	public function saveMap()
	{
		if ($this->errors)
			throw new SystemException('There are errors in map!');

		$consumers = BusinessValue::getConsumers();

		foreach ($this->consumerCodePersonMapping as $consumerKey => $codePersonMapping)
		{
			$consumer = $consumers[$consumerKey] ?? [];
			$setMapping =
				isset($consumer['SET_MAPPING']) && is_callable($consumer['SET_MAPPING'])
					? $consumer['SET_MAPPING']
					: null
			;
			$codes = $consumer['CODES'] ?? [];

			foreach ($codePersonMapping as $codeKey => $personMapping)
			{
				$code = $codes[$codeKey] ?? [];
				$fileInput = $code['INPUT'] ?? [];
				$fileType = $fileInput['TYPE'] ?? '';
				if (!(is_array($fileInput) && ($fileType == 'FILE' || $fileType == 'DATABASE_FILE')))
				{
					$fileInput = [];
				}

				foreach ($personMapping as $personTypeId => $mapping)
				{
					if ($setMapping)
					{
						$result = call_user_func($setMapping, $codeKey, $personTypeId, $mapping);
					}
					else
					{
						$consumerCodePersonMapping = \Bitrix\Sale\BusinessValue::getConsumerCodePersonMapping();
						if ($fileInput && ($file =& $mapping['PROVIDER_VALUE']))
						{
							if (Input\File::isDeletedSingle($file))
							{
								if ($fileInput['TYPE'] == 'FILE')
								{
									if (is_numeric($file['ID']) && isset($consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId]))
									{
										\CFile::Delete($file['ID']); // TODO isSuccess
									}
								}

								$file = null;
							}
							elseif (Input\File::isUploadedSingle($file))
							{
								if ($fileInput['TYPE'] == 'FILE')
								{
									if (($file = \CFile::SaveFile(array('MODULE_ID' => 'sale') + $file, 'sale/bizval')) && is_numeric($file))
									{
										if (($oldFile = BusinessValue::getMapping($codeKey, $consumerKey, $personTypeId, array('MATCH' => BusinessValue::MATCH_EXACT))) && is_numeric($oldFile['PROVIDER_VALUE']))
											\CFile::Delete($oldFile['PROVIDER_VALUE']); // TODO isSuccess
									}
									else
									{
										$this->errors[$consumerKey][$codeKey][$personTypeId]['DATABASE'] = 'unable to save file';
										continue;
									}

									$file = Input\Manager::getValue($fileInput, $file);
								}
								elseif($fileInput['TYPE'] == 'DATABASE_FILE')
								{
									/** @noinspection PhpVariableNamingConventionInspection */
									global $APPLICATION;
									$content = $APPLICATION->GetFileContent($file['tmp_name']);
									if (!$content)
									{
										continue;
									}

									$file = $content;
								}
							}
							else
							{
								$file = $file['ID'];
							}
						}
						elseif (isset($fileInput['TYPE']) && is_array($fileInput) && $fileInput['TYPE'] === 'FILE')
						{
							if (isset($consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId]))
							{
								\CFile::Delete($consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId]['PROVIDER_VALUE']);
							}
						}

						$common = IsModuleInstalled('bitrix24') ? false : true;
						$result = BusinessValue::setMapping($codeKey, $consumerKey, $personTypeId, $mapping, $common);
					}

					if (! $result->isSuccess())
						$this->errors[$consumerKey][$codeKey][$personTypeId]['DATABASE'] = $result->getErrorMessages();
				}
			}
		}

		return ! $this->errors;
	}

	private static function getTabControl($name, $personGroupCodes)
	{
		$domains = array(
			''                               => Loc::getMessage('BIZVAL_DOMAIN_COMMON_DSC'),
			BusinessValue::INDIVIDUAL_DOMAIN => Loc::getMessage('BIZVAL_DOMAIN_INDIVIDUAL'),
			BusinessValue::ENTITY_DOMAIN     => Loc::getMessage('BIZVAL_DOMAIN_ENTITY'    ),
		);

		$tabs = array();

		foreach ($personGroupCodes as $personTypeId => $groupCodes)
		{
			$personType = self::$personTypes[$personTypeId];
			if (isset($personType['ENTITY_REGISTRY_TYPE'])
				&& $personType['ENTITY_REGISTRY_TYPE'] !== Registry::REGISTRY_TYPE_ORDER
			)
			{
				continue;
			}

			$tabs []= array(
				'DIV'          => 'map'.$personTypeId,
				'TAB'          => htmlspecialcharsbx($personType['TITLE']),
				'TITLE'        => $domains[$personType['DOMAIN']],
			);
		}

		return new \CAdminViewTabControl($name.'TabControl', $tabs);
	}

	private static function getPersonGroupCodes(array $consumers, array $filter)
	{
		$personGroupCodes = [];

		$consumerCodePersonMapping = BusinessValue::getConsumerCodePersonMapping();

		$consumer = $consumers[$filter['CONSUMER_KEY']];

		if (
			(empty($filter['PROVIDER_KEY']) && empty($filter['CODE_KEY']))
			|| !empty($filter['CONSUMER_KEY'])
		)
		{
			$consumers = [
				$filter['CONSUMER_KEY'] => $consumer,
			];
		}

		foreach (self::$personTypes as $personTypeId => $personType)
		{
			foreach ($consumers as $consumerKey => $consumer)
			{
				if (is_array($consumer) && isset($consumer['CODES']) && is_array($consumer['CODES']))
				{
					$consumerCodes = $consumer['CODES'];
					foreach ($consumerCodes as $codeKey => $code)
					{
						$needCodeKey = !isset($filter['CODE_KEY']) || (string)$filter['CODE_KEY'] === (string)$codeKey;
						$needPersonTypeId = !isset($code['PERSON_TYPE_ID']) || (int)$code['PERSON_TYPE_ID'] === (int)$personTypeId;
						$needDomains =
							!isset($code['DOMAINS'])
							|| !is_array($code['DOMAINS'])
							|| in_array($personType['DOMAIN'], $code['DOMAINS'], true)
						;

						if (
							is_array($code)
							&& $needCodeKey
							&& $needPersonTypeId
							&& $needDomains
						)
						{
							$code['CONSUMER_KEY'] = $consumerKey;

							if (!empty($filter['PROVIDER_KEY']))
							{
								if (isset($consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId]))
								{
									$mapping = $consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId];

									if ($mapping['PROVIDER_KEY'  ] == $filter['PROVIDER_KEY'  ] &&
										$mapping['PROVIDER_VALUE'] == $filter['PROVIDER_VALUE'])
									{
										$personGroupCodes[$personTypeId][$consumer['NAME'] ?? $consumerKey][$codeKey] = $code; // CONSUMER
									}
								}
							}
							elseif (!empty($filter['CODE_KEY']))
							{
								$personGroupCodes[$personTypeId][$consumer['NAME'] ?? $consumerKey][$codeKey] = $code; // CONSUMER
							}
							else
							{
								$codeGroup = $code['GROUP'] ?? '';
								$personGroupCodes[$personTypeId][$codeGroup][$codeKey] = $code; // GROUP
							}
						}
					}
				}
			}

			if (isset($personGroupCodes[$personTypeId]) && empty($filter['PROVIDER_KEY']) && empty($filter['CODE_KEY'])) // GROUP
			{
				self::sortRenameGroups($personGroupCodes[$personTypeId], self::$groups);
			}
		}

		return $personGroupCodes;
	}

	public function renderMap(array $options = array())
	{
		$hideFilledCodes =
			isset($options['HIDE_FILLED_CODES']) && $options['HIDE_FILLED_CODES'] === false
				? false
				: true
		;

		$consumers = BusinessValue::getConsumers();
		$personGroupCodes = self::getPersonGroupCodes($consumers, $options);
		$tabControl = self::getTabControl($this->name, $personGroupCodes);

		$consumerCodePersonMapping = BusinessValue::getConsumerCodePersonMapping();

		if ($this->errors)
		{
			foreach ($this->consumerCodePersonMapping as $consumerKey => $codePersonMapping)
				foreach ($codePersonMapping as $codeKey => $personMapping)
					foreach ($personMapping as $personTypeId => $mapping)
						if ($mapping['PROVIDER_KEY'])
							$consumerCodePersonMapping[$consumerKey][$codeKey][$personTypeId] = $mapping;
		}

		$tabControl->Begin();

		foreach ($personGroupCodes as $personTypeId => $groupCodes)
		{
			$personType = self::$personTypes[$personTypeId];
			if (isset($personType['ENTITY_REGISTRY_TYPE'])
				&& $personType['ENTITY_REGISTRY_TYPE'] !== Registry::REGISTRY_TYPE_ORDER
			)
			{
				continue;
			}

			$tabControl->BeginNextTab();

			?>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<colgroup>
					<col class="adm-detail-content-cell-l" width="40%">
					<col class="adm-detail-content-cell-r" width="60%">
				</colgroup>
				<?

				$personHasHiddenRows = 0;

				foreach ($groupCodes as $groupName => $codes)
				{
					$groupHasVisibleRows = false;

					?>
					<tbody>
						<?

						ob_start(); // $rowsHTML

						foreach ($codes as $codeKey => $code)
						{
							if ($codeKey === 'USER_COLUMNS')
								continue;

							$consumerKey = $code['CONSUMER_KEY'];
							$consumer = $consumers[$consumerKey];

							if (isset($this->errors[$consumerKey][$codeKey][$personTypeId]) &&
								($error = $this->errors[$consumerKey][$codeKey][$personTypeId]))
							{
								if (! is_array($error))
									$error = array($error);

								?>
								<tr>
									<td></td>
									<td style="color:#ff1118; padding: 1em 0 0 13em;">
										<?

										foreach ($error as $k => $e)
											echo htmlspecialcharsbx(is_array($e) ? implode(', ', $e) : $e).'<br>';

										?>
									</td>
								</tr>
								<?
							}

							$o = array(
								'consumerCodePersonMapping' => $consumerCodePersonMapping,
								'GET_VALUE' => array('PROPERTY' => 'BY_ID'),
							);

							$mappings = array(
								'EXACT'   => BusinessValue::getMapping($codeKey, $consumerKey, $personTypeId, array('MATCH' => BusinessValue::MATCH_EXACT  ) + $o),
								'COMMON'  => BusinessValue::getMapping($codeKey, $consumerKey, $personTypeId, array('MATCH' => BusinessValue::MATCH_COMMON ) + $o),
								'DEFAULT' => BusinessValue::getMapping($codeKey, $consumerKey, $personTypeId, array('MATCH' => BusinessValue::MATCH_DEFAULT) + $o),
							);

							$inputNamePrefix = $this->name.'[MAP]['
								.($consumerKey ?: BusinessValueTable::COMMON_CONSUMER_KEY).']['
								.$codeKey.']['
								.($personTypeId ?: BusinessValueTable::COMMON_PERSON_TYPE_ID).']';

							ob_start(); // $columnsHTML

							if (isset($consumer['RENDER_COLUMNS']) && is_callable($consumer['RENDER_COLUMNS']))
							{
								$hideCode = call_user_func($consumer['RENDER_COLUMNS'], $codeKey, $personTypeId, $mappings, $inputNamePrefix);
							}
							else
							{
								?>
								<td>
									<?

									if (isset($code['CONSUMERS']) && is_array($code['CONSUMERS']) && count($code['CONSUMERS']) > 1)
									{
										echo implode(', ', array_map(function ($i) {return htmlspecialcharsbx($i);}, array_flip($code['NAMES'])));
										?>
										<img
											src="/bitrix/js/main/core/images/hint.gif"
											style="cursor: help;"
											title="<?= htmlspecialcharsbx(implode(', ', $code['CONSUMERS'])) ?>"
										>
										<?
									}
									else
									{
										echo htmlspecialcharsbx($code['NAME'] ?? $codeKey);
									}

									if (isset($code['DESCRIPTION']) && is_string($code['DESCRIPTION']))
									{
										?>
										<div style="font-size:10px;"><?=htmlspecialcharsbx($code['DESCRIPTION'])?></div>
										<?
									}

									?>
								</td>
								<td>
									<?

									$commonProviderInput = $commonProviderValueInput = null;

									if (isset($code['INPUT']) && is_array($code['INPUT']))
									{
										$providerInput = array('TYPE' => 'ENUM', 'HIDDEN' => true, 'OPTIONS' => array('INPUT' => ''));
										$providerValueInput = array(
											'INPUT' => array(
													'REQUIRED' => true,
													'ONCHANGE' => "bizvalChangeValue(this)",
													'NAME' => $code['NAME'],
												)
												+ $code['INPUT']
										);
									}
									else
									{
										$providersValues = $code['PROVIDERS'] ?? $consumer['PROVIDERS'] ?? [];

										$providerInput = self::getProviderInput($personTypeId, $providersValues);
										$providerValueInput = self::getValueInput($personTypeId);

										if ($personTypeId)
										{
											$commonProviderInput = self::getProviderInput('', $providersValues);
											$commonProviderValueInput = self::getValueInput('');
										}
									}

									try
									{
										$hideCode = self::renderMapping($mappings, $inputNamePrefix, $providerInput, $providerValueInput, $commonProviderInput, $commonProviderValueInput);
									}
									catch (SystemException $exception)
									{
										$hideCode = '';
									}

									?>
								</td>
								<?
							}

							$columnsHTML = ob_get_clean();

							?>
							<tr<?

							if ($hideFilledCodes && $hideCode)
							{
								?> class="<?=$this->name.$personTypeId?>row-with-value" style="display:none;"<?
								$personHasHiddenRows = true;
							}
							else
							{
								$groupHasVisibleRows = true;
							}

							?>>
								<?=$columnsHTML?>
							</tr>
							<?
						}

						$rowsHTML = ob_get_clean();

						if ($groupName)
						{
							?>
							<tr<?

							if ($hideFilledCodes && ! $groupHasVisibleRows)
								echo ' class="'.$this->name.$personTypeId.'row-with-value" style="display:none;"';

							?>>
								<td colspan="2" style="
									padding: 15px 15px 3px;
									text-align: center; color: #4B6267;
									font-weight: bold; border-bottom: 5px solid #E0E8EA;">
									<?=htmlspecialcharsbx($groupName)?>
								</td>
							</tr>
							<?
						}

						echo $rowsHTML;

						?>
					</tbody>
					<?
				}

				?>
			</table>
			<?

			if ($hideFilledCodes && $personHasHiddenRows)
			{
				?>
				<p>
					<a href="#" onclick="bizvalToggleRowsVisibility(this, '<?=$this->name.$personTypeId.'row-with-value'?>'); return false;">
						<?=Loc::getMessage('BIZVAL_PAGE_SHOW_ROWS')?>
					</a>
				</p>
				<?
			}
		}

		$tabControl->End();

		self::renderScript();
	}

	/** @internal */
	public static function renderMapping(array $mappings, $inputNamePrefix, array $providerInput, array $providerValueInput, array $commonProviderInput = null, array $commonProviderValueInput = null)
	{
		foreach ($mappings as &$m)
			$m = self::correctMapping($providerInput, $providerValueInput, $m);
		unset($m);

		$mappings['EXACT'] ??= [];
		$mappings['COMMON'] ??= [];
		$mappings['DEFAULT'] ??= [];

		if ($m = ($mappings['EXACT'] ?: $mappings['COMMON'] ?: $mappings['DEFAULT']))
		{
			$providerKey   = $m['PROVIDER_KEY'  ];
			$providerValue = $m['PROVIDER_VALUE'];
			$valueInput    = $providerValueInput[$providerKey];
		}
		else
		{
			$providerKey   = is_array($providerInput['OPTIONS']) ? key($providerInput['OPTIONS']) : null;
			$providerValue = null;
			$valueInput    = $providerValueInput[$providerKey] ?? array('TYPE' => 'STRING', 'HIDDEN' => true);
		}

		if ($providerKey == 'INPUT')
		{
			switch ($valueInput['TYPE'])
			{
				case 'ENUM':

					$valueInput['OPTIONS'] =  is_array($valueInput['OPTIONS'])
						? array('' => '') + $valueInput['OPTIONS']
						: array('' => '');

					break;

				case 'FILE':

					if ($providerValue)
						$providerValue = Input\File::loadInfoSingle($providerValue);

					$valueInput['CLASS'] = 'adm-designed-file';

					break;

				case 'DATABASE_FILE':
					$valueInput['CLASS'] = 'adm-designed-file';

					break;
			}
		}
		else
		{
			if ($commonProviderInput
				&& $commonProviderValueInput
				&& ! $mappings['EXACT']
				&& ! $mappings['COMMON']
				&& $mappings['DEFAULT']
				&& $mappings['DEFAULT'] == self::correctMapping($commonProviderInput, $commonProviderValueInput, $mappings['DEFAULT']))
			{
				$mappings['COMMON'] = $mappings['DEFAULT'];
			}
		}

//		if (! $mappings['COMMON'] && is_array($code['DEFAULT']))
//		{
//			if ($personTypeId) // TODO
//				$defaultMapping = $code['DEFAULT'];
//			elseif (! $mapping)
//				$mapping = $code['DEFAULT'];
//		}

		if (! $mappings['EXACT'] && $mappings['COMMON'])
			$providerInput['DISABLED'] = $valueInput['DISABLED'] = true;

		// !!! Do not change DOM !!!
		?><span><?=

			Input\Manager::getEditHtml($inputNamePrefix.'[PROVIDER_KEY]', $providerInput, $providerKey)

		?> </span><span><?=

			Input\Manager::getEditHtml($inputNamePrefix.'[PROVIDER_VALUE]', $valueInput, $providerValue)

		?> </span><label <?=$mappings['COMMON'] ? '' : ' style="display:none"'?>>
			<?=Loc::getMessage('BIZVAL_PAGE_DELETE_MAPPING')?>
			<input
				type="checkbox"
				name="<?=$inputNamePrefix?>[DELETE]"'
				<?if (! $mappings['EXACT'] && $mappings['COMMON']):?>
					checked
				<?endif?>
				<?if ($m = $mappings['EXACT']):?>
					data-initial-key="<?=htmlspecialcharsbx($m['PROVIDER_KEY'])?>"
					data-initial-value="<?=htmlspecialcharsbx($m['PROVIDER_VALUE'])?>"
				<?endif?>
				<?if ($m = $mappings['COMMON']):?>
					data-default-key="<?=htmlspecialcharsbx($m['PROVIDER_KEY'])?>"
					data-default-value="<?=htmlspecialcharsbx($m['PROVIDER_VALUE'])?>"
				<?endif?>
				onclick="bizvalToggleDelete(this)">
		</label><?

		return $providerValue; // $hideCode TODO
	}

	private static function correctMapping(array $providerInput, array $providerValueInput, array $mapping)
	{
		$key = $mapping['PROVIDER_KEY'] ?? null;
		$value = $mapping['PROVIDER_VALUE'] ?? null;

		$isValid =
			!Input\Manager::getError($providerInput, $key)
			&& isset($providerValueInput[$key])
			&& !Input\Manager::getError($providerValueInput[$key], $value)
		;

		if (!$isValid)
		{
			$mapping = array();
		}

		return $mapping;
	}

	private static function renderScript()
	{
		static $done;
		if ($done)
			return;
		$done = true;

		?>
		<script>

			function bizvalChangeProvider(keyElement, personTypeId, filterMode)
			{
				'use strict';

				var	providerKey = keyElement.options[keyElement.selectedIndex].value,
					wrapElement = keyElement.parentNode.nextSibling,
					name = wrapElement.firstChild.name,
					personProviderValueInput = <?

						$personProviderValueInput = self::$personProviderValueInput;

						echo \CUtil::PhpToJSObject(
							call_user_func(
								function () use ($personProviderValueInput)
								{
									foreach ($personProviderValueInput as &$providerFieldInput)
										foreach ($providerFieldInput as &$fieldInput)
											$fieldInput = Input\Manager::getEditHtml('', $fieldInput);

									return $personProviderValueInput;
								}
							)
						);

					?>;

				wrapElement.innerHTML = personProviderValueInput[personTypeId][providerKey];
				wrapElement.firstChild.name = name;

				if (! filterMode)
				{
					if (! personTypeId)
						bizvalChangeValue(wrapElement.firstChild);
				}

				return wrapElement.firstChild;
			}

			function bizvalToggleDelete(deleteElement)
			{
				'use strict';

				var path = deleteElement.name.split('[');

				if (! (path.length == 6
					&& path[1] == 'MAP]'
					&& path[5] == 'DELETE]'
				)) return;

				// [  0  ][ 1 ][ 2 ][ 3 ][4][    5         ]
				// mBizVal[MAP][1CC][ZIP][2][PROVIDER_VALUE]

				var elements = deleteElement.parentNode.parentNode.parentNode.querySelectorAll('input, select, textarea'), // TODO
					i = 0, length = elements.length,
					personTypeId = path[4].slice(0, -1);

				if (personTypeId == '<?=BusinessValueTable::COMMON_PERSON_TYPE_ID?>')
					personTypeId = '';

				for (; i < length; ++i)
				{
					var keyElement = elements[i],
						p = keyElement.name.split('[');

					if (p.length == 6
						&& p[0] == path[0] && p[1] == path[1] && p[2] == path[2] && p[3] == path[3] && p[4] == path[4]
						&& p[5] == 'PROVIDER_KEY]')
					{
						// note that, data-default.., must be set since checkbox only visible with default mapping!

						var valueElement, key, val;

						if (deleteElement.checked)
						{
							key = deleteElement.getAttribute('data-default-key');
							val = deleteElement.getAttribute('data-default-value');
						}
						else
						{
							key = deleteElement.hasAttribute('data-initial-key')
								? deleteElement.getAttribute('data-initial-key')
								: deleteElement.getAttribute('data-default-key');
							val = deleteElement.hasAttribute('data-initial-value')
								? deleteElement.getAttribute('data-initial-value')
								: deleteElement.getAttribute('data-default-value');
						}

						if (keyElement.value == 'INPUT')
						{
							valueElement = keyElement.parentNode.nextSibling.firstChild;

							if (valueElement.type == 'hidden') // checkbox
							{
								valueElement = valueElement.nextSibling;
								valueElement.checked = val;
							}
							else
							{
								valueElement.value = val;
							}
						}
						else
						{
							keyElement.value = key;
							valueElement = bizvalChangeProvider(keyElement, personTypeId, true);
							valueElement.value = val;
						}

						keyElement.disabled = deleteElement.checked;

						var parentElement = valueElement.parentNode;
						var tagList = ['input', 'select', 'textarea'];
						for (var tagIndex in tagList)
						{
							if (tagList.hasOwnProperty(tagIndex))
							{
								var inputs = BX.findChildren(parentElement, {tag : tagList[tagIndex]}, true);
								for (var k in inputs)
								{
									if (inputs.hasOwnProperty(k))
									{
										if (inputs[k].type != 'button')
											inputs[k].disabled = deleteElement.checked;
									}
								}
							}
						}

						if (! personTypeId)
							bizvalChangeValue(valueElement);

						break;
					}
				}
			}

			function bizvalChangeValue(valueElement)
			{
				'use strict';

				var path = valueElement.name.split('[');

				if (! (path.length == 6
					&& path[1] == 'MAP]'
					&& path[5] == 'PROVIDER_VALUE]'
					&& path[4] == '<?=BusinessValueTable::COMMON_PERSON_TYPE_ID?>]'
				)) return;

				// [  0  ][ 1 ][ 2 ][ 3 ][4][    5         ]
				// mBizVal[MAP][1CC][ZIP][2][PROVIDER_VALUE]

				var keyElement = valueElement.parentNode.previousSibling.firstChild, value,
					elements = document.querySelectorAll('input, select, textarea'), // TODO
					i = 0, length = elements.length;

				switch (valueElement.type)
				{
					case 'checkbox': value = valueElement.checked ? 'true' : ''; break;
					default:         value = valueElement.value;
				}

				for (; i < length; ++i)
				{
					var de = elements[i],
						p = de.name.split('[');

					if (p.length == 6
						&& p[0] == path[0] && p[1] == path[1] && p[2] == path[2] && p[3] == path[3]
						&& p[4] != path[4] // self exclude
						&& p[5] == 'DELETE]')
					{
						de.setAttribute('data-default-key', keyElement.value);
						de.setAttribute('data-default-value', value);

						var we = de.parentNode,
							v = valueElement.type == 'checkbox' ? we.previousSibling.firstChild.nextSibling.checked : we.previousSibling.firstChild.value;

						if (value)
						{
							if (de.checked || ! v)
							{
								de.checked = true;
								bizvalToggleDelete(de);
							}

							we.style.display = 'inline';
						}
						else
						{
							if (de.checked)
							{
								de.checked = false;
								bizvalToggleDelete(de);
							}

							we.style.display = 'none';
						}
					}
				}
			}

			function bizvalToggleRowsVisibility(anchor, className)
			{
				'use strict';

				var display;

				if (anchor.rowsAreVisible)
				{
					anchor.rowsAreVisible = false;
					anchor.innerText = '<?=Loc::getMessage('BIZVAL_PAGE_SHOW_ROWS')?>';
					display = 'none';
				}
				else
				{
					anchor.rowsAreVisible = true;
					anchor.innerText = '<?=Loc::getMessage('BIZVAL_PAGE_HIDE_ROWS')?>';
					display = 'table-row';
				}

				var nodes = document.querySelectorAll('.'+className), i, l = nodes.length;

				for (i = 0; i < l; i++)
					nodes[i].style.display = display;
			}

			<?

			foreach (BusinessValue::getConsumers() as $consumerKey => $consumer)
			{
				if (isset($consumer['GET_JAVASCRIPT']) && is_callable($consumer['GET_JAVASCRIPT']))
				{
					echo call_user_func($consumer['GET_JAVASCRIPT']);
				}
			}

			?>
		</script>
		<?
	}

	/** @internal */
	public static function getFilter($filter)
	{
		$filter =
			is_array($filter)
				? array_intersect_key($filter, array('CODE_KEY'=>1,'CONSUMER_KEY'=>1,'PROVIDER_KEY'=>1,'PROVIDER_VALUE'=>1))
				: array()
		;

		if (self::$consumerInput['OPTIONS'])
		{
			$value = $filter['CONSUMER_KEY'] ?? null;
			$filter['CONSUMER_KEY'] =
				! Input\Manager::getError(self::$consumerInput, $value)
					? Input\Manager::getValue(self::$consumerInput, $value)
					: key(self::$consumerInput['OPTIONS']) // REQUIRED
			;
		}

		if (is_array(self::$consumerCodeInput[$filter['CONSUMER_KEY']]))
		{
			$value = $filter['CODE_KEY'] ?? null;
			$filter['CODE_KEY'] =
				! Input\Manager::getError(self::$consumerCodeInput[$filter['CONSUMER_KEY']], $value)
					? Input\Manager::getValue(self::$consumerCodeInput[$filter['CONSUMER_KEY']], $value)
					: null
			;
		}


		// TODO null - personTypeId
		$value = $filter['PROVIDER_KEY'] ?? null;
		$filter['PROVIDER_KEY'] =
			! Input\Manager::getError(self::$personProviderInput[null], $value)
				? Input\Manager::getValue(self::$personProviderInput[null], $value)
				: null;

		// TODO null - personTypeId
		$value = $filter['PROVIDER_VALUE'] ?? null;
		$filter['PROVIDER_VALUE'] = $filter['PROVIDER_KEY']
			&& ! Input\Manager::getError(self::$personProviderValueInput[null][$filter['PROVIDER_KEY']], $value)
				? Input\Manager::getValue(self::$personProviderValueInput[null][$filter['PROVIDER_KEY']], $value)
				: null;

		return $filter;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private static $personTypes;
	private static $personProviderInput, $personProviderValueInput;
	private static $groups, $consumerInput, $consumerCodeInput;

	/** @internal */
	public static function initialize()
	{
		self::$personTypes = array('' => array('DOMAIN' => '', 'TITLE' => Loc::getMessage('BIZVAL_DOMAIN_COMMON'))) + BusinessValue::getPersonTypes();

		list (self::$personProviderInput, self::$personProviderValueInput) = self::getProviderInputs(BusinessValue::getProviders(), self::$personTypes);

		self::$groups = array('' => array()) + BusinessValue::getGroups();

		list (self::$consumerInput, self::$consumerCodeInput) = self::getConsumerInputs(BusinessValue::getConsumers(), self::$groups);
	}

	/** @internal */
	public static function getProviderInput($personTypeId, array $providerKeys = null)
	{
		$providerInput = self::$personProviderInput[$personTypeId];

		if ($providerKeys && is_array($providerInput['OPTIONS']))
			$providerInput['OPTIONS'] = array_intersect_key($providerInput['OPTIONS'], array_flip($providerKeys));

		return $providerInput;
	}

	/** @internal */
	public static function getValueInput($personTypeId, $providerKey = null)
	{
		return $providerKey
			? self::$personProviderValueInput[$personTypeId][$providerKey]
			: self::$personProviderValueInput[$personTypeId];
	}

	/** @internal */
	public static function getConsumerInput()
	{
		return self::$consumerInput;
	}

	/** @internal */
	public static function getConsumerCodeInput()
	{
		return self::$consumerCodeInput;
	}

	private static function getProviderInputs(array $providers, array $personTypes)
	{
		$personProviderInput = $personProviderValueInput = array();

		$onChange = "bizvalChangeValue(this)";

		foreach ($personTypes as $personTypeId => $personType)
		{
			$providerOptions = array();

			foreach ($providers as $providerKey => $provider)
			{
				if (isset($provider['INPUT']) && is_array($provider['INPUT']))
				{
					$provider['INPUT']['REQUIRED'] = true;
					$provider['INPUT']['ONCHANGE'] = $onChange;

					$providerOptions[$providerKey] = $provider['NAME'] ?? $providerKey;
					$personProviderValueInput[$personTypeId][$providerKey] = $provider['INPUT'];
				}
				elseif (isset($provider['FIELDS']) && is_array($provider['FIELDS']))
				{
					$fields = $provider['FIELDS'];
					$fieldOptions = array();

					// group fields
					foreach ($fields as $fieldKey => $field)
					{
						if (
							is_array($field)
							&& (empty($field['PERSON_TYPE_ID']) || $field['PERSON_TYPE_ID'] == $personTypeId)
						)
						{
							$group = $field['GROUP'] ?? null; // null is GENERAL
							$fieldOptions[$group][$fieldKey] = $field['NAME'] ?? $fieldKey;
						}
					}

					if (count($fieldOptions) == 1)
					{
						$fieldOptions = reset($fieldOptions);
					}
					elseif (is_array($provider['FIELDS_GROUPS']))
					{
						self::sortRenameGroups($fieldOptions, $provider['FIELDS_GROUPS']);
					}

					if (! empty($fieldOptions))
					{
						$providerOptions[$providerKey] = $provider['NAME'] ?? $providerKey;
						$personProviderValueInput[$personTypeId][$providerKey] = array('TYPE' => 'ENUM', 'OPTIONS' => $fieldOptions, 'ONCHANGE' => $onChange);
					}
				}
				else
				{
					$providerOptions[$providerKey] = $provider['NAME'] ?? $providerKey;
					$personProviderValueInput[$personTypeId][$providerKey] = array('TYPE' => 'STRING', 'SIZE' => 30, 'ONCHANGE' => $onChange);
				}
			}

			$personProviderValueInput[$personTypeId][''] = array('TYPE' => 'STRING', 'SIZE' => 30); // for filter only

			if ($providerOptions)
			{
				$personProviderInput[$personTypeId] = array(
					'TYPE'     => 'ENUM',
					'OPTIONS'  => $providerOptions,
					'REQUIRED' => true,
					'ONCHANGE' => "bizvalChangeProvider(this, '".$personTypeId."')",
				);
			}
		}

		return array($personProviderInput, $personProviderValueInput);
	}

	private static function getConsumerInputs(array $consumers, array $groups)
	{
		$consumerInput = array(
			'TYPE'     => 'ENUM',
			'REQUIRED' => true,
		);

		$consumerCodeInput = array();

		$consumerOptions = array();

		foreach ($consumers as $consumerKey => $consumer)
		{
			if (is_array($consumer) && isset($consumer['CODES']) && is_array($consumer['CODES']))
			{
				$consumerCodes = $consumer['CODES'];
				$consumerGroup = $consumer['GROUP'] ?? null; // null is GENERAL

				$consumerOptions[$consumerGroup][$consumerKey] = $consumer['NAME'] ?? $consumerKey;

				$codeOptions = array();

				foreach ($consumerCodes as $codeKey => $code)
				{
					if (is_array($code) && isset($code['GROUP']))
					{
						$codeOptions[$code['GROUP']][$codeKey] = $code['NAME'] ?? $codeKey;
					}
				}

				if (count($codeOptions) == 1)
				{
					$codeOptions = reset($codeOptions);
				}
				else
				{
					self::sortRenameGroups($codeOptions, $groups, true);
				}

				$consumerCodeInput[$consumerKey] = array(
					'TYPE' => 'ENUM',
					'OPTIONS' => array('' => Loc::getMessage('BIZVAL_PAGE_ALL')) + $codeOptions,
				);
			}
		}

		if ($consumerOptions)
		{
			self::sortRenameGroups($consumerOptions, $groups, true);
			$consumerInput['OPTIONS'] = $consumerOptions;
		}

		return array($consumerInput, $consumerCodeInput);
	}

	private static function sortRenameGroups(array &$groupedItems, array $groups, $flattenEmptyGroup = false)
	{
		$sortedItems = array();

		if ($flattenEmptyGroup && isset($groupedItems['']))
		{
			$sortedItems = $groupedItems[''];
			unset($groupedItems['']);
		}

		foreach ($groups as $groupKey => $group)
		{
			if (is_array($group) && isset($groupedItems[$groupKey]))
			{
				$sortedItems[$group['NAME'] ?? $groupKey] = $groupedItems[$groupKey];
				unset($groupedItems[$groupKey]);
			}
		}

		$groupedItems = $sortedItems + $groupedItems;
	}
}

BusinessValueControl::initialize();
