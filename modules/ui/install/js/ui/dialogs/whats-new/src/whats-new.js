import { Cache, Dom, Event, Tag, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup, type PopupOptions } from 'main.popup';
import Slide from './slide';
import type { WhatsNewOptions } from './types/whats-new-options';

export default class WhatsNew extends EventEmitter
{
	#popup: Popup = null;
	#slides: Array<Slide> = [];
	#cache = new Cache.MemoryCache();
	#position: ?number = null;
	#popupOptions: PopupOptions = {};
	infinityLoop: boolean = false;
	#documentKeyDownHandler: Function = null;
	#destroying: boolean = false;

	constructor(options: WhatsNewOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Dialogs.WhatsNew');

		options = Type.isPlainObject(options) ? options : {};

		if (!Type.isArrayFilled(options.slides))
		{
			throw new Error('NewStructurePopup: "items" parameter is required.');
		}

		options.slides.forEach(slideOptions => {
			this.#slides.push(new Slide(slideOptions));
		});

		if (Type.isPlainObject(options.popupOptions))
		{
			this.#popupOptions = options.popupOptions;
		}

		if (Type.isBoolean(options.infinityLoop))
		{
			this.infinityLoop = options.infinityLoop;
		}

		this.#documentKeyDownHandler = this.#handleDocumentKeyDown.bind(this);
		this.subscribeFromOptions(options.events);
	}

	getPopup(): Popup
	{
		if (this.#popup !== null)
		{
			return this.#popup;
		}

		this.#popup = new Popup(Object.assign({
			className: 'ui-whats-new-popup',
			closeIcon: false,
			closeByEsc: true,
			overlay: true,
			cacheable: false,
			animation: 'scale',
			content: this.getContentContainer(),
			width: 720,
			height: 530,
			autoHide: true
		}, this.#popupOptions));

		this.#popup.subscribe('onDestroy', this.#handlePopupDestroy.bind(this));
		this.#popup.subscribe('onShow', this.#handlePopupShow.bind(this));
		this.#popup.subscribe('onClose', this.#handlePopupClose.bind(this));

		this.selectSlide();

		return this.#popup;
	}

	getCurrentSlide(): Slide
	{
		return this.#slides[this.#position];
	}

	getSlides(): Slide[]
	{
		return this.#slides;
	}

	getSlideByPosition(position: number): ?Slide
	{
		return this.#slides[position] ?? null;
	}

	getPositionBySlide(slide: Slide): ?number
	{
		for (let position = 0; position < this.#slides.length; position++)
		{
			const current = this.#slides[position];
			if (current === slide)
			{
				return position;
			}
		}

		return null;
	}

	getFirstPosition(): number
	{
		return 0;
	}

	getLastPosition(): number
	{
		return this.#slides.length - 1;
	}

	getContentContainer(): HTMLElement
	{
		return this.#cache.remember('content', () => {
			return Tag.render`
				<div class="ui-whats-new-content"> 
					${this.getHeadContainer()}
					<div class="ui-whats-new-slide-wrap"> 
						${this.getPrevBtn()} 
						${this.getNextBtn()} 
						<div class="ui-whats-new-slide-inner">${this.getSliderBox()}</div>  
					</div> 
					<div class="ui-whats-new-bullet-box" onclick="${this.#handleBulletClick.bind(this)}">${
						this.#slides.map(slide => slide.getBullet())
					}</div>
					<div class="ui-whats-new-close-btn" onclick="${this.hide.bind(this)}"></div>
				</div>
			`;
		});
	}

	getHeadContainer(): HTMLElement
	{
		return this.#cache.remember('head', () => {
			return Tag.render`
				<div class="ui-whats-new-head"> 
					${this.getTitleContainer()}
					${this.getDescContainer()}
				</div>
			`;
		});
	}

	getTitleContainer(): HTMLElement
	{
		return this.#cache.remember('title', () => {
			return Tag.render`<div class="ui-whats-new-title"></div>`;
		});
	}

	getDescContainer(): HTMLElement
	{
		return this.#cache.remember('description', () => {
			return Tag.render`<div class="ui-whats-new-desc"></div>`;
		});
	}

	getSliderBox(): HTMLElement
	{
		return this.#cache.remember('sliderBox', () => {
			return Tag.render`<div class="ui-whats-new-slide-box">${
				this.#slides.map(slide => slide.getContainer())
			}</div>`;
		});
	}

	getPrevBtn(): HTMLElement
	{
		return this.#cache.remember('prevBtn', () => {
			return Tag.render`
				<div 
					class="ui-whats-new-slide-btn --btn-prev" 
					onclick="${this.selectPrevSlide.bind(this)}">
				</div>`
				;
		});
	}

	getNextBtn(): HTMLElement
	{
		return this.#cache.remember('nextBtn', () => {
			return Tag.render`
				<div 
					class="ui-whats-new-slide-btn --btn-next" 
					onclick="${this.selectNextSlide.bind(this)}">
				</div>
			`;
		});
	}

	show(): void
	{
		this.getPopup().show();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	destroy(): void
	{
		if (this.#destroying)
		{
			return;
		}

		this.#destroying = true;
		this.emit('onDestroy');

		this.#unbindEvents();
		this.getPopup().destroy();

		for (const property in this)
		{
			if (this.hasOwnProperty(property))
			{
				delete this[property];
			}
		}

		Object.setPrototypeOf(this, null);
	}

	selectPrevSlide(): void
	{
		if (this.infinityLoop && this.#position === this.getFirstPosition())
		{
			this.selectSlide(this.getLastPosition());
		}
		else
		{
			this.selectSlide(this.#position - 1);
		}
	}

	selectNextSlide(): void
	{
		if (this.infinityLoop && this.#position === this.getLastPosition())
		{
			this.selectSlide(this.getFirstPosition());
		}
		else
		{
			this.selectSlide(this.#position + 1);
		}
	}

	selectSlide(position = 0): void
	{
		const firstPosition = this.getFirstPosition();
		const lastPosition = this.getLastPosition();

		position = Math.min(Math.max(position, firstPosition), lastPosition);
		if (this.#position === position)
		{
			return;
		}

		const currentSlide = this.getSlideByPosition(this.#position);
		const newSlide = this.getSlideByPosition(position);
		const event = new BaseEvent({ data: { currentSlide, newSlide } });

		this.emit('Slide:onBeforeSelect', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#position = position;

		// Ears
		if (!this.infinityLoop)
		{
			if (position === firstPosition)
			{
				Dom.addClass(this.getPrevBtn(), '--hide');
				Dom.removeClass(this.getNextBtn(), '--hide');
			}
			else if (position === lastPosition)
			{
				Dom.removeClass(this.getPrevBtn(), '--hide');
				Dom.addClass(this.getNextBtn(), '--hide');
			}
			else
			{
				Dom.removeClass(this.getPrevBtn(), '--hide');
				Dom.removeClass(this.getNextBtn(), '--hide');
			}
		}

		// Sliding
		Dom.style(
			this.getSliderBox(),
			{
				transform: 'translateX(' + (-position * this.getSliderBox().offsetWidth) + 'px)',
			}
		);

		// Bullets
		this.#slides.forEach((slide, index) => {
			if (position === index)
			{
				Dom.addClass(slide.getBullet(), '--active');
			}
			else
			{
				Dom.removeClass(slide.getBullet(), '--active');
			}
		});

		// Header
		Dom.style(this.getHeadContainer(), { opacity: 0, transition: 'none' });

		const title = newSlide.getTitle().trim();
		const desc = newSlide.getDescription().trim();

		if (Type.isStringFilled(title))
		{
			Dom.removeClass(this.getContentContainer(), '--empty-head');
			if (Type.isStringFilled(desc))
			{
				Dom.removeClass(this.getContentContainer(), '--empty-desc');
			}
			else
			{
				Dom.addClass(this.getContentContainer(), '--empty-desc');
			}
		}
		else
		{
			Dom.addClass(this.getContentContainer(), '--empty-head');
		}

		this.getTitleContainer().innerHTML = title;
		this.getDescContainer().innerHTML = desc;

		const finalize = () => {
			this.getSlides().forEach((slide: Slide) => {
				if (this.getCurrentSlide() !== slide)
				{
					Dom.style(slide.getContainer(), 'opacity', null);
					slide.pauseVideo();
				}
			});

			Dom.style(this.getHeadContainer(), 'opacity', null);
		};

		if (newSlide.isVideo() && newSlide.isAutoplay())
		{
			newSlide.playVideo();
		}

		setTimeout(finalize, 700);

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				if (currentSlide)
				{
					Dom.style(currentSlide.getContainer(), 'opacity', 0);
				}
				Dom.style(newSlide.getContainer(), 'opacity', 1);
				Dom.style(this.getHeadContainer(), 'opacity', 1);
				Dom.style(this.getHeadContainer(), 'transition', null);
			});
		});

		this.emit('Slide:onSelect', { slide: newSlide });
	}

	#bindEvents(): void
	{
		Event.bind(document, 'keydown', this.#documentKeyDownHandler);
	}

	#unbindEvents(): void
	{
		Event.unbind(document, 'keydown', this.#documentKeyDownHandler);
	}

	#handleDocumentKeyDown(event: KeyboardEvent): void
	{
		if (!this.getPopup().isShown())
		{
			this.#unbindEvents();

			return;
		}

		if (event.metaKey || event.ctrlKey || event.altKey)
		{
			return;
		}

		if (event.key === 'ArrowLeft')
		{
			this.selectPrevSlide();
		}
		else if (event.key === 'ArrowRight')
		{
			this.selectNextSlide();
		}
	}

	#handleBulletClick(event: MouseEvent): void
	{
		const slide = this.getSlides().find((slide: Slide) => {
			return event.target === slide.getBullet();
		});

		const position = this.getPositionBySlide(slide);
		if (position !== null)
		{
			this.selectSlide(position);
		}
	}

	#handlePopupShow(): void
	{
		this.#bindEvents();

		this.emit('onShow');
	}

	#handlePopupClose(): void
	{
		this.#unbindEvents();
		this.getSlides().forEach((slide: Slide) => {
			slide.pauseVideo();
		});

		this.emit('onHide');
	}

	#handlePopupDestroy(): void
	{
		this.getSlides().forEach((slide: Slide) => {
			slide.pauseVideo();
		});

		this.destroy();
	}
}