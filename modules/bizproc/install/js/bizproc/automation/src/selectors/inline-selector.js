import {
	DelayInterval,
	DelayIntervalSelector,
	Helper,
	SelectorContext,
} from 'bizproc.automation';
import { Dom, Event, Loc, Runtime, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu, MenuManager } from 'main.popup';
import { Dialog } from 'ui.entity-selector';
import enrichFieldsWithModifiers from './enrich-fields-with-modifiers';
import { DocumentGroup } from './group/document-group';
import { FileGroup } from './group/file-group';

import { Field, MenuGroupItem } from './types';

export class InlineSelector extends EventEmitter
{
	context: SelectorContext;
	fieldProperty: ?Field = null;
	replaceOnWrite: boolean = false;
	menuButton: ?HTMLSpanElement = null;
	targetInput: ?HTMLElement = null;
	#menuGroups: Object<string, MenuGroupItem> = {};
	basisFields: Array<Object> = [];
	#dialog: ?Dialog = null;
	#switcherDialog: ?Menu = null;

	constructor(props: { context: SelectorContext })
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation.Selector');

		this.context = props.context;
		this.basisFields = this.context.fields;
	}

	hasGroup(groupId: string): boolean
	{
		return this.#menuGroups.hasOwnProperty(groupId);
	}

	addGroup(groupId: string, group: MenuGroupItem)
	{
		const normalizedGroup = this.#normalizeGroup(group);

		if (this.hasGroup(groupId))
		{
			this.#menuGroups[groupId] = (
				this.#normalizeGroup(this.#mergeGroups(this.#menuGroups[groupId], normalizedGroup))
			);

			return;
		}

		this.#menuGroups[groupId] = normalizedGroup;
	}

	#mergeGroups(originalGroup: MenuGroupItem, newGroup: MenuGroupItem): MenuGroupItem
	{
		return {
			...originalGroup,
			...newGroup,
			children: [
				...originalGroup.children,
				...newGroup.children,
			],
		};
	}

	addGroupItem(groupId: string, item: MenuGroupItem)
	{
		if (this.hasGroup(groupId))
		{
			this.#menuGroups[groupId].children.push(this.#normalizeGroup(item));
		}
	}

	#normalizeGroup(group: MenuGroupItem)
	{
		if (!Type.isArray(group.children))
		{
			group.children = [];
		}

		group.children = (
			group
				.children
				.filter(item => item.customData?.field ? this.#shouldShowField(item.customData.field) : true)
				.map(childGroup => this.#normalizeGroup(childGroup))
		);

		return {
			entityId: 'bp',
			tabs: 'recents',
			...group
		};
	}

	renderWith(targetInput: Element): HTMLDivElement
	{
		this.targetInput = Runtime.clone(targetInput);
		this.targetInput.setAttribute('autocomplete', 'off');

		this.menuButton = Tag.render`
			<span 
				onclick="${this.openMenu.bind(this)}"
				class="bizproc-automation-popup-select-dotted"
			></span>
		`;

		this.parseTargetProperties();

		this.replaceOnWrite |= (this.targetInput.getAttribute('data-select-mode') === 'replace');

		return Tag.render`
			<div class="bizproc-automation-popup-select">
				${this.targetInput}
				${this.menuButton}
			</div>
		`;
	}

	renderTo(targetInput: Element): void
	{
		targetInput.parentNode.replaceChild(this.renderWith(targetInput), targetInput);
	}

	bindTargetEvents(): void
	{
		Event.bind(this.targetInput, 'keydown', this.#onKeyDown.bind(this));
	}

	parseTargetProperties(): void
	{
		this.fieldProperty = JSON.parse(this.targetInput.getAttribute('data-property'));
		const propertyType = this.targetInput.getAttribute('data-selector-type');

		if (!this.fieldProperty && propertyType)
		{
			this.fieldProperty = { Type: propertyType };
		}

		if (this.fieldProperty)
		{
			this.fieldProperty.Type = this.fieldProperty.Type || propertyType;
			this.#prepareSelectorUsingFieldType();
		}
		else
		{
			this.context.useSwitcherMenu = false;
		}

		this.replaceOnWrite |= (this.targetInput.getAttribute('data-select-mode') === 'replace');
	}

	#prepareSelectorUsingFieldType(): void
	{
		this.basisFields = this.basisFields.filter((field) => this.#shouldShowField(field));

		const type = this.fieldProperty?.Type;
		if (type === 'file')
		{
			this.replaceOnWrite = true;
		}
		else if (type === 'date' || type === 'datetime')
		{
			this.replaceOnWrite = true;

			const delayIntervalSelector = new DelayIntervalSelector({
				labelNode: this.targetInput,
				basisFields: this.basisFields,
				useAfterBasis: true,
				onchange: (function(delay)
				{
					this.targetInput.value = delay.toExpression(
						this.basisFields,
						Helper.getResponsibleUserExpression(this.context.fields),
					);
				}).bind(this),
			});

			delayIntervalSelector.init(DelayInterval.fromString(this.targetInput.value, this.basisFields));
		}
	}

	#shouldShowField(field: Field): boolean
	{
		const fieldType = this.fieldProperty?.Type;
		if (fieldType === 'file')
		{
			return field.Type === 'file';
		}
		else if (fieldType === 'date' || fieldType === 'datetime')
		{
			return field.Type === 'date' || field.Type === 'datetime';
		}
		else if (fieldType === 'time')
		{
			return field.Type === 'date' || field.Type === 'datetime' || field.Type === 'time';
		}

		return true;
	}

	#onKeyDown(event: KeyboardEvent)
	{
		if (event.keyCode === 45 && event.altKey === false && event.ctrlKey === false && event.shiftKey === false)
		{
			this.openMenu(event);
			event.preventDefault();
		}
	}

	openMenu(event: KeyboardEvent, skipPropertiesSwitcher: boolean = false)
	{
		if (!skipPropertiesSwitcher && this.context.useSwitcherMenu && !this.targetInput.value)
		{
			return this.openPropertiesSwitcherMenu();
		}

		if (this.#dialog)
		{
			this.#dialog.show();
			return;
		}

		this.fillGroups();
		this.onMenuOpen();

		let menuItems = [];
		for (const group of Object.values(this.#menuGroups))
		{
			if (group.children.length > 0)
			{
				menuItems.push(group);
			}
		}

		if (menuItems.length === 1)
		{
			menuItems = menuItems[0].children;
		}

		let menuId = this.menuButton.getAttribute('data-selector-id');
		if (!menuId)
		{
			menuId = Helper.generateUniqueId();
			this.menuButton.setAttribute('data-selector-id', menuId);
		}

		this.#dialog = new Dialog({
			targetNode: this.menuButton,
			width: 500,
			height: 300,
			multiple: false,
			dropdownMode: true,
			enableSearch: true,
			items: this.injectDialogMenuTitles(menuItems),
			showAvatars: false,
			events: {
				'Item:onBeforeSelect': (event) => {
					event.preventDefault();

					const item = event.getData().item;
					this.onFieldSelect(item.getCustomData().get('field'));
				}
			},
			compactView: true,
		});

		this.#dialog.show();
	}

	fillGroups(): void
	{
		this.fillFieldsGroups();
		this.fillFileGroup();
	}

	fillFieldsGroups(): void
	{
		const documentGroup = new DocumentGroup({
			fields: this.getFields(),
			title: this.context.rootGroupTitle,
			setSuperTitle: false,
		});

		documentGroup.groupsWithChildren.forEach((group) => {
			this.addGroup(group.id, group);
		});
	}

	fillFileGroup(): void
	{
		const fileFields = this.getFields().filter((field) => field.Type === 'file');

		const fileGroup = new FileGroup({
			fields: enrichFieldsWithModifiers(
				fileFields,
				'Document',
				{
					friendly: false,
					printable: false,
					server: false,
					responsible: false,
					shortLink: true,
				},
			).filter((field) => field.Type === 'string'),
			setSuperTitle: false,
		});

		fileGroup.groupsWithChildren.forEach((group) => {
			this.addGroup(group.id, group);
		});
	}

	onMenuOpen(): void
	{
		this.emit('onOpenMenu', { selector: this });
	}

	openPropertiesSwitcherMenu()
	{
		const self = this;

		MenuManager.show(
			Helper.generateUniqueId(),
			this.menuButton,
			[
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'),
					disabled: self.fieldProperty?.Type === 'file',
					onclick(event) {
						this.popupWindow.close();
						self.emit('onAskConstant', {fieldProperty: self.fieldProperty});
					}
				},
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'),
					onclick(event) {
						this.popupWindow.close();
						self.emit('onAskParameter', {fieldProperty: self.fieldProperty});
					}
				},
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_ASK_MANUAL'),
					onclick(event) {
						this.popupWindow.close();
						self.openMenu(event, true);
					}
				}
			],
			{
				autoHide: true,
				offsetLeft: 20,
				angle: { position: 'top'},
				events: {
					onPopupClose()
					{
						this.destroy();
					}
				}
			}
		);
		this.#switcherDialog = MenuManager.currentItem;

		return true;
	}

	injectDialogMenuTitles(items)
	{
		items.forEach((parent) => {
			if (Type.isArray(parent.children))
			{
				this.injectDialogMenuSupertitles(parent.title, parent.children);
			}
		});

		return items;
	}

	injectDialogMenuSupertitles(title: string, children)
	{
		children.forEach((child) => {
			if (!child.supertitle)
			{
				child.supertitle = title;
			}
			if (Type.isArrayFilled(child.children))
			{
				this.injectDialogMenuSupertitles(child.title, child.children);
			}
		});
	}

	onFieldSelect(field: ?Field): void
	{
		if (!field)
		{
			return;
		}

		const inputType = this.targetInput.tagName.toLowerCase();

		if (inputType === 'select')
		{
			let expressionOption = this.targetInput.querySelector('[data-role="expression"]');
			if (!expressionOption)
			{
				expressionOption = (
					this.targetInput.appendChild(
						Dom.create(
							'option',
							{attrs: {'data-role': 'expression'}}
						)
					)
				);
			}
			expressionOption.setAttribute('value', field.Expression);
			expressionOption.textContent = field['Expression'];

			expressionOption.selected = true;
		}
		else if (inputType === 'label')
		{
			this.targetInput.textContent = field.Expression;
			const hiddenInput = document.getElementById(this.targetInput.getAttribute('for'));
			if (hiddenInput)
			{
				hiddenInput.value = field.Expression;
			}
		}
		else
		{
			if (this.replaceOnWrite)
			{
				this.targetInput.value = field.Expression;
				this.targetInput.selectionEnd = this.targetInput.value.length;
			}
			else
			{
				let beforePart = '';
				const middlePart = field.Expression;
				let afterPart = '';
				if (Type.isStringFilled(this.targetInput.value))
				{
					beforePart = this.targetInput.value.substr(0, this.targetInput.selectionEnd);
					afterPart = this.targetInput.value.substr(this.targetInput.selectionEnd);
				}

				this.targetInput.value = beforePart + middlePart + afterPart;
				this.targetInput.selectionEnd = beforePart.length + middlePart.length;
			}
		}

		BX.fireEvent(this.targetInput, 'change');
		this.emit('Field:Selected', { field });
	}

	destroy()
	{
		if (this.#dialog)
		{
			this.#dialog.destroy();
		}
		if (this.#switcherDialog)
		{
			this.#switcherDialog.destroy();
		}
	}

	getFields(): Array<Field>
	{
		return enrichFieldsWithModifiers(this.basisFields, 'Document', { shortLink: false });
	}
}
