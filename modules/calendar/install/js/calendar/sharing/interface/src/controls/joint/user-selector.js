import { Text, Dom, Loc, Tag, Event } from 'main.core';
import { Dialog } from 'ui.entity-selector';
import HintInfo from './hint-info';

export default class UserSelector
{
	#layout: {
		wrapper: HTMLElement,
		title: HTMLElement,
		chevron: HTMLElement,
		userSelectorWrapper: HTMLElement,
		userSelector: HTMLElement,
		hint: HTMLElement,
	};
	#userSelectorDialog: Dialog;
	#selectedEntityList: any;
	#selectedEntityNodeList: any;
	#defaultUserEntity: any;
	#isOpened: boolean;
	#onMembersAdded: function;

	constructor(props = {})
	{
		this.#layout = {};
		this.#userSelectorDialog = null;
		this.#selectedEntityList = {};
		this.#selectedEntityNodeList = {};
		this.#defaultUserEntity = props.userInfo || {};
		this.#isOpened = false;
		this.#onMembersAdded = props.onMembersAdded;

		this.openEntitySelector = this.openEntitySelector.bind(this);
	}

	render(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__user-selector-main">
					<div class="calendar-sharing__user-selector-title">
						${this.renderTitle()}
					</div>
					${this.renderUserSelectorWrapper()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	renderTitle(): HTMLElement
	{
		if (!this.#layout.title)
		{
			this.#layout.title = Tag.render`
				<div class="calendar-sharing__user-selector-title-text">
					${Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_TITLE_V2')}
				</div>
			`;

			const infoNotify = this.#layout.title.querySelector('[ data-role="calendar-sharing_popup-joint-slots"]');

			if (infoNotify)
			{
				let hintInfo;
				let timer;

				Event.bind(infoNotify, 'mouseenter', () => {
					timer = setTimeout(() => {
						if (!hintInfo)
						{
							hintInfo = new HintInfo({
								bindElement: infoNotify,
							});
						}

						hintInfo.show();
					}, 1000);
				});

				Event.bind(infoNotify, 'mouseleave', () => {
					clearTimeout(timer);

					if (hintInfo)
					{
						hintInfo.close();
					}
				});
			}
		}

		return this.#layout.title;
	}

	renderUserSelectorWrapper(): HTMLElement
	{
		if (!this.#layout.userSelectorWrapper)
		{
			this.#layout.userSelectorWrapper = Tag.render`
				<div class="calendar-sharing__user-selector-wrapper">
					${this.renderUserSelector()}
					<div class="calendar-sharing__user-selector-add">
				</div>
			`;

			Event.bind(this.#layout.userSelectorWrapper, 'click', this.openEntitySelector);
		}

		return this.#layout.userSelectorWrapper;
	}

	renderUserSelector(): HTMLElement
	{
		if (!this.#layout.userSelector)
		{
			const entityNode = this.getDefaultEntityNode();

			this.#layout.userSelector = Tag.render`
				<div class="calendar-sharing__user-selector-container" data-id="calendar-sharing-members">
					${entityNode}
				</div>
			`;
		}

		return this.#layout.userSelector;
	}

	getDefaultEntityNode(): HTMLElement
	{
		const entityNode = this.renderUserEntity(this.#defaultUserEntity);
		const key = this.getEntityKey(this.#defaultUserEntity.id);
		this.#selectedEntityList[key] = this.#defaultUserEntity;
		this.#selectedEntityNodeList[key] = entityNode;

		return entityNode;
	}

	renderUserEntity(entity): HTMLElement
	{
		if (this.hasAvatar(entity.avatar))
		{
			return Tag.render`
				<div class="calendar-sharing__user-selector-entity-container">
					<img class="calendar-sharing__user-selector-entity" title="${Text.encode(entity.name)}" src="${entity.avatar}" alt="">
				</div>
			`;
		}

		return Tag.render`
			<div class="ui-icon ui-icon-common-user calendar-sharing__user-selector-entity" title="${Text.encode(entity?.name)}"><i></i></div>
		`;
	}

	hasAvatar(avatar): boolean
	{
		return avatar && avatar !== '/bitrix/images/1.gif';
	}

	openEntitySelector(): void
	{
		if (!this.#layout.userSelector)
		{
			return;
		}

		const preselectedItem = ['user', this.#defaultUserEntity.id];

		if (!this.#userSelectorDialog)
		{
			this.#userSelectorDialog = new Dialog({
				width: 340,
				targetNode: this.#layout.userSelector,
				context: 'CALENDAR_SHARING',
				preselectedItems: [preselectedItem],
				enableSearch: true,
				zIndex: 4200,
				events: {
					'Item:onSelect': (event) => {
						this.onUserSelectorSelect(event);
					},
					'Item:onDeselect': (event) => {
						this.onUserSelectorDeselect(event);
					},
					'onHide': () => {
						if (this.hasChanges())
						{
							this.#onMembersAdded();
						}
					},
				},
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: true,
							emailUsers: false,
							inviteEmployeeLink: false,
							inviteGuestLink: false,
							analyticsSource: 'calendar',
						},
						filters: [
							{
								id: 'calendar.jointSharingFilter',
							},
						],
					},
				],
			});
		}

		this.#userSelectorDialog.show();
	}

	isUserSelectorDialogOpened(): boolean
	{
		if (this.#userSelectorDialog)
		{
			return this.#userSelectorDialog.isOpen();
		}

		return false;
	}

	onUserSelectorSelect(event): void
	{
		const item = event.data.item;
		const entity = {
			id: item.id,
			avatar: item.avatar,
			name: `${item.customData.get('name')} ${item.customData.get('lastName') ?? ''}`.trim(),
		};
		const entityNode = this.renderUserEntity(entity);

		if (this.#layout.userSelector)
		{
			Dom.append(entityNode, this.#layout.userSelector);
		}

		const key = this.getEntityKey(entity.id);

		this.#selectedEntityList[key] = entity;
		this.#selectedEntityNodeList[key] = entityNode;

	}

	onUserSelectorDeselect(event): void
	{
		const item = event.data.item;
		const key = this.getEntityKey(item.id);

		const entityNode = this.#selectedEntityNodeList[key];

		if (entityNode)
		{
			Dom.remove(entityNode);
			delete this.#selectedEntityList[key];
			delete this.#selectedEntityNodeList[key];
		}
	}

	clearSelectedUsers(): void
	{
		if (this.#layout.userSelector)
		{
			Dom.clean(this.#layout.userSelector);
			this.#selectedEntityList = {};
			this.#selectedEntityNodeList = {};

			const entityNode = this.getDefaultEntityNode();
			Dom.append(entityNode, this.#layout.userSelector);
		}

		if (this.#userSelectorDialog)
		{
			this.#userSelectorDialog.destroy();
			this.#userSelectorDialog = null;
		}
	}

	hasChanges(): boolean
	{
		return this.getPeopleCount() > 1;
	}

	getPeopleCount(): any
	{
		return Object.keys(this.#selectedEntityList).length;
	}

	getSelectedUserIdList(): any
	{
		const result = [];

		Object.values(this.#selectedEntityList).forEach((entity) => {
			result.push(entity.id);
		});

		return result;
	}

	getEntityKey(id)
	{
		return `user-${id}`;
	}
}