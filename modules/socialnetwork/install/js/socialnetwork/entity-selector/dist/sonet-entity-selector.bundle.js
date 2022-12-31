this.BX = this.BX || {};
this.BX.SocialNetwork = this.BX.SocialNetwork || {};
(function (exports,main_core_events,ui_entitySelector,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	var Footer = /*#__PURE__*/function (_DefaultFooter) {
	  babelHelpers.inherits(Footer, _DefaultFooter);

	  function Footer(dialog, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Footer);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Footer).call(this, dialog, options));
	    _this.handleDialogDestroy = _this.handleDialogDestroy.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleSliderMessage = _this.handleSliderMessage.bind(babelHelpers.assertThisInitialized(_this));

	    _this.bindEvents();

	    return _this;
	  }

	  babelHelpers.createClass(Footer, [{
	    key: "getContent",
	    value: function getContent() {
	      var _this2 = this;

	      return this.cache.remember('content', function () {
	        if (_this2.getOption('tagCreationLabel', false)) {
	          return _this2.createTagCreationLabel();
	        }

	        var inviteEmployeeLink = _this2.getOption('inviteEmployeeLink');

	        var inviteGuestLink = _this2.getOption('inviteGuestLink');

	        var inviteEmployeeScope = _this2.getOption('inviteEmployeeScope');

	        var createProjectLink = _this2.getOption('createProjectLink');

	        var complexPhrases = {
	          '111': 'SOCNET_ENTITY_SELECTOR_EMPLOYEE_OR_PROJECT_OR_GUEST',
	          '110': 'SOCNET_ENTITY_SELECTOR_INVITE_EMPLOYEE_OR_GUEST',
	          '101': 'SOCNET_ENTITY_SELECTOR_EMPLOYEE_OR_PROJECT',
	          '011': 'SOCNET_ENTITY_SELECTOR_PROJECT_OR_GUEST'
	        };
	        var complexCode = Number(Boolean(inviteEmployeeLink)).toString() + Number(Boolean(inviteGuestLink)).toString() + Number(Boolean(createProjectLink)).toString();
	        var complexPhrase = complexPhrases[complexCode] ? complexPhrases[complexCode] : null;

	        if (complexPhrase) {
	          var phrase = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), main_core.Loc.getMessage(complexPhrase));
	          var employee = phrase.querySelector('employee');
	          var guest = phrase.querySelector('guest');
	          var project = phrase.querySelector('project');
	          var spans = Array.from(phrase.querySelectorAll('span'));
	          var firstTag = Array.from(phrase.children).find(function (element) {
	            return [employee, guest, project].includes(element);
	          });
	          var hideIcon = employee && guest && project;

	          if (employee) {
	            var showIcon = !hideIcon && firstTag === employee;
	            phrase.replaceChild(_this2.createInviteEmployeeLink(employee.innerHTML, showIcon), employee);
	          }

	          if (guest) {
	            var _showIcon = !hideIcon && firstTag === guest;

	            var guestLink = _this2.createInviteGuestLink(guest.innerHTML, _showIcon);

	            phrase.replaceChild(guestLink, guest);

	            _this2.createHint(guestLink);
	          }

	          if (project) {
	            var _showIcon2 = !hideIcon && firstTag === project;

	            phrase.replaceChild(_this2.createProjectLink(project.innerHTML, _showIcon2), project);
	          }

	          spans.forEach(function (span) {
	            phrase.replaceChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-selector-footer-conjunction\">", "</span>"])), span.innerHTML), span);
	          }); // Get rid of the outer <div>

	          var fragment = document.createDocumentFragment();
	          Array.from(phrase.childNodes).forEach(function (element) {
	            fragment.appendChild(element);
	          });
	          return fragment;
	        } else if (inviteEmployeeLink) {
	          var _phrase = '';

	          switch (inviteEmployeeScope) {
	            case 'I':
	              _phrase = main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_EMPLOYEE');
	              break;

	            case 'E':
	              _phrase = main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_EXTRANET');
	              break;

	            case 'IE':
	              _phrase = main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_EMPLOYEE_OR_EXTRANET');
	              break;

	            default:
	          }

	          return _this2.createInviteEmployeeLink(_phrase, true);
	        } else if (inviteGuestLink) {
	          var _guestLink = _this2.createInviteGuestLink(main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_GUEST'), true);

	          _this2.createHint(_guestLink);

	          return _guestLink;
	        } else if (createProjectLink) {
	          return _this2.createProjectLink(main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_CREATE_PROJECT'), true);
	        }

	        return null;
	      });
	    }
	  }, {
	    key: "createTagCreationLabel",
	    value: function createTagCreationLabel() {
	      return main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_TAG_FOOTER_LABEL');
	    }
	  }, {
	    key: "createInviteEmployeeLink",
	    value: function createInviteEmployeeLink(text, icon) {
	      var className = "ui-selector-footer-link".concat(icon ? ' ui-selector-footer-link-add' : '');
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"", "\" onclick=\"", "\">", "</span>\n\t\t"])), className, this.handleInviteEmployeeClick.bind(this), text);
	    }
	  }, {
	    key: "createInviteGuestLink",
	    value: function createInviteGuestLink(text, icon) {
	      var className = "ui-selector-footer-link".concat(icon ? ' ui-selector-footer-link-add' : '');
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"", "\" onclick=\"", "\">", "</span>\n\t\t"])), className, this.handleInviteGuestClick.bind(this), text);
	    }
	  }, {
	    key: "createProjectLink",
	    value: function createProjectLink(text, icon) {
	      var className = "ui-selector-footer-link".concat(icon ? ' ui-selector-footer-link-add' : '');
	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"", "\" onclick=\"", "\">", "</span>\n\t\t"])), className, this.handleCreateProjectClick.bind(this), text);
	    }
	  }, {
	    key: "createHint",
	    value: function createHint(link) {
	      main_core.Runtime.loadExtension('ui.hint').then(function () {
	        var hint = BX.UI.Hint.createInstance();
	        var node = hint.createNode(main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITED_GUEST_HINT'));
	        main_core.Dom.insertAfter(node, link);
	      });
	    }
	  }, {
	    key: "handleInviteEmployeeClick",
	    value: function handleInviteEmployeeClick() {
	      var inviteEmployeeLink = this.getOption('inviteEmployeeLink');

	      if (main_core.Type.isStringFilled(inviteEmployeeLink)) {
	        var entity = this.getDialog().getEntity('user');
	        var userOptions = entity.getOptions() || {};
	        BX.SidePanel.Instance.open(inviteEmployeeLink, {
	          allowChangeHistory: false,
	          cacheable: false,
	          requestMethod: 'post',
	          requestParams: {
	            componentParams: JSON.stringify({
	              'USER_OPTIONS': userOptions
	            })
	          },
	          data: {
	            entitySelectorId: this.getDialog().getId()
	          }
	        });
	      }
	    }
	  }, {
	    key: "handleInviteGuestClick",
	    value: function handleInviteGuestClick() {
	      var inviteGuestLink = this.getOption('inviteGuestLink');

	      if (main_core.Type.isStringFilled(inviteGuestLink)) {
	        var entity = this.getDialog().getEntity('user');
	        var userOptions = entity.getOptions() || {};
	        var rows = [];
	        var searchQuery = this.getDialog().getTagSelectorQuery();

	        if (main_core.Validation.isEmail(searchQuery)) {
	          rows.push({
	            'email': searchQuery
	          });
	        }

	        BX.SidePanel.Instance.open(inviteGuestLink, {
	          width: 1200,
	          allowChangeHistory: false,
	          cacheable: false,
	          requestMethod: 'post',
	          requestParams: {
	            componentParams: JSON.stringify({
	              'USER_OPTIONS': userOptions,
	              'ROWS': rows
	            })
	          },
	          data: {
	            entitySelectorId: this.getDialog().getId()
	          }
	        });
	      }
	    }
	  }, {
	    key: "handleCreateProjectClick",
	    value: function handleCreateProjectClick() {
	      var createProjectLink = this.getOption('createProjectLink');

	      if (main_core.Type.isStringFilled(createProjectLink)) {
	        var entity = this.getDialog().getEntity('project');
	        var projectOptions = entity.getOptions() || {};
	        BX.SidePanel.Instance.open(createProjectLink, {
	          allowChangeHistory: false,
	          cacheable: false,
	          requestMethod: 'post',
	          requestParams: {
	            PROJECT_OPTIONS: projectOptions,
	            refresh: 'N'
	          },
	          data: {
	            entitySelectorId: this.getDialog().getId()
	          }
	        });
	      }
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.getDialog().subscribe('onDestroy', this.handleDialogDestroy);
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', this.handleSliderMessage);
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      this.getDialog().unsubscribe('onDestroy', this.handleDialogDestroy);
	      main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.handleSliderMessage);
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.unbindEvents();
	    }
	  }, {
	    key: "handleSliderMessage",
	    value: function handleSliderMessage(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          messageEvent = _event$getData2[0];

	      var slider = messageEvent.getSender();

	      if (slider.getData().get('entitySelectorId') !== this.getDialog().getId()) {
	        return;
	      }

	      if (messageEvent.getEventId() === 'BX.Intranet.Invitation:onAdd' || messageEvent.getEventId() === 'BX.Intranet.Invitation.Guest:onAdd') {
	        var _messageEvent$getData = messageEvent.getData(),
	            users = _messageEvent$getData.users;

	        this.addUsers(users);
	      }

	      if (messageEvent.getEventId() === 'BX.Socialnetwork.Workgroup:onAdd') {
	        var _messageEvent$getData2 = messageEvent.getData(),
	            projects = _messageEvent$getData2.projects;

	        this.addProjects(projects);
	      }
	    }
	  }, {
	    key: "addUsers",
	    value: function addUsers(users) {
	      var _this3 = this;

	      if (!main_core.Type.isArrayFilled(users)) {
	        return;
	      }

	      var tab = this.getDialog().getRecentTab() || this.getDialog().getTab('invited-users');

	      if (!tab) {
	        tab = this.getDialog().addTab(this.createInvitedUsersTab());
	      }

	      users.forEach(function (user) {
	        if (!main_core.Type.isPlainObject(user)) {
	          return;
	        }

	        var item = _this3.getDialog().addItem(Object.assign({}, user, {
	          tabs: tab.getId(),
	          sort: 2
	        }));

	        if (item) {
	          item.select();
	        }
	      });
	      this.getDialog().selectTab(tab.getId());
	    }
	  }, {
	    key: "addProjects",
	    value: function addProjects(projects) {
	      var _this4 = this;

	      if (!main_core.Type.isArrayFilled(projects)) {
	        return;
	      }

	      var tab = this.getDialog().getRecentTab() || this.getDialog().getTab('projects');
	      var tabId = tab ? tab.getId() : null;
	      projects.forEach(function (project) {
	        if (!main_core.Type.isPlainObject(project)) {
	          return;
	        }

	        var item = _this4.getDialog().addItem(Object.assign({}, project, {
	          tabs: tabId,
	          sort: 2
	        }));

	        if (item) {
	          item.select();
	        }
	      });
	      this.getDialog().selectTab(tabId);
	    }
	  }, {
	    key: "createInvitedUsersTab",
	    value: function createInvitedUsersTab() {
	      var icon = 'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20' + 'fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20fill-rule%3D%22evenodd' + '%22%20clip-rule%3D%22evenodd%22%20d%3D%22M11.447%202.984a8.447%208.447%200%20100%2016.893%208.447%2' + '08.447%200%20000-16.893zM5.785%2016.273c-.02.018-.116.09-.234.177a7.706%207.706%200%2001-1.846-5.02' + 'c0-.36.025-.717.073-1.069.442.573.927%201.347%201.094%201.579.272.379.575.75.84%201.136.444.648.91%' + '201.534.673%202.34-.102.346-.328.627-.6.857zm8.92-6.27s.402%201.575%202.122%201.444c.544-.04.509%20' + '1.414.162%202.015-.43.743-.318%201.627-.725%202.37-.256.467-.69.814-1.035%201.213-.43.501-.913.984-' + '1.631.922-.474-.04-.67-.565-.763-.939-.23-.928-.39-2.828-.39-2.828s-.668-1.443-2.177-1.003c-1.509.' + '44-1.816-.728-1.859-1.84-.102-2.742%202.204-3.032%202.472-2.984.383.069%201.507.262%201.79.418.956' + '.528%201.935-.2%201.858-.585-.077-.385-2.453-.939-3.296-.999-.843-.06-.92.24-1.151-.014-.187-.205-' + '.015-.53.116-.703.93-1.225%202.48-1.522%203.791-2.16.02-.01.051-.08.087-.184a7.72%207.72%200%20012.' + '846%201.81%207.719%207.719%200%20011.894%203.091c-.28.165.277-.057-1.185.283-1.462.34-2.926.673-2.9' + '26.673z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E';
	      return {
	        id: 'invited-users',
	        title: main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITED_USERS_TAB_TITLE'),
	        bgColor: {
	          selected: '#f7a700',
	          selectedHovered: '#faac09'
	        },
	        icon: {
	          //default: '/bitrix/js/socialnetwork/entity-selector/src/images/invited-users-tab-icon.svg',
	          //selected: '/bitrix/js/socialnetwork/entity-selector/src/images/invited-users-tab-icon-selected.svg'
	          "default": icon,
	          selected: icon.replace(/ABB1B8/g, 'fff')
	        }
	      };
	    }
	  }]);
	  return Footer;
	}(ui_entitySelector.DefaultFooter);

	var _templateObject$1;

	var TextFooter = /*#__PURE__*/function (_DefaultFooter) {
	  babelHelpers.inherits(TextFooter, _DefaultFooter);

	  function TextFooter() {
	    babelHelpers.classCallCheck(this, TextFooter);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextFooter).apply(this, arguments));
	  }

	  babelHelpers.createClass(TextFooter, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-selector-footer-default ui-selector-footer-long-text\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getContent() ? this.getContent() : '');
	    }
	  }]);
	  return TextFooter;
	}(ui_entitySelector.DefaultFooter);

	exports.Dialog = ui_entitySelector.Dialog;
	exports.Footer = Footer;
	exports.TextFooter = TextFooter;

}((this.BX.SocialNetwork.EntitySelector = this.BX.SocialNetwork.EntitySelector || {}),BX.Event,BX.UI.EntitySelector,BX));
//# sourceMappingURL=sonet-entity-selector.bundle.js.map
