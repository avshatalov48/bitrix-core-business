import {Type, Dom, Tag, Event} from 'main.core';
import {BasePanel} from 'landing.ui.panel.base';
import getDeltaFromEvent from './internal/get-delta-from-event';
import calculateDurationTransition from './internal/calculate-duration-transition';
import scrollTo from './internal/scroll-to';
import './css/style.css';
import 'landing.utils';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class Content extends BasePanel
{
	static createOverlay(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-overlay landing-ui-hide" data-is-shown="false" hidden></div>
		`;
	}

	static createHeader(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-element landing-ui-panel-content-header"></div>
		`;
	}

	static createTitle(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-title"></div>
		`;
	}

	static createBody(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-element landing-ui-panel-content-body"></div>
		`;
	}

	static createSidebar(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-body-sidebar"></div>
		`;
	}

	static createContent(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-body-content"></div>
		`;
	}

	static createFooter(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-panel-content-element landing-ui-panel-content-footer"></div>
		`;
	}

	static calculateTransitionDuration(diff: number = 0): number
	{
		return calculateDurationTransition(diff);
	}

	static scrollTo(container, element: HTMLElement): Promise
	{
		return scrollTo(container, element);
	}

	static getDeltaFromEvent(event)
	{
		return getDeltaFromEvent(event);
	}

	adjustActionsPanels: boolean = true;

	constructor(id: string, data = {})
	{
		super(id, data);

		Dom.addClass(this.layout, 'landing-ui-panel-content');

		this.data = Object.freeze(data);

		this.overlay = Content.createOverlay();
		this.header = Content.createHeader();
		this.title = Content.createTitle();
		this.body = Content.createBody();
		this.footer = Content.createFooter();
		this.sidebar = Content.createSidebar();
		this.content = Content.createContent();
		this.closeButton = new BX.Landing.UI.Button.BaseButton('close', {
			className: 'landing-ui-panel-content-close',
			onClick: () => {
				void this.hide();
				this.emit('onCancel');
			},
			attrs: {
				title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_SLIDER_CLOSE'),
			},
		});

		this.forms = new BX.Landing.UI.Collection.FormCollection();
		this.buttons = new BX.Landing.UI.Collection.ButtonCollection();
		this.sidebarButtons = new BX.Landing.UI.Collection.ButtonCollection();
		this.wheelEventName = Type.isNil(window.onwheel) ? window.onwheel : window.onmousewheel;
		this.onMouseWheel = this.onMouseWheel.bind(this);
		this.onMouseEnter = this.onMouseEnter.bind(this);
		this.onMouseLeave = this.onMouseLeave.bind(this);

		Dom.removeClass(this.layout, 'landing-ui-hide');
		Dom.addClass(this.overlay, 'landing-ui-hide');

		Dom.append(this.sidebar, this.body);
		Dom.append(this.content, this.body);
		Dom.append(this.header, this.layout);
		Dom.append(this.title, this.header);
		Dom.append(this.body, this.layout);
		Dom.append(this.footer, this.layout);
		Dom.append(this.closeButton.layout, this.layout);

		if (Type.isString(data.className))
		{
			Dom.addClass(this.layout, [data.className, `${data.className}-overlay`]);
		}

		if (Type.isString(data.subTitle) && data.subTitle !== '')
		{
			this.subTitle = Tag.render`
				<div class="landing-ui-panel-content-subtitle">${data.subTitle}</div>
			`;

			Dom.append(this.subTitle, this.header);
			Dom.addClass(this.layout, 'landing-ui-panel-content-with-subtitle');
		}

		if (this.data.showFromRight === true)
		{
			this.setLayoutClass('landing-ui-panel-show-from-right');
		}

		this.init();

		Event.bind(window.top, 'keydown', this.onKeyDown.bind(this));
		BX.Landing.PageObject.getEditorWindow();

		if (this.data.scrollAnimation)
		{
			this.scrollObserver = new IntersectionObserver(this.onIntersecting.bind(this));
		}

		this.checkReadyToSave = this.checkReadyToSave.bind(this);
	}

	init()
	{
		Dom.append(this.overlay, document.body);

		Event.bind(this.overlay, 'click', () => {
			this.emit('onCancel');
			void this.hide();
		});
		Event.bind(this.layout, 'mouseenter', this.onMouseEnter);
		Event.bind(this.layout, 'mouseleave', this.onMouseLeave);
		Event.bind(this.content, 'mouseenter', this.onMouseEnter);
		Event.bind(this.content, 'mouseleave', this.onMouseLeave);
		Event.bind(this.sidebar, 'mouseenter', this.onMouseEnter);
		Event.bind(this.sidebar, 'mouseleave', this.onMouseLeave);
		Event.bind(this.header, 'mouseenter', this.onMouseEnter);
		Event.bind(this.header, 'mouseleave', this.onMouseLeave);
		Event.bind(this.footer, 'mouseenter', this.onMouseEnter);
		Event.bind(this.footer, 'mouseleave', this.onMouseLeave);

		if ('title' in this.data)
		{
			this.setTitle(this.data.title);
		}

		if ('footer' in this.data)
		{
			if (Type.isArray(this.data.footer))
			{
				this.data.footer.forEach((item) => {
					if (item instanceof BX.Landing.UI.Button.BaseButton)
					{
						this.appendFooterButton(item);
					}

					if (Type.isDomNode(item))
					{
						Dom.append(item, this.footer);
					}
				});
			}
		}
	}

	// eslint-disable-next-line class-methods-use-this
	onIntersecting(items)
	{
		items.forEach((item) => {
			if (item.isIntersecting)
			{
				Dom.removeClass(item.target, 'landing-ui-is-not-visible');
				Dom.addClass(item.target, 'landing-ui-is-visible');
			}
			else
			{
				Dom.addClass(item.target, 'landing-ui-is-not-visible');
				Dom.removeClass(item.target, 'landing-ui-is-visible');
			}
		});
	}

	onKeyDown(event)
	{
		if (event.keyCode === 27)
		{
			this.emit('onCancel');
			void this.hide();
		}
	}

	onMouseEnter(event)
	{
		event.stopPropagation();

		Event.bind(this.layout, this.wheelEventName, this.onMouseWheel);
		Event.bind(this.layout, 'touchmove', this.onMouseWheel);

		if (
			this.sidebar.contains(event.target)
			|| this.content.contains(event.target)
			|| this.header.contains(event.target)
			|| this.footer.contains(event.target)
			|| (this.right && this.right.contains(event.target))
		)
		{
			this.scrollTarget = event.currentTarget;
		}
	}

	onMouseLeave(event)
	{
		event.stopPropagation();

		BX.unbind(this.layout, this.wheelEventName, this.onMouseWheel);
		BX.unbind(this.layout, 'touchmove', this.onMouseWheel);
	}

	onMouseWheel(event)
	{
		event.preventDefault();
		event.stopPropagation();

		const delta = Content.getDeltaFromEvent(event);
		const {scrollTop} = this.scrollTarget;

		requestAnimationFrame(() => {
			this.scrollTarget.scrollTop = scrollTop - delta.y;
		});
	}

	scrollTo(element)
	{
		void Content.scrollTo(this.content, element);
	}

	isShown(): boolean
	{
		return this.state === 'shown';
	}

	shouldAdjustActionsPanels(): boolean
	{
		return this.adjustActionsPanels;
	}

	// eslint-disable-next-line no-unused-vars
	show(options?: any): Promise<any>
	{
		if (!this.isShown())
		{
			if (this.shouldAdjustActionsPanels())
			{
				Dom.addClass(document.body, 'landing-ui-hide-action-panels');
			}

			void BX.Landing.Utils.Show(this.overlay);
			return BX.Landing.Utils.Show(this.layout).then(() => {
				this.state = 'shown';
			});
		}

		return Promise.resolve(true);
	}

	hide(): Promise<any>
	{
		if (this.isShown())
		{
			if (this.shouldAdjustActionsPanels())
			{
				Dom.removeClass(document.body, 'landing-ui-hide-action-panels');
			}

			void BX.Landing.Utils.Hide(this.overlay);
			return BX.Landing.Utils.Hide(this.layout).then(() => {
				this.state = 'hidden';
			});
		}

		return Promise.resolve(true);
	}

	appendForm(form)
	{
		this.forms.add(form);
		Dom.append(form.getNode(), this.content);
	}

	appendCard(card)
	{
		if (this.data.scrollAnimation)
		{
			Dom.addClass(card.layout, 'landing-ui-is-not-visible');
			this.scrollObserver.observe(card.layout);
		}

		Dom.append(card.layout, this.content);
	}

	clear()
	{
		this.clearContent();
		this.clearSidebar();
		this.forms.clear();
	}

	clearContent()
	{
		Dom.clean(this.content);
	}

	clearSidebar()
	{
		Dom.clean(this.sidebar);
	}

	setTitle(title)
	{
		this.title.innerHTML = title;
	}

	appendFooterButton(button)
	{
		this.buttons.add(button);
		Dom.append(button.layout, this.footer);
	}

	appendSidebarButton(button)
	{
		this.sidebarButtons.add(button);
		Dom.append(button.layout, this.sidebar);
	}

	setOverlayClass(className: string)
	{
		Dom.addClass(this.overlay, className);
	}

	renderTo(target: HTMLElement)
	{
		super.renderTo(target);
		Dom.append(this.overlay, target);
	}

	checkReadyToSave()
	{
		let canSave = true;
		this.forms.forEach(form => {
			form.fields.forEach(field => {
				if (field.readyToSave === false)
				{
					canSave = false
				}
				if (!field.getListeners('onChangeReadyToSave').has(this.checkReadyToSave))
				{
					field.subscribe('onChangeReadyToSave', this.checkReadyToSave);
				}
			})
		});

		canSave ? this.enableSave() : this.disableSave()
	}

	disableSave()
	{
		const saveButton = this.buttons.get('save_block_content');
		if (saveButton)
		{
			saveButton.disable();
		}
	}

	enableSave()
	{
		const saveButton = this.buttons.get('save_block_content');
		if (saveButton)
		{
			saveButton.enable();
		}
	}
}
