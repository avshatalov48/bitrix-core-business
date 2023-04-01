import { Tag, Dom, Cache, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { TouchController } from	'./touch-controller';

export class Ears extends EventEmitter
{
	constructor(options)
	{
		super(...arguments);
		this.setEventNamespace('BX.UI.Ears');
		this.container = options.container || null;
		this.smallSize = options.smallSize || null;
		this.noScrollbar = options.noScrollbar ? options.noScrollbar : false;
		this.className = options.className ? options.className : null;
		this.mousewheel = options.mousewheel || null;
		this.touchScroll = options.touchScroll || null;

		// layouts
		this.wrapper = null;
		this.leftEar = null;
		this.rightEar = null;
		this.parentContainer = Type.isDomNode(this.container) ? this.container.parentNode : null;
		this.delay = 12;
		this.scrollTimeout = null;
		this.cache = new Cache.MemoryCache();
	}

	bindEvents()
	{
		this.container.addEventListener('scroll', this.toggleEars.bind(this));

		if (this.mousewheel)
		{
			this.container.addEventListener('wheel', this.onWheel.bind(this));
		}

		this.getLeftEar().addEventListener('mouseenter', this.scrollLeft.bind(this));
		this.getLeftEar().addEventListener('mouseleave', this.stopScroll.bind(this));
		this.getLeftEar().addEventListener('mousedown', this.stopScroll.bind(this));
		this.getLeftEar().addEventListener('mouseup', this.scrollLeft.bind(this));

		this.getRightEar().addEventListener('mouseenter', this.scrollRight.bind(this));
		this.getRightEar().addEventListener('mouseleave', this.stopScroll.bind(this));
		this.getRightEar().addEventListener('mousedown', this.stopScroll.bind(this));
		this.getRightEar().addEventListener('mouseup', this.scrollRight.bind(this));
	}

	init(): this
	{
		if (!this.container)
		{
			console.warn('BX.UI.Ears.Preview: \'container\' is not defined');
			return;
		}

		this.setWrapper();
		this.bindEvents();

		if (this.touchScroll)
		{
			this.initTouchScroll();
		}

		setTimeout(() => {
			if (this.container.scrollWidth > this.container.offsetWidth)
			{
				this.toggleRightEar();

				let activeItem = this.container.querySelector('[data-role="ui-ears-active"]');

				activeItem ? this.scrollToActiveItem(activeItem) : null;
			}
		}, 600);
		return this;
	}

	scrollToActiveItem(activeItem)
	{
		let scrollToPoint = activeItem.offsetLeft - (this.container.offsetWidth / 2 - activeItem.offsetWidth / 2);
		let scrollWidth = 0;
		let interval = setInterval(() => {
			if( scrollWidth >= scrollToPoint ||
				scrollWidth + this.container.offsetWidth >= this.container.scrollWidth)
			{
				clearInterval(interval);
			}

			this.container.scrollLeft = scrollWidth += 10;
		},10)
	}

	onWheel(event)
	{
		if (event.deltaY < 0 || event.deltaX > 0)
		{
			this.scrollRight();
		}
		else
		{
			this.scrollLeft();
		}

		clearTimeout(this.scrollTimeout);
		this.scrollTimeout = setTimeout(() => this.stopScroll(), 150);
		event.preventDefault();
	}

	setWrapper() {
		this.container.classList.add('ui-ear-container');
		if (this.noScrollbar)
		{
			this.container.classList.add('ui-ear-container-no-scrollbar');
		}
		Dom.append(this.getWrapper(), this.parentContainer);
	}

	getWrapper()
	{
		return this.cache.remember('wrapper', () => {
			return Tag.render`
					<div class='ui-ears-wrapper ${this.smallSize ? ' ui-ears-wrapper-sm' : ''} ${this.className ? this.className : ''}'>
						${this.getLeftEar()}
						${this.getRightEar()}
						${this.container}
					</div>
				`;
		});
	}

	getLeftEar()
	{
		return this.cache.remember('leftEar', () => {
			return Tag.render`
					<div class='ui-ear ui-ear-left'></div>
				`;
		});
	}

	getRightEar()
	{
		return this.cache.remember('rightEar', () => {
			return Tag.render`
					<div class='ui-ear ui-ear-right'></div>
				`;
		});
	}

	toggleEars() 
	{
		this.toggleRightEar();
		this.toggleLeftEar();
	}

	toggleRightEar()
	{
		if (this.container.scrollWidth > this.container.offsetWidth
			&& (this.container.offsetWidth + this.container.scrollLeft) < this.container.scrollWidth)
		{
			this.getRightEar().classList.add('ui-ear-show');
		}
		else
		{
			this.getRightEar().classList.remove('ui-ear-show');
		}
	}

	toggleLeftEar()
	{
		if (this.container.scrollLeft > 0)
		{
			this.getLeftEar().classList.add('ui-ear-show');
		}
		else
		{
			this.getLeftEar().classList.remove('ui-ear-show');
		}
	}

	scrollLeft()
	{
		this.stopScroll('right');

		let previous = this.container.scrollLeft;
		this.container.scrollLeft -= 10;
		this.emit('onEarsAreMoved');
		if (this.container.scrollLeft <= 0 && previous > 0)
		{
			this.emit('onEarsAreHidden');
		}

		this.setDelay();
		this.scrollInterval = setInterval(
			this.scrollLeft.bind(this),
			this.delay);

		this.left = true;
	}

	scrollRight()
	{
		this.stopScroll('left');

		this.container.scrollLeft += 10;
		this.emit('onEarsAreMoved');
		if (this.container.scrollLeft <= 10)
		{
			this.emit('onEarsAreShown');
		}

		this.setDelay();
		this.scrollInterval = setInterval(
			this.scrollRight.bind(this),
			this.delay);

		this.right = true;
	}

	setDelay()
	{
		if (this.container.scrollWidth < this.container.offsetWidth * 1.6)
		{
			this.delay = 20;
			return;
		}

		const fullScrollLeft = this.container.scrollWidth - this.container.offsetWidth;
		const conditionRight = this.container.scrollLeft > fullScrollLeft / 1.3;
		const conditionLeft = this.container.scrollLeft < fullScrollLeft / 4;

		if (this.container.scrollLeft === fullScrollLeft)
		{
			this.delay = 12;
		}

		if (this.left)
		{
			if (conditionLeft)
			{
				this.delay = 25;
			}
			else {
				this.delay = 12;
			}
		}

		if (this.right)
		{
			if (conditionRight)
			{
				this.delay = 25;
			}
			else {
				this.delay = 12;
			}
		}
	}

	stopScroll(direction) {
		if (this.scrollInterval)
		{
			clearInterval(this.scrollInterval);
			this.scrollInterval = 0;
		}

		if (direction === 'right')
		{
			this.right = false;
		}
		else if (direction === 'left')
		{
			this.left = false;
		}
	}

	initTouchScroll()
	{
		new TouchController({
			target: this.container
		});
	}
}
