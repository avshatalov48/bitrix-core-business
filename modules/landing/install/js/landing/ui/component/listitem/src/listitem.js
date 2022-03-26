import {Cache, Dom, Runtime, Tag, Text, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Loc} from 'landing.loc';
import {BaseForm} from 'landing.ui.form.baseform';
import {IconButton} from 'landing.ui.component.iconbutton';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

export type ListItemOptions = {
	id: string,
	type: string,
	title?: string,
	description?: string,
	draggable?: boolean,
	editable?: boolean,
	removable?: boolean,
	actions?: Array<IconButton>,
	onEdit?: (BaseEvent) => {},
	onRemove?: (BaseEvent) => {},
	onFormChange?: (BaseEvent) => {},
	appendTo?: HTMLElement,
	prependTo?: HTMLElement,
	form?: BaseForm,
	sourceOptions?: {[key: string]: any},
	isSeparator?: boolean,
	error?: boolean,
};

export class ListItem extends EventEmitter
{
	constructor(options: ListItemOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Component.ListItem');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		this.onEditButtonClick = this.onEditButtonClick.bind(this);
		this.onRemoveButtonClick = this.onRemoveButtonClick.bind(this);
		this.onFormChange = this.onFormChange.bind(this);

		this.options = {...options};
		this.cache = new Cache.MemoryCache();

		if (Type.isDomNode(this.options.appendTo))
		{
			this.appendTo(this.options.appendTo);
		}
		else if (Type.isDomNode(this.options.prependTo))
		{
			this.prependTo(this.options.prependTo);
		}

		if (Type.isArrayFilled(this.options.actions))
		{
			this.setActionsButtons([...this.options.actions]);
		}

		if (this.options.error)
		{
			Dom.addClass(this.getLayout(), 'landing-ui-error');
		}
	}

	setActionsButtons(actionsButtons: Array<IconButton>)
	{
		this.cache.set('actionsButtons', actionsButtons);
	}

	getActionsButtons(): Array<IconButton>
	{
		return this.cache.get('actionsButtons', []);
	}

	appendTo(target: HTMLElement)
	{
		Dom.append(this.getLayout(), target);
	}

	prependTo(target: HTMLElement)
	{
		Dom.prepend(this.getLayout(), target);
	}

	getDragButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('dragButtonLayout', () => {
			const button = new IconButton({
				type: IconButton.Types.drag,
				title: Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_DRAG_TITLE'),
				style: {
					position: 'absolute',
					left: '1px',
					width: '8px',
				},
			});

			return button.getLayout();
		});
	}

	getTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('titleLayout', () => {
			return Tag.render`
				<div class="landing-ui-component-list-item-text-title">${Text.encode(this.options.title)}</div>
			`;
		});
	}

	setTitle(title: string)
	{
		this.getTitleLayout().textContent = title;
	}

	getTitle(): string
	{
		return this.getTitleLayout().innerText;
	}

	getDescriptionLayout(): HTMLDivElement
	{
		return this.cache.remember('descriptionLayout', () => {
			return Tag.render`
				<div class="landing-ui-component-list-item-text-description">${Text.encode(this.options.description)}</div>
			`;
		});
	}

	setDescription(description: string)
	{
		this.getDescriptionLayout().textContent = description;
	}

	getDescription(): string
	{
		return this.getDescriptionLayout().innerText;
	}

	getEditButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('editButtonLayout', () => {
			const button = new IconButton({
				type: IconButton.Types.edit,
				onClick: this.onEditButtonClick,
				title: Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_EDIT_TITLE'),
			});

			return button.getLayout();
		});
	}

	onEditButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		const editEvent = new BaseEvent();
		this.emit('onEdit', editEvent);

		if (!editEvent.isDefaultPrevented())
		{
			if (!this.isOpened())
			{
				this.open();
			}
			else
			{
				this.close();
			}
		}
	}

	getRemoveButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('removeButtonLayout', () => {
			const button = new IconButton({
				type: IconButton.Types.remove,
				onClick: this.onRemoveButtonClick,
				title: Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_REMOVE_TITLE'),
			});

			return button.getLayout();
		});
	}

	onRemoveButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		Dom.remove(this.getLayout());
		this.emit('onRemove');
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="landing-ui-component-list-item-header">
					${this.options.draggable ? this.getDragButtonLayout() : ''}
					<div class="landing-ui-component-list-item-text">
						${this.getTitleLayout()}
						${this.getDescriptionLayout()}
					</div>
					<div class="landing-ui-component-list-item-actions">
						<div class="landing-ui-component-list-item-actions-custom">
							${this.getActionsButtons().map((button) => button.getLayout())}
						</div>
						${this.options.editable ? this.getEditButtonLayout() : ''}
						${this.options.removable ? this.getRemoveButtonLayout() : ''}
					</div>
				</div>
			`;
		});
	}

	getBodyLayout(): HTMLDivElement
	{
		return this.cache.remember('bodyLayout', () => {
			return Tag.render`
				<div class="landing-ui-component-list-item-body"></div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div 
					class="landing-ui-component-list-item" 
					data-id="${this.options.id}"
					data-type="${this.options.type}"
					data-style="${this.options.isSeparator ? 'separator' : 'item'}"
				>
					${this.getHeaderLayout()}
					${this.getBodyLayout()}
				</div>
			`;
		});
	}

	open()
	{
		Dom.addClass(this.getLayout(), 'landing-ui-component-list-item-opened');

		if (!Type.isStringFilled(this.getBodyLayout().innerHTML))
		{
			if (this.options.form)
			{
				Dom.append(this.options.form.getLayout(), this.getBodyLayout());
				this.options.form.subscribe('onChange', this.onFormChange);
			}
		}
	}

	isOpened(): boolean
	{
		return Dom.hasClass(this.getLayout(), 'landing-ui-component-list-item-opened');
	}

	close()
	{
		Dom.removeClass(this.getLayout(), 'landing-ui-component-list-item-opened');
	}

	onFormChange()
	{
		this.emit('onFormChange');
	}

	setId(id: string | number)
	{
		Dom.attr(this.getLayout(), 'data-id', id);
	}

	getId(): string | number
	{
		return Dom.attr(this.getLayout(), 'data-id');
	}

	getValue()
	{
		const value = {
			name: this.options.id,
		};

		if (Type.isStringFilled(this.options.type))
		{
			value.type = this.options.type;
		}

		if (this.options.form)
		{
			const formValue = this.options.form.serialize();
			Object.assign(value, formValue);
		}

		if (this.options.content)
		{
			value.content = this.options.content;
		}

		if (this.options.sourceOptions)
		{
			const sourceOptions = Runtime.clone(this.options.sourceOptions);

			Object.entries(sourceOptions).forEach(([key, propValue]) => {
				if (Type.isArray(propValue) && Type.isArray(value[key]))
				{
					delete sourceOptions[key];
				}
			});

			return Runtime.merge(sourceOptions, value);
		}

		return value;
	}
}