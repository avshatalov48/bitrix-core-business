import {Dom, Text, Type} from 'main.core';
import {Loader} from 'main.loader';
import {EventEmitter} from 'main.core.events';

/**
 * @abstract
 * @mixes EventEmitter
 * @memberOf BX.UI.Timeline
 */
export class Editor
{
	id: string;
	isProgress = false;
	loader;

	constructor(params: {
		id: string,
	})
	{
		if(Type.isString(params.id) && params.id.length > 0)
		{
			this.id = params.id;
		}
		else
		{
			this.id = Text.getRandom();
		}
		this.layout = {};

		EventEmitter.makeObservable(this, 'BX.UI.Timeline.Editor');
	}

	getId(): string
	{
		return this.id;
	}

	getTitle(): string
	{
	}

	getContainer(): ?Element
	{
		return this.layout.container;
	}

	render(): Element
	{
		throw new Error('This method should be overridden');
	}

	clearLayout(isSkipContainer: boolean = false): Editor
	{
		const container = this.getContainer();
		Object.keys(this.layout).forEach((name: string) =>
		{
			const node = this.layout[name];
			if(!isSkipContainer || container !== node)
			{
				Dom.clean(node);
				delete this.layout[name];
			}
		});

		return this;
	}

	startProgress()
	{
		this.isProgress = true;
		this.getLoader().show();
	}

	stopProgress()
	{
		this.isProgress = false;
		if(this.getLoader().isShown())
		{
			this.getLoader().hide();
		}
	}

	getLoader(): Loader
	{
		if(!this.loader)
		{
			this.loader = new Loader({
				target: this.getContainer(),
			});
		}

		return this.loader;
	}

	isRendered(): boolean
	{
		return Type.isDomNode(this.getContainer());
	}
}