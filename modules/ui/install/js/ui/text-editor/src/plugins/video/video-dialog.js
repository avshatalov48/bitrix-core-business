import { Tag, Type, Dom, Loc } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { Popup, PopupTarget, PopupTargetOptions } from 'main.popup';
import { sanitizeUrl } from '../../helpers/sanitize-url';

import './video-dialog.css';

export type VideoDialogOptions = {
	targetContainer?: HTMLElement,
	events?: Object<string, (event: BaseEvent) => {}>,
}

export default class VideoDialog extends EventEmitter
{
	#popup: Popup = null;
	#videoUrl: string = '';
	#targetContainer: HTMLElement = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	constructor(options: VideoDialogOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.VideoDialog');

		const videoDialogOptions: VideoDialogOptions = Type.isPlainObject(options) ? options : {};

		this.setTargetContainer(videoDialogOptions.targetContainer);
		this.subscribeFromOptions(videoDialogOptions.events);
	}

	show(options: { target: PopupTarget, targetOptions: PopupTargetOptions } = {}): void
	{
		const target: PopupTarget = options.target ?? undefined;
		const targetOptions: PopupTargetOptions = Type.isPlainObject(options.targetOptions) ? options.targetOptions : {};

		if (!Type.isUndefined(target))
		{
			this.getPopup().setBindElement(target);
		}

		this.getPopup().adjustPosition({
			...targetOptions,
			forceBindPosition: true,
		});

		this.getPopup().show();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	isShown(): boolean
	{
		return this.#popup !== null && this.#popup.isShown();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	setVideoUrl(url: string): void
	{
		if (Type.isString(url))
		{
			this.#videoUrl = sanitizeUrl(url);
		}
	}

	getVideoUrl(): string
	{
		return this.#videoUrl;
	}

	setTargetContainer(container: HTMLElement): void
	{
		if (Type.isElementNode(container))
		{
			this.#targetContainer = container;
		}
	}

	getTargetContainer(): HTMLElement | null
	{
		return this.#targetContainer;
	}

	getPopup(): Popup
	{
		if (this.#popup === null)
		{
			this.#popup = new Popup({
				autoHide: true,
				cacheable: false,
				padding: 0,
				closeByEsc: true,
				targetContainer: this.getTargetContainer(),
				content: this.getContainer(),
				events: {
					onShow: () => {
						this.emit('onShow');
					},
					onClose: () => {
						this.emit('onClose');
					},
					onDestroy: () => {
						this.emit('onDestroy');
					},
					onAfterShow: () => {
						this.getUrlTextBox().focus();
					},
				},
			});
		}

		return this.#popup;
	}

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-text-editor-video-dialog">
					<div class="ui-text-editor-video-dialog-form">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-s ui-ctl-inline ui-ctl-w100 ui-text-editor-video-dialog-textbox">
							<div class="ui-ctl-tag">${Loc.getMessage('TEXT_EDITOR_VIDEO_INSERT_TITLE')}</div>
							${this.getUrlTextBox()}
						</div>
						<button type="button" 
							class="ui-text-editor-video-dialog-button" 
							onclick="${this.#handleSaveBtnClick.bind(this)}"
							data-testid="video-dialog-save-btn"
						>
							<span class="ui-icon-set --check"></span>
						</button>
						<button 
							type="button" 
							class="ui-text-editor-video-dialog-button"
							onclick="${this.#handleCancelBtnClick.bind(this)}"
							data-testid="video-dialog-cancel-btn"
						>
							<span class="ui-icon-set --cross-60"></span>
						</button>
					</div>
					${this.getStatusContainer()}
				</div>
			`;
		});
	}

	getUrlTextBox(): HTMLInputElement
	{
		return this.#refs.remember('url-textbox', () => {
			return Tag.render`
				<input 
					type="text"
					class="ui-ctl-element"
					placeholder="https://"
					value="${this.getVideoUrl()}"
					onkeydown="${this.#handleTextBoxKeyDown.bind(this)}"
					oninput="${this.#handleTextBoxInput.bind(this)}"
					data-testid="video-dialog-textbox"
				>
			`;
		});
	}

	getStatusContainer(): HTMLElement
	{
		return this.#refs.remember('status', () => {
			return Tag.render`
				<div class="ui-text-editor-video-dialog-status">${Loc.getMessage('TEXT_EDITOR_VIDEO_INSERT_HINT')}</div>
			`;
		});
	}

	showError(error: string)
	{
		Dom.addClass(this.getStatusContainer(), '--error');
		Dom.addClass(this.getUrlTextBox().parentNode, 'ui-ctl-warning');

		if (Type.isStringFilled(error))
		{
			this.getStatusContainer().textContent = error;
		}
	}

	clearError()
	{
		Dom.removeClass(this.getStatusContainer(), '--error');
		Dom.removeClass(this.getUrlTextBox().parentNode, 'ui-ctl-warning');
		this.getStatusContainer().textContent = Loc.getMessage('TEXT_EDITOR_VIDEO_INSERT_HINT');
	}

	#handleSaveBtnClick(): void
	{
		const url: string = this.getUrlTextBox().value.trim();
		if (url.length > 0)
		{
			this.setVideoUrl(url);
			this.emit('onSave');
		}
		else
		{
			this.getUrlTextBox().focus();
		}
	}

	#handleTextBoxKeyDown(event: KeyboardEvent)
	{
		if (event.key === 'Enter')
		{
			event.preventDefault();
			this.#handleSaveBtnClick();
		}
	}

	#handleTextBoxInput(event: KeyboardEvent)
	{
		this.emit('onInput');
	}

	#handleCancelBtnClick(): void
	{
		this.emit('onCancel');
	}
}
