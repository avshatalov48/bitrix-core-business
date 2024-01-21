import {Dom, Text, Type, Tag, Cache, Event} from 'main.core';
import {BaseForm} from 'landing.ui.form.baseform';
import 'ui.fonts.opensans';
import './css/style.css';

const depthKey = Symbol('depth');
const onHeaderClick = Symbol('onHeaderClick');
const onTextChange = Symbol('onTextChange');

/**
 * @memberOf BX.Landing.UI.Form
 */
export class MenuItemForm extends BaseForm
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Form.MenuItemForm');

		this.cache = new Cache.MemoryCache();
		this[onHeaderClick] = this[onHeaderClick].bind(this);
		this[onTextChange] = this[onTextChange].bind(this);
		this.onRemoveButtonClick = this.onRemoveButtonClick.bind(this);

		Dom.addClass(this.layout, 'landing-ui-form-menuitem');
		Dom.append(this.getHeaderLeftLayout(), this.header);
		Dom.append(this.getHeaderRightLayout(), this.header);

		this.setDepth(options.depth);

		const [firstField: BX.Landing.UI.Field.Link] = this.fields;
		if (firstField)
		{
			const {text} = firstField.getValue();
			this.setTitle(text);

			Event.bind(firstField.input.input, 'input', this[onTextChange]);
		}

		Event.bind(this.getHeader(), 'click', this[onHeaderClick]);
	}

	[onHeaderClick](event: MouseEvent)
	{
		event.preventDefault();

		if (this.isFormShown())
		{
			this.hideForm();
		}
		else
		{
			this.showForm();
		}
	}

	[onTextChange]()
	{
		const [firstField: BX.Landing.UI.Field.Link] = this.fields;
		if (firstField)
		{
			const {text} = firstField.getValue();
			this.setTitle(text);
		}
	}

	onRemoveButtonClick()
	{
		this.emit('remove', {form: this});
		Dom.remove(this.layout);
	}

	showForm()
	{
		Dom.addClass(this.layout, 'landing-ui-form-menuitem-open');
		Dom.style(this.body, 'display', 'block');
	}

	hideForm()
	{
		Dom.removeClass(this.layout, 'landing-ui-form-menuitem-open');
		Dom.style(this.body, 'display', null);
	}

	isFormShown(): boolean
	{
		return this.layout.classList.contains('landing-ui-form-menuitem-open');
	}

	getDragButton(): HTMLDivElement
	{
		return this.cache.remember('dragButton', () => {
			return Tag.render`
				<div class="landing-ui-form-header-drag-button landing-ui-drag"></div>
			`;
		});
	}

	getTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('titleLayout', () => {
			return Tag.render`
				<div class="landing-ui-form-header-title">${Text.encode(this.title)}</div>
			`;
		});
	}

	getHeaderLeftLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLeftLayout', () => {
			return Tag.render`
				<div class="landing-ui-form-header-left">
					${this.getDragButton()}
					${this.getTitleLayout()}
				</div>
			`;
		});
	}

	getRemoveButton(): HTMLSpanElement
	{
		return this.cache.remember('removeButton', () => {
			const button = Tag.render`<div class="landing-ui-form-header-remove-button"></div>`;
			Event.bind(button, 'click', this.onRemoveButtonClick);
			return button;
		});
	}

	getHeaderRightLayout(): HTMLDivElement
	{
		return this.cache.remember('headerRightLayout', () => {
			return Tag.render`
				<div class="landing-ui-form-header-right">
					${this.getRemoveButton()}
				</div>
			`;
		});
	}

	setTitle(title: string)
	{
		if (Type.isString(title) || Type.isNumber(title))
		{
			this.title = title;
			this.getTitleLayout().innerText = Text.decode(title);
		}
	}

	setDepth(depth: number)
	{
		const offset = 20;
		this[depthKey] = Text.toNumber(depth);
		Dom.style(this.layout, 'margin-left', `${depth * offset}px`);
		Dom.attr(this.layout, 'data-depth', depth);
	}

	getDepth(): number
	{
		return Text.toNumber(Dom.attr(this.layout, 'data-depth'));
	}

	serialize()
	{
		const [firstField: BX.Landing.UI.Field.Link] = this.fields;
		return firstField.getValue();
	}
}