;(function(window) {
if (BX.translit) return;

var
	arTransTable = null,
	translatorCache = [],
	defaultParams = {
		max_len: 100,
		change_case: 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
		replace_space_and_other: true,
		replace_space: '_',
		replace_other: '_',
		delete_repeat_replace: true,
		use_google: false, // Yandex.Translator is really using here but we won't rename a setting name now
		replace_dict: 'TRANS',
		replace_way: 'LE', // LE - LANG->ENGLISH, EL - ENGLISH->LANG
		skip_r_test: false
	},
	r = {
		en: /[A-Z0-9]/i,
		space: /\s/
	};

BX.translit = function (str, params)
{
	if (null == params) params = {};
	for (var i in defaultParams)
	{
		if (typeof params[i] == 'undefined')
			params[i] = defaultParams[i];
	}
	if (params.change_case)
		params.change_case = params.change_case.toUpperCase();

	if (params.use_google && params.callback && !!BX.message('YANDEX_KEY'))
	{
		return (new BX.CYandexTranslator(str, params)).run();
	}
	else
	{
		var len = str.length;
		var str_new = '';
		var last_chr_new = '';

		for (var i = 0; i < len; i++)
		{
			var chr = str.charAt(i);

			if (!params.skip_r_test && r.en.test(chr))
			{
				chr_new = chr;
			}
			else if (!params.skip_r_test && r.space.test(chr))
			{
				if (
					!params.delete_repeat_replace
					||
					(i > 0 && last_chr_new != params.replace_space)
				)
					chr_new = params.replace_space;
				else
					chr_new = '';
			}
			else
			{
				var chr_new = __getChar(chr, params.change_case, params.replace_way, params.replace_dict, params.replace_separator);
				if (null === chr_new)
				{
					if (params.replace_space_and_other)
					{
						if (!params.delete_repeat_replace
							||
							(i > 0 && i != len-1 && last_chr_new != params.replace_other)
						)
							chr_new = params.replace_other;
						else
							chr_new = '';
					}
					else
					{
						chr_new = chr;
					}
				}
			}

			if (null != chr_new && chr_new.length > 0)
			{
				switch(params.change_case)
				{
					case 'L': chr_new = chr_new.toLowerCase(); break;
					case 'U': chr_new = chr_new.toUpperCase(); break;
				}

				str_new += chr_new;
				last_chr_new = chr_new;
			}

			if (str_new.length >= params.max_len)
				break;
		}

		if (null != params.callback && BX.type.isFunction(params.callback))
		{
			params.callback(str_new)
			return str_new;
		}
		else
			return str_new;
	}
};

BX.correctText = function(str, params)
{
	if (typeof(params) == 'undefined')
		params = {};

	params.max_len = params.max_len? params.max_len: str.length;
	params.replace_way = params.replace_way? params.replace_way: 'EL'; // LE - LANG->ENGLISH, EL - ENGLISH->LANG, AUTO - auto select
	params.mixed = params.mixed? true: false;

	if (!params.mixed || params.replace_way == 'AUTO')
	{
		var countEnglish = 0;
		var countForeign = 0;
		var len = str.length;
		var regEnglish = /[A-Z]/i;
		var regSpecial = /[0-9!@#$%\^\&\*:;"~_ \(\),.\+\=\-\\\{\}\?\<\>]/i;
		for (var i = 0; i < len; i++)
		{
			var chr = str.charAt(i);
			if (regEnglish.test(chr))
			{
				countEnglish++
			}
			else if (regSpecial.test(chr))
			{
			}
			else
			{
				countForeign++;
			}
		}
	}
	if (params.replace_way == 'AUTO')
	{
		if (countForeign > countEnglish)
		{
			params.replace_way = 'LE';
		}
		else
		{
			params.replace_way = 'EL';
		}
	}

	if (params.replace_way == 'LE')
	{
		if (!params.mixed && (countEnglish > 0 || countForeign == 0))
		{
			return str;
		}
	}
	else if (!params.mixed && params.replace_way == 'EL')
	{
		if (!params.mixed && (countEnglish == 0 || countForeign > 0))
		{
			return str;
		}
	}

	return BX.translit(str, {
		'replace_dict': 'CORRECT',
		'replace_way': params.replace_way,
		'replace_separator': ' ',
		'skip_r_test': true,
		'change_case': false,
		'max_len': params.max_len,
		'delete_repeat_replace': false,
		'replace_space_and_other': false
	});
}

/* external translator interface class */
BX.IExternalTranslator = function(str, params)
{
	this.str = str;
	this.params = params;
}

BX.IExternalTranslator.prototype.run = function()
{
	var res = __checkCache(this.str);
	if (res)
		this.result({translation: res}, true);
	else
		this.translate();
};

/* translation function */
BX.IExternalTranslator.prototype.translate = function() {};

/* result processing function */
BX.IExternalTranslator.prototype.result = function(result, bSkipCache)
{
	if (!bSkipCache)
		translatorCache[translatorCache.length] = {original: this.str, translation: result.translation};

	this.params.use_google = false;
	BX.translit(result.translation, this.params);
};

/* Google Translate external class - DEPRECATED */
BX.CGoogleTranslator = function(str, params)
{
	BX.CBingTranslator.superclass.constructor.apply(this, arguments);
};
BX.extend(BX.CGoogleTranslator, BX.IExternalTranslator);

BX.CGoogleTranslator.prototype.translate = function()
{
	if (!window.google || typeof(window.google.load) != "function")
	{
		if (BX.browser.IsIE())
		{
			var cb_ie = BX.proxy(this.translate, this);
			var cb = function() {
				setTimeout(function() {
					cb_ie(arguments);
				}, 100);
			}
		}
		else
		{
			var cb = BX.proxy(this.translate, this);
		}

		BX.loadScript('http://www.google.com/jsapi?rnd=' + Math.random(), cb);
	}
	else if (!window.google.language)
	{
		google.load(
			'language', 1, {callback: BX.proxy(this.translate, this)}
		);
	}
	else
	{
		google.language.translate(
			this.str,
			BX.message('LANGUAGE_ID'),
			"en",
			BX.delegate(this.result, this)
		);
	}
};

/* Bing Translate external class - DEPARECATED AFTER 2012-08-01 */
BX.CBingTranslator = function(str, params)
{
	BX.CBingTranslator.superclass.constructor.apply(this, arguments);
};
BX.extend(BX.CBingTranslator, BX.IExternalTranslator);

BX.CBingTranslator.prototype.translate = function()
{
	var cb_name = 'bing_translate_callback_' + parseInt(Math.random() * 10000),
		url = 'http://api.bing.net/json.aspx?AppId='+BX.message('BING_KEY')+'&Query=' + BX.util.urlencode(this.str.substr(0, 5000))+'&Sources=Translation&Version=' + (this.params.version||'2.2') + '&Translation.SourceLanguage='+BX.message('LANGUAGE_ID')+'&Translation.TargetLanguage=en&JsonType=callback&JsonCallback=' + cb_name;

	window[cb_name] = BX.proxy(this.result, this);
	BX.loadScript(url, function() {window[cb_name] = null});
};

BX.CBingTranslator.prototype.result = function(result, bSkipCache)
{
	var res = {translation: this.str};
	if (result)
	{
		if (result.translation)
			res = result;
		else if (
			result.SearchResponse
			&& result.SearchResponse.Translation
			&& result.SearchResponse.Translation.Results
			&& result.SearchResponse.Translation.Results[0]
		)
		{
			res.translation = result.SearchResponse.Translation.Results[0].TranslatedTerm;
		}
	}

	return BX.CBingTranslator.superclass.result.apply(this, [res, bSkipCache]);
};


/* Yandex Translate external class */
BX.CYandexTranslator = function(str, params)
{
	BX.CYandexTranslator.superclass.constructor.apply(this, arguments);
};
BX.extend(BX.CYandexTranslator, BX.IExternalTranslator);

BX.CYandexTranslator.prototype.translate = function()
{
	var arStr = this.str.substr(0,5000).split(/\n/), text = '', i;

	for (i=0; i<arStr.length; i++)
		text += '&text=' + BX.util.urlencode(arStr[i]);

	var langPair = (BX.message('LANGUAGE_ID') == 'ua' ? 'uk' : BX.message('LANGUAGE_ID')) + '-en';

	var cb_name = 'yandex_translate_callback_' + parseInt(Math.random() * 100000),
		url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key='+BX.message('YANDEX_KEY')+'&lang='+langPair+'&callback=' + cb_name + '&clientId=bitrix' + text;

	window[cb_name] = BX.proxy(this.result, this);
	BX.loadScript(url, function() {window[cb_name] = null});
};

BX.CYandexTranslator.prototype.result = function(result, bSkipCache)
{
	var res = {translation: this.str};
	if (!!result)
	{
		if (result.translation)
			res = result;
		else if (result.code == 200 && result.text.length > 0)
			res.translation = result.text.join("\n");
	}

	return BX.CYandexTranslator.superclass.result.apply(this, [res, bSkipCache]);
};

/* private static functions */

function __checkCache(str)
{
	for (var i = 0, len = translatorCache.length; i < len; i++)
	{
		if (translatorCache[i].original == str)
			return translatorCache[i].translation;
	}

	return null;
};

function __generateTransTable(replace_dict, replace_way, replace_separator)
{
	var
		arFrom = (BX.message(replace_dict+(replace_way == 'LE'? '_FROM': '_TO')) || '').split(replace_separator),
		arTo = (BX.message(replace_dict+(replace_way == 'LE'? '_TO': '_FROM')) || '').split(replace_separator),
		i, len;

	if (arTransTable == null)
		arTransTable = {};

	if (typeof(arTransTable[replace_dict]) == 'undefined')
		arTransTable[replace_dict] = {};

	arTransTable[replace_dict][replace_way] = [];
	for (i = 0, len = arFrom.length; i < len; i++)
	{
		arTransTable[replace_dict][replace_way][i] = [arFrom[i], arTo[i]];
	}
};

function __getChar(chr, change_case, replace_way, replace_dict, replace_separator)
{
	if (typeof(replace_separator) == 'undefined')
		replace_separator = ',';

	if (typeof(replace_dict) == 'undefined')
		replace_dict = 'TRANS';

	if (arTransTable == null || typeof(arTransTable[replace_dict]) == 'undefined' || typeof(arTransTable[replace_dict][replace_way]) == 'undefined')
		__generateTransTable(replace_dict, replace_way, replace_separator)

	for (var i=0, len = arTransTable[replace_dict][replace_way].length; i < len; i++)
	{
		if (chr === arTransTable[replace_dict][replace_way][i][0])
		{
			return arTransTable[replace_dict][replace_way][i][1];
		}
	}

	return null;
};

})(window)
