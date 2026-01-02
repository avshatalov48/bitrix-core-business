import { Browser } from 'main.core';
import { DesktopFeature } from '../features';

type DesktopFeatureItem = $Keys<typeof DesktopFeature>;

export const versionFunctions = {
	getMajorVersion(): number
	{
		if (!this.isDesktop())
		{
			return 0;
		}

		const [majorVersion] = window.BXDesktopSystem.GetProperty('versionParts');

		return majorVersion;
	},
	getApiVersion(): number
	{
		if (!this.isDesktop())
		{
			return 0;
		}

		// eslint-disable-next-line no-unused-vars
		const [majorVersion, minorVersion, buildVersion, apiVersion] = window.BXDesktopSystem.GetProperty('versionParts');

		return apiVersion;
	},
	isFeatureEnabled(code: string): boolean
	{
		return Boolean(window.BXDesktopSystem?.FeatureEnabled(code));
	},
	isFeatureSupported(code: DesktopFeatureItem): boolean
	{
		return this.isFeatureSupportedInVersion(this.getApiVersion(), code);
	},
	isFeatureSupportedInVersion(version: number, code: DesktopFeatureItem): boolean
	{
		if (!DesktopFeature[code])
		{
			return false;
		}

		return version >= DesktopFeature[code].version;
	},
	/**
	  * Returns the Windows OS build number.
	  * Returns 0 if the OS is not Windows or if the function does not exist.
	  * For a list of Windows build numbers, see: https://en.wikipedia.org/wiki/List_of_Microsoft_Windows_versions
	  */
	getWindowsOSBuild(): number
	{
		if (!Browser.isWin())
		{
			return 0;
		}

		return window.BXDesktopSystem?.UserOsBuild() ?? 0;
	},
};
