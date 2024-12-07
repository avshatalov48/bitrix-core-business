import { BaseField } from './base-field';
import { Dom, Tag, Type } from 'main.core';

export class ItemPicker extends BaseField
{
	#items: Array = [];
	#selectNode: HTMLElement;
	#isMulti: boolean;
	#current;

	constructor(params)
	{
		super(params);
		this.#items = params.items;
		this.#isMulti = params.isMulti === true;
		this.#current = params.current;
		if (
			this.#isMulti
		&& this.getName().substring(this.getName().length - 2) !== '[]'
		)
		{
			this.setName(this.getName() + '[]');
		}

		this.#selectNode = this.#buildSelector();
	}

	prefixId(): string
	{
		return 'item_picker_';
	}

	renderContentField(): HTMLElement
	{
		return Tag.render`
		<div class="ui-section__picker-wrapper" id="${this.getId()}">
			<div class="ui-section__field-label">${this.getLabel()}</div>
			${this.#buildItems()}
			${this.renderErrors()}
			${this.getInputNode()}
		</div>
		`
	}

	getInputNode(): HTMLElement
	{
		return this.#selectNode;
	}

	onClickHandler(event): void
	{
		Dom.toggleClass(event.target, 'ui-section__selected');
		if (!Dom.hasClass(event.target, 'ui-section__selected') && this.#isMulti)
		{
			this.unSelect(event.target);
		}
		else
		{
			this.select(event.target);
		}
	}

	createItem(text: string, value: string, isSelected: boolean = false): HTMLElement
	{
		return Dom.create( 'div',
			{
				text: text,
				props: {
					className: "ui-section__item " + (isSelected ? 'ui-section__selected' : ''),
				},
				dataset: {
					value: value
				},
				events: {
					click: this.onClickHandler.bind(this)
				}
			}
		);
	}

	#buildSelector(): HTMLElement
	{
		let options = [];
		for (let {value, name, selected} of this.#items)
		{
			let selectedAttr = '';
			if (selected === true)
			{
				selectedAttr = 'selected';
			}
			options.push(Tag.render`<option ${selectedAttr} value="${value}">${name}</option>`);
		}

		return Dom.create('select', {
			attrs: {
				multiple: this.#isMulti ? 'on' : '',
				name: this.getName(),
				disabled: !this.isEnable() ? 'disable' : ''
			},
			style: {
				display: 'none'
			},
			children: options,
		});
	}

	#buildItems(): HTMLElement
	{
		let collectionNode = Tag.render`<div class="ui-section__item-collection"></div>`;
		for (let {value, name, selected} of this.#items)
		{
			Dom.append(this.createItem(name, value, selected), collectionNode);
		}

		return collectionNode;
	}

	select(node: HTMLElement, fireEvent = true)
	{
		if (!this.#isMulti)
		{
			this.unSelectAll();
		}
		const value = node.dataset['value'];
		let optNode = this.#selectNode.querySelector('option[value="'+value+'"]')
		if (Type.isDomNode(optNode))
		{
			Dom.addClass(node, 'ui-section__selected');
			optNode.selected = true;
			if (fireEvent)
			{
				this.fireEvent();
			}
		}
	}

	unSelect(node, fireEvent = true)
	{
		const value = node.dataset['value'];
		let optNode = this.#selectNode.querySelector('option[value="'+value+'"]')
		if (Type.isDomNode(optNode))
		{
			Dom.removeClass(node, 'ui-section__selected');
			optNode.selected = false;
			if (fireEvent)
			{
				this.fireEvent();
			}
		}
	}

	unSelectAll(fireEvent = false)
	{
		if (Type.isDomNode(this.field))
		{
			let items = this.field.querySelectorAll('.ui-section__item.ui-section__selected')
			items.forEach(item => {
				Dom.removeClass(item, 'ui-section__selected');
			});
		}

		let optsNodes = this.#selectNode.querySelectorAll('option');
		optsNodes.forEach((node) => {
			if (Type.isDomNode(node))
			{
				node.selected = false;
			}
		});
		if (fireEvent)
		{
			this.fireEvent()
		}
	}

	getNodesByValue(data)
	{
		let query;
		if (Type.isArray(data))
		{
			let queryList = data.map((value) => {
				return '.ui-section__item[data-value="'+value+'"]';
			});
			query = queryList.join(', ');
		}
		else
		{
			query = '.ui-section__item[data-value="'+data+'"]';
		}

		return this.field.querySelectorAll(query);
	}

	fireEvent()
	{
		this.#selectNode.dispatchEvent(new Event('change'));
		this.#selectNode.form.dispatchEvent(new Event('change'));
	}
}
