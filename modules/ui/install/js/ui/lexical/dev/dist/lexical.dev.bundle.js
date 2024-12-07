/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports) {
  'use strict';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function createCommand(type) {
    return {
      type
    };
  }
  const SELECTION_CHANGE_COMMAND = createCommand('SELECTION_CHANGE_COMMAND');
  const SELECTION_INSERT_CLIPBOARD_NODES_COMMAND = createCommand('SELECTION_INSERT_CLIPBOARD_NODES_COMMAND');
  const CLICK_COMMAND = createCommand('CLICK_COMMAND');
  const DELETE_CHARACTER_COMMAND = createCommand('DELETE_CHARACTER_COMMAND');
  const INSERT_LINE_BREAK_COMMAND = createCommand('INSERT_LINE_BREAK_COMMAND');
  const INSERT_PARAGRAPH_COMMAND = createCommand('INSERT_PARAGRAPH_COMMAND');
  const CONTROLLED_TEXT_INSERTION_COMMAND = createCommand('CONTROLLED_TEXT_INSERTION_COMMAND');
  const PASTE_COMMAND = createCommand('PASTE_COMMAND');
  const REMOVE_TEXT_COMMAND = createCommand('REMOVE_TEXT_COMMAND');
  const DELETE_WORD_COMMAND = createCommand('DELETE_WORD_COMMAND');
  const DELETE_LINE_COMMAND = createCommand('DELETE_LINE_COMMAND');
  const FORMAT_TEXT_COMMAND = createCommand('FORMAT_TEXT_COMMAND');
  const UNDO_COMMAND = createCommand('UNDO_COMMAND');
  const REDO_COMMAND = createCommand('REDO_COMMAND');
  const KEY_DOWN_COMMAND = createCommand('KEYDOWN_COMMAND');
  const KEY_ARROW_RIGHT_COMMAND = createCommand('KEY_ARROW_RIGHT_COMMAND');
  const MOVE_TO_END = createCommand('MOVE_TO_END');
  const KEY_ARROW_LEFT_COMMAND = createCommand('KEY_ARROW_LEFT_COMMAND');
  const MOVE_TO_START = createCommand('MOVE_TO_START');
  const KEY_ARROW_UP_COMMAND = createCommand('KEY_ARROW_UP_COMMAND');
  const KEY_ARROW_DOWN_COMMAND = createCommand('KEY_ARROW_DOWN_COMMAND');
  const KEY_ENTER_COMMAND = createCommand('KEY_ENTER_COMMAND');
  const KEY_SPACE_COMMAND = createCommand('KEY_SPACE_COMMAND');
  const KEY_BACKSPACE_COMMAND = createCommand('KEY_BACKSPACE_COMMAND');
  const KEY_ESCAPE_COMMAND = createCommand('KEY_ESCAPE_COMMAND');
  const KEY_DELETE_COMMAND = createCommand('KEY_DELETE_COMMAND');
  const KEY_TAB_COMMAND = createCommand('KEY_TAB_COMMAND');
  const INSERT_TAB_COMMAND = createCommand('INSERT_TAB_COMMAND');
  const INDENT_CONTENT_COMMAND = createCommand('INDENT_CONTENT_COMMAND');
  const OUTDENT_CONTENT_COMMAND = createCommand('OUTDENT_CONTENT_COMMAND');
  const DROP_COMMAND = createCommand('DROP_COMMAND');
  const FORMAT_ELEMENT_COMMAND = createCommand('FORMAT_ELEMENT_COMMAND');
  const DRAGSTART_COMMAND = createCommand('DRAGSTART_COMMAND');
  const DRAGOVER_COMMAND = createCommand('DRAGOVER_COMMAND');
  const DRAGEND_COMMAND = createCommand('DRAGEND_COMMAND');
  const COPY_COMMAND = createCommand('COPY_COMMAND');
  const CUT_COMMAND = createCommand('CUT_COMMAND');
  const SELECT_ALL_COMMAND = createCommand('SELECT_ALL_COMMAND');
  const CLEAR_EDITOR_COMMAND = createCommand('CLEAR_EDITOR_COMMAND');
  const CLEAR_HISTORY_COMMAND = createCommand('CLEAR_HISTORY_COMMAND');
  const CAN_REDO_COMMAND = createCommand('CAN_REDO_COMMAND');
  const CAN_UNDO_COMMAND = createCommand('CAN_UNDO_COMMAND');
  const FOCUS_COMMAND = createCommand('FOCUS_COMMAND');
  const BLUR_COMMAND = createCommand('BLUR_COMMAND');
  const KEY_MODIFIER_COMMAND = createCommand('KEY_MODIFIER_COMMAND');

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const CAN_USE_DOM = typeof window !== 'undefined' && typeof window.document !== 'undefined' && typeof window.document.createElement !== 'undefined';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const documentMode = CAN_USE_DOM && 'documentMode' in document ? document.documentMode : null;
  const IS_APPLE = CAN_USE_DOM && /Mac|iPod|iPhone|iPad/.test(navigator.platform);
  const IS_FIREFOX = CAN_USE_DOM && /^(?!.*Seamonkey)(?=.*Firefox).*/i.test(navigator.userAgent);
  const CAN_USE_BEFORE_INPUT = CAN_USE_DOM && 'InputEvent' in window && !documentMode ? 'getTargetRanges' in new window.InputEvent('input') : false;
  const IS_SAFARI = CAN_USE_DOM && /Version\/[\d.]+.*Safari/.test(navigator.userAgent);
  const IS_IOS = CAN_USE_DOM && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  const IS_ANDROID = CAN_USE_DOM && /Android/.test(navigator.userAgent);

  // Keep these in case we need to use them in the future.
  // export const IS_WINDOWS: boolean = CAN_USE_DOM && /Win/.test(navigator.platform);
  const IS_CHROME = CAN_USE_DOM && /^(?=.*Chrome).*/i.test(navigator.userAgent);
  // export const canUseTextInputEvent: boolean = CAN_USE_DOM && 'TextEvent' in window && !documentMode;

  const IS_ANDROID_CHROME = CAN_USE_DOM && IS_ANDROID && IS_CHROME;
  const IS_APPLE_WEBKIT = CAN_USE_DOM && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !IS_CHROME;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // DOM
  const DOM_ELEMENT_TYPE = 1;
  const DOM_TEXT_TYPE = 3;

  // Reconciling
  const NO_DIRTY_NODES = 0;
  const HAS_DIRTY_NODES = 1;
  const FULL_RECONCILE = 2;

  // Text node modes
  const IS_NORMAL = 0;
  const IS_TOKEN = 1;
  const IS_SEGMENTED = 2;
  // IS_INERT = 3

  // Text node formatting
  const IS_BOLD = 1;
  const IS_ITALIC = 1 << 1;
  const IS_STRIKETHROUGH = 1 << 2;
  const IS_UNDERLINE = 1 << 3;
  const IS_CODE = 1 << 4;
  const IS_SUBSCRIPT = 1 << 5;
  const IS_SUPERSCRIPT = 1 << 6;
  const IS_HIGHLIGHT = 1 << 7;
  const IS_ALL_FORMATTING = IS_BOLD | IS_ITALIC | IS_STRIKETHROUGH | IS_UNDERLINE | IS_CODE | IS_SUBSCRIPT | IS_SUPERSCRIPT | IS_HIGHLIGHT;

  // Text node details
  const IS_DIRECTIONLESS = 1;
  const IS_UNMERGEABLE = 1 << 1;

  // Element node formatting
  const IS_ALIGN_LEFT = 1;
  const IS_ALIGN_CENTER = 2;
  const IS_ALIGN_RIGHT = 3;
  const IS_ALIGN_JUSTIFY = 4;
  const IS_ALIGN_START = 5;
  const IS_ALIGN_END = 6;

  // Reconciliation
  const NON_BREAKING_SPACE = '\u00A0';
  const ZERO_WIDTH_SPACE = '\u200b';

  // For iOS/Safari we use a non breaking space, otherwise the cursor appears
  // overlapping the composed text.
  const COMPOSITION_SUFFIX = IS_SAFARI || IS_IOS || IS_APPLE_WEBKIT ? NON_BREAKING_SPACE : ZERO_WIDTH_SPACE;
  const DOUBLE_LINE_BREAK = '\n\n';

  // For FF, we need to use a non-breaking space, or it gets composition
  // in a stuck state.
  const COMPOSITION_START_CHAR = IS_FIREFOX ? NON_BREAKING_SPACE : COMPOSITION_SUFFIX;
  const RTL = '\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC';
  const LTR = 'A-Za-z\u00C0-\u00D6\u00D8-\u00F6' + '\u00F8-\u02B8\u0300-\u0590\u0800-\u1FFF\u200E\u2C00-\uFB1C' + '\uFE00-\uFE6F\uFEFD-\uFFFF';

  // eslint-disable-next-line no-misleading-character-class
  const RTL_REGEX = new RegExp('^[^' + LTR + ']*[' + RTL + ']');
  // eslint-disable-next-line no-misleading-character-class
  const LTR_REGEX = new RegExp('^[^' + RTL + ']*[' + LTR + ']');
  const TEXT_TYPE_TO_FORMAT = {
    bold: IS_BOLD,
    code: IS_CODE,
    highlight: IS_HIGHLIGHT,
    italic: IS_ITALIC,
    strikethrough: IS_STRIKETHROUGH,
    subscript: IS_SUBSCRIPT,
    superscript: IS_SUPERSCRIPT,
    underline: IS_UNDERLINE
  };
  const DETAIL_TYPE_TO_DETAIL = {
    directionless: IS_DIRECTIONLESS,
    unmergeable: IS_UNMERGEABLE
  };
  const ELEMENT_TYPE_TO_FORMAT = {
    center: IS_ALIGN_CENTER,
    end: IS_ALIGN_END,
    justify: IS_ALIGN_JUSTIFY,
    left: IS_ALIGN_LEFT,
    right: IS_ALIGN_RIGHT,
    start: IS_ALIGN_START
  };
  const ELEMENT_FORMAT_TO_TYPE = {
    [IS_ALIGN_CENTER]: 'center',
    [IS_ALIGN_END]: 'end',
    [IS_ALIGN_JUSTIFY]: 'justify',
    [IS_ALIGN_LEFT]: 'left',
    [IS_ALIGN_RIGHT]: 'right',
    [IS_ALIGN_START]: 'start'
  };
  const TEXT_MODE_TO_TYPE = {
    normal: IS_NORMAL,
    segmented: IS_SEGMENTED,
    token: IS_TOKEN
  };
  const TEXT_TYPE_TO_MODE = {
    [IS_NORMAL]: 'normal',
    [IS_SEGMENTED]: 'segmented',
    [IS_TOKEN]: 'token'
  };

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function normalizeClassNames(...classNames) {
    const rval = [];
    for (const className of classNames) {
      if (className && typeof className === 'string') {
        for (const [s] of className.matchAll(/\S+/g)) {
          rval.push(s);
        }
      }
    }
    return rval;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // The time between a text entry event and the mutation observer firing.
  const TEXT_MUTATION_VARIANCE = 100;
  let isProcessingMutations = false;
  let lastTextEntryTimeStamp = 0;
  function getIsProcessingMutations() {
    return isProcessingMutations;
  }
  function updateTimeStamp(event) {
    lastTextEntryTimeStamp = event.timeStamp;
  }
  function initTextEntryListener(editor) {
    if (lastTextEntryTimeStamp === 0) {
      getWindow(editor).addEventListener('textInput', updateTimeStamp, true);
    }
  }
  function isManagedLineBreak(dom, target, editor) {
    return (
      // @ts-expect-error: internal field
      target.__lexicalLineBreak === dom ||
      // @ts-ignore We intentionally add this to the Node.
      dom[`__lexicalKey_${editor._key}`] !== undefined
    );
  }
  function getLastSelection(editor) {
    return editor.getEditorState().read(() => {
      const selection = $getSelection();
      return selection !== null ? selection.clone() : null;
    });
  }
  function $handleTextMutation(target, node, editor) {
    const domSelection = getDOMSelection(editor._window);
    let anchorOffset = null;
    let focusOffset = null;
    if (domSelection !== null && domSelection.anchorNode === target) {
      anchorOffset = domSelection.anchorOffset;
      focusOffset = domSelection.focusOffset;
    }
    const text = target.nodeValue;
    if (text !== null) {
      $updateTextNodeFromDOMContent(node, text, anchorOffset, focusOffset, false);
    }
  }
  function shouldUpdateTextNodeFromMutation(selection, targetDOM, targetNode) {
    if ($isRangeSelection(selection)) {
      const anchorNode = selection.anchor.getNode();
      if (anchorNode.is(targetNode) && selection.format !== anchorNode.getFormat()) {
        return false;
      }
    }
    return targetDOM.nodeType === DOM_TEXT_TYPE && targetNode.isAttached();
  }
  function $flushMutations$1(editor, mutations, observer) {
    isProcessingMutations = true;
    const shouldFlushTextMutations = performance.now() - lastTextEntryTimeStamp > TEXT_MUTATION_VARIANCE;
    try {
      updateEditor(editor, () => {
        const selection = $getSelection() || getLastSelection(editor);
        const badDOMTargets = new Map();
        const rootElement = editor.getRootElement();
        // We use the current editor state, as that reflects what is
        // actually "on screen".
        const currentEditorState = editor._editorState;
        const blockCursorElement = editor._blockCursorElement;
        let shouldRevertSelection = false;
        let possibleTextForFirefoxPaste = '';
        for (let i = 0; i < mutations.length; i++) {
          const mutation = mutations[i];
          const type = mutation.type;
          const targetDOM = mutation.target;
          let targetNode = $getNearestNodeFromDOMNode(targetDOM, currentEditorState);
          if (targetNode === null && targetDOM !== rootElement || $isDecoratorNode(targetNode)) {
            continue;
          }
          if (type === 'characterData') {
            // Text mutations are deferred and passed to mutation listeners to be
            // processed outside of the Lexical engine.
            if (shouldFlushTextMutations && $isTextNode(targetNode) && shouldUpdateTextNodeFromMutation(selection, targetDOM, targetNode)) {
              $handleTextMutation(
              // nodeType === DOM_TEXT_TYPE is a Text DOM node
              targetDOM, targetNode, editor);
            }
          } else if (type === 'childList') {
            shouldRevertSelection = true;
            // We attempt to "undo" any changes that have occurred outside
            // of Lexical. We want Lexical's editor state to be source of truth.
            // To the user, these will look like no-ops.
            const addedDOMs = mutation.addedNodes;
            for (let s = 0; s < addedDOMs.length; s++) {
              const addedDOM = addedDOMs[s];
              const node = $getNodeFromDOMNode(addedDOM);
              const parentDOM = addedDOM.parentNode;
              if (parentDOM != null && addedDOM !== blockCursorElement && node === null && (addedDOM.nodeName !== 'BR' || !isManagedLineBreak(addedDOM, parentDOM, editor))) {
                if (IS_FIREFOX) {
                  const possibleText = addedDOM.innerText || addedDOM.nodeValue;
                  if (possibleText) {
                    possibleTextForFirefoxPaste += possibleText;
                  }
                }
                parentDOM.removeChild(addedDOM);
              }
            }
            const removedDOMs = mutation.removedNodes;
            const removedDOMsLength = removedDOMs.length;
            if (removedDOMsLength > 0) {
              let unremovedBRs = 0;
              for (let s = 0; s < removedDOMsLength; s++) {
                const removedDOM = removedDOMs[s];
                if (removedDOM.nodeName === 'BR' && isManagedLineBreak(removedDOM, targetDOM, editor) || blockCursorElement === removedDOM) {
                  targetDOM.appendChild(removedDOM);
                  unremovedBRs++;
                }
              }
              if (removedDOMsLength !== unremovedBRs) {
                if (targetDOM === rootElement) {
                  targetNode = internalGetRoot(currentEditorState);
                }
                badDOMTargets.set(targetDOM, targetNode);
              }
            }
          }
        }

        // Now we process each of the unique target nodes, attempting
        // to restore their contents back to the source of truth, which
        // is Lexical's "current" editor state. This is basically like
        // an internal revert on the DOM.
        if (badDOMTargets.size > 0) {
          for (const [targetDOM, targetNode] of badDOMTargets) {
            if ($isElementNode(targetNode)) {
              const childKeys = targetNode.getChildrenKeys();
              let currentDOM = targetDOM.firstChild;
              for (let s = 0; s < childKeys.length; s++) {
                const key = childKeys[s];
                const correctDOM = editor.getElementByKey(key);
                if (correctDOM === null) {
                  continue;
                }
                if (currentDOM == null) {
                  targetDOM.appendChild(correctDOM);
                  currentDOM = correctDOM;
                } else if (currentDOM !== correctDOM) {
                  targetDOM.replaceChild(correctDOM, currentDOM);
                }
                currentDOM = currentDOM.nextSibling;
              }
            } else if ($isTextNode(targetNode)) {
              targetNode.markDirty();
            }
          }
        }

        // Capture all the mutations made during this function. This
        // also prevents us having to process them on the next cycle
        // of onMutation, as these mutations were made by us.
        const records = observer.takeRecords();

        // Check for any random auto-added <br> elements, and remove them.
        // These get added by the browser when we undo the above mutations
        // and this can lead to a broken UI.
        if (records.length > 0) {
          for (let i = 0; i < records.length; i++) {
            const record = records[i];
            const addedNodes = record.addedNodes;
            const target = record.target;
            for (let s = 0; s < addedNodes.length; s++) {
              const addedDOM = addedNodes[s];
              const parentDOM = addedDOM.parentNode;
              if (parentDOM != null && addedDOM.nodeName === 'BR' && !isManagedLineBreak(addedDOM, target, editor)) {
                parentDOM.removeChild(addedDOM);
              }
            }
          }

          // Clear any of those removal mutations
          observer.takeRecords();
        }
        if (selection !== null) {
          if (shouldRevertSelection) {
            selection.dirty = true;
            $setSelection(selection);
          }
          if (IS_FIREFOX && isFirefoxClipboardEvents(editor)) {
            selection.insertRawText(possibleTextForFirefoxPaste);
          }
        }
      });
    } finally {
      isProcessingMutations = false;
    }
  }
  function $flushRootMutations(editor) {
    const observer = editor._observer;
    if (observer !== null) {
      const mutations = observer.takeRecords();
      $flushMutations$1(editor, mutations, observer);
    }
  }
  function initMutationObserver(editor) {
    initTextEntryListener(editor);
    editor._observer = new MutationObserver((mutations, observer) => {
      $flushMutations$1(editor, mutations, observer);
    });
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function $canSimpleTextNodesBeMerged(node1, node2) {
    const node1Mode = node1.__mode;
    const node1Format = node1.__format;
    const node1Style = node1.__style;
    const node2Mode = node2.__mode;
    const node2Format = node2.__format;
    const node2Style = node2.__style;
    return (node1Mode === null || node1Mode === node2Mode) && (node1Format === null || node1Format === node2Format) && (node1Style === null || node1Style === node2Style);
  }
  function $mergeTextNodes(node1, node2) {
    const writableNode1 = node1.mergeWithSibling(node2);
    const normalizedNodes = getActiveEditor()._normalizedNodes;
    normalizedNodes.add(node1.__key);
    normalizedNodes.add(node2.__key);
    return writableNode1;
  }
  function $normalizeTextNode(textNode) {
    let node = textNode;
    if (node.__text === '' && node.isSimpleText() && !node.isUnmergeable()) {
      node.remove();
      return;
    }

    // Backward
    let previousNode;
    while ((previousNode = node.getPreviousSibling()) !== null && $isTextNode(previousNode) && previousNode.isSimpleText() && !previousNode.isUnmergeable()) {
      if (previousNode.__text === '') {
        previousNode.remove();
      } else if ($canSimpleTextNodesBeMerged(previousNode, node)) {
        node = $mergeTextNodes(previousNode, node);
        break;
      } else {
        break;
      }
    }

    // Forward
    let nextNode;
    while ((nextNode = node.getNextSibling()) !== null && $isTextNode(nextNode) && nextNode.isSimpleText() && !nextNode.isUnmergeable()) {
      if (nextNode.__text === '') {
        nextNode.remove();
      } else if ($canSimpleTextNodesBeMerged(node, nextNode)) {
        node = $mergeTextNodes(node, nextNode);
        break;
      } else {
        break;
      }
    }
  }
  function $normalizeSelection(selection) {
    $normalizePoint(selection.anchor);
    $normalizePoint(selection.focus);
    return selection;
  }
  function $normalizePoint(point) {
    while (point.type === 'element') {
      const node = point.getNode();
      const offset = point.offset;
      let nextNode;
      let nextOffsetAtEnd;
      if (offset === node.getChildrenSize()) {
        nextNode = node.getChildAtIndex(offset - 1);
        nextOffsetAtEnd = true;
      } else {
        nextNode = node.getChildAtIndex(offset);
        nextOffsetAtEnd = false;
      }
      if ($isTextNode(nextNode)) {
        point.set(nextNode.__key, nextOffsetAtEnd ? nextNode.getTextContentSize() : 0, 'text');
        break;
      } else if (!$isElementNode(nextNode)) {
        break;
      }
      point.set(nextNode.__key, nextOffsetAtEnd ? nextNode.getChildrenSize() : 0, 'element');
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  let keyCounter = 1;
  function resetRandomKey() {
    keyCounter = 1;
  }
  function generateRandomKey() {
    return '' + keyCounter++;
  }
  function getRegisteredNodeOrThrow(editor, nodeType) {
    const registeredNode = editor._nodes.get(nodeType);
    if (registeredNode === undefined) {
      {
        throw Error(`registeredNode: Type ${nodeType} not found`);
      }
    }
    return registeredNode;
  }
  const scheduleMicroTask = typeof queueMicrotask === 'function' ? queueMicrotask : fn => {
    // No window prefix intended (#1400)
    Promise.resolve().then(fn);
  };
  function $isSelectionCapturedInDecorator(node) {
    return $isDecoratorNode($getNearestNodeFromDOMNode(node));
  }
  function isSelectionCapturedInDecoratorInput(anchorDOM) {
    const activeElement = document.activeElement;
    if (activeElement === null) {
      return false;
    }
    const nodeName = activeElement.nodeName;
    return $isDecoratorNode($getNearestNodeFromDOMNode(anchorDOM)) && (nodeName === 'INPUT' || nodeName === 'TEXTAREA' || activeElement.contentEditable === 'true' && getEditorPropertyFromDOMNode(activeElement) == null);
  }
  function isSelectionWithinEditor(editor, anchorDOM, focusDOM) {
    const rootElement = editor.getRootElement();
    try {
      return rootElement !== null && rootElement.contains(anchorDOM) && rootElement.contains(focusDOM) &&
      // Ignore if selection is within nested editor
      anchorDOM !== null && !isSelectionCapturedInDecoratorInput(anchorDOM) && getNearestEditorFromDOMNode(anchorDOM) === editor;
    } catch (error) {
      return false;
    }
  }

  /**
   * @returns true if the given argument is a LexicalEditor instance from this build of Lexical
   */
  function isLexicalEditor(editor) {
    // Check instanceof to prevent issues with multiple embedded Lexical installations
    return editor instanceof LexicalEditor;
  }
  function getNearestEditorFromDOMNode(node) {
    let currentNode = node;
    while (currentNode != null) {
      const editor = getEditorPropertyFromDOMNode(currentNode);
      if (isLexicalEditor(editor)) {
        return editor;
      }
      currentNode = getParentElement(currentNode);
    }
    return null;
  }

  /** @internal */
  function getEditorPropertyFromDOMNode(node) {
    // @ts-expect-error: internal field
    return node ? node.__lexicalEditor : null;
  }
  function getTextDirection(text) {
    if (RTL_REGEX.test(text)) {
      return 'rtl';
    }
    if (LTR_REGEX.test(text)) {
      return 'ltr';
    }
    return null;
  }
  function $isTokenOrSegmented(node) {
    return node.isToken() || node.isSegmented();
  }
  function isDOMNodeLexicalTextNode(node) {
    return node.nodeType === DOM_TEXT_TYPE;
  }
  function getDOMTextNode(element) {
    let node = element;
    while (node != null) {
      if (isDOMNodeLexicalTextNode(node)) {
        return node;
      }
      node = node.firstChild;
    }
    return null;
  }
  function toggleTextFormatType(format, type, alignWithFormat) {
    const activeFormat = TEXT_TYPE_TO_FORMAT[type];
    if (alignWithFormat !== null && (format & activeFormat) === (alignWithFormat & activeFormat)) {
      return format;
    }
    let newFormat = format ^ activeFormat;
    if (type === 'subscript') {
      newFormat &= ~TEXT_TYPE_TO_FORMAT.superscript;
    } else if (type === 'superscript') {
      newFormat &= ~TEXT_TYPE_TO_FORMAT.subscript;
    }
    return newFormat;
  }
  function $isLeafNode(node) {
    return $isTextNode(node) || $isLineBreakNode(node) || $isDecoratorNode(node);
  }
  function $setNodeKey(node, existingKey) {
    if (existingKey != null) {
      {
        errorOnNodeKeyConstructorMismatch(node, existingKey);
      }
      node.__key = existingKey;
      return;
    }
    errorOnReadOnly();
    errorOnInfiniteTransforms();
    const editor = getActiveEditor();
    const editorState = getActiveEditorState();
    const key = generateRandomKey();
    editorState._nodeMap.set(key, node);
    // TODO Split this function into leaf/element
    if ($isElementNode(node)) {
      editor._dirtyElements.set(key, true);
    } else {
      editor._dirtyLeaves.add(key);
    }
    editor._cloneNotNeeded.add(key);
    editor._dirtyType = HAS_DIRTY_NODES;
    node.__key = key;
  }
  function errorOnNodeKeyConstructorMismatch(node, existingKey) {
    const editorState = internalGetActiveEditorState();
    if (!editorState) {
      // tests expect to be able to do this kind of clone without an active editor state
      return;
    }
    const existingNode = editorState._nodeMap.get(existingKey);
    if (existingNode && existingNode.constructor !== node.constructor) {
      // Lifted condition to if statement because the inverted logic is a bit confusing
      if (node.constructor.name !== existingNode.constructor.name) {
        {
          throw Error(`Lexical node with constructor ${node.constructor.name} attempted to re-use key from node in active editor state with constructor ${existingNode.constructor.name}. Keys must not be re-used when the type is changed.`);
        }
      } else {
        {
          throw Error(`Lexical node with constructor ${node.constructor.name} attempted to re-use key from node in active editor state with different constructor with the same name (possibly due to invalid Hot Module Replacement). Keys must not be re-used when the type is changed.`);
        }
      }
    }
  }
  function internalMarkParentElementsAsDirty(parentKey, nodeMap, dirtyElements) {
    let nextParentKey = parentKey;
    while (nextParentKey !== null) {
      if (dirtyElements.has(nextParentKey)) {
        return;
      }
      const node = nodeMap.get(nextParentKey);
      if (node === undefined) {
        break;
      }
      dirtyElements.set(nextParentKey, false);
      nextParentKey = node.__parent;
    }
  }

  // TODO #6031 this function or their callers have to adjust selection (i.e. insertBefore)
  function removeFromParent(node) {
    const oldParent = node.getParent();
    if (oldParent !== null) {
      const writableNode = node.getWritable();
      const writableParent = oldParent.getWritable();
      const prevSibling = node.getPreviousSibling();
      const nextSibling = node.getNextSibling();
      // TODO: this function duplicates a bunch of operations, can be simplified.
      if (prevSibling === null) {
        if (nextSibling !== null) {
          const writableNextSibling = nextSibling.getWritable();
          writableParent.__first = nextSibling.__key;
          writableNextSibling.__prev = null;
        } else {
          writableParent.__first = null;
        }
      } else {
        const writablePrevSibling = prevSibling.getWritable();
        if (nextSibling !== null) {
          const writableNextSibling = nextSibling.getWritable();
          writableNextSibling.__prev = writablePrevSibling.__key;
          writablePrevSibling.__next = writableNextSibling.__key;
        } else {
          writablePrevSibling.__next = null;
        }
        writableNode.__prev = null;
      }
      if (nextSibling === null) {
        if (prevSibling !== null) {
          const writablePrevSibling = prevSibling.getWritable();
          writableParent.__last = prevSibling.__key;
          writablePrevSibling.__next = null;
        } else {
          writableParent.__last = null;
        }
      } else {
        const writableNextSibling = nextSibling.getWritable();
        if (prevSibling !== null) {
          const writablePrevSibling = prevSibling.getWritable();
          writablePrevSibling.__next = writableNextSibling.__key;
          writableNextSibling.__prev = writablePrevSibling.__key;
        } else {
          writableNextSibling.__prev = null;
        }
        writableNode.__next = null;
      }
      writableParent.__size--;
      writableNode.__parent = null;
    }
  }

  // Never use this function directly! It will break
  // the cloning heuristic. Instead use node.getWritable().
  function internalMarkNodeAsDirty(node) {
    errorOnInfiniteTransforms();
    const latest = node.getLatest();
    const parent = latest.__parent;
    const editorState = getActiveEditorState();
    const editor = getActiveEditor();
    const nodeMap = editorState._nodeMap;
    const dirtyElements = editor._dirtyElements;
    if (parent !== null) {
      internalMarkParentElementsAsDirty(parent, nodeMap, dirtyElements);
    }
    const key = latest.__key;
    editor._dirtyType = HAS_DIRTY_NODES;
    if ($isElementNode(node)) {
      dirtyElements.set(key, true);
    } else {
      // TODO split internally MarkNodeAsDirty into two dedicated Element/leave functions
      editor._dirtyLeaves.add(key);
    }
  }
  function internalMarkSiblingsAsDirty(node) {
    const previousNode = node.getPreviousSibling();
    const nextNode = node.getNextSibling();
    if (previousNode !== null) {
      internalMarkNodeAsDirty(previousNode);
    }
    if (nextNode !== null) {
      internalMarkNodeAsDirty(nextNode);
    }
  }
  function $setCompositionKey(compositionKey) {
    errorOnReadOnly();
    const editor = getActiveEditor();
    const previousCompositionKey = editor._compositionKey;
    if (compositionKey !== previousCompositionKey) {
      editor._compositionKey = compositionKey;
      if (previousCompositionKey !== null) {
        const node = $getNodeByKey(previousCompositionKey);
        if (node !== null) {
          node.getWritable();
        }
      }
      if (compositionKey !== null) {
        const node = $getNodeByKey(compositionKey);
        if (node !== null) {
          node.getWritable();
        }
      }
    }
  }
  function $getCompositionKey() {
    if (isCurrentlyReadOnlyMode()) {
      return null;
    }
    const editor = getActiveEditor();
    return editor._compositionKey;
  }
  function $getNodeByKey(key, _editorState) {
    const editorState = _editorState || getActiveEditorState();
    const node = editorState._nodeMap.get(key);
    if (node === undefined) {
      return null;
    }
    return node;
  }
  function $getNodeFromDOMNode(dom, editorState) {
    const editor = getActiveEditor();
    // @ts-ignore We intentionally add this to the Node.
    const key = dom[`__lexicalKey_${editor._key}`];
    if (key !== undefined) {
      return $getNodeByKey(key, editorState);
    }
    return null;
  }
  function $getNearestNodeFromDOMNode(startingDOM, editorState) {
    let dom = startingDOM;
    while (dom != null) {
      const node = $getNodeFromDOMNode(dom, editorState);
      if (node !== null) {
        return node;
      }
      dom = getParentElement(dom);
    }
    return null;
  }
  function cloneDecorators(editor) {
    const currentDecorators = editor._decorators;
    const pendingDecorators = Object.assign({}, currentDecorators);
    editor._pendingDecorators = pendingDecorators;
    return pendingDecorators;
  }
  function getEditorStateTextContent(editorState) {
    return editorState.read(() => $getRoot().getTextContent());
  }
  function markAllNodesAsDirty(editor, type) {
    // Mark all existing text nodes as dirty
    updateEditor(editor, () => {
      const editorState = getActiveEditorState();
      if (editorState.isEmpty()) {
        return;
      }
      if (type === 'root') {
        $getRoot().markDirty();
        return;
      }
      const nodeMap = editorState._nodeMap;
      for (const [, node] of nodeMap) {
        node.markDirty();
      }
    }, editor._pendingEditorState === null ? {
      tag: 'history-merge'
    } : undefined);
  }
  function $getRoot() {
    return internalGetRoot(getActiveEditorState());
  }
  function internalGetRoot(editorState) {
    return editorState._nodeMap.get('root');
  }
  function $setSelection(selection) {
    errorOnReadOnly();
    const editorState = getActiveEditorState();
    if (selection !== null) {
      {
        if (Object.isFrozen(selection)) {
          {
            throw Error(`$setSelection called on frozen selection object. Ensure selection is cloned before passing in.`);
          }
        }
      }
      selection.dirty = true;
      selection.setCachedNodes(null);
    }
    editorState._selection = selection;
  }
  function $flushMutations() {
    errorOnReadOnly();
    const editor = getActiveEditor();
    $flushRootMutations(editor);
  }
  function $getNodeFromDOM(dom) {
    const editor = getActiveEditor();
    const nodeKey = getNodeKeyFromDOM(dom, editor);
    if (nodeKey === null) {
      const rootElement = editor.getRootElement();
      if (dom === rootElement) {
        return $getNodeByKey('root');
      }
      return null;
    }
    return $getNodeByKey(nodeKey);
  }
  function getTextNodeOffset(node, moveSelectionToEnd) {
    return moveSelectionToEnd ? node.getTextContentSize() : 0;
  }
  function getNodeKeyFromDOM(
  // Note that node here refers to a DOM Node, not an Lexical Node
  dom, editor) {
    let node = dom;
    while (node != null) {
      // @ts-ignore We intentionally add this to the Node.
      const key = node[`__lexicalKey_${editor._key}`];
      if (key !== undefined) {
        return key;
      }
      node = getParentElement(node);
    }
    return null;
  }
  function doesContainGrapheme(str) {
    return /[\uD800-\uDBFF][\uDC00-\uDFFF]/g.test(str);
  }
  function getEditorsToPropagate(editor) {
    const editorsToPropagate = [];
    let currentEditor = editor;
    while (currentEditor !== null) {
      editorsToPropagate.push(currentEditor);
      currentEditor = currentEditor._parentEditor;
    }
    return editorsToPropagate;
  }
  function createUID() {
    return Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5);
  }
  function getAnchorTextFromDOM(anchorNode) {
    if (anchorNode.nodeType === DOM_TEXT_TYPE) {
      return anchorNode.nodeValue;
    }
    return null;
  }
  function $updateSelectedTextFromDOM(isCompositionEnd, editor, data) {
    // Update the text content with the latest composition text
    const domSelection = getDOMSelection(editor._window);
    if (domSelection === null) {
      return;
    }
    const anchorNode = domSelection.anchorNode;
    let {
      anchorOffset,
      focusOffset
    } = domSelection;
    if (anchorNode !== null) {
      let textContent = getAnchorTextFromDOM(anchorNode);
      const node = $getNearestNodeFromDOMNode(anchorNode);
      if (textContent !== null && $isTextNode(node)) {
        // Data is intentionally truthy, as we check for boolean, null and empty string.
        if (textContent === COMPOSITION_SUFFIX && data) {
          const offset = data.length;
          textContent = data;
          anchorOffset = offset;
          focusOffset = offset;
        }
        if (textContent !== null) {
          $updateTextNodeFromDOMContent(node, textContent, anchorOffset, focusOffset, isCompositionEnd);
        }
      }
    }
  }
  function $updateTextNodeFromDOMContent(textNode, textContent, anchorOffset, focusOffset, compositionEnd) {
    let node = textNode;
    if (node.isAttached() && (compositionEnd || !node.isDirty())) {
      const isComposing = node.isComposing();
      let normalizedTextContent = textContent;
      if ((isComposing || compositionEnd) && textContent[textContent.length - 1] === COMPOSITION_SUFFIX) {
        normalizedTextContent = textContent.slice(0, -1);
      }
      const prevTextContent = node.getTextContent();
      if (compositionEnd || normalizedTextContent !== prevTextContent) {
        if (normalizedTextContent === '') {
          $setCompositionKey(null);
          if (!IS_SAFARI && !IS_IOS && !IS_APPLE_WEBKIT) {
            // For composition (mainly Android), we have to remove the node on a later update
            const editor = getActiveEditor();
            setTimeout(() => {
              editor.update(() => {
                if (node.isAttached()) {
                  node.remove();
                }
              });
            }, 20);
          } else {
            node.remove();
          }
          return;
        }
        const parent = node.getParent();
        const prevSelection = $getPreviousSelection();
        const prevTextContentSize = node.getTextContentSize();
        const compositionKey = $getCompositionKey();
        const nodeKey = node.getKey();
        if (node.isToken() || compositionKey !== null && nodeKey === compositionKey && !isComposing ||
        // Check if character was added at the start or boundaries when not insertable, and we need
        // to clear this input from occurring as that action wasn't permitted.
        $isRangeSelection(prevSelection) && (parent !== null && !parent.canInsertTextBefore() && prevSelection.anchor.offset === 0 || prevSelection.anchor.key === textNode.__key && prevSelection.anchor.offset === 0 && !node.canInsertTextBefore() && !isComposing || prevSelection.focus.key === textNode.__key && prevSelection.focus.offset === prevTextContentSize && !node.canInsertTextAfter() && !isComposing)) {
          node.markDirty();
          return;
        }
        const selection = $getSelection();
        if (!$isRangeSelection(selection) || anchorOffset === null || focusOffset === null) {
          node.setTextContent(normalizedTextContent);
          return;
        }
        selection.setTextNodeRange(node, anchorOffset, node, focusOffset);
        if (node.isSegmented()) {
          const originalTextContent = node.getTextContent();
          const replacement = $createTextNode(originalTextContent);
          node.replace(replacement);
          node = replacement;
        }
        node.setTextContent(normalizedTextContent);
      }
    }
  }
  function $previousSiblingDoesNotAcceptText(node) {
    const previousSibling = node.getPreviousSibling();
    return ($isTextNode(previousSibling) || $isElementNode(previousSibling) && previousSibling.isInline()) && !previousSibling.canInsertTextAfter();
  }

  // This function is connected to $shouldPreventDefaultAndInsertText and determines whether the
  // TextNode boundaries are writable or we should use the previous/next sibling instead. For example,
  // in the case of a LinkNode, boundaries are not writable.
  function $shouldInsertTextAfterOrBeforeTextNode(selection, node) {
    if (node.isSegmented()) {
      return true;
    }
    if (!selection.isCollapsed()) {
      return false;
    }
    const offset = selection.anchor.offset;
    const parent = node.getParentOrThrow();
    const isToken = node.isToken();
    if (offset === 0) {
      return !node.canInsertTextBefore() || !parent.canInsertTextBefore() && !node.isComposing() || isToken || $previousSiblingDoesNotAcceptText(node);
    } else if (offset === node.getTextContentSize()) {
      return !node.canInsertTextAfter() || !parent.canInsertTextAfter() && !node.isComposing() || isToken;
    } else {
      return false;
    }
  }
  function isTab(key, altKey, ctrlKey, metaKey) {
    return key === 'Tab' && !altKey && !ctrlKey && !metaKey;
  }
  function isBold(key, altKey, metaKey, ctrlKey) {
    return key.toLowerCase() === 'b' && !altKey && controlOrMeta(metaKey, ctrlKey);
  }
  function isItalic(key, altKey, metaKey, ctrlKey) {
    return key.toLowerCase() === 'i' && !altKey && controlOrMeta(metaKey, ctrlKey);
  }
  function isUnderline(key, altKey, metaKey, ctrlKey) {
    return key.toLowerCase() === 'u' && !altKey && controlOrMeta(metaKey, ctrlKey);
  }
  function isParagraph(key, shiftKey) {
    return isReturn(key) && !shiftKey;
  }
  function isLineBreak(key, shiftKey) {
    return isReturn(key) && shiftKey;
  }

  // Inserts a new line after the selection

  function isOpenLineBreak(key, ctrlKey) {
    // 79 = KeyO
    return IS_APPLE && ctrlKey && key.toLowerCase() === 'o';
  }
  function isDeleteWordBackward(key, altKey, ctrlKey) {
    return isBackspace(key) && (IS_APPLE ? altKey : ctrlKey);
  }
  function isDeleteWordForward(key, altKey, ctrlKey) {
    return isDelete(key) && (IS_APPLE ? altKey : ctrlKey);
  }
  function isDeleteLineBackward(key, metaKey) {
    return IS_APPLE && metaKey && isBackspace(key);
  }
  function isDeleteLineForward(key, metaKey) {
    return IS_APPLE && metaKey && isDelete(key);
  }
  function isDeleteBackward(key, altKey, metaKey, ctrlKey) {
    if (IS_APPLE) {
      if (altKey || metaKey) {
        return false;
      }
      return isBackspace(key) || key.toLowerCase() === 'h' && ctrlKey;
    }
    if (ctrlKey || altKey || metaKey) {
      return false;
    }
    return isBackspace(key);
  }
  function isDeleteForward(key, ctrlKey, shiftKey, altKey, metaKey) {
    if (IS_APPLE) {
      if (shiftKey || altKey || metaKey) {
        return false;
      }
      return isDelete(key) || key.toLowerCase() === 'd' && ctrlKey;
    }
    if (ctrlKey || altKey || metaKey) {
      return false;
    }
    return isDelete(key);
  }
  function isUndo(key, shiftKey, metaKey, ctrlKey) {
    return key.toLowerCase() === 'z' && !shiftKey && controlOrMeta(metaKey, ctrlKey);
  }
  function isRedo(key, shiftKey, metaKey, ctrlKey) {
    if (IS_APPLE) {
      return key.toLowerCase() === 'z' && metaKey && shiftKey;
    }
    return key.toLowerCase() === 'y' && ctrlKey || key.toLowerCase() === 'z' && ctrlKey && shiftKey;
  }
  function isCopy(key, shiftKey, metaKey, ctrlKey) {
    if (shiftKey) {
      return false;
    }
    if (key.toLowerCase() === 'c') {
      return IS_APPLE ? metaKey : ctrlKey;
    }
    return false;
  }
  function isCut(key, shiftKey, metaKey, ctrlKey) {
    if (shiftKey) {
      return false;
    }
    if (key.toLowerCase() === 'x') {
      return IS_APPLE ? metaKey : ctrlKey;
    }
    return false;
  }
  function isArrowLeft(key) {
    return key === 'ArrowLeft';
  }
  function isArrowRight(key) {
    return key === 'ArrowRight';
  }
  function isArrowUp(key) {
    return key === 'ArrowUp';
  }
  function isArrowDown(key) {
    return key === 'ArrowDown';
  }
  function isMoveBackward(key, ctrlKey, altKey, metaKey) {
    return isArrowLeft(key) && !ctrlKey && !metaKey && !altKey;
  }
  function isMoveToStart(key, ctrlKey, shiftKey, altKey, metaKey) {
    return isArrowLeft(key) && !altKey && !shiftKey && (ctrlKey || metaKey);
  }
  function isMoveForward(key, ctrlKey, altKey, metaKey) {
    return isArrowRight(key) && !ctrlKey && !metaKey && !altKey;
  }
  function isMoveToEnd(key, ctrlKey, shiftKey, altKey, metaKey) {
    return isArrowRight(key) && !altKey && !shiftKey && (ctrlKey || metaKey);
  }
  function isMoveUp(key, ctrlKey, metaKey) {
    return isArrowUp(key) && !ctrlKey && !metaKey;
  }
  function isMoveDown(key, ctrlKey, metaKey) {
    return isArrowDown(key) && !ctrlKey && !metaKey;
  }
  function isModifier(ctrlKey, shiftKey, altKey, metaKey) {
    return ctrlKey || shiftKey || altKey || metaKey;
  }
  function isSpace(key) {
    return key === ' ';
  }
  function controlOrMeta(metaKey, ctrlKey) {
    if (IS_APPLE) {
      return metaKey;
    }
    return ctrlKey;
  }
  function isReturn(key) {
    return key === 'Enter';
  }
  function isBackspace(key) {
    return key === 'Backspace';
  }
  function isEscape(key) {
    return key === 'Escape';
  }
  function isDelete(key) {
    return key === 'Delete';
  }
  function isSelectAll(key, metaKey, ctrlKey) {
    return key.toLowerCase() === 'a' && controlOrMeta(metaKey, ctrlKey);
  }
  function $selectAll() {
    const root = $getRoot();
    const selection = root.select(0, root.getChildrenSize());
    $setSelection($normalizeSelection(selection));
  }
  function getCachedClassNameArray(classNamesTheme, classNameThemeType) {
    if (classNamesTheme.__lexicalClassNameCache === undefined) {
      classNamesTheme.__lexicalClassNameCache = {};
    }
    const classNamesCache = classNamesTheme.__lexicalClassNameCache;
    const cachedClassNames = classNamesCache[classNameThemeType];
    if (cachedClassNames !== undefined) {
      return cachedClassNames;
    }
    const classNames = classNamesTheme[classNameThemeType];
    // As we're using classList, we need
    // to handle className tokens that have spaces.
    // The easiest way to do this to convert the
    // className tokens to an array that can be
    // applied to classList.add()/remove().
    if (typeof classNames === 'string') {
      const classNamesArr = normalizeClassNames(classNames);
      classNamesCache[classNameThemeType] = classNamesArr;
      return classNamesArr;
    }
    return classNames;
  }
  function setMutatedNode(mutatedNodes, registeredNodes, mutationListeners, node, mutation) {
    if (mutationListeners.size === 0) {
      return;
    }
    const nodeType = node.__type;
    const nodeKey = node.__key;
    const registeredNode = registeredNodes.get(nodeType);
    if (registeredNode === undefined) {
      {
        throw Error(`Type ${nodeType} not in registeredNodes`);
      }
    }
    const klass = registeredNode.klass;
    let mutatedNodesByType = mutatedNodes.get(klass);
    if (mutatedNodesByType === undefined) {
      mutatedNodesByType = new Map();
      mutatedNodes.set(klass, mutatedNodesByType);
    }
    const prevMutation = mutatedNodesByType.get(nodeKey);
    // If the node has already been "destroyed", yet we are
    // re-making it, then this means a move likely happened.
    // We should change the mutation to be that of "updated"
    // instead.
    const isMove = prevMutation === 'destroyed' && mutation === 'created';
    if (prevMutation === undefined || isMove) {
      mutatedNodesByType.set(nodeKey, isMove ? 'updated' : mutation);
    }
  }
  function $nodesOfType(klass) {
    const klassType = klass.getType();
    const editorState = getActiveEditorState();
    if (editorState._readOnly) {
      const nodes = getCachedTypeToNodeMap(editorState).get(klassType);
      return nodes ? Array.from(nodes.values()) : [];
    }
    const nodes = editorState._nodeMap;
    const nodesOfType = [];
    for (const [, node] of nodes) {
      if (node instanceof klass && node.__type === klassType && node.isAttached()) {
        nodesOfType.push(node);
      }
    }
    return nodesOfType;
  }
  function resolveElement(element, isBackward, focusOffset) {
    const parent = element.getParent();
    let offset = focusOffset;
    let block = element;
    if (parent !== null) {
      if (isBackward && focusOffset === 0) {
        offset = block.getIndexWithinParent();
        block = parent;
      } else if (!isBackward && focusOffset === block.getChildrenSize()) {
        offset = block.getIndexWithinParent() + 1;
        block = parent;
      }
    }
    return block.getChildAtIndex(isBackward ? offset - 1 : offset);
  }
  function $getAdjacentNode(focus, isBackward) {
    const focusOffset = focus.offset;
    if (focus.type === 'element') {
      const block = focus.getNode();
      return resolveElement(block, isBackward, focusOffset);
    } else {
      const focusNode = focus.getNode();
      if (isBackward && focusOffset === 0 || !isBackward && focusOffset === focusNode.getTextContentSize()) {
        const possibleNode = isBackward ? focusNode.getPreviousSibling() : focusNode.getNextSibling();
        if (possibleNode === null) {
          return resolveElement(focusNode.getParentOrThrow(), isBackward, focusNode.getIndexWithinParent() + (isBackward ? 0 : 1));
        }
        return possibleNode;
      }
    }
    return null;
  }
  function isFirefoxClipboardEvents(editor) {
    const event = getWindow(editor).event;
    const inputType = event && event.inputType;
    return inputType === 'insertFromPaste' || inputType === 'insertFromPasteAsQuotation';
  }
  function dispatchCommand(editor, command, payload) {
    return triggerCommandListeners(editor, command, payload);
  }
  function $textContentRequiresDoubleLinebreakAtEnd(node) {
    return !$isRootNode(node) && !node.isLastChild() && !node.isInline();
  }
  function getElementByKeyOrThrow(editor, key) {
    const element = editor._keyToDOMMap.get(key);
    if (element === undefined) {
      {
        throw Error(`Reconciliation: could not find DOM element for node key ${key}`);
      }
    }
    return element;
  }
  function getParentElement(node) {
    const parentElement = node.assignedSlot || node.parentElement;
    return parentElement !== null && parentElement.nodeType === 11 ? parentElement.host : parentElement;
  }
  function scrollIntoViewIfNeeded(editor, selectionRect, rootElement) {
    const doc = rootElement.ownerDocument;
    const defaultView = doc.defaultView;
    if (defaultView === null) {
      return;
    }
    let {
      top: currentTop,
      bottom: currentBottom
    } = selectionRect;
    let targetTop = 0;
    let targetBottom = 0;
    let element = rootElement;
    while (element !== null) {
      const isBodyElement = element === doc.body;
      if (isBodyElement) {
        targetTop = 0;
        targetBottom = getWindow(editor).innerHeight;
      } else {
        const targetRect = element.getBoundingClientRect();
        targetTop = targetRect.top;
        targetBottom = targetRect.bottom;
      }
      let diff = 0;
      if (currentTop < targetTop) {
        diff = -(targetTop - currentTop);
      } else if (currentBottom > targetBottom) {
        diff = currentBottom - targetBottom;
      }
      if (diff !== 0) {
        if (isBodyElement) {
          // Only handles scrolling of Y axis
          defaultView.scrollBy(0, diff);
        } else {
          const scrollTop = element.scrollTop;
          element.scrollTop += diff;
          const yOffset = element.scrollTop - scrollTop;
          currentTop -= yOffset;
          currentBottom -= yOffset;
        }
      }
      if (isBodyElement) {
        break;
      }
      element = getParentElement(element);
    }
  }
  function $hasUpdateTag(tag) {
    const editor = getActiveEditor();
    return editor._updateTags.has(tag);
  }
  function $addUpdateTag(tag) {
    errorOnReadOnly();
    const editor = getActiveEditor();
    editor._updateTags.add(tag);
  }
  function $maybeMoveChildrenSelectionToParent(parentNode) {
    const selection = $getSelection();
    if (!$isRangeSelection(selection) || !$isElementNode(parentNode)) {
      return selection;
    }
    const {
      anchor,
      focus
    } = selection;
    const anchorNode = anchor.getNode();
    const focusNode = focus.getNode();
    if ($hasAncestor(anchorNode, parentNode)) {
      anchor.set(parentNode.__key, 0, 'element');
    }
    if ($hasAncestor(focusNode, parentNode)) {
      focus.set(parentNode.__key, 0, 'element');
    }
    return selection;
  }
  function $hasAncestor(child, targetNode) {
    let parent = child.getParent();
    while (parent !== null) {
      if (parent.is(targetNode)) {
        return true;
      }
      parent = parent.getParent();
    }
    return false;
  }
  function getDefaultView(domElem) {
    const ownerDoc = domElem.ownerDocument;
    return ownerDoc && ownerDoc.defaultView || null;
  }
  function getWindow(editor) {
    const windowObj = editor._window;
    if (windowObj === null) {
      {
        throw Error(`window object not found`);
      }
    }
    return windowObj;
  }
  function $isInlineElementOrDecoratorNode(node) {
    return $isElementNode(node) && node.isInline() || $isDecoratorNode(node) && node.isInline();
  }
  function $getNearestRootOrShadowRoot(node) {
    let parent = node.getParentOrThrow();
    while (parent !== null) {
      if ($isRootOrShadowRoot(parent)) {
        return parent;
      }
      parent = parent.getParentOrThrow();
    }
    return parent;
  }
  function $isRootOrShadowRoot(node) {
    return $isRootNode(node) || $isElementNode(node) && node.isShadowRoot();
  }

  /**
   * Returns a shallow clone of node with a new key
   *
   * @param node - The node to be copied.
   * @returns The copy of the node.
   */
  function $copyNode(node) {
    const copy = node.constructor.clone(node);
    $setNodeKey(copy, null);
    return copy;
  }
  function $applyNodeReplacement(node) {
    const editor = getActiveEditor();
    const nodeType = node.constructor.getType();
    const registeredNode = editor._nodes.get(nodeType);
    if (registeredNode === undefined) {
      {
        throw Error(`$initializeNode failed. Ensure node has been registered to the editor. You can do this by passing the node class via the "nodes" array in the editor config.`);
      }
    }
    const replaceFunc = registeredNode.replace;
    if (replaceFunc !== null) {
      const replacementNode = replaceFunc(node);
      if (!(replacementNode instanceof node.constructor)) {
        {
          throw Error(`$initializeNode failed. Ensure replacement node is a subclass of the original node.`);
        }
      }
      return replacementNode;
    }
    return node;
  }
  function errorOnInsertTextNodeOnRoot(node, insertNode) {
    const parentNode = node.getParent();
    if ($isRootNode(parentNode) && !$isElementNode(insertNode) && !$isDecoratorNode(insertNode)) {
      {
        throw Error(`Only element or decorator nodes can be inserted in to the root node`);
      }
    }
  }
  function $getNodeByKeyOrThrow(key) {
    const node = $getNodeByKey(key);
    if (node === null) {
      {
        throw Error(`Expected node with key ${key} to exist but it's not in the nodeMap.`);
      }
    }
    return node;
  }
  function createBlockCursorElement(editorConfig) {
    const theme = editorConfig.theme;
    const element = document.createElement('div');
    element.contentEditable = 'false';
    element.setAttribute('data-lexical-cursor', 'true');
    let blockCursorTheme = theme.blockCursor;
    if (blockCursorTheme !== undefined) {
      if (typeof blockCursorTheme === 'string') {
        const classNamesArr = normalizeClassNames(blockCursorTheme);
        // @ts-expect-error: intentional
        blockCursorTheme = theme.blockCursor = classNamesArr;
      }
      if (blockCursorTheme !== undefined) {
        element.classList.add(...blockCursorTheme);
      }
    }
    return element;
  }
  function needsBlockCursor(node) {
    return ($isDecoratorNode(node) || $isElementNode(node) && !node.canBeEmpty()) && !node.isInline();
  }
  function removeDOMBlockCursorElement(blockCursorElement, editor, rootElement) {
    rootElement.style.removeProperty('caret-color');
    editor._blockCursorElement = null;
    const parentElement = blockCursorElement.parentElement;
    if (parentElement !== null) {
      parentElement.removeChild(blockCursorElement);
    }
  }
  function updateDOMBlockCursorElement(editor, rootElement, nextSelection) {
    let blockCursorElement = editor._blockCursorElement;
    if ($isRangeSelection(nextSelection) && nextSelection.isCollapsed() && nextSelection.anchor.type === 'element' && rootElement.contains(document.activeElement)) {
      const anchor = nextSelection.anchor;
      const elementNode = anchor.getNode();
      const offset = anchor.offset;
      const elementNodeSize = elementNode.getChildrenSize();
      let isBlockCursor = false;
      let insertBeforeElement = null;
      if (offset === elementNodeSize) {
        const child = elementNode.getChildAtIndex(offset - 1);
        if (needsBlockCursor(child)) {
          isBlockCursor = true;
        }
      } else {
        const child = elementNode.getChildAtIndex(offset);
        if (needsBlockCursor(child)) {
          const sibling = child.getPreviousSibling();
          if (sibling === null || needsBlockCursor(sibling)) {
            isBlockCursor = true;
            insertBeforeElement = editor.getElementByKey(child.__key);
          }
        }
      }
      if (isBlockCursor) {
        const elementDOM = editor.getElementByKey(elementNode.__key);
        if (blockCursorElement === null) {
          editor._blockCursorElement = blockCursorElement = createBlockCursorElement(editor._config);
        }
        rootElement.style.caretColor = 'transparent';
        if (insertBeforeElement === null) {
          elementDOM.appendChild(blockCursorElement);
        } else {
          elementDOM.insertBefore(blockCursorElement, insertBeforeElement);
        }
        return;
      }
    }
    // Remove cursor
    if (blockCursorElement !== null) {
      removeDOMBlockCursorElement(blockCursorElement, editor, rootElement);
    }
  }
  function getDOMSelection(targetWindow) {
    return !CAN_USE_DOM ? null : (targetWindow || window).getSelection();
  }
  function $splitNode(node, offset) {
    let startNode = node.getChildAtIndex(offset);
    if (startNode == null) {
      startNode = node;
    }
    if (!!$isRootOrShadowRoot(node)) {
      throw Error(`Can not call $splitNode() on root element`);
    }
    const recurse = currentNode => {
      const parent = currentNode.getParentOrThrow();
      const isParentRoot = $isRootOrShadowRoot(parent);
      // The node we start split from (leaf) is moved, but its recursive
      // parents are copied to create separate tree
      const nodeToMove = currentNode === startNode && !isParentRoot ? currentNode : $copyNode(currentNode);
      if (isParentRoot) {
        if (!($isElementNode(currentNode) && $isElementNode(nodeToMove))) {
          throw Error(`Children of a root must be ElementNode`);
        }
        currentNode.insertAfter(nodeToMove);
        return [currentNode, nodeToMove, nodeToMove];
      } else {
        const [leftTree, rightTree, newParent] = recurse(parent);
        const nextSiblings = currentNode.getNextSiblings();
        newParent.append(nodeToMove, ...nextSiblings);
        return [leftTree, rightTree, nodeToMove];
      }
    };
    const [leftTree, rightTree] = recurse(startNode);
    return [leftTree, rightTree];
  }

  /**
   * @param x - The element being tested
   * @returns Returns true if x is an HTML anchor tag, false otherwise
   */
  function isHTMLAnchorElement(x) {
    return isHTMLElement(x) && x.tagName === 'A';
  }

  /**
   * @param x - The element being testing
   * @returns Returns true if x is an HTML element, false otherwise.
   */
  function isHTMLElement(x) {
    // @ts-ignore-next-line - strict check on nodeType here should filter out non-Element EventTarget implementors
    return x.nodeType === 1;
  }

  /**
   *
   * @param node - the Dom Node to check
   * @returns if the Dom Node is an inline node
   */
  function isInlineDomNode(node) {
    const inlineNodes = new RegExp(/^(a|abbr|acronym|b|cite|code|del|em|i|ins|kbd|label|output|q|ruby|s|samp|span|strong|sub|sup|time|u|tt|var|#text)$/, 'i');
    return node.nodeName.match(inlineNodes) !== null;
  }

  /**
   *
   * @param node - the Dom Node to check
   * @returns if the Dom Node is a block node
   */
  function isBlockDomNode(node) {
    const blockNodes = new RegExp(/^(address|article|aside|blockquote|canvas|dd|div|dl|dt|fieldset|figcaption|figure|footer|form|h1|h2|h3|h4|h5|h6|header|hr|li|main|nav|noscript|ol|p|pre|section|table|td|tfoot|ul|video)$/, 'i');
    return node.nodeName.match(blockNodes) !== null;
  }

  /**
   * This function is for internal use of the library.
   * Please do not use it as it may change in the future.
   */
  function INTERNAL_$isBlock(node) {
    if ($isRootNode(node) || $isDecoratorNode(node) && !node.isInline()) {
      return true;
    }
    if (!$isElementNode(node) || $isRootOrShadowRoot(node)) {
      return false;
    }
    const firstChild = node.getFirstChild();
    const isLeafElement = firstChild === null || $isLineBreakNode(firstChild) || $isTextNode(firstChild) || firstChild.isInline();
    return !node.isInline() && node.canBeEmpty() !== false && isLeafElement;
  }
  function $getAncestor(node, predicate) {
    let parent = node;
    while (parent !== null && parent.getParent() !== null && !predicate(parent)) {
      parent = parent.getParentOrThrow();
    }
    return predicate(parent) ? parent : null;
  }

  /**
   * Utility function for accessing current active editor instance.
   * @returns Current active editor
   */
  function $getEditor() {
    return getActiveEditor();
  }

  /** @internal */

  /**
   * @internal
   * Compute a cached Map of node type to nodes for a frozen EditorState
   */
  const cachedNodeMaps = new WeakMap();
  const EMPTY_TYPE_TO_NODE_MAP = new Map();
  function getCachedTypeToNodeMap(editorState) {
    // If this is a new Editor it may have a writable this._editorState
    // with only a 'root' entry.
    if (!editorState._readOnly && editorState.isEmpty()) {
      return EMPTY_TYPE_TO_NODE_MAP;
    }
    if (!editorState._readOnly) {
      throw Error(`getCachedTypeToNodeMap called with a writable EditorState`);
    }
    let typeToNodeMap = cachedNodeMaps.get(editorState);
    if (!typeToNodeMap) {
      typeToNodeMap = new Map();
      cachedNodeMaps.set(editorState, typeToNodeMap);
      for (const [nodeKey, node] of editorState._nodeMap) {
        const nodeType = node.__type;
        let nodeMap = typeToNodeMap.get(nodeType);
        if (!nodeMap) {
          nodeMap = new Map();
          typeToNodeMap.set(nodeType, nodeMap);
        }
        nodeMap.set(nodeKey, node);
      }
    }
    return typeToNodeMap;
  }

  /**
   * Returns a clone of a node using `node.constructor.clone()` followed by
   * `clone.afterCloneFrom(node)`. The resulting clone must have the same key,
   * parent/next/prev pointers, and other properties that are not set by
   * `node.constructor.clone` (format, style, etc.). This is primarily used by
   * {@link LexicalNode.getWritable} to create a writable version of an
   * existing node. The clone is the same logical node as the original node,
   * do not try and use this function to duplicate or copy an existing node.
   *
   * Does not mutate the EditorState.
   * @param node - The node to be cloned.
   * @returns The clone of the node.
   */
  function $cloneWithProperties(latestNode) {
    const constructor = latestNode.constructor;
    const mutableNode = constructor.clone(latestNode);
    mutableNode.afterCloneFrom(latestNode);
    {
      if (!(mutableNode.__key === latestNode.__key)) {
        throw Error(`$cloneWithProperties: ${constructor.name}.clone(node) (with type '${constructor.getType()}') did not return a node with the same key, make sure to specify node.__key as the last argument to the constructor`);
      }
      if (!(mutableNode.__parent === latestNode.__parent && mutableNode.__next === latestNode.__next && mutableNode.__prev === latestNode.__prev)) {
        throw Error(`$cloneWithProperties: ${constructor.name}.clone(node) (with type '${constructor.getType()}') overrided afterCloneFrom but did not call super.afterCloneFrom(prevNode)`);
      }
    }
    return mutableNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function $garbageCollectDetachedDecorators(editor, pendingEditorState) {
    const currentDecorators = editor._decorators;
    const pendingDecorators = editor._pendingDecorators;
    let decorators = pendingDecorators || currentDecorators;
    const nodeMap = pendingEditorState._nodeMap;
    let key;
    for (key in decorators) {
      if (!nodeMap.has(key)) {
        if (decorators === currentDecorators) {
          decorators = cloneDecorators(editor);
        }
        delete decorators[key];
      }
    }
  }
  function $garbageCollectDetachedDeepChildNodes(node, parentKey, prevNodeMap, nodeMap, nodeMapDelete, dirtyNodes) {
    let child = node.getFirstChild();
    while (child !== null) {
      const childKey = child.__key;
      // TODO Revise condition below, redundant? LexicalNode already cleans up children when moving Nodes
      if (child.__parent === parentKey) {
        if ($isElementNode(child)) {
          $garbageCollectDetachedDeepChildNodes(child, childKey, prevNodeMap, nodeMap, nodeMapDelete, dirtyNodes);
        }

        // If we have created a node and it was dereferenced, then also
        // remove it from out dirty nodes Set.
        if (!prevNodeMap.has(childKey)) {
          dirtyNodes.delete(childKey);
        }
        nodeMapDelete.push(childKey);
      }
      child = child.getNextSibling();
    }
  }
  function $garbageCollectDetachedNodes(prevEditorState, editorState, dirtyLeaves, dirtyElements) {
    const prevNodeMap = prevEditorState._nodeMap;
    const nodeMap = editorState._nodeMap;
    // Store dirtyElements in a queue for later deletion; deleting dirty subtrees too early will
    // hinder accessing .__next on child nodes
    const nodeMapDelete = [];
    for (const [nodeKey] of dirtyElements) {
      const node = nodeMap.get(nodeKey);
      if (node !== undefined) {
        // Garbage collect node and its children if they exist
        if (!node.isAttached()) {
          if ($isElementNode(node)) {
            $garbageCollectDetachedDeepChildNodes(node, nodeKey, prevNodeMap, nodeMap, nodeMapDelete, dirtyElements);
          }
          // If we have created a node and it was dereferenced, then also
          // remove it from out dirty nodes Set.
          if (!prevNodeMap.has(nodeKey)) {
            dirtyElements.delete(nodeKey);
          }
          nodeMapDelete.push(nodeKey);
        }
      }
    }
    for (const nodeKey of nodeMapDelete) {
      nodeMap.delete(nodeKey);
    }
    for (const nodeKey of dirtyLeaves) {
      const node = nodeMap.get(nodeKey);
      if (node !== undefined && !node.isAttached()) {
        if (!prevNodeMap.has(nodeKey)) {
          dirtyLeaves.delete(nodeKey);
        }
        nodeMap.delete(nodeKey);
      }
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  let subTreeTextContent = '';
  let subTreeDirectionedTextContent = '';
  let subTreeTextFormat = null;
  let subTreeTextStyle = '';
  let editorTextContent = '';
  let activeEditorConfig;
  let activeEditor$1;
  let activeEditorNodes;
  let treatAllNodesAsDirty = false;
  let activeEditorStateReadOnly = false;
  let activeMutationListeners;
  let activeTextDirection = null;
  let activeDirtyElements;
  let activeDirtyLeaves;
  let activePrevNodeMap;
  let activeNextNodeMap;
  let activePrevKeyToDOMMap;
  let mutatedNodes;
  function destroyNode(key, parentDOM) {
    const node = activePrevNodeMap.get(key);
    if (parentDOM !== null) {
      const dom = getPrevElementByKeyOrThrow(key);
      if (dom.parentNode === parentDOM) {
        parentDOM.removeChild(dom);
      }
    }

    // This logic is really important, otherwise we will leak DOM nodes
    // when their corresponding LexicalNodes are removed from the editor state.
    if (!activeNextNodeMap.has(key)) {
      activeEditor$1._keyToDOMMap.delete(key);
    }
    if ($isElementNode(node)) {
      const children = createChildrenArray(node, activePrevNodeMap);
      destroyChildren(children, 0, children.length - 1, null);
    }
    if (node !== undefined) {
      setMutatedNode(mutatedNodes, activeEditorNodes, activeMutationListeners, node, 'destroyed');
    }
  }
  function destroyChildren(children, _startIndex, endIndex, dom) {
    let startIndex = _startIndex;
    for (; startIndex <= endIndex; ++startIndex) {
      const child = children[startIndex];
      if (child !== undefined) {
        destroyNode(child, dom);
      }
    }
  }
  function setTextAlign(domStyle, value) {
    domStyle.setProperty('text-align', value);
  }
  const DEFAULT_INDENT_VALUE = '40px';
  function setElementIndent(dom, indent) {
    const indentClassName = activeEditorConfig.theme.indent;
    if (typeof indentClassName === 'string') {
      const elementHasClassName = dom.classList.contains(indentClassName);
      if (indent > 0 && !elementHasClassName) {
        dom.classList.add(indentClassName);
      } else if (indent < 1 && elementHasClassName) {
        dom.classList.remove(indentClassName);
      }
    }
    const indentationBaseValue = getComputedStyle(dom).getPropertyValue('--lexical-indent-base-value') || DEFAULT_INDENT_VALUE;
    dom.style.setProperty('padding-inline-start', indent === 0 ? '' : `calc(${indent} * ${indentationBaseValue})`);
  }
  function setElementFormat(dom, format) {
    const domStyle = dom.style;
    if (format === 0) {
      setTextAlign(domStyle, '');
    } else if (format === IS_ALIGN_LEFT) {
      setTextAlign(domStyle, 'left');
    } else if (format === IS_ALIGN_CENTER) {
      setTextAlign(domStyle, 'center');
    } else if (format === IS_ALIGN_RIGHT) {
      setTextAlign(domStyle, 'right');
    } else if (format === IS_ALIGN_JUSTIFY) {
      setTextAlign(domStyle, 'justify');
    } else if (format === IS_ALIGN_START) {
      setTextAlign(domStyle, 'start');
    } else if (format === IS_ALIGN_END) {
      setTextAlign(domStyle, 'end');
    }
  }
  function $createNode(key, parentDOM, insertDOM) {
    const node = activeNextNodeMap.get(key);
    if (node === undefined) {
      {
        throw Error(`createNode: node does not exist in nodeMap`);
      }
    }
    const dom = node.createDOM(activeEditorConfig, activeEditor$1);
    storeDOMWithKey(key, dom, activeEditor$1);

    // This helps preserve the text, and stops spell check tools from
    // merging or break the spans (which happens if they are missing
    // this attribute).
    if ($isTextNode(node)) {
      dom.setAttribute('data-lexical-text', 'true');
    } else if ($isDecoratorNode(node)) {
      dom.setAttribute('data-lexical-decorator', 'true');
    }
    if ($isElementNode(node)) {
      const indent = node.__indent;
      const childrenSize = node.__size;
      if (indent !== 0) {
        setElementIndent(dom, indent);
      }
      if (childrenSize !== 0) {
        const endIndex = childrenSize - 1;
        const children = createChildrenArray(node, activeNextNodeMap);
        $createChildrenWithDirection(children, endIndex, node, dom);
      }
      const format = node.__format;
      if (format !== 0) {
        setElementFormat(dom, format);
      }
      if (!node.isInline()) {
        reconcileElementTerminatingLineBreak(null, node, dom);
      }
      if ($textContentRequiresDoubleLinebreakAtEnd(node)) {
        subTreeTextContent += DOUBLE_LINE_BREAK;
        editorTextContent += DOUBLE_LINE_BREAK;
      }
    } else {
      const text = node.getTextContent();
      if ($isDecoratorNode(node)) {
        const decorator = node.decorate(activeEditor$1, activeEditorConfig);
        if (decorator !== null) {
          reconcileDecorator(key, decorator);
        }
        // Decorators are always non editable
        dom.contentEditable = 'false';
      } else if ($isTextNode(node)) {
        if (!node.isDirectionless()) {
          subTreeDirectionedTextContent += text;
        }
      }
      subTreeTextContent += text;
      editorTextContent += text;
    }
    if (parentDOM !== null) {
      if (insertDOM != null) {
        parentDOM.insertBefore(dom, insertDOM);
      } else {
        // @ts-expect-error: internal field
        const possibleLineBreak = parentDOM.__lexicalLineBreak;
        if (possibleLineBreak != null) {
          parentDOM.insertBefore(dom, possibleLineBreak);
        } else {
          parentDOM.appendChild(dom);
        }
      }
    }
    {
      // Freeze the node in DEV to prevent accidental mutations
      Object.freeze(node);
    }
    setMutatedNode(mutatedNodes, activeEditorNodes, activeMutationListeners, node, 'created');
    return dom;
  }
  function $createChildrenWithDirection(children, endIndex, element, dom) {
    const previousSubTreeDirectionedTextContent = subTreeDirectionedTextContent;
    subTreeDirectionedTextContent = '';
    $createChildren(children, element, 0, endIndex, dom, null);
    reconcileBlockDirection(element, dom);
    subTreeDirectionedTextContent = previousSubTreeDirectionedTextContent;
  }
  function $createChildren(children, element, _startIndex, endIndex, dom, insertDOM) {
    const previousSubTreeTextContent = subTreeTextContent;
    subTreeTextContent = '';
    let startIndex = _startIndex;
    for (; startIndex <= endIndex; ++startIndex) {
      $createNode(children[startIndex], dom, insertDOM);
      const node = activeNextNodeMap.get(children[startIndex]);
      if (node !== null && $isTextNode(node)) {
        if (subTreeTextFormat === null) {
          subTreeTextFormat = node.getFormat();
        }
        if (subTreeTextStyle === '') {
          subTreeTextStyle = node.getStyle();
        }
      }
    }
    if ($textContentRequiresDoubleLinebreakAtEnd(element)) {
      subTreeTextContent += DOUBLE_LINE_BREAK;
    }
    // @ts-expect-error: internal field
    dom.__lexicalTextContent = subTreeTextContent;
    subTreeTextContent = previousSubTreeTextContent + subTreeTextContent;
  }
  function isLastChildLineBreakOrDecorator(childKey, nodeMap) {
    const node = nodeMap.get(childKey);
    return $isLineBreakNode(node) || $isDecoratorNode(node) && node.isInline();
  }

  // If we end an element with a LineBreakNode, then we need to add an additional <br>
  function reconcileElementTerminatingLineBreak(prevElement, nextElement, dom) {
    const prevLineBreak = prevElement !== null && (prevElement.__size === 0 || isLastChildLineBreakOrDecorator(prevElement.__last, activePrevNodeMap));
    const nextLineBreak = nextElement.__size === 0 || isLastChildLineBreakOrDecorator(nextElement.__last, activeNextNodeMap);
    if (prevLineBreak) {
      if (!nextLineBreak) {
        // @ts-expect-error: internal field
        const element = dom.__lexicalLineBreak;
        if (element != null) {
          try {
            dom.removeChild(element);
          } catch (error) {
            if (typeof error === 'object' && error != null) {
              const msg = `${error.toString()} Parent: ${dom.tagName}, child: ${element.tagName}.`;
              throw new Error(msg);
            } else {
              throw error;
            }
          }
        }

        // @ts-expect-error: internal field
        dom.__lexicalLineBreak = null;
      }
    } else if (nextLineBreak) {
      const element = document.createElement('br');
      // @ts-expect-error: internal field
      dom.__lexicalLineBreak = element;
      dom.appendChild(element);
    }
  }
  function reconcileParagraphFormat(element) {
    if ($isParagraphNode(element) && subTreeTextFormat != null && subTreeTextFormat !== element.__textFormat && !activeEditorStateReadOnly) {
      element.setTextFormat(subTreeTextFormat);
      element.setTextStyle(subTreeTextStyle);
    }
  }
  function reconcileParagraphStyle(element) {
    if ($isParagraphNode(element) && subTreeTextStyle !== '' && subTreeTextStyle !== element.__textStyle && !activeEditorStateReadOnly) {
      element.setTextStyle(subTreeTextStyle);
    }
  }
  function reconcileBlockDirection(element, dom) {
    const previousSubTreeDirectionTextContent =
    // @ts-expect-error: internal field
    dom.__lexicalDirTextContent;
    // @ts-expect-error: internal field
    const previousDirection = dom.__lexicalDir;
    if (previousSubTreeDirectionTextContent !== subTreeDirectionedTextContent || previousDirection !== activeTextDirection) {
      const hasEmptyDirectionedTextContent = subTreeDirectionedTextContent === '';
      const direction = hasEmptyDirectionedTextContent ? activeTextDirection : getTextDirection(subTreeDirectionedTextContent);
      if (direction !== previousDirection) {
        const classList = dom.classList;
        const theme = activeEditorConfig.theme;
        let previousDirectionTheme = previousDirection !== null ? theme[previousDirection] : undefined;
        let nextDirectionTheme = direction !== null ? theme[direction] : undefined;

        // Remove the old theme classes if they exist
        if (previousDirectionTheme !== undefined) {
          if (typeof previousDirectionTheme === 'string') {
            const classNamesArr = normalizeClassNames(previousDirectionTheme);
            previousDirectionTheme = theme[previousDirection] = classNamesArr;
          }

          // @ts-ignore: intentional
          classList.remove(...previousDirectionTheme);
        }
        if (direction === null || hasEmptyDirectionedTextContent && direction === 'ltr') {
          // Remove direction
          dom.removeAttribute('dir');
        } else {
          // Apply the new theme classes if they exist
          if (nextDirectionTheme !== undefined) {
            if (typeof nextDirectionTheme === 'string') {
              const classNamesArr = normalizeClassNames(nextDirectionTheme);
              // @ts-expect-error: intentional
              nextDirectionTheme = theme[direction] = classNamesArr;
            }
            if (nextDirectionTheme !== undefined) {
              classList.add(...nextDirectionTheme);
            }
          }

          // Update direction
          dom.dir = direction;
        }
        if (!activeEditorStateReadOnly) {
          const writableNode = element.getWritable();
          writableNode.__dir = direction;
        }
      }
      activeTextDirection = direction;
      // @ts-expect-error: internal field
      dom.__lexicalDirTextContent = subTreeDirectionedTextContent;
      // @ts-expect-error: internal field
      dom.__lexicalDir = direction;
    }
  }
  function $reconcileChildrenWithDirection(prevElement, nextElement, dom) {
    const previousSubTreeDirectionTextContent = subTreeDirectionedTextContent;
    subTreeDirectionedTextContent = '';
    subTreeTextFormat = null;
    subTreeTextStyle = '';
    $reconcileChildren(prevElement, nextElement, dom);
    reconcileBlockDirection(nextElement, dom);
    reconcileParagraphFormat(nextElement);
    reconcileParagraphStyle(nextElement);
    subTreeDirectionedTextContent = previousSubTreeDirectionTextContent;
  }
  function createChildrenArray(element, nodeMap) {
    const children = [];
    let nodeKey = element.__first;
    while (nodeKey !== null) {
      const node = nodeMap.get(nodeKey);
      if (node === undefined) {
        {
          throw Error(`createChildrenArray: node does not exist in nodeMap`);
        }
      }
      children.push(nodeKey);
      nodeKey = node.__next;
    }
    return children;
  }
  function $reconcileChildren(prevElement, nextElement, dom) {
    const previousSubTreeTextContent = subTreeTextContent;
    const prevChildrenSize = prevElement.__size;
    const nextChildrenSize = nextElement.__size;
    subTreeTextContent = '';
    if (prevChildrenSize === 1 && nextChildrenSize === 1) {
      const prevFirstChildKey = prevElement.__first;
      const nextFrstChildKey = nextElement.__first;
      if (prevFirstChildKey === nextFrstChildKey) {
        $reconcileNode(prevFirstChildKey, dom);
      } else {
        const lastDOM = getPrevElementByKeyOrThrow(prevFirstChildKey);
        const replacementDOM = $createNode(nextFrstChildKey, null, null);
        try {
          dom.replaceChild(replacementDOM, lastDOM);
        } catch (error) {
          if (typeof error === 'object' && error != null) {
            const msg = `${error.toString()} Parent: ${dom.tagName}, new child: {tag: ${replacementDOM.tagName} key: ${nextFrstChildKey}}, old child: {tag: ${lastDOM.tagName}, key: ${prevFirstChildKey}}.`;
            throw new Error(msg);
          } else {
            throw error;
          }
        }
        destroyNode(prevFirstChildKey, null);
      }
      const nextChildNode = activeNextNodeMap.get(nextFrstChildKey);
      if ($isTextNode(nextChildNode)) {
        if (subTreeTextFormat === null) {
          subTreeTextFormat = nextChildNode.getFormat();
        }
        if (subTreeTextStyle === '') {
          subTreeTextStyle = nextChildNode.getStyle();
        }
      }
    } else {
      const prevChildren = createChildrenArray(prevElement, activePrevNodeMap);
      const nextChildren = createChildrenArray(nextElement, activeNextNodeMap);
      if (prevChildrenSize === 0) {
        if (nextChildrenSize !== 0) {
          $createChildren(nextChildren, nextElement, 0, nextChildrenSize - 1, dom, null);
        }
      } else if (nextChildrenSize === 0) {
        if (prevChildrenSize !== 0) {
          // @ts-expect-error: internal field
          const lexicalLineBreak = dom.__lexicalLineBreak;
          const canUseFastPath = lexicalLineBreak == null;
          destroyChildren(prevChildren, 0, prevChildrenSize - 1, canUseFastPath ? null : dom);
          if (canUseFastPath) {
            // Fast path for removing DOM nodes
            dom.textContent = '';
          }
        }
      } else {
        $reconcileNodeChildren(nextElement, prevChildren, nextChildren, prevChildrenSize, nextChildrenSize, dom);
      }
    }
    if ($textContentRequiresDoubleLinebreakAtEnd(nextElement)) {
      subTreeTextContent += DOUBLE_LINE_BREAK;
    }

    // @ts-expect-error: internal field
    dom.__lexicalTextContent = subTreeTextContent;
    subTreeTextContent = previousSubTreeTextContent + subTreeTextContent;
  }
  function $reconcileNode(key, parentDOM) {
    const prevNode = activePrevNodeMap.get(key);
    let nextNode = activeNextNodeMap.get(key);
    if (prevNode === undefined || nextNode === undefined) {
      {
        throw Error(`reconcileNode: prevNode or nextNode does not exist in nodeMap`);
      }
    }
    const isDirty = treatAllNodesAsDirty || activeDirtyLeaves.has(key) || activeDirtyElements.has(key);
    const dom = getElementByKeyOrThrow(activeEditor$1, key);

    // If the node key points to the same instance in both states
    // and isn't dirty, we just update the text content cache
    // and return the existing DOM Node.
    if (prevNode === nextNode && !isDirty) {
      if ($isElementNode(prevNode)) {
        // @ts-expect-error: internal field
        const previousSubTreeTextContent = dom.__lexicalTextContent;
        if (previousSubTreeTextContent !== undefined) {
          subTreeTextContent += previousSubTreeTextContent;
          editorTextContent += previousSubTreeTextContent;
        }

        // @ts-expect-error: internal field
        const previousSubTreeDirectionTextContent = dom.__lexicalDirTextContent;
        if (previousSubTreeDirectionTextContent !== undefined) {
          subTreeDirectionedTextContent += previousSubTreeDirectionTextContent;
        }
      } else {
        const text = prevNode.getTextContent();
        if ($isTextNode(prevNode) && !prevNode.isDirectionless()) {
          subTreeDirectionedTextContent += text;
        }
        editorTextContent += text;
        subTreeTextContent += text;
      }
      return dom;
    }
    // If the node key doesn't point to the same instance in both maps,
    // it means it were cloned. If they're also dirty, we mark them as mutated.
    if (prevNode !== nextNode && isDirty) {
      setMutatedNode(mutatedNodes, activeEditorNodes, activeMutationListeners, nextNode, 'updated');
    }

    // Update node. If it returns true, we need to unmount and re-create the node
    if (nextNode.updateDOM(prevNode, dom, activeEditorConfig)) {
      const replacementDOM = $createNode(key, null, null);
      if (parentDOM === null) {
        {
          throw Error(`reconcileNode: parentDOM is null`);
        }
      }
      parentDOM.replaceChild(replacementDOM, dom);
      destroyNode(key, null);
      return replacementDOM;
    }
    if ($isElementNode(prevNode) && $isElementNode(nextNode)) {
      // Reconcile element children
      const nextIndent = nextNode.__indent;
      if (nextIndent !== prevNode.__indent) {
        setElementIndent(dom, nextIndent);
      }
      const nextFormat = nextNode.__format;
      if (nextFormat !== prevNode.__format) {
        setElementFormat(dom, nextFormat);
      }
      if (isDirty) {
        $reconcileChildrenWithDirection(prevNode, nextNode, dom);
        if (!$isRootNode(nextNode) && !nextNode.isInline()) {
          reconcileElementTerminatingLineBreak(prevNode, nextNode, dom);
        }
      }
      if ($textContentRequiresDoubleLinebreakAtEnd(nextNode)) {
        subTreeTextContent += DOUBLE_LINE_BREAK;
        editorTextContent += DOUBLE_LINE_BREAK;
      }
    } else {
      const text = nextNode.getTextContent();
      if ($isDecoratorNode(nextNode)) {
        const decorator = nextNode.decorate(activeEditor$1, activeEditorConfig);
        if (decorator !== null) {
          reconcileDecorator(key, decorator);
        }
      } else if ($isTextNode(nextNode) && !nextNode.isDirectionless()) {
        // Handle text content, for LTR, LTR cases.
        subTreeDirectionedTextContent += text;
      }
      subTreeTextContent += text;
      editorTextContent += text;
    }
    if (!activeEditorStateReadOnly && $isRootNode(nextNode) && nextNode.__cachedText !== editorTextContent) {
      // Cache the latest text content.
      const nextRootNode = nextNode.getWritable();
      nextRootNode.__cachedText = editorTextContent;
      nextNode = nextRootNode;
    }
    {
      // Freeze the node in DEV to prevent accidental mutations
      Object.freeze(nextNode);
    }
    return dom;
  }
  function reconcileDecorator(key, decorator) {
    let pendingDecorators = activeEditor$1._pendingDecorators;
    const currentDecorators = activeEditor$1._decorators;
    if (pendingDecorators === null) {
      if (currentDecorators[key] === decorator) {
        return;
      }
      pendingDecorators = cloneDecorators(activeEditor$1);
    }
    pendingDecorators[key] = decorator;
  }
  function getFirstChild(element) {
    return element.firstChild;
  }
  function getNextSibling(element) {
    let nextSibling = element.nextSibling;
    if (nextSibling !== null && nextSibling === activeEditor$1._blockCursorElement) {
      nextSibling = nextSibling.nextSibling;
    }
    return nextSibling;
  }
  function $reconcileNodeChildren(nextElement, prevChildren, nextChildren, prevChildrenLength, nextChildrenLength, dom) {
    const prevEndIndex = prevChildrenLength - 1;
    const nextEndIndex = nextChildrenLength - 1;
    let prevChildrenSet;
    let nextChildrenSet;
    let siblingDOM = getFirstChild(dom);
    let prevIndex = 0;
    let nextIndex = 0;
    while (prevIndex <= prevEndIndex && nextIndex <= nextEndIndex) {
      const prevKey = prevChildren[prevIndex];
      const nextKey = nextChildren[nextIndex];
      if (prevKey === nextKey) {
        siblingDOM = getNextSibling($reconcileNode(nextKey, dom));
        prevIndex++;
        nextIndex++;
      } else {
        if (prevChildrenSet === undefined) {
          prevChildrenSet = new Set(prevChildren);
        }
        if (nextChildrenSet === undefined) {
          nextChildrenSet = new Set(nextChildren);
        }
        const nextHasPrevKey = nextChildrenSet.has(prevKey);
        const prevHasNextKey = prevChildrenSet.has(nextKey);
        if (!nextHasPrevKey) {
          // Remove prev
          siblingDOM = getNextSibling(getPrevElementByKeyOrThrow(prevKey));
          destroyNode(prevKey, dom);
          prevIndex++;
        } else if (!prevHasNextKey) {
          // Create next
          $createNode(nextKey, dom, siblingDOM);
          nextIndex++;
        } else {
          // Move next
          const childDOM = getElementByKeyOrThrow(activeEditor$1, nextKey);
          if (childDOM === siblingDOM) {
            siblingDOM = getNextSibling($reconcileNode(nextKey, dom));
          } else {
            if (siblingDOM != null) {
              dom.insertBefore(childDOM, siblingDOM);
            } else {
              dom.appendChild(childDOM);
            }
            $reconcileNode(nextKey, dom);
          }
          prevIndex++;
          nextIndex++;
        }
      }
      const node = activeNextNodeMap.get(nextKey);
      if (node !== null && $isTextNode(node)) {
        if (subTreeTextFormat === null) {
          subTreeTextFormat = node.getFormat();
        }
        if (subTreeTextStyle === '') {
          subTreeTextStyle = node.getStyle();
        }
      }
    }
    const appendNewChildren = prevIndex > prevEndIndex;
    const removeOldChildren = nextIndex > nextEndIndex;
    if (appendNewChildren && !removeOldChildren) {
      const previousNode = nextChildren[nextEndIndex + 1];
      const insertDOM = previousNode === undefined ? null : activeEditor$1.getElementByKey(previousNode);
      $createChildren(nextChildren, nextElement, nextIndex, nextEndIndex, dom, insertDOM);
    } else if (removeOldChildren && !appendNewChildren) {
      destroyChildren(prevChildren, prevIndex, prevEndIndex, dom);
    }
  }
  function $reconcileRoot(prevEditorState, nextEditorState, editor, dirtyType, dirtyElements, dirtyLeaves) {
    // We cache text content to make retrieval more efficient.
    // The cache must be rebuilt during reconciliation to account for any changes.
    subTreeTextContent = '';
    editorTextContent = '';
    subTreeDirectionedTextContent = '';
    // Rather than pass around a load of arguments through the stack recursively
    // we instead set them as bindings within the scope of the module.
    treatAllNodesAsDirty = dirtyType === FULL_RECONCILE;
    activeTextDirection = null;
    activeEditor$1 = editor;
    activeEditorConfig = editor._config;
    activeEditorNodes = editor._nodes;
    activeMutationListeners = activeEditor$1._listeners.mutation;
    activeDirtyElements = dirtyElements;
    activeDirtyLeaves = dirtyLeaves;
    activePrevNodeMap = prevEditorState._nodeMap;
    activeNextNodeMap = nextEditorState._nodeMap;
    activeEditorStateReadOnly = nextEditorState._readOnly;
    activePrevKeyToDOMMap = new Map(editor._keyToDOMMap);
    // We keep track of mutated nodes so we can trigger mutation
    // listeners later in the update cycle.
    const currentMutatedNodes = new Map();
    mutatedNodes = currentMutatedNodes;
    $reconcileNode('root', null);
    // We don't want a bunch of void checks throughout the scope
    // so instead we make it seem that these values are always set.
    // We also want to make sure we clear them down, otherwise we
    // can leak memory.
    // @ts-ignore
    activeEditor$1 = undefined;
    // @ts-ignore
    activeEditorNodes = undefined;
    // @ts-ignore
    activeDirtyElements = undefined;
    // @ts-ignore
    activeDirtyLeaves = undefined;
    // @ts-ignore
    activePrevNodeMap = undefined;
    // @ts-ignore
    activeNextNodeMap = undefined;
    // @ts-ignore
    activeEditorConfig = undefined;
    // @ts-ignore
    activePrevKeyToDOMMap = undefined;
    // @ts-ignore
    mutatedNodes = undefined;
    return currentMutatedNodes;
  }
  function storeDOMWithKey(key, dom, editor) {
    const keyToDOMMap = editor._keyToDOMMap;
    // @ts-ignore We intentionally add this to the Node.
    dom['__lexicalKey_' + editor._key] = key;
    keyToDOMMap.set(key, dom);
  }
  function getPrevElementByKeyOrThrow(key) {
    const element = activePrevKeyToDOMMap.get(key);
    if (element === undefined) {
      {
        throw Error(`Reconciliation: could not find DOM element for node key ${key}`);
      }
    }
    return element;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const PASS_THROUGH_COMMAND = Object.freeze({});
  const ANDROID_COMPOSITION_LATENCY = 30;
  const rootElementEvents = [['keydown', onKeyDown], ['pointerdown', onPointerDown], ['compositionstart', onCompositionStart], ['compositionend', onCompositionEnd], ['input', onInput], ['click', onClick], ['cut', PASS_THROUGH_COMMAND], ['copy', PASS_THROUGH_COMMAND], ['dragstart', PASS_THROUGH_COMMAND], ['dragover', PASS_THROUGH_COMMAND], ['dragend', PASS_THROUGH_COMMAND], ['paste', PASS_THROUGH_COMMAND], ['focus', PASS_THROUGH_COMMAND], ['blur', PASS_THROUGH_COMMAND], ['drop', PASS_THROUGH_COMMAND]];
  if (CAN_USE_BEFORE_INPUT) {
    rootElementEvents.push(['beforeinput', (event, editor) => onBeforeInput(event, editor)]);
  }
  let lastKeyDownTimeStamp = 0;
  let lastKeyCode = null;
  let lastBeforeInputInsertTextTimeStamp = 0;
  let unprocessedBeforeInputData = null;
  const rootElementsRegistered = new WeakMap();
  let isSelectionChangeFromDOMUpdate = false;
  let isSelectionChangeFromMouseDown = false;
  let isInsertLineBreak = false;
  let isFirefoxEndingComposition = false;
  let collapsedSelectionFormat = [0, '', 0, 'root', 0];

  // This function is used to determine if Lexical should attempt to override
  // the default browser behavior for insertion of text and use its own internal
  // heuristics. This is an extremely important function, and makes much of Lexical
  // work as intended between different browsers and across word, line and character
  // boundary/formats. It also is important for text replacement, node schemas and
  // composition mechanics.

  function $shouldPreventDefaultAndInsertText(selection, domTargetRange, text, timeStamp, isBeforeInput) {
    const anchor = selection.anchor;
    const focus = selection.focus;
    const anchorNode = anchor.getNode();
    const editor = getActiveEditor();
    const domSelection = getDOMSelection(editor._window);
    const domAnchorNode = domSelection !== null ? domSelection.anchorNode : null;
    const anchorKey = anchor.key;
    const backingAnchorElement = editor.getElementByKey(anchorKey);
    const textLength = text.length;
    return anchorKey !== focus.key ||
    // If we're working with a non-text node.
    !$isTextNode(anchorNode) ||
    // If we are replacing a range with a single character or grapheme, and not composing.
    (!isBeforeInput && (!CAN_USE_BEFORE_INPUT ||
    // We check to see if there has been
    // a recent beforeinput event for "textInput". If there has been one in the last
    // 50ms then we proceed as normal. However, if there is not, then this is likely
    // a dangling `input` event caused by execCommand('insertText').
    lastBeforeInputInsertTextTimeStamp < timeStamp + 50) || anchorNode.isDirty() && textLength < 2 || doesContainGrapheme(text)) && anchor.offset !== focus.offset && !anchorNode.isComposing() ||
    // Any non standard text node.
    $isTokenOrSegmented(anchorNode) ||
    // If the text length is more than a single character and we're either
    // dealing with this in "beforeinput" or where the node has already recently
    // been changed (thus is dirty).
    anchorNode.isDirty() && textLength > 1 ||
    // If the DOM selection element is not the same as the backing node during beforeinput.
    (isBeforeInput || !CAN_USE_BEFORE_INPUT) && backingAnchorElement !== null && !anchorNode.isComposing() && domAnchorNode !== getDOMTextNode(backingAnchorElement) ||
    // If TargetRange is not the same as the DOM selection; browser trying to edit random parts
    // of the editor.
    domSelection !== null && domTargetRange !== null && (!domTargetRange.collapsed || domTargetRange.startContainer !== domSelection.anchorNode || domTargetRange.startOffset !== domSelection.anchorOffset) ||
    // Check if we're changing from bold to italics, or some other format.
    anchorNode.getFormat() !== selection.format || anchorNode.getStyle() !== selection.style ||
    // One last set of heuristics to check against.
    $shouldInsertTextAfterOrBeforeTextNode(selection, anchorNode);
  }
  function shouldSkipSelectionChange(domNode, offset) {
    return domNode !== null && domNode.nodeValue !== null && domNode.nodeType === DOM_TEXT_TYPE && offset !== 0 && offset !== domNode.nodeValue.length;
  }
  function onSelectionChange(domSelection, editor, isActive) {
    const {
      anchorNode: anchorDOM,
      anchorOffset,
      focusNode: focusDOM,
      focusOffset
    } = domSelection;
    if (isSelectionChangeFromDOMUpdate) {
      isSelectionChangeFromDOMUpdate = false;

      // If native DOM selection is on a DOM element, then
      // we should continue as usual, as Lexical's selection
      // may have normalized to a better child. If the DOM
      // element is a text node, we can safely apply this
      // optimization and skip the selection change entirely.
      // We also need to check if the offset is at the boundary,
      // because in this case, we might need to normalize to a
      // sibling instead.
      if (shouldSkipSelectionChange(anchorDOM, anchorOffset) && shouldSkipSelectionChange(focusDOM, focusOffset)) {
        return;
      }
    }
    updateEditor(editor, () => {
      // Non-active editor don't need any extra logic for selection, it only needs update
      // to reconcile selection (set it to null) to ensure that only one editor has non-null selection.
      if (!isActive) {
        $setSelection(null);
        return;
      }
      if (!isSelectionWithinEditor(editor, anchorDOM, focusDOM)) {
        return;
      }
      const selection = $getSelection();

      // Update the selection format
      if ($isRangeSelection(selection)) {
        const anchor = selection.anchor;
        const anchorNode = anchor.getNode();
        if (selection.isCollapsed()) {
          // Badly interpreted range selection when collapsed - #1482
          if (domSelection.type === 'Range' && domSelection.anchorNode === domSelection.focusNode) {
            selection.dirty = true;
          }

          // If we have marked a collapsed selection format, and we're
          // within the given time range – then attempt to use that format
          // instead of getting the format from the anchor node.
          const windowEvent = getWindow(editor).event;
          const currentTimeStamp = windowEvent ? windowEvent.timeStamp : performance.now();
          const [lastFormat, lastStyle, lastOffset, lastKey, timeStamp] = collapsedSelectionFormat;
          const root = $getRoot();
          const isRootTextContentEmpty = editor.isComposing() === false && root.getTextContent() === '';
          if (currentTimeStamp < timeStamp + 200 && anchor.offset === lastOffset && anchor.key === lastKey) {
            selection.format = lastFormat;
            selection.style = lastStyle;
          } else {
            if (anchor.type === 'text') {
              if (!$isTextNode(anchorNode)) {
                throw Error(`Point.getNode() must return TextNode when type is text`);
              }
              selection.format = anchorNode.getFormat();
              selection.style = anchorNode.getStyle();
            } else if (anchor.type === 'element' && !isRootTextContentEmpty) {
              const lastNode = anchor.getNode();
              selection.style = '';
              if (lastNode instanceof ParagraphNode && lastNode.getChildrenSize() === 0) {
                selection.format = lastNode.getTextFormat();
                selection.style = lastNode.getTextStyle();
              } else {
                selection.format = 0;
              }
            }
          }
        } else {
          const anchorKey = anchor.key;
          const focus = selection.focus;
          const focusKey = focus.key;
          const nodes = selection.getNodes();
          const nodesLength = nodes.length;
          const isBackward = selection.isBackward();
          const startOffset = isBackward ? focusOffset : anchorOffset;
          const endOffset = isBackward ? anchorOffset : focusOffset;
          const startKey = isBackward ? focusKey : anchorKey;
          const endKey = isBackward ? anchorKey : focusKey;
          let combinedFormat = IS_ALL_FORMATTING;
          let hasTextNodes = false;
          for (let i = 0; i < nodesLength; i++) {
            const node = nodes[i];
            const textContentSize = node.getTextContentSize();
            if ($isTextNode(node) && textContentSize !== 0 &&
            // Exclude empty text nodes at boundaries resulting from user's selection
            !(i === 0 && node.__key === startKey && startOffset === textContentSize || i === nodesLength - 1 && node.__key === endKey && endOffset === 0)) {
              // TODO: what about style?
              hasTextNodes = true;
              combinedFormat &= node.getFormat();
              if (combinedFormat === 0) {
                break;
              }
            }
          }
          selection.format = hasTextNodes ? combinedFormat : 0;
        }
      }
      dispatchCommand(editor, SELECTION_CHANGE_COMMAND, undefined);
    });
  }

  // This is a work-around is mainly Chrome specific bug where if you select
  // the contents of an empty block, you cannot easily unselect anything.
  // This results in a tiny selection box that looks buggy/broken. This can
  // also help other browsers when selection might "appear" lost, when it
  // really isn't.
  function onClick(event, editor) {
    updateEditor(editor, () => {
      const selection = $getSelection();
      const domSelection = getDOMSelection(editor._window);
      const lastSelection = $getPreviousSelection();
      if (domSelection) {
        if ($isRangeSelection(selection)) {
          const anchor = selection.anchor;
          const anchorNode = anchor.getNode();
          if (anchor.type === 'element' && anchor.offset === 0 && selection.isCollapsed() && !$isRootNode(anchorNode) && $getRoot().getChildrenSize() === 1 && anchorNode.getTopLevelElementOrThrow().isEmpty() && lastSelection !== null && selection.is(lastSelection)) {
            domSelection.removeAllRanges();
            selection.dirty = true;
          } else if (event.detail === 3 && !selection.isCollapsed()) {
            // Tripple click causing selection to overflow into the nearest element. In that
            // case visually it looks like a single element content is selected, focus node
            // is actually at the beginning of the next element (if present) and any manipulations
            // with selection (formatting) are affecting second element as well
            const focus = selection.focus;
            const focusNode = focus.getNode();
            if (anchorNode !== focusNode) {
              if ($isElementNode(anchorNode)) {
                anchorNode.select(0);
              } else {
                anchorNode.getParentOrThrow().select(0);
              }
            }
          }
        } else if (event.pointerType === 'touch') {
          // This is used to update the selection on touch devices when the user clicks on text after a
          // node selection. See isSelectionChangeFromMouseDown for the inverse
          const domAnchorNode = domSelection.anchorNode;
          if (domAnchorNode !== null) {
            const nodeType = domAnchorNode.nodeType;
            // If the user is attempting to click selection back onto text, then
            // we should attempt create a range selection.
            // When we click on an empty paragraph node or the end of a paragraph that ends
            // with an image/poll, the nodeType will be ELEMENT_NODE
            if (nodeType === DOM_ELEMENT_TYPE || nodeType === DOM_TEXT_TYPE) {
              const newSelection = $internalCreateRangeSelection(lastSelection, domSelection, editor, event);
              $setSelection(newSelection);
            }
          }
        }
      }
      dispatchCommand(editor, CLICK_COMMAND, event);
    });
  }
  function onPointerDown(event, editor) {
    // TODO implement text drag & drop
    const target = event.target;
    const pointerType = event.pointerType;
    if (target instanceof Node && pointerType !== 'touch') {
      updateEditor(editor, () => {
        // Drag & drop should not recompute selection until mouse up; otherwise the initially
        // selected content is lost.
        if (!$isSelectionCapturedInDecorator(target)) {
          isSelectionChangeFromMouseDown = true;
        }
      });
    }
  }
  function getTargetRange(event) {
    if (!event.getTargetRanges) {
      return null;
    }
    const targetRanges = event.getTargetRanges();
    if (targetRanges.length === 0) {
      return null;
    }
    return targetRanges[0];
  }
  function $canRemoveText(anchorNode, focusNode) {
    return anchorNode !== focusNode || $isElementNode(anchorNode) || $isElementNode(focusNode) || !anchorNode.isToken() || !focusNode.isToken();
  }
  function isPossiblyAndroidKeyPress(timeStamp) {
    return lastKeyCode === 'MediaLast' && timeStamp < lastKeyDownTimeStamp + ANDROID_COMPOSITION_LATENCY;
  }
  function onBeforeInput(event, editor) {
    const inputType = event.inputType;
    const targetRange = getTargetRange(event);

    // We let the browser do its own thing for composition.
    if (inputType === 'deleteCompositionText' ||
    // If we're pasting in FF, we shouldn't get this event
    // as the `paste` event should have triggered, unless the
    // user has dom.event.clipboardevents.enabled disabled in
    // about:config. In that case, we need to process the
    // pasted content in the DOM mutation phase.
    IS_FIREFOX && isFirefoxClipboardEvents(editor)) {
      return;
    } else if (inputType === 'insertCompositionText') {
      return;
    }
    updateEditor(editor, () => {
      const selection = $getSelection();
      if (inputType === 'deleteContentBackward') {
        if (selection === null) {
          // Use previous selection
          const prevSelection = $getPreviousSelection();
          if (!$isRangeSelection(prevSelection)) {
            return;
          }
          $setSelection(prevSelection.clone());
        }
        if ($isRangeSelection(selection)) {
          const isSelectionAnchorSameAsFocus = selection.anchor.key === selection.focus.key;
          if (isPossiblyAndroidKeyPress(event.timeStamp) && editor.isComposing() && isSelectionAnchorSameAsFocus) {
            $setCompositionKey(null);
            lastKeyDownTimeStamp = 0;
            // Fixes an Android bug where selection flickers when backspacing
            setTimeout(() => {
              updateEditor(editor, () => {
                $setCompositionKey(null);
              });
            }, ANDROID_COMPOSITION_LATENCY);
            if ($isRangeSelection(selection)) {
              const anchorNode = selection.anchor.getNode();
              anchorNode.markDirty();
              selection.format = anchorNode.getFormat();
              if (!$isTextNode(anchorNode)) {
                throw Error(`Anchor node must be a TextNode`);
              }
              selection.style = anchorNode.getStyle();
            }
          } else {
            $setCompositionKey(null);
            event.preventDefault();
            // Chromium Android at the moment seems to ignore the preventDefault
            // on 'deleteContentBackward' and still deletes the content. Which leads
            // to multiple deletions. So we let the browser handle the deletion in this case.
            const selectedNodeText = selection.anchor.getNode().getTextContent();
            const hasSelectedAllTextInNode = selection.anchor.offset === 0 && selection.focus.offset === selectedNodeText.length;
            const shouldLetBrowserHandleDelete = IS_ANDROID_CHROME && isSelectionAnchorSameAsFocus && !hasSelectedAllTextInNode;
            if (!shouldLetBrowserHandleDelete) {
              dispatchCommand(editor, DELETE_CHARACTER_COMMAND, true);
            }
          }
          return;
        }
      }
      if (!$isRangeSelection(selection)) {
        return;
      }
      const data = event.data;

      // This represents the case when two beforeinput events are triggered at the same time (without a
      // full event loop ending at input). This happens with MacOS with the default keyboard settings,
      // a combination of autocorrection + autocapitalization.
      // Having Lexical run everything in controlled mode would fix the issue without additional code
      // but this would kill the massive performance win from the most common typing event.
      // Alternatively, when this happens we can prematurely update our EditorState based on the DOM
      // content, a job that would usually be the input event's responsibility.
      if (unprocessedBeforeInputData !== null) {
        $updateSelectedTextFromDOM(false, editor, unprocessedBeforeInputData);
      }
      if ((!selection.dirty || unprocessedBeforeInputData !== null) && selection.isCollapsed() && !$isRootNode(selection.anchor.getNode()) && targetRange !== null) {
        selection.applyDOMRange(targetRange);
      }
      unprocessedBeforeInputData = null;
      const anchor = selection.anchor;
      const focus = selection.focus;
      const anchorNode = anchor.getNode();
      const focusNode = focus.getNode();
      if (inputType === 'insertText' || inputType === 'insertTranspose') {
        if (data === '\n') {
          event.preventDefault();
          dispatchCommand(editor, INSERT_LINE_BREAK_COMMAND, false);
        } else if (data === DOUBLE_LINE_BREAK) {
          event.preventDefault();
          dispatchCommand(editor, INSERT_PARAGRAPH_COMMAND, undefined);
        } else if (data == null && event.dataTransfer) {
          // Gets around a Safari text replacement bug.
          const text = event.dataTransfer.getData('text/plain');
          event.preventDefault();
          selection.insertRawText(text);
        } else if (data != null && $shouldPreventDefaultAndInsertText(selection, targetRange, data, event.timeStamp, true)) {
          event.preventDefault();
          dispatchCommand(editor, CONTROLLED_TEXT_INSERTION_COMMAND, data);
        } else {
          unprocessedBeforeInputData = data;
        }
        lastBeforeInputInsertTextTimeStamp = event.timeStamp;
        return;
      }

      // Prevent the browser from carrying out
      // the input event, so we can control the
      // output.
      event.preventDefault();
      switch (inputType) {
        case 'insertFromYank':
        case 'insertFromDrop':
        case 'insertReplacementText':
          {
            dispatchCommand(editor, CONTROLLED_TEXT_INSERTION_COMMAND, event);
            break;
          }
        case 'insertFromComposition':
          {
            // This is the end of composition
            $setCompositionKey(null);
            dispatchCommand(editor, CONTROLLED_TEXT_INSERTION_COMMAND, event);
            break;
          }
        case 'insertLineBreak':
          {
            // Used for Android
            $setCompositionKey(null);
            dispatchCommand(editor, INSERT_LINE_BREAK_COMMAND, false);
            break;
          }
        case 'insertParagraph':
          {
            // Used for Android
            $setCompositionKey(null);

            // Safari does not provide the type "insertLineBreak".
            // So instead, we need to infer it from the keyboard event.
            // We do not apply this logic to iOS to allow newline auto-capitalization
            // work without creating linebreaks when pressing Enter
            if (isInsertLineBreak && !IS_IOS) {
              isInsertLineBreak = false;
              dispatchCommand(editor, INSERT_LINE_BREAK_COMMAND, false);
            } else {
              dispatchCommand(editor, INSERT_PARAGRAPH_COMMAND, undefined);
            }
            break;
          }
        case 'insertFromPaste':
        case 'insertFromPasteAsQuotation':
          {
            dispatchCommand(editor, PASTE_COMMAND, event);
            break;
          }
        case 'deleteByComposition':
          {
            if ($canRemoveText(anchorNode, focusNode)) {
              dispatchCommand(editor, REMOVE_TEXT_COMMAND, event);
            }
            break;
          }
        case 'deleteByDrag':
        case 'deleteByCut':
          {
            dispatchCommand(editor, REMOVE_TEXT_COMMAND, event);
            break;
          }
        case 'deleteContent':
          {
            dispatchCommand(editor, DELETE_CHARACTER_COMMAND, false);
            break;
          }
        case 'deleteWordBackward':
          {
            dispatchCommand(editor, DELETE_WORD_COMMAND, true);
            break;
          }
        case 'deleteWordForward':
          {
            dispatchCommand(editor, DELETE_WORD_COMMAND, false);
            break;
          }
        case 'deleteHardLineBackward':
        case 'deleteSoftLineBackward':
          {
            dispatchCommand(editor, DELETE_LINE_COMMAND, true);
            break;
          }
        case 'deleteContentForward':
        case 'deleteHardLineForward':
        case 'deleteSoftLineForward':
          {
            dispatchCommand(editor, DELETE_LINE_COMMAND, false);
            break;
          }
        case 'formatStrikeThrough':
          {
            dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'strikethrough');
            break;
          }
        case 'formatBold':
          {
            dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'bold');
            break;
          }
        case 'formatItalic':
          {
            dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'italic');
            break;
          }
        case 'formatUnderline':
          {
            dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'underline');
            break;
          }
        case 'historyUndo':
          {
            dispatchCommand(editor, UNDO_COMMAND, undefined);
            break;
          }
        case 'historyRedo':
          {
            dispatchCommand(editor, REDO_COMMAND, undefined);
            break;
          }
        // NO-OP
      }
    });
  }

  function onInput(event, editor) {
    // We don't want the onInput to bubble, in the case of nested editors.
    event.stopPropagation();
    updateEditor(editor, () => {
      const selection = $getSelection();
      const data = event.data;
      const targetRange = getTargetRange(event);
      if (data != null && $isRangeSelection(selection) && $shouldPreventDefaultAndInsertText(selection, targetRange, data, event.timeStamp, false)) {
        // Given we're over-riding the default behavior, we will need
        // to ensure to disable composition before dispatching the
        // insertText command for when changing the sequence for FF.
        if (isFirefoxEndingComposition) {
          $onCompositionEndImpl(editor, data);
          isFirefoxEndingComposition = false;
        }
        const anchor = selection.anchor;
        const anchorNode = anchor.getNode();
        const domSelection = getDOMSelection(editor._window);
        if (domSelection === null) {
          return;
        }
        const isBackward = selection.isBackward();
        const startOffset = isBackward ? selection.anchor.offset : selection.focus.offset;
        const endOffset = isBackward ? selection.focus.offset : selection.anchor.offset;
        // If the content is the same as inserted, then don't dispatch an insertion.
        // Given onInput doesn't take the current selection (it uses the previous)
        // we can compare that against what the DOM currently says.
        if (!CAN_USE_BEFORE_INPUT || selection.isCollapsed() || !$isTextNode(anchorNode) || domSelection.anchorNode === null || anchorNode.getTextContent().slice(0, startOffset) + data + anchorNode.getTextContent().slice(startOffset + endOffset) !== getAnchorTextFromDOM(domSelection.anchorNode)) {
          dispatchCommand(editor, CONTROLLED_TEXT_INSERTION_COMMAND, data);
        }
        const textLength = data.length;

        // Another hack for FF, as it's possible that the IME is still
        // open, even though compositionend has already fired (sigh).
        if (IS_FIREFOX && textLength > 1 && event.inputType === 'insertCompositionText' && !editor.isComposing()) {
          selection.anchor.offset -= textLength;
        }

        // This ensures consistency on Android.
        if (!IS_SAFARI && !IS_IOS && !IS_APPLE_WEBKIT && editor.isComposing()) {
          lastKeyDownTimeStamp = 0;
          $setCompositionKey(null);
        }
      } else {
        const characterData = data !== null ? data : undefined;
        $updateSelectedTextFromDOM(false, editor, characterData);

        // onInput always fires after onCompositionEnd for FF.
        if (isFirefoxEndingComposition) {
          $onCompositionEndImpl(editor, data || undefined);
          isFirefoxEndingComposition = false;
        }
      }

      // Also flush any other mutations that might have occurred
      // since the change.
      $flushMutations();
    });
    unprocessedBeforeInputData = null;
  }
  function onCompositionStart(event, editor) {
    updateEditor(editor, () => {
      const selection = $getSelection();
      if ($isRangeSelection(selection) && !editor.isComposing()) {
        const anchor = selection.anchor;
        const node = selection.anchor.getNode();
        $setCompositionKey(anchor.key);
        if (
        // If it has been 30ms since the last keydown, then we should
        // apply the empty space heuristic. We can't do this for Safari,
        // as the keydown fires after composition start.
        event.timeStamp < lastKeyDownTimeStamp + ANDROID_COMPOSITION_LATENCY ||
        // FF has issues around composing multibyte characters, so we also
        // need to invoke the empty space heuristic below.
        anchor.type === 'element' || !selection.isCollapsed() || node.getFormat() !== selection.format || $isTextNode(node) && node.getStyle() !== selection.style) {
          // We insert a zero width character, ready for the composition
          // to get inserted into the new node we create. If
          // we don't do this, Safari will fail on us because
          // there is no text node matching the selection.
          dispatchCommand(editor, CONTROLLED_TEXT_INSERTION_COMMAND, COMPOSITION_START_CHAR);
        }
      }
    });
  }
  function $onCompositionEndImpl(editor, data) {
    const compositionKey = editor._compositionKey;
    $setCompositionKey(null);

    // Handle termination of composition.
    if (compositionKey !== null && data != null) {
      // Composition can sometimes move to an adjacent DOM node when backspacing.
      // So check for the empty case.
      if (data === '') {
        const node = $getNodeByKey(compositionKey);
        const textNode = getDOMTextNode(editor.getElementByKey(compositionKey));
        if (textNode !== null && textNode.nodeValue !== null && $isTextNode(node)) {
          $updateTextNodeFromDOMContent(node, textNode.nodeValue, null, null, true);
        }
        return;
      }

      // Composition can sometimes be that of a new line. In which case, we need to
      // handle that accordingly.
      if (data[data.length - 1] === '\n') {
        const selection = $getSelection();
        if ($isRangeSelection(selection)) {
          // If the last character is a line break, we also need to insert
          // a line break.
          const focus = selection.focus;
          selection.anchor.set(focus.key, focus.offset, focus.type);
          dispatchCommand(editor, KEY_ENTER_COMMAND, null);
          return;
        }
      }
    }
    $updateSelectedTextFromDOM(true, editor, data);
  }
  function onCompositionEnd(event, editor) {
    // Firefox fires onCompositionEnd before onInput, but Chrome/Webkit,
    // fire onInput before onCompositionEnd. To ensure the sequence works
    // like Chrome/Webkit we use the isFirefoxEndingComposition flag to
    // defer handling of onCompositionEnd in Firefox till we have processed
    // the logic in onInput.
    if (IS_FIREFOX) {
      isFirefoxEndingComposition = true;
    } else {
      updateEditor(editor, () => {
        $onCompositionEndImpl(editor, event.data);
      });
    }
  }
  function onKeyDown(event, editor) {
    lastKeyDownTimeStamp = event.timeStamp;
    lastKeyCode = event.key;
    if (editor.isComposing()) {
      return;
    }
    const {
      key,
      shiftKey,
      ctrlKey,
      metaKey,
      altKey
    } = event;
    if (dispatchCommand(editor, KEY_DOWN_COMMAND, event)) {
      return;
    }
    if (key == null) {
      return;
    }
    if (isMoveForward(key, ctrlKey, altKey, metaKey)) {
      dispatchCommand(editor, KEY_ARROW_RIGHT_COMMAND, event);
    } else if (isMoveToEnd(key, ctrlKey, shiftKey, altKey, metaKey)) {
      dispatchCommand(editor, MOVE_TO_END, event);
    } else if (isMoveBackward(key, ctrlKey, altKey, metaKey)) {
      dispatchCommand(editor, KEY_ARROW_LEFT_COMMAND, event);
    } else if (isMoveToStart(key, ctrlKey, shiftKey, altKey, metaKey)) {
      dispatchCommand(editor, MOVE_TO_START, event);
    } else if (isMoveUp(key, ctrlKey, metaKey)) {
      dispatchCommand(editor, KEY_ARROW_UP_COMMAND, event);
    } else if (isMoveDown(key, ctrlKey, metaKey)) {
      dispatchCommand(editor, KEY_ARROW_DOWN_COMMAND, event);
    } else if (isLineBreak(key, shiftKey)) {
      isInsertLineBreak = true;
      dispatchCommand(editor, KEY_ENTER_COMMAND, event);
    } else if (isSpace(key)) {
      dispatchCommand(editor, KEY_SPACE_COMMAND, event);
    } else if (isOpenLineBreak(key, ctrlKey)) {
      event.preventDefault();
      isInsertLineBreak = true;
      dispatchCommand(editor, INSERT_LINE_BREAK_COMMAND, true);
    } else if (isParagraph(key, shiftKey)) {
      isInsertLineBreak = false;
      dispatchCommand(editor, KEY_ENTER_COMMAND, event);
    } else if (isDeleteBackward(key, altKey, metaKey, ctrlKey)) {
      if (isBackspace(key)) {
        dispatchCommand(editor, KEY_BACKSPACE_COMMAND, event);
      } else {
        event.preventDefault();
        dispatchCommand(editor, DELETE_CHARACTER_COMMAND, true);
      }
    } else if (isEscape(key)) {
      dispatchCommand(editor, KEY_ESCAPE_COMMAND, event);
    } else if (isDeleteForward(key, ctrlKey, shiftKey, altKey, metaKey)) {
      if (isDelete(key)) {
        dispatchCommand(editor, KEY_DELETE_COMMAND, event);
      } else {
        event.preventDefault();
        dispatchCommand(editor, DELETE_CHARACTER_COMMAND, false);
      }
    } else if (isDeleteWordBackward(key, altKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, DELETE_WORD_COMMAND, true);
    } else if (isDeleteWordForward(key, altKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, DELETE_WORD_COMMAND, false);
    } else if (isDeleteLineBackward(key, metaKey)) {
      event.preventDefault();
      dispatchCommand(editor, DELETE_LINE_COMMAND, true);
    } else if (isDeleteLineForward(key, metaKey)) {
      event.preventDefault();
      dispatchCommand(editor, DELETE_LINE_COMMAND, false);
    } else if (isBold(key, altKey, metaKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'bold');
    } else if (isUnderline(key, altKey, metaKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'underline');
    } else if (isItalic(key, altKey, metaKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, FORMAT_TEXT_COMMAND, 'italic');
    } else if (isTab(key, altKey, ctrlKey, metaKey)) {
      dispatchCommand(editor, KEY_TAB_COMMAND, event);
    } else if (isUndo(key, shiftKey, metaKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, UNDO_COMMAND, undefined);
    } else if (isRedo(key, shiftKey, metaKey, ctrlKey)) {
      event.preventDefault();
      dispatchCommand(editor, REDO_COMMAND, undefined);
    } else {
      const prevSelection = editor._editorState._selection;
      if ($isNodeSelection(prevSelection)) {
        if (isCopy(key, shiftKey, metaKey, ctrlKey)) {
          event.preventDefault();
          dispatchCommand(editor, COPY_COMMAND, event);
        } else if (isCut(key, shiftKey, metaKey, ctrlKey)) {
          event.preventDefault();
          dispatchCommand(editor, CUT_COMMAND, event);
        } else if (isSelectAll(key, metaKey, ctrlKey)) {
          event.preventDefault();
          dispatchCommand(editor, SELECT_ALL_COMMAND, event);
        }
        // FF does it well (no need to override behavior)
      } else if (!IS_FIREFOX && isSelectAll(key, metaKey, ctrlKey)) {
        event.preventDefault();
        dispatchCommand(editor, SELECT_ALL_COMMAND, event);
      }
    }
    if (isModifier(ctrlKey, shiftKey, altKey, metaKey)) {
      dispatchCommand(editor, KEY_MODIFIER_COMMAND, event);
    }
  }
  function getRootElementRemoveHandles(rootElement) {
    // @ts-expect-error: internal field
    let eventHandles = rootElement.__lexicalEventHandles;
    if (eventHandles === undefined) {
      eventHandles = [];
      // @ts-expect-error: internal field
      rootElement.__lexicalEventHandles = eventHandles;
    }
    return eventHandles;
  }

  // Mapping root editors to their active nested editors, contains nested editors
  // mapping only, so if root editor is selected map will have no reference to free up memory
  const activeNestedEditorsMap = new Map();
  function onDocumentSelectionChange(event) {
    const target = event.target;
    const targetWindow = target == null ? null : target.nodeType === 9 ? target.defaultView : target.ownerDocument.defaultView;
    const domSelection = getDOMSelection(targetWindow);
    if (domSelection === null) {
      return;
    }
    const nextActiveEditor = getNearestEditorFromDOMNode(domSelection.anchorNode);
    if (nextActiveEditor === null) {
      return;
    }
    if (isSelectionChangeFromMouseDown) {
      isSelectionChangeFromMouseDown = false;
      updateEditor(nextActiveEditor, () => {
        const lastSelection = $getPreviousSelection();
        const domAnchorNode = domSelection.anchorNode;
        if (domAnchorNode === null) {
          return;
        }
        const nodeType = domAnchorNode.nodeType;
        // If the user is attempting to click selection back onto text, then
        // we should attempt create a range selection.
        // When we click on an empty paragraph node or the end of a paragraph that ends
        // with an image/poll, the nodeType will be ELEMENT_NODE
        if (nodeType !== DOM_ELEMENT_TYPE && nodeType !== DOM_TEXT_TYPE) {
          return;
        }
        const newSelection = $internalCreateRangeSelection(lastSelection, domSelection, nextActiveEditor, event);
        $setSelection(newSelection);
      });
    }

    // When editor receives selection change event, we're checking if
    // it has any sibling editors (within same parent editor) that were active
    // before, and trigger selection change on it to nullify selection.
    const editors = getEditorsToPropagate(nextActiveEditor);
    const rootEditor = editors[editors.length - 1];
    const rootEditorKey = rootEditor._key;
    const activeNestedEditor = activeNestedEditorsMap.get(rootEditorKey);
    const prevActiveEditor = activeNestedEditor || rootEditor;
    if (prevActiveEditor !== nextActiveEditor) {
      onSelectionChange(domSelection, prevActiveEditor, false);
    }
    onSelectionChange(domSelection, nextActiveEditor, true);

    // If newly selected editor is nested, then add it to the map, clean map otherwise
    if (nextActiveEditor !== rootEditor) {
      activeNestedEditorsMap.set(rootEditorKey, nextActiveEditor);
    } else if (activeNestedEditor) {
      activeNestedEditorsMap.delete(rootEditorKey);
    }
  }
  function stopLexicalPropagation(event) {
    // We attach a special property to ensure the same event doesn't re-fire
    // for parent editors.
    // @ts-ignore
    event._lexicalHandled = true;
  }
  function hasStoppedLexicalPropagation(event) {
    // @ts-ignore
    const stopped = event._lexicalHandled === true;
    return stopped;
  }
  function addRootElementEvents(rootElement, editor) {
    // We only want to have a single global selectionchange event handler, shared
    // between all editor instances.
    const doc = rootElement.ownerDocument;
    const documentRootElementsCount = rootElementsRegistered.get(doc);
    if (documentRootElementsCount === undefined || documentRootElementsCount < 1) {
      doc.addEventListener('selectionchange', onDocumentSelectionChange);
    }
    rootElementsRegistered.set(doc, (documentRootElementsCount || 0) + 1);

    // @ts-expect-error: internal field
    rootElement.__lexicalEditor = editor;
    const removeHandles = getRootElementRemoveHandles(rootElement);
    for (let i = 0; i < rootElementEvents.length; i++) {
      const [eventName, onEvent] = rootElementEvents[i];
      const eventHandler = typeof onEvent === 'function' ? event => {
        if (hasStoppedLexicalPropagation(event)) {
          return;
        }
        stopLexicalPropagation(event);
        if (editor.isEditable() || eventName === 'click') {
          onEvent(event, editor);
        }
      } : event => {
        if (hasStoppedLexicalPropagation(event)) {
          return;
        }
        stopLexicalPropagation(event);
        const isEditable = editor.isEditable();
        switch (eventName) {
          case 'cut':
            return isEditable && dispatchCommand(editor, CUT_COMMAND, event);
          case 'copy':
            return dispatchCommand(editor, COPY_COMMAND, event);
          case 'paste':
            return isEditable && dispatchCommand(editor, PASTE_COMMAND, event);
          case 'dragstart':
            return isEditable && dispatchCommand(editor, DRAGSTART_COMMAND, event);
          case 'dragover':
            return isEditable && dispatchCommand(editor, DRAGOVER_COMMAND, event);
          case 'dragend':
            return isEditable && dispatchCommand(editor, DRAGEND_COMMAND, event);
          case 'focus':
            return isEditable && dispatchCommand(editor, FOCUS_COMMAND, event);
          case 'blur':
            {
              return isEditable && dispatchCommand(editor, BLUR_COMMAND, event);
            }
          case 'drop':
            return isEditable && dispatchCommand(editor, DROP_COMMAND, event);
        }
      };
      rootElement.addEventListener(eventName, eventHandler);
      removeHandles.push(() => {
        rootElement.removeEventListener(eventName, eventHandler);
      });
    }
  }
  function removeRootElementEvents(rootElement) {
    const doc = rootElement.ownerDocument;
    const documentRootElementsCount = rootElementsRegistered.get(doc);
    if (!(documentRootElementsCount !== undefined)) {
      throw Error(`Root element not registered`);
    } // We only want to have a single global selectionchange event handler, shared
    // between all editor instances.
    const newCount = documentRootElementsCount - 1;
    if (!(newCount >= 0)) {
      throw Error(`Root element count less than 0`);
    }
    rootElementsRegistered.set(doc, newCount);
    if (newCount === 0) {
      doc.removeEventListener('selectionchange', onDocumentSelectionChange);
    }
    const editor = getEditorPropertyFromDOMNode(rootElement);
    if (isLexicalEditor(editor)) {
      cleanActiveNestedEditorsMap(editor);
      // @ts-expect-error: internal field
      rootElement.__lexicalEditor = null;
    } else if (editor) {
      {
        throw Error(`Attempted to remove event handlers from a node that does not belong to this build of Lexical`);
      }
    }
    const removeHandles = getRootElementRemoveHandles(rootElement);
    for (let i = 0; i < removeHandles.length; i++) {
      removeHandles[i]();
    }

    // @ts-expect-error: internal field
    rootElement.__lexicalEventHandles = [];
  }
  function cleanActiveNestedEditorsMap(editor) {
    if (editor._parentEditor !== null) {
      // For nested editor cleanup map if this editor was marked as active
      const editors = getEditorsToPropagate(editor);
      const rootEditor = editors[editors.length - 1];
      const rootEditorKey = rootEditor._key;
      if (activeNestedEditorsMap.get(rootEditorKey) === editor) {
        activeNestedEditorsMap.delete(rootEditorKey);
      }
    } else {
      // For top-level editors cleanup map
      activeNestedEditorsMap.delete(editor._key);
    }
  }
  function markSelectionChangeFromDOMUpdate() {
    isSelectionChangeFromDOMUpdate = true;
  }
  function markCollapsedSelectionFormat(format, style, offset, key, timeStamp) {
    collapsedSelectionFormat = [format, style, offset, key, timeStamp];
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function $removeNode(nodeToRemove, restoreSelection, preserveEmptyParent) {
    errorOnReadOnly();
    const key = nodeToRemove.__key;
    const parent = nodeToRemove.getParent();
    if (parent === null) {
      return;
    }
    const selection = $maybeMoveChildrenSelectionToParent(nodeToRemove);
    let selectionMoved = false;
    if ($isRangeSelection(selection) && restoreSelection) {
      const anchor = selection.anchor;
      const focus = selection.focus;
      if (anchor.key === key) {
        moveSelectionPointToSibling(anchor, nodeToRemove, parent, nodeToRemove.getPreviousSibling(), nodeToRemove.getNextSibling());
        selectionMoved = true;
      }
      if (focus.key === key) {
        moveSelectionPointToSibling(focus, nodeToRemove, parent, nodeToRemove.getPreviousSibling(), nodeToRemove.getNextSibling());
        selectionMoved = true;
      }
    } else if ($isNodeSelection(selection) && restoreSelection && nodeToRemove.isSelected()) {
      nodeToRemove.selectPrevious();
    }
    if ($isRangeSelection(selection) && restoreSelection && !selectionMoved) {
      // Doing this is O(n) so lets avoid it unless we need to do it
      const index = nodeToRemove.getIndexWithinParent();
      removeFromParent(nodeToRemove);
      $updateElementSelectionOnCreateDeleteNode(selection, parent, index, -1);
    } else {
      removeFromParent(nodeToRemove);
    }
    if (!preserveEmptyParent && !$isRootOrShadowRoot(parent) && !parent.canBeEmpty() && parent.isEmpty()) {
      $removeNode(parent, restoreSelection);
    }
    if (restoreSelection && $isRootNode(parent) && parent.isEmpty()) {
      parent.selectEnd();
    }
  }
  class LexicalNode {
    // Allow us to look up the type including static props

    /** @internal */

    /** @internal */
    //@ts-ignore We set the key in the constructor.

    /** @internal */

    /** @internal */

    /** @internal */

    // Flow doesn't support abstract classes unfortunately, so we can't _force_
    // subclasses of Node to implement statics. All subclasses of Node should have
    // a static getType and clone method though. We define getType and clone here so we can call it
    // on any  Node, and we throw this error by default since the subclass should provide
    // their own implementation.
    /**
     * Returns the string type of this node. Every node must
     * implement this and it MUST BE UNIQUE amongst nodes registered
     * on the editor.
     *
     */
    static getType() {
      {
        throw Error(`LexicalNode: Node ${this.name} does not implement .getType().`);
      }
    }

    /**
     * Clones this node, creating a new node with a different key
     * and adding it to the EditorState (but not attaching it anywhere!). All nodes must
     * implement this method.
     *
     */
    static clone(_data) {
      {
        throw Error(`LexicalNode: Node ${this.name} does not implement .clone().`);
      }
    }

    /**
     * Perform any state updates on the clone of prevNode that are not already
     * handled by the constructor call in the static clone method. If you have
     * state to update in your clone that is not handled directly by the
     * constructor, it is advisable to override this method but it is required
     * to include a call to `super.afterCloneFrom(prevNode)` in your
     * implementation. This is only intended to be called by
     * {@link $cloneWithProperties} function or via a super call.
     *
     * @example
     * ```ts
     * class ClassesTextNode extends TextNode {
     *   // Not shown: static getType, static importJSON, exportJSON, createDOM, updateDOM
     *   __classes = new Set<string>();
     *   static clone(node: ClassesTextNode): ClassesTextNode {
     *     // The inherited TextNode constructor is used here, so
     *     // classes is not set by this method.
     *     return new ClassesTextNode(node.__text, node.__key);
     *   }
     *   afterCloneFrom(node: this): void {
     *     // This calls TextNode.afterCloneFrom and LexicalNode.afterCloneFrom
     *     // for necessary state updates
     *     super.afterCloneFrom(node);
     *     this.__addClasses(node.__classes);
     *   }
     *   // This method is a private implementation detail, it is not
     *   // suitable for the public API because it does not call getWritable
     *   __addClasses(classNames: Iterable<string>): this {
     *     for (const className of classNames) {
     *       this.__classes.add(className);
     *     }
     *     return this;
     *   }
     *   addClass(...classNames: string[]): this {
     *     return this.getWritable().__addClasses(classNames);
     *   }
     *   removeClass(...classNames: string[]): this {
     *     const node = this.getWritable();
     *     for (const className of classNames) {
     *       this.__classes.delete(className);
     *     }
     *     return this;
     *   }
     *   getClasses(): Set<string> {
     *     return this.getLatest().__classes;
     *   }
     * }
     * ```
     *
     */
    afterCloneFrom(prevNode) {
      this.__parent = prevNode.__parent;
      this.__next = prevNode.__next;
      this.__prev = prevNode.__prev;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any

    constructor(key) {
      this.__type = this.constructor.getType();
      this.__parent = null;
      this.__prev = null;
      this.__next = null;
      $setNodeKey(this, key);
      {
        if (this.__type !== 'root') {
          errorOnReadOnly();
          errorOnTypeKlassMismatch(this.__type, this.constructor);
        }
      }
    }
    // Getters and Traversers

    /**
     * Returns the string type of this node.
     */
    getType() {
      return this.__type;
    }
    isInline() {
      {
        throw Error(`LexicalNode: Node ${this.constructor.name} does not implement .isInline().`);
      }
    }

    /**
     * Returns true if there is a path between this node and the RootNode, false otherwise.
     * This is a way of determining if the node is "attached" EditorState. Unattached nodes
     * won't be reconciled and will ultimatelt be cleaned up by the Lexical GC.
     */
    isAttached() {
      let nodeKey = this.__key;
      while (nodeKey !== null) {
        if (nodeKey === 'root') {
          return true;
        }
        const node = $getNodeByKey(nodeKey);
        if (node === null) {
          break;
        }
        nodeKey = node.__parent;
      }
      return false;
    }

    /**
     * Returns true if this node is contained within the provided Selection., false otherwise.
     * Relies on the algorithms implemented in {@link BaseSelection.getNodes} to determine
     * what's included.
     *
     * @param selection - The selection that we want to determine if the node is in.
     */
    isSelected(selection) {
      const targetSelection = selection || $getSelection();
      if (targetSelection == null) {
        return false;
      }
      const isSelected = targetSelection.getNodes().some(n => n.__key === this.__key);
      if ($isTextNode(this)) {
        return isSelected;
      }
      // For inline images inside of element nodes.
      // Without this change the image will be selected if the cursor is before or after it.
      const isElementRangeSelection = $isRangeSelection(targetSelection) && targetSelection.anchor.type === 'element' && targetSelection.focus.type === 'element';
      if (isElementRangeSelection) {
        if (targetSelection.isCollapsed()) {
          return false;
        }
        const parentNode = this.getParent();
        if ($isDecoratorNode(this) && this.isInline() && parentNode) {
          const firstPoint = targetSelection.isBackward() ? targetSelection.focus : targetSelection.anchor;
          const firstElement = firstPoint.getNode();
          if (firstPoint.offset === firstElement.getChildrenSize() && firstElement.is(parentNode) && firstElement.getLastChildOrThrow().is(this)) {
            return false;
          }
        }
      }
      return isSelected;
    }

    /**
     * Returns this nodes key.
     */
    getKey() {
      // Key is stable between copies
      return this.__key;
    }

    /**
     * Returns the zero-based index of this node within the parent.
     */
    getIndexWithinParent() {
      const parent = this.getParent();
      if (parent === null) {
        return -1;
      }
      let node = parent.getFirstChild();
      let index = 0;
      while (node !== null) {
        if (this.is(node)) {
          return index;
        }
        index++;
        node = node.getNextSibling();
      }
      return -1;
    }

    /**
     * Returns the parent of this node, or null if none is found.
     */
    getParent() {
      const parent = this.getLatest().__parent;
      if (parent === null) {
        return null;
      }
      return $getNodeByKey(parent);
    }

    /**
     * Returns the parent of this node, or throws if none is found.
     */
    getParentOrThrow() {
      const parent = this.getParent();
      if (parent === null) {
        {
          throw Error(`Expected node ${this.__key} to have a parent.`);
        }
      }
      return parent;
    }

    /**
     * Returns the highest (in the EditorState tree)
     * non-root ancestor of this node, or null if none is found. See {@link lexical!$isRootOrShadowRoot}
     * for more information on which Elements comprise "roots".
     */
    getTopLevelElement() {
      let node = this;
      while (node !== null) {
        const parent = node.getParent();
        if ($isRootOrShadowRoot(parent)) {
          if (!($isElementNode(node) || node === this && $isDecoratorNode(node))) {
            throw Error(`Children of root nodes must be elements or decorators`);
          }
          return node;
        }
        node = parent;
      }
      return null;
    }

    /**
     * Returns the highest (in the EditorState tree)
     * non-root ancestor of this node, or throws if none is found. See {@link lexical!$isRootOrShadowRoot}
     * for more information on which Elements comprise "roots".
     */
    getTopLevelElementOrThrow() {
      const parent = this.getTopLevelElement();
      if (parent === null) {
        {
          throw Error(`Expected node ${this.__key} to have a top parent element.`);
        }
      }
      return parent;
    }

    /**
     * Returns a list of the every ancestor of this node,
     * all the way up to the RootNode.
     *
     */
    getParents() {
      const parents = [];
      let node = this.getParent();
      while (node !== null) {
        parents.push(node);
        node = node.getParent();
      }
      return parents;
    }

    /**
     * Returns a list of the keys of every ancestor of this node,
     * all the way up to the RootNode.
     *
     */
    getParentKeys() {
      const parents = [];
      let node = this.getParent();
      while (node !== null) {
        parents.push(node.__key);
        node = node.getParent();
      }
      return parents;
    }

    /**
     * Returns the "previous" siblings - that is, the node that comes
     * before this one in the same parent.
     *
     */
    getPreviousSibling() {
      const self = this.getLatest();
      const prevKey = self.__prev;
      return prevKey === null ? null : $getNodeByKey(prevKey);
    }

    /**
     * Returns the "previous" siblings - that is, the nodes that come between
     * this one and the first child of it's parent, inclusive.
     *
     */
    getPreviousSiblings() {
      const siblings = [];
      const parent = this.getParent();
      if (parent === null) {
        return siblings;
      }
      let node = parent.getFirstChild();
      while (node !== null) {
        if (node.is(this)) {
          break;
        }
        siblings.push(node);
        node = node.getNextSibling();
      }
      return siblings;
    }

    /**
     * Returns the "next" siblings - that is, the node that comes
     * after this one in the same parent
     *
     */
    getNextSibling() {
      const self = this.getLatest();
      const nextKey = self.__next;
      return nextKey === null ? null : $getNodeByKey(nextKey);
    }

    /**
     * Returns all "next" siblings - that is, the nodes that come between this
     * one and the last child of it's parent, inclusive.
     *
     */
    getNextSiblings() {
      const siblings = [];
      let node = this.getNextSibling();
      while (node !== null) {
        siblings.push(node);
        node = node.getNextSibling();
      }
      return siblings;
    }

    /**
     * Returns the closest common ancestor of this node and the provided one or null
     * if one cannot be found.
     *
     * @param node - the other node to find the common ancestor of.
     */
    getCommonAncestor(node) {
      const a = this.getParents();
      const b = node.getParents();
      if ($isElementNode(this)) {
        a.unshift(this);
      }
      if ($isElementNode(node)) {
        b.unshift(node);
      }
      const aLength = a.length;
      const bLength = b.length;
      if (aLength === 0 || bLength === 0 || a[aLength - 1] !== b[bLength - 1]) {
        return null;
      }
      const bSet = new Set(b);
      for (let i = 0; i < aLength; i++) {
        const ancestor = a[i];
        if (bSet.has(ancestor)) {
          return ancestor;
        }
      }
      return null;
    }

    /**
     * Returns true if the provided node is the exact same one as this node, from Lexical's perspective.
     * Always use this instead of referential equality.
     *
     * @param object - the node to perform the equality comparison on.
     */
    is(object) {
      if (object == null) {
        return false;
      }
      return this.__key === object.__key;
    }

    /**
     * Returns true if this node logical precedes the target node in the editor state.
     *
     * @param targetNode - the node we're testing to see if it's after this one.
     */
    isBefore(targetNode) {
      if (this === targetNode) {
        return false;
      }
      if (targetNode.isParentOf(this)) {
        return true;
      }
      if (this.isParentOf(targetNode)) {
        return false;
      }
      const commonAncestor = this.getCommonAncestor(targetNode);
      let indexA = 0;
      let indexB = 0;
      let node = this;
      while (true) {
        const parent = node.getParentOrThrow();
        if (parent === commonAncestor) {
          indexA = node.getIndexWithinParent();
          break;
        }
        node = parent;
      }
      node = targetNode;
      while (true) {
        const parent = node.getParentOrThrow();
        if (parent === commonAncestor) {
          indexB = node.getIndexWithinParent();
          break;
        }
        node = parent;
      }
      return indexA < indexB;
    }

    /**
     * Returns true if this node is the parent of the target node, false otherwise.
     *
     * @param targetNode - the would-be child node.
     */
    isParentOf(targetNode) {
      const key = this.__key;
      if (key === targetNode.__key) {
        return false;
      }
      let node = targetNode;
      while (node !== null) {
        if (node.__key === key) {
          return true;
        }
        node = node.getParent();
      }
      return false;
    }

    // TO-DO: this function can be simplified a lot
    /**
     * Returns a list of nodes that are between this node and
     * the target node in the EditorState.
     *
     * @param targetNode - the node that marks the other end of the range of nodes to be returned.
     */
    getNodesBetween(targetNode) {
      const isBefore = this.isBefore(targetNode);
      const nodes = [];
      const visited = new Set();
      let node = this;
      while (true) {
        if (node === null) {
          break;
        }
        const key = node.__key;
        if (!visited.has(key)) {
          visited.add(key);
          nodes.push(node);
        }
        if (node === targetNode) {
          break;
        }
        const child = $isElementNode(node) ? isBefore ? node.getFirstChild() : node.getLastChild() : null;
        if (child !== null) {
          node = child;
          continue;
        }
        const nextSibling = isBefore ? node.getNextSibling() : node.getPreviousSibling();
        if (nextSibling !== null) {
          node = nextSibling;
          continue;
        }
        const parent = node.getParentOrThrow();
        if (!visited.has(parent.__key)) {
          nodes.push(parent);
        }
        if (parent === targetNode) {
          break;
        }
        let parentSibling = null;
        let ancestor = parent;
        do {
          if (ancestor === null) {
            {
              throw Error(`getNodesBetween: ancestor is null`);
            }
          }
          parentSibling = isBefore ? ancestor.getNextSibling() : ancestor.getPreviousSibling();
          ancestor = ancestor.getParent();
          if (ancestor !== null) {
            if (parentSibling === null && !visited.has(ancestor.__key)) {
              nodes.push(ancestor);
            }
          } else {
            break;
          }
        } while (parentSibling === null);
        node = parentSibling;
      }
      if (!isBefore) {
        nodes.reverse();
      }
      return nodes;
    }

    /**
     * Returns true if this node has been marked dirty during this update cycle.
     *
     */
    isDirty() {
      const editor = getActiveEditor();
      const dirtyLeaves = editor._dirtyLeaves;
      return dirtyLeaves !== null && dirtyLeaves.has(this.__key);
    }

    /**
     * Returns the latest version of the node from the active EditorState.
     * This is used to avoid getting values from stale node references.
     *
     */
    getLatest() {
      const latest = $getNodeByKey(this.__key);
      if (latest === null) {
        {
          throw Error(`Lexical node does not exist in active editor state. Avoid using the same node references between nested closures from editorState.read/editor.update.`);
        }
      }
      return latest;
    }

    /**
     * Returns a mutable version of the node using {@link $cloneWithProperties}
     * if necessary. Will throw an error if called outside of a Lexical Editor
     * {@link LexicalEditor.update} callback.
     *
     */
    getWritable() {
      errorOnReadOnly();
      const editorState = getActiveEditorState();
      const editor = getActiveEditor();
      const nodeMap = editorState._nodeMap;
      const key = this.__key;
      // Ensure we get the latest node from pending state
      const latestNode = this.getLatest();
      const cloneNotNeeded = editor._cloneNotNeeded;
      const selection = $getSelection();
      if (selection !== null) {
        selection.setCachedNodes(null);
      }
      if (cloneNotNeeded.has(key)) {
        // Transforms clear the dirty node set on each iteration to keep track on newly dirty nodes
        internalMarkNodeAsDirty(latestNode);
        return latestNode;
      }
      const mutableNode = $cloneWithProperties(latestNode);
      cloneNotNeeded.add(key);
      internalMarkNodeAsDirty(mutableNode);
      // Update reference in node map
      nodeMap.set(key, mutableNode);
      return mutableNode;
    }

    /**
     * Returns the text content of the node. Override this for
     * custom nodes that should have a representation in plain text
     * format (for copy + paste, for example)
     *
     */
    getTextContent() {
      return '';
    }

    /**
     * Returns the length of the string produced by calling getTextContent on this node.
     *
     */
    getTextContentSize() {
      return this.getTextContent().length;
    }

    // View

    /**
     * Called during the reconciliation process to determine which nodes
     * to insert into the DOM for this Lexical Node.
     *
     * This method must return exactly one HTMLElement. Nested elements are not supported.
     *
     * Do not attempt to update the Lexical EditorState during this phase of the update lifecyle.
     *
     * @param _config - allows access to things like the EditorTheme (to apply classes) during reconciliation.
     * @param _editor - allows access to the editor for context during reconciliation.
     *
     * */
    createDOM(_config, _editor) {
      {
        throw Error(`createDOM: base method not extended`);
      }
    }

    /**
     * Called when a node changes and should update the DOM
     * in whatever way is necessary to make it align with any changes that might
     * have happened during the update.
     *
     * Returning "true" here will cause lexical to unmount and recreate the DOM node
     * (by calling createDOM). You would need to do this if the element tag changes,
     * for instance.
     *
     * */
    updateDOM(_prevNode, _dom, _config) {
      {
        throw Error(`updateDOM: base method not extended`);
      }
    }

    /**
     * Controls how the this node is serialized to HTML. This is important for
     * copy and paste between Lexical and non-Lexical editors, or Lexical editors with different namespaces,
     * in which case the primary transfer format is HTML. It's also important if you're serializing
     * to HTML for any other reason via {@link @lexical/html!$generateHtmlFromNodes}. You could
     * also use this method to build your own HTML renderer.
     *
     * */
    exportDOM(editor) {
      const element = this.createDOM(editor._config, editor);
      return {
        element
      };
    }

    /**
     * Controls how the this node is serialized to JSON. This is important for
     * copy and paste between Lexical editors sharing the same namespace. It's also important
     * if you're serializing to JSON for persistent storage somewhere.
     * See [Serialization & Deserialization](https://lexical.dev/docs/concepts/serialization#lexical---html).
     *
     * */
    exportJSON() {
      {
        throw Error(`exportJSON: base method not extended`);
      }
    }

    /**
     * Controls how the this node is deserialized from JSON. This is usually boilerplate,
     * but provides an abstraction between the node implementation and serialized interface that can
     * be important if you ever make breaking changes to a node schema (by adding or removing properties).
     * See [Serialization & Deserialization](https://lexical.dev/docs/concepts/serialization#lexical---html).
     *
     * */
    static importJSON(_serializedNode) {
      {
        throw Error(`LexicalNode: Node ${this.name} does not implement .importJSON().`);
      }
    }
    /**
     * @experimental
     *
     * Registers the returned function as a transform on the node during
     * Editor initialization. Most such use cases should be addressed via
     * the {@link LexicalEditor.registerNodeTransform} API.
     *
     * Experimental - use at your own risk.
     */
    static transform() {
      return null;
    }

    // Setters and mutators

    /**
     * Removes this LexicalNode from the EditorState. If the node isn't re-inserted
     * somewhere, the Lexical garbage collector will eventually clean it up.
     *
     * @param preserveEmptyParent - If falsy, the node's parent will be removed if
     * it's empty after the removal operation. This is the default behavior, subject to
     * other node heuristics such as {@link ElementNode#canBeEmpty}
     * */
    remove(preserveEmptyParent) {
      $removeNode(this, true, preserveEmptyParent);
    }

    /**
     * Replaces this LexicalNode with the provided node, optionally transferring the children
     * of the replaced node to the replacing node.
     *
     * @param replaceWith - The node to replace this one with.
     * @param includeChildren - Whether or not to transfer the children of this node to the replacing node.
     * */
    replace(replaceWith, includeChildren) {
      errorOnReadOnly();
      let selection = $getSelection();
      if (selection !== null) {
        selection = selection.clone();
      }
      errorOnInsertTextNodeOnRoot(this, replaceWith);
      const self = this.getLatest();
      const toReplaceKey = this.__key;
      const key = replaceWith.__key;
      const writableReplaceWith = replaceWith.getWritable();
      const writableParent = this.getParentOrThrow().getWritable();
      const size = writableParent.__size;
      removeFromParent(writableReplaceWith);
      const prevSibling = self.getPreviousSibling();
      const nextSibling = self.getNextSibling();
      const prevKey = self.__prev;
      const nextKey = self.__next;
      const parentKey = self.__parent;
      $removeNode(self, false, true);
      if (prevSibling === null) {
        writableParent.__first = key;
      } else {
        const writablePrevSibling = prevSibling.getWritable();
        writablePrevSibling.__next = key;
      }
      writableReplaceWith.__prev = prevKey;
      if (nextSibling === null) {
        writableParent.__last = key;
      } else {
        const writableNextSibling = nextSibling.getWritable();
        writableNextSibling.__prev = key;
      }
      writableReplaceWith.__next = nextKey;
      writableReplaceWith.__parent = parentKey;
      writableParent.__size = size;
      if (includeChildren) {
        if (!($isElementNode(this) && $isElementNode(writableReplaceWith))) {
          throw Error(`includeChildren should only be true for ElementNodes`);
        }
        this.getChildren().forEach(child => {
          writableReplaceWith.append(child);
        });
      }
      if ($isRangeSelection(selection)) {
        $setSelection(selection);
        const anchor = selection.anchor;
        const focus = selection.focus;
        if (anchor.key === toReplaceKey) {
          $moveSelectionPointToEnd(anchor, writableReplaceWith);
        }
        if (focus.key === toReplaceKey) {
          $moveSelectionPointToEnd(focus, writableReplaceWith);
        }
      }
      if ($getCompositionKey() === toReplaceKey) {
        $setCompositionKey(key);
      }
      return writableReplaceWith;
    }

    /**
     * Inserts a node after this LexicalNode (as the next sibling).
     *
     * @param nodeToInsert - The node to insert after this one.
     * @param restoreSelection - Whether or not to attempt to resolve the
     * selection to the appropriate place after the operation is complete.
     * */
    insertAfter(nodeToInsert, restoreSelection = true) {
      errorOnReadOnly();
      errorOnInsertTextNodeOnRoot(this, nodeToInsert);
      const writableSelf = this.getWritable();
      const writableNodeToInsert = nodeToInsert.getWritable();
      const oldParent = writableNodeToInsert.getParent();
      const selection = $getSelection();
      let elementAnchorSelectionOnNode = false;
      let elementFocusSelectionOnNode = false;
      if (oldParent !== null) {
        // TODO: this is O(n), can we improve?
        const oldIndex = nodeToInsert.getIndexWithinParent();
        removeFromParent(writableNodeToInsert);
        if ($isRangeSelection(selection)) {
          const oldParentKey = oldParent.__key;
          const anchor = selection.anchor;
          const focus = selection.focus;
          elementAnchorSelectionOnNode = anchor.type === 'element' && anchor.key === oldParentKey && anchor.offset === oldIndex + 1;
          elementFocusSelectionOnNode = focus.type === 'element' && focus.key === oldParentKey && focus.offset === oldIndex + 1;
        }
      }
      const nextSibling = this.getNextSibling();
      const writableParent = this.getParentOrThrow().getWritable();
      const insertKey = writableNodeToInsert.__key;
      const nextKey = writableSelf.__next;
      if (nextSibling === null) {
        writableParent.__last = insertKey;
      } else {
        const writableNextSibling = nextSibling.getWritable();
        writableNextSibling.__prev = insertKey;
      }
      writableParent.__size++;
      writableSelf.__next = insertKey;
      writableNodeToInsert.__next = nextKey;
      writableNodeToInsert.__prev = writableSelf.__key;
      writableNodeToInsert.__parent = writableSelf.__parent;
      if (restoreSelection && $isRangeSelection(selection)) {
        const index = this.getIndexWithinParent();
        $updateElementSelectionOnCreateDeleteNode(selection, writableParent, index + 1);
        const writableParentKey = writableParent.__key;
        if (elementAnchorSelectionOnNode) {
          selection.anchor.set(writableParentKey, index + 2, 'element');
        }
        if (elementFocusSelectionOnNode) {
          selection.focus.set(writableParentKey, index + 2, 'element');
        }
      }
      return nodeToInsert;
    }

    /**
     * Inserts a node before this LexicalNode (as the previous sibling).
     *
     * @param nodeToInsert - The node to insert before this one.
     * @param restoreSelection - Whether or not to attempt to resolve the
     * selection to the appropriate place after the operation is complete.
     * */
    insertBefore(nodeToInsert, restoreSelection = true) {
      errorOnReadOnly();
      errorOnInsertTextNodeOnRoot(this, nodeToInsert);
      const writableSelf = this.getWritable();
      const writableNodeToInsert = nodeToInsert.getWritable();
      const insertKey = writableNodeToInsert.__key;
      removeFromParent(writableNodeToInsert);
      const prevSibling = this.getPreviousSibling();
      const writableParent = this.getParentOrThrow().getWritable();
      const prevKey = writableSelf.__prev;
      // TODO: this is O(n), can we improve?
      const index = this.getIndexWithinParent();
      if (prevSibling === null) {
        writableParent.__first = insertKey;
      } else {
        const writablePrevSibling = prevSibling.getWritable();
        writablePrevSibling.__next = insertKey;
      }
      writableParent.__size++;
      writableSelf.__prev = insertKey;
      writableNodeToInsert.__prev = prevKey;
      writableNodeToInsert.__next = writableSelf.__key;
      writableNodeToInsert.__parent = writableSelf.__parent;
      const selection = $getSelection();
      if (restoreSelection && $isRangeSelection(selection)) {
        const parent = this.getParentOrThrow();
        $updateElementSelectionOnCreateDeleteNode(selection, parent, index);
      }
      return nodeToInsert;
    }

    /**
     * Whether or not this node has a required parent. Used during copy + paste operations
     * to normalize nodes that would otherwise be orphaned. For example, ListItemNodes without
     * a ListNode parent or TextNodes with a ParagraphNode parent.
     *
     * */
    isParentRequired() {
      return false;
    }

    /**
     * The creation logic for any required parent. Should be implemented if {@link isParentRequired} returns true.
     *
     * */
    createParentElementNode() {
      return $createParagraphNode();
    }
    selectStart() {
      return this.selectPrevious();
    }
    selectEnd() {
      return this.selectNext(0, 0);
    }

    /**
     * Moves selection to the previous sibling of this node, at the specified offsets.
     *
     * @param anchorOffset - The anchor offset for selection.
     * @param focusOffset -  The focus offset for selection
     * */
    selectPrevious(anchorOffset, focusOffset) {
      errorOnReadOnly();
      const prevSibling = this.getPreviousSibling();
      const parent = this.getParentOrThrow();
      if (prevSibling === null) {
        return parent.select(0, 0);
      }
      if ($isElementNode(prevSibling)) {
        return prevSibling.select();
      } else if (!$isTextNode(prevSibling)) {
        const index = prevSibling.getIndexWithinParent() + 1;
        return parent.select(index, index);
      }
      return prevSibling.select(anchorOffset, focusOffset);
    }

    /**
     * Moves selection to the next sibling of this node, at the specified offsets.
     *
     * @param anchorOffset - The anchor offset for selection.
     * @param focusOffset -  The focus offset for selection
     * */
    selectNext(anchorOffset, focusOffset) {
      errorOnReadOnly();
      const nextSibling = this.getNextSibling();
      const parent = this.getParentOrThrow();
      if (nextSibling === null) {
        return parent.select();
      }
      if ($isElementNode(nextSibling)) {
        return nextSibling.select(0, 0);
      } else if (!$isTextNode(nextSibling)) {
        const index = nextSibling.getIndexWithinParent();
        return parent.select(index, index);
      }
      return nextSibling.select(anchorOffset, focusOffset);
    }

    /**
     * Marks a node dirty, triggering transforms and
     * forcing it to be reconciled during the update cycle.
     *
     * */
    markDirty() {
      this.getWritable();
    }
  }
  function errorOnTypeKlassMismatch(type, klass) {
    const registeredNode = getActiveEditor()._nodes.get(type);
    // Common error - split in its own invariant
    if (registeredNode === undefined) {
      {
        throw Error(`Create node: Attempted to create node ${klass.name} that was not configured to be used on the editor.`);
      }
    }
    const editorKlass = registeredNode.klass;
    if (editorKlass !== klass) {
      {
        throw Error(`Create node: Type ${type} in node ${klass.name} does not match registered node ${editorKlass.name} with the same type`);
      }
    }
  }

  /**
   * Insert a series of nodes after this LexicalNode (as next siblings)
   *
   * @param firstToInsert - The first node to insert after this one.
   * @param lastToInsert - The last node to insert after this one. Must be a
   * later sibling of FirstNode. If not provided, it will be its last sibling.
   */
  function insertRangeAfter(node, firstToInsert, lastToInsert) {
    const lastToInsert2 = firstToInsert.getParentOrThrow().getLastChild();
    let current = firstToInsert;
    const nodesToInsert = [firstToInsert];
    while (current !== lastToInsert2) {
      if (!current.getNextSibling()) {
        {
          throw Error(`insertRangeAfter: lastToInsert must be a later sibling of firstToInsert`);
        }
      }
      current = current.getNextSibling();
      nodesToInsert.push(current);
    }
    let currentNode = node;
    for (const nodeToInsert of nodesToInsert) {
      currentNode = currentNode.insertAfter(nodeToInsert);
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class LineBreakNode extends LexicalNode {
    static getType() {
      return 'linebreak';
    }
    static clone(node) {
      return new LineBreakNode(node.__key);
    }
    constructor(key) {
      super(key);
    }
    getTextContent() {
      return '\n';
    }
    createDOM() {
      return document.createElement('br');
    }
    updateDOM() {
      return false;
    }
    static importDOM() {
      return {
        br: node => {
          if (isOnlyChildInBlockNode(node) || isLastChildInBlockNode(node)) {
            return null;
          }
          return {
            conversion: $convertLineBreakElement,
            priority: 0
          };
        }
      };
    }
    static importJSON(serializedLineBreakNode) {
      return $createLineBreakNode();
    }
    exportJSON() {
      return {
        type: 'linebreak',
        version: 1
      };
    }
  }
  function $convertLineBreakElement(node) {
    return {
      node: $createLineBreakNode()
    };
  }
  function $createLineBreakNode() {
    return $applyNodeReplacement(new LineBreakNode());
  }
  function $isLineBreakNode(node) {
    return node instanceof LineBreakNode;
  }
  function isOnlyChildInBlockNode(node) {
    const parentElement = node.parentElement;
    if (parentElement !== null && isBlockDomNode(parentElement)) {
      const firstChild = parentElement.firstChild;
      if (firstChild === node || firstChild.nextSibling === node && isWhitespaceDomTextNode(firstChild)) {
        const lastChild = parentElement.lastChild;
        if (lastChild === node || lastChild.previousSibling === node && isWhitespaceDomTextNode(lastChild)) {
          return true;
        }
      }
    }
    return false;
  }
  function isLastChildInBlockNode(node) {
    const parentElement = node.parentElement;
    if (parentElement !== null && isBlockDomNode(parentElement)) {
      // check if node is first child, because only childs dont count
      const firstChild = parentElement.firstChild;
      if (firstChild === node || firstChild.nextSibling === node && isWhitespaceDomTextNode(firstChild)) {
        return false;
      }

      // check if its last child
      const lastChild = parentElement.lastChild;
      if (lastChild === node || lastChild.previousSibling === node && isWhitespaceDomTextNode(lastChild)) {
        return true;
      }
    }
    return false;
  }
  function isWhitespaceDomTextNode(node) {
    return node.nodeType === DOM_TEXT_TYPE && /^( |\t|\r?\n)+$/.test(node.textContent || '');
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function getElementOuterTag(node, format) {
    if (format & IS_CODE) {
      return 'code';
    }
    if (format & IS_HIGHLIGHT) {
      return 'mark';
    }
    if (format & IS_SUBSCRIPT) {
      return 'sub';
    }
    if (format & IS_SUPERSCRIPT) {
      return 'sup';
    }
    return null;
  }
  function getElementInnerTag(node, format) {
    if (format & IS_BOLD) {
      return 'strong';
    }
    if (format & IS_ITALIC) {
      return 'em';
    }
    return 'span';
  }
  function setTextThemeClassNames(tag, prevFormat, nextFormat, dom, textClassNames) {
    const domClassList = dom.classList;
    // Firstly we handle the base theme.
    let classNames = getCachedClassNameArray(textClassNames, 'base');
    if (classNames !== undefined) {
      domClassList.add(...classNames);
    }
    // Secondly we handle the special case: underline + strikethrough.
    // We have to do this as we need a way to compose the fact that
    // the same CSS property will need to be used: text-decoration.
    // In an ideal world we shouldn't have to do this, but there's no
    // easy workaround for many atomic CSS systems today.
    classNames = getCachedClassNameArray(textClassNames, 'underlineStrikethrough');
    let hasUnderlineStrikethrough = false;
    const prevUnderlineStrikethrough = prevFormat & IS_UNDERLINE && prevFormat & IS_STRIKETHROUGH;
    const nextUnderlineStrikethrough = nextFormat & IS_UNDERLINE && nextFormat & IS_STRIKETHROUGH;
    if (classNames !== undefined) {
      if (nextUnderlineStrikethrough) {
        hasUnderlineStrikethrough = true;
        if (!prevUnderlineStrikethrough) {
          domClassList.add(...classNames);
        }
      } else if (prevUnderlineStrikethrough) {
        domClassList.remove(...classNames);
      }
    }
    for (const key in TEXT_TYPE_TO_FORMAT) {
      const format = key;
      const flag = TEXT_TYPE_TO_FORMAT[format];
      classNames = getCachedClassNameArray(textClassNames, key);
      if (classNames !== undefined) {
        if (nextFormat & flag) {
          if (hasUnderlineStrikethrough && (key === 'underline' || key === 'strikethrough')) {
            if (prevFormat & flag) {
              domClassList.remove(...classNames);
            }
            continue;
          }
          if ((prevFormat & flag) === 0 || prevUnderlineStrikethrough && key === 'underline' || key === 'strikethrough') {
            domClassList.add(...classNames);
          }
        } else if (prevFormat & flag) {
          domClassList.remove(...classNames);
        }
      }
    }
  }
  function diffComposedText(a, b) {
    const aLength = a.length;
    const bLength = b.length;
    let left = 0;
    let right = 0;
    while (left < aLength && left < bLength && a[left] === b[left]) {
      left++;
    }
    while (right + left < aLength && right + left < bLength && a[aLength - right - 1] === b[bLength - right - 1]) {
      right++;
    }
    return [left, aLength - left - right, b.slice(left, bLength - right)];
  }
  function setTextContent(nextText, dom, node) {
    const firstChild = dom.firstChild;
    const isComposing = node.isComposing();
    // Always add a suffix if we're composing a node
    const suffix = isComposing ? COMPOSITION_SUFFIX : '';
    const text = nextText + suffix;
    if (firstChild == null) {
      dom.textContent = text;
    } else {
      const nodeValue = firstChild.nodeValue;
      if (nodeValue !== text) {
        if (isComposing || IS_FIREFOX) {
          // We also use the diff composed text for general text in FF to avoid
          // the spellcheck red line from flickering.
          const [index, remove, insert] = diffComposedText(nodeValue, text);
          if (remove !== 0) {
            // @ts-expect-error
            firstChild.deleteData(index, remove);
          }
          // @ts-expect-error
          firstChild.insertData(index, insert);
        } else {
          firstChild.nodeValue = text;
        }
      }
    }
  }
  function createTextInnerDOM(innerDOM, node, innerTag, format, text, config) {
    setTextContent(text, innerDOM, node);
    const theme = config.theme;
    // Apply theme class names
    const textClassNames = theme.text;
    if (textClassNames !== undefined) {
      setTextThemeClassNames(innerTag, 0, format, innerDOM, textClassNames);
    }
  }
  function wrapElementWith(element, tag) {
    const el = document.createElement(tag);
    el.appendChild(element);
    return el;
  }

  // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging

  /** @noInheritDoc */
  // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
  class TextNode extends LexicalNode {
    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    static getType() {
      return 'text';
    }
    static clone(node) {
      return new TextNode(node.__text, node.__key);
    }
    afterCloneFrom(prevNode) {
      super.afterCloneFrom(prevNode);
      this.__format = prevNode.__format;
      this.__style = prevNode.__style;
      this.__mode = prevNode.__mode;
      this.__detail = prevNode.__detail;
    }
    constructor(text, key) {
      super(key);
      this.__text = text;
      this.__format = 0;
      this.__style = '';
      this.__mode = 0;
      this.__detail = 0;
    }

    /**
     * Returns a 32-bit integer that represents the TextFormatTypes currently applied to the
     * TextNode. You probably don't want to use this method directly - consider using TextNode.hasFormat instead.
     *
     * @returns a number representing the format of the text node.
     */
    getFormat() {
      const self = this.getLatest();
      return self.__format;
    }

    /**
     * Returns a 32-bit integer that represents the TextDetailTypes currently applied to the
     * TextNode. You probably don't want to use this method directly - consider using TextNode.isDirectionless
     * or TextNode.isUnmergeable instead.
     *
     * @returns a number representing the detail of the text node.
     */
    getDetail() {
      const self = this.getLatest();
      return self.__detail;
    }

    /**
     * Returns the mode (TextModeType) of the TextNode, which may be "normal", "token", or "segmented"
     *
     * @returns TextModeType.
     */
    getMode() {
      const self = this.getLatest();
      return TEXT_TYPE_TO_MODE[self.__mode];
    }

    /**
     * Returns the styles currently applied to the node. This is analogous to CSSText in the DOM.
     *
     * @returns CSSText-like string of styles applied to the underlying DOM node.
     */
    getStyle() {
      const self = this.getLatest();
      return self.__style;
    }

    /**
     * Returns whether or not the node is in "token" mode. TextNodes in token mode can be navigated through character-by-character
     * with a RangeSelection, but are deleted as a single entity (not invdividually by character).
     *
     * @returns true if the node is in token mode, false otherwise.
     */
    isToken() {
      const self = this.getLatest();
      return self.__mode === IS_TOKEN;
    }

    /**
     *
     * @returns true if Lexical detects that an IME or other 3rd-party script is attempting to
     * mutate the TextNode, false otherwise.
     */
    isComposing() {
      return this.__key === $getCompositionKey();
    }

    /**
     * Returns whether or not the node is in "segemented" mode. TextNodes in segemented mode can be navigated through character-by-character
     * with a RangeSelection, but are deleted in space-delimited "segments".
     *
     * @returns true if the node is in segmented mode, false otherwise.
     */
    isSegmented() {
      const self = this.getLatest();
      return self.__mode === IS_SEGMENTED;
    }
    /**
     * Returns whether or not the node is "directionless". Directionless nodes don't respect changes between RTL and LTR modes.
     *
     * @returns true if the node is directionless, false otherwise.
     */
    isDirectionless() {
      const self = this.getLatest();
      return (self.__detail & IS_DIRECTIONLESS) !== 0;
    }
    /**
     * Returns whether or not the node is unmergeable. In some scenarios, Lexical tries to merge
     * adjacent TextNodes into a single TextNode. If a TextNode is unmergeable, this won't happen.
     *
     * @returns true if the node is unmergeable, false otherwise.
     */
    isUnmergeable() {
      const self = this.getLatest();
      return (self.__detail & IS_UNMERGEABLE) !== 0;
    }

    /**
     * Returns whether or not the node has the provided format applied. Use this with the human-readable TextFormatType
     * string values to get the format of a TextNode.
     *
     * @param type - the TextFormatType to check for.
     *
     * @returns true if the node has the provided format, false otherwise.
     */
    hasFormat(type) {
      const formatFlag = TEXT_TYPE_TO_FORMAT[type];
      return (this.getFormat() & formatFlag) !== 0;
    }

    /**
     * Returns whether or not the node is simple text. Simple text is defined as a TextNode that has the string type "text"
     * (i.e., not a subclass) and has no mode applied to it (i.e., not segmented or token).
     *
     * @returns true if the node is simple text, false otherwise.
     */
    isSimpleText() {
      return this.__type === 'text' && this.__mode === 0;
    }

    /**
     * Returns the text content of the node as a string.
     *
     * @returns a string representing the text content of the node.
     */
    getTextContent() {
      const self = this.getLatest();
      return self.__text;
    }

    /**
     * Returns the format flags applied to the node as a 32-bit integer.
     *
     * @returns a number representing the TextFormatTypes applied to the node.
     */
    getFormatFlags(type, alignWithFormat) {
      const self = this.getLatest();
      const format = self.__format;
      return toggleTextFormatType(format, type, alignWithFormat);
    }

    /**
     *
     * @returns true if the text node supports font styling, false otherwise.
     */
    canHaveFormat() {
      return true;
    }

    // View

    createDOM(config, editor) {
      const format = this.__format;
      const outerTag = getElementOuterTag(this, format);
      const innerTag = getElementInnerTag(this, format);
      const tag = outerTag === null ? innerTag : outerTag;
      const dom = document.createElement(tag);
      let innerDOM = dom;
      if (this.hasFormat('code')) {
        dom.setAttribute('spellcheck', 'false');
      }
      if (outerTag !== null) {
        innerDOM = document.createElement(innerTag);
        dom.appendChild(innerDOM);
      }
      const text = this.__text;
      createTextInnerDOM(innerDOM, this, innerTag, format, text, config);
      const style = this.__style;
      if (style !== '') {
        dom.style.cssText = style;
      }
      return dom;
    }
    updateDOM(prevNode, dom, config) {
      const nextText = this.__text;
      const prevFormat = prevNode.__format;
      const nextFormat = this.__format;
      const prevOuterTag = getElementOuterTag(this, prevFormat);
      const nextOuterTag = getElementOuterTag(this, nextFormat);
      const prevInnerTag = getElementInnerTag(this, prevFormat);
      const nextInnerTag = getElementInnerTag(this, nextFormat);
      const prevTag = prevOuterTag === null ? prevInnerTag : prevOuterTag;
      const nextTag = nextOuterTag === null ? nextInnerTag : nextOuterTag;
      if (prevTag !== nextTag) {
        return true;
      }
      if (prevOuterTag === nextOuterTag && prevInnerTag !== nextInnerTag) {
        // should always be an element
        const prevInnerDOM = dom.firstChild;
        if (prevInnerDOM == null) {
          {
            throw Error(`updateDOM: prevInnerDOM is null or undefined`);
          }
        }
        const nextInnerDOM = document.createElement(nextInnerTag);
        createTextInnerDOM(nextInnerDOM, this, nextInnerTag, nextFormat, nextText, config);
        dom.replaceChild(nextInnerDOM, prevInnerDOM);
        return false;
      }
      let innerDOM = dom;
      if (nextOuterTag !== null) {
        if (prevOuterTag !== null) {
          innerDOM = dom.firstChild;
          if (innerDOM == null) {
            {
              throw Error(`updateDOM: innerDOM is null or undefined`);
            }
          }
        }
      }
      setTextContent(nextText, innerDOM, this);
      const theme = config.theme;
      // Apply theme class names
      const textClassNames = theme.text;
      if (textClassNames !== undefined && prevFormat !== nextFormat) {
        setTextThemeClassNames(nextInnerTag, prevFormat, nextFormat, innerDOM, textClassNames);
      }
      const prevStyle = prevNode.__style;
      const nextStyle = this.__style;
      if (prevStyle !== nextStyle) {
        dom.style.cssText = nextStyle;
      }
      return false;
    }
    static importDOM() {
      return {
        '#text': () => ({
          conversion: $convertTextDOMNode,
          priority: 0
        }),
        b: () => ({
          conversion: convertBringAttentionToElement,
          priority: 0
        }),
        code: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        em: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        i: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        s: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        span: () => ({
          conversion: convertSpanElement,
          priority: 0
        }),
        strong: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        sub: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        sup: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        }),
        u: () => ({
          conversion: convertTextFormatElement,
          priority: 0
        })
      };
    }
    static importJSON(serializedNode) {
      const node = $createTextNode(serializedNode.text);
      node.setFormat(serializedNode.format);
      node.setDetail(serializedNode.detail);
      node.setMode(serializedNode.mode);
      node.setStyle(serializedNode.style);
      return node;
    }

    // This improves Lexical's basic text output in copy+paste plus
    // for headless mode where people might use Lexical to generate
    // HTML content and not have the ability to use CSS classes.
    exportDOM(editor) {
      let {
        element
      } = super.exportDOM(editor);
      if (!(element !== null && isHTMLElement(element))) {
        throw Error(`Expected TextNode createDOM to always return a HTMLElement`);
      }
      element.style.whiteSpace = 'pre-wrap';
      // This is the only way to properly add support for most clients,
      // even if it's semantically incorrect to have to resort to using
      // <b>, <u>, <s>, <i> elements.
      if (this.hasFormat('bold')) {
        element = wrapElementWith(element, 'b');
      }
      if (this.hasFormat('italic')) {
        element = wrapElementWith(element, 'i');
      }
      if (this.hasFormat('strikethrough')) {
        element = wrapElementWith(element, 's');
      }
      if (this.hasFormat('underline')) {
        element = wrapElementWith(element, 'u');
      }
      return {
        element
      };
    }
    exportJSON() {
      return {
        detail: this.getDetail(),
        format: this.getFormat(),
        mode: this.getMode(),
        style: this.getStyle(),
        text: this.getTextContent(),
        type: 'text',
        version: 1
      };
    }

    // Mutators
    selectionTransform(prevSelection, nextSelection) {
      return;
    }

    /**
     * Sets the node format to the provided TextFormatType or 32-bit integer. Note that the TextFormatType
     * version of the argument can only specify one format and doing so will remove all other formats that
     * may be applied to the node. For toggling behavior, consider using {@link TextNode.toggleFormat}
     *
     * @param format - TextFormatType or 32-bit integer representing the node format.
     *
     * @returns this TextNode.
     * // TODO 0.12 This should just be a `string`.
     */
    setFormat(format) {
      const self = this.getWritable();
      self.__format = typeof format === 'string' ? TEXT_TYPE_TO_FORMAT[format] : format;
      return self;
    }

    /**
     * Sets the node detail to the provided TextDetailType or 32-bit integer. Note that the TextDetailType
     * version of the argument can only specify one detail value and doing so will remove all other detail values that
     * may be applied to the node. For toggling behavior, consider using {@link TextNode.toggleDirectionless}
     * or {@link TextNode.toggleUnmergeable}
     *
     * @param detail - TextDetailType or 32-bit integer representing the node detail.
     *
     * @returns this TextNode.
     * // TODO 0.12 This should just be a `string`.
     */
    setDetail(detail) {
      const self = this.getWritable();
      self.__detail = typeof detail === 'string' ? DETAIL_TYPE_TO_DETAIL[detail] : detail;
      return self;
    }

    /**
     * Sets the node style to the provided CSSText-like string. Set this property as you
     * would an HTMLElement style attribute to apply inline styles to the underlying DOM Element.
     *
     * @param style - CSSText to be applied to the underlying HTMLElement.
     *
     * @returns this TextNode.
     */
    setStyle(style) {
      const self = this.getWritable();
      self.__style = style;
      return self;
    }

    /**
     * Applies the provided format to this TextNode if it's not present. Removes it if it's present.
     * The subscript and superscript formats are mutually exclusive.
     * Prefer using this method to turn specific formats on and off.
     *
     * @param type - TextFormatType to toggle.
     *
     * @returns this TextNode.
     */
    toggleFormat(type) {
      const format = this.getFormat();
      const newFormat = toggleTextFormatType(format, type, null);
      return this.setFormat(newFormat);
    }

    /**
     * Toggles the directionless detail value of the node. Prefer using this method over setDetail.
     *
     * @returns this TextNode.
     */
    toggleDirectionless() {
      const self = this.getWritable();
      self.__detail ^= IS_DIRECTIONLESS;
      return self;
    }

    /**
     * Toggles the unmergeable detail value of the node. Prefer using this method over setDetail.
     *
     * @returns this TextNode.
     */
    toggleUnmergeable() {
      const self = this.getWritable();
      self.__detail ^= IS_UNMERGEABLE;
      return self;
    }

    /**
     * Sets the mode of the node.
     *
     * @returns this TextNode.
     */
    setMode(type) {
      const mode = TEXT_MODE_TO_TYPE[type];
      if (this.__mode === mode) {
        return this;
      }
      const self = this.getWritable();
      self.__mode = mode;
      return self;
    }

    /**
     * Sets the text content of the node.
     *
     * @param text - the string to set as the text value of the node.
     *
     * @returns this TextNode.
     */
    setTextContent(text) {
      if (this.__text === text) {
        return this;
      }
      const self = this.getWritable();
      self.__text = text;
      return self;
    }

    /**
     * Sets the current Lexical selection to be a RangeSelection with anchor and focus on this TextNode at the provided offsets.
     *
     * @param _anchorOffset - the offset at which the Selection anchor will be placed.
     * @param _focusOffset - the offset at which the Selection focus will be placed.
     *
     * @returns the new RangeSelection.
     */
    select(_anchorOffset, _focusOffset) {
      errorOnReadOnly();
      let anchorOffset = _anchorOffset;
      let focusOffset = _focusOffset;
      const selection = $getSelection();
      const text = this.getTextContent();
      const key = this.__key;
      if (typeof text === 'string') {
        const lastOffset = text.length;
        if (anchorOffset === undefined) {
          anchorOffset = lastOffset;
        }
        if (focusOffset === undefined) {
          focusOffset = lastOffset;
        }
      } else {
        anchorOffset = 0;
        focusOffset = 0;
      }
      if (!$isRangeSelection(selection)) {
        return $internalMakeRangeSelection(key, anchorOffset, key, focusOffset, 'text', 'text');
      } else {
        const compositionKey = $getCompositionKey();
        if (compositionKey === selection.anchor.key || compositionKey === selection.focus.key) {
          $setCompositionKey(key);
        }
        selection.setTextNodeRange(this, anchorOffset, this, focusOffset);
      }
      return selection;
    }
    selectStart() {
      return this.select(0, 0);
    }
    selectEnd() {
      const size = this.getTextContentSize();
      return this.select(size, size);
    }

    /**
     * Inserts the provided text into this TextNode at the provided offset, deleting the number of characters
     * specified. Can optionally calculate a new selection after the operation is complete.
     *
     * @param offset - the offset at which the splice operation should begin.
     * @param delCount - the number of characters to delete, starting from the offset.
     * @param newText - the text to insert into the TextNode at the offset.
     * @param moveSelection - optional, whether or not to move selection to the end of the inserted substring.
     *
     * @returns this TextNode.
     */
    spliceText(offset, delCount, newText, moveSelection) {
      const writableSelf = this.getWritable();
      const text = writableSelf.__text;
      const handledTextLength = newText.length;
      let index = offset;
      if (index < 0) {
        index = handledTextLength + index;
        if (index < 0) {
          index = 0;
        }
      }
      const selection = $getSelection();
      if (moveSelection && $isRangeSelection(selection)) {
        const newOffset = offset + handledTextLength;
        selection.setTextNodeRange(writableSelf, newOffset, writableSelf, newOffset);
      }
      const updatedText = text.slice(0, index) + newText + text.slice(index + delCount);
      writableSelf.__text = updatedText;
      return writableSelf;
    }

    /**
     * This method is meant to be overriden by TextNode subclasses to control the behavior of those nodes
     * when a user event would cause text to be inserted before them in the editor. If true, Lexical will attempt
     * to insert text into this node. If false, it will insert the text in a new sibling node.
     *
     * @returns true if text can be inserted before the node, false otherwise.
     */
    canInsertTextBefore() {
      return true;
    }

    /**
     * This method is meant to be overriden by TextNode subclasses to control the behavior of those nodes
     * when a user event would cause text to be inserted after them in the editor. If true, Lexical will attempt
     * to insert text into this node. If false, it will insert the text in a new sibling node.
     *
     * @returns true if text can be inserted after the node, false otherwise.
     */
    canInsertTextAfter() {
      return true;
    }

    /**
     * Splits this TextNode at the provided character offsets, forming new TextNodes from the substrings
     * formed by the split, and inserting those new TextNodes into the editor, replacing the one that was split.
     *
     * @param splitOffsets - rest param of the text content character offsets at which this node should be split.
     *
     * @returns an Array containing the newly-created TextNodes.
     */
    splitText(...splitOffsets) {
      errorOnReadOnly();
      const self = this.getLatest();
      const textContent = self.getTextContent();
      const key = self.__key;
      const compositionKey = $getCompositionKey();
      const offsetsSet = new Set(splitOffsets);
      const parts = [];
      const textLength = textContent.length;
      let string = '';
      for (let i = 0; i < textLength; i++) {
        if (string !== '' && offsetsSet.has(i)) {
          parts.push(string);
          string = '';
        }
        string += textContent[i];
      }
      if (string !== '') {
        parts.push(string);
      }
      const partsLength = parts.length;
      if (partsLength === 0) {
        return [];
      } else if (parts[0] === textContent) {
        return [self];
      }
      const firstPart = parts[0];
      const parent = self.getParent();
      let writableNode;
      const format = self.getFormat();
      const style = self.getStyle();
      const detail = self.__detail;
      let hasReplacedSelf = false;
      if (self.isSegmented()) {
        // Create a new TextNode
        writableNode = $createTextNode(firstPart);
        writableNode.__format = format;
        writableNode.__style = style;
        writableNode.__detail = detail;
        hasReplacedSelf = true;
      } else {
        // For the first part, update the existing node
        writableNode = self.getWritable();
        writableNode.__text = firstPart;
      }

      // Handle selection
      const selection = $getSelection();

      // Then handle all other parts
      const splitNodes = [writableNode];
      let textSize = firstPart.length;
      for (let i = 1; i < partsLength; i++) {
        const part = parts[i];
        const partSize = part.length;
        const sibling = $createTextNode(part).getWritable();
        sibling.__format = format;
        sibling.__style = style;
        sibling.__detail = detail;
        const siblingKey = sibling.__key;
        const nextTextSize = textSize + partSize;
        if ($isRangeSelection(selection)) {
          const anchor = selection.anchor;
          const focus = selection.focus;
          if (anchor.key === key && anchor.type === 'text' && anchor.offset > textSize && anchor.offset <= nextTextSize) {
            anchor.key = siblingKey;
            anchor.offset -= textSize;
            selection.dirty = true;
          }
          if (focus.key === key && focus.type === 'text' && focus.offset > textSize && focus.offset <= nextTextSize) {
            focus.key = siblingKey;
            focus.offset -= textSize;
            selection.dirty = true;
          }
        }
        if (compositionKey === key) {
          $setCompositionKey(siblingKey);
        }
        textSize = nextTextSize;
        splitNodes.push(sibling);
      }

      // Insert the nodes into the parent's children
      if (parent !== null) {
        internalMarkSiblingsAsDirty(this);
        const writableParent = parent.getWritable();
        const insertionIndex = this.getIndexWithinParent();
        if (hasReplacedSelf) {
          writableParent.splice(insertionIndex, 0, splitNodes);
          this.remove();
        } else {
          writableParent.splice(insertionIndex, 1, splitNodes);
        }
        if ($isRangeSelection(selection)) {
          $updateElementSelectionOnCreateDeleteNode(selection, parent, insertionIndex, partsLength - 1);
        }
      }
      return splitNodes;
    }

    /**
     * Merges the target TextNode into this TextNode, removing the target node.
     *
     * @param target - the TextNode to merge into this one.
     *
     * @returns this TextNode.
     */
    mergeWithSibling(target) {
      const isBefore = target === this.getPreviousSibling();
      if (!isBefore && target !== this.getNextSibling()) {
        {
          throw Error(`mergeWithSibling: sibling must be a previous or next sibling`);
        }
      }
      const key = this.__key;
      const targetKey = target.__key;
      const text = this.__text;
      const textLength = text.length;
      const compositionKey = $getCompositionKey();
      if (compositionKey === targetKey) {
        $setCompositionKey(key);
      }
      const selection = $getSelection();
      if ($isRangeSelection(selection)) {
        const anchor = selection.anchor;
        const focus = selection.focus;
        if (anchor !== null && anchor.key === targetKey) {
          adjustPointOffsetForMergedSibling(anchor, isBefore, key, target, textLength);
          selection.dirty = true;
        }
        if (focus !== null && focus.key === targetKey) {
          adjustPointOffsetForMergedSibling(focus, isBefore, key, target, textLength);
          selection.dirty = true;
        }
      }
      const targetText = target.__text;
      const newText = isBefore ? targetText + text : text + targetText;
      this.setTextContent(newText);
      const writableSelf = this.getWritable();
      target.remove();
      return writableSelf;
    }

    /**
     * This method is meant to be overriden by TextNode subclasses to control the behavior of those nodes
     * when used with the registerLexicalTextEntity function. If you're using registerLexicalTextEntity, the
     * node class that you create and replace matched text with should return true from this method.
     *
     * @returns true if the node is to be treated as a "text entity", false otherwise.
     */
    isTextEntity() {
      return false;
    }
  }
  function convertSpanElement(domNode) {
    // domNode is a <span> since we matched it by nodeName
    const span = domNode;
    const style = span.style;
    return {
      forChild: applyTextFormatFromStyle(style),
      node: null
    };
  }
  function convertBringAttentionToElement(domNode) {
    // domNode is a <b> since we matched it by nodeName
    const b = domNode;
    // Google Docs wraps all copied HTML in a <b> with font-weight normal
    const hasNormalFontWeight = b.style.fontWeight === 'normal';
    return {
      forChild: applyTextFormatFromStyle(b.style, hasNormalFontWeight ? undefined : 'bold'),
      node: null
    };
  }
  const preParentCache = new WeakMap();
  function isNodePre(node) {
    return node.nodeName === 'PRE' || node.nodeType === DOM_ELEMENT_TYPE && node.style !== undefined && node.style.whiteSpace !== undefined && node.style.whiteSpace.startsWith('pre');
  }
  function findParentPreDOMNode(node) {
    let cached;
    let parent = node.parentNode;
    const visited = [node];
    while (parent !== null && (cached = preParentCache.get(parent)) === undefined && !isNodePre(parent)) {
      visited.push(parent);
      parent = parent.parentNode;
    }
    const resultNode = cached === undefined ? parent : cached;
    for (let i = 0; i < visited.length; i++) {
      preParentCache.set(visited[i], resultNode);
    }
    return resultNode;
  }
  function $convertTextDOMNode(domNode) {
    const domNode_ = domNode;
    const parentDom = domNode.parentElement;
    if (!(parentDom !== null)) {
      throw Error(`Expected parentElement of Text not to be null`);
    }
    let textContent = domNode_.textContent || '';
    // No collapse and preserve segment break for pre, pre-wrap and pre-line
    if (findParentPreDOMNode(domNode_) !== null) {
      const parts = textContent.split(/(\r?\n|\t)/);
      const nodes = [];
      const length = parts.length;
      for (let i = 0; i < length; i++) {
        const part = parts[i];
        if (part === '\n' || part === '\r\n') {
          nodes.push($createLineBreakNode());
        } else if (part === '\t') {
          nodes.push($createTabNode());
        } else if (part !== '') {
          nodes.push($createTextNode(part));
        }
      }
      return {
        node: nodes
      };
    }
    textContent = textContent.replace(/\r/g, '').replace(/[ \t\n]+/g, ' ');
    if (textContent === '') {
      return {
        node: null
      };
    }
    if (textContent[0] === ' ') {
      // Traverse backward while in the same line. If content contains new line or tab -> pontential
      // delete, other elements can borrow from this one. Deletion depends on whether it's also the
      // last space (see next condition: textContent[textContent.length - 1] === ' '))
      let previousText = domNode_;
      let isStartOfLine = true;
      while (previousText !== null && (previousText = findTextInLine(previousText, false)) !== null) {
        const previousTextContent = previousText.textContent || '';
        if (previousTextContent.length > 0) {
          if (/[ \t\n]$/.test(previousTextContent)) {
            textContent = textContent.slice(1);
          }
          isStartOfLine = false;
          break;
        }
      }
      if (isStartOfLine) {
        textContent = textContent.slice(1);
      }
    }
    if (textContent[textContent.length - 1] === ' ') {
      // Traverse forward while in the same line, preserve if next inline will require a space
      let nextText = domNode_;
      let isEndOfLine = true;
      while (nextText !== null && (nextText = findTextInLine(nextText, true)) !== null) {
        const nextTextContent = (nextText.textContent || '').replace(/^( |\t|\r?\n)+/, '');
        if (nextTextContent.length > 0) {
          isEndOfLine = false;
          break;
        }
      }
      if (isEndOfLine) {
        textContent = textContent.slice(0, textContent.length - 1);
      }
    }
    if (textContent === '') {
      return {
        node: null
      };
    }
    return {
      node: $createTextNode(textContent)
    };
  }
  function findTextInLine(text, forward) {
    let node = text;
    // eslint-disable-next-line no-constant-condition
    while (true) {
      let sibling;
      while ((sibling = forward ? node.nextSibling : node.previousSibling) === null) {
        const parentElement = node.parentElement;
        if (parentElement === null) {
          return null;
        }
        node = parentElement;
      }
      node = sibling;
      if (node.nodeType === DOM_ELEMENT_TYPE) {
        const display = node.style.display;
        if (display === '' && !isInlineDomNode(node) || display !== '' && !display.startsWith('inline')) {
          return null;
        }
      }
      let descendant = node;
      while ((descendant = forward ? node.firstChild : node.lastChild) !== null) {
        node = descendant;
      }
      if (node.nodeType === DOM_TEXT_TYPE) {
        return node;
      } else if (node.nodeName === 'BR') {
        return null;
      }
    }
  }
  const nodeNameToTextFormat = {
    code: 'code',
    em: 'italic',
    i: 'italic',
    s: 'strikethrough',
    strong: 'bold',
    sub: 'subscript',
    sup: 'superscript',
    u: 'underline'
  };
  function convertTextFormatElement(domNode) {
    const format = nodeNameToTextFormat[domNode.nodeName.toLowerCase()];
    if (format === undefined) {
      return {
        node: null
      };
    }
    return {
      forChild: applyTextFormatFromStyle(domNode.style, format),
      node: null
    };
  }
  function $createTextNode(text = '') {
    return $applyNodeReplacement(new TextNode(text));
  }
  function $isTextNode(node) {
    return node instanceof TextNode;
  }
  function applyTextFormatFromStyle(style, shouldApply) {
    const fontWeight = style.fontWeight;
    const textDecoration = style.textDecoration.split(' ');
    // Google Docs uses span tags + font-weight for bold text
    const hasBoldFontWeight = fontWeight === '700' || fontWeight === 'bold';
    // Google Docs uses span tags + text-decoration: line-through for strikethrough text
    const hasLinethroughTextDecoration = textDecoration.includes('line-through');
    // Google Docs uses span tags + font-style for italic text
    const hasItalicFontStyle = style.fontStyle === 'italic';
    // Google Docs uses span tags + text-decoration: underline for underline text
    const hasUnderlineTextDecoration = textDecoration.includes('underline');
    // Google Docs uses span tags + vertical-align to specify subscript and superscript
    const verticalAlign = style.verticalAlign;
    return lexicalNode => {
      if (!$isTextNode(lexicalNode)) {
        return lexicalNode;
      }
      if (hasBoldFontWeight && !lexicalNode.hasFormat('bold')) {
        lexicalNode.toggleFormat('bold');
      }
      if (hasLinethroughTextDecoration && !lexicalNode.hasFormat('strikethrough')) {
        lexicalNode.toggleFormat('strikethrough');
      }
      if (hasItalicFontStyle && !lexicalNode.hasFormat('italic')) {
        lexicalNode.toggleFormat('italic');
      }
      if (hasUnderlineTextDecoration && !lexicalNode.hasFormat('underline')) {
        lexicalNode.toggleFormat('underline');
      }
      if (verticalAlign === 'sub' && !lexicalNode.hasFormat('subscript')) {
        lexicalNode.toggleFormat('subscript');
      }
      if (verticalAlign === 'super' && !lexicalNode.hasFormat('superscript')) {
        lexicalNode.toggleFormat('superscript');
      }
      if (shouldApply && !lexicalNode.hasFormat(shouldApply)) {
        lexicalNode.toggleFormat(shouldApply);
      }
      return lexicalNode;
    };
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class TabNode extends TextNode {
    static getType() {
      return 'tab';
    }
    static clone(node) {
      return new TabNode(node.__key);
    }
    afterCloneFrom(prevNode) {
      super.afterCloneFrom(prevNode);
      // TabNode __text can be either '\t' or ''. insertText will remove the empty Node
      this.__text = prevNode.__text;
    }
    constructor(key) {
      super('\t', key);
      this.__detail = IS_UNMERGEABLE;
    }
    static importDOM() {
      return null;
    }
    static importJSON(serializedTabNode) {
      const node = $createTabNode();
      node.setFormat(serializedTabNode.format);
      node.setStyle(serializedTabNode.style);
      return node;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        type: 'tab',
        version: 1
      };
    }
    setTextContent(_text) {
      {
        throw Error(`TabNode does not support setTextContent`);
      }
    }
    setDetail(_detail) {
      {
        throw Error(`TabNode does not support setDetail`);
      }
    }
    setMode(_type) {
      {
        throw Error(`TabNode does not support setMode`);
      }
    }
    canInsertTextBefore() {
      return false;
    }
    canInsertTextAfter() {
      return false;
    }
  }
  function $createTabNode() {
    return $applyNodeReplacement(new TabNode());
  }
  function $isTabNode(node) {
    return node instanceof TabNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  class Point {
    constructor(key, offset, type) {
      this._selection = null;
      this.key = key;
      this.offset = offset;
      this.type = type;
    }
    is(point) {
      return this.key === point.key && this.offset === point.offset && this.type === point.type;
    }
    isBefore(b) {
      let aNode = this.getNode();
      let bNode = b.getNode();
      const aOffset = this.offset;
      const bOffset = b.offset;
      if ($isElementNode(aNode)) {
        const aNodeDescendant = aNode.getDescendantByIndex(aOffset);
        aNode = aNodeDescendant != null ? aNodeDescendant : aNode;
      }
      if ($isElementNode(bNode)) {
        const bNodeDescendant = bNode.getDescendantByIndex(bOffset);
        bNode = bNodeDescendant != null ? bNodeDescendant : bNode;
      }
      if (aNode === bNode) {
        return aOffset < bOffset;
      }
      return aNode.isBefore(bNode);
    }
    getNode() {
      const key = this.key;
      const node = $getNodeByKey(key);
      if (node === null) {
        {
          throw Error(`Point.getNode: node not found`);
        }
      }
      return node;
    }
    set(key, offset, type) {
      const selection = this._selection;
      const oldKey = this.key;
      this.key = key;
      this.offset = offset;
      this.type = type;
      if (!isCurrentlyReadOnlyMode()) {
        if ($getCompositionKey() === oldKey) {
          $setCompositionKey(key);
        }
        if (selection !== null) {
          selection.setCachedNodes(null);
          selection.dirty = true;
        }
      }
    }
  }
  function $createPoint(key, offset, type) {
    // @ts-expect-error: intentionally cast as we use a class for perf reasons
    return new Point(key, offset, type);
  }
  function selectPointOnNode(point, node) {
    let key = node.__key;
    let offset = point.offset;
    let type = 'element';
    if ($isTextNode(node)) {
      type = 'text';
      const textContentLength = node.getTextContentSize();
      if (offset > textContentLength) {
        offset = textContentLength;
      }
    } else if (!$isElementNode(node)) {
      const nextSibling = node.getNextSibling();
      if ($isTextNode(nextSibling)) {
        key = nextSibling.__key;
        offset = 0;
        type = 'text';
      } else {
        const parentNode = node.getParent();
        if (parentNode) {
          key = parentNode.__key;
          offset = node.getIndexWithinParent() + 1;
        }
      }
    }
    point.set(key, offset, type);
  }
  function $moveSelectionPointToEnd(point, node) {
    if ($isElementNode(node)) {
      const lastNode = node.getLastDescendant();
      if ($isElementNode(lastNode) || $isTextNode(lastNode)) {
        selectPointOnNode(point, lastNode);
      } else {
        selectPointOnNode(point, node);
      }
    } else {
      selectPointOnNode(point, node);
    }
  }
  function $transferStartingElementPointToTextPoint(start, end, format, style) {
    const element = start.getNode();
    const placementNode = element.getChildAtIndex(start.offset);
    const textNode = $createTextNode();
    const target = $isRootNode(element) ? $createParagraphNode().append(textNode) : textNode;
    textNode.setFormat(format);
    textNode.setStyle(style);
    if (placementNode === null) {
      element.append(target);
    } else {
      placementNode.insertBefore(target);
    }
    // Transfer the element point to a text point.
    if (start.is(end)) {
      end.set(textNode.__key, 0, 'text');
    }
    start.set(textNode.__key, 0, 'text');
  }
  function $setPointValues(point, key, offset, type) {
    point.key = key;
    point.offset = offset;
    point.type = type;
  }
  class NodeSelection {
    constructor(objects) {
      this._cachedNodes = null;
      this._nodes = objects;
      this.dirty = false;
    }
    getCachedNodes() {
      return this._cachedNodes;
    }
    setCachedNodes(nodes) {
      this._cachedNodes = nodes;
    }
    is(selection) {
      if (!$isNodeSelection(selection)) {
        return false;
      }
      const a = this._nodes;
      const b = selection._nodes;
      return a.size === b.size && Array.from(a).every(key => b.has(key));
    }
    isCollapsed() {
      return false;
    }
    isBackward() {
      return false;
    }
    getStartEndPoints() {
      return null;
    }
    add(key) {
      this.dirty = true;
      this._nodes.add(key);
      this._cachedNodes = null;
    }
    delete(key) {
      this.dirty = true;
      this._nodes.delete(key);
      this._cachedNodes = null;
    }
    clear() {
      this.dirty = true;
      this._nodes.clear();
      this._cachedNodes = null;
    }
    has(key) {
      return this._nodes.has(key);
    }
    clone() {
      return new NodeSelection(new Set(this._nodes));
    }
    extract() {
      return this.getNodes();
    }
    insertRawText(text) {
      // Do nothing?
    }
    insertText() {
      // Do nothing?
    }
    insertNodes(nodes) {
      const selectedNodes = this.getNodes();
      const selectedNodesLength = selectedNodes.length;
      const lastSelectedNode = selectedNodes[selectedNodesLength - 1];
      let selectionAtEnd;
      // Insert nodes
      if ($isTextNode(lastSelectedNode)) {
        selectionAtEnd = lastSelectedNode.select();
      } else {
        const index = lastSelectedNode.getIndexWithinParent() + 1;
        selectionAtEnd = lastSelectedNode.getParentOrThrow().select(index, index);
      }
      selectionAtEnd.insertNodes(nodes);
      // Remove selected nodes
      for (let i = 0; i < selectedNodesLength; i++) {
        selectedNodes[i].remove();
      }
    }
    getNodes() {
      const cachedNodes = this._cachedNodes;
      if (cachedNodes !== null) {
        return cachedNodes;
      }
      const objects = this._nodes;
      const nodes = [];
      for (const object of objects) {
        const node = $getNodeByKey(object);
        if (node !== null) {
          nodes.push(node);
        }
      }
      if (!isCurrentlyReadOnlyMode()) {
        this._cachedNodes = nodes;
      }
      return nodes;
    }
    getTextContent() {
      const nodes = this.getNodes();
      let textContent = '';
      for (let i = 0; i < nodes.length; i++) {
        textContent += nodes[i].getTextContent();
      }
      return textContent;
    }
  }
  function $isRangeSelection(x) {
    return x instanceof RangeSelection;
  }
  class RangeSelection {
    constructor(anchor, focus, format, style) {
      this.anchor = anchor;
      this.focus = focus;
      anchor._selection = this;
      focus._selection = this;
      this._cachedNodes = null;
      this.format = format;
      this.style = style;
      this.dirty = false;
    }
    getCachedNodes() {
      return this._cachedNodes;
    }
    setCachedNodes(nodes) {
      this._cachedNodes = nodes;
    }

    /**
     * Used to check if the provided selections is equal to this one by value,
     * inluding anchor, focus, format, and style properties.
     * @param selection - the Selection to compare this one to.
     * @returns true if the Selections are equal, false otherwise.
     */
    is(selection) {
      if (!$isRangeSelection(selection)) {
        return false;
      }
      return this.anchor.is(selection.anchor) && this.focus.is(selection.focus) && this.format === selection.format && this.style === selection.style;
    }

    /**
     * Returns whether the Selection is "collapsed", meaning the anchor and focus are
     * the same node and have the same offset.
     *
     * @returns true if the Selection is collapsed, false otherwise.
     */
    isCollapsed() {
      return this.anchor.is(this.focus);
    }

    /**
     * Gets all the nodes in the Selection. Uses caching to make it generally suitable
     * for use in hot paths.
     *
     * @returns an Array containing all the nodes in the Selection
     */
    getNodes() {
      const cachedNodes = this._cachedNodes;
      if (cachedNodes !== null) {
        return cachedNodes;
      }
      const anchor = this.anchor;
      const focus = this.focus;
      const isBefore = anchor.isBefore(focus);
      const firstPoint = isBefore ? anchor : focus;
      const lastPoint = isBefore ? focus : anchor;
      let firstNode = firstPoint.getNode();
      let lastNode = lastPoint.getNode();
      const startOffset = firstPoint.offset;
      const endOffset = lastPoint.offset;
      if ($isElementNode(firstNode)) {
        const firstNodeDescendant = firstNode.getDescendantByIndex(startOffset);
        firstNode = firstNodeDescendant != null ? firstNodeDescendant : firstNode;
      }
      if ($isElementNode(lastNode)) {
        let lastNodeDescendant = lastNode.getDescendantByIndex(endOffset);
        // We don't want to over-select, as node selection infers the child before
        // the last descendant, not including that descendant.
        if (lastNodeDescendant !== null && lastNodeDescendant !== firstNode && lastNode.getChildAtIndex(endOffset) === lastNodeDescendant) {
          lastNodeDescendant = lastNodeDescendant.getPreviousSibling();
        }
        lastNode = lastNodeDescendant != null ? lastNodeDescendant : lastNode;
      }
      let nodes;
      if (firstNode.is(lastNode)) {
        if ($isElementNode(firstNode) && firstNode.getChildrenSize() > 0) {
          nodes = [];
        } else {
          nodes = [firstNode];
        }
      } else {
        nodes = firstNode.getNodesBetween(lastNode);
      }
      if (!isCurrentlyReadOnlyMode()) {
        this._cachedNodes = nodes;
      }
      return nodes;
    }

    /**
     * Sets this Selection to be of type "text" at the provided anchor and focus values.
     *
     * @param anchorNode - the anchor node to set on the Selection
     * @param anchorOffset - the offset to set on the Selection
     * @param focusNode - the focus node to set on the Selection
     * @param focusOffset - the focus offset to set on the Selection
     */
    setTextNodeRange(anchorNode, anchorOffset, focusNode, focusOffset) {
      $setPointValues(this.anchor, anchorNode.__key, anchorOffset, 'text');
      $setPointValues(this.focus, focusNode.__key, focusOffset, 'text');
      this._cachedNodes = null;
      this.dirty = true;
    }

    /**
     * Gets the (plain) text content of all the nodes in the selection.
     *
     * @returns a string representing the text content of all the nodes in the Selection
     */
    getTextContent() {
      const nodes = this.getNodes();
      if (nodes.length === 0) {
        return '';
      }
      const firstNode = nodes[0];
      const lastNode = nodes[nodes.length - 1];
      const anchor = this.anchor;
      const focus = this.focus;
      const isBefore = anchor.isBefore(focus);
      const [anchorOffset, focusOffset] = $getCharacterOffsets(this);
      let textContent = '';
      let prevWasElement = true;
      for (let i = 0; i < nodes.length; i++) {
        const node = nodes[i];
        if ($isElementNode(node) && !node.isInline()) {
          if (!prevWasElement) {
            textContent += '\n';
          }
          if (node.isEmpty()) {
            prevWasElement = false;
          } else {
            prevWasElement = true;
          }
        } else {
          prevWasElement = false;
          if ($isTextNode(node)) {
            let text = node.getTextContent();
            if (node === firstNode) {
              if (node === lastNode) {
                if (anchor.type !== 'element' || focus.type !== 'element' || focus.offset === anchor.offset) {
                  text = anchorOffset < focusOffset ? text.slice(anchorOffset, focusOffset) : text.slice(focusOffset, anchorOffset);
                }
              } else {
                text = isBefore ? text.slice(anchorOffset) : text.slice(focusOffset);
              }
            } else if (node === lastNode) {
              text = isBefore ? text.slice(0, focusOffset) : text.slice(0, anchorOffset);
            }
            textContent += text;
          } else if (($isDecoratorNode(node) || $isLineBreakNode(node)) && (node !== lastNode || !this.isCollapsed())) {
            textContent += node.getTextContent();
          }
        }
      }
      return textContent;
    }

    /**
     * Attempts to map a DOM selection range onto this Lexical Selection,
     * setting the anchor, focus, and type accordingly
     *
     * @param range a DOM Selection range conforming to the StaticRange interface.
     */
    applyDOMRange(range) {
      const editor = getActiveEditor();
      const currentEditorState = editor.getEditorState();
      const lastSelection = currentEditorState._selection;
      const resolvedSelectionPoints = $internalResolveSelectionPoints(range.startContainer, range.startOffset, range.endContainer, range.endOffset, editor, lastSelection);
      if (resolvedSelectionPoints === null) {
        return;
      }
      const [anchorPoint, focusPoint] = resolvedSelectionPoints;
      $setPointValues(this.anchor, anchorPoint.key, anchorPoint.offset, anchorPoint.type);
      $setPointValues(this.focus, focusPoint.key, focusPoint.offset, focusPoint.type);
      this._cachedNodes = null;
    }

    /**
     * Creates a new RangeSelection, copying over all the property values from this one.
     *
     * @returns a new RangeSelection with the same property values as this one.
     */
    clone() {
      const anchor = this.anchor;
      const focus = this.focus;
      const selection = new RangeSelection($createPoint(anchor.key, anchor.offset, anchor.type), $createPoint(focus.key, focus.offset, focus.type), this.format, this.style);
      return selection;
    }

    /**
     * Toggles the provided format on all the TextNodes in the Selection.
     *
     * @param format a string TextFormatType to toggle on the TextNodes in the selection
     */
    toggleFormat(format) {
      this.format = toggleTextFormatType(this.format, format, null);
      this.dirty = true;
    }

    /**
     * Sets the value of the style property on the Selection
     *
     * @param style - the style to set at the value of the style property.
     */
    setStyle(style) {
      this.style = style;
      this.dirty = true;
    }

    /**
     * Returns whether the provided TextFormatType is present on the Selection. This will be true if any node in the Selection
     * has the specified format.
     *
     * @param type the TextFormatType to check for.
     * @returns true if the provided format is currently toggled on on the Selection, false otherwise.
     */
    hasFormat(type) {
      const formatFlag = TEXT_TYPE_TO_FORMAT[type];
      return (this.format & formatFlag) !== 0;
    }

    /**
     * Attempts to insert the provided text into the EditorState at the current Selection.
     * converts tabs, newlines, and carriage returns into LexicalNodes.
     *
     * @param text the text to insert into the Selection
     */
    insertRawText(text) {
      const parts = text.split(/(\r?\n|\t)/);
      const nodes = [];
      const length = parts.length;
      for (let i = 0; i < length; i++) {
        const part = parts[i];
        if (part === '\n' || part === '\r\n') {
          nodes.push($createLineBreakNode());
        } else if (part === '\t') {
          nodes.push($createTabNode());
        } else {
          nodes.push($createTextNode(part));
        }
      }
      this.insertNodes(nodes);
    }

    /**
     * Attempts to insert the provided text into the EditorState at the current Selection as a new
     * Lexical TextNode, according to a series of insertion heuristics based on the selection type and position.
     *
     * @param text the text to insert into the Selection
     */
    insertText(text) {
      const anchor = this.anchor;
      const focus = this.focus;
      const format = this.format;
      const style = this.style;
      let firstPoint = anchor;
      let endPoint = focus;
      if (!this.isCollapsed() && focus.isBefore(anchor)) {
        firstPoint = focus;
        endPoint = anchor;
      }
      if (firstPoint.type === 'element') {
        $transferStartingElementPointToTextPoint(firstPoint, endPoint, format, style);
      }
      const startOffset = firstPoint.offset;
      let endOffset = endPoint.offset;
      const selectedNodes = this.getNodes();
      const selectedNodesLength = selectedNodes.length;
      let firstNode = selectedNodes[0];
      if (!$isTextNode(firstNode)) {
        {
          throw Error(`insertText: first node is not a text node`);
        }
      }
      const firstNodeText = firstNode.getTextContent();
      const firstNodeTextLength = firstNodeText.length;
      const firstNodeParent = firstNode.getParentOrThrow();
      const lastIndex = selectedNodesLength - 1;
      let lastNode = selectedNodes[lastIndex];
      if (selectedNodesLength === 1 && endPoint.type === 'element') {
        endOffset = firstNodeTextLength;
        endPoint.set(firstPoint.key, endOffset, 'text');
      }
      if (this.isCollapsed() && startOffset === firstNodeTextLength && (firstNode.isSegmented() || firstNode.isToken() || !firstNode.canInsertTextAfter() || !firstNodeParent.canInsertTextAfter() && firstNode.getNextSibling() === null)) {
        let nextSibling = firstNode.getNextSibling();
        if (!$isTextNode(nextSibling) || !nextSibling.canInsertTextBefore() || $isTokenOrSegmented(nextSibling)) {
          nextSibling = $createTextNode();
          nextSibling.setFormat(format);
          nextSibling.setStyle(style);
          if (!firstNodeParent.canInsertTextAfter()) {
            firstNodeParent.insertAfter(nextSibling);
          } else {
            firstNode.insertAfter(nextSibling);
          }
        }
        nextSibling.select(0, 0);
        firstNode = nextSibling;
        if (text !== '') {
          this.insertText(text);
          return;
        }
      } else if (this.isCollapsed() && startOffset === 0 && (firstNode.isSegmented() || firstNode.isToken() || !firstNode.canInsertTextBefore() || !firstNodeParent.canInsertTextBefore() && firstNode.getPreviousSibling() === null)) {
        let prevSibling = firstNode.getPreviousSibling();
        if (!$isTextNode(prevSibling) || $isTokenOrSegmented(prevSibling)) {
          prevSibling = $createTextNode();
          prevSibling.setFormat(format);
          if (!firstNodeParent.canInsertTextBefore()) {
            firstNodeParent.insertBefore(prevSibling);
          } else {
            firstNode.insertBefore(prevSibling);
          }
        }
        prevSibling.select();
        firstNode = prevSibling;
        if (text !== '') {
          this.insertText(text);
          return;
        }
      } else if (firstNode.isSegmented() && startOffset !== firstNodeTextLength) {
        const textNode = $createTextNode(firstNode.getTextContent());
        textNode.setFormat(format);
        firstNode.replace(textNode);
        firstNode = textNode;
      } else if (!this.isCollapsed() && text !== '') {
        // When the firstNode or lastNode parents are elements that
        // do not allow text to be inserted before or after, we first
        // clear the content. Then we normalize selection, then insert
        // the new content.
        const lastNodeParent = lastNode.getParent();
        if (!firstNodeParent.canInsertTextBefore() || !firstNodeParent.canInsertTextAfter() || $isElementNode(lastNodeParent) && (!lastNodeParent.canInsertTextBefore() || !lastNodeParent.canInsertTextAfter())) {
          this.insertText('');
          $normalizeSelectionPointsForBoundaries(this.anchor, this.focus, null);
          this.insertText(text);
          return;
        }
      }
      if (selectedNodesLength === 1) {
        if (firstNode.isToken()) {
          const textNode = $createTextNode(text);
          textNode.select();
          firstNode.replace(textNode);
          return;
        }
        const firstNodeFormat = firstNode.getFormat();
        const firstNodeStyle = firstNode.getStyle();
        if (startOffset === endOffset && (firstNodeFormat !== format || firstNodeStyle !== style)) {
          if (firstNode.getTextContent() === '') {
            firstNode.setFormat(format);
            firstNode.setStyle(style);
          } else {
            const textNode = $createTextNode(text);
            textNode.setFormat(format);
            textNode.setStyle(style);
            textNode.select();
            if (startOffset === 0) {
              firstNode.insertBefore(textNode, false);
            } else {
              const [targetNode] = firstNode.splitText(startOffset);
              targetNode.insertAfter(textNode, false);
            }
            // When composing, we need to adjust the anchor offset so that
            // we correctly replace that right range.
            if (textNode.isComposing() && this.anchor.type === 'text') {
              this.anchor.offset -= text.length;
            }
            return;
          }
        } else if ($isTabNode(firstNode)) {
          // We don't need to check for delCount because there is only the entire selected node case
          // that can hit here for content size 1 and with canInsertTextBeforeAfter false
          const textNode = $createTextNode(text);
          textNode.setFormat(format);
          textNode.setStyle(style);
          textNode.select();
          firstNode.replace(textNode);
          return;
        }
        const delCount = endOffset - startOffset;
        firstNode = firstNode.spliceText(startOffset, delCount, text, true);
        if (firstNode.getTextContent() === '') {
          firstNode.remove();
        } else if (this.anchor.type === 'text') {
          if (firstNode.isComposing()) {
            // When composing, we need to adjust the anchor offset so that
            // we correctly replace that right range.
            this.anchor.offset -= text.length;
          } else {
            this.format = firstNodeFormat;
            this.style = firstNodeStyle;
          }
        }
      } else {
        const markedNodeKeysForKeep = new Set([...firstNode.getParentKeys(), ...lastNode.getParentKeys()]);

        // We have to get the parent elements before the next section,
        // as in that section we might mutate the lastNode.
        const firstElement = $isElementNode(firstNode) ? firstNode : firstNode.getParentOrThrow();
        let lastElement = $isElementNode(lastNode) ? lastNode : lastNode.getParentOrThrow();
        let lastElementChild = lastNode;

        // If the last element is inline, we should instead look at getting
        // the nodes of its parent, rather than itself. This behavior will
        // then better match how text node insertions work. We will need to
        // also update the last element's child accordingly as we do this.
        if (!firstElement.is(lastElement) && lastElement.isInline()) {
          // Keep traversing till we have a non-inline element parent.
          do {
            lastElementChild = lastElement;
            lastElement = lastElement.getParentOrThrow();
          } while (lastElement.isInline());
        }

        // Handle mutations to the last node.
        if (endPoint.type === 'text' && (endOffset !== 0 || lastNode.getTextContent() === '') || endPoint.type === 'element' && lastNode.getIndexWithinParent() < endOffset) {
          if ($isTextNode(lastNode) && !lastNode.isToken() && endOffset !== lastNode.getTextContentSize()) {
            if (lastNode.isSegmented()) {
              const textNode = $createTextNode(lastNode.getTextContent());
              lastNode.replace(textNode);
              lastNode = textNode;
            }
            // root node selections only select whole nodes, so no text splice is necessary
            if (!$isRootNode(endPoint.getNode()) && endPoint.type === 'text') {
              lastNode = lastNode.spliceText(0, endOffset, '');
            }
            markedNodeKeysForKeep.add(lastNode.__key);
          } else {
            const lastNodeParent = lastNode.getParentOrThrow();
            if (!lastNodeParent.canBeEmpty() && lastNodeParent.getChildrenSize() === 1) {
              lastNodeParent.remove();
            } else {
              lastNode.remove();
            }
          }
        } else {
          markedNodeKeysForKeep.add(lastNode.__key);
        }

        // Either move the remaining nodes of the last parent to after
        // the first child, or remove them entirely. If the last parent
        // is the same as the first parent, this logic also works.
        const lastNodeChildren = lastElement.getChildren();
        const selectedNodesSet = new Set(selectedNodes);
        const firstAndLastElementsAreEqual = firstElement.is(lastElement);

        // We choose a target to insert all nodes after. In the case of having
        // and inline starting parent element with a starting node that has no
        // siblings, we should insert after the starting parent element, otherwise
        // we will incorrectly merge into the starting parent element.
        // TODO: should we keep on traversing parents if we're inside another
        // nested inline element?
        const insertionTarget = firstElement.isInline() && firstNode.getNextSibling() === null ? firstElement : firstNode;
        for (let i = lastNodeChildren.length - 1; i >= 0; i--) {
          const lastNodeChild = lastNodeChildren[i];
          if (lastNodeChild.is(firstNode) || $isElementNode(lastNodeChild) && lastNodeChild.isParentOf(firstNode)) {
            break;
          }
          if (lastNodeChild.isAttached()) {
            if (!selectedNodesSet.has(lastNodeChild) || lastNodeChild.is(lastElementChild)) {
              if (!firstAndLastElementsAreEqual) {
                insertionTarget.insertAfter(lastNodeChild, false);
              }
            } else {
              lastNodeChild.remove();
            }
          }
        }
        if (!firstAndLastElementsAreEqual) {
          // Check if we have already moved out all the nodes of the
          // last parent, and if so, traverse the parent tree and mark
          // them all as being able to deleted too.
          let parent = lastElement;
          let lastRemovedParent = null;
          while (parent !== null) {
            const children = parent.getChildren();
            const childrenLength = children.length;
            if (childrenLength === 0 || children[childrenLength - 1].is(lastRemovedParent)) {
              markedNodeKeysForKeep.delete(parent.__key);
              lastRemovedParent = parent;
            }
            parent = parent.getParent();
          }
        }

        // Ensure we do splicing after moving of nodes, as splicing
        // can have side-effects (in the case of hashtags).
        if (!firstNode.isToken()) {
          firstNode = firstNode.spliceText(startOffset, firstNodeTextLength - startOffset, text, true);
          if (firstNode.getTextContent() === '') {
            firstNode.remove();
          } else if (firstNode.isComposing() && this.anchor.type === 'text') {
            // When composing, we need to adjust the anchor offset so that
            // we correctly replace that right range.
            this.anchor.offset -= text.length;
          }
        } else if (startOffset === firstNodeTextLength) {
          firstNode.select();
        } else {
          const textNode = $createTextNode(text);
          textNode.select();
          firstNode.replace(textNode);
        }

        // Remove all selected nodes that haven't already been removed.
        for (let i = 1; i < selectedNodesLength; i++) {
          const selectedNode = selectedNodes[i];
          const key = selectedNode.__key;
          if (!markedNodeKeysForKeep.has(key)) {
            selectedNode.remove();
          }
        }
      }
    }

    /**
     * Removes the text in the Selection, adjusting the EditorState accordingly.
     */
    removeText() {
      this.insertText('');
    }

    /**
     * Applies the provided format to the TextNodes in the Selection, splitting or
     * merging nodes as necessary.
     *
     * @param formatType the format type to apply to the nodes in the Selection.
     */
    formatText(formatType) {
      if (this.isCollapsed()) {
        this.toggleFormat(formatType);
        // When changing format, we should stop composition
        $setCompositionKey(null);
        return;
      }
      const selectedNodes = this.getNodes();
      const selectedTextNodes = [];
      for (const selectedNode of selectedNodes) {
        if ($isTextNode(selectedNode)) {
          selectedTextNodes.push(selectedNode);
        }
      }
      const selectedTextNodesLength = selectedTextNodes.length;
      if (selectedTextNodesLength === 0) {
        this.toggleFormat(formatType);
        // When changing format, we should stop composition
        $setCompositionKey(null);
        return;
      }
      const anchor = this.anchor;
      const focus = this.focus;
      const isBackward = this.isBackward();
      const startPoint = isBackward ? focus : anchor;
      const endPoint = isBackward ? anchor : focus;
      let firstIndex = 0;
      let firstNode = selectedTextNodes[0];
      let startOffset = startPoint.type === 'element' ? 0 : startPoint.offset;

      // In case selection started at the end of text node use next text node
      if (startPoint.type === 'text' && startOffset === firstNode.getTextContentSize()) {
        firstIndex = 1;
        firstNode = selectedTextNodes[1];
        startOffset = 0;
      }
      if (firstNode == null) {
        return;
      }
      const firstNextFormat = firstNode.getFormatFlags(formatType, null);
      const lastIndex = selectedTextNodesLength - 1;
      let lastNode = selectedTextNodes[lastIndex];
      const endOffset = endPoint.type === 'text' ? endPoint.offset : lastNode.getTextContentSize();

      // Single node selected
      if (firstNode.is(lastNode)) {
        // No actual text is selected, so do nothing.
        if (startOffset === endOffset) {
          return;
        }
        // The entire node is selected or it is token, so just format it
        if ($isTokenOrSegmented(firstNode) || startOffset === 0 && endOffset === firstNode.getTextContentSize()) {
          firstNode.setFormat(firstNextFormat);
        } else {
          // Node is partially selected, so split it into two nodes
          // add style the selected one.
          const splitNodes = firstNode.splitText(startOffset, endOffset);
          const replacement = startOffset === 0 ? splitNodes[0] : splitNodes[1];
          replacement.setFormat(firstNextFormat);

          // Update selection only if starts/ends on text node
          if (startPoint.type === 'text') {
            startPoint.set(replacement.__key, 0, 'text');
          }
          if (endPoint.type === 'text') {
            endPoint.set(replacement.__key, endOffset - startOffset, 'text');
          }
        }
        this.format = firstNextFormat;
        return;
      }
      // Multiple nodes selected
      // The entire first node isn't selected, so split it
      if (startOffset !== 0 && !$isTokenOrSegmented(firstNode)) {
        [, firstNode] = firstNode.splitText(startOffset);
        startOffset = 0;
      }
      firstNode.setFormat(firstNextFormat);
      const lastNextFormat = lastNode.getFormatFlags(formatType, firstNextFormat);
      // If the offset is 0, it means no actual characters are selected,
      // so we skip formatting the last node altogether.
      if (endOffset > 0) {
        if (endOffset !== lastNode.getTextContentSize() && !$isTokenOrSegmented(lastNode)) {
          [lastNode] = lastNode.splitText(endOffset);
        }
        lastNode.setFormat(lastNextFormat);
      }

      // Process all text nodes in between
      for (let i = firstIndex + 1; i < lastIndex; i++) {
        const textNode = selectedTextNodes[i];
        const nextFormat = textNode.getFormatFlags(formatType, lastNextFormat);
        textNode.setFormat(nextFormat);
      }

      // Update selection only if starts/ends on text node
      if (startPoint.type === 'text') {
        startPoint.set(firstNode.__key, startOffset, 'text');
      }
      if (endPoint.type === 'text') {
        endPoint.set(lastNode.__key, endOffset, 'text');
      }
      this.format = firstNextFormat | lastNextFormat;
    }

    /**
     * Attempts to "intelligently" insert an arbitrary list of Lexical nodes into the EditorState at the
     * current Selection according to a set of heuristics that determine how surrounding nodes
     * should be changed, replaced, or moved to accomodate the incoming ones.
     *
     * @param nodes - the nodes to insert
     */
    insertNodes(nodes) {
      if (nodes.length === 0) {
        return;
      }
      if (this.anchor.key === 'root') {
        this.insertParagraph();
        const selection = $getSelection();
        if (!$isRangeSelection(selection)) {
          throw Error(`Expected RangeSelection after insertParagraph`);
        }
        return selection.insertNodes(nodes);
      }
      const firstPoint = this.isBackward() ? this.focus : this.anchor;
      const firstBlock = $getAncestor(firstPoint.getNode(), INTERNAL_$isBlock);
      const last = nodes[nodes.length - 1];

      // CASE 1: insert inside a code block
      if ('__language' in firstBlock && $isElementNode(firstBlock)) {
        if ('__language' in nodes[0]) {
          this.insertText(nodes[0].getTextContent());
        } else {
          const index = $removeTextAndSplitBlock(this);
          firstBlock.splice(index, 0, nodes);
          last.selectEnd();
        }
        return;
      }

      // CASE 2: All elements of the array are inline
      const notInline = node => ($isElementNode(node) || $isDecoratorNode(node)) && !node.isInline();
      if (!nodes.some(notInline)) {
        if (!$isElementNode(firstBlock)) {
          throw Error(`Expected 'firstBlock' to be an ElementNode`);
        }
        const index = $removeTextAndSplitBlock(this);
        firstBlock.splice(index, 0, nodes);
        last.selectEnd();
        return;
      }

      // CASE 3: At least 1 element of the array is not inline
      const blocksParent = $wrapInlineNodes(nodes);
      const nodeToSelect = blocksParent.getLastDescendant();
      const blocks = blocksParent.getChildren();
      const isMergeable = node => $isElementNode(node) && INTERNAL_$isBlock(node) && !node.isEmpty() && $isElementNode(firstBlock) && (!firstBlock.isEmpty() || firstBlock.canMergeWhenEmpty());
      const shouldInsert = !$isElementNode(firstBlock) || !firstBlock.isEmpty();
      const insertedParagraph = shouldInsert ? this.insertParagraph() : null;
      const lastToInsert = blocks[blocks.length - 1];
      let firstToInsert = blocks[0];
      if (isMergeable(firstToInsert)) {
        if (!$isElementNode(firstBlock)) {
          throw Error(`Expected 'firstBlock' to be an ElementNode`);
        }
        firstBlock.append(...firstToInsert.getChildren());
        firstToInsert = blocks[1];
      }
      if (firstToInsert) {
        insertRangeAfter(firstBlock, firstToInsert);
      }
      const lastInsertedBlock = $getAncestor(nodeToSelect, INTERNAL_$isBlock);
      if (insertedParagraph && $isElementNode(lastInsertedBlock) && (insertedParagraph.canMergeWhenEmpty() || INTERNAL_$isBlock(lastToInsert))) {
        lastInsertedBlock.append(...insertedParagraph.getChildren());
        insertedParagraph.remove();
      }
      if ($isElementNode(firstBlock) && firstBlock.isEmpty()) {
        firstBlock.remove();
      }
      nodeToSelect.selectEnd();

      // To understand this take a look at the test "can wrap post-linebreak nodes into new element"
      const lastChild = $isElementNode(firstBlock) ? firstBlock.getLastChild() : null;
      if ($isLineBreakNode(lastChild) && lastInsertedBlock !== firstBlock) {
        lastChild.remove();
      }
    }

    /**
     * Inserts a new ParagraphNode into the EditorState at the current Selection
     *
     * @returns the newly inserted node.
     */
    insertParagraph() {
      if (this.anchor.key === 'root') {
        const paragraph = $createParagraphNode();
        $getRoot().splice(this.anchor.offset, 0, [paragraph]);
        paragraph.select();
        return paragraph;
      }
      const index = $removeTextAndSplitBlock(this);
      const block = $getAncestor(this.anchor.getNode(), INTERNAL_$isBlock);
      if (!$isElementNode(block)) {
        throw Error(`Expected ancestor to be an ElementNode`);
      }
      const firstToAppend = block.getChildAtIndex(index);
      const nodesToInsert = firstToAppend ? [firstToAppend, ...firstToAppend.getNextSiblings()] : [];
      const newBlock = block.insertNewAfter(this, false);
      if (newBlock) {
        newBlock.append(...nodesToInsert);
        newBlock.selectStart();
        return newBlock;
      }
      // if newBlock is null, it means that block is of type CodeNode.
      return null;
    }

    /**
     * Inserts a logical linebreak, which may be a new LineBreakNode or a new ParagraphNode, into the EditorState at the
     * current Selection.
     */
    insertLineBreak(selectStart) {
      const lineBreak = $createLineBreakNode();
      this.insertNodes([lineBreak]);
      // this is used in MacOS with the command 'ctrl-O' (openLineBreak)
      if (selectStart) {
        const parent = lineBreak.getParentOrThrow();
        const index = lineBreak.getIndexWithinParent();
        parent.select(index, index);
      }
    }

    /**
     * Extracts the nodes in the Selection, splitting nodes where necessary
     * to get offset-level precision.
     *
     * @returns The nodes in the Selection
     */
    extract() {
      const selectedNodes = this.getNodes();
      const selectedNodesLength = selectedNodes.length;
      const lastIndex = selectedNodesLength - 1;
      const anchor = this.anchor;
      const focus = this.focus;
      let firstNode = selectedNodes[0];
      let lastNode = selectedNodes[lastIndex];
      const [anchorOffset, focusOffset] = $getCharacterOffsets(this);
      if (selectedNodesLength === 0) {
        return [];
      } else if (selectedNodesLength === 1) {
        if ($isTextNode(firstNode) && !this.isCollapsed()) {
          const startOffset = anchorOffset > focusOffset ? focusOffset : anchorOffset;
          const endOffset = anchorOffset > focusOffset ? anchorOffset : focusOffset;
          const splitNodes = firstNode.splitText(startOffset, endOffset);
          const node = startOffset === 0 ? splitNodes[0] : splitNodes[1];
          return node != null ? [node] : [];
        }
        return [firstNode];
      }
      const isBefore = anchor.isBefore(focus);
      if ($isTextNode(firstNode)) {
        const startOffset = isBefore ? anchorOffset : focusOffset;
        if (startOffset === firstNode.getTextContentSize()) {
          selectedNodes.shift();
        } else if (startOffset !== 0) {
          [, firstNode] = firstNode.splitText(startOffset);
          selectedNodes[0] = firstNode;
        }
      }
      if ($isTextNode(lastNode)) {
        const lastNodeText = lastNode.getTextContent();
        const lastNodeTextLength = lastNodeText.length;
        const endOffset = isBefore ? focusOffset : anchorOffset;
        if (endOffset === 0) {
          selectedNodes.pop();
        } else if (endOffset !== lastNodeTextLength) {
          [lastNode] = lastNode.splitText(endOffset);
          selectedNodes[lastIndex] = lastNode;
        }
      }
      return selectedNodes;
    }

    /**
     * Modifies the Selection according to the parameters and a set of heuristics that account for
     * various node types. Can be used to safely move or extend selection by one logical "unit" without
     * dealing explicitly with all the possible node types.
     *
     * @param alter the type of modification to perform
     * @param isBackward whether or not selection is backwards
     * @param granularity the granularity at which to apply the modification
     */
    modify(alter, isBackward, granularity) {
      const focus = this.focus;
      const anchor = this.anchor;
      const collapse = alter === 'move';

      // Handle the selection movement around decorators.
      const possibleNode = $getAdjacentNode(focus, isBackward);
      if ($isDecoratorNode(possibleNode) && !possibleNode.isIsolated()) {
        // Make it possible to move selection from range selection to
        // node selection on the node.
        if (collapse && possibleNode.isKeyboardSelectable()) {
          const nodeSelection = $createNodeSelection();
          nodeSelection.add(possibleNode.__key);
          $setSelection(nodeSelection);
          return;
        }
        const sibling = isBackward ? possibleNode.getPreviousSibling() : possibleNode.getNextSibling();
        if (!$isTextNode(sibling)) {
          const parent = possibleNode.getParentOrThrow();
          let offset;
          let elementKey;
          if ($isElementNode(sibling)) {
            elementKey = sibling.__key;
            offset = isBackward ? sibling.getChildrenSize() : 0;
          } else {
            offset = possibleNode.getIndexWithinParent();
            elementKey = parent.__key;
            if (!isBackward) {
              offset++;
            }
          }
          focus.set(elementKey, offset, 'element');
          if (collapse) {
            anchor.set(elementKey, offset, 'element');
          }
          return;
        } else {
          const siblingKey = sibling.__key;
          const offset = isBackward ? sibling.getTextContent().length : 0;
          focus.set(siblingKey, offset, 'text');
          if (collapse) {
            anchor.set(siblingKey, offset, 'text');
          }
          return;
        }
      }
      const editor = getActiveEditor();
      const domSelection = getDOMSelection(editor._window);
      if (!domSelection) {
        return;
      }
      const blockCursorElement = editor._blockCursorElement;
      const rootElement = editor._rootElement;
      // Remove the block cursor element if it exists. This will ensure selection
      // works as intended. If we leave it in the DOM all sorts of strange bugs
      // occur. :/
      if (rootElement !== null && blockCursorElement !== null && $isElementNode(possibleNode) && !possibleNode.isInline() && !possibleNode.canBeEmpty()) {
        removeDOMBlockCursorElement(blockCursorElement, editor, rootElement);
      }
      // We use the DOM selection.modify API here to "tell" us what the selection
      // will be. We then use it to update the Lexical selection accordingly. This
      // is much more reliable than waiting for a beforeinput and using the ranges
      // from getTargetRanges(), and is also better than trying to do it ourselves
      // using Intl.Segmenter or other workarounds that struggle with word segments
      // and line segments (especially with word wrapping and non-Roman languages).
      moveNativeSelection(domSelection, alter, isBackward ? 'backward' : 'forward', granularity);
      // Guard against no ranges
      if (domSelection.rangeCount > 0) {
        const range = domSelection.getRangeAt(0);
        // Apply the DOM selection to our Lexical selection.
        const anchorNode = this.anchor.getNode();
        const root = $isRootNode(anchorNode) ? anchorNode : $getNearestRootOrShadowRoot(anchorNode);
        this.applyDOMRange(range);
        this.dirty = true;
        if (!collapse) {
          // Validate selection; make sure that the new extended selection respects shadow roots
          const nodes = this.getNodes();
          const validNodes = [];
          let shrinkSelection = false;
          for (let i = 0; i < nodes.length; i++) {
            const nextNode = nodes[i];
            if ($hasAncestor(nextNode, root)) {
              validNodes.push(nextNode);
            } else {
              shrinkSelection = true;
            }
          }
          if (shrinkSelection && validNodes.length > 0) {
            // validNodes length check is a safeguard against an invalid selection; as getNodes()
            // will return an empty array in this case
            if (isBackward) {
              const firstValidNode = validNodes[0];
              if ($isElementNode(firstValidNode)) {
                firstValidNode.selectStart();
              } else {
                firstValidNode.getParentOrThrow().selectStart();
              }
            } else {
              const lastValidNode = validNodes[validNodes.length - 1];
              if ($isElementNode(lastValidNode)) {
                lastValidNode.selectEnd();
              } else {
                lastValidNode.getParentOrThrow().selectEnd();
              }
            }
          }

          // Because a range works on start and end, we might need to flip
          // the anchor and focus points to match what the DOM has, not what
          // the range has specifically.
          if (domSelection.anchorNode !== range.startContainer || domSelection.anchorOffset !== range.startOffset) {
            $swapPoints(this);
          }
        }
      }
    }
    /**
     * Helper for handling forward character and word deletion that prevents element nodes
     * like a table, columns layout being destroyed
     *
     * @param anchor the anchor
     * @param anchorNode the anchor node in the selection
     * @param isBackward whether or not selection is backwards
     */
    forwardDeletion(anchor, anchorNode, isBackward) {
      if (!isBackward && (
      // Delete forward handle case
      anchor.type === 'element' && $isElementNode(anchorNode) && anchor.offset === anchorNode.getChildrenSize() || anchor.type === 'text' && anchor.offset === anchorNode.getTextContentSize())) {
        const parent = anchorNode.getParent();
        const nextSibling = anchorNode.getNextSibling() || (parent === null ? null : parent.getNextSibling());
        if ($isElementNode(nextSibling) && nextSibling.isShadowRoot()) {
          return true;
        }
      }
      return false;
    }

    /**
     * Performs one logical character deletion operation on the EditorState based on the current Selection.
     * Handles different node types.
     *
     * @param isBackward whether or not the selection is backwards.
     */
    deleteCharacter(isBackward) {
      const wasCollapsed = this.isCollapsed();
      if (this.isCollapsed()) {
        const anchor = this.anchor;
        let anchorNode = anchor.getNode();
        if (this.forwardDeletion(anchor, anchorNode, isBackward)) {
          return;
        }

        // Handle the deletion around decorators.
        const focus = this.focus;
        const possibleNode = $getAdjacentNode(focus, isBackward);
        if ($isDecoratorNode(possibleNode) && !possibleNode.isIsolated()) {
          // Make it possible to move selection from range selection to
          // node selection on the node.
          if (possibleNode.isKeyboardSelectable() && $isElementNode(anchorNode) && anchorNode.getChildrenSize() === 0) {
            anchorNode.remove();
            const nodeSelection = $createNodeSelection();
            nodeSelection.add(possibleNode.__key);
            $setSelection(nodeSelection);
          } else {
            possibleNode.remove();
            const editor = getActiveEditor();
            editor.dispatchCommand(SELECTION_CHANGE_COMMAND, undefined);
          }
          return;
        } else if (!isBackward && $isElementNode(possibleNode) && $isElementNode(anchorNode) && anchorNode.isEmpty()) {
          anchorNode.remove();
          possibleNode.selectStart();
          return;
        }
        this.modify('extend', isBackward, 'character');
        if (!this.isCollapsed()) {
          const focusNode = focus.type === 'text' ? focus.getNode() : null;
          anchorNode = anchor.type === 'text' ? anchor.getNode() : null;
          if (focusNode !== null && focusNode.isSegmented()) {
            const offset = focus.offset;
            const textContentSize = focusNode.getTextContentSize();
            if (focusNode.is(anchorNode) || isBackward && offset !== textContentSize || !isBackward && offset !== 0) {
              $removeSegment(focusNode, isBackward, offset);
              return;
            }
          } else if (anchorNode !== null && anchorNode.isSegmented()) {
            const offset = anchor.offset;
            const textContentSize = anchorNode.getTextContentSize();
            if (anchorNode.is(focusNode) || isBackward && offset !== 0 || !isBackward && offset !== textContentSize) {
              $removeSegment(anchorNode, isBackward, offset);
              return;
            }
          }
          $updateCaretSelectionForUnicodeCharacter(this, isBackward);
        } else if (isBackward && anchor.offset === 0) {
          // Special handling around rich text nodes
          const element = anchor.type === 'element' ? anchor.getNode() : anchor.getNode().getParentOrThrow();
          if (element.collapseAtStart(this)) {
            return;
          }
        }
      }
      this.removeText();
      if (isBackward && !wasCollapsed && this.isCollapsed() && this.anchor.type === 'element' && this.anchor.offset === 0) {
        const anchorNode = this.anchor.getNode();
        if (anchorNode.isEmpty() && $isRootNode(anchorNode.getParent()) && anchorNode.getIndexWithinParent() === 0) {
          anchorNode.collapseAtStart(this);
        }
      }
    }

    /**
     * Performs one logical line deletion operation on the EditorState based on the current Selection.
     * Handles different node types.
     *
     * @param isBackward whether or not the selection is backwards.
     */
    deleteLine(isBackward) {
      if (this.isCollapsed()) {
        // Since `domSelection.modify('extend', ..., 'lineboundary')` works well for text selections
        // but doesn't properly handle selections which end on elements, a space character is added
        // for such selections transforming their anchor's type to 'text'
        const anchorIsElement = this.anchor.type === 'element';
        if (anchorIsElement) {
          this.insertText(' ');
        }
        this.modify('extend', isBackward, 'lineboundary');

        // If selection is extended to cover text edge then extend it one character more
        // to delete its parent element. Otherwise text content will be deleted but empty
        // parent node will remain
        const endPoint = isBackward ? this.focus : this.anchor;
        if (endPoint.offset === 0) {
          this.modify('extend', isBackward, 'character');
        }

        // Adjusts selection to include an extra character added for element anchors to remove it
        if (anchorIsElement) {
          const startPoint = isBackward ? this.anchor : this.focus;
          startPoint.set(startPoint.key, startPoint.offset + 1, startPoint.type);
        }
      }
      this.removeText();
    }

    /**
     * Performs one logical word deletion operation on the EditorState based on the current Selection.
     * Handles different node types.
     *
     * @param isBackward whether or not the selection is backwards.
     */
    deleteWord(isBackward) {
      if (this.isCollapsed()) {
        const anchor = this.anchor;
        const anchorNode = anchor.getNode();
        if (this.forwardDeletion(anchor, anchorNode, isBackward)) {
          return;
        }
        this.modify('extend', isBackward, 'word');
      }
      this.removeText();
    }

    /**
     * Returns whether the Selection is "backwards", meaning the focus
     * logically precedes the anchor in the EditorState.
     * @returns true if the Selection is backwards, false otherwise.
     */
    isBackward() {
      return this.focus.isBefore(this.anchor);
    }
    getStartEndPoints() {
      return [this.anchor, this.focus];
    }
  }
  function $isNodeSelection(x) {
    return x instanceof NodeSelection;
  }
  function getCharacterOffset(point) {
    const offset = point.offset;
    if (point.type === 'text') {
      return offset;
    }
    const parent = point.getNode();
    return offset === parent.getChildrenSize() ? parent.getTextContent().length : 0;
  }
  function $getCharacterOffsets(selection) {
    const anchorAndFocus = selection.getStartEndPoints();
    if (anchorAndFocus === null) {
      return [0, 0];
    }
    const [anchor, focus] = anchorAndFocus;
    if (anchor.type === 'element' && focus.type === 'element' && anchor.key === focus.key && anchor.offset === focus.offset) {
      return [0, 0];
    }
    return [getCharacterOffset(anchor), getCharacterOffset(focus)];
  }
  function $swapPoints(selection) {
    const focus = selection.focus;
    const anchor = selection.anchor;
    const anchorKey = anchor.key;
    const anchorOffset = anchor.offset;
    const anchorType = anchor.type;
    $setPointValues(anchor, focus.key, focus.offset, focus.type);
    $setPointValues(focus, anchorKey, anchorOffset, anchorType);
    selection._cachedNodes = null;
  }
  function moveNativeSelection(domSelection, alter, direction, granularity) {
    // Selection.modify() method applies a change to the current selection or cursor position,
    // but is still non-standard in some browsers.
    domSelection.modify(alter, direction, granularity);
  }
  function $updateCaretSelectionForUnicodeCharacter(selection, isBackward) {
    const anchor = selection.anchor;
    const focus = selection.focus;
    const anchorNode = anchor.getNode();
    const focusNode = focus.getNode();
    if (anchorNode === focusNode && anchor.type === 'text' && focus.type === 'text') {
      // Handling of multibyte characters
      const anchorOffset = anchor.offset;
      const focusOffset = focus.offset;
      const isBefore = anchorOffset < focusOffset;
      const startOffset = isBefore ? anchorOffset : focusOffset;
      const endOffset = isBefore ? focusOffset : anchorOffset;
      const characterOffset = endOffset - 1;
      if (startOffset !== characterOffset) {
        const text = anchorNode.getTextContent().slice(startOffset, endOffset);
        if (!doesContainGrapheme(text)) {
          if (isBackward) {
            focus.offset = characterOffset;
          } else {
            anchor.offset = characterOffset;
          }
        }
      }
    }
  }
  function $removeSegment(node, isBackward, offset) {
    const textNode = node;
    const textContent = textNode.getTextContent();
    const split = textContent.split(/(?=\s)/g);
    const splitLength = split.length;
    let segmentOffset = 0;
    let restoreOffset = 0;
    for (let i = 0; i < splitLength; i++) {
      const text = split[i];
      const isLast = i === splitLength - 1;
      restoreOffset = segmentOffset;
      segmentOffset += text.length;
      if (isBackward && segmentOffset === offset || segmentOffset > offset || isLast) {
        split.splice(i, 1);
        if (isLast) {
          restoreOffset = undefined;
        }
        break;
      }
    }
    const nextTextContent = split.join('').trim();
    if (nextTextContent === '') {
      textNode.remove();
    } else {
      textNode.setTextContent(nextTextContent);
      textNode.select(restoreOffset, restoreOffset);
    }
  }
  function shouldResolveAncestor(resolvedElement, resolvedOffset, lastPoint) {
    const parent = resolvedElement.getParent();
    return lastPoint === null || parent === null || !parent.canBeEmpty() || parent !== lastPoint.getNode();
  }
  function $internalResolveSelectionPoint(dom, offset, lastPoint, editor) {
    let resolvedOffset = offset;
    let resolvedNode;
    // If we have selection on an element, we will
    // need to figure out (using the offset) what text
    // node should be selected.

    if (dom.nodeType === DOM_ELEMENT_TYPE) {
      // Resolve element to a ElementNode, or TextNode, or null
      let moveSelectionToEnd = false;
      // Given we're moving selection to another node, selection is
      // definitely dirty.
      // We use the anchor to find which child node to select
      const childNodes = dom.childNodes;
      const childNodesLength = childNodes.length;
      const blockCursorElement = editor._blockCursorElement;
      // If the anchor is the same as length, then this means we
      // need to select the very last text node.
      if (resolvedOffset === childNodesLength) {
        moveSelectionToEnd = true;
        resolvedOffset = childNodesLength - 1;
      }
      let childDOM = childNodes[resolvedOffset];
      let hasBlockCursor = false;
      if (childDOM === blockCursorElement) {
        childDOM = childNodes[resolvedOffset + 1];
        hasBlockCursor = true;
      } else if (blockCursorElement !== null) {
        const blockCursorElementParent = blockCursorElement.parentNode;
        if (dom === blockCursorElementParent) {
          const blockCursorOffset = Array.prototype.indexOf.call(blockCursorElementParent.children, blockCursorElement);
          if (offset > blockCursorOffset) {
            resolvedOffset--;
          }
        }
      }
      resolvedNode = $getNodeFromDOM(childDOM);
      if ($isTextNode(resolvedNode)) {
        resolvedOffset = getTextNodeOffset(resolvedNode, moveSelectionToEnd);
      } else {
        let resolvedElement = $getNodeFromDOM(dom);
        // Ensure resolvedElement is actually a element.
        if (resolvedElement === null) {
          return null;
        }
        if ($isElementNode(resolvedElement)) {
          resolvedOffset = Math.min(resolvedElement.getChildrenSize(), resolvedOffset);
          let child = resolvedElement.getChildAtIndex(resolvedOffset);
          if ($isElementNode(child) && shouldResolveAncestor(child, resolvedOffset, lastPoint)) {
            const descendant = moveSelectionToEnd ? child.getLastDescendant() : child.getFirstDescendant();
            if (descendant === null) {
              resolvedElement = child;
            } else {
              child = descendant;
              resolvedElement = $isElementNode(child) ? child : child.getParentOrThrow();
            }
            resolvedOffset = 0;
          }
          if ($isTextNode(child)) {
            resolvedNode = child;
            resolvedElement = null;
            resolvedOffset = getTextNodeOffset(child, moveSelectionToEnd);
          } else if (child !== resolvedElement && moveSelectionToEnd && !hasBlockCursor) {
            resolvedOffset++;
          }
        } else {
          const index = resolvedElement.getIndexWithinParent();
          // When selecting decorators, there can be some selection issues when using resolvedOffset,
          // and instead we should be checking if we're using the offset
          if (offset === 0 && $isDecoratorNode(resolvedElement) && $getNodeFromDOM(dom) === resolvedElement) {
            resolvedOffset = index;
          } else {
            resolvedOffset = index + 1;
          }
          resolvedElement = resolvedElement.getParentOrThrow();
        }
        if ($isElementNode(resolvedElement)) {
          return $createPoint(resolvedElement.__key, resolvedOffset, 'element');
        }
      }
    } else {
      // TextNode or null
      resolvedNode = $getNodeFromDOM(dom);
    }
    if (!$isTextNode(resolvedNode)) {
      return null;
    }
    return $createPoint(resolvedNode.__key, resolvedOffset, 'text');
  }
  function resolveSelectionPointOnBoundary(point, isBackward, isCollapsed) {
    const offset = point.offset;
    const node = point.getNode();
    if (offset === 0) {
      const prevSibling = node.getPreviousSibling();
      const parent = node.getParent();
      if (!isBackward) {
        if ($isElementNode(prevSibling) && !isCollapsed && prevSibling.isInline()) {
          point.key = prevSibling.__key;
          point.offset = prevSibling.getChildrenSize();
          // @ts-expect-error: intentional
          point.type = 'element';
        } else if ($isTextNode(prevSibling)) {
          point.key = prevSibling.__key;
          point.offset = prevSibling.getTextContent().length;
        }
      } else if ((isCollapsed || !isBackward) && prevSibling === null && $isElementNode(parent) && parent.isInline()) {
        const parentSibling = parent.getPreviousSibling();
        if ($isTextNode(parentSibling)) {
          point.key = parentSibling.__key;
          point.offset = parentSibling.getTextContent().length;
        }
      }
    } else if (offset === node.getTextContent().length) {
      const nextSibling = node.getNextSibling();
      const parent = node.getParent();
      if (isBackward && $isElementNode(nextSibling) && nextSibling.isInline()) {
        point.key = nextSibling.__key;
        point.offset = 0;
        // @ts-expect-error: intentional
        point.type = 'element';
      } else if ((isCollapsed || isBackward) && nextSibling === null && $isElementNode(parent) && parent.isInline() && !parent.canInsertTextAfter()) {
        const parentSibling = parent.getNextSibling();
        if ($isTextNode(parentSibling)) {
          point.key = parentSibling.__key;
          point.offset = 0;
        }
      }
    }
  }
  function $normalizeSelectionPointsForBoundaries(anchor, focus, lastSelection) {
    if (anchor.type === 'text' && focus.type === 'text') {
      const isBackward = anchor.isBefore(focus);
      const isCollapsed = anchor.is(focus);

      // Attempt to normalize the offset to the previous sibling if we're at the
      // start of a text node and the sibling is a text node or inline element.
      resolveSelectionPointOnBoundary(anchor, isBackward, isCollapsed);
      resolveSelectionPointOnBoundary(focus, !isBackward, isCollapsed);
      if (isCollapsed) {
        focus.key = anchor.key;
        focus.offset = anchor.offset;
        focus.type = anchor.type;
      }
      const editor = getActiveEditor();
      if (editor.isComposing() && editor._compositionKey !== anchor.key && $isRangeSelection(lastSelection)) {
        const lastAnchor = lastSelection.anchor;
        const lastFocus = lastSelection.focus;
        $setPointValues(anchor, lastAnchor.key, lastAnchor.offset, lastAnchor.type);
        $setPointValues(focus, lastFocus.key, lastFocus.offset, lastFocus.type);
      }
    }
  }
  function $internalResolveSelectionPoints(anchorDOM, anchorOffset, focusDOM, focusOffset, editor, lastSelection) {
    if (anchorDOM === null || focusDOM === null || !isSelectionWithinEditor(editor, anchorDOM, focusDOM)) {
      return null;
    }
    const resolvedAnchorPoint = $internalResolveSelectionPoint(anchorDOM, anchorOffset, $isRangeSelection(lastSelection) ? lastSelection.anchor : null, editor);
    if (resolvedAnchorPoint === null) {
      return null;
    }
    const resolvedFocusPoint = $internalResolveSelectionPoint(focusDOM, focusOffset, $isRangeSelection(lastSelection) ? lastSelection.focus : null, editor);
    if (resolvedFocusPoint === null) {
      return null;
    }
    if (resolvedAnchorPoint.type === 'element' && resolvedFocusPoint.type === 'element') {
      const anchorNode = $getNodeFromDOM(anchorDOM);
      const focusNode = $getNodeFromDOM(focusDOM);
      // Ensure if we're selecting the content of a decorator that we
      // return null for this point, as it's not in the controlled scope
      // of Lexical.
      if ($isDecoratorNode(anchorNode) && $isDecoratorNode(focusNode)) {
        return null;
      }
    }

    // Handle normalization of selection when it is at the boundaries.
    $normalizeSelectionPointsForBoundaries(resolvedAnchorPoint, resolvedFocusPoint, lastSelection);
    return [resolvedAnchorPoint, resolvedFocusPoint];
  }
  function $isBlockElementNode(node) {
    return $isElementNode(node) && !node.isInline();
  }

  // This is used to make a selection when the existing
  // selection is null, i.e. forcing selection on the editor
  // when it current exists outside the editor.

  function $internalMakeRangeSelection(anchorKey, anchorOffset, focusKey, focusOffset, anchorType, focusType) {
    const editorState = getActiveEditorState();
    const selection = new RangeSelection($createPoint(anchorKey, anchorOffset, anchorType), $createPoint(focusKey, focusOffset, focusType), 0, '');
    selection.dirty = true;
    editorState._selection = selection;
    return selection;
  }
  function $createRangeSelection() {
    const anchor = $createPoint('root', 0, 'element');
    const focus = $createPoint('root', 0, 'element');
    return new RangeSelection(anchor, focus, 0, '');
  }
  function $createNodeSelection() {
    return new NodeSelection(new Set());
  }
  function $internalCreateSelection(editor) {
    const currentEditorState = editor.getEditorState();
    const lastSelection = currentEditorState._selection;
    const domSelection = getDOMSelection(editor._window);
    if ($isRangeSelection(lastSelection) || lastSelection == null) {
      return $internalCreateRangeSelection(lastSelection, domSelection, editor, null);
    }
    return lastSelection.clone();
  }
  function $createRangeSelectionFromDom(domSelection, editor) {
    return $internalCreateRangeSelection(null, domSelection, editor, null);
  }
  function $internalCreateRangeSelection(lastSelection, domSelection, editor, event) {
    const windowObj = editor._window;
    if (windowObj === null) {
      return null;
    }
    // When we create a selection, we try to use the previous
    // selection where possible, unless an actual user selection
    // change has occurred. When we do need to create a new selection
    // we validate we can have text nodes for both anchor and focus
    // nodes. If that holds true, we then return that selection
    // as a mutable object that we use for the editor state for this
    // update cycle. If a selection gets changed, and requires a
    // update to native DOM selection, it gets marked as "dirty".
    // If the selection changes, but matches with the existing
    // DOM selection, then we only need to sync it. Otherwise,
    // we generally bail out of doing an update to selection during
    // reconciliation unless there are dirty nodes that need
    // reconciling.

    const windowEvent = event || windowObj.event;
    const eventType = windowEvent ? windowEvent.type : undefined;
    const isSelectionChange = eventType === 'selectionchange';
    const useDOMSelection = !getIsProcessingMutations() && (isSelectionChange || eventType === 'beforeinput' || eventType === 'compositionstart' || eventType === 'compositionend' || eventType === 'click' && windowEvent && windowEvent.detail === 3 || eventType === 'drop' || eventType === undefined);
    let anchorDOM, focusDOM, anchorOffset, focusOffset;
    if (!$isRangeSelection(lastSelection) || useDOMSelection) {
      if (domSelection === null) {
        return null;
      }
      anchorDOM = domSelection.anchorNode;
      focusDOM = domSelection.focusNode;
      anchorOffset = domSelection.anchorOffset;
      focusOffset = domSelection.focusOffset;
      if (isSelectionChange && $isRangeSelection(lastSelection) && !isSelectionWithinEditor(editor, anchorDOM, focusDOM)) {
        return lastSelection.clone();
      }
    } else {
      return lastSelection.clone();
    }
    // Let's resolve the text nodes from the offsets and DOM nodes we have from
    // native selection.
    const resolvedSelectionPoints = $internalResolveSelectionPoints(anchorDOM, anchorOffset, focusDOM, focusOffset, editor, lastSelection);
    if (resolvedSelectionPoints === null) {
      return null;
    }
    const [resolvedAnchorPoint, resolvedFocusPoint] = resolvedSelectionPoints;
    return new RangeSelection(resolvedAnchorPoint, resolvedFocusPoint, !$isRangeSelection(lastSelection) ? 0 : lastSelection.format, !$isRangeSelection(lastSelection) ? '' : lastSelection.style);
  }
  function $getSelection() {
    const editorState = getActiveEditorState();
    return editorState._selection;
  }
  function $getPreviousSelection() {
    const editor = getActiveEditor();
    return editor._editorState._selection;
  }
  function $updateElementSelectionOnCreateDeleteNode(selection, parentNode, nodeOffset, times = 1) {
    const anchor = selection.anchor;
    const focus = selection.focus;
    const anchorNode = anchor.getNode();
    const focusNode = focus.getNode();
    if (!parentNode.is(anchorNode) && !parentNode.is(focusNode)) {
      return;
    }
    const parentKey = parentNode.__key;
    // Single node. We shift selection but never redimension it
    if (selection.isCollapsed()) {
      const selectionOffset = anchor.offset;
      if (nodeOffset <= selectionOffset && times > 0 || nodeOffset < selectionOffset && times < 0) {
        const newSelectionOffset = Math.max(0, selectionOffset + times);
        anchor.set(parentKey, newSelectionOffset, 'element');
        focus.set(parentKey, newSelectionOffset, 'element');
        // The new selection might point to text nodes, try to resolve them
        $updateSelectionResolveTextNodes(selection);
      }
    } else {
      // Multiple nodes selected. We shift or redimension selection
      const isBackward = selection.isBackward();
      const firstPoint = isBackward ? focus : anchor;
      const firstPointNode = firstPoint.getNode();
      const lastPoint = isBackward ? anchor : focus;
      const lastPointNode = lastPoint.getNode();
      if (parentNode.is(firstPointNode)) {
        const firstPointOffset = firstPoint.offset;
        if (nodeOffset <= firstPointOffset && times > 0 || nodeOffset < firstPointOffset && times < 0) {
          firstPoint.set(parentKey, Math.max(0, firstPointOffset + times), 'element');
        }
      }
      if (parentNode.is(lastPointNode)) {
        const lastPointOffset = lastPoint.offset;
        if (nodeOffset <= lastPointOffset && times > 0 || nodeOffset < lastPointOffset && times < 0) {
          lastPoint.set(parentKey, Math.max(0, lastPointOffset + times), 'element');
        }
      }
    }
    // The new selection might point to text nodes, try to resolve them
    $updateSelectionResolveTextNodes(selection);
  }
  function $updateSelectionResolveTextNodes(selection) {
    const anchor = selection.anchor;
    const anchorOffset = anchor.offset;
    const focus = selection.focus;
    const focusOffset = focus.offset;
    const anchorNode = anchor.getNode();
    const focusNode = focus.getNode();
    if (selection.isCollapsed()) {
      if (!$isElementNode(anchorNode)) {
        return;
      }
      const childSize = anchorNode.getChildrenSize();
      const anchorOffsetAtEnd = anchorOffset >= childSize;
      const child = anchorOffsetAtEnd ? anchorNode.getChildAtIndex(childSize - 1) : anchorNode.getChildAtIndex(anchorOffset);
      if ($isTextNode(child)) {
        let newOffset = 0;
        if (anchorOffsetAtEnd) {
          newOffset = child.getTextContentSize();
        }
        anchor.set(child.__key, newOffset, 'text');
        focus.set(child.__key, newOffset, 'text');
      }
      return;
    }
    if ($isElementNode(anchorNode)) {
      const childSize = anchorNode.getChildrenSize();
      const anchorOffsetAtEnd = anchorOffset >= childSize;
      const child = anchorOffsetAtEnd ? anchorNode.getChildAtIndex(childSize - 1) : anchorNode.getChildAtIndex(anchorOffset);
      if ($isTextNode(child)) {
        let newOffset = 0;
        if (anchorOffsetAtEnd) {
          newOffset = child.getTextContentSize();
        }
        anchor.set(child.__key, newOffset, 'text');
      }
    }
    if ($isElementNode(focusNode)) {
      const childSize = focusNode.getChildrenSize();
      const focusOffsetAtEnd = focusOffset >= childSize;
      const child = focusOffsetAtEnd ? focusNode.getChildAtIndex(childSize - 1) : focusNode.getChildAtIndex(focusOffset);
      if ($isTextNode(child)) {
        let newOffset = 0;
        if (focusOffsetAtEnd) {
          newOffset = child.getTextContentSize();
        }
        focus.set(child.__key, newOffset, 'text');
      }
    }
  }
  function applySelectionTransforms(nextEditorState, editor) {
    const prevEditorState = editor.getEditorState();
    const prevSelection = prevEditorState._selection;
    const nextSelection = nextEditorState._selection;
    if ($isRangeSelection(nextSelection)) {
      const anchor = nextSelection.anchor;
      const focus = nextSelection.focus;
      let anchorNode;
      if (anchor.type === 'text') {
        anchorNode = anchor.getNode();
        anchorNode.selectionTransform(prevSelection, nextSelection);
      }
      if (focus.type === 'text') {
        const focusNode = focus.getNode();
        if (anchorNode !== focusNode) {
          focusNode.selectionTransform(prevSelection, nextSelection);
        }
      }
    }
  }
  function moveSelectionPointToSibling(point, node, parent, prevSibling, nextSibling) {
    let siblingKey = null;
    let offset = 0;
    let type = null;
    if (prevSibling !== null) {
      siblingKey = prevSibling.__key;
      if ($isTextNode(prevSibling)) {
        offset = prevSibling.getTextContentSize();
        type = 'text';
      } else if ($isElementNode(prevSibling)) {
        offset = prevSibling.getChildrenSize();
        type = 'element';
      }
    } else {
      if (nextSibling !== null) {
        siblingKey = nextSibling.__key;
        if ($isTextNode(nextSibling)) {
          type = 'text';
        } else if ($isElementNode(nextSibling)) {
          type = 'element';
        }
      }
    }
    if (siblingKey !== null && type !== null) {
      point.set(siblingKey, offset, type);
    } else {
      offset = node.getIndexWithinParent();
      if (offset === -1) {
        // Move selection to end of parent
        offset = parent.getChildrenSize();
      }
      point.set(parent.__key, offset, 'element');
    }
  }
  function adjustPointOffsetForMergedSibling(point, isBefore, key, target, textLength) {
    if (point.type === 'text') {
      point.key = key;
      if (!isBefore) {
        point.offset += textLength;
      }
    } else if (point.offset > target.getIndexWithinParent()) {
      point.offset -= 1;
    }
  }
  function updateDOMSelection(prevSelection, nextSelection, editor, domSelection, tags, rootElement, nodeCount) {
    const anchorDOMNode = domSelection.anchorNode;
    const focusDOMNode = domSelection.focusNode;
    const anchorOffset = domSelection.anchorOffset;
    const focusOffset = domSelection.focusOffset;
    const activeElement = document.activeElement;

    // TODO: make this not hard-coded, and add another config option
    // that makes this configurable.
    if (tags.has('collaboration') && activeElement !== rootElement || activeElement !== null && isSelectionCapturedInDecoratorInput(activeElement)) {
      return;
    }
    if (!$isRangeSelection(nextSelection)) {
      // We don't remove selection if the prevSelection is null because
      // of editor.setRootElement(). If this occurs on init when the
      // editor is already focused, then this can cause the editor to
      // lose focus.
      if (prevSelection !== null && isSelectionWithinEditor(editor, anchorDOMNode, focusDOMNode)) {
        domSelection.removeAllRanges();
      }
      return;
    }
    const anchor = nextSelection.anchor;
    const focus = nextSelection.focus;
    const anchorKey = anchor.key;
    const focusKey = focus.key;
    const anchorDOM = getElementByKeyOrThrow(editor, anchorKey);
    const focusDOM = getElementByKeyOrThrow(editor, focusKey);
    const nextAnchorOffset = anchor.offset;
    const nextFocusOffset = focus.offset;
    const nextFormat = nextSelection.format;
    const nextStyle = nextSelection.style;
    const isCollapsed = nextSelection.isCollapsed();
    let nextAnchorNode = anchorDOM;
    let nextFocusNode = focusDOM;
    let anchorFormatOrStyleChanged = false;
    if (anchor.type === 'text') {
      nextAnchorNode = getDOMTextNode(anchorDOM);
      const anchorNode = anchor.getNode();
      anchorFormatOrStyleChanged = anchorNode.getFormat() !== nextFormat || anchorNode.getStyle() !== nextStyle;
    } else if ($isRangeSelection(prevSelection) && prevSelection.anchor.type === 'text') {
      anchorFormatOrStyleChanged = true;
    }
    if (focus.type === 'text') {
      nextFocusNode = getDOMTextNode(focusDOM);
    }

    // If we can't get an underlying text node for selection, then
    // we should avoid setting selection to something incorrect.
    if (nextAnchorNode === null || nextFocusNode === null) {
      return;
    }
    if (isCollapsed && (prevSelection === null || anchorFormatOrStyleChanged || $isRangeSelection(prevSelection) && (prevSelection.format !== nextFormat || prevSelection.style !== nextStyle))) {
      markCollapsedSelectionFormat(nextFormat, nextStyle, nextAnchorOffset, anchorKey, performance.now());
    }

    // Diff against the native DOM selection to ensure we don't do
    // an unnecessary selection update. We also skip this check if
    // we're moving selection to within an element, as this can
    // sometimes be problematic around scrolling.
    if (anchorOffset === nextAnchorOffset && focusOffset === nextFocusOffset && anchorDOMNode === nextAnchorNode && focusDOMNode === nextFocusNode &&
    // Badly interpreted range selection when collapsed - #1482
    !(domSelection.type === 'Range' && isCollapsed)) {
      // If the root element does not have focus, ensure it has focus
      if (activeElement === null || !rootElement.contains(activeElement)) {
        rootElement.focus({
          preventScroll: true
        });
      }
      if (anchor.type !== 'element') {
        return;
      }
    }

    // Apply the updated selection to the DOM. Note: this will trigger
    // a "selectionchange" event, although it will be asynchronous.
    try {
      domSelection.setBaseAndExtent(nextAnchorNode, nextAnchorOffset, nextFocusNode, nextFocusOffset);
    } catch (error) {
      // If we encounter an error, continue. This can sometimes
      // occur with FF and there's no good reason as to why it
      // should happen.
      {
        console.warn(error);
      }
    }
    if (!tags.has('skip-scroll-into-view') && nextSelection.isCollapsed() && rootElement !== null && rootElement === document.activeElement) {
      const selectionTarget = nextSelection instanceof RangeSelection && nextSelection.anchor.type === 'element' ? nextAnchorNode.childNodes[nextAnchorOffset] || null : domSelection.rangeCount > 0 ? domSelection.getRangeAt(0) : null;
      if (selectionTarget !== null) {
        let selectionRect;
        if (selectionTarget instanceof Text) {
          const range = document.createRange();
          range.selectNode(selectionTarget);
          selectionRect = range.getBoundingClientRect();
        } else {
          selectionRect = selectionTarget.getBoundingClientRect();
        }
        scrollIntoViewIfNeeded(editor, selectionRect, rootElement);
      }
    }
    markSelectionChangeFromDOMUpdate();
  }
  function $insertNodes(nodes) {
    let selection = $getSelection() || $getPreviousSelection();
    if (selection === null) {
      selection = $getRoot().selectEnd();
    }
    selection.insertNodes(nodes);
  }
  function $getTextContent() {
    const selection = $getSelection();
    if (selection === null) {
      return '';
    }
    return selection.getTextContent();
  }
  function $removeTextAndSplitBlock(selection) {
    let selection_ = selection;
    if (!selection.isCollapsed()) {
      selection_.removeText();
    }
    // A new selection can originate as a result of node replacement, in which case is registered via
    // $setSelection
    const newSelection = $getSelection();
    if ($isRangeSelection(newSelection)) {
      selection_ = newSelection;
    }
    if (!$isRangeSelection(selection_)) {
      throw Error(`Unexpected dirty selection to be null`);
    }
    const anchor = selection_.anchor;
    let node = anchor.getNode();
    let offset = anchor.offset;
    while (!INTERNAL_$isBlock(node)) {
      [node, offset] = $splitNodeAtPoint(node, offset);
    }
    return offset;
  }
  function $splitNodeAtPoint(node, offset) {
    const parent = node.getParent();
    if (!parent) {
      const paragraph = $createParagraphNode();
      $getRoot().append(paragraph);
      paragraph.select();
      return [$getRoot(), 0];
    }
    if ($isTextNode(node)) {
      const split = node.splitText(offset);
      if (split.length === 0) {
        return [parent, node.getIndexWithinParent()];
      }
      const x = offset === 0 ? 0 : 1;
      const index = split[0].getIndexWithinParent() + x;
      return [parent, index];
    }
    if (!$isElementNode(node) || offset === 0) {
      return [parent, node.getIndexWithinParent()];
    }
    const firstToAppend = node.getChildAtIndex(offset);
    if (firstToAppend) {
      const insertPoint = new RangeSelection($createPoint(node.__key, offset, 'element'), $createPoint(node.__key, offset, 'element'), 0, '');
      const newElement = node.insertNewAfter(insertPoint);
      if (newElement) {
        newElement.append(firstToAppend, ...firstToAppend.getNextSiblings());
      }
    }
    return [parent, node.getIndexWithinParent() + 1];
  }
  function $wrapInlineNodes(nodes) {
    // We temporarily insert the topLevelNodes into an arbitrary ElementNode,
    // since insertAfter does not work on nodes that have no parent (TO-DO: fix that).
    const virtualRoot = $createParagraphNode();
    let currentBlock = null;
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      const isLineBreakNode = $isLineBreakNode(node);
      if (isLineBreakNode || $isDecoratorNode(node) && node.isInline() || $isElementNode(node) && node.isInline() || $isTextNode(node) || node.isParentRequired()) {
        if (currentBlock === null) {
          currentBlock = node.createParentElementNode();
          virtualRoot.append(currentBlock);
          // In the case of LineBreakNode, we just need to
          // add an empty ParagraphNode to the topLevelBlocks.
          if (isLineBreakNode) {
            continue;
          }
        }
        if (currentBlock !== null) {
          currentBlock.append(node);
        }
      } else {
        virtualRoot.append(node);
        currentBlock = null;
      }
    }
    return virtualRoot;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  let activeEditorState = null;
  let activeEditor = null;
  let isReadOnlyMode = false;
  let isAttemptingToRecoverFromReconcilerError = false;
  let infiniteTransformCount = 0;
  const observerOptions = {
    characterData: true,
    childList: true,
    subtree: true
  };
  function isCurrentlyReadOnlyMode() {
    return isReadOnlyMode || activeEditorState !== null && activeEditorState._readOnly;
  }
  function errorOnReadOnly() {
    if (isReadOnlyMode) {
      {
        throw Error(`Cannot use method in read-only mode.`);
      }
    }
  }
  function errorOnInfiniteTransforms() {
    if (infiniteTransformCount > 99) {
      {
        throw Error(`One or more transforms are endlessly triggering additional transforms. May have encountered infinite recursion caused by transforms that have their preconditions too lose and/or conflict with each other.`);
      }
    }
  }
  function getActiveEditorState() {
    if (activeEditorState === null) {
      {
        throw Error(`Unable to find an active editor state. State helpers or node methods can only be used synchronously during the callback of editor.update(), editor.read(), or editorState.read().${collectBuildInformation()}`);
      }
    }
    return activeEditorState;
  }
  function getActiveEditor() {
    if (activeEditor === null) {
      {
        throw Error(`Unable to find an active editor. This method can only be used synchronously during the callback of editor.update() or editor.read().${collectBuildInformation()}`);
      }
    }
    return activeEditor;
  }
  function collectBuildInformation() {
    let compatibleEditors = 0;
    const incompatibleEditors = new Set();
    const thisVersion = LexicalEditor.version;
    if (typeof window !== 'undefined') {
      for (const node of document.querySelectorAll('[contenteditable]')) {
        const editor = getEditorPropertyFromDOMNode(node);
        if (isLexicalEditor(editor)) {
          compatibleEditors++;
        } else if (editor) {
          let version = String(editor.constructor.version || '<0.17.1');
          if (version === thisVersion) {
            version += ' (separately built, likely a bundler configuration issue)';
          }
          incompatibleEditors.add(version);
        }
      }
    }
    let output = ` Detected on the page: ${compatibleEditors} compatible editor(s) with version ${thisVersion}`;
    if (incompatibleEditors.size) {
      output += ` and incompatible editors with versions ${Array.from(incompatibleEditors).join(', ')}`;
    }
    return output;
  }
  function internalGetActiveEditor() {
    return activeEditor;
  }
  function internalGetActiveEditorState() {
    return activeEditorState;
  }
  function $applyTransforms(editor, node, transformsCache) {
    const type = node.__type;
    const registeredNode = getRegisteredNodeOrThrow(editor, type);
    let transformsArr = transformsCache.get(type);
    if (transformsArr === undefined) {
      transformsArr = Array.from(registeredNode.transforms);
      transformsCache.set(type, transformsArr);
    }
    const transformsArrLength = transformsArr.length;
    for (let i = 0; i < transformsArrLength; i++) {
      transformsArr[i](node);
      if (!node.isAttached()) {
        break;
      }
    }
  }
  function $isNodeValidForTransform(node, compositionKey) {
    return node !== undefined &&
    // We don't want to transform nodes being composed
    node.__key !== compositionKey && node.isAttached();
  }
  function $normalizeAllDirtyTextNodes(editorState, editor) {
    const dirtyLeaves = editor._dirtyLeaves;
    const nodeMap = editorState._nodeMap;
    for (const nodeKey of dirtyLeaves) {
      const node = nodeMap.get(nodeKey);
      if ($isTextNode(node) && node.isAttached() && node.isSimpleText() && !node.isUnmergeable()) {
        $normalizeTextNode(node);
      }
    }
  }

  /**
   * Transform heuristic:
   * 1. We transform leaves first. If transforms generate additional dirty nodes we repeat step 1.
   * The reasoning behind this is that marking a leaf as dirty marks all its parent elements as dirty too.
   * 2. We transform elements. If element transforms generate additional dirty nodes we repeat step 1.
   * If element transforms only generate additional dirty elements we only repeat step 2.
   *
   * Note that to keep track of newly dirty nodes and subtrees we leverage the editor._dirtyNodes and
   * editor._subtrees which we reset in every loop.
   */
  function $applyAllTransforms(editorState, editor) {
    const dirtyLeaves = editor._dirtyLeaves;
    const dirtyElements = editor._dirtyElements;
    const nodeMap = editorState._nodeMap;
    const compositionKey = $getCompositionKey();
    const transformsCache = new Map();
    let untransformedDirtyLeaves = dirtyLeaves;
    let untransformedDirtyLeavesLength = untransformedDirtyLeaves.size;
    let untransformedDirtyElements = dirtyElements;
    let untransformedDirtyElementsLength = untransformedDirtyElements.size;
    while (untransformedDirtyLeavesLength > 0 || untransformedDirtyElementsLength > 0) {
      if (untransformedDirtyLeavesLength > 0) {
        // We leverage editor._dirtyLeaves to track the new dirty leaves after the transforms
        editor._dirtyLeaves = new Set();
        for (const nodeKey of untransformedDirtyLeaves) {
          const node = nodeMap.get(nodeKey);
          if ($isTextNode(node) && node.isAttached() && node.isSimpleText() && !node.isUnmergeable()) {
            $normalizeTextNode(node);
          }
          if (node !== undefined && $isNodeValidForTransform(node, compositionKey)) {
            $applyTransforms(editor, node, transformsCache);
          }
          dirtyLeaves.add(nodeKey);
        }
        untransformedDirtyLeaves = editor._dirtyLeaves;
        untransformedDirtyLeavesLength = untransformedDirtyLeaves.size;

        // We want to prioritize node transforms over element transforms
        if (untransformedDirtyLeavesLength > 0) {
          infiniteTransformCount++;
          continue;
        }
      }

      // All dirty leaves have been processed. Let's do elements!
      // We have previously processed dirty leaves, so let's restart the editor leaves Set to track
      // new ones caused by element transforms
      editor._dirtyLeaves = new Set();
      editor._dirtyElements = new Map();
      for (const currentUntransformedDirtyElement of untransformedDirtyElements) {
        const nodeKey = currentUntransformedDirtyElement[0];
        const intentionallyMarkedAsDirty = currentUntransformedDirtyElement[1];
        if (nodeKey !== 'root' && !intentionallyMarkedAsDirty) {
          continue;
        }
        const node = nodeMap.get(nodeKey);
        if (node !== undefined && $isNodeValidForTransform(node, compositionKey)) {
          $applyTransforms(editor, node, transformsCache);
        }
        dirtyElements.set(nodeKey, intentionallyMarkedAsDirty);
      }
      untransformedDirtyLeaves = editor._dirtyLeaves;
      untransformedDirtyLeavesLength = untransformedDirtyLeaves.size;
      untransformedDirtyElements = editor._dirtyElements;
      untransformedDirtyElementsLength = untransformedDirtyElements.size;
      infiniteTransformCount++;
    }
    editor._dirtyLeaves = dirtyLeaves;
    editor._dirtyElements = dirtyElements;
  }
  function $parseSerializedNode(serializedNode) {
    const internalSerializedNode = serializedNode;
    return $parseSerializedNodeImpl(internalSerializedNode, getActiveEditor()._nodes);
  }
  function $parseSerializedNodeImpl(serializedNode, registeredNodes) {
    const type = serializedNode.type;
    const registeredNode = registeredNodes.get(type);
    if (registeredNode === undefined) {
      {
        throw Error(`parseEditorState: type "${type}" + not found`);
      }
    }
    const nodeClass = registeredNode.klass;
    if (serializedNode.type !== nodeClass.getType()) {
      {
        throw Error(`LexicalNode: Node ${nodeClass.name} does not implement .importJSON().`);
      }
    }
    const node = nodeClass.importJSON(serializedNode);
    const children = serializedNode.children;
    if ($isElementNode(node) && Array.isArray(children)) {
      for (let i = 0; i < children.length; i++) {
        const serializedJSONChildNode = children[i];
        const childNode = $parseSerializedNodeImpl(serializedJSONChildNode, registeredNodes);
        node.append(childNode);
      }
    }
    return node;
  }
  function parseEditorState(serializedEditorState, editor, updateFn) {
    const editorState = createEmptyEditorState();
    const previousActiveEditorState = activeEditorState;
    const previousReadOnlyMode = isReadOnlyMode;
    const previousActiveEditor = activeEditor;
    const previousDirtyElements = editor._dirtyElements;
    const previousDirtyLeaves = editor._dirtyLeaves;
    const previousCloneNotNeeded = editor._cloneNotNeeded;
    const previousDirtyType = editor._dirtyType;
    editor._dirtyElements = new Map();
    editor._dirtyLeaves = new Set();
    editor._cloneNotNeeded = new Set();
    editor._dirtyType = 0;
    activeEditorState = editorState;
    isReadOnlyMode = false;
    activeEditor = editor;
    try {
      const registeredNodes = editor._nodes;
      const serializedNode = serializedEditorState.root;
      $parseSerializedNodeImpl(serializedNode, registeredNodes);
      if (updateFn) {
        updateFn();
      }

      // Make the editorState immutable
      editorState._readOnly = true;
      {
        handleDEVOnlyPendingUpdateGuarantees(editorState);
      }
    } catch (error) {
      if (error instanceof Error) {
        editor._onError(error);
      }
    } finally {
      editor._dirtyElements = previousDirtyElements;
      editor._dirtyLeaves = previousDirtyLeaves;
      editor._cloneNotNeeded = previousCloneNotNeeded;
      editor._dirtyType = previousDirtyType;
      activeEditorState = previousActiveEditorState;
      isReadOnlyMode = previousReadOnlyMode;
      activeEditor = previousActiveEditor;
    }
    return editorState;
  }

  // This technically isn't an update but given we need
  // exposure to the module's active bindings, we have this
  // function here

  function readEditorState(editor, editorState, callbackFn) {
    const previousActiveEditorState = activeEditorState;
    const previousReadOnlyMode = isReadOnlyMode;
    const previousActiveEditor = activeEditor;
    activeEditorState = editorState;
    isReadOnlyMode = true;
    activeEditor = editor;
    try {
      return callbackFn();
    } finally {
      activeEditorState = previousActiveEditorState;
      isReadOnlyMode = previousReadOnlyMode;
      activeEditor = previousActiveEditor;
    }
  }
  function handleDEVOnlyPendingUpdateGuarantees(pendingEditorState) {
    // Given we can't Object.freeze the nodeMap as it's a Map,
    // we instead replace its set, clear and delete methods.
    const nodeMap = pendingEditorState._nodeMap;
    nodeMap.set = () => {
      throw new Error('Cannot call set() on a frozen Lexical node map');
    };
    nodeMap.clear = () => {
      throw new Error('Cannot call clear() on a frozen Lexical node map');
    };
    nodeMap.delete = () => {
      throw new Error('Cannot call delete() on a frozen Lexical node map');
    };
  }
  function $commitPendingUpdates(editor, recoveryEditorState) {
    const pendingEditorState = editor._pendingEditorState;
    const rootElement = editor._rootElement;
    const shouldSkipDOM = editor._headless || rootElement === null;
    if (pendingEditorState === null) {
      return;
    }

    // ======
    // Reconciliation has started.
    // ======

    const currentEditorState = editor._editorState;
    const currentSelection = currentEditorState._selection;
    const pendingSelection = pendingEditorState._selection;
    const needsUpdate = editor._dirtyType !== NO_DIRTY_NODES;
    const previousActiveEditorState = activeEditorState;
    const previousReadOnlyMode = isReadOnlyMode;
    const previousActiveEditor = activeEditor;
    const previouslyUpdating = editor._updating;
    const observer = editor._observer;
    let mutatedNodes = null;
    editor._pendingEditorState = null;
    editor._editorState = pendingEditorState;
    if (!shouldSkipDOM && needsUpdate && observer !== null) {
      activeEditor = editor;
      activeEditorState = pendingEditorState;
      isReadOnlyMode = false;
      // We don't want updates to sync block the reconciliation.
      editor._updating = true;
      try {
        const dirtyType = editor._dirtyType;
        const dirtyElements = editor._dirtyElements;
        const dirtyLeaves = editor._dirtyLeaves;
        observer.disconnect();
        mutatedNodes = $reconcileRoot(currentEditorState, pendingEditorState, editor, dirtyType, dirtyElements, dirtyLeaves);
      } catch (error) {
        // Report errors
        if (error instanceof Error) {
          editor._onError(error);
        }

        // Reset editor and restore incoming editor state to the DOM
        if (!isAttemptingToRecoverFromReconcilerError) {
          resetEditor(editor, null, rootElement, pendingEditorState);
          initMutationObserver(editor);
          editor._dirtyType = FULL_RECONCILE;
          isAttemptingToRecoverFromReconcilerError = true;
          $commitPendingUpdates(editor, currentEditorState);
          isAttemptingToRecoverFromReconcilerError = false;
        } else {
          // To avoid a possible situation of infinite loops, lets throw
          throw error;
        }
        return;
      } finally {
        observer.observe(rootElement, observerOptions);
        editor._updating = previouslyUpdating;
        activeEditorState = previousActiveEditorState;
        isReadOnlyMode = previousReadOnlyMode;
        activeEditor = previousActiveEditor;
      }
    }
    if (!pendingEditorState._readOnly) {
      pendingEditorState._readOnly = true;
      {
        handleDEVOnlyPendingUpdateGuarantees(pendingEditorState);
        if ($isRangeSelection(pendingSelection)) {
          Object.freeze(pendingSelection.anchor);
          Object.freeze(pendingSelection.focus);
        }
        Object.freeze(pendingSelection);
      }
    }
    const dirtyLeaves = editor._dirtyLeaves;
    const dirtyElements = editor._dirtyElements;
    const normalizedNodes = editor._normalizedNodes;
    const tags = editor._updateTags;
    const deferred = editor._deferred;
    if (needsUpdate) {
      editor._dirtyType = NO_DIRTY_NODES;
      editor._cloneNotNeeded.clear();
      editor._dirtyLeaves = new Set();
      editor._dirtyElements = new Map();
      editor._normalizedNodes = new Set();
      editor._updateTags = new Set();
    }
    $garbageCollectDetachedDecorators(editor, pendingEditorState);

    // ======
    // Reconciliation has finished. Now update selection and trigger listeners.
    // ======

    const domSelection = shouldSkipDOM ? null : getDOMSelection(editor._window);

    // Attempt to update the DOM selection, including focusing of the root element,
    // and scroll into view if needed.
    if (editor._editable &&
    // domSelection will be null in headless
    domSelection !== null && (needsUpdate || pendingSelection === null || pendingSelection.dirty)) {
      activeEditor = editor;
      activeEditorState = pendingEditorState;
      try {
        if (observer !== null) {
          observer.disconnect();
        }
        if (needsUpdate || pendingSelection === null || pendingSelection.dirty) {
          const blockCursorElement = editor._blockCursorElement;
          if (blockCursorElement !== null) {
            removeDOMBlockCursorElement(blockCursorElement, editor, rootElement);
          }
          updateDOMSelection(currentSelection, pendingSelection, editor, domSelection, tags, rootElement);
        }
        updateDOMBlockCursorElement(editor, rootElement, pendingSelection);
        if (observer !== null) {
          observer.observe(rootElement, observerOptions);
        }
      } finally {
        activeEditor = previousActiveEditor;
        activeEditorState = previousActiveEditorState;
      }
    }
    if (mutatedNodes !== null) {
      triggerMutationListeners(editor, mutatedNodes, tags, dirtyLeaves, currentEditorState);
    }
    if (!$isRangeSelection(pendingSelection) && pendingSelection !== null && (currentSelection === null || !currentSelection.is(pendingSelection))) {
      editor.dispatchCommand(SELECTION_CHANGE_COMMAND, undefined);
    }
    /**
     * Capture pendingDecorators after garbage collecting detached decorators
     */
    const pendingDecorators = editor._pendingDecorators;
    if (pendingDecorators !== null) {
      editor._decorators = pendingDecorators;
      editor._pendingDecorators = null;
      triggerListeners('decorator', editor, true, pendingDecorators);
    }

    // If reconciler fails, we reset whole editor (so current editor state becomes empty)
    // and attempt to re-render pendingEditorState. If that goes through we trigger
    // listeners, but instead use recoverEditorState which is current editor state before reset
    // This specifically important for collab that relies on prevEditorState from update
    // listener to calculate delta of changed nodes/properties
    triggerTextContentListeners(editor, recoveryEditorState || currentEditorState, pendingEditorState);
    triggerListeners('update', editor, true, {
      dirtyElements,
      dirtyLeaves,
      editorState: pendingEditorState,
      normalizedNodes,
      prevEditorState: recoveryEditorState || currentEditorState,
      tags
    });
    triggerDeferredUpdateCallbacks(editor, deferred);
    $triggerEnqueuedUpdates(editor);
  }
  function triggerTextContentListeners(editor, currentEditorState, pendingEditorState) {
    const currentTextContent = getEditorStateTextContent(currentEditorState);
    const latestTextContent = getEditorStateTextContent(pendingEditorState);
    if (currentTextContent !== latestTextContent) {
      triggerListeners('textcontent', editor, true, latestTextContent);
    }
  }
  function triggerMutationListeners(editor, mutatedNodes, updateTags, dirtyLeaves, prevEditorState) {
    const listeners = Array.from(editor._listeners.mutation);
    const listenersLength = listeners.length;
    for (let i = 0; i < listenersLength; i++) {
      const [listener, klass] = listeners[i];
      const mutatedNodesByType = mutatedNodes.get(klass);
      if (mutatedNodesByType !== undefined) {
        listener(mutatedNodesByType, {
          dirtyLeaves,
          prevEditorState,
          updateTags
        });
      }
    }
  }
  function triggerListeners(type, editor, isCurrentlyEnqueuingUpdates, ...payload) {
    const previouslyUpdating = editor._updating;
    editor._updating = isCurrentlyEnqueuingUpdates;
    try {
      const listeners = Array.from(editor._listeners[type]);
      for (let i = 0; i < listeners.length; i++) {
        // @ts-ignore
        listeners[i].apply(null, payload);
      }
    } finally {
      editor._updating = previouslyUpdating;
    }
  }
  function triggerCommandListeners(editor, type, payload) {
    if (editor._updating === false || activeEditor !== editor) {
      let returnVal = false;
      editor.update(() => {
        returnVal = triggerCommandListeners(editor, type, payload);
      });
      return returnVal;
    }
    const editors = getEditorsToPropagate(editor);
    for (let i = 4; i >= 0; i--) {
      for (let e = 0; e < editors.length; e++) {
        const currentEditor = editors[e];
        const commandListeners = currentEditor._commands;
        const listenerInPriorityOrder = commandListeners.get(type);
        if (listenerInPriorityOrder !== undefined) {
          const listenersSet = listenerInPriorityOrder[i];
          if (listenersSet !== undefined) {
            const listeners = Array.from(listenersSet);
            const listenersLength = listeners.length;
            for (let j = 0; j < listenersLength; j++) {
              if (listeners[j](payload, editor) === true) {
                return true;
              }
            }
          }
        }
      }
    }
    return false;
  }
  function $triggerEnqueuedUpdates(editor) {
    const queuedUpdates = editor._updates;
    if (queuedUpdates.length !== 0) {
      const queuedUpdate = queuedUpdates.shift();
      if (queuedUpdate) {
        const [updateFn, options] = queuedUpdate;
        $beginUpdate(editor, updateFn, options);
      }
    }
  }
  function triggerDeferredUpdateCallbacks(editor, deferred) {
    editor._deferred = [];
    if (deferred.length !== 0) {
      const previouslyUpdating = editor._updating;
      editor._updating = true;
      try {
        for (let i = 0; i < deferred.length; i++) {
          deferred[i]();
        }
      } finally {
        editor._updating = previouslyUpdating;
      }
    }
  }
  function processNestedUpdates(editor, initialSkipTransforms) {
    const queuedUpdates = editor._updates;
    let skipTransforms = initialSkipTransforms || false;

    // Updates might grow as we process them, we so we'll need
    // to handle each update as we go until the updates array is
    // empty.
    while (queuedUpdates.length !== 0) {
      const queuedUpdate = queuedUpdates.shift();
      if (queuedUpdate) {
        const [nextUpdateFn, options] = queuedUpdate;
        let onUpdate;
        let tag;
        if (options !== undefined) {
          onUpdate = options.onUpdate;
          tag = options.tag;
          if (options.skipTransforms) {
            skipTransforms = true;
          }
          if (options.discrete) {
            const pendingEditorState = editor._pendingEditorState;
            if (!(pendingEditorState !== null)) {
              throw Error(`Unexpected empty pending editor state on discrete nested update`);
            }
            pendingEditorState._flushSync = true;
          }
          if (onUpdate) {
            editor._deferred.push(onUpdate);
          }
          if (tag) {
            editor._updateTags.add(tag);
          }
        }
        nextUpdateFn();
      }
    }
    return skipTransforms;
  }
  function $beginUpdate(editor, updateFn, options) {
    const updateTags = editor._updateTags;
    let onUpdate;
    let tag;
    let skipTransforms = false;
    let discrete = false;
    if (options !== undefined) {
      onUpdate = options.onUpdate;
      tag = options.tag;
      if (tag != null) {
        updateTags.add(tag);
      }
      skipTransforms = options.skipTransforms || false;
      discrete = options.discrete || false;
    }
    if (onUpdate) {
      editor._deferred.push(onUpdate);
    }
    const currentEditorState = editor._editorState;
    let pendingEditorState = editor._pendingEditorState;
    let editorStateWasCloned = false;
    if (pendingEditorState === null || pendingEditorState._readOnly) {
      pendingEditorState = editor._pendingEditorState = cloneEditorState(pendingEditorState || currentEditorState);
      editorStateWasCloned = true;
    }
    pendingEditorState._flushSync = discrete;
    const previousActiveEditorState = activeEditorState;
    const previousReadOnlyMode = isReadOnlyMode;
    const previousActiveEditor = activeEditor;
    const previouslyUpdating = editor._updating;
    activeEditorState = pendingEditorState;
    isReadOnlyMode = false;
    editor._updating = true;
    activeEditor = editor;
    try {
      if (editorStateWasCloned) {
        if (editor._headless) {
          if (currentEditorState._selection !== null) {
            pendingEditorState._selection = currentEditorState._selection.clone();
          }
        } else {
          pendingEditorState._selection = $internalCreateSelection(editor);
        }
      }
      const startingCompositionKey = editor._compositionKey;
      updateFn();
      skipTransforms = processNestedUpdates(editor, skipTransforms);
      applySelectionTransforms(pendingEditorState, editor);
      if (editor._dirtyType !== NO_DIRTY_NODES) {
        if (skipTransforms) {
          $normalizeAllDirtyTextNodes(pendingEditorState, editor);
        } else {
          $applyAllTransforms(pendingEditorState, editor);
        }
        processNestedUpdates(editor);
        $garbageCollectDetachedNodes(currentEditorState, pendingEditorState, editor._dirtyLeaves, editor._dirtyElements);
      }
      const endingCompositionKey = editor._compositionKey;
      if (startingCompositionKey !== endingCompositionKey) {
        pendingEditorState._flushSync = true;
      }
      const pendingSelection = pendingEditorState._selection;
      if ($isRangeSelection(pendingSelection)) {
        const pendingNodeMap = pendingEditorState._nodeMap;
        const anchorKey = pendingSelection.anchor.key;
        const focusKey = pendingSelection.focus.key;
        if (pendingNodeMap.get(anchorKey) === undefined || pendingNodeMap.get(focusKey) === undefined) {
          {
            throw Error(`updateEditor: selection has been lost because the previously selected nodes have been removed and selection wasn't moved to another node. Ensure selection changes after removing/replacing a selected node.`);
          }
        }
      } else if ($isNodeSelection(pendingSelection)) {
        // TODO: we should also validate node selection?
        if (pendingSelection._nodes.size === 0) {
          pendingEditorState._selection = null;
        }
      }
    } catch (error) {
      // Report errors
      if (error instanceof Error) {
        editor._onError(error);
      }

      // Restore existing editor state to the DOM
      editor._pendingEditorState = currentEditorState;
      editor._dirtyType = FULL_RECONCILE;
      editor._cloneNotNeeded.clear();
      editor._dirtyLeaves = new Set();
      editor._dirtyElements.clear();
      $commitPendingUpdates(editor);
      return;
    } finally {
      activeEditorState = previousActiveEditorState;
      isReadOnlyMode = previousReadOnlyMode;
      activeEditor = previousActiveEditor;
      editor._updating = previouslyUpdating;
      infiniteTransformCount = 0;
    }
    const shouldUpdate = editor._dirtyType !== NO_DIRTY_NODES || editorStateHasDirtySelection(pendingEditorState, editor);
    if (shouldUpdate) {
      if (pendingEditorState._flushSync) {
        pendingEditorState._flushSync = false;
        $commitPendingUpdates(editor);
      } else if (editorStateWasCloned) {
        scheduleMicroTask(() => {
          $commitPendingUpdates(editor);
        });
      }
    } else {
      pendingEditorState._flushSync = false;
      if (editorStateWasCloned) {
        updateTags.clear();
        editor._deferred = [];
        editor._pendingEditorState = null;
      }
    }
  }
  function updateEditor(editor, updateFn, options) {
    if (editor._updating) {
      editor._updates.push([updateFn, options]);
    } else {
      $beginUpdate(editor, updateFn, options);
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging

  /** @noInheritDoc */
  // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
  class ElementNode extends LexicalNode {
    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    constructor(key) {
      super(key);
      this.__first = null;
      this.__last = null;
      this.__size = 0;
      this.__format = 0;
      this.__style = '';
      this.__indent = 0;
      this.__dir = null;
    }
    afterCloneFrom(prevNode) {
      super.afterCloneFrom(prevNode);
      this.__first = prevNode.__first;
      this.__last = prevNode.__last;
      this.__size = prevNode.__size;
      this.__indent = prevNode.__indent;
      this.__format = prevNode.__format;
      this.__style = prevNode.__style;
      this.__dir = prevNode.__dir;
    }
    getFormat() {
      const self = this.getLatest();
      return self.__format;
    }
    getFormatType() {
      const format = this.getFormat();
      return ELEMENT_FORMAT_TO_TYPE[format] || '';
    }
    getStyle() {
      const self = this.getLatest();
      return self.__style;
    }
    getIndent() {
      const self = this.getLatest();
      return self.__indent;
    }
    getChildren() {
      const children = [];
      let child = this.getFirstChild();
      while (child !== null) {
        children.push(child);
        child = child.getNextSibling();
      }
      return children;
    }
    getChildrenKeys() {
      const children = [];
      let child = this.getFirstChild();
      while (child !== null) {
        children.push(child.__key);
        child = child.getNextSibling();
      }
      return children;
    }
    getChildrenSize() {
      const self = this.getLatest();
      return self.__size;
    }
    isEmpty() {
      return this.getChildrenSize() === 0;
    }
    isDirty() {
      const editor = getActiveEditor();
      const dirtyElements = editor._dirtyElements;
      return dirtyElements !== null && dirtyElements.has(this.__key);
    }
    isLastChild() {
      const self = this.getLatest();
      const parentLastChild = this.getParentOrThrow().getLastChild();
      return parentLastChild !== null && parentLastChild.is(self);
    }
    getAllTextNodes() {
      const textNodes = [];
      let child = this.getFirstChild();
      while (child !== null) {
        if ($isTextNode(child)) {
          textNodes.push(child);
        }
        if ($isElementNode(child)) {
          const subChildrenNodes = child.getAllTextNodes();
          textNodes.push(...subChildrenNodes);
        }
        child = child.getNextSibling();
      }
      return textNodes;
    }
    getFirstDescendant() {
      let node = this.getFirstChild();
      while ($isElementNode(node)) {
        const child = node.getFirstChild();
        if (child === null) {
          break;
        }
        node = child;
      }
      return node;
    }
    getLastDescendant() {
      let node = this.getLastChild();
      while ($isElementNode(node)) {
        const child = node.getLastChild();
        if (child === null) {
          break;
        }
        node = child;
      }
      return node;
    }
    getDescendantByIndex(index) {
      const children = this.getChildren();
      const childrenLength = children.length;
      // For non-empty element nodes, we resolve its descendant
      // (either a leaf node or the bottom-most element)
      if (index >= childrenLength) {
        const resolvedNode = children[childrenLength - 1];
        return $isElementNode(resolvedNode) && resolvedNode.getLastDescendant() || resolvedNode || null;
      }
      const resolvedNode = children[index];
      return $isElementNode(resolvedNode) && resolvedNode.getFirstDescendant() || resolvedNode || null;
    }
    getFirstChild() {
      const self = this.getLatest();
      const firstKey = self.__first;
      return firstKey === null ? null : $getNodeByKey(firstKey);
    }
    getFirstChildOrThrow() {
      const firstChild = this.getFirstChild();
      if (firstChild === null) {
        {
          throw Error(`Expected node ${this.__key} to have a first child.`);
        }
      }
      return firstChild;
    }
    getLastChild() {
      const self = this.getLatest();
      const lastKey = self.__last;
      return lastKey === null ? null : $getNodeByKey(lastKey);
    }
    getLastChildOrThrow() {
      const lastChild = this.getLastChild();
      if (lastChild === null) {
        {
          throw Error(`Expected node ${this.__key} to have a last child.`);
        }
      }
      return lastChild;
    }
    getChildAtIndex(index) {
      const size = this.getChildrenSize();
      let node;
      let i;
      if (index < size / 2) {
        node = this.getFirstChild();
        i = 0;
        while (node !== null && i <= index) {
          if (i === index) {
            return node;
          }
          node = node.getNextSibling();
          i++;
        }
        return null;
      }
      node = this.getLastChild();
      i = size - 1;
      while (node !== null && i >= index) {
        if (i === index) {
          return node;
        }
        node = node.getPreviousSibling();
        i--;
      }
      return null;
    }
    getTextContent() {
      let textContent = '';
      const children = this.getChildren();
      const childrenLength = children.length;
      for (let i = 0; i < childrenLength; i++) {
        const child = children[i];
        textContent += child.getTextContent();
        if ($isElementNode(child) && i !== childrenLength - 1 && !child.isInline()) {
          textContent += DOUBLE_LINE_BREAK;
        }
      }
      return textContent;
    }
    getTextContentSize() {
      let textContentSize = 0;
      const children = this.getChildren();
      const childrenLength = children.length;
      for (let i = 0; i < childrenLength; i++) {
        const child = children[i];
        textContentSize += child.getTextContentSize();
        if ($isElementNode(child) && i !== childrenLength - 1 && !child.isInline()) {
          textContentSize += DOUBLE_LINE_BREAK.length;
        }
      }
      return textContentSize;
    }
    getDirection() {
      const self = this.getLatest();
      return self.__dir;
    }
    hasFormat(type) {
      if (type !== '') {
        const formatFlag = ELEMENT_TYPE_TO_FORMAT[type];
        return (this.getFormat() & formatFlag) !== 0;
      }
      return false;
    }

    // Mutators

    select(_anchorOffset, _focusOffset) {
      errorOnReadOnly();
      const selection = $getSelection();
      let anchorOffset = _anchorOffset;
      let focusOffset = _focusOffset;
      const childrenCount = this.getChildrenSize();
      if (!this.canBeEmpty()) {
        if (_anchorOffset === 0 && _focusOffset === 0) {
          const firstChild = this.getFirstChild();
          if ($isTextNode(firstChild) || $isElementNode(firstChild)) {
            return firstChild.select(0, 0);
          }
        } else if ((_anchorOffset === undefined || _anchorOffset === childrenCount) && (_focusOffset === undefined || _focusOffset === childrenCount)) {
          const lastChild = this.getLastChild();
          if ($isTextNode(lastChild) || $isElementNode(lastChild)) {
            return lastChild.select();
          }
        }
      }
      if (anchorOffset === undefined) {
        anchorOffset = childrenCount;
      }
      if (focusOffset === undefined) {
        focusOffset = childrenCount;
      }
      const key = this.__key;
      if (!$isRangeSelection(selection)) {
        return $internalMakeRangeSelection(key, anchorOffset, key, focusOffset, 'element', 'element');
      } else {
        selection.anchor.set(key, anchorOffset, 'element');
        selection.focus.set(key, focusOffset, 'element');
        selection.dirty = true;
      }
      return selection;
    }
    selectStart() {
      const firstNode = this.getFirstDescendant();
      return firstNode ? firstNode.selectStart() : this.select();
    }
    selectEnd() {
      const lastNode = this.getLastDescendant();
      return lastNode ? lastNode.selectEnd() : this.select();
    }
    clear() {
      const writableSelf = this.getWritable();
      const children = this.getChildren();
      children.forEach(child => child.remove());
      return writableSelf;
    }
    append(...nodesToAppend) {
      return this.splice(this.getChildrenSize(), 0, nodesToAppend);
    }
    setDirection(direction) {
      const self = this.getWritable();
      self.__dir = direction;
      return self;
    }
    setFormat(type) {
      const self = this.getWritable();
      self.__format = type !== '' ? ELEMENT_TYPE_TO_FORMAT[type] : 0;
      return this;
    }
    setStyle(style) {
      const self = this.getWritable();
      self.__style = style || '';
      return this;
    }
    setIndent(indentLevel) {
      const self = this.getWritable();
      self.__indent = indentLevel;
      return this;
    }
    splice(start, deleteCount, nodesToInsert) {
      const nodesToInsertLength = nodesToInsert.length;
      const oldSize = this.getChildrenSize();
      const writableSelf = this.getWritable();
      const writableSelfKey = writableSelf.__key;
      const nodesToInsertKeys = [];
      const nodesToRemoveKeys = [];
      const nodeAfterRange = this.getChildAtIndex(start + deleteCount);
      let nodeBeforeRange = null;
      let newSize = oldSize - deleteCount + nodesToInsertLength;
      if (start !== 0) {
        if (start === oldSize) {
          nodeBeforeRange = this.getLastChild();
        } else {
          const node = this.getChildAtIndex(start);
          if (node !== null) {
            nodeBeforeRange = node.getPreviousSibling();
          }
        }
      }
      if (deleteCount > 0) {
        let nodeToDelete = nodeBeforeRange === null ? this.getFirstChild() : nodeBeforeRange.getNextSibling();
        for (let i = 0; i < deleteCount; i++) {
          if (nodeToDelete === null) {
            {
              throw Error(`splice: sibling not found`);
            }
          }
          const nextSibling = nodeToDelete.getNextSibling();
          const nodeKeyToDelete = nodeToDelete.__key;
          const writableNodeToDelete = nodeToDelete.getWritable();
          removeFromParent(writableNodeToDelete);
          nodesToRemoveKeys.push(nodeKeyToDelete);
          nodeToDelete = nextSibling;
        }
      }
      let prevNode = nodeBeforeRange;
      for (let i = 0; i < nodesToInsertLength; i++) {
        const nodeToInsert = nodesToInsert[i];
        if (prevNode !== null && nodeToInsert.is(prevNode)) {
          nodeBeforeRange = prevNode = prevNode.getPreviousSibling();
        }
        const writableNodeToInsert = nodeToInsert.getWritable();
        if (writableNodeToInsert.__parent === writableSelfKey) {
          newSize--;
        }
        removeFromParent(writableNodeToInsert);
        const nodeKeyToInsert = nodeToInsert.__key;
        if (prevNode === null) {
          writableSelf.__first = nodeKeyToInsert;
          writableNodeToInsert.__prev = null;
        } else {
          const writablePrevNode = prevNode.getWritable();
          writablePrevNode.__next = nodeKeyToInsert;
          writableNodeToInsert.__prev = writablePrevNode.__key;
        }
        if (nodeToInsert.__key === writableSelfKey) {
          {
            throw Error(`append: attempting to append self`);
          }
        }
        // Set child parent to self
        writableNodeToInsert.__parent = writableSelfKey;
        nodesToInsertKeys.push(nodeKeyToInsert);
        prevNode = nodeToInsert;
      }
      if (start + deleteCount === oldSize) {
        if (prevNode !== null) {
          const writablePrevNode = prevNode.getWritable();
          writablePrevNode.__next = null;
          writableSelf.__last = prevNode.__key;
        }
      } else if (nodeAfterRange !== null) {
        const writableNodeAfterRange = nodeAfterRange.getWritable();
        if (prevNode !== null) {
          const writablePrevNode = prevNode.getWritable();
          writableNodeAfterRange.__prev = prevNode.__key;
          writablePrevNode.__next = nodeAfterRange.__key;
        } else {
          writableNodeAfterRange.__prev = null;
        }
      }
      writableSelf.__size = newSize;

      // In case of deletion we need to adjust selection, unlink removed nodes
      // and clean up node itself if it becomes empty. None of these needed
      // for insertion-only cases
      if (nodesToRemoveKeys.length) {
        // Adjusting selection, in case node that was anchor/focus will be deleted
        const selection = $getSelection();
        if ($isRangeSelection(selection)) {
          const nodesToRemoveKeySet = new Set(nodesToRemoveKeys);
          const nodesToInsertKeySet = new Set(nodesToInsertKeys);
          const {
            anchor,
            focus
          } = selection;
          if (isPointRemoved(anchor, nodesToRemoveKeySet, nodesToInsertKeySet)) {
            moveSelectionPointToSibling(anchor, anchor.getNode(), this, nodeBeforeRange, nodeAfterRange);
          }
          if (isPointRemoved(focus, nodesToRemoveKeySet, nodesToInsertKeySet)) {
            moveSelectionPointToSibling(focus, focus.getNode(), this, nodeBeforeRange, nodeAfterRange);
          }
          // Cleanup if node can't be empty
          if (newSize === 0 && !this.canBeEmpty() && !$isRootOrShadowRoot(this)) {
            this.remove();
          }
        }
      }
      return writableSelf;
    }
    // JSON serialization
    exportJSON() {
      return {
        children: [],
        direction: this.getDirection(),
        format: this.getFormatType(),
        indent: this.getIndent(),
        type: 'element',
        version: 1
      };
    }
    // These are intended to be extends for specific element heuristics.
    insertNewAfter(selection, restoreSelection) {
      return null;
    }
    canIndent() {
      return true;
    }
    /*
     * This method controls the behavior of a the node during backwards
     * deletion (i.e., backspace) when selection is at the beginning of
     * the node (offset 0)
     */
    collapseAtStart(selection) {
      return false;
    }
    excludeFromCopy(destination) {
      return false;
    }
    /** @deprecated @internal */
    canReplaceWith(replacement) {
      return true;
    }
    /** @deprecated @internal */
    canInsertAfter(node) {
      return true;
    }
    canBeEmpty() {
      return true;
    }
    canInsertTextBefore() {
      return true;
    }
    canInsertTextAfter() {
      return true;
    }
    isInline() {
      return false;
    }
    // A shadow root is a Node that behaves like RootNode. The shadow root (and RootNode) mark the
    // end of the hiercharchy, most implementations should treat it as there's nothing (upwards)
    // beyond this point. For example, node.getTopLevelElement(), when performed inside a TableCellNode
    // will return the immediate first child underneath TableCellNode instead of RootNode.
    isShadowRoot() {
      return false;
    }
    /** @deprecated @internal */
    canMergeWith(node) {
      return false;
    }
    extractWithChild(child, selection, destination) {
      return false;
    }

    /**
     * Determines whether this node, when empty, can merge with a first block
     * of nodes being inserted.
     *
     * This method is specifically called in {@link RangeSelection.insertNodes}
     * to determine merging behavior during nodes insertion.
     *
     * @example
     * // In a ListItemNode or QuoteNode implementation:
     * canMergeWhenEmpty(): true {
     *  return true;
     * }
     */
    canMergeWhenEmpty() {
      return false;
    }
  }
  function $isElementNode(node) {
    return node instanceof ElementNode;
  }
  function isPointRemoved(point, nodesToRemoveKeySet, nodesToInsertKeySet) {
    let node = point.getNode();
    while (node) {
      const nodeKey = node.__key;
      if (nodesToRemoveKeySet.has(nodeKey) && !nodesToInsertKeySet.has(nodeKey)) {
        return true;
      }
      node = node.getParent();
    }
    return false;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // eslint-disable-next-line @typescript-eslint/no-unused-vars

  /** @noInheritDoc */
  // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
  class DecoratorNode extends LexicalNode {
    constructor(key) {
      super(key);
    }

    /**
     * The returned value is added to the LexicalEditor._decorators
     */
    decorate(editor, config) {
      {
        throw Error(`decorate: base method not extended`);
      }
    }
    isIsolated() {
      return false;
    }
    isInline() {
      return true;
    }
    isKeyboardSelectable() {
      return true;
    }
  }
  function $isDecoratorNode(node) {
    return node instanceof DecoratorNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class RootNode extends ElementNode {
    /** @internal */

    static getType() {
      return 'root';
    }
    static clone() {
      return new RootNode();
    }
    constructor() {
      super('root');
      this.__cachedText = null;
    }
    getTopLevelElementOrThrow() {
      {
        throw Error(`getTopLevelElementOrThrow: root nodes are not top level elements`);
      }
    }
    getTextContent() {
      const cachedText = this.__cachedText;
      if (isCurrentlyReadOnlyMode() || getActiveEditor()._dirtyType === NO_DIRTY_NODES) {
        if (cachedText !== null) {
          return cachedText;
        }
      }
      return super.getTextContent();
    }
    remove() {
      {
        throw Error(`remove: cannot be called on root nodes`);
      }
    }
    replace(node) {
      {
        throw Error(`replace: cannot be called on root nodes`);
      }
    }
    insertBefore(nodeToInsert) {
      {
        throw Error(`insertBefore: cannot be called on root nodes`);
      }
    }
    insertAfter(nodeToInsert) {
      {
        throw Error(`insertAfter: cannot be called on root nodes`);
      }
    }

    // View

    updateDOM(prevNode, dom) {
      return false;
    }

    // Mutate

    append(...nodesToAppend) {
      for (let i = 0; i < nodesToAppend.length; i++) {
        const node = nodesToAppend[i];
        if (!$isElementNode(node) && !$isDecoratorNode(node)) {
          {
            throw Error(`rootNode.append: Only element or decorator nodes can be appended to the root node`);
          }
        }
      }
      return super.append(...nodesToAppend);
    }
    static importJSON(serializedNode) {
      // We don't create a root, and instead use the existing root.
      const node = $getRoot();
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    exportJSON() {
      return {
        children: [],
        direction: this.getDirection(),
        format: this.getFormatType(),
        indent: this.getIndent(),
        type: 'root',
        version: 1
      };
    }
    collapseAtStart() {
      return true;
    }
  }
  function $createRootNode() {
    return new RootNode();
  }
  function $isRootNode(node) {
    return node instanceof RootNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function editorStateHasDirtySelection(editorState, editor) {
    const currentSelection = editor.getEditorState()._selection;
    const pendingSelection = editorState._selection;

    // Check if we need to update because of changes in selection
    if (pendingSelection !== null) {
      if (pendingSelection.dirty || !pendingSelection.is(currentSelection)) {
        return true;
      }
    } else if (currentSelection !== null) {
      return true;
    }
    return false;
  }
  function cloneEditorState(current) {
    return new EditorState(new Map(current._nodeMap));
  }
  function createEmptyEditorState() {
    return new EditorState(new Map([['root', $createRootNode()]]));
  }
  function exportNodeToJSON(node) {
    const serializedNode = node.exportJSON();
    const nodeClass = node.constructor;
    if (serializedNode.type !== nodeClass.getType()) {
      {
        throw Error(`LexicalNode: Node ${nodeClass.name} does not match the serialized type. Check if .exportJSON() is implemented and it is returning the correct type.`);
      }
    }
    if ($isElementNode(node)) {
      const serializedChildren = serializedNode.children;
      if (!Array.isArray(serializedChildren)) {
        {
          throw Error(`LexicalNode: Node ${nodeClass.name} is an element but .exportJSON() does not have a children array.`);
        }
      }
      const children = node.getChildren();
      for (let i = 0; i < children.length; i++) {
        const child = children[i];
        const serializedChildNode = exportNodeToJSON(child);
        serializedChildren.push(serializedChildNode);
      }
    }

    // @ts-expect-error
    return serializedNode;
  }
  class EditorState {
    constructor(nodeMap, selection) {
      this._nodeMap = nodeMap;
      this._selection = selection || null;
      this._flushSync = false;
      this._readOnly = false;
    }
    isEmpty() {
      return this._nodeMap.size === 1 && this._selection === null;
    }
    read(callbackFn, options) {
      return readEditorState(options && options.editor || null, this, callbackFn);
    }
    clone(selection) {
      const editorState = new EditorState(this._nodeMap, selection === undefined ? this._selection : selection);
      editorState._readOnly = true;
      return editorState;
    }
    toJSON() {
      return readEditorState(null, this, () => ({
        root: exportNodeToJSON($getRoot())
      }));
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // TODO: Cleanup ArtificialNode__DO_NOT_USE #5966
  class ArtificialNode__DO_NOT_USE extends ElementNode {
    static getType() {
      return 'artificial';
    }
    createDOM(config) {
      // this isnt supposed to be used and is not used anywhere but defining it to appease the API
      const dom = document.createElement('div');
      return dom;
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class ParagraphNode extends ElementNode {
    /** @internal */

    constructor(key) {
      super(key);
      this.__textFormat = 0;
      this.__textStyle = '';
    }
    static getType() {
      return 'paragraph';
    }
    getTextFormat() {
      const self = this.getLatest();
      return self.__textFormat;
    }
    setTextFormat(type) {
      const self = this.getWritable();
      self.__textFormat = type;
      return self;
    }
    hasTextFormat(type) {
      const formatFlag = TEXT_TYPE_TO_FORMAT[type];
      return (this.getTextFormat() & formatFlag) !== 0;
    }
    getTextStyle() {
      const self = this.getLatest();
      return self.__textStyle;
    }
    setTextStyle(style) {
      const self = this.getWritable();
      self.__textStyle = style;
      return self;
    }
    static clone(node) {
      return new ParagraphNode(node.__key);
    }
    afterCloneFrom(prevNode) {
      super.afterCloneFrom(prevNode);
      this.__textFormat = prevNode.__textFormat;
      this.__textStyle = prevNode.__textStyle;
    }

    // View

    createDOM(config) {
      const dom = document.createElement('p');
      const classNames = getCachedClassNameArray(config.theme, 'paragraph');
      if (classNames !== undefined) {
        const domClassList = dom.classList;
        domClassList.add(...classNames);
      }
      return dom;
    }
    updateDOM(prevNode, dom, config) {
      return false;
    }
    static importDOM() {
      return {
        p: node => ({
          conversion: $convertParagraphElement,
          priority: 0
        })
      };
    }
    exportDOM(editor) {
      const {
        element
      } = super.exportDOM(editor);
      if (element && isHTMLElement(element)) {
        if (this.isEmpty()) {
          element.append(document.createElement('br'));
        }
        const formatType = this.getFormatType();
        element.style.textAlign = formatType;
        const direction = this.getDirection();
        if (direction) {
          element.dir = direction;
        }
        const indent = this.getIndent();
        if (indent > 0) {
          // padding-inline-start is not widely supported in email HTML, but
          // Lexical Reconciler uses padding-inline-start. Using text-indent instead.
          element.style.textIndent = `${indent * 20}px`;
        }
      }
      return {
        element
      };
    }
    static importJSON(serializedNode) {
      const node = $createParagraphNode();
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      node.setTextFormat(serializedNode.textFormat);
      return node;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        textFormat: this.getTextFormat(),
        textStyle: this.getTextStyle(),
        type: 'paragraph',
        version: 1
      };
    }

    // Mutation

    insertNewAfter(rangeSelection, restoreSelection) {
      const newElement = $createParagraphNode();
      newElement.setTextFormat(rangeSelection.format);
      newElement.setTextStyle(rangeSelection.style);
      const direction = this.getDirection();
      newElement.setDirection(direction);
      newElement.setFormat(this.getFormatType());
      newElement.setStyle(this.getTextStyle());
      this.insertAfter(newElement, restoreSelection);
      return newElement;
    }
    collapseAtStart() {
      const children = this.getChildren();
      // If we have an empty (trimmed) first paragraph and try and remove it,
      // delete the paragraph as long as we have another sibling to go to
      if (children.length === 0 || $isTextNode(children[0]) && children[0].getTextContent().trim() === '') {
        const nextSibling = this.getNextSibling();
        if (nextSibling !== null) {
          this.selectNext();
          this.remove();
          return true;
        }
        const prevSibling = this.getPreviousSibling();
        if (prevSibling !== null) {
          this.selectPrevious();
          this.remove();
          return true;
        }
      }
      return false;
    }
  }
  function $convertParagraphElement(element) {
    const node = $createParagraphNode();
    if (element.style) {
      node.setFormat(element.style.textAlign);
      const indent = parseInt(element.style.textIndent, 10) / 20;
      if (indent > 0) {
        node.setIndent(indent);
      }
    }
    return {
      node
    };
  }
  function $createParagraphNode() {
    return $applyNodeReplacement(new ParagraphNode());
  }
  function $isParagraphNode(node) {
    return node instanceof ParagraphNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // https://github.com/microsoft/TypeScript/issues/3841
  // eslint-disable-next-line @typescript-eslint/no-explicit-any

  // eslint-disable-next-line @typescript-eslint/no-explicit-any

  const DEFAULT_SKIP_INITIALIZATION = true;
  const COMMAND_PRIORITY_EDITOR = 0;
  const COMMAND_PRIORITY_LOW = 1;
  const COMMAND_PRIORITY_NORMAL = 2;
  const COMMAND_PRIORITY_HIGH = 3;
  const COMMAND_PRIORITY_CRITICAL = 4;

  // eslint-disable-next-line @typescript-eslint/no-unused-vars

  /**
   * Type helper for extracting the payload type from a command.
   *
   * @example
   * ```ts
   * const MY_COMMAND = createCommand<SomeType>();
   *
   * // ...
   *
   * editor.registerCommand(MY_COMMAND, payload => {
   *   // Type of `payload` is inferred here. But lets say we want to extract a function to delegate to
   *   handleMyCommand(editor, payload);
   *   return true;
   * });
   *
   * function handleMyCommand(editor: LexicalEditor, payload: CommandPayloadType<typeof MY_COMMAND>) {
   *   // `payload` is of type `SomeType`, extracted from the command.
   * }
   * ```
   */

  function resetEditor(editor, prevRootElement, nextRootElement, pendingEditorState) {
    const keyNodeMap = editor._keyToDOMMap;
    keyNodeMap.clear();
    editor._editorState = createEmptyEditorState();
    editor._pendingEditorState = pendingEditorState;
    editor._compositionKey = null;
    editor._dirtyType = NO_DIRTY_NODES;
    editor._cloneNotNeeded.clear();
    editor._dirtyLeaves = new Set();
    editor._dirtyElements.clear();
    editor._normalizedNodes = new Set();
    editor._updateTags = new Set();
    editor._updates = [];
    editor._blockCursorElement = null;
    const observer = editor._observer;
    if (observer !== null) {
      observer.disconnect();
      editor._observer = null;
    }

    // Remove all the DOM nodes from the root element
    if (prevRootElement !== null) {
      prevRootElement.textContent = '';
    }
    if (nextRootElement !== null) {
      nextRootElement.textContent = '';
      keyNodeMap.set('root', nextRootElement);
    }
  }
  function initializeConversionCache(nodes, additionalConversions) {
    const conversionCache = new Map();
    const handledConversions = new Set();
    const addConversionsToCache = map => {
      Object.keys(map).forEach(key => {
        let currentCache = conversionCache.get(key);
        if (currentCache === undefined) {
          currentCache = [];
          conversionCache.set(key, currentCache);
        }
        currentCache.push(map[key]);
      });
    };
    nodes.forEach(node => {
      const importDOM = node.klass.importDOM;
      if (importDOM == null || handledConversions.has(importDOM)) {
        return;
      }
      handledConversions.add(importDOM);
      const map = importDOM.call(node.klass);
      if (map !== null) {
        addConversionsToCache(map);
      }
    });
    if (additionalConversions) {
      addConversionsToCache(additionalConversions);
    }
    return conversionCache;
  }

  /**
   * Creates a new LexicalEditor attached to a single contentEditable (provided in the config). This is
   * the lowest-level initialization API for a LexicalEditor. If you're using React or another framework,
   * consider using the appropriate abstractions, such as LexicalComposer
   * @param editorConfig - the editor configuration.
   * @returns a LexicalEditor instance
   */
  function createEditor(editorConfig) {
    const config = editorConfig || {};
    const activeEditor = internalGetActiveEditor();
    const theme = config.theme || {};
    const parentEditor = editorConfig === undefined ? activeEditor : config.parentEditor || null;
    const disableEvents = config.disableEvents || false;
    const editorState = createEmptyEditorState();
    const namespace = config.namespace || (parentEditor !== null ? parentEditor._config.namespace : createUID());
    const initialEditorState = config.editorState;
    const nodes = [RootNode, TextNode, LineBreakNode, TabNode, ParagraphNode, ArtificialNode__DO_NOT_USE, ...(config.nodes || [])];
    const {
      onError,
      html
    } = config;
    const isEditable = config.editable !== undefined ? config.editable : true;
    let registeredNodes;
    if (editorConfig === undefined && activeEditor !== null) {
      registeredNodes = activeEditor._nodes;
    } else {
      registeredNodes = new Map();
      for (let i = 0; i < nodes.length; i++) {
        let klass = nodes[i];
        let replace = null;
        let replaceWithKlass = null;
        if (typeof klass !== 'function') {
          const options = klass;
          klass = options.replace;
          replace = options.with;
          replaceWithKlass = options.withKlass || null;
        }
        // Ensure custom nodes implement required methods and replaceWithKlass is instance of base klass.
        {
          // ArtificialNode__DO_NOT_USE can get renamed, so we use the type
          const nodeType = Object.prototype.hasOwnProperty.call(klass, 'getType') && klass.getType();
          const name = klass.name;
          if (replaceWithKlass) {
            if (!(replaceWithKlass.prototype instanceof klass)) {
              throw Error(`${replaceWithKlass.name} doesn't extend the ${name}`);
            }
          }
          if (name !== 'RootNode' && nodeType !== 'root' && nodeType !== 'artificial') {
            const proto = klass.prototype;
            ['getType', 'clone'].forEach(method => {
              // eslint-disable-next-line no-prototype-builtins
              if (!klass.hasOwnProperty(method)) {
                console.warn(`${name} must implement static "${method}" method`);
              }
            });
            if (
            // eslint-disable-next-line no-prototype-builtins
            !klass.hasOwnProperty('importDOM') &&
            // eslint-disable-next-line no-prototype-builtins
            klass.hasOwnProperty('exportDOM')) {
              console.warn(`${name} should implement "importDOM" if using a custom "exportDOM" method to ensure HTML serialization (important for copy & paste) works as expected`);
            }
            if (proto instanceof DecoratorNode) {
              // eslint-disable-next-line no-prototype-builtins
              if (!proto.hasOwnProperty('decorate')) {
                console.warn(`${proto.constructor.name} must implement "decorate" method`);
              }
            }
            if (
            // eslint-disable-next-line no-prototype-builtins
            !klass.hasOwnProperty('importJSON')) {
              console.warn(`${name} should implement "importJSON" method to ensure JSON and default HTML serialization works as expected`);
            }
            if (
            // eslint-disable-next-line no-prototype-builtins
            !proto.hasOwnProperty('exportJSON')) {
              console.warn(`${name} should implement "exportJSON" method to ensure JSON and default HTML serialization works as expected`);
            }
          }
        }
        const type = klass.getType();
        const transform = klass.transform();
        const transforms = new Set();
        if (transform !== null) {
          transforms.add(transform);
        }
        registeredNodes.set(type, {
          exportDOM: html && html.export ? html.export.get(klass) : undefined,
          klass,
          replace,
          replaceWithKlass,
          transforms
        });
      }
    }
    const editor = new LexicalEditor(editorState, parentEditor, registeredNodes, {
      disableEvents,
      namespace,
      theme
    }, onError ? onError : console.error, initializeConversionCache(registeredNodes, html ? html.import : undefined), isEditable);
    if (initialEditorState !== undefined) {
      editor._pendingEditorState = initialEditorState;
      editor._dirtyType = FULL_RECONCILE;
    }
    return editor;
  }
  class LexicalEditor {
    /** The version with build identifiers for this editor (since 0.17.1) */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */
    constructor(editorState, parentEditor, nodes, config, onError, htmlConversions, editable) {
      this._parentEditor = parentEditor;
      // The root element associated with this editor
      this._rootElement = null;
      // The current editor state
      this._editorState = editorState;
      // Handling of drafts and updates
      this._pendingEditorState = null;
      // Used to help co-ordinate selection and events
      this._compositionKey = null;
      this._deferred = [];
      // Used during reconciliation
      this._keyToDOMMap = new Map();
      this._updates = [];
      this._updating = false;
      // Listeners
      this._listeners = {
        decorator: new Set(),
        editable: new Set(),
        mutation: new Map(),
        root: new Set(),
        textcontent: new Set(),
        update: new Set()
      };
      // Commands
      this._commands = new Map();
      // Editor configuration for theme/context.
      this._config = config;
      // Mapping of types to their nodes
      this._nodes = nodes;
      // React node decorators for portals
      this._decorators = {};
      this._pendingDecorators = null;
      // Used to optimize reconciliation
      this._dirtyType = NO_DIRTY_NODES;
      this._cloneNotNeeded = new Set();
      this._dirtyLeaves = new Set();
      this._dirtyElements = new Map();
      this._normalizedNodes = new Set();
      this._updateTags = new Set();
      // Handling of DOM mutations
      this._observer = null;
      // Used for identifying owning editors
      this._key = createUID();
      this._onError = onError;
      this._htmlConversions = htmlConversions;
      this._editable = editable;
      this._headless = parentEditor !== null && parentEditor._headless;
      this._window = null;
      this._blockCursorElement = null;
    }

    /**
     *
     * @returns true if the editor is currently in "composition" mode due to receiving input
     * through an IME, or 3P extension, for example. Returns false otherwise.
     */
    isComposing() {
      return this._compositionKey != null;
    }
    /**
     * Registers a listener for Editor update event. Will trigger the provided callback
     * each time the editor goes through an update (via {@link LexicalEditor.update}) until the
     * teardown function is called.
     *
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerUpdateListener(listener) {
      const listenerSetOrMap = this._listeners.update;
      listenerSetOrMap.add(listener);
      return () => {
        listenerSetOrMap.delete(listener);
      };
    }
    /**
     * Registers a listener for for when the editor changes between editable and non-editable states.
     * Will trigger the provided callback each time the editor transitions between these states until the
     * teardown function is called.
     *
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerEditableListener(listener) {
      const listenerSetOrMap = this._listeners.editable;
      listenerSetOrMap.add(listener);
      return () => {
        listenerSetOrMap.delete(listener);
      };
    }
    /**
     * Registers a listener for when the editor's decorator object changes. The decorator object contains
     * all DecoratorNode keys -> their decorated value. This is primarily used with external UI frameworks.
     *
     * Will trigger the provided callback each time the editor transitions between these states until the
     * teardown function is called.
     *
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerDecoratorListener(listener) {
      const listenerSetOrMap = this._listeners.decorator;
      listenerSetOrMap.add(listener);
      return () => {
        listenerSetOrMap.delete(listener);
      };
    }
    /**
     * Registers a listener for when Lexical commits an update to the DOM and the text content of
     * the editor changes from the previous state of the editor. If the text content is the
     * same between updates, no notifications to the listeners will happen.
     *
     * Will trigger the provided callback each time the editor transitions between these states until the
     * teardown function is called.
     *
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerTextContentListener(listener) {
      const listenerSetOrMap = this._listeners.textcontent;
      listenerSetOrMap.add(listener);
      return () => {
        listenerSetOrMap.delete(listener);
      };
    }
    /**
     * Registers a listener for when the editor's root DOM element (the content editable
     * Lexical attaches to) changes. This is primarily used to attach event listeners to the root
     *  element. The root listener function is executed directly upon registration and then on
     * any subsequent update.
     *
     * Will trigger the provided callback each time the editor transitions between these states until the
     * teardown function is called.
     *
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerRootListener(listener) {
      const listenerSetOrMap = this._listeners.root;
      listener(this._rootElement, null);
      listenerSetOrMap.add(listener);
      return () => {
        listener(null, this._rootElement);
        listenerSetOrMap.delete(listener);
      };
    }
    /**
     * Registers a listener that will trigger anytime the provided command
     * is dispatched, subject to priority. Listeners that run at a higher priority can "intercept"
     * commands and prevent them from propagating to other handlers by returning true.
     *
     * Listeners registered at the same priority level will run deterministically in the order of registration.
     *
     * @param command - the command that will trigger the callback.
     * @param listener - the function that will execute when the command is dispatched.
     * @param priority - the relative priority of the listener. 0 | 1 | 2 | 3 | 4
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerCommand(command, listener, priority) {
      if (priority === undefined) {
        {
          throw Error(`Listener for type "command" requires a "priority".`);
        }
      }
      const commandsMap = this._commands;
      if (!commandsMap.has(command)) {
        commandsMap.set(command, [new Set(), new Set(), new Set(), new Set(), new Set()]);
      }
      const listenersInPriorityOrder = commandsMap.get(command);
      if (listenersInPriorityOrder === undefined) {
        {
          throw Error(`registerCommand: Command ${String(command)} not found in command map`);
        }
      }
      const listeners = listenersInPriorityOrder[priority];
      listeners.add(listener);
      return () => {
        listeners.delete(listener);
        if (listenersInPriorityOrder.every(listenersSet => listenersSet.size === 0)) {
          commandsMap.delete(command);
        }
      };
    }

    /**
     * Registers a listener that will run when a Lexical node of the provided class is
     * mutated. The listener will receive a list of nodes along with the type of mutation
     * that was performed on each: created, destroyed, or updated.
     *
     * One common use case for this is to attach DOM event listeners to the underlying DOM nodes as Lexical nodes are created.
     * {@link LexicalEditor.getElementByKey} can be used for this.
     *
     * If any existing nodes are in the DOM, and skipInitialization is not true, the listener
     * will be called immediately with an updateTag of 'registerMutationListener' where all
     * nodes have the 'created' NodeMutation. This can be controlled with the skipInitialization option
     * (default is currently true for backwards compatibility in 0.16.x but will change to false in 0.17.0).
     *
     * @param klass - The class of the node that you want to listen to mutations on.
     * @param listener - The logic you want to run when the node is mutated.
     * @param options - see {@link MutationListenerOptions}
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerMutationListener(klass, listener, options) {
      const klassToMutate = this.resolveRegisteredNodeAfterReplacements(this.getRegisteredNode(klass)).klass;
      const mutations = this._listeners.mutation;
      mutations.set(listener, klassToMutate);
      const skipInitialization = options && options.skipInitialization;
      if (!(skipInitialization === undefined ? DEFAULT_SKIP_INITIALIZATION : skipInitialization)) {
        this.initializeMutationListener(listener, klassToMutate);
      }
      return () => {
        mutations.delete(listener);
      };
    }

    /** @internal */
    getRegisteredNode(klass) {
      const registeredNode = this._nodes.get(klass.getType());
      if (registeredNode === undefined) {
        {
          throw Error(`Node ${klass.name} has not been registered. Ensure node has been passed to createEditor.`);
        }
      }
      return registeredNode;
    }

    /** @internal */
    resolveRegisteredNodeAfterReplacements(registeredNode) {
      while (registeredNode.replaceWithKlass) {
        registeredNode = this.getRegisteredNode(registeredNode.replaceWithKlass);
      }
      return registeredNode;
    }

    /** @internal */
    initializeMutationListener(listener, klass) {
      const prevEditorState = this._editorState;
      const nodeMap = getCachedTypeToNodeMap(prevEditorState).get(klass.getType());
      if (!nodeMap) {
        return;
      }
      const nodeMutationMap = new Map();
      for (const k of nodeMap.keys()) {
        nodeMutationMap.set(k, 'created');
      }
      if (nodeMutationMap.size > 0) {
        listener(nodeMutationMap, {
          dirtyLeaves: new Set(),
          prevEditorState,
          updateTags: new Set(['registerMutationListener'])
        });
      }
    }

    /** @internal */
    registerNodeTransformToKlass(klass, listener) {
      const registeredNode = this.getRegisteredNode(klass);
      registeredNode.transforms.add(listener);
      return registeredNode;
    }

    /**
     * Registers a listener that will run when a Lexical node of the provided class is
     * marked dirty during an update. The listener will continue to run as long as the node
     * is marked dirty. There are no guarantees around the order of transform execution!
     *
     * Watch out for infinite loops. See [Node Transforms](https://lexical.dev/docs/concepts/transforms)
     * @param klass - The class of the node that you want to run transforms on.
     * @param listener - The logic you want to run when the node is updated.
     * @returns a teardown function that can be used to cleanup the listener.
     */
    registerNodeTransform(klass, listener) {
      const registeredNode = this.registerNodeTransformToKlass(klass, listener);
      const registeredNodes = [registeredNode];
      const replaceWithKlass = registeredNode.replaceWithKlass;
      if (replaceWithKlass != null) {
        const registeredReplaceWithNode = this.registerNodeTransformToKlass(replaceWithKlass, listener);
        registeredNodes.push(registeredReplaceWithNode);
      }
      markAllNodesAsDirty(this, klass.getType());
      return () => {
        registeredNodes.forEach(node => node.transforms.delete(listener));
      };
    }

    /**
     * Used to assert that a certain node is registered, usually by plugins to ensure nodes that they
     * depend on have been registered.
     * @returns True if the editor has registered the provided node type, false otherwise.
     */
    hasNode(node) {
      return this._nodes.has(node.getType());
    }

    /**
     * Used to assert that certain nodes are registered, usually by plugins to ensure nodes that they
     * depend on have been registered.
     * @returns True if the editor has registered all of the provided node types, false otherwise.
     */
    hasNodes(nodes) {
      return nodes.every(this.hasNode.bind(this));
    }

    /**
     * Dispatches a command of the specified type with the specified payload.
     * This triggers all command listeners (set by {@link LexicalEditor.registerCommand})
     * for this type, passing them the provided payload.
     * @param type - the type of command listeners to trigger.
     * @param payload - the data to pass as an argument to the command listeners.
     */
    dispatchCommand(type, payload) {
      return dispatchCommand(this, type, payload);
    }

    /**
     * Gets a map of all decorators in the editor.
     * @returns A mapping of call decorator keys to their decorated content
     */
    getDecorators() {
      return this._decorators;
    }

    /**
     *
     * @returns the current root element of the editor. If you want to register
     * an event listener, do it via {@link LexicalEditor.registerRootListener}, since
     * this reference may not be stable.
     */
    getRootElement() {
      return this._rootElement;
    }

    /**
     * Gets the key of the editor
     * @returns The editor key
     */
    getKey() {
      return this._key;
    }

    /**
     * Imperatively set the root contenteditable element that Lexical listens
     * for events on.
     */
    setRootElement(nextRootElement) {
      const prevRootElement = this._rootElement;
      if (nextRootElement !== prevRootElement) {
        const classNames = getCachedClassNameArray(this._config.theme, 'root');
        const pendingEditorState = this._pendingEditorState || this._editorState;
        this._rootElement = nextRootElement;
        resetEditor(this, prevRootElement, nextRootElement, pendingEditorState);
        if (prevRootElement !== null) {
          // TODO: remove this flag once we no longer use UEv2 internally
          if (!this._config.disableEvents) {
            removeRootElementEvents(prevRootElement);
          }
          if (classNames != null) {
            prevRootElement.classList.remove(...classNames);
          }
        }
        if (nextRootElement !== null) {
          const windowObj = getDefaultView(nextRootElement);
          const style = nextRootElement.style;
          style.userSelect = 'text';
          style.whiteSpace = 'pre-wrap';
          style.wordBreak = 'break-word';
          nextRootElement.setAttribute('data-lexical-editor', 'true');
          this._window = windowObj;
          this._dirtyType = FULL_RECONCILE;
          initMutationObserver(this);
          this._updateTags.add('history-merge');
          $commitPendingUpdates(this);

          // TODO: remove this flag once we no longer use UEv2 internally
          if (!this._config.disableEvents) {
            addRootElementEvents(nextRootElement, this);
          }
          if (classNames != null) {
            nextRootElement.classList.add(...classNames);
          }
        } else {
          // If content editable is unmounted we'll reset editor state back to original
          // (or pending) editor state since there will be no reconciliation
          this._editorState = pendingEditorState;
          this._pendingEditorState = null;
          this._window = null;
        }
        triggerListeners('root', this, false, nextRootElement, prevRootElement);
      }
    }

    /**
     * Gets the underlying HTMLElement associated with the LexicalNode for the given key.
     * @returns the HTMLElement rendered by the LexicalNode associated with the key.
     * @param key - the key of the LexicalNode.
     */
    getElementByKey(key) {
      return this._keyToDOMMap.get(key) || null;
    }

    /**
     * Gets the active editor state.
     * @returns The editor state
     */
    getEditorState() {
      return this._editorState;
    }

    /**
     * Imperatively set the EditorState. Triggers reconciliation like an update.
     * @param editorState - the state to set the editor
     * @param options - options for the update.
     */
    setEditorState(editorState, options) {
      if (editorState.isEmpty()) {
        {
          throw Error(`setEditorState: the editor state is empty. Ensure the editor state's root node never becomes empty.`);
        }
      }
      $flushRootMutations(this);
      const pendingEditorState = this._pendingEditorState;
      const tags = this._updateTags;
      const tag = options !== undefined ? options.tag : null;
      if (pendingEditorState !== null && !pendingEditorState.isEmpty()) {
        if (tag != null) {
          tags.add(tag);
        }
        $commitPendingUpdates(this);
      }
      this._pendingEditorState = editorState;
      this._dirtyType = FULL_RECONCILE;
      this._dirtyElements.set('root', false);
      this._compositionKey = null;
      if (tag != null) {
        tags.add(tag);
      }
      $commitPendingUpdates(this);
    }

    /**
     * Parses a SerializedEditorState (usually produced by {@link EditorState.toJSON}) and returns
     * and EditorState object that can be, for example, passed to {@link LexicalEditor.setEditorState}. Typically,
     * deserialization from JSON stored in a database uses this method.
     * @param maybeStringifiedEditorState
     * @param updateFn
     * @returns
     */
    parseEditorState(maybeStringifiedEditorState, updateFn) {
      const serializedEditorState = typeof maybeStringifiedEditorState === 'string' ? JSON.parse(maybeStringifiedEditorState) : maybeStringifiedEditorState;
      return parseEditorState(serializedEditorState, this, updateFn);
    }

    /**
     * Executes a read of the editor's state, with the
     * editor context available (useful for exporting and read-only DOM
     * operations). Much like update, but prevents any mutation of the
     * editor's state. Any pending updates will be flushed immediately before
     * the read.
     * @param callbackFn - A function that has access to read-only editor state.
     */
    read(callbackFn) {
      $commitPendingUpdates(this);
      return this.getEditorState().read(callbackFn, {
        editor: this
      });
    }

    /**
     * Executes an update to the editor state. The updateFn callback is the ONLY place
     * where Lexical editor state can be safely mutated.
     * @param updateFn - A function that has access to writable editor state.
     * @param options - A bag of options to control the behavior of the update.
     * @param options.onUpdate - A function to run once the update is complete.
     * Useful for synchronizing updates in some cases.
     * @param options.skipTransforms - Setting this to true will suppress all node
     * transforms for this update cycle.
     * @param options.tag - A tag to identify this update, in an update listener, for instance.
     * Some tags are reserved by the core and control update behavior in different ways.
     * @param options.discrete - If true, prevents this update from being batched, forcing it to
     * run synchronously.
     */
    update(updateFn, options) {
      updateEditor(this, updateFn, options);
    }

    /**
     * Focuses the editor
     * @param callbackFn - A function to run after the editor is focused.
     * @param options - A bag of options
     * @param options.defaultSelection - Where to move selection when the editor is
     * focused. Can be rootStart, rootEnd, or undefined. Defaults to rootEnd.
     */
    focus(callbackFn, options = {}) {
      const rootElement = this._rootElement;
      if (rootElement !== null) {
        // This ensures that iOS does not trigger caps lock upon focus
        rootElement.setAttribute('autocapitalize', 'off');
        updateEditor(this, () => {
          const selection = $getSelection();
          const root = $getRoot();
          if (selection !== null) {
            // Marking the selection dirty will force the selection back to it
            selection.dirty = true;
          } else if (root.getChildrenSize() !== 0) {
            if (options.defaultSelection === 'rootStart') {
              root.selectStart();
            } else {
              root.selectEnd();
            }
          }
        }, {
          onUpdate: () => {
            rootElement.removeAttribute('autocapitalize');
            if (callbackFn) {
              callbackFn();
            }
          },
          tag: 'focus'
        });
        // In the case where onUpdate doesn't fire (due to the focus update not
        // occuring).
        if (this._pendingEditorState === null) {
          rootElement.removeAttribute('autocapitalize');
        }
      }
    }

    /**
     * Removes focus from the editor.
     */
    blur() {
      const rootElement = this._rootElement;
      if (rootElement !== null) {
        rootElement.blur();
      }
      const domSelection = getDOMSelection(this._window);
      if (domSelection !== null) {
        domSelection.removeAllRanges();
      }
    }
    /**
     * Returns true if the editor is editable, false otherwise.
     * @returns True if the editor is editable, false otherwise.
     */
    isEditable() {
      return this._editable;
    }
    /**
     * Sets the editable property of the editor. When false, the
     * editor will not listen for user events on the underling contenteditable.
     * @param editable - the value to set the editable mode to.
     */
    setEditable(editable) {
      if (this._editable !== editable) {
        this._editable = editable;
        triggerListeners('editable', this, true, editable);
      }
    }
    /**
     * Returns a JSON-serializable javascript object NOT a JSON string.
     * You still must call JSON.stringify (or something else) to turn the
     * state into a string you can transfer over the wire and store in a database.
     *
     * See {@link LexicalNode.exportJSON}
     *
     * @returns A JSON-serializable javascript object
     */
    toJSON() {
      return {
        editorState: this._editorState.toJSON()
      };
    }
  }
  LexicalEditor.version = "0.17.1+dev.esm";

  var modDev = /*#__PURE__*/Object.freeze({
    $addUpdateTag: $addUpdateTag,
    $applyNodeReplacement: $applyNodeReplacement,
    $cloneWithProperties: $cloneWithProperties,
    $copyNode: $copyNode,
    $createLineBreakNode: $createLineBreakNode,
    $createNodeSelection: $createNodeSelection,
    $createParagraphNode: $createParagraphNode,
    $createPoint: $createPoint,
    $createRangeSelection: $createRangeSelection,
    $createRangeSelectionFromDom: $createRangeSelectionFromDom,
    $createTabNode: $createTabNode,
    $createTextNode: $createTextNode,
    $getAdjacentNode: $getAdjacentNode,
    $getCharacterOffsets: $getCharacterOffsets,
    $getEditor: $getEditor,
    $getNearestNodeFromDOMNode: $getNearestNodeFromDOMNode,
    $getNearestRootOrShadowRoot: $getNearestRootOrShadowRoot,
    $getNodeByKey: $getNodeByKey,
    $getNodeByKeyOrThrow: $getNodeByKeyOrThrow,
    $getPreviousSelection: $getPreviousSelection,
    $getRoot: $getRoot,
    $getSelection: $getSelection,
    $getTextContent: $getTextContent,
    $hasAncestor: $hasAncestor,
    $hasUpdateTag: $hasUpdateTag,
    $insertNodes: $insertNodes,
    $isBlockElementNode: $isBlockElementNode,
    $isDecoratorNode: $isDecoratorNode,
    $isElementNode: $isElementNode,
    $isInlineElementOrDecoratorNode: $isInlineElementOrDecoratorNode,
    $isLeafNode: $isLeafNode,
    $isLineBreakNode: $isLineBreakNode,
    $isNodeSelection: $isNodeSelection,
    $isParagraphNode: $isParagraphNode,
    $isRangeSelection: $isRangeSelection,
    $isRootNode: $isRootNode,
    $isRootOrShadowRoot: $isRootOrShadowRoot,
    $isTabNode: $isTabNode,
    $isTextNode: $isTextNode,
    $isTokenOrSegmented: $isTokenOrSegmented,
    $nodesOfType: $nodesOfType,
    $normalizeSelection__EXPERIMENTAL: $normalizeSelection,
    $parseSerializedNode: $parseSerializedNode,
    $selectAll: $selectAll,
    $setCompositionKey: $setCompositionKey,
    $setSelection: $setSelection,
    $splitNode: $splitNode,
    ArtificialNode__DO_NOT_USE: ArtificialNode__DO_NOT_USE,
    BLUR_COMMAND: BLUR_COMMAND,
    CAN_REDO_COMMAND: CAN_REDO_COMMAND,
    CAN_UNDO_COMMAND: CAN_UNDO_COMMAND,
    CLEAR_EDITOR_COMMAND: CLEAR_EDITOR_COMMAND,
    CLEAR_HISTORY_COMMAND: CLEAR_HISTORY_COMMAND,
    CLICK_COMMAND: CLICK_COMMAND,
    COMMAND_PRIORITY_CRITICAL: COMMAND_PRIORITY_CRITICAL,
    COMMAND_PRIORITY_EDITOR: COMMAND_PRIORITY_EDITOR,
    COMMAND_PRIORITY_HIGH: COMMAND_PRIORITY_HIGH,
    COMMAND_PRIORITY_LOW: COMMAND_PRIORITY_LOW,
    COMMAND_PRIORITY_NORMAL: COMMAND_PRIORITY_NORMAL,
    CONTROLLED_TEXT_INSERTION_COMMAND: CONTROLLED_TEXT_INSERTION_COMMAND,
    COPY_COMMAND: COPY_COMMAND,
    CUT_COMMAND: CUT_COMMAND,
    DELETE_CHARACTER_COMMAND: DELETE_CHARACTER_COMMAND,
    DELETE_LINE_COMMAND: DELETE_LINE_COMMAND,
    DELETE_WORD_COMMAND: DELETE_WORD_COMMAND,
    DRAGEND_COMMAND: DRAGEND_COMMAND,
    DRAGOVER_COMMAND: DRAGOVER_COMMAND,
    DRAGSTART_COMMAND: DRAGSTART_COMMAND,
    DROP_COMMAND: DROP_COMMAND,
    DecoratorNode: DecoratorNode,
    ElementNode: ElementNode,
    FOCUS_COMMAND: FOCUS_COMMAND,
    FORMAT_ELEMENT_COMMAND: FORMAT_ELEMENT_COMMAND,
    FORMAT_TEXT_COMMAND: FORMAT_TEXT_COMMAND,
    INDENT_CONTENT_COMMAND: INDENT_CONTENT_COMMAND,
    INSERT_LINE_BREAK_COMMAND: INSERT_LINE_BREAK_COMMAND,
    INSERT_PARAGRAPH_COMMAND: INSERT_PARAGRAPH_COMMAND,
    INSERT_TAB_COMMAND: INSERT_TAB_COMMAND,
    IS_ALL_FORMATTING: IS_ALL_FORMATTING,
    IS_BOLD: IS_BOLD,
    IS_CODE: IS_CODE,
    IS_HIGHLIGHT: IS_HIGHLIGHT,
    IS_ITALIC: IS_ITALIC,
    IS_STRIKETHROUGH: IS_STRIKETHROUGH,
    IS_SUBSCRIPT: IS_SUBSCRIPT,
    IS_SUPERSCRIPT: IS_SUPERSCRIPT,
    IS_UNDERLINE: IS_UNDERLINE,
    KEY_ARROW_DOWN_COMMAND: KEY_ARROW_DOWN_COMMAND,
    KEY_ARROW_LEFT_COMMAND: KEY_ARROW_LEFT_COMMAND,
    KEY_ARROW_RIGHT_COMMAND: KEY_ARROW_RIGHT_COMMAND,
    KEY_ARROW_UP_COMMAND: KEY_ARROW_UP_COMMAND,
    KEY_BACKSPACE_COMMAND: KEY_BACKSPACE_COMMAND,
    KEY_DELETE_COMMAND: KEY_DELETE_COMMAND,
    KEY_DOWN_COMMAND: KEY_DOWN_COMMAND,
    KEY_ENTER_COMMAND: KEY_ENTER_COMMAND,
    KEY_ESCAPE_COMMAND: KEY_ESCAPE_COMMAND,
    KEY_MODIFIER_COMMAND: KEY_MODIFIER_COMMAND,
    KEY_SPACE_COMMAND: KEY_SPACE_COMMAND,
    KEY_TAB_COMMAND: KEY_TAB_COMMAND,
    LineBreakNode: LineBreakNode,
    MOVE_TO_END: MOVE_TO_END,
    MOVE_TO_START: MOVE_TO_START,
    OUTDENT_CONTENT_COMMAND: OUTDENT_CONTENT_COMMAND,
    PASTE_COMMAND: PASTE_COMMAND,
    ParagraphNode: ParagraphNode,
    REDO_COMMAND: REDO_COMMAND,
    REMOVE_TEXT_COMMAND: REMOVE_TEXT_COMMAND,
    RootNode: RootNode,
    SELECTION_CHANGE_COMMAND: SELECTION_CHANGE_COMMAND,
    SELECTION_INSERT_CLIPBOARD_NODES_COMMAND: SELECTION_INSERT_CLIPBOARD_NODES_COMMAND,
    SELECT_ALL_COMMAND: SELECT_ALL_COMMAND,
    TEXT_TYPE_TO_FORMAT: TEXT_TYPE_TO_FORMAT,
    TabNode: TabNode,
    TextNode: TextNode,
    UNDO_COMMAND: UNDO_COMMAND,
    createCommand: createCommand,
    createEditor: createEditor,
    getEditorPropertyFromDOMNode: getEditorPropertyFromDOMNode,
    getNearestEditorFromDOMNode: getNearestEditorFromDOMNode,
    isBlockDomNode: isBlockDomNode,
    isCurrentlyReadOnlyMode: isCurrentlyReadOnlyMode,
    isHTMLAnchorElement: isHTMLAnchorElement,
    isHTMLElement: isHTMLElement,
    isInlineDomNode: isInlineDomNode,
    isLexicalEditor: isLexicalEditor,
    isSelectionCapturedInDecoratorInput: isSelectionCapturedInDecoratorInput,
    isSelectionWithinEditor: isSelectionWithinEditor,
    resetRandomKey: resetRandomKey
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const j = "undefined" != typeof window && void 0 !== window.document && void 0 !== window.document.createElement,
    H = j && "documentMode" in document ? document.documentMode : null,
    q = j && /Mac|iPod|iPhone|iPad/.test(navigator.platform),
    Q = j && /^(?!.*Seamonkey)(?=.*Firefox).*/i.test(navigator.userAgent),
    X = !(!j || !("InputEvent" in window) || H) && "getTargetRanges" in new window.InputEvent("input"),
    Y = j && /Version\/[\d.]+.*Safari/.test(navigator.userAgent),
    Z = j && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
    G = j && /Android/.test(navigator.userAgent),
    tt = j && /^(?=.*Chrome).*/i.test(navigator.userAgent),
    nt = j && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !tt;
  function Bt(t) {
    return t && t.__esModule && Object.prototype.hasOwnProperty.call(t, "default") ? t.default : t;
  }
  var Rt = Bt(function (t) {
    const e = new URLSearchParams();
    e.append("code", t);
    for (let t = 1; t < arguments.length; t++) e.append("v", arguments[t]);
    throw Error(`Minified Lexical error #${t}; visit https://lexical.dev/docs/error?${e} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });
  const hr = Object.freeze({});

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod = modDev;
  const $addUpdateTag$1 = mod.$addUpdateTag;
  const $applyNodeReplacement$1 = mod.$applyNodeReplacement;
  const $cloneWithProperties$1 = mod.$cloneWithProperties;
  const $copyNode$1 = mod.$copyNode;
  const $createLineBreakNode$1 = mod.$createLineBreakNode;
  const $createNodeSelection$1 = mod.$createNodeSelection;
  const $createParagraphNode$1 = mod.$createParagraphNode;
  const $createPoint$1 = mod.$createPoint;
  const $createRangeSelection$1 = mod.$createRangeSelection;
  const $createRangeSelectionFromDom$1 = mod.$createRangeSelectionFromDom;
  const $createTabNode$1 = mod.$createTabNode;
  const $createTextNode$1 = mod.$createTextNode;
  const $getAdjacentNode$1 = mod.$getAdjacentNode;
  const $getCharacterOffsets$1 = mod.$getCharacterOffsets;
  const $getEditor$1 = mod.$getEditor;
  const $getNearestNodeFromDOMNode$1 = mod.$getNearestNodeFromDOMNode;
  const $getNearestRootOrShadowRoot$1 = mod.$getNearestRootOrShadowRoot;
  const $getNodeByKey$1 = mod.$getNodeByKey;
  const $getNodeByKeyOrThrow$1 = mod.$getNodeByKeyOrThrow;
  const $getPreviousSelection$1 = mod.$getPreviousSelection;
  const $getRoot$1 = mod.$getRoot;
  const $getSelection$1 = mod.$getSelection;
  const $getTextContent$1 = mod.$getTextContent;
  const $hasAncestor$1 = mod.$hasAncestor;
  const $hasUpdateTag$1 = mod.$hasUpdateTag;
  const $insertNodes$1 = mod.$insertNodes;
  const $isBlockElementNode$1 = mod.$isBlockElementNode;
  const $isDecoratorNode$1 = mod.$isDecoratorNode;
  const $isElementNode$1 = mod.$isElementNode;
  const $isInlineElementOrDecoratorNode$1 = mod.$isInlineElementOrDecoratorNode;
  const $isLeafNode$1 = mod.$isLeafNode;
  const $isLineBreakNode$1 = mod.$isLineBreakNode;
  const $isNodeSelection$1 = mod.$isNodeSelection;
  const $isParagraphNode$1 = mod.$isParagraphNode;
  const $isRangeSelection$1 = mod.$isRangeSelection;
  const $isRootNode$1 = mod.$isRootNode;
  const $isRootOrShadowRoot$1 = mod.$isRootOrShadowRoot;
  const $isTabNode$1 = mod.$isTabNode;
  const $isTextNode$1 = mod.$isTextNode;
  const $isTokenOrSegmented$1 = mod.$isTokenOrSegmented;
  const $nodesOfType$1 = mod.$nodesOfType;
  const $normalizeSelection__EXPERIMENTAL = mod.$normalizeSelection__EXPERIMENTAL;
  const $parseSerializedNode$1 = mod.$parseSerializedNode;
  const $selectAll$1 = mod.$selectAll;
  const $setCompositionKey$1 = mod.$setCompositionKey;
  const $setSelection$1 = mod.$setSelection;
  const $splitNode$1 = mod.$splitNode;
  const ArtificialNode__DO_NOT_USE$1 = mod.ArtificialNode__DO_NOT_USE;
  const BLUR_COMMAND$1 = mod.BLUR_COMMAND;
  const CAN_REDO_COMMAND$1 = mod.CAN_REDO_COMMAND;
  const CAN_UNDO_COMMAND$1 = mod.CAN_UNDO_COMMAND;
  const CLEAR_EDITOR_COMMAND$1 = mod.CLEAR_EDITOR_COMMAND;
  const CLEAR_HISTORY_COMMAND$1 = mod.CLEAR_HISTORY_COMMAND;
  const CLICK_COMMAND$1 = mod.CLICK_COMMAND;
  const COMMAND_PRIORITY_CRITICAL$1 = mod.COMMAND_PRIORITY_CRITICAL;
  const COMMAND_PRIORITY_EDITOR$1 = mod.COMMAND_PRIORITY_EDITOR;
  const COMMAND_PRIORITY_HIGH$1 = mod.COMMAND_PRIORITY_HIGH;
  const COMMAND_PRIORITY_LOW$1 = mod.COMMAND_PRIORITY_LOW;
  const COMMAND_PRIORITY_NORMAL$1 = mod.COMMAND_PRIORITY_NORMAL;
  const CONTROLLED_TEXT_INSERTION_COMMAND$1 = mod.CONTROLLED_TEXT_INSERTION_COMMAND;
  const COPY_COMMAND$1 = mod.COPY_COMMAND;
  const CUT_COMMAND$1 = mod.CUT_COMMAND;
  const DELETE_CHARACTER_COMMAND$1 = mod.DELETE_CHARACTER_COMMAND;
  const DELETE_LINE_COMMAND$1 = mod.DELETE_LINE_COMMAND;
  const DELETE_WORD_COMMAND$1 = mod.DELETE_WORD_COMMAND;
  const DRAGEND_COMMAND$1 = mod.DRAGEND_COMMAND;
  const DRAGOVER_COMMAND$1 = mod.DRAGOVER_COMMAND;
  const DRAGSTART_COMMAND$1 = mod.DRAGSTART_COMMAND;
  const DROP_COMMAND$1 = mod.DROP_COMMAND;
  const DecoratorNode$1 = mod.DecoratorNode;
  const ElementNode$1 = mod.ElementNode;
  const FOCUS_COMMAND$1 = mod.FOCUS_COMMAND;
  const FORMAT_ELEMENT_COMMAND$1 = mod.FORMAT_ELEMENT_COMMAND;
  const FORMAT_TEXT_COMMAND$1 = mod.FORMAT_TEXT_COMMAND;
  const INDENT_CONTENT_COMMAND$1 = mod.INDENT_CONTENT_COMMAND;
  const INSERT_LINE_BREAK_COMMAND$1 = mod.INSERT_LINE_BREAK_COMMAND;
  const INSERT_PARAGRAPH_COMMAND$1 = mod.INSERT_PARAGRAPH_COMMAND;
  const INSERT_TAB_COMMAND$1 = mod.INSERT_TAB_COMMAND;
  const IS_ALL_FORMATTING$1 = mod.IS_ALL_FORMATTING;
  const IS_BOLD$1 = mod.IS_BOLD;
  const IS_CODE$1 = mod.IS_CODE;
  const IS_HIGHLIGHT$1 = mod.IS_HIGHLIGHT;
  const IS_ITALIC$1 = mod.IS_ITALIC;
  const IS_STRIKETHROUGH$1 = mod.IS_STRIKETHROUGH;
  const IS_SUBSCRIPT$1 = mod.IS_SUBSCRIPT;
  const IS_SUPERSCRIPT$1 = mod.IS_SUPERSCRIPT;
  const IS_UNDERLINE$1 = mod.IS_UNDERLINE;
  const KEY_ARROW_DOWN_COMMAND$1 = mod.KEY_ARROW_DOWN_COMMAND;
  const KEY_ARROW_LEFT_COMMAND$1 = mod.KEY_ARROW_LEFT_COMMAND;
  const KEY_ARROW_RIGHT_COMMAND$1 = mod.KEY_ARROW_RIGHT_COMMAND;
  const KEY_ARROW_UP_COMMAND$1 = mod.KEY_ARROW_UP_COMMAND;
  const KEY_BACKSPACE_COMMAND$1 = mod.KEY_BACKSPACE_COMMAND;
  const KEY_DELETE_COMMAND$1 = mod.KEY_DELETE_COMMAND;
  const KEY_DOWN_COMMAND$1 = mod.KEY_DOWN_COMMAND;
  const KEY_ENTER_COMMAND$1 = mod.KEY_ENTER_COMMAND;
  const KEY_ESCAPE_COMMAND$1 = mod.KEY_ESCAPE_COMMAND;
  const KEY_MODIFIER_COMMAND$1 = mod.KEY_MODIFIER_COMMAND;
  const KEY_SPACE_COMMAND$1 = mod.KEY_SPACE_COMMAND;
  const KEY_TAB_COMMAND$1 = mod.KEY_TAB_COMMAND;
  const LineBreakNode$1 = mod.LineBreakNode;
  const MOVE_TO_END$1 = mod.MOVE_TO_END;
  const MOVE_TO_START$1 = mod.MOVE_TO_START;
  const OUTDENT_CONTENT_COMMAND$1 = mod.OUTDENT_CONTENT_COMMAND;
  const PASTE_COMMAND$1 = mod.PASTE_COMMAND;
  const ParagraphNode$1 = mod.ParagraphNode;
  const REDO_COMMAND$1 = mod.REDO_COMMAND;
  const REMOVE_TEXT_COMMAND$1 = mod.REMOVE_TEXT_COMMAND;
  const RootNode$1 = mod.RootNode;
  const SELECTION_CHANGE_COMMAND$1 = mod.SELECTION_CHANGE_COMMAND;
  const SELECTION_INSERT_CLIPBOARD_NODES_COMMAND$1 = mod.SELECTION_INSERT_CLIPBOARD_NODES_COMMAND;
  const SELECT_ALL_COMMAND$1 = mod.SELECT_ALL_COMMAND;
  const TEXT_TYPE_TO_FORMAT$1 = mod.TEXT_TYPE_TO_FORMAT;
  const TabNode$1 = mod.TabNode;
  const TextNode$1 = mod.TextNode;
  const UNDO_COMMAND$1 = mod.UNDO_COMMAND;
  const createCommand$1 = mod.createCommand;
  const createEditor$1 = mod.createEditor;
  const getEditorPropertyFromDOMNode$1 = mod.getEditorPropertyFromDOMNode;
  const getNearestEditorFromDOMNode$1 = mod.getNearestEditorFromDOMNode;
  const isBlockDomNode$1 = mod.isBlockDomNode;
  const isCurrentlyReadOnlyMode$1 = mod.isCurrentlyReadOnlyMode;
  const isHTMLAnchorElement$1 = mod.isHTMLAnchorElement;
  const isHTMLElement$1 = mod.isHTMLElement;
  const isInlineDomNode$1 = mod.isInlineDomNode;
  const isLexicalEditor$1 = mod.isLexicalEditor;
  const isSelectionCapturedInDecoratorInput$1 = mod.isSelectionCapturedInDecoratorInput;
  const isSelectionWithinEditor$1 = mod.isSelectionWithinEditor;
  const resetRandomKey$1 = mod.resetRandomKey;

  var Lexical = /*#__PURE__*/Object.freeze({
    $addUpdateTag: $addUpdateTag$1,
    $applyNodeReplacement: $applyNodeReplacement$1,
    $cloneWithProperties: $cloneWithProperties$1,
    $copyNode: $copyNode$1,
    $createLineBreakNode: $createLineBreakNode$1,
    $createNodeSelection: $createNodeSelection$1,
    $createParagraphNode: $createParagraphNode$1,
    $createPoint: $createPoint$1,
    $createRangeSelection: $createRangeSelection$1,
    $createRangeSelectionFromDom: $createRangeSelectionFromDom$1,
    $createTabNode: $createTabNode$1,
    $createTextNode: $createTextNode$1,
    $getAdjacentNode: $getAdjacentNode$1,
    $getCharacterOffsets: $getCharacterOffsets$1,
    $getEditor: $getEditor$1,
    $getNearestNodeFromDOMNode: $getNearestNodeFromDOMNode$1,
    $getNearestRootOrShadowRoot: $getNearestRootOrShadowRoot$1,
    $getNodeByKey: $getNodeByKey$1,
    $getNodeByKeyOrThrow: $getNodeByKeyOrThrow$1,
    $getPreviousSelection: $getPreviousSelection$1,
    $getRoot: $getRoot$1,
    $getSelection: $getSelection$1,
    $getTextContent: $getTextContent$1,
    $hasAncestor: $hasAncestor$1,
    $hasUpdateTag: $hasUpdateTag$1,
    $insertNodes: $insertNodes$1,
    $isBlockElementNode: $isBlockElementNode$1,
    $isDecoratorNode: $isDecoratorNode$1,
    $isElementNode: $isElementNode$1,
    $isInlineElementOrDecoratorNode: $isInlineElementOrDecoratorNode$1,
    $isLeafNode: $isLeafNode$1,
    $isLineBreakNode: $isLineBreakNode$1,
    $isNodeSelection: $isNodeSelection$1,
    $isParagraphNode: $isParagraphNode$1,
    $isRangeSelection: $isRangeSelection$1,
    $isRootNode: $isRootNode$1,
    $isRootOrShadowRoot: $isRootOrShadowRoot$1,
    $isTabNode: $isTabNode$1,
    $isTextNode: $isTextNode$1,
    $isTokenOrSegmented: $isTokenOrSegmented$1,
    $nodesOfType: $nodesOfType$1,
    $normalizeSelection__EXPERIMENTAL: $normalizeSelection__EXPERIMENTAL,
    $parseSerializedNode: $parseSerializedNode$1,
    $selectAll: $selectAll$1,
    $setCompositionKey: $setCompositionKey$1,
    $setSelection: $setSelection$1,
    $splitNode: $splitNode$1,
    ArtificialNode__DO_NOT_USE: ArtificialNode__DO_NOT_USE$1,
    BLUR_COMMAND: BLUR_COMMAND$1,
    CAN_REDO_COMMAND: CAN_REDO_COMMAND$1,
    CAN_UNDO_COMMAND: CAN_UNDO_COMMAND$1,
    CLEAR_EDITOR_COMMAND: CLEAR_EDITOR_COMMAND$1,
    CLEAR_HISTORY_COMMAND: CLEAR_HISTORY_COMMAND$1,
    CLICK_COMMAND: CLICK_COMMAND$1,
    COMMAND_PRIORITY_CRITICAL: COMMAND_PRIORITY_CRITICAL$1,
    COMMAND_PRIORITY_EDITOR: COMMAND_PRIORITY_EDITOR$1,
    COMMAND_PRIORITY_HIGH: COMMAND_PRIORITY_HIGH$1,
    COMMAND_PRIORITY_LOW: COMMAND_PRIORITY_LOW$1,
    COMMAND_PRIORITY_NORMAL: COMMAND_PRIORITY_NORMAL$1,
    CONTROLLED_TEXT_INSERTION_COMMAND: CONTROLLED_TEXT_INSERTION_COMMAND$1,
    COPY_COMMAND: COPY_COMMAND$1,
    CUT_COMMAND: CUT_COMMAND$1,
    DELETE_CHARACTER_COMMAND: DELETE_CHARACTER_COMMAND$1,
    DELETE_LINE_COMMAND: DELETE_LINE_COMMAND$1,
    DELETE_WORD_COMMAND: DELETE_WORD_COMMAND$1,
    DRAGEND_COMMAND: DRAGEND_COMMAND$1,
    DRAGOVER_COMMAND: DRAGOVER_COMMAND$1,
    DRAGSTART_COMMAND: DRAGSTART_COMMAND$1,
    DROP_COMMAND: DROP_COMMAND$1,
    DecoratorNode: DecoratorNode$1,
    ElementNode: ElementNode$1,
    FOCUS_COMMAND: FOCUS_COMMAND$1,
    FORMAT_ELEMENT_COMMAND: FORMAT_ELEMENT_COMMAND$1,
    FORMAT_TEXT_COMMAND: FORMAT_TEXT_COMMAND$1,
    INDENT_CONTENT_COMMAND: INDENT_CONTENT_COMMAND$1,
    INSERT_LINE_BREAK_COMMAND: INSERT_LINE_BREAK_COMMAND$1,
    INSERT_PARAGRAPH_COMMAND: INSERT_PARAGRAPH_COMMAND$1,
    INSERT_TAB_COMMAND: INSERT_TAB_COMMAND$1,
    IS_ALL_FORMATTING: IS_ALL_FORMATTING$1,
    IS_BOLD: IS_BOLD$1,
    IS_CODE: IS_CODE$1,
    IS_HIGHLIGHT: IS_HIGHLIGHT$1,
    IS_ITALIC: IS_ITALIC$1,
    IS_STRIKETHROUGH: IS_STRIKETHROUGH$1,
    IS_SUBSCRIPT: IS_SUBSCRIPT$1,
    IS_SUPERSCRIPT: IS_SUPERSCRIPT$1,
    IS_UNDERLINE: IS_UNDERLINE$1,
    KEY_ARROW_DOWN_COMMAND: KEY_ARROW_DOWN_COMMAND$1,
    KEY_ARROW_LEFT_COMMAND: KEY_ARROW_LEFT_COMMAND$1,
    KEY_ARROW_RIGHT_COMMAND: KEY_ARROW_RIGHT_COMMAND$1,
    KEY_ARROW_UP_COMMAND: KEY_ARROW_UP_COMMAND$1,
    KEY_BACKSPACE_COMMAND: KEY_BACKSPACE_COMMAND$1,
    KEY_DELETE_COMMAND: KEY_DELETE_COMMAND$1,
    KEY_DOWN_COMMAND: KEY_DOWN_COMMAND$1,
    KEY_ENTER_COMMAND: KEY_ENTER_COMMAND$1,
    KEY_ESCAPE_COMMAND: KEY_ESCAPE_COMMAND$1,
    KEY_MODIFIER_COMMAND: KEY_MODIFIER_COMMAND$1,
    KEY_SPACE_COMMAND: KEY_SPACE_COMMAND$1,
    KEY_TAB_COMMAND: KEY_TAB_COMMAND$1,
    LineBreakNode: LineBreakNode$1,
    MOVE_TO_END: MOVE_TO_END$1,
    MOVE_TO_START: MOVE_TO_START$1,
    OUTDENT_CONTENT_COMMAND: OUTDENT_CONTENT_COMMAND$1,
    PASTE_COMMAND: PASTE_COMMAND$1,
    ParagraphNode: ParagraphNode$1,
    REDO_COMMAND: REDO_COMMAND$1,
    REMOVE_TEXT_COMMAND: REMOVE_TEXT_COMMAND$1,
    RootNode: RootNode$1,
    SELECTION_CHANGE_COMMAND: SELECTION_CHANGE_COMMAND$1,
    SELECTION_INSERT_CLIPBOARD_NODES_COMMAND: SELECTION_INSERT_CLIPBOARD_NODES_COMMAND$1,
    SELECT_ALL_COMMAND: SELECT_ALL_COMMAND$1,
    TEXT_TYPE_TO_FORMAT: TEXT_TYPE_TO_FORMAT$1,
    TabNode: TabNode$1,
    TextNode: TextNode$1,
    UNDO_COMMAND: UNDO_COMMAND$1,
    createCommand: createCommand$1,
    createEditor: createEditor$1,
    getEditorPropertyFromDOMNode: getEditorPropertyFromDOMNode$1,
    getNearestEditorFromDOMNode: getNearestEditorFromDOMNode$1,
    isBlockDomNode: isBlockDomNode$1,
    isCurrentlyReadOnlyMode: isCurrentlyReadOnlyMode$1,
    isHTMLAnchorElement: isHTMLAnchorElement$1,
    isHTMLElement: isHTMLElement$1,
    isInlineDomNode: isInlineDomNode$1,
    isLexicalEditor: isLexicalEditor$1,
    isSelectionCapturedInDecoratorInput: isSelectionCapturedInDecoratorInput$1,
    isSelectionWithinEditor: isSelectionWithinEditor$1,
    resetRandomKey: resetRandomKey$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const CSS_TO_STYLES = new Map();

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function getDOMTextNode$1(element) {
    let node = element;
    while (node != null) {
      if (node.nodeType === Node.TEXT_NODE) {
        return node;
      }
      node = node.firstChild;
    }
    return null;
  }
  function getDOMIndexWithinParent(node) {
    const parent = node.parentNode;
    if (parent == null) {
      throw new Error('Should never happen');
    }
    return [parent, Array.from(parent.childNodes).indexOf(node)];
  }

  /**
   * Creates a selection range for the DOM.
   * @param editor - The lexical editor.
   * @param anchorNode - The anchor node of a selection.
   * @param _anchorOffset - The amount of space offset from the anchor to the focus.
   * @param focusNode - The current focus.
   * @param _focusOffset - The amount of space offset from the focus to the anchor.
   * @returns The range of selection for the DOM that was created.
   */
  function createDOMRange(editor, anchorNode, _anchorOffset, focusNode, _focusOffset) {
    const anchorKey = anchorNode.getKey();
    const focusKey = focusNode.getKey();
    const range = document.createRange();
    let anchorDOM = editor.getElementByKey(anchorKey);
    let focusDOM = editor.getElementByKey(focusKey);
    let anchorOffset = _anchorOffset;
    let focusOffset = _focusOffset;
    if ($isTextNode$1(anchorNode)) {
      anchorDOM = getDOMTextNode$1(anchorDOM);
    }
    if ($isTextNode$1(focusNode)) {
      focusDOM = getDOMTextNode$1(focusDOM);
    }
    if (anchorNode === undefined || focusNode === undefined || anchorDOM === null || focusDOM === null) {
      return null;
    }
    if (anchorDOM.nodeName === 'BR') {
      [anchorDOM, anchorOffset] = getDOMIndexWithinParent(anchorDOM);
    }
    if (focusDOM.nodeName === 'BR') {
      [focusDOM, focusOffset] = getDOMIndexWithinParent(focusDOM);
    }
    const firstChild = anchorDOM.firstChild;
    if (anchorDOM === focusDOM && firstChild != null && firstChild.nodeName === 'BR' && anchorOffset === 0 && focusOffset === 0) {
      focusOffset = 1;
    }
    try {
      range.setStart(anchorDOM, anchorOffset);
      range.setEnd(focusDOM, focusOffset);
    } catch (e) {
      return null;
    }
    if (range.collapsed && (anchorOffset !== focusOffset || anchorKey !== focusKey)) {
      // Range is backwards, we need to reverse it
      range.setStart(focusDOM, focusOffset);
      range.setEnd(anchorDOM, anchorOffset);
    }
    return range;
  }

  /**
   * Creates DOMRects, generally used to help the editor find a specific location on the screen.
   * @param editor - The lexical editor
   * @param range - A fragment of a document that can contain nodes and parts of text nodes.
   * @returns The selectionRects as an array.
   */
  function createRectsFromDOMRange(editor, range) {
    const rootElement = editor.getRootElement();
    if (rootElement === null) {
      return [];
    }
    const rootRect = rootElement.getBoundingClientRect();
    const computedStyle = getComputedStyle(rootElement);
    const rootPadding = parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
    const selectionRects = Array.from(range.getClientRects());
    let selectionRectsLength = selectionRects.length;
    //sort rects from top left to bottom right.
    selectionRects.sort((a, b) => {
      const top = a.top - b.top;
      // Some rects match position closely, but not perfectly,
      // so we give a 3px tolerance.
      if (Math.abs(top) <= 3) {
        return a.left - b.left;
      }
      return top;
    });
    let prevRect;
    for (let i = 0; i < selectionRectsLength; i++) {
      const selectionRect = selectionRects[i];
      // Exclude rects that overlap preceding Rects in the sorted list.
      const isOverlappingRect = prevRect && prevRect.top <= selectionRect.top && prevRect.top + prevRect.height > selectionRect.top && prevRect.left + prevRect.width > selectionRect.left;
      // Exclude selections that span the entire element
      const selectionSpansElement = selectionRect.width + rootPadding === rootRect.width;
      if (isOverlappingRect || selectionSpansElement) {
        selectionRects.splice(i--, 1);
        selectionRectsLength--;
        continue;
      }
      prevRect = selectionRect;
    }
    return selectionRects;
  }

  /**
   * Creates an object containing all the styles and their values provided in the CSS string.
   * @param css - The CSS string of styles and their values.
   * @returns The styleObject containing all the styles and their values.
   */
  function getStyleObjectFromRawCSS(css) {
    const styleObject = {};
    const styles = css.split(';');
    for (const style of styles) {
      if (style !== '') {
        const [key, value] = style.split(/:([^]+)/); // split on first colon
        if (key && value) {
          styleObject[key.trim()] = value.trim();
        }
      }
    }
    return styleObject;
  }

  /**
   * Given a CSS string, returns an object from the style cache.
   * @param css - The CSS property as a string.
   * @returns The value of the given CSS property.
   */
  function getStyleObjectFromCSS(css) {
    let value = CSS_TO_STYLES.get(css);
    if (value === undefined) {
      value = getStyleObjectFromRawCSS(css);
      CSS_TO_STYLES.set(css, value);
    }
    {
      // Freeze the value in DEV to prevent accidental mutations
      Object.freeze(value);
    }
    return value;
  }

  /**
   * Gets the CSS styles from the style object.
   * @param styles - The style object containing the styles to get.
   * @returns A string containing the CSS styles and their values.
   */
  function getCSSFromStyleObject(styles) {
    let css = '';
    for (const style in styles) {
      if (style) {
        css += `${style}: ${styles[style]};`;
      }
    }
    return css;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Generally used to append text content to HTML and JSON. Grabs the text content and "slices"
   * it to be generated into the new TextNode.
   * @param selection - The selection containing the node whose TextNode is to be edited.
   * @param textNode - The TextNode to be edited.
   * @returns The updated TextNode.
   */
  function $sliceSelectedTextNodeContent(selection, textNode) {
    const anchorAndFocus = selection.getStartEndPoints();
    if (textNode.isSelected(selection) && !textNode.isSegmented() && !textNode.isToken() && anchorAndFocus !== null) {
      const [anchor, focus] = anchorAndFocus;
      const isBackward = selection.isBackward();
      const anchorNode = anchor.getNode();
      const focusNode = focus.getNode();
      const isAnchor = textNode.is(anchorNode);
      const isFocus = textNode.is(focusNode);
      if (isAnchor || isFocus) {
        const [anchorOffset, focusOffset] = $getCharacterOffsets$1(selection);
        const isSame = anchorNode.is(focusNode);
        const isFirst = textNode.is(isBackward ? focusNode : anchorNode);
        const isLast = textNode.is(isBackward ? anchorNode : focusNode);
        let startOffset = 0;
        let endOffset = undefined;
        if (isSame) {
          startOffset = anchorOffset > focusOffset ? focusOffset : anchorOffset;
          endOffset = anchorOffset > focusOffset ? anchorOffset : focusOffset;
        } else if (isFirst) {
          const offset = isBackward ? focusOffset : anchorOffset;
          startOffset = offset;
          endOffset = undefined;
        } else if (isLast) {
          const offset = isBackward ? anchorOffset : focusOffset;
          startOffset = 0;
          endOffset = offset;
        }
        textNode.__text = textNode.__text.slice(startOffset, endOffset);
        return textNode;
      }
    }
    return textNode;
  }

  /**
   * Determines if the current selection is at the end of the node.
   * @param point - The point of the selection to test.
   * @returns true if the provided point offset is in the last possible position, false otherwise.
   */
  function $isAtNodeEnd(point) {
    if (point.type === 'text') {
      return point.offset === point.getNode().getTextContentSize();
    }
    const node = point.getNode();
    if (!$isElementNode$1(node)) {
      throw Error(`isAtNodeEnd: node must be a TextNode or ElementNode`);
    }
    return point.offset === node.getChildrenSize();
  }

  /**
   * Trims text from a node in order to shorten it, eg. to enforce a text's max length. If it deletes text
   * that is an ancestor of the anchor then it will leave 2 indents, otherwise, if no text content exists, it deletes
   * the TextNode. It will move the focus to either the end of any left over text or beginning of a new TextNode.
   * @param editor - The lexical editor.
   * @param anchor - The anchor of the current selection, where the selection should be pointing.
   * @param delCount - The amount of characters to delete. Useful as a dynamic variable eg. textContentSize - maxLength;
   */
  function $trimTextContentFromAnchor(editor, anchor, delCount) {
    // Work from the current selection anchor point
    let currentNode = anchor.getNode();
    let remaining = delCount;
    if ($isElementNode$1(currentNode)) {
      const descendantNode = currentNode.getDescendantByIndex(anchor.offset);
      if (descendantNode !== null) {
        currentNode = descendantNode;
      }
    }
    while (remaining > 0 && currentNode !== null) {
      if ($isElementNode$1(currentNode)) {
        const lastDescendant = currentNode.getLastDescendant();
        if (lastDescendant !== null) {
          currentNode = lastDescendant;
        }
      }
      let nextNode = currentNode.getPreviousSibling();
      let additionalElementWhitespace = 0;
      if (nextNode === null) {
        let parent = currentNode.getParentOrThrow();
        let parentSibling = parent.getPreviousSibling();
        while (parentSibling === null) {
          parent = parent.getParent();
          if (parent === null) {
            nextNode = null;
            break;
          }
          parentSibling = parent.getPreviousSibling();
        }
        if (parent !== null) {
          additionalElementWhitespace = parent.isInline() ? 0 : 2;
          nextNode = parentSibling;
        }
      }
      let text = currentNode.getTextContent();
      // If the text is empty, we need to consider adding in two line breaks to match
      // the content if we were to get it from its parent.
      if (text === '' && $isElementNode$1(currentNode) && !currentNode.isInline()) {
        // TODO: should this be handled in core?
        text = '\n\n';
      }
      const currentNodeSize = text.length;
      if (!$isTextNode$1(currentNode) || remaining >= currentNodeSize) {
        const parent = currentNode.getParent();
        currentNode.remove();
        if (parent != null && parent.getChildrenSize() === 0 && !$isRootNode$1(parent)) {
          parent.remove();
        }
        remaining -= currentNodeSize + additionalElementWhitespace;
        currentNode = nextNode;
      } else {
        const key = currentNode.getKey();
        // See if we can just revert it to what was in the last editor state
        const prevTextContent = editor.getEditorState().read(() => {
          const prevNode = $getNodeByKey$1(key);
          if ($isTextNode$1(prevNode) && prevNode.isSimpleText()) {
            return prevNode.getTextContent();
          }
          return null;
        });
        const offset = currentNodeSize - remaining;
        const slicedText = text.slice(0, offset);
        if (prevTextContent !== null && prevTextContent !== text) {
          const prevSelection = $getPreviousSelection$1();
          let target = currentNode;
          if (!currentNode.isSimpleText()) {
            const textNode = $createTextNode$1(prevTextContent);
            currentNode.replace(textNode);
            target = textNode;
          } else {
            currentNode.setTextContent(prevTextContent);
          }
          if ($isRangeSelection$1(prevSelection) && prevSelection.isCollapsed()) {
            const prevOffset = prevSelection.anchor.offset;
            target.select(prevOffset, prevOffset);
          }
        } else if (currentNode.isSimpleText()) {
          // Split text
          const isSelected = anchor.key === key;
          let anchorOffset = anchor.offset;
          // Move offset to end if it's less than the remaining number, otherwise
          // we'll have a negative splitStart.
          if (anchorOffset < remaining) {
            anchorOffset = currentNodeSize;
          }
          const splitStart = isSelected ? anchorOffset - remaining : 0;
          const splitEnd = isSelected ? anchorOffset : offset;
          if (isSelected && splitStart === 0) {
            const [excessNode] = currentNode.splitText(splitStart, splitEnd);
            excessNode.remove();
          } else {
            const [, excessNode] = currentNode.splitText(splitStart, splitEnd);
            excessNode.remove();
          }
        } else {
          const textNode = $createTextNode$1(slicedText);
          currentNode.replace(textNode);
        }
        remaining = 0;
      }
    }
  }

  /**
   * Gets the TextNode's style object and adds the styles to the CSS.
   * @param node - The TextNode to add styles to.
   */
  function $addNodeStyle(node) {
    const CSSText = node.getStyle();
    const styles = getStyleObjectFromRawCSS(CSSText);
    CSS_TO_STYLES.set(CSSText, styles);
  }
  function $patchStyle(target, patch) {
    const prevStyles = getStyleObjectFromCSS('getStyle' in target ? target.getStyle() : target.style);
    const newStyles = Object.entries(patch).reduce((styles, [key, value]) => {
      if (typeof value === 'function') {
        styles[key] = value(prevStyles[key], target);
      } else if (value === null) {
        delete styles[key];
      } else {
        styles[key] = value;
      }
      return styles;
    }, {
      ...prevStyles
    } || {});
    const newCSSText = getCSSFromStyleObject(newStyles);
    target.setStyle(newCSSText);
    CSS_TO_STYLES.set(newCSSText, newStyles);
  }

  /**
   * Applies the provided styles to the TextNodes in the provided Selection.
   * Will update partially selected TextNodes by splitting the TextNode and applying
   * the styles to the appropriate one.
   * @param selection - The selected node(s) to update.
   * @param patch - The patch to apply, which can include multiple styles. \\{CSSProperty: value\\} . Can also accept a function that returns the new property value.
   */
  function $patchStyleText(selection, patch) {
    const selectedNodes = selection.getNodes();
    const selectedNodesLength = selectedNodes.length;
    const anchorAndFocus = selection.getStartEndPoints();
    if (anchorAndFocus === null) {
      return;
    }
    const [anchor, focus] = anchorAndFocus;
    const lastIndex = selectedNodesLength - 1;
    let firstNode = selectedNodes[0];
    let lastNode = selectedNodes[lastIndex];
    if (selection.isCollapsed() && $isRangeSelection$1(selection)) {
      $patchStyle(selection, patch);
      return;
    }
    const firstNodeText = firstNode.getTextContent();
    const firstNodeTextLength = firstNodeText.length;
    const focusOffset = focus.offset;
    let anchorOffset = anchor.offset;
    const isBefore = anchor.isBefore(focus);
    let startOffset = isBefore ? anchorOffset : focusOffset;
    let endOffset = isBefore ? focusOffset : anchorOffset;
    const startType = isBefore ? anchor.type : focus.type;
    const endType = isBefore ? focus.type : anchor.type;
    const endKey = isBefore ? focus.key : anchor.key;

    // This is the case where the user only selected the very end of the
    // first node so we don't want to include it in the formatting change.
    if ($isTextNode$1(firstNode) && startOffset === firstNodeTextLength) {
      const nextSibling = firstNode.getNextSibling();
      if ($isTextNode$1(nextSibling)) {
        // we basically make the second node the firstNode, changing offsets accordingly
        anchorOffset = 0;
        startOffset = 0;
        firstNode = nextSibling;
      }
    }

    // This is the case where we only selected a single node
    if (selectedNodes.length === 1) {
      if ($isTextNode$1(firstNode) && firstNode.canHaveFormat()) {
        startOffset = startType === 'element' ? 0 : anchorOffset > focusOffset ? focusOffset : anchorOffset;
        endOffset = endType === 'element' ? firstNodeTextLength : anchorOffset > focusOffset ? anchorOffset : focusOffset;

        // No actual text is selected, so do nothing.
        if (startOffset === endOffset) {
          return;
        }

        // The entire node is selected or a token/segment, so just format it
        if ($isTokenOrSegmented$1(firstNode) || startOffset === 0 && endOffset === firstNodeTextLength) {
          $patchStyle(firstNode, patch);
          firstNode.select(startOffset, endOffset);
        } else {
          // The node is partially selected, so split it into two nodes
          // and style the selected one.
          const splitNodes = firstNode.splitText(startOffset, endOffset);
          const replacement = startOffset === 0 ? splitNodes[0] : splitNodes[1];
          $patchStyle(replacement, patch);
          replacement.select(0, endOffset - startOffset);
        }
      } // multiple nodes selected.
    } else {
      if ($isTextNode$1(firstNode) && startOffset < firstNode.getTextContentSize() && firstNode.canHaveFormat()) {
        if (startOffset !== 0 && !$isTokenOrSegmented$1(firstNode)) {
          // the entire first node isn't selected and it isn't a token or segmented, so split it
          firstNode = firstNode.splitText(startOffset)[1];
          startOffset = 0;
          if (isBefore) {
            anchor.set(firstNode.getKey(), startOffset, 'text');
          } else {
            focus.set(firstNode.getKey(), startOffset, 'text');
          }
        }
        $patchStyle(firstNode, patch);
      }
      if ($isTextNode$1(lastNode) && lastNode.canHaveFormat()) {
        const lastNodeText = lastNode.getTextContent();
        const lastNodeTextLength = lastNodeText.length;

        // The last node might not actually be the end node
        //
        // If not, assume the last node is fully-selected unless the end offset is
        // zero.
        if (lastNode.__key !== endKey && endOffset !== 0) {
          endOffset = lastNodeTextLength;
        }

        // if the entire last node isn't selected and it isn't a token or segmented, split it
        if (endOffset !== lastNodeTextLength && !$isTokenOrSegmented$1(lastNode)) {
          [lastNode] = lastNode.splitText(endOffset);
        }
        if (endOffset !== 0 || endType === 'element') {
          $patchStyle(lastNode, patch);
        }
      }

      // style all the text nodes in between
      for (let i = 1; i < lastIndex; i++) {
        const selectedNode = selectedNodes[i];
        const selectedNodeKey = selectedNode.getKey();
        if ($isTextNode$1(selectedNode) && selectedNode.canHaveFormat() && selectedNodeKey !== firstNode.getKey() && selectedNodeKey !== lastNode.getKey() && !selectedNode.isToken()) {
          $patchStyle(selectedNode, patch);
        }
      }
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Converts all nodes in the selection that are of one block type to another.
   * @param selection - The selected blocks to be converted.
   * @param createElement - The function that creates the node. eg. $createParagraphNode.
   */
  function $setBlocksType(selection, createElement) {
    if (selection === null) {
      return;
    }
    const anchorAndFocus = selection.getStartEndPoints();
    const anchor = anchorAndFocus ? anchorAndFocus[0] : null;
    if (anchor !== null && anchor.key === 'root') {
      const element = createElement();
      const root = $getRoot$1();
      const firstChild = root.getFirstChild();
      if (firstChild) {
        firstChild.replace(element, true);
      } else {
        root.append(element);
      }
      return;
    }
    const nodes = selection.getNodes();
    const firstSelectedBlock = anchor !== null ? $getAncestor$1(anchor.getNode(), INTERNAL_$isBlock$1) : false;
    if (firstSelectedBlock && nodes.indexOf(firstSelectedBlock) === -1) {
      nodes.push(firstSelectedBlock);
    }
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      if (!INTERNAL_$isBlock$1(node)) {
        continue;
      }
      if (!$isElementNode$1(node)) {
        throw Error(`Expected block node to be an ElementNode`);
      }
      const targetElement = createElement();
      targetElement.setFormat(node.getFormatType());
      targetElement.setIndent(node.getIndent());
      node.replace(targetElement, true);
    }
  }
  function isPointAttached(point) {
    return point.getNode().isAttached();
  }
  function $removeParentEmptyElements(startingNode) {
    let node = startingNode;
    while (node !== null && !$isRootOrShadowRoot$1(node)) {
      const latest = node.getLatest();
      const parentNode = node.getParent();
      if (latest.getChildrenSize() === 0) {
        node.remove(true);
      }
      node = parentNode;
    }
  }

  /**
   * @deprecated
   * Wraps all nodes in the selection into another node of the type returned by createElement.
   * @param selection - The selection of nodes to be wrapped.
   * @param createElement - A function that creates the wrapping ElementNode. eg. $createParagraphNode.
   * @param wrappingElement - An element to append the wrapped selection and its children to.
   */
  function $wrapNodes(selection, createElement, wrappingElement = null) {
    const anchorAndFocus = selection.getStartEndPoints();
    const anchor = anchorAndFocus ? anchorAndFocus[0] : null;
    const nodes = selection.getNodes();
    const nodesLength = nodes.length;
    if (anchor !== null && (nodesLength === 0 || nodesLength === 1 && anchor.type === 'element' && anchor.getNode().getChildrenSize() === 0)) {
      const target = anchor.type === 'text' ? anchor.getNode().getParentOrThrow() : anchor.getNode();
      const children = target.getChildren();
      let element = createElement();
      element.setFormat(target.getFormatType());
      element.setIndent(target.getIndent());
      children.forEach(child => element.append(child));
      if (wrappingElement) {
        element = wrappingElement.append(element);
      }
      target.replace(element);
      return;
    }
    let topLevelNode = null;
    let descendants = [];
    for (let i = 0; i < nodesLength; i++) {
      const node = nodes[i];
      // Determine whether wrapping has to be broken down into multiple chunks. This can happen if the
      // user selected multiple Root-like nodes that have to be treated separately as if they are
      // their own branch. I.e. you don't want to wrap a whole table, but rather the contents of each
      // of each of the cell nodes.
      if ($isRootOrShadowRoot$1(node)) {
        $wrapNodesImpl(selection, descendants, descendants.length, createElement, wrappingElement);
        descendants = [];
        topLevelNode = node;
      } else if (topLevelNode === null || topLevelNode !== null && $hasAncestor$1(node, topLevelNode)) {
        descendants.push(node);
      } else {
        $wrapNodesImpl(selection, descendants, descendants.length, createElement, wrappingElement);
        descendants = [node];
      }
    }
    $wrapNodesImpl(selection, descendants, descendants.length, createElement, wrappingElement);
  }

  /**
   * Wraps each node into a new ElementNode.
   * @param selection - The selection of nodes to wrap.
   * @param nodes - An array of nodes, generally the descendants of the selection.
   * @param nodesLength - The length of nodes.
   * @param createElement - A function that creates the wrapping ElementNode. eg. $createParagraphNode.
   * @param wrappingElement - An element to wrap all the nodes into.
   * @returns
   */
  function $wrapNodesImpl(selection, nodes, nodesLength, createElement, wrappingElement = null) {
    if (nodes.length === 0) {
      return;
    }
    const firstNode = nodes[0];
    const elementMapping = new Map();
    const elements = [];
    // The below logic is to find the right target for us to
    // either insertAfter/insertBefore/append the corresponding
    // elements to. This is made more complicated due to nested
    // structures.
    let target = $isElementNode$1(firstNode) ? firstNode : firstNode.getParentOrThrow();
    if (target.isInline()) {
      target = target.getParentOrThrow();
    }
    let targetIsPrevSibling = false;
    while (target !== null) {
      const prevSibling = target.getPreviousSibling();
      if (prevSibling !== null) {
        target = prevSibling;
        targetIsPrevSibling = true;
        break;
      }
      target = target.getParentOrThrow();
      if ($isRootOrShadowRoot$1(target)) {
        break;
      }
    }
    const emptyElements = new Set();

    // Find any top level empty elements
    for (let i = 0; i < nodesLength; i++) {
      const node = nodes[i];
      if ($isElementNode$1(node) && node.getChildrenSize() === 0) {
        emptyElements.add(node.getKey());
      }
    }
    const movedNodes = new Set();

    // Move out all leaf nodes into our elements array.
    // If we find a top level empty element, also move make
    // an element for that.
    for (let i = 0; i < nodesLength; i++) {
      const node = nodes[i];
      let parent = node.getParent();
      if (parent !== null && parent.isInline()) {
        parent = parent.getParent();
      }
      if (parent !== null && $isLeafNode$1(node) && !movedNodes.has(node.getKey())) {
        const parentKey = parent.getKey();
        if (elementMapping.get(parentKey) === undefined) {
          const targetElement = createElement();
          targetElement.setFormat(parent.getFormatType());
          targetElement.setIndent(parent.getIndent());
          elements.push(targetElement);
          elementMapping.set(parentKey, targetElement);
          // Move node and its siblings to the new
          // element.
          parent.getChildren().forEach(child => {
            targetElement.append(child);
            movedNodes.add(child.getKey());
            if ($isElementNode$1(child)) {
              // Skip nested leaf nodes if the parent has already been moved
              child.getChildrenKeys().forEach(key => movedNodes.add(key));
            }
          });
          $removeParentEmptyElements(parent);
        }
      } else if (emptyElements.has(node.getKey())) {
        if (!$isElementNode$1(node)) {
          throw Error(`Expected node in emptyElements to be an ElementNode`);
        }
        const targetElement = createElement();
        targetElement.setFormat(node.getFormatType());
        targetElement.setIndent(node.getIndent());
        elements.push(targetElement);
        node.remove(true);
      }
    }
    if (wrappingElement !== null) {
      for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        wrappingElement.append(element);
      }
    }
    let lastElement = null;

    // If our target is Root-like, let's see if we can re-adjust
    // so that the target is the first child instead.
    if ($isRootOrShadowRoot$1(target)) {
      if (targetIsPrevSibling) {
        if (wrappingElement !== null) {
          target.insertAfter(wrappingElement);
        } else {
          for (let i = elements.length - 1; i >= 0; i--) {
            const element = elements[i];
            target.insertAfter(element);
          }
        }
      } else {
        const firstChild = target.getFirstChild();
        if ($isElementNode$1(firstChild)) {
          target = firstChild;
        }
        if (firstChild === null) {
          if (wrappingElement) {
            target.append(wrappingElement);
          } else {
            for (let i = 0; i < elements.length; i++) {
              const element = elements[i];
              target.append(element);
              lastElement = element;
            }
          }
        } else {
          if (wrappingElement !== null) {
            firstChild.insertBefore(wrappingElement);
          } else {
            for (let i = 0; i < elements.length; i++) {
              const element = elements[i];
              firstChild.insertBefore(element);
              lastElement = element;
            }
          }
        }
      }
    } else {
      if (wrappingElement) {
        target.insertAfter(wrappingElement);
      } else {
        for (let i = elements.length - 1; i >= 0; i--) {
          const element = elements[i];
          target.insertAfter(element);
          lastElement = element;
        }
      }
    }
    const prevSelection = $getPreviousSelection$1();
    if ($isRangeSelection$1(prevSelection) && isPointAttached(prevSelection.anchor) && isPointAttached(prevSelection.focus)) {
      $setSelection$1(prevSelection.clone());
    } else if (lastElement !== null) {
      lastElement.selectEnd();
    } else {
      selection.dirty = true;
    }
  }

  /**
   * Determines if the default character selection should be overridden. Used with DecoratorNodes
   * @param selection - The selection whose default character selection may need to be overridden.
   * @param isBackward - Is the selection backwards (the focus comes before the anchor)?
   * @returns true if it should be overridden, false if not.
   */
  function $shouldOverrideDefaultCharacterSelection(selection, isBackward) {
    const possibleNode = $getAdjacentNode$1(selection.focus, isBackward);
    return $isDecoratorNode$1(possibleNode) && !possibleNode.isIsolated() || $isElementNode$1(possibleNode) && !possibleNode.isInline() && !possibleNode.canBeEmpty();
  }

  /**
   * Moves the selection according to the arguments.
   * @param selection - The selected text or nodes.
   * @param isHoldingShift - Is the shift key being held down during the operation.
   * @param isBackward - Is the selection selected backwards (the focus comes before the anchor)?
   * @param granularity - The distance to adjust the current selection.
   */
  function $moveCaretSelection(selection, isHoldingShift, isBackward, granularity) {
    selection.modify(isHoldingShift ? 'extend' : 'move', isBackward, granularity);
  }

  /**
   * Tests a parent element for right to left direction.
   * @param selection - The selection whose parent is to be tested.
   * @returns true if the selections' parent element has a direction of 'rtl' (right to left), false otherwise.
   */
  function $isParentElementRTL(selection) {
    const anchorNode = selection.anchor.getNode();
    const parent = $isRootNode$1(anchorNode) ? anchorNode : anchorNode.getParentOrThrow();
    return parent.getDirection() === 'rtl';
  }

  /**
   * Moves selection by character according to arguments.
   * @param selection - The selection of the characters to move.
   * @param isHoldingShift - Is the shift key being held down during the operation.
   * @param isBackward - Is the selection backward (the focus comes before the anchor)?
   */
  function $moveCharacter(selection, isHoldingShift, isBackward) {
    const isRTL = $isParentElementRTL(selection);
    $moveCaretSelection(selection, isHoldingShift, isBackward ? !isRTL : isRTL, 'character');
  }

  /**
   * Expands the current Selection to cover all of the content in the editor.
   * @param selection - The current selection.
   */
  function $selectAll$2(selection) {
    const anchor = selection.anchor;
    const focus = selection.focus;
    const anchorNode = anchor.getNode();
    const topParent = anchorNode.getTopLevelElementOrThrow();
    const root = topParent.getParentOrThrow();
    let firstNode = root.getFirstDescendant();
    let lastNode = root.getLastDescendant();
    let firstType = 'element';
    let lastType = 'element';
    let lastOffset = 0;
    if ($isTextNode$1(firstNode)) {
      firstType = 'text';
    } else if (!$isElementNode$1(firstNode) && firstNode !== null) {
      firstNode = firstNode.getParentOrThrow();
    }
    if ($isTextNode$1(lastNode)) {
      lastType = 'text';
      lastOffset = lastNode.getTextContentSize();
    } else if (!$isElementNode$1(lastNode) && lastNode !== null) {
      lastNode = lastNode.getParentOrThrow();
    }
    if (firstNode && lastNode) {
      anchor.set(firstNode.getKey(), 0, firstType);
      focus.set(lastNode.getKey(), lastOffset, lastType);
    }
  }

  /**
   * Returns the current value of a CSS property for Nodes, if set. If not set, it returns the defaultValue.
   * @param node - The node whose style value to get.
   * @param styleProperty - The CSS style property.
   * @param defaultValue - The default value for the property.
   * @returns The value of the property for node.
   */
  function $getNodeStyleValueForProperty(node, styleProperty, defaultValue) {
    const css = node.getStyle();
    const styleObject = getStyleObjectFromCSS(css);
    if (styleObject !== null) {
      return styleObject[styleProperty] || defaultValue;
    }
    return defaultValue;
  }

  /**
   * Returns the current value of a CSS property for TextNodes in the Selection, if set. If not set, it returns the defaultValue.
   * If all TextNodes do not have the same value, it returns an empty string.
   * @param selection - The selection of TextNodes whose value to find.
   * @param styleProperty - The CSS style property.
   * @param defaultValue - The default value for the property, defaults to an empty string.
   * @returns The value of the property for the selected TextNodes.
   */
  function $getSelectionStyleValueForProperty(selection, styleProperty, defaultValue = '') {
    let styleValue = null;
    const nodes = selection.getNodes();
    const anchor = selection.anchor;
    const focus = selection.focus;
    const isBackward = selection.isBackward();
    const endOffset = isBackward ? focus.offset : anchor.offset;
    const endNode = isBackward ? focus.getNode() : anchor.getNode();
    if ($isRangeSelection$1(selection) && selection.isCollapsed() && selection.style !== '') {
      const css = selection.style;
      const styleObject = getStyleObjectFromCSS(css);
      if (styleObject !== null && styleProperty in styleObject) {
        return styleObject[styleProperty];
      }
    }
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];

      // if no actual characters in the end node are selected, we don't
      // include it in the selection for purposes of determining style
      // value
      if (i !== 0 && endOffset === 0 && node.is(endNode)) {
        continue;
      }
      if ($isTextNode$1(node)) {
        const nodeStyleValue = $getNodeStyleValueForProperty(node, styleProperty, defaultValue);
        if (styleValue === null) {
          styleValue = nodeStyleValue;
        } else if (styleValue !== nodeStyleValue) {
          // multiple text nodes are in the selection and they don't all
          // have the same style.
          styleValue = '';
          break;
        }
      }
    }
    return styleValue === null ? defaultValue : styleValue;
  }

  /**
   * This function is for internal use of the library.
   * Please do not use it as it may change in the future.
   */
  function INTERNAL_$isBlock$1(node) {
    if ($isDecoratorNode$1(node)) {
      return false;
    }
    if (!$isElementNode$1(node) || $isRootOrShadowRoot$1(node)) {
      return false;
    }
    const firstChild = node.getFirstChild();
    const isLeafElement = firstChild === null || $isLineBreakNode$1(firstChild) || $isTextNode$1(firstChild) || firstChild.isInline();
    return !node.isInline() && node.canBeEmpty() !== false && isLeafElement;
  }
  function $getAncestor$1(node, predicate) {
    let parent = node;
    while (parent !== null && parent.getParent() !== null && !predicate(parent)) {
      parent = parent.getParentOrThrow();
    }
    return predicate(parent) ? parent : null;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @deprecated renamed to {@link $trimTextContentFromAnchor} by @lexical/eslint-plugin rules-of-lexical */
  const trimTextContentFromAnchor = $trimTextContentFromAnchor;

  var modDev$1 = /*#__PURE__*/Object.freeze({
    $addNodeStyle: $addNodeStyle,
    $getSelectionStyleValueForProperty: $getSelectionStyleValueForProperty,
    $isAtNodeEnd: $isAtNodeEnd,
    $isParentElementRTL: $isParentElementRTL,
    $moveCaretSelection: $moveCaretSelection,
    $moveCharacter: $moveCharacter,
    $patchStyleText: $patchStyleText,
    $selectAll: $selectAll$2,
    $setBlocksType: $setBlocksType,
    $shouldOverrideDefaultCharacterSelection: $shouldOverrideDefaultCharacterSelection,
    $sliceSelectedTextNodeContent: $sliceSelectedTextNodeContent,
    $trimTextContentFromAnchor: $trimTextContentFromAnchor,
    $wrapNodes: $wrapNodes,
    createDOMRange: createDOMRange,
    createRectsFromDOMRange: createRectsFromDOMRange,
    getStyleObjectFromCSS: getStyleObjectFromCSS,
    trimTextContentFromAnchor: trimTextContentFromAnchor,
    $cloneWithProperties: $cloneWithProperties$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  function m$1(e) {
    return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
  }
  var T$1 = m$1(function (e) {
    const t = new URLSearchParams();
    t.append("code", e);
    for (let e = 1; e < arguments.length; e++) t.append("v", arguments[e]);
    throw Error(`Minified Lexical error #${e}; visit https://lexical.dev/docs/error?${t} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$1 = modDev$1;
  const $addNodeStyle$1 = mod$1.$addNodeStyle;
  const $cloneWithProperties$2 = mod$1.$cloneWithProperties;
  const $getSelectionStyleValueForProperty$1 = mod$1.$getSelectionStyleValueForProperty;
  const $isAtNodeEnd$1 = mod$1.$isAtNodeEnd;
  const $isParentElementRTL$1 = mod$1.$isParentElementRTL;
  const $moveCaretSelection$1 = mod$1.$moveCaretSelection;
  const $moveCharacter$1 = mod$1.$moveCharacter;
  const $patchStyleText$1 = mod$1.$patchStyleText;
  const $selectAll$3 = mod$1.$selectAll;
  const $setBlocksType$1 = mod$1.$setBlocksType;
  const $shouldOverrideDefaultCharacterSelection$1 = mod$1.$shouldOverrideDefaultCharacterSelection;
  const $sliceSelectedTextNodeContent$1 = mod$1.$sliceSelectedTextNodeContent;
  const $trimTextContentFromAnchor$1 = mod$1.$trimTextContentFromAnchor;
  const $wrapNodes$1 = mod$1.$wrapNodes;
  const createDOMRange$1 = mod$1.createDOMRange;
  const createRectsFromDOMRange$1 = mod$1.createRectsFromDOMRange;
  const getStyleObjectFromCSS$1 = mod$1.getStyleObjectFromCSS;
  const trimTextContentFromAnchor$1 = mod$1.trimTextContentFromAnchor;

  var LexicalSelection = /*#__PURE__*/Object.freeze({
    $addNodeStyle: $addNodeStyle$1,
    $cloneWithProperties: $cloneWithProperties$2,
    $getSelectionStyleValueForProperty: $getSelectionStyleValueForProperty$1,
    $isAtNodeEnd: $isAtNodeEnd$1,
    $isParentElementRTL: $isParentElementRTL$1,
    $moveCaretSelection: $moveCaretSelection$1,
    $moveCharacter: $moveCharacter$1,
    $patchStyleText: $patchStyleText$1,
    $selectAll: $selectAll$3,
    $setBlocksType: $setBlocksType$1,
    $shouldOverrideDefaultCharacterSelection: $shouldOverrideDefaultCharacterSelection$1,
    $sliceSelectedTextNodeContent: $sliceSelectedTextNodeContent$1,
    $trimTextContentFromAnchor: $trimTextContentFromAnchor$1,
    $wrapNodes: $wrapNodes$1,
    createDOMRange: createDOMRange$1,
    createRectsFromDOMRange: createRectsFromDOMRange$1,
    getStyleObjectFromCSS: getStyleObjectFromCSS$1,
    trimTextContentFromAnchor: trimTextContentFromAnchor$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const CAN_USE_DOM$1 = typeof window !== 'undefined' && typeof window.document !== 'undefined' && typeof window.document.createElement !== 'undefined';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const documentMode$1 = CAN_USE_DOM$1 && 'documentMode' in document ? document.documentMode : null;
  const IS_APPLE$1 = CAN_USE_DOM$1 && /Mac|iPod|iPhone|iPad/.test(navigator.platform);
  const IS_FIREFOX$1 = CAN_USE_DOM$1 && /^(?!.*Seamonkey)(?=.*Firefox).*/i.test(navigator.userAgent);
  const CAN_USE_BEFORE_INPUT$1 = CAN_USE_DOM$1 && 'InputEvent' in window && !documentMode$1 ? 'getTargetRanges' in new window.InputEvent('input') : false;
  const IS_SAFARI$1 = CAN_USE_DOM$1 && /Version\/[\d.]+.*Safari/.test(navigator.userAgent);
  const IS_IOS$1 = CAN_USE_DOM$1 && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  const IS_ANDROID$1 = CAN_USE_DOM$1 && /Android/.test(navigator.userAgent);

  // Keep these in case we need to use them in the future.
  // export const IS_WINDOWS: boolean = CAN_USE_DOM && /Win/.test(navigator.platform);
  const IS_CHROME$1 = CAN_USE_DOM$1 && /^(?=.*Chrome).*/i.test(navigator.userAgent);
  // export const canUseTextInputEvent: boolean = CAN_USE_DOM && 'TextEvent' in window && !documentMode;

  const IS_ANDROID_CHROME$1 = CAN_USE_DOM$1 && IS_ANDROID$1 && IS_CHROME$1;
  const IS_APPLE_WEBKIT$1 = CAN_USE_DOM$1 && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !IS_CHROME$1;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function normalizeClassNames$1(...classNames) {
    const rval = [];
    for (const className of classNames) {
      if (className && typeof className === 'string') {
        for (const [s] of className.matchAll(/\S+/g)) {
          rval.push(s);
        }
      }
    }
    return rval;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Returns a function that will execute all functions passed when called. It is generally used
   * to register multiple lexical listeners and then tear them down with a single function call, such
   * as React's useEffect hook.
   * @example
   * ```ts
   * useEffect(() => {
   *   return mergeRegister(
   *     editor.registerCommand(...registerCommand1 logic),
   *     editor.registerCommand(...registerCommand2 logic),
   *     editor.registerCommand(...registerCommand3 logic)
   *   )
   * }, [editor])
   * ```
   * In this case, useEffect is returning the function returned by mergeRegister as a cleanup
   * function to be executed after either the useEffect runs again (due to one of its dependencies
   * updating) or the component it resides in unmounts.
   * Note the functions don't neccesarily need to be in an array as all arguments
   * are considered to be the func argument and spread from there.
   * The order of cleanup is the reverse of the argument order. Generally it is
   * expected that the first "acquire" will be "released" last (LIFO order),
   * because a later step may have some dependency on an earlier one.
   * @param func - An array of cleanup functions meant to be executed by the returned function.
   * @returns the function which executes all the passed cleanup functions.
   */
  function mergeRegister(...func) {
    return () => {
      for (let i = func.length - 1; i >= 0; i--) {
        func[i]();
      }
      // Clean up the references and make future calls a no-op
      func.length = 0;
    };
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function px(value) {
    return `${value}px`;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const mutationObserverConfig = {
    attributes: true,
    characterData: true,
    childList: true,
    subtree: true
  };
  function positionNodeOnRange(editor, range, onReposition) {
    let rootDOMNode = null;
    let parentDOMNode = null;
    let observer = null;
    let lastNodes = [];
    const wrapperNode = document.createElement('div');
    function position() {
      if (!(rootDOMNode !== null)) {
        throw Error(`Unexpected null rootDOMNode`);
      }
      if (!(parentDOMNode !== null)) {
        throw Error(`Unexpected null parentDOMNode`);
      }
      const {
        left: rootLeft,
        top: rootTop
      } = rootDOMNode.getBoundingClientRect();
      const parentDOMNode_ = parentDOMNode;
      const rects = createRectsFromDOMRange$1(editor, range);
      if (!wrapperNode.isConnected) {
        parentDOMNode_.append(wrapperNode);
      }
      let hasRepositioned = false;
      for (let i = 0; i < rects.length; i++) {
        const rect = rects[i];
        // Try to reuse the previously created Node when possible, no need to
        // remove/create on the most common case reposition case
        const rectNode = lastNodes[i] || document.createElement('div');
        const rectNodeStyle = rectNode.style;
        if (rectNodeStyle.position !== 'absolute') {
          rectNodeStyle.position = 'absolute';
          hasRepositioned = true;
        }
        const left = px(rect.left - rootLeft);
        if (rectNodeStyle.left !== left) {
          rectNodeStyle.left = left;
          hasRepositioned = true;
        }
        const top = px(rect.top - rootTop);
        if (rectNodeStyle.top !== top) {
          rectNode.style.top = top;
          hasRepositioned = true;
        }
        const width = px(rect.width);
        if (rectNodeStyle.width !== width) {
          rectNode.style.width = width;
          hasRepositioned = true;
        }
        const height = px(rect.height);
        if (rectNodeStyle.height !== height) {
          rectNode.style.height = height;
          hasRepositioned = true;
        }
        if (rectNode.parentNode !== wrapperNode) {
          wrapperNode.append(rectNode);
          hasRepositioned = true;
        }
        lastNodes[i] = rectNode;
      }
      while (lastNodes.length > rects.length) {
        lastNodes.pop();
      }
      if (hasRepositioned) {
        onReposition(lastNodes);
      }
    }
    function stop() {
      parentDOMNode = null;
      rootDOMNode = null;
      if (observer !== null) {
        observer.disconnect();
      }
      observer = null;
      wrapperNode.remove();
      for (const node of lastNodes) {
        node.remove();
      }
      lastNodes = [];
    }
    function restart() {
      const currentRootDOMNode = editor.getRootElement();
      if (currentRootDOMNode === null) {
        return stop();
      }
      const currentParentDOMNode = currentRootDOMNode.parentElement;
      if (!(currentParentDOMNode instanceof HTMLElement)) {
        return stop();
      }
      stop();
      rootDOMNode = currentRootDOMNode;
      parentDOMNode = currentParentDOMNode;
      observer = new MutationObserver(mutations => {
        const nextRootDOMNode = editor.getRootElement();
        const nextParentDOMNode = nextRootDOMNode && nextRootDOMNode.parentElement;
        if (nextRootDOMNode !== rootDOMNode || nextParentDOMNode !== parentDOMNode) {
          return restart();
        }
        for (const mutation of mutations) {
          if (!wrapperNode.contains(mutation.target)) {
            // TODO throttle
            return position();
          }
        }
      });
      observer.observe(currentParentDOMNode, mutationObserverConfig);
      position();
    }
    const removeRootListener = editor.registerRootListener(restart);
    return () => {
      removeRootListener();
      stop();
    };
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function markSelection(editor, onReposition) {
    let previousAnchorNode = null;
    let previousAnchorOffset = null;
    let previousFocusNode = null;
    let previousFocusOffset = null;
    let removeRangeListener = () => {};
    function compute(editorState) {
      editorState.read(() => {
        const selection = $getSelection$1();
        if (!$isRangeSelection$1(selection)) {
          // TODO
          previousAnchorNode = null;
          previousAnchorOffset = null;
          previousFocusNode = null;
          previousFocusOffset = null;
          removeRangeListener();
          removeRangeListener = () => {};
          return;
        }
        const {
          anchor,
          focus
        } = selection;
        const currentAnchorNode = anchor.getNode();
        const currentAnchorNodeKey = currentAnchorNode.getKey();
        const currentAnchorOffset = anchor.offset;
        const currentFocusNode = focus.getNode();
        const currentFocusNodeKey = currentFocusNode.getKey();
        const currentFocusOffset = focus.offset;
        const currentAnchorNodeDOM = editor.getElementByKey(currentAnchorNodeKey);
        const currentFocusNodeDOM = editor.getElementByKey(currentFocusNodeKey);
        const differentAnchorDOM = previousAnchorNode === null || currentAnchorNodeDOM === null || currentAnchorOffset !== previousAnchorOffset || currentAnchorNodeKey !== previousAnchorNode.getKey() || currentAnchorNode !== previousAnchorNode && (!(previousAnchorNode instanceof TextNode$1) || currentAnchorNode.updateDOM(previousAnchorNode, currentAnchorNodeDOM, editor._config));
        const differentFocusDOM = previousFocusNode === null || currentFocusNodeDOM === null || currentFocusOffset !== previousFocusOffset || currentFocusNodeKey !== previousFocusNode.getKey() || currentFocusNode !== previousFocusNode && (!(previousFocusNode instanceof TextNode$1) || currentFocusNode.updateDOM(previousFocusNode, currentFocusNodeDOM, editor._config));
        if (differentAnchorDOM || differentFocusDOM) {
          const anchorHTMLElement = editor.getElementByKey(anchor.getNode().getKey());
          const focusHTMLElement = editor.getElementByKey(focus.getNode().getKey());
          // TODO handle selection beyond the common TextNode
          if (anchorHTMLElement !== null && focusHTMLElement !== null && anchorHTMLElement.tagName === 'SPAN' && focusHTMLElement.tagName === 'SPAN') {
            const range = document.createRange();
            let firstHTMLElement;
            let firstOffset;
            let lastHTMLElement;
            let lastOffset;
            if (focus.isBefore(anchor)) {
              firstHTMLElement = focusHTMLElement;
              firstOffset = focus.offset;
              lastHTMLElement = anchorHTMLElement;
              lastOffset = anchor.offset;
            } else {
              firstHTMLElement = anchorHTMLElement;
              firstOffset = anchor.offset;
              lastHTMLElement = focusHTMLElement;
              lastOffset = focus.offset;
            }
            const firstTextNode = firstHTMLElement.firstChild;
            if (!(firstTextNode !== null)) {
              throw Error(`Expected text node to be first child of span`);
            }
            const lastTextNode = lastHTMLElement.firstChild;
            if (!(lastTextNode !== null)) {
              throw Error(`Expected text node to be first child of span`);
            }
            range.setStart(firstTextNode, firstOffset);
            range.setEnd(lastTextNode, lastOffset);
            removeRangeListener();
            removeRangeListener = positionNodeOnRange(editor, range, domNodes => {
              for (const domNode of domNodes) {
                const domNodeStyle = domNode.style;
                if (domNodeStyle.background !== 'Highlight') {
                  domNodeStyle.background = 'Highlight';
                }
                if (domNodeStyle.color !== 'HighlightText') {
                  domNodeStyle.color = 'HighlightText';
                }
                if (domNodeStyle.zIndex !== '-1') {
                  domNodeStyle.zIndex = '-1';
                }
                if (domNodeStyle.pointerEvents !== 'none') {
                  domNodeStyle.pointerEvents = 'none';
                }
                if (domNodeStyle.marginTop !== px(-1.5)) {
                  domNodeStyle.marginTop = px(-1.5);
                }
                if (domNodeStyle.paddingTop !== px(4)) {
                  domNodeStyle.paddingTop = px(4);
                }
                if (domNodeStyle.paddingBottom !== px(0)) {
                  domNodeStyle.paddingBottom = px(0);
                }
              }
              if (onReposition !== undefined) {
                onReposition(domNodes);
              }
            });
          }
        }
        previousAnchorNode = currentAnchorNode;
        previousAnchorOffset = currentAnchorOffset;
        previousFocusNode = currentFocusNode;
        previousFocusOffset = currentFocusOffset;
      });
    }
    compute(editor.getEditorState());
    return mergeRegister(editor.registerUpdateListener(({
      editorState
    }) => compute(editorState)), removeRangeListener, () => {
      removeRangeListener();
    });
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  // Hotfix to export these with inlined types #5918
  const CAN_USE_BEFORE_INPUT$2 = CAN_USE_BEFORE_INPUT$1;
  const CAN_USE_DOM$2 = CAN_USE_DOM$1;
  const IS_ANDROID$2 = IS_ANDROID$1;
  const IS_ANDROID_CHROME$2 = IS_ANDROID_CHROME$1;
  const IS_APPLE$2 = IS_APPLE$1;
  const IS_APPLE_WEBKIT$2 = IS_APPLE_WEBKIT$1;
  const IS_CHROME$2 = IS_CHROME$1;
  const IS_FIREFOX$2 = IS_FIREFOX$1;
  const IS_IOS$2 = IS_IOS$1;
  const IS_SAFARI$2 = IS_SAFARI$1;
  /**
   * Takes an HTML element and adds the classNames passed within an array,
   * ignoring any non-string types. A space can be used to add multiple classes
   * eg. addClassNamesToElement(element, ['element-inner active', true, null])
   * will add both 'element-inner' and 'active' as classes to that element.
   * @param element - The element in which the classes are added
   * @param classNames - An array defining the class names to add to the element
   */
  function addClassNamesToElement(element, ...classNames) {
    const classesToAdd = normalizeClassNames$1(...classNames);
    if (classesToAdd.length > 0) {
      element.classList.add(...classesToAdd);
    }
  }

  /**
   * Takes an HTML element and removes the classNames passed within an array,
   * ignoring any non-string types. A space can be used to remove multiple classes
   * eg. removeClassNamesFromElement(element, ['active small', true, null])
   * will remove both the 'active' and 'small' classes from that element.
   * @param element - The element in which the classes are removed
   * @param classNames - An array defining the class names to remove from the element
   */
  function removeClassNamesFromElement(element, ...classNames) {
    const classesToRemove = normalizeClassNames$1(...classNames);
    if (classesToRemove.length > 0) {
      element.classList.remove(...classesToRemove);
    }
  }

  /**
   * Returns true if the file type matches the types passed within the acceptableMimeTypes array, false otherwise.
   * The types passed must be strings and are CASE-SENSITIVE.
   * eg. if file is of type 'text' and acceptableMimeTypes = ['TEXT', 'IMAGE'] the function will return false.
   * @param file - The file you want to type check.
   * @param acceptableMimeTypes - An array of strings of types which the file is checked against.
   * @returns true if the file is an acceptable mime type, false otherwise.
   */
  function isMimeType(file, acceptableMimeTypes) {
    for (const acceptableType of acceptableMimeTypes) {
      if (file.type.startsWith(acceptableType)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Lexical File Reader with:
   *  1. MIME type support
   *  2. batched results (HistoryPlugin compatibility)
   *  3. Order aware (respects the order when multiple Files are passed)
   *
   * const filesResult = await mediaFileReader(files, ['image/']);
   * filesResult.forEach(file => editor.dispatchCommand('INSERT_IMAGE', \\{
   *   src: file.result,
   * \\}));
   */
  function mediaFileReader(files, acceptableMimeTypes) {
    const filesIterator = files[Symbol.iterator]();
    return new Promise((resolve, reject) => {
      const processed = [];
      const handleNextFile = () => {
        const {
          done,
          value: file
        } = filesIterator.next();
        if (done) {
          return resolve(processed);
        }
        const fileReader = new FileReader();
        fileReader.addEventListener('error', reject);
        fileReader.addEventListener('load', () => {
          const result = fileReader.result;
          if (typeof result === 'string') {
            processed.push({
              file,
              result
            });
          }
          handleNextFile();
        });
        if (isMimeType(file, acceptableMimeTypes)) {
          fileReader.readAsDataURL(file);
        } else {
          handleNextFile();
        }
      };
      handleNextFile();
    });
  }

  /**
   * "Depth-First Search" starts at the root/top node of a tree and goes as far as it can down a branch end
   * before backtracking and finding a new path. Consider solving a maze by hugging either wall, moving down a
   * branch until you hit a dead-end (leaf) and backtracking to find the nearest branching path and repeat.
   * It will then return all the nodes found in the search in an array of objects.
   * @param startingNode - The node to start the search, if ommitted, it will start at the root node.
   * @param endingNode - The node to end the search, if ommitted, it will find all descendants of the startingNode.
   * @returns An array of objects of all the nodes found by the search, including their depth into the tree.
   * \\{depth: number, node: LexicalNode\\} It will always return at least 1 node (the ending node) so long as it exists
   */
  function $dfs(startingNode, endingNode) {
    const nodes = [];
    const start = (startingNode || $getRoot$1()).getLatest();
    const end = endingNode || ($isElementNode$1(start) ? start.getLastDescendant() || start : start);
    let node = start;
    let depth = $getDepth(node);
    while (node !== null && !node.is(end)) {
      nodes.push({
        depth,
        node
      });
      if ($isElementNode$1(node) && node.getChildrenSize() > 0) {
        node = node.getFirstChild();
        depth++;
      } else {
        // Find immediate sibling or nearest parent sibling
        let sibling = null;
        while (sibling === null && node !== null) {
          sibling = node.getNextSibling();
          if (sibling === null) {
            node = node.getParent();
            depth--;
          } else {
            node = sibling;
          }
        }
      }
    }
    if (node !== null && node.is(end)) {
      nodes.push({
        depth,
        node
      });
    }
    return nodes;
  }
  function $getDepth(node) {
    let innerNode = node;
    let depth = 0;
    while ((innerNode = innerNode.getParent()) !== null) {
      depth++;
    }
    return depth;
  }

  /**
   * Performs a right-to-left preorder tree traversal.
   * From the starting node it goes to the rightmost child, than backtracks to paret and finds new rightmost path.
   * It will return the next node in traversal sequence after the startingNode.
   * The traversal is similar to $dfs functions above, but the nodes are visited right-to-left, not left-to-right.
   * @param startingNode - The node to start the search.
   * @returns The next node in pre-order right to left traversal sequence or `null`, if the node does not exist
   */
  function $getNextRightPreorderNode(startingNode) {
    let node = startingNode;
    if ($isElementNode$1(node) && node.getChildrenSize() > 0) {
      node = node.getLastChild();
    } else {
      let sibling = null;
      while (sibling === null && node !== null) {
        sibling = node.getPreviousSibling();
        if (sibling === null) {
          node = node.getParent();
        } else {
          node = sibling;
        }
      }
    }
    return node;
  }

  /**
   * Takes a node and traverses up its ancestors (toward the root node)
   * in order to find a specific type of node.
   * @param node - the node to begin searching.
   * @param klass - an instance of the type of node to look for.
   * @returns the node of type klass that was passed, or null if none exist.
   */
  function $getNearestNodeOfType(node, klass) {
    let parent = node;
    while (parent != null) {
      if (parent instanceof klass) {
        return parent;
      }
      parent = parent.getParent();
    }
    return null;
  }

  /**
   * Returns the element node of the nearest ancestor, otherwise throws an error.
   * @param startNode - The starting node of the search
   * @returns The ancestor node found
   */
  function $getNearestBlockElementAncestorOrThrow(startNode) {
    const blockNode = $findMatchingParent(startNode, node => $isElementNode$1(node) && !node.isInline());
    if (!$isElementNode$1(blockNode)) {
      {
        throw Error(`Expected node ${startNode.__key} to have closest block element node.`);
      }
    }
    return blockNode;
  }
  /**
   * Starts with a node and moves up the tree (toward the root node) to find a matching node based on
   * the search parameters of the findFn. (Consider JavaScripts' .find() function where a testing function must be
   * passed as an argument. eg. if( (node) => node.__type === 'div') ) return true; otherwise return false
   * @param startingNode - The node where the search starts.
   * @param findFn - A testing function that returns true if the current node satisfies the testing parameters.
   * @returns A parent node that matches the findFn parameters, or null if one wasn't found.
   */
  const $findMatchingParent = (startingNode, findFn) => {
    let curr = startingNode;
    while (curr !== $getRoot$1() && curr != null) {
      if (findFn(curr)) {
        return curr;
      }
      curr = curr.getParent();
    }
    return null;
  };

  /**
   * Attempts to resolve nested element nodes of the same type into a single node of that type.
   * It is generally used for marks/commenting
   * @param editor - The lexical editor
   * @param targetNode - The target for the nested element to be extracted from.
   * @param cloneNode - See {@link $createMarkNode}
   * @param handleOverlap - Handles any overlap between the node to extract and the targetNode
   * @returns The lexical editor
   */
  function registerNestedElementResolver(editor, targetNode, cloneNode, handleOverlap) {
    const $isTargetNode = node => {
      return node instanceof targetNode;
    };
    const $findMatch = node => {
      // First validate we don't have any children that are of the target,
      // as we need to handle them first.
      const children = node.getChildren();
      for (let i = 0; i < children.length; i++) {
        const child = children[i];
        if ($isTargetNode(child)) {
          return null;
        }
      }
      let parentNode = node;
      let childNode = node;
      while (parentNode !== null) {
        childNode = parentNode;
        parentNode = parentNode.getParent();
        if ($isTargetNode(parentNode)) {
          return {
            child: childNode,
            parent: parentNode
          };
        }
      }
      return null;
    };
    const $elementNodeTransform = node => {
      const match = $findMatch(node);
      if (match !== null) {
        const {
          child,
          parent
        } = match;

        // Simple path, we can move child out and siblings into a new parent.

        if (child.is(node)) {
          handleOverlap(parent, node);
          const nextSiblings = child.getNextSiblings();
          const nextSiblingsLength = nextSiblings.length;
          parent.insertAfter(child);
          if (nextSiblingsLength !== 0) {
            const newParent = cloneNode(parent);
            child.insertAfter(newParent);
            for (let i = 0; i < nextSiblingsLength; i++) {
              newParent.append(nextSiblings[i]);
            }
          }
          if (!parent.canBeEmpty() && parent.getChildrenSize() === 0) {
            parent.remove();
          }
        }
      }
    };
    return editor.registerNodeTransform(targetNode, $elementNodeTransform);
  }

  /**
   * Clones the editor and marks it as dirty to be reconciled. If there was a selection,
   * it would be set back to its previous state, or null otherwise.
   * @param editor - The lexical editor
   * @param editorState - The editor's state
   */
  function $restoreEditorState(editor, editorState) {
    const FULL_RECONCILE = 2;
    const nodeMap = new Map();
    const activeEditorState = editor._pendingEditorState;
    for (const [key, node] of editorState._nodeMap) {
      nodeMap.set(key, $cloneWithProperties$1(node));
    }
    if (activeEditorState) {
      activeEditorState._nodeMap = nodeMap;
    }
    editor._dirtyType = FULL_RECONCILE;
    const selection = editorState._selection;
    $setSelection$1(selection === null ? null : selection.clone());
  }

  /**
   * If the selected insertion area is the root/shadow root node (see {@link lexical!$isRootOrShadowRoot}),
   * the node will be appended there, otherwise, it will be inserted before the insertion area.
   * If there is no selection where the node is to be inserted, it will be appended after any current nodes
   * within the tree, as a child of the root node. A paragraph node will then be added after the inserted node and selected.
   * @param node - The node to be inserted
   * @returns The node after its insertion
   */
  function $insertNodeToNearestRoot(node) {
    const selection = $getSelection$1() || $getPreviousSelection$1();
    if ($isRangeSelection$1(selection)) {
      const {
        focus
      } = selection;
      const focusNode = focus.getNode();
      const focusOffset = focus.offset;
      if ($isRootOrShadowRoot$1(focusNode)) {
        const focusChild = focusNode.getChildAtIndex(focusOffset);
        if (focusChild == null) {
          focusNode.append(node);
        } else {
          focusChild.insertBefore(node);
        }
        node.selectNext();
      } else {
        let splitNode;
        let splitOffset;
        if ($isTextNode$1(focusNode)) {
          splitNode = focusNode.getParentOrThrow();
          splitOffset = focusNode.getIndexWithinParent();
          if (focusOffset > 0) {
            splitOffset += 1;
            focusNode.splitText(focusOffset);
          }
        } else {
          splitNode = focusNode;
          splitOffset = focusOffset;
        }
        const [, rightTree] = $splitNode$1(splitNode, splitOffset);
        rightTree.insertBefore(node);
        rightTree.selectStart();
      }
    } else {
      if (selection != null) {
        const nodes = selection.getNodes();
        nodes[nodes.length - 1].getTopLevelElementOrThrow().insertAfter(node);
      } else {
        const root = $getRoot$1();
        root.append(node);
      }
      const paragraphNode = $createParagraphNode$1();
      node.insertAfter(paragraphNode);
      paragraphNode.select();
    }
    return node.getLatest();
  }

  /**
   * Wraps the node into another node created from a createElementNode function, eg. $createParagraphNode
   * @param node - Node to be wrapped.
   * @param createElementNode - Creates a new lexical element to wrap the to-be-wrapped node and returns it.
   * @returns A new lexical element with the previous node appended within (as a child, including its children).
   */
  function $wrapNodeInElement(node, createElementNode) {
    const elementNode = createElementNode();
    node.replace(elementNode);
    elementNode.append(node);
    return elementNode;
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any

  /**
   * @param object = The instance of the type
   * @param objectClass = The class of the type
   * @returns Whether the object is has the same Klass of the objectClass, ignoring the difference across window (e.g. different iframs)
   */
  function objectKlassEquals(object, objectClass) {
    return object !== null ? Object.getPrototypeOf(object).constructor.name === objectClass.name : false;
  }

  /**
   * Filter the nodes
   * @param nodes Array of nodes that needs to be filtered
   * @param filterFn A filter function that returns node if the current node satisfies the condition otherwise null
   * @returns Array of filtered nodes
   */

  function $filter(nodes, filterFn) {
    const result = [];
    for (let i = 0; i < nodes.length; i++) {
      const node = filterFn(nodes[i]);
      if (node !== null) {
        result.push(node);
      }
    }
    return result;
  }
  /**
   * Appends the node before the first child of the parent node
   * @param parent A parent node
   * @param node Node that needs to be appended
   */
  function $insertFirst(parent, node) {
    const firstChild = parent.getFirstChild();
    if (firstChild !== null) {
      firstChild.insertBefore(node);
    } else {
      parent.append(node);
    }
  }

  /**
   * Calculates the zoom level of an element as a result of using
   * css zoom property.
   * @param element
   */
  function calculateZoomLevel(element) {
    if (IS_FIREFOX$2) {
      return 1;
    }
    let zoom = 1;
    while (element) {
      zoom *= Number(window.getComputedStyle(element).getPropertyValue('zoom'));
      element = element.parentElement;
    }
    return zoom;
  }

  /**
   * Checks if the editor is a nested editor created by LexicalNestedComposer
   */
  function $isEditorIsNestedEditor(editor) {
    return editor._parentEditor !== null;
  }

  var modDev$2 = /*#__PURE__*/Object.freeze({
    $dfs: $dfs,
    $filter: $filter,
    $findMatchingParent: $findMatchingParent,
    $getNearestBlockElementAncestorOrThrow: $getNearestBlockElementAncestorOrThrow,
    $getNearestNodeOfType: $getNearestNodeOfType,
    $getNextRightPreorderNode: $getNextRightPreorderNode,
    $insertFirst: $insertFirst,
    $insertNodeToNearestRoot: $insertNodeToNearestRoot,
    $isEditorIsNestedEditor: $isEditorIsNestedEditor,
    $restoreEditorState: $restoreEditorState,
    $wrapNodeInElement: $wrapNodeInElement,
    CAN_USE_BEFORE_INPUT: CAN_USE_BEFORE_INPUT$2,
    CAN_USE_DOM: CAN_USE_DOM$2,
    IS_ANDROID: IS_ANDROID$2,
    IS_ANDROID_CHROME: IS_ANDROID_CHROME$2,
    IS_APPLE: IS_APPLE$2,
    IS_APPLE_WEBKIT: IS_APPLE_WEBKIT$2,
    IS_CHROME: IS_CHROME$2,
    IS_FIREFOX: IS_FIREFOX$2,
    IS_IOS: IS_IOS$2,
    IS_SAFARI: IS_SAFARI$2,
    addClassNamesToElement: addClassNamesToElement,
    calculateZoomLevel: calculateZoomLevel,
    isMimeType: isMimeType,
    markSelection: markSelection,
    mediaFileReader: mediaFileReader,
    mergeRegister: mergeRegister,
    objectKlassEquals: objectKlassEquals,
    positionNodeOnRange: positionNodeOnRange,
    registerNestedElementResolver: registerNestedElementResolver,
    removeClassNamesFromElement: removeClassNamesFromElement,
    $splitNode: $splitNode$1,
    isBlockDomNode: isBlockDomNode$1,
    isHTMLAnchorElement: isHTMLAnchorElement$1,
    isHTMLElement: isHTMLElement$1,
    isInlineDomNode: isInlineDomNode$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  function g$1(e) {
    return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
  }
  var p$1 = g$1(function (e) {
    const t = new URLSearchParams();
    t.append("code", e);
    for (let e = 1; e < arguments.length; e++) t.append("v", arguments[e]);
    throw Error(`Minified Lexical error #${e}; visit https://lexical.dev/docs/error?${t} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });
  const h$1 = "undefined" != typeof window && void 0 !== window.document && void 0 !== window.document.createElement,
    m$2 = h$1 && "documentMode" in document ? document.documentMode : null,
    v$2 = h$1 && /Mac|iPod|iPhone|iPad/.test(navigator.platform),
    y$1 = h$1 && /^(?!.*Seamonkey)(?=.*Firefox).*/i.test(navigator.userAgent),
    w$2 = !(!h$1 || !("InputEvent" in window) || m$2) && "getTargetRanges" in new window.InputEvent("input"),
    E$2 = h$1 && /Version\/[\d.]+.*Safari/.test(navigator.userAgent),
    P$2 = h$1 && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
    S$2 = h$1 && /Android/.test(navigator.userAgent),
    x$2 = h$1 && /^(?=.*Chrome).*/i.test(navigator.userAgent),
    A$2 = h$1 && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !x$2;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$2 = modDev$2;
  const $dfs$1 = mod$2.$dfs;
  const $filter$1 = mod$2.$filter;
  const $findMatchingParent$1 = mod$2.$findMatchingParent;
  const $getNearestBlockElementAncestorOrThrow$1 = mod$2.$getNearestBlockElementAncestorOrThrow;
  const $getNearestNodeOfType$1 = mod$2.$getNearestNodeOfType;
  const $getNextRightPreorderNode$1 = mod$2.$getNextRightPreorderNode;
  const $insertFirst$1 = mod$2.$insertFirst;
  const $insertNodeToNearestRoot$1 = mod$2.$insertNodeToNearestRoot;
  const $isEditorIsNestedEditor$1 = mod$2.$isEditorIsNestedEditor;
  const $restoreEditorState$1 = mod$2.$restoreEditorState;
  const $splitNode$2 = mod$2.$splitNode;
  const $wrapNodeInElement$1 = mod$2.$wrapNodeInElement;
  const CAN_USE_BEFORE_INPUT$3 = mod$2.CAN_USE_BEFORE_INPUT;
  const CAN_USE_DOM$3 = mod$2.CAN_USE_DOM;
  const IS_ANDROID$3 = mod$2.IS_ANDROID;
  const IS_ANDROID_CHROME$3 = mod$2.IS_ANDROID_CHROME;
  const IS_APPLE$3 = mod$2.IS_APPLE;
  const IS_APPLE_WEBKIT$3 = mod$2.IS_APPLE_WEBKIT;
  const IS_CHROME$3 = mod$2.IS_CHROME;
  const IS_FIREFOX$3 = mod$2.IS_FIREFOX;
  const IS_IOS$3 = mod$2.IS_IOS;
  const IS_SAFARI$3 = mod$2.IS_SAFARI;
  const addClassNamesToElement$1 = mod$2.addClassNamesToElement;
  const calculateZoomLevel$1 = mod$2.calculateZoomLevel;
  const isBlockDomNode$2 = mod$2.isBlockDomNode;
  const isHTMLAnchorElement$2 = mod$2.isHTMLAnchorElement;
  const isHTMLElement$2 = mod$2.isHTMLElement;
  const isInlineDomNode$2 = mod$2.isInlineDomNode;
  const isMimeType$1 = mod$2.isMimeType;
  const markSelection$1 = mod$2.markSelection;
  const mediaFileReader$1 = mod$2.mediaFileReader;
  const mergeRegister$1 = mod$2.mergeRegister;
  const objectKlassEquals$1 = mod$2.objectKlassEquals;
  const positionNodeOnRange$1 = mod$2.positionNodeOnRange;
  const registerNestedElementResolver$1 = mod$2.registerNestedElementResolver;
  const removeClassNamesFromElement$1 = mod$2.removeClassNamesFromElement;

  var LexicalUtils = /*#__PURE__*/Object.freeze({
    $dfs: $dfs$1,
    $filter: $filter$1,
    $findMatchingParent: $findMatchingParent$1,
    $getNearestBlockElementAncestorOrThrow: $getNearestBlockElementAncestorOrThrow$1,
    $getNearestNodeOfType: $getNearestNodeOfType$1,
    $getNextRightPreorderNode: $getNextRightPreorderNode$1,
    $insertFirst: $insertFirst$1,
    $insertNodeToNearestRoot: $insertNodeToNearestRoot$1,
    $isEditorIsNestedEditor: $isEditorIsNestedEditor$1,
    $restoreEditorState: $restoreEditorState$1,
    $splitNode: $splitNode$2,
    $wrapNodeInElement: $wrapNodeInElement$1,
    CAN_USE_BEFORE_INPUT: CAN_USE_BEFORE_INPUT$3,
    CAN_USE_DOM: CAN_USE_DOM$3,
    IS_ANDROID: IS_ANDROID$3,
    IS_ANDROID_CHROME: IS_ANDROID_CHROME$3,
    IS_APPLE: IS_APPLE$3,
    IS_APPLE_WEBKIT: IS_APPLE_WEBKIT$3,
    IS_CHROME: IS_CHROME$3,
    IS_FIREFOX: IS_FIREFOX$3,
    IS_IOS: IS_IOS$3,
    IS_SAFARI: IS_SAFARI$3,
    addClassNamesToElement: addClassNamesToElement$1,
    calculateZoomLevel: calculateZoomLevel$1,
    isBlockDomNode: isBlockDomNode$2,
    isHTMLAnchorElement: isHTMLAnchorElement$2,
    isHTMLElement: isHTMLElement$2,
    isInlineDomNode: isInlineDomNode$2,
    isMimeType: isMimeType$1,
    markSelection: markSelection$1,
    mediaFileReader: mediaFileReader$1,
    mergeRegister: mergeRegister$1,
    objectKlassEquals: objectKlassEquals$1,
    positionNodeOnRange: positionNodeOnRange$1,
    registerNestedElementResolver: registerNestedElementResolver$1,
    removeClassNamesFromElement: removeClassNamesFromElement$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * How you parse your html string to get a document is left up to you. In the browser you can use the native
   * DOMParser API to generate a document (see clipboard.ts), but to use in a headless environment you can use JSDom
   * or an equivalent library and pass in the document here.
   */
  function $generateNodesFromDOM(editor, dom) {
    const elements = dom.body ? dom.body.childNodes : [];
    let lexicalNodes = [];
    const allArtificialNodes = [];
    for (let i = 0; i < elements.length; i++) {
      const element = elements[i];
      if (!IGNORE_TAGS.has(element.nodeName)) {
        const lexicalNode = $createNodesFromDOM(element, editor, allArtificialNodes, false);
        if (lexicalNode !== null) {
          lexicalNodes = lexicalNodes.concat(lexicalNode);
        }
      }
    }
    $unwrapArtificalNodes(allArtificialNodes);
    return lexicalNodes;
  }
  function $generateHtmlFromNodes(editor, selection) {
    if (typeof document === 'undefined' || typeof window === 'undefined' && typeof global.window === 'undefined') {
      throw new Error('To use $generateHtmlFromNodes in headless mode please initialize a headless browser implementation such as JSDom before calling this function.');
    }
    const container = document.createElement('div');
    const root = $getRoot$1();
    const topLevelChildren = root.getChildren();
    for (let i = 0; i < topLevelChildren.length; i++) {
      const topLevelNode = topLevelChildren[i];
      $appendNodesToHTML(editor, topLevelNode, container, selection);
    }
    return container.innerHTML;
  }
  function $appendNodesToHTML(editor, currentNode, parentElement, selection = null) {
    let shouldInclude = selection !== null ? currentNode.isSelected(selection) : true;
    const shouldExclude = $isElementNode$1(currentNode) && currentNode.excludeFromCopy('html');
    let target = currentNode;
    if (selection !== null) {
      let clone = $cloneWithProperties$1(currentNode);
      clone = $isTextNode$1(clone) && selection !== null ? $sliceSelectedTextNodeContent$1(selection, clone) : clone;
      target = clone;
    }
    const children = $isElementNode$1(target) ? target.getChildren() : [];
    const registeredNode = editor._nodes.get(target.getType());
    let exportOutput;

    // Use HTMLConfig overrides, if available.
    if (registeredNode && registeredNode.exportDOM !== undefined) {
      exportOutput = registeredNode.exportDOM(editor, target);
    } else {
      exportOutput = target.exportDOM(editor);
    }
    const {
      element,
      after
    } = exportOutput;
    if (!element) {
      return false;
    }
    const fragment = document.createDocumentFragment();
    for (let i = 0; i < children.length; i++) {
      const childNode = children[i];
      const shouldIncludeChild = $appendNodesToHTML(editor, childNode, fragment, selection);
      if (!shouldInclude && $isElementNode$1(currentNode) && shouldIncludeChild && currentNode.extractWithChild(childNode, selection, 'html')) {
        shouldInclude = true;
      }
    }
    if (shouldInclude && !shouldExclude) {
      if (isHTMLElement$2(element)) {
        element.append(fragment);
      }
      parentElement.append(element);
      if (after) {
        const newElement = after.call(target, element);
        if (newElement) {
          element.replaceWith(newElement);
        }
      }
    } else {
      parentElement.append(fragment);
    }
    return shouldInclude;
  }
  function getConversionFunction(domNode, editor) {
    const {
      nodeName
    } = domNode;
    const cachedConversions = editor._htmlConversions.get(nodeName.toLowerCase());
    let currentConversion = null;
    if (cachedConversions !== undefined) {
      for (const cachedConversion of cachedConversions) {
        const domConversion = cachedConversion(domNode);
        if (domConversion !== null && (currentConversion === null || (currentConversion.priority || 0) < (domConversion.priority || 0))) {
          currentConversion = domConversion;
        }
      }
    }
    return currentConversion !== null ? currentConversion.conversion : null;
  }
  const IGNORE_TAGS = new Set(['STYLE', 'SCRIPT']);
  function $createNodesFromDOM(node, editor, allArtificialNodes, hasBlockAncestorLexicalNode, forChildMap = new Map(), parentLexicalNode) {
    let lexicalNodes = [];
    if (IGNORE_TAGS.has(node.nodeName)) {
      return lexicalNodes;
    }
    let currentLexicalNode = null;
    const transformFunction = getConversionFunction(node, editor);
    const transformOutput = transformFunction ? transformFunction(node) : null;
    let postTransform = null;
    if (transformOutput !== null) {
      postTransform = transformOutput.after;
      const transformNodes = transformOutput.node;
      currentLexicalNode = Array.isArray(transformNodes) ? transformNodes[transformNodes.length - 1] : transformNodes;
      if (currentLexicalNode !== null) {
        for (const [, forChildFunction] of forChildMap) {
          currentLexicalNode = forChildFunction(currentLexicalNode, parentLexicalNode);
          if (!currentLexicalNode) {
            break;
          }
        }
        if (currentLexicalNode) {
          lexicalNodes.push(...(Array.isArray(transformNodes) ? transformNodes : [currentLexicalNode]));
        }
      }
      if (transformOutput.forChild != null) {
        forChildMap.set(node.nodeName, transformOutput.forChild);
      }
    }

    // If the DOM node doesn't have a transformer, we don't know what
    // to do with it but we still need to process any childNodes.
    const children = node.childNodes;
    let childLexicalNodes = [];
    const hasBlockAncestorLexicalNodeForChildren = currentLexicalNode != null && $isRootOrShadowRoot$1(currentLexicalNode) ? false : currentLexicalNode != null && $isBlockElementNode$1(currentLexicalNode) || hasBlockAncestorLexicalNode;
    for (let i = 0; i < children.length; i++) {
      childLexicalNodes.push(...$createNodesFromDOM(children[i], editor, allArtificialNodes, hasBlockAncestorLexicalNodeForChildren, new Map(forChildMap), currentLexicalNode));
    }
    if (postTransform != null) {
      childLexicalNodes = postTransform(childLexicalNodes);
    }
    if (isBlockDomNode$2(node)) {
      if (!hasBlockAncestorLexicalNodeForChildren) {
        childLexicalNodes = wrapContinuousInlines(node, childLexicalNodes, $createParagraphNode$1);
      } else {
        childLexicalNodes = wrapContinuousInlines(node, childLexicalNodes, () => {
          const artificialNode = new ArtificialNode__DO_NOT_USE$1();
          allArtificialNodes.push(artificialNode);
          return artificialNode;
        });
      }
    }
    if (currentLexicalNode == null) {
      if (childLexicalNodes.length > 0) {
        // If it hasn't been converted to a LexicalNode, we hoist its children
        // up to the same level as it.
        lexicalNodes = lexicalNodes.concat(childLexicalNodes);
      } else {
        if (isBlockDomNode$2(node) && isDomNodeBetweenTwoInlineNodes(node)) {
          // Empty block dom node that hasnt been converted, we replace it with a linebreak if its between inline nodes
          lexicalNodes = lexicalNodes.concat($createLineBreakNode$1());
        }
      }
    } else {
      if ($isElementNode$1(currentLexicalNode)) {
        // If the current node is a ElementNode after conversion,
        // we can append all the children to it.
        currentLexicalNode.append(...childLexicalNodes);
      }
    }
    return lexicalNodes;
  }
  function wrapContinuousInlines(domNode, nodes, createWrapperFn) {
    const textAlign = domNode.style.textAlign;
    const out = [];
    let continuousInlines = [];
    // wrap contiguous inline child nodes in para
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      if ($isBlockElementNode$1(node)) {
        if (textAlign && !node.getFormat()) {
          node.setFormat(textAlign);
        }
        out.push(node);
      } else {
        continuousInlines.push(node);
        if (i === nodes.length - 1 || i < nodes.length - 1 && $isBlockElementNode$1(nodes[i + 1])) {
          const wrapper = createWrapperFn();
          wrapper.setFormat(textAlign);
          wrapper.append(...continuousInlines);
          out.push(wrapper);
          continuousInlines = [];
        }
      }
    }
    return out;
  }
  function $unwrapArtificalNodes(allArtificialNodes) {
    for (const node of allArtificialNodes) {
      if (node.getNextSibling() instanceof ArtificialNode__DO_NOT_USE$1) {
        node.insertAfter($createLineBreakNode$1());
      }
    }
    // Replace artificial node with it's children
    for (const node of allArtificialNodes) {
      const children = node.getChildren();
      for (const child of children) {
        node.insertBefore(child);
      }
      node.remove();
    }
  }
  function isDomNodeBetweenTwoInlineNodes(node) {
    if (node.nextSibling == null || node.previousSibling == null) {
      return false;
    }
    return isInlineDomNode$1(node.nextSibling) && isInlineDomNode$1(node.previousSibling);
  }

  var modDev$3 = /*#__PURE__*/Object.freeze({
    $generateHtmlFromNodes: $generateHtmlFromNodes,
    $generateNodesFromDOM: $generateNodesFromDOM
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$3 = modDev$3;
  const $generateHtmlFromNodes$1 = mod$3.$generateHtmlFromNodes;
  const $generateNodesFromDOM$1 = mod$3.$generateNodesFromDOM;

  var LexicalHtml = /*#__PURE__*/Object.freeze({
    $generateHtmlFromNodes: $generateHtmlFromNodes$1,
    $generateNodesFromDOM: $generateNodesFromDOM$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Checks the depth of listNode from the root node.
   * @param listNode - The ListNode to be checked.
   * @returns The depth of the ListNode.
   */
  function $getListDepth(listNode) {
    let depth = 1;
    let parent = listNode.getParent();
    while (parent != null) {
      if ($isListItemNode(parent)) {
        const parentList = parent.getParent();
        if ($isListNode(parentList)) {
          depth++;
          parent = parentList.getParent();
          continue;
        }
        {
          throw Error(`A ListItemNode must have a ListNode for a parent.`);
        }
      }
      return depth;
    }
    return depth;
  }

  /**
   * Finds the nearest ancestral ListNode and returns it, throws an invariant if listItem is not a ListItemNode.
   * @param listItem - The node to be checked.
   * @returns The ListNode found.
   */
  function $getTopListNode(listItem) {
    let list = listItem.getParent();
    if (!$isListNode(list)) {
      {
        throw Error(`A ListItemNode must have a ListNode for a parent.`);
      }
    }
    let parent = list;
    while (parent !== null) {
      parent = parent.getParent();
      if ($isListNode(parent)) {
        list = parent;
      }
    }
    return list;
  }

  /**
   * A recursive Depth-First Search (Postorder Traversal) that finds all of a node's children
   * that are of type ListItemNode and returns them in an array.
   * @param node - The ListNode to start the search.
   * @returns An array containing all nodes of type ListItemNode found.
   */
  // This should probably be $getAllChildrenOfType
  function $getAllListItems(node) {
    let listItemNodes = [];
    const listChildren = node.getChildren().filter($isListItemNode);
    for (let i = 0; i < listChildren.length; i++) {
      const listItemNode = listChildren[i];
      const firstChild = listItemNode.getFirstChild();
      if ($isListNode(firstChild)) {
        listItemNodes = listItemNodes.concat($getAllListItems(firstChild));
      } else {
        listItemNodes.push(listItemNode);
      }
    }
    return listItemNodes;
  }

  /**
   * Checks to see if the passed node is a ListItemNode and has a ListNode as a child.
   * @param node - The node to be checked.
   * @returns true if the node is a ListItemNode and has a ListNode child, false otherwise.
   */
  function isNestedListNode(node) {
    return $isListItemNode(node) && $isListNode(node.getFirstChild());
  }

  /**
   * Takes a deeply nested ListNode or ListItemNode and traverses up the branch to delete the first
   * ancestral ListNode (which could be the root ListNode) or ListItemNode with siblings, essentially
   * bringing the deeply nested node up the branch once. Would remove sublist if it has siblings.
   * Should not break ListItem -> List -> ListItem chain as empty List/ItemNodes should be removed on .remove().
   * @param sublist - The nested ListNode or ListItemNode to be brought up the branch.
   */
  function $removeHighestEmptyListParent(sublist) {
    // Nodes may be repeatedly indented, to create deeply nested lists that each
    // contain just one bullet.
    // Our goal is to remove these (empty) deeply nested lists. The easiest
    // way to do that is crawl back up the tree until we find a node that has siblings
    // (e.g. is actually part of the list contents) and delete that, or delete
    // the root of the list (if no list nodes have siblings.)
    let emptyListPtr = sublist;
    while (emptyListPtr.getNextSibling() == null && emptyListPtr.getPreviousSibling() == null) {
      const parent = emptyListPtr.getParent();
      if (parent == null || !($isListItemNode(emptyListPtr) || $isListNode(emptyListPtr))) {
        break;
      }
      emptyListPtr = parent;
    }
    emptyListPtr.remove();
  }

  /**
   * Wraps a node into a ListItemNode.
   * @param node - The node to be wrapped into a ListItemNode
   * @returns The ListItemNode which the passed node is wrapped in.
   */
  function $wrapInListItem(node) {
    const listItemWrapper = $createListItemNode();
    return listItemWrapper.append(node);
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function $isSelectingEmptyListItem(anchorNode, nodes) {
    return $isListItemNode(anchorNode) && (nodes.length === 0 || nodes.length === 1 && anchorNode.is(nodes[0]) && anchorNode.getChildrenSize() === 0);
  }

  /**
   * Inserts a new ListNode. If the selection's anchor node is an empty ListItemNode and is a child of
   * the root/shadow root, it will replace the ListItemNode with a ListNode and the old ListItemNode.
   * Otherwise it will replace its parent with a new ListNode and re-insert the ListItemNode and any previous children.
   * If the selection's anchor node is not an empty ListItemNode, it will add a new ListNode or merge an existing ListNode,
   * unless the the node is a leaf node, in which case it will attempt to find a ListNode up the branch and replace it with
   * a new ListNode, or create a new ListNode at the nearest root/shadow root.
   * @param editor - The lexical editor.
   * @param listType - The type of list, "number" | "bullet" | "check".
   */
  function insertList(editor, listType) {
    editor.update(() => {
      const selection = $getSelection$1();
      if (selection !== null) {
        const nodes = selection.getNodes();
        if ($isRangeSelection$1(selection)) {
          const anchorAndFocus = selection.getStartEndPoints();
          if (!(anchorAndFocus !== null)) {
            throw Error(`insertList: anchor should be defined`);
          }
          const [anchor] = anchorAndFocus;
          const anchorNode = anchor.getNode();
          const anchorNodeParent = anchorNode.getParent();
          if ($isSelectingEmptyListItem(anchorNode, nodes)) {
            const list = $createListNode(listType);
            if ($isRootOrShadowRoot$1(anchorNodeParent)) {
              anchorNode.replace(list);
              const listItem = $createListItemNode();
              if ($isElementNode$1(anchorNode)) {
                listItem.setFormat(anchorNode.getFormatType());
                listItem.setIndent(anchorNode.getIndent());
              }
              list.append(listItem);
            } else if ($isListItemNode(anchorNode)) {
              const parent = anchorNode.getParentOrThrow();
              append(list, parent.getChildren());
              parent.replace(list);
            }
            return;
          }
        }
        const handled = new Set();
        for (let i = 0; i < nodes.length; i++) {
          const node = nodes[i];
          if ($isElementNode$1(node) && node.isEmpty() && !$isListItemNode(node) && !handled.has(node.getKey())) {
            $createListOrMerge(node, listType);
            continue;
          }
          if ($isLeafNode$1(node)) {
            let parent = node.getParent();
            while (parent != null) {
              const parentKey = parent.getKey();
              if ($isListNode(parent)) {
                if (!handled.has(parentKey)) {
                  const newListNode = $createListNode(listType);
                  append(newListNode, parent.getChildren());
                  parent.replace(newListNode);
                  handled.add(parentKey);
                }
                break;
              } else {
                const nextParent = parent.getParent();
                if ($isRootOrShadowRoot$1(nextParent) && !handled.has(parentKey)) {
                  handled.add(parentKey);
                  $createListOrMerge(parent, listType);
                  break;
                }
                parent = nextParent;
              }
            }
          }
        }
      }
    });
  }
  function append(node, nodesToAppend) {
    node.splice(node.getChildrenSize(), 0, nodesToAppend);
  }
  function $createListOrMerge(node, listType) {
    if ($isListNode(node)) {
      return node;
    }
    const previousSibling = node.getPreviousSibling();
    const nextSibling = node.getNextSibling();
    const listItem = $createListItemNode();
    listItem.setFormat(node.getFormatType());
    listItem.setIndent(node.getIndent());
    append(listItem, node.getChildren());
    if ($isListNode(previousSibling) && listType === previousSibling.getListType()) {
      previousSibling.append(listItem);
      node.remove();
      // if the same type of list is on both sides, merge them.

      if ($isListNode(nextSibling) && listType === nextSibling.getListType()) {
        append(previousSibling, nextSibling.getChildren());
        nextSibling.remove();
      }
      return previousSibling;
    } else if ($isListNode(nextSibling) && listType === nextSibling.getListType()) {
      nextSibling.getFirstChildOrThrow().insertBefore(listItem);
      node.remove();
      return nextSibling;
    } else {
      const list = $createListNode(listType);
      list.append(listItem);
      node.replace(list);
      return list;
    }
  }

  /**
   * A recursive function that goes through each list and their children, including nested lists,
   * appending list2 children after list1 children and updating ListItemNode values.
   * @param list1 - The first list to be merged.
   * @param list2 - The second list to be merged.
   */
  function mergeLists(list1, list2) {
    const listItem1 = list1.getLastChild();
    const listItem2 = list2.getFirstChild();
    if (listItem1 && listItem2 && isNestedListNode(listItem1) && isNestedListNode(listItem2)) {
      mergeLists(listItem1.getFirstChild(), listItem2.getFirstChild());
      listItem2.remove();
    }
    const toMerge = list2.getChildren();
    if (toMerge.length > 0) {
      list1.append(...toMerge);
    }
    list2.remove();
  }

  /**
   * Searches for the nearest ancestral ListNode and removes it. If selection is an empty ListItemNode
   * it will remove the whole list, including the ListItemNode. For each ListItemNode in the ListNode,
   * removeList will also generate new ParagraphNodes in the removed ListNode's place. Any child node
   * inside a ListItemNode will be appended to the new ParagraphNodes.
   * @param editor - The lexical editor.
   */
  function removeList(editor) {
    editor.update(() => {
      const selection = $getSelection$1();
      if ($isRangeSelection$1(selection)) {
        const listNodes = new Set();
        const nodes = selection.getNodes();
        const anchorNode = selection.anchor.getNode();
        if ($isSelectingEmptyListItem(anchorNode, nodes)) {
          listNodes.add($getTopListNode(anchorNode));
        } else {
          for (let i = 0; i < nodes.length; i++) {
            const node = nodes[i];
            if ($isLeafNode$1(node)) {
              const listItemNode = $getNearestNodeOfType$1(node, ListItemNode);
              if (listItemNode != null) {
                listNodes.add($getTopListNode(listItemNode));
              }
            }
          }
        }
        for (const listNode of listNodes) {
          let insertionPoint = listNode;
          const listItems = $getAllListItems(listNode);
          for (const listItemNode of listItems) {
            const paragraph = $createParagraphNode$1();
            append(paragraph, listItemNode.getChildren());
            insertionPoint.insertAfter(paragraph);
            insertionPoint = paragraph;

            // When the anchor and focus fall on the textNode
            // we don't have to change the selection because the textNode will be appended to
            // the newly generated paragraph.
            // When selection is in empty nested list item, selection is actually on the listItemNode.
            // When the corresponding listItemNode is deleted and replaced by the newly generated paragraph
            // we should manually set the selection's focus and anchor to the newly generated paragraph.
            if (listItemNode.__key === selection.anchor.key) {
              selection.anchor.set(paragraph.getKey(), 0, 'element');
            }
            if (listItemNode.__key === selection.focus.key) {
              selection.focus.set(paragraph.getKey(), 0, 'element');
            }
            listItemNode.remove();
          }
          listNode.remove();
        }
      }
    });
  }

  /**
   * Takes the value of a child ListItemNode and makes it the value the ListItemNode
   * should be if it isn't already. Also ensures that checked is undefined if the
   * parent does not have a list type of 'check'.
   * @param list - The list whose children are updated.
   */
  function updateChildrenListItemValue(list) {
    const isNotChecklist = list.getListType() !== 'check';
    let value = list.getStart();
    for (const child of list.getChildren()) {
      if ($isListItemNode(child)) {
        if (child.getValue() !== value) {
          child.setValue(value);
        }
        if (isNotChecklist && child.getLatest().__checked != null) {
          child.setChecked(undefined);
        }
        if (!$isListNode(child.getFirstChild())) {
          value++;
        }
      }
    }
  }

  /**
   * Merge the next sibling list if same type.
   * <ul> will merge with <ul>, but NOT <ul> with <ol>.
   * @param list - The list whose next sibling should be potentially merged
   */
  function mergeNextSiblingListIfSameType(list) {
    const nextSibling = list.getNextSibling();
    if ($isListNode(nextSibling) && list.getListType() === nextSibling.getListType()) {
      mergeLists(list, nextSibling);
    }
  }

  /**
   * Adds an empty ListNode/ListItemNode chain at listItemNode, so as to
   * create an indent effect. Won't indent ListItemNodes that have a ListNode as
   * a child, but does merge sibling ListItemNodes if one has a nested ListNode.
   * @param listItemNode - The ListItemNode to be indented.
   */
  function $handleIndent(listItemNode) {
    // go through each node and decide where to move it.
    const removed = new Set();
    if (isNestedListNode(listItemNode) || removed.has(listItemNode.getKey())) {
      return;
    }
    const parent = listItemNode.getParent();

    // We can cast both of the below `isNestedListNode` only returns a boolean type instead of a user-defined type guards
    const nextSibling = listItemNode.getNextSibling();
    const previousSibling = listItemNode.getPreviousSibling();
    // if there are nested lists on either side, merge them all together.

    if (isNestedListNode(nextSibling) && isNestedListNode(previousSibling)) {
      const innerList = previousSibling.getFirstChild();
      if ($isListNode(innerList)) {
        innerList.append(listItemNode);
        const nextInnerList = nextSibling.getFirstChild();
        if ($isListNode(nextInnerList)) {
          const children = nextInnerList.getChildren();
          append(innerList, children);
          nextSibling.remove();
          removed.add(nextSibling.getKey());
        }
      }
    } else if (isNestedListNode(nextSibling)) {
      // if the ListItemNode is next to a nested ListNode, merge them
      const innerList = nextSibling.getFirstChild();
      if ($isListNode(innerList)) {
        const firstChild = innerList.getFirstChild();
        if (firstChild !== null) {
          firstChild.insertBefore(listItemNode);
        }
      }
    } else if (isNestedListNode(previousSibling)) {
      const innerList = previousSibling.getFirstChild();
      if ($isListNode(innerList)) {
        innerList.append(listItemNode);
      }
    } else {
      // otherwise, we need to create a new nested ListNode

      if ($isListNode(parent)) {
        const newListItem = $createListItemNode();
        const newList = $createListNode(parent.getListType());
        newListItem.append(newList);
        newList.append(listItemNode);
        if (previousSibling) {
          previousSibling.insertAfter(newListItem);
        } else if (nextSibling) {
          nextSibling.insertBefore(newListItem);
        } else {
          parent.append(newListItem);
        }
      }
    }
  }

  /**
   * Removes an indent by removing an empty ListNode/ListItemNode chain. An indented ListItemNode
   * has a great grandparent node of type ListNode, which is where the ListItemNode will reside
   * within as a child.
   * @param listItemNode - The ListItemNode to remove the indent (outdent).
   */
  function $handleOutdent(listItemNode) {
    // go through each node and decide where to move it.

    if (isNestedListNode(listItemNode)) {
      return;
    }
    const parentList = listItemNode.getParent();
    const grandparentListItem = parentList ? parentList.getParent() : undefined;
    const greatGrandparentList = grandparentListItem ? grandparentListItem.getParent() : undefined;
    // If it doesn't have these ancestors, it's not indented.

    if ($isListNode(greatGrandparentList) && $isListItemNode(grandparentListItem) && $isListNode(parentList)) {
      // if it's the first child in it's parent list, insert it into the
      // great grandparent list before the grandparent
      const firstChild = parentList ? parentList.getFirstChild() : undefined;
      const lastChild = parentList ? parentList.getLastChild() : undefined;
      if (listItemNode.is(firstChild)) {
        grandparentListItem.insertBefore(listItemNode);
        if (parentList.isEmpty()) {
          grandparentListItem.remove();
        }
        // if it's the last child in it's parent list, insert it into the
        // great grandparent list after the grandparent.
      } else if (listItemNode.is(lastChild)) {
        grandparentListItem.insertAfter(listItemNode);
        if (parentList.isEmpty()) {
          grandparentListItem.remove();
        }
      } else {
        // otherwise, we need to split the siblings into two new nested lists
        const listType = parentList.getListType();
        const previousSiblingsListItem = $createListItemNode();
        const previousSiblingsList = $createListNode(listType);
        previousSiblingsListItem.append(previousSiblingsList);
        listItemNode.getPreviousSiblings().forEach(sibling => previousSiblingsList.append(sibling));
        const nextSiblingsListItem = $createListItemNode();
        const nextSiblingsList = $createListNode(listType);
        nextSiblingsListItem.append(nextSiblingsList);
        append(nextSiblingsList, listItemNode.getNextSiblings());
        // put the sibling nested lists on either side of the grandparent list item in the great grandparent.
        grandparentListItem.insertBefore(previousSiblingsListItem);
        grandparentListItem.insertAfter(nextSiblingsListItem);
        // replace the grandparent list item (now between the siblings) with the outdented list item.
        grandparentListItem.replace(listItemNode);
      }
    }
  }

  /**
   * Attempts to insert a ParagraphNode at selection and selects the new node. The selection must contain a ListItemNode
   * or a node that does not already contain text. If its grandparent is the root/shadow root, it will get the ListNode
   * (which should be the parent node) and insert the ParagraphNode as a sibling to the ListNode. If the ListNode is
   * nested in a ListItemNode instead, it will add the ParagraphNode after the grandparent ListItemNode.
   * Throws an invariant if the selection is not a child of a ListNode.
   * @returns true if a ParagraphNode was inserted succesfully, false if there is no selection
   * or the selection does not contain a ListItemNode or the node already holds text.
   */
  function $handleListInsertParagraph() {
    const selection = $getSelection$1();
    if (!$isRangeSelection$1(selection) || !selection.isCollapsed()) {
      return false;
    }
    // Only run this code on empty list items
    const anchor = selection.anchor.getNode();
    if (!$isListItemNode(anchor) || anchor.getChildrenSize() !== 0) {
      return false;
    }
    const topListNode = $getTopListNode(anchor);
    const parent = anchor.getParent();
    if (!$isListNode(parent)) {
      throw Error(`A ListItemNode must have a ListNode for a parent.`);
    }
    const grandparent = parent.getParent();
    let replacementNode;
    if ($isRootOrShadowRoot$1(grandparent)) {
      replacementNode = $createParagraphNode$1();
      topListNode.insertAfter(replacementNode);
    } else if ($isListItemNode(grandparent)) {
      replacementNode = $createListItemNode();
      grandparent.insertAfter(replacementNode);
    } else {
      return false;
    }
    replacementNode.select();
    const nextSiblings = anchor.getNextSiblings();
    if (nextSiblings.length > 0) {
      const newList = $createListNode(parent.getListType());
      if ($isParagraphNode$1(replacementNode)) {
        replacementNode.insertAfter(newList);
      } else {
        const newListItem = $createListItemNode();
        newListItem.append(newList);
        replacementNode.insertAfter(newListItem);
      }
      nextSiblings.forEach(sibling => {
        sibling.remove();
        newList.append(sibling);
      });
    }

    // Don't leave hanging nested empty lists
    $removeHighestEmptyListParent(anchor);
    return true;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function normalizeClassNames$2(...classNames) {
    const rval = [];
    for (const className of classNames) {
      if (className && typeof className === 'string') {
        for (const [s] of className.matchAll(/\S+/g)) {
          rval.push(s);
        }
      }
    }
    return rval;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class ListItemNode extends ElementNode$1 {
    /** @internal */

    /** @internal */

    static getType() {
      return 'listitem';
    }
    static clone(node) {
      return new ListItemNode(node.__value, node.__checked, node.__key);
    }
    constructor(value, checked, key) {
      super(key);
      this.__value = value === undefined ? 1 : value;
      this.__checked = checked;
    }
    createDOM(config) {
      const element = document.createElement('li');
      const parent = this.getParent();
      if ($isListNode(parent) && parent.getListType() === 'check') {
        updateListItemChecked(element, this, null);
      }
      element.value = this.__value;
      $setListItemThemeClassNames(element, config.theme, this);
      return element;
    }
    updateDOM(prevNode, dom, config) {
      const parent = this.getParent();
      if ($isListNode(parent) && parent.getListType() === 'check') {
        updateListItemChecked(dom, this, prevNode);
      }
      // @ts-expect-error - this is always HTMLListItemElement
      dom.value = this.__value;
      $setListItemThemeClassNames(dom, config.theme, this);
      return false;
    }
    static transform() {
      return node => {
        if (!$isListItemNode(node)) {
          throw Error(`node is not a ListItemNode`);
        }
        if (node.__checked == null) {
          return;
        }
        const parent = node.getParent();
        if ($isListNode(parent)) {
          if (parent.getListType() !== 'check' && node.getChecked() != null) {
            node.setChecked(undefined);
          }
        }
      };
    }
    static importDOM() {
      return {
        li: () => ({
          conversion: $convertListItemElement,
          priority: 0
        })
      };
    }
    static importJSON(serializedNode) {
      const node = $createListItemNode();
      node.setChecked(serializedNode.checked);
      node.setValue(serializedNode.value);
      node.setFormat(serializedNode.format);
      node.setDirection(serializedNode.direction);
      return node;
    }
    exportDOM(editor) {
      const element = this.createDOM(editor._config);
      element.style.textAlign = this.getFormatType();
      return {
        element
      };
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        checked: this.getChecked(),
        type: 'listitem',
        value: this.getValue(),
        version: 1
      };
    }
    append(...nodes) {
      for (let i = 0; i < nodes.length; i++) {
        const node = nodes[i];
        if ($isElementNode$1(node) && this.canMergeWith(node)) {
          const children = node.getChildren();
          this.append(...children);
          node.remove();
        } else {
          super.append(node);
        }
      }
      return this;
    }
    replace(replaceWithNode, includeChildren) {
      if ($isListItemNode(replaceWithNode)) {
        return super.replace(replaceWithNode);
      }
      this.setIndent(0);
      const list = this.getParentOrThrow();
      if (!$isListNode(list)) {
        return replaceWithNode;
      }
      if (list.__first === this.getKey()) {
        list.insertBefore(replaceWithNode);
      } else if (list.__last === this.getKey()) {
        list.insertAfter(replaceWithNode);
      } else {
        // Split the list
        const newList = $createListNode(list.getListType());
        let nextSibling = this.getNextSibling();
        while (nextSibling) {
          const nodeToAppend = nextSibling;
          nextSibling = nextSibling.getNextSibling();
          newList.append(nodeToAppend);
        }
        list.insertAfter(replaceWithNode);
        replaceWithNode.insertAfter(newList);
      }
      if (includeChildren) {
        if (!$isElementNode$1(replaceWithNode)) {
          throw Error(`includeChildren should only be true for ElementNodes`);
        }
        this.getChildren().forEach(child => {
          replaceWithNode.append(child);
        });
      }
      this.remove();
      if (list.getChildrenSize() === 0) {
        list.remove();
      }
      return replaceWithNode;
    }
    insertAfter(node, restoreSelection = true) {
      const listNode = this.getParentOrThrow();
      if (!$isListNode(listNode)) {
        {
          throw Error(`insertAfter: list node is not parent of list item node`);
        }
      }
      if ($isListItemNode(node)) {
        return super.insertAfter(node, restoreSelection);
      }
      const siblings = this.getNextSiblings();

      // Split the lists and insert the node in between them
      listNode.insertAfter(node, restoreSelection);
      if (siblings.length !== 0) {
        const newListNode = $createListNode(listNode.getListType());
        siblings.forEach(sibling => newListNode.append(sibling));
        node.insertAfter(newListNode, restoreSelection);
      }
      return node;
    }
    remove(preserveEmptyParent) {
      const prevSibling = this.getPreviousSibling();
      const nextSibling = this.getNextSibling();
      super.remove(preserveEmptyParent);
      if (prevSibling && nextSibling && isNestedListNode(prevSibling) && isNestedListNode(nextSibling)) {
        mergeLists(prevSibling.getFirstChild(), nextSibling.getFirstChild());
        nextSibling.remove();
      }
    }
    insertNewAfter(_, restoreSelection = true) {
      const newElement = $createListItemNode(this.__checked == null ? undefined : false);
      this.insertAfter(newElement, restoreSelection);
      return newElement;
    }
    collapseAtStart(selection) {
      const paragraph = $createParagraphNode$1();
      const children = this.getChildren();
      children.forEach(child => paragraph.append(child));
      const listNode = this.getParentOrThrow();
      const listNodeParent = listNode.getParentOrThrow();
      const isIndented = $isListItemNode(listNodeParent);
      if (listNode.getChildrenSize() === 1) {
        if (isIndented) {
          // if the list node is nested, we just want to remove it,
          // effectively unindenting it.
          listNode.remove();
          listNodeParent.select();
        } else {
          listNode.insertBefore(paragraph);
          listNode.remove();
          // If we have selection on the list item, we'll need to move it
          // to the paragraph
          const anchor = selection.anchor;
          const focus = selection.focus;
          const key = paragraph.getKey();
          if (anchor.type === 'element' && anchor.getNode().is(this)) {
            anchor.set(key, anchor.offset, 'element');
          }
          if (focus.type === 'element' && focus.getNode().is(this)) {
            focus.set(key, focus.offset, 'element');
          }
        }
      } else {
        listNode.insertBefore(paragraph);
        this.remove();
      }
      return true;
    }
    getValue() {
      const self = this.getLatest();
      return self.__value;
    }
    setValue(value) {
      const self = this.getWritable();
      self.__value = value;
    }
    getChecked() {
      const self = this.getLatest();
      let listType;
      const parent = this.getParent();
      if ($isListNode(parent)) {
        listType = parent.getListType();
      }
      return listType === 'check' ? Boolean(self.__checked) : undefined;
    }
    setChecked(checked) {
      const self = this.getWritable();
      self.__checked = checked;
    }
    toggleChecked() {
      this.setChecked(!this.__checked);
    }
    getIndent() {
      // If we don't have a parent, we are likely serializing
      const parent = this.getParent();
      if (parent === null) {
        return this.getLatest().__indent;
      }
      // ListItemNode should always have a ListNode for a parent.
      let listNodeParent = parent.getParentOrThrow();
      let indentLevel = 0;
      while ($isListItemNode(listNodeParent)) {
        listNodeParent = listNodeParent.getParentOrThrow().getParentOrThrow();
        indentLevel++;
      }
      return indentLevel;
    }
    setIndent(indent) {
      if (!(typeof indent === 'number')) {
        throw Error(`Invalid indent value.`);
      }
      indent = Math.floor(indent);
      if (!(indent >= 0)) {
        throw Error(`Indent value must be non-negative.`);
      }
      let currentIndent = this.getIndent();
      while (currentIndent !== indent) {
        if (currentIndent < indent) {
          $handleIndent(this);
          currentIndent++;
        } else {
          $handleOutdent(this);
          currentIndent--;
        }
      }
      return this;
    }

    /** @deprecated @internal */
    canInsertAfter(node) {
      return $isListItemNode(node);
    }

    /** @deprecated @internal */
    canReplaceWith(replacement) {
      return $isListItemNode(replacement);
    }
    canMergeWith(node) {
      return $isParagraphNode$1(node) || $isListItemNode(node);
    }
    extractWithChild(child, selection) {
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      const anchorNode = selection.anchor.getNode();
      const focusNode = selection.focus.getNode();
      return this.isParentOf(anchorNode) && this.isParentOf(focusNode) && this.getTextContent().length === selection.getTextContent().length;
    }
    isParentRequired() {
      return true;
    }
    createParentElementNode() {
      return $createListNode('bullet');
    }
    canMergeWhenEmpty() {
      return true;
    }
  }
  function $setListItemThemeClassNames(dom, editorThemeClasses, node) {
    const classesToAdd = [];
    const classesToRemove = [];
    const listTheme = editorThemeClasses.list;
    const listItemClassName = listTheme ? listTheme.listitem : undefined;
    let nestedListItemClassName;
    if (listTheme && listTheme.nested) {
      nestedListItemClassName = listTheme.nested.listitem;
    }
    if (listItemClassName !== undefined) {
      classesToAdd.push(...normalizeClassNames$2(listItemClassName));
    }
    if (listTheme) {
      const parentNode = node.getParent();
      const isCheckList = $isListNode(parentNode) && parentNode.getListType() === 'check';
      const checked = node.getChecked();
      if (!isCheckList || checked) {
        classesToRemove.push(listTheme.listitemUnchecked);
      }
      if (!isCheckList || !checked) {
        classesToRemove.push(listTheme.listitemChecked);
      }
      if (isCheckList) {
        classesToAdd.push(checked ? listTheme.listitemChecked : listTheme.listitemUnchecked);
      }
    }
    if (nestedListItemClassName !== undefined) {
      const nestedListItemClasses = normalizeClassNames$2(nestedListItemClassName);
      if (node.getChildren().some(child => $isListNode(child))) {
        classesToAdd.push(...nestedListItemClasses);
      } else {
        classesToRemove.push(...nestedListItemClasses);
      }
    }
    if (classesToRemove.length > 0) {
      removeClassNamesFromElement$1(dom, ...classesToRemove);
    }
    if (classesToAdd.length > 0) {
      addClassNamesToElement$1(dom, ...classesToAdd);
    }
  }
  function updateListItemChecked(dom, listItemNode, prevListItemNode, listNode) {
    // Only add attributes for leaf list items
    if ($isListNode(listItemNode.getFirstChild())) {
      dom.removeAttribute('role');
      dom.removeAttribute('tabIndex');
      dom.removeAttribute('aria-checked');
    } else {
      dom.setAttribute('role', 'checkbox');
      dom.setAttribute('tabIndex', '-1');
      if (!prevListItemNode || listItemNode.__checked !== prevListItemNode.__checked) {
        dom.setAttribute('aria-checked', listItemNode.getChecked() ? 'true' : 'false');
      }
    }
  }
  function $convertListItemElement(domNode) {
    const isGitHubCheckList = domNode.classList.contains('task-list-item');
    if (isGitHubCheckList) {
      for (const child of domNode.children) {
        if (child.tagName === 'INPUT') {
          return $convertCheckboxInput(child);
        }
      }
    }
    const ariaCheckedAttr = domNode.getAttribute('aria-checked');
    const checked = ariaCheckedAttr === 'true' ? true : ariaCheckedAttr === 'false' ? false : undefined;
    return {
      node: $createListItemNode(checked)
    };
  }
  function $convertCheckboxInput(domNode) {
    const isCheckboxInput = domNode.getAttribute('type') === 'checkbox';
    if (!isCheckboxInput) {
      return {
        node: null
      };
    }
    const checked = domNode.hasAttribute('checked');
    return {
      node: $createListItemNode(checked)
    };
  }

  /**
   * Creates a new List Item node, passing true/false will convert it to a checkbox input.
   * @param checked - Is the List Item a checkbox and, if so, is it checked? undefined/null: not a checkbox, true/false is a checkbox and checked/unchecked, respectively.
   * @returns The new List Item.
   */
  function $createListItemNode(checked) {
    return $applyNodeReplacement$1(new ListItemNode(undefined, checked));
  }

  /**
   * Checks to see if the node is a ListItemNode.
   * @param node - The node to be checked.
   * @returns true if the node is a ListItemNode, false otherwise.
   */
  function $isListItemNode(node) {
    return node instanceof ListItemNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class ListNode extends ElementNode$1 {
    /** @internal */

    /** @internal */

    /** @internal */

    static getType() {
      return 'list';
    }
    static clone(node) {
      const listType = node.__listType || TAG_TO_LIST_TYPE[node.__tag];
      return new ListNode(listType, node.__start, node.__key);
    }
    constructor(listType, start, key) {
      super(key);
      const _listType = TAG_TO_LIST_TYPE[listType] || listType;
      this.__listType = _listType;
      this.__tag = _listType === 'number' ? 'ol' : 'ul';
      this.__start = start;
    }
    getTag() {
      return this.__tag;
    }
    setListType(type) {
      const writable = this.getWritable();
      writable.__listType = type;
      writable.__tag = type === 'number' ? 'ol' : 'ul';
    }
    getListType() {
      return this.__listType;
    }
    getStart() {
      return this.__start;
    }

    // View

    createDOM(config, _editor) {
      const tag = this.__tag;
      const dom = document.createElement(tag);
      if (this.__start !== 1) {
        dom.setAttribute('start', String(this.__start));
      }
      // @ts-expect-error Internal field.
      dom.__lexicalListType = this.__listType;
      $setListThemeClassNames(dom, config.theme, this);
      return dom;
    }
    updateDOM(prevNode, dom, config) {
      if (prevNode.__tag !== this.__tag) {
        return true;
      }
      $setListThemeClassNames(dom, config.theme, this);
      return false;
    }
    static transform() {
      return node => {
        if (!$isListNode(node)) {
          throw Error(`node is not a ListNode`);
        }
        mergeNextSiblingListIfSameType(node);
        updateChildrenListItemValue(node);
      };
    }
    static importDOM() {
      return {
        ol: () => ({
          conversion: $convertListNode,
          priority: 0
        }),
        ul: () => ({
          conversion: $convertListNode,
          priority: 0
        })
      };
    }
    static importJSON(serializedNode) {
      const node = $createListNode(serializedNode.listType, serializedNode.start);
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    exportDOM(editor) {
      const {
        element
      } = super.exportDOM(editor);
      if (element && isHTMLElement$2(element)) {
        if (this.__start !== 1) {
          element.setAttribute('start', String(this.__start));
        }
        if (this.__listType === 'check') {
          element.setAttribute('__lexicalListType', 'check');
        }
      }
      return {
        element
      };
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        listType: this.getListType(),
        start: this.getStart(),
        tag: this.getTag(),
        type: 'list',
        version: 1
      };
    }
    canBeEmpty() {
      return false;
    }
    canIndent() {
      return false;
    }
    append(...nodesToAppend) {
      for (let i = 0; i < nodesToAppend.length; i++) {
        const currentNode = nodesToAppend[i];
        if ($isListItemNode(currentNode)) {
          super.append(currentNode);
        } else {
          const listItemNode = $createListItemNode();
          if ($isListNode(currentNode)) {
            listItemNode.append(currentNode);
          } else if ($isElementNode$1(currentNode)) {
            const textNode = $createTextNode$1(currentNode.getTextContent());
            listItemNode.append(textNode);
          } else {
            listItemNode.append(currentNode);
          }
          super.append(listItemNode);
        }
      }
      return this;
    }
    extractWithChild(child) {
      return $isListItemNode(child);
    }
  }
  function $setListThemeClassNames(dom, editorThemeClasses, node) {
    const classesToAdd = [];
    const classesToRemove = [];
    const listTheme = editorThemeClasses.list;
    if (listTheme !== undefined) {
      const listLevelsClassNames = listTheme[`${node.__tag}Depth`] || [];
      const listDepth = $getListDepth(node) - 1;
      const normalizedListDepth = listDepth % listLevelsClassNames.length;
      const listLevelClassName = listLevelsClassNames[normalizedListDepth];
      const listClassName = listTheme[node.__tag];
      let nestedListClassName;
      const nestedListTheme = listTheme.nested;
      const checklistClassName = listTheme.checklist;
      if (nestedListTheme !== undefined && nestedListTheme.list) {
        nestedListClassName = nestedListTheme.list;
      }
      if (listClassName !== undefined) {
        classesToAdd.push(listClassName);
      }
      if (checklistClassName !== undefined && node.__listType === 'check') {
        classesToAdd.push(checklistClassName);
      }
      if (listLevelClassName !== undefined) {
        classesToAdd.push(...normalizeClassNames$2(listLevelClassName));
        for (let i = 0; i < listLevelsClassNames.length; i++) {
          if (i !== normalizedListDepth) {
            classesToRemove.push(node.__tag + i);
          }
        }
      }
      if (nestedListClassName !== undefined) {
        const nestedListItemClasses = normalizeClassNames$2(nestedListClassName);
        if (listDepth > 1) {
          classesToAdd.push(...nestedListItemClasses);
        } else {
          classesToRemove.push(...nestedListItemClasses);
        }
      }
    }
    if (classesToRemove.length > 0) {
      removeClassNamesFromElement$1(dom, ...classesToRemove);
    }
    if (classesToAdd.length > 0) {
      addClassNamesToElement$1(dom, ...classesToAdd);
    }
  }

  /*
   * This function normalizes the children of a ListNode after the conversion from HTML,
   * ensuring that they are all ListItemNodes and contain either a single nested ListNode
   * or some other inline content.
   */
  function $normalizeChildren(nodes) {
    const normalizedListItems = [];
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      if ($isListItemNode(node)) {
        normalizedListItems.push(node);
        const children = node.getChildren();
        if (children.length > 1) {
          children.forEach(child => {
            if ($isListNode(child)) {
              normalizedListItems.push($wrapInListItem(child));
            }
          });
        }
      } else {
        normalizedListItems.push($wrapInListItem(node));
      }
    }
    return normalizedListItems;
  }
  function isDomChecklist(domNode) {
    if (domNode.getAttribute('__lexicallisttype') === 'check' ||
    // is github checklist
    domNode.classList.contains('contains-task-list')) {
      return true;
    }
    // if children are checklist items, the node is a checklist ul. Applicable for googledoc checklist pasting.
    for (const child of domNode.childNodes) {
      if (isHTMLElement$2(child) && child.hasAttribute('aria-checked')) {
        return true;
      }
    }
    return false;
  }
  function $convertListNode(domNode) {
    const nodeName = domNode.nodeName.toLowerCase();
    let node = null;
    if (nodeName === 'ol') {
      // @ts-ignore
      const start = domNode.start;
      node = $createListNode('number', start);
    } else if (nodeName === 'ul') {
      if (isDomChecklist(domNode)) {
        node = $createListNode('check');
      } else {
        node = $createListNode('bullet');
      }
    }
    return {
      after: $normalizeChildren,
      node
    };
  }
  const TAG_TO_LIST_TYPE = {
    ol: 'number',
    ul: 'bullet'
  };

  /**
   * Creates a ListNode of listType.
   * @param listType - The type of list to be created. Can be 'number', 'bullet', or 'check'.
   * @param start - Where an ordered list starts its count, start = 1 if left undefined.
   * @returns The new ListNode
   */
  function $createListNode(listType, start = 1) {
    return $applyNodeReplacement$1(new ListNode(listType, start));
  }

  /**
   * Checks to see if the node is a ListNode.
   * @param node - The node to be checked.
   * @returns true if the node is a ListNode, false otherwise.
   */
  function $isListNode(node) {
    return node instanceof ListNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const INSERT_UNORDERED_LIST_COMMAND = createCommand$1('INSERT_UNORDERED_LIST_COMMAND');
  const INSERT_ORDERED_LIST_COMMAND = createCommand$1('INSERT_ORDERED_LIST_COMMAND');
  const INSERT_CHECK_LIST_COMMAND = createCommand$1('INSERT_CHECK_LIST_COMMAND');
  const REMOVE_LIST_COMMAND = createCommand$1('REMOVE_LIST_COMMAND');

  var modDev$4 = /*#__PURE__*/Object.freeze({
    $createListItemNode: $createListItemNode,
    $createListNode: $createListNode,
    $getListDepth: $getListDepth,
    $handleListInsertParagraph: $handleListInsertParagraph,
    $isListItemNode: $isListItemNode,
    $isListNode: $isListNode,
    INSERT_CHECK_LIST_COMMAND: INSERT_CHECK_LIST_COMMAND,
    INSERT_ORDERED_LIST_COMMAND: INSERT_ORDERED_LIST_COMMAND,
    INSERT_UNORDERED_LIST_COMMAND: INSERT_UNORDERED_LIST_COMMAND,
    ListItemNode: ListItemNode,
    ListNode: ListNode,
    REMOVE_LIST_COMMAND: REMOVE_LIST_COMMAND,
    insertList: insertList,
    removeList: removeList
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  function p$3(e) {
    return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
  }
  var _$3 = p$3(function (e) {
    const t = new URLSearchParams();
    t.append("code", e);
    for (let e = 1; e < arguments.length; e++) t.append("v", arguments[e]);
    throw Error(`Minified Lexical error #${e}; visit https://lexical.dev/docs/error?${t} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });
  const j$3 = createCommand$1("INSERT_UNORDERED_LIST_COMMAND"),
    q$2 = createCommand$1("INSERT_ORDERED_LIST_COMMAND"),
    H$3 = createCommand$1("INSERT_CHECK_LIST_COMMAND"),
    G$2 = createCommand$1("REMOVE_LIST_COMMAND");

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$4 = modDev$4;
  const $createListItemNode$1 = mod$4.$createListItemNode;
  const $createListNode$1 = mod$4.$createListNode;
  const $getListDepth$1 = mod$4.$getListDepth;
  const $handleListInsertParagraph$1 = mod$4.$handleListInsertParagraph;
  const $isListItemNode$1 = mod$4.$isListItemNode;
  const $isListNode$1 = mod$4.$isListNode;
  const INSERT_CHECK_LIST_COMMAND$1 = mod$4.INSERT_CHECK_LIST_COMMAND;
  const INSERT_ORDERED_LIST_COMMAND$1 = mod$4.INSERT_ORDERED_LIST_COMMAND;
  const INSERT_UNORDERED_LIST_COMMAND$1 = mod$4.INSERT_UNORDERED_LIST_COMMAND;
  const ListItemNode$1 = mod$4.ListItemNode;
  const ListNode$1 = mod$4.ListNode;
  const REMOVE_LIST_COMMAND$1 = mod$4.REMOVE_LIST_COMMAND;
  const insertList$1 = mod$4.insertList;
  const removeList$1 = mod$4.removeList;

  var LexicalList = /*#__PURE__*/Object.freeze({
    $createListItemNode: $createListItemNode$1,
    $createListNode: $createListNode$1,
    $getListDepth: $getListDepth$1,
    $handleListInsertParagraph: $handleListInsertParagraph$1,
    $isListItemNode: $isListItemNode$1,
    $isListNode: $isListNode$1,
    INSERT_CHECK_LIST_COMMAND: INSERT_CHECK_LIST_COMMAND$1,
    INSERT_ORDERED_LIST_COMMAND: INSERT_ORDERED_LIST_COMMAND$1,
    INSERT_UNORDERED_LIST_COMMAND: INSERT_UNORDERED_LIST_COMMAND$1,
    ListItemNode: ListItemNode$1,
    ListNode: ListNode$1,
    REMOVE_LIST_COMMAND: REMOVE_LIST_COMMAND$1,
    insertList: insertList$1,
    removeList: removeList$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const SUPPORTED_URL_PROTOCOLS = new Set(['http:', 'https:', 'mailto:', 'sms:', 'tel:']);

  /** @noInheritDoc */
  class LinkNode extends ElementNode$1 {
    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    static getType() {
      return 'link';
    }
    static clone(node) {
      return new LinkNode(node.__url, {
        rel: node.__rel,
        target: node.__target,
        title: node.__title
      }, node.__key);
    }
    constructor(url, attributes = {}, key) {
      super(key);
      const {
        target = null,
        rel = null,
        title = null
      } = attributes;
      this.__url = url;
      this.__target = target;
      this.__rel = rel;
      this.__title = title;
    }
    createDOM(config) {
      const element = document.createElement('a');
      element.href = this.sanitizeUrl(this.__url);
      if (this.__target !== null) {
        element.target = this.__target;
      }
      if (this.__rel !== null) {
        element.rel = this.__rel;
      }
      if (this.__title !== null) {
        element.title = this.__title;
      }
      addClassNamesToElement$1(element, config.theme.link);
      return element;
    }
    updateDOM(prevNode, anchor, config) {
      if (anchor instanceof HTMLAnchorElement) {
        const url = this.__url;
        const target = this.__target;
        const rel = this.__rel;
        const title = this.__title;
        if (url !== prevNode.__url) {
          anchor.href = url;
        }
        if (target !== prevNode.__target) {
          if (target) {
            anchor.target = target;
          } else {
            anchor.removeAttribute('target');
          }
        }
        if (rel !== prevNode.__rel) {
          if (rel) {
            anchor.rel = rel;
          } else {
            anchor.removeAttribute('rel');
          }
        }
        if (title !== prevNode.__title) {
          if (title) {
            anchor.title = title;
          } else {
            anchor.removeAttribute('title');
          }
        }
      }
      return false;
    }
    static importDOM() {
      return {
        a: node => ({
          conversion: $convertAnchorElement,
          priority: 1
        })
      };
    }
    static importJSON(serializedNode) {
      const node = $createLinkNode(serializedNode.url, {
        rel: serializedNode.rel,
        target: serializedNode.target,
        title: serializedNode.title
      });
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    sanitizeUrl(url) {
      try {
        const parsedUrl = new URL(url);
        // eslint-disable-next-line no-script-url
        if (!SUPPORTED_URL_PROTOCOLS.has(parsedUrl.protocol)) {
          return 'about:blank';
        }
      } catch (_unused) {
        return url;
      }
      return url;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        rel: this.getRel(),
        target: this.getTarget(),
        title: this.getTitle(),
        type: 'link',
        url: this.getURL(),
        version: 1
      };
    }
    getURL() {
      return this.getLatest().__url;
    }
    setURL(url) {
      const writable = this.getWritable();
      writable.__url = url;
    }
    getTarget() {
      return this.getLatest().__target;
    }
    setTarget(target) {
      const writable = this.getWritable();
      writable.__target = target;
    }
    getRel() {
      return this.getLatest().__rel;
    }
    setRel(rel) {
      const writable = this.getWritable();
      writable.__rel = rel;
    }
    getTitle() {
      return this.getLatest().__title;
    }
    setTitle(title) {
      const writable = this.getWritable();
      writable.__title = title;
    }
    insertNewAfter(_, restoreSelection = true) {
      const linkNode = $createLinkNode(this.__url, {
        rel: this.__rel,
        target: this.__target,
        title: this.__title
      });
      this.insertAfter(linkNode, restoreSelection);
      return linkNode;
    }
    canInsertTextBefore() {
      return false;
    }
    canInsertTextAfter() {
      return false;
    }
    canBeEmpty() {
      return false;
    }
    isInline() {
      return true;
    }
    extractWithChild(child, selection, destination) {
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      const anchorNode = selection.anchor.getNode();
      const focusNode = selection.focus.getNode();
      return this.isParentOf(anchorNode) && this.isParentOf(focusNode) && selection.getTextContent().length > 0;
    }
    isEmailURI() {
      return this.__url.startsWith('mailto:');
    }
    isWebSiteURI() {
      return this.__url.startsWith('https://') || this.__url.startsWith('http://');
    }
  }
  function $convertAnchorElement(domNode) {
    let node = null;
    if (isHTMLAnchorElement$2(domNode)) {
      const content = domNode.textContent;
      if (content !== null && content !== '' || domNode.children.length > 0) {
        node = $createLinkNode(domNode.getAttribute('href') || '', {
          rel: domNode.getAttribute('rel'),
          target: domNode.getAttribute('target'),
          title: domNode.getAttribute('title')
        });
      }
    }
    return {
      node
    };
  }

  /**
   * Takes a URL and creates a LinkNode.
   * @param url - The URL the LinkNode should direct to.
   * @param attributes - Optional HTML a tag attributes \\{ target, rel, title \\}
   * @returns The LinkNode.
   */
  function $createLinkNode(url, attributes) {
    return $applyNodeReplacement$1(new LinkNode(url, attributes));
  }

  /**
   * Determines if node is a LinkNode.
   * @param node - The node to be checked.
   * @returns true if node is a LinkNode, false otherwise.
   */
  function $isLinkNode(node) {
    return node instanceof LinkNode;
  }
  // Custom node type to override `canInsertTextAfter` that will
  // allow typing within the link
  class AutoLinkNode extends LinkNode {
    /** @internal */
    /** Indicates whether the autolink was ever unlinked. **/

    constructor(url, attributes = {}, key) {
      super(url, attributes, key);
      this.__isUnlinked = attributes.isUnlinked !== undefined && attributes.isUnlinked !== null ? attributes.isUnlinked : false;
    }
    static getType() {
      return 'autolink';
    }
    static clone(node) {
      return new AutoLinkNode(node.__url, {
        isUnlinked: node.__isUnlinked,
        rel: node.__rel,
        target: node.__target,
        title: node.__title
      }, node.__key);
    }
    getIsUnlinked() {
      return this.__isUnlinked;
    }
    setIsUnlinked(value) {
      const self = this.getWritable();
      self.__isUnlinked = value;
      return self;
    }
    createDOM(config) {
      if (this.__isUnlinked) {
        return document.createElement('span');
      } else {
        return super.createDOM(config);
      }
    }
    updateDOM(prevNode, anchor, config) {
      return super.updateDOM(prevNode, anchor, config) || prevNode.__isUnlinked !== this.__isUnlinked;
    }
    static importJSON(serializedNode) {
      const node = $createAutoLinkNode(serializedNode.url, {
        isUnlinked: serializedNode.isUnlinked,
        rel: serializedNode.rel,
        target: serializedNode.target,
        title: serializedNode.title
      });
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    static importDOM() {
      // TODO: Should link node should handle the import over autolink?
      return null;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        isUnlinked: this.__isUnlinked,
        type: 'autolink',
        version: 1
      };
    }
    insertNewAfter(selection, restoreSelection = true) {
      const element = this.getParentOrThrow().insertNewAfter(selection, restoreSelection);
      if ($isElementNode$1(element)) {
        const linkNode = $createAutoLinkNode(this.__url, {
          isUnlinked: this.__isUnlinked,
          rel: this.__rel,
          target: this.__target,
          title: this.__title
        });
        element.append(linkNode);
        return linkNode;
      }
      return null;
    }
  }

  /**
   * Takes a URL and creates an AutoLinkNode. AutoLinkNodes are generally automatically generated
   * during typing, which is especially useful when a button to generate a LinkNode is not practical.
   * @param url - The URL the LinkNode should direct to.
   * @param attributes - Optional HTML a tag attributes. \\{ target, rel, title \\}
   * @returns The LinkNode.
   */
  function $createAutoLinkNode(url, attributes) {
    return $applyNodeReplacement$1(new AutoLinkNode(url, attributes));
  }

  /**
   * Determines if node is an AutoLinkNode.
   * @param node - The node to be checked.
   * @returns true if node is an AutoLinkNode, false otherwise.
   */
  function $isAutoLinkNode(node) {
    return node instanceof AutoLinkNode;
  }
  const TOGGLE_LINK_COMMAND = createCommand$1('TOGGLE_LINK_COMMAND');

  /**
   * Generates or updates a LinkNode. It can also delete a LinkNode if the URL is null,
   * but saves any children and brings them up to the parent node.
   * @param url - The URL the link directs to.
   * @param attributes - Optional HTML a tag attributes. \\{ target, rel, title \\}
   */
  function $toggleLink(url, attributes = {}) {
    const {
      target,
      title
    } = attributes;
    const rel = attributes.rel === undefined ? 'noreferrer' : attributes.rel;
    const selection = $getSelection$1();
    if (!$isRangeSelection$1(selection)) {
      return;
    }
    const nodes = selection.extract();
    if (url === null) {
      // Remove LinkNodes
      nodes.forEach(node => {
        const parent = node.getParent();
        if (!$isAutoLinkNode(parent) && $isLinkNode(parent)) {
          const children = parent.getChildren();
          for (let i = 0; i < children.length; i++) {
            parent.insertBefore(children[i]);
          }
          parent.remove();
        }
      });
    } else {
      // Add or merge LinkNodes
      if (nodes.length === 1) {
        const firstNode = nodes[0];
        // if the first node is a LinkNode or if its
        // parent is a LinkNode, we update the URL, target and rel.
        const linkNode = $getAncestor$2(firstNode, $isLinkNode);
        if (linkNode !== null) {
          linkNode.setURL(url);
          if (target !== undefined) {
            linkNode.setTarget(target);
          }
          if (rel !== null) {
            linkNode.setRel(rel);
          }
          if (title !== undefined) {
            linkNode.setTitle(title);
          }
          return;
        }
      }
      let prevParent = null;
      let linkNode = null;
      nodes.forEach(node => {
        const parent = node.getParent();
        if (parent === linkNode || parent === null || $isElementNode$1(node) && !node.isInline()) {
          return;
        }
        if ($isLinkNode(parent)) {
          linkNode = parent;
          parent.setURL(url);
          if (target !== undefined) {
            parent.setTarget(target);
          }
          if (rel !== null) {
            linkNode.setRel(rel);
          }
          if (title !== undefined) {
            linkNode.setTitle(title);
          }
          return;
        }
        if (!parent.is(prevParent)) {
          prevParent = parent;
          linkNode = $createLinkNode(url, {
            rel,
            target,
            title
          });
          if ($isLinkNode(parent)) {
            if (node.getPreviousSibling() === null) {
              parent.insertBefore(linkNode);
            } else {
              parent.insertAfter(linkNode);
            }
          } else {
            node.insertBefore(linkNode);
          }
        }
        if ($isLinkNode(node)) {
          if (node.is(linkNode)) {
            return;
          }
          if (linkNode !== null) {
            const children = node.getChildren();
            for (let i = 0; i < children.length; i++) {
              linkNode.append(children[i]);
            }
          }
          node.remove();
          return;
        }
        if (linkNode !== null) {
          linkNode.append(node);
        }
      });
    }
  }
  /** @deprecated renamed to {@link $toggleLink} by @lexical/eslint-plugin rules-of-lexical */
  const toggleLink = $toggleLink;
  function $getAncestor$2(node, predicate) {
    let parent = node;
    while (parent !== null && parent.getParent() !== null && !predicate(parent)) {
      parent = parent.getParentOrThrow();
    }
    return predicate(parent) ? parent : null;
  }

  var modDev$5 = /*#__PURE__*/Object.freeze({
    $createAutoLinkNode: $createAutoLinkNode,
    $createLinkNode: $createLinkNode,
    $isAutoLinkNode: $isAutoLinkNode,
    $isLinkNode: $isLinkNode,
    $toggleLink: $toggleLink,
    AutoLinkNode: AutoLinkNode,
    LinkNode: LinkNode,
    TOGGLE_LINK_COMMAND: TOGGLE_LINK_COMMAND,
    toggleLink: toggleLink
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const p$4 = createCommand$1("TOGGLE_LINK_COMMAND");

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$5 = modDev$5;
  const $createAutoLinkNode$1 = mod$5.$createAutoLinkNode;
  const $createLinkNode$1 = mod$5.$createLinkNode;
  const $isAutoLinkNode$1 = mod$5.$isAutoLinkNode;
  const $isLinkNode$1 = mod$5.$isLinkNode;
  const $toggleLink$1 = mod$5.$toggleLink;
  const AutoLinkNode$1 = mod$5.AutoLinkNode;
  const LinkNode$1 = mod$5.LinkNode;
  const TOGGLE_LINK_COMMAND$1 = mod$5.TOGGLE_LINK_COMMAND;
  const toggleLink$1 = mod$5.toggleLink;

  var LexicalLink = /*#__PURE__*/Object.freeze({
    $createAutoLinkNode: $createAutoLinkNode$1,
    $createLinkNode: $createLinkNode$1,
    $isAutoLinkNode: $isAutoLinkNode$1,
    $isLinkNode: $isLinkNode$1,
    $toggleLink: $toggleLink$1,
    AutoLinkNode: AutoLinkNode$1,
    LinkNode: LinkNode$1,
    TOGGLE_LINK_COMMAND: TOGGLE_LINK_COMMAND$1,
    toggleLink: toggleLink$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const CAN_USE_DOM$4 = typeof window !== 'undefined' && typeof window.document !== 'undefined' && typeof window.document.createElement !== 'undefined';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const getDOMSelection$1 = targetWindow => CAN_USE_DOM$4 ? (targetWindow || window).getSelection() : null;
  /**
   * Returns the *currently selected* Lexical content as an HTML string, relying on the
   * logic defined in the exportDOM methods on the LexicalNode classes. Note that
   * this will not return the HTML content of the entire editor (unless all the content is included
   * in the current selection).
   *
   * @param editor - LexicalEditor instance to get HTML content from
   * @param selection - The selection to use (default is $getSelection())
   * @returns a string of HTML content
   */
  function $getHtmlContent(editor, selection = $getSelection$1()) {
    if (selection == null) {
      {
        throw Error(`Expected valid LexicalSelection`);
      }
    }

    // If we haven't selected anything
    if ($isRangeSelection$1(selection) && selection.isCollapsed() || selection.getNodes().length === 0) {
      return '';
    }
    return $generateHtmlFromNodes$1(editor, selection);
  }

  /**
   * Returns the *currently selected* Lexical content as a JSON string, relying on the
   * logic defined in the exportJSON methods on the LexicalNode classes. Note that
   * this will not return the JSON content of the entire editor (unless all the content is included
   * in the current selection).
   *
   * @param editor  - LexicalEditor instance to get the JSON content from
   * @param selection - The selection to use (default is $getSelection())
   * @returns
   */
  function $getLexicalContent(editor, selection = $getSelection$1()) {
    if (selection == null) {
      {
        throw Error(`Expected valid LexicalSelection`);
      }
    }

    // If we haven't selected anything
    if ($isRangeSelection$1(selection) && selection.isCollapsed() || selection.getNodes().length === 0) {
      return null;
    }
    return JSON.stringify($generateJSONFromSelectedNodes(editor, selection));
  }

  /**
   * Attempts to insert content of the mime-types text/plain or text/uri-list from
   * the provided DataTransfer object into the editor at the provided selection.
   * text/uri-list is only used if text/plain is not also provided.
   *
   * @param dataTransfer an object conforming to the [DataTransfer interface] (https://html.spec.whatwg.org/multipage/dnd.html#the-datatransfer-interface)
   * @param selection the selection to use as the insertion point for the content in the DataTransfer object
   */
  function $insertDataTransferForPlainText(dataTransfer, selection) {
    const text = dataTransfer.getData('text/plain') || dataTransfer.getData('text/uri-list');
    if (text != null) {
      selection.insertRawText(text);
    }
  }

  /**
   * Attempts to insert content of the mime-types application/x-lexical-editor, text/html,
   * text/plain, or text/uri-list (in descending order of priority) from the provided DataTransfer
   * object into the editor at the provided selection.
   *
   * @param dataTransfer an object conforming to the [DataTransfer interface] (https://html.spec.whatwg.org/multipage/dnd.html#the-datatransfer-interface)
   * @param selection the selection to use as the insertion point for the content in the DataTransfer object
   * @param editor the LexicalEditor the content is being inserted into.
   */
  function $insertDataTransferForRichText(dataTransfer, selection, editor) {
    const lexicalString = dataTransfer.getData('application/x-lexical-editor');
    if (lexicalString) {
      try {
        const payload = JSON.parse(lexicalString);
        if (payload.namespace === editor._config.namespace && Array.isArray(payload.nodes)) {
          const nodes = $generateNodesFromSerializedNodes(payload.nodes);
          return $insertGeneratedNodes(editor, nodes, selection);
        }
      } catch (_unused) {
        // Fail silently.
      }
    }
    const htmlString = dataTransfer.getData('text/html');
    if (htmlString) {
      try {
        const parser = new DOMParser();
        const dom = parser.parseFromString(htmlString, 'text/html');
        const nodes = $generateNodesFromDOM$1(editor, dom);
        return $insertGeneratedNodes(editor, nodes, selection);
      } catch (_unused2) {
        // Fail silently.
      }
    }

    // Multi-line plain text in rich text mode pasted as separate paragraphs
    // instead of single paragraph with linebreaks.
    // Webkit-specific: Supports read 'text/uri-list' in clipboard.
    const text = dataTransfer.getData('text/plain') || dataTransfer.getData('text/uri-list');
    if (text != null) {
      if ($isRangeSelection$1(selection)) {
        const parts = text.split(/(\r?\n|\t)/);
        if (parts[parts.length - 1] === '') {
          parts.pop();
        }
        for (let i = 0; i < parts.length; i++) {
          const currentSelection = $getSelection$1();
          if ($isRangeSelection$1(currentSelection)) {
            const part = parts[i];
            if (part === '\n' || part === '\r\n') {
              currentSelection.insertParagraph();
            } else if (part === '\t') {
              currentSelection.insertNodes([$createTabNode$1()]);
            } else {
              currentSelection.insertText(part);
            }
          }
        }
      } else {
        selection.insertRawText(text);
      }
    }
  }

  /**
   * Inserts Lexical nodes into the editor using different strategies depending on
   * some simple selection-based heuristics. If you're looking for a generic way to
   * to insert nodes into the editor at a specific selection point, you probably want
   * {@link lexical.$insertNodes}
   *
   * @param editor LexicalEditor instance to insert the nodes into.
   * @param nodes The nodes to insert.
   * @param selection The selection to insert the nodes into.
   */
  function $insertGeneratedNodes(editor, nodes, selection) {
    if (!editor.dispatchCommand(SELECTION_INSERT_CLIPBOARD_NODES_COMMAND$1, {
      nodes,
      selection
    })) {
      selection.insertNodes(nodes);
    }
    return;
  }
  function exportNodeToJSON$1(node) {
    const serializedNode = node.exportJSON();
    const nodeClass = node.constructor;
    if (serializedNode.type !== nodeClass.getType()) {
      {
        throw Error(`LexicalNode: Node ${nodeClass.name} does not implement .exportJSON().`);
      }
    }
    if ($isElementNode$1(node)) {
      const serializedChildren = serializedNode.children;
      if (!Array.isArray(serializedChildren)) {
        {
          throw Error(`LexicalNode: Node ${nodeClass.name} is an element but .exportJSON() does not have a children array.`);
        }
      }
    }
    return serializedNode;
  }
  function $appendNodesToJSON(editor, selection, currentNode, targetArray = []) {
    let shouldInclude = selection !== null ? currentNode.isSelected(selection) : true;
    const shouldExclude = $isElementNode$1(currentNode) && currentNode.excludeFromCopy('html');
    let target = currentNode;
    if (selection !== null) {
      let clone = $cloneWithProperties$1(currentNode);
      clone = $isTextNode$1(clone) && selection !== null ? $sliceSelectedTextNodeContent$1(selection, clone) : clone;
      target = clone;
    }
    const children = $isElementNode$1(target) ? target.getChildren() : [];
    const serializedNode = exportNodeToJSON$1(target);

    // TODO: TextNode calls getTextContent() (NOT node.__text) within its exportJSON method
    // which uses getLatest() to get the text from the original node with the same key.
    // This is a deeper issue with the word "clone" here, it's still a reference to the
    // same node as far as the LexicalEditor is concerned since it shares a key.
    // We need a way to create a clone of a Node in memory with its own key, but
    // until then this hack will work for the selected text extract use case.
    if ($isTextNode$1(target)) {
      const text = target.__text;
      // If an uncollapsed selection ends or starts at the end of a line of specialized,
      // TextNodes, such as code tokens, we will get a 'blank' TextNode here, i.e., one
      // with text of length 0. We don't want this, it makes a confusing mess. Reset!
      if (text.length > 0) {
        serializedNode.text = text;
      } else {
        shouldInclude = false;
      }
    }
    for (let i = 0; i < children.length; i++) {
      const childNode = children[i];
      const shouldIncludeChild = $appendNodesToJSON(editor, selection, childNode, serializedNode.children);
      if (!shouldInclude && $isElementNode$1(currentNode) && shouldIncludeChild && currentNode.extractWithChild(childNode, selection, 'clone')) {
        shouldInclude = true;
      }
    }
    if (shouldInclude && !shouldExclude) {
      targetArray.push(serializedNode);
    } else if (Array.isArray(serializedNode.children)) {
      for (let i = 0; i < serializedNode.children.length; i++) {
        const serializedChildNode = serializedNode.children[i];
        targetArray.push(serializedChildNode);
      }
    }
    return shouldInclude;
  }

  // TODO why $ function with Editor instance?
  /**
   * Gets the Lexical JSON of the nodes inside the provided Selection.
   *
   * @param editor LexicalEditor to get the JSON content from.
   * @param selection Selection to get the JSON content from.
   * @returns an object with the editor namespace and a list of serializable nodes as JavaScript objects.
   */
  function $generateJSONFromSelectedNodes(editor, selection) {
    const nodes = [];
    const root = $getRoot$1();
    const topLevelChildren = root.getChildren();
    for (let i = 0; i < topLevelChildren.length; i++) {
      const topLevelNode = topLevelChildren[i];
      $appendNodesToJSON(editor, selection, topLevelNode, nodes);
    }
    return {
      namespace: editor._config.namespace,
      nodes
    };
  }

  /**
   * This method takes an array of objects conforming to the BaseSeralizedNode interface and returns
   * an Array containing instances of the corresponding LexicalNode classes registered on the editor.
   * Normally, you'd get an Array of BaseSerialized nodes from {@link $generateJSONFromSelectedNodes}
   *
   * @param serializedNodes an Array of objects conforming to the BaseSerializedNode interface.
   * @returns an Array of Lexical Node objects.
   */
  function $generateNodesFromSerializedNodes(serializedNodes) {
    const nodes = [];
    for (let i = 0; i < serializedNodes.length; i++) {
      const serializedNode = serializedNodes[i];
      const node = $parseSerializedNode$1(serializedNode);
      if ($isTextNode$1(node)) {
        $addNodeStyle$1(node);
      }
      nodes.push(node);
    }
    return nodes;
  }
  const EVENT_LATENCY = 50;
  let clipboardEventTimeout = null;

  // TODO custom selection
  // TODO potentially have a node customizable version for plain text
  /**
   * Copies the content of the current selection to the clipboard in
   * text/plain, text/html, and application/x-lexical-editor (Lexical JSON)
   * formats.
   *
   * @param editor the LexicalEditor instance to copy content from
   * @param event the native browser ClipboardEvent to add the content to.
   * @returns
   */
  async function copyToClipboard(editor, event, data) {
    if (clipboardEventTimeout !== null) {
      // Prevent weird race conditions that can happen when this function is run multiple times
      // synchronously. In the future, we can do better, we can cancel/override the previously running job.
      return false;
    }
    if (event !== null) {
      return new Promise((resolve, reject) => {
        editor.update(() => {
          resolve($copyToClipboardEvent(editor, event, data));
        });
      });
    }
    const rootElement = editor.getRootElement();
    const windowDocument = editor._window == null ? window.document : editor._window.document;
    const domSelection = getDOMSelection$1(editor._window);
    if (rootElement === null || domSelection === null) {
      return false;
    }
    const element = windowDocument.createElement('span');
    element.style.cssText = 'position: fixed; top: -1000px;';
    element.append(windowDocument.createTextNode('#'));
    rootElement.append(element);
    const range = new Range();
    range.setStart(element, 0);
    range.setEnd(element, 1);
    domSelection.removeAllRanges();
    domSelection.addRange(range);
    return new Promise((resolve, reject) => {
      const removeListener = editor.registerCommand(COPY_COMMAND$1, secondEvent => {
        if (objectKlassEquals$1(secondEvent, ClipboardEvent)) {
          removeListener();
          if (clipboardEventTimeout !== null) {
            window.clearTimeout(clipboardEventTimeout);
            clipboardEventTimeout = null;
          }
          resolve($copyToClipboardEvent(editor, secondEvent, data));
        }
        // Block the entire copy flow while we wait for the next ClipboardEvent
        return true;
      }, COMMAND_PRIORITY_CRITICAL$1);
      // If the above hack execCommand hack works, this timeout code should never fire. Otherwise,
      // the listener will be quickly freed so that the user can reuse it again
      clipboardEventTimeout = window.setTimeout(() => {
        removeListener();
        clipboardEventTimeout = null;
        resolve(false);
      }, EVENT_LATENCY);
      windowDocument.execCommand('copy');
      element.remove();
    });
  }

  // TODO shouldn't pass editor (pass namespace directly)
  function $copyToClipboardEvent(editor, event, data) {
    if (data === undefined) {
      const domSelection = getDOMSelection$1(editor._window);
      if (!domSelection) {
        return false;
      }
      const anchorDOM = domSelection.anchorNode;
      const focusDOM = domSelection.focusNode;
      if (anchorDOM !== null && focusDOM !== null && !isSelectionWithinEditor$1(editor, anchorDOM, focusDOM)) {
        return false;
      }
      const selection = $getSelection$1();
      if (selection === null) {
        return false;
      }
      data = $getClipboardDataFromSelection(selection);
    }
    event.preventDefault();
    const clipboardData = event.clipboardData;
    if (clipboardData === null) {
      return false;
    }
    setLexicalClipboardDataTransfer(clipboardData, data);
    return true;
  }
  const clipboardDataFunctions = [['text/html', $getHtmlContent], ['application/x-lexical-editor', $getLexicalContent]];

  /**
   * Serialize the content of the current selection to strings in
   * text/plain, text/html, and application/x-lexical-editor (Lexical JSON)
   * formats (as available).
   *
   * @param selection the selection to serialize (defaults to $getSelection())
   * @returns LexicalClipboardData
   */
  function $getClipboardDataFromSelection(selection = $getSelection$1()) {
    const clipboardData = {
      'text/plain': selection ? selection.getTextContent() : ''
    };
    if (selection) {
      const editor = $getEditor$1();
      for (const [mimeType, $editorFn] of clipboardDataFunctions) {
        const v = $editorFn(editor, selection);
        if (v !== null) {
          clipboardData[mimeType] = v;
        }
      }
    }
    return clipboardData;
  }

  /**
   * Call setData on the given clipboardData for each MIME type present
   * in the given data (from {@link $getClipboardDataFromSelection})
   *
   * @param clipboardData the event.clipboardData to populate from data
   * @param data The lexical data
   */
  function setLexicalClipboardDataTransfer(clipboardData, data) {
    for (const k in data) {
      const v = data[k];
      if (v !== undefined) {
        clipboardData.setData(k, v);
      }
    }
  }

  var modDev$6 = /*#__PURE__*/Object.freeze({
    $generateJSONFromSelectedNodes: $generateJSONFromSelectedNodes,
    $generateNodesFromSerializedNodes: $generateNodesFromSerializedNodes,
    $getClipboardDataFromSelection: $getClipboardDataFromSelection,
    $getHtmlContent: $getHtmlContent,
    $getLexicalContent: $getLexicalContent,
    $insertDataTransferForPlainText: $insertDataTransferForPlainText,
    $insertDataTransferForRichText: $insertDataTransferForRichText,
    $insertGeneratedNodes: $insertGeneratedNodes,
    copyToClipboard: copyToClipboard,
    setLexicalClipboardDataTransfer: setLexicalClipboardDataTransfer
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  function w$4(t) {
    return t && t.__esModule && Object.prototype.hasOwnProperty.call(t, "default") ? t.default : t;
  }
  var y$4 = w$4(function (t) {
    const e = new URLSearchParams();
    e.append("code", t);
    for (let t = 1; t < arguments.length; t++) e.append("v", arguments[t]);
    throw Error(`Minified Lexical error #${t}; visit https://lexical.dev/docs/error?${e} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });
  const v$4 = "undefined" != typeof window && void 0 !== window.document && void 0 !== window.document.createElement;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$6 = modDev$6;
  const $generateJSONFromSelectedNodes$1 = mod$6.$generateJSONFromSelectedNodes;
  const $generateNodesFromSerializedNodes$1 = mod$6.$generateNodesFromSerializedNodes;
  const $getClipboardDataFromSelection$1 = mod$6.$getClipboardDataFromSelection;
  const $getHtmlContent$1 = mod$6.$getHtmlContent;
  const $getLexicalContent$1 = mod$6.$getLexicalContent;
  const $insertDataTransferForPlainText$1 = mod$6.$insertDataTransferForPlainText;
  const $insertDataTransferForRichText$1 = mod$6.$insertDataTransferForRichText;
  const $insertGeneratedNodes$1 = mod$6.$insertGeneratedNodes;
  const copyToClipboard$1 = mod$6.copyToClipboard;
  const setLexicalClipboardDataTransfer$1 = mod$6.setLexicalClipboardDataTransfer;

  var LexicalClipboard = /*#__PURE__*/Object.freeze({
    $generateJSONFromSelectedNodes: $generateJSONFromSelectedNodes$1,
    $generateNodesFromSerializedNodes: $generateNodesFromSerializedNodes$1,
    $getClipboardDataFromSelection: $getClipboardDataFromSelection$1,
    $getHtmlContent: $getHtmlContent$1,
    $getLexicalContent: $getLexicalContent$1,
    $insertDataTransferForPlainText: $insertDataTransferForPlainText$1,
    $insertDataTransferForRichText: $insertDataTransferForRichText$1,
    $insertGeneratedNodes: $insertGeneratedNodes$1,
    copyToClipboard: copyToClipboard$1,
    setLexicalClipboardDataTransfer: setLexicalClipboardDataTransfer$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const HISTORY_MERGE = 0;
  const HISTORY_PUSH = 1;
  const DISCARD_HISTORY_CANDIDATE = 2;
  const OTHER = 0;
  const COMPOSING_CHARACTER = 1;
  const INSERT_CHARACTER_AFTER_SELECTION = 2;
  const DELETE_CHARACTER_BEFORE_SELECTION = 3;
  const DELETE_CHARACTER_AFTER_SELECTION = 4;
  function getDirtyNodes(editorState, dirtyLeaves, dirtyElements) {
    const nodeMap = editorState._nodeMap;
    const nodes = [];
    for (const dirtyLeafKey of dirtyLeaves) {
      const dirtyLeaf = nodeMap.get(dirtyLeafKey);
      if (dirtyLeaf !== undefined) {
        nodes.push(dirtyLeaf);
      }
    }
    for (const [dirtyElementKey, intentionallyMarkedAsDirty] of dirtyElements) {
      if (!intentionallyMarkedAsDirty) {
        continue;
      }
      const dirtyElement = nodeMap.get(dirtyElementKey);
      if (dirtyElement !== undefined && !$isRootNode$1(dirtyElement)) {
        nodes.push(dirtyElement);
      }
    }
    return nodes;
  }
  function getChangeType(prevEditorState, nextEditorState, dirtyLeavesSet, dirtyElementsSet, isComposing) {
    if (prevEditorState === null || dirtyLeavesSet.size === 0 && dirtyElementsSet.size === 0 && !isComposing) {
      return OTHER;
    }
    const nextSelection = nextEditorState._selection;
    const prevSelection = prevEditorState._selection;
    if (isComposing) {
      return COMPOSING_CHARACTER;
    }
    if (!$isRangeSelection$1(nextSelection) || !$isRangeSelection$1(prevSelection) || !prevSelection.isCollapsed() || !nextSelection.isCollapsed()) {
      return OTHER;
    }
    const dirtyNodes = getDirtyNodes(nextEditorState, dirtyLeavesSet, dirtyElementsSet);
    if (dirtyNodes.length === 0) {
      return OTHER;
    }

    // Catching the case when inserting new text node into an element (e.g. first char in paragraph/list),
    // or after existing node.
    if (dirtyNodes.length > 1) {
      const nextNodeMap = nextEditorState._nodeMap;
      const nextAnchorNode = nextNodeMap.get(nextSelection.anchor.key);
      const prevAnchorNode = nextNodeMap.get(prevSelection.anchor.key);
      if (nextAnchorNode && prevAnchorNode && !prevEditorState._nodeMap.has(nextAnchorNode.__key) && $isTextNode$1(nextAnchorNode) && nextAnchorNode.__text.length === 1 && nextSelection.anchor.offset === 1) {
        return INSERT_CHARACTER_AFTER_SELECTION;
      }
      return OTHER;
    }
    const nextDirtyNode = dirtyNodes[0];
    const prevDirtyNode = prevEditorState._nodeMap.get(nextDirtyNode.__key);
    if (!$isTextNode$1(prevDirtyNode) || !$isTextNode$1(nextDirtyNode) || prevDirtyNode.__mode !== nextDirtyNode.__mode) {
      return OTHER;
    }
    const prevText = prevDirtyNode.__text;
    const nextText = nextDirtyNode.__text;
    if (prevText === nextText) {
      return OTHER;
    }
    const nextAnchor = nextSelection.anchor;
    const prevAnchor = prevSelection.anchor;
    if (nextAnchor.key !== prevAnchor.key || nextAnchor.type !== 'text') {
      return OTHER;
    }
    const nextAnchorOffset = nextAnchor.offset;
    const prevAnchorOffset = prevAnchor.offset;
    const textDiff = nextText.length - prevText.length;
    if (textDiff === 1 && prevAnchorOffset === nextAnchorOffset - 1) {
      return INSERT_CHARACTER_AFTER_SELECTION;
    }
    if (textDiff === -1 && prevAnchorOffset === nextAnchorOffset + 1) {
      return DELETE_CHARACTER_BEFORE_SELECTION;
    }
    if (textDiff === -1 && prevAnchorOffset === nextAnchorOffset) {
      return DELETE_CHARACTER_AFTER_SELECTION;
    }
    return OTHER;
  }
  function isTextNodeUnchanged(key, prevEditorState, nextEditorState) {
    const prevNode = prevEditorState._nodeMap.get(key);
    const nextNode = nextEditorState._nodeMap.get(key);
    const prevSelection = prevEditorState._selection;
    const nextSelection = nextEditorState._selection;
    const isDeletingLine = $isRangeSelection$1(prevSelection) && $isRangeSelection$1(nextSelection) && prevSelection.anchor.type === 'element' && prevSelection.focus.type === 'element' && nextSelection.anchor.type === 'text' && nextSelection.focus.type === 'text';
    if (!isDeletingLine && $isTextNode$1(prevNode) && $isTextNode$1(nextNode) && prevNode.__parent === nextNode.__parent) {
      // This has the assumption that object key order won't change if the
      // content did not change, which should normally be safe given
      // the manner in which nodes and exportJSON are typically implemented.
      return JSON.stringify(prevEditorState.read(() => prevNode.exportJSON())) === JSON.stringify(nextEditorState.read(() => nextNode.exportJSON()));
    }
    return false;
  }
  function createMergeActionGetter(editor, delay) {
    let prevChangeTime = Date.now();
    let prevChangeType = OTHER;
    return (prevEditorState, nextEditorState, currentHistoryEntry, dirtyLeaves, dirtyElements, tags) => {
      const changeTime = Date.now();

      // If applying changes from history stack there's no need
      // to run history logic again, as history entries already calculated
      if (tags.has('historic')) {
        prevChangeType = OTHER;
        prevChangeTime = changeTime;
        return DISCARD_HISTORY_CANDIDATE;
      }
      const changeType = getChangeType(prevEditorState, nextEditorState, dirtyLeaves, dirtyElements, editor.isComposing());
      const mergeAction = (() => {
        const isSameEditor = currentHistoryEntry === null || currentHistoryEntry.editor === editor;
        const shouldPushHistory = tags.has('history-push');
        const shouldMergeHistory = !shouldPushHistory && isSameEditor && tags.has('history-merge');
        if (shouldMergeHistory) {
          return HISTORY_MERGE;
        }
        if (prevEditorState === null) {
          return HISTORY_PUSH;
        }
        const selection = nextEditorState._selection;
        const hasDirtyNodes = dirtyLeaves.size > 0 || dirtyElements.size > 0;
        if (!hasDirtyNodes) {
          if (selection !== null) {
            return HISTORY_MERGE;
          }
          return DISCARD_HISTORY_CANDIDATE;
        }
        if (shouldPushHistory === false && changeType !== OTHER && changeType === prevChangeType && changeTime < prevChangeTime + delay && isSameEditor) {
          return HISTORY_MERGE;
        }

        // A single node might have been marked as dirty, but not have changed
        // due to some node transform reverting the change.
        if (dirtyLeaves.size === 1) {
          const dirtyLeafKey = Array.from(dirtyLeaves)[0];
          if (isTextNodeUnchanged(dirtyLeafKey, prevEditorState, nextEditorState)) {
            return HISTORY_MERGE;
          }
        }
        return HISTORY_PUSH;
      })();
      prevChangeTime = changeTime;
      prevChangeType = changeType;
      return mergeAction;
    };
  }
  function redo(editor, historyState) {
    const redoStack = historyState.redoStack;
    const undoStack = historyState.undoStack;
    if (redoStack.length !== 0) {
      const current = historyState.current;
      if (current !== null) {
        undoStack.push(current);
        editor.dispatchCommand(CAN_UNDO_COMMAND$1, true);
      }
      const historyStateEntry = redoStack.pop();
      if (redoStack.length === 0) {
        editor.dispatchCommand(CAN_REDO_COMMAND$1, false);
      }
      historyState.current = historyStateEntry || null;
      if (historyStateEntry) {
        historyStateEntry.editor.setEditorState(historyStateEntry.editorState, {
          tag: 'historic'
        });
      }
    }
  }
  function undo(editor, historyState) {
    const redoStack = historyState.redoStack;
    const undoStack = historyState.undoStack;
    const undoStackLength = undoStack.length;
    if (undoStackLength !== 0) {
      const current = historyState.current;
      const historyStateEntry = undoStack.pop();
      if (current !== null) {
        redoStack.push(current);
        editor.dispatchCommand(CAN_REDO_COMMAND$1, true);
      }
      if (undoStack.length === 0) {
        editor.dispatchCommand(CAN_UNDO_COMMAND$1, false);
      }
      historyState.current = historyStateEntry || null;
      if (historyStateEntry) {
        historyStateEntry.editor.setEditorState(historyStateEntry.editorState, {
          tag: 'historic'
        });
      }
    }
  }
  function clearHistory(historyState) {
    historyState.undoStack = [];
    historyState.redoStack = [];
    historyState.current = null;
  }

  /**
   * Registers necessary listeners to manage undo/redo history stack and related editor commands.
   * It returns `unregister` callback that cleans up all listeners and should be called on editor unmount.
   * @param editor - The lexical editor.
   * @param historyState - The history state, containing the current state and the undo/redo stack.
   * @param delay - The time (in milliseconds) the editor should delay generating a new history stack,
   * instead of merging the current changes with the current stack.
   * @returns The listeners cleanup callback function.
   */
  function registerHistory(editor, historyState, delay) {
    const getMergeAction = createMergeActionGetter(editor, delay);
    const applyChange = ({
      editorState,
      prevEditorState,
      dirtyLeaves,
      dirtyElements,
      tags
    }) => {
      const current = historyState.current;
      const redoStack = historyState.redoStack;
      const undoStack = historyState.undoStack;
      const currentEditorState = current === null ? null : current.editorState;
      if (current !== null && editorState === currentEditorState) {
        return;
      }
      const mergeAction = getMergeAction(prevEditorState, editorState, current, dirtyLeaves, dirtyElements, tags);
      if (mergeAction === HISTORY_PUSH) {
        if (redoStack.length !== 0) {
          historyState.redoStack = [];
          editor.dispatchCommand(CAN_REDO_COMMAND$1, false);
        }
        if (current !== null) {
          undoStack.push({
            ...current
          });
          editor.dispatchCommand(CAN_UNDO_COMMAND$1, true);
        }
      } else if (mergeAction === DISCARD_HISTORY_CANDIDATE) {
        return;
      }

      // Else we merge
      historyState.current = {
        editor,
        editorState
      };
    };
    const unregister = mergeRegister$1(editor.registerCommand(UNDO_COMMAND$1, () => {
      undo(editor, historyState);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(REDO_COMMAND$1, () => {
      redo(editor, historyState);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(CLEAR_EDITOR_COMMAND$1, () => {
      clearHistory(historyState);
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(CLEAR_HISTORY_COMMAND$1, () => {
      clearHistory(historyState);
      editor.dispatchCommand(CAN_REDO_COMMAND$1, false);
      editor.dispatchCommand(CAN_UNDO_COMMAND$1, false);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerUpdateListener(applyChange));
    return unregister;
  }

  /**
   * Creates an empty history state.
   * @returns - The empty history state, as an object.
   */
  function createEmptyHistoryState() {
    return {
      current: null,
      redoStack: [],
      undoStack: []
    };
  }

  var modDev$7 = /*#__PURE__*/Object.freeze({
    createEmptyHistoryState: createEmptyHistoryState,
    registerHistory: registerHistory
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$7 = modDev$7;
  const createEmptyHistoryState$1 = mod$7.createEmptyHistoryState;
  const registerHistory$1 = mod$7.registerHistory;

  var LexicalHistory = /*#__PURE__*/Object.freeze({
    createEmptyHistoryState: createEmptyHistoryState$1,
    registerHistory: registerHistory$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Returns the root's text content.
   * @returns The root's text content.
   */
  function $rootTextContent() {
    const root = $getRoot$1();
    return root.getTextContent();
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Determines if the root has any text content and can trim any whitespace if it does.
   * @param isEditorComposing - Is the editor in composition mode due to an active Input Method Editor?
   * @param trim - Should the root text have its whitespaced trimmed? Defaults to true.
   * @returns true if text content is empty, false if there is text or isEditorComposing is true.
   */
  function $isRootTextContentEmpty(isEditorComposing, trim = true) {
    if (isEditorComposing) {
      return false;
    }
    let text = $rootTextContent();
    if (trim) {
      text = text.trim();
    }
    return text === '';
  }

  /**
   * Returns a function that executes {@link $isRootTextContentEmpty}
   * @param isEditorComposing - Is the editor in composition mode due to an active Input Method Editor?
   * @param trim - Should the root text have its whitespaced trimmed? Defaults to true.
   * @returns A function that executes $isRootTextContentEmpty based on arguments.
   */
  function $isRootTextContentEmptyCurry(isEditorComposing, trim) {
    return () => $isRootTextContentEmpty(isEditorComposing, trim);
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Determines if the input should show the placeholder. If anything is in
   * in the root the placeholder should not be shown.
   * @param isComposing - Is the editor in composition mode due to an active Input Method Editor?
   * @returns true if the input should show the placeholder, false otherwise.
   */
  function $canShowPlaceholder(isComposing) {
    if (!$isRootTextContentEmpty(isComposing, false)) {
      return false;
    }
    const root = $getRoot$1();
    const children = root.getChildren();
    const childrenLength = children.length;
    if (childrenLength > 1) {
      return false;
    }
    for (let i = 0; i < childrenLength; i++) {
      const topBlock = children[i];
      if ($isDecoratorNode$1(topBlock)) {
        return false;
      }
      if ($isElementNode$1(topBlock)) {
        if (!$isParagraphNode$1(topBlock)) {
          return false;
        }
        if (topBlock.__indent !== 0) {
          return false;
        }
        const topBlockChildren = topBlock.getChildren();
        const topBlockChildrenLength = topBlockChildren.length;
        for (let s = 0; s < topBlockChildrenLength; s++) {
          const child = topBlockChildren[i];
          if (!$isTextNode$1(child)) {
            return false;
          }
        }
      }
    }
    return true;
  }

  /**
   * Returns a function that executes {@link $canShowPlaceholder}
   * @param isEditorComposing - Is the editor in composition mode due to an active Input Method Editor?
   * @returns A function that executes $canShowPlaceholder with arguments.
   */
  function $canShowPlaceholderCurry(isEditorComposing) {
    return () => $canShowPlaceholder(isEditorComposing);
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Finds a TextNode with a size larger than targetCharacters and returns
   * the node along with the remaining length of the text.
   * @param root - The RootNode.
   * @param targetCharacters - The number of characters whose TextNode must be larger than.
   * @returns The TextNode and the intersections offset, or null if no TextNode is found.
   */
  function $findTextIntersectionFromCharacters(root, targetCharacters) {
    let node = root.getFirstChild();
    let currentCharacters = 0;
    mainLoop: while (node !== null) {
      if ($isElementNode$1(node)) {
        const child = node.getFirstChild();
        if (child !== null) {
          node = child;
          continue;
        }
      } else if ($isTextNode$1(node)) {
        const characters = node.getTextContentSize();
        if (currentCharacters + characters > targetCharacters) {
          return {
            node,
            offset: targetCharacters - currentCharacters
          };
        }
        currentCharacters += characters;
      }
      const sibling = node.getNextSibling();
      if (sibling !== null) {
        node = sibling;
        continue;
      }
      let parent = node.getParent();
      while (parent !== null) {
        const parentSibling = parent.getNextSibling();
        if (parentSibling !== null) {
          node = parentSibling;
          continue mainLoop;
        }
        parent = parent.getParent();
      }
      break;
    }
    return null;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Returns a tuple that can be rested (...) into mergeRegister to clean up
   * node transforms listeners that transforms text into another node, eg. a HashtagNode.
   * @example
   * ```ts
   *   useEffect(() => {
      return mergeRegister(
        ...registerLexicalTextEntity(editor, getMatch, targetNode, createNode),
      );
    }, [createNode, editor, getMatch, targetNode]);
   * ```
   * Where targetNode is the type of node containing the text you want to transform (like a text input),
   * then getMatch uses a regex to find a matching text and creates the proper node to include the matching text.
   * @param editor - The lexical editor.
   * @param getMatch - Finds a matching string that satisfies a regex expression.
   * @param targetNode - The node type that contains text to match with. eg. HashtagNode
   * @param createNode - A function that creates a new node to contain the matched text. eg createHashtagNode
   * @returns An array containing the plain text and reverse node transform listeners.
   */
  function registerLexicalTextEntity(editor, getMatch, targetNode, createNode) {
    const isTargetNode = node => {
      return node instanceof targetNode;
    };
    const $replaceWithSimpleText = node => {
      const textNode = $createTextNode$1(node.getTextContent());
      textNode.setFormat(node.getFormat());
      node.replace(textNode);
    };
    const getMode = node => {
      return node.getLatest().__mode;
    };
    const $textNodeTransform = node => {
      if (!node.isSimpleText()) {
        return;
      }
      let prevSibling = node.getPreviousSibling();
      let text = node.getTextContent();
      let currentNode = node;
      let match;
      if ($isTextNode$1(prevSibling)) {
        const previousText = prevSibling.getTextContent();
        const combinedText = previousText + text;
        const prevMatch = getMatch(combinedText);
        if (isTargetNode(prevSibling)) {
          if (prevMatch === null || getMode(prevSibling) !== 0) {
            $replaceWithSimpleText(prevSibling);
            return;
          } else {
            const diff = prevMatch.end - previousText.length;
            if (diff > 0) {
              const concatText = text.slice(0, diff);
              const newTextContent = previousText + concatText;
              prevSibling.select();
              prevSibling.setTextContent(newTextContent);
              if (diff === text.length) {
                node.remove();
              } else {
                const remainingText = text.slice(diff);
                node.setTextContent(remainingText);
              }
              return;
            }
          }
        } else if (prevMatch === null || prevMatch.start < previousText.length) {
          return;
        }
      }
      let prevMatchLengthToSkip = 0;
      // eslint-disable-next-line no-constant-condition
      while (true) {
        match = getMatch(text);
        let nextText = match === null ? '' : text.slice(match.end);
        text = nextText;
        if (nextText === '') {
          const nextSibling = currentNode.getNextSibling();
          if ($isTextNode$1(nextSibling)) {
            nextText = currentNode.getTextContent() + nextSibling.getTextContent();
            const nextMatch = getMatch(nextText);
            if (nextMatch === null) {
              if (isTargetNode(nextSibling)) {
                $replaceWithSimpleText(nextSibling);
              } else {
                nextSibling.markDirty();
              }
              return;
            } else if (nextMatch.start !== 0) {
              return;
            }
          }
        }
        if (match === null) {
          return;
        }
        if (match.start === 0 && $isTextNode$1(prevSibling) && prevSibling.isTextEntity()) {
          prevMatchLengthToSkip += match.end;
          continue;
        }
        let nodeToReplace;
        if (match.start === 0) {
          [nodeToReplace, currentNode] = currentNode.splitText(match.end);
        } else {
          [, nodeToReplace, currentNode] = currentNode.splitText(match.start + prevMatchLengthToSkip, match.end + prevMatchLengthToSkip);
        }
        if (!(nodeToReplace !== undefined)) {
          throw Error(`${'nodeToReplace'} should not be undefined. You may want to check splitOffsets passed to the splitText.`);
        }
        const replacementNode = createNode(nodeToReplace);
        replacementNode.setFormat(nodeToReplace.getFormat());
        nodeToReplace.replace(replacementNode);
        if (currentNode == null) {
          return;
        }
        prevMatchLengthToSkip = 0;
        prevSibling = replacementNode;
      }
    };
    const $reverseNodeTransform = node => {
      const text = node.getTextContent();
      const match = getMatch(text);
      if (match === null || match.start !== 0) {
        $replaceWithSimpleText(node);
        return;
      }
      if (text.length > match.end) {
        // This will split out the rest of the text as simple text
        node.splitText(match.end);
        return;
      }
      const prevSibling = node.getPreviousSibling();
      if ($isTextNode$1(prevSibling) && prevSibling.isTextEntity()) {
        $replaceWithSimpleText(prevSibling);
        $replaceWithSimpleText(node);
      }
      const nextSibling = node.getNextSibling();
      if ($isTextNode$1(nextSibling) && nextSibling.isTextEntity()) {
        $replaceWithSimpleText(nextSibling);

        // This may have already been converted in the previous block
        if (isTargetNode(node)) {
          $replaceWithSimpleText(node);
        }
      }
    };
    const removePlainTextTransform = editor.registerNodeTransform(TextNode$1, $textNodeTransform);
    const removeReverseNodeTransform = editor.registerNodeTransform(targetNode, $reverseNodeTransform);
    return [removePlainTextTransform, removeReverseNodeTransform];
  }

  var modDev$8 = /*#__PURE__*/Object.freeze({
    $canShowPlaceholder: $canShowPlaceholder,
    $canShowPlaceholderCurry: $canShowPlaceholderCurry,
    $findTextIntersectionFromCharacters: $findTextIntersectionFromCharacters,
    $isRootTextContentEmpty: $isRootTextContentEmpty,
    $isRootTextContentEmptyCurry: $isRootTextContentEmptyCurry,
    $rootTextContent: $rootTextContent,
    registerLexicalTextEntity: registerLexicalTextEntity
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  function d$2(t) {
    return t && t.__esModule && Object.prototype.hasOwnProperty.call(t, "default") ? t.default : t;
  }
  var x$6 = d$2(function (t) {
    const e = new URLSearchParams();
    e.append("code", t);
    for (let t = 1; t < arguments.length; t++) e.append("v", arguments[t]);
    throw Error(`Minified Lexical error #${t}; visit https://lexical.dev/docs/error?${e} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$8 = modDev$8;
  const $canShowPlaceholder$1 = mod$8.$canShowPlaceholder;
  const $canShowPlaceholderCurry$1 = mod$8.$canShowPlaceholderCurry;
  const $findTextIntersectionFromCharacters$1 = mod$8.$findTextIntersectionFromCharacters;
  const $isRootTextContentEmpty$1 = mod$8.$isRootTextContentEmpty;
  const $isRootTextContentEmptyCurry$1 = mod$8.$isRootTextContentEmptyCurry;
  const $rootTextContent$1 = mod$8.$rootTextContent;
  const registerLexicalTextEntity$1 = mod$8.registerLexicalTextEntity;

  var LexicalText = /*#__PURE__*/Object.freeze({
    $canShowPlaceholder: $canShowPlaceholder$1,
    $canShowPlaceholderCurry: $canShowPlaceholderCurry$1,
    $findTextIntersectionFromCharacters: $findTextIntersectionFromCharacters$1,
    $isRootTextContentEmpty: $isRootTextContentEmpty$1,
    $isRootTextContentEmptyCurry: $isRootTextContentEmptyCurry$1,
    $rootTextContent: $rootTextContent$1,
    registerLexicalTextEntity: registerLexicalTextEntity$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function caretFromPoint(x, y) {
    if (typeof document.caretRangeFromPoint !== 'undefined') {
      const range = document.caretRangeFromPoint(x, y);
      if (range === null) {
        return null;
      }
      return {
        node: range.startContainer,
        offset: range.startOffset
      };
      // @ts-ignore
    } else if (document.caretPositionFromPoint !== 'undefined') {
      // @ts-ignore FF - no types
      const range = document.caretPositionFromPoint(x, y);
      if (range === null) {
        return null;
      }
      return {
        node: range.offsetNode,
        offset: range.offset
      };
    } else {
      // Gracefully handle IE
      return null;
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const CAN_USE_DOM$5 = typeof window !== 'undefined' && typeof window.document !== 'undefined' && typeof window.document.createElement !== 'undefined';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const documentMode$2 = CAN_USE_DOM$5 && 'documentMode' in document ? document.documentMode : null;
  const CAN_USE_BEFORE_INPUT$4 = CAN_USE_DOM$5 && 'InputEvent' in window && !documentMode$2 ? 'getTargetRanges' in new window.InputEvent('input') : false;
  const IS_SAFARI$4 = CAN_USE_DOM$5 && /Version\/[\d.]+.*Safari/.test(navigator.userAgent);
  const IS_IOS$4 = CAN_USE_DOM$5 && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

  // Keep these in case we need to use them in the future.
  // export const IS_WINDOWS: boolean = CAN_USE_DOM && /Win/.test(navigator.platform);
  const IS_CHROME$4 = CAN_USE_DOM$5 && /^(?=.*Chrome).*/i.test(navigator.userAgent);
  const IS_APPLE_WEBKIT$4 = CAN_USE_DOM$5 && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !IS_CHROME$4;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const DRAG_DROP_PASTE = createCommand$1('DRAG_DROP_PASTE_FILE');
  /** @noInheritDoc */
  class QuoteNode extends ElementNode$1 {
    static getType() {
      return 'quote';
    }
    static clone(node) {
      return new QuoteNode(node.__key);
    }
    constructor(key) {
      super(key);
    }

    // View

    createDOM(config) {
      const element = document.createElement('blockquote');
      addClassNamesToElement$1(element, config.theme.quote);
      return element;
    }
    updateDOM(prevNode, dom) {
      return false;
    }
    static importDOM() {
      return {
        blockquote: node => ({
          conversion: $convertBlockquoteElement,
          priority: 0
        })
      };
    }
    exportDOM(editor) {
      const {
        element
      } = super.exportDOM(editor);
      if (element && isHTMLElement$2(element)) {
        if (this.isEmpty()) {
          element.append(document.createElement('br'));
        }
        const formatType = this.getFormatType();
        element.style.textAlign = formatType;
        const direction = this.getDirection();
        if (direction) {
          element.dir = direction;
        }
      }
      return {
        element
      };
    }
    static importJSON(serializedNode) {
      const node = $createQuoteNode();
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        type: 'quote'
      };
    }

    // Mutation

    insertNewAfter(_, restoreSelection) {
      const newBlock = $createParagraphNode$1();
      const direction = this.getDirection();
      newBlock.setDirection(direction);
      this.insertAfter(newBlock, restoreSelection);
      return newBlock;
    }
    collapseAtStart() {
      const paragraph = $createParagraphNode$1();
      const children = this.getChildren();
      children.forEach(child => paragraph.append(child));
      this.replace(paragraph);
      return true;
    }
    canMergeWhenEmpty() {
      return true;
    }
  }
  function $createQuoteNode() {
    return $applyNodeReplacement$1(new QuoteNode());
  }
  function $isQuoteNode(node) {
    return node instanceof QuoteNode;
  }
  /** @noInheritDoc */
  class HeadingNode extends ElementNode$1 {
    /** @internal */

    static getType() {
      return 'heading';
    }
    static clone(node) {
      return new HeadingNode(node.__tag, node.__key);
    }
    constructor(tag, key) {
      super(key);
      this.__tag = tag;
    }
    getTag() {
      return this.__tag;
    }

    // View

    createDOM(config) {
      const tag = this.__tag;
      const element = document.createElement(tag);
      const theme = config.theme;
      const classNames = theme.heading;
      if (classNames !== undefined) {
        const className = classNames[tag];
        addClassNamesToElement$1(element, className);
      }
      return element;
    }
    updateDOM(prevNode, dom) {
      return false;
    }
    static importDOM() {
      return {
        h1: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        h2: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        h3: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        h4: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        h5: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        h6: node => ({
          conversion: $convertHeadingElement,
          priority: 0
        }),
        p: node => {
          // domNode is a <p> since we matched it by nodeName
          const paragraph = node;
          const firstChild = paragraph.firstChild;
          if (firstChild !== null && isGoogleDocsTitle(firstChild)) {
            return {
              conversion: () => ({
                node: null
              }),
              priority: 3
            };
          }
          return null;
        },
        span: node => {
          if (isGoogleDocsTitle(node)) {
            return {
              conversion: domNode => {
                return {
                  node: $createHeadingNode('h1')
                };
              },
              priority: 3
            };
          }
          return null;
        }
      };
    }
    exportDOM(editor) {
      const {
        element
      } = super.exportDOM(editor);
      if (element && isHTMLElement$2(element)) {
        if (this.isEmpty()) {
          element.append(document.createElement('br'));
        }
        const formatType = this.getFormatType();
        element.style.textAlign = formatType;
        const direction = this.getDirection();
        if (direction) {
          element.dir = direction;
        }
      }
      return {
        element
      };
    }
    static importJSON(serializedNode) {
      const node = $createHeadingNode(serializedNode.tag);
      node.setFormat(serializedNode.format);
      node.setIndent(serializedNode.indent);
      node.setDirection(serializedNode.direction);
      return node;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        tag: this.getTag(),
        type: 'heading',
        version: 1
      };
    }

    // Mutation
    insertNewAfter(selection, restoreSelection = true) {
      const anchorOffet = selection ? selection.anchor.offset : 0;
      const lastDesc = this.getLastDescendant();
      const isAtEnd = !lastDesc || selection && selection.anchor.key === lastDesc.getKey() && anchorOffet === lastDesc.getTextContentSize();
      const newElement = isAtEnd || !selection ? $createParagraphNode$1() : $createHeadingNode(this.getTag());
      const direction = this.getDirection();
      newElement.setDirection(direction);
      this.insertAfter(newElement, restoreSelection);
      if (anchorOffet === 0 && !this.isEmpty() && selection) {
        const paragraph = $createParagraphNode$1();
        paragraph.select();
        this.replace(paragraph, true);
      }
      return newElement;
    }
    collapseAtStart() {
      const newElement = !this.isEmpty() ? $createHeadingNode(this.getTag()) : $createParagraphNode$1();
      const children = this.getChildren();
      children.forEach(child => newElement.append(child));
      this.replace(newElement);
      return true;
    }
    extractWithChild() {
      return true;
    }
  }
  function isGoogleDocsTitle(domNode) {
    if (domNode.nodeName.toLowerCase() === 'span') {
      return domNode.style.fontSize === '26pt';
    }
    return false;
  }
  function $convertHeadingElement(element) {
    const nodeName = element.nodeName.toLowerCase();
    let node = null;
    if (nodeName === 'h1' || nodeName === 'h2' || nodeName === 'h3' || nodeName === 'h4' || nodeName === 'h5' || nodeName === 'h6') {
      node = $createHeadingNode(nodeName);
      if (element.style !== null) {
        node.setFormat(element.style.textAlign);
      }
    }
    return {
      node
    };
  }
  function $convertBlockquoteElement(element) {
    const node = $createQuoteNode();
    if (element.style !== null) {
      node.setFormat(element.style.textAlign);
    }
    return {
      node
    };
  }
  function $createHeadingNode(headingTag) {
    return $applyNodeReplacement$1(new HeadingNode(headingTag));
  }
  function $isHeadingNode(node) {
    return node instanceof HeadingNode;
  }
  function onPasteForRichText(event, editor) {
    event.preventDefault();
    editor.update(() => {
      const selection = $getSelection$1();
      const clipboardData = objectKlassEquals$1(event, InputEvent) || objectKlassEquals$1(event, KeyboardEvent) ? null : event.clipboardData;
      if (clipboardData != null && selection !== null) {
        $insertDataTransferForRichText$1(clipboardData, selection, editor);
      }
    }, {
      tag: 'paste'
    });
  }
  async function onCutForRichText(event, editor) {
    await copyToClipboard$1(editor, objectKlassEquals$1(event, ClipboardEvent) ? event : null);
    editor.update(() => {
      const selection = $getSelection$1();
      if ($isRangeSelection$1(selection)) {
        selection.removeText();
      } else if ($isNodeSelection$1(selection)) {
        selection.getNodes().forEach(node => node.remove());
      }
    });
  }

  // Clipboard may contain files that we aren't allowed to read. While the event is arguably useless,
  // in certain occasions, we want to know whether it was a file transfer, as opposed to text. We
  // control this with the first boolean flag.
  function eventFiles(event) {
    let dataTransfer = null;
    if (objectKlassEquals$1(event, DragEvent)) {
      dataTransfer = event.dataTransfer;
    } else if (objectKlassEquals$1(event, ClipboardEvent)) {
      dataTransfer = event.clipboardData;
    }
    if (dataTransfer === null) {
      return [false, [], false];
    }
    const types = dataTransfer.types;
    const hasFiles = types.includes('Files');
    const hasContent = types.includes('text/html') || types.includes('text/plain');
    return [hasFiles, Array.from(dataTransfer.files), hasContent];
  }
  function $handleIndentAndOutdent(indentOrOutdent) {
    const selection = $getSelection$1();
    if (!$isRangeSelection$1(selection)) {
      return false;
    }
    const alreadyHandled = new Set();
    const nodes = selection.getNodes();
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      const key = node.getKey();
      if (alreadyHandled.has(key)) {
        continue;
      }
      const parentBlock = $findMatchingParent$1(node, parentNode => $isElementNode$1(parentNode) && !parentNode.isInline());
      if (parentBlock === null) {
        continue;
      }
      const parentKey = parentBlock.getKey();
      if (parentBlock.canIndent() && !alreadyHandled.has(parentKey)) {
        alreadyHandled.add(parentKey);
        indentOrOutdent(parentBlock);
      }
    }
    return alreadyHandled.size > 0;
  }
  function $isTargetWithinDecorator(target) {
    const node = $getNearestNodeFromDOMNode$1(target);
    return $isDecoratorNode$1(node);
  }
  function $isSelectionAtEndOfRoot(selection) {
    const focus = selection.focus;
    return focus.key === 'root' && focus.offset === $getRoot$1().getChildrenSize();
  }
  function registerRichText(editor) {
    const removeListener = mergeRegister$1(editor.registerCommand(CLICK_COMMAND$1, payload => {
      const selection = $getSelection$1();
      if ($isNodeSelection$1(selection)) {
        selection.clear();
        return true;
      }
      return false;
    }, 0), editor.registerCommand(DELETE_CHARACTER_COMMAND$1, isBackward => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.deleteCharacter(isBackward);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(DELETE_WORD_COMMAND$1, isBackward => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.deleteWord(isBackward);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(DELETE_LINE_COMMAND$1, isBackward => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.deleteLine(isBackward);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(CONTROLLED_TEXT_INSERTION_COMMAND$1, eventOrText => {
      const selection = $getSelection$1();
      if (typeof eventOrText === 'string') {
        if (selection !== null) {
          selection.insertText(eventOrText);
        }
      } else {
        if (selection === null) {
          return false;
        }
        const dataTransfer = eventOrText.dataTransfer;
        if (dataTransfer != null) {
          $insertDataTransferForRichText$1(dataTransfer, selection, editor);
        } else if ($isRangeSelection$1(selection)) {
          const data = eventOrText.data;
          if (data) {
            selection.insertText(data);
          }
          return true;
        }
      }
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(REMOVE_TEXT_COMMAND$1, () => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.removeText();
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(FORMAT_TEXT_COMMAND$1, format => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.formatText(format);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(FORMAT_ELEMENT_COMMAND$1, format => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection) && !$isNodeSelection$1(selection)) {
        return false;
      }
      const nodes = selection.getNodes();
      for (const node of nodes) {
        const element = $findMatchingParent$1(node, parentNode => $isElementNode$1(parentNode) && !parentNode.isInline());
        if (element !== null) {
          element.setFormat(format);
        }
      }
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(INSERT_LINE_BREAK_COMMAND$1, selectStart => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.insertLineBreak(selectStart);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(INSERT_PARAGRAPH_COMMAND$1, () => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      selection.insertParagraph();
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(INSERT_TAB_COMMAND$1, () => {
      $insertNodes$1([$createTabNode$1()]);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(INDENT_CONTENT_COMMAND$1, () => {
      return $handleIndentAndOutdent(block => {
        const indent = block.getIndent();
        block.setIndent(indent + 1);
      });
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(OUTDENT_CONTENT_COMMAND$1, () => {
      return $handleIndentAndOutdent(block => {
        const indent = block.getIndent();
        if (indent > 0) {
          block.setIndent(indent - 1);
        }
      });
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ARROW_UP_COMMAND$1, event => {
      const selection = $getSelection$1();
      if ($isNodeSelection$1(selection) && !$isTargetWithinDecorator(event.target)) {
        // If selection is on a node, let's try and move selection
        // back to being a range selection.
        const nodes = selection.getNodes();
        if (nodes.length > 0) {
          nodes[0].selectPrevious();
          return true;
        }
      } else if ($isRangeSelection$1(selection)) {
        const possibleNode = $getAdjacentNode$1(selection.focus, true);
        if (!event.shiftKey && $isDecoratorNode$1(possibleNode) && !possibleNode.isIsolated() && !possibleNode.isInline()) {
          possibleNode.selectPrevious();
          event.preventDefault();
          return true;
        }
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ARROW_DOWN_COMMAND$1, event => {
      const selection = $getSelection$1();
      if ($isNodeSelection$1(selection)) {
        // If selection is on a node, let's try and move selection
        // back to being a range selection.
        const nodes = selection.getNodes();
        if (nodes.length > 0) {
          nodes[0].selectNext(0, 0);
          return true;
        }
      } else if ($isRangeSelection$1(selection)) {
        if ($isSelectionAtEndOfRoot(selection)) {
          event.preventDefault();
          return true;
        }
        const possibleNode = $getAdjacentNode$1(selection.focus, false);
        if (!event.shiftKey && $isDecoratorNode$1(possibleNode) && !possibleNode.isIsolated() && !possibleNode.isInline()) {
          possibleNode.selectNext();
          event.preventDefault();
          return true;
        }
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ARROW_LEFT_COMMAND$1, event => {
      const selection = $getSelection$1();
      if ($isNodeSelection$1(selection)) {
        // If selection is on a node, let's try and move selection
        // back to being a range selection.
        const nodes = selection.getNodes();
        if (nodes.length > 0) {
          event.preventDefault();
          nodes[0].selectPrevious();
          return true;
        }
      }
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      if ($shouldOverrideDefaultCharacterSelection$1(selection, true)) {
        const isHoldingShift = event.shiftKey;
        event.preventDefault();
        $moveCharacter$1(selection, isHoldingShift, true);
        return true;
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ARROW_RIGHT_COMMAND$1, event => {
      const selection = $getSelection$1();
      if ($isNodeSelection$1(selection) && !$isTargetWithinDecorator(event.target)) {
        // If selection is on a node, let's try and move selection
        // back to being a range selection.
        const nodes = selection.getNodes();
        if (nodes.length > 0) {
          event.preventDefault();
          nodes[0].selectNext(0, 0);
          return true;
        }
      }
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      const isHoldingShift = event.shiftKey;
      if ($shouldOverrideDefaultCharacterSelection$1(selection, false)) {
        event.preventDefault();
        $moveCharacter$1(selection, isHoldingShift, false);
        return true;
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_BACKSPACE_COMMAND$1, event => {
      if ($isTargetWithinDecorator(event.target)) {
        return false;
      }
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      event.preventDefault();
      const {
        anchor
      } = selection;
      const anchorNode = anchor.getNode();
      if (selection.isCollapsed() && anchor.offset === 0 && !$isRootNode$1(anchorNode)) {
        const element = $getNearestBlockElementAncestorOrThrow$1(anchorNode);
        if (element.getIndent() > 0) {
          return editor.dispatchCommand(OUTDENT_CONTENT_COMMAND$1, undefined);
        }
      }
      return editor.dispatchCommand(DELETE_CHARACTER_COMMAND$1, true);
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_DELETE_COMMAND$1, event => {
      if ($isTargetWithinDecorator(event.target)) {
        return false;
      }
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      event.preventDefault();
      return editor.dispatchCommand(DELETE_CHARACTER_COMMAND$1, false);
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ENTER_COMMAND$1, event => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      if (event !== null) {
        // If we have beforeinput, then we can avoid blocking
        // the default behavior. This ensures that the iOS can
        // intercept that we're actually inserting a paragraph,
        // and autocomplete, autocapitalize etc work as intended.
        // This can also cause a strange performance issue in
        // Safari, where there is a noticeable pause due to
        // preventing the key down of enter.
        if ((IS_IOS$4 || IS_SAFARI$4 || IS_APPLE_WEBKIT$4) && CAN_USE_BEFORE_INPUT$4) {
          return false;
        }
        event.preventDefault();
        if (event.shiftKey) {
          return editor.dispatchCommand(INSERT_LINE_BREAK_COMMAND$1, false);
        }
      }
      return editor.dispatchCommand(INSERT_PARAGRAPH_COMMAND$1, undefined);
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(KEY_ESCAPE_COMMAND$1, () => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection)) {
        return false;
      }
      editor.blur();
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(DROP_COMMAND$1, event => {
      const [, files] = eventFiles(event);
      if (files.length > 0) {
        const x = event.clientX;
        const y = event.clientY;
        const eventRange = caretFromPoint(x, y);
        if (eventRange !== null) {
          const {
            offset: domOffset,
            node: domNode
          } = eventRange;
          const node = $getNearestNodeFromDOMNode$1(domNode);
          if (node !== null) {
            const selection = $createRangeSelection$1();
            if ($isTextNode$1(node)) {
              selection.anchor.set(node.getKey(), domOffset, 'text');
              selection.focus.set(node.getKey(), domOffset, 'text');
            } else {
              const parentKey = node.getParentOrThrow().getKey();
              const offset = node.getIndexWithinParent() + 1;
              selection.anchor.set(parentKey, offset, 'element');
              selection.focus.set(parentKey, offset, 'element');
            }
            const normalizedSelection = $normalizeSelection__EXPERIMENTAL(selection);
            $setSelection$1(normalizedSelection);
          }
          editor.dispatchCommand(DRAG_DROP_PASTE, files);
        }
        event.preventDefault();
        return true;
      }
      const selection = $getSelection$1();
      if ($isRangeSelection$1(selection)) {
        return true;
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(DRAGSTART_COMMAND$1, event => {
      const [isFileTransfer] = eventFiles(event);
      const selection = $getSelection$1();
      if (isFileTransfer && !$isRangeSelection$1(selection)) {
        return false;
      }
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(DRAGOVER_COMMAND$1, event => {
      const [isFileTransfer] = eventFiles(event);
      const selection = $getSelection$1();
      if (isFileTransfer && !$isRangeSelection$1(selection)) {
        return false;
      }
      const x = event.clientX;
      const y = event.clientY;
      const eventRange = caretFromPoint(x, y);
      if (eventRange !== null) {
        const node = $getNearestNodeFromDOMNode$1(eventRange.node);
        if ($isDecoratorNode$1(node)) {
          // Show browser caret as the user is dragging the media across the screen. Won't work
          // for DecoratorNode nor it's relevant.
          event.preventDefault();
        }
      }
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(SELECT_ALL_COMMAND$1, () => {
      $selectAll$1();
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(COPY_COMMAND$1, event => {
      copyToClipboard$1(editor, objectKlassEquals$1(event, ClipboardEvent) ? event : null);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(CUT_COMMAND$1, event => {
      onCutForRichText(event, editor);
      return true;
    }, COMMAND_PRIORITY_EDITOR$1), editor.registerCommand(PASTE_COMMAND$1, event => {
      const [, files, hasTextContent] = eventFiles(event);
      if (files.length > 0 && !hasTextContent) {
        editor.dispatchCommand(DRAG_DROP_PASTE, files);
        return true;
      }

      // if inputs then paste within the input ignore creating a new node on paste event
      if (isSelectionCapturedInDecoratorInput$1(event.target)) {
        return false;
      }
      const selection = $getSelection$1();
      if (selection !== null) {
        onPasteForRichText(event, editor);
        return true;
      }
      return false;
    }, COMMAND_PRIORITY_EDITOR$1));
    return removeListener;
  }

  var modDev$9 = /*#__PURE__*/Object.freeze({
    $createHeadingNode: $createHeadingNode,
    $createQuoteNode: $createQuoteNode,
    $isHeadingNode: $isHeadingNode,
    $isQuoteNode: $isQuoteNode,
    DRAG_DROP_PASTE: DRAG_DROP_PASTE,
    HeadingNode: HeadingNode,
    QuoteNode: QuoteNode,
    eventFiles: eventFiles,
    registerRichText: registerRichText
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const ct$1 = "undefined" != typeof window && void 0 !== window.document && void 0 !== window.document.createElement,
    at$1 = ct$1 && "documentMode" in document ? document.documentMode : null,
    ut$1 = !(!ct$1 || !("InputEvent" in window) || at$1) && "getTargetRanges" in new window.InputEvent("input"),
    lt$1 = ct$1 && /Version\/[\d.]+.*Safari/.test(navigator.userAgent),
    dt$1 = ct$1 && /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
    mt$1 = ct$1 && /^(?=.*Chrome).*/i.test(navigator.userAgent),
    ft$1 = ct$1 && /AppleWebKit\/[\d.]+/.test(navigator.userAgent) && !mt$1,
    gt$1 = createCommand$1("DRAG_DROP_PASTE_FILE");

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$9 = modDev$9;
  const $createHeadingNode$1 = mod$9.$createHeadingNode;
  const $createQuoteNode$1 = mod$9.$createQuoteNode;
  const $isHeadingNode$1 = mod$9.$isHeadingNode;
  const $isQuoteNode$1 = mod$9.$isQuoteNode;
  const DRAG_DROP_PASTE$1 = mod$9.DRAG_DROP_PASTE;
  const HeadingNode$1 = mod$9.HeadingNode;
  const QuoteNode$1 = mod$9.QuoteNode;
  const eventFiles$1 = mod$9.eventFiles;
  const registerRichText$1 = mod$9.registerRichText;

  var LexicalRichText = /*#__PURE__*/Object.freeze({
    $createHeadingNode: $createHeadingNode$1,
    $createQuoteNode: $createQuoteNode$1,
    $isHeadingNode: $isHeadingNode$1,
    $isQuoteNode: $isQuoteNode$1,
    DRAG_DROP_PASTE: DRAG_DROP_PASTE$1,
    HeadingNode: HeadingNode$1,
    QuoteNode: QuoteNode$1,
    eventFiles: eventFiles$1,
    registerRichText: registerRichText$1
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const PIXEL_VALUE_REG_EXP = /^(\d+(?:\.\d+)?)px$/;

  // .PlaygroundEditorTheme__tableCell width value from
  // packages/lexical-playground/src/themes/PlaygroundEditorTheme.css
  const COLUMN_WIDTH = 75;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const TableCellHeaderStates = {
    BOTH: 3,
    COLUMN: 2,
    NO_STATUS: 0,
    ROW: 1
  };
  /** @noInheritDoc */
  class TableCellNode extends ElementNode$1 {
    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    /** @internal */

    static getType() {
      return 'tablecell';
    }
    static clone(node) {
      const cellNode = new TableCellNode(node.__headerState, node.__colSpan, node.__width, node.__key);
      cellNode.__rowSpan = node.__rowSpan;
      cellNode.__backgroundColor = node.__backgroundColor;
      return cellNode;
    }
    static importDOM() {
      return {
        td: node => ({
          conversion: $convertTableCellNodeElement,
          priority: 0
        }),
        th: node => ({
          conversion: $convertTableCellNodeElement,
          priority: 0
        })
      };
    }
    static importJSON(serializedNode) {
      const colSpan = serializedNode.colSpan || 1;
      const rowSpan = serializedNode.rowSpan || 1;
      const cellNode = $createTableCellNode(serializedNode.headerState, colSpan, serializedNode.width || undefined);
      cellNode.__rowSpan = rowSpan;
      cellNode.__backgroundColor = serializedNode.backgroundColor || null;
      return cellNode;
    }
    constructor(headerState = TableCellHeaderStates.NO_STATUS, colSpan = 1, width, key) {
      super(key);
      this.__colSpan = colSpan;
      this.__rowSpan = 1;
      this.__headerState = headerState;
      this.__width = width;
      this.__backgroundColor = null;
    }
    createDOM(config) {
      const element = document.createElement(this.getTag());
      if (this.__width) {
        element.style.width = `${this.__width}px`;
      }
      if (this.__colSpan > 1) {
        element.colSpan = this.__colSpan;
      }
      if (this.__rowSpan > 1) {
        element.rowSpan = this.__rowSpan;
      }
      if (this.__backgroundColor !== null) {
        element.style.backgroundColor = this.__backgroundColor;
      }
      addClassNamesToElement$1(element, config.theme.tableCell, this.hasHeader() && config.theme.tableCellHeader);
      return element;
    }
    exportDOM(editor) {
      const {
        element
      } = super.exportDOM(editor);
      if (element) {
        const element_ = element;
        element_.style.border = '1px solid black';
        if (this.__colSpan > 1) {
          element_.colSpan = this.__colSpan;
        }
        if (this.__rowSpan > 1) {
          element_.rowSpan = this.__rowSpan;
        }
        element_.style.width = `${this.getWidth() || COLUMN_WIDTH}px`;
        element_.style.verticalAlign = 'top';
        element_.style.textAlign = 'start';
        const backgroundColor = this.getBackgroundColor();
        if (backgroundColor !== null) {
          element_.style.backgroundColor = backgroundColor;
        } else if (this.hasHeader()) {
          element_.style.backgroundColor = '#f2f3f5';
        }
      }
      return {
        element
      };
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        backgroundColor: this.getBackgroundColor(),
        colSpan: this.__colSpan,
        headerState: this.__headerState,
        rowSpan: this.__rowSpan,
        type: 'tablecell',
        width: this.getWidth()
      };
    }
    getColSpan() {
      return this.__colSpan;
    }
    setColSpan(colSpan) {
      this.getWritable().__colSpan = colSpan;
      return this;
    }
    getRowSpan() {
      return this.__rowSpan;
    }
    setRowSpan(rowSpan) {
      this.getWritable().__rowSpan = rowSpan;
      return this;
    }
    getTag() {
      return this.hasHeader() ? 'th' : 'td';
    }
    setHeaderStyles(headerState) {
      const self = this.getWritable();
      self.__headerState = headerState;
      return this.__headerState;
    }
    getHeaderStyles() {
      return this.getLatest().__headerState;
    }
    setWidth(width) {
      const self = this.getWritable();
      self.__width = width;
      return this.__width;
    }
    getWidth() {
      return this.getLatest().__width;
    }
    getBackgroundColor() {
      return this.getLatest().__backgroundColor;
    }
    setBackgroundColor(newBackgroundColor) {
      this.getWritable().__backgroundColor = newBackgroundColor;
    }
    toggleHeaderStyle(headerStateToToggle) {
      const self = this.getWritable();
      if ((self.__headerState & headerStateToToggle) === headerStateToToggle) {
        self.__headerState -= headerStateToToggle;
      } else {
        self.__headerState += headerStateToToggle;
      }
      return self;
    }
    hasHeaderState(headerState) {
      return (this.getHeaderStyles() & headerState) === headerState;
    }
    hasHeader() {
      return this.getLatest().__headerState !== TableCellHeaderStates.NO_STATUS;
    }
    updateDOM(prevNode) {
      return prevNode.__headerState !== this.__headerState || prevNode.__width !== this.__width || prevNode.__colSpan !== this.__colSpan || prevNode.__rowSpan !== this.__rowSpan || prevNode.__backgroundColor !== this.__backgroundColor;
    }
    isShadowRoot() {
      return true;
    }
    collapseAtStart() {
      return true;
    }
    canBeEmpty() {
      return false;
    }
    canIndent() {
      return false;
    }
  }
  function $convertTableCellNodeElement(domNode) {
    const domNode_ = domNode;
    const nodeName = domNode.nodeName.toLowerCase();
    let width = undefined;
    if (PIXEL_VALUE_REG_EXP.test(domNode_.style.width)) {
      width = parseFloat(domNode_.style.width);
    }
    const tableCellNode = $createTableCellNode(nodeName === 'th' ? TableCellHeaderStates.ROW : TableCellHeaderStates.NO_STATUS, domNode_.colSpan, width);
    tableCellNode.__rowSpan = domNode_.rowSpan;
    const backgroundColor = domNode_.style.backgroundColor;
    if (backgroundColor !== '') {
      tableCellNode.__backgroundColor = backgroundColor;
    }
    const style = domNode_.style;
    const textDecoration = style.textDecoration.split(' ');
    const hasBoldFontWeight = style.fontWeight === '700' || style.fontWeight === 'bold';
    const hasLinethroughTextDecoration = textDecoration.includes('line-through');
    const hasItalicFontStyle = style.fontStyle === 'italic';
    const hasUnderlineTextDecoration = textDecoration.includes('underline');
    return {
      after: childLexicalNodes => {
        if (childLexicalNodes.length === 0) {
          childLexicalNodes.push($createParagraphNode$1());
        }
        return childLexicalNodes;
      },
      forChild: (lexicalNode, parentLexicalNode) => {
        if ($isTableCellNode(parentLexicalNode) && !$isElementNode$1(lexicalNode)) {
          const paragraphNode = $createParagraphNode$1();
          if ($isLineBreakNode$1(lexicalNode) && lexicalNode.getTextContent() === '\n') {
            return null;
          }
          if ($isTextNode$1(lexicalNode)) {
            if (hasBoldFontWeight) {
              lexicalNode.toggleFormat('bold');
            }
            if (hasLinethroughTextDecoration) {
              lexicalNode.toggleFormat('strikethrough');
            }
            if (hasItalicFontStyle) {
              lexicalNode.toggleFormat('italic');
            }
            if (hasUnderlineTextDecoration) {
              lexicalNode.toggleFormat('underline');
            }
          }
          paragraphNode.append(lexicalNode);
          return paragraphNode;
        }
        return lexicalNode;
      },
      node: tableCellNode
    };
  }
  function $createTableCellNode(headerState, colSpan = 1, width) {
    return $applyNodeReplacement$1(new TableCellNode(headerState, colSpan, width));
  }
  function $isTableCellNode(node) {
    return node instanceof TableCellNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const INSERT_TABLE_COMMAND = createCommand$1('INSERT_TABLE_COMMAND');

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class TableRowNode extends ElementNode$1 {
    /** @internal */

    static getType() {
      return 'tablerow';
    }
    static clone(node) {
      return new TableRowNode(node.__height, node.__key);
    }
    static importDOM() {
      return {
        tr: node => ({
          conversion: $convertTableRowElement,
          priority: 0
        })
      };
    }
    static importJSON(serializedNode) {
      return $createTableRowNode(serializedNode.height);
    }
    constructor(height, key) {
      super(key);
      this.__height = height;
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        ...(this.getHeight() && {
          height: this.getHeight()
        }),
        type: 'tablerow',
        version: 1
      };
    }
    createDOM(config) {
      const element = document.createElement('tr');
      if (this.__height) {
        element.style.height = `${this.__height}px`;
      }
      addClassNamesToElement$1(element, config.theme.tableRow);
      return element;
    }
    isShadowRoot() {
      return true;
    }
    setHeight(height) {
      const self = this.getWritable();
      self.__height = height;
      return this.__height;
    }
    getHeight() {
      return this.getLatest().__height;
    }
    updateDOM(prevNode) {
      return prevNode.__height !== this.__height;
    }
    canBeEmpty() {
      return false;
    }
    canIndent() {
      return false;
    }
  }
  function $convertTableRowElement(domNode) {
    const domNode_ = domNode;
    let height = undefined;
    if (PIXEL_VALUE_REG_EXP.test(domNode_.style.height)) {
      height = parseFloat(domNode_.style.height);
    }
    return {
      node: $createTableRowNode(height)
    };
  }
  function $createTableRowNode(height) {
    return $applyNodeReplacement$1(new TableRowNode(height));
  }
  function $isTableRowNode(node) {
    return node instanceof TableRowNode;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const CAN_USE_DOM$6 = typeof window !== 'undefined' && typeof window.document !== 'undefined' && typeof window.document.createElement !== 'undefined';

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  function $createTableNodeWithDimensions(rowCount, columnCount, includeHeaders = true) {
    const tableNode = $createTableNode();
    for (let iRow = 0; iRow < rowCount; iRow++) {
      const tableRowNode = $createTableRowNode();
      for (let iColumn = 0; iColumn < columnCount; iColumn++) {
        let headerState = TableCellHeaderStates.NO_STATUS;
        if (typeof includeHeaders === 'object') {
          if (iRow === 0 && includeHeaders.rows) {
            headerState |= TableCellHeaderStates.ROW;
          }
          if (iColumn === 0 && includeHeaders.columns) {
            headerState |= TableCellHeaderStates.COLUMN;
          }
        } else if (includeHeaders) {
          if (iRow === 0) {
            headerState |= TableCellHeaderStates.ROW;
          }
          if (iColumn === 0) {
            headerState |= TableCellHeaderStates.COLUMN;
          }
        }
        const tableCellNode = $createTableCellNode(headerState);
        const paragraphNode = $createParagraphNode$1();
        paragraphNode.append($createTextNode$1());
        tableCellNode.append(paragraphNode);
        tableRowNode.append(tableCellNode);
      }
      tableNode.append(tableRowNode);
    }
    return tableNode;
  }
  function $getTableCellNodeFromLexicalNode(startingNode) {
    const node = $findMatchingParent$1(startingNode, n => $isTableCellNode(n));
    if ($isTableCellNode(node)) {
      return node;
    }
    return null;
  }
  function $getTableRowNodeFromTableCellNodeOrThrow(startingNode) {
    const node = $findMatchingParent$1(startingNode, n => $isTableRowNode(n));
    if ($isTableRowNode(node)) {
      return node;
    }
    throw new Error('Expected table cell to be inside of table row.');
  }
  function $getTableNodeFromLexicalNodeOrThrow(startingNode) {
    const node = $findMatchingParent$1(startingNode, n => $isTableNode(n));
    if ($isTableNode(node)) {
      return node;
    }
    throw new Error('Expected table cell to be inside of table.');
  }
  function $getTableRowIndexFromTableCellNode(tableCellNode) {
    const tableRowNode = $getTableRowNodeFromTableCellNodeOrThrow(tableCellNode);
    const tableNode = $getTableNodeFromLexicalNodeOrThrow(tableRowNode);
    return tableNode.getChildren().findIndex(n => n.is(tableRowNode));
  }
  function $getTableColumnIndexFromTableCellNode(tableCellNode) {
    const tableRowNode = $getTableRowNodeFromTableCellNodeOrThrow(tableCellNode);
    return tableRowNode.getChildren().findIndex(n => n.is(tableCellNode));
  }
  function $getTableCellSiblingsFromTableCellNode(tableCellNode, table) {
    const tableNode = $getTableNodeFromLexicalNodeOrThrow(tableCellNode);
    const {
      x,
      y
    } = tableNode.getCordsFromCellNode(tableCellNode, table);
    return {
      above: tableNode.getCellNodeFromCords(x, y - 1, table),
      below: tableNode.getCellNodeFromCords(x, y + 1, table),
      left: tableNode.getCellNodeFromCords(x - 1, y, table),
      right: tableNode.getCellNodeFromCords(x + 1, y, table)
    };
  }
  function $removeTableRowAtIndex(tableNode, indexToDelete) {
    const tableRows = tableNode.getChildren();
    if (indexToDelete >= tableRows.length || indexToDelete < 0) {
      throw new Error('Expected table cell to be inside of table row.');
    }
    const targetRowNode = tableRows[indexToDelete];
    targetRowNode.remove();
    return tableNode;
  }
  function $insertTableRow(tableNode, targetIndex, shouldInsertAfter = true, rowCount, table) {
    const tableRows = tableNode.getChildren();
    if (targetIndex >= tableRows.length || targetIndex < 0) {
      throw new Error('Table row target index out of range');
    }
    const targetRowNode = tableRows[targetIndex];
    if ($isTableRowNode(targetRowNode)) {
      for (let r = 0; r < rowCount; r++) {
        const tableRowCells = targetRowNode.getChildren();
        const tableColumnCount = tableRowCells.length;
        const newTableRowNode = $createTableRowNode();
        for (let c = 0; c < tableColumnCount; c++) {
          const tableCellFromTargetRow = tableRowCells[c];
          if (!$isTableCellNode(tableCellFromTargetRow)) {
            throw Error(`Expected table cell`);
          }
          const {
            above,
            below
          } = $getTableCellSiblingsFromTableCellNode(tableCellFromTargetRow, table);
          let headerState = TableCellHeaderStates.NO_STATUS;
          const width = above && above.getWidth() || below && below.getWidth() || undefined;
          if (above && above.hasHeaderState(TableCellHeaderStates.COLUMN) || below && below.hasHeaderState(TableCellHeaderStates.COLUMN)) {
            headerState |= TableCellHeaderStates.COLUMN;
          }
          const tableCellNode = $createTableCellNode(headerState, 1, width);
          tableCellNode.append($createParagraphNode$1());
          newTableRowNode.append(tableCellNode);
        }
        if (shouldInsertAfter) {
          targetRowNode.insertAfter(newTableRowNode);
        } else {
          targetRowNode.insertBefore(newTableRowNode);
        }
      }
    } else {
      throw new Error('Row before insertion index does not exist.');
    }
    return tableNode;
  }
  const getHeaderState = (currentState, possibleState) => {
    if (currentState === TableCellHeaderStates.BOTH || currentState === possibleState) {
      return possibleState;
    }
    return TableCellHeaderStates.NO_STATUS;
  };
  function $insertTableRow__EXPERIMENTAL(insertAfter = true) {
    const selection = $getSelection$1();
    if (!($isRangeSelection$1(selection) || $isTableSelection(selection))) {
      throw Error(`Expected a RangeSelection or TableSelection`);
    }
    const focus = selection.focus.getNode();
    const [focusCell,, grid] = $getNodeTriplet(focus);
    const [gridMap, focusCellMap] = $computeTableMap(grid, focusCell, focusCell);
    const columnCount = gridMap[0].length;
    const {
      startRow: focusStartRow
    } = focusCellMap;
    if (insertAfter) {
      const focusEndRow = focusStartRow + focusCell.__rowSpan - 1;
      const focusEndRowMap = gridMap[focusEndRow];
      const newRow = $createTableRowNode();
      for (let i = 0; i < columnCount; i++) {
        const {
          cell,
          startRow
        } = focusEndRowMap[i];
        if (startRow + cell.__rowSpan - 1 <= focusEndRow) {
          const currentCell = focusEndRowMap[i].cell;
          const currentCellHeaderState = currentCell.__headerState;
          const headerState = getHeaderState(currentCellHeaderState, TableCellHeaderStates.COLUMN);
          newRow.append($createTableCellNode(headerState).append($createParagraphNode$1()));
        } else {
          cell.setRowSpan(cell.__rowSpan + 1);
        }
      }
      const focusEndRowNode = grid.getChildAtIndex(focusEndRow);
      if (!$isTableRowNode(focusEndRowNode)) {
        throw Error(`focusEndRow is not a TableRowNode`);
      }
      focusEndRowNode.insertAfter(newRow);
    } else {
      const focusStartRowMap = gridMap[focusStartRow];
      const newRow = $createTableRowNode();
      for (let i = 0; i < columnCount; i++) {
        const {
          cell,
          startRow
        } = focusStartRowMap[i];
        if (startRow === focusStartRow) {
          const currentCell = focusStartRowMap[i].cell;
          const currentCellHeaderState = currentCell.__headerState;
          const headerState = getHeaderState(currentCellHeaderState, TableCellHeaderStates.COLUMN);
          newRow.append($createTableCellNode(headerState).append($createParagraphNode$1()));
        } else {
          cell.setRowSpan(cell.__rowSpan + 1);
        }
      }
      const focusStartRowNode = grid.getChildAtIndex(focusStartRow);
      if (!$isTableRowNode(focusStartRowNode)) {
        throw Error(`focusEndRow is not a TableRowNode`);
      }
      focusStartRowNode.insertBefore(newRow);
    }
  }
  function $insertTableColumn(tableNode, targetIndex, shouldInsertAfter = true, columnCount, table) {
    const tableRows = tableNode.getChildren();
    const tableCellsToBeInserted = [];
    for (let r = 0; r < tableRows.length; r++) {
      const currentTableRowNode = tableRows[r];
      if ($isTableRowNode(currentTableRowNode)) {
        for (let c = 0; c < columnCount; c++) {
          const tableRowChildren = currentTableRowNode.getChildren();
          if (targetIndex >= tableRowChildren.length || targetIndex < 0) {
            throw new Error('Table column target index out of range');
          }
          const targetCell = tableRowChildren[targetIndex];
          if (!$isTableCellNode(targetCell)) {
            throw Error(`Expected table cell`);
          }
          const {
            left,
            right
          } = $getTableCellSiblingsFromTableCellNode(targetCell, table);
          let headerState = TableCellHeaderStates.NO_STATUS;
          if (left && left.hasHeaderState(TableCellHeaderStates.ROW) || right && right.hasHeaderState(TableCellHeaderStates.ROW)) {
            headerState |= TableCellHeaderStates.ROW;
          }
          const newTableCell = $createTableCellNode(headerState);
          newTableCell.append($createParagraphNode$1());
          tableCellsToBeInserted.push({
            newTableCell,
            targetCell
          });
        }
      }
    }
    tableCellsToBeInserted.forEach(({
      newTableCell,
      targetCell
    }) => {
      if (shouldInsertAfter) {
        targetCell.insertAfter(newTableCell);
      } else {
        targetCell.insertBefore(newTableCell);
      }
    });
    return tableNode;
  }
  function $insertTableColumn__EXPERIMENTAL(insertAfter = true) {
    const selection = $getSelection$1();
    if (!($isRangeSelection$1(selection) || $isTableSelection(selection))) {
      throw Error(`Expected a RangeSelection or TableSelection`);
    }
    const anchor = selection.anchor.getNode();
    const focus = selection.focus.getNode();
    const [anchorCell] = $getNodeTriplet(anchor);
    const [focusCell,, grid] = $getNodeTriplet(focus);
    const [gridMap, focusCellMap, anchorCellMap] = $computeTableMap(grid, focusCell, anchorCell);
    const rowCount = gridMap.length;
    const startColumn = insertAfter ? Math.max(focusCellMap.startColumn, anchorCellMap.startColumn) : Math.min(focusCellMap.startColumn, anchorCellMap.startColumn);
    const insertAfterColumn = insertAfter ? startColumn + focusCell.__colSpan - 1 : startColumn - 1;
    const gridFirstChild = grid.getFirstChild();
    if (!$isTableRowNode(gridFirstChild)) {
      throw Error(`Expected firstTable child to be a row`);
    }
    let firstInsertedCell = null;
    function $createTableCellNodeForInsertTableColumn(headerState = TableCellHeaderStates.NO_STATUS) {
      const cell = $createTableCellNode(headerState).append($createParagraphNode$1());
      if (firstInsertedCell === null) {
        firstInsertedCell = cell;
      }
      return cell;
    }
    let loopRow = gridFirstChild;
    rowLoop: for (let i = 0; i < rowCount; i++) {
      if (i !== 0) {
        const currentRow = loopRow.getNextSibling();
        if (!$isTableRowNode(currentRow)) {
          throw Error(`Expected row nextSibling to be a row`);
        }
        loopRow = currentRow;
      }
      const rowMap = gridMap[i];
      const currentCellHeaderState = rowMap[insertAfterColumn < 0 ? 0 : insertAfterColumn].cell.__headerState;
      const headerState = getHeaderState(currentCellHeaderState, TableCellHeaderStates.ROW);
      if (insertAfterColumn < 0) {
        $insertFirst$2(loopRow, $createTableCellNodeForInsertTableColumn(headerState));
        continue;
      }
      const {
        cell: currentCell,
        startColumn: currentStartColumn,
        startRow: currentStartRow
      } = rowMap[insertAfterColumn];
      if (currentStartColumn + currentCell.__colSpan - 1 <= insertAfterColumn) {
        let insertAfterCell = currentCell;
        let insertAfterCellRowStart = currentStartRow;
        let prevCellIndex = insertAfterColumn;
        while (insertAfterCellRowStart !== i && insertAfterCell.__rowSpan > 1) {
          prevCellIndex -= currentCell.__colSpan;
          if (prevCellIndex >= 0) {
            const {
              cell: cell_,
              startRow: startRow_
            } = rowMap[prevCellIndex];
            insertAfterCell = cell_;
            insertAfterCellRowStart = startRow_;
          } else {
            loopRow.append($createTableCellNodeForInsertTableColumn(headerState));
            continue rowLoop;
          }
        }
        insertAfterCell.insertAfter($createTableCellNodeForInsertTableColumn(headerState));
      } else {
        currentCell.setColSpan(currentCell.__colSpan + 1);
      }
    }
    if (firstInsertedCell !== null) {
      $moveSelectionToCell(firstInsertedCell);
    }
  }
  function $deleteTableColumn(tableNode, targetIndex) {
    const tableRows = tableNode.getChildren();
    for (let i = 0; i < tableRows.length; i++) {
      const currentTableRowNode = tableRows[i];
      if ($isTableRowNode(currentTableRowNode)) {
        const tableRowChildren = currentTableRowNode.getChildren();
        if (targetIndex >= tableRowChildren.length || targetIndex < 0) {
          throw new Error('Table column target index out of range');
        }
        tableRowChildren[targetIndex].remove();
      }
    }
    return tableNode;
  }
  function $deleteTableRow__EXPERIMENTAL() {
    const selection = $getSelection$1();
    if (!($isRangeSelection$1(selection) || $isTableSelection(selection))) {
      throw Error(`Expected a RangeSelection or TableSelection`);
    }
    const anchor = selection.anchor.getNode();
    const focus = selection.focus.getNode();
    const [anchorCell,, grid] = $getNodeTriplet(anchor);
    const [focusCell] = $getNodeTriplet(focus);
    const [gridMap, anchorCellMap, focusCellMap] = $computeTableMap(grid, anchorCell, focusCell);
    const {
      startRow: anchorStartRow
    } = anchorCellMap;
    const {
      startRow: focusStartRow
    } = focusCellMap;
    const focusEndRow = focusStartRow + focusCell.__rowSpan - 1;
    if (gridMap.length === focusEndRow - anchorStartRow + 1) {
      // Empty grid
      grid.remove();
      return;
    }
    const columnCount = gridMap[0].length;
    const nextRow = gridMap[focusEndRow + 1];
    const nextRowNode = grid.getChildAtIndex(focusEndRow + 1);
    for (let row = focusEndRow; row >= anchorStartRow; row--) {
      for (let column = columnCount - 1; column >= 0; column--) {
        const {
          cell,
          startRow: cellStartRow,
          startColumn: cellStartColumn
        } = gridMap[row][column];
        if (cellStartColumn !== column) {
          // Don't repeat work for the same Cell
          continue;
        }
        // Rows overflowing top have to be trimmed
        if (row === anchorStartRow && cellStartRow < anchorStartRow) {
          cell.setRowSpan(cell.__rowSpan - (cellStartRow - anchorStartRow));
        }
        // Rows overflowing bottom have to be trimmed and moved to the next row
        if (cellStartRow >= anchorStartRow && cellStartRow + cell.__rowSpan - 1 > focusEndRow) {
          cell.setRowSpan(cell.__rowSpan - (focusEndRow - cellStartRow + 1));
          if (!(nextRowNode !== null)) {
            throw Error(`Expected nextRowNode not to be null`);
          }
          if (column === 0) {
            $insertFirst$2(nextRowNode, cell);
          } else {
            const {
              cell: previousCell
            } = nextRow[column - 1];
            previousCell.insertAfter(cell);
          }
        }
      }
      const rowNode = grid.getChildAtIndex(row);
      if (!$isTableRowNode(rowNode)) {
        throw Error(`Expected GridNode childAtIndex(${String(row)}) to be RowNode`);
      }
      rowNode.remove();
    }
    if (nextRow !== undefined) {
      const {
        cell
      } = nextRow[0];
      $moveSelectionToCell(cell);
    } else {
      const previousRow = gridMap[anchorStartRow - 1];
      const {
        cell
      } = previousRow[0];
      $moveSelectionToCell(cell);
    }
  }
  function $deleteTableColumn__EXPERIMENTAL() {
    const selection = $getSelection$1();
    if (!($isRangeSelection$1(selection) || $isTableSelection(selection))) {
      throw Error(`Expected a RangeSelection or TableSelection`);
    }
    const anchor = selection.anchor.getNode();
    const focus = selection.focus.getNode();
    const [anchorCell,, grid] = $getNodeTriplet(anchor);
    const [focusCell] = $getNodeTriplet(focus);
    const [gridMap, anchorCellMap, focusCellMap] = $computeTableMap(grid, anchorCell, focusCell);
    const {
      startColumn: anchorStartColumn
    } = anchorCellMap;
    const {
      startRow: focusStartRow,
      startColumn: focusStartColumn
    } = focusCellMap;
    const startColumn = Math.min(anchorStartColumn, focusStartColumn);
    const endColumn = Math.max(anchorStartColumn + anchorCell.__colSpan - 1, focusStartColumn + focusCell.__colSpan - 1);
    const selectedColumnCount = endColumn - startColumn + 1;
    const columnCount = gridMap[0].length;
    if (columnCount === endColumn - startColumn + 1) {
      // Empty grid
      grid.selectPrevious();
      grid.remove();
      return;
    }
    const rowCount = gridMap.length;
    for (let row = 0; row < rowCount; row++) {
      for (let column = startColumn; column <= endColumn; column++) {
        const {
          cell,
          startColumn: cellStartColumn
        } = gridMap[row][column];
        if (cellStartColumn < startColumn) {
          if (column === startColumn) {
            const overflowLeft = startColumn - cellStartColumn;
            // Overflowing left
            cell.setColSpan(cell.__colSpan -
            // Possible overflow right too
            Math.min(selectedColumnCount, cell.__colSpan - overflowLeft));
          }
        } else if (cellStartColumn + cell.__colSpan - 1 > endColumn) {
          if (column === endColumn) {
            // Overflowing right
            const inSelectedArea = endColumn - cellStartColumn + 1;
            cell.setColSpan(cell.__colSpan - inSelectedArea);
          }
        } else {
          cell.remove();
        }
      }
    }
    const focusRowMap = gridMap[focusStartRow];
    const nextColumn = anchorStartColumn > focusStartColumn ? focusRowMap[anchorStartColumn + anchorCell.__colSpan] : focusRowMap[focusStartColumn + focusCell.__colSpan];
    if (nextColumn !== undefined) {
      const {
        cell
      } = nextColumn;
      $moveSelectionToCell(cell);
    } else {
      const previousRow = focusStartColumn < anchorStartColumn ? focusRowMap[focusStartColumn - 1] : focusRowMap[anchorStartColumn - 1];
      const {
        cell
      } = previousRow;
      $moveSelectionToCell(cell);
    }
  }
  function $moveSelectionToCell(cell) {
    const firstDescendant = cell.getFirstDescendant();
    if (firstDescendant == null) {
      cell.selectStart();
    } else {
      firstDescendant.getParentOrThrow().selectStart();
    }
  }
  function $insertFirst$2(parent, node) {
    const firstChild = parent.getFirstChild();
    if (firstChild !== null) {
      firstChild.insertBefore(node);
    } else {
      parent.append(node);
    }
  }
  function $unmergeCell() {
    const selection = $getSelection$1();
    if (!($isRangeSelection$1(selection) || $isTableSelection(selection))) {
      throw Error(`Expected a RangeSelection or TableSelection`);
    }
    const anchor = selection.anchor.getNode();
    const [cell, row, grid] = $getNodeTriplet(anchor);
    const colSpan = cell.__colSpan;
    const rowSpan = cell.__rowSpan;
    if (colSpan > 1) {
      for (let i = 1; i < colSpan; i++) {
        cell.insertAfter($createTableCellNode(TableCellHeaderStates.NO_STATUS).append($createParagraphNode$1()));
      }
      cell.setColSpan(1);
    }
    if (rowSpan > 1) {
      const [map, cellMap] = $computeTableMap(grid, cell, cell);
      const {
        startColumn,
        startRow
      } = cellMap;
      let currentRowNode;
      for (let i = 1; i < rowSpan; i++) {
        const currentRow = startRow + i;
        const currentRowMap = map[currentRow];
        currentRowNode = (currentRowNode || row).getNextSibling();
        if (!$isTableRowNode(currentRowNode)) {
          throw Error(`Expected row next sibling to be a row`);
        }
        let insertAfterCell = null;
        for (let column = 0; column < startColumn; column++) {
          const currentCellMap = currentRowMap[column];
          const currentCell = currentCellMap.cell;
          if (currentCellMap.startRow === currentRow) {
            insertAfterCell = currentCell;
          }
          if (currentCell.__colSpan > 1) {
            column += currentCell.__colSpan - 1;
          }
        }
        if (insertAfterCell === null) {
          for (let j = 0; j < colSpan; j++) {
            $insertFirst$2(currentRowNode, $createTableCellNode(TableCellHeaderStates.NO_STATUS).append($createParagraphNode$1()));
          }
        } else {
          for (let j = 0; j < colSpan; j++) {
            insertAfterCell.insertAfter($createTableCellNode(TableCellHeaderStates.NO_STATUS).append($createParagraphNode$1()));
          }
        }
      }
      cell.setRowSpan(1);
    }
  }
  function $computeTableMap(grid, cellA, cellB) {
    const [tableMap, cellAValue, cellBValue] = $computeTableMapSkipCellCheck(grid, cellA, cellB);
    if (!(cellAValue !== null)) {
      throw Error(`Anchor not found in Grid`);
    }
    if (!(cellBValue !== null)) {
      throw Error(`Focus not found in Grid`);
    }
    return [tableMap, cellAValue, cellBValue];
  }
  function $computeTableMapSkipCellCheck(grid, cellA, cellB) {
    const tableMap = [];
    let cellAValue = null;
    let cellBValue = null;
    function write(startRow, startColumn, cell) {
      const value = {
        cell,
        startColumn,
        startRow
      };
      const rowSpan = cell.__rowSpan;
      const colSpan = cell.__colSpan;
      for (let i = 0; i < rowSpan; i++) {
        if (tableMap[startRow + i] === undefined) {
          tableMap[startRow + i] = [];
        }
        for (let j = 0; j < colSpan; j++) {
          tableMap[startRow + i][startColumn + j] = value;
        }
      }
      if (cellA !== null && cellA.is(cell)) {
        cellAValue = value;
      }
      if (cellB !== null && cellB.is(cell)) {
        cellBValue = value;
      }
    }
    function isEmpty(row, column) {
      return tableMap[row] === undefined || tableMap[row][column] === undefined;
    }
    const gridChildren = grid.getChildren();
    for (let i = 0; i < gridChildren.length; i++) {
      const row = gridChildren[i];
      if (!$isTableRowNode(row)) {
        throw Error(`Expected GridNode children to be TableRowNode`);
      }
      const rowChildren = row.getChildren();
      let j = 0;
      for (const cell of rowChildren) {
        if (!$isTableCellNode(cell)) {
          throw Error(`Expected TableRowNode children to be TableCellNode`);
        }
        while (!isEmpty(i, j)) {
          j++;
        }
        write(i, j, cell);
        j += cell.__colSpan;
      }
    }
    return [tableMap, cellAValue, cellBValue];
  }
  function $getNodeTriplet(source) {
    let cell;
    if (source instanceof TableCellNode) {
      cell = source;
    } else if ('__type' in source) {
      const cell_ = $findMatchingParent$1(source, $isTableCellNode);
      if (!$isTableCellNode(cell_)) {
        throw Error(`Expected to find a parent TableCellNode`);
      }
      cell = cell_;
    } else {
      const cell_ = $findMatchingParent$1(source.getNode(), $isTableCellNode);
      if (!$isTableCellNode(cell_)) {
        throw Error(`Expected to find a parent TableCellNode`);
      }
      cell = cell_;
    }
    const row = cell.getParent();
    if (!$isTableRowNode(row)) {
      throw Error(`Expected TableCellNode to have a parent TableRowNode`);
    }
    const grid = row.getParent();
    if (!$isTableNode(grid)) {
      throw Error(`Expected TableRowNode to have a parent GridNode`);
    }
    return [cell, row, grid];
  }
  function $getTableCellNodeRect(tableCellNode) {
    const [cellNode,, gridNode] = $getNodeTriplet(tableCellNode);
    const rows = gridNode.getChildren();
    const rowCount = rows.length;
    const columnCount = rows[0].getChildren().length;

    // Create a matrix of the same size as the table to track the position of each cell
    const cellMatrix = new Array(rowCount);
    for (let i = 0; i < rowCount; i++) {
      cellMatrix[i] = new Array(columnCount);
    }
    for (let rowIndex = 0; rowIndex < rowCount; rowIndex++) {
      const row = rows[rowIndex];
      const cells = row.getChildren();
      let columnIndex = 0;
      for (let cellIndex = 0; cellIndex < cells.length; cellIndex++) {
        // Find the next available position in the matrix, skip the position of merged cells
        while (cellMatrix[rowIndex][columnIndex]) {
          columnIndex++;
        }
        const cell = cells[cellIndex];
        const rowSpan = cell.__rowSpan || 1;
        const colSpan = cell.__colSpan || 1;

        // Put the cell into the corresponding position in the matrix
        for (let i = 0; i < rowSpan; i++) {
          for (let j = 0; j < colSpan; j++) {
            cellMatrix[rowIndex + i][columnIndex + j] = cell;
          }
        }

        // Return to the original index, row span and column span of the cell.
        if (cellNode === cell) {
          return {
            colSpan,
            columnIndex,
            rowIndex,
            rowSpan
          };
        }
        columnIndex += colSpan;
      }
    }
    return null;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  class TableSelection {
    constructor(tableKey, anchor, focus) {
      this.anchor = anchor;
      this.focus = focus;
      anchor._selection = this;
      focus._selection = this;
      this._cachedNodes = null;
      this.dirty = false;
      this.tableKey = tableKey;
    }
    getStartEndPoints() {
      return [this.anchor, this.focus];
    }

    /**
     * Returns whether the Selection is "backwards", meaning the focus
     * logically precedes the anchor in the EditorState.
     * @returns true if the Selection is backwards, false otherwise.
     */
    isBackward() {
      return this.focus.isBefore(this.anchor);
    }
    getCachedNodes() {
      return this._cachedNodes;
    }
    setCachedNodes(nodes) {
      this._cachedNodes = nodes;
    }
    is(selection) {
      if (!$isTableSelection(selection)) {
        return false;
      }
      return this.tableKey === selection.tableKey && this.anchor.is(selection.anchor) && this.focus.is(selection.focus);
    }
    set(tableKey, anchorCellKey, focusCellKey) {
      this.dirty = true;
      this.tableKey = tableKey;
      this.anchor.key = anchorCellKey;
      this.focus.key = focusCellKey;
      this._cachedNodes = null;
    }
    clone() {
      return new TableSelection(this.tableKey, this.anchor, this.focus);
    }
    isCollapsed() {
      return false;
    }
    extract() {
      return this.getNodes();
    }
    insertRawText(text) {
      // Do nothing?
    }
    insertText() {
      // Do nothing?
    }
    insertNodes(nodes) {
      const focusNode = this.focus.getNode();
      if (!$isElementNode$1(focusNode)) {
        throw Error(`Expected TableSelection focus to be an ElementNode`);
      }
      const selection = $normalizeSelection__EXPERIMENTAL(focusNode.select(0, focusNode.getChildrenSize()));
      selection.insertNodes(nodes);
    }

    // TODO Deprecate this method. It's confusing when used with colspan|rowspan
    getShape() {
      const anchorCellNode = $getNodeByKey$1(this.anchor.key);
      if (!$isTableCellNode(anchorCellNode)) {
        throw Error(`Expected TableSelection anchor to be (or a child of) TableCellNode`);
      }
      const anchorCellNodeRect = $getTableCellNodeRect(anchorCellNode);
      if (!(anchorCellNodeRect !== null)) {
        throw Error(`getCellRect: expected to find AnchorNode`);
      }
      const focusCellNode = $getNodeByKey$1(this.focus.key);
      if (!$isTableCellNode(focusCellNode)) {
        throw Error(`Expected TableSelection focus to be (or a child of) TableCellNode`);
      }
      const focusCellNodeRect = $getTableCellNodeRect(focusCellNode);
      if (!(focusCellNodeRect !== null)) {
        throw Error(`getCellRect: expected to find focusCellNode`);
      }
      const startX = Math.min(anchorCellNodeRect.columnIndex, focusCellNodeRect.columnIndex);
      const stopX = Math.max(anchorCellNodeRect.columnIndex, focusCellNodeRect.columnIndex);
      const startY = Math.min(anchorCellNodeRect.rowIndex, focusCellNodeRect.rowIndex);
      const stopY = Math.max(anchorCellNodeRect.rowIndex, focusCellNodeRect.rowIndex);
      return {
        fromX: Math.min(startX, stopX),
        fromY: Math.min(startY, stopY),
        toX: Math.max(startX, stopX),
        toY: Math.max(startY, stopY)
      };
    }
    getNodes() {
      const cachedNodes = this._cachedNodes;
      if (cachedNodes !== null) {
        return cachedNodes;
      }
      const anchorNode = this.anchor.getNode();
      const focusNode = this.focus.getNode();
      const anchorCell = $findMatchingParent$1(anchorNode, $isTableCellNode);
      // todo replace with triplet
      const focusCell = $findMatchingParent$1(focusNode, $isTableCellNode);
      if (!$isTableCellNode(anchorCell)) {
        throw Error(`Expected TableSelection anchor to be (or a child of) TableCellNode`);
      }
      if (!$isTableCellNode(focusCell)) {
        throw Error(`Expected TableSelection focus to be (or a child of) TableCellNode`);
      }
      const anchorRow = anchorCell.getParent();
      if (!$isTableRowNode(anchorRow)) {
        throw Error(`Expected anchorCell to have a parent TableRowNode`);
      }
      const tableNode = anchorRow.getParent();
      if (!$isTableNode(tableNode)) {
        throw Error(`Expected tableNode to have a parent TableNode`);
      }
      const focusCellGrid = focusCell.getParents()[1];
      if (focusCellGrid !== tableNode) {
        if (!tableNode.isParentOf(focusCell)) {
          // focus is on higher Grid level than anchor
          const gridParent = tableNode.getParent();
          if (!(gridParent != null)) {
            throw Error(`Expected gridParent to have a parent`);
          }
          this.set(this.tableKey, gridParent.getKey(), focusCell.getKey());
        } else {
          // anchor is on higher Grid level than focus
          const focusCellParent = focusCellGrid.getParent();
          if (!(focusCellParent != null)) {
            throw Error(`Expected focusCellParent to have a parent`);
          }
          this.set(this.tableKey, focusCell.getKey(), focusCellParent.getKey());
        }
        return this.getNodes();
      }

      // TODO Mapping the whole Grid every time not efficient. We need to compute the entire state only
      // once (on load) and iterate on it as updates occur. However, to do this we need to have the
      // ability to store a state. Killing TableSelection and moving the logic to the plugin would make
      // this possible.
      const [map, cellAMap, cellBMap] = $computeTableMap(tableNode, anchorCell, focusCell);
      let minColumn = Math.min(cellAMap.startColumn, cellBMap.startColumn);
      let minRow = Math.min(cellAMap.startRow, cellBMap.startRow);
      let maxColumn = Math.max(cellAMap.startColumn + cellAMap.cell.__colSpan - 1, cellBMap.startColumn + cellBMap.cell.__colSpan - 1);
      let maxRow = Math.max(cellAMap.startRow + cellAMap.cell.__rowSpan - 1, cellBMap.startRow + cellBMap.cell.__rowSpan - 1);
      let exploredMinColumn = minColumn;
      let exploredMinRow = minRow;
      let exploredMaxColumn = minColumn;
      let exploredMaxRow = minRow;
      function expandBoundary(mapValue) {
        const {
          cell,
          startColumn: cellStartColumn,
          startRow: cellStartRow
        } = mapValue;
        minColumn = Math.min(minColumn, cellStartColumn);
        minRow = Math.min(minRow, cellStartRow);
        maxColumn = Math.max(maxColumn, cellStartColumn + cell.__colSpan - 1);
        maxRow = Math.max(maxRow, cellStartRow + cell.__rowSpan - 1);
      }
      while (minColumn < exploredMinColumn || minRow < exploredMinRow || maxColumn > exploredMaxColumn || maxRow > exploredMaxRow) {
        if (minColumn < exploredMinColumn) {
          // Expand on the left
          const rowDiff = exploredMaxRow - exploredMinRow;
          const previousColumn = exploredMinColumn - 1;
          for (let i = 0; i <= rowDiff; i++) {
            expandBoundary(map[exploredMinRow + i][previousColumn]);
          }
          exploredMinColumn = previousColumn;
        }
        if (minRow < exploredMinRow) {
          // Expand on top
          const columnDiff = exploredMaxColumn - exploredMinColumn;
          const previousRow = exploredMinRow - 1;
          for (let i = 0; i <= columnDiff; i++) {
            expandBoundary(map[previousRow][exploredMinColumn + i]);
          }
          exploredMinRow = previousRow;
        }
        if (maxColumn > exploredMaxColumn) {
          // Expand on the right
          const rowDiff = exploredMaxRow - exploredMinRow;
          const nextColumn = exploredMaxColumn + 1;
          for (let i = 0; i <= rowDiff; i++) {
            expandBoundary(map[exploredMinRow + i][nextColumn]);
          }
          exploredMaxColumn = nextColumn;
        }
        if (maxRow > exploredMaxRow) {
          // Expand on the bottom
          const columnDiff = exploredMaxColumn - exploredMinColumn;
          const nextRow = exploredMaxRow + 1;
          for (let i = 0; i <= columnDiff; i++) {
            expandBoundary(map[nextRow][exploredMinColumn + i]);
          }
          exploredMaxRow = nextRow;
        }
      }
      const nodes = [tableNode];
      let lastRow = null;
      for (let i = minRow; i <= maxRow; i++) {
        for (let j = minColumn; j <= maxColumn; j++) {
          const {
            cell
          } = map[i][j];
          const currentRow = cell.getParent();
          if (!$isTableRowNode(currentRow)) {
            throw Error(`Expected TableCellNode parent to be a TableRowNode`);
          }
          if (currentRow !== lastRow) {
            nodes.push(currentRow);
          }
          nodes.push(cell, ...$getChildrenRecursively(cell));
          lastRow = currentRow;
        }
      }
      if (!isCurrentlyReadOnlyMode$1()) {
        this._cachedNodes = nodes;
      }
      return nodes;
    }
    getTextContent() {
      const nodes = this.getNodes().filter(node => $isTableCellNode(node));
      let textContent = '';
      for (let i = 0; i < nodes.length; i++) {
        const node = nodes[i];
        const row = node.__parent;
        const nextRow = (nodes[i + 1] || {}).__parent;
        textContent += node.getTextContent() + (nextRow !== row ? '\n' : '\t');
      }
      return textContent;
    }
  }
  function $isTableSelection(x) {
    return x instanceof TableSelection;
  }
  function $createTableSelection() {
    const anchor = $createPoint$1('root', 0, 'element');
    const focus = $createPoint$1('root', 0, 'element');
    return new TableSelection('root', anchor, focus);
  }
  function $getChildrenRecursively(node) {
    const nodes = [];
    const stack = [node];
    while (stack.length > 0) {
      const currentNode = stack.pop();
      if (!(currentNode !== undefined)) {
        throw Error(`Stack.length > 0; can't be undefined`);
      }
      if ($isElementNode$1(currentNode)) {
        stack.unshift(...currentNode.getChildren());
      }
      if (currentNode !== node) {
        nodes.push(currentNode);
      }
    }
    return nodes;
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  class TableObserver {
    constructor(editor, tableNodeKey) {
      this.isHighlightingCells = false;
      this.anchorX = -1;
      this.anchorY = -1;
      this.focusX = -1;
      this.focusY = -1;
      this.listenersToRemove = new Set();
      this.tableNodeKey = tableNodeKey;
      this.editor = editor;
      this.table = {
        columns: 0,
        domRows: [],
        rows: 0
      };
      this.tableSelection = null;
      this.anchorCellNodeKey = null;
      this.focusCellNodeKey = null;
      this.anchorCell = null;
      this.focusCell = null;
      this.hasHijackedSelectionStyles = false;
      this.trackTable();
      this.isSelecting = false;
    }
    getTable() {
      return this.table;
    }
    removeListeners() {
      Array.from(this.listenersToRemove).forEach(removeListener => removeListener());
    }
    trackTable() {
      const observer = new MutationObserver(records => {
        this.editor.update(() => {
          let gridNeedsRedraw = false;
          for (let i = 0; i < records.length; i++) {
            const record = records[i];
            const target = record.target;
            const nodeName = target.nodeName;
            if (nodeName === 'TABLE' || nodeName === 'TBODY' || nodeName === 'THEAD' || nodeName === 'TR') {
              gridNeedsRedraw = true;
              break;
            }
          }
          if (!gridNeedsRedraw) {
            return;
          }
          const tableElement = this.editor.getElementByKey(this.tableNodeKey);
          if (!tableElement) {
            throw new Error('Expected to find TableElement in DOM');
          }
          this.table = getTable(tableElement);
        });
      });
      this.editor.update(() => {
        const tableElement = this.editor.getElementByKey(this.tableNodeKey);
        if (!tableElement) {
          throw new Error('Expected to find TableElement in DOM');
        }
        this.table = getTable(tableElement);
        observer.observe(tableElement, {
          attributes: true,
          childList: true,
          subtree: true
        });
      });
    }
    clearHighlight() {
      const editor = this.editor;
      this.isHighlightingCells = false;
      this.anchorX = -1;
      this.anchorY = -1;
      this.focusX = -1;
      this.focusY = -1;
      this.tableSelection = null;
      this.anchorCellNodeKey = null;
      this.focusCellNodeKey = null;
      this.anchorCell = null;
      this.focusCell = null;
      this.hasHijackedSelectionStyles = false;
      this.enableHighlightStyle();
      editor.update(() => {
        const tableNode = $getNodeByKey$1(this.tableNodeKey);
        if (!$isTableNode(tableNode)) {
          throw new Error('Expected TableNode.');
        }
        const tableElement = editor.getElementByKey(this.tableNodeKey);
        if (!tableElement) {
          throw new Error('Expected to find TableElement in DOM');
        }
        const grid = getTable(tableElement);
        $updateDOMForSelection(editor, grid, null);
        $setSelection$1(null);
        editor.dispatchCommand(SELECTION_CHANGE_COMMAND$1, undefined);
      });
    }
    enableHighlightStyle() {
      const editor = this.editor;
      editor.update(() => {
        const tableElement = editor.getElementByKey(this.tableNodeKey);
        if (!tableElement) {
          throw new Error('Expected to find TableElement in DOM');
        }
        removeClassNamesFromElement$1(tableElement, editor._config.theme.tableSelection);
        tableElement.classList.remove('disable-selection');
        this.hasHijackedSelectionStyles = false;
      });
    }
    disableHighlightStyle() {
      const editor = this.editor;
      editor.update(() => {
        const tableElement = editor.getElementByKey(this.tableNodeKey);
        if (!tableElement) {
          throw new Error('Expected to find TableElement in DOM');
        }
        addClassNamesToElement$1(tableElement, editor._config.theme.tableSelection);
        this.hasHijackedSelectionStyles = true;
      });
    }
    updateTableTableSelection(selection) {
      if (selection !== null && selection.tableKey === this.tableNodeKey) {
        const editor = this.editor;
        this.tableSelection = selection;
        this.isHighlightingCells = true;
        this.disableHighlightStyle();
        $updateDOMForSelection(editor, this.table, this.tableSelection);
      } else if (selection == null) {
        this.clearHighlight();
      } else {
        this.tableNodeKey = selection.tableKey;
        this.updateTableTableSelection(selection);
      }
    }
    setFocusCellForSelection(cell, ignoreStart = false) {
      const editor = this.editor;
      editor.update(() => {
        const tableNode = $getNodeByKey$1(this.tableNodeKey);
        if (!$isTableNode(tableNode)) {
          throw new Error('Expected TableNode.');
        }
        const tableElement = editor.getElementByKey(this.tableNodeKey);
        if (!tableElement) {
          throw new Error('Expected to find TableElement in DOM');
        }
        const cellX = cell.x;
        const cellY = cell.y;
        this.focusCell = cell;
        if (this.anchorCell !== null) {
          const domSelection = getDOMSelection$2(editor._window);
          // Collapse the selection
          if (domSelection) {
            domSelection.setBaseAndExtent(this.anchorCell.elem, 0, this.focusCell.elem, 0);
          }
        }
        if (!this.isHighlightingCells && (this.anchorX !== cellX || this.anchorY !== cellY || ignoreStart)) {
          this.isHighlightingCells = true;
          this.disableHighlightStyle();
        } else if (cellX === this.focusX && cellY === this.focusY) {
          return;
        }
        this.focusX = cellX;
        this.focusY = cellY;
        if (this.isHighlightingCells) {
          const focusTableCellNode = $getNearestNodeFromDOMNode$1(cell.elem);
          if (this.tableSelection != null && this.anchorCellNodeKey != null && $isTableCellNode(focusTableCellNode) && tableNode.is($findTableNode(focusTableCellNode))) {
            const focusNodeKey = focusTableCellNode.getKey();
            this.tableSelection = this.tableSelection.clone() || $createTableSelection();
            this.focusCellNodeKey = focusNodeKey;
            this.tableSelection.set(this.tableNodeKey, this.anchorCellNodeKey, this.focusCellNodeKey);
            $setSelection$1(this.tableSelection);
            editor.dispatchCommand(SELECTION_CHANGE_COMMAND$1, undefined);
            $updateDOMForSelection(editor, this.table, this.tableSelection);
          }
        }
      });
    }
    setAnchorCellForSelection(cell) {
      this.isHighlightingCells = false;
      this.anchorCell = cell;
      this.anchorX = cell.x;
      this.anchorY = cell.y;
      this.editor.update(() => {
        const anchorTableCellNode = $getNearestNodeFromDOMNode$1(cell.elem);
        if ($isTableCellNode(anchorTableCellNode)) {
          const anchorNodeKey = anchorTableCellNode.getKey();
          this.tableSelection = this.tableSelection != null ? this.tableSelection.clone() : $createTableSelection();
          this.anchorCellNodeKey = anchorNodeKey;
        }
      });
    }
    formatCells(type) {
      this.editor.update(() => {
        const selection = $getSelection$1();
        if (!$isTableSelection(selection)) {
          {
            throw Error(`Expected grid selection`);
          }
        }
        const formatSelection = $createRangeSelection$1();
        const anchor = formatSelection.anchor;
        const focus = formatSelection.focus;
        selection.getNodes().forEach(cellNode => {
          if ($isTableCellNode(cellNode) && cellNode.getTextContentSize() !== 0) {
            anchor.set(cellNode.getKey(), 0, 'element');
            focus.set(cellNode.getKey(), cellNode.getChildrenSize(), 'element');
            formatSelection.formatText(type);
          }
        });
        $setSelection$1(selection);
        this.editor.dispatchCommand(SELECTION_CHANGE_COMMAND$1, undefined);
      });
    }
    clearText() {
      const editor = this.editor;
      editor.update(() => {
        const tableNode = $getNodeByKey$1(this.tableNodeKey);
        if (!$isTableNode(tableNode)) {
          throw new Error('Expected TableNode.');
        }
        const selection = $getSelection$1();
        if (!$isTableSelection(selection)) {
          {
            throw Error(`Expected grid selection`);
          }
        }
        const selectedNodes = selection.getNodes().filter($isTableCellNode);
        if (selectedNodes.length === this.table.columns * this.table.rows) {
          tableNode.selectPrevious();
          // Delete entire table
          tableNode.remove();
          const rootNode = $getRoot$1();
          rootNode.selectStart();
          return;
        }
        selectedNodes.forEach(cellNode => {
          if ($isElementNode$1(cellNode)) {
            const paragraphNode = $createParagraphNode$1();
            const textNode = $createTextNode$1();
            paragraphNode.append(textNode);
            cellNode.append(paragraphNode);
            cellNode.getChildren().forEach(child => {
              if (child !== paragraphNode) {
                child.remove();
              }
            });
          }
        });
        $updateDOMForSelection(editor, this.table, null);
        $setSelection$1(null);
        editor.dispatchCommand(SELECTION_CHANGE_COMMAND$1, undefined);
      });
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  const LEXICAL_ELEMENT_KEY = '__lexicalTableSelection';
  const getDOMSelection$2 = targetWindow => CAN_USE_DOM$6 ? (targetWindow || window).getSelection() : null;
  const isMouseDownOnEvent = event => {
    return (event.buttons & 1) === 1;
  };
  function applyTableHandlers(tableNode, tableElement, editor, hasTabHandler) {
    const rootElement = editor.getRootElement();
    if (rootElement === null) {
      throw new Error('No root element.');
    }
    const tableObserver = new TableObserver(editor, tableNode.getKey());
    const editorWindow = editor._window || window;
    attachTableObserverToTableElement(tableElement, tableObserver);
    const createMouseHandlers = () => {
      const onMouseUp = () => {
        tableObserver.isSelecting = false;
        editorWindow.removeEventListener('mouseup', onMouseUp);
        editorWindow.removeEventListener('mousemove', onMouseMove);
      };
      const onMouseMove = moveEvent => {
        // delaying mousemove handler to allow selectionchange handler from LexicalEvents.ts to be executed first
        setTimeout(() => {
          if (!isMouseDownOnEvent(moveEvent) && tableObserver.isSelecting) {
            tableObserver.isSelecting = false;
            editorWindow.removeEventListener('mouseup', onMouseUp);
            editorWindow.removeEventListener('mousemove', onMouseMove);
            return;
          }
          const focusCell = getDOMCellFromTarget(moveEvent.target);
          if (focusCell !== null && (tableObserver.anchorX !== focusCell.x || tableObserver.anchorY !== focusCell.y)) {
            moveEvent.preventDefault();
            tableObserver.setFocusCellForSelection(focusCell);
          }
        }, 0);
      };
      return {
        onMouseMove: onMouseMove,
        onMouseUp: onMouseUp
      };
    };
    tableElement.addEventListener('mousedown', event => {
      setTimeout(() => {
        if (event.button !== 0) {
          return;
        }
        if (!editorWindow) {
          return;
        }
        const anchorCell = getDOMCellFromTarget(event.target);
        if (anchorCell !== null) {
          stopEvent(event);
          tableObserver.setAnchorCellForSelection(anchorCell);
        }
        const {
          onMouseUp,
          onMouseMove
        } = createMouseHandlers();
        tableObserver.isSelecting = true;
        editorWindow.addEventListener('mouseup', onMouseUp);
        editorWindow.addEventListener('mousemove', onMouseMove);
      }, 0);
    });

    // Clear selection when clicking outside of dom.
    const mouseDownCallback = event => {
      if (event.button !== 0) {
        return;
      }
      editor.update(() => {
        const selection = $getSelection$1();
        const target = event.target;
        if ($isTableSelection(selection) && selection.tableKey === tableObserver.tableNodeKey && rootElement.contains(target)) {
          tableObserver.clearHighlight();
        }
      });
    };
    editorWindow.addEventListener('mousedown', mouseDownCallback);
    tableObserver.listenersToRemove.add(() => editorWindow.removeEventListener('mousedown', mouseDownCallback));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_ARROW_DOWN_COMMAND$1, event => $handleArrowKey(editor, event, 'down', tableNode, tableObserver), COMMAND_PRIORITY_HIGH$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_ARROW_UP_COMMAND$1, event => $handleArrowKey(editor, event, 'up', tableNode, tableObserver), COMMAND_PRIORITY_HIGH$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_ARROW_LEFT_COMMAND$1, event => $handleArrowKey(editor, event, 'backward', tableNode, tableObserver), COMMAND_PRIORITY_HIGH$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_ARROW_RIGHT_COMMAND$1, event => $handleArrowKey(editor, event, 'forward', tableNode, tableObserver), COMMAND_PRIORITY_HIGH$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_ESCAPE_COMMAND$1, event => {
      const selection = $getSelection$1();
      if ($isTableSelection(selection)) {
        const focusCellNode = $findMatchingParent$1(selection.focus.getNode(), $isTableCellNode);
        if ($isTableCellNode(focusCellNode)) {
          stopEvent(event);
          focusCellNode.selectEnd();
          return true;
        }
      }
      return false;
    }, COMMAND_PRIORITY_HIGH$1));
    const deleteTextHandler = command => () => {
      const selection = $getSelection$1();
      if (!$isSelectionInTable(selection, tableNode)) {
        return false;
      }
      if ($isTableSelection(selection)) {
        tableObserver.clearText();
        return true;
      } else if ($isRangeSelection$1(selection)) {
        const tableCellNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n));
        if (!$isTableCellNode(tableCellNode)) {
          return false;
        }
        const anchorNode = selection.anchor.getNode();
        const focusNode = selection.focus.getNode();
        const isAnchorInside = tableNode.isParentOf(anchorNode);
        const isFocusInside = tableNode.isParentOf(focusNode);
        const selectionContainsPartialTable = isAnchorInside && !isFocusInside || isFocusInside && !isAnchorInside;
        if (selectionContainsPartialTable) {
          tableObserver.clearText();
          return true;
        }
        const nearestElementNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isElementNode$1(n));
        const topLevelCellElementNode = nearestElementNode && $findMatchingParent$1(nearestElementNode, n => $isElementNode$1(n) && $isTableCellNode(n.getParent()));
        if (!$isElementNode$1(topLevelCellElementNode) || !$isElementNode$1(nearestElementNode)) {
          return false;
        }
        if (command === DELETE_LINE_COMMAND$1 && topLevelCellElementNode.getPreviousSibling() === null) {
          // TODO: Fix Delete Line in Table Cells.
          return true;
        }
      }
      return false;
    };
    [DELETE_WORD_COMMAND$1, DELETE_LINE_COMMAND$1, DELETE_CHARACTER_COMMAND$1].forEach(command => {
      tableObserver.listenersToRemove.add(editor.registerCommand(command, deleteTextHandler(command), COMMAND_PRIORITY_CRITICAL$1));
    });
    const $deleteCellHandler = event => {
      const selection = $getSelection$1();
      if (!$isSelectionInTable(selection, tableNode)) {
        const nodes = selection ? selection.getNodes() : null;
        if (nodes) {
          const table = nodes.find(node => $isTableNode(node) && node.getKey() === tableObserver.tableNodeKey);
          if ($isTableNode(table)) {
            const parentNode = table.getParent();
            if (!parentNode) {
              return false;
            }
            table.remove();
          }
        }
        return false;
      }
      if ($isTableSelection(selection)) {
        if (event) {
          event.preventDefault();
          event.stopPropagation();
        }
        tableObserver.clearText();
        return true;
      } else if ($isRangeSelection$1(selection)) {
        const tableCellNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n));
        if (!$isTableCellNode(tableCellNode)) {
          return false;
        }
      }
      return false;
    };
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_BACKSPACE_COMMAND$1, $deleteCellHandler, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(KEY_DELETE_COMMAND$1, $deleteCellHandler, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(CUT_COMMAND$1, event => {
      const selection = $getSelection$1();
      if (selection) {
        if (!($isTableSelection(selection) || $isRangeSelection$1(selection))) {
          return false;
        }
        // Copying to the clipboard is async so we must capture the data
        // before we delete it
        void copyToClipboard$1(editor, objectKlassEquals$1(event, ClipboardEvent) ? event : null, $getClipboardDataFromSelection$1(selection));
        const intercepted = $deleteCellHandler(event);
        if ($isRangeSelection$1(selection)) {
          selection.removeText();
        }
        return intercepted;
      }
      return false;
    }, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(FORMAT_TEXT_COMMAND$1, payload => {
      const selection = $getSelection$1();
      if (!$isSelectionInTable(selection, tableNode)) {
        return false;
      }
      if ($isTableSelection(selection)) {
        tableObserver.formatCells(payload);
        return true;
      } else if ($isRangeSelection$1(selection)) {
        const tableCellNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n));
        if (!$isTableCellNode(tableCellNode)) {
          return false;
        }
      }
      return false;
    }, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(FORMAT_ELEMENT_COMMAND$1, formatType => {
      const selection = $getSelection$1();
      if (!$isTableSelection(selection) || !$isSelectionInTable(selection, tableNode)) {
        return false;
      }
      const anchorNode = selection.anchor.getNode();
      const focusNode = selection.focus.getNode();
      if (!$isTableCellNode(anchorNode) || !$isTableCellNode(focusNode)) {
        return false;
      }
      const [tableMap, anchorCell, focusCell] = $computeTableMap(tableNode, anchorNode, focusNode);
      const maxRow = Math.max(anchorCell.startRow, focusCell.startRow);
      const maxColumn = Math.max(anchorCell.startColumn, focusCell.startColumn);
      const minRow = Math.min(anchorCell.startRow, focusCell.startRow);
      const minColumn = Math.min(anchorCell.startColumn, focusCell.startColumn);
      for (let i = minRow; i <= maxRow; i++) {
        for (let j = minColumn; j <= maxColumn; j++) {
          const cell = tableMap[i][j].cell;
          cell.setFormat(formatType);
          const cellChildren = cell.getChildren();
          for (let k = 0; k < cellChildren.length; k++) {
            const child = cellChildren[k];
            if ($isElementNode$1(child) && !child.isInline()) {
              child.setFormat(formatType);
            }
          }
        }
      }
      return true;
    }, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(CONTROLLED_TEXT_INSERTION_COMMAND$1, payload => {
      const selection = $getSelection$1();
      if (!$isSelectionInTable(selection, tableNode)) {
        return false;
      }
      if ($isTableSelection(selection)) {
        tableObserver.clearHighlight();
        return false;
      } else if ($isRangeSelection$1(selection)) {
        const tableCellNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n));
        if (!$isTableCellNode(tableCellNode)) {
          return false;
        }
        if (typeof payload === 'string') {
          const edgePosition = $getTableEdgeCursorPosition(editor, selection, tableNode);
          if (edgePosition) {
            $insertParagraphAtTableEdge(edgePosition, tableNode, [$createTextNode$1(payload)]);
            return true;
          }
        }
      }
      return false;
    }, COMMAND_PRIORITY_CRITICAL$1));
    if (hasTabHandler) {
      tableObserver.listenersToRemove.add(editor.registerCommand(KEY_TAB_COMMAND$1, event => {
        const selection = $getSelection$1();
        if (!$isRangeSelection$1(selection) || !selection.isCollapsed() || !$isSelectionInTable(selection, tableNode)) {
          return false;
        }
        const tableCellNode = $findCellNode(selection.anchor.getNode());
        if (tableCellNode === null) {
          return false;
        }
        stopEvent(event);
        const currentCords = tableNode.getCordsFromCellNode(tableCellNode, tableObserver.table);
        selectTableNodeInDirection(tableObserver, tableNode, currentCords.x, currentCords.y, !event.shiftKey ? 'forward' : 'backward');
        return true;
      }, COMMAND_PRIORITY_CRITICAL$1));
    }
    tableObserver.listenersToRemove.add(editor.registerCommand(FOCUS_COMMAND$1, payload => {
      return tableNode.isSelected();
    }, COMMAND_PRIORITY_HIGH$1));
    function getObserverCellFromCellNode(tableCellNode) {
      const currentCords = tableNode.getCordsFromCellNode(tableCellNode, tableObserver.table);
      return tableNode.getDOMCellFromCordsOrThrow(currentCords.x, currentCords.y, tableObserver.table);
    }
    tableObserver.listenersToRemove.add(editor.registerCommand(SELECTION_INSERT_CLIPBOARD_NODES_COMMAND$1, selectionPayload => {
      const {
        nodes,
        selection
      } = selectionPayload;
      const anchorAndFocus = selection.getStartEndPoints();
      const isTableSelection = $isTableSelection(selection);
      const isRangeSelection = $isRangeSelection$1(selection);
      const isSelectionInsideOfGrid = isRangeSelection && $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n)) !== null && $findMatchingParent$1(selection.focus.getNode(), n => $isTableCellNode(n)) !== null || isTableSelection;
      if (nodes.length !== 1 || !$isTableNode(nodes[0]) || !isSelectionInsideOfGrid || anchorAndFocus === null) {
        return false;
      }
      const [anchor] = anchorAndFocus;
      const newGrid = nodes[0];
      const newGridRows = newGrid.getChildren();
      const newColumnCount = newGrid.getFirstChildOrThrow().getChildrenSize();
      const newRowCount = newGrid.getChildrenSize();
      const gridCellNode = $findMatchingParent$1(anchor.getNode(), n => $isTableCellNode(n));
      const gridRowNode = gridCellNode && $findMatchingParent$1(gridCellNode, n => $isTableRowNode(n));
      const gridNode = gridRowNode && $findMatchingParent$1(gridRowNode, n => $isTableNode(n));
      if (!$isTableCellNode(gridCellNode) || !$isTableRowNode(gridRowNode) || !$isTableNode(gridNode)) {
        return false;
      }
      const startY = gridRowNode.getIndexWithinParent();
      const stopY = Math.min(gridNode.getChildrenSize() - 1, startY + newRowCount - 1);
      const startX = gridCellNode.getIndexWithinParent();
      const stopX = Math.min(gridRowNode.getChildrenSize() - 1, startX + newColumnCount - 1);
      const fromX = Math.min(startX, stopX);
      const fromY = Math.min(startY, stopY);
      const toX = Math.max(startX, stopX);
      const toY = Math.max(startY, stopY);
      const gridRowNodes = gridNode.getChildren();
      let newRowIdx = 0;
      for (let r = fromY; r <= toY; r++) {
        const currentGridRowNode = gridRowNodes[r];
        if (!$isTableRowNode(currentGridRowNode)) {
          return false;
        }
        const newGridRowNode = newGridRows[newRowIdx];
        if (!$isTableRowNode(newGridRowNode)) {
          return false;
        }
        const gridCellNodes = currentGridRowNode.getChildren();
        const newGridCellNodes = newGridRowNode.getChildren();
        let newColumnIdx = 0;
        for (let c = fromX; c <= toX; c++) {
          const currentGridCellNode = gridCellNodes[c];
          if (!$isTableCellNode(currentGridCellNode)) {
            return false;
          }
          const newGridCellNode = newGridCellNodes[newColumnIdx];
          if (!$isTableCellNode(newGridCellNode)) {
            return false;
          }
          const originalChildren = currentGridCellNode.getChildren();
          newGridCellNode.getChildren().forEach(child => {
            if ($isTextNode$1(child)) {
              const paragraphNode = $createParagraphNode$1();
              paragraphNode.append(child);
              currentGridCellNode.append(child);
            } else {
              currentGridCellNode.append(child);
            }
          });
          originalChildren.forEach(n => n.remove());
          newColumnIdx++;
        }
        newRowIdx++;
      }
      return true;
    }, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(SELECTION_CHANGE_COMMAND$1, () => {
      const selection = $getSelection$1();
      const prevSelection = $getPreviousSelection$1();
      if ($isRangeSelection$1(selection)) {
        const {
          anchor,
          focus
        } = selection;
        const anchorNode = anchor.getNode();
        const focusNode = focus.getNode();
        // Using explicit comparison with table node to ensure it's not a nested table
        // as in that case we'll leave selection resolving to that table
        const anchorCellNode = $findCellNode(anchorNode);
        const focusCellNode = $findCellNode(focusNode);
        const isAnchorInside = !!(anchorCellNode && tableNode.is($findTableNode(anchorCellNode)));
        const isFocusInside = !!(focusCellNode && tableNode.is($findTableNode(focusCellNode)));
        const isPartialyWithinTable = isAnchorInside !== isFocusInside;
        const isWithinTable = isAnchorInside && isFocusInside;
        const isBackward = selection.isBackward();
        if (isPartialyWithinTable) {
          const newSelection = selection.clone();
          if (isFocusInside) {
            const [tableMap] = $computeTableMap(tableNode, focusCellNode, focusCellNode);
            const firstCell = tableMap[0][0].cell;
            const lastCell = tableMap[tableMap.length - 1].at(-1).cell;
            newSelection.focus.set(isBackward ? firstCell.getKey() : lastCell.getKey(), isBackward ? firstCell.getChildrenSize() : lastCell.getChildrenSize(), 'element');
          }
          $setSelection$1(newSelection);
          $addHighlightStyleToTable(editor, tableObserver);
        } else if (isWithinTable) {
          // Handle case when selection spans across multiple cells but still
          // has range selection, then we convert it into grid selection
          if (!anchorCellNode.is(focusCellNode)) {
            tableObserver.setAnchorCellForSelection(getObserverCellFromCellNode(anchorCellNode));
            tableObserver.setFocusCellForSelection(getObserverCellFromCellNode(focusCellNode), true);
            if (!tableObserver.isSelecting) {
              setTimeout(() => {
                const {
                  onMouseUp,
                  onMouseMove
                } = createMouseHandlers();
                tableObserver.isSelecting = true;
                editorWindow.addEventListener('mouseup', onMouseUp);
                editorWindow.addEventListener('mousemove', onMouseMove);
              }, 0);
            }
          }
        }
      } else if (selection && $isTableSelection(selection) && selection.is(prevSelection) && selection.tableKey === tableNode.getKey()) {
        // if selection goes outside of the table we need to change it to Range selection
        const domSelection = getDOMSelection$2(editor._window);
        if (domSelection && domSelection.anchorNode && domSelection.focusNode) {
          const focusNode = $getNearestNodeFromDOMNode$1(domSelection.focusNode);
          const isFocusOutside = focusNode && !tableNode.is($findTableNode(focusNode));
          const anchorNode = $getNearestNodeFromDOMNode$1(domSelection.anchorNode);
          const isAnchorInside = anchorNode && tableNode.is($findTableNode(anchorNode));
          if (isFocusOutside && isAnchorInside && domSelection.rangeCount > 0) {
            const newSelection = $createRangeSelectionFromDom$1(domSelection, editor);
            if (newSelection) {
              newSelection.anchor.set(tableNode.getKey(), selection.isBackward() ? tableNode.getChildrenSize() : 0, 'element');
              domSelection.removeAllRanges();
              $setSelection$1(newSelection);
            }
          }
        }
      }
      if (selection && !selection.is(prevSelection) && ($isTableSelection(selection) || $isTableSelection(prevSelection)) && tableObserver.tableSelection && !tableObserver.tableSelection.is(prevSelection)) {
        if ($isTableSelection(selection) && selection.tableKey === tableObserver.tableNodeKey) {
          tableObserver.updateTableTableSelection(selection);
        } else if (!$isTableSelection(selection) && $isTableSelection(prevSelection) && prevSelection.tableKey === tableObserver.tableNodeKey) {
          tableObserver.updateTableTableSelection(null);
        }
        return false;
      }
      if (tableObserver.hasHijackedSelectionStyles && !tableNode.isSelected()) {
        $removeHighlightStyleToTable(editor, tableObserver);
      } else if (!tableObserver.hasHijackedSelectionStyles && tableNode.isSelected()) {
        $addHighlightStyleToTable(editor, tableObserver);
      }
      return false;
    }, COMMAND_PRIORITY_CRITICAL$1));
    tableObserver.listenersToRemove.add(editor.registerCommand(INSERT_PARAGRAPH_COMMAND$1, () => {
      const selection = $getSelection$1();
      if (!$isRangeSelection$1(selection) || !selection.isCollapsed() || !$isSelectionInTable(selection, tableNode)) {
        return false;
      }
      const edgePosition = $getTableEdgeCursorPosition(editor, selection, tableNode);
      if (edgePosition) {
        $insertParagraphAtTableEdge(edgePosition, tableNode);
        return true;
      }
      return false;
    }, COMMAND_PRIORITY_CRITICAL$1));
    return tableObserver;
  }
  function attachTableObserverToTableElement(tableElement, tableObserver) {
    tableElement[LEXICAL_ELEMENT_KEY] = tableObserver;
  }
  function getTableObserverFromTableElement(tableElement) {
    return tableElement[LEXICAL_ELEMENT_KEY];
  }
  function getDOMCellFromTarget(node) {
    let currentNode = node;
    while (currentNode != null) {
      const nodeName = currentNode.nodeName;
      if (nodeName === 'TD' || nodeName === 'TH') {
        // @ts-expect-error: internal field
        const cell = currentNode._cell;
        if (cell === undefined) {
          return null;
        }
        return cell;
      }
      currentNode = currentNode.parentNode;
    }
    return null;
  }
  function getTable(tableElement) {
    const domRows = [];
    const grid = {
      columns: 0,
      domRows,
      rows: 0
    };
    let currentNode = tableElement.firstChild;
    let x = 0;
    let y = 0;
    domRows.length = 0;
    while (currentNode != null) {
      const nodeMame = currentNode.nodeName;
      if (nodeMame === 'TD' || nodeMame === 'TH') {
        const elem = currentNode;
        const cell = {
          elem,
          hasBackgroundColor: elem.style.backgroundColor !== '',
          highlighted: false,
          x,
          y
        };

        // @ts-expect-error: internal field
        currentNode._cell = cell;
        let row = domRows[y];
        if (row === undefined) {
          row = domRows[y] = [];
        }
        row[x] = cell;
      } else {
        const child = currentNode.firstChild;
        if (child != null) {
          currentNode = child;
          continue;
        }
      }
      const sibling = currentNode.nextSibling;
      if (sibling != null) {
        x++;
        currentNode = sibling;
        continue;
      }
      const parent = currentNode.parentNode;
      if (parent != null) {
        const parentSibling = parent.nextSibling;
        if (parentSibling == null) {
          break;
        }
        y++;
        x = 0;
        currentNode = parentSibling;
      }
    }
    grid.columns = x + 1;
    grid.rows = y + 1;
    return grid;
  }
  function $updateDOMForSelection(editor, table, selection) {
    const selectedCellNodes = new Set(selection ? selection.getNodes() : []);
    $forEachTableCell(table, (cell, lexicalNode) => {
      const elem = cell.elem;
      if (selectedCellNodes.has(lexicalNode)) {
        cell.highlighted = true;
        $addHighlightToDOM(editor, cell);
      } else {
        cell.highlighted = false;
        $removeHighlightFromDOM(editor, cell);
        if (!elem.getAttribute('style')) {
          elem.removeAttribute('style');
        }
      }
    });
  }
  function $forEachTableCell(grid, cb) {
    const {
      domRows
    } = grid;
    for (let y = 0; y < domRows.length; y++) {
      const row = domRows[y];
      if (!row) {
        continue;
      }
      for (let x = 0; x < row.length; x++) {
        const cell = row[x];
        if (!cell) {
          continue;
        }
        const lexicalNode = $getNearestNodeFromDOMNode$1(cell.elem);
        if (lexicalNode !== null) {
          cb(cell, lexicalNode, {
            x,
            y
          });
        }
      }
    }
  }
  function $addHighlightStyleToTable(editor, tableSelection) {
    tableSelection.disableHighlightStyle();
    $forEachTableCell(tableSelection.table, cell => {
      cell.highlighted = true;
      $addHighlightToDOM(editor, cell);
    });
  }
  function $removeHighlightStyleToTable(editor, tableObserver) {
    tableObserver.enableHighlightStyle();
    $forEachTableCell(tableObserver.table, cell => {
      const elem = cell.elem;
      cell.highlighted = false;
      $removeHighlightFromDOM(editor, cell);
      if (!elem.getAttribute('style')) {
        elem.removeAttribute('style');
      }
    });
  }
  const selectTableNodeInDirection = (tableObserver, tableNode, x, y, direction) => {
    const isForward = direction === 'forward';
    switch (direction) {
      case 'backward':
      case 'forward':
        if (x !== (isForward ? tableObserver.table.columns - 1 : 0)) {
          selectTableCellNode(tableNode.getCellNodeFromCordsOrThrow(x + (isForward ? 1 : -1), y, tableObserver.table), isForward);
        } else {
          if (y !== (isForward ? tableObserver.table.rows - 1 : 0)) {
            selectTableCellNode(tableNode.getCellNodeFromCordsOrThrow(isForward ? 0 : tableObserver.table.columns - 1, y + (isForward ? 1 : -1), tableObserver.table), isForward);
          } else if (!isForward) {
            tableNode.selectPrevious();
          } else {
            tableNode.selectNext();
          }
        }
        return true;
      case 'up':
        if (y !== 0) {
          selectTableCellNode(tableNode.getCellNodeFromCordsOrThrow(x, y - 1, tableObserver.table), false);
        } else {
          tableNode.selectPrevious();
        }
        return true;
      case 'down':
        if (y !== tableObserver.table.rows - 1) {
          selectTableCellNode(tableNode.getCellNodeFromCordsOrThrow(x, y + 1, tableObserver.table), true);
        } else {
          tableNode.selectNext();
        }
        return true;
      default:
        return false;
    }
  };
  const adjustFocusNodeInDirection = (tableObserver, tableNode, x, y, direction) => {
    const isForward = direction === 'forward';
    switch (direction) {
      case 'backward':
      case 'forward':
        if (x !== (isForward ? tableObserver.table.columns - 1 : 0)) {
          tableObserver.setFocusCellForSelection(tableNode.getDOMCellFromCordsOrThrow(x + (isForward ? 1 : -1), y, tableObserver.table));
        }
        return true;
      case 'up':
        if (y !== 0) {
          tableObserver.setFocusCellForSelection(tableNode.getDOMCellFromCordsOrThrow(x, y - 1, tableObserver.table));
          return true;
        } else {
          return false;
        }
      case 'down':
        if (y !== tableObserver.table.rows - 1) {
          tableObserver.setFocusCellForSelection(tableNode.getDOMCellFromCordsOrThrow(x, y + 1, tableObserver.table));
          return true;
        } else {
          return false;
        }
      default:
        return false;
    }
  };
  function $isSelectionInTable(selection, tableNode) {
    if ($isRangeSelection$1(selection) || $isTableSelection(selection)) {
      const isAnchorInside = tableNode.isParentOf(selection.anchor.getNode());
      const isFocusInside = tableNode.isParentOf(selection.focus.getNode());
      return isAnchorInside && isFocusInside;
    }
    return false;
  }
  function selectTableCellNode(tableCell, fromStart) {
    if (fromStart) {
      tableCell.selectStart();
    } else {
      tableCell.selectEnd();
    }
  }
  const BROWSER_BLUE_RGB = '172,206,247';
  function $addHighlightToDOM(editor, cell) {
    const element = cell.elem;
    const node = $getNearestNodeFromDOMNode$1(element);
    if (!$isTableCellNode(node)) {
      throw Error(`Expected to find LexicalNode from Table Cell DOMNode`);
    }
    const backgroundColor = node.getBackgroundColor();
    if (backgroundColor === null) {
      element.style.setProperty('background-color', `rgb(${BROWSER_BLUE_RGB})`);
    } else {
      element.style.setProperty('background-image', `linear-gradient(to right, rgba(${BROWSER_BLUE_RGB},0.85), rgba(${BROWSER_BLUE_RGB},0.85))`);
    }
    element.style.setProperty('caret-color', 'transparent');
  }
  function $removeHighlightFromDOM(editor, cell) {
    const element = cell.elem;
    const node = $getNearestNodeFromDOMNode$1(element);
    if (!$isTableCellNode(node)) {
      throw Error(`Expected to find LexicalNode from Table Cell DOMNode`);
    }
    const backgroundColor = node.getBackgroundColor();
    if (backgroundColor === null) {
      element.style.removeProperty('background-color');
    }
    element.style.removeProperty('background-image');
    element.style.removeProperty('caret-color');
  }
  function $findCellNode(node) {
    const cellNode = $findMatchingParent$1(node, $isTableCellNode);
    return $isTableCellNode(cellNode) ? cellNode : null;
  }
  function $findTableNode(node) {
    const tableNode = $findMatchingParent$1(node, $isTableNode);
    return $isTableNode(tableNode) ? tableNode : null;
  }
  function $handleArrowKey(editor, event, direction, tableNode, tableObserver) {
    if ((direction === 'up' || direction === 'down') && isTypeaheadMenuInView(editor)) {
      return false;
    }
    const selection = $getSelection$1();
    if (!$isSelectionInTable(selection, tableNode)) {
      if ($isRangeSelection$1(selection)) {
        if (selection.isCollapsed() && direction === 'backward') {
          const anchorType = selection.anchor.type;
          const anchorOffset = selection.anchor.offset;
          if (anchorType !== 'element' && !(anchorType === 'text' && anchorOffset === 0)) {
            return false;
          }
          const anchorNode = selection.anchor.getNode();
          if (!anchorNode) {
            return false;
          }
          const parentNode = $findMatchingParent$1(anchorNode, n => $isElementNode$1(n) && !n.isInline());
          if (!parentNode) {
            return false;
          }
          const siblingNode = parentNode.getPreviousSibling();
          if (!siblingNode || !$isTableNode(siblingNode)) {
            return false;
          }
          stopEvent(event);
          siblingNode.selectEnd();
          return true;
        } else if (event.shiftKey && (direction === 'up' || direction === 'down')) {
          const focusNode = selection.focus.getNode();
          if ($isRootOrShadowRoot$1(focusNode)) {
            const selectedNode = selection.getNodes()[0];
            if (selectedNode) {
              const tableCellNode = $findMatchingParent$1(selectedNode, $isTableCellNode);
              if (tableCellNode && tableNode.isParentOf(tableCellNode)) {
                const firstDescendant = tableNode.getFirstDescendant();
                const lastDescendant = tableNode.getLastDescendant();
                if (!firstDescendant || !lastDescendant) {
                  return false;
                }
                const [firstCellNode] = $getNodeTriplet(firstDescendant);
                const [lastCellNode] = $getNodeTriplet(lastDescendant);
                const firstCellCoords = tableNode.getCordsFromCellNode(firstCellNode, tableObserver.table);
                const lastCellCoords = tableNode.getCordsFromCellNode(lastCellNode, tableObserver.table);
                const firstCellDOM = tableNode.getDOMCellFromCordsOrThrow(firstCellCoords.x, firstCellCoords.y, tableObserver.table);
                const lastCellDOM = tableNode.getDOMCellFromCordsOrThrow(lastCellCoords.x, lastCellCoords.y, tableObserver.table);
                tableObserver.setAnchorCellForSelection(firstCellDOM);
                tableObserver.setFocusCellForSelection(lastCellDOM, true);
                return true;
              }
            }
            return false;
          } else {
            const focusParentNode = $findMatchingParent$1(focusNode, n => $isElementNode$1(n) && !n.isInline());
            if (!focusParentNode) {
              return false;
            }
            const sibling = direction === 'down' ? focusParentNode.getNextSibling() : focusParentNode.getPreviousSibling();
            if ($isTableNode(sibling) && tableObserver.tableNodeKey === sibling.getKey()) {
              const firstDescendant = sibling.getFirstDescendant();
              const lastDescendant = sibling.getLastDescendant();
              if (!firstDescendant || !lastDescendant) {
                return false;
              }
              const [firstCellNode] = $getNodeTriplet(firstDescendant);
              const [lastCellNode] = $getNodeTriplet(lastDescendant);
              const newSelection = selection.clone();
              newSelection.focus.set((direction === 'up' ? firstCellNode : lastCellNode).getKey(), direction === 'up' ? 0 : lastCellNode.getChildrenSize(), 'element');
              $setSelection$1(newSelection);
              return true;
            }
          }
        }
      }
      return false;
    }
    if ($isRangeSelection$1(selection) && selection.isCollapsed()) {
      const {
        anchor,
        focus
      } = selection;
      const anchorCellNode = $findMatchingParent$1(anchor.getNode(), $isTableCellNode);
      const focusCellNode = $findMatchingParent$1(focus.getNode(), $isTableCellNode);
      if (!$isTableCellNode(anchorCellNode) || !anchorCellNode.is(focusCellNode)) {
        return false;
      }
      const anchorCellTable = $findTableNode(anchorCellNode);
      if (anchorCellTable !== tableNode && anchorCellTable != null) {
        const anchorCellTableElement = editor.getElementByKey(anchorCellTable.getKey());
        if (anchorCellTableElement != null) {
          tableObserver.table = getTable(anchorCellTableElement);
          return $handleArrowKey(editor, event, direction, anchorCellTable, tableObserver);
        }
      }
      if (direction === 'backward' || direction === 'forward') {
        const anchorType = anchor.type;
        const anchorOffset = anchor.offset;
        const anchorNode = anchor.getNode();
        if (!anchorNode) {
          return false;
        }
        const selectedNodes = selection.getNodes();
        if (selectedNodes.length === 1 && $isDecoratorNode$1(selectedNodes[0])) {
          return false;
        }
        if (isExitingTableAnchor(anchorType, anchorOffset, anchorNode, direction)) {
          return $handleTableExit(event, anchorNode, tableNode, direction);
        }
        return false;
      }
      const anchorCellDom = editor.getElementByKey(anchorCellNode.__key);
      const anchorDOM = editor.getElementByKey(anchor.key);
      if (anchorDOM == null || anchorCellDom == null) {
        return false;
      }
      let edgeSelectionRect;
      if (anchor.type === 'element') {
        edgeSelectionRect = anchorDOM.getBoundingClientRect();
      } else {
        const domSelection = window.getSelection();
        if (domSelection === null || domSelection.rangeCount === 0) {
          return false;
        }
        const range = domSelection.getRangeAt(0);
        edgeSelectionRect = range.getBoundingClientRect();
      }
      const edgeChild = direction === 'up' ? anchorCellNode.getFirstChild() : anchorCellNode.getLastChild();
      if (edgeChild == null) {
        return false;
      }
      const edgeChildDOM = editor.getElementByKey(edgeChild.__key);
      if (edgeChildDOM == null) {
        return false;
      }
      const edgeRect = edgeChildDOM.getBoundingClientRect();
      const isExiting = direction === 'up' ? edgeRect.top > edgeSelectionRect.top - edgeSelectionRect.height : edgeSelectionRect.bottom + edgeSelectionRect.height > edgeRect.bottom;
      if (isExiting) {
        stopEvent(event);
        const cords = tableNode.getCordsFromCellNode(anchorCellNode, tableObserver.table);
        if (event.shiftKey) {
          const cell = tableNode.getDOMCellFromCordsOrThrow(cords.x, cords.y, tableObserver.table);
          tableObserver.setAnchorCellForSelection(cell);
          tableObserver.setFocusCellForSelection(cell, true);
        } else {
          return selectTableNodeInDirection(tableObserver, tableNode, cords.x, cords.y, direction);
        }
        return true;
      }
    } else if ($isTableSelection(selection)) {
      const {
        anchor,
        focus
      } = selection;
      const anchorCellNode = $findMatchingParent$1(anchor.getNode(), $isTableCellNode);
      const focusCellNode = $findMatchingParent$1(focus.getNode(), $isTableCellNode);
      const [tableNodeFromSelection] = selection.getNodes();
      const tableElement = editor.getElementByKey(tableNodeFromSelection.getKey());
      if (!$isTableCellNode(anchorCellNode) || !$isTableCellNode(focusCellNode) || !$isTableNode(tableNodeFromSelection) || tableElement == null) {
        return false;
      }
      tableObserver.updateTableTableSelection(selection);
      const grid = getTable(tableElement);
      const cordsAnchor = tableNode.getCordsFromCellNode(anchorCellNode, grid);
      const anchorCell = tableNode.getDOMCellFromCordsOrThrow(cordsAnchor.x, cordsAnchor.y, grid);
      tableObserver.setAnchorCellForSelection(anchorCell);
      stopEvent(event);
      if (event.shiftKey) {
        const cords = tableNode.getCordsFromCellNode(focusCellNode, grid);
        return adjustFocusNodeInDirection(tableObserver, tableNodeFromSelection, cords.x, cords.y, direction);
      } else {
        focusCellNode.selectEnd();
      }
      return true;
    }
    return false;
  }
  function stopEvent(event) {
    event.preventDefault();
    event.stopImmediatePropagation();
    event.stopPropagation();
  }
  function isTypeaheadMenuInView(editor) {
    // There is no inbuilt way to check if the component picker is in view
    // but we can check if the root DOM element has the aria-controls attribute "typeahead-menu".
    const root = editor.getRootElement();
    if (!root) {
      return false;
    }
    return root.hasAttribute('aria-controls') && root.getAttribute('aria-controls') === 'typeahead-menu';
  }
  function isExitingTableAnchor(type, offset, anchorNode, direction) {
    return isExitingTableElementAnchor(type, anchorNode, direction) || $isExitingTableTextAnchor(type, offset, anchorNode, direction);
  }
  function isExitingTableElementAnchor(type, anchorNode, direction) {
    return type === 'element' && (direction === 'backward' ? anchorNode.getPreviousSibling() === null : anchorNode.getNextSibling() === null);
  }
  function $isExitingTableTextAnchor(type, offset, anchorNode, direction) {
    const parentNode = $findMatchingParent$1(anchorNode, n => $isElementNode$1(n) && !n.isInline());
    if (!parentNode) {
      return false;
    }
    const hasValidOffset = direction === 'backward' ? offset === 0 : offset === anchorNode.getTextContentSize();
    return type === 'text' && hasValidOffset && (direction === 'backward' ? parentNode.getPreviousSibling() === null : parentNode.getNextSibling() === null);
  }
  function $handleTableExit(event, anchorNode, tableNode, direction) {
    const anchorCellNode = $findMatchingParent$1(anchorNode, $isTableCellNode);
    if (!$isTableCellNode(anchorCellNode)) {
      return false;
    }
    const [tableMap, cellValue] = $computeTableMap(tableNode, anchorCellNode, anchorCellNode);
    if (!isExitingCell(tableMap, cellValue, direction)) {
      return false;
    }
    const toNode = $getExitingToNode(anchorNode, direction, tableNode);
    if (!toNode || $isTableNode(toNode)) {
      return false;
    }
    stopEvent(event);
    if (direction === 'backward') {
      toNode.selectEnd();
    } else {
      toNode.selectStart();
    }
    return true;
  }
  function isExitingCell(tableMap, cellValue, direction) {
    const firstCell = tableMap[0][0];
    const lastCell = tableMap[tableMap.length - 1][tableMap[0].length - 1];
    const {
      startColumn,
      startRow
    } = cellValue;
    return direction === 'backward' ? startColumn === firstCell.startColumn && startRow === firstCell.startRow : startColumn === lastCell.startColumn && startRow === lastCell.startRow;
  }
  function $getExitingToNode(anchorNode, direction, tableNode) {
    const parentNode = $findMatchingParent$1(anchorNode, n => $isElementNode$1(n) && !n.isInline());
    if (!parentNode) {
      return undefined;
    }
    const anchorSibling = direction === 'backward' ? parentNode.getPreviousSibling() : parentNode.getNextSibling();
    return anchorSibling && $isTableNode(anchorSibling) ? anchorSibling : direction === 'backward' ? tableNode.getPreviousSibling() : tableNode.getNextSibling();
  }
  function $insertParagraphAtTableEdge(edgePosition, tableNode, children) {
    const paragraphNode = $createParagraphNode$1();
    if (edgePosition === 'first') {
      tableNode.insertBefore(paragraphNode);
    } else {
      tableNode.insertAfter(paragraphNode);
    }
    paragraphNode.append(...(children || []));
    paragraphNode.selectEnd();
  }
  function $getTableEdgeCursorPosition(editor, selection, tableNode) {
    const tableNodeParent = tableNode.getParent();
    if (!tableNodeParent) {
      return undefined;
    }
    const tableNodeParentDOM = editor.getElementByKey(tableNodeParent.getKey());
    if (!tableNodeParentDOM) {
      return undefined;
    }

    // TODO: Add support for nested tables
    const domSelection = window.getSelection();
    if (!domSelection || domSelection.anchorNode !== tableNodeParentDOM) {
      return undefined;
    }
    const anchorCellNode = $findMatchingParent$1(selection.anchor.getNode(), n => $isTableCellNode(n));
    if (!anchorCellNode) {
      return undefined;
    }
    const parentTable = $findMatchingParent$1(anchorCellNode, n => $isTableNode(n));
    if (!$isTableNode(parentTable) || !parentTable.is(tableNode)) {
      return undefined;
    }
    const [tableMap, cellValue] = $computeTableMap(tableNode, anchorCellNode, anchorCellNode);
    const firstCell = tableMap[0][0];
    const lastCell = tableMap[tableMap.length - 1][tableMap[0].length - 1];
    const {
      startRow,
      startColumn
    } = cellValue;
    const isAtFirstCell = startRow === firstCell.startRow && startColumn === firstCell.startColumn;
    const isAtLastCell = startRow === lastCell.startRow && startColumn === lastCell.startColumn;
    if (isAtFirstCell) {
      return 'first';
    } else if (isAtLastCell) {
      return 'last';
    } else {
      return undefined;
    }
  }

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */

  /** @noInheritDoc */
  class TableNode extends ElementNode$1 {
    static getType() {
      return 'table';
    }
    static clone(node) {
      return new TableNode(node.__key);
    }
    static importDOM() {
      return {
        table: _node => ({
          conversion: $convertTableElement,
          priority: 1
        })
      };
    }
    static importJSON(_serializedNode) {
      return $createTableNode();
    }
    constructor(key) {
      super(key);
    }
    exportJSON() {
      return {
        ...super.exportJSON(),
        type: 'table',
        version: 1
      };
    }
    createDOM(config, editor) {
      const tableElement = document.createElement('table');
      addClassNamesToElement$1(tableElement, config.theme.table);
      return tableElement;
    }
    updateDOM() {
      return false;
    }
    exportDOM(editor) {
      return {
        ...super.exportDOM(editor),
        after: tableElement => {
          if (tableElement) {
            const newElement = tableElement.cloneNode();
            const colGroup = document.createElement('colgroup');
            const tBody = document.createElement('tbody');
            if (isHTMLElement$2(tableElement)) {
              tBody.append(...tableElement.children);
            }
            const firstRow = this.getFirstChildOrThrow();
            if (!$isTableRowNode(firstRow)) {
              throw new Error('Expected to find row node.');
            }
            const colCount = firstRow.getChildrenSize();
            for (let i = 0; i < colCount; i++) {
              const col = document.createElement('col');
              colGroup.append(col);
            }
            newElement.replaceChildren(colGroup, tBody);
            return newElement;
          }
        }
      };
    }
    canBeEmpty() {
      return false;
    }
    isShadowRoot() {
      return true;
    }
    getCordsFromCellNode(tableCellNode, table) {
      const {
        rows,
        domRows
      } = table;
      for (let y = 0; y < rows; y++) {
        const row = domRows[y];
        if (row == null) {
          continue;
        }
        const x = row.findIndex(cell => {
          if (!cell) {
            return;
          }
          const {
            elem
          } = cell;
          const cellNode = $getNearestNodeFromDOMNode$1(elem);
          return cellNode === tableCellNode;
        });
        if (x !== -1) {
          return {
            x,
            y
          };
        }
      }
      throw new Error('Cell not found in table.');
    }
    getDOMCellFromCords(x, y, table) {
      const {
        domRows
      } = table;
      const row = domRows[y];
      if (row == null) {
        return null;
      }
      const index = x < row.length ? x : row.length - 1;
      const cell = row[index];
      if (cell == null) {
        return null;
      }
      return cell;
    }
    getDOMCellFromCordsOrThrow(x, y, table) {
      const cell = this.getDOMCellFromCords(x, y, table);
      if (!cell) {
        throw new Error('Cell not found at cords.');
      }
      return cell;
    }
    getCellNodeFromCords(x, y, table) {
      const cell = this.getDOMCellFromCords(x, y, table);
      if (cell == null) {
        return null;
      }
      const node = $getNearestNodeFromDOMNode$1(cell.elem);
      if ($isTableCellNode(node)) {
        return node;
      }
      return null;
    }
    getCellNodeFromCordsOrThrow(x, y, table) {
      const node = this.getCellNodeFromCords(x, y, table);
      if (!node) {
        throw new Error('Node at cords not TableCellNode.');
      }
      return node;
    }
    canSelectBefore() {
      return true;
    }
    canIndent() {
      return false;
    }
  }
  function $getElementForTableNode(editor, tableNode) {
    const tableElement = editor.getElementByKey(tableNode.getKey());
    if (tableElement == null) {
      throw new Error('Table Element Not Found');
    }
    return getTable(tableElement);
  }
  function $convertTableElement(_domNode) {
    return {
      node: $createTableNode()
    };
  }
  function $createTableNode() {
    return $applyNodeReplacement$1(new TableNode());
  }
  function $isTableNode(node) {
    return node instanceof TableNode;
  }

  var modDev$a = /*#__PURE__*/Object.freeze({
    $computeTableMap: $computeTableMap,
    $computeTableMapSkipCellCheck: $computeTableMapSkipCellCheck,
    $createTableCellNode: $createTableCellNode,
    $createTableNode: $createTableNode,
    $createTableNodeWithDimensions: $createTableNodeWithDimensions,
    $createTableRowNode: $createTableRowNode,
    $createTableSelection: $createTableSelection,
    $deleteTableColumn: $deleteTableColumn,
    $deleteTableColumn__EXPERIMENTAL: $deleteTableColumn__EXPERIMENTAL,
    $deleteTableRow__EXPERIMENTAL: $deleteTableRow__EXPERIMENTAL,
    $findCellNode: $findCellNode,
    $findTableNode: $findTableNode,
    $getElementForTableNode: $getElementForTableNode,
    $getNodeTriplet: $getNodeTriplet,
    $getTableCellNodeFromLexicalNode: $getTableCellNodeFromLexicalNode,
    $getTableCellNodeRect: $getTableCellNodeRect,
    $getTableColumnIndexFromTableCellNode: $getTableColumnIndexFromTableCellNode,
    $getTableNodeFromLexicalNodeOrThrow: $getTableNodeFromLexicalNodeOrThrow,
    $getTableRowIndexFromTableCellNode: $getTableRowIndexFromTableCellNode,
    $getTableRowNodeFromTableCellNodeOrThrow: $getTableRowNodeFromTableCellNodeOrThrow,
    $insertTableColumn: $insertTableColumn,
    $insertTableColumn__EXPERIMENTAL: $insertTableColumn__EXPERIMENTAL,
    $insertTableRow: $insertTableRow,
    $insertTableRow__EXPERIMENTAL: $insertTableRow__EXPERIMENTAL,
    $isTableCellNode: $isTableCellNode,
    $isTableNode: $isTableNode,
    $isTableRowNode: $isTableRowNode,
    $isTableSelection: $isTableSelection,
    $removeTableRowAtIndex: $removeTableRowAtIndex,
    $unmergeCell: $unmergeCell,
    INSERT_TABLE_COMMAND: INSERT_TABLE_COMMAND,
    TableCellHeaderStates: TableCellHeaderStates,
    TableCellNode: TableCellNode,
    TableNode: TableNode,
    TableObserver: TableObserver,
    TableRowNode: TableRowNode,
    applyTableHandlers: applyTableHandlers,
    getDOMCellFromTarget: getDOMCellFromTarget,
    getTableObserverFromTableElement: getTableObserverFromTableElement
  });

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const ne$2 = createCommand$1("INSERT_TABLE_COMMAND");
  function ie$1(e) {
    return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
  }
  var ce$1 = ie$1(function (e) {
    const t = new URLSearchParams();
    t.append("code", e);
    for (let e = 1; e < arguments.length; e++) t.append("v", arguments[e]);
    throw Error(`Minified Lexical error #${e}; visit https://lexical.dev/docs/error?${t} for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`);
  });
  const ae$1 = "undefined" != typeof window && void 0 !== window.document && void 0 !== window.document.createElement;

  /**
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *
   */
  const mod$a = modDev$a;
  const $computeTableMap$1 = mod$a.$computeTableMap;
  const $computeTableMapSkipCellCheck$1 = mod$a.$computeTableMapSkipCellCheck;
  const $createTableCellNode$1 = mod$a.$createTableCellNode;
  const $createTableNode$1 = mod$a.$createTableNode;
  const $createTableNodeWithDimensions$1 = mod$a.$createTableNodeWithDimensions;
  const $createTableRowNode$1 = mod$a.$createTableRowNode;
  const $createTableSelection$1 = mod$a.$createTableSelection;
  const $deleteTableColumn$1 = mod$a.$deleteTableColumn;
  const $deleteTableColumn__EXPERIMENTAL$1 = mod$a.$deleteTableColumn__EXPERIMENTAL;
  const $deleteTableRow__EXPERIMENTAL$1 = mod$a.$deleteTableRow__EXPERIMENTAL;
  const $findCellNode$1 = mod$a.$findCellNode;
  const $findTableNode$1 = mod$a.$findTableNode;
  const $getElementForTableNode$1 = mod$a.$getElementForTableNode;
  const $getNodeTriplet$1 = mod$a.$getNodeTriplet;
  const $getTableCellNodeFromLexicalNode$1 = mod$a.$getTableCellNodeFromLexicalNode;
  const $getTableCellNodeRect$1 = mod$a.$getTableCellNodeRect;
  const $getTableColumnIndexFromTableCellNode$1 = mod$a.$getTableColumnIndexFromTableCellNode;
  const $getTableNodeFromLexicalNodeOrThrow$1 = mod$a.$getTableNodeFromLexicalNodeOrThrow;
  const $getTableRowIndexFromTableCellNode$1 = mod$a.$getTableRowIndexFromTableCellNode;
  const $getTableRowNodeFromTableCellNodeOrThrow$1 = mod$a.$getTableRowNodeFromTableCellNodeOrThrow;
  const $insertTableColumn$1 = mod$a.$insertTableColumn;
  const $insertTableColumn__EXPERIMENTAL$1 = mod$a.$insertTableColumn__EXPERIMENTAL;
  const $insertTableRow$1 = mod$a.$insertTableRow;
  const $insertTableRow__EXPERIMENTAL$1 = mod$a.$insertTableRow__EXPERIMENTAL;
  const $isTableCellNode$1 = mod$a.$isTableCellNode;
  const $isTableNode$1 = mod$a.$isTableNode;
  const $isTableRowNode$1 = mod$a.$isTableRowNode;
  const $isTableSelection$1 = mod$a.$isTableSelection;
  const $removeTableRowAtIndex$1 = mod$a.$removeTableRowAtIndex;
  const $unmergeCell$1 = mod$a.$unmergeCell;
  const INSERT_TABLE_COMMAND$1 = mod$a.INSERT_TABLE_COMMAND;
  const TableCellHeaderStates$1 = mod$a.TableCellHeaderStates;
  const TableCellNode$1 = mod$a.TableCellNode;
  const TableNode$1 = mod$a.TableNode;
  const TableObserver$1 = mod$a.TableObserver;
  const TableRowNode$1 = mod$a.TableRowNode;
  const applyTableHandlers$1 = mod$a.applyTableHandlers;
  const getDOMCellFromTarget$1 = mod$a.getDOMCellFromTarget;
  const getTableObserverFromTableElement$1 = mod$a.getTableObserverFromTableElement;

  var LexicalTable = /*#__PURE__*/Object.freeze({
    $computeTableMap: $computeTableMap$1,
    $computeTableMapSkipCellCheck: $computeTableMapSkipCellCheck$1,
    $createTableCellNode: $createTableCellNode$1,
    $createTableNode: $createTableNode$1,
    $createTableNodeWithDimensions: $createTableNodeWithDimensions$1,
    $createTableRowNode: $createTableRowNode$1,
    $createTableSelection: $createTableSelection$1,
    $deleteTableColumn: $deleteTableColumn$1,
    $deleteTableColumn__EXPERIMENTAL: $deleteTableColumn__EXPERIMENTAL$1,
    $deleteTableRow__EXPERIMENTAL: $deleteTableRow__EXPERIMENTAL$1,
    $findCellNode: $findCellNode$1,
    $findTableNode: $findTableNode$1,
    $getElementForTableNode: $getElementForTableNode$1,
    $getNodeTriplet: $getNodeTriplet$1,
    $getTableCellNodeFromLexicalNode: $getTableCellNodeFromLexicalNode$1,
    $getTableCellNodeRect: $getTableCellNodeRect$1,
    $getTableColumnIndexFromTableCellNode: $getTableColumnIndexFromTableCellNode$1,
    $getTableNodeFromLexicalNodeOrThrow: $getTableNodeFromLexicalNodeOrThrow$1,
    $getTableRowIndexFromTableCellNode: $getTableRowIndexFromTableCellNode$1,
    $getTableRowNodeFromTableCellNodeOrThrow: $getTableRowNodeFromTableCellNodeOrThrow$1,
    $insertTableColumn: $insertTableColumn$1,
    $insertTableColumn__EXPERIMENTAL: $insertTableColumn__EXPERIMENTAL$1,
    $insertTableRow: $insertTableRow$1,
    $insertTableRow__EXPERIMENTAL: $insertTableRow__EXPERIMENTAL$1,
    $isTableCellNode: $isTableCellNode$1,
    $isTableNode: $isTableNode$1,
    $isTableRowNode: $isTableRowNode$1,
    $isTableSelection: $isTableSelection$1,
    $removeTableRowAtIndex: $removeTableRowAtIndex$1,
    $unmergeCell: $unmergeCell$1,
    INSERT_TABLE_COMMAND: INSERT_TABLE_COMMAND$1,
    TableCellHeaderStates: TableCellHeaderStates$1,
    TableCellNode: TableCellNode$1,
    TableNode: TableNode$1,
    TableObserver: TableObserver$1,
    TableRowNode: TableRowNode$1,
    applyTableHandlers: applyTableHandlers$1,
    getDOMCellFromTarget: getDOMCellFromTarget$1,
    getTableObserverFromTableElement: getTableObserverFromTableElement$1
  });

  exports.Core = Lexical;
  exports.Html = LexicalHtml;
  exports.List = LexicalList;
  exports.Link = LexicalLink;
  exports.Clipboard = LexicalClipboard;
  exports.Selection = LexicalSelection;
  exports.History = LexicalHistory;
  exports.Utils = LexicalUtils;
  exports.Text = LexicalText;
  exports.RichText = LexicalRichText;
  exports.Table = LexicalTable;

}((this.BX.UI.Lexical = this.BX.UI.Lexical || {})));
//# sourceMappingURL=lexical.dev.bundle.js.map
