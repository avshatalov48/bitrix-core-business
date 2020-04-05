import {Type, Reflection, Event} from 'main.core';

export class Step extends Event.EventEmitter
{
	constructor(options)
	{
		super(options);
		this.target = null;
		if (
			Type.isString(options.target) && options.target !== '' ||
			Type.isFunction(options.target) ||
			Type.isDomNode(options.target)
		)
		{
			this.target = options.target;
		}

		this.id = options.id || null;
		this.text = options.text;
		this.areaPadding = options.areaPadding;
		this.link = options.link || "";
		this.rounded = options.rounded || false;
		this.title = options.title || null;
		this.article = options.article || null;
		this.position = options.position || null;

		const events = Type.isPlainObject(options.events) ? options.events : {};

		for (let eventName in events)
		{
			const callback = Type.isFunction(events[eventName]) ? events[eventName] : Reflection.getClass(events[eventName]);
			if (callback)
			{
				this.subscribe(this.constructor.getFullEventName(eventName), () => {
					callback();
				});
			}
		}
	}

	getTarget()
	{
		if (Type.isString(this.target) && this.target !== '')
		{
			return document.querySelector(this.target);
		}

		if (Type.isFunction(this.target))
		{
			return this.target();
		}

		return this.target;
	}

	getId()
	{
		return this.id;
	}

	getAreaPadding()
	{
		return this.areaPadding;
	}

	getRounded()
	{
		return this.rounded;
	}

	getText()
	{
		return this.text;
	}

	getLink()
	{
		return this.link;
	}

	getTitle()
	{
		return this.title;
	}

	getPosition()
	{
		return this.position;
	}

	getArticle()
	{
		return this.article;
	}

	static getFullEventName(shortName)
	{
		return "Step:" + shortName;
	}

	setTarget(target)
	{
		this.target = target;
	}
}