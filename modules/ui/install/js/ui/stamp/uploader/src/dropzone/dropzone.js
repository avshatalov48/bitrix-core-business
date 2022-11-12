import {EventEmitter, BaseEvent} from 'main.core.events';
import {Tag, Cache, Loc} from 'main.core';

import './css/style.css';

type DropzoneOptions = {
	events?: {[key: string]: (BaseEvent) => void},
};

export default class Dropzone extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: DropzoneOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader.Dropzone');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	setOptions(options: DropzoneOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): DropzoneOptions
	{
		return this.cache.get('options', {});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-dropzone">
					<div class="ui-stamp-uploader-dropzone-icon"></div>
					<div class="ui-stamp-uploader-dropzone-header">
						${Loc.getMessage('UI_STAMP_UPLOADER_DROPZONE_HEADER')}
					</div>
					<div class="ui-stamp-uploader-dropzone-text">
						${Loc.getMessage('UI_STAMP_UPLOADER_DROPZONE_TEXT')}
					</div>
				</div>
			`;
		});
	}
}