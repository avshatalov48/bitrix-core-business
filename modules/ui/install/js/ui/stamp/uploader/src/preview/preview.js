import {BaseEvent, EventEmitter} from 'main.core.events';
import {Dom, Tag, Cache, Loc} from 'main.core';

import './css/style.css';

type PreviewOptions = {
	events: {
		[key: string]: (event: BaseEvent) => void,
	},
};

export default class Preview extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: PreviewOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	setOptions(options: PreviewOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): PreviewOptions
	{
		return this.cache.get('options', {});
	}

	getImagePreviewLayout(): HTMLDivElement
	{
		return this.cache.remember('imagePreviewLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-preview-image"></div>
			`;
		});
	}

	getActionButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('actionButtonLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-preview-actions-button"></div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div 
					class="ui-stamp-uploader-preview" 
					title="${Loc.getMessage('UI_STAMP_UPLOADER_PREVIEW_TITLE')}"
				>
					${this.getImagePreviewLayout()}
					${this.getActionButtonLayout()}
				</div>
			`;
		});
	}

	show(src: string)
	{
		Dom.style(this.getImagePreviewLayout(), {
			backgroundImage: `url(${src})`,
		});

		Dom.addClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	}

	hide()
	{
		Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	}
}