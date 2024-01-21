import {Dom, Tag, Type, Text} from "main.core";
import {TagSelector} from "ui.entity-selector";
import {BaseField} from "./base-field";

export class UserSelector extends BaseField
{
	#entitySelector: TagSelector;
	#defaultValues: Array = [];
	#inputContainer: HTMLElement;
	#encode: ?function = null;
	#decode: ?function = null;
	#defaultTags: Array = [];
	#className: string = '';
	#enableAll: boolean;
	#enableDepartments: boolean;

	constructor(params)
	{
		super(params);
		this.#encode = Type.isFunction(params.encodeValue) ? params.encodeValue : null;
		this.#decode = Type.isFunction(params.decodeValue) ? params.decodeValue : null;
		this.#inputContainer = Tag.render`<div class="ui-section__input-container"></div>`;
		this.#className = params.className;
		this.#enableAll = params.enableAll !== false;
		this.#enableDepartments = params.enableDepartments === true;

		this.#initInput(params.values);

		const entities = [
			{
				id: 'user',
				options: {
					intranetUsersOnly: true,
				},
			},
			{
				id: 'department',
				options: {
					selectMode: this.#enableDepartments ? 'usersAndDepartments' : 'usersOnly',
					allowFlatDepartments: this.#enableDepartments,
					allowSelectRootDepartment: this.#enableDepartments,
				}
			}
		];

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

		this.#entitySelector = new TagSelector({
			id: this.getId(),
			textBoxAutoHide: true,
			textBoxWidth: 350,
			maxHeight: 99,
			dialogOptions: {
				id: this.getId(),
				preselectedItems: this.#defaultValues,
				events: {
					'Item:onSelect': this.onChangeSelector.bind(this),
					'Item:onDeselect': this.onChangeSelector.bind(this)
				},
				entities: entities,
			}
		});
		this.#defaultTags = this.#entitySelector.getTags();

		if (!this.isEnable())
		{
			this.#entitySelector.hideAddButton()
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

	getInputNode(): HTMLElement
	{
		return this.#entitySelector.getContainer();
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
