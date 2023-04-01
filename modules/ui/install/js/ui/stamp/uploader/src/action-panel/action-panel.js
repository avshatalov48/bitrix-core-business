import {EventEmitter, BaseEvent} from 'main.core.events';
import {Tag, Cache, Loc, Dom} from 'main.core';
import {ApplyButton, CancelButton, Button} from 'ui.buttons';

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
		this.subscribeFromOptions(options.events);
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

	getApplyButton(): Button
	{
		return this.cache.remember('applyButton', () => {
			return new ApplyButton({
				color: Button.Color.PRIMARY,
				size: Button.Size.EXTRA_SMALL,
				round: true,
				onclick: () => {
					this.emit('onApplyClick');
				},
			});
		});
	}

	getCancelButton(): Button
	{
		return this.cache.remember('cancelButton', () => {
			return new CancelButton({
				color: Button.Color.LIGHT_BORDER,
				size: Button.Size.EXTRA_SMALL,
				round: true,
				onclick: () => {
					this.emit('onCancelClick');
				},
			});
		});
	}

	getCropActionsLayout(): HTMLDivElement
	{
		return this.cache.remember('cropActionsLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-action-crop-actions" hidden>
					${this.getApplyButton().render()}
					${this.getCancelButton().render()}
				</div>
			`;
		});
	}

	showCropAction()
	{
		Dom.show(this.getCropActionsLayout());
		Dom.hide(this.getCropButton());
	}

	hideCropActions()
	{
		Dom.hide(this.getCropActionsLayout());
		Dom.show(this.getCropButton());
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-action-panel">
					${this.getCropActionsLayout()}
					${this.getCropButton()}
				</div>
			`;
		});
	}

	disable()
	{
		Dom.addClass(this.getLayout(), 'ui-stamp-uploader-action-panel-disabled');
	}

	enable()
	{
		Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-action-panel-disabled');
	}
}