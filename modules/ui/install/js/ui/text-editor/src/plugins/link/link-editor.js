import { Dom, Tag, Type, Loc } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { Popup, PopupTargetOptions, PopupTarget } from 'main.popup';

import './link-editor.css';

import { sanitizeUrl } from '../../helpers/sanitize-url';

export type LinkEditorOptions = {
	editMode?: boolean,
	autoLinkMode?: boolean,
	linkUrl?: string,
	targetContainer?: HTMLElement,
	events?: Object<string, (event: BaseEvent) => {}>,
}

export class LinkEditor extends EventEmitter
{
	#popup: Popup = null;
	#editMode: boolean = null;
	#autoLinkMode: boolean = null;
	#linkUrl: string = '';
	#targetContainer: HTMLElement = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	constructor(options: LinkEditorOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.LinkEditor');

		const linkEditorOptions: LinkEditorOptions = Type.isPlainObject(options) ? options : {};

		this.setTargetContainer(linkEditorOptions.targetContainer);
		this.setLinkUrl(linkEditorOptions.linkUrl);

		if (Type.isBoolean(linkEditorOptions.editMode))
		{
			this.setEditMode(linkEditorOptions.editMode);
		}
		else
		{
			this.setEditMode(this.#linkUrl === '');
		}

		this.setAutoLinkMode(options.autoLinkMode);

		this.subscribeFromOptions(linkEditorOptions.events);
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

	isShown(): boolean
	{
		return this.#popup !== null && this.#popup.isShown();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	setAutoLinkMode(autoLinkMode: boolean = true): void
	{
		if (autoLinkMode === this.#autoLinkMode)
		{
			return;
		}

		if (autoLinkMode)
		{
			Dom.addClass(this.getContainer(), '--auto-link-mode');
		}
		else
		{
			Dom.removeClass(this.getContainer(), '--auto-link-mode');
		}

		if (this.#popup !== null)
		{
			this.#popup.adjustPosition();
		}

		this.#autoLinkMode = autoLinkMode;
	}

	setEditMode(editMode: boolean = true): void
	{
		if (editMode === this.#editMode)
		{
			return;
		}

		if (editMode)
		{
			Dom.addClass(this.getContainer(), '--edit-mode');
		}
		else
		{
			Dom.removeClass(this.getContainer(), '--edit-mode');
		}

		if (this.#popup !== null)
		{
			this.#popup.adjustPosition();
		}

		this.#editMode = editMode;
	}

	setLinkUrl(url: string): void
	{
		if (Type.isString(url))
		{
			this.#linkUrl = sanitizeUrl(url);

			this.getLinkTextBox().value = this.#linkUrl;
			this.getLinkLabel().textContent = this.#linkUrl;
			this.getLinkLabel().href = this.#linkUrl;
		}
	}

	getLinkUrl(): string
	{
		return this.#linkUrl;
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
						if (this.#editMode)
						{
							this.getLinkTextBox().focus();
						}
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
				<div class="ui-text-editor-link-editor">
					<div class="ui-text-editor-link-form">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-s ui-ctl-inline ui-ctl-w100 ui-text-editor-link-textbox">
							<div class="ui-ctl-tag">${Loc.getMessage('TEXT_EDITOR_LINK_URL')}</div>
							${this.getLinkTextBox()}
						</div>
						<button type="button" 
							class="ui-text-editor-link-form-button" 
							onclick="${this.#handleSaveBtnClick.bind(this)}"
							data-testid="save-link-btn"
						>
							<span class="ui-icon-set --check"></span>
						</button>
						<button 
							type="button" 
							class="ui-text-editor-link-form-button"
							onclick="${this.#handleCancelBtnClick.bind(this)}"
							data-testid="cancel-link-btn"
						>
							<span class="ui-icon-set --cross-60"></span>
						</button>
					</div>
					<div class="ui-text-editor-link-preview">
						${this.getLinkLabel()}
						<button 
							type="button" 
							class="ui-text-editor-link-form-button"
							onclick="${this.#handleEditBtnClick.bind(this)}"
							data-testid="edit-link-btn"
						>
							<span class="ui-icon-set --pencil-60"></span>
						</button>
						<button 
							type="button" 
							class="ui-text-editor-link-form-button ui-text-editor-link-form-delete-btn"
							onclick="${this.#handleUnlinkBtnClick.bind(this)}"
							data-testid="unlink-btn"
						>
							<span class="ui-icon-set --delete-hyperlink"></span>
						</button>
					</div>
				</div>
			`;
		});
	}

	getLinkTextBox(): HTMLInputElement
	{
		return this.#refs.remember('link-textbox', () => {
			return Tag.render`
				<input 
					type="text"
					class="ui-ctl-element"
					placeholder="https://"
					value="${this.getLinkUrl()}"
					onkeydown="${this.#handleLinkTextBoxKeyDown.bind(this)}"
					data-testid="link-textbox-input"
				>
			`;
		});
	}

	getLinkLabel(): HTMLAnchorElement
	{
		return this.#refs.remember('link-label', () => {
			return Tag.render`
				<a href="" target="_blank" class="ui-text-editor-link-label"></a>
			`;
		});
	}

	#handleSaveBtnClick(): void
	{
		const url: string = this.getLinkTextBox().value.trim();
		if (url.length > 0)
		{
			this.setLinkUrl(url);
			this.emit('onSave');
		}
		else
		{
			this.getLinkTextBox().focus();
		}
	}

	#handleLinkTextBoxKeyDown(event: KeyboardEvent)
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

	#handleEditBtnClick(): void
	{
		this.setEditMode(true);
		this.getLinkTextBox().focus();
		this.getLinkTextBox().select();
	}

	#handleUnlinkBtnClick(): void
	{
		this.emit('onUnlink');
	}
}
