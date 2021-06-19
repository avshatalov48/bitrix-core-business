import {Type, Reflection, Event, Dom} from 'main.core';

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
		this.cursorMode = options.cursorMode || false;
		this.targetEvent = options.targetEvent || null;
		this.buttons = options.buttons || [];

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

	getTargetPos()
	{
		if (Type.isDomNode(this.target))
		{
			return Dom.getPosition(this.target);
		}
	}

	getId()
	{
		return this.id;
	}

	getButtons()
	{
		return this.buttons;
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

	getCursorMode()
	{
		return this.cursorMode;
	}

	getTargetEvent()
	{
		return this.targetEvent;
	}

	static getFullEventName(shortName)
	{
		return "Step:" + shortName;
	}

	setTarget(target)
	{
		this.target = target;
	}

	initTargetEvent()
	{
		if(Type.isFunction(this.targetEvent))
		{
			this.targetEvent();
			return;
		}

		this.getTarget().dispatchEvent(new MouseEvent(this.targetEvent))
	}
}
