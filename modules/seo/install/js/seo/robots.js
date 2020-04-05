;(function(){

if (window.BX.seoParser)
	return;

var
	BX = window.BX,
	ACTIONS = [];

BX.seoParser = function(content, textarea)
{
	this.textarea = textarea;

	this.contentData = [];
	this.editors = [];
	this.editors_index = {};

	this.parseContent(content);

	BX.ready(BX.delegate(this.init, this));
};

BX.seoParser.prototype.init = function()
{
	this.textarea = BX(this.textarea);
	BX.bind(this.textarea, 'change', BX.delegate(this.parseContent, this));
};

BX.seoParser.prototype.parseContent = function(content)
{
	this.contentData = [];

	if(typeof content == 'undefined' || !BX.type.isString(content))
	{
		content = this.textarea.value;
	}

	if(content.length > 0)
	{
		var lines = content.split(/\n+/g);

		var userAgent = '',
			userAgentData = {name: '', data: []},
			i;

		for(i = 0; i < lines.length; i++)
		{
			var line = BX.util.trim(lines[i]);
			if(line.length > 0)
			{
				var rule;

				if(/^#/.test(line))
				{
					rule = [line];
				}
				else
				{
					rule = line.split(/:\s*/);
					rule = [rule.shift(), rule.join(':')];
				}

				if(rule[0].toUpperCase() == 'USER-AGENT')
				{
					if(userAgent !== '')
					{
						this.contentData.push(userAgentData);
					}

					userAgent = rule[1];
					userAgentData = {name: userAgent, data: []}
				}
				else if (!!userAgent)
				{
					if(rule[0].toUpperCase() == 'DISALLOW' &&
						(!rule[1]
						|| rule[1].replace(/#.*/, '') === ''
						)
					)
					{
						continue;
					}

					userAgentData.data.push(rule);
				}
			}
		}

		if(userAgent !== '')
		{
			this.contentData.push(userAgentData);
		}

		for(i = 0; i < this.editors.length; i++)
		{
			this.sendData(this.editors[i].getUserAgent());
		}
	}
};

BX.seoParser.prototype.compile = function()
{
	var i, j, text = '', nn = '\r\n';

	for(i = 0; i < this.contentData.length; i++)
	{
		if(typeof this.editors_index[this.contentData[i].name.toUpperCase()] != 'undefined')
		{
			delete this.contentData[i];
		}
	}

	for(i = 0; i < this.editors.length; i++)
	{

		this.contentData = BX.util.insertIntoArray(this.contentData, i, {
			name: this.editors[i].getUserAgent(),
			data: this.editors[i].getRules()
		});
	}

	this.contentData = BX.util.array_values(this.contentData);

	for (i = 0; i < this.contentData.length; i++)
	{
		if(BX.type.isArray(this.contentData[i].data)
			&& this.contentData[i].data.length > 0
		)
		{
			text += (text === '' ? '' : nn)
				+ 'User-Agent: ' + this.contentData[i].name + nn;

			for(j = 0; j < this.contentData[i].data.length; j++)
			{
				text += this.contentData[i].data[j][0]
				if(typeof this.contentData[i].data[j][1] != 'undefined'
					&& this.contentData[i].data[j][1].length > 0
				)
				{
					text += ': ' + this.contentData[i].data[j][1];
				}
				text += nn;
			}
		}
	}

	this.textarea.value = text;
};

BX.seoParser.prototype.registerEditor = function(editor)
{
	this.editors_index[editor.getUserAgent().toUpperCase()] = this.editors.length;
	this.editors.push(editor);

	this.sendData(editor.getUserAgent());
};

BX.seoParser.prototype.sendData = function(agent)
{
	agent = agent.toUpperCase();

	if(typeof this.editors_index[agent] != 'undefined')
	{
		for(var i = 0; i < this.contentData.length; i++)
		{
			if(this.contentData[i].name.toUpperCase() == agent)
			{
				this.editors[this.editors_index[agent]].setRules(
					this.contentData[i].data||[]
				);

				return;
			}
		}

		this.editors[this.editors_index[agent]].setRules([]);
	}
};


BX.seoEditor = function(params)
{
	this.service = params.service;
	this.userAgent = params.userAgent;
	this.cont = {
		rules: params.cont.rules,
		buttons: params.cont.buttons
	}

	this.rules = [];

	BX.ready(BX.delegate(this.init, this));
};

BX.seoEditor.prototype.init = function()
{
	this.cont.rules = BX(this.cont.rules);
	this.cont.buttons = BX(this.cont.buttons);

	this.build();
	this.buildButtons();
};

BX.seoEditor.prototype.buildButtons = function()
{
	var gc = function(i, editor)
	{
		return function(e)
		{
			ACTIONS[i].callback(editor)
			return BX.PreventDefault(e);
		}
	}

	for(var i = 0; i < ACTIONS.length; i++)
	{
		if(typeof ACTIONS[i].compatible != 'undefined'
			&& BX.type.isArray(ACTIONS[i].compatible)
			&& !BX.util.in_array(this.service, ACTIONS[i].compatible)
		)
		{
			continue;
		}

		this.cont.buttons.appendChild(BX.create('DIV', {
			props: {
				className: 'seo-robots-action'
			},
			children: [
				BX.create('INPUT', {
					props: {
						type: 'button',
						className: 'seo-robots-action-button ' + (ACTIONS[i].className||'adm-btn'),
						name: ACTIONS[i].name||'',
						value: ACTIONS[i].text,
						title: ACTIONS[i].title||''
					},
					events: {
						click: gc(i, this)
					}
				})
			]
		}));
	}
};

BX.seoEditor.prototype.build = function()
{
	if(!BX.isReady)
	{
		BX.ready(BX.delegate(this.build, this));
		return;
	}

	this.cont.rules.innerHTML = '';

	this.cont.rules.appendChild(BX.create('DIV', {text: 'User-Agent: ' + this.userAgent}));

	this.rules = BX.util.array_values(this.rules);

	for(var i = 0; i < this.rules.length; i++)
	{
		if(!!this.rules[i])
		{
			var text = this.rules[i][0];
			if(typeof this.rules[i][1] != 'undefined'
				&& this.rules[i][1].length > 0)
				text += ': ' + this.rules[i][1];
			this.cont.rules.appendChild(
				BX.create('DIV', {
					props: {
						className: 'seo-robots-entry'
					},
					children: [
						BX.create('SPAN', {text: text}),
						BX.create('SPAN', {props: {className: 'seo-robots-delete'}, events: {click: this.getDeleteAction(i)}})
					]
				})
			);
		}
	}
};

BX.seoEditor.prototype.getDeleteAction = function(i)
{
	return BX.delegate(function(){
		delete this.rules[i];
		this.build();
	}, this);
};

BX.seoEditor.prototype.getUserAgent = function()
{
	return this.userAgent;
};

BX.seoEditor.prototype.getRules = function(rule)
{
	var rules = [],
		cnt = this.rules.length;

	if(!!rule)
		rule = rule.toUpperCase();

	for(var i = 0; i < cnt; i++)
	{
		if(!rule || rule == this.rules[i][0].toUpperCase())
		{
			rules.push([this.rules[i][0], this.rules[i][1]]);
		}
	}

	return rules;
};

BX.seoEditor.prototype.setRules = function(rules, ruleType)
{
	if(typeof ruleType == 'undefined')
	{
		this.rules = rules;
	}
	else
	{
		var i;

		for(i = 0; i < this.rules.length; i++)
		{
			if(!!this.rules[i] && this.rules[i][0] == ruleType)
			{
				delete this.rules[i]
			}
		}

		if(!!rules && rules.length > 0)
		{
			for(i = 0; i < rules.length; i++)
			{
				this.rules.push([rules[i][0], rules[i][1]]);
			}
		}
	}

	this.build();
};

BX.seoEditor.prototype.addRule = function(rule, bSkipUniqueCheck)
{
	if(!bSkipUniqueCheck)
	{
		for(var i = 0; i < this.rules.length; i++)
		{
			if(!!this.rules[i])
			{
				if(this.rules[i][0] == rule[0] && this.rules[i][1] == rule[1])
				{
					return;
				}
			}
		}
	}

	this.rules.push([rule[0], rule[1]]);
	this.build();
};

var tmpWindow = null;
var arStandardDisallow = ['*/index.php', '/bitrix/', '/*show_include_exec_time=', '/*show_page_exec_time=',
	'/*show_sql_stat=', '/*bitrix_include_areas=', '/*clear_cache=', '/*clear_cache_session=', '/*ADD_TO_COMPARE_LIST',
	'/*ORDER_BY', '/*PAGEN', '/*?print=', '/*&print=', '/*print_course=', '/*?action=', '/*&action=', '/*register=',
	'/*forgot_password=', '/*change_password=', '/*login=', '/*logout=', '/*auth=', '/*backurl=','/*back_url=',
	'/*BACKURL=','/*BACK_URL=', '/*back_url_admin=', '/*?utm_source=', '/*?bxajaxid=', '/*&bxajaxid=',
	'/*?view_result=', '/*&view_result='
];
var arStandardAllow = ['/bitrix/components/', '/bitrix/cache/', '/bitrix/js/', '/bitrix/templates/', '/bitrix/panel/'];

function getActionWindow()
{
	if(!tmpWindow)
	{
		tmpWindow = new BX.PopupWindow(Math.random(), null, {
			closeByEsc: true,
			closeIcon : true,
			titleBar: true,
			overlay: {
				backgroundColor: 'black', opacity: '50'
			}
		});
		tmpWindow.closeBtn = new BX.PopupWindowButtonLink({
			text : BX.message('JS_CORE_WINDOW_CLOSE'),
			className : "popup-window-button-link-cancel",
			events : {click : function(e) {
				this.popupWindow.close();
				return BX.PreventDefault(e);
			}}
		});
	}

	return tmpWindow;
}

function showFileDialog(cb, path)
{
	var cbName = "seo_callback_" + parseInt(Math.random() * 100000);
	window[cbName] = cb;

	var UserConfig =
	{
		site : BX.message('SEO_SITE_ID'),
		path : '/',
		view : "list",
		sort : "type",
		sort_order : "asc"
	};

	var oConfig =
	{
		submitFuncName: cbName,
		select: 'FD',
		operation: 'O',
		showUploadTab: false,
		showAddToMenuTab: false,
		site: BX.message('SEO_SITE_ID'),
		path: '/',
		lang: BX.message('LANGUAGE_ID'),
		fileFilter: '',
		allowAllFiles: false,
		saveConfig: true,
		sessid: BX.bitrix_sessid(),
		checkChildren: true,
		genThumb: true,
		zIndex: 2500
	};

	if(!!window.oBXFileDialog && !!window.oBXFileDialog.UserConfig)
	{
		UserConfig = window.oBXFileDialog.UserConfig;
		oConfig.path = UserConfig.path;
	}

	if (!!path)
	{
		oConfig.path = path;
	}

	window.oBXFileDialog = new window.BXFileDialog();
	window.oBXFileDialog.Open(oConfig, UserConfig);
}

ACTIONS.push({
	name: 'auto',
	compatible: ["common"],
	text: BX.message('SEO_ROBOT_ACTION_AUTO'),
	title: BX.message('SEO_ROBOT_ACTION_AUTO_TITLE'),
	className: 'adm-btn',
	callback: function(editor)
	{
		var host = BX.message('SEO_HOST');
		var wnd = getActionWindow();

		var hostRules = editor.getRules('Host');
		if(hostRules.length > 0)
		{
			host = '';
		}

		var configure = function()
		{
			var rules = [];
			var disallow_list = [];
			var allow_list = [];
			var editorRules = editor.getRules('Disallow');
			var i;

			for(i = 0; i < editorRules.length; i++)
			{
				if(typeof editorRules[1] != 'undefined' && editorRules[1] !== '')
				{
					disallow_list.push(editorRules[1]);
				}
			}

			editorRules = editor.getRules('Allow');
			for(i = 0; i < editorRules.length; i++)
			{
				if(typeof editorRules[1] != 'undefined' && editorRules[1] !== '')
				{
					allow_list.push(editorRules[1]);
				}
			}

			for(i = 0; i < arStandardDisallow.length; i++)
			{
				if(!BX.util.in_array(arStandardDisallow[i], disallow_list))
				{
					editor.addRule(['Disallow', arStandardDisallow[i]]);
				}
			}

			for(i = 0; i < arStandardAllow.length; i++)
			{
				if(!BX.util.in_array(arStandardAllow[i], allow_list))
				{
					editor.addRule(['Allow', arStandardAllow[i]]);
				}
			}

			if(host.length > 0)
			{
				editor.addRule(['Host', host]);
			}

			wnd.close();
		};

		if(host.length > 0 && host.substring(0, 4) != 'www.')
		{
			var divContent = BX.create('DIV', {html:'<input type="text" value="www.'+BX.util.htmlspecialchars(host)+'" class="seo-robots-settings-input">'});

			wnd.setTitleBar(BX.message('SEO_ROBOT_ACTION_MAIN_HOST'));
			wnd.setContent(divContent);
			wnd.setButtons([
				new BX.PopupWindowButton({
					text : BX.message('JS_CORE_WINDOW_SAVE'),
					className : "popup-window-button-accept",
					events: {
						click: function()
						{
							host = divContent.firstChild.value;
							configure();
						}
					}
				}),
				wnd.closeBtn
			]);

			wnd.show();
		}
		else
		{
			configure();
		}
	}
});

ACTIONS.push({
	name: 'disallow_url',
	text: BX.message('SEO_ROBOT_ACTION_DISALLOW'),
	title: BX.message('SEO_ROBOT_ACTION_DISALLOW_TITLE'),
	className: 'adm-btn',
	callback: function(editor)
	{
		var arRules = editor.getRules('Disallow');
		var str = '<div class="seo-robots-settings-row"><input type="text" value="#PATH#" class="seo-robots-settings-input"><input type="button" value="..."></div>';
		var strContent = '';

		for(var i = 0; i < arRules.length + 5; i++)
		{
			strContent += str.replace('#PATH#', BX.util.htmlspecialchars((arRules[i]||['',''])[1]));
		}

		var divContent = BX.create('DIV', {
			props: {className: 'seo-robots-settings'},
			events: {
				click: BX.delegateEvent({
					tagName: 'INPUT',
					property: {
						type: 'button',
						value: '...'
					}
				}, function()
				{
					var input = this.previousSibling;
					showFileDialog(function(filename, path, site, title, menu)
					{
						input.value = path.replace(/\/+$/, '') + '/' + filename;
					}, input.value)
				})
			},
			html:strContent
		});

		var wnd = getActionWindow();

		wnd.setTitleBar(BX.message('SEO_ROBOT_ACTION_DISALLOW_PATH'));
		wnd.setContent(divContent);
		wnd.setButtons([
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events: {
					click: function()
					{
						var node = divContent.firstChild;

						var rules = [];
						while(!!node)
						{
							if(node.tagName.toUpperCase() == 'DIV'
								&& !!node.firstChild
								&& node.firstChild.tagName.toUpperCase() == 'INPUT'
								&& node.firstChild.type == 'text'
								&& node.firstChild.value !== '')
							{
								rules.push(['Disallow', node.firstChild.value]);
							}

							node = node.nextSibling;
						}

						editor.setRules(rules, 'Disallow');
						wnd.close();
					}
				}
			}),
			wnd.closeBtn
		]);

		wnd.show();
	}
});

ACTIONS.push({
	name: 'allow_url',
	text: BX.message('SEO_ROBOT_ACTION_ALLOW'),
	title: BX.message('SEO_ROBOT_ACTION_ALLOW_TITLE'),
	className: 'adm-btn',
	callback: function(editor)
	{
		var arRules = editor.getRules('Allow');
		var str = '<div class="seo-robots-settings-row"><input type="text" value="#PATH#" class="seo-robots-settings-input"><input type="button" value="..."></div>';
		var strContent = '';

		for(var i = 0; i < arRules.length + 5; i++)
		{
			strContent += str.replace('#PATH#', BX.util.htmlspecialchars((arRules[i]||['',''])[1]));
		}

	var divContent = BX.create('DIV', {
			props: {className: 'seo-robots-settings'},
			events: {
				click: BX.delegateEvent({
					tagName: 'INPUT',
					property: {
						type: 'button',
						value: '...'
					}
				}, function()
				{
					var input = this.previousSibling;
					showFileDialog(function(filename, path, site, title, menu)
					{
						input.value = path.replace(/\/+$/, '') + '/' + filename;
					}, input.value)
				})
			},
			html:strContent
		});

		var wnd = getActionWindow();

		wnd.setTitleBar(BX.message('SEO_ROBOT_ACTION_ALLOW_PATH'));
		wnd.setContent(divContent);
		wnd.setButtons([
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events: {
					click: function()
					{
						var node = divContent.firstChild;

						var rules = [];
						while(!!node)
						{
							if(node.tagName.toUpperCase() == 'DIV'
								&& !!node.firstChild
								&& node.firstChild.tagName.toUpperCase() == 'INPUT'
								&& node.firstChild.type == 'text'
								&& node.firstChild.value !== '')
							{
								rules.push(['Allow', node.firstChild.value]);
							}

							node = node.nextSibling;
						}

						editor.setRules(rules, 'Allow');
						wnd.close();
					}
				}
			}),
			wnd.closeBtn
		]);

		wnd.show();
	}
});

ACTIONS.push({
	name: 'main_host',
	text: BX.message('SEO_ROBOT_ACTION_MAIN_HOST'),
	title: BX.message('SEO_ROBOT_ACTION_MAIN_HOST_TITLE'),
	className: 'adm-btn',
	callback: function(editor)
	{
		var arRules = editor.getRules('Host');

		var mainHost = BX.message('SEO_HOST'),
			bHttps = location.protocol == 'https:';

		if(arRules.length > 0)
		{
			mainHost = arRules[0][1];
			if(mainHost.substring(0, 8) == 'https://')
			{
				bHttps = true;
				mainHost = mainHost.substring(8, mainHost.length);
			}
		}

		var strContent = '<form name="host_form">';

		strContent += '<div class="seo-robots-settings-row"><input type="checkbox" id="main_host_https" name="main_host_https"'+(bHttps ? ' checked="checked"' : '')+'><label for="main_host_https">&nbsp;https</label></div>';

		var bChecked = (mainHost == BX.message('SEO_HOST')) || !mainHost;
		var bTextChecked = !bChecked;

		strContent += '<div class="seo-robots-settings-row"><input type="radio" name="main_host" value="'+BX.util.htmlspecialchars(BX.message('SEO_HOST'))+'" id="main_host_1"'+(bChecked ? ' checked="checked"' : '')+'><label for="main_host_1">'+BX.util.htmlspecialchars(BX.message('SEO_HOST'))+'</label></div>';

		if(BX.message('SEO_HOST').substring(0,4)!= 'www.')
		{
			bChecked = mainHost == 'www.' + BX.message('SEO_HOST');
			bTextChecked &= !bChecked;
			strContent += '<div class="seo-robots-settings-row"><input type="radio" name="main_host" value="www.'+BX.util.htmlspecialchars(BX.message('SEO_HOST'))+'" id="main_host_2"'+(bChecked ? ' checked="checked"' : '')+'><label for="main_host_2">www.'+BX.util.htmlspecialchars(BX.message('SEO_HOST'))+'</label></div>';
		}

		strContent += '<div class="seo-robots-settings-row"><input type="radio" name="main_host" value="" id="main_host_3"'+(bTextChecked ? ' checked="checked"' : '')+'><input type="text" name="main_host_value" value="'+(bTextChecked?mainHost:'')+'" onfocus="BX(\'main_host_3\').checked=true" class="seo-robots-settings-input"></div>';

		strContent += '</form>';

		var divContent = BX.create('DIV', {props: {className: 'seo-robots-settings'}, html:strContent});

		var wnd = getActionWindow();

		wnd.setTitleBar(BX.message('SEO_ROBOT_MAIN_HOST'));
		wnd.setContent(divContent);
		wnd.setButtons([
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events: {
					click: function()
					{
						var hostList = document.forms.host_form.main_host,
							host = BX.message('SEO_HOST'),
							rules = [];

						for(var i = 0; i < hostList.length; i++)
						{
							if(hostList[i].checked)
							{
								host = hostList[i].value;
								break;
							}
						}

						if(host === '')
						{
							host = document.forms.host_form.main_host_value.value;
						}

						if(host.length > 0)
						{
							if(document.forms.host_form.main_host_https.checked)
							{
								host = 'https://' + host;
							}

							rules.push(['Host', host]);
						}

						editor.setRules(rules, 'Host');
						wnd.close();
					}
				}
			}),
			wnd.closeBtn
		]);

		wnd.show();
		BX.adminFormTools.modifyFormElements(document.forms.host_form);
	}
});

ACTIONS.push({
	name: 'crawl_delay',
	text: BX.message('SEO_ROBOT_ACTION_CRAWL_DELAY'),
	title: BX.message('SEO_ROBOT_ACTION_CRAWL_DELAY_TITLE'),
	compatible: ['common', 'yandex'],
	className: 'adm-btn',
	callback: function(editor)
	{
		var arRules = editor.getRules('Crawl-delay');

		var value = 2;

		if(arRules.length > 0 && !isNaN(parseInt(arRules[0][1])))
		{
			value = arRules[0][1]; // no parseint here - we can strip comment this way
		}

		var strContent = '<input type="text" value="'+BX.util.htmlspecialchars(value)+'" class="seo-robots-settings-input">';

		var divContent = BX.create('DIV', {html:strContent});

		var wnd = getActionWindow();

		wnd.setTitleBar(BX.message('SEO_ROBOT_CRAWL_DELAY'));
		wnd.setContent(divContent);
		wnd.setButtons([
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events: {
					click: function()
					{
						var node = divContent.lastChild,
							rules = [];

						if(node.value.length > 0)
						{
							rules.push(['Crawl-delay', node.value]);
						}

						editor.setRules(rules, 'Crawl-delay');
						wnd.close();
					}
				}
			}),
			wnd.closeBtn
		]);

		wnd.show();
	}
});

ACTIONS.push({
	name: 'sitemap',
	compatible: ["common"],
	text: BX.message('SEO_ROBOT_ACTION_SITEMAP'),
	title: BX.message('SEO_ROBOT_ACTION_SITEMAP_TITLE'),
	className: 'adm-btn',
	callback: function(editor)
	{
		var host = 'http://' + BX.message('SEO_HOST');
		var arRules = editor.getRules('Host');
		if(arRules.length > 0)
		{
			host = arRules[0][1];
			if(!/^http[s]{0,1}:\/\//.test(host))
			{
				host = 'http://' + host;
			}
		}


		var value = host + '/sitemap.xml';

		var strContent = '<input type="text" value="'+BX.util.htmlspecialchars(value)+'" class="seo-robots-settings-input">';

		var divContent = BX.create('DIV', {html:strContent});

		var wnd = getActionWindow();

		wnd.setTitleBar(BX.message('SEO_ROBOT_ACTION_SITEMAP_URL'));
		wnd.setContent(divContent);
		wnd.setButtons([
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events: {
					click: function()
					{
						var node = divContent.lastChild;
						editor.addRule(['Sitemap', node.value]);
						wnd.close();
					}
				}
			}),
			wnd.closeBtn
		]);

		wnd.show();
	}
});

})();