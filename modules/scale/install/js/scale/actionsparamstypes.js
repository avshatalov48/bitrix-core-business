
/**
 * Classes
 * BX.Scale.ActionsParamsTypes.Proto
 * BX.Scale.ActionsParamsTypes.String
 * BX.Scale.ActionsParamsTypes.Checkbox
 * BX.Scale.ActionsParamsTypes.Dropdown
 */

 ;(function(window) {

	if (BX.Scale.ActionsParamsTypes) return;
		BX.Scale.ActionsParamsTypes = {};

	/**
	 * Class BX.Scale.ActionsParamsTypes.Proto
	 * Abstract class for user params.
	 * @param paramId
	 * @param params
	 * @constructor
	 */
	BX.Scale.ActionsParamsTypes.Proto = {

		init: function(paramId, params)
		{
			this.id = paramId;
			this.domNodeId = "action_user_param_"+paramId;
			this.domNode = null;
			this.name = params.NAME;
			this.defaultValue = params.DEFAULT_VALUE;
			this.required = params.REQUIRED;
			this.type = params.TYPE;
			this.title = params.TITLE ? params.TITLE : '';
			this.pattern = params.PATTERN ? params.PATTERN : '';
		},

		/**
		 * Absract function generates HTML for UI
		 */

		/**
		 * Absract function generates DOM node
		 */
		createDomNode: function(){},

		/**
		 *  @returns {domNode}
		 */
		getDomNode: function()
		{
			return this.domNode;
		},

		/**
		 * Function returns entered by user value
		 */
		getValue: function()
		{
			var result = false;

			if(this.domNode && this.domNode.value !== undefined)
				result = this.domNode.value

			return result;
		}
	};

	/**
	 * Class BX.Scale.ActionsParamsTypes.String
	 */

	BX.Scale.ActionsParamsTypes.String = function(paramId, params)
	{
		this.init(paramId, params);

		this.createDomNode = function()
		{
			var type = this.type == "PASSWORD" ? "password" : "text",
				 _this = this;

			this.domNode = BX.create('INPUT', {props: {id: this.domNodeId, name: this.domNodeId, type: type}});

			if(this.title)
				this.domNode.title = this.title;

			if(this.pattern)
			{
				var re = new RegExp(this.pattern);
				BX.bind(this.domNode, 'keypress', function(event){

					var charCode = event.which || event.keyCode;

					if (!re.test(String.fromCharCode(charCode)))
					{
						if(event.preventDefault)
							event.preventDefault();
						else
							event.returnValue = false;
					}
				});
			}

			if(this.defaultValue !== undefined)
				this.domNode.value = this.defaultValue;

			if(this.required !== undefined && this.required == "Y")
			{
				this.domNode.onkeyup = this.domNode.oninput = this.domNode.onpaste = this.domNode.oncut = this.domNode.onblur = function(e){
					var empty = _this.isEmpty();
					BX.onCustomEvent("BXScaleActionParamKeyUp", [{paramId: _this.id, empty: empty }]);
				}
			}
		};

		this.isEmpty = function()
		{
			return (this.domNode.value.length <= 0);
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.String.prototype = BX.Scale.ActionsParamsTypes.Proto;

	/**
	 * Class BX.Scale.ActionsParamsTypes.Checkbox
	 */
	BX.Scale.ActionsParamsTypes.Checkbox = function(paramId, params)
	{
		this.init(paramId, params);
		this.checked = params.CHECKED == "Y" || this.defaultValue == "Y";
		this.string = params.STRING || "";

		this.createDomNode = function()
		{
			this.domNode = BX.create('INPUT', {props: {id: this.domNodeId, name: this.domNodeId, type: 'checkbox', checked: this.checked}});
		};

		this.getValue = function()
		{
			var domNode = BX(this.domNodeId),
				result = false;

			if(domNode && domNode.checked !== undefined)
				result = domNode.checked ? this.string : "";

			return result;
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.Checkbox.prototype = BX.Scale.ActionsParamsTypes.Proto;

	/**
	 * Class BX.Scale.ActionsParamsTypes.Dropdown
	 */
	BX.Scale.ActionsParamsTypes.Dropdown = function(paramId, params)
	{
		this.init(paramId, params);
		this.values = params.VALUES;

		this.createDomNode = function()
		{
			this.domNode = BX.create('SELECT', {props: {id: this.domNodeId, name: this.domNodeId}});

			for(var i in this.values)
			{
				var oOption = BX.create('OPTION');
				oOption.appendChild(document.createTextNode(this.values[i]));
				oOption.setAttribute("value", i);

				if (this.defaultValue)
				{
					oOption.defaultSelected = true;
					oOption.selected = true;
				}

				this.domNode.appendChild(oOption);
			}
		};

		this.getValue = function()
		{
			var result = false;

			if (this.domNode.selectedIndex != -1)
				result = this.domNode.options[this.domNode.selectedIndex].value;

			return result;
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.Dropdown.prototype = BX.Scale.ActionsParamsTypes.Proto;

	/**
	 * Class BX.Scale.ActionsParamsTypes.Text
	 */

	BX.Scale.ActionsParamsTypes.Text = function(paramId, params)
	{
		this.init(paramId, params);

		this.createDomNode = function()
		{
			this.domNode = BX.create('DIV');
			this.textNode = BX.create('SPAN', {html: this.defaultValue});
			this.inputNode = BX.create('INPUT', {props: {id: this.domNodeId, name: this.domNodeId, type: "hidden"}});

			if(this.defaultValue !== undefined)
				this.inputNode.value = this.defaultValue;

			this.domNode.appendChild(this.inputNode);
			this.domNode.appendChild(this.textNode);
		};

		this.getValue =  function()
		{
			var result = false;

			if(this.inputNode && this.inputNode.value !== undefined)
				result = this.inputNode.value;

			return result;
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.Text.prototype = BX.Scale.ActionsParamsTypes.Proto;

	BX.Scale.ActionsParamsTypes.File = function(paramId, params)
	{
		this.remotePaths = [];
		this.init(paramId, params);

		this.createDomNode = function()
		{
			this.domNode = BX.create('INPUT', {props: {id: this.domNodeId, name: this.domNodeId, type: 'file'}});

			var _this = this;

			BX.bind(this.domNode, 'change', function () {
				var files = _this.domNode.files,
					formData = new FormData();

				for (var i = 0; i < files.length; i++)
					formData.append(_this.name, files[i], files[i].name);

				formData.append('params[operation]', 'upload_files');
				formData.append('sessid', BX.bitrix_sessid());
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '/bitrix/admin/scale_ajax.php', true);

				xhr.onload = function ()
				{
					if (xhr.status === 200)
					{
						if(xhr.response)
						{
							var response = JSON.parse(xhr.response);

							if(response)
							{
								if(response.RESULT && response.RESULT == 'OK')
								{
									_this.remotePaths = [];

									if(response.FILES && response.FILES.length)
									{
										for(var i = 0, l=response.FILES.length; i < l; i++)
											_this.remotePaths.push(response.FILES[i]);

										BX.onCustomEvent(_this.domNode, "BXScaleActionFileGetRemotePaths", [{id: _this.id, remotePaths: _this.remotePaths }]);
									}
								}
								else if(response.ERROR)
								{
									BX.Scale.AdminFrame.alert(response.ERROR, 'File uploading error');
								}
							}
							else
							{
								BX.Scale.AdminFrame.alert('Can\'t parse server answer to json: '+xhr.response, 'Json parsing error');
							}
						}
					}
					else
					{
						BX.Scale.AdminFrame.alert('Can\'t upload file(s). Status = '+xhr.status+' ('+xhr.statusText+')');
					}
				};

				xhr.send(formData);
			})
		};

		this.isEmpty = function()
		{
			return (this.domNode.value.length <= 0);
		};

		this.getValue = function()
		{
			return this.remotePaths;
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.File.prototype = BX.Scale.ActionsParamsTypes.Proto;

	BX.Scale.ActionsParamsTypes.RemoteAndLocalPath = function(paramId, params)
	{
		this.init(paramId, params);

		this.createDomNode = function()
		{
			this.remotePath = new BX.Scale.ActionsParamsTypes.String(paramId, params);
			this.localFile = new BX.Scale.ActionsParamsTypes.File(paramId+'_lf', params);

			BX.addCustomEvent(this.localFile.domNode, "BXScaleActionFileGetRemotePaths", BX.proxy(function(params){
					if(params.remotePaths && params.remotePaths.length)
					{
						this.remotePath.domNode.value = params.remotePaths.pop();
					}
				},
				this
			));

			this.domNode = BX.create('SPAN', {props:{id: paramId}});
			this.domNode.appendChild(this.remotePath.domNode);
			this.domNode.appendChild(BX.create('SPAN',{html: '&nbsp;'}));
			this.domNode.appendChild(this.modifyFileInput(this.localFile.domNode));

			if(this.defaultValue !== undefined)
				this.remotePath.value = this.defaultValue;
		};

		this.modifyFileInput = function(fileInput)
		{
			var result = BX.adminFormTools.modifyFile(fileInput);
			result.firstChild.innerHTML = BX.message('SCALE_PANEL_JS_LOAD_FILE');
			result.style.marginLeft = '10px';
			return result;
		};

		this.getValue =  function()
		{
			return this.remotePath.getValue();
		};

		this.createDomNode();
	};

	BX.Scale.ActionsParamsTypes.RemoteAndLocalPath.prototype = BX.Scale.ActionsParamsTypes.Proto;

})(window);