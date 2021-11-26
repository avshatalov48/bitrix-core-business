(function (exports,main_core,tasks_commentRenderer) {
	'use strict';

	var RenderParts = /*#__PURE__*/function () {
	  function RenderParts() {
	    babelHelpers.classCallCheck(this, RenderParts);
	  }

	  babelHelpers.createClass(RenderParts, null, [{
	    key: "init",
	    value: function init(params) {
	      if (!main_core.Type.isUndefined(params.currentUserSonetGroupIdList)) {
	        this.currentUserSonetGroupIdList = params.currentUserSonetGroupIdList;
	      }

	      if (!main_core.Type.isUndefined(params.publicSection)) {
	        this.publicSection = !!params.publicSection;
	      }

	      this.mobile = !!params.mobile;

	      if (!main_core.Type.isUndefined(params.currentExtranetUser)) {
	        this.currentExtranetUser = !!params.currentExtranetUser;
	      }

	      if (this.currentExtranetUser) {
	        if (main_core.Type.isPlainObject(params.availableUsersList)) {
	          params.availableUsersList = Object.entries(params.availableUsersList).map(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                key = _ref2[0],
	                value = _ref2[1];

	            return value;
	          });
	        }

	        if (main_core.Type.isArray(params.availableUsersList)) {
	          this.availableUsersList = params.availableUsersList.map(function (value) {
	            return parseInt(value);
	          }).filter(function (value) {
	            return !main_core.Type.isNil(value);
	          });
	        }
	      }
	    }
	  }, {
	    key: "getNodeSG",
	    value: function getNodeSG(entity) {
	      var hidden = main_core.Type.isStringFilled(entity.VISIBILITY) && entity.VISIBILITY === 'group_members' && !this.currentUserSonetGroupIdList.includes(entity.ENTITY_ID);

	      if (hidden) {
	        return this.getNodeHiddenDestination();
	      } else {
	        return !this.mobile ? main_core.Dom.create('a', {
	          attrs: {
	            href: entity.LINK,
	            target: '_blank'
	          },
	          text: entity.NAME
	        }) : main_core.Dom.create('span', {
	          text: entity.NAME
	        });
	      }
	    }
	  }, {
	    key: "getNodeU",
	    value: function getNodeU(entity) {
	      var hidden = this.currentExtranetUser && !this.availableUsersList.includes(entity.ENTITY_ID);

	      if (hidden) {
	        return this.getNodeHiddenDestination();
	      } else {
	        var classesList = ['blog-p-user-name'];

	        if (entity.VISIBILITY === 'extranet') {
	          classesList.push('blog-p-user-name-extranet');
	        }

	        return !this.mobile ? main_core.Dom.create('a', {
	          attrs: {
	            href: entity.LINK
	          },
	          props: {
	            className: classesList.join(' ')
	          },
	          text: entity.NAME
	        }) : main_core.Dom.create('a', {
	          attrs: {
	            href: entity.LINK
	          },
	          text: entity.NAME
	        });
	      }
	    }
	  }, {
	    key: "getNodeDR",
	    value: function getNodeDR(entity) {
	      return !this.mobile ? main_core.Dom.create('a', {
	        attrs: {
	          href: entity.LINK,
	          target: '_blank'
	        },
	        text: entity.NAME
	      }) : main_core.Dom.create('span', {
	        text: entity.NAME
	      });
	    }
	  }, {
	    key: "getNodeTask",
	    value: function getNodeTask(entity) {
	      return !this.mobile && !this.publicSection && entity.LINK.length > 0 && typeof entity.VISIBILITY != 'undefined' && typeof entity.VISIBILITY.userId != 'undefined' && parseInt(entity.VISIBILITY.userId) == parseInt(main_core.Loc.getMessage('USER_ID')) ? main_core.Dom.create('a', {
	        attrs: {
	          href: entity.LINK,
	          target: '_blank'
	        },
	        text: entity.NAME
	      }) : main_core.Dom.create('span', {
	        text: entity.NAME
	      });
	    }
	  }, {
	    key: "getNodePost",
	    value: function getNodePost(entity) {
	      return !this.mobile && !this.publicSection && entity.LINK.length > 0 && main_core.Type.isPlainObject(entity.VISIBILITY) && entity.VISIBILITY.available === true ? main_core.Dom.create('a', {
	        attrs: {
	          href: entity.LINK,
	          target: '_blank'
	        },
	        text: entity.NAME
	      }) : main_core.Dom.create('span', {
	        text: entity.NAME
	      });
	    }
	  }, {
	    key: "getNodeCalendarEvent",
	    value: function getNodeCalendarEvent(entity) {
	      return !this.mobile && !this.publicSection && entity.LINK.length > 0 && main_core.Type.isPlainObject(entity.VISIBILITY) && entity.VISIBILITY.available === true ? main_core.Dom.create('a', {
	        attrs: {
	          href: entity.LINK,
	          target: '_blank'
	        },
	        text: entity.NAME
	      }) : main_core.Dom.create('span', {
	        text: entity.NAME
	      });
	    }
	  }, {
	    key: "getNodeUA",
	    value: function getNodeUA() {
	      return main_core.Dom.create('span', {
	        text: main_core.Loc.getMessage('SONET_RENDERPARTS_JS_DESTINATION_ALL')
	      });
	    }
	  }, {
	    key: "getNodeHiddenDestination",
	    value: function getNodeHiddenDestination() {
	      return main_core.Dom.create('span', {
	        text: main_core.Loc.getMessage('SONET_RENDERPARTS_JS_HIDDEN')
	      });
	    }
	  }, {
	    key: "getTaskCommentPart",
	    value: function getTaskCommentPart(entity) {
	      return tasks_commentRenderer.CommentRenderer.getCommentPart(entity);
	    }
	  }]);
	  return RenderParts;
	}();
	babelHelpers.defineProperty(RenderParts, "currentUserSonetGroupIdList", []);

	exports.RenderParts = RenderParts;

}((this.BX = this.BX || {}),BX,BX.Tasks));
//# sourceMappingURL=renderparts.bundle.js.map
