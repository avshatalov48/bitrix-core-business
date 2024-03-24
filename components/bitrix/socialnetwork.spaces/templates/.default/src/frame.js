import { Dom, Event, Runtime, Tag, Type, Uri } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Loader } from './loader';

type Params = {
	pageId: string,
	pageView: string,
	id: string,
	src: string,
	className: string,
}

export type LoadInfo = {
	src: string,
	window: Window;
}

export class Frame extends EventEmitter
{
	#pageId: string;
	#pageView: string;
	#id: string;
	#src: string;
	#className: string;

	#loader: Loader;

	#sidePanelManager: BX.SidePanel.Manager;

	#container: HTMLElement;
	#node: HTMLIFrameElement;
	#window: Window;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Frame');

		this.#pageId = params.pageId;
		this.#pageView = params.pageView;
		this.#id = params.id;
		this.#className = params.className;

		this.#loader = new Loader({
			pageView: this.#pageView,
			pageUrl: params.src,
		});

		this.#sidePanelManager = BX.SidePanel.Instance;

		this.#setSrc(params.src);

		this.updateSrcDebounced = Runtime.debounce(this.updateSrc.bind(this), 1000);
	}

	renderTo(container: HTMLElement): void
	{
		this.#container = container;

		this.#loader.show(this.#container);

		Dom.append(this.#render(), container);
	}

	reload(pageView: string, src?: string)
	{
		this.#loader.setLoader(pageView);

		Dom.addClass(this.#node, '--hidden');
		this.#loader.show(this.#container);

		if (src)
		{
			this.#setSrc(src);
		}

		if (this.#loader.isShown())
		{
			this.updateSrcDebounced();
		}
		else
		{
			this.updateSrc();
		}
	}

	updateSrc()
	{
		this.#node.src = this.#src;
	}

	getFrameNode(): HTMLIFrameElement
	{
		return this.#node;
	}

	getWindow(): Window
	{
		return this.#window;
	}

	#render(): HTMLIFrameElement
	{
		this.#node = Tag.render`
			<iframe
				id="${this.#id}"
				class="${`${this.#className} --hidden`}"
				src="${this.#src}"
				onload="${this.#load.bind(this)}"
			>
			</iframe>
		`;

		return this.#node;
	}

	#setSrc(src: string)
	{
		const uri = new Uri(src);
		uri.setQueryParams({ IFRAME: 'Y' });

		this.#src = uri.toString();
	}

	#load(event)
	{
		this.#window = event.target.contentWindow;

		Event.bind(this.#window, 'unload', () => {
			this.emit('unload', {
				src: this.#src,
				window: this.#window,
			});
		});

		const url = new URL(this.#src, location);
		url.searchParams.delete('empty-state');
		this.#src = url.toString();

		this.#initHacks();

		this.#changeLinksTargets(this.#window.document.body);
		this.#initObserver();

		this.#loader.hide();
		Dom.removeClass(this.#node, '--hidden');

		this.emit('load', {
			src: this.#src,
			window: this.#window,
		});
	}

	#initHacks()
	{
		this.#sidePanelManager.registerAnchorListener(this.#window.document);
	}

	#initObserver()
	{
		if (Type.isUndefined(MutationObserver))
		{
			return;
		}

		const observer = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				for (let i = 0; i < mutation.addedNodes.length; ++i)
				{
					this.#changeLinksTargets(mutation.addedNodes.item(i));
				}
			});
		});
		observer.observe(this.#window.document.body, { childList: true, subtree: true });
	}

	#changeLinksTargets(context: ?HTMLElement)
	{
		if (!context)
		{
			return;
		}

		let list = [];
		if (context.tagName === 'A')
		{
			list = [context];
		}
		else if (Type.isElementNode(context))
		{
			list = [...context.querySelectorAll('a')];
		}

		list
			.filter((a: HTMLLinkElement) => !a.target)
			// eslint-disable-next-line no-return-assign,no-param-reassign
			.forEach((a: HTMLLinkElement) => a.target = '_top')
		;
	}
}
