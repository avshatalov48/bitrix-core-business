LHEButtons['TaskCheckbox'] = {
	id: 'TaskCheckbox',
	name: LHE_TC.butTitle,
	bBBHide: true,
	handler: function(pBut)
	{
		var oRange = pBut.pLEditor.GetSelectionRange();
		var selectedText = '', pUl = false, pLi = false;

		// Get selected text
		if (oRange.startContainer) // DOM Model
		{
			var oSel = pBut.pLEditor.GetSelectionObject();
			if (oSel && !oSel.collapsed)
				selectedText = true;
		}
		else // IE
		{
			selectedText = oRange.text || oRange.htmlText;
		}

		if (selectedText)
		{
			var p = pBut.pLEditor.GetSelectionObject();
			pLi = (p && p.nodeName.toUpperCase() == 'LI') ? p : BX.findParent(p, {tag: 'LI'});
			pUl = BX.findParent(p, {tag: 'UL'});
		}
		else
		{
			pBut.pLEditor.InsertHTML('<span id="bx_lhe_temp_bogus_node_0" style="visibility: hidden;">#</span>');
		}

		setTimeout(function()
		{
			var pNode = pBut.pLEditor.pEditorDocument.getElementById('bx_lhe_temp_bogus_node_0');
			if (pNode)
			{
				pLi = BX.findParent(pNode, {tag: "LI"});
				pUl = BX.findParent(pNode, {tag: "UL"});
				pNode.parentNode.removeChild(pNode);
			}

			if (pUl)
			{
				//Open dialog, add new point
				pBut.pLEditor.OpenDialog({id: 'TaskCheckbox', pUl: pUl, pLi: pLi});
			}
			else
			{
				if (selectedText)
				{
					var
						arUL = pBut.pLEditor.pEditorDocument.body.getElementsByTagName('UL'),
						i0, l0 = arUL.length, i1, l1;

					for (i0 = 0; i0 < l0; i0++)
						arUL[i0].setAttribute('id', 'bx_ul_tmp');

					pBut.pLEditor.executeCommand("InsertUnorderedList");

					arUL = pBut.pLEditor.pEditorDocument.body.getElementsByTagName('UL');
					l1 = arUL.length;

					for (i1 = 0; i1 < l1; i1++)
					{
						if (!arUL[i1].getAttribute('id') || arUL[i1].getAttribute('id') != 'bx_ul_tmp')
						{
							arUL[i1].className = 'bx-subtasklist';
							pBut.pLEditor.SetBxTag(arUL[i1], {tag: 'subtasklist'});

							var
								arLi = arUL[i1].getElementsByTagName('LI'),
								i, l = arLi.length;

							if (arLi && l > 0)
							{
								for(i = 0; i < l; i++)
								{
									if (arLi[i])
										pBut.pLEditor.SetBxTag(arLi[i], {tag: 'subtask'});
								}
							}
						}
						else
						{
							arUL[i1].removeAttribute('id');
						}
					}
				}
				else
				{
					pBut.pLEditor.OpenDialog({id: 'TaskCheckbox'});
				}
			}
		},
		50
		);
	},
	parsers:
	[
		{
			name: "subtasklist",
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					if (window.subTaskStyles && window.subTaskStyles.length > 0)
						setTimeout(function(){pLEditor.AppendCSS(window.subTaskStyles);}, 300);

					var newStr;
					var taskParse = function(str, b1, b2, b3, b4, b5, b6)
					{
						var
							id = pLEditor.SetBxTag(false, {tag: "subtask", params: {id: b3}}),
							bChecked = b1.toLowerCase().indexOf('checked') != -1 || b4.toLowerCase().indexOf('checked') != -1,
							html = b6,
							cn = bChecked ? 'class="checked"' : '';

						newStr += '<li id="' + id + '" ' + cn + '>' + html + '</li>';
						return str;
					};

					var taskContParse = function(str, b1, b2)
					{
						newStr = '';
						if (str.toLowerCase().indexOf('bx_subtask_') > 0)
						{
							b2.replace(/<input([\s\S]*?)id\s*=\s*("|\')(bx_subtask_\d*?)\2([\s\S]*?)>[\s\S]*?<label[\s\S]*?for\s*=\s*("|\')\3\5[\s\S]*?>([\s\S]*?)<\/label>/ig, taskParse);
							return newStr == '' ? '' : '<ul class="bx-subtasklist"  id="' + pLEditor.SetBxTag(false, {tag: "subtasklist"}) + '">' + newStr + '</ul>';
						}
						return str;
					};

					sContent = sContent.replace(/<div[\s\S]*?class\s*=\s*("|\')bx-subtask-list\1[\s\S]*?>([\s\S]*?)<\/div>/ig, taskContParse);

					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{
					if (bxTag.tag != 'subtasklist' || !pNode.arNodes)
						return '';

					var html = '', i, l = pNode.arNodes.length, id, label, j, l1, oTag;

					for (i = 0; i < l; i++)
					{
						el = pNode.arNodes[i];
						if (el.text.toLowerCase() == 'li')
						{
							bChecked = (el.arAttributes['class'] == 'checked') ? ' checked' : '';
							pLEditor.subTaskIdCounter++;
							id = 'bx_subtask_' + pLEditor.subTaskIdCounter;

							label = '';
							for (j = 0, l1 = el.arNodes.length; j < l1; j++)
								label += pLEditor._RecursiveGetHTML(el.arNodes[j]);

							html += '<input type="checkbox" ' + bChecked + ' name="' + id + '" id="' + id + '" /><label for="' + id + '">' + label + '</label><br />' + "\n";
						}
					}
					if (html.length > 0)
					{
						html = '<div class="bx-subtask-list">' + html + '</div>';
					}
					return html;
				}
			}
		},
		{
			name: "subtask",
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{
					if (BX.browser.IsIE() && bxTag.tag == 'subtask' && pNode.text.toLowerCase() != 'li')
						return pLEditor._RecursiveGetHTML(pNode);
					return '';
				}
			}
		}
	]
};

window.LHEDailogs['TaskCheckbox'] = function(pObj)
{
	var OnSave = function()
	{

		pObj.Close();
	};

	return {
		title: LHE_TC.butTitle,
		innerHTML : '<table width="100%"><tr>' +
		'<td class="lhe-dialog-label lhe-label-imp"><label for="lhed_subtask_name">' + LHE_TC.subTaskLabel + ':</label></td>' +
		'<td class="lhe-dialog-param"><input type="text" size="30" value="" id="lhed_subtask_name"></td>' +
	'</tr></table>',
		width: 400,
		OnLoad: function()
		{
			pObj.pName = BX("lhed_subtask_name");
			pObj.pName.onkeyup = function(e)
			{
				if (!e) e = window.event;
				if (e.keyCode == 13)
					window.obLHEDialog.PARAMS.buttons[0].emulate();
			};
			pObj.pLEditor.focus(pObj.pName);
		},
		OnSave: function()
		{
			var subTask = pObj.pName.value;
			var bxTag = false;

			if (pObj.arParams.pUl && pObj.arParams.pUl.id)
				bxTag = pObj.pLEditor.GetBxTag(pObj.arParams.pUl.id);

			if (bxTag && bxTag.tag == 'subtasklist')
			{
				var newLi = BX.create("LI", {props: {id: pObj.pLEditor.SetBxTag(false, {tag: 'subtask'})}, text: subTask}, pObj.pLEditor.pEditorDocument);

				if (pObj.arParams.pLi && pObj.arParams.pLi.nextSibling)
					pObj.arParams.pUl.insertBefore(newLi, pObj.arParams.pLi.nextSibling);
				else
					pObj.arParams.pUl.appendChild(newLi);
			}
			else
			{
				var
					arUL0 = pObj.pLEditor.pEditorDocument.body.getElementsByTagName('UL'),
					i0, l0 = arUL0.length, i1, l1;

				for (i0 = 0; i0 < l0; i0++)
					arUL0[i0].setAttribute('__bx_tmp_old_ul', true);

				pObj.pLEditor.SelectRange(pObj.pLEditor.oPrevRange);
				pObj.pLEditor.InsertHTML(' <p>' + subTask + '</p>');

				pObj.pLEditor.executeCommand('InsertUnorderedList');

				var arUL = pObj.pLEditor.pEditorDocument.body.getElementsByTagName('UL');
				l1 = arUL.length;

				for (i1 = 0; i1 < l1; i1++)
				{
					if (!arUL[i1].getAttribute('__bx_tmp_old_ul'))
					{
						arUL[i1].className = 'bx-subtasklist';

						arUL[i1].id = pObj.pLEditor.SetBxTag(false, {tag: 'subtasklist'});

						var
							arLi = arUL[i1].getElementsByTagName('LI'),
							i, l = arLi.length;

						if (arLi && l > 0)
						{
							for(i = 0; i < l; i++)
							{
								if (arLi[i])
									arLi[i].id = pObj.pLEditor.SetBxTag(false, {tag: 'subtask'});
							}
						}
						break;
					}
					else
					{
						arUL[i1].removeAttribute('__bx_tmp_old_ul');
					}
				}
			}
		}
	};
}

window.LHETaskOnInit = function(oLHE)
{
	var id = oLHE.id;

	if (id != 'LHETaskId')
		return;

	oLHE.TaskInitEvents();
	BX.addCustomEvent(oLHE, "OnParseContent", BX.proxy(oLHE.TaskOnParse_, oLHE));
	BX.addCustomEvent(oLHE, "OnUnParseContent", BX.proxy(oLHE.TaskOnUnParse_, oLHE));
};

JCLightHTMLEditor.prototype.TaskMouseDown_ = function(e)
{
	if (!e)
		e = window.event;

	var targ;
	if (e.target)
		targ = e.target;
	else if (e.srcElement)
		targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;

	if (targ)
	{
		var bxTag = false, bxTag2 = false, pLi = (targ && targ.nodeName.toUpperCase() == 'LI') ? targ : BX.findParent(targ, {tag: 'LI'});
		if (pLi && pLi.id)
			bxTag = this.GetBxTag(pLi.id);

		if (!bxTag && pLi && pLi.parentNode && pLi.parentNode.id)
			bxTag2 = this.GetBxTag(pLi.parentNode.id);

		if (bxTag && bxTag.tag == 'subtask' || bxTag2 && bxTag2 == 'subtasklist')
			pLi.className = pLi.className != 'checked' ? 'checked' : '';
	}
}

JCLightHTMLEditor.prototype.TaskInitEvents = function()
{
	// Init events
	var _this = this;
	BX.bind(this.pEditorDocument, 'mousedown', function(e) {_this.TaskMouseDown_(e);});
}

JCLightHTMLEditor.prototype.TaskOnParse_ = function()
{
	var _this = this;
	setTimeout(function(){_this.TaskInitEvents();}, 100);
}

JCLightHTMLEditor.prototype.TaskOnUnParse_ = function()
{
	this.subTaskIdCounter = 0;
}

BX.addCustomEvent('LHE_OnInit', LHETaskOnInit);