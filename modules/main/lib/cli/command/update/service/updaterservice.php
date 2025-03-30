<?php

namespace Bitrix\Main\Cli\Command\Update\Service;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdaterService
{
	protected bool $stableVersionsOnly;

	public function __construct()
	{
		$this->stableVersionsOnly = Option::get('main', 'stable_versions_only', 'Y') == 'Y';
	}

	public function listUpdates(): Result
	{
		$error = '';
		$result = new Result();

		$updateList = \CUpdateClient::GetUpdatesList($error, false, $this->stableVersionsOnly);

		if ($error != '')
		{
			return $result->addError(new Error($error));
		}

		if ($updateList)
		{
			if (isset($updateList["ERROR"]))
			{
				foreach ($updateList["ERROR"] as $errorMessage)
				{
					if ($errorMessage["@"]["TYPE"] != "RESERVED_KEY" && $errorMessage["@"]["TYPE"] != "NEW_UPDATE_SYSTEM")
					{
						$error .= "[" . $errorMessage["@"]["TYPE"] . "] " . $errorMessage["#"] . "\n";
					}
					elseif ($errorMessage["@"]["TYPE"] == "RESERVED_KEY")
					{
						$error .= "You must activate your license key before using the update system.\n";
					}
				}
				if ($error != '')
				{
					return $result->addError(new Error(trim($error)));
				}
			}

			$result->setData($updateList);
		}

		return $result;
	}

	public function repair(string $type): Result
	{
		$error = '';
		$result = new Result();

		if ($type == 'include')
		{
			if (!\CUpdateClient::RegisterVersion($error, false, $this->stableVersionsOnly))
			{
				return $result->addError(new Error($error));
			}
		}

		return $result;
	}

	public function updateUpdateSystem(): Result
	{
		$error = '';
		$result = new Result();

		if (!\CUpdateClient::UpdateUpdate($error, false, $this->stableVersionsOnly))
		{
			return $result->addError(new Error($error));
		}

		return $result;
	}

	public function installUpdates(array $modules, SymfonyStyle $io): Result
	{
		$result = new Result();

		while (true)
		{
			$error = '';
			$loadResult = \CUpdateClient::LoadModulesUpdates($error, $updateDescription, false, $this->stableVersionsOnly, $modules);

			if ($loadResult == 'S')
			{
				if (isset($updateDescription['DATA']['#']['ITEM']))
				{
					$this->showStep($updateDescription['DATA']['#']['ITEM'], $io);
				}
			}
			elseif ($loadResult == 'E')
			{
				if ($error == '')
				{
					$error = '[CL02] Cannot extract files from archive.';
				}
				return $result->addError(new Error($error));
			}
			elseif ($loadResult == 'F')
			{
				return $result;
			}

			$error = '';
			$loadResult = \CUpdateClient::LoadModulesUpdates($error, $updateDescription, false, $this->stableVersionsOnly, $modules);

			if ($loadResult == 'E')
			{
				return $result->addError(new Error($error));
			}

			$temporaryUpdatesDir = '';
			if (!\CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $error))
			{
				return $result->addError(new Error($error));
			}

			if (!\CUpdateClient::CheckUpdatability($temporaryUpdatesDir, $error))
			{
				return $result->addError(new Error($error));
			}

			if (!\CUpdateClient::UpdateStepModules($temporaryUpdatesDir, $error))
			{
				$error .= '[CL04] Module update failed.';
				return $result->addError(new Error($error));
			}

			\CUpdateClient::finalizeModuleUpdate($updateDescription["DATA"]["#"]["ITEM"]);

			$io->writeln('Done!');
		}
	}

	public function getDependencies(array $updateList, array $unresolved): array
	{
		$allModules = [];
		$dependencies = [];
		$resolved = [];

		foreach ($updateList as $moduleId => $module)
		{
			$allModules[$moduleId] = array_key_last($module['VERSION']);
		}

		// avoid endless loop
		$unresolved = array_filter($unresolved, function ($item) use ($allModules) { return isset($allModules[$item]); }, ARRAY_FILTER_USE_KEY);

		do
		{
			foreach ($updateList as $moduleId => $module)
			{
				if (isset($unresolved[$moduleId]) && !isset($resolved[$moduleId]))
				{
					$moduleDep = $this->getModuleDependencies($updateList, $moduleId, $allModules[$moduleId]);

					$resolved[$moduleId] = 1;
					unset($unresolved[$moduleId]);

					foreach ($moduleDep as $key => $value)
					{
						if (isset($allModules[$key]))
						{
							if (!isset($resolved[$key]))
							{
								$unresolved[$key] = 1;
							}
							$dependencies[$key] = $allModules[$key];
						}
					}
				}
			}
		}
		while (!empty($unresolved));

		return $dependencies;
	}

	public function checkExpertDependencies(array $updateList, array $expertModules): Result
	{
		$result = new Result();

		$unresolved = [];
		foreach ($expertModules as $module => $version)
		{
			$dependencies = $this->getModuleDependencies($updateList, $module, $version);

			foreach ($dependencies as $depModule => $depVersion)
			{
				if (!isset($expertModules[$depModule]) || \CUpdateClient::CompareVersions($expertModules[$depModule], $depVersion) < 0)
				{
					if (isset($updateList[$depModule]["VERSION"][$depVersion]))
					{
						$unresolved[$module . ' (' . $version . ')'][] = $depModule . ' (' . $depVersion . ')';
					}
				}
			}
		}

		if (!empty($unresolved))
		{
			$result->addError(new Error("Unresolved dependencies."));
			$result->setData($unresolved);
		}

		return $result;
	}

	protected function getModuleDependencies(array $updateList, string $moduleId, string $moduleVersion): array
	{
		$dependencies = [];

		if (isset($updateList[$moduleId]["VERSION"]) && is_array($updateList[$moduleId]["VERSION"]))
		{
			foreach ($updateList[$moduleId]["VERSION"] as $versionId => $version)
			{
				if (\CUpdateClient::CompareVersions($versionId, $moduleVersion) <= 0)
				{
					if (isset($version["VERSION_CONTROL"]) && is_array($version["VERSION_CONTROL"]))
					{
						foreach ($version["VERSION_CONTROL"] as $versionModuleId => $versionVersion)
						{
							if (isset($dependencies[$versionModuleId]))
							{
								if (\CUpdateClient::CompareVersions($versionVersion, $dependencies[$versionModuleId]) > 0)
								{
									$dependencies[$versionModuleId] = $versionVersion;
								}
							}
							else
							{
								$dependencies[$versionModuleId] = $versionVersion;
							}
						}
					}
				}
				else
				{
					break;
				}
			}
		}

		return $dependencies;
	}

	public function transformUpdateList(array $updateList): array
	{
		$result = [];

		foreach ($updateList as $module)
		{
			$moduleId = $module['@']['ID'];
			$result[$moduleId] = [];

			if (isset($module["#"]["VERSION"]) && is_array($module["#"]["VERSION"]))
			{
				$result[$moduleId]["VERSION"] = [];

				foreach ($module["#"]["VERSION"] as $version)
				{
					$versionId = $version["@"]["ID"];
					$result[$moduleId]["VERSION"][$versionId] = [];

					if (isset($version["#"]["VERSION_CONTROL"]) && is_array($version["#"]["VERSION_CONTROL"]))
					{
						$result[$moduleId]["VERSION"][$versionId]["VERSION_CONTROL"] = [];

						foreach ($version["#"]["VERSION_CONTROL"] as $versionControl)
						{
							$result[$moduleId]["VERSION"][$versionId]["VERSION_CONTROL"][$versionControl["@"]["MODULE"]] = $versionControl["@"]["VERSION"];
						}
					}
				}
			}
		}

		return $result;
	}

	public function installLanguages(array $languages, SymfonyStyle $io): Result
	{
		$result = new Result();

		while (true)
		{
			$error = '';
			$loadResult = \CUpdateClient::LoadLangsUpdates($error, $updateDescription, false, $this->stableVersionsOnly, $languages);

			if ($loadResult == "S")
			{
				if (isset($updateDescription['DATA']['#']['ITEM']))
				{
					$this->showStep($updateDescription['DATA']['#']['ITEM'], $io);
				}
			}
			elseif ($loadResult == "E")
			{
				if ($error == '')
				{
					$error = '[CL02] Cannot extract files from archive.';
				}
				return $result->addError(new Error($error));
			}
			elseif ($loadResult == "F")
			{
				return $result;
			}

			$error = '';
			$loadResult = \CUpdateClient::LoadLangsUpdates($error, $updateDescription, false, $this->stableVersionsOnly, $languages);

			if ($loadResult == 'E')
			{
				return $result->addError(new Error($error));
			}

			$temporaryUpdatesDir = '';
			if (!\CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $error))
			{
				return $result->addError(new Error($error));
			}

			if (isset($updateDescription["DATA"]["#"]["NOUPDATES"]))
			{
				\CUpdateClient::ClearUpdateFolder($_SERVER["DOCUMENT_ROOT"] . "/bitrix/updates/" . $temporaryUpdatesDir);

				$io->writeln('Done!');

				return $result;
			}
			else
			{
				if (!\CUpdateClient::UpdateStepLangs($temporaryUpdatesDir, $error))
				{
					$error .= "[CL04] Language file update failed.";
					return $result->addError(new Error($error));
				}

				$itemsUpdated = [];
				if (isset($updateDescription["DATA"]["#"]["ITEM"]))
				{
					foreach ($updateDescription["DATA"]["#"]["ITEM"] as $item)
					{
						$itemsUpdated[$item["@"]["ID"]] = $item["@"]["NAME"];
					}
				}

				\CUpdateClient::finalizeLanguageUpdate($itemsUpdated);
			}

			$io->writeln('Done!');
		}
	}

	protected function showStep(array $items, SymfonyStyle $io): void
	{
		$message = '';
		foreach ($items as $description)
		{
			if ($message != '')
			{
				$message .= ', ';
			}
			$message .= $description['@']['NAME'];
			if (!empty($description['@']['VALUE']))
			{
				$message .= ' ('. $description['@']['VALUE'] . ')';
			}
		}
		if ($message != '')
		{
			$io->write('Installing ' . $message . '... ');
		}
	}
}
