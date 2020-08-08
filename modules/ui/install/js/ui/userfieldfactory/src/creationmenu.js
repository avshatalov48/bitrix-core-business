import {Dom, Event, Type, Tag} from 'main.core';
import {Popup} from 'main.popup';

const SCROLL_OFFSET = 3;

/**
 * @memberof BX.UI.UserFieldFactory
 */
export class CreationMenu
{
	#enableScrollToBottom: boolean;
	#enableScrollToTop: boolean;

	constructor(id: string, types: Array, params: Object)
	{
		this.id = id;
		this.items = types;
		this.params = {};
		if(Type.isPlainObject(params))
		{
			this.params = params;
		}
	}

	getId(): string
	{
		if(!this.id)
		{
			return 'ui-user-field-factory-menu';
		}

		return this.id;
	}

	getPopup(onItemClick = null): Popup
	{
		if(!this.popup)
		{
			let options = {...CreationMenu.getDefaultPopupOptions(), ...this.params};

			options.events = {
				onPopupShow: this.onPopupShow.bind(this),
				onPopupDestroy: this.onPopupDestroy.bind(this),
			};
			options.id = this.getId();

			this.popup = new Popup(options);
		}

		this.popup.setContent(this.render(onItemClick));

		return this.popup;
	}

	static getDefaultPopupOptions(): Object
	{
		return {
			autoHide: true,
			draggable: false,
			offsetLeft: 0,
			offsetTop: 0,
			noAllPaddings: true,
			bindOptions: { forceBindPosition: true },
			closeByEsc: true,
			cacheable: false,
		};
	}

	open(callback: function): void
	{
		const popup = this.getPopup(callback);
		if(!popup.isShown())
		{
			popup.show();
		}
	}

	render(onItemClick): Element
	{
		if(!this.container)
		{
			this.container = Tag.render`<div class="ui-userfieldfactory-creation-menu-container"></div>`;

			const scrollIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"42\" height=\"13\" viewBox=\"0 0 42 13\">\n" +
				"  <polyline fill=\"none\" stroke=\"#CACDD1\" stroke-width=\"2\" points=\"274 98 284 78.614 274 59\" transform=\"rotate(90 186 -86.5)\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
				"</svg>\n";

			this.topScrollButton = Tag.render`<div class="ui-userfieldfactory-creation-menu-scroll-top">${scrollIcon}</div>`;
			this.bottomScrollButton = Tag.render`<div class="ui-userfieldfactory-creation-menu-scroll-bottom">${scrollIcon}</div>`;

			this.container.appendChild(this.topScrollButton);
			this.container.appendChild(this.bottomScrollButton);

			this.container.appendChild(this.renderList(onItemClick));
		}

		return this.container;
	}

	renderList(onItemClick): Element
	{
		if(!this.containerList)
		{
			this.containerList = Tag.render`<div class="ui-userfieldfactory-creation-menu-list"></div>`;

			this.items.forEach((item) =>
			{
				this.containerList.appendChild(this.renderItem(item, onItemClick));
			});
		}

		return this.containerList;
	}

	renderItem(item, onClick): Element
	{
		return Tag.render`<div class="ui-userfieldfactory-creation-menu-item" onclick="${(()=>{this.handleItemClick(item, onClick);})}">
			<div class="ui-userfieldfactory-creation-menu-item-title">${item.title}</div>
			<div class="ui-userfieldfactory-creation-menu-item-desc">${item.description}</div>
		</div>`;
	}

	handleItemClick(item, onClick)
	{
		if(Type.isFunction(item.onClick))
		{
			item.onClick(item.name);
		}
		else if(Type.isFunction(onClick))
		{
			onClick(item.name);
		}
		this.getPopup().close();
	}

	onPopupShow()
	{
		Event.bind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
		Event.bind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
		Event.bind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
		Event.bind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
		Event.bind(this.containerList, "scroll", this.onScroll.bind(this));

		window.setTimeout(this.adjust.bind(this), 100);
	}

	onPopupDestroy()
	{
		Event.unbind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
		Event.unbind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
		Event.unbind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
		Event.unbind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
		Event.unbind(this.containerList, "scroll", this.onScroll.bind(this));

		this.container = null;
		this.containerList = null;
		this.topScrollButton = null;
		this.bottomScrollButton = null;

		this.popup = null;
	}

	onBottomButtonMouseOver()
	{
		if(this.#enableScrollToBottom)
		{
			return;
		}

		this.#enableScrollToBottom = true;
		this.#enableScrollToTop = false;

		(function scroll()
		{
			if(!this.#enableScrollToBottom)
			{
				return;
			}

			if((this.containerList.scrollTop + this.containerList.offsetHeight) !== this.containerList.scrollHeight)
			{
				this.containerList.scrollTop += SCROLL_OFFSET;
			}

			if((this.containerList.scrollTop + this.containerList.offsetHeight) === this.containerList.scrollHeight)
			{
				this.#enableScrollToBottom = false;
			}
			else
			{
				window.setTimeout(scroll.bind(this), 20);
			}
		}).bind(this)();
	}

	onBottomButtonMouseOut()
	{
		this.#enableScrollToBottom = false;
	}

	onTopButtonMouseOver()
	{
		if(this.#enableScrollToTop)
		{
			return;
		}

		this.#enableScrollToBottom = false;
		this.#enableScrollToTop = true;

		(function scroll()
		{
			if(!this.#enableScrollToTop)
			{
				return;
			}

			if(this.containerList.scrollTop > 0)
			{
				this.containerList.scrollTop -= SCROLL_OFFSET;
			}

			if(this.containerList.scrollTop === 0)
			{
				this.#enableScrollToTop = false;
			}
			else
			{
				window.setTimeout(scroll.bind(this), 20);
			}
		}).bind(this)();
	}

	onTopButtonMouseOut()
	{
		this.#enableScrollToTop = false;
	}

	onScroll()
	{
		this.adjust();
	}

	adjust()
	{
		const height = this.containerList.offsetHeight;
		const scrollTop = this.containerList.scrollTop;
		const scrollHeight = this.containerList.scrollHeight;

		if(scrollTop === 0)
		{
			Dom.hide(this.topScrollButton);
		}
		else
		{
			Dom.show(this.topScrollButton);
		}

		if((scrollTop + height) === scrollHeight)
		{
			Dom.hide(this.bottomScrollButton);
		}
		else
		{
			Dom.show(this.bottomScrollButton);
		}
	}
}