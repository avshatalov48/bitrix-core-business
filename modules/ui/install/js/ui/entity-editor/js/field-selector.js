/* eslint no-underscore-dangle: off */
/* eslint @bitrix24/bitrix24-rules/no-pseudo-private: off */

//region FIELD SELECTOR
if (BX.Type.isUndefined(BX.UI.EntityEditorFieldSelector))
{
	BX.UI.EntityEditorFieldSelector = function()
	{
		this._id = '';
		this._settings = {};
		this._scheme = null;
		this._excludedNames = null;
		this._currentSchemeElementName = '';
		this.checkboxList = null;
		this.defaultSectionKey = 'default-section';
		this.categories = [];
		this.options = [];
	};

	BX.UI.EntityEditorFieldSelector.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};
				this._scheme = BX.prop.get(this._settings, "scheme", null);
				if (!this._scheme)
				{
					throw "BX.UI.EntityEditorFieldSelector. Parameter 'scheme' is not found.";
				}
				this._excludedNames = BX.prop.getObject(this._settings, 'excludedNames', {});
			},

			getMessage: function(name)
			{
				return BX.prop.getString(BX.UI.EntityEditorFieldSelector.messages, name, name);
			},

			isSchemeElementEnabled: function(sectionElement, schemeElement)
			{
				const sectionName = sectionElement.getName();
				const elementName = schemeElement.getName();
				const elementList = this._excludedNames[sectionName];

				if (BX.Type.isArrayFilled(elementList))
				{
					return !elementList.includes(elementName);
				}

				return true;
			},

			addClosingListener: function(listener)
			{
				BX.Event.EventEmitter.subscribe("BX.UI.EntityEditorFieldSelector:close", listener);
			},

			removeClosingListener: function(listener)
			{
				BX.Event.EventEmitter.unsubscribe("BX.UI.EntityEditorFieldSelector:close", listener);
			},

			isOpened: function()
			{
				return this.checkboxList && this.checkboxList.isShown();
			},

			setExcludedNames: function(excludedNames)
			{
				this._excludedNames = excludedNames;
			},

			setCurrentSchemeElementName: function(currentSchemeElementName)
			{
				this._currentSchemeElementName = currentSchemeElementName;
			},

			open: function()
			{
				if(this.isOpened())
				{
					return;
				}

				BX.Runtime.loadExtension('ui.dialogs.checkbox-list').then(() => {
					this.checkboxList = this.createFieldsSelector();
					this.checkboxList.show();
				});
			},

			createFieldsSelector: function()
			{
				this.initCheckboxListParams();
				this.prepareElements();
				this.prepareHiddenElements();

				const {
					'_settings': settings,
					categories,
					options
				} = this;

				const sections = this.getDefaultSections();

				return new BX.UI.CheckboxList({
					columnCount: 3,
					lang: {
						title: BX.prop.getString(settings, 'title', ''),
						acceptBtn: BX.Loc.getMessage('UI_ENTITY_EDITOR_SELECT'),
						placeholder: BX.Loc.getMessage('UI_ENTITY_EDITOR_FIELD_SEARCH_PLACEHOLDER'),
						emptyStateTitle: BX.Loc.getMessage('UI_ENTITY_EDITOR_FIELD_EMPTY_STATE_TITLE'),
						emptyStateDescription: BX.Loc.getMessage('UI_ENTITY_EDITOR_FIELD_EMPTY_STATE_DESCRIPTION'),
						allSectionsDisabledTitle: BX.Loc.getMessage('UI_ENTITY_EDITOR_FIELD_ALL_SECTIONS_DISABLED'),
					},
					sections,
					categories,
					options,
					params: {
						destroyPopupAfterClose: true,
						useSearch: BX.prop.getBoolean(settings, 'useFieldsSearch', true),
						showBackToDefaultSettings: BX.prop.getBoolean(settings, 'showBackToDefaultSettings', false),
						useSectioning: BX.Type.isStringFilled(sections[0].title),
					},
					events: {
						onApply: (event) => this.onApplyCheckboxList(event.data.fields),
						onCancel: (event) => this.onCancelCheckboxList(),
					},
				});
			},

			initCheckboxListParams: function()
			{
				this.categories = [];
				this.options = [];
			},

			prepareElements: function()
			{
				const columns = this._scheme.getElements();
				columns.forEach((column) => {
					const sections = column.getElements();
					sections.forEach((section) => {
						const effectiveElements = [];
						const childElements = section.getElements();
						childElements.forEach((childElement) => {
							if (!this.isSchemeElementEnabled(section, childElement))
							{
								return;
							}

							if (childElement.isTransferable() && childElement.getName() !== '')
							{
								effectiveElements.push(childElement);
							}
						});

						if (!BX.Type.isArrayFilled(effectiveElements))
						{
							return;
						}

						this.categories.push({
							title: section.getTitle(),
							sectionKey: this.defaultSectionKey,
							key:  section.getName(),
						});

						effectiveElements.forEach((element) => this.addOption(element, section));
					});
				});
			},

			prepareHiddenElements: function()
			{
				const hiddenElements = BX.prop.getArray(this._settings, 'hiddenElements', []);
				if (!BX.Type.isArrayFilled(hiddenElements))
				{
					return;
				}

				const hiddenCategory = {
					title: BX.Loc.getMessage('UI_ENTITY_EDITOR_SECTION_WITH_HIDDEN_FIELDS'),
					sectionKey: this.defaultSectionKey,
					key: 'hidden',
				}
				this.categories.push(hiddenCategory);

				hiddenElements.forEach((element) => this.addOption(element, null, hiddenCategory));
			},

			addOption: function(element, section = null, category = null)
			{
				this.options.push({
					title: element.getTitle(),
					value: false,
					categoryKey: this.getSectionName(section, category),
					defaultValue: false,
					id: this.getElementId(element, section),
				});
			},

			getElementId: function(element, section = null)
			{
				return this.getSectionName(section) + '\\' + element.getName();
			},

			getSectionName: function(section = null, category = null)
			{
				if (category)
				{
					return category.key;
				}

				return section ? section.getName() : this._currentSchemeElementName;
			},

			getDefaultSections: function()
			{
				return [
					{
						key: this.defaultSectionKey,
						title: BX.prop.getString(this._settings, 'buttonTitle', null),
						value: true,
					},
				];
			},

			onApplyCheckboxList: function(fields)
			{
				BX.Event.EventEmitter.emit(
					'BX.UI.EntityEditorFieldSelector:close',
					{
						sender: this,
						isCanceled: false,
						items: this.getSelectedItems(fields),
					}
				);
			},

			onCancelCheckboxList: function()
			{
				BX.Event.EventEmitter.emit(
					'BX.UI.EntityEditorFieldSelector:close',
					{
						sender: this,
						isCanceled: true,
					}
				);
			},

			/**
			 * @param {string[]} fields
			 * @returns {[{
			 *     sectionName: string,
			 *     fieldName: string,
			 * }]}
			 */
			getSelectedItems: function(fields)
			{
				const results = [];

				fields.forEach((field) => {
					if (!BX.Type.isStringFilled(field))
					{
						return;
					}

					const parts = field.split('\\');
					if (parts.length >= 2)
					{
						results.push({
							sectionName: parts[0],
							fieldName: parts[1],
						});
					}
				});

				return results;
			},
		};

	if (BX.Type.isUndefined(BX.UI.EntityEditorFieldSelector.messages))
	{
		BX.UI.EntityEditorFieldSelector.messages = {};
	}

	BX.UI.EntityEditorFieldSelector.create = function(id, settings)
	{
		const self = new BX.UI.EntityEditorFieldSelector(id, settings);
		self.initialize(id, settings);

		return self;
	}
}
//endregion

//region USER SELECTOR
if(typeof(BX.UI.EntityEditorUserSelector) === "undefined")
{
	BX.UI.EntityEditorUserSelector = function()
	{
		this._id = "";
		this._settings = {};
	};

	BX.UI.EntityEditorUserSelector.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};
				this._isInitialized = false;
				this._onlyUsers = BX.prop.getBoolean(this._settings, "onlyUsers", true);
			},
			getId: function()
			{
				return this._id;
			},
			open: function(anchor)
			{
				if(this._mainWindow && this._mainWindow === BX.SocNetLogDestination.containerWindow)
				{
					return;
				}

				if(!this._isInitialized)
				{
					BX.SocNetLogDestination.init(
						{
							name: this._id,
							extranetUser:  false,
							userSearchArea: "I",
							bindMainPopup: { node: anchor, offsetTop: "5px", offsetLeft: "15px" },
							callback: {
								select : BX.delegate(this.onSelect, this),
								unSelect: BX.delegate(this.onSelect, this)
							},
							showSearchInput: BX.prop.getBoolean(this._settings, "showSearchInput", true),
							departmentSelectDisable: (this._onlyUsers ? true : false),
							items:
								{
									users: BX.UI.EntityEditorUserSelector.users,
									groups: {},
									sonetgroups: (this._onlyUsers ? {} : BX.UI.EntityEditorUserSelector.socnetGroups),
									department: BX.UI.EntityEditorUserSelector.department,
									departmentRelation : BX.SocNetLogDestination.buildDepartmentRelation(BX.UI.EntityEditorUserSelector.department)
								},
							itemsLast: BX.UI.EntityEditorUserSelector.last,
							itemsSelected: BX.prop.getObject(this._settings, "itemsSelected", {}),
							isCrmFeed: false,
							useClientDatabase: false,
							destSort: {},
							allowAddUser: false,
							allowSearchCrmEmailUsers: false,
							allowUserSearch: true
						}
					);
					this._isInitialized = true;
				}

				BX.SocNetLogDestination.openDialog(this._id, { bindNode: anchor });
				this._mainWindow = BX.SocNetLogDestination.containerWindow;
			},
			close: function()
			{
				if(this._mainWindow && this._mainWindow === BX.SocNetLogDestination.containerWindow)
				{
					BX.SocNetLogDestination.closeDialog();
					this._mainWindow = null;
					this._isInitialized = false;
				}

			},
			onSelect: function(item, type, search, bUndeleted)
			{
				if(this._onlyUsers && type !== "users")
				{
					return;
				}

				var callback = BX.prop.getFunction(this._settings, "callback", null);
				if(callback)
				{
					callback(this, item);
				}
			}
		};

	BX.UI.EntityEditorUserSelector.items = {};
	BX.UI.EntityEditorUserSelector.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUserSelector(id, settings);
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	}
}
//endregion

// region USER SELECTOR EntitySelector
if (BX.Type.isUndefined(BX.UI.EntityEditorEntitySelector))
{
	BX.UI.EntityEditorEntitySelector = function()
	{
		this._id = '';
		this._settings = {};
	};

	BX.UI.EntityEditorEntitySelector.prototype = {
		initialize(id, settings)
		{
			this._id = id;
			this._settings = BX.Type.isObject(settings) ? settings : {};
			this._entitySelector = null;

			this.validateSettings(this._settings);
		},

		validateSettings(settings)
		{
			if (!BX.Type.isFunction(settings.callback))
			{
				throw new TypeError('BX.UI.EntityEditorEntitySelector. Callback is not defined.');
			}
		},

		getId()
		{
			return this._id;
		},

		open(anchor)
		{
			if (!this._entitySelector)
			{
				this._entitySelector = this._createDialog(anchor);
			}

			this._entitySelector.show();
		},

		close()
		{
			if (this._entitySelector)
			{
				this._entitySelector.destroy();
			}
			this._entitySelector = null;
		},

		onSelect(event)
		{
			const item = event.getData().item;

			if (!item)
			{
				return;
			}

			let id = null;
			switch (item.entityId)
			{
				case 'user':
					id = `U${item.id}`;
					break;
				case 'department':
					id = `DR${item.id}`;
					break;
				case 'project':
					id = `SG${item.id}`;
					break;
				default:
					id = null;
			}

			if (!id)
			{
				return;
			}

			const result = {
				id,
				name: item.title?.text || '',
			};

			this._settings.callback(this, result);
		},

		_createDialog(anchor)
		{
			return new BX.UI.EntitySelector.Dialog({
				targetNode: anchor,
				id: `crm-ee-user_selector-${BX.Text.getRandom()}`,
				context: 'crm-ee-user_selector',
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: true,
							emailUsers: false,
							inviteEmployeeLink: false,
							inviteGuestLink: false,
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersAndDepartments',
						},
					},
				],
				popupOptions: {
					bindOptions: { forceBindPosition: true },
				},
				enableSearch: true,
				events: {
					'Item:onSelect': this.onSelect.bind(this),
					'Item:onDeselect': this.onSelect.bind(this),
					onHide: this.close.bind(this),
				},
				hideOnSelect: true,
				offsetTop: 3,
				clearUnavailableItems: true,
				multiple: false,
			});
		},
	};

	BX.UI.EntityEditorEntitySelector.items = {};
	BX.UI.EntityEditorEntitySelector.create = function(id, settings)
	{
		const self = new BX.UI.EntityEditorEntitySelector(id, settings);
		self.initialize(id, settings);
		this.items[self.getId()] = self;

		return self;
	};
}
// endregion
