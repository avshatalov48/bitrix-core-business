import {Tag, Dom, Cache} from 'main.core';

export class Ears
{
	constructor(options)
	{
		this.container = options.container;
		this.smallSize = options.smallSize || null;
		this.noScrollbar = options.noScrollbar ? options.noScrollbar : false;
		this.wrapper = null;
		this.leftEar = null;
		this.rightEar = null;
		this.parentContainer = this.container.parentNode;
		this.cache = new Cache.MemoryCache();
	}

	bindEvents() {
		this.container.addEventListener('scroll', this.toggleEars.bind(this));
		this.getLeftEar().addEventListener('mouseenter', this.scrollLeft.bind(this));
		this.getLeftEar().addEventListener('mouseleave', this.stopScroll.bind(this));
		this.getRightEar().addEventListener('mouseenter', this.scrollRight.bind(this));
		this.getRightEar().addEventListener('mouseleave', this.stopScroll.bind(this));

	}

	init() {
		this.setWrapper();
		this.bindEvents();

		setTimeout(function() {
			if(this.container.scrollWidth > this.container.offsetWidth) {
				this.toggleRightEar();
			}
		}.bind(this), 600);

	}

	setWrapper() {
		this.container.classList.add("ui-ear-container");
		if(this.noScrollbar)
		{
			this.container.classList.add("ui-ear-container-no-scrollbar");
		}
		Dom.append(this.getWrapper(), this.parentContainer);
	}

	getWrapper()
	{
		return this.cache.remember('wrapper', () => {
			return Tag.render`
					<div class="ui-ears-wrapper ${this.smallSize ? ' ui-ears-wrapper-sm' : ''}">
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
					<div class="ui-ear ui-ear-left"></div>
				`;
		});
	}

	getRightEar()
	{
		return this.cache.remember('rightEar', () => {
			return Tag.render`
					<div class="ui-ear ui-ear-right"></div>
				`;
		});
	}

	toggleEars() {
		this.toggleRightEar();
		this.toggleLeftEar();
	}

	toggleRightEar() {
		if (this.container.scrollWidth > this.container.offsetWidth
			&& (this.container.offsetWidth + this.container.scrollLeft) < this.container.scrollWidth)
		{
			this.getRightEar().classList.add("ui-ear-show");
		}
		else
		{
			this.getRightEar().classList.remove("ui-ear-show");
		}
	}

	toggleLeftEar() {
		if (this.container.scrollLeft > 0)
		{
			this.getLeftEar().classList.add("ui-ear-show");
		}
		else
		{
			this.getLeftEar().classList.remove("ui-ear-show");
		}
	}

	scrollLeft () {
		this.stopScroll();
		this.scrollInterval = setInterval(
			function() {
				this.container.scrollLeft -= 10;
			}.bind(this),
			20
		);
	}

	scrollRight () {
		this.stopScroll();
		this.scrollInterval = setInterval(
			function() {
				this.container.scrollLeft += 10;
			}.bind(this),
			20
		);
	}

	stopScroll() {
		if (this.scrollInterval)
		{
			clearInterval(this.scrollInterval);
			this.scrollInterval = 0;
		}
	}
}