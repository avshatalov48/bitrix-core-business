import {ErrorCollection} from "./error-collection";
import {BaseSettingsVisitor} from './visitors/base-settings-visitor';
import {EventEmitter} from 'main.core.events';
import {Dom, Type} from 'main.core';

export class BaseSettingsElement extends EventEmitter
{
	#errorCollection: ErrorCollection
	#parentElement: ?BaseSettingsElement;
	#childrenElements: [BaseSettingsElement];

	constructor(params)
	{
		params = Type.isNil(params) ? {} : params;
		super();
		this.#parentElement = null;
		this.setEventNamespace('BX.UI.FormElement.Field');
		if (!Type.isNil(params.parent))
		{
			this.setParentElement(params.parent);
		}
		this.#childrenElements = [];
		if (!Type.isNil(params.children))
		{
			this.setChildrenElements(params.children);
		}
		this.addChild(params.child);
		this.#errorCollection = new ErrorCollection();
	}

	getErrorCollection(): ErrorCollection
	{
		return this.#errorCollection;
	}

	setErrorCollection(errorCollection: ErrorCollection): void
	{
		this.#errorCollection.merge(errorCollection);

		this.#parentElement?.setErrorCollection(this.#errorCollection);
	}

	getParentElement(): ?BaseSettingsElement
	{
		return this.#parentElement;
	}

	getChildrenElements(): [BaseSettingsElement]
	{
		return this.#childrenElements;
	}

	setParentElement(parent: BaseSettingsElement): BaseSettingsElement
	{
		if (parent instanceof BaseSettingsElement)
		{
			this.#parentElement = parent;
			this.#parentElement.addChild(this);
		}

		return this;
	}

	unsetParentElement()
	{
		this.#parentElement = null;
	}

	setChildrenElements(value: Array<BaseSettingsElement>)
	{
		for (let element of value)
		{
			this.addChild(element);
		}
	}

	addChild(child: BaseSettingsElement)
	{
		if (child instanceof BaseSettingsElement)
		{
			if (!this.#childrenElements.includes(child))
			{
				this.#childrenElements.push(child);
			}
			if (Type.isNil(child.getParentElement()))
			{
				child.setParentElement(this);
			}
		}
	}

	removeChild(child: BaseSettingsElement)
	{
		if (child instanceof BaseSettingsElement)
		{
			this.#childrenElements = this.#childrenElements
				.filter((element) => element !== child)
			;
			child.unsetParentElement();
		}
	}

	//#region "Renderable" Interface

	render(): HTMLElement
	{
		return '';
	}

	renderErrors(): HTMLElement
	{
		return '';
	}

	accept(visitor: BaseSettingsVisitor)
	{
		visitor.visitSettingsElement(this);
	}

	highlight(): boolean
	{
		return false;
	}

	highlightElement(element: HTMLElement): void
	{
		Dom.addClass(element, '--founded-item');
		setTimeout(() => {
			Dom.removeClass(element, '--founded-item');
			Dom.addClass(element, '--after-founded-item');
			setTimeout(() => {
				Dom.removeClass(element, '--after-founded-item');
			}, 5000);
		}, 0);
	}

	//#endregion "Renderable" Interface
}
