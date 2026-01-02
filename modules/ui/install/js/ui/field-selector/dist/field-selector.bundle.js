/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,ui_entitySelector,main_core) {
	'use strict';

	class ValueType {
	  static isValid(type) {
	    return type === ValueType.INTEGER || type === ValueType.STRING;
	  }
	}
	ValueType.INTEGER = 'int';
	ValueType.STRING = 'string';

	var _values = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("values");
	class BaseCollection {
	  constructor() {
	    Object.defineProperty(this, _values, {
	      writable: true,
	      value: []
	    });
	    this.clear();
	  }
	  clear() {
	    babelHelpers.classPrivateFieldLooseBase(this, _values)[_values] = [];
	  }
	  set(rawValues) {
	    babelHelpers.classPrivateFieldLooseBase(this, _values)[_values] = this.prepare(rawValues);
	  }
	  get() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _values)[_values];
	  }
	  prepare(rawValues) {
	    const result = [];
	    rawValues.forEach(value => {
	      var _value$, _value$2;
	      if (!main_core.Type.isArray(value) || value.length !== 2) {
	        return;
	      }
	      if (!this.validateEntityId((_value$ = value[0]) != null ? _value$ : null)) {
	        return;
	      }
	      if (!this.validateValue((_value$2 = value[1]) != null ? _value$2 : null)) {
	        return;
	      }
	      result.push(value);
	    });
	    return result;
	  }
	  validateEntityId(entityId) {
	    return main_core.Type.isStringFilled(entityId);
	  }
	  validateValue(value) {
	    return false;
	  }
	}

	class IntegerCollection extends BaseCollection {
	  validateValue(value) {
	    return main_core.Type.isInteger(value) && value > 0;
	  }
	}

	class StringCollection extends BaseCollection {
	  validateValue(value) {
	    return main_core.Type.isString(value) && value.length > 0;
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	var _state = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("state");
	var _containerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerId");
	var _fieldName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldName");
	var _multiple = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("multiple");
	var _valueCollection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("valueCollection");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	var _entities = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("entities");
	var _searchMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchMessages");
	var _changeEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changeEvents");
	class FieldSelector {
	  constructor(selectorConfig) {
	    Object.defineProperty(this, _state, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _containerId, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _fieldName, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _multiple, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _valueCollection, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _entities, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _searchMessages, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _changeEvents, {
	      writable: true,
	      value: []
	    });
	    const config = main_core.Type.isPlainObject(selectorConfig) ? selectorConfig : {};
	    this.initState();
	    this.setContainerId(config.containerId);
	    this.setFieldName(config.fieldName);
	    this.setMultiple(config.multiple);
	    this.setContext(config.context);
	    this.setEntities(config.entities);
	    this.initValueCollection(config.collectionType);
	    if (!this.isStateSuccess()) {
	      return;
	    }
	    this.setValues(main_core.Type.isArray(config.selectedItems) ? config.selectedItems : [config.selectedItems]);
	    this.setSearchMessages(config.searchMessages);
	    this.setChangeEvents(config.changeEvents);
	  }
	  initState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = true;
	  }
	  isStateSuccess() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _state)[_state];
	  }
	  showError(error) {
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = false;
	    if (main_core.Type.isStringFilled(error)) {
	      console.error(`BX.UI.FieldSelector: ${error}`);
	    }
	  }
	  setContainerId(containerId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _containerId)[_containerId] = main_core.Type.isStringFilled(containerId) ? containerId : '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _containerId)[_containerId] === '') {
	      this.showError('containerId is empty. Selector is can\'t be used');
	    }
	  }
	  getContainerId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _containerId)[_containerId];
	  }
	  setFieldName(fieldName) {
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName] = main_core.Type.isStringFilled(fieldName) ? fieldName : '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName] === '') {
	      this.showError('fieldName is empty. Selector is can\'t be used');
	    }
	  }
	  getFieldName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName];
	  }
	  setMultiple(multiple) {
	    babelHelpers.classPrivateFieldLooseBase(this, _multiple)[_multiple] = main_core.Type.isBoolean(multiple) ? multiple : false;
	  }
	  getMultiple() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _multiple)[_multiple];
	  }
	  getTagSelectorContainerId() {
	    return `${this.getContainerId()}_selector`;
	  }
	  getTagResultContainerId() {
	    return `${this.getContainerId()}_results`;
	  }
	  getTagSelectorControlId() {
	    return `${this.getContainerId()}Control`;
	  }
	  initValueCollection(valueType) {
	    if (!ValueType.isValid(valueType)) {
	      this.showError('Unknown value collection type. Selector is can\'t be used');
	      return;
	    }
	    switch (valueType) {
	      case ValueType.STRING:
	        babelHelpers.classPrivateFieldLooseBase(this, _valueCollection)[_valueCollection] = new StringCollection();
	        break;
	      case ValueType.INTEGER:
	        babelHelpers.classPrivateFieldLooseBase(this, _valueCollection)[_valueCollection] = new IntegerCollection();
	        break;
	      default:
	        break;
	    }
	  }
	  setValues(rawValues) {
	    babelHelpers.classPrivateFieldLooseBase(this, _valueCollection)[_valueCollection].set(rawValues);
	  }
	  getValues() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _valueCollection)[_valueCollection].get();
	  }
	  setContext(context) {
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = context;
	  }
	  getContext() {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context]) != null ? _babelHelpers$classPr : null;
	  }
	  setEntities(entities) {
	    babelHelpers.classPrivateFieldLooseBase(this, _entities)[_entities] = [];
	    if (!main_core.Type.isArrayFilled(entities)) {
	      this.showError('Entity list is empty. Selector is can\'t be used');
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _entities)[_entities] = entities;
	  }
	  getEntities() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _entities)[_entities];
	  }
	  setSearchMessages(messages) {
	    if (main_core.Type.isPlainObject(messages)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].title = main_core.Type.isStringFilled(messages.title) ? messages.title : '';
	      babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].subtitle = main_core.Type.isStringFilled(messages.subtitle) ? messages.subtitle : '';
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].title = '';
	      babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].subtitle = '';
	    }
	  }
	  getSearchTabTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].title;
	  }
	  getSearchSubtitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _searchMessages)[_searchMessages].subtitle;
	  }
	  setChangeEvents(events) {
	    babelHelpers.classPrivateFieldLooseBase(this, _changeEvents)[_changeEvents] = [];
	    if (main_core.Type.isArrayFilled(events)) {
	      events.forEach(value => {
	        if (main_core.Type.isStringFilled(value)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _changeEvents)[_changeEvents].push(value);
	        }
	      });
	    }
	  }
	  getChangeEvents() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _changeEvents)[_changeEvents];
	  }
	  render() {
	    if (!this.isStateSuccess()) {
	      return;
	    }
	    const containerId = this.getContainerId();
	    const container = document.getElementById(containerId);
	    if (!main_core.Type.isElementNode(container)) {
	      this.showError(`dom-container ${containerId} is absent. Selector is can't be used`);
	      return;
	    }
	    const tagSelectorContainer = main_core.Tag.render(_t || (_t = _`
			<div id="${0}"></div>
		`), this.getTagSelectorContainerId());
	    main_core.Dom.append(tagSelectorContainer, container);
	    const tagResult = main_core.Tag.render(_t2 || (_t2 = _`
			<div id="${0}"></div>
		`), this.getTagResultContainerId());
	    main_core.Dom.append(tagResult, container);
	    this.renderSelectedItems(this.getValues());
	    const tagSelectorConfig = {
	      id: this.getTagSelectorControlId(),
	      multiple: this.getMultiple(),
	      dialogOptions: {
	        id: this.getTagSelectorControlId(),
	        context: this.getContext(),
	        multiple: this.getMultiple(),
	        preselectedItems: this.getValues(),
	        entities: this.getEntities(),
	        searchOptions: {
	          allowCreateItem: false
	        },
	        searchTabOptions: {
	          stub: true,
	          stubOptions: {
	            title: main_core.Text.encode(this.getSearchTabTitle()),
	            subtitle: main_core.Text.encode(this.getSearchSubtitle()),
	            arrow: false
	          }
	        },
	        events: {
	          'Item:onSelect': this.updateSelectedItems.bind(this),
	          'Item:onDeselect': this.updateSelectedItems.bind(this)
	        }
	      }
	    };
	    const tagSelector = new ui_entitySelector.TagSelector(tagSelectorConfig);
	    tagSelector.renderTo(tagSelectorContainer);
	  }
	  renderSelectedItems(items) {
	    const tagResult = document.getElementById(this.getTagResultContainerId());
	    if (!main_core.Type.isDomNode(tagResult)) {
	      return;
	    }
	    const fieldName = this.getFieldName();
	    tagResult.innerHTML = '';
	    if (items.length > 0) {
	      items.forEach(value => {
	        const hiddenValue = main_core.Tag.render(_t3 || (_t3 = _`
					<input type="hidden" name="${0}" value="${0}">
				`), fieldName, main_core.Tag.safe(_t4 || (_t4 = _`${0}`), value[1].toString()));
	        main_core.Dom.append(hiddenValue, tagResult);
	      });
	    } else {
	      const emptyValue = main_core.Tag.render(_t5 || (_t5 = _`
				<input type="hidden" name="${0}" value="">
			`), fieldName);
	      main_core.Dom.append(emptyValue, tagResult);
	    }
	  }
	  updateSelectedItems(event) {
	    const dialog = event.getTarget();
	    if (!dialog.isMultiple()) {
	      dialog.hide();
	    }
	    const selectedItems = dialog.getSelectedItems();
	    if (main_core.Type.isArray(selectedItems)) {
	      const parsedValues = [];
	      selectedItems.forEach(item => {
	        parsedValues.push([item.getEntityId(), item.getId()]);
	      });
	      this.setValues(parsedValues);
	      this.renderSelectedItems(parsedValues);
	      const eventList = this.getChangeEvents();
	      eventList.forEach(changeEvent => {
	        main_core_events.EventEmitter.emit(changeEvent);
	      });
	    }
	  }
	}

	exports.FieldSelector = FieldSelector;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX.UI.EntitySelector,BX));
//# sourceMappingURL=field-selector.bundle.js.map
