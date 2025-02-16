import { Loc, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Icon, Main as MainIconSet, Set } from 'ui.icon-set.api.core';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';

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
	videoContainerMinHeight: number;
	width?: number;
	title?: string;
	text?: string;
	icon?: string;
	colors?: PromoVideoPopupOptionsColors;
	targetOptions: PromoVideoPopupTargetOptions;
	angleOptions?: PromoVideoPopupAngleOptions;
	offset?: PromoVideoPopupOffset;
	button?: PromoVideoPopupButtonOptions;
	useOverlay?: boolean;
}

export const PromoVideoPopupButtonPosition = Object.freeze({
	LEFT: 'left',
	RIGHT: 'right',
	CENTER: 'center',
});

export type PromoVideoPopupButtonOptions = {
	color?: ButtonColor;
	text?: string;
	size?: ButtonSize;
	position: PromoVideoPopupButtonPosition.LEFT
		| PromoVideoPopupButtonPosition.RIGHT
		| PromoVideoPopupButtonPosition.CENTER;
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
	/**
	 * @deprecated Use button option from PromoVideoPopupOptions instead
	 */
	button: ButtonColor,
}

export class PromoVideoPopup extends EventEmitter
{
	#videoSrc: string;
	#title: string;
	#width: number;
	#text: string;
	#icon: string;
	#colors: PromoVideoPopupOptionsColors;
	#targetOptions: ?PromoVideoPopupTargetOptions;
	#angleOptions: PromoVideoPopupAngleOptions;
	#offset: ?PromoVideoPopupOffset;
	#videoContainerMinHeight: number = 255;
	#buttonOptions: PromoVideoPopupButtonOptions = null;
	#useOverlay: boolean;

	#popup: ?Popup;

	constructor(options: PromoVideoPopupOptions)
	{
		super(options);
		this.setEventNamespace('UI.PromoVideoPopup');

		this.#validateOptions(options);

		this.#videoSrc = options.videoSrc;
		this.#title = options.title;
		this.#width = options.width ?? PromoVideoPopup.getWidth();
		this.#text = options.text;
		this.#icon = this.#isIconExist(options.icon) ? options.icon : MainIconSet.B_24;
		this.#colors = options.colors;
		this.#targetOptions = options.targetOptions ?? null;
		this.#angleOptions = options.angleOptions || false;
		this.#offset = options.offset;
		this.#videoContainerMinHeight = options.videoContainerMinHeight;
		this.#buttonOptions = options.button ?? null;
		this.#useOverlay = options.useOverlay === true;
	}

	/**
	 * @deprecated
	 */
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

	getWidth(): number
	{
		return this.#width;
	}

	setTargetOptions(targetOptions: PromoVideoPopupTargetOptions): this
	{
		this.#targetOptions = targetOptions;

		if (this.#popup)
		{
			this.#popup.setBindElement(targetOptions);
		}

		return this;
	}

	#iniPopup(): void
	{
		const styles = getComputedStyle(document.body);
		const backgroundPrimary = styles.getPropertyValue('--ui-color-background-primary');
		const backgroundPrimaryRgb = styles.getPropertyValue('--ui-color-background-primary-rgb');

		this.#popup = new Popup({
			bindElement: this.#targetOptions,
			cacheable: false,
			width: this.#width,
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
			overlay: this.#getPopupOverlay(),
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
				<div
					class="ui__promo-video-popup-content_promo-video-wrapper"
					style="min-height: ${`${this.#videoContainerMinHeight}px`}"
				>
					${this.#renderVideo()}
				</div>
				<div class="${this.#getPopupFooterElementClassname()}">
					${this.#renderAcceptButton()}
				</div>
			</div>
		`;
	}

	#renderVideo(): HTMLElement
	{
		const videoElement = Tag.render`
			<video
				src="${this.#videoSrc}"
				autoplay
				preload
				loop
				class="ui__promo-video-popup-content_promo-video"
			></video>
		`;

		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-events-binding
		videoElement.addEventListener('canplay', () => {
			videoElement.muted = true;
			videoElement.play();
		});

		return videoElement;
	}

	#renderAcceptButton(): HTMLElement
	{
		const buttonOptions = this.#getButtonOptions();

		const btn = new Button({
			color: buttonOptions.color,
			text: buttonOptions.text,
			size: buttonOptions.size,
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
		const videoContainerMinHeight = options?.videoContainerMinHeight;

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

		if (videoContainerMinHeight && Type.isNumber(videoContainerMinHeight) === false)
		{
			throw new TypeError('UI.PromoVideoPopup: videoContainerMinHeight option must be number');
		}
	}

	#isIconExist(icon: string): boolean
	{
		return Object.values(Set).includes(icon);
	}

	#getButtonOptions(): PromoVideoPopupButtonOptions
	{
		const defaultOptions = this.#getDefaultButtonOptions();

		return {
			text: this.#buttonOptions?.text ?? defaultOptions.text,
			color: this.#buttonOptions?.color ?? defaultOptions.color,
			size: this.#buttonOptions?.size ?? defaultOptions.size,
			position: this.#buttonOptions?.position ?? defaultOptions.position,
		};
	}

	#getDefaultButtonOptions(): PromoVideoPopupButtonOptions
	{
		return {
			text: Loc.getMessage('PROMO_VIDEO_POPUP_ACCEPT'),
			size: ButtonSize.SMALL,
			color: this.#getOptionsButtonColor() || ButtonColor.PRIMARY,
			position: PromoVideoPopupButtonPosition.LEFT,
		};
	}

	#getPopupOverlay(): { backgroundColor: string } | false
	{
		return this.#useOverlay ? { backgroundColor: 'rgba(0, 0, 0, 0.4)' } : false;
	}

	#getPopupFooterElementClassname(): string
	{
		let buttonAlignModifier = '';

		if (this.#getButtonOptions().position === PromoVideoPopupButtonPosition.CENTER)
		{
			buttonAlignModifier = '--align-center';
		}

		if (this.#getButtonOptions().position === PromoVideoPopupButtonPosition.RIGHT)
		{
			buttonAlignModifier = '--align-right';
		}

		return `ui__promo-video-popup-content_footer ${buttonAlignModifier}`;
	}
}
