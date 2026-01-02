/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Reaction = this.BX.UI.Reaction || {};
(function (exports,main_core_events,main_core_zIndexManager,ui_iconSet_api_core,ui_iconSet_outline,main_core,ui_reaction_item) {
	'use strict';

	var _STORAGE_KEY = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("STORAGE_KEY");
	var _INITIAL_COUNTER_VALUE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("INITIAL_COUNTER_VALUE");
	var _counters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("counters");
	var _loadCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadCounters");
	var _saveCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveCounters");
	var _initializeDefaultCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initializeDefaultCounters");
	var _sortReactionsByRank = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortReactionsByRank");
	var _isValidReaction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValidReaction");
	class ReactionPickerRanking {
	  constructor() {
	    Object.defineProperty(this, _isValidReaction, {
	      value: _isValidReaction2
	    });
	    Object.defineProperty(this, _sortReactionsByRank, {
	      value: _sortReactionsByRank2
	    });
	    Object.defineProperty(this, _initializeDefaultCounters, {
	      value: _initializeDefaultCounters2
	    });
	    Object.defineProperty(this, _saveCounters, {
	      value: _saveCounters2
	    });
	    Object.defineProperty(this, _loadCounters, {
	      value: _loadCounters2
	    });
	    Object.defineProperty(this, _counters, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters] = babelHelpers.classPrivateFieldLooseBase(this, _loadCounters)[_loadCounters]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initializeDefaultCounters)[_initializeDefaultCounters]();
	  }
	  getRankedReactionsNames() {
	    const allReactions = Object.values(ui_reaction_item.ReactionName);
	    const sortedReactions = babelHelpers.classPrivateFieldLooseBase(this, _sortReactionsByRank)[_sortReactionsByRank](allReactions);
	    const likeIndex = sortedReactions.indexOf(ui_reaction_item.ReactionName.like);
	    if (likeIndex > 0) {
	      sortedReactions.splice(likeIndex, 1);
	      sortedReactions.unshift(ui_reaction_item.ReactionName.like);
	    }
	    return sortedReactions;
	  }
	  incrementReactionCounter(reactionName) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isValidReaction)[_isValidReaction](reactionName)) {
	      return;
	    }
	    const currentCount = babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(reactionName) || 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].set(reactionName, currentCount + 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _saveCounters)[_saveCounters]();
	  }
	  getReactionCounter(reactionName) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isValidReaction)[_isValidReaction](reactionName)) {
	      return 0;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(reactionName) || 0;
	  }
	  resetCounters() {
	    babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].clear();
	    babelHelpers.classPrivateFieldLooseBase(this, _initializeDefaultCounters)[_initializeDefaultCounters]();
	    babelHelpers.classPrivateFieldLooseBase(this, _saveCounters)[_saveCounters]();
	  }
	}
	function _loadCounters2() {
	  try {
	    const stored = localStorage.getItem(babelHelpers.classPrivateFieldLooseBase(ReactionPickerRanking, _STORAGE_KEY)[_STORAGE_KEY]);
	    if (!stored) {
	      return new Map();
	    }
	    const parsed = JSON.parse(stored);
	    if (!main_core.Type.isObject(parsed) || parsed === null) {
	      return new Map();
	    }
	    return new Map(Object.entries(parsed));
	  } catch (error) {
	    // eslint-disable-next-line no-console
	    console.warn('Failed to load reaction counters from localStorage:', error);
	    return new Map();
	  }
	}
	function _saveCounters2() {
	  try {
	    const countersObject = Object.fromEntries(babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters]);
	    localStorage.setItem(babelHelpers.classPrivateFieldLooseBase(ReactionPickerRanking, _STORAGE_KEY)[_STORAGE_KEY], JSON.stringify(countersObject));
	  } catch (error) {
	    // eslint-disable-next-line no-console
	    console.warn('Failed to save reaction counters to localStorage:', error);
	  }
	}
	function _initializeDefaultCounters2() {
	  const allReactions = Object.values(ui_reaction_item.ReactionName);
	  const topReactions = [ui_reaction_item.ReactionName.like, ui_reaction_item.ReactionName.faceWithTearsOfJoy, ui_reaction_item.ReactionName.redHeart, ui_reaction_item.ReactionName.neutralFace, ui_reaction_item.ReactionName.fire, ui_reaction_item.ReactionName.cry];
	  for (const reaction of topReactions) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].has(reaction)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].set(reaction, babelHelpers.classPrivateFieldLooseBase(ReactionPickerRanking, _INITIAL_COUNTER_VALUE)[_INITIAL_COUNTER_VALUE]);
	    }
	  }
	  for (const reaction of allReactions) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].has(reaction)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].set(reaction, 0);
	    }
	  }
	}
	function _sortReactionsByRank2(reactions) {
	  return reactions.toSorted((a, b) => {
	    const countA = babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(a) || 0;
	    const countB = babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(b) || 0;
	    if (countA !== countB) {
	      return countB - countA;
	    }
	    return 0;
	  });
	}
	function _isValidReaction2(reactionName) {
	  return Object.values(ui_reaction_item.ReactionName).includes(reactionName);
	}
	Object.defineProperty(ReactionPickerRanking, _STORAGE_KEY, {
	  writable: true,
	  value: 'ui-reaction-picker-ranking'
	});
	Object.defineProperty(ReactionPickerRanking, _INITIAL_COUNTER_VALUE, {
	  writable: true,
	  value: 2
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	const ReactionPickerEvents = {
	  show: 'show',
	  hide: 'hide',
	  expand: 'expand',
	  select: 'select',
	  mouseenter: 'mouseenter',
	  mouseleave: 'mouseleave'
	};
	var _target = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("target");
	var _popover = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popover");
	var _isShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isShown");
	var _listContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("listContainer");
	var _expandedListContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expandedListContainer");
	var _reactions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reactions");
	var _allowedReactions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowedReactions");
	var _ranking = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ranking");
	var _mouseenterHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mouseenterHandler");
	var _mouseleaveHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mouseleaveHandler");
	var _rowSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rowSize");
	var _minHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minHeight");
	var _offsetBetweenTargetElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("offsetBetweenTargetElement");
	var _adjustPositionToElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustPositionToElement");
	var _adjustPositionToCoordinates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustPositionToCoordinates");
	var _initPopover = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPopover");
	var _renderReactionsList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderReactionsList");
	var _renderReactionElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderReactionElement");
	var _renderExpandButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpandButton");
	var _destroyPopover = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroyPopover");
	var _destroyReactions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroyReactions");
	var _expand = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expand");
	var _isEnoughSpaceBelowTargetElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEnoughSpaceBelowTargetElement");
	var _isEnoughSpaceAboveTargetElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEnoughSpaceAboveTargetElement");
	var _getReactionsNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getReactionsNames");
	class ReactionPicker extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _getReactionsNames, {
	      value: _getReactionsNames2
	    });
	    Object.defineProperty(this, _isEnoughSpaceAboveTargetElement, {
	      value: _isEnoughSpaceAboveTargetElement2
	    });
	    Object.defineProperty(this, _isEnoughSpaceBelowTargetElement, {
	      value: _isEnoughSpaceBelowTargetElement2
	    });
	    Object.defineProperty(this, _expand, {
	      value: _expand2
	    });
	    Object.defineProperty(this, _destroyReactions, {
	      value: _destroyReactions2
	    });
	    Object.defineProperty(this, _destroyPopover, {
	      value: _destroyPopover2
	    });
	    Object.defineProperty(this, _renderExpandButton, {
	      value: _renderExpandButton2
	    });
	    Object.defineProperty(this, _renderReactionElement, {
	      value: _renderReactionElement2
	    });
	    Object.defineProperty(this, _renderReactionsList, {
	      value: _renderReactionsList2
	    });
	    Object.defineProperty(this, _initPopover, {
	      value: _initPopover2
	    });
	    Object.defineProperty(this, _adjustPositionToCoordinates, {
	      value: _adjustPositionToCoordinates2
	    });
	    Object.defineProperty(this, _adjustPositionToElement, {
	      value: _adjustPositionToElement2
	    });
	    Object.defineProperty(this, _target, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popover, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isShown, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _listContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _expandedListContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _reactions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _allowedReactions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ranking, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mouseenterHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _mouseleaveHandler, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('UI.ReactionPicker.V2');
	    babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] = options.target;
	    babelHelpers.classPrivateFieldLooseBase(this, _allowedReactions)[_allowedReactions] = main_core.Type.isArrayFilled(options.reactions) ? options.reactions : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _listContainer)[_listContainer] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _expandedListContainer)[_expandedListContainer] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _reactions)[_reactions] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _ranking)[_ranking] = new ReactionPickerRanking();
	  }
	  show() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _initPopover)[_initPopover]();
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], document.body);
	    this.adjustPosition();
	    main_core_zIndexManager.ZIndexManager.register(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]);
	    main_core_zIndexManager.ZIndexManager.bringToFront(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]);
	    requestAnimationFrame(() => {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], '--visible');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown] = true;
	    this.emit(ReactionPickerEvents.show);
	  }
	  hide() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) {
	      return;
	    }
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], '--hiding');
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], '--visible');
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _destroyReactions)[_destroyReactions]();
	      babelHelpers.classPrivateFieldLooseBase(this, _destroyPopover)[_destroyPopover]();
	    }, 150);
	    babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown] = false;
	    this.emit(ReactionPickerEvents.hide);
	  }
	  isShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown];
	  }
	  adjustPosition() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) {
	      return;
	    }
	    if (main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target].top) && main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target].left)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustPositionToCoordinates)[_adjustPositionToCoordinates](babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] instanceof HTMLElement) {
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustPositionToElement)[_adjustPositionToElement](babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	    }
	  }
	  getPopoverRect() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover] ? main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) : undefined;
	  }
	}
	function _adjustPositionToElement2(target) {
	  const elementRect = main_core.Dom.getPosition(target);
	  const popoverRect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]);
	  let top = 0;
	  const isEnoughSpaceBelowTargetElement = babelHelpers.classPrivateFieldLooseBase(this, _isEnoughSpaceBelowTargetElement)[_isEnoughSpaceBelowTargetElement]();
	  if (isEnoughSpaceBelowTargetElement === false) {
	    top = elementRect.top - popoverRect.height - babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _offsetBetweenTargetElement)[_offsetBetweenTargetElement];
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isEnoughSpaceAboveTargetElement)[_isEnoughSpaceAboveTargetElement]()) {
	    top = elementRect.top - babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _minHeight)[_minHeight] - babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _offsetBetweenTargetElement)[_offsetBetweenTargetElement];
	  } else if (isEnoughSpaceBelowTargetElement) {
	    top = elementRect.top + elementRect.height + babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _offsetBetweenTargetElement)[_offsetBetweenTargetElement];
	  }
	  let left = elementRect.left - 53;
	  const rightEdge = left + popoverRect.width;
	  const windowWidth = window.innerWidth;
	  const rightPadding = 40;
	  if (rightEdge + rightPadding > windowWidth) {
	    left = windowWidth - popoverRect.width - rightPadding;
	  }
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], {
	    top: `${top}px`,
	    left: `${left}px`
	  });
	}
	function _adjustPositionToCoordinates2(position) {
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], {
	    top: `${position.top}px`,
	    left: `${position.left}px`
	  });
	}
	function _initPopover2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _listContainer)[_listContainer] = babelHelpers.classPrivateFieldLooseBase(this, _renderReactionsList)[_renderReactionsList](babelHelpers.classPrivateFieldLooseBase(this, _getReactionsNames)[_getReactionsNames]().slice(0, babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _rowSize)[_rowSize] - 1));
	    babelHelpers.classPrivateFieldLooseBase(this, _expandedListContainer)[_expandedListContainer] = main_core.Tag.render(_t || (_t = _`
				<div class="reactions-select-popover__expanded-list"></div>
			`));
	    const showExpandButton = babelHelpers.classPrivateFieldLooseBase(this, _getReactionsNames)[_getReactionsNames]().length > babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _rowSize)[_rowSize];
	    babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover] = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="reactions-select-popover --ui-context-content-light">
					<div class="reactions-select-popover__inner">
						${0}
						${0}
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _listContainer)[_listContainer], showExpandButton ? babelHelpers.classPrivateFieldLooseBase(this, _renderExpandButton)[_renderExpandButton]() : null, babelHelpers.classPrivateFieldLooseBase(this, _expandedListContainer)[_expandedListContainer]);
	    babelHelpers.classPrivateFieldLooseBase(this, _mouseenterHandler)[_mouseenterHandler] = () => {
	      this.emit(ReactionPickerEvents.mouseenter);
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _mouseleaveHandler)[_mouseleaveHandler] = () => {
	      this.emit(ReactionPickerEvents.mouseleave);
	    };
	    main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], 'mouseenter', babelHelpers.classPrivateFieldLooseBase(this, _mouseenterHandler)[_mouseenterHandler]);
	    main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], 'mouseleave', babelHelpers.classPrivateFieldLooseBase(this, _mouseleaveHandler)[_mouseleaveHandler]);
	  }
	}
	function _renderReactionsList2(reactionsIds, isExpanded = false) {
	  const list = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="reactions-select__list"></div>
		`));
	  if (isExpanded) {
	    main_core.Dom.addClass(list, '--expanded');
	  }
	  reactionsIds.forEach(reactionId => {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderReactionElement)[_renderReactionElement](reactionId), list);
	  });
	  return list;
	}
	function _renderReactionElement2(reactionName) {
	  const reactionTitle = ui_reaction_item.ReactionTitle[reactionName];
	  const reaction = new ui_reaction_item.Reaction({
	    name: reactionName,
	    size: 32,
	    animation: {
	      animate: true,
	      infinite: true
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _reactions)[_reactions].push(reaction);
	  const element = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="reactions-select__list-elem" title="${0}">
				<div ref="inner" class="reactions-select__list-elem-inner">
					${0}
				</div>
			</div>
		`), reactionTitle, reaction.render());
	  main_core.bind(element.inner, 'mouseenter', () => {
	    // reaction.playAnimation(true);
	  });
	  main_core.bind(element.inner, 'mouseleave', () => {
	    // reaction.pauseAnimation(false);
	  });
	  main_core.bind(element.root, 'click', () => {
	    this.emit(ReactionPickerEvents.select, {
	      reaction: reactionName
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _ranking)[_ranking].incrementReactionCounter(reactionName);
	  });
	  return element.root;
	}
	function _renderExpandButton2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Outline.CHEVRON_DOWN_M,
	    size: 24
	  });
	  const button = main_core.Tag.render(_t5 || (_t5 = _`
			<button
				class="reactions-select__expand-button --ui-hoverable"
				title="${0}"
			>
				${0}
			</button>
		`), main_core.Loc.getMessage('UI_REACTIONS_LIST_EXPAND_BUTTON_TITLE'), icon.render());
	  const wrapper = main_core.Tag.render(_t6 || (_t6 = _`
			<div class="reactions-select__expand-button-wrapper">
				${0}
			</div>
		`), button);
	  main_core.bind(button, 'click', () => {
	    main_core.Dom.remove(wrapper);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderReactionElement)[_renderReactionElement](babelHelpers.classPrivateFieldLooseBase(this, _getReactionsNames)[_getReactionsNames]()[babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _rowSize)[_rowSize] - 1]), babelHelpers.classPrivateFieldLooseBase(this, _listContainer)[_listContainer]);
	    babelHelpers.classPrivateFieldLooseBase(this, _expand)[_expand]();
	  });
	  return wrapper;
	}
	function _destroyPopover2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _mouseenterHandler)[_mouseenterHandler]) {
	    main_core.unbind(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], 'mouseenter', babelHelpers.classPrivateFieldLooseBase(this, _mouseenterHandler)[_mouseenterHandler]);
	    babelHelpers.classPrivateFieldLooseBase(this, _mouseenterHandler)[_mouseenterHandler] = null;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _mouseleaveHandler)[_mouseleaveHandler]) {
	    main_core.unbind(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], 'mouseleave', babelHelpers.classPrivateFieldLooseBase(this, _mouseleaveHandler)[_mouseleaveHandler]);
	    babelHelpers.classPrivateFieldLooseBase(this, _mouseleaveHandler)[_mouseleaveHandler] = null;
	  }
	  main_core_zIndexManager.ZIndexManager.unregister(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]);
	  babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover].remove();
	  babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _listContainer)[_listContainer] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _expandedListContainer)[_expandedListContainer] = null;
	}
	function _destroyReactions2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _reactions)[_reactions].forEach(reaction => {
	    reaction.destroy();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _reactions)[_reactions] = [];
	}
	function _expand2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover], '--expanded');
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderReactionsList)[_renderReactionsList](babelHelpers.classPrivateFieldLooseBase(this, _getReactionsNames)[_getReactionsNames]().slice(babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _rowSize)[_rowSize]), true), babelHelpers.classPrivateFieldLooseBase(this, _expandedListContainer)[_expandedListContainer]);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isEnoughSpaceBelowTargetElement)[_isEnoughSpaceBelowTargetElement]() === false) {
	    this.adjustPosition();
	  }
	}
	function _isEnoughSpaceBelowTargetElement2() {
	  const popoverRect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _popover)[_popover]);
	  const targetRect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	  return targetRect.bottom + popoverRect.height + babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _offsetBetweenTargetElement)[_offsetBetweenTargetElement] < window.innerHeight;
	}
	function _isEnoughSpaceAboveTargetElement2() {
	  const elementRect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	  return elementRect.top - babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _minHeight)[_minHeight] - window.scrollY - babelHelpers.classPrivateFieldLooseBase(ReactionPicker, _offsetBetweenTargetElement)[_offsetBetweenTargetElement] > 0;
	}
	function _getReactionsNames2() {
	  const rankedReactions = babelHelpers.classPrivateFieldLooseBase(this, _ranking)[_ranking].getRankedReactionsNames();
	  const excludedReactions = new Set([ui_reaction_item.ReactionName.signHorns, ui_reaction_item.ReactionName.faceWithStuckOutTongue, ui_reaction_item.ReactionName.handshake, ui_reaction_item.ReactionName.hundredPoints, ui_reaction_item.ReactionName.sleepingSymbol, ui_reaction_item.ReactionName.crossMark, ui_reaction_item.ReactionName.whiteHeavyCheckMark, ui_reaction_item.ReactionName.eyes]);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _allowedReactions)[_allowedReactions]) {
	    return rankedReactions.filter(reaction => babelHelpers.classPrivateFieldLooseBase(this, _allowedReactions)[_allowedReactions].includes(reaction));
	  }
	  return rankedReactions.filter(reaction => !excludedReactions.has(reaction));
	}
	Object.defineProperty(ReactionPicker, _rowSize, {
	  writable: true,
	  value: 7
	});
	Object.defineProperty(ReactionPicker, _minHeight, {
	  writable: true,
	  value: 56
	});
	Object.defineProperty(ReactionPicker, _offsetBetweenTargetElement, {
	  writable: true,
	  value: 8
	});

	exports.ReactionPickerEvents = ReactionPickerEvents;
	exports.ReactionPicker = ReactionPicker;

}((this.BX.UI.Reaction.Picker = this.BX.UI.Reaction.Picker || {}),BX.Event,BX,BX.UI.IconSet,BX,BX,BX.UI.Reaction.Item));
//# sourceMappingURL=reaction-picker.bundle.js.map
