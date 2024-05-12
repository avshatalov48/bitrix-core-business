import { Dom, Tag, Loc, Event, Type } from 'main.core';
import { Icon, Actions } from 'ui.icon-set.api.core';
import { EventEmitter } from 'main.core.events';
import ListItem from './list-item';
import 'ui.icon-set.actions'

const DEFAULT_LIST_HEIGHT = 300;
const LIST_PADDING_SUM = 45;

type ListProps = {
	userInfo: any,
	onLinkListClose: func,
	sortJointLinksByFrequentUse: boolean,
};

export default class List
{
	#props: ListProps;
	#layout: {
		wrapper: HTMLElement,
		title: HTMLElement,
		sortingButton: HTMLElement,
		sortingButtonText: HTMLElement,
		backButton: HTMLElement,
		list: HTMLElement,
		emptyState: HTMLElement,
	};
	#linkList: any;
	#popupOpenState = false;
	#sortByFrequentUse: boolean;
	#pathToUser: string;

	constructor(props: ListProps)
	{
		this.#props = props;
		this.#layout = {};
		this.#linkList = null;
		this.#pathToUser = null;
		this.#sortByFrequentUse = props.sortJointLinksByFrequentUse;
		this.getLinkListInfo();

		this.setListItemPopupState = this.setListItemPopupState.bind(this);

		this.eventSubscribe();
	}

	eventSubscribe()
	{
		EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', (event) => {
			this.onJointLinkCopy(event);
		});
		EventEmitter.subscribe('CalendarSharing:onJointLinkDelete', (event) => {
			this.onJointLinkDelete(event);
		});
	}

	getLinkListInfo(): void
	{
		BX.ajax.runAction('calendar.api.sharingajax.getAllUserLink').then((response) => {
			if (response && response.data)
			{
				this.#linkList = response.data.userLinks;
				this.#pathToUser = response.data.pathToUser;

				this.updateLinkList();

				if (this.isListEmpty())
				{
					this.hideSortingButton();

					return;
				}

				if (this.#linkList)
				{
					this.showSortingButton();
				}
			}
		});
	}

	render(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper =  Tag.render`
				<div class="calendar-sharing__dialog-link-list-wrapper">
					${this.getTitleNode()}
					${this.getListNode()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	getTitleNode(): HTMLElement
	{
		if (!this.#layout.title)
		{
			this.#layout.title =  Tag.render`
				<div class="calendar-sharing__dialog-link-list-title-wrapper">
					<div class="calendar-sharing__dialog-link-list-title">
						${this.getChevronBackIcon()}
						<div class="calendar-sharing__dialog-link-list-title-text">
							${Loc.getMessage('CALENDAR_SHARING_LINK_LIST_TITLE')}
						</div>
					</div>
					${this.getSortingButton()}
				</div>
			`;
		}

		return this.#layout.title;
	}

	getChevronBackIcon(): HTMLElement
	{
		if (!this.#layout.backButton)
		{
			const icon = new Icon({
				icon: Actions.CHEVRON_LEFT,
				size: 24,
			});

			this.#layout.backButton = Tag.render`
				<div class="calendar-sharing__dialog-link-list-back-button">
					${icon.render()}
				</div>
			`;

			Event.bind(this.#layout.backButton, 'click', this.close.bind(this));
		}

		return this.#layout.backButton;
	}

	getSortingButton(): HTMLElement
	{
		if (!this.#layout.sortingButton)
		{
			const icon = new Icon({
				icon: Actions.SORT,
				size: 14,
				color: '#2066b0',
			});

			this.#layout.sortingButton = Tag.render`
				<div class="calendar-sharing__dialog-link-list-sorting-button">
					${icon.render()}
					${this.getSortingButtonText()}
				</div>
			`;

			Event.bind(this.#layout.sortingButton, 'click', this.changeListSort.bind(this));
		}

		return this.#layout.sortingButton;
	}

	getSortingButtonText(): HTMLElement
	{
		if (!this.#layout.sortingButtonText)
		{
			this.#layout.sortingButtonText = Tag.render`
				<div class="calendar-sharing__dialog-link-list-sorting-button-text">
					${this.#getSortingName()}
				</div>
			`;
		}

		return this.#layout.sortingButtonText;
	}

	getListNode(): HTMLElement
	{
		if (!this.#layout.list)
		{
			this.#layout.list = Tag.render`
				<div class="calendar-sharing__dialog-link-list-container">
					${this.getListItemsNode()}
				</div>
			`;
		}

		return this.#layout.list;
	}

	getListItemsNode(): HTMLElement
	{
		if (this.isListEmpty())
		{
			return this.getEmptyStateNode();
		}

		const linkListItems = this.getListItems();

		return Tag.render`
			<div class="calendar-sharing__dialog-link-list">
				${linkListItems.map((listItem) => listItem.render())}
			</div>
		`;
	}

	getEmptyStateNode(): HTMLElement
	{
		if (!this.#layout.emptyState)
		{
			this.#layout.emptyState = Tag.render`
				<div class="calendar-sharing__dialog-link-list-empty-state-wrapper">
					<div class="calendar-sharing__dialog-link-list-empty-state-icon"></div>
					<div class="calendar-sharing__dialog-link-list-empty-state-text">${Loc.getMessage('CALENDAR_SHARING_LIST_EMPTY_TITLE')}</div>
				</div>
			`;
		}

		return this.#layout.emptyState;
	}

	getListItems(): any
	{
		if (this.#sortByFrequentUse)
		{
			return this.getSortedByFrequentUseListItems();
		}

		return this.getSortedByDateListItems();
	}

	getSortedByFrequentUseListItems(): any
	{
		return Object.values(this.#linkList).sort((a, b) => {
			if (a.frequentUse > b.frequentUse)
			{
				return -1;
			}
			if (a.frequentUse < b.frequentUse)
			{
				return 1;
			}
			if (a.id > b.id)
			{
				return -1;
			}
			if (a.id < b.id)
			{
				return 1;
			}

			return 0;
		}).map((item) => new ListItem({
			...item,
			userInfo: this.#props.userInfo,
			pathToUser: this.#pathToUser,
			setListItemPopupState: this.setListItemPopupState,
		}));
	}

	getSortedByDateListItems(): any
	{
		return Object.keys(this.#linkList).sort((a, b) => b - a).map((index) => {
			return new ListItem({
				...this.#linkList[index],
				userInfo: this.#props.userInfo,
				pathToUser: this.#pathToUser,
				setListItemPopupState: this.setListItemPopupState,
			});
		});
	}

	show(maxListHeight): void
	{
		if (this.#layout.list && maxListHeight)
		{
			Dom.style(this.#layout.list, 'max-height', `${maxListHeight - LIST_PADDING_SUM}px`);
		}

		if (this.#layout.wrapper)
		{
			Dom.addClass(this.#layout.wrapper, '--show');
		}
	}

	close(): void
	{
		if (this.#layout.list)
		{
			Dom.style(this.#layout.list, 'max-height', `${DEFAULT_LIST_HEIGHT}px`);
		}

		if (this.#layout.wrapper)
		{
			Dom.removeClass(this.#layout.wrapper, '--show');
		}

		if (this.#props.onLinkListClose)
		{
			this.#props.onLinkListClose();
		}
	}

	updateLinkList(): void
	{
		if (this.#layout.list)
		{
			Dom.clean(this.getListNode());

			const listItems = this.getListItemsNode();
			Dom.append(listItems, this.#layout.list);
		}
	}

	changeListSort(): void
	{
		this.#sortByFrequentUse = !this.#sortByFrequentUse;
		BX.ajax.runAction('calendar.api.sharingajax.setSortJointLinksByFrequentUse', {
			data: {
				sortByFrequentUse: this.#sortByFrequentUse ? 'Y' : 'N',
			},
		});

		const sortName = this.#getSortingName();

		if (this.#layout.sortingButtonText)
		{
			Dom.adjust(this.#layout.sortingButtonText, {
				text: sortName,
			});
		}

		this.updateLinkList();
	}

	#getSortingName(): string
	{
		return this.#sortByFrequentUse
			? Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_RECENT')
			: Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_DATE');
	}

	setListItemPopupState(state): void
	{
		this.#popupOpenState = state;
	}

	isOpenListItemPopup(): boolean
	{
		return this.#popupOpenState;
	}

	onJointLinkCopy(event): void
	{
		const id = event.data.id;
		const hash = event.data.hash;

		setTimeout(() => {
			if (this.#linkList[id])
			{
				this.#linkList[id].frequentUse = this.#linkList[id].frequentUse + 1;
				this.updateLinkList();
			}
		}, 1000);

		BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
			data: {
				hash,
			},
		});
	}

	onJointLinkDelete(event): void
	{
		const id = event.data.id;

		if (this.#linkList[id])
		{
			delete this.#linkList[id];
		}

		if (this.isListEmpty())
		{
			this.updateLinkList();
			this.hideSortingButton();
		}
	}

	isListEmpty()
	{
		return Type.isNil(this.#linkList)
			|| (Type.isArray(this.#linkList) && !Type.isArrayFilled(this.#linkList))
			|| (Type.isObject(this.#linkList) && !Object.keys(this.#linkList).length)
		;
	}

	hideSortingButton()
	{
		if (this.#layout.sortingButton)
		{
			Dom.addClass(this.#layout.sortingButton, '--hide');
		}
	}

	showSortingButton()
	{
		if (this.#layout.sortingButton)
		{
			Dom.removeClass(this.#layout.sortingButton, '--hide');
		}
	}
}