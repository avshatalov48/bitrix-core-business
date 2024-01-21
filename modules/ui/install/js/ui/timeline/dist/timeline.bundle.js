/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_loader,ui_dialogs_messagebox,main_popup,main_core_events,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10;

	/**
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */
	class Item {
	  constructor(params) {
	    this.isProgress = false;
	    main_core_events.EventEmitter.makeObservable(this, 'UI.Timeline.Item');
	    this.id = params.id;
	    this.createdTimestamp = null;
	    this.action = '';
	    this.title = '';
	    this.description = '';
	    this.htmlDescription = '';
	    this.textDescription = '';
	    this.userId = params.userId;
	    this.isFixed = params.isFixed === true;
	    this.data = {};
	    this.eventIds = new Set();
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isSet(params.eventIds)) {
	        this.eventIds = params.eventIds;
	      }
	      if (main_core.Type.isString(params.action)) {
	        this.action = params.action;
	      }
	      if (main_core.Type.isString(params.title)) {
	        this.title = params.title;
	      }
	      if (main_core.Type.isString(params.description)) {
	        this.description = params.description;
	      }
	      if (main_core.Type.isString(params.htmlDescription)) {
	        this.htmlDescription = params.htmlDescription;
	      }
	      if (main_core.Type.isString(params.textDescription)) {
	        this.textDescription = params.textDescription;
	      }
	      if (main_core.Type.isNumber(params.createdTimestamp)) {
	        this.createdTimestamp = params.createdTimestamp;
	      }
	      if (main_core.Type.isPlainObject(params.data)) {
	        this.data = params.data;
	      }
	    }
	    this.layout = {};
	    this.timeFormat = 'H:M';
	    this.nameFormat = '';
	    this.users = new Map();
	    this.isLast = false;
	    this.events = params.events;
	    this.isPinned = false;
	  }
	  afterRender() {
	    main_core.Event.bind(this.renderPin(), 'click', this.onPinClick.bind(this));
	    this.bindActionsButtonClick();
	  }
	  bindActionsButtonClick() {
	    const button = this.getActionsButton();
	    if (button) {
	      main_core.Event.bind(button, 'click', this.onActionsButtonClick.bind(this));
	    }
	  }
	  setIsLast(isLast) {
	    this.isLast = isLast;
	    if (this.isRendered()) {
	      if (this.isLast) {
	        this.getContainer().classList.add('ui-item-detail-stream-section-last');
	      } else {
	        this.getContainer().classList.remove('ui-item-detail-stream-section-last');
	      }
	    }
	  }
	  setUserData(users) {
	    if (users) {
	      this.users = users;
	    }
	    return this;
	  }
	  setTimeFormat(timeFormat) {
	    if (main_core.Type.isString(timeFormat)) {
	      this.timeFormat = timeFormat;
	    }
	    return this;
	  }
	  setNameFormat(nameFormat) {
	    if (main_core.Type.isString(nameFormat)) {
	      this.nameFormat = nameFormat;
	    }
	    return this;
	  }
	  getContainer() {
	    return this.layout.container;
	  }
	  isRendered() {
	    return main_core.Type.isDomNode(this.getContainer());
	  }
	  getCreatedTime() {
	    if (this.createdTimestamp > 0) {
	      this.createdTimestamp = main_core.Text.toInteger(this.createdTimestamp);
	      return new Date(this.createdTimestamp);
	    }
	    return null;
	  }
	  formatTime(time) {
	    return BX.date.format(this.timeFormat, time);
	  }
	  getId() {
	    return this.id;
	  }
	  getTitle() {
	    return this.title;
	  }
	  getUserId() {
	    return main_core.Text.toInteger(this.userId);
	  }
	  getScope() {
	    if (main_core.Type.isString(this.data.scope)) {
	      return this.data.scope;
	    }
	    return null;
	  }
	  isScopeManual() {
	    const scope = this.getScope();
	    return !scope || scope === 'manual';
	  }
	  isScopeAutomation() {
	    return this.getScope() === 'automation';
	  }
	  isScopeTask() {
	    return this.getScope() === 'task';
	  }
	  isScopeRest() {
	    return this.getScope() === 'rest';
	  }
	  render() {
	    this.layout.container = this.renderContainer();
	    this.updateLayout();
	    return this.layout.container;
	  }
	  updateLayout() {
	    this.clearLayout(true);
	    this.layout.container.appendChild(this.renderIcon());
	    if (this.hasMenu()) {
	      this.layout.container.appendChild(this.renderActionsButton());
	    }
	    this.layout.container.appendChild(this.renderPin());
	    let content = this.getContent();
	    if (!content) {
	      content = this.renderContent();
	    }
	    this.layout.container.appendChild(content);
	    this.afterRender();
	  }
	  renderContainer() {
	    return main_core.Tag.render(_t || (_t = _`<div class="ui-item-detail-stream-section ${0}"></div>`), this.isLast ? 'ui-item-detail-stream-section-last' : '');
	  }
	  renderPin() {
	    if (!this.layout.pin) {
	      this.layout.pin = main_core.Tag.render(_t2 || (_t2 = _`<span class="ui-item-detail-stream-section-top-fixed-btn"></span>`));
	    }
	    if (this.isFixed) {
	      this.layout.pin.classList.add('ui-item-detail-stream-section-top-fixed-btn-active');
	    } else {
	      this.layout.pin.classList.remove('ui-item-detail-stream-section-top-fixed-btn-active');
	    }
	    return this.layout.pin;
	  }
	  renderContent() {
	    this.layout.content = main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-item-detail-stream-section-content">${0}</div>`), this.renderDescription());
	    return this.getContent();
	  }
	  getContent() {
	    return this.layout.content;
	  }
	  renderDescription() {
	    this.layout.description = main_core.Tag.render(_t4 || (_t4 = _`<div class="ui-item-detail-stream-content-event"></div>`));
	    let header = this.renderHeader();
	    if (header) {
	      this.layout.description.appendChild(header);
	    }
	    this.layout.description.appendChild(this.renderMain());
	    return this.layout.description;
	  }
	  renderHeader() {
	    return null;
	  }
	  renderHeaderUser(userId, size = 21) {
	    userId = main_core.Text.toInteger(userId);
	    let userData = {
	      link: 'javascript: void(0)',
	      fullName: '',
	      photo: null
	    };
	    if (userId > 0) {
	      userData = this.users.get(userId);
	    }
	    if (!userData) {
	      return main_core.Tag.render(_t5 || (_t5 = _`<a></a>`));
	    }
	    const safeFullName = main_core.Tag.safe(_t6 || (_t6 = _`${0}`), userData.fullName);
	    return main_core.Tag.render(_t7 || (_t7 = _`<a class="ui-item-detail-stream-content-employee" href="${0}" target="_blank" title="${0}" ${0}></a>`), userData.link, safeFullName, userData.photo ? 'style="background-image: url(\'' + userData.photo + '\'); background-size: 100%;"' : '');
	  }
	  renderMain() {
	    this.layout.main = main_core.Tag.render(_t8 || (_t8 = _`<div class="ui-item-detail-stream-content-detail">${0}</div>`), this.description);
	    return this.getMain();
	  }
	  getMain() {
	    return this.layout.main;
	  }
	  renderIcon() {
	    this.layout.icon = main_core.Tag.render(_t9 || (_t9 = _`<div class="ui-item-detail-stream-section-icon"></div>`));
	    return this.layout.icon;
	  }
	  getItem() {
	    if (main_core.Type.isPlainObject(this.data.item)) {
	      return this.data.item;
	    }
	    return null;
	  }
	  onPinClick() {
	    this.isFixed = !this.isFixed;
	    this.renderPin();
	    if (main_core.Type.isFunction(this.events.onPinClick)) {
	      this.events.onPinClick(this);
	    }
	    this.emit('onPinClick');
	  }
	  clearLayout(isSkipContainer = false) {
	    const container = this.getContainer();
	    Object.keys(this.layout).forEach(name => {
	      const node = this.layout[name];
	      if (!isSkipContainer || container !== node) {
	        main_core.Dom.remove(node);
	        delete this.layout[name];
	      }
	    });
	    return this;
	  }
	  getDataForUpdate() {
	    return {
	      description: this.description,
	      htmlDescription: this.htmlDescription,
	      data: this.data,
	      userId: this.userId
	    };
	  }
	  updateData(params) {
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.description)) {
	        this.description = params.description;
	      }
	      if (main_core.Type.isString(params.htmlDescription)) {
	        this.htmlDescription = params.htmlDescription;
	      }
	      if (main_core.Type.isPlainObject(params.data)) {
	        this.data = params.data;
	      }
	      if (params.userId > 0) {
	        this.userId = params.userId;
	      }
	    }
	    return this;
	  }
	  update(params) {
	    this.updateData(params).updateLayout();
	    return this;
	  }
	  onError(params) {
	    if (main_core.Type.isFunction(this.events.onError)) {
	      this.events.onError(params);
	    }
	    this.emit('error', params);
	  }
	  onDelete() {
	    if (main_core.Type.isFunction(this.events.onDelete)) {
	      this.events.onDelete(this);
	    }
	    this.emit('onDeleteComplete');
	  }
	  hasMenu() {
	    return this.hasActions();
	  }
	  hasActions() {
	    return this.getActions().length > 0;
	  }
	  getActions() {
	    return [];
	  }
	  renderActionsButton() {
	    this.layout.contextMenuButton = main_core.Tag.render(_t10 || (_t10 = _`<div class="ui-timeline-item-context-menu"></div>`));
	    return this.getActionsButton();
	  }
	  getActionsButton() {
	    return this.layout.contextMenuButton;
	  }
	  getActionsMenuId() {
	    return 'ui-timeline-item-context-menu-' + this.getId();
	  }
	  onActionsButtonClick() {
	    this.getActionsMenu().toggle();
	  }
	  getActionsMenu() {
	    return main_popup.MenuManager.create({
	      id: this.getActionsMenuId(),
	      bindElement: this.getActionsButton(),
	      items: this.getActions(),
	      offsetTop: 0,
	      offsetLeft: 16,
	      angle: {
	        position: "top",
	        offset: 0
	      },
	      events: {
	        onPopupShow: this.onContextMenuShow.bind(this),
	        onPopupClose: this.onContextMenuClose.bind(this)
	      }
	    });
	  }
	  onContextMenuShow() {
	    this.getActionsButton().classList.add('active');
	  }
	  onContextMenuClose() {
	    this.getActionsButton().classList.remove('active');
	    this.getActionsMenu().destroy();
	  }
	  startProgress() {
	    this.isProgress = true;
	    this.getLoader().show();
	  }
	  stopProgress() {
	    this.isProgress = false;
	    if (this.getLoader().isShown()) {
	      this.getLoader().hide();
	    }
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        target: this.getContainer()
	      });
	    }
	    return this.loader;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1;
	class History extends Item {
	  renderContainer() {
	    const container = super.renderContainer();
	    if (this.isScopeAutomation()) {
	      container.classList.add('ui-item-detail-stream-section-icon-robot');
	    } else {
	      container.classList.add('ui-item-detail-stream-section-info');
	    }
	    return container;
	  }
	  renderHeader() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="ui-item-detail-stream-content-header">
			<div class="ui-item-detail-stream-content-title">
				<span class="ui-item-detail-stream-content-title-text">${0}</span>
				<span class="ui-item-detail-stream-content-title-time">${0}</span>
			</div>
			${0}
		</div>`), main_core.Text.encode(this.getTitle()), this.formatTime(this.getCreatedTime()), this.renderHeaderUser(this.getUserId()));
	  }
	  renderStageChangeTitle() {
	    return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<div class="ui-item-detail-stream-content-title">
			<span class="ui-item-detail-stream-content-title-text">${0}</span>
		</div>`), main_core.Loc.getMessage('UI_TIMELINE_STAGE_CHANGE_SUBTITLE'));
	  }
	  renderStageChange() {
	    const stageFrom = this.getStageFrom();
	    const stageTo = this.getStageTo();
	    if (stageFrom && stageTo && stageFrom.id !== stageTo.id) {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<div class="ui-item-detail-stream-content-detail-info">
				<span class="ui-item-detail-stream-content-detail-info-status">${0}</span>
				<span class="ui-item-detail-stream-content-detail-info-separator"></span>
				<span class="ui-item-detail-stream-content-detail-info-status">${0}</span>
			</div>`), main_core.Text.encode(stageFrom.name), main_core.Text.encode(stageTo.name));
	    }
	    return null;
	  }
	  getStageFrom() {
	    if (main_core.Type.isPlainObject(this.data.stageFrom)) {
	      return this.data.stageFrom;
	    }
	    return null;
	  }
	  getStageTo() {
	    if (main_core.Type.isPlainObject(this.data.stageTo)) {
	      return this.data.stageTo;
	    }
	    return null;
	  }
	  getFields() {
	    if (main_core.Type.isArray(this.data.fields)) {
	      return this.data.fields;
	    }
	    return null;
	  }
	  renderFieldsChange() {
	    const fields = this.getFields();
	    if (fields) {
	      const list = [];
	      fields.forEach(field => {
	        list.push(main_core.Tag.render(_t4$1 || (_t4$1 = _$1`<div class="ui-item-detail-stream-content-detail-field">${0}</div>`), main_core.Text.encode(field.title)));
	      });
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$1`<div class="ui-item-detail-stream-content-detail-info ui-item-detail-stream-content-detail-info-break">
				${0}
			</div>`), list);
	    }
	    return null;
	  }
	  renderFieldsChangeTitle() {
	    return main_core.Tag.render(_t6$1 || (_t6$1 = _$1`<div class="ui-item-detail-stream-content-title">
			<span class="ui-item-detail-stream-content-title-text">${0}</span>
		</div>`), main_core.Loc.getMessage('UI_TIMELINE_FIELDS_CHANGE_SUBTITLE'));
	  }
	}

	let _$2 = t => t,
	  _t$2;
	class StageChange extends History {
	  renderMain() {
	    let stageChange = this.renderStageChange();
	    if (!stageChange) {
	      stageChange = '';
	    }
	    let fieldsChange = this.renderFieldsChange();
	    if (!fieldsChange) {
	      fieldsChange = '';
	    }
	    return main_core.Tag.render(_t$2 || (_t$2 = _$2`<div class="ui-item-detail-stream-content-detail">
			${0}
			${0}
		</div>`), stageChange, fieldsChange);
	  }
	}

	let _$3 = t => t,
	  _t$3;
	class FieldsChange extends History {
	  renderMain() {
	    let fieldsChange = this.renderFieldsChange();
	    if (!fieldsChange) {
	      fieldsChange = '';
	    }
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`<div class="ui-item-detail-stream-content-detail">
			${0}
		</div>`), fieldsChange);
	  }
	}

	/**
	 * @abstract
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */
	class Editor {
	  constructor(params) {
	    this.isProgress = false;
	    if (main_core.Type.isString(params.id) && params.id.length > 0) {
	      this.id = params.id;
	    } else {
	      this.id = main_core.Text.getRandom();
	    }
	    this.layout = {};
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.Timeline.Editor');
	  }
	  getId() {
	    return this.id;
	  }
	  getTitle() {}
	  getContainer() {
	    return this.layout.container;
	  }
	  render() {
	    throw new Error('This method should be overridden');
	  }
	  clearLayout(isSkipContainer = false) {
	    const container = this.getContainer();
	    Object.keys(this.layout).forEach(name => {
	      const node = this.layout[name];
	      if (!isSkipContainer || container !== node) {
	        main_core.Dom.clean(node);
	        delete this.layout[name];
	      }
	    });
	    return this;
	  }
	  startProgress() {
	    this.isProgress = true;
	    this.getLoader().show();
	  }
	  stopProgress() {
	    this.isProgress = false;
	    if (this.getLoader().isShown()) {
	      this.getLoader().hide();
	    }
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        target: this.getContainer()
	      });
	    }
	    return this.loader;
	  }
	  isRendered() {
	    return main_core.Type.isDomNode(this.getContainer());
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$2,
	  _t7$1;

	/**
	 * @memberOf BX.UI.Timeline
	 * @mixes EventEmitter
	 */
	class CommentEditor extends Editor {
	  constructor(params) {
	    super(params);
	    this.commentId = 0;
	    this.editorContent = null;
	    if (main_core.Type.isNumber(params.commentId)) {
	      this.commentId = params.commentId;
	    }
	    this.setEventNamespace('BX.UI.Timeline.CommentEditor');
	  }
	  getTitle() {
	    return main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT');
	  }
	  getVisualEditorName() {
	    return 'UiTimelineCommentVisualEditor' + this.getId().replace('- ', '');
	  }
	  getTextarea() {
	    return this.layout.textarea;
	  }
	  renderTextarea() {
	    this.layout.textarea = main_core.Tag.render(_t$4 || (_t$4 = _$4`<textarea onfocus="${0}" rows="1" class="ui-item-detail-stream-section-new-comment-textarea" placeholder="${0}"></textarea>`), this.onFocus.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_TEXTAREA'));
	    return this.getTextarea();
	  }
	  getVisualEditorContainer() {
	    return this.layout.visualEditorContainer;
	  }
	  renderVisualEditorContainer() {
	    this.layout.visualEditorContainer = main_core.Tag.render(_t2$2 || (_t2$2 = _$4`<div class="ui-timeline-comment-visual-editor"></div>`));
	    return this.getVisualEditorContainer();
	  }
	  getButtonsContainer() {
	    return this.layout.buttonsContainer;
	  }
	  renderButtons() {
	    this.layout.buttonsContainer = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`<div class="ui-item-detail-stream-section-new-comment-btn-container">
			${0}
			${0}
		</div>`), this.renderSaveButton(), this.renderCancelButton());
	    return this.getButtonsContainer();
	  }
	  getSaveButton() {
	    return this.layout.saveButton;
	  }
	  renderSaveButton() {
	    this.layout.saveButton = main_core.Tag.render(_t4$2 || (_t4$2 = _$4`<button onclick="${0}" class="ui-btn ui-btn-xs ui-btn-primary">${0}</button>`), this.save.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_SEND'));
	    return this.getSaveButton();
	  }
	  getCancelButton() {
	    return this.layout.cancelButton;
	  }
	  renderCancelButton() {
	    this.layout.cancelButton = main_core.Tag.render(_t5$2 || (_t5$2 = _$4`<span onclick="${0}" class="ui-btn ui-btn-xs ui-btn-link">${0}</span>`), this.cancel.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_CANCEL'));
	    return this.getCancelButton();
	  }
	  render() {
	    this.layout.container = main_core.Tag.render(_t6$2 || (_t6$2 = _$4`<div class="ui-timeline-comment-editor">
				${0}
				${0}
				${0}
			</div>`), this.renderTextarea(), this.renderButtons(), this.renderVisualEditorContainer());
	    return this.getContainer();
	  }
	  onFocus() {
	    const container = this.getContainer();
	    if (container) {
	      container.classList.add('focus');
	    }
	    this.showVisualEditor();
	  }
	  showVisualEditor() {
	    if (!this.getVisualEditorContainer()) {
	      return;
	    }
	    if (this.postForm && this.visualEditor) {
	      this.postForm.eventNode.style.display = 'block';
	      this.visualEditor.Focus();
	    } else if (!this.isProgress) {
	      this.loadVisualEditor().then(() => {
	        main_core_events.EventEmitter.emit(this.postForm.eventNode, 'OnShowLHE', [true]);
	        //todo there should be some other way
	        setTimeout(() => {
	          this.editorContent = this.postForm.oEditor.GetContent();
	        }, 300);
	      }).catch(() => {
	        this.cancel();
	        this.emit('error', {
	          message: 'Could not load visual editor. Please try again later'
	        });
	      });
	    }
	  }
	  loadVisualEditor() {
	    return new Promise((resolve, reject) => {
	      if (this.isProgress) {
	        reject();
	      }
	      this.showEditorLoader();
	      const event = new main_core_events.BaseEvent({
	        data: {
	          name: this.getVisualEditorName(),
	          commentId: this.commentId
	        }
	      });
	      this.emitAsync('onLoadVisualEditor', event).then(() => {
	        const html = event.getData().html;
	        if (main_core.Type.isString(html)) {
	          main_core.Runtime.html(this.getVisualEditorContainer(), html).then(() => {
	            this.hideEditorLoader();
	            if (LHEPostForm && BXHtmlEditor) {
	              this.postForm = LHEPostForm.getHandler(this.getVisualEditorName());
	              this.visualEditor = BXHtmlEditor.Get(this.getVisualEditorName());
	              resolve();
	            } else {
	              reject();
	            }
	          });
	        } else {
	          reject();
	        }
	      }).catch(() => {
	        reject();
	      });
	    });
	  }
	  showEditorLoader() {
	    this.editorLoader = main_core.Tag.render(_t7$1 || (_t7$1 = _$4`<div class="ui-timeline-wait"></div>`));
	    main_core.Dom.append(this.editorLoader, this.getContainer());
	  }
	  hideEditorLoader() {
	    main_core.Dom.remove(this.editorLoader);
	  }
	  hideVisualEditor() {
	    if (this.postForm) {
	      this.postForm.eventNode.style.display = 'none';
	    }
	  }
	  save() {
	    if (this.isProgress || !this.postForm) {
	      return;
	    }
	    let isCancel = false;
	    const description = this.postForm.oEditor.GetContent();
	    this.editorContent = description;
	    const files = this.getAttachments();
	    this.emit('beforeSave', {
	      description,
	      isCancel,
	      files
	    });
	    if (description === '') {
	      this.getEmptyMessageNotification().show();
	      return;
	    }
	    this.startProgress();
	    const event = new main_core_events.BaseEvent({
	      data: {
	        description,
	        files,
	        commentId: this.commentId
	      }
	    });
	    this.emitAsync('onSave', event).then(() => {
	      this.postForm.reinit();
	      this.stopProgress();
	      this.emit('afterSave', {
	        data: event.getData()
	      });
	      this.cancel();
	    }).catch(() => {
	      //todo why are we here?
	      this.stopProgress();
	      this.cancel();
	      const message = event.getData().message;
	      if (message) {
	        this.emit('error', {
	          message
	        });
	      }
	    });
	  }
	  cancel() {
	    this.hideVisualEditor();
	    const container = this.getContainer();
	    if (container) {
	      container.classList.remove('focus');
	    }
	    this.stopProgress();
	    this.emit('cancel');
	  }
	  getEmptyMessageNotification() {
	    if (!this.emptyMessagePopup) {
	      this.emptyMessagePopup = new main_popup.Popup({
	        id: this.getId() + '-empty-message-popup',
	        bindElement: this.getSaveButton(),
	        content: BX.message('UI_TIMELINE_EMPTY_COMMENT_NOTIFICATION'),
	        darkMode: true,
	        autoHide: true,
	        zIndex: 990,
	        angle: {
	          position: 'top',
	          offset: 77
	        },
	        closeByEsc: true,
	        bindOptions: {
	          forceBindPosition: true
	        }
	      });
	    }
	    return this.emptyMessagePopup;
	  }
	  refresh() {
	    if (this.postForm && this.postForm.oEditor) {
	      if (this.editorContent) {
	        this.postForm.oEditor.SetContent(this.editorContent);
	      }
	    }
	    if (this.visualEditor) {
	      this.visualEditor.ReInitIframe();
	    }
	  }
	  getAttachments() {
	    const attachments = [];
	    if (!this.postForm || !main_core.Type.isPlainObject(this.postForm.arFiles) || !main_core.Type.isPlainObject(this.postForm.controllers)) {
	      return attachments;
	    }
	    const fileControllers = [];
	    Object.values(this.postForm.arFiles).forEach(controller => {
	      if (!fileControllers.includes(controller)) {
	        fileControllers.push(controller);
	      }
	    });
	    fileControllers.forEach(fileController => {
	      if (this.postForm.controllers[fileController] && main_core.Type.isPlainObject(this.postForm.controllers[fileController].values)) {
	        Object.keys(this.postForm.controllers[fileController].values).forEach(fileId => {
	          if (!attachments.includes(fileId)) {
	            attachments.push(fileId);
	          }
	        });
	      }
	    });
	    return attachments;
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$3;
	const COLLAPSE_TEXT_MAX_LENGTH = 128;

	/**
	 * @memberOf BX.UI.Timeline
	 * @mixes EventEmitter
	 */
	class Comment extends History {
	  constructor(props) {
	    super(props);
	    this.isCollapsed = null;
	    this.isContentLoaded = null;
	    this.setEventNamespace('BX.UI.Timeline.Comment');
	  }
	  afterRender() {
	    super.afterRender();
	    if (this.isCollapsed === null) {
	      this.isCollapsed = this.isAddExpandBlock();
	    }
	    if (this.isContentLoaded === null) {
	      this.isContentLoaded = !this.hasFiles();
	    }
	    if (this.isCollapsed) {
	      this.getMain().classList.add('ui-timeline-content-description-collapsed');
	      this.getMain().classList.remove('ui-timeline-content-description-expand');
	    } else {
	      this.getMain().classList.remove('ui-timeline-content-description-collapsed');
	      this.getMain().classList.add('ui-timeline-content-description-expand');
	    }
	    if (this.isAddExpandBlock()) {
	      this.getMainDescription().appendChild(this.renderExpandBlock());
	    }
	    if (this.hasFiles()) {
	      this.getContent().appendChild(main_core.Tag.render(_t$5 || (_t$5 = _$5`<div class="ui-timeline-section-files">${0}</div>`), this.renderFilesContainer()));
	      main_core.Event.ready(() => {
	        setTimeout(() => {
	          this.loadFilesContent();
	        }, 100);
	      });
	    }
	  }
	  getFiles() {
	    if (main_core.Type.isArray(this.data.files)) {
	      return this.data.files;
	    }
	    return [];
	  }
	  hasFiles() {
	    return this.getFiles().length > 0;
	  }
	  isAddExpandBlock() {
	    return this.textDescription.length > COLLAPSE_TEXT_MAX_LENGTH || this.hasFiles();
	  }
	  renderContainer() {
	    const container = super.renderContainer();
	    container.classList.add('ui-item-detail-stream-section-comment');
	    container.classList.remove('ui-item-detail-stream-section-info');
	    return container;
	  }
	  renderMain() {
	    this.layout.main = main_core.Tag.render(_t2$3 || (_t2$3 = _$5`<div class="ui-item-detail-stream-content-detail">
			${0}
		</div>`), this.renderMainDescription());
	    return this.getMain();
	  }
	  getMain() {
	    return this.layout.main;
	  }
	  renderMainDescription() {
	    this.layout.mainDescription = main_core.Tag.render(_t3$3 || (_t3$3 = _$5`<div class="ui-item-detail-stream-content-description" onclick="${0}">${0}</div>`), this.onMainClick.bind(this), this.htmlDescription);
	    return this.getMainDescription();
	  }
	  getMainDescription() {
	    return this.layout.mainDescription;
	  }
	  renderExpandBlock() {
	    this.layout.expandBlock = main_core.Tag.render(_t4$3 || (_t4$3 = _$5`<div class="ui-timeline-content-description-expand-container">${0}</div>`), this.renderExpandButton());
	    return this.getExpandBlock();
	  }
	  getExpandBlock() {
	    return this.layout.expandBlock;
	  }
	  renderExpandButton() {
	    this.layout.expandButton = main_core.Tag.render(_t5$3 || (_t5$3 = _$5`<a class="ui-timeline-content-description-expand-btn" onclick="${0}">
			${0}
		</a>`), this.onExpandButtonClick.bind(this), main_core.Loc.getMessage(this.isCollapsed ? 'UI_TIMELINE_EXPAND_SM' : 'UI_TIMELINE_COLLAPSE_SM'));
	    return this.getExpandButton();
	  }
	  getExpandButton() {
	    return this.layout.expandButton;
	  }
	  getCommendEditor() {
	    if (!this.commentEditor) {
	      this.commentEditor = new CommentEditor({
	        commentId: this.getId(),
	        id: 'UICommentEditor' + this.getId() + (this.isPinned ? 'pinned' : '') + main_core.Text.getRandom()
	      });
	      this.commentEditor.layout.container = this.getContainer();
	      this.commentEditor.subscribe('cancel', this.switchToViewMode.bind(this));
	      this.commentEditor.subscribe('afterSave', this.onSaveComment.bind(this));
	    }
	    return this.commentEditor;
	  }
	  getEditorContainer() {
	    return this.layout.editorContainer;
	  }
	  renderEditorContainer() {
	    const editorContainer = this.getCommendEditor().getVisualEditorContainer();
	    if (editorContainer) {
	      this.layout.editorContainer = editorContainer;
	    } else {
	      this.layout.editorContainer = this.getCommendEditor().renderVisualEditorContainer();
	    }
	    return this.getEditorContainer();
	  }
	  getEditorButtons() {
	    return this.layout.editorButtons;
	  }
	  renderEditorButtons() {
	    this.layout.editorButtons = this.getCommendEditor().renderButtons();
	    return this.getEditorButtons();
	  }
	  renderFilesContainer() {
	    this.layout.filesContainer = main_core.Tag.render(_t6$3 || (_t6$3 = _$5`<div class="ui-timeline-section-files-inner"></div>`));
	    return this.getFilesContainer();
	  }
	  getFilesContainer() {
	    return this.layout.filesContainer;
	  }
	  switchToEditMode() {
	    if (!this.isRendered()) {
	      return;
	    }
	    if (!this.getEditorContainer()) {
	      this.getMain().appendChild(this.renderEditorContainer());
	      this.getMain().appendChild(this.renderEditorButtons());
	    } else {
	      this.getCommendEditor().refresh();
	    }
	    this.getContent().classList.add('ui-item-detail-comment-edit');
	    this.getCommendEditor().showVisualEditor();
	  }
	  switchToViewMode() {
	    this.getContent().classList.remove('ui-item-detail-comment-edit');
	    this.getCommendEditor().hideVisualEditor();
	  }
	  getActions() {
	    return [{
	      text: main_core.Loc.getMessage('UI_TIMELINE_ACTION_MODIFY'),
	      onclick: this.actionEdit.bind(this)
	    }, {
	      text: main_core.Loc.getMessage('UI_TIMELINE_ACTION_DELETE'),
	      onclick: this.actionDelete.bind(this)
	    }];
	  }
	  actionEdit() {
	    this.getActionsMenu().close();
	    this.switchToEditMode();
	  }
	  actionDelete() {
	    this.getActionsMenu().close();
	    ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('UI_TIMELINE_COMMENT_DELETE_CONFIRM'), () => {
	      return new Promise(resolve => {
	        if (this.isProgress) {
	          return;
	        }
	        this.startProgress();
	        const event = new main_core_events.BaseEvent({
	          data: {
	            commentId: this.getId()
	          }
	        });
	        this.emitAsync('onDelete', event).then(() => {
	          this.stopProgress();
	          this.onDelete();
	          resolve();
	        }).catch(() => {
	          this.stopProgress();
	          const message = event.getData().message;
	          if (message) {
	            this.emit('error', {
	              message
	            });
	          }
	          resolve();
	        });
	      });
	    });
	  }
	  clearLayout(isSkipContainer = false) {
	    this.commentEditor = null;
	    return super.clearLayout(isSkipContainer);
	  }
	  onSaveComment(event) {
	    const data = event.getData();
	    if (data.data && data.data.comment) {
	      this.update(data.data.comment);
	    }
	  }
	  onMainClick({
	    target
	  }) {
	    if (main_core.Type.isDomNode(target)) {
	      const tagName = target.tagName.toLowerCase();
	      if (tagName === 'a' || tagName === 'img' || main_core.Dom.hasClass(target, 'feed-con-file-changes-link-more') || main_core.Dom.hasClass(target, 'feed-com-file-inline') || document.getSelection().toString().length > 0) {
	        return;
	      }
	    }
	    this.switchToEditMode();
	  }
	  onExpandButtonClick(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    if (!this.isRendered()) {
	      return;
	    }
	    if (this.isCollapsed === true) {
	      this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + 130 + "px";
	      this.getMain().classList.remove('ui-timeline-content-description-collapsed');
	      this.getMain().classList.add('ui-timeline-content-description-expand');
	      setTimeout(() => {
	        this.getExpandBlock().style.maxHeight = "";
	      }, 300);
	      this.getExpandButton().innerText = main_core.Loc.getMessage('UI_TIMELINE_COLLAPSE_SM');
	      if (!this.isContentLoaded) {
	        this.isContentLoaded = true;
	        this.loadContent();
	      }
	      this.isCollapsed = false;
	    } else if (this.isCollapsed === false) {
	      this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + "px";
	      this.getMain().classList.add('ui-timeline-content-description-collapsed');
	      this.getMain().classList.remove('ui-timeline-content-description-expand');
	      setTimeout(() => {
	        this.getExpandBlock().style.maxHeight = "";
	      }, 0);
	      this.getExpandButton().innerText = main_core.Loc.getMessage('UI_TIMELINE_EXPAND_SM');
	      this.isCollapsed = true;
	    }
	  }
	  loadFilesContent() {
	    if (this.isProgress) {
	      return;
	    }
	    this.startProgress();
	    const event = new main_core_events.BaseEvent({
	      data: {
	        commentId: this.getId()
	      }
	    });
	    this.emitAsync('onLoadFilesContent', event).then(() => {
	      this.stopProgress();
	      const html = event.getData().html;
	      if (main_core.Type.isString(html)) {
	        main_core.Runtime.html(this.getFilesContainer(), html);
	      }
	    }).catch(() => {
	      this.stopProgress();
	      const message = event.getData().message;
	      if (message) {
	        this.emit('error', {
	          message
	        });
	      }
	    });
	  }
	  loadContent() {
	    if (this.isProgress) {
	      return;
	    }
	    this.startProgress();
	    const event = new main_core_events.BaseEvent({
	      data: {
	        commentId: this.getId()
	      }
	    });
	    this.emitAsync('onLoadContent', event).then(() => {
	      this.stopProgress();
	      const comment = event.getData().comment;
	      if (comment && main_core.Type.isString(comment.htmlDescription)) {
	        main_core.Runtime.html(this.getMainDescription(), comment.htmlDescription);
	        if (this.isAddExpandBlock()) {
	          this.getMainDescription().appendChild(this.getExpandBlock());
	        }
	        this.updateData(comment);
	      }
	    }).catch(() => {
	      this.stopProgress();
	      const message = event.getData().message;
	      if (message) {
	        this.emit('error', {
	          message
	        });
	      }
	    });
	  }
	}

	/**
	 * @abstract
	 */
	class Animation {
	  start() {}
	  finish(node, onFinish) {}
	}

	let _$6 = t => t,
	  _t$6;
	class Drop extends Animation {
	  constructor(params) {
	    super(params);
	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.container)) {
	        this.item = params.item;
	        this.container = params.container;
	        this.insertAfter = params.insertAfter;
	      }
	    }
	  }
	  start() {
	    const timeout = Drop.DEFAULT_TIMEOUT;
	    return new Promise(resolve => {
	      if (!this.item || !this.container) {
	        resolve();
	      }
	      setTimeout(() => {
	        this.createGhost(this.item.render(), resolve);
	      }, timeout);
	    });
	  }
	  createGhost(node, onFinish) {
	    node.style.position = "absolute";
	    node.style.width = this.container.offsetWidth + "px";
	    node.style.top = main_core.Dom.getPosition(this.container).top + "px";
	    node.style.left = main_core.Dom.getPosition(this.container).left + "px";
	    document.body.appendChild(node);
	    this.anchor = main_core.Tag.render(_t$6 || (_t$6 = _$6`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-shadow"></div>`));
	    main_core.Dom.prepend(this.anchor, this.container);
	    if (main_core.Type.isDomNode(this.insertAfter)) {
	      main_core.Dom.insertAfter(this.anchor, this.insertAfter);
	    }
	    this.moveGhost(node, onFinish);
	  }
	  moveGhost(node, onFinish) {
	    const anchorPosition = main_core.Dom.getPosition(this.anchor);
	    const startPosition = main_core.Dom.getPosition(this.container);
	    const movingEvent = new BX.easing({
	      duration: Drop.DURATION,
	      start: {
	        top: startPosition.top,
	        height: 0
	      },
	      finish: {
	        top: anchorPosition.top - 5,
	        height: main_core.Dom.getPosition(node).height
	      },
	      transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	      step: state => {
	        node.style.top = state.top + "px";
	        this.anchor.style.height = state.height + "px";
	      },
	      complete: () => {
	        this.finish(node, onFinish);
	      }
	    });
	    movingEvent.animate();
	  }
	  finish(node, onFinish) {
	    node.style.position = "";
	    node.style.width = "";
	    node.style.height = "";
	    node.style.top = "";
	    node.style.left = "";
	    node.style.opacity = "";
	    main_core.Dom.insertAfter(node, this.anchor);
	    main_core.Dom.remove(this.anchor);
	    this.anchor = null;
	    if (main_core.Type.isFunction(onFinish)) {
	      onFinish();
	    }
	  }
	}
	Drop.DEFAULT_TIMEOUT = 150;
	Drop.DURATION = 1200;

	class Pin extends Animation {
	  constructor(params) {
	    super(params);
	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.anchor)) {
	        this.item = params.item;
	        this.anchor = params.anchor;
	        this.startPosition = params.startPosition;
	      }
	    }
	  }
	  start() {
	    return new Promise(resolve => {
	      if (!this.item || !this.anchor) {
	        resolve();
	      }
	      this.node = this.item.render();
	      main_core.Dom.addClass(this.node, 'ui-item-detail-stream-section-top-fixed');
	      this.node.style.position = "absolute";
	      this.node.style.width = this.startPosition.width + "px";
	      let _cloneHeight = this.startPosition.height;
	      const _minHeight = 65;
	      const _sumPaddingContent = 18;
	      if (_cloneHeight < _sumPaddingContent + _minHeight) _cloneHeight = _sumPaddingContent + _minHeight;
	      this.node.style.height = _cloneHeight + "px";
	      this.node.style.top = this.startPosition.top + "px";
	      this.node.style.left = this.startPosition.left + "px";
	      this.node.style.zIndex = 960;
	      document.body.appendChild(this.node);
	      this._anchorPosition = main_core.Dom.getPosition(this.anchor);
	      const finish = {
	        top: this._anchorPosition.top,
	        height: _cloneHeight + 15,
	        opacity: 1
	      };
	      const _difference = this.startPosition.top - this._anchorPosition.bottom;
	      const _deepHistoryLimit = 2 * (document.body.clientHeight + this.startPosition.height);
	      if (_difference > _deepHistoryLimit) {
	        finish.top = this.startPosition.top - _deepHistoryLimit;
	        finish.opacity = 0;
	      }
	      let _duration = Math.abs(finish.top - this.startPosition.top) * 2;
	      _duration = _duration < Pin.DURATION ? Pin.DURATION : _duration;
	      const movingEvent = new BX.easing({
	        duration: _duration,
	        start: {
	          top: this.startPosition.top,
	          height: 0,
	          opacity: 1
	        },
	        finish: finish,
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          this.node.style.top = state.top + "px";
	          this.node.style.opacity = state.opacity;
	          this.anchor.style.height = state.height + "px";
	        },
	        complete: () => {
	          this.finish(this.node, resolve);
	        }
	      });
	      movingEvent.animate();
	    });
	  }
	  finish(node, onFinish) {
	    node.style.position = "";
	    node.style.width = "";
	    node.style.height = "";
	    node.style.top = "";
	    node.style.left = "";
	    node.style.zIndex = "";
	    this.anchor.style.height = 0;
	    main_core.Dom.insertAfter(node, this.anchor);
	    if (main_core.Type.isFunction(onFinish)) {
	      onFinish();
	    }
	  }
	}
	Pin.DURATION = 1500;

	class Show extends Animation {
	  constructor(params) {
	    super(params);
	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.container) && main_core.Type.isDomNode(params.insertAfter)) {
	        this.item = params.item;
	        this.container = params.container;
	        this.insertAfter = params.insertAfter;
	      }
	    }
	  }
	  start() {
	    return new Promise(resolve => {
	      if (!this.item || !this.container || !this.insertAfter) {
	        resolve();
	      }
	      main_core.Dom.insertAfter(this.item.render(), this.insertAfter);
	      this.expand().then(() => {
	        this.fadeIn().then(() => {
	          this.finish(this.item.getContainer(), resolve);
	        });
	      });
	    });
	  }
	  expand() {
	    return new Promise(resolve => {
	      const node = this.item.getContainer();
	      const position = main_core.Dom.getPosition(node);
	      node.style.height = 0;
	      node.style.opacity = 0;
	      node.style.overflow = 'hidden';
	      const show = new BX.easing({
	        duration: Show.EXPAND_DURATION,
	        start: {
	          height: 0
	        },
	        finish: {
	          height: position.height
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          node.style.height = state.height + 'px';
	        },
	        complete: resolve
	      });
	      show.animate();
	    });
	  }
	  fadeIn() {
	    return new Promise(resolve => {
	      this.item.getContainer().style.overflow = '';
	      const fadeIn = new BX.easing({
	        duration: Show.FADE_IN_DURATION,
	        start: {
	          opacity: 0
	        },
	        finish: {
	          opacity: 100
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          this.item.getContainer().style.opacity = state.opacity / 100;
	        },
	        complete: resolve
	      });
	      fadeIn.animate();
	    });
	  }
	  finish(node, onFinish) {
	    this.item.getContainer().style.height = "";
	    this.item.getContainer().style.opacity = "";
	    if (main_core.Type.isFunction(onFinish)) {
	      onFinish();
	    }
	  }
	}
	Show.EXPAND_DURATION = 150;
	Show.FADE_IN_DURATION = 150;

	let _$7 = t => t,
	  _t$7;
	class TaskComplete extends Animation {
	  constructor(params) {
	    super(params);
	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && params.task instanceof Item && main_core.Type.isDomNode(params.insertAfter)) {
	        this.item = params.item;
	        this.task = params.task;
	        this.insertAfter = params.insertAfter;
	      }
	    }
	  }
	  start() {
	    return new Promise(resolve => {
	      if (!this.item || !this.task || !this.container || !this.insertAfter) {
	        resolve();
	      }
	      const node = this.item.render();
	      const taskNode = this.task.getContainer();
	      const startPosition = main_core.Dom.getPosition(taskNode);
	      node.style.position = "absolute";
	      node.style.width = taskNode.offsetWidth + "px";
	      node.style.top = startPosition.top + "px";
	      node.style.left = startPosition.left + "px";
	      node.style.zIndex = "999";
	      main_core.Dom.addClass(node, 'ui-item-detail-stream-section-show');
	      document.body.appendChild(node);
	      this.anchor = main_core.Tag.render(_t$7 || (_t$7 = _$7`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-shadow"></div>`));
	      main_core.Dom.prepend(this.anchor, this.container);
	      if (main_core.Type.isDomNode(this.insertAfter)) {
	        main_core.Dom.insertAfter(this.anchor, this.insertAfter);
	      }
	      taskNode.style.height = taskNode.offsetHeight + 'px';
	      main_core.Dom.addClass(taskNode, 'ui-item-detail-stream-section-hide');
	      setTimeout(function () {
	        const taskHeight = taskNode.offsetHeight;
	        this.anchor.style.height = taskHeight + "px";
	        main_core.Dom.remove(taskNode);
	        main_core.Dom.removeClass(node, 'ui-item-detail-stream-section-show');
	        const movingEvent = new BX.easing({
	          duration: 800,
	          start: {
	            top: main_core.Dom.getPosition(node).top,
	            height: taskHeight
	          },
	          finish: {
	            top: main_core.Dom.getPosition(this.anchor).top,
	            height: main_core.Dom.getPosition(node).height
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: state => {
	            node.style.top = state.top + "px";
	            this.anchor.style.height = state.height + "px";
	          },
	          complete: () => {
	            this.finish(node, resolve);
	          }
	        });
	        movingEvent.animate();
	      }.bind(this), 200);
	    });
	  }
	  finish(node, onFinish) {
	    node.style.position = "";
	    node.style.width = "";
	    node.style.top = "";
	    node.style.left = "";
	    node.style.zIndex = "";
	    main_core.Dom.insertAfter(node, this.anchor);
	    main_core.Dom.remove(this.anchor);
	    this.anchor = null;
	    if (main_core.Type.isFunction(onFinish)) {
	      onFinish();
	    }
	  }
	}
	TaskComplete.DURATION = 1200;

	class Hide extends Animation {
	  constructor(params) {
	    super(params);
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isDomNode(params.node)) {
	        this.node = params.node;
	      }
	    }
	  }
	  start() {
	    return new Promise(resolve => {
	      if (!this.node) {
	        resolve();
	      }
	      const node = this.node;
	      const wrapperPosition = main_core.Dom.getPosition(node);
	      const hideEvent = new BX.easing({
	        duration: Hide.DURATION,
	        start: {
	          height: wrapperPosition.height,
	          opacity: 1,
	          marginBottom: 15
	        },
	        finish: {
	          height: 0,
	          opacity: 0,
	          marginBottom: 0
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          if (node) {
	            node.style.height = state.height + "px";
	            node.style.opacity = state.opacity;
	            node.style.marginBottom = state.marginBottom;
	          }
	        },
	        complete: () => {
	          this.finish(node, resolve);
	        }
	      });
	      hideEvent.animate();
	    });
	  }
	  finish(node, onFinish) {
	    main_core.Dom.remove(node);
	    if (main_core.Type.isFunction(onFinish)) {
	      onFinish();
	    }
	  }
	}
	Hide.DURATION = 1000;

	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _isRunning = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRunning");
	class Queue {
	  static add(animation) {
	    babelHelpers.classPrivateFieldLooseBase(Queue, _items)[_items].push(animation);
	    return Queue;
	  }
	  static run() {
	    if (babelHelpers.classPrivateFieldLooseBase(Queue, _isRunning)[_isRunning]) {
	      return;
	    }

	    /** @var Animation animation */
	    let animations = babelHelpers.classPrivateFieldLooseBase(Queue, _items)[_items].shift();
	    if (!animations) {
	      return;
	    }
	    if (!main_core.Type.isArray(animations)) {
	      animations = [animations];
	    }
	    babelHelpers.classPrivateFieldLooseBase(Queue, _isRunning)[_isRunning] = true;
	    const promises = [];
	    animations.forEach(animation => {
	      if (animation instanceof Animation) {
	        promises.push(animation.start());
	      }
	    });
	    Promise.all(promises).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(Queue, _isRunning)[_isRunning] = false;
	      Queue.run();
	    });
	  }
	}
	Object.defineProperty(Queue, _items, {
	  writable: true,
	  value: []
	});
	Object.defineProperty(Queue, _isRunning, {
	  writable: true,
	  value: false
	});

	let _$8 = t => t,
	  _t$8,
	  _t2$4,
	  _t3$4,
	  _t4$4,
	  _t5$4,
	  _t6$4,
	  _t7$2,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11,
	  _t12;

	/**
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */
	class Stream {
	  constructor(params) {
	    this.users = new Map();
	    this.eventIds = new Set();
	    this.pinnedItems = [];
	    this.tasks = [];
	    this.items = [];
	    this.editors = new Map();
	    this.layout = {};
	    this.dateSeparators = new Map();
	    this.nameFormat = params.nameFormat;
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.Timeline.Stream');
	    this.initItemClasses(params.itemClasses);
	    this.currentPage = 1;
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isNumber(params.pageSize)) {
	        this.pageSize = params.pageSize;
	      }
	      if (!this.pageSize || this.pageSize <= 0) {
	        this.pageSize = 20;
	      }
	      this.addUsers(params.users);
	      if (main_core.Type.isArray(params.items)) {
	        params.items.forEach(data => {
	          const item = this.createItem(data);
	          if (item) {
	            this.addItem(item);
	          }
	        });
	      }
	      if (main_core.Type.isArray(params.tasks)) {
	        this.initTasks(params.tasks);
	      }
	      if (main_core.Type.isArray(params.editors)) {
	        params.editors.forEach(editor => {
	          if (editor instanceof Editor) {
	            this.editors.set(editor.getId(), editor);
	          }
	        });
	      }
	    }
	    this.bindEvents();
	    this.progress = false;
	    this.emit('onAfterInit', {
	      stream: this
	    });
	  }
	  initTasks(tasks) {
	    this.tasks = [];
	    tasks.forEach(data => {
	      const task = this.createItem(data);
	      if (task) {
	        this.tasks.push(task);
	      }
	    });
	  }
	  bindEvents() {
	    this.onScrollHandler = main_core.Runtime.throttle(this.onScroll.bind(this), 100).bind(this);
	    main_core.Event.ready(() => {
	      if (this.getItems().length >= this.pageSize) {
	        this.enableLoadOnScroll();
	      }
	    });
	    Array.from(this.editors.values()).forEach(editor => {
	      editor.subscribe('error', event => {
	        this.onError(event.getData());
	      });
	    });
	  }
	  initItemClasses(itemClasses) {
	    if (itemClasses) {
	      this.itemClasses = new Map(itemClasses);
	    } else {
	      this.itemClasses = new Map();
	    }
	    this.itemClasses.set('item_create', History);
	    this.itemClasses.set('stage_change', StageChange);
	    this.itemClasses.set('fields_change', FieldsChange);
	    this.itemClasses.set('comment', Comment);
	  }
	  createItem(data, itemClassName) {
	    if (!main_core.Type.isPlainObject(data.events)) {
	      data.events = {};
	    }
	    data.eventIds = this.eventIds;
	    data.events.onPinClick = this.onItemPinClick.bind(this);
	    data.events.onDelete = this.onItemDelete.bind(this);
	    data.events.onError = this.onError.bind(this);
	    if (!main_core.Type.isFunction(itemClassName)) {
	      itemClassName = this.getItemClassName(data);
	    }
	    const item = new itemClassName(data);
	    if (item instanceof Item) {
	      return item.setUserData(this.users).setTimeFormat(this.getTimeFormat()).setNameFormat(this.nameFormat);
	    }
	    return null;
	  }
	  addItem(item) {
	    if (item instanceof Item) {
	      this.items.push(item);
	      if (item.isFixed) {
	        this.pinnedItems.push(this.getPinnedItemFromItem(item));
	      }
	    }
	    return this;
	  }

	  /**
	   * @protected
	   */
	  static getItemFromArray(items, id) {
	    let result = null;
	    let key = 0;
	    while (true) {
	      if (!items[key]) {
	        break;
	      }
	      const item = items[key];
	      if (item.getId() === id) {
	        result = item;
	        break;
	      }
	      key++;
	    }
	    return result;
	  }
	  static getItemIndexFromArray(items, id) {
	    let result = null;
	    let key = 0;
	    while (true) {
	      if (!items[key]) {
	        break;
	      }
	      const item = items[key];
	      if (item.getId() === id) {
	        result = key;
	        break;
	      }
	      key++;
	    }
	    return result;
	  }
	  getItems() {
	    return this.items;
	  }
	  getItem(id) {
	    return Stream.getItemFromArray(this.getItems(), id);
	  }
	  getPinnedItems() {
	    return this.pinnedItems;
	  }
	  getPinnedItem(id) {
	    return Stream.getItemFromArray(this.getPinnedItems(), id);
	  }
	  getTasks() {
	    return this.tasks;
	  }
	  getTask(id) {
	    return Stream.getItemFromArray(this.getTasks(), id);
	  }
	  render() {
	    if (!this.layout.container) {
	      this.layout.container = main_core.Tag.render(_t$8 || (_t$8 = _$8`<div class="ui-item-detail-stream-container"></div>`));
	    }
	    if (this.editors.size > 0) {
	      this.renderEditors();
	    }
	    if (!this.layout.content) {
	      this.layout.content = main_core.Tag.render(_t2$4 || (_t2$4 = _$8`<div class="ui-item-detail-stream-content"></div>`));
	      this.layout.container.appendChild(this.layout.content);
	    }
	    if (!this.layout.pinnedItemsContainer) {
	      this.layout.pinnedItemsContainer = main_core.Tag.render(_t3$4 || (_t3$4 = _$8`<div class="ui-item-detail-stream-container-list ui-item-detail-stream-container-list-fixed"></div>`));
	      this.layout.content.appendChild(this.layout.pinnedItemsContainer);
	    }
	    this.renderPinnedItems();
	    if (!this.layout.tasksContainer) {
	      this.layout.tasksContainer = main_core.Tag.render(_t4$4 || (_t4$4 = _$8`<div class="ui-item-detail-stream-container-list"></div>`));
	      this.layout.content.appendChild(this.layout.tasksContainer);
	    }
	    this.renderTasks();
	    if (!this.layout.itemsContainer) {
	      this.layout.itemsContainer = main_core.Tag.render(_t5$4 || (_t5$4 = _$8`<div class="ui-item-detail-stream-container-list"></div>`));
	      this.layout.content.appendChild(this.layout.itemsContainer);
	    }
	    this.renderItems();
	    this.emit('onAfterRender');
	    return this.layout.container;
	  }
	  getContainer() {
	    return this.layout.container;
	  }
	  renderEditors() {
	    if (!this.layout.container) {
	      return;
	    }
	    if (!this.layout.editors) {
	      this.layout.editorsTitle = main_core.Tag.render(_t6$4 || (_t6$4 = _$8`<div class="ui-item-detail-stream-section-new-header"></div>`));
	      this.layout.editorsContent = main_core.Tag.render(_t7$2 || (_t7$2 = _$8`<div class="ui-item-detail-stream-section-new-detail"></div>`));
	      this.layout.editors = main_core.Tag.render(_t8$1 || (_t8$1 = _$8`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-new">
				<div class="ui-item-detail-stream-section-icon"></div>
				<div class="ui-item-detail-stream-section-content">
					${0}
				</div>
				${0}
			</div>`), this.layout.editorsTitle, this.layout.editorsContent);
	      let isTitleActive = true;
	      Array.from(this.editors.values()).forEach(editor => {
	        this.layout.editorsTitle.appendChild(main_core.Tag.render(_t9$1 || (_t9$1 = _$8`<a class="ui-item-detail-stream-section-new-action ${0}">${0}</a>`), isTitleActive ? 'ui-item-detail-stream-section-new-action-active' : '', editor.getTitle()));
	        this.layout.editorsContent.appendChild(editor.render());
	        isTitleActive = false;
	      });
	      this.layout.container.appendChild(this.layout.editors);
	    }
	  }
	  renderPinnedItems() {
	    main_core.Dom.clean(this.layout.pinnedItemsContainer);
	    this.createFixedAnchor();
	    this.getPinnedItems().forEach(pinnedItem => {
	      if (!pinnedItem.isRendered()) {
	        pinnedItem.render();
	      }
	      main_core.Dom.append(pinnedItem.getContainer(), this.layout.pinnedItemsContainer);
	    });
	  }
	  createFixedAnchor() {
	    this.fixedAnchor = main_core.Tag.render(_t10$1 || (_t10$1 = _$8`<div class="ui-item-detail-stream-section-fixed-anchor"></div>`));
	    main_core.Dom.prepend(this.fixedAnchor, this.layout.pinnedItemsContainer);
	  }
	  updateTasks(tasks) {
	    if (!this.tasks) {
	      this.tasks = [];
	    }
	    const newTasks = [];
	    tasks.forEach(data => {
	      const task = this.createItem(data);
	      if (task) {
	        newTasks.push(task);
	        this.addUsers(data.users);
	      }
	    });
	    const deleteTasks = [];
	    this.tasks.forEach(task => {
	      if (!Stream.getItemFromArray(newTasks, task.getId())) {
	        deleteTasks.push(task);
	      }
	    });
	    deleteTasks.forEach(task => {
	      this.deleteItem(task);
	    });
	    let tasksTitle = this.getTasksTitle();
	    if (newTasks.length > 0) {
	      if (!tasksTitle) {
	        tasksTitle = this.renderTasksTitle();
	        this.layout.tasksContainer.appendChild(tasksTitle);
	      }
	      newTasks.forEach(task => {
	        if (!this.getTask(task.getId())) {
	          this.tasks.push(task);
	          Queue.add(new Show({
	            item: task,
	            container: this.layout.tasksContainer,
	            insertAfter: tasksTitle
	          }));
	        } else {
	          const streamTask = this.getTask(task.getId());
	          streamTask.setUserData(this.users);
	          streamTask.update(task.getDataForUpdate());
	        }
	      });
	    } else {
	      const title = this.getTasksTitle();
	      if (title) {
	        main_core.Dom.remove(title);
	        this.layout.tasksTitle = null;
	      }
	    }
	    Queue.run();
	  }
	  renderTasks() {
	    if (this.getTasks().length > 0) {
	      this.layout.tasksContainer.appendChild(this.renderTasksTitle());
	      this.getTasks().forEach(task => {
	        if (!task.isRendered()) {
	          main_core.Dom.append(task.render(), this.layout.tasksContainer);
	        }
	      });
	    } else {
	      const title = this.getTasksTitle();
	      if (title) {
	        title.parentElement.removeChild(title);
	      }
	    }
	  }
	  getTasksTitle() {
	    return this.layout.tasksTitle;
	  }
	  renderTasksTitle() {
	    if (!this.layout.tasksTitle) {
	      this.layout.tasksTitle = main_core.Tag.render(_t11 || (_t11 = _$8`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-planned-label">
				<div class="ui-item-detail-stream-section-content">
					<div class="ui-item-detail-stream-planned-text">${0}</div>
				</div>
			</div>`), main_core.Loc.getMessage('UI_TIMELINE_TASKS_TITLE'));
	    }
	    return this.layout.tasksTitle;
	  }
	  renderItems() {
	    const lastItem = this.items[this.items.length - 1];
	    this.items.forEach(item => {
	      item.setIsLast(item === lastItem);
	      if (!item.isRendered()) {
	        const day = this.constructor.getDayFromDate(item.getCreatedTime());
	        if (!this.getDateSeparator(day)) {
	          const dateSeparator = this.createDateSeparator(day);
	          main_core.Dom.append(dateSeparator, this.layout.itemsContainer);
	        }
	        main_core.Dom.append(item.render(), this.layout.itemsContainer);
	      }
	    });
	  }
	  getDateSeparator(day) {
	    return this.dateSeparators.get(day);
	  }
	  createDateSeparator(day) {
	    const separator = this.renderDateSeparator(day);
	    this.dateSeparators.set(day, separator);
	    return separator;
	  }
	  static getDayFromDate(date) {
	    if (date instanceof Date) {
	      if (Stream.isToday(date)) {
	        return BX.date.format('today');
	      }
	      return BX.date.format('d F Y', date);
	    }
	    return null;
	  }
	  static isToday(date) {
	    return BX.date.format('d F Y', date) === BX.date.format('d F Y');
	  }
	  renderDateSeparator(day) {
	    return main_core.Tag.render(_t12 || (_t12 = _$8`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-history-label">
			<div class="ui-item-detail-stream-section-content">
				<div class="ui-item-detail-stream-history-text">${0}</div>
			</div>
		</div>`), day);
	  }
	  getItemClassName(data) {
	    let itemClassName = null;
	    if (main_core.Type.isPlainObject(data) && main_core.Type.isString(data.itemClassName)) {
	      itemClassName = data.itemClassName;
	    }
	    if (itemClassName) {
	      itemClassName = main_core.Reflection.getClass(itemClassName);
	    }
	    if (!main_core.Type.isFunction(itemClassName)) {
	      if (main_core.Type.isPlainObject(data) && main_core.Type.isString(data.action)) {
	        itemClassName = this.itemClasses.get(data.action);
	      }
	      if (!itemClassName) {
	        itemClassName = History;
	      }
	    }
	    return itemClassName;
	  }
	  insertItem(item) {
	    if (!(item instanceof Item)) {
	      return this;
	    }
	    if (this.getItem(item.getId())) {
	      return this;
	    }
	    this.items.unshift(item);
	    const day = this.constructor.getDayFromDate(item.getCreatedTime());
	    if (!day) {
	      return this;
	    }
	    if (!this.getDateSeparator(day)) {
	      const separator = this.createDateSeparator(day);
	      main_core.Dom.prepend(separator, this.layout.itemsContainer);
	    }
	    Queue.add(new Drop({
	      item,
	      insertAfter: this.getDateSeparator(day),
	      container: this.layout.editorsContent
	    })).run();
	    return this;
	  }
	  getTimeFormat() {
	    if (!this.timeFormat) {
	      const datetimeFormat = main_core.Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
	      const dateFormat = main_core.Loc.getMessage("FORMAT_DATE");
	      this.timeFormat = BX.date.convertBitrixFormat(datetimeFormat.trim().replace(dateFormat, ""));
	    }
	    return this.timeFormat;
	  }
	  getDateTimeFormat() {
	    if (!this.dateTimeFormat) {
	      const datetimeFormat = main_core.Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
	      this.dateTimeFormat = BX.date.convertBitrixFormat(datetimeFormat);
	    }
	    return this.dateTimeFormat;
	  }
	  startProgress() {
	    this.progress = true;
	    if (!this.getLoader().isShown()) {
	      const lastItem = this.items[this.items.length - 1];
	      if (lastItem && lastItem.isRendered()) {
	        this.getLoader().show(lastItem.getContainer());
	      } else {
	        this.getLoader().show(this.layout.container);
	      }
	    }
	  }
	  stopProgress() {
	    this.progress = false;
	    this.getLoader().hide();
	  }
	  isProgress() {
	    return this.progress === true;
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        size: 150
	      });
	    }
	    return this.loader;
	  }
	  enableLoadOnScroll() {
	    main_core.Event.bind(window, 'scroll', this.onScrollHandler);
	  }
	  disableLoadOnScroll() {
	    main_core.Event.unbind(window, 'scroll', this.onScrollHandler);
	  }
	  onScroll() {
	    if (this.isProgress()) {
	      return;
	    }
	    const lastItem = this.items[this.items.length - 1];
	    if (!lastItem) {
	      this.disableLoadOnScroll();
	      return;
	    }
	    if (!lastItem.isRendered()) {
	      return;
	    }
	    const pos = lastItem.getContainer().getBoundingClientRect();
	    if (pos.top <= document.documentElement.clientHeight) {
	      this.emit('onScrollToTheBottom');
	    }
	  }
	  getPinnedItemFromItem(item) {
	    const pinnedItem = main_core.Runtime.clone(item);
	    if (item.isRendered()) {
	      pinnedItem.clearLayout();
	    }
	    pinnedItem.setTimeFormat(this.getDateTimeFormat());
	    pinnedItem.isPinned = true;
	    return pinnedItem;
	  }
	  onItemPinClick(item) {
	    if (item.isFixed) {
	      this.pinItem(item);
	    } else {
	      this.unPinItem(item);
	    }
	    this.emit('onPinClick', {
	      item
	    });
	  }
	  pinItem(item) {
	    const pinnedItem = this.getPinnedItem(item.getId());
	    if (!pinnedItem) {
	      this.getPinnedItems().push(this.getPinnedItemFromItem(item));
	    }
	    Queue.add(new Pin({
	      item: this.getPinnedItem(item.getId()),
	      anchor: this.fixedAnchor,
	      startPosition: main_core.Dom.getPosition(item.getContainer())
	    })).run();
	    return this;
	  }
	  unPinItem(item) {
	    const pinnedItem = this.getPinnedItem(item.getId());
	    if (pinnedItem === item) {
	      const commonItem = this.getItem(pinnedItem.getId());
	      if (commonItem) {
	        commonItem.isFixed = false;
	        commonItem.renderPin();
	      }
	    }
	    if (pinnedItem && pinnedItem.isRendered()) {
	      Queue.add(new Hide({
	        node: pinnedItem.getContainer()
	      })).run();
	    }
	    this.pinnedItems = this.pinnedItems.filter(filteredItem => filteredItem.getId() !== item.getId());
	  }
	  onItemDelete(item) {
	    this.deleteItem(item);
	  }
	  deleteItem(item) {
	    let itemIndex = Stream.getItemIndexFromArray(this.items, item.getId());
	    const animations = [];
	    if (itemIndex !== null) {
	      if (item.isRendered()) {
	        const animation = new Hide({
	          node: this.getItem(item.getId()).getContainer()
	        });
	        animations.push(animation);
	      }
	      this.items.splice(itemIndex, 1);
	    }
	    itemIndex = Stream.getItemIndexFromArray(this.pinnedItems, item.getId());
	    if (itemIndex !== null) {
	      if (item.isRendered()) {
	        const animation = new Hide({
	          node: this.getPinnedItem(item.getId()).getContainer()
	        });
	        animations.push(animation);
	      }
	      this.pinnedItems.splice(itemIndex, 1);
	    }
	    itemIndex = Stream.getItemIndexFromArray(this.tasks, item.getId());
	    if (itemIndex !== null) {
	      let isAddHideAnimation = true;
	      if (item.completedData) {
	        const newItem = this.createItem(item.completedData);
	        if (newItem) {
	          if (!this.getItem(newItem.getId())) {
	            this.items.unshift(newItem);
	            const day = this.constructor.getDayFromDate(newItem.getCreatedTime());
	            if (day) {
	              if (!this.getDateSeparator(day)) {
	                const separator = this.createDateSeparator(day);
	                main_core.Dom.prepend(separator, this.layout.itemsContainer);
	              }
	              Queue.add(new TaskComplete({
	                item: newItem,
	                task: item,
	                insertAfter: this.getDateSeparator(day)
	              })).run();
	              isAddHideAnimation = false;
	            }
	          }
	        }
	      }
	      if (isAddHideAnimation) {
	        animations.push(new Hide({
	          node: this.getTask(item.getId()).getContainer()
	        }));
	      }
	      this.tasks.splice(itemIndex, 1);
	    }
	    Queue.add(animations).run();
	  }
	  onError({
	    message
	  }) {
	    this.showError(message);
	  }
	  showError(message) {
	    console.error(message);
	  }
	  addUsers(users) {
	    if (main_core.Type.isPlainObject(users)) {
	      if (!this.users) {
	        this.users = new Map();
	      }
	      Object.keys(users).forEach(userId => {
	        userId = main_core.Text.toInteger(userId);
	        if (userId > 0) {
	          this.users.set(userId, users[userId]);
	        }
	      });
	    }
	  }
	  addAnimation(animation) {
	    Queue.add(animation).run();
	  }
	}

	/**
	 * @memberOf BX.UI
	 */
	const Timeline = {
	  Stream,
	  Item,
	  History,
	  StageChange,
	  Editor,
	  CommentEditor,
	  FieldsChange
	};

	exports.Timeline = Timeline;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI.Dialogs,BX.Main,BX.Event,BX));
//# sourceMappingURL=timeline.bundle.js.map
