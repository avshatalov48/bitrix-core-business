/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	class Collab {
	  constructor(data) {
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: null
	    });
	    this.updateData(data);
	  }
	  updateData(data) {
	    var _data$NAME;
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Text.toNumber(data.ID);
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = (_data$NAME = data.NAME) == null ? void 0 : _data$NAME.toString();
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	}

	class CollabManager {
	  constructor(data, config) {
	    let dataCollabs = data.collabs || [];
	    if (!dataCollabs.length) {
	      const extensionConfig = main_core.Extension.getSettings('calendar.collabmanager');
	      dataCollabs = extensionConfig.collabs || [];
	    }
	    this.updateCollabs(dataCollabs);
	  }
	  updateCollabs(collabs) {
	    this.collabs = collabs.map(c => new Collab(c));
	  }
	  getById(id) {
	    return this.collabs.find(c => c.getId() === main_core.Text.toNumber(id));
	  }
	}

	exports.CollabManager = CollabManager;

}((this.BX.Calendar = this.BX.Calendar || {}),BX));
//# sourceMappingURL=collabmanager.bundle.js.map
