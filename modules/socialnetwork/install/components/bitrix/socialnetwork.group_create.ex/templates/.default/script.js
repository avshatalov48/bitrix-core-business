this.BX = this.BX || {};
(function (exports,ui_entitySelector,ui_buttons,ui_alerts,main_core_events,main_popup,main_core) {
	'use strict';

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
	      /*
	      		const submitButtonNode = document.getElementById('sonet_group_create_popup_form_button_submit');
	      		if (
	      			submitButtonNode
	      			&& submitButtonNode.getAttribute('bx-action-type') === 'create'
	      		)
	      		{
	      			submitButtonNode.innerHTML = (isChecked ? Loc.getMessage('SONET_GCE_T_DO_CREATE_PROJECT') : Loc.getMessage('SONET_GCE_T_DO_CREATE'));
	      		}
	      */

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
	  }

	  babelHelpers.createClass(ConfidentialitySelector, [{
	    key: "selectItem",
	    value: function selectItem(selector) {
	      Util.selectSelectorItem(selector);
	      WorkgroupForm.getInstance().recalcForm({
	        selectedConfidentialityType: selector.getAttribute('data-bx-confidentiality-type')
	      });
	    }
	  }], [{
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
	    this.groupId = main_core.Type.isUndefined(params.groupId) ? parseInt(params.groupId) : 0;
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
	          _this2.imageNode.style = "background-image: url('".concat(response.data.fileUri, "'); background-size: cover;");

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

	var TypePresetSelector = /*#__PURE__*/function () {
	  function TypePresetSelector() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, TypePresetSelector);
	    this.cssClass = {
	      container: 'socialnetwork-group-create-ex__type-preset-wrapper',
	      selector: 'socialnetwork-group-create-ex__type-preset-selector'
	    };
	    this.container = document.querySelector(".".concat(this.cssClass.container));

	    if (!this.container) {
	      return;
	    }

	    var firstItemSelected = false;
	    var selectors = this.container.querySelectorAll(".".concat(this.cssClass.selector));
	    selectors.forEach(function (selector) {
	      selector.addEventListener('click', function (e) {
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

	var FieldsManager = /*#__PURE__*/function () {
	  function FieldsManager() {
	    babelHelpers.classCallCheck(this, FieldsManager);
	  }

	  babelHelpers.createClass(FieldsManager, null, [{
	    key: "check",
	    value: function check() {
	      if (WorkgroupForm.getInstance().wizardManager.stepsCount === 1) {
	        return this.checkAll();
	      } else {
	        return this.checkStep(WorkgroupForm.getInstance().wizardManager.currentStep);
	      }
	    }
	  }, {
	    key: "checkStep",
	    value: function checkStep(step) {
	      var _this = this;

	      step = parseInt(step);
	      var errorDataList = [];

	      if (main_core.Type.isArray(this.mandatoryFieldsByStep[step])) {
	        this.mandatoryFieldsByStep[step].forEach(function (fieldData) {
	          var fieldNode = document.getElementById(fieldData.id);

	          if (!main_core.Type.isDomNode(fieldNode)) {
	            return;
	          }

	          if (fieldNode.tagName.toLowerCase() !== 'input') {
	            if (fieldData.type !== 'string') {
	              return;
	            }

	            fieldNode = fieldNode.querySelector('input[type="text"]');

	            if (!main_core.Type.isDomNode(fieldNode)) {
	              return;
	            }
	          }

	          fieldData.fieldNode = fieldNode;

	          var errorText = _this.checkField(fieldData);

	          if (main_core.Type.isStringFilled(errorText)) {
	            var bindNode = document.getElementById(fieldData.bindNodeId);
	            errorDataList.push({
	              bindNode: main_core.Type.isDomNode(bindNode) ? bindNode : fieldNode,
	              message: errorText
	            });
	          }
	        });
	      }

	      return errorDataList;
	    }
	  }, {
	    key: "checkAll",
	    value: function checkAll() {
	      var _this2 = this;

	      var errorDataList = [];
	      Object.entries(this.mandatoryFieldsByStep).forEach(function (stepData) {
	        errorDataList = errorDataList.concat(_this2.checkStep(parseInt(stepData[0])));
	      });
	      return errorDataList;
	    }
	  }, {
	    key: "checkField",
	    value: function checkField(fieldData) {
	      var errorText = '';

	      if (!main_core.Type.isPlainObject(fieldData) && !main_core.Type.isDomNode(fieldData.fieldNode)) {
	        return errorText;
	      }

	      if (main_core.Type.isFunction(fieldData.condition)) {
	        if (!fieldData.condition()) {
	          return errorText;
	        }
	      }

	      var fieldNode = fieldData.fieldNode;
	      var fieldType = main_core.Type.isStringFilled(fieldData.type) ? fieldData.type : 'string';

	      switch (fieldType) {
	        case 'string':
	          errorText = fieldNode.value.trim() === '' ? main_core.Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR') : '';
	          break;

	        default:
	          errorText = '';
	      }

	      return errorText;
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorData) {
	      if (!main_core.Type.isPlainObject(errorData) || !main_core.Type.isStringFilled(errorData.message) || !main_core.Type.isDomNode(errorData.bindNode)) {
	        return;
	      }

	      WorkgroupForm.getInstance().alertManager.showAlert(errorData.message, errorData.bindNode.parentNode);
	    }
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
	    id: 'SCRUM_MASTER_CODE',
	    type: 'string',
	    bindNodeId: 'SCRUM_MASTER_CODE',
	    condition: function condition() {
	      return !!WorkgroupForm.getInstance().scrumManager.isScrumProject;
	    }
	  }]
	});

	var Buttons = /*#__PURE__*/function () {
	  function Buttons() {
	    babelHelpers.classCallCheck(this, Buttons);
	    this.submitButton = document.getElementById('sonet_group_create_popup_form_button_submit');

	    if (!this.submitButton) {
	      return;
	    }

	    this.submitButton.addEventListener('click', function (e) {
	      var button = ui_buttons.ButtonManager.createFromNode(e.currentTarget);

	      if (button && button.isDisabled()) {
	        return;
	      }

	      WorkgroupForm.getInstance().alertManager.hideAllAlerts();
	      var errorDataList = FieldsManager.check().filter(function (errorData) {
	        return main_core.Type.isPlainObject(errorData) && main_core.Type.isStringFilled(errorData.message) && main_core.Type.isDomNode(errorData.bindNode);
	      });

	      if (errorDataList.length > 0) {
	        errorDataList.forEach(function (errorData) {
	          FieldsManager.showError(errorData);
	        });
	      } else if (WorkgroupForm.getInstance().wizardManager.currentStep < WorkgroupForm.getInstance().wizardManager.stepsCount) {
	        WorkgroupForm.getInstance().wizardManager.currentStep++;

	        if (WorkgroupForm.getInstance().wizardManager.currentStep === 3 && Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1) // skip confidentiality step
	          {
	            WorkgroupForm.getInstance().wizardManager.currentStep++;
	          }

	        WorkgroupForm.getInstance().wizardManager.showCurrentStep();
	      } else {
	        WorkgroupForm.getInstance().submitForm(e);
	      }

	      return e.preventDefault();
	    });
	    this.backButton = document.getElementById('sonet_group_create_popup_form_button_step_2_back');

	    if (this.backButton) {
	      this.backButton.addEventListener('click', function (e) {
	        var button = ui_buttons.ButtonManager.createFromNode(e.currentTarget);

	        if (button && button.isDisabled()) {
	          return;
	        }

	        if (WorkgroupForm.getInstance().wizardManager.currentStep > 1) {
	          WorkgroupForm.getInstance().wizardManager.currentStep--;

	          if (WorkgroupForm.getInstance().wizardManager.currentStep === 3 && Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1) // skip confidentiality step
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

	  babelHelpers.createClass(Buttons, null, [{
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

	        buttonNode.removeEventListener('click', WorkgroupForm.getInstance().submitForm);
	      } else {
	        if (button) {
	          button.setWaiting(false);
	        }

	        buttonNode.addEventListener('click', WorkgroupForm.getInstance().submitForm);
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
	babelHelpers.defineProperty(Buttons, "cssClass", {
	  hidden: 'socialnetwork-group-create-ex__button-invisible'
	});

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
	        ['project', 'scrum', 'group'].forEach(function (projectType) {
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

	var InviteSelector = /*#__PURE__*/function () {
	  function InviteSelector(params) {
	    babelHelpers.classCallCheck(this, InviteSelector);
	    this.selectorId = params.selectorId;
	    main_core_events.EventEmitter.subscribe('BX.Main.User.SelectorController:select', this.selectHandler.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.User.SelectorController:unSelect', this.selectHandler.bind(this));
	  }

	  babelHelpers.createClass(InviteSelector, [{
	    key: "selectHandler",
	    value: function selectHandler(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          eventParams = _event$getCompatData2[0];

	      if (eventParams.selectorId === this.selectorId) {
	        this.showDepartmentHint({
	          selectorId: eventParams.selectorId
	        });
	      }
	    }
	  }, {
	    key: "showDepartmentHint",
	    value: function showDepartmentHint(params) {
	      if (!main_core.Type.isStringFilled(params.selectorId)) {
	        return;
	      }

	      var hintNode = document.getElementById('GROUP_ADD_DEPT_HINT_block');

	      if (!hintNode) {
	        return;
	      }

	      var selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

	      if (main_core.Type.isUndefined(selectorInstance)) {
	        return;
	      }

	      if (!main_core.Type.isPlainObject(selectorInstance.itemsSelected)) {
	        hintNode.classList.remove('visible');
	        return false;
	      }

	      var departmentFound = false;
	      Object.entries(selectorInstance.itemsSelected).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 1),
	            itemId = _ref2[0];

	        if (departmentFound) {
	          return;
	        }

	        if (itemId.match(/DR\d+/)) {
	          departmentFound = true;
	        }
	      });

	      if (departmentFound) {
	        hintNode.classList.add('visible');
	      } else {
	        hintNode.classList.remove('visible');
	      }

	      return departmentFound;
	    }
	  }]);
	  return InviteSelector;
	}();

	var TeamExtranetManager = /*#__PURE__*/function () {
	  function TeamExtranetManager() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, TeamExtranetManager);
	    this.menuId = 'sonet_group_create_popup_action_popup';
	    var emailsTextareaNode = document.getElementById('EMAILS');

	    if (emailsTextareaNode) {
	      emailsTextareaNode.addEventListener('blur', function (e) {
	        if (_this.value === '') {
	          e.currentTarget.classList.remove('invite-dialog-inv-form-textarea-active');
	          e.currentTarget.value = e.currentTarget.value.replace(new RegExp(/^$/), main_core.Loc.getMessage('SONET_GCE_T_EMAILS_DESCR'));
	        }
	      });
	      emailsTextareaNode.addEventListener('focus', function (e) {
	        e.currentTarget.classList.add('invite-dialog-inv-form-textarea-active');
	        e.currentTarget.value = e.currentTarget.value.replace(main_core.Loc.getMessage('SONET_GCE_T_EMAILS_DESCR'), '');
	      });
	    }

	    this.actionLinkAdd = document.getElementById('sonet_group_create_popup_action_title_add');
	    this.actionLinkInvite = document.getElementById('sonet_group_create_popup_action_title_invite');

	    if (this.actionLinkAdd) {
	      this.actionLinkAdd.addEventListener('click', function () {
	        _this.onActionSelect('add');
	      });
	    }

	    if (this.actionLinkInvite) {
	      this.actionLinkInvite.addEventListener('click', function () {
	        _this.onActionSelect('invite');
	      });
	    }

	    this.inviteBlock1 = document.getElementById('sonet_group_create_popup_action_block_invite');
	    this.inviteBlock2 = document.getElementById('sonet_group_create_popup_action_block_invite_2');
	    this.addBlock = document.getElementById('sonet_group_create_popup_action_block_add');
	  }

	  babelHelpers.createClass(TeamExtranetManager, [{
	    key: "onActionSelect",
	    value: function onActionSelect(action) {
	      if (action !== 'add') {
	        action = 'invite';
	      }

	      this.lastAction = action;

	      if (action === 'invite') {
	        this.inviteBlock1.style.display = 'block';
	        this.inviteBlock2.style.display = 'block';
	        this.addBlock.style.display = 'none';
	        this.actionLinkInvite.classList.add('--active');
	        this.actionLinkAdd.classList.remove('--active');
	      } else {
	        this.inviteBlock1.style.display = 'none';
	        this.inviteBlock2.style.display = 'none';
	        this.addBlock.style.display = 'block';
	        this.actionLinkInvite.classList.remove('--active');
	        this.actionLinkAdd.classList.add('--active');
	      }

	      main_popup.MenuManager.destroy(this.menuId);
	    }
	  }]);
	  return TeamExtranetManager;
	}();

	var TeamManager = function TeamManager() {
	  babelHelpers.classCallCheck(this, TeamManager);
	  document.querySelectorAll('[data-employees-selector-id]').forEach(function (employeeSeectorNode) {
	    var selectorId = employeeSeectorNode.getAttribute('data-employees-selector-id');

	    if (main_core.Type.isStringFilled(selectorId)) {
	      WorkgroupForm.getInstance().arUserSelector.push(selectorId);
	      new InviteSelector({
	        selectorId: selectorId
	      });
	    }
	  });
	  new TeamExtranetManager();
	};

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

	var WorkgroupForm = /*#__PURE__*/function () {
	  babelHelpers.createClass(WorkgroupForm, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return WorkgroupForm.instance;
	    }
	  }]);

	  function WorkgroupForm(params) {
	    babelHelpers.classCallCheck(this, WorkgroupForm);
	    this.componentName = params.componentName;
	    this.signedParameters = params.signedParameters;
	    this.userSelector = '';
	    this.lastAction = 'invite';
	    this.arUserSelector = [];
	    this.animationList = {};
	    this.selectedTypeCode = false;
	    this.groupId = parseInt(params.groupId);
	    this.isScrumProject = params.isScrumProject;
	    this.config = params.config;
	    this.avatarUploaderId = params.avatarUploaderId;
	    this.themePickerData = params.themePickerData;
	    this.projectOptions = params.projectOptions;
	    this.projectTypes = params.projectTypes;
	    this.confidentialityTypes = params.confidentialityTypes;
	    this.selectedProjectType = params.selectedProjectType;
	    this.selectedConfidentialityType = params.selectedConfidentialityType;
	    this.scrumManager = new Scrum({
	      isScrumProject: this.isScrumProject
	    });
	    this.wizardManager = new Wizard({
	      currentStep: Object.entries(this.projectTypes).length > 1 ? 1 : 2,
	      stepsCount: params.stepsCount > 1 ? params.stepsCount : 1
	    });
	    this.alertManager = new AlertManager({
	      errorContainerId: 'sonet_group_create_error_block'
	    });
	    WorkgroupForm.instance = this;
	    this.init(params);
	    this.buttonsInstance = new Buttons();
	  }

	  babelHelpers.createClass(WorkgroupForm, [{
	    key: "init",
	    value: function init(params) {
	      var _this = this;

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

	      if (document.getElementById('group-tags-bind-node')) {
	        new Tags({
	          groupId: this.groupId,
	          containerNodeId: 'group-tags-bind-node',
	          hiddenFieldId: 'GROUP_KEYWORDS'
	        });
	      }

	      new TypePresetSelector();
	      new ConfidentialitySelector();
	      new TeamManager();
	      new FeaturesManager();
	      var groupNameNode = document.getElementById('GROUP_NAME_input');

	      if (groupNameNode) {
	        groupNameNode.focus();
	      }

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
	          _this.switchExtranet(extranetCheckboxNode.checked, true);
	        });
	      }

	      var visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');

	      if (visibleCheckboxNode && visibleCheckboxNode.type === 'checkbox') {
	        visibleCheckboxNode.addEventListener('click', function () {
	          _this.switchNotVisible(visibleCheckboxNode.checked);
	        });
	      }

	      var projectCheckboxNode = document.getElementById('GROUP_PROJECT');

	      if (projectCheckboxNode && projectCheckboxNode.type === 'checkbox') {
	        projectCheckboxNode.addEventListener('click', function () {
	          Util.recalcFormPartProject(projectCheckboxNode.checked);
	        });
	      }

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
	        this.switchExtranet(Util.getCheckedValue(extranetCheckboxNode, false));
	      }

	      var visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
	      var openedCheckboxNode = document.getElementById('GROUP_OPENED');

	      if (visibleCheckboxNode && openedCheckboxNode && !Util.getCheckedValue(visibleCheckboxNode)) {
	        Util.setCheckedValue(openedCheckboxNode, false);
	      }
	    }
	  }, {
	    key: "switchExtranet",
	    value: function switchExtranet(isChecked, useAnimation) {
	      var extranetBlockNode = document.getElementById('INVITE_EXTRANET_block');
	      var extranetBlockContainerNode = document.getElementById('INVITE_EXTRANET_block_container');

	      if (extranetBlockNode && extranetBlockContainerNode) {
	        if (isChecked) {
	          extranetBlockContainerNode.style.display = 'block';
	        }

	        this.showHideBlock({
	          container: extranetBlockContainerNode,
	          block: extranetBlockNode,
	          show: isChecked,
	          duration: useAnimation ? 1000 : 0,
	          callback: {
	            complete: function complete() {
	              if (isChecked) {
	                extranetBlockContainerNode.classList.remove('--hidden');
	              } else {
	                extranetBlockContainerNode.style.display = 'none';
	                extranetBlockContainerNode.classList.add('--hidden');
	              }
	            }
	          }
	        });
	      }

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
	      }

	      if (isChecked) {
	        ConfidentialitySelector.unselectAll();
	        ConfidentialitySelector.select('secret');
	        ConfidentialitySelector.disableAll();
	        ConfidentialitySelector.enable('secret');
	      } else {
	        ConfidentialitySelector.enableAll();
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
	      var _this2 = this;

	      var inviteActionNode = document.getElementById('EXTRANET_INVITE_ACTION');

	      if (inviteActionNode) {
	        inviteActionNode.value = this.lastAction;
	      }

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

	              _this2.alertManager.showAlert("".concat(warningText).concat(response.ERROR));

	              if (main_core.Type.isStringFilled(response.WIZARD_STEP_PROCESSED)) {
	                _this2.wizardManager.recalcAfterSubmit({
	                  processedStep: response.WIZARD_STEP_PROCESSED.toLowerCase(),
	                  createdGroupId: parseInt(!main_core.Type.isUndefined(response.CREATED_GROUP_ID) ? response.CREATED_GROUP_ID : 0)
	                });
	              }

	              if (main_core.Type.isArray(response.USERS_ID)) {
	                var selectedUsers = [];
	                response.USERS_ID.forEach(function (currentValue) {
	                  selectedUsers["U".concat(currentValue)] = 'users';
	                });

	                _this2.arUserSelector.forEach(function (selectorId) {
	                  var selectorInstance = BX.UI.SelectorManager.instances[selectorId];

	                  if (main_core.Type.isUndefined(selectorInstance)) {
	                    return;
	                  }

	                  var selectorNode = document.getElementById("ui-tile-selector-".concat(selectorId));
	                  selectorNode.querySelectorAll('.ui-tile-selector-item').forEach(function (node) {
	                    var userCode = node.getAttribute('data-bx-id');

	                    if (!main_core.Type.isStringFilled(userCode)) {
	                      return;
	                    }

	                    selectorInstance.getRenderInstance().deleteItem({
	                      entityType: 'USERS',
	                      itemId: userCode
	                    });
	                  });
	                  selectorInstance.itemsSelected = selectedUsers;
	                  selectorInstance.reinit();
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

	              if (window === top.window) // not frame
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
	                      projectOptions: _this2.projectOptions
	                    }
	                  };
	                } else if (response.ACTION === 'invite') {
	                  eventData = {
	                    code: 'afterInvite',
	                    data: {}
	                  };
	                }

	                if (eventData) {
	                  window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);

	                  if (response.ACTION === 'create') {
	                    var createdGroupsData = JSON.parse(response.SELECTOR_GROUPS);

	                    if (main_core.Type.isArray(createdGroupsData)) {
	                      window.top.BX.SidePanel.Instance.postMessageAll(window, 'BX.Socialnetwork.Workgroup:onAdd', {
	                        projects: createdGroupsData
	                      });
	                    }
	                  }

	                  if (currentSlider) {
	                    BX.SidePanel.Instance.close(false, function () {
	                      BX.SidePanel.Instance.destroy(currentSlider.getUrl());
	                    });
	                  }

	                  if (response.ACTION == 'create' && main_core.Type.isStringFilled(response.URL) && (!main_core.Type.isStringFilled(_this2.config.refresh) || _this2.config.refresh === 'Y')) {
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
	                      BX.SidePanel.Instance.open(response.URL);
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

	            _this2.alertManager.showAlert(main_core.Loc.getMessage('SONET_GCE_T_AJAX_ERROR'));
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
	      var _this3 = this;

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
	              _this3.animationList[blockNode.id] = null;
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
	  }]);
	  return WorkgroupForm;
	}();

	babelHelpers.defineProperty(WorkgroupForm, "instance", null);

	exports.WorkgroupForm = WorkgroupForm;
	exports.WorkgroupFormUFManager = UFManager;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.UI.EntitySelector,BX.UI,BX.UI,BX.Event,BX.Main,BX));
//# sourceMappingURL=script.js.map
