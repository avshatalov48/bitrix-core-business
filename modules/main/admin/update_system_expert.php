<?php
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
/** @var array $arUpdateList */
/** @var array $arClientModules */

class UpdateSystemExpertHelper
{
	protected $updateList;
	protected $clientModules;

	public function __construct(array $updateList, array $clientModules)
	{
		$this->clientModules = $clientModules;
		$this->updateList = $updateList;
		$this->enrichUpdatesListByLastVersionsOfInstalledModules();
	}

	public function getUpdatesCount(): int
	{
		if (
			isset($this->updateList["MODULES"][0]["#"]["MODULE"])
			&& is_array($this->updateList["MODULES"][0]["#"]["MODULE"])
		)
		{
			return count($this->updateList["MODULES"][0]["#"]["MODULE"]);
		}

		return 0;
	}

	public function getUpdatesList(): array
	{
		if ($this->getUpdatesCount() > 0)
		{
			return $this->updateList["MODULES"][0]["#"]["MODULE"];
		}

		return [];
	}

	public function getClientModules(): array
	{
		return $this->clientModules;
	}

	public function escapeModuleId(string $moduleId): string
	{
		$moduleId = preg_replace("#[^A-Za-z0-9._-]#", "", $moduleId);

		return CUtil::JSEscape(htmlspecialcharsbx($moduleId));
	}

	public function getUpdateIdentifier(string $moduleId, string $version): string
	{
		return $moduleId . '|' . $version;
	}

	public function getJavascriptObjectWithDependencies(): string
	{
		$result = '';

		if (
			!isset($this->updateList['MODULES'][0]['#']['MODULE'])
			|| !is_array($this->updateList['MODULES'][0]['#']['MODULE'])
		)
		{
			return '';
		}
		foreach ($this->updateList['MODULES'][0]['#']['MODULE'] as $moduleDescription)
		{
			if (
				!isset($moduleDescription['#']['VERSION'])
				|| !is_array($moduleDescription['#']['VERSION'])
			)
			{
				continue;
			}
			$moduleName = $this->escapeModuleId($moduleDescription['@']['ID']);
			if (empty($moduleName))
			{
				continue;
			}
			$versionsList = $this->flattenModuleUpdates($moduleName, $moduleDescription['#']['VERSION']);
			if (empty($versionsList))
			{
				continue;
			}
			if (!empty($result))
			{
				$result .= ', ';
			}
			$result .= $versionsList;
		}

		return $result;
	}

	public function getJavascriptObjectWithUpdates()
	{
		$count = [];
		foreach ($this->getUpdatesList() as $moduleUpdates)
		{
			$moduleName = $this->escapeModuleId($moduleUpdates['@']['ID']);
			if (isset($moduleUpdates["#"]["VERSION"]) && is_array($moduleUpdates["#"]["VERSION"]))
			{
				$count[$moduleName] = count($moduleUpdates["#"]["VERSION"]);
			}
			else
			{
				$count[$moduleName] = 1;
			}
		}

		$result = '';
		foreach ($count as $moduleName => $cnt)
		{
			if (!empty($result))
			{
				$result .= ',';
			}
			$result .= '"' . $moduleName . '": ' . $cnt;
		}

		return $result;
	}

	protected function flattenModuleUpdates(string $moduleName, array $versions): string
	{
		$result = '';
		$previousVersion = null;

		foreach ($versions as $versionDescription)
		{
			if (!isset($versionDescription['@']['ID']))
			{
				continue;
			}
			$version = $versionDescription['@']['ID'];
			$identifier = $this->getUpdateIdentifier($moduleName, $version);
			$dependencies = [];
			if ($previousVersion)
			{
				$dependencies[] = $previousVersion;
			}
			$previousVersion = $identifier;
			if (!empty($result))
			{
				$result .= ',';
			}
			$result .= "\"" . $identifier . "\":[";
			if (
				isset($versionDescription['#']['VERSION_CONTROL'])
				&& is_array($versionDescription['#']['VERSION_CONTROL'])
			)
			{
				foreach ($versionDescription['#']['VERSION_CONTROL'] as $dependency)
				{
					if (isset($dependency['@']['MODULE']) && isset($dependency['@']['VERSION']))
					{
						$identifier = $this->getUpdateIdentifier(
							$dependency['@']['MODULE'],
							$dependency['@']['VERSION']
						);
						$dependencies[] = $identifier;
					}
				}
				$dependencies = array_unique($dependencies);
			}
			if (!empty($dependencies))
			{
				$result .= "\"" . implode('","', $dependencies) . "\"";
			}
			$result .= "]";
		}

		return $result;
	}

	private function enrichUpdatesListByLastVersionsOfInstalledModules(): void
	{
		$clientModules = $this->getClientModules();
		$presentModulesWithUpdates = [];
		if (isset($this->updateList['MODULES'][0]['#']['MODULE']))
		{
			foreach ($this->updateList['MODULES'][0]['#']['MODULE'] as $moduleUpdates)
			{
				$moduleId = $this->escapeModuleId($moduleUpdates["@"]["ID"]);
				$presentModulesWithUpdates[$moduleId] = $moduleId;
			}
		}
		else
		{
			$this->updateList['MODULES'][0]['#'] = [];
		}

		foreach ($clientModules as $moduleId => $version)
		{
			if (!isset($presentModulesWithUpdates[$moduleId]))
			{
				[$name, $description] = $this->getModuleInfo($moduleId);
				$this->updateList['MODULES'][0]['#']['MODULE'][] = [
					'@' => [
						'ID' => $moduleId,
						'NAME' => $name,
						'DESCRIPTION' => $description,
					],
					'#' => [],
				];
			}
		}
	}

	private function getModuleInfo($moduleId)
	{
		$module = CModule::CreateModuleObject($moduleId);
		$name = $moduleId;
		$description = '';
		if (is_object($module))
		{
			$name = $module->MODULE_NAME ?? $moduleId;
			$description = $module->MODULE_DESCRIPTION ?? '';
		}

		return [$name, $description];
	}
}

$expertUpdateHelper = new UpdateSystemExpertHelper($arUpdateList, $arClientModules);
$updatesCount = $expertUpdateHelper->getUpdatesCount();

?>
<!--suppress HtmlDeprecatedAttribute -->
<!--suppress HtmlFormInputWithoutLabel -->
<!--suppress JSPrimitiveTypeWrapperUsage -->
<!--suppress ES6ConvertVarToLetConst -->
<style>
	.conflicts_message {
		margin-top: 16px;
    }
	.conflicts_message.conflicts_message_hidden {
		display: none;
	}
	.conflicts_message .conflicts_message_title {
		font-weight: bold;
		margin-bottom: 8px;
	}
	#expert_install_conflicts_message {
		line-height: 150%;
    }
	#expert_install_conflicts_message a {
		text-decoration: none;
    }
</style>
<tr>
	<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td colspan="2"><?= GetMessage("SUP_SULL_CNT") ?>: <?= $updatesCount ?><BR><BR></td>
			</tr>
			<tr>
				<td>
					<input TYPE="button" ID="expert_install_updates_sel_button" NAME="expert_install_updates"<?= (($updatesCount <= 0) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SULL_BUTTON") ?>" onclick="UpdateSystemExpertHelper.getInstance().handleInstallUpdatesButtonClicked()">
				</td>
				<td align="right">
					<input TYPE="button" value="<?= GetMessage('SUP_EXPORT_BUTTON') ?>" title="<?= GetMessage('SUP_EXPORT_TITLE') ?>" onclick="UpdateSystemExpertHelper.getInstance().handleExportButtonClicked()">
					<input TYPE="button" value="<?= GetMessage('SUP_IMPORT_BUTTON') ?>" title="<?= GetMessage('SUP_IMPORT_TITLE') ?>" onclick="UpdateSystemExpertHelper.getInstance().handleImportButtonClicked()">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="expert_install_conflicts" class="conflicts_message conflicts_message_hidden">
						<div class="conflicts_message_title"><?=GetMessage('SUP_CONFLICT_POPUP_TITLE');?></div>
						<div id="expert_install_conflicts_message"></div>
					</div>
				</td>
			</tr>
		</table>
		<br>
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal" id="expert_table_updates_sel_list">
			<tr>
				<td class="heading"><INPUT TYPE="checkbox" NAME="select_all" id="expert_id_select_all" title="<?= GetMessage("SUP_SULL_CBT") ?>" onClick="UpdateSystemExpertHelper.getInstance().handleSelectAllRowsClicked()" checked></td>
				<td class="heading"><B><?= GetMessage("SUP_SULL_NAME") ?></B></td>
				<td class="heading"><B><?= GetMessage("SUP_SULL_TYPE") ?></B></td>
				<td class="heading"><B><?= GetMessage("SUP_SULL_REL_FROM") ?></B></td>
				<td class="heading"><B><?= GetMessage("SUP_SULL_REL_TO") ?></B></td>
				<td class="heading"><B><?= GetMessage("SUP_SULL_NOTE") ?></B></td>
			</tr>
			<?php
			$clientModules = $expertUpdateHelper->getClientModules();
			if ($updatesCount > 0)
			{
				foreach ($expertUpdateHelper->getUpdatesList() as $moduleUpdates)
				{
					$moduleId = $expertUpdateHelper->escapeModuleId($moduleUpdates["@"]["ID"]);
					$escapedModuleId = CUtil::JSEscape(htmlspecialcharsbx($moduleId));
					$versionFrom = null;
					if (array_key_exists($moduleId, $clientModules))
					{
						$versionFrom = $clientModules[$moduleId];
					}
					$availableVersions = [];
					if ($versionFrom)
					{
						$availableVersions[] = $versionFrom;
					}

					$strTitleTmp = $moduleUpdates["@"]["NAME"]." (".$moduleId.")\n".$moduleUpdates["@"]["DESCRIPTION"]."\n";
					if (isset($moduleUpdates["#"]["VERSION"]) && is_array($moduleUpdates["#"]["VERSION"]))
					{
						for ($j = 0, $cntj = count($moduleUpdates["#"]["VERSION"]); $j < $cntj; $j++)
						{
							$availableVersions[] = $moduleUpdates["#"]["VERSION"][$j]["@"]["ID"];
						}
					}
					$selectedVersionTo = null;
					if (!empty($availableVersions))
					{
						$versionsIndex = count($availableVersions);
						while ($versionsIndex > 0)
						{
							$versionsIndex--;
							if (is_numeric(mb_substr($availableVersions[$versionsIndex], 0, 1)))
							{
								$selectedVersionTo = $availableVersions[$versionsIndex];
								break;
							}
						}
					}
					$strTitleTmp = htmlspecialcharsbx(preg_replace("/<.+?>/i", "", $strTitleTmp));
					?>
					<tr title="<?= $strTitleTmp ?>">
						<td><a name="expert_module_<?= $escapedModuleId ?>"></a><INPUT TYPE="checkbox" NAME="select_module_<?= $escapedModuleId ?>" value="Y" onClick="UpdateSystemExpertHelper.getInstance().handleModuleCheckboxClicked()" checked id="id_expert_select_module_<?= $escapedModuleId ?>"></td>
						<td><label for="id_expert_select_module_<?= $escapedModuleId ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($moduleUpdates["@"]["NAME"]), GetMessage("SUP_SULL_MODULE")) . " (" . htmlspecialcharsbx($moduleUpdates["@"]["ID"]) . ")" ?></label></td>
						<td><?= ($versionFrom ? GetMessage("SUP_SULL_REF_O") : GetMessage("SUP_SULL_REF_N")) ?></td>
						<td><?= $versionFrom;?></td>
						<td>
							<select id="id_expert_module_version_to_<?=$escapedModuleId;?>" name="module_version_to_<?=$escapedModuleId;?>" onchange="UpdateSystemExpertHelper.getInstance().handleSelectVersionChanged()">
								<?php foreach ($availableVersions as $version)
								{
									?>
									<option<?=(($selectedVersionTo === $version) ? " selected" : "")?>><?=htmlspecialcharsbx($version);?></option>
									<?php
								}
								?>
							</select>
						</td>
						<td><?php if(isset($moduleUpdates["#"]["VERSION"])):?><a href="javascript:ShowDescription('<?= $escapedModuleId ?>')"><?= GetMessage("SUP_SULL_NOTE_D") ?></a><?php endif ?></td>
					</tr>
					<?
				}
			}?>
		</table>
	</td>
</tr>
<script>
try
{
	UpdateIdentifier = function(module, version)
	{
		this.module = module;
		this.version = version;
	};
	UpdateIdentifier.createFromString = function(identifier)
	{
		var parts = identifier.split('|');

		return new UpdateIdentifier(parts[0], parts[1]);
	};
	UpdateIdentifier.prototype.getModule = function()
	{
		return this.module;
	};
	UpdateIdentifier.prototype.getVersion = function()
	{
		return this.version;
	};
	UpdateIdentifier.prototype.toString = function()
	{
		return this.module + '|' + this.version;
	};
	UpdateIdentifier.prototype.isModuleTheSame = function(identifier)
	{
		return this.getModule() === identifier.getModule();
	};
	UpdateIdentifier.prototype.isGreaterThan = function(identifier)
	{
		if (!this.isModuleTheSame(identifier))
		{
			return false;
		}

		return UpdateIdentifier.compareVersions(this.getVersion(), identifier.getVersion()) > 0;
	};
	UpdateIdentifier.prototype.isGreaterOrEqualThan = function(identifier)
	{
		if (!this.isModuleTheSame(identifier))
		{
			return false;
		}

		return UpdateIdentifier.compareVersions(this.getVersion(), identifier.getVersion()) >= 0;
	};
	UpdateIdentifier.prototype.isLowerThan = function(identifier)
	{
		if (!this.isModuleTheSame(identifier))
		{
			return false;
		}

		return UpdateIdentifier.compareVersions(this.getVersion(), identifier.getVersion()) < 0;
	};
	UpdateIdentifier.prototype.isLowerOrEqualThan = function(identifier)
	{
		if (!this.isModuleTheSame(identifier))
		{
			return false;
		}

		return UpdateIdentifier.compareVersions(this.getVersion(), identifier.getVersion()) <= 0;
	};
	UpdateIdentifier.compareVersions = function(first, second)
	{
		first = UpdateIdentifier.normalizeVersion(first);
		second = UpdateIdentifier.normalizeVersion(second);

		var firstParts = first.split('.').map(Number);
		var secondParts = second.split('.').map(Number);
		if (
			firstParts[0] === secondParts[0]
			&& firstParts[1] === secondParts[1]
			&& firstParts[2] === secondParts[2]
		)
		{
			return 0;
		}
		if (
			firstParts[0] > secondParts[0]
			||
			(
				firstParts[0] === secondParts[0]
				&& firstParts[1] > secondParts[1]
			)
			||
			(
				firstParts[0] === secondParts[0]
				&& firstParts[1] === secondParts[1]
				&& firstParts[2] > secondParts[2]
			)
		)
		{
			return 1;
		}

		return -1;
	};
	UpdateIdentifier.normalizeVersion = function(version) {
		var matches = version.match(/(\d+)\.(\d+)\.(\d+)/);
		if (matches !== null && matches.length && matches.length > 0)
		{
			return matches[0];
		}

		return version;
	};
	UpdateSystemExpertHelper = function(dependencies, updateCounts, clientModules)
	{
		this.dependencies = dependencies;
		this.updateCounts = updateCounts;
		this.clientModules = clientModules;
		this.installedUpdates = [];
		this.updatedModules = {};
	};
	UpdateSystemExpertHelper.messages = {
		CONFLICT_POPUP_TITLE: "<?=GetMessageJS('SUP_CONFLICT_POPUP_TITLE');?>",
		CONFLICT_MODULE_MESSAGE: "<?=GetMessageJS('SUP_CONFLICT_MODULE_MESSAGE');?>",
		SUP_CONFLICT_NOTHING_SELECTED: "<?=GetMessageJS('SUP_CONFLICT_NOTHING_SELECTED');?>",
		SUP_IMPORT_SUCCESS: "<?=GetMessageJS('SUP_IMPORT_SUCCESS');?>",
		SUP_IMPORT_ERROR: "<?=GetMessageJS('SUP_IMPORT_ERROR');?>",
	};
	UpdateSystemExpertHelper.getMessage = function(code, replacements)
	{
		var message = UpdateSystemExpertHelper.messages[code];
		if (!message)
		{
			return '';
		}
		if (!replacements || typeof(replacements) !== 'object')
		{
			return message;
		}
		Object.keys(replacements).forEach(function(replacement) {
			var globalRegexp = new RegExp(replacement, 'gi');
			message = message.replace(
				globalRegexp,
				function() {
					return replacements[replacement] ? String(replacements[replacement]) : '';
				}
			);
		});

		return message;
	};
	UpdateSystemExpertHelper.prototype.getSelectedUpdates = function()
	{
		var moduleNames = Object.keys(this.updateCounts);
		var selectedUpdates = [];
		var selectNode;
		var selectedIndex;
		var input;

		for (var i = 0, cnt = moduleNames.length; i < cnt; i++)
		{
			var moduleName = moduleNames[i];
			selectNode = document.getElementById('id_expert_module_version_to_' + moduleName);
			if (!selectNode || selectNode.tagName.toUpperCase() !== 'SELECT')
			{
				continue;
			}
			selectedIndex = selectNode.selectedIndex;
			if (selectedIndex === undefined || !selectNode.options)
			{
				continue;
			}
			input = document.getElementById('id_expert_select_module_' + moduleName);
			if (input && input.checked)
			{
				selectedUpdates.push(new UpdateIdentifier(moduleName, selectNode.options[selectedIndex].innerText));
			}
		}

		return selectedUpdates;
	};
	UpdateSystemExpertHelper.prototype.getUninstalledDependencies = function(selectedUpdates)
	{
		var uninstalledDependencies = {};

		Object.keys(this.dependencies).forEach(function(updateStringIdentifier) {
			var uninstalledModuleDependencies = [];
			var moduleDependencies = this.dependencies[updateStringIdentifier];
			moduleDependencies.forEach(function(dependencyStringIdentifier) {
				var dependencyIdentifier = UpdateIdentifier.createFromString(dependencyStringIdentifier);
				if (!this.hasModule(dependencyIdentifier.getModule()))
				{
					// skip modules that are not present on this server
					return;
				}
				if (this.isUpdateInstalled(dependencyIdentifier))
				{
					return;
				}
				if (!this.isUpdateSelected(dependencyIdentifier, selectedUpdates))
				{
					uninstalledModuleDependencies.push(dependencyIdentifier);
				}
			}.bind(this));
			if (uninstalledModuleDependencies.length > 0)
			{
				uninstalledDependencies[updateStringIdentifier] = uninstalledModuleDependencies;
			}
		}.bind(this));

		return uninstalledDependencies;
	};
	UpdateSystemExpertHelper.prototype.isUpdateSelected = function(identifier, selectedUpdates)
	{
		var result = false;

		selectedUpdates.forEach(function(installedIdentifier) {
			if (!result && installedIdentifier.isGreaterOrEqualThan(identifier))
			{
				result = true;
			}
		}.bind(this));

		return result;
	};
	UpdateSystemExpertHelper.prototype.isUpdateInstalled = function(identifier) {
		var moduleName = identifier.getModule();
		if (!this.clientModules[moduleName])
		{
			// module is not installed
			return false;
		}
		var installedIdentifier = new UpdateIdentifier(moduleName, this.clientModules[moduleName]);

		return installedIdentifier.isGreaterOrEqualThan(identifier);
	};
	UpdateSystemExpertHelper.prototype.hasModule = function(module)
	{
		return this.updateCounts.hasOwnProperty(module);
	};
	UpdateSystemExpertHelper.prototype.collapseMeaninglessDependencies = function(uninstalledDependencies, selectedUpdates)
	{
		var result = {};

		Object.keys(uninstalledDependencies).forEach(function(installUpdateStringIdentifier) {
			var installUpdateIdentifier = UpdateIdentifier.createFromString(installUpdateStringIdentifier);
			if (!this.isUpdateSelected(installUpdateIdentifier, selectedUpdates))
			{
				return;
			}
			var module = installUpdateIdentifier.getModule();
			if (!result[module])
			{
				result[module] = {
					update: null,
					requires: {},
				};
			}
			if (
				!result[module].update
				|| installUpdateIdentifier.isLowerThan(result[module].update)
			)
			{
				result[module].update = installUpdateIdentifier;
			}
			uninstalledDependencies[installUpdateStringIdentifier].forEach(function(dependencyIdentifier) {
				var requiredModule = dependencyIdentifier.getModule();
				if (
					!result[module].requires[requiredModule]
					|| dependencyIdentifier.isLowerThan(result[module].requires[requiredModule])
				)
				{
					result[module].requires[requiredModule] = dependencyIdentifier;
				}
			}.bind(this));
		}.bind(this));

		return result;
	};
	UpdateSystemExpertHelper.prototype.prepareDependencies = function(selectedUpdates)
	{
		return this.collapseMeaninglessDependencies(this.getUninstalledDependencies(selectedUpdates), selectedUpdates);
	};
	UpdateSystemExpertHelper.prototype.getConflictMessage = function()
	{
		var selectedUpdates = this.getSelectedUpdates();
		if (selectedUpdates.length === 0)
		{
			return UpdateSystemExpertHelper.getMessage('SUP_CONFLICT_NOTHING_SELECTED');
		}
		var dependencies = this.prepareDependencies(selectedUpdates);
		var modules = Object.keys(dependencies);
		if (modules.length <= 0)
		{
			return null;
		}

		var message = '';
		modules.forEach(function(module) {
			var requiredUpdates = Object.values(dependencies[module].requires);
			var requiredUpdatesMessage = '';
			requiredUpdates.forEach(function(requiredUpdate) {
				if (requiredUpdatesMessage.length > 0)
				{
					requiredUpdatesMessage += ', ';
				}
				requiredUpdatesMessage += '<a href="#expert_module_' + requiredUpdate.getModule() + '">' + requiredUpdate.getModule() + " " + requiredUpdate.getVersion() + '</a>';
			}.bind(this));
			message += UpdateSystemExpertHelper.getMessage('CONFLICT_MODULE_MESSAGE', {
				'#MODULE#': '<a href="#expert_module_' + module + '">' + module + " " + dependencies[module].update.getVersion() + '</a>',
				'#REQUIRES#': requiredUpdatesMessage
			})
			message += "<br>\n";
		}.bind(this));

		return message;
	};
	UpdateSystemExpertHelper.isObjectsEqual = function(obj1, obj2) {
		var props1 = Object.getOwnPropertyNames(obj1);
		var props2 = Object.getOwnPropertyNames(obj2);
		if (props1.length !== props2.length)
		{
			return false;
		}
		for (var i = 0; i < props1.length; i++)
		{
			var prop = props1[i];
			var bothAreObjects = typeof(obj1[prop]) === "object" && typeof(obj2[prop]) === "object";
			if (
				(!bothAreObjects && (obj1[prop] !== obj2[prop]))
				|| (bothAreObjects && !UpdateSystemExpertHelper.isObjectsEqual(obj1[prop], obj2[prop]))
			)
			{
				return false;
			}
		}

		return true;
	};
	UpdateSystemExpertHelper.runTests = function()
	{
		UpdateSystemExpertHelper.testGetUninstalledDependencies();
		UpdateSystemExpertHelper.testCollapseMeaninglessDependencies();
	}
	UpdateSystemExpertHelper.testGetUninstalledDependencies = function(isDebug)
	{
		var count = {
			success: 0,
			errors: 0,
		};
		var errors = [];
		var samples = {
			empty: {
				selectedUpdates: [],
				dependencies: {},
				updateCounts: {},
				uninstalledDependencies: {},
			},
			dependencyAbsent: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|20.0.0")
				],
				dependencies: {
					"main|20.0.0": ["catalog|21.0.0"]
				},
				updateCounts: {
					main: 10
				},
				uninstalledDependencies: {},
			},
			lowerVersionIncludedInUninstalledDependencies: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|19.999.999")
				],
				dependencies: {
					"main|20.0.0": ["ui|21.0.0"]
				},
				updateCounts: {
					main: 1,
					ui: 1
				},
				uninstalledDependencies: {
					"main|20.0.0": [UpdateIdentifier.createFromString("ui|21.0.0")]
				},
			},
			complex: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("ui|20.0.0"),
					UpdateIdentifier.createFromString("iblock|20.0.0"),
					UpdateIdentifier.createFromString("main|21.0.0")
				],
				dependencies: {
					"ui|19.0.0": ["main|20.0.0"],
					"main|21.0.0": ["iblock|21.0.0"],
					"iblock|20.0.0": ["ui|20.0.0"]
				},
				updateCounts: {
					main: 1,
					ui: 1,
					iblock: 1
				},
				uninstalledDependencies: {
					"main|21.0.0": [UpdateIdentifier.createFromString("iblock|21.0.0")]
				},
			},
		};

		if (isDebug === true)
		{
			debugger;
		}
		Object.keys(samples).forEach(function(caseName) {
			var updateHelper = new UpdateSystemExpertHelper(samples[caseName].dependencies, samples[caseName].updateCounts)

			var uninstalledDependencies = updateHelper.getUninstalledDependencies(samples[caseName].selectedUpdates);

			if (!UpdateSystemExpertHelper.isObjectsEqual(uninstalledDependencies, samples[caseName].uninstalledDependencies))
			{
				count.errors++;
				errors.push({
					message: 'Error testGetUninstalledDependencies with case ' + caseName,
					actual: uninstalledDependencies,
					expected: samples[caseName].uninstalledDependencies
				});
			}
			else
			{
				count.success++;
			}
		});

		console.log(count);
		console.log(errors);
	};
	UpdateSystemExpertHelper.testCollapseMeaninglessDependencies = function(isDebug)
	{
		var count = {
			success: 0,
			errors: 0,
		};
		var errors = [];
		var samples = {
			empty: {
				selectedUpdates: [],
				uninstalledDependencies: {},
				collapsedDependencies: {}
			},
			removeAbsentUpdates: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|20.0.0")
				],
				uninstalledDependencies: {
					"main|21.0.0": [UpdateIdentifier.createFromString("iblock|21.0.0")]
				},
				collapsedDependencies: {}
			},
			collapseToMinimumInstalledUpdates: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|21.0.0")
				],
				uninstalledDependencies: {
					"main|21.0.0": [UpdateIdentifier.createFromString("iblock|21.0.0")],
					"main|20.0.0": [UpdateIdentifier.createFromString("ui|21.0.0")],
					"main|20.99.99": [UpdateIdentifier.createFromString("crm|21.0.0")]
				},
				collapsedDependencies: {
					main: {
						update: UpdateIdentifier.createFromString("main|20.0.0"),
						requires: {
							iblock: UpdateIdentifier.createFromString("iblock|21.0.0"),
							ui: UpdateIdentifier.createFromString("ui|21.0.0"),
							crm: UpdateIdentifier.createFromString("crm|21.0.0")
						}
					}
				}
			},
			collapseToMinimumRequiredUpdates: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|21.0.0")
				],
				uninstalledDependencies: {
					"main|21.0.0": [
						UpdateIdentifier.createFromString("iblock|21.0.0"),
						UpdateIdentifier.createFromString("iblock|20.0.0"),
						UpdateIdentifier.createFromString("iblock|19.0.0")
					],
				},
				collapsedDependencies: {
					main: {
						update: UpdateIdentifier.createFromString("main|21.0.0"),
						requires: {
							iblock: UpdateIdentifier.createFromString("iblock|19.0.0")
						}
					}
				}
			},
			complex: {
				selectedUpdates: [
					UpdateIdentifier.createFromString("main|21.0.0"),
				],
				uninstalledDependencies: {
					"main|21.0.0": [
						UpdateIdentifier.createFromString("ui|21.0.0"),
						UpdateIdentifier.createFromString("iblock|20.0.0"),
						UpdateIdentifier.createFromString("iblock|19.0.0")
					],
					"main|20.0.0": [
						UpdateIdentifier.createFromString("iblock|21.0.0"),
						UpdateIdentifier.createFromString("ui|20.0.0"),
						UpdateIdentifier.createFromString("ui|19.0.0")
					]
				},
				collapsedDependencies: {
					main: {
						update: UpdateIdentifier.createFromString("main|20.0.0"),
						requires: {
							iblock: UpdateIdentifier.createFromString("iblock|19.0.0"),
							ui: UpdateIdentifier.createFromString("ui|19.0.0")
						}
					}
				}
			}
		}

		if (isDebug === true)
		{
			debugger;
		}
		Object.keys(samples).forEach(function(caseName) {
			var updateHelper = new UpdateSystemExpertHelper({}, {})

			var collapsedDependencies = updateHelper.collapseMeaninglessDependencies(
				samples[caseName].uninstalledDependencies,
				samples[caseName].selectedUpdates
			);

			if (!UpdateSystemExpertHelper.isObjectsEqual(collapsedDependencies, samples[caseName].collapsedDependencies))
			{
				count.errors++;
				errors.push({
					message: 'Error testCollapseMeaninglessDependencies with case ' + caseName,
					actual: collapsedDependencies,
					expected: samples[caseName].collapsedDependencies
				});
			}
			else
			{
				count.success++;
			}
		});

		console.log(count);
		console.log(errors);
	};
	UpdateSystemExpertHelper.getInstance = function()
	{
		if (!UpdateSystemExpertHelper.instance)
		{
			UpdateSystemExpertHelper.instance = new UpdateSystemExpertHelper(
				{<?=$expertUpdateHelper->getJavascriptObjectWithDependencies();?>},
				{<?=$expertUpdateHelper->getJavascriptObjectWithUpdates();?>},
				<?=json_encode($expertUpdateHelper->getClientModules());?>
			);
		}

		return UpdateSystemExpertHelper.instance;
	};
	UpdateSystemExpertHelper.prototype.processConflicts = function()
	{
		var conflictMessage = this.getConflictMessage();
		if (!conflictMessage)
		{
			this.enableInstallButton();
			this.hideConflictMessage();
			return;
		}

		this.disableInstallButton();
		this.showConflictMessage(conflictMessage);
	};
	UpdateSystemExpertHelper.prototype.showConflictMessage = function(conflictMessage)
	{
		document.getElementById('expert_install_conflicts').classList.remove('conflicts_message_hidden');
		document.getElementById('expert_install_conflicts_message').innerHTML = conflictMessage;
	};
	UpdateSystemExpertHelper.prototype.hideConflictMessage = function()
	{
		document.getElementById('expert_install_conflicts').classList.add('conflicts_message_hidden');
		document.getElementById('expert_install_conflicts_message').innerHTML = '';
	};
	UpdateSystemExpertHelper.prototype.disableInstallButton = function()
	{
		this.disableElement(document.getElementById('expert_install_updates_sel_button'));
	};
	UpdateSystemExpertHelper.prototype.enableInstallButton = function()
	{
		this.enableElement(document.getElementById('expert_install_updates_sel_button'));
	};
	UpdateSystemExpertHelper.prototype.handleSelectVersionChanged = function()
	{
		this.processConflicts();
	};
	UpdateSystemExpertHelper.prototype.handleModuleCheckboxClicked = function()
	{
		this.processConflicts();
	};
	UpdateSystemExpertHelper.prototype.handleInstallUpdatesButtonClicked = function()
	{
		var conflictMessage = this.getConflictMessage();
		if (conflictMessage)
		{
			this.showConflictMessage(conflictMessage);
			this.disableInstallButton();
			return;
		}

		var selectedModules = this.getSelectedModules();
		if (Object.keys(selectedModules).length > 0)
		{
			this.installUpdates(selectedModules);
		}
	};

	UpdateSystemExpertHelper.prototype.getSelectedModules = function()
	{
		var selectedModules = {};
		var selectedUpdates = this.getSelectedUpdates();

		selectedUpdates.forEach(function(updateTo) {
			var module = updateTo.getModule();
			selectedModules[module] = updateTo.getVersion();
		}.bind(this));

		return selectedModules;
	}

	UpdateSystemExpertHelper.prototype.handleExportButtonClicked = function()
	{
		var modules = this.getSelectedModules();
		if (Object.keys(modules).length === 0)
		{
			return;
		}

		const file = new File(
			[ JSON.stringify(modules) ],
			'updates.json',
			{ type: 'application/json' },
		);

		const link = document.createElement('a');
		link.href = URL.createObjectURL(file);
		link.download = 'updates.json';
		link.click();

		setTimeout(function () {
			URL.revokeObjectURL(link.href);
		}, 60 * 1000);
	};

	UpdateSystemExpertHelper.prototype.handleImportButtonClicked = function()
	{
		const input = document.createElement('input');
		input.setAttribute('type', 'file');
		input.setAttribute('accept', '.json');

		input.onchange = function () {
			input.files[0].text().then(function(text) {
				var modulesUpdates;
				try
				{
					modulesUpdates = JSON.parse(text);
				}
				catch (error)
				{
				}

				if (typeof modulesUpdates !== "object")
				{
					alert(UpdateSystemExpertHelper.getMessage('SUP_IMPORT_ERROR'));
					return;
				}

				var checkbox = document.getElementById('expert_id_select_all');
				checkbox.checked = false;
				this.handleSelectAllRowsClicked(false);

				Object.keys(modulesUpdates).forEach(function(module) {
					var moduleCheckbox = document.getElementById('id_expert_select_module_' + module);
					if (moduleCheckbox)
					{
						moduleCheckbox.checked = true;
					}

					var selectNode = document.getElementById('id_expert_module_version_to_' + module);
					if (selectNode && selectNode.tagName.toUpperCase() === 'SELECT')
					{
						for (var i = 0; i < selectNode.length; i++)
						{
							selectNode[i].selected = (selectNode[i].value == modulesUpdates[module]);
						}
					}
				});

				this.processConflicts();

				// reset file input
				input.value = '';

				setTimeout(function () {
					alert(UpdateSystemExpertHelper.getMessage('SUP_IMPORT_SUCCESS'));
				}, 100);

			}.bind(this));

		}.bind(this);

		input.click();
	}

	UpdateSystemExpertHelper.prototype.installUpdates = function(selectedModules)
	{
		SetProgressHint("<?= GetMessageJS("SUP_INITIAL") ?>");
		globalQuantity = 0;
		Object.keys(selectedModules).forEach(function(module) {
			globalQuantity += Number(this.updateCounts[module]);
		}.bind(this));
		cycleModules = true;
		cycleLangs = false;

		tabControl.SelectTab('tab1');

		this.selectedModulesToInstall = selectedModules;
		this.updatedModules = [];
		__InstallUpdates();
		SetProgressD();
	};
	UpdateSystemExpertHelper.prototype.isExpertModeEnabled = function()
	{
		return this.selectedModulesToInstall && Object.keys(this.selectedModulesToInstall).length > 0;
	};
	UpdateSystemExpertHelper.prototype.getInstallationData = function()
	{
		return 'expertModules=' + encodeURIComponent(JSON.stringify(this.selectedModulesToInstall));
	};
	UpdateSystemExpertHelper.prototype.handleInstallationCompleted = function()
	{
		this.selectedModulesToInstall = [];
		this.updatedModules = [];
	};
	UpdateSystemExpertHelper.prototype.handleSelectAllRowsClicked = function(processConflicts = true)
	{
		var checkbox = document.getElementById('expert_id_select_all');
		if (!checkbox)
		{
			return;
		}
		var isChecked = checkbox.checked;
		var modules = Object.keys(this.updateCounts);
		modules.forEach(function(module) {
			var moduleCheckbox = document.getElementById('id_expert_select_module_' + module);
			if (moduleCheckbox)
			{
				moduleCheckbox.checked = isChecked;
			}
		});

		if (processConflicts)
		{
			this.processConflicts();
		}
	};
	UpdateSystemExpertHelper.prototype.disableUpdatesTable = function()
	{
		this.disableInstallButton();
		var moduleNames = Object.keys(this.updateCounts);
		moduleNames.forEach(function(moduleName) {
			this.disableElement(document.getElementById('id_expert_select_module_' + moduleName));
		}.bind(this));
		this.disableElement(document.getElementById('expert_id_select_all'));
	};
	UpdateSystemExpertHelper.prototype.disableElement = function(element)
	{
		if (!element || !element.style)
		{
			return;
		}
		element.disabled = true;
		element.style.pointerEvents = "none";
	};
	UpdateSystemExpertHelper.prototype.enableElement = function(element)
	{
		if (!element || !element.style)
		{
			return;
		}
		element.disabled = false;
		element.style.pointerEvents = "";
	};

	BX.ready(function() {
		UpdateSystemExpertHelper.getInstance().processConflicts();
	});
}
catch (e)
{
	console.error('Expert mode is unavailable', e);
}
</script>