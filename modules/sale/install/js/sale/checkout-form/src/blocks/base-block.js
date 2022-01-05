import {Cache, Dom, Event, Tag, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Form, {Stage} from '../form/form';
import Model from '../form/model';
import {BlockType} from './factory';

export class BaseBlock extends EventEmitter
{
	static VIEW_MODE: Mode = 'view';
	static EDIT_MODE: Mode = 'edit';

	form: Form = null;
	settings: BlockSetting = null;

	cache = new Cache.MemoryCache();
	mode: Mode = BaseBlock.EDIT_MODE;

	constructor(form: Form, settings: BlockSetting = {})
	{
		super();
		this.setEventNamespace('BX.Sale.CheckoutForm.Block');

		this.form = form;
		this.settings = settings;
	}

	getForm(): Form
	{
		return this.form;
	}

	getModel(): Model
	{
		return this.getForm().getModel();
	}

	getCache()
	{
		return this.cache;
	}

	getWrapper(): ?HTMLElement
	{
		return this.getCache().remember('wrapper', () => {
			let wrapper;

			if (this.hasSetting('wrapperId'))
			{
				wrapper = document.getElementById(this.getSetting('wrapperId'));
				if (!Type.isDomNode(wrapper))
				{
					throw new Error(`Can't find block wrapper with id '${this.getSetting('wrapperId')}'.`);
				}
			}
			else
			{
				wrapper = Tag.render`<div></div>`;
				this.getForm().getContainer().appendChild(wrapper);
			}

			return wrapper;
		});
	}

	hasSetting(name: string): boolean
	{
		return name in this.settings;
	}

	getSetting(name: string, defaultValue = null): mixed
	{
		return this.settings[name] || defaultValue;
	}

	getMode(): Mode
	{
		return this.mode;
	}

	setMode(mode: Mode): void
	{
		this.mode = mode;
	}

	getType(): string
	{
		return this.getSetting('type');
	}

	getStage(): number
	{
		return this.getSetting('stage', Stage.INITIAL);
	}

	isSuccess(): boolean
	{
		return this.getSetting('type') === BlockType.SUCCESS;
	}

	refreshLayout(forceLayout: boolean = false): void
	{
		let mode;

		const formStage = this.getForm().getStage();
		const blockStage = this.getStage();

		if (Type.isPlainObject(blockStage))
		{
			const {view: viewStage, edit: editStage, hide: hideStage} = blockStage;
			let currentStage = 0;

			while (currentStage <= formStage)
			{
				if (currentStage === hideStage)
				{
					mode = undefined;
				}
				else if (currentStage === editStage)
				{
					mode = BaseBlock.EDIT_MODE;
				}
				else if (currentStage === viewStage)
				{
					mode = BaseBlock.VIEW_MODE;
				}

				currentStage++;
			}
		}
		else if (Type.isNumber(blockStage))
		{
			if (blockStage <= formStage)
			{
				mode = BaseBlock.EDIT_MODE;
			}
		}

		this.clearLayout();

		if (mode || forceLayout)
		{
			if (mode)
			{
				this.setMode(mode);
			}

			this.layout();
		}
	}

	clearLayout(): void
	{
		if (this.getCache().has('wrapper'))
		{
			const wrapper = this.getWrapper();

			if (Type.isDomNode(wrapper))
			{
				Event.unbindAll(wrapper);

				if (this.hasSetting('wrapperId'))
				{
					Dom.clean(wrapper);
				}
				else
				{
					Dom.remove(wrapper);
				}

				this.getCache().delete('wrapper');
			}
		}
	}

	layout(): void
	{
		throw new Error('Not implemented method.');
	}
}

export type BlockSetting = {
	type: string,
	options: { [key: string]: any }
}

export type Mode = 'view' | 'edit'