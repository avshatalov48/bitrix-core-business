/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,ui_buttons,im_public_iframe,ui_alerts,main_core_events,ui_entitySelector,main_core,ui_lottie) {
	'use strict';

	var _templateObject;
	var Util = /*#__PURE__*/function () {
	  function Util() {
	    babelHelpers.classCallCheck(this, Util);
	  }
	  babelHelpers.createClass(Util, null, [{
	    key: "initExpandSwitches",
	    value: function initExpandSwitches() {
	      var expandSwitchers = document.querySelectorAll('[data-role="socialnetwork-group-create-ex__expandable"]');
	      expandSwitchers.forEach(function (switcher) {
	        switcher.addEventListener('click', function (e) {
	          var targetId = e.currentTarget.getAttribute('for');
	          var target = document.getElementById(targetId);
	          var switcherWrapper = target.firstElementChild;
	          if (target.offsetHeight === 0) {
	            target.style.height = switcherWrapper.offsetHeight + 'px';
	            target.classList.add('--open');
	            var scrollToTarget = function scrollToTarget() {
	              var elementRealTop = target.getBoundingClientRect().top / 100;
	              var time = 400;
	              var currentTime = 0;
	              var scrollBySvs = function scrollBySvs() {
	                window.scrollBy(0, elementRealTop);
	              };
	              while (currentTime <= time) {
	                window.setTimeout(scrollBySvs, currentTime, elementRealTop);
	                currentTime += time / 100;
	              }
	              target.removeEventListener('transitionend', scrollToTarget);
	            };
	            var adjustHeight = function adjustHeight() {
	              target.style.height = 'auto';
	              target.removeEventListener('transitionend', adjustHeight);
	            };
	            target.addEventListener('transitionend', adjustHeight);
	            target.addEventListener('transitionend', scrollToTarget);
	          }
	          if (target.offsetHeight > 0) {
	            target.style.height = target.offsetHeight + 'px';
	            setTimeout(function () {
	              target.style.removeProperty('height');
	              target.classList.remove('--open');
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "initDropdowns",
	    value: function initDropdowns() {
	      var _this = this;
	      var dropdownAreaList = document.querySelectorAll('[data-role="soc-net-dropdown"]');
	      dropdownAreaList.forEach(function (dropdownArea) {
	        dropdownArea.addEventListener('click', function (e) {
	          var dropdownArea = e.currentTarget;
	          var dropdownItemsData = _this.getDropdownItems(dropdownArea);
	          var items = [];
	          Object.entries(dropdownItemsData).forEach(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              key = _ref2[0],
	              value = _ref2[1];
	            items.push({
	              text: value,
	              onclick: function onclick() {
	                dropdownMenu.close();
	                _this.setDropdownValue(dropdownArea.querySelector('.ui-ctl-element'), value, dropdownArea);
	                _this.setInputValue(dropdownArea.querySelector('input'), key, dropdownArea);
	                var neighbourDropdownArea = null;
	                if (dropdownArea.classList.contains('--nonproject')) {
	                  neighbourDropdownArea = dropdownArea.parentNode.querySelector('.--project');
	                } else if (dropdownArea.classList.contains('--project')) {
	                  neighbourDropdownArea = dropdownArea.parentNode.querySelector('.--nonproject');
	                }
	                if (main_core.Type.isDomNode(neighbourDropdownArea)) {
	                  _this.setDropdownValue(neighbourDropdownArea.querySelector('.ui-ctl-element'), value, neighbourDropdownArea);
	                  _this.setInputValue(neighbourDropdownArea.querySelector('input'), key, neighbourDropdownArea);
	                }
	              }
	            });
	          });
	          var dropdownMenu = new BX.PopupMenuWindow({
	            autoHide: true,
	            cacheable: false,
	            bindElement: dropdownArea,
	            width: dropdownArea.offsetWidth,
	            closeByEsc: true,
	            animation: 'fading-slide',
	            items: items
	          });
	          dropdownMenu.params.width = dropdownArea.offsetWidth;
	          dropdownMenu.show();
	        });
	      });
	    }
	  }, {
	    key: "setDropdownValue",
	    value: function setDropdownValue(node, value, containerNode) {
	      var dropdownItemsData = this.getDropdownItems(containerNode);
	      Object.entries(dropdownItemsData).forEach(function (_ref3) {
	        var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	          itemValue = _ref4[1];
	        if (value === itemValue) {
	          node.innerText = value;
	        }
	      });
	    }
	  }, {
	    key: "setInputValue",
	    value: function setInputValue(node, value, containerNode) {
	      var dropdownItemsData = this.getDropdownItems(containerNode);
	      Object.entries(dropdownItemsData).forEach(function (_ref5) {
	        var _ref6 = babelHelpers.slicedToArray(_ref5, 1),
	          itemKey = _ref6[0];
	        if (value === itemKey) {
	          node.value = value;
	        }
	      });
	    }
	  }, {
	    key: "getDropdownItems",
	    value: function getDropdownItems(node) {
	      var dropdownItemsData = {};
	      try {
	        dropdownItemsData = JSON.parse(node.getAttribute('data-items'));
	      } catch (e) {
	        return {};
	      }
	      if (!main_core.Type.isPlainObject(dropdownItemsData)) {
	        return {};
	      }
	      return dropdownItemsData;
	    }
	  }, {
	    key: "recalcFormPartProject",
	    value: function recalcFormPartProject(isChecked) {
	      isChecked = !!isChecked;
	      var projectCheckboxNode = document.getElementById('GROUP_PROJECT');
	      if (projectCheckboxNode) {
	        this.setCheckedValue(projectCheckboxNode, isChecked);
	      }
	      document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-project, .socialnetwork-group-create-ex__create--switch-nonproject').forEach(function (node) {
	        if (isChecked) {
	          node.classList.add('--project');
	        } else {
	          node.classList.remove('--project');
	        }
	      });
	      this.recalcNameInput();
	    }
	  }, {
	    key: "recalcNameInput",
	    value: function recalcNameInput() {
	      var inputNode = document.getElementById('GROUP_NAME_input');
	      if (!inputNode) {
	        return;
	      }
	      var placeholderText = main_core.Loc.getMessage('SONET_GCE_T_NAME3');
	      var formInstance = WorkgroupForm.getInstance();
	      if (main_core.Type.isPlainObject(formInstance.projectTypes[formInstance.selectedProjectType])) {
	        if (main_core.Type.isStringFilled(formInstance.projectTypes[formInstance.selectedProjectType].SCRUM_PROJECT) && formInstance.projectTypes[formInstance.selectedProjectType].SCRUM_PROJECT === 'Y') {
	          placeholderText = main_core.Loc.getMessage('SONET_GCE_T_NAME3_SCRUM');
	        } else if (main_core.Type.isStringFilled(formInstance.projectTypes[formInstance.selectedProjectType].PROJECT) && formInstance.projectTypes[formInstance.selectedProjectType].PROJECT === 'Y') {
	          placeholderText = main_core.Loc.getMessage('SONET_GCE_T_NAME3_PROJECT');
	        }
	      }
	      inputNode.placeholder = placeholderText;
	    }
	  }, {
	    key: "setCheckedValue",
	    value: function setCheckedValue(node, value) {
	      if (!main_core.Type.isDomNode(node)) {
	        return;
	      }
	      value = !!value;
	      if (node.type === 'checkbox') {
	        node.checked = value;
	      } else {
	        node.value = value ? 'Y' : 'N';
	      }
	    }
	  }, {
	    key: "getCheckedValue",
	    value: function getCheckedValue(node) {
	      var result = false;
	      if (!main_core.Type.isDomNode(node)) {
	        return result;
	      }
	      if (node.type == 'hidden') {
	        result = node.value === 'Y';
	      } else if (node.type == 'checkbox') {
	        result = node.checked;
	      }
	      return result;
	    }
	  }, {
	    key: "unselectAllSelectorItems",
	    value: function unselectAllSelectorItems(container, selectorClass) {
	      var _this2 = this;
	      if (!main_core.Type.isDomNode(container)) {
	        return;
	      }
	      container.querySelectorAll(".".concat(selectorClass)).forEach(function (selector) {
	        selector.classList.remove(_this2.cssClass.selectorActive);
	      });
	    }
	  }, {
	    key: "selectSelectorItem",
	    value: function selectSelectorItem(node) {
	      node.classList.add(this.cssClass.selectorActive);
	    }
	  }, {
	    key: "disableAllSelectorItems",
	    value: function disableAllSelectorItems(container, selectorClass) {
	      var _this3 = this;
	      if (!main_core.Type.isDomNode(container)) {
	        return;
	      }
	      container.querySelectorAll(".".concat(selectorClass)).forEach(function (selector) {
	        selector.classList.add(_this3.cssClass.selectorDisabled);
	      });
	    }
	  }, {
	    key: "enableAllSelectorItems",
	    value: function enableAllSelectorItems(container, selectorClass) {
	      var _this4 = this;
	      if (!main_core.Type.isDomNode(container)) {
	        return;
	      }
	      container.querySelectorAll(".".concat(selectorClass)).forEach(function (selector) {
	        selector.classList.remove(_this4.cssClass.selectorDisabled);
	      });
	    }
	  }, {
	    key: "enableSelectorItem",
	    value: function enableSelectorItem(node) {
	      node.classList.remove(this.cssClass.selectorDisabled);
	    }
	  }, {
	    key: "recalcInputValue",
	    value: function recalcInputValue(params) {
	      var selectedItems = params.selectedItems || [];
	      var multiple = main_core.Type.isBoolean(params.multiple) ? params.multiple : true;
	      var inputContainerNodeId = params.inputContainerNodeId || '';
	      var inputNodeName = params.inputNodeName || '';
	      if (!main_core.Type.isArray(selectedItems) || !main_core.Type.isStringFilled(inputNodeName) || !main_core.Type.isStringFilled(inputContainerNodeId)) {
	        return;
	      }
	      var inputContainerNode = document.getElementById(inputContainerNodeId);
	      if (!inputContainerNode) {
	        return;
	      }
	      if (multiple) {
	        inputNodeName = "".concat(inputNodeName, "[]");
	      }
	      inputContainerNode.querySelectorAll("input[name=\"".concat(inputNodeName, "\"]")).forEach(function (node) {
	        main_core.Dom.remove(node);
	      });
	      selectedItems.forEach(function (item) {
	        var prefix = null;
	        switch (item.entityId) {
	          case 'department':
	            prefix = 'DR';
	            break;
	          case 'user':
	            prefix = 'U';
	            break;
	          default:
	        }
	        if (prefix) {
	          inputContainerNode.appendChild(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name=\"", "\" value=\"", "", "\" >"], ["<input type=\"hidden\" name=\"", "\" value=\"", "", "\" \\>"])), inputNodeName, prefix, item.id));
	        }
	      });
	    }
	  }]);
	  return Util;
	}();
	babelHelpers.defineProperty(Util, "cssClass", {
	  selectorActive: '--active',
	  selectorDisabled: '--disabled'
	});

	var ConfidentialitySelector = /*#__PURE__*/function () {
	  function ConfidentialitySelector() {
	    var _this = this;
	    babelHelpers.classCallCheck(this, ConfidentialitySelector);
	    var firstItemSelected = false;
	    ConfidentialitySelector.getItems().forEach(function (selector) {
	      selector.addEventListener('click', function (e) {
	        var selector = e.currentTarget;
	        if (selector.classList.contains(Util.cssClass.selectorDisabled)) {
	          return;
	        }
	        Util.unselectAllSelectorItems(ConfidentialitySelector.getContainer(), ConfidentialitySelector.cssClass.selector);
	        Util.selectSelectorItem(selector);
	        WorkgroupForm.getInstance().recalcForm({
	          selectedConfidentialityType: selector.getAttribute('data-bx-confidentiality-type')
	        });
	      });
	      var confidentialityType = selector.getAttribute('data-bx-confidentiality-type');
	      if (main_core.Type.isStringFilled(WorkgroupForm.getInstance().selectedConfidentialityType)) {
	        if (WorkgroupForm.getInstance().selectedConfidentialityType === confidentialityType) {
	          _this.selectItem(selector);
	        }
	      } else if (!firstItemSelected) {
	        _this.selectItem(selector);
	        firstItemSelected = true;
	      }
	    });
	    this.bindEvents();
	  }
	  babelHelpers.createClass(ConfidentialitySelector, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      WorkgroupForm.getInstance().subscribe('onSwitchExtranet', ConfidentialitySelector.onSwitchExtranet);
	    }
	  }, {
	    key: "selectItem",
	    value: function selectItem(selector) {
	      Util.selectSelectorItem(selector);
	      WorkgroupForm.getInstance().recalcForm({
	        selectedConfidentialityType: selector.getAttribute('data-bx-confidentiality-type')
	      });
	    }
	  }], [{
	    key: "onSwitchExtranet",
	    value: function onSwitchExtranet(event) {
	      var data = event.getData();
	      if (!main_core.Type.isBoolean(data.isChecked)) {
	        return;
	      }
	      if (data.isChecked) {
	        ConfidentialitySelector.unselectAll();
	        ConfidentialitySelector.select('secret');
	        ConfidentialitySelector.disableAll();
	        ConfidentialitySelector.enable('secret');
	      } else {
	        ConfidentialitySelector.enableAll();
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return document.querySelector(".".concat(this.cssClass.container));
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      var container = this.getContainer();
	      if (!container) {
	        return [];
	      }
	      return container.querySelectorAll(".".concat(this.cssClass.selector));
	    }
	  }, {
	    key: "unselectAll",
	    value: function unselectAll() {
	      Util.unselectAllSelectorItems(this.getContainer(), this.cssClass.selector);
	    }
	  }, {
	    key: "select",
	    value: function select(accessCode) {
	      this.getItems().forEach(function (selector) {
	        if (selector.getAttribute('data-bx-confidentiality-type') !== accessCode) {
	          return;
	        }
	        Util.selectSelectorItem(selector);
	      });
	    }
	  }, {
	    key: "disableAll",
	    value: function disableAll() {
	      Util.disableAllSelectorItems(this.getContainer(), this.cssClass.selector);
	    }
	  }, {
	    key: "enableAll",
	    value: function enableAll() {
	      Util.enableAllSelectorItems(this.getContainer(), this.cssClass.selector);
	    }
	  }, {
	    key: "enable",
	    value: function enable(accessCode) {
	      this.getItems().forEach(function (selector) {
	        if (selector.getAttribute('data-bx-confidentiality-type') !== accessCode) {
	          return;
	        }
	        Util.enableSelectorItem(selector);
	      });
	    }
	  }]);
	  return ConfidentialitySelector;
	}();
	babelHelpers.defineProperty(ConfidentialitySelector, "cssClass", {
	  container: 'socialnetwork-group-create-ex__type-confidentiality-wrapper',
	  selector: 'socialnetwork-group-create-ex__group-selector'
	});

	var Scrum = /*#__PURE__*/function () {
	  function Scrum(params) {
	    babelHelpers.classCallCheck(this, Scrum);
	    this.isScrumProject = params.isScrumProject;
	  }
	  babelHelpers.createClass(Scrum, [{
	    key: "makeAdditionalCustomizationForm",
	    value: function makeAdditionalCustomizationForm() {
	      if (this.isScrumProject) {
	        this.createHiddenInputs();
	        this.showScrumBlocks();
	        if (!main_core.Type.isStringFilled(WorkgroupForm.getInstance().selectedConfidentialityType)) {
	          ConfidentialitySelector.unselectAll();
	          ConfidentialitySelector.select('open');
	          WorkgroupForm.getInstance().recalcForm({
	            selectedConfidentialityType: 'open'
	          });
	        }
	        var landingCheckbox = document.getElementById('GROUP_LANDING');
	        if (landingCheckbox) {
	          landingCheckbox.disabled = true;
	          landingCheckbox.checked = false;
	        }
	        this.toggleFeatures(true);
	      } else {
	        this.removeHiddenInputs();
	        this.hideScrumBlocks();
	        var _landingCheckbox = document.getElementById('GROUP_LANDING');
	        if (_landingCheckbox) {
	          _landingCheckbox.disabled = false;
	        }
	        this.toggleFeatures(false);
	      }
	      Util.recalcNameInput();
	    }
	  }, {
	    key: "hideScrumBlocks",
	    value: function hideScrumBlocks() {
	      document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-scrum, .socialnetwork-group-create-ex__create--switch-nonscrum').forEach(function (scrumBlock) {
	        scrumBlock.classList.remove('--scrum');
	      });
	      var moderatorsBlock = document.getElementById('expandable-moderator-block');
	      if (moderatorsBlock) {
	        moderatorsBlock.classList.add('socialnetwork-group-create-ex__content-expandable');
	      }
	      var moderatorsSwitch = document.getElementById('GROUP_MODERATORS_PROJECT_switch');
	      if (moderatorsSwitch) {
	        moderatorsSwitch.classList.add('ui-ctl-file-link');
	      }
	      var ownerBlock = document.getElementById('GROUP_OWNER_block');
	      if (ownerBlock) {
	        ownerBlock.classList.remove('--space-bottom');
	      }
	    }
	  }, {
	    key: "showScrumBlocks",
	    value: function showScrumBlocks() {
	      document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-scrum, .socialnetwork-group-create-ex__create--switch-nonscrum').forEach(function (scrumBlock) {
	        scrumBlock.classList.add('--scrum');
	      });
	      var moderatorsBlock = document.getElementById('expandable-moderator-block');
	      if (moderatorsBlock) {
	        moderatorsBlock.classList.remove('socialnetwork-group-create-ex__content-expandable');
	      }
	      var moderatorsSwitch = document.getElementById('GROUP_MODERATORS_PROJECT_switch');
	      if (moderatorsSwitch) {
	        moderatorsSwitch.classList.remove('ui-ctl-file-link');
	      }
	      var ownerBlock = document.getElementById('GROUP_OWNER_block');
	      if (ownerBlock) {
	        ownerBlock.classList.add('--space-bottom');
	      }
	    }
	  }, {
	    key: "createHiddenInputs",
	    value: function createHiddenInputs() {
	      document.forms['sonet_group_create_popup_form'].appendChild(main_core.Dom.create('input', {
	        attrs: {
	          type: 'hidden',
	          name: 'SCRUM_PROJECT',
	          value: 'Y'
	        }
	      }));
	    }
	  }, {
	    key: "removeHiddenInputs",
	    value: function removeHiddenInputs() {
	      document.forms['sonet_group_create_popup_form'].querySelectorAll('input[name="SCRUM_PROJECT"]').forEach(function (input) {
	        main_core.Dom.remove(input);
	      });
	    }
	  }, {
	    key: "toggleFeatures",
	    value: function toggleFeatures(isScrum) {
	      var featuresNode = document.querySelector('.socialnetwork-group-create-ex__project-instruments');
	      if (featuresNode) {
	        featuresNode.querySelectorAll('input[type="checkbox"][name="tasks_active"], input[type="checkbox"][name="calendar_active"]').forEach(function (featuresCheckboxNode) {
	          if (isScrum) {
	            featuresCheckboxNode.disabled = true;
	            featuresCheckboxNode.checked = true;
	            featuresCheckboxNode.parentNode.insertBefore(main_core.Dom.create('input', {
	              attrs: {
	                type: 'hidden',
	                name: featuresCheckboxNode.name,
	                value: 'Y'
	              }
	            }), featuresCheckboxNode);
	          } else {
	            featuresCheckboxNode.disabled = false;
	            document.forms['sonet_group_create_popup_form'].querySelectorAll("input[type=\"hidden\"][name=\"".concat(featuresCheckboxNode.name, "\"]")).forEach(function (hiddenInput) {
	              main_core.Dom.remove(hiddenInput);
	            });
	          }
	        });
	      }
	    }
	  }]);
	  return Scrum;
	}();

	var Avatar = /*#__PURE__*/function () {
	  function Avatar(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, Avatar);
	    this.confirmPopup = null;
	    if (!main_core.Type.isStringFilled(params.componentName) || main_core.Type.isUndefined(params.signedParameters)) {
	      return;
	    }
	    this.componentName = params.componentName;
	    this.signedParameters = params.signedParameters;
	    this.groupId = !main_core.Type.isUndefined(params.groupId) ? parseInt(params.groupId) : 0;
	    var container = document.querySelector('[data-role="group-avatar-cont"]');
	    if (!container) {
	      return;
	    }
	    this.selectorNode = container.querySelector('[data-role="group-avatar-selector"]');
	    this.imageNode = container.querySelector('[data-role="group-avatar-image"]');
	    this.inputNode = container.querySelector('[data-role="group-avatar-input"]');
	    this.typeInputNode = container.querySelector('[data-role="group-avatar-type-input"]');
	    this.removeNode = container.querySelector('[data-role="group-avatar-remove"]');
	    if (!main_core.Type.isDomNode(this.imageNode) || !main_core.Type.isDomNode(this.inputNode) || !main_core.Type.isDomNode(this.typeInputNode) || !main_core.Type.isDomNode(this.removeNode)) {
	      return;
	    }
	    this.recalc();
	    var avatarEditor = new BX.AvatarEditor({
	      enableCamera: false
	    });
	    this.selectorNode.addEventListener('click', function (e) {
	      if (e.target.getAttribute('data-role') === 'group-avatar-remove' && _this.imageNode.style.backgroundImage !== '') {
	        _this.showConfirmPopup(main_core.Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM'), _this.deletePhoto.bind(_this));
	      } else if (e.target.getAttribute('data-role') === 'group-avatar-type') {
	        _this.clearType();
	        _this.setType(e.target.getAttribute('data-avatar-type'));
	      } else if (e.target.getAttribute('data-role') === 'group-avatar-image') {
	        avatarEditor.show('file');
	      }
	    });
	    main_core_events.EventEmitter.subscribe('onApply', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	        _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	        file = _event$getCompatData2[0];
	      var formData = new FormData();
	      if (!file.name) {
	        file.name = 'tmp.png';
	      }
	      formData.append('newPhoto', file, file.name);
	      _this.changePhoto(formData);
	    });
	  }
	  babelHelpers.createClass(Avatar, [{
	    key: "recalc",
	    value: function recalc() {
	      if (this.getFileId() <= 0) {
	        this.removeNode.classList.add(Avatar.classList.hidden);
	        this.imageNode.classList.remove(Avatar.classList.selected);
	      } else {
	        this.removeNode.classList.remove(Avatar.classList.hidden);
	        this.imageNode.classList.add(Avatar.classList.selected);
	      }
	    }
	  }, {
	    key: "changePhoto",
	    value: function changePhoto(formData) {
	      var _this2 = this;
	      var loader = this.showLoader({
	        node: this.imageNode,
	        loader: null,
	        size: 78
	      });
	      main_core.ajax.runComponentAction(this.componentName, 'loadPhoto', {
	        signedParameters: this.signedParameters,
	        mode: 'ajax',
	        data: formData
	      }).then(function (response) {
	        if (main_core.Type.isPlainObject(response.data) && parseInt(response.data.fileId) > 0 && main_core.Type.isStringFilled(response.data.fileUri)) {
	          _this2.clearType();
	          _this2.inputNode.value = parseInt(response.data.fileId);
	          _this2.typeInputNode.value = '';
	          _this2.imageNode.style = "background-image: url('".concat(encodeURI(response.data.fileUri), "'); background-size: cover;");
	          _this2.recalc();
	        }
	        _this2.hideLoader({
	          loader: loader
	        });
	      }, function (response) {
	        _this2.hideLoader({
	          loader: loader
	        });
	        _this2.showErrorPopup(response["errors"][0].message);
	      });
	    }
	  }, {
	    key: "deletePhoto",
	    value: function deletePhoto() {
	      var _this3 = this;
	      var fileId = this.getFileId();
	      if (fileId < 0) {
	        return;
	      }
	      var loader = this.showLoader({
	        node: this.imageNode,
	        loader: null,
	        size: 78
	      });
	      main_core.ajax.runComponentAction(this.componentName, 'deletePhoto', {
	        signedParameters: this.signedParameters,
	        mode: 'ajax',
	        data: {
	          fileId: fileId,
	          groupId: this.groupId
	        }
	      }).then(function (response) {
	        _this3.imageNode.style = '';
	        _this3.inputNode.value = '';
	        _this3.recalc();
	        _this3.hideLoader({
	          loader: loader
	        });
	      }, function (response) {
	        _this3.hideLoader({
	          loader: loader
	        });
	        _this3.showErrorPopup(response.errors[0].message);
	      });
	    }
	  }, {
	    key: "clearType",
	    value: function clearType() {
	      this.selectorNode.querySelectorAll('[data-role="group-avatar-type"]').forEach(function (typeItemNode) {
	        typeItemNode.classList.remove(Avatar.classList.selected);
	      });
	    }
	  }, {
	    key: "setType",
	    value: function setType(avatarType) {
	      this.inputNode.value = '';
	      this.imageNode.style = '';
	      this.typeInputNode.value = avatarType;
	      this.imageNode.classList.remove(Avatar.classList.selected);
	      this.selectorNode.querySelectorAll('[data-role="group-avatar-type"]').forEach(function (typeItemNode) {
	        if (typeItemNode.getAttribute('data-avatar-type') !== avatarType) {
	          return;
	        }
	        typeItemNode.classList.add(Avatar.classList.selected);
	      });
	      this.recalc();
	    }
	  }, {
	    key: "getFileId",
	    value: function getFileId() {
	      return main_core.Type.isStringFilled(this.inputNode.value) ? parseInt(this.inputNode.value) : 0;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader(params) {
	      var loader = null;
	      if (main_core.Type.isDomNode(params.node)) {
	        if (main_core.Type.isNull(params.loader)) {
	          loader = new BX.Loader({
	            target: params.node,
	            size: params.hasOwnProperty('size') ? params.size : 40
	          });
	        } else {
	          loader = params.loader;
	        }
	        loader.show();
	      }
	      return loader;
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader(params) {
	      if (!main_core.Type.isNull(params.loader)) {
	        params.loader.hide();
	        params.loader = null;
	      }
	      if (main_core.Type.isDomNode(params.node)) {
	        main_core.Dom.clean(params.node);
	      }
	    }
	  }, {
	    key: "showErrorPopup",
	    value: function showErrorPopup(error) {
	      if (!error) {
	        return;
	      }
	      new main_popup.Popup('gce-image-upload-error', null, {
	        autoHide: true,
	        closeByEsc: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        closeIcon: true,
	        content: error,
	        events: {},
	        cacheable: false
	      }).show();
	    }
	  }, {
	    key: "showConfirmPopup",
	    value: function showConfirmPopup(text, confirmCallback) {
	      var _this4 = this;
	      this.confirmPopup = new main_popup.Popup('gce-image-delete-confirm', null, {
	        autoHide: true,
	        closeByEsc: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        closeIcon: true,
	        content: text,
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this4.confirmPopup.destroy();
	          }
	        },
	        cacheable: false,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_YES'),
	          events: {
	            click: function click(button) {
	              button.setWaiting(true);
	              _this4.confirmPopup.close();
	              confirmCallback();
	            }
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_NO'),
	          events: {
	            click: function click() {
	              _this4.confirmPopup.close();
	            }
	          }
	        })]
	      });
	      this.confirmPopup.show();
	    }
	  }]);
	  return Avatar;
	}();
	babelHelpers.defineProperty(Avatar, "classList", {
	  hidden: '--hidden',
	  selected: '--selected'
	});

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _dateStartInput = /*#__PURE__*/new WeakMap();
	var _dateEndInput = /*#__PURE__*/new WeakMap();
	var _culture = /*#__PURE__*/new WeakMap();
	var _getDateInput = /*#__PURE__*/new WeakSet();
	var _bindHandlers = /*#__PURE__*/new WeakSet();
	var _adjustDates = /*#__PURE__*/new WeakSet();
	var _getTimeStamp = /*#__PURE__*/new WeakSet();
	var _convertToSeconds = /*#__PURE__*/new WeakSet();
	var _getFormatDate = /*#__PURE__*/new WeakSet();
	var DateCorrector = function DateCorrector(params) {
	  babelHelpers.classCallCheck(this, DateCorrector);
	  _classPrivateMethodInitSpec(this, _getFormatDate);
	  _classPrivateMethodInitSpec(this, _convertToSeconds);
	  _classPrivateMethodInitSpec(this, _getTimeStamp);
	  _classPrivateMethodInitSpec(this, _adjustDates);
	  _classPrivateMethodInitSpec(this, _bindHandlers);
	  _classPrivateMethodInitSpec(this, _getDateInput);
	  _classPrivateFieldInitSpec(this, _dateStartInput, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _dateEndInput, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _culture, {
	    writable: true,
	    value: void 0
	  });
	  babelHelpers.classPrivateFieldSet(this, _dateStartInput, _classPrivateMethodGet(this, _getDateInput, _getDateInput2).call(this, 'PROJECT_DATE_START'));
	  babelHelpers.classPrivateFieldSet(this, _dateEndInput, _classPrivateMethodGet(this, _getDateInput, _getDateInput2).call(this, 'PROJECT_DATE_FINISH'));
	  babelHelpers.classPrivateFieldSet(this, _culture, params.culture);
	  _classPrivateMethodGet(this, _bindHandlers, _bindHandlers2).call(this);
	};
	function _getDateInput2(name) {
	  if (document.getElementsByName(name)[0]) {
	    return document.getElementsByName(name)[0];
	  }
	  return null;
	}
	function _bindHandlers2() {
	  if (babelHelpers.classPrivateFieldGet(this, _dateStartInput)) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _dateStartInput), 'change', _classPrivateMethodGet(this, _adjustDates, _adjustDates2).bind(this, true));
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _dateEndInput)) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _dateEndInput), 'change', _classPrivateMethodGet(this, _adjustDates, _adjustDates2).bind(this, false));
	  }
	}
	function _adjustDates2(startChanged) {
	  var start = _classPrivateMethodGet(this, _getTimeStamp, _getTimeStamp2).call(this, babelHelpers.classPrivateFieldGet(this, _dateStartInput).value);
	  var end = _classPrivateMethodGet(this, _getTimeStamp, _getTimeStamp2).call(this, babelHelpers.classPrivateFieldGet(this, _dateEndInput).value);
	  var startDate = start ? new Date(start * 1000) : null;
	  var endDate = end ? new Date(end * 1000) : null;
	  if (startDate && endDate) {
	    if (startDate >= endDate) {
	      var defaultOffset = 86400 * 1000;
	      if (startChanged) {
	        var newEndDate = new Date(startDate.getTime() + defaultOffset);
	        babelHelpers.classPrivateFieldGet(this, _dateEndInput).value = _classPrivateMethodGet(this, _getFormatDate, _getFormatDate2).call(this, newEndDate.getTime() / 1000);
	      } else {
	        var newStartDate = new Date(endDate.getTime() - defaultOffset);
	        babelHelpers.classPrivateFieldGet(this, _dateStartInput).value = _classPrivateMethodGet(this, _getFormatDate, _getFormatDate2).call(this, newStartDate.getTime() / 1000);
	      }
	    }
	  }
	}
	function _getTimeStamp2(date) {
	  if (date.toString().length > 0) {
	    // eslint-disable-next-line bitrix-rules/no-bx
	    var parsedValue = BX.parseDate(date, true);
	    if (parsedValue === null) {
	      return null;
	    }
	    return _classPrivateMethodGet(this, _convertToSeconds, _convertToSeconds2).call(this, parsedValue.getTime());
	  }
	  return null;
	}
	function _convertToSeconds2(value) {
	  return Math.floor(parseInt(value) / 1000);
	}
	function _getFormatDate2(timeStamp) {
	  var date = new Date(timeStamp * 1000);
	  return BX.date.format(babelHelpers.classPrivateFieldGet(this, _culture).shortDateFormat, date);
	}

	var ThemePicker = /*#__PURE__*/function () {
	  function ThemePicker(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, ThemePicker);
	    this.container = params.container;
	    this.theme = params.theme;
	    this.draw(this.theme);
	    var previewImageNode = this.getNode('image');
	    if (previewImageNode) {
	      previewImageNode.addEventListener('click', this.open);
	    }
	    var titleNode = this.getNode('title');
	    if (titleNode) {
	      titleNode.addEventListener('click', this.open);
	    }
	    var deleteNode = this.getNode('delete');
	    if (deleteNode) {
	      deleteNode.addEventListener('click', function () {
	        _this.select({});
	      });
	    }
	    main_core_events.EventEmitter.subscribe('Intranet.ThemePicker:onSave', function (event) {
	      var _event$getData = event.getData(),
	        _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	        data = _event$getData2[0];
	      _this.select(data);
	    });
	  }
	  babelHelpers.createClass(ThemePicker, [{
	    key: "select",
	    value: function select(data) {
	      var theme = main_core.Type.isPlainObject(data.theme) ? data.theme : {};
	      this.draw(theme);
	    }
	  }, {
	    key: "draw",
	    value: function draw(theme) {
	      var previewImageNode = this.getNode('image');
	      if (previewImageNode) {
	        previewImageNode.style.backgroundImage = main_core.Type.isStringFilled(theme.previewImage) ? "url('".concat(theme.previewImage, "')") : '';
	        previewImageNode.style.backgroundColor = main_core.Type.isStringFilled(theme.previewColor) ? theme.previewColor : 'transparent';
	      }
	      var titleNode = this.getNode('title');
	      if (titleNode) {
	        titleNode.innerHTML = main_core.Type.isStringFilled(theme.title) ? theme.title : '';
	      }
	      var inputNode = this.getNode('id');
	      if (inputNode) {
	        inputNode.value = main_core.Type.isStringFilled(theme.id) ? theme.id : '';
	      }
	    }
	  }, {
	    key: "open",
	    value: function open(event) {
	      BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(true);
	      event.preventDefault();
	    }
	  }, {
	    key: "getNode",
	    value: function getNode(name) {
	      var result = null;
	      if (!main_core.Type.isStringFilled(name)) {
	        return result;
	      }
	      return this.container.querySelector("[bx-group-edit-theme-node=\"".concat(name, "\"]"));
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.container;
	    }
	  }]);
	  return ThemePicker;
	}();

	var Tags = /*#__PURE__*/function () {
	  function Tags(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, Tags);
	    var containerNode = document.getElementById(params.containerNodeId);
	    if (!containerNode) {
	      return;
	    }
	    this.hiddenFieldNode = document.getElementById(params.hiddenFieldId);
	    var tagSelector = new ui_entitySelector.TagSelector({
	      addButtonCaption: main_core.Loc.getMessage('SONET_GCE_T_TAG_ADD'),
	      addButtonCaptionMore: main_core.Loc.getMessage('SONET_GCE_T_KEYWORDS_ADD_TAG'),
	      dialogOptions: {
	        width: 350,
	        height: 300,
	        offsetLeft: 50,
	        compactView: true,
	        preload: true,
	        context: 'PROJECT_TAG',
	        searchTabOptions: {
	          stubOptions: {
	            title: main_core.Loc.getMessage('SONET_GCE_T_TAG_SEARCH_FAILED'),
	            subtitle: main_core.Loc.getMessage('SONET_GCE_T_TAG_SEARCH_ADD_HINT'),
	            arrow: true
	          }
	        },
	        entities: [{
	          id: 'project-tag',
	          options: {
	            groupId: params.groupId
	          }
	        }],
	        searchOptions: {
	          allowCreateItem: true,
	          footerOptions: {
	            label: main_core.Loc.getMessage('SONET_GCE_T_TAG_SEARCH_ADD_FOOTER_LABEL')
	          }
	        },
	        events: {
	          'Item:onSelect': function ItemOnSelect(event) {
	            _this.recalcinputValue(event.getTarget().getSelectedItems());
	          },
	          'Item:onDeselect': function ItemOnDeselect(event) {
	            _this.recalcinputValue(event.getTarget().getSelectedItems());
	          },
	          'Search:onItemCreateAsync': function SearchOnItemCreateAsync(event) {
	            return new Promise(function (resolve) {
	              var _event$getData = event.getData(),
	                searchQuery = _event$getData.searchQuery;
	              var name = searchQuery.getQuery().toLowerCase();
	              var dialog = event.getTarget();
	              setTimeout(function () {
	                var tagsList = name.split(',');
	                tagsList.forEach(function (tag) {
	                  var item = dialog.addItem({
	                    id: tag,
	                    entityId: 'project-tag',
	                    title: tag,
	                    tabs: ['all', 'recents']
	                  });
	                  if (item) {
	                    item.select();
	                  }
	                });
	                resolve();
	              }, 1000);
	            });
	          }
	        }
	      }
	    });
	    tagSelector.renderTo(containerNode);
	  }
	  babelHelpers.createClass(Tags, [{
	    key: "recalcinputValue",
	    value: function recalcinputValue(items) {
	      if (!main_core.Type.isArray(items) || !main_core.Type.isDomNode(this.hiddenFieldNode)) {
	        return;
	      }
	      var tagsList = [];
	      items.forEach(function (item) {
	        tagsList.push(item.id);
	      });
	      this.hiddenFieldNode.value = tagsList.join(',');
	    }
	  }]);
	  return Tags;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	var FieldsManager = /*#__PURE__*/function () {
	  function FieldsManager() {
	    babelHelpers.classCallCheck(this, FieldsManager);
	  }
	  babelHelpers.createClass(FieldsManager, null, [{
	    key: "check",
	    value: function () {
	      var _check = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
	        return _regeneratorRuntime().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              if (!(WorkgroupForm.getInstance().wizardManager.stepsCount === 1)) {
	                _context.next = 4;
	                break;
	              }
	              _context.next = 3;
	              return this.checkAll();
	            case 3:
	              return _context.abrupt("return", _context.sent);
	            case 4:
	              _context.next = 6;
	              return this.checkStep(WorkgroupForm.getInstance().wizardManager.currentStep);
	            case 6:
	              return _context.abrupt("return", _context.sent);
	            case 7:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee, this);
	      }));
	      function check() {
	        return _check.apply(this, arguments);
	      }
	      return check;
	    }()
	  }, {
	    key: "checkStep",
	    value: function () {
	      var _checkStep = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(step) {
	        var errorDataList, _iterator, _step, fieldData, fieldNode, errorText, bindNode;
	        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
	          while (1) switch (_context2.prev = _context2.next) {
	            case 0:
	              step = parseInt(step);
	              errorDataList = [];
	              if (!main_core.Type.isArray(this.mandatoryFieldsByStep[step])) {
	                _context2.next = 31;
	                break;
	              }
	              _iterator = _createForOfIteratorHelper(this.mandatoryFieldsByStep[step]);
	              _context2.prev = 4;
	              _iterator.s();
	            case 6:
	              if ((_step = _iterator.n()).done) {
	                _context2.next = 23;
	                break;
	              }
	              fieldData = _step.value;
	              fieldNode = document.getElementById(fieldData.id);
	              if (main_core.Type.isDomNode(fieldNode)) {
	                _context2.next = 11;
	                break;
	              }
	              return _context2.abrupt("continue", 21);
	            case 11:
	              if (!(fieldNode.tagName.toLowerCase() !== 'input')) {
	                _context2.next = 16;
	                break;
	              }
	              if (!(fieldData.type === 'string')) {
	                _context2.next = 16;
	                break;
	              }
	              fieldNode = fieldNode.querySelector('input[type="text"]');
	              if (main_core.Type.isDomNode(fieldNode)) {
	                _context2.next = 16;
	                break;
	              }
	              return _context2.abrupt("continue", 21);
	            case 16:
	              fieldData.fieldNode = fieldNode;
	              // eslint-disable-next-line no-await-in-loop
	              _context2.next = 19;
	              return this.checkField(fieldData);
	            case 19:
	              errorText = _context2.sent;
	              if (main_core.Type.isStringFilled(errorText)) {
	                bindNode = document.getElementById(fieldData.bindNodeId);
	                errorDataList.push({
	                  bindNode: main_core.Type.isDomNode(bindNode) ? bindNode : fieldNode,
	                  message: errorText
	                });
	              }
	            case 21:
	              _context2.next = 6;
	              break;
	            case 23:
	              _context2.next = 28;
	              break;
	            case 25:
	              _context2.prev = 25;
	              _context2.t0 = _context2["catch"](4);
	              _iterator.e(_context2.t0);
	            case 28:
	              _context2.prev = 28;
	              _iterator.f();
	              return _context2.finish(28);
	            case 31:
	              return _context2.abrupt("return", errorDataList);
	            case 32:
	            case "end":
	              return _context2.stop();
	          }
	        }, _callee2, this, [[4, 25, 28, 31]]);
	      }));
	      function checkStep(_x) {
	        return _checkStep.apply(this, arguments);
	      }
	      return checkStep;
	    }()
	  }, {
	    key: "checkAll",
	    value: function () {
	      var _checkAll = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
	        var errorDataList, _i, _Object$entries, stepData;
	        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
	          while (1) switch (_context3.prev = _context3.next) {
	            case 0:
	              errorDataList = [];
	              _i = 0, _Object$entries = Object.entries(this.mandatoryFieldsByStep);
	            case 2:
	              if (!(_i < _Object$entries.length)) {
	                _context3.next = 12;
	                break;
	              }
	              stepData = _Object$entries[_i];
	              _context3.t0 = errorDataList;
	              _context3.next = 7;
	              return this.checkStep(parseInt(stepData[0]));
	            case 7:
	              _context3.t1 = _context3.sent;
	              errorDataList = _context3.t0.concat.call(_context3.t0, _context3.t1);
	            case 9:
	              _i++;
	              _context3.next = 2;
	              break;
	            case 12:
	              return _context3.abrupt("return", errorDataList);
	            case 13:
	            case "end":
	              return _context3.stop();
	          }
	        }, _callee3, this);
	      }));
	      function checkAll() {
	        return _checkAll.apply(this, arguments);
	      }
	      return checkAll;
	    }()
	  }, {
	    key: "checkField",
	    value: function () {
	      var _checkField = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee4(fieldData) {
	        var _WorkgroupForm$getIns, _WorkgroupForm$getIns2;
	        var errorText, fieldNode, fieldType, fieldId, groupId, type, exists, empty;
	        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
	          while (1) switch (_context4.prev = _context4.next) {
	            case 0:
	              errorText = '';
	              if (!(!main_core.Type.isPlainObject(fieldData) && !main_core.Type.isDomNode(fieldData.fieldNode))) {
	                _context4.next = 3;
	                break;
	              }
	              return _context4.abrupt("return", errorText);
	            case 3:
	              if (!main_core.Type.isFunction(fieldData.condition)) {
	                _context4.next = 6;
	                break;
	              }
	              if (fieldData.condition()) {
	                _context4.next = 6;
	                break;
	              }
	              return _context4.abrupt("return", errorText);
	            case 6:
	              fieldNode = fieldData.fieldNode;
	              fieldType = main_core.Type.isStringFilled(fieldData.type) ? fieldData.type : 'string';
	              fieldId = fieldData.id;
	              groupId = (_WorkgroupForm$getIns = WorkgroupForm.getInstance()) === null || _WorkgroupForm$getIns === void 0 ? void 0 : _WorkgroupForm$getIns.groupId;
	              type = (_WorkgroupForm$getIns2 = WorkgroupForm.getInstance()) === null || _WorkgroupForm$getIns2 === void 0 ? void 0 : _WorkgroupForm$getIns2.selectedProjectType;
	              _context4.t0 = fieldType;
	              _context4.next = _context4.t0 === 'string' ? 14 : _context4.t0 === 'input_hidden_container' ? 25 : 29;
	              break;
	            case 14:
	              if (!(fieldNode.value.trim() === '')) {
	                _context4.next = 17;
	                break;
	              }
	              errorText = main_core.Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR');
	              return _context4.abrupt("break", 30);
	            case 17:
	              if (!(groupId <= 0 && fieldId === 'GROUP_NAME_input')) {
	                _context4.next = 23;
	                break;
	              }
	              _context4.next = 20;
	              return FieldsManager.checkSameGroupExists(fieldNode.value);
	            case 20:
	              exists = _context4.sent;
	              if (exists) {
	                errorText = type === 'project' ? main_core.Loc.getMessage('SONET_GCE_T_GROUP_NAME_EXISTS_PROJECT') : main_core.Loc.getMessage('SONET_GCE_T_GROUP_NAME_EXISTS');
	              }
	              return _context4.abrupt("break", 30);
	            case 23:
	              errorText = '';
	              return _context4.abrupt("break", 30);
	            case 25:
	              empty = true;
	              fieldNode.querySelectorAll('input[type="hidden"]').forEach(function (hiddenNode) {
	                if (!empty) {
	                  return;
	                }
	                if (main_core.Type.isStringFilled(hiddenNode.value)) {
	                  empty = false;
	                }
	              });
	              errorText = empty ? main_core.Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR') : '';
	              return _context4.abrupt("break", 30);
	            case 29:
	              errorText = '';
	            case 30:
	              return _context4.abrupt("return", errorText);
	            case 31:
	            case "end":
	              return _context4.stop();
	          }
	        }, _callee4);
	      }));
	      function checkField(_x2) {
	        return _checkField.apply(this, arguments);
	      }
	      return checkField;
	    }()
	  }, {
	    key: "showError",
	    value: function showError(errorData) {
	      if (!main_core.Type.isPlainObject(errorData) || !main_core.Type.isStringFilled(errorData.message) || !main_core.Type.isDomNode(errorData.bindNode)) {
	        return;
	      }
	      WorkgroupForm.getInstance().alertManager.showAlert(errorData.message, errorData.bindNode.parentNode);
	    }
	  }, {
	    key: "checkSameGroupExists",
	    value: function () {
	      var _checkSameGroupExists = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee5(groupName) {
	        var _response$data;
	        var response;
	        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
	          while (1) switch (_context5.prev = _context5.next) {
	            case 0:
	              _context5.next = 2;
	              return main_core.ajax.runAction('socialnetwork.api.workgroup.isExistingGroup', {
	                data: {
	                  name: groupName
	                }
	              });
	            case 2:
	              response = _context5.sent;
	              return _context5.abrupt("return", response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.exists);
	            case 4:
	            case "end":
	              return _context5.stop();
	          }
	        }, _callee5);
	      }));
	      function checkSameGroupExists(_x3) {
	        return _checkSameGroupExists.apply(this, arguments);
	      }
	      return checkSameGroupExists;
	    }()
	  }]);
	  return FieldsManager;
	}();
	babelHelpers.defineProperty(FieldsManager, "mandatoryFieldsByStep", {
	  2: [{
	    id: 'GROUP_NAME_input',
	    type: 'string',
	    bindNodeId: 'GROUP_NAME_input'
	  }],
	  4: [{
	    id: 'SCRUM_MASTER_CODE_container',
	    type: 'input_hidden_container',
	    bindNodeId: 'SCRUM_MASTER_selector',
	    condition: function condition() {
	      return !!WorkgroupForm.getInstance().scrumManager.isScrumProject;
	    }
	  }]
	});

	function _regeneratorRuntime$1() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$1 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _sendCollabCreateButtonAnalytics = /*#__PURE__*/new WeakSet();
	var Buttons = /*#__PURE__*/function () {
	  function Buttons() {
	    babelHelpers.classCallCheck(this, Buttons);
	    _classPrivateMethodInitSpec$1(this, _sendCollabCreateButtonAnalytics);
	    this.submitButton = document.getElementById('sonet_group_create_popup_form_button_submit');
	    if (!this.submitButton) {
	      return;
	    }
	    this.initCollabCreateButton();
	    this.submitButtonClickHandler = this.submitButtonClickHandler.bind(this);
	    this.submitButton.addEventListener('click', this.submitButtonClickHandler);
	    this.backButton = document.getElementById('sonet_group_create_popup_form_button_step_2_back');
	    if (this.backButton) {
	      this.backButton.addEventListener('click', function (e) {
	        var button = ui_buttons.ButtonManager.createFromNode(e.currentTarget);
	        if (button && button.isDisabled()) {
	          return;
	        }
	        if (WorkgroupForm.getInstance().wizardManager.currentStep > 1) {
	          WorkgroupForm.getInstance().wizardManager.currentStep--;
	          if (WorkgroupForm.getInstance().wizardManager.currentStep === 3 && Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1)
	            // skip confidentiality step
	            {
	              WorkgroupForm.getInstance().wizardManager.currentStep--;
	            }
	          WorkgroupForm.getInstance().wizardManager.showCurrentStep();
	        }
	        return e.preventDefault();
	      });
	    }
	    this.cancelButton = document.getElementById('sonet_group_create_popup_form_button_step_2_cancel');
	    if (this.cancelButton) {
	      this.cancelButton.addEventListener('click', function (e) {
	        var button = ui_buttons.ButtonManager.createFromNode(e.currentTarget);
	        if (button && button.isDisabled()) {
	          return;
	        }
	        var currentSlider = BX.SidePanel.Instance.getSliderByWindow(window);
	        if (currentSlider) {
	          var _event = new main_core_events.BaseEvent({
	            compatData: [currentSlider.getEvent('onClose')],
	            data: currentSlider.getEvent('onClose')
	          });
	          main_core_events.EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', _event);
	        } else {
	          var url = e.currentTarget.getAttribute('bx-url');
	          if (main_core.Type.isStringFilled(url)) {
	            window.location = url;
	          }
	        }
	        var event = new main_core_events.BaseEvent({
	          compatData: [false],
	          data: false
	        });
	        main_core_events.EventEmitter.emit(window.top, 'BX.Bitrix24.PageSlider:close', event);
	        main_core_events.EventEmitter.emit(window.top, 'onSonetIframeCancelClick');
	        return e.preventDefault();
	      });
	    }
	  }
	  babelHelpers.createClass(Buttons, [{
	    key: "submitButtonClickHandler",
	    value: function () {
	      var _submitButtonClickHandler = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee(e) {
	        var button, errorDataList, submitFunction;
	        return _regeneratorRuntime$1().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              e.preventDefault();
	              button = ui_buttons.ButtonManager.createFromNode(e.currentTarget);
	              if (!(button && button.isDisabled())) {
	                _context.next = 4;
	                break;
	              }
	              return _context.abrupt("return");
	            case 4:
	              WorkgroupForm.getInstance().alertManager.hideAllAlerts();
	              _context.next = 7;
	              return FieldsManager.check();
	            case 7:
	              errorDataList = _context.sent.filter(function (errorData) {
	                return main_core.Type.isPlainObject(errorData) && main_core.Type.isStringFilled(errorData.message) && main_core.Type.isDomNode(errorData.bindNode);
	              });
	              if (errorDataList.length > 0) {
	                errorDataList.forEach(function (errorData) {
	                  FieldsManager.showError(errorData);
	                });
	              } else if (WorkgroupForm.getInstance().wizardManager.currentStep < WorkgroupForm.getInstance().wizardManager.stepsCount) {
	                WorkgroupForm.getInstance().wizardManager.currentStep++;
	                if (WorkgroupForm.getInstance().wizardManager.currentStep === 3 && Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1)
	                  // skip confidentiality step
	                  {
	                    WorkgroupForm.getInstance().wizardManager.currentStep++;
	                  }
	                WorkgroupForm.getInstance().wizardManager.showCurrentStep();
	              } else {
	                submitFunction = function (event) {
	                  WorkgroupForm.getInstance().submitForm(event);
	                }.bind(WorkgroupForm.getInstance());
	                submitFunction(e);
	              }
	              return _context.abrupt("return", e.preventDefault());
	            case 10:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee);
	      }));
	      function submitButtonClickHandler(_x) {
	        return _submitButtonClickHandler.apply(this, arguments);
	      }
	      return submitButtonClickHandler;
	    }()
	  }, {
	    key: "initCollabCreateButton",
	    value: function initCollabCreateButton() {
	      var _this = this;
	      this.collabCreateButton = document.getElementById('sonet_group_create_popup_form_button_collab');
	      if (!this.collabCreateButton) {
	        return;
	      }
	      this.collabCreateButton.onclick = function (e) {
	        e.preventDefault();
	        _classPrivateMethodGet$1(_this, _sendCollabCreateButtonAnalytics, _sendCollabCreateButtonAnalytics2).call(_this);
	        im_public_iframe.Messenger.openChatCreation('collab');
	      };
	    }
	  }, {
	    key: "updateButtonsByProject",
	    value: function updateButtonsByProject(projectType) {
	      if (projectType === 'collab') {
	        Buttons.hideButton(this.submitButton);
	        Buttons.showButton(this.collabCreateButton);
	      } else {
	        Buttons.hideButton(this.collabCreateButton);
	        Buttons.showButton(this.submitButton);
	      }
	    }
	  }], [{
	    key: "showWaitSubmitButton",
	    value: function showWaitSubmitButton(disable) {
	      disable = !!disable;
	      var buttonNode = document.getElementById('sonet_group_create_popup_form_button_submit');
	      if (!buttonNode) {
	        return;
	      }
	      var button = ui_buttons.ButtonManager.createFromNode(buttonNode);
	      if (disable) {
	        if (button) {
	          button.setWaiting(true);
	        }
	        buttonNode.removeEventListener('click', WorkgroupForm.getInstance().submitButtonClickHandler);
	      } else {
	        if (button) {
	          button.setWaiting(false);
	        }
	        buttonNode.addEventListener('click', WorkgroupForm.getInstance().submitButtonClickHandler);
	      }
	    }
	  }, {
	    key: "disableButton",
	    value: function disableButton(buttonNode, disable) {
	      if (!main_core.Type.isDomNode(buttonNode)) {
	        return;
	      }
	      var button = ui_buttons.ButtonManager.createFromNode(buttonNode);
	      if (!button) {
	        return;
	      }
	      button.setDisabled(disable);
	    }
	  }, {
	    key: "showButton",
	    value: function showButton(buttonNode) {
	      if (!main_core.Type.isDomNode(buttonNode)) {
	        return;
	      }
	      buttonNode.classList.remove(this.cssClass.hidden);
	    }
	  }, {
	    key: "hideButton",
	    value: function hideButton(buttonNode) {
	      if (!main_core.Type.isDomNode(buttonNode)) {
	        return;
	      }
	      buttonNode.classList.add(this.cssClass.hidden);
	    }
	  }]);
	  return Buttons;
	}();
	function _sendCollabCreateButtonAnalytics2() {
	  var analyticsData = {
	    event: 'click_create_new',
	    category: 'collab',
	    c_section: 'project',
	    tool: 'im',
	    p2: "user_".concat(WorkgroupForm.getInstance().currentUserType)
	  };
	  if (BX.UI.Analytics) {
	    BX.UI.Analytics.sendData(analyticsData);
	  } else {
	    // eslint-disable-next-line promise/catch-or-return
	    BX.Runtime.loadExtension('ui.analytics').then(function () {
	      BX.UI.Analytics.sendData(analyticsData);
	    });
	  }
	}
	babelHelpers.defineProperty(Buttons, "cssClass", {
	  hidden: 'socialnetwork-group-create-ex__button-invisible'
	});

	var TypePresetSelector = /*#__PURE__*/function () {
	  function TypePresetSelector() {
	    var _this = this;
	    var buttonsInstance = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, TypePresetSelector);
	    this.cssClass = {
	      container: 'socialnetwork-group-create-ex__type-preset-wrapper',
	      selector: 'socialnetwork-group-create-ex__type-preset-selector'
	    };
	    this.buttonsInstance = buttonsInstance;
	    this.container = document.querySelector(".".concat(this.cssClass.container));
	    if (!this.container) {
	      return;
	    }
	    var firstItemSelected = false;
	    var selectors = this.container.querySelectorAll(".".concat(this.cssClass.selector));
	    selectors.forEach(function (selector) {
	      selector.addEventListener('click', function (e) {
	        var _this$buttonsInstance;
	        var selector = e.currentTarget;
	        if (selector.classList.contains(Util.cssClass.selectorDisabled)) {
	          return;
	        }
	        Util.unselectAllSelectorItems(_this.container, _this.cssClass.selector);
	        Util.selectSelectorItem(selector);
	        var projectType = selector.getAttribute('data-bx-project-type');
	        WorkgroupForm.getInstance().recalcForm({
	          selectedProjectType: projectType
	        });
	        (_this$buttonsInstance = _this.buttonsInstance) === null || _this$buttonsInstance === void 0 ? void 0 : _this$buttonsInstance.updateButtonsByProject(projectType);
	        WorkgroupForm.getInstance().wizardManager.setProjectType(projectType);
	      });
	      var projectType = selector.getAttribute('data-bx-project-type');
	      if (main_core.Type.isStringFilled(WorkgroupForm.getInstance().selectedProjectType)) {
	        if (WorkgroupForm.getInstance().selectedProjectType === projectType) {
	          _this.selectItem(selector);
	        }
	      } else if (!firstItemSelected) {
	        _this.selectItem(selector);
	        firstItemSelected = true;
	      }
	    });
	  }
	  babelHelpers.createClass(TypePresetSelector, [{
	    key: "selectItem",
	    value: function selectItem(selector) {
	      var projectType = selector.getAttribute('data-bx-project-type');
	      Util.selectSelectorItem(selector);
	      WorkgroupForm.getInstance().recalcForm({
	        selectedProjectType: projectType
	      });
	      WorkgroupForm.getInstance().wizardManager.setProjectType(projectType);
	    }
	  }]);
	  return TypePresetSelector;
	}();

	var Wizard = /*#__PURE__*/function () {
	  babelHelpers.createClass(Wizard, null, [{
	    key: "getFirstStepNumber",
	    value: function getFirstStepNumber() {
	      return Object.entries(WorkgroupForm.getInstance().projectTypes).length > 1 ? 1 : 2;
	    }
	  }]);
	  function Wizard(params) {
	    babelHelpers.classCallCheck(this, Wizard);
	    this.processedStep = 0;
	    this.currentStep = params.currentStep;
	    this.stepsCount = params.stepsCount;
	    this.step1BackgroudNode = document.querySelector(".".concat(Wizard.cssClass.step1Backgroud));
	    this.bodyContainer = document.querySelector(".".concat(Wizard.cssClass.bodyContainer));
	    this.breadcrumbsContainer = document.querySelector(".".concat(Wizard.cssClass.breadcrumbsContainer));
	  }
	  babelHelpers.createClass(Wizard, [{
	    key: "showCurrentStep",
	    value: function showCurrentStep() {
	      var _this = this;
	      if (main_core.Type.isDomNode(this.bodyContainer)) {
	        this.bodyContainer.querySelectorAll(".".concat(Wizard.cssClass.bodyItem)).forEach(function (bodyItem) {
	          if (bodyItem.classList.contains("--step-".concat(_this.currentStep))) {
	            bodyItem.classList.add(Wizard.cssClass.activeBodyItem);
	          } else {
	            bodyItem.classList.remove(Wizard.cssClass.activeBodyItem);
	          }
	        });
	      }
	      if (main_core.Type.isDomNode(this.breadcrumbsContainer)) {
	        this.breadcrumbsContainer.querySelectorAll(".".concat(Wizard.cssClass.breadcrumbsItem)).forEach(function (breadcrumbsItem) {
	          if (breadcrumbsItem.classList.contains("--step-".concat(_this.currentStep))) {
	            breadcrumbsItem.classList.add(Wizard.cssClass.activeBreadcrumbsItem);
	          } else {
	            breadcrumbsItem.classList.remove(Wizard.cssClass.activeBreadcrumbsItem);
	          }
	        });
	      }
	      if (this.currentStep === Wizard.getFirstStepNumber() || this.currentStep <= this.processedStep + 1) {
	        Buttons.hideButton(WorkgroupForm.getInstance().buttonsInstance.backButton);
	      } else {
	        if (main_core.Type.isDomNode(this.step1BackgroudNode)) {
	          this.step1BackgroudNode.classList.add("--stop");
	        }
	        Buttons.showButton(WorkgroupForm.getInstance().buttonsInstance.backButton);
	      }
	    }
	  }, {
	    key: "setProjectType",
	    value: function setProjectType(projectType) {
	      var _this2 = this;
	      if (main_core.Type.isDomNode(this.step1BackgroudNode)) {
	        ['project', 'scrum', 'group', 'collab'].forEach(function (projectType) {
	          _this2.step1BackgroudNode.classList.remove("--".concat(projectType));
	        });
	        this.step1BackgroudNode.classList.remove('--stop');
	        this.step1BackgroudNode.classList.add("--".concat(projectType));
	      }
	    }
	  }, {
	    key: "recalcAfterSubmit",
	    value: function recalcAfterSubmit(params) {
	      var processedStep = main_core.Type.isStringFilled(params.processedStep) ? params.processedStep : '';
	      var createdGroupId = parseInt(!main_core.Type.isUndefined(params.createdGroupId) ? params.createdGroupId : 0);
	      var tabInputNode = document.getElementById('TAB');
	      var tabGroupIdNode = document.getElementById('SONET_GROUP_ID');
	      if (!tabInputNode || !main_core.Type.isStringFilled(processedStep) || createdGroupId <= 0) {
	        return;
	      }
	      tabGroupIdNode.value = createdGroupId;
	      if (processedStep === 'create') {
	        this.processedStep = 1;
	        tabInputNode.value = 'edit';
	      } else if (processedStep === 'edit') {
	        this.processedStep = 3;
	        tabInputNode.value = 'invite';
	        this.bodyContainer.querySelectorAll('.socialnetwork-group-create-ex__create--switch-notinviteonly').forEach(function (selector) {
	          selector.classList.add('--inviteonly');
	        });
	      }
	      this.showCurrentStep();
	    }
	  }]);
	  return Wizard;
	}();
	babelHelpers.defineProperty(Wizard, "cssClass", {
	  step1Backgroud: 'socialnetwork-group-create-ex__background-gif',
	  breadcrumbsContainer: 'socialnetwork-group-create-ex__breadcrumbs',
	  breadcrumbsItem: 'socialnetwork-group-create-ex__breadcrumbs-item',
	  bodyContainer: 'socialnetwork-group-create-ex__content',
	  bodyItem: 'socialnetwork-group-create-ex__content-body',
	  activeBodyItem: '--active',
	  activeBreadcrumbsItem: '--active'
	});

	var AlertManager = /*#__PURE__*/function () {
	  function AlertManager(params) {
	    babelHelpers.classCallCheck(this, AlertManager);
	    if (!main_core.Type.isStringFilled(params.errorContainerId)) {
	      return;
	    }
	    this.globalErrorContainer = document.getElementById(params.errorContainerId);
	    this.nodeAlerts = new Map();
	  }
	  babelHelpers.createClass(AlertManager, [{
	    key: "showAlert",
	    value: function showAlert(text, targetNode) {
	      if (main_core.Type.isDomNode(targetNode)) {
	        targetNode.classList.add('ui-ctl-danger');
	      } else {
	        targetNode = this.globalErrorContainer;
	      }
	      var textAlert = new ui_alerts.Alert({
	        color: ui_alerts.Alert.Color.DANGER,
	        animate: true
	      });
	      this.nodeAlerts.set(targetNode, textAlert);
	      setTimeout(function () {
	        targetNode.parentNode.insertBefore(textAlert.getContainer(), targetNode.nextSibling);
	        textAlert.setText(text);
	        window.scrollTo({
	          top: main_core.Dom.getPosition(targetNode).top,
	          behavior: 'smooth'
	        });
	      }, 500);
	    }
	  }, {
	    key: "hideAllAlerts",
	    value: function hideAllAlerts() {
	      this.nodeAlerts.forEach(function (textAlert, targetNode) {
	        textAlert.hide();
	        if (main_core.Type.isDomNode(targetNode)) {
	          targetNode.classList.remove('ui-ctl-danger');
	        }
	      });
	      this.nodeAlerts.clear();
	    }
	  }]);
	  return AlertManager;
	}();

	var TeamManager$$1 = /*#__PURE__*/function () {
	  babelHelpers.createClass(TeamManager$$1, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return TeamManager$$1.instance;
	    }
	  }]);
	  function TeamManager$$1(params) {
	    babelHelpers.classCallCheck(this, TeamManager$$1);
	    this.groupId = parseInt(params.groupId, 10);
	    this.ownerSelector = null;
	    this.scrumMasterSelector = null;
	    this.moderatorsSelector = null;
	    this.usersSelector = null;
	    this.ownerOptions = params.ownerOptions || {};
	    this.scrumMasterOptions = params.scrumMasterOptions || {};
	    this.moderatorsOptions = params.moderatorsOptions || {};
	    this.usersOptions = params.usersOptions || {};
	    this.ownerContainerNode = document.getElementById('GROUP_OWNER_selector');
	    this.scrumMasterContainerNode = document.getElementById('SCRUM_MASTER_selector');
	    this.moderatorsContainerNode = document.getElementById('GROUP_MODERATORS_selector');
	    this.usersContainerNode = document.getElementById('GROUP_USERS_selector');
	    this.isCurrentUserAdmin = main_core.Type.isBoolean(params.isCurrentUserAdmin) ? params.isCurrentUserAdmin : false;
	    this.extranetInstalled = main_core.Type.isBoolean(params.extranetInstalled) ? params.extranetInstalled : false;
	    this.allowExtranet = main_core.Type.isBoolean(params.allowExtranet) ? params.allowExtranet : false;
	    TeamManager$$1.instance = this;
	    this.buildOwnerSelector();
	    this.buildScrumMasterSelector();
	    this.buildModeratorsSelector();
	    this.buildUsersSelector();
	    this.bindEvents();
	  }
	  babelHelpers.createClass(TeamManager$$1, [{
	    key: "buildOwnerSelector",
	    value: function buildOwnerSelector() {
	      if (!main_core.Type.isDomNode(this.ownerContainerNode)) {
	        return;
	      }
	      main_core.Dom.clean(this.ownerContainerNode);
	      var selectorOptions = this.ownerOptions;
	      this.ownerSelector = new ui_entitySelector.TagSelector({
	        id: selectorOptions.selectorId || 'group_create_owner',
	        dialogOptions: {
	          id: selectorOptions.selectorId || 'group_create_owner',
	          offsetLeft: 78,
	          context: TeamManager$$1.contextList.owner,
	          preselectedItems: selectorOptions.value,
	          events: {
	            onLoad: this.onLoad.bind(this),
	            'Item:onSelect': TeamManager$$1.onOwnerSelect,
	            'Item:onDeselect': TeamManager$$1.onOwnerSelect
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              intranetUsersOnly: !this.allowExtranet,
	              inviteEmployeeLink: true,
	              inviteExtranetLink: true,
	              groupId: this.groupId,
	              checkWorkgroupWhenInvite: true,
	              footerInviteIntranetOnly: !this.allowExtranet
	            }
	          }, {
	            id: 'department',
	            options: {
	              selectMode: 'usersOnly'
	            }
	          }]
	        },
	        multiple: false,
	        addButtonCaption: main_core.Loc.getMessage('SONET_GCE_T_ADD_OWNER')
	      });
	      this.ownerSelector.renderTo(this.ownerContainerNode);
	    }
	  }, {
	    key: "buildScrumMasterSelector",
	    value: function buildScrumMasterSelector() {
	      if (!main_core.Type.isDomNode(this.scrumMasterContainerNode)) {
	        return;
	      }
	      main_core.Dom.clean(this.scrumMasterContainerNode);
	      var selectorOptions = this.scrumMasterOptions;
	      this.scrumMasterSelector = new ui_entitySelector.TagSelector({
	        id: selectorOptions.selectorId || 'group_create_scrum_master',
	        dialogOptions: {
	          id: selectorOptions.selectorId || 'group_create_scrum_master',
	          offsetLeft: 78,
	          context: TeamManager$$1.contextList.scrumMaster,
	          preselectedItems: selectorOptions.value,
	          events: {
	            onLoad: this.onLoad.bind(this),
	            'Item:onSelect': TeamManager$$1.onScrumMasterSelect,
	            'Item:onDeselect': TeamManager$$1.onScrumMasterSelect
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              intranetUsersOnly: !this.allowExtranet,
	              inviteEmployeeLink: true,
	              footerInviteIntranetOnly: !this.allowExtranet
	            }
	          }, {
	            id: 'department',
	            options: {
	              selectMode: 'usersOnly'
	            }
	          }]
	        },
	        multiple: false,
	        addButtonCaption: main_core.Loc.getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER'),
	        addButtonCaptionMore: main_core.Loc.getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER_MORE')
	      });
	      this.scrumMasterSelector.renderTo(this.scrumMasterContainerNode);
	    }
	  }, {
	    key: "buildModeratorsSelector",
	    value: function buildModeratorsSelector() {
	      if (!main_core.Type.isDomNode(this.moderatorsContainerNode)) {
	        return;
	      }
	      main_core.Dom.clean(this.moderatorsContainerNode);
	      var selectorOptions = this.moderatorsOptions;
	      this.moderatorsSelector = new ui_entitySelector.TagSelector({
	        id: selectorOptions.selectorId || 'group_create_moderators',
	        dialogOptions: {
	          id: selectorOptions.selectorId || 'group_create_moderators',
	          offsetLeft: 78,
	          context: TeamManager$$1.contextList.moderators,
	          preselectedItems: selectorOptions.value,
	          events: {
	            onLoad: this.onLoad.bind(this),
	            'Item:onSelect': TeamManager$$1.onModeratorsSelect,
	            'Item:onDeselect': TeamManager$$1.onModeratorsSelect
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              intranetUsersOnly: !this.allowExtranet,
	              inviteEmployeeLink: true,
	              groupId: this.groupId,
	              checkWorkgroupWhenInvite: true,
	              footerInviteIntranetOnly: !this.allowExtranet
	            }
	          }, {
	            id: 'department',
	            options: {
	              selectMode: 'usersOnly'
	            }
	          }]
	        },
	        multiple: true,
	        addButtonCaption: main_core.Loc.getMessage('SONET_GCE_T_ADD_USER'),
	        addButtonCaptionMore: main_core.Loc.getMessage('SONET_GCE_T_ADD_USER_MORE')
	      });
	      this.moderatorsSelector.renderTo(this.moderatorsContainerNode);
	    }
	  }, {
	    key: "buildUsersSelector",
	    value: function buildUsersSelector() {
	      if (!main_core.Type.isDomNode(this.usersContainerNode)) {
	        return;
	      }
	      main_core.Dom.clean(this.usersContainerNode);
	      var selectorOptions = this.usersOptions;
	      this.usersSelector = new ui_entitySelector.TagSelector({
	        id: selectorOptions.selectorId || 'group_create_users',
	        dialogOptions: {
	          id: selectorOptions.selectorId || 'group_create_users',
	          offsetLeft: 78,
	          context: TeamManager$$1.contextList.users,
	          preselectedItems: selectorOptions.value,
	          events: {
	            onLoad: this.onLoad.bind(this),
	            'Item:onSelect': TeamManager$$1.onUsersSelect,
	            'Item:onDeselect': TeamManager$$1.onUsersSelect
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              inviteEmployeeLink: true,
	              '!userId': this.isCurrentUserAdmin ? [parseInt(main_core.Loc.getMessage('USER_ID'))] : [],
	              intranetUsersOnly: !this.allowExtranet,
	              groupId: this.groupId,
	              checkWorkgroupWhenInvite: true,
	              footerInviteIntranetOnly: !this.allowExtranet
	            }
	          }, {
	            id: 'department',
	            options: {
	              selectMode: selectorOptions.enableSelectDepartment ? 'usersAndDepartments' : 'usersOnly'
	            }
	          }]
	        },
	        multiple: true,
	        addButtonCaption: main_core.Loc.getMessage('SONET_GCE_T_ADD_USER'),
	        addButtonCaptionMore: main_core.Loc.getMessage('SONET_GCE_T_ADD_USER_MORE')
	      });
	      this.usersSelector.renderTo(this.usersContainerNode);
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      WorkgroupForm.getInstance().subscribe('onSwitchExtranet', this.onSwitchExtranet.bind(this));
	      main_core_events.EventEmitter.emit('BX.Socialnetwork.WorkgroupFormTeamManager::onEventsBinded');
	    }
	  }, {
	    key: "onSwitchExtranet",
	    value: function onSwitchExtranet(event) {
	      var data = event.getData();
	      if (!main_core.Type.isBoolean(data.isChecked)) {
	        return;
	      }
	      this.allowExtranet = this.extranetInstalled && data.isChecked;
	      if (this.ownerSelector && ['DONE', 'UNSENT'].includes(this.ownerSelector.getDialog().loadState)) {
	        this.recalcSelectorByExtranetSwitched({
	          selector: this.ownerSelector,
	          isChecked: data.isChecked,
	          options: this.ownerOptions
	        });
	        this.buildOwnerSelector();
	      }
	      if (this.scrumMasterSelector && ['DONE', 'UNSENT'].includes(this.scrumMasterSelector.getDialog().loadState)) {
	        this.recalcSelectorByExtranetSwitched({
	          selector: this.scrumMasterSelector,
	          isChecked: data.isChecked,
	          options: this.scrumMasterOptions
	        });
	        this.buildScrumMasterSelector();
	      }
	      if (this.moderatorsSelector && ['DONE', 'UNSENT'].includes(this.moderatorsSelector.getDialog().loadState)) {
	        this.recalcSelectorByExtranetSwitched({
	          selector: this.moderatorsSelector,
	          isChecked: data.isChecked,
	          options: this.moderatorsOptions
	        });
	        this.buildModeratorsSelector();
	      }
	      if (this.usersSelector && ['DONE', 'UNSENT'].includes(this.usersSelector.getDialog().loadState)) {
	        this.recalcSelectorByExtranetSwitched({
	          selector: this.usersSelector,
	          isChecked: data.isChecked,
	          options: this.usersOptions
	        });
	        this.buildUsersSelector();
	      }
	    }
	  }, {
	    key: "recalcSelectorByExtranetSwitched",
	    value: function recalcSelectorByExtranetSwitched(params) {
	      var selector = params.selector;
	      var isChecked = params.isChecked;
	      var context = selector.getDialog().getContext();
	      var selectedItems = selector.getDialog().getSelectedItems();
	      if (this.extranetInstalled && !isChecked && main_core.Type.isArray(selectedItems)) {
	        selectedItems = selectedItems.filter(function (item) {
	          return !(item.getEntityId() === 'user' && item.getEntityType() === 'extranet');
	        });
	        switch (context) {
	          case TeamManager$$1.contextList.owner:
	            Util.recalcInputValue({
	              selectedItems: selectedItems,
	              inputNodeName: 'OWNER_CODE',
	              inputContainerNodeId: 'OWNER_CODE_container',
	              multiple: false
	            });
	            break;
	          case TeamManager$$1.contextList.scrumMaster:
	            Util.recalcInputValue({
	              selectedItems: selectedItems,
	              inputNodeName: 'SCRUM_MASTER_CODE',
	              inputContainerNodeId: 'SCRUM_MASTER_CODE_container',
	              multiple: false
	            });
	            break;
	          case TeamManager$$1.contextList.moderators:
	            Util.recalcInputValue({
	              selectedItems: selectedItems,
	              inputNodeName: 'MODERATOR_CODES',
	              inputContainerNodeId: 'MODERATOR_CODES_container',
	              multiple: true
	            });
	            break;
	          case TeamManager$$1.contextList.users:
	            Util.recalcInputValue({
	              selectedItems: selectedItems,
	              inputNodeName: 'USER_CODES',
	              inputContainerNodeId: 'USER_CODES_container',
	              multiple: true
	            });
	            break;
	          default:
	        }
	      }
	      params.options.value = selectedItems.map(function (item) {
	        return [item.getEntityId(), item.getId()];
	      });
	    }
	  }, {
	    key: "onLoad",
	    value: function onLoad(event) {
	      switch (event.getTarget().context) {
	        case TeamManager$$1.contextList.owner:
	          this.recalcSelectorByExtranetSwitched({
	            selector: this.ownerSelector,
	            isChecked: this.allowExtranet,
	            options: this.ownerOptions
	          });
	          break;
	        case TeamManager$$1.contextList.scrumMaster:
	          this.recalcSelectorByExtranetSwitched({
	            selector: this.scrumMasterSelector,
	            isChecked: this.allowExtranet,
	            options: this.scrumMasterOptions
	          });
	          break;
	        case TeamManager$$1.contextList.moderators:
	          this.recalcSelectorByExtranetSwitched({
	            selector: this.moderatorsSelector,
	            isChecked: this.allowExtranet,
	            options: this.moderatorsOptions
	          });
	          if (WorkgroupForm.getInstance().initialFocus === 'addModerator') {
	            this.moderatorsSelector.getAddButtonLink().click();
	          }
	          break;
	        case TeamManager$$1.contextList.users:
	          this.recalcSelectorByExtranetSwitched({
	            selector: this.usersSelector,
	            isChecked: this.allowExtranet,
	            options: this.usersOptions
	          });
	          break;
	        default:
	      }
	    }
	  }], [{
	    key: "onOwnerSelect",
	    value: function onOwnerSelect(event) {
	      Util.recalcInputValue({
	        selectedItems: event.getTarget().getSelectedItems(),
	        inputNodeName: 'OWNER_CODE',
	        inputContainerNodeId: 'OWNER_CODE_container',
	        multiple: false
	      });
	    }
	  }, {
	    key: "onScrumMasterSelect",
	    value: function onScrumMasterSelect(event) {
	      Util.recalcInputValue({
	        selectedItems: event.getTarget().getSelectedItems(),
	        inputNodeName: 'SCRUM_MASTER_CODE',
	        inputContainerNodeId: 'SCRUM_MASTER_CODE_container',
	        multiple: false
	      });
	    }
	  }, {
	    key: "onModeratorsSelect",
	    value: function onModeratorsSelect(event) {
	      Util.recalcInputValue({
	        selectedItems: event.getTarget().getSelectedItems(),
	        inputNodeName: 'MODERATOR_CODES',
	        inputContainerNodeId: 'MODERATOR_CODES_container',
	        multiple: true
	      });
	    }
	  }, {
	    key: "onUsersSelect",
	    value: function onUsersSelect(event) {
	      Util.recalcInputValue({
	        selectedItems: event.getTarget().getSelectedItems(),
	        inputNodeName: 'USER_CODES',
	        inputContainerNodeId: 'USER_CODES_container',
	        multiple: true
	      });
	      var hintNode = document.getElementById('GROUP_ADD_DEPT_HINT_block');
	      if (hintNode) {
	        TeamManager$$1.showDepartmentHint({
	          selectedItems: event.getTarget().getSelectedItems(),
	          hintNode: hintNode
	        });
	      }
	    }
	  }, {
	    key: "showDepartmentHint",
	    value: function showDepartmentHint(params) {
	      var selectedItems = params.selectedItems || {};
	      var hintNode = params.hintNode || null;
	      if (!main_core.Type.isDomNode(hintNode)) {
	        return;
	      }
	      if (!main_core.Type.isArray(selectedItems)) {
	        hintNode.classList.remove('visible');
	        return;
	      }
	      var departmentFound = !main_core.Type.isUndefined(selectedItems.find(function (item) {
	        return item.entityId === 'department';
	      }));
	      if (departmentFound) {
	        hintNode.classList.add('visible');
	      } else {
	        hintNode.classList.remove('visible');
	      }
	    }
	  }]);
	  return TeamManager$$1;
	}();
	babelHelpers.defineProperty(TeamManager$$1, "instance", null);
	babelHelpers.defineProperty(TeamManager$$1, "contextList", {
	  owner: 'GROUP_INVITE_OWNER',
	  scrumMaster: 'GROUP_INVITE_SCRUM_MASTER',
	  moderators: 'GROUP_INVITE_MODERATORS',
	  users: 'GROUP_INVITE'
	});

	var FeaturesManager = function FeaturesManager() {
	  babelHelpers.classCallCheck(this, FeaturesManager);
	  var containerNode = document.getElementById('additional-block-features');
	  if (!containerNode) {
	    return;
	  }
	  containerNode.querySelectorAll('.socialnetwork-group-create-ex__project-instruments--icon-action.--edit').forEach(function (editButton) {
	    editButton.addEventListener('click', function (e) {
	      var editButton = e.currentTarget;
	      var featureNode = editButton.closest('.socialnetwork-group-create-ex__project-instruments--item');
	      if (featureNode) {
	        featureNode.classList.add('--custom-value');
	        var inputNode = featureNode.querySelector('[data-role="feature-input-text"]');
	        var textNode = featureNode.querySelector('[data-role="feature-label"]');
	        if (inputNode && textNode) {
	          inputNode.value = textNode.innerText;
	        }
	      }
	      e.preventDefault();
	    });
	  });
	  containerNode.querySelectorAll('.socialnetwork-group-create-ex__project-instruments--icon-action.--revert').forEach(function (cancelButton) {
	    cancelButton.addEventListener('click', function (e) {
	      var editButton = e.currentTarget;
	      var featureNode = editButton.closest('.socialnetwork-group-create-ex__project-instruments--item');
	      if (featureNode) {
	        featureNode.classList.remove('--custom-value');
	        var inputNode = featureNode.querySelector('[data-role="feature-input-text"]');
	        if (inputNode) {
	          inputNode.value = '';
	        }
	      }
	      e.preventDefault();
	    });
	  });
	};

	var UFManager = function UFManager(params) {
	  babelHelpers.classCallCheck(this, UFManager);
	  if (main_core.Type.isPlainObject(FieldsManager.mandatoryFieldsByStep) && main_core.Type.isArray(FieldsManager.mandatoryFieldsByStep[2]) && main_core.Type.isArray(params.mandatoryUFList)) {
	    params.mandatoryUFList.forEach(function (ufData) {
	      if (!main_core.Type.isStringFilled(ufData.id) || !main_core.Type.isStringFilled(ufData.type)) {
	        return;
	      }
	      FieldsManager.mandatoryFieldsByStep[2].push(ufData);
	    });
	  }
	};

	var fr = 60;
	var v = "5.9.6";
	var ip = 0;
	var op = 299;
	var w = 220;
	var h = 220;
	var nm = "Project";
	var ddd = 0;
	var markers = [];
	var assets = [{
	  nm: "Project",
	  fr: 60,
	  id: "lz9ors5c4atk6fk1",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 5,
	    hd: false,
	    nm: "Project - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 6,
	    hd: false,
	    nm: "Intersect - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [30.5332, 32]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 95.91,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 137.274,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [109.8989, 105.99989999999998]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 95.91,
	          s: [45],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 137.274,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 95.91,
	          s: [175, 175],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 137.274,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 102,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 7,
	    hd: false,
	    nm: "Intersect",
	    sr: 1,
	    parent: 6,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 95.91,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 137.274,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 102,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[61.0663, 61.9827], [30.5919, 64], [0, 61.9664], [2.4298, 50.7784], [14.9827, 41.3945], [18.1659, 39.8181], [21.3138, 38.2496], [21.5058, 35.9819], [23.944, 35.6984], [23.749, 32.9177], [20.8855, 26.8815], [18.7057, 24.319], [18.3477, 22.4563], [19.391, 18.6498], [18.1047, 15.2994], [22.6828, 3.3941], [41.9079, 7.9875], [41.9079, 18.3663], [42.9151, 23.6613], [40.0914, 26.7977], [37.6754, 32.7929], [37.8674, 35.7083], [40.1195, 36.0442], [40.5005, 38.5669], [47.1713, 41.4823], [57.7083, 48.8026], [61.0663, 61.9826]],
	            i: [[9.392600000000002, -1.3001000000000005], [10.7904, 0], [9.4242, 1.3104], [-0.5192, 1.9719], [-5.5026, 2.3159], [-0.877, 0.4883], [-1.4213, 0.6073], [0.0329, 0.7606], [0, 0], [0.5154, 3.3505], [0.127, 5.3346], [0.1241, 3.2328], [0.1628, 0.5949], [-1.7816, 1.1079], [0, 0], [-5.9279, -1.0545], [-1.3468, -11.4149], [0.5301, -3.4409], [2.0232, -5.6352], [1.7102, 0.9142], [2.6937, -0.8688], [0, -0.1857], [0, 0], [-0.443, -0.2466], [-2.3675, -0.6345], [0, -2.6078], [-1.4422, -6.2443]],
	            o: [[-9.392600000000002, 1.3001000000000005], [-10.8354, 0], [0.90059, -4.413699999999999], [1.0677399999999997, -4.054899999999996], [1.4383999999999997, -0.6049999999999969], [0.8588999999999984, -0.47820000000000107], [0.1603999999999992, -0.7456000000000031], [0, 0], [0, 0], [0, 0], [0, 0], [-0.025200000000001666, -0.6465999999999994], [-0.38980000000000103, -1.4250000000000007], [0, 0], [0, 0], [-2.404299999999999, -3.72322], [0.5300999999999974, 3.4409400000000003], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [2.052900000000001, 1.2974000000000032], [6.992699999999999, 1.7348], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 8,
	    hd: false,
	    nm: "stroke - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [30.5332, 32]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [109.8988, 105.9999]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 102,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 9,
	    hd: false,
	    nm: "stroke - Stroke",
	    sr: 1,
	    parent: 8,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 102,
	    op: 300,
	    st: 0,
	    bm: 0,
	    ty: 4,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[61.0663, 61.9827], [30.5919, 64], [0, 61.9664], [2.4298, 50.7784], [14.9827, 41.3945], [18.1659, 39.8181], [21.3138, 38.2496], [21.5058, 35.9819], [23.944, 35.6984], [23.749, 32.9177], [20.8855, 26.8815], [18.7057, 24.319], [18.3477, 22.4563], [19.391, 18.6498], [18.1047, 15.2994], [22.6828, 3.3941], [41.9079, 7.9875], [41.9079, 18.3663], [42.9151, 23.6613], [40.0914, 26.7977], [37.6754, 32.7929], [37.8674, 35.7083], [40.1195, 36.0442], [40.5005, 38.5669], [47.1713, 41.4823], [57.7083, 48.8026], [61.0663, 61.9826]],
	            i: [[9.392600000000002, -1.3001000000000005], [10.7904, 0], [9.4242, 1.3104], [-0.5192, 1.9719], [-5.5026, 2.3159], [-0.877, 0.4883], [-1.4213, 0.6073], [0.0329, 0.7606], [0, 0], [0.5154, 3.3505], [0.127, 5.3346], [0.1241, 3.2328], [0.1628, 0.5949], [-1.7816, 1.1079], [0, 0], [-5.9279, -1.0545], [-1.3468, -11.4149], [0.5301, -3.4409], [2.0232, -5.6352], [1.7102, 0.9142], [2.6937, -0.8688], [0, -0.1857], [0, 0], [-0.443, -0.2466], [-2.3675, -0.6345], [0, -2.6078], [-1.4422, -6.2443]],
	            o: [[-9.392600000000002, 1.3001000000000005], [-10.8354, 0], [0.90059, -4.413699999999999], [1.0677399999999997, -4.054899999999996], [1.4383999999999997, -0.6049999999999969], [0.8588999999999984, -0.47820000000000107], [0.1603999999999992, -0.7456000000000031], [0, 0], [0, 0], [0, 0], [0, 0], [-0.025200000000001666, -0.6465999999999994], [-0.38980000000000103, -1.4250000000000007], [0, 0], [0, 0], [-2.404299999999999, -3.72322], [0.5300999999999974, 3.4409400000000003], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [2.052900000000001, 1.2974000000000032], [6.992699999999999, 1.7348], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "st",
	        c: {
	          a: 0,
	          k: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	        },
	        o: {
	          a: 0,
	          k: 100
	        },
	        w: {
	          a: 0,
	          k: 8
	        },
	        lc: 1,
	        lj: 2,
	        ml: 4,
	        bm: 0,
	        nm: "Stroke",
	        hd: false
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [32.53315, 34]
	        },
	        s: {
	          a: 0,
	          k: [130.1326, 136]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    hasMask: true,
	    masksProperties: [{
	      nm: "Mask",
	      pt: {
	        a: 0,
	        k: {
	          c: true,
	          v: [[61.0663, 61.9827], [30.5919, 64], [0, 61.9664], [2.4298, 50.7784], [14.9827, 41.3945], [18.1659, 39.8181], [21.3138, 38.2496], [21.5058, 35.9819], [23.944, 35.6984], [23.749, 32.9177], [20.8855, 26.8815], [18.7057, 24.319], [18.3477, 22.4563], [19.391, 18.6498], [18.1047, 15.2994], [22.6828, 3.3941], [41.9079, 7.9875], [41.9079, 18.3663], [42.9151, 23.6613], [40.0914, 26.7977], [37.6754, 32.7929], [37.8674, 35.7083], [40.1195, 36.0442], [40.5005, 38.5669], [47.1713, 41.4823], [57.7083, 48.8026], [61.0663, 61.9826]],
	          i: [[9.392600000000002, -1.3001000000000005], [10.7904, 0], [9.4242, 1.3104], [-0.5192, 1.9719], [-5.5026, 2.3159], [-0.877, 0.4883], [-1.4213, 0.6073], [0.0329, 0.7606], [0, 0], [0.5154, 3.3505], [0.127, 5.3346], [0.1241, 3.2328], [0.1628, 0.5949], [-1.7816, 1.1079], [0, 0], [-5.9279, -1.0545], [-1.3468, -11.4149], [0.5301, -3.4409], [2.0232, -5.6352], [1.7102, 0.9142], [2.6937, -0.8688], [0, -0.1857], [0, 0], [-0.443, -0.2466], [-2.3675, -0.6345], [0, -2.6078], [-1.4422, -6.2443]],
	          o: [[-9.392600000000002, 1.3001000000000005], [-10.8354, 0], [0.90059, -4.413699999999999], [1.0677399999999997, -4.054899999999996], [1.4383999999999997, -0.6049999999999969], [0.8588999999999984, -0.47820000000000107], [0.1603999999999992, -0.7456000000000031], [0, 0], [0, 0], [0, 0], [0, 0], [-0.025200000000001666, -0.6465999999999994], [-0.38980000000000103, -1.4250000000000007], [0, 0], [0, 0], [-2.404299999999999, -3.72322], [0.5300999999999974, 3.4409400000000003], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [2.052900000000001, 1.2974000000000032], [6.992699999999999, 1.7348], [0, 0], [0, 0]]
	        }
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      mode: "s",
	      x: {
	        a: 0,
	        k: 0
	      }
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 10,
	    hd: false,
	    nm: "Intersect - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [26.111, 31.9329]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.11],
	            y: [0.99]
	          }
	        }, {
	          t: 169.008,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [128.0162, 105.9329],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.11],
	            y: [0.99]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 169.008,
	          s: [75.7943, 105.9329]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 138,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 11,
	    hd: false,
	    nm: "Intersect",
	    sr: 1,
	    parent: 10,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.11],
	            y: [0.99]
	          }
	        }, {
	          t: 169.008,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 138,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[52.2219, 63.8657], [48.1034, 45.7294], [39.2427, 39.5271], [34.1904, 37.1628], [32.5489, 35.5112], [31.8566, 35.1555], [31.784, 32.653], [37.5802, 30.7904], [39.2213, 30.0753], [38.4221, 28.637], [37.7657, 26.2757], [40.7196, 29.2117], [38.1924, 23.3215], [37.2258, 17.6178], [35.3697, 7.0032], [32.3042, 2.5595], [25.2886, 0], [24.9981, 0], [17.9825, 2.5595], [14.9186, 7.0032], [13.0609, 17.6178], [12.1403, 23.4519], [9.5687, 29.2101], [12.5046, 26.2658], [11.6923, 28.8286], [11.0506, 30.0687], [12.6917, 30.7838], [18.4863, 32.653], [18.4091, 35.2604], [17.7759, 35.5112], [16.9586, 36.3785], [16.1345, 37.1628], [12.175, 39.4527], [1.6192, 47.4034], [0, 54.8028], [52.2219, 63.8657]],
	            i: [[0.3400000000000034, 4.3530000000000015], [0, 0], [5.8802, 1.4698], [1.7263, 1.0993], [0, 0], [0, 0], [0, 0], [0, 0], [-0.146, 0], [0.3003, 0.4596], [0, 0], [-1.1148, -0.8366], [0.6951, 2.0241], [0.1785, 1.9231], [0.8378, 3.4959], [1.4091, 1.1689], [2.5254, 0.1998], [0, 0], [2.0655, -1.4759], [0.5968, -1.736], [0.3975, -3.5739], [0.4597, -1.9171], [1.0156, -1.8437], [-0.8393, 1.1127], [0.1116, -0.3022], [0.1714, -0.4342], [0, 0], [0, 0], [0, 0], [0, 0], [0.4173, -0.2657], [0.0642, -0.3005], [2.3943, -1.0147], [0.8979, -3.4356], [0.6317, -3.0753], [-20.7785, -0.6972]],
	            o: [[-0.3400000000000034, -4.3530000000000015], [0, -2.2094999999999985], [-1.9908000000000001, -0.5375000000000014], [-0.3725999999999985, -0.20889999999999986], [0, 0], [0, 0], [0, 0], [0, 0], [-0.2319000000000031, -0.4980000000000011], [-0.10990000000000322, -0.30049999999999955], [0.8449999999999989, 1.1113], [-0.9872999999999976, -1.8972000000000016], [-0.4649000000000001, -1.873899999999999], [-0.3984000000000023, -3.573599999999999], [-0.5955999999999975, -1.7370599999999996], [-2.0657999999999994, -1.4752], [0, 0], [-2.525500000000001, 0.19909], [-1.4070999999999998, 1.17029], [-0.8397000000000006, 3.49549], [-0.15310000000000024, 1.9662000000000006], [-0.6958000000000002, 1.9883999999999986], [1.1086799999999997, -0.8408000000000015], [0, 0], [-0.2553000000000001, 0.39029999999999987], [0.1443999999999992, 0], [0, 0], [0, 0], [0, 0], [0.014500000000001734, 0.3376999999999981], [-0.3788000000000018, 0.2411999999999992], [-0.8207000000000004, 0.8258999999999972], [-4.62717, 1.9620999999999995], [-0.35633000000000004, 1.3633999999999986], [13.0072, 4.999299999999998], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 12,
	    hd: false,
	    nm: "Intersect - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [26.09, 31.9202]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 169.008,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [92.64150000000001, 105.9202],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [50, 0]
	        }, {
	          t: 169.008,
	          s: [144.82, 105.9202]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 138,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 13,
	    hd: false,
	    nm: "Intersect",
	    sr: 1,
	    parent: 12,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 124.86,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 169.008,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 138,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[52.18, 54.5382], [49.9124, 45.7294], [41.0517, 39.5271], [35.9994, 37.1628], [34.3579, 35.5112], [33.6656, 35.1555], [33.593, 32.653], [39.3892, 30.7904], [41.0303, 30.0753], [40.2311, 28.637], [39.5747, 26.2757], [42.5286, 29.2117], [40.0014, 23.3215], [39.0348, 17.6178], [37.1787, 7.0032], [34.1132, 2.5595], [27.0976, 0], [26.8071, 0], [19.7915, 2.5595], [16.7276, 7.0032], [14.8699, 17.6178], [13.9493, 23.4519], [11.3777, 29.2101], [14.3136, 26.2658], [13.5013, 28.8286], [12.8596, 30.0687], [14.5007, 30.7838], [20.2953, 32.653], [20.2181, 35.2604], [19.5849, 35.5112], [18.7676, 36.3785], [17.9435, 37.1628], [13.984, 39.4527], [3.4282, 47.4034], [0, 63.8403], [52.18, 54.5382]],
	            i: [[1.0968000000000018, 4.5379000000000005], [0, 0], [5.8802, 1.4698], [1.7263, 1.0993], [0, 0], [0, 0], [0, 0], [0, 0], [-0.146, 0], [0.3002, 0.4596], [0, 0], [-1.1148, -0.8366], [0.6951, 2.0241], [0.1785, 1.9231], [0.8378, 3.4959], [1.4091, 1.1689], [2.5253, 0.1998], [0, 0], [2.0655, -1.4759], [0.5968, -1.736], [0.3975, -3.5739], [0.4596, -1.9171], [1.0156, -1.8437], [-0.8393, 1.1127], [0.1116, -0.3022], [0.1714, -0.4342], [0, 0], [0, 0], [0, 0], [0, 0], [0.4173, -0.2657], [0.0642, -0.3005], [2.3943, -1.0147], [0.8979, -3.4356], [0.513, -2.62], [-12.8921, 5.0863]],
	            o: [[-1.0968000000000018, -4.5379000000000005], [0, -2.2094999999999985], [-1.9908000000000001, -0.5375000000000014], [-0.3725999999999985, -0.20889999999999986], [0, 0], [0, 0], [0, 0], [0, 0], [-0.2319000000000031, -0.4980000000000011], [-0.10990000000000322, -0.30049999999999955], [0.8449999999999989, 1.1113], [-0.9872999999999976, -1.8972000000000016], [-0.4650000000000034, -1.873899999999999], [-0.3984000000000023, -3.573599999999999], [-0.5955999999999975, -1.7370599999999996], [-2.065800000000003, -1.4752], [0, 0], [-2.525500000000001, 0.19909], [-1.4070999999999998, 1.17029], [-0.8397000000000006, 3.49549], [-0.15310000000000024, 1.9662000000000006], [-0.6958000000000002, 1.9883999999999986], [1.1085999999999991, -0.8408000000000015], [0, 0], [-0.2553000000000001, 0.39029999999999987], [0.1443999999999992, 0], [0, 0], [0, 0], [0, 0], [0.014500000000001734, 0.3376999999999981], [-0.37879999999999825, 0.2411999999999992], [-0.8206999999999987, 0.8258999999999972], [-4.627190000000001, 1.9620999999999995], [-0.7871099999999998, 3.011800000000001], [20.8472, -0.7633999999999972], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 14,
	    hd: false,
	    nm: "Star 7 - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [10, 10]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 68.466,
	          s: [136, 107],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 89.34,
	          s: [92, 187.9998]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 111.39,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 158.33999999999997,
	          s: [10],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 205.278,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 15,
	    hd: false,
	    nm: "Star 7",
	    sr: 1,
	    parent: 14,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[10, 0], [8.303, 8.303], [0, 10], [8.303, 11.697], [10, 20], [11.697, 11.697], [20, 10], [11.697, 8.303], [10, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 16,
	    hd: false,
	    nm: "Star 5 - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [11, 11]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 68.676,
	          s: [88, 147],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 88.614,
	          s: [43.0002, 190.9998]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 111.75,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 158.688,
	          s: [-5],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 205.566,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 17,
	    hd: false,
	    nm: "Star 5",
	    sr: 1,
	    parent: 16,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 0], [9.1333, 9.1333], [0, 11], [9.1333, 12.8667], [11, 22], [12.8667, 12.8667], [22, 11], [12.8667, 9.1333], [11, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 18,
	    hd: false,
	    nm: "Star 4 - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [16, 16]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 68.76599999999999,
	          s: [132, 104],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 88.76400000000001,
	          s: [188, 39]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 112.11,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 159.108,
	          s: [-7],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 205.91400000000002,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 19,
	    hd: false,
	    nm: "Star 4",
	    sr: 1,
	    parent: 18,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[16, 0], [13.2848, 13.2848], [0, 16], [13.2848, 18.7152], [16, 32], [18.7152, 18.7152], [32, 16], [18.7152, 13.2848], [16, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 20,
	    hd: false,
	    nm: "Star 6 - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [20, 20]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 68.922,
	          s: [97, 88],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 88.76400000000001,
	          s: [39, 19]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 112.536,
	          s: [0],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 159.40800000000002,
	          s: [8],
	          o: {
	            x: [0.25],
	            y: [0.1]
	          },
	          i: {
	            x: [0.25],
	            y: [1]
	          }
	        }, {
	          t: 206.364,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 21,
	    hd: false,
	    nm: "Star 6",
	    sr: 1,
	    parent: 20,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 30,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20, 0], [16.606, 16.606], [0, 20], [16.606, 23.394], [20, 40], [23.394, 23.394], [40, 20], [23.394, 16.606], [20, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 22,
	    hd: false,
	    nm: "Path - Null",
	    sr: 1,
	    parent: 5,
	    ks: {
	      a: {
	        a: 0,
	        k: [80.5, 77]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 5.706,
	          s: [277.5, 290],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.55],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 19.104,
	          s: [110.5, 119]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 6.252,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.55],
	            y: [1]
	          }
	        }, {
	          t: 24.936,
	          s: [10],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.55],
	            y: [1]
	          }
	        }, {
	          t: 43.632,
	          s: [-20],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.55],
	            y: [1]
	          }
	        }, {
	          t: 53.346000000000004,
	          s: [2.2961],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.55],
	            y: [1]
	          }
	        }, {
	          t: 59.477999999999994,
	          s: [0]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 300,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 23,
	    hd: false,
	    nm: "Path",
	    sr: 1,
	    parent: 22,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 300,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11.9293, 13.1586], [149.0946, 13.1586], [149.0946, 115.7436], [131.0732, 122.739], [127, 154], [92.9685, 126.1859], [11.9293, 115.7436], [11.9293, 13.1586]],
	            i: [[-16.23948, 18.09532], [-14.5448, -16.9854], [17.1492, -15.8942], [8.3532, -1.6702], [3.0176, 0], [11.9325, 10.6368], [8.91, 6.8561], [-16.2395, 18.0953]],
	            o: [[16.23948, -18.09532], [14.54531, 16.98538], [-3.2580199999999877, 3.020169999999993], [-0.18308999999999287, 10.259179999999986], [-2.750489999999999, 0], [-34.10755, 0.6280299999999954], [-15.56835, -11.978949999999998], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 1,
	          k: [{
	            t: 17.855999999999998,
	            s: [70],
	            o: {
	              x: [0.5],
	              y: [0]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 47.604,
	            s: [100]
	          }]
	        },
	        c: {
	          a: 1,
	          k: [{
	            t: 17.855999999999998,
	            s: [0.6, 0.8901960784313725, 0.23137254901960785, 1],
	            o: {
	              x: [0.5],
	              y: [0]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 47.604,
	            s: [0.6, 0.8901960784313725, 0.23137254901960785, 1]
	          }]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}];
	var layers = [{
	  ty: 3,
	  ddd: 0,
	  ind: 5,
	  hd: false,
	  nm: "Project - Null",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    o: {
	      a: 0,
	      k: 100
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    }
	  },
	  ao: 0,
	  ip: 0,
	  op: 300,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 0,
	  nm: "Project",
	  refId: "lz9ors5c4atk6fk1",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    o: {
	      a: 0,
	      k: 100
	    }
	  },
	  ao: 0,
	  w: 220,
	  h: 220,
	  ip: 0,
	  op: 300,
	  st: 0,
	  hd: false,
	  bm: 0
	}];
	var meta = {
	  a: "",
	  d: "",
	  tc: "",
	  g: "Aninix"
	};
	var projectLottieIconInfo = {
	  fr: fr,
	  v: v,
	  ip: ip,
	  op: op,
	  w: w,
	  h: h,
	  nm: nm,
	  ddd: ddd,
	  markers: markers,
	  assets: assets,
	  layers: layers,
	  meta: meta
	};

	var fr$1 = 60;
	var v$1 = "5.9.6";
	var ip$1 = 0;
	var op$1 = 228.69199999999998;
	var w$1 = 220;
	var h$1 = 220;
	var nm$1 = "Scrum";
	var ddd$1 = 0;
	var markers$1 = [];
	var assets$1 = [{
	  nm: "[FRAME] Scrum - Null / Frame 386 - Null / Frame 385 - Null / 02 - Null / 02",
	  fr: 60,
	  id: "lz9or3o0yt7oupgc",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 13,
	    hd: false,
	    nm: "Scrum - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 14,
	    hd: false,
	    nm: "Frame 386 - Null",
	    sr: 1,
	    parent: 13,
	    ks: {
	      a: {
	        a: 0,
	        k: [80, 80]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 15.372,
	          s: [-19.900000000000006, 110],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 56.064,
	          s: [110, 110],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 176.22,
	          s: [110, 110],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 205.458,
	          s: [246, 110]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 63.19199999999999,
	          s: [-1],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 170.02800000000002,
	          s: [-360]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 15,
	    hd: false,
	    nm: "Frame 385 - Null",
	    sr: 1,
	    parent: 14,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [67, 114]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 16,
	    hd: false,
	    nm: "02 - Null",
	    sr: 1,
	    parent: 15,
	    ks: {
	      a: {
	        a: 0,
	        k: [14.5, 22.5]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [16.5, 24.5]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 17,
	    hd: false,
	    nm: "02",
	    sr: 1,
	    parent: 16,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5556, 35.5539], [1.5556, 28.5712], [4.3672, 25.8366], [7.1788, 23.102], [7.1788, 18.5533], [7.1788, 14.0046], [4.3287, 11.2168], [1.4786, 8.429], [1.4786, 1.4463], [8.6174, 1.4463], [12.0844, 4.8375], [15.5514, 8.2287], [22.4853, 15.011], [22.5217, 15.0463], [24.0002, 18.5378], [22.5217, 22.0293], [22.4853, 22.0646], [15.59, 28.8093], [12.1423, 32.1816], [8.6946, 35.5539], [1.5558, 35.5539]],
	            i: [[1.97133, 1.9282200000000032], [-1.9713, 1.9282], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-1.9713, 1.9282], [-1.9713, -1.9282], [-1.3539, -1.3243], [0, 0], [0, 0], [0, 0], [0, -1.2637], [0.9857, -0.9642], [0, 0], [2.6928, -2.634], [0, 0], [0, 0], [1.9713, 1.9282]],
	            o: [[-1.97133, -1.9282200000000032], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-1.97132, -1.9282199999999996], [1.9713199999999997, -1.92822], [1.35393, 1.3243299999999998], [0, 0], [0, 0], [0, 0], [0.985710000000001, 0.9641600000000015], [0.00005000000000165983, 1.2636900000000004], [0, 0], [-2.692800000000002, 2.633960000000002], [0, 0], [0, 0], [-1.97131, 1.9282299999999992], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] Scrum - Null / Frame 386 - Null / Frame 385",
	  fr: 60,
	  id: "lz9or3ny6yxegwz9",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 18,
	    hd: false,
	    nm: "Scrum - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 19,
	    hd: false,
	    nm: "Frame 386 - Null",
	    sr: 1,
	    parent: 18,
	    ks: {
	      a: {
	        a: 0,
	        k: [80, 80]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 15.372,
	          s: [-19.900000000000006, 110],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 56.064,
	          s: [110, 110],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 176.22,
	          s: [110, 110],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 205.458,
	          s: [246, 110]
	        }]
	      },
	      r: {
	        a: 1,
	        k: [{
	          t: 63.19199999999999,
	          s: [-1],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 170.02800000000002,
	          s: [-360]
	        }]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 20,
	    ty: 0,
	    nm: "Frame 385",
	    refId: "lz9or3o0yt7oupgc",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 220,
	    h: 220,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    hd: false,
	    bm: 0
	  }]
	}, {
	  nm: "Scrum",
	  fr: 60,
	  id: "lz9or3nxfohhtv6i",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 21,
	    hd: false,
	    nm: "Scrum - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 22,
	    ty: 0,
	    nm: "Frame 386",
	    refId: "lz9or3ny6yxegwz9",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 220,
	    h: 220,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 23,
	    hd: false,
	    nm: "s stroke - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 0
	      },
	      p: {
	        a: 0,
	        k: [110, 159.5]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 24,
	    hd: false,
	    nm: "s stroke - Stroke",
	    sr: 1,
	    parent: 23,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    ty: 4,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: false,
	            v: [[0, 0], [0, 0]],
	            i: [[0, 0], [0, 0]],
	            o: [[0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "st",
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        o: {
	          a: 0,
	          k: 100
	        },
	        w: {
	          a: 0,
	          k: 11
	        },
	        lc: 1,
	        lj: 1,
	        ml: 4,
	        bm: 0,
	        nm: "Stroke",
	        hd: false
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [5.75, 5.5]
	        },
	        s: {
	          a: 0,
	          k: [23, 22]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 25,
	    hd: false,
	    nm: "Ellipse 5177 - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [55.5, 55.5]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [110.5, 110.5]
	      },
	      r: {
	        a: 0,
	        k: 90
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 26,
	    hd: false,
	    nm: "Ellipse 5177 - Stroke",
	    sr: 1,
	    parent: 25,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    ty: 4,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[110, 55], [55, 110], [0, 55], [55, 0], [110, 55]],
	            i: [[0, -30.376499999999993], [30.3765, 0], [0, 30.3765], [-30.3765, 0], [0, -30.3765]],
	            o: [[0, 30.376499999999993], [-30.3765, 0], [0, -30.3765], [30.376499999999993, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "st",
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        o: {
	          a: 0,
	          k: 100
	        },
	        w: {
	          a: 0,
	          k: 11
	        },
	        lc: 2,
	        lj: 1,
	        ml: 4,
	        bm: 0,
	        nm: "Stroke",
	        hd: false
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "tm",
	      s: {
	        a: 1,
	        k: [{
	          t: 65.04,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 168.51,
	          s: [0]
	        }]
	      },
	      e: {
	        a: 1,
	        k: [{
	          t: 110.112,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 183.978,
	          s: [0]
	        }]
	      },
	      o: {
	        a: 0,
	        k: 0
	      },
	      m: 1
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [60.5, 60.5]
	        },
	        s: {
	          a: 0,
	          k: [242, 242]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 27,
	    hd: false,
	    nm: "f stroke2 - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 182.604,
	          s: [103.84, 165],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 213.768,
	          s: [231, 165]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 28,
	    hd: false,
	    nm: "f stroke2 - Stroke",
	    sr: 1,
	    parent: 27,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    ty: 4,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 174.63,
	            s: [{
	              c: false,
	              v: [[0, 0], [0, 0]],
	              i: [[0, 0], [0, 0]],
	              o: [[0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [1],
	              y: [1]
	            }
	          }, {
	            t: 182.70600000000002,
	            s: [{
	              c: false,
	              v: [[0, 0], [44.7344, 0]],
	              i: [[0, 0], [0, 0]],
	              o: [[0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "st",
	        c: {
	          a: 1,
	          k: [{
	            t: 166.62,
	            s: [1, 1, 1, 1],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [1],
	              y: [1]
	            }
	          }, {
	            t: 176.832,
	            s: [1, 1, 1, 1]
	          }]
	        },
	        o: {
	          a: 1,
	          k: [{
	            t: 166.62,
	            s: [0],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [1],
	              y: [1]
	            }
	          }, {
	            t: 176.832,
	            s: [100]
	          }]
	        },
	        w: {
	          a: 0,
	          k: 11
	        },
	        lc: 2,
	        lj: 1,
	        ml: 4,
	        bm: 0,
	        nm: "Stroke",
	        hd: false
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [55.5, 5.5]
	        },
	        s: {
	          a: 0,
	          k: [222, 22]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 29,
	    hd: false,
	    nm: "f stroke - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [100, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 115.00200000000001,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 121.19399999999999,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 18.588,
	          s: [-8, 165],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 53.94,
	          s: [109, 165],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 177.966,
	          s: [109, 165],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 193.008,
	          s: [109, 165]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 30,
	    hd: false,
	    nm: "f stroke - Stroke",
	    sr: 1,
	    parent: 29,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 115.00200000000001,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 121.19399999999999,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    ty: 4,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 80.34,
	            s: [{
	              c: false,
	              v: [[-4, 0], [100, 0]],
	              i: [[0, 0], [0, 0]],
	              o: [[0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.25],
	              y: [0.1]
	            },
	            i: {
	              x: [0.25],
	              y: [1]
	            }
	          }, {
	            t: 131.268,
	            s: [{
	              c: false,
	              v: [[100, 0], [100, 0]],
	              i: [[0, 0], [0, 0]],
	              o: [[0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "st",
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        o: {
	          a: 0,
	          k: 100
	        },
	        w: {
	          a: 0,
	          k: 11
	        },
	        lc: 2,
	        lj: 1,
	        ml: 4,
	        bm: 0,
	        nm: "Stroke",
	        hd: false
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [55.5, 5.5]
	        },
	        s: {
	          a: 0,
	          k: [222, 22]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 31,
	    hd: false,
	    nm: "back - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [80, 80]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [110, 110]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 5.964,
	          s: [100, 100],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 12.99,
	          s: [111.58999999999999, 111.58999999999999],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 17.904,
	          s: [110.00000000000001, 110.00000000000001],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 28.416,
	          s: [98.41, 98.41],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 35.778000000000006,
	          s: [100.03999999999999, 100.03999999999999]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 32,
	    hd: false,
	    nm: "back",
	    sr: 1,
	    parent: 31,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[160, 80], [80, 160], [0, 80], [80, 0], [160, 80]],
	            i: [[0, -44.184], [44.184, 0], [0, 44.184], [-44.184, 0], [0, -44.184]],
	            o: [[0, 44.184], [-44.184, 0], [0, -44.184], [44.184, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 1,
	          k: [{
	            t: 6.1080000000000005,
	            s: [70],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 36.312,
	            s: [100]
	          }]
	        },
	        c: {
	          a: 1,
	          k: [{
	            t: 6.1080000000000005,
	            s: [0.07450980392156863, 0.8862745098039215, 0.8392156862745098, 1],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 36.312,
	            s: [0.07450980392156863, 0.8862745098039215, 0.8392156862745098, 1]
	          }]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 33,
	    hd: false,
	    nm: "round - Null",
	    sr: 1,
	    parent: 21,
	    ks: {
	      a: {
	        a: 0,
	        k: [96, 96]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [110, 110]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 6.21,
	          s: [100, 100],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 13.248,
	          s: [111.58999999999999, 111.58999999999999],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 18.174,
	          s: [110.00000000000001, 110.00000000000001],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 28.692,
	          s: [98.41, 98.41],
	          o: {
	            x: [0.33],
	            y: [0]
	          },
	          i: {
	            x: [0.67],
	            y: [1]
	          }
	        }, {
	          t: 36.054,
	          s: [100.03999999999999, 100.03999999999999]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 34,
	    hd: false,
	    nm: "round",
	    sr: 1,
	    parent: 33,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 229.69199999999998,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 4,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[192, 96], [96, 192], [0, 96], [96, 0], [192, 96]],
	            i: [[0, -53.01933], [53.0193, 0], [0, 53.0193], [-53.0193, 0], [0, -53.0193]],
	            o: [[0, 53.01933], [-53.01934, 0], [0, -53.01934], [53.01933, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[96, 186], [186, 96], [96, 6], [6, 96], [96, 186]],
	            i: [[-49.705629999999985, 0], [0, 49.7056], [49.7056, 0], [0, -49.7056], [-49.7056, 0]],
	            o: [[49.705629999999985, 0], [0, -49.70563], [-49.70563, 0], [0, 49.705629999999985], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 1,
	          k: [{
	            t: 6.1080000000000005,
	            s: [70],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 52.002,
	            s: [100]
	          }]
	        },
	        c: {
	          a: 1,
	          k: [{
	            t: 6.1080000000000005,
	            s: [0.07450980392156863, 0.8862745098039215, 0.8392156862745098, 1],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 52.002,
	            s: [0.07450980392156863, 0.8862745098039215, 0.8392156862745098, 1]
	          }]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}];
	var layers$1 = [{
	  ty: 3,
	  ddd: 0,
	  ind: 21,
	  hd: false,
	  nm: "Scrum - Null",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    o: {
	      a: 0,
	      k: 100
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    }
	  },
	  ao: 0,
	  ip: 0,
	  op: 229.69199999999998,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 0,
	  nm: "Scrum",
	  refId: "lz9or3nxfohhtv6i",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    o: {
	      a: 0,
	      k: 100
	    }
	  },
	  ao: 0,
	  w: 220,
	  h: 220,
	  ip: 0,
	  op: 229.69199999999998,
	  st: 0,
	  hd: false,
	  bm: 0
	}];
	var meta$1 = {
	  a: "",
	  d: "",
	  tc: "",
	  g: "Aninix"
	};
	var scrumLottieIconInfo = {
	  fr: fr$1,
	  v: v$1,
	  ip: ip$1,
	  op: op$1,
	  w: w$1,
	  h: h$1,
	  nm: nm$1,
	  ddd: ddd$1,
	  markers: markers$1,
	  assets: assets$1,
	  layers: layers$1,
	  meta: meta$1
	};

	var _templateObject$1, _templateObject2, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _projectLottieAnimation = /*#__PURE__*/new WeakMap();
	var _projectLottieIconContainer = /*#__PURE__*/new WeakMap();
	var _scrumLottieAnimation = /*#__PURE__*/new WeakMap();
	var _scrumLottieIconContainer = /*#__PURE__*/new WeakMap();
	var _renderScrumDemoInfoContent = /*#__PURE__*/new WeakSet();
	var _renderProjectDemoInfoContent = /*#__PURE__*/new WeakSet();
	var _getLottieScrum = /*#__PURE__*/new WeakSet();
	var _getLottieProject = /*#__PURE__*/new WeakSet();
	var _bindStartWorkBtn = /*#__PURE__*/new WeakSet();
	var WorkgroupForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(WorkgroupForm, _EventEmitter);
	  babelHelpers.createClass(WorkgroupForm, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return WorkgroupForm.instance;
	    }
	  }]);
	  function WorkgroupForm(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, WorkgroupForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WorkgroupForm).call(this));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _bindStartWorkBtn);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getLottieProject);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getLottieScrum);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _renderProjectDemoInfoContent);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _renderScrumDemoInfoContent);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _projectLottieAnimation, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _projectLottieIconContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _scrumLottieAnimation, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _scrumLottieIconContainer, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.WorkgroupForm');
	    _this.componentName = params.componentName;
	    _this.signedParameters = params.signedParameters;
	    _this.userSelector = '';
	    _this.lastAction = 'invite';
	    _this.animationList = {};
	    _this.selectedTypeCode = false;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _projectLottieAnimation, null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _projectLottieIconContainer, null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _scrumLottieAnimation, null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _scrumLottieIconContainer, null);
	    _this.groupId = parseInt(params.groupId);
	    _this.isScrumProject = params.isScrumProject;
	    _this.config = params.config;
	    _this.avatarUploaderId = params.avatarUploaderId;
	    _this.themePickerData = params.themePickerData;
	    _this.projectOptions = params.projectOptions;
	    _this.projectTypes = params.projectTypes;
	    _this.confidentialityTypes = params.confidentialityTypes;
	    _this.selectedProjectType = params.selectedProjectType;
	    _this.selectedConfidentialityType = params.selectedConfidentialityType;
	    _this.initialFocus = main_core.Type.isStringFilled(params.focus) ? params.focus : '';
	    _this.culture = params.culture ? params.culture : {};
	    _this.currentUserType = params.currentUserType;
	    _this.demoInfoAlreadyBeenShown = false;
	    _this.scrumManager = new Scrum({
	      isScrumProject: _this.isScrumProject
	    });
	    _this.wizardManager = new Wizard({
	      currentStep: Object.entries(_this.projectTypes).length > 1 ? 1 : 2,
	      stepsCount: params.stepsCount > 1 ? params.stepsCount : 1
	    });
	    _this.alertManager = new AlertManager({
	      errorContainerId: 'sonet_group_create_error_block'
	    });
	    WorkgroupForm.instance = babelHelpers.assertThisInitialized(_this);
	    _this.buttonsInstance = new Buttons();
	    _this.init(params);
	    return _this;
	  }
	  babelHelpers.createClass(WorkgroupForm, [{
	    key: "init",
	    value: function init(params) {
	      this.scrumManager.makeAdditionalCustomizationForm();
	      if (this.groupId <= 0) {
	        this.recalcForm();
	      }
	      new Avatar({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        groupId: this.groupId
	      });
	      if (main_core.Type.isPlainObject(params.themePickerData) && document.getElementById('GROUP_THEME_container')) {
	        new ThemePicker({
	          container: document.getElementById('GROUP_THEME_container'),
	          theme: params.themePickerData
	        });
	      }
	      new DateCorrector({
	        culture: this.culture
	      });
	      if (document.getElementById('group-tags-bind-node')) {
	        new Tags({
	          groupId: this.groupId,
	          containerNodeId: 'group-tags-bind-node',
	          hiddenFieldId: 'GROUP_KEYWORDS'
	        });
	      }
	      new TypePresetSelector(this.buttonsInstance);
	      new ConfidentialitySelector();
	      new FeaturesManager();
	      if (main_core.Type.isStringFilled(this.initialFocus)) {
	        if (this.initialFocus === 'description') {
	          var groupDescriptionNode = document.getElementById('GROUP_DESCRIPTION_input');
	          if (groupDescriptionNode) {
	            groupDescriptionNode.focus();
	          }
	        }
	      } else {
	        var groupNameNode = document.getElementById('GROUP_NAME_input');
	        if (groupNameNode) {
	          groupNameNode.focus();
	        }
	      }
	      this.bindEvents();
	      Util.initExpandSwitches();
	      Util.initDropdowns();
	      if (main_core.Type.isStringFilled(params.expandableSettingsNodeId)) {
	        BX.UI.Hint.init(document.getElementById(params.expandableSettingsNodeId));
	      }
	      if (this.groupId <= 0 && this.selectedProjectType === 'scrum') {
	        this.saveScrumAnalyticData();
	      }
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this2 = this;
	      if (BX.SidePanel.Instance.getTopSlider()) {
	        main_core_events.EventEmitter.subscribe(BX.SidePanel.Instance.getTopSlider().getWindow(), 'SidePanel.Slider:onClose', function (event) {
	          setTimeout(function () {
	            var sliderInstance = event.getTarget();
	            if (!sliderInstance) {
	              return;
	            }
	            BX.SidePanel.Instance.destroy(sliderInstance.getUrl());
	          }, 500);
	        });
	      }
	      var extranetCheckboxNode = document.getElementById('IS_EXTRANET_GROUP');
	      if (extranetCheckboxNode && extranetCheckboxNode.type === 'checkbox') {
	        extranetCheckboxNode.addEventListener('click', function () {
	          _this2.switchExtranet(extranetCheckboxNode.checked);
	        });
	      }
	      var visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
	      if (visibleCheckboxNode && visibleCheckboxNode.type === 'checkbox') {
	        visibleCheckboxNode.addEventListener('click', function () {
	          _this2.switchNotVisible(visibleCheckboxNode.checked);
	        });
	      }
	      var projectCheckboxNode = document.getElementById('GROUP_PROJECT');
	      if (projectCheckboxNode && projectCheckboxNode.type === 'checkbox') {
	        projectCheckboxNode.addEventListener('click', function () {
	          Util.recalcFormPartProject(projectCheckboxNode.checked);
	        });
	      }
	      main_core_events.EventEmitter.subscribe('BX.Socialnetwork.WorkgroupFormTeamManager::onEventsBinded', this.recalcFormDependencies.bind(this));
	    }
	  }, {
	    key: "recalcForm",
	    value: function recalcForm(params) {
	      if (main_core.Type.isPlainObject(params)) {
	        if (!main_core.Type.isUndefined(params.selectedProjectType)) {
	          this.selectedProjectType = main_core.Type.isStringFilled(params.selectedProjectType) ? params.selectedProjectType : '';
	        }
	        if (!main_core.Type.isUndefined(params.selectedConfidentialityType)) {
	          this.selectedConfidentialityType = main_core.Type.isStringFilled(params.selectedConfidentialityType) ? params.selectedConfidentialityType : '';
	        }
	      }
	      if (this.groupId <= 0) {
	        this.scrumManager.isScrumProject = main_core.Type.isPlainObject(this.projectTypes[this.selectedProjectType]) && main_core.Type.isStringFilled(this.projectTypes[this.selectedProjectType]['SCRUM_PROJECT']) && this.projectTypes[this.selectedProjectType]['SCRUM_PROJECT'] === 'Y';
	        Util.recalcFormPartProject(main_core.Type.isPlainObject(this.projectTypes[this.selectedProjectType]) && main_core.Type.isStringFilled(this.projectTypes[this.selectedProjectType].PROJECT) && this.projectTypes[this.selectedProjectType].PROJECT === 'Y');
	      }
	      this.scrumManager.makeAdditionalCustomizationForm();
	      if (this.groupId <= 0) {
	        var openedCheckboxNode = document.getElementById('GROUP_OPENED');
	        if (openedCheckboxNode) {
	          Util.setCheckedValue(openedCheckboxNode, main_core.Type.isPlainObject(this.confidentialityTypes[this.selectedConfidentialityType]) && main_core.Type.isStringFilled(this.confidentialityTypes[this.selectedConfidentialityType].OPENED) && this.confidentialityTypes[this.selectedConfidentialityType].OPENED === 'Y');
	        }
	        var visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
	        if (visibleCheckboxNode) {
	          Util.setCheckedValue(visibleCheckboxNode, main_core.Type.isPlainObject(this.confidentialityTypes[this.selectedConfidentialityType]) && main_core.Type.isStringFilled(this.confidentialityTypes[this.selectedConfidentialityType].VISIBLE) && this.confidentialityTypes[this.selectedConfidentialityType].VISIBLE === 'Y');
	        }
	      }
	      this.recalcFormDependencies();
	    }
	  }, {
	    key: "recalcFormDependencies",
	    value: function recalcFormDependencies() {
	      var extranetCheckboxNode = document.getElementById('IS_EXTRANET_GROUP');
	      if (extranetCheckboxNode) {
	        this.switchExtranet(Util.getCheckedValue(extranetCheckboxNode));
	      }
	      var visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
	      if (visibleCheckboxNode) {
	        this.switchNotVisible(visibleCheckboxNode.checked);
	      }
	    }
	  }, {
	    key: "switchExtranet",
	    value: function switchExtranet(isChecked) {
	      this.emit('onSwitchExtranet', new main_core_events.BaseEvent({
	        data: {
	          isChecked: isChecked
	        }
	      }));
	      var openedBlock = document.getElementById('GROUP_OPENED');
	      if (openedBlock) {
	        if (!isChecked) {
	          if (openedBlock.type === 'checkbox') {
	            openedBlock.disabled = false;
	          }
	        } else {
	          if (openedBlock.type === 'checkbox') {
	            openedBlock.disabled = true;
	            openedBlock.checked = false;
	          } else {
	            openedBlock.value = 'N';
	          }
	        }
	      }
	      var visibleBlock = document.getElementById('GROUP_VISIBLE');
	      if (visibleBlock) {
	        if (!isChecked) {
	          if (visibleBlock.type == 'checkbox') {
	            visibleBlock.disabled = false;
	          }
	        } else {
	          if (visibleBlock.type == 'checkbox') {
	            visibleBlock.disabled = true;
	            visibleBlock.checked = false;
	          } else {
	            visibleBlock.value = 'N';
	          }
	        }
	        this.switchNotVisible(visibleBlock.checked);
	      }
	    }
	  }, {
	    key: "switchNotVisible",
	    value: function switchNotVisible(isChecked) {
	      var openedNode = document.getElementById('GROUP_OPENED');
	      if (openedNode && openedNode.type == 'checkbox') {
	        if (isChecked) {
	          openedNode.disabled = false;
	        } else {
	          openedNode.disabled = true;
	          openedNode.checked = false;
	        }
	      }
	    }
	  }, {
	    key: "submitForm",
	    value: function submitForm(e) {
	      var _this3 = this;
	      var actionUrl = document.getElementById('sonet_group_create_popup_form').action;
	      if (actionUrl) {
	        var groupIdNode = document.getElementById('SONET_GROUP_ID');
	        var b24statAction = 'addSonetGroup';
	        if (groupIdNode) {
	          if (parseInt(groupIdNode.value) <= 0) {
	            actionUrl = main_core.Uri.addParam(actionUrl, {
	              action: 'createGroup',
	              groupType: this.selectedTypeCode
	            });
	          } else {
	            b24statAction = 'editSonetGroup';
	          }
	        }
	        actionUrl = main_core.Uri.addParam(actionUrl, {
	          b24statAction: b24statAction
	        });
	        var formElements = document.forms['sonet_group_create_popup_form'].elements;
	        if (formElements.GROUP_PROJECT && (formElements.IS_EXTRANET_GROUP || formElements.GROUP_OPENED)) {
	          var b24statType = formElements.GROUP_PROJECT.checked ? 'project-' : 'group-';
	          if (formElements.IS_EXTRANET_GROUP && formElements.IS_EXTRANET_GROUP.checked) {
	            b24statType += 'external';
	          } else {
	            b24statType += formElements.GROUP_OPENED.checked ? 'open' : 'closed';
	          }
	          actionUrl = main_core.Uri.addParam(actionUrl, {
	            b24statType: b24statType
	          });
	        }
	        if (formElements.SCRUM_PROJECT && b24statAction === 'addSonetGroup') {
	          actionUrl = main_core.Uri.addParam(actionUrl, {
	            analyticsLabel: {
	              scrum: 'Y',
	              action: 'scrum_create'
	            }
	          });
	        }
	        Buttons.showWaitSubmitButton(true);
	        main_core.ajax.submitAjax(document.forms['sonet_group_create_popup_form'], {
	          url: actionUrl,
	          method: 'POST',
	          dataType: 'json',
	          data: {
	            PROJECT_OPTIONS: this.projectOptions
	          },
	          onsuccess: function onsuccess(response) {
	            if (main_core.Type.isStringFilled(response.ERROR)) {
	              var warningText = main_core.Type.isStringFilled(response.WARNING) ? "".concat(response.WARNING, "<br>") : '';
	              _this3.alertManager.showAlert("".concat(warningText).concat(response.ERROR));
	              if (main_core.Type.isStringFilled(response.WIZARD_STEP_PROCESSED)) {
	                _this3.wizardManager.recalcAfterSubmit({
	                  processedStep: response.WIZARD_STEP_PROCESSED.toLowerCase(),
	                  createdGroupId: parseInt(!main_core.Type.isUndefined(response.CREATED_GROUP_ID) ? response.CREATED_GROUP_ID : 0)
	                });
	              }
	              if (main_core.Type.isArray(response.SUCCESSFULL_USERS_ID) && response.SUCCESSFULL_USERS_ID.length > 0) {
	                response.SUCCESSFULL_USERS_ID = response.SUCCESSFULL_USERS_ID.map(function (userId) {
	                  return Number(userId);
	                });
	                var usersSelector = TeamManager$$1.getInstance().usersSelector;
	                var usersSelectorDialog = usersSelector ? usersSelector.getDialog() : null;
	                if (usersSelectorDialog) {
	                  usersSelectorDialog.getSelectedItems().forEach(function (item) {
	                    if (item.entityId === 'user' && response.SUCCESSFULL_USERS_ID.includes(item.id)) {
	                      item.deselect();
	                    }
	                  });
	                }
	                window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	                  code: 'afterInvite',
	                  data: {}
	                });
	              }
	              Buttons.showWaitSubmitButton(false);
	            } else if (response.MESSAGE === 'SUCCESS') {
	              var currentSlider = BX.SidePanel.Instance.getSliderByWindow(window);
	              if (currentSlider) {
	                var event = new main_core_events.BaseEvent({
	                  compatData: [currentSlider.getEvent('onClose')],
	                  data: currentSlider.getEvent('onClose')
	                });
	                main_core_events.EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', event);
	              }
	              if (window === top.window)
	                // not frame
	                {
	                  if (main_core.Type.isStringFilled(response.URL)) {
	                    top.location.href = response.URL;
	                  }
	                } else if (main_core.Type.isStringFilled(response.ACTION)) {
	                var eventData = null;
	                if (['create', 'edit'].includes(response.ACTION) && !main_core.Type.isUndefined(response.GROUP)) {
	                  eventData = {
	                    code: response.ACTION == 'create' ? 'afterCreate' : 'afterEdit',
	                    data: {
	                      group: response.GROUP,
	                      projectOptions: _this3.projectOptions
	                    }
	                  };
	                } else if (response.ACTION === 'invite') {
	                  eventData = {
	                    code: 'afterInvite',
	                    data: {}
	                  };
	                }
	                if (eventData) {
	                  var groupWillBeShown = response.ACTION === 'create' && main_core.Type.isStringFilled(response.URL) && (!main_core.Type.isStringFilled(_this3.config.refresh) || _this3.config.refresh === 'Y');
	                  window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
	                  if (response.ACTION === 'create') {
	                    var createdGroupsData = JSON.parse(response.SELECTOR_GROUPS);
	                    if (main_core.Type.isArray(createdGroupsData)) {
	                      window.top.BX.SidePanel.Instance.postMessageAll(window, 'BX.Socialnetwork.Workgroup:onAdd', {
	                        projects: createdGroupsData
	                      });
	                      if (!groupWillBeShown) {
	                        _this3.showDemoInfo(response);
	                      }
	                    }
	                  }
	                  if (currentSlider) {
	                    BX.SidePanel.Instance.close(false, function () {
	                      BX.SidePanel.Instance.destroy(currentSlider.getUrl());
	                    });
	                  }
	                  if (groupWillBeShown) {
	                    var bindingFound = false;
	                    BX.SidePanel.Instance.anchorRules.find(function (rule) {
	                      if (bindingFound || !main_core.Type.isArray(rule.condition)) {
	                        return;
	                      }
	                      rule.condition.forEach(function (condition) {
	                        if (bindingFound) {
	                          return;
	                        }
	                        if (response.URL.match(condition)) {
	                          bindingFound = true;
	                        }
	                      });
	                    });
	                    if (bindingFound) {
	                      BX.SidePanel.Instance.open(response.URL, {
	                        events: {
	                          onLoad: function onLoad() {
	                            _this3.showDemoInfo(response);
	                          }
	                        }
	                      });
	                    } else {
	                      top.window.location.href = response.URL;
	                    }
	                  }
	                }
	              }
	            }
	          },
	          onfailure: function onfailure(errorData) {
	            Buttons.showWaitSubmitButton(false);
	            _this3.alertManager.showAlert(main_core.Loc.getMessage('SONET_GCE_T_AJAX_ERROR'));
	          }
	        });
	      }
	      e.preventDefault();
	    }
	  }, {
	    key: "saveScrumAnalyticData",
	    value: function saveScrumAnalyticData() {
	      var actionUrl = document.getElementById('sonet_group_create_popup_form').action;
	      var source = new main_core.Uri(actionUrl).getQueryParam('source');
	      var availableSources = new Set(['guide_adv', 'guide_direct', 'guide_portal']);
	      if (availableSources.has(source)) {
	        main_core.ajax.runAction('bitrix:tasks.scrum.info.saveScrumStart', {
	          data: {},
	          analyticsLabel: {
	            scrum: 'Y',
	            action: 'scrum_start',
	            source: source
	          }
	        });
	      }
	    }
	  }, {
	    key: "showHideBlock",
	    value: function showHideBlock(params) {
	      var _this4 = this;
	      if (!main_core.Type.isPlainObject(params)) {
	        return false;
	      }
	      var containerNode = params.container;
	      var blockNode = params.block;
	      var show = !!params.show;
	      if (!main_core.Type.isDomNode(containerNode) || !main_core.Type.isDomNode(blockNode)) {
	        return false;
	      }
	      if (!main_core.Type.isUndefined(this.animationList[blockNode.id]) && !main_core.Type.isNull(this.animationList[blockNode.id])) {
	        return false;
	      }
	      this.animationList[blockNode.id] = null;
	      var maxHeight = parseInt(blockNode.offsetHeight);
	      var duration = !main_core.Type.isUndefined(params.duration) && parseInt(params.duration) > 0 ? parseInt(params.duration) : 0;
	      if (show) {
	        containerNode.style.display = 'block';
	      }
	      if (duration > 0) {
	        if (main_core.Type.isStringFilled(blockNode.id)) {
	          this.animationList[blockNode.id] = true;
	        }
	        BX.delegate(new BX.easing({
	          duration: duration,
	          start: {
	            height: show ? 0 : maxHeight,
	            opacity: show ? 0 : 100
	          },
	          finish: {
	            height: show ? maxHeight : 0,
	            opacity: show ? 100 : 0
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            containerNode.style.maxHeight = "".concat(state.height, "px");
	            containerNode.style.opacity = state.opacity / 100;
	          },
	          complete: function complete() {
	            if (main_core.Type.isStringFilled(blockNode.id)) {
	              _this4.animationList[blockNode.id] = null;
	            }
	            if (!main_core.Type.isUndefined(params.callback) && main_core.Type.isFunction(params.callback.complete)) {
	              containerNode.style.maxHeight = '';
	              containerNode.style.opacity = '';
	              params.callback.complete();
	            }
	          }
	        }).animate(), this);
	      } else {
	        params.callback.complete();
	      }
	      return true;
	    }
	  }, {
	    key: "showDemoInfo",
	    value: function showDemoInfo(response) {
	      if (this.demoInfoAlreadyBeenShown) {
	        return;
	      }
	      if ('trialEnabled' in response && main_core.Type.isPlainObject(response.trialEnabled)) {
	        if ('scrum' in response.trialEnabled && response.trialEnabled.scrum === true) {
	          this.showScrumDemoInfo();
	          this.demoInfoAlreadyBeenShown = true;
	        } else if ('project' in response.trialEnabled && response.trialEnabled.project === true) {
	          this.showProjectDemoInfo();
	          this.demoInfoAlreadyBeenShown = true;
	        }
	      }
	    }
	  }, {
	    key: "showScrumDemoInfo",
	    value: function showScrumDemoInfo() {
	      var _this5 = this;
	      var popup = new top.BX.PopupWindow({
	        id: "socialnetwork-scrum-demo-info-".concat(main_core.Text.getRandom()),
	        className: 'socialnetwork__demo-info --scrum',
	        width: 620,
	        overlay: true,
	        padding: 48,
	        closeIcon: true,
	        content: _classPrivateMethodGet$2(this, _renderScrumDemoInfoContent, _renderScrumDemoInfoContent2).call(this),
	        events: {
	          onFirstShow: function onFirstShow(baseEvent) {
	            top.BX.loadCSS(WorkgroupForm.PATH_TO_CSS);
	            _classPrivateMethodGet$2(_this5, _bindStartWorkBtn, _bindStartWorkBtn2).call(_this5, baseEvent.getTarget());
	          }
	        }
	      });
	      popup.show();
	    }
	  }, {
	    key: "showProjectDemoInfo",
	    value: function showProjectDemoInfo() {
	      var _this6 = this;
	      var popup = new top.BX.PopupWindow({
	        id: "socialnetwork-project-demo-info-".concat(main_core.Text.getRandom()),
	        className: 'socialnetwork__demo-info --project',
	        width: 620,
	        overlay: true,
	        padding: 48,
	        closeIcon: true,
	        content: _classPrivateMethodGet$2(this, _renderProjectDemoInfoContent, _renderProjectDemoInfoContent2).call(this),
	        events: {
	          onFirstShow: function onFirstShow(baseEvent) {
	            top.BX.loadCSS(WorkgroupForm.PATH_TO_CSS);
	            _classPrivateMethodGet$2(_this6, _bindStartWorkBtn, _bindStartWorkBtn2).call(_this6, baseEvent.getTarget());
	          }
	        }
	      });
	      popup.show();
	    }
	  }]);
	  return WorkgroupForm;
	}(main_core_events.EventEmitter);
	function _renderScrumDemoInfoContent2() {
	  return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"socialnetwork__demo-info_wrapper\">\n\t\t\t\t<div class=\"socialnetwork__demo-info_content\">\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_text-trial\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-btn ui-btn-sm ui-btn-success ui-btn-round ui-btn-no-caps\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TITLE_SCRUM_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TEXT_SCRUM_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TEXT_TRIAL_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_BTN_1'), _classPrivateMethodGet$2(this, _getLottieScrum, _getLottieScrum2).call(this));
	}
	function _renderProjectDemoInfoContent2() {
	  return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"socialnetwork__demo-info_wrapper\">\n\t\t\t\t<div class=\"socialnetwork__demo-info_content\">\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"socialnetwork__demo-info_text-trial\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-btn ui-btn-sm ui-btn-success ui-btn-round ui-btn-no-caps\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TITLE_PROJECT_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TEXT_PROJECT_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_TEXT_TRIAL_1'), main_core.Loc.getMessage('SONET_GCE_T_DEMO_INFO_BTN_1'), _classPrivateMethodGet$2(this, _getLottieProject, _getLottieProject2).call(this));
	}
	function _getLottieScrum2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _scrumLottieIconContainer)) {
	    babelHelpers.classPrivateFieldSet(this, _scrumLottieIconContainer, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"socialnetwork__demo-info_image\"></div>\n\t\t\t"]))));
	    babelHelpers.classPrivateFieldSet(this, _scrumLottieAnimation, ui_lottie.Lottie.loadAnimation({
	      container: babelHelpers.classPrivateFieldGet(this, _scrumLottieIconContainer),
	      renderer: 'svg',
	      loop: false,
	      animationData: scrumLottieIconInfo
	    }));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _scrumLottieIconContainer);
	}
	function _getLottieProject2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _projectLottieIconContainer)) {
	    babelHelpers.classPrivateFieldSet(this, _projectLottieIconContainer, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"socialnetwork__demo-info_image\"></div>\n\t\t\t"]))));
	    babelHelpers.classPrivateFieldSet(this, _projectLottieAnimation, ui_lottie.Lottie.loadAnimation({
	      container: babelHelpers.classPrivateFieldGet(this, _projectLottieIconContainer),
	      renderer: 'svg',
	      loop: false,
	      animationData: projectLottieIconInfo
	    }));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _projectLottieIconContainer);
	}
	function _bindStartWorkBtn2(popup) {
	  var popupContainer = popup.getContentContainer();
	  if (main_core.Type.isDomNode(popupContainer)) {
	    var btnNode = popup.getContentContainer().querySelector('.ui-btn');
	    if (main_core.Type.isDomNode(popupContainer)) {
	      main_core.Event.bind(btnNode, 'click', function () {
	        return popup.close();
	      });
	    }
	  }
	}
	babelHelpers.defineProperty(WorkgroupForm, "instance", null);
	babelHelpers.defineProperty(WorkgroupForm, "PATH_TO_CSS", '/bitrix/components/bitrix/socialnetwork.group_create.ex/templates/.default/style.css');

	exports.WorkgroupForm = WorkgroupForm;
	exports.WorkgroupFormTeamManager = TeamManager$$1;
	exports.WorkgroupFormUFManager = UFManager;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.Main,BX.UI,BX.Messenger.v2.Lib,BX.UI,BX.Event,BX.UI.EntitySelector,BX,BX.UI));
//# sourceMappingURL=script.js.map
