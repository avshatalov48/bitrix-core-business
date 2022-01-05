this.BX = this.BX || {};
(function (exports,main_popup,ui_buttons,seo_ads_login,catalog_productSelector,main_core_events,ui_textcrop,main_core,ui_entitySelector) {
	'use strict';

	var SeoAccount = /*#__PURE__*/function () {
	  function SeoAccount(options) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, SeoAccount);
	    this.clientNode = options.clientNode;
	    this.avatarNode = options.avatarNode;
	    this.accountNode = options.accountNode;
	    this.instagramAccountNode = options.instagramAccountNode;
	    this.linkNode = options.linkNode;
	    this.provider = options.provider;
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.uiNodes = options.uiNodes;
	    this._helper = Helper.getInstance(this, []);
	    this.loaded = [];
	    this.clientSelector = new BX.Seo.Ads.ClientSelector(options.clientBlock, {
	      selected: this.provider.PROFILE,
	      items: this.provider.CLIENTS,
	      canAddItems: true,
	      events: {
	        onNewItem: function onNewItem() {
	          seo_ads_login.LoginFactory.getLoginObject(_this.provider).login();
	        },
	        onSelectItem: function onSelectItem(item) {
	          _this.setProfile(item);
	        },
	        onRemoveItem: function onRemoveItem(item) {
	          _this.logout(item.CLIENT_ID);
	        }
	      }
	    });
	    return this;
	  }

	  babelHelpers.createClass(SeoAccount, [{
	    key: "listenSeoAuth",
	    value: function listenSeoAuth() {
	      BX.addCustomEvent(window, 'seo-client-auth-result', BX.proxy(this.onSeoAuth, this));
	    }
	  }, {
	    key: "onSeoAuth",
	    value: function onSeoAuth(eventData) {
	      eventData.reload = false;
	      this.getProvider(eventData.clientId);
	    }
	  }, {
	    key: "logout",
	    value: function logout(clientId) {
	      var _this2 = this;

	      var analyticsLabel = !(this.provider.TYPE === "facebook" || this.provider.TYPE === "instagram") ? {} : {
	        connect: "FBE",
	        action: "disconnect",
	        type: "disconnect"
	      };

	      this._helper.showBlock('loading');

	      this._helper.request('logout', {
	        logoutClientId: clientId
	      }, function (provider) {
	        _this2.provider = provider;

	        if (_this2.clientSelector) {
	          _this2.clientSelector.setSelected(_this2.provider.PROFILE);

	          _this2.clientSelector.setItems(_this2.provider.CLIENTS);
	        }

	        _this2._helper.setProvider(provider);

	        _this2._helper.showBlockByAuth();
	      }, analyticsLabel);
	    }
	  }, {
	    key: "getProvider",
	    value: function getProvider(clientId) {
	      var _this3 = this;

	      this.showBlock('loading');
	      this.request('getProvider', {}, function (provider) {
	        _this3.provider = provider;

	        if (_this3.clientSelector) {
	          if (!_this3.provider.PROFILE || clientId && clientId !== _this3.provider.PROFILE.CLIENT_ID) {
	            // set PROFILE equal to clientId or first record from CLIENTS:
	            for (var i = 0; i < _this3.provider.CLIENTS.length; i++) {
	              var client = _this3.provider.CLIENTS[i];

	              if (!clientId || clientId.toString() === client.CLIENT_ID.toString()) {
	                _this3.setProfile(client);

	                break;
	              }
	            }
	          }

	          _this3.clientSelector.setSelected(_this3.provider.PROFILE);

	          _this3.clientSelector.setItems(_this3.provider.CLIENTS);
	        }

	        _this3.showBlockByAuth();
	      });
	    }
	  }, {
	    key: "loadAccounts",
	    value: function loadAccounts(type) {
	      var _this4 = this;

	      // this.loader.forAccount(true);
	      if (this.clientSelector) {
	        this.clientSelector.disable();
	      }

	      this._helper.request('getAccounts', {}, function (data) {
	        if (_this4.clientSelector) {
	          _this4.clientSelector.enable();
	        }

	        _this4.uiNodes.accountNotice.ad.style.display = 'none';

	        if (!data.length) {
	          _this4.uiNodes.accountNotice.ad.style.display = 'block';
	          return;
	        }

	        var dropDownData = data.map(function (accountData) {
	          return {
	            caption: accountData.name,
	            value: accountData.id,
	            selected: accountData.id === _this4.accountId,
	            currency: accountData.currency
	          };
	        }, _this4);

	        _this4._helper.fillDropDownControl(_this4.accountNode, dropDownData);

	        if (dropDownData.length > 0) {
	          setTimeout(function () {
	            BX.fireEvent(_this4.accountNode, 'change');
	          }, 150);
	        }

	        _this4.accountNode.disabled = false;
	      });
	    }
	  }, {
	    key: "loadInstagramAccounts",
	    value: function loadInstagramAccounts(type) {
	      var _this5 = this;

	      if (this.clientSelector) {
	        this.clientSelector.disable();
	      }

	      this._helper.request('getInstagramAccounts', {}, function (data) {
	        if (_this5.clientSelector) {
	          _this5.clientSelector.enable();
	        }

	        _this5.uiNodes.accountNotice.instagram.style.display = 'none';

	        if (!data.length) {
	          _this5.uiNodes.accountNotice.instagram.style.display = 'block';
	          return;
	        }

	        var dropDownData = data.map(function (accountData) {
	          return {
	            caption: accountData.name,
	            value: accountData.id,
	            pageId: accountData.page_id,
	            actorId: accountData.actor_id
	          };
	        }, _this5);

	        _this5._helper.fillDropDownControl(_this5.instagramAccountNode, dropDownData);

	        if (dropDownData.length > 0) {
	          setTimeout(function () {
	            BX.fireEvent(_this5.instagramAccountNode, 'change');
	          }, 150);
	        }

	        _this5.instagramAccountNode.disabled = false;
	      });
	    }
	  }, {
	    key: "loadSettings",
	    value: function loadSettings() {
	      this.instagramAccountNode.disabled = true;
	      this.accountNode.disabled = true;
	      var type = this.provider.TYPE;
	      var isSupportAccount = this.provider.IS_SUPPORT_ACCOUNT;

	      if (!this.provider.PROFILE) {
	        return;
	      }

	      if (!this.loaded.includes(type)) {
	        this.loaded.push(type);
	      }

	      if (this.accountNode && isSupportAccount) {
	        this.loadAccounts();
	        this.loadInstagramAccounts();
	      }
	    }
	  }, {
	    key: "setProfile",
	    value: function setProfile(item) {
	      this.clientId = item && item.CLIENT_ID ? item.CLIENT_ID : null;
	      this.provider.PROFILE = item;
	      this.accountId = null;
	      this.pageId = null;

	      if (this.clientSelector.selected) {
	        this._helper.showBlockMain();
	      }

	      this.clientSelector.setSelected(item);
	    }
	  }]);
	  return SeoAccount;
	}();

	var _templateObject;
	var Helper = /*#__PURE__*/function () {
	  function Helper(seoAccount) {
	    babelHelpers.classCallCheck(this, Helper);
	    this.provider = seoAccount.provider;
	    this.clientId = seoAccount.clientId;
	    this.clientSelector = seoAccount.clientSelector;
	    this.clientNode = seoAccount.clientNode;
	    this.avatarNode = seoAccount.avatarNode;
	    this.linkNode = seoAccount.linkNode;
	    this.seoAccount = seoAccount;
	    this.signedParameters = seoAccount.signedParameters;
	    this.containerNode = BX('crm-ads-new-campaign');
	    this.mess = {
	      errorAction: main_core.Loc.getMessage('UI_HELPER_ERROR_MSG'),
	      dlgBtnClose: main_core.Loc.getMessage('UI_HELPER_BUTTON_CLOSE')
	    };
	    return this;
	  }

	  babelHelpers.createClass(Helper, [{
	    key: "setProvider",
	    value: function setProvider(value) {
	      this.provider = value;
	    }
	  }, {
	    key: "request",
	    value: function request(action, requestData, callback, analytics) {
	      var _this = this;

	      requestData.action = action;
	      requestData.type = this.seoAccount.provider.TYPE;
	      requestData.clientId = this.seoAccount.clientId;
	      this.sendActionRequest(action, requestData, function (response) {
	        return _this.onResponse(response, callback);
	      }, null, analytics || {});
	    }
	  }, {
	    key: "onResponse",
	    value: function onResponse(response, callback) {
	      if (!response.error) {
	        callback.apply(this, [response.data]);
	      }
	    }
	  }, {
	    key: "sendActionRequest",
	    value: function sendActionRequest(action, data, callbackSuccess, callbackFailure, analytics) {
	      var _this2 = this;

	      callbackSuccess = callbackSuccess || null;
	      callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);
	      data = data || {};
	      analytics = analytics || {};
	      BX.ajax.runComponentAction(this.seoAccount.componentName, action, {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: data,
	        analyticsLabel: analytics
	      }).then(function (response) {
	        var data = response.data || {};

	        if (data.error) {
	          callbackFailure.apply(_this2, [data]);
	        } else if (callbackSuccess) {
	          callbackSuccess.apply(_this2, [data]);
	        }
	      }, function () {
	        var data = {
	          'error': true,
	          'text': ''
	        };
	        callbackFailure.apply(_this2, [data]);
	      });
	    }
	  }, {
	    key: "showErrorPopup",
	    value: function showErrorPopup(data) {
	      console.log(data);
	      var text = data.text || this.mess.errorAction;
	      var popup = main_popup.PopupManager.create({
	        id: 'crm_ads_rtg_error',
	        autoHide: true,
	        lightShadow: true,
	        closeByEsc: true,
	        overlay: {
	          backgroundColor: 'black',
	          opacity: 500
	        },
	        events: {
	          'onPopupClose': this.onErrorPopupClose.bind(this)
	        },
	        buttons: [new ui_buttons.Button({
	          text: 'close' || this.mess.dlgBtnClose,
	          events: {
	            click: function click() {
	              popup.close();
	            }
	          }
	        })]
	      });
	      popup.setContent("<span class=\"crm-ads-rtg-warning-popup-alert\">".concat(text, "</span>"));
	      popup.show();
	    }
	  }, {
	    key: "onErrorPopupClose",
	    value: function onErrorPopupClose() {
	      if (this.clientSelector) {
	        this.clientSelector.enable();
	      }
	    }
	  }, {
	    key: "showBlock",
	    value: function showBlock(blockCodes) {
	      blockCodes = main_core.Type.isArray(blockCodes) ? blockCodes : [blockCodes];
	      var attributeBlock = 'data-bx-ads-block';
	      var blockNodes = babelHelpers.toConsumableArray(this.containerNode.querySelectorAll('[' + attributeBlock + ']'));
	      blockNodes.forEach(function (blockNode) {
	        var code = blockNode.getAttribute(attributeBlock);
	        var isShow = blockCodes.includes(code);
	        blockNode.style.display = isShow ? blockNode.dataset.flex ? 'flex' : 'block' : 'none';
	      }, this);
	    }
	  }, {
	    key: "showBlockRefresh",
	    value: function showBlockRefresh() {
	      this.showBlock(['auth', 'refresh']);
	    }
	  }, {
	    key: "showBlockLogin",
	    value: function showBlockLogin() {
	      this.showBlock('login');
	      var btn = BX('seo-ads-login-btn');

	      if (btn && this.provider && this.provider.AUTH_URL) {
	        btn.setAttribute('onclick', 'BX.util.popup(\'' + this.provider.AUTH_URL + '\', 800, 600);');
	      }

	      if (this.clientNode) {
	        this.clientNode.value = "";
	      }
	    }
	  }, {
	    key: "showBlockMain",
	    value: function showBlockMain() {
	      if (this.avatarNode) {
	        this.avatarNode.style['background-image'] = 'url(' + this.provider.PROFILE.PICTURE + ')';
	      }

	      if (this.nameNode) {
	        this.nameNode.innerText = this.provider.PROFILE.NAME;
	      }

	      if (this.linkNode) {
	        if (this.provider.PROFILE.LINK) {
	          this.linkNode.setAttribute('href', this.provider.PROFILE.LINK);
	        } else {
	          this.linkNode.removeAttribute('href');
	        }
	      }

	      if (this.clientNode) {
	        this.clientNode.value = this.provider.PROFILE && this.provider.PROFILE.CLIENT_ID ? this.provider.PROFILE.CLIENT_ID : "";
	      }

	      this.showBlock(['auth', 'main']);
	      this.seoAccount.loadSettings();
	    }
	  }, {
	    key: "showBlockByAuth",
	    value: function showBlockByAuth() {
	      if (this.provider.HAS_AUTH) {
	        this.showBlockMain();
	      } else {
	        this.showBlockLogin();
	      }
	    }
	  }, {
	    key: "fillDropDownControl",
	    value: function fillDropDownControl(node, items) {
	      items = items || [];
	      node.innerHTML = '';
	      items.forEach(function (item) {
	        if (!item || !item.caption) {
	          return;
	        }

	        var option = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<option value='", "' selected='", "'>", "</option>"])), item.value, !!item.selected, item.caption);

	        if (item.currency) {
	          option.dataset.currency = item.currency;
	        }

	        if (item.pageId) {
	          option.dataset.pageId = item.pageId;
	        }

	        if (item.actorId) {
	          option.dataset.actorId = item.actorId;
	        }

	        node.appendChild(option);
	      });
	    }
	  }], [{
	    key: "getCreated",
	    value: function getCreated() {
	      if (this._instance === undefined) {
	        return null;
	      }

	      return this._instance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance(seoAccount, signedParameters) {
	      if (this._instance === undefined) {
	        this._instance = new Helper(seoAccount, signedParameters);
	      }

	      return this._instance;
	    }
	  }]);
	  return Helper;
	}();

	var _templateObject$1, _templateObject2, _templateObject3;
	var SeoAdBuilder = /*#__PURE__*/function () {
	  function SeoAdBuilder(options) {
	    var _this2 = this;

	    babelHelpers.classCallCheck(this, SeoAdBuilder);
	    babelHelpers.defineProperty(this, "_DEFAULT_CURRENCY", 'RUB');
	    babelHelpers.defineProperty(this, "_STAGES", {
	      accountSelected: 1,
	      postSelected: 2,
	      pageSelected: 3,
	      audienceSelected: 4,
	      budgetSelected: 5,
	      toModeration: 6
	    });

	    if (this._instance) {
	      return this._instance;
	    }

	    this.optionSelectedClass = 'crm-ads-new-campaign-item-option--selected';
	    this.containerId = options.containerId;
	    this.provider = options.provider;
	    this.context = options.context;
	    this.onRequest = options.onRequest;
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.postListUrl = options.postListUrl;
	    this.audienceUrl = options.audienceUrl;
	    this.crmAudienceUrl = options.crmAudienceUrl;
	    this.pageConfigurationUrl = options.pageConfigurationUrl;
	    this.mess = options.mess;
	    this.type = options.type;
	    this.iBlockId = options.iBlockId;
	    this.basePriceId = options.basePriceId;
	    this.storeExists = options.storeExists;
	    this.isCloud = options.isCloud || false;
	    this.clientId = options.clientId;
	    this.accountId = options.accountId;
	    this.baseCurrency = options.baseCurrency;
	    this.arrows = document.querySelectorAll(".crm-ads-new-campaign-item-arrow");
	    this.price = [];
	    this.price[this._DEFAULT_CURRENCY] = [];
	    this.price[this._DEFAULT_CURRENCY]['recommended'] = {
	      duration: 3,
	      value: 100
	    };
	    this.price[this._DEFAULT_CURRENCY]['verified'] = {
	      duration: 3,
	      value: 200
	    };
	    this.price[this._DEFAULT_CURRENCY]['boost'] = {
	      duration: 3,
	      value: 300
	    };
	    this.price[this._DEFAULT_CURRENCY]['confident'] = {
	      duration: 5,
	      value: 500
	    };
	    this.price['USD'] = [];
	    this.price['USD']['recommended'] = {
	      duration: 3,
	      value: 50
	    };
	    this.price['USD']['verified'] = {
	      duration: 3,
	      value: 100
	    };
	    this.price['USD']['boost'] = {
	      duration: 3,
	      value: 150
	    };
	    this.price['USD']['confident'] = {
	      duration: 5,
	      value: 200
	    };
	    this.price['EUR'] = [];
	    this.price['EUR']['recommended'] = {
	      duration: 3,
	      value: 50
	    };
	    this.price['EUR']['verified'] = {
	      duration: 3,
	      value: 100
	    };
	    this.price['EUR']['boost'] = {
	      duration: 3,
	      value: 150
	    };
	    this.price['EUR']['confident'] = {
	      duration: 5,
	      value: 200
	    };
	    this.completedStages = {};
	    this.selectedRegions = {};
	    this.loader = {
	      init: function init(caller) {
	        _this2.caller = caller;
	      },
	      change: function change(loaderNode, inputNode, isShow) {
	        loaderNode.style.display = isShow ? '' : 'none';

	        if (inputNode) {
	          inputNode.disabled = !inputNode.options.length === 0 || isShow ? false : true;
	        }
	      }
	    };
	    this.init();
	  }

	  babelHelpers.createClass(SeoAdBuilder, [{
	    key: "init",
	    value: function init() {
	      this._instance = this;
	      this.initiateUINodes();
	      this.initiateAutoAudienceMode();

	      for (var i = this._STAGES.accountSelected; i <= this._STAGES.toModeration; i++) {
	        this.deActivateStage(i);
	      }

	      this.initiateAccounts();
	      this.activateStage(this._STAGES.audienceSelected);
	      this.initiateSwitcher('product');
	      this.initiateSwitcher('audience');
	      this.initiateSwitcher('budget');
	      this.bindEvents();
	      this.buildSelector();
	      this.storeBlockShow(true);
	    }
	  }, {
	    key: "reInitAdCreator",
	    value: function reInitAdCreator() {
	      this.adCreatorData = {};
	      this.adCreatorData.audienceConfig = {};
	      this.adCreatorData.crmAudienceConfig = {};
	    }
	  }, {
	    key: "initiateAccounts",
	    value: function initiateAccounts() {
	      this.seoAccount = new SeoAccount({
	        clientNode: this.uiNodes.clientInput,
	        provider: this.provider,
	        avatarNode: this.uiNodes.avatar,
	        linkNode: this.uiNodes.link,
	        accountNode: this.uiNodes.account,
	        instagramAccountNode: this.uiNodes.instagramAccount,
	        clientBlock: this.uiNodes.clientBlock,
	        signedParameters: this.signedParameters,
	        componentName: this.componentName,
	        uiNodes: this.uiNodes
	      });
	      this.profileConfigured = false;

	      if (!this.clientId && !this.provider.PROFILE) {
	        // use first client by default
	        for (var i = 0; i < this.provider.CLIENTS.length; i++) {
	          this.seoAccount.setProfile(this.provider.CLIENTS[i]);
	          this.profileConfigured = true;
	          break;
	        }
	      }

	      this.loader.init(this);

	      if (this.provider.PROFILE) {
	        this.activateStage(this._STAGES.accountSelected);
	      }

	      if (!this.profileConfigured) {
	        this.seoAccount.setProfile(this.provider.PROFILE);
	      }

	      this.seoAccount._helper.showBlockByAuth();
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this3 = this;

	      main_core.Event.bind(this.uiNodes.addPost, 'click', this.openPostSlider.bind(this));
	      this.uiNodes.createLinks.forEach(function (createLink) {
	        main_core.Event.bind(createLink, 'click', BX.proxy(function () {
	          if (!this.hasPostLis) {
	            this.showBlockRefresh();
	          }
	        }, this));
	      }, this);
	      main_core.Event.bind(this.uiNodes.refreshButton, 'click', BX.proxy(function () {
	        this.seoAccount.getProvider();
	      }, this));

	      if (this.uiNodes.autoRemover.checker) {
	        main_core.Event.bind(this.uiNodes.autoRemover.checker, 'click', function () {
	          var autoRemover = _this3.uiNodes.autoRemover;
	          autoRemover.select.disabled = !autoRemover.checker.checked;
	        });
	      }

	      main_core.Event.bind(this.uiNodes.logout, 'click', BX.proxy(function () {
	        this.seoAccount.logout(this.clientId);
	      }, this));
	      main_core.Event.bind(this.uiNodes.addClientBtn, 'click', BX.proxy(function () {
	        BX.util.popup(_this.provider.AUTH_URL, 800, 600);
	      }, this));
	      this.arrows.forEach(function (arrow) {
	        arrow.addEventListener('click', _this3.switchCollapsed);
	      });
	      main_core.Event.bind(this.uiNodes.account, 'change', this.checkCurrency.bind(this));
	      document.querySelectorAll('.seo-ads-budget-item-block').forEach(function (div) {
	        main_core.Event.bind(div, 'click', _this3.calculateTotal.bind(_this3));
	      });
	      document.querySelectorAll('.seo-ads-audience-item-block').forEach(function (div) {
	        main_core.Event.bind(div, 'click', _this3.changeAudienceMode.bind(_this3));
	      });
	      document.querySelectorAll('.seo-ads-product-item-block').forEach(function (div) {
	        main_core.Event.bind(div, 'click', _this3.changeProductSelectionMode.bind(_this3));
	      });
	      main_core.Event.bind(this.uiNodes.audienceExpert, 'click', this.showAudienceExpertModeForm.bind(this));
	      main_core.Event.bind(this.uiNodes.productExpert, 'click', this.openTargetPageSlider.bind(this));
	      main_core.Event.bind(this.uiNodes.addProductBtn, 'click', this.toCreateStoreSlider.bind(this));
	      main_core.Event.bind(this.uiNodes.addCurrencyBtn, 'click', this.addCurrency.bind(this));
	      main_core.Event.bind(this.uiNodes.toModerationBtn, 'click', this.sendToModeration.bind(this));
	    }
	  }, {
	    key: "initiateUINodes",
	    value: function initiateUINodes() {
	      this.containerNode = BX('crm-ads-new-campaign');
	      BX.UI.Hint.init(this.containerNode);
	      this.uiNodes = {
	        'avatar': this.containerNode.querySelector('[data-bx-ads-auth-avatar]'),
	        'name': this.containerNode.querySelector('[data-bx-ads-auth-name]'),
	        'link': this.containerNode.querySelector('[data-bx-ads-auth-link]'),
	        'logout': this.containerNode.querySelector('[data-bx-ads-auth-logout]'),
	        'clientBlock': this.containerNode.querySelector('[data-bx-ads-client]'),
	        'clientInput': this.containerNode.querySelector('[data-bx-ads-client-input]'),
	        'account': this.containerNode.querySelector('[data-bx-ads-account]'),
	        'accountLoader': this.containerNode.querySelector('[data-bx-ads-account-loader]'),
	        'instagramAccount': this.containerNode.querySelector('[data-bx-ads-instagram-account]'),
	        'instagramAccountLoader': this.containerNode.querySelector('[data-bx-ads-instagram-account-loader]'),
	        'errorNotFound': this.containerNode.querySelector('[data-bx-ads-post-not-found]'),
	        'addPost': this.containerNode.querySelector('.crm-ads-new-campaign-item-post-new'),
	        'addProductBtn': this.containerNode.querySelector('.seo-ads-add-product-btn'),
	        'addCurrencyBtn': this.containerNode.querySelector('.seo-ads-currency-apply-btn'),
	        'toModerationBtn': this.containerNode.querySelector('.seo-ads-to-moderation-btn'),
	        'refreshButton': this.containerNode.querySelector('[data-bx-ads-refresh-btn]'),
	        'currencyBlock': document.querySelector('.seo-ads-currency-block'),
	        'audienceSummary': document.querySelector('.seo-ads-audience-summary'),
	        'createLinks': BX.convert.nodeListToArray(this.containerNode.querySelectorAll('[data-bx-ads-post-create-link]')),
	        'accountNotice': {
	          'instagram': this.containerNode.querySelector('.seo-ads-no-ad-account-instagram'),
	          'ad': this.containerNode.querySelector('.seo-ads-no-ad-account')
	        },
	        'audienceExpert': BX('crm-ads-new-campaign-item-expert-audience'),
	        'productExpert': BX('crm-ads-new-campaign-item-expert-product'),
	        'budgetExpert': BX('crm-ads-new-campaign-item-expert-budget'),
	        'autoRemover': {
	          'node': this.containerNode.querySelector('[data-bx-ads-post-auto-remove]'),
	          'checker': this.containerNode.querySelector('[data-bx-ads-post-auto-remove-checker]'),
	          'select': this.containerNode.querySelector('[data-bx-ads-post-auto-remove-select]')
	        },
	        'form': {
	          'permalink': this.containerNode.querySelector('[data-bx-ads-permalink]'),
	          'mediaId': this.containerNode.querySelector('[data-bx-ads-media-id]'),
	          'targetUrl': this.containerNode.querySelector('[data-bx-ads-target-url]'),
	          'duration': this.containerNode.querySelector('[data-bx-ads-duration]'),
	          'page': this.containerNode.querySelector('[data-bx-ads-page-id]'),
	          'body': this.containerNode.querySelector('[data-bx-ads-body]'),
	          'adsId': this.containerNode.querySelector('[data-bx-ads-id]'),
	          'pageId': this.containerNode.querySelector('[data-bx-ads-page-id]'),
	          'budget': this.containerNode.querySelector('[data-bx-ads-budget]'),
	          'ageFrom': this.containerNode.querySelector('[data-bx-ads-age-from]'),
	          'ageTo': this.containerNode.querySelector('[data-bx-ads-age-to]'),
	          'genders': this.containerNode.querySelector('[data-bx-ads-genders]'),
	          'interests': this.containerNode.querySelector('[data-bx-ads-interests]'),
	          'imageUrl': this.containerNode.querySelector('[data-bx-ads-image-url]'),
	          'instagramAccountId': this.containerNode.querySelector('[data-bx-ads-actor-id]'),
	          'segmentInclude': this.containerNode.querySelector('[data-bx-ads-segment-include]'),
	          'segmentExclude': this.containerNode.querySelector('[data-bx-ads-segment-exclude]'),
	          'regions': this.containerNode.querySelector('[data-bx-ads-regions]')
	        },
	        'adsStoreBlock': this.containerNode.querySelectorAll('.seo-ads-store'),
	        'addClientBtn': this.containerNode.querySelector('[data-bx-ads-client-add-btn]'),
	        'addPostBtn': this.containerNode.querySelector('[data-bx-ads-post-add]')
	      };
	    }
	  }, {
	    key: "initiateSwitcher",
	    value: function initiateSwitcher(id) {
	      new BX.UI.Switcher({
	        node: BX("crm-ads-new-campaign-item-expert-".concat(id)),
	        size: "small"
	      });
	    }
	  }, {
	    key: "checkCurrency",
	    value: function checkCurrency() {
	      var account = this.uiNodes.account;
	      this.usedCurrency = account.options[account.selectedIndex].dataset.currency;
	      this.currencyExists(this.usedCurrency);
	    }
	  }, {
	    key: "calculateTotal",
	    value: function calculateTotal(event) {
	      var _this4 = this;

	      if (this.checkInstagramAccount()) {
	        return;
	      }

	      var target = event.target.dataset.type ? event.target : event.target.parentNode;
	      var type = target.dataset.type;
	      var price = this.price[this.usedCurrency][type];
	      var total = price.duration * price.value;
	      document.querySelectorAll('.seo-ads-budget-total-value').forEach(function (element) {
	        element.textContent = total;
	      });
	      document.querySelector('.seo-ads-budget-total-currency').textContent = this.usedCurrency;
	      document.querySelector('.seo-ads-budget-total-duration').textContent = price.duration;
	      document.querySelector('.seo-ads-total-budget').textContent = total;
	      document.querySelector('.seo-ads-total-currency').textContent = this.usedCurrency;
	      document.querySelector('.seo-ads-total-duration').textContent = price.duration;
	      document.querySelector('.crm-ads-new-campaign-item-cost').style.display = 'block';
	      document.querySelectorAll('.seo-ads-budget-item-block').forEach(function (div) {
	        div.classList.remove(_this4.optionSelectedClass);
	      });
	      target.classList.add(this.optionSelectedClass);
	      this.uiNodes.form.budget.value = total;
	      this.uiNodes.form.duration.value = price.duration;
	      this.prepareCurrencyBlocks();
	      this.activateStage(this._STAGES.budgetSelected);
	    }
	  }, {
	    key: "checkInstagramAccount",
	    value: function checkInstagramAccount() {
	      if (!this.uiNodes.instagramAccount.value) {
	        this.scrollToStage(this._STAGES.accountSelected);
	        return true;
	      }

	      return false;
	    }
	  }, {
	    key: "changeAudienceMode",
	    value: function changeAudienceMode(event) {
	      var _this5 = this;

	      if (this.checkInstagramAccount()) {
	        return;
	      }

	      var target = event.target.dataset.type ? event.target : event.target.parentNode;
	      var type = target.dataset.type;
	      document.querySelectorAll('.seo-ads-audience-item-block').forEach(function (div) {
	        div.classList.remove(_this5.optionSelectedClass);
	      });
	      target.classList.add(this.optionSelectedClass);

	      switch (type) {
	        case 'auto':
	          this.initiateAutoAudienceMode();
	          break;

	        case 'crm':
	          this.showCrmAudienceExpertModeForm();
	          break;

	        case 'expert':
	          this.showAudienceExpertModeForm();
	          break;
	      }
	    }
	  }, {
	    key: "changeProductSelectionMode",
	    value: function changeProductSelectionMode(event) {
	      var _this6 = this;

	      if (this.checkInstagramAccount()) {
	        return;
	      }

	      var target = event.target.dataset.type ? event.target : event.target.parentNode;
	      var type = target.dataset.type;
	      document.querySelectorAll('.seo-ads-product-item-block').forEach(function (div) {
	        div.classList.remove(_this6.optionSelectedClass);
	      });
	      target.classList.add(this.optionSelectedClass);

	      switch (type) {
	        case 'auto':
	          this.storeBlockShow(true);
	          break;

	        case 'expert':
	          this.openTargetPageSlider();
	          break;
	      }
	    }
	  }, {
	    key: "storeBlockShow",
	    value: function storeBlockShow(isShown) {
	      var _this7 = this;

	      this.uiNodes.adsStoreBlock.forEach(function (element) {
	        if (_this7.storeExists && element.dataset.type === 'store-not-created') {
	          return;
	        }

	        if (!_this7.storeExists && element.dataset.type !== 'store-not-created') {
	          return;
	        }

	        element.style.display = isShown ? 'block' : 'none';
	      });
	    }
	  }, {
	    key: "prepareCurrencyBlocks",
	    value: function prepareCurrencyBlocks() {
	      var _this8 = this;

	      document.querySelectorAll('.seo-ads-current-currency').forEach(function (element) {
	        element.textContent = _this8.usedCurrency;
	      });
	    }
	  }, {
	    key: "prepareCurrencyBlock",
	    value: function prepareCurrencyBlock() {
	      var currency = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this._DEFAULT_CURRENCY;

	      if (!this.price[currency]) {
	        for (var key in this.price[this._DEFAULT_CURRENCY]) {
	          this.convertToCurrency(key, this._DEFAULT_CURRENCY !== this.baseCurrency ? this.baseCurrency : currency, this.price[this._DEFAULT_CURRENCY][key]);
	        }

	        return;
	      }

	      for (var _key in this.price[currency]) {
	        document.querySelector(".seo-ads-budget-".concat(_key, "-duration")).textContent = this.price[currency][_key].duration;
	        document.querySelector(".seo-ads-budget-".concat(_key, "-value")).textContent = this.price[currency][_key].value;
	        document.querySelector(".seo-ads-budget-".concat(_key, "-currency")).textContent = currency;
	      }
	    }
	  }, {
	    key: "convertToCurrency",
	    value: function convertToCurrency(key, targetCurrency, price) {
	      var _this9 = this;

	      this.seoAccount._helper.request('convertCurrency', {
	        sourceCurrency: this.baseCurrency,
	        targetCurrency: targetCurrency,
	        amount: price.value
	      }, function (response) {
	        var amount = response.amount;

	        if (!_this9.price[targetCurrency]) {
	          _this9.price[targetCurrency] = [];
	        }

	        if (!_this9.price[targetCurrency][key]) {
	          _this9.price[targetCurrency][key] = {
	            duration: price.duration,
	            value: amount
	          };
	        }

	        if (Object.keys(_this9.price[targetCurrency]).length === 4) {
	          _this9.prepareCurrencyBlock(targetCurrency);
	        }
	      });
	    }
	  }, {
	    key: "currencyExists",
	    value: function currencyExists(currency) {
	      var _this10 = this;

	      this.seoAccount._helper.request('checkCurrencyExists', {
	        currency: currency
	      }, function (response) {
	        var exists = response.exists;

	        if (exists === false) {
	          _this10.prepareCurrencyBlocks();

	          _this10.uiNodes.currencyBlock.style.display = 'block';
	        }

	        _this10.prepareCurrencyBlock(_this10.usedCurrency);
	      });
	    }
	  }, {
	    key: "addCurrency",
	    value: function addCurrency() {
	      var _this11 = this;

	      var count = document.querySelector('.seo-ads-currency-count');
	      var course = document.querySelector('.seo-ads-currency-course');

	      if (!count.value || !course) {
	        return;
	      }

	      this.seoAccount._helper.request('addCurrency', {
	        newCurrency: this.usedCurrency,
	        course: course.value,
	        amountCnt: count.value
	      }, function (response) {
	        var success = response.success;

	        if (success === false) {
	          return;
	        }

	        _this11.uiNodes.currencyBlock.style.display = 'none';
	        delete _this11.price[_this11.usedCurrency];

	        _this11.prepareCurrencyBlock(_this11.usedCurrency);
	      });
	    }
	  }, {
	    key: "switchCollapsed",
	    value: function switchCollapsed(event) {
	      var block = event.target.closest('.crm-ads-new-campaign-item');
	      var content = block.querySelector('.crm-ads-new-campaign-item-content');

	      if (block.classList.contains('crm-ads-new-campaign-item--hide')) {
	        block.classList.remove('crm-ads-new-campaign-item--hide');
	        content.style.height = content.scrollHeight + 'px';
	      } else {
	        block.classList.add('crm-ads-new-campaign-item--hide');
	        content.style.height = content.scrollHeight + 'px';
	        setTimeout(function () {
	          return content.style.height = '0';
	        });
	      }
	    }
	  }, {
	    key: "clipTitle",
	    value: function clipTitle(title) {
	      if (!title) {
	        return;
	      }

	      var text = title.textContent;
	      var nodeHeight = 20;
	      BX.cleanNode(title);
	      var titleInner = BX.create("span", {
	        text: text
	      });
	      title.appendChild(titleInner);
	      var a = 0;

	      while (titleInner.offsetHeight > nodeHeight && text.length > a) {
	        a = a + 1;
	        titleInner.innerText = text.slice(0, -a) + '...';
	      }
	    }
	  }, {
	    key: "onPostSelected",
	    value: function onPostSelected(event) {
	      if (event.eventId === "seo-ads-post-selected" && event.data) {
	        if (!event.data.media_url) {
	          this.deActivateStage(this._STAGES.postSelected);
	          return;
	        }

	        var postItem = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral([" \n\t\t\t<div class=\"crm-ads-new-campaign-item-post\">\n\t\t\t   <div class=\"crm-ads-new-campaign-item-post-img\" \n\t\t\t\t\tstyle=\"background-image: url(", ")\">\n\t\t\t   </div>\n\t\t\t   <span class=\"crm-ads-new-campaign-item-post-text\">", "</span>\n\t\t\t   <span class=\"crm-ads-new-campaign-item-post-delete\"></span>\n\t\t\t</div>\n\t\t\t"])), event.data.media_url, event.data.caption || '');
	        var postListNode = document.querySelector('.crm-ads-new-campaign-item-posts');
	        var addNewNode = document.querySelector('.crm-ads-new-campaign-item-post-new');
	        var previewNode = document.querySelector('.crm-ads-new-campaign-item-total-preview-img-value');

	        if (addNewNode !== postListNode.firstChild) {
	          postListNode.removeChild(postListNode.firstChild);
	        }

	        postListNode.insertBefore(postItem, postListNode.firstChild);
	        main_core.Event.bind(postItem.querySelector('.crm-ads-new-campaign-item-post-delete'), 'click', function () {
	          postItem.parentNode.removeChild(postItem);
	        });
	        previewNode.style.backgroundImage = 'url(' + event.data.media_url + ')';
	        this.postData = event.data;
	        var title = document.querySelector('.crm-ads-new-campaign-item-post-text');
	        this.clipTitle(title);
	        this.activateStage(this._STAGES.postSelected);
	      }
	    }
	  }, {
	    key: "openPostSlider",
	    value: function openPostSlider() {
	      if (this.uiNodes.instagramAccount.value) {
	        this.openSlider(this.postListUrl, {
	          sessid: BX.bitrix_sessid(),
	          componentParams: {
	            ACCOUNT_ID: this.uiNodes.instagramAccount.value,
	            CLIENT_ID: this.uiNodes.clientInput.value,
	            TYPE: this.provider.TYPE
	          }
	        }, this.onPostSelected);
	      }
	    }
	  }, {
	    key: "onTargetPageSelected",
	    value: function onTargetPageSelected(event) {
	      if (event.eventId === "seo-ads-target-post-selected" && event.data) {
	        if (!event.data.targetUrl) {
	          this.deActivateStage(this._STAGES.pageSelected);
	          return;
	        }

	        document.querySelector('.seo-ads-target-url').textContent = event.data.targetUrl;
	        this.uiNodes.form.targetUrl.value = event.data.targetUrl;
	        this.activateStage(this._STAGES.pageSelected);
	      }
	    }
	  }, {
	    key: "onFBAudienceConfigured",
	    value: function onFBAudienceConfigured(event) {
	      if (event.eventId === "seo-fb-audience-configured" && event.data) {
	        this.reInitAdCreator();

	        if (!event.data) {
	          this.deActivateStage(this._STAGES.audienceSelected);
	          return;
	        }

	        this.adCreatorData.audienceConfig = event.data;
	        this.activateStage(this._STAGES.audienceSelected);
	        this.uiNodes.audienceSummary.innerHTML = this.buildAudienceSummary();
	      }
	    }
	  }, {
	    key: "onCrmAudienceConfigured",
	    value: function onCrmAudienceConfigured(event) {
	      if (event.eventId === "seo-crm-audience-configured" && event.data) {
	        this.reInitAdCreator();

	        if (!event.data) {
	          this.deActivateStage(this._STAGES.audienceSelected);
	          return;
	        }

	        this.adCreatorData.crmAudienceConfig = event.data;
	        this.activateStage(this._STAGES.audienceSelected);
	        this.uiNodes.audienceSummary.innerHTML = this.buildAudienceSummary();
	      }
	    }
	  }, {
	    key: "openTargetPageSlider",
	    value: function openTargetPageSlider() {
	      if (this.uiNodes.instagramAccount.value) {
	        this.storeBlockShow(false);
	        this.openSlider(this.pageConfigurationUrl, {
	          sessid: BX.bitrix_sessid(),
	          targetUrl: this.uiNodes.form.targetUrl.value || '',
	          cacheable: false
	        }, this.onTargetPageSelected);
	      }
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(url, params, callback) {
	      var sliderOptions = {
	        width: 1150,
	        cacheable: params.cacheable || true,
	        allowChangeHistory: false,
	        requestMethod: 'post',
	        requestParams: params
	      };
	      var eventName = BX.SidePanel.Slider.getEventFullName("onMessage");
	      BX.removeAllCustomEvents(window, eventName, callback.bind(this));
	      BX.addCustomEvent(window, eventName, callback.bind(this));
	      BX.SidePanel.Instance.open(url, sliderOptions);
	    }
	  }, {
	    key: "showAudienceExpertModeForm",
	    value: function showAudienceExpertModeForm() {
	      if (this.uiNodes.instagramAccount.value) {
	        this.openSlider(this.audienceUrl, {
	          sessid: BX.bitrix_sessid(),
	          componentParams: {
	            ACCOUNT_ID: this.uiNodes.instagramAccount.value,
	            CLIENT_ID: this.uiNodes.clientInput.value,
	            TYPE: this.provider.TYPE
	          }
	        }, this.onFBAudienceConfigured);
	      }
	    }
	  }, {
	    key: "showCrmAudienceExpertModeForm",
	    value: function showCrmAudienceExpertModeForm() {
	      if (this.uiNodes.instagramAccount.value) {
	        this.openSlider(this.crmAudienceUrl, {
	          sessid: BX.bitrix_sessid(),
	          componentParams: {
	            TYPE: this.provider.TYPE
	          }
	        }, this.onCrmAudienceConfigured);
	      }
	    }
	  }, {
	    key: "initiateAutoAudienceMode",
	    value: function initiateAutoAudienceMode() {
	      this.reInitAdCreator();
	      this.adCreatorData.crmAudienceConfig.genders = [1, 2];
	      this.adCreatorData.crmAudienceConfig.ageFrom = 25;
	      this.adCreatorData.crmAudienceConfig.ageTo = 45;
	      this.activateStage(this._STAGES.audienceSelected);
	      this.uiNodes.audienceSummary.innerHTML = main_core.Loc.getMessage('SEO_AD_BUILDER_AUDIENCE_MEN_WOMAN_25_45');
	    }
	  }, {
	    key: "buildAudienceSummary",
	    value: function buildAudienceSummary() {
	      var summary = '';

	      if (this.adCreatorData.audienceConfig.genderTitles) {
	        summary += "".concat(main_core.Loc.getMessage('SEO_AD_BUILDER_GENDER'), ": ").concat(this.adCreatorData.audienceConfig.genderTitles.join(', '), " ");
	      }

	      if (this.adCreatorData.audienceConfig.ageFrom) {
	        summary += "".concat(this.adCreatorData.audienceConfig.ageFrom, " - ").concat(this.adCreatorData.audienceConfig.ageTo, "\n\t\t\t ").concat(main_core.Loc.getMessage('SEO_AD_BUILDER_YEARS_OLD'), " <br/>");
	      }

	      if (this.adCreatorData.audienceConfig.interests) {
	        var interests = [];
	        this.adCreatorData.audienceConfig.interests.forEach(function (interest) {
	          interests.push(interest.name);
	        });
	        summary += "".concat(main_core.Loc.getMessage('SEO_AD_BUILDER_INTERESTS'), ": ").concat(interests.join(', '), "<br/>");
	      }

	      if (this.adCreatorData.crmAudienceConfig.segmentInclude) {
	        summary += "".concat(main_core.Loc.getMessage('SEO_AD_BUILDER_CRM_AUDIENCE'), "<br/>");
	      }

	      if (Object.keys(this.selectedRegions).length) {
	        var regions = [];

	        for (var code in this.selectedRegions) {
	          regions.push(this.selectedRegions[code].title);
	        }

	        summary += "".concat(main_core.Loc.getMessage('SEO_AD_BUILDER_REGION'), ": ").concat(regions.join(', '), "<br/>");
	      }

	      return summary;
	    }
	  }, {
	    key: "sendToModeration",
	    value: function sendToModeration(event) {
	      this.uiNodes.toModerationBtn.classList.add('ui-btn-wait');
	      var formNode = this.uiNodes.form;

	      if (Object.keys(this.completedStages).length < 6) {
	        for (var i = this._STAGES.accountSelected; i <= this._STAGES.toModeration; i++) {
	          if (!this.completedStages[i]) {
	            this.scrollToStage(i);
	            this.uiNodes.toModerationBtn.classList.remove('ui-btn-wait');
	            return;
	          }
	        }

	        this.uiNodes.toModerationBtn.classList.remove('ui-btn-wait');
	        return;
	      }

	      var instagramAccount = this.uiNodes.instagramAccount.options[this.uiNodes.instagramAccount.selectedIndex].dataset;
	      var params = {
	        client_id: this.uiNodes.clientInput.value,
	        budget: formNode.budget.value,
	        duration: formNode.duration.value,
	        targetUrl: formNode.targetUrl.value,
	        accountId: this.uiNodes.account.value,
	        instagramAccountId: instagramAccount.actorId,
	        pageId: instagramAccount.pageId,
	        body: this.postData.caption,
	        mediaId: this.postData.id,
	        permalink: this.postData.permalink,
	        imageUrl: this.postData.media_url,
	        countries: this.selectedRegions,
	        interests: this.adCreatorData.audienceConfig.interests || [],
	        ageFrom: this.adCreatorData.audienceConfig.ageFrom || '',
	        ageTo: this.adCreatorData.audienceConfig.ageTo || '',
	        genders: this.adCreatorData.audienceConfig.genders || ''
	      };
	      var form = document.getElementById('bx-sender-letter-edit').querySelector('form');
	      formNode.permalink.value = this.postData.permalink;
	      formNode.pageId.value = params.pageId;
	      formNode.body.value = this.postData.caption;
	      formNode.mediaId.value = params.mediaId;
	      formNode.imageUrl.value = params.imageUrl;
	      formNode.instagramAccountId.value = params.instagramAccountId;
	      formNode.interests.value = JSON.stringify(params.interests);
	      formNode.ageFrom.value = params.ageFrom;
	      formNode.ageTo.value = params.ageTo;
	      formNode.genders.value = JSON.stringify(params.genders);
	      formNode.regions.value = JSON.stringify(params.countries);
	      var include = this.adCreatorData.crmAudienceConfig.segmentInclude || [];
	      var exclude = this.adCreatorData.crmAudienceConfig.segmentExclude || [];

	      for (var _i = 0; _i < include.length; _i++) {
	        var input = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name='SEGMENT[INCLUDE][]'>"])));
	        input.value = include[_i];
	        form.appendChild(input);
	      }

	      for (var _i2 = 0; _i2 < exclude.length; _i2++) {
	        var _input = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name='SEGMENT[EXCLUDE][]'>"])));

	        _input.value = exclude[_i2];
	        form.appendChild(_input);
	      }

	      form.submit();
	    }
	  }, {
	    key: "activateStage",
	    value: function activateStage(stageNum) {
	      var stage = document.querySelector("[data-stage=\"".concat(stageNum, "\"]"));
	      var line = stage.querySelector('.crm-ads-new-campaign-item-line');
	      var number = stage.querySelector('.crm-ads-new-campaign-item-number');
	      var checker = stage.querySelector('.crm-ads-new-campaign-item-number-checker');

	      if (line && number) {
	        line.classList.remove('crm-ads-new-campaign-item--inactive');
	        number.classList.remove('crm-ads-new-campaign-item--inactive');
	      }

	      if (checker) {
	        checker.style.display = 'block';
	      }

	      this.completedStages[stageNum] = stageNum;

	      if (Object.keys(this.completedStages).length === 5) {
	        this.activateStage(this._STAGES.toModeration);
	      }

	      if (Object.keys(this.completedStages).length < 5) {
	        this.deActivateStage(this._STAGES.toModeration);
	      }
	    }
	  }, {
	    key: "deActivateStage",
	    value: function deActivateStage(stageNum) {
	      var stage = document.querySelector("[data-stage=\"".concat(stageNum, "\"]"));
	      var line = stage.querySelector('.crm-ads-new-campaign-item-line');
	      var number = stage.querySelector('.crm-ads-new-campaign-item-number');
	      var checker = stage.querySelector('.crm-ads-new-campaign-item-number-checker');

	      if (line && number) {
	        line.classList.add('crm-ads-new-campaign-item--inactive');
	        number.classList.add('crm-ads-new-campaign-item--inactive');
	      }

	      if (checker) {
	        checker.style.display = 'none';
	      }

	      delete this.completedStages[stageNum];

	      if (Object.keys(this.completedStages).length < 6 && this.completedStages[this._STAGES.toModeration]) {
	        this.deActivateStage(this._STAGES.toModeration);
	      }
	    }
	  }, {
	    key: "scrollToStage",
	    value: function scrollToStage(stageNum) {
	      var stage = document.querySelector("[data-stage=\"".concat(stageNum, "\"]"));
	      stage.scrollIntoView({
	        behavior: 'smooth'
	      });
	    }
	  }, {
	    key: "buildSelector",
	    value: function buildSelector() {
	      var _this12 = this;

	      var selector = new ui_entitySelector.TagSelector({
	        id: 'seo-ads-regions',
	        dialogOptions: {
	          id: 'seo-ads-regions',
	          context: 'SEO_ADS_REGIONS',
	          dropdownMode: true,
	          compactView: true,
	          showAvatars: false,
	          width: 350,
	          height: 250,
	          recentTabOptions: {
	            stub: true,
	            stubOptions: {
	              title: main_core.Loc.getMessage('UI_TAG_SELECTOR_START_INPUT')
	            }
	          },
	          searchOptions: {
	            allowCreateItem: false
	          },
	          events: {
	            'Item:onSelect': function ItemOnSelect(event) {
	              var data = event.data.item;
	              _this12.selectedRegions[data.id] = data;
	              _this12.uiNodes.audienceSummary.innerHTML = _this12.buildAudienceSummary();
	            }
	          },
	          entities: [{
	            id: 'facebook_regions',
	            searchable: true,
	            dynamicSearch: true,
	            options: {
	              clientId: this.uiNodes.clientInput.value
	            }
	          }]
	        }
	      });
	      selector.renderTo(document.getElementById('seo-ads-regions'));
	      selector.getDialog().getRecentTab().setVisible(false);
	      var selectorOptions = {
	        iblockId: this.iBlockId,
	        basePriceId: this.basePriceId,
	        fileInputId: '',
	        config: {
	          ENABLE_SEARCH: true,
	          ENABLE_IMAGE_CHANGE_SAVING: true
	        }
	      };
	      this.productSelector = new catalog_productSelector.ProductSelector('facebook-product-selector', selectorOptions);
	      main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.productSelectedEvent.bind(this));
	    }
	  }, {
	    key: "productSelectedEvent",
	    value: function productSelectedEvent(event) {
	      var _this13 = this;

	      var fieldData = event.data.fields;

	      this.seoAccount._helper.request('getProductUrl', {
	        id: fieldData.ID
	      }, function (response) {
	        document.querySelector('.seo-ads-target-url').textContent = response;
	        _this13.uiNodes.form.targetUrl.value = response;

	        _this13.activateStage(_this13._STAGES.pageSelected);
	      });
	    }
	  }, {
	    key: "toCreateStoreSlider",
	    value: function toCreateStoreSlider() {
	      if (!this.isCloud) {
	        this.openTargetPageSlider();
	        return;
	      }

	      var sliderOptions = {
	        width: 990,
	        cacheable: true,
	        allowChangeHistory: false,
	        requestMethod: 'get'
	      };
	      BX.SidePanel.Instance.open('/shop/stores/site/edit/0/?super=Y', sliderOptions);
	    }
	  }]);
	  return SeoAdBuilder;
	}();

	var _templateObject$2;
	var SeoPostSelector = /*#__PURE__*/function () {
	  function SeoPostSelector(options) {
	    babelHelpers.classCallCheck(this, SeoPostSelector);
	    this.helper = Helper.getCreated();
	    this.last = null;
	    this.stopLoading = false;
	    this.loadInProgress = false;
	    this._accountId = options.accountId;
	    this._clientId = options.clientId;
	    this._type = options.type;
	    this.signedParameters = options.signedParameters;
	    this.emptyBlock = document.querySelector('.seo-ads-empty-post-list-block');
	    this.listContent = document.querySelector('.crm-order-instagram-view-list');
	    this.dataContent = [];
	    this.loader = new BX.Loader({
	      target: document.querySelector(".crm-order-instagram-view")
	    });
	    this.init();
	  }

	  babelHelpers.createClass(SeoPostSelector, [{
	    key: "init",
	    value: function init() {
	      this.hideListContentBlock();
	      var topSlider = BX.SidePanel.Instance.getTopSlider().iframe.contentDocument;
	      var observer = new IntersectionObserver(this.loadPostList.bind(this), {
	        root: topSlider,
	        rootMargin: '0px',
	        threshold: 1.0
	      });
	      observer.observe(this.listContent);
	      this.loadPostList();
	    }
	  }, {
	    key: "loadPostList",
	    value: function loadPostList() {
	      var _this = this;

	      if (this.loadInProgress) {
	        return;
	      }

	      if (this.stopLoading) {
	        return;
	      }

	      this.loader.show();
	      this.loadInProgress = true;
	      var requestData = {
	        'clientId': this._clientId || null,
	        'type': this._type || null,
	        'accountId': this._accountId || null,
	        'last': this.last
	      };
	      BX.ajax.runComponentAction('bitrix:seo.ads.builder', 'getPostList', {
	        'mode': 'class',
	        'signedParameters': this.signedParameters,
	        'data': requestData
	      }).then(function (response) {
	        var data = response.data || {};

	        if (data.error) ; else {
	          _this.successFn.apply(_this, [data]);
	        }

	        _this.loadInProgress = false;
	      }, function () {
	        _this.loadInProgress = false;

	        _this.loader.hide();
	      });
	    }
	  }, {
	    key: "showEmptyListBlock",
	    value: function showEmptyListBlock() {
	      this.emptyBlock.style.display = 'block';
	    }
	  }, {
	    key: "hideEmptyListBlock",
	    value: function hideEmptyListBlock() {
	      this.emptyBlock.style.display = 'none';
	    }
	  }, {
	    key: "showListContentBlock",
	    value: function showListContentBlock() {
	      this.listContent.parentNode.style.display = 'block';
	    }
	  }, {
	    key: "hideListContentBlock",
	    value: function hideListContentBlock() {
	      this.listContent.parentNode.style.display = 'none';
	    }
	  }, {
	    key: "successFn",
	    value: function successFn(response) {
	      var _this2 = this;

	      var data = response.data;

	      if (this.clientSelector) {
	        this.clientSelector.enable();
	      }

	      data.postList.forEach(function (postListItem) {
	        var postBlock = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<label class=\"crm-order-instagram-view-item\" id=\"instItemID_", "\">\n\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-detail\">\n\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t<span class=\"crm-order-instagram-view-item-img\" style=\"background-image: url(", ")\"></span>\n\t\t\t\t\t\t\t\t<span class=\"crm-order-instagram-decal-container\">\n\t\t\t\t\t\t\t\t\t<span>\n\t\t\t\t\t\t\t\t\t\t<svg width=\"67\" height=\"16\" viewBox=\"0 0 67 16\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t\t\t\t<path d=\"M48.6026 0.306715C54.4273 0.290801 60.2679 0.306716 66.1244 0.290802C63.0688 5.35159 60.0292 10.3805 56.9736 15.4413C56.9577 15.3777 56.9418 15.3299 56.9418 15.2981C56.7508 13.9931 56.5439 12.6722 56.353 11.3672C56.162 10.0782 55.971 8.78911 55.7641 7.50004C55.6845 6.95895 55.605 6.41785 55.5254 5.86085C55.5095 5.74945 55.4458 5.66988 55.3503 5.60622C54.8252 5.19245 54.3 4.77867 53.7589 4.36489C52.0879 3.05991 50.401 1.75493 48.7299 0.449946C48.6822 0.402203 48.6345 0.370374 48.5708 0.32263C48.5867 0.338545 48.5867 0.322629 48.6026 0.306715ZM56.4962 5.66988C56.8463 7.96156 57.1805 10.2532 57.5306 12.5449C57.642 12.4335 63.976 1.96182 64.0078 1.81859C61.4933 3.10765 58.9947 4.38081 56.4962 5.66988ZM62.7824 1.34115C62.7824 1.32524 62.7665 1.32524 62.7665 1.30932C62.3845 1.2775 51.6105 1.2775 51.4672 1.32524C52.9791 2.50291 54.4751 3.66466 55.971 4.82641C58.2468 3.66466 60.5066 2.50291 62.7824 1.34115Z\" fill=\"#999\"/>\n\t\t\t\t\t\t\t\t\t\t\t<path d=\"M9.46094 15.2344C8.60547 14.3984 4.79192 10.6375 2.65772 8.51853C2.49858 8.3753 2.35535 8.21616 2.21212 8.05702C1.44823 7.26129 1.03445 6.32234 0.954879 5.24016C0.859393 4.01475 1.1936 2.91666 1.95749 1.96179C2.67364 1.07058 3.59667 0.481747 4.74251 0.306688C6.66816 0.00431397 8.19594 0.672722 9.34178 2.24825C9.37361 2.28008 9.38952 2.31191 9.40544 2.34374C9.40544 2.34374 9.42135 2.35965 9.45318 2.37556C9.48501 2.32782 9.53275 2.26416 9.5805 2.21642C10.2012 1.34113 11.0128 0.752293 12.0313 0.434004C12.4769 0.290774 12.9384 0.258947 13.4 0.258947C14.0206 0.258947 14.6095 0.370347 15.1665 0.640892C16.5828 1.29338 17.49 2.37556 17.8242 3.88743C18.1425 5.31973 17.8242 6.64063 16.933 7.80238C16.7738 8.00927 16.5828 8.18433 16.3919 8.37531C14.9277 9.82352 13.4795 11.2717 12.0154 12.7199C11.1923 13.543 10.2422 14.4922 9.46094 15.2344ZM9.42135 13.818C9.51684 13.7226 9.61233 13.643 9.6919 13.5634C10.233 13.0223 10.79 12.4812 11.3311 11.9401C12.827 10.4601 14.3389 8.98005 15.8349 7.4841C15.9622 7.3727 16.0736 7.24538 16.1691 7.10215C17.0125 5.95631 17.1717 4.71498 16.6306 3.39409C15.9622 1.77081 14.1479 0.879609 12.4292 1.32521C11.5539 1.54801 10.8537 2.04136 10.3444 2.78934C10.0579 3.21903 9.8033 3.64872 9.51684 4.07841C9.48501 4.12615 9.45318 4.1739 9.42135 4.23755C9.3577 4.14207 9.30995 4.07841 9.26221 3.99884C8.99166 3.58506 8.73703 3.15537 8.46649 2.7416C7.65485 1.56393 6.19073 0.975095 4.80617 1.27747C2.60998 1.7549 1.40048 3.99883 2.13255 6.09954C2.32352 6.64063 2.64181 7.10215 3.05558 7.51593C3.81948 8.2639 4.58337 9.01188 5.33135 9.75986C6.58859 11.0012 7.84583 12.2584 9.11898 13.4997C9.21447 13.6111 9.30995 13.7066 9.42135 13.818Z\" fill=\"#999\"/>\n\t\t\t\t\t\t\t\t\t\t\t<path d=\"M41.4411 14.9002C41.282 14.8843 41.1387 14.8684 40.9796 14.8525C40.343 14.7888 39.7223 14.7093 39.0858 14.6456C38.5447 14.5819 38.0195 14.5501 37.4784 14.4705C37.2874 14.4546 37.1442 14.4865 36.9851 14.566C36.2371 14.9002 35.4414 15.1071 34.6297 15.1708C32.9269 15.2981 31.3354 14.948 29.8872 14.009C28.5504 13.1337 27.5796 11.9561 26.9749 10.476C26.7043 9.82352 26.5452 9.13921 26.4815 8.43897C26.3383 6.60881 26.7203 4.92188 27.7865 3.42593C28.9642 1.77082 30.5397 0.736386 32.529 0.35444C32.9269 0.274868 33.3407 0.227125 33.7385 0.243039C34.0727 0.258954 34.4069 0.243039 34.7411 0.274868C35.7278 0.370354 36.6668 0.656814 37.5262 1.15016C39.2927 2.13686 40.4862 3.60098 41.1069 5.54254C41.3138 6.19503 41.4093 6.86344 41.4252 7.54776C41.457 8.50263 41.2979 9.40975 40.9796 10.301C40.9478 10.3964 40.9478 10.4919 40.9478 10.5874C41.0432 11.4627 41.1387 12.338 41.2342 13.1974C41.2979 13.7066 41.3456 14.2159 41.4093 14.7411C41.4411 14.7888 41.4411 14.8366 41.4411 14.9002ZM40.3271 13.8021C40.3271 13.7544 40.3271 13.7385 40.3271 13.7226C40.2793 13.2292 40.2157 12.7518 40.1679 12.2584C40.1043 11.6537 40.0406 11.0649 39.977 10.4601C39.9611 10.3487 39.977 10.2691 40.0088 10.1577C40.4385 9.09146 40.5499 7.97745 40.4226 6.84753C40.3271 6.03589 40.0725 5.272 39.6587 4.57177C38.3219 2.1846 35.7119 0.895532 33.0065 1.29339C31.7333 1.48437 30.6193 2.00954 29.6644 2.853C27.8502 4.46036 27.0863 6.83161 27.6274 9.18695C28.0889 11.1444 29.2347 12.5926 31.0012 13.5316C31.7492 13.9294 32.5449 14.1522 33.3884 14.2159C33.8499 14.2477 34.2955 14.2477 34.757 14.2C35.505 14.1045 36.2212 13.8817 36.9055 13.5475C37.0487 13.4838 37.176 13.4679 37.3352 13.4838C37.6694 13.5316 37.9877 13.5634 38.3219 13.5952C38.7675 13.643 39.2131 13.6907 39.6587 13.7385C39.8656 13.7544 40.0884 13.7703 40.3271 13.8021Z\" fill=\"#999\"/>\n\t\t\t\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t\t<span>\n\t\t\t\t\t\t\t\t\t\t<svg width=\"13\" height=\"14\" viewBox=\"0 0 13 14\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M6.145 8.0856L0.5 13.7847V0.5H12.5V13.7847L6.855 8.0856L6.5 7.727L6.145 8.0856Z\" stroke=\"#999\"/>\n\t\t\t\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-decs-block\">\n\t\t\t\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-decs\">\n\t\t\t\t\t\t\t\t\t\t<span class=\"crm-order-instagram-view-item-name\">", "</span>\n\t\t\t\t\t\t\t\t\t\t<span class=\"crm-order-instagram-view-item-edit\"></span>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-checkbox-container\">\n\t\t\t\t\t\t\t\t<input class=\"crm-order-instagram-view-item-input\" \n\t\t\t\t\t\t\t\t\t\ttype=\"checkbox\" \n\t\t\t\t\t\t\t\t\t\tid=\"", "\" \n\t\t\t\t\t\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-input-title\">", "</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"crm-order-instagram-view-item-selected-icon\">\n\t\t\t\t\t\t\t<svg width=\"13\" height=\"10\" viewBox=\"0 0 13 10\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M4.87744 6.02868L10.9842 0L12.8322 1.85828L4.90632 9.7162L4.87744 9.68717L4.84857 9.7162L0 5.02339L1.84802 3.16511L4.87744 6.02868Z\" fill=\"white\"/>\n\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</label>"])), postListItem.id, postListItem.media_url, BX.util.htmlspecialchars(postListItem.caption || ''), postListItem.id, postListItem.id, main_core.Loc.getMessage('SEO_AD_BUILDER_POST_SELECTOR_SELECT'));

	        _this2.listContent.appendChild(postBlock);

	        main_core.Event.bind(postBlock, 'click', _this2.selectPost.bind(_this2));
	        _this2.dataContent[postListItem.id] = postListItem;

	        _this2.showListContentBlock();

	        var text = new BX.UI.TextCrop({
	          rows: 3,
	          target: postBlock.querySelector('.crm-order-instagram-view-item-name')
	        });
	        text.init();
	      });
	      this.loader.hide();

	      if (data.last) {
	        this.last = data.last;
	        return;
	      }

	      if (Object.keys(this.dataContent).length === 0) {
	        this.hideListContentBlock();
	        this.showEmptyListBlock();
	      }

	      this.stopLoading = true;
	    }
	  }, {
	    key: "selectPost",
	    value: function selectPost(event) {
	      var targetElement = event.target;
	      var id = targetElement.dataset.id;
	      document.querySelectorAll('.crm-order-instagram-view-item-input').forEach(function (element) {
	        element.checked = id === element.dataset.id;
	      });
	      document.querySelectorAll('.crm-order-instagram-view-item').forEach(function (element) {
	        BX.removeClass(element, "crm-order-instagram-view-item-selected");
	      });
	      BX.addClass(document.querySelector('#instItemID_' + id), "crm-order-instagram-view-item-selected");
	      BX.SidePanel.Instance.close();
	      BX.SidePanel.Instance.postMessage(window, 'seo-ads-post-selected', this.dataContent[id]);
	    }
	  }]);
	  return SeoPostSelector;
	}();

	var SeoAudience = /*#__PURE__*/function () {
	  function SeoAudience(options) {
	    babelHelpers.classCallCheck(this, SeoAudience);
	    this.helper = Helper.getCreated();
	    this.last = null;
	    this._accountId = options.accountId;
	    this._clientId = options.clientId;
	    this._type = options.type;
	    this.signedParameters = options.signedParameters;
	    this.emptyBlock = document.querySelector('.seo-ads-empty-post-list-block');
	    this.listContent = document.querySelector('.crm-order-instagram-view-list');
	    this.dataContent = [];
	    this.selectedInterest = {};
	    this.loader = new BX.Loader({
	      target: document.querySelector(".crm-order-instagram-view")
	    });
	    this.rangeInput = document.querySelector('.crm-ads-new-campaign-item-runner-value');
	    this.inputMax = BX('max');
	    this.inputMin = BX('min');
	    this.MAX_VALUE = 65;
	    this.MIN_VALUE = 13;
	    this.init();
	  }

	  babelHelpers.createClass(SeoAudience, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      document.querySelectorAll('.crm-ads-new-campaign-item-runner-input').forEach(function (element) {
	        var block = element.closest('.crm-ads-new-campaign-item-runner-block--double');

	        if (block) {
	          _this.setDoubleInputPosition();

	          _this.setDoubleLabelPosition(element);

	          main_core.Event.bind(element, 'change', _this.onDoubleInputRange.bind(_this));
	          main_core.Event.bind(element, 'input', _this.onDoubleInputRange.bind(_this));
	        } else {
	          main_core.Event.bind(element, 'change', _this.onInputRange.bind(_this));
	          main_core.Event.bind(element, 'input', _this.onInputRange.bind(_this));
	        }
	      });
	      this.buildSelector();
	    }
	  }, {
	    key: "checkSex",
	    value: function checkSex() {}
	  }, {
	    key: "onInputRange",
	    value: function onInputRange(event) {
	      var label = event.target.closest('.crm-ads-new-campaign-item-runner-block').children[0].children[0];
	      var value = event.target.value;

	      if (value < this.MIN_VALUE) {
	        event.target.value = this.MIN_VALUE;
	      }

	      label.textContent = event.target.value;
	      this.rangeInput.style.width = event.target.offsetWidth * event.target.value / 65 + "px";
	    }
	  }, {
	    key: "onDoubleInputRange",
	    value: function onDoubleInputRange(event) {
	      this.setDoubleLabelPosition(event.target);
	      this.setDoubleInputPosition();
	    }
	  }, {
	    key: "setDoubleLabelPosition",
	    value: function setDoubleLabelPosition(element) {
	      var value = element.value;
	      var label = element.previousElementSibling;

	      if (value < this.MIN_VALUE) {
	        element.value = this.MIN_VALUE;
	      }

	      label.children[0].textContent = element.value;
	      label.style.left = (value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (element.offsetWidth - 70) + 20 + 'px';
	    }
	  }, {
	    key: "setDoubleInputPosition",
	    value: function setDoubleInputPosition() {
	      var labelMaxLeft = BX('label-max').getBoundingClientRect().left;
	      var labelMinLeft = BX('label-min').getBoundingClientRect().left;
	      var min = Math.min(labelMaxLeft, labelMinLeft);

	      if (labelMaxLeft === min) {
	        this.rangeInput.style.width = (this.inputMin.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMin.offsetWidth - 40) + 20 - ((this.inputMax.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMax.offsetWidth - 40) + 20) + 'px';
	        this.rangeInput.style.left = (this.inputMax.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMax.offsetWidth - 40) + 20 + 'px';
	      } else {
	        this.rangeInput.style.width = (this.inputMax.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMax.offsetWidth - 40) + 20 - ((this.inputMin.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMin.offsetWidth - 40) + 20) + 'px';
	        this.rangeInput.style.left = (this.inputMin.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE) * (this.inputMin.offsetWidth - 40) + 20 + 'px';
	      }
	    }
	  }, {
	    key: "buildSelector",
	    value: function buildSelector() {
	      var _this2 = this;

	      var selector = new ui_entitySelector.TagSelector({
	        id: 'seo-ads-interests',
	        dialogOptions: {
	          id: 'seo-ads-interests',
	          context: 'SEO_ADS_INTERESTS',
	          dropdownMode: true,
	          searchOptions: {
	            allowCreateItem: false
	          },
	          width: 350,
	          height: 250,
	          recentTabOptions: {
	            stub: true,
	            stubOptions: {
	              title: main_core.Loc.getMessage('UI_TAG_SELECTOR_START_INPUT')
	            }
	          },
	          events: {
	            'Item:onSelect': function ItemOnSelect(event) {
	              var data = event.data.item;
	              _this2.selectedInterest[data.id] = data;
	              var sum = 0;

	              for (var key in _this2.selectedInterest) {
	                sum += _this2.selectedInterest[key].customData.get('audienceSize');
	              }

	              document.querySelector('.crm-ads-new-campaign-item-cost-value').textContent = sum;
	              return;
	            }
	          },
	          entities: [{
	            id: 'facebook_interests',
	            searchable: true,
	            dynamicSearch: true,
	            options: {
	              clientId: this._clientId
	            }
	          }]
	        }
	      });
	      selector.renderTo(document.getElementById('seo-ads-interests'));
	    }
	  }, {
	    key: "showEmptyListBlock",
	    value: function showEmptyListBlock() {
	      this.emptyBlock.style.display = 'block';
	    }
	  }, {
	    key: "hideEmptyListBlock",
	    value: function hideEmptyListBlock() {
	      this.emptyBlock.style.display = 'none';
	    }
	  }, {
	    key: "showListContentBlock",
	    value: function showListContentBlock() {
	      this.listContent.parentNode.style.display = 'block';
	    }
	  }, {
	    key: "hideListContentBlock",
	    value: function hideListContentBlock() {
	      this.listContent.parentNode.style.display = 'none';
	    }
	  }, {
	    key: "apply",
	    value: function apply() {
	      var applyBtn = document.getElementById('ui-button-panel-apply');
	      BX.SidePanel.Instance.close();
	      var genders = [];
	      var genderTitles = [];

	      if (document.getElementById('male').checked) {
	        genders.push(1);
	        genderTitles.push(document.getElementById('male').parentNode.querySelector('span').innerText);
	      }

	      if (document.getElementById('female').checked) {
	        genders.push(2);
	        genderTitles.push(document.getElementById('female').parentNode.querySelector('span').innerText);
	      }

	      var interests = [];
	      Object.entries(this.selectedInterest).forEach(function (entry) {
	        var _entry = babelHelpers.slicedToArray(entry, 2),
	            key = _entry[0],
	            value = _entry[1];

	        interests.push({
	          id: value.id,
	          name: value.title
	        });
	      });
	      BX.SidePanel.Instance.postMessage(window, 'seo-fb-audience-configured', {
	        interests: interests,
	        ageFrom: this.inputMin.value,
	        ageTo: this.inputMax.value,
	        genderTitles: genderTitles,
	        genders: genders
	      });
	      document.getElementById('ui-button-panel-apply').classList.remove('ui-btn-wait');
	    }
	  }]);
	  return SeoAudience;
	}();

	var SeoCrmAudience = /*#__PURE__*/function () {
	  function SeoCrmAudience() {
	    babelHelpers.classCallCheck(this, SeoCrmAudience);
	  }

	  babelHelpers.createClass(SeoCrmAudience, null, [{
	    key: "apply",
	    value: function apply(applyBtn) {
	      BX.SidePanel.Instance.close();
	      BX.SidePanel.Instance.postMessage(window, 'seo-crm-audience-configured', {
	        segmentInclude: window.senderSegmentSelector.selectorInclude.selector.getTilesId() || [],
	        segmentExclude: window.senderSegmentSelector.selectorExclude.selector.getTilesId() || []
	      });
	      setTimeout(function () {
	        applyBtn.classList.remove('ui-btn-wait');
	      }, 200);
	    }
	  }]);
	  return SeoCrmAudience;
	}();

	var PageConfiguration = /*#__PURE__*/function () {
	  function PageConfiguration() {
	    babelHelpers.classCallCheck(this, PageConfiguration);
	    this.helper = Helper.getCreated();
	    this.targetUrlBlock = document.querySelector('.seo-ads-target-url');
	    return this;
	  }

	  babelHelpers.createClass(PageConfiguration, [{
	    key: "apply",
	    value: function apply(applyBtn) {
	      if (!this.validateUrl(this.targetUrlBlock.value)) {
	        this.removeWait(applyBtn);
	        return;
	      }

	      BX.SidePanel.Instance.close();
	      BX.SidePanel.Instance.postMessage(window, 'seo-ads-target-post-selected', {
	        targetUrl: this.targetUrlBlock.value
	      });
	      this.removeWait(applyBtn);
	    }
	  }, {
	    key: "removeWait",
	    value: function removeWait(applyBtn) {
	      setTimeout(function () {
	        applyBtn.classList.remove('ui-btn-wait');
	      }, 200);
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {
	      BX.SidePanel.Instance.close();
	    }
	  }, {
	    key: "validateUrl",
	    value: function validateUrl(value) {
	      return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
	    }
	  }]);
	  return PageConfiguration;
	}();

	exports.Helper = Helper;
	exports.SeoAccount = SeoAccount;
	exports.SeoPostSelector = SeoPostSelector;
	exports.SeoAudience = SeoAudience;
	exports.SeoCrmAudience = SeoCrmAudience;
	exports.PageConfiguration = PageConfiguration;
	exports.SeoAdBuilder = SeoAdBuilder;

}((this.BX.Seo = this.BX.Seo || {}),BX.Main,BX.UI,BX.Seo.Ads,BX.Catalog,BX.Event,BX.UI,BX,BX.UI.EntitySelector));
//# sourceMappingURL=seoadbuilder.bundle.js.map
