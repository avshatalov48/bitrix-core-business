import { Type, Cache, Tag } from 'main.core';
import type { SlideOptions } from './types/slide-options';

export default class Slide
{
	#id: string = '';
	#title: string = '';
	#description: string = '';
	#className: string = '';
	#image: ?string = null;
	#video: ?string = null;
	#autoplay: boolean = false;
	#html: string | HTMLElement | null = null;
	#iframe: ?HTMLIFrameElement = null;
	#cache = new Cache.MemoryCache();

	constructor(options: SlideOptions)
	{
		options = Type.isPlainObject(options) ? options : {};

		this.#id = Type.isStringFilled(options.id) ? options.id : this.#id;
		this.#className = Type.isStringFilled(options.className) ? options.className : this.#className;
		this.#image = Type.isStringFilled(options.image) ? options.image : this.#image;
		this.#title = Type.isStringFilled(options.title) ? options.title : this.#title;
		this.#description = Type.isStringFilled(options.description) ? options.description : this.#description;

		this.#setVideo(options.video);
		this.#autoplay = Type.isBoolean(options.autoplay) ? options.autoplay : this.#autoplay;

		if (Type.isElementNode(options.html) || Type.isStringFilled(options.html))
		{
			this.#html = options.html;
		}
	}

	getId(): string
	{
		return this.#id;
	}

	getTitle(): string
	{
		return this.#title;
	}

	getDescription(): string
	{
		return this.#description;
	}

	getBullet(): HTMLElement
	{
		return this.#cache.remember('bullet', () => {
			return Tag.render`<span class="ui-whats-new-bullet" title="${this.getTitle()}"></span>`;
		});
	}

	#setVideo(video: string)
	{
		if (!Type.isStringFilled(video))
		{
			return;
		}

		const url = new URL(video);
		url.searchParams.append('enablejsapi', '1');

		this.#video = url.toString();
	}

	getIframe(): ?HTMLIFrameElement
	{
		return this.#iframe;
	}

	pauseVideo(): void
	{
		if (this.getIframe())
		{
			this.getIframe().contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'stopVideo' }), '*');
		}
	}

	playVideo(): void
	{
		if (this.getIframe())
		{
			this.getIframe().contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo' }), '*');
		}
	}

	isVideo(): boolean
	{
		return this.#video !== null;
	}

	isAutoplay(): boolean
	{
		return this.#autoplay;
	}

	getContainer(): HTMLElement
	{
		return this.#cache.remember('container', () => {
			if (this.#video)
			{
				this.#iframe = Tag.render`<iframe 
						src="${this.#video}" 
						id="${this.#id}" 
						class="ui-whats-new-slide-item ${this.#className}" 
						title="YouTube video player"
						frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
						allowfullscreen></iframe>
				`;

				return this.#iframe;
			}
			else
			{
				return Tag.render`<div 
						id="${this.#id}" 
						class="ui-whats-new-slide-item ${this.#className}" 
						${this.#image ? 'style="background-image: url(' + this.#image + ')"' : ''}>${this.#html ?? ''}</div>`
					;
			}
		});
	}
}