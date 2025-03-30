import { Type, Tag, Dom, Event } from 'main.core';
import { Actions, Icon } from 'ui.icon-set.api.core';
import { Dialog } from 'ui.entity-selector';
import 'ui.icon-set.actions';
import './css/style.css';

const settingEntityId = 'setting';

export class SettingSelector
{
	#container: HTMLElement | null = null;
	#settingButtonTextNode: HTMLElement | null = null;
	#settingButton: HTMLElement | null = null;
	#hiddenInput: HTMLInputElement | null = null;
	#inputName: string;
	#settingsMap = new Map();
	#selectedOptionKey: '';
	#dialogOptions: {};

	// At least one item from the list must be selected.
	#forbidOptionDeselect = true;

	constructor(options: Options)
	{
		const {
			settingsMap = {},
			selectedOptionKey,
			inputName,
			dialogOptions = {},
		} = options;

		Object.entries(settingsMap).forEach(([key, value]) => {
			this.#settingsMap.set(key, value);
		});

		this.#dialogOptions = dialogOptions;
		this.#inputName = inputName;
		this.#selectedOptionKey = selectedOptionKey;
		this.#container = this.#renderContainer();
		this.#createSelector();
	}

	getSelected(): string
	{
		return this.#selectedOptionKey;
	}

	#createSelector(): void
	{
		const items = [];

		this.#settingsMap.forEach((value, key) => {
			items.push({
				id: key,
				title: value,
				entityId: settingEntityId,
				selected: (key === this.getSelected()),
				tabs: 'recents',
			});
		});

		this.settingDialog = new Dialog({
			items,
			targetNode: this.#settingButton,
			width: 170,
			height: (37 * items.length) + 15,
			multiple: false,
			enableSearch: false,
			dropdownMode: true,
			showAvatars: false,
			compactView: true,
			events: {
				'Item:onBeforeDeselect': (event) => {
					if (this.#forbidOptionDeselect)
					{
						event.preventDefault();
					}
				},
				'Item:onBeforeSelect': () => {
					this.#forbidOptionDeselect = false;
				},
				'Item:onSelect': (event) => {
					const { item: selectedItem } = event.getData();
					this.select(selectedItem.getId());
				},
				'Item:onDeselect': () => {
					this.#forbidOptionDeselect = true;
				},
			},
			...this.#dialogOptions,
		});

		Event.bind(this.#settingButton, 'click', () => {
			this.settingDialog.show();
		});
	}

	select(key)
	{
		this.#selectedOptionKey = key;
		this.#settingButtonTextNode.textContent = this.#settingsMap.get(this.getSelected());
		if (this.#hiddenInput)
		{
			this.#hiddenInput.setAttribute('value', key);
		}
	}

	#renderContainer(): HTMLElement
	{
		const icon = new Icon({
			icon: Actions.CHEVRON_DOWN,
			color: getComputedStyle(document.body).getPropertyValue('--ui-color-base-80'),
			size: 16,
		});

		this.icon = icon.render();

		let selectedOptionText = this.#settingsMap.get(this.#selectedOptionKey);

		if (selectedOptionText === undefined)
		{
			selectedOptionText = '';
		}

		this.#settingButtonTextNode = Tag.render`<div class="setting-selector-button-text"></div>`;

		this.#settingButtonTextNode.setAttribute('title', selectedOptionText);
		this.#settingButtonTextNode.textContent = selectedOptionText;

		this.#settingButton = Tag.render`
			<div class="setting-selector-button">
				${this.#settingButtonTextNode}
				${this.icon}
			</div>
		`;

		if (this.#inputName === undefined)
		{
			this.#hiddenInput = Tag.render``;
		}
		else
		{
			this.#hiddenInput = Tag.render`<input type="hidden">`;

			this.#hiddenInput.setAttribute('name', this.#inputName);
			this.#hiddenInput.setAttribute('value', this.#selectedOptionKey);
		}

		return Tag.render`
			 <div class="setting-selector-container">
			${this.#settingButton}
			${this.#hiddenInput}
			 </div>
		`;
	}

	renderTo(targetContainer: HTMLElement): void
	{
		if (Type.isDomNode(targetContainer))
		{
			Dom.append(this.#container, targetContainer);
		}
	}
}
