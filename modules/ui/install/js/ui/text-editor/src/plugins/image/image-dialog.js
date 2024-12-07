import { Tag, Type, Loc } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { Popup, PopupTarget, PopupTargetOptions } from 'main.popup';
import { sanitizeUrl } from '../../helpers/sanitize-url';

import './image-dialog.css';

export type ImageDialogOptions = {
	targetContainer?: HTMLElement,
	events?: Object<string, (event: BaseEvent) => {}>,
}

export default class ImageDialog extends EventEmitter
{
	#popup: Popup = null;
	#imageUrl: string = '';
	#targetContainer: HTMLElement = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	constructor(options: ImageDialogOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.ImageDialog');

		const imageDialogOptions: ImageDialogOptions = Type.isPlainObject(options) ? options : {};

		this.setTargetContainer(imageDialogOptions.targetContainer);
		this.subscribeFromOptions(imageDialogOptions.events);
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

	setImageUrl(url: string): void
	{
		if (Type.isString(url))
		{
			this.#imageUrl = sanitizeUrl(url);
		}
	}

	getImageUrl(): string
	{
		return this.#imageUrl;
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
					onClose: () => {
						this.emit('onClose');
					},
					onDestroy: () => {
						this.emit('onDestroy');
					},
					onShow: () => {
						this.emit('onShow');
					},
					onAfterShow: () => {
						this.emit('onAfterShow');
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
				<div class="ui-text-editor-image-dialog">
					<div class="ui-text-editor-image-dialog-form">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-s ui-ctl-inline ui-ctl-w100 ui-text-editor-image-dialog-textbox">
							<div class="ui-ctl-tag">${Loc.getMessage('TEXT_EDITOR_IMAGE_URL')}</div>
							${this.getUrlTextBox()}
						</div>
						<button type="button" 
							class="ui-text-editor-image-dialog-button" 
							onclick="${this.#handleSaveBtnClick.bind(this)}"
							data-testid="image-dialog-save-btn"
						>
							<span class="ui-icon-set --check"></span>
						</button>
						<button 
							type="button" 
							class="ui-text-editor-image-dialog-button"
							onclick="${this.#handleCancelBtnClick.bind(this)}"
							data-testid="image-dialog-cancel-btn"
						>
							<span class="ui-icon-set --cross-60"></span>
						</button>
					</div>
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
					placeholder="https://example.com/image.jpeg"
					value="${this.getImageUrl()}"
					onkeydown="${this.#handleTextBoxKeyDown.bind(this)}"
					data-testid="image-dialog-textbox"
				>
			`;
		});
	}

	#handleSaveBtnClick(): void
	{
		const url: string = this.getUrlTextBox().value.trim();
		if (url.length > 0)
		{
			this.setImageUrl(url);
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

	#handleCancelBtnClick(): void
	{
		this.emit('onCancel');
	}
}
