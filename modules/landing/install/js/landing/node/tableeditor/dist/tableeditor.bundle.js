this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Node = this.BX.Landing.Node || {};
(function (exports,ui_draganddrop_draggable,main_core) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var TableEditor = /*#__PURE__*/function () {
	  function TableEditor(node, textNode) {
	    babelHelpers.classCallCheck(this, TableEditor);
	    this.textNode = textNode;
	    this.table = node.querySelector('.landing-table');
	    if (!this.table) {
	      return;
	    }
	    this.node = node;
	    this.tBody = this.node.getElementsByTagName('tbody')[0];
	    this.addTitles(this.node);
	    this.enableEditCells(this.table);
	    this.dragAndDropRows(this);
	    this.dragAndDropCols(this);
	    this.resizeColumn(this);
	    this.buildLines(this);
	    this.addRow(this);
	    this.addCol(this);
	    this.onUnselect(this);
	    this.unselect(this);
	    this.selectAll(this);
	    this.selectRow(this);
	    this.selectCol(this);
	    this.onCopyTable(this);
	    this.onDeleteElementTable(this);
	    this.onShowPopupMenu(this);
	  }
	  babelHelpers.createClass(TableEditor, [{
	    key: "addTitles",
	    value: function addTitles(tableNode) {
	      if (!tableNode.hasAttribute('title-added')) {
	        tableNode.title = '';
	        tableNode.querySelector('.landing-table-th-select-all').title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_SELECT_TABLE'));
	        tableNode.querySelectorAll('.landing-table-div-col-dnd').forEach(function (element) {
	          element.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_DND_COLS'));
	        });
	        tableNode.querySelectorAll('.landing-table-col-resize').forEach(function (element) {
	          element.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_RESIZE_COLS'));
	        });
	        tableNode.querySelectorAll('.landing-table-col-add').forEach(function (element) {
	          element.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_BUTTON_ADD_COL'));
	        });
	        tableNode.querySelectorAll('.landing-table-row-dnd').forEach(function (element) {
	          element.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_DND_ROWS'));
	        });
	        tableNode.querySelectorAll('.landing-table-row-add').forEach(function (element) {
	          element.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_BUTTON_ADD_ROW'));
	        });
	        tableNode.setAttribute('title-added', 'true');
	      }
	    }
	  }, {
	    key: "unselect",
	    value: function unselect(tableEditor) {
	      var isSelectAll = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      if (tableEditor.table) {
	        if (!isSelectAll) {
	          tableEditor.table.classList.remove('table-selected-all');
	          this.removeClasses(tableEditor.table, 'landing-table-th-select-all-selected');
	          this.removeClasses(tableEditor.table, 'landing-table-cell-selected');
	        }
	        this.removeClasses(tableEditor.table, 'landing-table-row-selected');
	        this.removeClasses(tableEditor.table, 'landing-table-th-selected');
	        this.removeClasses(tableEditor.table, 'landing-table-th-selected-cell');
	        this.removeClasses(tableEditor.table, 'landing-table-th-selected-top');
	        this.removeClasses(tableEditor.table, 'landing-table-th-selected-x');
	        this.removeClasses(tableEditor.table, 'landing-table-tr-selected-left');
	        this.removeClasses(tableEditor.table, 'landing-table-tr-selected-y');
	        this.removeClasses(tableEditor.table, 'landing-table-col-selected');
	        this.removeClasses(tableEditor.table, 'landing-table-tr-selected');
	        this.removeClasses(tableEditor.table, 'table-selected-all-right');
	        this.removeClasses(tableEditor.table, 'table-selected-all-bottom');
	      }
	    }
	  }, {
	    key: "onUnselect",
	    value: function onUnselect(tableEditor) {
	      main_core.Event.bind(tableEditor.table, 'click', function () {
	        var classList = new Set(['landing-table-th-select-all', 'landing-table-row-dnd', 'landing-table-row-add']);
	        var isContains = babelHelpers.toConsumableArray(event.target.classList).some(function (className) {
	          return classList.has(className);
	        });
	        if (!isContains) {
	          var classListChild = new Set(['landing-table-col-dnd']);
	          isContains = babelHelpers.toConsumableArray(event.target.parentElement.classList).some(function (className) {
	            return classListChild.has(className);
	          });
	          if (!isContains) {
	            tableEditor.unselect(tableEditor);
	          }
	        }
	      });
	    }
	  }, {
	    key: "selectAll",
	    value: function selectAll(tableEditor) {
	      var thTech = tableEditor.table.querySelector('.landing-table-th-select-all');
	      main_core.Event.bind(thTech, 'click', function () {
	        var isSelectedTable = false;
	        if (tableEditor.table.classList.contains('table-selected-all')) {
	          isSelectedTable = true;
	        }
	        tableEditor.unselect(tableEditor, true);
	        var setRows = tableEditor.table.querySelectorAll('.landing-table-tr');
	        var count = 0;
	        setRows.forEach(function (row) {
	          var setTh = row.childNodes;
	          var index = 0;
	          var lastThIndex = 0;
	          row.childNodes.forEach(function (cell) {
	            if (cell.nodeType === 1) {
	              lastThIndex = index;
	            }
	            index++;
	          });
	          if (count > 0) {
	            var lastTh = setTh[lastThIndex];
	            if (isSelectedTable) {
	              lastTh.classList.remove('table-selected-all-right');
	            } else {
	              lastTh.classList.add('table-selected-all-right');
	            }
	          }
	          count++;
	          if (count === setRows.length) {
	            setTh.forEach(function (th) {
	              if (th.nodeType === 1) {
	                if (isSelectedTable) {
	                  th.classList.remove('table-selected-all-bottom');
	                } else {
	                  th.classList.add('table-selected-all-bottom');
	                }
	              }
	            });
	          }
	        });
	        thTech.classList.toggle('landing-table-th-select-all-selected');
	        tableEditor.table.classList.toggle('table-selected-all');
	        tableEditor.table.querySelectorAll('.landing-table-col-dnd').forEach(function (thDnd) {
	          thDnd.classList.toggle('landing-table-cell-selected');
	        });
	        tableEditor.table.querySelectorAll('.landing-table-row-dnd').forEach(function (trDnd) {
	          trDnd.classList.toggle('landing-table-cell-selected');
	        });
	      });
	    }
	  }, {
	    key: "selectRow",
	    value: function selectRow(tableEditor) {
	      var neededPosition = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var setTrDnd = tableEditor.table.querySelectorAll('.landing-table-row-dnd');
	      if (neededPosition !== null) {
	        var newSetTrDnd = [];
	        newSetTrDnd[0] = setTrDnd[neededPosition];
	        setTrDnd = newSetTrDnd;
	      }
	      setTrDnd.forEach(function (trDnd) {
	        main_core.Event.bind(trDnd, 'click', function () {
	          if (!event.target.classList.contains('landing-table-row-add')) {
	            tableEditor.unselect(tableEditor);
	            var setTh = trDnd.parentElement.childNodes;
	            var count = 0;
	            setTh.forEach(function (th) {
	              if (th.nodeType === 1) {
	                if (count === 1) {
	                  th.classList.add('landing-table-tr-selected-left');
	                }
	                if (count >= 1) {
	                  th.classList.add('landing-table-tr-selected-y');
	                }
	                count++;
	              }
	            });
	            trDnd.parentElement.classList.add('landing-table-row-selected');
	            tableEditor.tBody.classList.add('landing-table-tr-selected');
	          }
	        });
	      });
	    }
	  }, {
	    key: "selectCol",
	    value: function selectCol(tableEditor) {
	      var neededPosition = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var setThDnd = tableEditor.table.querySelectorAll('.landing-table-col-dnd');
	      if (neededPosition !== null) {
	        var newSetTrDnd = [];
	        newSetTrDnd[0] = setThDnd[neededPosition];
	        setThDnd = newSetTrDnd;
	      }
	      setThDnd.forEach(function (thDnd) {
	        main_core.Event.bind(thDnd, 'click', function () {
	          if (!event.target.classList.contains('landing-table-col-add') && !event.target.classList.contains('landing-table-col-resize')) {
	            tableEditor.unselect(tableEditor);
	            var cellIndex = thDnd.cellIndex;
	            var count = 0;
	            tableEditor.tBody.childNodes.forEach(function (tr) {
	              if (tr.nodeType === 1) {
	                var countNode = 0;
	                var nodeCount = 0;
	                var needNodePosition = 0;
	                tr.childNodes.forEach(function (trChild) {
	                  if (trChild.nodeType === 1) {
	                    if (cellIndex === nodeCount) {
	                      needNodePosition = countNode;
	                    }
	                    nodeCount++;
	                  }
	                  countNode++;
	                });
	                if (count === 0) {
	                  tr.classList.add('landing-table-col-selected');
	                  tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-cell');
	                }
	                if (count === 1) {
	                  tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-top');
	                }
	                if (count >= 1) {
	                  tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-x');
	                }
	                count++;
	                tr.childNodes[needNodePosition].classList.add('landing-table-th-selected');
	              }
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "buildLines",
	    value: function buildLines(tableEditor) {
	      if (tableEditor.node) {
	        var width = tableEditor.node.querySelector('.landing-table').getBoundingClientRect().width;
	        var height = tableEditor.node.querySelector('.landing-table').getBoundingClientRect().height;
	        var offset = 5;
	        var linesX = document.querySelectorAll('.landing-table-row-add-line');
	        linesX.forEach(function (lineX) {
	          lineX.style.width = "".concat(width + offset, "px");
	        });
	        var linesY = document.querySelectorAll('.landing-table-col-add-line');
	        linesY.forEach(function (lineY) {
	          lineY.style.height = "".concat(height + offset, "px");
	        });
	      }
	    }
	  }, {
	    key: "getButtonsAddRow",
	    value: function getButtonsAddRow(node) {
	      return node.querySelectorAll('.landing-table-row-add');
	    }
	  }, {
	    key: "addRow",
	    value: function addRow(tableEditor) {
	      var _this = this;
	      var neededPosition = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var buttons = tableEditor.getButtonsAddRow(tableEditor.node);
	      if (neededPosition === null) {
	        buttons = Array.prototype.slice.call(buttons, 0);
	      } else {
	        var button = buttons[neededPosition];
	        buttons = [];
	        buttons[0] = button;
	      }
	      buttons = Array.prototype.slice.call(buttons, 0);
	      buttons.forEach(function (button) {
	        main_core.Event.bind(button, 'click', function () {
	          var selectedCell = tableEditor.table.querySelector('.landing-table-th-selected-cell');
	          var selectedCellPos = 0;
	          var nodeCount = 0;
	          if (selectedCell) {
	            selectedCell.parentNode.childNodes.forEach(function (node) {
	              if (selectedCellPos === 0 && node === selectedCell) {
	                selectedCellPos = nodeCount;
	              }
	              if (node.nodeType === 1) {
	                nodeCount++;
	              }
	            });
	          }
	          var trDnd = document.createElement('th');
	          trDnd.classList.add('landing-table-th', 'landing-table-row-dnd');
	          if (tableEditor.table.classList.contains('table-selected-all')) {
	            trDnd.classList.add('landing-table-cell-selected');
	          }
	          var row = button.parentNode.parentNode;
	          var neededPosition = babelHelpers.toConsumableArray(row.parentNode.children).indexOf(button.parentNode.parentNode);
	          var count = 0;
	          var lastElementPosition = 0;
	          tableEditor.tBody.childNodes.forEach(function (element) {
	            if (element.nodeType === 1) {
	              lastElementPosition = count;
	            }
	            count++;
	          });
	          var tr = tableEditor.tBody.childNodes[lastElementPosition];
	          var newTd = document.createElement('td');
	          newTd.classList.add('landing-table-th', 'landing-table-td');
	          newTd.style.width = '50px';
	          var table = tableEditor.node.querySelector('.landing-table');
	          if (table.hasAttribute('bg-color')) {
	            newTd.style.backgroundColor = table.getAttribute('bg-color');
	          }
	          if (table.hasAttribute('text-color')) {
	            newTd.style.color = table.getAttribute('text-color');
	          }
	          var newTr = document.createElement('tr');
	          newTr.classList.add('landing-table-tr');
	          trDnd.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_DND_ROWS'));
	          trDnd.style.width = '16px';
	          var divAddRow = document.createElement('div');
	          divAddRow.classList.add('landing-table-row-add');
	          divAddRow.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_BUTTON_ADD_COL'));
	          var divLineX = document.createElement('div');
	          divLineX.classList.add('landing-table-row-add-line');
	          var divRowDnd = document.createElement('div');
	          divRowDnd.classList.add('landing-table-div-row-dnd');
	          divAddRow.appendChild(divLineX);
	          trDnd.appendChild(divAddRow);
	          trDnd.appendChild(divRowDnd);
	          if (tr) {
	            var _count = tr.children.length;
	            var setTd = [];
	            button.parentNode.parentNode.childNodes.forEach(function (item) {
	              if (item.nodeType === 1) {
	                setTd.push(item);
	              }
	            });
	            for (var i = 0; i < _count; i++) {
	              var newTdCloned = newTd.cloneNode(true);
	              if (i === selectedCellPos) {
	                newTdCloned.classList.add('landing-table-th-selected', 'landing-table-th-selected-x');
	              }
	              if (i === 0) {
	                newTr.appendChild(trDnd);
	              } else {
	                newTdCloned.style.width = setTd[i].style.width;
	                newTdCloned.style.height = setTd[i].style.height;
	                newTr.appendChild(newTdCloned);
	              }
	            }
	          }
	          button.parentNode.parentNode.parentNode.insertBefore(newTr, button.parentNode.parentNode.nextSibling);
	          tableEditor.buildLines(tableEditor);
	          tableEditor.enableEditCells(tableEditor.node);
	          _this.textNode.onChange(true);
	          tableEditor.selectRow(tableEditor, neededPosition);
	          tableEditor.addRow(tableEditor, neededPosition);
	          tableEditor.unselect(tableEditor);
	          BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	        });
	      });
	    }
	  }, {
	    key: "getButtonsAddCol",
	    value: function getButtonsAddCol(node) {
	      return node.querySelectorAll('.landing-table-col-add');
	    }
	  }, {
	    key: "addCol",
	    value: function addCol(tableEditor) {
	      var _this2 = this;
	      var neededPosition = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var buttons = tableEditor.getButtonsAddCol(tableEditor.node);
	      if (neededPosition === null) {
	        buttons = Array.prototype.slice.call(buttons, 0);
	      } else {
	        var button = buttons[neededPosition];
	        buttons = [];
	        buttons[0] = button;
	      }
	      buttons.forEach(function (button) {
	        main_core.Event.bind(button, 'click', function () {
	          var selectedRow = tableEditor.table.querySelector('.landing-table-row-selected');
	          var selectedRowPos = 0;
	          var countNode = 0;
	          if (selectedRow) {
	            selectedRow.parentNode.childNodes.forEach(function (node) {
	              if (node === selectedRow && selectedRowPos === 0) {
	                selectedRowPos = countNode;
	              }
	              if (node.nodeType === 1) {
	                countNode++;
	              }
	            });
	          }
	          var newThFirst;
	          var newThFirstCloned;
	          newThFirst = document.createElement('th');
	          newThFirst.classList.add('landing-table-th', 'landing-table-col-dnd');
	          newThFirst.style.width = '50px';
	          if (tableEditor.table.classList.contains('table-selected-all')) {
	            newThFirst.classList.add('landing-table-cell-selected');
	          }
	          var row = button.parentNode.parentNode;
	          var position = babelHelpers.toConsumableArray(row.children).indexOf(button.parentNode);
	          if (tableEditor.tBody.childNodes.length > 0) {
	            var count = 0;
	            tableEditor.tBody.childNodes.forEach(function (element) {
	              if (element.nodeType === 1) {
	                newThFirstCloned = newThFirst.cloneNode(true);
	                var divColumnDnd = document.createElement('div');
	                divColumnDnd.classList.add('landing-table-div-col-dnd');
	                divColumnDnd.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_DND_COLS'));
	                var divColumnResize = document.createElement('div');
	                divColumnResize.classList.add('landing-table-col-resize');
	                divColumnResize.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_RESIZE_COLS'));
	                var divAddColHere = document.createElement('div');
	                divAddColHere.classList.add('landing-table-col-add');
	                divAddColHere.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage('LANDING_TABLE_BUTTON_ADD_COL'));
	                var divLineY = document.createElement('div');
	                divLineY.classList.add('landing-table-col-add-line');
	                divAddColHere.appendChild(divLineY);
	                newThFirstCloned.appendChild(divColumnDnd);
	                newThFirstCloned.appendChild(divColumnResize);
	                newThFirstCloned.appendChild(divAddColHere);
	                var newTd = document.createElement('td');
	                newTd.classList.add('landing-table-th', 'landing-table-td');
	                newTd.style.width = '50px';
	                var table = tableEditor.node.querySelector('.landing-table');
	                if (table.hasAttribute('bg-color')) {
	                  newTd.style.backgroundColor = table.getAttribute('bg-color');
	                }
	                if (table.hasAttribute('text-color')) {
	                  newTd.style.color = table.getAttribute('text-color');
	                }
	                if (selectedRowPos > 0 && selectedRowPos === count) {
	                  newTd.classList.add('landing-table-tr-selected-y');
	                }
	                var countChild = 0;
	                var countNodes = 0;
	                var newNeededPosition = 0;
	                element.childNodes.forEach(function (node) {
	                  if (node.nodeType === 1) {
	                    if (countNodes === position) {
	                      newNeededPosition = countChild;
	                    }
	                    countNodes++;
	                  }
	                  countChild++;
	                });
	                if (count === 0) {
	                  element.childNodes[newNeededPosition].parentNode.insertBefore(newThFirstCloned, element.childNodes[newNeededPosition].nextSibling);
	                } else {
	                  element.childNodes[newNeededPosition].parentNode.insertBefore(newTd, element.childNodes[newNeededPosition].nextSibling);
	                }
	                count++;
	              }
	            });
	          }
	          tableEditor.buildLines(tableEditor);
	          tableEditor.enableEditCells(tableEditor.node);
	          _this2.textNode.onChange(true);
	          tableEditor.selectCol(tableEditor, position);
	          tableEditor.addCol(tableEditor, position);
	          tableEditor.unselect(tableEditor);
	          BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	        });
	      });
	    }
	  }, {
	    key: "dragAndDropRows",
	    value: function dragAndDropRows(tableEditor) {
	      var _this3 = this;
	      this.draggableRows = new ui_draganddrop_draggable.Draggable({
	        container: tableEditor.tBody,
	        draggable: '.landing-table-tr',
	        dragElement: '.landing-table-row-dnd',
	        type: ui_draganddrop_draggable.Draggable.HEADLESS
	      });
	      var rows = [];
	      var setRowPositionsY;
	      var setRowHeights;
	      var currentPositionRow;
	      var newPositionRow = 0;
	      var draggableRowOffsetY;
	      var tablePositionLeft;
	      var tablePositionTop;
	      var currentPositionRowX;
	      var currentPositionRowY;
	      var cloneRow;
	      var originalSource;
	      this.draggableRows.subscribe('start', function (event) {
	        originalSource = _this3.draggableRows.dragStartEvent.data.originalSource;
	        tablePositionLeft = tableEditor.tBody.getBoundingClientRect().left;
	        tablePositionTop = tableEditor.tBody.getBoundingClientRect().top;
	        setRowPositionsY = [];
	        setRowHeights = [];
	        draggableRowOffsetY = 0;
	        currentPositionRow = event.getData().sourceIndex;
	        rows = tableEditor.tBody.querySelectorAll('.landing-table-tr');
	        rows.forEach(function (row) {
	          setRowPositionsY.push(row.getBoundingClientRect().y);
	          setRowHeights.push(row.getBoundingClientRect().height);
	        });
	        currentPositionRowX = rows[currentPositionRow].getBoundingClientRect().x;
	        currentPositionRowY = rows[currentPositionRow].getBoundingClientRect().y;
	        cloneRow = document.createElement('tr');
	        cloneRow.classList.add('landing-table-tr-draggable');
	        rows[currentPositionRow].childNodes.forEach(function (node) {
	          cloneRow.append(node.cloneNode(true));
	        });
	        if (rows[currentPositionRow].classList.contains('landing-table-row-selected')) {
	          cloneRow.classList.add('landing-table-row-selected');
	        }
	        var indexFirstNode;
	        var count = 0;
	        while (!indexFirstNode) {
	          if (rows[currentPositionRow].childNodes[count].nodeType === 1) {
	            indexFirstNode = count;
	          }
	          count++;
	        }
	        cloneRow.childNodes[indexFirstNode].style.borderRadius = getComputedStyle(rows[currentPositionRow].childNodes[indexFirstNode]).borderRadius;
	      }).subscribe('move', function (event) {
	        if (!originalSource.classList.contains('landing-table-row-add')) {
	          tableEditor.tBody.classList.add('landing-table-draggable');
	          rows[currentPositionRow].classList.add('landing-table-tr-taken');
	          draggableRowOffsetY = event.getData().offsetY;
	          tableEditor.tBody.append(cloneRow);
	          cloneRow.style.position = 'absolute';
	          cloneRow.style.top = "".concat(currentPositionRowY - tablePositionTop + draggableRowOffsetY - 0.5, "px");
	          cloneRow.style.left = "".concat(currentPositionRowX - tablePositionLeft - 0.5, "px");
	          if (draggableRowOffsetY > 0) {
	            cloneRow.style.transform = 'rotate(-1deg)';
	          } else {
	            cloneRow.style.transform = 'rotate(1deg)';
	          }
	        }
	      }).subscribe('end', function () {
	        cloneRow.remove();
	        rows[currentPositionRow].classList.remove('landing-table-tr-taken');
	        rows[currentPositionRow].style = '';
	        var newDraggableRowPositionY = currentPositionRowY + draggableRowOffsetY;
	        var newDraggableRowPositionBottomY = newDraggableRowPositionY + rows[currentPositionRow].getBoundingClientRect().height;
	        if (draggableRowOffsetY < 0) {
	          var _iterator = _createForOfIteratorHelper(setRowPositionsY.entries()),
	            _step;
	          try {
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	                i = _step$value[0],
	                transitivePositionY = _step$value[1];
	              if (i === currentPositionRow) {
	                transitivePositionY -= setRowHeights[i - 1] / 2;
	              }
	              if (newDraggableRowPositionY >= transitivePositionY) {
	                newPositionRow = i;
	              }
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        }
	        if (draggableRowOffsetY === 0) {
	          newPositionRow = currentPositionRow;
	        }
	        if (draggableRowOffsetY > 0) {
	          var _iterator2 = _createForOfIteratorHelper(setRowPositionsY.entries()),
	            _step2;
	          try {
	            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	              var _step2$value = babelHelpers.slicedToArray(_step2.value, 2),
	                _i = _step2$value[0],
	                element = _step2$value[1];
	              var _transitivePositionY = element + setRowHeights[_i] / 2;
	              if (_i === currentPositionRow) {
	                _transitivePositionY = element;
	              }
	              if (newDraggableRowPositionBottomY >= _transitivePositionY) {
	                newPositionRow = _i;
	              }
	            }
	          } catch (err) {
	            _iterator2.e(err);
	          } finally {
	            _iterator2.f();
	          }
	        }

	        // draggable row can only be in the 1 position, 0 position for technical row
	        if (newPositionRow === 0) {
	          newPositionRow++;
	        }

	        // need to move
	        if (currentPositionRow !== newPositionRow) {
	          var referenceNode = null;
	          var referenceNodeNext = null;
	          if (rows[newPositionRow]) {
	            referenceNode = rows[newPositionRow];
	            referenceNodeNext = referenceNode.nextSibling;
	            while (referenceNodeNext && referenceNodeNext.nodeType !== 1) {
	              referenceNodeNext = referenceNodeNext.nextSibling;
	            }
	          }
	          if (currentPositionRow > newPositionRow) {
	            tableEditor.tBody.insertBefore(rows[currentPositionRow], referenceNode);
	          }
	          if (currentPositionRow < newPositionRow) {
	            tableEditor.tBody.insertBefore(rows[currentPositionRow], referenceNodeNext);
	          }
	        }
	        tableEditor.tBody.classList.remove('landing-table-draggable');
	        _this3.textNode.onChange(true);
	      });
	    }
	  }, {
	    key: "dragAndDropCols",
	    value: function dragAndDropCols(tableEditor) {
	      var _this4 = this;
	      this.draggableCols = new ui_draganddrop_draggable.Draggable({
	        container: tableEditor.tBody,
	        draggable: '.landing-table-div-col-dnd',
	        type: ui_draganddrop_draggable.Draggable.HEADLESS
	      });
	      var currentPositionCol;
	      var newPositionCol = 0;
	      var draggableColOffsetX;
	      var draggableColOffsetY;
	      var setColCells = [];
	      var setColPositionsX;
	      var setColWidths;
	      var setRows;
	      var tablePositionLeft;
	      var currentPositionColX;
	      var setColCellsStyles;
	      var draggableCol;
	      this.draggableCols.subscribe('start', function (event) {
	        tablePositionLeft = tableEditor.tBody.getBoundingClientRect().left;
	        setColPositionsX = [];
	        setColWidths = [];
	        setColCellsStyles = [];
	        draggableColOffsetX = 0;
	        draggableColOffsetY = 0;
	        currentPositionCol = event.getData().originalSource.parentNode.cellIndex;
	        if (currentPositionCol) {
	          setColCells = babelHelpers.toConsumableArray(tableEditor.tBody.querySelectorAll('.landing-table-tr')).map(function (row) {
	            return row.children[currentPositionCol];
	          });
	          setRows = tableEditor.tBody.querySelectorAll('.landing-table-tr');
	          setRows[0].childNodes.forEach(function (thOfFirstRow) {
	            if (thOfFirstRow.nodeType === 1) {
	              setColPositionsX.push(thOfFirstRow.getBoundingClientRect().x);
	              setColWidths.push(thOfFirstRow.getBoundingClientRect().width);
	            }
	          });
	        }
	        currentPositionColX = setColCells[0].getBoundingClientRect().x;
	        draggableCol = document.createElement('div');
	        setColCells.forEach(function (cell) {
	          setColCellsStyles.push(cell.getAttribute('style'));
	          draggableCol.append(cell.cloneNode(true));
	          draggableCol.lastChild.style.borderRadius = getComputedStyle(cell).borderRadius;
	          draggableCol.lastChild.style.height = "".concat(cell.getBoundingClientRect().height, "px");
	          draggableCol.lastChild.style.width = "".concat(cell.getBoundingClientRect().width, "px");
	        });
	        draggableCol.hidden = true;
	        draggableCol.classList.add('landing-table-col-draggable');
	        tableEditor.tBody.append(draggableCol);
	      }).subscribe('move', function (event) {
	        tableEditor.tBody.classList.add('landing-table-draggable');
	        setColCells.forEach(function (cell) {
	          cell.classList.add('landing-table-col-taken');
	        });
	        draggableColOffsetX = event.getData().offsetX;
	        draggableColOffsetY = event.getData().offsetY;
	        draggableCol.hidden = false;
	        draggableCol.style.position = 'absolute';
	        draggableCol.style.left = "".concat(currentPositionColX - tablePositionLeft + draggableColOffsetX, "px");
	        draggableCol.style.top = "".concat(0, "px");
	        if (draggableColOffsetX < 0) {
	          draggableCol.style.transform = 'rotate(-1deg)';
	        }
	        if (draggableColOffsetX > 0) {
	          draggableCol.style.transform = 'rotate(1deg)';
	        }
	      }).subscribe('end', function () {
	        draggableCol.remove();
	        setColCells.forEach(function (cell) {
	          cell.hidden = false;
	        });
	        if (currentPositionCol) {
	          var newDraggableColPositionX = setColPositionsX[currentPositionCol] + draggableColOffsetX;
	          var newDraggableColPositionRightX = setColPositionsX[currentPositionCol] + draggableColOffsetX + setColCells[0].getBoundingClientRect().width;
	          var i = 0;
	          setColCells.forEach(function (cell) {
	            cell.style = setColCellsStyles[i];
	            cell.classList.remove('landing-table-col-taken');
	            i++;
	          });
	          if (draggableColOffsetX < 0) {
	            var _iterator3 = _createForOfIteratorHelper(setColPositionsX.entries()),
	              _step3;
	            try {
	              for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	                var _step3$value = babelHelpers.slicedToArray(_step3.value, 2),
	                  _i2 = _step3$value[0],
	                  transitivePositionX = _step3$value[1];
	                if (_i2 > 0) {
	                  transitivePositionX -= setColWidths[_i2 - 1] / 2;
	                }
	                if (newDraggableColPositionX > transitivePositionX) {
	                  newPositionCol = _i2;
	                }
	              }
	            } catch (err) {
	              _iterator3.e(err);
	            } finally {
	              _iterator3.f();
	            }
	          }
	          if (draggableColOffsetX === 0) {
	            newPositionCol = currentPositionCol;
	          }
	          if (draggableColOffsetX > 0) {
	            var _iterator4 = _createForOfIteratorHelper(setColPositionsX.entries()),
	              _step4;
	            try {
	              for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	                var _step4$value = babelHelpers.slicedToArray(_step4.value, 2),
	                  _i3 = _step4$value[0],
	                  element = _step4$value[1];
	                var _transitivePositionX = element + setColWidths[_i3] / 2;
	                if (_i3 === currentPositionCol) {
	                  _transitivePositionX = element;
	                }
	                if (newDraggableColPositionRightX > _transitivePositionX) {
	                  newPositionCol = _i3;
	                }
	              }
	            } catch (err) {
	              _iterator4.e(err);
	            } finally {
	              _iterator4.f();
	            }
	          }

	          // draggable col can only be in the 1 position, 0 position for technical
	          if (newPositionCol === 0) {
	            newPositionCol++;
	          }
	          if (currentPositionCol !== newPositionCol) {
	            setRows.forEach(function (row) {
	              var childCells = [];
	              row.childNodes.forEach(function (th) {
	                if (th.nodeType === 1) {
	                  childCells.push(th);
	                }
	              });
	              var referenceNode = null;
	              var referenceNodeNext = null;
	              if (childCells[newPositionCol]) {
	                referenceNode = childCells[newPositionCol];
	                referenceNodeNext = referenceNode.nextSibling;
	                while (referenceNodeNext && referenceNodeNext.nodeType !== 1) {
	                  referenceNodeNext = referenceNodeNext.nextSibling;
	                }
	              }
	              if (currentPositionCol > newPositionCol) {
	                row.insertBefore(childCells[currentPositionCol], referenceNode);
	              }
	              if (currentPositionCol < newPositionCol) {
	                row.insertBefore(childCells[currentPositionCol], referenceNodeNext);
	              }
	            });
	          }
	          tableEditor.tBody.classList.remove('landing-table-draggable');
	          _this4.textNode.onChange(true);
	        }
	      });
	    }
	  }, {
	    key: "resizeColumn",
	    value: function resizeColumn(tableEditor) {
	      var _this5 = this;
	      var tbody = this.tBody;
	      this.resizeElement = new ui_draganddrop_draggable.Draggable({
	        container: tbody,
	        draggable: '.landing-table-col-resize',
	        type: ui_draganddrop_draggable.Draggable.HEADLESS
	      });
	      var thWidth;
	      var setTh;
	      this.resizeElement.subscribe('start', function (event) {
	        setTh = [];
	        var th = event.getData().draggable.parentNode;
	        thWidth = th.getBoundingClientRect().width;
	        var currentPosition = th.cellIndex;
	        var setTr = tbody.querySelectorAll('.landing-table-tr');
	        setTr.forEach(function (tr) {
	          setTh.push(tr.children[currentPosition]);
	        });
	      }).subscribe('move', function (event) {
	        var offsetX = event.getData().offsetX;
	        var thNewWidth = thWidth + offsetX;
	        setTh.forEach(function (th) {
	          BX.Dom.style(th, 'width', "".concat(thNewWidth, "px"));
	        });
	      }).subscribe('end', function () {
	        var tBodyWidth = tbody.getBoundingClientRect().width;
	        var tableContainerWidth = tbody.parentElement.parentElement.getBoundingClientRect().width;
	        if (tableContainerWidth > tBodyWidth) {
	          tbody.parentElement.parentElement.classList.add('landing-table-scroll-hidden');
	        } else {
	          tbody.parentElement.parentElement.classList.remove('landing-table-scroll-hidden');
	        }
	        tableEditor.buildLines(tableEditor);
	        _this5.textNode.onChange(true);
	      });
	    }
	  }, {
	    key: "enableEditCells",
	    value: function enableEditCells(table) {
	      var thContentList = table.querySelectorAll('.landing-table-td');
	      thContentList.forEach(function (td) {
	        td.setAttribute('contenteditable', 'true');
	      });
	    }
	  }, {
	    key: "removeClasses",
	    value: function removeClasses(element, className) {
	      var setElements = element.querySelectorAll(".".concat(className));
	      setElements.forEach(function (element) {
	        element.classList.remove(className);
	      });
	    }
	  }, {
	    key: "onCopyTable",
	    value: function onCopyTable(tableEditor) {
	      BX.Event.EventEmitter.subscribe('BX.Landing.TableEditor:onCopyTable', function () {
	        tableEditor.unselect(tableEditor);
	        BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	      });
	    }
	  }, {
	    key: "onShowPopupMenu",
	    value: function onShowPopupMenu(tableEditor) {
	      BX.Event.EventEmitter.subscribe('BX.Landing.PopupMenuWindow:onShow', function () {
	        tableEditor.unselect(tableEditor);
	        BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	      });
	    }
	  }, {
	    key: "onDeleteElementTable",
	    value: function onDeleteElementTable(tableEditor) {
	      BX.Event.EventEmitter.subscribe('BX.Landing.TableEditor:onDeleteElementTable', function () {
	        tableEditor.buildLines(tableEditor);
	      });
	    }
	  }]);
	  return TableEditor;
	}();

	exports.TableEditor = TableEditor;

}((this.BX.Landing.Node.TableEditor = this.BX.Landing.Node.TableEditor || {}),BX.UI.DragAndDrop,BX));
//# sourceMappingURL=tableeditor.bundle.js.map
