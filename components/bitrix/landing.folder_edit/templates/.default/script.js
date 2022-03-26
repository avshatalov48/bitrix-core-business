this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _siteId = /*#__PURE__*/new WeakMap();

	var _siteType = /*#__PURE__*/new WeakMap();

	var _folderId = /*#__PURE__*/new WeakMap();

	var _selectorCreateIndex = /*#__PURE__*/new WeakMap();

	var _selectorIndexMetaBlock = /*#__PURE__*/new WeakMap();

	var _selectorSelect = /*#__PURE__*/new WeakMap();

	var _selectorPageLink = /*#__PURE__*/new WeakMap();

	var _selectorFieldId = /*#__PURE__*/new WeakMap();

	var _selectorPreviewBlock = /*#__PURE__*/new WeakMap();

	var _selectorPreviewTitle = /*#__PURE__*/new WeakMap();

	var _selectorPreviewDescription = /*#__PURE__*/new WeakMap();

	var _selectorPreviewPicture = /*#__PURE__*/new WeakMap();

	var _selectorPreviewSrcPicture = /*#__PURE__*/new WeakMap();

	var _selectorPreviewPictureWrapper = /*#__PURE__*/new WeakMap();

	var _pathToLandingEdit = /*#__PURE__*/new WeakMap();

	var _pathToLandingCreate = /*#__PURE__*/new WeakMap();

	var _linkUrlSelector = /*#__PURE__*/new WeakMap();

	var _linkPictureSelector = /*#__PURE__*/new WeakMap();

	var _ajaxPathLoadPreview = /*#__PURE__*/new WeakMap();

	var _initSelector = /*#__PURE__*/new WeakSet();

	var _initPicture = /*#__PURE__*/new WeakSet();

	var _onSelect = /*#__PURE__*/new WeakSet();

	var _onClickSelect = /*#__PURE__*/new WeakSet();

	var _onClickIndexCreate = /*#__PURE__*/new WeakSet();

	var _loadPreview = /*#__PURE__*/new WeakSet();

	var FolderEdit = function FolderEdit(options) {
	  babelHelpers.classCallCheck(this, FolderEdit);

	  _classPrivateMethodInitSpec(this, _loadPreview);

	  _classPrivateMethodInitSpec(this, _onClickIndexCreate);

	  _classPrivateMethodInitSpec(this, _onClickSelect);

	  _classPrivateMethodInitSpec(this, _onSelect);

	  _classPrivateMethodInitSpec(this, _initPicture);

	  _classPrivateMethodInitSpec(this, _initSelector);

	  _classPrivateFieldInitSpec(this, _siteId, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _siteType, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _folderId, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorCreateIndex, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorIndexMetaBlock, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorSelect, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPageLink, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorFieldId, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewBlock, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewTitle, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewDescription, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewPicture, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewSrcPicture, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _selectorPreviewPictureWrapper, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _pathToLandingEdit, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _pathToLandingCreate, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _linkUrlSelector, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _linkPictureSelector, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec(this, _ajaxPathLoadPreview, {
	    writable: true,
	    value: '/bitrix/services/main/ajax.php?action=landing.api.landing.getById&landingId=#id#'
	  });

	  babelHelpers.classPrivateFieldSet(this, _siteId, options.siteId);
	  babelHelpers.classPrivateFieldSet(this, _siteType, options.siteType);
	  babelHelpers.classPrivateFieldSet(this, _folderId, options.folderId);
	  babelHelpers.classPrivateFieldSet(this, _selectorCreateIndex, options.selectorCreateIndex);
	  babelHelpers.classPrivateFieldSet(this, _selectorIndexMetaBlock, options.selectorIndexMetaBlock);
	  babelHelpers.classPrivateFieldSet(this, _selectorSelect, options.selectorSelect);
	  babelHelpers.classPrivateFieldSet(this, _selectorPageLink, options.selectorPageLink);
	  babelHelpers.classPrivateFieldSet(this, _selectorFieldId, options.selectorFieldId);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewBlock, options.selectorPreviewBlock);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewTitle, options.selectorPreviewTitle);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewDescription, options.selectorPreviewDescription);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewPicture, options.selectorPreviewPicture);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewSrcPicture, options.selectorPreviewSrcPicture);
	  babelHelpers.classPrivateFieldSet(this, _selectorPreviewPictureWrapper, options.selectorPreviewPictureWrapper);
	  babelHelpers.classPrivateFieldSet(this, _pathToLandingEdit, options.pathToLandingEdit);
	  babelHelpers.classPrivateFieldSet(this, _pathToLandingCreate, options.pathToLandingCreate);

	  _classPrivateMethodGet(this, _initSelector, _initSelector2).call(this);

	  _classPrivateMethodGet(this, _initPicture, _initPicture2).call(this);

	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _selectorSelect), 'click', _classPrivateMethodGet(this, _onClickSelect, _onClickSelect2).bind(this));

	  if (babelHelpers.classPrivateFieldGet(this, _selectorCreateIndex)) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _selectorCreateIndex), 'click', _classPrivateMethodGet(this, _onClickIndexCreate, _onClickIndexCreate2).bind(this));
	  }
	};

	function _initSelector2() {
	  babelHelpers.classPrivateFieldSet(this, _linkUrlSelector, new BX.Landing.UI.Field.LinkURL({
	    title: null,
	    content: null,
	    allowedTypes: [BX.Landing.UI.Field.LinkURL.TYPE_PAGE],
	    options: {
	      siteId: babelHelpers.classPrivateFieldGet(this, _siteId),
	      currentSiteOnly: true,
	      disableAddPage: true,
	      landingId: -1,
	      filter: {
	        'ID': babelHelpers.classPrivateFieldGet(this, _siteId),
	        '=TYPE': babelHelpers.classPrivateFieldGet(this, _siteType)
	      },
	      filterLanding: {
	        'FOLDER_ID': babelHelpers.classPrivateFieldGet(this, _folderId)
	      }
	    },
	    onInput: _classPrivateMethodGet(this, _onSelect, _onSelect2).bind(this)
	  }));
	}

	function _initPicture2() {
	  var _this = this;

	  if (!babelHelpers.classPrivateFieldGet(this, _selectorPreviewSrcPicture)) {
	    return;
	  }

	  babelHelpers.classPrivateFieldSet(this, _linkPictureSelector, new BX.Landing.UI.Field.Image({
	    id: 'folderPicture',
	    disableLink: true,
	    disableAltField: true,
	    allowClear: true,
	    content: {
	      src: babelHelpers.classPrivateFieldGet(this, _selectorPreviewSrcPicture).getAttribute('value'),
	      id: babelHelpers.classPrivateFieldGet(this, _selectorPreviewPicture).getAttribute('value')
	    },
	    uploadParams: {
	      action: 'Site::uploadFile',
	      id: babelHelpers.classPrivateFieldGet(this, _siteId)
	    },
	    dimensions: {
	      width: 1200,
	      height: 1200
	    }
	  }));
	  main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _selectorPreviewPictureWrapper));
	  main_core.Dom.append(babelHelpers.classPrivateFieldGet(this, _linkPictureSelector)['layout'], babelHelpers.classPrivateFieldGet(this, _selectorPreviewPictureWrapper));
	  babelHelpers.classPrivateFieldGet(this, _linkPictureSelector)['layout'].addEventListener('input', function () {
	    var file = babelHelpers.classPrivateFieldGet(_this, _linkPictureSelector).getValue();
	    babelHelpers.classPrivateFieldGet(_this, _selectorPreviewPicture).setAttribute('value', file['id2x']);
	  });
	}

	function _onSelect2(title) {
	  var id = babelHelpers.classPrivateFieldGet(this, _linkUrlSelector).getValue().substr(8);
	  var path = babelHelpers.classPrivateFieldGet(this, _pathToLandingEdit).replace('#landing_edit#', id);
	  babelHelpers.classPrivateFieldGet(this, _selectorPageLink).text = title;
	  babelHelpers.classPrivateFieldGet(this, _selectorPageLink).setAttribute('href', path);
	  babelHelpers.classPrivateFieldGet(this, _selectorFieldId).setAttribute('value', id);

	  _classPrivateMethodGet(this, _loadPreview, _loadPreview2).call(this, id);
	}

	function _onClickSelect2() {
	  babelHelpers.classPrivateFieldGet(this, _linkUrlSelector).onSelectButtonClick();
	}

	function _onClickIndexCreate2(e) {
	  BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(this, _pathToLandingCreate), {
	    allowChangeHistory: false,
	    events: {
	      onClose: function onClose() {
	        window.location.reload();
	      }
	    }
	  });
	  BX.PreventDefault(e);
	}

	function _loadPreview2(landingId) {
	  var _this2 = this;

	  babelHelpers.classPrivateFieldGet(this, _selectorPreviewBlock).style.display = 'block';
	  babelHelpers.classPrivateFieldGet(this, _selectorIndexMetaBlock).style.display = 'flex';
	  BX.ajax({
	    url: babelHelpers.classPrivateFieldGet(this, _ajaxPathLoadPreview).replace('#id#', landingId),
	    method: 'GET',
	    dataType: 'json',
	    onsuccess: function onsuccess(result) {
	      var data = result.data;

	      if (!data['ADDITIONAL_FIELDS']) {
	        return;
	      }

	      var title = data['ADDITIONAL_FIELDS']['METAOG_TITLE'] || data['TITLE'];
	      var description = data['ADDITIONAL_FIELDS']['METAOG_DESCRIPTION'] || data['DESCRIPTION'] || '';
	      babelHelpers.classPrivateFieldGet(_this2, _selectorPreviewTitle).setAttribute('value', title);
	      babelHelpers.classPrivateFieldGet(_this2, _selectorPreviewDescription).setAttribute('value', description);
	      babelHelpers.classPrivateFieldGet(_this2, _selectorPreviewPicture).setAttribute('value', '');
	      babelHelpers.classPrivateFieldGet(_this2, _selectorPreviewPicture).setAttribute('value', data['ADDITIONAL_FIELDS']['~METAOG_IMAGE'] || '');
	      babelHelpers.classPrivateFieldGet(_this2, _selectorPreviewSrcPicture).setAttribute('value', data['ADDITIONAL_FIELDS']['METAOG_IMAGE'] || '');
	      babelHelpers.classPrivateFieldGet(_this2, _linkPictureSelector).setValue({
	        src: data['ADDITIONAL_FIELDS']['METAOG_IMAGE'] || '',
	        id: data['ADDITIONAL_FIELDS']['~METAOG_IMAGE'] || -1
	      });
	    }
	  });
	}

	exports.FolderEdit = FolderEdit;

}((this.BX.Landing.Component = this.BX.Landing.Component || {}),BX));
//# sourceMappingURL=script.js.map
