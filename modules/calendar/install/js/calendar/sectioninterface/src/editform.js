import { Runtime, Dom, Event, Loc, Text, Type, Tag } from 'main.core';
import { Util } from 'calendar.util';
import {EventEmitter} from 'main.core.events';
import {Dialog as EntitySelectorDialog} from 'ui.entity-selector';


export class EditForm extends EventEmitter
{
	DOM = {};
	isCreated = false;

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.SectionInterface.EditForm');

		this.DOM.outerWrap = options.wrap;
		this.sectionAccessTasks = options.sectionAccessTasks;
		this.sectionManager = options.sectionManager;
		this.closeCallback = options.closeCallback;
		this.BX = Util.getBX();
		this.keyHandlerBinded = this.keyHandler.bind(this);
	}

	show(params = {})
	{
		this.section = params.section;
		this.create();
		this.showAccess = params.showAccess !== false;
		this.allowChangeName = params.allowChangeName !== false;
		if (this.showAccess)
		{
			this.DOM.accessLink.style.display = '';
			this.DOM.accessWrap.style.display = '';
		}
		else
		{
			this.DOM.accessLink.style.display = 'none';
			this.DOM.accessWrap.style.display = 'none';
		}

		Event.bind(document, 'keydown', this.keyHandlerBinded);
		Dom.addClass(this.DOM.outerWrap, 'show');

		if (params.section)
		{
			if (params.section.color)
			{
				this.setColor(params.section.color);
			}

			this.setAccess(params.section.access || params.section.data.ACCESS || {});

			if (params.section.name)
			{
				this.DOM.sectionTitleInput.value = params.section.name;
			}
		}

		if (this.allowChangeName)
		{
			BX.focus(this.DOM.sectionTitleInput);
			if (this.DOM.sectionTitleInput.value !== '')
			{
				this.DOM.sectionTitleInput.select();
			}
		}
		else
		{
			Dom.addClass(this.DOM.sectionTitleInput, '--disabled');
			this.DOM.sectionTitleInput.disabled = true;
		}

		this.isOpenedState = true;
	}

	close()
	{
		this.isOpenedState = false;
		Event.unbind(document, 'keydown', this.keyHandlerBinded);
		Dom.removeClass(this.DOM.outerWrap, 'show');

		if (Type.isFunction(this.closeCallback))
		{
			this.closeCallback();
		}
	}

	isOpened()
	{
		return this.isOpenedState;
	}

	create()
	{
		this.wrap = this.DOM.outerWrap.querySelector('.calendar-form-content');

		if (this.wrap)
		{
			Dom.clean(this.wrap);
		}
		else
		{
			this.wrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-form-content'}}));
		}

		this.DOM.formFieldsWrap = this.wrap.appendChild(
			Dom.create(
				'DIV',
				{
					props: {className: 'calendar-list-slider-widget-content'}
				}
			)
		)
			.appendChild(
				Dom.create(
					'DIV',
					{
						props: {className: 'calendar-list-slider-widget-content-block'}
					}
				)
			);

		// Title
		this.DOM.sectionTitleInput = this.DOM.formFieldsWrap.appendChild(
			Dom.create(
				'DIV',
				{
					props: {className: 'calendar-field-container calendar-field-container-string'}
				}
			)
		)
			.appendChild(Dom.create('DIV', {props: {className: 'calendar-field-block'}}))
			.appendChild(Dom.create('INPUT', {
				attrs: {type: 'text', placeholder: Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE')},
				props: {className: 'calendar-field calendar-field-string'}
			}));

		this.DOM.optionsWrap = this.DOM.formFieldsWrap.appendChild(
			Dom.create(
				'DIV',
				{
					props: {className: 'calendar-list-slider-new-calendar-options-container'}
				}
			)
		);

		this.initSectionColorSelector();

		this.initAccessController();

		// Buttons
		this.buttonsWrap = this.DOM.formFieldsWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-list-slider-btn-container'}}));

		this.saveBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
			className: 'ui-btn ui-btn-success',
			events: {click: this.save.bind(this)}
		});
		this.saveBtn.renderTo(this.buttonsWrap);

		new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
			className: 'ui-btn ui-btn-link',
			events: {click: this.checkClose.bind(this)}
		}).renderTo(this.buttonsWrap);

		this.isCreated = true;
	}

	keyHandler(e)
	{
		if(e.keyCode === Util.getKeyCode('escape'))
		{
			this.checkClose();
		}
		else if(e.keyCode === Util.getKeyCode('enter'))
		{
			this.save();
		}
	}

	checkClose()
	{
		this.close();
	}

	save()
	{
		this.saveBtn.setWaiting(true);
		this.sectionManager.saveSection(
			this.DOM.sectionTitleInput.value,
			this.color,
			this.access,
			{section: this.section}
		)
			.then(() => {
				this.saveBtn.setWaiting(false);
				this.close();
			});
	}

	initSectionColorSelector()
	{
		this.DOM.colorContWrap = this.DOM.optionsWrap.appendChild(Dom.create('DIV', {
			props: { className: 'calendar-list-slider-new-calendar-option-color' },
			html: Loc.getMessage('EC_SEC_SLIDER_COLOR')
		}));
		this.colorIcon = this.DOM.colorContWrap.appendChild(Dom.create('SPAN', {
			props: { className: 'calendar-list-slider-new-calendar-option-color-selected' }
		}));
		this.colorChangeLink = this.DOM.colorContWrap.appendChild(Dom.create('SPAN', {
			props: { className: 'calendar-list-slider-new-calendar-option-color-change' },
			html: Loc.getMessage('EC_SEC_SLIDER_CHANGE')
		}));
		
		Event.bind(this.colorIcon, 'click', this.showSimplePicker.bind(this));
		Event.bind(this.colorChangeLink, 'click', this.showSimplePicker.bind(this));
	}

	showSimplePicker(value)
	{
		const colors = Runtime.clone(Util.getDefaultColorList(), true);
		const innerCont = Dom.create(
			'DIV',
			{
				props: {className: 'calendar-simple-color-wrap calendar-field-container-colorpicker-square'}
			}
		);
		const colorWrap = innerCont.appendChild(
			Dom.create(
				'DIV',
				{
						events: {click: BX.delegate(this.simplePickerClick, this)}
				}
			)
		);
		const moreLinkWrap = innerCont.appendChild(
			Dom.create(
				'DIV',
				{
					props: {className: 'calendar-simple-color-more-link-wrap'}
				}
			)
		);
		const moreLink = moreLinkWrap.appendChild(
			Dom.create(
				'SPAN',
				{
					props: {className: 'calendar-simple-color-more-link'},
					html: Loc.getMessage('EC_COLOR'),
					events: {click: BX.delegate(this.showFullPicker, this)}
				}
			)
		);

		this.simplePickerColorWrap = colorWrap;
		this.colors = [];

		if (!colors.includes(this.color))
		{
			colors.push(this.color);
		}

		for (let i = 0; i < colors.length; i++)
		{
			this.colors.push({
				color: colors[i],
				node: colorWrap.appendChild(Dom.create('SPAN', {
					props: {className: 'calendar-field-colorpicker-color-item'},
					style: {backgroundColor: colors[i]},
					attrs: {'data-bx-calendar-color': colors[i]},
					html: '<span class="calendar-field-colorpicker-color"></span>'
				}))
			});
		}

		this.lastActiveNode = this.colors[BX.util.array_search(this.color, colors) || 0].node;
		Dom.addClass(this.lastActiveNode, 'active');

		this.simpleColorPopup = BX.PopupWindowManager.create(
			"simple-color-popup-" + Util.getRandomInt(),
			this.colorIcon,
			{
				//zIndex: this.zIndex,
				autoHide: true,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 9,
				lightShadow: true,
				content: innerCont,
				cacheable: false
			}
		);

		this.simpleColorPopup.setAngle({offset: 10});
		this.simpleColorPopup.show(true);
	}

	simplePickerClick(e)
	{
		const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
		if (Type.isElementNode(target))
		{
			const value = target.getAttribute('data-bx-calendar-color');
			if(value !== null)
			{
				if (this.lastActiveNode)
				{
					Dom.removeClass(this.lastActiveNode, 'active');
				}

				Dom.addClass(target, 'active');
				this.lastActiveNode = target;
				this.setColor(value);
			}
		}
	}

	showFullPicker()
	{
		if (this.simpleColorPopup)
		{
			this.simpleColorPopup.close();
		}

		if (!this.fullColorPicker || this.fullColorPicker.getPopupWindow()?.isDestroyed())
		{
			this.fullColorPicker = new BX.ColorPicker({
				bindElement: this.colorIcon,
				onColorSelected: BX.delegate(function(color){
					this.setColor(color);
				}, this),
				popupOptions: {
					cacheable: false,
					zIndex: this.zIndex,
					events: {
						onPopupClose:BX.delegate(function(){
						}, this)
					}
				}
			});
		}
		this.fullColorPicker.open();
	}

	setColor(value)
	{
		this.colorIcon.style.backgroundColor = value;
		this.color = value;
	}

	setAccess(value)
	{
		let rowsCount = 0;
		for (let code in value)
		{
			if (value.hasOwnProperty(code))
			{
				rowsCount++;
			}
		}
		this.accessRowsCount = rowsCount;
		this.access = value;

		for (let code in value)
		{
			if (value.hasOwnProperty(code))
			{
				this.insertAccessRow(Util.getAccessName(code), code, value[code]);
			}
		}
		this.checkAccessTableHeight();
	}

	initAccessController()
	{
		this.buildAccessController();
		if (this.sectionManager && this.sectionManager.calendarType === 'group')
		{
			this.initDialogGroup();
		}
		else
		{
			this.initDialogStandard();
		}
		this.initAccessSelectorPopup();
	}

	initAccessSelectorPopup()
	{
		Event.bind(this.DOM.accessWrap, 'click', (e) => {
			const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
			if (Type.isElementNode(target))
			{
				if (target.getAttribute('data-bx-calendar-access-selector') !== null)
				{
					// show selector
					const code = target.getAttribute('data-bx-calendar-access-selector');
					if (this.accessControls[code])
					{
						this.showAccessSelectorPopup({
								node: this.accessControls[code].removeIcon,
								setValueCallback: (value) => {
									if (this.accessTasks[value] && this.accessControls[code])
									{
										this.accessControls[code].valueNode.innerHTML =
											Text.encode(this.accessTasks[value].title);
										this.access[code] = value;
									}
								},
							},
						);
					}
				}
				else if (target.getAttribute('data-bx-calendar-access-remove') !== null)
				{
					const code = target.getAttribute('data-bx-calendar-access-remove');
					if (this.accessControls[code])
					{
						Dom.remove(this.accessControls[code].rowNode);
						this.accessControls[code] = null;
						delete this.access[code];
					}
				}
			}
		});
	}

	buildAccessController()
	{
		this.DOM.accessLink = this.DOM.optionsWrap.appendChild(
			Tag.render`<div class="calendar-list-slider-new-calendar-option-more">${Loc.getMessage('EC_SEC_SLIDER_ACCESS')}</div>`,
		);

		this.DOM.accessWrap = this.DOM.formFieldsWrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-access-container">
					<div class="calendar-list-slider-access-inner-wrap">
						${this.DOM.accessTable = Tag.render`
							<table class="calendar-section-slider-access-table"></table>
						`}
					</div>
					<div class="calendar-list-slider-new-calendar-options-container">
						${this.DOM.accessButton = Tag.render`
							<span class="calendar-list-slider-new-calendar-option-add">
								${Loc.getMessage('EC_SEC_SLIDER_ACCESS_ADD')}
							</span>`
			}
					</div>
				</div>`,
		);

		this.accessControls = {};
		this.accessTasks = this.sectionAccessTasks;

		Event.bind(this.DOM.accessLink, 'click', () => {
			if (Dom.hasClass(this.DOM.accessWrap, 'shown'))
			{
				Dom.removeClass(this.DOM.accessWrap, 'shown');
			}
			else
			{
				Dom.addClass(this.DOM.accessWrap, 'shown');
			}
			this.checkAccessTableHeight();
		});
	}

	initDialogStandard()
	{
		Event.bind(this.DOM.accessButton, 'click', () => {
			this.entitySelectorDialog = new EntitySelectorDialog({
				targetNode: this.DOM.accessButton,
				context: 'CALENDAR',
				preselectedItems: [],
				enableSearch: true,
				events: {
					'Item:onSelect': this.handleEntitySelectorChanges.bind(this),
					'Item:onDeselect': this.handleEntitySelectorChanges.bind(this),
				},
				popupOptions: {
					targetContainer: document.body,
				},
				entities: [
					{
						id: 'user',
					},
					{
						id: 'project',
					},
					{
						id: 'department',
						options: { selectMode: 'usersAndDepartments' },
					},
					{
						id: 'meta-user',
						options: { 'all-users': true },
					},
				]
			});
			this.entitySelectorDialog.show();
		});
	}

	initDialogGroup()
	{
		Event.bind(this.DOM.accessButton, 'click', () => {
			this.entitySelectorDialog = new EntitySelectorDialog({
				targetNode: this.DOM.accessButton,
				context: 'CALENDAR',
				preselectedItems: [],
				enableSearch: true,
				events: {
					'Item:onSelect': this.handleEntitySelectorChanges.bind(this),
					'Item:onDeselect': this.handleEntitySelectorChanges.bind(this),
				},
				popupOptions: {
					targetContainer: document.body,
				},
				entities: [
					{
						id: 'user',
					},
					{
						id: 'department',
						options: { selectMode: 'usersAndDepartments' },
					},
					{
						id: 'meta-user',
						options: { 'all-users': true },
					},
				],
				tabs: [
					{
						id: 'groupAccess',
						title: this.sectionManager.ownerName,
					},
				],
				items: [
					{
						id: 'SG' + this.sectionManager.ownerId + '_' + 'A',
						entityId: 'group',
						tabs: 'groupAccess',
						title: Loc.getMessage('EC_ACCESS_GROUP_ADMIN'),
					},
					{
						id: 'SG' + this.sectionManager.ownerId + '_' + 'E',
						entityId: 'group',
						tabs: 'groupAccess',
						title: Loc.getMessage('EC_ACCESS_GROUP_MODERATORS'),
					},
					{
						id: 'SG' + this.sectionManager.ownerId + '_' + 'K',
						entityId: 'group',
						tabs: 'groupAccess',
						title: Loc.getMessage('EC_ACCESS_GROUP_MEMBERS'),
					},
				],
			});
			this.entitySelectorDialog.show();
		});
	}

	handleEntitySelectorChanges()
	{
		const entityList = this.entitySelectorDialog.getSelectedItems();
		this.entitySelectorDialog.hide();
		if (Type.isArray(entityList))
		{
			entityList.forEach((entity) => {
				let title;
				if (entity.entityId === 'group')
				{
					title = this.sectionManager.ownerName + ': ' + entity.title.text;
				}
				else
				{
					title = entity.title.text;
				}
				const code = Util.convertEntityToAccessCode(entity);
				Util.setAccessName(code, title);
				this.insertAccessRow(title, code);
			});
		}

		Runtime.debounce(() => {
			this.entitySelectorDialog.destroy();
		}, 400)();
	}

	// todo: refactor it
	insertAccessRow(title, code, value)
	{
		if (!this.accessControls[code])
		{
			if (value === undefined)
			{
				for(let taskId in this.sectionAccessTasks)
				{
					if (
						this.sectionAccessTasks.hasOwnProperty(taskId)
						&& this.sectionAccessTasks[taskId].name === 'calendar_view'
					)
					{
						value = taskId;
						break;
					}
				}
			}

			const
				rowNode = Dom.adjust(this.DOM.accessTable.insertRow(-1), {props : {className: 'calendar-section-slider-access-table-row'}}),
				titleNode = Dom.adjust(rowNode.insertCell(-1), {
					props : {className: 'calendar-section-slider-access-table-cell'},
					html: '<span class="calendar-section-slider-access-title">' + Text.encode(title) + ':</span>'}),
				valueCell = Dom.adjust(rowNode.insertCell(-1), {
					props : {className: 'calendar-section-slider-access-table-cell'},
					attrs: {'data-bx-calendar-access-selector': code}
				}),
				selectNode = valueCell.appendChild(Dom.create('SPAN', {
					props: {className: 'calendar-section-slider-access-container'}
				})),
				valueNode = selectNode.appendChild(Dom.create('SPAN', {
					text: this.accessTasks[value] ? this.accessTasks[value].title : '',
					props: {className: 'calendar-section-slider-access-value'}
				})),
				removeIcon = selectNode.appendChild(Dom.create('SPAN', {
					props: {className: 'calendar-section-slider-access-remove'},
					attrs: {'data-bx-calendar-access-remove': code}
				}));

			this.access[code] = value;

			this.accessControls[code] = {
				rowNode: rowNode,
				titleNode: titleNode,
				valueNode: valueNode,
				removeIcon: removeIcon
			};
		}
	}

	checkAccessTableHeight()
	{
		if (this.checkTableTimeout)
		{
			this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
		}

		this.checkTableTimeout = setTimeout(() => {
			if (Dom.hasClass(this.DOM.accessWrap, 'shown'))
			{
				if (this.DOM.accessWrap.offsetHeight - this.DOM.accessTable.offsetHeight < 36)
				{
					this.DOM.accessWrap.style.maxHeight = parseInt(this.DOM.accessTable.offsetHeight) + 100 + 'px';
				}
			}
			else
			{
				this.DOM.accessWrap.style.maxHeight = '';
			}
		}, 300);
	}

	showAccessSelectorPopup(params)
	{
		if (
			this.accessPopupMenu
			&& this.accessPopupMenu.popupWindow
			&& this.accessPopupMenu.popupWindow.isShown()
		)
		{
			return this.accessPopupMenu.close();
		}

		const _this = this;
		const menuItems = [];

		for(let taskId in this.accessTasks)
		{
			if (this.accessTasks.hasOwnProperty(taskId))
			{
				menuItems.push(
					{
						text: this.accessTasks[taskId].title,
						onclick: (function (value)
						{
							return function ()
							{
								params.setValueCallback(value);
								_this.accessPopupMenu.close();
							}
						})(taskId)
					}
				);
			}
		}

		this.accessPopupMenu = this.BX.PopupMenu.create(
			'section-access-popup' + Util.randomInt(),
			params.node,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				offsetTop: -5,
				offsetLeft: 0,
				angle: true,
				cacheable: false
			}
		);

		this.accessPopupMenu.show();

	}
}








