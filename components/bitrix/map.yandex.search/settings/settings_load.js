var jsYandexCE_search = {
	map: null,
	arData: null,
	obForm: null,

	currentView: '',

	bPositionFixed: true,

	__arValidKeys: ['yandex_lat', 'yandex_lon', 'yandex_scale'],

	__obMapTypes: {
		'HYBRID': 'yandex#hybrid',
		'SATELLITE': 'yandex#satellite',
		'PUBLIC': 'yandex#publicMap',
		'PUBLIC_HYBRID': 'yandex#publicMapHybrid',
		'MAP': 'yandex#map'
	},

	__currentPolyLine: null,
	__currentPolyLineObject: null,

	_getTypeName: function(yandex_type)
	{
		return (
			yandex_type == 'yandex#hybrid'
				? 'HYBRID'
				: (
				yandex_type == 'yandex#satellite'
					? 'SATELLITE'
					: (
					yandex_type == 'yandex#publicMap'
						? 'PUBLIC'
						: (
						yandex_type == 'yandex#publicMapHybrid'
							? 'PUBLIC_HYBRID'
							: 'MAP'
						)
					)
				)
			);
	},

	init: function(map)
	{
		BX.loadCSS('/bitrix/components/bitrix/map.yandex.system/templates/.default/style.css');

		if (null != map)
			jsYandexCE_search.map = map; //GLOBAL_arMapObjects['system_view_edit'];

		// TODO: check!
		jsYandexCE_search.arData = arPositionData;

		jsYandexCE_search.obForm = document.forms['bx_popup_form_yandex_map'];
		jsYandexCE_search.obForm.onsubmit = jsYandexCE_search.__saveChanges;

		jsYandexCE_search.map.events.add("boundschange", jsYandexCE_search.__getPositionValues);
		jsYandexCE_search.map.events.add("sizechange", jsYandexCE_search.__getPositionValues);
		jsYandexCE_search.map.events.add("typechange", jsYandexCE_search.__getPositionValues);

		if (!jsYandexCE_search.arData.yandex_lat || !jsYandexCE_search.arData.yandex_lon || !jsYandexCE_search.arData.yandex_scale)
		{
			var obPos = jsYandexCE_search.map.getCenter();
			jsYandexCE_search.arData.yandex_lat = obPos[0];
			jsYandexCE_search.arData.yandex_lon = obPos[1];
			jsYandexCE_search.arData.yandex_scale = jsYandexCE_search.map.getZoom();
			jsYandexCE_search.bPositionFixed = false;
		}
		else
		{
			jsYandexCE_search.bPositionFixed = true;
		}

		jsYandexCE_search.currentView = jsYandexMess.current_view;
		jsYandexCE_search.setControlValue('yandex_lat', jsYandexCE_search.arData.yandex_lat);
		jsYandexCE_search.setControlValue('yandex_lon', jsYandexCE_search.arData.yandex_lon);
		jsYandexCE_search.setControlValue('yandex_scale', jsYandexCE_search.arData.yandex_scale);
		jsYandexCE_search.setControlValue('yandex_view', jsYandexMess['MAP_VIEW_' + jsYandexCE_search._getTypeName(jsYandexCE_search.map.getType())]);

		BX('bx_restore_position').onclick = jsYandexCE_search.restorePositionValues;
		BX('bx_yandex_position_fix').onclick = function () {jsYandexCE_search.setFixedFlag(this.checked)};
		jsYandexCE_search.setFixedFlag(BX('bx_yandex_position_fix').defaultChecked);

		BX('bx_yandex_map_controls').style.visibility = 'visible';
		BX('bx_yandex_map_address_search').style.visibility = 'visible';
	},

	__getPositionValues: function()
	{
		if (jsYandexCE_search.bPositionFixed)
			return;

		var obPos = jsYandexCE_search.map.getCenter();
		jsYandexCE_search.arData.yandex_lat = obPos[0];
		jsYandexCE_search.arData.yandex_lon = obPos[1];
		jsYandexCE_search.arData.yandex_scale = jsYandexCE_search.map.getZoom();
		
		jsYandexCE_search.setControlValue('yandex_lat', jsYandexCE_search.arData.yandex_lat);
		jsYandexCE_search.setControlValue('yandex_lon', jsYandexCE_search.arData.yandex_lon);
		jsYandexCE_search.setControlValue('yandex_scale', jsYandexCE_search.arData.yandex_scale);

		jsYandexCE_search.currentView = jsYandexCE_search._getTypeName(jsYandexCE_search.map.getType());
		jsYandexCE_search.setControlValue('yandex_view', jsYandexMess['MAP_VIEW_' + jsYandexCE_search.currentView]);
	},

	restorePositionValues: function(e)
	{
		if (jsYandexCE_search.currentView && jsYandexCE_search.__obMapTypes[jsYandexCE_search.currentView])
			jsYandexCE_search.map.setType(jsYandexCE_search.__obMapTypes[jsYandexCE_search.currentView]);

		jsYandexCE_search.map.setZoom(jsYandexCE_search.arData.yandex_scale);
		jsYandexCE_search.map.panTo([+jsYandexCE_search.arData.yandex_lat, +jsYandexCE_search.arData.yandex_lon]);

		return BX.PreventDefault(e);
	},

	setFixedFlag: function(value)
	{
		jsYandexCE_search.bPositionFixed = value;
		if (!value)
			jsYandexCE_search.__getPositionValues();
	},

	setControlValue: function(control, value)
	{
		var obControl = jsYandexCE_search.obForm['bx_' + control];
		if (null != obControl)
			obControl.value = value;
			
		var obControlOut = BX('bx_' + control + '_value');
		if (null != obControlOut)
			obControlOut.innerHTML = value;
	},

	__checkValidKey: function(key)
	{
		if (Number(key) == key)
			return true;
	
		for (var i = 0, len = jsYandexCE_search.__arValidKeys.length; i < len; i++)
		{
			if (jsYandexCE_search.__arValidKeys[i] == key)
				return true;
		}
		
		return false;
	},

	__serialize: function(obj)
	{
		if (typeof(obj) == 'object')
		{
			var str = '', cnt = 0;
			for (var i in obj)
			{
				if (jsYandexCE_search.__checkValidKey(i))
				{
					++cnt;
					str += jsYandexCE_search.__serialize(i) + jsYandexCE_search.__serialize(obj[i]);
				}
			}

			str = "a:" + cnt + ":{" + str + "}";

			return str;
		}
		else if (typeof(obj) == 'boolean')
		{
			return 'b:' + (obj ? 1 : 0) + ';';
		}
		else if (null == obj)
		{
			return 'N;'
		}
		else if (Number(obj) == obj && obj != '' && obj != ' ')
		{
			if (Math.floor(obj) == obj)
				return 'i:' + obj + ';';
			else
				return 'd:' + obj + ';';
    	}
  		else if(typeof(obj) == 'string')
  		{
			obj = obj.replace(/\r\n/g, "\n");
			obj = obj.replace(/\n/g, "###RN###");

			var offset = 0;
			if (window._global_BX_UTF)
			{
				for (var q = 0, cnt = obj.length; q < cnt; q++)
				{
					if (obj.charCodeAt(q) > 127) offset++;
				}
			}
			
  			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	},

	__saveChanges: function()
	{
		if (!jsYandexCE_search.map) 
			return false;

		window.jsYandexCEOpener_search.saveData(jsYandexCE_search.__serialize(jsYandexCE_search.arData), jsYandexCE_search.currentView);
		delete jsYandexCE_search.map;
		return false;
	}
}

var jsYandexCESearch = {
	bInited: false,

	map: null,
	obInput: null,
	timerID: null,
	timerDelay: 1000,

	arSearchResults: [],

	obOut: null,

	__init: function(input)
	{
		if (jsYandexCESearch.bInited) return;

		jsYandexCESearch.map = jsYandexCE_search.map;
		jsYandexCESearch.obInput = input;

		input.form.onsubmit = function() {jsYandexCESearch.doSearch(); return false;}

		input.onfocus = jsYandexCESearch.showResults;
		input.onblur = jsYandexCESearch.hideResults;

		jsYandexCESearch.bInited = true;
	},

	setTypingStarted: function(input)
	{
		if (!jsYandexCESearch.bInited)
			jsYandexCESearch.__init(input);

		jsYandexCESearch.hideResults();

		if (null != jsYandexCESearch.timerID)
			clearTimeout(jsYandexCESearch.timerID);

		jsYandexCESearch.timerID = setTimeout(jsYandexCESearch.doSearch, jsYandexCESearch.timerDelay);
	},

	doSearch: function()
	{
		var value = BX.util.trim(jsYandexCESearch.obInput.value);
		if (value.length > 1)
		{
			ymaps.geocode(value).then(
				jsYandexCESearch.__searchResultsLoad,
				jsYandexCESearch.handleError
			);
		}
	},

	handleError: function(error)
	{
		alert(this.jsMess.mess_error + ': ' + error.message);
	},

	__generateOutput: function()
	{
		var obPos = BX.pos(jsYandexCESearch.obInput);

		jsYandexCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsYandexCESearch.obOut.className = 'bx-yandex-address-search-results';
		jsYandexCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
		jsYandexCESearch.obOut.style.left = obPos.left + 'px';
		jsYandexCESearch.obOut.style.zIndex = parseInt(BX.WindowManager.Get().zIndex) + 200;
	},

	__searchResultsLoad: function(res)
	{
		if (null == jsYandexCESearch.obOut)
			jsYandexCESearch.__generateOutput();

		jsYandexCESearch.obOut.innerHTML = '';
		jsYandexCESearch.clearSearchResults();

		var len = res.geoObjects.getLength();
		if (len > 0)
		{
			for (var i = 0; i < len; i++)
			{
				jsYandexCESearch.arSearchResults[i] = res.geoObjects.get(i);

				var obListElement = document.createElement('LI');

				if (i == 0)
					obListElement.className = 'bx-yandex-first';

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				var obText = obLink.appendChild(document.createElement('SPAN'));

				obText.appendChild(document.createTextNode(
					jsYandexCESearch.arSearchResults[i].properties.get('metaDataProperty').GeocoderMetaData.text
				));

				obLink.BXSearchIndex = i;
				obLink.onclick = jsYandexCESearch.__showSearchResult;

				obListElement.appendChild(obLink);
				jsYandexCESearch.obOut.appendChild(obListElement);
			}
		}
		else
		{
			jsYandexCESearch.obOut.innerHTML = '<li class="bx-yandex-notfound">' + window.jsYandexMess.nothing_found + '</li>';
		}

		jsYandexCESearch.showResults();
	},

	__showSearchResult: function(e)
	{
		if (null !== this.BXSearchIndex)
		{
			jsYandexCESearch.map.panTo(jsYandexCESearch.arSearchResults[this.BXSearchIndex].geometry.getCoordinates());
		}

		return BX.PreventDefault(e);
	},

	showResults: function()
	{
		if (null != jsYandexCESearch.obOut)
			jsYandexCESearch.obOut.style.display = 'block';
	},

	hideResults: function()
	{
		if (null != jsYandexCESearch.obOut)
		{
			setTimeout("jsYandexCESearch.obOut.style.display = 'none'", 300);
		}
	},

	clearSearchResults: function()
	{
		for (var i = 0; i < jsYandexCESearch.arSearchResults.length; i++)
		{
			delete jsYandexCESearch.arSearchResults[i];
		}

		jsYandexCESearch.arSearchResults = [];
	},

	clear: function()
	{
		if (!jsYandexCESearch.bInited)
			return;

		jsYandexCESearch.bInited = false;
		if (null != jsYandexCESearch.obOut)
		{
			jsYandexCESearch.obOut.parentNode.removeChild(jsYandexCESearch.obOut);
			jsYandexCESearch.obOut = null;
		}

		jsYandexCESearch.arSearchResults = [];
		jsYandexCESearch.map = null;
		jsYandexCESearch.obInput = null;
		jsYandexCESearch.timerID = null;
	}
}

