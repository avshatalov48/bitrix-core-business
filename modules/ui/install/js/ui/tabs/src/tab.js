import {Tag, Dom, Loc, Type, Cache, Text, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';
import { Loader } from 'main.loader';
import { Tabs } from './tabs';
import type { TabOptionsType, TabHeadOptionsType } from './types';

const justCounter = {
	localId: 0,
	localSorting: 0
};

export class Tab extends EventEmitter
{
	#parentElement: ?Tabs;

	#id: string;
	#sort: number = 0;
	#head: HTMLElement;
	#body: HTMLElement;
	#dataContainer: HTMLElement;
	#active: boolean = false;
	#restricted: boolean = true;
	#bannerCode: ?string = null;
	#helpDeskCode: ?string = null;

	#loader: ?Loader = null;

	constructor(options: TabOptionsType, parentElement: ?Tabs = null)
	{
		super({});

		this.setEventNamespace('UI:Tabs:');
		this.setParent(parentElement);

		this.#id = Type.isStringFilled(options.id) ? options.id : ('TabId' + (++justCounter.localId));
		this.#sort = Type.isInteger(options.sort) ? options.sort : (++justCounter.localSorting);
		this.#active = Type.isBoolean(options.active) ? options.active : false;

		this.#restricted = options.restricted === true;
		this.#bannerCode = Type.isStringFilled(options.bannerCode) ? options.bannerCode : null;
		this.#helpDeskCode = Type.isStringFilled(options.helpDeskCode) ? options.helpDeskCode : null;

		this.#initHead(options.head);
		this.#initBody(options.body);
	}

	getId(): string
	{
		return this.#id;
	}

	getSort()
	{
		return this.#sort;
	}

	setParent(parentElement: ?Tabs): this
	{
		if (parentElement instanceof Tabs)
		{
			this.#parentElement = parentElement;
		}
	}

	#initHead(headOptions: TabHeadOptionsType | HTMLElement | string): HTMLElement
	{
		const options = Type.isPlainObject(headOptions) ? headOptions : (Type.isStringFilled(headOptions) ?
			{title: headOptions} : {})
		;
		let innerHeader;
		if (Type.isDomNode(headOptions))
		{
			innerHeader = headOptions;
		}
		else if (this.#restricted !== true)
		{
			innerHeader = Tag.render`<div title="${Text.encode(options.description ?? '')}">${Text.encode(options.title) ?? '&nbsp;'}</div>`;
		}
		else
		{
			innerHeader = Tag.render`<div class="ui-tabs__tab-header-container-inner" title="${Text.encode(options.description ?? '')}">
				<div class="ui-tabs__tab-header-container-inner-title">${Text.encode(options.title)}</div>
				<div class="ui-tabs__tab-header-container-inner-lockbox"><span class="ui-icon-set --lock field-has-lock"></span></div>
			</div>`;
			Event.bind(innerHeader, 'click', this.showBanner.bind(this));
		}

		this.#head = Tag.render`<span class="ui-tabs__tab-header-container ${Text.encode(options.className ?? '')}" data-bx-role="tab-header" data-bx-name="${Text.encode(this.#id)}">${innerHeader}</span>`;

		Event.bind(
			this.#head,
			'click',
			() => {
				this.emit('changeTab');
			},
		);
	}

	#initBody(body: Function | Promise | string | HTMLElement)
	{
		this.#dataContainer = Tag.render`<div class="ui-tabs__tab-body_data"></div>`;
		this.#body = Tag.render`<div class="ui-tabs__tab-body_inner"></div>`;
		this.#body.dataset.id = this.#id;
		this.#body.dataset.role = 'body';
		this.#body.appendChild(this.#dataContainer);

		if (body)
		{
			this.subscribe('onActive', () => {
				this.#loadBody(body);
			});
		}
	}

	#loadBody(body)
	{
		let resultBody = body;
		if (Type.isFunction(body))
		{
			resultBody = body(this);
		}

		let promiseBody;
		if (!resultBody || (
			Object.prototype.toString.call(resultBody) === "[object Promise]" ||
			resultBody.toString() === "[object BX.Promise]"
		))
		{
			promiseBody = resultBody;
			this.#showLoader();
		}
		else
		{
			promiseBody = Promise.resolve(resultBody);
		}

		promiseBody.then(
			(result) =>
			{
				this.#removeLoader();
				if (Type.isDomNode(result))
				{
					this.#dataContainer.appendChild(result);
				}
				else if (Type.isString(result))
				{
					this.#dataContainer.innerHTML = result; //HTML! Not Text.encoded
				}
				else
				{
					throw new Error('Tab body has to be a text or a dom-element.');
				}
				this.emit('onLoad');
			},
			(reason) =>
			{
				console.log('reason: ', reason);
				this.#removeLoader();
				this.#dataContainer.innerHTML = reason;
				this.emit('onLoadErrored');
			}
		);
	}

	#showLoader()
	{
		this.#loader = new Loader({
			target: this.#dataContainer,
			color: 'rgba(82, 92, 105, 0.9)',
			mode: 'inline'
		});
		this
			.#loader
			.show()
			.then(() => {
				console.log('The loader is shown')
			})
		;
	}

	#removeLoader()
	{
		if (this.#loader)
		{
			this.#loader.destroy();
			this.#loader = null;
		}
	}

	isRestricted(): boolean
	{
		return this.#restricted;
	}

	getBannerCode(): ?string
	{
		return this.#bannerCode;
	}

	showBanner(event): void
	{
		if (this.getBannerCode())
		{
			BX.UI.InfoHelper.show(this.getBannerCode());
		}
		if (event)
		{
			event.stopPropagation();
			event.preventDefault();
		}
	}

	getHeader(): HTMLElement
	{
		return this.#head;
	}

	getBody(): HTMLElement
	{
		return this.#body;
	}

	// Here just in case
	getBodyDataContainer(): HTMLElement
	{
		return this.#dataContainer;
	}

	inactivate(withAnimation: boolean = true): Tab
	{
		Dom.removeClass(this.#body, 'ui-tabs__tab-active-animation');
		if (withAnimation !== false)
		{
			Dom.addClass(this.#body, 'ui-tabs__tab-active-animation');
		}

		if (this.#active === true)
		{
			Dom.removeClass(this.#head, '--header-active');
			Dom.removeClass(this.#body, '--body-active');

			this.#active = false;
			this.emit('onInactive');
		}
		return this;
	}

	activate(withAnimation: boolean = true): Tab
	{
		Dom.removeClass(this.#body, 'ui-tabs__tab-active-animation');
		if (withAnimation !== false)
		{
			Dom.addClass(this.#body, 'ui-tabs__tab-active-animation');
		}

		if (this.#active !== true)
		{
			Dom.addClass(this.#head, '--header-active');
			Dom.addClass(this.#body, '--body-active');

			this.#active = true;
			this.emit('onActive');
		}

		return this;
	}

	isActive(): boolean
	{
		return this.#active;
	}

	showError({message, code})
	{
		const errorContainer = this.getBody().querySelector('[data-bx-role="error-container"]');
		if (errorContainer)
		{
			errorContainer.innerText = message || code;
		}
		Dom.addClass(this.getBodyContainer(), 'ui-avatar-editor--error');
	}
}
