import type { JsonObject } from 'main.core';
import { Type, Dom, Event, Runtime, Reflection, Browser, ajax as Ajax, Uri } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { PopupManager } from 'main.popup';

import { Slider } from './slider';
import type { SliderEvent } from './slider-event';
import { Toolbar } from './toolbar';
import { MessageEvent } from './message-event';
import type { ToolbarItem } from './toolbar-item';
import type { LinkOptions } from './types/link-options';
import type { MinimizeOptions } from './types/minimize-options';
import type { RuleOptions } from './types/rule-options';

import { type SliderOptions } from './types/slider-options';

let sliderClassName = null;
let sliderDefaultOptions: SliderOptions = null;
let sliderPriorityOptions: SliderOptions = null;

/**
 * @namespace BX.SidePanel
 * @alias Manager
 */
export class SliderManager
{
	constructor()
	{
		this.anchorRules = [];
		this.anchorBinding = true;

		this.openSliders = [];
		this.lastOpenSlider = null;

		this.opened = false;
		this.hidden = false;
		this.hacksApplied = false;

		this.pageUrl = this.getCurrentUrl();
		this.pageTitle = this.getCurrentTitle();
		this.titleChanged = false;

		this.toolbar = null;

		this.fullScreenSlider = null;

		this.handleAnchorClick = this.handleAnchorClick.bind(this);
		this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
		this.handleWindowResize = Runtime.throttle(this.handleWindowResize, 300, this);
		this.handleWindowScroll = this.handleWindowScroll.bind(this);
		this.handleTouchMove = this.handleTouchMove.bind(this);

		this.handleSliderOpenStart = this.handleSliderOpenStart.bind(this);
		this.handleSliderOpenComplete = this.handleSliderOpenComplete.bind(this);
		this.handleSliderMaximizeStart = this.handleSliderMaximizeStart.bind(this);
		this.handleSliderCloseStart = this.handleSliderCloseStart.bind(this);
		this.handleSliderCloseComplete = this.handleSliderCloseComplete.bind(this);
		this.handleSliderMinimizeStart = this.handleSliderMinimizeStart.bind(this);
		this.handleSliderLoad = this.handleSliderLoad.bind(this);
		this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
		this.handleEscapePress = this.handleEscapePress.bind(this);
		this.handleFullScreenChange = this.handleFullScreenChange.bind(this);

		EventEmitter.subscribe('SidePanel:open', this.open.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:close', this.close.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:closeAll', this.closeAll.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:destroy', this.destroy.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:hide', this.hide.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:unhide', this.unhide.bind(this), { compatMode: true });

		EventEmitter.subscribe('SidePanel:postMessage', this.postMessage.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:postMessageAll', this.postMessageAll.bind(this), { compatMode: true });
		EventEmitter.subscribe('SidePanel:postMessageTop', this.postMessageTop.bind(this), { compatMode: true });

		// Compatibility
		EventEmitter.subscribe('BX.Bitrix24.PageSlider:close', this.close.bind(this), { compatMode: true });
		EventEmitter.subscribe('Bitrix24.Slider:postMessage', this.handlePostMessageCompatible.bind(this), { compatMode: true });
	}

	static registerSliderClass(
		className: string,
		defaultOptions: SliderOptions = null,
		priorityOptions: SliderOptions = null,
	): void
	{
		if (Type.isStringFilled(className))
		{
			sliderClassName = className;
		}

		if (Type.isPlainObject(defaultOptions))
		{
			sliderDefaultOptions = defaultOptions;
		}

		if (Type.isPlainObject(priorityOptions))
		{
			sliderPriorityOptions = priorityOptions;
		}
	}

	static getSliderClass(): Slider
	{
		const sliderClass = sliderClassName === null ? null : Reflection.getClass(sliderClassName);

		return sliderClass === null ? Slider : sliderClass;
	}

	static getSliderDefaultOptions(): SliderOptions
	{
		return sliderDefaultOptions === null ? {} : sliderDefaultOptions;
	}

	static getSliderPriorityOptions(): SliderOptions
	{
		return sliderPriorityOptions === null ? {} : sliderPriorityOptions;
	}

	open(url: string, options: SliderOptions): boolean
	{
		const slider = this.#createSlider(url, options);
		if (slider === null)
		{
			return false;
		}

		return this.tryApplyHacks(
			slider,
			() => slider.open(),
		);
	}

	#createSlider(sliderUrl: string, sliderOptions: SliderOptions): Slider | null
	{
		if (!Type.isStringFilled(sliderUrl))
		{
			return null;
		}

		const url = this.refineUrl(sliderUrl);

		if (this.isHidden())
		{
			this.unhide();
		}

		const topSlider = this.getTopSlider();
		if (topSlider && topSlider.isOpen() && topSlider.getUrl() === url)
		{
			return null;
		}

		if (this.getLastOpenSlider() && this.getLastOpenSlider().getUrl() === url)
		{
			return this.getLastOpenSlider();
		}

		const rule = this.getUrlRule(url);
		const ruleOptions = rule !== null && Type.isPlainObject(rule.options) ? rule.options : {};

		const useGlobalOptions = sliderOptions?.useGlobalOptions ?? ruleOptions.useGlobalOptions ?? true;
		const useRuleOptions = sliderOptions !== ruleOptions && useGlobalOptions;
		const options: SliderOptions = {
			...(useRuleOptions ? ruleOptions : {}),
			...sliderOptions,
			events: [useRuleOptions && ruleOptions.events, sliderOptions?.events],
		};

		const defaultOptions = SliderManager.getSliderDefaultOptions();
		const priorityOptions = SliderManager.getSliderPriorityOptions();

		const SliderClass = SliderManager.getSliderClass();
		const slider: Slider = new SliderClass(url, Runtime.merge(defaultOptions, options, priorityOptions));

		let offset = null;
		if (slider.shouldUseAutoOffset() && slider.getWidth() === null && slider.getCustomLeftBoundary() === null)
		{
			offset = 0;
			const lastOffset = this.#getLastOffset();
			if (topSlider && lastOffset !== null)
			{
				offset = Math.min(lastOffset + this.#getMinOffset(), this.#getMaxOffset());
			}
		}

		slider.setOffset(offset);

		if (topSlider && topSlider.getCustomRightBoundary() !== null)
		{
			const rightBoundary = slider.calculateRightBoundary();
			if (rightBoundary > topSlider.getCustomRightBoundary())
			{
				slider.setCustomRightBoundary(topSlider.getCustomRightBoundary());
			}
		}

		EventEmitter.subscribe(slider, 'SidePanel.Slider:onOpenStart', this.handleSliderOpenStart, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onBeforeOpenComplete', this.handleSliderOpenComplete, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onMaximizeStart', this.handleSliderMaximizeStart, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onCloseStart', this.handleSliderCloseStart, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onBeforeCloseComplete', this.handleSliderCloseComplete, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onMinimizeStart', this.handleSliderMinimizeStart, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onLoad', this.handleSliderLoad, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onDestroy', this.handleSliderDestroy, { compatMode: true });
		EventEmitter.subscribe(slider, 'SidePanel.Slider:onEscapePress', this.handleEscapePress, { compatMode: true });

		return slider;
	}

	getMinimizeOptions(url: string): MinimizeOptions | null
	{
		const rule = this.getUrlRule(url);
		const ruleOptions = rule !== null && Type.isPlainObject(rule.options) ? rule.options : {};

		return Type.isPlainObject(ruleOptions.minimizeOptions) ? ruleOptions.minimizeOptions : null;
	}

	maximize(url, options: SliderOptions): boolean
	{
		const slider = this.#createSlider(url, options);
		if (slider === null)
		{
			return false;
		}

		return this.tryApplyHacks(
			slider,
			() => slider.maximize(),
		);
	}

	tryApplyHacks(slider, cb: Function): boolean
	{
		if (!this.isOpen())
		{
			this.applyHacks(slider);
		}

		const success = cb();
		if (!success)
		{
			this.resetHacks(slider);
		}

		return success;
	}

	isOpen(): boolean
	{
		return this.opened;
	}

	close(immediately: boolean, callback: Function): void
	{
		const topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.close(immediately, callback);
		}
	}

	closeAll(immediately: boolean): void
	{
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			const success = slider.close(immediately);
			if (!success)
			{
				break;
			}
		}
	}

	minimize(immediately: boolean, callback: Function): void
	{
		const topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.minimize(immediately, callback);
		}
	}

	hide(): boolean
	{
		if (this.hidden)
		{
			return false;
		}

		const topSlider = this.getTopSlider();

		this.getOpenSliders().forEach((slider: Slider) => {
			slider.hide();
		});

		this.hidden = true;

		this.resetHacks(topSlider);

		return true;
	}

	unhide(): boolean
	{
		if (!this.hidden)
		{
			return false;
		}

		this.getOpenSliders().forEach((slider) => {
			slider.unhide();
		});

		this.hidden = false;

		setTimeout(() => {
			this.applyHacks(this.getTopSlider());
		}, 0);

		return true;
	}

	isHidden(): boolean
	{
		return this.hidden;
	}

	destroy(sliderUrl: string)
	{
		if (!Type.isStringFilled(sliderUrl))
		{
			return;
		}

		const url = this.refineUrl(sliderUrl);
		const sliderToDestroy = this.getSlider(url);

		if (this.getLastOpenSlider() && (sliderToDestroy || this.getLastOpenSlider().getUrl() === url))
		{
			this.getLastOpenSlider().destroy();
		}

		if (sliderToDestroy !== null)
		{
			const openSliders = this.getOpenSliders();
			for (let i = openSliders.length - 1; i >= 0; i--)
			{
				const slider = openSliders[i];
				slider.destroy();

				if (slider === sliderToDestroy)
				{
					break;
				}
			}
		}
	}

	reload(): void
	{
		const topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.reload();
		}
	}

	getTopSlider(): Slider | null
	{
		const count = this.openSliders.length;

		return this.openSliders[count - 1] || null;
	}

	getPreviousSlider(currentSlider?: Slider): Slider | null
	{
		let previousSlider = null;
		const openSliders = this.getOpenSliders();
		currentSlider = currentSlider || this.getTopSlider();

		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (slider === currentSlider)
			{
				previousSlider = openSliders[i - 1] || null;
				break;
			}
		}

		return previousSlider;
	}

	getSlider(sliderUrl: string): Slider | null
	{
		const url = this.refineUrl(sliderUrl);

		const openSliders = this.getOpenSliders();
		for (const slider of openSliders)
		{
			if (slider.getUrl() === url)
			{
				return slider;
			}
		}

		return null;
	}

	getSliderByWindow(window: Window): Slider | null
	{
		const openSliders = this.getOpenSliders();
		for (const slider of openSliders)
		{
			if (slider.getFrameWindow() === window)
			{
				return slider;
			}
		}

		return null;
	}

	getOpenSliders(): Slider[]
	{
		return this.openSliders;
	}

	getOpenSlidersCount(): number
	{
		return this.openSliders.length;
	}

	/**
	 * @private
	 */
	#addOpenSlider(slider: Slider): void
	{
		if (!(slider instanceof Slider))
		{
			throw new TypeError('Slider is not an instance of BX.SidePanel.Slider');
		}

		this.openSliders.push(slider);
	}

	/**
	 * @private
	 */
	#removeOpenSlider(slider): boolean
	{
		const openSliders = [...this.getOpenSliders()];
		for (const [i, openSlider] of openSliders.entries())
		{
			if (openSlider === slider)
			{
				this.openSliders.splice(i, 1);

				return true;
			}
		}

		return false;
	}

	getLastOpenSlider(): Slider | null
	{
		return this.lastOpenSlider;
	}

	#setLastOpenSlider(slider: Slider): void
	{
		if (this.lastOpenSlider !== slider)
		{
			if (this.lastOpenSlider)
			{
				this.lastOpenSlider.destroy();
			}

			this.lastOpenSlider = slider;
		}
	}

	#resetLastOpenSlider(): void
	{
		if (this.lastOpenSlider && this.getTopSlider() !== this.lastOpenSlider)
		{
			this.lastOpenSlider.destroy();
		}

		this.lastOpenSlider = null;
	}

	adjustLayout(): void
	{
		this.getOpenSliders().forEach((slider: Slider) => {
			slider.adjustLayout();
		});
	}

	createToolbar(options): Toolbar
	{
		if (this.toolbar === null)
		{
			this.toolbar = new Toolbar(options);
		}

		return this.toolbar;
	}

	getToolbar(): Toolbar
	{
		return this.toolbar;
	}

	#getLastOffset(): number | null
	{
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (slider.getOffset() !== null)
			{
				return slider.getOffset();
			}
		}

		return null;
	}

	refineUrl(url: string): string
	{
		if (Type.isStringFilled(url) && /IFRAME/.test(url))
		{
			return Uri.removeParam(url, ['IFRAME', 'IFRAME_TYPE']);
		}

		return url;
	}

	getPageUrl(): string
	{
		return this.pageUrl;
	}

	getCurrentUrl(): string
	{
		return window.location.pathname + window.location.search + window.location.hash;
	}

	getPageTitle(): string
	{
		return this.pageTitle;
	}

	getCurrentTitle(): string
	{
		let title = document.title;
		if (!Type.isUndefined(window.BXIM))
		{
			title = title.replace(/^\(\d+\) /, ''); // replace a messenger counter.
		}

		return title;
	}

	enterFullScreen()
	{
		if (!this.getTopSlider() || this.getFullScreenSlider())
		{
			return;
		}

		const container = document.body;
		if (container.requestFullscreen)
		{
			Event.bind(document, 'fullscreenchange', this.handleFullScreenChange);
			container.requestFullscreen();
		}
		else if (container.webkitRequestFullScreen)
		{
			Event.bind(document, 'webkitfullscreenchange', this.handleFullScreenChange);
			container.webkitRequestFullScreen();
		}
		else if (container.msRequestFullscreen)
		{
			Event.bind(document, 'MSFullscreenChange', this.handleFullScreenChange);
			container.msRequestFullscreen();
		}
		else if (container.mozRequestFullScreen)
		{
			Event.bind(document, 'mozfullscreenchange', this.handleFullScreenChange);
			container.mozRequestFullScreen();
		}
		else
		{
			console.log('Slider: Full Screen mode is not supported.');
		}
	}

	exitFullScreen()
	{
		if (!this.getFullScreenSlider())
		{
			return;
		}

		if (document.exitFullscreen)
		{
			document.exitFullscreen();
		}
		else if (document.webkitExitFullscreen)
		{
			document.webkitExitFullscreen();
		}
		else if (document.msExitFullscreen)
		{
			document.msExitFullscreen();
		}
		else if (document.mozCancelFullScreen)
		{
			document.mozCancelFullScreen();
		}
	}

	getFullScreenElement(): HTMLElement
	{
		return (
			document.fullscreenElement
			|| document.webkitFullscreenElement
			|| document.mozFullScreenElement
			|| document.msFullscreenElement
			|| null
		);
	}

	getFullScreenSlider(): Slider | null
	{
		return this.fullScreenSlider;
	}

	handleFullScreenChange(event): void
	{
		if (this.getFullScreenElement())
		{
			this.fullScreenSlider = this.getTopSlider();
			Dom.addClass(this.fullScreenSlider.getOverlay(), 'side-panel-fullscreen');

			this.fullScreenSlider.fireEvent('onFullScreenEnter');
		}
		else
		{
			if (this.getFullScreenSlider())
			{
				Dom.removeClass(this.getFullScreenSlider().getOverlay(), 'side-panel-fullscreen');
				this.fullScreenSlider.fireEvent('onFullScreenExit');
				this.fullScreenSlider = null;
			}

			Event.unbind(document, event.type, this.handleFullScreenChange);
			window.scrollTo(0, this.pageScrollTop);

			setTimeout(() => {
				this.adjustLayout();
				const resizeEvent = document.createEvent('Event');
				resizeEvent.initEvent('resize', true, true);
				window.dispatchEvent(resizeEvent);
			}, 1000);
		}
	}

	postMessage(source: string | Window | Slider, eventId: string, data: JsonObject): void
	{
		const sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		let previousSlider: Slider = null;
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (slider === sender)
			{
				previousSlider = openSliders[i - 1] || null;
				break;
			}
		}

		const sliderWindow = previousSlider ? previousSlider.getWindow() : window;
		sliderWindow.BX.onCustomEvent('Bitrix24.Slider:onMessage', [sender, data]); // Compatibility

		const event = new MessageEvent({
			sender,
			slider: previousSlider || null,
			data,
			eventId,
		});

		if (previousSlider)
		{
			previousSlider.firePageEvent(event);
			previousSlider.fireFrameEvent(event);
		}
		else
		{
			EventEmitter.emit(event.getFullName().toLowerCase(), new BaseEvent({ data: [event], compatData: [event] }));
		}
	}

	postMessageAll(source: string | Window | Slider, eventId: string, data: JsonObject): void
	{
		const sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		let event = null;
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (slider === sender)
			{
				continue;
			}

			event = new MessageEvent({ sender, slider, data, eventId });
			slider.firePageEvent(event);
			slider.fireFrameEvent(event);
		}

		event = new MessageEvent({ sender, slider: null, data, eventId });

		EventEmitter.emit(event.getFullName().toLowerCase(), new BaseEvent({ data: [event], compatData: [event] }));
	}

	postMessageTop(source: string | Window | Slider, eventId: string, data: JsonObject): void
	{
		const sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		const event = new MessageEvent({ sender, slider: null, data, eventId });

		EventEmitter.emit(event.getFullName().toLowerCase(), new BaseEvent({ data: [event], compatData: [event] }));
	}

	#getMinOffset(): number
	{
		return 63;
	}

	#getMaxOffset(): number
	{
		return this.#getMinOffset() * 3;
	}

	bindAnchors(parameters: { rules: RuleOptions[] })
	{
		if (!Type.isPlainObject(parameters) || !Type.isArray(parameters.rules) || parameters.rules.length === 0)
		{
			return;
		}

		if (this.anchorRules.length === 0)
		{
			this.registerAnchorListener(window.document);
		}

		if (!(parameters.rules instanceof Object))
		{
			console.error(
				'BX.SitePanel: anchor rules were created in a different context. ' +
				'This might be a reason for a memory leak.',
			);

			console.trace();
		}

		parameters.rules.forEach((rule) => {
			if (Type.isArray(rule.condition))
			{
				for (let m = 0; m < rule.condition.length; m++)
				{
					if (Type.isString(rule.condition[m]))
					{
						rule.condition[m] = new RegExp(rule.condition[m], 'i');
					}
				}
			}

			rule.options ??= {};

			this.anchorRules.push(rule);
		});
	}

	isAnchorBinding(): boolean
	{
		return this.anchorBinding;
	}

	enableAnchorBinding(): void
	{
		this.anchorBinding = true;
	}

	disableAnchorBinding(): void
	{
		this.anchorBinding = false;
	}

	registerAnchorListener(targetDocument)
	{
		Event.bind(targetDocument, 'click', this.handleAnchorClick, true);
	}

	unregisterAnchorListener(targetDocument)
	{
		Event.unbind(targetDocument, 'click', this.handleAnchorClick, true);
	}

	/**
	 * @private
	 */
	handleSliderOpenStart(event: SliderEvent)
	{
		if (!event.isActionAllowed())
		{
			return;
		}

		const slider = event.getSlider();
		if (slider.isDestroyed())
		{
			return;
		}

		if (this.getTopSlider())
		{
			this.exitFullScreen();

			this.getTopSlider().hideOverlay();
			slider.setOverlayBackground();

			const sameWidth = (
				this.getTopSlider().getOffset() === slider.getOffset()
				&& this.getTopSlider().getWidth() === slider.getWidth()
				&& this.getTopSlider().getCustomLeftBoundary() === slider.getCustomLeftBoundary()
			);

			if (!sameWidth)
			{
				this.getTopSlider().showShadow();
			}

			this.getTopSlider().hideCloseBtn();
			this.getTopSlider().hideExtraLabels();
		}
		else
		{
			slider.setOverlayAnimation(true);
		}

		this.#addOpenSlider(slider);

		// this.getOpenSliders().forEach((currentSlider: Slider, index: number, openSliders: Slider[]) => {
		// 	currentSlider.getLabel().moveAt(openSliders.length - index - 1); // move down
		// });

		this.losePageFocus();

		if (!this.opened)
		{
			this.pageUrl = this.getCurrentUrl();
			this.pageTitle = this.getCurrentTitle();
		}

		this.opened = true;

		this.#resetLastOpenSlider();
	}

	/**
	 * @private
	 */
	handleSliderOpenComplete(event: SliderEvent)
	{
		this.setBrowserHistory(event.getSlider());
		this.updateBrowserTitle();
		event.getSlider().setAnimation('sliding');

		const openSliders = this.getOpenSliders();
		const topSlider = event.getSlider();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (topSlider === slider)
			{
				continue;
			}

			if (topSlider.getContainer().offsetLeft <= slider.getContainer().offsetLeft)
			{
				Dom.addClass(slider.getOverlay(), '--invisible');
			}
			else
			{
				break;
			}
		}
	}

	/**
	 * @private
	 */
	handleSliderCloseStart(event: SliderEvent)
	{
		if (!event.isActionAllowed())
		{
			return;
		}

		if (event.getSlider() && event.getSlider().isDestroyed())
		{
			return;
		}

		const previousSlider = this.getPreviousSlider();
		const topSlider = this.getTopSlider();

		this.exitFullScreen();

		// this.getOpenSliders().forEach((slider, index, openSliders) => {
		// 	slider.getLabel().moveAt(openSliders.length - index - 2); // move up
		// });

		let visibleSlider = null;
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (event.getSlider() === slider)
			{
				continue;
			}

			if (visibleSlider === null || slider.getContainer().offsetLeft < visibleSlider.getContainer().offsetLeft)
			{
				Dom.removeClass(slider.getOverlay(), '--invisible');
				visibleSlider = slider;
			}
		}

		if (previousSlider)
		{
			previousSlider.unhideOverlay();
			previousSlider.hideShadow();
			previousSlider.showCloseBtn();
			previousSlider.showExtraLabels();

			if (topSlider)
			{
				topSlider.hideOverlay();
				topSlider.hideShadow();
			}
		}
	}

	handleSliderMaximizeStart(event)
	{
		if (!event.isActionAllowed() || this.getToolbar() === null)
		{
			return;
		}

		const slider = event.getSlider();
		if (slider && slider.isDestroyed())
		{
			return;
		}

		const { entityType, entityId } = slider.getMinimizeOptions() || {};
		const item = this.getToolbar().getItem(entityType, entityId);

		this.getToolbar().request('maximize', item);

		const origin = this.#getItemOrigin(slider, item);
		slider.setAnimation('scale', { origin });
	}

	handleSliderMinimizeStart(event)
	{
		if (!event.isActionAllowed() || this.getToolbar() === null)
		{
			return;
		}

		const slider = event.getSlider();
		if (slider && slider.isDestroyed())
		{
			return;
		}

		if (!this.getToolbar().isShown())
		{
			this.getToolbar().show();
		}

		let title = slider.getTitle();
		if (!title)
		{
			title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
		}

		this.getToolbar().expand(true);

		const minimizeOptions = this.getMinimizeOptions(slider.getUrl());
		const { entityType, entityId, url } = slider.getMinimizeOptions() || minimizeOptions || {};

		const item = this.getToolbar().minimizeItem({
			title,
			url: Type.isStringFilled(url) ? url : slider.getUrl(),
			entityType,
			entityId,
		});

		const origin = this.#getItemOrigin(slider, item);
		slider.setAnimation('scale', { origin });
	}

	#getItemOrigin(slider: Slider, item: ToolbarItem): void
	{
		if (item && item.getContainer().offsetWidth > 0)
		{
			const rect = item.getContainer().getBoundingClientRect();
			const offset = slider.getContainer().getBoundingClientRect().left;
			const left = rect.left - offset + rect.width / 2;

			return `${left}px ${rect.top}px`;
		}

		return '50% 100%';
	}

	/**
	 * @private
	 */
	handleSliderCloseComplete(event: SliderEvent)
	{
		const slider = event.getSlider();
		if (slider === this.getTopSlider())
		{
			this.#setLastOpenSlider(slider);
		}

		event.getSlider().setAnimation('sliding');

		this.cleanUpClosedSlider(slider);
	}

	/**
	 * @private
	 */
	handleSliderDestroy(event: SliderEvent)
	{
		const slider = event.getSlider();

		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onOpenStart', this.handleSliderOpenStart);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onBeforeOpenComplete', this.handleSliderOpenComplete);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onMaximizeStart', this.handleSliderMaximizeStart);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onCloseStart', this.handleSliderCloseStart);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onBeforeCloseComplete', this.handleSliderCloseComplete);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onMinimizeStart', this.handleSliderMinimizeStart);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onLoad', this.handleSliderLoad);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onDestroy', this.handleSliderDestroy);
		EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onEscapePress', this.handleEscapePress);

		const frameWindow = event.getSlider().getFrameWindow();
		if (frameWindow && !event.getSlider().allowCrossOrigin)
		{
			this.unregisterAnchorListener(frameWindow.document);
		}

		if (slider === this.getLastOpenSlider())
		{
			this.lastOpenSlider = null;
		}

		this.cleanUpClosedSlider(slider);
	}

	handleEscapePress(event)
	{
		const topSlider = this.getTopSlider();
		if (topSlider?.canCloseByEsc() && this.isOnTop(topSlider))
		{
			topSlider.close();
		}
	}

	/**
	 * @private
	 */
	cleanUpClosedSlider(slider: Slider)
	{
		this.#removeOpenSlider(slider);

		slider.unhideOverlay();
		slider.hideShadow();

		// this.getOpenSliders().forEach((slider, index, openSliders) => {
		// 	slider.getLabel().moveAt(openSliders.length - index - 1); //update position
		// });

		if (this.getTopSlider())
		{
			this.getTopSlider().unhideOverlay();
			this.getTopSlider().hideShadow();
			this.getTopSlider().showCloseBtn();
			this.getTopSlider().showExtraLabels();
			this.getTopSlider().focus();
		}
		else
		{
			window.focus();
		}

		if (!this.getOpenSlidersCount())
		{
			this.resetHacks(slider);
			this.opened = false;
		}

		this.resetBrowserHistory();
		this.updateBrowserTitle();
	}

	/**
	 * @private
	 */
	handleSliderLoad(event: SliderEvent)
	{
		const frameWindow = event.getSlider().getFrameWindow();
		if (frameWindow)
		{
			this.registerAnchorListener(frameWindow.document);
		}

		this.setBrowserHistory(event.getSlider());
		this.updateBrowserTitle();
	}

	/**
	 * @private
	 */
	handlePostMessageCompatible(source: string | Window | Slider, data: JsonObject)
	{
		this.postMessage(source, '', data);
	}

	/**
	 * @private
	 */
	getSliderFromSource(source: string | Window | Slider)
	{
		if (source instanceof Slider)
		{
			return source;
		}

		if (Type.isStringFilled(source))
		{
			return this.getSlider(source);
		}

		if (source !== null && source === source.window && window !== source)
		{
			return this.getSliderByWindow(source);
		}

		return null;
	}

	/**
	 * @private
	 */
	applyHacks(slider: Slider): boolean
	{
		if (this.hacksApplied)
		{
			return false;
		}

		if (slider)
		{
			slider.applyHacks();
		}

		this.disablePageScrollbar();
		this.bindEvents();

		if (slider)
		{
			slider.applyPostHacks();
		}

		this.hacksApplied = true;

		return true;
	}

	/**
	 * @private
	 */
	resetHacks(slider: Slider): boolean
	{
		if (!this.hacksApplied)
		{
			return false;
		}

		if (slider)
		{
			slider.resetPostHacks();
		}

		this.enablePageScrollbar();
		this.unbindEvents();

		if (slider)
		{
			slider.resetHacks();
		}

		this.hacksApplied = false;

		return true;
	}

	/**
	 * @private
	 */
	bindEvents(): boolean
	{
		Event.bind(document, 'keydown', this.handleDocumentKeyDown);
		Event.bind(window, 'resize', this.handleWindowResize);
		Event.bind(window, 'scroll', this.handleWindowScroll); // Live Comments can change scrollTop

		if (Browser.isMobile())
		{
			Event.bind(document.body, 'touchmove', this.handleTouchMove);
		}
	}

	/**
	 * @private
	 */
	unbindEvents()
	{
		Event.unbind(document, 'keydown', this.handleDocumentKeyDown);
		Event.unbind(window, 'resize', this.handleWindowResize);
		Event.unbind(window, 'scroll', this.handleWindowScroll);

		if (Browser.isMobile())
		{
			Event.unbind(document.body, 'touchmove', this.handleTouchMove);
		}
	}

	/**
	 * @private
	 */
	disablePageScrollbar(): void
	{
		const scrollWidth = window.innerWidth - document.documentElement.clientWidth;
		document.body.style.paddingRight = scrollWidth + 'px';
		Dom.style(document.body, '--scroll-shift-width', `${scrollWidth}px`);
		Dom.addClass(document.body, 'side-panel-disable-scrollbar');
		this.pageScrollTop = window.pageYOffset || document.documentElement.scrollTop;
	}

	/**
	 * @private
	 */
	enablePageScrollbar()
	{
		document.body.style.removeProperty('padding-right');
		Dom.style(document.body, '--scroll-shift-width', null);
		Dom.removeClass(document.body, 'side-panel-disable-scrollbar');
	}

	/**
	 * @private
	 */
	losePageFocus()
	{
		if (Type.isDomNode(document.activeElement))
		{
			document.activeElement.blur();
		}
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleDocumentKeyDown(event: KeyboardEvent)
	{
		if (event.keyCode !== 27)
		{
			return;
		}

		event.preventDefault(); // otherwise an iframe loading can be cancelled by a browser

		this.handleEscapePress();
	}

	/**
	 * @private
	 */
	handleWindowResize()
	{
		this.adjustLayout();
	}

	/**
	 * @private
	 */
	handleWindowScroll()
	{
		window.scrollTo(0, this.pageScrollTop);
		this.adjustLayout();
	}

	/**
	 * @private
	 * @param {Event} event
	 */
	handleTouchMove(event)
	{
		event.preventDefault();
	}

	/**
	 * @private
	 */
	isOnTop(slider: Slider): boolean
	{
		if (slider)
		{
			const popups = PopupManager.getPopups();
			const isOnTopOfAllPopups = popups.every((popup) => slider.isOnTopOfPopup(popup));
			if (!isOnTopOfAllPopups)
			{
				return false;
			}
		}

		// Photo Slider or something else can cover Side Panel.
		const centerX = document.documentElement.clientWidth / 2;
		const centerY = document.documentElement.clientHeight / 2;
		const element = document.elementFromPoint(centerX, centerY);

		return Dom.hasClass(element, 'side-panel') || element.closest('.side-panel') !== null;
	}

	/**
	 * @private
	 */
	extractLinkFromEvent(event: MouseEvent): LinkOptions | null
	{
		const target: HTMLElement = event.target;

		if (event.which !== 1 || !Type.isDomNode(target) || event.ctrlKey || event.metaKey)
		{
			return null;
		}

		let a = target;
		if (target.nodeName !== 'A' && Type.isElementNode(target))
		{
			a = target.closest('a');
		}

		if (!Type.isDomNode(a))
		{
			return null;
		}

		// do not use a.href here, the code will fail on links like <a href="#SG13"></a>
		const href = a.getAttribute('href');
		if (href)
		{
			return {
				url: href,
				anchor: a,
				target: a.getAttribute('target'),
			};
		}

		return null;
	}

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleAnchorClick(event)
	{
		if (!this.isAnchorBinding())
		{
			return;
		}

		const link = this.extractLinkFromEvent(event);

		if (!link || Dom.attr(link.anchor, 'data-slider-ignore-autobinding') !== null)
		{
			return;
		}

		if (Dom.attr(event.target, 'data-slider-ignore-autobinding') !== null)
		{
			return;
		}

		const rule = this.getUrlRule(link.url, link);

		if (!this.isValidLink(rule, link))
		{
			return;
		}

		if (Type.isFunction(rule.handler))
		{
			rule.handler(event, link);
		}
		else
		{
			event.preventDefault();
			if (Dom.attr(event.target, 'data-slider-maximize') === null)
			{
				this.open(link.url, rule.options);
			}
			else
			{
				this.maximize(link.url, rule.options);
			}
		}
	}

	/**
	 * @public
	 * @param {string} url
	 */
	emulateAnchorClick(url)
	{
		const link = {
			url,
			anchor: null,
			target: null,
		};

		const rule = this.getUrlRule(url, link);

		if (!this.isValidLink(rule, link))
		{
			BX.reload(url);
		}
		else if (Type.isFunction(rule.handler))
		{
			rule.handler(
				new MouseEvent(
					'slider',
					{
						bubbles: false,
						cancelable: true,
					},
				),
				link,
			);
		}
		else
		{
			this.open(link.url, rule.options);
		}
	}

	/**
	 * @private
	 */
	getUrlRule(href: string, link: LinkOptions): RuleOptions | null
	{
		if (!Type.isStringFilled(href))
		{
			return null;
		}

		if (!Type.isPlainObject(link))
		{
			const a = document.createElement('a');
			a.href = href;

			link = { url: href, anchor: a, target: '' };
		}

		for (let k = 0; k < this.anchorRules.length; k++)
		{
			const rule = this.anchorRules[k];

			if (!Type.isArray(rule.condition))
			{
				continue;
			}

			for (let m = 0; m < rule.condition.length; m++)
			{
				const matches = href.match(rule.condition[m]);
				if (matches && !this.hasStopParams(href, rule.stopParameters))
				{
					link.matches = matches;

					let options = Type.isFunction(rule.options) ? rule.options(link) : rule.options;
					const minimizeOptions = Type.isFunction(rule.minimizeOptions) ? rule.minimizeOptions(link) : null;
					if (Type.isPlainObject(minimizeOptions))
					{
						if (Type.isPlainObject(options))
						{
							options.minimizeOptions = minimizeOptions;
						}
						else
						{
							options = { minimizeOptions };
						}
					}

					if (Type.isStringFilled(rule.loader) && !Type.isStringFilled(options.loader))
					{
						options.loader = rule.loader;
						delete rule.loader;
					}

					return { ...rule, options };
				}
			}
		}

		return null;
	}

	/**
	 * @private
	 */
	isValidLink(rule: RuleOptions, link: LinkOptions): boolean
	{
		if (!rule)
		{
			return false;
		}

		if (rule.allowCrossDomain !== true && Ajax.isCrossDomain(link.url))
		{
			return false;
		}

		if (rule.mobileFriendly !== true && Browser.isMobile())
		{
			return false;
		}

		return !Type.isFunction(rule.validate) || rule.validate(link);
	}

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	setBrowserHistory(slider)
	{
		if (!(slider instanceof Slider))
		{
			return;
		}

		if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded())
		{
			window.history.replaceState({}, '', slider.getUrl());
		}
	}

	/**
	 * @private
	 */
	resetBrowserHistory()
	{
		let topSlider = null;
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			const slider = openSliders[i];
			if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded())
			{
				topSlider = slider;
				break;
			}
		}

		const url = topSlider ? topSlider.getUrl() : this.getPageUrl();
		if (url)
		{
			window.history.replaceState({}, '', url);
		}
	}

	/**
	 * @public
	 */
	updateBrowserTitle()
	{
		let title = null;
		const openSliders = this.getOpenSliders();
		for (let i = openSliders.length - 1; i >= 0; i--)
		{
			title = this.getBrowserTitle(openSliders[i]);
			if (Type.isStringFilled(title))
			{
				break;
			}
		}

		if (Type.isStringFilled(title))
		{
			document.title = title;
			this.titleChanged = true;
		}
		else if (this.titleChanged)
		{
			document.title = this.getPageTitle();
			this.titleChanged = false;
		}
	}

	/**
	 * @private
	 */
	getBrowserTitle(slider: Slider): string | null
	{
		if (!slider || !slider.canChangeTitle() || !slider.isOpen() || !slider.isLoaded())
		{
			return null;
		}

		let title = slider.getTitle();
		if (!title && !slider.isSelfContained())
		{
			title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
		}

		return Type.isStringFilled(title) ? title : null;
	}

	/**
	 * @private
	 */
	hasStopParams(url: string, params: string[]): boolean
	{
		if (!params || !Type.isArray(params) || !Type.isStringFilled(url))
		{
			return false;
		}

		const questionPos = url.indexOf('?');
		if (questionPos === -1)
		{
			return false;
		}

		const query = url.slice(Math.max(0, questionPos));
		for (const param of params)
		{
			if (new RegExp(`[?&]${param}=`, 'i').test(query))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @deprecated use getLastOpenSlider method
	 */
	getLastOpenPage(): Slider | null
	{
		return this.getLastOpenSlider();
	}

	/**
	 * @deprecated use getTopSlider method
	 */
	getCurrentPage(): Slider | null
	{
		return this.getTopSlider();
	}
}
