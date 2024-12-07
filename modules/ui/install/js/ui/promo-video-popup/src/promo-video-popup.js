import { Tag, Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Icon, Main as MainIconSet, Set } from 'ui.icon-set.api.core';
import { Button, ButtonColor } from 'ui.buttons';

import 'ui.icon-set.main';

import './promo-video-popup.css';

export const AnglePosition = Object.freeze({
	TOP: 'top',
	LEFT: 'left',
	BOTTOM: 'bottom',
	RIGHT: 'right',
});

export const PromoVideoPopupEvents = Object.freeze({
	ACCEPT: 'accept',
	HIDE: 'hide',
});

export type PromoVideoPopupOptions = {
	videoSrc: string;
	title?: string;
	text?: string;
	icon?: string;
	colors?: PromoVideoPopupOptionsColors;
	targetOptions: PromoVideoPopupTargetOptions;
	angleOptions?: PromoVideoPopupAngleOptions;
	offset?: PromoVideoPopupOffset;
}

type PromoVideoPopupOffset = {
	top: number;
	left: number;
}

type PromoVideoPopupTargetOptions = HTMLElement | { top: number, left: number };

type PromoVideoPopupAngleOptions = {
	position: AnglePosition.TOP | AnglePosition.BOTTOM | AnglePosition.LEFT | AnglePosition.RIGHT;
	offset?: number;
}

type PromoVideoPopupOptionsColors = {
	iconBackground: string;
	title: string;
	button: ButtonColor,
}

export class PromoVideoPopup extends EventEmitter
{
	#videoSrc: string;
	#title: string;
	#text: string;
	#icon: string;
	#colors: PromoVideoPopupOptionsColors;
	#targetOptions: PromoVideoPopupTargetOptions;
	#angleOptions: PromoVideoPopupAngleOptions;
	#offset: ?PromoVideoPopupOffset;

	#popup: ?Popup;

	constructor(options: PromoVideoPopupOptions)
	{
		super(options);
		this.setEventNamespace('UI.PromoVideoPopup');

		this.#validateOptions(options);

		this.#videoSrc = options.videoSrc;
		this.#title = options.title;
		this.#text = options.text;
		this.#icon = this.#isIconExist(options.icon) ? options.icon : MainIconSet.B_24;
		this.#colors = options.colors;
		this.#targetOptions = options.targetOptions;
		this.#angleOptions = options.angleOptions || false;
		this.#offset = options.offset;
	}

	static getWidth(): number
	{
		return 498;
	}

	show(): void
	{
		if (!this.#popup)
		{
			this.#iniPopup();
		}

		if (this.#popup.isShown())
		{
			return;
		}

		this.#popup.show();
	}

	hide(): void
	{
		this.#popup?.close();
	}

	isShown(): boolean
	{
		return Boolean(this.#popup?.isShown());
	}

	adjustPosition(): void
	{
		this.#popup?.adjustPosition({
			forceBindPosition: true,
		});
	}

	#iniPopup(): void
	{
		const styles = getComputedStyle(document.body);
		const backgroundPrimary = styles.getPropertyValue('--ui-color-background-primary');
		const backgroundPrimaryRgb = styles.getPropertyValue('--ui-color-background-primary-rgb');

		this.#popup = new Popup({
			bindElement: this.#targetOptions,
			cacheable: false,
			width: PromoVideoPopup.getWidth(),
			borderRadius: '16px',
			angle: this.#angleOptions,
			content: this.#renderPopupContent(),
			closeByEsc: true,
			autoHide: true,
			closeIcon: true,
			background: `rgba(${backgroundPrimaryRgb}, 0.5)`,
			contentBackground: backgroundPrimary,
			contentPadding: 12,
			contentBorderRadius: '8px',
			className: this.#getPopupClassname(),
			events: {
				onPopupClose: () => {
					setTimeout(() => {
						this.emit(PromoVideoPopupEvents.HIDE);
						this.#popup.destroy();
						this.#popup = null;
					}, 300);
				},
			},
			animation: {
				showClassName: '--show',
				closeClassName: this.#getAnimationCloseClassName(),
				closeAnimationType: 'animation',
			},
		});

		this.#popup.setOffset({
			offsetTop: this.#offset?.top,
			offsetLeft: this.#offset?.left,
		});
	}

	#getPopupClassname(): string
	{
		let classNames = ['ui__promo-video-popup'];

		if (this.#angleOptions?.position === AnglePosition.RIGHT)
		{
			classNames = [...classNames, '--from-right'];
		}

		if (this.#angleOptions?.position === AnglePosition.TOP)
		{
			classNames = [...classNames, '--from-top'];
		}

		return classNames.join(' ');
	}

	#getAnimationCloseClassName(): string
	{
		switch (this.#angleOptions?.position)
		{
			case AnglePosition.RIGHT:
			{
				return '--close-left';
			}

			case AnglePosition.TOP:
			{
				return '--close-bottom';
			}

			default:
			{
				return '--close';
			}
		}
	}

	#renderPopupContent(): HTMLElement
	{
		return Tag.render`
			<div
				class="ui__promo-video-popup-content"
				style="${this.#getPopupContentVariablesStyles()}"
			>
				<div class="ui__promo-video-popup-content_header">
					<div class="ui__promo-video-popup-content_header-icon">
						${this.#renderIcon()}
					</div>
					<div class="ui__promo-video-popup-content_header-title">
						${this.#title}
					</div>
				</div>
				<div class="ui__promo-video-popup-content_promo-text">
					${this.#text}
				</div>
				<div class="ui__promo-video-popup-content_promo-video-wrapper">
					<video
						src="${this.#videoSrc}"
						autoplay
						preload
						loop
						class="ui__promo-video-popup-content_promo-video"
					></video>
				</div>
				<div class="ui__promo-video-popup-content_footer">
					${this.#renderAcceptButton()}
				</div>
			</div>
		`;
	}

	#renderAcceptButton(): HTMLElement
	{
		const color = this.#getOptionsButtonColor() || ButtonColor.PRIMARY;

		const btn = new Button({
			color,
			text: Loc.getMessage('PROMO_VIDEO_POPUP_ACCEPT'),
			size: Button.Size.SMALL,
			round: true,
			onclick: () => {
				this.emit(PromoVideoPopupEvents.ACCEPT);
			},
		});

		return btn.render();
	}

	#renderIcon(): HTMLElement
	{
		const color = getComputedStyle(document.body).getPropertyValue('--ui-color-on-primary');

		const icon = new Icon({
			color,
			size: 18,
			icon: this.#icon,
		});

		return icon.render();
	}

	#getPopupContentVariablesStyles(): string
	{
		const cssVariables = {};

		if (this.#getOptionsTitleColor())
		{
			cssVariables['--ui__promo-video-popup_title-color'] = this.#getOptionsTitleColor();
		}

		if (this.#getOptionsIconColor())
		{
			cssVariables['--ui__promo-video-popup_icon-color'] = this.#getOptionsIconColor();
		}

		return Object.entries(cssVariables).map(([variable, value]) => {
			return `${variable}: ${value}`;
		}).join(';');
	}

	#getOptionsTitleColor(): string
	{
		return this.#colors?.title;
	}

	#getOptionsIconColor(): string
	{
		return this.#colors?.iconBackground;
	}

	#getOptionsButtonColor(): ?string
	{
		return this.#colors?.button;
	}

	// eslint-disable-next-line sonarjs/cognitive-complexity
	#validateOptions(options: PromoVideoPopupOptions): void
	{
		const title = options?.title;
		const text = options?.text;
		const video = options?.videoSrc;
		const iconColor = options?.colors?.iconBackground;
		const titleColor = options?.colors?.title;
		const buttonColor = options?.colors?.button;
		const targetOptions = options?.targetOptions;
		const offset = options?.offset;

		if (!options)
		{
			throw new TypeError('UI.PromoVideoPopup: options are required for constructor');
		}

		if (!targetOptions)
		{
			throw new Error('UI.PromoVideoPopup: targetOptions is required option');
		}

		if (title && Type.isString(title) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: title option must be string');
		}

		if (iconColor && Type.isStringFilled(iconColor) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: colors.icon option must be string');
		}

		if (titleColor && Type.isStringFilled(titleColor) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: colors.title option must be string');
		}

		if (buttonColor && Object.values(ButtonColor).includes(buttonColor) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: colors.button option must be from ButtonColor from ui.buttons');
		}

		if (text && Type.isString(text) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: description option must be string');
		}

		if (!video)
		{
			throw new Error('UI.PromoVideoPopup: videoSrc is required option');
		}

		if (video && Type.isStringFilled(video) === false)
		{
			throw new Error('UI.PromoVideoPopup: videoSrc must be string');
		}

		if (offset && Type.isPlainObject(offset) === false)
		{
			throw new Error('UI.PromoVideoPopup: offset options must be plain object with top and left properties');
		}

		if (offset?.top && Type.isNumber(offset?.top) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: offset.top option must be number');
		}

		if (offset?.left && Type.isNumber(offset?.left) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: offset.left option must be number');
		}
	}

	#isIconExist(icon: string): boolean
	{
		return Object.values(Set).includes(icon);
	}
}
