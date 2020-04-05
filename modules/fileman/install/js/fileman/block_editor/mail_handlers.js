BXMailBlockEditorHandler = function()
{
	this.init = function()
	{
		this.helper = new BXBlockEditorHelper;
		this.handlerList = {};

		this.initHandlerVars();
		this.initHandlerList();
		this.bind();
	};

	this.bind = function()
	{
		if(!BX.BlockEditorManager)
		{
			return;
		}

		BX.addCustomEvent(BX.BlockEditorManager, 'onEditorAdd', BX.delegate(function(editor)
		{
			BX.addCustomEvent(editor, 'onBlockInitHandlers', BX.delegate(function(block, type)
			{
				if(!this.handlerList[type])
				{
					return;
				}

				block.editHandlerList = this.handlerList[type];
				this.helper.each(this.handlersByType[type]['prop'], function(value, key){
					block[key] = value;
				}, this);

			}, this));
		}, this));
	};

	this.initHandlerList = function()
	{
		this.handlerList = {};
		this.helper.each(this.handlersByType, function(type, typeCode)
		{
			// add handlers from presets
			this.helper.each(this.handlersByType[typeCode].preset.list, function(className)
			{
				this.helper.each(this.presetHandlers[className], function(handler, code)
				{
					var clonedHandler = BX.clone(handler);
					clonedHandler['className'] = className;
					if(!this.handlerList[typeCode])
					{
						this.handlerList[typeCode] = {}
					}
					this.handlerList[typeCode][code] = clonedHandler;
				}, this);
			}, this);

			// remove handlers that excluded from presets
			this.helper.each(type.preset.exclude, function(code)
			{
				if(this.handlerList[typeCode][code])
				{
					this.handlerList[typeCode][code] = null;
					delete this.handlerList[typeCode][code];
				}
			}, this);


			// add or rewrite special handlers
			this.helper.each(type.list, function(handler, code)
			{
				if(!this.handlerList[typeCode])
				{
					this.handlerList[typeCode] = {};
				}

				this.handlerList[typeCode][code] = handler;
			}, this);

		}, this);
	};

	this.initHandlerVars = function(){
		this.presetHandlers = {
			'bxBlockContentText':
			{
				'content': {
					'func':	function (node, param, value) {
						return this.helper.innerHTML(node, value);
					},
					'reload_dependence': [
						'column-count'
					]
				}
				/*
				,
				'color': {
					'func': function (node, param, value) {
						return this.helper.color(node, param, value);
					}
				},
				'font-size': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				},
				'text-align': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				},
				'font-family': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				}
				*/
			},
			'bxBlockContentEdge':
			{
				'background-color': {
					'func': function(node, param, value){
						return this.helper.color(node, param, value);
					}
				},
				'border': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				}
			},
			'bxBlockContentLine':
			{
				'background-color': {
					'func': function(node, param, value){
						return this.helper.color(node, param, value);
					}
				},
				'height': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				},
				'margin-top': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				},
				'margin-bottom': {
					func: function (node, param, value) {
						return this.helper.style(node, param, value);
					}
				}
			},
			'bxBlockContentImageGroup':
			{
				'src': {
					'func': function (node, param, value) {
						return this.helper.groupImageSrc(node, value);
					},
					'params': {'multi': true}
				}
				/*
				,
				'groupimage-view': {
					'func': function (node, param, value)
					{
						return this.helper.groupImageView(node, value);
					}
				}
				*/
			},
			'bxBlockContentImage':
			{
				'align': {
					'func': function(node, param, value){
						this.helper.style(node, 'text-align', value);
						return this.helper.attr(BX.findChild(node, {'tag': 'img'}, true), param, value);
					}
				},
				'src': {
					'func': function(node, param, value){
						return this.helper.imageSrc(BX.findChild(node, {'tag': 'img'}, true), value);
					},
					'params': {'multi': false}
				},
				'title': {
					'func': function(node, param, value){
						return this.helper.attr(BX.findChild(node, {'tag': 'img'}, true), param, value);
					}
				},
				'href': {
					'func': function(node, param, value) {
						return this.helper.attr(BX.findChild(node, {'tag': 'a'}, true), param, value);
					}
				}
			},
			'bxBlockContentButton':
			{
				'button_caption': {
					'func': function(node, param, value){
						return this.helper.textContent(node, value);
					}
				},
				'color': {
					'func': function(node, param, value){
						return this.helper.color(node, param, value);
					}
				},
				'font-size': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				},
				'padding': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				},
				'href': {
					'func': function(node, param, value){
						return this.helper.attr(node, param, value);
					}
				},
				'text-decoration': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				}
			},
			'bxBlockContentButtonEdge': {
				'background-color': {
					'func': function(node, param, value){
						return this.helper.color(node, param, value);
					}
				},
				'wide': {
					'func': function(node, param, value){
						if(typeof(value) !== "undefined")
						{
							if(value === 'N')
							{
								node.removeAttribute('width');
							}
							else
							{
								node.setAttribute('width', '100%');
							}
						}

						if(node.getAttribute('width'))
						{
							return 'Y';
						}
						else
						{
							return 'N';
						}
					}
				},
				'border': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				},
				'border-radius': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				},
				'align': {
					'func': function(node, param, value){
						return this.helper.attr(node, param, value);
					}
				},
				'width': {
					'func': function(node, param, value){
						return this.helper.attr(BX.findChild(node, {'tag': 'td'}, true), param, value);
					}
				}
			},
			'bxBlockImageText': {
				'imagetextpart': {
					'func': function(node, param, value) {
						return this.helper.imageTextPart(node, value);
					}
				},

				'imagetextalign': {
					'func': function(node, param, value) {
						return this.helper.imageTextAlign(node, value);
					}
				}
			},
			'bxBlockContentSocial': {
				'font-size': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				},
				'color': {
					'func': function(node, param, value){
						return this.helper.color(node, param, value);
					}
				},
				'text-decoration': {
					'func': function(node, param, value){
						return this.helper.style(node, param, value);
					}
				}
			},
			'bxBlockContentEdgeSocial': {
				'social_content': {
					'func': function(node, param, value){
						var list;
						if(typeof(value) !== "undefined")
						{
							value = JSON.parse(value);
							var itemList = BX.findChildren(node, {tag: 'table'}, true);
							var diffLength = value.length - itemList.length;
							var diffLengthAbs = Math.abs(diffLength);
							var diffDelete = diffLength < 0;
							if (diffLength !== 0)
							{
								for (var i = 0; i < diffLengthAbs; i++)
								{
									if (diffDelete)
									{
										BX.remove(itemList.pop());
									}
									else
									{
										itemList[0].parentNode.appendChild(BX.clone(itemList[0]));
									}
								}
							}

							itemList = BX.findChildren(node, {tag: 'table'}, true);
							for (var j = 0; j < value.length; j++)
							{
								var itemValue = value[j];
								var a = BX.findChild(itemList[j], {'tag': 'a'}, true);
								a.textContent = itemValue.name.trim();
								a.href = itemValue.href.trim();
								a.title = itemValue.name.trim();
							}
						}

						list = BX.findChildren(node, {tag: 'a'}, true);
						var result = [];
						this.helper.each(list, function(a){
							result.push({'href': a.href, 'name': a.text.trim()});
						});

						return JSON.stringify(result);
					}
				},
				'align': {
					'func': function(node, param, value){
						return this.helper.attr(node, param, value);
					}
				}
			}
		};

		this.handlersByType = {
			'code': {
				preset: {
					list: [],
					exclude: []
				},
				list: {
					'html-raw': {
						'className': '',
						'func': function (node, param, value) {
							return this.helper.innerHTML(node, value);
						}
					}
				}
			},
			'line': {
				preset: {
					list: ['bxBlockContentLine'],
					exclude: []
				},
				list: {}
			},
			'text': {
				preset: {
					list: ['bxBlockContentText'],
					exclude: []
				},
				list: {
					'column-count':
					{
						'className': 'bxBlockContentText',
						'func': function (node, param, value) {
							return this.helper.column(node, param, value);
						}
					},
					'paddings': {
						'className': 'bxBlockContentText',
						'func': function(node, param, value) {
							return this.helper.paddings(node, param, value);
						}
					}
				}
			},
			'boxedtext': {
				preset: {
					list: ['bxBlockContentText', 'bxBlockContentEdge'],
					exclude: []
				},
				list: {
					'column-count':
					{
						'className': 'bxBlockContentText',
						'func': function (node, param, value) {
							return this.helper.column(node, param, value);
						}
					},
					'paddings': {
						'className': 'bxBlockContentEdge',
						'func': function(node, param, value) {
							return this.helper.paddings(node.parentNode, param, value);
						}
					}
				}
			},
			'image': {
				preset: {
					list: ['bxBlockContentImage'],
					exclude: []
				},
				list: {
					'paddings': {
						'className': 'bxBlockContentImage',
						'func': function(node, param, value) {
							return this.helper.paddings(node, param, value);
						}
					}
				},
				'prop':{
					'onMove': function(node, nearNode) {
						//this.helper.imageAutoWidth(node, nearNode);
					}
				}
			},
			'imagegroup': {
				preset: {
					list: ['bxBlockContentImageGroup'],
					exclude: ['href']
				},
				list: {
					'paddings': {
						'className': 'bxBlockContentImage',
						'func': function(node, param, value) {
							return this.helper.paddings(node, param, value);
						}
					}
				}
			},
			'imagetext': {
				preset: {
					list: ['bxBlockImageText', 'bxBlockContentImage', 'bxBlockContentText'],
					exclude: ['align']
				},
				list: {}
			},
			'boxedimage': {
				preset: {
					list: ['bxBlockContentEdge', 'bxBlockContentImage', 'bxBlockContentText'],
					exclude: []
				},
				list: {},
				'prop':{
					'onMove': function(node, nearNode) {
						//this.helper.imageAutoWidth(node, nearNode);
					}
				}
			},
			'button': {
				preset: {
					list: ['bxBlockContentButtonEdge', 'bxBlockContentButton'],
					exclude: []
				},
				list: {}
			},
			'social': {
				preset: {
					list: ['bxBlockContentSocial', 'bxBlockContentEdgeSocial'],
					exclude: []
				},
				list: {}
			}
		};
	};

	var _this = this;
	BX.ready(function(){
		_this.init();
	});
};

new BXMailBlockEditorHandler;