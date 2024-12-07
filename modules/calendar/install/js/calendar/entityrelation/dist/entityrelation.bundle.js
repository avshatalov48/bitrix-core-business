/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,main_loader,im_public,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	class Bar {
	  constructor(options) {
	    this.parentNode = options.parentNode;
	    this.init();
	  }
	  init() {
	    this.bar = main_core.Tag.render(_t || (_t = _`
			<div class="calendar-relation-bar">
			</div>
		`));
	    main_core.Event.bind(this.bar, 'mouseenter', () => {
	      main_core_events.EventEmitter.emit('BX.Calendar.EntityRelation.onMouseEnter');
	    });
	  }
	  renderLoader() {
	    main_core.Dom.clean(this.bar);
	    if (!this.loaderWrap) {
	      this.loaderWrap = main_core.Tag.render(_t2 || (_t2 = _`<div class="calendar-relation-bar-loader"></div>`));
	    }
	    main_core.Dom.append(this.loaderWrap, this.bar);
	    this.showLoader();
	    return this.bar;
	  }
	  showLoader() {
	    if (this.loader) {
	      this.loader.destroy();
	    }
	    this.loader = new main_loader.Loader({
	      target: this.loaderWrap,
	      size: 22,
	      color: '#2066B0',
	      offset: {
	        left: '0px',
	        top: '0px'
	      },
	      mode: 'inline'
	    });
	    this.loader.show();
	  }
	  render(relationData) {
	    main_core.Dom.clean(this.bar);
	    main_core.Dom.append(this.getEntityLink(relationData), this.bar);
	    main_core.Dom.append(this.getOwnerData(relationData), this.bar);
	    return this.bar;
	  }
	  getEntityLink(relationData) {
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<a
				class="calendar-relation-entity-link"
				href="${0}"
				title="${0}"
			>
				<div class="calendar-relation-entity-link-text">
					${0}
				</div>
				<div class="calendar-relation-entity-link-arrow"></div>
			</a>
		`), relationData.entity.link, main_core.Loc.getMessage('CALENDAR_RELATION_OPEN_ENTITY_HINT_DEAL'), main_core.Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_DEAL'));
	  }
	  getOwnerData(relationData) {
	    const {
	      root,
	      chatButton
	    } = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="calendar-relation-owner">
				<div class="calendar-relation-owner-role">${0}</div>
				<div class="calendar-relation-owner-info">
					${0}
					${0}
					<div
						ref="chatButton"
						class="calendar-relation-owner-chat"
						title="${0}"
					/>
				</div>
			</div>
		`), main_core.Loc.getMessage('CALENDAR_RELATION_OWNER_ROLE_DEAL'), this.getOwnerAvatarNode(relationData), this.getOwnerNameNode(relationData), main_core.Loc.getMessage('CALENDAR_RELATION_CHAT_BUTTON_HINT'));
	    main_core.Event.bind(chatButton, 'click', () => this.openChat(relationData.owner.id));
	    return root;
	  }
	  getOwnerAvatarNode(relationData) {
	    const avatarWrap = main_core.Tag.render(_t5 || (_t5 = _`
			<a
				href="${0}"
				class="calendar-relation-owner-avatar ui-icon ui-icon-common-user"
				title="${0}"
			>
			</a>
		`), relationData.owner.link, main_core.Loc.getMessage('CALENDAR_RELATION_OWNER_PROFILE_HINT'));
	    let avatar = null;
	    if (relationData.owner.avatar) {
	      avatar = main_core.Tag.render(_t6 || (_t6 = _`
				<img
					src="${0}"
					alt=""
				/>
			`), encodeURI(relationData.owner.avatar));
	    } else {
	      avatar = main_core.Tag.render(_t7 || (_t7 = _`
				<i></i>
			`));
	    }
	    main_core.Dom.append(avatar, avatarWrap);
	    return avatarWrap;
	  }
	  getOwnerNameNode(relationData) {
	    return main_core.Tag.render(_t8 || (_t8 = _`
			<a
				class="calendar-relation-owner-name"
				href="${0}"
				title="${0}"
			>
				${0}
			</a>
		`), relationData.owner.link, main_core.Loc.getMessage('CALENDAR_RELATION_OWNER_PROFILE_HINT'), relationData.owner.name);
	  }
	  openChat(chatId) {
	    im_public.Messenger.openChat(chatId);
	  }
	}

	class Client {
	  static async getRelationData(eventId) {
	    if (main_core.Type.isNil(eventId)) {
	      return false;
	    }
	    const action = 'calendar.api.calendarentryajax.getEventEntityRelation';
	    const data = {
	      eventId
	    };
	    const response = await main_core.ajax.runAction(action, {
	      data
	    }).then(ajaxResponse => {
	      return ajaxResponse;
	    }, () => {
	      return null;
	    });
	    return (response == null ? void 0 : response.data) || false;
	  }
	}

	class RelationCollection {
	  static getRelation(eventId) {
	    var _RelationCollection$m;
	    return (_RelationCollection$m = RelationCollection.map.get(eventId)) != null ? _RelationCollection$m : false;
	  }
	  static setRelation(relationData) {
	    RelationCollection.map.set(relationData.eventId, relationData);
	  }
	}
	RelationCollection.map = new Map();

	class RelationInterface {
	  constructor(options) {
	    var _options$eventId;
	    this.bar = new Bar({
	      parentNode: options.parentNode
	    });
	    this.eventId = (_options$eventId = options.eventId) != null ? _options$eventId : null;
	    this.relationData = RelationCollection.getRelation(this.eventId) || null;
	    this.layout = null;
	  }
	  render() {
	    if (main_core.Type.isNil(this.relationData)) {
	      this.layout = this.bar.renderLoader();
	      this.showLazy();
	    } else if (this.relationData) {
	      this.layout = this.bar.render(this.relationData);
	    }
	    return this.layout;
	  }
	  async showLazy() {
	    this.relationData = await Client.getRelationData(this.eventId);
	    if (this.relationData) {
	      RelationCollection.setRelation(this.relationData);
	      const barLayout = this.bar.render(this.relationData);
	      main_core.Dom.replace(this.layout, barLayout);
	      this.layout = barLayout;
	    } else {
	      this.destroy();
	    }
	  }
	  destroy() {
	    main_core.Dom.remove(this.layout);
	    this.layout = null;
	  }
	}

	exports.RelationInterface = RelationInterface;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Event,BX,BX.Messenger.v2.Lib,BX));
//# sourceMappingURL=entityrelation.bundle.js.map
