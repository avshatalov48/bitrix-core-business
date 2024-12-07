
export const callBackgroundFunctions = {
	isBlur(source)
	{
		return source.toString().toLowerCase().includes('blur')
	},
	getLimitationBackground(source)
	{
		const limitation = BX.message('call_features');
		const defaultLimitation = {enable: true};
		let limitationType = '';

		if (source && source !== 'none') {
			limitationType = `${this.isBlur(source) ? 'blur_' : ''}background`
		}
		const currentLimitation = limitationType ? limitation?.[`call_${limitationType}`] : null;

		if (!currentLimitation) {
			return defaultLimitation;
		}

		return {
			enable: currentLimitation.enable,
			articleCode: currentLimitation.articleCode
		}
	},
	openArticle(articleCode)
	{
		const infoHelper = BX.UI.InfoHelper;

		if (infoHelper.isOpen())
		{
			infoHelper.close()
		}

		infoHelper.show(articleCode);
	},
	handleLimitationBackground(limitationObj, handle)
	{
		const {enable, articleCode} = limitationObj

		if (enable && typeof handle === "function")
		{
			handle()
		}

		if (!enable && articleCode)
		{
			this.openArticle(articleCode)
		}
	},
	getBackgroundImage(): Object
	{
		const id = BXDesktopSystem.QuerySettings("bxd_camera_background_id") || 'none';

		if (!this.isDesktop() || !this.getLimitationBackground(id)?.enable)
		{
			return { id: 'none', source: '' };
		}

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

		const limitation = this.getLimitationBackground(source);

		let currentSource = '';
		let currentId = '';


		this.handleLimitationBackground(limitation, () => {
			currentSource = source;
			currentId = id;
		})

		setTimeout(() => {
			this.setCallMask(false);
			BXDesktopSystem.StoreSettings('bxd_camera_background_id', currentId);
			BXDesktopSystem.StoreSettings('bxd_camera_background', currentSource);
			promise.resolve(currentId || "none");
		}, 100);

		return promise;
	},
};
