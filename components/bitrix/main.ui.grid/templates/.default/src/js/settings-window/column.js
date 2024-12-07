/* eslint-disable */

;(function() {
	'use strict';

	BX.namespace('BX.Grid.SettingsWindow');


	/**
	 * @param {BX.Main.grid} parent
	 * @param {HTMLElement} node
	 * @constructor
	 */
	BX.Grid.SettingsWindow.Column = function(parent, node)
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

	BX.Grid.SettingsWindow.Column.inited = {};

	BX.Grid.SettingsWindow.Column.prototype = {
		init: function(parent, node)
		{
			this.parent = parent;
			this.node = node;

			try {
				this.lastTitle = node.querySelector("label").innerText.trim();
			} catch (err) {}

			this.updateState();

			if (!BX.Grid.SettingsWindow.Column.inited[this.getId()])
			{
				BX.Grid.SettingsWindow.Column.inited[this.getId()] = true;
				BX.bind(this.getEditButton(), 'click', BX.proxy(this.onEditButtonClick, this));
				BX.bind(this.getStickyButton(), 'click', BX.proxy(this.onStickyButtonClick, this));
			}
		},

		getStickyButton: function()
		{
			return this.node.querySelector(".main-grid-settings-window-list-item-sticky-button");
		},

		isSticked: function()
		{
			return this.node.classList.contains("main-grid-settings-window-list-item-sticked");
		},

		onStickyButtonClick: function()
		{
			if (this.isSticked())
			{
				this.unstick();
			}
			else
			{
				this.stick();
			}
		},

		stick: function()
		{
			this.node.classList.add("main-grid-settings-window-list-item-sticked");
		},

		unstick: function()
		{
			this.node.classList.remove("main-grid-settings-window-list-item-sticked");
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
				sticked: this.isSticked(),
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
			state.sticked ? this.stick() : this.unstick();
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
			this.node.dataset.stickedDefault === "true" ? this.stick() : this.unstick();
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
				BX.Event.bind(this.label, 'paste', this.onLabelPaste.bind(this));
				BX.Event.bind(this.label, 'keydown', this.onLabelKeydown.bind(this));
			}

			return this.label;
		},

		onLabelPaste: function(event)
		{
			event.preventDefault();

			if (event.clipboardData && event.clipboardData.getData)
			{
				var sourceText = event.clipboardData.getData("text/plain");
				var encodedText = BX.Text.encode(sourceText);
				var formattedHtml = encodedText
					.trim()
					.replace(new RegExp('\t', 'g'), " ")
					.replace(new RegExp('\n', 'g'), " ")
					.replace(/ +(?= )/g,'');
				document.execCommand("insertHTML", false, formattedHtml);
			}
		},

		onLabelKeydown: function(event)
		{
			if (event.keyCode === 13)
			{
				event.preventDefault();
			}
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
