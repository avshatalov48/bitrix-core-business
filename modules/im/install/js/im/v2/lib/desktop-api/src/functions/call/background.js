export const callBackgroundFunctions = {
	getBackgroundImage(): Object
	{
		if (!this.isDesktop())
		{
			return { id: 'none', source: '' };
		}

		const id = BXDesktopSystem.QuerySettings("bxd_camera_background_id") || 'none';

		return { id };
	},
	setCallBackground(id, source)
	{
		if (source === 'none' || source === '')
		{
			source = '';
		}
		else if (source === 'blur')
		{
			// empty
		}
		else if (source === 'gaussianBlur')
		{
			source = 'GaussianBlur';
		}
		else
		{
			source = this.prepareResourcePath(source);
		}

		var promise = new BX.Promise();

		setTimeout(() => {
			this.setCallMask(false);
			BXDesktopSystem.StoreSettings('bxd_camera_background_id', id);
			BXDesktopSystem.StoreSettings('bxd_camera_background', source);
			promise.resolve();
		}, 100);

		return promise;
	},
};
