;(function() {
	'use strict';

	BX.namespace('BX.Grid');


	/**
	 * @param {BX.Main.grid} parent
	 * @param {HTMLElement} node
	 * @constructor
	 */
	BX.Grid.SettingsWindowColumn = function(parent, node)
	{
		this.node = null;
		this.label = null;
		this.checkbox = null;
		this.editButton = null;
		this.settings = null;
		this.parent = null;
		this.default = null;
		this.defaultTitle = null;
		this.state = null;
		this.lastTitle = null;
		this.init(parent, node);
	};


	BX.Grid.SettingsWindowColumn.prototype = {
		init: function(parent, node)
		{
			this.parent = parent;
			this.node = node;

			try {
				this.lastTitle = node.querySelector("label").innerText.trim();
			} catch (err) {}

			this.updateState();

			BX.bind(this.getEditButton(), 'click', BX.proxy(this.onEditButtonClick, this));
		},


		onEditButtonClick: function(event)
		{
			event.stopPropagation();
			this.isEditEnabled() ? this.disableEdit() : this.enableEdit();
		},


		/**
		 * @private
		 * @param {object} state
		 * @property {boolean} state.selected
		 * @property {title} state.title
		 */
		setState: function(state)
		{
			this.state = state;
		},


		/**
		 * Gets state of column
		 * @return {object}
		 */
		getState: function()
		{
			return this.state;
		},


		/**
		 * Updates default state
		 */
		updateState: function()
		{
			this.setState({
				selected: this.isSelected(),
				title: this.getTitle()
			});
		},


		/**
		 * Restores last state
		 */
		restoreState: function()
		{
			var state = this.getState();

			state.selected ? this.select() : this.unselect();
			this.setTitle(state.title);
		},


		/**
		 * Gets column id
		 * @return {string}
		 */
		getId: function()
		{
			return this.getNode().dataset.name;
		},


		/**
		 * Gets column title
		 * @return {string}
		 */
		getTitle: function()
		{
			return this.getLabel().innerText;
		},


		/**
		 * Sets column title
		 * @param {string} title
		 */
		setTitle: function(title)
		{
			this.getLabel().innerText = !!title && title !== "undefined" ? title : this.getDefaultTitle();
		},


		/**
		 * @return {boolean}
		 */
		isEdited: function()
		{
			return this.getTitle() !== this.getDefaultTitle();
		},


		/**
		 * Gets column settings
		 * @return {?object}
		 */
		getSettings: function()
		{
			if (this.settings === null)
			{
				var columns = this.parent.getParam('DEFAULT_COLUMNS');
				this.settings = this.getId() in columns ? columns[this.getId()] : {};
			}

			return this.settings;
		},


		/**
		 * Checks column is default
		 * @return {boolean}
		 */
		isDefault: function()
		{
			if (this.default === null)
			{
				var settings = this.getSettings();
				this.default = 'default' in settings ? settings.default : false;
			}

			return this.default;
		},


		/**
		 * Restore column to default state
		 */
		restore: function()
		{
			this.isDefault() ? this.select() : this.unselect();
			this.setTitle(this.getDefaultTitle());
			this.disableEdit();
			this.updateState();
		},


		/**
		 * Gets default column title
		 * @return {?string}
		 */
		getDefaultTitle: function()
		{
			if (this.defaultTitle === null)
			{
				var settings = this.getSettings();
				this.defaultTitle = 'name' in settings ? settings.name : this.lastTitle;
			}

			return this.defaultTitle;
		},


		/**
		 * Gets column node
		 * @return {?HTMLElement}
		 */
		getNode: function()
		{
			return this.node;
		},


		/**
		 * Gets column label node
		 * @return {?HTMLLabelElement}
		 */
		getLabel: function()
		{
			if (this.label === null)
			{
				this.label = BX.Grid.Utils.getByTag(this.getNode(), 'label', true);
			}

			return this.label;
		},


		/**
		 * Gets column checkbox node
		 * @return {?HTMLInputElement}
		 */
		getCheckbox: function()
		{
			if (this.checkbox === null)
			{
				this.checkbox = BX.Grid.Utils.getBySelector(this.getNode(), 'input[type="checkbox"]', true);
			}

			return this.checkbox;
		},


		/**
		 * Gets edit button
		 * @return {?HTMLElement}
		 */
		getEditButton: function()
		{
			if (this.editButton === null)
			{
				this.editButton = BX.Grid.Utils.getByClass(
					this.getNode(),
					this.parent.settings.get('classSettingsWindowColumnEditButton'),
					true
				);
			}

			return this.editButton;
		},


		/**
		 * Enables edit mode
		 */
		enableEdit: function()
		{
			this.getLabel().contentEditable = true;
			this.getCheckbox().disabled = true;
			this.adjustCaret();
		},


		/**
		 * Disables edit mode
		 */
		disableEdit: function()
		{
			this.getLabel().contentEditable = false;
			this.getCheckbox().disabled = false;
		},


		/**
		 * Checks is edit enabled
		 * @return {boolean}
		 */
		isEditEnabled: function()
		{
			return this.getLabel().isContentEditable;
		},


		/**
		 * Checks column is active
		 * @return {boolean}
		 */
		isSelected: function()
		{
			return this.getCheckbox().checked;
		},


		/**
		 * Selects column
		 */
		select: function()
		{
			this.getCheckbox().checked = true;
		},


		/**
		 * Unselects column
		 */
		unselect: function()
		{
			this.getCheckbox().checked = false;
		},


		/**
		 * @private
		 */
		adjustCaret: function()
		{
			var range = document.createRange();
			var selection = window.getSelection();
			var elementTextLength = this.getLabel().innerText.length;
			var textNodes = this.getLabel().childNodes;
			var lastTextNode = textNodes[textNodes.length - 1];

			range.setStart(lastTextNode, elementTextLength);
			range.setEnd(lastTextNode, elementTextLength);
			range.collapse(true);

			selection.removeAllRanges();
			selection.addRange(range);
			BX.fireEvent(this.getNode(), 'focus');
		}
	};

})();