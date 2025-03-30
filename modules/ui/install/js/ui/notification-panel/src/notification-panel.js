import { Type, Dom, Tag, Text } from 'main.core';
import { Icon } from 'ui.icon-set.api.core';
import { EventEmitter } from 'main.core.events';
import { Popup, PopupWindowManager } from 'main.popup';
import 'ui.design-tokens';
import './style.css';

export type NotificationPanelOptions = {
	id: ?string,
	styleClass: ?string,
	content: string | HTMLElement,
	textColor: ?string,
	crossColor: ?string,
	backgroundColor: ?string,
	leftIcon: ?Icon,
	rightButtons: ?Array,
	showCloseIcon: ?boolean,
	zIndex: ?number,
	events: ?{
		onShow: ?func,
		onHide: ?func,
		onHideByButton: ?func,
	},
};

export class NotificationPanel extends EventEmitter
{
	#panel: ?HTMLElement = null;
	#popup: ?Popup = null;
	#container: HTMLElement;
	options: NotificationPanelOptions;

	constructor(options: NotificationPanelOptions)
	{
		super();
		this.setEventNamespace('UI:NotificationPanel');
		this.options = { ...this.getDefaultOptions(), ...Type.isPlainObject(options) ? options : {} };
		this.options.id ??= Text.getRandom();
	}

	getDefaultOptions(): NotificationPanelOptions
	{
		return {
			styleClass: '',
			backgroundColor: '#F2FEE2',
			textColor: null,
			crossColor: null,
			leftIcon: null,
			rightButtons: [],
			showCloseIcon: true,
			zIndex: null,
			events: {},
		};
	}

	getContainer(): HTMLElement
	{
		if (this.#container)
		{
			return this.#container;
		}

		this.#container = Tag.render`
			<div class="ui-notification-panel__container">
				${this.getContent()}
				${this.getFooter()}
			</div>
		`;

		return this.#container;
	}

	getButtonsContainer(buttons): HTMLElement
	{
		const buttonsContainer = Tag.render`<div class="ui-notification-panel__buttons-container"></div>`;

		buttons.forEach((button) => {
			button.renderTo(buttonsContainer);
		});

		return buttonsContainer;
	}

	getContent(): HTMLElement
	{
		const content = Tag.render`<div class="ui-notification-panel__content"></div>`;

		if (this.options.leftIcon)
		{
			this.options.leftIcon.size = 28;
			this.options.leftIcon.renderTo(content);
			Dom.append(
				Tag.render`<div class="ui-notification-panel__left-icon-divider"></div>`,
				content,
			);
		}

		if (Type.isElementNode(this.options.content))
		{
			Dom.append(this.options.content, content);
		}
		else if (Type.isString(this.options.content))
		{
			const textColor = this.options.textColor;
			Dom.append(
				Tag.render`<div class="ui-notification-panel__text" ${textColor ? 'style="color: ' + textColor + '"' : ''}>${this.options.content}</div>`,
				content,
			);
		}

		Dom.append(this.getFooter(), content);

		return content;
	}

	getFooter(): HTMLElement
	{
		const footer = Tag.render`<div class="ui-notification-panel__footer"></div>`;

		if (this.options.rightButtons)
		{
			Dom.append(this.getButtonsContainer(this.options.rightButtons), footer);
		}

		if (this.options.showCloseIcon)
		{
			Dom.append(this.getCloseButton(), footer);
		}

		return footer;
	}

	getCloseButton(): HTMLElement
	{
		const crossColor = this.options.crossColor;
		return Tag.render`
			<div 
				class="ui-notification-panel__close-button ui-icon-set --cross-45"
				onclick="${this.hideByButton.bind(this)}"
				${crossColor ? 'style="--ui-icon-set__icon-color: ' + crossColor + '"' : ''}
			>
			</div>
		`;
	}

	getPopup(): Popup
	{
		this.#popup ??= PopupWindowManager.create({
			id: this.options.id,
			content: this.getContent(),
			background: this.options.backgroundColor,
			targetContainer: this.#panel,
			className: 'ui-notification-panel__container',
			animation: {
				showClassName: 'ui-notification-panel__show',
				closeClassName: 'ui-notification-panel__hide',
				closeAnimationType: 'transition',
			},
			events: {
				onShow: this.#handlePopupShow.bind(this),
				onClose: this.#handlePopupClose.bind(this),
			},
			zIndexOptions: {
				alwaysOnTop: true,
			},
		});

		return this.#popup;
	}

	show(): void
	{
		if (!this.#panel)
		{
			this.#createPanel();
		}

		const popup = this.getPopup();
		popup.show();
		if (this.options.zIndex)
		{
			popup.getZIndexComponent().setZIndex(this.options.zIndex);
		}
	}

	hide(): void
	{
		this.getPopup().close();
	}

	hideByButton(): void
	{
		this.options.events?.onHideByButton?.();
		this.emit('onHideByButton');
		this.hide();
	}

	#createPanel(): void
	{
		this.#panel = Tag.render`<div class="ui-notification-panel ${this.options.styleClass}" id="notification-panel-${this.options.id}"></div>`;
		let mainTable = document.body.querySelector('.bx-layout-table');
		if (!mainTable)
		{
			mainTable = document.body.querySelector('.ui-slider-page');
		}
		Dom.insertBefore(this.#panel, mainTable);
	}

	#handlePopupShow(): void
	{
		this.options.events?.onShow?.();
		this.emit('onShow');
	}

	#handlePopupClose(): void
	{
		this.options.events?.onHide?.();
		this.emit('onHide');
	}
}
