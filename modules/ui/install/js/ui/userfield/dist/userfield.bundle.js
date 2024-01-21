/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	class UserField {
	  constructor(data, params) {
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
	  load() {
	    return new Promise((resolve, reject) => {
	      const errors = [];
	      if (this.progress) {
	        errors.push('Another action is in progress');
	        reject(errors);
	        return;
	      }
	      if (!this.isSaved()) {
	        errors.push('Cant load UserField without id');
	        reject(errors);
	        return;
	      }
	      const action = this.actions.get;
	      if (!main_core.Type.isString(action) || action.length <= 0) {
	        errors.push('UserField load action is not specified');
	        reject(errors);
	        return;
	      }
	      this.progress = true;
	      main_core.ajax.runAction(action, {
	        data: {
	          id: this.getId(),
	          moduleId: this.moduleId
	        }
	      }).then(response => {
	        this.progress = false;
	        this.setData(response.data.field);
	        resolve(response);
	      }).catch(response => {
	        this.progress = false;
	        response.errors.forEach(({
	          message
	        }) => {
	          errors.push(message);
	        });
	        reject(errors);
	      });
	    });
	  }
	  save() {
	    return new Promise((resolve, reject) => {
	      const errors = [];
	      if (this.progress) {
	        errors.push('Another action is in progress');
	        reject(errors);
	        return;
	      }
	      let action;
	      let data;
	      if (this.isSaved()) {
	        action = this.actions.update;
	        data = {
	          id: this.getId(),
	          field: this.getData(),
	          moduleId: this.moduleId
	        };
	      } else {
	        action = this.actions.add;
	        data = {
	          field: this.getData(),
	          moduleId: this.moduleId
	        };
	      }
	      if (!main_core.Type.isString(action) || action.length <= 0) {
	        errors.push('UserField action is not specified');
	        reject(errors);
	        return;
	      }
	      this.progress = true;
	      main_core.ajax.runAction(action, {
	        data
	      }).then(response => {
	        this.progress = false;
	        this.setData(response.data.field);
	        resolve(response);
	      }).catch(response => {
	        this.progress = false;
	        response.errors.forEach(({
	          message
	        }) => {
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
	  delete() {
	    return new Promise((resolve, reject) => {
	      const errors = [];
	      if (this.progress) {
	        errors.push('Another action is in progress');
	        reject(errors);
	        return;
	      }
	      if (!this.isSaved()) {
	        errors.push('Cant delete UserField without id');
	        reject(errors);
	        return;
	      }
	      const action = this.actions.delete;
	      if (!main_core.Type.isString(action) || action.length <= 0) {
	        errors.push('UserField action is not specified');
	        reject(errors);
	        return;
	      }
	      this.progress = true;
	      main_core.ajax.runAction(action, {
	        data: {
	          id: this.getId(),
	          moduleId: this.moduleId
	        }
	      }).then(() => {
	        this.deleted = true;
	        this.progress = false;
	        resolve();
	      }).catch(response => {
	        this.progress = false;
	        response.errors.forEach(({
	          message
	        }) => {
	          errors.push(message);
	        });
	        reject(errors);
	      });
	    });
	  }
	  setLanguageId(languageId) {
	    this.languageId = languageId;
	    return this;
	  }
	  setModuleId(moduleId) {
	    this.moduleId = moduleId;
	    return this;
	  }
	  getLanguageId() {
	    if (!this.languageId) {
	      return main_core.Loc.getMessage('LANGUAGE_ID');
	    }
	    return this.languageId;
	  }
	  getId() {
	    return main_core.Text.toInteger(this.data.id);
	  }
	  isSaved() {
	    return this.getId() > 0;
	  }
	  setData(data) {
	    this.data = data;
	    return this;
	  }
	  getData() {
	    return this.data;
	  }
	  getName() {
	    return this.data.fieldName;
	  }
	  setName(name) {
	    if (this.isSaved()) {
	      console.error('Changing name is not available on saved UserField');
	      return this;
	    }
	    this.data.fieldName = name;
	    return this;
	  }
	  getEntityId() {
	    return this.data.entityId;
	  }
	  getUserTypeId() {
	    return this.data.userTypeId;
	  }
	  setUserTypeId(userTypeId) {
	    if (this.isSaved()) {
	      console.error('Changing userTypeId is not available on saved UserField');
	      return this;
	    }
	    this.data.userTypeId = userTypeId;
	    return this;
	  }
	  getEnumeration() {
	    if (!main_core.Type.isArray(this.data.enum)) {
	      this.data.enum = [];
	    }
	    return this.data.enum;
	  }
	  setEnumeration(items) {
	    this.data.enum = items;
	    return this;
	  }
	  static getTitleFieldNames() {
	    return ['editFormLabel', 'listColumnLabel', 'listFilterLabel'];
	  }
	  getTitle() {
	    const titleFieldNames = UserField.getTitleFieldNames();
	    const titleFieldsCount = titleFieldNames.length;
	    const languageId = this.getLanguageId();
	    for (let index = 0; index < titleFieldsCount; index++) {
	      if (this.data[titleFieldNames[index]] && main_core.Type.isString(this.data[titleFieldNames[index]][languageId]) && this.data[titleFieldNames[index]][languageId].length > 0) {
	        return this.data[titleFieldNames[index]][languageId];
	      }
	    }
	    return this.getName();
	  }
	  setTitle(title, languageId) {
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
	  isMultiple() {
	    return this.data.multiple === 'Y';
	  }
	  isMandatory() {
	    return this.data.mandatory === 'Y';
	  }
	  setIsMandatory(mandatory) {
	    this.data.mandatory = main_core.Text.toBoolean(mandatory) ? 'Y' : 'N';
	  }
	  setIsMultiple(isMultiple) {
	    if (this.isSaved()) {
	      console.error('Changing multiple is not available on saved UserField');
	      return this;
	    }
	    this.data.multiple = main_core.Text.toBoolean(isMultiple) === true ? 'Y' : 'N';
	    return this;
	  }
	  getDetailUrl() {
	    return this.data.detailUrl;
	  }
	  isDeleted() {
	    return this.deleted;
	  }
	  serialize() {
	    return JSON.stringify({
	      data: this.data,
	      languageId: this.languageId,
	      moduleId: this.moduleId,
	      progress: this.progress,
	      deleted: this.deleted
	    });
	  }
	  static unserialize(serializedData) {
	    const serializedUserField = JSON.parse(serializedData);
	    const userField = new UserField(serializedUserField.data, {
	      languageId: serializedUserField.languageId,
	      moduleId: serializedUserField.moduleId
	    });
	    userField.progress = serializedUserField.progress;
	    userField.deleted = serializedUserField.deleted;
	    return userField;
	  }
	}

	exports.UserField = UserField;

}((this.BX.UI.UserField = this.BX.UI.UserField || {}),BX));
//# sourceMappingURL=userfield.bundle.js.map
