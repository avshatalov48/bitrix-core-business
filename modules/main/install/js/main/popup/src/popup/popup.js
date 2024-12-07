import Button from '../compatibility/button';

import { Type, Text, Tag, Event, Dom, Browser, Reflection } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { type PopupOptions, type PopupTarget, type PopupAnimationOptions } from './popup-types';
import { ZIndexManager, ZIndexComponent } from 'main.core.z-index-manager';
import PositionEvent from './position-event';
import CloseIconSize from './popup-close-icon-size';

declare type TargetPosition = {
	left: number,
	top: number,
	bottom: number,
	windowSize: number,
	windowScroll: number,
	popupWidth: number,
	popupHeight: number
};

const aliases = {
	onPopupWindowInit: { namespace: 'BX.Main.Popup', eventName: 'onInit' },
	onPopupWindowIsInitialized: { namespace: 'BX.Main.Popup', eventName: 'onAfterInit' },
	onPopupFirstShow: { namespace: 'BX.Main.Popup', eventName: 'onFirstShow' },
	onPopupShow: { namespace: 'BX.Main.Popup', eventName: 'onShow' },
	onAfterPopupShow: { namespace: 'BX.Main.Popup', eventName: 'onAfterShow' },
	onPopupClose: { namespace: 'BX.Main.Popup', eventName: 'onClose' },
	onPopupAfterClose: { namespace: 'BX.Main.Popup', eventName: 'onAfterClose' },
	onPopupDestroy: { namespace: 'BX.Main.Popup', eventName: 'onDestroy' },
	onPopupFullscreenLeave: { namespace: 'BX.Main.Popup', eventName: 'onFullscreenLeave' },
	onPopupFullscreenEnter: { namespace: 'BX.Main.Popup', eventName: 'onFullscreenEnter' },
	onPopupDragStart: { namespace: 'BX.Main.Popup', eventName: 'onDragStart' },
	onPopupDrag: { namespace: 'BX.Main.Popup', eventName: 'onDrag' },
	onPopupDragEnd: { namespace: 'BX.Main.Popup', eventName: 'onDragEnd' },
	onPopupResizeStart: { namespace: 'BX.Main.Popup', eventName: 'onResizeStart' },
	onPopupResize: { namespace: 'BX.Main.Popup', eventName: 'onResize' },
	onPopupResizeEnd: { namespace: 'BX.Main.Popup', eventName: 'onResizeEnd' }
};

EventEmitter.registerAliases(aliases);

const disabledScrolls: WeakMap<HTMLElement, Set<Popup>> = new WeakMap();

/**
 * @memberof BX.Main
 */
export default class Popup extends EventEmitter
{
	/**
	 * @private
	 */
	static options = {};

	/**
	 * @private
	 */
	static defaultOptions = {

		//left offset for popup about target
		angleLeftOffset: 40,

		//when popup position is 'top' offset distance between popup body and target node
		positionTopXOffset: -11,

		//offset distance between popup body and target node if use angle, sum with positionTopXOffset
		angleTopOffset: 10,

		popupZindex: 1000,
		popupOverlayZindex: 1100,

		angleMinLeft: 10,
		angleMaxLeft: 30,

		angleMinRight: 10,
		angleMaxRight: 30,

		angleMinBottom: 23,
		angleMaxBottom: 25,

		angleMinTop: 23,
		angleMaxTop: 25,

		offsetLeft: 0,
		offsetTop: 0
	};

	static setOptions(options: { [name: string]: any })
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		for (let option in options)
		{
			this.options[option] = options[option];
		}
	}

	static getOption(option: string, defaultValue?: any)
	{
		if (!Type.isUndefined(this.options[option]))
		{
			return this.options[option];
		}
		else if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}
		else
		{
			return this.defaultOptions[option];
		}
	}

	constructor(options?: PopupOptions)
	{
		super();
		this.setEventNamespace('BX.Main.Popup');

		let [popupId: string, bindElement: PopupTarget, params: PopupOptions] = arguments; //compatible arguments

		this.compatibleMode = params && Type.isBoolean(params.compatibleMode) ? params.compatibleMode : true;
		if (Type.isPlainObject(options) && !bindElement && !params)
		{
			params = options;
			popupId = options.id;
			bindElement = options.bindElement;
			this.compatibleMode = false;
		}

		params = params || {};
		this.params = params;

		if (!Type.isStringFilled(popupId))
		{
			popupId = 'popup-window-' + Text.getRandom().toLowerCase();
		}

		this.emit('onInit', new BaseEvent({ compatData: [popupId, bindElement, params] }));

		/**
		 * @private
		 */
		this.uniquePopupId = popupId;
		this.params.zIndex = Type.isNumber(params.zIndex) ? parseInt(params.zIndex) : 0;
		this.params.zIndexAbsolute = Type.isNumber(params.zIndexAbsolute) ? parseInt(params.zIndexAbsolute) : 0;
		this.buttons = params.buttons && Type.isArray(params.buttons) ? params.buttons : [];
		this.offsetTop = Popup.getOption('offsetTop');
		this.offsetLeft = Popup.getOption('offsetLeft');
		this.firstShow = false;
		this.bordersWidth = 20;
		this.bindElementPos = null;
		this.closeIcon = null;
		this.resizeIcon = null;
		this.angle = null;
		this.angleArrowElement = null;
		this.overlay = null;
		this.titleBar = null;
		this.bindOptions = typeof (params.bindOptions) === 'object' ? params.bindOptions : {};
		this.autoHide = params.autoHide === true;
		this.disableScroll = params.disableScroll === true || params.isScrollBlock === true;
		this.autoHideHandler = Type.isFunction(params.autoHideHandler) ? params.autoHideHandler : null;
		this.handleAutoHide = this.handleAutoHide.bind(this);
		this.handleOverlayClick = this.handleOverlayClick.bind(this);
		this.isAutoHideBinded = false;
		this.closeByEsc = params.closeByEsc === true;
		this.isCloseByEscBinded = false;
		this.toFrontOnShow = true;

		this.cacheable = true;
		this.destroyed = false;
		this.fixed = false;

		this.width = null;
		this.height = null;
		this.minWidth = null;
		this.minHeight = null;
		this.maxWidth = null;
		this.maxHeight = null;

		this.padding = null;
		this.contentPadding = null;
		this.background = null;
		this.contentBackground = null;

		this.borderRadius = null;
		this.contentBorderRadius = null;

		this.targetContainer = Type.isElementNode(params.targetContainer) ? params.targetContainer : document.body;

		this.dragOptions = {
			cursor: '',
			callback: function() {
			},
			eventName: ''
		};

		this.dragged = false;
		this.dragPageX = 0;
		this.dragPageY = 0;

		this.animationShowClassName = null;
		this.animationCloseClassName = null;
		this.animationCloseEventType = null;

		this.handleDocumentMouseMove = this.handleDocumentMouseMove.bind(this);
		this.handleDocumentMouseUp = this.handleDocumentMouseUp.bind(this);
		this.handleDocumentKeyUp = this.handleDocumentKeyUp.bind(this);
		this.handleResizeWindow = this.handleResizeWindow.bind(this);
		this.handleResize = this.handleResize.bind(this);
		this.handleMove = this.handleMove.bind(this);
		this.onTitleMouseDown = this.onTitleMouseDown.bind(this);
		this.handleFullScreen = this.handleFullScreen.bind(this);

		this.subscribeFromOptions(params.events);

		let popupClassName = 'popup-window';

		if (params.titleBar)
		{
			popupClassName += ' popup-window-with-titlebar';
		}

		if (params.className && Type.isStringFilled(params.className))
		{
			popupClassName += ' ' + params.className;
		}

		if (params.darkMode)
		{
			popupClassName += ' popup-window-dark';
		}

		if (params.titleBar)
		{
			this.titleBar = Tag.render`
				<div class="popup-window-titlebar" id="popup-window-titlebar-${popupId}"></div>
			`;
		}

		if (params.closeIcon)
		{
			let className = 'popup-window-close-icon'
				+ (params.titleBar ? ' popup-window-titlebar-close-icon' : '');
			if (Object.values(CloseIconSize).includes(params.closeIconSize) && params.closeIconSize !== CloseIconSize.SMALL)
			{
				className += ` --${params.closeIconSize}`;
			}

			this.closeIcon = Tag.render`
				<span class="${className}" onclick="${this.handleCloseIconClick.bind(this)}"></span>
			`;



			if (Type.isPlainObject(params.closeIcon))
			{
				Dom.style(this.closeIcon, params.closeIcon);
			}
		}

		/**
		 * @private
		 */
		this.contentContainer = Tag.render
			`<div id="popup-window-content-${popupId}" class="popup-window-content"></div>`
		;

		/**
		 * @private
		 */
		this.popupContainer = Tag.render
			`<div
				class="${popupClassName}"
				id="${popupId}"
				style="display: none; position: absolute; left: 0; top: 0;"
			>${[this.titleBar, this.contentContainer, this.closeIcon]}</div>`
		;

		this.targetContainer.appendChild(this.popupContainer);

		this.zIndexComponent = ZIndexManager.register(this.popupContainer, params.zIndexOptions);

		this.buttonsContainer = null;

		if (params.contentColor && Type.isStringFilled(params.contentColor))
		{
			if (
				params.contentColor === 'white'
				|| params.contentColor === 'gray'
			)
			{
				popupClassName += ' popup-window-content-' + params.contentColor;
			}

			this.setContentColor(params.contentColor);

		}

		if (params.angle)
		{
			this.setAngle(params.angle);
		}

		if (params.overlay)
		{
			this.setOverlay(params.overlay);
		}

		this.setOffset(params);
		this.setBindElement(bindElement);
		this.setTitleBar(params.titleBar);
		this.setContent(params.content);
		this.setButtons(params.buttons);
		this.setWidth(params.width);
		this.setHeight(params.height);
		this.setMinWidth(params.minWidth);
		this.setMinHeight(params.minHeight);
		this.setMaxWidth(params.maxWidth);
		this.setMaxHeight(params.maxHeight);
		this.setResizeMode(params.resizable);
		this.setPadding(params.padding);
		this.setContentPadding(params.contentPadding);
		this.setBorderRadius(params.borderRadius);
		this.setContentBorderRadius(params.contentBorderRadius);
		this.setBackground(params.background);
		this.setContentBackground(params.contentBackground);
		this.setAnimation(params.animation);
		this.setCacheable(params.cacheable);
		this.setToFrontOnShow(params.toFrontOnShow);
		this.setFixed(params.fixed);

		// Compatibility
		if (params.contentNoPaddings)
		{
			this.setContentPadding(0);
		}
		if (params.noAllPaddings)
		{
			this.setPadding(0);
			this.setContentPadding(0);
		}

		if (params.bindOnResize !== false)
		{
			Event.bind(window, 'resize', this.handleResizeWindow);
		}

		this.emit('onAfterInit', new BaseEvent({ compatData: [popupId, this] }));
	}

	/**
	 * @private
	 */
	subscribeFromOptions(events): void
	{
		super.subscribeFromOptions(events, aliases);
	}

	getId(): string
	{
		return this.uniquePopupId;
	}

	isCompatibleMode(): boolean
	{
		return this.compatibleMode;
	}

	setContent(content: string | Element | Node)
	{
		if (!this.contentContainer || !content)
		{
			return;
		}

		if (Type.isElementNode(content))
		{
			Dom.clean(this.contentContainer);

			const hasParent = Type.isDomNode(content.parentNode);
			this.contentContainer.appendChild(content);
			if (this.isCompatibleMode() || hasParent)
			{
				content.style.display = 'block';
			}
		}
		else if (Type.isString(content))
		{
			this.contentContainer.innerHTML = content;
		}
		else
		{
			this.contentContainer.innerHTML = '&nbsp;';
		}
	}

	setButtons(buttons: [])
	{
		this.buttons = buttons && Type.isArray(buttons) ? buttons : [];

		if (this.buttonsContainer)
		{
			Dom.remove(this.buttonsContainer);
		}

		const ButtonClass = Reflection.getClass('BX.UI.Button');
		if (this.buttons.length > 0 && this.contentContainer)
		{
			const newButtons = [];
			for (let i = 0; i < this.buttons.length; i++)
			{
				const button = this.buttons[i];
				if (button instanceof Button)
				{
					button.popupWindow = this;
					newButtons.push(button.render());
				}
				else if (ButtonClass && (button instanceof ButtonClass))
				{
					button.setContext(this);
					newButtons.push(button.render());
				}
			}

			this.buttonsContainer = this.contentContainer.parentNode.appendChild(
				Tag.render`<div class="popup-window-buttons">${newButtons}</div>`
			);
		}
	}

	getButtons(): []
	{
		return this.buttons;
	}

	getButton(id: string)
	{
		for (let i = 0; i < this.buttons.length; i++)
		{
			const button = this.buttons[i];
			if (button.getId() === id)
			{
				return button;
			}
		}

		return null;
	}

	setBindElement(bindElement: Element | { left: number, top: number } | null | MouseEvent)
	{
		if (bindElement === null)
		{
			this.bindElement = null;
		}
		else if (typeof (bindElement) === 'object')
		{
			if (Type.isDomNode(bindElement) || (Type.isNumber(bindElement.top) && Type.isNumber(bindElement.left)))
			{
				this.bindElement = bindElement;
			}
			else if (Type.isNumber(bindElement.clientX) && Type.isNumber(bindElement.clientY))
			{
				this.bindElement = { left: bindElement.pageX, top: bindElement.pageY, bottom: bindElement.pageY };
			}
		}
	}

	/**
	 * @private
	 */
	getBindElementPos(bindElement: HTMLElement | any): TargetPosition | DOMRect
	{
		if (Type.isDomNode(bindElement))
		{
			if (this.isTargetDocumentBody())
			{
				return this.isFixed() ? bindElement.getBoundingClientRect() : Dom.getPosition(bindElement);
			}
			else
			{
				return this.getPositionRelativeToTarget(bindElement);
			}
		}
		else if (bindElement && typeof (bindElement) === 'object')
		{
			if (!Type.isNumber(bindElement.bottom))
			{
				bindElement.bottom = bindElement.top;
			}

			return bindElement;
		}
		else
		{
			const windowSize = this.getWindowSize();
			const windowScroll = this.getWindowScroll();

			const popupWidth = this.getPopupContainer().offsetWidth;
			const popupHeight = this.getPopupContainer().offsetHeight;

			this.bindOptions.forceTop = true;

			return {
				left: windowSize.innerWidth / 2 - popupWidth / 2 + windowScroll.scrollLeft,
				top: windowSize.innerHeight / 2 - popupHeight / 2 + (this.isFixed() ? 0 : windowScroll.scrollTop),
				bottom: windowSize.innerHeight / 2 - popupHeight / 2 + (this.isFixed() ? 0 : windowScroll.scrollTop),

				//for optimisation purposes
				windowSize: windowSize,
				windowScroll: windowScroll,
				popupWidth: popupWidth,
				popupHeight: popupHeight
			};
		}
	}

	/**
	 * @internal
	 */
	getPositionRelativeToTarget(element: HTMLElement): DOMRect
	{
		let offsetLeft = element.offsetLeft;
		let offsetTop = element.offsetTop;
		let offsetElement = element.offsetParent;

		while (offsetElement && offsetElement !== this.getTargetContainer())
		{
			offsetLeft += offsetElement.offsetLeft;
			offsetTop += offsetElement.offsetTop;
			offsetElement = offsetElement.offsetParent;
		}

		const elementRect = element.getBoundingClientRect();

		return new DOMRect(
			offsetLeft,
			offsetTop,
			elementRect.width,
			elementRect.height
		);
	}

	// private
	getWindowSize(): { innerWidth: number, innerHeight: number }
	{
		if (this.isTargetDocumentBody())
		{
			return {
				innerWidth: window.innerWidth,
				innerHeight: window.innerHeight
			};
		}
		else
		{
			return {
				innerWidth: this.getTargetContainer().offsetWidth,
				innerHeight: this.getTargetContainer().offsetHeight
			};
		}
	}

	// private
	getWindowScroll()
	{
		if (this.isTargetDocumentBody())
		{
			return {
				scrollLeft: window.pageXOffset,
				scrollTop: window.pageYOffset
			};
		}
		else
		{
			return {
				scrollLeft: this.getTargetContainer().scrollLeft,
				scrollTop: this.getTargetContainer().scrollTop
			};
		}
	}

	setAngle(params: { offset: number, position?: 'top' | 'bottom' | 'left' | 'right' })
	{
		if (params === false)
		{
			if (this.angle !== null)
			{
				Dom.remove(this.angle.element);
			}

			this.angle = null;
			this.angleArrowElement = null;
			return;
		}

		const className = 'popup-window-angly';
		if (this.angle === null)
		{
			const position = this.bindOptions.position && this.bindOptions.position === 'top' ? 'bottom' : 'top';
			const angleMinLeft = Popup.getOption(position === 'top' ? 'angleMinTop' : 'angleMinBottom');
			let defaultOffset = Type.isNumber(params.offset) ? params.offset : 0;

			const angleLeftOffset = Popup.getOption('angleLeftOffset', null);
			if (defaultOffset > 0 && Type.isNumber(angleLeftOffset))
			{
				defaultOffset += angleLeftOffset - Popup.defaultOptions.angleLeftOffset;
			}

			this.angleArrowElement = Tag.render`<div class="popup-window-angly--arrow"></div>`;
			if (this.background)
			{
				this.angleArrowElement.style.background = this.background;
			}

			this.angle = {
				element: Tag.render`
					<div class="${className} ${className}-${position}">
						${this.angleArrowElement}
					</div>
				`,
				position: position,
				offset: 0,
				defaultOffset: Math.max(defaultOffset, angleMinLeft)
				//Math.max(Type.isNumber(params.offset) ? params.offset : 0, angleMinLeft)
			};

			this.getPopupContainer().appendChild(this.angle.element);
		}

		if (typeof (params) === 'object' && params.position && ['top', 'right', 'bottom', 'left', 'hide'].includes(params.position))
		{
			Dom.removeClass(this.angle.element, className + '-' + this.angle.position);
			Dom.addClass(this.angle.element, className + '-' + params.position);

			this.angle.position = params.position;
		}

		if (typeof (params) === 'object' && Type.isNumber(params.offset))
		{
			const offset = params.offset;
			let minOffset, maxOffset;
			if (this.angle.position === 'top')
			{
				minOffset = Popup.getOption('angleMinTop');
				maxOffset = this.getPopupContainer().offsetWidth - Popup.getOption('angleMaxTop');
				maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

				this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
				this.angle.element.style.left = this.angle.offset + 'px';
				this.angle.element.style.marginLeft = 0;
				this.angle.element.style.removeProperty('top');
			}
			else if (this.angle.position === 'bottom')
			{
				minOffset = Popup.getOption('angleMinBottom');
				maxOffset = this.getPopupContainer().offsetWidth - Popup.getOption('angleMaxBottom');
				maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

				this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
				this.angle.element.style.marginLeft = this.angle.offset + 'px';
				this.angle.element.style.left = 0;
				this.angle.element.style.removeProperty('top');
			}
			else if (this.angle.position === 'right')
			{
				minOffset = Popup.getOption('angleMinRight');
				maxOffset = this.getPopupContainer().offsetHeight - Popup.getOption('angleMaxRight');
				maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

				this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
				this.angle.element.style.top = this.angle.offset + 'px';
				this.angle.element.style.removeProperty('left');
				this.angle.element.style.removeProperty('margin-left');
			}
			else if (this.angle.position === 'left')
			{
				minOffset = Popup.getOption('angleMinLeft');
				maxOffset = this.getPopupContainer().offsetHeight - Popup.getOption('angleMaxLeft');
				maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

				this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
				this.angle.element.style.top = this.angle.offset + 'px';
				this.angle.element.style.removeProperty('left');
				this.angle.element.style.removeProperty('margin-left');
			}
		}
	}

	getWidth(): number
	{
		return this.width;
	}

	setWidth(width: number)
	{
		this.setWidthProperty('width', width);
	}

	getHeight(): number
	{
		return this.height;
	}

	setHeight(height: number)
	{
		this.setHeightProperty('height', height);
	}

	getMinWidth(): number
	{
		return this.minWidth;
	}

	setMinWidth(width: number)
	{
		this.setWidthProperty('minWidth', width);
	}

	getMinHeight(): number
	{
		return this.minHeight;
	}

	setMinHeight(height: number)
	{
		this.setHeightProperty('minHeight', height);
	}

	getMaxWidth(): number
	{
		return this.maxWidth;
	}

	setMaxWidth(width: number)
	{
		this.setWidthProperty('maxWidth', width);
	}

	getMaxHeight(): number
	{
		return this.maxHeight;
	}

	setMaxHeight(height: number)
	{
		this.setHeightProperty('maxHeight', height);
	}

	/**
	 * @private
	 */
	setWidthProperty(property: string, width: number)
	{
		const props = ['width', 'minWidth', 'maxWidth'];
		if (props.indexOf(property) === -1)
		{
			return;
		}

		if (Type.isNumber(width) && width >= 0)
		{
			this[property] = width;
			this.getResizableContainer().style[property] = width + 'px';
			this.getContentContainer().style.overflowX = 'auto';
			this.getPopupContainer().classList.add('popup-window-fixed-width');

			if (this.getTitleContainer() && Browser.isIE11())
			{
				this.getTitleContainer().style[property] = width + 'px';
			}
		}
		else if (width === null || width === false)
		{
			this[property] = null;
			this.getResizableContainer().style.removeProperty(Text.toKebabCase(property));

			const hasOtherProps = props.some(function(prop) {
				return this.getResizableContainer().style.getPropertyValue(Text.toKebabCase(prop)) !== '';
			}, this);

			if (!hasOtherProps)
			{
				this.getContentContainer().style.removeProperty('overflow-x');
				this.getPopupContainer().classList.remove('popup-window-fixed-width');
			}

			if (this.getTitleContainer() && Browser.isIE11())
			{
				this.getTitleContainer().style.removeProperty(Text.toKebabCase(property));
			}
		}
	}

	/**
	 * @private
	 */
	setHeightProperty(property: string, height: number)
	{
		const props = ['height', 'minHeight', 'maxHeight'];
		if (props.indexOf(property) === -1)
		{
			return;
		}

		if (Type.isNumber(height) && height >= 0)
		{
			this[property] = height;
			this.getResizableContainer().style[property] = height + 'px';
			this.getContentContainer().style.overflowY = 'auto';
			this.getPopupContainer().classList.add('popup-window-fixed-height');
		}
		else if (height === null || height === false)
		{
			this[property] = null;
			this.getResizableContainer().style.removeProperty(Text.toKebabCase(property));

			const hasOtherProps = props.some(function(prop) {
				return this.getResizableContainer().style.getPropertyValue(Text.toKebabCase(prop)) !== '';
			}, this);

			if (!hasOtherProps)
			{
				this.getContentContainer().style.removeProperty('overflow-y');
				this.getPopupContainer().classList.remove('popup-window-fixed-height');
			}
		}
	}

	setPadding(padding: number)
	{
		if (Type.isNumber(padding) && padding >= 0)
		{
			this.padding = padding;
			this.getPopupContainer().style.padding = padding + 'px';
		}
		else if (padding === null)
		{
			this.padding = null;
			this.getPopupContainer().style.removeProperty('padding');
		}
	}

	getPadding(): number
	{
		return this.padding;
	}

	setContentPadding(padding: number)
	{
		if (Type.isNumber(padding) && padding >= 0)
		{
			this.contentPadding = padding;
			this.getContentContainer().style.padding = padding + 'px';
		}
		else if (padding === null)
		{
			this.contentPadding = null;
			this.getContentContainer().style.removeProperty('padding');
		}
	}

	getContentPadding(): number
	{
		return this.contentPadding;
	}

	setBorderRadius(radius): void
	{
		if (Type.isStringFilled(radius))
		{
			this.borderRadius = radius;
			this.getPopupContainer().style.setProperty('--popup-window-border-radius', radius);
		}
		else if (radius === null)
		{
			this.borderRadius = null;
			this.getPopupContainer().style.removeProperty('--popup-window-border-radius');
		}
	}

	setContentBorderRadius(radius): void
	{
		if (Type.isStringFilled(radius))
		{
			this.contentBorderRadius = radius;
			this.getContentContainer().style.setProperty('--popup-window-content-border-radius', radius);
		}
		else if (radius === null)
		{
			this.contentBorderRadius = null;
			this.getContentContainer().style.removeProperty('--popup-window-content-border-radius');
		}
	}

	setContentColor(color: string | null)
	{
		if (Type.isString(color) && this.contentContainer)
		{
			this.contentContainer.style.backgroundColor = color;
		}
		else if (color === null)
		{
			this.contentContainer.style.style.removeProperty('background-color');
		}
	}

	setBackground(background: string | null)
	{
		if (Type.isStringFilled(background))
		{
			this.background = background;
			this.getPopupContainer().style.background = background;

			if (this.angleArrowElement)
			{
				this.angleArrowElement.style.background = background;
			}
		}
		else if (background === null)
		{
			this.background = null;
			this.getPopupContainer().style.removeProperty('background');

			if (this.angleArrowElement)
			{
				this.angleArrowElement.style.removeProperty('background');
			}
		}
	}

	getBackground(): string | null
	{
		return this.background;
	}

	setContentBackground(background: string | null)
	{
		if (Type.isStringFilled(background))
		{
			this.contentBackground = background;
			this.getContentContainer().style.background = background;
		}
		else if (background === null)
		{
			this.contentBackground = null;
			this.getContentContainer().style.removeProperty('background');
		}
	}

	getContentBackground(): string | null
	{
		return this.contentBackground;
	}

	isDestroyed(): boolean
	{
		return this.destroyed;
	}

	setCacheable(cacheable: boolean): void
	{
		this.cacheable = cacheable !== false;
	}

	isCacheable(): boolean
	{
		return this.cacheable;
	}

	setToFrontOnShow(flag: boolean): void
	{
		this.toFrontOnShow = flag !== false;
	}

	shouldFrontOnShow(): boolean
	{
		return this.toFrontOnShow;
	}

	setFixed(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.fixed = flag;
			if (flag)
			{
				Dom.addClass(this.getPopupContainer(), '--fixed');
			}
			else
			{
				Dom.removeClass(this.getPopupContainer(), '--fixed');
			}
		}
	}

	isFixed(): boolean
	{
		return this.fixed;
	}

	setResizeMode(mode: boolean): void
	{
		if (mode === true || Type.isPlainObject(mode))
		{
			if (!this.resizeIcon)
			{
				this.resizeIcon = Tag.render`
					<div class="popup-window-resize" onmousedown="${this.handleResizeMouseDown.bind(this)}"></div>
				`;

				this.getPopupContainer().appendChild(this.resizeIcon);
			}

			//Compatibility
			this.setMinWidth(mode.minWidth);
			this.setMinHeight(mode.minHeight);
		}
		else if (mode === false && this.resizeIcon)
		{
			Dom.remove(this.resizeIcon);
			this.resizeIcon = null;
		}
	}

	getTargetContainer(): HTMLElement
	{
		return this.targetContainer;
	}

	isTargetDocumentBody(): boolean
	{
		return this.getTargetContainer() === document.body;
	}

	getPopupContainer(): Element
	{
		return this.popupContainer;
	}

	getContentContainer(): Element
	{
		return this.contentContainer;
	}

	getResizableContainer(): Element
	{
		return Browser.isIE11() ? this.getContentContainer() : this.getPopupContainer();
	}

	getTitleContainer(): Element
	{
		return this.titleBar;
	}

	/**
	 * @private
	 */
	onTitleMouseDown(event: MouseEvent): void
	{
		this._startDrag(
			event,
			{
				cursor: 'move',
				callback: this.handleMove,
				eventName: 'Drag'
			}
		);
	}

	/**
	 * @private
	 */
	handleResizeMouseDown(event): void
	{
		this._startDrag(
			event,
			{
				cursor: 'nwse-resize',
				eventName: 'Resize',
				callback: this.handleResize
			}
		);

		if (this.isTargetDocumentBody())
		{
			this.resizeContentPos = Dom.getPosition(this.getResizableContainer());
			this.resizeContentOffset =
				this.resizeContentPos.left - Dom.getPosition(this.getPopupContainer()).left;
		}
		else
		{
			this.resizeContentPos = this.getPositionRelativeToTarget(this.getResizableContainer());
			this.resizeContentOffset =
				this.resizeContentPos.left - this.getPositionRelativeToTarget(this.getPopupContainer()).left;
		}

		this.resizeContentPos.offsetX = 0;
		this.resizeContentPos.offsetY = 0;
	}

	/**
	 * @private
	 */
	handleResize(offsetX, offsetY, pageX, pageY): void
	{
		this.resizeContentPos.offsetX += offsetX;
		this.resizeContentPos.offsetY += offsetY;

		let width = this.resizeContentPos.width + this.resizeContentPos.offsetX;
		let height = this.resizeContentPos.height + this.resizeContentPos.offsetY;

		const scrollWidth =
			this.isTargetDocumentBody() ? document.documentElement.scrollWidth : this.getTargetContainer().scrollWidth
		;

		if (this.resizeContentPos.left + width + this.resizeContentOffset >= scrollWidth)
		{
			width = scrollWidth - this.resizeContentPos.left - this.resizeContentOffset;
		}

		width = Math.max(width, this.getMinWidth());
		height = Math.max(height, this.getMinHeight());

		if (this.getMaxWidth() !== null)
		{
			width = Math.min(width, this.getMaxWidth());
		}

		if (this.getMaxHeight() !== null)
		{
			height = Math.min(height, this.getMaxHeight());
		}

		this.setWidth(width);
		this.setHeight(height);
	}

	isTopAngle(): boolean
	{
		return this.angle !== null && this.angle.position === 'top';
	}

	isBottomAngle(): boolean
	{
		return this.angle !== null && this.angle.position === 'bottom';
	}

	isTopOrBottomAngle(): boolean
	{
		return this.angle !== null && (this.angle.position === 'top' || this.angle.position === 'bottom');
	}

	/**
	 * @private
	 */
	getAngleHeight(): number
	{
		return (this.isTopOrBottomAngle() ? Popup.getOption('angleTopOffset') : 0);
	}

	setOffset(params: { offsetTop: number, offsetLeft: number }): void
	{
		if (!Type.isPlainObject(params))
		{
			return;
		}

		if (Type.isNumber(params.offsetLeft))
		{
			this.offsetLeft = params.offsetLeft + Popup.getOption('offsetLeft');
		}

		if (Type.isNumber(params.offsetTop))
		{
			this.offsetTop = params.offsetTop + Popup.getOption('offsetTop');
		}
	}

	setTitleBar(params: string | { content: string }): void
	{
		if (!this.titleBar)
		{
			return;
		}

		if (typeof (params) === 'object' && Type.isDomNode(params.content))
		{
			this.titleBar.innerHTML = '';
			this.titleBar.appendChild(params.content);
		}
		else if (typeof (params) === 'string')
		{
			this.titleBar.innerHTML = '';
			this.titleBar.appendChild(
				Dom.create('span', {
					props: {
						className: 'popup-window-titlebar-text'
					},
					text: params
				})
			);
		}

		if (this.params.draggable)
		{
			this.titleBar.style.cursor = 'move';
			Event.bind(this.titleBar, 'mousedown', this.onTitleMouseDown);
		}
	}

	setClosingByEsc(enable: boolean): void
	{
		enable = Type.isBoolean(enable) ? enable : true;
		if (enable)
		{
			this.closeByEsc = true;
			this.bindClosingByEsc();
		}
		else
		{
			this.closeByEsc = false;
			this.unbindClosingByEsc();
		}
	}

	/**
	 * @private
	 */
	bindClosingByEsc(): void
	{
		if (this.closeByEsc && !this.isCloseByEscBinded)
		{
			Event.bind(document, 'keyup', this.handleDocumentKeyUp);
			this.isCloseByEscBinded = true;
		}
	}

	/**
	 * @private
	 */
	unbindClosingByEsc(): void
	{
		if (this.isCloseByEscBinded)
		{
			Event.unbind(document, 'keyup', this.handleDocumentKeyUp);
			this.isCloseByEscBinded = false;
		}
	}

	setAutoHide(enable: boolean): void
	{
		enable = Type.isBoolean(enable) ? enable : true;
		if (enable)
		{
			this.autoHide = true;
			this.bindAutoHide();
		}
		else
		{
			this.autoHide = false;
			this.unbindAutoHide();
		}
	}

	/**
	 * @private
	 */
	bindAutoHide(): void
	{
		if (this.autoHide && !this.isAutoHideBinded && this.isShown())
		{
			this.isAutoHideBinded = true;

			if (this.isCompatibleMode())
			{
				Event.bind(this.getPopupContainer(), 'click', this.handleContainerClick);
			}

			if (this.overlay && this.overlay.element)
			{
				Event.bind(this.overlay.element, 'click', this.handleOverlayClick);
			}
			else
			{
				if (this.isCompatibleMode())
				{
					Event.bind(document, 'click', this.handleAutoHide);
				}
				else
				{
					document.addEventListener('click', this.handleAutoHide, true);
				}
			}
		}
	}

	/**
	 * @private
	 */
	unbindAutoHide(): void
	{
		if (this.isAutoHideBinded)
		{
			this.isAutoHideBinded = false;

			if (this.isCompatibleMode())
			{
				Event.unbind(this.getPopupContainer(), 'click', this.handleContainerClick);
			}

			if (this.overlay && this.overlay.element)
			{
				Event.unbind(this.overlay.element, 'click', this.handleOverlayClick);
			}
			else
			{
				if (this.isCompatibleMode())
				{
					Event.unbind(document, 'click', this.handleAutoHide);
				}
				else
				{
					document.removeEventListener('click', this.handleAutoHide, true);
				}
			}
		}
	}

	/**
	 * @private
	 */
	handleAutoHide(event): void
	{
		if (this.isDestroyed())
		{
			return;
		}

		if (this.autoHideHandler !== null)
		{
			if (this.autoHideHandler(event))
			{
				this._tryCloseByEvent(event);
			}
		}
		else if (event.target !== this.getPopupContainer() && !this.getPopupContainer().contains(event.target))
		{
			this._tryCloseByEvent(event);
		}
	}

	/**
	 * @private
	 */
	_tryCloseByEvent(event): void
	{
		if (this.isCompatibleMode())
		{
			this.tryCloseByEvent(event);
		}
		else
		{
			setTimeout(() => {
				this.tryCloseByEvent(event);
			}, 0);
		}
	}

	/**
	 * @private
	 */
	tryCloseByEvent(event): void
	{
		if (event.button === 0)
		{
			this.close();
		}
	}

	/**
	 * @private
	 */
	handleOverlayClick(event): void
	{
		this.tryCloseByEvent(event);
		event.stopPropagation();
	}

	setOverlay(params: { backgroundColor?: string, opacity?: number }): void
	{
		if (this.overlay === null)
		{
			this.overlay = {
				element: Tag.render`
					<div class="popup-window-overlay" id="popup-window-overlay-${this.getId()}"></div>
				`
			};

			this.resizeOverlay();

			this.targetContainer.appendChild(this.overlay.element);
			this.getZIndexComponent().setOverlay(this.overlay.element);
		}

		if (params && Type.isNumber(params.opacity) && params.opacity >= 0 && params.opacity <= 100)
		{
			this.overlay.element.style.opacity = parseFloat(params.opacity / 100).toPrecision(3);
		}

		if (params && params.backgroundColor)
		{
			this.overlay.element.style.backgroundColor = params.backgroundColor;
		}
	}

	removeOverlay(): void
	{
		if (this.overlay !== null && this.overlay.element !== null)
		{
			Dom.remove(this.overlay.element);
			this.getZIndexComponent().setOverlay(null);
		}

		if (this.overlayTimeout)
		{
			clearInterval(this.overlayTimeout);
			this.overlayTimeout = null;
		}

		this.overlay = null;
	}

	hideOverlay(): void
	{
		if (this.overlay !== null && this.overlay.element !== null)
		{
			if (this.overlayTimeout)
			{
				clearInterval(this.overlayTimeout);
				this.overlayTimeout = null;
			}

			this.overlay.element.style.display = 'none';
		}
	}

	showOverlay(): void
	{
		if (this.overlay !== null && this.overlay.element !== null)
		{
			this.overlay.element.style.display = 'block';

			let popupHeight = this.getPopupContainer().offsetHeight;
			this.overlayTimeout = setInterval(() => {
				if (popupHeight !== this.getPopupContainer().offsetHeight)
				{
					this.resizeOverlay();
					popupHeight = this.getPopupContainer().offsetHeight;
				}
			}, 1000);
		}
	}

	resizeOverlay(): void
	{
		if (this.overlay !== null && this.overlay.element !== null)
		{
			let scrollWidth;
			let scrollHeight;
			if (this.isTargetDocumentBody())
			{
				scrollWidth = document.documentElement.scrollWidth;
				scrollHeight = Math.max(
					document.body.scrollHeight, document.documentElement.scrollHeight,
					document.body.offsetHeight, document.documentElement.offsetHeight,
					document.body.clientHeight, document.documentElement.clientHeight
				);
			}
			else
			{
				scrollWidth = this.getTargetContainer().scrollWidth;
				scrollHeight = this.getTargetContainer().scrollHeight;
			}

			this.overlay.element.style.width = scrollWidth + 'px';
			this.overlay.element.style.height = scrollHeight + 'px';
		}
	}

	getZindex(): number
	{
		return this.getZIndexComponent().getZIndex();
	}

	getZIndexComponent(): ZIndexComponent
	{
		return this.zIndexComponent;
	}

	setDisableScroll(flag: boolean): void
	{
		const disable = Type.isBoolean(flag) ? flag : true;
		if (disable)
		{
			this.disableScroll = true;
			this.#disableTargetScroll();
		}
		else
		{
			this.disableScroll = false;
			this.#enableTargetScroll();
		}
	}

	#disableTargetScroll(): void
	{
		const target = this.getTargetContainer();
		let popups: Set<Popup> = disabledScrolls.get(target);
		if (!popups)
		{
			popups = new Set();
			disabledScrolls.set(target, popups);
		}

		popups.add(this);

		Dom.addClass(target, 'popup-window-disable-scroll');
	}

	#enableTargetScroll(): void
	{
		const target = this.getTargetContainer();
		const popups: Set<Popup> = disabledScrolls.get(target) || null;
		if (popups)
		{
			popups.delete(this);
		}

		if (popups === null || popups.size === 0)
		{
			Dom.removeClass(target, 'popup-window-disable-scroll');
		}
	}

	show(): void
	{
		if (this.isShown() || this.isDestroyed())
		{
			return;
		}

		this.emit('onBeforeShow');

		this.showOverlay();
		this.getPopupContainer().style.display = 'block';
		Dom.addClass(this.getPopupContainer(), '--open');

		if (this.shouldFrontOnShow())
		{
			this.bringToFront();
		}

		if (!this.firstShow)
		{
			this.emit('onFirstShow', new BaseEvent({ compatData: [this] }));
			this.firstShow = true;
		}

		this.emit('onShow', new BaseEvent({ compatData: [this] }));

		if (this.disableScroll)
		{
			this.#disableTargetScroll();
		}

		this.adjustPosition();

		this.animateOpening(() => {

			if (this.isDestroyed())
			{
				return;
			}

			Dom.removeClass(this.getPopupContainer(), this.animationShowClassName);
			this.emit('onAfterShow', new BaseEvent({ compatData: [this] }));
		});

		this.bindClosingByEsc();

		if (this.isCompatibleMode())
		{
			setTimeout(() => {
				this.bindAutoHide();
			}, 100);
		}
		else
		{
			this.bindAutoHide();
		}
	}

	close(): void
	{
		if (this.isDestroyed() || !this.isShown())
		{
			return;
		}

		this.emit('onClose', new BaseEvent({ compatData: [this] }));

		if (this.isDestroyed())
		{
			return;
		}

		if (this.disableScroll)
		{
			this.#enableTargetScroll();
		}

		this.animateClosing(() => {

			if (this.isDestroyed())
			{
				return;
			}

			this.hideOverlay();

			this.getPopupContainer().style.display = 'none';
			Dom.removeClass(this.getPopupContainer(), '--open');

			Dom.removeClass(this.getPopupContainer(), this.animationCloseClassName);

			this.unbindClosingByEsc();

			if (this.isCompatibleMode())
			{
				setTimeout(() => {
					this.unbindAutoHide();
				}, 0);
			}
			else
			{
				this.unbindAutoHide();
			}

			this.emit('onAfterClose', new BaseEvent({ compatData: [this] }));

			if (!this.isCacheable())
			{
				this.destroy();
			}

		});
	}

	bringToFront(): void
	{
		if (this.isShown())
		{
			ZIndexManager.bringToFront(this.getPopupContainer());
		}
	}

	toggle(): void
	{
		this.isShown() ? this.close() : this.show();
	}

	/**
	 *
	 * @private
	 */
	animateOpening(callback: Function): void
	{
		Dom.removeClass(this.getPopupContainer(), this.animationCloseClassName);

		if (this.animationShowClassName !== null)
		{
			Dom.addClass(this.getPopupContainer(), this.animationShowClassName);

			if (this.animationCloseEventType !== null)
			{
				const eventName = this.animationCloseEventType + 'end';
				this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
					this.removeEventListener(eventName, handleTransitionEnd);
					callback();
				});
			}
			else
			{
				callback();
			}
		}
		else
		{
			callback();
		}
	}

	/**
	 * @private
	 */
	animateClosing(callback: Function): void
	{
		Dom.removeClass(this.getPopupContainer(), this.animationShowClassName);

		if (this.animationCloseClassName !== null)
		{
			Dom.addClass(this.getPopupContainer(), this.animationCloseClassName);

			if (this.animationCloseEventType !== null)
			{
				const eventName = this.animationCloseEventType + 'end';
				this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
					this.removeEventListener(eventName, handleTransitionEnd);
					callback();
				});
			}
			else
			{
				callback();
			}
		}
		else
		{
			callback();
		}
	}

	setAnimation(options: PopupAnimationOptions): void
	{
		if (Type.isPlainObject(options))
		{
			this.animationShowClassName = Type.isStringFilled(options.showClassName) ? options.showClassName : null;
			this.animationCloseClassName = Type.isStringFilled(options.closeClassName) ? options.closeClassName : null;
			this.animationCloseEventType =
				options.closeAnimationType === 'animation' || options.closeAnimationType === 'transition'
					? options.closeAnimationType
					: null
			;
		}
		else if (Type.isStringFilled(options))
		{
			const animationName = options;
			if (animationName === 'fading')
			{
				this.animationShowClassName = 'popup-window-show-animation-opacity';
				this.animationCloseClassName = 'popup-window-close-animation-opacity';
				this.animationCloseEventType = 'animation';
			}
			else if (animationName === 'fading-slide')
			{
				this.animationShowClassName = 'popup-window-show-animation-opacity-transform';
				this.animationCloseClassName = 'popup-window-close-animation-opacity';
				this.animationCloseEventType = 'animation';
			}
			else if (animationName === 'scale')
			{
				this.animationShowClassName = 'popup-window-show-animation-scale';
				this.animationCloseClassName = 'popup-window-close-animation-opacity';
				this.animationCloseEventType = 'animation';
			}
		}
		else if (options === false || options === null)
		{
			this.animationShowClassName = null;
			this.animationCloseClassName = null;
			this.animationCloseEventType = null;
		}
	}

	isShown(): boolean
	{
		return !this.isDestroyed() && this.getPopupContainer().style.display === 'block';
	}

	destroy(): void
	{
		if (this.destroyed)
		{
			return;
		}

		if (this.disableScroll)
		{
			this.#enableTargetScroll();
		}

		this.destroyed = true;

		this.emit('onDestroy', new BaseEvent({ compatData: [this] }));

		this.unbindClosingByEsc();

		if (this.isCompatibleMode())
		{
			setTimeout(() => {
				this.unbindAutoHide();
			}, 0);
		}
		else
		{
			this.unbindAutoHide();
		}

		Event.unbindAll(this);
		Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
		Event.unbind(document, 'mouseup', this.handleDocumentMouseUp);
		Event.unbind(window, 'resize', this.handleResizeWindow);

		this.removeOverlay();

		ZIndexManager.unregister(this.popupContainer);
		this.zIndexComponent = null;

		Dom.remove(this.popupContainer);

		this.popupContainer = null;
		this.contentContainer = null;
		this.closeIcon = null;
		this.titleBar = null;
		this.buttonsContainer = null;
		this.angle = null;
		this.angleArrowElement = null;
		this.resizeIcon = null;
	}

	adjustPosition(bindOptions: {
		forceBindPosition?: boolean,
		forceLeft?: boolean,
		forceTop?: boolean,
		position?: 'top' | 'bootom'
	}): void
	{
		if (bindOptions && typeof (bindOptions) === 'object')
		{
			this.bindOptions = bindOptions;
		}

		const bindElementPos = this.getBindElementPos(this.bindElement);

		if (
			!this.bindOptions.forceBindPosition &&
			this.bindElementPos !== null &&
			bindElementPos.top === this.bindElementPos.top &&
			bindElementPos.left === this.bindElementPos.left
		)
		{
			return;
		}

		this.bindElementPos = bindElementPos;

		const windowSize = bindElementPos.windowSize ? bindElementPos.windowSize : this.getWindowSize();
		const windowScroll = bindElementPos.windowScroll ? bindElementPos.windowScroll : this.getWindowScroll();

		const popupWidth = bindElementPos.popupWidth ? bindElementPos.popupWidth : this.popupContainer.offsetWidth;
		const popupHeight = bindElementPos.popupHeight ? bindElementPos.popupHeight : this.popupContainer.offsetHeight;

		const angleTopOffset = Popup.getOption('angleTopOffset');

		let left =
			this.bindElementPos.left + this.offsetLeft -
			(this.isTopOrBottomAngle() ? Popup.getOption('angleLeftOffset') : 0)
		;

		if (
			!this.bindOptions.forceLeft &&
			(left + popupWidth + this.bordersWidth) >= (windowSize.innerWidth + windowScroll.scrollLeft) &&
			(windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth) > 0)
		{
			const bindLeft = left;
			left = windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth;
			if (this.isTopOrBottomAngle())
			{
				this.setAngle({ offset: bindLeft - left + this.angle.defaultOffset });
			}
		}
		else if (this.isTopOrBottomAngle())
		{
			this.setAngle({ offset: this.angle.defaultOffset + (left < 0 ? left : 0) });
		}

		if (left < 0)
		{
			left = 0;
		}

		let top = 0;

		if (this.bindOptions.position && this.bindOptions.position === 'top')
		{

			top = this.bindElementPos.top - popupHeight - this.offsetTop - (this.isBottomAngle() ? angleTopOffset : 0);
			if (top < 0 || (!this.bindOptions.forceTop && top < windowScroll.scrollTop))
			{
				top = this.bindElementPos.bottom + this.offsetTop;
				if (this.angle !== null)
				{
					top += angleTopOffset;
					this.setAngle({ position: 'top' });
				}
			}
			else if (this.isTopAngle())
			{
				top = top - angleTopOffset + Popup.getOption('positionTopXOffset');
				this.setAngle({ position: 'bottom' });
			}
			else
			{
				top += Popup.getOption('positionTopXOffset');
			}
		}
		else
		{
			top = this.bindElementPos.bottom + this.offsetTop + this.getAngleHeight();

			if (
				!this.bindOptions.forceTop &&
				(top + popupHeight) > (windowSize.innerHeight + windowScroll.scrollTop) &&
				(this.bindElementPos.top - popupHeight - this.getAngleHeight()) >= 0) //Can we place the PopupWindow above the bindElement?
			{
				//The PopupWindow doesn't place below the bindElement. We should place it above.
				top = this.bindElementPos.top - popupHeight;

				if (this.isTopOrBottomAngle())
				{
					top -= angleTopOffset;
					this.setAngle({ position: 'bottom' });
				}

				top += Popup.getOption('positionTopXOffset');

			}
			else if (this.isBottomAngle())
			{
				top += angleTopOffset;
				this.setAngle({ position: 'top' });
			}
		}

		if (top < 0)
		{
			top = 0;
		}

		const event = new PositionEvent();
		event.left = left;
		event.top = top;

		this.emit('onBeforeAdjustPosition', event);

		Dom.adjust(this.popupContainer, {
			style: {
				top: event.top + 'px',
				left: event.left + 'px'
			}
		});
	}

	enterFullScreen(): void
	{
		if (Popup.fullscreenStatus)
		{
			if (document.cancelFullScreen)
			{
				document.cancelFullScreen();
			}
			else if (document.mozCancelFullScreen)
			{
				document.mozCancelFullScreen();
			}
			else if (document.webkitCancelFullScreen)
			{
				document.webkitCancelFullScreen();
			}
		}
		else
		{
			if (this.contentContainer.requestFullScreen)
			{
				this.contentContainer.requestFullScreen();
				Event.bind(window, 'fullscreenchange', this.handleFullScreen);
			}
			else if (this.contentContainer.mozRequestFullScreen)
			{
				this.contentContainer.mozRequestFullScreen();
				Event.bind(window, 'mozfullscreenchange', this.handleFullScreen);
			}
			else if (this.contentContainer.webkitRequestFullScreen)
			{
				this.contentContainer.webkitRequestFullScreen();
				Event.bind(window, 'webkitfullscreenchange', this.handleFullScreen);
			}
			else
			{
				console.log('fullscreen mode is not supported');
			}
		}
	}

	/**
	 * @private
	 */
	handleFullScreen(event): void
	{
		if (Popup.fullscreenStatus)
		{
			Event.unbind(window, 'fullscreenchange', this.handleFullScreen);
			Event.unbind(window, 'webkitfullscreenchange', this.handleFullScreen);
			Event.unbind(window, 'mozfullscreenchange', this.handleFullScreen);

			Popup.fullscreenStatus = false;

			if (!this.isDestroyed())
			{
				Dom.removeClass(this.contentContainer, 'popup-window-fullscreen');
				this.emit('onFullscreenLeave');
				this.adjustPosition();
			}
		}
		else
		{
			Popup.fullscreenStatus = true;

			if (!this.isDestroyed())
			{
				Dom.addClass(this.contentContainer, 'popup-window-fullscreen');
				this.emit('onFullscreenEnter');
				this.adjustPosition();
			}
		}
	}

	/**
	 * @private
	 */
	handleCloseIconClick(event): void
	{
		this.tryCloseByEvent(event);
		event.stopPropagation();
	}

	/**
	 * @private
	 */
	handleContainerClick(event): void
	{
		event.stopPropagation();
	}

	/**
	 * @private
	 */
	handleDocumentKeyUp(event): void
	{
		if (event.keyCode === 27)
		{
			checkEscPressed(this.getZindex(), () => {
				this.close();
			});
		}
	}

	/**
	 * @private
	 */
	handleResizeWindow(): void
	{
		if (this.isShown())
		{
			this.adjustPosition();
			if (this.overlay !== null)
			{
				this.resizeOverlay();
			}
		}
	}

	/**
	 * @private
	 */
	handleMove(offsetX: number, offsetY: number, pageX: number, pageY: number): void
	{
		let left = parseInt(this.popupContainer.style.left) + offsetX;
		let top = parseInt(this.popupContainer.style.top) + offsetY;

		if (typeof (this.params.draggable) === 'object' && this.params.draggable.restrict)
		{
			//Left side
			if (left < 0)
			{
				left = 0;
			}

			let scrollWidth;
			let scrollHeight;
			if (this.isTargetDocumentBody())
			{
				scrollWidth = document.documentElement.scrollWidth;
				scrollHeight = document.documentElement.scrollHeight;
			}
			else
			{
				scrollWidth = this.getTargetContainer().scrollWidth;
				scrollHeight = this.getTargetContainer().scrollHeight;
			}

			//Right side
			const floatWidth = this.popupContainer.offsetWidth;
			const floatHeight = this.popupContainer.offsetHeight;

			if (left > (scrollWidth - floatWidth))
			{
				left = scrollWidth - floatWidth;
			}

			if (top > (scrollHeight - floatHeight))
			{
				top = scrollHeight - floatHeight;
			}

			//Top side
			if (top < 0)
			{
				top = 0;
			}
		}

		this.popupContainer.style.left = left + 'px';
		this.popupContainer.style.top = top + 'px';
	}

	/**
	 * @private
	 */
	_startDrag(event: MouseEvent, options): void
	{
		options = options || {};
		if (Type.isStringFilled(options.cursor))
		{
			this.dragOptions.cursor = options.cursor;
		}

		if (Type.isStringFilled(options.eventName))
		{
			this.dragOptions.eventName = options.eventName;
		}

		if (Type.isFunction(options.callback))
		{
			this.dragOptions.callback = options.callback;
		}

		this.dragPageX = event.pageX;
		this.dragPageY = event.pageY;
		this.dragged = false;

		Event.bind(document, 'mousemove', this.handleDocumentMouseMove);
		Event.bind(document, 'mouseup', this.handleDocumentMouseUp);

		if (document.body.setCapture)
		{
			document.body.setCapture();
		}

		document.body.ondrag = () => false;
		document.body.onselectstart = () => false;
		document.body.style.cursor = this.dragOptions.cursor;
		document.body.style.MozUserSelect = 'none';
		this.popupContainer.style.MozUserSelect = 'none';

		if (this.shouldFrontOnShow())
		{
			this.bringToFront();
		}

		event.preventDefault();
	}

	/**
	 * @private
	 */
	handleDocumentMouseMove(event): void
	{
		if (this.dragPageX === event.pageX && this.dragPageY === event.pageY)
		{
			return;
		}

		this.dragOptions.callback(
			event.pageX - this.dragPageX,
			event.pageY - this.dragPageY,
			event.pageX,
			event.pageY
		);

		this.dragPageX = event.pageX;
		this.dragPageY = event.pageY;

		if (!this.dragged)
		{
			this.emit(`on${this.dragOptions.eventName}Start`, new BaseEvent({ compatData: [this] }));
			this.dragged = true;
		}

		this.emit(`on${this.dragOptions.eventName}`, new BaseEvent({ compatData: [this] }));
	}

	/**
	 * @private
	 */
	handleDocumentMouseUp(event: MouseEvent): void
	{
		if (document.body.releaseCapture)
		{
			document.body.releaseCapture();
		}

		Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
		Event.unbind(document, 'mouseup', this.handleDocumentMouseUp);

		document.body.ondrag = null;
		document.body.onselectstart = null;
		document.body.style.cursor = '';
		document.body.style.MozUserSelect = '';
		this.popupContainer.style.MozUserSelect = '';

		this.emit(`on${this.dragOptions.eventName}End`, new BaseEvent({ compatData: [this] }));
		this.dragged = false;

		event.preventDefault();
	}
}

let escCallbackIndex = -1;
let escCallback = null;

function checkEscPressed(zIndex, callback)
{
	if (zIndex === false)
	{
		if (escCallback && escCallback.length > 0)
		{
			for (let i = 0; i < escCallback.length; i++)
			{
				escCallback[i]();
			}

			escCallback = null;
			escCallbackIndex = -1;
		}
	}
	else
	{
		if (escCallback === null)
		{
			escCallback = [];
			escCallbackIndex = -1;
			setTimeout(() => {
				checkEscPressed(false);
			}, 10);
		}

		if (zIndex > escCallbackIndex)
		{
			escCallbackIndex = zIndex;
			escCallback = [callback];
		}
		else if (zIndex === escCallbackIndex)
		{
			escCallback.push(callback);
		}
	}
}
