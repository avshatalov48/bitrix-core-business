this.BX = this.BX || {};
(function (exports,ui_entitySelector,main_core_events,main_popup,main_core) {
	'use strict';

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['date-group'] = function (data) {
	    var group, select, deleteButton, label, dragButton;
	    group = {
	      block: 'main-ui-control-field-group',
	      name: data.name + '_datesel',
	      mix: 'mix' in data ? data.mix : null,
	      attrs: {
	        'data-type': 'type' in data ? data.type : '',
	        'data-name': 'name' in data ? data.name : '',
	        'data-time': data.enableTime
	      },
	      content: []
	    };

	    if ('label' in data && BX.type.isNotEmptyString(data.label)) {
	      var labelContent = data.label;

	      if ('icon' in data && BX.Type.isPlainObject(data.icon)) {
	        labelContent = [{
	          block: 'main-ui-control-field-label-icon',
	          tag: 'img',
	          attrs: {
	            title: data.icon.title ? data.icon.title : '',
	            src: data.icon.url
	          }
	        }, {
	          block: 'main-ui-control-field-label-text',
	          tag: 'span',
	          content: labelContent
	        }];
	      }

	      label = {
	        block: 'main-ui-control-field-label',
	        tag: 'span',
	        attrs: {
	          title: data.label
	        },
	        content: labelContent
	      };
	      group.content.push(label);
	    }

	    select = {
	      block: 'main-ui-control-field',
	      dragButton: false,
	      content: {
	        block: 'main-ui-select',
	        tabindex: 'tabindex' in data ? data.tabindex : '',
	        value: 'value' in data ? data.value : '',
	        items: 'items' in data ? data.items : '',
	        name: 'name' in data ? data.name + '_datesel' : '',
	        params: 'params' in data ? data.params : '',
	        valueDelete: false
	      }
	    };
	    group.content.push(select);

	    if ('content' in data && BX.type.isArray(data.content)) {
	      data.content.forEach(function (current) {
	        group.content.push(current);
	      });
	    }

	    if ('content' in data && (BX.type.isPlainObject(data.content) || BX.type.isNotEmptyString(data.content))) {
	      group.content.push(data.content);
	    }

	    deleteButton = {
	      block: 'main-ui-item-icon-container',
	      content: {
	        block: 'main-ui-item-icon',
	        mix: ['main-ui-delete', 'main-ui-filter-field-delete'],
	        tag: 'span',
	        attrs: {
	          title: 'deleteTitle' in data && data.deleteTitle ? data.deleteTitle : ''
	        }
	      }
	    };
	    group.content.push(deleteButton);

	    if (!('dragButton' in data) || data.dragButton !== false) {
	      dragButton = {
	        block: 'main-ui-filter-icon-grab',
	        mix: ['main-ui-item-icon'],
	        tag: 'span',
	        attrs: {
	          title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
	        }
	      };
	      group.content.push(dragButton);
	    }

	    return group;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-control-field'] = function (data) {
	    var field, deleteButton, valueDeleteButton, label, dragButton;
	    field = {
	      block: 'main-ui-control-field',
	      mix: 'mix' in data ? data.mix : null,
	      attrs: {
	        'data-type': 'type' in data ? data.type : '',
	        'data-name': 'name' in data ? data.name : ''
	      },
	      content: []
	    };

	    if ('label' in data && BX.type.isNotEmptyString(data.label)) {
	      var labelContent = data.label;

	      if ('icon' in data && BX.Type.isPlainObject(data.icon)) {
	        labelContent = [{
	          block: 'main-ui-control-field-label-icon',
	          tag: 'img',
	          attrs: {
	            title: data.icon.title ? data.icon.title : '',
	            src: data.icon.url
	          }
	        }, {
	          block: 'main-ui-control-field-label-text',
	          tag: 'span',
	          content: labelContent
	        }];
	      }

	      label = {
	        block: 'main-ui-control-field-label',
	        tag: 'span',
	        attrs: {
	          title: data.label
	        },
	        content: labelContent
	      };
	      field.content.push(label);
	    }

	    if (BX.type.isArray(data.content)) {
	      data.content.forEach(function (current) {
	        field.content.push(current);
	      });
	    } else if (BX.type.isPlainObject(data.content) || BX.type.isNotEmptyString(data.content)) {
	      field.content.push(data.content);
	    }

	    if ('valueDelete' in data && data.valueDelete === true) {
	      valueDeleteButton = {
	        block: 'main-ui-control-value-delete',
	        content: {
	          block: 'main-ui-control-value-delete-item',
	          tag: 'span'
	        }
	      };
	      field.content.push(valueDeleteButton);
	    }

	    if ('deleteButton' in data && data.deleteButton === true) {
	      deleteButton = {
	        block: 'main-ui-item-icon-container',
	        content: {
	          block: 'main-ui-item-icon',
	          mix: ['main-ui-delete', 'main-ui-filter-field-delete'],
	          tag: 'span',
	          attrs: {
	            title: 'deleteTitle' in data && data.deleteTitle ? data.deleteTitle : ''
	          }
	        }
	      };
	      field.content.push(deleteButton);
	    }

	    if (!('dragButton' in data) || data.dragButton !== false) {
	      dragButton = {
	        block: 'main-ui-filter-icon-grab',
	        mix: ['main-ui-item-icon'],
	        tag: 'span',
	        attrs: {
	          title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
	        }
	      };
	      field.content.push(dragButton);
	    }

	    return field;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-control-field-group'] = function (data) {
	    var field, deleteButton, label, dragButton;
	    field = {
	      block: 'main-ui-control-field-group',
	      mix: 'mix' in data ? data.mix : null,
	      attrs: {
	        'data-type': 'type' in data ? data.type : '',
	        'data-name': 'name' in data ? data.name : ''
	      },
	      content: []
	    };

	    if ('label' in data && BX.type.isNotEmptyString(data.label)) {
	      var labelContent = data.label;

	      if ('icon' in data && BX.Type.isPlainObject(data.icon)) {
	        labelContent = [{
	          block: 'main-ui-control-field-label-icon',
	          tag: 'img',
	          attrs: {
	            title: data.icon.title ? data.icon.title : '',
	            src: data.icon.url
	          }
	        }, {
	          block: 'main-ui-control-field-label-text',
	          tag: 'span',
	          content: labelContent
	        }];
	      }

	      label = {
	        block: 'main-ui-control-field-label',
	        tag: 'span',
	        attrs: {
	          title: data.label
	        },
	        content: labelContent
	      };
	      field.content.push(label);
	    }

	    if (BX.type.isArray(data.content)) {
	      data.content.forEach(function (current) {
	        field.content.push(current);
	      });
	    } else if (BX.type.isPlainObject(data.content) || BX.type.isNotEmptyString(data.content)) {
	      field.content.push(data.content);
	    }

	    if ('deleteButton' in data && data.deleteButton === true) {
	      deleteButton = {
	        block: 'main-ui-item-icon-container',
	        content: {
	          block: 'main-ui-item-icon',
	          mix: ['main-ui-delete', 'main-ui-filter-field-delete'],
	          tag: 'span',
	          attrs: {
	            title: 'deleteTitle' in data && data.deleteTitle ? data.deleteTitle : ''
	          }
	        }
	      };
	      field.content.push(deleteButton);
	    }

	    if (!('dragButton' in data) || data.dragButton !== false) {
	      dragButton = {
	        block: 'main-ui-filter-icon-grab',
	        mix: ['main-ui-item-icon'],
	        tag: 'span',
	        attrs: {
	          title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
	        }
	      };
	      field.content.push(dragButton);
	    }

	    return field;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-control-string'] = function (data) {
	    return {
	      block: 'main-ui-control-string',
	      mix: ['main-ui-control'],
	      tag: 'input',
	      attrs: {
	        type: 'type' in data ? data.type : 'text',
	        name: 'name' in data ? data.name : '',
	        placeholder: 'placeholder' in data ? data.placeholder : '',
	        tabindex: 'tabindex' in data ? data.tabindex : '',
	        value: 'value' in data ? data.value : ''
	      }
	    };
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-control-textarea'] = function (data) {
	    return {
	      block: 'main-ui-control-string',
	      mix: ['main-ui-control main-ui-control-textarea'],
	      tag: 'textarea',
	      attrs: {
	        name: 'name' in data ? data.name : '',
	        placeholder: 'placeholder' in data ? data.placeholder : '',
	        tabindex: 'tabindex' in data ? data.tabindex : ''
	      },
	      content: 'value' in data ? data.value : ''
	    };
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-filter-field-list-item'] = function (data) {
	    var label = {
	      block: 'main-ui-select-inner-label',
	      content: 'label' in data ? data.label : ''
	    };
	    var item = {
	      block: 'main-ui-filter-field-list-item',
	      mix: 'main-ui-select-inner-item',
	      attrs: {
	        'data-id': data.id,
	        'data-name': data.name,
	        'data-item': 'item' in data ? JSON.stringify(data.item) : {}
	      },
	      events: {
	        click: 'onClick' in data ? data.onClick : ''
	      },
	      content: label
	    };
	    return item;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-filter-info'] = function (data) {
	    return {
	      block: 'main-ui-filter-info',
	      tag: 'span',
	      content: data.content,
	      attrs: {
	        title: data.title
	      }
	    };
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-number'] = function (data) {
	    var control, input, valueDelete;
	    control = {
	      block: 'main-ui-number',
	      mix: ['main-ui-control'],
	      content: []
	    };

	    if ('mix' in data && BX.type.isArray(data.mix)) {
	      data.mix.forEach(function (current) {
	        control.mix.push(current);
	      });
	    }

	    input = {
	      block: 'main-ui-number-input',
	      mix: ['main-ui-control-input'],
	      tag: 'input',
	      attrs: {
	        type: 'number',
	        name: 'name' in data ? data.name : '',
	        tabindex: 'tabindex' in data ? data.tabindex : '',
	        value: 'value' in data ? data.value : '',
	        placeholder: 'placeholder' in data ? data.placeholder : '',
	        autocomplete: 'off'
	      }
	    };
	    control.content.push(input);

	    if ('valueDelete' in data && data.valueDelete === true) {
	      valueDelete = {
	        block: 'main-ui-control-value-delete',
	        content: {
	          block: 'main-ui-control-value-delete-item',
	          tag: 'span'
	        }
	      };
	      control.content.push(valueDelete);
	    }

	    return control;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['main-ui-search-square'] = function (data) {
	    var mix = ['main-ui-filter-search-square'];

	    if ('isPreset' in data && data.isPreset) {
	      mix.push('main-ui-filter-search-square-preset');
	    }

	    var title = 'title' in data ? data.title : '';
	    var name = 'name' in data ? BX.util.htmlspecialcharsback(data.name) : '';

	    if ('icon' in data && BX.Type.isPlainObject(data.icon)) {
	      var iconTitle = data.icon.title;
	      title = title.length ? iconTitle + ': ' + title : '';
	      name = name.length ? iconTitle + ': ' + name : '';
	    }

	    return {
	      block: 'main-ui-square',
	      mix: mix,
	      attrs: {
	        'data-item': 'item' in data ? JSON.stringify(data.item) : '',
	        'title': title
	      },
	      content: [{
	        block: 'main-ui-square-item',
	        content: name
	      }, {
	        block: 'main-ui-square-delete',
	        mix: ['main-ui-item-icon']
	      }]
	    };
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['number-group'] = function (data) {
	    var group, select, deleteButton, label, dragButton;
	    group = {
	      block: 'main-ui-control-field-group',
	      name: 'name' in data ? data.name + '_numsel' : '',
	      mix: 'mix' in data ? data.mix : null,
	      attrs: {
	        'data-type': 'type' in data ? data.type : '',
	        'data-name': 'name' in data ? data.name : ''
	      },
	      content: []
	    };

	    if ('label' in data && BX.type.isNotEmptyString(data.label)) {
	      var labelContent = data.label;

	      if ('icon' in data && BX.Type.isPlainObject(data.icon)) {
	        labelContent = [{
	          block: 'main-ui-control-field-label-icon',
	          tag: 'img',
	          attrs: {
	            title: data.icon.title ? data.icon.title : '',
	            src: data.icon.url
	          }
	        }, {
	          block: 'main-ui-control-field-label-text',
	          tag: 'span',
	          content: labelContent
	        }];
	      }

	      label = {
	        block: 'main-ui-control-field-label',
	        tag: 'span',
	        attrs: {
	          title: data.label
	        },
	        content: labelContent
	      };
	      group.content.push(label);
	    }

	    select = {
	      block: 'main-ui-control-field',
	      dragButton: false,
	      content: {
	        block: 'main-ui-select',
	        tabindex: 'tabindex' in data ? data.tabindex : '',
	        value: 'value' in data ? data.value : '',
	        items: 'items' in data ? data.items : '',
	        name: 'name' in data ? data.name + '_numsel' : '',
	        params: 'params' in data ? data.params : '',
	        valueDelete: false
	      }
	    };
	    group.content.push(select);

	    if ('content' in data && BX.type.isArray(data.content)) {
	      data.content.forEach(function (current) {
	        group.content.push(current);
	      });
	    }

	    if ('content' in data && (BX.type.isPlainObject(data.content) || BX.type.isNotEmptyString(data.content))) {
	      group.content.push(data.content);
	    }

	    deleteButton = {
	      block: 'main-ui-item-icon-container',
	      content: {
	        block: 'main-ui-item-icon',
	        mix: ['main-ui-delete', 'main-ui-filter-field-delete'],
	        tag: 'span',
	        attrs: {
	          title: 'deleteTitle' in data && data.deleteTitle ? data.deleteTitle : ''
	        }
	      }
	    };
	    group.content.push(deleteButton);

	    if (!('dragButton' in data) || data.dragButton !== false) {
	      dragButton = {
	        block: 'main-ui-filter-icon-grab',
	        mix: ['main-ui-item-icon'],
	        tag: 'span',
	        attrs: {
	          title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
	        }
	      };
	      group.content.push(dragButton);
	    }

	    return group;
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui.block');

	  BX.Main.ui.block['sidebar-item'] = function (data) {
	    return {
	      block: 'main-ui-filter-sidebar-item' + ('pinned' in data && data.pinned ? ' main-ui-item-pin' : ''),
	      attrs: {
	        'data-id': 'id' in data ? data.id : ''
	      },
	      content: [{
	        block: 'main-ui-filter-icon-grab',
	        tag: 'span',
	        mix: ['main-ui-item-icon'],
	        attrs: {
	          title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
	        }
	      }, {
	        block: 'main-ui-filter-sidebar-item-text-container',
	        tag: 'span',
	        content: [{
	          block: 'main-ui-filter-sidebar-item-input',
	          tag: 'input',
	          attrs: {
	            type: 'text',
	            placeholder: 'placeholder' in data ? data.placeholder : '',
	            value: 'text' in data ? BX.util.htmlspecialchars(BX.util.htmlspecialcharsback(data.text)) : ''
	          }
	        }, {
	          block: 'main-ui-filter-sidebar-item-text',
	          tag: 'span',
	          content: 'text' in data ? data.text : '',
	          attrs: {
	            title: 'text' in data ? data.text : ''
	          }
	        }, {
	          block: 'main-ui-filter-icon-pin',
	          tag: 'span',
	          mix: ['main-ui-item-icon'],
	          attrs: {
	            title: 'noEditPinTitle' in data && data.noEditPinTitle ? data.noEditPinTitle : ''
	          }
	        }]
	      }, {
	        block: 'main-ui-filter-icon-edit',
	        tag: 'span',
	        mix: ['main-ui-item-icon'],
	        attrs: {
	          title: 'editNameTitle' in data && data.editNameTitle ? data.editNameTitle : ''
	        }
	      }, {
	        block: 'main-ui-delete',
	        tag: 'span',
	        mix: ['main-ui-item-icon'],
	        attrs: {
	          title: 'removeTitle' in data && data.removeTitle ? data.removeTitle : ''
	        }
	      }, {
	        block: 'main-ui-filter-icon-pin',
	        tag: 'span',
	        mix: ['main-ui-item-icon'],
	        attrs: {
	          title: 'editPinTitle' in data && data.editPinTitle ? data.editPinTitle : ''
	        }
	      }, {
	        block: 'main-ui-filter-edit-mask'
	      }]
	    };
	  };
	})();

	(function () {

	  BX.namespace('BX.Filter');
	  /**
	   * @type {{
	   * 		cache: {},
	   * 		styleForEach: BX.Filter.Utils.styleForEach,
	   * 		closestParent: BX.Filter.Utils.closestParent,
	   * 		closestChilds: BX.Filter.Utils.closestChilds,
	   * 		getNext: BX.Filter.Utils.getNext,
	   * 		getPrev: BX.Filter.Utils.getPrev,
	   * 		collectionSort: BX.Filter.Utils.collectionSort,
	   * 		getIndex: BX.Filter.Utils.getIndex,
	   * 		getByClass: BX.Filter.Utils.getByClass,
	   * 		getByTag: BX.Filter.Utils.getByTag,
	   * 		getBySelector: BX.Filter.Utils.getBySelector,
	   * 		requestAnimationFrame: BX.Filter.Utils.requestAnimationFrame,
	   * 		sortObject: BX.Filter.Utils.sortObject,
	   * 		objectsIsEquals: BX.Filter.Utils.objectsIsEquals,
	   * 		isKey: BX.Filter.Utils.isKey
	   * 	}}
	   */

	  BX.Filter.Utils = {
	    /** @protected **/
	    cache: {},

	    /**
	     * Sets css properties for element or elements collection
	     * @param {?HTMLElement|?HTMLElement[]} collection
	     * @param {object} properties
	     */
	    styleForEach: function styleForEach(collection, properties) {
	      var keys;
	      properties = BX.type.isPlainObject(properties) ? properties : null;
	      keys = Object.keys(properties);
	      [].forEach.call(collection || [], function (current) {
	        keys.forEach(function (propKey) {
	          BX.style(current, propKey, properties[propKey]);
	        });
	      });
	    },

	    /**
	     * Gets closest parent or closest parent element with class name
	     * @param {HTMLElement} item
	     * @param {?string} [className]
	     * @return {?HTMLElement|?Node}
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
	     * Gets closest childs elements
	     * @param {HTMLElement} item
	     * @return {?HTMLElement}
	     */
	    closestChilds: function closestChilds(item) {
	      return !!item ? item.children : null;
	    },

	    /**
	     * Gets next element
	     * @param {HTMLElement} currentItem
	     * @return {?HTMLElement}
	     */
	    getNext: function getNext(currentItem) {
	      return !!currentItem ? currentItem.nextElementSibling : null;
	    },

	    /**
	     * Gets previews element
	     * @param {HTMLElement} currentItem
	     * @return {?HTMLElement}
	     */
	    getPrev: function getPrev(currentItem) {
	      return !!currentItem ? currentItem.previousElementSibling : null;
	    },

	    /**
	     * Move current item after target item
	     * @param {HTMLElement} current
	     * @param {HTMLElement} target
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
	     * Gets collection item index
	     * @param {Array|HTMLCollection|NodeList} collection
	     * @param {*} item
	     * @return {int}
	     */
	    getIndex: function getIndex(collection, item) {
	      return [].indexOf.call(collection || [], item);
	    },

	    /**
	     * Gets elements by class name
	     * @param {HTMLElement|HTMLDocument} rootElement
	     * @param {string} className
	     * @param {boolean} [all = false]
	     * @returns {?HTMLElement|?HTMLElement[]}
	     */
	    getByClass: function getByClass(rootElement, className, all) {
	      var result = [];

	      if (className) {
	        result = (rootElement || document.body).getElementsByClassName(className);

	        if (!all) {
	          result = result.length ? result[0] : null;
	        } else {
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },

	    /**
	     * Gets element or elements by tag name
	     * @param {HTMLElement|HTMLDocument} rootElement
	     * @param {string} tag
	     * @param {boolean} [all = false]
	     * @return {?HTMLElement|?HTMLElement[]}
	     */
	    getByTag: function getByTag(rootElement, tag, all) {
	      var result = [];

	      if (tag) {
	        result = (rootElement || document.body).getElementsByTagName(tag);

	        if (!all) {
	          result = result.length ? result[0] : null;
	        } else {
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },

	    /**
	     * Gets element or elements by css selector
	     * @param {HTMLElement|HTMLDocument|Node} rootElement
	     * @param {string} selector
	     * @param {boolean} [all = false]
	     * @return {?HTMLElement|?HTMLElement[]}
	     */
	    getBySelector: function getBySelector(rootElement, selector, all) {
	      var result = [];

	      if (selector) {
	        if (!all) {
	          result = (rootElement || document.body).querySelector(selector);
	        } else {
	          result = (rootElement || document.body).querySelectorAll(selector);
	          result = [].slice.call(result);
	        }
	      }

	      return result;
	    },
	    requestAnimationFrame: function requestAnimationFrame() {
	      var raf = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || window.oRequestAnimationFrame || function (callback) {
	        window.setTimeout(callback, 1000 / 60);
	      };

	      raf.apply(window, arguments);
	    },

	    /**
	     * Sorts object properties
	     * @param {object} input
	     * @return {object}
	     */
	    sortObject: function sortObject(input) {
	      var output = {};
	      Object.keys(input).sort().forEach(function (key) {
	        output[key] = input[key];
	      });
	      return output;
	    },

	    /**
	     * Compares two objects or arrays
	     * @param {object} object1
	     * @param {object} object2
	     * @return {boolean}
	     */
	    objectsIsEquals: function objectsIsEquals(object1, object2) {
	      return JSON.stringify(object1) === JSON.stringify(object2);
	    },
	    isKey: function isKey(event, keyCode) {
	      var keyboard = {
	        8: 'backspace',
	        9: 'tab',
	        13: 'enter',
	        16: 'shift',
	        17: 'ctrl',
	        18: 'alt',
	        27: 'escape',
	        32: 'space',
	        37: 'leftArrow',
	        38: 'upArrow',
	        39: 'rightArrow',
	        40: 'downArrow',
	        46: 'delete',
	        112: 'f1',
	        113: 'f2',
	        114: 'f3',
	        115: 'f4',
	        116: 'f5',
	        117: 'f6',
	        118: 'f7',
	        119: 'f8',
	        120: 'f9',
	        121: 'f10',
	        122: 'f11',
	        123: 'f12',
	        65: 'a'
	      };
	      var code = !!event ? 'keyCode' in event ? event.keyCode : 'which' in event ? event.which : 0 : 0;
	      return code in keyboard && keyboard[code] === keyCode;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Filter');
	  BX.Filter.DestinationSelectorManager = {
	    fields: [],
	    controls: {},
	    onSelect: function onSelect(isNumeric, prefix, params) {
	      if (!BX.type.isNotEmptyObject(params) || !BX.type.isNotEmptyObject(params.item) || !BX.type.isNotEmptyString(params.selectorId)) {
	        return;
	      }

	      var selectorId = params.selectorId,
	          item = params.item;
	      var control = BX.Filter.DestinationSelectorManager.controls[selectorId];

	      if (control) {
	        var value = item.id;

	        if (BX.type.isNotEmptyString(isNumeric) && isNumeric == 'Y' && BX.type.isNotEmptyString(prefix)) {
	          var re = new RegExp('^' + prefix + '(\\d+)$');
	          var found = value.match(re);

	          if (BX.type.isArray(found)) {
	            value = found[1];
	          }
	        } else {
	          var eventResult = {};
	          BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:convert', [{
	            selectorId: selectorId,
	            value: value
	          }, eventResult]);

	          if (BX.type.isNotEmptyString(eventResult.value)) {
	            value = eventResult.value;
	          }
	        }

	        control.setData(BX.util.htmlspecialcharsback(item.name), value);
	        control.getLabelNode().value = '';
	        control.getLabelNode().blur();
	      }
	    },
	    onDialogOpen: function onDialogOpen(params) {
	      if (typeof params == 'undefined' || !BX.type.isNotEmptyString(params.selectorId)) {
	        return;
	      }

	      var selectorId = params.selectorId;
	      var item = BX.Filter.DestinationSelector.items[selectorId];

	      if (item) {
	        item.onDialogOpen();
	      }
	    },
	    onDialogClose: function onDialogClose(params) {
	      if (typeof params == 'undefined' || !BX.type.isNotEmptyString(params.selectorId)) {
	        return;
	      }

	      var selectorId = params.selectorId;
	      var item = BX.Filter.DestinationSelector.items[selectorId];

	      if (item) {
	        item.onDialogClose();
	      }
	    }
	  };

	  BX.Filter.DestinationSelector = function () {
	    this.id = "";
	    this.filterId = "";
	    this.settings = {};
	    this.fieldId = "";
	    this.control = null;
	    this.inited = null;
	  };

	  BX.Filter.DestinationSelector.items = {};

	  BX.Filter.DestinationSelector.create = function (id, settings) {
	    if (typeof this.items[id] != 'undefined') {
	      return this.items[id];
	    }

	    var self = new BX.Filter.DestinationSelector(id, settings);
	    self.initialize(id, settings);
	    this.items[id] = self;
	    BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:create', [id]);
	    return self;
	  };

	  BX.Filter.DestinationSelector.prototype.getSetting = function (name, defaultval) {
	    return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultval;
	  };

	  BX.Filter.DestinationSelector.prototype.getSearchInput = function () {
	    return this.control ? this.control.getLabelNode() : null;
	  };

	  BX.Filter.DestinationSelector.prototype.initialize = function (id, settings) {
	    this.id = id;
	    this.settings = settings ? settings : {};
	    this.fieldId = this.getSetting("fieldId", "");
	    this.filterId = this.getSetting("filterId", "");
	    this.inited = false;
	    this.opened = null;
	    var initialValue = this.getSetting("initialValue", false);

	    if (!!initialValue) {
	      var initialSettings = {};
	      initialSettings[this.fieldId] = initialValue.itemId;
	      initialSettings[this.fieldId + '_label'] = initialValue.itemName;
	      BX.Main.filterManager.getById(this.filterId).getApi().setFields(initialSettings);
	    }

	    BX.addCustomEvent(window, "BX.Main.Filter:customEntityFocus", BX.delegate(this.onCustomEntitySelectorOpen, this));
	    BX.addCustomEvent(window, "BX.Main.Filter:customEntityBlur", BX.delegate(this.onCustomEntitySelectorClose, this));
	    BX.addCustomEvent(window, "BX.Main.Filter:onGetStopBlur", BX.delegate(this.onGetStopBlur, this));
	    BX.addCustomEvent(window, "BX.Main.SelectorV2:beforeInitDialog", BX.delegate(this.onBeforeInitDialog, this));
	    BX.addCustomEvent(window, "BX.Main.Filter:customEntityRemove", BX.delegate(this.onCustomEntityRemove, this));
	  };

	  BX.Filter.DestinationSelector.prototype.open = function () {
	    var name = this.id;

	    if (!this.inited) {
	      var input = this.getSearchInput();
	      input.id = input.name;
	      BX.addCustomEvent(window, "BX.Main.SelectorV2:afterInitDialog", BX.delegate(function (params) {
	        if (typeof params.id != 'undefined' || params.id != this.id) {
	          return;
	        }

	        this.opened = true;
	      }, this));
	      BX.addCustomEvent(window, "BX.UI.SelectorManager:onCreate", BX.delegate(function (selectorId) {
	        if (!BX.type.isNotEmptyString(selectorId) || selectorId != this.id) {
	          return;
	        }

	        BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:setSelected', [{
	          selectorId: selectorId,
	          current: this.control.getCurrentValues()
	        }]);
	      }, this));
	      BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:openInit', [{
	        id: this.id,
	        inputId: input.id,
	        containerId: input.id
	      }]);
	    } else {
	      var currentValue = {};
	      currentValue[this.currentUser.entityId] = "users";
	      BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:open', [{
	        id: this.id,
	        bindNode: this.control.getField(),
	        value: currentValue
	      }]);
	      this.opened = true;
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.close = function () {
	    if (typeof BX.Main.selectorManagerV2.controls[this.id] !== "undefined") {
	      BX.Main.selectorManagerV2.controls[this.id].closeDialog();
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onCustomEntitySelectorOpen = function (control) {
	    var fieldId = control.getId();

	    if (this.fieldId !== fieldId) {
	      this.control = null;
	    } else {
	      this.control = control;

	      if (this.control) {
	        var current = this.control.getCurrentValues();
	        this.currentUser = {
	          entityId: current["value"]
	        };
	      }

	      BX.Filter.DestinationSelectorManager.controls[this.id] = this.control;

	      if (!this.opened) {
	        this.open();
	      } else {
	        this.close();
	      }
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onCustomEntitySelectorClose = function (control) {
	    if (this.fieldId === control.getId() && this.inited === true && this.opened === true) {
	      this.control = null;
	      window.setTimeout(BX.delegate(this.close, this), 0);
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onGetStopBlur = function (event, result) {
	    if (BX.findParent(event.target, {
	      className: 'bx-lm-box'
	    })) {
	      result.stopBlur = true;
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onCustomEntityRemove = function (control) {
	    if (this.fieldId === control.getId()) {
	      var instance = BX.UI.SelectorManager.instances[control.getId()];

	      if (instance && typeof control.hiddenInput != 'undefined' && typeof control.hiddenInput.value != 'undefined' && BX.type.isNotEmptyObject(instance.itemsSelected) && typeof instance.itemsSelected[control.hiddenInput.value] != 'undefined') {
	        delete instance.itemsSelected[control.hiddenInput.value];
	      }
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onBeforeInitDialog = function (params) {
	    if (typeof params.id == 'undefined' || params.id != this.id) {
	      return;
	    }

	    this.inited = true;

	    if (!this.control) {
	      params.blockInit = true;
	    }
	  };

	  BX.Filter.DestinationSelector.prototype.onDialogOpen = function () {
	    this.opened = true;
	  };

	  BX.Filter.DestinationSelector.prototype.onDialogClose = function () {
	    this.opened = false;
	  };
	})();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var EntitySelector = /*#__PURE__*/function () {
	  function EntitySelector(id, settings) {
	    babelHelpers.classCallCheck(this, EntitySelector);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "filter", null);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "dialogOptions", null);
	    babelHelpers.defineProperty(this, "control", null);
	    babelHelpers.defineProperty(this, "isMultiple", false);
	    babelHelpers.defineProperty(this, "needAddEntityIdToFilter", false);
	    babelHelpers.defineProperty(this, "isActive", false);
	    babelHelpers.defineProperty(this, "needShowDialogOnEmptyInput", true);
	    this.id = id;
	    this.settings = settings ? settings : {};
	    this.filter = this.getSetting('filter', null);

	    if (!this.filter) {
	      throw new Error('Filter option is required for EntitySelector field');
	    }

	    this.isMultiple = !!this.getSetting('isMultiple', false);
	    this.needAddEntityIdToFilter = this.getSetting('addEntityIdToResult', 'N') === 'Y';
	    this.needShowDialogOnEmptyInput = !!this.getSetting('showDialogOnEmptyInput', true);
	    this.dialogOptions = this.prepareDialogOptions();
	    this.dialog = null;
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:customEntityFocus', this.onCustomEntityFocus.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:customEntityBlur', this.onCustomEntityBlur.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:onGetStopBlur', this.onGetStopBlur.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:move", ', this.onCustomEntityRemove.bind(this));
	    this.controlInputChangeHandler = this.onSearchInputChange.bind(this);
	  }

	  babelHelpers.createClass(EntitySelector, [{
	    key: "open",
	    value: function open() {
	      var _this = this;

	      this.isActive = true;

	      if (!this.dialog) {
	        this.initDialog().then(function () {
	          if (_this.isActive) {
	            _this.openDialog();
	          }
	        });
	      } else {
	        this.openDialog();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.isActive = false;

	      if (this.dialog && this.dialog.isOpen()) {
	        this.dialog.hide();
	      }
	    }
	  }, {
	    key: "getFilterField",
	    value: function getFilterField() {
	      return this.filter.getField(this.id);
	    }
	  }, {
	    key: "getFilterFieldInputWrapper",
	    value: function getFilterFieldInputWrapper() {
	      var field = this.getFilterField();

	      if (!field) {
	        return null;
	      }

	      return BX.Filter.Utils.getBySelector(field.node, '.main-ui-control-entity');
	    }
	  }, {
	    key: "getFilterFieldInput",
	    value: function getFilterFieldInput() {
	      var field = this.getFilterField();

	      if (!field) {
	        return null;
	      }

	      return BX.Filter.Utils.getBySelector(field.node, '.' + this.filter.settings.classStringInput + '[type="text"]');
	    }
	  }, {
	    key: "setControl",
	    value: function setControl(control) {
	      this.control = control;
	    }
	  }, {
	    key: "unsetControl",
	    value: function unsetControl() {
	      this.control = null;
	    }
	  }, {
	    key: "getSetting",
	    value: function getSetting(name, defaultValue) {
	      return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultValue;
	    }
	  }, {
	    key: "prepareDialogOptions",
	    value: function prepareDialogOptions() {
	      var defaultOptions = {
	        enableSearch: false,
	        hideOnSelect: true,
	        autoHide: false,
	        hideByEsc: false
	      };
	      var dialogOptions = this.getSetting('dialogOptions', {});
	      dialogOptions = Object.assign(defaultOptions, dialogOptions);
	      return dialogOptions;
	    }
	  }, {
	    key: "openDialog",
	    value: function openDialog() {
	      if (this.dialog.isOpen()) {
	        return;
	      }

	      var inputWrapper = this.getFilterFieldInputWrapper();
	      var searchInput = this.getFilterFieldInput();
	      var searchQuery = main_core.Type.isDomNode(searchInput) ? searchInput.value.trim() : '';
	      this.dialog.setTargetNode(inputWrapper);
	      this.dialog.setWidth(inputWrapper.offsetWidth);

	      if (this.needShowDialogOnEmptyInput || searchQuery.length) {
	        this.dialog.show();
	      }

	      this.updateSelectedItemsInDialog(this.dialog);

	      if (searchQuery.length) {
	        this.dialog.search(searchQuery);
	      }
	    }
	  }, {
	    key: "initDialog",
	    value: function initDialog() {
	      var _this2 = this;

	      return EntitySelector.initDialogExtension().then(function (exports) {
	        var Dialog = exports.Dialog;
	        _this2.dialog = new Dialog(_objectSpread(_objectSpread({}, _this2.dialogOptions), {}, {
	          id: _this2.getDialogId(),
	          multiple: _this2.isMultiple
	        }));
	        main_core_events.EventEmitter.subscribe(_this2.dialog, 'Item:onSelect', _this2.onDialogItemSelect.bind(_this2));
	        main_core_events.EventEmitter.subscribe(_this2.dialog, 'Item:onDeselect', _this2.onDialogItemDeSelect.bind(_this2));
	        main_core_events.EventEmitter.subscribe(_this2.dialog, 'onLoad', _this2.onDialogLoad.bind(_this2));

	        var searchInput = _this2.getFilterFieldInput();

	        main_core.Event.bind(searchInput, 'input', _this2.controlInputChangeHandler);
	      });
	    }
	  }, {
	    key: "addItemToFilter",
	    value: function addItemToFilter(id, title) {
	      if (!this.control) {
	        return;
	      }

	      if (this.isMultiple) {
	        var currentValues = this.control.getCurrentValues();

	        if (!currentValues.filter(function (item) {
	          return item.value === id;
	        }).length) {
	          currentValues.push({
	            value: id,
	            label: title
	          });
	          this.control.setMultipleData(currentValues);
	        }
	      } else {
	        this.control.setSingleData(title, id);
	      }
	    }
	  }, {
	    key: "removeItemFromFilter",
	    value: function removeItemFromFilter(id) {
	      if (!this.control) {
	        return;
	      }

	      if (this.isMultiple) {
	        var currentValues = this.control.getCurrentValues();
	        this.control.setMultipleData(currentValues.filter(function (item) {
	          return item.value !== id;
	        }));
	      } else {
	        this.control.clearValue();
	      }
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.id + '_' + this.filter.getParam('FILTER_ID');
	    }
	  }, {
	    key: "getItemId",
	    value: function getItemId(item) {
	      if (this.needAddEntityIdToFilter) {
	        return JSON.stringify([item.getEntityId() + '', item.getId() + '']);
	      }

	      return item.getId() + '';
	    }
	  }, {
	    key: "updateSelectedItemsInDialog",
	    value: function updateSelectedItemsInDialog(dialog) {
	      var _this3 = this;

	      if (!this.control) {
	        return;
	      }

	      var currentValues = this.control.getCurrentValues();

	      if (!this.isMultiple) {
	        currentValues = [currentValues];
	      }

	      var selectedIds = currentValues.map(function (item) {
	        return item.value;
	      });
	      dialog.getItems().forEach(function (dialogItem) {
	        if (selectedIds.indexOf(_this3.getItemId(dialogItem)) > -1) {
	          dialogItem.select(true);
	        } else {
	          dialogItem.deselect();
	        }
	      });
	    }
	  }, {
	    key: "onCustomEntityFocus",
	    value: function onCustomEntityFocus(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          control = _event$getData2[0];

	      if (this.id !== control.getId()) {
	        return;
	      }

	      this.setControl(control);
	      this.open();
	    }
	  }, {
	    key: "onCustomEntityBlur",
	    value: function onCustomEntityBlur(event) {
	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	          control = _event$getData4[0];

	      if (this.id !== control.getId()) {
	        return;
	      }

	      this.close();
	      this.unsetControl();
	    }
	  }, {
	    key: "onGetStopBlur",
	    value: function onGetStopBlur(event) {
	      var _event$getData5 = event.getData(),
	          _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 2),
	          browserEvent = _event$getData6[0],
	          result = _event$getData6[1];

	      if (!(this.dialog && this.dialog.isOpen())) {
	        return; // if dialog wasn't shown, cancel blur is not required
	      }

	      var field = this.getFilterField();

	      if (!field) {
	        return;
	      }

	      var target = browserEvent.target;

	      if (target === field.node || // click on any child except field deletion button
	      field.node.contains(target) && !main_core.Dom.hasClass(target, this.filter.settings.classFieldDelete) || target === document.body) {
	        result.stopBlur = true;
	        return;
	      }

	      var dialogContainerElement = this.dialog.getPopup().getContentContainer();

	      if (target === dialogContainerElement || dialogContainerElement.contains(target)) {
	        result.stopBlur = true;
	      }
	    }
	  }, {
	    key: "onCustomEntityRemove",
	    value: function onCustomEntityRemove(event) {
	      var _event$getData7 = event.getData(),
	          _event$getData8 = babelHelpers.slicedToArray(_event$getData7, 1),
	          control = _event$getData8[0];

	      if (this.id !== control.getId()) {
	        return;
	      }

	      if (this.dialog) {
	        this.dialog.destroy();
	        this.dialog = null;
	      }

	      this.unsetControl();
	    }
	  }, {
	    key: "onSearchInputChange",
	    value: function onSearchInputChange(event) {
	      if (this.dialog) {
	        if (!this.needShowDialogOnEmptyInput) {
	          if (event.target.value) {
	            this.open();
	          } else {
	            this.close();
	          }
	        }

	        this.dialog.search(event.target.value);
	      }
	    }
	  }, {
	    key: "onDialogItemSelect",
	    value: function onDialogItemSelect(event) {
	      var _event$getData9 = event.getData(),
	          item = _event$getData9.item;

	      this.addItemToFilter(this.getItemId(item), item.getTitle());
	      this.getFilterFieldInput().value = ''; // clear search query
	    }
	  }, {
	    key: "onDialogItemDeSelect",
	    value: function onDialogItemDeSelect(event) {
	      var _event$getData10 = event.getData(),
	          item = _event$getData10.item;

	      this.removeItemFromFilter(this.getItemId(item));
	    }
	  }, {
	    key: "onDialogLoad",
	    value: function onDialogLoad(event) {
	      var dialog = event.getTarget();
	      this.updateSelectedItemsInDialog(dialog);
	    }
	  }], [{
	    key: "initDialogExtension",
	    value: function initDialogExtension() {
	      if (!EntitySelector.initExtensionPromise) {
	        EntitySelector.initExtensionPromise = main_core.Runtime.loadExtension('ui.entity-selector');
	      }

	      return EntitySelector.initExtensionPromise;
	    }
	  }, {
	    key: "create",
	    value: function create(id, settings) {
	      if (main_core.Type.isObject(this.items[id])) {
	        if (main_core.Type.isObject(settings.filter)) {
	          this.items[id].filter = settings.filter;
	        }

	        return this.items[id];
	      }

	      var self = new EntitySelector(id, settings);
	      this.items[id] = self;
	      return self;
	    }
	  }]);
	  return EntitySelector;
	}();

	babelHelpers.defineProperty(EntitySelector, "initExtensionPromise", null);
	babelHelpers.defineProperty(EntitySelector, "items", {});
	var namespace = main_core.Reflection.namespace('BX.Filter');
	namespace.EntitySelector = EntitySelector;

	(function () {

	  BX.namespace('BX.Filter');

	  BX.Filter.FieldController = function (field, parent) {
	    this.field = null;
	    this.parent = null;
	    this.type = null;
	    this.input = null;
	    this.deleteButton = null;
	    this.init(field, parent);
	  };

	  BX.Filter.FieldController.prototype = {
	    init: function init(field, parent) {
	      if (!BX.type.isDomNode(field)) {
	        throw 'BX.Filter.FieldController.init: field isn\'t dom node';
	      }

	      if (!(parent instanceof BX.Main.Filter)) {
	        throw 'BX.Filter.FieldController.init: parent not instance of BX.Main.ui.Filter';
	      }

	      this.field = field;
	      this.parent = parent;
	      this.bind();
	      this.isShowDelete() ? this.showDelete() : this.hideDelete();
	    },
	    isShowDelete: function isShowDelete() {
	      var squares = this.getSquares();
	      return this.getInputValue() || BX.type.isArray(squares) && squares.length;
	    },
	    getField: function getField() {
	      return this.field;
	    },
	    getInput: function getInput() {
	      var type, types;

	      if (!BX.type.isDomNode(this.input)) {
	        type = this.getType();
	        types = this.parent.types;

	        if (type === types.DATE) {
	          this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classDateInput);
	        }

	        if (type === types.NUMBER || type === 'number') {
	          this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classNumberInput);
	        }

	        if (type === types.STRING) {
	          this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classStringInput);
	        }

	        if (type === types.CUSTOM_ENTITY) {
	          this.input = BX.Filter.Utils.getBySelector(this.getField(), 'input[type="hidden"]');
	        }
	      }

	      return this.input;
	    },
	    getDeleteButton: function getDeleteButton() {
	      if (!BX.type.isDomNode(this.deleteButton)) {
	        this.deleteButton = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classValueDelete);
	      }

	      return this.deleteButton;
	    },
	    getSquares: function getSquares() {
	      return BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classSquare);
	    },
	    bind: function bind() {
	      if (this.getType() !== this.parent.types.MULTI_SELECT && this.getType() !== this.parent.types.SELECT) {
	        BX.bind(this.getDeleteButton(), 'click', BX.delegate(this._onDeleteClick, this));
	        BX.bind(this.getInput(), 'input', BX.delegate(this._onInput, this));
	      }
	    },
	    clearInput: function clearInput() {
	      var input = this.getInput();

	      if (BX.type.isDomNode(input)) {
	        input.value = '';
	      }
	    },
	    hideDelete: function hideDelete() {
	      var deleteButton = this.getDeleteButton();

	      if (BX.type.isDomNode(deleteButton)) {
	        BX.addClass(deleteButton, this.parent.settings.classHide);
	      }
	    },
	    showDelete: function showDelete() {
	      var deleteButton = this.getDeleteButton();

	      if (BX.type.isDomNode(deleteButton)) {
	        BX.removeClass(deleteButton, this.parent.settings.classHide);
	      }
	    },
	    removeSquares: function removeSquares() {
	      var squares = this.getSquares();

	      if (BX.type.isArray(squares) && squares.length) {
	        squares.forEach(function (square) {
	          BX.remove(square);
	        });
	      }
	    },
	    _onDeleteClick: function _onDeleteClick() {
	      this.removeSquares();
	      this.clearInput();
	      this.hideDelete();
	    },
	    _onInput: function _onInput() {
	      this.getInputValue() ? this.showDelete() : this.hideDelete();
	    },
	    getInputValue: function getInputValue() {
	      var result = '';
	      var input = this.getInput();

	      if (BX.type.isDomNode(input)) {
	        result = input.value;
	      }

	      return result;
	    },
	    getType: function getType() {
	      if (!BX.type.isNotEmptyString(this.type)) {
	        this.type = BX.data(this.getField(), 'type');
	      }

	      return this.type;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Main.ui');

	  BX.Main.ui.CustomEntity = function () {
	    this.field = null;
	    this.labelInput = null;
	    this.hiddenInput = null;
	    this.popupContainer = null;
	    this.inputClass = 'main-ui-control-string';
	    this.squareClass = 'main-ui-square';
	    this.multiple = null;
	  };
	  /**
	   * @static
	   * @param {HTMLElement} field
	   * @return {boolean}
	   */


	  BX.Main.ui.CustomEntity.isMultiple = function (field) {
	    if (!!field && !BX.hasClass(field, 'main-ui-control-entity')) {
	      field = BX.Filter.Utils.getByClass(field, 'main-ui-control-entity');
	    }

	    return !!field && JSON.parse(BX.data(field, 'multiple'));
	  }; //noinspection JSUnusedGlobalSymbols


	  BX.Main.ui.CustomEntity.prototype = {
	    setField: function setField(field) {
	      if (this.field !== field) {
	        this.field = field;
	        this.reset();
	      }
	    },
	    isMultiple: function isMultiple() {
	      return BX.Main.ui.CustomEntity.isMultiple(this.getField());
	    },
	    reset: function reset() {
	      this.labelInput = null;
	      this.hiddenInput = null;
	    },
	    getField: function getField() {
	      return this.field;
	    },
	    getId: function getId() {
	      var hiddenNode = this.getHiddenNode();
	      var id = null;

	      if (BX.type.isDomNode(hiddenNode)) {
	        id = hiddenNode.name;
	      }

	      return id;
	    },
	    getLabelNode: function getLabelNode() {
	      if (!BX.type.isDomNode(this.labelInput)) {
	        this.labelInput = BX.Filter.Utils.getBySelector(this.getField(), '.' + this.inputClass + '[type="text"]');
	      }

	      return this.labelInput;
	    },
	    getHiddenNode: function getHiddenNode() {
	      if (!BX.type.isDomNode(this.hiddenInput)) {
	        this.hiddenInput = BX.Filter.Utils.getBySelector(this.getField(), '.' + this.inputClass + '[type="hidden"]');
	      }

	      return this.hiddenInput;
	    },
	    getSquareByValue: function getSquareByValue(value) {
	      return BX.Filter.Utils.getBySelector(this.getField(), ['[data-item*=":' + BX.util.jsencode(value) + '}"]', '[data-item*=":\\"' + BX.util.jsencode(value) + '\\"}"]'].join(', '));
	    },
	    getSquares: function getSquares() {
	      return BX.Filter.Utils.getByClass(this.getField(), this.squareClass, true);
	    },
	    removeSquares: function removeSquares() {
	      this.getSquares().forEach(BX.remove);
	    },
	    setSquare: function setSquare(label, value) {
	      var field = this.getField();
	      var squareData = {
	        block: 'main-ui-square',
	        name: label,
	        item: {
	          '_label': label,
	          '_value': value
	        }
	      };
	      var square = BX.decl(squareData);
	      var squares = this.getSquares();

	      if (!squares.length) {
	        BX.prepend(square, field);
	      } else {
	        BX.insertAfter(square, squares[squares.length - 1]);
	      }
	    },
	    getCurrentValues: function getCurrentValues() {
	      var squares = this.getSquares();
	      var data, result;

	      if (this.isMultiple()) {
	        result = [];

	        for (var i = 0, length = squares.length; i < length; i++) {
	          try {
	            data = JSON.parse(BX.data(squares[i], 'item'));
	            result.push({
	              label: data._label,
	              value: data._value
	            });
	          } catch (ex) {}
	        }
	      } else {
	        if (squares.length === 0) {
	          result = {
	            label: '',
	            value: ''
	          };
	        } else {
	          try {
	            data = JSON.parse(BX.data(squares[0], 'item'));
	            result = {
	              label: data._label,
	              value: data._value
	            };
	          } catch (ex) {
	            result = {
	              label: '',
	              value: ''
	            };
	          }
	        }
	      }

	      return result;
	    },
	    setData: function setData(label, value) {
	      return this.isMultiple() ? this.setMultipleData(label, value) : this.setSingleData(label, value);
	    },
	    setSingleData: function setSingleData(label, value) {
	      var hiddenNode = this.getHiddenNode();
	      this.removeSquares();
	      this.setSquare(label, value);

	      if (BX.type.isDomNode(hiddenNode)) {
	        hiddenNode.value = value;
	        BX.fireEvent(hiddenNode, 'input');
	      }
	    },
	    setMultipleData: function setMultipleData(items, value) {
	      var values = [];
	      var hiddenNode = this.getHiddenNode();

	      if (BX.type.isArray(items)) {
	        this.removeSquares();

	        if (BX.type.isArray(items)) {
	          items.forEach(function (item) {
	            values.push(item.value);
	            this.setSquare(item.label, item.value);
	          }, this);

	          if (BX.type.isDomNode(hiddenNode)) {
	            hiddenNode.value = JSON.stringify(values);
	            BX.fireEvent(hiddenNode, 'input');
	          }
	        }
	      }

	      if (!BX.type.isArray(items) && value !== null) {
	        if (!this.getSquareByValue(value)) {
	          this.setSquare(items, value);
	          this.getSquares().forEach(function (square) {
	            var squareData = JSON.parse(BX.data(square, 'item'));

	            if (BX.type.isPlainObject(squareData)) {
	              values.push(squareData._value);
	            }
	          });
	          hiddenNode.value = JSON.stringify(values);
	          BX.fireEvent(hiddenNode, 'input');
	        }
	      }
	    },
	    clearValue: function clearValue() {
	      this.removeSquares();
	      var hiddenNode = this.getHiddenNode();
	      hiddenNode.value = this.isMultiple() ? '[]' : '';
	    },
	    setPopupContainer: function setPopupContainer(container) {
	      if (BX.type.isDomNode(container)) {
	        this.popupContainer = container;
	      }
	    },
	    getPopupContainer: function getPopupContainer() {
	      return this.popupContainer;
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Filter');
	  /**
	   * Filter search block class
	   * @param parent
	   * @constructor
	   */

	  BX.Filter.Search = function (parent) {
	    this.parent = null;
	    this.container = null;
	    this.input = null;
	    this.preset = null;
	    this.buttonsContainer = null;
	    this.delay = 800;
	    this.timeout = null;
	    this.init(parent);
	  };

	  BX.Filter.Search.prototype = {
	    init: function init(parent) {
	      this.parent = parent;
	      BX.bind(this.getInput(), 'input', BX.delegate(this._onInputWithoutDebounce, this));

	      if (this.parent.getParam('ENABLE_LIVE_SEARCH')) {
	        BX.bind(this.getInput(), 'input', BX.debounce(this._onInput, this.delay, this));
	      }

	      BX.bind(this.getInput(), 'keydown', BX.delegate(this._onKeyDown, this));
	      BX.bind(this.getFindButton(), 'click', BX.delegate(this._onSearchClick, this));
	      BX.bind(this.getContainer(), 'click', BX.delegate(this._onSearchContainerClick, this));
	      this.removeAutofocus();
	      this.firstInit = true;
	    },

	    /**
	     * Removes autofocus attr from search input
	     */
	    removeAutofocus: function removeAutofocus() {
	      var input = this.getInput();

	      if (!!input) {
	        input.blur();
	        input.autofocus = null;
	      }
	    },
	    getFindButton: function getFindButton() {
	      if (!BX.type.isDomNode(this.findButton)) {
	        this.findButton = BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classSearchButton);
	      }

	      return this.findButton;
	    },
	    _onSearchClick: function _onSearchClick() {
	      this.apply();
	    },
	    selectSquare: function selectSquare(square) {
	      !!square && BX.addClass(square, this.parent.settings.classSquareSelected);
	    },
	    selectSquares: function selectSquares() {
	      this.getSquares().forEach(this.selectSquare, this);
	    },
	    unselectSquare: function unselectSquare(square) {
	      !!square && BX.removeClass(square, this.parent.settings.classSquareSelected);
	    },
	    unselectSquares: function unselectSquares() {
	      this.getSquares().forEach(this.unselectSquare, this);
	    },
	    removeSquares: function removeSquares() {
	      this.getSquares().forEach(this.removeSquare, this);
	    },
	    isSquaresSelected: function isSquaresSelected() {
	      var squares = this.getSquares();
	      return squares.length && squares.every(this.isSquareSelected, this);
	    },
	    isSquareSelected: function isSquareSelected(square) {
	      return !!square && BX.hasClass(square, this.parent.settings.classSquareSelected);
	    },
	    getLastSquare: function getLastSquare() {
	      var squares = this.getSquares();
	      return !!squares ? squares[squares.length - 1] : null;
	    },
	    isTextSelected: function isTextSelected() {
	      var searchStringLength = this.getSearchString().length;
	      var searchInput = this.getInput();
	      var selectionStart = searchInput.selectionStart;
	      var selectionEnd = searchInput.selectionEnd;
	      return selectionStart === 0 && selectionEnd !== 0 && selectionEnd === searchStringLength;
	    },
	    isSelectionStart: function isSelectionStart() {
	      var searchInput = this.getInput();
	      var selectionStart = searchInput.selectionStart;
	      var selectionEnd = searchInput.selectionEnd;
	      return selectionStart === 0 && selectionEnd === 0;
	    },
	    isSquareRemoveButton: function isSquareRemoveButton(node) {
	      return !!node && BX.hasClass(node, this.parent.settings.classSquareDelete);
	    },
	    isClearButton: function isClearButton(node) {
	      return !!node && BX.hasClass(node, this.parent.settings.classClearSearchValueButton);
	    },
	    getClearButton: function getClearButton() {
	      return this.getContainer().querySelector("." + this.parent.settings.classClearSearchValueButton);
	    },
	    isSearchButton: function isSearchButton(node) {
	      return !!node && BX.hasClass(node, this.parent.settings.classSearchButton);
	    },

	    /**
	     * Adjust focus on search input
	     */
	    adjustFocus: function adjustFocus() {
	      if (!BX.browser.IsMobile()) {
	        var searchInput = this.getInput();

	        if (document.activeElement !== searchInput && window.scrollY < BX.pos(searchInput).top) {
	          //Puts cursor after last character
	          //noinspection SillyAssignmentJS
	          searchInput.value = searchInput.value;
	          searchInput.blur();
	          searchInput.focus();
	        }
	      }
	    },
	    findSquareByChild: function findSquareByChild(childNode) {
	      return BX.findParent(childNode, {
	        className: this.parent.settings.classSquare
	      }, true, false);
	    },

	    /**
	     * @param {HTMLElement} square
	     */
	    getSquareData: function getSquareData(square) {
	      var rawData = BX.data(square, 'item');
	      return !!square && !!rawData ? JSON.parse(rawData) : null;
	    },

	    /**
	     * @param {HTMLElement} square
	     * @return {boolean}
	     */
	    isSquareControl: function isSquareControl(square) {
	      var squareData = this.getSquareData(square);
	      return !!squareData && (squareData.type === 'control' || BX.type.isArray(squareData));
	    },
	    onPresetSquareRemove: function onPresetSquareRemove() {
	      var Filter = this.parent;
	      var Preset = Filter.getPreset();
	      var currentPresetId = Preset.getCurrentPresetId();
	      var isResetToDefaultMode = Filter.getParam('RESET_TO_DEFAULT_MODE');
	      var isValueRequiredModeMail = Filter.getParam('VALUE_REQUIRED');
	      var isPinned = Preset.isPinned(currentPresetId);
	      var squares = this.getSquares();

	      if (squares.length === 1) {
	        if (isValueRequiredModeMail && isPinned) {
	          this.parent.showPopup();
	          this.adjustPlaceholder();
	          this.parent.getPreset().deactivateAllPresets();
	        } else {
	          if (isResetToDefaultMode && isPinned || !isResetToDefaultMode) {
	            var resetWithoutSearch = true;
	            this.lastPromise = Filter.resetFilter(resetWithoutSearch);
	            Filter.closePopup();
	          }
	        }

	        if (isResetToDefaultMode && !isPinned) {
	          this.lastPromise = Filter.getPreset().applyPinnedPreset();
	        }
	      }

	      if (squares.length > 1) {
	        var currentPreset = Preset.getPreset(Preset.getCurrentPresetId());
	        var tmpPreset = Preset.getPreset('tmp_filter');
	        tmpPreset.FIELDS = BX.clone(currentPreset.ADDITIONAL);
	        currentPreset.ADDITIONAL = [];
	        Preset.deactivateAllPresets();
	        Preset.applyPreset('tmp_filter');
	        Filter.applyFilter();
	      }
	    },
	    onControlSquareRemove: function onControlSquareRemove(square) {
	      var Filter = this.parent;
	      var Preset = Filter.getPreset();
	      var isResetToDefaultMode = Filter.getParam('RESET_TO_DEFAULT_MODE');
	      var isValueRequiredModeMail = Filter.getParam('VALUE_REQUIRED');
	      var squareData;

	      if (isResetToDefaultMode && this.getSquares().length === 1) {
	        if (isValueRequiredModeMail) {
	          squareData = this.getSquareData(square);
	          Filter.clearControls(squareData);
	          this.parent.showPopup();
	          this.adjustPlaceholder();
	          this.parent.getPreset().deactivateAllPresets();
	        } else {
	          this.lastPromise = Filter.getPreset().applyPinnedPreset();
	        }
	      } else {
	        squareData = this.getSquareData(square);
	        Filter.clearControls(squareData);
	        Filter.closePopup();

	        if (BX.type.isArray(squareData)) {
	          squareData.forEach(function (square) {
	            Preset.removeAdditionalField(square.name);
	          });
	        }

	        if (BX.type.isPlainObject(squareData)) {
	          Preset.removeAdditionalField(squareData.name);
	        }

	        this.apply();
	      }
	    },
	    onValueRequiredSquareRemove: function onValueRequiredSquareRemove() {
	      var Filter = this.parent;
	      Filter.getPreset().deactivateAllPresets();
	      Filter.showPopup();
	      this.adjustPlaceholder();
	    },

	    /**
	     * @param {HTMLElement} square
	     */
	    complexSquareRemove: function complexSquareRemove(square) {
	      var isValueRequiredMode = this.parent.getParam('VALUE_REQUIRED_MODE');
	      var isPresetSquare = !this.isSquareControl(square);

	      if (isValueRequiredMode) {
	        this.onValueRequiredSquareRemove();
	      } else {
	        if (isPresetSquare) {
	          this.onPresetSquareRemove();
	        } else {
	          this.onControlSquareRemove(square);
	        }
	      }

	      this.removeSquare(square);
	      this.adjustClearButton();
	    },
	    adjustClearButton: function adjustClearButton() {
	      !!this.getLastSquare() ? this.showClearButton() : this.hideClearButton();
	    },

	    /**
	     * @param {HTMLElement} square
	     */
	    removeSquare: function removeSquare(square) {
	      !!square && BX.remove(square);
	    },
	    _onSearchContainerClick: function _onSearchContainerClick(event) {
	      var Filter = this.parent;

	      if (this.isClearButton(event.target)) {
	        if (!Filter.getParam('VALUE_REQUIRED')) {
	          if (!Filter.getParam('VALUE_REQUIRED_MODE')) {
	            if (Filter.getParam('RESET_TO_DEFAULT_MODE')) {
	              this.clearInput();
	              this.lastPromise = Filter.getPreset().applyPinnedPreset();
	            } else {
	              Filter.resetFilter();
	            }

	            Filter.closePopup();
	            this.adjustFocus();
	          } else {
	            this.removeSquares();
	            Filter.showPopup();
	            this.adjustPlaceholder();
	            this.hideClearButton();
	            Filter.getPreset().deactivateAllPresets();
	          }
	        } else {
	          var isPinned = Filter.getPreset().isPinned(Filter.getPreset().getCurrentPresetId());

	          if (isPinned || Filter.getPreset().getCurrentPresetId() === 'tmp_filter') {
	            var presetData = Filter.getPreset().getPreset(Filter.getPreset().getCurrentPresetId());

	            if (presetData.ADDITIONAL.length) {
	              presetData.ADDITIONAL = [];
	              this.lastPromise = Filter.getPreset().applyPreset(Filter.getPreset().getCurrentPresetId());
	              this.apply();
	            } else {
	              this.removeSquares();
	              Filter.showPopup();
	              this.adjustPlaceholder();
	              this.hideClearButton();
	              Filter.getPreset().deactivateAllPresets();
	            }
	          } else {
	            if (Filter.getParam('RESET_TO_DEFAULT_MODE')) {
	              this.lastPromise = Filter.getPreset().applyPinnedPreset();
	            } else {
	              Filter.resetFilter();
	            }

	            Filter.closePopup();
	            this.adjustFocus();
	          }

	          this.clearInput();
	        }
	      } else if (this.isSearchButton(event.target)) {
	        this.apply();
	        this.adjustFocus();
	      } else if (this.isSquareRemoveButton(event.target)) {
	        var square = this.findSquareByChild(event.target);
	        this.complexSquareRemove(square);
	        this.adjustFocus();
	      } else {
	        if (!Filter.getPopup().isShown()) {
	          Filter.showPopup();
	        } else {
	          var input = this.getInput();
	          var start = input.selectionStart;
	          var end = input.selectionEnd;
	          var searchLength = this.getSearchString().length;

	          if (!(searchLength && start === 0 && end === searchLength)) {
	            if (Filter.getParam('VALUE_REQUIRED')) {
	              if (!this.getSquares().length) {
	                this.lastPromise = Filter.getPreset().applyPinnedPreset();
	              } else {
	                Filter.closePopup();
	              }
	            } else {
	              Filter.closePopup();

	              if (Filter.getParam('VALUE_REQUIRED_MODE')) {
	                Filter.restoreRemovedPreset();
	              }
	            }
	          }
	        }
	      }
	    },
	    _onKeyDown: function _onKeyDown(event) {
	      var utils = BX.Filter.Utils;
	      var parent = this.parent;

	      if (utils.isKey(event, 'enter')) {
	        if (parent.getParam('VALUE_REQUIRED')) {
	          if (!this.getSquares().length) {
	            this.parent.getPreset().applyPinnedPreset();
	          } else {
	            this.apply();
	            this.firstInit = false;
	            this.lastSearchString = this.getSearchString();
	          }
	        } else {
	          this.apply();
	          this.firstInit = false;
	          this.lastSearchString = this.getSearchString();
	        }

	        parent.closePopup();
	      }

	      if (utils.isKey(event, 'tab') || utils.isKey(event, 'downArrow')) {
	        parent.showPopup();
	        parent.adjustFocus();
	        this.unselectSquares();
	      }

	      if (utils.isKey(event, 'upArrow')) {
	        parent.closePopup();

	        if (parent.getParam('VALUE_REQUIRED_MODE')) {
	          this.parent.restoreRemovedPreset();
	        }

	        if (parent.getParam('VALUE_REQUIRED')) {
	          if (!this.getSquares().length) {
	            this.parent.getPreset().applyPinnedPreset();
	          }
	        }
	      }

	      if (utils.isKey(event, 'a') && event.metaKey || utils.isKey(event, 'a') && event.ctrlKey) {
	        this.selectSquares();
	      }

	      if (utils.isKey(event, 'backspace') && this.isTextSelected() && this.isSquaresSelected()) {
	        clearTimeout(this.timeout);

	        if (this.parent.getParam('VALUE_REQUIRED')) {
	          var isPinned = this.parent.getPreset().isPinned(this.parent.getPreset().getCurrentPresetId());

	          if (isPinned) {
	            this.removeSquares();
	            this.parent.showPopup();
	            this.adjustPlaceholder();
	            this.hideClearButton();
	            this.parent.getPreset().deactivateAllPresets();
	          } else {
	            if (this.parent.getParam('RESET_TO_DEFAULT_MODE')) {
	              this.lastPromise = this.parent.getPreset().applyPinnedPreset();
	            } else {
	              this.parent.resetFilter();
	            }

	            this.parent.closePopup();
	            this.adjustFocus();
	          }

	          this.clearInput();
	        } else {
	          if (this.parent.getParam('RESET_TO_DEFAULT_MODE')) {
	            this.lastPromise = this.parent.getPreset().applyPinnedPreset();
	          } else {
	            this.lastPromise = this.parent.resetFilter();
	          }

	          this.parent.closePopup();
	        }
	      }

	      if (utils.isKey(event, 'backspace') && this.isSelectionStart()) {
	        clearTimeout(this.timeout);
	        var square = this.getLastSquare();
	        this.isSquareSelected(square) ? this.complexSquareRemove(square) : this.selectSquare(square);
	      }

	      if (!utils.isKey(event, 'backspace') && !event.metaKey && this.isSquaresSelected()) {
	        this.unselectSquares();
	      }
	    },
	    getSearchString: function getSearchString() {
	      var input = this.getInput();
	      return !!input ? input.value : '';
	    },
	    getSquares: function getSquares() {
	      return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classSquare, true);
	    },
	    adjustPlaceholder: function adjustPlaceholder() {
	      if (this.parent.getParam("LIMITS_ENABLED")) {
	        this.setInputPlaceholder(this.parent.getParam('MAIN_UI_FILTER__PLACEHOLDER_LIMITS_EXCEEDED'));
	      } else if (this.parent.getParam("DISABLE_SEARCH") || !this.parent.settings.get('SEARCH')) {
	        this.setInputPlaceholder(this.parent.getParam('MAIN_UI_FILTER__PLACEHOLDER'));
	      } else {
	        this.setInputPlaceholder(this.parent.getParam('MAIN_UI_FILTER__PLACEHOLDER_DEFAULT'));
	      }
	    },
	    isResolvedRequest: function isResolvedRequest() {
	      return !this.lastPromise || !!this.lastPromise && this.lastPromise.state;
	    },

	    /**
	     * Calls BX.Main.Filter.applyFilter
	     * @return {BX.Promise}
	     */
	    apply: function apply() {
	      if (this.isResolvedRequest()) {
	        this.lastPromise = this.parent._onFindButtonClick();
	      }

	      return this.lastPromise;
	    },

	    /**
	     * Calls BX.Main.Filter.resetFilter()
	     * @return {BX.Promise}
	     */
	    reset: function reset() {
	      if (this.isResolvedRequest()) {
	        this.parent.getSearch().removePreset();
	        this.parent.getPreset().deactivateAllPresets();
	        this.parent.getPreset().resetPreset(true);
	        this.timeout = setTimeout(BX.delegate(function () {
	          this.lastPromise = this.parent.resetFilter();
	        }, this), this.delay);
	      }

	      return this.lastPromise;
	    },
	    _onInputWithoutDebounce: function _onInputWithoutDebounce() {
	      clearTimeout(this.timeout);
	      var searchString = this.getSearchString();
	      this.lastSearchString = !!this.lastSearchString ? this.lastSearchString : searchString;

	      if (searchString !== this.lastSearchString && (!this.parent.isIe() || !this.firstInit)) {
	        if (this.parent.getParam('ENABLE_LIVE_SEARCH')) {
	          this.parent.showGridAnimation();
	          BX.onCustomEvent(window, 'BX.Filter.Search:input', [this.parent.params.FILTER_ID, searchString]);
	        }

	        this.parent.getPopup().isShown() && this.parent.closePopup();
	      }

	      if (searchString) {
	        this.showClearButton();
	      } else {
	        if (!this.getSquares().length && this.lastSearchString !== searchString) {
	          this.hideClearButton();
	          this.adjustPlaceholder();
	        }
	      }

	      if (this.parent.isAppliedUserFilter()) {
	        BX.Dom.addClass(this.container, 'main-ui-filter-search--active');
	      } else {
	        BX.Dom.removeClass(this.container, 'main-ui-filter-search--active');
	      }
	    },
	    _onInput: function _onInput() {
	      var searchString = this.getSearchString();

	      if (searchString !== this.lastSearchString && (!this.parent.isIe() || !this.firstInit)) {
	        this.apply();
	      }

	      this.firstInit = false;
	      this.lastSearchString = searchString;
	    },
	    getButtonsContainer: function getButtonsContainer() {
	      if (!BX.type.isDomNode(this.buttonsContainer)) {
	        this.buttonsContainer = BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classSearchButtonsContainer);
	      }

	      return this.buttonsContainer;
	    },
	    showClearButton: function showClearButton() {
	      BX.addClass(this.getButtonsContainer(), this.parent.settings.classShow);
	    },
	    hideClearButton: function hideClearButton() {
	      BX.removeClass(this.getButtonsContainer(), this.parent.settings.classShow);
	    },
	    getInput: function getInput() {
	      var inputId;

	      if (!BX.type.isDomNode(this.input)) {
	        inputId = [this.parent.getParam('FILTER_ID', ''), '_search'].join('');
	        this.input = BX(inputId);
	      }

	      return this.input;
	    },
	    getContainer: function getContainer() {
	      var containerId;

	      if (!BX.type.isDomNode(this.container)) {
	        containerId = [this.parent.getParam('FILTER_ID'), '_search_container'].join('');
	        this.container = BX(containerId);
	      }

	      return this.container;
	    },
	    setInputPlaceholder: function setInputPlaceholder(text) {
	      var input = this.getInput();
	      input.placeholder = text;
	    },
	    clearInput: function clearInput() {
	      var form = this.getInput();

	      if (BX.type.isDomNode(form)) {
	        form.value = null;
	      }
	    },
	    clearForm: function clearForm() {
	      this.clearInput();
	      this.removePreset();
	    },
	    makeSquares: function makeSquares(squaresData, depth, additional) {
	      var square;
	      var tmpSquare = null;
	      var container = this.getContainer();
	      var result = {
	        squares: [],
	        moreSquares: []
	      };
	      squaresData.forEach(function (current, index) {
	        if (index < depth) {
	          square = BX.decl(current);
	          tmpSquare = tmpSquare || square;

	          if (!additional) {
	            if (index === 0) {
	              BX.prepend(square, container);
	            } else {
	              BX.insertAfter(square, tmpSquare);
	            }
	          } else {
	            var lastSquare = BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classSquare);

	            if (lastSquare) {
	              BX.insertAfter(square, lastSquare);
	            } else {
	              BX.prepend(square, container);
	            }
	          }

	          tmpSquare = square;
	          result.squares.push(square);
	        } else {
	          result.moreSquares.push({
	            type: 'control',
	            name: current.value,
	            title: current.title,
	            icon: current.icon
	          });
	        }
	      }, this);
	      return result;
	    },
	    squares: function squares(fields, depth, additional) {
	      var squaresData, moreSquares, square, squaresWidth, result;
	      var squares = BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classSquare, true);

	      if (additional) {
	        squares.forEach(function (current) {
	          var item = BX.data(current, 'item');

	          if (item) {
	            BX.remove(current);
	          }
	        });
	      } else {
	        squares.forEach(BX.remove);
	      }

	      squaresData = this.prepareSquaresData(fields);
	      moreSquares = this.makeSquares(squaresData, depth, additional);
	      squaresWidth = 0;
	      result = {
	        squaresData: squaresData,
	        width: 0
	      };

	      if (moreSquares.moreSquares.length) {
	        square = {
	          block: 'main-ui-search-square',
	          name: this.parent.getParam('MAIN_UI_FILTER__AND') + ' ' + this.parent.getParam('MAIN_UI_FILTER__MORE') + ' ' + moreSquares.moreSquares.length,
	          item: moreSquares.moreSquares,
	          title: moreSquares.moreSquares.map(function (curr) {
	            var title = curr.title;

	            if ('icon' in curr && BX.Type.isPlainObject(curr.icon)) {
	              var iconTitle = curr.icon.title;
	              title = title.length ? iconTitle + ': ' + title : '';
	            }

	            return title;
	          }).join(', \n')
	        };
	        square = BX.decl(square);
	        moreSquares.squares.push(square);
	        BX.insertAfter(square, moreSquares.squares[moreSquares.squares.length - 2]);
	        squaresWidth = moreSquares.squares.reduce(function (prev, curr) {
	          return prev + BX.width(curr) + (parseFloat(BX.style(curr, 'margin-right')) || 0);
	        }, 0);
	      }

	      result.width = squaresWidth;
	      return result;
	    },
	    setPreset: function setPreset(presetData) {
	      var container = this.getContainer();
	      var square, squares;
	      var squaresResult;

	      if (BX.type.isPlainObject(presetData)) {
	        squares = BX.Filter.Utils.getByClass(container, this.parent.settings.classSquare, true);
	        squares.forEach(BX.remove);
	        presetData = BX.clone(presetData);
	        presetData.ADDITIONAL = presetData.ADDITIONAL || [];
	        BX.onCustomEvent(window, 'BX.Filter.Search:beforeSquaresUpdate', [presetData, this]);

	        if (presetData.ID !== 'default_filter' && presetData.ID !== 'tmp_filter') {
	          square = BX.decl({
	            block: 'main-ui-search-square',
	            name: presetData.TITLE,
	            value: presetData.ID,
	            isPreset: true
	          });
	          BX.prepend(square, container);

	          if ('ADDITIONAL' in presetData && BX.type.isArray(presetData.ADDITIONAL) && presetData.ADDITIONAL.length) {
	            squaresResult = this.squares(presetData.ADDITIONAL, 1, true);

	            if (BX.width(container) - squaresResult.width < 100) {
	              squaresResult = this.squares(presetData.ADDITIONAL, 0, true);
	            }
	          }
	        } else {
	          if ('ADDITIONAL' in presetData && BX.type.isArray(presetData.ADDITIONAL) && presetData.ADDITIONAL.length) {
	            presetData.ADDITIONAL.forEach(function (current, index) {
	              if (!('ID' in current)) {
	                current.ID = 'ADDITIONAL_ID_' + index;
	              }

	              if (!('NAME' in current)) {
	                current.NAME = 'ADDITIONAL_NAME_' + index;
	              }

	              if (!('TYPE' in current)) {
	                current.TYPE = 'STRING';
	              }

	              if ('LABEL' in current && 'LABEL' in current) {
	                presetData.FIELDS.push(current);
	              }
	            });
	          }

	          if (BX.type.isArray(presetData.FIELDS) && presetData.FIELDS.length) {
	            squaresResult = this.squares(presetData.FIELDS, 2);

	            if (BX.width(container) - squaresResult.width < 100) {
	              squaresResult = this.squares(presetData.FIELDS, 1);
	            }
	          }
	        }

	        if (squaresResult && BX.type.isArray(squaresResult.squaresData) && squaresResult.squaresData.length || presetData.ID !== 'default_filter' && presetData.ID !== 'tmp_filter') {
	          if (this.parent.getParam("LIMITS_ENABLED")) {
	            this.setInputPlaceholder(this.parent.getParam('MAIN_UI_FILTER__PLACEHOLDER_LIMITS_EXCEEDED'));
	          } else {
	            this.setInputPlaceholder(this.parent.getParam('MAIN_UI_FILTER__PLACEHOLDER_WITH_FILTER'));
	          }

	          this.showClearButton();
	        } else {
	          this.adjustPlaceholder();
	        }

	        if (BX.type.isNotEmptyString(this.parent.getSearch().getInput().value)) {
	          this.showClearButton();
	        }
	      }
	    },
	    prepareSquaresData: function prepareSquaresData(fields) {
	      var value, tmpValues, title;
	      var result = [];
	      fields = fields.filter(function (current) {
	        return !!current && this.parent.params.FIELDS.some(function (currentField) {
	          return current.NAME === currentField.NAME;
	        });
	      }, this);
	      fields.map(function (current) {
	        value = null;

	        if (!BX.Type.isStringFilled(current.ADDITIONAL_FILTER)) {
	          switch (current.TYPE) {
	            case this.parent.types.DATE:
	              {
	                value = current.LABEL + ': ' + current.SUB_TYPE.NAME;

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.QUARTER && BX.type.isNotEmptyString(current.VALUES._quarter)) {
	                  var quarter = current.QUARTERS.filter(function (curr) {
	                    return curr.VALUE == current.VALUES._quarter;
	                  }).map(function (curr) {
	                    return curr.NAME;
	                  });
	                  quarter = quarter.length ? quarter.join('') : '';
	                  value = current.LABEL + ': ' + quarter + ' ' + this.parent.getParam('MAIN_UI_FILTER__QUARTER').toLocaleLowerCase() + ' ' + current.VALUES._year;
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.YEAR && BX.type.isNotEmptyString(current.VALUES._year)) {
	                  value = current.LABEL + ': ' + current.VALUES._year;
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.MONTH && BX.type.isNotEmptyString(current.VALUES._month)) {
	                  var month = current.MONTHS.filter(function (curr) {
	                    return curr.VALUE == current.VALUES._month;
	                  }).map(function (curr) {
	                    return curr.NAME;
	                  });
	                  month = month.length ? month.join('') : '';
	                  value = current.LABEL + ': ' + month + ' ' + current.VALUES._year;
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.EXACT && BX.type.isNotEmptyString(current.VALUES._from)) {
	                  value = current.LABEL + ': ' + current.VALUES._from;
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.RANGE) {
	                  if (BX.type.isNotEmptyString(current.VALUES._from) && BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + current.VALUES._from + '-' + current.VALUES._to;
	                  } else if (!BX.type.isNotEmptyString(current.VALUES._from) && BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__BEFORE') + ' ' + current.VALUES._to;
	                  } else if (BX.type.isNotEmptyString(current.VALUES._from) && !BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__AFTER') + ' ' + current.VALUES._from;
	                  }
	                }

	                if ((current.SUB_TYPE.VALUE === this.parent.dateTypes.NEXT_DAYS || current.SUB_TYPE.VALUE === this.parent.dateTypes.PREV_DAYS) && !BX.type.isNumber(parseInt(current.VALUES._days))) {
	                  value = null;
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.NEXT_DAYS && BX.type.isNumber(parseInt(current.VALUES._days))) {
	                  value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__DATE_NEXT_DAYS_LABEL').replace('#N#', current.VALUES._days);
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.PREV_DAYS && BX.type.isNumber(parseInt(current.VALUES._days))) {
	                  value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__DATE_PREV_DAYS_LABEL').replace('#N#', current.VALUES._days);
	                }

	                if (current.SUB_TYPE.VALUE === this.parent.dateTypes.NONE) {
	                  value = null;
	                }

	                break;
	              }

	            case this.parent.types.CUSTOM_DATE:
	              {
	                if (BX.type.isArray(current.VALUE.days) && current.VALUE.days.length || BX.type.isArray(current.VALUE.months) && current.VALUE.months.length || BX.type.isArray(current.VALUE.years) && current.VALUE.years.length) {
	                  value = current.LABEL;
	                }

	                break;
	              }

	            case this.parent.types.SELECT:
	              {
	                if (BX.type.isPlainObject(current.VALUE) && current.VALUE.VALUE || current.STRICT) {
	                  value = current.LABEL + ': ' + current.VALUE.NAME;
	                }

	                break;
	              }

	            case this.parent.types.MULTI_SELECT:
	              {
	                if (BX.type.isArray(current.VALUE) && current.VALUE.length) {
	                  tmpValues = [];
	                  value = current.LABEL + ': ';
	                  current.VALUE.forEach(function (val, index) {
	                    if (index < 2) {
	                      tmpValues.push(val.NAME);
	                    }
	                  });
	                  value += tmpValues.join(', ');

	                  if (current.VALUE.length > 2) {
	                    title = [];
	                    current.VALUE.forEach(function (val) {
	                      title.push(val.NAME);
	                    });
	                    value = title.join(', ');
	                  }
	                }

	                break;
	              }

	            case this.parent.types.NUMBER:
	              {
	                if (current.SUB_TYPE.VALUE === 'exact') {
	                  if (BX.type.isNotEmptyString(current.VALUES._from)) {
	                    value = current.LABEL + ': ' + current.VALUES._from;
	                  } else {
	                    value = null;
	                  }
	                }

	                if (current.SUB_TYPE.VALUE === 'range') {
	                  if (BX.type.isNotEmptyString(current.VALUES._from) && BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + current.VALUES._from + '-' + current.VALUES._to;
	                  } else if (!BX.type.isNotEmptyString(current.VALUES._from) && BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__NUMBER_LESS') + ' ' + current.VALUES._to;
	                  } else if (BX.type.isNotEmptyString(current.VALUES._from) && !BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': ' + this.parent.getParam('MAIN_UI_FILTER__NUMBER_MORE') + ' ' + current.VALUES._from;
	                  } else {
	                    value = null;
	                  }
	                }

	                if (current.SUB_TYPE.VALUE === 'more') {
	                  if (BX.type.isNotEmptyString(current.VALUES._from)) {
	                    value = current.LABEL + ': > ';
	                    value += current.VALUES._from;
	                  }
	                }

	                if (current.SUB_TYPE.VALUE === 'less') {
	                  if (BX.type.isNotEmptyString(current.VALUES._to)) {
	                    value = current.LABEL + ': < ';
	                    value += current.VALUES._to;
	                  }
	                }

	                break;
	              }

	            case this.parent.types.CUSTOM_ENTITY:
	            case this.parent.types.DEST_SELECTOR:
	            case this.parent.types.ENTITY_SELECTOR:
	              {
	                if (current.MULTIPLE) {
	                  var label = !!current.VALUES._label ? current.VALUES._label : [];

	                  if (BX.type.isPlainObject(label)) {
	                    label = Object.keys(label).map(function (key) {
	                      return label[key];
	                    });
	                  }

	                  if (!BX.type.isArray(label)) {
	                    label = [label];
	                  }

	                  if (label.length > 0) {
	                    value = current.LABEL + ': ';
	                    value += label.join(', ');
	                  }
	                } else {
	                  if (BX.type.isNotEmptyString(current.VALUES._value) && BX.type.isNotEmptyString(current.VALUES._label)) {
	                    value = current.LABEL + ': ';
	                    value += current.VALUES._label;
	                  }
	                }

	                break;
	              }

	            case this.parent.types.CUSTOM:
	              {
	                value = '_VALUE' in current && BX.type.isNotEmptyString(current._VALUE) ? current.LABEL : null;
	                break;
	              }

	            default:
	              {
	                if (BX.type.isNotEmptyString(current.VALUE)) {
	                  value = current.LABEL + ': ' + current.VALUE;
	                }

	                break;
	              }
	          }
	        } else {
	          var squareItem = {
	            block: 'main-ui-search-square',
	            name: current.LABEL + ': ' + BX.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_IS_EMPTY'),
	            value: current.NAME,
	            icon: 'ICON' in current ? current.ICON : null,
	            item: {
	              type: 'control',
	              name: current.NAME
	            },
	            title: current.LABEL + ': ' + BX.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_IS_EMPTY')
	          };

	          if (current.ADDITIONAL_FILTER === BX.Filter.AdditionalFilter.Type.HAS_ANY_VALUE) {
	            squareItem.name = current.LABEL + ': ' + BX.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HAS_ANY_VALUE');
	            squareItem.title = current.LABEL + ': ' + BX.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HAS_ANY_VALUE');
	          }

	          result.push(squareItem);
	        }

	        if (value !== null) {
	          result.push({
	            block: 'main-ui-search-square',
	            name: value,
	            value: current.NAME,
	            icon: 'ICON' in current ? current.ICON : null,
	            item: {
	              type: 'control',
	              name: current.NAME
	            },
	            title: value
	          });
	        }
	      }, this);
	      return result;
	    },
	    getPreset: function getPreset() {
	      var container = this.getContainer();
	      var presetClass = this.parent.settings.classSquare;
	      var preset = null;

	      if (BX.type.isDomNode(container)) {
	        preset = BX.Filter.Utils.getByClass(container, presetClass);
	      }

	      return preset;
	    },
	    removePreset: function removePreset() {
	      var preset = this.getPreset();

	      if (BX.type.isDomNode(preset)) {
	        BX.remove(preset);
	        this.adjustPlaceholder();
	      }

	      this.hideClearButton();
	    },
	    updatePreset: function updatePreset(presetData) {
	      this.removePreset();
	      this.setPreset(presetData);
	    }
	  };
	})();

	(function () {

	  BX.namespace('BX.Filter');
	  /**
	   * Filter settings class
	   * @param options
	   * @param parent
	   * @constructor
	   */

	  BX.Filter.Settings = function (options, parent) {
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
	    this.classPopupFieldList4Column = 'main-ui-filter-field-list-4-column';
	    this.classPopupFieldList5Column = 'main-ui-filter-field-list-5-column';
	    this.classPopupFieldList6Column = 'main-ui-filter-field-list-6-column';
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
	    this.classLimitsAnimation = 'main-ui-filter-field-limits-animate';
	    this.classSidebarControlsContainer = 'main-ui-filter-add-container';
	    this.searchContainerPostfix = '_search_container';
	    this.classPresetButtonsContainer = 'main-ui-filter-field-preset-button-container';
	    this.classFindButton = 'main-ui-filter-find';
	    this.classResetButton = 'main-ui-filter-reset';
	    this.classDefaultFilter = 'main-ui-filter-default-preset';
	    this.classRestoreButton = 'main-ui-filter-reset-link';
	    this.classPinButton = 'main-ui-filter-icon-pin';
	    this.classPopupOverlay = 'popup-window-overlay';
	    this.classSidePanelContainer = 'side-panel-container';
	    this.classPinnedPreset = 'main-ui-item-pin';
	    this.classWaitButtonClass = 'ui-btn-clock';
	    this.classForAllCheckbox = 'main-ui-filter-save-for-all';
	    this.classShow = 'main-ui-show';
	    this.classFocus = 'main-ui-focus';
	    this.classPresetField = 'main-ui-filter-preset-field';
	    this.classPopupSearchFieldListItemHidden = 'main-ui-filter-field-list-item-hidden';
	    this.classPopupSearchFieldListItemVisible = 'main-ui-filter-field-list-item-visible';
	    this.classPopupSearchSectionItem = 'main-ui-filter-popup-search-section-input';
	    this.classPopupSearchSectionItemIcon = 'main-ui-filter-popup-search-section-item-icon';
	    this.classPopupSearchSectionItemIconActive = 'main-ui-filter-popup-search-section-item-icon-active';
	    this.numberPostfix = '_numsel';
	    this.datePostfix = '_datesel';
	    this.toPostfix = '_to';
	    this.fromPostfix = '_from';
	    this.daysPostfix = '_days';
	    this.monthPostfix = '_month';
	    this.quarterPostfix = '_quarter';
	    this.yearPostfix = '_year';
	    this.generalTemplateId = '';
	    this.maxPopupColumnCount = 6;
	    this.popupWidth = 630;
	    this.init(options, parent);
	  };

	  BX.Filter.Settings.prototype = {
	    init: function init(options, parent) {
	      this.generalTemplateId = parent.getParam('FILTER_ID') + '_GENERAL_template';
	      this.mergeSettings(options);
	    },
	    get: function get(name, defaultValue) {
	      return name && name in this && !BX.type.isFunction(this[name]) ? this[name] : defaultValue;
	    },
	    mergeSettings: function mergeSettings(options) {
	      if (BX.type.isPlainObject(options)) {
	        Object.keys(options).forEach(function (key) {
	          if (!BX.type.isFunction(this[key])) {
	            this[key] = options[key];
	          }
	        }, this);
	      }
	    }
	  };
	})();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	(function () {

	  BX.namespace('BX.Main');
	  /**
	   * General filter class
	   * @param {object} params Component params
	   * @param {object} options Extends BX.Filter.Settings
	   * @param {object} types Field types from Bitrix\Main\UI\Filter\Type
	   * @param types.STRING
	   * @param types.SELECT
	   * @param types.DATE
	   * @param types.CUSTOM_DATE
	   * @param types.MULTI_SELECT
	   * @param types.NUMBER
	   * @param types.DEST_SELECTOR
	   * @param types.ENTITY_SELECTOR
	   * @param types.CUSTOM_ENTITY
	   * @param types.CHECKBOX
	   * @param types.CUSTOM
	   * @param types.ENTITY
	   * @param {object} dateTypes Date field types from Bitrix\Main\UI\Filter\DateType
	   * @param dateTypes.NONE
	   * @param dateTypes.YESTERDAY
	   * @param dateTypes.CURRENT_DAY
	   * @param dateTypes.CURRENT_WEEK
	   * @param dateTypes.CURRENT_MONTH
	   * @param dateTypes.CURRENT_QUARTER
	   * @param dateTypes.LAST_7_DAYS
	   * @param dateTypes.LAST_30_DAYS
	   * @param dateTypes.LAST_60_DAYS
	   * @param dateTypes.LAST_90_DAYS
	   * @param dateTypes.MONTH
	   * @param dateTypes.QUARTER
	   * @param dateTypes.YEAR
	   * @param dateTypes.EXACT
	   * @param dateTypes.LAST_WEEK
	   * @param dateTypes.LAST_MONTH
	   * @param dateTypes.RANGE
	   * @param dateTypes.NEXT_DAYS
	   * @param dateTypes.PREV_DAYS
	   * @param dateTypes.TOMORROW
	   * @param dateTypes.NEXT_MONTH
	   * @param dateTypes.NEXT_WEEK
	   * @param {object} numberTypes Number field types from Bitrix\Main\UI\Filter\NumberType
	   */

	  BX.Main.Filter = function (params, options, types, dateTypes, numberTypes, additionalDateTypes) {
	    this.params = params;
	    this.search = null;
	    this.popup = null;
	    this.presets = null;
	    this.fields = null;
	    this.types = types;
	    this.dateTypes = dateTypes;
	    this.additionalDateTypes = additionalDateTypes;
	    this.numberTypes = numberTypes;
	    this.settings = new BX.Filter.Settings(options, this);
	    this.filter = null;
	    this.api = null;
	    this.isAddPresetModeState = false;
	    this.firstInit = true;
	    this.analyticsLabel = null;
	    this.emitter = new BX.Event.EventEmitter();
	    this.emitter.setEventNamespace('BX.Filter.Field');

	    this.emitter.subscribe = function (eventName, listener) {
	      BX.Event.EventEmitter.subscribe(this.emitter, eventName.replace('BX.Filter.Field:', ''), listener);
	    }.bind(this);

	    this.enableFieldsSearch = null;
	    this.enableHeadersSections = null;
	    this.init();
	  };
	  /**
	   * Converts string to camel case
	   * @param {string} string
	   * @return {*}
	   */


	  function toCamelCase(string) {
	    if (BX.type.isString(string)) {
	      string = string.toLowerCase();
	      string = string.replace(/[\-_\s]+(.)?/g, function (match, chr) {
	        return chr ? chr.toUpperCase() : '';
	      });
	      return string.substr(0, 1).toLowerCase() + string.substr(1);
	    }

	    return string;
	  } //noinspection JSUnusedGlobalSymbols


	  BX.Main.Filter.prototype = {
	    init: function init() {
	      BX.bind(document, 'mousedown', BX.delegate(this._onDocumentClick, this));
	      BX.bind(document, 'keydown', BX.delegate(this._onDocumentKeydown, this));
	      BX.bind(window, 'load', BX.delegate(this.onWindowLoad, this));
	      BX.addCustomEvent('Grid::ready', BX.delegate(this._onGridReady, this));
	      this.getSearch().updatePreset(this.getParam('CURRENT_PRESET'));
	      this.enableFieldsSearch = this.getParam('ENABLE_FIELDS_SEARCH', false);
	      this.enableHeadersSections = this.getParam('HEADERS_SECTIONS', false);
	    },
	    getEmitter: function getEmitter() {
	      return this.emitter;
	    },
	    onWindowLoad: function onWindowLoad() {
	      this.settings.get('AUTOFOCUS') && this.adjustFocus();
	    },

	    /**
	     * Removes apply_filter param from url
	     */
	    clearGet: function clearGet() {
	      if ('history' in window) {
	        var url = window.location.toString();
	        var clearUrl = BX.util.remove_url_param(url, 'apply_filter');
	        window.history.replaceState(null, '', clearUrl);
	      }
	    },

	    /**
	     * Adjusts focus on search field
	     */
	    adjustFocus: function adjustFocus() {
	      this.getSearch().adjustFocus();
	    },
	    _onAddPresetKeydown: function _onAddPresetKeydown(event) {
	      if (BX.Filter.Utils.isKey(event, 'enter')) {
	        this._onSaveButtonClick();
	      }
	    },
	    _onDocumentKeydown: function _onDocumentKeydown(event) {
	      if (BX.Filter.Utils.isKey(event, 'escape')) {
	        if (this.getPopup().isShown()) {
	          BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
	          this.closePopup();

	          if (this.getParam('VALUE_REQUIRED_MODE')) {
	            this.restoreRemovedPreset();
	          }

	          if (this.getParam('VALUE_REQUIRED')) {
	            if (!this.getSearch().getSquares().length) {
	              this.getPreset().applyPinnedPreset();
	            }
	          }
	        }
	      }
	    },

	    /**
	     * Gets BX.Filter.Api instance
	     * @return {BX.Filter.Api}
	     */
	    getApi: function getApi() {
	      if (!(this.api instanceof BX.Filter.Api)) {
	        this.api = new BX.Filter.Api(this);
	      }

	      return this.api;
	    },

	    /**
	     * Adds sidebar item
	     * @param {string} id
	     * @param {string} name
	     * @param {boolean} [pinned = false]
	     */
	    addSidebarItem: function addSidebarItem(id, name, pinned) {
	      var Presets = this.getPreset();
	      var presetsContainer = Presets.getContainer();
	      var sidebarItem = Presets.createSidebarItem(id, name, pinned);
	      var preset = Presets.getPresetNodeById(id);

	      if (BX.type.isDomNode(preset)) {
	        BX.remove(preset);
	        BX.prepend(sidebarItem, presetsContainer);
	      } else {
	        presetsContainer && presetsContainer.insertBefore(sidebarItem, Presets.getAddPresetField());
	      }

	      BX.bind(sidebarItem, 'click', BX.delegate(Presets._onPresetClick, Presets));
	    },

	    /**
	     * Saves user settings
	     * @param {boolean} [forAll = false]
	     */
	    saveUserSettings: function saveUserSettings(forAll) {
	      var optionsParams = {
	        'FILTER_ID': this.getParam('FILTER_ID'),
	        'GRID_ID': this.getParam('GRID_ID'),
	        'action': 'SET_FILTER_ARRAY'
	      };
	      var Presets = this.getPreset();
	      var currentPresetId = Presets.getCurrentPresetId();
	      var presetsSettings = {};
	      this.params['PRESETS'] = BX.clone(this.editablePresets);
	      presetsSettings.current_preset = currentPresetId;
	      Presets.getPresets().forEach(function (current, index) {
	        var presetId = Presets.getPresetId(current);

	        if (presetId && presetId !== 'tmp_filter') {
	          var presetData = Presets.getPreset(presetId);
	          presetData.TITLE = BX.util.htmlspecialchars(BX.util.htmlspecialcharsback(presetData.TITLE));
	          presetData.SORT = index;
	          Presets.updatePresetName(current, presetData.TITLE);
	          presetsSettings[presetId] = {
	            sort: index,
	            name: presetData.TITLE,
	            fields: this.preparePresetSettingsFields(presetData.FIELDS),
	            for_all: forAll && !BX.type.isBoolean(presetData.FOR_ALL) || forAll && presetData.FOR_ALL === true
	          };
	        }
	      }, this);
	      this.saveOptions(presetsSettings, optionsParams, null, forAll);
	    },

	    /**
	     * Checks is for all
	     * @return {boolean}
	     */
	    isForAll: function isForAll(forAll) {
	      var checkbox = this.getForAllCheckbox();
	      return BX.type.isBoolean(forAll) && forAll || !!checkbox && !!checkbox.checked;
	    },

	    /**
	     * Gets for all checkbox
	     * @return {?HTMLElement}
	     */
	    getForAllCheckbox: function getForAllCheckbox() {
	      if (!this.forAllCheckbox) {
	        this.forAllCheckbox = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
	      }

	      return this.forAllCheckbox;
	    },

	    /**
	     * Prepares preset settings fields
	     * @param fields
	     * @return {?object}
	     */
	    preparePresetSettingsFields: function preparePresetSettingsFields(fields) {
	      var result = {};
	      var valuesKeys;
	      (fields || []).forEach(function (current) {
	        switch (current.TYPE) {
	          case this.types.STRING:
	            {
	              result[current.NAME] = current.VALUE;
	              break;
	            }

	          case this.types.TEXTAREA:
	            {
	              result[current.NAME] = current.VALUE;
	              break;
	            }

	          case this.types.SELECT:
	            {
	              result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
	              break;
	            }

	          case this.types.MULTI_SELECT:
	            {
	              if (BX.type.isArray(current.VALUE) && current.VALUE.length) {
	                current.VALUE.forEach(function (curr, index) {
	                  result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
	                  result[current.NAME][index] = curr.VALUE;
	                }, this);
	              }

	              break;
	            }

	          case this.types.CHECKBOX:
	            {
	              if (BX.type.isArray(current.VALUE) && current.VALUE.length) {
	                current.VALUE.forEach(function (curr, index) {
	                  result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
	                  result[current.NAME][index] = curr.VALUE;
	                }, this);
	              }

	              break;
	            }

	          case this.types.DATE:
	            {
	              if (BX.type.isPlainObject(current.VALUES)) {
	                valuesKeys = Object.keys(current.VALUES);
	                result[current.NAME + '_datesel'] = current.SUB_TYPE.VALUE;
	                valuesKeys.forEach(function (curr) {
	                  result[current.NAME + curr] = current.VALUES[curr];
	                }, this);
	              }

	              break;
	            }

	          case this.types.NUMBER:
	            {
	              if (BX.type.isPlainObject(current.VALUES)) {
	                valuesKeys = Object.keys(current.VALUES);
	                result[current.NAME + '_numsel'] = current.SUB_TYPE.VALUE;
	                valuesKeys.forEach(function (curr) {
	                  result[current.NAME + curr] = current.VALUES[curr];
	                }, this);
	              }

	              break;
	            }

	          case this.types.DEST_SELECTOR:
	            {
	              if (BX.type.isPlainObject(current.VALUES)) {
	                result[current.NAME] = current.VALUES._value;
	                result[current.NAME + '_label'] = current.VALUES._label;
	              }

	              break;
	            }

	          case this.types.DEST_SELECTOR:
	          case this.types.ENTITY_SELECTOR:
	          case this.types.CUSTOM_ENTITY:
	            {
	              if (BX.type.isPlainObject(current.VALUES)) {
	                result[current.NAME] = current.VALUES._value;
	                result[current.NAME + '_label'] = current.VALUES._label;
	              }

	              break;
	            }

	          default:
	            {
	              break;
	            }
	        }
	      }, this);
	      return result;
	    },

	    /**
	     * Saves preset
	     */
	    savePreset: function savePreset() {
	      var presetId = 'filter_' + +new Date();
	      var presetName = BX.util.htmlspecialcharsback(this.getPreset().getAddPresetFieldInput().value);
	      this.updatePreset(presetId, presetName, null, true, null, null, true);
	      this.addSidebarItem(presetId, presetName);
	      this.getPreset().applyPreset(presetId);
	      this.getPreset().activatePreset(presetId);
	      this.applyFilter();
	    },

	    /**
	     * Updates preset
	     * @param {string} presetId
	     * @param {?string} [presetName]
	     * @param {?boolean} [reset]
	     * @param {?boolean} [sort]
	     * @param {?function} [beforeLoad]
	     * @param {?function} [afterLoad]
	     * @param {boolean} [isNew]
	     * @return {BX.Promise}
	     */
	    updatePreset: function updatePreset(presetId, presetName, reset, sort, beforeLoad, afterLoad, isNew) {
	      var fields = this.getFilterFieldsValues();
	      var sourceFields = this.getPreset().getFields().map(function (curr) {
	        return BX.data(curr, 'name');
	      });
	      var preset = this.getPreset().getCurrentPresetData();
	      var params = {
	        'FILTER_ID': this.getParam('FILTER_ID'),
	        'GRID_ID': this.getParam('GRID_ID'),
	        'action': 'SET_FILTER'
	      };
	      var rows, value, tmpPresetNode, tmpPresetInput, presets;
	      var data = {};
	      data.additional = {};

	      if (presetId !== 'tmp_filter' && presetId !== 'default_filter' && !isNew) {
	        var additional = BX.type.isArray(preset.ADDITIONAL) ? preset.ADDITIONAL : [];
	        additional.forEach(function (field) {
	          Object.keys(fields).forEach(function (key) {
	            if (key.indexOf(field.NAME) !== -1) {
	              data.additional[key] = fields[key];
	              delete fields[key];
	            }
	          });
	        });
	      }

	      rows = Object.keys(fields);

	      if (!reset) {
	        data.apply_filter = 'Y';
	      } else {
	        data.clear_filter = 'Y';
	      }

	      data.save = 'Y';
	      data.fields = fields;
	      data.rows = sourceFields.join(',');
	      data.preset_id = presetId || preset.ID;

	      if (BX.type.isNotEmptyString(presetName)) {
	        data.name = BX.util.htmlspecialchars(presetName);
	      } else {
	        tmpPresetNode = this.getPreset().getPresetNodeById(data.preset_id);
	        tmpPresetInput = this.getPreset().getPresetInput(tmpPresetNode);

	        if (BX.type.isDomNode(tmpPresetInput) && BX.type.isNotEmptyString(tmpPresetInput.value)) {
	          data.name = tmpPresetInput.value;
	        } else {
	          data.name = preset.TITLE;
	        }
	      }

	      if ((!('sort' in data) || !BX.type.isNumber(data.sort)) && sort) {
	        presets = this.getParam('PRESETS');
	        data.sort = presets.length + 2;
	      }

	      if (!reset) {
	        rows.forEach(function (key) {
	          if (BX.type.isArray(data.fields[key])) {
	            value = data.fields[key].length ? {} : '';
	            data.fields[key].forEach(function (val, index) {
	              value[index] = val;
	            }, this);

	            if (value || BX.type.isNumber(value) || BX.type.isBoolean(value)) {
	              data.fields[key] = value;
	            }
	          }
	        }, this);
	      }

	      if (data.preset_id === 'tmp_filter' || this.isAddPresetEnabled() || reset) {
	        this.updateParams(data);
	      }

	      if (BX.type.isFunction(beforeLoad)) {
	        beforeLoad();
	      }

	      var promise = new BX.Promise(null, this);
	      promise.setAutoResolve('fulfill', 0);
	      promise.then(function () {
	        var afterPromise = new BX.Promise(null, this);
	        this.saveOptions(data, params, BX.proxy(afterPromise.fulfill, afterPromise));
	        return afterPromise;
	      }).then(function () {
	        !!afterLoad && afterLoad();
	      });
	      return promise;
	    },

	    /**
	     * Saves fields sort
	     */
	    saveFieldsSort: function saveFieldsSort() {
	      var params = {
	        'FILTER_ID': this.getParam('FILTER_ID'),
	        'GRID_ID': this.getParam('GRID_ID'),
	        'action': 'SET_FILTER'
	      };
	      var fields = this.getPreset().getFields();
	      var data = {};
	      data.preset_id = 'default_filter';

	      if (BX.type.isArray(fields)) {
	        data.rows = fields.map(function (current) {
	          return BX.data(current, 'name');
	        });
	        data.rows = data.rows.join(',');
	      }

	      this.updateParams(data);
	      this.saveOptions(data, params);
	    },

	    /**
	     * Updates params
	     * @param {object} data
	     */
	    updateParams: function updateParams(data) {
	      var preset, presets;

	      if (BX.type.isPlainObject(data) && 'preset_id' in data) {
	        preset = this.getPreset().getPreset(data.preset_id);

	        if (BX.type.isPlainObject(preset)) {
	          if ('name' in data && BX.type.isNotEmptyString(data.name)) {
	            preset.TITLE = data.name;
	          }

	          if ('rows' in data && !('fields' in data)) {
	            data.fields = {};
	            data.rows.split(',').forEach(function (curr) {
	              data.fields[curr] = '';
	            });
	          }

	          if ('fields' in data) {
	            preset.FIELDS = this.preparePresetFields(data.fields, data.rows);
	          }

	          if ('additional' in data && preset.ID !== 'tmp_filter') {
	            preset.ADDITIONAL = this.preparePresetFields(data.additional, data.rows);
	          }
	        } else {
	          presets = this.getParam('PRESETS');
	          preset = {
	            ID: data.preset_id,
	            TITLE: data.name,
	            SORT: presets.length + 2,
	            FIELDS: this.preparePresetFields(data.fields, data.rows)
	          };
	          presets.push(preset);
	        }
	      }
	    },

	    /**
	     * Prepares preset fields
	     * @param {object[]} dataFields
	     * @param rows
	     * @return {object[]}
	     */
	    preparePresetFields: function preparePresetFields(dataFields, rows) {
	      var fieldKeys, field;
	      var fields = [];

	      if (BX.type.isPlainObject(dataFields)) {
	        rows = BX.type.isNotEmptyString(rows) ? rows.split(',') : [];
	        fieldKeys = rows.length ? rows : Object.keys(dataFields);
	        fieldKeys.forEach(function (current) {
	          current = current.replace('_datesel', '').replace('_numsel', '').replace('_' + BX.Filter.AdditionalFilter.Type.IS_EMPTY, '').replace('_' + BX.Filter.AdditionalFilter.Type.HAS_ANY_VALUE, '');
	          field = BX.clone(this.getFieldByName(current));

	          if (BX.type.isPlainObject(field)) {
	            field.ADDITIONAL_FILTER = BX.Filter.AdditionalFilter.fetchAdditionalFilter(current, dataFields);

	            if (!BX.Type.isStringFilled(field.ADDITIONAL_FILTER)) {
	              if (field.TYPE === this.types.STRING) {
	                field.VALUE = dataFields[current];
	              }

	              if (field.TYPE === this.types.TEXTAREA) {
	                field.VALUE = dataFields[current];
	              }

	              if (field.TYPE === this.types.MULTI_SELECT) {
	                field.VALUE = this.prepareMultiSelectValue(dataFields[current], field.ITEMS);
	              }

	              if (field.TYPE === this.types.SELECT || field.TYPE === this.types.CHECKBOX) {
	                field.VALUE = this.prepareSelectValue(dataFields[current], field.ITEMS);
	              }

	              if (field.TYPE === this.types.DATE) {
	                field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_datesel'], field.SUB_TYPES);
	                field.VALUES = {
	                  '_from': dataFields[current + '_from'],
	                  '_to': dataFields[current + '_to'],
	                  '_days': dataFields[current + '_days'],
	                  '_month': dataFields[current + '_month'],
	                  '_quarter': dataFields[current + '_quarter'],
	                  '_year': dataFields[current + '_year'],
	                  '_allow_year': dataFields[current + '_allow_year']
	                };
	              }

	              if (field.TYPE === this.types.CUSTOM_DATE) {
	                field.VALUE = {
	                  'days': Object.keys(dataFields[current + '_days'] || {}).map(function (index) {
	                    return dataFields[current + '_days'][index];
	                  }),
	                  'months': Object.keys(dataFields[current + '_months'] || {}).map(function (index) {
	                    return dataFields[current + '_months'][index];
	                  }),
	                  'years': Object.keys(dataFields[current + '_years'] || {}).map(function (index) {
	                    return dataFields[current + '_years'][index];
	                  })
	                };
	              }

	              if (field.TYPE === this.types.NUMBER) {
	                field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_numsel'], field.SUB_TYPES);
	                field.VALUES = {
	                  '_from': dataFields[current + '_from'],
	                  '_to': dataFields[current + '_to']
	                };
	              }

	              if (field.TYPE === this.types.DEST_SELECTOR || field.TYPE === this.types.ENTITY_SELECTOR || field.TYPE === this.types.CUSTOM_ENTITY) {
	                if (typeof dataFields[current + '_label'] !== 'undefined') {
	                  field.VALUES._label = dataFields[current + '_label'];
	                }

	                if (typeof dataFields[current] !== 'undefined') {
	                  field.VALUES._value = dataFields[current];
	                }
	              }

	              if (field.TYPE === this.types.CUSTOM) {
	                field._VALUE = dataFields[current];
	              }
	            }

	            fields.push(field);
	          }
	        }, this);
	      }

	      return fields;
	    },

	    /**
	     * Prepares select values
	     * @param value
	     * @param items
	     * @return {object}
	     */
	    prepareSelectValue: function prepareSelectValue(value, items) {
	      var result = {};
	      var tmpResult;

	      if (BX.type.isNotEmptyString(value) && BX.type.isArray(items)) {
	        tmpResult = this.prepareMultiSelectValue({
	          0: value
	        }, items);
	        result = tmpResult.length > 0 ? tmpResult[0] : {};
	      } else {
	        result = items[0];
	      }

	      return result;
	    },

	    /**
	     * Prepares multiselect value
	     * @param values
	     * @param items
	     * @return {Array}
	     */
	    prepareMultiSelectValue: function prepareMultiSelectValue(values, items) {
	      var result = [];

	      if (BX.type.isPlainObject(values) && BX.type.isArray(items)) {
	        var valuesKeys = Object.keys(values);
	        var valuesValues = valuesKeys.map(function (curr) {
	          return values[curr];
	        });
	        result = items.filter(function (current) {
	          return valuesValues.some(function (val) {
	            return val === current.VALUE;
	          });
	        }, this);
	      }

	      return result;
	    },

	    /**
	     * Get field by name
	     * @param {string} name
	     * @return {?object}
	     */
	    getFieldByName: function getFieldByName(name) {
	      var fields = this.getParam('FIELDS');
	      var field = fields.find(function (current) {
	        return current.NAME === name;
	      });

	      if (field) {
	        return field;
	      }

	      var node = this.getFieldListContainer().querySelector('[data-name="' + name + '"]');
	      field = BX.Filter.Field.instances.get(node);

	      if (field) {
	        return field.options;
	      }

	      return null;
	    },

	    /**
	     * @private
	     * @return {Promise}
	     */
	    confirmSaveForAll: function confirmSaveForAll() {
	      return new Promise(function (resolve) {
	        var action = {
	          CONFIRM: true,
	          CONFIRM_MESSAGE: this.getParam('MAIN_UI_FILTER__CONFIRM_MESSAGE_FOR_ALL'),
	          CONFIRM_APPLY_BUTTON: this.getParam('MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL'),
	          CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
	        };
	        this.confirmDialog(action, resolve);
	      }.bind(this));
	    },

	    /**
	     * Save options
	     * @param {object} data
	     * @param {object} [params]
	     * @param {function} [callback]
	     * @param {boolean} [forAll = false]
	     */
	    saveOptions: function saveOptions(data, params, callback, forAll) {
	      params.action = toCamelCase(params.action);
	      params.forAll = this.isForAll(forAll);
	      params.commonPresetsId = this.getParam('COMMON_PRESETS_ID');
	      params.apply_filter = data.apply_filter || "N";
	      params.clear_filter = data.clear_filter || "N";
	      params.with_preset = data.with_preset || "N";
	      params.save = data.save || "N";
	      var requestData = {
	        params: params,
	        data: data
	      };
	      delete data.apply_filter;
	      delete data.save;
	      delete data.clear_filter;
	      delete data.with_preset;

	      if (params.forAll && params.action === 'setFilterArray') {
	        return this.confirmSaveForAll().then(function () {
	          return this.backend(params.action, requestData);
	        }.bind(this)).then(function () {
	          this.disableEdit();
	          this.disableAddPreset();
	        }.bind(this));
	      }

	      return this.backend(params.action, requestData).then(function () {
	        BX.removeClass(this.getFindButton(), this.settings.classWaitButtonClass);
	        BX.type.isFunction(callback) && callback();
	      }.bind(this));
	    },

	    /**
	     *
	     * @param {string} action
	     * @param data
	     */
	    backend: function backend(action, data) {
	      var analyticsLabel = this.analyticsLabel || {};
	      this.analyticsLabel = {};
	      return BX.ajax.runComponentAction('bitrix:main.ui.filter', action, {
	        mode: 'ajax',
	        data: data,
	        analyticsLabel: _objectSpread$1({
	          FILTER_ID: this.getParam('FILTER_ID'),
	          GRID_ID: this.getParam('GRID_ID'),
	          PRESET_ID: data['data']['preset_id'],
	          FIND: data['data'].hasOwnProperty('fields') && data['data']['fields'].hasOwnProperty('FIND') && !!data['data']['fields']['FIND'] ? "Y" : "N",
	          ROWS: BX.Type.isObject(data['data']['additional']) && Object.keys(data['data']['additional']).length == 0 ? "N" : "Y"
	        }, analyticsLabel)
	      });
	    },

	    /**
	     * Sends analytics when limit is enabled
	     */
	    limitAnalyticsSend: function limitAnalyticsSend() {
	      BX.ajax.runComponentAction('bitrix:main.ui.filter', 'limitAnalytics', {
	        mode: 'ajax',
	        data: {},
	        analyticsLabel: {
	          FILTER_ID: this.getParam('FILTER_ID'),
	          LIMIT: this.getParam('FILTER_ID')
	        }
	      });
	    },

	    /**
	     * Prepares event.path
	     * @param event
	     * @return {*}
	     */
	    prepareEvent: function prepareEvent(event) {
	      var i, x;

	      if (!('path' in event) || !event.path.length) {
	        event.path = [event.target];
	        i = 0;

	        while ((x = event.path[i++].parentNode) !== null) {
	          event.path.push(x);
	        }
	      }

	      return event;
	    },

	    /**
	     * Restores removed preset values
	     * VALUE_REQUIRED_MODE = true only
	     */
	    restoreRemovedPreset: function restoreRemovedPreset() {
	      if (this.getParam('VALUE_REQUIRED_MODE')) {
	        var currentPreset = this.getParam('CURRENT_PRESET');

	        if (BX.type.isPlainObject(currentPreset)) {
	          var currentPresetId = currentPreset.ID;
	          var presetNode = this.getPreset().getPresetNodeById(currentPresetId);
	          this.getPreset().applyPreset(currentPresetId);
	          this.getPreset().activatePreset(presetNode);
	        }
	      }
	    },

	    /**
	     * Checks that the event occurred on the scroll bar
	     * @param {MouseEvent} event
	     * @return {boolean}
	     */
	    hasScrollClick: function hasScrollClick(event) {
	      var x = 'clientX' in event ? event.clientX : 'x' in event ? event.x : 0;
	      return x >= document.documentElement.offsetWidth;
	    },

	    /**
	     * Checks whether to use common presets
	     * @return {boolean}
	     */
	    isUseCommonPresets: function isUseCommonPresets() {
	      return !!this.getParam('COMMON_PRESETS_ID');
	    },

	    /**
	     * Checks whether event is inside filter
	     * @param {MouseEvent} event
	     * @returns {boolean}
	     */
	    isInsideFilterEvent: function isInsideFilterEvent(event) {
	      event = this.prepareEvent(event);
	      return (event.path || []).some(function (current) {
	        return BX.type.isDomNode(current) && (BX.hasClass(current, this.settings.classFilterContainer) || BX.hasClass(current, this.settings.classSearchContainer) || BX.hasClass(current, this.settings.classDefaultPopup) || BX.hasClass(current, this.settings.classPopupOverlay) || BX.hasClass(current, this.settings.classSidePanelContainer));
	      }, this);
	    },
	    _onDocumentClick: function _onDocumentClick(event) {
	      var popup = this.getPopup();

	      if (!this.isInsideFilterEvent(event) && !this.hasScrollClick(event)) {
	        if (popup && popup.isShown()) {
	          this.closePopup();

	          if (this.getParam('VALUE_REQUIRED_MODE')) {
	            this.restoreRemovedPreset();
	          }

	          if (this.getParam('VALUE_REQUIRED')) {
	            if (!this.getSearch().getSquares().length) {
	              this.getPreset().applyPinnedPreset();
	            }
	          }
	        }

	        BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
	      }
	    },
	    _onAddFieldClick: function _onAddFieldClick(event) {
	      var popup = this.getFieldsPopup();
	      event.stopPropagation();
	      event.preventDefault();

	      if (popup && !popup.isShown()) {
	        this.showFieldsPopup();
	        this.syncFields();
	      } else {
	        this.closeFieldListPopup();
	      }
	    },

	    /**
	     * Synchronizes field list in popup and filter field list
	     * @param {?{cache: boolean}} [options]
	     */
	    syncFields: function syncFields(options) {
	      if (BX.type.isPlainObject(options)) {
	        if (options.cache === false) {
	          this.fieldsPopupItems = null;
	        }
	      }

	      var fields = this.getPreset().getFields();
	      var items = this.getFieldsPopupItems();
	      var currentId, isNeedCheck;

	      if (BX.type.isArray(items) && items.length) {
	        items.forEach(function (current) {
	          currentId = BX.data(current, 'name').replace('_datesel', '').replace('_numsel', '');
	          isNeedCheck = fields.some(function (field) {
	            return BX.data(field, 'name') === currentId;
	          });

	          if (isNeedCheck) {
	            BX.addClass(current, this.settings.classMenuItemChecked);
	          } else {
	            BX.removeClass(current, this.settings.classMenuItemChecked);
	          }
	        }, this);
	      }
	    },

	    /**
	     * Gets items of popup window with a list of available fields
	     * @return {?HTMLElement[]}
	     */
	    getFieldsPopupItems: function getFieldsPopupItems() {
	      if (!BX.type.isArray(this.fieldsPopupItems)) {
	        var popup = this.getFieldsPopup();

	        if ('contentContainer' in popup && BX.type.isDomNode(popup.contentContainer)) {
	          this.fieldsPopupItems = BX.Filter.Utils.getByClass(popup.contentContainer, this.settings.classMenuItem, true);
	        }

	        this.prepareAnimation();
	      }

	      return this.fieldsPopupItems;
	    },

	    /**
	     * Gets popup container class name by popup items count
	     * @param {int|string} itemsCount
	     * @return {string}
	     */
	    getFieldListContainerClassName: function getFieldListContainerClassName(itemsCount) {
	      var popupColumnsCount = parseInt(this.settings.get('popupColumnsCount', 0), 10);

	      if (popupColumnsCount > 0 && popupColumnsCount <= this.settings.maxPopupColumnCount) {
	        return this.settings.get('classPopupFieldList' + popupColumnsCount + 'Column');
	      }

	      var containerClass = this.settings.classPopupFieldList1Column;

	      if (itemsCount > 6 && itemsCount < 12) {
	        containerClass = this.settings.classPopupFieldList2Column;
	      }

	      if (itemsCount > 12) {
	        containerClass = this.settings.classPopupFieldList3Column;
	      }

	      return containerClass;
	    },

	    /**
	     * Prepares fields declarations
	     * @param {object[]} fields
	     * @return {object[]}
	     */
	    prepareFieldsDecl: function prepareFieldsDecl(fields) {
	      return (fields || []).map(function (item) {
	        return {
	          block: 'main-ui-filter-field-list-item',
	          label: 'LABEL' in item ? item.LABEL : '',
	          id: 'ID' in item ? item.ID : '',
	          name: 'NAME' in item ? item.NAME : '',
	          item: item,
	          sectionId: 'SECTION_ID' in item ? item.SECTION_ID : '',
	          onClick: BX.delegate(this._clickOnFieldListItem, this)
	        };
	      }, this);
	    },

	    /**
	     * Gets lazy load field list
	     * @return {BX.Promise}
	     */
	    getLazyLoadFields: function getLazyLoadFields() {
	      var p = new BX.Promise();
	      BX.ajax({
	        method: 'GET',
	        url: this.getParam("LAZY_LOAD")["GET_LIST"],
	        dataType: 'json',
	        onsuccess: function onsuccess(response) {
	          p.fulfill(response);
	        }
	      });
	      return p;
	    },

	    /**
	     * Gets fields list popup content
	     * @return {BX.Promise}
	     */
	    getFieldsListPopupContent: function getFieldsListPopupContent() {
	      var p = new BX.Promise();
	      var fields = this.getParam('FIELDS');
	      var fieldsCount = BX.type.isArray(fields) ? fields.length : 0;

	      if (this.getParam('LAZY_LOAD')) {
	        var callback = function (response) {
	          p.fulfill(this.getPopupContent(this.settings.classPopupFieldList, this.getFieldListContainerClassName(response.length), this.prepareFieldsDecl(response)));
	        }.bind(this);

	        if (BX.type.isNotEmptyObject(this.getParam('LAZY_LOAD')['CONTROLLER'])) {
	          var sourceComponentName = this.getParam('LAZY_LOAD')['CONTROLLER']['componentName'];
	          var sourceComponentSignedParameters = this.getParam('LAZY_LOAD')['CONTROLLER']['signedParameters'];
	          BX.ajax.runAction(this.getParam('LAZY_LOAD')['CONTROLLER']['getList'], {
	            data: {
	              filterId: this.getParam('FILTER_ID'),
	              componentName: BX.type.isNotEmptyString(sourceComponentName) ? sourceComponentName : '',
	              signedParameters: BX.type.isNotEmptyString(sourceComponentSignedParameters) ? sourceComponentSignedParameters : ''
	            }
	          }).then(function (response) {
	            callback(response.data);
	          }.bind(this), function (response) {});
	        } else {
	          this.getLazyLoadFields().then(callback);
	        }

	        return p;
	      }

	      p.fulfill(this.getPopupContent(this.settings.classPopupFieldList, this.getFieldListContainerClassName(fieldsCount), this.prepareFieldsDecl(fields)));
	      return p;
	    },
	    getPopupContent: function getPopupContent(block, mix, content) {
	      var wrapper = BX.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div></div>"])));

	      if (!this.enableHeadersSections) {
	        var fieldsContent = BX.decl({
	          content: content,
	          block: block,
	          mix: mix
	        });
	        this.setPopupElementWidthFromSettings(fieldsContent);
	        wrapper.appendChild(fieldsContent);

	        if (this.enableFieldsSearch) {
	          this.preparePopupContentHeader(wrapper);
	        }

	        return wrapper;
	      }

	      var defaultHeaderSection = this.getDefaultHeaderSection();
	      var sections = {};
	      content.forEach(function (item) {
	        var sectionId = item.sectionId.length ? item.sectionId : defaultHeaderSection.id;

	        if (sections[sectionId] === undefined) {
	          sections[sectionId] = [];
	        }

	        sections[sectionId].push(item);
	      });
	      this.preparePopupContentHeader(wrapper);
	      this.preparePopupContentFields(wrapper, sections, block, mix);
	      return wrapper;
	    },
	    preparePopupContentHeader: function preparePopupContentHeader(wrapper) {
	      var headerWrapper = BX.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"main-ui-filter-popup-search-header-wrapper\">\n\t\t\t\t\t<div class=\"ui-form-row-inline\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	      wrapper.prepend(headerWrapper);
	      this.preparePopupContentHeaderSections(headerWrapper);
	      this.preparePopupContentHeaderSearch(headerWrapper);
	    },
	    preparePopupContentHeaderSections: function preparePopupContentHeaderSections(headerWrapper) {
	      if (!this.enableHeadersSections) {
	        return;
	      }

	      var headerSectionsWrapper = BX.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t<div class=\"ui-form-content main-ui-filter-popup-search-section-wrapper\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	      headerWrapper.firstElementChild.appendChild(headerSectionsWrapper);
	      var headersSections = this.getHeadersSections();

	      for (var key in headersSections) {
	        var itemClass = this.settings.classPopupSearchSectionItemIcon + (headersSections[key].selected ? " ".concat(this.settings.classPopupSearchSectionItemIconActive) : '');
	        var headerSectionItem = BX.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"main-ui-filter-popup-search-section-item\" data-ui-popup-filter-section-button=\"", "\">\n\t\t\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), key, itemClass, BX.Text.encode(headersSections[key].name));
	        BX.bind(headerSectionItem, 'click', this.onFilterSectionClick.bind(this, headerSectionItem));
	        headerSectionsWrapper.firstElementChild.appendChild(headerSectionItem);
	      }
	    },
	    onFilterSectionClick: function onFilterSectionClick(item) {
	      var activeClass = this.settings.classPopupSearchSectionItemIconActive;
	      var sectionId = item.dataset.uiPopupFilterSectionButton;
	      var section = document.querySelectorAll("[data-ui-popup-filter-section='" + sectionId + "']");

	      if (BX.Dom.hasClass(item.firstElementChild, activeClass)) {
	        BX.Dom.removeClass(item.firstElementChild, activeClass);
	        BX.Dom.hide(section[0]);
	      } else {
	        BX.Dom.addClass(item.firstElementChild, activeClass);
	        BX.Dom.show(section[0]);
	      }
	    },
	    preparePopupContentHeaderSearch: function preparePopupContentHeaderSearch(headerWrapper) {
	      if (!this.enableFieldsSearch) {
	        return;
	      }

	      var searchForm = BX.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t<div class=\"ui-form-content main-ui-filter-popup-search-input-wrapper\">\n\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon\">\n\t\t\t\t\t\t\t<div class=\"ui-ctl-before ui-ctl-icon-search\"></div>\n\t\t\t\t\t\t\t<button class=\"ui-ctl-after ui-ctl-icon-clear\"></button>\n\t\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element ", "\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), this.settings.classPopupSearchSectionItem);
	      headerWrapper.firstElementChild.appendChild(searchForm);
	      var inputs = searchForm.getElementsByClassName(this.settings.classPopupSearchSectionItem);

	      if (inputs.length) {
	        var input = inputs[0];
	        BX.bind(input, 'input', this.onFilterSectionSearchInput.bind(this, input));
	        BX.bind(input.previousElementSibling, 'click', this.onFilterSectionSearchInputClear.bind(this, input));
	      }
	    },
	    preparePopupContentFields: function preparePopupContentFields(wrapper, sections, block, mix) {
	      if (!this.enableHeadersSections) {
	        return;
	      }

	      var sectionsWrapper = BX.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-ui-filter-popup-search-sections-wrapper\"></div>"])));
	      wrapper.appendChild(sectionsWrapper);

	      for (var key in sections) {
	        var sectionWrapper = BX.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"main-ui-filter-popup-section-wrapper\" data-ui-popup-filter-section=\"", "\"></div>\n\t\t\t\t"])), key);
	        this.setPopupElementWidthFromSettings(sectionWrapper);

	        if (!this.getHeadersSectionParam(key, 'selected')) {
	          sectionWrapper.setAttribute('hidden', '');
	        }

	        var sectionTitle = BX.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<h3 class=\"main-ui-filter-popup-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</h3>\n\t\t\t\t"])), BX.Text.encode(this.getHeadersSectionParam(key, 'name')));
	        var fieldsBlock = BX.decl({
	          block: block,
	          mix: mix,
	          content: sections[key]
	        });
	        sectionWrapper.appendChild(sectionTitle);
	        sectionWrapper.appendChild(fieldsBlock);
	        sectionsWrapper.appendChild(sectionWrapper);
	      }
	    },
	    prepareAnimation: function prepareAnimation() {
	      var _this = this;

	      if (this.enableFieldsSearch) {
	        this.fieldsPopupItems.forEach(function (item) {
	          BX.bind(item, 'animationend', _this.onAnimationEnd.bind(_this, item));
	        });
	      }
	    },
	    onAnimationEnd: function onAnimationEnd(item) {
	      item.style.display = BX.Dom.hasClass(item, this.settings.classPopupSearchFieldListItemHidden) ? 'none' : 'inline-block';
	    },
	    onFilterSectionSearchInput: function onFilterSectionSearchInput(input) {
	      var search = input.value;

	      if (search.length) {
	        search = search.toLowerCase();
	      }

	      this.getFieldsPopupItems().forEach(function (item) {
	        var title = item.innerText.toLowerCase();

	        if (search.length && title.indexOf(search) === -1) {
	          BX.Dom.removeClass(item, this.settings.classPopupSearchFieldListItemVisible);
	          BX.Dom.addClass(item, this.settings.classPopupSearchFieldListItemHidden);
	        } else {
	          BX.Dom.removeClass(item, this.settings.classPopupSearchFieldListItemHidden);
	          BX.Dom.addClass(item, this.settings.classPopupSearchFieldListItemVisible);
	          item.style.display = 'inline-block';
	        }
	      }.bind(this));
	    },
	    onFilterSectionSearchInputClear: function onFilterSectionSearchInputClear(input) {
	      if (input.value.length) {
	        input.value = '';
	        this.onFilterSectionSearchInput(input);
	      }
	    },
	    getDefaultHeaderSection: function getDefaultHeaderSection() {
	      var headersSections = this.getHeadersSections();

	      for (var key in headersSections) {
	        if ('selected' in headersSections[key] && headersSections[key].selected) {
	          return headersSections[key];
	        }
	      }

	      return null;
	    },
	    getHeadersSections: function getHeadersSections() {
	      return this.getParam('HEADERS_SECTIONS');
	    },
	    getHeadersSectionParam: function getHeadersSectionParam(sectionId, paramName, defaultValue) {
	      if (this.getHeadersSections()[sectionId] !== undefined && this.getHeadersSections()[sectionId][paramName] !== undefined) {
	        return this.getHeadersSections()[sectionId][paramName];
	      }

	      return defaultValue;
	    },

	    /**
	     * Gets field loader
	     * @return {BX.Loader}
	     */
	    getFieldLoader: function getFieldLoader() {
	      if (!this.fieldLoader) {
	        this.fieldLoader = new BX.Loader({
	          mode: "custom",
	          size: 18,
	          offset: {
	            left: "5px",
	            top: "5px"
	          }
	        });
	      }

	      return this.fieldLoader;
	    },
	    _clickOnFieldListItem: function _clickOnFieldListItem(event) {
	      var target = event.target;
	      var data;

	      if (!BX.hasClass(target, this.settings.classFieldListItem)) {
	        target = BX.findParent(target, {
	          className: this.settings.classFieldListItem
	        }, true, false);
	      }

	      if (BX.type.isDomNode(target)) {
	        try {
	          data = JSON.parse(BX.data(target, 'item'));
	        } catch (err) {}

	        var isChecked = BX.hasClass(target, this.settings.classMenuItemChecked);

	        var _event = new BX.Event.BaseEvent({
	          data: data
	        });

	        this.emitter.emit(isChecked ? 'onBeforeRemoveFilterItem' : 'onBeforeAddFilterItem', _event);

	        if (_event.isDefaultPrevented()) {
	          return;
	        }

	        var p = new BX.Promise();

	        if (this.getParam("LAZY_LOAD")) {
	          this.getFieldLoader().show(target);
	          var label = target.querySelector(".main-ui-select-inner-label");

	          if (label) {
	            label.classList.add("main-ui-no-before");
	          }

	          var callback = function (response) {
	            p.fulfill(response);
	            this.getFieldLoader().hide();

	            if (label) {
	              label.classList.remove("main-ui-no-before");
	            }
	          }.bind(this);

	          if (BX.type.isNotEmptyObject(this.getParam('LAZY_LOAD')['CONTROLLER'])) {
	            var sourceComponentName = this.getParam('LAZY_LOAD')['CONTROLLER']['componentName'];
	            var sourceComponentSignedParameters = this.getParam('LAZY_LOAD')['CONTROLLER']['signedParameters'];
	            BX.ajax.runAction(this.getParam('LAZY_LOAD')['CONTROLLER']['getField'], {
	              data: {
	                filterId: this.getParam('FILTER_ID'),
	                id: data.NAME,
	                componentName: BX.type.isNotEmptyString(sourceComponentName) ? sourceComponentName : '',
	                signedParameters: BX.type.isNotEmptyString(sourceComponentSignedParameters) ? sourceComponentSignedParameters : ''
	              }
	            }).then(function (response) {
	              callback(response.data);
	            }.bind(this), function (response) {});
	          } else {
	            this.getLazyLoadField(data.NAME).then(callback);
	          }
	        } else {
	          p.fulfill(data);
	        }

	        p.then(function (response) {
	          this.params.FIELDS.push(response);

	          if (BX.hasClass(target, this.settings.classMenuItemChecked)) {
	            BX.removeClass(target, this.settings.classMenuItemChecked);
	            this.getPreset().removeField(response);
	          } else {
	            if (BX.type.isPlainObject(response)) {
	              this.getPreset().addField(response);
	              BX.addClass(target, this.settings.classMenuItemChecked);

	              if (BX.type.isString(response.HTML)) {
	                var wrap = BX.create("div");
	                this.getHiddenElement().appendChild(wrap);
	                BX.html(wrap, response.HTML);
	              }
	            }
	          }

	          this.syncFields();
	        }.bind(this));
	      }
	    },
	    getHiddenElement: function getHiddenElement() {
	      if (!this.hiddenElement) {
	        this.hiddenElement = BX.create("div");
	        document.body.appendChild(this.hiddenElement);
	      }

	      return this.hiddenElement;
	    },

	    /**
	     * Gets lazy load fields
	     * @param id
	     * @return {BX.Promise}
	     */
	    getLazyLoadField: function getLazyLoadField(id) {
	      var p = new BX.Promise();
	      BX.ajax({
	        method: 'get',
	        url: BX.util.add_url_param(this.getParam("LAZY_LOAD")["GET_FIELD"], {
	          id: id
	        }),
	        dataType: 'json',
	        onsuccess: function onsuccess(response) {
	          p.fulfill(response);
	        }
	      });
	      return p;
	    },

	    /**
	     * Shows fields list popup
	     */
	    showFieldsPopup: function showFieldsPopup() {
	      var popup = this.getFieldsPopup();
	      this.adjustFieldListPopupPosition();
	      popup.show();
	    },

	    /**
	     * Closes fields list popup
	     */
	    closeFieldListPopup: function closeFieldListPopup() {
	      var popup = this.getFieldsPopup();
	      popup.close();
	    },

	    /**
	     * Adjusts field list popup position
	     */
	    adjustFieldListPopupPosition: function adjustFieldListPopupPosition() {
	      var popup = this.getFieldsPopup();
	      var pos = BX.pos(this.getAddField());
	      pos.forceBindPosition = true;
	      popup.adjustPosition(pos);
	    },

	    /**
	     * Gets field list popup instance
	     * @return {BX.PopupWindow}
	     */
	    getFieldsPopup: function getFieldsPopup() {
	      var bindElement = this.settings.get('showPopupInCenter', false) ? null : this.getAddField();

	      if (!this.fieldsPopup) {
	        this.fieldsPopup = new BX.PopupWindow(this.getParam('FILTER_ID') + '_fields_popup', bindElement, {
	          autoHide: true,
	          offsetTop: 4,
	          offsetLeft: 0,
	          lightShadow: true,
	          closeIcon: bindElement === null,
	          closeByEsc: bindElement === null,
	          noAllPaddings: true,
	          zIndex: 13
	        });
	        this.fieldsPopupLoader = new BX.Loader({
	          target: this.fieldsPopup.contentContainer
	        });
	        this.fieldsPopupLoader.show();
	        this.setPopupElementWidthFromSettings(this.fieldsPopup.contentContainer);
	        this.fieldsPopup.contentContainer.style.height = "330px";
	        this.getFieldsListPopupContent().then(function (res) {
	          this.fieldsPopup.contentContainer.removeAttribute("style");
	          this.fieldsPopupLoader.hide();
	          this.fieldsPopup.setContent(res);
	          this.syncFields({
	            cache: false
	          });
	          this.adjustFieldListPopupPosition();
	        }.bind(this));
	      }

	      return this.fieldsPopup;
	    },
	    setPopupElementWidthFromSettings: function setPopupElementWidthFromSettings(element) {
	      element.style.width = this.settings.popupWidth + 'px';
	    },
	    _onAddPresetClick: function _onAddPresetClick() {
	      this.enableAddPreset();
	    },

	    /**
	     * Enables shows wait spinner for button
	     * @param {HTMLElement} button
	     */
	    enableWaitSate: function enableWaitSate(button) {
	      !!button && BX.addClass(button, this.settings.classWaitButtonClass);
	    },

	    /**
	     * Disables shows wait spinner for button
	     * @param {HTMLElement} button
	     */
	    disableWaitState: function disableWaitState(button) {
	      !!button && BX.removeClass(button, this.settings.classWaitButtonClass);
	    },
	    _onSaveButtonClick: function _onSaveButtonClick() {
	      var forAll = !!this.getSaveForAllCheckbox() && this.getSaveForAllCheckbox().checked;
	      var input = this.getPreset().getAddPresetFieldInput();
	      var mask = input.parentNode.querySelector(".main-ui-filter-edit-mask");
	      var presetName;

	      function onAnimationEnd(event) {
	        if (event.animationName === "fieldError") {
	          event.currentTarget.removeEventListener("animationend", onAnimationEnd);
	          event.currentTarget.removeEventListener("oAnimationEnd", onAnimationEnd);
	          event.currentTarget.removeEventListener("webkitAnimationEnd", onAnimationEnd);
	          event.currentTarget.classList.remove("main-ui-filter-error");
	        }
	      }

	      function showLengthError(mask) {
	        mask.addEventListener("animationend", onAnimationEnd);
	        mask.addEventListener("oAnimationEnd", onAnimationEnd);
	        mask.addEventListener("webkitAnimationEnd", onAnimationEnd);
	        mask.classList.add("main-ui-filter-error");
	        var promise = new BX.Promise();
	        promise.fulfill(true);
	        return promise;
	      }

	      this.enableWaitSate(this.getFindButton());

	      if (this.isAddPresetEnabled() && !forAll) {
	        presetName = input.value;

	        if (presetName.length) {
	          this.savePreset();
	          this.disableAddPreset();
	        } else {
	          showLengthError(mask).then(function () {
	            input.focus();
	          });
	        }
	      }

	      if (this.isEditEnabled()) {
	        var preset = this.getPreset();
	        var presetNode = preset.getPresetNodeById(preset.getCurrentPresetId());
	        var presetNameInput = preset.getPresetInput(presetNode);

	        if (presetNameInput.value.length) {
	          preset.updateEditablePreset(preset.getCurrentPresetId());
	          this.saveUserSettings(forAll);
	          !forAll && this.disableEdit();
	        } else {
	          var presetMask = presetNode.querySelector(".main-ui-filter-edit-mask");
	          showLengthError(presetMask).then(function () {
	            presetNameInput.focus();
	          });
	        }
	      }
	    },
	    _onCancelButtonClick: function _onCancelButtonClick() {
	      this.disableAddPreset();
	      this.getPreset().clearAddPresetFieldInput();
	      this.disableEdit();
	      !!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
	    },
	    _onGridReady: function _onGridReady(grid) {
	      if (!this.grid && grid.getContainerId() === this.getParam('GRID_ID')) {
	        this.grid = grid;
	      }
	    },
	    _onFilterMousedown: function _onFilterMousedown(event) {
	      var target = event.target;

	      if (this.getFields().isDragButton(target)) {
	        var inputs = BX.Filter.Utils.getByTag(target.parentNode, 'input', true);
	        (inputs || []).forEach(function (item) {
	          BX.fireEvent(item, 'blur');
	        });
	        BX.fireEvent(this.getFilter(), 'click');
	      }
	    },
	    _onFilterClick: function _onFilterClick(event) {
	      var Fields = this.getFields();
	      var Presets = this.getPreset();
	      var field;

	      if (Fields.isFieldDelete(event.target)) {
	        field = Fields.getField(event.target);
	        Presets.removeField(field);
	      }

	      if (Fields.isFieldValueDelete(event.target)) {
	        field = Fields.getField(event.target);
	        Fields.clearFieldValue(field);
	      }
	    },

	    /**
	     * Gets filter buttons container
	     * @return {?HTMLElement}
	     */
	    getButtonsContainer: function getButtonsContainer() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classButtonsContainer);
	    },

	    /**
	     * Gets save button element
	     * @return {?HTMLElement}
	     */
	    getSaveButton: function getSaveButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSaveButton);
	    },

	    /**
	     * Gets cancel element
	     * @return {?HTMLElement}
	     */
	    getCancelButton: function getCancelButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classCancelButton);
	    },

	    /**
	     * Gets find button element
	     * @return {?HTMLElement}
	     */
	    getFindButton: function getFindButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFindButton);
	    },

	    /**
	     * Gets reset button element
	     * @return {?HTMLElement}
	     */
	    getResetButton: function getResetButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classResetButton);
	    },

	    /**
	     * Gets add preset button
	     * @return {?HTMLElement}
	     */
	    getAddPresetButton: function getAddPresetButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddPresetButton);
	    },

	    /**
	     * Checks that add preset mode enabled
	     * @return {boolean}
	     */
	    isAddPresetEnabled: function isAddPresetEnabled() {
	      return this.isAddPresetModeState;
	    },

	    /**
	     * Enables add preset mode
	     */
	    enableAddPreset: function enableAddPreset() {
	      var Preset = this.getPreset();
	      var addPresetField = Preset.getAddPresetField();
	      var addPresetFieldInput = Preset.getAddPresetFieldInput();
	      var buttonsContainer = this.getButtonsContainer();
	      BX.show(addPresetField);
	      BX.show(buttonsContainer);
	      BX.hide(this.getPresetButtonsContainer());
	      this.hideForAllCheckbox();

	      if (BX.type.isDomNode(addPresetFieldInput)) {
	        addPresetFieldInput.focus();
	      }

	      BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
	      this.isAddPresetModeState = true;
	    },

	    /**
	     * Disables add preset mode
	     */
	    disableAddPreset: function disableAddPreset() {
	      var Preset = this.getPreset();
	      var addPresetField = Preset.getAddPresetField();
	      var buttonsContainer = this.getButtonsContainer();
	      BX.hide(addPresetField);
	      BX.hide(buttonsContainer);
	      BX.show(this.getPresetButtonsContainer());
	      this.showForAllCheckbox();
	      Preset.getAddPresetFieldInput().value = '';
	      BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
	      this.isAddPresetModeState = false;
	    },

	    /**
	     * Gets control from field list
	     * @return {?HTMLElement[]}
	     */
	    getControls: function getControls() {
	      var container = this.getFieldListContainer();
	      var controls = null;

	      if (BX.type.isDomNode(container)) {
	        controls = BX.Filter.Utils.getByClass(container, this.settings.classControl, true);
	      }

	      return controls;
	    },

	    /**
	     * Gets filter fields
	     * @return {?HTMLElement[]}
	     */
	    getFilterFields: function getFilterFields() {
	      var container = this.getFieldListContainer();
	      var fields = [];
	      var groups = [];

	      if (BX.type.isDomNode(container)) {
	        fields = BX.Filter.Utils.getByClass(container, this.settings.classField, true);
	        groups = BX.Filter.Utils.getByClass(container, this.settings.classFieldGroup, true);

	        if (!BX.type.isArray(fields)) {
	          fields = [];
	        }

	        if (BX.type.isArray(groups)) {
	          groups.forEach(function (current) {
	            fields.push(current);
	          });
	        }
	      }

	      return fields;
	    },

	    /**
	     * Gets filter fields values
	     * @return {object}
	     */
	    getFilterFieldsValues: function getFilterFieldsValues() {
	      var fields = this.getPreset().getFields();
	      var Search = this.getSearch();
	      var values = {};
	      var type, name;
	      values['FIND'] = Search.getInput().value;

	      if (BX.type.isArray(fields) && fields.length) {
	        fields.forEach(function (current) {
	          var additionalFilter = BX.Filter.AdditionalFilter.getInstance().getFilter(current);

	          if (additionalFilter) {
	            Object.assign(values, additionalFilter);
	            return;
	          }

	          type = BX.data(current, 'type');
	          name = BX.data(current, 'name');

	          switch (type) {
	            case this.types.STRING:
	              {
	                this.prepareControlStringValue(values, current);
	                break;
	              }

	            case this.types.TEXTAREA:
	              {
	                this.prepareControlTextareaValue(values, current);
	                break;
	              }

	            case this.types.NUMBER:
	              {
	                this.prepareControlNumberValue(values, name, current);
	                break;
	              }

	            case this.types.DATE:
	              {
	                this.prepareControlDateValue(values, name, current);
	                break;
	              }

	            case this.types.CUSTOM_DATE:
	              {
	                this.prepareControlCustomDateValue(values, name, current);
	                break;
	              }

	            case this.types.SELECT:
	              {
	                this.prepareControlSelectValue(values, name, current);
	                break;
	              }

	            case this.types.MULTI_SELECT:
	              {
	                this.prepareControlMultiselectValue(values, name, current);
	                break;
	              }

	            case this.types.DEST_SELECTOR:
	            case this.types.CUSTOM_ENTITY:
	            case this.types.ENTITY_SELECTOR:
	              {
	                this.prepareControlCustomEntityValue(values, name, current);
	                break;
	              }

	            case this.types.CUSTOM:
	              {
	                this.prepareControlCustomValue(values, name, current);
	                break;
	              }

	            default:
	              {
	                break;
	              }
	          }
	        }, this);
	      }

	      return values;
	    },

	    /**
	     * @param values
	     * @param name
	     * @param field
	     */
	    prepareControlCustomEntityValue: function prepareControlCustomEntityValue(values, name, field) {
	      var squares = this.fetchSquares(field);
	      var squaresData = this.fetchSquaresData(squares);
	      var isMultiple = BX.Main.ui.CustomEntity.isMultiple(field);
	      values[name] = '';
	      values[name + '_label'] = '';

	      if (isMultiple) {
	        values[name] = [];
	        values[name + '_label'] = [];
	        !!squaresData && squaresData.forEach(function (item) {
	          values[name].push(item._value.toString());
	          values[name + '_label'].push(item._label.toString());
	        });
	      } else {
	        if (squaresData.length) {
	          values[name] = squaresData[0]._value.toString();
	          values[name + '_label'] = squaresData[0]._label.toString();
	        }
	      }
	    },

	    /**
	     * @param {HTMLElement} field
	     * @return {HTMLElement[]}
	     */
	    fetchSquares: function fetchSquares(field) {
	      return !!field ? BX.Filter.Utils.getByClass(field, this.settings.classSquare, true) : [];
	    },

	    /**
	     * @param {HTMLElement[]} squares
	     * @return {object[]}
	     */
	    fetchSquaresData: function fetchSquaresData(squares) {
	      return squares.map(function (square) {
	        return JSON.parse(BX.data(square, 'item'));
	      }, this);
	    },

	    /**
	     * @param {object} values
	     * @param {string} name
	     * @param {HTMLElement} field
	     */
	    prepareControlCustomValue: function prepareControlCustomValue(values, name, field) {
	      var stringFields = BX.Filter.Utils.getByTag(field, 'input', true);
	      values[name] = '';

	      if (BX.type.isArray(stringFields)) {
	        stringFields.forEach(function (current) {
	          if (BX.type.isNotEmptyString(current.name)) {
	            values[current.name] = current.value;
	          }
	        });
	      }
	    },
	    prepareControlMultiselectValue: function prepareControlMultiselectValue(values, name, field) {
	      var select = BX.Filter.Utils.getByClass(field, this.settings.classMultiSelect);
	      var value = JSON.parse(BX.data(select, 'value'));
	      values[name] = '';

	      if (BX.type.isArray(value) && value.length) {
	        values[name] = {};
	        value.forEach(function (current, index) {
	          values[name][index] = current.VALUE;
	        });
	      }
	    },
	    prepareControlSelectValue: function prepareControlSelectValue(values, name, field) {
	      var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
	      var value = JSON.parse(BX.data(select, 'value'));
	      values[name] = value.VALUE;
	    },
	    prepareControlCustomDateValue: function prepareControlCustomDateValue(values, name, field) {
	      var daysControl = field.querySelector("[data-name=\"" + name + '_days' + "\"]");

	      if (daysControl) {
	        var daysValue = JSON.parse(daysControl.dataset.value);
	        values[name + '_days'] = daysValue.map(function (item) {
	          return item.VALUE;
	        });
	      }

	      var monthsControl = field.querySelector("[data-name=\"" + name + '_months' + "\"]");

	      if (monthsControl) {
	        var monthsValue = JSON.parse(monthsControl.dataset.value);
	        values[name + '_months'] = monthsValue.map(function (item) {
	          return item.VALUE;
	        });
	      }

	      var yearsControl = field.querySelector("[data-name=\"" + name + '_years' + "\"]");

	      if (yearsControl) {
	        var yearsValue = JSON.parse(yearsControl.dataset.value);
	        values[name + '_years'] = yearsValue.map(function (item) {
	          return item.VALUE;
	        });
	      }
	    },
	    prepareControlDateValue: function prepareControlDateValue(values, name, field, withAdditional) {
	      var additionalFieldsContainer = field.querySelector('.main-ui-filter-additional-fields-container');

	      if (additionalFieldsContainer && !withAdditional) {
	        BX.remove(additionalFieldsContainer);
	      }

	      var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
	      var yearsSwitcher = field.querySelector(".main-ui-select[data-name*=\"_allow_year\"]");
	      var selectName = name + this.settings.datePostfix;
	      var fromName = name + this.settings.fromPostfix;
	      var toName = name + this.settings.toPostfix;
	      var daysName = name + this.settings.daysPostfix;
	      var monthName = name + this.settings.monthPostfix;
	      var quarterName = name + this.settings.quarterPostfix;
	      var yearName = name + this.settings.yearPostfix;
	      var yearsSwitcherName = name + "_allow_year";
	      var selectValue, stringFields, controls, controlName, yearsSwitcherValue;
	      values[selectName] = '';
	      values[fromName] = '';
	      values[toName] = '';
	      values[daysName] = '';
	      values[monthName] = '';
	      values[quarterName] = '';
	      values[yearName] = '';
	      var input = field.querySelector(".main-ui-date-input");

	      if (input && input.dataset.isValid === "false") {
	        return;
	      }

	      selectValue = JSON.parse(BX.data(select, 'value'));
	      values[selectName] = selectValue.VALUE;

	      if (yearsSwitcher) {
	        yearsSwitcherValue = JSON.parse(BX.data(yearsSwitcher, 'value'));
	        values[yearsSwitcherName] = yearsSwitcherValue.VALUE;
	      }

	      switch (selectValue.VALUE) {
	        case this.dateTypes.EXACT:
	          {
	            stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput);
	            values[fromName] = stringFields.value;
	            values[toName] = stringFields.value;
	            break;
	          }

	        case this.dateTypes.QUARTER:
	          {
	            controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

	            if (BX.type.isArray(controls)) {
	              controls.forEach(function (current) {
	                controlName = BX.data(current, 'name');

	                if (controlName && controlName.indexOf('_quarter') !== -1) {
	                  values[quarterName] = JSON.parse(BX.data(current, 'value')).VALUE;
	                }

	                if (controlName && controlName.endsWith('_year') && !controlName.endsWith('_allow_year')) {
	                  values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
	                }
	              }, this);
	            }

	            break;
	          }

	        case this.dateTypes.YEAR:
	          {
	            controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

	            if (BX.type.isArray(controls)) {
	              controls.forEach(function (current) {
	                controlName = BX.data(current, 'name');

	                if (controlName && controlName.endsWith('_year') && !controlName.endsWith('_allow_year')) {
	                  values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
	                }
	              }, this);
	            }

	            break;
	          }

	        case this.dateTypes.MONTH:
	          {
	            controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

	            if (BX.type.isArray(controls)) {
	              controls.forEach(function (current) {
	                controlName = BX.data(current, 'name');

	                if (controlName && controlName.indexOf('_month') !== -1) {
	                  values[monthName] = JSON.parse(BX.data(current, 'value')).VALUE;
	                }

	                if (controlName && controlName.endsWith('_year') && !controlName.endsWith('_allow_year')) {
	                  values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
	                }
	              }, this);
	            }

	            break;
	          }

	        case this.additionalDateTypes.PREV_DAY:
	        case this.additionalDateTypes.NEXT_DAY:
	        case this.additionalDateTypes.MORE_THAN_DAYS_AGO:
	        case this.additionalDateTypes.AFTER_DAYS:
	        case this.dateTypes.NEXT_DAYS:
	        case this.dateTypes.PREV_DAYS:
	          {
	            var control = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput);

	            if (!!control && control.name === daysName) {
	              values[daysName] = control.value;
	            }

	            break;
	          }

	        case this.dateTypes.RANGE:
	          {
	            stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput, true);
	            stringFields.forEach(function (current) {
	              if (current.name === fromName) {
	                values[fromName] = current.value;
	              } else if (current.name === toName) {
	                values[toName] = current.value;
	              }
	            }, this);
	            break;
	          }

	        case "CUSTOM_DATE":
	          {
	            var customValues = {};
	            this.prepareControlCustomDateValue(customValues, name, field);
	            values[name + '_days'] = customValues[name + '_days'];
	            values[monthName] = customValues[name + '_months'];
	            values[yearName] = customValues[name + '_years'];
	            break;
	          }

	        default:
	          {
	            break;
	          }
	      }

	      if (additionalFieldsContainer && !withAdditional) {
	        BX.append(additionalFieldsContainer, field);
	      }

	      var additionalFields = Array.from(field.querySelectorAll('.main-ui-filter-additional-fields-container > [data-type="DATE"]'));

	      if (additionalFields) {
	        additionalFields.forEach(function (additionalField) {
	          var name = additionalField.dataset.name;
	          this.prepareControlDateValue(values, name, additionalField, true);
	        }, this);
	      }
	    },
	    prepareControlNumberValue: function prepareControlNumberValue(values, name, field) {
	      var stringFields = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput, true);
	      var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
	      var selectName = name + this.settings.numberPostfix;
	      var fromName = name + this.settings.fromPostfix;
	      var toName = name + this.settings.toPostfix;
	      var selectValue;
	      values[fromName] = '';
	      values[toName] = '';
	      selectValue = JSON.parse(BX.data(select, 'value'));
	      values[selectName] = selectValue.VALUE;
	      stringFields.forEach(function (current) {
	        if (current.name.indexOf(this.settings.fromPostfix) !== -1) {
	          values[fromName] = current.value || '';

	          if (values[selectName] === 'exact') {
	            values[toName] = current.value || '';
	          }
	        } else if (current.name.indexOf(this.settings.toPostfix) !== -1) {
	          values[toName] = current.value || '';
	        }
	      }, this);
	    },
	    prepareControlStringValue: function prepareControlStringValue(values, field) {
	      var control = BX.Filter.Utils.getByClass(field, this.settings.classStringInput);
	      var name;

	      if (BX.type.isDomNode(control)) {
	        name = control.name;
	        values[name] = control.value;
	      }
	    },
	    prepareControlTextareaValue: function prepareControlTextareaValue(values, field) {
	      var control = BX.Filter.Utils.getByClass(field, this.settings.classStringInput);
	      var name;

	      if (BX.type.isDomNode(control)) {
	        name = control.name;
	        values[name] = control.value;
	      }
	    },

	    /**
	     * Shows grid animation
	     */
	    showGridAnimation: function showGridAnimation() {
	      this.grid && this.grid.tableFade();
	    },

	    /**
	     * Hides grid animations
	     */
	    hideGridAnimation: function hideGridAnimation() {
	      this.grid && this.grid.tableUnfade();
	    },

	    /**
	     * @private
	     * @param {?Boolean} clear - is need reset filter
	     * @param {?Boolean} applyPreset - is need apply preset
	     * @return {String}
	     */
	    getPresetId: function getPresetId(clear, applyPreset) {
	      var presetId = this.getPreset().getCurrentPresetId();

	      if (!this.isEditEnabled() && !this.isAddPresetEnabled() && !applyPreset || presetId === 'default_filter' && !clear) {
	        presetId = 'tmp_filter';
	      }

	      return presetId;
	    },
	    isAppliedUserFilter: function isAppliedUserFilter() {
	      var _this2 = this;

	      var presetOptions = this.getPreset().getCurrentPresetData();

	      if (BX.Type.isPlainObject(presetOptions)) {
	        var hasFields = BX.Type.isArrayFilled(presetOptions.FIELDS) && presetOptions.FIELDS.some(function (field) {
	          return !_this2.getPreset().isEmptyField(field);
	        });
	        var hasAdditional = BX.Type.isArrayFilled(presetOptions.ADDITIONAL) && presetOptions.ADDITIONAL.some(function (field) {
	          return !_this2.getPreset().isEmptyField(field);
	        });
	        return !presetOptions.IS_PINNED && (hasFields || hasAdditional) || presetOptions.IS_PINNED && BX.Type.isArrayFilled(presetOptions.ADDITIONAL) || BX.Type.isStringFilled(this.getSearch().getSearchString());
	      }

	      return false;
	    },

	    /**
	     * Applies filter
	     * @param {?Boolean} [clear] - is need reset filter
	     * @param {?Boolean} [applyPreset] - is need apply preset
	     * @return {BX.Promise}
	     */
	    applyFilter: function applyFilter(clear, applyPreset) {
	      var presetId = this.getPresetId(clear, applyPreset);
	      var filterId = this.getParam('FILTER_ID');
	      var promise = new BX.Promise(null, this);
	      var Preset = this.getPreset();
	      var Search = this.getSearch();
	      var applyParams = {
	        autoResolve: !this.grid
	      };
	      var self = this;

	      if (this.isAppliedUserFilter()) {
	        BX.Dom.addClass(this.getSearch().container, 'main-ui-filter-search--active');
	      } else {
	        BX.Dom.removeClass(this.getSearch().container, 'main-ui-filter-search--active');
	      }

	      this.clearGet();
	      this.showGridAnimation();
	      var action = clear ? "clear" : "apply";
	      BX.onCustomEvent(window, 'BX.Main.Filter:beforeApply', [filterId, {
	        action: action
	      }, this, promise]);
	      this.updatePreset(presetId, null, clear, null).then(function () {
	        Search.updatePreset(Preset.getPreset(presetId));

	        if (self.getParam('VALUE_REQUIRED')) {
	          if (!Search.getSquares().length) {
	            self.lastPromise = Preset.applyPinnedPreset();
	          }
	        }
	      }).then(function () {
	        var params = {
	          apply_filter: 'Y',
	          clear_nav: 'Y'
	        };
	        var fulfill = BX.delegate(promise.fulfill, promise);
	        var reject = BX.delegate(promise.reject, promise);
	        self.grid && self.grid.reloadTable('POST', params, fulfill, reject);
	        BX.onCustomEvent(window, 'BX.Main.Filter:apply', [filterId, {
	          action: action
	        }, self, promise, applyParams]);
	        applyParams.autoResolve && promise.fulfill();
	      });
	      return promise;
	    },

	    /**
	     * Gets add field buttons
	     * @return {?HTMLElement}
	     */
	    getAddField: function getAddField() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddField);
	    },

	    /**
	     * Gets fields list container
	     * @return {?HTMLElement}
	     */
	    getFieldListContainer: function getFieldListContainer() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFileldControlList);
	    },

	    /**
	     * @return {BX.Filter.Fields}
	     */
	    getFields: function getFields() {
	      if (!(this.fields instanceof BX.Filter.Fields)) {
	        this.fields = new BX.Filter.Fields(this);
	      }

	      return this.fields;
	    },

	    /**
	     * @return {BX.Filter.Presets}
	     */
	    getPreset: function getPreset() {
	      if (!(this.presets instanceof BX.Filter.Presets)) {
	        this.presets = new BX.Filter.Presets(this);
	      }

	      return this.presets;
	    },

	    /**
	     * @param controlData
	     * @return {*}
	     */
	    resetControlData: function resetControlData(controlData) {
	      if (BX.type.isPlainObject(controlData)) {
	        switch (controlData.TYPE) {
	          case this.types.MULTI_SELECT:
	            {
	              controlData.VALUE = [];
	              break;
	            }

	          case this.types.SELECT:
	            {
	              controlData.VALUE = controlData.ITEMS[0];
	              break;
	            }

	          case this.types.DATE:
	            {
	              controlData.SUB_TYPE = controlData.SUB_TYPES[0];
	              controlData.VALUES = {
	                '_from': '',
	                '_to': '',
	                '_days': '',
	                '_quarter': '',
	                '_year': ''
	              };
	              break;
	            }

	          case this.types.CUSTOM_DATE:
	            {
	              controlData.VALUES = {
	                'days': [],
	                'months': [],
	                'years': []
	              };
	              break;
	            }

	          case this.types.NUMBER:
	            {
	              controlData.SUB_TYPE = controlData.SUB_TYPES[0];
	              controlData.VALUES = {
	                '_from': '',
	                '_to': ''
	              };
	              break;
	            }

	          case this.types.DEST_SELECTOR:
	          case this.types.ENTITY_SELECTOR:
	          case this.types.CUSTOM_ENTITY:
	            {
	              controlData.VALUES = {
	                '_label': '',
	                '_value': ''
	              };
	              break;
	            }

	          case this.types.CUSTOM:
	            {
	              controlData._VALUE = '';
	              break;
	            }

	          default:
	            {
	              controlData.VALUE = '';
	            }
	        }
	      }

	      return controlData;
	    },
	    clearControl: function clearControl(name) {
	      var control = this.getPreset().getField({
	        NAME: name
	      });
	      var controlData, newControl;

	      if (BX.type.isDomNode(control)) {
	        controlData = this.getFieldByName(name);
	        controlData = this.resetControlData(controlData);
	        newControl = this.getPreset().createControl(controlData);
	        BX.insertAfter(newControl, control);
	        BX.remove(control);
	      }
	    },
	    clearControls: function clearControls(squareData) {
	      if (BX.type.isArray(squareData)) {
	        squareData.forEach(function (item) {
	          'name' in item && this.clearControl(item.name);
	        }, this);
	      } else if (BX.type.isPlainObject(squareData) && 'name' in squareData) {
	        this.clearControl(squareData.name);
	      }
	    },

	    /**
	     * Gets filter popup template
	     * @return {?string}
	     */
	    getTemplate: function getTemplate() {
	      return BX.html(BX(this.settings.generalTemplateId));
	    },
	    isIe: function isIe() {
	      if (!BX.type.isBoolean(this.ie)) {
	        this.ie = BX.hasClass(document.documentElement, 'bx-ie');
	      }

	      return this.ie;
	    },

	    /**
	     * Closes filter popup
	     */
	    closePopup: function closePopup() {
	      var popup = this.getPopup();
	      var popupContainer = popup.popupContainer;
	      var configCloseDelay = this.settings.get('FILTER_CLOSE_DELAY');
	      var closeDelay;
	      BX.Dom.removeClass(this.getSearch().container, 'main-ui-filter-search--showed');
	      setTimeout(BX.delegate(function () {
	        if (!this.isIe()) {
	          BX.removeClass(popupContainer, this.settings.classAnimationShow);
	          BX.addClass(popupContainer, this.settings.classAnimationClose);
	          closeDelay = parseFloat(BX.style(popupContainer, 'animation-duration'));

	          if (BX.type.isNumber(closeDelay)) {
	            closeDelay = closeDelay * 1000;
	          }

	          setTimeout(function () {
	            popup.close();
	          }, closeDelay);
	        } else {
	          popup.close();
	        }
	      }, this), configCloseDelay);

	      if (this.getParam("LIMITS_ENABLED")) {
	        BX.removeClass(this.getFilter(), this.settings.classLimitsAnimation);
	      }

	      this.closeFieldListPopup();
	      this.adjustFocus();
	    },

	    /**
	     * Shows filter popup
	     */
	    showPopup: function showPopup() {
	      var popup = this.getPopup();
	      var popupContainer;

	      if (!popup.isShown()) {
	        BX.Dom.addClass(this.getSearch().container, 'main-ui-filter-search--showed');
	        this.isOpened = true;
	        var showDelay = this.settings.get('FILTER_SHOW_DELAY');

	        if (this.getParam('LIMITS_ENABLED') === true) {
	          this.limitAnalyticsSend();
	        }

	        setTimeout(BX.delegate(function () {
	          popup.show();

	          if (!this.isIe()) {
	            popupContainer = popup.popupContainer;
	            BX.removeClass(popupContainer, this.settings.classAnimationClose);
	            BX.addClass(popupContainer, this.settings.classAnimationShow);
	            BX.onCustomEvent(window, "BX.Main.Filter:show", [this]);
	          }

	          var textareas = [].slice.call(this.getFieldListContainer().querySelectorAll('textarea'));
	          textareas.forEach(function (item) {
	            BX.style(item, 'height', item.scrollHeight + 'px');
	          });
	        }, this), showDelay);
	      }
	    },

	    /**
	     * Gets save for all checkbox element
	     * @return {?HTMLInputElement}
	     */
	    getSaveForAllCheckbox: function getSaveForAllCheckbox() {
	      if (!this.saveForAllCheckbox && !!this.getSaveForAllCheckboxContainer()) {
	        this.saveForAllCheckbox = BX.Filter.Utils.getBySelector(this.getSaveForAllCheckboxContainer(), 'input[type="checkbox"]');
	      }

	      return this.saveForAllCheckbox;
	    },

	    /**
	     * Gets save for all checkbox container
	     * @return {?HTMLElement}
	     */
	    getSaveForAllCheckboxContainer: function getSaveForAllCheckboxContainer() {
	      if (!this.saveForAllCheckboxContainer) {
	        this.saveForAllCheckboxContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
	      }

	      return this.saveForAllCheckboxContainer;
	    },

	    /**
	     * Shows for all checkbox
	     */
	    showForAllCheckbox: function showForAllCheckbox() {
	      !!this.getSaveForAllCheckboxContainer() && BX.removeClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
	    },

	    /**
	     * Hides for all checkbox
	     */
	    hideForAllCheckbox: function hideForAllCheckbox() {
	      !!this.getSaveForAllCheckboxContainer() && BX.addClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
	    },

	    /**
	     * Gets popup bind element
	     * @return {?HTMLElement}
	     */
	    getPopupBindElement: function getPopupBindElement() {
	      if (!this.popupBindElement) {
	        var selector = this.settings.get('POPUP_BIND_ELEMENT_SELECTOR');
	        var result = null;

	        if (BX.type.isNotEmptyString(selector)) {
	          result = BX.Filter.Utils.getBySelector(document, selector);
	        }

	        this.popupBindElement = !!result ? result : this.getSearch().getContainer();
	      }

	      return this.popupBindElement;
	    },

	    /**
	     * Gets filter popup window instance
	     * @return {BX.PopupWindow}
	     */
	    getPopup: function getPopup() {
	      if (!(this.popup instanceof BX.PopupWindow)) {
	        this.popup = new BX.PopupWindow(this.getParam('FILTER_ID') + this.settings.searchContainerPostfix, this.getPopupBindElement(), {
	          autoHide: false,
	          offsetTop: parseInt(this.settings.get('POPUP_OFFSET_TOP')),
	          offsetLeft: parseInt(this.settings.get('POPUP_OFFSET_LEFT')),
	          lightShadow: true,
	          closeIcon: false,
	          closeByEsc: false,
	          noAllPaddings: true,
	          zIndex: 12
	        });
	        this.popup.setContent(this.getTemplate());
	        BX.bind(this.getFieldListContainer(), 'keydown', BX.delegate(this._onFieldsContainerKeydown, this));
	        BX.bind(this.getFilter(), 'click', BX.delegate(this._onFilterClick, this));
	        BX.bind(this.getAddPresetButton(), 'click', BX.delegate(this._onAddPresetClick, this));
	        BX.bind(this.getPreset().getAddPresetFieldInput(), 'keydown', BX.delegate(this._onAddPresetKeydown, this));
	        BX.bind(this.getPreset().getContainer(), 'keydown', BX.delegate(this._onPresetInputKeydown, this));
	        BX.bind(this.getSaveButton(), 'click', BX.delegate(this._onSaveButtonClick, this));
	        BX.bind(this.getCancelButton(), 'click', BX.delegate(this._onCancelButtonClick, this));
	        BX.bind(this.getFindButton(), 'click', BX.delegate(this._onFindButtonClick, this));
	        BX.bind(this.getResetButton(), 'click', BX.delegate(this._onResetButtonClick, this));
	        BX.bind(this.getAddField(), 'click', BX.delegate(this._onAddFieldClick, this));
	        BX.bind(this.getEditButton(), 'click', BX.delegate(this._onEditButtonClick, this));
	        BX.bind(this.getRestoreButton(), 'click', BX.delegate(this._onRestoreButtonClick, this));
	        BX.bind(this.getRestoreFieldsButton(), 'click', BX.delegate(this._onRestoreFieldsButtonClick, this));
	        this.getFilter().addEventListener('mousedown', BX.delegate(this._onFilterMousedown, this), true);
	        this.getPreset().showCurrentPresetFields();
	        this.getPreset().bindOnPresetClick();
	      }

	      return this.popup;
	    },
	    _onRestoreFieldsButtonClick: function _onRestoreFieldsButtonClick() {
	      this.restoreDefaultFields();
	    },

	    /**
	     * Restores default fields list
	     */
	    restoreDefaultFields: function restoreDefaultFields() {
	      var defaultPreset = this.getPreset().getPreset('default_filter', true);
	      var presets = this.getParam('PRESETS');
	      var currentPresetId = this.getPreset().getCurrentPresetId();
	      var params = {
	        'FILTER_ID': this.getParam('FILTER_ID'),
	        'GRID_ID': this.getParam('GRID_ID'),
	        'action': 'SET_FILTER'
	      };
	      var fields = defaultPreset.FIELDS.map(function (curr) {
	        return curr.NAME;
	      });
	      var rows = fields.join(',');
	      presets.forEach(function (current, index) {
	        if (current.ID === 'default_filter') {
	          presets[index] = BX.clone(defaultPreset);
	        }
	      }, this);

	      if (BX.type.isArray(this.editablePresets)) {
	        this.editablePresets.forEach(function (current, index) {
	          if (current.ID === 'default_filter') {
	            this.editablePresets[index] = BX.clone(defaultPreset);
	          }
	        }, this);
	      }

	      this.getPreset().applyPreset(currentPresetId);
	      this.updatePreset(currentPresetId);
	      this.saveOptions({
	        preset_id: "default_filter",
	        rows: rows,
	        save: "Y",
	        apply_filter: "N"
	      }, params);
	    },

	    /**
	     * Gets restore default fields button
	     * @return {?HTMLElement}
	     */
	    getRestoreFieldsButton: function getRestoreFieldsButton() {
	      if (!this.restoreFieldsButton) {
	        this.restoreFieldsButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreFieldsButton);
	      }

	      return this.restoreFieldsButton;
	    },

	    /**
	     * Restores filter
	     */
	    restoreFilter: function restoreFilter() {
	      var defaultPresets = this.getParam('DEFAULT_PRESETS');
	      var allPresets = this.getParam('PRESETS');
	      var isReplace = false;
	      var replaceIndex, applyPresetId, presetNode;

	      if (BX.type.isArray(defaultPresets)) {
	        defaultPresets.sort(function (a, b) {
	          return a.SORT < b.SORT;
	        });
	        defaultPresets.forEach(function (defPreset) {
	          isReplace = allPresets.some(function (current, index) {
	            if (current.ID === defPreset.ID) {
	              replaceIndex = index;
	              return true;
	            }
	          });

	          if (isReplace) {
	            allPresets[replaceIndex] = BX.clone(defPreset);
	          } else {
	            allPresets.push(BX.clone(defPreset));
	          }

	          if (defPreset.ID !== 'default_filter') {
	            this.addSidebarItem(defPreset.ID, defPreset.TITLE, defPreset.IS_PINNED);

	            if (defPreset.IS_PINNED) {
	              applyPresetId = defPreset.ID;
	            }
	          }
	        }, this);
	      }

	      this.saveRestoreFilter();
	      this.disableAddPreset();
	      this.disableEdit();

	      if (!applyPresetId) {
	        applyPresetId = "default_filter";
	      }

	      presetNode = this.getPreset().getPresetNodeById(applyPresetId);

	      if (presetNode) {
	        BX.fireEvent(presetNode, 'click');
	      }
	    },
	    saveRestoreFilter: function saveRestoreFilter() {
	      var params = {
	        'FILTER_ID': this.getParam('FILTER_ID'),
	        'GRID_ID': this.getParam('GRID_ID'),
	        'action': 'RESTORE_FILTER'
	      };
	      var presets = this.getParam('PRESETS');
	      var data = {};
	      var rows;

	      if (BX.type.isArray(presets)) {
	        presets.forEach(function (current) {
	          rows = current.FIELDS.map(function (field) {
	            return field.NAME;
	          });
	          rows = rows.join(',');
	          data[current.ID] = {
	            name: current.TITLE || null,
	            sort: current.SORT,
	            preset_id: current.ID,
	            fields: this.prepareFields(current.FIELDS),
	            rows: rows,
	            for_all: current.FOR_ALL
	          };
	        }, this);
	        this.saveOptions(data, params);
	      }
	    },

	    /**
	     * Prepares fields
	     * @param {object[]} fields
	     * @return {object}
	     */
	    prepareFields: function prepareFields(fields) {
	      var result = {};
	      var valuesKeys;

	      if (BX.type.isArray(fields)) {
	        fields.forEach(function (current) {
	          if (current.TYPE === this.types.SELECT) {
	            result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
	          }

	          if (current.TYPE === this.types.MULTI_SELECT) {
	            current.VALUE.forEach(function (val, i) {
	              result[current.NAME] = result[current.NAME] || {};
	              result[current.NAME][i] = val.VALUE;
	            });
	            result[current.NAME] = result[current.NAME] || '';
	          }

	          if (current.TYPE === this.types.DATE || current.TYPE === this.types.NUMBER) {
	            valuesKeys = Object.keys(current.VALUES);
	            valuesKeys.forEach(function (key) {
	              result[current.NAME + key] = current.VALUES[key];
	            });

	            if (current.TYPE === this.types.DATE) {
	              result[current.NAME + '_datesel'] = 'VALUE' in current.SUB_TYPE ? current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
	            }

	            if (current.TYPE === this.types.NUMBER) {
	              result[current.NAME + '_numsel'] = 'VALUE' in current.SUB_TYPE ? current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
	            }
	          }

	          if (current.TYPE === this.types.DEST_SELECTOR || current.TYPE === this.types.ENTITY_SELECTOR || current.TYPE === this.types.CUSTOM_ENTITY) {
	            result[current.NAME + '_label'] = current.VALUES._label;
	            result[current.NAME + '_value'] = current.VALUES._value;
	          }
	        }, this);
	      }

	      return result;
	    },

	    /**
	     * Gets restore button
	     * @return {?HTMLElement}
	     */
	    getRestoreButton: function getRestoreButton() {
	      if (!BX.type.isDomNode(this.restoreButton)) {
	        this.restoreButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreButton);
	      }

	      return this.restoreButton;
	    },
	    _onPresetInputKeydown: function _onPresetInputKeydown(event) {
	      if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT') {
	        BX.fireEvent(this.getSaveButton(), 'click');
	      }
	    },
	    _onFieldsContainerKeydown: function _onFieldsContainerKeydown(event) {
	      if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT') {
	        BX.fireEvent(this.getFindButton(), 'click');
	      }
	    },
	    _onFindButtonClick: function _onFindButtonClick() {
	      var presets = this.getPreset();
	      var currentPresetId = presets.getCurrentPresetId();
	      var promise;

	      if (currentPresetId !== 'tmp_filter' && currentPresetId !== 'default_filter' && !presets.isPresetValuesModified(currentPresetId)) {
	        var preset = presets.getPreset(currentPresetId);
	        var additional = presets.getAdditionalValues(currentPresetId);
	        var rows = presets.getFields().map(function (current) {
	          return BX.data(current, 'name');
	        });
	        preset.ADDITIONAL = this.preparePresetFields(additional, rows);
	        preset.ADDITIONAL = preset.ADDITIONAL.filter(function (field) {
	          return !this.getPreset().isEmptyField(field);
	        }, this);
	        promise = this.applyFilter(false, currentPresetId);
	        this.closePopup();
	      } else {
	        presets.deactivateAllPresets();
	        promise = this.applyFilter();
	        this.closePopup();
	      }

	      return promise;
	    },
	    _onResetButtonClick: function _onResetButtonClick() {
	      if (this.getParam('VALUE_REQUIRED')) {
	        var preset = this.getPreset().getCurrentPresetData();

	        if (preset.ADDITIONAL.length) {
	          this.closePopup();
	        }

	        BX.fireEvent(this.getSearch().getClearButton(), 'click');
	      } else {
	        if (this.getParam('RESET_TO_DEFAULT_MODE')) {
	          this.getSearch().clearInput();
	          this.getPreset().applyPinnedPreset();
	        } else {
	          this.resetFilter();
	        }

	        this.closePopup();
	      }
	    },

	    /**
	     * @param withoutSearch
	     * @return {BX.Promise}
	     */
	    resetFilter: function resetFilter(withoutSearch) {
	      var Search = this.getSearch();
	      var Presets = this.getPreset();

	      if (!withoutSearch) {
	        Search.clearInput();
	      }

	      Search.removePreset();
	      Presets.deactivateAllPresets();
	      Presets.resetPreset(true);
	      Search.hideClearButton();
	      Search.adjustPlaceholder();
	      return this.applyFilter(true, true);
	    },
	    _onEditButtonClick: function _onEditButtonClick() {
	      if (!this.isEditEnabled()) {
	        this.enableEdit();
	      } else {
	        this.disableEdit();
	      }
	    },

	    /**
	     * Enables fields drag and drop
	     */
	    enableFieldsDragAndDrop: function enableFieldsDragAndDrop() {
	      var fields = this.getPreset().getFields();
	      this.fieldsList = [];

	      if (BX.type.isArray(fields)) {
	        this.fieldsList = fields.map(this.registerDragItem, this);
	      }
	    },

	    /**
	     * Register drag item
	     * @param {HTMLElement} item
	     * @return {HTMLElement}
	     */
	    registerDragItem: function registerDragItem(item) {
	      var dragButton = this.getDragButton(item);

	      if (dragButton) {
	        dragButton.onbxdragstart = BX.delegate(this._onFieldDragStart, this);
	        dragButton.onbxdragstop = BX.delegate(this._onFieldDragStop, this);
	        dragButton.onbxdrag = BX.delegate(this._onFieldDrag, this);
	        jsDD.registerObject(dragButton);
	        jsDD.registerDest(dragButton);
	      }

	      return item;
	    },

	    /**
	     * Unregister drag item
	     * @param {HTMLElement} item
	     */
	    unregisterDragItem: function unregisterDragItem(item) {
	      var dragButton = this.getDragButton(item);

	      if (dragButton) {
	        jsDD.unregisterObject(dragButton);
	        jsDD.unregisterDest(dragButton);
	      }
	    },
	    _onFieldDragStart: function _onFieldDragStart() {
	      this.dragItem = this.getFields().getField(jsDD.current_node);
	      this.dragIndex = BX.Filter.Utils.getIndex(this.fieldsList, this.dragItem);
	      this.dragRect = this.dragItem.getBoundingClientRect();
	      this.offset = this.dragRect.height;
	      this.dragStartOffset = jsDD.start_y - (this.dragRect.top + BX.scrollTop(window));
	      BX.Filter.Utils.styleForEach(this.fieldsList, {
	        'transition': '100ms'
	      });
	      BX.addClass(this.dragItem, this.settings.classPresetOndrag);
	      BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
	    },
	    _onFieldDragStop: function _onFieldDragStop() {
	      BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
	      BX.removeClass(this.dragItem, this.settings.classPresetOndrag);
	      BX.Filter.Utils.styleForEach(this.fieldsList, {
	        'transition': '',
	        'transform': ''
	      });
	      BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);
	      this.fieldsList = this.getPreset().getFields();
	      this.saveFieldsSort();
	    },
	    _onFieldDrag: function _onFieldDrag() {
	      var self = this;
	      var currentRect, currentMiddle;
	      this.dragOffset = this.realY - this.dragRect.top - this.dragStartOffset;
	      this.sortOffset = self.realY + BX.scrollTop(window);
	      BX.Filter.Utils.styleForEach([this.dragItem], {
	        'transition': '0ms',
	        'transform': 'translate3d(0px, ' + this.dragOffset + 'px, 0px)'
	      });
	      this.fieldsList.forEach(function (current, index) {
	        if (current) {
	          currentRect = current.getBoundingClientRect();
	          currentMiddle = currentRect.top + BX.scrollTop(window) + currentRect.height / 2;

	          if (index > self.dragIndex && self.sortOffset > currentMiddle && current.style.transform !== 'translate3d(0px, ' + -self.offset + 'px, 0px)' && current.style.transform !== '') {
	            self.targetItem = current;
	            BX.style(current, 'transform', 'translate3d(0px, ' + -self.offset + 'px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }

	          if (index < self.dragIndex && self.sortOffset < currentMiddle && current.style.transform !== 'translate3d(0px, ' + self.offset + 'px, 0px)' && current.style.transform !== '') {
	            self.targetItem = current;
	            BX.style(current, 'transform', 'translate3d(0px, ' + self.offset + 'px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }

	          if ((index < self.dragIndex && self.sortOffset > currentMiddle || index > self.dragIndex && self.sortOffset < currentMiddle) && current.style.transform !== 'translate3d(0px, 0px, 0px)') {
	            if (current.style.transform !== '') {
	              self.targetItem = current;
	            }

	            BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }
	        }
	      });
	    },

	    /**
	     * Disables fields drag and drop
	     */
	    disableFieldsDragAndDrop: function disableFieldsDragAndDrop() {
	      if (BX.type.isArray(this.fieldsList) && this.fieldsList.length) {
	        this.fieldsList.map(this.unregisterDragItem, this);
	      }
	    },

	    /**
	     * Enables presets drag and drop
	     */
	    enablePresetsDragAndDrop: function enablePresetsDragAndDrop() {
	      var Preset, presets, dragButton, presetId;
	      Preset = this.getPreset();
	      presets = Preset.getPresets();
	      this.presetsList = [];

	      if (BX.type.isArray(presets) && presets.length) {
	        presets.forEach(function (current) {
	          presetId = Preset.getPresetId(current);

	          if (!BX.hasClass(current, this.settings.classAddPresetField) && presetId !== 'default_filter' && !BX.hasClass(current, this.settings.classDefaultFilter)) {
	            dragButton = this.getDragButton(current);
	            dragButton.onbxdragstart = BX.delegate(this._onDragStart, this);
	            dragButton.onbxdragstop = BX.delegate(this._onDragStop, this);
	            dragButton.onbxdrag = BX.delegate(this._onDrag, this);
	            jsDD.registerObject(dragButton);
	            jsDD.registerDest(dragButton);
	            this.presetsList.push(current);
	          }
	        }, this);
	      }
	    },

	    /**
	     * Gets drag button
	     * @param {HTMLElement} presetNode
	     * @return {?HTMLElement}
	     */
	    getDragButton: function getDragButton(presetNode) {
	      return BX.Filter.Utils.getByClass(presetNode, this.settings.classPresetDragButton);
	    },

	    /**
	     * Disables presets drag and drop
	     */
	    disablePresetsDragAndDrop: function disablePresetsDragAndDrop() {
	      if (BX.type.isArray(this.presetsList) && this.presetsList.length) {
	        this.presetsList.forEach(function (current) {
	          if (!BX.hasClass(current, this.settings.classAddPresetField)) {
	            jsDD.unregisterObject(current);
	            jsDD.unregisterDest(current);
	          }
	        }, this);
	      }
	    },
	    _onDragStart: function _onDragStart() {
	      this.dragItem = this.getPreset().normalizePreset(jsDD.current_node);
	      this.dragIndex = BX.Filter.Utils.getIndex(this.presetsList, this.dragItem);
	      this.dragRect = this.dragItem.getBoundingClientRect();
	      this.offset = this.dragRect.height;
	      this.dragStartOffset = jsDD.start_y - (this.dragRect.top + BX.scrollTop(window));
	      BX.Filter.Utils.styleForEach(this.list, {
	        'transition': '100ms'
	      });
	      BX.addClass(this.dragItem, this.settings.classPresetOndrag);
	      BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
	    },
	    _onMouseMove: function _onMouseMove(event) {
	      this.realX = event.clientX;
	      this.realY = event.clientY;
	    },

	    /**
	     * Gets drag offset
	     * @return {number}
	     */
	    getDragOffset: function getDragOffset() {
	      return jsDD.x - this.startDragOffset - this.dragRect.left;
	    },
	    _onDragStop: function _onDragStop() {
	      var Preset, presets;
	      BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
	      BX.removeClass(this.dragItem, this.settings.classPresetOndrag);
	      BX.Filter.Utils.styleForEach(this.presetsList, {
	        'transition': '',
	        'transform': ''
	      });
	      BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);
	      Preset = this.getPreset();
	      presets = Preset.getPresets();
	      this.presetsList = [];

	      if (BX.type.isArray(presets) && presets.length) {
	        presets.forEach(function (current) {
	          if (!BX.hasClass(current, this.settings.classAddPresetField) && !BX.hasClass(current, this.settings.classDefaultFilter)) {
	            this.presetsList.push(current);
	          }
	        }, this);
	      }
	    },
	    _onDrag: function _onDrag() {
	      var self = this;
	      var currentRect, currentMiddle;
	      this.dragOffset = this.realY - this.dragRect.top - this.dragStartOffset;
	      this.sortOffset = self.realY + BX.scrollTop(window);
	      BX.Filter.Utils.styleForEach([this.dragItem], {
	        'transition': '0ms',
	        'transform': 'translate3d(0px, ' + this.dragOffset + 'px, 0px)'
	      });
	      this.presetsList.forEach(function (current, index) {
	        if (current) {
	          currentRect = current.getBoundingClientRect();
	          currentMiddle = currentRect.top + BX.scrollTop(window) + currentRect.height / 2;

	          if (index > self.dragIndex && self.sortOffset > currentMiddle && current.style.transform !== 'translate3d(0px, ' + -self.offset + 'px, 0px)' && current.style.transform !== '') {
	            self.targetItem = current;
	            BX.style(current, 'transform', 'translate3d(0px, ' + -self.offset + 'px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }

	          if (index < self.dragIndex && self.sortOffset < currentMiddle && current.style.transform !== 'translate3d(0px, ' + self.offset + 'px, 0px)' && current.style.transform !== '') {
	            self.targetItem = current;
	            BX.style(current, 'transform', 'translate3d(0px, ' + self.offset + 'px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }

	          if ((index < self.dragIndex && self.sortOffset > currentMiddle || index > self.dragIndex && self.sortOffset < currentMiddle) && current.style.transform !== 'translate3d(0px, 0px, 0px)') {
	            if (current.style.transform !== '') {
	              self.targetItem = current;
	            }

	            BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
	            BX.style(current, 'transition', '300ms');
	          }
	        }
	      });
	    },

	    /**
	     * Gets sidebar controls container
	     * @return {?HTMLElement}
	     */
	    getSidebarControlsContainer: function getSidebarControlsContainer() {
	      if (!BX.type.isDomNode(this.sidebarControlsContainer)) {
	        this.sidebarControlsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSidebarControlsContainer);
	      }

	      return this.sidebarControlsContainer;
	    },

	    /**
	     * Enables edit mode
	     */
	    enableEdit: function enableEdit() {
	      var Preset = this.getPreset();
	      var presets = Preset.getPresets();
	      var presetId;

	      if (BX.type.isArray(presets) && presets.length) {
	        presets.forEach(function (current) {
	          presetId = Preset.getPresetId(current);

	          if (!BX.hasClass(current, this.settings.classAddPresetField) && presetId !== 'default_filter') {
	            BX.addClass(current, this.settings.classPresetEdit);
	          }
	        }, this);
	      }

	      this.enablePresetsDragAndDrop();
	      BX.show(this.getButtonsContainer());
	      BX.hide(this.getPresetButtonsContainer());
	      BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
	      this.editablePresets = BX.clone(this.getParam('PRESETS'));
	      this.isEditEnabledState = true;
	    },

	    /**
	     * Disables edit mode
	     */
	    disableEdit: function disableEdit() {
	      var Preset = this.getPreset();
	      var presets = Preset.getPresets();

	      if (BX.type.isArray(presets) && presets.length) {
	        presets.forEach(function (current) {
	          if (!BX.hasClass(current, this.settings.classAddPresetField)) {
	            BX.removeClass(current, this.settings.classPresetEdit);
	            this.getPreset().disableEditPresetName(current);
	          }
	        }, this);
	      }

	      this.disablePresetsDragAndDrop();

	      if (!this.isAddPresetEnabled()) {
	        BX.style(this.getButtonsContainer(), 'display', '');
	      }

	      BX.show(this.getPresetButtonsContainer());
	      BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
	      this.editablePresets = null;
	      this.isEditEnabledState = false;
	      this.applyFilter(null, true);
	    },

	    /**
	     * Get preset buttons container
	     * @return {?HTMLElement}
	     */
	    getPresetButtonsContainer: function getPresetButtonsContainer() {
	      if (!BX.type.isDomNode(this.presetButtonsContainer)) {
	        this.presetButtonsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classPresetButtonsContainer);
	      }

	      return this.presetButtonsContainer;
	    },

	    /**
	     * Checks is edit mode enabled
	     * @return {boolean}
	     */
	    isEditEnabled: function isEditEnabled() {
	      return this.isEditEnabledState;
	    },

	    /**
	     * Gets edit button element
	     * @return {?HTMLElement}
	     */
	    getEditButton: function getEditButton() {
	      return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classEditButton);
	    },

	    /**
	     * Gets component param by param name
	     * @param {string} paramName
	     * @param {*} [defaultValue] - Be returns if param with paramName not set
	     * @returns {*}
	     */
	    getParam: function getParam(paramName, defaultValue) {
	      return paramName in this.params ? this.params[paramName] : defaultValue;
	    },

	    /**
	     * Gets container of filter popup
	     * @returns {HTMLElement|null}
	     */
	    getFilter: function getFilter() {
	      return BX.Filter.Utils.getByClass(this.getPopup().contentContainer, this.settings.classFilterContainer);
	    },

	    /**
	     * @returns {BX.Filter.Search}
	     */
	    getSearch: function getSearch() {
	      if (!(this.search instanceof BX.Filter.Search)) {
	        this.search = new BX.Filter.Search(this);
	      }

	      return this.search;
	    },
	    _onRestoreButtonClick: function _onRestoreButtonClick() {
	      var action = {
	        CONFIRM: true,
	        CONFIRM_MESSAGE: this.getParam('CONFIRM_MESSAGE'),
	        CONFIRM_APPLY_BUTTON: this.getParam('CONFIRM_APPLY'),
	        CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
	      };
	      this.confirmDialog(action, BX.delegate(this.restoreFilter, this));
	    },

	    /**
	     * Shows confirmation popup
	     * @param {object} action - Popup properties
	     * @param {boolean} action.CONFIRM - true If the user must confirm the action
	     * @param {string} action.CONFIRM_MESSAGE - Message of confirm popup
	     * @param {string} action.CONFIRM_APPLY_BUTTON - Text of apply button
	     * @param {string} action.CONFIRM_CANCEL_BUTTON - Text of cancel button
	     * @param {string} [action.CONFIRM_TITLE] - Title of confirm popup
	     * @param {function} then - Callback after a successful confirmation
	     * @param {function} [cancel] - callback after cancel confirmation
	     */
	    confirmDialog: function confirmDialog(action, then, cancel) {
	      if ('CONFIRM' in action && action.CONFIRM) {
	        var dialogId = this.getParam('FILTER_ID') + '-confirm-dialog';
	        var popupMessage = '<div class="main-ui-filter-confirm-content">' + action.CONFIRM_MESSAGE + '</div>';
	        var popupTitle = 'CONFIRM_TITLE' in action ? action.CONFIRM_TITLE : '';
	        var applyButton = new BX.PopupWindowButton({
	          text: action.CONFIRM_APPLY_BUTTON,
	          events: {
	            click: function click() {
	              BX.type.isFunction(then) ? then() : null;
	              this.popupWindow.close();
	              this.popupWindow.destroy();
	            }
	          }
	        });
	        var cancelButton = new BX.PopupWindowButtonLink({
	          text: action.CONFIRM_CANCEL_BUTTON,
	          events: {
	            click: function click() {
	              BX.type.isFunction(cancel) ? cancel() : null;
	              this.popupWindow.close();
	              this.popupWindow.destroy();
	            }
	          }
	        });
	        var dialog = new BX.PopupWindow(dialogId, null, {
	          content: popupMessage,
	          titleBar: popupTitle,
	          autoHide: false,
	          zIndex: 9999,
	          overlay: 0.4,
	          offsetTop: -100,
	          closeIcon: true,
	          closeByEsc: true,
	          buttons: [applyButton, cancelButton]
	        });
	        BX.addCustomEvent(dialog, 'onPopupClose', BX.delegate(function () {
	          !!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
	        }, this));

	        if (!dialog.isShown()) {
	          dialog.show();
	          var popupContainer = dialog.popupContainer;
	          BX.removeClass(popupContainer, this.settings.classAnimationShow);
	          BX.addClass(popupContainer, this.settings.classAnimationShow);
	        }
	      } else {
	        BX.type.isFunction(then) ? then() : null;
	      }
	    },
	    getInitialValue: function getInitialValue(name) {
	      if (BX.type.isString(name)) {
	        var values = this.params.INITIAL_FILTER;

	        if (BX.type.isPlainObject(values)) {
	          var filteredEntries = Object.entries(values).reduce(function (acc, item) {
	            if (item[0].startsWith(name)) {
	              acc.push(item);
	            }

	            return acc;
	          }, []);

	          if (filteredEntries.length === 1) {
	            return filteredEntries[0][1];
	          }

	          if (filteredEntries.length > 1) {
	            return filteredEntries.reduce(function (acc, item) {
	              acc[item[0].replace(name, '')] = item[1];
	              return acc;
	            }, {});
	          }
	        }
	      }

	      return '';
	    },
	    getField: function getField(name) {
	      var node = this.getFieldListContainer().querySelector('[data-name="' + name + '"]');
	      return BX.Filter.Field.instances.get(node);
	    }
	  };
	})();

	(function () {
	  BX.Main.filterManager = {
	    data: {},
	    push: function push(id, instance) {
	      if (BX.type.isNotEmptyString(id) && instance) {
	        this.data[id] = instance;
	      }
	    },
	    getById: function getById(id) {
	      var result = null;

	      if (id in this.data) {
	        result = this.data[id];
	      }

	      return result;
	    }
	  };
	})();

	var _templateObject$1;

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var onValueChange = Symbol('onValueChange');
	var Field = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(Field, _Event$EventEmitter);

	  function Field(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Field);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Field).call(this, options));

	    _this.setEventNamespace('BX.Filter.Field');

	    _this.id = options.options.NAME;
	    _this.parent = options.parent;
	    _this.node = options.node;
	    _this.options = _objectSpread$2({}, options.options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this[onValueChange] = _this[onValueChange].bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.node, 'input', _this[onValueChange]);
	    main_core.Event.bind(_this.node, 'change', _this[onValueChange]);
	    var clearButtons = babelHelpers.toConsumableArray(_this.node.querySelectorAll('.main-ui-control-value-delete'));
	    clearButtons.forEach(function (button) {
	      button.addEventListener('click', function () {
	        setTimeout(function () {
	          _this[onValueChange]();
	        });
	      });
	    });
	    var MO = new MutationObserver(function () {
	      _this[onValueChange]();
	    });
	    var selects = babelHelpers.toConsumableArray(_this.node.querySelectorAll('.main-ui-select'));
	    selects.forEach(function (select) {
	      MO.observe(select, {
	        attributes: true,
	        attributeFilter: ['data-value']
	      });
	    });
	    Field.instances.set(_this.node, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Field, [{
	    key: "subscribe",
	    value: function subscribe(eventName, listener) {
	      main_core.Event.EventEmitter.subscribe(this, eventName.replace('BX.Filter.Field:', ''), listener);
	    }
	  }, {
	    key: onValueChange,
	    value: function value() {
	      this.emit('change', {
	        field: this,
	        value: this.getValue()
	      });
	    }
	    /**
	     * @private
	     * @return {HTMLDivElement}
	     */

	  }, {
	    key: "getAdditionalFieldContainer",
	    value: function getAdditionalFieldContainer() {
	      return this.cache.remember('additionalFieldsContainer', function () {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"main-ui-filter-additional-fields-container\"></div>\n\t\t\t"])));
	      });
	    }
	    /**
	     * @private
	     * @return {boolean}
	     */

	  }, {
	    key: "hasAdditional",
	    value: function hasAdditional() {
	      return main_core.Dom.hasClass(this.node, 'main-ui-filter-field-with-additional-fields');
	    }
	  }, {
	    key: "addAdditionalField",
	    value: function addAdditionalField(field) {
	      if (!this.hasAdditional()) {
	        main_core.Dom.addClass(this.node, 'main-ui-filter-field-with-additional-fields');
	        main_core.Dom.append(this.getAdditionalFieldContainer(), this.node);
	      }

	      var preset = this.parent.getPreset();
	      var options = this.prepareFieldOptions(field);
	      var renderedField = preset.createControl(options);
	      this.appendRenderedField(renderedField);
	      return Field.instances.get(renderedField);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "prepareListItems",
	    value: function prepareListItems() {
	      var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (main_core.Type.isPlainObject(items)) {
	        return Object.entries(items).map(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              VALUE = _ref2[0],
	              NAME = _ref2[1];

	          return {
	            NAME: NAME,
	            VALUE: VALUE
	          };
	        });
	      }

	      return {};
	    }
	    /**
	     * @private
	     * @return {object}
	     */

	  }, {
	    key: "prepareFieldOptions",
	    value: function prepareFieldOptions(options) {
	      var _this2 = this;

	      if (main_core.Type.isPlainObject(options)) {
	        var stubs = this.parent.params.FIELDS_STUBS;
	        var _options$type = options.type,
	            type = _options$type === void 0 ? 'string' : _options$type;
	        var stub = stubs.find(function (item) {
	          return item.NAME === type;
	        });

	        if (main_core.Type.isPlainObject(stub)) {
	          var baseField = _objectSpread$2(_objectSpread$2({}, stub), {}, {
	            NAME: options.id,
	            LABEL: options.name,
	            TYPE: type === 'checkbox' ? 'SELECT' : stub.TYPE,
	            VALUE_REQUIRED: options.valueRequired === true
	          });

	          if (type === 'list') {
	            return _objectSpread$2(_objectSpread$2({}, baseField), {}, {
	              ITEMS: [].concat(babelHelpers.toConsumableArray(baseField.ITEMS), [this.prepareListItems(options.items)]),
	              params: {
	                isMulti: function () {
	                  if (main_core.Type.isPlainObject(options.params)) {
	                    return options.params === true;
	                  }

	                  return false;
	                }()
	              }
	            });
	          }

	          if (type === 'date') {
	            var subType = function () {
	              if (main_core.Type.isPlainObject(options.value) && Reflect.has(options.value, '_datesel')) {
	                // eslint-disable-next-line no-underscore-dangle
	                return options.value._datesel;
	              }

	              return _this2.parent.dateTypes.NONE;
	            }();

	            return _objectSpread$2(_objectSpread$2({}, baseField), {}, {
	              SUB_TYPES: function () {
	                if (main_core.Type.isArray(options.exclude)) {
	                  return baseField.SUB_TYPES.filter(function (item) {
	                    return !options.exclude.includes(item.VALUE);
	                  });
	                }

	                return baseField.SUB_TYPES;
	              }(),
	              SUB_TYPE: function () {
	                return baseField.SUB_TYPES.find(function (item) {
	                  return item.VALUE === subType;
	                });
	              }(),
	              VALUES: function () {
	                if (main_core.Type.isPlainObject(options.value)) {
	                  return _objectSpread$2({}, options.value);
	                }

	                return baseField.VALUES;
	              }()
	            });
	          }

	          if (type === 'string' || type === 'custom_date' || type === 'number' || type === 'checkbox' || type === 'custom_entity') {
	            return baseField;
	          }
	        }
	      }

	      return options;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "appendRenderedField",
	    value: function appendRenderedField(field) {
	      if (main_core.Type.isDomNode(field)) {
	        var additionalFieldsContainer = this.getAdditionalFieldContainer();
	        main_core.Dom.append(field, additionalFieldsContainer);
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var allValues = this.parent.getFilterFieldsValues();
	      var _this$options = this.options,
	          TYPE = _this$options.TYPE,
	          NAME = _this$options.NAME;

	      if (TYPE === 'DATE' || TYPE === 'NUMBER') {
	        return Object.entries(allValues).reduce(function (acc, _ref3) {
	          var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	              key = _ref4[0],
	              value = _ref4[1];

	          if (key.startsWith(NAME)) {
	            acc[key.replace(NAME, '')] = value;
	          }

	          return acc;
	        }, {});
	      }

	      if (NAME in allValues) {
	        return allValues[NAME];
	      }

	      return '';
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var _this3 = this;

	      var type = this.options.TYPE;

	      if (type === 'DATE' || type === 'NUMBER') {
	        if (main_core.Type.isPlainObject(value)) {
	          var container = this.parent.getFieldListContainer();
	          Object.entries(value).forEach(function (_ref5) {
	            var _ref6 = babelHelpers.slicedToArray(_ref5, 2),
	                key = _ref6[0],
	                fieldValue = _ref6[1];

	            var fieldNode = container.querySelector("[data-name=\"".concat(_this3.id, "\"] [data-name=\"").concat(_this3.id).concat(key, "\"], [data-name=\"").concat(_this3.id, "\"] [name=\"").concat(_this3.id).concat(key, "\"]"));

	            if (fieldNode) {
	              if (main_core.Dom.hasClass(fieldNode, 'main-ui-select')) {
	                var items = main_core.Dom.attr(fieldNode, 'data-items');

	                if (main_core.Type.isArray(items)) {
	                  var item = items.find(function (currentItem) {
	                    return currentItem.VALUE === fieldValue;
	                  });

	                  if (main_core.Type.isPlainObject(item)) {
	                    main_core.Dom.attr(fieldNode, 'data-value', item);
	                    var nameNode = fieldNode.querySelector('.main-ui-select-name');

	                    if (nameNode) {
	                      nameNode.innerText = item.NAME;
	                    }

	                    var result = BX.Main.ui.Factory.get(fieldNode);

	                    if (!result) {
	                      result = {
	                        node: fieldNode,
	                        instance: new BX.Main.ui.select(fieldNode)
	                      };
	                      BX.Main.ui.Factory.data.push(result);
	                    }

	                    if (main_core.Type.isPlainObject(result)) {
	                      BX.onCustomEvent(window, 'UI::Select::Change', [result.instance, item]);
	                    }
	                  }
	                }
	              } else if (fieldNode.tagName === 'INPUT') {
	                fieldNode.value = fieldValue;
	              }
	            }
	          });
	        }
	      }
	    }
	  }]);
	  return Field;
	}(main_core.Event.EventEmitter);
	babelHelpers.defineProperty(Field, "instances", new WeakMap());

	var Api = /*#__PURE__*/function () {
	  function Api(parent) {
	    babelHelpers.classCallCheck(this, Api);
	    this.parent = parent;
	  }

	  babelHelpers.createClass(Api, [{
	    key: "setFields",
	    value: function setFields(fields) {
	      if (main_core.Type.isPlainObject(fields)) {
	        this.parent.getPopup();
	        var preset = this.parent.getPreset();
	        preset.deactivateAllPresets();
	        var data = {
	          preset_id: 'tmp_filter',
	          fields: fields
	        };
	        this.parent.updateParams(data);
	        preset.applyPreset('tmp_filter');
	      }
	    }
	  }, {
	    key: "setFilter",
	    value: function setFilter(filter) {
	      var analyticsLabel = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      this.setAnalyticsLabel(analyticsLabel);

	      if (main_core.Type.isObject(filter)) {
	        this.parent.updateParams(filter);
	        this.parent.getPreset().deactivateAllPresets();
	        this.parent.getPreset().activatePreset(filter.preset_id);
	        this.parent.getPreset().applyPreset(filter.preset_id);

	        if (!filter.checkFields || !this.parent.getPreset().isPresetValuesModified(filter.preset_id)) {
	          this.parent.applyFilter(false, filter.preset_id);
	        } else {
	          var newFields = {};

	          if (main_core.Type.isPlainObject(filter.fields)) {
	            newFields = Object.assign({}, filter.fields);
	          }

	          if (main_core.Type.isPlainObject(filter.additional)) {
	            newFields = Object.assign({}, filter.additional);
	          }

	          this.parent.getPreset().deactivateAllPresets();
	          this.setFields(newFields);
	          this.apply();
	        }
	      }
	    }
	    /**
	     * Extends current applied filter
	     * @param {Object.<String, *>} fields
	     * @param {boolean} [force = false]
	     */

	  }, {
	    key: "extendFilter",
	    value: function extendFilter(fields) {
	      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var analyticsLabel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      this.setAnalyticsLabel(analyticsLabel);

	      if (main_core.Type.isObject(fields)) {
	        Object.keys(fields).forEach(function (key) {
	          if (main_core.Type.isNumber(fields[key])) {
	            fields[key] = String(fields[key]);
	          }
	        });
	        var currentPresetId = this.parent.getPreset().getCurrentPresetId();

	        if (force || currentPresetId === 'tmp_filter' || currentPresetId === 'default_filter') {
	          var newFields = Object.assign({}, this.parent.getFilterFieldsValues(), fields);
	          this.setFields(newFields);
	          this.apply();
	          return;
	        }

	        var previewsAdditionalValues = this.parent.getPreset().getAdditionalValues(currentPresetId);

	        if (main_core.Type.isPlainObject(previewsAdditionalValues) && Object.keys(previewsAdditionalValues).length) {
	          fields = Object.assign({}, previewsAdditionalValues, fields);
	        }

	        this.setFilter({
	          preset_id: currentPresetId,
	          additional: fields,
	          checkFields: true
	        });
	      }
	    }
	  }, {
	    key: "apply",
	    value: function apply() {
	      var analyticsLabel = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      this.setAnalyticsLabel(analyticsLabel);

	      if (!this.parent.isEditEnabled()) {
	        if (!this.parent.isEditEnabled()) {
	          this.parent.applyFilter();
	        }

	        this.parent.closePopup();

	        if (this.parent.isAddPresetEnabled()) {
	          this.parent.disableAddPreset();
	        }
	      }
	    }
	  }, {
	    key: "getEmitter",
	    value: function getEmitter() {
	      return this.parent.emitter;
	    }
	  }, {
	    key: "setAnalyticsLabel",
	    value: function setAnalyticsLabel() {
	      var analyticsLabel = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (main_core.Type.isObject(analyticsLabel)) {
	        this.parent.analyticsLabel = analyticsLabel;
	      }
	    }
	  }]);
	  return Api;
	}();

	function createDateInputDecl(options) {
	  return {
	    block: 'main-ui-control-field',
	    type: options.type,
	    dragButton: false,
	    content: {
	      block: 'main-ui-date',
	      mix: ['filter-type-single'],
	      calendarButton: true,
	      valueDelete: true,
	      placeholder: options.placeholder,
	      name: options.name,
	      tabindex: options.tabindex,
	      value: options.value,
	      enableTime: options.enableTime
	    }
	  };
	}

	function createNumberInputDecl(options) {
	  return {
	    block: 'main-ui-control-field',
	    type: options.type,
	    dragButton: false,
	    content: {
	      block: 'main-ui-number',
	      mix: ['filter-type-single'],
	      valueDelete: true,
	      placeholder: options.placeholder,
	      name: options.name,
	      tabindex: options.tabindex,
	      value: options.value
	    }
	  };
	}

	function createLineDecl() {
	  return {
	    block: 'main-ui-filter-field-line',
	    content: {
	      block: 'main-ui-filter-field-line-item',
	      tag: 'span'
	    }
	  };
	}

	function createSelectDecl(options) {
	  return {
	    block: 'main-ui-control-field',
	    dragButton: false,
	    content: {
	      block: 'main-ui-select',
	      tabindex: options.tabindex,
	      value: options.value,
	      items: options.items,
	      name: options.name,
	      valueDelete: false
	    }
	  };
	}

	var _templateObject$2, _templateObject2$1;

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Filter
	 */

	var AdditionalFilter = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(AdditionalFilter, _EventEmitter);
	  babelHelpers.createClass(AdditionalFilter, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return AdditionalFilter.cache.remember('instance', function () {
	        return new AdditionalFilter();
	      });
	    }
	  }, {
	    key: "fetchAdditionalFilter",
	    value: function fetchAdditionalFilter(name, fields) {
	      if (main_core.Type.isStringFilled(name) && main_core.Type.isPlainObject(fields)) {
	        if ("".concat(name, "_").concat(AdditionalFilter.Type.IS_EMPTY) in fields) {
	          return AdditionalFilter.Type.IS_EMPTY;
	        }

	        if ("".concat(name, "_").concat(AdditionalFilter.Type.HAS_ANY_VALUE) in fields) {
	          return AdditionalFilter.Type.HAS_ANY_VALUE;
	        }
	      }

	      return null;
	    }
	  }]);

	  function AdditionalFilter() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, AdditionalFilter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AdditionalFilter).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());

	    _this.setEventNamespace('BX.Main.Filter.AdditionalFilter');

	    _this.options = _objectSpread$3({}, options);
	    main_core.Event.bind(document, 'click', _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(AdditionalFilter, [{
	    key: "getAdditionalFilterMenu",
	    value: function getAdditionalFilterMenu() {
	      var _this2 = this;

	      return this.cache.remember('menu', function () {
	        return new main_popup.Menu({
	          id: 'additional_filter_menu',
	          autoHide: false,
	          items: [{
	            id: 'isEmpty',
	            text: main_core.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_MENU_IS_EMPTY'),
	            onclick: _this2.onAdditionalFilterMenuItemClick.bind(_this2, AdditionalFilter.Type.IS_EMPTY)
	          }, {
	            id: 'hasAnyValue',
	            text: main_core.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_MENU_HAS_ANY_VALUE'),
	            onclick: _this2.onAdditionalFilterMenuItemClick.bind(_this2, AdditionalFilter.Type.HAS_ANY_VALUE)
	          }, {
	            id: 'delimiter',
	            delimiter: true
	          }, {
	            id: 'helper',
	            html: main_core.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HOW') + '<span class="ui-hint"><span class="ui-hint-icon"></span></span>',
	            onclick: function onclick() {
	              if (top.BX.Helper) {
	                top.BX.Helper.show("redirect=detail&code=14006190");
	                event.preventDefault();
	              }
	            }
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onAdditionalFilterMenuItemClick",
	    value: function onAdditionalFilterMenuItemClick(typeId) {
	      var node = this.getCurrentFieldNode();
	      this.initAdditionalFilter(node, typeId);
	    }
	  }, {
	    key: "onDocumentClick",
	    value: function onDocumentClick() {
	      this.getAdditionalFilterMenu().close();
	    }
	  }, {
	    key: "setCurrentFieldId",
	    value: function setCurrentFieldId(fieldId) {
	      this.cache.set('currentFieldId', fieldId);
	    }
	  }, {
	    key: "getCurrentFieldId",
	    value: function getCurrentFieldId() {
	      return this.cache.get('currentFieldId', '');
	    }
	  }, {
	    key: "setCurrentFieldNode",
	    value: function setCurrentFieldNode(node) {
	      this.cache.set('currentFieldNode', node);
	    }
	  }, {
	    key: "getCurrentFieldNode",
	    value: function getCurrentFieldNode() {
	      return this.cache.get('currentFieldNode');
	    }
	  }, {
	    key: "onAdditionalFilterButtonClick",
	    value: function onAdditionalFilterButtonClick(fieldId, event) {
	      event.stopPropagation();
	      var currentTarget = event.currentTarget;
	      this.setCurrentFieldId(fieldId);
	      this.setCurrentFieldNode(currentTarget.parentElement);
	      var menu = this.getAdditionalFilterMenu();
	      var allowedItems = String(main_core.Dom.attr(currentTarget, 'data-allowed-types')).split(',');
	      menu.getMenuItems().forEach(function (menuItem) {
	        var menuItemId = menuItem.getId();

	        if (allowedItems.includes(menuItemId) || menuItemId === 'helper' || menuItemId === 'delimiter') {
	          main_core.Dom.removeClass(menuItem.layout.item, 'main-ui-disable');
	        } else {
	          main_core.Dom.addClass(menuItem.layout.item, 'main-ui-disable');
	        }
	      });

	      if (menu.getPopupWindow().isShown()) {
	        if (menu.getPopupWindow().bindElement !== currentTarget) {
	          menu.getPopupWindow().setBindElement(currentTarget);
	          menu.getPopupWindow().adjustPosition();
	        } else {
	          menu.close();
	        }
	      } else {
	        menu.getPopupWindow().setBindElement(currentTarget);
	        menu.show();
	      }
	    }
	  }, {
	    key: "getAdditionalFilterButton",
	    value: function getAdditionalFilterButton(_ref) {
	      var _this3 = this;

	      var fieldId = _ref.fieldId,
	          enabled = _ref.enabled;
	      return this.cache.remember("field_".concat(fieldId), function () {
	        var disabled = !main_core.Type.isArrayFilled(enabled) && enabled !== true;

	        var allowedTypes = function () {
	          if (main_core.Type.isArrayFilled(enabled)) {
	            return enabled.join(',');
	          }

	          if (!disabled) {
	            return [AdditionalFilter.Type.IS_EMPTY, AdditionalFilter.Type.HAS_ANY_VALUE].join(',');
	          }

	          return '';
	        }();

	        return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span \n\t\t\t\t\tclass=\"ui-icon ui-icon-service-light-other main-ui-filter-additional-filters-button", "\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\tdata-allowed-types=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t<i></i>\n\t\t\t\t</span>\n\t\t\t"])), disabled ? ' main-ui-disable' : '', _this3.onAdditionalFilterButtonClick.bind(_this3, fieldId), allowedTypes);
	      });
	    }
	  }, {
	    key: "initAdditionalFilter",
	    value: function initAdditionalFilter(fieldNode, typeId) {
	      var currentFieldId = this.getCurrentFieldId();

	      if (currentFieldId === '') {
	        currentFieldId = fieldNode.attributes[1].value;
	      }

	      var placeholder = this.getAdditionalFilterPlaceholderField(currentFieldId, typeId);
	      main_core.Dom.addClass(fieldNode, 'main-ui-filter-field-with-additional-filter');
	      var currentPlaceholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');

	      if (currentPlaceholder) {
	        main_core.Dom.replace(currentPlaceholder, placeholder);
	      } else {
	        main_core.Dom.append(placeholder, fieldNode);
	      }
	    }
	  }, {
	    key: "restoreField",
	    value: function restoreField(fieldNode) {
	      if (main_core.Type.isDomNode(fieldNode)) {
	        var placeholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');

	        if (placeholder) {
	          main_core.Dom.remove(placeholder);
	        }

	        main_core.Dom.removeClass(fieldNode, 'main-ui-filter-field-with-additional-filter');
	      }
	    }
	  }, {
	    key: "getAdditionalFilterPlaceholderField",
	    value: function getAdditionalFilterPlaceholderField(fieldId, typeId) {
	      var _this4 = this;

	      return this.cache.remember("placeholder_".concat(fieldId, "_").concat(typeId), function () {
	        var message = function () {
	          if (typeId === AdditionalFilter.Type.HAS_ANY_VALUE) {
	            return main_core.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HAS_ANY_VALUE');
	          }

	          return main_core.Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_IS_EMPTY');
	        }();

	        var onRemoveClick = function onRemoveClick(event) {
	          _this4.restoreField(event.currentTarget.closest('.main-ui-filter-field-with-additional-filter'));
	        };

	        return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"main-ui-control main-ui-filter-additional-filter-placeholder\" data-type=\"", "\">\n\t\t\t\t\t<div class=\"main-ui-square\">\n\t\t\t\t\t\t<div class=\"main-ui-square-item\">", "</div>\n\t\t\t\t\t\t<div class=\"main-ui-item-icon main-ui-square-delete\" onclick=\"", "\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), typeId, message, onRemoveClick);
	      });
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter(fieldNode) {
	      if (main_core.Type.isDomNode(fieldNode)) {
	        var placeholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');

	        if (main_core.Type.isDomNode(placeholder)) {
	          var type = main_core.Dom.attr(placeholder, 'data-type');
	          var fieldId = main_core.Dom.attr(fieldNode, 'data-name');
	          return babelHelpers.defineProperty({}, "".concat(fieldId, "_").concat(type), 'y');
	        }
	      }

	      return null;
	    }
	  }]);
	  return AdditionalFilter;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(AdditionalFilter, "Type", {
	  IS_EMPTY: 'isEmpty',
	  HAS_ANY_VALUE: 'hasAnyValue'
	});
	babelHelpers.defineProperty(AdditionalFilter, "cache", new main_core.Cache.MemoryCache());

	var _templateObject$3;

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var errorMessages = new WeakMap();
	var errorMessagesTypes = new WeakMap();
	var values = new WeakMap();
	var Fields = /*#__PURE__*/function () {
	  function Fields(parent) {
	    babelHelpers.classCallCheck(this, Fields);
	    this.parent = null;
	    this.init(parent);
	  }

	  babelHelpers.createClass(Fields, [{
	    key: "init",
	    value: function init(parent) {
	      this.parent = parent;
	      BX.addCustomEvent(window, 'UI::Select::change', this._onDateTypeChange.bind(this));
	    }
	  }, {
	    key: "deleteField",
	    value: function deleteField(node) {
	      main_core.Dom.remove(node);
	    }
	  }, {
	    key: "isFieldDelete",
	    value: function isFieldDelete(node) {
	      return main_core.Dom.hasClass(node, this.parent.settings.classFieldDelete);
	    }
	  }, {
	    key: "isFieldValueDelete",
	    value: function isFieldValueDelete(node) {
	      return main_core.Dom.hasClass(node, this.parent.settings.classValueDelete) || main_core.Dom.hasClass(node.parentNode, this.parent.settings.classValueDelete);
	    }
	  }, {
	    key: "isDragButton",
	    value: function isDragButton(node) {
	      return node && main_core.Dom.hasClass(node, this.parent.settings.classPresetDragButton);
	    }
	    /**
	     * Clears values of filter field node
	     * @param {HTMLElement} field
	     */

	  }, {
	    key: "clearFieldValue",
	    value: function clearFieldValue(field) {
	      if (field) {
	        var controls = babelHelpers.toConsumableArray(field.querySelectorAll('.main-ui-control'));
	        var squares = babelHelpers.toConsumableArray(field.querySelectorAll('.main-ui-square'));
	        squares.forEach(function (square) {
	          return main_core.Dom.remove(square);
	        });
	        controls.forEach(function (control) {
	          if (Reflect.has(control, 'value')) {
	            control.value = '';
	          }
	        });
	      }
	    }
	  }, {
	    key: "getField",
	    value: function getField(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node.closest('.main-ui-control-field, .main-ui-control-field-group');
	      }

	      return null;
	    }
	  }, {
	    key: "render",
	    value: function render(template, data) {
	      if (main_core.Type.isString(template) && main_core.Type.isPlainObject(data)) {
	        var html = Object.entries(data).reduce(function (acc, _ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              key = _ref2[0],
	              value = _ref2[1];

	          return acc.replace(new RegExp("{{".concat(key, "}}"), 'g'), value);
	        }, template);
	        var wrapped = main_core.Dom.create('div', {
	          html: html
	        });
	        var fieldGroup = wrapped.querySelector('.main-ui-control-field-group');

	        if (fieldGroup) {
	          return fieldGroup;
	        }

	        var field = wrapped.querySelector('.main-ui-control-field');

	        if (field) {
	          return field;
	        }

	        var fieldLine = wrapped.querySelector('.main-ui-filter-field-line');

	        if (fieldLine) {
	          return fieldLine;
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "createInputText",
	    value: function createInputText(fieldData) {
	      var field = {
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        deleteButton: true,
	        valueDelete: true,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: [{
	          block: 'main-ui-control-string',
	          name: fieldData.NAME,
	          placeholder: fieldData.PLACEHOLDER || '',
	          value: main_core.Type.isString(fieldData.VALUE) || main_core.Type.isNumber(fieldData.VALUE) ? fieldData.VALUE : '',
	          tabindex: fieldData.TABINDEX
	        }]
	      };
	      var renderedField = BX.decl(field);
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: renderedField
	        })
	      });
	      return renderedField;
	    }
	  }, {
	    key: "createTextarea",
	    value: function createTextarea(fieldData) {
	      var field = BX.decl({
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        deleteButton: true,
	        valueDelete: true,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: [{
	          block: 'main-ui-control-textarea',
	          name: fieldData.NAME,
	          placeholder: fieldData.PLACEHOLDER || '',
	          value: main_core.Type.isString(fieldData.VALUE) || main_core.Type.isNumber(fieldData.VALUE) ? fieldData.VALUE : '',
	          tabindex: fieldData.TABINDEX
	        }]
	      });
	      var textarea = field.querySelector('textarea');

	      var onChange = function onChange() {
	        main_core.Dom.style(textarea, 'height', '1px');
	        main_core.Dom.style(textarea, 'height', "".concat(textarea.scrollHeight, "px"));
	      };

	      main_core.Event.bind(textarea, 'input', onChange);
	      main_core.Event.bind(textarea, 'change', onChange);
	      main_core.Event.bind(textarea, 'keyup', onChange);
	      main_core.Event.bind(textarea, 'cut', onChange);
	      main_core.Event.bind(textarea, 'paste', onChange);
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createCustomEntityFieldLayout",
	    value: function createCustomEntityFieldLayout(fieldData) {
	      var field = {
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        deleteButton: true,
	        valueDelete: true,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: {
	          block: 'main-ui-control-entity',
	          mix: 'main-ui-control',
	          attrs: {
	            'data-multiple': JSON.stringify(fieldData.MULTIPLE)
	          },
	          content: []
	        }
	      };

	      if ('_label' in fieldData.VALUES && !!fieldData.VALUES._label) {
	        if (fieldData.MULTIPLE) {
	          var label = fieldData.VALUES._label ? fieldData.VALUES._label : [];

	          if (main_core.Type.isPlainObject(label)) {
	            label = Object.keys(label).map(function (key) {
	              return label[key];
	            });
	          }

	          if (!main_core.Type.isArray(label)) {
	            label = [label];
	          }

	          var value = fieldData.VALUES._value ? fieldData.VALUES._value : [];

	          if (main_core.Type.isPlainObject(value)) {
	            value = Object.keys(value).map(function (key) {
	              return value[key];
	            });
	          }

	          if (!main_core.Type.isArray(value)) {
	            value = [value];
	          }

	          label.forEach(function (currentLabel, index) {
	            field.content.content.push({
	              block: 'main-ui-square',
	              tag: 'span',
	              name: currentLabel,
	              item: {
	                _label: currentLabel,
	                _value: value[index]
	              }
	            });
	          });
	        } else {
	          field.content.content.push({
	            block: 'main-ui-square',
	            tag: 'span',
	            name: '_label' in fieldData.VALUES ? fieldData.VALUES._label : '',
	            item: fieldData.VALUES
	          });
	        }
	      }

	      field.content.content.push({
	        block: 'main-ui-square-search',
	        tag: 'span',
	        content: {
	          block: 'main-ui-control-string',
	          name: "".concat(fieldData.NAME, "_label"),
	          tabindex: fieldData.TABINDEX,
	          type: 'text',
	          placeholder: fieldData.PLACEHOLDER || ''
	        }
	      }, {
	        block: 'main-ui-control-string',
	        name: fieldData.NAME,
	        type: 'hidden',
	        placeholder: fieldData.PLACEHOLDER || '',
	        value: '_value' in fieldData.VALUES ? fieldData.VALUES._value : '',
	        tabindex: fieldData.TABINDEX
	      });
	      field = BX.decl(field);
	      var input = BX.Filter.Utils.getBySelector(field, '.main-ui-control-string[type="text"]');
	      BX.addClass(input, 'main-ui-square-search-item');
	      input.autocomplete = 'off';
	      main_core.Event.bind(input, 'focus', BX.proxy(this._onCustomEntityInputFocus, this));
	      main_core.Event.bind(input, 'click', BX.proxy(this._onCustomEntityInputClick, this));

	      if (!this.bindDocument) {
	        main_core.Event.bind(document, 'click', BX.proxy(this._onCustomEntityBlur, this));
	        document.addEventListener('focus', BX.proxy(this._onDocumentFocus, this), true);
	        this.bindDocument = true;
	      }

	      main_core.Event.bind(input, 'keydown', BX.proxy(this._onCustomEntityKeydown, this));
	      main_core.Event.bind(field, 'click', BX.proxy(this._onCustomEntityFieldClick, this));
	      return field;
	    }
	  }, {
	    key: "createDestSelector",
	    value: function createDestSelector(fieldData) {
	      var field = this.createCustomEntityFieldLayout(fieldData);
	      BX.ready(BX.proxy(function () {
	        BX.Filter.DestinationSelector.create(fieldData.NAME, {
	          filterId: this.parent.getParam('FILTER_ID'),
	          fieldId: fieldData.NAME
	        });
	      }, this));
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createEntitySelector",
	    value: function createEntitySelector(fieldData) {
	      var field = this.createCustomEntityFieldLayout(fieldData);
	      BX.Filter.EntitySelector.create(fieldData.NAME, {
	        filter: this.parent,
	        isMultiple: fieldData.MULTIPLE,
	        addEntityIdToResult: fieldData.ADD_ENTITY_ID_TO_RESULT,
	        showDialogOnEmptyInput: fieldData.SHOW_DIALOG_ON_EMPTY_INPUT,
	        dialogOptions: fieldData.DIALOG_OPTIONS
	      });
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createCustomEntity",
	    value: function createCustomEntity(fieldData) {
	      var field = this.createCustomEntityFieldLayout(fieldData);
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "_onCustomEntityInputFocus",
	    value: function _onCustomEntityInputFocus(event) {
	      BX.fireEvent(event.currentTarget, 'click');
	    }
	  }, {
	    key: "_onCustomEntityInputClick",
	    value: function _onCustomEntityInputClick(event) {
	      event.preventDefault();
	      event.stopPropagation();

	      if (event.isTrusted) {
	        this.trustTimestamp = event.timeStamp;
	        this.notTrustTimestamp = this.notTrustTimestamp || event.timeStamp;
	      } else {
	        this.notTrustTimestamp = event.timeStamp;
	      }

	      var trustDate = new Date(this.trustTimestamp);
	      var notTrustDate = new Date(this.notTrustTimestamp);
	      var trustTime = "".concat(trustDate.getMinutes(), ":").concat(trustDate.getSeconds());
	      var notTrustTime = "".concat(notTrustDate.getMinutes(), ":").concat(notTrustDate.getSeconds());

	      if (trustTime !== notTrustTime) {
	        this._onCustomEntityFocus(event);
	      }
	    }
	  }, {
	    key: "_onDocumentFocus",
	    value: function _onDocumentFocus(event) {
	      var CustomEntity = this.getCustomEntityInstance();
	      var popupContainer = CustomEntity.getPopupContainer();
	      var isOnInputField = CustomEntity.getLabelNode() === event.target;
	      var isInsidePopup = !!popupContainer && popupContainer.contains(event.target);

	      if (!isOnInputField && !isInsidePopup) {
	        this._onCustomEntityBlur(event);
	      }
	    }
	  }, {
	    key: "_onCustomEntityKeydown",
	    value: function _onCustomEntityKeydown(event) {
	      var target = event.target,
	          currentTarget = event.currentTarget;
	      var parentNode = target.parentNode.parentNode;
	      var squares = parentNode.querySelectorAll('.main-ui-square');
	      var square = squares[squares.length - 1];

	      if (!main_core.Type.isDomNode(square)) {
	        return;
	      }

	      if (BX.Filter.Utils.isKey(event, 'backspace') && currentTarget.selectionStart === 0) {
	        if (main_core.Dom.hasClass(square, 'main-ui-square-selected')) {
	          var input = parentNode.querySelector('input[type="hidden"]');

	          if (main_core.Type.isDomNode(input)) {
	            input.value = '';
	            BX.fireEvent(input, 'input');
	          }

	          main_core.Dom.remove(square);
	          return;
	        }

	        main_core.Dom.addClass(square, 'main-ui-square-selected');
	        return;
	      }

	      main_core.Dom.removeClass(square, 'main-ui-square-selected');
	    }
	  }, {
	    key: "_onCustomEntityFieldClick",
	    value: function _onCustomEntityFieldClick(_ref3) {
	      var target = _ref3.target;

	      if (main_core.Dom.hasClass(target, 'main-ui-square-delete')) {
	        var square = target.closest('.main-ui-square');

	        if (main_core.Type.isDomNode(square)) {
	          var CustomEntity = this.getCustomEntityInstance();
	          BX.onCustomEvent(window, 'BX.Main.Filter:customEntityRemove', [CustomEntity]);
	          main_core.Dom.remove(square);
	        }

	        return;
	      }

	      var input = target.querySelector('input[type="text"]');

	      if (main_core.Type.isDomNode(input)) {
	        BX.fireEvent(input, 'focus');
	      }
	    }
	  }, {
	    key: "_onCustomEntityBlur",
	    value: function _onCustomEntityBlur(event) {
	      var eventData = {
	        stopBlur: false
	      };
	      BX.onCustomEvent(window, 'BX.Main.Filter:onGetStopBlur', [event, eventData]);

	      if (typeof eventData.stopBlur === 'undefined' || !eventData.stopBlur) {
	        var CustomEntity = this.getCustomEntityInstance();
	        BX.onCustomEvent(window, 'BX.Main.Filter:customEntityBlur', [CustomEntity]);
	        main_core.Event.unbind(CustomEntity.getPopupContainer(), 'click', this._stopPropagation);
	        main_core.Dom.removeClass(CustomEntity.getField(), 'main-ui-focus');
	      }
	    }
	  }, {
	    key: "_stopPropagation",
	    value: function _stopPropagation(event) {
	      event.stopPropagation();
	    }
	  }, {
	    key: "getCustomEntityInstance",
	    value: function getCustomEntityInstance() {
	      if (!(this.customEntityInstance instanceof BX.Main.ui.CustomEntity)) {
	        this.customEntityInstance = new BX.Main.ui.CustomEntity();
	      }

	      return this.customEntityInstance;
	    }
	  }, {
	    key: "_onCustomEntityFocus",
	    value: function _onCustomEntityFocus(event) {
	      event.stopPropagation();
	      var currentTarget = event.currentTarget;
	      var field = currentTarget.closest('.main-ui-control-entity');
	      var CustomEntity = this.getCustomEntityInstance();
	      CustomEntity.setField(field);
	      BX.onCustomEvent('BX.Main.Filter:customEntityFocus', [CustomEntity]);
	      var popupContainer = CustomEntity.getPopupContainer();

	      if (main_core.Type.isElementNode(popupContainer)) {
	        main_core.Event.bind(popupContainer, 'click', this._stopPropagation);
	      }

	      main_core.Dom.addClass(field, 'main-ui-focus');
	    }
	  }, {
	    key: "createCustom",
	    value: function createCustom(fieldData) {
	      var field = BX.decl({
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        deleteButton: true,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: {
	          block: 'main-ui-custom',
	          mix: ['main-ui-control', 'main-ui-custom-style'],
	          attrs: {
	            'data-name': fieldData.NAME
	          },
	          content: ''
	        }
	      });

	      if (main_core.Type.isString(fieldData.VALUE)) {
	        var fieldValue = function () {
	          if (Reflect.has(fieldData, '_VALUE')) {
	            return fieldData._VALUE;
	          }

	          return '';
	        }();

	        var html = main_core.Text.decode(fieldData.VALUE).replace("name=\"".concat(fieldData.NAME, "\""), "name=\"".concat(fieldData.NAME, "\" value=\"").concat(fieldValue, "\""));
	        var control = field.querySelector('.main-ui-custom');
	        main_core.Runtime.html(control, html);
	      }

	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createSelect",
	    value: function createSelect(fieldData) {
	      var field = BX.decl({
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        deleteButton: true,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: {
	          block: this.parent.settings.classSelect,
	          name: fieldData.NAME,
	          items: fieldData.ITEMS,
	          value: 'VALUE' in fieldData ? fieldData.VALUE : fieldData.ITEMS[0],
	          params: fieldData.PARAMS,
	          tabindex: fieldData.TABINDEX,
	          valueDelete: false
	        }
	      });
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createMultiSelect",
	    value: function createMultiSelect(fieldData) {
	      var field = BX.decl({
	        block: 'main-ui-control-field',
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
	        name: fieldData.NAME,
	        type: fieldData.TYPE,
	        deleteButton: true,
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        content: {
	          block: 'main-ui-multi-select',
	          name: fieldData.NAME,
	          tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
	          placeholder: !this.parent.getParam('ENABLE_LABEL') && 'PLACEHOLDER' in fieldData ? fieldData.PLACEHOLDER : '',
	          items: 'ITEMS' in fieldData ? fieldData.ITEMS : [],
	          value: 'VALUE' in fieldData ? fieldData.VALUE : [],
	          params: 'PARAMS' in fieldData ? fieldData.PARAMS : {
	            isMulti: true
	          },
	          valueDelete: true
	        }
	      });
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createCustomDate",
	    value: function createCustomDate(fieldData) {
	      var group = {
	        block: 'main-ui-control-field-group',
	        type: fieldData.TYPE,
	        mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel, 'main-ui-filter-date-group'] : ['main-ui-filter-date-group'],
	        label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
	        icon: this.parent.getParam('ENABLE_LABEL') && fieldData.ICON ? fieldData.ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
	        name: 'NAME' in fieldData ? fieldData.NAME : '',
	        deleteButton: true,
	        content: []
	      };

	      if (main_core.Type.isPlainObject(fieldData.VALUE.days)) {
	        fieldData.VALUE.days = Object.keys(fieldData.VALUE.days).map(function (index) {
	          return fieldData.VALUE.days[index];
	        });
	      }

	      var daysValue = fieldData.DAYS.filter(function (item) {
	        return fieldData.VALUE.days.some(function (value) {
	          return value === item.VALUE;
	        });
	      });
	      var days = {
	        block: 'main-ui-control-field',
	        mix: ['main-ui-control-custom-date'],
	        placeholder: fieldData.DAYS_PLACEHOLDER,
	        dragButton: false,
	        content: {
	          block: 'main-ui-multi-select',
	          name: "".concat(fieldData.NAME, "_days"),
	          tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
	          items: fieldData.DAYS,
	          value: daysValue,
	          params: 'PARAMS' in fieldData ? fieldData.PARAMS : {
	            isMulti: true
	          },
	          valueDelete: true,
	          attrs: {
	            'data-placeholder': fieldData.DAYS_PLACEHOLDER
	          }
	        }
	      };

	      if (main_core.Type.isPlainObject(fieldData.VALUE.months)) {
	        fieldData.VALUE.months = Object.keys(fieldData.VALUE.months).map(function (index) {
	          return fieldData.VALUE.months[index];
	        });
	      }

	      var monthsValue = fieldData.MONTHS.filter(function (item) {
	        return fieldData.VALUE.months.some(function (value) {
	          return value === item.VALUE;
	        });
	      });
	      var months = {
	        block: 'main-ui-control-field',
	        mix: ['main-ui-control-custom-date'],
	        dragButton: false,
	        content: {
	          block: 'main-ui-multi-select',
	          name: "".concat(fieldData.NAME, "_months"),
	          tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
	          items: fieldData.MONTHS,
	          value: monthsValue,
	          params: 'PARAMS' in fieldData ? fieldData.PARAMS : {
	            isMulti: true
	          },
	          valueDelete: true,
	          attrs: {
	            'data-placeholder': fieldData.MONTHS_PLACEHOLDER
	          }
	        }
	      };

	      if (main_core.Type.isPlainObject(fieldData.VALUE.years)) {
	        fieldData.VALUE.years = Object.keys(fieldData.VALUE.years).map(function (index) {
	          return fieldData.VALUE.years[index];
	        });
	      }

	      var yearsValue = fieldData.YEARS.filter(function (item) {
	        return fieldData.VALUE.years.some(function (value) {
	          return value === item.VALUE;
	        });
	      });
	      var years = {
	        block: 'main-ui-control-field',
	        mix: ['main-ui-control-custom-date'],
	        dragButton: false,
	        content: {
	          block: 'main-ui-multi-select',
	          name: "".concat(fieldData.NAME, "_years"),
	          tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
	          items: fieldData.YEARS,
	          value: yearsValue,
	          params: 'PARAMS' in fieldData ? fieldData.PARAMS : {
	            isMulti: true
	          },
	          valueDelete: true,
	          attrs: {
	            'data-placeholder': fieldData.YEARS_PLACEHOLDER
	          }
	        }
	      };
	      group.content.push(days);
	      group.content.push(months);
	      group.content.push(years);
	      var field = BX.decl(group);
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, fieldData),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "_onDateTypeChange",
	    value: function _onDateTypeChange(instance, data) {
	      var _this = this;

	      if (this.parent.getPopup().contentContainer.contains(instance.node)) {
	        var fieldData = {};
	        var dateGroup = null;
	        var label;
	        var controls;
	        var index;

	        if (main_core.Type.isPlainObject(data) && Reflect.has(data, 'VALUE')) {
	          var fieldNode = instance.getNode();
	          var params = instance.getParams();
	          var name = fieldNode.dataset.name;

	          if (!main_core.Type.isPlainObject(params) && (name.endsWith('_datesel') || name.endsWith('_numsel'))) {
	            var group = fieldNode.parentNode.parentNode;
	            fieldData.TABINDEX = instance.getInput().getAttribute('tabindex');
	            fieldData.SUB_TYPES = instance.getItems();
	            fieldData.SUB_TYPE = data;
	            fieldData.NAME = group.dataset.name;
	            fieldData.TYPE = group.dataset.type;
	            fieldData.VALUE_REQUIRED = group.dataset.valueRequired === 'true';
	            var presetData = this.parent.getPreset().getCurrentPresetData();

	            if (main_core.Type.isArray(presetData.FIELDS)) {
	              var presetField = presetData.FIELDS.find(function (current) {
	                return current.NAME === fieldData.NAME;
	              });

	              if (main_core.Type.isNil(presetField)) {
	                presetField = this.parent.params.FIELDS_STUBS.find(function (current) {
	                  return current.TYPE === fieldData.TYPE;
	                });
	              }

	              if (!main_core.Type.isNil(presetField)) {
	                if (name.endsWith('_datesel')) {
	                  fieldData.MONTHS = presetField.MONTHS;
	                  fieldData.MONTH = presetField.MONTH;
	                  fieldData.YEARS = presetField.YEARS;
	                  fieldData.YEAR = presetField.YEAR;
	                  fieldData.QUARTERS = presetField.QUARTERS;
	                  fieldData.QUARTER = presetField.QUARTER;
	                  fieldData.ENABLE_TIME = presetField.ENABLE_TIME;
	                  fieldData.YEARS_SWITCHER = presetField.YEARS_SWITCHER;
	                }

	                fieldData.VALUES = presetField.VALUES;
	                fieldData.REQUIRED = presetField.REQUIRED;
	              }
	            }

	            if (this.parent.getParam('ENABLE_LABEL')) {
	              label = group.querySelector('.main-ui-control-field-label');
	              fieldData.LABEL = label.innerText;
	            }

	            if (name.endsWith('_datesel')) {
	              dateGroup = this.createDate(fieldData);
	            } else {
	              dateGroup = this.createNumber(fieldData);
	            }

	            if (main_core.Type.isArray(this.parent.fieldsList)) {
	              index = this.parent.fieldsList.indexOf(group);

	              if (index !== -1) {
	                this.parent.fieldsList[index] = dateGroup;
	                this.parent.registerDragItem(dateGroup);
	              }
	            }

	            this.parent.unregisterDragItem(group);
	            controls = babelHelpers.toConsumableArray(dateGroup.querySelectorAll('.main-ui-control-field'));

	            if (main_core.Type.isArray(controls) && controls.length) {
	              controls.forEach(function (control) {
	                control.FieldController = new BX.Filter.FieldController(control, _this.parent);
	              });
	            }

	            if (this.parent.getParam('ENABLE_ADDITIONAL_FILTERS')) {
	              var button = AdditionalFilter.getInstance().getAdditionalFilterButton({
	                fieldId: fieldData.NAME,
	                enabled: fieldData.ADDITIONAL_FILTER_ALLOWED
	              });
	              main_core.Dom.append(button, dateGroup);
	            }

	            main_core.Dom.insertAfter(dateGroup, group);
	            main_core.Dom.remove(group);
	          }
	        }
	      }
	    }
	  }, {
	    key: "createNumber",
	    value: function createNumber(options) {
	      var numberTypes = this.parent.numberTypes;
	      var ENABLE_LABEL = this.parent.params.ENABLE_LABEL;
	      var _options$SUB_TYPE = options.SUB_TYPE,
	          SUB_TYPE = _options$SUB_TYPE === void 0 ? {} : _options$SUB_TYPE,
	          _options$SUB_TYPES = options.SUB_TYPES,
	          SUB_TYPES = _options$SUB_TYPES === void 0 ? [] : _options$SUB_TYPES,
	          _options$TABINDEX = options.TABINDEX,
	          TABINDEX = _options$TABINDEX === void 0 ? '' : _options$TABINDEX,
	          _options$VALUES = options.VALUES,
	          VALUES = _options$VALUES === void 0 ? {
	        _from: '',
	        _to: ''
	      } : _options$VALUES,
	          _options$LABEL = options.LABEL,
	          LABEL = _options$LABEL === void 0 ? '' : _options$LABEL,
	          _options$ICON = options.ICON,
	          ICON = _options$ICON === void 0 ? null : _options$ICON,
	          TYPE = options.TYPE;
	      var subType = SUB_TYPE.VALUE || numberTypes.SINGLE;
	      var placeholder = SUB_TYPE.PLACEHOLDER || '';
	      var fieldName = options.NAME.replace('_numsel', '');

	      var classes = function () {
	        if (ENABLE_LABEL) {
	          return ['main-ui-filter-wield-with-label', 'main-ui-filter-number-group'];
	        }

	        return ['main-ui-filter-number-group'];
	      }();

	      var fieldGroup = {
	        block: 'number-group',
	        type: TYPE,
	        mix: classes,
	        label: ENABLE_LABEL ? LABEL : '',
	        icon: ENABLE_LABEL ? ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        tabindex: TABINDEX,
	        value: SUB_TYPE,
	        items: SUB_TYPES,
	        name: fieldName,
	        deleteButton: true,
	        content: []
	      };

	      if (subType !== numberTypes.LESS) {
	        var from = {
	          block: 'main-ui-control-field',
	          type: TYPE,
	          dragButton: false,
	          content: {
	            block: 'main-ui-number',
	            mix: ['filter-type-single'],
	            calendarButton: true,
	            valueDelete: true,
	            placeholder: placeholder,
	            name: "".concat(fieldName, "_from"),
	            tabindex: TABINDEX,
	            value: VALUES._from || ''
	          }
	        };
	        fieldGroup.content.push(from);
	      }

	      if (subType === numberTypes.RANGE) {
	        var line = {
	          block: 'main-ui-filter-field-line',
	          content: {
	            block: 'main-ui-filter-field-line-item',
	            tag: 'span'
	          }
	        };
	        fieldGroup.content.push(line);
	      }

	      if (subType === numberTypes.RANGE || subType === numberTypes.LESS) {
	        var to = {
	          block: 'main-ui-control-field',
	          type: TYPE,
	          dragButton: false,
	          content: {
	            block: 'main-ui-number',
	            calendarButton: true,
	            valueDelete: true,
	            name: "".concat(fieldName, "_to"),
	            tabindex: TABINDEX,
	            value: VALUES._to || ''
	          }
	        };
	        fieldGroup.content.push(to);
	      }

	      var field = BX.decl(fieldGroup);
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4({}, options),
	          node: field
	        })
	      });
	      return field;
	    }
	  }, {
	    key: "createDate",
	    value: function createDate(options) {
	      var _this2 = this;

	      var _this$parent = this.parent,
	          dateTypes = _this$parent.dateTypes,
	          additionalDateTypes = _this$parent.additionalDateTypes;
	      var _options$SUB_TYPE2 = options.SUB_TYPE,
	          SUB_TYPE = _options$SUB_TYPE2 === void 0 ? {} : _options$SUB_TYPE2,
	          _options$SUB_TYPES2 = options.SUB_TYPES,
	          SUB_TYPES = _options$SUB_TYPES2 === void 0 ? [] : _options$SUB_TYPES2,
	          _options$PLACEHOLDER = options.PLACEHOLDER,
	          PLACEHOLDER = _options$PLACEHOLDER === void 0 ? '' : _options$PLACEHOLDER,
	          _options$VALUES2 = options.VALUES,
	          VALUES = _options$VALUES2 === void 0 ? {
	        _from: '',
	        _to: '',
	        _quarter: '',
	        _days: '',
	        _month: '',
	        _year: '',
	        _allow_year: ''
	      } : _options$VALUES2,
	          _options$TABINDEX2 = options.TABINDEX,
	          TABINDEX = _options$TABINDEX2 === void 0 ? '' : _options$TABINDEX2,
	          _options$ENABLE_TIME = options.ENABLE_TIME,
	          ENABLE_TIME = _options$ENABLE_TIME === void 0 ? false : _options$ENABLE_TIME,
	          _options$LABEL2 = options.LABEL,
	          LABEL = _options$LABEL2 === void 0 ? '' : _options$LABEL2,
	          _options$ICON2 = options.ICON,
	          ICON = _options$ICON2 === void 0 ? null : _options$ICON2,
	          TYPE = options.TYPE,
	          _options$VALUE_REQUIR = options.VALUE_REQUIRED,
	          VALUE_REQUIRED = _options$VALUE_REQUIR === void 0 ? false : _options$VALUE_REQUIR,
	          _options$REQUIRED = options.REQUIRED,
	          REQUIRED = _options$REQUIRED === void 0 ? false : _options$REQUIRED;
	      var ENABLE_LABEL = this.parent.params.ENABLE_LABEL;
	      var subType = SUB_TYPE.VALUE || dateTypes.NONE;
	      var fieldName = options.NAME.replace('_datesel', '');

	      var classes = function () {
	        if (ENABLE_LABEL) {
	          return ['main-ui-filter-wield-with-label', 'main-ui-filter-date-group'];
	        }

	        return ['main-ui-filter-date-group'];
	      }();

	      var fieldGroup = {
	        block: 'date-group',
	        type: TYPE,
	        mix: classes,
	        label: ENABLE_LABEL ? LABEL : '',
	        icon: ENABLE_LABEL ? ICON : null,
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
	        deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
	        tabindex: TABINDEX,
	        value: SUB_TYPE,
	        items: SUB_TYPES,
	        name: fieldName,
	        enableTime: ENABLE_TIME,
	        deleteButton: true,
	        content: []
	      };

	      if (subType === dateTypes.EXACT) {
	        var fieldDecl = createDateInputDecl({
	          type: TYPE,
	          name: "".concat(fieldName.NAME, "_from"),
	          placeholder: PLACEHOLDER,
	          tabindex: TABINDEX,
	          value: VALUES._from || '',
	          enableTime: ENABLE_TIME
	        });
	        fieldGroup.content.push(fieldDecl);
	      }

	      if (subType === dateTypes.NEXT_DAYS || subType === dateTypes.PREV_DAYS || subType === additionalDateTypes.PREV_DAY || subType === additionalDateTypes.NEXT_DAY || subType === additionalDateTypes.MORE_THAN_DAYS_AGO || subType === additionalDateTypes.AFTER_DAYS) {
	        var _fieldDecl = createNumberInputDecl({
	          type: TYPE,
	          name: "".concat(fieldName, "_days"),
	          tabindex: TABINDEX,
	          value: VALUES._days || '',
	          placeholder: PLACEHOLDER
	        });

	        fieldGroup.content.push(_fieldDecl);
	      }

	      if (subType === dateTypes.RANGE) {
	        var rangeGroup = {
	          block: 'main-ui-filter-range-group',
	          content: [createDateInputDecl({
	            type: TYPE,
	            name: "".concat(fieldName, "_from"),
	            placeholder: PLACEHOLDER,
	            tabindex: TABINDEX,
	            value: VALUES._from || '',
	            enableTime: ENABLE_TIME
	          }), createLineDecl(), createDateInputDecl({
	            type: TYPE,
	            name: "".concat(fieldName, "_to"),
	            placeholder: PLACEHOLDER,
	            tabindex: TABINDEX,
	            value: VALUES._to || '',
	            enableTime: ENABLE_TIME
	          })]
	        };
	        fieldGroup.content.push(rangeGroup);
	      }

	      if (subType === dateTypes.MONTH) {
	        var MONTHS = options.MONTHS,
	            MONTH = options.MONTH,
	            YEARS = options.YEARS,
	            YEAR = options.YEAR;
	        var monthValue = MONTHS.find(function (item) {
	          return item.VALUE === VALUES._month;
	        }) || MONTH || MONTHS[0];
	        var yearValue = YEARS.find(function (item) {
	          return item.VALUE === VALUES._year;
	        }) || YEAR || YEARS[0];
	        fieldGroup.content.push(createSelectDecl({
	          name: "".concat(fieldName, "_month"),
	          value: monthValue,
	          items: MONTHS,
	          tabindex: TABINDEX
	        }), createSelectDecl({
	          name: "".concat(fieldName, "_year"),
	          value: yearValue,
	          items: YEARS,
	          tabindex: TABINDEX
	        }));
	      }

	      if (subType === dateTypes.QUARTER) {
	        var _YEARS = options.YEARS,
	            _YEAR = options.YEAR,
	            QUARTERS = options.QUARTERS,
	            QUARTER = options.QUARTER,
	            PARAMS = options.PARAMS;

	        var _yearValue = _YEARS.find(function (item) {
	          return item.VALUE === VALUES._year;
	        }) || _YEAR || _YEARS[0];

	        var quarterValue = QUARTERS.find(function (item) {
	          return item.VALUE === VALUES._quarter;
	        }) || QUARTER || QUARTERS[0];
	        fieldGroup.content.push(createSelectDecl({
	          name: "".concat(fieldName, "_year"),
	          value: _yearValue,
	          items: _YEARS,
	          tabindex: TABINDEX
	        }), createSelectDecl({
	          name: "".concat(fieldName, "_quarter"),
	          value: quarterValue,
	          items: QUARTERS,
	          tabindex: TABINDEX,
	          params: PARAMS
	        }));
	      }

	      if (subType === dateTypes.YEAR) {
	        var _YEARS2 = options.YEARS,
	            _YEAR2 = options.YEAR;

	        var _yearValue2 = _YEARS2.find(function (item) {
	          return item.VALUE === VALUES._year;
	        }) || _YEAR2 || _YEARS2[0];

	        fieldGroup.content.push(createSelectDecl({
	          name: "".concat(fieldName, "_year"),
	          value: _yearValue2,
	          items: _YEARS2,
	          tabindex: TABINDEX
	        }));
	      }

	      if (subType === 'CUSTOM_DATE') {
	        var customDateSubType = SUB_TYPES.find(function (item) {
	          return item.VALUE === 'CUSTOM_DATE';
	        });

	        if (customDateSubType) {
	          var customDateDecl = main_core.Runtime.clone(customDateSubType.DECL);

	          if (main_core.Type.isArray(VALUES._days)) {
	            customDateDecl.VALUE.days = VALUES._days;
	          }

	          if (main_core.Type.isArray(VALUES._month)) {
	            customDateDecl.VALUE.months = VALUES._month;
	          }

	          if (main_core.Type.isArray(VALUES._year)) {
	            customDateDecl.VALUE.years = VALUES._year;
	          }

	          var renderedField = this.createCustomDate(customDateDecl);
	          main_core.Dom.removeClass(renderedField, 'main-ui-filter-wield-with-label');
	          var buttons = babelHelpers.toConsumableArray(renderedField.querySelectorAll('.main-ui-item-icon-container, .main-ui-filter-icon-grab'));
	          buttons.forEach(function (button) {
	            return main_core.Dom.remove(button);
	          });
	          fieldGroup.content.push(renderedField);
	          fieldGroup.mix.push('main-ui-filter-custom-date-group');
	        }
	      }

	      if (subType !== dateTypes.NONE && subType !== additionalDateTypes.CUSTOM_DATE && options.YEARS_SWITCHER) {
	        var YEARS_SWITCHER = main_core.Runtime.clone(options.YEARS_SWITCHER);
	        var ITEMS = YEARS_SWITCHER.ITEMS;
	        YEARS_SWITCHER.VALUE = ITEMS.reduce(function (acc, item) {
	          return item.VALUE === VALUES._allow_year ? item : acc;
	        });

	        var _renderedField = this.createSelect(YEARS_SWITCHER);

	        main_core.Dom.addClass(_renderedField, ['main-ui-filter-year-switcher', 'main-ui-filter-with-padding']);
	        main_core.Dom.removeClass(_renderedField, 'main-ui-filter-wield-with-label');

	        var _buttons = babelHelpers.toConsumableArray(_renderedField.querySelectorAll('.main-ui-item-icon-container, .main-ui-filter-icon-grab'));

	        _buttons.forEach(function (button) {
	          return main_core.Dom.remove(button);
	        });

	        var lastIndex = fieldGroup.content.length - 1;
	        var lastContentItem = fieldGroup.content[lastIndex];

	        if (main_core.Type.isPlainObject(lastContentItem)) {
	          if (!main_core.Type.isArray(lastContentItem.mix)) {
	            lastContentItem.mix = [];
	          }

	          lastContentItem.mix.push('main-ui-filter-remove-margin-right');
	        }

	        if (main_core.Type.isDomNode(lastContentItem)) {
	          main_core.Dom.addClass(lastContentItem, 'main-ui-filter-remove-margin-right');
	        }

	        requestAnimationFrame(function () {
	          main_core.Dom.addClass(_renderedField.previousElementSibling, 'main-ui-filter-remove-margin-right');
	        });
	        fieldGroup.content.push(_renderedField);
	        fieldGroup.mix.push('main-ui-filter-date-with-years-switcher');
	      }

	      var renderedFieldGroup = BX.decl(fieldGroup);
	      var onDateChange = main_core.Runtime.debounce(this.onDateChange, 500, this);
	      var inputs = babelHelpers.toConsumableArray(renderedFieldGroup.querySelectorAll('.main-ui-date-input'));
	      inputs.forEach(function (input) {
	        input.addEventListener('change', onDateChange);
	        input.addEventListener('input', onDateChange);
	        var parentNode = input.parentNode;
	        var clearButton = parentNode.querySelector('.main-ui-control-value-delete');

	        if (clearButton) {
	          clearButton.addEventListener('click', function () {
	            setTimeout(function () {
	              _this2.onDateChange({
	                target: input
	              });
	            });
	          });
	        }
	      });

	      if (VALUE_REQUIRED) {
	        renderedFieldGroup.dataset.valueRequired = true;
	        var allInputs = [].concat(babelHelpers.toConsumableArray(inputs), babelHelpers.toConsumableArray(renderedFieldGroup.querySelectorAll('.main-ui-number-input')));
	        allInputs.forEach(function (input) {
	          input.addEventListener('change', _this2.checkRequiredDateValue.bind(_this2));
	          input.addEventListener('input', _this2.checkRequiredDateValue.bind(_this2));
	          var parentNode = input.parentNode;
	          var clearButton = parentNode.querySelector('.main-ui-control-value-delete');

	          if (clearButton) {
	            clearButton.addEventListener('click', function () {
	              setTimeout(function () {
	                _this2.checkRequiredDateValue({
	                  target: input
	                });
	              });
	            });
	          }

	          main_core.Event.bindOnce(input, 'mouseout', function () {
	            _this2.checkRequiredDateValue({
	              target: input
	            });
	          });
	        });
	      }

	      if (REQUIRED) {
	        var removeButton = renderedFieldGroup.querySelector('.main-ui-filter-field-delete');

	        if (removeButton) {
	          BX.remove(removeButton);
	        }
	      }

	      var currentValues = {};
	      this.parent.prepareControlDateValue(currentValues, fieldName, renderedFieldGroup);
	      Object.entries(currentValues).forEach(function (_ref4) {
	        var _ref5 = babelHelpers.slicedToArray(_ref4, 2),
	            key = _ref5[0],
	            value = _ref5[1];

	        currentValues[key.replace(fieldName, '')] = value;
	        delete currentValues[key];
	      });
	      this.parent.getEmitter().emit('init', {
	        field: new Field({
	          parent: this.parent,
	          options: _objectSpread$4(_objectSpread$4({}, options), {}, {
	            VALUES: currentValues
	          }),
	          node: renderedFieldGroup
	        })
	      });
	      return renderedFieldGroup;
	    }
	  }, {
	    key: "checkRequiredDateValue",
	    value: function checkRequiredDateValue(event) {
	      if (event.target.value === '') {
	        this.showError({
	          id: 'valueError',
	          target: event.target,
	          text: this.parent.params.MAIN_UI_FILTER__VALUE_REQUIRED
	        });
	        return;
	      }

	      this.hideError({
	        id: 'valueError',
	        target: event.target
	      });
	    }
	  }, {
	    key: "onDateChange",
	    value: function onDateChange(event) {
	      var _this3 = this;

	      if (values.get(event.target) === event.target.value) {
	        return;
	      }

	      values.set(event.target, event.target.value);

	      if (event.target.value === '') {
	        this.hideError({
	          id: 'formatError',
	          target: event.target
	        });
	        return;
	      }

	      BX.ajax.runComponentAction('bitrix:main.ui.filter', 'checkDateFormat', {
	        mode: 'ajax',
	        data: {
	          value: event.target.value,
	          format: BX.message('FORMAT_DATETIME')
	        }
	      }).then(function (result) {
	        if (!result.data.result) {
	          _this3.showError({
	            id: 'formatError',
	            target: event.target
	          });

	          return;
	        }

	        _this3.hideError({
	          id: 'formatError',
	          target: event.target
	        });
	      });
	    }
	  }, {
	    key: "showError",
	    value: function showError(_ref6) {
	      var id = _ref6.id,
	          target = _ref6.target,
	          _ref6$text = _ref6.text,
	          text = _ref6$text === void 0 ? null : _ref6$text;
	      main_core.Dom.style(target, 'border-color', '#FF5752');

	      if (errorMessages.has(target) && errorMessagesTypes.get(target) === id) {
	        main_core.Dom.remove(errorMessages.get(target));
	      }

	      var _this$parent$params = this.parent.params,
	          MAIN_UI_FILTER__DATE_ERROR_TITLE = _this$parent$params.MAIN_UI_FILTER__DATE_ERROR_TITLE,
	          MAIN_UI_FILTER__DATE_ERROR_LABEL = _this$parent$params.MAIN_UI_FILTER__DATE_ERROR_LABEL;
	      var errorText = text || "".concat(MAIN_UI_FILTER__DATE_ERROR_LABEL, " ").concat(main_core.Loc.getMessage('FORMAT_DATE'));
	      var dateErrorMessage = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass=\"main-ui-filter-error-message\" \n\t\t\t\ttitle=\"", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), MAIN_UI_FILTER__DATE_ERROR_TITLE, errorText);
	      errorMessages.set(target, dateErrorMessage);
	      errorMessagesTypes.set(target, id);
	      main_core.Dom.insertAfter(dateErrorMessage, target);
	      main_core.Dom.attr(target, 'is-valid', 'false');
	    }
	  }, {
	    key: "hideError",
	    value: function hideError(_ref7) {
	      var id = _ref7.id,
	          target = _ref7.target;
	      main_core.Dom.style(target, 'border-color', null);

	      if (errorMessages.has(target) && errorMessagesTypes.get(target) === id) {
	        main_core.Dom.remove(errorMessages.get(target));
	      }

	      main_core.Dom.attr(target, 'is-valid', 'true');
	    }
	  }]);
	  return Fields;
	}();

	/* eslint-disable no-underscore-dangle */
	var Presets = /*#__PURE__*/function () {
	  function Presets(parent) {
	    babelHelpers.classCallCheck(this, Presets);
	    this.parent = null;
	    this.presets = null;
	    this.container = null;
	    this.init(parent);
	  }

	  babelHelpers.createClass(Presets, [{
	    key: "init",
	    value: function init(parent) {
	      this.parent = parent;
	    }
	  }, {
	    key: "bindOnPresetClick",
	    value: function bindOnPresetClick() {
	      var _this = this;

	      (this.getPresets() || []).forEach(function (current) {
	        main_core.Event.bind(current, 'click', BX.delegate(_this._onPresetClick, _this));
	      });
	    }
	    /**
	     * Gets add preset field
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getAddPresetField",
	    value: function getAddPresetField() {
	      return this.getContainer().querySelector('.main-ui-filter-new-filter');
	    }
	    /**
	     * Gets add preset name input
	     * @return {?HTMLInputElement}
	     */

	  }, {
	    key: "getAddPresetFieldInput",
	    value: function getAddPresetFieldInput() {
	      return this.getAddPresetField().querySelector('.main-ui-filter-sidebar-edit-control');
	    }
	    /**
	     * Clears add preset input value
	     */

	  }, {
	    key: "clearAddPresetFieldInput",
	    value: function clearAddPresetFieldInput() {
	      var input = this.getAddPresetFieldInput();

	      if (main_core.Type.isDomNode(input)) {
	        input.value = '';
	      }
	    }
	    /**
	     * Finds preset node by child node
	     * @param {?HTMLElement} node
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "normalizePreset",
	    value: function normalizePreset(node) {
	      return node.closest('.main-ui-filter-sidebar-item');
	    }
	    /**
	     * Deactivates all presets
	     */

	  }, {
	    key: "deactivateAllPresets",
	    value: function deactivateAllPresets() {
	      this.getPresets().forEach(function (current) {
	        main_core.Dom.removeClass(current, 'main-ui-filter-current-item');
	      });
	    }
	    /**
	     * Creates sidebar preset item
	     * @param {string} id - Preset id
	     * @param {string} title - Preset title
	     * @param {boolean} [isPinned] - Pass true is preset pinned
	     */

	  }, {
	    key: "createSidebarItem",
	    value: function createSidebarItem(id, title, isPinned) {
	      return BX.decl({
	        block: 'sidebar-item',
	        text: main_core.Text.decode(title),
	        id: id,
	        pinned: isPinned,
	        noEditPinTitle: this.parent.getParam('MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET'),
	        editNameTitle: this.parent.getParam('MAIN_UI_FILTER__EDIT_PRESET_TITLE'),
	        removeTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_PRESET'),
	        editPinTitle: this.parent.getParam('MAIN_UI_FILTER__SET_AS_DEFAULT_PRESET'),
	        dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_TITLE')
	      });
	    }
	    /**
	     * Highlights preset node as active
	     * @param {?HTMLElement|string} preset - preset node or preset id
	     */

	  }, {
	    key: "activatePreset",
	    value: function activatePreset(preset) {
	      var _this2 = this;

	      this.deactivateAllPresets();

	      var presetNode = function () {
	        if (main_core.Type.isString(preset)) {
	          return _this2.getPresetNodeById(preset);
	        }

	        return preset;
	      }();

	      if (main_core.Type.isDomNode(presetNode)) {
	        main_core.Dom.addClass(presetNode, 'main-ui-filter-current-item');
	      }
	    }
	    /**
	     * Gets preset node by preset id
	     * @param {string} id
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getPresetNodeById",
	    value: function getPresetNodeById(id) {
	      return this.getPresets().find(function (current) {
	        return main_core.Dom.attr(current, 'data-id') === id;
	      });
	    }
	    /**
	     * Gets preset id by preset node
	     * @param {?HTMLElement} preset
	     */

	  }, {
	    key: "getPresetId",
	    value: function getPresetId(preset) {
	      return main_core.Dom.attr(preset, 'data-id');
	    }
	    /**
	     * Updates preset name
	     * @param {?HTMLElement} presetNode
	     * @param {string} name
	     */

	  }, {
	    key: "updatePresetName",
	    value: function updatePresetName(presetNode, name) {
	      if (main_core.Type.isDomNode(presetNode) && main_core.Type.isString(name) && name !== '') {
	        var nameNode = this.getPresetNameNode(presetNode);

	        if (main_core.Type.isDomNode(nameNode)) {
	          main_core.Runtime.html(nameNode, name);
	        }
	      }
	    }
	    /**
	     * Removes preset
	     * @param {HTMLElement} presetNode
	     * @param {string} presetId
	     * @param {boolean} isDefault
	     */

	  }, {
	    key: "removePreset",
	    value: function removePreset(presetNode, presetId, isDefault) {
	      var currentPresetId = this.getCurrentPresetId();
	      var newPresets = [];
	      var postData = {
	        preset_id: presetId,
	        is_default: isDefault
	      };
	      var getData = {
	        FILTER_ID: this.parent.getParam('FILTER_ID'),
	        action: 'REMOVE_FILTER'
	      };
	      this.parent.saveOptions(postData, getData);
	      BX.remove(presetNode);

	      if (BX.type.isArray(this.parent.params.PRESETS)) {
	        newPresets = this.parent.params.PRESETS.filter(function (current) {
	          return current.ID !== presetId;
	        }, this);
	        this.parent.params.PRESETS = newPresets;
	      }

	      if (BX.type.isArray(this.parent.editablePresets)) {
	        newPresets = this.parent.editablePresets.filter(function (current) {
	          return current.ID !== presetId;
	        }, this);
	        this.parent.editablePresets = newPresets;
	      }

	      if (presetId === currentPresetId) {
	        this.parent.getSearch().removePreset();
	        this.resetPreset();
	      }
	    }
	    /**
	     * Pin preset (Sets as default preset)
	     * @param {string} presetId
	     */

	  }, {
	    key: "pinPreset",
	    value: function pinPreset(presetId) {
	      if (!BX.type.isNotEmptyString(presetId)) {
	        presetId = 'default_filter';
	      }

	      var presetNode = this.getPresetNodeById(presetId);

	      if (this.parent.getParam('VALUE_REQUIRED_MODE')) {
	        if (presetId === 'default_filter') {
	          return;
	        }
	      }

	      var params = {
	        FILTER_ID: this.parent.getParam('FILTER_ID'),
	        GRID_ID: this.parent.getParam('GRID_ID'),
	        action: 'PIN_PRESET'
	      };
	      var data = {
	        preset_id: presetId
	      };
	      this.getPresets().forEach(function (current) {
	        main_core.Dom.removeClass(current, this.parent.settings.classPinnedPreset);
	      }, this);
	      BX.addClass(presetNode, this.parent.settings.classPinnedPreset);
	      this.parent.saveOptions(data, params);
	    }
	  }, {
	    key: "_onPresetClick",
	    value: function _onPresetClick(event) {
	      var presetNode;
	      var presetId;
	      var presetData;
	      var isDefault;
	      var target;
	      var settings;
	      var parent;
	      event.preventDefault();
	      parent = this.parent;
	      settings = parent.settings;
	      target = event.target;
	      presetNode = event.currentTarget;
	      presetId = this.getPresetId(presetNode);
	      presetData = this.getPreset(presetId);

	      if (main_core.Dom.hasClass(target, settings.classPinButton)) {
	        if (this.parent.isEditEnabled()) {
	          if (main_core.Dom.hasClass(presetNode, settings.classPinnedPreset)) {
	            this.pinPreset('default_filter');
	          } else {
	            this.pinPreset(presetId);
	          }
	        }
	      }

	      if (main_core.Dom.hasClass(target, settings.classPresetEditButton)) {
	        this.enableEditPresetName(presetNode);
	      }

	      if (main_core.Dom.hasClass(target, settings.classPresetDeleteButton)) {
	        isDefault = 'IS_DEFAULT' in presetData ? presetData.IS_DEFAULT : false;
	        this.removePreset(presetNode, presetId, isDefault);
	        return false;
	      }

	      if (!main_core.Dom.hasClass(target, settings.classPresetDragButton) && !main_core.Dom.hasClass(target, settings.classAddPresetFieldInput)) {
	        if (this.parent.isEditEnabled()) {
	          this.updateEditablePreset(this.getCurrentPresetId());
	        }

	        var currentPreset = this.getPreset(this.getCurrentPresetId());
	        var preset = this.getPreset(presetId);
	        currentPreset.ADDITIONAL = [];
	        preset.ADDITIONAL = [];
	        this.activatePreset(presetNode);
	        this.applyPreset(presetId);

	        if (!this.parent.isEditEnabled()) {
	          parent.applyFilter(null, true);

	          if (event.isTrusted) {
	            parent.closePopup();
	          }

	          if (parent.isAddPresetEnabled()) {
	            parent.disableAddPreset();
	          }
	        }
	      }
	    }
	    /**
	     * Applies default preset
	     * @return {BX.Promise}
	     */

	  }, {
	    key: "applyPinnedPreset",
	    value: function applyPinnedPreset() {
	      var Filter = this.parent;
	      var isPinned = this.isPinned(this.getCurrentPresetId());
	      var promise;

	      if (this.parent.getParam('VALUE_REQUIRED') && this.getPinnedPresetId() === 'default_filter') {
	        this.applyPreset('default_filter');
	        this.deactivateAllPresets();
	        promise = this.parent.applyFilter();
	      } else if (!isPinned) {
	        var pinnedPresetId = this.getPinnedPresetId();
	        var pinnedPresetNode = this.getPinnedPresetNode();
	        var clear = false;
	        var applyPreset = true;
	        this.deactivateAllPresets();
	        this.activatePreset(pinnedPresetNode);
	        this.applyPreset(pinnedPresetId);
	        promise = Filter.applyFilter(clear, applyPreset);
	        Filter.closePopup();
	      } else {
	        promise = Filter.resetFilter();
	      }

	      return promise;
	    }
	    /**
	     * Updates editable presets
	     * @param {string} presetId
	     */

	  }, {
	    key: "updateEditablePreset",
	    value: function updateEditablePreset(presetId) {
	      var fields = this.parent.getFilterFieldsValues();
	      var presetRows = this.getFields().map(function (curr) {
	        return BX.data(curr, 'name');
	      });
	      var presetFields = this.parent.preparePresetFields(fields, presetRows);
	      var preset = this.getPreset(presetId);
	      preset.FIELDS = presetFields;
	      preset.TITLE = this.getPresetInput(this.getPresetNodeById(presetId)).value;
	      preset.ROWS = presetRows;
	    }
	    /**
	     * Gets preset input node
	     * @param presetNode
	     * @return {?HTMLInputElement}
	     */

	  }, {
	    key: "getPresetInput",
	    value: function getPresetInput(presetNode) {
	      return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetEditInput);
	    }
	    /**
	     * Enable edit preset name
	     * @param {HTMLElement} presetNode
	     */

	  }, {
	    key: "enableEditPresetName",
	    value: function enableEditPresetName(presetNode) {
	      var input = this.getPresetInput(presetNode);
	      BX.addClass(presetNode, this.parent.settings.classPresetNameEdit);
	      input.focus(); // noinspection SillyAssignmentJS

	      input.value = BX.util.htmlspecialcharsback(input.value);
	      main_core.Event.bind(input, 'input', BX.delegate(this._onPresetNameInput, this));
	    }
	  }, {
	    key: "_onPresetNameInput",
	    value: function _onPresetNameInput(event) {
	      var Search = this.parent.getSearch();
	      var inputValue = event.currentTarget.value;
	      var presetNode = BX.findParent(event.currentTarget, {
	        className: this.parent.settings.classPreset
	      }, true, false);
	      var presetId = this.getPresetId(presetNode);
	      var currentPresetId = this.getCurrentPresetId();
	      var data = {
	        ID: presetId,
	        TITLE: inputValue
	      };

	      if (presetId === currentPresetId) {
	        Search.updatePreset(data);
	      }
	    }
	    /**
	     * Gets preset name node element
	     * @param {HTMLElement} presetNode
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getPresetNameNode",
	    value: function getPresetNameNode(presetNode) {
	      return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetName);
	    }
	    /**
	     * Disable edit name for preset
	     * @param {HTMLElement} presetNode
	     */

	  }, {
	    key: "disableEditPresetName",
	    value: function disableEditPresetName(presetNode) {
	      var input = this.getPresetInput(presetNode);
	      main_core.Dom.removeClass(presetNode, this.parent.settings.classPresetNameEdit);

	      if (BX.type.isDomNode(input)) {
	        input.blur();
	        BX.unbind(input, 'input', BX.delegate(this._onPresetNameInput, this));
	      }
	    }
	    /**
	     * Gets preset object
	     * @param {string} presetId
	     * @param {boolean} [isDefault = false] - gets from default presets collection
	     * @return {?object}
	     */

	  }, {
	    key: "getPreset",
	    value: function getPreset(presetId, isDefault) {
	      var presets = this.parent.getParam(isDefault ? 'DEFAULT_PRESETS' : 'PRESETS', []);

	      if (this.parent.isEditEnabled() && !isDefault) {
	        presets = this.parent.editablePresets;
	      }

	      var filtered = presets.filter(function (current) {
	        return current.ID === presetId;
	      });

	      if (presetId === 'tmp_filter' && !filtered.length) {
	        var tmpPreset = BX.clone(this.getPreset('default_filter'));
	        tmpPreset.ID = 'tmp_filter';
	        presets.push(tmpPreset);
	        filtered.push(tmpPreset);
	      }

	      return filtered.length !== 0 ? filtered[0] : null;
	    }
	    /**
	     * Gets preset field by preset name (id)
	     * @param {string} presetId
	     * @param {string} fieldName
	     * @return {?object}
	     */

	  }, {
	    key: "getPresetField",
	    value: function getPresetField(presetId, fieldName) {
	      var preset = this.getPreset(presetId);
	      var field = null;

	      if (BX.type.isPlainObject(preset) && 'FIELDS' in preset && BX.type.isArray(preset.FIELDS)) {
	        field = preset.FIELDS.filter(function (current) {
	          return current.NAME === fieldName;
	        });
	        field = field.length ? field[0] : null;
	      }

	      return field;
	    }
	    /**
	     * Applies preset by id
	     * @param {string} presetId
	     * @param {boolean} [noValues = false]
	     */

	  }, {
	    key: "applyPreset",
	    value: function applyPreset(presetId, noValues) {
	      presetId = noValues ? 'default_filter' : presetId || 'default_filter';
	      var preset = this.getPreset(presetId);

	      if (presetId !== 'default_preset') {
	        preset = this.extendPreset(preset);
	      }

	      this.parent.getSearch().updatePreset(preset);
	      this.updatePresetFields(preset, noValues);
	    }
	    /**
	     * Extends preset
	     * @param {object} preset
	     * @return {object}
	     */

	  }, {
	    key: "extendPreset",
	    value: function extendPreset(preset) {
	      var defaultPreset = BX.clone(this.getPreset('default_filter'));

	      if (BX.type.isPlainObject(preset)) {
	        preset = BX.clone(preset);
	        preset.FIELDS.forEach(function (curr) {
	          var index;
	          var someField = defaultPreset.FIELDS.some(function (defCurr, defIndex) {
	            var result = false;

	            if (defCurr.NAME === curr.NAME) {
	              index = defIndex;
	              result = true;
	            }

	            return result;
	          }, this);

	          if (someField && index || someField && index === 0) {
	            defaultPreset.FIELDS[index] = curr;
	          } else if (!this.isEmptyField(curr)) {
	            defaultPreset.FIELDS.push(curr);
	          }
	        }, this);
	        preset.FIELDS = defaultPreset.FIELDS;
	      }

	      return preset;
	    }
	    /**
	     * Checks field is empty
	     * @param {object} field
	     * @return {boolean}
	     */

	  }, {
	    key: "isEmptyField",
	    value: function isEmptyField(field) {
	      var result = true;

	      if (main_core.Type.isStringFilled(field.ADDITIONAL_FILTER)) {
	        return false;
	      }

	      if (field.TYPE === this.parent.types.STRING) {
	        if (field.VALUE && field.VALUE.length) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.SELECT) {
	        if (BX.type.isPlainObject(field.VALUE) && 'VALUE' in field.VALUE && field.VALUE.VALUE) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.MULTI_SELECT) {
	        if (BX.type.isArray(field.VALUE) && field.VALUE.length) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.CUSTOM_DATE) {
	        if (BX.type.isArray(field.VALUE.days) && field.VALUE.days.length || BX.type.isArray(field.VALUE.months) && field.VALUE.months.length || BX.type.isArray(field.VALUE.years) && field.VALUE.years.length) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.CUSTOM_ENTITY || field.TYPE === this.parent.types.DEST_SELECTOR || field.TYPE === this.parent.types.ENTITY_SELECTOR) {
	        if (BX.type.isPlainObject(field.VALUES)) {
	          if (BX.type.isNotEmptyString(field.VALUES._label) && BX.type.isNotEmptyString(field.VALUES._value)) {
	            result = false;
	          }

	          if (BX.type.isPlainObject(field.VALUES._label) && BX.type.isPlainObject(field.VALUES._value) && Object.keys(field.VALUES._label).length && Object.keys(field.VALUES._value).length) {
	            result = false;
	          }

	          if (BX.type.isArray(field.VALUES._label) && BX.type.isArray(field.VALUES._value) && field.VALUES._label.length && field.VALUES._value.length) {
	            result = false;
	          }

	          if ((BX.type.isArray(field.VALUES._label) && field.VALUES._label.length || BX.type.isPlainObject(field.VALUES._label) && Object.keys(field.VALUES._label).length) && (BX.type.isArray(field.VALUES._value) && field.VALUES._value.length || BX.type.isPlainObject(field.VALUES._value) && Object.keys(field.VALUES._value).length)) {
	            result = false;
	          }
	        }
	      }

	      if (field.TYPE === this.parent.types.DATE) {
	        var datesel = '_datesel' in field.VALUES ? field.VALUES._datesel : field.SUB_TYPE.VALUE;

	        if (BX.type.isPlainObject(field.VALUES) && (field.VALUES._from || field.VALUES._to || field.VALUES._quarter || field.VALUES._month && !BX.type.isArray(field.VALUES._month) || field.VALUES._year && !BX.type.isArray(field.VALUES._year) || field.VALUES._days && !BX.type.isArray(field.VALUES._days)) || BX.type.isArray(field.VALUES._days) && field.VALUES._days.length || BX.type.isArray(field.VALUES._month) && field.VALUES._month.length || BX.type.isArray(field.VALUES._year) && field.VALUES._year.length || datesel === this.parent.dateTypes.CURRENT_DAY || datesel === this.parent.dateTypes.CURRENT_WEEK || datesel === this.parent.dateTypes.CURRENT_MONTH || datesel === this.parent.dateTypes.CURRENT_QUARTER || datesel === this.parent.dateTypes.LAST_7_DAYS || datesel === this.parent.dateTypes.LAST_30_DAYS || datesel === this.parent.dateTypes.LAST_60_DAYS || datesel === this.parent.dateTypes.LAST_90_DAYS || datesel === this.parent.dateTypes.LAST_WEEK || datesel === this.parent.dateTypes.LAST_MONTH || datesel === this.parent.dateTypes.TOMORROW || datesel === this.parent.dateTypes.YESTERDAY || datesel === this.parent.dateTypes.NEXT_WEEK || datesel === this.parent.dateTypes.NEXT_MONTH) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.NUMBER) {
	        if (BX.type.isPlainObject(field.VALUES) && (field.VALUES._from || field.VALUES._to)) {
	          result = false;
	        }
	      }

	      if (field.TYPE === this.parent.types.CHECKBOX) {
	        if (BX.type.isPlainObject(field.VALUE) && field.VALUE.VALUE) {
	          result = false;
	        }
	      }

	      return result;
	    }
	    /**
	     * Resets preset
	     * @param {boolean} [noValues]
	     */

	  }, {
	    key: "resetPreset",
	    value: function resetPreset(noValues) {
	      this.applyPreset('', noValues);
	    }
	    /**
	     * Gets preset fields elements
	     * @return {?HTMLElement[]}
	     */

	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var container = this.parent.getFieldListContainer();
	      var fields = null;

	      if (BX.type.isDomNode(container)) {
	        fields = BX.Filter.Utils.getBySelector(container.parentNode, ".".concat(this.parent.settings.classFileldControlList, " > div"), true);
	      }

	      return fields;
	    }
	    /**
	     * Gets field element by field object
	     * @param {object} fieldData
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getField",
	    value: function getField(fieldData) {
	      var fields = this.getFields();
	      var field = null;
	      var tmpName;
	      var filtered;

	      if (BX.type.isArray(fields) && fields.length) {
	        filtered = fields.filter(function (current) {
	          if (BX.type.isDomNode(current)) {
	            tmpName = BX.data(current, 'name');
	          }

	          return tmpName === fieldData.NAME;
	        }, this);
	        field = filtered.length > 0 ? filtered[0] : null;
	      }

	      return field;
	    }
	    /**
	     * Removes field element by field object
	     * @param {object} field
	     * @param {boolean} disableSaveFieldsSort
	     */

	  }, {
	    key: "removeField",
	    value: function removeField(field, disableSaveFieldsSort) {
	      var index;
	      var fieldName;
	      disableSaveFieldsSort = disableSaveFieldsSort || false;

	      if (BX.type.isPlainObject(field)) {
	        fieldName = field.NAME;
	        field = this.getField(field);

	        if (BX.type.isArray(this.parent.fieldsList)) {
	          index = this.parent.fieldsList.indexOf(field);

	          if (index !== -1) {
	            delete this.parent.fieldsList[index];
	          }
	        }

	        this.parent.unregisterDragItem(field);
	      }

	      if (BX.type.isDomNode(field)) {
	        fieldName = BX.data(field, 'name');
	        this.parent.getFields().deleteField(field);
	      }

	      if (!this.parent.isEditEnabled() && !this.parent.isAddPresetEnabled()) {
	        var currentPresetId = this.getCurrentPresetId();
	        var currentPresetField = this.getPresetField(currentPresetId, fieldName);

	        if (currentPresetField && !this.isEmptyField(currentPresetField)) {
	          this.deactivateAllPresets();
	          this.parent.applyFilter();
	        }
	      }

	      if (!disableSaveFieldsSort) {
	        this.parent.saveFieldsSort();
	      }
	    }
	    /**
	     * Removes field elements by field objects.
	     * @param {object[]} fields
	     */

	  }, {
	    key: "removeFields",
	    value: function removeFields(fields) {
	      fields.forEach(function (field) {
	        this.removeField(field, true);
	      }, this);
	      this.parent.saveFieldsSort();
	    }
	    /**
	     * Adds field into filter field list by field object
	     * @param {object} fieldData
	     */

	  }, {
	    key: "addField",
	    value: function addField(fieldData) {
	      var container;
	      var control;
	      var controls;

	      if (BX.type.isPlainObject(fieldData)) {
	        container = this.parent.getFieldListContainer();
	        controls = this.parent.getControls();
	        control = BX.type.isArray(controls) ? controls[controls.length - 1] : null;

	        if (BX.type.isDomNode(control)) {
	          if (control.nodeName !== 'INPUT') {
	            control = BX.Filter.Utils.getByTag(control, 'input');
	          }

	          if (BX.type.isDomNode(control)) {
	            fieldData.TABINDEX = parseInt(control.getAttribute('tabindex')) + 1;
	          }
	        } else {
	          fieldData.TABINDEX = 2;
	        }

	        if (BX.type.isDomNode(container)) {
	          control = this.createControl(fieldData);

	          if (BX.type.isDomNode(control)) {
	            BX.append(control, container);

	            if (BX.type.isArray(this.parent.fieldsList)) {
	              this.parent.fieldsList.push(control);
	            }

	            this.parent.registerDragItem(control);
	          }
	        }
	      }

	      if (!this.parent.isEditEnabled() && !this.parent.isAddPresetEnabled()) {
	        var currentPresetId = this.getCurrentPresetId();
	        var currentPresetField = this.getPresetField(currentPresetId, fieldData.NAME);

	        if (currentPresetField && !this.isEmptyField(currentPresetField)) {
	          this.parent.updatePreset('tmp_filter');
	          this.deactivateAllPresets();
	          this.parent.getSearch().updatePreset(this.getPreset('tmp_filter'));
	        }
	      }

	      this.parent.saveFieldsSort();
	    }
	    /**
	     * Creates field control by field object
	     * @param {object} fieldData
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "createControl",
	    value: function createControl(fieldData) {
	      var control;

	      switch (fieldData.TYPE) {
	        case this.parent.types.STRING:
	          {
	            control = this.parent.getFields().createInputText(fieldData);
	            break;
	          }

	        case this.parent.types.TEXTAREA:
	          {
	            control = this.parent.getFields().createTextarea(fieldData);
	            break;
	          }

	        case this.parent.types.SELECT:
	          {
	            control = this.parent.getFields().createSelect(fieldData);
	            break;
	          }

	        case this.parent.types.MULTI_SELECT:
	          {
	            control = this.parent.getFields().createMultiSelect(fieldData);
	            break;
	          }

	        case this.parent.types.NUMBER:
	          {
	            control = this.parent.getFields().createNumber(fieldData);
	            break;
	          }

	        case this.parent.types.DATE:
	          {
	            control = this.parent.getFields().createDate(fieldData);
	            break;
	          }

	        case this.parent.types.CUSTOM_DATE:
	          {
	            control = this.parent.getFields().createCustomDate(fieldData);
	            break;
	          }

	        case this.parent.types.DEST_SELECTOR:
	          {
	            control = this.parent.getFields().createDestSelector(fieldData);
	            break;
	          }

	        case this.parent.types.ENTITY_SELECTOR:
	          {
	            control = this.parent.getFields().createEntitySelector(fieldData);
	            break;
	          }

	        case this.parent.types.CUSTOM:
	          {
	            control = this.parent.getFields().createCustom(fieldData);
	            break;
	          }

	        case this.parent.types.CUSTOM_ENTITY:
	          {
	            control = this.parent.getFields().createCustomEntity(fieldData);
	            break;
	          }

	        default:
	          {
	            break;
	          }
	      }

	      if (this.parent.getParam('ENABLE_ADDITIONAL_FILTERS')) {
	        var additionalFilterInstance = AdditionalFilter.getInstance();
	        var button = additionalFilterInstance.getAdditionalFilterButton({
	          fieldId: fieldData.NAME,
	          enabled: fieldData.ADDITIONAL_FILTER_ALLOWED
	        });
	        main_core.Dom.append(button, control);

	        if (!fieldData.ADDITIONAL_FILTER_ALLOWED) {
	          BX.Dom.addClass(control, 'main-ui-filter-additional-filters-hide');
	        }

	        if (main_core.Type.isStringFilled(fieldData.ADDITIONAL_FILTER)) {
	          additionalFilterInstance.initAdditionalFilter(control, fieldData.ADDITIONAL_FILTER);
	        }
	      }

	      if (BX.type.isDomNode(control)) {
	        control.dataset.name = fieldData.NAME;
	        control.FieldController = new BX.Filter.FieldController(control, this.parent);

	        if (fieldData.REQUIRED) {
	          var removeButton = control.querySelector('.main-ui-filter-field-delete');

	          if (removeButton) {
	            BX.remove(removeButton);
	          }
	        }
	      }

	      return control;
	    }
	    /**
	     * Removes not compared properties
	     * @param {object} fields
	     * @param {boolean} [noClean]
	     */

	  }, {
	    key: "removeNotCompareVariables",
	    value: function removeNotCompareVariables(fields, noClean) {
	      if (BX.type.isPlainObject(fields)) {
	        var dateType = this.parent.dateTypes;
	        var additionalDateTypes = this.parent.additionalDateTypes;

	        if ('FIND' in fields) {
	          delete fields.FIND;
	        }

	        if (!noClean) {
	          Object.keys(fields).forEach(function (key) {
	            if (key.indexOf('_numsel') !== -1) {
	              delete fields[key];
	            }

	            if (key.indexOf('_datesel') !== -1) {
	              var datesel = fields[key];

	              if (datesel === dateType.EXACT || datesel === dateType.RANGE || datesel === additionalDateTypes.PREV_DAY || datesel === additionalDateTypes.NEXT_DAY || datesel === additionalDateTypes.MORE_THAN_DAYS_AGO || datesel === additionalDateTypes.AFTER_DAYS || datesel === dateType.PREV_DAYS || datesel === dateType.NEXT_DAYS || datesel === dateType.YEAR || datesel === dateType.MONTH || datesel === dateType.QUARTER || datesel === dateType.NONE || datesel === dateType.CUSTOM_DATE) {
	                delete fields[key];
	              }
	            }

	            var field = this.parent.getFieldByName(key);

	            if (fields[key] === '' && (!field || !field.STRICT)) {
	              delete fields[key];
	            }
	          }, this);
	        }
	      }
	    }
	    /**
	     * Checks is modified preset field values
	     * @param {string} presetId
	     * @returns {boolean}
	     */

	  }, {
	    key: "isPresetValuesModified",
	    value: function isPresetValuesModified(presetId) {
	      var currentPresetData = this.getPreset(presetId);
	      var presetFields = this.parent.preparePresetSettingsFields(currentPresetData.FIELDS);
	      var currentFields = this.parent.getFilterFieldsValues();
	      this.removeNotCompareVariables(presetFields);
	      this.removeNotCompareVariables(currentFields);
	      var comparedPresetFields = BX.Filter.Utils.sortObject(presetFields);
	      var comparedCurrentFields = BX.Filter.Utils.sortObject(currentFields);
	      return !Object.keys(comparedPresetFields).every(function (key) {
	        return comparedPresetFields[key] === comparedCurrentFields[key] || (BX.type.isPlainObject(comparedPresetFields[key]) || BX.type.isArray(comparedPresetFields[key])) && BX.Filter.Utils.objectsIsEquals(comparedPresetFields[key], comparedCurrentFields[key]);
	      });
	    }
	    /**
	     * Gets additional preset values
	     * @param {string} presetId
	     * @return {?object}
	     */

	  }, {
	    key: "getAdditionalValues",
	    value: function getAdditionalValues(presetId) {
	      var currentPresetData = this.getPreset(presetId);
	      var notEmptyFields = currentPresetData.FIELDS.filter(function (field) {
	        return !this.isEmptyField(field);
	      }, this);
	      var presetFields = this.parent.preparePresetSettingsFields(notEmptyFields);
	      var currentFields = this.parent.getFilterFieldsValues();
	      this.removeNotCompareVariables(presetFields, true);
	      this.removeNotCompareVariables(currentFields, true);
	      this.removeSameProperties(currentFields, presetFields);
	      return currentFields;
	    }
	    /**
	     * Removes same object properties
	     * @param {object} object1
	     * @param {object} object2
	     */

	  }, {
	    key: "removeSameProperties",
	    value: function removeSameProperties(object1, object2) {
	      if (BX.type.isPlainObject(object1) && BX.type.isPlainObject(object2)) {
	        Object.keys(object2).forEach(function (key) {
	          if (key in object1) {
	            delete object1[key];
	          }
	        });
	      }
	    }
	    /**
	     * Removes additional field by field name
	     * @param {string} name
	     */

	  }, {
	    key: "removeAdditionalField",
	    value: function removeAdditionalField(name) {
	      var preset = this.getPreset(this.getCurrentPresetId());

	      if (BX.type.isArray(preset.ADDITIONAL)) {
	        preset.ADDITIONAL = preset.ADDITIONAL.filter(function (field) {
	          return field.NAME !== name;
	        });
	      }
	    }
	    /**
	     * Updates preset fields list
	     * @param {object} preset
	     * @param {boolean} [noValues = false]
	     */

	  }, {
	    key: "updatePresetFields",
	    value: function updatePresetFields(preset, noValues) {
	      var _this3 = this;

	      var fields;
	      var fieldListContainer;
	      var fieldNodes = [];

	      if (BX.type.isPlainObject(preset) && 'FIELDS' in preset) {
	        fields = preset.FIELDS;

	        if (BX.type.isArray(preset.ADDITIONAL)) {
	          preset.ADDITIONAL.filter(function (field) {
	            return _this3.parent.params.FIELDS.some(function (currentField) {
	              return field.NAME === currentField.NAME;
	            });
	          }).forEach(function (field) {
	            var replaced = false;
	            field.IS_PRESET_FIELD = true;
	            fields.forEach(function (presetField, index) {
	              if (field.NAME === presetField.NAME) {
	                fields[index] = field;
	                replaced = true;
	              }
	            });

	            if (!replaced) {
	              fields.push(field);
	            }
	          });
	        }

	        (fields || []).filter(function (field) {
	          return _this3.parent.params.FIELDS.some(function (currentField) {
	            return field.NAME === currentField.NAME;
	          });
	        }).forEach(function (fieldData, index) {
	          fieldData.TABINDEX = index + 1;

	          if (noValues) {
	            switch (fieldData.TYPE) {
	              case this.parent.types.SELECT:
	                {
	                  fieldData.VALUE = fieldData.ITEMS[0];
	                  break;
	                }

	              case this.parent.types.MULTI_SELECT:
	                {
	                  fieldData.VALUE = [];
	                  break;
	                }

	              case this.parent.types.DATE:
	                {
	                  fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
	                  fieldData.VALUES = {
	                    _from: '',
	                    _to: '',
	                    _days: ''
	                  };
	                  break;
	                }

	              case this.parent.types.CUSTOM_DATE:
	                {
	                  fieldData.VALUE = {
	                    days: [],
	                    months: [],
	                    years: []
	                  };
	                  break;
	                }

	              case this.parent.types.NUMBER:
	                {
	                  fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
	                  fieldData.VALUES = {
	                    _from: '',
	                    _to: ''
	                  };
	                  break;
	                }

	              case this.parent.types.CUSTOM_ENTITY:
	                {
	                  fieldData.VALUES = {
	                    _label: '',
	                    _value: ''
	                  };
	                  break;
	                }

	              case this.parent.types.CUSTOM:
	                {
	                  fieldData._VALUE = '';
	                  break;
	                }

	              default:
	                {
	                  if ('VALUE' in fieldData) {
	                    if (BX.type.isArray(fieldData.VALUE)) {
	                      fieldData.VALUE = [];
	                    } else {
	                      fieldData.VALUE = '';
	                    }
	                  }

	                  break;
	                }
	            }
	          }

	          fieldNodes.push(this.createControl(fieldData));
	        }, this);
	        this.parent.disableFieldsDragAndDrop();
	        fieldListContainer = this.parent.getFieldListContainer();
	        BX.cleanNode(fieldListContainer);

	        if (fieldNodes.length) {
	          fieldNodes.forEach(function (current, index) {
	            if (BX.type.isDomNode(current)) {
	              if (preset.ID !== 'tmp_filter' && preset.ID !== 'default_filter' && !('IS_PRESET_FIELD' in fields[index]) && !this.isEmptyField(fields[index])) {
	                BX.addClass(current, this.parent.settings.classPresetField);
	              }

	              BX.append(current, fieldListContainer);

	              if (BX.type.isString(fields[index].HTML)) {
	                var wrap = BX.create('div');
	                this.parent.getHiddenElement().appendChild(wrap);
	                BX.html(wrap, fields[index].HTML);
	              }
	            }
	          }, this);
	          this.parent.enableFieldsDragAndDrop();
	        }
	      }
	    }
	    /**
	     * Shows current preset fields
	     */

	  }, {
	    key: "showCurrentPresetFields",
	    value: function showCurrentPresetFields() {
	      var preset = this.getCurrentPresetData();
	      this.updatePresetFields(preset);
	    }
	    /**
	     * Gets current preset element
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getCurrentPreset",
	    value: function getCurrentPreset() {
	      return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPresetCurrent);
	    }
	    /**
	     * Gets current preset id
	     * @return {*}
	     */

	  }, {
	    key: "getCurrentPresetId",
	    value: function getCurrentPresetId() {
	      var current = this.getCurrentPreset();
	      var currentId = null;

	      if (BX.type.isDomNode(current)) {
	        currentId = this.getPresetId(current);
	      } else {
	        currentId = 'tmp_filter';
	      }

	      return currentId;
	    }
	    /**
	     * Gets current preset data
	     * @return {?object}
	     */

	  }, {
	    key: "getCurrentPresetData",
	    value: function getCurrentPresetData() {
	      var currentId = this.getCurrentPresetId();
	      var currentData = null;

	      if (BX.type.isNotEmptyString(currentId)) {
	        currentData = this.getPreset(currentId);
	        currentData = this.extendPreset(currentData);
	      }

	      return currentData;
	    }
	    /**
	     * Gets presets container element
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return BX.Filter.Utils.getByClass(this.parent.getFilter(), this.parent.settings.classPresetsContainer);
	    }
	    /**
	     * Gets preset nodes
	     * @return {?HTMLElement[]}
	     */

	  }, {
	    key: "getPresets",
	    value: function getPresets() {
	      return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPreset, true);
	    }
	    /**
	     * Gets default presets elements
	     * @return {?HTMLElement[]}
	     */

	  }, {
	    key: "getDefaultPresets",
	    value: function getDefaultPresets() {
	      return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classDefaultFilter, true);
	    }
	    /**
	     * Gets default preset element
	     * @return {?HTMLElement}
	     */

	  }, {
	    key: "getPinnedPresetNode",
	    value: function getPinnedPresetNode() {
	      return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPinnedPreset);
	    }
	    /**
	     * Checks preset is pinned (default)
	     * @param presetId
	     * @return {boolean}
	     */

	  }, {
	    key: "isPinned",
	    value: function isPinned(presetId) {
	      return this.getPinnedPresetId() === presetId;
	    }
	    /**
	     * Gets pinned (default) preset id
	     * @return {string}
	     */

	  }, {
	    key: "getPinnedPresetId",
	    value: function getPinnedPresetId() {
	      var node = this.getPinnedPresetNode();
	      var id = 'default_filter';

	      if (node) {
	        var dataId = BX.data(node, 'id');
	        id = dataId || id;
	      }

	      return id;
	    }
	  }]);
	  return Presets;
	}();

	exports.Field = Field;
	exports.Api = Api;
	exports.Fields = Fields;
	exports.Presets = Presets;
	exports.AdditionalFilter = AdditionalFilter;

}((this.BX.Filter = this.BX.Filter || {}),BX.UI.EntitySelector,BX.Event,BX.Main,BX));
//# sourceMappingURL=script.js.map
