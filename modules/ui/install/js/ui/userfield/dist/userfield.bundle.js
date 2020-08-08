this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var UserField =
	/*#__PURE__*/
	function () {
	  function UserField(data, params) {
	    babelHelpers.classCallCheck(this, UserField);
	    this.progress = false;
	    this.deleted = false;
	    this.setData(data);

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.languageId)) {
	        this.setLanguageId(params.languageId);
	      }

	      this.moduleId = params.moduleId;
	    }

	    this.actions = {
	      get: 'main.userFieldConfig.get',
	      add: 'main.userFieldConfig.add',
	      update: 'main.userFieldConfig.update',
	      delete: 'main.userFieldConfig.delete'
	    };
	  }

	  babelHelpers.createClass(UserField, [{
	    key: "load",
	    value: function load() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        var errors = [];

	        if (_this.progress) {
	          errors.push('Another action is in progress');
	          reject(errors);
	          return;
	        }

	        if (!_this.isSaved()) {
	          errors.push('Cant load UserField without id');
	          reject(errors);
	          return;
	        }

	        var action = _this.actions.get;

	        if (!main_core.Type.isString(action) || action.length <= 0) {
	          errors.push('UserField load action is not specified');
	          reject(errors);
	          return;
	        }

	        _this.progress = true;
	        main_core.ajax.runAction(action, {
	          data: {
	            id: _this.getId(),
	            moduleId: _this.moduleId
	          }
	        }).then(function (response) {
	          _this.progress = false;

	          _this.setData(response.data.field);

	          resolve(response);
	        }).catch(function (response) {
	          _this.progress = false;
	          response.errors.forEach(function (_ref) {
	            var message = _ref.message;
	            errors.push(message);
	          });
	          reject(errors);
	        });
	      });
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        var errors = [];

	        if (_this2.progress) {
	          errors.push('Another action is in progress');
	          reject(errors);
	          return;
	        }

	        var action;
	        var data;

	        if (_this2.isSaved()) {
	          action = _this2.actions.update;
	          data = {
	            id: _this2.getId(),
	            field: _this2.getData(),
	            moduleId: _this2.moduleId
	          };
	        } else {
	          action = _this2.actions.add;
	          data = {
	            field: _this2.getData(),
	            moduleId: _this2.moduleId
	          };
	        }

	        if (!main_core.Type.isString(action) || action.length <= 0) {
	          errors.push('UserField action is not specified');
	          reject(errors);
	          return;
	        }

	        _this2.progress = true;
	        main_core.ajax.runAction(action, {
	          data: data
	        }).then(function (response) {
	          _this2.progress = false;

	          _this2.setData(response.data.field);

	          resolve(response);
	        }).catch(function (response) {
	          _this2.progress = false;
	          response.errors.forEach(function (_ref2) {
	            var message = _ref2.message;

	            if (main_core.Type.isPlainObject(message) && message.text) {
	              errors.push(message.text);
	            } else {
	              errors.push(message);
	            }
	          });
	          reject(errors);
	        });
	      });
	    }
	  }, {
	    key: "delete",
	    value: function _delete() {
	      var _this3 = this;

	      return new Promise(function (resolve, reject) {
	        var errors = [];

	        if (_this3.progress) {
	          errors.push('Another action is in progress');
	          reject(errors);
	          return;
	        }

	        if (!_this3.isSaved()) {
	          errors.push('Cant delete UserField without id');
	          reject(errors);
	          return;
	        }

	        var action = _this3.actions.delete;

	        if (!main_core.Type.isString(action) || action.length <= 0) {
	          errors.push('UserField action is not specified');
	          reject(errors);
	          return;
	        }

	        _this3.progress = true;
	        main_core.ajax.runAction(action, {
	          data: {
	            id: _this3.getId(),
	            moduleId: _this3.moduleId
	          }
	        }).then(function () {
	          _this3.deleted = true;
	          _this3.progress = false;
	          resolve();
	        }).catch(function (response) {
	          _this3.progress = false;
	          response.errors.forEach(function (_ref3) {
	            var message = _ref3.message;
	            errors.push(message);
	          });
	          reject(errors);
	        });
	      });
	    }
	  }, {
	    key: "setLanguageId",
	    value: function setLanguageId(languageId) {
	      this.languageId = languageId;
	      return this;
	    }
	  }, {
	    key: "setModuleId",
	    value: function setModuleId(moduleId) {
	      this.moduleId = moduleId;
	      return this;
	    }
	  }, {
	    key: "getLanguageId",
	    value: function getLanguageId() {
	      if (!this.languageId) {
	        return main_core.Loc.getMessage('LANGUAGE_ID');
	      }

	      return this.languageId;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return main_core.Text.toInteger(this.data.id);
	    }
	  }, {
	    key: "isSaved",
	    value: function isSaved() {
	      return this.getId() > 0;
	    }
	  }, {
	    key: "setData",
	    value: function setData(data) {
	      this.data = data;
	      return this;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.data.fieldName;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (this.isSaved()) {
	        console.error('Changing name is not available on saved UserField');
	        return this;
	      }

	      this.data.fieldName = name;
	      return this;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.data.entityId;
	    }
	  }, {
	    key: "getUserTypeId",
	    value: function getUserTypeId() {
	      return this.data.userTypeId;
	    }
	  }, {
	    key: "setUserTypeId",
	    value: function setUserTypeId(userTypeId) {
	      if (this.isSaved()) {
	        console.error('Changing userTypeId is not available on saved UserField');
	        return this;
	      }

	      this.data.userTypeId = userTypeId;
	      return this;
	    }
	  }, {
	    key: "getEnumeration",
	    value: function getEnumeration() {
	      if (!main_core.Type.isArray(this.data.enum)) {
	        this.data.enum = [];
	      }

	      return this.data.enum;
	    }
	  }, {
	    key: "setEnumeration",
	    value: function setEnumeration(items) {
	      this.data.enum = items;
	      return this;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      var titleFieldNames = UserField.getTitleFieldNames();
	      var titleFieldsCount = titleFieldNames.length;
	      var languageId = this.getLanguageId();

	      for (var index = 0; index < titleFieldsCount; index++) {
	        if (this.data[titleFieldNames[index]] && main_core.Type.isString(this.data[titleFieldNames[index]][languageId]) && this.data[titleFieldNames[index]][languageId].length > 0) {
	          return this.data[titleFieldNames[index]][languageId];
	        }
	      }

	      return this.getName();
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title, languageId) {
	      if (main_core.Type.isString(title) && title.length > 0) {
	        if (!languageId) {
	          languageId = this.getLanguageId();
	        }

	        if (!this.data['editFormLabel']) {
	          this.data['editFormLabel'] = {};
	        }

	        this.data['editFormLabel'][languageId] = title;

	        if (this.getUserTypeId() === 'boolean') {
	          if (!this.data.settings) {
	            this.data.settings = {};
	          }

	          this.data.settings.LABEL_CHECKBOX = title;
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.data.multiple === 'Y';
	    }
	  }, {
	    key: "isMandatory",
	    value: function isMandatory() {
	      return this.data.mandatory === 'Y';
	    }
	  }, {
	    key: "setIsMandatory",
	    value: function setIsMandatory(mandatory) {
	      this.data.mandatory = main_core.Text.toBoolean(mandatory) ? 'Y' : 'N';
	    }
	  }, {
	    key: "setIsMultiple",
	    value: function setIsMultiple(isMultiple) {
	      if (this.isSaved()) {
	        console.error('Changing multiple is not available on saved UserField');
	        return this;
	      }

	      this.data.multiple = main_core.Text.toBoolean(isMultiple) === true ? 'Y' : 'N';
	      return this;
	    }
	  }, {
	    key: "getDetailUrl",
	    value: function getDetailUrl() {
	      return this.data.detailUrl;
	    }
	  }, {
	    key: "isDeleted",
	    value: function isDeleted() {
	      return this.deleted;
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return JSON.stringify({
	        data: this.data,
	        languageId: this.languageId,
	        moduleId: this.moduleId,
	        progress: this.progress,
	        deleted: this.deleted
	      });
	    }
	  }], [{
	    key: "getTitleFieldNames",
	    value: function getTitleFieldNames() {
	      return ['editFormLabel', 'listColumnLabel', 'listFilterLabel'];
	    }
	  }, {
	    key: "unserialize",
	    value: function unserialize(serializedData) {
	      var serializedUserField = JSON.parse(serializedData);
	      var userField = new UserField(serializedUserField.data, {
	        languageId: serializedUserField.languageId,
	        moduleId: serializedUserField.moduleId
	      });
	      userField.progress = serializedUserField.progress;
	      userField.deleted = serializedUserField.deleted;
	      return userField;
	    }
	  }]);
	  return UserField;
	}();

	exports.UserField = UserField;

}((this.BX.UI.UserField = this.BX.UI.UserField || {}),BX));
//# sourceMappingURL=userfield.bundle.js.map
