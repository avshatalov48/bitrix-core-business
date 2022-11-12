import {Tag, Dom, Loc, Type, Cache} from 'main.core';
import {EventEmitter} from 'main.core.events';

export default class DefaultTab extends EventEmitter
{
	cache = new Cache.MemoryCache();
	static priority = 1;
	#parent: ?DefaultTab;

	constructor()
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');
	}

	getPriority(): Number
	{
		return this.constructor.priority;
	}

	setParentTab(tab: DefaultTab)
	{
		this.#parent = tab;
	}

	getHeaderContainer(): Element
	{
		return this.cache.remember('headerContainer', () => {
			const id = this.constructor.code;
			const title = this.getHeader();
			if (title === null)
			{
				return Tag.render`<span style="display: none;" data-bx-role="tab-header"  data-bx-state="hidden" data-bx-name="${id}"></span>`;
			}
			return Tag.render`<span class="ui-avatar-editor__tab-button-item" data-bx-role="tab-header" data-bx-state="visible" data-bx-name="${id}">${title}</span>`;
		})
	}

	getHeader(): ?String
	{
		if (this.#parent !== null)
			return this.constructor.code.toUpperCase();

		return null;
	}

	getBodyContainer(): Element
	{
		return this.cache.remember('bodyContainer', () => {
			const id = this.constructor.code;
			return Tag.render`
				<div class="ui-avatar-editor__content-block ui-avatar-editor__${id}-block" data-bx-role="tab-body" data-bx-name="${id}">${this.getBody()}</div>`;
		})
	}

	getBody(): String|Element
	{
		const id = this.constructor.code;

		return this.cache.remember('body', () => {
			return Tag.render`
			<div>
				${id.toUpperCase()}
			</div>`;
		})
	}

	inactivate(): DefaultTab
	{
		if (this.#parent)
		{
			this.#parent.getHeaderContainer().removeAttribute('data-bx-active');
			Dom.removeClass(this.#parent.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
		}
		this.getHeaderContainer().removeAttribute('data-bx-active');
		Dom.removeClass(this.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
		this.getBodyContainer().removeAttribute('data-bx-active');
		this.emit('onInactive');
		return this;
	}

	activate(): DefaultTab
	{
		if (this.#parent)
		{
			this.#parent.getHeaderContainer().setAttribute('data-bx-active', 'Y');
			Dom.addClass(this.#parent.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
		}
		this.getHeaderContainer().setAttribute('data-bx-active', 'Y');
		Dom.addClass(this.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
		this.getBodyContainer().setAttribute('data-bx-active', 'Y');
		this.emit('onActive');
		return this;
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

	static isAvailable()
	{
		return true;
	}

	static get code()
	{
		return 'default';
	}
}
