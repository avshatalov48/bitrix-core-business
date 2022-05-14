import { Tag, Event, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

export default class CountersItem
{
	constructor(options)
	{
		this.count = options.count;
		this.name = options.name;
		this.type = options.type;
		this.color = options.color;
		this.filterPresetId = options.filterPresetId;
		this.filter = options.filter;
		this.activeByDefault = !!options.activeByDefault;

		this.$container = null;
		this.$remove = null;
		this.$counter = null;

		this.bindEvents();
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Socialnetwork.Interface.Counters:active', (param) => {
			this !== param.data ? this.unActive() : null;
		});
	}

	getCounter()
	{
		if (!this.$counter)
		{
			const count = this.count > 99 ? '99+' : this.count;
			this.$counter = Tag.render`
				<div class="sonet-counters--item-num ${this.getCounterColor()}">
					<div class="sonet-counters--item-num-text --stop --without-animate">${count}</div>
				</div>
			`;
		}

		return this.$counter;
	}

	getCounterColor()
	{
		if (!this.color)
		{
			return null;
		}

		return `--${this.color}`;
	}

	animateCounter(start, value)
	{
		if (start > 99 && value > 99)
		{
			return;
		}

		value > 99
			? value = 99
			: null;

		if (start > 99)
		{
			start = 99;
		}

		let duration = start - value;
		if (duration < 0)
		{
			duration = duration * -1;
		}

		this.$counter.innerHTML = '';
		this.getCounter().classList.remove('--update');
		this.getCounter().classList.remove('--update-multi');

		if (duration > 5)
		{
			setTimeout(()=> {
				this.getCounter().style.animationDuration = (duration * 50) + 'ms';
				this.getCounter().classList.add('--update-multi');
			});
		}
		const timer = setInterval(()=> {
			value < start
				? start--
				: start++;

			const node = Tag.render`
				<div class="sonet-counters--item-num-text ${value < start ? '--decrement' : ''}">${start}</div>
			`;

			if (start === value)
			{
				node.classList.add('--stop');

				if (duration < 5)
				{
					this.getCounter().classList.add('--update');
				}

				clearInterval(timer);
				start === 0 ? this.fade() : this.unFade();
			}

			if (start !== value)
			{
				Event.bind(node, 'animationend', ()=> {
					node.parentNode.removeChild(node);
				});
			}
			this.$counter.appendChild(node);
		}, 50);
	}

	updateCount(param: number)
	{
		if (this.count === param)
		{
			return;
		}

		this.animateCounter(this.count, param);

		this.count = param;
	}

	getRemove()
	{
		if (!this.$remove)
		{
			this.$remove = Tag.render`
				<div class="sonet-counters--item-remove"></div>
			`;
		}

		return this.$remove;
	}

	fade()
	{
		this.getContainer().classList.add('--fade');
	}

	unFade()
	{
		this.getContainer().classList.remove('--fade');
	}

	active(node: HTMLElement)
	{
		const targetNode = Type.isDomNode(node) ? node : this.getContainer();
		targetNode.classList.add('--hover');
		EventEmitter.emit('BX.Socialnetwork.Interface.Counters:active', this);
	}

	unActive(node: HTMLElement)
	{
		const targetNode = Type.isDomNode(node) ? node : this.getContainer();
		targetNode.classList.remove('--hover');
		EventEmitter.emit('BX.Socialnetwork.Interface.Counters:unActive', this);
	}

	adjustClick()
	{
		EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
			counter: this,
		});

		this.$container.classList.contains('--hover')
			? this.unActive()
			: this.active()
		;
	}

	getContainer(): HTMLElement
	{
		if (!this.$container)
		{
			this.$container = Tag.render`
				<div class="sonet-counters--item ${Number(this.count) === 0 ? ' --fade' : ''}">
					<div class="sonet-counters--item-wrapper">
						${this.getCounter()}
						<div class="sonet-counters--item-title">${this.name}</div>
						${this.getRemove()}
					</div>
				</div>
			`;

			if (
				this.filter.isFilteredByPresetId(this.filterPresetId)
				|| this.activeByDefault
			)
			{
				this.active(this.$container);
			}

			Event.bind(this.$container, 'click', this.adjustClick.bind(this));
		}

		return this.$container;
	}
}