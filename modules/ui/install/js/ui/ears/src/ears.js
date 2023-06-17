import { Tag, Dom, Cache, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { TouchController } from	'./touch-controller';
import './style.css';

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
		this.vertical = options.vertical || null;
		this.itemsInShow = options.itemsInShow || null;
		if (this.itemsInShow)
		{
			this.noScrollbar = true;
		}

		this.itemSize = null;

		// layouts
		this.wrapper = null;
		this.leftEar = null;
		this.rightEar = null;
		this.topEar = null;
		this.bottomEar = null;
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

		if (this.vertical)
		{
			if (this.itemsInShow)
			{
				this.getBottomEar().addEventListener('click', this.scrollToNext.bind(this));
				this.getTopEar().addEventListener('click', this.scrollToPrev.bind(this));
			}
			else
			{
				this.getBottomEar().addEventListener('mouseenter', this.scrollBottom.bind(this));
				this.getBottomEar().addEventListener('mouseleave', this.stopScroll.bind(this));
				this.getBottomEar().addEventListener('mousedown', this.stopScroll.bind(this));
				this.getBottomEar().addEventListener('mouseup', this.scrollBottom.bind(this));

				this.getTopEar().addEventListener('mouseenter', this.scrollTop.bind(this));
				this.getTopEar().addEventListener('mouseleave', this.stopScroll.bind(this));
				this.getTopEar().addEventListener('mousedown', this.stopScroll.bind(this));
				this.getTopEar().addEventListener('mouseup', this.scrollTop.bind(this));
			}

		}

		if (!this.vertical)
		{
			if (this.itemsInShow)
			{
				this.getRightEar().addEventListener('click', this.scrollToNext.bind(this));
				this.getLeftEar().addEventListener('click', this.scrollToPrev.bind(this));
			}
			else
			{
				this.getLeftEar().addEventListener('mouseenter', this.scrollLeft.bind(this));
				this.getLeftEar().addEventListener('mouseleave', this.stopScroll.bind(this));
				this.getLeftEar().addEventListener('mousedown', this.stopScroll.bind(this));
				this.getLeftEar().addEventListener('mouseup', this.scrollLeft.bind(this));

				this.getRightEar().addEventListener('mouseenter', this.scrollRight.bind(this));
				this.getRightEar().addEventListener('mouseleave', this.stopScroll.bind(this));
				this.getRightEar().addEventListener('mousedown', this.stopScroll.bind(this));
				this.getRightEar().addEventListener('mouseup', this.scrollRight.bind(this));
			}
		}
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

	scrollToPrev()
	{
		if (this.vertical)
		{
			this.container.scrollTo({
				top: this.container.scrollTop - this.getItemSize(),
				behavior: 'smooth'
			});
		}
		else
		{
			this.container.scrollTo({
				left: this.container.scrollLeft - this.getItemSize(),
				behavior: 'smooth'
			});
		}
	}

	scrollToNext()
	{
		if (this.vertical)
		{
			this.container.scrollTo({
				top: this.container.scrollTop + this.getItemSize(),
				behavior: 'smooth'
			});
		}
		else
		{
			this.container.scrollTo({
				left: this.container.scrollLeft + this.getItemSize(),
				behavior: 'smooth'
			});
		}
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

	getItemSize()
	{
		if (!this.itemSize)
		{
			const itemNode = this.container.firstElementChild;
			this.itemSize = this.vertical
				? this.container.firstElementChild.offsetHeight
				: this.container.firstElementChild.offsetWidth;

			let spaceInt = 0;

			if (this.vertical)
			{
				spaceInt = parseInt(window.getComputedStyle(itemNode).marginTop) > parseInt(window.getComputedStyle(itemNode).marginTop)
					? parseInt(window.getComputedStyle(itemNode).marginTop)
					: parseInt(window.getComputedStyle(itemNode).marginBottom);
			}
			else
			{
				spaceInt = parseInt(window.getComputedStyle(itemNode).marginLeft) + parseInt(window.getComputedStyle(itemNode).marginRight);
			}

			if (spaceInt > 0)
			{
				this.itemSize = this.itemSize + spaceInt;
			}
		}

		return this.itemSize;
	}

	setWrapper() {
		this.container.classList.add('ui-ear-container');
		this.container.classList.add(this.vertical ? '--vertical' : '--horizontal');

		if (this.noScrollbar)
		{
			this.container.classList.add('ui-ear-container-no-scrollbar');
		}

		Dom.append(this.getWrapper(), this.parentContainer);

		if (this.itemsInShow)
		{
			this.container.style.setProperty(this.vertical ? 'height' : 'width', this.getItemSize() * this.itemsInShow  + 'px');
		}
	}

	getWrapper()
	{
		return this.cache.remember('wrapper', () => {
			return Tag.render`
					<div class='ui-ears-wrapper ${this.smallSize ? ' ui-ears-wrapper-sm' : ''} ${this.className ? this.className : ''}'>
						${this.vertical ? this.getTopEar() : this.getLeftEar() }
						${this.vertical ? this.getBottomEar() : this.getRightEar()}
						${this.container}
					</div>
				`;
		});
	}

	getTopEar()
	{
		return this.cache.remember('topEar', () => {
			return Tag.render`
					<div class='ui-ear ui-ear-top'></div>
				`;
		});
	}

	getBottomEar()
	{
		return this.cache.remember('bottomEar', () => {
			return Tag.render`
					<div class='ui-ear ui-ear-bottom'></div>
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
		if (this.vertical)
		{
			this.toggleTopEar();
			this.toggleBottomEar();
		}
		else
		{
			this.toggleRightEar();
			this.toggleLeftEar();
		}
	}

	toggleTopEar()
	{
		if (this.container.scrollTop > 0)
		{
			this.getTopEar().classList.add('ui-ear-show');
		}
		else
		{
			this.getTopEar().classList.remove('ui-ear-show');
		}
	}

	toggleBottomEar()
	{
		if (this.container.scrollHeight > this.container.offsetHeight
			&& Math.ceil(this.container.offsetHeight + this.container.scrollTop) < this.container.scrollHeight)
		{
			this.getBottomEar().classList.add('ui-ear-show');
		}
		else
		{
			this.getBottomEar().classList.remove('ui-ear-show');
		}
	}

	toggleRightEar()
	{
		if (this.container.scrollWidth > this.container.offsetWidth
			&& Math.ceil(this.container.offsetWidth + this.container.scrollLeft) < this.container.scrollWidth)
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

	scrollTop()
	{
		console.log('scrollTop');
		this.stopScroll('bottom');
		let previous = this.container.scrollTop;
		this.container.scrollTop -= 10;
		this.emit('onEarsAreMoved');
		if (this.container.scrollTop <= 10)
		{
			this.emit('onEarsAreHidden');
		}

		this.setDelay();
		this.scrollInterval = setInterval(
			this.scrollTop.bind(this),
			this.delay);

		this.top = true;

	}

	scrollBottom()
	{
		console.log('scrollBottom');
		this.stopScroll('top');
		let previous = this.container.scrollTop;
		this.container.scrollTop += 10;
		this.emit('onEarsAreMoved');
		if (this.container.scrollTop >= 0 && previous < 0)
		{
			this.emit('onEarsAreHidden');
		}

		this.setDelay();
		this.scrollInterval = setInterval(
			this.scrollBottom.bind(this),
			this.delay);

		this.bottom = true;
		
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
		else if (direction === 'bottom')
		{
			this.bottom = false;
		}
		else if (direction === 'top')
		{
			this.top = false;
		}
	}

	initTouchScroll()
	{
		new TouchController({
			target: this.container
		});
	}
}