import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';

class WorkgroupCardThemePicker
{
	constructor(params)
	{
		this.containerNode = params.containerNode;

		this.init();
	}

	init()
	{
		if (!this.containerNode)
		{
			return;
		}

		const themePickerInstance = BX.Intranet.Bitrix24.ThemePicker.Singleton;
		const themePickerNode = this.containerNode.querySelector('.socialnetwork-group-slider-theme-btn');

		if (themePickerNode)
		{
			themePickerNode.addEventListener('click', () => {
				themePickerInstance.showDialog(true);
			});
		}

		EventEmitter.subscribe('Intranet.ThemePicker:onSaveTheme', (event: BaseEvent) => {
			const { theme } = event.getData();
			this.draw(theme);
		});
	}

	draw(theme)
	{
		const themeBoxNode = this.containerNode.querySelector('.socialnetwork-group-slider-theme-box');
		if (!themeBoxNode)
		{
			return;
		}

		themeBoxNode.style.backgroundImage = (Type.isStringFilled(theme.previewImage) ? `url('${encodeURI(theme.previewImage)}')` : 'none');
		themeBoxNode.style.backgroundColor = (Type.isStringFilled(theme.previewColor) ? theme.previewColor : 'transparent');
	}
}

export {
	WorkgroupCardThemePicker,
};
