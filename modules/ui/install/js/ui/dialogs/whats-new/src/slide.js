import { Type, Cache, Tag, Dom } from 'main.core';
import type { SlideOptions } from './types/slide-options';
import type { VideoOptions, VideoSourceOptions } from './types/video-options';

export default class Slide
{
	#id: string = '';
	#title: string = '';
	#description: string = '';
	#className: string = '';
	#image: ?string = null;
	#videoUrl: ?string = null;
	#videoIframe: ?HTMLIFrameElement = null;
	#videoHtmlElement: ?HTMLVideoElement = null;
	#videoOptions: ?VideoOptions = null;
	#videoPlayPromise: Promise = null;
	#autoplay: boolean = false;
	#html: string | HTMLElement | null = null;
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

	#setVideo(options: string | VideoOptions)
	{
		if (Type.isStringFilled(options))
		{
			const url = new URL(options);
			if (url.host.includes('youtube'))
			{
				url.searchParams.append('enablejsapi', '1');
			}

			this.#videoUrl = url.toString();
		}
		else if (Type.isPlainObject(options) && Type.isArrayFilled(options.sources))
		{
			this.#videoOptions = options;
		}
	}

	getVideoIframe(): ?HTMLIFrameElement
	{
		return this.#videoIframe;
	}

	getVideoHtmlElement(): ?HTMLVideoElement
	{
		return this.#videoHtmlElement;
	}

	pauseVideo(): void
	{
		if (this.getVideoIframe())
		{
			this.getVideoIframe().contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'stopVideo' }), '*');
		}
		else if (this.getVideoHtmlElement())
		{
			if (this.#videoPlayPromise)
			{
				this.#videoPlayPromise
					.then(() => {
						this.getVideoHtmlElement().pause();
						this.#videoPlayPromise = null;
					})
					.catch(() => {

					})
				;
			}
		}
	}

	playVideo(): void
	{
		if (this.getVideoIframe())
		{
			this.getVideoIframe().contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo' }), '*');
		}
		else if (this.getVideoHtmlElement())
		{
			this.#videoPlayPromise = this.getVideoHtmlElement().play();
		}
	}

	isVideo(): boolean
	{
		return this.#videoUrl !== null || this.#videoOptions !== null;
	}

	isAutoplay(): boolean
	{
		return this.#autoplay;
	}

	getContainer(): HTMLElement
	{
		return this.#cache.remember('container', () => {
			if (this.#videoUrl)
			{
				this.#videoIframe = Tag.render`<iframe 
						src="${this.#videoUrl}" 
						id="${this.#id}" 
						class="ui-whats-new-slide-item ${this.#className}" 
						frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
						allowfullscreen></iframe>
				`;

				return this.#videoIframe;
			}
			else if (this.#videoOptions)
			{
				const sources = [];

				this.#videoOptions.sources.forEach((source: VideoSourceOptions) => {
					sources.push(`<source src="${source.src}" type="${source.type}" />`);
				});

				this.#videoHtmlElement = Tag.render`<video>${sources.join('')}</video>`;
				if (Type.isPlainObject(this.#videoOptions.attrs))
				{
					Dom.attr(this.#videoHtmlElement, this.#videoOptions.attrs);
				}

				return (
					Tag.render`
						<div 
							id="${this.#id}" 
							class="ui-whats-new-slide-item ${this.#className}"
						>${this.#videoHtmlElement}</div>`
				);
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