export const DesktopSettingsKey = {
	hideImTab: 'bxd_hide_im_tab',
	smoothing: 'bxd_camera_smoothing',
	smoothing_v2: 'bxd_camera_smoothing_v2',
	sliderBindingsStatus: 'sliderBindingsStatus',
};

export const settingsFunctions = {
	getSliderBindingsStatus(): boolean
	{
		const result = this.getCustomSetting(DesktopSettingsKey.sliderBindingsStatus, '1');

		return result === '1';
	},
	isAirDesignEnabledInDesktop(): boolean
	{
		// there is only AIR design now. Temporary solution, need to remove it in the future
		const isAirDesignEnabled = true;

		return this.isDesktop() && isAirDesignEnabled;
	},
	getCameraSmoothingStatus(): boolean
	{
		return this.getCustomSetting(DesktopSettingsKey.smoothing, '0') === '1';
	},
	setCameraSmoothingStatus(status: boolean)
	{
		const preparedStatus = status === true ? '1' : '0';

		if (this.getApiVersion() > 76)
		{
			this.setCustomSetting(DesktopSettingsKey.smoothing_v2, preparedStatus);
			return;
		}

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
	setCustomSetting(name: string, value: string)
	{
		BXDesktopSystem?.StoreSettings(name, value);
	},
	getCustomSetting(name: string, defaultValue: string): string
	{
		return BXDesktopSystem?.QuerySettings(name, defaultValue);
	},
};
