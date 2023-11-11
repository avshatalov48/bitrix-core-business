export const callMaskFunctions = {
	getCallMask()
	{
		if (!this.isDesktop())
		{
			return { id: '' };
		}

		return {
			id: BXDesktopSystem.QuerySettings('bxd_camera_3dbackground_id') || '',
		};
	},
	setCallMaskLoadHandlers(callback: function)
	{
		this.subscribe('BX3dAvatarReady', callback);
		this.subscribe('BX3dAvatarError', callback);
	},
	setCallMask(id, maskUrl, backgroundUrl): boolean
	{
		if (this.getApiVersion() < 72)
		{
			return false;
		}

		if (!id)
		{
			BXDesktopSystem.Set3dAvatar('', '');
			BXDesktopSystem.StoreSettings('bxd_camera_3dbackground_id', '');
			return true;
		}

		maskUrl = this.prepareResourcePath(maskUrl);
		backgroundUrl = this.prepareResourcePath(backgroundUrl);

		BXDesktopSystem.Set3dAvatar(maskUrl, backgroundUrl);
		BXDesktopSystem.StoreSettings('bxd_camera_3dbackground_id', id);

		return true;
	},
};
