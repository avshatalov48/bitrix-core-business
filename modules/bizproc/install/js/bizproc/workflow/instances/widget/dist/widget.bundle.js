/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {};
(function (exports,main_core,main_core_events,main_popup,ui_imageStackSteps,ui_label,main_date) {
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
	const defaulFormatDuration = [['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']];
	const autoRunIconType = {
	  type: ui_imageStackSteps.imageTypeEnum.ICON,
	  data: {
	    icon: 'business-process-1',
	    color: 'var(--ui-color-base-10)'
	  }
	};
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _stack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stack");
	var _popupInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupInstance");
	var _popupListNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupListNode");
	var _listSkeleton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("listSkeleton");
	var _offset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("offset");
	var _initStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initStack");
	var _getStackText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackText");
	var _getStackUserImages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackUserImages");
	var _getEmptyStackImages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEmptyStackImages");
	var _handleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClick");
	var _getPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupContent");
	var _renderListSkeleton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderListSkeleton");
	var _renderListPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderListPage");
	var _loadList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadList");
	var _handleNextPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleNextPage");
	var _renderListItemFaces = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderListItemFaces");
	var _formatDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatDuration");
	class Widget {
	  constructor(params) {
	    Object.defineProperty(this, _formatDuration, {
	      value: _formatDuration2
	    });
	    Object.defineProperty(this, _renderListItemFaces, {
	      value: _renderListItemFaces2
	    });
	    Object.defineProperty(this, _handleNextPage, {
	      value: _handleNextPage2
	    });
	    Object.defineProperty(this, _loadList, {
	      value: _loadList2
	    });
	    Object.defineProperty(this, _renderListPage, {
	      value: _renderListPage2
	    });
	    Object.defineProperty(this, _renderListSkeleton, {
	      value: _renderListSkeleton2
	    });
	    Object.defineProperty(this, _getPopupContent, {
	      value: _getPopupContent2
	    });
	    Object.defineProperty(this, _handleClick, {
	      value: _handleClick2
	    });
	    Object.defineProperty(this, _getEmptyStackImages, {
	      value: _getEmptyStackImages2
	    });
	    Object.defineProperty(this, _getStackUserImages, {
	      value: _getStackUserImages2
	    });
	    Object.defineProperty(this, _getStackText, {
	      value: _getStackText2
	    });
	    Object.defineProperty(this, _initStack, {
	      value: _initStack2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stack, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupInstance, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupListNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _listSkeleton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _offset, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _initStack)[_initStack]();
	  }
	  static renderTo(node) {
	    const instance = new Widget(JSON.parse(node.dataset.widget));
	    main_core.Dom.replace(node, instance.render());
	  }
	  render() {
	    const isEmpty = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].allCount < 1;
	    const node = main_core.Tag.render(_t || (_t = _`<div class="bp-workflow-instances-widget ${0}"></div>`), isEmpty ? '--empty' : '');
	    babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].renderTo(node);
	    if (!isEmpty) {
	      main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick].bind(this));
	    }
	    return node;
	  }
	}
	function _initStack2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack] = new ui_imageStackSteps.ImageStackSteps({
	    steps: [{
	      id: 'basis',
	      stack: {
	        images: main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].users) ? babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].users, babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].allCount) : babelHelpers.classPrivateFieldLooseBase(this, _getEmptyStackImages)[_getEmptyStackImages]()
	      },
	      footer: {
	        type: ui_imageStackSteps.footerTypeEnum.TEXT,
	        data: {
	          text: babelHelpers.classPrivateFieldLooseBase(this, _getStackText)[_getStackText](babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].allCount)
	        },
	        styles: {
	          maxWidth: 90
	        }
	      },
	      styles: {
	        minWidth: 90
	      }
	    }]
	  });
	}
	function _getStackText2(counter) {
	  if (counter < 1) {
	    return main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_LIST_EMPTY');
	  }
	  return main_core.Loc.getMessagePlural('BIZPROC_JS_WORKFLOW_INST_WIDGET_LIST', counter, {
	    '#COUNT#': counter < 100 ? counter : '99+'
	  });
	}
	function _getStackUserImages2(avatars, allCount) {
	  const images = [];
	  avatars.forEach(avatar => {
	    const userId = main_core.Text.toInteger(avatar.id);
	    if (userId > 0) {
	      images.push({
	        type: ui_imageStackSteps.imageTypeEnum.USER,
	        data: {
	          userId,
	          src: String(avatar.avatarUrl || '')
	        }
	      });
	    } else {
	      images.push(autoRunIconType);
	    }
	  });
	  if (allCount > 3) {
	    const mixed = images.slice(0, 2);
	    mixed.push({
	      type: ui_imageStackSteps.imageTypeEnum.COUNTER,
	      data: {
	        text: `+${allCount - 2}`
	      }
	    });
	    return mixed;
	  }
	  return images;
	}
	function _getEmptyStackImages2(fill, length = 3) {
	  return Array.from({
	    length
	  }).fill(fill != null ? fill : {
	    type: ui_imageStackSteps.imageTypeEnum.USER_STUB
	  });
	}
	function _handleClick2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance] = new main_popup.Popup({
	      autoHide: true,
	      width: 305,
	      minHeight: 342,
	      animation: 'fading-slide',
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getPopupContent)[_getPopupContent](),
	      bindElement: event.target,
	      padding: 0,
	      borderRadius: '12px'
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].toggle();
	}
	function _getPopupContent2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton] = babelHelpers.classPrivateFieldLooseBase(this, _renderListSkeleton)[_renderListSkeleton]();
	  babelHelpers.classPrivateFieldLooseBase(this, _popupListNode)[_popupListNode] = main_core.Tag.render(_t2 || (_t2 = _`<div class="bizproc-workflow-instances-popup-list">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton]);
	  babelHelpers.classPrivateFieldLooseBase(this, _loadList)[_loadList]();
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="bizproc-workflow-instances-popup-content">
				<div class="bizproc-workflow-instances-popup-title">${0}</div>
				<div class="bizproc-workflow-instances-popup-text">
					${0}
					<br>
					${0}
				</div>
				<div class="bizproc-workflow-instances-popup-heads">
					<div class="bizproc-workflow-instances-popup-heads-item">
						${0}
					</div>
					<div class="bizproc-workflow-instances-popup-heads-item">
						${0}
					</div>
					<div class="bizproc-workflow-instances-popup-heads-item">
						${0}
					</div>
				</div>
				${0}
			</div>
		`), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TITLE'), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TEXT_P1'), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TEXT_P2'), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_AUTHOR'), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_IN_PROGRESS'), main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TIME'), babelHelpers.classPrivateFieldLooseBase(this, _popupListNode)[_popupListNode]);
	}
	function _renderListSkeleton2() {
	  let i = 0;
	  let opacity = 1.15;
	  const target = main_core.Tag.render(_t4 || (_t4 = _`<div class="bizproc-workflow-instances-popup-list-page"></div>`));
	  while (i < 5) {
	    ++i;
	    opacity -= 0.15;
	    const facesNode = babelHelpers.classPrivateFieldLooseBase(this, _renderListItemFaces)[_renderListItemFaces]();
	    const label = new ui_label.Label({
	      color: ui_label.LabelColor.DEFAULT,
	      size: ui_label.LabelSize.SM,
	      fill: true,
	      customClass: 'bizproc-workflow-instances-popup-list-item-time-skeleton'
	    });
	    const node = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="bizproc-workflow-instances-popup-list-item" style="opacity: ${0}">
					${0}
					<div class="bizproc-workflow-instances-popup-list-item-time">${0}</div>
				</div>
			`), opacity, facesNode, label.render());
	    main_core.Dom.append(node, target);
	  }
	  return target;
	}
	function _renderListPage2(list) {
	  const pageNode = main_core.Tag.render(_t6 || (_t6 = _`<div class="bizproc-workflow-instances-popup-list-page"></div>`));
	  list.forEach(item => {
	    const facesNode = babelHelpers.classPrivateFieldLooseBase(this, _renderListItemFaces)[_renderListItemFaces](item.avatars.author, item.avatars.running);
	    const label = new ui_label.Label({
	      text: babelHelpers.classPrivateFieldLooseBase(this, _formatDuration)[_formatDuration](item.time.current),
	      color: ui_label.LabelColor.LIGHT_BLUE,
	      size: ui_label.LabelSize.SM,
	      fill: true
	    });
	    const itemNode = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="bizproc-workflow-instances-popup-list-item">
					${0}
					<div class="bizproc-workflow-instances-popup-list-item-time">${0}</div>
				</div>
			`), facesNode, label.render());
	    main_core.Dom.append(itemNode, pageNode);
	  });
	  return pageNode;
	}
	function _loadList2() {
	  main_core.ajax.runAction('bizproc.workflow.getTemplateInstances', {
	    data: {
	      templateId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].tplId,
	      offset: babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset]
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset] += response.data.list.length;
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderListPage)[_renderListPage](response.data.list), babelHelpers.classPrivateFieldLooseBase(this, _popupListNode)[_popupListNode]);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton], babelHelpers.classPrivateFieldLooseBase(this, _popupListNode)[_popupListNode]); // move skeleton to the end

	    babelHelpers.classPrivateFieldLooseBase(this, _handleNextPage)[_handleNextPage](response.data.hasNextPage);
	  }).catch(response => {
	    var _response$errors;
	    if (((_response$errors = response.errors) == null ? void 0 : _response$errors.length) > 0) {
	      main_core.Runtime.loadExtension('ui.dialogs.messagebox').then(({
	        MessageBox
	      }) => {
	        MessageBox.alert(main_core.Text.encode(response.errors[0].message));
	      }).catch(() => {});
	    } else {
	      var _console;
	      (_console = console) == null ? void 0 : _console.error(response);
	    }
	  });
	}
	function _handleNextPage2(hasNextPage) {
	  if (hasNextPage && babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton]) {
	    new IntersectionObserver((entries, observer) => {
	      entries.forEach(entry => {
	        if (entry.isIntersecting) {
	          observer.disconnect();
	          babelHelpers.classPrivateFieldLooseBase(this, _loadList)[_loadList]();
	        }
	      });
	    }).observe(babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton]);
	    return;
	  }
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton]);
	  babelHelpers.classPrivateFieldLooseBase(this, _listSkeleton)[_listSkeleton] = null;
	}
	function _renderListItemFaces2(author, running) {
	  const facesNode = main_core.Tag.render(_t8 || (_t8 = _`
			<div class="bizproc-workflow-instances-popup-list-item-faces"></div>
		`));
	  const stack = new ui_imageStackSteps.ImageStackSteps({
	    steps: [{
	      id: 'col-1',
	      stack: {
	        images: main_core.Type.isArrayFilled(author) ? babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](author) : babelHelpers.classPrivateFieldLooseBase(this, _getEmptyStackImages)[_getEmptyStackImages](autoRunIconType, 1)
	      },
	      styles: {
	        minWidth: 36
	      }
	    }, {
	      id: 'col-2',
	      stack: {
	        images: main_core.Type.isArrayFilled(running) ? babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](running) : babelHelpers.classPrivateFieldLooseBase(this, _getEmptyStackImages)[_getEmptyStackImages](autoRunIconType, 1)
	      }
	    }]
	  });
	  stack.renderTo(facesNode);
	  return facesNode;
	}
	function _formatDuration2(duration) {
	  if (!duration) {
	    return '?';
	  }
	  return main_date.DateTimeFormat.format(defaulFormatDuration, 0, duration);
	}

	exports.Widget = Widget;

}((this.BX.Bizproc.Workflow.Instances = this.BX.Bizproc.Workflow.Instances || {}),BX,BX.Event,BX.Main,BX.UI,BX.UI,BX.Main));
//# sourceMappingURL=widget.bundle.js.map
