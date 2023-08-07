const DesktopSettingsKey = {
	smoothing: 'bxd_camera_smoothing'
};

export const settingsFunctions = {
	getCameraSmoothingStatus(): boolean
	{
		return BXDesktopSystem?.QuerySettings(DesktopSettingsKey.smoothing, '0') === '1';
	},
	setCameraSmoothingStatus(status: boolean)
	{
		const preparedStatus = status === true ? '1' : '0';
		BXDesktopSystem?.StoreSettings(DesktopSettingsKey.smoothing, preparedStatus);
	}
};