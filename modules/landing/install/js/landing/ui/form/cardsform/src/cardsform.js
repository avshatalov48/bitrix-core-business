import {BaseForm} from 'landing.ui.form.baseform';
import {FormCollection} from 'landing.ui.collection.formcollection';
import {Loc} from 'landing.loc';
import {Content} from 'landing.ui.panel.content';
import {Dom, Runtime, Text, Type, Event} from 'main.core';
import {CardForm} from 'landing.ui.form.cardform';
import {Draggable} from 'ui.draganddrop.draggable';
import {PageObject} from 'landing.pageobject';
import {BaseEvent} from 'main.core.events';
import {TextField} from 'landing.ui.field.textfield';

import './css/cards_form.css';

export class CardsForm extends BaseForm
{
	constructor(options = {})
	{
		super(options);

		Dom.addClass(this.layout, 'landing-ui-form-cards');
		this.type = 'cards';
		this.code = options.code;
		this.id = `${this.code.replace('.', '')}-${Text.getRandom()}`;
		this.presets = options.presets;
		this.childForms = new FormCollection();
		this.presetForm = new FormCollection();
		this.sync = options.sync;
		this.forms = options.forms;
		this.wheelEventName = window.onwheel ? 'wheel' : 'mousewheel';

		this.onFormRemove = this.onFormRemove.bind(this);
		this.onAddCardClick = this.onAddCardClick.bind(this);
		this.onMouseWheel = this.onMouseWheel.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);

		this.addButton = this.createAddButton();
		this.draggable = new Draggable({
			container: this.body,
			draggable: '.landing-ui-form-cards-item',
			dragElement: '.landing-ui-form-card-item-header-drag',
			type: Draggable.MOVE,
		});

		this.draggable.subscribe('end', this.onDragEnd);

		setTimeout(() => {
			this.value = this.serialize();
		});

		this.adjustLastFormState();
		Dom.append(this.addButton.layout, this.footer);
	}

	createAddButton(): BX.Landing.UI.Button.BaseButton
	{
		return new BX.Landing.UI.Button.BaseButton(`add-card-${Text.getRandom()}`, {
			className: 'landing-ui-card-add-button',
			text: Loc.getMessage('LANDING_CARDS_FORM_ADD_BUTTON'),
			onClick: this.onAddCardClick,
		});
	}

	onFormRemove(event: BaseEvent)
	{
		this.childForms.remove(event.getTarget());
		this.sortForms();
		this.adjustLastFormState();
	}

	onDragEnd()
	{
		// @todo: Need add sort:end event for Draggable
		setTimeout(() => {
			this.sortForms();
		});
	}

	sortForms()
	{
		const children = [...this.body.children];

		this.childForms.sort((a, b) => {
			const aIndex = parseInt(children.indexOf(a.wrapper));
			const bIndex = parseInt(children.indexOf(b.wrapper));
			return aIndex < bIndex ? -1 : 1;
		});

		this.childForms.forEach((form, index) => {
			const [code] = form.selector.split('@');
			form.selector = `${code}@${index}`;
		});
	}

	addChildForm(form: CardForm)
	{
		this.childForms.add(form);
		form.subscribe('onRemove', this.onFormRemove);

		Dom.append(form.wrapper, this.body);
		this.adjustLastFormState();
	}

	addPresetForm(form)
	{
		this.presetForm.add(form);
		form.wrapper.hidden = true;
		Dom.append(form.wrapper, this.body);
		this.adjustLastFormState();
	}

	onAddCardClick()
	{
		if (Type.isPlainObject(this.presets) && Object.keys(this.presets).length > 0)
		{
			this.showPresetsPopup();
		}
		else
		{
			this.addEmptyCard();
		}
	}

	onPresetItemClick(presetId)
	{
		const preset = this.presets[presetId];

		const newForm = this.presetForm.find((form) => {
			return form.preset.id === presetId;
		}).clone();

		newForm.selector = `${newForm.selector.split('@')[0]}@${this.childForms.length}`;
		newForm.oldIndex = this.childForms.length;
		newForm.preset = Runtime.clone(preset);
		newForm.preset.id = presetId;
		this.addChildForm(newForm);
		this.adjustLastFormState();
		this.popup.close();

		if (Type.isPlainObject(preset.values))
		{
			newForm.fields.forEach((field) => {
				const code = field.selector.split('@')[0];

				if (code in preset.values)
				{
					field.setValue(preset.values[code]);

					if (field instanceof TextField)
					{
						BX.fireEvent(field.input, 'input');
					}
				}

				if (Type.isArray(preset.disallow))
				{
					const isDisallow = !!preset.disallow.find((fieldCode) => {
						return code === fieldCode;
					});

					if (isDisallow)
					{
						field.layout.hidden = true;
					}
				}
			});
		}
	}

	showPresetsPopup()
	{
		if (!this.popup)
		{
			this.popup = new BX.PopupMenuWindow({
				id: 'catalog_blocks_list',
				bindElement: this.addButton.layout,
				items: Object.keys(this.presets).map((preset) => {
					return {
						html: this.presets[preset].name,
						className: 'landing-ui-form-cards-preset-popup-item menu-popup-no-icon',
						onclick: this.onPresetItemClick.bind(this, preset),
					};
				}),
				autoHide: true,
				maxHeight: 176,
				minHeight: 87,
			});

			Event.bind(this.popup.popupWindow.popupContainer, 'mouseover', this.onMouseOver.bind(this));
			Event.bind(this.popup.popupWindow.popupContainer, 'mouseleave', this.onMouseLeave.bind(this));
			const rootWindow = PageObject.getRootWindow();
			Event.bind(rootWindow.document, 'click', this.onDocumentClick.bind(this));
			Dom.append(
				this.popup.popupWindow.popupContainer,
				this.addButton.layout.closest('.landing-ui-panel-content-body-content'),
			);
		}

		if (this.popup.popupWindow.isShown())
		{
			this.popup.popupWindow.close();
		}
		else
		{
			this.popup.popupWindow.show();
		}

		this.adjustPopupPosition();
	}

	onMouseOver()
	{
		const container = this.popup.popupWindow.getPopupContainer();
		Event.bind(container, this.wheelEventName, this.onMouseWheel, true);
		Event.bind(container, 'touchmove', this.onMouseWheel, true);
	}

	onMouseLeave()
	{
		const container = this.popup.popupWindow.getPopupContainer();
		Event.unbind(container, this.wheelEventName, this.onMouseWheel, true);
		Event.unbind(container, 'touchmove', this.onMouseWheel, true);
	}

	onMouseWheel(event)
	{
		event.stopPropagation();
		event.preventDefault();

		const delta = Content.getDeltaFromEvent(event);
		const {scrollTop} = this.popup.popupWindow.getContentContainer();

		requestAnimationFrame(() => {
			this.popup.popupWindow.contentContainer.scrollTop = scrollTop - delta.y;
		});
	}

	onDocumentClick()
	{
		if (this.popup.popupWindow)
		{
			this.popup.popupWindow.close();
		}
	}

	adjustPopupPosition()
	{
		if (this.popup.popupWindow)
		{
			requestAnimationFrame(() => {
				const offsetParent = this.addButton.layout.closest('.landing-ui-panel-content-body-content');

				const buttonTop = BX.Landing.Utils.offsetTop(this.addButton.layout, offsetParent);
				const buttonLeft = BX.Landing.Utils.offsetLeft(this.addButton.layout, offsetParent);
				const buttonRect = this.addButton.layout.getBoundingClientRect();
				const popupRect = this.popup.popupWindow.popupContainer.getBoundingClientRect();

				const yOffset = 14;

				this.popup.popupWindow.popupContainer.style.top = `${buttonTop + buttonRect.height + yOffset}px`;
				this.popup.popupWindow.popupContainer.style.left = `${buttonLeft - (popupRect.width / 2) + (buttonRect.width / 2)}px`;
				this.popup.popupWindow.setAngle({
					offset: 83,
					position: 'top',
				});
			});
		}
	}

	addEmptyCard()
	{
		const newData = Runtime.clone(this.childForms[0].data);
		const newSelector = `${newData.selector.split('@')[0]}@${this.childForms.length}`;
		newData.selector = newSelector;
		const newForm = this.childForms[0].clone(newData);
		newForm.oldIndex = this.childForms.length;
		newForm.selector = newSelector;
		newForm.fields.forEach((field) => field.reset());
		this.addChildForm(newForm);
		this.adjustLastFormState();
	}

	getVisibleForms()
	{
		return [...this.body.children].filter((item) => {
			return !item.hidden;
		});
	}

	adjustLastFormState()
	{
		const visibleItems = this.getVisibleForms();

		if (visibleItems.length === 1)
		{
			Dom.addClass(visibleItems[0], 'landing-ui-disallow-remove');
			return;
		}

		[...visibleItems].forEach((item) => {
			Dom.removeClass(item, 'landing-ui-disallow-remove');
		});
	}

	serialize()
	{
		return this.childForms.map((form) => {
			return form.serialize();
		});
	}

	/**
	 * Gets indexes map
	 * @return {Object}
	 */
	getIndexesMap()
	{
		return this.childForms.reduce((acc, form, index) => {
			return {...acc, [index]: form.oldIndex};
		}, {});
	}

	getUsedPresets()
	{
		return this.childForms.reduce((acc, form) => {
			if (Type.isPlainObject(form.preset))
			{
				const [, index] = form.selector.split('@');
				acc[index] = form.preset.id;
			}

			return acc;
		}, {});
	}

	isChanged()
	{
		return JSON.stringify(this.value) !== JSON.stringify(this.serialize());
	}
}