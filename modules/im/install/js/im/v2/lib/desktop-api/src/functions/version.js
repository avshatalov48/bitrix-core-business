import { DesktopFeature } from '../features';

type DesktopFeatureItem = $Keys<typeof DesktopFeature>;

export const versionFunctions = {
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
};
