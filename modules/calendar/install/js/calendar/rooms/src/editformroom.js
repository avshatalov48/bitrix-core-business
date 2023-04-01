import { Tag, Dom, Loc, Event} from 'main.core';
import { EditForm } from '../../sectioninterface/src/editform';
import { Util } from 'calendar.util';
import { TagSelector } from 'ui.entity-selector';

export class EditFormRoom extends EditForm
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Calendar.Rooms.EditFormRoom');

		this.DOM.outerWrap = options.wrap;
		this.roomsManager = options.roomsManager;
		this.categoryManager = options.categoryManager;
		this.capacityNumbers = [3, 5, 7, 10, 25];
		this.zIndex = options.zIndex || 3100;
		this.closeCallback = options.closeCallback;
		this.BX = Util.getBX();
		this.keyHandlerBinded = this.keyHandler.bind(this);
		this.freezeButtonsCallback = options.freezeButtonsCallback;
	}

	show(params = {})
	{
		this.setParams(params);
		this.create();
		if (this.showAccess)
		{
			Dom.style(this.DOM.accessLink, 'display', null);
			Dom.style(this.DOM.accessWrap, 'display', null);
		}
		else
		{
			Dom.style(this.DOM.accessLink, 'display', 'none');
			Dom.style(this.DOM.accessWrap, 'display', 'none');
		}

		Event.bind(document, 'keydown', this.keyHandlerBinded);
		Dom.addClass(this.DOM.outerWrap, 'show');

		if (this.room)
		{
			this.setInputValues(this.room);
		}

		this.setFocusOnInput();

		this.isOpenedState = true;
	}

	setParams(params)
	{
		this.actionType = params.actionType;
		this.room = params.room;
		this.showAccess = params.showAccess !== false;
	}

	setInputValues(room)
	{
		if (room.color)
		{
			this.setColor(room.color);
		}

		this.setAccess(room.access || room.data.ACCESS || {});

		if (room.name)
		{
			this.DOM.roomsTitleInput.value = room.name;
		}

		if (this.room.capacity)
		{
			this.DOM.roomsCapacityInput.value = room.capacity;
		}
	}

	setFocusOnInput()
	{
		BX.focus(this.DOM.roomsTitleInput);
		if (this.DOM.roomsTitleInput.value !== '')
		{
			this.DOM.roomsTitleInput.select();
		}
	}

	create()
	{
		this.wrap = this.getSliderContentWrap();
		this.DOM.formFieldsWrap = this.getFormFieldsWrap(this.wrap);

		this.DOM.roomsTitleInput = this.createTitleInput(this.DOM.formFieldsWrap);
		this.DOM.roomsCapacityInput = this.createCapacityInput(this.DOM.formFieldsWrap);
		this.DOM.categorySelect = this.DOM.formFieldsWrap.appendChild(this.renderCategorySelector());

		this.createBottomOptions(this.DOM.formFieldsWrap);
		this.createButtons(this.DOM.formFieldsWrap);

		this.isCreated = true;
	}

	getSliderContentWrap()
	{
		let sliderContentWrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
		if (sliderContentWrap)
		{
			Dom.clean(sliderContentWrap);
		}
		else
		{
			sliderContentWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-form-content"></div>
				`
			);
		}

		return sliderContentWrap;
	}

	getFormFieldsWrap(wrap)
	{
		return wrap.appendChild(
				Tag.render`
				<div class="calendar-list-slider-widget-content"></div>
			`
			)
			.appendChild(
				Tag.render`
				<div class="calendar-list-slider-widget-content-block"></div>
			`
			);
	}

	createTitleInput(wrap)
	{
		return wrap.appendChild(
			Tag.render`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<input type="text" placeholder="${Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE')}" 
							class="calendar-field calendar-field-string"
						/>
					</div>
				</div>
		`)
		.querySelector('.calendar-field')
		;
	}

	createCapacityInput(wrap)
	{
		return wrap.appendChild(
			Tag.render`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<div class ="calendar-list-slider-card-widget-title" style="margin-bottom: 0">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_SECTION_CAPACITY')}
							</span>
							<input type="number" class="calendar-field calendar-field-number" placeholder="0"/>
						</div>
					</div>
				</div>
		`)
		.querySelector('.calendar-field')
		;
	}

	renderCategorySelector()
	{
		const categorySelector = this.renderCategorySelectorWrap();

		this.categoryTagSelector = this.createCategoryTagSelector();

		this.categoryTagSelector.renderTo(categorySelector.querySelector('.calendar-list-slider-card-widget-title'));
		if(this.categoryTagSelector.isRendered())
		{
			this.onAfterCategorySelectorRender();
		}

		return categorySelector;
	}

	renderCategorySelectorWrap()
	{
		return Tag.render`
			<div class="calendar-field-container calendar-field-container-string calendar-field-container-rooms">
				<div class="calendar-field-block">
					<div class ="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">
							${Loc.getMessage('EC_SEC_SLIDER_ROOM_CATEGORY')}
						</span>
					</div>
				</div>
			</div>
		`;
	}

	createCategoryTagSelector()
	{
		let preparedCategories = [];
		preparedCategories = this.prepareCategoriesForDialog(this.categoryManager.getCategories());

		this.selectedCategory = null;
		if(this.room && this.room.categoryId)
		{
			this.selectedCategory = this.prepareCategoriesForDialog([
				this.categoryManager.getCategory(this.room.categoryId)
			]);
		}

		return new TagSelector({
			placeholder: Loc.getMessage('EC_SEC_SLIDER_CATEGORY_SELECTOR_PLACEHOLDER'),
			textBoxWidth: 320,
			multiple: false,
			events: {
				onTagAdd: () => {
					const itemsContainer = this.categoryTagSelector.getItemsContainer();
					Dom.addClass(
						itemsContainer,
						'calendar-room-form-category-selector-container-with-change-button',
					);
				},
				onTagRemove: () => {
					const itemsContainer = this.categoryTagSelector.getItemsContainer();
					Dom.removeClass(
						itemsContainer,
						'calendar-room-form-category-selector-container-with-change-button',
					);
				}
			},
			dialogOptions: {
				context: 'CALENDAR_CONTEXT',
				width: 315,
				height: 280,
				compactView: true,
				showAvatars: false,
				dropdownMode: true,
				tabs: [
					{
						id: 'category',
						title: 'categories',
						itemOrder: { title: 'asc' },
						icon: 'none',
						stubOptions: { title: Loc.getMessage('EC_SEC_SLIDER_CATEGORY_SELECTOR_STUB') },
					},
				],
				items: preparedCategories,
				selectedItems: this.selectedCategory,
			},
		});
	}

	onAfterCategorySelectorRender()
	{
		//make avatar containers in input smaller and hide tab icon
		Dom.addClass(this.categoryTagSelector.getDialog().getContainer(),'calendar-room-form-category-selector-dialog');

		//make entity selector input style similar to other inputs in room slider
		Dom.addClass(this.categoryTagSelector.getOuterContainer(), 'calendar-field-tag-selector-outer-container');
		Dom.addClass(this.categoryTagSelector.getTextBox(), 'calendar-field-tag-selector-text-box');
		if(this.selectedCategory !== null)
		{
			const itemsContainer = this.categoryTagSelector.getItemsContainer();
			Dom.addClass(itemsContainer, 'calendar-room-form-category-selector-container-with-change-button');
		}
	}

	createBottomOptions(wrap)
	{
		this.DOM.optionsWrap = wrap.appendChild(
			Tag.render`
			<div class="calendar-list-slider-new-calendar-options-container"></div>`
		);

		this.initSectionColorSelector();

		this.initAccessController();
	}

	createButtons(wrap)
	{
		this.buttonsWrap = wrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-btn-container"></div>
			`
		);

		if (this.actionType === 'createRoom')
		{
			this.renderCreateButton(this.buttonsWrap);
		}
		else if (this.actionType === 'updateRoom')
		{
			this.renderUpdateButton(this.buttonsWrap);
		}

		this.renderCancelButton(this.buttonsWrap);
	}

	renderCreateButton(wrap)
	{
		this.saveBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
			className: 'ui-btn ui-btn-success',
			events: { click: this.createRoom.bind(this) }
		});
		this.saveBtn.renderTo(wrap);
	}

	renderUpdateButton(wrap)
	{
		this.saveBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
			className: 'ui-btn ui-btn-success',
			events: { click: this.updateRoom.bind(this) }
		});
		this.saveBtn.renderTo(wrap);
	}

	renderCancelButton(wrap)
	{
		new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
			className: 'ui-btn ui-btn-link',
			events: { click: this.checkClose.bind(this) }
		}).renderTo(wrap);
	}

	createRoom()
	{
		if(this.freezeButtonsCallback)
		{
			this.freezeButtonsCallback();
		}
		this.saveBtn.setWaiting(true);
		this.roomsManager.createRoom({
				name: this.DOM.roomsTitleInput.value,
				capacity: this.DOM.roomsCapacityInput.value,
				color: this.color,
				access: this.access,
				categoryId: this.getSelectedCategory(),
			})
			.then(() => {
				this.saveBtn.setWaiting(false);
				this.close();
			});
	}

	initAccessController()
	{
		this.buildAccessController();
		this.initDialogStandard();
		this.initAccessSelectorPopup();
	}

	updateRoom()
	{
		if(this.freezeButtonsCallback)
		{
			this.freezeButtonsCallback();
		}
		this.saveBtn.setWaiting(true);
		this.roomsManager.updateRoom({
				id: this.room.id,
				location_id: this.room.location_id,
				name: this.DOM.roomsTitleInput.value,
				capacity: this.DOM.roomsCapacityInput.value,
				color: this.color,
				access: this.access,
				categoryId: this.getSelectedCategory(),
			})
			.then(() => {
				this.saveBtn.setWaiting(false);
				this.close();
			});
	}

	keyHandler(e)
	{
		if (this.categoryTagSelector.getDialog().isOpen())
		{
			return;
		}
		if (e.keyCode === Util.getKeyCode('escape'))
		{
			this.checkClose();
		}
		else if (e.keyCode === Util.getKeyCode('enter') && this.actionType === 'createRoom')
		{
			this.createRoom();
		}
		else if (e.keyCode === Util.getKeyCode('enter') && this.actionType === 'updateRoom')
		{
			this.updateRoom();
		}
	}

	prepareCategoriesForDialog(categories)
	{
		return categories.map((category) => {
			return {
				id: category.id,
				entityId: 'category',
				title: category.name,
				tabs: 'category',
			}
		});
	}

	getSelectedCategory()
	{
		const item = this.categoryTagSelector.getDialog().getSelectedItems()[0];

		return item ? item.id : null;
	}
}