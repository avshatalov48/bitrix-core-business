import 'ui.fonts.opensans';
import './css/style.css';
import 'sidepanel';
import {Dom, Tag, Type, BaseError, Event, Runtime} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {CloseButton, CancelButton, BaseButton} from 'ui.buttons';
import {Menu, type MenuOptions, Item as MenuItem} from 'ui.sidepanel.menu';

const UI = BX.UI;
const SidePanel = BX.SidePanel;

type DesignOptions = {
	margin: ?boolean;
	section: ?boolean;
	alignButtonsLeft: ?boolean;
};

type SidePanelMenuOptions = {
	...MenuOptions;
	contentAttribute: ?string;
};

type Options = {
	extensions: ?Array<string>;
	title: ?string;
	toolbar: ?Function;
	content: string|Element|Promise|BX.Promise;
	buttons: ?Function;
	design: ?DesignOptions;
	menu: ?SidePanelMenuOptions;
};

function prepareOptions(options: Options = {})
{
	options = Object.assign({}, options);
	options.design = Object.assign({}, options.design || {});
	options.design = {
		margin: true,
		section: true,
		...options.design,
	};

	options.extensions = (options.extensions || []).concat([
		'ui.sidepanel.layout',
		'ui.buttons',
	]);
	if (options.toolbar)
	{
		options.extensions.push('ui.buttons.icons');
	}
	if (options.design.section)
	{
		options.extensions.push('ui.sidepanel-content');
	}
	if (options.menu)
	{
		options.extensions.push('ui.sidepanel.menu');
	}

	return options;
}

export class Layout
{
	static createContent(options: Options = {})
	{
		options = prepareOptions(options);
		return top.BX.Runtime
			.loadExtension(options.extensions)
			.then(() => (new Layout(options)).render())
		;
	}

	static createLayout(options: Options = {}): Promise
	{
		options = prepareOptions(options);

		return top.BX.Runtime
			.loadExtension(options.extensions)
			.then(() => new Layout(options))
		;
	}

	#container;
	#containerFooter;
	#options;
	#menu: Menu;

	constructor(options: Options = {})
	{
		this.#options = prepareOptions(options);
		const menuOptions = this.#options.menu;
		if (menuOptions)
		{
			this.#menu = new Menu(Object.assign(menuOptions));
			if (Type.isUndefined(menuOptions.contentAttribute))
			{
				menuOptions.contentAttribute = 'data-menu-item-id';
			}
			if (menuOptions.contentAttribute)
			{
				this.#menu.subscribe('click', (event: BaseEvent) => {
					this.#onMenuItemClick((event.getData() || {}).item);
				});
			}
		}
	}

	getContainer()
	{
		if (!this.#container)
		{
			this.#container = Tag.render`<div class="ui-sidepanel-layout"></div>`;
		}
		return this.#container;
	}

	getMenu(): Menu
	{
		return this.#menu;
	}

	getFooterContainer()
	{
		if (!this.#containerFooter)
		{
			this.#containerFooter = Tag.render`<div class="ui-sidepanel-layout-footer"></div>`;
		}

		return this.#containerFooter;
	}

	render(content: string = '', promised: boolean = false)
	{
		if (this.#options.content && !promised)
		{
			content = this.#options.content();
			if (
				Object.prototype.toString.call(content) === "[object Promise]"
				|| (content.toString && content.toString() === "[object BX.Promise]")
			)
			{
				return content.then(content => this.render(content, true));
			}
		}

		const container = this.getContainer();
		container.innerHTML = '';

		// HEADER
		if (this.#options.title)
		{
			const title = Tag.safe`${this.#options.title}`;
			const header = Tag.render`
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">${title}</div>
				</div>
			`;

			if (Type.isFunction(this.#options.toolbar))
			{
				const toolbar = Tag.render`<div class="ui-sidepanel-layout-toolbar"></div>`;
				this.#options.toolbar({...UI}).forEach(button => {
					if (button instanceof BaseButton)
					{
						button.renderTo(toolbar)
					}
					else if (Type.isDomNode(button))
					{
						toolbar.appendChild(button);
					}
					else
					{
						throw BaseError('Wrong button type ' + button);
					}
				});
				header.appendChild(toolbar);
			}

			container.appendChild(header);
		}

		// CONTENT
		{
			const design = this.#options.design;
			const classes = ['ui-sidepanel-layout-content'];
			const styles = [];
			if (design.margin)
			{
				if (design.margin === true)
				{
					classes.push('ui-sidepanel-layout-content-margin');
				}
				else
				{
					styles.push('margin: ' + design.margin);
				}
			}
			let contentElement = Tag.render`<div class="${classes.join(' ')}" style="${styles.join('; ')}"></div>`;
			container.appendChild(contentElement);

			if (this.#menu)
			{
				this.#menu.renderTo(contentElement);
			}
			contentElement.appendChild(Tag.render`<div class="ui-sidepanel-layout-content-inner"></div>`);
			contentElement = contentElement.lastElementChild;

			if (design.section)
			{
				contentElement.appendChild(Tag.render`<div class="ui-slider-section ui-sidepanel-layout-content-fill-height"></div>`);
				contentElement = contentElement.firstElementChild;
			}
			if (typeof content === 'string')
			{
				contentElement.innerHTML = content;
			}
			else if (content instanceof Element)
			{
				contentElement.appendChild(content);
			}

			if (this.#menu)
			{
				this.#onMenuItemClick(this.#menu.getActiveItem(), contentElement);
			}
		}

		// FOOTER
		const isButtonsUndefined = typeof this.#options.buttons === 'undefined';
		if (typeof this.#options.buttons === 'function' || isButtonsUndefined)
		{
			const cancelButton = new CancelButton({onclick: () => SidePanel.Instance.close()});
			const closeButton = new CloseButton({onclick: () => SidePanel.Instance.close()});
			const defaults = {
				...UI,
				cancelButton,
				closeButton,
			};
			if (isButtonsUndefined)
			{
				this.#options.buttons = () => [closeButton];
			}

			const buttonList = this.#options.buttons(defaults);
			if (buttonList && buttonList.length > 0)
			{
				container.appendChild(Tag.render`<div class="ui-sidepanel-layout-footer-anchor"></div>`);

				const classes = ['ui-sidepanel-layout-buttons'];
				if (this.#options.design.alignButtonsLeft)
				{
					classes.push('ui-sidepanel-layout-buttons-align-left');
				}
				const buttons = Tag.render`<div class="${classes.join(' ')}"></div>`;
				this.getFooterContainer().appendChild(buttons);
				buttonList.forEach(button => {
					if (button instanceof BaseButton)
					{
						button.renderTo(buttons)
					}
					else if (Type.isDomNode(button))
					{
						buttons.appendChild(button);
					}
					else
					{
						throw BaseError('Wrong button type ' + button);
					}
				});
				container.appendChild(this.getFooterContainer());
			}
		}

		setTimeout(()=> {
			this.afterRender();
		});
		return container;
	}

	afterRender()
	{
		this.#adjustFooter();

		const resizeHandler = Runtime.throttle(this.#adjustFooter, 300, this);
		Event.bind(window, "resize", resizeHandler);

		const topSlider = SidePanel.Instance.getTopSlider();
		if (topSlider)
		{
			EventEmitter.subscribeOnce(topSlider, 'SidePanel.Slider:onDestroy', () => {
				Event.unbind(window, "resize", resizeHandler);
			});
		}
	}

	#getScrollWidth()
	{
		const div = Tag.render`<div style="overflow-y: scroll; width: 50px; height: 50px; opacity: 0; pointer-events: none; position: absolute;"></div>`;
		document.body.appendChild(div);
		const scrollWidth = div.offsetWidth - div.clientWidth;
		Dom.remove(div);

		return scrollWidth;
	}

	#adjustFooter()
	{
		const parentSet = this.getContainer().parentNode;

		if (
			parentSet !== null
			&& parentSet.scrollWidth > parentSet.offsetWidth
		)
		{
			Dom.style(this.getFooterContainer(), 'bottom', `${this.#getScrollWidth()}px`);
		}
		else
		{
			Dom.style(this.getFooterContainer(), 'bottom', 0);
		}
	}

	#onMenuItemClick(item: MenuItem, container: HTMLElement = null)
	{
		if (!item)
		{
			return;
		}

		const id = item.getId();
		let attr = this.#options.menu.contentAttribute;
		if (!attr)
		{
			return;
		}

		container = container || this.#container;
		let nodes = container.querySelectorAll(`[${attr}]`);
		nodes = Array.prototype.slice.call(nodes);
		nodes.forEach(node => {
			node.hidden = node.getAttribute(attr) !== id;
		});
	}
}
