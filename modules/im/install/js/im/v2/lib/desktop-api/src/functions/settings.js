export const DesktopSettingsKey = {
	smoothing: 'bxd_camera_smoothing',
	telemetry: 'bxd_telemetry',
	sliderBindingsStatus: 'sliderBindingsStatus',
};

export const settingsFunctions = {
	getCameraSmoothingStatus(): boolean
	{
		return this.getCustomSetting(DesktopSettingsKey.smoothing, '0') === '1';
	},
	setCameraSmoothingStatus(status: boolean)
	{
		const preparedStatus = status === true ? '1' : '0';
		this.setCustomSetting(DesktopSettingsKey.smoothing, preparedStatus);
	},
	isTwoWindowMode(): boolean
	{
		return Boolean(BXDesktopSystem?.IsTwoWindowsMode());
	},
	setTwoWindowMode(flag: boolean)
	{
		if (flag === true)
		{
			BXDesktopSystem?.V10();

			return;
		}

		BXDesktopSystem?.V8();
	},
	getAutostartStatus(): boolean
	{
		return BXDesktopSystem?.GetProperty('autostart');
	},
	setAutostartStatus(flag: boolean)
	{
		BXDesktopSystem?.SetProperty('autostart', flag);
	},
	getTelemetryStatus(): boolean
	{
		return this.getCustomSetting(DesktopSettingsKey.telemetry, '1') === '1';
	},
	setTelemetryStatus(flag: boolean)
	{
		this.setCustomSetting(DesktopSettingsKey.telemetry, flag ? '1' : '0');
	},
	setCustomSetting(name: string, value: string)
	{
		BXDesktopSystem?.StoreSettings(name, value);
	},
	getCustomSetting(name: string, defaultValue: string): string
	{
		return BXDesktopSystem?.QuerySettings(name, defaultValue);
	},
};
