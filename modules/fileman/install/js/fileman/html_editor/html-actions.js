/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 *
 * Commands class
 * Rich Text Query/Formatting Commands
 */
(function()
{
	function BXEditorActions(editor)
	{
		this.editor = editor;
		this.document = editor.sandbox.GetDocument();
		BX.addCustomEvent(this.editor, 'OnIframeReInit', BX.proxy(function(){this.document = this.editor.sandbox.GetDocument();}, this));
		this.actions = this.GetActionList();
		this.contentActionIndex = {
			removeFormat: 1,
			bold: 1,
			italic: 1,
			underline: 1,
			strikeout: 1,
			fontSize: 1,
			foreColor: 1,
			backgroundColor: 1,
			formatInline: 1,
			formatBlock: 1,
			createLink: 1,
			insertHTML: 1,
			insertImage: 1,
			insertLineBreak: 1,
			removeLink: 1,
			insertOrderedList: 1,
			insertUnorderedList: 1,
			align: 1,
			indent: 1,
			outdent: 1,
			formatStyle: 1,
			fontFamily: 1,
			universalFormatStyle: 1,
			quote: 1,
			code: 1,
			sub: 1,
			sup: 1,
			insertSmile: 1
		};
	}

	BXEditorActions.prototype =
	{
		IsSupportedByBrowser: function(action)
		{
			// Following actions are supported but contain bugs in some browsers
			var
				isIe = BX.browser.IsIE() || BX.browser.IsIE10() || BX.browser.IsIE11(),
				arBuggyActions = {
					indent: isIe,
					outdent: isIe,
					formatBlock: isIe,
					insertUnorderedList: BX.browser.IsIE() || BX.browser.IsOpera(),
					insertOrderedList: BX.browser.IsIE() || BX.browser.IsOpera()
				},
				arSupportedActions = { // Firefox throws some errors for queryCommandSupported...
					insertHTML: BX.browser.IsFirefox()
				};

			if (!arBuggyActions[action])
			{
				// Firefox throws errors when invoking queryCommandSupported or queryCommandEnabled
				try {
					return this.document.queryCommandSupported(action);
				} catch(e1) {}

				try {
					return this.document.queryCommandEnabled(action);
				} catch(e2) {
					return !!arSupportedActions[action];
				}
			}
			return false;
		},

		IsSupported: function(action)
		{
			return !!this.actions[action];
		},

		IsContentAction: function(action)
		{
			return this.contentActionIndex[action];
		},

		Exec: function(action, value, bSilent)
		{
			var
				_this = this,
				oAction = this.actions[action],
				func = oAction && oAction.exec,
				isContentAction = this.IsContentAction(action),
				result = null;

			if (!bSilent)
			{
				this.editor.On("OnBeforeCommandExec", [isContentAction, action, oAction, value]);
			}

			if (isContentAction)
			{
				this.editor.Focus(false);
			}

			if (func)
			{
				result = func.apply(oAction, arguments);
			}
			else
			{
				try
				{
					result = this.document.execCommand(action, false, value);
				} catch(e){}
			}

			if (isContentAction)
			{
				setTimeout(function(){_this.editor.Focus(false);}, 1);
			}

			if (!bSilent)
			{
				this.editor.On("OnAfterCommandExec", [isContentAction, action]);
			}

			return result;
		},

		CheckState: function(action, value)
		{
			var
				oAction = this.actions[action],
				result = null;

			if (oAction && oAction.state)
			{
				result = oAction.state.apply(oAction, arguments);
			}
			else
			{
				try
				{
					result = this.document.queryCommandState(action);
				}
				catch(e)
				{
					result = false;
				}
			}
			return result;
		},

		/**
		 * Get the current command's value
		 *
		 * @param {String} command The command string which to check (eg. "formatBlock")
		 * @return {String} The command value
		 * @example
		 *    var currentBlockElement = commands.value("formatBlock");
		 */
		GetValue: function(command)
		{
			var
				obj = this.commands[command],
				method  = obj && obj.value;

			if (method)
			{
				return method.call(obj, this.composer, command);
			}
			else
			{
				try {
					// try/catch for buggy firefox
					return this.document.queryCommandValue(command);
				} catch(e) {
					return null;
				}
			}
		},

		GetActionList: function()
		{
			this.actions = {
				changeView: this.GetChangeView(),
				splitMode: this.GetChangeSplitMode(),
				fullscreen: this.GetFullscreen(),
				changeTemplate: this.GetChangeTemplate(),
				removeFormat: this.GetRemoveFormat(),

				// Format text
				bold: this.GetBold(),
				italic: this.GetItalic(),
				underline: this.GetUnderline(),
				strikeout: this.GetStrikeout(),
				// Size
				fontSize: this.GetFontSize(),
				// Color
				foreColor: this.GetForeColor(),
				backgroundColor: this.GetBackgroundColor(),
				//
				formatInline: this.GetFormatInline(),
				formatBlock: this.GetFormatBlock(),
				// Insert
				createLink: this.GetCreateLink(),
				insertHTML: this.GetInsertHTML(),
				insertImage: this.GetInsertImage(),
				insertLineBreak: this.GetInsertLineBreak(),
				insertTable: this.GetInsertTable(),
				removeLink: this.GetRemoveLink(),
				insertHr: this.GetInsertHr(),
				// Lists
				insertOrderedList: this.GetInsertList({bOrdered: true}),
				insertUnorderedList: this.GetInsertList({bOrdered: false}),
				align: this.GetAlign(),
				indent: this.GetIndent(),
				outdent: this.GetOutdent(),

				formatStyle: this.GetFormatStyle(),
				fontFamily: this.GetFontFamily(),
				universalFormatStyle: this.GetUniversalFormatStyle(),

				selectNode: this.GetSelectNode(),
				doUndo: this.GetUndoRedo(true),
				doRedo: this.GetUndoRedo(false),

				sub: this.GetSubSup('sub'),
				sup: this.GetSubSup('sup'),
				quote: this.GetQuote(),
				code: this.GetCode(),
				insertSmile: this.GetInsertSmile(),
				tableOperation: this.GetTableOperation(),

				// Bbcodes actions
				formatBbCode: this.GetFormatBbCode()
			};

			this.editor.On("OnGetActionsList");

			return this.actions;
		},

		GetChangeView: function()
		{
			var _this = this;
			return {
				exec: function()
				{
					var value = arguments[1];
					if ({'code': 1, 'wysiwyg': 1, 'split': 1}[value])
						_this.editor.SetView(value)
				},

				state: function() {
					return false;
				},

				value: function() {}
			};
		},

		GetChangeSplitMode: function()
		{
			var _this = this;
			return {
				exec: function()
				{
					_this.editor.SetSplitMode(arguments[1] == 1);
				},

				state: function() {
					return _this.editor.GetSplitMode();
				},

				value: function() {}
			};
		},

		GetFullscreen: function()
		{
			var _this = this;
			return {
				exec: function()
				{
					_this.editor.Expand();
				},

				state: function()
				{
					return _this.editor.IsExpanded();
				},

				value: function() {}
			};
		},

		/**
		 * formatInline scenarios for tag "B" (| = caret, |foo| = selected text)
		 *
		 *   #1 caret in unformatted text:
		 *      abcdefg|
		 *   output:
		 *      abcdefg<b>|</b>
		 *
		 *   #2 unformatted text selected:
		 *      abc|deg|h
		 *   output:
		 *      abc<b>|deg|</b>h
		 *
		 *   #3 unformatted text selected across boundaries:
		 *      ab|c <span>defg|h</span>
		 *   output:
		 *      ab<b>|c </b><span><b>defg</b>|h</span>
		 *
		 *   #4 formatted text entirely selected
		 *      <b>|abc|</b>
		 *   output:
		 *      |abc|
		 *
		 *   #5 formatted text partially selected
		 *      <b>ab|c|</b>
		 *   output:
		 *      <b>ab</b>|c|
		 *
		 *   #6 formatted text selected across boundaries
		 *      <span>ab|c</span> <b>de|fgh</b>
		 *   output:
		 *      <span>ab|c</span> de|<b>fgh</b>
		 */
		GetFormatInline: function()
		{
			var
				_this = this,
				TAG_ALIAS = {
					strong: "b",
					em: "i",
					b: "strong",
					i: "em"
				},
				htmlApplier = {};

			function getKey(tagName, style, className)
			{
				var key = tagName + ":";
				if (className)
					key += className;
				if (style)
				{
					for (var i in style)
						if (style.hasOwnProperty(i))
							key += i + '=' + style[i] + ';';
				}
				return key;
			}

			function getStyler(tagName, style, className)
			{
				var key = getKey(tagName, style, className);
				if (!htmlApplier[key])
				{
					var alias = TAG_ALIAS[tagName];
					var tags = alias ? [tagName.toLowerCase(), alias.toLowerCase()] : [tagName.toLowerCase()];
					htmlApplier[key] = new _this.editor.HTMLStyler(_this.editor, tags, style, className, true);
				}
				return htmlApplier[key];
			}

			return {
				exec: function(command, value, tagName, arStyle, cssClass, params)
				{
					params = (!params || typeof params != 'object') ? {} : params;
					_this.editor.iframeView.Focus();
					var range = _this.editor.selection.GetRange();
					if (!range)
					{
						return false;
					}

					var applier = getStyler(tagName, arStyle, cssClass);

					if (params.bClear)
					{
						range = applier.UndoToRange(range, false);
					}
					else
					{
						range = applier.ToggleRange(range);
					}

					setTimeout(function()
					{
						_this.editor.selection.SetSelection(range);

						// If we format text in the table - we could accidentally broke table,
						// so now we should check and repair all possible tables (mantis: #66342)
						var
							table, i, j, k, cellNode,
							tables = [],
							bogusNodes = [],
							nodes = range.getNodes([1]);

						for (i = 0; i < nodes.length; i++)
						{
							if (BX.util.in_array(nodes[i].nodeName, ['TD', 'TR', 'TH']))
							{
								table = BX.findParent(nodes[i], function(n)
								{
									return n.nodeName == "TABLE";
								}, _this.editor.GetIframeDoc().body);

								if (table && !BX.util.in_array(table, tables))
								{
									tables.push(table);
								}
							}
						}

						// Now we have list of tables. In most cases it will be one node.
						for (i = 0; i < tables.length; i++)
						{
							table = tables[i];
							for (j = 0; j < table.rows.length; j++)
							{
								for (k = 0; k < table.rows[j].childNodes.length; k++)
								{
									cellNode = table.rows[j].childNodes[k];
									// If it's cell inside the row, it should at least be on of the table dedicated tag. If not - we will remove it to save html valid.
									if (cellNode
										&& cellNode.nodeType == 1
										&& !BX.util.in_array(cellNode.nodeName, _this.editor.TABLE_TAGS))
									{
										bogusNodes.push(cellNode);
									}
								}
							}
						}

						// Clean invalid tags inside the table.
						for (i = 0; i < bogusNodes.length; i++)
						{
							BX.cleanNode(bogusNodes[i], true);
						}

						var lastCreatedNode = _this.editor.selection.GetSelectedNode();
						if (lastCreatedNode && lastCreatedNode.nodeType == 1)
						{
							_this.editor.lastCreatedId = Math.round(Math.random() * 1000000);
							lastCreatedNode.setAttribute('data-bx-last-created-id', _this.editor.lastCreatedId);
						}

					}, 10);
				},

				state: function(action, value, tagName, arStyle, cssClass)
				{
					var
						doc = _this.editor.GetIframeDoc(),
						aliasTagName = TAG_ALIAS[tagName] || tagName;

					// Check whether the document contains a node with the desired tagName
					if (
						!_this.editor.util.DocumentHasTag(doc, tagName)
						&&
						(tagName != aliasTagName && !_this.editor.util.DocumentHasTag(doc, aliasTagName))
						)
					{
						return false;
					}

					var range = _this.editor.selection.GetRange();
					if (!range)
					{
						return false;
					}

					var applier = getStyler(tagName, arStyle, cssClass);
					return applier.IsAppliedToRange(range, false);
				},

				value: BX.DoNothing
			};
		},

		/*
		* TODO: 1. Clean useless spans when H1-H6 are created
		* */
		GetFormatBlock: function()
		{
			var
				_this = this,
				DEFAULT_NODE_NAME = "DIV",
				blockTags = _this.editor.GetBlockTags(),
				nestedBlockTags = _this.editor.NESTED_BLOCK_TAGS;

			/**
			 * Remove similiar classes (based on classRegExp)
			 * and add the desired class name
			 */
			function _addClass(element, className, classRegExp)
			{
				if (element.className)
				{
					_removeClass(element, classRegExp);
					element.className += " " + className;
				}
				else
				{
					element.className = className;
				}
			}

			function _removeClass(element, classRegExp)
			{
				element.className = element.className.replace(classRegExp, "");
			}

			/**
			 * Adds line breaks before and after the given node if the previous and next siblings
			 * aren't already causing a visual line break (block element or <br>)
			 */
			function addBrBeforeAndAfter(node)
			{
				var
					nextSibling = _this.editor.util.GetNextNotEmptySibling(node),
					previousSibling = _this.editor.util.GetPreviousNotEmptySibling(node);

				if (nextSibling && !isBrOrBlockElement(nextSibling))
				{
					node.parentNode.insertBefore(_this.document.createElement("BR"), nextSibling);
				}

				if (previousSibling && !isBrOrBlockElement(previousSibling))
				{
					node.parentNode.insertBefore(_this.document.createElement("BR"), node);
				}
			}

			// Removes line breaks before and after the given node
			function removeBrBeforeAndAfter(node)
			{
				var
					nextSibling = _this.editor.util.GetNextNotEmptySibling(node),
					previousSibling = _this.editor.util.GetPreviousNotEmptySibling(node);

				if (nextSibling && nextSibling.nodeName === "BR")
				{
					nextSibling.parentNode.removeChild(nextSibling);
				}

				if (previousSibling && previousSibling.nodeName === "BR")
				{
					previousSibling.parentNode.removeChild(previousSibling);
				}
			}

			function removeLastChildBr(node)
			{
				var lastChild = node.lastChild;
				if (lastChild && lastChild.nodeName === "BR")
				{
					lastChild.parentNode.removeChild(lastChild);
				}
			}

			function isBrOrBlockElement(element)
			{
				return element.nodeName === "BR" || _this.editor.util.IsBlockElement(element);
			}

			// Execute native query command
			function execNativeCommand(command, nodeName, className, style)
			{
				if (className || style)
				{
//					var eventListener = dom.observe(doc, "DOMNodeInserted", function(event)
//					{
//						var target = event.target,
//							displayStyle;
//						if (target.nodeType !== 1 /* element node */)
//							return;
//
//						displayStyle = dom.getStyle("display").from(target);
//						if (displayStyle.substr(0, 6) !== "inline")
//						{
//							// Make sure that only block elements receive the given class
//							target.className += " " + className;
//						}
//					});
				}
				_this.document.execCommand(command, false, nodeName);
//				if (eventListener)
//					eventListener.stop();
			}

			function _hasClasses(element)
			{
				return !element.className || BX.util.trim(element.className) === '';
			}

			function applyBlockElement(node, newNodeName, className, arStyle)
			{
				// Rename current block element to new block element and add class
				if (newNodeName)
				{
					if (node.nodeName !== newNodeName)
					{
						node = _this.editor.util.RenameNode(node, newNodeName);
					}
					if (className)
					{
						node.className = className;
					}
					if (arStyle && arStyle.length > 0)
					{
						_this.editor.util.SetCss(node, arStyle);
					}
				}
				else // Get rid of node
				{
					// Insert a line break afterwards and beforewards when there are siblings
					// that are not of type line break or block element
					addBrBeforeAndAfter(node);
					_this.editor.util.ReplaceWithOwnChildren(node);
				}
			}

			return {
				exec: function(command, nodeName, className, arStyle, params)
				{
					params = params || {};
					nodeName = typeof nodeName === "string" ? nodeName.toUpperCase() : nodeName;
					var
						childBlockNodes, i, createdBlockNodes,
						range = params.range || _this.editor.selection.GetRange(),
						blockElement = nodeName ? _this.actions.formatBlock.state(command, nodeName, className, arStyle) : false,
						selectedNode;

					if (params.range)
						_this.editor.selection.RestoreBookmark();

					if (blockElement) // Same block element
					{
						var wholeBlockSelected = range.startContainer == blockElement.firstChild && range.endContainer == blockElement.lastChild;

						// Divs and blockquotes can be inside each other
						if (nodeName && BX.util.in_array(nodeName, nestedBlockTags) && params.nestedBlocks !== false)
						{
							// create new div
							blockElement = _this.document.createElement(nodeName || DEFAULT_NODE_NAME);
							if (className)
							{
								blockElement.className = className;
							}

							// Select line with wrap
							_this.editor.selection.Surround(blockElement);
							_this.editor.selection.SelectNode(blockElement);
							_this.editor.util.SetCss(blockElement, arStyle);

							var nextBr = _this.editor.util.GetNextNotEmptySibling(blockElement);
							if (nextBr && nextBr.nodeName == 'BR')
								BX.remove(nextBr);

							setTimeout(function()
							{
								if (blockElement)
								{
									_this.editor.selection.SelectNode(blockElement);
								}
							}, 50);
						}
						else if (params && params.splitBlock && !wholeBlockSelected)
						{
							var childBlock = _this.document.createElement(nodeName || DEFAULT_NODE_NAME);

							_this.editor.selection.Surround(childBlock);
							_this.editor.selection.SelectNode(childBlock);

							if (className)
								childBlock.className = className;

							_this.editor.util.SetCss(childBlock, arStyle);

							if (blockElement && blockElement.lastChild)
							{
								var _range = range.cloneRange();
								_range.setStartAfter(childBlock);
								_range.setEndAfter(blockElement.lastChild);

								var afterBlock = blockElement.cloneNode(false);
								_this.editor.selection.SetSelection(_range);
								_this.editor.selection.Surround(afterBlock);

								var firstBr = _this.editor.selection._GetNonTextFirstChild(afterBlock);
								if (firstBr && firstBr.nodeName == 'BR')
									BX.remove(firstBr);
							}

							_this.editor.selection.SetSelection(range);

							setTimeout(function()
							{
								if (childBlock)
								{
									_this.editor.selection.SelectNode(childBlock);
								}
							}, 50);
						}
						else
						{
							_this.editor.util.SetCss(blockElement, arStyle);
							if (className)
							{
								blockElement.className = className;

								// Bug workaround for bug with rendering in Firefox
								if (BX.browser.IsFirefox())
								{
									var tmpId = "bx-editor-temp-" + Math.round(Math.random() * 1000000);
									blockElement.id = tmpId;
									blockElement.parentNode.innerHTML = blockElement.parentNode.innerHTML;
									blockElement = _this.editor.GetIframeElement(tmpId);
									if (blockElement)
										blockElement.removeAttribute("id");
								}
							}

							setTimeout(function()
							{
								if (blockElement)
								{
									_this.editor.selection.SelectNode(blockElement);
								}
							}, 50);
						}
					}
					else
					{
						// Find other block element and rename it (<h2></h2> => <h1></h1>)
						if (nodeName === null || BX.util.in_array(nodeName, blockTags))
						{
							blockElement = false;
							selectedNode = _this.editor.selection.GetSelectedNode();

							if (selectedNode)
							{
								if (selectedNode.nodeType == 1 && BX.util.in_array(selectedNode.nodeName, blockTags))
								{
									blockElement = selectedNode;
								}
								else
								{
									blockElement = BX.findParent(selectedNode, function(n)
									{
										return BX.util.in_array(n.nodeName, blockTags);
									}, _this.document.body);
								}
							}
							else
							{
								var
									commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);

								if (commonAncestor && commonAncestor.nodeName !== 'BODY' &&
									BX.util.in_array(commonAncestor.nodeName, blockTags))
								{
									blockElement = commonAncestor;
								}
							}

							if (blockElement && !_this.actions.quote.checkNode(blockElement))
							{
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									applyBlockElement(blockElement, nodeName, className, arStyle);
								});
								return true;
							}
						}

						blockElement = BX.create(nodeName || DEFAULT_NODE_NAME, {}, _this.document);
						if (className)
						{
							blockElement.className = className;
						}
						_this.editor.util.SetCss(blockElement, arStyle);

						if (range.collapsed || selectedNode && selectedNode.nodeType == 3 /* text node*/)
						{
							// Select node
							_this.editor.selection.SelectNode(selectedNode);
						}
						else if (range.collapsed)
						{
							// Select line with wrap
							_this.editor.selection.SelectLine();
						}

						_this.editor.selection.Surround(blockElement, range);
						removeBrBeforeAndAfter(blockElement);
						removeLastChildBr(blockElement);

						// Used in align action
						if (params.leaveChilds)
						{
							return blockElement;
						}

						if (nodeName && !BX.util.in_array(nodeName, nestedBlockTags))
						{
							range = _this.editor.selection.GetRange();
							createdBlockNodes = range.getNodes([1]);

							// 1. Find all child "P" and remove them
							if (nodeName == 'P')
							{
								childBlockNodes = blockElement.getElementsByTagName(nodeName);
								// Clean empty bogus H1, H2, H3...
								for (i = 0; i < createdBlockNodes.length; i++)
								{
									if (arStyle && _this.editor.util.CheckCss(createdBlockNodes[i], arStyle, false) && _this.editor.util.IsEmptyNode(createdBlockNodes[i], true, true))
									{
										BX.remove(createdBlockNodes[i]);
									}
								}

								while (childBlockNodes[0])
								{
									addBrBeforeAndAfter(childBlockNodes[0]);
									_this.editor.util.ReplaceWithOwnChildren(childBlockNodes[0]);
								}
							}
							else if (nodeName.substr(0, 1) == 'H')
							{
								// Clean empty bogus H1, H2, H3...
								for (i = 0; i < createdBlockNodes.length; i++)
								{
									if (createdBlockNodes[i].nodeName !== nodeName && _this.editor.util.IsEmptyNode(createdBlockNodes[i], true, true))
									{
										BX.remove(createdBlockNodes[i]);
									}
								}

								var childHeaders = BX.findChild(blockElement, function(n)
								{
									return BX.util.in_array(n.nodeName, blockTags) && n.nodeName.substr(0, 1) == 'H';
								}, true, true);

								for (i = 0; i < childHeaders.length; i++)
								{
									addBrBeforeAndAfter(childHeaders[i]);
									_this.editor.util.ReplaceWithOwnChildren(childHeaders[i]);
								}
							}
						}

						if (blockElement && params.bxTagParams && typeof params.bxTagParams == 'object')
						{
							_this.editor.SetBxTag(blockElement, params.bxTagParams);
						}

						if (blockElement && blockElement.parentNode)
						{
							var parent = blockElement.parentNode;
							if (parent.nodeName == 'UL' || parent.nodeName == 'OL')
							{
								// 1. Clean empty LI before and after
								var li = _this.editor.util.GetPreviousNotEmptySibling(blockElement);
								if (_this.editor.util.IsEmptyLi(li))
								{
									BX.remove(li);
								}
								li = _this.editor.util.GetNextNotEmptySibling(blockElement);
								if (_this.editor.util.IsEmptyLi(li))
								{
									BX.remove(li);
								}

								// 2. If parent list doesn't have other items - put it inside blockquote
								if (!_this.editor.util.GetPreviousNotEmptySibling(blockElement) && !_this.editor.util.GetNextNotEmptySibling(blockElement))
								{
									var blockElementNew = blockElement.cloneNode(false);
									parent.parentNode.insertBefore(blockElementNew, parent);
									_this.editor.util.ReplaceWithOwnChildren(blockElement);
									blockElementNew.appendChild(parent);
								}
							}

							if (blockElement.firstChild && blockElement.firstChild.nodeName == 'BLOCKQUOTE')
							{
								var prev = _this.editor.util.GetPreviousNotEmptySibling(blockElement);
								if (prev && prev.nodeName == 'BLOCKQUOTE' && _this.editor.util.IsEmptyNode(prev))
								{
									BX.remove(prev);
								}
							}

							if ((blockElement.nodeName == 'BLOCKQUOTE' || blockElement.nodeName == 'PRE') && _this.editor.util.IsEmptyNode(blockElement))
							{
								blockElement.innerHTML = '';
								var br = _this.document.createElement("br");
								blockElement.appendChild(br);
								_this.editor.selection.SetAfter(br);
							}
						}

						setTimeout(function()
						{
							if (blockElement)
							{
								_this.editor.selection.SelectNode(blockElement);
							}
						}, 10);

						return true;
					}
				},

				state: function(command, nodeName, className, style)
				{
					nodeName = typeof(nodeName) === "string" ? nodeName.toUpperCase() : nodeName;
					var
						result = false,
						selectedNode = _this.editor.selection.GetSelectedNode();

					if (selectedNode && selectedNode.nodeName)
					{
						if (selectedNode.nodeName != nodeName)
						{
							selectedNode = BX.findParent(selectedNode, function(n)
							{
								return n.nodeName == nodeName;
							}, _this.document.body);
						}
						result = (selectedNode && selectedNode.tagName == nodeName) ? selectedNode : false;
					}
					else
					{
						var
							range = _this.editor.selection.GetRange(),
							commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);

						if (commonAncestor.nodeName == nodeName)
						{
							result = commonAncestor;
						}
					}
					return result;
				},

				value: BX.DoNothing,

				removeBrBeforeAndAfter: removeBrBeforeAndAfter,
				addBrBeforeAndAfter: addBrBeforeAndAfter
			};
		},

		GetRemoveFormat: function()
		{
			var
				FORMAT_NODES_INLINE = {
					"B": 1,
					"STRONG": 1,
					"I": 1,
					"EM": 1,
					"U": 1,
					"DEL": 1,
					"S": 1,
					"STRIKE": 1,
					"A": 1,
					"SPAN" : 1,
					"CODE" : 1,
					"NOBR" : 1,
					"Q" : 1,
					"FONT" : 1,
					"CENTER": 1,
					"CITE": 1
				},
				FORMAT_NODES_BLOCK = {
					"H1": 1,
					"H2": 1,
					"H3": 1,
					"H4": 1,
					"H5": 1,
					"H6": 1,
					"DIV": 1,
					"P": 1,
					"LI": 1,
					"UL" : 1,
					"OL" : 1,
					"MENU" : 1,
					"BLOCKQUOTE": 1,
					"PRE": 1
				},
				_this = this;

			function checkAndCleanNode(node, doc)
			{
				if (!node)
					return;
				var nodeName = node.nodeName;
				if (FORMAT_NODES_INLINE[nodeName])
				{
					_this.editor.util.ReplaceWithOwnChildren(node);
				}
				else if (FORMAT_NODES_BLOCK[nodeName])
				{
					_this.actions.formatBlock.addBrBeforeAndAfter(node);
					_this.editor.util.ReplaceWithOwnChildren(node);
				}
				else
				{
					node.removeAttribute("style");
					node.removeAttribute("class");
					node.removeAttribute("align");

					if (_this.editor.bbCode && node.nodeName == 'TABLE')
					{
						node.removeAttribute('align');
					}
				}
			}

			function checkTableNode(node)
			{
				return BX.findParent(node, function(n)
				{
					return n.nodeName == "TABLE";
				}, _this.editor.GetIframeDoc().body);
			}

			function getUnitaryParent(textNode)
			{
				var
					prevSibling, nextSibling,
					parent = textNode.parentNode;

				while (parent && parent.nodeName !== 'BODY')
				{
					prevSibling = parent.previousSibling && !_this.editor.util.IsEmptyNode(parent.previousSibling);
					nextSibling = parent.nextSibling && !_this.editor.util.IsEmptyNode(parent.nextSibling);

					if (prevSibling || nextSibling || parent.parentNode.nodeName == 'BODY')
					{
						break;
					}
					parent = parent.parentNode;
				}
				return parent;
			}

			function checkParentList(node)
			{
				var
					doc = _this.editor.GetIframeDoc(),
					list = BX.findParent(node, function(n)
					{
						return n.nodeName == "UL" || n.nodeName == "OL" || n.nodeName == "MENU";
					}, doc.body);

				if (list)
				{
					var
						i, child,
						listBefore = list.cloneNode(false),
						listAfter = list.cloneNode(false),
						before = true;

					BX.cleanNode(listBefore);
					BX.cleanNode(listAfter);

					for (i = 0; i < list.childNodes.length; i++)
					{
						child = list.childNodes[i];
						if (child == node)
						{
							before = false;
						}

						if (child.nodeName == 'LI')
						{
							if (!_this.editor.util.IsEmptyNode(child, true, true))
							{
								(before ? listBefore : listAfter).appendChild(child.cloneNode(true));
							}
						}
					}

					if (listBefore.childNodes.length > 0)
					{
						list.parentNode.insertBefore(listBefore, list);
					}
					list.parentNode.insertBefore(node, list);
					if (listAfter.childNodes.length > 0)
					{
						list.parentNode.insertBefore(listAfter, list);
					}
					BX.remove(list);
					return true;
				}
				return false;
			}

			function cleanNodes(nodes, doc)
			{
				if (nodes && nodes.length > 0)
				{
					var i, len, sorted = [];
					for (i = 0, len = nodes.length; i < len; i++)
					{
						if (!_this.editor.util.CheckSurrogateNode(nodes[i]))
						{
							sorted.push({node: nodes[i], nesting: _this.editor.util.GetNodeDomOffset(nodes[i])});
						}
					}
					sorted = sorted.sort(function(a, b){return b.nesting - a.nesting});
					for (i = 0, len = sorted.length; i < len; i++)
					{
						checkAndCleanNode(sorted[i].node, doc);
					}
				}
			}

			function _selectAndGetNodes(node)
			{
				var
					i, found = false,
					range = _this.editor.selection.SelectNode(node),
					nodes = range.getNodes([1]);

				if (node.nodeType == 1)
				{
					for (i = 0; i < nodes.length; i++)
					{
						if (nodes[i] == node)
						{
							found = true;
							break;
						}
					}
					if (!found)
					{
						nodes = [node].concat(nodes);
					}
				}

				if (!nodes || typeof nodes != 'object' || nodes.length == 0)
				{
					nodes = [node];
				}
				return nodes;
			}

			function getNodesFromTo(nodeStart, nodeEnd)
			{
				var list = [];
				if (nodeStart && (!nodeEnd || nodeStart == nodeEnd))
				{
					list.push(nodeStart);
				}
				else if (!nodeStart && nodeEnd)
				{
					list.push(nodeEnd);
				}

				return list;
			}


			return {
				exec: function(action, value)
				{
					var range = _this.editor.selection.GetRange();

					if (!range || _this.editor.iframeView.IsEmpty())
						return;

					var
						bSurround = true,
						i,
						textNodes, textNode, node, tmpNode,
						nodes = range.getNodes([1]),
						doc = _this.editor.GetIframeDoc();

					// Range is collapsed or text node is selected
					if (nodes.length == 0)
					{
						textNodes = range.getNodes([3]);

						if (textNodes && textNodes.length == 1)
						{
							textNode = textNodes[0];
						}

						if (!textNode && range.startContainer == range.endContainer)
						{
							if (range.startContainer.nodeType == 3)
							{
								textNode = range.startContainer;
							}
							else
							{
								bSurround = false;
								nodes = _selectAndGetNodes(range.startContainer);
							}
						}

						if (textNode && nodes.length == 0)
						{
							node = getUnitaryParent(textNode);
							if (node && (node.nodeName != 'BODY' || range.collapsed))
							{
								bSurround = false;
								nodes = _selectAndGetNodes(node);
							}
						}
					}
					else
					{
						var
							updateSel = false,
							clearRanges = [],
							startTableCheck = checkTableNode(range.startContainer),
							endTableCheck = checkTableNode(range.endContainer);

						if (startTableCheck)
						{
							clearRanges.push(
								{
									startContainer: range.startContainer,
									startOffset: range.startOffset,
									end: startTableCheck
								}
							);

							range.setStartAfter(startTableCheck);
							updateSel = true;
						}

						if (endTableCheck)
						{
							updateSel = true;
							clearRanges.push(
								{
									start: endTableCheck,
									endContainer: range.endContainer,
									endOffset: range.endOffset
								}
							);
							range.setEndBefore(endTableCheck);
						}


						var
							startList = _this.editor.util.FindParentEx(range.startContainer, function(n){return n.nodeName == "UL" || n.nodeName == "OL" || n.nodeName == "MENU";}, doc.body),
							endList = _this.editor.util.FindParentEx(range.endContainer, function(n){return n.nodeName == "UL" || n.nodeName == "OL" || n.nodeName == "MENU";}, doc.body);
							//listNodes = getNodesFromTo(startList, endList);

						if (startList)
						{
							range.setStartBefore(startList);
							if (!endList)
								range.setEndAfter(startList);
							updateSel = true;
						}

						if (endList)
						{
							updateSel = true;
							range.setEndAfter(endList);
							if (!startList)
								range.setStartBefore(endList);
						}

						if (updateSel)
						{
							_this.editor.selection.SetSelection(range);
							nodes = range.getNodes([1]);
						}
					}

					if (bSurround)
					{
						tmpNode = doc.createElement("span");
						_this.editor.selection.Surround(tmpNode, range);
						nodes = _selectAndGetNodes(tmpNode);
					}

					if (nodes && nodes.length > 0)
					{
						_this.editor.selection.ExecuteAndRestoreSimple(function()
						{
							cleanNodes(nodes, doc);
						});
					}

					if (clearRanges && clearRanges.length > 0)
					{
						var
							_range = range.cloneRange();

						for (i = 0; i < clearRanges.length; i++)
						{
							if (clearRanges[i].start)
							{
								_range.setStartBefore(clearRanges[i].start);
							}
							else
							{
								_range.setStart(clearRanges[i].startContainer, clearRanges[i].startOffset);
							}
							if (clearRanges[i].end)
							{
								_range.setEndAfter(clearRanges[i].end);
							}
							else
							{
								_range.setEnd(clearRanges[i].endContainer,  clearRanges[i].endOffset);
							}
							_this.editor.selection.SetSelection(_range);
							cleanNodes(_range.getNodes([1]), doc);
						}

						_this.editor.selection.SetSelection(range);
					}

					if (bSurround && tmpNode && tmpNode.parentNode)
					{
						if (checkParentList(tmpNode))
						{
							_this.editor.selection.SelectNode(tmpNode);
						}

						_this.editor.selection.ExecuteAndRestoreSimple(function()
						{
							_this.editor.util.ReplaceWithOwnChildren(tmpNode);
						});
					}

					if (!_this.editor.iframeView.IsEmpty(true))
					{
						_this.actions.formatBlock.exec('formatBlock', null);
					}
				},
				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetBold: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						return _this.actions.formatInline.exec(action, value, "b");
					}
					else // BBCode mode
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'B'});
					}
				},
				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "b");
				},
				value: BX.DoNothing
			};
		},

		GetItalic: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						return _this.actions.formatInline.exec(action, value, "i");
					}
					else
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'I'});
					}
				},
				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "i");
				},
				value: BX.DoNothing
			};
		},

		GetUnderline: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						return _this.actions.formatInline.exec(action, value, "u");
					}
					else
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'U'});
					}
				},
				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "u");
				},
				value: BX.DoNothing
			};
		},

		GetStrikeout: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						return _this.actions.formatInline.exec(action, value, "del");
					}
					else
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'S'});
					}
				},
				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "del");
				},
				value: BX.DoNothing
			};
		},

		GetFontSize: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					var res;
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						if (value > 0) // Format
							res = _this.actions.formatInline.exec(action, value, "span", {fontSize: value + 'pt'});
						else // Clear font-size format
							res = _this.actions.formatInline.exec(action, value, "span", {fontSize: null}, null, {bClear: true});
					}
					else // textarea + bbcode
					{
						res = _this.actions.formatBbCode.exec(action, {tag: 'SIZE', value: value + 'pt'});
					}
					return res;
				},

				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "span", {fontSize: value + 'pt'});
				},

				value: BX.DoNothing
			};
		},

		GetForeColor: function()
		{
			var _this = this;

			function checkListItemColor()
			{
				var
					doc = _this.editor.GetIframeDoc(),
					i, item, res = [],
					range = _this.editor.selection.GetRange();

				if (range)
				{
					var nodes = range.getNodes([1]);

					if (nodes.length == 0 && range.startContainer == range.endContainer &&  range.startContainer.nodeName != 'BODY')
					{
						nodes = [range.startContainer];
					}

					for (i = 0; i < nodes.length; i++)
					{
						item = BX.findParent(nodes[i], function(n){
							return n.nodeName == 'LI' && n.style && n.style.color;
						}, doc.body);

						if (item)
						{
							res.push(item);
						}
					}
				}

				return res.length === 0 ? false : res;
			}

			function checkAndApplyColorListItems(value)
			{
				var
					doc = _this.editor.GetIframeDoc(),
					node = _this.editor.selection.GetSelectedNode(),
					i, j, spans, range, span, li,
					nodes;

				if (node && (node.nodeType === 3 || node.nodeName == 'SPAN'))
				{
					span = node.nodeName == 'SPAN' ? node : BX.findParent(node, {tag: 'span'}, doc.body);
					if (span && span.style.color)
					{
						li = BX.findParent(span, {tag: 'li'}, doc.body);
						if (li)
						{
							if (li.childNodes.length == 1 && li.firstChild == span)
							{
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									li.style.color = value;
									span.style.color = '';
									if (BX.util.trim(span.style.cssText) == '')
									{
										_this.editor.util.ReplaceWithOwnChildren(span);
									}
								});
							}
						}
					}
				}
				else
				{
					if (!node)
					{
						range = _this.editor.selection.GetRange();
						nodes = range.getNodes([1]);
					}
					else
					{
						nodes = [node];
					}

					for (i = 0; i < nodes.length; i++)
					{
						if (nodes[i] && nodes[i].nodeName == "LI" && !_this.editor.bbCode)
						{
							// 1. Add color to LI
							nodes[i].style.color = value;
							// Find and clear all spans created by action
							spans = BX.findChild(nodes[i], function(n)
							{
								return n.nodeName == "SPAN";
							}, true, true);

							_this.editor.selection.ExecuteAndRestoreSimple(function()
							{
								for (j = 0; j < spans.length; j++)
								{
									if (spans[j] && spans[j].parentNode &&
										spans[j].parentNode.parentNode &&
										spans[j].parentNode.parentNode.parentNode)
									{
										try{
											spans[j].style.color = '';
											if (BX.util.trim(spans[j].style.cssText) == '')
											{
												_this.editor.util.ReplaceWithOwnChildren(spans[j]);
											}
										}
										catch(e)
										{}
									}
								}
							});
						}
					}
				}
			}

			return {
				exec: function(action, value)
				{
					var res;

					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						if (value == '')
						{
							res = _this.actions.formatInline.exec(action, value, "span", {color: null}, null, {bClear: true});
						}
						else
						{
							res =  _this.actions.formatInline.exec(action, value, "span", {color: value});
							checkAndApplyColorListItems(value);
						}
					}
					else if (value) // textarea + bbcode
					{
						res = _this.actions.formatBbCode.exec(action, {tag: 'COLOR', value: value});
					}

					return res;
				},

				state: function(action, value)
				{
					var state = _this.actions.formatInline.state(action, value, "span", {color: value});
					if (!state)
					{
						state = checkListItemColor();
					}

					return state;
				},

				value: BX.DoNothing
			};
		},

		GetBackgroundColor: function()
		{
			var _this = this;

			return {
				exec: function(action, value)
				{
					var res;
					if (value == '')
					{
						res =  _this.actions.formatInline.exec(action, value, "span", {backgroundColor: null}, null, {bClear: true});
					}
					else
					{
						res =  _this.actions.formatInline.exec(action, value, "span", {backgroundColor: value});
					}
					return res;
				},

				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "span", {backgroundColor: value});
				},

				value: BX.DoNothing
			};
		},

		GetCreateLink: function()
		{
			var
				ATTRIBUTES = ['title', 'id', 'name', 'target', 'rel'],
				_this = this;

			return {
				exec: function(action, value)
				{
					// Only for bbCode == true
					if (_this.editor.bbCode && _this.editor.synchro.IsFocusedOnTextarea())
					{
						_this.editor.textareaView.Focus();
						var linkHtml = "[URL=" + _this.editor.util.spaceUrlEncode(value.href) + "]" + (value.text || value.href) + "[/URL]";
						_this.editor.textareaView.WrapWith(false, false, linkHtml);
					}
					else
					{
						_this.editor.iframeView.Focus();

						var
							nodeToSetCarret,
							params = (value && typeof(value) === "object") ? value : {href: value},
							i, link, linksCount = 0, lastLink,
							links;

						function applyAttributes(link, params)
						{
							var attr;
							link.removeAttribute("class");
							link.removeAttribute("target");

							for (attr in params)
							{
								if (params.hasOwnProperty(attr) && BX.util.in_array(attr, ATTRIBUTES))
								{
									if (params[attr] == '' || params[attr] == undefined)
									{
										link.removeAttribute(attr);
									}
									else
									{
										link.setAttribute(attr, params[attr]);
									}
								}
							}
							if (params.className)
								link.className = params.className;
							link.href = _this.editor.util.spaceUrlEncode(params.href) || '';

							if (params.noindex)
							{
								link.setAttribute("data-bx-noindex", "Y");
							}
							else
							{
								link.removeAttribute("data-bx-noindex");
							}
						}

						links = value.node ? [value.node] : _this.actions.formatInline.state(action, value, "a");
						if (links)
						{
							// Selection contains links
							for (i = 0; i < links.length; i++)
							{
								link = links[i];
								if (link)
								{
									applyAttributes(link, params);
									lastLink = link;
									linksCount++;
								}
							}

							if (linksCount === 1 && lastLink && (lastLink.querySelector && !lastLink.querySelector("*")) && params.text != '')
							{
								_this.editor.util.SetTextContent(lastLink, params.text);
							}
							nodeToSetCarret = lastLink;

							if (nodeToSetCarret)
								_this.editor.selection.SetAfter(nodeToSetCarret);

							setTimeout(function()
							{
								if (nodeToSetCarret)
									_this.editor.selection.SetAfter(nodeToSetCarret);
							}, 10);
						}
						else
						{

							var
								tmpClass = "_bx-editor-temp-" + Math.round(Math.random() * 1000000),
								invisText,
								isEmpty,
								textContent;

							// Create LINKS
							_this.actions.formatInline.exec(action, value, "A", false, tmpClass);

							if (_this.document.querySelectorAll)
							{
								links = _this.document.querySelectorAll("A." + tmpClass);
							}
							else
							{
								links = [];
							}

							for (i = 0; i < links.length; i++)
							{
								link = links[i];
								if (link)
								{
									applyAttributes(link, params);
								}
							}

							nodeToSetCarret = link; // last link

							if (links.length === 1)
							{
								textContent = _this.editor.util.GetTextContent(link);
								isEmpty = textContent === "" || textContent === _this.editor.INVISIBLE_SPACE;

								if (textContent != params.text)
								{
									_this.editor.util.SetTextContent(link, params.text || params.href);
								}

								if (link.querySelector && !link.querySelector("*") && isEmpty) // Link is empty
								{
									_this.editor.util.SetTextContent(link, params.text || params.href);
								}
							}

							if (link)
							{
								if (link.nextSibling && link.nextSibling.nodeType == 3 && _this.editor.util.IsEmptyNode(link.nextSibling))
								{
									invisText = link.nextSibling;
								}
								else
								{
									invisText = _this.editor.util.GetInvisibleTextNode();
								}
								_this.editor.util.InsertAfter(invisText, link);
								nodeToSetCarret = invisText;
							}

							if (nodeToSetCarret)
								_this.editor.selection.SetAfter(nodeToSetCarret);

							setTimeout(function()
							{
								if (nodeToSetCarret)
									_this.editor.selection.SetAfter(nodeToSetCarret);
							}, 10);
						}
					}
				},

				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "a");
				},

				value: BX.DoNothing
			};
		},

		GetRemoveLink: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					_this.editor.iframeView.Focus();

					var
						i, link, links;

					if (value && typeof value == 'object')
					{
						links = value;
					}
					else
					{
						links = _this.actions.formatInline.state(action, value, "a");
					}

					if (links)
					{
						// Selection contains links
						_this.editor.selection.ExecuteAndRestoreSimple(function()
						{
							for (i = 0; i < links.length; i++)
							{
								link = links[i];
								if (link)
								{
									_this.editor.util.ReplaceWithOwnChildren(links[i]);
								}
							}
						});
					}
				},

				state: function(action, value)
				{
					//return _this.actions.formatInline.state(action, value, "a");
				},
				value: BX.DoNothing
			};
		},

		GetInsertHTML: function()
		{
			var _this = this;

			return {
				exec: function(action, html)
				{
					_this.editor.iframeView.Focus();

					if (_this.IsSupportedByBrowser(action))
						_this.document.execCommand(action, false, html);
					else
						_this.editor.selection.InsertHTML(html);
				},

				state: function()
				{
					return false;
				},

				value: BX.DoNothing
			};
		},

		GetInsertImage: function()
		{
			var
				ATTRIBUTES = ['title', 'alt', 'width', 'height', 'align'],
				_this = this;

			return {
				exec: function(action, value)
				{
					if (value.src == '')
						return;

					value.src = _this.editor.util.spaceUrlEncode(value.src);

					// Only for bbCode == true
					if (_this.editor.bbCode && _this.editor.synchro.IsFocusedOnTextarea())
					{
						_this.editor.textareaView.Focus();
						var size = '';
						if (value.width)
							size += ' WIDTH=' + parseInt(value.width);
						if (value.height)
							size += ' HEIGHT=' + parseInt(value.height);
						var imgHtml = "[IMG" + size + "]" + value.src + "[/IMG]";
						_this.editor.textareaView.WrapWith(false, false, imgHtml);
						return;
					}

					_this.editor.iframeView.Focus();
					var
						params = (value && typeof(value) === "object") ? value : {src: value},
						image = value.image || _this.actions.insertImage.state(action, value),
						invisText, sibl;

					function applyAttributes(image, params)
					{
						var attr, appAttr;
						image.removeAttribute("class");
						image.setAttribute('data-bx-orig-src', params.src || '');

						for (attr in params)
						{
							if (params.hasOwnProperty(attr) && BX.util.in_array(attr, ATTRIBUTES))
							{
								if (params[attr] == '' || params[attr] == undefined)
								{
									image.removeAttribute(attr);
								}
								else
								{
									appAttr = image.getAttribute('data-bx-app-ex-' + attr);
									if (!appAttr || _this.editor.phpParser.AdvancedPhpGetFragmentByCode(appAttr, true) != params[attr])
									{
										image.setAttribute(attr, params[attr]);
										if (appAttr)
										{
											image.removeAttribute('data-bx-app-ex-' + attr);
										}
									}
								}
							}
						}

						if (params.className)
						{
							image.className = params.className;
						}

						appAttr = image.getAttribute('data-bx-app-ex-src');
						if (!appAttr || _this.editor.phpParser.AdvancedPhpGetFragmentByCode(appAttr, true) != params.src)
						{
							image.src = params.src || '';
							if (appAttr)
							{
								image.removeAttribute('data-bx-app-ex-src');
							}
						}

					}

					if (!image)
					{
						image = _this.document.createElement('IMG');
						applyAttributes(image, params);
						_this.editor.selection.InsertNode(image);
					}
					else
					{
						applyAttributes(image, params);
					}

					var
						nodeToSetCarret = image,
						parentLink = (image.parentNode && image.parentNode.nodeName == 'A') ? image.parentNode : null;

					if (params.link)
					{
						if (parentLink)
						{
							// Just change url
							parentLink.href = params.link;
						}
						else
						{
							// Add surrounding link
							parentLink = _this.document.createElement('A');
							parentLink.href = params.link;
							image.parentNode.insertBefore(parentLink, image);
							parentLink.appendChild(image);
						}
						nodeToSetCarret = parentLink;
					}
					else if (parentLink)
					{
						// Remove parent link
						_this.editor.util.ReplaceWithOwnChildren(parentLink);
					}

					// For IE it's impssible to set the caret after an <img> if it's the lastChild in the document
					if (BX.browser.IsIE())
					{
						_this.editor.selection.SetAfter(image);
						sibl = image.nextSibling;
						if (sibl && sibl.nodeType == 3 && _this.editor.util.IsEmptyNode(sibl))
							invisText = sibl;
						else
							invisText = _this.editor.util.GetInvisibleTextNode();
						_this.editor.selection.InsertNode(invisText);
						nodeToSetCarret = invisText;
					}

					_this.editor.selection.SetAfter(nodeToSetCarret);
					_this.editor.util.Refresh();
				},

				state: function()
				{
					var
						selectedNode,
						text,
						selectedImg;

					if (!_this.editor.util.DocumentHasTag(_this.document, 'IMG'))
						return false;

					selectedNode = _this.editor.selection.GetSelectedNode();
					if (!selectedNode)
						return false;

					if (selectedNode.nodeName === 'IMG')
						return selectedNode;

					if (selectedNode.nodeType !== 1 /* element node */)
						return false;

					text = BX.util.trim(_this.editor.selection.GetText());
					if (text && text != _this.editor.INVISIBLE_SPACE)
						return false;

					selectedImg = _this.editor.selection.GetNodes(1, function(node)
					{
						return node.nodeName === "IMG";
					});

					// Works only for one image
					if (selectedImg.length !== 1)
						return false;

					return selectedImg[0];
				},

				value: BX.DoNothing
//				value: function(composer) {
//					var image = this.state(composer);
//					return image && image.src;
//				}
			};
		},

		GetInsertLineBreak: function()
		{
			var
				_this = this,
				br = "<br>" + (BX.browser.IsOpera() ? " " : "");

			return {
				exec: function(action)
				{
					if (_this.IsSupportedByBrowser(action))
					{
						_this.document.execCommand(action, false, null);
					}
					else
					{
						_this.actions.insertHTML.exec("insertHTML", br);
					}

					if (BX.browser.IsChrome() || BX.browser.IsSafari() || BX.browser.IsIE10())
					{
						_this.editor.selection.ScrollIntoView();
					}
				},

				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetInsertHr: function()
		{
			var
				_this = this,
				html = "<hr>" + (BX.browser.IsOpera() ? " " : "");

			return {
				exec: function(action)
				{
					_this.actions.insertHTML.exec("insertHTML", html);
					if (BX.browser.IsChrome() || BX.browser.IsSafari() || BX.browser.IsIE10())
					{
						_this.editor.selection.ScrollIntoView();
					}
				},

				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetInsertTable: function()
		{
			var
				_this = this,
				ATTRIBUTES = ['title', 'id', 'border', 'cellSpacing', 'cellPadding', 'align'];

			function replaceTdByTh(td)
			{
				var
				//i, attribute,
					th = _this.document.createElement('TH');

				while (td.firstChild)
					th.appendChild(td.firstChild);

				//for (i = 0; i < td.attributes.length; i++)
				//{
				//	attribute = td.attributes[i];
				//	th.setAttribute(td.getAttribute());
				//}
				_this.editor.util.ReplaceNode(td, th);
			}

			function replaceThByTd(th)
			{
				var
					td = _this.document.createElement('TD');

				while (th.firstChild)
					td.appendChild(th.firstChild);

				_this.editor.util.ReplaceNode(th, td);
			}

			function applyAttributes(table, params)
			{
				var attr;
				table.removeAttribute("class");

				for (attr in params)
				{
					if (params.hasOwnProperty(attr) && BX.util.in_array(attr, ATTRIBUTES))
					{
						if (params[attr] === '' || params[attr] == undefined)
						{
							table.removeAttribute(attr);
						}
						else
						{
							table.setAttribute(attr, params[attr]);
						}
					}
				}
				if (params.className)
				{
					table.className = params.className;
				}

				table.removeAttribute("data-bx-no-border");
				if (table.getAttribute("border") == 0 || !table.getAttribute("border"))
				{
					table.removeAttribute("border");
					table.setAttribute("data-bx-no-border", "Y");
				}

				if (params.width)
				{
					if (parseInt(params.width) == params.width)
					{
						params.width = params.width + 'px';
					}

					if (table.getAttribute("width"))
					{
						table.setAttribute("width", params.width);
					}
					else
					{
						table.style.width = params.width;
					}
				}

				if (params.height)
				{
					if (parseInt(params.height) == params.height)
					{
						params.height = params.height + 'px';
					}

					if (table.getAttribute("height"))
					{
						table.setAttribute("height", params.height);
					}
					else
					{
						table.style.height = params.height;
					}
				}

				var r, c, pCell;
				for(r = 0; r < table.rows.length; r++)
				{
					for(c = 0; c < table.rows[r].cells.length; c++)
					{
						pCell = table.rows[r].cells[c];
						if (
							((params.headers == 'top' || params.headers == 'topleft') && r == 0)
								||
								((params.headers == 'left' || params.headers == 'topleft') && c == 0)
							)
						{
							replaceTdByTh(pCell);
						}
						else if (pCell.nodeName == 'TH')
						{
							replaceThByTd(pCell);
						}
					}
				}

				var pCaption = BX.findChild(table, {tag: 'CAPTION'}, false);
				if (params.caption)
				{
					if (pCaption)
					{
						pCaption.innerHTML = BX.util.htmlspecialchars(params.caption);
					}
					else
					{
						pCaption = _this.document.createElement('CAPTION');
						pCaption.innerHTML = BX.util.htmlspecialchars(params.caption);
						table.insertBefore(pCaption, table.firstChild);
					}
				}
				else if (pCaption)
				{
					BX.remove(pCaption);
				}
			}

			return {
				exec: function(action, params)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						if (!params || !params.rows || !params.cols)
						{
							return false;
						}

						_this.editor.iframeView.Focus();
						var table = params.table || _this.actions.insertTable.state(action, params);

						params.rows = parseInt(params.rows) || 1;
						params.cols = parseInt(params.cols) || 1;

						if (params.align == 'left' || _this.editor.bbCode)
						{
							params.align = '';
						}

						if (!table)
						{
							table = _this.document.createElement('TABLE');

							var
								tbody = table.appendChild(_this.document.createElement('TBODY')),
								r, c, row, cell;

							params.rows = parseInt(params.rows) || 1;
							params.cols = parseInt(params.cols) || 1;

							for(r = 0; r < params.rows; r++)
							{
								row = tbody.insertRow(-1);
								for(c = 0; c < params.cols; c++)
								{
									cell = BX.adjust(row.insertCell(-1), {html: '&nbsp;'});
								}
							}
							applyAttributes(table, params);
							_this.editor.selection.InsertNode(table);

							var nextNode = _this.editor.util.GetNextNotEmptySibling(table);
							if (!nextNode)
							{
								_this.editor.util.InsertAfter(BX.create('BR', {}, _this.document), table);
							}

							if (nextNode && nextNode.nodeName == 'BR' && !nextNode.nextSibling)
							{
								_this.editor.util.InsertAfter(_this.editor.util.GetInvisibleTextNode(), nextNode);
							}
						}
						else
						{
							applyAttributes(table, params);
						}

						var nodeToSetCarret = table.rows[0].cells[0].firstChild;
						if (nodeToSetCarret)
						{
							_this.editor.selection.SetAfter(nodeToSetCarret);
						}

						// For Firefox refresh white markers
						setTimeout(function(){_this.editor.util.Refresh(table);}, 10);
					}
					else // bbcode + textarea
					{
						_this.editor.textareaView.Focus();
						var
							tbl = '',
							i, j,
							cellHTML = _this.editor.INVISIBLE_SPACE;

						if (params.rows > 0 && params.cols > 0)
						{
							tbl += "[TABLE]\n";
							for(i = 0; i < params.rows; i++)
							{
								tbl += "\t[TR]\n";
								for(j = 0; j < params.cols; j++)
								{
									tbl += "\t\t[TD]" + cellHTML + "[/TD]\n";
								}
								tbl += "\t[/TR]\n";
							}
							tbl += "[/TABLE]\n";
						}

						_this.editor.textareaView.WrapWith(false, false, tbl);
					}
				},

				state: function(action, value)
				{
					var
						selectedNode,
						selectedTable;

					if (!_this.editor.util.DocumentHasTag(_this.document, 'TABLE'))
					{
						return false;
					}

					selectedNode = _this.editor.selection.GetSelectedNode();
					if (!selectedNode)
					{
						return false;
					}

					if (selectedNode.nodeName === 'TABLE')
					{
						return selectedNode;
					}

					if (selectedNode.nodeType !== 1 /* element node */)
					{
						return false;
					}

					selectedTable = _this.editor.selection.GetNodes(1, function(node)
					{
						return node.nodeName === "TABLE";
					});

					// Works only for one table
					if (selectedTable.length !== 1)
					{
						return false;
					}

					return selectedTable[0];
				},
				value: BX.DoNothing
			};
		},

		//
		GetInsertList: function(params)
		{
			var _this = this;
			var
				bOrdered = !!params.bOrdered,
				listTag = bOrdered ? 'OL' : 'UL',
				otherListTag = bOrdered ? 'UL' : 'OL';

			function getNextNotEmptySibling(node)
			{
				var nextSibling = node.nextSibling;
				while (nextSibling && nextSibling.nodeType == 3 && _this.editor.util.IsEmptyNode(nextSibling, true))
				{
					nextSibling = nextSibling.nextSibling;
				}
				return nextSibling;
			}

			function removeList(list)
			{
				if (list.nodeName !== "MENU" && list.nodeName !== "UL" && list.nodeName !== "OL")
				{
					return;
				}

				var
					frag = _this.document.createDocumentFragment(),
					previousSibling = list.previousSibling,
					firstChild,
					lastChild,
					bAppendBr,
					listItem;

				if (previousSibling && !_this.editor.util.IsBlockElement(previousSibling) && !_this.editor.util.IsEmptyNode(previousSibling, true))
				{
					frag.appendChild(_this.document.createElement("BR"));
				}

				while (listItem = list.firstChild)
				{
					lastChild = listItem.lastChild;
					while (firstChild = listItem.firstChild)
					{
						// Custom bullit items
						if (firstChild.nodeName == 'I' && firstChild.innerHTML == '' && firstChild.className != '')
						{
							BX.remove(firstChild);
							firstChild = listItem.firstChild;
							if (!firstChild)
								break;
						}

						bAppendBr = firstChild === lastChild &&
							!_this.editor.util.IsBlockElement(firstChild) &&
							firstChild.nodeName !== "BR";

						frag.appendChild(firstChild);
						if (bAppendBr)
						{
							frag.appendChild(_this.document.createElement("BR"));
						}
					}
					listItem.parentNode.removeChild(listItem);
				}

				var nextSibling = _this.editor.util.GetNextNotEmptySibling(list);
				if (nextSibling && nextSibling.nodeName == 'BR' && frag.lastChild && frag.lastChild.nodeName == 'BR')
				{
					// Remove unnecessary BR
					BX.remove(frag.lastChild);
				}

				list.parentNode.replaceChild(frag, list);
			}

			function convertToList(element, listType)
			{
				if (!element || !element.parentNode)
					return false;

				var nodeName = element.nodeName.toUpperCase();

				if (nodeName === "UL" || nodeName === "OL" || nodeName === "MENU")
				{
					return element;
				}

				var
					list = _this.document.createElement(listType),
					childNode,
					currentLi;

				while (element.firstChild)
				{
					currentLi = currentLi || list.appendChild(_this.document.createElement("li"));
					childNode = element.firstChild;

					if (_this.editor.util.IsBlockElement(childNode))
					{
						currentLi = currentLi.firstChild ? list.appendChild(_this.document.createElement("li")) : currentLi;
						currentLi.appendChild(childNode);
						currentLi = null;
						continue;
					}

					if (childNode.nodeName === "BR")
					{
						currentLi = currentLi.firstChild ? null : currentLi;
						element.removeChild(childNode);
						continue;
					}

					currentLi.appendChild(childNode);
				}

				element.parentNode.replaceChild(list, element);
				return list;
			}

			function isListNode(n)
			{
				return n.nodeName == 'OL' || n.nodeName == 'UL' || n.nodeName == 'MENU'
			}

			function getSelectedList(tag, node)
			{
				if (!node)
				{
					node = _this.editor.selection.GetSelectedNode();
				}

				if (!node || node.nodeName == 'BODY')
				{
					var
						range = _this.editor.selection.GetRange(),
						commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);

					if (commonAncestor && isListNode(commonAncestor))
					{
						node = commonAncestor;
					}
					else
					{
						var
							i, onlyList = true, list, parentList,
							nodes = range.getNodes([1]),
							l = nodes.length;

						if (commonAncestor)
						{
							list = parentList = BX.findParent(commonAncestor, isListNode, _this.document.body);
						}

						if (!parentList)
						{
							for (i = 0; i < l; i++)
							{
								parentList = BX.findParent(nodes[i], isListNode, commonAncestor);
								if (!parentList || (list && parentList != list))
								{
									onlyList = false;
									break;
								}
								list = parentList;
							}
						}

						if (!list)
						{
							var
								_list = false;
							for (i = 0; i < l; i++)
							{
								if (isListNode(nodes[i]))
								{
									_list = nodes[i];
									break;
								}
							}

							if (_list)
							{
								var ok = true;
								for (i = 0; i < l; i++)
								{
									if (nodes[i] == _list ||
										BX.findParent(nodes[i], function(n){return n === _list;}, _this.document.body) ||
										nodes[i].nodeName == 'BR' ||
										_this.editor.util.IsEmptyNode(nodes[i])
										)
									{
									}
									else
									{
										ok = false;
										break;
									}
								}

								if (ok)
								{
									list = _list;
								}
							}
						}

						if (list)
						{
							node = list;
						}
					}
				}

				return node && node.nodeName == tag ? node : BX.findParent(node, {tagName: tag}, _this.document.body);
			}


			function customBullit(list, bullitParams)
			{
				if (!list)
					return false;

				if (!bullitParams)
					bullitParams = {tag: 'I', remove: true};

				var i, node, fch, doc = list.ownerDocument;
				for (i = 0; i < list.childNodes.length; i++)
				{
					node = list.childNodes[i];
					if (node && node.nodeType == 1 && node.nodeName == 'LI')
					{
						fch = node.firstChild;
						// Custom bullit already here
						if (fch.nodeName == bullitParams.tag && fch.innerHTML == '')
						{
							if (bullitParams.remove)
								BX.remove(fch);
							else
								fch.className = bullitParams.className;
						}
						else // Add custom bullits to the list
						{
							node.insertBefore(BX.create(bullitParams.tag, {props: {className: bullitParams.className}}, doc), fch);
						}
					}
				}
			}

			function getCustomBullitClass(list)
			{
				if (!list)
					return false;

				var i, node, fch;
				for (i = 0; i < list.childNodes.length; i++)
				{
					node = list.childNodes[i];
					if (node && node.nodeType == 1 && node.nodeName == 'LI')
					{
						fch = node.firstChild;
						if (fch && fch.nodeName == 'I' && fch.innerHTML == '' && fch.className !== '')
						{
							return fch.className;
						}
					}
				}

				return false;
			}

			function checkCustomBullitList(list, bullitClass, setFocusAfterLastBullit)
			{
				if (list && (list.nodeName == 'UL' || list.nodeName == 'OL'))
				{
					var i, node, fch, doc = list.ownerDocument, lastBullit;
					for (i = 0; i < list.childNodes.length; i++)
					{
						node = list.childNodes[i];
						if (node && node.nodeType == 1 && node.nodeName == 'LI' && node.firstChild)
						{
							fch = node.firstChild;
							if (fch.nodeName == 'I' && fch.innerHTML == '' && fch.className !== '')
							{
								if (!bullitClass)
									bullitClass = fch.className;
							}
							else if (fch.nodeName == 'I' && fch.innerHTML == '' && bullitClass)
							{
								fch.className = bullitClass;
								lastBullit = fch;
							}
							else if(fch && bullitClass)
							{
								lastBullit = node.insertBefore(BX.create('I', {props: {className: bullitClass}}, doc), fch);
							}
						}
					}

					if (setFocusAfterLastBullit && lastBullit)
						_this.editor.selection._MoveCursorAfterNode(lastBullit);
				}
			}

			return {
				exec: function(action, params)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						var range = _this.editor.selection.GetRange();

						if (_this.IsSupportedByBrowser(action) && range.collapsed)
						{
							_this.document.execCommand(action, false, null);
						}
						else
						{
							var
								selectedNode = _this.editor.selection.GetSelectedNode(),
								list = getSelectedList(listTag, selectedNode),
								otherList = getSelectedList(otherListTag, selectedNode),
								isEmpty,
								tempElement;

							if (list)
							{
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									removeList(list);
								});
							}
							else if (otherList)
							{
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									_this.editor.util.RenameNode(otherList, listTag);
								});
							}
							else
							{
								tempElement = _this.document.createElement("span");
								_this.editor.selection.Surround(tempElement);
								isEmpty = tempElement.innerHTML === "" || tempElement.innerHTML === _this.editor.INVISIBLE_SPACE;
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									// mantis #54087
									var i, spans = tempElement.getElementsByTagName('SPAN');
									for (i = spans.length - 1; i >= 0; i--)
									{
										// Clean spans without classes styles and id's
										if (!spans[i].className && !spans[i].id && !spans[i].style.cssText)
										{
											_this.editor.util.ReplaceWithOwnChildren(spans[i]);
										}
									}
									list = convertToList(tempElement, listTag);
								});

								if (list)
								{
									var i = 0, item;
									while (i < list.childNodes.length)
									{
										item = list.childNodes[i];
										if (item.nodeName == 'LI')
										{
											if (_this.editor.util.IsEmptyNode(item, true, true))
											{
												BX.remove(item);
												continue;
											}
											i++;
										}
										else if (item.nodeType == 1)
										{
											BX.remove(item);
										}
									}

									// Mantis: #53646, #53820
									var prevSib = _this.editor.util.GetPreviousNotEmptySibling(list);
									if (prevSib && (
										prevSib.nodeName == 'BLOCKQUOTE' ||
										prevSib.nodeName == 'PRE' ||
										prevSib.nodeName == 'UL' ||
										prevSib.nodeName == 'OL'
										)
										&& list.childNodes[0] && BX.findChild(list.childNodes[0], {tag: prevSib.nodeName}))
									{
										if (BX.util.trim(_this.editor.util.GetTextContent(prevSib)) == '')
										{
											BX.remove(prevSib);
										}
									}
								}

								if (isEmpty && list && list.querySelector)
								{
									_this.editor.selection.SelectNode(list.querySelector("li"));
								}
							}
						}
					}
					else // bbcode + textarea
					{
						if (params && params.items)
						{
							_this.editor.textareaView.Focus();
							var lst = '[LIST' + (bOrdered ? '=1' : '')+ ']\n', it;

							for(it = 0; it < params.items.length; it++)
							{
								lst += "\t[*]" + params.items[it] + "\n";
							}
							lst += "[/LIST]\n";

							_this.editor.textareaView.WrapWith(false, false, lst);
						}
					}
				},

				state: function()
				{
					return getSelectedList(listTag) || false;
				},

				value: BX.DoNothing,

				customBullit: customBullit,
				getCustomBullitClass: getCustomBullitClass,
				checkCustomBullitList: checkCustomBullitList
			};
		},

		GetAlign: function()
		{
			var
				CLASS_NAME_TMP = 'bx-align-tmp',
				LIST_ALIGN_ATTR = 'data-bx-checked-align-list',
				DEFAULT_VALUE = 'left',
				TABLE_NODES = {TD: 1, TR: 1, TH: 1, TABLE: 1, TBODY: 1, CAPTION: 1, COL: 1, COLGROUP: 1, TFOOT: 1, THEAD: 1},
				ALIGN_NODES = {IMG: 1, P: 1, DIV: 1, TABLE: 1, H1: 1, H2: 1, H3: 1, H4: 1, H5: 1, H6: 1},
				_this = this;

			function checkNodeAlign(n)
			{
				var
					nodeName = n.nodeName, align,
					res = false;

				if (n.nodeType === 1)
				{
					align = n.style.textAlign;
					if (n.style.textAlign)
					{
						res = {node: n, style: align};
					}

					if (ALIGN_NODES[nodeName])
					{
						align = n.getAttribute('align');
						if (align)
						{
							if (res)
							{
								res.attribute = align;
							}
							else
							{
								res = {node: n, attribute: align};
							}
						}
					}
				}
				return res;
			}

			function isCell(n)
			{
				return n && (n.nodeName == 'TD' || n.nodeName == 'TH');
			}

			function alignTableNode(n, value)
			{
				n.setAttribute('data-bx-tmp-align', value);
				n.style.textAlign = value;
			}

			function alignTable(n, value)
			{
				if (value == 'left' || value == 'justify' || _this.editor.bbCode)
				{
					n.removeAttribute('align');
				}
				else
				{
					n.setAttribute('align', value);
				}
			}

			function checkAlignTable(table, value)
			{
				var
					ths,
					i, res = true,
					tds = table.getElementsByTagName("TD");

				for (i = 0; i < tds.length; i++)
				{
					if (tds[i].getAttribute('data-bx-tmp-align') != value)
					{
						res = false;
						break;
					}
				}

				if (res)
				{
					ths = table.getElementsByTagName("TH");
					for (i = 0; i < ths.length; i++)
					{
						if (ths[i].getAttribute('data-bx-tmp-align') != value)
						{
							res = false;
							break;
						}
					}
				}

				if (res)
				{
					alignTable(table, value);
				}
			}

			function createAlignNodeInside(node, value)
			{
				var alignNode = BX.create("DIV", {style: {textAlign: value}, html: node.innerHTML}, _this.editor.GetIframeDoc());
				node.innerHTML = '';
				node.appendChild(alignNode);
				return alignNode;
			}

			function createAlignNodeOutside(node, value)
			{
				var alignNode = BX.create("DIV", {style: {textAlign: value}}, _this.editor.GetIframeDoc());
				node.parentNode.insertBefore(alignNode, node);
				alignNode.appendChild(node);
				return alignNode;
			}

			function checkListItemsAlign(list, item, value)
			{
				var
					doc = _this.editor.GetIframeDoc(),
					bb = _this.editor.bbCode;
				if (!list && item)
				{
					list = BX.findParent(item, function(n)
					{
						return n.nodeName == 'OL' || n.nodeName == 'UL' || n.nodeName == 'MENU';
					}, doc);
				}

				if (list && !list.getAttribute(LIST_ALIGN_ATTR))
				{
					var
						i, clean = true,
						lis = list.getElementsByTagName('LI');

					for (i = 0; i < lis.length; i++)
					{
						if (lis[i].style.textAlign !== value)
						{
							clean = false;
							break;
						}
					}

					if (bb)
					{
						list.style.textAlign = '';
						if (list.style.cssText == '')
						{
							list.removeAttribute('style');
						}
						cleanListItemsAlign(list);

						createAlignNodeOutside(list, value);
					}
					else if (clean)
					{
						list.style.textAlign = value;
						cleanListItemsAlign(list);
					}

					list.setAttribute(LIST_ALIGN_ATTR, 'Y');
					return list;
				}

				return false;
			}

			function cleanListItemsAlign(list)
			{
				var i, lis = list.getElementsByTagName('LI');
				for (i = 0; i < lis.length; i++)
				{
					lis[i].style.textAlign = '';
					if (lis[i].style.cssText == '')
					{
						lis[i].removeAttribute('style');
					}
				}
			}

			function pushTable(arr, newTable)
			{
				if (newTable && newTable.nodeName == 'TABLE')
				{
					var i, found = false;
					for (i = 0; i < arr.length; i++)
					{
						if (arr[i] == newTable)
						{
							found = true;
							break;
						}
					}
					if (!found)
					{
						arr.push(newTable);
					}
				}
				return arr;
			}

			return {
				exec: function(action, value)
				{
					var res;

					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						var
							i,
							tagName = 'P',
							range = _this.editor.selection.GetRange(),
							blockElement = false,
							tableElement = false,
							listNode = false,
							bookmark = _this.editor.selection.GetBookmark(),
							selectedNode = _this.editor.selection.GetSelectedNode();

						if (selectedNode)
						{
							if (_this.editor.util.IsBlockNode(selectedNode))
							{
								blockElement = selectedNode;
							}
							else if (selectedNode.nodeType == 1 && TABLE_NODES[selectedNode.nodeName])
							{
								tableElement = selectedNode;
								res = true;
								setTimeout(function(){
									_this.editor.selection.SelectNode(tableElement);
									if (tableElement.nodeName == 'TABLE')
									{
										alignTable(tableElement, value);
									}
								}, 10);
							}
							else
							{
								if (selectedNode.nodeName == 'LI')
								{
									listNode = selectedNode;
								}
								else if (selectedNode.nodeName == 'OL' || selectedNode.nodeName == 'UL' || selectedNode.nodeName == 'MENU')
								{
									if (_this.editor.bbCode)
									{
										createAlignNodeOutside(selectedNode, value);
										selectedNode.style.textAlign = '';
									}
									else
									{
										selectedNode.style.textAlign = value;
									}
									res = true;
									cleanListItemsAlign(selectedNode);
									setTimeout(function(){_this.editor.selection.SelectNode(selectedNode);}, 10);
								}
								else
								{
									listNode = BX.findParent(selectedNode, function(n)
									{
										return n.nodeName == 'LI';
									}, _this.document.body);

								}

								if (listNode)
								{
									if (_this.editor.bbCode)
									{
										createAlignNodeInside(listNode, value);
										listNode.style.textAlign = '';
									}
									else
									{
										listNode.style.textAlign = value;
									}
									res = true;
									setTimeout(function(){_this.editor.selection.SelectNode(listNode);}, 10);
								}
								else
								{
									blockElement = BX.findParent(selectedNode, function(n)
									{
										return _this.editor.util.IsBlockNode(n) && !_this.actions.quote.checkNode(n);
									}, _this.document.body);
								}
							}
						}
						else
						{
							// In Chrome when we select some parts of table we apply align for tds, but if entire table was selected - we trying to continue align other elements.
							var
								tables = [],
								tableIsHere = false,
								arLists = [], arLis = [],
								nodes = range.getNodes([1]);

							for (i = 0; i < nodes.length; i++)
							{
								if (isCell(nodes[i]))
								{
									tables = pushTable(tables, BX.findParent(nodes[i], {tagName: 'TABLE'}));
									alignTableNode(nodes[i], value);
									res = true;
								}

								if (nodes[i].nodeName == 'TABLE')
								{
									tables = pushTable(tables, nodes[i]);
									tableIsHere = true;
								}
								else if (nodes[i].nodeName == 'OL' || nodes[i].nodeName == 'UL' || nodes[i].nodeName == 'MENU')
								{
									nodes[i].style.textAlign = value;
									arLists.push(nodes[i]);
									res = true;
								}
								else if (nodes[i].nodeName == 'LI')
								{
									nodes[i].style.textAlign = value;
									res = true;
									arLis.push(nodes[i]);
								}
							}

							for (i = 0; i < tables.length; i++)
							{
								checkAlignTable(tables[i], value);
							}

							// Example: ctra+a was pressed
							if (res)
							{
								var commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);
								if (commonAncestor && commonAncestor.nodeName == 'BODY')
								{
									res = false;
								}
							}

							for (i = 0; i < arLists.length; i++)
							{
								cleanListItemsAlign(arLists[i]);
							}
							var arCheckedLists = [], checkedList;

							for (i = 0; i < arLis.length; i++)
							{
								checkedList = checkListItemsAlign(false, arLis[i], value);
								if (checkedList)
								{
									arCheckedLists.push(checkedList);
								}
							}
							for (i = 0; i < arCheckedLists.length; i++)
							{
								arCheckedLists[i].removeAttribute(LIST_ALIGN_ATTR);
							}
						}

						if (!res)
						{
							// Simple situation - we inside of the block element - just add text-align to it...
							if (blockElement)
							{
								if (_this.editor.bbCode)
								{
									createAlignNodeInside(blockElement, value);
									blockElement.style.textAlign = '';
								}
								else
								{
									// Accept all block tags except DIVs
									tagName = blockElement.tagName != 'DIV' ? blockElement.tagName : 'P';
									res = _this.actions.formatBlock.exec('formatBlock', tagName, null, {textAlign: value});
								}
								_this.editor.util.Refresh(blockElement);
							}
							else if(tableElement)
							{
								if (isCell(tableElement))
								{
									alignTableNode(tableElement, value);
								}
								else
								{
									var
										tds = BX.findChild(tableElement, isCell, true, true),
										ths = BX.findChild(tableElement, isCell, true, true);

									for (i = 0; i < tds.length; i++)
									{
										alignTableNode(tds[i], value);
									}
									for (i = 0; i < ths.length; i++)
									{
										alignTableNode(ths[i], value);
									}
								}
							}
							else if (range.collapsed)
							{
								res = _this.actions.formatBlock.exec('formatBlock', 'P', CLASS_NAME_TMP, {textAlign: value});

								// Selection workaround mantis:53937
								var
									focusNode,
									alignNodes = _this.document.querySelectorAll("." + CLASS_NAME_TMP);

								for (i = 0; i <= alignNodes.length; i++)
								{
									BX.removeClass(alignNodes[i], CLASS_NAME_TMP);
									if (i == 0)
									{
										focusNode = alignNodes[i].firstNode;
										if (!focusNode)
											focusNode = alignNodes[i].appendChild(_this.editor.util.GetInvisibleTextNode());
										setTimeout(function()
										{
											if (focusNode)
												_this.editor.selection.SetAfter(focusNode);
										}, 100);
									}
								}
							}
							else
							{
								// Image selected
								var image = _this.actions.insertImage.state();

								if (!res && false)
								{
									var onlyPar = true;
									nodes = range.getNodes([1]);
									if (nodes && nodes.length > 0)
									{
										for (i = 0; i < nodes.length; i++)
										{
											if (nodes[i].nodeName == "P")
											{
												nodes[i].style.textAlign = value;
											}
											else
											{
												onlyPar = false;
											}
										}
										res = onlyPar;
									}
								}

								// Mixed content
								if (!res)
								{
									tagName = _this.editor.bbCode ? 'DIV' : 'P';

									res = _this.actions.formatBlock.exec('formatBlock', tagName, null, {textAlign: value}, {leaveChilds: true, splitBlock: true});
									if (res && typeof res == 'object' && res.nodeName == tagName)
									{
										var
											iter = 0, maxIter = 2000, prev,
											child, newPar, createNewPar = false;

										// mantis:#54026
										if (res.firstChild && res.firstChild.nodeName == 'BLOCKQUOTE')
										{
											prev = _this.editor.util.GetPreviousNotEmptySibling(res);
											if (prev && prev.nodeName == 'BLOCKQUOTE' && _this.editor.util.IsEmptyNode(prev))
											{
												BX.remove(prev);
											}
										}

										i = 0;

										while (i < res.childNodes.length || iter > maxIter)
										{
											child = res.childNodes[i];
											if(_this.editor.util.IsBlockNode(child))
											{
												child.style.textAlign = value;
												createNewPar = true;
												i++;
											}
											else
											{
												if (!newPar || createNewPar)
												{
													newPar = _this.document.createElement(tagName);
													newPar.style.textAlign = value;
													res.insertBefore(newPar, child);
													i++;
												}

												newPar.appendChild(child);
												createNewPar = false;
											}
											iter++;
										}

										// Clean useless <p></p> before and after
										if (res.previousSibling && res.previousSibling.nodeName == "P" && _this.editor.util.IsEmptyNode(res.previousSibling, true, true))
										{
											BX.remove(res.previousSibling);
										}
										if (res.nextSibling && res.nextSibling.nodeName == "P" && _this.editor.util.IsEmptyNode(res.nextSibling, true, true))
										{
											BX.remove(res.nextSibling);
										}
										_this.editor.util.ReplaceWithOwnChildren(res);

										// Selection workaround mantis:53937
										setTimeout(function()
										{
											if (newPar)
												_this.editor.selection.SelectNode(newPar);
										}, 100);
									}
								}

								if (image)
								{
									// For Firefox
									_this.editor.util.Refresh(image);
								}
							}
						}

						setTimeout(function(){_this.editor.selection.SetBookmark(bookmark);}, 10);
					}
					else // bbcode + textarea
					{
						if (value)
						{
							res = _this.actions.formatBbCode.exec(action, {tag: value.toUpperCase()});
						}
					}

					return res;
				},
				state: function(action, value)
				{
					var
						alignRes, node,
						selectedNode = _this.editor.selection.GetSelectedNode();

					if (selectedNode)
					{
						alignRes = checkNodeAlign(selectedNode);
						if (!alignRes)
						{
							node = BX.findParent(selectedNode, function(n)
							{
								alignRes = checkNodeAlign(n);
								return alignRes;
							}, _this.document.body);
						}

						return {
							node: alignRes ? alignRes.node : null,
							value: alignRes ? (alignRes.style || alignRes.attribute) : DEFAULT_VALUE,
							res: alignRes
						};
					}
					else
					{
						var
							result = {node: null, value: DEFAULT_VALUE, res: true},
							range = _this.editor.selection.GetRange();
						if (!range.collapsed)
						{
							var
								alRes,
								curValue = '', i,
								nodes = range.getNodes([1]);

							for (i = 0; i < nodes.length; i++)
							{
								if (!_this.editor.util.CheckSurrogateNode(nodes[i]) &&
									nodes[i].nodeName !== 'BR' &&
									_this.editor.util.GetNodeDomOffset(nodes[i]) == 0
									)
								{
									alRes = checkNodeAlign(nodes[i]);
									value = alRes ? (alRes.style || alRes.attribute) : DEFAULT_VALUE;

									if (!curValue)
									{
										curValue = value;
									}

									if (value != curValue)
									{
										result.res = false;
										break;
									}

								}
							}
							if (result.res)
							{
								result.value = curValue;
							}
						}
						else
						{
							result.res = false;
						}

						return result;
					}
				},
				value: BX.DoNothing
			};
		},

		GetIndent: function()
		{
			var _this = this;
			return {
				exec: function(action)
				{
					// Mantis bug workaround: #59811
					var rng = _this.editor.selection.GetRange();
					if (rng && rng.collapsed && rng.endContainer == rng.startContainer && BX.util.in_array(rng.startContainer.nodeName, ['TD', 'TR', 'TH']) && arguments[1] !== rng.startContainer.nodeName)
					{
						var
							id = 'bxed_bogus_node_59811',
							focusNode = rng.startContainer.appendChild(BX.create('SPAN', {props: {id: id}, html: '&nbsp;'}, _this.document));is_text = _this.editor.util.GetInvisibleTextNode();

						if (focusNode)
						{
							rng.setStartBefore(focusNode);
							rng.setEndAfter(focusNode);
							_this.editor.selection.SetSelection(rng);

							return setTimeout(function()
							{
								_this.actions.indent.exec(action, rng.startContainer.nodeName);

								var focusNode = _this.editor.GetIframeElement(id);
								if (focusNode)
								{
									_this.editor.selection.SetAfter(focusNode);
									BX.remove(focusNode);
								}

							}, 0);
						}
					}

					if (_this.IsSupportedByBrowser(action))
					{
						_this.document.execCommand(action);
					}
					else
					{
						_this.actions.formatBlock.exec('formatBlock', 'BLOCKQUOTE');
					}

					var range = _this.editor.selection.GetRange();
					if (range)
					{
						var bqCnt = 0, i, nodes = range.getNodes([1]);

						for (i = 0; i < nodes.length; i++)
						{
							if (nodes[i].nodeName == 'BLOCKQUOTE')
							{
								bqCnt++;
								nodes[i].removeAttribute('style');
							}
						}

						if (range.startContainer)
						{
							var
								invis_text,
								bq = false,
								node = range.startContainer;
							while (node)
							{
								node = BX.findParent(node, {tag: 'BLOCKQUOTE'}, _this.document.body);
								if (node)
								{
									if (!bq)
										bq = node;
									node.removeAttribute('style');
								}
							}

							if (bq)
							{
								invis_text = _this.editor.util.GetInvisibleTextNode();
								bq.appendChild(invis_text);
								_this.editor.selection.SetAfter(invis_text);
							}
						}
					}
				},
				state: function(action, value)
				{
					var
						res = false,
						range = _this.editor.selection.GetRange();
					if (range)
					{
						var commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);
						if (commonAncestor && commonAncestor.nodeType == 1 && commonAncestor.nodeName === 'BLOCKQUOTE')
						{
							res = commonAncestor;
						}
					}
					return res;
				},
				value: BX.DoNothing
			};
		},

		GetOutdent: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					var
						i,
						attr = 'data-bx-tmp-flag',
						doc = _this.editor.GetIframeDoc(),
						blockNodes = doc.getElementsByTagName('BLOCKQUOTE');

					//if (blockNodes && blockNodes.length > 0)
					{
						var
							parNodesToClear = [],
							parNodes = doc.getElementsByTagName('P');
						for (i = 0; i < parNodes.length; i++)
						{
							parNodes[i].setAttribute(attr, 'Y');
						}
						_this.document.execCommand(action);

						parNodes = doc.getElementsByTagName('P');
						for (i = 0; i < parNodes.length; i++)
						{
							if (!parNodes[i].getAttribute(attr))
							{
								parNodesToClear.push(parNodes[i]);
							}
							else
							{
								parNodes[i].removeAttribute(attr);
							}
						}

						_this.editor.selection.ExecuteAndRestoreSimple(function()
						{
							for (i = 0; i < parNodesToClear.length; i++)
							{
								_this.actions.formatBlock.addBrBeforeAndAfter(parNodesToClear[i]);
								_this.editor.util.ReplaceWithOwnChildren(parNodesToClear[i]);
							}
						});
					}
				},
				state: function(action, value)
				{
					var
						range = _this.editor.selection.GetRange();

					return false;
				},
				value: BX.DoNothing
			};
		},

		GetFontFamily: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					var res;

					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						if (value)
							res = _this.actions.formatInline.exec(action, value, "span", {fontFamily: value});
						else // Clear fontFamily format
							res = _this.actions.formatInline.exec(action, value, "span", {fontFamily: null}, null, {bClear: true});
					}
					else // textarea + bbcode
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'FONT', value: value});
					}

					return res;
				},

				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, "span", {fontFamily: value});
				},

				value: BX.DoNothing
			};
		},

		GetFormatStyle: function()
		{
			var
				_this = this,
				styleSel = this.editor.toolbar.controls.StyleSelector,
				classList = styleSel ? styleSel.checkedClasses : [],
				tagList = styleSel ? styleSel.checkedTags : [];

			function isNodeSuitable(node)
			{
				if (node && node.nodeType == 1 && node.nodeName !== 'BODY' &&
					(
						BX.util.in_array(node.nodeName, tagList) ||
						BX.util.in_array(node.className, classList)
					))
				{
					return !_this.editor.GetBxTag(node.id).tag;
				}
				return false;
			}

			return {
				exec: function(action, value)
				{
					if (!value) // Clear font style
					{
						return _this.actions.removeFormat.exec('removeFormat');
					}
					else if (typeof value === 'string') // Tag name - H1, H2
					{
						return _this.actions.formatBlock.exec('formatBlock', value);
					}
					else if (typeof value === 'object') // class name from template-s css
					{
						// Handle font awesome list
						if (value.tag == 'UL')
						{
							var list = _this.actions.insertUnorderedList.state();
							if (list && value.className && value.className.indexOf('~~') !== -1)
							{
								var cn = value.className.split('~~');
								if (cn && cn.length >=2 )
								{
									var
										listClass = cn[0],
										bullitClass = cn[1];

									list.className = listClass;
									_this.actions.insertUnorderedList.customBullit(list, {tag: 'I', className: bullitClass, html: ''});
								}
							}
							else if (list)
							{
								list.className = value.className || '';
								_this.actions.insertUnorderedList.customBullit(list, false);
							}
						}
						else if (value.tag)
						{
							var
								className = value.className,
								tag = value.tag.toUpperCase();

							// Inline
							if (tag == 'SPAN')
							{
								//command, value, tagName, arStyle, cssClass, params)
								_this.actions.formatInline.exec(action, value, tag, false, className);
							}
							else //if (tag == 'P')
							{
								_this.actions.formatBlock.exec('formatBlock', tag, className, null, {nestedBlocks: false});
							}
						}

						if (!_this.editor.util.FirstLetterSupported())
						{
							_this.editor.parser.FirstLetterCheckNodes('', '', true);
						}
					}
				},

				state: function(action, value)
				{
					var
						result = false,
						selectedNode = _this.editor.selection.GetSelectedNode();

					if (selectedNode)
					{
						if (isNodeSuitable(selectedNode))
						{
							result = selectedNode;
						}
						else
						{
							result = BX.findParent(selectedNode, isNodeSuitable, _this.document.body);
						}
					}
					else
					{
						var
							range = _this.editor.selection.GetRange(),
							commonAncestor = _this.editor.selection.GetCommonAncestorForRange(range);

						if (isNodeSuitable(commonAncestor))
						{
							result = commonAncestor;
						}
					}

					return result;
				},
				value: BX.DoNothing
			};
		},

		GetChangeTemplate: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					_this.editor.ApplyTemplate(value);
				},

				state: function(action, value)
				{
					return _this.editor.GetTemplateId();
				},

				value: BX.DoNothing
			};
		},

		GetSelectNode: function()
		{
			var _this = this;
			return {
				exec: function(action, node)
				{
					if (!_this.editor.iframeView.IsFocused())
					{
						_this.editor.iframeView.Focus();
					}

					if (node === false || (node && node.nodeName == 'BODY')) // Select all
					{
						if (_this.IsSupportedByBrowser('SelectAll'))
						{
							_this.document.execCommand('SelectAll');
						}
						else
						{
							_this.editor.selection.SelectNode(node);
						}
					}
					else
					{
						_this.editor.selection.SelectNode(node);

					}
				},

				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetUndoRedo: function(bUndo)
		{
			var _this = this;
			return {
				exec: function(action)
				{
					if (action == 'doUndo')
					{
						_this.editor.undoManager.Undo();
					}
					else if(action == 'doRedo')
					{
						_this.editor.undoManager.Redo();
					}
				},
				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetUniversalFormatStyle: function()
		{
			var
				TEMP_CLASS = 'bx-tmp-ufs-class',
				STATUS_ATTR = 'data-bx-tmp-status',
				_this = this;

			function getAncestorNodes(nodes)
			{
				var result = [];
				if (nodes && nodes.length > 0)
				{
					var
						i, len, sorted = [], node, status;
					for (i = 0, len = nodes.length; i < len; i++)
					{
						if (!_this.editor.util.CheckSurrogateNode(nodes[i]) && nodes[i].nodeName !== 'BR')
						{
							nodes[i].setAttribute(STATUS_ATTR, 'Y');
							sorted.push({node: nodes[i], nesting: _this.editor.util.GetNodeDomOffset(nodes[i])});
						}
					}
					sorted = sorted.sort(function(a, b){return a.nesting - b.nesting});
					for (i = 0, len = sorted.length; i < len; i++)
					{
						node = sorted[i].node;
						status = node.getAttribute(STATUS_ATTR);
						if (status == 'Y' && !findUnIncludedNodes(node))
						{
							// Prevent including all childs of this node to result
							BX.findChild(node, function(n)
							{
								if (n.nodeType == 1 && n.nodeName !== 'BR' && n.setAttribute)
								{
									n.setAttribute(STATUS_ATTR, n.className == TEMP_CLASS ? 'GET_RID_OF' : 'SKIP');
								}
								return false;
							}, true, true);

							result.push(node);
						}
					}
				}
				return result;
			}

			function findUnIncludedNodes(node)
			{
				var res = BX.findChild(node, function(n)
				{
					return n.nodeType == 1 && n.nodeName !== 'BR' && n.getAttribute && n.getAttribute(STATUS_ATTR) !== 'Y';
				}, true, false);
				return !!res;
			}

			function adjustNodeStyle(node, className, style)
			{
				try
				{
					if (className !== false)
					{
						if (className == '')
						{
							node.removeAttribute('class');
						}
						else
						{
							node.className = className;
						}
					}
					if (style !== false)
					{
						if (style == '')
						{
							node.removeAttribute('style');
						}
						else
						{
							node.style.cssText = style;
						}
					}
				}
				catch(e)
				{}
			}

			return {
				exec: function(action, value)
				{
					if (value.nodes && value.nodes.length > 0)
					{
						for (i = 0; i < value.nodes.length; i++)
						{
							adjustNodeStyle(value.nodes[i], value.className, value.style);
						}
					}
					else
					{
						_this.actions.formatInline.exec(action, value, "span", false, TEMP_CLASS);

						if (document.querySelectorAll)
						{
							var tmpSpanNodes = _this.editor.GetIframeDoc().querySelectorAll('.' + TEMP_CLASS);
							if (tmpSpanNodes)
							{
								for (i = 0; i < tmpSpanNodes.length; i++)
								{
									node = tmpSpanNodes[i];
									if (BX.util.trim(node.innerHTML) == '')
									{
										_this.editor.util.ReplaceWithOwnChildren(node);
									}
								}
							}
						}

						var
							i, node,
							nodes = _this.actions.universalFormatStyle.state(action),
							existNodes = getAncestorNodes(nodes);

						for (i = 0; i < existNodes.length; i++)
						{
							adjustNodeStyle(existNodes[i], value.className, value.style);
						}

						if (document.querySelectorAll)
						{
							tmpSpanNodes = _this.editor.GetIframeDoc().querySelectorAll('.' + TEMP_CLASS);
							if (tmpSpanNodes)
							{
								for (i = 0; i < tmpSpanNodes.length; i++)
								{
									node = tmpSpanNodes[i];
									if (node.getAttribute(STATUS_ATTR) == 'GET_RID_OF')
									{
										_this.editor.util.ReplaceWithOwnChildren(tmpSpanNodes[i]);
									}
									else
									{
										adjustNodeStyle(tmpSpanNodes[i], value.className, value.style);
									}
								}
							}
						}
					}
				},
				state: function(action)
				{
					var range = _this.editor.selection.GetRange();

					if (range)
					{
						var
							textNodes, textNode, node,
							nodes = range.getNodes([1]);

						// Range is collapsed or text node is selected
						if (nodes.length == 0)
						{
							textNodes = range.getNodes([3]);
							if (textNodes && textNodes.length == 1)
							{
								textNode = textNodes[0];
							}

							if (!textNode && range.startContainer == range.endContainer)
							{
								if (range.startContainer.nodeType == 3)
								{
									textNode = range.startContainer;
								}
								else
								{
									_this.editor.selection.SelectNode(range.startContainer);
									nodes = [range.startContainer];
								}
							}

							if (textNode && nodes.length == 0)
							{
								node = textNode.parentNode;
								if (node)
								{
									nodes = [node];
								}
							}
						}
						return nodes;
					}
				},
				value: BX.DoNothing
			};
		},

		GetSubSup: function(type)
		{
			var _this = this;

			type = type == 'sup' ? 'sup' : 'sub';

			return {
				exec: function(action, value)
				{
					return _this.actions.formatInline.exec(action, value, type);
				},
				state: function(action, value)
				{
					return _this.actions.formatInline.state(action, value, type);
				},
				value: BX.DoNothing
			};
		},

		GetQuote: function()
		{
			var
				range,
				externalSelection,
				_this = this;

			function checkNode(n)
			{
				return n && n.className == 'bxhtmled-quote' && n.nodeName == 'BLOCKQUOTE';
			}

			function setExternalSelection(text)
			{
				externalSelection = text;
			}
			function getExternalSelection()
			{
				return externalSelection;
			}
			function setRange(rng)
			{
				return range = rng;
			}
			function setExternalSelectionFromRange(range)
			{
				range = range || _this.editor.selection.GetRange(_this.editor.selection.GetSelection(document));

				if (range)
				{
					var tmpDiv;
					// mantis:64329
					if (range.startContainer == range.endContainer && range.startOffset == 0 && range.endOffset == range.endContainer.length && range.startContainer.parentNode && range.startContainer.parentNode.nodeName == 'A' && range.startContainer.parentNode.href)
					{
						tmpDiv = BX.create('DIV', {html: range.startContainer.parentNode.href}, _this.editor.GetIframeDoc());
					}
					else
					{
						var html = range.toHtml();
						html = html.replace(/<br.*?>/ig, "#BX_BR#");
						tmpDiv = BX.create('DIV', {html: BX.util.htmlspecialchars(html)}, _this.editor.GetIframeDoc());
					}

					var extSel = _this.editor.util.GetTextContentEx(tmpDiv);
					extSel = extSel.replace(/#BX_BR#/ig, "<br>");
					setExternalSelection(extSel);
					BX.remove(tmpDiv);
				}
				else
				{
					setExternalSelection('');
				}
			}

			function checkSpaceAfterQuotes(target)
			{
				var
					i, quote,
					quotes = target.querySelectorAll("blockquote.bxhtmled-quote");

				for (i = 0; i < quotes.length; i++)
				{
					quote = quotes[i];
					if (quote.nextSibling === null)
					{
						_this.editor.util.InsertAfter(_this.editor.util.GetInvisibleTextNode(), quote);
					}
				}
			}

			return {
				exec: function(action)
				{
					var
						res = false,
						sel = getExternalSelection();

					if (_this.editor.bbCode && _this.editor.synchro.IsFocusedOnTextarea())
					{
						_this.editor.textareaView.Focus();
						if(sel)
						{
							res = _this.editor.textareaView.WrapWith(false, false, "[QUOTE]" + sel + "[/QUOTE]");
						}
						else
						{
							res = _this.actions.formatBbCode.exec(action, {tag: 'QUOTE'});
						}
					}
					else
					{
						if (!range && _this.editor.selection.lastCheckedRange && _this.editor.selection.lastCheckedRange.range)
						{
							range = _this.editor.selection.lastCheckedRange.range;
						}
						_this.editor.iframeView.Focus();
						if (range)
						{
							_this.editor.selection.SetSelection(range);
						}

						if(sel)
						{
							var quoteId = 'bxq_' + Math.round(Math.random() * 1000000);

							_this.editor.InsertHtml('<blockquote id="' + quoteId + '" class="bxhtmled-quote">' + sel + '</blockquote>' + _this.editor.INVISIBLE_SPACE, range);

							setTimeout(function()
							{
								var quote = _this.editor.GetIframeElement(quoteId);
								if (quote)
								{
									var prev = quote.previousSibling;
									if (prev && prev.nodeType == 3 && _this.editor.util.IsEmptyNode(prev) &&
										prev.previousSibling && prev.previousSibling.nodeName == 'BR')
									{
										BX.remove(prev);
									}
									quote.removeAttribute('id');
								}
							}, 0);
						}
						else
						{
							if (!range && _this.editor.selection.lastRange)
								range = _this.editor.selection.lastRange;

							res = _this.actions.formatBlock.exec('formatBlock', 'blockquote', 'bxhtmled-quote', false, {range: range});
						}

						if(!sel)
							_this.editor.selection.ScrollIntoView();
					}

					range = null;
					return res;
				},
				state: function()
				{
					return _this.actions.formatBlock.state('formatBlock', 'blockquote', 'bxhtmled-quote');
				},
				value: BX.DoNothing,
				setExternalSelectionFromRange : setExternalSelectionFromRange,
				setExternalSelection : setExternalSelection,
				getExternalSelection : getExternalSelection,
				checkSpaceAfterQuotes: checkSpaceAfterQuotes,
				setRange : setRange,
				checkNode: checkNode
			};
		},

		GetCode: function()
		{
			var _this = this;
			return {
				exec: function(action)
				{
					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						var codeElement = _this.actions.code.state();
						if (codeElement)
						{
							var innerHtml = BX.util.trim(codeElement.innerHTML);
							if (innerHtml == '<br>' || innerHtml === '')
							{
								_this.editor.selection.SetAfter(codeElement);
								BX.remove(codeElement);
							}
							else
							{
								_this.editor.selection.ExecuteAndRestoreSimple(function()
								{
									codeElement.className = '';
									codeElement = _this.editor.util.RenameNode(codeElement, 'P');
								});
							}
						}
						else
						{
							_this.actions.formatBlock.exec('formatBlock', 'pre', 'bxhtmled-code');
						}
					}
					else // bbcode + textarea
					{
						return _this.actions.formatBbCode.exec(action, {tag: 'CODE'});
					}
				},
				state: function()
				{
					return _this.actions.formatBlock.state('formatBlock', 'pre', 'bxhtmled-code');
				},
				value: BX.DoNothing
			};
		},

		GetInsertSmile: function()
		{
			var _this = this;
			return {
				exec: function(action, value)
				{
					var smile = _this.editor.smilesIndex[value];
					if (_this.editor.bbCode && _this.editor.synchro.IsFocusedOnTextarea())
					{
						_this.editor.textareaView.Focus();
						_this.editor.textareaView.WrapWith(false, false, " " + smile.code + " ");
					}
					else
					{
						_this.editor.iframeView.Focus();
						if (smile)
						{
							var smileImg = BX.create("IMG", {props:
							{
								src: smile.path,
								title: smile.name || smile.code
							}}, _this.editor.iframeView.document);
							_this.editor.SetBxTag(smileImg, {tag: "smile", params: smile});
							if (smile.width)
								smileImg.style.width = parseInt(smile.width) + 'px';
							if (smile.height)
								smileImg.style.height = parseInt(smile.height) + 'px';
							_this.editor.selection.InsertNode(smileImg);

							var textBefore = _this.editor.iframeView.document.createTextNode(' ');
							smileImg.parentNode.insertBefore(textBefore, smileImg);
							var textAfer = BX.create("SPAN", {html: '&nbsp;'}, _this.editor.iframeView.document);
							_this.editor.util.InsertAfter(textAfer, smileImg);
							_this.editor.selection.SetAfter(textAfer);
							setTimeout(function(){_this.editor.selection.SetAfter(textAfer);}, 10);
						}
					}
				},
				state: BX.DoNothing,
				value: BX.DoNothing
			};
		},

		GetTableOperation: function()
		{
			var
				_this = this,
				newCellHtml = '&nbsp;';

			function createTableMatrix(oTable)
			{
				var aRows = oTable.rows;
				// Row and Column counters.
				var
					arMatrix = [],
					i, // index
					c, j, oCell, rs, cs,
					iColSpan, iRowSpan,
					r = -1; // row

				for (i = 0; i < aRows.length; i++)
				{
					r++;
					if (!arMatrix[r])
					{
						arMatrix[r] = [];
					}

					c = -1;

					for (j = 0; j < aRows[i].cells.length; j++)
					{
						oCell = aRows[i].cells[j];

						c++;
						while (arMatrix[r][c])
						{
							c++;
						}

						iColSpan = isNaN(oCell.colSpan) ? 1 : oCell.colSpan;
						iRowSpan = isNaN(oCell.rowSpan) ? 1 : oCell.rowSpan;

						for(rs = 0; rs < iRowSpan; rs++)
						{
							if (!arMatrix[r + rs])
							{
								arMatrix[r + rs] = [];
							}

							for (cs = 0; cs < iColSpan; cs++)
							{
								arMatrix[r + rs][c + cs] = aRows[i].cells[j];
							}
						}

						c += iColSpan - 1;
					}
				}
				return arMatrix;
			}

			function getIndexes(oCell, arMatrix)
			{
				var
					i, j,
					arIndexes = [];

				for (i = 0; i < arMatrix.length; i++)
				{
					for (j = 0, l = arMatrix[i].length; j < l; j++)
					{
						if (arMatrix[i][j] == oCell)
						{
							arIndexes.push({r : i, c : j});
						}
					}
				}
				return arIndexes;
			}

			function getCellIndexInfo(ind)
			{
				var
					rows = [], cols = [],
					indInfo = {
						cells: 0
					},
					ii;

				for(ii = 0; ii < ind.length; ii++)
				{
					indInfo.cells++;
					indInfo.maxRow = ii === 0 ? ind[ii].r : Math.max(ind[ii].r, indInfo.maxRow);
					indInfo.minRow = ii === 0 ? ind[ii].r : Math.min(ind[ii].r, indInfo.minRow);
					indInfo.maxCol = ii === 0 ? ind[ii].c : Math.max(ind[ii].c, indInfo.maxCol);
					indInfo.minCol = ii === 0 ? ind[ii].c : Math.min(ind[ii].c, indInfo.minCol);

					if (!BX.util.in_array(ind[ii].r, rows))
						rows.push(ind[ii].r);

					if (!BX.util.in_array(ind[ii].c, cols))
						cols.push(ind[ii].c);
				}

				indInfo.rows = rows.length;
				indInfo.cols = cols.length;

				return indInfo;
			}

			function findAndPushAndUniqueCell(cells, node, table)
			{
				if (node)
				{
					node = getParentCell(node, table);

					if (node && !BX.util.in_array(node, cells))
						cells.push(node);
				}

				return cells;
			}

			function getSelectedCells(range, table)
			{
				var cells = [], cell;

				if (BX.browser.IsFirefox())
				{
					var
						i, rng, start, end,
						sel = rangy.getNativeSelection(_this.editor.sandbox.GetWindow());

					for (i = 0; i < sel.rangeCount; i++)
					{
						rng = sel.getRangeAt(i);

						start = rng.startContainer.nodeType === 1 ? rng.startContainer.childNodes[rng.startOffset] : rng.startContainer;
						end = rng.endContainer.nodeType === 1 ? rng.endContainer.childNodes[rng.endOffset] : rng.endContainer;

						cells = findAndPushAndUniqueCell(cells, start, table);
						cells = findAndPushAndUniqueCell(cells, end, table);
					}
				}
				else
				{
					if (range.collapsed)
					{
						cell = getParentCell(range.startContainer);
						cells = findAndPushAndUniqueCell(cells, cell, table);
					}
					else
					{
						var nodes = range.getNodes([1]);
						for (i = 0; i < nodes.length; i++)
						{
							if (nodes[i].nodeName == 'TD' || nodes[i].nodeName == 'TH')
							{
								cells = findAndPushAndUniqueCell(cells, nodes[i], table);
							}
						}
					}
				}

				return cells;
			}

			function insertColumn(element, table, actionType)
			{
				var td = BX.findParent(element, {tag: 'TD'});

				if (!td)
					return;

				var
					tr = td.parentNode,
					cellInd = actionType == 'insertColumnLeft' ? td.cellIndex : td.cellIndex + 1,
					rowInd = tr.rowIndex,
					mtx = createTableMatrix(table),
					arInd = getIndexes(td, mtx);

				tr.insertCell(cellInd).innerHTML = newCellHtml;

				var
					r, ind, i, c, j,
					curFullCellInd = actionType == 'insertColumnLeft' ? arInd[0].c : arInd[0].c + 1;

				for (j = 0; j < table.rows.length; j++)
				{
					r = table.rows[j];
					if (r.rowIndex == rowInd)
					{
						continue;
					}

					ind = 0;
					i = 0;
					for(i = 0; i < r.cells.length; i++)
					{
						c = r.cells[i];
						arInd = getIndexes(c, mtx);
						if (arInd[0].c >= curFullCellInd)
						{
							ind = c.cellIndex;
							break;
						}
						ind = i + 1;
					}

					r.insertCell(ind).innerHTML = '&nbsp;';
				}
			}

			function insertRow(element, table, actionType)
			{
				var tr = BX.findParent(element, {tag: 'TR'});
				if (!tr || !table)
					return;

				var
					i, newCell,
					rowInd = actionType == 'insertRowUpper' ? tr.rowIndex : tr.rowIndex + 1,
					newRow = table.insertRow(rowInd);

				for(i = 0; i < tr.cells.length; i++)
				{
					newCell = newRow.insertCell(i);
					newCell.innerHTML = newCellHtml;
					newCell.colSpan = tr.cells[i].colSpan;
				}
			}

			function insertCell(element, table, actionType)
			{
				var td = getParentCell(element, table);
				if (!td || !table)
					return;

				var
					tr = td.parentNode,
					cellInd = actionType == 'insertCellLeft' ? td.cellIndex : td.cellIndex + 1;

				tr.insertCell(cellInd).innerHTML = newCellHtml;
			}

			function getParentCell(node, table)
			{
				if (node.nodeName == 'TD' || node.nodeName == 'TH')
					return node;

				return BX.findParent(node, function(n)
				{
					return n.nodeName == 'TD' || n.nodeName == 'TH'
				}, table);
			}


			function getMergeState(cells, table)
			{
				var
					indInfo, i,
					mtx = createTableMatrix(table),
					firstIndInfo = getCellIndexInfo(getIndexes(cells[0], mtx)),
					lastIndInfo = firstIndInfo,
					gaps = false,
					sameRow = true,
					sameCol = true;

				for(i = 1; i < cells.length; i++)
				{
					indInfo = getCellIndexInfo(getIndexes(cells[i], mtx));
					sameRow = sameRow && indInfo.rows == firstIndInfo.rows && indInfo.maxRow == firstIndInfo.maxRow  && indInfo.minRow == firstIndInfo.minRow;
					sameCol = sameCol && indInfo.cols == firstIndInfo.cols && indInfo.maxCol == firstIndInfo.maxCol  && indInfo.minCol == firstIndInfo.minCol;

					gaps = gaps ||
						(sameRow && Math.abs(indInfo.minCol - lastIndInfo.maxCol) > 1)
						||
						(sameCol && Math.abs(indInfo.minRow - lastIndInfo.maxRow) > 1)
						||
						!sameRow && !sameCol;

					lastIndInfo = indInfo;
				}

				return {
					sameCol : sameCol,
					sameRow: sameRow,
					gaps: gaps
				};
			}

			function canBeMerged(cells, range, table)
			{
				if (!cells)
					cells = getSelectedCells(range, table);

				if (!cells || cells.length < 2)
					return false;

				var mergeState = getMergeState(cells, table);
				return !mergeState.gaps && (!mergeState.sameRow && mergeState.sameCol || mergeState.sameRow && !mergeState.sameCol);
			}

			function canBeMergedWithRight(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length !== 1)
					return false;

				var
					mtx = createTableMatrix(table),
					ind = getIndexes(cells[0], mtx);

				if (ind.length < 1)
					return false;

				var
					i, rightTd,
					maxCol = ind[ind.length - 1].c, // Max col
					res = true, c;

				for (i = 0; i < ind.length; i++)
				{
					if (ind[i].c == maxCol)
					{
						if (mtx[ind[i].r] && mtx[ind[i].r][ind[i].c + 1])
						{
							c = mtx[ind[i].r][ind[i].c + 1];

							if (rightTd === undefined)
								rightTd = c;
							else if (rightTd !== c)
								res = false;
						}
						else
						{
							res = false;
						}
					}
				}

				res = res && rightTd && canBeMerged([cells[0], rightTd], range, table);

				return res;
			}

			function canBeMergedWithBottom(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length !== 1)
					return false;

				var
					mtx = createTableMatrix(table),
					ind = getIndexes(cells[0], mtx);

				if (ind.length < 1)
					return false;

				var
					i, bottomTd,
					maxRow = ind[ind.length - 1].r, // Max row
					res = true, c;

				for (i = 0; i < ind.length; i++)
				{
					if (ind[i].r == maxRow)
					{
						if (mtx[maxRow + 1] && mtx[maxRow + 1][ind[i].c])
						{
							c = mtx[maxRow + 1][ind[i].c];
							if (bottomTd === undefined)
								bottomTd = c;
							else if (bottomTd !== c)
								res = false;
						}
						else
						{
							res = false;
						}
					}
				}

				res = res && bottomTd && canBeMerged([cells[0], bottomTd], range, table);

				return res;
			}

			function mergeCells(range, table, cells)
			{
				if (!cells)
					cells = getSelectedCells(range, table);

				if (cells.length < 2)
					return;

				var
					mergeState = getMergeState(cells, table),
					i, tr,
					newCellColSpan = 0,
					newCellRowSpan = 0,
					newCellContent = '';

				// Horizontal cells
				if (mergeState.sameRow && !mergeState.sameCol && !mergeState.gaps)
				{
					for(i = 0; i < cells.length; i++)
					{
						newCellContent += ' ' + BX.util.trim(cells[i].innerHTML);
						tr = cells[i].parentNode;
						newCellColSpan += cells[i].colSpan;

						if (i > 0)
							tr.removeChild(cells[i]);
					}

					cells[0].colSpan = newCellColSpan;
					cells[0].innerHTML = BX.util.trim(newCellContent);
				}
				// vertical cells
				else if (!mergeState.sameRow && mergeState.sameCol && !mergeState.gaps)
				{
					for(i = 0; i < cells.length; i++)
					{
						newCellContent += ' ' + BX.util.trim(cells[i].innerHTML);
						tr = cells[i].parentNode;
						newCellRowSpan += cells[i].rowSpan;

						if (i > 0)
							tr.removeChild(cells[i]);
					}

					cells[0].rowSpan = newCellRowSpan;
					cells[0].innerHTML = BX.util.trim(newCellContent);
				}
				else
				{
					alert(BX.message('BXEdTableMergeError'));
				}
			}

			function mergeRightCell(range, table)
			{
				var cells = getSelectedCells(range, table);

				if (!cells || cells.length !== 1)
					return false;

				var tr = BX.findParent(cells[0], {tag: 'TR'}, table);

				if (cells[0].cellIndex < tr.cells.length - 1)
				{
					cells.push(tr.cells[cells[0].cellIndex + 1]);
				}

				return mergeCells(range, table, cells);
			}

			function mergeBottomCell(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length !== 1)
					return false;

				var
					mtx = createTableMatrix(table),
					ind = getIndexes(cells[0], mtx),
					i, bottomTd,
					maxRow = ind[ind.length - 1].r, // Max row
					res = true, c;

				for (i = 0; i < ind.length; i++)
				{
					if (ind[i].r == maxRow)
					{
						if (mtx[maxRow + 1] && mtx[maxRow + 1][ind[i].c])
						{
							c = mtx[maxRow + 1][ind[i].c];
							if (bottomTd === undefined)
								bottomTd = c;
							else if (bottomTd !== c)
								res = false;
						}
						else
						{
							res = false;
						}
					}
				}

				if (res)
				{
					cells.push(bottomTd);
					return mergeCells(range, table, cells);
				}
			}

			function mergeRow(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length !== 1)
					return false;

				var
					i, newCells = [],
					tr = cells[0].parentNode;

				for(i = 0; i < tr.cells.length; i++)
				{
					newCells.push(tr.cells[i]);
				}

				return mergeCells(range, table, newCells);
			}

			function mergeColumn(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length !== 1)
					return false;

				var
					i, j, newCells = [],
					mtx = createTableMatrix(table),
					indInfo = getCellIndexInfo(getIndexes(cells[0], mtx));

				for (i = 0; i < mtx.length; i++)
				{
					for (j = indInfo.minCol; j <= indInfo.minCol; j++)
					{
						newCells = findAndPushAndUniqueCell(newCells, mtx[i][j], table);
					}
				}

				return mergeCells(range, table, newCells);
			}

			function splitHorizontally(range, table)
			{
				var cells = getSelectedCells(range, table);

				if (!cells || cells.length != 1)
					return false;

				var
					i, j,
					realInd = 0,
					realIndI,
					trI,
					newCell,
					colSpan = cells[0].colSpan,
					rowSpan = cells[0].rowSpan,
					tr = cells[0].parentNode;

				for(i = 0; i <= cells[0].cellIndex; i++)
					realInd += tr.cells[i].colSpan;

				if (colSpan > 1)
				{
					cells[0].colSpan--;
				}
				else
				{
					for(j = 0; j < table.rows.length; j++)
					{
						if (j == tr.rowIndex || j >= tr.rowIndex && j < tr.rowIndex + rowSpan)
							continue;

						realIndI = 0;
						trI = table.rows[j];

						i = 0;
						while (realIndI < realInd && i < trI.cells.length)
							realIndI += trI.cells[i++].colSpan;

						i--;
						trI.cells[i].colSpan += 1;

						// mantis: 71909
						if (trI.cells[i].rowSpan > 1)
							j = j + trI.cells[i].rowSpan - 1;
					}
				}
				newCell = tr.insertCell(cells[0].cellIndex + 1);
				newCell.rowSpan = cells[0].rowSpan;
				newCell.innerHTML = newCellHtml;
			}

			function splitVertically(range, table)
			{
				var cells = getSelectedCells(range, table);

				if (!cells || cells.length != 1)
					return false;

				var
					i, r, c, row, cell,
					indI,
					fullRowInd, realCellInd,
					mtx = createTableMatrix(table),
					ind = getIndexes(cells[0], mtx),
					tr = cells[0].parentNode,
					//maxCellCount = arTMX[0].length; //max count of cell in table
					curRowIndex = tr.rowIndex,
					curCellIndex = cells[0].cellIndex,
					curFullRowInd = ind[0].r,
					curFullCellInd = ind[0].c,
					bOneW = true,
					bOneH = true;

				for(i = 1; i < ind.length; i++)
				{
					if (ind[i].r != curFullRowInd)
						bOneH = false;
					if (ind[i].c != curFullCellInd)
						bOneW = false;
				}

				if (bOneH) // if rowSpan == 1 and we have to split this cell
				{
					var
						newRow = table.insertRow(tr.rowIndex + 1),
						newCell = newRow.insertCell(-1);

					newCell.innerHTML = newCellHtml;
					if (!bOneW)
						newCell.colSpan = cells[0].colSpan;


					for(r = 0; r <= curFullRowInd; r++)
					{
						row = table.rows[r];
						for(c = 0; c < row.cells.length; c++)
						{
							cell = row.cells[c];
							if (r == curRowIndex && c == curCellIndex)
								continue;

							fullRowInd = r; // oRow.rowIndex
							if (cell.rowSpan > 1)
								fullRowInd += cell.rowSpan - 1;

							if (fullRowInd >= curFullRowInd)
								cell.rowSpan++;
						}
					}
				}
				else // If cell has rowspan > 1
				{
					row = table.rows[curRowIndex + --cells[0].rowSpan];
					realCellInd = false;
					for(c = 0; c < row.cells.length; c++)
					{
						indI = getIndexes(row.cells[c], mtx);
						for(i = 0; i < indI.length; i++)
						{
							if (indI[i].c > curCellIndex)
								realCellInd = 0;
							else if (indI[i].c + 1 == curCellIndex)
								realCellInd = row.cells[c].cellIndex + 1;

							if (realCellInd !== false)
								break;
						}
					}

					newCell = row.insertCell(realCellInd);
					newCell.innerHTML = newCellHtml;
					if (!bOneW)
						newCell.colSpan = cells[0].colSpan;
				}
			}

			// Remove
			function removeColumn(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length != 1)
					return false;
				var cell = cells[0];

				if (!cell)
					return false;

				var
					tr, td,
					mtx = createTableMatrix(table),
					ind = getIndexes(cell, mtx),
					j;

				for (j = 0; j < mtx.length; j++)
				{
					td = mtx[j][ind[0].c];
					if (td && td.parentNode)
					{
						tr = td.parentNode;
						BX.remove(td);
						if (tr.cells.length == 0)
							BX.remove(tr);
					}
				}

				if (table.rows.length == 0)
					BX.remove(table);
			}

			function removeRow(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length != 1)
					return false;
				var cell = cells[0];


				if (!cell)
					return false

				BX.remove(cell.parentNode);

				if (table.rows.length == 0)
					BX.remove(table);
			}

			function removeCell(range, table, cell)
			{
				if (!cell)
				{
					var cells = getSelectedCells(range, table);
					if (!cells || cells.length != 1)
						return false;
					cell = cells[0];
				}

				if (!cell)
					return false;

				var tr = cell.parentNode;
				BX.remove(cell);

				if (tr.cells.length == 0)
					BX.remove(tr);

				if (table.rows.length == 0)
					BX.remove(table);
			}

			function removeSelectedCells(range, table)
			{
				var cells = getSelectedCells(range, table);
				if (!cells || cells.length == 1)
					return false;

				var i, tr;
				for (i = 0; i < cells.length; i++)
				{
					tr = cells[i].parentNode;
					BX.remove(cells[i]);

					if (tr.cells.length == 0)
						BX.remove(tr);
				}

				if (table.rows.length == 0)
					BX.remove(table);
			}

			return {
				exec: function(action, value)
				{
					var node = value.range.commonAncestorContainer;

					switch (value.actionType)
					{
						// Insert
						case 'insertColumnLeft':
						case 'insertColumnRight':
							insertColumn(node, value.tableNode, value.actionType);
							break;
						case 'insertRowUpper':
						case 'insertRowLower':
							insertRow(node, value.tableNode, value.actionType);
							break;
						case 'insertCellLeft':
						case 'insertCellRight':
							insertCell(node, value.tableNode, value.actionType);
							break;

						// Remove
						case 'removeColumn':
							removeColumn(value.range, value.tableNode);
							break;
						case 'removeRow':
							removeRow(value.range, value.tableNode);
							break;
						case 'removeCell':
							removeCell(value.range, value.tableNode);
							break;
						case 'removeSelectedCells':
							removeSelectedCells(value.range, value.tableNode);
							break;

						// Merge
						case 'mergeSelectedCells':
							mergeCells(value.range, value.tableNode);
							break;
						case 'mergeRightCell':
							mergeRightCell(value.range, value.tableNode);
							break;
						case 'mergeBottomCell':
							mergeBottomCell(value.range, value.tableNode);
							break;
						case 'mergeRow':
							mergeRow(value.range, value.tableNode);
							break;
						case 'mergeColumn':
							mergeColumn(value.range, value.tableNode);
							break;

						// split
						case 'splitHorizontally':
							splitHorizontally(value.range, value.tableNode);
							break;
						case 'splitVertically':
							splitVertically(value.range, value.tableNode);
							break;
					}

				},
				state: BX.DoNothing,
				value: BX.DoNothing,
				getSelectedCells: getSelectedCells,
				canBeMerged: canBeMerged,
				canBeMergedWithRight: canBeMergedWithRight,
				canBeMergedWithBottom: canBeMergedWithBottom
			};
		},


		GetFormatBbCode: function()
		{
			var _this = this;
			return {
				view: 'textarea',
				exec: function(action, params)
				{
					var
						value = params.value,
						tag = params.tag.toUpperCase(),
						tag_end = tag;

					if (tag == 'FONT' || tag == 'COLOR' || tag == 'SIZE')
					{
						tag += "=" + value;
					}

					if(params.singleTag === true)
						_this.editor.textareaView.WrapWith("[", "]", tag);
					else
						_this.editor.textareaView.WrapWith("[" + tag + "]", "[/" + tag_end + "]");
				},
				state: BX.DoNothing,
				value: BX.DoNothing
			};
		}
	};

	window.BXEditorActions = BXEditorActions;
})();
