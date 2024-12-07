import { Dom, Tag, Type, Text } from 'main.core';
import { TagSelector } from 'ui.entity-selector';
import { BaseField } from './base-field';

export class UserSelector extends BaseField
{
	#entitySelector: TagSelector;
	#defaultValues: Array = [];
	#inputContainer: HTMLElement;
	#encode: ?function = null;
	#decode: ?function = null;
	#defaultTags: Array = [];
	#className: string = '';
	#enableUsers: boolean;
	#enableAll: boolean;
	#enableDepartments: boolean;

	constructor(params)
	{
		super(params);
		this.#encode = Type.isFunction(params.encodeValue) ? params.encodeValue : null;
		this.#decode = Type.isFunction(params.decodeValue) ? params.decodeValue : null;
		this.#inputContainer = Tag.render`<div class="ui-section__input-container"></div>`;
		this.#className = params.className;
		this.#enableUsers = params.enableUsers !== false;
		this.#enableAll = this.#enableUsers && params.enableAll !== false;
		this.#enableDepartments = params.enableDepartments === true;

		this.#initInput(params.values);

		const entities = [];

		if (this.#enableUsers)
		{
			entities.push(
				{
					id: 'user',
					options: {
						intranetUsersOnly: true,
					},
				},
			);
		}

		if (this.#enableUsers || this.#enableDepartments)
		{
			entities.push(
				{
					id: 'department',
					options: {
						selectMode: this.#getDepartamentsSelectMode(),
						allowFlatDepartments: this.#enableDepartments,
						allowSelectRootDepartment: this.#enableDepartments,
					},
				},
			);
		}

		if (this.#enableAll)
		{
			entities.push(
				{
					id: 'meta-user',
					options: {
						'all-users': this.#enableAll, // All users
					},
				},
			);
		}

		if (params.entities)
		{
			entities.push(...params.entities);
		}

		const multiple = params.multiple !== false;

		this.#entitySelector = new TagSelector({
			id: this.getId(),
			textBoxAutoHide: false,
			textBoxWidth: 350,
			maxHeight: 99,
			dialogOptions: {
				id: this.getId(),
				preselectedItems: this.#defaultValues,
				multiple: multiple,
				hideOnDeselect: !multiple,
				events: {
					'Item:onSelect': this.onChangeSelector.bind(this),
					'Item:onDeselect': this.onChangeSelector.bind(this),
				},
				entities: entities,
			},
			multiple: multiple,
		});
		this.#defaultTags = this.#entitySelector.getTags();

		if (!this.isEnable())
		{
			this.#entitySelector.hideAddButton();
			this.#entitySelector.getTextBox().readOnly = true;
			Dom.adjust(this.#entitySelector.getContainer(), {
				events: {
					click: (event) => {
						event.preventDefault();
						if (!Type.isNil(this.getHelpMessage()))
						{
							this.getHelpMessage().show();
						}
					},
				},
			});
		}
	}

	#getDepartamentsSelectMode(): string
	{
		if (this.#enableUsers && this.#enableDepartments)
		{
			return 'usersAndDepartments';
		}

		if (this.#enableUsers && !this.#enableDepartments)
		{
			return 'usersOnly';
		}

		return 'departmentsOnly';
	}

	getSelector(): TagSelector
	{
		return this.#entitySelector;
	}

	getInputNode(): HTMLElement
	{
		return this.#entitySelector.getContainer();
	}

	getErrorBox(): HTMLElement
	{
		return this.#entitySelector.getOuterContainer();
	}

	prefixId(): string
	{
		return 'user_selector_';
	}

	renderContentField(): HTMLElement
	{
		const content = Tag.render`
			<div id="${this.getId()}" class="ui-section__field-user_selector ${this.#className}">
				<div class="ui-section__field">
					<div class="ui-section__field-label">
						${this.getLabel()}
					</div>
				</div>
				${this.renderErrors()}
				<div class="ui-section__input-box">
					${this.#inputContainer}
				</div>
			</div>
		`;
		this.#entitySelector.renderTo(content.querySelector('.ui-section__field'));

		return content;
	}

	onChangeSelector(event): void
	{
		let selectedItems = event.target.getSelectedItems();
		Dom.clean(this.#inputContainer);
		if (Type.isArray(selectedItems))
		{
			selectedItems.forEach(item =>
			{
				let type = '';
				switch (item.entityId)
				{
					case 'meta-user':
						type = 'AU';
						break;

					case 'department':
						if (item.id.toString().split(':')[1] === 'F')
						{
							type = 'D';
						}
						else
						{
							type = 'DR';
						}
						break;

					case 'user':
						type = 'U';
						break;

					default:
						break;
				}

				if (type)
				{
					const value = Type.isFunction(this.#encode) ? this.#encode({id: item.id, type: type}) : item.id;
					if (value)
					{
						Dom.append(this.#createInputElement(value), this.#inputContainer);
					}
				}
			});
		}
		this.#triggerEventChange();
	}

	#createInputElement(value: string): HTMLElement
	{
		return Dom.create('input', {
			attrs: {
				name: this.getName(),
				value: Text.encode(value),
				type: 'text',

			},
			style: {
				display: 'none',
			},
		});
	}

	setValues(values): void
	{
		if (Type.isArray(values))
		{
			for (let userId of values)
			{
				const value = Type.isFunction(this.#decode) ? this.#decode(userId) : userId;

				let item = [];
				if (Type.isObject(value) && Type.isString(value.type) && Type.isString(value.id))
				{
					switch (value.type)
					{
						case 'AU':
							item = ['meta-user', 'all-users'];
							break;

						case 'DR':
							if (!this.#enableDepartments)
							{
								continue;
							}
							item = ['department', value.id];
							break;

						case 'D':
							if (!this.#enableDepartments)
							{
								continue;
							}
							item = ['department', value.id.toString() + ':F'];
							break;

						case 'U':
							item = ['user', value.id];
							break;

						default:
							continue;
					}
				}

				if (Type.isArrayFilled(value))
				{
					item = value;
				}

				this.#defaultValues.push(item);
			}
		}
		else
		{
			this.#defaultValues = [];
		}
	}

	#initInput(values)
	{
		if (Type.isArray(values))
		{
			for (let value of values)
			{
				let input = this.#createInputElement(value);
				Dom.append(input, this.#inputContainer);
			}
			this.setValues(values);
		}
	}

	#triggerEventChange()
	{
		let input = this.#inputContainer.firstChild;
		let form;
		if (Type.isNil(input))
		{
			input = this.#createInputElement('');
			Dom.append(input, this.#inputContainer);
			form = input.form;
			Dom.remove(input);
		}
		else
		{
			form = input.form;
		}
		form.dispatchEvent(new Event('change'));
	}
}
