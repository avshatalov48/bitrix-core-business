import {EventEmitter} from 'main.core.events';
import {Cache, Tag} from 'main.core';
import {IconButton} from 'landing.ui.component.iconbutton';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

type FieldElementOptions = {
	id: string,
	title: string,
	removable?: boolean,
	draggable?: boolean,
	actionsLabel?: string,
	actionsList?: Array<{name: string, value: any}>,
	actionsValue?: any,
	onRemove?: () => void,
	// eslint-disable-next-line no-use-before-define
	color?: $Values<typeof FieldElement.Colors>
};

const defaultOptions = {
	removable: true,
	draggable: false,
	// eslint-disable-next-line no-use-before-define
	color: 'blue',
};

export class FieldElement extends EventEmitter
{
	static Colors = {
		blue: 'blue',
		green: 'green',
		red: 'red',
	};

	constructor(options: FieldElementOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.RuleField.FieldElement');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.options = {...defaultOptions, ...options};
		this.cache = new Cache.MemoryCache();
	}

	getDragButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('dragButton', () => {
			const button = new IconButton({
				type: IconButton.Types.drag,
				style: {
					width: '20px',
				},
			});

			return button.getLayout();
		});
	}

	getActionsDropdown(): BX.Landing.UI.Field.DropdownInline
	{
		return this.cache.remember('actionsDropdown', () => {
			const field = new window.top.BX.Landing.UI.Field.DropdownInline({
				title: this.options.actionsLabel,
				items: this.options.actionsList,
				content: this.options.actionsValue,
			});

			field.subscribe('onChange', () => {
				this.emit('onChange');
			});

			return field;
		});
	}

	getActionsLayout(): HTMLDivElement
	{
		return this.cache.remember('actionsLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-element-text-action">
					${this.getActionsDropdown().getLayout()}
				</div>
			`;
		});
	}

	getTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('titleLayout', () => {
			return Tag.render`<div class="landing-ui-field-element-text-title">${this.options.title}</div>`;
		});
	}

	getRemoveButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('removeButton', () => {
			const button = new IconButton({
				type: IconButton.Types.remove,
				onClick: () => this.emit('onRemove'),
				iconSize: '9px',
				style: {
					width: '20px',
					marginLeft: 'auto',
				},
			});

			return button.getLayout();
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-element-${this.options.color}">
					${this.options.draggable ? this.getDragButtonLayout() : ''}
					<div class="landing-ui-field-element-text">
						${this.options.actionsLabel ? this.getActionsLayout() : ''}
						${this.getTitleLayout()}
					</div>
					${this.options.removable ? this.getRemoveButtonLayout() : ''}
				</div>
			`;
		});
	}
}