import {BaseEvent, EventEmitter} from "main.core.events";
import {Dom, Reflection, Type, Tag, Loc, Text} from 'main.core';
import {Loader} from "main.loader";
import Section from "./section";
import 'ui.notification';
import { EntitySelectorOptions } from './selector/entity-selector-adapter';

const BX = Reflection.namespace('BX');

export type GridOptions = {
	options: GridOptions;
	renderTo: HTMLElement;
	buttonPanel: BX.UI.ButtonPanel;
	component: string;
	actionSave: null;
	actionDelete: null;
	actionLoad: null;
	mode: 'string';
	openPopupEvent: null;
	popupContainer: null;
	additionalSaveParams: {};
	userGroups: [];
	accessRights: [];
	loadParams: {};
	needToLoadUserGroups?: boolean;
	isSaveOnlyChangedRights?: boolean;
	useEntitySelectorDialogAsPopup?: boolean;
	entitySelectorDialogOptions?: EntitySelectorOptions
};

export default class Grid {
	static ACTION_SAVE = 'save';
	static ACTION_DELETE = 'delete';
	static ACTION_LOAD = 'load';
	static MODE = 'ajax';

	constructor(options: GridOptions)
	{
		options = options || {};
		this.options = options;
		this.renderTo = options.renderTo;
		this.buttonPanel = BX.UI.ButtonPanel || null;

		this.layout = {
			container: null
		};
		this.component = options.component ? options.component : null;
		this.actionSave = options.actionSave || Grid.ACTION_SAVE;
		this.actionDelete = options.actionDelete || Grid.ACTION_DELETE;
		this.actionLoad = options.actionLoad || Grid.ACTION_LOAD;
		this.mode = options.mode || Grid.MODE;
		this.openPopupEvent = options.openPopupEvent ? options.openPopupEvent : null;
		this.popupContainer = options.popupContainer ? options.popupContainer : null;
		this.additionalSaveParams = options.additionalSaveParams ? options.additionalSaveParams : null;
		this.loadParams = options.loadParams ? options.loadParams : null;
		this.loader = null;
		this.timer = null;
		this.needToLoadUserGroups = options.needToLoadUserGroups ?? true;
		this.isSaveOnlyChangedRights = options.isSaveOnlyChangedRights || false;

		this.useEntitySelectorDialogAsPopup = options.useEntitySelectorDialogAsPopup || false;
		this.entitySelectorDialogOptions = options.entitySelectorDialogOptions || null;

		this.expandedGroups = [];
		this.groupElements = [];
		this.changedAccessIds = new Map();

		this.initData();
		if (options.userGroups)
		{
			this.userGroups = options.userGroups;
		}
		if (options.accessRights)
		{
			this.accessRights = options.accessRights;
		}

		this.isRequested = false;

		this.loadData();
		this.bindEvents();
	}

	bindEvents(): void
	{
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:updateRole', this.updateRole.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:selectAccessItems', this.updateAccessVariationRight.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOn', this.updateAccessRight.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOff', this.updateAccessRight.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:update', this.adjustButtonPanel.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addUserGroup.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addRoleColumn.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addRoleColumn.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addUserGroup.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.removeRoleColumn.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.adjustButtonPanel.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:toggleGroup', this.toggleGroup.bind(this));
		EventEmitter.subscribe('BX.Main.SelectorV2:onGetEntityTypes', this.onGetEntityTypes.bind(this));
	}

	initData(): void
	{
		this.accessRights = [];
		this.userGroups = [];
		this.accessRightsSections = [];
		this.headSection = null;
		this.members = [];
		this.columns = [];
		this.changedAccessIds = new Map();
	}

	fireEventReset(): void
	{
		EventEmitter.emit('BX.UI.AccessRights:reset', this);
	}

	fireEventRefresh(): void
	{
		EventEmitter.emit( 'BX.UI.AccessRights:refresh', this);
	}

	getButtonPanel(): BX.UI.ButtonPanel
	{
		return this.buttonPanel;
	}

	showNotification(title): void
	{
		BX.UI.Notification.Center.notify({
			content: title,
			position: 'top-right',
			autoHideDelay: 3000,
		});
	}

	sendActionRequest(): void
	{
		if (this.isRequested)
		{
			return;
		}

		this.isRequested = true;
		EventEmitter.emit(this, 'onBeforeSave', this);

		this.timer = setTimeout(
			() => {
				this.blockGrid();
			},
			1000
		);

		let needReload = false;
		const dataToSave = [];

		for (let i = 0; i < this.userGroups.length; i++)
		{
			const userGroup = this.userGroups[i];

			if (Text.toNumber(userGroup.id) === 0)
			{
				needReload = true;
			}

			let accessRights = userGroup.accessRights;

			if (this.isSaveOnlyChangedRights === true)
			{
				accessRights = this.#filterOnlyChangedAccessRight(accessRights, userGroup);
			}

			dataToSave.push({
				accessCodes: userGroup.accessCodes,
				id: userGroup.id,
				title: userGroup.title,
				type: userGroup.type,
				accessRights,
			});
		}

		BX.ajax.runComponentAction(
			this.component,
			this.actionSave,
			{
				mode: this.mode,
				data: {
					userGroups: dataToSave,
					parameters: this.additionalSaveParams
				},
				// analyticsLabel: {
				// 	viewMode: 'grid',
				// 	filterState: 'closed'
				// }
			}
		).then(
			() => {
				if (needReload)
				{
					this.reloadGrid();
				}

				this.isRequested = false;
				this.showNotification(Loc.getMessage('JS_UI_ACCESSRIGHTS_STTINGS_HAVE_BEEN_SAVED'));
				this.unBlockGrid();
				this.fireEventRefresh();
				setTimeout(() => {
					this.adjustButtonPanel()
				});
				clearTimeout(this.timer);
				const waitContainer = this.buttonPanel.getContainer().querySelector('.ui-btn-wait');
				Dom.removeClass(waitContainer, 'ui-btn-wait');
				this.changedAccessIds = new Map();
			},
			(response) => {
				let errorMessage = 'Error message';
				if (response.errors)
				{
					errorMessage = response.errors[0].message;
				}
				this.isRequested = false;
				this.showNotification(errorMessage);
				this.unBlockGrid();
				clearTimeout(this.timer);
				const waitContainer = this.buttonPanel.getContainer().querySelector('.ui-btn-wait');
				Dom.removeClass(waitContainer, 'ui-btn-wait');
			}
		);

		EventEmitter.emit( 'BX.UI.AccessRights:preservation', this);
	}

	lock(): void
	{
		Dom.addClass(this.getMainContainer(), '--lock');
	}

	unlock(): void
	{
		Dom.removeClass(this.getMainContainer(), '--lock');
	}

	deleteActionRequest(roleId): void
	{
		if (this.isRequested)
		{
			return;
		}

		this.isRequested = true;

		this.timer = setTimeout(
			() => {
				this.blockGrid();
				},
			1000
		);

		BX.ajax.runComponentAction(
			this.component,
			this.actionDelete,
			{
				mode: this.mode,
				data: {
					roleId: roleId
				},
				// analyticsLabel: {
				// 	viewMode: 'grid',
				// 	filterState: 'closed'
				// }
			}
		).then(
			() => {
				this.isRequested = false;
				this.showNotification(Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_REMOVE'));
				this.unBlockGrid();
				clearTimeout(this.timer);
			},
			(response) => {
				let errorMessage = 'Error message';
				if (response.errors)
				{
					errorMessage = response.errors[0].message;
				}
				this.isRequested = false;
				this.showNotification(errorMessage);
				this.unBlockGrid();
				clearTimeout(this.timer);
			}
		);
	}

	reloadGrid(): void
	{
		this.initData();

		BX.ajax.runComponentAction(
			this.component,
			this.actionLoad,
			{
				mode: this.mode,
				data: {
					parameters: this.loadParams
				},
			}
		).then(
			(response) => {
				if (
					response.data['ACCESS_RIGHTS']
					&& response.data['USER_GROUPS']
				) {
					this.accessRights = response.data.ACCESS_RIGHTS;
					this.userGroups = response.data.USER_GROUPS;
					this.loadData();
					this.draw();
				}
				this.unBlockGrid();
			},
			(err) => {
				console.error(err);
				this.unBlockGrid
			}
		);
	}

	blockGrid(): void
	{
		const offsetTop =
			this.layout.container.getBoundingClientRect().top < 0
				? '0'
				: this.layout.container.getBoundingClientRect().top
		;

		Dom.addClass(this.layout.container, 'ui-access-rights-block');
		Dom.style(this.layout.container, 'height', 'calc(100vh - ' + offsetTop  + 'px)')

		setTimeout(() => {
			Dom.style(this.layout.container, 'height', 'calc(100vh - ' + offsetTop  + 'px)')
		});

		this
			.getLoader()
			.show()
		;
	}

	unBlockGrid(): void
	{
		Dom.removeClass(this.layout.container, 'ui-access-rights-block');
		Dom.style(this.layout.container, 'height', null)

		this
			.getLoader()
			.hide()
		;
	}

	getLoader(): Loader
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				target: this.layout.container
			});
		}

		return this.loader;
	}

	removeRoleColumn(param): void
	{
		this.headSection.removeColumn(param.data);
		this.accessRightsSections.map(
			(data) => {
				data.removeColumn(param.data);
			}
		);

		const targetIndex = this.userGroups.indexOf(param.data.userGroup);
		this.userGroups.splice(targetIndex, 1);

		const roleId = param.data.userGroup.id;
		if (roleId > 0)
		{
			this.deleteActionRequest(roleId);
		}
	}

	addRoleColumn(event: BaseEvent): void
	{
		const [param] = event.getData();
		if (!param)
		{
			return;
		}

		const sections = this.accessRightsSections;

		for (let i = 0; i < sections.length; i++)
		{
			param.headSection = false;
			param.newColumn = true;
			sections[i].addColumn(param);
			sections[i].scrollToRight(sections[i].getColumnsContainer().scrollWidth - sections[i].getColumnsContainer().offsetWidth, 'stop');
		}

		param.headSection = true;
		param.newColumn = true;
		this.headSection.addColumn(param);

		this.actualizeExpandedGroups();
	}

	addUserGroup(event: BaseEvent): void
	{
		let [options] = event.getData();
		options = options || {};
		this.userGroups.push(options);
	}

	updateRole(event: BaseEvent): void
	{
		const item = event.getData();
		const index = this.userGroups.indexOf(item.userGroup);
		if (index >= 0)
		{
			this.userGroups[index].title = item.text;
		}
	}

	adjustButtonPanel(): void
	{
		const modifiedItems = this.getMainContainer().querySelectorAll('.ui-access-rights-column-item-changer-on');
		const modifiedRoles = this.getMainContainer().querySelectorAll('.ui-access-rights-column-new');
		const modifiedUsers = this.getMainContainer().querySelectorAll('.ui-access-rights-members-item-new');
		const modifiedVariables = this.getMainContainer().querySelectorAll('.ui-tag-selector-container');

		if(modifiedItems.length > 0 || modifiedRoles.length > 0 || modifiedUsers.length > 0 || modifiedVariables.length > 0)
		{
			this.buttonPanel.show();
		}
		else
		{
			this.buttonPanel.hide();
		}
	}

	updateAccessRight(event: BaseEvent): void
	{
		const data = event.getData();
		const userGroup = this.userGroups[this.userGroups.indexOf(data.userGroup)];
		const accessId = data.access.id;

		setTimeout(() => {
			this.#storeChangedAccessId(data);
		}, 0)

		for (let i = 0; i < userGroup.accessRights.length; i++)
		{
			const item = userGroup.accessRights[i];
			if (item && String(item.id) === String(accessId))
			{
				item.value = (String(item.value) === '0') ? '1' : '0';

				return;
			}
		}

		userGroup.accessRights.push({
			id: accessId,
			value: data.switcher.isChecked() ? '1' : '0'
		});
	}

	updateAccessVariationRight(event: BaseEvent): void
	{
		const item = event.getData();
		const userGroup = this.userGroups[this.userGroups.indexOf(item.userGroup)];
		const accessId = item.access.id;

		this.#storeChangedAccessId(item);

		const deleteIds = [];
		for (let i = 0; i < userGroup.accessRights.length; i++)
		{
			const item = userGroup.accessRights[i];
			if (item && String(item.id) === String(accessId))
			{
				deleteIds.push(i);
			}
		}

		deleteIds.forEach((i) => {
			delete (userGroup.accessRights[i]);
		});

		const values = item.selectedValues || [];
		values.forEach((value) => {
			userGroup.accessRights.push({
				id: accessId,
				value: value
			});
		});
	}

	loadData()
	{
		this.accessRights.map(
			(data, index) => {
				data.id = index;
				this.accessRightsSections.push(this.addSection(data));
			}
		);
	}

	getColumns(): Column[]
	{
		return this.columns;
	}

	getSections(): Section[]
	{
		return this.accessRightsSections;
	}

	getUserGroups(): []
	{
		this.userGroups.forEach(
			(item) => {
				if (item.accessCodes)
				{
					for (const user in item.members)
					{
						item.accessCodes[user] = item.members[user].type
					}
				}
			}
		);

		return this.userGroups;
	}

	getHeadSection(): Section
	{
		if (!this.headSection)
		{
			this.headSection = new Section({
				headSection: true,
				userGroups: this.userGroups,
				grid: this
			});
		}

		return this.headSection;
	}

	addSection(options): Section
	{
		options = options || {};
		return new Section({
			id: options.id,
			hint: options.sectionHint,
			title: options.sectionTitle,
			rights: options.rights ? options.rights : [],
			grid: this
		});
	}

	getSectionNode(): HTMLElement
	{
		return Tag.render`<div class='ui-access-rights-section'></div>`;
	}

	getMainContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`<div class='ui-access-rights'></div>`;
		}

		return this.layout.container;
	}

	draw(): void
	{
		const docFragmentSections = document.createDocumentFragment();
		Dom.append(this.getHeadSection().render(), docFragmentSections);

		this
			.getSections()
			.map((data) => {
				Dom.append(data.render(), docFragmentSections);
			})
		;

		this.layout.container = null;
		Dom.append(docFragmentSections, this.getMainContainer());

		this.renderTo.innerHTML = '';
		Dom.append(this.getMainContainer(), this.renderTo);

		this.afterRender();
	}

	afterRender(): void
	{
		this.getHeadSection().adjustEars();
		this
			.getSections()
			.map((data) => {
				data.adjustEars();
			})
		;
	}

	onMemberSelect(params): void
	{
		const option = Grid.buildOption(params);
		if (!option)
		{
			return;
		}

		if (params.state === 'select')
		{
			EventEmitter.emit('BX.UI.AccessRights:addToAccessCodes', option);
		}
	}

	onMemberUnselect(params)
	{
		const option = Grid.buildOption(params);

		if (!option)
		{
			return;
		}

		EventEmitter.emit('BX.UI.AccessRights:removeFromAccessCodes', option);
	}

	onGetEntityTypes(): void
	{
		if (!this.needToLoadUserGroups)
		{
			return;
		}

		const controls = BX.Main
			.selectorManagerV2
			.controls
		;
		const selectorInstance = controls[Object.keys(controls)[0]];

		selectorInstance.entityTypes.USERGROUPS = {
			options: {
				enableSearch: 'Y',
				searchById: 'Y',
				addTab: 'Y',
				returnItemUrl: (selectorInstance.getOption('returnItemUrl') === 'N' ? 'N' : 'Y')
			}
		};
	}

	toggleGroup(event: BaseEvent): void
	{
		const groupId = event.getData().id;

		var idx = this.expandedGroups.indexOf(groupId);
		if (idx > -1)
		{
			this.expandedGroups.splice(idx, 1);
		}
		else
		{
			this.expandedGroups.push(groupId);
		}

		this.actualizeExpandedGroups();
	}

	actualizeExpandedGroups()
	{
		for (const groupItem of this.groupElements)
		{
			if (this.igGroupsExpanded(groupItem.group))
			{
				groupItem.container.classList.add('--expanded');
			}
			else
			{
				groupItem.container.classList.remove('--expanded');
			}
		}
	}

	igGroupsExpanded(group: string): string[]
	{
		return this.expandedGroups.includes(group);
	}

	#makeChangedHash(roleId: number | string, accessId: string): string
	{
		return `r${roleId}_a${accessId}`;
	}

	#storeChangedAccessId(item): void
	{
		const accessId = item.access.id;
		const isAccessChanged = item.isModify;
		const userGroup = this.userGroups[this.userGroups.indexOf(item.userGroup)];

		const changedCode = this.#makeChangedHash(userGroup.id, accessId);

		if (isAccessChanged && !this.changedAccessIds.has(changedCode))
		{
			this.changedAccessIds.set(changedCode, { accessId, roleId: userGroup.id });
		}
		else if (!isAccessChanged && this.changedAccessIds.has(changedCode))
		{
			this.changedAccessIds.delete(changedCode);
		}
	}

	#filterOnlyChangedAccessRight(accessRights, userGroup): Array
	{
		const processedChanged = new Map(this.changedAccessIds);

		const filteredAccessRights = accessRights.filter((access) => {

			if (Number(userGroup.id) === 0)
			{
				return true;
			}

			const changedCode = this.#makeChangedHash(userGroup.id, access.id);

			const found = this.changedAccessIds.has(changedCode);

			if (found)
			{
				processedChanged.delete(changedCode);
			}

			return found;
		});

		// some rights may be changed but not present in the accessRights array because they values were deleted.
		// Than have to will add them with null value.
		for (const [key, data] of processedChanged)
		{
			if (data.roleId != userGroup.id)
			{
				continue;
			}

			filteredAccessRights.push({
				id: data.accessId,
				value: null,
			});
		}

		return filteredAccessRights;
	}

	static buildOption(params): {}
	{
		const controls = BX.Main
			.selectorManagerV2
			.controls
		;
		const selectorInstance = controls[Object.keys(controls)[0]].selectorInstance;
		const dataColumnAttribute = 'bx-data-column-id';

		const node = selectorInstance.bindOptions.node;

		if (!node.hasAttribute(dataColumnAttribute) || Type.isUndefined(params.item))
		{
			return false;
		}

		const columnId = node.getAttribute(dataColumnAttribute);

		const accessItem = params.item.id;
		const entityType = params.entityType;
		const accessCodesResult = {};
		accessCodesResult[accessItem] = entityType;

		return {
			accessCodes: accessCodesResult,
			columnId,
			item: params.item,
		};
	}
}

const namespace = Reflection.namespace('BX.UI');
namespace.AccessRights = Grid;
