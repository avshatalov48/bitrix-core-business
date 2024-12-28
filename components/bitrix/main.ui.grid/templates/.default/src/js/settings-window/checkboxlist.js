import { Reflection, Text, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import {
	CheckboxList as UiCheckboxList,
	CheckboxListCategory,
	CheckboxListOption,
	CheckboxListSection,
} from 'ui.dialogs.checkbox-list';
import type { PopupInterface } from './popup-interface';

const namespace = Reflection.namespace('BX.Grid.SettingsWindow');

type CheckboxListOptions = {
	sections?: Object[],
	categories?: Object[],
	columnsWithSections?: Object[];
	columns?: Object[];
	checked?: string[];
}

type CheckboxListParams = {
	grid: BX.Main.grid;
	parent: Object;
	title: string;
	isUseLazyLoadColumns: boolean;
}

const SAVE_FOR_ALL = 'forAll';
const SAVE_FOR_ME = 'forMe';

class CheckboxList implements PopupInterface
{
	params: CheckboxListParams = {};
	grid: BX.Main.grid;
	parent: BX.Grid.SettingsWindow;
	useSearch: boolean;
	useSectioning: boolean;
	options: CheckboxListOptions = {};

	stickyColumns: Set<string> = new Set();
	popup: UiCheckboxList = null;
	popupItems: HTMLCollection = null;

	constructor(params: CheckboxListParams)
	{
		this.params = params;

		this.grid = params.grid;
		this.parent = params.parent;
		this.options = this.grid.arParams.CHECKBOX_LIST_OPTIONS;

		this.useSearch = Boolean(this.grid.arParams.ENABLE_FIELDS_SEARCH);
		this.useSectioning = Type.isArrayFilled(this.options.sections);
		this.isForAllValue = false;
	}

	getPopup(): UiCheckboxList
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

		const {
			useSearch,
			useSectioning,
			params: { title, placeholder, emptyStateTitle, emptyStateDescription, allSectionsDisabledTitle },
		} = this;

		const context = {
			parentType: 'grid',
		};

		this.popup = new UiCheckboxList({
			context,
			popupOptions: {
				width: 1100,
			},
			columnCount: 4,
			lang: {
				title,
				placeholder,
				emptyStateTitle,
				emptyStateDescription,
				allSectionsDisabledTitle,
			},
			sections: this.getSections(),
			categories: this.getCategories(),
			options: this.getOptions(),
			events: {
				onApply: (event) => this.onApply(event),
				onDefault: (event) => this.onDefault(event),
			},
			params: {
				useSearch,
				useSectioning,
				destroyPopupAfterClose: false,
				closeAfterApply: false,
				isEditableOptionsTitle: true,
			},
			customFooterElements: this.getCustomFooterElements(),
		});
	}

	getSections(): CheckboxListSection[]
	{
		const sections = this.options.sections ?? [];

		const result = [];
		sections.forEach((section) => {
			const { id, name, selected } = section;

			result.push({
				key: id,
				title: name,
				value: selected,
			});
		});

		return result;
	}

	getCategories(): CheckboxListCategory[]
	{
		const categories = this.options.categories ?? [];

		const result = [];

		if (categories.length === 0)
		{
			this.getSections().forEach((section) => {
				const { key, title } = section;

				result.push({
					key,
					title,
					sectionKey: key,
				});
			});

			return result;
		}

		categories.forEach((category) => {
			const { title, sectionKey, key } = category;

			result.push({
				title,
				sectionKey,
				key,
			});
		});

		return result;
	}

	getOptions(): CheckboxListOption[]
	{
		const options = this.options;
		const columns = options.columns ?? [];
		const columnsWithSections = options.columnsWithSections ?? [];
		const result = [];
		const customNames = this.grid.getUserOptions().getCurrentOptions()?.custom_names ?? {};

		if (this.useSectioning)
		{
			for (const sectionName in columnsWithSections)
			{
				columnsWithSections[sectionName].forEach((column) => {
					const { id, default: defaultValue } = column;
					let { name: title } = column;
					if (Type.isPlainObject(customNames) && Object.hasOwn(customNames, 'id'))
					{
						title = customNames[id];
					}

					result.push({
						title: Text.decode(title),
						value: this.isChecked(id),
						categoryKey: sectionName,
						defaultValue,
						id,
					});

					this.prepareColumnParams(column);
				});
			}

			return result;
		}

		columns.forEach((column) => {
			const {
				id,
				name: title,
				default: defaultValue,
			} = column;

			result.push({
				title: Text.decode(title),
				value: this.isChecked(id),
				defaultValue,
				id,
			});

			this.prepareColumnParams(column);
		});

		return result;
	}

	isChecked(fieldName: string): boolean
	{
		const checked = this.options.checked ?? [];

		return checked.includes(fieldName);
	}

	prepareColumnParams(column: Object): void
	{
		const { sticked, id } = column;

		if (sticked)
		{
			this.stickyColumns.add(id);
		}
	}

	getCustomFooterElements(): Object[]
	{
		if (this.isAdmin())
		{
			const { arParams: params, containerId } = this.parent;

			return [
				{
					type: 'textToggle',
					id: `${containerId}-${SAVE_FOR_ALL}`,
					title: params.SETTINGS_FOR_LABEL,
					dataItems: [
						{
							value: SAVE_FOR_ME,
							label: params.SETTINGS_FOR_FOR_ME_LABEL,
						},
						{
							value: SAVE_FOR_ALL,
							label: params.SETTINGS_FOR_FOR_ALL_LABEL,
						},
					],
					// eslint-disable-next-line no-return-assign
					onClick: (value: boolean) => {
						this.isForAllValue = (value === SAVE_FOR_ALL);
					},
				},
			];
		}

		return [];
	}

	show(): void
	{
		this.popup.show();
	}

	getStickedColumns(): string[]
	{
		const {
			ALLOW_STICKED_COLUMNS: isStickyColumnsAllowed,
			HAS_STICKED_COLUMNS: hasStickyColumns,
		} = this.parent.arParams;

		if (isStickyColumnsAllowed && hasStickyColumns)
		{
			return this.stickyColumns.values();
		}

		return [];
	}

	onApply(event): void
	{
		const { fields: columns, data } = event.data;

		if (this.isForAll())
		{
			const params = {
				CONFIRM: true,
				CONFIRM_MESSAGE: this.grid.getParam('SETTINGS_FOR_ALL_CONFIRM_MESSAGE'),
			};

			this.grid.confirmDialog(
				params,
				() => this.saveColumnsAndHidePopup(columns, data),
			);
		}
		else
		{
			this.saveColumnsAndHidePopup(columns, data);
		}
	}

	saveColumnsAndHidePopup(columns, data)
	{
		this.saveColumns(columns, data);
		this.popup.hide();
	}

	prepareOrderedColumnsList(newColumns: Array<string>): Array<string>
	{
		if (Type.isArray(newColumns))
		{
			const currentOptions: { [key: string]: any } = this.grid.getUserOptions().getCurrentOptions();
			const currentColumns: Array<string> = currentOptions?.columns?.split?.(',');
			if (Type.isArray(currentColumns))
			{
				const filteredColumns: Array<string> = currentColumns.filter((column: string) => {
					return newColumns.includes(column);
				});

				const newAddedColumns: Array<string> = newColumns.filter((column: string) => {
					return !filteredColumns.includes(column);
				});

				return [...filteredColumns, ...newAddedColumns];
			}
		}

		return newColumns;
	}

	saveColumns(columns, data): void
	{
		const options = this.grid.getUserOptions();
		const columnNames = this.getColumnNames(data);
		const stickyColumns = this.getStickedColumns();
		const orderedColumns: Array<string> = this.prepareOrderedColumnsList(columns);

		const batch = [
			{
				action: options.getAction('GRID_SET_COLUMNS'),
				columns: orderedColumns.join(','),
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

		options.batch(batch, () => this.grid.reloadTable());
	}

	getColumnNames(data): {[key: string]: string}[]
	{
		const options = this.options;
		const columns = options.columns ?? [];

		const names = {};
		const { titles } = data;

		if (!Type.isObjectLike(titles))
		{
			return {};
		}

		columns.forEach((column) => {
			const id = column.id;
			if (Type.isStringFilled(titles[id]) && titles[id] !== column.name)
			{
				names[id] = titles[id];
			}
			else if (
				Type.isStringFilled(this.parent.arParams.DEFAULT_COLUMNS[id].name)
				&& this.parent.arParams.DEFAULT_COLUMNS[id].name !== column.name
			)
			{
				names[id] = column.name;
			}
		});

		return names;
	}

	onDefault(event: BaseEvent)
	{
		const params = {
			CONFIRM: true,
			CONFIRM_MESSAGE: this.grid.arParams.CONFIRM_RESET_MESSAGE,
		};

		this.grid.confirmDialog(
			params,
			() => {
				this.grid.getUserOptions().reset(
					this.isForAll(),
					() => {
						this.reset();
						this.grid.reloadTable(null, null, () => {
							this.popup.options
								.forEach((item) => {
									this.grid.gridSettings.select(item.id, item.defaultValue === true);
								})
							;
						});
					},
				);
			},
		);

		event.preventDefault();

		return event;
	}

	sortItems(): void
	{
		// may be implemented
	}

	reset(): void
	{
		this.options.checked = [];

		this.popup.options
			.filter((item) => item.defaultValue)
			.forEach((item) => {
				this.options.checked.push(item.id);
			})
		;

		this.close();
	}

	getSelectedColumns(): ?string[]
	{
		return this.getPopup().getSelectedOptions();
	}

	close(): void
	{
		this.popup?.destroy();
	}

	isForAll(): boolean
	{
		return this.isForAllValue;
	}

	isAdmin(): boolean
	{
		return Boolean(this.parent.arParams.IS_ADMIN ?? false);
	}

	getPopupItems(): HTMLCollection
	{
		return this.options.columns;
	}

	getItems(): []
	{
		return this.getPopup().getOptions();
	}

	select(id: string, value: boolean = true): void
	{
		// to maintain backward compatibility without creating dependencies on ui within the ticket #187991
		// @todo remove later
		if (this.getPopup()?.selectOption?.length === 1 && value === false)
		{
			return;
		}

		this.getPopup().selectOption(id, value);
	}

	saveColumnsByNames(columns: string[], callback: Function): void
	{
		this.getItems()
			.filter((item) => columns.includes(item.id))
			.forEach((item) => this.select(item.id))
		;

		this.getPopup().apply();

		if (Type.isFunction(callback))
		{
			callback();
		}
	}
}

namespace.CheckboxList = CheckboxList;
