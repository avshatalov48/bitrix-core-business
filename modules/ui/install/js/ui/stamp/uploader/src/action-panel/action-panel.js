import {EventEmitter, BaseEvent} from 'main.core.events';
import {Tag, Cache, Loc} from 'main.core';

import './css/style.css';

type ActionPanelOptions = {
	events?: {
		onCropClick: (event: BaseEvent) => void,
	},
};

export default class ActionPanel extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: ActionPanelOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader.ActionPanel');
		this.setOptions(options);
	}

	setOptions(options: ActionPanelOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): ActionPanelOptions
	{
		return this.cache.get('options', {});
	}

	getCropButton(): HTMLDivElement
	{
		return this.cache.remember('cropButton', () => {
			const onClick = (event: MouseEvent) => {
				event.preventDefault();
				this.emit('onCropClick');
			};

			return Tag.render`
				<div 
					class="ui-stamp-uploader-crop-button"
					onclick="${onClick}"
				>
					${Loc.getMessage('UI_STAMP_UPLOADER_CROP_BUTTON_LABEL')}
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-action-panel">
					${this.getCropButton()}
				</div>
			`;
		});
	}
}