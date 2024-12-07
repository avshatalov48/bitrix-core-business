/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _documentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentId");
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _url = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("url");
	var _entityName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("entityName");
	var _moduleName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moduleName");
	class DocumentId {
	  constructor(options) {
	    Object.defineProperty(this, _documentId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _url, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _entityName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _moduleName, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isArray(options)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId] = options;
	    } else if (main_core.Type.isPlainObject(options)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId] = options.documentId;
	      babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = options.documentName;
	      babelHelpers.classPrivateFieldLooseBase(this, _url)[_url] = options.documentUrl;
	      babelHelpers.classPrivateFieldLooseBase(this, _entityName)[_entityName] = options.entityName;
	      babelHelpers.classPrivateFieldLooseBase(this, _moduleName)[_moduleName] = options.moduleName;
	    }
	  }
	  hasName() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _name)[_name]);
	  }
	  hasUrl() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _url)[_url]);
	  }
	  get name() {
	    return this.hasName() ? babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] : '';
	  }
	  get url() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _url)[_url];
	  }
	  hasEntityName() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _entityName)[_entityName]);
	  }
	  get entityName() {
	    return this.hasEntityName() ? babelHelpers.classPrivateFieldLooseBase(this, _entityName)[_entityName] : '';
	  }
	  hasModuleName() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _name)[_name]);
	  }
	  get moduleName() {
	    return this.hasModuleName() ? babelHelpers.classPrivateFieldLooseBase(this, _moduleName)[_moduleName] : '';
	  }
	  get moduleId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId][0];
	  }
	  get entity() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId][1];
	  }
	  get id() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId][2];
	  }
	  toJSON() {
	    return JSON.stringify(babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId]);
	  }
	}

	exports.DocumentId = DocumentId;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX));
//# sourceMappingURL=document.bundle.js.map
