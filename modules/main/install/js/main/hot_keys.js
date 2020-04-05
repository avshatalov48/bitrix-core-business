
if(!BXHotKeys)
{

	function CBXHotKeys()
	{
		var _this = this;
		var idxKS = 0;
		var idxCode = 1;
		var idxCodeId = 2;
		var idxName = 3;
		var idxHKId = 4;
		var arServSymb = { 8: 'Back Space',9: 'Tab',13: 'Enter',16: 'Shift',17: 'Ctrl',18: 'Alt',19: 'Pause',
						20: 'Caps Lock',27: 'ESC',32: 'Space bar',33: 'Page Up',34: 'Page Down',35: 'End',36: 'Home',
						37: 'Left',38: 'Up',39: 'Right',40: 'Down',45: 'Insert',46: 'Delete',96: '0 (ext)',97: '1 (ext)',
						98: '2 (ext)',99: '3 (ext)',100: '4 (ext)',101: '5 (ext)',102: '6 (ext)',105: '9 (ext)',106: '* (ext)',
						107: '+ (ext)',104: '8 (ext)',103: '7 (ext)',110: '. (ext)',111: '/ (ext)',112: 'F1',113: 'F2',114: 'F3',
						115: 'F4',116: 'F5',117: 'F6',118: 'F7',119: 'F8',120: 'F9',121: 'F10',122: 'F11',123: 'F12',144: 'Num Lock',
						186: ';',188: ',',190: '.',191: '/',192: '`',219: '[',220: '|',221: ']',222: "'",189: '-',187: '+',145: 'Scrol Lock' };
		var bxHotKeyCode=0;
		var inputKeyCode=0;
		var inputDopString="";

		this.ArrHKCode=[];
		this.MesNotAssign="";
		this.MesClToChange="";
		this.MesClean="";
		this.MesBusy="";
		this.MesClose="";
		this.MesSettings="";
		this.MesDefault="";
		this.MesDelAll="";
		this.MesDelete="";
		this.MesDelConfirm="";
		this.MesDefaultConfirm="";
		this.MesExport="";
		this.MesExpFalse="";
		this.MesImport="";
		this.MesImpFalse="";
		this.MesImpSuc="";
		this.MesImpHeader="";
		this.MesFileEmpty="";
		this.MesChooseFile="";
		this.uid="";
		this.deleting = false;
		this.keyStrokes = {};
		this.keyStrokesInit = false;

		this.Init = function()
		{
			this.Register();
		};

		// keysString: Ctrl+Alt+Shift+KeyCode
		this.UpdateKS = function(codeId, keysString)
		{
			for(var i=0; i<this.ArrHKCode.length; i++)
			{
				if(this.ArrHKCode[i][idxCodeId] == codeId)
				{
					this.ArrHKCode[i][idxKS] = keysString;
					this.keyStrokesInit = false;
					return true;
				}
			}
		};

		this.UpdateHk = function(codeId, hkId)
		{
			for(var i=0; i<this.ArrHKCode.length; i++)
			{
				if(this.ArrHKCode[i][idxCodeId] == codeId)
				{
					this.ArrHKCode[i][idxHKId] = hkId;
					return i;
				}
			}

			return (-1);
		};

		this.Add = function(keysString, execCode, codeId, name, hkId)
		{
			for(var i=0; i<this.ArrHKCode.length; i++)
				if(this.ArrHKCode[i][idxCodeId] == codeId)
					return false;

			this.keyStrokesInit = false;

			return this.ArrHKCode.push([String(keysString), String(execCode), codeId, String(name), hkId]);
		};

		// keysString: Ctrl+Alt+Shift+KeyCode
		this.GetExCode = function(keysString)
		{
			var ret = "";
			if(keysString)
			{
				if(this.keyStrokesInit == false)
				{
					this.keyStrokes = {};
					this.keyStrokesInit = true;
					for (var i = 0; i < this.ArrHKCode.length; i++)
					{
						var ks = this.ArrHKCode[i][idxKS];
						if(ks != '')
						{
							if(!this.keyStrokes[ks])
							{
								this.keyStrokes[ks] = '';
							}
							else
							{
								this.keyStrokes[ks] += ' ';
							}
							this.keyStrokes[ks] += this.ArrHKCode[i][idxCode];
						}
					}
				}

				if(this.keyStrokes[keysString])
				{
					ret = this.keyStrokes[keysString];
				}
			}

			return ret;
		};

		this.MakeKeyString = function(Event)
		{
			this.inputDopString = (Event.ctrlKey ? 'Ctrl+':'') + (Event.altKey ? 'Alt+':'') + (Event.shiftKey ? 'Shift+':'');
			this.inputKeyCode = Event.keyCode;

			if(!this.inputKeyCode)
				this.inputKeyCode = Event.charCode;

			return this.inputDopString + this.inputKeyCode;
		};

		this.ShowMenu = function()
		{
			return '<table class="bx-hk-settings-toolbar" cellspacing="0" cellpadding="0" border="0">'+
				'<tr><td class="bx-left"><div class="bx-hk-settings-empty"></div></td>'+
				'<td class="bx-content">'+
				'<a class="bx-context-button" hidefocus="true" href="javascript:void(0)" onclick="BXHotKeys.Import();">'+
				'<span class="bx-context-button-icon btn-import"></span>'+
				'<span class="bx-context-button-text">'+this.MesImport+'</span>'+
				'</a>'+
				'<a class="bx-context-button" hidefocus="true" href="javascript:void(0)" onclick="BXHotKeys.Export();">'+
				'<span class="bx-context-button-icon btn-export"></span>'+
				'<span class="bx-context-button-text">'+this.MesExport+'</span>'+
				'</a>'+
				'<a class="bx-context-button" hidefocus="true" href="javascript:void(0)" onclick="if(confirm(BXHotKeys.MesDelConfirm)) BXHotKeys.DelAll();">'+
				'<span class="bx-context-button-icon btn-delall"></span>'+
				'<span class="bx-context-button-text">'+this.MesDelAll+'</span>'+
				'</a>'+
				'<a class="bx-context-button" hidefocus="true" href="javascript:void(0)" onclick="if(confirm(BXHotKeys.MesDefaultConfirm)) { BXHotKeys.DelAll(); BXHotKeys.SetDefault(); }">'+
				'<span class="bx-context-button-icon btn-default"></span>'+
				'<span class="bx-context-button-text">'+this.MesDefault+'</span>'+
				'</a></td>'+
				'<td class="bx-right"><div class="bx-hk-settings-empty"></div></td></tr>'+
				'</table>';
		};

		this.ShowSettings = function()
		{
			var formText ='<table width="100%" id="tbl_hk_settings">';
			var keyStr="";
			var editStr="";

			for(var i=0; i<this.ArrHKCode.length; i++)
			{
				if(this.ArrHKCode[i][idxKS])
					keyStr=this.PrintKSAsChar(this.ArrHKCode[i][idxKS]);
				else
					keyStr=this.MesNotAssign;

				if(this.ArrHKCode[i][idxCode])
					editStr = "<td width='30%' id='hotkeys-float-form-"+this.ArrHKCode[i][idxCodeId]+"'><a href='javascript:void(0)' onclick='BXHotKeys.SubstInput("+this.ArrHKCode[i][idxCodeId]+", "+
							this.ArrHKCode[i][idxHKId]+", \""+this.ArrHKCode[i][idxKS]+"\");' title='"+this.MesClToChange+"' class='bx-hk-settings'>"+keyStr+"</a></td><td width='10%' align='right' id='hotkeys-float-form-del-"+this.ArrHKCode[i][idxCodeId]+"'><a href='javascript:void(0)' onclick='BXHotKeys.DeleteBase("+
							this.ArrHKCode[i][idxCodeId]+","+this.ArrHKCode[i][idxHKId]+");' class='hk-delete-icon' title='"+this.MesDelete+"'></a></td>";
				else
					editStr ="<td width='30%'>&nbsp;</td><td width='10%'>&nbsp</td>";

				formText+="<tr class = 'bx-hk-settings-row'><td width='60%'>"+this.ArrHKCode[i][idxName]+"</td>"+editStr+"</tr>";
			}

			formText+='</table>';

			var btnClose = new BX.CWindowButton({
				'title': this.MesClose,
				'action': function() { this.parentWindow.Close(); }
			});

			var obWnd = new BX.CDialog({
							title: this.MesSettings,
							content: formText,
							buttons: [btnClose],
							width: 500,
							height: 400,
							resizable: false
						});

			this.tblSettParent=BX("tbl_hk_settings").parentNode;
			BX.addCustomEvent(obWnd, 'onWindowClose', function(obWnd) {
																		obWnd.DIV.parentNode.removeChild(obWnd.DIV);
																		_this.Register();
																	});

			//some customization to standart BX.CDialog
			var hk_menu_div = document.createElement("div");
			hk_menu_div.className = "bx-hk-settings-toolbar";
			hk_menu_div.innerHTML = this.ShowMenu();

			var dialog_head = BX.findChild(obWnd.DIV, {attribute: {'class': 'bx-core-adm-dialog-content'}}, true );

			if(dialog_head)
			{
				dialog_head.appendChild(hk_menu_div);
				BX.findChild(obWnd.DIV, {attribute: {'class': 'bx-core-adm-dialog-content'}}, true ).style.marginTop="37px";
			}
			else // ie quirck mode
			{
				this.hk_getElementsByClass("bx-core-adm-dialog-head")[0].appendChild(hk_menu_div);
				this.hk_getElementsByClass("bx-hk-settings-toolbar",obWnd.DIV,"div")[0].style.width = "480px";
			}

			obWnd.Show();

			this.Unregister();
		};

		this.hk_getElementsByClass = function(className, node, tag)
		{
			node = node || document;
			tag = tag || '*';
			var list = node.getElementsByTagName(tag),
			length = list.length,
			result = [], i;
			for(i = 0; i < length; i++)
			{
				if(list[i].className == className)
				{
					result.push(list[i]);
					break;
				}
			}
			return result
		};

		this.DelAll = function()
		{
			_this.deleting = true;

			for(var i=0; i<this.ArrHKCode.length; i++)
			{
				_this.UpdateKS(this.ArrHKCode[i][idxCodeId],"");
				_this.UpdateHk(this.ArrHKCode[i][idxCodeId],0);
				_this.SubstAnch(this.ArrHKCode[i][idxCodeId], 0,"");
				_this.SubstDel(this.ArrHKCode[i][idxCodeId],0);
			}

			var request = new JCHttpRequest;
			var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=delete_all';
			var sParam = "&sessid="+phpVars.bitrix_sessid;
			request.Action = function (result)
			{
				_this.deleting = false;
			};

			request.Post(options_url, sParam);
		};

		this.Register = function()
		{
			try //reautorization gives unstable error
			{
				jsUtils.addEvent(document, 'keypress', _this.KeyPressHandler);
				jsUtils.addEvent(document, 'keydown', _this.KeyDownHandler);
			}
			catch (e)
			{
				//nothing
			}
		};

		this.Unregister = function()
		{
			jsUtils.removeEvent(document, 'keypress', _this.KeyPressHandler);
			jsUtils.removeEvent(document, 'keydown', _this.KeyDownHandler);
		};

		this.SetDefault = function()
		{
			var request = new JCHttpRequest;
			var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=set_default';
			var sParam = "&sessid="+phpVars.bitrix_sessid;

			request.Action = function (strDefHK)
			{
				if(strDefHK)
				{
					if(!strDefHK)
						return false;

					var arHK=[];
					var row="";
					var arStrHK=strDefHK.split(";;");

					for(var i=0; i<arStrHK.length; i++)
					{
						arHK=arStrHK[i].split("::");
						row=_this.UpdateHk(arHK[0],arHK[1]);
						if (row>=0)
						{
							_this.UpdateKS(arHK[0],arHK[2]);
							_this.SubstAnch(arHK[0],arHK[1],arHK[2]);
							_this.SubstDel(arHK[0],arHK[1]);
						}
					}
				}
			};

			//waiting while deleting hot-keys
			waiter =
				{
					func: function()
					{
						if (!(this.deleting))
						{
							request.Post(options_url, sParam);
							clearInterval(intervalID);
						}
					}
				};
			intervalID = window.setInterval(function(){ waiter.func.call(waiter) }, 1000);
		};

		this.IsKeysBusy = function(strKeyString,code_id)
		{
			for(var i=0; i<this.ArrHKCode.length; i++)
				if (this.ArrHKCode[i][idxKS]==strKeyString && this.ArrHKCode[i][idxCodeId]!=code_id)
					return true;

			return false;
		};

		this.SubstInput = function(code_id, hk_id, keysString)
		{

			var td = document.getElementById('hotkeys-float-form-'+code_id);

			if(!td)
				return false;

			td.innerHTML='';
			td.innerHTML = '<input type="text" class="adm-input" name="HUMAN_KEYS_STRING" size="10" maxlength="30" value="'+this.PrintKSAsChar(keysString)+'" id="HKeysString" autocomplete="off">'+
							'<input type="hidden" name="KEYS_STRING" value="'+keysString+'" id="KeysString">';

			var inpHKString = document.getElementById("HKeysString");
			var inpKString = document.getElementById("KeysString");

			inpHKString .onkeydown  = _this.SetInput;
			inpHKString .onkeypress = _this.SetInput;
			inpHKString .onkeyup = function ()
			{
				ShowWaitWindow();

				inpHKString .onblur ="";

				if(_this.IsKeysBusy(inpKString.value,code_id))
					if(!confirm(_this.MesBusy))
					{
						_this.SubstAnch(code_id, hk_id, keysString);
						return false;
					}


				_this.bxHotKeyCode=0;

				_this.UpdateKS(code_id,inpKString.value);

				if(hk_id)
				{
					_this.UpdateHk(code_id,hk_id);
					_this.UpdateBase(hk_id,inpKString.value);
				}
				else
					_this.AddBase(code_id,inpKString.value);

				_this.SubstAnch(code_id, hk_id, inpKString.value);

				CloseWaitWindow();
			};

			inpHKString.focus();

			inpHKString.onblur = function ()
			{
				_this.SubstAnch(code_id, hk_id, keysString);
			}
		};

		this.SubstAnch = function(code_id, hk_id, keysString)
		{
			var td = document.getElementById('hotkeys-float-form-'+code_id);
			if(td)
				td.innerHTML = "<a href='javascript:void(0)' onclick='BXHotKeys.SubstInput("+code_id+", "+hk_id+", \""+keysString+"\");' title='"+this.MesClToChange+"' class='bx-hk-settings'>"+(keysString ? this.PrintKSAsChar(keysString) : this.MesNotAssign)+"</a>";
		};

		this.SubstDel = function(code_id, hk_id)
		{
			var td = document.getElementById('hotkeys-float-form-del-'+code_id);
			if (td)
				td.innerHTML = "<a href='javascript:void(0)' onclick='BXHotKeys.DeleteBase("+code_id+","+hk_id+");' class='hk-delete-icon' title='"+this.MesDelete+"'></a>";
		};


		this.AddBase = function(code_id,keysString)
		{
			var request = new JCHttpRequest;
			var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=add';
			var sParam = "&KEYS_STRING="+encodeURIComponent(keysString)+"&CODE_ID="+code_id+"&USER_ID="+_this.uid+"&sessid="+phpVars.bitrix_sessid;
			request.Action = function (hk_id)
			{
				if(hk_id && (hk_id == Number(hk_id)))
				{
					var row =_this.UpdateHk(code_id,hk_id);
					if (row>=0)
					{
						_this.SubstAnch(code_id, hk_id,keysString);
						_this.SubstDel(code_id, hk_id);
					}
				}
			};
			request.Post(options_url, sParam);
		};

		this.Export = function()
		{
			window.open("/bitrix/admin/hot_keys_act.php?hkaction=export&sessid="+phpVars.bitrix_sessid);
		};

		this.OnFileInputChange = function(ob)
		{
			fileName = ob.value;
			fileName = fileName.replace(/\\/g, '/');
			fileName = fileName.substr(fileName.lastIndexOf("/")+1);

			if(ob.parentNode.childNodes[0].textContent)
				ob.parentNode.childNodes[0].textContent = fileName;
			else
				ob.parentNode.childNodes[0].innerText = fileName;
		};

		this.Import = function()
		{
			var formText = 	'<form action="/bitrix/admin/hot_keys_act.php?hkaction=import" method="post" enctype="multipart/form-data" target="upload_iframe" id="hk_import_form" name="hk_import_form">'+
							'<input type="hidden" name="sessid" value="'+BX.bitrix_sessid()+'">'+
							'<span class="adm-input-file"><span>'+this.MesChooseFile+'</span><input type="file" name="bx_hk_filename" id="bx_hk_filename" class="adm-designed-file" onchange="BXHotKeys.OnFileInputChange(this);"></span>'+
							'</form>'+
							'<iframe id="upload_iframe" name="upload_iframe" style="display:none"></iframe>';

			var btnClose = new BX.CWindowButton({
				'title': this.MesClose,
				'action': function() { this.parentWindow.Close(); }
			});

			var btnImport = new BX.CWindowButton({
				'title': this.MesImport,
				'action': function()
									{
										if(!BX('bx_hk_filename').value)
										{
											alert(_this.MesFileEmpty);
											return;
										}

										BX('hk_import_form').submit();
										ShowWaitWindow();
									}
			});


			var impWnd = new BX.CDialog({
							title: this.MesImpHeader,
							content: formText,
							buttons: [btnImport,btnClose],
							width: 300,
							height: 60,
							resizable: false
						});

			impWnd.Show();

		};

		this.OnImportResponse = function(hkNum)
		{
			if(hkNum)
				alert(_this.MesImpSuc+hkNum);
			else
				alert(_this.MesImpFalse);

			BX.reload();
		};

		this.UpdateBase = function(hk_id, keysString)
		{
			var request = new JCHttpRequest;
			var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=update';
			var sParam = "&KEYS_STRING="+encodeURIComponent(keysString)+"&ID="+hk_id+"&sessid="+phpVars.bitrix_sessid;
			request.Post(options_url, sParam);
		};

		this.DeleteBase = function(code_id, hk_id)
		{
			if(hk_id)
			{
				var request = new JCHttpRequest;
				var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=delete';
				var sParam = "&ID="+hk_id+"&sessid="+phpVars.bitrix_sessid;
				request.Post(options_url, sParam);
				_this.UpdateKS(code_id,"");
				_this.UpdateHk(code_id,0);
				_this.SubstAnch(code_id, 0,"");
				_this.SubstDel(code_id,0);
			}
		};

		this.PrintKSAsChar = function(strKeysString)
		{
			if(!strKeysString)
				return "";

			var lastPlus = strKeysString.lastIndexOf("+");
			if(lastPlus)
			{
				var charCode = strKeysString.substr(lastPlus+1,strKeysString.length - (lastPlus+1));
				var preChar = strKeysString.substr(0,lastPlus+1);
				if(charCode==16 || charCode==17 || charCode==18)
					return preChar.substr(0,preChar.length-1);
			}
			else
			{
				charCode = strKeysString;
				preChar = "";
			}

			var codeSymb=arServSymb[charCode];
			if(!codeSymb)
				codeSymb = String.fromCharCode(charCode);

			return preChar+codeSymb;
		};

		this.SetInput = function(e)
		{
			e = e || event;

			var inputDopString = (e.ctrlKey ? 'Ctrl+':'') + (e.altKey ? 'Alt+':'') + (e.shiftKey ? 'Shift+':'');

			if(e.keyCode && e.type!="keypress")
				_this.bxHotKeyCode = e.keyCode;

			var charCode;
			if(e.charCode==undefined)
				charCode = e.which;
			else
				charCode = e.charCode;

			if (charCode && (!_this.bxHotKeyCode || _this.bxHotKeyCode==17 || _this.bxHotKeyCode==18 || _this.bxHotKeyCode==16 || _this.bxHotKeyCode==224))
				_this.bxHotKeyCode = charCode;

			document.getElementById("KeysString").value = inputDopString + _this.bxHotKeyCode;
			document.getElementById("HKeysString").value = _this.PrintKSAsChar(document.getElementById("KeysString").value);
			return false;
		};

		//Key-handlers
		this.KeyPressHandler = function(e)
		{
			e = e || event;

			if(e.charCode > 256)
			{
				var ExCode=_this.GetExCode(_this.MakeKeyString(e));

				if (ExCode)
					eval(ExCode);
			}
		};

		this.KeyDownHandler = function(e)
		{
			e = e || event;

			var ExCode=_this.GetExCode(_this.MakeKeyString(e));

			if (ExCode)
				eval(ExCode);
		}
	}

	var BXHotKeys = new CBXHotKeys;
	BXHotKeys.Init();
	window.BXHotKeys = BXHotKeys;
}

