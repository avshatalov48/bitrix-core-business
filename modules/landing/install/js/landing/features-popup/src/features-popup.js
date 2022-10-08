import {EventEmitter, BaseEvent} from 'main.core.events';
import {Cache, Dom, Event, Tag, Text, Type} from 'main.core';
import {Popup} from 'main.popup';
import {PageObject} from 'landing.pageobject';

import 'ui.fonts.opensans';
import './css/style.css';

type FeaturesPopupItemOptions = {
	id: string,
	title?: string,
	theme?: string,
	icon?: {
		className: string,
		onClick: (event: BaseEvent) => void,
	},
	link?: {
		label: string,
		onClick: (event: BaseEvent) => void,
	},
	actionButton?: {
		label: string,
		onClick: (event: BaseEvent) => void,
	},
	onClick?: (event: BaseEvent) => void,
};

type FeaturesPopupOptions = {
	bindElement: HTMLElement,
	items: Array<FeaturesPopupItemOptions>,
	events: {
		onShow: (event: BaseEvent) => void,
		onClose: (event: BaseEvent) => void,
	},
};

export class FeaturesPopup extends EventEmitter
{
	#cache = new Cache.MemoryCache();

	static Themes = {
		Highlight: 'highlight',
	};

	constructor(options: FeaturesPopupOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.FeaturesPopup');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		Event.bind(PageObject.getEditorWindow().document, 'click', () => {
			this.hide();
		});
	}

	setOptions(options: FeaturesPopupOptions)
	{
		this.#cache.set('options', {...options});
	}

	getOptions(): FeaturesPopupOptions
	{
		return this.#cache.get('options', {});
	}

	#getPopup(): Popup
	{
		return this.#cache.remember('popup', () => {
			return new Popup({
				id: `landing-features-popup-${Text.getRandom()}`,
				bindElement: this.getOptions().bindElement,
				content: this.#getContent(),
				className: 'landing-features-popup',
				width: 410,
				autoHide: true,
				closeByEsc: true,
				noAllPaddings : true,
				angle: {
					position: 'top',
					offset: 115
				},
				minWidth: 410,
				contentBackground: 'transparent',
				background: '#E9EAED',
			});
		});
	}

	show(): void
	{
		this.#getPopup().show();
		this.emit('onShow');
	}

	hide(): void
	{
		this.#getPopup().close();
		this.emit('onClose');
	}

	isShown(): boolean
	{
		return this.#getPopup().isShown();
	}

	#getContent(): HTMLDivElement
	{
		return this.#cache.remember('content', () => {
			return Tag.render`
				<div class="landing-features-popup-content">
					${this.getOptions().items.map((options) => {
						return FeaturesPopup.createRow(options);	
					})}
				</div>
			`;
		});
	}

	static createContentBlock(options: FeaturesPopupItemOptions | Array<FeaturesPopupItemOptions>): HTMLDivElement | Array<HTMLDivElement>
	{
		if (Type.isArray(options))
		{
			return options.map((optionsItem) => {
				return FeaturesPopup.createContentBlock(optionsItem);
			});
		}

		const getTitle = () => {
			if (Type.isStringFilled(options.title))
			{
				return Tag.render`
					<div class="landing-features-popup-content-block-text-title">
						${Text.encode(options.title)}
					</div>
				`;
			}

			return '';
		};

		const getLink = () => {
			if (
				Type.isPlainObject(options.link)
				&& Type.isStringFilled(options.link.label)
				&& Type.isFunction(options.link.onClick)
			)
			{
				return Tag.render`
					<div 
						class="landing-features-popup-content-block-text-link"
						onclick="${options.link.onClick}"
					>
						${Text.encode(options.link.label)}
					</div>
				`;
			}

			return '';
		};

		const getActionButton = () => {
			if (
				Type.isPlainObject(options.actionButton)
				&& Type.isStringFilled(options.actionButton.label)
				&& Type.isFunction(options.actionButton.onClick)
			)
			{
				return Tag.render`
					<div class="landing-features-popup-content-block-action">
						<span 
							class="ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border"
							onclick="${options.actionButton.onClick}"
						>${Text.encode(options.actionButton.label)}</span>
					</div>
				`;
			}

			return '';
		};

		const getTextBlock = () => {
			const title = getTitle();
			const link = getLink();
			if (title || link)
			{
				return Tag.render`
					<div class="landing-features-popup-content-block-text">
						${getTitle()}
						${getLink()}
					</div>
				`;
			}

			return '';
		};

		const getIcon = () => {
			if (Type.isPlainObject(options.icon))
			{
				return Tag.render`
					<div class="landing-features-popup-content-block-icon">
						<div class="ui-icon ui-icon-md ${options.icon.className}">
							<i></i>
						</div>
					</div>
				`;
			}

			return '';
		};

		const blockClass = (() => {
			let result = '';
			if (Type.isFunction(options.onClick))
			{
				result += ' landing-features-popup-content-block-clickable';
			}

			if (Type.isStringFilled(options.theme))
			{
				result += ` landing-features-popup-content-block-theme-${options.theme}`;
			}

			return result;
		})();

		const block = Tag.render`
			<div 
				class="landing-features-popup-content-block${blockClass}"
				data-id="${Text.encode(options.id || Text.getRandom())}"
			>
				${getIcon()}
				${getTextBlock()}
				${getActionButton()}
			</div>
		`;

		if (Type.isFunction(options.onClick))
		{
			Event.bind(block, 'click', options.onClick);
		}

		if (Type.isStringFilled(options.backgroundColor))
		{
			Dom.style(block, 'background-color', options.backgroundColor);
		}

		return block;
	}

	static createRow(options: FeaturesPopupItemOptions): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-features-popup-content-row">
				${FeaturesPopup.createContentBlock(options)}
			</div>
		`;
	}
}