;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.Settings
	 * @constructor
	 */
	BX.Grid.Settings = function()
	{
		this.settings = {};
		this.defaultSettings = {
			classContainer: 'main-grid',
			classWrapper: 'main-grid-wrapper',
			classTable: 'main-grid-table',
			classScrollContainer: 'main-grid-container',
			classFadeContainer: 'main-grid-fade',
			classFadeContainerRight: 'main-grid-fade-right',
			classFadeContainerLeft: 'main-grid-fade-left',
			classNavPanel: 'main-grid-nav-panel',
			classActionPanel: 'main-grid-action-panel',
			classCursor: 'main-grid-cursor',
			classRowCustom: 'main-grid-row-custom',
			classMoreButton: 'main-grid-more-btn',
			classRow: 'main-grid-row',
			classHeadRow: 'main-grid-row-head',
			classBodyRow: 'main-grid-row-body',
			classFootRow: 'main-grid-row-foot',
			classDataRows: 'main-grid-row-data',
			classPanels: 'main-grid-bottom-panels',
			classCellHeadContainer: 'main-grid-cell-head-container',
			classCellHeadOndrag: 'main-grid-cell-head-ondrag',
			classEmptyRows: 'main-grid-row-empty',
			classEmptyBlock: 'main-grid-empty-block',
			classCheckAllCheckboxes: 'main-grid-check-all',
			classCheckedRow: 'main-grid-row-checked',
			classRowCheckbox: 'main-grid-row-checkbox',
			classPagination: 'main-grid-panel-cell-pagination',
			classActionCol: 'main-grid-cell-action',
			classCounterDisplayed: 'main-grid-counter-displayed',
			classCounterSelected: 'main-grid-counter-selected',
			classCounterTotal: 'main-grid-panel-total',
			classTableFade: 'main-grid-table-fade',
			classDragActive: 'main-grid-on-row-drag',
			classResizeButton: 'main-grid-resize-button',
			classOnDrag: 'main-grid-ondrag',
			classDisableDrag: 'main-grid-row-drag-disabled',
			classPanelCellContent: 'main-grid-panel-content',
			classCollapseButton: 'main-grid-plus-button',
			classRowStateLoad: 'main-grid-load-row',
			classRowStateExpand: 'main-grid-row-expand',
			classHeaderSortable: 'main-grid-col-sortable',
			classHeaderNoSortable: 'main-grid-col-no-sortable',
			classCellStatic: 'main-grid-cell-static',
			classHeadCell: 'main-grid-cell-head',
			classPageSize: 'main-grid-panel-select-pagesize',
			classGroupEditButton: 'main-grid-control-panel-action-edit',
			classGroupDeleteButton: 'main-grid-control-panel-action-remove',
			classGroupActionsDisabled: 'main-grid-control-panel-action-icon-disable',
			classPanelButton: 'main-grid-buttons',
			classPanelApplyButton: 'main-grid-control-panel-apply-button',
			classPanelCheckbox: 'main-grid-panel-checkbox',
			classEditor: 'main-grid-editor',
			classEditorContainer: 'main-grid-editor-container',
			classEditorText: 'main-grid-editor-text',
			classEditorDate: 'main-grid-editor-date',
			classEditorNumber: 'main-grid-editor-number',
			classEditorRange: 'main-grid-editor-range',
			classEditorCheckbox: 'main-grid-editor-checkbox',
			classEditorTextarea: 'main-grid-editor-textarea',
			classEditorCustom: 'main-grid-editor-custom',
			classCellContainer: 'main-grid-cell-content',
			classEditorOutput: 'main-grid-editor-output',
			classSettingsWindow: 'main-grid-settings-window',
			classSettingsWindowColumn: 'main-grid-settings-window-list-item',
			classSettingsWindowColumnLabel: 'main-grid-settings-window-list-item-label',
			classSettingsWindowColumnEditState: 'main-grid-settings-window-list-item-edit',
			classSettingsWindowColumnEditInput: 'main-grid-settings-window-list-item-edit-input',
			classSettingsWindowColumnEditButton: 'main-grid-settings-window-list-item-edit-button',
			classSettingsWindowColumnCheckbox: 'main-grid-settings-window-list-item-checkbox',
			classSettingsWindowShow: 'main-grid-settings-window-show',
			classSettingsWindowSelectAll: 'main-grid-settings-window-select-all',
			classSettingsWindowUnselectAll: 'main-grid-settings-window-unselect-all',
			classSettingsButton: 'main-grid-interface-settings-icon',
			classSettingsButtonActive: 'main-grid-interface-settings-icon-active',
			classSettingsWindowClose: 'main-grid-settings-window-actions-item-close',
			classSettingsWindowReset: 'main-grid-settings-window-actions-item-reset',
			classSettingsWindowColumnChecked: 'main-grid-settings-window-list-item-checked',
			classShowAnimation: 'main-grid-show-popup-animation',
			classCloseAnimation: 'main-grid-close-popup-animation',
			classLoader: 'main-grid-loader-container',
			classLoaderShow: 'main-grid-show-loader',
			classLoaderHide: 'main-grid-hide-loader',
			classRowError: 'main-grid-error',
			loaderHideAnimationName: 'hideLoader',
			classHide: 'main-grid-hide',
			classEar: 'main-grid-ear',
			classEarLeft: 'main-grid-ear-left',
			classEarRight: 'main-grid-ear-right',
			classNotCount: 'main-grid-not-count',
			classCounter: 'main-grid-panel-counter',
			classForAllCounterEnabled: 'main-grid-panel-counter-for-all-enable',
			classLoad: 'load',
			classRowActionButton: 'main-grid-row-action-button',
			classDropdown: 'main-dropdown',
			classPanelControl: 'main-grid-panel-control',
			classPanelControlContainer: 'main-grid-panel-control-container',
			classForAllCheckbox: 'main-grid-for-all-checkbox',
			classDisable: 'main-grid-disable',
			dataActionsKey: 'actions',
			updateActionMore: 'more',
			classShow: 'show',
			classGridShow: 'main-grid-show',
			updateActionPagination: 'pagination',
			updateActionSort: 'sort',
			ajaxIdDataProp: 'ajaxid',
			pageSizeId: 'grid_page_size',
			sortableRows: true,
			sortableColumns: true,
			animationDuration: 300
		};
		this.prepare();
	};


	BX.Grid.Settings.prototype = {
		prepare: function()
		{
			this.settings = this.defaultSettings;
		},

		getDefault: function()
		{
			return this.defaultSettings;
		},

		get: function(name)
		{
			var result;

			try {
				result = (this.getDefault())[name];
			} catch (err) {
				result = null;
			}

			return result;
		},

		getList: function()
		{
			return this.getDefault();
		}
	};
})();
