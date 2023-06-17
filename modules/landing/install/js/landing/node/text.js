;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var escapeText = BX.Landing.Utils.escapeText;
	var headerTagMatcher = BX.Landing.Utils.Matchers.headerTag;
	var changeTagName = BX.Landing.Utils.changeTagName;
	var textToPlaceholders = BX.Landing.Utils.textToPlaceholders;


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
				var sourceText = event.clipboardData.getData("text/plain");
				var encodedText = BX.Text.encode(sourceText);
				if (this.isLinkPasted(sourceText))
				{
					encodedText = this.prepareToLink(encodedText);
				}
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
				var range = document.createRange();
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
				this.aiTextButton = new BX.Landing.UI.Button.AiText("ai_text", {
					html: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_AI_TEXT"),
					attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_AI_TEXT")},
					onClick: function() {
						BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

						let repository = BX.Landing.Main.getInstance()["options"]["blocks"];
						let sections = this.manifest.sections;
						let startMessage = '';
						let engineParameters = {
						//	assistant_text: "friendly business tone"
						};

						// retrieve startMessage from settings' meta
						for (let i = 0, c = sections.length; i < c; i++)
						{
							let section = sections[i];
							if (repository[section] && repository[section]["meta"])
							{
								if (repository[section]["meta"]["ai_text_placeholder"])
								{
									startMessage = repository[section]["meta"]["ai_text_placeholder"];
								}
								if (repository[section]["meta"]["ai_text_max_tokens"])
								{
									engineParameters['max_tokens'] = parseInt(repository[section]["meta"]["ai_text_max_tokens"]);
								}
								/*if (repository[section]["meta"]["ai_text_assistant_text"])
								{
									engineParameters['assistant_text'] = repository[section]["meta"]["ai_text_assistant_text"];
								}*/
							}
						}

						if (!this.aiTextPicker)
						{
							let siteId = BX.Landing.Main.getInstance()["options"]["site_id"];
							let picker = top.BX.AI ? top.BX.AI.Picker : BX.AI.Picker;

							this.aiTextPicker = new picker({
								/*startMessage: (startMessage.length > 0)
									? startMessage
									: "write {text, reviews,benefits} {for website}, {flower shop}, {family business}",*/
								moduleId: "landing",
								contextId: "text_site_" + siteId,
								analyticLabel: 'landing_text',
								history: true,
								onSelect: function (item) {
									this.node.innerHTML = item.data.replace(/(\r\n|\r|\n)/g, '<br>');
									this.onChange();
								}.bind(this),
								onTariffRestriction: function() {
									BX.UI.InfoHelper.show("limit_sites_TextAssistant_AI");
								},
							});

							this.aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text)
						}

						this.aiTextPicker.setEngineParameters(engineParameters);

						this.aiTextPicker.text();

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
				neededButtons.forEach(function(neededButton) {
					tableButtons[neededButton]['options']['target'] = 'row';
					tableButtons[neededButton]['options']['setTd'] = setTd;
					buttons.push(tableButtons[neededButton]);
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
				neededButtons.forEach(function(neededButton) {
					tableButtons[neededButton]['options']['target'] = 'col';
					tableButtons[neededButton]['options']['setTd'] = setTd;
					buttons.push(tableButtons[neededButton]);
				})
			}

			if (event.srcElement.classList.contains('landing-table-th-select-all'))
			{
				var isSelectedAll;
				if (event.srcElement.classList.contains('landing-table-th-select-all-selected'))
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
				|| this.hasParentWithClass(event.srcElement, 'landing-table-td')
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

			var activeAlignButtonId;
			let setActiveAlignButtonId = [];
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
		 * Checking whether an element is a descendant of a parent of any level with the desired class
		 * @return {boolean}
		 */
		hasParentWithClass: function(element, className)
		{
			let parent = element.parentNode;

			while (parent !== null)
			{
				if (parent.classList && BX.Dom.hasClass(parent, className))
				{
					return true;
				}
				parent = parent.parentNode;
			}

			return false;
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

			var data = {};
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
		},

		onBackspaceDown: function(event) {
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
		},

		isLinkPasted: function(text) {
			var reg = /^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:[-a-zA-Z0-9()@:%_\+.~#?&\/=]*)$/;
			return !!text.match(reg);
		},

		prepareToLink: function(text)
		{
			return "<a class='g-bg-transparent' href='" + text + "' target='_blank'> " + text + " </a>";
		},
	};

})();