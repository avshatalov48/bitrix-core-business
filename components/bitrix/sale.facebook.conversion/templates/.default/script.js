(function (exports,main_core,seo_ads_login) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15;

	var SaleFacebookConversion = /*#__PURE__*/function () {
	  function SaleFacebookConversion(containerId, options) {
	    babelHelpers.classCallCheck(this, SaleFacebookConversion);
	    this.wrapper = document.getElementById(containerId);
	    this.eventName = options.eventName;
	    this.facebookBusinessParams = options.facebookBusinessParams;
	    this.shops = options.shops;
	    this.conversionDataLabelsText = options.conversionDataLabelsText;
	    this.title = options.title;

	    if (this.facebookBusinessParams.available) {
	      this.layout();
	    } else {
	      this.layoutError();
	    }
	  }

	  babelHelpers.createClass(SaleFacebookConversion, [{
	    key: "layout",
	    value: function layout() {
	      this.wrapper.innerHTML = '';
	      this.wrapper.appendChild(this.getTitleLayout());
	      this.wrapper.appendChild(this.getInformationLayout());

	      if (this.facebookBusinessParams.auth && this.facebookBusinessParams.profile) {
	        this.wrapper.appendChild(this.getFacebookAuthConnectedLayout());

	        if (this.shops) {
	          var shopsContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"facebook-conversion-shops-container\"></div>"])));

	          for (var shopId in this.shops) {
	            var paramsContainer = this.getParamsContainerLayout(shopId);
	            var switcherContainer = this.getSwitcherContainerLayout(shopId, paramsContainer);
	            var shopContainer = this.getShopContainerLayout(shopId, switcherContainer, paramsContainer);
	            shopsContainer.appendChild(shopContainer);
	          }

	          this.wrapper.appendChild(shopsContainer);
	        }
	      } else {
	        this.wrapper.appendChild(this.getFacebookAuthDisconnectedLayout());
	      }
	    }
	  }, {
	    key: "getShopContainerLayout",
	    value: function getShopContainerLayout(shopId, switcherContainer, paramsContainer) {
	      var shopName = this.shops[shopId].name;
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<div class=\"facebook-conversion-shop-container\">\n\t\t\t\t\t<div class=\"facebook-conversion-shop-name\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Tag.safe(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["", ""])), shopName), switcherContainer, paramsContainer);
	    }
	  }, {
	    key: "notify",
	    value: function notify(message) {
	      BX.UI.Notification.Center.notify({
	        content: message,
	        autoHideDelay: 5000
	      });
	    }
	  }, {
	    key: "getSwitcherContainerLayout",
	    value: function getSwitcherContainerLayout(shopId, paramsContainer) {
	      var switcherContainer = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span></span>"])));
	      var switcher = new BX.UI.Switcher({
	        node: switcherContainer,
	        checked: this.shops[shopId].enabled === 'Y'
	      });
	      switcher.handlers = {
	        checked: this.changeShopEnabledState.bind(this, shopId, 'N', paramsContainer),
	        unchecked: this.changeShopEnabledState.bind(this, shopId, 'Y', paramsContainer)
	      };
	      return switcherContainer;
	    }
	  }, {
	    key: "changeShopEnabledState",
	    value: function changeShopEnabledState(shopId, state, paramsContainer) {
	      var _this = this;

	      this.shops[shopId].enabled = state;
	      BX.ajax.runComponentAction('bitrix:sale.facebook.conversion', 'changeShopEnabledState', {
	        mode: 'class',
	        data: {
	          eventName: this.eventName,
	          shopId: shopId,
	          enabled: state
	        }
	      }).then(function () {
	        _this.notify(main_core.Loc.getMessage('FACEBOOK_CONVERSION_SAVE_SUCCESS'));

	        paramsContainer.style.display = state === 'Y' ? 'block' : 'none';
	      }).catch(function () {
	        _this.notify(main_core.Loc.getMessage('FACEBOOK_CONVERSION_SAVE_ERROR'));
	      });
	    }
	  }, {
	    key: "getParamsContainerLayout",
	    value: function getParamsContainerLayout(shopId) {
	      var params = this.shops[shopId].params;
	      var enabled = this.shops[shopId].enabled;
	      var paramsContainer = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"facebook-conversion-params-container\"></div>"])));
	      paramsContainer.style.display = enabled === 'Y' ? 'block' : 'none';

	      for (var paramName in params) {
	        var param = params[paramName];
	        var isNeedToDisableParam = paramName === 'id' || paramName === 'ids';
	        var checkbox = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t\tid=\"", "\"\n\t\t\t\t\ttype=\"checkbox\"\n\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t", "\n\t\t\t\t>\n\t\t\t"])), shopId + '_' + paramName, param === 'Y' ? 'checked' : '');
	        checkbox.disabled = isNeedToDisableParam;
	        main_core.Event.bind(checkbox, 'change', this.onParamCheckboxChange.bind(this, shopId, paramName));
	        paramsContainer.appendChild(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>\n\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<span class=\"ui-ctl-label-text ", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</label>\n\t\t\t\t</div>\n\t\t\t"])), checkbox, isNeedToDisableParam ? 'facebook-conversion-text-disabled' : '', this.conversionDataLabelsText[paramName]));
	      }

	      return paramsContainer;
	    }
	  }, {
	    key: "onParamCheckboxChange",
	    value: function onParamCheckboxChange(shopId, paramName, event) {
	      var _this2 = this;

	      var checked = event.currentTarget.checked;
	      var dependedParamId = event.currentTarget.dataset.dependedParamId;

	      if (dependedParamId) {
	        this.changeDependedParamCheckboxState(dependedParamId, checked);
	      }

	      this.shops[shopId].params[paramName] = checked ? 'Y' : 'N';
	      BX.ajax.runComponentAction('bitrix:sale.facebook.conversion', 'changeParamState', {
	        mode: 'class',
	        data: {
	          eventName: this.eventName,
	          shopId: shopId,
	          paramName: paramName,
	          state: checked ? 'Y' : 'N'
	        }
	      }).then(function () {
	        _this2.notify(main_core.Loc.getMessage('FACEBOOK_CONVERSION_SAVE_SUCCESS'));
	      }).catch(function () {
	        _this2.notify(main_core.Loc.getMessage('FACEBOOK_CONVERSION_SAVE_ERROR'));
	      });
	    }
	  }, {
	    key: "changeDependedParamCheckboxState",
	    value: function changeDependedParamCheckboxState(dependedParamId, isCheckedRequiredCheckbox) {
	      var dependedCheckbox = document.getElementById(dependedParamId);

	      if (dependedCheckbox) {
	        dependedCheckbox.disabled = !isCheckedRequiredCheckbox;
	        var parentCheckboxNode = dependedCheckbox.parentNode;
	        var checkboxText = parentCheckboxNode.querySelector('.ui-ctl-label-text');
	        checkboxText.className = dependedCheckbox.disabled ? 'ui-ctl-label-text facebook-conversion-text-disabled' : 'ui-ctl-label-text';
	      }
	    }
	  }, {
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      return main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<p class=\"facebook-conversion-event-title\">\n\t\t\t\t", "\n\t\t\t</p>\n\t\t"])), this.title);
	    }
	  }, {
	    key: "getInformationLayout",
	    value: function getInformationLayout() {
	      return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"facebook-conversion-information-container\">\n\t\t\t\t<div class=\"facebook-conversion-logo-container\">\n\t\t\t\t\t<div class=\"facebook-conversion-logo\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div>\n\t\t\t\t\t<div class=\"facebook-conversion-description\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<ol class=\"facebook-conversion-description-list\">\n\t\t\t\t\t\t<li>", "</li>\n\t\t\t\t\t\t<li>", "</li>\n\t\t\t\t\t\t<li>", "</li>\n\t\t\t\t\t\t<li>", "</li>\n\t\t\t\t\t</ol>\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<a \n\t\t\t\t\t\t\thref=\"https://www.facebook.com/business/help/1292598407460746?id=1205376682832142\" \n\t\t\t\t\t\t\tclass=\"facebook-conversion-info\" \n\t\t\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_GIVE_EVENTS'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_GIVE_CLIENT_ACTIONS'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_MAKE_AD_AUDIENCES'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_SHOW_AD'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_INFO'));
	    }
	  }, {
	    key: "getFacebookAuthDisconnectedLayout",
	    value: function getFacebookAuthDisconnectedLayout() {
	      return main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"facebook-conversion-auth-container-disconnected\">\n\t\t\t\t<div class=\"facebook-conversion-auth-container-connect-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"facebook-conversion-auth-connect-container\">\n\t\t\t\t\t<a\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-light-border\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t\t<span class=\"facebook-conversion-auth-connect-info\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('FACEBOOK_CONVERSION_CONNECT_TITLE'), this.login.bind(this), main_core.Loc.getMessage('FACEBOOK_CONVERSION_CONNECT'), main_core.Loc.getMessage('FACEBOOK_CONVERSION_CONNECT_INFO'));
	    }
	  }, {
	    key: "login",
	    value: function login() {
	      seo_ads_login.LoginFactory.getLoginObject({
	        'TYPE': 'facebook',
	        'ENGINE_CODE': 'business.facebook'
	      }).login();
	    }
	  }, {
	    key: "getFacebookAuthConnectedLayout",
	    value: function getFacebookAuthConnectedLayout() {
	      return main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"facebook-conversion-auth-container-connected\">\n\t\t\t\t<div class=\"facebook-conversion-auth-social-avatar\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"facebook-conversion-auth-social-avatar-icon\"\n\t\t\t\t\t\tstyle=\"background-image: url(", ")\"\n\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"facebook-conversion-auth-social-user\">\n\t\t\t\t\t<a\n\t\t\t\t\t\t", "\n\t\t\t\t\t\ttarget=\"_top\"\n\t\t\t\t\t\tclass=\"facebook-conversion-auth-social-user-link\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"facebook-conversion-auth-social-disconnect\">\n\t\t\t\t\t<span\n\t\t\t\t\t\tclass=\"facebook-conversion-auth-social-disconnect-link\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Tag.safe(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t"])), this.facebookBusinessParams.profile.picture), this.facebookBusinessParams.profile.url ? 'href="' + main_core.Tag.safe(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["", ""])), this.facebookBusinessParams.profile.url) + '"' : '', main_core.Tag.safe(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["", ""])), this.facebookBusinessParams.profile.name), this.logout.bind(this), main_core.Loc.getMessage('FACEBOOK_CONVERSION_DISCONNECT'));
	    }
	  }, {
	    key: "logout",
	    value: function logout() {
	      BX.ajax.runComponentAction('bitrix:sale.facebook.conversion', 'logout', {
	        mode: 'class',
	        analyticsLabel: {
	          connect: 'FBE',
	          action: 'disconnect',
	          type: 'disconnect'
	        }
	      }).then(function () {
	        document.location.reload();
	      }).catch(function () {
	        document.location.reload();
	      });
	    }
	  }, {
	    key: "layoutError",
	    value: function layoutError() {
	      var errorNode = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-slider-no-access\">\n\t\t\t\t<div class=\"ui-slider-no-access-inner\">\n\t\t\t\t\t<div class=\"ui-slider-no-access-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-slider-no-access-img\">\n\t\t\t\t\t\t<div class=\"ui-slider-no-access-img-inner\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('FACEBOOK_CONVERSION_NOT_AVAILABLE'));
	      this.wrapper.appendChild(errorNode);
	    }
	  }]);
	  return SaleFacebookConversion;
	}();

	main_core.Reflection.namespace('BX').SaleFacebookConversion = SaleFacebookConversion;

}((this.window = this.window || {}),BX,BX.Seo.Ads));
//# sourceMappingURL=script.js.map
