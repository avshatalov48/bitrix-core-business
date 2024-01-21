this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_node_base,landing_node_tableeditor) {
	'use strict';

	const escapeText = BX.Landing.Utils.escapeText;
	const matchers = BX.Landing.Utils.Matchers;
	const changeTagName = BX.Landing.Utils.changeTagName;
	const textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
	class Text extends landing_node_base.Base {
	  constructor(options) {
	    super(options);
	    this.currentNode = null;
	    this.type = 'text';
	    this.tableBaseFontSize = '22';
	    this.onClick = this.onClick.bind(this);
	    this.onPaste = this.onPaste.bind(this);
	    this.onDrop = this.onDrop.bind(this);
	    this.onInput = this.onInput.bind(this);
	    this.onKeyDown = this.onKeyDown.bind(this);
	    this.onMousedown = this.onMousedown.bind(this);
	    this.onMouseup = this.onMouseup.bind(this);

	    // Bind on node events
	    this.bindEvents(this.node);
	    main_core.Event.bind(document, 'mouseup', this.onMouseup);
	  }
	  bindEvents(node) {
	    main_core.Event.bind(node, 'mousedown', this.onMousedown);
	    main_core.Event.bind(node, 'click', this.onClick);
	    main_core.Event.bind(node, 'paste', this.onPaste);
	    main_core.Event.bind(node, 'drop', this.onDrop);
	    main_core.Event.bind(node, 'input', this.onInput);
	    main_core.Event.bind(node, 'keydown', this.onKeyDown);
	  }

	  /**
	   * Handles allow inline edit event
	   */
	  onAllowInlineEdit() {
	    // Show title "Click to edit" for node
	    this.node.setAttribute('title', escapeText(BX.Landing.Loc.getMessage('LANDING_TITLE_OF_TEXT_NODE')));
	  }

	  /**
	   * Handles change event
	   * @param {boolean} [preventAdjustPosition]
	   * @param {?boolean} [preventHistory = false]
	   */
	  onChange(preventAdjustPosition, preventHistory) {
	    super.onChange.call(this, preventHistory);
	    if (!preventAdjustPosition) {
	      BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(this.node);
	    }
	    if (!preventHistory) {
	      BX.Landing.History.getInstance().push();
	    }
	  }
	  onKeyDown(event) {
	    if (event.code === 'Backspace') {
	      this.onBackspaceDown(event);
	    }
	    this.onInput(event);
	  }
	  onInput(event) {
	    clearTimeout(this.inputTimeout);
	    const key = event.keyCode || event.which;
	    if (!(key === 90 && (/win/i.test(top.window.navigator.userAgent) ? event.ctrlKey : event.metaKey))) {
	      this.inputTimeout = setTimeout(() => {
	        if (this.lastValue !== this.getValue()) {
	          this.onChange(true);
	          this.lastValue = this.getValue();
	        }
	      }, 400);
	    }
	    if (this.isTable(event)) {
	      const tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'), 10);
	      if (event.srcElement.textContent === '' && BX.Dom.hasClass(event.srcElement, 'landing-table-td') && tableFontSize < this.tableBaseFontSize) {
	        BX.Dom.addClass(event.srcElement, 'landing-table-td-height');
	      } else {
	        BX.Dom.removeClass(event.srcElement, 'landing-table-td-height');
	      }
	    }
	  }

	  /**
	   * Handles escape press event
	   */
	  onEscapePress() {
	    // Hide editor by press on Escape button
	    if (this.isEditable()) {
	      if (this === this.currentNode) {
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
	  onDrop(event) {
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
	  onPaste(event) {
	    event.preventDefault();
	    if (event.clipboardData && event.clipboardData.getData) {
	      const sourceText = event.clipboardData.getData('text/plain');
	      let encodedText = BX.Text.encode(sourceText);
	      if (this.isLinkPasted(sourceText)) {
	        encodedText = this.prepareToLink(encodedText);
	      }
	      const formattedHtml = encodedText.replaceAll('\n', '<br>');
	      document.execCommand('insertHTML', false, formattedHtml);
	    } else {
	      // ie11
	      const text = window.clipboardData.getData('text');
	      document.execCommand('paste', true, BX.Text.encode(text));
	    }
	    this.onChange();
	  }

	  /**
	   * Handles click on document
	   */
	  onDocumentClick(event) {
	    if (this.isEditable() && !this.fromNode) {
	      BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	      this.disableEdit();
	    }
	    this.fromNode = false;
	  }
	  onMousedown(event) {
	    BX.Event.EventEmitter.emit('BX.Landing.Node.Text:onMousedown');
	    if (!this.manifest.group) {
	      this.fromNode = true;
	      if (this.manifest.allowInlineEdit !== false && BX.Landing.Main.getInstance().isControlsEnabled()) {
	        event.stopPropagation();
	        this.enableEdit();
	        if (this.isTable(event)) {
	          this.disableEdit();
	          this.currentNode.node.querySelectorAll('.landing-table-container').forEach(table => {
	            if (!table.hasAttribute('table-prepare')) {
	              this.prepareNewTable(table);
	            }
	          });
	          const tableFontSize = parseInt(window.getComputedStyle(event.srcElement).getPropertyValue('font-size'), 10);
	          if (event.srcElement.textContent === '' && BX.Dom.hasClass(event.srcElement, 'landing-table-td') && tableFontSize < this.tableBaseFontSize) {
	            BX.Dom.addClass(event.srcElement, 'landing-table-td-height');
	          } else {
	            BX.Dom.removeClass(event.srcElement, 'landing-table-td-height');
	          }
	        } else {
	          if (!this.manifest.textOnly && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown()) {
	            BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.node, null, this.buttons);
	          }
	          if (this.nodeTableContainerList) {
	            this.nodeTableContainerList.forEach(tableContainer => {
	              tableContainer.tableEditor.unselect(tableContainer.tableEditor);
	            });
	          }
	        }
	        BX.Landing.UI.Tool.ColorPicker.hideAll();
	      }
	      requestAnimationFrame(() => {
	        if (event.target.nodeName === 'A' || event.target.parentElement.nodeName === 'A') {
	          const range = document.createRange();
	          range.selectNode(event.target);
	          window.getSelection().removeAllRanges();
	          window.getSelection().addRange(range);
	        }
	      });
	    }
	  }
	  onMouseup() {
	    setTimeout(() => {
	      this.fromNode = false;
	    }, 10);
	  }

	  /**
	   * Click on field - switch edit mode.
	   */
	  onClick(event) {
	    if (this.isTable(event)) {
	      this.addTableButtons(event);
	    }
	    event.stopPropagation();
	    event.preventDefault();
	    this.fromNode = false;
	    if (event.target.nodeName === 'A' || event.target.parentElement.nodeName === 'A') {
	      const range = document.createRange();
	      range.selectNode(event.target);
	      window.getSelection().removeAllRanges();
	      window.getSelection().addRange(range);
	    }
	  }

	  /**
	   * Checks that is editable
	   * @return {boolean}
	   */
	  isEditable() {
	    return this.node.isContentEditable;
	  }

	  /**
	   * Enables edit mode
	   */
	  enableEdit() {
	    const currentNode = this.currentNode;
	    if (currentNode) {
	      const node = this.currentNode.node;
	      const nodeTableContainerList = node.querySelectorAll('.landing-table-container');
	      if (nodeTableContainerList.length > 0) {
	        nodeTableContainerList.forEach(nodeTableContainer => {
	          if (!nodeTableContainer.tableEditor) {
	            nodeTableContainer.tableEditor = new landing_node_tableeditor.TableEditor(nodeTableContainer, this.currentNode);
	          }
	        });
	        this.nodeTableContainerList = nodeTableContainerList;
	      }
	    }
	    if (!this.isEditable() && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown()) {
	      if (this !== this.currentNode && this.currentNode !== null) {
	        this.disableEdit();
	      }
	      this.currentNode = this;
	      BX.Landing.Node.Text.currentNode = this.currentNode;
	      this.buttons = [];
	      this.buttons.push(this.getDesignButton());
	      if (this.isHeader()) {
	        this.buttons.push(this.getChangeTagButton());
	        this.getChangeTagButton().onChangeHandler = this.onChangeTag.bind(this);
	      }
	      this.lastValue = this.getValue();
	      this.node.contentEditable = true;
	      this.node.setAttribute('title', '');
	    }
	  }

	  /**
	   * Gets design button for editor
	   * @return {BX.Landing.UI.Button.Design}
	   */
	  getDesignButton() {
	    if (!this.designButton) {
	      this.designButton = new BX.Landing.UI.Button.Design('design', {
	        html: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_DESIGN'),
	        attrs: {
	          title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_DESIGN')
	        },
	        onClick: function () {
	          BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	          this.disableEdit();
	          this.onDesignShow(this.manifest.code);
	        }.bind(this)
	      });
	    }
	    this.designButton.insertBefore = 'ai_copilot';
	    return this.designButton;
	  }

	  /**
	   * Disables edit mode
	   */
	  disableEdit() {
	    if (this.isEditable()) {
	      this.node.contentEditable = false;
	      if (this.lastValue !== this.getValue()) {
	        this.onChange();
	        this.lastValue = this.getValue();
	      }
	      if (this.isAllowInlineEdit()) {
	        this.node.setAttribute('title', escapeText(BX.Landing.Loc.getMessage('LANDING_TITLE_OF_TEXT_NODE')));
	      }
	    }
	  }

	  /**
	   * Gets form field
	   * @return {BX.Landing.UI.Field.Text}
	   */
	  getField() {
	    if (this.field) {
	      this.field.setValue(this.node.innerHTML);
	      this.field.content = this.node.innerHTML;
	    } else {
	      this.field = new BX.Landing.UI.Field.Text({
	        selector: this.selector,
	        title: this.manifest.name,
	        content: this.node.innerHTML,
	        textOnly: this.manifest.textOnly,
	        bind: this.node
	      });
	      if (this.isHeader()) {
	        this.field.changeTagButton = this.getChangeTagButton();
	      }
	    }
	    return this.field;
	  }

	  /**
	   * Sets node value
	   * @param value
	   * @param {?boolean} [preventSave = false]
	   * @param {?boolean} [preventHistory = false]
	   */
	  setValue(value, preventSave, preventHistory) {
	    this.preventSave(preventSave);
	    this.lastValue = this.isSavePrevented() ? this.getValue() : this.lastValue;
	    this.node.innerHTML = value;
	    this.onChange(false, preventHistory);
	  }

	  /**
	   * Gets node value
	   * @return {string}
	   */
	  getValue() {
	    if (this.node.querySelector('.landing-table-container') !== null) {
	      const node = this.node.cloneNode(true);
	      this.prepareTable(node);
	      return textToPlaceholders(node.innerHTML);
	    }
	    return textToPlaceholders(this.node.innerHTML);
	  }

	  /**
	   * Checks that this node is header
	   * @return {boolean}
	   */
	  isHeader() {
	    return matchers.headerTag.test(this.node.nodeName);
	  }

	  /**
	   * Checks that this node is table
	   * @return {boolean}
	   */
	  isTable(event) {
	    let nodeIsTable = false;
	    if (this.currentNode && event) {
	      this.currentNode.node.querySelectorAll('.landing-table-container').forEach(table => {
	        if (table.contains(event.srcElement)) {
	          nodeIsTable = true;
	        }
	      });
	    }
	    return nodeIsTable;
	  }

	  /**
	   * Delete br tags in new table and add flag after this
	   */
	  prepareNewTable(table) {
	    table.querySelectorAll('br').forEach(tdTag => {
	      tdTag.remove();
	    });
	    table.setAttribute('table-prepare', 'true');
	    this.currentNode.onChange(true);
	  }
	  addTableButtons(event) {
	    const buttons = [];
	    let neededButtons = [];
	    let setTd = [];
	    const tableButtons = this.getTableButtons();
	    const tableAlignButtons = [tableButtons[0], tableButtons[1], tableButtons[2], tableButtons[3]];
	    const node = this.currentNode.node;
	    let table = null;
	    let isCell = false;
	    let isButtonAddRow = false;
	    let isButtonAddCol = false;
	    let isNeedTablePanel = true;
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table') || BX.Dom.hasClass(event.srcElement, 'landing-table-col-dnd')) {
	      isNeedTablePanel = false;
	    }
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table-row-add')) {
	      isButtonAddRow = true;
	    }
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table-col-add')) {
	      isButtonAddCol = true;
	    }
	    let hideButtons = [];
	    const nodeTableList = node.querySelectorAll('.landing-table');
	    if (nodeTableList.length > 0) {
	      nodeTableList.forEach(nodeTable => {
	        if (nodeTable.contains(event.srcElement)) {
	          table = nodeTable;
	          return true;
	        }
	        return false;
	      });
	    }
	    let isSelectedAll;
	    tableButtons.forEach(tableButton => {
	      tableButton.options.srcElement = event.srcElement;
	      tableButton.options.node = node;
	      tableButton.options.table = table;
	    });
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table-row-dnd')) {
	      setTd = event.srcElement.parentNode.children;
	      setTd = [...setTd];
	      neededButtons = this.getAmountTableRows(table) > 1 ? [0, 1, 2, 3, 4, 5, 6] : [0, 1, 2, 3, 4, 5];
	      neededButtons.forEach(neededButton => {
	        tableButtons[neededButton].options.target = 'row';
	        tableButtons[neededButton].options.setTd = setTd;
	        buttons.push(tableButtons[neededButton]);
	      });
	    }
	    if (BX.Dom.hasClass(event.srcElement.parentNode, 'landing-table-col-dnd')) {
	      const childNodes = event.srcElement.parentElement.parentElement.childNodes;
	      const childNodesArray = [...childNodes];
	      const childNodesArrayPrepare = [];
	      childNodesArray.forEach(childNode => {
	        if (childNode.nodeType === 1) {
	          childNodesArrayPrepare.push(childNode);
	        }
	      });
	      const neededPosition = childNodesArrayPrepare.indexOf(event.srcElement.parentElement);
	      const rows = event.srcElement.parentElement.parentElement.parentElement.childNodes;
	      rows.forEach(row => {
	        if (row.nodeType === 1) {
	          const rowChildPrepare = [];
	          row.childNodes.forEach(rowChildNode => {
	            if (rowChildNode.nodeType === 1) {
	              rowChildPrepare.push(rowChildNode);
	            }
	          });
	          if (rowChildPrepare[neededPosition]) {
	            setTd.push(rowChildPrepare[neededPosition]);
	          }
	        }
	      });
	      neededButtons = this.getAmountTableCols(table) > 1 ? [0, 1, 2, 3, 4, 5, 7] : [0, 1, 2, 3, 4, 5];
	      neededButtons.forEach(neededButton => {
	        tableButtons[neededButton].options.target = 'col';
	        tableButtons[neededButton].options.setTd = setTd;
	        buttons.push(tableButtons[neededButton]);
	      });
	    }
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table-th-select-all')) {
	      if (BX.Dom.hasClass(event.srcElement, 'landing-table-th-select-all-selected')) {
	        isSelectedAll = true;
	        const rows = event.srcElement.parentElement.parentElement.childNodes;
	        rows.forEach(row => {
	          row.childNodes.forEach(th => {
	            setTd.push(th);
	          });
	        });
	        neededButtons = [0, 1, 2, 3, 4, 5, 8, 9, 10];
	        neededButtons.forEach(neededButton => {
	          tableButtons[neededButton].options.target = 'table';
	          tableButtons[neededButton].options.setTd = setTd;
	          buttons.push(tableButtons[neededButton]);
	        });
	      } else {
	        isSelectedAll = false;
	        BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	      }
	    }
	    if (BX.Dom.hasClass(event.srcElement, 'landing-table-td') || event.srcElement.closest('.landing-table-td') !== null) {
	      setTd.push(event.srcElement);
	      neededButtons = [3, 2, 1, 0];
	      neededButtons.forEach(neededButton => {
	        tableButtons[neededButton].options.target = 'cell';
	        tableButtons[neededButton].options.setTd = setTd;
	        tableButtons[neededButton].insertAfter = 'strikeThrough';
	        buttons.push(tableButtons[neededButton]);
	      });
	      isCell = true;
	      hideButtons = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'createTable', 'pasteTable'];
	    }
	    let activeAlignButtonId = null;
	    const setActiveAlignButtonId = [];
	    setTd.forEach(th => {
	      if (th.nodeType === 1) {
	        activeAlignButtonId = undefined;
	        if (BX.Dom.hasClass(th, 'text-left')) {
	          activeAlignButtonId = 'alignLeft';
	        }
	        if (BX.Dom.hasClass(th, 'text-center')) {
	          activeAlignButtonId = 'alignCenter';
	        }
	        if (BX.Dom.hasClass(th, 'text-right')) {
	          activeAlignButtonId = 'alignRight';
	        }
	        if (BX.Dom.hasClass(th, 'text-justify')) {
	          activeAlignButtonId = 'alignJustify';
	        }
	        setActiveAlignButtonId.push(activeAlignButtonId);
	      }
	    });
	    let count = 0;
	    let isIdentical = true;
	    while (count < setActiveAlignButtonId.length && isIdentical) {
	      if (count > 0 && setActiveAlignButtonId[count] !== setActiveAlignButtonId[count - 1]) {
	        isIdentical = false;
	      }
	      count++;
	    }
	    activeAlignButtonId = isIdentical ? setActiveAlignButtonId[0] : undefined;
	    if (activeAlignButtonId) {
	      tableAlignButtons.forEach(tableAlignButton => {
	        if (tableAlignButton.id === activeAlignButtonId) {
	          BX.Dom.addClass(tableAlignButton.layout, 'landing-ui-active');
	        }
	      });
	    }
	    if (buttons[0] && buttons[1] && buttons[2] && buttons[3]) {
	      buttons[0].options.alignButtons = tableAlignButtons;
	      buttons[1].options.alignButtons = tableAlignButtons;
	      buttons[2].options.alignButtons = tableAlignButtons;
	      buttons[3].options.alignButtons = tableAlignButtons;
	    }
	    if (!this.manifest.textOnly) {
	      if (isNeedTablePanel) {
	        if (!isButtonAddRow && !isButtonAddCol && table) {
	          if (isCell) {
	            BX.Landing.UI.Panel.EditorPanel.getInstance().show(table.parentNode, null, buttons, true, hideButtons);
	          } else if (isSelectedAll === false) {
	            BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	          } else {
	            BX.Landing.UI.Panel.EditorPanel.getInstance().show(table.parentNode, null, buttons, true);
	          }
	        }
	      } else {
	        BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	      }
	    }
	  }

	  /**
	   * Gets change tag button
	   * @return {BX.Landing.UI.Button.ChangeTag}
	   */
	  getChangeTagButton() {
	    if (!this.changeTagButton) {
	      this.changeTagButton = new BX.Landing.UI.Button.ChangeTag('changeTag', {
	        html: `<span class="landing-ui-icon-editor-${this.node.nodeName.toLowerCase()}"></span>`,
	        attrs: {
	          title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_CHANGE_TAG')
	        },
	        onChange: this.onChangeTag.bind(this)
	      });
	    }
	    this.changeTagButton.insertAfter = 'unlink';
	    this.changeTagButton.activateItem(this.node.nodeName);
	    return this.changeTagButton;
	  }
	  getTableButtons() {
	    this.buttons = [];
	    this.buttons.push(new BX.Landing.UI.Button.AlignTable('alignLeft', {
	      html: '<span class="landing-ui-icon-editor-left"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_LEFT')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.AlignTable('alignCenter', {
	      html: '<span class="landing-ui-icon-editor-center"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_CENTER')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.AlignTable('alignRight', {
	      html: '<span class="landing-ui-icon-editor-right"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_RIGHT')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.AlignTable('alignJustify', {
	      html: '<span class="landing-ui-icon-editor-justify"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_JUSTIFY')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.TableColorAction('tableTextColor', {
	      text: BX.Landing.Loc.getMessage('EDITOR_ACTION_SET_FORE_COLOR'),
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_COLOR')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.TableColorAction('tableBgColor', {
	      html: '<i class="landing-ui-icon-editor-fill-color"></i>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_CELL_BG')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.DeleteElementTable('deleteRow', {
	      html: '<span class="landing-ui-icon-editor-delete"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_DELETE_ROW_TABLE')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.DeleteElementTable('deleteCol', {
	      html: '<span class="landing-ui-icon-editor-delete"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_DELETE_COL_TABLE')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.StyleTable('styleTable', {
	      html: `
						${BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_STYLE')}
							<i class="fas fa-chevron-down g-ml-8"></i>
					`,
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_STYLE')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.CopyTable('copyTable', {
	      text: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_COPY'),
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_COPY')
	      }
	    }, this.currentNode), new BX.Landing.UI.Button.DeleteTable('deleteTable', {
	      html: '<span class="landing-ui-icon-editor-delete"></span>',
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_DELETE')
	      }
	    }, this.currentNode));
	    return this.buttons;
	  }

	  /**
	   * Handles change tag event
	   * @param value
	   * @param {?boolean} [preventHistory = false]
	   */
	  onChangeTag(value, preventHistory) {
	    this.node = changeTagName(this.node, value);
	    this.bindEvents(this.node);
	    if (!this.getField().isEditable() && !preventHistory) {
	      this.disableEdit();
	      this.enableEdit();
	    }
	    const data = {};
	    data[this.selector] = value;
	    if (!preventHistory) {
	      this.changeOptionsHandler(data).then(() => {
	        BX.Landing.History.getInstance().push();
	      }).catch(() => {});
	    }
	  }
	  getAmountTableCols(table) {
	    return table.querySelectorAll('.landing-table-col-dnd').length;
	  }
	  getAmountTableRows(table) {
	    return table.querySelectorAll('.landing-table-row-dnd').length;
	  }
	  prepareTable(node) {
	    const setClassesForRemove = ['table-selected-all', 'landing-table-th-select-all-selected', 'landing-table-cell-selected', 'landing-table-row-selected', 'landing-table-th-selected', 'landing-table-th-selected-cell', 'landing-table-th-selected-top', 'landing-table-th-selected-x', 'landing-table-tr-selected-left', 'landing-table-tr-selected-y', 'landing-table-col-selected', 'landing-table-tr-selected', 'table-selected-all-right', 'table-selected-all-bottom'];
	    setClassesForRemove.forEach(className => {
	      node.querySelectorAll(`.${className}`).forEach(element => {
	        BX.Dom.removeClass(element, className);
	      });
	    });
	    return node;
	  }
	  onBackspaceDown(event) {
	    const selection = window.getSelection();
	    const position = selection.getRangeAt(0).startOffset;
	    if (position === 0) {
	      let focusNode = selection.focusNode;
	      if (!BX.Type.isNil(focusNode) && focusNode.nodeType !== 3) {
	        if (focusNode.firstChild.nodeType === 3 && focusNode.firstChild.firstChild.nodeType === 3) {
	          focusNode = focusNode.firstChild.firstChild;
	        } else if (focusNode.firstChild.nodeType === 3) {
	          focusNode = null;
	        } else {
	          focusNode = focusNode.firstChild;
	        }
	      }
	      if (focusNode) {
	        const focusNodeParent = focusNode.parentNode;
	        const allowedNodeName = new Set(['BLOCKQUOTE', 'UL']);
	        if (focusNodeParent && allowedNodeName.has(focusNodeParent.nodeName)) {
	          const focusNodeContainer = document.createElement('div');
	          focusNodeContainer.append(focusNode);
	          focusNodeParent.append(focusNodeContainer);
	        }
	        let contentNode = focusNode.parentNode.parentNode;
	        while (contentNode && !allowedNodeName.has(contentNode.nodeName)) {
	          contentNode = contentNode.parentNode;
	        }
	        if (contentNode && contentNode.childNodes.length === 1) {
	          contentNode.after(focusNode.parentNode);
	          contentNode.remove();
	          event.preventDefault();
	        }
	      }
	    }
	  }
	  isLinkPasted(text) {
	    const reg = /^https?:\/\/(?:www\.)?[\w#%+.:=@~-]{1,256}\.[\d()A-Za-z]{1,6}\b[\w#%&()+./:=?@~-]*$/;
	    return Boolean(reg.test(text));
	  }
	  prepareToLink(text) {
	    return `<a class='g-bg-transparent' href='${text}' target='_blank'> ${text} </a>`;
	  }
	}

	exports.Text = Text;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX,BX.Landing.Node,BX.Landing.Node.TableEditor));
//# sourceMappingURL=text.bundle.js.map
