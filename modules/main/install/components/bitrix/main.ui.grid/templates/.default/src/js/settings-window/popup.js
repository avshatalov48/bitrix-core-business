import { ajax as Ajax, Dom, Event, Reflection, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Loader } from 'main.loader';
import { Popup as MainPopup } from 'main.popup';
import type { PopupInterface } from './popup-interface';

const namespace = Reflection.namespace('BX.Grid.SettingsWindow');

class Popup implements PopupInterface
{
	options: Object = {};
	grid: BX.Main.grid;
	parent: BX.Grid.SettingsWindow;

	items: ?[] = null;
	popupItems: ?HTMLCollection = null;
	popup: MainPopup = null;
	filterSectionsSearchInput: ?HTMLElement = null;
	filterSections: ?HTMLCollection = null;
	allColumns: Object<string, string> = null;
	applyButton: HTMLElement = null;
	resetButton: HTMLElement = null;
	cancelButton: HTMLElement = null;
	selectAllButton: HTMLElement = null;
	unselectAllButton: HTMLElement = null;

	constructor(options: Object)
	{
		this.options = options;

		this.grid = options.grid;
		this.parent = options.parent;
	}

	getPopup(): MainPopup
	{
		if (!this.popup)
		{
			this.createPopup();
		}

		return this.popup;
	}

	createPopup(): void
	{
		if (this.popup)
		{
			return;
		}

		const leftIndentFromWindow = 20;
		const rightIndentFromWindow = 20;
		const popupWidth = document.body.offsetWidth > 1000
			? 1000
			: document.body.offsetWidth - leftIndentFromWindow - rightIndentFromWindow
		;

		const { title: titleBar } = this.options;

		this.popup = new MainPopup(
			this.getPopupId(),
			null,
			{
				titleBar,
				autoHide: false,
				overlay: 0.6,
				width: popupWidth,
				closeIcon: true,
				closeByEsc: true,
				contentNoPaddings: true,
				content: this.getSourceContent(),
				events: {
					onPopupClose: this.onPopupClose.bind(this),
				},
			},
		);

		this.getItems().forEach((item) => {
			Event.bind(item.getNode(), 'click', this.onItemClick.bind(this));
			Event.bind(item.getNode(), 'animationend', this.onAnimationEnd.bind(this, item.getNode()));
		});

		Event.bind(this.getResetButton(), 'click', this.onResetButtonClick.bind(this));
		Event.bind(this.getApplyButton(), 'click', this.onApplyButtonClick.bind(this));
		Event.bind(this.getCancelButton(), 'click', this.popup.close.bind(this.popup));
		Event.bind(this.getSelectAllButton(), 'click', this.onSelectAll.bind(this));
		Event.bind(this.getUnselectAllButton(), 'click', this.onUnselectAll.bind(this));

		if (
			Type.isObjectLike(this.grid.arParams.COLUMNS_ALL_WITH_SECTIONS)
			&& Object.keys(this.grid.arParams.COLUMNS_ALL_WITH_SECTIONS).length > 0
		)
		{
			this.prepareFilterSections();
		}

		if (this.grid.arParams.ENABLE_FIELDS_SEARCH)
		{
			this.prepareFilterSectionsSearchInput();
		}
	}

	show(): void
	{
		this.popup.show();
	}

	close(): void
	{
		this.onPopupClose();
	}

	onPopupClose(): void
	{
		this.emitSaveEvent();

		this.restoreLastColumns();
		this.disableAllColumnsLabelEdit();
		this.adjustActionButtonsState();
	}

	emitSaveEvent(): void
	{
		EventEmitter.emit(window, 'BX.Grid.SettingsWindow:close', [this, this.parent]);
	}

	restoreLastColumns(): void
	{
		this.getItems().forEach((current) => current.restoreState());
	}

	disableAllColumnsLabelEdit(): void
	{
		this.getItems().forEach((column) => column.disableEdit());
	}

	getPopupId(): string
	{
		return `${this.grid.getContainerId()}-grid-settings-window`;
	}

	getSourceContent(): ?HTMLElement
	{
		const classSettingsWindow = this.grid.settings.get('classSettingsWindow');
		const sourceContent = this.grid.getContainer().querySelector(`.${classSettingsWindow}`);

		if (!this.options.isUseLazyLoadColumns)
		{
			return sourceContent;
		}

		const contentList = sourceContent.querySelector('.main-grid-settings-window-list');
		contentList.innerHTML = '';

		const loader = new Loader({
			target: contentList,
		});

		void loader.show();

		this
			.fetchColumns()
			.then((response) => {
				response.forEach((columnOptions) => {
					this.prepareColumnOptions(columnOptions);
					Dom.append(this.createColumnElement(columnOptions), contentList);
				});

				this.hideAndDestroyLoader();

				this.reset();

				this.getItems().forEach((item) => {
					Event.bind(item.getNode(), 'click', this.onItemClick);
				});

				const fixedFooter = Tag.render`
					<div class="main-grid-popup-window-buttons-wrapper"></div>
				`;

				Dom.append(sourceContent.querySelector('.popup-window-buttons'), fixedFooter);

				requestAnimationFrame(() => {
					Dom.style(
						fixedFooter,
						{
							width: `${this.getPopupContainer().clientWidth}px`,
						},
					);

					Dom.append(fixedFooter, this.getPopupContainer());
				});
			})
			.catch((err) => {
				console.error(err);
			})
		;

		return sourceContent;
	}

	fetchColumns(): BX.Promise
	{
		// @todo replace to vanilla Promise
		const promise = new BX.Promise();

		const lazyLoadParams = this.grid.getParam('LAZY_LOAD');
		const gridId = this.grid.getId();

		if (Type.isPlainObject(lazyLoadParams))
		{
			const { controller, GET_LIST: url } = lazyLoadParams;

			if (Type.isNil(controller))
			{
				ajax({
					url,
					method: 'GET',
					dataType: 'json',
					onsuccess: promise.fulfill.bind(promise),
				});
			}
			else
			{
				Ajax.runAction(
					`${controller}.getColumnsList`,
					{
						method: 'GET',
						data: {
							gridId,
						},
					},
				).then(promise.fulfill.bind(promise));
			}
		}

		return promise;
	}

	prepareColumnOptions(options): void
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		const customNames = this.grid.getUserOptions().getCurrentOptions().custom_names;
		if (Type.isPlainObject(customNames) && options.id in customNames)
		{
			// eslint-disable-next-line no-param-reassign
			options.name = customNames[options.id];
		}

		if (this.grid.getColumnHeaderCellByName(options.id))
		{
			// eslint-disable-next-line no-param-reassign
			options.selected = true;
		}
	}

	createColumnElement(options): HTMLElement
	{
		const checkboxId = `${options.id}-checkbox`;
		const checkedClass = options.selected ? ' checked' : '';

		return Tag.render`
			<div data-name=${options.id} class='main-grid-settings-window-list-item'>
				<input
					id='${checkboxId}'
					type='checkbox'
					class='main-grid-settings-window-list-item-checkbox${checkedClass})'
				>
				<label
					for='${checkboxId}'
					class='main-grid-settings-window-list-item-label'
				>
					${options.name}
				</label>
				<span class='main-grid-settings-window-list-item-edit-button'></span>
			</div>
		`;
	}

	hideAndDestroyLoader(loader: Loader): void
	{
		void loader.hide().then(() => loader.destroy());
	}

	onItemClick(): void
	{
		this.adjustActionButtonsState();
	}

	onAnimationEnd(node: HTMLElement): void
	{
		const display = (
			Dom.hasClass(node, this.grid.settings.get('classSettingsWindowSearchSectionItemHidden'))
				? 'none'
				: 'inline-block'
		);

		Dom.style(node, { display });
	}

	adjustActionButtonsState(): void
	{
		if (this.getSelectedColumns().length > 0)
		{
			this.enableActions();

			return;
		}

		this.disableActions();
	}

	getSelectedColumns(): []
	{
		const columns = [];

		this.getItems().forEach((column) => {
			if (column.isSelected())
			{
				columns.push(column.getId());
			}
		});

		return columns;
	}

	getItems(): ?BX.Grid.SettingsWindow.Column[]
	{
		if (this.items === null)
		{
			const { grid } = this;
			const items = this.getPopupItems();
			this.items = [...items].map((current) => {
				return new BX.Grid.SettingsWindow.Column(grid, current);
			});
		}

		return this.items;
	}

	getPopupItems(): HTMLCollection
	{
		if (!this.popupItems)
		{
			const popupContainer = this.getPopupContentContainer();
			const selector = this.grid.settings.get('classSettingsWindowColumn');
			this.popupItems = popupContainer.getElementsByClassName(selector);
		}

		return this.popupItems;
	}

	enableActions(): void
	{
		const applyButton = this.getApplyButton();

		if (applyButton)
		{
			Dom.removeClass(applyButton, this.grid.settings.get('classDisable'));
		}
	}

	prepareFilterSectionsSearchInput(): void
	{
		const input = this.getFilterSectionsSearchInput();

		Event.bind(input, 'input', this.onFilterSectionSearchInput.bind(this));
		Event.bind(input.previousElementSibling, 'click', this.onFilterSectionSearchInputClear.bind(this));
	}

	getFilterSectionsSearchInput(): HTMLElement | null
	{
		if (!this.filterSectionsSearchInput)
		{
			const selector = this.grid.settings.get('classSettingsWindowSearchSectionInput');
			this.filterSectionsSearchInput = this.getPopupContentContainer().querySelector(`.${selector}`);
		}

		return this.filterSectionsSearchInput;
	}

	onFilterSectionSearchInput(): void
	{
		let search = this.filterSectionsSearchInput.value;
		if (search.length > 0)
		{
			search = search.toLowerCase();
		}

		this.items.forEach((item) => {
			const title = item.lastTitle.toLowerCase();
			const node = item.getNode();

			if (search.length > 0 && !title.includes(search))
			{
				Dom.removeClass(
					node,
					this.grid.settings.get('classSettingsWindowSearchSectionItemVisible'),
				);
				Dom.addClass(
					node,
					this.grid.settings.get('classSettingsWindowSearchSectionItemHidden'),
				);
			}
			else
			{
				Dom.removeClass(
					node,
					this.grid.settings.get('classSettingsWindowSearchSectionItemHidden'),
				);
				Dom.addClass(
					node,
					this.grid.settings.get('classSettingsWindowSearchSectionItemVisible'),
				);

				Dom.style(node, { display: 'inline-block' });
			}
		});
	}

	onFilterSectionSearchInputClear(): void
	{
		this.filterSectionsSearchInput.value = '';
		this.onFilterSectionSearchInput();
	}

	getResetButton(): HTMLElement
	{
		if (this.resetButton === null)
		{
			this.resetButton = document.getElementById(this.getResetButtonId());
		}

		return this.resetButton;
	}

	getResetButtonId(): string
	{
		return `${this.grid.getContainerId()}-grid-settings-reset-button`;
	}

	onResetButtonClick(): void
	{
		const params = {
			CONFIRM: true,
			CONFIRM_MESSAGE: this.grid.arParams.CONFIRM_RESET_MESSAGE,
		};

		this.grid.confirmDialog(
			params,
			() => {
				this.enableWait(this.getApplyButton());

				this.grid.getUserOptions().reset(
					this.isForAll(),
					() => {
						this.grid.reloadTable(null, null, () => {
							this.restoreColumns();
							this.disableWait(this.getApplyButton());
							this.popup.close();
						});
					},
				);
			},
		);
	}

	restoreColumns(): void
	{
		this.getItems().forEach((column) => column.restore());

		this.sortItems();
		this.reset();
	}

	sortItems(): void
	{
		const showedColumns = this.getShowedColumns();
		const allColumns = {};

		this.getAllColumns().forEach((name) => {
			allColumns[name] = name;
		});

		let counter = 0;
		Object.keys(allColumns).forEach((name) => {
			if (this.isShowedColumn(name))
			{
				allColumns[name] = showedColumns[counter];
				counter++;
			}

			const current = this.getColumnByName(allColumns[name]);
			if (current)
			{
				Dom.append(current, current.parentNode);
			}
		});
	}

	getShowedColumns(): string[]
	{
		return this.parent.gridSettings.getSelectedColumns();
	}

	getColumnByName(name)
	{
		return BX.Grid.Utils.getBySelector(
			this.getPopupContainer(),
			`.${this.grid.settings.get('classSettingsWindowColumn')}[data-name="${name}"]`,
			true,
		);
	}

	isShowedColumn(columnName): boolean
	{
		return this.getSelectedColumns().includes(columnName);
	}

	getAllColumns()
	{
		if (!this.allColumns)
		{
			this.allColumns = this.getItems().map((column) => column.getId());
		}

		return this.allColumns;
	}

	reset(): void
	{
		this.popupItems = null;
		this.allColumns = null;
		this.items = null;
	}

	getApplyButton(): HTMLElement
	{
		if (this.applyButton === null)
		{
			this.applyButton = document.getElementById(this.getApplyButtonId());
		}

		return this.applyButton;
	}

	getApplyButtonId(): string
	{
		return `${this.grid.getContainerId()}-grid-settings-apply-button`;
	}

	onApplyButtonClick(): void
	{
		const params = {
			CONFIRM: this.isForAll(),
			CONFIRM_MESSAGE: this.grid.getParam('SETTINGS_FOR_ALL_CONFIRM_MESSAGE'),
		};

		this.grid.confirmDialog(
			params,
			() => this.onApplyConfirmDialogButton(),
			() => this.unselectForAllCheckbox(),
		);
	}

	onApplyConfirmDialogButton(): void
	{
		this.enableWait(this.getApplyButton());
		this.saveColumns(
			this.getSelectedColumns(),
			() => {
				this.popup.close();
				this.disableWait(this.getApplyButton());
				this.unselectForAllCheckbox();
			},
		);

		this.emitSaveEvent();
	}

	enableWait(buttonNode): void
	{
		Dom.addClass(buttonNode, 'ui-btn-wait');
		Dom.removeClass(buttonNode, 'popup-window-button');
	}

	disableWait(buttonNode): void
	{
		Dom.removeClass(buttonNode, 'ui-btn-wait');
		Dom.addClass(buttonNode, 'popup-window-button');
	}

	saveColumns(columns, callback): void
	{
		const options = this.grid.getUserOptions();
		const columnNames = this.getColumnNames();
		const stickyColumns = this.getStickedColumns();
		const batch = [
			{
				action: options.getAction('GRID_SET_COLUMNS'),
				columns: columns.join(','),
			},
			{
				action: options.getAction('SET_CUSTOM_NAMES'),
				custom_names: columnNames,
			},
			{
				action: options.getAction('GRID_SET_STICKED_COLUMNS'),
				stickedColumns: stickyColumns,
			},
		];

		if (this.isForAll())
		{
			batch.push({
				action: options.getAction('GRID_SAVE_SETTINGS'),
				view_id: 'default',
				set_default_settings: 'Y',
				delete_user_settings: 'Y',
			});
		}

		options.batch(batch, () => this.grid.reloadTable(null, null, callback));

		this.updateColumnsState();
	}

	getColumnNames(): Object<string, string>
	{
		const names = {};
		this.getItems().forEach((column) => {
			if (column.isEdited())
			{
				names[column.getId()] = column.getTitle();
			}
		});

		return names;
	}

	getStickedColumns(): []
	{
		return this.getItems().reduce((accumulator, item) => {
			if (item.isSticked())
			{
				accumulator.push(item.getId());
			}

			return accumulator;
		}, []);
	}

	updateColumnsState(): void
	{
		this.getItems().forEach((current) => current.updateState());
	}

	isForAll(): boolean
	{
		const checkbox = this.getForAllCheckbox();

		return checkbox && Boolean(checkbox.checked);
	}

	unselectForAllCheckbox(): void
	{
		const checkbox = this.getForAllCheckbox();
		if (checkbox)
		{
			checkbox.checked = null;
		}
	}

	getForAllCheckbox(): HTMLElement
	{
		return this.getPopupContainer().querySelector('.main-grid-settings-window-for-all-checkbox');
	}

	getPopupContainer(): HTMLElement
	{
		return this.getPopup().getPopupContainer();
	}

	getPopupContentContainer(): HTMLElement
	{
		return this.getPopup().getContentContainer();
	}

	getCancelButton(): HTMLElement
	{
		if (this.cancelButton === null)
		{
			this.cancelButton = document.getElementById(this.getCancelButtonId());
		}

		return this.cancelButton;
	}

	getCancelButtonId(): string
	{
		return `${this.grid.getContainerId()}-grid-settings-cancel-button`;
	}

	getSelectAllButton(): HTMLElement
	{
		if (!this.selectAllButton)
		{
			const selector = this.grid.settings.get('classSettingsWindowSelectAll');
			this.selectAllButton = this.getPopupContentContainer().querySelector(`.${selector}`);
		}

		return this.selectAllButton;
	}

	onSelectAll(): void
	{
		this.selectAll();
		this.enableActions();
	}

	selectAll(): void
	{
		this.getItems().forEach((column) => column.select());
	}

	getUnselectAllButton(): HTMLElement | null
	{
		if (!this.unselectAllButton)
		{
			const selector = this.grid.settings.get('classSettingsWindowUnselectAll');
			this.unselectAllButton = this.getPopupContentContainer().querySelector(`.${selector}`);
		}

		return this.unselectAllButton;
	}

	onUnselectAll(): void
	{
		this.unselectAll();
		this.disableActions();
	}

	disableActions(): void
	{
		const applyButton = this.getApplyButton();

		if (applyButton)
		{
			Dom.addClass(applyButton, this.grid.settings.get('classDisable'));
		}
	}

	unselectAll(): void
	{
		this.getItems().forEach((column) => column.unselect());
	}

	prepareFilterSections(): void
	{
		const filterSections = this.getFilterSections();
		for (const item of filterSections)
		{
			Event.bind(item, 'click', this.onFilterSectionClick.bind(this, item));
		}
	}

	getFilterSections(): HTMLCollection
	{
		if (!this.filterSections)
		{
			const selector = this.grid.settings.get('classSettingsWindowSearchSectionsWrapper');
			const wrapper = this.getPopupContentContainer().querySelector(`.${selector}`);

			this.filterSections = (wrapper.children ?? new HTMLCollection());
		}

		return this.filterSections;
	}

	onFilterSectionClick(item: HTMLElement): void
	{
		const activeClass = this.grid.settings.get('classSettingsWindowSearchActiveSectionIcon');
		const sectionId = item.dataset?.uiGridFilterSectionButton;
		const section = document.querySelector(`[data-ui-grid-filter-section='${sectionId}']`);

		if (Dom.hasClass(item.firstChild, activeClass))
		{
			Dom.removeClass(item.firstChild, activeClass);
			Dom.hide(section);
		}
		else
		{
			Dom.addClass(item.firstChild, activeClass);
			Dom.show(section);
		}
	}

	select(id: string, value: boolean = true): void
	{
		const column = this.getItems().find((item) => item.getId() === id);

		if (value)
		{
			column?.select();
		}
		else
		{
			column?.unselect();
		}
	}

	saveColumnsByNames(columns: string[], callback: Function): void
	{
		this.saveColumns(columns, callback);
	}
}

namespace.Popup = Popup;
