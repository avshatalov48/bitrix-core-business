(function (exports,main_core_events,ui_cnt,main_core) {
	'use strict';

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.ActionPanel
	   *
	   * @param {BX.Main.grid} parent
	   * @param {object} actions List of available actions Bitrix\Main\Grid\Panel\Actions::getList()
	   * @param {string} actions.CREATE
	   * @param {string} actions.SEND
	   * @param {string} actions.ACTIVATE
	   * @param {string} actions.SHOW
	   * @param {string} actions.HIDE
	   * @param {string} actions.REMOVE
	   * @param {string} actions.CALLBACK
	   * @param {string} actions.INLINE_EDIT
	   * @param {string} actions.HIDE_ALL_EXPECT
	   * @param {string} actions.SHOW_ALL
	   * @param {string} actions.RESET_CONTROLS
	   *
	   * @param {object} types List of available control types
	   * of the actions panel Bitrix\Main\Grid\Panel\Types::getList()
	   * @param {string} types.DROPDOWN
	   * @param {string} types.CHECKBOX
	   * @param {string} types.TEXT
	   * @param {string} types.BUTTON
	   * @param {string} types.LINK
	   * @param {string} types.CUSTOM
	   * @param {string} types.HIDDEN
	   *
	   * @constructor
	   */

	  BX.Grid.ActionPanel = function (parent, actions, types) {
	    this.parent = null;
	    this.rel = {};
	    this.actions = null;
	    this.types = null;
	    this.lastActivated = [];
	    this.init(parent, actions, types);
	    this.button = [];
	    this.elements = [];
	    this.buttonOnChange = [];
	    this.buttonData = {};
	  };

	  BX.Grid.ActionPanel.prototype = {
	    init: function init(parent, actions, types) {
	      this.parent = parent;
	      this.actions = eval(actions);
	      this.types = eval(types);
	      BX.addCustomEvent(window, 'Dropdown::change', BX.proxy(this._dropdownEventHandle, this));
	      BX.addCustomEvent(window, 'Dropdown::load', BX.proxy(this._dropdownEventHandle, this));
	      var panel = this.getPanel();
	      BX.bind(panel, 'change', BX.delegate(this._checkboxChange, this));
	      BX.bind(panel, 'click', BX.delegate(this._clickOnButton, this));
	      BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this._gridUpdatedEventHandle, this));
	    },
	    destroy: function destroy() {
	      BX.removeCustomEvent(window, 'Dropdown::change', BX.proxy(this._dropdownEventHandle, this));
	      BX.removeCustomEvent(window, 'Dropdown::load', BX.proxy(this._dropdownEventHandle, this));
	      BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this._gridUpdatedEventHandle, this));
	    },
	    _gridUpdatedEventHandle: function _gridUpdatedEventHandle() {
	      var cancelButton = BX('grid_cancel_button');
	      cancelButton && BX.fireEvent(BX.firstChild(cancelButton), 'click');
	    },
	    _dropdownEventHandle: function _dropdownEventHandle(id, event, item, dataItem) {
	      this.isPanelControl(BX(id)) && this._dropdownChange(id, event, item, dataItem);
	    },
	    resetForAllCheckbox: function resetForAllCheckbox() {
	      var checkbox = this.getForAllCheckbox();

	      if (BX.type.isDomNode(checkbox)) {
	        checkbox.checked = null;
	      }
	    },
	    getForAllCheckbox: function getForAllCheckbox() {
	      return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classForAllCheckbox'), true);
	    },
	    getPanel: function getPanel() {
	      return BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classActionPanel'), true);
	    },
	    getApplyButton: function getApplyButton() {
	      return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelApplyButton'), true);
	    },
	    isPanelControl: function isPanelControl(node) {
	      return BX.hasClass(node, this.parent.settings.get('classPanelControl'));
	    },
	    getTextInputs: function getTextInputs() {
	      return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="text"]');
	    },
	    getHiddenInputs: function getHiddenInputs() {
	      return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="hidden"]');
	    },
	    getSelects: function getSelects() {
	      return BX.Grid.Utils.getBySelector(this.getPanel(), 'select');
	    },
	    getDropdowns: function getDropdowns() {
	      return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classDropdown'));
	    },
	    getCheckboxes: function getCheckboxes() {
	      return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelCheckbox'));
	    },
	    getButtons: function getButtons() {
	      return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelButton'));
	    },
	    isDropdown: function isDropdown(node) {
	      return BX.hasClass(node, this.parent.settings.get('classDropdown'));
	    },
	    isCheckbox: function isCheckbox(node) {
	      return BX.hasClass(node, this.parent.settings.get('classPanelCheckbox'));
	    },
	    isTextInput: function isTextInput(node) {
	      return node.type === 'text';
	    },
	    isHiddenInput: function isHiddenInput(node) {
	      return node.type === 'hidden';
	    },
	    isSelect: function isSelect(node) {
	      return node.tagName === 'SELECT';
	    },
	    createDropdown: function createDropdown(data, relative) {
	      var emptyText = data.EMPTY_TEXT || '';
	      var isMultiple = data.MULTIPLE === 'Y';
	      var container = this.createContainer(data.ID, relative, {});
	      var dropdown = BX.create('div', {
	        props: {
	          className: 'main-dropdown main-grid-panel-control',
	          id: data.ID + '_control'
	        },
	        attrs: {
	          name: data.NAME,
	          'data-name': data.NAME,
	          'data-empty-text': emptyText,
	          'data-multiple': isMultiple ? 'Y' : 'N',
	          'data-items': JSON.stringify(data.ITEMS),
	          'data-value': isMultiple ? '' : data.ITEMS[0].VALUE,
	          'data-popup-position': 'fixed'
	        },
	        children: [BX.create('span', {
	          props: {
	            className: 'main-dropdown-inner'
	          },
	          html: isMultiple ? emptyText : data.ITEMS[0].NAME
	        })]
	      });
	      container.appendChild(dropdown);
	      return container;
	    },
	    createCheckbox: function createCheckbox(data, relative) {
	      var checkbox = this.createContainer(data.ID, relative, {});
	      var inner = BX.create('span', {
	        props: {
	          className: 'main-grid-checkbox-container'
	        }
	      });
	      var titleSpan = BX.create('span', {
	        props: {
	          className: 'main-grid-control-panel-content-title'
	        }
	      });
	      var input = BX.create('input', {
	        props: {
	          type: 'checkbox',
	          className: this.parent.settings.get('classPanelCheckbox') + ' main-grid-checkbox',
	          id: data.ID + '_control'
	        },
	        attrs: {
	          value: data.VALUE || '',
	          title: data.TITLE || '',
	          name: data.NAME || '',
	          'data-onchange': JSON.stringify(data.ONCHANGE)
	        }
	      });
	      input.checked = data.CHECKED || null;
	      checkbox.appendChild(inner);
	      checkbox.appendChild(titleSpan);
	      inner.appendChild(input);
	      inner.appendChild(BX.create('label', {
	        props: {
	          className: 'main-grid-checkbox'
	        },
	        attrs: {
	          "for": data.ID + '_control',
	          title: data.TITLE
	        }
	      }));
	      titleSpan.appendChild(BX.create('label', {
	        attrs: {
	          "for": data.ID + '_control',
	          title: data.TITLE
	        },
	        html: data.LABEL
	      }));
	      return checkbox;
	    },

	    /**
	     * @param {object} data
	     * @param {object} data.ID
	     * @param {object} data.TITLE
	     * @param {object} data.PLACEHOLDER
	     * @param {object} data.ONCHANGE
	     * @param {string} relative
	     * @returns {*}
	     */
	    createText: function createText(data, relative) {
	      var container = this.createContainer(data.ID, relative, {});
	      var title = BX.type.isNotEmptyString(data["TITLE"]) ? data["TITLE"] : "";

	      if (title !== "") {
	        container.appendChild(BX.create('label', {
	          attrs: {
	            title: title,
	            "for": data.ID + '_control'
	          },
	          text: title
	        }));
	      }

	      container.appendChild(BX.create('input', {
	        props: {
	          className: 'main-grid-control-panel-input-text main-grid-panel-control',
	          id: data.ID + '_control'
	        },
	        attrs: {
	          name: data.NAME,
	          title: title,
	          placeholder: data.PLACEHOLDER || '',
	          value: data.VALUE || '',
	          type: 'text',
	          'data-onchange': JSON.stringify(data.ONCHANGE || [])
	        }
	      }));
	      return container;
	    },
	    createHidden: function createHidden(data, relative) {
	      var container = this.createContainer(data.ID, relative, {
	        CLASS: 'main-grid-panel-hidden-control-container'
	      });
	      container.appendChild(BX.create('input', {
	        props: {
	          id: data.ID + '_control',
	          type: 'hidden'
	        },
	        attrs: {
	          name: data.NAME,
	          value: data.VALUE || ''
	        }
	      }));
	      return container;
	    },
	    createButton: function createButton(data, relative) {
	      this.buttonOnChange = data.ONCHANGE || [];
	      this.buttonData = data;
	      this.button = this.createButtonNode(data);
	      BX.removeCustomEvent(window, 'Grid::unselectRow', BX.proxy(this.prepareButton, this));
	      BX.removeCustomEvent(window, 'Grid::selectRow', BX.proxy(this.prepareButton, this));
	      BX.removeCustomEvent(window, 'Grid::allRowsSelected', BX.proxy(this.prepareButton, this));
	      BX.removeCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this.prepareButton, this));

	      if (this.buttonData.SETTINGS && data.ID === this.buttonData.SETTINGS.buttonId) {
	        BX.addCustomEvent(window, 'Grid::unselectRow', BX.proxy(this.prepareButton, this));
	        BX.addCustomEvent(window, 'Grid::selectRow', BX.proxy(this.prepareButton, this));
	        BX.addCustomEvent(window, 'Grid::allRowsSelected', BX.proxy(this.prepareButton, this));
	        BX.addCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this.prepareButton, this));
	      }

	      this.prepareButton();
	      var container = this.createContainer(data.ID, relative, {});
	      container.appendChild(this.button);
	      return container;
	    },
	    createButtonNode: function createButtonNode(data) {
	      return BX.create('button', {
	        props: {
	          className: 'main-grid-buttons' + (data.CLASS ? ' ' + data.CLASS : ''),
	          id: data.ID + '_control',
	          title: BX.type.isNotEmptyString(data.TITLE) ? data.TITLE : ''
	        },
	        attrs: {
	          name: data.NAME || ''
	        },
	        html: data.TEXT
	      });
	    },
	    prepareButton: function prepareButton() {
	      if (this.isSetButtonDisabled()) {
	        BX.Dom.attr(this.button, 'data-onchange', []);
	        BX.Dom.addClass(this.button, 'ui-btn ui-btn-disabled');
	      } else {
	        BX.Dom.attr(this.button, 'data-onchange', this.buttonOnChange);
	        BX.Dom.removeClass(this.button, 'ui-btn ui-btn-disabled');
	      }
	    },
	    isSetButtonDisabled: function isSetButtonDisabled() {
	      return !!(this.buttonData.SETTINGS && this.buttonData.SETTINGS.minSelectedRows && this.getSelectedIds().length < this.buttonData.SETTINGS.minSelectedRows);
	    },

	    /**
	     * @param {object} data
	     * @param {object} data.ID
	     * @param {object} data.TITLE
	     * @param {object} data.PLACEHOLDER
	     * @param {object} data.ONCHANGE
	     * @param {object} data.CLASS
	     * @param {object} data.HREF
	     * @param {string} relative
	     * @returns {*}
	     */
	    createLink: function createLink(data, relative) {
	      var container = this.createContainer(data.ID, relative, {});
	      var link = BX.create('a', {
	        props: {
	          className: 'main-grid-link' + (data.CLASS ? ' ' + data.CLASS : ''),
	          id: data.ID + '_control'
	        },
	        attrs: {
	          href: data.HREF || '',
	          'data-onchange': JSON.stringify(data.ONCHANGE || [])
	        },
	        html: data.TEXT
	      });
	      container.appendChild(link);
	      return container;
	    },
	    createCustom: function createCustom(data, relative) {
	      var container = this.createContainer(data.ID, relative, {
	        CLASS: 'main-grid-panel-hidden-control-container'
	      });
	      var custom = BX.create('div', {
	        props: {
	          className: 'main-grid-panel-custom' + (data.CLASS ? ' ' + data.CLASS : '')
	        },
	        html: data.VALUE
	      });
	      container.appendChild(custom);
	      return container;
	    },
	    createContainer: function createContainer(id, relative, options) {
	      id = id.replace('_control', '');
	      relative = relative.replace('_control', '');
	      options = options || {};
	      return BX.create('span', {
	        props: {
	          className: this.parent.settings.get('classPanelControlContainer') + (options.CLASS ? ' ' + options.CLASS : ''),
	          id: id
	        },
	        attrs: {
	          'data-relative': relative
	        }
	      });
	    },
	    removeItemsRelativeCurrent: function removeItemsRelativeCurrent(node) {
	      var element = node;
	      var relative = [node.id];
	      var result = [];
	      var dataRelative;

	      while (element) {
	        dataRelative = BX.data(element, 'relative');

	        if (relative.includes(dataRelative)) {
	          relative.push(element.id);
	          result.push(element);
	        }

	        element = element.nextElementSibling;
	      }

	      result.forEach(function (current) {
	        BX.remove(current);
	      });
	    },
	    validateData: function validateData(data) {
	      return 'ONCHANGE' in data && BX.type.isArray(data.ONCHANGE);
	    },
	    activateControl: function activateControl(id) {
	      var element = BX(id);

	      if (BX.type.isDomNode(element)) {
	        BX.removeClass(element, this.parent.settings.get('classDisable'));
	        element.disabled = null;
	      }
	    },
	    deactivateControl: function deactivateControl(id) {
	      var element = BX(id);

	      if (BX.type.isDomNode(element)) {
	        BX.addClass(element, this.parent.settings.get('classDisable'));
	        element.disabled = true;
	      }
	    },
	    showControl: function showControl(id) {
	      var control = BX(id);
	      control && BX.show(control);
	    },
	    hideControl: function hideControl(id) {
	      var control = BX(id);
	      control && BX.hide(control);
	    },
	    validateActionObject: function validateActionObject(action) {
	      return BX.type.isPlainObject(action) && 'ACTION' in action && BX.type.isNotEmptyString(action.ACTION) && (action.ACTION === this.actions.RESET_CONTROLS || 'DATA' in action && BX.type.isArray(action.DATA));
	    },
	    validateControlObject: function validateControlObject(controlObject) {
	      return BX.type.isPlainObject(controlObject) && 'TYPE' in controlObject && 'ID' in controlObject;
	    },
	    createDate: function createDate(data, relative) {
	      var container = this.createContainer(data.ID, relative, {});
	      var date = BX.decl({
	        block: 'main-ui-date',
	        mix: ['main-grid-panel-date'],
	        calendarButton: true,
	        valueDelete: true,
	        placeholder: 'PLACEHOLDER' in data ? data.PLACEHOLDER : '',
	        name: 'NAME' in data ? data.NAME + '_from' : '',
	        tabindex: 'TABINDEX' in data ? data.TABINDEX : '',
	        value: 'VALUE' in data ? data.VALUE : '',
	        enableTime: 'TIME' in data ? data.TIME ? 'true' : 'false' : 'false'
	      });
	      container.appendChild(date);
	      return container;
	    },
	    createControl: function createControl(controlObject, relativeId) {
	      var newElement = null;

	      switch (controlObject.TYPE) {
	        case this.types.DROPDOWN:
	          newElement = this.createDropdown(controlObject, relativeId);
	          break;

	        case this.types.CHECKBOX:
	          newElement = this.createCheckbox(controlObject, relativeId);
	          break;

	        case this.types.TEXT:
	          newElement = this.createText(controlObject, relativeId);
	          break;

	        case this.types.HIDDEN:
	          newElement = this.createHidden(controlObject, relativeId);
	          break;

	        case this.types.BUTTON:
	          newElement = this.createButton(controlObject, relativeId);
	          break;

	        case this.types.LINK:
	          newElement = this.createLink(controlObject, relativeId);
	          break;

	        case this.types.CUSTOM:
	          newElement = this.createCustom(controlObject, relativeId);
	          break;

	        case this.types.DATE:
	          newElement = this.createDate(controlObject, relativeId);
	          break;
	      }

	      return newElement;
	    },
	    onChangeHandler: function onChangeHandler(container, actions, isPseudo) {
	      var newElement, callback;
	      var self = this;

	      if (BX.type.isDomNode(container) && BX.type.isArray(actions)) {
	        actions.forEach(function (action) {
	          if (self.validateActionObject(action)) {
	            if (action.ACTION === self.actions.CREATE) {
	              self.removeItemsRelativeCurrent(container);
	              action.DATA.reverse();
	              action.DATA.forEach(function (controlObject) {
	                if (self.validateControlObject(controlObject)) {
	                  newElement = self.createControl(controlObject, container.id || BX.data(container, 'relative'));

	                  if (BX.type.isDomNode(newElement)) {
	                    BX.insertAfter(newElement, container);

	                    if ('ONCHANGE' in controlObject && controlObject.TYPE === self.types.CHECKBOX && 'CHECKED' in controlObject && controlObject.CHECKED) {
	                      self.onChangeHandler(newElement, controlObject.ONCHANGE);
	                    }

	                    if (controlObject.TYPE === self.types.DROPDOWN && BX.type.isArray(controlObject.ITEMS) && controlObject.ITEMS.length && 'ONCHANGE' in controlObject.ITEMS[0] && BX.type.isArray(controlObject.ITEMS[0].ONCHANGE)) {
	                      self.onChangeHandler(newElement, controlObject.ITEMS[0].ONCHANGE);
	                    }
	                  }
	                }
	              });
	            }

	            if (action.ACTION === self.actions.ACTIVATE) {
	              self.removeItemsRelativeCurrent(container);

	              if (BX.type.isArray(action.DATA)) {
	                action.DATA.forEach(function (currentId) {
	                  self.lastActivated.push(currentId.ID);
	                  self.activateControl(currentId.ID);
	                });
	              }
	            }

	            if (action.ACTION === self.actions.SHOW) {
	              if (BX.type.isArray(action.DATA)) {
	                action.DATA.forEach(function (showCurrent) {
	                  self.showControl(showCurrent.ID);
	                });
	              }
	            }

	            if (action.ACTION === self.actions.HIDE) {
	              if (BX.type.isArray(action.DATA)) {
	                action.DATA.forEach(function (hideCurrent) {
	                  self.hideControl(hideCurrent.ID);
	                });
	              }
	            }

	            if (action.ACTION === self.actions.HIDE_ALL_EXPECT) {
	              if (BX.type.isArray(action.DATA)) {
	                (self.getControls() || []).forEach(function (current) {
	                  if (!action.DATA.some(function (el) {
	                    return el.ID === current.id;
	                  })) {
	                    self.hideControl(current.id);
	                  }
	                });
	              }
	            }

	            if (action.ACTION === self.actions.SHOW_ALL) {
	              (self.getControls() || []).forEach(function (current) {
	                self.showControl(current.id);
	              });
	            }

	            if (action.ACTION === self.actions.REMOVE) {
	              if (BX.type.isArray(action.DATA)) {
	                action.DATA.forEach(function (removeCurrent) {
	                  BX.remove(BX(removeCurrent.ID));
	                });
	              }
	            }

	            if (action.ACTION === self.actions.CALLBACK) {
	              this.confirmDialog(action, BX.delegate(function () {
	                if (BX.type.isArray(action.DATA)) {
	                  action.DATA.forEach(function (currentCallback) {
	                    if (currentCallback.JS.indexOf('Grid.') !== -1) {
	                      callback = currentCallback.JS.replace('Grid', 'self.parent');
	                      callback = callback.replace('()', '');
	                      callback += '.apply(self.parent, [container])';

	                      try {
	                        eval(callback); // jshint ignore:line
	                      } catch (err) {
	                        throw new Error(err);
	                      }
	                    } else if (BX.type.isNotEmptyString(currentCallback.JS)) {
	                      try {
	                        eval(currentCallback.JS);
	                      } catch (err) {
	                        throw new Error(err);
	                      }
	                    }
	                  });
	                }
	              }, this));
	            }

	            if (action.ACTION === self.actions.RESET_CONTROLS) {
	              this.removeItemsRelativeCurrent(container);
	            }
	          }
	        }, this);
	      } else {
	        if (!isPseudo) {
	          this.removeItemsRelativeCurrent(container);
	        }

	        self.lastActivated.forEach(function (current) {
	          self.deactivateControl(current);
	        });
	        self.lastActivated = [];
	      }
	    },
	    confirmDialog: function confirmDialog(action, then, cancel) {
	      this.parent.confirmDialog(action, then, cancel);
	    },

	    /**
	     * Dropdown value change handler
	     * @param {string} id Dropdown id
	     * @param {object} event
	     * @param item
	     * @param {object} dataItem
	     * @param {object} dataItem.ONCHANGE
	     * @param {boolean} dataItem.PSEUDO
	     * @private
	     */
	    _dropdownChange: function _dropdownChange(id, event, item, dataItem) {
	      var dropdown = BX(id);
	      var container = dropdown.parentNode;
	      var onChange = dataItem && 'ONCHANGE' in dataItem ? dataItem.ONCHANGE : null;
	      var isPseudo = dataItem && 'PSEUDO' in dataItem && dataItem.PSEUDO !== false;
	      this.onChangeHandler(container, onChange, isPseudo);
	    },
	    _checkboxChange: function _checkboxChange(event) {
	      var onChange;

	      try {
	        onChange = eval(BX.data(event.target, 'onchange'));
	      } catch (err) {
	        onChange = null;
	      }

	      this.onChangeHandler(BX.findParent(event.target, {
	        className: this.parent.settings.get('classPanelContainer')
	      }, true, false), event.target.checked || event.target.id.indexOf('actallrows_') !== -1 ? onChange : null);
	    },
	    _clickOnButton: function _clickOnButton(event) {
	      var onChange;

	      if (this.isButton(event.target)) {
	        event.preventDefault();

	        try {
	          onChange = eval(BX.data(event.target, 'onchange'));
	        } catch (err) {
	          onChange = null;
	        }

	        this.onChangeHandler(BX.findParent(event.target, {
	          className: this.parent.settings.get('classPanelContainer')
	        }, true, false), onChange);
	      }
	    },
	    isButton: function isButton(node) {
	      return BX.hasClass(node, this.parent.settings.get('classPanelButton'));
	    },
	    getSelectedIds: function getSelectedIds() {
	      var rows = this.parent.getRows().getSelected().filter(function (row) {
	        return row.isShown();
	      });
	      return rows.map(function (current) {
	        return current.getId();
	      });
	    },
	    getControls: function getControls() {
	      return BX.findChild(this.getPanel(), {
	        className: this.parent.settings.get('classPanelControlContainer')
	      }, true, true);
	    },
	    getValues: function getValues() {
	      var data = {};
	      var self = this;
	      var controls = [].concat(this.getDropdowns(), this.getTextInputs(), this.getHiddenInputs(), this.getSelects(), this.getCheckboxes(), this.getButtons());
	      (controls || []).forEach(function (current) {
	        if (BX.type.isDomNode(current)) {
	          if (self.isDropdown(current)) {
	            var dropdownValue = BX.data(current, 'value');
	            var multiple = BX.data(current, 'multiple') === 'Y';
	            dropdownValue = dropdownValue !== null && dropdownValue !== undefined ? dropdownValue : '';
	            data[BX.data(current, 'name')] = multiple ? dropdownValue.split(',') : dropdownValue;
	          }

	          if (self.isSelect(current)) {
	            data[current.getAttribute('name')] = current.options[current.selectedIndex].value;
	          }

	          if (self.isCheckbox(current) && current.checked) {
	            data[current.getAttribute('name')] = current.value;
	          }

	          if (self.isTextInput(current) || self.isHiddenInput(current)) {
	            data[current.getAttribute('name')] = current.value;
	          }

	          if (self.isButton(current)) {
	            var name = BX.data(current, 'name');
	            var value = BX.data(current, 'value');
	            value = value !== null && value !== undefined ? value : '';

	            if (name) {
	              data[name] = value;
	            }
	          }
	        }
	      });
	      return data;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * Base class
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.BaseClass = function (parent) {
	    this.parent = parent;
	  };

	  BX.Grid.BaseClass.prototype = {
	    getParent: function getParent() {
	      return this.parent;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.ColsSortable
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.ColsSortable = function (parent) {
	    this.parent = null;
	    this.dragItem = null;
	    this.targetItem = null;
	    this.rowsList = null;
	    this.colsList = null;
	    this.dragRect = null;
	    this.offset = null;
	    this.startDragOffset = null;
	    this.dragColumn = null;
	    this.targetColumn = null;
	    this.isDrag = null;
	    this.init(parent);
	  };

	  BX.Grid.ColsSortable.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.colsList = this.getColsList();
	      this.rowsList = this.getRowsList();

	      if (!this.inited) {
	        this.inited = true;
	        BX.addCustomEvent('Grid::updated', BX.proxy(this.reinit, this));
	        BX.addCustomEvent('Grid::headerUpdated', BX.proxy(this.reinit, this));
	      }

	      this.registerObjects();
	    },
	    destroy: function destroy() {
	      BX.removeCustomEvent('Grid::updated', BX.proxy(this.reinit, this));
	      this.unregisterObjects();
	    },
	    reinit: function reinit() {
	      this.unregisterObjects();
	      this.reset();
	      this.init(this.parent);
	    },
	    reset: function reset() {
	      this.dragItem = null;
	      this.targetItem = null;
	      this.rowsList = null;
	      this.colsList = null;
	      this.dragRect = null;
	      this.offset = null;
	      this.startDragOffset = null;
	      this.dragColumn = null;
	      this.targetColumn = null;
	      this.isDrag = null;
	      this.fixedTableColsList = null;
	    },
	    isActive: function isActive() {
	      return this.isDrag;
	    },
	    registerObjects: function registerObjects() {
	      this.unregisterObjects();
	      this.getColsList().forEach(this.register, this);
	      this.getFixedHeaderColsList().forEach(this.register, this);
	    },
	    unregisterObjects: function unregisterObjects() {
	      this.getColsList().forEach(this.unregister, this);
	      this.getFixedHeaderColsList().forEach(this.unregister, this);
	    },
	    unregister: function unregister(column) {
	      jsDD.unregisterObject(column);
	    },
	    register: function register(column) {
	      column.onbxdragstart = BX.proxy(this._onDragStart, this);
	      column.onbxdrag = BX.proxy(this._onDrag, this);
	      column.onbxdragstop = BX.proxy(this._onDragEnd, this);
	      jsDD.registerObject(column);
	    },
	    getColsList: function getColsList() {
	      if (!this.colsList) {
	        this.colsList = BX.Grid.Utils.getByTag(this.parent.getRows().getHeadFirstChild().getNode(), 'th');
	        this.colsList = this.colsList.filter(function (current) {
	          return !this.isStatic(current);
	        }, this);
	      }

	      return this.colsList;
	    },
	    getFixedHeaderColsList: function getFixedHeaderColsList() {
	      if (!this.fixedTableColsList && this.parent.getParam('ALLOW_PIN_HEADER')) {
	        this.fixedTableColsList = BX.Grid.Utils.getByTag(this.parent.getPinHeader().getFixedTable(), 'th');
	        this.fixedTableColsList = this.fixedTableColsList.filter(function (current) {
	          return !this.isStatic(current);
	        }, this);
	      }

	      return this.fixedTableColsList || [];
	    },
	    getRowsList: function getRowsList() {
	      var rowsList = this.parent.getRows().getSourceRows();

	      if (this.parent.getParam('ALLOW_PIN_HEADER')) {
	        rowsList = rowsList.concat(BX.Grid.Utils.getByTag(this.parent.getPinHeader().getFixedTable(), 'tr'));
	      }

	      return rowsList;
	    },
	    isStatic: function isStatic(item) {
	      return BX.hasClass(item, this.parent.settings.get('classCellStatic')) && !BX.hasClass(item, 'main-grid-fixed-column');
	    },
	    getDragOffset: function getDragOffset() {
	      var offset = this.parent.getScrollContainer().scrollLeft - this.startScrollOffset;
	      return jsDD.x - this.startDragOffset - this.dragRect.left + offset;
	    },
	    getColumn: function getColumn(cell) {
	      var column = [];

	      if (cell instanceof HTMLTableCellElement) {
	        column = this.rowsList.map(function (row) {
	          return row.cells[cell.cellIndex];
	        });
	      }

	      return column;
	    },
	    _onDragStart: function _onDragStart() {
	      if (this.parent.getParam('ALLOW_PIN_HEADER') && this.parent.getPinHeader().isPinned()) {
	        this.colsList = this.getFixedHeaderColsList();
	      } else {
	        this.colsList = this.getColsList();
	      }

	      this.startScrollOffset = this.parent.getScrollContainer().scrollLeft;
	      this.isDrag = true;
	      this.dragItem = jsDD.current_node;
	      this.dragRect = this.dragItem.getBoundingClientRect();
	      this.offset = Math.ceil(this.dragRect.width);
	      this.startDragOffset = jsDD.start_x - this.dragRect.left;
	      this.dragColumn = this.getColumn(this.dragItem);
	      this.dragIndex = BX.Grid.Utils.getIndex(this.colsList, this.dragItem);
	      this.parent.preventSortableClick = true;
	    },
	    isDragToRight: function isDragToRight(node, index) {
	      var nodeClientRect = node.getBoundingClientRect();
	      var nodeCenter = Math.ceil(nodeClientRect.left + nodeClientRect.width / 2 + BX.scrollLeft(window));
	      var dragIndex = this.dragIndex;
	      var x = jsDD.x;
	      return index > dragIndex && x > nodeCenter;
	    },
	    isDragToLeft: function isDragToLeft(node, index) {
	      var nodeClientRect = node.getBoundingClientRect();
	      var nodeCenter = Math.ceil(nodeClientRect.left + nodeClientRect.width / 2 + BX.scrollLeft(window));
	      var dragIndex = this.dragIndex;
	      var x = jsDD.x;
	      return index < dragIndex && x < nodeCenter;
	    },
	    isDragToBack: function isDragToBack(node, index) {
	      var nodeClientRect = node.getBoundingClientRect();
	      var nodeCenter = Math.ceil(nodeClientRect.left + nodeClientRect.width / 2 + BX.scrollLeft(window));
	      var dragIndex = this.dragIndex;
	      var x = jsDD.x;
	      return index > dragIndex && x < nodeCenter || index < dragIndex && x > nodeCenter;
	    },
	    isMovedToRight: function isMovedToRight(node) {
	      return node.style.transform === 'translate3d(' + -this.offset + 'px, 0px, 0px)';
	    },
	    isMovedToLeft: function isMovedToLeft(node) {
	      return node.style.transform === 'translate3d(' + this.offset + 'px, 0px, 0px)';
	    },
	    isMoved: function isMoved(node) {
	      return node.style.transform !== 'translate3d(0px, 0px, 0px)' && node.style.transform !== '';
	    },

	    /**
	     * Moves grid column by offset
	     * @param {array} column - Array cells of column
	     * @param {int} offset - Pixels offset
	     * @param {int} [transition = 300] - Transition duration in milliseconds
	     */
	    moveColumn: function moveColumn(column, offset, transition) {
	      transition = BX.type.isNumber(transition) ? transition : 300;
	      BX.Grid.Utils.styleForEach(column, {
	        'transition': transition + 'ms',
	        'transform': 'translate3d(' + offset + 'px, 0px, 0px)'
	      });
	    },
	    _onDrag: function _onDrag() {
	      this.dragOffset = this.getDragOffset();
	      this.targetItem = this.targetItem || this.dragItem;
	      this.targetColumn = this.targetColumn || this.dragColumn;
	      var leftOffset = -this.offset;
	      var rightOffset = this.offset;
	      var defaultOffset = 0;
	      var dragTransitionDuration = 0;
	      this.moveColumn(this.dragColumn, this.dragOffset, dragTransitionDuration);
	      [].forEach.call(this.colsList, function (current, index) {
	        if (current && !current.classList.contains('main-grid-cell-static')) {
	          if (this.isDragToRight(current, index) && !this.isMovedToRight(current)) {
	            this.targetColumn = this.getColumn(current);
	            this.moveColumn(this.targetColumn, leftOffset);
	          }

	          if (this.isDragToLeft(current, index) && !this.isMovedToLeft(current)) {
	            this.targetColumn = this.getColumn(current);
	            this.moveColumn(this.targetColumn, rightOffset);
	          }

	          if (this.isDragToBack(current, index) && this.isMoved(current)) {
	            this.targetColumn = this.getColumn(current);
	            this.moveColumn(this.targetColumn, defaultOffset);
	          }
	        }
	      }, this);
	    },
	    _onDragEnd: function _onDragEnd() {
	      [].forEach.call(this.dragColumn, function (current, index) {
	        BX.Grid.Utils.collectionSort(current, this.targetColumn[index]);
	      }, this);
	      this.rowsList.forEach(function (current) {
	        BX.Grid.Utils.styleForEach(current.cells, {
	          transition: '',
	          transform: ''
	        });
	      });
	      this.reinit();
	      var columns = this.colsList.map(function (current) {
	        return BX.data(current, 'name');
	      });
	      this.parent.getUserOptions().setColumns(columns);
	      BX.onCustomEvent(this.parent.getContainer(), 'Grid::columnMoved', [this.parent]);
	      setTimeout(function () {
	        this.parent.preventSortableClick = false;
	      }.bind(this), 10);
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  var originalUpdatePageData = window.parent.BX.ajax.UpdatePageData;

	  function disableBxAjaxUpdatePageData() {
	    window.parent.BX.ajax.UpdatePageData = function () {};
	  }

	  function enableBxAjaxUpdatePageData() {
	    window.parent.BX.ajax.UpdatePageData = originalUpdatePageData;
	  }
	  /**
	   * Works with requests and server response
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */


	  BX.Grid.Data = function (parent) {
	    this.parent = parent;
	    this.reset();
	  };
	  /**
	   * Reset to default values
	   * @private
	   */


	  BX.Grid.Data.prototype.reset = function () {
	    this.response = null;
	    this.xhr = null;
	    this.headRows = null;
	    this.bodyRows = null;
	    this.footRows = null;
	    this.moreButton = null;
	    this.pagination = null;
	    this.counterDisplayed = null;
	    this.counterSelected = null;
	    this.counterTotal = null;
	    this.limit = null;
	    this.actionPanel = null;
	    this.rowsByParentId = {};
	    this.rowById = {};
	    this.isValidResponse = null;
	  };
	  /**
	   * Gets filter
	   * @return {BX.Main.Filter}
	   */


	  BX.Grid.Data.prototype.getParent = function () {
	    return this.parent;
	  };
	  /**
	   * Validates server response
	   * @return {boolean}
	   */


	  BX.Grid.Data.prototype.validateResponse = function () {
	    if (!BX.type.isBoolean(this.isValidResponse)) {
	      this.isValidResponse = !!this.getResponse() && !!BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classContainer'), true);
	    }

	    return this.isValidResponse;
	  };
	  /**
	   * Send request
	   * @param {string} [url]
	   * @param {string} [method]
	   * @param {object} [data]
	   * @param {string} [action]
	   * @param {function} [then]
	   * @param {function} [error]
	   */


	  BX.Grid.Data.prototype.request = function (url, method, data, action, then, error) {
	    if (!BX.type.isString(url)) {
	      url = "";
	    }

	    if (!BX.type.isNotEmptyString(method)) {
	      method = "GET";
	    }

	    if (!BX.type.isPlainObject(data)) {
	      data = {};
	    }

	    var eventArgs = {
	      gridId: this.parent.getId(),
	      url: url,
	      method: method,
	      data: data
	    };
	    this.parent.disableCheckAllCheckboxes();
	    BX.onCustomEvent(window, "Grid::beforeRequest", [this, eventArgs]);

	    if (eventArgs.hasOwnProperty("cancelRequest") && eventArgs.cancelRequest === true) {
	      return;
	    }

	    url = eventArgs.url;

	    if (!BX.type.isNotEmptyString(url)) {
	      url = this.parent.baseUrl;
	    }

	    url = BX.Grid.Utils.addUrlParams(url, {
	      sessid: BX.bitrix_sessid(),
	      internal: 'true',
	      grid_id: this.parent.getId()
	    });

	    if ('apply_filter' in data && data.apply_filter === 'Y') {
	      url = BX.Grid.Utils.addUrlParams(url, {
	        apply_filter: 'Y'
	      });
	    } else {
	      url = BX.util.remove_url_param(url, 'apply_filter');
	    }

	    if ('clear_nav' in data && data.clear_nav === 'Y') {
	      url = BX.Grid.Utils.addUrlParams(url, {
	        clear_nav: 'Y'
	      });
	    } else {
	      url = BX.util.remove_url_param(url, 'clear_nav');
	    }

	    url = BX.Grid.Utils.addUrlParams(url, {
	      grid_action: action || 'showpage'
	    });
	    method = eventArgs.method;
	    data = eventArgs.data;
	    this.reset();
	    var self = this;
	    setTimeout(function () {
	      var formData = BX.Http.Data.convertObjectToFormData(data);
	      disableBxAjaxUpdatePageData();
	      var xhr = BX.ajax({
	        url: BX.Grid.Utils.ajaxUrl(url, self.getParent().getAjaxId()),
	        data: formData,
	        method: method,
	        dataType: 'html',
	        headers: [{
	          name: 'X-Ajax-Grid-UID',
	          value: self.getParent().getAjaxId()
	        }, {
	          name: 'X-Ajax-Grid-Req',
	          value: JSON.stringify({
	            action: action || 'showpage'
	          })
	        }],
	        processData: true,
	        scriptsRunFirst: false,
	        start: false,
	        preparePost: false,
	        onsuccess: function onsuccess(response) {
	          self.response = BX.create('div', {
	            html: response
	          });
	          self.response = self.response.querySelector('#' + self.parent.getContainerId());
	          self.xhr = xhr;

	          if (self.parent.getParam('HANDLE_RESPONSE_ERRORS')) {
	            var res;

	            try {
	              res = JSON.parse(response);
	            } catch (err) {
	              res = {
	                messages: []
	              };
	            }

	            if (res.messages.length) {
	              self.parent.arParams['MESSAGES'] = res.messages;
	              self.parent.messages.show();
	              self.parent.tableUnfade();

	              if (BX.type.isFunction(error)) {
	                BX.delegate(error, self)(xhr);
	              }

	              return;
	            }
	          }

	          if (BX.type.isFunction(then)) {
	            self.parent.enableCheckAllCheckboxes();
	            BX.delegate(then, self)(response, xhr);
	          }

	          enableBxAjaxUpdatePageData();
	        },
	        onerror: function onerror(err) {
	          self.error = error;
	          self.xhr = xhr;

	          if (BX.type.isFunction(error)) {
	            self.parent.enableCheckAllCheckboxes();
	            BX.delegate(error, self)(xhr, err);
	          }
	        }
	      });
	      xhr.send(formData);
	    }, 0);
	  };
	  /**
	   * Gets server response
	   * @return {?Element}
	   */


	  BX.Grid.Data.prototype.getResponse = function () {
	    return this.response;
	  };
	  /**
	   * Gets head rows of grid from server response
	   * @return {?HTMLTableRowElement[]}
	   */


	  BX.Grid.Data.prototype.getHeadRows = function () {
	    if (!this.headRows) {
	      this.headRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classHeadRow'));
	    }

	    return this.headRows;
	  };
	  /**
	   * Gets body rows of grid form server request
	   * @return {?HTMLTableRowElement[]}
	   */


	  BX.Grid.Data.prototype.getBodyRows = function () {
	    if (!this.bodyRows) {
	      this.bodyRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classBodyRow'));
	    }

	    return this.bodyRows;
	  };
	  /**
	   * Gets rows by parent id
	   * @param {string|number} id
	   * @return {?HTMLTableRowElement[]}
	   */


	  BX.Grid.Data.prototype.getRowsByParentId = function (id) {
	    if (!(id in this.rowsByParentId)) {
	      this.rowsByParentId[id] = BX.Grid.Utils.getBySelector(this.getResponse(), '.' + this.getParent().settings.get('classBodyRow') + '[data-parent-id="' + id + '"]');
	    }

	    return this.rowsByParentId[id];
	  };
	  /**
	   * Gets row by row id
	   * @param {string|number} id
	   * @return {?HTMLTableRowElement}
	   */


	  BX.Grid.Data.prototype.getRowById = function (id) {
	    if (!(id in this.rowById)) {
	      this.rowById[id] = BX.Grid.Utils.getBySelector(this.getResponse(), '.' + this.getParent().settings.get('classBodyRow') + '[data-id="' + id + '"]', true);
	    }

	    return this.rowById[id];
	  };
	  /**
	   * Gets tfoot rows of grid from request
	   * @return {?HTMLTableRowElement[]}
	   */


	  BX.Grid.Data.prototype.getFootRows = function () {
	    if (!this.footRows) {
	      this.footRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classFootRow'));
	    }

	    return this.footRows;
	  };
	  /**
	   * Gets more button from request
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getMoreButton = function () {
	    if (!this.moreButton) {
	      this.moreButton = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classMoreButton'), true);
	    }

	    return this.moreButton;
	  };
	  /**
	   * Gets pagination of grid from request
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getPagination = function () {
	    if (!this.pagination) {
	      this.pagination = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classPagination'), true);

	      if (BX.type.isDomNode(this.pagination)) {
	        this.pagination = BX.firstChild(this.pagination);
	      }
	    }

	    return this.pagination;
	  };
	  /**
	   * Gets counter of displayed rows
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getCounterDisplayed = function () {
	    if (!this.counterDisplayed) {
	      this.counterDisplayed = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classCounterDisplayed'), true);
	    }

	    return this.counterDisplayed;
	  };
	  /**
	   * Gets counter of selected rows
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getCounterSelected = function () {
	    if (!this.counterSelected) {
	      this.counterSelected = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classCounterSelected'), true);
	    }

	    return this.counterSelected;
	  };
	  /**
	   * Gets counter of total rows count
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getCounterTotal = function () {
	    if (!BX.type.isDomNode(this.counterTotal)) {
	      var selector = '.' + this.getParent().settings.get('classCounterTotal') + ' .' + this.getParent().settings.get('classPanelCellContent');
	      this.counterTotal = BX.Grid.Utils.getBySelector(this.getResponse(), selector, true);
	    }

	    return this.counterTotal;
	  };
	  /**
	   * Gets dropdown of pagesize
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getLimit = function () {
	    if (!this.limit) {
	      this.limit = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classPageSize'), true);
	    }

	    return this.limit;
	  };
	  /**
	   * Gets dropdown of pagesize
	   * @alias BX.Grid.Data.prototype.getLimit
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getPageSize = function () {
	    return this.getLimit();
	  };
	  /**
	   * Gets action panel of grid
	   * @return {?HTMLElement}
	   */


	  BX.Grid.Data.prototype.getActionPanel = function () {
	    if (!this.actionPanel) {
	      this.actionPanel = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classActionPanel'), true);
	    }

	    return this.actionPanel;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main');
	  /**
	   * BX.Main.dropdown
	   * @param dropdown
	   */

	  BX.Main.dropdown = function (dropdown) {
	    this.id = null;
	    this.dropdown = null;
	    this.items = null;
	    this.value = null;
	    this.menuId = null;
	    this.menu = null;
	    this.menuItems = null;
	    this.multiple = null;
	    this.emptyText = null;
	    this.dataItems = 'items';
	    this.dataValue = 'value';
	    this.dataPseudo = 'pseudo';
	    this.dropdownItemClass = 'main-dropdown-item';
	    this.activeClass = 'main-dropdown-active';
	    this.selectedClass = 'main-dropdown-item-selected';
	    this.notSelectedClass = 'main-dropdown-item-not-selected';
	    this.lockedClass = 'main-dropdown-item-locked';
	    this.menuItemClass = 'menu-popup-item';
	    this.init(dropdown);
	  };

	  BX.Main.dropdown.prototype = {
	    init: function init(dropdown) {
	      this.id = dropdown.id;
	      this.dropdown = dropdown;
	      this.items = this.getItems();
	      this.value = this.getValue();
	      this.menuId = this.getMenuId();
	      this.multiple = this.getMultiple();
	      this.emptyText = this.getEmptyText();
	      this.menu = this.createMenu();
	      this.menu.popupWindow.show();
	      this.adjustPosition();
	      BX.bind(this.dropdown, 'click', BX.delegate(this.showMenu, this));
	    },
	    getMenuId: function getMenuId() {
	      return this.id + '_menu';
	    },
	    getItems: function getItems() {
	      var result;

	      try {
	        var str = BX.data(this.dropdown, this.dataItems);
	        result = eval(str);
	      } catch (err) {
	        result = [];
	      }

	      return result;
	    },
	    // single
	    getValue: function getValue() {
	      return BX.data(this.dropdown, this.dataValue);
	    },
	    getValueItem: function getValueItem() {
	      var value = this.getValue();
	      return this.getItems().find(function (item) {
	        return item.VALUE === value;
	      });
	    },
	    // multiple
	    getValueAsArray: function getValueAsArray() {
	      var value = this.getValue();

	      if (value === undefined) {
	        value = '';
	      }

	      return value.toString().split(',').filter(function (i) {
	        return i !== '';
	      });
	    },
	    getValueItems: function getValueItems() {
	      var values = this.getValueAsArray();
	      return this.getItems().filter(function (item) {
	        return values.includes(item.VALUE);
	      });
	    },
	    toggleValue: function toggleValue(value) {
	      if (this.multiple) {
	        if (value || value === 0 || value === '0') {
	          var values = this.getValueAsArray();
	          var index = values.indexOf(value);

	          if (index < 0) {
	            values.push(value);
	          } else {
	            values.splice(index, 1);
	          }

	          this.dropdown.dataset[this.dataValue] = values.join(',');
	        } else {
	          this.dropdown.dataset[this.dataValue] = null;
	        }
	      } else {
	        this.dropdown.dataset[this.dataValue] = value;
	      }
	    },
	    getValueText: function getValueText() {
	      if (this.multiple) {
	        return this.getValueItems().map(function (item) {
	          return item.NAME;
	        }).filter(function (i) {
	          return !!i;
	        }).join(", ") || this.emptyText;
	      }

	      var item = this.getValueItem();
	      return item ? item.NAME : this.emptyText;
	    },
	    getMultiple: function getMultiple() {
	      return this.dropdown.dataset.multiple === 'Y';
	    },
	    getEmptyText: function getEmptyText() {
	      return this.dropdown.dataset.emptyText || null;
	    },
	    prepareMenuItems: function prepareMenuItems() {
	      var self = this;
	      var attrs, subItem;
	      var currentValue = this.multiple ? this.getValueAsArray() : this.getValue();

	      function prepareItems(items) {
	        var isHtmlEntity = self.dropdown.dataset['htmlEntity'] === 'true';
	        return items.map(function (item) {
	          attrs = {};
	          attrs['data-' + self.dataValue] = item.VALUE;
	          attrs['data-' + self.dataPseudo] = 'PSEUDO' in item && item.PSEUDO ? 'true' : 'false';
	          subItem = BX.create('div', {
	            children: [BX.create('span', {
	              props: {
	                className: self.dropdownItemClass
	              },
	              attrs: attrs,
	              html: isHtmlEntity ? item.NAME : null,
	              text: isHtmlEntity ? null : item.NAME
	            })]
	          });
	          var selected = self.multiple ? currentValue.includes(item.VALUE) : currentValue === item.VALUE;
	          return {
	            html: subItem.innerHTML,
	            className: selected ? self.selectedClass : self.notSelectedClass,
	            delimiter: item.DELIMITER,
	            items: 'ITEMS' in item ? prepareItems(item.ITEMS) : null
	          };
	        });
	      }

	      var items = prepareItems(this.getItems());
	      BX.onCustomEvent(window, 'Dropdown::onPrepareItems', [this.id, this.menuId, items]);
	      return items;
	    },
	    createMenu: function createMenu() {
	      var self = this;
	      return BX.PopupMenu.create(this.getMenuId(), this.dropdown, this.prepareMenuItems(), {
	        'autoHide': true,
	        'offsetTop': -8,
	        'offsetLeft': +(this.dropdown.dataset.menuOffsetLeft || 40),
	        'maxHeight': +(this.dropdown.dataset.menuMaxHeight || 170),
	        'angle': {
	          'position': 'bottom',
	          'offset': 0
	        },
	        'events': {
	          'onPopupClose': BX.delegate(this._onCloseMenu, this),
	          'onPopupShow': function onPopupShow() {
	            self._onShowMenu();
	          }
	        }
	      });
	    },
	    showMenu: function showMenu() {
	      this.menu = BX.PopupMenu.getMenuById(this.menuId);

	      if (!this.menu) {
	        this.menu = this.createMenu();
	        this.menu.popupWindow.show();
	      }

	      this.adjustPosition();
	    },
	    adjustPosition: function adjustPosition() {
	      if (this.dropdown.dataset.popupPosition === 'fixed') {
	        var container = this.menu.popupWindow.popupContainer;
	        container.style.setProperty('top', 'auto');
	        container.style.setProperty('bottom', '45px');
	        container.style.setProperty('left', '0px');
	        this.dropdown.appendChild(container);
	      }
	    },
	    getSubItem: function getSubItem(node) {
	      return BX.Grid.Utils.getByClass(node, this.dropdownItemClass, true);
	    },
	    refresh: function refresh(item) {
	      var subItem = this.getSubItem(item);
	      var value = BX.data(subItem, this.dataValue);

	      if (BX.Type.isUndefined(value)) {
	        value = '';
	      }

	      this.toggleValue(value);
	      BX.firstChild(this.dropdown).innerText = this.getValueText();
	    },
	    selectItem: function selectItem(node) {
	      var self = this;
	      (this.menu.menuItems || []).forEach(function (current) {
	        // multiple
	        if (self.multiple) {
	          if (node === current.layout.item) {
	            if (BX.hasClass(node, self.selectedClass)) {
	              BX.addClass(current.layout.item, self.notSelectedClass);
	              BX.removeClass(current.layout.item, self.selectedClass);
	            } else {
	              BX.removeClass(current.layout.item, self.notSelectedClass);
	              BX.addClass(current.layout.item, self.selectedClass);
	            }
	          }

	          return;
	        } // single


	        BX.removeClass(current.layout.item, self.selectedClass);

	        if (node !== current.layout.item) {
	          BX.addClass(current.layout.item, self.notSelectedClass);
	        } else {
	          BX.removeClass(current.layout.item, self.notSelectedClass);
	          BX.addClass(current.layout.item, self.selectedClass);
	        }
	      });
	    },
	    lockedItem: function lockedItem(node) {
	      BX.addClass(node, this.lockedClass);
	    },
	    getDataItemIndexByValue: function getDataItemIndexByValue(items, value) {

	      if (BX.type.isArray(items)) {
	        items.map(function (current, index) {
	          if (current.VALUE === value) {
	            return false;
	          }
	        });
	      }

	      return false;
	    },
	    getDataItemByValue: function getDataItemByValue(value) {
	      var result = this.getItems().filter(function (current) {
	        return current.VALUE === value;
	      });
	      return result.length > 0 ? result[0] : null;
	    },
	    _onShowMenu: function _onShowMenu() {
	      var self = this;
	      BX.addClass(this.dropdown, this.activeClass);
	      (this.menu.menuItems || []).forEach(function (current) {
	        BX.bind(current.layout.item, 'click', BX.delegate(self._onItemClick, self));
	      });
	    },
	    _onCloseMenu: function _onCloseMenu() {
	      BX.removeClass(this.dropdown, this.activeClass);
	      BX.PopupMenu.destroy(this.menuId);
	    },
	    _onItemClick: function _onItemClick(event) {
	      var item = this.getMenuItem(event.target);
	      var value, dataItem;
	      var subItem = this.getSubItem(item);
	      var isPseudo = BX.data(subItem, 'pseudo');

	      if (!(isPseudo === 'true')) {
	        this.refresh(item);
	        this.selectItem(item);

	        if (!this.multiple) {
	          this.menu.popupWindow.close();
	        }

	        value = this.getValue();
	        dataItem = this.getDataItemByValue(value);
	      } else {
	        value = BX.data(subItem, 'value');
	        dataItem = this.getDataItemByValue(value);
	      }

	      event.stopPropagation();
	      BX.onCustomEvent(window, 'Dropdown::change', [this.dropdown.id, event, item, dataItem, value]);
	    },
	    getMenuItem: function getMenuItem(node) {
	      var item = node;

	      if (!BX.hasClass(item, this.menuItemClass)) {
	        item = BX.findParent(item, {
	          "class": this.menuItemClass
	        });
	      }

	      return item;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Main');
	  BX.Main.dropdownManager = {
	    dropdownClass: 'main-dropdown',
	    data: {},
	    init: function init() {
	      var self = this;
	      var result;
	      var onLoadItems;
	      var items;
	      BX.bind(document, 'click', BX.delegate(function (event) {
	        if (BX.hasClass(event.target, this.dropdownClass)) {
	          event.preventDefault();
	          result = this.getById(event.target.id);

	          if (result && result.dropdown === event.target) {
	            self.push(event.target.id, this.getById(event.target.id));
	          } else {
	            self.push(event.target.id, new BX.Main.dropdown(event.target));
	          }
	        }
	      }, this));
	      onLoadItems = BX.Grid.Utils.getByClass(document.body, this.dropdownClass);

	      if (BX.type.isArray(onLoadItems)) {
	        onLoadItems.forEach(function (current) {
	          result = self.getById(current.id);

	          try {
	            items = eval(BX.data(current, 'items'));
	          } catch (err) {}

	          BX.onCustomEvent(window, 'Dropdown::load', [current.id, {}, null, BX.type.isArray(items) && items.length ? items[0] : [], BX.data(current, 'value')]);
	        });
	      }
	    },
	    push: function push(id, instance) {
	      this.data[id] = instance;
	    },
	    getById: function getById(id) {
	      return id in this.data ? this.data[id] : null;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * @param {HtmlElement} node
	   * @param {BX.Main.grid} [parent]
	   * @constructor
	   */

	  BX.Grid.Element = function (node, parent) {
	    this.node = null;
	    this.href = null;
	    this.parent = null;
	    this.init(node, parent);
	  };

	  BX.Grid.Element.prototype = {
	    init: function init(node, parent) {
	      this.node = node;
	      this.parent = parent;
	      this.resetOnclickAttr();
	    },
	    getParent: function getParent() {
	      return this.parent;
	    },
	    load: function load() {
	      BX.addClass(this.getNode(), this.getParent().settings.get('classLoad'));
	    },
	    unload: function unload() {
	      BX.removeClass(this.getNode(), this.getParent().settings.get('classLoad'));
	    },
	    isLoad: function isLoad() {
	      return BX.hasClass(this.getNode(), this.getParent().settings.get('classLoad'));
	    },
	    resetOnclickAttr: function resetOnclickAttr() {
	      if (BX.type.isDomNode(this.getNode())) {
	        this.getNode().onclick = null;
	      }
	    },
	    getObserver: function getObserver() {
	      return BX.Grid.observer;
	    },
	    getNode: function getNode() {
	      return this.node;
	    },
	    getLink: function getLink() {
	      var result;

	      try {
	        result = this.getNode().href;
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.Fader
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.Fader = function (parent) {
	    this.parent = null;
	    this.table = null;
	    this.container = null;
	    this.init(parent);
	  };

	  BX.Grid.Fader.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.table = this.parent.getTable();
	      this.container = this.table.parentNode;
	      this.scrollStartEventName = this.parent.isTouch() ? 'touchstart' : 'mouseenter';
	      this.scrollEndEventName = this.parent.isTouch() ? 'touchend' : 'mouseleave';

	      if (this.parent.getParam('ALLOW_PIN_HEADER')) {
	        this.fixedTable = this.parent.getPinHeader().getFixedTable();
	      }

	      this.debounceScrollHandler = BX.debounce(this._onWindowScroll, 400, this);
	      BX.bind(window, 'resize', BX.proxy(this.toggle, this));
	      document.addEventListener('scroll', this.debounceScrollHandler, BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      this.container.addEventListener('scroll', BX.proxy(this.toggle, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this.toggle, this));
	      BX.addCustomEvent(window, 'Grid::resize', BX.proxy(this.toggle, this));
	      BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this._onHeaderUpdated, this));
	      BX.addCustomEvent(window, 'Grid::columnResize', BX.proxy(this.toggle, this));
	      BX.bind(this.getEarLeft(), this.scrollStartEventName, BX.proxy(this._onMouseoverLeft, this));
	      BX.bind(this.getEarRight(), this.scrollStartEventName, BX.proxy(this._onMouseoverRight, this));
	      BX.bind(this.getEarLeft(), this.scrollEndEventName, BX.proxy(this.stopScroll, this));
	      BX.bind(this.getEarRight(), this.scrollEndEventName, BX.proxy(this.stopScroll, this));
	      this.toggle();
	      this.adjustEarOffset(true);
	    },
	    destroy: function destroy() {
	      BX.unbind(window, 'resize', BX.proxy(this.toggle, this));
	      document.removeEventListener('scroll', this.debounceScrollHandler, BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      this.container.removeEventListener('scroll', BX.proxy(this.toggle, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this.toggle, this));
	      BX.removeCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this._onHeaderUpdated, this));
	      BX.removeCustomEvent(window, 'Grid::columnResize', BX.proxy(this.toggle, this));
	      BX.unbind(this.getEarLeft(), this.scrollStartEventName, BX.proxy(this._onMouseoverLeft, this));
	      BX.unbind(this.getEarRight(), this.scrollStartEventName, BX.proxy(this._onMouseoverRight, this));
	      BX.unbind(this.getEarLeft(), this.scrollEndEventName, BX.proxy(this.stopScroll, this));
	      BX.unbind(this.getEarRight(), this.scrollEndEventName, BX.proxy(this.stopScroll, this));
	      this.hideLeftEar();
	      this.hideRightEar();
	      this.stopScroll();
	    },
	    _onHeaderUpdated: function _onHeaderUpdated() {
	      if (this.parent.getParam('ALLOW_PIN_HEADER')) {
	        this.fixedTable = this.parent.getPinHeader().getFixedTable();
	      }
	    },
	    _onMouseoverLeft: function _onMouseoverLeft(event) {
	      this.parent.isTouch() && event.preventDefault();
	      this.startScrollByDirection('left');
	    },
	    _onMouseoverRight: function _onMouseoverRight(event) {
	      this.parent.isTouch() && event.preventDefault();
	      this.startScrollByDirection('right');
	    },
	    stopScroll: function stopScroll() {
	      clearTimeout(this.scrollTimer);
	      clearInterval(this.scrollInterval);
	    },
	    startScrollByDirection: function startScrollByDirection(direction) {
	      var container = this.container;
	      var offset = container.scrollLeft;
	      var self = this;
	      var stepLength = 8;
	      var stepTime = 1000 / 60 / 2;
	      this.scrollTimer = setTimeout(function () {
	        self.scrollInterval = setInterval(function () {
	          container.scrollLeft = direction == 'right' ? offset += stepLength : offset -= stepLength;
	        }, stepTime);
	      }, 100);
	    },
	    getEarLeft: function getEarLeft() {
	      if (!this.earLeft) {
	        this.earLeft = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classEarLeft'), true);
	      }

	      return this.earLeft;
	    },
	    getEarRight: function getEarRight() {
	      if (!this.earRight) {
	        this.earRight = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classEarRight'), true);
	      }

	      return this.earRight;
	    },
	    getShadowLeft: function getShadowLeft() {
	      return this.parent.getContainer().querySelector(".main-grid-fade-shadow-left");
	    },
	    getShadowRight: function getShadowRight() {
	      return this.parent.getContainer().querySelector(".main-grid-fade-shadow-right");
	    },
	    adjustEarOffset: function adjustEarOffset(prepare) {
	      if (prepare) {
	        this.windowHeight = BX.height(window);
	        this.tbodyPos = BX.pos(this.table.tBodies[0]);
	        this.headerPos = BX.pos(this.table.tHead);
	      }

	      var scrollY = window.scrollY;

	      if (this.parent.isIE()) {
	        scrollY = document.documentElement.scrollTop;
	      }

	      var bottomPos = scrollY + this.windowHeight - this.tbodyPos.top;
	      var posTop = scrollY - this.tbodyPos.top;

	      if (bottomPos > this.tbodyPos.bottom - this.tbodyPos.top) {
	        bottomPos = this.tbodyPos.bottom - this.tbodyPos.top;
	      }

	      if (posTop < this.headerPos.height) {
	        posTop = this.headerPos.height;
	      } else {
	        bottomPos -= posTop;
	        bottomPos += this.headerPos.height;
	      }

	      BX.Grid.Utils.requestAnimationFrame(BX.proxy(function () {
	        if (posTop !== this.lastPosTop) {
	          var translate = 'translate3d(0px, ' + posTop + 'px, 0)';
	          this.getEarLeft().style.transform = translate;
	          this.getEarRight().style.transform = translate;
	        }

	        if (bottomPos !== this.lastBottomPos) {
	          this.getEarLeft().style.height = bottomPos + 'px';
	          this.getEarRight().style.height = bottomPos + 'px';
	        }

	        this.lastPosTop = posTop;
	        this.lastBottomPos = bottomPos;
	      }, this));
	    },
	    _onWindowScroll: function _onWindowScroll() {
	      this.adjustEarOffset();
	    },
	    hasScroll: function hasScroll() {
	      return this.table.offsetWidth > this.container.clientWidth;
	    },
	    hasScrollLeft: function hasScrollLeft() {
	      return this.container.scrollLeft > 0;
	    },
	    hasScrollRight: function hasScrollRight() {
	      return this.table.offsetWidth > this.container.scrollLeft + this.container.clientWidth;
	    },
	    showLeftEar: function showLeftEar() {
	      BX.addClass(this.container.parentNode, this.parent.settings.get('classFadeContainerLeft'));
	      BX.addClass(this.getEarLeft(), this.parent.settings.get('classShow'));
	    },
	    hideLeftEar: function hideLeftEar() {
	      BX.removeClass(this.container.parentNode, this.parent.settings.get('classFadeContainerLeft'));
	      BX.removeClass(this.getEarLeft(), this.parent.settings.get('classShow'));
	    },
	    showRightEar: function showRightEar() {
	      BX.addClass(this.container.parentNode, this.parent.settings.get('classFadeContainerRight'));
	      BX.addClass(this.getEarRight(), this.parent.settings.get('classShow'));
	    },
	    hideRightEar: function hideRightEar() {
	      BX.removeClass(this.container.parentNode, this.parent.settings.get('classFadeContainerRight'));
	      BX.removeClass(this.getEarRight(), this.parent.settings.get('classShow'));
	    },
	    adjustFixedTablePosition: function adjustFixedTablePosition() {
	      var left = this.container.scrollLeft;
	      BX.Grid.Utils.requestAnimationFrame(BX.delegate(function () {
	        this.fixedTable.style.marginLeft = -left + 'px';
	      }, this));
	    },
	    toggle: function toggle() {
	      this.adjustEarOffset(true);
	      this.fixedTable && this.adjustFixedTablePosition();

	      if (this.hasScroll()) {
	        this.hasScrollLeft() ? this.showLeftEar() : this.hideLeftEar();
	        this.hasScrollRight() ? this.showRightEar() : this.hideRightEar();
	      } else {
	        this.hideLeftEar();
	        this.hideRightEar();
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * Updates grid
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.Updater = function (parent) {
	    this.parent = parent;
	  };
	  /**
	   * Gets parent object
	   * @return {?BX.Main.grid}
	   */


	  BX.Grid.Updater.prototype.getParent = function () {
	    return this.parent;
	  };
	  /**
	   * Updates head rows
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.updateHeadRows = function (rows) {
	    var headers;

	    if (BX.type.isArray(rows) && rows.length) {
	      headers = this.getParent().getHeaders();
	      headers.forEach(function (header) {
	        header = BX.cleanNode(header);
	        rows.forEach(function (row) {
	          if (BX.type.isDomNode(row)) {
	            header.appendChild(BX.clone(row));
	          }
	        });
	      });
	    }
	  };
	  /**
	   * Appends head rows
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.appendHeadRows = function (rows) {
	    var headers;

	    if (BX.type.isArray(rows) && rows.length) {
	      headers = this.getParent().getHeaders();
	      headers.forEach(function (header) {
	        rows.forEach(function (row) {
	          if (BX.type.isDomNode(row)) {
	            header.appendChild(BX.clone(row));
	          }
	        });
	      });
	    }
	  };
	  /**
	   * Prepends head rows
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.prependHeadRows = function (rows) {
	    var headers;

	    if (BX.type.isArray(rows) && rows.length) {
	      headers = this.getParent().getHeaders();
	      headers.forEach(function (header) {
	        header = BX.cleanNode(header);
	        rows.forEach(function (row) {
	          if (BX.type.isDomNode(row)) {
	            header.prepend(BX.clone(row));
	          }
	        });
	      });
	    }
	  };
	  /**
	   * Updates body row by row id
	   * @param {?string|number} id
	   * @param {HTMLTableRowElement} row
	   */


	  BX.Grid.Updater.prototype.updateBodyRowById = function (id, row) {
	    if ((BX.type.isNumber(id) || BX.type.isNotEmptyString(id)) && BX.type.isDomNode(row)) {
	      var currentRow = this.getParent().getRows().getById(id);

	      if (currentRow) {
	        var currentNode = currentRow.getNode();
	        BX.insertAfter(row, currentNode);
	        BX.remove(currentNode);
	      }
	    }
	  };
	  /**
	   * Updates all body rows.
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.updateBodyRows = function (rows) {
	    if (BX.type.isArray(rows)) {
	      var body = this.getParent().getBody();
	      body.innerHTML = '';
	      rows.forEach(function (current) {
	        !!current && body.appendChild(current);
	      });
	    }
	  };
	  /**
	   * Appends body rows.
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.appendBodyRows = function (rows) {
	    var body;

	    if (BX.type.isArray(rows)) {
	      body = this.getParent().getBody();
	      rows.forEach(function (current) {
	        if (BX.type.isDomNode(current)) {
	          body.appendChild(current);
	        }
	      });
	    }
	  };
	  /**
	   * Prepends body rows
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.prependBodyRows = function (rows) {
	    var body;

	    if (BX.type.isArray(rows)) {
	      body = this.getParent().getBody();
	      rows.forEach(function (current) {
	        if (BX.type.isDomNode(current)) {
	          BX.prepend(body, current);
	        }
	      });
	    }
	  };
	  /**
	   * Updates table footer rows.
	   * @param {?HTMLTableRowElement[]} rows
	   */


	  BX.Grid.Updater.prototype.updateFootRows = function (rows) {
	    var foot;

	    if (BX.type.isArray(rows)) {
	      foot = BX.cleanNode(this.getParent().getFoot());
	      rows.forEach(function (current) {
	        if (BX.type.isDomNode(current)) {
	          foot.appendChild(current);
	        }
	      });
	    }
	  };
	  /**
	   * Updates total rows counter
	   * @param {?HTMLElement} counter
	   */


	  BX.Grid.Updater.prototype.updateCounterTotal = function (counter) {
	    var counterCell;

	    if (BX.type.isDomNode(counter)) {
	      counterCell = BX.cleanNode(this.getParent().getCounterTotal());
	      counterCell.appendChild(counter);
	    }
	  };
	  /**
	   * Updates grid pagination
	   * @param {?HTMLElement} pagination
	   */


	  BX.Grid.Updater.prototype.updatePagination = function (pagination) {
	    var paginationCell = this.getParent().getPagination().getContainer();

	    if (!!paginationCell) {
	      paginationCell.innerHTML = '';

	      if (BX.type.isDomNode(pagination)) {
	        paginationCell.appendChild(pagination);
	      }
	    }
	  };
	  /**
	   * Updates more button
	   * @param {?HTMLElement} button
	   */


	  BX.Grid.Updater.prototype.updateMoreButton = function (button) {
	    if (BX.type.isDomNode(button)) {
	      var buttonParent = BX.Grid.Utils.closestParent(this.getParent().getMoreButton().getNode());
	      buttonParent.innerHTML = '';
	      buttonParent.appendChild(button);
	    }
	  };
	  /**
	   * Updates group actions panel
	   * @param {HTMLElement} panel
	   */


	  BX.Grid.Updater.prototype.updateGroupActions = function (panel) {
	    var GroupActions = this.parent.getActionsPanel();

	    if (!!GroupActions && BX.type.isDomNode(panel)) {
	      var panelNode = GroupActions.getPanel();

	      if (BX.type.isDomNode(panelNode)) {
	        panelNode.innerHTML = '';
	        var panelChild = BX.firstChild(panel);

	        if (BX.type.isDomNode(panelChild)) {
	          panelNode.appendChild(panelChild);
	        }
	      }
	    }
	  };
	})();

	(function () {

	  BX.Reflection.namespace('BX.Grid');

	  BX.Grid.ImageField = function (parent, options) {
	    this.parent = parent;
	    this.options = options;
	    this.cache = new BX.Cache.MemoryCache();
	  };

	  BX.Grid.ImageField.prototype = {
	    getPreview: function getPreview() {
	      return this.cache.remember('preview', function () {
	        return BX.create('img', {
	          props: {
	            className: 'main-grid-image-editor-preview'
	          },
	          attrs: {
	            src: this.options.VALUE
	          }
	        });
	      }.bind(this));
	    },
	    getFileInput: function getFileInput() {
	      return this.cache.remember('fileInput', function () {
	        return BX.create("input", {
	          props: {
	            className: "main-grid-image-editor-file-input"
	          },
	          attrs: {
	            type: "file",
	            accept: "image/*",
	            name: this.options.NAME
	          },
	          events: {
	            change: function (event) {
	              var reader = new FileReader();

	              reader.onload = function (event) {
	                this.getPreview().src = event.currentTarget.result;
	              }.bind(this);

	              reader.readAsDataURL(event.target.files[0]);
	              BX.Dom.remove(this.getFakeField());
	              BX.Dom.append(this.getFileInput(), this.getLayout());
	              BX.Dom.removeClass(this.getRemoveButton(), 'ui-btn-disabled');
	              BX.Dom.style(this.getPreview(), null);
	            }.bind(this)
	          }
	        });
	      }.bind(this));
	    },
	    getUploadButton: function getUploadButton() {
	      return this.cache.remember('uploadButton', function () {
	        return BX.create('button', {
	          props: {
	            className: "ui-btn ui-btn-xs"
	          },
	          text: this.parent.getParam("MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_EDIT"),
	          events: {
	            click: function (event) {
	              event.preventDefault();
	              this.getFileInput().click();
	            }.bind(this)
	          }
	        });
	      }.bind(this));
	    },
	    getRemoveButton: function getRemoveButton() {
	      return this.cache.remember('removeButton', function () {
	        return BX.create('button', {
	          props: {
	            className: "ui-btn ui-btn-xs ui-btn-danger"
	          },
	          events: {
	            click: function (event) {
	              event.preventDefault();
	              BX.Dom.append(this.getFakeField(), this.getLayout());
	              BX.Dom.remove(this.getFileInput());
	              BX.Dom.addClass(this.getRemoveButton(), 'ui-btn-disabled');
	              BX.Dom.style(this.getPreview(), {
	                opacity: .4
	              });
	            }.bind(this)
	          },
	          text: this.parent.getParam('MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_REMOVE')
	        });
	      }.bind(this));
	    },
	    getFakeField: function getFakeField() {
	      return this.cache.remember('deleted', function () {
	        return BX.create("input", {
	          props: {
	            className: "main-grid-image-editor-fake-file-input"
	          },
	          attrs: {
	            type: "hidden",
	            name: this.options.NAME,
	            value: 'null'
	          }
	        });
	      }.bind(this));
	    },
	    getLayout: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return BX.create("div", {
	          props: {
	            className: "main-grid-image-editor main-grid-editor"
	          },
	          attrs: {
	            name: this.options.NAME
	          },
	          children: [BX.create("div", {
	            props: {
	              className: "main-grid-image-editor-left"
	            },
	            children: [this.getPreview()]
	          }), BX.create("div", {
	            props: {
	              className: "main-grid-image-editor-right"
	            },
	            children: [this.getUploadButton(), this.getRemoveButton()]
	          }), this.getFileInput()]
	        });
	      }.bind(this));
	    }
	  };
	})();

	var _templateObject, _templateObject2;

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.InlineEditor
	   * @param {BX.Main.grid} parent
	   * @param {Object} types
	   * @constructor
	   */

	  BX.Grid.InlineEditor = function (parent, types) {
	    this.parent = null;
	    this.types = null;
	    this.isDropdownChangeEventSubscribed = false;
	    this.init(parent, types);
	  };

	  BX.Grid.InlineEditor.prototype = {
	    init: function init(parent, types) {
	      this.parent = parent;

	      try {
	        this.types = eval(types);
	      } catch (err) {
	        this.types = null;
	      }
	    },
	    createContainer: function createContainer() {
	      return BX.create('div', {
	        props: {
	          className: this.parent.settings.get('classEditorContainer')
	        }
	      });
	    },
	    createTextarea: function createTextarea(editObject, height) {
	      var textarea = BX.create('textarea', {
	        props: {
	          className: [this.parent.settings.get('classEditor'), this.parent.settings.get('classEditorTextarea')].join(' ')
	        },
	        attrs: {
	          name: editObject.NAME,
	          style: 'height:' + height + 'px'
	        },
	        html: editObject.VALUE
	      });
	      return textarea;
	    },
	    createInput: function createInput(editObject) {
	      var className = this.parent.settings.get('classEditorText');
	      var attrs = {
	        value: editObject.VALUE !== undefined && editObject.VALUE !== null ? BX.util.htmlspecialcharsback(editObject.VALUE) : '',
	        name: editObject.NAME !== undefined && editObject.NAME !== null ? editObject.NAME : ''
	      };

	      if (editObject.TYPE === this.types.CHECKBOX) {
	        className = this.parent.settings.get('classEditorCheckbox');
	        attrs.type = 'checkbox';
	        attrs.checked = attrs.value == 'Y';
	      }

	      if (editObject.TYPE === this.types.DATE) {
	        className = [className, this.parent.settings.get('classEditorDate')].join(' ');
	      }

	      if (editObject.TYPE === this.types.NUMBER) {
	        className = [className, this.parent.settings.get('classEditorNumber')].join(' ');
	        attrs.type = 'number';
	      }

	      if (editObject.TYPE === this.types.RANGE) {
	        className = [className, this.parent.settings.get('classEditorRange')].join(' ');
	        attrs.type = 'range';

	        if (BX.type.isPlainObject(editObject.DATA)) {
	          attrs.min = editObject.DATA.MIN || '0';
	          attrs.max = editObject.DATA.MAX || 99999;
	          attrs.step = editObject.DATA.STEP || '';
	        }
	      }

	      if (BX.type.isNotEmptyString(editObject.PLACEHOLDER)) {
	        attrs.placeholder = BX.util.htmlspecialchars(editObject.PLACEHOLDER);
	      }

	      if (editObject.DISABLED) {
	        attrs.disabled = true;
	      }

	      className = [this.parent.settings.get('classEditor'), className].join(' ');
	      return BX.create('input', {
	        props: {
	          className: className,
	          id: editObject.NAME + '_control'
	        },
	        attrs: attrs
	      });
	    },
	    createCustom: function createCustom(editObject) {
	      var className = this.parent.settings.get('classEditorCustom');
	      className = [this.parent.settings.get('classEditor'), className].join(' ');
	      return BX.create('div', {
	        props: {
	          className: className
	        },
	        attrs: {
	          'data-name': editObject.NAME
	        },
	        html: editObject.VALUE || ""
	      });
	    },
	    createMoney: function createMoney(editObject) {
	      var value = editObject.VALUE;
	      var fieldChildren = [];
	      var priceObject = value.PRICE || {};
	      priceObject.PLACEHOLDER = editObject.PLACEHOLDER || '';
	      fieldChildren.push(this.createMoneyPrice(priceObject));

	      if (BX.type.isArray(editObject.CURRENCY_LIST) && editObject.CURRENCY_LIST.length > 0) {
	        var currencyObject = value.CURRENCY || {};
	        currencyObject.DATA = {
	          ITEMS: editObject.CURRENCY_LIST
	        };
	        currencyObject.HTML_ENTITY = editObject.HTML_ENTITY || false;
	        fieldChildren.push(this.createMoneyCurrency(currencyObject));
	      }

	      if (BX.type.isNotEmptyObject(value.HIDDEN)) {
	        for (var fieldName in value.HIDDEN) {
	          if (value.HIDDEN.hasOwnProperty(fieldName) && BX.type.isNotEmptyString(fieldName)) {
	            var hidden = this.createInput({
	              NAME: fieldName,
	              VALUE: value['HIDDEN'][fieldName],
	              TYPE: this.types.TEXT
	            });
	            hidden.type = 'hidden';
	            fieldChildren.push(hidden);
	          }
	        }
	      }

	      var className = this.parent.settings.get('classEditorMoney');
	      className = [this.parent.settings.get('classEditor'), className].join(' ');
	      var attrs = value.ATTRIBUTES || {};
	      attrs['data-name'] = editObject.NAME;
	      return BX.create('div', {
	        props: {
	          className: className
	        },
	        attrs: attrs,
	        children: fieldChildren
	      });
	    },
	    createMoneyPrice: function createMoneyPrice(priceObject) {
	      priceObject.TYPE = this.types.NUMBER;
	      var priceInput = this.createInput(priceObject);
	      priceInput.classList.add('main-grid-editor-money-price');
	      main_core.Event.bind(priceInput, 'change', function (event) {
	        var fieldNode = event.target.parentNode;
	        var currencyDropdown = fieldNode.querySelector('.main-grid-editor-money-currency');
	        var eventData = {
	          field: fieldNode,
	          values: {
	            price: event.target.value || '',
	            currency: currencyDropdown.dataset.value || ''
	          }
	        };
	        main_core_events.EventEmitter.emit('Grid.MoneyField::change', eventData);
	      });
	      return priceInput;
	    },
	    createMoneyCurrency: function createMoneyCurrency(currencyObject) {
	      var currencyBlock = this.createDropdown(currencyObject);
	      currencyBlock.dataset.menuOffsetLeft = 15;
	      currencyBlock.dataset.menuMaxHeight = 200;
	      currencyBlock.classList.add('main-grid-editor-money-currency');

	      if (currencyObject.DISABLED === true) {
	        currencyBlock.classList.remove('main-dropdown');
	        currencyBlock.dataset.disabled = true;
	      }

	      if (!this.isDropdownChangeEventSubscribed) {
	        this.isDropdownChangeEventSubscribed = true;
	        main_core_events.EventEmitter.subscribe('Dropdown::change', function (event) {
	          var _event$getData = event.getData(),
	              _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	              controlId = _event$getData2[0];

	          if (!BX.type.isNotEmptyString(controlId)) {
	            return;
	          }

	          var dropdownObject = BX.Main.dropdownManager.getById(controlId);

	          if (dropdownObject.dropdown && dropdownObject.dropdown.classList.contains('main-grid-editor-money-currency')) {
	            var fieldNode = dropdownObject.dropdown.parentNode;
	            var priceField = fieldNode.querySelector('.main-grid-editor-money-price');
	            var eventData = {
	              field: fieldNode,
	              values: {
	                price: priceField.value || '',
	                currency: dropdownObject.dropdown.dataset.value || ''
	              }
	            };
	            main_core_events.EventEmitter.emit('Grid.MoneyField::change', eventData);
	          }
	        });
	      }

	      return currencyBlock;
	    },
	    createOutput: function createOutput(editObject) {
	      return BX.create('output', {
	        props: {
	          className: this.parent.settings.get('classEditorOutput') || ''
	        },
	        attrs: {
	          "for": editObject.NAME + '_control'
	        },
	        text: editObject.VALUE || ''
	      });
	    },
	    getDropdownValueItemByValue: function getDropdownValueItemByValue(items, value) {
	      var result = items.filter(function (current) {
	        return current.VALUE === value;
	      });
	      return result.length > 0 ? result[0] : items[0];
	    },
	    createDropdown: function createDropdown(editObject) {
	      var valueItem = this.getDropdownValueItemByValue(editObject.DATA.ITEMS, editObject.VALUE);
	      var isHtmlEntity = 'HTML_ENTITY' in editObject && editObject.HTML_ENTITY === true;
	      return BX.create('div', {
	        props: {
	          className: [this.parent.settings.get('classEditor'), 'main-dropdown main-grid-editor-dropdown'].join(' '),
	          id: editObject.NAME + '_control'
	        },
	        attrs: {
	          name: editObject.NAME,
	          tabindex: '0',
	          'data-items': JSON.stringify(editObject.DATA.ITEMS),
	          'data-value': valueItem.VALUE,
	          'data-html-entity': editObject.HTML_ENTITY
	        },
	        children: [BX.create('span', {
	          props: {
	            className: 'main-dropdown-inner'
	          },
	          html: isHtmlEntity ? valueItem.NAME : null,
	          text: isHtmlEntity ? null : valueItem.NAME
	        })]
	      });
	    },
	    createMultiselect: function createMultiselect(editObject) {
	      var _this = this;

	      var selectedValues = [];

	      var squares = function () {
	        if (BX.Type.isArrayFilled(editObject.VALUE)) {
	          return editObject.VALUE.map(function (value) {
	            var _item$HTML;

	            var item = _this.getDropdownValueItemByValue(editObject.DATA.ITEMS, value);

	            selectedValues.push(item);
	            var itemName = (_item$HTML = item.HTML) !== null && _item$HTML !== void 0 ? _item$HTML : BX.util.htmlspecialchars(item.NAME);
	            var renderedItem = BX.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"main-ui-square\">\n\t\t\t\t\t\t\t\t<span class=\"main-ui-square-item\">", "</span>\n\t\t\t\t\t\t\t\t<span class=\"main-ui-item-icon main-ui-square-delete\"></span>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t"])), itemName);
	            BX.Dom.attr(renderedItem, 'data-item', item);
	            return renderedItem;
	          });
	        }

	        return [];
	      }();

	      var layout = BX.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"main-grid-editor main-ui-control main-ui-multi-select\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tid=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t<span class=\"main-ui-square-container\">", "</span>\n\t\t\t\t\t<span class=\"main-ui-hide main-ui-control-value-delete\">\n\t\t\t\t\t\t<span class=\"main-ui-control-value-delete-item\"></span>\n\t\t\t\t\t</span>\n\t\t\t\t\t<span class=\"main-ui-square-search\">\n\t\t\t\t\t\t<input type=\"text\" class=\"main-ui-square-search-item\">\n\t\t\t\t\t</span>\t\n\t\t\t\t</div>\n\t\t\t"])), BX.Text.encode(editObject.NAME), "".concat(BX.Text.encode(editObject.NAME), "_control"), squares);
	      BX.Dom.attr(layout, {
	        'data-params': {
	          isMulti: true
	        },
	        'data-items': editObject.DATA.ITEMS,
	        'data-value': selectedValues
	      });
	      return layout;
	    },
	    validateEditObject: function validateEditObject(editObject) {
	      return BX.type.isPlainObject(editObject) && 'TYPE' in editObject && 'NAME' in editObject && 'VALUE' in editObject && (!('items' in editObject) || BX.type.isArray(editObject.items) && editObject.items.length);
	    },
	    initCalendar: function initCalendar(event) {
	      BX.calendar({
	        node: event.target,
	        field: event.target
	      });
	    },
	    bindOnRangeChange: function bindOnRangeChange(control, output) {
	      function bubble(control, output) {
	        BX.html(output, control.value);
	        var value = parseFloat(control.value);
	        var max = parseFloat(control.getAttribute('max'));
	        var min = parseFloat(control.getAttribute('min'));
	        var thumbWidth = 16;
	        var range = max - min;
	        var position = (value - min) / range * 100;
	        var positionOffset = Math.round(thumbWidth * position / 100) - thumbWidth / 2;
	        output.style.left = position + '%';
	        output.style.marginLeft = -positionOffset + 'px';
	      }

	      setTimeout(function () {
	        bubble(control, output);
	      }, 0);
	      BX.bind(control, 'input', function () {
	        bubble(control, output);
	      });
	    },
	    createImageEditor: function createImageEditor(editObject) {
	      return new BX.Grid.ImageField(this.parent, editObject).getLayout();
	    },
	    getEditor: function getEditor(editObject, height) {
	      var control, span;
	      var container = this.createContainer();

	      if (this.validateEditObject(editObject)) {
	        editObject.VALUE = editObject.VALUE === null ? '' : editObject.VALUE;

	        switch (editObject.TYPE) {
	          case this.types.TEXT:
	            {
	              control = this.createInput(editObject);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.DATE:
	            {
	              control = this.createInput(editObject);
	              BX.bind(control, 'click', this.initCalendar);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.NUMBER:
	            {
	              control = this.createInput(editObject);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.RANGE:
	            {
	              control = this.createInput(editObject);
	              span = this.createOutput(editObject);
	              this.bindOnRangeChange(control, span);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.CHECKBOX:
	            {
	              control = this.createInput(editObject);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.TEXTAREA:
	            {
	              control = this.createTextarea(editObject, height);
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.DROPDOWN:
	            {
	              control = this.createDropdown(editObject);
	              break;
	            }

	          case this.types.MULTISELECT:
	            {
	              control = this.createMultiselect(editObject);
	              break;
	            }

	          case this.types.IMAGE:
	            {
	              control = this.createImageEditor(editObject);
	              break;
	            }

	          case this.types.MONEY:
	            {
	              control = this.createMoney(editObject);
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          case this.types.CUSTOM:
	            {
	              control = this.createCustom(editObject);
	              requestAnimationFrame(function () {
	                var html = editObject.HTML || editObject.VALUE || null;

	                if (html) {
	                  var res = BX.processHTML(html);
	                  res.SCRIPT.forEach(function (item) {
	                    if (item.isInternal && item.JS) {
	                      BX.evalGlobal(item.JS);
	                    }
	                  });
	                }
	              });
	              BX.bind(control, 'click', function (event) {
	                event.stopPropagation();
	              });
	              BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
	              break;
	            }

	          default:
	            {
	              break;
	            }
	        }
	      }

	      if (BX.type.isDomNode(span)) {
	        container.appendChild(span);
	      }

	      if (BX.type.isDomNode(control)) {
	        container.appendChild(control);
	      }

	      return container;
	    },
	    _onControlKeydown: function _onControlKeydown(event) {
	      if (event.code === 'Enter') {
	        event.preventDefault();
	        var saveButton = BX.Grid.Utils.getBySelector(this.parent.getContainer(), '#grid_save_button > button', true);

	        if (saveButton) {
	          BX.fireEvent(saveButton, 'click');
	        }
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');

	  BX.Grid.Loader = function (parent) {
	    this.parent = null;
	    this.container = null;
	    this.windowHeight = null;
	    this.tbodyPos = null;
	    this.headerPos = null;
	    this.lastPosTop = null;
	    this.lastBottomPos = null;
	    this.table = null;
	    this.loader = null;
	    this.adjustLoaderOffset = this.adjustLoaderOffset.bind(this);
	    this.init(parent);
	  };

	  BX.Grid.Loader.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.table = this.parent.getTable();
	      this.loader = new BX.Loader({
	        target: this.getContainer()
	      });
	    },
	    adjustLoaderOffset: function adjustLoaderOffset() {
	      this.windowHeight = BX.height(window);
	      this.tbodyPos = BX.pos(this.table.tBodies[0]);
	      this.headerPos = BX.pos(this.table.tHead);
	      var scrollY = window.scrollY;

	      if (this.parent.isIE()) {
	        scrollY = document.documentElement.scrollTop;
	      }

	      var bottomPos = scrollY + this.windowHeight - this.tbodyPos.top;
	      var posTop = scrollY - this.tbodyPos.top;

	      if (bottomPos > this.tbodyPos.bottom - this.tbodyPos.top) {
	        bottomPos = this.tbodyPos.bottom - this.tbodyPos.top;
	      }

	      if (posTop < this.headerPos.height) {
	        posTop = this.headerPos.height;
	      } else {
	        bottomPos -= posTop;
	        bottomPos += this.headerPos.height;
	      }

	      requestAnimationFrame(function () {
	        if (posTop !== this.lastPosTop) {
	          this.getContainer().style.transform = 'translate3d(0px, ' + posTop + 'px, 0)';
	        }

	        if (bottomPos !== this.lastBottomPos) {
	          this.getContainer().style.height = bottomPos + 'px';
	        }

	        this.lastPosTop = posTop;
	        this.lastBottomPos = bottomPos;
	      }.bind(this));
	    },
	    getContainer: function getContainer() {
	      if (!this.container) {
	        this.container = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classLoader'), true);
	      }

	      return this.container;
	    },
	    show: function show() {
	      if (!this.loader.isShown()) {
	        this.adjustLoaderOffset();
	        this.getContainer().style.display = "block";
	        this.getContainer().style.opacity = "1";
	        this.getContainer().style.visibility = "visible";
	        var rowsCount = this.parent.getRows().getCountDisplayed();

	        if (rowsCount > 0 && rowsCount <= 2) {
	          this.loader.setOptions({
	            size: 60
	          });
	          this.loader.show();
	        } else {
	          this.loader.setOptions({
	            size: 110
	          });
	          this.loader.show();
	        }
	      }
	    },
	    hide: function hide() {
	      if (this.loader.isShown()) {
	        this.adjustLoaderOffset();
	        this.loader.hide().then(function () {
	          this.getContainer().style.display = "none";
	        }.bind(this));
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Main');
	  /**
	   * Works with grid instances
	   * @type {{data: Array, push: BX.Main.gridManager.push, getById: BX.Main.gridManager.getById}}
	   */

	  if (BX.Main.gridManager) {
	    return;
	  }

	  BX.Main.gridManager = {
	    data: [],
	    push: function push(id, instance) {
	      if (BX.type.isNotEmptyString(id) && instance) {
	        var object = {
	          id: id,
	          instance: instance,
	          old: null
	        };

	        if (this.getById(id) === null) {
	          this.data.push(object);
	        } else {
	          this.data[0] = object;
	        }
	      }
	    },
	    getById: function getById(id) {
	      var result = this.data.filter(function (current) {
	        return current.id === id || current.id.replace('main_grid_', '') === id;
	      });
	      return result.length === 1 ? result[0] : null;
	    },
	    getInstanceById: function getInstanceById(id) {
	      var result = this.getById(id);
	      return BX.type.isPlainObject(result) ? result["instance"] : null;
	    },
	    reload: function reload(id, url) {
	      var instance = this.getInstanceById(id);

	      if (instance) {
	        instance.reload(url);
	      }
	    },
	    getDataIndex: function getDataIndex(id) {
	      var result = null;
	      this.data.forEach(function (item, index) {
	        if (item.id === id) {
	          result = index;
	        }
	      });
	      return result;
	    },
	    destroy: function destroy(id) {
	      if (BX.type.isNotEmptyString(id)) {
	        var grid = this.getInstanceById(id);

	        if (grid instanceof BX.Main.grid) {
	          grid.destroy();
	          var index = this.getDataIndex(id);

	          if (index !== null) {
	            delete this.data[index];
	          }
	        }
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * Works with message
	   * @param {BX.Main.grid} parent
	   * @param {object} types - Types of message
	   * @constructor
	   */

	  BX.Grid.Message = function (parent, types) {
	    this.parent = null;
	    this.types = null;
	    this.messages = null;
	    this.popup = null;
	    this.init(parent, types);
	  };

	  BX.Grid.Message.prototype = {
	    /**
	     * @private
	     * @param {BX.Main.grid} parent
	     * @param {object} types
	     */
	    init: function init(parent, types) {
	      this.parent = parent;
	      this.types = types;
	      this.show();
	      BX.addCustomEvent('BX.Main.grid:paramsUpdated', BX.proxy(this.onUpdated, this));
	    },

	    /**
	     * @private
	     */
	    onUpdated: function onUpdated() {
	      this.show();
	    },

	    /**
	     * Gets data for messages
	     * @return {object[]}
	     */
	    getData: function getData() {
	      return this.parent.arParams.MESSAGES;
	    },

	    /**
	     * Checks is need show message
	     * @return {boolean}
	     */
	    isNeedShow: function isNeedShow() {
	      return this.getData().length > 0;
	    },

	    /**
	     * Show message
	     */
	    show: function show() {
	      if (this.isNeedShow()) {
	        this.getPopup().setContent(this.getContent());
	        this.getPopup().show();
	      }
	    },

	    /**
	     * Gets content for message popup
	     * @return {?HTMLElement}
	     */
	    getContent: function getContent() {
	      var data = this.getData();
	      var content = null;

	      if (BX.type.isArray(data) && data.length) {
	        var messagesDecl = {
	          block: 'main-grid-messages',
	          content: []
	        };
	        data.forEach(function (message) {
	          var messageDecl = {
	            block: 'main-grid-message',
	            mix: 'main-grid-message-' + message.TYPE.toLowerCase(),
	            content: []
	          };

	          if (BX.type.isNotEmptyString(message.TITLE)) {
	            messageDecl.content.push({
	              block: 'main-grid-message-title',
	              content: BX.create("div", {
	                html: message.TITLE
	              }).innerText
	            });
	          }

	          if (BX.type.isNotEmptyString(message.TEXT)) {
	            messageDecl.content.push({
	              block: 'main-grid-message-text',
	              content: BX.create("div", {
	                html: message.TEXT
	              }).innerText
	            });
	          }

	          messagesDecl.content.push(messageDecl);
	        });
	        content = BX.decl(messagesDecl);
	      }

	      return content;
	    },

	    /**
	     * Gets popup of message
	     * @return {BX.PopupWindow}
	     */
	    getPopup: function getPopup() {
	      if (this.popup === null) {
	        this.popup = new BX.PopupWindow(this.getPopupId(), null, {
	          autoHide: true,
	          overlay: 0.3,
	          minWidth: 400,
	          maxWidth: 800,
	          contentNoPaddings: true,
	          closeByEsc: true,
	          buttons: [new BX.PopupWindowButton({
	            text: this.parent.getParam('CLOSE'),
	            className: 'webform-small-button-blue webform-small-button',
	            events: {
	              click: function click() {
	                this.popupWindow.close();
	              }
	            }
	          })]
	        });
	      }

	      return this.popup;
	    },

	    /**
	     * Gets popup id
	     * @return {string}
	     */
	    getPopupId: function getPopupId() {
	      return this.parent.getContainerId() + '-main-grid-message';
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  BX.Grid.observer = {
	    handlers: [],
	    add: function add(node, event, handler, context) {
	      BX.bind(node, event, context ? BX.proxy(handler, context) : handler);
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');

	  BX.Grid.Pagesize = function (parent) {
	    this.parent = null;
	    this.init(parent);
	  };

	  BX.Grid.Pagesize.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      BX.addCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
	    },
	    destroy: function destroy() {
	      BX.removeCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
	    },
	    onChange: function onChange(id, event, item, dataValue, value) {
	      var self = this;

	      if (id === this.parent.getContainerId() + '_' + this.parent.settings.get('pageSizeId')) {
	        if (value >= 0) {
	          this.parent.tableFade();
	          this.parent.getUserOptions().setPageSize(value, function () {
	            self.parent.reloadTable();
	            BX.onCustomEvent(self.parent.getContainer(), 'Grid::pageSizeChanged', [self.parent]);
	          });
	        }
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.Pagination
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.Pagination = function (parent) {
	    this.parent = null;
	    this.container = null;
	    this.links = null;
	    this.init(parent);
	  };

	  BX.Grid.Pagination.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	    },
	    getParent: function getParent() {
	      return this.parent;
	    },
	    getContainer: function getContainer() {
	      if (!this.container) {
	        this.container = BX.Grid.Utils.getByClass(this.getParent().getContainer(), this.getParent().settings.get('classPagination'), true);
	      }

	      return this.container;
	    },
	    getLinks: function getLinks() {
	      var self = this;
	      var result = BX.Grid.Utils.getByTag(this.getContainer(), 'a');
	      this.links = [];

	      if (result) {
	        this.links = result.map(function (current) {
	          return new BX.Grid.Element(current, self.getParent());
	        });
	      }

	      return this.links;
	    },
	    getLink: function getLink(node) {
	      var result = null;
	      var filter;

	      if (BX.type.isDomNode(node)) {
	        filter = this.getLinks().filter(function (current) {
	          return node === current.getNode();
	        });

	        if (filter.length) {
	          result = filter[0];
	        }
	      }

	      return result;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.PinHeader
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.PinHeader = function (parent) {
	    this.parent = null;
	    this.table = null;
	    this.header = null;
	    this.container = null;
	    this.parentNodeResizeObserver = null;
	    var adminPanel = this.getAdminPanel();

	    if (adminPanel) {
	      this.mo = new MutationObserver(this.onAdminPanelMutation.bind(this));
	      this.mo.observe(document.documentElement, {
	        attributes: true
	      });
	    }

	    this.init(parent);
	  };

	  BX.Grid.PinHeader.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.rect = BX.pos(this.parent.getHead());
	      this.gridRect = BX.pos(this.parent.getTable());
	      var workArea = BX.Grid.Utils.getBySelector(document, '#workarea-content', true);

	      if (!workArea) {
	        workArea = this.parent.getContainer().parentNode;
	        workArea = !!workArea ? workArea.parentNode : workArea;
	      }

	      if (!!workArea) {
	        this.parentNodeResizeObserver = new BX.ResizeObserver(BX.proxy(this.refreshRect, this));
	        this.parentNodeResizeObserver.observe(workArea);
	      }

	      this.create(true);
	      document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      document.addEventListener('resize', BX.proxy(this._onResize, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      BX.addCustomEvent('Grid::updated', BX.proxy(this._onGridUpdate, this));
	      BX.addCustomEvent('Grid::resize', BX.proxy(this._onGridUpdate, this));
	      BX.bind(window, 'resize', BX.proxy(this._onGridUpdate, this));
	    },
	    refreshRect: function refreshRect() {
	      this.gridRect = BX.pos(this.parent.getTable());
	      this.rect = BX.pos(this.parent.getHead());
	    },
	    _onGridUpdate: function _onGridUpdate() {
	      var isPinned = this.isPinned();
	      BX.remove(this.getContainer());
	      this.create();
	      isPinned && this.pin();
	      this.table = null;
	      this.refreshRect();

	      this._onScroll();

	      BX.onCustomEvent(window, 'Grid::headerUpdated', []);
	    },
	    create: function create(async) {
	      var cells = BX.Grid.Utils.getByTag(this.parent.getHead(), 'th');
	      var cloneThead = BX.clone(this.parent.getHead());
	      var cloneCells = BX.Grid.Utils.getByTag(cloneThead, 'th');

	      var resizeCloneCells = function resizeCloneCells() {
	        cells.forEach(function (cell, index) {
	          var width = BX.width(cell);

	          if (index > 0) {
	            width -= parseInt(BX.style(cell, 'border-left-width'));
	            width -= parseInt(BX.style(cell, 'border-right-width'));
	          }

	          cloneCells[index].firstElementChild && (cloneCells[index].firstElementChild.style.width = width + 'px');

	          if (cells.length - 1 > index) {
	            cloneCells[index].style.width = width + 'px';
	          }
	        });
	      };

	      async ? setTimeout(resizeCloneCells, 0) : resizeCloneCells();
	      this.container = BX.decl({
	        block: 'main-grid-fixed-bar',
	        mix: 'main-grid-fixed-top',
	        attrs: {
	          style: 'width: ' + BX.width(this.parent.getContainer()) + 'px'
	        },
	        content: {
	          block: 'main-grid-table',
	          tag: 'table',
	          content: cloneThead
	        }
	      });
	      this.container.hidden = true;
	      this.parent.getWrapper().appendChild(this.container);
	    },
	    getContainer: function getContainer() {
	      return this.container;
	    },
	    getFixedTable: function getFixedTable() {
	      return this.table || (this.table = BX.Grid.Utils.getByTag(this.getContainer(), 'table', true));
	    },
	    getAdminPanel: function getAdminPanel() {
	      if (!this.adminPanel) {
	        this.adminPanel = document.querySelector('.adm-header');
	      }

	      return this.adminPanel;
	    },
	    isAdminPanelPinned: function isAdminPanelPinned() {
	      return BX.hasClass(document.documentElement, 'adm-header-fixed');
	    },
	    getPinOffset: function getPinOffset() {
	      var adminPanel = this.getAdminPanel();

	      if (adminPanel && this.isAdminPanelPinned()) {
	        return BX.Text.toNumber(BX.style(adminPanel, 'height'));
	      }

	      return 0;
	    },
	    pin: function pin() {
	      var container = this.getContainer();

	      if (container) {
	        container.hidden = false;
	      }

	      BX.onCustomEvent(window, 'Grid::headerPinned', []);
	    },
	    unpin: function unpin() {
	      var container = this.getContainer();

	      if (container) {
	        container.hidden = true;
	      }

	      BX.onCustomEvent(window, 'Grid::headerUnpinned', []);
	    },
	    stopPin: function stopPin() {
	      BX.Grid.Utils.styleForEach([this.getContainer()], {
	        'position': 'absolute',
	        'top': this.gridRect.bottom - this.rect.height - this.gridRect.top + 'px',
	        'box-shadow': 'none'
	      });
	    },
	    startPin: function startPin() {
	      BX.Grid.Utils.styleForEach([this.getContainer()], {
	        'position': 'fixed',
	        'top': this.getPinOffset() + 'px',
	        'box-shadow': ''
	      });
	    },
	    isPinned: function isPinned() {
	      return !this.getContainer().hidden;
	    },
	    _onScroll: function _onScroll() {
	      var scrollY = 0;

	      if (this.scrollRect) {
	        scrollY = this.scrollRect.scrollTop;
	      } else {
	        if (document.scrollingElement) {
	          this.scrollRect = document.scrollingElement;
	        } else {
	          if (document.documentElement.scrollTop > 0) {
	            this.scrollRect = document.documentElement;
	          } else if (document.body.scrollTop > 0) {
	            this.scrollRect = document.body;
	          }
	        }
	      }

	      if (this.gridRect.bottom > scrollY + this.rect.height) {
	        this.startPin();
	        var offset = this.getPinOffset();

	        if (this.rect.top - offset <= scrollY) {
	          !this.isPinned() && this.pin();
	        } else {
	          this.isPinned() && this.unpin();
	        }
	      } else {
	        this.stopPin();
	      }
	    },
	    onAdminPanelMutation: function onAdminPanelMutation() {
	      this._onScroll();
	    },
	    _onResize: function _onResize() {
	      this.rect = BX.pos(this.parent.getHead());
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.PinPanel
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.PinPanel = function (parent) {
	    this.parent = null;
	    this.panel = null;
	    this.isSelected = null;
	    this.offset = null;
	    this.animationDuration = null;
	    this.pinned = false;
	    this.init(parent);
	  };

	  BX.Grid.PinPanel.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.offset = 10;
	      this.animationDuration = 200;
	      this.panel = this.getPanel();
	      this.bindOnRowsEvents();
	    },
	    destroy: function destroy() {
	      this.unbindOnRowsEvents();
	    },
	    bindOnRowsEvents: function bindOnRowsEvents() {
	      BX.addCustomEvent('Grid::thereSelectedRows', BX.proxy(this._onThereSelectedRows, this));
	      BX.addCustomEvent('Grid::allRowsSelected', BX.proxy(this._onThereSelectedRows, this));
	      BX.addCustomEvent('Grid::noSelectedRows', BX.proxy(this._onNoSelectedRows, this));
	      BX.addCustomEvent('Grid::allRowsUnselected', BX.proxy(this._onNoSelectedRows, this));
	      BX.addCustomEvent('Grid::updated', BX.proxy(this._onNoSelectedRows, this));
	    },
	    unbindOnRowsEvents: function unbindOnRowsEvents() {
	      BX.removeCustomEvent('Grid::thereSelectedRows', BX.proxy(this._onThereSelectedRows, this));
	      BX.removeCustomEvent('Grid::allRowsSelected', BX.proxy(this._onThereSelectedRows, this));
	      BX.removeCustomEvent('Grid::noSelectedRows', BX.proxy(this._onNoSelectedRows, this));
	      BX.removeCustomEvent('Grid::allRowsUnselected', BX.proxy(this._onNoSelectedRows, this));
	      BX.removeCustomEvent('Grid::updated', BX.proxy(this._onNoSelectedRows, this));
	    },
	    bindOnWindowEvents: function bindOnWindowEvents() {
	      BX.bind(window, 'resize', BX.proxy(this._onResize, this));
	      document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	    },
	    unbindOnWindowEvents: function unbindOnWindowEvents() {
	      BX.unbind(window, 'resize', BX.proxy(this._onResize, this));
	      document.removeEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	    },
	    getPanel: function getPanel() {
	      this.panel = this.panel || this.parent.getActionsPanel().getPanel();
	      return this.panel;
	    },
	    getScrollBottom: function getScrollBottom() {
	      return BX.scrollTop(window) + this.getWindowHeight();
	    },
	    getPanelRect: function getPanelRect() {
	      if (!BX.type.isPlainObject(this.panelRect)) {
	        this.panelRect = BX.pos(this.getPanel());
	      }

	      return this.panelRect;
	    },
	    getPanelPrevBottom: function getPanelPrevBottom() {
	      var prev = BX.previousSibling(this.getPanel());
	      return BX.pos(prev).bottom + parseFloat(BX.style(prev, 'margin-bottom'));
	    },
	    getWindowHeight: function getWindowHeight() {
	      this.windowHeight = this.windowHeight || BX.height(window);
	      return this.windowHeight;
	    },
	    pinPanel: function pinPanel(withAnimation) {
	      var panel = this.getPanel();
	      var width = BX.width(this.getPanel().parentNode);
	      var height = BX.height(this.getPanel().parentNode);
	      var bodyRect = BX.pos(this.parent.getBody());
	      var offset = this.getStartDiffPanelPosition();
	      panel.parentNode.style.setProperty('height', height + 'px');
	      panel.style.setProperty('transform', 'translateY(' + offset + 'px)');
	      panel.classList.add('main-grid-fixed-bottom');
	      panel.style.setProperty('width', width + 'px');
	      panel.style.removeProperty('position');
	      panel.style.removeProperty('top');
	      requestAnimationFrame(function () {
	        if (withAnimation !== false) {
	          panel.style.setProperty('transition', 'transform 200ms ease');
	        }

	        panel.style.setProperty('transform', 'translateY(0)');
	      });

	      if (this.isNeedPinAbsolute() && !this.absolutePin) {
	        this.absolutePin = true;
	        panel.style.removeProperty('transition');
	        panel.style.setProperty('position', 'absolute');
	        panel.style.setProperty('top', bodyRect.top + 'px');
	      }

	      if (!this.isNeedPinAbsolute() && this.absolutePin) {
	        this.absolutePin = false;
	      }

	      this.adjustPanelPosition();
	      this.pinned = true;
	    },
	    unpinPanel: function unpinPanel(withAnimation) {
	      var panel = this.getPanel();
	      var panelRect = BX.pos(panel);
	      var parentRect = BX.pos(panel.parentNode);
	      var offset = Math.abs(panelRect.bottom - parentRect.bottom);

	      if (withAnimation !== false) {
	        panel.style.setProperty('transition', 'transform 200ms ease');
	      }

	      var translateOffset = offset < panelRect.height ? offset + 'px' : '100%';
	      panel.style.setProperty('transform', 'translateY(' + translateOffset + ')');

	      var delay = function delay(cb, _delay) {
	        if (withAnimation !== false) {
	          return setTimeout(cb, _delay);
	        }

	        cb();
	      };

	      delay(function () {
	        panel.parentNode.style.removeProperty('height');
	        panel.classList.remove('main-grid-fixed-bottom');
	        panel.style.removeProperty('transition');
	        panel.style.removeProperty('transform');
	        panel.style.removeProperty('width');
	        panel.style.removeProperty('position');
	        panel.style.removeProperty('top');
	      }, withAnimation !== false ? 200 : 0);
	      this.pinned = false;
	    },
	    isSelectedRows: function isSelectedRows() {
	      return this.isSelected;
	    },
	    isNeedPinAbsolute: function isNeedPinAbsolute() {
	      return BX.pos(this.parent.getBody()).top + this.getPanelRect().height >= this.getScrollBottom();
	    },
	    isNeedPin: function isNeedPin() {
	      return this.getScrollBottom() - this.getPanelRect().height <= this.getPanelPrevBottom();
	    },
	    adjustPanelPosition: function adjustPanelPosition() {
	      var scrollX = window.pageXOffset;
	      this.lastScrollX = this.lastScrollX !== null ? this.lastScrollX : scrollX;
	      BX.Grid.Utils.requestAnimationFrame(BX.proxy(function () {
	        if (scrollX !== this.lastScrollX) {
	          var panelPos = this.getPanelRect();
	          BX.style(this.getPanel(), 'left', panelPos.left - scrollX + 'px');
	        }
	      }, this));
	      this.lastScrollX = scrollX;
	    },
	    pinController: function pinController(withAnimation) {
	      if (this.getPanel()) {
	        if (!this.isPinned() && this.isNeedPin() && this.isSelectedRows()) {
	          return this.pinPanel(withAnimation);
	        }

	        if (this.isPinned() && !this.isNeedPin() || !this.isSelectedRows()) {
	          this.unpinPanel(withAnimation);
	        }
	      }
	    },
	    getEndDiffPanelPosition: function getEndDiffPanelPosition() {
	      var panelPos = BX.pos(this.getPanel());
	      var prevPanelPos = BX.pos(BX.previousSibling(this.getPanel()));
	      var scrollTop = BX.scrollTop(window);
	      var scrollBottom = scrollTop + BX.height(window);
	      var diff = panelPos.height + this.offset;
	      var prevPanelBottom = prevPanelPos.bottom + parseFloat(BX.style(this.getPanel(), 'margin-top'));

	      if (prevPanelBottom < scrollBottom && prevPanelBottom + panelPos.height > scrollBottom) {
	        diff = Math.abs(scrollBottom - (prevPanelBottom + panelPos.height));
	      }

	      return diff;
	    },
	    getStartDiffPanelPosition: function getStartDiffPanelPosition() {
	      var panelPos = BX.pos(this.getPanel());
	      var scrollTop = BX.scrollTop(window);
	      var scrollBottom = scrollTop + BX.height(window);
	      var diff = panelPos.height;

	      if (panelPos.bottom > scrollBottom && panelPos.top < scrollBottom) {
	        diff = panelPos.bottom - scrollBottom;
	      }

	      return diff;
	    },
	    isPinned: function isPinned() {
	      return this.pinned;
	    },
	    _onThereSelectedRows: function _onThereSelectedRows() {
	      this.bindOnWindowEvents();
	      this.isSelected = true;

	      if (this.lastIsSelected) {
	        this.pinController();
	      } else {
	        this.lastIsSelected = true;
	        this.pinController();
	      }
	    },
	    _onNoSelectedRows: function _onNoSelectedRows() {
	      this.unbindOnWindowEvents();
	      this.isSelected = false;
	      this.pinController();
	      this.lastIsSelected = false;
	    },
	    _onScroll: function _onScroll() {
	      this.pinController(false);
	    },
	    _onResize: function _onResize() {
	      this.windowHeight = BX.height(window);
	      this.panel = this.parent.getActionsPanel().getPanel();
	      this.panelRect = this.getPanel().getBoundingClientRect();
	      this.pinController(false);
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');

	  BX.Grid.Resize = function (parent) {
	    this.parent = null;
	    this.lastRegisterButtons = null;
	    this.init(parent);
	  };

	  BX.Grid.Resize.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
	      BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));
	      this.registerTableButtons();
	      this.registerPinnedTableButtons();
	    },
	    destroy: function destroy() {
	      BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
	      BX.removeCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));
	      BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.forEach(jsDD.unregisterObject);
	      (this.getButtons() || []).forEach(jsDD.unregisterObject);
	    },
	    registerTableButtons: function registerTableButtons() {
	      (this.getButtons() || []).forEach(this.register, this);
	      this.registerPinnedTableButtons();
	    },
	    register: function register(item) {
	      if (BX.type.isDomNode(item)) {
	        item.onbxdragstart = BX.delegate(this._onDragStart, this);
	        item.onbxdragstop = BX.delegate(this._onDragEnd, this);
	        item.onbxdrag = BX.delegate(this._onDrag, this);
	        jsDD.registerObject(item);
	      }
	    },
	    registerPinnedTableButtons: function registerPinnedTableButtons() {
	      if (this.parent.getParam('ALLOW_PIN_HEADER')) {
	        var pinnedTableButtons = this.getPinnedTableButtons();

	        if (BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.length) {
	          this.lastRegisterButtons.forEach(jsDD.unregisterObject);
	        }

	        this.lastRegisterButtons = pinnedTableButtons;
	        (this.getPinnedTableButtons() || []).forEach(this.register, this);
	      }
	    },
	    getButtons: function getButtons() {
	      return BX.Grid.Utils.getByClass(this.parent.getRows().getHeadFirstChild().getNode(), this.parent.settings.get('classResizeButton'));
	    },
	    getPinnedTableButtons: function getPinnedTableButtons() {
	      return BX.Grid.Utils.getByClass(this.parent.getPinHeader().getFixedTable(), this.parent.settings.get('classResizeButton'));
	    },
	    _onDragStart: function _onDragStart() {
	      var cell = BX.findParent(jsDD.current_node, {
	        className: this.parent.settings.get('classHeadCell')
	      });
	      var cells = this.parent.getRows().getHeadFirstChild().getCells();
	      var cellsKeys = Object.keys(cells);
	      var cellContainer;
	      this.__overlay = BX.create('div', {
	        props: {
	          className: 'main-grid-cell-overlay'
	        }
	      });
	      BX.append(this.__overlay, cell);
	      this.__resizeCell = cell.cellIndex;
	      cellsKeys.forEach(function (key) {
	        if (!BX.hasClass(cells[key], 'main-grid-special-empty')) {
	          var width = BX.width(cells[key]);

	          if (key > 0) {
	            width -= parseInt(BX.style(cells[key], 'border-left-width'));
	            width -= parseInt(BX.style(cells[key], 'border-right-width'));
	          }

	          BX.width(cells[key], width);
	          cellContainer = BX.firstChild(cells[key]);
	          BX.width(cellContainer, width);
	        }
	      });
	    },
	    _onDrag: function _onDrag(x) {
	      var table = this.parent.getTable();
	      var fixedTable = this.parent.getParam('ALLOW_PIN_HEADER') ? this.parent.getPinHeader().getFixedTable() : null;
	      var cell = table.rows[0].cells[this.__resizeCell];
	      var fixedCell, fixedCellContainer;
	      var cpos = BX.pos(cell);
	      var cellAttrWidth = parseFloat(cell.style.width);
	      var sX;
	      x -= cpos.left;
	      sX = x;

	      if (cpos.width > cellAttrWidth) {
	        x = cpos.width;
	      }

	      x = sX > x ? sX : x;
	      x = Math.max(x, 80);

	      if (x !== cpos.width) {
	        var fixedCells = this.parent.getAllRows()[0].querySelectorAll('.main-grid-fixed-column').length;
	        var column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells); // Resize current column

	        column.forEach(function (item) {
	          item.style.width = x + 'px';
	          item.style.minWidth = x + 'px';
	          item.style.maxWidth = x + 'px';
	          BX.Dom.style(item.firstElementChild, 'width', x + 'px');
	        }); // Resize false columns

	        if (column[0].classList.contains('main-grid-fixed-column')) {
	          column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells + 1);
	          column.forEach(function (item) {
	            item.style.width = x + 'px';
	            item.style.minWidth = x + 'px';
	            item.style.maxWidth = x + 'px';
	          });
	        }

	        this.parent.adjustFixedColumnsPosition();
	        this.parent.adjustFadePosition(this.parent.getFadeOffset());

	        if (BX.type.isDomNode(fixedTable) && BX.type.isDomNode(fixedTable.rows[0])) {
	          fixedCell = fixedTable.rows[0].cells[this.__resizeCell];
	          fixedCellContainer = BX.firstChild(fixedCell);
	          fixedCellContainer.style.width = x + 'px';
	          fixedCellContainer.style.minWidth = x + 'px';
	          fixedCell.style.width = x + 'px';
	          fixedCell.style.minWidth = x + 'px';
	        }
	      }

	      BX.onCustomEvent(window, 'Grid::columnResize', []);
	    },
	    _onDragEnd: function _onDragEnd() {
	      this.saveSizes();
	      var cell = BX.findParent(jsDD.current_node, {
	        className: this.parent.settings.get('classHeadCell')
	      });
	      var overlay = cell.querySelector('.main-grid-cell-overlay');

	      if (overlay) {
	        BX.Dom.remove(overlay);
	      }
	    },
	    getColumnSizes: function getColumnSizes() {
	      var cells = this.parent.getRows().getHeadFirstChild().getCells();
	      var columns = {};
	      var name;
	      [].forEach.call(cells, function (current) {
	        name = BX.data(current, 'name');

	        if (BX.type.isNotEmptyString(name)) {
	          columns[name] = BX.width(current);
	        }
	      }, this);
	      return columns;
	    },
	    saveSizes: function saveSizes() {
	      this.parent.getUserOptions().setColumnSizes(this.getColumnSizes(), 1);
	    }
	  };
	})();

	var _templateObject$1, _templateObject2$1, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15;

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.Row
	   * @param {BX.Main.Grid} parent
	   * @param {HtmlElement} node
	   * @constructor
	   */

	  BX.Grid.Row = function (parent, node) {
	    this.node = null;
	    this.checkbox = null;
	    this.sort = null;
	    this.actions = null;
	    this.settings = null;
	    this.index = null;
	    this.actionsButton = null;
	    this.parent = null;
	    this.depth = null;
	    this.parentId = null;
	    this.editData = null;
	    this.custom = null;
	    this.onElementClick = this.onElementClick.bind(this);
	    this.init(parent, node);
	    this.initElementsEvents();
	  }; //noinspection JSUnusedGlobalSymbols,JSUnusedGlobalSymbols


	  BX.Grid.Row.prototype = {
	    init: function init(parent, node) {
	      if (BX.type.isDomNode(node)) {
	        this.node = node;
	        this.parent = parent;
	        this.settings = new BX.Grid.Settings();
	        this.bindNodes = [];

	        if (this.isBodyChild()) {
	          this.bindNodes = [].slice.call(this.node.parentNode.querySelectorAll("tr[data-bind=\"" + this.getId() + "\"]"));

	          if (this.bindNodes.length) {
	            this.node.addEventListener("mouseover", this.onMouseOver.bind(this));
	            this.node.addEventListener("mouseleave", this.onMouseLeave.bind(this));
	            this.bindNodes.forEach(function (row) {
	              row.addEventListener("mouseover", this.onMouseOver.bind(this));
	              row.addEventListener("mouseleave", this.onMouseLeave.bind(this));
	              row.addEventListener("click", function () {
	                if (this.isSelected()) {
	                  this.unselect();
	                } else {
	                  this.select();
	                }
	              }.bind(this));
	            }, this);
	          }
	        }

	        if (this.parent.getParam('ALLOW_CONTEXT_MENU')) {
	          BX.bind(this.getNode(), 'contextmenu', BX.delegate(this._onRightClick, this));
	        }
	      }
	    },
	    onMouseOver: function onMouseOver() {
	      this.node.classList.add("main-grid-row-over");
	      this.bindNodes.forEach(function (row) {
	        row.classList.add("main-grid-row-over");
	      });
	    },
	    onMouseLeave: function onMouseLeave() {
	      this.node.classList.remove("main-grid-row-over");
	      this.bindNodes.forEach(function (row) {
	        row.classList.remove("main-grid-row-over");
	      });
	    },
	    isCustom: function isCustom() {
	      if (this.custom === null) {
	        this.custom = BX.hasClass(this.getNode(), this.parent.settings.get('classRowCustom'));
	      }

	      return this.custom;
	    },
	    _onRightClick: function _onRightClick(event) {
	      event.preventDefault();

	      if (!this.isHeadChild()) {
	        this.showActionsMenu(event);
	      }
	    },
	    getDefaultAction: function getDefaultAction() {
	      return BX.data(this.getNode(), 'default-action');
	    },
	    getEditorValue: function getEditorValue() {
	      var self = this;
	      var cells = this.getCells();
	      var values = {};
	      var cellValues;
	      [].forEach.call(cells, function (current) {
	        cellValues = self.getCellEditorValue(current);

	        if (BX.type.isArray(cellValues)) {
	          cellValues.forEach(function (cellValue) {
	            values[cellValue.NAME] = cellValue.VALUE !== undefined ? cellValue.VALUE : "";

	            if (cellValue.hasOwnProperty("RAW_NAME") && cellValue.hasOwnProperty("RAW_VALUE")) {
	              values[cellValue.NAME + "_custom"] = values[cellValue.NAME + "_custom"] || {};
	              values[cellValue.NAME + "_custom"][cellValue.RAW_NAME] = values[cellValue.NAME + "_custom"][cellValue.RAW_NAME] || cellValue.RAW_VALUE;
	            }
	          });
	        } else if (cellValues) {
	          values[cellValues.NAME] = cellValues.VALUE !== undefined ? cellValues.VALUE : "";
	        }
	      });
	      return values;
	    },

	    /**
	     * @deprecated
	     * @use this.getEditorValue()
	     */
	    editGetValues: function editGetValues() {
	      return this.getEditorValue();
	    },
	    getCellEditorValue: function getCellEditorValue(cell) {
	      var editor = BX.Grid.Utils.getByClass(cell, this.parent.settings.get('classEditor'), true);
	      var result = null;

	      if (BX.type.isDomNode(editor)) {
	        if (BX.hasClass(editor, 'main-grid-editor-checkbox')) {
	          result = {
	            'NAME': editor.getAttribute('name'),
	            'VALUE': editor.checked ? 'Y' : 'N'
	          };
	        } else if (BX.hasClass(editor, 'main-grid-editor-custom')) {
	          result = this.getCustomValue(editor);
	        } else if (BX.hasClass(editor, 'main-grid-editor-money')) {
	          result = this.getMoneyValue(editor);
	        } else if (BX.hasClass(editor, 'main-ui-multi-select')) {
	          result = this.getMultiSelectValues(editor);
	        } else {
	          result = this.getImageValue(editor);
	        }
	      }

	      return result;
	    },
	    isEdit: function isEdit() {
	      return BX.hasClass(this.getNode(), 'main-grid-row-edit');
	    },
	    hide: function hide() {
	      BX.addClass(this.getNode(), this.parent.settings.get('classHide'));
	    },
	    show: function show() {
	      BX.Dom.attr(this.getNode(), 'hidden', null);
	      BX.removeClass(this.getNode(), this.parent.settings.get('classHide'));
	    },
	    isShown: function isShown() {
	      return !BX.hasClass(this.getNode(), this.parent.settings.get('classHide'));
	    },
	    isNotCount: function isNotCount() {
	      return BX.hasClass(this.getNode(), this.parent.settings.get('classNotCount'));
	    },
	    getContentContainer: function getContentContainer(target) {
	      if (BX.Type.isDomNode(target)) {
	        var cell = target.closest('.main-grid-cell');

	        if (BX.Type.isDomNode(cell)) {
	          return cell.querySelector('.main-grid-cell-content');
	        }
	      }

	      return target;
	    },
	    getContent: function getContent(cell) {
	      var container = this.getContentContainer(cell);
	      var content;

	      if (BX.type.isDomNode(container)) {
	        content = BX.html(container);
	      }

	      return content;
	    },
	    getMoneyValue: function getMoneyValue(editor) {
	      var result = [];
	      var filteredValue = {
	        PRICE: {},
	        CURRENCY: {},
	        HIDDEN: {}
	      };
	      var fieldName = editor.getAttribute('data-name');
	      var inputs = [].slice.call(editor.querySelectorAll('input'));
	      inputs.forEach(function (element) {
	        result.push({
	          NAME: fieldName,
	          RAW_NAME: element.name,
	          RAW_VALUE: element.value || '',
	          VALUE: element.value || ''
	        });

	        if (element.classList.contains('main-grid-editor-money-price')) {
	          filteredValue.PRICE = {
	            NAME: element.name,
	            VALUE: element.value
	          };
	        } else if (element.type === ' hidden') {
	          filteredValue.HIDDEN[element.name] = element.value;
	        }
	      });
	      var currencySelector = editor.querySelector('.main-grid-editor-dropdown');

	      if (currencySelector) {
	        var currencyFieldName = currencySelector.getAttribute('name');

	        if (BX.type.isNotEmptyString(currencyFieldName)) {
	          result.push({
	            NAME: fieldName,
	            RAW_NAME: currencyFieldName,
	            RAW_VALUE: currencySelector.dataset.value || '',
	            VALUE: currencySelector.dataset.value || ''
	          });
	          filteredValue.CURRENCY = {
	            NAME: currencyFieldName,
	            VALUE: currencySelector.dataset.value
	          };
	        }
	      }

	      result.push({
	        NAME: fieldName,
	        VALUE: filteredValue
	      });
	      return result;
	    },
	    getCustomValue: function getCustomValue(editor) {
	      var map = new Map(),
	          name = editor.getAttribute('data-name');
	      var inputs = [].slice.call(editor.querySelectorAll('input, select, checkbox, textarea'));
	      inputs.forEach(function (element) {
	        var resultObject = {
	          'NAME': name,
	          'RAW_NAME': element.name,
	          'RAW_VALUE': element.value,
	          'VALUE': element.value
	        };

	        switch (element.tagName) {
	          case 'SELECT':
	            if (element.multiple) {
	              var selectValues = [];
	              element.querySelectorAll('option').forEach(function (option) {
	                if (option.selected) {
	                  selectValues.push(option.value);
	                }
	              });
	              resultObject['RAW_VALUE'] = selectValues;
	              resultObject['VALUE'] = selectValues;
	              map.set(element.name, resultObject);
	            } else {
	              map.set(element.name, resultObject);
	            }

	            break;

	          case 'INPUT':
	            switch (element.type.toUpperCase()) {
	              case 'RADIO':
	                if (element.checked) {
	                  resultObject['RAW_VALUE'] = element.value;
	                  resultObject['VALUE'] = element.value;
	                  map.set(element.name, resultObject);
	                }

	                break;

	              case 'CHECKBOX':
	                resultObject['RAW_VALUE'] = element.checked ? element.value : '';
	                resultObject['VALUE'] = element.checked ? element.value : '';
	                map.set(element.name, resultObject);
	                break;

	              case 'FILE':
	                resultObject['RAW_VALUE'] = element.files[0];
	                resultObject['VALUE'] = element.files[0];
	                map.set(element.name, resultObject);
	                break;

	              default:
	                map.set(element.name, resultObject);
	            }

	            break;

	          default:
	            map.set(element.name, resultObject);
	        }
	      });
	      var result = [];
	      map.forEach(function (value) {
	        result.push(value);
	      });
	      return result;
	    },
	    getImageValue: function getImageValue(editor) {
	      var result = null;

	      if (BX.hasClass(editor, 'main-grid-image-editor')) {
	        var input = editor.querySelector('.main-grid-image-editor-file-input');

	        if (input) {
	          result = {
	            'NAME': input.name,
	            'VALUE': input.files[0]
	          };
	        } else {
	          var fakeInput = editor.querySelector('.main-grid-image-editor-fake-file-input');

	          if (fakeInput) {
	            result = {
	              'NAME': fakeInput.name,
	              'VALUE': fakeInput.value
	            };
	          }
	        }
	      } else if (editor.value) {
	        result = {
	          'NAME': editor.getAttribute('name'),
	          'VALUE': editor.value
	        };
	      } else {
	        result = {
	          'NAME': editor.getAttribute('name'),
	          'VALUE': BX.data(editor, 'value')
	        };
	      }

	      return result;
	    },
	    getMultiSelectValues: function getMultiSelectValues(editor) {
	      var value = JSON.parse(BX.data(editor, 'value'));
	      return {
	        'NAME': editor.getAttribute('name'),
	        'VALUE': main_core.Type.isArrayFilled(value) ? value : ''
	      };
	    },

	    /**
	     * @param {HTMLTableCellElement} cell
	     * @return {?HTMLElement}
	     */
	    getEditorContainer: function getEditorContainer(cell) {
	      return BX.Grid.Utils.getByClass(cell, this.parent.settings.get('classEditorContainer'), true);
	    },

	    /**
	     * @return {HTMLElement}
	     */
	    getCollapseButton: function getCollapseButton() {
	      if (!this.collapseButton) {
	        this.collapseButton = BX.Grid.Utils.getByClass(this.getNode(), this.parent.settings.get('classCollapseButton'), true);
	      }

	      return this.collapseButton;
	    },
	    stateLoad: function stateLoad() {
	      BX.addClass(this.getNode(), this.parent.settings.get('classRowStateLoad'));
	    },
	    stateUnload: function stateUnload() {
	      BX.removeClass(this.getNode(), this.parent.settings.get('classRowStateLoad'));
	    },
	    stateExpand: function stateExpand() {
	      BX.addClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
	    },
	    stateCollapse: function stateCollapse() {
	      BX.removeClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
	    },
	    getParentId: function getParentId() {
	      if (this.parentId === null) {
	        this.parentId = BX.data(this.getNode(), 'parent-id');

	        if (typeof this.parentId !== 'undefined' && this.parentId !== null) {
	          this.parentId = this.parentId.toString();
	        }
	      }

	      return this.parentId;
	    },

	    /**
	     * @return {DOMStringMap}
	     */
	    getDataset: function getDataset() {
	      return this.getNode().dataset;
	    },

	    /**
	     * Gets row depth level
	     * @return {?number}
	     */
	    getDepth: function getDepth() {
	      if (this.depth === null) {
	        this.depth = BX.data(this.getNode(), 'depth');
	      }

	      return this.depth;
	    },

	    /**
	     * Set row depth
	     * @param {number} depth
	     */
	    setDepth: function setDepth(depth) {
	      depth = parseInt(depth);

	      if (BX.type.isNumber(depth)) {
	        var depthOffset = depth - parseInt(this.getDepth());
	        var Rows = this.parent.getRows();
	        this.getDataset().depth = depth;
	        this.getShiftCells().forEach(function (cell) {
	          BX.data(cell, 'depth', depth);
	          BX.style(cell, 'padding-left', depth * 20 + 'px');
	        }, this);
	        Rows.getRowsByParentId(this.getId(), true).forEach(function (row) {
	          var childDepth = parseInt(depthOffset) + parseInt(row.getDepth());
	          row.getDataset().depth = childDepth;
	          row.getShiftCells().forEach(function (cell) {
	            BX.data(cell, 'depth', childDepth);
	            BX.style(cell, 'padding-left', childDepth * 20 + 'px');
	          });
	        });
	      }
	    },

	    /**
	     * Sets parent id
	     * @param {string|number} id
	     */
	    setParentId: function setParentId(id) {
	      this.getDataset()['parentId'] = id;
	    },

	    /**
	     * @return {HTMLTableRowElement}
	     */
	    getShiftCells: function getShiftCells() {
	      return BX.Grid.Utils.getBySelector(this.getNode(), 'td[data-shift="true"]');
	    },
	    showChildRows: function showChildRows() {
	      var rows = this.getChildren();
	      var isCustom = this.isCustom();
	      rows.forEach(function (row) {
	        row.show();

	        if (!isCustom && row.isExpand()) {
	          row.showChildRows();
	        }
	      });
	      this.parent.updateCounterDisplayed();
	      this.parent.updateCounterSelected();
	      this.parent.adjustCheckAllCheckboxes();
	      this.parent.adjustRows();
	    },

	    /**
	     * @return {BX.Grid.Row[]}
	     */
	    getChildren: function getChildren() {
	      var functionName = this.isCustom() ? 'getRowsByGroupId' : 'getRowsByParentId';
	      var id = this.isCustom() ? this.getGroupId() : this.getId();
	      return this.parent.getRows()[functionName](id, true);
	    },
	    hideChildRows: function hideChildRows() {
	      var rows = this.getChildren();
	      rows.forEach(function (row) {
	        row.hide();
	      });
	      this.parent.updateCounterDisplayed();
	      this.parent.updateCounterSelected();
	      this.parent.adjustCheckAllCheckboxes();
	      this.parent.adjustRows();
	    },
	    isChildsLoaded: function isChildsLoaded() {
	      if (!BX.type.isBoolean(this.childsLoaded)) {
	        this.childsLoaded = this.isCustom() || BX.data(this.getNode(), 'child-loaded') === 'true';
	      }

	      return this.childsLoaded;
	    },
	    expand: function expand() {
	      var self = this;
	      this.stateExpand();

	      if (this.isChildsLoaded()) {
	        this.showChildRows();
	      } else {
	        this.stateLoad();
	        this.loadChildRows(function (rows) {
	          rows.reverse().forEach(function (current) {
	            BX.insertAfter(current, self.getNode());
	          });
	          self.parent.getRows().reset();
	          self.parent.bindOnRowEvents();

	          if (self.parent.getParam('ALLOW_ROWS_SORT')) {
	            self.parent.getRowsSortable().reinit();
	          }

	          if (self.parent.getParam('ALLOW_COLUMNS_SORT')) {
	            self.parent.getColsSortable().reinit();
	          }

	          self.stateUnload();
	          BX.data(self.getNode(), 'child-loaded', 'true');
	          self.parent.updateCounterDisplayed();
	          self.parent.updateCounterSelected();
	          self.parent.adjustCheckAllCheckboxes();
	        });
	      }
	    },
	    collapse: function collapse() {
	      this.stateCollapse();
	      this.hideChildRows();
	    },
	    isExpand: function isExpand() {
	      return BX.hasClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
	    },
	    toggleChildRows: function toggleChildRows() {
	      if (!this.isExpand()) {
	        this.expand();
	      } else {
	        this.collapse();
	      }
	    },
	    loadChildRows: function loadChildRows(callback) {
	      if (BX.type.isFunction(callback)) {
	        var self = this;
	        var depth = parseInt(this.getDepth());
	        var action = this.parent.getUserOptions().getAction('GRID_GET_CHILD_ROWS');
	        depth = BX.type.isNumber(depth) ? depth + 1 : 1;
	        this.parent.getData().request('', 'POST', {
	          action: action,
	          parent_id: this.getId(),
	          depth: depth
	        }, null, function () {
	          var rows = this.getRowsByParentId(self.getId());
	          callback.apply(null, [rows]);
	        });
	      }
	    },
	    update: function update(data, url, callback) {
	      data = !!data ? data : '';
	      var action = this.parent.getUserOptions().getAction('GRID_UPDATE_ROW');
	      var depth = this.getDepth();
	      var id = this.getId();
	      var parentId = this.getParentId();
	      var rowData = {
	        id: id,
	        parentId: parentId,
	        action: action,
	        depth: depth,
	        data: data
	      };
	      var self = this;
	      this.stateLoad();
	      this.parent.getData().request(url, 'POST', rowData, null, function () {
	        var bodyRows = this.getBodyRows();
	        self.parent.getUpdater().updateBodyRows(bodyRows);
	        self.stateUnload();
	        self.parent.getRows().reset();
	        self.parent.getUpdater().updateFootRows(this.getFootRows());
	        self.parent.getUpdater().updatePagination(this.getPagination());
	        self.parent.getUpdater().updateMoreButton(this.getMoreButton());
	        self.parent.getUpdater().updateCounterTotal(this.getCounterTotal());
	        self.parent.bindOnRowEvents();
	        self.parent.adjustEmptyTable(bodyRows);
	        self.parent.bindOnMoreButtonEvents();
	        self.parent.bindOnClickPaginationLinks();
	        self.parent.updateCounterDisplayed();
	        self.parent.updateCounterSelected();

	        if (self.parent.getParam('ALLOW_COLUMNS_SORT')) {
	          self.parent.colsSortable.reinit();
	        }

	        if (self.parent.getParam('ALLOW_ROWS_SORT')) {
	          self.parent.rowsSortable.reinit();
	        }

	        BX.onCustomEvent(window, 'Grid::rowUpdated', [{
	          id: id,
	          data: data,
	          grid: self.parent,
	          response: this
	        }]);
	        BX.onCustomEvent(window, 'Grid::updated', [self.parent]);

	        if (BX.type.isFunction(callback)) {
	          callback({
	            id: id,
	            data: data,
	            grid: self.parent,
	            response: this
	          });
	        }
	      });
	    },
	    remove: function remove(data, url, callback) {
	      data = !!data ? data : '';
	      var action = this.parent.getUserOptions().getAction('GRID_DELETE_ROW');
	      var depth = this.getDepth();
	      var id = this.getId();
	      var parentId = this.getParentId();
	      var rowData = {
	        id: id,
	        parentId: parentId,
	        action: action,
	        depth: depth,
	        data: data
	      };
	      var self = this;
	      this.stateLoad();
	      this.parent.getData().request(url, 'POST', rowData, null, function () {
	        var bodyRows = this.getBodyRows();
	        self.parent.getUpdater().updateBodyRows(bodyRows);
	        self.stateUnload();
	        self.parent.getRows().reset();
	        self.parent.getUpdater().updateFootRows(this.getFootRows());
	        self.parent.getUpdater().updatePagination(this.getPagination());
	        self.parent.getUpdater().updateMoreButton(this.getMoreButton());
	        self.parent.getUpdater().updateCounterTotal(this.getCounterTotal());
	        self.parent.bindOnRowEvents();
	        self.parent.adjustEmptyTable(bodyRows);
	        self.parent.bindOnMoreButtonEvents();
	        self.parent.bindOnClickPaginationLinks();
	        self.parent.updateCounterDisplayed();
	        self.parent.updateCounterSelected();

	        if (self.parent.getParam('ALLOW_COLUMNS_SORT')) {
	          self.parent.colsSortable.reinit();
	        }

	        if (self.parent.getParam('ALLOW_ROWS_SORT')) {
	          self.parent.rowsSortable.reinit();
	        }

	        BX.onCustomEvent(window, 'Grid::rowRemoved', [{
	          id: id,
	          data: data,
	          grid: self.parent,
	          response: this
	        }]);
	        BX.onCustomEvent(window, 'Grid::updated', [self.parent]);

	        if (BX.type.isFunction(callback)) {
	          callback({
	            id: id,
	            data: data,
	            grid: self.parent,
	            response: this
	          });
	        }
	      });
	    },
	    editCancel: function editCancel() {
	      var cells = this.getCells();
	      var self = this;
	      var editorContainer;
	      [].forEach.call(cells, function (current) {
	        editorContainer = self.getEditorContainer(current);

	        if (BX.type.isDomNode(editorContainer)) {
	          BX.remove(self.getEditorContainer(current));
	          BX.show(self.getContentContainer(current));
	        }
	      });
	      BX.removeClass(this.getNode(), 'main-grid-row-edit');
	    },
	    getCellByIndex: function getCellByIndex(index) {
	      return this.getCells()[index];
	    },
	    getEditDataByCellIndex: function getEditDataByCellIndex(index) {
	      return eval(BX.data(this.getCellByIndex(index), 'edit'));
	    },
	    getCellNameByCellIndex: function getCellNameByCellIndex(index) {
	      return BX.data(this.getCellByIndex(index), 'name');
	    },
	    resetEditData: function resetEditData() {
	      this.editData = null;
	    },
	    setEditData: function setEditData(editData) {
	      this.editData = editData;
	    },
	    getEditData: function getEditData() {
	      if (this.editData === null) {
	        var editableData = this.parent.getParam('EDITABLE_DATA');
	        var rowId = this.getId();

	        if (BX.type.isPlainObject(editableData) && rowId in editableData) {
	          this.editData = editableData[rowId];
	        } else {
	          this.editData = {};
	        }
	      }

	      return this.editData;
	    },
	    getCellEditDataByCellIndex: function getCellEditDataByCellIndex(cellIndex) {
	      var editData = this.getEditData();
	      var result = null;
	      cellIndex = parseInt(cellIndex);

	      if (BX.type.isNumber(cellIndex) && BX.type.isPlainObject(editData)) {
	        var columnEditData = this.parent.getRows().getHeadFirstChild().getEditDataByCellIndex(cellIndex);

	        if (BX.type.isPlainObject(columnEditData)) {
	          result = columnEditData;
	          result.VALUE = editData[columnEditData.NAME];
	        }
	      }

	      return result;
	    },
	    edit: function edit() {
	      var cells = this.getCells();
	      var self = this;
	      var editObject, editor, height, contentContainer;
	      [].forEach.call(cells, function (current, index) {
	        if (current.dataset.editable === 'true') {
	          try {
	            editObject = self.getCellEditDataByCellIndex(index);
	          } catch (err) {
	            throw new Error(err);
	          }

	          if (self.parent.getEditor().validateEditObject(editObject)) {
	            contentContainer = self.getContentContainer(current);
	            height = BX.height(contentContainer);
	            editor = self.parent.getEditor().getEditor(editObject, height);

	            if (!self.getEditorContainer(current) && BX.type.isDomNode(editor)) {
	              current.appendChild(editor);
	              BX.hide(contentContainer);
	            }
	          }
	        }
	      });
	      BX.addClass(this.getNode(), 'main-grid-row-edit');
	    },
	    setDraggable: function setDraggable(value) {
	      if (!value) {
	        BX.addClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
	        this.parent.getRowsSortable().unregister(this.getNode());
	      } else {
	        BX.removeClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
	        this.parent.getRowsSortable().register(this.getNode());
	      }
	    },
	    isDraggable: function isDraggable() {
	      return !BX.hasClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
	    },
	    getNode: function getNode() {
	      return this.node;
	    },
	    getIndex: function getIndex() {
	      return this.getNode().rowIndex;
	    },
	    getId: function getId() {
	      return String(BX.data(this.getNode(), 'id'));
	    },
	    getGroupId: function getGroupId() {
	      return BX.data(this.getNode(), 'group-id').toString();
	    },
	    getObserver: function getObserver() {
	      return BX.Grid.observer;
	    },
	    getCheckbox: function getCheckbox() {
	      if (!this.checkbox) {
	        this.checkbox = BX.Grid.Utils.getByClass(this.getNode(), this.settings.get('classRowCheckbox'), true);
	      }

	      return this.checkbox;
	    },
	    getActionsMenu: function getActionsMenu() {
	      if (!this.actionsMenu) {
	        var buttonRect = this.getActionsButton().getBoundingClientRect();
	        this.actionsMenu = BX.PopupMenu.create('main-grid-actions-menu-' + this.getId(), this.getActionsButton(), this.getMenuItems(), {
	          'autoHide': true,
	          'offsetTop': -(buttonRect.height / 2 + 26),
	          'offsetLeft': 30,
	          'angle': {
	            'position': 'left',
	            'offset': buttonRect.height / 2 - 8
	          },
	          'events': {
	            'onPopupClose': BX.delegate(this._onCloseMenu, this),
	            'onPopupShow': BX.delegate(this._onPopupShow, this)
	          }
	        });
	        BX.addCustomEvent('Grid::updated', function () {
	          if (this.actionsMenu) {
	            this.actionsMenu.destroy();
	            this.actionsMenu = null;
	          }
	        }.bind(this));
	        BX.bind(this.actionsMenu.popupWindow.popupContainer, 'click', BX.delegate(function (event) {
	          var actionsMenu = this.getActionsMenu();

	          if (actionsMenu) {
	            var target = BX.getEventTarget(event);
	            var item = BX.findParent(target, {
	              className: 'menu-popup-item'
	            }, 10);

	            if (!item || !item.dataset.preventCloseContextMenu) {
	              actionsMenu.close();
	            }
	          }
	        }, this));
	      }

	      return this.actionsMenu;
	    },
	    _onCloseMenu: function _onCloseMenu() {},
	    _onPopupShow: function _onPopupShow(popupMenu) {
	      popupMenu.setBindElement(this.getActionsButton());
	    },
	    actionsMenuIsShown: function actionsMenuIsShown() {
	      return this.getActionsMenu().popupWindow.isShown();
	    },
	    showActionsMenu: function showActionsMenu(event) {
	      BX.fireEvent(document.body, 'click');
	      this.getActionsMenu().popupWindow.show();

	      if (event) {
	        this.getActionsMenu().popupWindow.popupContainer.style.top = event.pageY - 25 + BX.PopupWindow.getOption("offsetTop") + "px";
	        this.getActionsMenu().popupWindow.popupContainer.style.left = event.pageX + 20 + BX.PopupWindow.getOption("offsetLeft") + "px";
	      }
	    },
	    closeActionsMenu: function closeActionsMenu() {
	      if (this.actionsMenu) {
	        if (this.actionsMenu.popupWindow) {
	          this.actionsMenu.popupWindow.close();
	        }
	      }
	    },
	    getMenuItems: function getMenuItems() {
	      return this.getActions() || [];
	    },
	    getActions: function getActions() {
	      try {
	        this.actions = this.actions || eval(BX.data(this.getActionsButton(), this.settings.get('dataActionsKey')));
	      } catch (err) {
	        this.actions = null;
	      }

	      return this.actions;
	    },
	    getActionsButton: function getActionsButton() {
	      if (!this.actionsButton) {
	        this.actionsButton = BX.Grid.Utils.getByClass(this.getNode(), this.settings.get('classRowActionButton'), true);
	      }

	      return this.actionsButton;
	    },
	    initSelect: function initSelect() {
	      if (this.isSelected() && !BX.hasClass(this.getNode(), this.settings.get('classCheckedRow'))) {
	        BX.addClass(this.getNode(), this.settings.get('classCheckedRow'));
	      }
	    },
	    getParentNode: function getParentNode() {
	      var result;

	      try {
	        result = this.getNode().parentNode;
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    },
	    getParentNodeName: function getParentNodeName() {
	      var result;

	      try {
	        result = this.getParentNode().nodeName;
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    },
	    isSelectable: function isSelectable() {
	      return !this.isEdit() || this.parent.getParam('ALLOW_EDIT_SELECTION');
	    },
	    select: function select() {
	      var checkbox;

	      if (this.isSelectable() && (this.parent.getParam('ADVANCED_EDIT_MODE') || !this.parent.getRows().hasEditable())) {
	        checkbox = this.getCheckbox();

	        if (checkbox) {
	          if (!BX.data(checkbox, 'disabled')) {
	            BX.addClass(this.getNode(), this.settings.get('classCheckedRow'));
	            this.bindNodes.forEach(function (row) {
	              BX.addClass(row, this.settings.get('classCheckedRow'));
	            }, this);
	            checkbox.checked = true;
	          }
	        }
	      }
	    },
	    unselect: function unselect() {
	      if (this.isSelectable()) {
	        BX.removeClass(this.getNode(), this.settings.get('classCheckedRow'));
	        this.bindNodes.forEach(function (row) {
	          BX.removeClass(row, this.settings.get('classCheckedRow'));
	        }, this);

	        if (this.getCheckbox()) {
	          this.getCheckbox().checked = false;
	        }
	      }
	    },
	    getCells: function getCells() {
	      return this.getNode().cells;
	    },
	    isSelected: function isSelected() {
	      return this.getCheckbox() && this.getCheckbox().checked || BX.hasClass(this.getNode(), this.settings.get('classCheckedRow'));
	    },
	    isHeadChild: function isHeadChild() {
	      return this.getParentNodeName() === 'THEAD' && BX.hasClass(this.getNode(), this.settings.get('classHeadRow'));
	    },
	    isBodyChild: function isBodyChild() {
	      return BX.hasClass(this.getNode(), this.settings.get('classBodyRow')) && !BX.hasClass(this.getNode(), this.settings.get('classEmptyRows'));
	    },
	    isFootChild: function isFootChild() {
	      return this.getParentNodeName() === 'TFOOT' && BX.hasClass(this.getNode(), this.settings.get('classFootRow'));
	    },
	    prependTo: function prependTo(target) {
	      BX.Dom.prepend(this.getNode(), target);
	    },
	    appendTo: function appendTo(target) {
	      BX.Dom.append(this.getNode(), target);
	    },
	    setId: function setId(id) {
	      BX.Dom.attr(this.getNode(), 'data-id', id);
	    },
	    setActions: function setActions(actions) {
	      var actionCell = this.getNode().querySelector('.main-grid-cell-action');

	      if (actionCell) {
	        var actionButton = actionCell.querySelector('.main-grid-row-action-button');

	        if (!actionButton) {
	          actionButton = BX.Dom.create({
	            tag: 'div',
	            props: {
	              className: 'main-grid-row-action-button'
	            }
	          });
	          var container = this.getContentContainer(actionCell);
	          BX.Dom.append(actionButton, container);
	        }

	        BX.Dom.attr(actionButton, {
	          href: '#',
	          'data-actions': actions
	        });
	        this.actions = actions;

	        if (this.actionsMenu) {
	          this.actionsMenu.destroy();
	          this.actionsMenu = null;
	        }
	      }
	    },
	    makeCountable: function makeCountable() {
	      BX.Dom.removeClass(this.getNode(), 'main-grid-not-count');
	    },
	    makeNotCountable: function makeNotCountable() {
	      BX.Dom.addClass(this.getNode(), 'main-grid-not-count');
	    },
	    getColumnOptions: function getColumnOptions(columnId) {
	      var columns = this.parent.getParam('COLUMNS_ALL');

	      if (BX.Type.isPlainObject(columns) && Reflect.has(columns, columnId)) {
	        return columns[columnId];
	      }

	      return null;
	    },
	    setCellsContent: function setCellsContent(content) {
	      var _this = this;

	      var headRow = this.parent.getRows().getHeadFirstChild();
	      babelHelpers.toConsumableArray(this.getCells()).forEach(function (cell, cellIndex) {
	        var cellName = headRow.getCellNameByCellIndex(cellIndex);

	        if (Reflect.has(content, cellName)) {
	          var columnOptions = _this.getColumnOptions(cellName);

	          var container = _this.getContentContainer(cell);

	          var cellContent = content[cellName];

	          if (columnOptions.type === 'labels' && BX.Type.isArray(cellContent)) {
	            var labels = cellContent.map(function (labelOptions) {
	              var label = BX.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"ui-label ", "\"></span>\n\t\t\t\t\t\t\t"])), labelOptions.color);

	              if (labelOptions.light !== true) {
	                BX.Dom.addClass(label, 'ui-label-fill');
	              }

	              if (BX.Type.isPlainObject(labelOptions.events)) {
	                if (Reflect.has(labelOptions.events, 'click')) {
	                  BX.Dom.addClass(label, 'ui-label-link');
	                }

	                _this.bindOnEvents(label, labelOptions.events);
	              }

	              var labelContent = function () {
	                if (BX.Type.isStringFilled(labelOptions.html)) {
	                  return labelOptions.html;
	                }

	                return labelOptions.text;
	              }();

	              var inner = BX.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"ui-label-inner\">", "</span>\n\t\t\t\t\t\t\t"])), labelContent);
	              BX.Dom.append(inner, label);

	              if (BX.Type.isPlainObject(labelOptions.removeButton)) {
	                var button = function () {
	                  if (labelOptions.removeButton.type === BX.Grid.Label.RemoveButtonType.INSIDE) {
	                    return BX.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t\t\t<span class=\"ui-label-icon\"></span>\t\n\t\t\t\t\t\t\t\t\t\t"])));
	                  }

	                  return BX.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t\t<span class=\"main-grid-label-remove-button ", "\"></span>\t\n\t\t\t\t\t\t\t\t\t"])), labelOptions.removeButton.type);
	                }();

	                if (BX.Type.isPlainObject(labelOptions.removeButton.events)) {
	                  _this.bindOnEvents(button, labelOptions.removeButton.events);
	                }

	                BX.Dom.append(button, label);
	              }

	              return label;
	            });
	            var labelsContainer = BX.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"main-grid-labels\">", "</div>\n\t\t\t\t\t\t"])), labels);
	            BX.Dom.clean(container);
	            var oldLabelsContainer = container.querySelector('.main-grid-labels');

	            if (BX.Type.isDomNode(oldLabelsContainer)) {
	              BX.Dom.replace(oldLabelsContainer, labelsContainer);
	            } else {
	              BX.Dom.append(labelsContainer, container);
	            }
	          } else if (columnOptions.type === 'tags' && BX.Type.isPlainObject(cellContent)) {
	            var tags = cellContent.items.map(function (tagOptions) {
	              var tag = BX.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"main-grid-tag\"></span>\n\t\t\t\t\t\t\t"])));

	              _this.bindOnEvents(tag, tagOptions.events);

	              if (tagOptions.active === true) {
	                BX.Dom.addClass(tag, 'main-grid-tag-active');
	              }

	              var tagContent = function () {
	                if (BX.Type.isStringFilled(tagOptions.html)) {
	                  return tagOptions.html;
	                }

	                return BX.Text.encode(tagOptions.text);
	              }();

	              var tagInner = BX.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"main-grid-tag-inner\">", "</span>\n\t\t\t\t\t\t\t"])), tagContent);
	              BX.Dom.append(tagInner, tag);

	              if (tagOptions.active === true) {
	                var removeButton = BX.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t<span class=\"main-grid-tag-remove\"></span>\n\t\t\t\t\t\t\t\t"])));
	                BX.Dom.append(removeButton, tag);

	                if (BX.Type.isPlainObject(tagOptions.removeButton)) {
	                  _this.bindOnEvents(removeButton, tagOptions.removeButton.events);
	                }
	              }

	              return tag;
	            });
	            var tagsContainer = BX.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"main-grid-tags\">", "</span>\n\t\t\t\t\t\t"])), tags);
	            var addButton = BX.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"main-grid-tag-add\"></span>\n\t\t\t\t\t\t"])));

	            if (BX.Type.isPlainObject(cellContent.addButton)) {
	              _this.bindOnEvents(addButton, cellContent.addButton.events);
	            }

	            BX.Dom.append(addButton, tagsContainer);
	            var oldTagsContainer = container.querySelector('.main-grid-tags');

	            if (BX.Type.isDomNode(oldTagsContainer)) {
	              BX.Dom.replace(oldTagsContainer, tagsContainer);
	            } else {
	              BX.Dom.append(tagsContainer, container);
	            }
	          } else {
	            BX.Runtime.html(container, cellContent);
	          }
	        }
	      });
	    },
	    getCellById: function getCellById(id) {
	      var headRow = this.parent.getRows().getHeadFirstChild();
	      return babelHelpers.toConsumableArray(this.getCells()).find(function (cell, index) {
	        return headRow.getCellNameByCellIndex(index) === id;
	      });
	    },
	    isTemplate: function isTemplate() {
	      return this.isBodyChild() && /^template_[0-9]$/.test(this.getId());
	    },
	    enableAbsolutePosition: function enableAbsolutePosition() {
	      var headCells = babelHelpers.toConsumableArray(this.parent.getRows().getHeadFirstChild().getCells());
	      var cellsWidth = headCells.map(function (cell) {
	        return BX.Dom.style(cell, 'width');
	      });
	      var cells = this.getCells();
	      cellsWidth.forEach(function (width, index) {
	        BX.Dom.style(cells[index], 'width', width);
	      });
	      BX.Dom.style(this.getNode(), 'position', 'absolute');
	    },
	    disableAbsolutePosition: function disableAbsolutePosition() {
	      BX.Dom.style(this.getNode(), 'position', null);
	    },
	    getHeight: function getHeight() {
	      return BX.Text.toNumber(BX.Dom.style(this.getNode(), 'height'));
	    },
	    setCellActions: function setCellActions(cellActions) {
	      var _this2 = this;

	      Object.entries(cellActions).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            cellId = _ref2[0],
	            actions = _ref2[1];

	        var cell = _this2.getCellById(cellId);

	        if (cell) {
	          var inner = cell.querySelector('.main-grid-cell-inner');

	          if (inner) {
	            var container = function () {
	              var currentContainer = inner.querySelector('.main-grid-cell-content-actions');

	              if (currentContainer) {
	                BX.Dom.clean(currentContainer);
	                return currentContainer;
	              }

	              var newContainer = BX.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div class=\"main-grid-cell-content-actions\"></div>\n\t\t\t\t\t\t\t"])));
	              BX.Dom.append(newContainer, inner);
	              return newContainer;
	            }();

	            if (BX.Type.isArrayFilled(actions)) {
	              actions.forEach(function (action) {
	                var actionClass = function () {
	                  if (BX.Type.isArrayFilled(action["class"])) {
	                    return action["class"].join(' ');
	                  }

	                  return action["class"];
	                }();

	                var button = BX.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t<span class=\"main-grid-cell-content-action ", "\"></span>\n\t\t\t\t\t\t\t\t"])), actionClass);

	                if (BX.Type.isPlainObject(action.events)) {
	                  _this2.bindOnEvents(button, action.events);
	                }

	                if (BX.Type.isPlainObject(action.attributes)) {
	                  BX.Dom.attr(button, action.attributes);
	                }

	                BX.Dom.append(button, container);
	              });
	            }
	          }
	        }
	      });
	    },

	    /**
	     * @private
	     */
	    initElementsEvents: function initElementsEvents() {
	      var _this3 = this;

	      var buttons = babelHelpers.toConsumableArray(this.getNode().querySelectorAll('.main-grid-cell [data-events]'));

	      if (BX.Type.isArrayFilled(buttons)) {
	        buttons.forEach(function (button) {
	          var events = eval(BX.Dom.attr(button, 'data-events'));

	          if (BX.Type.isPlainObject(events)) {
	            BX.Dom.attr(button, 'data-events', null);

	            _this3.bindOnEvents(button, events);
	          }
	        });
	      }
	    },

	    /**
	     * @private
	     * @param event
	     */
	    onElementClick: function onElementClick(event) {
	      event.stopPropagation();
	    },

	    /**
	     * @private
	     */
	    bindOnEvents: function bindOnEvents(button, events) {
	      if (BX.Type.isDomNode(button) && BX.Type.isPlainObject(events)) {
	        BX.Event.bind(button, 'click', this.onElementClick.bind(this));

	        var target = function () {
	          var selector = BX.Dom.attr(button, 'data-target');

	          if (selector) {
	            return button.closest(selector);
	          }

	          return button;
	        }();

	        var event = new BX.Event.BaseEvent({
	          data: {
	            button: button,
	            target: target,
	            row: this
	          }
	        });
	        event.setTarget(target);
	        Object.entries(events).forEach(function (_ref3) {
	          var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	              eventName = _ref4[0],
	              handler = _ref4[1];

	          var preparedHandler = eval(handler);
	          BX.Event.bind(button, eventName, preparedHandler.bind(null, event));
	        });
	      }
	    },
	    setCounters: function setCounters(counters) {
	      var _this4 = this;

	      if (BX.Type.isPlainObject(counters)) {
	        Object.entries(counters).forEach(function (_ref5) {
	          var _ref6 = babelHelpers.slicedToArray(_ref5, 2),
	              columnId = _ref6[0],
	              counter = _ref6[1];

	          var cell = _this4.getCellById(columnId);

	          if (BX.Type.isDomNode(cell)) {
	            var cellInner = cell.querySelector('.main-grid-cell-inner');

	            var counterContainer = function () {
	              var container = cell.querySelector('.main-grid-cell-counter');

	              if (BX.Type.isDomNode(container)) {
	                return container;
	              }

	              return BX.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"main-grid-cell-counter\"></span>\n\t\t\t\t\t\t\t"])));
	            }();

	            var uiCounter = function () {
	              var currentCounter = counterContainer.querySelector('.ui-counter');

	              if (BX.Type.isDomNode(currentCounter)) {
	                return currentCounter;
	              }

	              var newCounter = BX.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"ui-counter\"></span>\n\t\t\t\t\t\t\t"])));
	              BX.Dom.append(newCounter, counterContainer);
	              return newCounter;
	            }();

	            if (BX.Type.isPlainObject(counter.events)) {
	              _this4.bindOnEvents(uiCounter, counter.events);
	            }

	            var counterInner = function () {
	              var currentInner = uiCounter.querySelector('.ui-counter-inner');

	              if (BX.Type.isDomNode(currentInner)) {
	                return currentInner;
	              }

	              var newInner = BX.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<span class=\"ui-counter-inner\"></span>\n\t\t\t\t\t\t\t"])));
	              BX.Dom.append(newInner, uiCounter);
	              return newInner;
	            }();

	            if (BX.Type.isStringFilled(counter.type)) {
	              Object.values(BX.Grid.Counters.Type).forEach(function (type) {
	                BX.Dom.removeClass(counterContainer, "main-grid-cell-counter-".concat(type));
	              });
	              BX.Dom.addClass(counterContainer, "main-grid-cell-counter-".concat(counter.type));
	            }

	            if (BX.Type.isStringFilled(counter.color)) {
	              Object.values(BX.Grid.Counters.Color).forEach(function (color) {
	                BX.Dom.removeClass(uiCounter, color);
	              });
	              BX.Dom.addClass(uiCounter, counter.color);
	            }

	            if (BX.Type.isStringFilled(counter.size)) {
	              Object.values(BX.Grid.Counters.Size).forEach(function (size) {
	                BX.Dom.removeClass(uiCounter, size);
	              });
	              BX.Dom.addClass(uiCounter, counter.size);
	            }

	            if (BX.Type.isStringFilled(counter["class"])) {
	              BX.Dom.addClass(uiCounter, counter["class"]);
	            }

	            if (BX.Type.isStringFilled(counter.value) || BX.Type.isNumber(counter.value)) {
	              var currentValue = BX.Text.toNumber(counterInner.innerText);
	              var value = BX.Text.toNumber(counter.value);

	              if (value > 0) {
	                if (value < 100) {
	                  counterInner.innerText = counter.value;
	                } else {
	                  counterInner.innerText = '99+';
	                }

	                if (counter.animation !== false) {
	                  if (value !== currentValue) {
	                    if (value > currentValue) {
	                      BX.Dom.addClass(counterInner, 'ui-counter-plus');
	                    } else {
	                      BX.Dom.addClass(counterInner, 'ui-counter-minus');
	                    }
	                  }

	                  BX.Event.bindOnce(counterInner, 'animationend', function (event) {
	                    if (event.animationName === 'uiCounterPlus' || event.animationName === 'uiCounterMinus') {
	                      BX.Dom.removeClass(counterInner, ['ui-counter-plus', 'ui-counter-minus']);
	                    }
	                  });
	                }
	              }
	            }

	            if (BX.Text.toNumber(counter.value) > 0) {
	              var align = counter.type === BX.Grid.Counters.Type.RIGHT ? 'right' : 'left';

	              if (align === 'left') {
	                BX.Dom.prepend(counterContainer, cellInner);
	              } else if (align === 'right') {
	                BX.Dom.append(counterContainer, cellInner);
	              }
	            } else {
	              var leftAlignedClass = "main-grid-cell-counter-".concat(BX.Grid.Counters.Type.LEFT_ALIGNED);

	              if (BX.Dom.hasClass(counterContainer, leftAlignedClass)) {
	                BX.remove(uiCounter);
	              } else {
	                BX.remove(counterContainer);
	              }
	            }
	          }
	        });
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.Rows
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.Rows = function (parent) {
	    this.parent = null;
	    this.rows = null;
	    this.headChild = null;
	    this.bodyChild = null;
	    this.footChild = null;
	    this.init(parent);
	  };

	  BX.Grid.Rows.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	    },
	    reset: function reset() {
	      this.rows = null;
	      this.headChild = null;
	      this.bodyChild = null;
	      this.footChild = null;
	    },
	    enableDragAndDrop: function enableDragAndDrop() {
	      this.parent.arParams["ALLOW_ROWS_SORT"] = true;

	      if (!(this.parent.getRowsSortable() instanceof BX.Grid.RowsSortable)) {
	        this.parent.rowsSortable = new BX.Grid.RowsSortable(this.parent);
	      }
	    },
	    disableDragAndDrop: function disableDragAndDrop() {
	      this.parent.arParams["ALLOW_ROWS_SORT"] = false;

	      if (this.parent.getRowsSortable() instanceof BX.Grid.RowsSortable) {
	        this.parent.getRowsSortable().destroy();
	        this.parent.rowsSortable = null;
	      }
	    },
	    getFootLastChild: function getFootLastChild() {
	      return this.getLast(this.getFootChild());
	    },
	    getFootFirstChild: function getFootFirstChild() {
	      return this.getFirst(this.getFootChild());
	    },
	    getBodyLastChild: function getBodyLastChild() {
	      return this.getLast(this.getBodyChild());
	    },
	    getBodyFirstChild: function getBodyFirstChild() {
	      return this.getFirst(this.getBodyChild());
	    },
	    getHeadLastChild: function getHeadLastChild() {
	      return this.getLast(this.getHeadChild());
	    },
	    getHeadFirstChild: function getHeadFirstChild() {
	      return this.getFirst(this.getHeadChild());
	    },
	    getEditSelectedValues: function getEditSelectedValues(withTemplate) {
	      var selectedRows = this.getSelected(withTemplate);
	      var values = {};
	      selectedRows.forEach(function (current) {
	        values[current.getId()] = current.editGetValues();
	      });
	      return values;
	    },
	    getSelectedIds: function getSelectedIds(withTemplate) {
	      return this.getSelected(withTemplate).map(function (current) {
	        return current.getId();
	      });
	    },
	    initSelected: function initSelected() {
	      var selected = this.getSelected();

	      if (BX.type.isArray(selected) && selected.length) {
	        selected.forEach(function (row) {
	          row.initSelect();
	        });
	        this.parent.enableActionsPanel();
	      }
	    },
	    editSelected: function editSelected() {
	      this.getSelected().forEach(function (current) {
	        current.edit();
	      });
	      BX.onCustomEvent(window, 'Grid::thereEditedRows', []);
	    },
	    editSelectedCancel: function editSelectedCancel(withTemplate) {
	      this.getSelected(withTemplate).forEach(function (current) {
	        current.editCancel();
	      });
	      BX.onCustomEvent(window, 'Grid::noEditedRows', []);
	    },
	    isSelected: function isSelected() {
	      return this.getBodyChild().some(function (current) {
	        return current.isShown() && current.isSelected();
	      });
	    },
	    isAllSelected: function isAllSelected() {
	      return !this.getBodyChild().filter(function (current) {
	        return !!current.getCheckbox();
	      }).some(function (current) {
	        return !current.isSelected();
	      });
	    },
	    getParent: function getParent() {
	      return this.parent;
	    },
	    getCountSelected: function getCountSelected() {
	      var result;

	      try {
	        result = this.getSelected().filter(function (row) {
	          return !row.isNotCount() && row.isShown();
	        }).length;
	      } catch (err) {
	        result = 0;
	      }

	      return result;
	    },
	    getCountDisplayed: function getCountDisplayed() {
	      var result;

	      try {
	        result = this.getBodyChild().filter(function (row) {
	          return row.isShown() && !row.isNotCount();
	        }).length;
	      } catch (err) {
	        result = 0;
	      }

	      return result;
	    },
	    addRows: function addRows(rows) {
	      var body = BX.findChild(this.getParent().getTable(), {
	        tag: 'TBODY'
	      }, true, false);
	      rows.forEach(function (current) {
	        body.appendChild(current);
	      });
	    },

	    /**
	     * Gets all rows of table
	     * @return {BX.Grid.Row[]}
	     */
	    getRows: function getRows() {
	      var result;
	      var self = this;

	      if (!this.rows) {
	        result = [].slice.call(this.getParent().getTable().querySelectorAll('tr[data-id], thead > tr'));
	        this.rows = result.map(function (current) {
	          return new BX.Grid.Row(self.parent, current);
	        });
	      }

	      return this.rows;
	    },

	    /**
	     * Gets selected rows
	     * @return {BX.Grid.Row[]}
	     */
	    getSelected: function getSelected(withTemplate) {
	      return this.getBodyChild(withTemplate).filter(function (current) {
	        return current.isShown() && current.isSelected();
	      });
	    },
	    normalizeNode: function normalizeNode(node) {
	      if (!BX.hasClass(node, this.getParent().settings.get('classBodyRow'))) {
	        node = BX.findParent(node, {
	          className: this.getParent().settings.get('classBodyRow')
	        }, true, false);
	      }

	      return node;
	    },

	    /**
	     * Gets BX.Grid.Row by id
	     * @param {string|number} id
	     * @return {?BX.Grid.Row}
	     */
	    getById: function getById(id) {
	      return this.getBodyChild().find(function (current) {
	        return String(current.getId()) === String(id);
	      }) || null;
	    },

	    /**
	     * Gets BX.Grid.Row for tr node
	     * @param {HTMLTableRowElement} node
	     * @return {?BX.Grid.Row}
	     */
	    get: function get(node) {
	      if (BX.Type.isDomNode(node)) {
	        var rowNode = node.closest('.main-grid-row');

	        if (BX.Type.isDomNode(rowNode)) {
	          var rowInstance = this.getRows().find(function (row) {
	            return row.getNode() === rowNode;
	          });

	          if (rowInstance) {
	            return rowInstance;
	          }
	        }
	      }

	      return null;
	    },

	    /** @static @method getLast */
	    getLast: function getLast(array) {
	      var result;

	      try {
	        result = array[array.length - 1];
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    },

	    /** @static @method getFirst */
	    getFirst: function getFirst(array) {
	      var result;

	      try {
	        result = array[0];
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    },
	    getHeadChild: function getHeadChild() {
	      this.headChild = this.headChild || this.getRows().filter(function (current) {
	        return current.isHeadChild();
	      });
	      return this.headChild;
	    },

	    /**
	     * Gets child rows of tbody
	     * @return {BX.Grid.Row[]}
	     */
	    getBodyChild: function getBodyChild(withTemplates) {
	      return this.getRows().filter(function (current) {
	        return current.isBodyChild() && (!current.isTemplate() || withTemplates);
	      });
	    },
	    getFootChild: function getFootChild() {
	      this.footChild = this.footChild || this.getRows().filter(function (current) {
	        return current.isFootChild();
	      });
	      return this.footChild;
	    },
	    selectAll: function selectAll() {
	      this.getRows().map(function (current) {
	        current.isShown() && current.select();
	      });
	    },
	    unselectAll: function unselectAll() {
	      this.getRows().map(function (current) {
	        current.unselect();
	      });
	    },

	    /**
	     * Gets row by rowIndex
	     * @param {number} rowIndex
	     * @return {?BX.Grid.Row}
	     */
	    getByIndex: function getByIndex(rowIndex) {
	      var filter = this.getBodyChild().filter(function (item) {
	        return item;
	      }).filter(function (item) {
	        return item.getNode().rowIndex === rowIndex;
	      });
	      return filter.length ? filter[0] : null;
	    },

	    /**
	     * Gets child rows
	     * @param {number|string} parentId
	     * @param {boolean} [recursive]
	     * @return {BX.Grid.Row[]}
	     */
	    getRowsByParentId: function getRowsByParentId(parentId, recursive) {
	      var result = [];
	      var self = this;

	      if (!parentId) {
	        return result;
	      }

	      parentId = parentId.toString();

	      function getByParentId(parentId) {
	        self.getBodyChild().forEach(function (row) {
	          if (row.getParentId() === parentId) {
	            result.push(row);
	            recursive && getByParentId(row.getId());
	          }
	        }, self);
	      }

	      getByParentId(parentId);
	      return result;
	    },
	    getRowsByGroupId: function getRowsByGroupId(groupId) {
	      var result = [];
	      var self = this;

	      if (!groupId) {
	        return result;
	      }

	      groupId = groupId.toString();

	      function getByParentId(groupId) {
	        self.getBodyChild().forEach(function (row) {
	          if (row.getGroupId() === groupId && !row.isCustom()) {
	            result.push(row);
	          }
	        }, self);
	      }

	      getByParentId(groupId);
	      return result;
	    },
	    getExpandedRows: function getExpandedRows() {
	      return this.getRows().filter(function (row) {
	        return row.isShown() && row.isExpand();
	      });
	    },
	    getIdsExpandedRows: function getIdsExpandedRows() {
	      return this.getExpandedRows().map(function (row) {
	        return row.getId();
	      });
	    },
	    getIdsCollapsedGroups: function getIdsCollapsedGroups() {
	      return this.getRows().filter(function (row) {
	        return row.isCustom() && !row.isExpand();
	      }).map(function (row) {
	        return row.getId();
	      });
	    },

	    /**
	     * @return {HTMLElement[]}
	     */
	    getSourceRows: function getSourceRows() {
	      return BX.Grid.Utils.getBySelector(this.getParent().getTable(), ['.main-grid-header > tr', '.main-grid-header + tbody > tr'].join(', '));
	    },

	    /**
	     * @return {HTMLElement[]}
	     */
	    getSourceBodyChild: function getSourceBodyChild() {
	      return this.getSourceRows().filter(function (current) {
	        return BX.Grid.Utils.closestParent(current).nodeName === 'TBODY';
	      });
	    },

	    /**
	     * @return {HTMLElement[]}
	     */
	    getSourceHeadChild: function getSourceHeadChild() {
	      return this.getSourceRows().filter(function (current) {
	        return BX.Grid.Utils.closestParent(current).nodeName === 'THEAD';
	      });
	    },

	    /**
	     * @return {HTMLElement[]}
	     */
	    getSourceFootChild: function getSourceFootChild() {
	      return this.getSourceRows().filter(function (current) {
	        return BX.Grid.Utils.closestParent(current).nodeName === 'TFOOT';
	      });
	    },
	    hasEditable: function hasEditable() {
	      return this.getBodyChild().some(function (current) {
	        return current.isEdit();
	      });
	    },
	    insertAfter: function insertAfter(currentId, targetId) {
	      var currentRow = this.getById(currentId);
	      var targetRow = this.getById(targetId);

	      if (currentRow && targetRow) {
	        BX.Dom.insertAfter(currentRow.getNode(), targetRow.getNode());
	        this.reset();
	      }
	    },
	    insertBefore: function insertBefore(currentId, targetId) {
	      var currentRow = this.getById(currentId);
	      var targetRow = this.getById(targetId);

	      if (currentRow && targetRow) {
	        BX.Dom.insertBefore(currentRow.getNode(), targetRow.getNode());
	        this.reset();
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');

	  BX.Grid.RowDragEvent = function (eventName) {
	    this.allowMoveRow = true;
	    this.allowInsertBeforeTarget = true;
	    this.dragItem = null;
	    this.targetItem = null;
	    this.eventName = !!eventName ? eventName : '';
	    this.errorMessage = '';
	  };

	  BX.Grid.RowDragEvent.prototype = {
	    allowMove: function allowMove() {
	      this.allowMoveRow = true;
	      this.errorMessage = '';
	    },
	    allowInsertBefore: function allowInsertBefore() {
	      this.allowInsertBeforeTarget = true;
	    },
	    disallowMove: function disallowMove(errorMessage) {
	      this.allowMoveRow = false;
	      this.errorMessage = errorMessage || '';
	    },
	    disallowInsertBefore: function disallowInsertBefore() {
	      this.allowInsertBeforeTarget = false;
	    },
	    getDragItem: function getDragItem() {
	      return this.dragItem;
	    },
	    getTargetItem: function getTargetItem() {
	      return this.targetItem;
	    },
	    getEventName: function getEventName() {
	      return this.eventName;
	    },
	    setDragItem: function setDragItem(item) {
	      return this.dragItem = item;
	    },
	    setTargetItem: function setTargetItem(item) {
	      return this.targetItem = item;
	    },
	    setEventName: function setEventName(name) {
	      return this.eventName = name;
	    },
	    isAllowedMove: function isAllowedMove() {
	      return this.allowMoveRow;
	    },
	    isAllowedInsertBefore: function isAllowedInsertBefore() {
	      return this.allowInsertBeforeTarget;
	    },
	    getErrorMessage: function getErrorMessage() {
	      return this.errorMessage;
	    }
	  };

	  BX.Grid.RowsSortable = function (parent) {
	    this.parent = null;
	    this.list = null;
	    this.setDefaultProps();
	    this.init(parent);
	  };

	  BX.Grid.RowsSortable.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      this.list = this.getList();
	      this.prepareListItems();
	      jsDD.Enable();

	      if (!this.inited) {
	        this.inited = true;
	        this.onscrollDebounceHandler = BX.debounce(this._onWindowScroll, 300, this);

	        if (!this.parent.getParam('ALLOW_ROWS_SORT_IN_EDIT_MODE', false)) {
	          BX.addCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
	          BX.addCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
	        }

	        document.addEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({
	          passive: true
	        }));
	      }
	    },
	    destroy: function destroy() {
	      if (!this.parent.getParam('ALLOW_ROWS_SORT_IN_EDIT_MODE', false)) {
	        BX.removeCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
	        BX.removeCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
	      }

	      document.removeEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({
	        passive: true
	      }));
	      this.unregisterObjects();
	    },
	    _onWindowScroll: function _onWindowScroll() {
	      this.windowScrollTop = BX.scrollTop(window);
	      this.rowsRectList = null;
	    },
	    disable: function disable() {
	      this.unregisterObjects();
	    },
	    enable: function enable() {
	      this.reinit();
	    },
	    reinit: function reinit() {
	      this.unregisterObjects();
	      this.setDefaultProps();
	      this.init(this.parent);
	    },
	    getList: function getList() {
	      return this.parent.getRows().getSourceBodyChild();
	    },
	    unregisterObjects: function unregisterObjects() {
	      this.list.forEach(this.unregister, this);
	    },
	    prepareListItems: function prepareListItems() {
	      this.list.forEach(this.register, this);
	    },
	    register: function register(row) {
	      var Rows = this.parent.getRows();
	      var rowInstance = Rows.get(row);

	      if (rowInstance && rowInstance.isDraggable()) {
	        row.onbxdragstart = BX.delegate(this._onDragStart, this);
	        row.onbxdrag = BX.delegate(this._onDrag, this);
	        row.onbxdragstop = BX.delegate(this._onDragEnd, this);
	        jsDD.registerObject(row);
	      }
	    },
	    unregister: function unregister(row) {
	      jsDD.unregisterObject(row);
	    },
	    getIndex: function getIndex(item) {
	      return BX.Grid.Utils.getIndex(this.list, item);
	    },
	    calcOffset: function calcOffset() {
	      var offset = this.dragRect.height;

	      if (this.additionalDragItems.length) {
	        this.additionalDragItems.forEach(function (row) {
	          offset += row.clientHeight;
	        });
	      }

	      return offset;
	    },
	    getTheadCells: function getTheadCells(sourceCells) {
	      return [].map.call(sourceCells, function (cell, index) {
	        return {
	          block: '',
	          tag: 'th',
	          attrs: {
	            style: 'width: ' + BX.width(sourceCells[index]) + 'px;'
	          }
	        };
	      });
	    },
	    createFake: function createFake() {
	      var content = [];
	      this.cloneDragItem = BX.clone(this.dragItem);
	      this.cloneDragAdditionalDragItems = [];
	      this.cloneDragAdditionalDragItemRows = [];
	      var theadCellsDecl = this.getTheadCells(this.dragItem.cells);
	      content.push(this.cloneDragItem);
	      this.additionalDragItems.forEach(function (row) {
	        var cloneRow = BX.clone(row);
	        content.push(cloneRow);
	        this.cloneDragAdditionalDragItems.push(cloneRow);
	        this.cloneDragAdditionalDragItemRows.push(new BX.Grid.Row(this.parent, cloneRow));
	      }, this);
	      var tableWidth = BX.width(this.parent.getTable());
	      this.fake = BX.decl({
	        block: 'main-grid-fake-container',
	        attrs: {
	          style: 'position: absolute; top: ' + this.getDragStartRect().top + 'px; width: ' + tableWidth + 'px'
	        },
	        content: {
	          block: 'main-grid-table',
	          mix: 'main-grid-table-fake',
	          tag: 'table',
	          attrs: {
	            style: 'width: ' + tableWidth + 'px'
	          },
	          content: [{
	            block: 'main-grid-header',
	            tag: 'thead',
	            content: {
	              block: 'main-grid-row-head',
	              tag: 'tr',
	              content: theadCellsDecl
	            }
	          }, {
	            block: '',
	            tag: 'tbody',
	            content: content
	          }]
	        }
	      });
	      BX.insertAfter(this.fake, this.parent.getTable());
	      this.cloneDragItem = new BX.Grid.Row(this.parent, this.cloneDragItem);
	      return this.fake;
	    },
	    getDragStartRect: function getDragStartRect() {
	      return BX.pos(this.dragItem, this.parent.getTable());
	    },
	    _onDragStart: function _onDragStart() {
	      this.moved = false;
	      this.dragItem = jsDD.current_node;
	      this.targetItem = this.dragItem;
	      this.additionalDragItems = this.getAdditionalDragItems(this.dragItem);
	      this.dragIndex = this.getIndex(this.dragItem);
	      this.dragRect = this.getRowRect(this.dragItem, this.dragIndex);
	      this.offset = this.calcOffset();
	      this.dragStartOffset = jsDD.start_y - this.dragRect.top;
	      this.dragEvent = new BX.Grid.RowDragEvent();
	      this.dragEvent.setEventName('BX.Main.grid:rowDragStart');
	      this.dragEvent.setDragItem(this.dragItem);
	      this.dragEvent.setTargetItem(this.targetItem);
	      this.dragEvent.allowInsertBefore();
	      var dragRow = this.parent.getRows().get(this.dragItem);
	      this.startDragDepth = dragRow.getDepth();
	      this.startDragParentId = dragRow.getParentId();
	      this.createFake();
	      BX.addClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
	      BX.addClass(this.dragItem, this.parent.settings.get('classDragActive'));
	      BX.onCustomEvent(window, 'BX.Main.grid:rowDragStart', [this.dragEvent, this.parent]);
	    },
	    getAdditionalDragItems: function getAdditionalDragItems(dragItem) {
	      var Rows = this.parent.getRows();
	      return Rows.getRowsByParentId(Rows.get(dragItem).getId(), true).map(function (row) {
	        return row.getNode();
	      });
	    },

	    /**
	     * @param {?HTMLElement} row
	     * @param {int} offset
	     * @param {?int} [transition] css transition-duration in ms
	     */
	    moveRow: function moveRow(row, offset, transition) {
	      if (!!row) {
	        var transitionDuration = BX.type.isNumber(transition) ? transition : 300;
	        row.style.transition = transitionDuration + 'ms';
	        row.style.transform = 'translate3d(0px, ' + offset + 'px, 0px)';
	      }
	    },
	    getDragOffset: function getDragOffset() {
	      return jsDD.y - this.dragRect.top - this.dragStartOffset;
	    },
	    getWindowScrollTop: function getWindowScrollTop() {
	      if (this.windowScrollTop === null) {
	        this.windowScrollTop = BX.scrollTop(window);
	      }

	      return this.windowScrollTop;
	    },
	    getSortOffset: function getSortOffset() {
	      return jsDD.y;
	    },
	    getRowRect: function getRowRect(row, index) {
	      if (!this.rowsRectList) {
	        this.rowsRectList = {};
	        this.list.forEach(function (current, i) {
	          this.rowsRectList[i] = current.getBoundingClientRect();
	        }, this);
	      }

	      return this.rowsRectList[index];
	    },
	    getRowCenter: function getRowCenter(row, index) {
	      var rect = this.getRowRect(row, index);
	      return rect.top + this.getWindowScrollTop() + rect.height / 2;
	    },
	    isDragToBottom: function isDragToBottom(row, index) {
	      var rowCenter = this.getRowCenter(row, index);
	      var sortOffset = this.getSortOffset();
	      return index > this.dragIndex && rowCenter < sortOffset;
	    },
	    isMovedToBottom: function isMovedToBottom(row) {
	      return row.style.transform === 'translate3d(0px, ' + -this.offset + 'px, 0px)';
	    },
	    isDragToTop: function isDragToTop(row, index) {
	      var rowCenter = this.getRowCenter(row, index);
	      var sortOffset = this.getSortOffset();
	      return index < this.dragIndex && rowCenter > sortOffset;
	    },
	    isMovedToTop: function isMovedToTop(row) {
	      return row.style.transform === 'translate3d(0px, ' + this.offset + 'px, 0px)';
	    },
	    isDragToBack: function isDragToBack(row, index) {
	      var rowCenter = this.getRowCenter(row, index);
	      var dragIndex = this.dragIndex;
	      var y = jsDD.y;
	      return index > dragIndex && y < rowCenter || index < dragIndex && y > rowCenter;
	    },
	    isMoved: function isMoved(row) {
	      return row.style.transform !== 'translate3d(0px, 0px, 0px)' && row.style.transform !== '';
	    },
	    _onDrag: function _onDrag() {
	      var dragTransitionDuration = 0;
	      var defaultOffset = 0;
	      this.moveRow(this.dragItem, this.getDragOffset(), dragTransitionDuration);
	      this.moveRow(this.fake, this.getDragOffset(), dragTransitionDuration);
	      BX.Grid.Utils.styleForEach(this.additionalDragItems, {
	        'transition': dragTransitionDuration + 'ms',
	        'transform': 'translate3d(0px, ' + this.getDragOffset() + 'px, 0px)'
	      });
	      this.list.forEach(function (current, index) {
	        if (!!current && current !== this.dragItem && this.additionalDragItems.indexOf(current) === -1) {
	          if (this.isDragToTop(current, index) && !this.isMovedToTop(current)) {
	            this.targetItem = current;
	            this.moveRow(current, this.offset);
	            this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
	            this.dragEvent.setTargetItem(this.targetItem);
	            BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
	            this.checkError(this.dragEvent);
	            this.updateProperties(this.dragItem, this.targetItem);
	            this.isDragetToTop = true;
	            this.moved = true;
	          }

	          if (this.isDragToBottom(current, index) && !this.isMovedToBottom(current)) {
	            this.targetItem = this.findNextVisible(this.list, index);
	            this.moveRow(current, -this.offset);
	            this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
	            this.dragEvent.setTargetItem(this.targetItem);
	            BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
	            this.checkError(this.dragEvent);
	            this.updateProperties(this.dragItem, this.targetItem);
	            this.isDragetToTop = false;

	            if (this.targetItem) {
	              this.moved = true;
	            }
	          }

	          if (this.isDragToBack(current, index) && this.isMoved(current)) {
	            this.moveRow(current, defaultOffset);
	            this.targetItem = current;

	            if (this.isDragetToTop) {
	              this.targetItem = this.findNextVisible(this.list, index);
	            }

	            this.moved = true;
	            this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
	            this.dragEvent.setTargetItem(this.targetItem);
	            BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
	            this.checkError(this.dragEvent);
	            this.updateProperties(this.dragItem, this.targetItem);
	          }
	        }
	      }, this);
	    },
	    createError: function createError(target, message) {
	      var error = BX.decl({
	        block: 'main-grid-error',
	        content: !!message ? message : ''
	      });
	      !!target && target.appendChild(error);
	      setTimeout(function () {
	        BX.addClass(error, 'main-grid-error-show');
	      }, 0);
	      return error;
	    },
	    checkError: function checkError(event) {
	      if (!event.isAllowedMove() && !this.error) {
	        this.error = this.createError(this.fake, event.getErrorMessage());
	      }

	      if (event.isAllowedMove() && this.error) {
	        BX.remove(this.error);
	        this.error = null;
	      }
	    },
	    findNextVisible: function findNextVisible(list, index) {
	      var result = null;
	      var Rows = this.parent.getRows();
	      list.forEach(function (item, currentIndex) {
	        if (!result && currentIndex > index) {
	          var row = Rows.get(item);

	          if (row && row.isShown()) {
	            result = item;
	          }
	        }
	      });
	      return result;
	    },

	    /**
	     * Updates row properties
	     * @param {?HTMLTableRowElement} dragItem
	     * @param {?HTMLTableRowElement} targetItem
	     */
	    updateProperties: function updateProperties(dragItem, targetItem) {
	      var Rows = this.parent.getRows();
	      var dragRow = Rows.get(dragItem);
	      var depth = 0;
	      var parentId = 0;

	      if (!!targetItem) {
	        var targetRow = Rows.get(targetItem);
	        depth = targetRow.getDepth();
	        parentId = targetRow.getParentId();
	      }

	      dragRow.setDepth(depth);
	      dragRow.setParentId(parentId);
	      this.cloneDragItem.setDepth(depth);
	      this.cloneDragAdditionalDragItemRows.forEach(function (row, index) {
	        row.setDepth(BX.data(this.additionalDragItems[index], 'depth'));
	      }, this);
	    },
	    resetDragProperties: function resetDragProperties() {
	      var dragRow = this.parent.getRows().get(this.dragItem);
	      dragRow.setDepth(this.startDragDepth);
	      dragRow.setParentId(this.startDragParentId);
	    },
	    _onDragOver: function _onDragOver() {},
	    _onDragLeave: function _onDragLeave() {},
	    _onDragEnd: function _onDragEnd() {
	      BX.onCustomEvent(window, 'BX.Main.grid:rowDragEnd', [this.dragEvent, this.parent]);
	      BX.removeClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
	      BX.removeClass(this.dragItem, this.parent.settings.get('classDragActive'));
	      BX.Grid.Utils.styleForEach(this.list, {
	        'transition': '',
	        'transform': ''
	      });

	      if (this.dragEvent.isAllowedMove()) {
	        this.sortRows(this.dragItem, this.targetItem);
	        this.sortAdditionalDragItems(this.dragItem, this.additionalDragItems);
	        this.list = this.getList();
	        this.parent.getRows().reset();
	        var dragItem = this.parent.getRows().get(this.dragItem);
	        var ids = this.parent.getRows().getBodyChild().map(function (row) {
	          return row.getId();
	        });

	        if (this.parent.getParam('ALLOW_ROWS_SORT_INSTANT_SAVE', true)) {
	          this.saveRowsSort(ids);
	        }

	        BX.onCustomEvent(window, 'Grid::rowMoved', [ids, dragItem, this.parent]);
	      } else {
	        this.resetDragProperties();
	      }

	      BX.remove(this.fake);
	      this.setDefaultProps();
	    },
	    sortAdditionalDragItems: function sortAdditionalDragItems(dragItem, additional) {
	      additional.reduce(function (prev, current) {
	        !!current && BX.insertAfter(current, prev);
	        return current;
	      }, dragItem);
	    },
	    sortRows: function sortRows(current, target) {
	      if (!!target) {
	        target.parentNode.insertBefore(current, target);
	      } else if (this.moved) {
	        current.parentNode.appendChild(current);
	      }
	    },
	    saveRowsSort: function saveRowsSort(rows) {
	      var data = {
	        ids: rows,
	        action: this.parent.getUserOptions().getAction('GRID_SAVE_ROWS_SORT')
	      };
	      this.parent.getData().request(null, 'POST', data);
	    },
	    setDefaultProps: function setDefaultProps() {
	      this.moved = false;
	      this.dragItem = null;
	      this.targetItem = null;
	      this.dragRect = null;
	      this.dragIndex = null;
	      this.offset = null;
	      this.realX = null;
	      this.realY = null;
	      this.dragStartOffset = null;
	      this.windowScrollTop = null;
	      this.rowsRectList = null;
	      this.error = false;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.Settings
	   * @constructor
	   */

	  BX.Grid.Settings = function () {
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
	      classEditorMoney: 'main-grid-editor-money',
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
	      classSettingsWindowSearchSectionsWrapper: 'main-grid-settings-window-search-section-wrapper',
	      classSettingsWindowSearchActiveSectionIcon: 'main-grid-settings-window-search-section-item-icon-active',
	      classSettingsWindowSearchSectionInput: 'main-grid-settings-window-search-section-input',
	      classSettingsWindowSearchSectionItemHidden: 'main-grid-settings-window-list-item-hidden',
	      classSettingsWindowSearchSectionItemVisible: 'main-grid-settings-window-list-item-visible',
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
	    prepare: function prepare() {
	      this.settings = this.defaultSettings;
	    },
	    getDefault: function getDefault() {
	      return this.defaultSettings;
	    },
	    get: function get(name) {
	      var result;

	      try {
	        result = this.getDefault()[name];
	      } catch (err) {
	        result = null;
	      }

	      return result;
	    },
	    getList: function getList() {
	      return this.getDefault();
	    }
	  };
	})();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * @param {BX.Main.grid} parent
	   * @constructor
	   */

	  BX.Grid.SettingsWindow = function (parent) {
	    this.parent = null;
	    this.popupItems = null;
	    this.items = null;
	    this.popup = null;
	    this.sourceContent = null;
	    this.applyButton = null;
	    this.resetButton = null;
	    this.cancelButton = null;
	    this.filterSections = null;
	    this.filterSectionsSearchInput = null;
	    this.init(parent);
	    BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:init', [this]);
	  };

	  BX.Grid.SettingsWindow.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      BX.bind(this.parent.getContainer(), 'click', BX.proxy(this._onContainerClick, this));
	      BX.addCustomEvent(window, 'Grid::columnMoved', BX.proxy(this._onColumnMoved, this));
	    },
	    destroy: function destroy() {
	      BX.unbind(this.parent.getContainer(), 'click', BX.proxy(this._onContainerClick, this));
	      BX.removeCustomEvent(window, 'Grid::columnMoved', BX.proxy(this._onColumnMoved, this));
	      this.getPopup().close();
	    },

	    /**
	     * Gets select all button
	     * @return {?HTMLElement}
	     */
	    getSelectAllButton: function getSelectAllButton() {
	      if (!this.selectAllButton) {
	        this.selectAllButton = BX.Grid.Utils.getByClass(this.getPopup().contentContainer, this.parent.settings.get('classSettingsWindowSelectAll'), true);
	      }

	      return this.selectAllButton;
	    },

	    /**
	     * Gets unselect all button
	     * @return {?HTMLElement}
	     */
	    getUnselectAllButton: function getUnselectAllButton() {
	      if (!this.unselectAllButton) {
	        this.unselectAllButton = BX.Grid.Utils.getByClass(this.getPopup().contentContainer, this.parent.settings.get('classSettingsWindowUnselectAll'), true);
	      }

	      return this.unselectAllButton;
	    },

	    /**
	     * @private
	     */
	    reset: function reset() {
	      this.popupItems = null;
	      this.allColumns = null;
	      this.items = null;
	    },
	    _onContainerClick: function _onContainerClick(event) {
	      if (BX.hasClass(event.target, this.parent.settings.get('classSettingsButton'))) {
	        this._onSettingsButtonClick(event);
	      }
	    },
	    _onSettingsButtonClick: function _onSettingsButtonClick() {
	      BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:show', [this]);
	      this.getPopup().show();
	    },
	    fetchColumns: function fetchColumns() {
	      var promise = new BX.Promise();
	      BX.ajax({
	        url: this.parent.getParam("LAZY_LOAD")["GET_LIST"],
	        method: "GET",
	        dataType: "json",
	        onsuccess: promise.fulfill.bind(promise)
	      });
	      return promise;
	    },
	    prepareColumnOptions: function prepareColumnOptions(options) {
	      var customNames = this.parent.getUserOptions().getCurrentOptions().custom_names;

	      if (BX.type.isPlainObject(options)) {
	        if (BX.type.isPlainObject(customNames)) {
	          if (options.id in customNames) {
	            options.name = customNames[options.id];
	          }
	        }

	        if (this.parent.getColumnHeaderCellByName(options.id)) {
	          options.selected = true;
	        }
	      }

	      return options;
	    },

	    /**
	     * Creates column element
	     * @param {{id: string, name: string}} options
	     * @return {HTMLElement}
	     */
	    createColumnElement: function createColumnElement(options) {
	      var html = "<div data-name=\"" + options.id + "\" class=\"main-grid-settings-window-list-item\">" + "<input id=\"" + options.id + "-checkbox\" type=\"checkbox\" class=\"main-grid-settings-window-list-item-checkbox\"" + (options.selected ? " checked" : "") + ">" + "<label for=\"" + options.id + "-checkbox\" class=\"main-grid-settings-window-list-item-label\">" + options.name + "</label>" + "<span class=\"main-grid-settings-window-list-item-edit-button\"></span>" + "</div>";
	      return BX.create("div", {
	        html: html
	      }).firstElementChild;
	    },
	    useLazyLoadColumns: function useLazyLoadColumns() {
	      return !!this.parent.getParam("LAZY_LOAD");
	    },

	    /**
	     * @private
	     * @return {?HTMLElement}
	     */
	    getSourceContent: function getSourceContent() {
	      if (!this.sourceContent) {
	        this.sourceContent = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classSettingsWindow'), true);

	        if (this.useLazyLoadColumns()) {
	          // Clear columns list
	          this.contentList = this.sourceContent.querySelector(".main-grid-settings-window-list");
	          this.contentList.innerHTML = ""; // Make and show loader

	          var loader = new BX.Loader({
	            target: this.contentList
	          });
	          loader.show(); // Fetch all columns list

	          this.fetchColumns() // Make list items
	          .then(function (response) {
	            response.forEach(function (columnOptions) {
	              columnOptions = this.prepareColumnOptions(columnOptions);
	              this.contentList.appendChild(this.createColumnElement(columnOptions));
	            }, this); // Remove loader

	            loader.hide().then(function () {
	              loader.destroy();
	            }); // Reset cached items

	            this.reset(); // Init new item

	            this.getItems().forEach(function (item) {
	              BX.bind(item.getNode(), 'click', BX.delegate(this.onItemClick, this));
	            }, this);
	            this.fixedFooter = BX.create("div", {
	              props: {
	                className: "main-grid-popup-window-buttons-wrapper"
	              },
	              children: [this.sourceContent.querySelector(".popup-window-buttons")]
	            });
	            requestAnimationFrame(function () {
	              this.popup.popupContainer.appendChild(this.fixedFooter);
	              this.fixedFooter.style.width = this.popup.popupContainer.clientWidth + "px";
	            }.bind(this));
	          }.bind(this));
	        }
	      }

	      return this.sourceContent;
	    },

	    /**
	     * Gets popup items of columns
	     * @return {?HTMLElement[]}
	     */
	    getPopupItems: function getPopupItems() {
	      var popupContainer;

	      if (!this.popupItems) {
	        popupContainer = this.getPopup().contentContainer;
	        this.popupItems = BX.Grid.Utils.getByClass(popupContainer, this.parent.settings.get('classSettingsWindowColumn'));
	      }

	      return this.popupItems;
	    },

	    /**
	     * Gets selected columns ids
	     * @return {string[]}
	     */
	    getSelectedColumns: function getSelectedColumns() {
	      var columns = [];
	      this.getItems().forEach(function (column) {
	        column.isSelected() && columns.push(column.getId());
	      });
	      return columns;
	    },

	    /**
	     * Restores columns to default state
	     */
	    restoreColumns: function restoreColumns() {
	      this.getItems().forEach(function (column) {
	        column.restore();
	      });
	      this.sortItems();
	      this.reset();
	    },

	    /**
	     * Restores columns to saved state
	     */
	    restoreLastColumns: function restoreLastColumns() {
	      this.getItems().forEach(function (current) {
	        current.restoreState();
	      });
	    },

	    /**
	     * Updates columns state
	     */
	    updateColumnsState: function updateColumnsState() {
	      this.getItems().forEach(function (current) {
	        current.updateState();
	      });
	    },
	    getStickedColumns: function getStickedColumns() {
	      return this.getItems().reduce(function (accumulator, item) {
	        if (item.isSticked()) {
	          accumulator.push(item.getId());
	        }

	        return accumulator;
	      }, []);
	    },

	    /**
	     * Saves columns settings
	     * @param {string[]} columns - ids
	     * @param {?function} callback
	     */
	    saveColumns: function saveColumns(columns, callback) {
	      var options = this.parent.getUserOptions();
	      var columnNames = this.getColumnNames();
	      var stickedColumns = this.getStickedColumns();
	      var batch = [];
	      batch.push({
	        action: options.getAction('GRID_SET_COLUMNS'),
	        columns: columns.join(',')
	      });
	      batch.push({
	        action: options.getAction('SET_CUSTOM_NAMES'),
	        custom_names: columnNames
	      });
	      batch.push({
	        action: options.getAction('GRID_SET_STICKED_COLUMNS'),
	        stickedColumns: stickedColumns
	      });

	      if (this.isForAll()) {
	        batch.push({
	          action: options.getAction('GRID_SAVE_SETTINGS'),
	          view_id: 'default',
	          set_default_settings: 'Y',
	          delete_user_settings: 'Y'
	        });
	      }

	      options.batch(batch, BX.delegate(function () {
	        this.parent.reloadTable(null, null, callback);
	      }, this));
	      this.updateColumnsState();
	    },

	    /**
	     * Disables edit for all columns
	     */
	    disableAllColumnslabelEdit: function disableAllColumnslabelEdit() {
	      this.getItems().forEach(function (column) {
	        column.disableEdit();
	      });
	    },

	    /**
	     * Gets all columns ids
	     * @return {string[]}
	     */
	    getAllColumns: function getAllColumns() {
	      if (!this.allColumns) {
	        this.allColumns = this.getItems().map(function (column) {
	          return column.getId();
	        });
	      }

	      return this.allColumns;
	    },
	    isShowedColumn: function isShowedColumn(columnName) {
	      return this.getSelectedColumns().some(function (name) {
	        return name === columnName;
	      });
	    },
	    getShowedColumns: function getShowedColumns() {
	      var result = [];
	      var cells = this.parent.getRows().getHeadFirstChild().getCells();
	      [].slice.call(cells).forEach(function (column) {
	        if ("name" in column.dataset) {
	          result.push(column.dataset.name);
	        }
	      });
	      return result;
	    },
	    sortItems: function sortItems() {
	      var showedColumns = this.getShowedColumns();
	      var allColumns = {};
	      this.getAllColumns().forEach(function (name) {
	        allColumns[name] = name;
	      }, this);
	      var counter = 0;
	      Object.keys(allColumns).forEach(function (name) {
	        if (this.isShowedColumn(name)) {
	          allColumns[name] = showedColumns[counter];
	          counter++;
	        }

	        var current = this.getColumnByName(allColumns[name]);
	        current && current.parentNode.appendChild(current);
	      }, this);
	    },

	    /**
	     * Gets current columns names
	     * @return {object}
	     */
	    getColumnNames: function getColumnNames() {
	      var names = {};
	      this.getItems().map(function (column) {
	        if (column.isEdited()) {
	          names[column.getId()] = column.getTitle();
	        }
	      });
	      return names;
	    },

	    /**
	     * Gets column node by name
	     * @param {string} name
	     * @return {?HTMLElement}
	     */
	    getColumnByName: function getColumnByName(name) {
	      return BX.Grid.Utils.getBySelector(this.getPopup().popupContainer, '.' + this.parent.settings.get('classSettingsWindowColumn') + '[data-name="' + name + '"]', true);
	    },
	    _onColumnMoved: function _onColumnMoved() {
	      this.sortItems();
	      this.reset();
	    },
	    onResetButtonClick: function onResetButtonClick() {
	      this.parent.confirmDialog({
	        CONFIRM: true,
	        CONFIRM_MESSAGE: this.parent.arParams.CONFIRM_RESET_MESSAGE
	      }, BX.delegate(function () {
	        this.enableWait(this.getApplyButton());
	        this.parent.getUserOptions().reset(this.isForAll(), BX.delegate(function () {
	          this.parent.reloadTable(null, null, BX.delegate(function () {
	            this.restoreColumns();
	            this.disableWait(this.getApplyButton());
	            this.getPopup().close();
	          }, this));
	        }, this));
	      }, this));
	    },

	    /**
	     * Gets reset button id
	     * @return {string}
	     */
	    getResetButtonId: function getResetButtonId() {
	      return this.parent.getContainerId() + '-grid-settings-reset-button';
	    },
	    onApplyButtonClick: function onApplyButtonClick() {
	      this.parent.confirmDialog({
	        CONFIRM: this.isForAll(),
	        CONFIRM_MESSAGE: this.parent.getParam('SETTINGS_FOR_ALL_CONFIRM_MESSAGE')
	      }, BX.delegate(function () {
	        this.enableWait(this.getApplyButton());
	        this.saveColumns(this.getSelectedColumns(), BX.delegate(function () {
	          this.getPopup().close();
	          this.disableWait(this.getApplyButton());
	          this.unselectForAllCheckbox();
	        }, this));
	        BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:save', [this]);
	      }, this), BX.delegate(function () {
	        this.unselectForAllCheckbox();
	      }, this));
	    },

	    /**
	     * Gets apply button id
	     * @return {string}
	     */
	    getApplyButtonId: function getApplyButtonId() {
	      return this.parent.getContainerId() + '-grid-settings-apply-button';
	    },

	    /**
	     * Gets apply button
	     * @return {HTMLElement}
	     */
	    getApplyButton: function getApplyButton() {
	      if (this.applyButton === null) {
	        this.applyButton = BX(this.getApplyButtonId());
	      }

	      return this.applyButton;
	    },

	    /**
	     * Gets cancel button id
	     * @return {string}
	     */
	    getCancelButtonId: function getCancelButtonId() {
	      return this.parent.getContainerId() + '-grid-settings-cancel-button';
	    },

	    /**
	     * Gets cancel button
	     * @return {HTMLElement}
	     */
	    getCancelButton: function getCancelButton() {
	      if (this.cancelButton === null) {
	        this.cancelButton = BX(this.getCancelButtonId());
	      }

	      return this.cancelButton;
	    },

	    /**
	     * Unselect for all checkbox
	     */
	    unselectForAllCheckbox: function unselectForAllCheckbox() {
	      var checkbox = this.getForAllCheckbox();
	      checkbox && (checkbox.checked = null);
	    },

	    /**
	     * Enables wait animation for button
	     * @param {HTMLElement} buttonNode
	     */
	    enableWait: function enableWait(buttonNode) {
	      BX.addClass(buttonNode, 'ui-btn-wait');
	      BX.removeClass(buttonNode, 'popup-window-button');
	    },

	    /**
	     * Disables wait animation for button
	     * @param {HTMLElement} buttonNode
	     */
	    disableWait: function disableWait(buttonNode) {
	      BX.removeClass(buttonNode, 'ui-btn-wait');
	      BX.addClass(buttonNode, 'popup-window-button');
	    },

	    /**
	     * Creates title of settings popup window
	     * @return {string}
	     */
	    createTitle: function createTitle() {
	      var tmpDiv = BX.create('div');
	      var customSettingsTitle = this.parent.getParam('SETTINGS_WINDOW_TITLE');

	      if (customSettingsTitle !== "") {
	        customSettingsTitle = '&laquo;' + customSettingsTitle + '&raquo;';
	        tmpDiv.innerHTML = '<span>' + this.parent.getParam('SETTINGS_TITLE') + ' ' + customSettingsTitle + '</span>';
	        return tmpDiv.firstChild.innerText;
	      }

	      var gridsCount = BX.Main.gridManager.data.length;

	      if (gridsCount === 1) {
	        var pageTitleNode = BX('pagetitle');
	        var pageTitle = BX.Type.isDomNode(pageTitleNode) && BX.Type.isStringFilled(pageTitleNode.innerText) ? '&laquo;' + BX.Text.encode(pageTitleNode.innerText) + '&raquo;' : '';
	        tmpDiv.innerHTML = '<span>' + this.parent.getParam('SETTINGS_TITLE') + ' ' + pageTitle + '</span>';
	        return tmpDiv.firstChild.innerText;
	      }

	      return this.parent.getParam('SETTINGS_TITLE');
	    },

	    /**
	     * Gets popup id
	     * @return {string}
	     */
	    getPopupId: function getPopupId() {
	      return this.parent.getContainerId() + '-grid-settings-window';
	    },

	    /**
	     * Creates grid settings popup window
	     * @return {BX.PopupWindow}
	     */
	    createPopup: function createPopup() {
	      if (!this.popup) {
	        this.popup = new BX.PopupWindow(this.getPopupId(), null, {
	          titleBar: this.createTitle(),
	          autoHide: false,
	          overlay: 0.6,
	          width: 1000,
	          closeIcon: true,
	          closeByEsc: true,
	          contentNoPaddings: true,
	          content: this.getSourceContent(),
	          events: {
	            onPopupClose: BX.delegate(this.onPopupClose, this)
	          }
	        });
	        this.getItems().forEach(function (item) {
	          BX.bind(item.getNode(), 'click', BX.delegate(this.onItemClick, this));
	          BX.bind(item.getNode(), 'animationend', this.onAnimationEnd.bind(this, item.getNode()));
	        }, this);
	        BX.bind(this.getResetButton(), 'click', BX.proxy(this.onResetButtonClick, this));
	        BX.bind(this.getApplyButton(), 'click', BX.proxy(this.onApplyButtonClick, this));
	        BX.bind(this.getCancelButton(), 'click', BX.proxy(this.popup.close, this.popup));
	        BX.bind(this.getSelectAllButton(), 'click', BX.delegate(this.onSelectAll, this));
	        BX.bind(this.getUnselectAllButton(), 'click', BX.delegate(this.onUnselectAll, this));

	        if (this.parent.arParams['COLUMNS_ALL_WITH_SECTIONS'] && Object.keys(this.parent.arParams['COLUMNS_ALL_WITH_SECTIONS']).length) {
	          this.prepareFilterSections();
	        }

	        if (this.parent.arParams['ENABLE_FIELDS_SEARCH']) {
	          this.prepareFilterSectionsSearchInput();
	        }
	      }

	      return this.popup;
	    },
	    onItemClick: function onItemClick() {
	      this.adjustActionButtonsState();
	    },
	    onAnimationEnd: function onAnimationEnd(node) {
	      node.style.display = BX.Dom.hasClass(node, this.parent.settings.get('classSettingsWindowSearchSectionItemHidden')) ? 'none' : 'inline-block';
	    },
	    prepareFilterSections: function prepareFilterSections() {
	      var filterSections = this.getFilterSections();

	      var _iterator = _createForOfIteratorHelper(filterSections),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var item = _step.value;
	          BX.bind(item, 'click', this.onFilterSectionClick.bind(this, item));
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    },

	    /**
	     * Gets all filter section items
	     * @return {HTMLCollection}
	     */
	    getFilterSections: function getFilterSections() {
	      if (!this.filterSections) {
	        var _wrapper$children;

	        var wrapper = BX.Grid.Utils.getByClass(this.getPopup().contentContainer, this.parent.settings.get('classSettingsWindowSearchSectionsWrapper'), true);
	        this.filterSections = (_wrapper$children = wrapper.children) !== null && _wrapper$children !== void 0 ? _wrapper$children : new HTMLCollection();
	      }

	      return this.filterSections;
	    },
	    onFilterSectionClick: function onFilterSectionClick(item) {
	      var activeClass = this.parent.settings.get('classSettingsWindowSearchActiveSectionIcon');
	      var sectionId = item.dataset.uiGridFilterSectionButton;
	      var section = document.querySelectorAll("[data-ui-grid-filter-section='" + sectionId + "']");

	      if (BX.Dom.hasClass(item.firstChild, activeClass)) {
	        BX.Dom.removeClass(item.firstChild, activeClass);
	        BX.Dom.hide(section[0]);
	      } else {
	        BX.Dom.addClass(item.firstChild, activeClass);
	        BX.Dom.show(section[0]);
	      }
	    },
	    prepareFilterSectionsSearchInput: function prepareFilterSectionsSearchInput() {
	      var input = this.getFilterSectionsSearchInput();
	      BX.bind(input, 'input', this.onFilterSectionSearchInput.bind(this));
	      BX.bind(input.previousElementSibling, 'click', this.onFilterSectionSearchInputClear.bind(this));
	    },
	    getFilterSectionsSearchInput: function getFilterSectionsSearchInput() {
	      if (!this.filterSectionsSearchInput) {
	        this.filterSectionsSearchInput = BX.Grid.Utils.getByClass(this.getPopup().contentContainer, this.parent.settings.get('classSettingsWindowSearchSectionInput'), true);
	      }

	      return this.filterSectionsSearchInput;
	    },
	    onFilterSectionSearchInput: function onFilterSectionSearchInput() {
	      var search = this.filterSectionsSearchInput.value;

	      if (search.length) {
	        search = search.toLowerCase();
	      }

	      this.items.forEach(function (item) {
	        var title = item.lastTitle.toLowerCase();

	        if (search.length && title.indexOf(search) === -1) {
	          BX.Dom.removeClass(item.getNode(), this.parent.settings.get('classSettingsWindowSearchSectionItemVisible'));
	          BX.Dom.addClass(item.getNode(), this.parent.settings.get('classSettingsWindowSearchSectionItemHidden'));
	        } else {
	          BX.Dom.removeClass(item.getNode(), this.parent.settings.get('classSettingsWindowSearchSectionItemHidden'));
	          BX.Dom.addClass(item.getNode(), this.parent.settings.get('classSettingsWindowSearchSectionItemVisible'));
	          item.getNode().style.display = 'inline-block';
	        }
	      }.bind(this));
	    },
	    onFilterSectionSearchInputClear: function onFilterSectionSearchInputClear() {
	      this.filterSectionsSearchInput.value = '';
	      this.onFilterSectionSearchInput();
	    },

	    /**
	     * Gets columns collection
	     * @return {BX.Grid.SettingsWindowColumn[]}
	     */
	    getItems: function getItems() {
	      if (this.items === null) {
	        this.items = this.getPopupItems().map(function (current) {
	          return new BX.Grid.SettingsWindowColumn(this.parent, current);
	        }, this);
	      }

	      return this.items;
	    },
	    onPopupClose: function onPopupClose() {
	      BX.onCustomEvent(window, 'BX.Grid.SettingsWindow:close', [this]);
	      this.restoreLastColumns();
	      this.disableAllColumnslabelEdit();
	      this.adjustActionButtonsState();
	    },

	    /**
	     * Gets popup window
	     * @return {BX.PopupWindow}
	     */
	    getPopup: function getPopup() {
	      return !!this.popup ? this.popup : this.popup = this.createPopup();
	    },
	    onSelectAll: function onSelectAll() {
	      this.selectAll();
	      this.enableActions();
	    },
	    onUnselectAll: function onUnselectAll() {
	      this.unselectAll();
	      this.disableActions();
	    },

	    /**
	     * Select all columns
	     */
	    selectAll: function selectAll() {
	      this.getItems().forEach(function (column) {
	        column.select();
	      });
	    },

	    /**
	     * Unselect all columns
	     */
	    unselectAll: function unselectAll() {
	      this.getItems().forEach(function (column) {
	        column.unselect();
	      });
	    },
	    isForAll: function isForAll() {
	      var checkbox = this.getForAllCheckbox();
	      return checkbox && !!checkbox.checked;
	    },

	    /**
	     * Gets for all checkbox
	     * @return {?HTMLElement}
	     */
	    getForAllCheckbox: function getForAllCheckbox() {
	      return BX.Grid.Utils.getByClass(this.getPopup().popupContainer, 'main-grid-settings-window-for-all-checkbox', true);
	    },

	    /**
	     * Gets reset button
	     * @return {?HTMLElement}
	     */
	    getResetButton: function getResetButton() {
	      if (this.resetButton === null) {
	        this.resetButton = BX(this.getResetButtonId());
	      }

	      return this.resetButton;
	    },
	    disableActions: function disableActions() {
	      var applyButton = this.getApplyButton();

	      if (!!applyButton) {
	        BX.addClass(applyButton, this.parent.settings.get('classDisable'));
	      }
	    },
	    enableActions: function enableActions() {
	      var applyButton = this.getApplyButton();

	      if (!!applyButton) {
	        BX.removeClass(applyButton, this.parent.settings.get('classDisable'));
	      }
	    },
	    adjustActionButtonsState: function adjustActionButtonsState() {
	      if (this.getSelectedColumns().length) {
	        this.enableActions();
	      } else {
	        this.disableActions();
	      }
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * @param {BX.Main.grid} parent
	   * @param {HTMLElement} node
	   * @constructor
	   */

	  BX.Grid.SettingsWindowColumn = function (parent, node) {
	    this.node = null;
	    this.label = null;
	    this.checkbox = null;
	    this.editButton = null;
	    this.settings = null;
	    this.parent = null;
	    this["default"] = null;
	    this.defaultTitle = null;
	    this.state = null;
	    this.lastTitle = null;
	    this.init(parent, node);
	  };

	  BX.Grid.SettingsWindowColumn.inited = {};
	  BX.Grid.SettingsWindowColumn.prototype = {
	    init: function init(parent, node) {
	      this.parent = parent;
	      this.node = node;

	      try {
	        this.lastTitle = node.querySelector("label").innerText.trim();
	      } catch (err) {}

	      this.updateState();

	      if (!BX.Grid.SettingsWindowColumn.inited[this.getId()]) {
	        BX.Grid.SettingsWindowColumn.inited[this.getId()] = true;
	        BX.bind(this.getEditButton(), 'click', BX.proxy(this.onEditButtonClick, this));
	        BX.bind(this.getStickyButton(), 'click', BX.proxy(this.onStickyButtonClick, this));
	      }
	    },
	    getStickyButton: function getStickyButton() {
	      return this.node.querySelector(".main-grid-settings-window-list-item-sticky-button");
	    },
	    isSticked: function isSticked() {
	      return this.node.classList.contains("main-grid-settings-window-list-item-sticked");
	    },
	    onStickyButtonClick: function onStickyButtonClick() {
	      if (this.isSticked()) {
	        this.unstick();
	      } else {
	        this.stick();
	      }
	    },
	    stick: function stick() {
	      this.node.classList.add("main-grid-settings-window-list-item-sticked");
	    },
	    unstick: function unstick() {
	      this.node.classList.remove("main-grid-settings-window-list-item-sticked");
	    },
	    onEditButtonClick: function onEditButtonClick(event) {
	      event.stopPropagation();
	      this.isEditEnabled() ? this.disableEdit() : this.enableEdit();
	    },

	    /**
	     * @private
	     * @param {object} state
	     * @property {boolean} state.selected
	     * @property {title} state.title
	     */
	    setState: function setState(state) {
	      this.state = state;
	    },

	    /**
	     * Gets state of column
	     * @return {object}
	     */
	    getState: function getState() {
	      return this.state;
	    },

	    /**
	     * Updates default state
	     */
	    updateState: function updateState() {
	      this.setState({
	        selected: this.isSelected(),
	        sticked: this.isSticked(),
	        title: this.getTitle()
	      });
	    },

	    /**
	     * Restores last state
	     */
	    restoreState: function restoreState() {
	      var state = this.getState();
	      state.selected ? this.select() : this.unselect();
	      state.sticked ? this.stick() : this.unstick();
	      this.setTitle(state.title);
	    },

	    /**
	     * Gets column id
	     * @return {string}
	     */
	    getId: function getId() {
	      return this.getNode().dataset.name;
	    },

	    /**
	     * Gets column title
	     * @return {string}
	     */
	    getTitle: function getTitle() {
	      return this.getLabel().innerText;
	    },

	    /**
	     * Sets column title
	     * @param {string} title
	     */
	    setTitle: function setTitle(title) {
	      this.getLabel().innerText = !!title && title !== "undefined" ? title : this.getDefaultTitle();
	    },

	    /**
	     * @return {boolean}
	     */
	    isEdited: function isEdited() {
	      return this.getTitle() !== this.getDefaultTitle();
	    },

	    /**
	     * Gets column settings
	     * @return {?object}
	     */
	    getSettings: function getSettings() {
	      if (this.settings === null) {
	        var columns = this.parent.getParam('DEFAULT_COLUMNS');
	        this.settings = this.getId() in columns ? columns[this.getId()] : {};
	      }

	      return this.settings;
	    },

	    /**
	     * Checks column is default
	     * @return {boolean}
	     */
	    isDefault: function isDefault() {
	      if (this["default"] === null) {
	        var settings = this.getSettings();
	        this["default"] = 'default' in settings ? settings["default"] : false;
	      }

	      return this["default"];
	    },

	    /**
	     * Restore column to default state
	     */
	    restore: function restore() {
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
	    getDefaultTitle: function getDefaultTitle() {
	      if (this.defaultTitle === null) {
	        var settings = this.getSettings();
	        this.defaultTitle = 'name' in settings ? settings.name : this.lastTitle;
	      }

	      return this.defaultTitle;
	    },

	    /**
	     * Gets column node
	     * @return {?HTMLElement}
	     */
	    getNode: function getNode() {
	      return this.node;
	    },

	    /**
	     * Gets column label node
	     * @return {?HTMLLabelElement}
	     */
	    getLabel: function getLabel() {
	      if (this.label === null) {
	        this.label = BX.Grid.Utils.getByTag(this.getNode(), 'label', true);
	      }

	      return this.label;
	    },

	    /**
	     * Gets column checkbox node
	     * @return {?HTMLInputElement}
	     */
	    getCheckbox: function getCheckbox() {
	      if (this.checkbox === null) {
	        this.checkbox = BX.Grid.Utils.getBySelector(this.getNode(), 'input[type="checkbox"]', true);
	      }

	      return this.checkbox;
	    },

	    /**
	     * Gets edit button
	     * @return {?HTMLElement}
	     */
	    getEditButton: function getEditButton() {
	      if (this.editButton === null) {
	        this.editButton = BX.Grid.Utils.getByClass(this.getNode(), this.parent.settings.get('classSettingsWindowColumnEditButton'), true);
	      }

	      return this.editButton;
	    },

	    /**
	     * Enables edit mode
	     */
	    enableEdit: function enableEdit() {
	      this.getLabel().contentEditable = true;
	      this.getCheckbox().disabled = true;
	      this.adjustCaret();
	    },

	    /**
	     * Disables edit mode
	     */
	    disableEdit: function disableEdit() {
	      this.getLabel().contentEditable = false;
	      this.getCheckbox().disabled = false;
	    },

	    /**
	     * Checks is edit enabled
	     * @return {boolean}
	     */
	    isEditEnabled: function isEditEnabled() {
	      return this.getLabel().isContentEditable;
	    },

	    /**
	     * Checks column is active
	     * @return {boolean}
	     */
	    isSelected: function isSelected() {
	      return this.getCheckbox().checked;
	    },

	    /**
	     * Selects column
	     */
	    select: function select() {
	      this.getCheckbox().checked = true;
	    },

	    /**
	     * Unselects column
	     */
	    unselect: function unselect() {
	      this.getCheckbox().checked = false;
	    },

	    /**
	     * @private
	     */
	    adjustCaret: function adjustCaret() {
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

	(function () {

	  BX.namespace('BX.Grid');
	  /**
	   * BX.Grid.UserOptions
	   * @param {BX.Main.grid} parent
	   * @param {Object} userOptions
	   * @param {Object} userOptionsActions
	   * @param {String} url
	   * @constructor
	   */

	  BX.Grid.UserOptions = function (parent, userOptions, userOptionsActions, url) {
	    this.options = null;
	    this.actions = null;
	    this.parent = null;
	    this.url = null;
	    this.init(parent, userOptions, userOptionsActions, url);
	  };

	  BX.Grid.UserOptions.prototype = {
	    init: function init(parent, userOptions, userOptionsActions, url) {
	      this.url = url;
	      this.parent = parent;

	      try {
	        this.options = eval(userOptions);
	      } catch (err) {
	        console.warn('BX.Grid.UserOptions.init: Failed parse user options json string');
	      }

	      try {
	        this.actions = eval(userOptionsActions);
	      } catch (err) {
	        console.warn('BX.Grid.UserOptions.init: Failed parse user options actions json string');
	      }
	    },
	    getCurrentViewName: function getCurrentViewName() {
	      var options = this.getOptions();
	      return 'current_view' in options ? options.current_view : null;
	    },
	    getViewsList: function getViewsList() {
	      var options = this.getOptions();
	      return 'views' in options ? options.views : {};
	    },
	    getCurrentOptions: function getCurrentOptions() {
	      var name = this.getCurrentViewName();
	      var views = this.getViewsList();
	      var result = null;

	      if (name in views) {
	        result = views[name];
	      }

	      if (!BX.type.isPlainObject(result)) {
	        result = {};
	      }

	      return result;
	    },
	    getUrl: function getUrl(action) {
	      return BX.util.add_url_param(this.url, {
	        GRID_ID: this.parent.getContainerId(),
	        bxajaxid: this.parent.getAjaxId(),
	        action: action
	      });
	    },
	    getOptions: function getOptions() {
	      return this.options || {};
	    },
	    getActions: function getActions() {
	      return this.actions;
	    },
	    getAction: function getAction(name) {
	      var action = null;

	      try {
	        action = this.getActions()[name];
	      } catch (err) {
	        action = null;
	      }

	      return action;
	    },
	    update: function update(newOptions) {
	      this.options = newOptions;
	    },
	    setColumns: function setColumns(columns, callback) {
	      var options = this.getCurrentOptions();

	      if (BX.type.isPlainObject(options)) {
	        options.columns = columns.join(',');
	        this.save(this.getAction('GRID_SET_COLUMNS'), {
	          columns: options.columns
	        }, callback);
	      }

	      return this;
	    },
	    setColumnsNames: function setColumnsNames(columns, callback) {
	      var options = {
	        view_id: 'default'
	      };

	      if (BX.type.isPlainObject(options)) {
	        options.custom_names = columns;
	        this.save(this.getAction('SET_CUSTOM_NAMES'), options, callback);
	      }

	      return this;
	    },
	    setColumnSizes: function setColumnSizes(sizes, expand) {
	      this.save(this.getAction('GRID_SET_COLUMN_SIZES'), {
	        sizes: sizes,
	        expand: expand
	      });
	    },
	    reset: function reset(forAll, callback) {
	      var data = {};

	      if (!!forAll) {
	        data = {
	          view_id: 'default',
	          set_default_settings: 'Y',
	          delete_user_settings: 'Y',
	          view_settings: this.getCurrentOptions()
	        };
	      }

	      this.save(this.getAction('GRID_RESET'), data, callback);
	    },
	    setSort: function setSort(by, order, callback) {
	      if (by && order) {
	        this.save(this.getAction('GRID_SET_SORT'), {
	          by: by,
	          order: order
	        }, callback);
	      }

	      return this;
	    },
	    setPageSize: function setPageSize(pageSize, callback) {
	      if (BX.type.isNumber(parseInt(pageSize))) {
	        this.save(this.getAction('GRID_SET_PAGE_SIZE'), {
	          pageSize: pageSize
	        }, callback);
	      }
	    },
	    setExpandedRows: function setExpandedRows(ids, callback) {
	      BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_EXPANDED_ROWS'), {
	        ids: ids
	      }, callback);
	    },
	    setCollapsedGroups: function setCollapsedGroups(ids, callback) {
	      BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_COLLAPSED_GROUPS'), {
	        ids: ids
	      }, callback);
	    },
	    resetExpandedRows: function resetExpandedRows() {
	      this.save(this.getAction('GRID_RESET_EXPANDED_ROWS'), {});
	    },
	    saveForAll: function saveForAll(callback) {
	      this.save(this.getAction('GRID_SAVE_SETTINGS'), {
	        view_id: 'default',
	        set_default_settings: 'Y',
	        delete_user_settings: 'Y',
	        view_settings: this.getCurrentOptions()
	      }, callback);
	    },
	    batch: function batch(data, callback) {
	      this.save(this.getAction('GRID_SAVE_BATH'), {
	        bath: data
	      }, callback);
	    },
	    save: function save(action, data, callback) {
	      var self = this;
	      BX.ajax.post(this.getUrl(action), data, function (res) {
	        try {
	          res = JSON.parse(res);

	          if (!res.error) {
	            self.update(res);

	            if (BX.type.isFunction(callback)) {
	              callback(res);
	            }

	            BX.onCustomEvent(self.parent.getContainer(), 'Grid::optionsChanged', [self.parent]);
	          }
	        } catch (err) {}
	      });
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Grid');
	  BX.Grid.Utils = {
	    /**
	     * Prepares url for ajax request
	     * @param {string} url
	     * @param {string} ajaxId Bitrix ajax id
	     * @returns {string} Prepares ajax url with ajax id
	     */
	    ajaxUrl: function ajaxUrl(url, ajaxId) {
	      return this.addUrlParams(url, {
	        'bxajaxid': ajaxId
	      });
	    },
	    addUrlParams: function addUrlParams(url, params) {
	      return BX.util.add_url_param(url, params);
	    },

	    /**
	     * Moves array item currentIndex to newIndex
	     * @param {array} array
	     * @param {int} currentIndex
	     * @param {int} newIndex
	     * @returns {*}
	     */
	    arrayMove: function arrayMove(array, currentIndex, newIndex) {
	      if (newIndex >= array.length) {
	        var k = newIndex - array.length;

	        while (k-- + 1) {
	          array.push(undefined);
	        }
	      }

	      array.splice(newIndex, 0, array.splice(currentIndex, 1)[0]);
	      return array;
	    },

	    /**
	     * Gets item index in array or HTMLCollection
	     * @param {array|HTMLCollection} collection
	     * @param {*} item
	     * @returns {number}
	     */
	    getIndex: function getIndex(collection, item) {
	      return [].indexOf.call(collection || [], item);
	    },

	    /**
	     * Gets nextElementSibling
	     * @param {Element} currentItem
	     * @returns {Element|null}
	     */
	    getNext: function getNext(currentItem) {
	      if (currentItem) {
	        return currentItem.nextElementSibling || null;
	      }
	    },

	    /**
	     * Gets previousElementSibling
	     * @param {Element} currentItem
	     * @returns {Element|null}
	     */
	    getPrev: function getPrev(currentItem) {
	      if (currentItem) {
	        return currentItem.previousElementSibling || null;
	      }
	    },

	    /**
	     * Gets closest parent element of node
	     * @param {Node} item
	     * @param {string} [className]
	     * @returns {*|null|Node}
	     */
	    closestParent: function closestParent(item, className) {
	      if (item) {
	        if (!className) {
	          return item.parentNode || null;
	        } else {
	          return BX.findParent(item, {
	            className: className
	          });
	        }
	      }
	    },

	    /**
	     * Gets closest childs of node
	     * @param item
	     * @returns {Array|null}
	     */
	    closestChilds: function closestChilds(item) {
	      if (item) {
	        return item.children || null;
	      }
	    },

	    /**
	     * Sorts collection
	     * @param current
	     * @param target
	     */
	    collectionSort: function collectionSort(current, target) {
	      var root, collection, collectionLength, currentIndex, targetIndex;

	      if (current && target && current !== target && current.parentNode === target.parentNode) {
	        root = this.closestParent(target);
	        collection = this.closestChilds(root);
	        collectionLength = collection.length;
	        currentIndex = this.getIndex(collection, current);
	        targetIndex = this.getIndex(collection, target);

	        if (collectionLength === targetIndex) {
	          root.appendChild(target);
	        }

	        if (currentIndex > targetIndex) {
	          root.insertBefore(current, target);
	        }

	        if (currentIndex < targetIndex && collectionLength !== targetIndex) {
	          root.insertBefore(current, this.getNext(target));
	        }
	      }
	    },

	    /**
	     * Gets table collumn
	     * @param table
	     * @param cell
	     * @returns {Array}
	     */
	    getColumn: function getColumn(table, cell) {
	      var currentIndex = this.getIndex(this.closestChilds(this.closestParent(cell)), cell);
	      var column = [];
	      [].forEach.call(table.rows, function (current) {
	        column.push(current.cells[currentIndex]);
	      });
	      return column;
	    },

	    /**
	     * Sets style properties and values for each item in collection
	     * @param {HTMLElement[]|HTMLCollection} collection
	     * @param {object} properties
	     */
	    styleForEach: function styleForEach(collection, properties) {
	      properties = BX.type.isPlainObject(properties) ? properties : null;
	      var keys = Object.keys(properties);
	      [].forEach.call(collection || [], function (current) {
	        keys.forEach(function (propKey) {
	          BX.style(current, propKey, properties[propKey]);
	        });
	      });
	    },
	    requestAnimationFrame: function requestAnimationFrame() {
	      var raf = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || window.oRequestAnimationFrame || function (callback) {
	        window.setTimeout(callback, 1000 / 60);
	      };

	      raf.apply(window, arguments);
	    },

	    /**
	     * Gets elements by class name
	     * @param rootElement
	     * @param className
	     * @param first
	     * @returns {Array|null}
	     */
	    getByClass: function getByClass(rootElement, className, first) {
	      var result = [];

	      if (className) {
	        result = rootElement ? rootElement.getElementsByClassName(className) : [];

	        if (first) {
	          result = result.length ? result[0] : null;
	        } else {
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },
	    getByTag: function getByTag(rootElement, tag, first) {
	      var result = [];

	      if (tag) {
	        result = rootElement ? rootElement.getElementsByTagName(tag) : [];

	        if (first) {
	          result = result.length ? result[0] : null;
	        } else {
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },
	    getBySelector: function getBySelector(rootElement, selector, first) {
	      var result = [];

	      if (selector) {
	        if (first) {
	          result = rootElement ? rootElement.querySelector(selector) : null;
	        } else {
	          result = rootElement ? rootElement.querySelectorAll(selector) : [];
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },
	    listenerParams: function listenerParams(params) {
	      try {
	        window.addEventListener('test', null, params);
	      } catch (e) {
	        params = false;
	      }

	      return params;
	    }
	  };
	})();

	var _templateObject$2, _templateObject2$2, _templateObject3$1, _templateObject4$1, _templateObject5$1;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @memberOf BX.Grid
	 */
	var Realtime = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Realtime, _EventEmitter);

	  function Realtime(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Realtime);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Realtime).call(this));

	    _this.setEventNamespace('BX.Grid.Realtime');

	    _this.options = _objectSpread({}, options);
	    return _this;
	  }

	  babelHelpers.createClass(Realtime, [{
	    key: "addRow",
	    value: function addRow(options) {
	      var grid = this.options.grid;
	      var row = grid.getTemplateRow();
	      row.makeCountable();
	      grid.hideEmptyStub();

	      if (main_core.Type.isNumber(options.id) || main_core.Type.isStringFilled(options.id)) {
	        row.setId(options.id);
	      } else {
	        throw new ReferenceError('id is not number or string');
	      }

	      if (main_core.Type.isArrayFilled(options.actions)) {
	        row.setActions(options.actions);
	      }

	      if (main_core.Type.isPlainObject(options.columns)) {
	        row.setCellsContent(options.columns);
	      }

	      if (main_core.Type.isPlainObject(options.cellActions)) {
	        row.setCellActions(options.cellActions);
	      }

	      if (main_core.Type.isPlainObject(options.counters)) {
	        var preparedCounters = Object.entries(options.counters).reduce(function (acc, _ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              columnId = _ref2[0],
	              counter = _ref2[1];

	          if (main_core.Type.isPlainObject(counter)) {
	            acc[columnId] = _objectSpread(_objectSpread({}, counter), {}, {
	              animation: main_core.Text.toBoolean(counter.animation)
	            });
	          }

	          return acc;
	        }, {});
	        row.setCounters(preparedCounters);
	      }

	      if (options.prepend === true) {
	        row.prependTo(grid.getBody());
	      } else if (options.append === true) {
	        row.appendTo(grid.getBody());
	      } else if (main_core.Type.isNumber(options.insertBefore) || main_core.Type.isStringFilled(options.insertBefore)) {
	        var targetRow = grid.getRows().getById(options.insertBefore);

	        if (targetRow) {
	          BX.Dom.insertBefore(row.getNode(), targetRow.getNode());
	        }
	      } else if (main_core.Type.isNumber(options.insertAfter) || main_core.Type.isStringFilled(options.insertAfter)) {
	        var _targetRow = grid.getRows().getById(options.insertAfter);

	        if (_targetRow) {
	          BX.Dom.insertAfter(row.getNode(), _targetRow.getNode());
	        }
	      } else {
	        throw new ReferenceError('prepend, append, insertBefore or insertAfter not filled');
	      }

	      row.show();

	      if (options.animation !== false) {
	        row.enableAbsolutePosition();
	        var movedElements = grid.getRows().getSourceBodyChild().filter(function (currentRow) {
	          return currentRow.rowIndex > row.getIndex();
	        });
	        var fakeRowNode = document.createElement('tr');
	        main_core.Dom.style(fakeRowNode, {
	          height: '0px',
	          transition: '200ms height linear'
	        });
	        main_core.Dom.append(fakeRowNode, grid.getBody());
	        var offset = row.getHeight();
	        main_core.Dom.style(fakeRowNode, 'height', "".concat(offset, "px"));
	        movedElements.forEach(function (element) {
	          main_core.Dom.style(element, {
	            transition: '200ms transform linear',
	            transform: "translateY(".concat(offset, "px) translateZ(0)")
	          });
	        });
	        main_core.Dom.addClass(row.getNode(), 'main-ui-grid-show-new-row');
	        main_core.Event.bind(row.getNode(), 'animationend', function (event) {
	          if (event.animationName === 'showNewRow') {
	            movedElements.forEach(function (element) {
	              main_core.Dom.style(element, {
	                transition: null,
	                transform: null
	              });
	            });
	            main_core.Dom.remove(fakeRowNode);
	            row.disableAbsolutePosition();
	            main_core.Dom.removeClass(row.getNode(), 'main-ui-grid-show-new-row');
	          }
	        });
	      }

	      grid.getRows().reset();
	      grid.bindOnRowEvents();
	      grid.updateCounterDisplayed();
	      grid.updateCounterSelected();

	      if (grid.getParam('ALLOW_ROWS_SORT')) {
	        grid.rowsSortable.reinit();
	      }

	      if (grid.getParam('ALLOW_COLUMNS_SORT')) {
	        grid.colsSortable.reinit();
	      }
	    }
	  }, {
	    key: "showStub",
	    value: function showStub() {
	      var _this2 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var tr = document.createElement('tr');
	      main_core.Dom.addClass(tr, 'main-grid-row main-grid-row-empty main-grid-row-body');
	      var td = document.createElement('td');
	      main_core.Dom.addClass(td, 'main-grid-cell main-grid-cell-center');
	      var colspan = this.options.grid.getRows().getHeadFirstChild().getCells().length;
	      main_core.Dom.attr(td, 'colspan', colspan);

	      var content = function () {
	        if (main_core.Type.isPlainObject(options.content)) {
	          var result = [];

	          if (main_core.Type.isStringFilled(options.content.title)) {
	            result.push(main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"main-grid-empty-block-title\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"])), options.content.title));
	          }

	          if (main_core.Type.isStringFilled(options.content.description)) {
	            result.push(main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"main-grid-empty-block-description\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"])), options.content.description));
	          }

	          return result;
	        }

	        if (main_core.Type.isStringFilled(options.content) || main_core.Type.isDomNode(options.content)) {
	          return options.content;
	        }

	        return [main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-grid-empty-image\"></div>"]))), main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-grid-empty-text\">", "</div>"])), _this2.options.grid.getParam('EMPTY_STUB_TEXT'))];
	      }();

	      var container = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"main-grid-empty-block\">\n\t\t\t\t<div class=\"main-grid-empty-inner\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), content);
	      main_core.Dom.append(container, td);
	      main_core.Dom.append(td, tr);
	      var oldStub = this.options.grid.getBody().querySelector('.main-grid-row-empty');

	      if (oldStub) {
	        main_core.Dom.remove(oldStub);
	      }

	      main_core.Dom.append(tr, this.options.grid.getBody());
	      this.options.grid.getRows().getBodyChild().forEach(function (row) {
	        row.hide();
	      });
	      this.options.grid.adjustEmptyTable(this.options.grid.getRows().getSourceBodyChild());
	    }
	  }]);
	  return Realtime;
	}(main_core_events.EventEmitter);
	var namespace = main_core.Reflection.namespace('BX.Grid');
	namespace.Realtime = Realtime;

	/**
	 * @memberOf BX.Grid
	 */

	var CellActions = function CellActions() {
	  babelHelpers.classCallCheck(this, CellActions);
	};
	babelHelpers.defineProperty(CellActions, "PIN", 'main-grid-cell-content-action-pin');
	babelHelpers.defineProperty(CellActions, "MUTE", 'main-grid-cell-content-action-mute');
	var namespace$1 = main_core.Reflection.namespace('BX.Grid');
	namespace$1.CellActions = CellActions;

	/**
	 * @memberOf BX.Grid
	 */

	var CellActionState = function CellActionState() {
	  babelHelpers.classCallCheck(this, CellActionState);
	};
	babelHelpers.defineProperty(CellActionState, "SHOW_BY_HOVER", 'main-grid-cell-content-action-by-hover');
	babelHelpers.defineProperty(CellActionState, "ACTIVE", 'main-grid-cell-content-action-active');
	var namespace$2 = main_core.Reflection.namespace('BX.Grid');
	namespace$2.CellActionState = CellActionState;

	/**
	 * @memberOf BX.Grid
	 */

	var Counters = function Counters() {
	  babelHelpers.classCallCheck(this, Counters);
	};
	babelHelpers.defineProperty(Counters, "Type", {
	  LEFT: 'left',
	  LEFT_ALIGNED: 'left-aligned',
	  RIGHT: 'right'
	});
	babelHelpers.defineProperty(Counters, "Color", {
	  DANGER: 'ui-counter-danger',
	  SUCCESS: 'ui-counter-success',
	  PRIMARY: 'ui-counter-primary',
	  GRAY: 'ui-counter-gray',
	  LIGHT: 'ui-counter-light',
	  DARK: 'ui-counter-dark'
	});
	babelHelpers.defineProperty(Counters, "Size", {
	  LARGE: 'ui-counter-lg',
	  MEDIUM: 'ui-counter-md'
	});
	var namespace$3 = main_core.Reflection.namespace('BX.Grid');
	namespace$3.Counters = Counters;

	/**
	 * @memberOf BX.Grid
	 */

	var Label = function Label() {
	  babelHelpers.classCallCheck(this, Label);
	};
	babelHelpers.defineProperty(Label, "Color", {
	  DEFAULT: 'ui-label-default',
	  DANGER: 'ui-label-danger',
	  SUCCESS: 'ui-label-success',
	  WARNING: 'ui-label-warning',
	  PRIMARY: 'ui-label-primary',
	  SECONDARY: 'ui-label-secondary',
	  LIGHTGREEN: 'ui-label-lightgreen',
	  LIGHTBLUE: 'ui-label-lightblue',
	  LIGHT: 'ui-label-light'
	});
	babelHelpers.defineProperty(Label, "RemoveButtonType", {
	  INSIDE: 'main-grid-tag-remove-inside',
	  OUTSIDE: 'main-grid-tag-remove-outside'
	});
	var namespace$4 = main_core.Reflection.namespace('BX.Grid');
	namespace$4.Label = Label;

	(function () {

	  BX.namespace('BX.Main');
	  /**
	   * @event Grid::ready
	   * @event Grid::columnMoved
	   * @event Grid::rowMoved
	   * @event Grid::pageSizeChanged
	   * @event Grid::optionsUpdated
	   * @event Grid::dataSorted
	   * @event Grid::thereSelectedRows
	   * @event Grid::allRowsSelected
	   * @event Grid::allRowsUnselected
	   * @event Grid::noSelectedRows
	   * @event Grid::updated
	   * @event Grid::headerPinned
	   * @event Grid::headerUnpinned
	   * @event Grid::beforeRequest
	   * @param {string} containerId
	   * @param {object} arParams
	   * @param {boolean} arParams.ALLOW_COLUMNS_SORT
	   * @param {boolean} arParams.ALLOW_ROWS_SORT
	   * @param {boolean} arParams.ALLOW_COLUMNS_RESIZE
	   * @param {boolean} arParams.SHOW_ROW_CHECKBOXES
	   * @param {boolean} arParams.ALLOW_HORIZONTAL_SCROLL
	   * @param {boolean} arParams.ALLOW_PIN_HEADER
	   * @param {boolean} arParams.SHOW_ACTION_PANEL
	   * @param {boolean} arParams.PRESERVE_HISTORY
	   * @param {boolean} arParams.BACKEND_URL
	   * @param {boolean} arParams.ALLOW_CONTEXT_MENU
	   * @param {object} arParams.DEFAULT_COLUMNS
	   * @param {boolean} arParams.ENABLE_COLLAPSIBLE_ROWS
	   * @param {object} arParams.EDITABLE_DATA
	   * @param {string} arParams.SETTINGS_TITLE
	   * @param {string} arParams.APPLY_SETTINGS
	   * @param {string} arParams.CANCEL_SETTINGS
	   * @param {string} arParams.CONFIRM_APPLY
	   * @param {string} arParams.CONFIRM_CANCEL
	   * @param {string} arParams.CONFIRM_MESSAGE
	   * @param {string} arParams.CONFIRM_FOR_ALL_MESSAGE
	   * @param {string} arParams.CONFIRM_RESET_MESSAGE
	   * @param {object} arParams.COLUMNS_ALL_WITH_SECTIONS
	   * @param {boolean} arParams.ENABLE_FIELDS_SEARCH
	   * @param {string} arParams.RESET_DEFAULT
	   * @param {object} userOptions
	   * @param {object} userOptionsActions
	   * @param {object} userOptionsHandlerUrl
	   * @param {object} panelActions
	   * @param {object} panelTypes
	   * @param {object} editorTypes
	   * @param {object} messageTypes
	   * @constructor
	   */

	  BX.Main.grid = function (containerId, arParams, userOptions, userOptionsActions, userOptionsHandlerUrl, panelActions, panelTypes, editorTypes, messageTypes) {
	    BX.Event.EventEmitter.makeObservable(this, 'BX.Main.Grid');
	    this.settings = null;
	    this.containerId = '';
	    this.container = null;
	    this.wrapper = null;
	    this.fadeContainer = null;
	    this.scrollContainer = null;
	    this.pagination = null;
	    this.moreButton = null;
	    this.table = null;
	    this.rows = null;
	    this.history = false;
	    this.userOptions = null;
	    this.checkAll = null;
	    this.sortable = null;
	    this.updater = null;
	    this.data = null;
	    this.fader = null;
	    this.editor = null;
	    this.isEditMode = null;
	    this.pinHeader = null;
	    this.pinPanel = null;
	    this.arParams = null;
	    this.resize = null;
	    this.editableRows = [];
	    this.init(containerId, arParams, userOptions, userOptionsActions, userOptionsHandlerUrl, panelActions, panelTypes, editorTypes, messageTypes);
	  };

	  BX.Main.grid.isNeedResourcesReady = function (container) {
	    return BX.hasClass(container, 'main-grid-load-animation');
	  };

	  BX.Main.grid.prototype = {
	    init: function init(containerId, arParams, userOptions, userOptionsActions, userOptionsHandlerUrl, panelActions, panelTypes, editorTypes, messageTypes) {
	      this.baseUrl = window.location.pathname + window.location.search;
	      this.container = BX(containerId);

	      if (!BX.type.isNotEmptyString(containerId)) {
	        throw 'BX.Main.grid.init: parameter containerId is empty';
	      }

	      if (BX.type.isPlainObject(arParams)) {
	        this.arParams = arParams;
	      } else {
	        throw new Error('BX.Main.grid.init: arParams isn\'t object');
	      }

	      this.settings = new BX.Grid.Settings();
	      this.containerId = containerId;
	      this.userOptions = new BX.Grid.UserOptions(this, userOptions, userOptionsActions, userOptionsHandlerUrl);
	      this.gridSettings = new BX.Grid.SettingsWindow(this);
	      this.messages = new BX.Grid.Message(this, messageTypes);
	      this.cache = new BX.Cache.MemoryCache();

	      if (this.getParam('ALLOW_PIN_HEADER')) {
	        this.pinHeader = new BX.Grid.PinHeader(this);
	        BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.bindOnCheckAll, this));
	      }

	      this.bindOnCheckAll();

	      if (this.getParam('ALLOW_HORIZONTAL_SCROLL')) {
	        this.fader = new BX.Grid.Fader(this);
	      }

	      this.pageSize = new BX.Grid.Pagesize(this);
	      this.editor = new BX.Grid.InlineEditor(this, editorTypes);

	      if (this.getParam('SHOW_ACTION_PANEL')) {
	        this.actionPanel = new BX.Grid.ActionPanel(this, panelActions, panelTypes);
	        this.pinPanel = new BX.Grid.PinPanel(this);
	      }

	      this.isEditMode = false;

	      if (!BX.type.isDomNode(this.getContainer())) {
	        throw 'BX.Main.grid.init: Failed to find container with id ' + this.getContainerId();
	      }

	      if (!BX.type.isDomNode(this.getTable())) {
	        throw 'BX.Main.grid.init: Failed to find table';
	      }

	      this.bindOnRowEvents();

	      if (this.getParam('ALLOW_COLUMNS_RESIZE')) {
	        this.resize = new BX.Grid.Resize(this);
	      }

	      this.bindOnMoreButtonEvents();
	      this.bindOnClickPaginationLinks();
	      this.bindOnClickHeader();

	      if (this.getParam('ALLOW_ROWS_SORT')) {
	        this.initRowsDragAndDrop();
	      }

	      if (this.getParam('ALLOW_COLUMNS_SORT')) {
	        this.initColsDragAndDrop();
	      }

	      this.getRows().initSelected();
	      this.adjustEmptyTable(this.getRows().getSourceBodyChild());
	      BX.onCustomEvent(this.getContainer(), 'Grid::ready', [this]);
	      BX.addCustomEvent(window, 'Grid::unselectRow', BX.proxy(this._onUnselectRows, this));
	      BX.addCustomEvent(window, 'Grid::unselectRows', BX.proxy(this._onUnselectRows, this));
	      BX.addCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this._onUnselectRows, this));
	      BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this._onGridUpdated, this));
	      window.frames[this.getFrameId()].onresize = BX.throttle(this._onFrameResize, 20, this);

	      if (this.getParam('ALLOW_STICKED_COLUMNS')) {
	        this.initStickedColumns();
	      }
	    },
	    destroy: function destroy() {
	      BX.removeCustomEvent(window, 'Grid::unselectRow', BX.proxy(this._onUnselectRows, this));
	      BX.removeCustomEvent(window, 'Grid::unselectRows', BX.proxy(this._onUnselectRows, this));
	      BX.removeCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this._onUnselectRows, this));
	      BX.removeCustomEvent(window, 'Grid::headerPinned', BX.proxy(this.bindOnCheckAll, this));
	      BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this._onGridUpdated, this));
	      this.getPinHeader() && this.getPinHeader().destroy();
	      this.getFader() && this.getFader().destroy();
	      this.getResize() && this.getResize().destroy();
	      this.getColsSortable() && this.getColsSortable().destroy();
	      this.getRowsSortable() && this.getRowsSortable().destroy();
	      this.getSettingsWindow() && this.getSettingsWindow().destroy();
	      this.getActionsPanel() && this.getActionsPanel().destroy();
	      this.getPinPanel() && this.getPinPanel().destroy();
	      this.getPageSize() && this.getPageSize().destroy();
	    },
	    _onFrameResize: function _onFrameResize() {
	      BX.onCustomEvent(window, 'Grid::resize', [this]);
	    },
	    _onGridUpdated: function _onGridUpdated() {
	      this.initStickedColumns();
	      this.adjustFadePosition(this.getFadeOffset());
	    },

	    /**
	     * @private
	     * @return {string}
	     */
	    getFrameId: function getFrameId() {
	      return "main-grid-tmp-frame-" + this.getContainerId();
	    },
	    enableActionsPanel: function enableActionsPanel() {
	      if (this.getParam('SHOW_ACTION_PANEL')) {
	        var panel = this.getActionsPanel().getPanel();

	        if (BX.type.isDomNode(panel)) {
	          BX.removeClass(panel, this.settings.get('classDisable'));
	        }
	      }
	    },
	    disableActionsPanel: function disableActionsPanel() {
	      if (this.getParam('SHOW_ACTION_PANEL')) {
	        var panel = this.getActionsPanel().getPanel();

	        if (BX.type.isDomNode(panel)) {
	          BX.addClass(panel, this.settings.get('classDisable'));
	        }
	      }
	    },
	    getSettingsWindow: function getSettingsWindow() {
	      return this.gridSettings;
	    },
	    _onUnselectRows: function _onUnselectRows() {
	      var panel = this.getActionsPanel();
	      var checkbox;

	      if (panel instanceof BX.Grid.ActionPanel) {
	        checkbox = panel.getForAllCheckbox();

	        if (BX.type.isDomNode(checkbox)) {
	          checkbox.checked = null;
	          this.disableForAllCounter();
	        }
	      }

	      this.adjustCheckAllCheckboxes();
	    },

	    /**
	     * @return {boolean}
	     */
	    isIE: function isIE() {
	      if (!BX.type.isBoolean(this.ie)) {
	        this.ie = BX.hasClass(document.documentElement, 'bx-ie');
	      }

	      return this.ie;
	    },

	    /**
	     * @return {boolean}
	     */
	    isTouch: function isTouch() {
	      if (!BX.type.isBoolean(this.touch)) {
	        this.touch = BX.hasClass(document.documentElement, 'bx-touch');
	      }

	      return this.touch;
	    },

	    /**
	     * @param {string} paramName
	     * @param {*} [defaultValue]
	     * @return {*}
	     */
	    getParam: function getParam(paramName, defaultValue) {
	      if (defaultValue === undefined) {
	        defaultValue = null;
	      }

	      return this.arParams.hasOwnProperty(paramName) ? this.arParams[paramName] : defaultValue;
	    },

	    /**
	     * @return {HTMLElement[]}
	     */
	    getCounterTotal: function getCounterTotal() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterTotal'), true);
	    },
	    getActionKey: function getActionKey() {
	      return 'action_button_' + this.getId();
	    },

	    /**
	     * @return {?BX.Grid.PinHeader}
	     */
	    getPinHeader: function getPinHeader() {
	      if (this.getParam('ALLOW_PIN_HEADER')) {
	        this.pinHeader = this.pinHeader || new BX.Grid.PinHeader(this);
	      }

	      return this.pinHeader;
	    },

	    /**
	     * @return {BX.Grid.Resize}
	     */
	    getResize: function getResize() {
	      if (!(this.resize instanceof BX.Grid.Resize) && this.getParam('ALLOW_COLUMNS_RESIZE')) {
	        this.resize = new BX.Grid.Resize(this);
	      }

	      return this.resize;
	    },
	    confirmForAll: function confirmForAll(container) {
	      var checkbox;
	      var self = this;

	      if (BX.type.isDomNode(container)) {
	        checkbox = BX.Grid.Utils.getByTag(container, 'input', true);
	      }

	      if (checkbox.checked) {
	        this.getActionsPanel().confirmDialog({
	          CONFIRM: true,
	          CONFIRM_MESSAGE: this.arParams.CONFIRM_FOR_ALL_MESSAGE
	        }, function () {
	          if (BX.type.isDomNode(checkbox)) {
	            checkbox.checked = true;
	          }

	          self.selectAllCheckAllCheckboxes();
	          self.getRows().selectAll();
	          self.enableForAllCounter();
	          self.updateCounterDisplayed();
	          self.updateCounterSelected();
	          self.enableActionsPanel();
	          self.adjustCheckAllCheckboxes();
	          self.lastRowAction = null;
	          BX.onCustomEvent(window, 'Grid::allRowsSelected', []);
	        }, function () {
	          if (BX.type.isDomNode(checkbox)) {
	            checkbox.checked = null;
	            self.disableForAllCounter();
	            self.updateCounterDisplayed();
	            self.updateCounterSelected();
	            self.adjustCheckAllCheckboxes();
	            self.lastRowAction = null;
	          }
	        });
	      } else {
	        this.unselectAllCheckAllCheckboxes();
	        this.adjustCheckAllCheckboxes();
	        this.getRows().unselectAll();
	        this.disableForAllCounter();
	        this.updateCounterDisplayed();
	        this.updateCounterSelected();
	        this.disableActionsPanel();
	        BX.onCustomEvent(window, 'Grid::allRowsUnselected', []);
	      }
	    },
	    disableCheckAllCheckboxes: function disableCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (checkbox) {
	        checkbox.getNode().disabled = true;
	      });
	    },
	    enableCheckAllCheckboxes: function enableCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (checkbox) {
	        checkbox.getNode().disabled = false;
	      });
	    },
	    indeterminateCheckAllCheckboxes: function indeterminateCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (checkbox) {
	        checkbox.getNode().indeterminate = true;
	      });
	    },
	    determinateCheckAllCheckboxes: function determinateCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (checkbox) {
	        checkbox.getNode().indeterminate = false;
	      });
	    },
	    editSelected: function editSelected() {
	      this.disableCheckAllCheckboxes();
	      this.getRows().editSelected();

	      if (this.getParam('ALLOW_PIN_HEADER')) {
	        this.getPinHeader()._onGridUpdate();
	      }

	      BX.onCustomEvent(window, 'Grid::resize', [this]);
	    },
	    editSelectedSave: function editSelectedSave() {
	      var data = {
	        'FIELDS': this.getRows().getEditSelectedValues(true)
	      };

	      if (this.getParam("ALLOW_VALIDATE")) {
	        this.tableFade();
	        data[this.getActionKey()] = 'validate';
	        this.getData().request('', 'POST', data, 'validate', function (res) {
	          res = JSON.parse(res);

	          if (res.messages.length) {
	            this.arParams['MESSAGES'] = res.messages;
	            this.messages.show();
	            var editButton = this.getActionsPanel().getButtons().find(function (button) {
	              return button.id === "grid_edit_button_control";
	            });
	            this.tableUnfade();
	            BX.fireEvent(editButton, 'click');
	          } else {
	            data[this.getActionKey()] = 'edit';
	            this.reloadTable('POST', data);
	          }
	        }.bind(this));
	        return;
	      }

	      if (this.getParam('HANDLE_RESPONSE_ERRORS')) {
	        data[this.getActionKey()] = 'edit';
	        var self = this;
	        this.tableFade();
	        this.getData().request('', "POST", data, '', function (res) {
	          try {
	            res = JSON.parse(res);
	          } catch (err) {
	            res = {
	              messages: []
	            };
	          }

	          if (res.messages.length) {
	            self.arParams['MESSAGES'] = res.messages;
	            self.messages.show();
	            var editButton = self.getActionsPanel().getButtons().find(function (button) {
	              return button.id === "grid_edit_button_control";
	            });
	            self.tableUnfade();
	            BX.fireEvent(editButton, 'click');
	            return;
	          }

	          self.getRows().reset();
	          var bodyRows = this.getBodyRows();
	          self.getUpdater().updateHeadRows(this.getHeadRows());
	          self.getUpdater().updateBodyRows(bodyRows);
	          self.getUpdater().updateFootRows(this.getFootRows());
	          self.getUpdater().updatePagination(this.getPagination());
	          self.getUpdater().updateMoreButton(this.getMoreButton());
	          self.getUpdater().updateCounterTotal(this.getCounterTotal());
	          self.adjustEmptyTable(bodyRows);
	          self.bindOnRowEvents();
	          self.bindOnMoreButtonEvents();
	          self.bindOnClickPaginationLinks();
	          self.bindOnClickHeader();
	          self.bindOnCheckAll();
	          self.updateCounterDisplayed();
	          self.updateCounterSelected();
	          self.disableActionsPanel();
	          self.disableForAllCounter();

	          if (self.getParam('SHOW_ACTION_PANEL')) {
	            self.getUpdater().updateGroupActions(this.getActionPanel());
	          }

	          if (self.getParam('ALLOW_COLUMNS_SORT')) {
	            self.colsSortable.reinit();
	          }

	          if (self.getParam('ALLOW_ROWS_SORT')) {
	            self.rowsSortable.reinit();
	          }

	          self.tableUnfade();
	          BX.onCustomEvent(window, 'Grid::updated', [self]);
	        }, function (res) {
	          var editButton = self.getActionsPanel().getButtons().find(function (button) {
	            return button.id === "grid_edit_button_control";
	          });
	          self.tableUnfade();
	          BX.fireEvent(editButton, 'click');
	        });
	        return;
	      }

	      data[this.getActionKey()] = 'edit';
	      this.reloadTable('POST', data);
	    },
	    getForAllKey: function getForAllKey() {
	      return 'action_all_rows_' + this.getId();
	    },
	    updateRow: function updateRow(id, data, url, callback) {
	      var row = this.getRows().getById(id);

	      if (row instanceof BX.Grid.Row) {
	        row.update(data, url, callback);
	      }
	    },
	    removeRow: function removeRow(id, data, url, callback) {
	      var row = this.getRows().getById(id);

	      if (row instanceof BX.Grid.Row) {
	        row.remove(data, url, callback);
	      }
	    },
	    addRow: function addRow(data, url, callback) {
	      var action = this.getUserOptions().getAction('GRID_ADD_ROW');
	      var rowData = {
	        action: action,
	        data: data
	      };
	      var self = this;
	      this.tableFade();
	      this.getData().request(url, 'POST', rowData, null, function () {
	        var bodyRows = this.getBodyRows();
	        self.getUpdater().updateBodyRows(bodyRows);
	        self.tableUnfade();
	        self.getRows().reset();
	        self.getUpdater().updateFootRows(this.getFootRows());
	        self.getUpdater().updatePagination(this.getPagination());
	        self.getUpdater().updateMoreButton(this.getMoreButton());
	        self.getUpdater().updateCounterTotal(this.getCounterTotal());
	        self.bindOnRowEvents();
	        self.adjustEmptyTable(bodyRows);
	        self.bindOnMoreButtonEvents();
	        self.bindOnClickPaginationLinks();
	        self.updateCounterDisplayed();
	        self.updateCounterSelected();

	        if (self.getParam('ALLOW_COLUMNS_SORT')) {
	          self.colsSortable.reinit();
	        }

	        if (self.getParam('ALLOW_ROWS_SORT')) {
	          self.rowsSortable.reinit();
	        }

	        BX.onCustomEvent(window, 'Grid::rowAdded', [{
	          data: data,
	          grid: self,
	          response: this
	        }]);
	        BX.onCustomEvent(window, 'Grid::updated', [self]);

	        if (BX.type.isFunction(callback)) {
	          callback({
	            data: data,
	            grid: self,
	            response: this
	          });
	        }
	      });
	    },
	    editSelectedCancel: function editSelectedCancel() {
	      this.getRows().editSelectedCancel();

	      if (this.getParam('ALLOW_PIN_HEADER')) {
	        this.getPinHeader()._onGridUpdate();
	      }
	    },
	    removeSelected: function removeSelected() {
	      var data = {
	        'ID': this.getRows().getSelectedIds()
	      };
	      var values = this.getActionsPanel().getValues();
	      data[this.getActionKey()] = 'delete';
	      data[this.getForAllKey()] = this.getForAllKey() in values ? values[this.getForAllKey()] : 'N';
	      this.reloadTable('POST', data);
	    },
	    sendSelected: function sendSelected() {
	      var values = this.getActionsPanel().getValues();
	      var selectedRows = this.getRows().getSelectedIds();
	      var data = {
	        rows: selectedRows,
	        controls: values
	      };
	      this.reloadTable('POST', data);
	    },

	    /**
	     * @return {?BX.Grid.ActionPanel}
	     */
	    getActionsPanel: function getActionsPanel() {
	      return this.actionPanel;
	    },
	    getPinPanel: function getPinPanel() {
	      return this.pinPanel;
	    },
	    getApplyButton: function getApplyButton() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classPanelButton'), true);
	    },
	    getEditor: function getEditor() {
	      return this.editor;
	    },
	    reload: function reload(url) {
	      this.reloadTable("GET", {}, null, url);
	    },
	    getPanels: function getPanels() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classPanels'), true);
	    },
	    getEmptyBlock: function getEmptyBlock() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classEmptyBlock'), true);
	    },
	    adjustEmptyTable: function adjustEmptyTable(rows) {
	      function adjustEmptyBlockPosition(event) {
	        var target = event.currentTarget;
	        BX.style(emptyBlock, 'transform', 'translate3d(' + BX.scrollLeft(target) + 'px, 0px, 0');
	      }

	      var filteredRows = rows.filter(function (row) {
	        return BX.Dom.attr(row, 'data-id') !== 'template_0' && !BX.Dom.hasClass(row, 'main-grid-hide');
	      });

	      if (!BX.hasClass(document.documentElement, 'bx-ie') && filteredRows.length === 1 && BX.hasClass(filteredRows[0], this.settings.get('classEmptyRows'))) {
	        var gridRect = BX.pos(this.getContainer());
	        var scrollBottom = BX.scrollTop(window) + BX.height(window);
	        var diff = gridRect.bottom - scrollBottom;
	        var panelsHeight = BX.height(this.getPanels());
	        var emptyBlock = this.getEmptyBlock();
	        var containerWidth = BX.width(this.getContainer());

	        if (containerWidth) {
	          BX.width(emptyBlock, containerWidth);
	        }

	        BX.style(emptyBlock, 'transform', 'translate3d(' + BX.scrollLeft(this.getScrollContainer()) + 'px, 0px, 0');
	        BX.unbind(this.getScrollContainer(), 'scroll', adjustEmptyBlockPosition);
	        BX.bind(this.getScrollContainer(), 'scroll', adjustEmptyBlockPosition);
	        var parent = this.getContainer();
	        var paddingOffset = 0;

	        while (parent = parent.parentElement) {
	          var parentPaddingTop = parseFloat(BX.style(parent, "padding-top"));
	          var parentPaddingBottom = parseFloat(BX.style(parent, "padding-bottom"));

	          if (!isNaN(parentPaddingTop)) {
	            paddingOffset += parentPaddingTop;
	          }

	          if (!isNaN(parentPaddingBottom)) {
	            paddingOffset += parentPaddingBottom;
	          }
	        }

	        if (diff > 0) {
	          BX.style(this.getTable(), 'min-height', gridRect.height - diff - panelsHeight - paddingOffset + 'px');
	        } else {
	          BX.style(this.getTable(), 'min-height', gridRect.height + Math.abs(diff) - panelsHeight - paddingOffset + 'px');
	        }

	        if (this.getCurrentPage() <= 1) {
	          BX.Dom.hide(this.getPanels());
	        }
	      } else {
	        BX.style(this.getTable(), 'min-height', ''); // Chrome hack for 0116845 bug. @todo refactoring

	        BX.style(this.getTable(), 'height', '1px');
	        requestAnimationFrame(function () {
	          BX.style(this.getTable(), 'height', '1px');
	        }.bind(this));
	        BX.Dom.show(this.getPanels());
	      }
	    },
	    reloadTable: function reloadTable(method, data, callback, url) {
	      var bodyRows;

	      if (!BX.type.isNotEmptyString(method)) {
	        method = "GET";
	      }

	      if (!BX.type.isPlainObject(data)) {
	        data = {};
	      }

	      var self = this;
	      this.tableFade();

	      if (!BX.type.isString(url)) {
	        url = "";
	      }

	      this.getData().request(url, method, data, '', function () {
	        BX.onCustomEvent(window, 'BX.Main.Grid:onBeforeReload', [self]);
	        self.getRows().reset();
	        bodyRows = this.getBodyRows();
	        self.getUpdater().updateHeadRows(this.getHeadRows());
	        self.getUpdater().updateBodyRows(bodyRows);
	        self.getUpdater().updateFootRows(this.getFootRows());
	        self.getUpdater().updatePagination(this.getPagination());
	        self.getUpdater().updateMoreButton(this.getMoreButton());
	        self.getUpdater().updateCounterTotal(this.getCounterTotal());
	        self.adjustEmptyTable(bodyRows);
	        self.bindOnRowEvents();
	        self.bindOnMoreButtonEvents();
	        self.bindOnClickPaginationLinks();
	        self.bindOnClickHeader();
	        self.bindOnCheckAll();
	        self.updateCounterDisplayed();
	        self.updateCounterSelected();
	        self.disableActionsPanel();
	        self.disableForAllCounter();

	        if (self.getParam('SHOW_ACTION_PANEL')) {
	          self.getUpdater().updateGroupActions(this.getActionPanel());
	        }

	        if (self.getParam('ALLOW_COLUMNS_SORT')) {
	          self.colsSortable.reinit();
	        }

	        if (self.getParam('ALLOW_ROWS_SORT')) {
	          self.rowsSortable.reinit();
	        }

	        self.tableUnfade();
	        BX.onCustomEvent(window, 'Grid::updated', [self]);

	        if (BX.type.isFunction(callback)) {
	          callback();
	        }

	        if (self.getParam('ALLOW_PIN_HEADER')) {
	          self.getPinHeader()._onGridUpdate();
	        }
	      });
	    },
	    getGroupEditButton: function getGroupEditButton() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classGroupEditButton'), true);
	    },
	    getGroupDeleteButton: function getGroupDeleteButton() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classGroupDeleteButton'), true);
	    },
	    enableGroupActions: function enableGroupActions() {
	      var editButton = this.getGroupEditButton();
	      var deleteButton = this.getGroupDeleteButton();

	      if (BX.type.isDomNode(editButton)) {
	        BX.removeClass(editButton, this.settings.get('classGroupActionsDisabled'));
	      }

	      if (BX.type.isDomNode(deleteButton)) {
	        BX.removeClass(deleteButton, this.settings.get('classGroupActionsDisabled'));
	      }
	    },
	    disableGroupActions: function disableGroupActions() {
	      var editButton = this.getGroupEditButton();
	      var deleteButton = this.getGroupDeleteButton();

	      if (BX.type.isDomNode(editButton)) {
	        BX.addClass(editButton, this.settings.get('classGroupActionsDisabled'));
	      }

	      if (BX.type.isDomNode(deleteButton)) {
	        BX.addClass(deleteButton, this.settings.get('classGroupActionsDisabled'));
	      }
	    },
	    closeActionsMenu: function closeActionsMenu() {
	      var rows = this.getRows().getRows();

	      for (var i = 0, l = rows.length; i < l; i++) {
	        rows[i].closeActionsMenu();
	      }
	    },
	    getPageSize: function getPageSize() {
	      return this.pageSize;
	    },

	    /**
	     * @return {?BX.Grid.Fader}
	     */
	    getFader: function getFader() {
	      return this.fader;
	    },

	    /**
	     * @return {BX.Grid.Data}
	     */
	    getData: function getData() {
	      this.data = this.data || new BX.Grid.Data(this);
	      return this.data;
	    },

	    /**
	     * @return {BX.Grid.Updater}
	     */
	    getUpdater: function getUpdater() {
	      this.updater = this.updater || new BX.Grid.Updater(this);
	      return this.updater;
	    },
	    isSortableHeader: function isSortableHeader(item) {
	      return BX.hasClass(item, this.settings.get('classHeaderSortable'));
	    },
	    isNoSortableHeader: function isNoSortableHeader(item) {
	      return BX.hasClass(item, this.settings.get('classHeaderNoSortable'));
	    },
	    bindOnClickHeader: function bindOnClickHeader() {
	      var self = this;
	      var cell;
	      BX.bind(this.getContainer(), 'click', function (event) {
	        cell = BX.findParent(event.target, {
	          tag: 'th'
	        }, true, false);

	        if (cell && self.isSortableHeader(cell) && !self.preventSortableClick) {
	          var onBeforeSortEvent = new BX.Event.BaseEvent({
	            data: {
	              grid: self,
	              columnName: BX.data(cell, 'name')
	            }
	          });
	          BX.Event.EventEmitter.emit('BX.Main.grid:onBeforeSort', onBeforeSortEvent);

	          if (onBeforeSortEvent.isDefaultPrevented()) {
	            return;
	          }

	          self.preventSortableClick = false;

	          self._clickOnSortableHeader(cell, event);
	        }
	      });
	    },
	    enableEditMode: function enableEditMode() {
	      this.isEditMode = true;
	    },
	    disableEditMode: function disableEditMode() {
	      this.isEditMode = false;
	    },
	    isEditMode: function isEditMode() {
	      return this.isEditMode;
	    },
	    getColumnHeaderCellByName: function getColumnHeaderCellByName(name) {
	      return BX.Grid.Utils.getBySelector(this.getContainer(), '#' + this.getId() + ' th[data-name="' + name + '"]', true);
	    },
	    getColumnByName: function getColumnByName(name) {
	      var columns = this.getParam('DEFAULT_COLUMNS');
	      return !!name && name in columns ? columns[name] : null;
	    },
	    adjustIndex: function adjustIndex(index) {
	      var fixedCells = this.getAllRows()[0].querySelectorAll('.main-grid-fixed-column').length;
	      return index + fixedCells;
	    },
	    getColumnByIndex: function getColumnByIndex(index) {
	      index = this.adjustIndex(index);
	      return this.getAllRows().reduce(function (accumulator, row) {
	        if (!row.classList.contains('main-grid-row-custom') && !row.classList.contains('main-grid-row-empty')) {
	          accumulator.push(row.children[index]);
	        }

	        return accumulator;
	      }, []);
	    },
	    getAllRows: function getAllRows() {
	      var rows = [].slice.call(this.getTable().rows);
	      var fixedTable = this.getContainer().parentElement.querySelector(".main-grid-fixed-bar table");

	      if (fixedTable) {
	        rows.push(fixedTable.rows[0]);
	      }

	      return rows;
	    },
	    initStickedColumns: function initStickedColumns() {
	      [].slice.call(this.getAllRows()[0].children).forEach(function (cell, index) {
	        if (cell.classList.contains('main-grid-sticked-column')) {
	          this.stickyColumnByIndex(index);
	        }
	      }, this);

	      if (this.getParam('ALLOW_COLUMNS_RESIZE')) {
	        this.getResize().destroy();
	        this.getResize().init(this);
	      }
	    },
	    setStickedColumns: function setStickedColumns(columns) {
	      if (BX.type.isArray(columns)) {
	        var options = this.getUserOptions();
	        var actions = [{
	          action: options.getAction('GRID_SET_STICKED_COLUMNS'),
	          stickedColumns: columns
	        }];
	        options.batch(actions, function () {
	          this.reloadTable();
	        }.bind(this));
	      }
	    },
	    getStickedColumns: function getStickedColumns() {
	      var columns = [].slice.call(this.getHead().querySelectorAll('.main-grid-cell-head'));
	      return columns.reduce(function (acc, column) {
	        if (BX.hasClass(column, 'main-grid-fixed-column') && !BX.hasClass(column, 'main-grid-cell-checkbox') && !BX.hasClass(column, 'main-grid-cell-action')) {
	          acc.push(column.dataset.name);
	        }

	        return acc;
	      }.bind(this), []);
	    },
	    stickyColumnByIndex: function stickyColumnByIndex(index) {
	      var column = this.getColumnByIndex(index);
	      var cellWidth = column[0].clientWidth;
	      var heights = column.map(function (cell) {
	        return BX.height(cell);
	      });
	      column.forEach(function (cell, cellIndex) {
	        cell.style.minWidth = cellWidth + 'px';
	        cell.style.width = cellWidth + 'px';
	        cell.style.minHeight = heights[cellIndex] + 'px';
	        var clone = BX.clone(cell);
	        var lastStickyCell = this.getLastStickyCellFromRowByIndex(cellIndex);

	        if (lastStickyCell) {
	          var lastStickyCellLeft = parseInt(BX.style(lastStickyCell, 'left'));
	          var lastStickyCellWidth = parseInt(BX.style(lastStickyCell, 'width'));
	          lastStickyCellLeft = isNaN(lastStickyCellLeft) ? 0 : lastStickyCellLeft;
	          lastStickyCellWidth = isNaN(lastStickyCellWidth) ? 0 : lastStickyCellWidth;
	          cell.style.left = lastStickyCellLeft + lastStickyCellWidth + 'px';
	        }

	        cell.classList.add('main-grid-fixed-column');
	        cell.classList.add('main-grid-cell-static');
	        clone.classList.add('main-grid-cell-static');

	        if (this.getColsSortable()) {
	          this.getColsSortable().unregister(cell);
	          this.getColsSortable().unregister(clone);
	        }

	        BX.insertAfter(clone, cell);
	      }, this);
	      this.adjustFadePosition(this.getFadeOffset());
	    },
	    adjustFixedColumnsPosition: function adjustFixedColumnsPosition() {
	      var fixedCells = this.getAllRows()[0].querySelectorAll('.main-grid-fixed-column').length;
	      var columnsPosition = [].slice.call(this.getAllRows()[0].children).reduce(function (accumulator, cell, index, columns) {
	        var cellLeft;
	        var cellWidth;

	        if (columns[index - 1] && columns[index - 1].classList.contains('main-grid-fixed-column')) {
	          cellLeft = parseInt(BX.style(columns[index - 1], 'left'));
	          cellWidth = parseInt(BX.style(columns[index - 1], 'width'));
	          cellLeft = isNaN(cellLeft) ? 0 : cellLeft;
	          cellWidth = isNaN(cellWidth) ? 0 : cellWidth;
	          accumulator.push({
	            index: index + 1,
	            left: cellLeft + cellWidth
	          });
	        }

	        return accumulator;
	      }, []);
	      columnsPosition.forEach(function (item) {
	        var column = this.getColumnByIndex(item.index - fixedCells);
	        column.forEach(function (cell) {
	          if (item.index !== columnsPosition[columnsPosition.length - 1].index) {
	            cell.style.left = item.left + 'px';
	          }
	        });
	      }, this);
	      this.getAllRows().forEach(function (row) {
	        var height = BX.height(row);
	        var cells = [].slice.call(row.children);
	        cells.forEach(function (cell) {
	          cell.style.minHeight = height + 'px';
	        });
	      });
	    },
	    getLastStickyCellFromRowByIndex: function getLastStickyCellFromRowByIndex(index) {
	      return [].slice.call(this.getAllRows()[index].children).reduceRight(function (accumulator, cell) {
	        if (!accumulator && cell.classList.contains('main-grid-fixed-column')) {
	          accumulator = cell;
	        }

	        return accumulator;
	      }, null);
	    },
	    getFadeOffset: function getFadeOffset() {
	      var fadeOffset = 0;
	      var lastStickyCell = this.getLastStickyCellFromRowByIndex(0);

	      if (lastStickyCell) {
	        var lastStickyCellLeft = parseInt(BX.style(lastStickyCell, 'left'));
	        var lastStickyCellWidth = lastStickyCell.offsetWidth;
	        lastStickyCellLeft = isNaN(lastStickyCellLeft) ? 0 : lastStickyCellLeft;
	        lastStickyCellWidth = isNaN(lastStickyCellWidth) ? 0 : lastStickyCellWidth;
	        fadeOffset = lastStickyCellLeft + lastStickyCellWidth;
	      }

	      return fadeOffset;
	    },
	    adjustFadePosition: function adjustFadePosition(offset) {
	      var earLeft = this.getFader().getEarLeft();
	      var shadowLeft = this.getFader().getShadowLeft();
	      earLeft.style.left = offset + 'px';
	      shadowLeft.style.left = offset + 'px';
	    },

	    /**
	     * @param {string|object} column
	     */
	    sortByColumn: function sortByColumn(column) {
	      var headerCell = null;
	      var header = null;

	      if (!BX.type.isPlainObject(column)) {
	        headerCell = this.getColumnHeaderCellByName(column);
	        header = this.getColumnByName(column);
	      } else {
	        header = column;
	        header.sort_url = this.prepareSortUrl(column);
	      }

	      if (header && (!!headerCell && !BX.hasClass(headerCell, this.settings.get('classLoad')) || !headerCell)) {
	        !!headerCell && BX.addClass(headerCell, this.settings.get('classLoad'));
	        this.tableFade();
	        var self = this;
	        this.getUserOptions().setSort(header.sort_by, header.sort_order, function () {
	          self.getData().request(header.sort_url, null, null, 'sort', function () {
	            self.rows = null;
	            self.getUpdater().updateHeadRows(this.getHeadRows());
	            self.getUpdater().updateBodyRows(this.getBodyRows());
	            self.getUpdater().updatePagination(this.getPagination());
	            self.getUpdater().updateMoreButton(this.getMoreButton());
	            self.bindOnRowEvents();
	            self.bindOnMoreButtonEvents();
	            self.bindOnClickPaginationLinks();
	            self.bindOnCheckAll();
	            self.updateCounterDisplayed();
	            self.updateCounterSelected();
	            self.disableActionsPanel();
	            self.disableForAllCounter();

	            if (self.getParam('SHOW_ACTION_PANEL')) {
	              self.getActionsPanel().resetForAllCheckbox();
	            }

	            if (self.getParam('ALLOW_ROWS_SORT')) {
	              self.rowsSortable.reinit();
	            }

	            if (self.getParam('ALLOW_COLUMNS_SORT')) {
	              self.colsSortable.reinit();
	            }

	            BX.onCustomEvent(window, 'BX.Main.grid:sort', [header, self]);
	            BX.onCustomEvent(window, 'Grid::updated', [self]);
	            self.tableUnfade();
	          });
	        });
	      }
	    },
	    prepareSortUrl: function prepareSortUrl(header) {
	      var url = window.location.toString();

	      if ('sort_by' in header) {
	        url = BX.util.add_url_param(url, {
	          by: header.sort_by
	        });
	      }

	      if ('sort_order' in header) {
	        url = BX.util.add_url_param(url, {
	          order: header.sort_order
	        });
	      }

	      return url;
	    },
	    _clickOnSortableHeader: function _clickOnSortableHeader(header, event) {
	      event.preventDefault();
	      this.sortByColumn(BX.data(header, 'name'));
	    },
	    getObserver: function getObserver() {
	      return BX.Grid.observer;
	    },
	    initRowsDragAndDrop: function initRowsDragAndDrop() {
	      this.rowsSortable = new BX.Grid.RowsSortable(this);
	    },
	    initColsDragAndDrop: function initColsDragAndDrop() {
	      this.colsSortable = new BX.Grid.ColsSortable(this);
	    },

	    /**
	     * @return {BX.Grid.RowsSortable}
	     */
	    getRowsSortable: function getRowsSortable() {
	      return this.rowsSortable;
	    },

	    /**
	     * @return {BX.Grid.ColsSortable}
	     */
	    getColsSortable: function getColsSortable() {
	      return this.colsSortable;
	    },
	    getUserOptionsHandlerUrl: function getUserOptionsHandlerUrl() {
	      return this.userOptionsHandlerUrl || '';
	    },

	    /**
	     * @return {BX.Grid.UserOptions}
	     */
	    getUserOptions: function getUserOptions() {
	      return this.userOptions;
	    },
	    getCheckAllCheckboxes: function getCheckAllCheckboxes() {
	      var checkAllNodes = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCheckAllCheckboxes'));
	      return checkAllNodes.map(function (current) {
	        return new BX.Grid.Element(current);
	      });
	    },
	    selectAllCheckAllCheckboxes: function selectAllCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (current) {
	        current.getNode().checked = true;
	      });
	    },
	    unselectAllCheckAllCheckboxes: function unselectAllCheckAllCheckboxes() {
	      this.getCheckAllCheckboxes().forEach(function (current) {
	        current.getNode().checked = false;
	      });
	    },
	    adjustCheckAllCheckboxes: function adjustCheckAllCheckboxes() {
	      var total = this.getRows().getBodyChild().filter(function (row) {
	        return row.isShown() && !!row.getCheckbox();
	      }).length;
	      var selected = this.getRows().getSelected().filter(function (row) {
	        return row.isShown();
	      }).length;

	      if (total > 0 && selected > 0 && total === selected) {
	        this.selectAllCheckAllCheckboxes();
	      } else {
	        this.unselectAllCheckAllCheckboxes();
	      }

	      if (selected > 0 && selected < total) {
	        this.indeterminateCheckAllCheckboxes();
	      } else {
	        this.determinateCheckAllCheckboxes();
	      }
	    },
	    bindOnCheckAll: function bindOnCheckAll() {
	      var self = this;
	      this.getCheckAllCheckboxes().forEach(function (current) {
	        current.getObserver().add(current.getNode(), 'change', self._clickOnCheckAll, self);
	      });
	    },
	    _clickOnCheckAll: function _clickOnCheckAll(event) {
	      event.preventDefault();
	      this.toggleSelectionAll();
	      this.determinateCheckAllCheckboxes();
	    },
	    toggleSelectionAll: function toggleSelectionAll() {
	      if (!this.getRows().isAllSelected() && (this.lastRowAction === 'select' || !this.lastRowAction)) {
	        this.getRows().selectAll();
	        this.selectAllCheckAllCheckboxes();
	        this.enableActionsPanel();
	        BX.onCustomEvent(window, 'Grid::allRowsSelected', [this]);
	      } else {
	        this.getRows().unselectAll();
	        this.unselectAllCheckAllCheckboxes();
	        this.disableActionsPanel();
	        BX.onCustomEvent(window, 'Grid::allRowsUnselected', [this]);
	      }

	      delete this.lastRowAction;
	      this.updateCounterSelected();
	    },
	    bindOnClickPaginationLinks: function bindOnClickPaginationLinks() {
	      var self = this;
	      this.getPagination().getLinks().forEach(function (current) {
	        current.getObserver().add(current.getNode(), 'click', self._clickOnPaginationLink, self);
	      });
	    },
	    bindOnMoreButtonEvents: function bindOnMoreButtonEvents() {
	      var self = this;
	      this.getMoreButton().getObserver().add(this.getMoreButton().getNode(), 'click', self._clickOnMoreButton, self);
	    },
	    bindOnRowEvents: function bindOnRowEvents() {
	      var observer = this.getObserver();
	      var showCheckboxes = this.getParam('SHOW_ROW_CHECKBOXES');
	      var enableCollapsibleRows = this.getParam('ENABLE_COLLAPSIBLE_ROWS');
	      this.getRows().getBodyChild().forEach(function (current) {
	        showCheckboxes && observer.add(current.getNode(), 'click', this._onClickOnRow, this);
	        current.getDefaultAction() && observer.add(current.getNode(), 'dblclick', this._onRowDblclick, this);
	        current.getActionsButton() && observer.add(current.getActionsButton(), 'click', this._clickOnRowActionsButton, this);
	        enableCollapsibleRows && current.getCollapseButton() && observer.add(current.getCollapseButton(), 'click', this._onCollapseButtonClick, this);
	      }, this);
	    },
	    _onCollapseButtonClick: function _onCollapseButtonClick(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      var row = this.getRows().get(event.currentTarget);
	      row.toggleChildRows();

	      if (row.isCustom()) {
	        this.getUserOptions().setCollapsedGroups(this.getRows().getIdsCollapsedGroups());
	      } else {
	        this.getUserOptions().setExpandedRows(this.getRows().getIdsExpandedRows());
	      }

	      BX.fireEvent(document.body, 'click');
	    },
	    _clickOnRowActionsButton: function _clickOnRowActionsButton(event) {
	      var row = this.getRows().get(event.target);
	      event.preventDefault();

	      if (!row.actionsMenuIsShown()) {
	        row.showActionsMenu();
	      } else {
	        row.closeActionsMenu();
	      }
	    },
	    _onRowDblclick: function _onRowDblclick(event) {
	      event.preventDefault();
	      var row = this.getRows().get(event.target);
	      var defaultJs = '';

	      if (!row.isEdit()) {
	        clearTimeout(this.clickTimer);
	        this.clickPrevent = true;

	        try {
	          defaultJs = row.getDefaultAction();
	          eval(defaultJs);
	        } catch (err) {
	          console.warn(err);
	        }
	      }
	    },
	    _onClickOnRow: function _onClickOnRow(event) {
	      var clickDelay = 50;
	      var selection = window.getSelection();

	      if (event.target.nodeName === 'LABEL') {
	        event.preventDefault();
	      }

	      if (event.shiftKey || selection.toString().length === 0) {
	        if (event.shiftKey) {
	          selection.removeAllRanges();
	        }

	        this.clickTimer = setTimeout(BX.delegate(function () {
	          if (!this.clickPrevent) {
	            clickActions.apply(this, [event]);
	          }

	          this.clickPrevent = false;
	        }, this), clickDelay);
	      }

	      function clickActions(event) {
	        var rows, row, containsNotSelected, min, max, contentContainer;
	        var isPrevent = true;

	        if (event.target.nodeName !== 'A' && event.target.nodeName !== 'INPUT') {
	          row = this.getRows().get(event.target);

	          if (row) {
	            contentContainer = row.getContentContainer(event.target);

	            if (BX.type.isDomNode(contentContainer) && event.target.nodeName !== 'TD' && event.target !== contentContainer) {
	              isPrevent = BX.data(contentContainer, 'prevent-default') === 'true';
	            }

	            if (isPrevent) {
	              if (row.getCheckbox()) {
	                rows = [];
	                this.currentIndex = 0;
	                this.getRows().getRows().forEach(function (currentRow, index) {
	                  if (currentRow === row) {
	                    this.currentIndex = index;
	                  }
	                }, this);
	                this.lastIndex = this.lastIndex || this.currentIndex;

	                if (!event.shiftKey) {
	                  if (!row.isSelected()) {
	                    this.lastRowAction = 'select';
	                    row.select();
	                    BX.onCustomEvent(window, 'Grid::selectRow', [row, this]);
	                  } else {
	                    this.lastRowAction = 'unselect';
	                    row.unselect();
	                    BX.onCustomEvent(window, 'Grid::unselectRow', [row, this]);
	                  }
	                } else {
	                  min = Math.min(this.currentIndex, this.lastIndex);
	                  max = Math.max(this.currentIndex, this.lastIndex);

	                  while (min <= max) {
	                    rows.push(this.getRows().getRows()[min]);
	                    min++;
	                  }

	                  containsNotSelected = rows.some(function (current) {
	                    return !current.isSelected();
	                  });

	                  if (containsNotSelected) {
	                    rows.forEach(function (current) {
	                      current.select();
	                    });
	                    this.lastRowAction = 'select';
	                    BX.onCustomEvent(window, 'Grid::selectRows', [rows, this]);
	                  } else {
	                    rows.forEach(function (current) {
	                      current.unselect();
	                    });
	                    this.lastRowAction = 'unselect';
	                    BX.onCustomEvent(window, 'Grid::unselectRows', [rows, this]);
	                  }
	                }

	                this.updateCounterSelected();
	                this.lastIndex = this.currentIndex;
	              }

	              this.adjustRows();
	              this.adjustCheckAllCheckboxes();
	            }
	          }
	        }
	      }
	    },
	    adjustRows: function adjustRows() {
	      if (this.getRows().isSelected()) {
	        BX.onCustomEvent(window, 'Grid::thereSelectedRows', [this]);
	        this.enableActionsPanel();
	      } else {
	        BX.onCustomEvent(window, 'Grid::noSelectedRows', []);
	        this.disableActionsPanel();
	      }
	    },
	    getPagination: function getPagination() {
	      return new BX.Grid.Pagination(this);
	    },
	    getState: function getState() {
	      return window.history.state;
	    },
	    tableFade: function tableFade() {
	      BX.addClass(this.getTable(), this.settings.get('classTableFade'));
	      this.getLoader().show();
	      BX.onCustomEvent('Grid::disabled', [this]);
	    },
	    tableUnfade: function tableUnfade() {
	      BX.removeClass(this.getTable(), this.settings.get('classTableFade'));
	      this.getLoader().hide();
	      BX.onCustomEvent('Grid::enabled', [this]);
	    },
	    _clickOnPaginationLink: function _clickOnPaginationLink(event) {
	      event.preventDefault();
	      var self = this;
	      var link = this.getPagination().getLink(event.target);

	      if (!link.isLoad()) {
	        this.getUserOptions().resetExpandedRows();
	        link.load();
	        this.tableFade();
	        this.getData().request(link.getLink(), null, null, 'pagination', function () {
	          self.rows = null;
	          self.getUpdater().updateBodyRows(this.getBodyRows());
	          self.getUpdater().updateHeadRows(this.getHeadRows());
	          self.getUpdater().updateMoreButton(this.getMoreButton());
	          self.getUpdater().updatePagination(this.getPagination());
	          self.bindOnRowEvents();
	          self.bindOnMoreButtonEvents();
	          self.bindOnClickPaginationLinks();
	          self.bindOnCheckAll();
	          self.updateCounterDisplayed();
	          self.updateCounterSelected();
	          self.disableActionsPanel();
	          self.disableForAllCounter();

	          if (self.getParam('SHOW_ACTION_PANEL')) {
	            self.getActionsPanel().resetForAllCheckbox();
	          }

	          if (self.getParam('ALLOW_ROWS_SORT')) {
	            self.rowsSortable.reinit();
	          }

	          if (self.getParam('ALLOW_COLUMNS_SORT')) {
	            self.colsSortable.reinit();
	          }

	          link.unload();
	          self.tableUnfade();
	          BX.onCustomEvent(window, 'Grid::updated', [self]);
	        });
	      }
	    },
	    _clickOnMoreButton: function _clickOnMoreButton(event) {
	      event.preventDefault();
	      var self = this;
	      var moreButton = this.getMoreButton();
	      moreButton.load();
	      this.getData().request(moreButton.getLink(), null, null, 'more', function () {
	        self.getUpdater().appendBodyRows(this.getBodyRows());
	        self.getUpdater().updateMoreButton(this.getMoreButton());
	        self.getUpdater().updatePagination(this.getPagination());
	        self.getRows().reset();
	        self.bindOnRowEvents();
	        self.bindOnMoreButtonEvents();
	        self.bindOnClickPaginationLinks();
	        self.bindOnCheckAll();
	        self.updateCounterDisplayed();
	        self.updateCounterSelected();

	        if (self.getParam('ALLOW_PIN_HEADER')) {
	          self.getPinHeader()._onGridUpdate();
	        }

	        if (self.getParam('ALLOW_ROWS_SORT')) {
	          self.rowsSortable.reinit();
	        }

	        if (self.getParam('ALLOW_COLUMNS_SORT')) {
	          self.colsSortable.reinit();
	        }

	        self.unselectAllCheckAllCheckboxes();
	      });
	    },
	    getAjaxId: function getAjaxId() {
	      return BX.data(this.getContainer(), this.settings.get('ajaxIdDataProp'));
	    },
	    update: function update(data, action) {
	      var newRows, newHeadRows, newNavPanel, thisBody, thisHead, thisNavPanel;

	      if (!BX.type.isNotEmptyString(data)) {
	        return;
	      }

	      thisBody = BX.Grid.Utils.getByTag(this.getTable(), 'tbody', true);
	      thisHead = BX.Grid.Utils.getByTag(this.getTable(), 'thead', true);
	      thisNavPanel = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classNavPanel'), true);
	      data = BX.create('div', {
	        html: data
	      });
	      newHeadRows = BX.Grid.Utils.getByClass(data, this.settings.get('classHeadRow'));
	      newRows = BX.Grid.Utils.getByClass(data, this.settings.get('classDataRows'));
	      newNavPanel = BX.Grid.Utils.getByClass(data, this.settings.get('classNavPanel'), true);

	      if (action === this.settings.get('updateActionMore')) {
	        this.getRows().addRows(newRows);
	        this.unselectAllCheckAllCheckboxes();
	      }

	      if (action === this.settings.get('updateActionPagination')) {
	        BX.cleanNode(thisBody);
	        this.getRows().addRows(newRows);
	        this.unselectAllCheckAllCheckboxes();
	      }

	      if (action === this.settings.get('updateActionSort')) {
	        BX.cleanNode(thisHead);
	        BX.cleanNode(thisBody);
	        thisHead.appendChild(newHeadRows[0]);
	        this.getRows().addRows(newRows);
	      }

	      thisNavPanel.innerHTML = newNavPanel.innerHTML;
	      this.bindOnRowEvents();
	      this.bindOnMoreButtonEvents();
	      this.bindOnClickPaginationLinks();
	      this.bindOnClickHeader();
	      this.bindOnCheckAll();
	      this.updateCounterDisplayed();
	      this.updateCounterSelected();
	      this.sortable.reinit();
	    },
	    getCounterDisplayed: function getCounterDisplayed() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterDisplayed'));
	    },
	    getCounterSelected: function getCounterSelected() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterSelected'));
	    },
	    updateCounterDisplayed: function updateCounterDisplayed() {
	      var counterDisplayed = this.getCounterDisplayed();
	      var rows;

	      if (BX.type.isArray(counterDisplayed)) {
	        rows = this.getRows();
	        counterDisplayed.forEach(function (current) {
	          if (BX.type.isDomNode(current)) {
	            current.innerText = rows.getCountDisplayed();
	          }
	        }, this);
	      }
	    },
	    updateCounterSelected: function updateCounterSelected() {
	      var counterSelected = this.getCounterSelected();
	      var rows;

	      if (BX.type.isArray(counterSelected)) {
	        rows = this.getRows();
	        counterSelected.forEach(function (current) {
	          if (BX.type.isDomNode(current)) {
	            current.innerText = rows.getCountSelected();
	          }
	        }, this);
	      }
	    },
	    getContainerId: function getContainerId() {
	      return this.containerId;
	    },
	    getId: function getId() {
	      //ID is equals to container Id
	      return this.containerId;
	    },
	    getContainer: function getContainer() {
	      return BX(this.getContainerId());
	    },
	    getCounter: function getCounter() {
	      if (!this.counter) {
	        this.counter = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounter'));
	      }

	      return this.counter;
	    },
	    enableForAllCounter: function enableForAllCounter() {
	      var counter = this.getCounter();

	      if (BX.type.isArray(counter)) {
	        counter.forEach(function (current) {
	          BX.addClass(current, this.settings.get('classForAllCounterEnabled'));
	        }, this);
	      }
	    },
	    disableForAllCounter: function disableForAllCounter() {
	      var counter = this.getCounter();

	      if (BX.type.isArray(counter)) {
	        counter.forEach(function (current) {
	          BX.removeClass(current, this.settings.get('classForAllCounterEnabled'));
	        }, this);
	      }
	    },
	    getScrollContainer: function getScrollContainer() {
	      if (!this.scrollContainer) {
	        this.scrollContainer = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classScrollContainer'), true);
	      }

	      return this.scrollContainer;
	    },
	    getWrapper: function getWrapper() {
	      if (!this.wrapper) {
	        this.wrapper = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classWrapper'), true);
	      }

	      return this.wrapper;
	    },
	    getFadeContainer: function getFadeContainer() {
	      if (!this.fadeContainer) {
	        this.fadeContainer = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classFadeContainer'), true);
	      }

	      return this.fadeContainer;
	    },
	    getTable: function getTable() {
	      return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classTable'), true);
	    },
	    getHeaders: function getHeaders() {
	      return BX.Grid.Utils.getBySelector(this.getWrapper(), '.main-grid-header[data-relative="' + this.getContainerId() + '"]');
	    },
	    getHead: function getHead() {
	      return BX.Grid.Utils.getByTag(this.getContainer(), 'thead', true);
	    },
	    getBody: function getBody() {
	      return BX.Grid.Utils.getByTag(this.getContainer(), 'tbody', true);
	    },
	    getFoot: function getFoot() {
	      return BX.Grid.Utils.getByTag(this.getContainer(), 'tfoot', true);
	    },

	    /**
	     * @return {BX.Grid.Rows}
	     */
	    getRows: function getRows() {
	      if (!(this.rows instanceof BX.Grid.Rows)) {
	        this.rows = new BX.Grid.Rows(this);
	      }

	      return this.rows;
	    },
	    getMoreButton: function getMoreButton() {
	      var node = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classMoreButton'), true);
	      return new BX.Grid.Element(node, this);
	    },

	    /**
	     * Gets loader instance
	     * @return {BX.Grid.Loader}
	     */
	    getLoader: function getLoader() {
	      if (!(this.loader instanceof BX.Grid.Loader)) {
	        this.loader = new BX.Grid.Loader(this);
	      }

	      return this.loader;
	    },
	    blockSorting: function blockSorting() {
	      var headerCells = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classHeadCell'));
	      headerCells.forEach(function (header) {
	        if (this.isSortableHeader(header)) {
	          BX.removeClass(header, this.settings.get('classHeaderSortable'));
	          BX.addClass(header, this.settings.get('classHeaderNoSortable'));
	        }
	      }, this);
	    },
	    unblockSorting: function unblockSorting() {
	      var headerCells = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classHeadCell'));
	      headerCells.forEach(function (header) {
	        if (this.isNoSortableHeader(header) && header.dataset.sortBy) {
	          BX.addClass(header, this.settings.get('classHeaderSortable'));
	          BX.removeClass(header, this.settings.get('classHeaderNoSortable'));
	        }
	      }, this);
	    },
	    confirmDialog: function confirmDialog(action, then, cancel) {
	      var dialog, popupContainer, applyButton, cancelButton;

	      if ('CONFIRM' in action && action.CONFIRM) {
	        action.CONFIRM_MESSAGE = action.CONFIRM_MESSAGE || this.arParams.CONFIRM_MESSAGE;
	        action.CONFIRM_APPLY_BUTTON = action.CONFIRM_APPLY_BUTTON || this.arParams.CONFIRM_APPLY;
	        action.CONFIRM_CANCEL_BUTTON = action.CONFIRM_CANCEL_BUTTON || this.arParams.CONFIRM_CANCEL;
	        dialog = new BX.PopupWindow(this.getContainerId() + '-confirm-dialog', null, {
	          content: '<div class="main-grid-confirm-content">' + action.CONFIRM_MESSAGE + '</div>',
	          titleBar: 'CONFIRM_TITLE' in action ? action.CONFIRM_TITLE : '',
	          autoHide: false,
	          zIndex: 9999,
	          overlay: 0.4,
	          offsetTop: -100,
	          closeIcon: true,
	          closeByEsc: true,
	          events: {
	            onClose: function onClose() {
	              BX.unbind(window, 'keydown', hotKey);
	            }
	          },
	          buttons: [new BX.PopupWindowButton({
	            text: action.CONFIRM_APPLY_BUTTON,
	            id: this.getContainerId() + '-confirm-dialog-apply-button',
	            events: {
	              click: function click() {
	                BX.type.isFunction(then) ? then() : null;
	                this.popupWindow.close();
	                this.popupWindow.destroy();
	                BX.onCustomEvent(window, 'Grid::confirmDialogApply', [this]);
	                BX.unbind(window, 'keydown', hotKey);
	              }
	            }
	          }), new BX.PopupWindowButtonLink({
	            text: action.CONFIRM_CANCEL_BUTTON,
	            id: this.getContainerId() + '-confirm-dialog-cancel-button',
	            events: {
	              click: function click() {
	                BX.type.isFunction(cancel) ? cancel() : null;
	                this.popupWindow.close();
	                this.popupWindow.destroy();
	                BX.onCustomEvent(window, 'Grid::confirmDialogCancel', [this]);
	                BX.unbind(window, 'keydown', hotKey);
	              }
	            }
	          })]
	        });

	        if (!dialog.isShown()) {
	          dialog.show();
	          popupContainer = dialog.popupContainer;
	          BX.removeClass(popupContainer, this.settings.get('classCloseAnimation'));
	          BX.addClass(popupContainer, this.settings.get('classShowAnimation'));
	          applyButton = BX(this.getContainerId() + '-confirm-dialog-apply-button');
	          cancelButton = BX(this.getContainerId() + '-confirm-dialog-cancel-button');
	          BX.bind(window, 'keydown', hotKey);
	        }
	      } else {
	        BX.type.isFunction(then) ? then() : null;
	      }

	      function hotKey(event) {
	        if (event.code === 'Enter') {
	          event.preventDefault();
	          event.stopPropagation();
	          BX.fireEvent(applyButton, 'click');
	        }

	        if (event.code === 'Escape') {
	          event.preventDefault();
	          event.stopPropagation();
	          BX.fireEvent(cancelButton, 'click');
	        }
	      }
	    },
	    getCurrentPage: function getCurrentPage() {
	      var currentPage = parseInt(this.arParams.CURRENT_PAGE);

	      if (BX.Type.isNumber(currentPage)) {
	        return currentPage;
	      }

	      return 0;
	    },

	    /**
	     * @private
	     * @return {Element | any}
	     */
	    getEmptyStub: function getEmptyStub() {
	      return this.getTable().querySelector('.main-grid-row-empty');
	    },

	    /**
	     * @private
	     */
	    showEmptyStub: function showEmptyStub() {
	      var stub = this.getEmptyStub();

	      if (stub) {
	        BX.Dom.attr(stub, 'hidden', null);

	        if (this.getCurrentPage() <= 1) {
	          BX.Dom.hide(this.getPanels());
	        }
	      }
	    },

	    /**
	     * @private
	     */
	    hideEmptyStub: function hideEmptyStub() {
	      var stub = this.getEmptyStub();

	      if (stub) {
	        BX.Dom.attr(stub, 'hidden', true);
	        BX.Dom.style(this.getTable(), 'min-height', null);
	        BX.Dom.show(this.getPanels());
	      }
	    },

	    /**
	     * @return {BX.Grid.Row}
	     */
	    getTemplateRow: function getTemplateRow() {
	      var templateRow = BX.Runtime.clone(this.getRows().getBodyChild(true).find(function (row) {
	        return row.getId() === 'template_0';
	      }));
	      var cloned = BX.Runtime.clone(templateRow.getNode());
	      BX.Dom.prepend(cloned, this.getBody());
	      var checkbox = cloned.querySelector('[type="checkbox"]');

	      if (checkbox) {
	        BX.Dom.attr(checkbox, 'disabled', null);
	        BX.Dom.attr(checkbox, 'data-disabled', null);
	      }

	      return new BX.Grid.Row(this, cloned);
	    },

	    /**
	     * @private
	     * @return {{}[]}
	     */
	    getRowEditorValue: function getRowEditorValue(withTemplate) {
	      this.rows = null;
	      return this.getRows().getSelected(withTemplate).map(function (row) {
	        return row.getEditorValue();
	      });
	    },

	    /**
	     * @private
	     * @return {HTMLElement|HTMLBodyElement}
	     */
	    getRowEditorActionPanel: function getRowEditorActionPanel() {
	      if (!this.rowEditorActionPanel) {
	        this.rowEditorActionPanel = BX.Dom.create({
	          tag: 'div',
	          props: {
	            className: 'main-ui-grid-row-editor-actions-panel'
	          },
	          children: [BX.Dom.create({
	            tag: 'span',
	            props: {
	              className: 'ui-btn ui-btn-success'
	            },
	            text: this.arParams.SAVE_BUTTON_LABEL,
	            events: {
	              click: this.saveRows.bind(this)
	            }
	          }), BX.Dom.create({
	            tag: 'span',
	            props: {
	              className: 'ui-btn ui-btn-link'
	            },
	            text: this.arParams.CANCEL_BUTTON_LABEL,
	            events: {
	              click: this.hideRowsEditor.bind(this)
	            }
	          })]
	        });
	      }

	      return this.rowEditorActionPanel;
	    },

	    /**
	     * @private
	     */
	    showRowEditorActionsPanel: function showRowEditorActionsPanel() {
	      var panel = this.getRowEditorActionPanel();
	      BX.Dom.append(panel, this.actionPanel.getPanel());
	    },

	    /**
	     * @private
	     */
	    hideRowEditorActionsPanel: function hideRowEditorActionsPanel() {
	      BX.Dom.remove(this.getRowEditorActionPanel());
	    },

	    /**
	     * @return {BX.Grid.Row}
	     */
	    prependRowEditor: function prependRowEditor() {
	      return this.addRowEditor('prepend');
	    },

	    /**
	     * @return {BX.Grid.Row}
	     */
	    appendRowEditor: function appendRowEditor() {
	      return this.addRowEditor('append');
	    },

	    /**
	     * @return {BX.Grid.Row}
	     */
	    addRowEditor: function addRowEditor() {
	      var direction = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'prepend';
	      BX.Dom.style(this.getTable(), 'min-height', null);
	      var templateRow = this.getTemplateRow();
	      this.editableRows.push(templateRow);

	      if (direction === 'prepend') {
	        templateRow.prependTo(this.getBody());
	      } else {
	        templateRow.appendTo(this.getBody());
	      }

	      templateRow.show();
	      templateRow.select();
	      templateRow.edit();
	      this.getRows().reset();

	      if (this.getParam('ALLOW_ROWS_SORT')) {
	        this.rowsSortable.reinit();
	      }

	      if (this.getParam('ALLOW_COLUMNS_SORT')) {
	        this.colsSortable.reinit();
	      }

	      this.hideEmptyStub();
	      return templateRow;
	    },
	    hideRowsEditor: function hideRowsEditor() {
	      this.editableRows.forEach(function (row) {
	        BX.Dom.remove(row.getNode());
	      });
	      this.editableRows = [];
	    },
	    saveRows: function saveRows() {
	      var _this = this;

	      var value = this.getRowEditorValue(true);
	      this.emitAsync('onAddRowsAsync', {
	        rows: value
	      }).then(function (result) {
	        result.forEach(function (rowData, rowIndex) {
	          var row = _this.editableRows[rowIndex];

	          if (row) {
	            row.editCancel();
	            row.unselect();
	            row.makeCountable();
	            row.setId(rowData.id);
	            row.setActions(rowData.actions);
	            row.setCellsContent(rowData.columns);
	          }
	        });

	        _this.bindOnRowEvents();

	        _this.updateCounterDisplayed();

	        _this.updateCounterSelected();

	        _this.editableRows = [];
	      });
	    },
	    getRealtime: function getRealtime() {
	      var _this2 = this;

	      return this.cache.remember('realtime', function () {
	        return new BX.Grid.Realtime({
	          grid: _this2
	        });
	      });
	    }
	  };
	})();

}((this.window = this.window || {}),BX.Event,BX.UI,BX));
//# sourceMappingURL=script.js.map
