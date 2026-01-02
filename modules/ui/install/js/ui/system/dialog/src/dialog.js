import { bind, Dom, Tag, Type, Event } from 'main.core';
import { Popup } from 'main.popup';
import { Headline, Text } from 'ui.system.typography';
import { Outline, Icon, IconHoverMode } from 'ui.icon-set.api.core';
import 'ui.icon-set.outline';

import type { DialogAnglePositon, DialogEvents, DialogStickPosition } from './type';
import { DialogAnglePositions, DialogBackground, aliases } from './const';
import { getClosestZIndexElement } from './helpers/get-closest-z-index';

import './css/dialog.css';

type DialogBindElement = Element | { top: number; left: number; };

export type DialogOptions = {
	title?: string;
	subtitle?: string;
	hasCloseButton?: boolean;
	content: HTMLElement;
	leftButtons?: Button[];
	centerButtons?: Button[];
	rightButtons?: Button[];
	hasVerticalPadding?: boolean;
	hasHorizontalPadding?: boolean;
	events?: DialogEvents;
	hasOverlay?: boolean;
	disableScrolling?: boolean;
	closeByEsc?: boolean;
	closeByClickOutside?: boolean;
	background?: DialogBackground;

	// stickPosition: ?DialogStickPosition;
	// bindElement?: DialogBindElement;
	// anglePosition?: DialogAnglePositon;
};

export class Dialog extends Event.EventEmitter
{
	#title: ?string;
	#subtitle: ?string;
	#hasCloseButton: ?boolean;
	#content: ?HTMLElement;
	#leftButtons: Button[] = [];
	#rightButtons: Button[] = [];
	#centerButtons: Button[] = [];
	#hasVerticalPadding: ?boolean;
	#hasHorizontalPadding: ?boolean;
	#hasOverlay: ?boolean;
	#width: ?number;
	#disableScrolling: boolean;
	#closeByClickOutside: ?boolean;
	#closeByEsc: ?boolean;
	#dialogBackground: DialogBackground;

	#stickPosition: ?DialogStickPosition;
	#bindElement: ?DialogBindElement;
	#anglePosition: ?DialogAnglePositon;

	#popup: ?Popup;

	constructor(options: DialogOptions = {})
	{
		super(options);

		this.setEventNamespace('UI.System.Dialog');
		this.subscribeFromOptions(options.events);

		this.#title = options.title;
		this.#subtitle = options.subtitle;
		this.#hasCloseButton = Type.isBoolean(options.hasCloseButton) ? options.hasCloseButton : true;
		this.#content = options.content;
		this.#leftButtons = options.leftButtons ?? [];
		this.#centerButtons = options.centerButtons ?? [];
		this.#rightButtons = options.rightButtons ?? [];
		this.#hasHorizontalPadding = options.hasHorizontalPadding ?? true;
		this.#hasVerticalPadding = options.hasVerticalPadding ?? true;
		this.#hasOverlay = options.hasOverlay === true;
		this.#disableScrolling = options.disableScrolling;
		this.#closeByClickOutside = options.closeByClickOutside ?? true;
		this.#closeByEsc = options.closeByEsc ?? true;
		this.#width = options.width;
		this.#dialogBackground = options.background || DialogBackground.default;

		// this.#stickPosition = options.stickPosition;
		// this.#anglePosition = options.anglePosition;
		// this.#bindElement = options.bindElement;
	}

	static show(options?: DialogOptions): void
	{
		const dialog = new Dialog(options);

		dialog.show();
	}

	subscribeFromOptions(events)
	{
		super.subscribeFromOptions(events, aliases);
	}

	show(): void
	{
		this.#getPopup().show();
	}

	hide(): void
	{
		this.#getPopup().close();
	}

	setTitle(title: string): void
	{
		this.#title = title;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	setSubtitle(subtitle: string): void
	{
		this.#subtitle = subtitle;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	setContent(content: HTMLElement): void
	{
		this.#content = content;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	setLeftButtons(buttons: Button[]): void
	{
		this.#leftButtons = buttons;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	setCenterButtons(buttons: Button[]): void
	{
		this.#centerButtons = buttons;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	setRightButtons(buttons: Button[]): void
	{
		this.#rightButtons = buttons;

		if (this.#popup)
		{
			this.#updatePopupContent();
		}
	}

	#createPopup(): Popup
	{
		return new Popup({
			noAllPaddings: true,
			className: this.#getPopupClassName(),
			closeIcon: false,
			content: this.#renderPopupContent(),
			events: {
				onShow: () => {
					this.#adjustDialogPosition();
					if (this.#disableScrolling)
					{
						Dom.addClass(document.body, 'ui-system-dialog__disable-scrolling');
					}
					this.emit('onShow');
				},
				onAfterShow: () => {
					this.#adjustDialogPosition();
					this.emit('onAfterShow');
				},
				onClose: () => {
					this.#adjustDialogPosition();
					if (this.#disableScrolling)
					{
						Dom.removeClass(document.body, 'ui-system-dialog__disable-scrolling');
					}
					this.emit('onHide');
				},
				onAfterClose: () => {
					this.#adjustDialogPosition();
					this.emit('onAfterHide');
				},
			},
			borderRadius: this.#getPopupBorderRadius(),
			minWidth: this.#width,
			maxWidth: this.#width,
			overlay: this.#hasOverlay ? {
				backgroundColor: 'rgba(0, 32, 78, 0.46)',
				opacity: 100,
			} : undefined,
			autoHideHandler: (event: PointerEvent) => {
				if (event.target.closest('.ui-system-dialog'))
				{
					event.preventDefault();

					return false;
				}

				const zIndex = getClosestZIndexElement(event.target);

				if (zIndex > this.#getPopup().getZindex())
				{
					event.preventDefault();

					return false;
				}

				return true;
			},
			autoHide: this.#closeByClickOutside,
			closeByEsc: this.#closeByEsc,
			cacheable: false,

			anglePosition: this.#anglePosition,
			// bindElement: this.#getCalculatedBindElement(),
		});
	}

	#getPopup(): Popup
	{
		if (!this.#popup)
		{
			this.#popup = this.#createPopup();
		}

		return this.#popup;
	}

	#updatePopupContent(): void
	{
		this.#popup?.setContent(this.#renderPopupContent());
	}

	#renderPopupContent(): HTMLElement
	{
		return Tag.render`
			<div class="ui-system-dialog__content-wrapper">
				${this.#renderDialogHeader()}
				${this.#renderDialogContent()}
				${this.#renderDialogFooter()}
			</div>
		`;
	}

	#renderDialogHeader(): HTMLElement
	{
		return Tag.render`
			<header class="ui-system-dialog__header">
				<div class="ui-system-dialog__header-left">
					${this.#title ? Headline.render(this.#title, { size: 'md' }) : ''}
					${this.#subtitle ? Text.render(this.#subtitle, { size: 'xs', className: 'ui-system-dialog__subtitle' }) : ''}
				</div>
				${this.#hasCloseButton ? this.#renderCloseButton() : ''}
			</header>
		`;
	}

	#renderCloseButton(): ?HTMLButtonElement
	{
		const icon = new Icon({
			icon: Outline.CROSS_L,
			size: 24,
			hoverMode: IconHoverMode.DEFAULT,
		});

		const btn = Tag.render`<button class="ui-system-dialog__header-close-btn">${icon.render()}</button>`;

		bind(btn, 'click', () => {
			this.#popup.close();
		});

		return btn;
	}

	#renderDialogContent(): HTMLElement
	{
		const classes = ['ui-system-dialog__content'];

		if (this.#hasHorizontalPadding === false)
		{
			classes.push('--rm-horizontal');
		}

		if (this.#hasVerticalPadding === false)
		{
			classes.push('--rm-vertical');
		}

		return Tag.render`<div class="${classes.join(' ')}">${this.#content}</div>`;
	}

	#renderDialogFooter(): ?HTMLElement
	{
		return Tag.render`
			<footer class="ui-system-dialog__footer">
				${this.#renderLeftButtons(this.#leftButtons)}
				${this.#renderCenterButtons(this.#centerButtons)}
				${this.#renderRightButtons(this.#rightButtons)}
			</footer>
		`;
	}

	#renderLeftButtons(buttons: Button[]): HTMLElement
	{
		const container = Tag.render`<div class="ui-system-dialog__left-buttons"></div>`;

		buttons.forEach((button) => {
			Dom.append(button.render(), container);
		});

		return container;
	}

	#renderCenterButtons(buttons: Button[]): HTMLElement
	{
		const container = Tag.render`<div class="ui-system-dialog__center-buttons"></div>`;

		buttons.forEach((button) => {
			Dom.append(button.render(), container);
		});

		return container;
	}

	#renderRightButtons(buttons: Button[]): HTMLElement
	{
		const container = Tag.render`<div class="ui-system-dialog__right-buttons"></div>`;

		buttons.forEach((button) => {
			Dom.append(button.render(), container);
		});

		return container;
	}

	#getPopupBorderRadius(): string
	{
		if (this.#stickPosition === 'top')
		{
			return '0 0 18px 18px';
		}

		if (this.#stickPosition === 'right')
		{
			return '18ox 0 0 18px';
		}

		if (this.#stickPosition === 'bottom')
		{
			return '18px 18px 0 0';
		}

		if (this.#stickPosition === 'left')
		{
			return '18px 0 0 18px';
		}

		return '18px 18px 18px 18px';
	}

	#getCalculatedBindElement(): ?DialogBindElement
	{
		if (!this.#bindElement)
		{
			return null;
		}

		if (Type.isPlainObject(this.#bindElement) && 'top' in this.#bindElement && 'left' in this.#bindElement)
		{
			return this.#bindElement;
		}

		if (Type.isDomNode(this.#bindElement) && this.#anglePosition)
		{
			const { width = 0, height = 0 } = Dom.getPosition(this.#popup?.getPopupContainer());

			return this.#calculateAnglePosition(this.#bindElement, this.#anglePosition, width, height);
		}

		return this.#bindElement;
	}

	#calculateAnglePosition(
		element: HTMLElement,
		anglePosition: DialogAnglePositon,
		popupWidth: number,
		popupHeight: number,
	): { top: number; left: number }
	{
		const elementPosition = Dom.getPosition(element);
		const { left, top, width, height } = elementPosition;

		const angleWidth = 16;
		const angleHeight = 8;
		const angleOffset = 30;

		switch (anglePosition)
		{
			case DialogAnglePositions.topLeft:
				return {
					top: top + height + popupHeight + angleHeight,
					left: left + (width - angleWidth) / 2 - angleOffset,
				};

			case DialogAnglePositions.topCenter:
				return {
					top: top + height + popupHeight + angleHeight * 2,
					left: left - (width / 2) - angleOffset - angleWidth / 2,
				};

			case DialogAnglePositions.topRight:
				return {
					top: top + height + popupHeight + angleHeight * 2,
					left: left - popupWidth + width / 2 + angleOffset + angleWidth / 2,
				};

			case DialogAnglePositions.rightTop:
				return {
					top: top + (height / 2) - angleOffset - angleWidth / 2,
					left: left - popupWidth - angleHeight,
				};

			case DialogAnglePositions.rightCenter:
				return {
					top: top + (height / 2) - popupHeight / 2,
					left: left - popupWidth - angleHeight,
				};

			case DialogAnglePositions.rightBottom:
				return {
					top: top + (height / 2) - popupHeight + angleOffset + angleWidth / 2,
					left: left - popupWidth - angleHeight,
				};

			case DialogAnglePositions.bottomLeft:
				return {
					top: top - popupHeight - angleHeight,
					left: left + (width / 2) - angleOffset - angleWidth / 2,
				};

			case DialogAnglePositions.bottomCenter:
				return {
					top: top - popupHeight - angleHeight,
					left: left - (width / 2) - angleOffset - angleWidth / 2,
				};

			case DialogAnglePositions.bottomRight:
				return {
					top: top - popupHeight - angleHeight,
					left: left - popupWidth + width / 2 + angleOffset + angleWidth / 2,
				};

			case DialogAnglePositions.leftTop:
				return {
					top: top + (height / 2) - angleOffset - angleWidth / 2,
					left: left + width + angleHeight,
				};

			case DialogAnglePositions.leftCenter:
				return {
					top: top + (height / 2) - popupHeight / 2,
					left: left + width + angleHeight,
				};

			case DialogAnglePositions.leftBottom:
				return {
					top: top + (height / 2) - popupHeight + angleOffset + angleWidth / 2,
					left: left + width + angleHeight,
				};

			default:
				return {
					top: top + (height / 2),
					left: left + (width / 2),
				};
		}
	}

	#getPopupClassName(): string
	{
		const classes = ['ui-system-dialog'];

		if (this.#anglePosition)
		{
			classes.push('popup-window-with-angle');

			const angleClass = this.#anglePosition
				.replaceAll(/([A-Z])/g, '-$1')
				.toLowerCase();

			classes.push(`popup-window-angle-${angleClass}`);
		}

		classes.push(`--bg-${this.#dialogBackground}`);

		return classes.join(' ');
	}

	#adjustDialogPosition(): void
	{
		if (this.#popup)
		{
			this.#popup.setBindElement(this.#getCalculatedBindElement());

			this.#popup.adjustPosition();
		}
	}
}
