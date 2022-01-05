(function (exports,main_core,main_core_events,socialnetwork_renderparts) {
	'use strict';

	var CommentAux = /*#__PURE__*/function () {
	  function CommentAux() {
	    babelHelpers.classCallCheck(this, CommentAux);
	  }

	  babelHelpers.createClass(CommentAux, null, [{
	    key: "init",
	    value: function init(params) {
	      main_core_events.EventEmitter.emit('BX.CommentAux.initialize', new main_core_events.BaseEvent({
	        compatData: []
	      }));
	      socialnetwork_renderparts.RenderParts.init(params);
	    }
	  }, {
	    key: "isSourcePost",
	    value: function isSourcePost(eventType) {
	      return this.postEventTypeList.includes(eventType);
	    }
	  }, {
	    key: "isSourceComment",
	    value: function isSourceComment(eventType) {
	      return this.commentEventTypeList.includes(eventType);
	    }
	  }, {
	    key: "getTypesList",
	    value: function getTypesList() {
	      return Object.values(this.typesList);
	    }
	  }, {
	    key: "getLiveTypesList",
	    value: function getLiveTypesList() {
	      return [this.typesList.createentity, this.typesList.createtask, this.typesList.fileversion, this.typesList.taskinfo];
	    }
	  }, {
	    key: "getLiveText",
	    value: function getLiveText(type, params) {
	      var _this = this;

	      var result = '';
	      var sourceEntityType = '';
	      var sourceEntityLink = '';
	      var suffix = '';

	      if (type.toLowerCase() === this.typesList.share) {
	        if (main_core.Type.isPlainObject(params) && params.length > 0) {
	          result = main_core.Loc.getMessage(params.length === 1 ? 'SONET_COMMENTAUX_JS_SHARE_TEXT' : 'SONET_COMMENTAUX_JS_SHARE_TEXT_1');
	          result = result.replace('#SHARE_LIST#', this.getShareList(params));
	        }
	      } else if (type.toLowerCase() === this.typesList.createentity) {
	        if (main_core.Type.isPlainObject(params) && main_core.Type.isStringFilled(params.entityType) && !main_core.Type.isUndefined(params.entityId) && parseInt(params.entityId) > 0 && main_core.Type.isStringFilled(params.entityName) && main_core.Type.isStringFilled(params.sourceEntityType) && !main_core.Type.isUndefined(params.sourceEntityId) && parseInt(params.sourceEntityId) > 0) {
	          var entityName = this.renderEntity({
	            ENTITY_TYPE: params.entityType,
	            NAME: params.entityName,
	            LINK: main_core.Type.isStringFilled(params.entityUrl) ? params.entityUrl : '',
	            VISIBILITY: this.getEntityVisibility(params)
	          });
	          sourceEntityLink = main_core.Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '';
	          sourceEntityLink = !socialnetwork_renderparts.RenderParts.mobile ? "<a target=\"_blank\" href=\"".concat(sourceEntityLink, "\">") : '';

	          if (this.isSourcePost(params.sourceEntityType)) {
	            sourceEntityType = main_core.Type.isStringFilled(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST';
	            suffix = main_core.Type.isStringFilled(params.suffix) ? "_".concat(params.suffix) : '';
	            sourceEntityType = "".concat(sourceEntityType).concat(suffix);
	            result = main_core.Loc.getMessage("SONET_COMMENTAUX_JS_CREATEENTITY_POST_".concat(sourceEntityType)).replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType)).replace('#ENTITY_NAME#', entityName).replace('#A_BEGIN#', sourceEntityLink).replace('#A_END#', !socialnetwork_renderparts.RenderParts.mobile ? '</a>' : '');
	          } else if (this.isSourceComment(params.sourceEntityType)) {
	            suffix = main_core.Type.isStringFilled(params.suffix) ? "_".concat(params.suffix) : '';
	            sourceEntityType = main_core.Type.isStringFilled(params.sourceEntityType) ? "".concat(params.sourceEntityType).concat(suffix) : 'BLOG_COMMENT';
	            result = main_core.Loc.getMessage("SONET_COMMENTAUX_JS_CREATEENTITY_COMMENT_".concat(sourceEntityType)).replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType)).replace('#ENTITY_NAME#', entityName).replace('#A_BEGIN#', sourceEntityLink).replace('#A_END#', !socialnetwork_renderparts.RenderParts.mobile ? '</a>' : '');
	          }
	        }
	      } else if (type.toLowerCase() === this.typesList.createtask) {
	        if (main_core.Type.isPlainObject(params) && !main_core.Type.isUndefined(params.taskId) && parseInt(params.taskId) > 0 && main_core.Type.isStringFilled(params.taskName) && main_core.Type.isStringFilled(params.sourceEntityType) && !main_core.Type.isUndefined(params.sourceEntityId) && parseInt(params.sourceEntityId) > 0) {
	          var task = this.renderEntity({
	            ENTITY_TYPE: 'task',
	            NAME: params.taskName,
	            LINK: main_core.Type.isStringFilled(params.taskUrl) ? params.taskUrl : '',
	            VISIBILITY: {
	              userId: !main_core.Type.isUndefined(params.taskResponsibleId) && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0
	            }
	          });

	          if (this.isSourcePost(params.sourceEntityType)) {
	            sourceEntityType = main_core.Type.isStringFilled(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST';
	            suffix = main_core.Type.isStringFilled(params.suffix) ? "_".concat(params.suffix) : '';
	            sourceEntityLink = main_core.Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '';
	            result = main_core.Loc.getMessage("SONET_COMMENTAUX_JS_CREATETASK_POST_".concat(sourceEntityType).concat(suffix)).replace('#TASK_NAME#', task).replace('#A_BEGIN#', !socialnetwork_renderparts.RenderParts.mobile ? "<a target=\"_blank\" href=\"".concat(sourceEntityLink, "\">") : '').replace('#A_END#', !socialnetwork_renderparts.RenderParts.mobile ? '</a>' : '');
	          } else if (this.isSourceComment(params.sourceEntityType)) {
	            suffix = main_core.Type.isStringFilled(params.suffix) ? "_".concat(params.suffix) : '';
	            sourceEntityType = main_core.Type.isStringFilled(params.sourceEntityType) ? "".concat(params.sourceEntityType).concat(suffix) : 'BLOG_COMMENT';
	            sourceEntityLink = main_core.Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '';
	            result = main_core.Loc.getMessage("SONET_COMMENTAUX_JS_CREATETASK_COMMENT_".concat(sourceEntityType)).replace('#TASK_NAME#', task).replace('#A_BEGIN#', !socialnetwork_renderparts.RenderParts.mobile ? "<a target=\"_blank\" href=\"".concat(sourceEntityLink, "\">") : '').replace('#A_END#', !socialnetwork_renderparts.RenderParts.mobile ? '</a>' : '');
	          }
	        }
	      } else if (type.toLowerCase() === this.typesList.fileversion) {
	        var messageType = main_core.Type.isPlainObject(params) && !main_core.Type.isUndefined(params.isEnabledKeepVersion) && params.isEnabledKeepVersion ? 'SONET_COMMENTAUX_JS_FILEVERSION_TEXT' : 'SONET_COMMENTAUX_JS_HEAD_FILEVERSION_TEXT';
	        var userGenderSuffix = main_core.Type.isPlainObject(params) && main_core.Type.isStringFilled(params.userGender) ? "_".concat(params.userGender) : '';
	        result = main_core.Loc.getMessage("".concat(messageType).concat(userGenderSuffix));
	      } else if (type.toLowerCase() === this.typesList.taskinfo) {
	        if (main_core.Type.isPlainObject(params) && main_core.Type.isStringFilled(params.JSON)) {
	          var textList = [];
	          var partsData = {};

	          try {
	            partsData = JSON.parse(main_core.Text.decode(params.JSON));
	          } catch (e) {
	            partsData = {};
	          }

	          main_core.Type.isArray(partsData);
	          {
	            partsData.forEach(function (partsItems) {
	              if (!main_core.Type.isArray(partsItems)) {
	                return;
	              }

	              partsItems.forEach(function (item) {
	                var messageCode = item[0];

	                if (!main_core.Type.isStringFilled(messageCode)) {
	                  return;
	                }

	                textList.push(_this.renderEntity({
	                  ENTITY_TYPE: 'TASK_COMMENT_PART',
	                  CODE: messageCode,
	                  REPLACE_LIST: main_core.Type.isPlainObject(item[1]) ? item[1] : {}
	                }));
	              });
	            });
	          }

	          if (textList.length) {
	            result = textList.join('<br>');
	          }
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getShareList",
	    value: function getShareList(params) {
	      var _this2 = this;

	      var result = '';
	      var renderedShareList = [];

	      if (!main_core.Type.isPlainObject(params) || params.length <= 0) {
	        return result;
	      }

	      Object.values(params).forEach(function (value) {
	        renderedShareList.push(_this2.renderEntity(value));
	      });
	      result = renderedShareList.join(', ');
	      return result;
	    }
	  }, {
	    key: "renderEntity",
	    value: function renderEntity(entity) {
	      var result = '';

	      if (!main_core.Type.isPlainObject(entity) || !main_core.Type.isStringFilled(entity.ENTITY_TYPE)) {
	        return result;
	      }

	      switch (entity.ENTITY_TYPE.toUpperCase()) {
	        case 'U':
	          result = socialnetwork_renderparts.RenderParts.getNodeU(entity);
	          break;

	        case 'UA':
	          result = socialnetwork_renderparts.RenderParts.getNodeUA();
	          break;

	        case 'SG':
	          result = socialnetwork_renderparts.RenderParts.getNodeSG(entity);
	          break;

	        case 'DR':
	          result = socialnetwork_renderparts.RenderParts.getNodeDR(entity);
	          break;

	        case 'TASK':
	          result = socialnetwork_renderparts.RenderParts.getNodeTask(entity);
	          break;

	        case 'BLOG_POST':
	          result = socialnetwork_renderparts.RenderParts.getNodePost(entity);
	          break;

	        case 'CALENDAR_EVENT':
	          result = socialnetwork_renderparts.RenderParts.getNodeCalendarEvent(entity);
	          break;

	        case 'TASK_COMMENT_PART':
	          result = socialnetwork_renderparts.RenderParts.getTaskCommentPart(entity);
	          break;

	        default:
	      }

	      var tmp = main_core.Dom.create('div', {
	        children: [result]
	      });
	      result = tmp.innerHTML;
	      main_core.Dom.clean(tmp);
	      main_core.Dom.remove(tmp);
	      return result;
	    }
	  }, {
	    key: "getEntityCreatedMessage",
	    value: function getEntityCreatedMessage(entityType) {
	      var result = '';

	      if (!main_core.Type.isStringFilled(entityType)) {
	        return result;
	      }

	      switch (entityType) {
	        case 'TASK':
	          result = main_core.Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_TASK');
	          break;

	        case 'BLOG_POST':
	          result = main_core.Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_BLOG_POST');
	          break;

	        case 'CALENDAR_EVENT':
	          result = main_core.Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_CALENDAR_EVENT');
	          break;

	        default:
	      }

	      return result;
	    }
	  }, {
	    key: "getEntityTypeName",
	    value: function getEntityTypeName(entityType) {
	      var result = '';

	      if (!main_core.Type.isStringFilled(entityType)) {
	        return result;
	      }

	      switch (entityType) {
	        case 'TASK':
	          result = main_core.Loc.getMessage('SONET_COMMENTAUX_CREATEENTITY_ENTITY_TASK');
	          break;

	        default:
	      }

	      return result;
	    }
	  }, {
	    key: "getEntityVisibility",
	    value: function getEntityVisibility(params) {
	      var result = {};
	      var currentUserId = parseInt(main_core.Loc.getMessage('USER_ID'));

	      if (params.entityType.toUpperCase() === 'TASK') {
	        result.userId = !main_core.Type.isUndefined(params.taskResponsibleId) && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0;
	      } else if (params.entityType.toUpperCase() === 'BLOG_POST') {
	        result.available = main_core.Type.isArray(params.socNetPermissions) && (params.socNetPermissions.indexOf('G2') > -1 || params.socNetPermissions.indexOf('UA') > -1 || params.socNetPermissions.indexOf("U".concat(currentUserId)) > -1 || params.socNetPermissions.indexOf("US".concat(currentUserId)) > -1);
	      } else if (params.entityType.toUpperCase() === 'CALENDAR_EVENT') {
	        result.available = main_core.Type.isArray(params.attendees) && params.attendees.indexOf(currentUserId) > -1;
	      }

	      return result;
	    }
	  }]);
	  return CommentAux;
	}();
	babelHelpers.defineProperty(CommentAux, "postEventTypeList", ['BLOG_POST', 'FORUM_TOPIC', 'TASK', 'TIMEMAN_ENTRY', 'TIMEMAN_REPORT', 'LOG_ENTRY', 'PHOTO_ALBUM', 'PHOTO_PHOTO', 'WIKI', 'LISTS_NEW_ELEMENT', 'CALENDAR_EVENT', 'INTRANET_NEW_USER', 'BITRIX24_NEW_USER']);
	babelHelpers.defineProperty(CommentAux, "commentEventTypeList", ['BLOG_COMMENT', 'FORUM_POST', 'LOG_COMMENT']);
	babelHelpers.defineProperty(CommentAux, "typesList", {
	  share: 'share',
	  createentity: 'createentity',
	  createtask: 'createtask',
	  fileversion: 'fileversion',
	  taskinfo: 'taskinfo'
	});

	exports.CommentAux = CommentAux;

}((this.BX = this.BX || {}),BX,BX.Event,BX));
//# sourceMappingURL=commentaux.bundle.js.map
