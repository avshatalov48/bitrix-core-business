import {Dom, Type, Tag} from 'main.core';
import {Loc} from 'landing.loc';
import {Env} from 'landing.env';
import {Main} from 'landing.main';
import {BaseForm} from 'landing.ui.form.baseform';
import {MenuItemForm} from 'landing.ui.form.menuitemform';
import {Draggable} from 'ui.draganddrop.draggable';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class MenuForm extends BaseForm
{
	constructor(options = {})
	{
		super(options);
		Dom.addClass(this.layout, 'landing-ui-form-menu');

		this.forms = new BX.Landing.UI.Collection.FormCollection();

		if (Type.isArray(options.forms))
		{
			options.forms.forEach((form) => {
				this.addForm(form);
			});
		}

		this.draggable = new Draggable({
			container: this.getBody(),
			draggable: '.landing-ui-form-menuitem',
			dragElement: '.landing-ui-form-header-drag-button',
			type: Draggable.DROP_PREVIEW,
			depth: {
				margin: 20,
			},
		});

		this.onMenuItemRemove = this.onMenuItemRemove.bind(this);

		Dom.append(this.getAddItemLayout(), this.layout);
	}

	addForm(form: BaseForm)
	{
		if (!this.forms.contains(form))
		{
			this.forms.add(form);
			Dom.append(form.layout, this.body);
			form.subscribe('remove', this.onMenuItemRemove.bind(this));

			if (this.draggable)
			{
				this.draggable.invalidateCache();
			}
		}
	}

	onMenuItemRemove(event)
	{
		const children = this.draggable.getChildren(event.data.form.layout);

		children.forEach((element) => {
			Dom.remove(element);
		});

		this.forms.remove(event.data.form);
		this.draggable.invalidateCache();
	}

	serialize()
	{
		const draggableElements = this.draggable.getDraggableElements();
		const getChildren = (parent) => {
			const parentDepth = this.draggable.getElementDepth(parent);
			const allChildren = this.draggable.getChildren(parent);

			return allChildren.reduce((acc, current) => {
				const currentDepth = this.draggable.getElementDepth(current);

				if (currentDepth === (parentDepth + 1))
				{
					const form = this.forms.getByLayout(current);
					acc.push({
						...form.serialize(),
						children: getChildren(current),
					});
				}

				return acc;
			}, []);
		};

		return draggableElements.reduce((acc, element) => {
			if (this.draggable.getElementDepth(element) === 0)
			{
				const form = this.forms.getByLayout(element);
				acc.push({
					...form.serialize(),
					children: getChildren(element),
				});
			}

			return acc;
		}, []);
	}

	onAddButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		const pageType = Env.getInstance().getType();
		const content = {
			text: Loc.getMessage('LANDING_NEW_PAGE_LABEL'),
			target: '_blank',
			href: ['KNOWLEDGE', 'GROUP'].includes(pageType) ? '#landing0' : '',
		};

		const allowedTypes = [
			BX.Landing.UI.Field.LinkURL.TYPE_BLOCK,
			BX.Landing.UI.Field.LinkURL.TYPE_PAGE,
			BX.Landing.UI.Field.LinkURL.TYPE_CRM_FORM,
			BX.Landing.UI.Field.LinkURL.TYPE_CRM_PHONE,
		];

		if (pageType === 'STORE')
		{
			allowedTypes.push(
				BX.Landing.UI.Field.LinkURL.TYPE_CATALOG,
			);
		}

		const field = new BX.Landing.UI.Field.Link({
			content,
			options: {
				siteId: Env.getInstance().getSiteId(),
				landingId: Main.getInstance().id,
				filter: {
					'=TYPE': pageType,
				},
			},
			allowedTypes,
		});

		const form = new MenuItemForm({
			fields: [field],
		});

		form.showForm();

		this.addForm(form);

		setTimeout(() => {
			field.input.enableEdit();

			const {input} = field.input;
			const [textNode] = input.childNodes;

			if (textNode)
			{
				const range = document.createRange();
				const sel = window.getSelection();

				range.setStart(textNode, input.innerText.length);
				range.collapse(true);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		});
	}

	getAddButton(): HTMLButtonElement
	{
		return this.cache.remember('addButton', () => {
			return Tag.render`
				<button 
					class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-add ui-btn-round landing-ui-form-menu-add-button"
					onclick="${this.onAddButtonClick.bind(this)}"
					>
					${Loc.getMessage('LANDING_ADD_MENU_ITEM')}
				</button>
			`;
		});
	}

	getAddItemLayout(): HTMLElement
	{
		return this.cache.remember('addItemLayout', () => {
			return Tag.render`
				<div class="landing-ui-form-menu-add">
					${this.getAddButton()}
				</div>
			`;
		});
	}
}