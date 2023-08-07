export const versionFunctions = {
	getApiVersion(): number
	{
		if (!this.isDesktop())
		{
			return 0;
		}

		const [majorVersion, minorVersion, buildVersion, apiVersion] = window.BXDesktopSystem.GetProperty('versionParts');

		return apiVersion;
	},
	isFeatureEnabled(code: string): boolean
	{
		return !!window.BXDesktopSystem?.FeatureEnabled(code);
	},
};