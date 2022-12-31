this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_popup,ui_entitySelector,main_date,main_core,main_core_events) {
	'use strict';

	var AjaxProcessor = /*#__PURE__*/function () {
	  function AjaxProcessor() {
	    babelHelpers.classCallCheck(this, AjaxProcessor);
	    babelHelpers.defineProperty(this, "htmlWasInserted", false);
	    babelHelpers.defineProperty(this, "scriptsLoaded", false);
	  }

	  babelHelpers.createClass(AjaxProcessor, [{
	    key: "processCSS",
	    value: function processCSS(block, callback) {
	      if (main_core.Type.isArray(block.CSS) && block.CSS.length > 0) {
	        BX.load(block.CSS, callback);
	      } else {
	        callback();
	      }
	    }
	  }, {
	    key: "processExternalJS",
	    value: function processExternalJS(block, callback) {
	      if (main_core.Type.isArray(block.JS) && block.JS.length > 0) {
	        BX.load(block.JS, callback);
	      } else {
	        callback();
	      }
	    }
	  }, {
	    key: "processAjaxBlockInsertHTML",
	    value: function processAjaxBlockInsertHTML(block, container, callbackExternal) {
	      container.appendChild(BX.create('DIV', {
	        html: block.CONTENT
	      }));
	      this.htmlWasInserted = true;

	      if (this.scriptsLoaded) {
	        this.processInlineJS(block, callbackExternal);
	      }
	    }
	  }, {
	    key: "processInlineJS",
	    value: function processInlineJS(block, callbackExternal) {
	      this.scriptsLoaded = true;

	      if (this.htmlWasInserted) {
	        BX.ajax.processRequestData(block.CONTENT, {
	          scriptsRunFirst: false,
	          dataType: 'HTML'
	        });
	        callbackExternal();
	      }
	    }
	  }]);
	  return AjaxProcessor;
	}();

	var PostForm = /*#__PURE__*/function () {
	  babelHelpers.createClass(PostForm, null, [{
	    key: "setInstance",
	    value: function setInstance(instance) {
	      PostForm.instance = instance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      return PostForm.instance;
	    }
	  }]);

	  function PostForm(params) {
	    babelHelpers.classCallCheck(this, PostForm);
	    babelHelpers.defineProperty(this, "lazyLoad", null);
	    babelHelpers.defineProperty(this, "ajaxUrl", '');
	    babelHelpers.defineProperty(this, "inited", false);
	    babelHelpers.defineProperty(this, "loaded", false);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "containerMicro", null);
	    babelHelpers.defineProperty(this, "containerMicroInner", null);
	    babelHelpers.defineProperty(this, "clickDisabled", false);
	    babelHelpers.defineProperty(this, "lastWait", []);
	    babelHelpers.defineProperty(this, "animationStartHeight", 0);
	    babelHelpers.defineProperty(this, "initedEditorsList", []);
	    babelHelpers.defineProperty(this, "options", {});
	    this.init(params);
	    PostForm.setInstance(this);
	  }

	  babelHelpers.createClass(PostForm, [{
	    key: "setOption",
	    value: function setOption(key, value) {
	      if (!main_core.Type.isStringFilled(key)) {
	        return;
	      }

	      this.options[key] = value;
	    }
	  }, {
	    key: "onShow",
	    value: function onShow() {
	      if (!main_core.Type.isStringFilled(this.options.startVideoRecorder) || this.options.startVideoRecorder !== 'Y') {
	        return;
	      }

	      setTimeout(function () {
	        var editorForm = document.getElementById('divoPostFormLHE_blogPostForm');

	        if (!editorForm) {
	          return;
	        }

	        main_core_events.EventEmitter.emit(editorForm, 'OnShowLHE', new main_core_events.BaseEvent({
	          compatData: ['justShow']
	        }));
	        BX.VideoRecorder.start('blogPostForm', 'post');
	      }, 500);
	    }
	  }, {
	    key: "onSliderClose",
	    value: function onSliderClose() {
	      var sliderInstance = BX.SidePanel.Instance.getTopSlider();

	      if (!sliderInstance) {
	        return;
	      }

	      BX.SidePanel.Instance.postMessageAll(window, 'SidePanel.Wrapper:onClose', {
	        sliderData: sliderInstance.getData()
	      });
	    }
	  }, {
	    key: "init",
	    value: function init(params) {
	      var _this = this;

	      if (this.inited !== true) {
	        this.inited = true;
	        this.lazyLoad = !main_core.Type.isUndefined(params.lazyLoad) ? !!params.lazyLoad : false;
	        this.ajaxUrl = main_core.Type.isStringFilled(params.ajaxUrl) ? params.ajaxUrl : '';
	        this.container = main_core.Type.isDomNode(params.container) ? params.container : null;
	        this.containerMicro = main_core.Type.isDomNode(params.containerMicro) ? params.containerMicro : null;
	        this.containerMicroInner = main_core.Type.isDomNode(params.containerMicroInner) ? params.containerMicroInner : null;
	        this.successPostId = !main_core.Type.isUndefined(params.successPostId) && parseInt(params.successPostId) > 0 ? parseInt(params.successPostId) : 0; //region dnd

	        if (this.containerMicro) {
	          this.containerMicro.setAttribute('dropzone', 'copy f:*\/*');
	          var timerListenEnter = 0;

	          var stopListenEnter = function stopListenEnter(event) {
	            if (timerListenEnter > 0) {
	              clearTimeout(timerListenEnter);
	              timerListenEnter = 0;
	            }

	            event.stopPropagation();
	            event.preventDefault();
	          };

	          var fireDragEnter = function fireDragEnter(event) {
	            stopListenEnter(event);

	            _this.containerMicro.click();
	          };

	          var startListenEnter = function startListenEnter(event) {
	            if (timerListenEnter <= 0) {
	              timerListenEnter = setTimeout(function () {
	                fireDragEnter(event);
	              }, 3000);
	            }

	            event.stopPropagation();
	            event.preventDefault();
	          };

	          this.containerMicro.addEventListener('dragover', startListenEnter);
	          this.containerMicro.addEventListener('dragenter', startListenEnter);
	          this.containerMicro.addEventListener('dragleave', stopListenEnter);
	          this.containerMicro.addEventListener('dragexit', stopListenEnter);
	          this.containerMicro.addEventListener('drop', stopListenEnter);
	        } //region

	      }

	      var sliderInstance = BX.SidePanel.Instance.getTopSlider();

	      if (sliderInstance) {
	        if (this.successPostId > 0) {
	          BX.SidePanel.Instance.postMessage(window, 'Socialnetwork.PostForm:onAdd', {
	            originatorSliderId: sliderInstance.getData().get('sliderId'),
	            successPostId: this.successPostId
	          });
	          BX.SidePanel.Instance.close();
	        } else if (!sliderInstance.getData().get('initialized')) {
	          main_core_events.EventEmitter.subscribe(sliderInstance, 'BX.Socialnetwork.SidePanel.Slider:onClose', this.onSliderClose);
	          sliderInstance.getData().set('initialized', true);
	        }
	      }
	    }
	  }, {
	    key: "get",
	    value: function get(params) {
	      var _this2 = this;

	      if (this.clickDisabled) {
	        return;
	      }

	      if (this.lazyLoad && !this.loaded) {
	        this.clickDisabled = true;
	        this.animationStartHeight = this.containerMicro.offsetHeight;

	        if (main_core.Type.isStringFilled(params.loaderType) && params.loaderType === 'tab') {
	          this.showWaitTab();
	        } else {
	          this.containerMicroInner.style.display = 'none';
	          this.showWait(this.containerMicro);
	        }

	        main_core.ajax({
	          method: 'POST',
	          dataType: 'json',
	          url: this.ajaxUrl,
	          data: {
	            action: 'SBPE_get_full_form',
	            sessid: main_core.Loc.getMessage('bitrix_sessid')
	          },
	          onsuccess: function onsuccess(result) {
	            _this2.loaded = true;
	            _this2.clickDisabled = false;

	            _this2.closeWait();

	            if (result.success) {
	              _this2.processAjaxBlock(result.PROPS, params.callback);
	            }
	          },
	          onfailure: function onfailure() {
	            _this2.clickDisabled = false;

	            _this2.closeWait();

	            _this2.containerMicroInner.style.display = 'block';
	          }
	        });
	      } else if (main_core.Type.isFunction(params.callback)) {
	        params.callback();
	      }
	    }
	  }, {
	    key: "processAjaxBlock",
	    value: function processAjaxBlock(block, callbackExternal) {
	      var _this3 = this;

	      if (!block) {
	        return;
	      }

	      var processor = new AjaxProcessor();
	      processor.processCSS(block, function () {
	        processor.processAjaxBlockInsertHTML(block, _this3.container, callbackExternal);
	      });
	      processor.processExternalJS(block, function () {
	        processor.processInlineJS(block, callbackExternal);
	      });
	    }
	  }, {
	    key: "showWait",
	    value: function showWait(node) {
	      var _this4 = this;

	      var waiterNode = node.bxmsg = document.body.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          id: "wait_".concat(node.id),
	          className: 'feed-add-post-loader-cont'
	        },
	        html: '<svg class="feed-add-post-loader" viewBox="25 25 50 50"><circle class="feed-add-post-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="feed-add-post-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>'
	      }));
	      setTimeout(function () {
	        _this4.adjustWait(node);
	      }, 10);
	      this.lastWait.push(waiterNode);
	      return waiterNode;
	    }
	  }, {
	    key: "showWaitTab",
	    value: function showWaitTab() {
	      if (!BX('feed-add-post-more-icon') || !BX('feed-add-post-more-icon-waiter')) {
	        return;
	      }

	      BX('feed-add-post-more-icon').style.display = 'none';
	      BX('feed-add-post-more-icon-waiter').style.display = 'block';
	    }
	  }, {
	    key: "closeWait",
	    value: function closeWait() {
	      var waiterNode = this.containerMicro.bxmsg;

	      if (waiterNode && waiterNode.parentNode) {
	        for (var i = 0, len = this.lastWait.length; i < len; i++) {
	          if (waiterNode === this.lastWait[i]) {
	            this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
	            break;
	          }
	        }

	        waiterNode.parentNode.removeChild(waiterNode);

	        if (this.containerMicro) {
	          this.containerMicro.bxmsg = null;
	        }

	        main_core.Dom.clean(waiterNode);
	        main_core.Dom.remove(waiterNode);
	      }

	      if (BX('feed-add-post-more-icon') && BX('feed-add-post-more-icon-waiter') && BX('feed-add-post-more-icon').style.display === 'none') {
	        BX('feed-add-post-more-icon').style.display = 'block';
	        BX('feed-add-post-more-icon-waiter').style.display = 'none';
	      }
	    }
	  }, {
	    key: "adjustWait",
	    value: function adjustWait(node) {
	      if (!node.bxmsg) {
	        return;
	      }

	      var nodePosition = BX.pos(node);
	      var topDelta = nodePosition.top + 15;

	      if (topDelta < BX.GetDocElement().scrollTop) {
	        topDelta = BX.GetDocElement().scrollTop + 5;
	      }

	      node.bxmsg.style.top = "".concat(topDelta + 5, "px");

	      if (node === BX.GetDocElement()) {
	        node.bxmsg.style.right = '5px';
	      } else {
	        node.bxmsg.style.left = "".concat(nodePosition.left + parseInt((nodePosition.width - node.bxmsg.offsetWidth) / 2), "px");
	      }
	    }
	  }, {
	    key: "tasksTaskEvent",
	    value: function tasksTaskEvent(taskId) {
	      if (!main_core.Reflection.getClass('BX.UI.Notification.Center')) {
	        return;
	      }

	      var taskLink = main_core.Loc.getMessage('PATH_TO_USER_TASKS_TASK').replace('#user_id#', main_core.Loc.getMessage('USER_ID')).replace('#task_id#', taskId).replace('#action#', 'view');
	      window.top.BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BLOG_POST_EDIT_T_CREATE_TASK_SUCCESS_TITLE'),
	        actions: [{
	          title: main_core.Loc.getMessage('BLOG_POST_EDIT_T_CREATE_TASK_BUTTON_TITLE'),
	          events: {
	            click: function click(event, balloon, action) {
	              balloon.close();
	              window.top.BX.SidePanel.Instance.open(taskLink);
	            }
	          }
	        }]
	      });
	    }
	  }]);
	  return PostForm;
	}();

	babelHelpers.defineProperty(PostForm, "instance", null);

	var PostFormTabs = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PostFormTabs, _EventEmitter);
	  babelHelpers.createClass(PostFormTabs, null, [{
	    key: "setInstance",
	    value: function setInstance(instance) {
	      PostFormTabs.instance = instance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      if (PostFormTabs.instance === null) {
	        new PostFormTabs();
	      }

	      return PostFormTabs.instance;
	    }
	  }]);

	  function PostFormTabs() {
	    var _this;

	    babelHelpers.classCallCheck(this, PostFormTabs);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostFormTabs).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "inited", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tabs", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "bodies", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "active", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "animation", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "animationStartHeight", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "previousTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menu", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "listsMenu", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menuItems", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "lastWait", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clickDisabled", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tabContainer", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "arrow", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "config", {
	      id: {
	        message: 'message',
	        task: 'tasks',
	        calendar: 'calendar',
	        file: 'file',
	        gratitude: 'grat',
	        important: 'important',
	        vote: 'vote',
	        more: 'more',
	        listItem: 'lists'
	      }
	    });

	    _this.setEventNamespace('BX.Socialnetwork.Livefeed.Post.Form.Tabs');

	    _this.init();

	    _this.emit('onInitialized', {
	      tabsInstance: babelHelpers.assertThisInitialized(_this)
	    });

	    PostFormTabs.setInstance(babelHelpers.assertThisInitialized(_this));
	    window.SBPETabs = babelHelpers.assertThisInitialized(_this);
	    return _this;
	  }

	  babelHelpers.createClass(PostFormTabs, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;

	      this.tabContainer = document.getElementById('feed-add-post-form-tab');
	      this.arrow = document.getElementById('feed-add-post-form-tab-arrow');
	      this.tabs = {};
	      this.bodies = {};
	      var tabsList = this.tabContainer && this.tabContainer.querySelectorAll('span.feed-add-post-form-link');

	      if (tabsList) {
	        for (var i = 0; i < tabsList.length; i++) {
	          var id = tabsList[i].getAttribute('id').replace('feed-add-post-form-tab-', '');
	          var limited = tabsList[i].getAttribute('limited');
	          this.tabs[id] = tabsList[i];

	          if (this.tabs[id].style.display === 'none') {
	            this.menuItems.push({
	              tabId: id,
	              text: tabsList[i].getAttribute('data-name'),
	              className: "menu-popup-no-icon feed-add-post-form-".concat(id, " feed-add-post-form-").concat(id, "-more"),
	              onclick: this.createOnClick(id, tabsList[i].getAttribute('data-name'), tabsList[i].getAttribute('data-onclick'), tabsList[i].getAttribute('data-limited') === 'Y')
	            });
	            this.tabs[id] = this.tabs[id].parentNode;
	          }

	          this.bodies[id] = document.getElementById("feed-add-post-content-".concat(id));
	        }
	      }

	      if (!!this.tabs[this.config.id.file]) {
	        this.bodies[this.config.id.file] = [this.bodies[this.config.id.message]];
	      }

	      if (!!this.tabs[this.config.id.calendar]) {
	        this.bodies[this.config.id.calendar] = [this.bodies[this.config.id.calendar]];
	      }

	      if (!!this.tabs[this.config.id.vote]) {
	        this.bodies[this.config.id.vote] = [this.bodies[this.config.id.message], this.bodies[this.config.id.vote]];
	      }

	      if (!!this.tabs[this.config.id.more]) {
	        this.bodies[this.config.id.more] = null;
	      }

	      if (!!this.tabs[this.config.id.important]) {
	        this.bodies[this.config.id.important] = [this.bodies[this.config.id.message], this.bodies[this.config.id.important]];
	      }

	      if (!!this.tabs[this.config.id.gratitude]) {
	        this.bodies[this.config.id.gratitude] = [this.bodies[this.config.id.message], this.bodies[this.config.id.gratitude]];
	      }

	      if (!!this.tabs[this.config.id.listItem]) {
	        this.bodies[this.config.id.listItem] = [this.bodies[this.config.id.listItem]];
	      }

	      if (!!this.tabs[this.config.id.task]) {
	        this.bodies[this.config.id.task] = [this.bodies[this.config.id.task]];
	      }

	      for (var ii in this.bodies) {
	        if (this.bodies.hasOwnProperty(ii) && main_core.Type.isDomNode(this.bodies[ii])) {
	          this.bodies[ii] = [this.bodies[ii]];
	        }
	      }

	      this.inited = true;
	      this.previousTab = false;
	      var uploadFileNode = document.getElementById('bx-b-uploadfile-blogPostForm');

	      if (uploadFileNode) {
	        uploadFileNode.setAttribute('bx-press', 'pressOut');
	        main_core.Event.bind(uploadFileNode, 'mousedown', function () {
	          uploadFileNode.setAttribute("bx-press", uploadFileNode.getAttribute("bx-press") == "pressOut" ? "pressOn" : "pressOut");
	        });
	      }

	      var form = document.getElementById('blogPostForm');

	      if (!form) {
	        return;
	      }

	      if (!form.changePostFormTab) {
	        form.appendChild(main_core.Dom.create('INPUT', {
	          props: {
	            type: 'hidden',
	            name: 'changePostFormTab',
	            value: ''
	          }
	        }));
	      }

	      this.subscribe('changePostFormTab', function (event) {
	        var _event$getData = event.getData(),
	            type = _event$getData.type;

	        if (type === _this2.config.id.more) {
	          return;
	        }

	        form.changePostFormTab.value = type;

	        if (form['UF_BLOG_POST_IMPRTNT']) {
	          form['UF_BLOG_POST_IMPRTNT'].value = type === _this2.config.id.important ? '1' : '0';
	        }
	      });
	    }
	  }, {
	    key: "createOnClick",
	    value: function createOnClick(id, name, onclick, limited) {
	      var _this3 = this;

	      return function () {
	        var btn = document.getElementById('feed-add-post-form-link-more');
	        var btnText = document.getElementById('feed-add-post-form-link-text');

	        if (!limited) {
	          btnText.innerHTML = name;

	          if (id !== _this3.config.id.listItem) {
	            btn.className = "feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-link-active feed-add-post-form-".concat(id, "-link");

	            _this3.changePostFormTab(id, false);
	          } else {
	            btn.className = "feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-".concat(id, "-link");
	          }
	        }

	        if (main_core.Type.isStringFilled(onclick)) {
	          BX.evalGlobal(onclick);
	        }

	        _this3.menu.popupWindow.close();
	      };
	    }
	  }, {
	    key: "changePostFormTab",
	    value: function changePostFormTab(type, iblock) {
	      if (this.clickDisabled) {
	        return false;
	      }

	      return this.setActive(type, iblock);
	    }
	  }, {
	    key: "setActive",
	    value: function setActive(type, iblock) {
	      var _this4 = this;

	      if (main_core.Type.isNull(type) || this.active === type && type !== this.config.id.listItem) {
	        return this.active;
	      } else if (!this.tabs[type]) {
	        return false;
	      }

	      var needAnimation = type !== this.config.id.task || this.isTaskTabLoaded();

	      if (needAnimation) {
	        this.startAnimation();
	      }

	      for (var ii in this.tabs) {
	        if (this.tabs.hasOwnProperty(ii) && ii !== type) {
	          this.tabs[ii].classList.remove('feed-add-post-form-link-active');

	          if (this.bodies[ii] == null || this.bodies[type] == null) {
	            continue;
	          }

	          for (var jj = 0; jj < this.bodies[ii].length; jj++) {
	            if (this.bodies[type][jj] != this.bodies[ii][jj]) {
	              main_core.Dom.adjust(this.bodies[ii][jj], {
	                style: {
	                  display: 'none'
	                }
	              });
	            }
	          }
	        }
	      }

	      if (!!this.tabs[type]) {
	        this.active = type;
	        var tabPosTab = BX.pos(this.tabs[type], true);
	        this.arrow.style.display = 'block';
	        this.arrow.style.top = "".concat(tabPosTab.bottom, "px");
	        var leftStart = parseInt(this.arrow.style.left) || 0;
	        var widthStart = parseInt(this.arrow.style.width) || 0;
	        new BX.easing({
	          duration: 200,
	          start: {
	            left: leftStart,
	            width: widthStart
	          },
	          finish: {
	            left: tabPosTab.left,
	            width: tabPosTab.width
	          },
	          transition: BX.easing.makeEaseInOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            _this4.arrow.style.left = "".concat(state.left, "px");
	            _this4.arrow.style.width = "".concat(state.width, "px");
	          },
	          complete: function complete() {
	            _this4.arrow.style.display = 'none';

	            _this4.tabs[type].classList.add('feed-add-post-form-link-active');
	          }
	        }).animate();

	        if (this.previousTab === this.config.id.file || type === this.config.id.file) {
	          var hasValuesFile = false;
	          var hasValuesDocs = false;
	          var messageBody = document.getElementById('divoPostFormLHE_blogPostForm');

	          if (!!messageBody && !!messageBody.childNodes && messageBody.childNodes.length > 0) {
	            for (var _ii in messageBody.childNodes) {
	              if (!messageBody.childNodes.hasOwnProperty(_ii)) {
	                continue;
	              }

	              if (messageBody.childNodes[_ii].className === 'file-selectdialog') {
	                var nodeFile = messageBody.childNodes[_ii];
	                var values1 = nodeFile.querySelector('.file-placeholder-tbody');
	                var values2 = nodeFile.querySelector('.feed-add-photo-block');

	                if (values1.rows > 0 || !!values2 && values2.length > 1) {
	                  hasValuesFile = true;
	                }
	              } else if (main_core.Type.isStringFilled(messageBody.childNodes[_ii].className) && (messageBody.childNodes[_ii].className.indexOf('wduf-selectdialog') >= 0 || messageBody.childNodes[_ii].className.indexOf('diskuf-selectdialog') >= 0)) {
	                var nodeDocs = messageBody.childNodes[_ii];
	                var webdavValues = nodeDocs.querySelectorAll('.wd-inline-file');
	                hasValuesDocs = !!webdavValues && webdavValues.length > 0;
	              } else if (main_core.Type.isDomNode(messageBody.childNodes[_ii]) && messageBody.childNodes[_ii].classList && !messageBody.childNodes[_ii].classList.contains('urlpreview') && !messageBody.childNodes[_ii].classList.contains('feed-add-post-strings-blocks')) {
	                main_core.Dom.adjust(messageBody.childNodes[_ii], {
	                  style: {
	                    display: type === this.config.id.file ? 'none' : ''
	                  }
	                });
	              }
	            }

	            if (type === this.config.id.file) {
	              if (!!window['PlEditorblogPostForm'] && !window['PlEditorblogPostForm'].SBPEBinded) {
	                window['PlEditorblogPostForm'].SBPEBinded = true;
	                main_core_events.EventEmitter.subscribe(window["PlEditorblogPostForm"].eventNode, 'onUploadsHasBeenChanged', function (event) {
	                  var wdObj = event.getData()[1];

	                  if (wdObj.dialogName === 'AttachFileDialog' && wdObj.urlUpload.indexOf('&dropped=Y') < 0) {
	                    wdObj.urlUpload = wdObj.agent.uploadFileUrl = wdObj.urlUpload.replace('&random_folder=Y', '&dropped=Y');
	                  }

	                  document.getElementById('bx-b-uploadfile-blogPostForm').setAttribute('bx-press', 'pressOn');

	                  if (_this4.active !== _this4.config.id.file) {
	                    _this4.changePostFormTab(_this4.config.id.message);
	                  }
	                });
	              }

	              window['PlEditorblogPostForm'].controllerInit('show');
	              messageBody.classList.add('feed-add-post-form', 'feed-add-post-edit-form', 'feed-add-post-edit-form-file');
	            } else {
	              messageBody.classList.remove('feed-add-post-form', 'feed-add-post-edit-form', 'feed-add-post-edit-form-file');

	              if (!hasValuesFile && !hasValuesDocs && document.getElementById('bx-b-uploadfile-blogPostForm').getAttribute('bx-press') === 'pressOut' && !!window['PlEditorblogPostForm']) {
	                window['PlEditorblogPostForm'].controllerInit('hide');
	              }
	            }
	          }
	        }

	        var editorForm = document.getElementById('divoPostFormLHE_blogPostForm');

	        if (editorForm && editorForm.style.display === 'none') {
	          main_core_events.EventEmitter.emit(editorForm, 'OnShowLHE', new main_core_events.BaseEvent({
	            compatData: ['justShow']
	          }));
	        }

	        if (type === this.config.id.listItem) {
	          main_core_events.EventEmitter.emit('onDisplayClaimLiveFeed', new main_core_events.BaseEvent({
	            compatData: [iblock]
	          }));
	        }

	        this.previousTab = type;

	        if (!!this.bodies[type]) {
	          for (var _jj = 0; _jj < this.bodies[type].length; _jj++) {
	            if (!!this.bodies[type][_jj]) {
	              main_core.Dom.adjust(this.bodies[type][_jj], {
	                style: {
	                  display: 'block'
	                }
	              });
	            }
	          }
	        }
	      }

	      if (needAnimation) {
	        this.endAnimation();
	      }

	      if (type !== this.config.id.listItem) {
	        this.restoreMoreMenu();
	      }

	      this.emit('changePostFormTab', {
	        type: type
	      });
	      return this.active;
	    }
	  }, {
	    key: "isTaskTabLoaded",
	    value: function isTaskTabLoaded() {
	      var contentContainer = document.getElementById('feed-add-post-content-tasks-container');
	      return contentContainer && contentContainer.children.length;
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      this.active = null;
	      var postEditSlider = false;
	      var currentSlider = window !== top.window ? BX.SidePanel.Instance.getSliderByWindow(window) : null;

	      if (window !== top.window) // slider
	        {
	          if (currentSlider && currentSlider.url.match(/\/user\/(\d+)\/blog\/edit\//)) {
	            postEditSlider = true;
	          }
	        }

	      if (!postEditSlider) {
	        this.changePostFormTab("message");
	        var formInstance = PostForm.getInstance();

	        if (formInstance && main_core.Type.isDomNode(formInstance.containerMicroInner)) {
	          formInstance.containerMicroInner.style.display = 'block';
	        }

	        this.startAnimation();
	      }

	      var editorForm = document.getElementById('divoPostFormLHE_blogPostForm');

	      if (editorForm) {
	        main_core_events.EventEmitter.emit(editorForm, 'OnShowLHE', new main_core_events.BaseEvent({
	          compatData: [false]
	        }));
	      }

	      main_core_events.EventEmitter.emit('onExtAutoSaveReset_blogPostForm', new main_core_events.BaseEvent({
	        compatData: []
	      }));

	      if (!postEditSlider) {
	        this.endAnimation();
	      } else {
	        if (currentSlider) {
	          main_core_events.EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', new main_core_events.BaseEvent({
	            compatData: [currentSlider.getEvent('onClose')]
	          }));
	        }

	        BX.SidePanel.Instance.close();
	      }
	    }
	  }, {
	    key: "startAnimation",
	    value: function startAnimation() {
	      if (this.animation) {
	        this.animation.stop();
	      }

	      var container = document.getElementById('microblog-form');

	      if (!container) {
	        return;
	      }

	      if (PostForm.getInstance().animationStartHeight > 0) {
	        this.animationStartHeight = PostForm.getInstance().animationStartHeight;
	        PostForm.getInstance().animationStartHeight = 0;
	      } else {
	        this.animationStartHeight = container.parentNode.offsetHeight;
	      }

	      container.parentNode.style.height = "".concat(this.animationStartHeight, "px");
	      container.parentNode.style.overflowY = 'hidden';
	      container.parentNode.style.position = 'relative';
	      container.style.opacity = 0;
	    }
	  }, {
	    key: "endAnimation",
	    value: function endAnimation() {
	      var _this5 = this;

	      var container = document.getElementById('microblog-form');

	      if (!container) {
	        return;
	      }

	      this.animation = new BX.easing({
	        duration: 500,
	        start: {
	          height: this.animationStartHeight,
	          opacity: 0
	        },
	        finish: {
	          height: container.offsetHeight + container.offsetTop,
	          opacity: 100
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: function step(state) {
	          container.parentNode.style.height = "".concat(state.height, "px");
	          container.style.opacity = state.opacity / 100;
	        },
	        complete: function complete() {
	          container.style.cssText = '';
	          container.parentNode.style.cssText = '';
	          _this5.animation = null;
	        }
	      });
	      this.animation.animate();
	    }
	  }, {
	    key: "showMoreMenu",
	    value: function showMoreMenu() {
	      if (!this.menu) {
	        this.menu = main_popup.MenuManager.create('feed-add-post-form-popup', document.getElementById('feed-add-post-form-link-text'), this.menuItems, {
	          className: 'feed-add-post-form-popup',
	          closeByEsc: true,
	          offsetTop: 5,
	          offsetLeft: 3,
	          angle: true
	        });
	      }

	      this.menu.popupWindow.show();
	    }
	  }, {
	    key: "restoreMoreMenu",
	    value: function restoreMoreMenu() {
	      var itemCnt = this.menuItems.length;

	      if (itemCnt < 1) {
	        return;
	      }

	      for (var i = 0; i < itemCnt; i++) {
	        if (this.active === this.menuItems[i]['tabId']) {
	          return;
	        }
	      }

	      var btn = document.getElementById('feed-add-post-form-link-more');
	      var btnText = document.getElementById('feed-add-post-form-link-text');
	      btn.className = 'feed-add-post-form-link feed-add-post-form-link-more';
	      btnText.innerHTML = main_core.Loc.getMessage('SBPE_MORE');
	    }
	  }, {
	    key: "getTaskForm",
	    value: function getTaskForm() {
	      var _this6 = this;

	      var tabContainer = document.getElementById('feed-add-post-form-tab-tasks') && document.getElementById('feed-add-post-form-tab-tasks').style.display !== 'none' ? document.getElementById('feed-add-post-form-tab-tasks') : document.getElementById('feed-add-post-form-link-more');
	      var content = document.getElementById('feed-add-post-content-tasks');
	      var contentContainer = document.getElementById('feed-add-post-content-tasks-container');

	      if (contentContainer && contentContainer.innerHTML.length <= 0 && !this.clickDisabled) {
	        this.clickDisabled = true;
	        PostForm.getInstance().showWait(contentContainer);
	        this.startAnimation();
	        var componentParameters = {
	          GROUP_ID: main_core.Loc.getMessage('TASK_SOCNET_GROUP_ID'),
	          PATH_TO_USER_TASKS: main_core.Loc.getMessage('PATH_TO_USER_TASKS'),
	          PATH_TO_USER_TASKS_TASK: main_core.Loc.getMessage('PATH_TO_USER_TASKS_TASK'),
	          PATH_TO_GROUP_TASKS: main_core.Loc.getMessage('PATH_TO_GROUP_TASKS'),
	          PATH_TO_GROUP_TASKS_TASK: main_core.Loc.getMessage('PATH_TO_GROUP_TASKS_TASK'),
	          PATH_TO_USER_PROFILE: main_core.Loc.getMessage('PATH_TO_USER_PROFILE'),
	          PATH_TO_GROUP: main_core.Loc.getMessage('PATH_TO_GROUP'),
	          PATH_TO_USER_TASKS_PROJECTS_OVERVIEW: main_core.Loc.getMessage('PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'),
	          PATH_TO_USER_TASKS_TEMPLATES: main_core.Loc.getMessage('PATH_TO_USER_TASKS_TEMPLATES'),
	          PATH_TO_USER_TEMPLATES_TEMPLATE: main_core.Loc.getMessage('PATH_TO_USER_TEMPLATES_TEMPLATE'),
	          ENABLE_FOOTER: 'N',
	          TEMPLATE_CONTROLLER_ID: 'livefeed_task_form',
	          ENABLE_FORM: 'N',
	          BACKURL: main_core.Loc.getMessage('TASK_SUBMIT_BACKURL')
	        };
	        main_core.ajax.runComponentAction('bitrix:tasks.task', 'uiEdit', {
	          mode: 'class',
	          data: {
	            parameters: {
	              COMPONENT_PARAMETERS: componentParameters
	            }
	          }
	        }).then(function (response) {
	          main_core.Runtime.html(contentContainer, response.data.html).then(function () {
	            _this6.clickDisabled = false;

	            _this6.closeWait(contentContainer);

	            _this6.endAnimation();

	            main_core_events.EventEmitter.emit(document.getElementById('divlivefeed_task_form'), 'OnShowLHE', new main_core_events.BaseEvent({
	              compatData: ['justShow']
	            }));
	          });
	          main_core.Dom.adjust(content, {
	            style: {
	              display: 'block'
	            }
	          });
	        }, function (response) {
	          _this6.clickDisabled = false;

	          _this6.closeWait(contentContainer);

	          _this6.endAnimation();

	          if (response.errors && response.errors.length) {
	            var errors = [];
	            response.errors.forEach(function (error) {
	              errors.push(error.message);
	            });
	            throw new Error(errors.join(' '));
	          }
	        });
	      } else {
	        this.startAnimation();
	        this.endAnimation();
	      }
	    }
	  }, {
	    key: "closeWait",
	    value: function closeWait(node) {
	      var waiterNode = node.bxmsg;

	      if (waiterNode && waiterNode.parentNode) {
	        for (var i = 0, len = this.lastWait.length; i < len; i++) {
	          if (waiterNode === this.lastWait[i]) {
	            this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
	            break;
	          }
	        }

	        waiterNode.parentNode.removeChild(waiterNode);

	        if (node) {
	          node.bxmsg = null;
	        }

	        main_core.Dom.clean(waiterNode);
	        main_core.Dom.remove(waiterNode);
	      }
	    }
	  }, {
	    key: "getLists",
	    value: function getLists() {
	      var _this7 = this;

	      var tabContainer = document.getElementById('feed-add-post-form-tab-lists') && document.getElementById('feed-add-post-form-tab-lists').style.display !== 'none' ? document.getElementById('feed-add-post-form-tab-lists') : document.getElementById('feed-add-post-form-link-more');
	      var tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists');
	      var tabsDefault = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
	      var menuItemsListsDefault = [];
	      var menuItemsLists = [];

	      if (tabs.length) {
	        menuItemsLists = this.getMenuItems(tabs, this.createOnclickLists);
	        menuItemsListsDefault = this.getMenuItemsDefault(tabsDefault);
	        menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);
	        this.showMoreMenuLists(menuItemsLists);
	      } else {
	        var siteId = null;

	        if (document.getElementById('bx-lists-select-site-id')) {
	          siteId = document.getElementById('bx-lists-select-site-id').value;
	        }

	        main_core.ajax({
	          method: 'POST',
	          dataType: 'json',
	          url: '/bitrix/components/bitrix/socialnetwork.blog.post.edit/post.ajax.php',
	          data: {
	            bitrix_processes: 1,
	            siteId: siteId,
	            sessid: main_core.Loc.getMessage('bitrix_sessid')
	          },
	          onsuccess: function onsuccess(result) {
	            if (result.success) {
	              for (var k in result.lists) {
	                if (!result.lists.hasOwnProperty(k)) {
	                  continue;
	                }

	                tabContainer.appendChild(main_core.Dom.create('span', {
	                  attrs: {
	                    'data-name': result.lists[k].NAME,
	                    'data-picture': result.lists[k].PICTURE,
	                    'data-description': result.lists[k].DESCRIPTION,
	                    'data-picture-small': result.lists[k].PICTURE_SMALL,
	                    'data-code': result.lists[k].CODE,
	                    'iblockId': result.lists[k].ID
	                  },
	                  props: {
	                    className: 'feed-add-post-form-link-lists',
	                    id: 'feed-add-post-form-tab-lists'
	                  },
	                  style: {
	                    display: 'none'
	                  }
	                }));
	              }

	              tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists');
	              menuItemsLists = _this7.getMenuItems(tabs, _this7.createOnclickLists);

	              if (!tabsDefault.length) {
	                for (var _k in result.permissions) {
	                  if (!result.permissions.hasOwnProperty(_k)) {
	                    continue;
	                  }

	                  var onclick = void 0;

	                  if (_k === 'new') {
	                    onclick = "document.location.href = \"".concat(document.getElementById('bx-lists-lists-page').value, "0/edit/\"");
	                  } else if (_k === 'market') {
	                    if (result.admin && document.getElementById('bx-lists-lists-page')) {
	                      onclick = "document.location.href = \"".concat(document.getElementById('bx-lists-lists-page').value, "?bp_catalog=y\"");
	                    } else {
	                      if (document.getElementById('bx-lists-random-string')) {
	                        onclick = "BX.Lists[\"LiveFeedClass_".concat(BX('bx-lists-random-string').value, "\"].errorPopup(\"").concat(main_core.Loc.getMessage('LISTS_CATALOG_PROCESSES_ACCESS_DENIED'), "\");");
	                      }
	                    }
	                  } else if (_k === 'settings') {
	                    onclick = "document.location.href = \"".concat(BX('bx-lists-lists-page').value, "\"");
	                  }

	                  tabContainer.appendChild(main_core.Dom.create('span', {
	                    attrs: {
	                      'data-name': result.permissions[_k],
	                      'data-picture-small': '',
	                      'data-key': _k,
	                      'data-onclick': onclick
	                    },
	                    props: {
	                      className: 'feed-add-post-form-link-lists-default',
	                      id: 'feed-add-post-form-tab-lists'
	                    },
	                    style: {
	                      display: 'none'
	                    }
	                  }));
	                }

	                tabsDefault = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
	              }

	              menuItemsListsDefault = _this7.getMenuItemsDefault(tabsDefault);
	              menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);

	              _this7.showMoreMenuLists(menuItemsLists);
	            } else {
	              tabContainer.appendChild(main_core.Dom.create('span', {
	                attrs: {
	                  'data-name': result.error,
	                  'data-picture-small': ''
	                },
	                props: {
	                  className: 'feed-add-post-form-link-lists-default',
	                  id: 'feed-add-post-form-tab-lists'
	                },
	                style: {
	                  display: 'none'
	                }
	              }));
	              tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
	              menuItemsLists = _this7.getMenuItems(tabs, false);

	              _this7.showMoreMenuLists(menuItemsLists);
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems(tabs, createOnclickLists) {
	      var menuItemsLists = [];

	      for (var i = 0; i < tabs.length; i++) {
	        var id = tabs[i].getAttribute('id').replace('feed-add-post-form-tab-', '');

	        if (createOnclickLists) {
	          menuItemsLists.push({
	            tabId: id,
	            text: BX.util.htmlspecialchars(tabs[i].getAttribute("data-name")),
	            className: "feed-add-post-form-".concat(id, " feed-add-post-form-").concat(id, "-item"),
	            onclick: createOnclickLists(id, [tabs[i].getAttribute('iblockId'), tabs[i].getAttribute('data-name'), tabs[i].getAttribute('data-description'), tabs[i].getAttribute('data-picture'), tabs[i].getAttribute('data-code')])
	          });
	        } else {
	          menuItemsLists.push({
	            tabId: id,
	            text: tabs[i].getAttribute('data-name'),
	            className: "feed-add-post-form-".concat(id),
	            onclick: ''
	          });
	        }
	      }

	      return menuItemsLists;
	    }
	  }, {
	    key: "getMenuItemsDefault",
	    value: function getMenuItemsDefault(tabs) {
	      var menuItemsLists = [];

	      for (var i = 0; i < tabs.length; i++) {
	        menuItemsLists.push({
	          text: BX.util.htmlspecialchars(tabs[i].getAttribute('data-name')),
	          className: "feed-add-post-form-lists-default-".concat(tabs[i].getAttribute('data-key')),
	          onclick: tabs[i].getAttribute('data-onclick')
	        });
	      }

	      return menuItemsLists;
	    }
	  }, {
	    key: "showMoreMenuLists",
	    value: function showMoreMenuLists(menuItemsLists) {
	      var menuBindElement = document.getElementById('feed-add-post-form-tab-lists').style.display !== 'none' ? document.getElementById('feed-add-post-form-tab-lists') : document.getElementById('feed-add-post-form-link-more');
	      this.listsMenu = main_popup.MenuManager.create('lists', menuBindElement, menuItemsLists, {
	        closeByEsc: true,
	        offsetTop: 5,
	        offsetLeft: 12,
	        angle: true
	      });
	      var spanIcon = document.getElementById('popup-window-content-menu-popup-lists').querySelectorAll('span.menu-popup-item-icon');
	      var spanDataPicture = menuBindElement.querySelectorAll('span.feed-add-post-form-link-lists');
	      var spanDataPictureDefault = menuBindElement.querySelectorAll('span.feed-add-post-form-link-lists-default');
	      spanDataPicture = Array.from(spanDataPicture).concat(Array.from(spanDataPictureDefault));

	      for (var i = 0; i < spanIcon.length; i++) {
	        if (!spanDataPicture[i].getAttribute('data-picture-small')) {
	          continue;
	        }

	        spanIcon[i].innerHTML = spanDataPicture[i].getAttribute('data-picture-small');
	      }

	      this.listsMenu.popupWindow.show();
	    }
	  }, {
	    key: "createOnclickLists",
	    value: function createOnclickLists(id, iblock) {
	      return function () {
	        PostFormTabs.getInstance().changePostFormTab(id, iblock);
	        PostFormTabs.getInstance().listsMenu.popupWindow.close();
	        PostFormTabs.getInstance().menu.popupWindow.close();
	      };
	    }
	  }]);
	  return PostFormTabs;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(PostFormTabs, "instance", null);

	var PostFormDateEnd = /*#__PURE__*/function () {
	  function PostFormDateEnd() {
	    babelHelpers.classCallCheck(this, PostFormDateEnd);
	    babelHelpers.defineProperty(this, "isInitialized", false);
	    babelHelpers.defineProperty(this, "popupShowingPeriods", null);
	    babelHelpers.defineProperty(this, "menuItems", []);
	    babelHelpers.defineProperty(this, "customDateStyleModifier", 'feed-add-post-expire-date-customize');
	    babelHelpers.defineProperty(this, "customDatePopupOptionClass", 'js-custom-date-end');
	    babelHelpers.defineProperty(this, "postExpireDateBlock", null);
	    babelHelpers.defineProperty(this, "formUfInputDateCustom", null);
	    babelHelpers.defineProperty(this, "formDateDuration", null);
	    babelHelpers.defineProperty(this, "formDateTimeEditing", null);
	    babelHelpers.defineProperty(this, "popupTrigger", null);
	    babelHelpers.defineProperty(this, "customDateSelectedTitle", null);
	    babelHelpers.defineProperty(this, "selectors", {
	      postExpireDateBlock: '.js-post-expire-date-block',
	      postEndTime: '.js-form-post-end-time',
	      postEditingEndTime: '.js-form-editing-post-end-time',
	      postEndPeriod: '.js-form-post-end-period',
	      popupTrigger: '.js-important-till-popup-trigger',
	      customDateFinal: '.js-date-post-showing-custom',
	      durationOptionsContainer: '.js-post-showing-duration-options-container',
	      durationOption: '.js-post-showing-duration-option'
	    });
	    this.init();
	  }

	  babelHelpers.createClass(PostFormDateEnd, [{
	    key: "init",
	    value: function init() {
	      if (this.isInitialized) {
	        return;
	      }

	      this.addEventHandlers();

	      if (!this.formDateTimeEditing.value) {
	        this.customDateSelectedTitle.innerText = this.getCurrentDate();
	      }

	      this.isInitialized = true;
	    }
	  }, {
	    key: "addEventHandlers",
	    value: function addEventHandlers() {
	      var _this = this;

	      this.postExpireDateBlock = document.querySelector(this.selectors.postExpireDateBlock);
	      this.formUfInputDateCustom = document.querySelector(this.selectors.postEndTime);
	      this.formDateDuration = document.querySelector(this.selectors.postEndPeriod);
	      this.formDateTimeEditing = document.querySelector(this.selectors.postEditingEndTime);
	      this.popupTrigger = document.querySelector(this.selectors.popupTrigger);

	      if (this.popupTrigger) {
	        this.popupTrigger.addEventListener('click', function () {
	          _this.showPostEndPeriodsPopup();
	        });
	      }

	      this.customDateSelectedTitle = document.querySelector(this.selectors.customDateFinal);

	      if (this.customDateSelectedTitle) {
	        this.customDateSelectedTitle.addEventListener('click', function () {
	          var curDate = new Date();
	          var curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset() * 60;

	          if (_this.formDateTimeEditing.value) {
	            curDate = BX.parseDate(_this.formDateTimeEditing.value);
	            curTimestamp = BX.date.convertToUTC(curDate);
	          }

	          BX.calendar({
	            node: _this.customDateSelectedTitle,
	            form: 'blogPostForm',
	            value: curTimestamp,
	            bTime: false,
	            callback: function callback() {
	              return true;
	            },
	            callback_after: _this.onEndDateSet.bind(_this)
	          });
	        });
	      }
	    }
	  }, {
	    key: "showPostEndPeriodsPopup",
	    value: function showPostEndPeriodsPopup() {
	      if (!this.popupShowingPeriods) {
	        this.createPopupShowingPeriods();
	      }

	      this.popupShowingPeriods.popupWindow.show();
	    }
	  }, {
	    key: "createPopupShowingPeriods",
	    value: function createPopupShowingPeriods() {
	      if (this.menuItems.length <= 0) {
	        this.menuItems = this.createPopupItems();
	      }

	      this.popupShowingPeriods = main_popup.MenuManager.create('feed-add-post-form-popup42', document.getElementById('js-post-expire-date-wrapper'), this.menuItems, {
	        className: "feed-add-post-expire-date-options",
	        closeByEsc: true,
	        angle: true
	      });
	    }
	  }, {
	    key: "createPopupItems",
	    value: function createPopupItems() {
	      var _this2 = this;

	      var menuPostDurationItems = [];
	      var selectOptions = document.querySelector(this.selectors.durationOptionsContainer).querySelectorAll(this.selectors.durationOption);

	      if (!selectOptions) {
	        return menuPostDurationItems;
	      }

	      selectOptions.forEach(function (element) {
	        menuPostDurationItems.push({
	          onclick: _this2.onPopupItemClick.bind(_this2),
	          dataset: {
	            value: element.getAttribute('data-value'),
	            "class": element.getAttribute('data-class')
	          },
	          text: element.getAttribute('data-text'),
	          className: "menu-popup-item menu-popup-no-icon ".concat(element.getAttribute('data-class'))
	        });
	      });
	      return menuPostDurationItems;
	    }
	  }, {
	    key: "onPopupItemClick",
	    value: function onPopupItemClick(event) {
	      var element = event.currentTarget;

	      if (element.getAttribute('data-class') === this.customDatePopupOptionClass) {
	        this.postExpireDateBlock.classList.add(this.customDateStyleModifier);

	        if (this.formDateTimeEditing.value) {
	          this.formUfInputDateCustom.value = this.formDateTimeEditing.value;
	          this.customDateSelectedTitle.innerText = this.formDateTimeEditing.value;
	        } else {
	          this.formUfInputDateCustom.value = this.getCurrentDate();
	        }
	      } else {
	        this.postExpireDateBlock.classList.remove(this.customDateStyleModifier);
	        this.formUfInputDateCustom.value = null;
	      }

	      this.popupTrigger.innerText = element.innerText.toLowerCase();
	      this.formDateDuration.value = element.getAttribute('data-value').toUpperCase();
	      this.popupShowingPeriods.popupWindow.close();
	    }
	  }, {
	    key: "onEndDateSet",
	    value: function onEndDateSet(value) {
	      if (!value) {
	        return;
	      }

	      this.formDateTimeEditing.value = this.getFormattedDate(value);
	      this.formUfInputDateCustom.value = this.getFormattedDate(value);
	      this.customDateSelectedTitle.innerText = this.getFormattedDate(value);
	    }
	  }, {
	    key: "getFormattedDate",
	    value: function getFormattedDate(value) {
	      return BX.date.format(BX.date.convertBitrixFormat(main_core.Loc.getMessage('FORMAT_DATE')), value);
	    }
	  }, {
	    key: "getCurrentDate",
	    value: function getCurrentDate() {
	      return this.getFormattedDate(new Date());
	    }
	  }]);
	  return PostFormDateEnd;
	}();

	var PostFormGratSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PostFormGratSelector, _EventEmitter);
	  babelHelpers.createClass(PostFormGratSelector, null, [{
	    key: "setInstance",
	    value: function setInstance(instance) {
	      PostFormGratSelector.instance = instance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      return PostFormGratSelector.instance;
	    }
	  }]);

	  function PostFormGratSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, PostFormGratSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostFormGratSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "popupWindow", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sendEvent", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "gratsContentElement", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "itemSelectedImageItem", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "itemSelectedInput", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "gratsList", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selector", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "config", {
	      fields: {
	        employeesValue: {
	          name: 'GRAT_DEST_DATA'
	        }
	      }
	    });

	    _this.init(params);

	    PostFormGratSelector.setInstance(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(PostFormGratSelector, [{
	    key: "init",
	    value: function init(params) {
	      var _this2 = this;

	      if (!params.name) {
	        params.name = 'lm';
	      }

	      this.itemSelectedImageItem[params.name] = params.itemSelectedImageItem;
	      this.itemSelectedInput[params.name] = params.itemSelectedInput;
	      this.gratsList[params.name] = params.gratsList;
	      this.itemSelectedImageItem[params.name].addEventListener('click', function (e) {
	        _this2.openDialog(params.name);

	        e.preventDefault();
	      });
	      this.createEntitySelector(params.entitySelectorParams);
	    }
	  }, {
	    key: "openDialog",
	    value: function openDialog(name) {
	      var _this3 = this;

	      if (!name) {
	        name = 'lm';
	      }

	      if (this.popupWindow != null) {
	        this.popupWindow.close();
	        return false;
	      }

	      var gratItems = [];

	      for (var i = 0; i < this.gratsList[name].length; i++) {
	        gratItems[gratItems.length] = main_core.Dom.create('span', {
	          props: {
	            className: "feed-add-grat-box ".concat(this.gratsList[name][i].style)
	          },
	          attrs: {
	            'data-title': this.gratsList[name][i].title,
	            'data-code': this.gratsList[name][i].code,
	            'data-style': this.gratsList[name][i].style
	          },
	          events: {
	            click: function click(e) {
	              var node = e.currentTarget;

	              _this3.selectItem(name, node.getAttribute('data-code'), node.getAttribute('data-style'), node.getAttribute('data-title'));

	              e.preventDefault();
	            }
	          }
	        });
	      }

	      var gratRows = [];
	      var rownum = 1;

	      for (var _i = 0; _i < gratItems.length; _i++) {
	        if (_i >= gratItems.length / 2) {
	          rownum = 2;
	        }

	        if (main_core.Type.isNil(gratRows[rownum])) {
	          gratRows[rownum] = main_core.Dom.create('div', {
	            props: {
	              className: 'feed-add-grat-list-row'
	            }
	          });
	        }

	        gratRows[rownum].appendChild(gratItems[_i]);
	      }

	      this.gratsContentElement = main_core.Dom.create('div', {
	        children: [main_core.Dom.create('div', {
	          props: {
	            className: 'feed-add-grat-list-title'
	          },
	          html: main_core.Loc.getMessage('BLOG_GRAT_POPUP_TITLE')
	        }), main_core.Dom.create('div', {
	          props: {
	            className: 'feed-add-grat-list'
	          },
	          children: gratRows
	        })]
	      });
	      this.popupWindow = new main_popup.Popup('BXSocNetGratSelector', document.getElementById('feed-add-post-grat-type-selected'), {
	        autoHide: true,
	        offsetLeft: 25,
	        bindOptions: {
	          forceBindPosition: true
	        },
	        closeByEsc: true,
	        closeIcon: {
	          top: '5px',
	          right: '10px'
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this3.popupWindow.destroy();
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            _this3.popupWindow = null;
	          }
	        },
	        content: this.gratsContentElement,
	        angle: {
	          position: 'bottom',
	          offset: 20
	        },
	        lightShadow: true
	      });
	      this.popupWindow.setAngle({});
	      this.popupWindow.show();
	      return true;
	    }
	  }, {
	    key: "selectItem",
	    value: function selectItem(name, code, style, title) {
	      var gratSpan = this.itemSelectedImageItem[name].querySelector('span');

	      if (gratSpan) {
	        gratSpan.className = "feed-add-grat-box ".concat(style);
	      }

	      this.itemSelectedImageItem[name].title = title;
	      this.itemSelectedInput[name].value = code;
	      this.popupWindow.close();
	    }
	  }, {
	    key: "createEntitySelector",
	    value: function createEntitySelector(params) {
	      var _this4 = this;

	      this.selector = new ui_entitySelector.TagSelector({
	        id: params.id,
	        dialogOptions: {
	          id: params.id,
	          context: 'GRATITUDE',
	          preselectedItems: main_core.Type.isArray(params.preselectedItems) ? params.preselectedItems : [],
	          events: {
	            'Item:onSelect': function ItemOnSelect(event) {
	              _this4.recalcValue(event.getTarget().getSelectedItems(), params.inputNodeId);
	            },
	            'Item:onDeselect': function ItemOnDeselect(event) {
	              _this4.recalcValue(event.getTarget().getSelectedItems(), params.inputNodeId);
	            }
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              emailUsers: false,
	              inviteEmployeeLink: false,
	              intranetUsersOnly: true
	            }
	          }, {
	            id: 'department',
	            options: {
	              selectMode: 'usersOnly'
	            }
	          }]
	        },
	        addButtonCaption: main_core.Loc.getMessage('BLOG_GRATMEDAL_1'),
	        addButtonCaptionMore: main_core.Loc.getMessage('BLOG_GRATMEDAL_1')
	      });
	      this.selector.renderTo(document.getElementById(params.tagNodeId));
	      this.selector.subscribe('onContainerClick', function () {
	        _this4.emit('Selector::onContainerClick');
	      });
	    }
	  }, {
	    key: "recalcValue",
	    value: function recalcValue(selectedItems, inputNodeId) {
	      if (!main_core.Type.isArray(selectedItems) || !document.getElementById(inputNodeId)) {
	        return;
	      }

	      var result = [];
	      selectedItems.forEach(function (item) {
	        result.push([item.entityId, item.id]);
	      });
	      document.getElementById(inputNodeId).value = JSON.stringify(result);
	    }
	  }]);
	  return PostFormGratSelector;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(PostFormGratSelector, "instance", null);

	var PostFormEditor = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PostFormEditor, _EventEmitter);
	  babelHelpers.createClass(PostFormEditor, null, [{
	    key: "setInstance",
	    value: function setInstance(id, instance) {
	      PostFormEditor.instance[id] = instance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance(id) {
	      return PostFormEditor.instance[id];
	    }
	  }]);

	  function PostFormEditor(formID, params) {
	    var _this;

	    babelHelpers.classCallCheck(this, PostFormEditor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostFormEditor).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "disabled", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "formId", '');

	    _this.init(formID, params);

	    PostFormEditor.setInstance(formID, babelHelpers.assertThisInitialized(_this));
	    window['setBlogPostFormSubmitted'] = _this.setBlogPostFormSubmitted.bind(babelHelpers.assertThisInitialized(_this));
	    window['submitBlogPostForm'] = _this.submitBlogPostForm.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(PostFormEditor, [{
	    key: "init",
	    value: function init(formID, params) {
	      var _this2 = this;

	      this.disabled = false;
	      this.formId = formID;
	      this.formParams = {
	        editorID: params.editorID,
	        showTitle: !!params.showTitle,
	        submitted: false,
	        text: params.text,
	        autoSave: params.autoSave,
	        handler: LHEPostForm && LHEPostForm.getHandler(params.editorID),
	        editor: LHEPostForm && LHEPostForm.getEditor(params.editorID),
	        restoreAutosave: !!params.restoreAutosave,
	        createdFromEmail: !!params.createdFromEmail
	      };
	      main_core_events.EventEmitter.subscribe('onInitialized', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 2),
	            obj = _event$getData2[0],
	            form = _event$getData2[1];

	        _this2.onHandlerInited(obj, form);
	      });

	      if (this.formParams.handler) {
	        this.onHandlerInited(this.formParams.handler, formID);
	      }

	      main_core_events.EventEmitter.subscribe('OnEditorInitedAfter', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	            editor = _event$getData4[0];

	        _this2.onEditorInited(editor);
	      });

	      if (this.formParams.editor) {
	        this.onEditorInited(this.formParams.editor);
	      }

	      main_core_events.EventEmitter.subscribe('onSocNetLogMoveBody', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 1),
	            p = _event$getData6[0];

	        if (p === 'sonet_log_microblog_container') {
	          _this2.reinit();
	        }
	      });
	      main_core.Event.ready(function () {
	        if (main_core.Browser.isIE() && document.getElementById('POST_TITLE')) {
	          var showTitlePlaceholderBlur = function showTitlePlaceholderBlur() {
	            var node = document.getElementById('POST_TITLE');

	            if (!node || node.value === node.getAttribute('placeholder')) {
	              node.value = node.getAttribute('placeholder');
	              node.classList.remove('feed-add-post-inp-active');
	            }
	          };

	          main_core.Event.bind(document.getElementById('POST_TITLE'), 'blur', showTitlePlaceholderBlur);
	          showTitlePlaceholderBlur();

	          document.getElementById('POST_TITLE').__onchange = function (e) {
	            var node = document.getElementById('POST_TITLE');

	            if (node.value === node.getAttribute('placeholder')) {
	              node.value = '';
	            }

	            if (node.className.indexOf('feed-add-post-inp-active') < 0) {
	              node.classList.add('feed-add-post-inp-active');
	            }
	          };

	          main_core.Event.bind(document.getElementById('POST_TITLE'), 'click', document.getElementById('POST_TITLE').__onchange);
	          main_core.Event.bind(document.getElementById('POST_TITLE'), 'keydown', document.getElementById('POST_TITLE').__onchange);
	          main_core.Event.bind(document.getElementById('POST_TITLE').form, 'submit', function () {
	            var node = document.getElementById('POST_TITLE');

	            if (node.value === node.getAttribute('placeholder')) {
	              node.value = '';
	            }
	          });
	        }

	        if (params.activeTab !== '') {
	          PostFormTabs.getInstance().changePostFormTab(params.activeTab);
	        }

	        PostFormTabs.getInstance().subscribe('changePostFormTab', _this2.checkHideAlert.bind(_this2));

	        if (PostFormGratSelector.getInstance()) {
	          PostFormGratSelector.getInstance().subscribe('Selector::onContainerClick', _this2.hideAlert.bind(_this2));
	        }
	      });
	    }
	  }, {
	    key: "showPanelTitle",
	    value: function showPanelTitle(show, saveChanges) {
	      show = show === true || show === false ? show : document.getElementById('blog-title').style.display && document.getElementById('blog-title').style.display === 'none';
	      saveChanges = saveChanges !== false;
	      var showTitleValue = this.formParams.showTitle;
	      var node = document.getElementById("lhe_button_title_".concat(this.formId));
	      var nodeBlock = document.getElementById("feed-add-post-block".concat(this.formId));
	      var stv = document.getElementById('show_title') || {};

	      if (show) {
	        BX.show(document.getElementById('blog-title'));

	        if (document.getElementById('POST_TITLE')) {
	          document.getElementById('POST_TITLE').focus();
	        }

	        this.formParams.showTitle = true;
	        stv.value = 'Y';

	        if (node) {
	          node.classList.add('feed-add-post-form-btn-active');
	        }

	        if (nodeBlock) {
	          nodeBlock.classList.add('blog-post-edit-open');
	        }
	      } else {
	        BX.hide(document.getElementById('blog-title'));
	        this.formParams.showTitle = false;
	        stv.value = "N";

	        if (node) {
	          node.classList.remove('feed-add-post-form-btn-active');
	        }
	      }

	      if (saveChanges) {
	        BX.userOptions.save('socialnetwork', 'postEdit', 'showTitle', this.formParams.showTitle ? 'Y' : 'N');
	      } else {
	        this.formParams.showTitle = showTitleValue;
	      }
	    }
	  }, {
	    key: "setBlogPostFormSubmitted",
	    value: function setBlogPostFormSubmitted(value) {
	      if (document.getElementById('blog-submit-button-save')) {
	        if (value) {
	          document.getElementById('blog-submit-button-save').classList.add('ui-btn-clock');
	        } else {
	          document.getElementById('blog-submit-button-save').classList.remove('ui-btn-clock');
	        }
	      }

	      this.formParams.submitted = value;
	      this.disabled = value;
	    }
	  }, {
	    key: "submitBlogPostForm",
	    value: function submitBlogPostForm(editor, value) {
	      var _this3 = this;

	      if (this.disabled) {
	        return;
	      }

	      if (!main_core.Type.isObject(editor)) {
	        value = editor;
	        editor = LHEPostForm.getEditor(this.formParams.editorID);
	      }

	      if (editor && editor.id === this.formParams.editorID) {
	        if (this.formParams.submitted) {
	          return false;
	        }

	        editor.SaveContent();

	        if (!value) {
	          value = 'save';
	        }

	        if (document.getElementById('blog-title').style.display === 'none') {
	          document.getElementById('POST_TITLE').value = '';
	        }

	        var submitButton = this.getSubmitButton({
	          buttonType: value
	        });

	        if (submitButton) {
	          submitButton.classList.add('ui-btn-clock');
	          this.disabled = true;
	          window.addEventListener('beforeunload', function (event) {
	            // is called on every sumbit, with or without dialog
	            setTimeout(function () {
	              BX.removeClass(submitButton, 'ui-btn-clock');
	              _this3.disabled = false;
	              _this3.formParams.submitted = false;
	            }, 3000); // timeout needed to process a form on a back-end
	          });
	        }

	        var actionUrl = '';
	        var activeTab = PostFormTabs.getInstance().active;

	        if (main_core.Type.isStringFilled(activeTab)) {
	          actionUrl = document.getElementById(this.formId).action;
	          main_core.Uri.removeParam(actionUrl, ['b24statTab']);
	          main_core.Uri.addParam(actionUrl, {
	            b24statTab: activeTab
	          });
	          document.getElementById(this.formId).action = actionUrl;
	        }

	        if ([PostFormTabs.getInstance().config.id.message, PostFormTabs.getInstance().config.id.file, PostFormTabs.getInstance().config.id.gratitude, PostFormTabs.getInstance().config.id.important, PostFormTabs.getInstance().config.id.vote].includes(activeTab)) {
	          if (!this.checkDestinationValue({
	            buttonType: value
	          })) {
	            return;
	          }
	        }

	        if (activeTab === PostFormTabs.getInstance().config.id.gratitude && PostFormGratSelector.getInstance()) {
	          if (!this.checkEmployeesValue({
	            buttonType: value
	          })) {
	            return;
	          }
	        }

	        setTimeout(function () {
	          BX.submit(document.getElementById(_this3.formId), value);
	          _this3.formParams.submitted = true;
	        }, 100);
	      }
	    }
	  }, {
	    key: "checkDestinationValue",
	    value: function checkDestinationValue(_ref) {
	      var buttonType = _ref.buttonType;

	      if (main_core.Type.isUndefined(MPFEntitySelector)) {
	        return true;
	      }

	      var tagSelector = new MPFEntitySelector({
	        id: "oPostFormLHE_".concat(this.formId)
	      });

	      if (!tagSelector || !main_core.Type.isArray(tagSelector.tags) || tagSelector.tags.length > 0) {
	        return true;
	      }

	      this.enableSubmitButton({
	        buttonType: buttonType
	      });
	      this.showBottomAlert({
	        text: main_core.Loc.getMessage('BLOG_POST_EDIT_T_GRAT_ERROR_NO_DESTINATION')
	      });
	      tagSelector.subscribeOnce('onContainerClick', this.hideAlert);
	      return false;
	    }
	  }, {
	    key: "checkEmployeesValue",
	    value: function checkEmployeesValue(_ref2) {
	      var buttonType = _ref2.buttonType;
	      var employeesValueNode = document.getElementById(this.formId).elements[PostFormGratSelector.getInstance().config.fields.employeesValue.name];

	      if (employeesValueNode && main_core.Type.isStringFilled(employeesValueNode.value) && employeesValueNode.value !== '[]') {
	        return true;
	      }

	      this.enableSubmitButton({
	        buttonType: buttonType
	      });
	      this.showBottomAlert({
	        text: main_core.Loc.getMessage('BLOG_POST_EDIT_T_GRAT_ERROR_NO_EMPLOYEES')
	      });
	      return false;
	    }
	  }, {
	    key: "checkHideAlert",
	    value: function checkHideAlert(event) {
	      var _event$getData7 = event.getData(),
	          type = _event$getData7.type;

	      if (type === PostFormTabs.getInstance().config.id.gratitude) {
	        return;
	      }

	      this.hideAlert();
	    }
	  }, {
	    key: "hideAlert",
	    value: function hideAlert() {
	      var alertNode = document.getElementById('feed-add-post-bottom-alertblogPostForm');

	      if (!alertNode) {
	        return;
	      }

	      main_core.Dom.clean(alertNode);
	    }
	  }, {
	    key: "enableSubmitButton",
	    value: function enableSubmitButton(_ref3) {
	      var buttonType = _ref3.buttonType;
	      var submitButton = this.getSubmitButton({
	        buttonType: buttonType
	      });

	      if (submitButton) {
	        submitButton.classList.remove('ui-btn-clock');
	        this.disabled = false;
	      }
	    }
	  }, {
	    key: "getSubmitButton",
	    value: function getSubmitButton(_ref4) {
	      var buttonType = _ref4.buttonType;
	      var result = null;

	      if (buttonType === 'save' && document.getElementById('blog-submit-button-save')) {
	        result = document.getElementById('blog-submit-button-save');
	      } else if (buttonType === 'draft' && document.getElementById('blog-submit-button-draft')) {
	        result = document.getElementById('blog-submit-button-draft');
	      }

	      return result;
	    }
	  }, {
	    key: "showBottomAlert",
	    value: function showBottomAlert(params) {
	      if (!main_core.Type.isPlainObject(params) || !main_core.Type.isStringFilled(params.text)) {
	        return;
	      }

	      var alertNode = document.getElementById('feed-add-post-bottom-alertblogPostForm');

	      if (alertNode) {
	        main_core.Dom.clean(alertNode);
	        alertNode.appendChild(main_core.Dom.create('div', {
	          props: {
	            className: 'ui-alert ui-alert-danger'
	          },
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: 'ui-alert-message'
	            },
	            text: params.text
	          })]
	        }));
	      }
	    }
	  }, {
	    key: "onHandlerInited",
	    value: function onHandlerInited(obj, form) {
	      if (form !== this.formId) {
	        return;
	      }

	      this.formParams.handler = obj;
	      main_core_events.EventEmitter.subscribe(obj.eventNode, 'OnControlClick', function () {
	        PostFormTabs.getInstance().changePostFormTab(PostFormTabs.getInstance().config.id.message);
	      });
	      main_core_events.EventEmitter.subscribe(obj.eventNode, 'OnAfterShowLHE', this.OnAfterShowLHE.bind(this));
	      main_core_events.EventEmitter.subscribe(obj.eventNode, 'OnAfterHideLHE', this.OnAfterHideLHE.bind(this));

	      if (obj.eventNode.style.display == 'none') {
	        this.OnAfterHideLHE();
	      } else {
	        this.OnAfterShowLHE();
	      }
	    }
	  }, {
	    key: "OnAfterShowLHE",
	    value: function OnAfterShowLHE() {
	      var div = [document.getElementById('feed-add-post-form-notice-blockblogPostForm'), document.getElementById('feed-add-buttons-blockblogPostForm'), document.getElementById('feed-add-post-bottom-alertblogPostForm'), document.getElementById('feed-add-post-content-message-add-ins')];

	      for (var ii = 0; ii < div.length; ii++) {
	        if (!div[ii]) {
	          continue;
	        }

	        div[ii].classList.remove('feed-post-form-block-hidden');
	      }

	      if (this.formParams.showTitle) {
	        this.showPanelTitle(true, false);
	      }
	    }
	  }, {
	    key: "OnAfterHideLHE",
	    value: function OnAfterHideLHE() {
	      var div = [document.getElementById('feed-add-post-form-notice-blockblogPostForm'), document.getElementById('feed-add-buttons-blockblogPostForm'), document.getElementById('feed-add-post-bottom-alertblogPostForm'), document.getElementById('feed-add-post-content-message-add-ins')];

	      for (var ii = 0; ii < div.length; ii++) {
	        if (!div[ii]) {
	          continue;
	        }

	        div[ii].classList.add('feed-post-form-block-hidden');
	      }

	      if (this.formParams.showTitle) {
	        this.showPanelTitle(false, false);
	      }
	    }
	  }, {
	    key: "onEditorInited",
	    value: function onEditorInited(editor) {
	      var _this4 = this;

	      if (PostForm.getInstance().initedEditorsList.includes(editor.id)) {
	        return;
	      }

	      if (editor.id !== this.formParams.editorID) {
	        return;
	      }

	      this.formParams.editor = editor;

	      if (this.formParams.autoSave !== 'N') {
	        new PostFormAutoSave(this.formParams.autoSave, this.formParams.restoreAutosave);
	      }

	      var f = window[editor.id + 'Files'];
	      var handler = LHEPostForm.getHandler(editor.id);
	      var needToReparse = [];
	      var node = null;
	      var controller = null;

	      for (var id in handler.controllers) {
	        if (!handler.controllers.hasOwnProperty(id)) {
	          continue;
	        }

	        if (handler.controllers[id].parser && handler.controllers[id].parser.bxTag === 'postimage') {
	          controller = handler.controllers[id];
	          break;
	        }
	      }

	      var closure = function closure(a, b) {
	        return function () {
	          a.insertFile(b);
	        };
	      };

	      var closure2 = function closure2(a, b, c) {
	        return function () {
	          if (controller) {
	            controller.deleteFile(b, {});
	            main_core.Dom.remove(document.getElementById("wd-doc'".concat(b)));
	            main_core.ajax({
	              method: 'GET',
	              url: c
	            });
	          } else {
	            a.deleteFile(b, c, a, {
	              controlID: 'common'
	            });
	          }
	        };
	      };

	      for (var intId in f) {
	        if (!f.hasOwnProperty(intId)) {
	          continue;
	        }

	        if (controller) {
	          controller.addFile(f[intId]);
	        } else {
	          var _id = handler.checkFile(intId, "common", f[intId]);

	          needToReparse.push(intId);

	          if (!!_id && document.getElementById("wd-doc".concat(intId)) && !document.getElementById("wd-doc".concat(intId)).hasOwnProperty('bx-bound')) {
	            BX("wd-doc".concat(intId)).setAttribute('bx-bound', 'Y');

	            if ((node = document.getElementById("wd-doc".concat(intId)).querySelector('.feed-add-img-wrap')) && node) {
	              main_core.Event.bind(node, 'click', closure(handler, _id));
	              node.style.cursor = 'pointer';
	            }

	            if ((node = document.getElementById("wd-doc".concat(intId)).querySelector('.feed-add-img-title')) && node) {
	              main_core.Event.bind(node, 'click', closure(handler, _id));
	              node.style.cursor = 'pointer';
	            }
	          }
	        }

	        if ((node = document.getElementById("wd-doc".concat(intId)).querySelector('.feed-add-post-del-but')) && node) {
	          main_core.Event.bind(node, 'click', closure2(handler, intId, f[intId].del_url));
	          node.style.cursor = "pointer";
	        }
	      }

	      if (needToReparse.length > 0) {
	        editor.SaveContent();
	        var content = editor.GetContent();
	        content = content.replace(new RegExp('\\&\\#91\\;IMG ID=(' + needToReparse.join('|') + ')([WIDTHHEIGHT=0-9 ]*)\\&\\#93\\;', 'gim'), '[IMG ID=$1$2]');
	        editor.SetContent(content);
	        editor.Focus();
	      }

	      PostForm.getInstance().initedEditorsList.push(editor.id);
	      main_core_events.EventEmitter.subscribe(editor, 'OnSetViewAfter', function () {
	        if (_this4.formParams.createdFromEmail) {
	          if (editor.GetContent() === '') {
	            editor.SetContent("".concat(main_core.Loc.getMessage('CREATED_ON_THE_BASIC_OF_THE_MESSAGE')));
	          }

	          editor.Focus(true);
	        }
	      });
	    }
	  }, {
	    key: "reinit",
	    value: function reinit() {
	      if (!this.formParams.editorID) {
	        return;
	      }

	      if (main_core.Type.isFunction(this.formParams.editor)) {
	        this.formParams.editor(this.formParams.text);
	      } else {
	        setTimeout(this.reinit, 50);
	      }
	    }
	  }]);
	  return PostFormEditor;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(PostFormEditor, "instance", {});

	var PostFormAutoSave = /*#__PURE__*/function () {
	  function PostFormAutoSave(autoSaveRestoreMethod, initRestore) {
	    babelHelpers.classCallCheck(this, PostFormAutoSave);
	    this.init(autoSaveRestoreMethod, initRestore);
	  }

	  babelHelpers.createClass(PostFormAutoSave, [{
	    key: "init",
	    value: function init(autoSaveRestoreMethod, initRestore) {
	      var _this = this;

	      var formId = 'blogPostForm';
	      var form = document.getElementById(formId);
	      var titleID = 'POST_TITLE';
	      var title = document.getElementById(titleID);
	      var tags = form.TAGS;

	      if (!form) {
	        return;
	      }

	      initRestore = !main_core.Type.isUndefined(initRestore) ? !!initRestore : true;
	      main_core_events.EventEmitter.subscribe(form, 'onAutoSavePrepare', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 2),
	            ob = _event$getData2[0],
	            handler = _event$getData2[1];

	        ob.DISABLE_STANDARD_NOTIFY = true;
	        var _ob = ob;
	        setTimeout(function () {
	          _this.bindLHEEvents(_ob);
	        }, 100);
	      });
	      main_core_events.EventEmitter.subscribe(form, 'onAutoSave', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 2),
	            ob = _event$getData4[0],
	            formData = _event$getData4[1];

	        formData.TAGS = tags.value;
	        delete formData.POST_MESSAGE;
	      });

	      if (autoSaveRestoreMethod == 'Y') {
	        main_core_events.EventEmitter.subscribe(form, 'onAutoSaveRestoreFound', function (event) {
	          var _event$getData5 = event.getData(),
	              _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 2),
	              ob = _event$getData6[0],
	              data = _event$getData6[1];

	          var text = data["text".concat(formId)];
	          text = main_core.Type.isStringFilled(text) ? text.trim() : '';
	          var title = data[titleID];
	          title = main_core.Type.isStringFilled(title) ? title.trim() : '';

	          if (!main_core.Type.isStringFilled(text) && !main_core.Type.isStringFilled(title)) {
	            return;
	          }

	          ob.Restore();
	        });
	      } else {
	        main_core_events.EventEmitter.subscribe(form, 'onAutoSaveRestoreFound', function (event) {
	          var _event$getData7 = event.getData(),
	              _event$getData8 = babelHelpers.slicedToArray(_event$getData7, 2),
	              ob = _event$getData8[0],
	              data = _event$getData8[1];

	          var text = data["text".concat(formId)];
	          text = main_core.Type.isStringFilled(text) ? text.trim() : '';
	          var title = data[titleID];
	          title = main_core.Type.isStringFilled(title) ? title.trim() : '';

	          if (!main_core.Type.isStringFilled(text) && !main_core.Type.isStringFilled(title)) {
	            return;
	          }

	          var messageBody = document.getElementById('microoPostFormLHE_blogPostForm');
	          var textNode = main_core.Dom.create('DIV', {
	            attrs: {
	              className: 'feed-add-successfully'
	            },
	            children: [main_core.Dom.create('SPAN', {
	              attrs: {
	                className: 'feed-add-info-icon'
	              }
	            }), main_core.Dom.create('A', {
	              attrs: {
	                className: 'feed-add-info-text',
	                href: '#'
	              },
	              events: {
	                click: function click() {
	                  ob.Restore();
	                  textNode.parentNode.removeChild(textNode);
	                  return false;
	                }
	              },
	              text: main_core.Loc.getMessage('BLOG_POST_AUTOSAVE2')
	            })]
	          });

	          if (messageBody) {
	            messageBody.parentNode.insertBefore(textNode, messageBody);
	          }
	        });
	      }

	      if (initRestore) {
	        main_core_events.EventEmitter.subscribe(form, 'onAutoSaveRestore', function (event) {
	          var _event$getData9 = event.getData(),
	              _event$getData10 = babelHelpers.slicedToArray(_event$getData9, 2),
	              ob = _event$getData10[0],
	              data = _event$getData10[1];

	          title.value = data[titleID];

	          if (main_core.Type.isStringFilled(data[titleID]) && data[titleID] !== title.getAttribute('placeholder')) {
	            if (document.getElementById('divoPostFormLHE_blogPostForm').style.display !== 'none') {
	              PostFormEditor.getInstance(formId).showPanelTitle(true);
	            } else {
	              window.bShowTitle = true;
	            }

	            if (main_core.Type.isFunction(title.__onchange)) {
	              title.__onchange();
	            }
	          }

	          var formTags = window["BXPostFormTags_".concat(formId)];

	          if (data.TAGS.length > 0 && formTags) {
	            var _tags = formTags.addTag(data.TAGS);

	            if (_tags.length > 0) {
	              BX.show(formTags.tagsArea);
	            }
	          }

	          main_core_events.EventEmitter.emit('onAutoSaveRestoreDestination', new main_core_events.BaseEvent({
	            compatData: [{
	              formId: formId,
	              data: data
	            }]
	          }));

	          _this.bindLHEEvents(ob);
	        });
	      }
	    }
	  }, {
	    key: "bindLHEEvents",
	    value: function bindLHEEvents(_ob) {
	      var form = document.getElementById('blogPostForm');
	      var title = document.getElementById('POST_TITLE');
	      var tags = form.TAGS;
	      main_core.Event.bind(title, 'keydown', _ob.Init.bind(_ob));
	      main_core.Event.bind(tags, 'keydown', _ob.Init.bind(_ob));
	    }
	  }]);
	  return PostFormAutoSave;
	}();

	exports.PostForm = PostForm;
	exports.PostFormTabs = PostFormTabs;
	exports.PostFormDateEnd = PostFormDateEnd;
	exports.PostFormGratSelector = PostFormGratSelector;
	exports.PostFormAutoSave = PostFormAutoSave;
	exports.PostFormEditor = PostFormEditor;

}((this.BX.Socialnetwork.Livefeed = this.BX.Socialnetwork.Livefeed || {}),BX.Main,BX.UI.EntitySelector,BX.Main,BX,BX.Event));
//# sourceMappingURL=script.js.map
