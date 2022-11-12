import {Cache, Loc, Tag} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Button} from 'ui.buttons';

import './css/style.css';

type FileSelectOptions = {
	events: {
		[key: string]: (BaseEvent) => void,
	},
};

export default class FileSelect extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: FileSelectOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader.FileSelect');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	setOptions(options: FileSelectOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): FileSelectOptions
	{
		return this.cache.get('options', {});
	}

	getTakePhotoButton(): Button
	{
		return this.cache.remember('takePhotoButton', () => {
			return new Button({
				text: Loc.getMessage('UI_STAMP_UPLOADER_TAKE_PHOTO_BUTTON_LABEL'),
				color: Button.Color.LIGHT_BORDER,
				size: Button.Size.LARGE,
				icon: Button.Icon.CAMERA,
				round: true,
				onclick: () => {
					this.emit('onTakePhotoClick');
				},
			});
		});
	}

	getSelectPhotoButton(): Button
	{
		return this.cache.remember('selectPhotoButton', () => {
			return new Button({
				text: Loc.getMessage('UI_STAMP_UPLOADER_SELECT_PHOTO_BUTTON_LABEL'),
				color: Button.Color.LIGHT_BORDER,
				size: Button.Size.LARGE,
				icon: Button.Icon.DOWNLOAD,
				round: true,
				onclick: () => {
					this.emit('onTakePhotoClick');
				},
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-file-select">
					<div class="ui-stamp-uploader-file-select-select-photo">
						${this.getSelectPhotoButton().render()}
					</div>
				</div>
			`;
		});
	}
}