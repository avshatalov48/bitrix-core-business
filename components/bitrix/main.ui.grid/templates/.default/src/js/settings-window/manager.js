import { Tag, Text, Type } from 'main.core';

(function() {
	'use strict';

	BX.namespace('BX.Grid.SettingsWindow');

	/**
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.SettingsWindow.Manager = function(parent)
	{
		this.parent = null;

		this.fieldsSettingsInstance = null;
		this.init(parent);
	};

	BX.Grid.SettingsWindow.Manager.prototype = {
		init(parent)
		{
			this.parent = parent;
			BX.bind(this.parent.getContainer(), 'click', BX.proxy(this._onContainerClick, this));
			BX.addCustomEvent(window, 'Grid::columnMoved', BX.proxy(this._onColumnMoved, this));
		},

		destroy()
		{
			BX.unbind(this.parent.getContainer(), 'click', BX.proxy(this._onContainerClick, this));
			BX.removeCustomEvent(window, 'Grid::columnMoved', BX.proxy(this._onColumnMoved, this));
			this.getPopup().close();
		},

		_onContainerClick(event)
		{
			if (BX.hasClass(event.target, this.parent.settings.get('classSettingsButton')))
			{
				this._onSettingsButtonClick(event);
			}
		},

		_onSettingsButtonClick()
		{
			this.getFieldsSettingsInstance().then((fieldsSettingsInstance) => {
				this.fieldsSettingsInstance = fieldsSettingsInstance;
				this.fieldsSettingsInstance.show();

				BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:show', [this.fieldsSettingsInstance]);
			});
		},

		getFieldsSettingsInstance()
		{
			if (this.fieldsSettingsInstance)
			{
				return Promise.resolve(this.fieldsSettingsInstance);
			}

			return new Promise((resolve) => {
				const fieldsSettingsInstance = this.createFieldsSettingsInstance();

				resolve(fieldsSettingsInstance);
			});
		},

		createFieldsSettingsInstance()
		{
			let fieldsSettingsInstance = null;
			const { parent } = this;

			const params = {
				grid: parent,
				parent,
				isUseLazyLoadColumns: this.useLazyLoadColumns(),
				title: this.getPopupTitle(),
				placeholder: parent.getParam('SETTINGS_FIELD_SEARCH_PLACEHOLDER'),
				emptyStateTitle: parent.getParam('SETTINGS_FIELD_SEARCH_EMPTY_STATE_TITLE'),
				emptyStateDescription: parent.getParam('SETTINGS_FIELD_SEARCH_EMPTY_STATE_DESCRIPTION'),
				allSectionsDisabledTitle: parent.getParam('SETTINGS_FIELD_SEARCH_ALL_SECTIONS_DISABLED'),
			};

			if (this.useCheckboxList())
			{
				fieldsSettingsInstance = new BX.Grid.SettingsWindow.CheckboxList(params);
			}
			else
			{
				fieldsSettingsInstance = new BX.Grid.SettingsWindow.Popup(params);
			}

			fieldsSettingsInstance.createPopup();

			BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:init', [fieldsSettingsInstance]);

			return fieldsSettingsInstance;
		},

		useCheckboxList()
		{
			return Boolean(this.parent.getParam('USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'))
				&& Type.isFunction(BX.UI?.CheckboxList)
			;
		},

		useLazyLoadColumns()
		{
			return Boolean(this.parent.getParam('LAZY_LOAD'));
		},

		_onColumnMoved()
		{
			this.sortItems();
			this.reset();
		},

		sortItems()
		{
			this.getPopup().sortItems();
		},

		reset()
		{
			this.getPopup().reset();
		},

		getSelectedColumns()
		{
			return this.getPopup().getSelectedColumns();
		},

		getPopup()
		{
			if (this.fieldsSettingsInstance === null)
			{
				this.fieldsSettingsInstance = this.createFieldsSettingsInstance();
			}

			return this.fieldsSettingsInstance;
		},

		getPopupTitle(): string
		{
			const customSettingsTitle = this.parent.getParam('SETTINGS_WINDOW_TITLE');
			const settingsTitle = this.parent.getParam('SETTINGS_TITLE');
			const tmpDiv = Tag.render`<div></div>`;

			if (Type.isStringFilled(customSettingsTitle))
			{
				tmpDiv.innerHTML = `<span>${settingsTitle} &laquo;${customSettingsTitle}&raquo;</span>`;

				return tmpDiv.firstChild.innerText;
			}

			const gridsCount = BX.Main.gridManager.data.length;

			if (gridsCount === 1)
			{
				const getTitleFromNodeById = (nodeId: string): string => {
					const node = document.getElementById(nodeId);

					return (
						Type.isDomNode(node) && Type.isStringFilled(node.innerText)
							? Text.encode(node.innerText)
							: ''
					);
				};

				const pageTitle = getTitleFromNodeById('pagetitle');
				const pageTitleBtnWrapper = getTitleFromNodeById('pagetitle_btn_wrapper');

				const fullTitle = `${pageTitle} ${pageTitleBtnWrapper}`.trim();

				tmpDiv.innerHTML = `<span>${settingsTitle} &laquo;${fullTitle}&raquo;</span>`;

				return tmpDiv.firstChild.innerText;
			}

			return settingsTitle;
		},

		getShowedColumns(): string[]
		{
			const result = [];
			const cells = this.parent.getRows().getHeadFirstChild().getCells();

			[].slice.call(cells).forEach((column) => {
				if ('name' in column.dataset)
				{
					result.push(column.dataset.name);
				}
			});

			return result;
		},

		getItems(): []
		{
			return this.getPopup().getItems();
		},

		saveColumns(columns: string[], callback: Function): void
		{
			this.getPopup().saveColumnsByNames(columns, callback);
		},

		select(name: string, value: boolean = true): void
		{
			this.getPopup().select(name, value);
		},
	};
})();
