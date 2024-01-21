this.BX = this.BX || {};
(function (exports,pull_client,main_core,main_popup,main_loader,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popup = /*#__PURE__*/new WeakMap();
	var _loader = /*#__PURE__*/new WeakMap();
	var _groupId = /*#__PURE__*/new WeakMap();
	var _pathToUser = /*#__PURE__*/new WeakMap();
	var _pageNum = /*#__PURE__*/new WeakMap();
	var _listMembers = /*#__PURE__*/new WeakMap();
	var _createPopup = /*#__PURE__*/new WeakSet();
	var _appendMembers = /*#__PURE__*/new WeakSet();
	var _renderPopupContent = /*#__PURE__*/new WeakSet();
	var _renderMember = /*#__PURE__*/new WeakSet();
	var _bindPopupScroll = /*#__PURE__*/new WeakSet();
	var _showLoader = /*#__PURE__*/new WeakSet();
	var _destroyLoader = /*#__PURE__*/new WeakSet();
	var _getList = /*#__PURE__*/new WeakSet();
	var _consoleError = /*#__PURE__*/new WeakSet();
	var MembersList = /*#__PURE__*/function () {
	  function MembersList(params) {
	    babelHelpers.classCallCheck(this, MembersList);
	    _classPrivateMethodInitSpec(this, _consoleError);
	    _classPrivateMethodInitSpec(this, _getList);
	    _classPrivateMethodInitSpec(this, _destroyLoader);
	    _classPrivateMethodInitSpec(this, _showLoader);
	    _classPrivateMethodInitSpec(this, _bindPopupScroll);
	    _classPrivateMethodInitSpec(this, _renderMember);
	    _classPrivateMethodInitSpec(this, _renderPopupContent);
	    _classPrivateMethodInitSpec(this, _appendMembers);
	    _classPrivateMethodInitSpec(this, _createPopup);
	    _classPrivateFieldInitSpec(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _groupId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _pathToUser, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _pageNum, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _listMembers, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _groupId, main_core.Type.isInteger(parseInt(params.groupId, 10)) ? parseInt(params.groupId, 10) : 0);
	    babelHelpers.classPrivateFieldSet(this, _popup, _classPrivateMethodGet(this, _createPopup, _createPopup2).call(this, params.bindElement));
	    babelHelpers.classPrivateFieldSet(this, _pathToUser, params.pathToUser);
	    babelHelpers.classPrivateFieldSet(this, _listMembers, new Set());
	    babelHelpers.classPrivateFieldSet(this, _pageNum, 1);
	  }
	  babelHelpers.createClass(MembersList, [{
	    key: "show",
	    value: function show() {
	      babelHelpers.classPrivateFieldGet(this, _popup).toggle();
	    }
	  }]);
	  return MembersList;
	}();
	function _createPopup2(bindElement) {
	  var _this = this;
	  var popup = new main_popup.Popup({
	    id: 'sn-mrp-members-popup',
	    className: 'sn-mrp-members-popup',
	    bindElement: bindElement,
	    autoHide: true,
	    closeByEsc: true,
	    content: _classPrivateMethodGet(this, _renderPopupContent, _renderPopupContent2).call(this),
	    events: {
	      onAfterPopupShow: function onAfterPopupShow() {
	        var popupContainer = popup.getContentContainer();
	        var listContainer = popupContainer.querySelector('.sn-mrp-members-popup-content');
	        var list = popupContainer.querySelector('.sn-mrp-members-popup-inner');
	        _classPrivateMethodGet(_this, _bindPopupScroll, _bindPopupScroll2).call(_this, list);
	        _classPrivateMethodGet(_this, _showLoader, _showLoader2).call(_this, listContainer);

	        // eslint-disable-next-line promise/catch-or-return
	        _classPrivateMethodGet(_this, _appendMembers, _appendMembers2).call(_this, list).then(function () {
	          _classPrivateMethodGet(_this, _destroyLoader, _destroyLoader2).call(_this);
	          _classPrivateMethodGet(_this, _bindPopupScroll, _bindPopupScroll2).call(_this, list);
	        });
	      }
	    }
	  });
	  return popup;
	}
	function _appendMembers2(list) {
	  var _this2 = this;
	  // eslint-disable-next-line promise/catch-or-return
	  return _classPrivateMethodGet(this, _getList, _getList2).call(this).then(function (listMembers) {
	    listMembers.forEach(function (member) {
	      babelHelpers.classPrivateFieldGet(_this2, _listMembers).add(member);
	      main_core.Dom.append(_classPrivateMethodGet(_this2, _renderMember, _renderMember2).call(_this2, member), list);
	    });
	  });
	}
	function _renderPopupContent2() {
	  return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-mrp-members-popup-container\">\n\t\t\t\t<div class=\"sn-mrp-members-popup-content\">\n\t\t\t\t\t<div class=\"sn-mrp-members-popup-content-box\">\n\t\t\t\t\t\t<div class=\"sn-mrp-members-popup-inner\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])));
	}
	function _renderMember2(member) {
	  var photoIcon = '<i></i>';
	  if (member.photo) {
	    photoIcon = "<i style=\"background-image: url('".concat(encodeURI(member.photo), "')\"></i>");
	  }
	  return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a\n\t\t\t\tclass=\"sn-mrp-members-popup-item\"\n\t\t\t\thref=\"", "\"\n\t\t\t\tdata-id=\"sn-mrp-members-popup-item-", "\"\n\t\t\t>\n\t\t\t\t<span class=\"sn-mrp-members-popup-avatar-new\">\n\t\t\t\t\t<div class=\"ui-icon ui-icon-common-user sn-mrp-members-popup-avatar-img\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<span></span>\n\t\t\t\t</span>\n\t\t\t\t<span class=\"sn-mrp-members-popup-name\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</a>\n\t\t"])), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _pathToUser).replace('#user_id#', member.id)), member.id, photoIcon, main_core.Text.encode(member.name));
	}
	function _bindPopupScroll2(list) {
	  var _this3 = this;
	  main_core.Event.bind(list, 'scroll', function () {
	    if (list.scrollTop > (list.scrollHeight - list.offsetHeight) / 1.5) {
	      _classPrivateMethodGet(_this3, _appendMembers, _appendMembers2).call(_this3, list);
	      main_core.Event.unbindAll(list, 'scroll');
	    }
	  });
	}
	function _showLoader2(target) {
	  babelHelpers.classPrivateFieldSet(this, _loader, new main_loader.Loader({
	    target: target,
	    size: 40
	  }));
	  babelHelpers.classPrivateFieldGet(this, _loader).show();
	}
	function _destroyLoader2() {
	  babelHelpers.classPrivateFieldGet(this, _loader).destroy();
	}
	function _getList2() {
	  var _this4 = this;
	  return new Promise(function (resolve) {
	    main_core.ajax.runAction('socialnetwork.api.workgroup.getListIncomingUsers', {
	      data: {
	        groupId: babelHelpers.classPrivateFieldGet(_this4, _groupId),
	        pageNum: babelHelpers.classPrivateFieldGet(_this4, _pageNum)
	      }
	    }).then(function (response) {
	      var _this$pageNum, _this$pageNum2;
	      babelHelpers.classPrivateFieldSet(_this4, _pageNum, (_this$pageNum = babelHelpers.classPrivateFieldGet(_this4, _pageNum), _this$pageNum2 = _this$pageNum++, _this$pageNum)), _this$pageNum2;
	      resolve(response.data);
	    })["catch"](function (error) {
	      _classPrivateMethodGet(_this4, _consoleError, _consoleError2).call(_this4, 'getList', error);
	    });
	  });
	}
	function _consoleError2(action, error) {
	  // eslint-disable-next-line no-console
	  console.error("MembershipRequestPanel.MembersList: ".concat(action, " error"), error);
	}

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _groupId$1 = /*#__PURE__*/new WeakMap();
	var _update = /*#__PURE__*/new WeakSet();
	var PullRequests = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PullRequests, _EventEmitter);
	  function PullRequests(groupId) {
	    var _this;
	    babelHelpers.classCallCheck(this, PullRequests);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PullRequests).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _update);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _groupId$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.MRP.PullRequests');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _groupId$1, groupId);
	    return _this;
	  }
	  babelHelpers.createClass(PullRequests, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'socialnetwork';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      return {
	        workgroup_user_add: _classPrivateMethodGet$1(this, _update, _update2).bind(this),
	        workgroup_user_delete: _classPrivateMethodGet$1(this, _update, _update2).bind(this),
	        workgroup_user_update: _classPrivateMethodGet$1(this, _update, _update2).bind(this)
	      };
	    }
	  }]);
	  return PullRequests;
	}(main_core_events.EventEmitter);
	function _update2(data) {
	  if (parseInt(data.params.GROUP_ID, 10) === babelHelpers.classPrivateFieldGet(this, _groupId$1)) {
	    this.emit('update');
	  }
	}

	var _templateObject$1, _templateObject2$1, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _sidePanelManager = /*#__PURE__*/new WeakMap();
	var _groupId$2 = /*#__PURE__*/new WeakMap();
	var _pathToUser$1 = /*#__PURE__*/new WeakMap();
	var _pathToUsers = /*#__PURE__*/new WeakMap();
	var _waiting = /*#__PURE__*/new WeakMap();
	var _membersList = /*#__PURE__*/new WeakMap();
	var _container = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _subscribeToPull = /*#__PURE__*/new WeakSet();
	var _getData = /*#__PURE__*/new WeakSet();
	var _update$1 = /*#__PURE__*/new WeakSet();
	var _render = /*#__PURE__*/new WeakSet();
	var _remove = /*#__PURE__*/new WeakSet();
	var _renderSingleRequest = /*#__PURE__*/new WeakSet();
	var _renderMultipleRequest = /*#__PURE__*/new WeakSet();
	var _renderPhoto = /*#__PURE__*/new WeakSet();
	var _showMembers = /*#__PURE__*/new WeakSet();
	var _acceptIncomingRequest = /*#__PURE__*/new WeakSet();
	var _rejectIncomingRequest = /*#__PURE__*/new WeakSet();
	var _openGroupUsers = /*#__PURE__*/new WeakSet();
	var _isWaiting = /*#__PURE__*/new WeakSet();
	var _activateWaiting = /*#__PURE__*/new WeakSet();
	var _deactivateWaiting = /*#__PURE__*/new WeakSet();
	var _consoleError$1 = /*#__PURE__*/new WeakSet();
	var MembershipRequestPanel = /*#__PURE__*/function () {
	  function MembershipRequestPanel(params) {
	    babelHelpers.classCallCheck(this, MembershipRequestPanel);
	    _classPrivateMethodInitSpec$2(this, _consoleError$1);
	    _classPrivateMethodInitSpec$2(this, _deactivateWaiting);
	    _classPrivateMethodInitSpec$2(this, _activateWaiting);
	    _classPrivateMethodInitSpec$2(this, _isWaiting);
	    _classPrivateMethodInitSpec$2(this, _openGroupUsers);
	    _classPrivateMethodInitSpec$2(this, _rejectIncomingRequest);
	    _classPrivateMethodInitSpec$2(this, _acceptIncomingRequest);
	    _classPrivateMethodInitSpec$2(this, _showMembers);
	    _classPrivateMethodInitSpec$2(this, _renderPhoto);
	    _classPrivateMethodInitSpec$2(this, _renderMultipleRequest);
	    _classPrivateMethodInitSpec$2(this, _renderSingleRequest);
	    _classPrivateMethodInitSpec$2(this, _remove);
	    _classPrivateMethodInitSpec$2(this, _render);
	    _classPrivateMethodInitSpec$2(this, _update$1);
	    _classPrivateMethodInitSpec$2(this, _getData);
	    _classPrivateMethodInitSpec$2(this, _subscribeToPull);
	    _classPrivateFieldInitSpec$2(this, _sidePanelManager, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _groupId$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _pathToUser$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _pathToUsers, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _waiting, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _membersList, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _sidePanelManager, BX.SidePanel.Instance);
	    babelHelpers.classPrivateFieldSet(this, _groupId$2, main_core.Type.isInteger(parseInt(params.groupId, 10)) ? parseInt(params.groupId, 10) : 0);
	    babelHelpers.classPrivateFieldSet(this, _pathToUser$1, params.pathToUser);
	    babelHelpers.classPrivateFieldSet(this, _pathToUsers, params.pathToUsers);
	    babelHelpers.classPrivateFieldSet(this, _waiting, false);
	    babelHelpers.classPrivateFieldSet(this, _node, null);
	    _classPrivateMethodGet$2(this, _subscribeToPull, _subscribeToPull2).call(this);
	  }
	  babelHelpers.createClass(MembershipRequestPanel, [{
	    key: "renderTo",
	    value: function renderTo(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('BX.Socialnetwork.Spaces.MembershipRequestPanel: HTMLElement for render not found');
	      }
	      babelHelpers.classPrivateFieldSet(this, _container, container);
	      _classPrivateMethodGet$2(this, _render, _render2).call(this, babelHelpers.classPrivateFieldGet(this, _container), _classPrivateMethodGet$2(this, _getData, _getData2).call(this));
	    }
	  }]);
	  return MembershipRequestPanel;
	}();
	function _subscribeToPull2() {
	  var pullRequests = new PullRequests(babelHelpers.classPrivateFieldGet(this, _groupId$2));
	  pullRequests.subscribe('update', _classPrivateMethodGet$2(this, _update$1, _update2$1).bind(this));
	  pull_client.PULL.subscribe(pullRequests);
	}
	function _getData2() {
	  var _this = this;
	  return main_core.ajax.runAction('socialnetwork.api.workgroup.get', {
	    data: {
	      params: {
	        select: ['LIST_OF_MEMBERS_AWAITING_INVITE', 'COUNTERS'],
	        groupId: babelHelpers.classPrivateFieldGet(this, _groupId$2)
	      }
	    }
	  }).then(function (response) {
	    return {
	      listAwaitingMembers: response.data.LIST_OF_MEMBERS_AWAITING_INVITE,
	      counters: response.data.COUNTERS
	    };
	  })["catch"](function (error) {
	    _classPrivateMethodGet$2(_this, _consoleError$1, _consoleError2$1).call(_this, 'getData', error);
	  });
	}
	function _update2$1() {
	  babelHelpers.classPrivateFieldSet(this, _membersList, null);
	  _classPrivateMethodGet$2(this, _render, _render2).call(this, babelHelpers.classPrivateFieldGet(this, _container), _classPrivateMethodGet$2(this, _getData, _getData2).call(this));
	}
	function _render2(container, dataPromise) {
	  var _this2 = this;
	  // eslint-disable-next-line promise/catch-or-return
	  dataPromise.then(function (data) {
	    main_core.Dom.clean(container);
	    var listAwaitingMembers = data.listAwaitingMembers;
	    if (listAwaitingMembers.length > 0) {
	      if (listAwaitingMembers.length > 1) {
	        var amountRequests = parseInt(data.counters.workgroup_requests_in, 10);
	        main_core.Dom.append(_classPrivateMethodGet$2(_this2, _renderMultipleRequest, _renderMultipleRequest2).call(_this2, amountRequests, listAwaitingMembers), container);
	      } else {
	        main_core.Dom.append(_classPrivateMethodGet$2(_this2, _renderSingleRequest, _renderSingleRequest2).call(_this2, listAwaitingMembers[0]), container);
	      }
	    }
	  });
	}
	function _remove2() {
	  main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _node));
	}
	function _renderSingleRequest2(member) {
	  var acceptId = 'sn-mrp-single-accept-btn';
	  var rejectId = 'sn-mrp-single-reject-btn';
	  babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__warning\">\n\t\t\t\t<div class=\"sn-spaces__warning-icon\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__warning-info\">\n\t\t\t\t\t<div class=\"sn-spaces__warning-info_count\">1</div>\n\t\t\t\t\t<div class=\"sn-spaces__warning-info_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__warning-btns\">\n\t\t\t\t\t<button\n\t\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t\t<button\n\t\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-xs ui-btn-light ui-btn-no-caps ui-btn-round\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), _classPrivateMethodGet$2(this, _renderPhoto, _renderPhoto2).call(this, member.photo), main_core.Loc.getMessage('SN_MRP_SINGLE_LABEL').replace('#id#', 'sn-mrp-member-profile-link').replace('#class#', 'sn-spaces__warning-info_link').replace('#path#', main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _pathToUser$1).replace('#user_id#', member.id))).replace('#name#', main_core.Text.encode(member.name)), acceptId, main_core.Loc.getMessage('SN_MRP_SINGLE_ACCEPT_BTN'), rejectId, main_core.Loc.getMessage('SN_MRP_SINGLE_REJECT_BTN')));
	  var acceptBtn = babelHelpers.classPrivateFieldGet(this, _node).querySelector("[data-id='".concat(acceptId, "']"));
	  main_core.Event.bind(acceptBtn, 'click', _classPrivateMethodGet$2(this, _acceptIncomingRequest, _acceptIncomingRequest2).bind(this, acceptBtn, [member.id]));
	  var rejectBtn = babelHelpers.classPrivateFieldGet(this, _node).querySelector("[data-id='".concat(rejectId, "']"));
	  main_core.Event.bind(rejectBtn, 'click', _classPrivateMethodGet$2(this, _rejectIncomingRequest, _rejectIncomingRequest2).bind(this, rejectBtn, [member.id]));
	  return babelHelpers.classPrivateFieldGet(this, _node);
	}
	function _renderMultipleRequest2(amountRequests, listAwaitingMembers) {
	  var _this3 = this;
	  var visibleAmount = 5;
	  var invisibleAmount = amountRequests - visibleAmount;
	  var members = listAwaitingMembers.slice(0, visibleAmount);
	  var photosId = 'sn-mrp-multiple-photos';
	  var acceptId = 'sn-mrp-multiple-accept-btn';
	  var rejectId = 'sn-mrp-multiple-reject-btn';
	  babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__warning\">\n\t\t\t\t<div\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tclass=\"sn-spaces__warning-icon\"\n\t\t\t\t\tstyle=\"cursor: pointer;\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t<div\n\t\t\t\t\t\tstyle=\"", "\"\n\t\t\t\t\t\tclass=\"sn-spaces__warning-icon_element --count\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<span class=\"sn-spaces__warning-icon_element-plus\">+</span>\n\t\t\t\t\t\t<span class=\"sn-spaces__warning-icon_element-number\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__warning-info\">\n\t\t\t\t\t<div class=\"sn-spaces__warning-info_count\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"sn-spaces__warning-info_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__warning-btns\">\n\t\t\t\t\t<button\n\t\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t\t<button\n\t\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-xs ui-btn-light ui-btn-no-caps ui-btn-round\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), photosId, members.map(function (member) {
	    return _classPrivateMethodGet$2(_this3, _renderPhoto, _renderPhoto2).call(_this3, member.photo);
	  }), invisibleAmount > 0 ? '' : 'display: none;', invisibleAmount, parseInt(amountRequests, 10), main_core.Loc.getMessage('SN_MRP_MULTIPLE_LABEL'), acceptId, main_core.Loc.getMessage('SN_MRP_MULTIPLE_ACCEPT_BTN'), rejectId, main_core.Loc.getMessage('SN_MRP_MULTIPLE_REJECT_BTN')));
	  var photosList = babelHelpers.classPrivateFieldGet(this, _node).querySelector("[data-id='".concat(photosId, "']"));
	  main_core.Event.bind(photosList, 'click', function () {
	    return _classPrivateMethodGet$2(_this3, _showMembers, _showMembers2).call(_this3, photosList);
	  });
	  var acceptBtn = babelHelpers.classPrivateFieldGet(this, _node).querySelector("[data-id='".concat(acceptId, "']"));
	  main_core.Event.bind(acceptBtn, 'click', function () {
	    _classPrivateMethodGet$2(_this3, _acceptIncomingRequest, _acceptIncomingRequest2).call(_this3, acceptBtn, listAwaitingMembers.map(function (member) {
	      return member.id;
	    }));
	  });
	  var rejectBtn = babelHelpers.classPrivateFieldGet(this, _node).querySelector("[data-id='".concat(rejectId, "']"));
	  main_core.Event.bind(rejectBtn, 'click', _classPrivateMethodGet$2(this, _openGroupUsers, _openGroupUsers2).bind(this, 'in'));
	  return babelHelpers.classPrivateFieldGet(this, _node);
	}
	function _renderPhoto2(photo) {
	  if (photo) {
	    return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"sn-spaces__warning-icon_element\"\n\t\t\t\t\tstyle=\"background-image: url('", "');\"\n\t\t\t\t></div>\n\t\t\t"])), encodeURI(photo));
	  }
	  return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"sn-spaces__warning-icon_element ui-icon ui-icon-common-user ui-icon-xs\"><i></i></div>"])));
	}
	function _showMembers2(bindElement) {
	  if (!babelHelpers.classPrivateFieldGet(this, _membersList)) {
	    babelHelpers.classPrivateFieldSet(this, _membersList, new MembersList({
	      groupId: babelHelpers.classPrivateFieldGet(this, _groupId$2),
	      bindElement: bindElement,
	      pathToUser: babelHelpers.classPrivateFieldGet(this, _pathToUser$1)
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _membersList).show();
	}
	function _acceptIncomingRequest2(btn, userIds) {
	  var _this4 = this;
	  if (_classPrivateMethodGet$2(this, _isWaiting, _isWaiting2).call(this)) {
	    return;
	  }
	  _classPrivateMethodGet$2(this, _activateWaiting, _activateWaiting2).call(this, btn);
	  main_core.ajax.runAction('socialnetwork.api.workgroup.acceptIncomingRequest', {
	    data: {
	      groupId: babelHelpers.classPrivateFieldGet(this, _groupId$2),
	      userIds: userIds
	    }
	  }).then(function (response) {
	    _classPrivateMethodGet$2(_this4, _remove, _remove2).call(_this4);
	    _classPrivateMethodGet$2(_this4, _deactivateWaiting, _deactivateWaiting2).call(_this4, btn);
	  })["catch"](function (error) {
	    _classPrivateMethodGet$2(_this4, _consoleError$1, _consoleError2$1).call(_this4, 'acceptIncomingRequest', error);
	    _classPrivateMethodGet$2(_this4, _deactivateWaiting, _deactivateWaiting2).call(_this4, btn);
	  });
	}
	function _rejectIncomingRequest2(btn, userIds) {
	  var _this5 = this;
	  if (_classPrivateMethodGet$2(this, _isWaiting, _isWaiting2).call(this)) {
	    return;
	  }
	  _classPrivateMethodGet$2(this, _activateWaiting, _activateWaiting2).call(this, btn);
	  main_core.ajax.runAction('socialnetwork.api.workgroup.rejectIncomingRequest', {
	    data: {
	      groupId: babelHelpers.classPrivateFieldGet(this, _groupId$2),
	      userIds: userIds
	    }
	  }).then(function (response) {
	    _classPrivateMethodGet$2(_this5, _remove, _remove2).call(_this5);
	    _classPrivateMethodGet$2(_this5, _deactivateWaiting, _deactivateWaiting2).call(_this5, btn);
	  })["catch"](function (error) {
	    _classPrivateMethodGet$2(_this5, _consoleError$1, _consoleError2$1).call(_this5, 'acceptIncomingRequest', error);
	    _classPrivateMethodGet$2(_this5, _deactivateWaiting, _deactivateWaiting2).call(_this5, btn);
	  });
	}
	function _openGroupUsers2(mode) {
	  var availableModes = {
	    all: 'members',
	    "in": 'requests_in',
	    out: 'requests_out'
	  };
	  var uri = new main_core.Uri(babelHelpers.classPrivateFieldGet(this, _pathToUsers));
	  uri.setQueryParams({
	    mode: availableModes[mode]
	  });
	  babelHelpers.classPrivateFieldGet(this, _sidePanelManager).open(uri.toString(), {
	    width: 1200,
	    cacheable: false,
	    loader: 'group-users-loader'
	  });
	}
	function _isWaiting2() {
	  return babelHelpers.classPrivateFieldGet(this, _waiting);
	}
	function _activateWaiting2(btn) {
	  babelHelpers.classPrivateFieldSet(this, _waiting, true);
	  main_core.Dom.addClass(btn, 'ui-btn-wait');
	}
	function _deactivateWaiting2(btn) {
	  babelHelpers.classPrivateFieldSet(this, _waiting, false);
	  main_core.Dom.removeClass(btn, 'ui-btn-wait');
	}
	function _consoleError2$1(action, error) {
	  // eslint-disable-next-line no-console
	  console.error("MembershipRequestPanel: ".concat(action, " error"), error);
	}

	exports.MembershipRequestPanel = MembershipRequestPanel;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX,BX,BX.Main,BX,BX.Event));
//# sourceMappingURL=membership-request-panel.bundle.js.map
