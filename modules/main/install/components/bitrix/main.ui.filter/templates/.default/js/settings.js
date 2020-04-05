;(function() {
	'use strict';

	BX.namespace('BX.Filter');

	/**
	 * Filter settings class
	 * @param options
	 * @param parent
	 * @constructor
	 */
	BX.Filter.Settings = function(options, parent)
	{
		/**
		 * Field
		 * @type {string}
		 */
		this.classField = 'main-ui-control-field';
		this.classFieldGroup = 'main-ui-control-field-group';
		this.classFieldLine = 'main-ui-filter-field-line';
		this.classFieldDelete = 'main-ui-filter-field-delete';
		this.classFieldLabel = 'main-ui-control-field-label';
		this.classFieldWithLabel = 'main-ui-filter-wield-with-label';
		this.classPresetName = 'main-ui-filter-sidebar-item-text';
		this.classControl = 'main-ui-control';
		this.classDateInput = 'main-ui-date-input';
		this.classHide = 'main-ui-hide';
		this.classNumberInput = 'main-ui-number-input';
		this.classSelect = 'main-ui-select';
		this.classMultiSelect = 'main-ui-multi-select';
		this.classValueDelete = 'main-ui-control-value-delete';
		this.classStringInput = 'main-ui-control-string';
		this.classAddField = 'main-ui-filter-field-add-item';
		this.classAddPresetField = 'main-ui-filter-new-filter';
		this.classAddPresetFieldInput = 'main-ui-filter-sidebar-edit-control';
		this.classAddPresetButton = 'main-ui-filter-add-item';
		this.classButtonsContainer = 'main-ui-filter-field-button-container';
		this.classSaveButton = 'main-ui-filter-save';
		this.classCancelButton = 'main-ui-filter-cancel';
		this.classMenuItem = 'main-ui-select-inner-item';
		this.classMenuItemText = 'main-ui-select-inner-item-element';
		this.classMenuMultiItemText = 'main-ui-select-inner-label';
		this.classMenuItemChecked = 'main-ui-checked';
		this.classSearchContainer = 'main-ui-filter-search';
		this.classDefaultPopup = 'popup-window';
		this.classPopupFieldList = 'main-ui-filter-popup-field-list';
		this.classPopupFieldList1Column = 'main-ui-filter-field-list-1-column';
		this.classPopupFieldList2Column = 'main-ui-filter-field-list-2-column';
		this.classPopupFieldList3Column = 'main-ui-filter-field-list-3-column';
		this.classFieldListItem = 'main-ui-filter-field-list-item';
		this.classEditButton = 'main-ui-filter-add-edit';
		this.classPresetEdit = 'main-ui-filter-edit';
		this.classPresetNameEdit = 'main-ui-filter-edit-text';
		this.classPresetDeleteButton = 'main-ui-delete';
		this.classPresetDragButton = 'main-ui-filter-icon-grab';
		this.classPresetEditButton = 'main-ui-filter-icon-edit';
		this.classPresetEditInput = 'main-ui-filter-sidebar-item-input';
		this.classPresetOndrag = 'main-ui-filter-sidebar-item-ondrag';
		this.classSquare = 'main-ui-square';
		this.classSquareDelete = 'main-ui-square-delete';
		this.classSquareSelected = 'main-ui-square-selected';
		this.classPresetsContainer = 'main-ui-filter-sidebar-item-container';
		this.classPreset = 'main-ui-filter-sidebar-item';
		this.classPresetCurrent = 'main-ui-filter-current-item';
		this.classFilterContainer = 'main-ui-filter-wrapper';
		this.classFileldControlList = 'main-ui-filter-field-container-list';
		this.classRestoreFieldsButton = 'main-ui-filter-field-restore-items';
		this.classClearSearchValueButton = 'main-ui-delete';
		this.classSearchButtonsContainer = 'main-ui-item-icon-block';
		this.classSearchButton = 'main-ui-search';
		this.classDisabled = 'main-ui-disable';
		this.classAnimationShow = 'main-ui-popup-show-animation';
		this.classAnimationClose = 'main-ui-popup-close-animation';
		this.classSidebarControlsContainer = 'main-ui-filter-add-container';
		this.searchContainerPostfix = '_search_container';
		this.classPresetButtonsContainer = 'main-ui-filter-field-preset-button-container';
		this.classFindButton = 'main-ui-filter-find';
		this.classResetButton = 'main-ui-filter-reset';
		this.classDefaultFilter = 'main-ui-filter-default-preset';
		this.classRestoreButton = 'main-ui-filter-reset-link';
		this.classPinButton = 'main-ui-filter-icon-pin';
		this.classPopupOverlay = 'popup-window-overlay';
		this.classPinnedPreset = 'main-ui-item-pin';
		this.classWaitButtonClass = 'ui-btn-clock';
		this.classForAllCheckbox = 'main-ui-filter-save-for-all';
		this.classShow = 'main-ui-show';
		this.classFocus = 'main-ui-focus';
		this.classPresetField = 'main-ui-filter-preset-field';
		this.numberPostfix = '_numsel';
		this.datePostfix = '_datesel';
		this.toPostfix = '_to';
		this.fromPostfix = '_from';
		this.daysPostfix = '_days';
		this.monthPostfix = '_month';
		this.quarterPostfix = '_quarter';
		this.yearPostfix = '_year';
		this.generalTemplateId = '';
		this.init(options, parent);
	};

	BX.Filter.Settings.prototype = {
		init: function(options, parent)
		{
			this.generalTemplateId = parent.getParam('FILTER_ID') + '_GENERAL_template';
			this.mergeSettings(options);
		},

		get: function(name, defaultValue)
		{
			return (name && name in this && !BX.type.isFunction(this[name])) ? this[name] : defaultValue;
		},

		mergeSettings: function(options)
		{
			if (BX.type.isPlainObject(options))
			{
				Object.keys(options).forEach(function(key) {
					if (!BX.type.isFunction(this[key]))
					{
						this[key] = options[key];
					}
				}, this);
			}
		}
	};

})();