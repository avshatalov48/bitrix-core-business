import { Cache, Dom, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { TouchController } from './touch-controller';
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
		this.immediateInit = Type.isBoolean(options.immediateInit) ? options.immediateInit : false;

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
		this.touchController = null;
	}

	bindEvents()
	{
		this.toggleEars = this.toggleEars.bind(this);
		this.onWheel = this.onWheel.bind(this);
		this.scrollToNext = this.scrollToNext.bind(this);
		this.scrollToPrev = this.scrollToPrev.bind(this);
		this.scrollBottom = this.scrollBottom.bind(this);
		this.stopScroll = this.stopScroll.bind(this);
		this.scrollTop = this.scrollTop.bind(this);
		this.scrollLeft = this.scrollLeft.bind(this);
		this.scrollRight = this.scrollRight.bind(this);

		this.container.addEventListener('scroll', this.toggleEars);

		if (this.mousewheel)
		{
			this.container.addEventListener('wheel', this.onWheel);
		}

		if (this.vertical)
		{
			if (this.itemsInShow)
			{
				this.getBottomEar().addEventListener('click', this.scrollToNext);
				this.getTopEar().addEventListener('click', this.scrollToPrev);
			}
			else
			{
				this.getBottomEar().addEventListener('mouseenter', this.scrollBottom);
				this.getBottomEar().addEventListener('mouseleave', this.stopScroll);
				this.getBottomEar().addEventListener('mousedown', this.stopScroll);
				this.getBottomEar().addEventListener('mouseup', this.scrollBottom);

				this.getTopEar().addEventListener('mouseenter', this.scrollTop);
				this.getTopEar().addEventListener('mouseleave', this.stopScroll);
				this.getTopEar().addEventListener('mousedown', this.stopScroll);
				this.getTopEar().addEventListener('mouseup', this.scrollTop);
			}

		}

		if (!this.vertical)
		{
			if (this.itemsInShow)
			{
				this.getRightEar().addEventListener('click', this.scrollToNext);
				this.getLeftEar().addEventListener('click', this.scrollToPrev);
			}
			else
			{
				this.getLeftEar().addEventListener('mouseenter', this.scrollLeft);
				this.getLeftEar().addEventListener('mouseleave', this.stopScroll);
				this.getLeftEar().addEventListener('mousedown', this.stopScroll);
				this.getLeftEar().addEventListener('mouseup', this.scrollLeft);

				this.getRightEar().addEventListener('mouseenter', this.scrollRight);
				this.getRightEar().addEventListener('mouseleave', this.stopScroll);
				this.getRightEar().addEventListener('mousedown', this.stopScroll);
				this.getRightEar().addEventListener('mouseup', this.scrollRight);
			}
		}
	}

	#unbindEvents(): void
	{
		this.container.removeEventListener('scroll', this.toggleEars);

		this.container.removeEventListener('wheel', this.onWheel);

		this.getBottomEar().removeEventListener('click', this.scrollToNext);
		this.getTopEar().removeEventListener('click', this.scrollToPrev);

		this.getBottomEar().removeEventListener('mouseenter', this.scrollBottom);
		this.getBottomEar().removeEventListener('mouseleave', this.stopScroll);
		this.getBottomEar().removeEventListener('mousedown', this.stopScroll);
		this.getBottomEar().removeEventListener('mouseup', this.scrollBottom);

		this.getTopEar().removeEventListener('mouseenter', this.scrollTop);
		this.getTopEar().removeEventListener('mouseleave', this.stopScroll);
		this.getTopEar().removeEventListener('mousedown', this.stopScroll);
		this.getTopEar().removeEventListener('mouseup', this.scrollTop);

		this.getRightEar().removeEventListener('click', this.scrollToNext);
		this.getLeftEar().removeEventListener('click', this.scrollToPrev);

		this.getLeftEar().removeEventListener('mouseenter', this.scrollLeft);
		this.getLeftEar().removeEventListener('mouseleave', this.stopScroll);
		this.getLeftEar().removeEventListener('mousedown', this.stopScroll);
		this.getLeftEar().removeEventListener('mouseup', this.scrollLeft);

		this.getRightEar().removeEventListener('mouseenter', this.scrollRight);
		this.getRightEar().removeEventListener('mouseleave', this.stopScroll);
		this.getRightEar().removeEventListener('mousedown', this.stopScroll);
		this.getRightEar().removeEventListener('mouseup', this.scrollRight);
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

		const init = () => {
			if (this.container.scrollWidth > this.container.offsetWidth)
			{
				this.toggleRightEar();

				let activeItem = this.container.querySelector('[data-role="ui-ears-active"]');

				activeItem ? this.scrollToActiveItem(activeItem) : null;
			}

			this.toggleEars();
		};

		if (this.immediateInit)
		{
			init();
		}
		else
		{
			setTimeout(init, 600);
		}

		return this;
	}

	destroy(): void
	{
		clearTimeout(this.scrollTimeout);
		clearInterval(this.scrollInterval);

		this.unsubscribeAll();

		this.#unbindEvents();
		this.touchController?.destroy();
		this.touchController = null;

		this.container.classList.remove('ui-ear-container');
		this.container.classList.remove('--vertical');
		this.container.classList.remove('--horizontal');
		this.container.classList.remove('ui-ear-container-no-scrollbar');

		Dom.replace(this.getWrapper(), this.container);
		this.cache = null;
		this.container = null;
		this.parentContainer = null;
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
		this.stopScroll('bottom');
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
		this.touchController = new TouchController({
			target: this.container,
		});
	}
}
