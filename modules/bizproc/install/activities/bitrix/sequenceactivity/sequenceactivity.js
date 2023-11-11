/* eslint-disable */
(function (exports,main_core,main_core_events,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;

	// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	let _SequenceActivityCurClick = null;

	// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	function _SequenceActivityClick(activityIndex, i, presetId) {
	  const preset = presetId ? window.arAllActivities[activityIndex].PRESETS.find(item => item.ID === presetId) : null;
	  const defaultProps = preset ? preset.PROPERTIES : {};
	  const activity = {
	    Properties: {
	      Title: preset && preset.NAME || main_core.Text.encode(window.arAllActivities[activityIndex].NAME),
	      ...defaultProps
	    },
	    Type: window.arAllActivities[activityIndex].CLASS,
	    Children: []
	  };
	  _SequenceActivityCurClick.AddActivity(window.CreateActivity(activity), i);
	}

	// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	function _SequenceActivityMyActivityClick(isn, i) {
	  if (window.arUserParams && BX.type.isArray(window.arUserParams.SNIPPETS) && window.arUserParams.SNIPPETS[isn]) {
	    _SequenceActivityCurClick.AddActivity(window.CreateActivity(window.arUserParams.SNIPPETS[isn]), i);
	  }
	}
	const BizProcActivity = window.BizProcActivity;
	var _numberHeadRows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("numberHeadRows");
	var _menuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menuItems");
	var _onLineMouseOver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onLineMouseOver");
	var _onLineMouseOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onLineMouseOut");
	var _initDragNDropHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDragNDropHandlers");
	var _onDragging = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDragging");
	var _onDrop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDrop");
	var _moveActivityToPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moveActivityToPosition");
	var _checkMovingCycle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkMovingCycle");
	var _copyActivityToPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyActivityToPosition");
	var _removeChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeChild");
	var _refreshArrows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refreshArrows");
	var _removeResources = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeResources");
	var _onClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClick");
	var _getAllActivitiesMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAllActivitiesMenuItems");
	var _getGroupMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getGroupMenuItems");
	var _addMyActivitiesMenuItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMyActivitiesMenuItem");
	var _getActivityMenuItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getActivityMenuItem");
	var _renderActivityMenuItemNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActivityMenuItemNode");
	var _onGroupItemSubMenuShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onGroupItemSubMenuShow");
	var _addActivity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addActivity");
	var _createArrow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createArrow");
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	class SequenceActivity extends BizProcActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    Object.defineProperty(this, _createArrow, {
	      value: _createArrow2
	    });
	    Object.defineProperty(this, _addActivity, {
	      value: _addActivity2
	    });
	    Object.defineProperty(this, _onGroupItemSubMenuShow, {
	      value: _onGroupItemSubMenuShow2
	    });
	    Object.defineProperty(this, _renderActivityMenuItemNode, {
	      value: _renderActivityMenuItemNode2
	    });
	    Object.defineProperty(this, _getActivityMenuItem, {
	      value: _getActivityMenuItem2
	    });
	    Object.defineProperty(this, _addMyActivitiesMenuItem, {
	      value: _addMyActivitiesMenuItem2
	    });
	    Object.defineProperty(this, _getGroupMenuItems, {
	      value: _getGroupMenuItems2
	    });
	    Object.defineProperty(this, _getAllActivitiesMenuItems, {
	      value: _getAllActivitiesMenuItems2
	    });
	    Object.defineProperty(this, _onClick, {
	      value: _onClick2
	    });
	    Object.defineProperty(this, _removeResources, {
	      value: _removeResources2
	    });
	    Object.defineProperty(this, _refreshArrows, {
	      value: _refreshArrows2
	    });
	    Object.defineProperty(this, _removeChild, {
	      value: _removeChild2
	    });
	    Object.defineProperty(this, _copyActivityToPosition, {
	      value: _copyActivityToPosition2
	    });
	    Object.defineProperty(this, _checkMovingCycle, {
	      value: _checkMovingCycle2
	    });
	    Object.defineProperty(this, _moveActivityToPosition, {
	      value: _moveActivityToPosition2
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
	    Object.defineProperty(this, _onLineMouseOut, {
	      value: _onLineMouseOut2
	    });
	    Object.defineProperty(this, _onLineMouseOver, {
	      value: _onLineMouseOver2
	    });
	    Object.defineProperty(this, _numberHeadRows, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _menuItems, {
	      writable: true,
	      value: void 0
	    });
	    this.Type = 'SequenceActivity';
	    this.childsContainer = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _initDragNDropHandlers)[_initDragNDropHandlers]();

	    // compatibility
	    this.LineMouseOver = babelHelpers.classPrivateFieldLooseBase(this, _onLineMouseOver)[_onLineMouseOver];
	    this.LineMouseOut = babelHelpers.classPrivateFieldLooseBase(this, _onLineMouseOut)[_onLineMouseOut];
	    this.OnClick = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick](event.target);
	    };
	    this.ondragging = babelHelpers.classPrivateFieldLooseBase(this, _onDragging)[_onDragging].bind(this);
	    this.ondrop = babelHelpers.classPrivateFieldLooseBase(this, _onDrop)[_onDrop].bind(this);
	    this.ActivityRemoveChild = this.RemoveChild;
	    this.RemoveChild = babelHelpers.classPrivateFieldLooseBase(this, _removeChild)[_removeChild].bind(this);
	    this.RemoveResources = babelHelpers.classPrivateFieldLooseBase(this, _removeResources)[_removeResources].bind(this);
	    this.AddActivity = babelHelpers.classPrivateFieldLooseBase(this, _addActivity)[_addActivity].bind(this);
	    this.CreateLine = babelHelpers.classPrivateFieldLooseBase(this, _createArrow)[_createArrow].bind(this);
	    this.ActivityDraw = this.Draw;
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	  }
	  get iHead() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _numberHeadRows)[_numberHeadRows];
	  }
	  set iHead(value) {
	    if (main_core.Type.isInteger(value) && value >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _numberHeadRows)[_numberHeadRows] = value;
	    }
	  }
	}
	function _onLineMouseOver2() {
	  main_core.Dom.style(this.parentNode, 'backgroundImage', 'url(/bitrix/images/bizproc/arr_over.gif)');
	}
	function _onLineMouseOut2() {
	  main_core.Dom.style(this.parentNode, 'backgroundImage', 'url(/bitrix/images/bizproc/arr.gif)');
	}
	function _initDragNDropHandlers2() {
	  this.lastDrop = false;
	  this.h1id = window.DragNDrop.AddHandler('ondragging', babelHelpers.classPrivateFieldLooseBase(this, _onDragging)[_onDragging].bind(this));
	  this.h2id = window.DragNDrop.AddHandler('ondrop', babelHelpers.classPrivateFieldLooseBase(this, _onDrop)[_onDrop].bind(this));
	}
	function _onDragging2(event, x, y) {
	  if (this.childsContainer) {
	    for (let i = 0; i <= this.childActivities.length; i++) {
	      const arrow = this.childsContainer.rows[i * 2 + this.iHead].cells[0].childNodes[0];
	      const position = main_core.Dom.getPosition(arrow);
	      if (position.left < x && x < position.right && position.top < y && y < position.bottom) {
	        arrow.onmouseover();
	        this.lastDrop = arrow;
	        return;
	      }
	    }
	    if (this.lastDrop) {
	      this.lastDrop.onmouseout();
	      this.lastDrop = false;
	    }
	  }
	}
	function _onDrop2(x, y, event) {
	  if (this.childsContainer && this.lastDrop) {
	    if (window.DragNDrop.obj.parentActivity && event.ctrlKey === false && event.metaKey === false) {
	      babelHelpers.classPrivateFieldLooseBase(this, _moveActivityToPosition)[_moveActivityToPosition](window.DragNDrop.obj, this.lastDrop);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _copyActivityToPosition)[_copyActivityToPosition](window.DragNDrop.obj, this.lastDrop);
	    }
	    this.lastDrop.onmouseout();
	    this.lastDrop = false;
	  }
	}
	function _moveActivityToPosition2(originalActivity, positionNode) {
	  const parentActivity = originalActivity.parentActivity;
	  const childPosition = parentActivity.childActivities.findIndex(child => child.Name === originalActivity.Name);
	  const position = positionNode.ind;
	  if (parentActivity.Name !== this.Name || childPosition !== position && childPosition + 1 !== position) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _checkMovingCycle)[_checkMovingCycle](originalActivity)) {
	      parentActivity.childsContainer.deleteRow(childPosition * 2 + 1 + parentActivity.iHead);
	      parentActivity.childsContainer.deleteRow(childPosition * 2 + 1 + parentActivity.iHead);
	      parentActivity.childActivities.splice(childPosition, 1);
	      babelHelpers.classPrivateFieldLooseBase(this, _refreshArrows)[_refreshArrows](parentActivity);

	      // after refresh arrows position changed
	      babelHelpers.classPrivateFieldLooseBase(this, _addActivity)[_addActivity](originalActivity, positionNode.ind);
	    } else {
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dialogs,no-alert
	      alert(window.BPMESS.BPSA_ERROR_MOVE_1);
	    }
	  }
	}
	function _checkMovingCycle2(originalActivity) {
	  // eslint-disable-next-line unicorn/no-this-assignment
	  let activity = this;
	  while (activity) {
	    if (originalActivity.Name === activity.Name) {
	      return false;
	    }
	    activity = activity.parentActivity;
	  }
	  return true;
	}
	function _copyActivityToPosition2(originalActivity, positionNode) {
	  const copiedActivity = window.CreateActivity(originalActivity);
	  this.AddActivity(copiedActivity, positionNode.ind);
	}
	function _removeChild2(child) {
	  const index = this.childActivities.indexOf(child);
	  if (index >= 0) {
	    this.ActivityRemoveChild(child);
	    if (this.childsContainer) {
	      this.childsContainer.deleteRow([index * 2 + 1 + this.iHead]);
	      this.childsContainer.deleteRow([index * 2 + 1 + this.iHead]);
	      babelHelpers.classPrivateFieldLooseBase(this, _refreshArrows)[_refreshArrows](this);
	    }
	  }
	}
	function _refreshArrows2(activity) {
	  for (let i = 0; i <= activity.childActivities.length; i++) {
	    // eslint-disable-next-line no-param-reassign
	    activity.childsContainer.rows[i * 2 + activity.iHead].cells[0].childNodes[0].ind = i;
	  }
	}
	function _removeResources2() {
	  window.DragNDrop.RemoveHandler('ondragging', this.h1id);
	  window.DragNDrop.RemoveHandler('ondrop', this.h2id);
	  if (this.childsContainer && this.childsContainer.parentNode) {
	    main_core.Dom.remove(this.childsContainer);
	    this.childsContainer = null;
	  }
	}
	function _onClick2(bindElement) {
	  // eslint-disable-next-line unicorn/no-this-assignment
	  _SequenceActivityCurClick = this;
	  const menu = new main_popup.Menu({
	    bindElement,
	    id: `all-worfklow-activity-${main_core.Text.getRandom()}`,
	    minWidth: 190,
	    autoHide: true,
	    zIndexOptions: {
	      alwaysOnTop: true
	    },
	    cacheable: false,
	    items: babelHelpers.classPrivateFieldLooseBase(this, _getAllActivitiesMenuItems)[_getAllActivitiesMenuItems](),
	    subMenuOptions: {
	      maxWidth: 850,
	      maxHeight: 600
	    }
	  });
	  menu.show();
	}
	function _getAllActivitiesMenuItems2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _addMyActivitiesMenuItem)[_addMyActivitiesMenuItem]();
	    return babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems];
	  }
	  const items = babelHelpers.classPrivateFieldLooseBase(this, _getGroupMenuItems)[_getGroupMenuItems]();
	  Object.entries(window.arAllActivities).forEach(([id, description]) => {
	    if ((id !== 'setstateactivity' || window.rootActivity.Type !== this.Type) && !description.EXCLUDED && description.CATEGORY) {
	      var _description$CATEGORY;
	      const groupId = (_description$CATEGORY = description.CATEGORY.OWN_ID) != null ? _description$CATEGORY : description.CATEGORY.ID;
	      if (items[groupId]) {
	        items[groupId].items.push(babelHelpers.classPrivateFieldLooseBase(this, _getActivityMenuItem)[_getActivityMenuItem](id, description));
	        if (main_core.Type.isArrayFilled(description.PRESETS)) {
	          description.PRESETS.forEach(preset => {
	            items[groupId].items.push(babelHelpers.classPrivateFieldLooseBase(this, _getActivityMenuItem)[_getActivityMenuItem](id, description, preset));
	          });
	        }
	      }
	    }
	  });
	  if (items.rest && main_core.Reflection.getClass('BX.rest.Marketplace')) {
	    items.rest.items.push({
	      className: 'bizproc-designer-sequence-activity-menu-item-icon',
	      html: babelHelpers.classPrivateFieldLooseBase(this, _renderActivityMenuItemNode)[_renderActivityMenuItemNode](window.BPMESS.BPSA_MARKETPLACE_ADD_TITLE_2, window.BPMESS.BPSA_MARKETPLACE_ADD_DESCR_3),
	      title: window.BPMESS.BPSA_MARKETPLACE_ADD_DESCR_3,
	      dataset: {
	        icon: '/bitrix/images/bizproc/act_icon_plus.png',
	        name: window.BPMESS.BPSA_MARKETPLACE_ADD_TITLE_2
	      },
	      onclick: (event, menuItem) => {
	        BX.rest.Marketplace.open({}, 'auto_pb');
	        menuItem.getMenuWindow().getParentMenuWindow().close();
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems] = Object.values(items).filter(item => item.items.length > 0);
	  babelHelpers.classPrivateFieldLooseBase(this, _addMyActivitiesMenuItem)[_addMyActivitiesMenuItem]();
	  return babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems];
	}
	function _getGroupMenuItems2() {
	  const items = {};
	  Object.entries(window.arAllActGroups).forEach(([id, title]) => {
	    items[id] = {
	      id,
	      text: title,
	      items: [],
	      events: {
	        'SubMenu:onShow': babelHelpers.classPrivateFieldLooseBase(this, _onGroupItemSubMenuShow)[_onGroupItemSubMenuShow]
	      }
	    };
	  });
	  return items;
	}
	function _addMyActivitiesMenuItem2() {
	  const index = babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems].findIndex(item => item.id === 'MyActivity');
	  if (index >= 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems].splice(index, 1);
	  }
	  if (window.arUserParams && main_core.Type.isArrayFilled(window.arUserParams.SNIPPETS)) {
	    const item = {
	      id: 'MyActivity',
	      text: window.BPMESS.BPSA_MY_ACTIVITIES_1,
	      items: [],
	      events: {
	        'SubMenu:onShow': babelHelpers.classPrivateFieldLooseBase(this, _onGroupItemSubMenuShow)[_onGroupItemSubMenuShow]
	      }
	    };
	    window.arUserParams.SNIPPETS.forEach((snippet, index) => {
	      var _snippet$Icon;
	      item.items.push({
	        className: 'bizproc-designer-sequence-activity-menu-item-icon',
	        html: babelHelpers.classPrivateFieldLooseBase(this, _renderActivityMenuItemNode)[_renderActivityMenuItemNode](snippet.Properties.Title, ''),
	        dataset: {
	          icon: (_snippet$Icon = snippet.Icon) != null ? _snippet$Icon : '/bitrix/images/bizproc/act_icon.gif',
	          name: snippet.Properties.Title
	        },
	        title: snippet.Properties.Title,
	        onclick: (event, menuItem) => {
	          _SequenceActivityMyActivityClick(index, menuItem.getMenuWindow().getParentMenuWindow().bindElement.ind);
	          menuItem.getMenuWindow().getParentMenuWindow().close();
	        }
	      });
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems].push(item);
	  }
	}
	function _getActivityMenuItem2(id, description, preset) {
	  var _description$ICON;
	  const descriptionText = preset && preset.DESCRIPTION ? preset.DESCRIPTION : description.DESCRIPTION;
	  return {
	    onclick: (event, menuItem) => {
	      _SequenceActivityClick(id, menuItem.getMenuWindow().getParentMenuWindow().bindElement.ind, preset ? preset.ID : null);
	      menuItem.getMenuWindow().getParentMenuWindow().close();
	    },
	    className: 'bizproc-designer-sequence-activity-menu-item-icon',
	    html: babelHelpers.classPrivateFieldLooseBase(this, _renderActivityMenuItemNode)[_renderActivityMenuItemNode](preset ? preset.NAME : description.NAME, descriptionText),
	    title: descriptionText,
	    dataset: {
	      icon: (_description$ICON = description.ICON) != null ? _description$ICON : '/bitrix/images/bizproc/act_icon.gif',
	      name: description.NAME
	    }
	  };
	}
	function _renderActivityMenuItemNode2(title, description) {
	  return main_core.Tag.render(_t || (_t = _`
			<div style="line-height: normal; overflow: hidden; text-overflow: ellipsis;">
				<span><b>${0}</b></span>
				<br/>
				<span>${0}</span>
			</div>
		`), main_core.Text.encode(title), main_core.Text.encode(description));
	}
	function _onGroupItemSubMenuShow2(event) {
	  const groupItem = event.getTarget();
	  if (groupItem.getSubMenu() && groupItem.getSubMenu().getMenuItems()) {
	    groupItem.getSubMenu().getMenuItems().forEach(item => {
	      const iconNode = item.layout.item.querySelector('.menu-popup-item-icon');
	      main_core.Dom.append(main_core.Tag.render(_t2 || (_t2 = _`<img src="${0}" alt="${0}"/>`), main_core.Text.encode(item.dataset.icon), main_core.Text.encode(item.dataset.name)), iconNode);
	    });
	  }
	}
	function _addActivity2(newActivity, position) {
	  this.childActivities.splice(position, 0, newActivity);
	  // eslint-disable-next-line no-param-reassign
	  newActivity.parentActivity = this;
	  newActivity.setCanBeActivated(this.getCanBeActivatedChild());
	  const row = this.childsContainer.insertRow(position * 2 + 1 + this.iHead).insertCell(-1);
	  main_core.Dom.attr(row, {
	    align: 'center',
	    vAlign: 'center'
	  });
	  newActivity.Draw(row);
	  const row2 = this.childsContainer.insertRow(position * 2 + 2 + this.iHead).insertCell(-1);
	  main_core.Dom.attr(row2, {
	    align: 'center',
	    vAlign: 'center'
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _createArrow)[_createArrow](position + 1);
	  babelHelpers.classPrivateFieldLooseBase(this, _refreshArrows)[_refreshArrows](this);
	  window.BPTemplateIsModified = true;
	}
	function _createArrow2(index) {
	  main_core.Dom.style(this.childsContainer.rows[index * 2 + this.iHead].cells[0], {
	    height: '40px',
	    background: 'url(/bitrix/images/bizproc/arr.gif) no-repeat scroll 50% 50%'
	  });
	  const image = BX.Dom.create('img', {
	    attrs: {
	      src: '/bitrix/images/1.gif',
	      width: '28',
	      height: '21'
	    }
	  });
	  image.onmouseover = babelHelpers.classPrivateFieldLooseBase(this, _onLineMouseOver)[_onLineMouseOver];
	  image.onmouseout = babelHelpers.classPrivateFieldLooseBase(this, _onLineMouseOut)[_onLineMouseOut];
	  image.ind = index;
	  main_core.Event.bind(image, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick].bind(this, image));
	  main_core.Dom.append(image, this.childsContainer.rows[index * 2 + this.iHead].cells[0]);
	}
	function _draw2(wrapper) {
	  const rows = Array.from({
	    length: this.iHead + this.childActivities.length * 2
	  }, () => main_core.Tag.render(_t3 || (_t3 = _`
				<tr><td align="center" valign="center"></td></tr>
			`)));
	  this.childsContainer = main_core.Tag.render(_t4 || (_t4 = _`
			<table 
				class="seqactivitycontainer"
				id="${0}"
				width="100%"
				cellspacing="0"
				cellpadding="0"
				border="0"
			>
				<tbody>
					<tr><td align="center" valign="center"></td></tr>
					${0}
				</tbody>
			</table>
		`), main_core.Text.encode(this.Name), rows);
	  main_core.Dom.append(this.childsContainer, wrapper);
	  babelHelpers.classPrivateFieldLooseBase(this, _createArrow)[_createArrow](0);
	  this.childActivities.forEach((child, index) => {
	    child.Draw(this.childsContainer.rows[index * 2 + 1 + this.iHead].cells[0]);
	    babelHelpers.classPrivateFieldLooseBase(this, _createArrow)[_createArrow](main_core.Text.toInteger(index) + 1);
	  });
	  if (this.AfterSDraw) {
	    this.AfterSDraw();
	  }
	}

	exports.SequenceActivity = SequenceActivity;

}((this.window = this.window || {}),BX,BX.Event,BX.Main));
//# sourceMappingURL=sequenceactivity.js.map
