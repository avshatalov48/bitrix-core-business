import { Tag, Type } from 'main.core';
import { Counter } from 'ui.cnt';
import { EventEmitter } from "main.core.events";

export default class CounterItem {
	constructor(options)
	{
		this.id = options.id;
		this.title = Type.isString(options.title) ? options.title : null;
		this.value = Type.isNumber(options.value) ? options.value : null;
		this.color = Type.isString(options.color) ? options.color : null;
		this.eventsForActive = Type.isObject(options.eventsForActive) ? options.eventsForActive : null;
		this.eventsForUnActive = Type.isObject(options.eventsForUnActive) ? options.eventsForUnActive : null;
		this.panel = options.panel ? options.panel : null;

		this.layout = {
			container: null,
			value: null,
			title: null,
			cross: null
		}

		this.counter = null;
		this.isActive = false;

		if (!this.#getPanel().isMultiselect())
		{
			this.#bindEvents();
		}
	}

	#bindEvents()
	{
		EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', (item) => {
			if (item.data !== this)
			{
				this.deactivate();
			}
		});
	}

	updateValue(param: Number)
	{
		if (Type.isNumber(param))
		{
			this.value = param;
			this.#getCounter().update(param);
			this.#getCounter().show();

			if (param === 0)
			{
				this.updateColor('THEME');
			}
		}
	}

	updateColor(param: string)
	{
		if (Type.isString(param))
		{
			this.color = param;
			this.#getCounter().setColor(Counter.Color[param]);
		}
	}

	activate(isEmitEvent: boolean = true)
	{
		this.isActive = true;
		this.getContainer().classList.add('--active');
		if(isEmitEvent)
		{
			EventEmitter.emit('BX.UI.CounterPanel.Item:activate', this);
		}
	}

	deactivate(isEmitEvent: boolean = true)
	{
		this.isActive = false;
		this.getContainer().classList.remove('--active');
		this.getContainer().classList.remove('--hover');
		if(isEmitEvent)
		{
			EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
		}
	}

	#getPanel()
	{
		return this.panel;
	}

	#getCounter()
	{
		if (!this.counter)
		{
			this.counter = new Counter({
				value: this.value ? this.value : 0,
				color: this.color ? Counter.Color[this.color] : Counter.Color.THEME,
				animation: true
			});
		}

		return this.counter;
	}

	#getValue()
	{
		if (!this.layout.value)
		{
			this.layout.value = Tag.render`
				<div class="ui-counter-panel__item-value">
					${this.#getCounter().getContainer()}
				</div>
			`;
		}

		return this.layout.value;
	}

	#getTitle()
	{
		if (!this.layout.title)
		{
			this.layout.title = Tag.render`
				<div class="ui-counter-panel__item-title">${this.title}</div>
			`;
		}

		return this.layout.title;
	}

	#getCross()
	{
		if (!this.layout.cross)
		{
			this.layout.cross = Tag.render`
				<div class="ui-counter-panel__item-cross">
					<div class="ui-counter-panel__item-cross--icon"></div>
				</div>
			`;
		}

		return this.layout.cross;
	}
	
	#setEvents()
	{
		if (this.eventsForActive)
		{
			const eventKeys = Object.keys(this.eventsForActive);

			for (let i = 0; i < eventKeys.length; i++)
			{
				let event = eventKeys[i];
				this.getContainer().addEventListener(event, () => {
					if (this.isActive)
					{
						this.eventsForActive[event]();
					}
				})
			}
		}

		if (this.eventsForUnActive)
		{
			const eventKeys = Object.keys(this.eventsForUnActive);

			for (let i = 0; i < eventKeys.length; i++)
			{
				let event = eventKeys[i];
				this.getContainer().addEventListener(event, () => {
					if (!this.isActive)
					{
						this.eventsForUnActive[event]();
					}
				})
			}
		}
	}

	getContainer()
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-counter-panel__item">
					${this.#getValue()}
					${this.title ? this.#getTitle() : ''}
					${this.#getCross()}
				</div>
			`;

			this.#setEvents();

			this.layout.container.addEventListener('mouseenter', () => {
				if (!this.isActive)
				{
					this.layout.container.classList.add('--hover');
				}
			});

			this.layout.container.addEventListener('mouseleave', () => {
				if (!this.isActive)
				{
					this.layout.container.classList.remove('--hover');
				}
			});

			this.layout.container.addEventListener('click', () => {
				this.isActive
					? this.deactivate()
					: this.activate();
			});
		}

		return this.layout.container;
	}
}
