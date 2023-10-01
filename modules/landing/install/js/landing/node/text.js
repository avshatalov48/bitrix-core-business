;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	const escapeText = BX.Landing.Utils.escapeText;
	const headerTagMatcher = BX.Landing.Utils.Matchers.headerTag;
	const changeTagName = BX.Landing.Utils.changeTagName;
	const textToPlaceholders = BX.Landing.Utils.textToPlaceholders;

	/**
	 * Implements interface for works with text node of blocks
	 *
	 * @param {nodeOptions} options
	 *
	 * @extends {BX.Landing.Block.Node}
	 * @inheritDoc
	 */
	BX.Landing.Block.Node.Text = function(options)
	{
		BX.Runtime.loadExtension('landing.node.text.tableeditor');
		BX.Landing.Block.Node.apply(this, arguments);

		this.type = "text";
		this.tableBaseFontSize = '22';

		this.onClick = this.onClick.bind(this);
		this.onPaste = this.onPaste.bind(this);
		this.onDrop = this.onDrop.bind(this);
		this.onInput = this.onInput.bind(this);
		this.onKeyDown = this.onKeyDown.bind(this);
		this.onMousedown = this.onMousedown.bind(this);
		this.onMouseup = this.onMouseup.bind(this);

		// Bind on node events
		this.node.addEventListener("mousedown", this.onMousedown);
		this.node.addEventListener("click", this.onClick);
		this.node.addEventListener("paste", this.onPaste);
		this.node.addEventListener("drop", this.onDrop);
		this.node.addEventListener("input", this.onInput);
		this.node.addEventListener("keydown", this.onKeyDown);

		document.addEventListener("mouseup", this.onMouseup);
	};


	/**
	 * Stores node with active editor
	 * @type {?BX.Landing.Block.Node.Text}
	 */
	BX.Landing.Block.Node.Text.currentNode = null;


	BX.Landing.Block.Node.Text.prototype = {
		__proto__: BX.Landing.Block.Node.prototype,
		superClass: BX.Landing.Block.Node.prototype,
		constructor: BX.Landing.Block.Node.Text,


		/**
		 * Handles allow inline edit event
		 */
		onAllowInlineEdit: function()
		{
			// Show title "Click to edit" for node
			this.node.setAttribute("title", escapeText(BX.Landing.Loc.getMessage("LANDING_TITLE_OF_TEXT_NODE")));
		},


		/**
		 * Handles change event
		 * @param {boolean} [preventAdjustPosition]
		 * @param {?boolean} [preventHistory = false]
		 */
		onChange: function(preventAdjustPosition, preventHistory)
		{
			this.superClass.onChange.call(this, preventHistory);
			if (!preventAdjustPosition)
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(this.node);
			}
			if (!preventHistory)
			{
				BX.Landing.History.getInstance().push();
			}
		},

		onKeyDown: function(event)
		{
			if (event.code === 'Backspace')
			{
				this.onBackspaceDown(event);
			}
			this.onInput(event);
		},


		onInput: function(event)
		{
			clearTimeout(this.inputTimeout);

			const key = event.keyCode || event.which;

			if (!(key === 90 && (top.window.navigator.userAgent.match(/win/i) ? event.ctrlKey : event.metaKey)))
			{
				this.inputTimeout = setTimeout(function() {
					if (this.lastValue !== this.getValue())
					{
						this.onChange(true);
						this.lastValue = this.getValue();
					}
				}.bind(this), 400);
			}

			if (this.isTable(event))
			{
				const tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'));
				if (
					event.srcElement.textContent === ''
					&& BX.Dom.hasClass(event.srcElement, 'landing-table-td')
					&& tableFontSize < this.tableBaseFontSize
				)
				{
					BX.Dom.addClass(event.srcElement, 'landing-table-td-height');
				}
				else
				{
					BX.Dom.removeClass(event.srcElement, 'landing-table-td-height');
				}
			}
		},


		/**
		 * Handles escape press event
		 */
		onEscapePress: function()
		{
			// Hide editor by press on Escape button
			if (this.isEditable())
			{
				if (this === BX.Landing.Block.Node.Text.currentNode)
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}

				this.disableEdit();
			}
		},


		/**
		 * Handles drop event on this node
		 *
		 * @param {DragEvent} event
		 */
		onDrop: function(event)
		{
			// Prevents drag and drop any content into editor area
			event.preventDefault();
		},


		/**
		 * Handles paste event on this node
		 *
		 * @param {ClipboardEvent} event
		 * @param {function} event.preventDefault
		 * @param {object} event.clipboardData
		 */
		onPaste: function(event)
		{
			event.preventDefault();

			if (event.clipboardData && event.clipboardData.getData)
			{
				const sourceText = event.clipboardData.getData("text/plain");
				let encodedText = BX.Text.encode(sourceText);
				if (this.isLinkPasted(sourceText))
				{
					encodedText = this.prepareToLink(encodedText);
				}
				const formattedHtml = encodedText.replace(new RegExp('\n', 'g'), "<br>");
				document.execCommand("insertHTML", false, formattedHtml);
			}
			else
			{
				// ie11
				const text = window.clipboardData.getData("text");
				document.execCommand("paste", true, BX.Text.encode(text));
			}

			this.onChange();
		},


		/**
		 * Handles click on document
		 */
		onDocumentClick: function(event)
		{
			if (this.isEditable() && !this.fromNode)
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				this.disableEdit();
			}

			this.fromNode = false;
		},


		onMousedown: function(event)
		{
			if (!this.manifest.group)
			{
				this.fromNode = true;

				if (this.manifest.allowInlineEdit !== false &&
					BX.Landing.Main.getInstance().isControlsEnabled())
				{
					event.stopPropagation();
					this.enableEdit();
					if (this.isTable(event))
					{
						this.disableEdit();
						BX.Landing.Block.Node.Text.currentNode.node.querySelectorAll('.landing-table-container')
							.forEach(function(table) {
								if (!table.hasAttribute('table-prepare'))
								{
									BX.Landing.Block.Node.Text.prototype.prepareNewTable(table);
								}
							})
						const tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'));
						if (
							event.srcElement.textContent === ''
							&& BX.Dom.hasClass(event.srcElement, 'landing-table-td')
							&& tableFontSize < this.tableBaseFontSize
						)
						{
							BX.Dom.addClass(event.srcElement, 'landing-table-td-height')
						}
						else
						{
							BX.Dom.removeClass(event.srcElement, 'landing-table-td-height')
						}
					}
					else
					{
						if (!this.manifest.textOnly && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
						{
							BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.node, null, this.buttons);
						}
						if (BX.Landing.Block.Node.Text.nodeTableContainerList)
						{
							BX.Landing.Block.Node.Text.nodeTableContainerList.forEach(function(tableContainer) {
								tableContainer.tableEditor.unselect(tableContainer.tableEditor);
							});
						}
					}

					BX.Landing.UI.Tool.ColorPicker.hideAll();
				}

				requestAnimationFrame(function() {
					if (event.target.nodeName === "A" ||
						event.target.parentElement.nodeName === "A")
					{
						const range = document.createRange();
						range.selectNode(event.target);
						window.getSelection().removeAllRanges();
						window.getSelection().addRange(range);
					}
				});
			}
		},


		onMouseup: function()
		{
			setTimeout(function() {
				this.fromNode = false;
			}.bind(this), 10);
		},


		/**
		 * Click on field - switch edit mode.
		 */
		onClick: function(event)
		{
			if (this.isTable(event))
			{
				this.addTableButtons(event);
			}

			event.stopPropagation();
			event.preventDefault();
			this.fromNode = false;

			if (event.target.nodeName === "A" ||
				event.target.parentElement.nodeName === "A")
			{
				const range = document.createRange();
				range.selectNode(event.target);
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);
			}
		},


		/**
		 * Checks that is editable
		 * @return {boolean}
		 */
		isEditable: function()
		{
			return this.node.isContentEditable;
		},


		/**
		 * Enables edit mode
		 */
		enableEdit: function()
		{
			const currentNode = BX.Landing.Block.Node.Text.currentNode;
			if (currentNode)
			{
				const node = BX.Landing.Block.Node.Text.currentNode.node;
				const nodeTableContainerList = node.querySelectorAll('.landing-table-container');
				if (nodeTableContainerList.length > 0)
				{
					nodeTableContainerList.forEach(function(nodeTableContainer) {
						if (!nodeTableContainer.tableEditor)
						{
							nodeTableContainer.tableEditor = new BX.Landing.Node.Text.TableEditor.default(nodeTableContainer);
						}
					})
					BX.Landing.Block.Node.Text.nodeTableContainerList = nodeTableContainerList;
				}
			}

			if (!this.isEditable() && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				if (this !== BX.Landing.Block.Node.Text.currentNode && BX.Landing.Block.Node.Text.currentNode !== null)
				{
					BX.Landing.Block.Node.Text.currentNode.disableEdit();
				}

				BX.Landing.Block.Node.Text.currentNode = this;

				this.buttons = [];
				this.buttons.push(this.getDesignButton());

				if (BX.Landing.Main.getInstance()["options"]["allow_ai_text"])
				{
					this.buttons.push(this.getAiTextButton());
				}

				if (this.isHeader())
				{
					this.buttons.push(this.getChangeTagButton());
					this.getChangeTagButton().onChangeHandler = this.onChangeTag.bind(this);
				}

				this.lastValue = this.getValue();
				this.node.contentEditable = true;

				this.node.setAttribute("title", "");
			}
		},


		/**
		 * Gets design button for editor
		 * @return {BX.Landing.UI.Button.Design}
		 */
		getDesignButton: function()
		{
			if (!this.designButton)
			{
				this.designButton = new BX.Landing.UI.Button.Design("design", {
					html: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_DESIGN"),
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_DESIGN")},
					onClick: function() {
						BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
						this.disableEdit();
						this.onDesignShow(this.manifest.code);
					}.bind(this)
				});
			}

			return this.designButton;
		},

		/**
		 * Gets AI (text) button for editor
		 * @return {BX.Landing.UI.Button.AiText}
		 */
		getAiTextButton: function()
		{
			if (!this.aiTextButton)
			{
				this.aiTextButton = new BX.Landing.UI.Button.AiText.getInstance("ai_text", {
					html: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_AI_TEXT"),
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_AI_TEXT")},
					sections: this.manifest.sections,
					onSelect: function (item) {
						this.node.innerHTML = item.data.replace(/(\r\n|\r|\n)/g, "<br>");
						this.onChange();
					}.bind(this)
				});
			}

			return this.aiTextButton;
		},

		/**
		 * Disables edit mode
		 */
		disableEdit: function()
		{
			if (this.isEditable())
			{
				this.node.contentEditable = false;

				if (this.lastValue !== this.getValue())
				{
					this.onChange();
					this.lastValue = this.getValue();
				}

				if (this.isAllowInlineEdit())
				{
					this.node.setAttribute("title", escapeText(BX.Landing.Loc.getMessage("LANDING_TITLE_OF_TEXT_NODE")));
				}
			}
		},


		/**
		 * Gets form field
		 * @return {BX.Landing.UI.Field.Text}
		 */
		getField: function()
		{
			if (!this.field)
			{
				this.field = new BX.Landing.UI.Field.Text({
					selector: this.selector,
					title: this.manifest.name,
					content: this.node.innerHTML,
					textOnly: this.manifest.textOnly,
					bind: this.node
				});

				if (this.isHeader())
				{
					this.field.changeTagButton = this.getChangeTagButton();
				}
			}
			else
			{
				this.field.setValue(this.node.innerHTML);
				this.field.content = this.node.innerHTML;
			}

			return this.field;
		},


		/**
		 * Sets node value
		 * @param value
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			this.preventSave(preventSave);
			this.lastValue = this.isSavePrevented() ? this.getValue() : this.lastValue;
			this.node.innerHTML = value;
			this.onChange(false, preventHistory);
		},


		/**
		 * Gets node value
		 * @return {string}
		 */
		getValue: function()
		{
			if (this.node.querySelector('.landing-table-container') !== null)
			{
				const node = this.node.cloneNode(true);
				this.prepareTable(node);
				return textToPlaceholders(node.innerHTML);
			}
			return textToPlaceholders(this.node.innerHTML);
		},


		/**
		 * Checks that this node is header
		 * @return {boolean}
		 */
		isHeader: function()
		{
			return headerTagMatcher.test(this.node.nodeName);
		},

		/**
		 * Checks that this node is table
		 * @return {boolean}
		 */
		isTable: function(event)
		{
			let nodeIsTable = false;
			if (BX.Landing.Block.Node.Text.currentNode && event)
			{
				BX.Landing.Block.Node.Text.currentNode.node.querySelectorAll('.landing-table-container')
					.forEach(function(table) {
						if (table.contains(event.srcElement))
						{
							nodeIsTable = true;
						}
					})
			}
			return nodeIsTable;
		},

		/**
		 * Delete br tags in new table and add flag after this
		 */
		prepareNewTable: function(table)
		{
			table.querySelectorAll('br').forEach(function(tdTag) {
				tdTag.remove();
			})
			table.setAttribute('table-prepare', 'true');
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		},

		addTableButtons: function(event)
		{
			const buttons = [];
			let neededButtons = [];
			let setTd = [];
			const tableButtons = this.getTableButtons();
			const tableAlignButtons = [tableButtons[0], tableButtons[1], tableButtons[2], tableButtons[3]];
			const node = BX.Landing.Block.Node.Text.currentNode.node;
			let table = null;
			let isCell = false;
			let isButtonAddRow = false;
			let isButtonAddCol = false;
			let isNeedTablePanel = true;
			if (
				BX.Dom.hasClass(event.srcElement, 'landing-table')
				|| BX.Dom.hasClass(event.srcElement, 'landing-table-col-dnd')
			)
			{
				isNeedTablePanel = false;
			}
			if (BX.Dom.hasClass(event.srcElement, 'landing-table-row-add'))
			{
				isButtonAddRow = true;
			}
			if (BX.Dom.hasClass(event.srcElement, 'landing-table-col-add'))
			{
				isButtonAddCol = true;
			}
			let hideButtons = [];
			const nodeTableList = node.querySelectorAll('.landing-table');
			if (nodeTableList.length > 0)
			{
				nodeTableList.forEach(function(nodeTable) {
					if (nodeTable.contains(event.srcElement))
					{
						table = nodeTable;
						return true;
					}
				});
			}
			let isSelectedAll;

			tableButtons.forEach(function(tableButton) {
				tableButton['options']['srcElement'] = event.srcElement;
				tableButton['options']['node'] = node;
				tableButton['options']['table'] = table;
			});

			if (BX.Dom.hasClass(event.srcElement, 'landing-table-row-dnd'))
			{
				setTd = event.srcElement.parentNode.children;
				setTd = Array.from(setTd);
				if (this.getAmountTableRows(table) > 1)
				{
					neededButtons = [0, 1, 2, 3, 4, 5, 6];
				}
				else
				{
					neededButtons = [0, 1, 2, 3, 4, 5];
				}
				neededButtons.forEach(function(neededButton) {
					tableButtons[neededButton]['options']['target'] = 'row';
					tableButtons[neededButton]['options']['setTd'] = setTd;
					buttons.push(tableButtons[neededButton]);
				});
			}

			if (BX.Dom.hasClass(event.srcElement.parentNode, 'landing-table-col-dnd'))
			{
				const childNodes = event.srcElement.parentElement.parentElement.childNodes;
				const childNodesArray = Array.from(childNodes);
				const childNodesArrayPrepare = [];
				childNodesArray.forEach(function(childNode) {
					if (childNode.nodeType === 1)
					{
						childNodesArrayPrepare.push(childNode);
					}
				});
				const neededPosition = childNodesArrayPrepare.indexOf(event.srcElement.parentElement);
				const rows = event.srcElement.parentElement.parentElement.parentElement.childNodes;
				rows.forEach(function(row) {
					if (row.nodeType === 1)
					{
						const rowChildPrepare = [];
						row.childNodes.forEach(function(rowChildNode) {
							if (rowChildNode.nodeType === 1)
							{
								rowChildPrepare.push(rowChildNode);
							}
						});
						if (rowChildPrepare[neededPosition])
						{
							setTd.push(rowChildPrepare[neededPosition]);
						}
					}
				});
				if (this.getAmountTableCols(table) > 1)
				{
					neededButtons = [0, 1, 2, 3, 4, 5, 7];
				}
				else
				{
					neededButtons = [0, 1, 2, 3, 4, 5];
				}
				neededButtons.forEach(function(neededButton) {
					tableButtons[neededButton]['options']['target'] = 'col';
					tableButtons[neededButton]['options']['setTd'] = setTd;
					buttons.push(tableButtons[neededButton]);
				});
			}

			if (BX.Dom.hasClass(event.srcElement, 'landing-table-th-select-all'))
			{
				if (BX.Dom.hasClass(event.srcElement, 'landing-table-th-select-all-selected'))
				{
					isSelectedAll = true;
					const rows = event.srcElement.parentElement.parentElement.childNodes;
					rows.forEach(function(row) {
						row.childNodes.forEach(function(th) {
							setTd.push(th);
						})
					})
					neededButtons = [0, 1, 2, 3, 4, 5, 8, 9, 10];
					neededButtons.forEach(function(neededButton) {
						tableButtons[neededButton]['options']['target'] = 'table';
						tableButtons[neededButton]['options']['setTd'] = setTd;
						buttons.push(tableButtons[neededButton]);
					})
				}
				else
				{
					isSelectedAll = false;
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}
			}

			if (
				BX.Dom.hasClass(event.srcElement, 'landing-table-td')
				|| event.srcElement.closest('.landing-table-td') !== null
			)
			{
				setTd.push(event.srcElement);
				neededButtons = [3, 2, 1, 0];
				neededButtons.forEach(function(neededButton) {
					tableButtons[neededButton]['options']['target'] = 'cell';
					tableButtons[neededButton]['options']['setTd'] = setTd;
					tableButtons[neededButton].insertAfter = 'strikeThrough';
					buttons.push(tableButtons[neededButton]);
				});
				isCell = true;
				hideButtons = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'createTable', 'pasteTable'];
			}

			let activeAlignButtonId;
			const setActiveAlignButtonId = [];
			setTd.forEach(function(th) {
				if (th.nodeType === 1)
				{
					activeAlignButtonId = undefined;
					if (BX.Dom.hasClass(th, 'text-left'))
					{
						activeAlignButtonId = 'alignLeft';
					}
					if (BX.Dom.hasClass(th, 'text-center'))
					{
						activeAlignButtonId = 'alignCenter';
					}
					if (BX.Dom.hasClass(th, 'text-right'))
					{
						activeAlignButtonId = 'alignRight';
					}
					if (BX.Dom.hasClass(th, 'text-justify'))
					{
						activeAlignButtonId = 'alignJustify';
					}
					setActiveAlignButtonId.push(activeAlignButtonId);
				}
			});
			let count = 0;
			let isIdentical = true;
			while (count < setActiveAlignButtonId.length && isIdentical)
			{
				if (count > 0)
				{
					if (setActiveAlignButtonId[count] !== setActiveAlignButtonId[count - 1])
					{
						isIdentical = false;
					}
				}
				count++;
			}
			if (isIdentical)
			{
				activeAlignButtonId = setActiveAlignButtonId[0];
			}
			else
			{
				activeAlignButtonId = undefined;
			}
			if (activeAlignButtonId)
			{
				tableAlignButtons.forEach(function(tableAlignButton) {
					if (tableAlignButton.id === activeAlignButtonId)
					{
						BX.Dom.addClass(tableAlignButton.layout, 'landing-ui-active');
					}
				});
			}

			if (buttons[0] && buttons[1] && buttons[2] && buttons[3])
			{
				buttons[0]['options']['alignButtons'] = tableAlignButtons;
				buttons[1]['options']['alignButtons'] = tableAlignButtons;
				buttons[2]['options']['alignButtons'] = tableAlignButtons;
				buttons[3]['options']['alignButtons'] = tableAlignButtons;
			}

			if (!this.manifest.textOnly)
			{
				if (isNeedTablePanel)
				{
					if (!isButtonAddRow && !isButtonAddCol && table)
					{
						if (!isCell)
						{
							if (isSelectedAll === false)
							{
								BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
							}
							else
							{
								BX.Landing.UI.Panel.EditorPanel.getInstance().show(table.parentNode, null, buttons, true);
							}
						}
						else
						{
							BX.Landing.UI.Panel.EditorPanel.getInstance().show(table.parentNode, null, buttons, true, hideButtons);
						}
					}
				}
				else
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}
			}
		},

		/**
		 * Gets change tag button
		 * @return {BX.Landing.UI.Button.ChangeTag}
		 */
		getChangeTagButton: function()
		{
			if (!this.changeTagButton)
			{
				this.changeTagButton = new BX.Landing.UI.Button.ChangeTag("changeTag", {
					html: "<span class=\"landing-ui-icon-editor-"+this.node.nodeName.toLowerCase()+"\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_CHANGE_TAG")},
					onChange: this.onChangeTag.bind(this)
				});
			}

			this.changeTagButton.insertAfter = "unlink";

			this.changeTagButton.activateItem(this.node.nodeName);

			return this.changeTagButton;
		},

		getTableButtons: function()
		{
			this.buttons = [];
			this.buttons.push(
				new BX.Landing.UI.Button.AlignTable("alignLeft", {
					html: "<span class=\"landing-ui-icon-editor-left\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_LEFT")},
				}),
				new BX.Landing.UI.Button.AlignTable("alignCenter", {
					html: "<span class=\"landing-ui-icon-editor-center\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_CENTER")},
				}),
				new BX.Landing.UI.Button.AlignTable("alignRight", {
					html: "<span class=\"landing-ui-icon-editor-right\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_RIGHT")},
				}),
				new BX.Landing.UI.Button.AlignTable("alignJustify", {
					html: "<span class=\"landing-ui-icon-editor-justify\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_JUSTIFY")},
				}),
				new BX.Landing.UI.Button.TableColorAction("tableTextColor", {
					text: BX.Landing.Loc.getMessage("EDITOR_ACTION_SET_FORE_COLOR"),
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_COLOR")},
				}),
				new BX.Landing.UI.Button.TableColorAction("tableBgColor", {
					html: "<i class=\"landing-ui-icon-editor-fill-color\"></i>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_CELL_BG")},
				}),
				new BX.Landing.UI.Button.DeleteElementTable("deleteRow", {
					html: "<span class=\"landing-ui-icon-editor-delete\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_DELETE_ROW_TABLE")},
				}),
				new BX.Landing.UI.Button.DeleteElementTable("deleteCol", {
					html: "<span class=\"landing-ui-icon-editor-delete\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_DELETE_COL_TABLE")},
				}),
				new BX.Landing.UI.Button.StyleTable("styleTable", {
					html: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_STYLE")
						+ "<i class=\"fas fa-chevron-down g-ml-8\"></i>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_STYLE")},
				}),
				new BX.Landing.UI.Button.CopyTable("copyTable", {
					text: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_COPY"),
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_COPY")},
				}),
				new BX.Landing.UI.Button.DeleteTable("deleteTable", {
					html: "<span class=\"landing-ui-icon-editor-delete\"></span>",
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TABLE_DELETE")},
				}),
			);

			return this.buttons;
		},


		/**
		 * Handles change tag event
		 * @param value
		 * @param {?boolean} [preventHistory = false]
		 */
		onChangeTag: function(value, preventHistory)
		{
			this.node = changeTagName(this.node, value);

			this.node.addEventListener("mousedown", this.onMousedown);
			this.node.addEventListener("click", this.onClick);
			this.node.addEventListener("paste", this.onPaste);
			this.node.addEventListener("drop", this.onDrop);
			this.node.addEventListener("input", this.onInput);
			this.node.addEventListener("keydown", this.onInput);

			if (!this.getField().isEditable() && !preventHistory)
			{
				this.disableEdit();
				this.enableEdit();
			}

			const data = {};
			data[this.selector] = value;

			if (!preventHistory)
			{
				this.changeOptionsHandler(data)
					.then(() => {
						BX.Landing.History.getInstance().push();
					})
			}
		},

		getAmountTableCols: function(table)
		{
			return table.querySelectorAll('.landing-table-col-dnd').length;
		},

		getAmountTableRows: function(table)
		{
			return table.querySelectorAll('.landing-table-row-dnd').length;
		},

		prepareTable: function(node)
		{
			const setClassesForRemove = [
				'table-selected-all',
				'landing-table-th-select-all-selected',
				'landing-table-cell-selected',
				'landing-table-row-selected',
				'landing-table-th-selected',
				'landing-table-th-selected-cell',
				'landing-table-th-selected-top',
				'landing-table-th-selected-x',
				'landing-table-tr-selected-left',
				'landing-table-tr-selected-y',
				'landing-table-col-selected',
				'landing-table-tr-selected',
				'table-selected-all-right',
				'table-selected-all-bottom',
			];
			setClassesForRemove.forEach(function(className) {
				node.querySelectorAll('.' + className).forEach(function(element){
					BX.Dom.removeClass(element, className);
				})
			})
			return node;
		},

		onBackspaceDown: function(event) {
			const selection = window.getSelection();
			const position = selection.getRangeAt(0).startOffset;
			if (position === 0)
			{
				let focusNode = selection.focusNode;
				if (!BX.Type.isNil(focusNode) && focusNode.nodeType !== 3)
				{
					if (focusNode.firstChild.nodeType === 3 && focusNode.firstChild.firstChild.nodeType === 3)
					{
						focusNode = focusNode.firstChild.firstChild;
					}
					else if (focusNode.firstChild.nodeType !== 3)
					{
						focusNode = focusNode.firstChild;
					}
					else
					{
						focusNode = null;
					}
				}
				if (focusNode)
				{
					const focusNodeParent = focusNode.parentNode;
					const allowedNodeName = ['BLOCKQUOTE', 'UL'];
					if (focusNodeParent && allowedNodeName.includes(focusNodeParent.nodeName))
					{
						const focusNodeContainer = document.createElement('div');
						focusNodeContainer.append(focusNode);
						focusNodeParent.append(focusNodeContainer);
					}
					let contentNode = focusNode.parentNode.parentNode;
					while (contentNode && !allowedNodeName.includes(contentNode.nodeName))
					{
						contentNode = contentNode.parentNode;
					}
					if (contentNode && contentNode.childNodes.length === 1)
					{
						contentNode.after(focusNode.parentNode);
						contentNode.remove();

						event.preventDefault();
					}
				}
			}
		},

		isLinkPasted: function(text) {
			const reg = /^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:[-a-zA-Z0-9()@:%_\+.~#?&\/=]*)$/;
			return !!text.match(reg);
		},

		prepareToLink: function(text)
		{
			return "<a class='g-bg-transparent' href='" + text + "' target='_blank'> " + text + " </a>";
		},
	};

})();