import { Type, Loc, Dom, Event, Runtime, Text, Browser, Uri, Tag, type JsonObject } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { ZIndexManager, type ZIndexComponent } from 'main.core.z-index-manager';
import type { Popup } from 'main.popup';

import { Dictionary } from './dictionary';
import { Label } from './label';
import { MessageEvent } from './message-event';
import { SliderEvent } from './slider-event';

import type { SliderOptions, SliderEvents } from './types/slider-options';
import { type MinimizeOptions } from './types/minimize-options';
import { type OuterBoundary } from './types/outer-boundary';

export class Slider
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	#startPosition: 'right' | 'bottom' | 'top' = 'right';
	#startAnimationState: JsonObject = null;
	#endAnimationState: JsonObject = null;
	#currentAnimationState: JsonObject = null;
	#outerBoundary: OuterBoundary = {};
	#hideToolbarOnOpen: boolean = false;
	#designSystemContext: string = '--ui-context-content-light';
	#zIndexComponent: ZIndexComponent = null;
	#autoOffset: boolean = true;

	constructor(url: string, sliderOptions: SliderOptions)
	{
		const options: SliderOptions = Type.isPlainObject(sliderOptions) ? sliderOptions : {};

		this.contentCallback = Type.isFunction(options.contentCallback) ? options.contentCallback : null;
		this.contentCallbackInvoved = false;
		this.contentClassName = Type.isStringFilled(options.contentClassName) ? options.contentClassName : null;
		this.containerClassName = Type.isStringFilled(options.containerClassName) ? options.containerClassName : null;
		this.overlayClassName = Type.isStringFilled(options.overlayClassName) ? options.overlayClassName : null;

		this.url = this.contentCallback ? url : this.refineUrl(url);

		this.offset = null;
		this.hideControls = options.hideControls === true;
		this.width = Type.isNumber(options.width) ? options.width : null;
		this.cacheable = options.cacheable !== false;
		this.autoFocus = options.autoFocus !== false;
		this.printable = options.printable === true;
		this.allowChangeHistory = Type.isBoolean(options.allowChangeHistory) ? options.allowChangeHistory : null;
		this.allowChangeTitle = Type.isBoolean(options.allowChangeTitle) ? options.allowChangeTitle : null;
		this.allowCrossOrigin = options.allowCrossOrigin === true;
		this.data = new Dictionary(Type.isPlainObject(options.data) ? options.data : {});

		this.customLeftBoundary = null;
		this.customRightBoundary = null;
		this.setCustomLeftBoundary(options.customLeftBoundary);
		this.setCustomRightBoundary(options.customRightBoundary);

		this.title = null;
		this.setTitle(options.title);
		/**
		 *
		 * @type {HTMLIFrameElement}
		 */
		this.iframe = null;
		this.iframeSrc = null;
		this.iframeId = null;
		this.requestMethod = (
			Type.isStringFilled(options.requestMethod) && options.requestMethod.toLowerCase() === 'post'
				? 'post'
				: 'get'
		);
		this.requestParams = Type.isPlainObject(options.requestParams) ? options.requestParams : {};

		this.opened = false;
		this.hidden = false;
		this.destroyed = false;
		this.loaded = false;
		this.loadedCnt = 0;

		this.minimizing = false;
		this.maximizing = false;

		this.handleFrameKeyDown = this.handleFrameKeyDown.bind(this);
		this.handleFrameFocus = this.handleFrameFocus.bind(this);
		this.handleFrameUnload = this.handleFrameUnload.bind(this);
		this.handlePopupInit = this.handlePopupInit.bind(this);
		this.handleCrossOriginWindowMessage = this.handleCrossOriginWindowMessage.bind(this);

		this.layout = {
			overlay: null,
			container: null,
			loader: null,
			content: null,
		};

		this.loader = (
			Type.isStringFilled(options.loader) || Type.isElementNode(options.loader)
				? options.loader
				: (Type.isStringFilled(options.typeLoader) ? options.typeLoader : 'default-loader')
		);

		this.animation = null;
		this.animationDuration = Type.isNumber(options.animationDuration) ? options.animationDuration : 200;
		this.overlayBgColor = (
			Type.isStringFilled(options.overlayBgColor) && /^#[\dA-Za-f]{6}$/.test(options.overlayBgColor)
				? options.overlayBgColor
				: '#000000'
		);
		this.overlayOpacity = (
			Type.isNumber(options.overlayOpacity)
				? Math.min(Math.max(options.overlayOpacity, 0), 100)
				: 40
		);

		this.overlayBgCallback = Type.isFunction(options.overlayBgCallback) ? options.overlayBgCallback : null;

		this.#startPosition = (
			['right', 'bottom', 'top'].includes(options.startPosition)
				? options.startPosition
				: this.#startPosition
		);
		this.#outerBoundary = Type.isPlainObject(options.outerBoundary) ? options.outerBoundary : {};
		this.#startAnimationState = this.#getAnimationState('start');
		this.#endAnimationState = this.#getAnimationState('end');
		this.#currentAnimationState = null;

		this.overlayAnimation = false;
		this.animationName = 'sliding';
		this.animationOptions = {};

		this.minimizeOptions = null;
		this.setMinimizeOptions(options.minimizeOptions);
		this.setToolbarOnOpen(options.hideToolbarOnOpen);
		this.setDesignSystemContext(options.designSystemContext);
		this.setAutoOffset(options.autoOffset);

		this.label = new Label(this, {
			className: '--close-label --ui-hoverable',
			iconClass: 'side-panel-label-icon-close ui-icon-set --cross-l',
			iconTitle: Loc.getMessage('MAIN_SIDEPANEL_CLOSE'),
			onclick(label, slider)
			{
				slider.close();
			},
		});

		const labelOptions = Type.isPlainObject(options.label) ? options.label : {};
		this.label.setText(labelOptions.text);
		this.label.setColor(labelOptions.color);
		this.label.setBgColor(labelOptions.bgColor, labelOptions.opacity);

		this.minimizeLabel = new Label(this, {
			className: '--ui-hoverable',
			iconClass: 'side-panel-label-icon-minimize ui-icon-set --o-minimize',
			iconTitle: Loc.getMessage('MAIN_SIDEPANEL_MINIMIZE'),
			onclick: (label, slider) => {
				if (this.isLoaded())
				{
					this.minimize();
				}
			},
			visible: this.areMinimizeOptionsValid(this.minimizeOptions),
		});
		this.newWindowLabel = null;
		this.copyLinkLabel = null;
		this.printLabel = null;

		if (options.newWindowLabel === true && (this.canChangeHistory() || Type.isStringFilled(options.newWindowUrl)))
		{
			this.newWindowLabel = new Label(this, {
				className: '--ui-hoverable',
				iconClass: 'side-panel-label-icon-new-window ui-icon-set --go-to-l',
				iconTitle: Loc.getMessage('MAIN_SIDEPANEL_NEW_WINDOW'),
				onclick(label, slider)
				{
					const newWindowUrl = Type.isStringFilled(options.newWindowUrl) ? options.newWindowUrl : slider.getUrl();
					Object.assign(document.createElement('a'), {
						target: '_blank',
						href: newWindowUrl,
					}).click();
				},
			});
		}

		if (options.copyLinkLabel === true && (this.canChangeHistory() || Type.isStringFilled(options.newWindowUrl)))
		{
			this.copyLinkLabel = new Label(this, {
				className: '--ui-hoverable',
				iconClass: 'side-panel-label-icon-copy-link ui-icon-set --o-link',
				iconTitle: Loc.getMessage('MAIN_SIDEPANEL_COPY_LINK'),
			});

			BX.clipboard.bindCopyClick(
				this.copyLinkLabel.getIconBox(),
				{
					text: () => {
						const link = document.createElement('a');
						link.href = Type.isStringFilled(options.newWindowUrl) ? options.newWindowUrl : this.getUrl();

						return link.href;
					},
				},
			);
		}

		this.printLabel = new Label(this, {
			hidden: !this.isPrintable(),
			className: '--side-panel-label-print --ui-hoverable',
			iconClass: 'side-panel-label-icon-print ui-icon-set --o-printer',
			iconTitle: Loc.getMessage('MAIN_SIDEPANEL_PRINT'),
			onclick: this.handlePrintBtnClick.bind(this),
		});

		// Compatibility
		if (
			this.url.includes('crm.activity.planner/slider.php')
			&& options.events
			&& Type.isFunction(options.events.onOpen)
			&& options.events.compatibleEvents !== false
		)
		{
			const onOpen = options.events.onOpen;
			delete options.events.onOpen;
			options.events.onLoad = function(event) {
				onOpen(event.getSlider());
			};
		}

		[options.events].flat().forEach((events: SliderEvents) => this.#subscribeEvents(events));
	}

	#subscribeEvents(events: SliderEvents): void
	{
		if (Type.isPlainObject(events))
		{
			for (const [eventName, fn] of Object.entries(events))
			{
				if (Type.isFunction(fn))
				{
					EventEmitter.subscribe(this, Slider.getEventFullName(eventName), fn, { compatMode: true });
				}
			}
		}
	}

	static getEventFullName(eventName: string): string
	{
		return `SidePanel.Slider:${eventName}`;
	}

	open(): boolean
	{
		if (this.isOpen())
		{
			return false;
		}

		if (!this.canOpen())
		{
			return false;
		}

		if (this.isDestroyed())
		{
			return false;
		}

		if (this.maximizing)
		{
			this.fireEvent('onMaximizeStart');
		}

		this.createLayout();

		Dom.removeClass(this.getOverlay(), '--closing');
		Dom.addClass(this.getOverlay(), '--opening');

		this.adjustLayout();

		ZIndexManager.bringToFront(this.getOverlay());

		this.opened = true;

		this.fireEvent('onOpenStart');

		this.animateOpening();

		return true;
	}

	close(immediately: boolean, callback: Function): boolean
	{
		if (!this.isOpen())
		{
			return false;
		}

		if (!this.canClose())
		{
			return false;
		}

		if (this.minimizing)
		{
			this.fireEvent('onMinimizeStart');
		}

		this.fireEvent('onCloseStart');

		this.opened = false;

		if (this.isDestroyed())
		{
			return false;
		}

		if (this.animation)
		{
			this.animation.stop();
		}

		Dom.removeClass(this.getOverlay(), '--opening');
		Dom.addClass(this.getOverlay(), '--closing');

		this.fireEvent('onClosing');

		if (immediately === true || Browser.isMobile())
		{
			this.#currentAnimationState = this.#startAnimationState;
			this.completeAnimation(callback);
		}
		else
		{
			this.animation = new BX.easing({
				duration: this.animationDuration,
				start: this.#currentAnimationState,
				finish: this.#startAnimationState,
				step: (state) => {
					this.#currentAnimationState = state;
					this.animateStep(state);
				},
				complete: () => {
					this.completeAnimation(callback);
				},
			});

			// Chrome rendering bug
			Dom.style(this.getContainer(), 'opacity', 0.96);

			if (this.animationName === 'scale' && Type.isStringFilled(this.animationOptions.origin))
			{
				Dom.style(this.getContainer(), 'transform-origin', this.animationOptions.origin);
			}

			this.animation.animate();
		}

		return true;
	}

	minimize(immediately, callback): boolean
	{
		this.minimizing = true;

		const success = this.close(immediately, callback);
		if (!success)
		{
			this.minimizing = false;
		}

		return success;
	}

	isMinimizing(): boolean
	{
		return this.minimizing;
	}

	maximize(): boolean
	{
		this.maximizing = true;
		const success = this.open();
		if (!success)
		{
			this.maximizing = false;
		}

		return success;
	}

	isMaximizing(): boolean
	{
		return this.maximizing;
	}

	setAnimation(type, options): void
	{
		this.animationName = type === 'scale' ? type : 'sliding';
		this.animationOptions = Type.isPlainObject(options) ? options : {};
	}

	setMinimizeOptions(minimizeOptions: MinimizeOptions | null): void
	{
		const showMinimizeLabel = this.areMinimizeOptionsValid(minimizeOptions);

		this.minimizeOptions = minimizeOptions;
		this.minimizeLabel?.setVisible(showMinimizeLabel);
	}

	areMinimizeOptionsValid(minimizeOptions: ?MinimizeOptions): boolean
	{
		return Type.isPlainObject(minimizeOptions)
			&& Type.isStringFilled(minimizeOptions.entityType)
			&& (Type.isStringFilled(minimizeOptions.entityId) || Type.isNumber(minimizeOptions.entityId))
			&& Type.isStringFilled(minimizeOptions.url)
		;
	}

	getMinimizeOptions(): MinimizeOptions | null
	{
		return this.minimizeOptions;
	}

	setToolbarOnOpen(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#hideToolbarOnOpen = flag;
		}
	}

	shouldHideToolbarOnOpen(): boolean
	{
		return this.#hideToolbarOnOpen;
	}

	#getAnimationState(mode: 'start' | 'end'): JsonObject
	{
		const states = {
			right: {
				start: {
					translateX: 100,
					translateY: 0,
					opacity: 0,
					scale: 0,
					progress: 0,
					intensity: 0,
				},
				end: {
					translateX: 0,
					translateY: 0,
					opacity: this.overlayOpacity,
					scale: 100,
					progress: 100,
					intensity: 255,
				},
			},
			bottom: {
				start: {
					translateX: 0,
					translateY: 100,
					opacity: 0,
					scale: 0,
					progress: 0,
					intensity: 0,
				},
				end: {
					translateX: 0,
					translateY: 0,
					opacity: this.overlayOpacity,
					scale: 100,
					progress: 100,
					intensity: 255,
				},
			},
			top: {
				start: {
					translateX: 0,
					translateY: -100,
					opacity: 0,
					scale: 0,
					progress: 0,
					intensity: 0,
				},
				end: {
					translateX: 0,
					translateY: 0,
					opacity: this.overlayOpacity,
					scale: 100,
					progress: 100,
					intensity: 255,
				},
			},
		};

		return states[this.#startPosition][mode];
	}

	getDesignSystemContext(): string
	{
		return this.#designSystemContext;
	}

	setDesignSystemContext(context: string): void
	{
		if (Type.isString(context))
		{
			if (this.layout.container !== null)
			{
				Dom.removeClass(this.layout.container, this.#designSystemContext);
				Dom.addClass(this.layout.container, context);
			}

			this.#designSystemContext = context;
		}
	}

	getUrl(): string
	{
		return this.url;
	}

	setUrl(url: string): void
	{
		if (Type.isStringFilled(url))
		{
			this.url = url;
		}
	}

	focus(): void
	{
		this.getWindow().focus();

		// if (this.isSelfContained())
		// {
		// 	this.getContentContainer().setAttribute("tabindex", "0");
		// 	this.getContentContainer().focus();
		// }
	}

	isOpen(): boolean
	{
		return this.opened;
	}

	getStartPosition(): 'right' | 'bottom' | 'top'
	{
		return this.#startPosition;
	}

	/**
	 * @deprecated
	 */
	setZindex(zIndex: number): void
	{}

	/**
	 * @public
	 * @returns {number}
	 */
	getZindex(): number
	{
		return this.getZIndexComponent().getZIndex();
	}

	getZIndexComponent(): ZIndexComponent | null
	{
		return this.#zIndexComponent;
	}

	setOffset(offset: number | null): void
	{
		if (Type.isNumber(offset) || offset === null)
		{
			this.offset = offset;
		}
	}

	getOffset(): number | null
	{
		return this.offset;
	}

	setAutoOffset(autoOffset: boolean): void
	{
		if (Type.isBoolean(autoOffset))
		{
			this.#autoOffset = autoOffset;
		}
	}

	shouldUseAutoOffset(): boolean
	{
		return this.#autoOffset;
	}

	setWidth(width: number): void
	{
		if (Type.isNumber(width))
		{
			this.width = width;
		}
	}

	getWidth(): number
	{
		return this.width;
	}

	setTitle(title: string): void
	{
		if (Type.isStringFilled(title))
		{
			this.title = title;
		}
	}

	getTitle(): string | null
	{
		return this.title;
	}

	getData(): Dictionary
	{
		return this.data;
	}

	isSelfContained(): boolean
	{
		return this.contentCallback !== null;
	}

	isPostMethod(): boolean
	{
		return this.requestMethod === 'post';
	}

	getRequestParams(): JsonObject
	{
		return this.requestParams;
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getFrameId(): string
	{
		if (this.iframeId === null)
		{
			this.iframeId = `iframe_${Text.getRandom(10).toLowerCase()}`;
		}

		return this.iframeId;
	}

	getWindow(): Window
	{
		return this.iframe ? this.iframe.contentWindow : window;
	}

	getFrameWindow(): Window | null
	{
		return this.iframe ? this.iframe.contentWindow : null;
	}

	isHidden(): boolean
	{
		return this.hidden;
	}

	isCacheable(): boolean
	{
		return this.cacheable;
	}

	isFocusable(): boolean
	{
		return this.autoFocus;
	}

	isPrintable(): boolean
	{
		return this.printable;
	}

	isDestroyed(): boolean
	{
		return this.destroyed;
	}

	isLoaded(): boolean
	{
		return this.loaded;
	}

	canChangeHistory(): boolean
	{
		if (this.allowCrossOrigin || /^\/bitrix\/(components|tools)\//i.test(this.getUrl()))
		{
			return false;
		}

		if (this.allowChangeHistory === null)
		{
			return !this.isSelfContained();
		}

		return this.allowChangeHistory;
	}

	canChangeTitle(): boolean
	{
		if (this.allowChangeTitle === null)
		{
			if (this.getTitle() !== null)
			{
				return true;
			}

			return this.canChangeHistory();
		}

		return this.allowChangeTitle;
	}

	setCacheable(cacheable: boolean = true): void
	{
		this.cacheable = cacheable !== false;
	}

	setAutoFocus(autoFocus: boolean = true): void
	{
		this.autoFocus = autoFocus !== false;
	}

	/**
	 * @public
	 * @param {boolean} printable
	 */
	setPrintable(printable: boolean = true): void
	{
		this.printable = printable !== false;
		if (this.printable)
		{
			this.showPrintBtn();
		}
		else
		{
			this.hidePrintBtn();
		}
	}

	getLoader(): string
	{
		return this.loader;
	}

	showLoader(): void
	{
		const loader = this.getLoader();
		if (!this.layout.loader)
		{
			this.createLoader(loader);
		}

		Dom.style(this.layout.loader, { opacity: 1, display: 'block' });
	}

	closeLoader(): void
	{
		if (this.layout.loader)
		{
			Dom.style(this.layout.loader, { opacity: 0, display: 'none' });
		}
	}

	showCloseBtn(): void
	{
		this.getLabel().show();
	}

	hideCloseBtn(): void
	{
		this.getLabel().hide();
	}

	showOrLightenCloseBtn(): void
	{
		if (Type.isStringFilled(this.getLabel().getText()))
		{
			this.getLabel().showIcon();
		}
		else
		{
			this.getLabel().lightenIcon();
		}
	}

	hideOrDarkenCloseBtn(): void
	{
		if (Type.isStringFilled(this.getLabel().getText()))
		{
			this.getLabel().hideIcon();
		}
		else
		{
			this.getLabel().darkenIcon();
		}
	}

	showPrintBtn(): void
	{
		if (this.printLabel !== null)
		{
			this.printLabel.show();
		}
	}

	hidePrintBtn(): void
	{
		if (this.printLabel !== null)
		{
			this.printLabel.hide();
		}
	}

	showExtraLabels(): void
	{
		Dom.removeClass(this.getExtraLabelsContainer(), '--hidden');
	}

	hideExtraLabels(): void
	{
		Dom.addClass(this.getExtraLabelsContainer(), '--hidden');
	}

	setContentClass(className: string): void
	{
		if (Type.isStringFilled(className))
		{
			this.removeContentClass();
			this.contentClassName = className;
			Dom.addClass(this.getContentContainer(), className);
		}
	}

	removeContentClass(): void
	{
		if (this.contentClassName !== null)
		{
			Dom.removeClass(this.getContentContainer(), this.contentClassName);
			this.contentClassName = null;
		}
	}

	setContainerClass(className: string): void
	{
		if (Type.isStringFilled(className))
		{
			this.removeContainerClass();
			this.containerClassName = className;
			Dom.addClass(this.getContainer(), className);
		}
	}

	removeContainerClass(): void
	{
		if (this.containerClassName !== null)
		{
			Dom.removeClass(this.getContainer(), this.containerClassName);
			this.containerClassName = null;
		}
	}

	setOverlayClass(className: string): void
	{
		if (Type.isStringFilled(className))
		{
			this.removeOverlayClass();
			this.overlayClassName = className;
			Dom.addClass(this.getOverlay(), className);
		}
	}

	removeOverlayClass(): void
	{
		if (this.overlayClassName !== null)
		{
			Dom.removeClass(this.getOverlay(), this.overlayClassName);
			this.overlayClassName = null;
		}
	}

	applyHacks(): void
	{
		// You can override this method in a derived class
	}

	applyPostHacks(): void
	{
		// You can override this method in a derived class
	}

	resetHacks(): void
	{
		// You can override this method in a derived class
	}

	resetPostHacks(): void
	{
		// You can override this method in a derived class
	}

	getTopBoundary(): number
	{
		return 0;
	}

	/**
	 * @protected
	 */
	calculateLeftBoundary(): number
	{
		const customLeftBoundary = this.getCustomLeftBoundary();
		if (customLeftBoundary !== null)
		{
			return customLeftBoundary;
		}

		return this.getLeftBoundary();
	}

	getLeftBoundary(): number
	{
		const windowWidth = Browser.isMobile() ? window.innerWidth : document.documentElement.clientWidth;

		return windowWidth < 1160 ? this.getMinLeftBoundary() : 300;
	}

	getMinLeftBoundary(): number
	{
		return this.hideControls && this.getCustomLeftBoundary() !== null ? 0 : 65;
	}

	/**
	 * @internal
	 */
	getLeftBoundaryOffset(): number
	{
		const offset = this.getOffset() === null ? 0 : this.getOffset();

		return Math.max(this.calculateLeftBoundary(), this.getMinLeftBoundary()) + offset;
	}

	setCustomLeftBoundary(boundary: number | null): void
	{
		if (Type.isNumber(boundary) || boundary === null)
		{
			this.customLeftBoundary = boundary;
		}
	}

	getCustomLeftBoundary(): number
	{
		return this.customLeftBoundary;
	}

	setCustomRightBoundary(boundary: number | null): void
	{
		if (Type.isNumber(boundary) || boundary === null)
		{
			this.customRightBoundary = boundary;
		}
	}

	getCustomRightBoundary(): number
	{
		return this.customRightBoundary;
	}

	/**
	 * @protected
	 */
	calculateRightBoundary(): number
	{
		const customRightBoundary = this.getCustomRightBoundary();
		if (customRightBoundary !== null)
		{
			return -window.pageXOffset + customRightBoundary;
		}

		return this.getRightBoundary();
	}

	getRightBoundary(): number
	{
		return -window.pageXOffset;
	}

	getOuterBoundary(): OuterBoundary
	{
		return this.#outerBoundary;
	}

	calculateOuterBoundary(): OuterBoundary
	{
		// You can override this method in a derived class
	}

	#calculateOuterBoundary(): OuterBoundary
	{
		const outerBoundary = this.calculateOuterBoundary();

		return Runtime.merge(Type.isPlainObject(outerBoundary) ? outerBoundary : {}, this.getOuterBoundary());
	}

	destroy(): boolean
	{
		if (this.isDestroyed())
		{
			return false;
		}

		this.firePageEvent('onDestroy');
		this.fireFrameEvent('onDestroy');

		const frameWindow = this.getFrameWindow();
		if (frameWindow && !this.allowCrossOrigin)
		{
			Event.unbind(frameWindow, 'keydown', this.handleFrameKeyDown);
			Event.unbind(frameWindow, 'focus', this.handleFrameFocus);
			Event.unbind(frameWindow, 'unload', this.handleFrameUnload);
		}
		else if (this.allowCrossOrigin)
		{
			Event.unbind(window, 'message', this.handleCrossOriginWindowMessage);
		}

		EventEmitter.unsubscribe('BX.Main.Popup:onInit', this.handlePopupInit);
		ZIndexManager.unregister(this.layout.overlay);
		this.#zIndexComponent = null;

		Dom.remove(this.layout.overlay);

		this.layout.container = null;
		this.layout.overlay = null;
		this.layout.content = null;
		this.layout.closeBtn = null;
		this.layout.loader = null;

		this.#refs = null;

		this.iframe = null;
		this.destroyed = true;

		EventEmitter.unsubscribeAll(this);

		this.firePageEvent('onDestroyComplete');

		return true;
	}

	/**
	 * @internal
	 */
	hide(): void
	{
		this.hidden = true;

		Dom.style(this.getContainer(), 'display', 'none');
		Dom.style(this.getOverlay(), 'display', 'none');
	}

	/**
	 * @internal
	 */
	unhide(): void
	{
		this.hidden = false;

		Dom.style(this.getContainer(), 'display', null);
		Dom.style(this.getOverlay(), 'display', null);
	}

	/**
	 * @public
	 */
	reload(): void
	{
		this.loaded = false;
		if (this.isSelfContained())
		{
			this.contentCallbackInvoved = false;
			this.showLoader();
			this.setContent();
		}
		else
		{
			this.showLoader();
			this.getFrameWindow().location.reload();
		}
	}

	/**
	 * @public
	 */
	adjustLayout(): void
	{
		const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		const windowHeight = Browser.isMobile() ? window.innerHeight : document.documentElement.clientHeight;

		let topBoundary = this.getTopBoundary();
		const isTopBoundaryVisible = topBoundary - scrollTop > 0;
		topBoundary = isTopBoundaryVisible ? topBoundary : scrollTop;

		const height = isTopBoundaryVisible > 0 ? windowHeight - topBoundary + scrollTop : windowHeight;
		const leftBoundary = this.getLeftBoundaryOffset();
		const rightBoundary = this.calculateRightBoundary();

		Dom.style(
			this.getOverlay(),
			{
				left: `${window.pageXOffset}px`,
				top: `${topBoundary}px`,
				right: `${rightBoundary}px`,
				height: `${height}px`,
			},
		);

		const { right = null, top = null, bottom = null } = this.#calculateOuterBoundary();

		Dom.style(
			this.getContainer(),
			{
				width: `calc(100% - ${leftBoundary + (right === null ? 0 : right)}px)`,
				maxWidth: this.getWidth() === null ? null : `${this.getWidth()}px`,
				right: right === null ? null : `${right}px`,
				top: top === null ? null : `${top}px`,
				bottom: bottom === null ? null : `${bottom}px`,
				// height: `${height}px`, // height: '100%',
			},
		);

		this.getLabel().adjustLayout();
	}

	/**
	 * @private
	 */
	createLayout()
	{
		if (this.layout.overlay !== null && this.layout.overlay.parentNode)
		{
			return;
		}

		if (this.isSelfContained())
		{
			Dom.addClass(this.getOverlay(), '--self-contained');
			Dom.append(this.getOverlay(), document.body);

			this.setContent();

			EventEmitter.subscribe('BX.Main.Popup:onInit', this.handlePopupInit);
		}
		else
		{
			Dom.append(this.getFrame(), this.getContentContainer());
			Dom.append(this.getOverlay(), document.body);
			this.setFrameSrc(); // setFrameSrc must be below than appendChild, otherwise POST method fails.
		}

		this.#zIndexComponent = ZIndexManager.register(this.getOverlay());
	}

	getFrame(): HTMLIFrameElement
	{
		if (this.iframe !== null)
		{
			return this.iframe;
		}

		this.iframe = Dom.create('iframe', {
			attrs: {
				referrerpolicy: this.allowCrossOrigin ? 'strict-origin' : false,
				src: 'about:blank',
				frameborder: '0',
			},
			props: {
				className: 'side-panel-iframe',
				name: this.getFrameId(),
				id: this.getFrameId(),
			},
			events: {
				load: this.handleFrameLoad.bind(this),
			},
		});

		return this.iframe;
	}

	getOverlay(): HTMLElement
	{
		if (this.layout.overlay !== null)
		{
			return this.layout.overlay;
		}

		const overlayClass = this.overlayClassName === null ? '' : ` ${this.overlayClassName}`;

		this.layout.overlay = Dom.create('div', {
			props: {
				className: `side-panel side-panel-overlay${overlayClass}`,
			},
			events: {
				mousedown: this.handleOverlayClick.bind(this),
			},
			children: [
				this.getContainer(),
			],
		});

		return this.layout.overlay;
	}

	unhideOverlay(): void
	{
		Dom.removeClass(this.getOverlay(), '--hidden');
	}

	hideOverlay(): void
	{
		Dom.addClass(this.getOverlay(), '--hidden');
	}

	hideShadow(): void
	{
		Dom.removeClass(this.getContainer(), 'side-panel-show-shadow');
	}

	showShadow(): void
	{
		Dom.addClass(this.getContainer(), 'side-panel-show-shadow');
	}

	setOverlayBackground(): void
	{
		if (this.overlayBgCallback === null)
		{
			const opacity = parseInt(this.overlayOpacity / 100 * 255, 10).toString(16).padStart(2, 0);
			Dom.style(this.getOverlay(), 'background-color', `${this.overlayBgColor}${opacity}`);
		}
		else
		{
			const state = this.#getAnimationState('end');
			Dom.style(this.getOverlay(), 'background', this.overlayBgCallback(state, this));
		}
	}

	setOverlayAnimation(animate: boolean): void
	{
		if (Type.isBoolean(animate))
		{
			this.overlayAnimation = animate;
		}
	}

	getOverlayAnimation(): boolean
	{
		return this.overlayAnimation;
	}

	getOverlayBgColor(): string
	{
		return this.overlayBgColor;
	}

	getOverlayOpacity(): number
	{
		return this.overlayOpacity;
	}

	getContainer(): HTMLElement
	{
		if (this.layout.container !== null)
		{
			return this.layout.container;
		}

		const content = Tag.render`
			<div class="side-panel-content-wrapper">${this.getContentContainer()}</div>
		`;

		this.layout.container = Tag.render`
			<div class="side-panel side-panel-container">
				${
					this.hideControls
						? content
						: [content, this.getLabelsContainer()]
				}
			</div>
		`;

		Dom.addClass(this.layout.container, this.getDesignSystemContext());
		Dom.addClass(this.layout.container, this.containerClassName);

		return this.layout.container;
	}

	getContentContainer(): HTMLElement
	{
		if (this.layout.content !== null)
		{
			return this.layout.content;
		}

		const contentClass = this.contentClassName === null ? '' : ` ${this.contentClassName}`;

		this.layout.content = Dom.create('div', {
			props: {
				className: `side-panel-content-container${contentClass}`,
			},
		});

		return this.layout.content;
	}

	getLabelsContainer(): HTMLElement
	{
		return this.#refs.remember('labels-container', () => {
			return Dom.create('div', {
				props: {
					className: 'side-panel-labels',
				},
				children: [
					this.getLabel().getContainer(),
					this.getExtraLabelsContainer(),
				],
			})
			;
		});
	}

	getExtraLabelsContainer(): HTMLElement
	{
		return this.#refs.remember('icon-labels', () => {
			return Dom.create('div', {
				props: {
					className: 'side-panel-extra-labels',
				},
				children: [
					this.minimizeLabel.getContainer(),
					this.newWindowLabel ? this.newWindowLabel.getContainer() : null,
					this.copyLinkLabel ? this.copyLinkLabel.getContainer() : null,
					this.printLabel ? this.printLabel.getContainer() : null,
				],
			});
		});
	}

	getCloseBtn(): HTMLElement
	{
		return this.getLabel().getIconBox();
	}

	getLabel(): Label
	{
		return this.label;
	}

	getNewWindowLabel(): Label | null
	{
		return this.newWindowLabel;
	}

	getCopyLinkLabel(): Label | null
	{
		return this.copyLinkLabel;
	}

	getMinimizeLabel(): Label | null
	{
		return this.minimizeLabel;
	}

	getPrintLabel(): Label | null
	{
		return this.printLabel;
	}

	/**
	 * @private
	 */
	setContent(): void
	{
		if (this.contentCallbackInvoved)
		{
			return;
		}

		this.contentCallbackInvoved = true;

		Dom.clean(this.getContentContainer());

		let promise = this.contentCallback(this);
		const isPromiseReturned = (
			promise
			&& (
				Object.prototype.toString.call(promise) === '[object Promise]'
				|| promise.toString() === '[object BX.Promise]'
			)
		);

		if (!isPromiseReturned)
		{
			promise = Promise.resolve(promise);
		}

		promise
			.then((result) => {
				if (this.isDestroyed())
				{
					return;
				}

				if (Type.isPlainObject(result) && Type.isStringFilled(result.html))
				{
					Runtime.html(this.getContentContainer(), result.html)
						.then(() => {
							this.removeLoader();
							this.loaded = true;
							this.firePageEvent('onLoad');
						})
						.catch((reason) => {
							this.removeLoader();
							this.getContentContainer().innerHTML = reason;
						});
				}
				else
				{
					if (Type.isDomNode(result))
					{
						Dom.append(result, this.getContentContainer());
					}
					else if (Type.isStringFilled(result))
					{
						this.getContentContainer().innerHTML = result;
					}

					this.removeLoader();
					this.loaded = true;
					this.firePageEvent('onLoad');
				}
			})
			.catch((reason) => {
				this.removeLoader();
				this.getContentContainer().innerHTML = reason;
			})
		;
	}

	/**
	 * @private
	 */
	setFrameSrc(): void
	{
		if (this.iframeSrc === this.getUrl())
		{
			return;
		}

		const url = Uri.addParam(this.getUrl(), { IFRAME: 'Y', IFRAME_TYPE: 'SIDE_SLIDER' });
		if (this.isPostMethod())
		{
			const form = document.createElement('form');
			form.method = 'POST';
			form.action = url;
			form.target = this.getFrameId();
			Dom.style(form, 'display', 'none');

			BX.util.addObjectToForm(this.getRequestParams(), form);

			Dom.append(form, document.body);

			form.submit();

			Dom.remove(form);
		}
		else
		{
			this.iframeSrc = this.getUrl();
			this.iframe.src = url;
		}

		this.loaded = false;
		this.listenIframeLoading();
	}

	/**
	 * @private
	 */
	createLoader(sliderLoader: string | HTMLElement): void
	{
		Dom.remove(this.layout.loader);

		const loader = (
			Type.isStringFilled(sliderLoader) || Type.isElementNode(sliderLoader)
				? sliderLoader
				: 'default-loader'
		);

		const oldLoaders = [
			'task-new-loader',
			'task-edit-loader',
			'task-view-loader',
			'crm-entity-details-loader',
			'crm-button-view-loader',
			'crm-webform-view-loader',
			'create-mail-loader',
			'view-mail-loader',
		];

		if (Type.isElementNode(loader))
		{
			this.layout.loader = this.createHTMLLoader(loader);
		}
		else if (oldLoaders.includes(loader) && this.loaderExists(loader))
		{
			this.layout.loader = this.createOldLoader(loader);
		}
		else if (loader.charAt(0) === '/')
		{
			this.layout.loader = this.createSvgLoader(loader);
		}
		else
		{
			const matches = loader.match(/^([\w.-]+):([\w.-]+)$/i);
			if (matches)
			{
				const moduleId = matches[1];
				const svgName = matches[2];
				const svg = `/bitrix/images/${moduleId}/slider/${svgName}.svg`;
				this.layout.loader = this.createSvgLoader(svg);
			}
			else
			{
				this.layout.loader = this.createDefaultLoader();
			}
		}

		Dom.append(this.layout.loader, this.getContainer());
	}

	createSvgLoader(svg): HTMLElement
	{
		return Dom.create('div', {
			props: {
				className: 'side-panel-loader',
			},
			children: [
				Dom.create('div', {
					props: {
						className: 'side-panel-loader-container',
					},
					style: {
						backgroundImage: `url("${svg}")`,
					},
				}),
			],
		});
	}

	createDefaultLoader(): HTMLElement
	{
		return Dom.create('div', {
			props: {
				className: 'side-panel-loader',
			},
			children: [
				Dom.create('div', {
					props: {
						className: 'side-panel-default-loader-container',
					},
					html:
						'<svg class="side-panel-default-loader-circular" viewBox="25 25 50 50">'
						+ '<circle '
							+ 'class="side-panel-default-loader-path" '
							+ 'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"'
						+ '/>'
						+ '</svg>',
				}),
			],
		});
	}

	/**
	 * @private
	 */
	createOldLoader(loader: string): HTMLElement
	{
		if (loader === 'crm-entity-details-loader')
		{
			return Dom.create('div', {
				props: {
					className: `side-panel-loader ${loader}`,
				},
				children: [
					Dom.create('img', {
						attrs: {
							src:
								'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX'
								+ '///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC',
						},
						props: {
							className: 'side-panel-loader-mask top',
						},
					}),
					Dom.create('div', {
						props: {
							className: 'side-panel-loader-bg left',
						},
						children: [
							Dom.create('img', {
								attrs: {
									src:
										'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1B'
										+ 'MVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC',
								},
								props: {
									className: 'side-panel-loader-mask left',
								},
							}),
						],
					}),
					Dom.create('div', {
						props: {
							className: 'side-panel-loader-bg right',
						},
						children: [
							Dom.create('img', {
								attrs: {
									src:
										'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BM'
										+ 'VEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC',
								},
								props: {
									className: 'side-panel-loader-mask right',
								},
							}),
						],
					}),
				],
			});
		}

		return Dom.create('div', {
			props: {
				className: `side-panel-loader ${loader}`,
			},
			children: [
				Dom.create('img', {
					attrs: {
						src:
							'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX'
							+ '///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC',
					},
					props: {
						className: 'side-panel-loader-mask left',
					},
				}),
				Dom.create('img', {
					attrs: {
						src:
							'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA'
							+ '1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC',
					},
					props: {
						className: 'side-panel-loader-mask right',
					},
				}),
			],
		});
	}

	/**
	 * @private
	 */
	createHTMLLoader(loader: HTMLElement): HTMLElement
	{
		return Dom.create('div', {
			children: [
				loader,
			],
		});
	}

	loaderExists(loader): boolean
	{
		if (!Type.isStringFilled(loader))
		{
			return false;
		}

		for (let i = 0; i < document.styleSheets.length; i++)
		{
			const style = document.styleSheets[i];
			if (!Type.isStringFilled(style.href) || !style.href.includes('sidepanel'))
			{
				continue;
			}

			let rules = null;
			try
			{
				rules = style.rules || style.cssRules;
			}
			catch
			{
				try
				{
					rules = style.cssRules;
				}
				catch
				{
					rules = [];
				}
			}

			for (const rule of rules)
			{
				if (Type.isStringFilled(rule.selectorText) && rule.selectorText.includes(loader))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @private
	 */
	removeLoader()
	{
		Dom.remove(this.layout.loader);
		this.layout.loader = null;
	}

	/**
	 * @private
	 */
	animateOpening()
	{
		if (this.animation)
		{
			this.animation.stop();
		}

		this.fireEvent('onOpening');

		if (Browser.isMobile())
		{
			this.#currentAnimationState = this.#endAnimationState;
			this.animateStep(this.#currentAnimationState);
			this.completeAnimation();

			return;
		}

		this.#currentAnimationState = (
			this.#currentAnimationState === null
				? this.#startAnimationState
				: this.#currentAnimationState
		);

		this.animation = new BX.easing({
			duration: this.animationDuration,
			start: this.#currentAnimationState,
			finish: this.#endAnimationState,
			step: (state) => {
				this.#currentAnimationState = state;
				this.animateStep(state);
			},
			complete: () => {
				this.completeAnimation();
			},
		});

		if (this.animationName === 'scale' && Type.isStringFilled(this.animationOptions.origin))
		{
			Dom.style(this.getContainer(), 'transform-origin', this.animationOptions.origin);
		}

		this.animation.animate();
	}

	/**
	 * @private
	 * @param {object} state
	 */
	animateStep(state)
	{
		if (this.animationName === 'scale')
		{
			Dom.style(this.getContainer(), 'transform', `scale(${state.scale / 100})`);
		}
		else
		{
			Dom.style(this.getContainer(), 'transform', `translate(${state.translateX}%, ${state.translateY}%)`);
		}

		if (this.getOverlayAnimation())
		{
			if (this.overlayBgCallback === null)
			{
				const opacity = parseInt(state.opacity / 100 * 255, 10).toString(16).padStart(2, 0);
				Dom.style(this.getOverlay(), 'background-color', `${this.overlayBgColor}${opacity}`);
			}
			else
			{
				Dom.style(this.getOverlay(), 'background', this.overlayBgCallback(state, this));
			}
		}
	}

	/**
	 * @private
	 * @param callback
	 */
	completeAnimation(callback)
	{
		this.animation = null;
		if (this.isOpen())
		{
			this.#currentAnimationState = this.#endAnimationState;
			this.maximizing = false;

			Dom.removeClass(this.getOverlay(), '--opening');
			Dom.addClass(this.getOverlay(), '--open');
			if (this.animationName === 'scale')
			{
				const state = this.#getAnimationState('end');
				Dom.style(
					this.getContainer(),
					{
						'transform-origin': null,
						transform: `translate(${state.translateX}%, ${state.translateY}%)`,
					},
				);
			}

			this.firePageEvent('onBeforeOpenComplete');
			this.fireFrameEvent('onBeforeOpenComplete');

			this.firePageEvent('onOpenComplete');
			this.fireFrameEvent('onOpenComplete');

			if (!this.isLoaded())
			{
				this.showLoader();
			}

			if (this.isFocusable())
			{
				this.focus();
			}
		}
		else
		{
			this.#currentAnimationState = this.#startAnimationState;
			this.minimizing = false;

			Dom.removeClass(this.getOverlay(), '--open --opening --closing');
			if (this.animationName === 'scale')
			{
				const state = this.#getAnimationState('start');
				Dom.style(
					this.getContainer(),
					{
						'transform-origin': null,
						transform: `translate(${state.translateX}%, ${state.translateY}%)`,
					},
				);
			}

			Dom.style(
				this.getContainer(),
				{
					width: null,
					right: null,
					opacity: null,
					'max-width': null,
					'min-width': null,
				},
			);

			Dom.style(this.getCloseBtn(), 'opacity', null);

			this.firePageEvent('onBeforeCloseComplete');
			this.fireFrameEvent('onBeforeCloseComplete');

			this.firePageEvent('onCloseComplete');
			this.fireFrameEvent('onCloseComplete');

			if (Type.isFunction(callback))
			{
				callback(this);
			}

			if (!this.isCacheable())
			{
				this.destroy();
			}
		}
	}

	/**
	 * @internal
	 */
	firePageEvent(eventName: string): SliderEvent
	{
		const sliderEvent = this.getEvent(eventName);
		if (sliderEvent === null)
		{
			throw new Error('\'eventName\' is invalid.');
		}

		EventEmitter.emit(
			this,
			sliderEvent.getFullName().toLowerCase(),
			new BaseEvent({ data: [sliderEvent], compatData: [sliderEvent] }),
		);

		// Events for compatibility
		if (['onClose', 'onOpen'].includes(eventName))
		{
			EventEmitter.emit(`BX.Bitrix24.PageSlider:${eventName}`, new BaseEvent({ data: [this], compatData: [this] }));
			EventEmitter.emit(`Bitrix24.Slider:${eventName}`, new BaseEvent({ data: [this], compatData: [this] }));
		}

		return sliderEvent;
	}

	/**
	 * @internal
	 */
	fireFrameEvent(eventName): SliderEvent
	{
		const sliderEvent = this.getEvent(eventName);
		if (sliderEvent === null)
		{
			throw new Error('\'eventName\' is invalid.');
		}

		if (this.allowCrossOrigin)
		{
			return null;
		}

		const frameWindow = this.getFrameWindow();
		if (frameWindow && frameWindow.BX && frameWindow.BX.onCustomEvent)
		{
			frameWindow.BX.onCustomEvent(this, sliderEvent.getFullName(), [sliderEvent]);

			// Events for compatibility
			if (['onClose', 'onOpen'].includes(eventName))
			{
				frameWindow.BX.onCustomEvent(`BX.Bitrix24.PageSlider:${eventName}`, [this]);
				frameWindow.BX.onCustomEvent(`Bitrix24.Slider:${eventName}`, [this]); // Compatibility
			}
		}

		return sliderEvent;
	}

	fireEvent(eventName)
	{
		this.firePageEvent(eventName);
		this.fireFrameEvent(eventName);
	}

	/**
	 * @private
	 */
	getEvent(eventName: string | SliderEvent): SliderEvent | null
	{
		let event = null;
		if (Type.isStringFilled(eventName))
		{
			event = new SliderEvent();
			event.setSlider(this);
			event.setName(eventName);
		}
		else if (eventName instanceof SliderEvent)
		{
			event = eventName;
		}

		return event;
	}

	/**
	 * @private
	 */
	canOpen(): boolean
	{
		return this.canAction('open');
	}

	/**
	 * @private
	 */
	canClose(): boolean
	{
		return this.canAction('close');
	}

	/**
	 * @package
	 * @returns {boolean}
	 */
	canCloseByEsc(): boolean
	{
		return this.canAction('closeByEsc');
	}

	/**
	 * @private
	 * @param {string} action
	 * @returns {boolean}
	 */
	canAction(action: string): boolean
	{
		if (!Type.isStringFilled(action))
		{
			return false;
		}

		const eventName = `on${action.charAt(0).toUpperCase()}${action.slice(1)}`;

		const pageEvent = this.firePageEvent(eventName);
		const frameEvent = this.fireFrameEvent(eventName);

		return pageEvent.isActionAllowed() && (!frameEvent || frameEvent.isActionAllowed());
	}

	/**
	 * @private
	 */
	handleCrossOriginWindowMessage(event: MessageEvent)
	{
		const frameUrl = new URL(this.url);
		const eventUrl = new URL(event.origin);
		if (eventUrl.origin !== frameUrl.origin)
		{
			return;
		}

		const message = { type: '', data: undefined };
		if (Type.isString(event.data))
		{
			message.type = event.data;
		}
		else if (Type.isPlainObject(event.data))
		{
			message.type = event.data.type;
			message.data = event.data.data;
		}

		switch (message.type)
		{
			case 'BX:SidePanel:close':
			{
				this.close();

				break;
			}

			case 'BX:SidePanel:load:force':
			{
				if (!this.isLoaded() && !this.isDestroyed())
				{
					this.handleFrameLoad();
				}

				break;
			}

			case 'BX:SidePanel:data:send':
			{
				const pageEvent = new MessageEvent({ sender: this, data: message.data });
				pageEvent.setName('onXDomainMessage');
				this.firePageEvent(pageEvent);

				break;
			}

			default:
				// No default
		}
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameLoad(event)
	{
		if (this.loaded)
		{
			return;
		}

		const frameWindow = this.iframe.contentWindow;
		const iframeLocation = frameWindow.location;

		if (this.allowCrossOrigin)
		{
			Event.bind(window, 'message', this.handleCrossOriginWindowMessage);
		}

		try
		{
			if (iframeLocation.toString() === 'about:blank')
			{
				return;
			}
		}
		catch (e)
		{
			if (this.allowCrossOrigin)
			{
				this.loaded = true;
				this.closeLoader();

				return;
			}

			// eslint-disable-next-line no-console
			console.warn('SidePanel: Try to use "allowCrossOrigin: true" option.');

			throw e;
		}

		Event.bind(frameWindow, 'keydown', this.handleFrameKeyDown);
		Event.bind(frameWindow, 'focus', this.handleFrameFocus);
		Event.bind(frameWindow, 'unload', this.handleFrameUnload);

		if (Browser.isMobile())
		{
			frameWindow.document.body.style.paddingBottom = `${window.innerHeight * 2 / 3}px`;
		}

		const iframeUrl = iframeLocation.pathname + iframeLocation.search + iframeLocation.hash;
		this.iframeSrc = this.refineUrl(iframeUrl);
		this.url = this.iframeSrc;

		if (this.isPrintable())
		{
			this.injectPrintStyles();
		}

		this.loaded = true;
		this.loadedCnt++;

		if (this.loadedCnt > 1)
		{
			this.firePageEvent('onLoad');
			this.fireFrameEvent('onLoad');

			this.firePageEvent('onReload');
			this.fireFrameEvent('onReload');
		}
		else
		{
			this.firePageEvent('onLoad');
			this.fireFrameEvent('onLoad');
		}

		if (this.isFocusable())
		{
			this.focus();
		}

		this.closeLoader();
	}

	/**
	 * @private
	 */
	listenIframeLoading()
	{
		if (this.allowCrossOrigin)
		{
			return;
		}

		const isLoaded = setInterval(() => {
			if (this.isLoaded() || this.isDestroyed())
			{
				clearInterval(isLoaded);

				return;
			}

			if (this.iframe.contentWindow.location.toString() === 'about:blank')
			{
				return;
			}

			if (
				this.iframe.contentWindow.document.readyState === 'complete'
				|| this.iframe.contentWindow.document.readyState === 'interactive'
			)
			{
				clearInterval(isLoaded);
				this.handleFrameLoad();
			}
		}, 200);
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameUnload(event)
	{
		this.loaded = false;
		this.listenIframeLoading();
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameKeyDown(event)
	{
		if (event.keyCode !== 27)
		{
			return;
		}

		const framePopupManager = this.getWindow().BX?.Main?.PopupManager;
		if (framePopupManager)
		{
			const popups = framePopupManager.getPopups();
			for (const popup of popups)
			{
				if (popup.isShown())
				{
					return;
				}
			}
		}

		const centerX = this.getWindow().document.documentElement.clientWidth / 2;
		const centerY = this.getWindow().document.documentElement.clientHeight / 2;
		const element = this.getWindow().document.elementFromPoint(centerX, centerY);

		if (Dom.hasClass(element, 'bx-core-dialog-overlay') || Dom.hasClass(element, 'bx-core-window'))
		{
			return;
		}

		if (element.closest('.bx-core-window'))
		{
			return;
		}

		this.firePageEvent('onEscapePress');
		this.fireFrameEvent('onEscapePress');
	}

	isOnTopOfPopup(popup: Popup): boolean
	{
		const sameStack = this.getZIndexComponent().getStack() === popup.getZIndexComponent().getStack();
		const popupOnTop = sameStack && popup.getZindex() > this.getZindex();
		let popupInside = this.getContainer().contains(popup.getPopupContainer());
		if (this.getFrameWindow())
		{
			popupInside = this.getFrameWindow().document.contains(popup.getPopupContainer());
		}

		return !(popup.isShown() && (popupOnTop || popupInside));
	}

	/**
	 * @private
	 * @param {BaseEvent} event
	 */
	handlePopupInit(event)
	{
		const data = event.getCompatData();
		const bindElement = data[1];
		const params = data[2];

		if (
			!Type.isElementNode(params.targetContainer)
			&& Type.isElementNode(bindElement)
			&& this.getContentContainer().contains(bindElement)
		)
		{
			params.targetContainer = this.getContentContainer();
		}
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameFocus(event)
	{
		this.firePageEvent('onFrameFocus');
	}

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleOverlayClick(event)
	{
		if (event.target === this.getOverlay())
		{
			if (this.animation === null)
			{
				this.close();
				event.stopPropagation();
			}
			else
			{
				event.preventDefault();
			}
		}
	}

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handlePrintBtnClick(event)
	{
		if (this.isSelfContained())
		{
			const frame = document.createElement('iframe');
			frame.src = 'about:blank';
			frame.name = 'sidepanel-print-frame';

			Dom.style(frame, 'display', 'none');
			Dom.append(frame, document.body);

			const frameWindow = frame.contentWindow;
			const frameDoc = frameWindow.document;
			frameDoc.open();
			frameDoc.write('<html><head>');

			let headTags = '';
			const links = document.head.querySelectorAll('link, style');
			for (const link of links)
			{
				headTags += link.outerHTML;
			}

			headTags += '<style>html, body { background: #fff !important; height: 100%; }</style>';

			frameDoc.write(headTags);

			frameDoc.write('</head><body>');
			frameDoc.write(this.getContentContainer().innerHTML);
			frameDoc.write('</body></html>');
			frameDoc.close();

			frameWindow.focus();
			frameWindow.print();

			setTimeout(() => {
				Dom.remove(frame);
				window.focus();
			}, 1000);
		}
		else
		{
			this.focus();
			this.getFrameWindow().print();
		}
	}

	/**
	 * @private
	 */
	injectPrintStyles()
	{
		const frameDocument = this.getFrameWindow().document;

		let bodyClass = '';

		const classList = frameDocument.body.classList;
		for (const className of classList)
		{
			bodyClass += `.${className}`;
		}

		const bodyStyle = `@media print { body${bodyClass} { `
			+ 'background: #fff !important; '
			+ '-webkit-print-color-adjust: exact;'
			+ 'color-adjust: exact; '
			+ '} }';

		const style = frameDocument.createElement('style');
		style.type = 'text/css';
		if (style.styleSheet)
		{
			style.styleSheet.cssText = bodyStyle;
		}
		else
		{
			style.appendChild(frameDocument.createTextNode(bodyStyle));
		}

		frameDocument.head.appendChild(style);
	}

	refineUrl(url: string): string
	{
		if (Type.isStringFilled(url) && /IFRAME/.test(url))
		{
			return Uri.removeParam(url, ['IFRAME', 'IFRAME_TYPE']);
		}

		return url;
	}
}
