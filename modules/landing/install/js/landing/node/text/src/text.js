import {Type} from 'main.core';
import {Node} from 'landing.node';

export class Text extends Node
{
	constructor()
	{
		super();

		this.escapeText = BX.Landing.Utils.escapeText;
		this.headerTagMatcher = BX.Landing.Utils.Matchers.headerTag;
		this.changeTagName = BX.Landing.Utils.changeTagName;
		this.textToPlaceholders = BX.Landing.Utils.textToPlaceholders;

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
	}

	static currentNode = [];

	/**
	 * Handles allow inline edit event
	 */
	onAllowInlineEdit()
	{
		// Show title "Click to edit" for node
		this.node.setAttribute("title", this.escapeText(BX.Landing.Loc.getMessage("LANDING_TITLE_OF_TEXT_NODE")));
	}

	/**
	 * Handles change event
	 * @param {boolean} [preventAdjustPosition]
	 * @param {boolean} [preventHistory]
	 */
	onChange(preventAdjustPosition, preventHistory)
	{
		this.superClass.onChange.call(this, arguments);
		if (!preventAdjustPosition)
		{
			BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(this.node);
		}
		if (!preventHistory)
		{
			// todo: old or new extention use?
			BX.Landing.History.getInstance().push();
		}
	}

	onKeyDown()
	{
		if (event.code === 'Backspace')
		{
			this.onBackspaceDown(event);
		}
		this.onInput(event);
	}

	onInput(event)
	{
		clearTimeout(this.inputTimeout);

		var key = event.keyCode || event.which;

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
			var tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'));
			if (event.srcElement.textContent === ''
				&& event.srcElement.classList.contains('landing-table-td')
				&& tableFontSize < this.tableBaseFontSize)
			{
				event.srcElement.classList.add('landing-table-td-height');
			}
			else
			{
				event.srcElement.classList.remove('landing-table-td-height');
			}
		}
	}

	/**
	 * Handles escape press event
	 */
	onEscapePress()
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
	}

	/**
	 * Handles drop event on this node
	 *
	 * @param {DragEvent} event
	 */
	onDrop(event)
	{
		// Prevents drag and drop any content into editor area
		event.preventDefault();
	}

	/**
	 * Handles paste event on this node
	 *
	 * @param {ClipboardEvent} event
	 * @param {function} event.preventDefault
	 * @param {object} event.clipboardData
	 */
	onPaste(event)
	{
		event.preventDefault();

		if (event.clipboardData && event.clipboardData.getData)
		{
			var sourceText = event.clipboardData.getData("text/plain");
			var encodedText = BX.Text.encode(sourceText);
			var formattedHtml = encodedText.replace(new RegExp('\n', 'g'), "<br>");
			document.execCommand("insertHTML", false, formattedHtml);
		}
		else
		{
			// ie11
			var text = window.clipboardData.getData("text");
			document.execCommand("paste", true, BX.Text.encode(text));
		}

		this.onChange();
	}

	/**
	 * Handles click on document
	 */
	onDocumentClick(event)
	{
		if (this.isEditable() && !this.fromNode)
		{
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			this.disableEdit();
		}

		this.fromNode = false;
	}

	onMousedown(event)
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
					var tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'));
					if (event.srcElement.textContent === ''
						&& event.srcElement.classList.contains('landing-table-td')
						&& tableFontSize < this.tableBaseFontSize)
					{
						event.srcElement.classList.add('landing-table-td-height');
					}
					else
					{
						event.srcElement.classList.remove('landing-table-td-height');
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
						})
					}
				}

				BX.Landing.UI.Tool.ColorPicker.hideAll();
			}

			requestAnimationFrame(function() {
				if (event.target.nodeName === "A" ||
					event.target.parentElement.nodeName === "A")
				{
					var range = document.createRange();
					range.selectNode(event.target);
					window.getSelection().removeAllRanges();
					window.getSelection().addRange(range);
				}
			});
		}
	}

	onMouseup()
	{
		setTimeout(function() {
			this.fromNode = false;
		}.bind(this), 10);
	}

	/**
	 * Click on field - switch edit mode.
	 */
	onClick(event)
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
			var range = document.createRange();
			range.selectNode(event.target);
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(range);
		}
	}

	/**
	 * Checks that is editable
	 * @return {boolean}
	 */
	isEditable()
	{
		return this.node.isContentEditable;
	}

	/**
	 * Enables edit mode
	 */
	enableEdit()
	{
		var currentNode = BX.Landing.Block.Node.Text.currentNode;
		if (currentNode)
		{
			var node = BX.Landing.Block.Node.Text.currentNode.node;
			var nodeTableContainerList = node.querySelectorAll('.landing-table-container');
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

			if (this.isHeader())
			{
				this.buttons.push(this.getChangeTagButton());
				this.getChangeTagButton().onChangeHandler = this.onChangeTag.bind(this);
			}

			this.lastValue = this.getValue();
			this.node.contentEditable = true;

			this.node.setAttribute("title", "");
		}
	}

	/**
	 * Gets design button for editor
	 * @return {BX.Landing.UI.Button.Design}
	 */
	getDesignButton()
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
	}

	/**
	 * Disables edit mode
	 */
	disableEdit()
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
				this.node.setAttribute("title", this.escapeText(BX.Landing.Loc.getMessage("LANDING_TITLE_OF_TEXT_NODE")));
			}
		}
	}

	/**
	 * Gets form field
	 * @return {BX.Landing.UI.Field.Text}
	 */
	getField()
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
	}

	/**
	 * Sets node value
	 * @param value
	 * @param {?boolean} [preventSave = false]
	 * @param {?boolean} [preventHistory = false]
	 */
	setValue(value, preventSave, preventHistory)
	{
		this.preventSave(preventSave);
		this.lastValue = this.isSavePrevented() ? this.getValue() : this.lastValue;
		this.node.innerHTML = value;
		this.onChange(false, preventHistory);
	}

	/**
	 * Gets node value
	 * @return {string}
	 */
	getValue()
	{
		if (this.node.querySelector('.landing-table-container') !== null)
		{
			var node = this.node.cloneNode(true);
			this.prepareTable(node);
			return this.textToPlaceholders(node.innerHTML);
		}
		return this.textToPlaceholders(this.node.innerHTML);
	}

	/**
	 * Checks that this node is header
	 * @return {boolean}
	 */
	isHeader()
	{
		return this.headerTagMatcher.test(this.node.nodeName);
	}

	/**
	 * Checks that this node is table
	 * @return {boolean}
	 */
	isTable(event)
	{
		var nodeIsTable = false;
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
	}

	/**
	 * Delete br tags in new table and add flag after this
	 */
	prepareNewTable()
	{
		table.querySelectorAll('br').forEach(function(tdTag) {
			tdTag.remove();
		})
		table.setAttribute('table-prepare', 'true');
		BX.Landing.Block.Node.Text.currentNode.onChange(true);
	}

	addTableButtons(event)
	{
		var buttons = [];
		var neededButtons = [];
		var setTd = [];
		var tableButtons = this.getTableButtons();
		var tableAlignButtons = [tableButtons[0], tableButtons[1], tableButtons[2], tableButtons[3]];
		var node = BX.Landing.Block.Node.Text.currentNode.node;
		var table = null;
		var isCell = false;
		var isButtonAddRow = false;
		var isButtonAddCol = false;
		var isNeedTablePanel = true;
		if (event.srcElement.classList.contains('landing-table')
			|| event.srcElement.classList.contains('landing-table-col-dnd'))
		{
			isNeedTablePanel = false;
		}
		if (event.srcElement.classList.contains('landing-table-row-add'))
		{
			isButtonAddRow = true;
		}
		if (event.srcElement.classList.contains('landing-table-col-add'))
		{
			isButtonAddCol = true;
		}
		var hideButtons = [];
		var nodeTableList = node.querySelectorAll('.landing-table');
		if (nodeTableList.length > 0)
		{
			nodeTableList.forEach(function(nodeTable) {
				if (nodeTable.contains(event.srcElement))
				{
					table = nodeTable;
					return true;
				}
			})
		}

		tableButtons.forEach(function(tableButton){
			tableButton['options']['srcElement'] = event.srcElement;
			tableButton['options']['node'] = node;
			tableButton['options']['table'] = table;
		})

		if (event.srcElement.classList.contains('landing-table-row-dnd'))
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
			neededButtons.forEach(function(neededButon) {
				tableButtons[neededButon]['options']['target'] = 'row';
				tableButtons[neededButon]['options']['setTd'] = setTd;
				buttons.push(tableButtons[neededButon]);
			})
		}

		if (event.srcElement.parentNode.classList.contains('landing-table-col-dnd'))
		{
			var childNodes = event.srcElement.parentElement.parentElement.childNodes;
			var childNodesArray = Array.from(childNodes);
			var childNodesArrayPrepare = [];
			childNodesArray.forEach(function(childNode) {
				if (childNode.nodeType === 1)
				{
					childNodesArrayPrepare.push(childNode);
				}
			})
			var neededPosition = childNodesArrayPrepare.indexOf(event.srcElement.parentElement);
			var rows = event.srcElement.parentElement.parentElement.parentElement.childNodes;
			rows.forEach(function(row) {
				if (row.nodeType === 1)
				{
					var rowChildPrepare = [];
					row.childNodes.forEach(function(rowChildNode) {
						if (rowChildNode.nodeType === 1)
						{
							rowChildPrepare.push(rowChildNode);
						}
					})
					if (rowChildPrepare[neededPosition])
					{
						setTd.push(rowChildPrepare[neededPosition]);
					}
				}
			})
			if (this.getAmountTableCols(table) > 1)
			{
				neededButtons = [0, 1, 2, 3, 4, 5, 7];
			}
			else
			{
				neededButtons = [0, 1, 2, 3, 4, 5];
			}
			neededButtons.forEach(function(neededButon) {
				tableButtons[neededButon]['options']['target'] = 'col';
				tableButtons[neededButon]['options']['setTd'] = setTd;
				buttons.push(tableButtons[neededButon]);
			})
		}

		if (event.srcElement.classList.contains('landing-table-th-select-all'))
		{
			var isSelectedAll;
			if (event.srcElement.classList.contains('landing-table-th-select-all-selected'))
			{
				isSelectedAll = true;
				var rows = event.srcElement.parentElement.parentElement.childNodes;
				rows.forEach(function(row) {
					row.childNodes.forEach(function(th) {
						setTd.push(th);
					})
				})
				neededButtons = [0, 1, 2, 3, 4, 5, 8, 9, 10];
				neededButtons.forEach(function(neededButon) {
					tableButtons[neededButon]['options']['target'] = 'table';
					tableButtons[neededButon]['options']['setTd'] = setTd;
					buttons.push(tableButtons[neededButon]);
				})
			}
			else
			{
				isSelectedAll = false;
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			}
		}

		if (event.srcElement.classList.contains('landing-table-td'))
		{
			setTd.push(event.srcElement);
			neededButtons = [3, 2, 1, 0];
			neededButtons.forEach(function(neededButon) {
				tableButtons[neededButon]['options']['target'] = 'cell';
				tableButtons[neededButon]['options']['setTd'] = setTd;
				tableButtons[neededButon].insertAfter = 'strikeThrough';
				buttons.push(tableButtons[neededButon]);
			})
			isCell = true;
			hideButtons = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'createTable', 'pasteTable'];
		}

		var activeAlignButtonId;
		var setActiveAlignButtonId = [];
		setTd.forEach(function(th) {
			if (th.nodeType === 1)
			{
				activeAlignButtonId = undefined;
				if (th.classList.contains('text-left'))
				{
					activeAlignButtonId = 'alignLeft';
				}
				if (th.classList.contains('text-center'))
				{
					activeAlignButtonId = 'alignCenter';
				}
				if (th.classList.contains('text-right'))
				{
					activeAlignButtonId = 'alignRight';
				}
				if (th.classList.contains('text-justify'))
				{
					activeAlignButtonId = 'alignJustify';
				}
				setActiveAlignButtonId.push(activeAlignButtonId);
			}
		})
		var count = 0;
		var isIdentical = true;
		while (count < setActiveAlignButtonId.length && isIdentical) {
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
					tableAlignButton.layout.classList.add('landing-ui-active');
				}
			})
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
						isSelectedAll = true;
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
	}

	/**
	 * Gets change tag button
	 * @return {BX.Landing.UI.Button.ChangeTag}
	 */
	getChangeTagButton()
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
	}

	getTableButtons()
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
			new BX.Landing.UI.Button.ColorAction("tableTextColor", {
				text: BX.Landing.Loc.getMessage("EDITOR_ACTION_SET_FORE_COLOR"),
				attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_COLOR")},
			}),
			new BX.Landing.UI.Button.ColorAction("tableBgColor", {
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
			})
		);
		return this.buttons;
	}

	/**
	 * Handles change tag event
	 * @param value
	 */
	onChangeTag(value)
	{
		this.node = this.changeTagName(this.node, value);

		this.node.addEventListener("mousedown", this.onMousedown);
		this.node.addEventListener("click", this.onClick);
		this.node.addEventListener("paste", this.onPaste);
		this.node.addEventListener("drop", this.onDrop);
		this.node.addEventListener("input", this.onInput);
		this.node.addEventListener("keydown", this.onInput);

		if (!this.getField().isEditable())
		{
			this.disableEdit();
			this.enableEdit();
		}

		var data = {};
		data[this.selector] = value;
		this.changeOptionsHandler(data);
	}

	getAmountTableCols(table)
	{
		return table.querySelectorAll('.landing-table-col-dnd').length;
	}

	getAmountTableRows(table)
	{
		return table.querySelectorAll('.landing-table-row-dnd').length;
	}

	prepareTable(node)
	{
		var setClassesForRemove = [
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
				element.classList.remove(className);
			})
		})
		return node;
	}

	onBackspaceDown()
	{
		var selection = window.getSelection();
		var position = selection.getRangeAt(0).startOffset;
		if (position === 0)
		{
			var focusNode = selection.focusNode;
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
				var focusNodeParent = focusNode.parentNode;
				var allowedNodeName = ['BLOCKQUOTE', 'UL'];
				if (focusNodeParent && allowedNodeName.includes(focusNodeParent.nodeName))
				{
					var focusNodeContainer = document.createElement('div');
					focusNodeContainer.append(focusNode);
					focusNodeParent.append(focusNodeContainer);
				}
				var contentNode = focusNode.parentNode.parentNode;
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
	}
}