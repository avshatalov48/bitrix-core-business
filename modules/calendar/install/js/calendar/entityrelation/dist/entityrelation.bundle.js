/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,main_loader,im_public,main_core,ui_iconSet_api_core) {
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
	  render(relationData, entityLink) {
	    main_core.Dom.clean(this.bar);
	    main_core.Dom.append(entityLink, this.bar);
	    main_core.Dom.append(this.getOwnerData(relationData), this.bar);
	    return this.bar;
	  }
	  getEntityLink({
	    link = '#',
	    text,
	    title
	  }) {
	    const arrowIcon = new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Outline.CHEVRON_RIGHT_M,
	      size: 20,
	      color: 'rgb(130, 139, 149)'
	    });
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<a
				class="calendar-relation-entity-link"
				href="${0}"
				title="${0}"
			>
				<div class="calendar-relation-entity-link-text">
					${0}
				</div>
				<div class="calendar-relation-entity-link-arrow">
					${0}
				</div>
			</a>
		`), link, title, text, arrowIcon.render());
	  }
	  getOwnerData(relationData) {
	    const chatIcon = new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Outline.MESSAGES,
	      size: 22,
	      color: 'rgb(0, 117, 255)'
	    });
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
					>
						${0}
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('CALENDAR_RELATION_OWNER_ROLE_DEAL'), this.getOwnerAvatarNode(relationData), this.getOwnerNameNode(relationData), main_core.Loc.getMessage('CALENDAR_RELATION_CHAT_BUTTON_HINT'), chatIcon.render());
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

	var _bar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bar");
	var _BookingEventPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("BookingEventPopup");
	var _getEntityLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEntityLink");
	var _openBooking = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openBooking");
	class BookingBar {
	  constructor(bar) {
	    Object.defineProperty(this, _openBooking, {
	      value: _openBooking2
	    });
	    Object.defineProperty(this, _getEntityLink, {
	      value: _getEntityLink2
	    });
	    Object.defineProperty(this, _bar, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _BookingEventPopup, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _bar)[_bar] = bar;
	  }
	  render(relationData) {
	    main_core.Runtime.loadExtension('booking.application.booking-event-popup').then(exports => {
	      babelHelpers.classPrivateFieldLooseBase(this, _BookingEventPopup)[_BookingEventPopup] = exports.BookingEventPopup;
	    }).catch(error => {
	      console.error('Calendar. EntityRelation. Load BookingEventPopup extension error', error);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _bar)[_bar].render(relationData, babelHelpers.classPrivateFieldLooseBase(this, _getEntityLink)[_getEntityLink](relationData));
	  }
	}
	function _getEntityLink2(relationData) {
	  const entityLink = babelHelpers.classPrivateFieldLooseBase(this, _bar)[_bar].getEntityLink({
	    link: relationData.entity.link,
	    text: main_core.Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_BOOKING'),
	    title: main_core.Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_BOOKING')
	  });
	  main_core.Event.bind(entityLink, 'click', async event => {
	    event.preventDefault();
	    await babelHelpers.classPrivateFieldLooseBase(this, _openBooking)[_openBooking](relationData.entity.id);
	  });
	  return entityLink;
	}
	async function _openBooking2(bookingId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _BookingEventPopup)[_BookingEventPopup]) {
	    return;
	  }
	  await new (babelHelpers.classPrivateFieldLooseBase(this, _BookingEventPopup)[_BookingEventPopup])({
	    bookingId
	  }).show();
	}

	var _bar$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bar");
	var _getEntityLink$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEntityLink");
	class DealBar {
	  constructor(bar) {
	    Object.defineProperty(this, _getEntityLink$1, {
	      value: _getEntityLink2$1
	    });
	    Object.defineProperty(this, _bar$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _bar$1)[_bar$1] = bar;
	  }
	  render(relationData) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _bar$1)[_bar$1].render(relationData, babelHelpers.classPrivateFieldLooseBase(this, _getEntityLink$1)[_getEntityLink$1](relationData));
	  }
	}
	function _getEntityLink2$1(relationData) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _bar$1)[_bar$1].getEntityLink({
	    link: relationData.entity.link,
	    text: main_core.Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_DEAL'),
	    title: main_core.Loc.getMessage('CALENDAR_RELATION_OPEN_ENTITY_HINT_DEAL')
	  });
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

	let _$1 = t => t,
	  _t$1;
	const HELP_DESK_CODE = 26423530;
	var _getHelpDeskIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHelpDeskIcon");
	var _showHelpDesk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showHelpDesk");
	class EntityRelationsHeader {
	  constructor() {
	    Object.defineProperty(this, _showHelpDesk, {
	      value: _showHelpDesk2
	    });
	    Object.defineProperty(this, _getHelpDeskIcon, {
	      value: _getHelpDeskIcon2
	    });
	  }
	  render() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar--relation-entities-header">
				<h6 class="calendar--relation-entities-title">
					${0}
				</h6>
				<span class="calendar--relation-entities-help-desk-icon">
					${0}
				</span>
			</div>
		`), main_core.Loc.getMessage('CALENDAR_RELATIONS_TITLE'), babelHelpers.classPrivateFieldLooseBase(this, _getHelpDeskIcon)[_getHelpDeskIcon]());
	  }
	}
	function _getHelpDeskIcon2() {
	  const helpDeskIcon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Outline.QUESTION,
	    size: 16,
	    color: 'rgba(167, 167, 167, 1)'
	  }).render();
	  main_core.Event.bind(helpDeskIcon, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _showHelpDesk)[_showHelpDesk]());
	  return helpDeskIcon;
	}
	function _showHelpDesk2() {
	  if (!top.BX.Helper) {
	    return;
	  }
	  const params = {
	    redirect: 'detail',
	    code: HELP_DESK_CODE
	  };
	  const queryString = Object.entries(params).map(([key, value]) => `${key}=${value}`).join('&');
	  top.BX.Helper.show(queryString);
	}

	let _$2 = t => t,
	  _t$2;
	var _renderBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderBar");
	var _wrapLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapLayout");
	class RelationInterface {
	  constructor(options) {
	    var _options$eventId;
	    Object.defineProperty(this, _wrapLayout, {
	      value: _wrapLayout2
	    });
	    Object.defineProperty(this, _renderBar, {
	      value: _renderBar2
	    });
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
	      void this.showLazy();
	    } else if (this.relationData) {
	      this.layout = babelHelpers.classPrivateFieldLooseBase(this, _renderBar)[_renderBar](this.relationData);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapLayout)[_wrapLayout](this.layout);
	  }
	  async showLazy() {
	    this.relationData = await Client.getRelationData(this.eventId);
	    if (this.relationData) {
	      RelationCollection.setRelation(this.relationData);
	      const barLayout = babelHelpers.classPrivateFieldLooseBase(this, _renderBar)[_renderBar](this.relationData);
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
	function _renderBar2(relationData) {
	  if (relationData.entity.type === 'deal') {
	    return new DealBar(this.bar).render(relationData);
	  }
	  if (relationData.entity.type === 'booking') {
	    return new BookingBar(this.bar).render(relationData);
	  }
	  return null;
	}
	function _wrapLayout2(layout) {
	  if (layout === null) {
	    return layout;
	  }
	  return main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar--relation-entities-wrapper">
				${0}
				${0}
			</div>
		`), new EntityRelationsHeader().render(), this.layout);
	}

	exports.RelationInterface = RelationInterface;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Event,BX,BX.Messenger.v2.Lib,BX,BX.UI.IconSet));
//# sourceMappingURL=entityrelation.bundle.js.map
