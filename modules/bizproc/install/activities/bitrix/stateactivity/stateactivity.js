(function (exports,main_core,main_popup,ui_buttons) {
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
	var _sequenceHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceHeader");
	var _sequenceContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceContent");
	var _sequenceFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceFooter");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _reDraw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reDraw");
	var _onRemoveClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRemoveClick");
	var _removeResources = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeResources");
	var _initDragNDropHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDragNDropHandlers");
	var _onDragging = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDragging");
	var _onDrop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDrop");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	var _renderContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _renderChildren = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderChildren");
	var _resolveIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveIcon");
	var _removeChildActivity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeChildActivity");
	var _openChildSetting = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openChildSetting");
	var _onClickChildRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClickChildRow");
	var _showSequence = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSequence");
	var _hideRows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideRows");
	var _drawSequenceHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawSequenceHeader");
	var _drawSequenceContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawSequenceContent");
	var _drawSequenceFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawSequenceFooter");
	var _hideSequence = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideSequence");
	var _showAddChildMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showAddChildMenu");
	var _getChildMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChildMenuItems");
	var _addInitializeChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addInitializeChild");
	var _addCommandChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addCommandChild");
	var _addDelayChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addDelayChild");
	var _addFinalizeChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFinalizeChild");
	class StateActivity extends window.BizProcActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _addFinalizeChild, {
	      value: _addFinalizeChild2
	    });
	    Object.defineProperty(this, _addDelayChild, {
	      value: _addDelayChild2
	    });
	    Object.defineProperty(this, _addCommandChild, {
	      value: _addCommandChild2
	    });
	    Object.defineProperty(this, _addInitializeChild, {
	      value: _addInitializeChild2
	    });
	    Object.defineProperty(this, _getChildMenuItems, {
	      value: _getChildMenuItems2
	    });
	    Object.defineProperty(this, _showAddChildMenu, {
	      value: _showAddChildMenu2
	    });
	    Object.defineProperty(this, _hideSequence, {
	      value: _hideSequence2
	    });
	    Object.defineProperty(this, _drawSequenceFooter, {
	      value: _drawSequenceFooter2
	    });
	    Object.defineProperty(this, _drawSequenceContent, {
	      value: _drawSequenceContent2
	    });
	    Object.defineProperty(this, _drawSequenceHeader, {
	      value: _drawSequenceHeader2
	    });
	    Object.defineProperty(this, _hideRows, {
	      value: _hideRows2
	    });
	    Object.defineProperty(this, _showSequence, {
	      value: _showSequence2
	    });
	    Object.defineProperty(this, _onClickChildRow, {
	      value: _onClickChildRow2
	    });
	    Object.defineProperty(this, _openChildSetting, {
	      value: _openChildSetting2
	    });
	    Object.defineProperty(this, _removeChildActivity, {
	      value: _removeChildActivity2
	    });
	    Object.defineProperty(this, _renderChildren, {
	      value: _renderChildren2
	    });
	    Object.defineProperty(this, _renderContent, {
	      value: _renderContent2
	    });
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _onDrop, {
	      value: _onDrop2
	    });
	    Object.defineProperty(this, _onDragging, {
	      value: _onDragging2
	    });
	    Object.defineProperty(this, _initDragNDropHandlers, {
	      value: _initDragNDropHandlers2
	    });
	    Object.defineProperty(this, _removeResources, {
	      value: _removeResources2
	    });
	    Object.defineProperty(this, _onRemoveClick, {
	      value: _onRemoveClick2
	    });
	    Object.defineProperty(this, _reDraw, {
	      value: _reDraw2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    this.lastDrop = false;
	    this.main = null;
	    this.commandTable = null;
	    Object.defineProperty(this, _sequenceHeader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _sequenceContent, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _sequenceFooter, {
	      writable: true,
	      value: null
	    });
	    this.Type = 'StateActivity';
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.OnRemoveClick = babelHelpers.classPrivateFieldLooseBase(this, _onRemoveClick)[_onRemoveClick].bind(this);
	    this.RemoveResources = babelHelpers.classPrivateFieldLooseBase(this, _removeResources)[_removeResources].bind(this);
	    this.InitStateActivity = this.Init;
	    this.Init = babelHelpers.classPrivateFieldLooseBase(this, _init)[_init].bind(this);

	    // region compatibility
	    this.ondragging = babelHelpers.classPrivateFieldLooseBase(this, _onDragging)[_onDragging].bind(this);
	    this.ondrop = babelHelpers.classPrivateFieldLooseBase(this, _onDrop)[_onDrop].bind(this);
	    this.reDraw = babelHelpers.classPrivateFieldLooseBase(this, _reDraw)[_reDraw].bind(this);
	    this.remove = event => {
	      const target = event.target;
	      const node = target.parentNode.parentNode.parentNode.parentNode.parentNode;
	      const id = node.id;
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	      babelHelpers.classPrivateFieldLooseBase(this, _removeChildActivity)[_removeChildActivity](node, id);
	    };
	    this.settings = event => {
	      const target = event.target;
	      const id = target.parentNode.parentNode.parentNode.parentNode.parentNode.id;
	      babelHelpers.classPrivateFieldLooseBase(this, _openChildSetting)[_openChildSetting](id);
	    };
	    this.clickrow = event => {
	      const target = event.target;
	      const id = target.parentNode.parentNode.parentNode.parentNode.parentNode.id;
	      babelHelpers.classPrivateFieldLooseBase(this, _onClickChildRow)[_onClickChildRow](id);
	    };
	    this.HideRows = babelHelpers.classPrivateFieldLooseBase(this, _hideRows)[_hideRows].bind(this);
	    this.SequentialShow = babelHelpers.classPrivateFieldLooseBase(this, _showSequence)[_showSequence].bind(this);
	    this.SequentialHide = babelHelpers.classPrivateFieldLooseBase(this, _hideSequence)[_hideSequence].bind(this);
	    this.AddInitialize = babelHelpers.classPrivateFieldLooseBase(this, _addInitializeChild)[_addInitializeChild].bind(this);
	    this.AddCommand = babelHelpers.classPrivateFieldLooseBase(this, _addCommandChild)[_addCommandChild].bind(this);
	    this.AddDelayActivity = babelHelpers.classPrivateFieldLooseBase(this, _addDelayChild)[_addDelayChild].bind(this);
	    this.AddFinilize = babelHelpers.classPrivateFieldLooseBase(this, _addFinalizeChild)[_addFinalizeChild].bind(this);
	    this.ShowAddMenu = event => {
	      // eslint-disable-next-line no-undef
	      this.menu = new PopupMenu('state_float_menu');
	      this.menu.create(2000);
	      const target = event.target;
	      babelHelpers.classPrivateFieldLooseBase(this, _showAddChildMenu)[_showAddChildMenu](target);
	    };
	    // endregion
	  }
	}

	// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	function _init2(activityInfo) {
	  this.InitStateActivity(activityInfo);
	  this.childActivities.forEach(child => {
	    if (child.Type === 'EventDrivenActivity') {
	      const child0 = child.childActivities[0];
	      child.setActivated(child0.Activated);
	      child0.setCanBeActivated(child.canBeActivated);
	    }
	  });
	}
	function _draw2(wrapper) {
	  babelHelpers.classPrivateFieldLooseBase(this, _initDragNDropHandlers)[_initDragNDropHandlers]();
	  this.main = main_core.Tag.render(_t || (_t = _`
			<table class="bizproc-designer-state-activity-table" cellpadding="0" cellspacing="0">
				<tbody>
					<tr id="${0}">
						<td style="height: 24px; white-space: nowrap;">
							${0}
						</td>
					</tr>
					<tr>
						<td>
							${0}
						</td>
					</tr>
				</tbody>
			</table>
		`), main_core.Text.encode(this.Name), babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle](), babelHelpers.classPrivateFieldLooseBase(this, _renderContent)[_renderContent]());
	  main_core.Dom.append(this.main, wrapper);
	}
	function _reDraw2() {
	  const parentNode = this.main.parentNode;
	  main_core.Dom.remove(this.main);
	  this.main = null;
	  this.commandTable = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw](parentNode);
	}
	function _onRemoveClick2() {
	  this.parentActivity.RemoveChild(this);
	}
	function _removeResources2() {
	  window.DragNDrop.RemoveHandler('ondragging', this.h1id);
	  window.DragNDrop.RemoveHandler('ondrop', this.h2id);
	  main_core.Dom.remove(this.main);
	  this.h1id = null;
	  this.h2id = null;
	  this.main = null;
	  this.commandTable = null;
	}
	function _initDragNDropHandlers2() {
	  this.lastDrop = false;
	  if (!this.h1id) {
	    this.h1id = window.DragNDrop.AddHandler('ondragging', babelHelpers.classPrivateFieldLooseBase(this, _onDragging)[_onDragging].bind(this));
	    this.h2id = window.DragNDrop.AddHandler('ondrop', babelHelpers.classPrivateFieldLooseBase(this, _onDrop)[_onDrop].bind(this));
	  }
	}
	function _onDragging2(event, X, Y) {
	  const arrow = this.main;
	  const position = main_core.Dom.getPosition(arrow);
	  if (position.left < X && X < position.right && position.top < Y && Y < position.bottom) {
	    this.lastDrop = arrow;
	    main_core.Dom.style(arrow, 'opacity', '.25');
	    return;
	  }
	  if (this.lastDrop) {
	    main_core.Dom.style(arrow, 'opacity', null);
	    this.lastDrop = false;
	  }
	}
	function _onDrop2() {
	  if (this.lastDrop) {
	    main_core.Dom.style(this.lastDrop, 'opacity', null);
	    this.lastDrop = false;
	    if (this !== window.DragNDrop.obj && this.parentActivity.ReplaceChild) {
	      this.parentActivity.ReplaceChild(this, window.DragNDrop.obj);
	    }
	  }
	}
	function _renderTitle2() {
	  const {
	    root,
	    title,
	    setting,
	    remove
	  } = main_core.Tag.render(_t2 || (_t2 = _`
			<table 
				class="bizproc-designer-state-activity-title-table${0}"
				cellpadding="0"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td ref="title">
							<div
								class="bizproc-designer-state-activity-title"
								title="${0}"
							><b>${0}</b></div>
						</td>
						<td ref='setting' style="cursor: pointer;">
							<div class="ui-icon-set --settings-4 bizproc-designer-state-activity-title-icon"></div>
						</td>
						<td ref='remove' style="cursor: pointer;">
							<div class="ui-icon-set --cross-60 bizproc-designer-state-activity-title-icon"></div>
						</td>
					</tr>
				</tbody>
			</table>
		`), this.Activated === 'N' ? ' --deactivated' : '', main_core.Text.encode(this.Properties.Title), main_core.Text.encode(this.Properties.Title));
	  main_core.Event.bind(title, 'mousedown', event => {
	    const draggedDiv = window.DragNDrop.StartDrag(event, this);
	    draggedDiv.innerHTML = this.main.innerHTML;
	    main_core.Dom.style(draggedDiv, 'width', `${this.main.offsetWidth}px`);
	  });
	  main_core.Event.bind(setting, 'click', this.OnSettingsClick);
	  main_core.Event.bind(remove, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onRemoveClick)[_onRemoveClick].bind(this));
	  return root;
	}
	function _renderContent2() {
	  const {
	    root,
	    add
	  } = main_core.Tag.render(_t3 || (_t3 = _`
			<table 
				class="bizproc-designer-state-activity-children-table${0}"
				cellpadding="4"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td style="font-size: 12px; text-align: left; vertical-align: center">
							<a
								ref="add"
								href="javascript:void(0)"
								style="text-decoration: none"
							>
								<span>${0}</span>
								<div 
									class="ui-icon-set --chevron-down"
									style="--ui-icon-set__icon-color: #2067b0; --ui-icon-set__icon-size: 10px"
								></div>
							</a>
						</td>
					</tr>
					${0}
				</tbody>
			</table>
		`), this.Activated === 'N' ? ' --deactivated' : '', main_core.Text.encode(window.BPMESS.STATEACT_ADD), babelHelpers.classPrivateFieldLooseBase(this, _renderChildren)[_renderChildren]());
	  main_core.Event.bind(add, 'click', babelHelpers.classPrivateFieldLooseBase(this, _showAddChildMenu)[_showAddChildMenu].bind(this, add));
	  this.commandTable = root;
	  return root;
	}
	function _renderChildren2() {
	  if (this.childActivities.length <= 0) {
	    return [];
	  }
	  const nodes = [];
	  this.childActivities.forEach(child => {
	    let childTitle = child.Properties.Title;
	    let icon = child.Type === 'StateFinalizationActivity' ? 'fin' : 'init';
	    let activatedClass = !child.canBeActivated || child.Activated === 'N' ? ' --deactivated' : '';
	    if (child.Type === 'EventDrivenActivity') {
	      const child0 = child.childActivities[0];
	      childTitle = child0.Properties.Title;
	      icon = child0.Type === 'DelayActivity' ? 'delay' : 'cmd';
	      activatedClass = !child0.canBeActivated || child0.Activated === 'N' ? ' --deactivated' : '';
	    }
	    const {
	      iconCode,
	      iconSize,
	      iconColor
	    } = babelHelpers.classPrivateFieldLooseBase(this.constructor, _resolveIcon)[_resolveIcon](icon);
	    const {
	      root,
	      title,
	      setting,
	      remove
	    } = main_core.Tag.render(_t4 || (_t4 = _`
				<tr id="${0}">
					<td class="bizproc-designer-state-activity-child${0}">
						<table style="font-size: 12px; width: 100%">
							<tbody>
								<tr>
									<td style="width: 17px">
										<div
											class="ui-icon-set --${0}"
											style="
												--ui-icon-set__icon-size: ${0};
												--ui-icon-set__icon-color: ${0}
											"
										></div>
									</td>
									<td ref="title" title="${0}">
										${0}
									</td>
									<td 
										ref="setting" 
										title="${0}"
										style="width: 14px"
									>
										<div 
											class="ui-icon-set --settings-4 bizproc-designer-state-activity-child-icon"
										></div>
									</td>
									<td
										ref="remove"
										title="${0}"
										style="width: 14px"
									>
										<div 
											class="ui-icon-set --cross-60 bizproc-designer-state-activity-child-icon"
										></div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			`), main_core.Text.encode(child.Name), activatedClass, iconCode, iconSize, iconColor, main_core.Text.encode(window.BPMESS.STATEACT_EDITBP), main_core.Text.encode(childTitle), main_core.Text.encode(window.BPMESS.STATEACT_SETT), main_core.Text.encode(window.BPMESS.STATEACT_DEL));
	    main_core.Event.bind(title, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onClickChildRow)[_onClickChildRow].bind(this, child.Name));
	    main_core.Event.bind(setting, 'click', babelHelpers.classPrivateFieldLooseBase(this, _openChildSetting)[_openChildSetting].bind(this, child.Name));
	    main_core.Event.bind(remove, 'click', babelHelpers.classPrivateFieldLooseBase(this, _removeChildActivity)[_removeChildActivity].bind(this, root, child.Name));
	    nodes.push(root);
	  });
	  return nodes;
	}
	function _resolveIcon2(icon) {
	  if (icon === 'delay') {
	    return {
	      iconCode: 'hourglass-sandglass',
	      iconSize: '17px',
	      iconColor: 'rgb(42, 177, 28)' // 'rgb(123, 205, 116)',
	    };
	  }

	  if (icon === 'cmd') {
	    return {
	      iconCode: 'forward',
	      iconSize: '17px',
	      iconColor: 'rgb(176, 26, 109)'
	    };
	  }
	  if (icon === 'fin') {
	    return {
	      iconCode: 'statefin',
	      iconSize: '12px',
	      iconColor: 'none'
	    };
	  }
	  if (icon === 'init') {
	    return {
	      iconCode: 'stateinit',
	      iconSize: '12px',
	      iconColor: '#1a92b7'
	    };
	  }
	  return {};
	}
	function _removeChildActivity2(childNode, childId) {
	  const child = this.findChildById(childId);
	  if (child) {
	    main_core.Dom.remove(childNode);
	    this.RemoveChild(child);
	    this.parentActivity.DrawLines();
	  }
	}
	function _openChildSetting2(childId) {
	  let child = this.findChildById(childId);
	  if (child) {
	    if (child.Type === 'EventDrivenActivity') {
	      child = child.childActivities[0];
	    }
	    child.Settings();
	  }
	}
	function _onClickChildRow2(childId) {
	  const child = this.findChildById(childId);
	  if (child) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showSequence)[_showSequence](child);
	  }
	}
	function _showSequence2(child) {
	  // eslint-disable-next-line no-underscore-dangle,@bitrix24/bitrix24-rules/no-pseudo-private
	  window.rootActivity._redrawObject = child;
	  main_core.Dom.style(this.parentActivity.Table, 'display', 'none');
	  babelHelpers.classPrivateFieldLooseBase(this, _hideRows)[_hideRows]();
	  babelHelpers.classPrivateFieldLooseBase(this, _drawSequenceHeader)[_drawSequenceHeader](child);
	  babelHelpers.classPrivateFieldLooseBase(this, _drawSequenceContent)[_drawSequenceContent](child);
	  babelHelpers.classPrivateFieldLooseBase(this, _drawSequenceFooter)[_drawSequenceFooter]();
	  if (document.getElementById('bizprocsavebuttons')) {
	    main_core.Dom.style(document.getElementById('bizprocsavebuttons'), 'display', 'none');
	  }
	  scroll(0, 0);
	}
	function _hideRows2() {
	  // eslint-disable-next-line no-underscore-dangle
	  for (let i = 0; i < this.parentActivity.__l.length; i++) {
	    for (let j = 0; j < 5; j++) {
	      // eslint-disable-next-line no-underscore-dangle
	      main_core.Dom.style(this.parentActivity.__l[i][j], 'display', 'none');
	    }
	  }
	}
	function _drawSequenceHeader2(child) {
	  const title = child.Type === 'EventDrivenActivity' ? child.childActivities[0].Properties.Title : child.Properties.Title;
	  const {
	    root,
	    link
	  } = main_core.Tag.render(_t5 || (_t5 = _`
			<div style="font-size: 12px">
				<a ref="link" href="javascript:void(0)">${0}</a>
				<span> - ${0}</span>
			</div>
		`), main_core.Text.encode(this.Properties.Title), main_core.Text.encode(title));
	  main_core.Event.bind(link, 'click', babelHelpers.classPrivateFieldLooseBase(this, _hideSequence)[_hideSequence].bind(this));
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceHeader)[_sequenceHeader] = root;
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _sequenceHeader)[_sequenceHeader], this.parentActivity.Table.parentNode);
	}
	function _drawSequenceContent2(child) {
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceContent)[_sequenceContent] = main_core.Tag.render(_t6 || (_t6 = _`<div></div>`));
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _sequenceContent)[_sequenceContent], this.parentActivity.Table.parentNode);
	  child.Draw(babelHelpers.classPrivateFieldLooseBase(this, _sequenceContent)[_sequenceContent]);
	}
	function _drawSequenceFooter2() {
	  const backButton = new ui_buttons.Button({
	    text: window.BPMESS.STATEACT_BACK_1,
	    size: ui_buttons.Button.Size.EXTRA_SMALL,
	    color: ui_buttons.Button.Color.LIGHT_BORDER,
	    noCaps: true,
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _hideSequence)[_hideSequence].bind(this)
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceFooter)[_sequenceFooter] = main_core.Tag.render(_t7 || (_t7 = _`<div>${0}</div>`), backButton.render());
	  main_core.Dom.style(backButton.getContainer(), 'margin', '15px');
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _sequenceFooter)[_sequenceFooter], this.parentActivity.Table.parentNode);
	}
	function _hideSequence2() {
	  main_core.Dom.style(this.parentActivity.Table, 'display', 'table');
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _sequenceHeader)[_sequenceHeader]);
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _sequenceContent)[_sequenceContent]);
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _sequenceFooter)[_sequenceFooter]);
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceHeader)[_sequenceHeader] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceContent)[_sequenceContent] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _sequenceFooter)[_sequenceFooter] = null;
	  if (document.getElementById('bizprocsavebuttons')) {
	    main_core.Dom.style(document.getElementById('bizprocsavebuttons'), 'display', 'block');
	  }

	  // eslint-disable-next-line no-underscore-dangle,@bitrix24/bitrix24-rules/no-pseudo-private
	  window.rootActivity._redrawObject = null;
	  window.arWorkflowTemplate = window.rootActivity.Serialize();
	  window.ReDraw();
	}
	function _showAddChildMenu2(bindElement) {
	  const showMenuAction = () => {
	    new main_popup.Menu({
	      bindElement,
	      id: `state_float_menu-${main_core.Text.getRandom()}`,
	      minWidth: 277,
	      autoHide: true,
	      zIndexOptions: {
	        alwaysOnTop: true
	      },
	      cacheable: false,
	      items: babelHelpers.classPrivateFieldLooseBase(this, _getChildMenuItems)[_getChildMenuItems]()
	    }).show();
	  };
	  if (!main_core.Reflection.getClass('BX.Main.Menu')) {
	    main_core.Runtime.loadExtension('main.popup').then(() => showMenuAction()).catch(() => {});
	    return;
	  }
	  showMenuAction();
	}
	function _getChildMenuItems2() {
	  const getItemHtml = (icon, text) => {
	    const {
	      iconCode,
	      iconColor
	    } = babelHelpers.classPrivateFieldLooseBase(this.constructor, _resolveIcon)[_resolveIcon](icon);
	    return main_core.Tag.render(_t8 || (_t8 = _`
				<div style="display: inline-flex; align-items: center">
					<span 
						class="ui-icon-set --${0}"
						style="
							--ui-icon-set__icon-size: 17px;
							--ui-icon-set__icon-color: ${0};
							margin-right: 5px;
						"
					></span>
					<span>${0}</span>
				</div>
			`), iconCode, iconColor, main_core.Text.encode(text));
	  };
	  const items = [{
	    id: '2',
	    html: getItemHtml('cmd', window.BPMESS.STATEACT_MENU_COMMAND),
	    onclick: (event, menuItem) => {
	      menuItem.getMenuWindow().close();
	      babelHelpers.classPrivateFieldLooseBase(this, _addCommandChild)[_addCommandChild]();
	      babelHelpers.classPrivateFieldLooseBase(this, _reDraw)[_reDraw]();
	    }
	  }, {
	    id: '3',
	    html: getItemHtml('delay', window.BPMESS.STATEACT_MENU_DELAY),
	    onclick: (event, menuItem) => {
	      menuItem.getMenuWindow().close();
	      babelHelpers.classPrivateFieldLooseBase(this, _addDelayChild)[_addDelayChild]();
	      babelHelpers.classPrivateFieldLooseBase(this, _reDraw)[_reDraw]();
	    }
	  }];
	  let hasInitChild = false;
	  let hasFinishChild = false;
	  this.childActivities.forEach(child => {
	    if (child.Type === 'StateInitializationActivity') {
	      hasInitChild = true;
	    }
	    if (child.Type === 'StateFinalizationActivity') {
	      hasFinishChild = true;
	    }
	  });
	  if (!hasInitChild) {
	    items.push({
	      id: '1',
	      html: getItemHtml('init', window.BPMESS.STATEACT_MENU_INIT_1),
	      onclick: (event, menuItem) => {
	        menuItem.getMenuWindow().close();
	        babelHelpers.classPrivateFieldLooseBase(this, _addInitializeChild)[_addInitializeChild]();
	        babelHelpers.classPrivateFieldLooseBase(this, _reDraw)[_reDraw]();
	      }
	    });
	  }
	  if (!hasFinishChild) {
	    items.push({
	      id: '5',
	      html: getItemHtml('fin', window.BPMESS.STATEACT_MENU_FIN_1),
	      onclick: (event, menuItem) => {
	        menuItem.getMenuWindow().close();
	        babelHelpers.classPrivateFieldLooseBase(this, _addFinalizeChild)[_addFinalizeChild]();
	        babelHelpers.classPrivateFieldLooseBase(this, _reDraw)[_reDraw]();
	      }
	    });
	  }
	  return items;
	}
	function _addInitializeChild2() {
	  const row = this.commandTable.insertRow(1);
	  const cell = row.insertCell(-1);
	  cell.innerHTML = '';
	  const activity = window.CreateActivity('StateInitializationActivity');
	  this.childActivities.push(activity);
	  activity.parentActivity = this;
	  activity.setCanBeActivated(this.getCanBeActivatedChild());
	  babelHelpers.classPrivateFieldLooseBase(this, _showSequence)[_showSequence](activity);
	}
	function _addCommandChild2() {
	  const eventDrivenActivity = window.CreateActivity('EventDrivenActivity');
	  const handleExternalEventActivity = window.CreateActivity('HandleExternalEventActivity');
	  eventDrivenActivity.childActivities.push(handleExternalEventActivity);
	  handleExternalEventActivity.parentActivity = eventDrivenActivity;
	  const row = this.commandTable.insertRow(1);
	  const cell = row.insertCell(-1);
	  cell.innerHTML = '';
	  this.childActivities.push(eventDrivenActivity);
	  eventDrivenActivity.parentActivity = this;
	  eventDrivenActivity.setCanBeActivated(this.getCanBeActivatedChild());
	  handleExternalEventActivity.Settings();
	}
	function _addDelayChild2() {
	  const eventDrivenActivity = window.CreateActivity('EventDrivenActivity');
	  const delayActivity = window.CreateActivity('DelayActivity');
	  eventDrivenActivity.childActivities.push(delayActivity);
	  delayActivity.parentActivity = eventDrivenActivity;
	  const row = this.commandTable.insertRow(1);
	  const cell = row.insertCell(-1);
	  cell.innerHTML = '';
	  this.childActivities.push(eventDrivenActivity);
	  eventDrivenActivity.parentActivity = this;
	  eventDrivenActivity.setCanBeActivated(this.getCanBeActivatedChild());
	  delayActivity.Settings();
	}
	function _addFinalizeChild2() {
	  const row = this.commandTable.insertRow(1);
	  const cell = row.insertCell(-1);
	  cell.innerHTML = '';
	  const activity = window.CreateActivity('StateFinalizationActivity');
	  this.childActivities.push(activity);
	  activity.parentActivity = this;
	  activity.setCanBeActivated(this.getCanBeActivatedChild());
	  babelHelpers.classPrivateFieldLooseBase(this, _showSequence)[_showSequence](activity);
	}
	Object.defineProperty(StateActivity, _resolveIcon, {
	  value: _resolveIcon2
	});
	window.__StateActivityAdd = function (type, id) {
	  const activity = window.rootActivity.childActivities.find(act => act.Name === id);
	  if (activity) {
	    switch (type) {
	      case 'init':
	        activity.AddInitialize();
	        break;
	      case 'command':
	        activity.AddCommand();
	        break;
	      case 'delay':
	        activity.AddDelayActivity();
	        break;
	      case 'finish':
	        activity.AddFinilize();
	        break;
	      default:
	      // no default
	    }

	    if (BX.Type.isFunction(activity.reDraw)) {
	      activity.reDraw();
	    }
	  }
	};

	exports.StateActivity = StateActivity;

}((this.window = this.window || {}),BX,BX.Main,BX.UI));
//# sourceMappingURL=stateactivity.js.map
