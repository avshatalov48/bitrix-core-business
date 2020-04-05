var jsGoogleCE = {
	map: null,
	arData: null,
	obForm: null,
	
	currentView: '',
	
	bPositionFixed: true,

	__arValidKeys: ['google_lat', 'google_lon', 'google_scale'],
	
	init: function() 
	{
		BX.loadCSS('/bitrix/components/bitrix/map.google.system/templates/.default/style.css');
	
		jsGoogleCE.map = GLOBAL_arMapObjects['system_search_edit'];
		
		jsGoogleCE.arData = arPositionData;
		jsGoogleCE.obForm = document.forms['bx_popup_form_google_map'];
		jsGoogleCE.obForm.onsubmit = jsGoogleCE.__saveChanges;
		
		google.maps.event.addListener(jsGoogleCE.map, 'dragend', jsGoogleCE.__getPositionValues);
		google.maps.event.addListener(jsGoogleCE.map, 'maptypeid_changed', jsGoogleCE.__getPositionValues);
		
		if (!jsGoogleCE.arData.google_lat || !jsGoogleCE.arData.google_lon || !jsGoogleCE.arData.google_scale)
		{
			var obPos = jsGoogleCE.map.getCenter();
			jsGoogleCE.arData.google_lat = obPos.lat();
			jsGoogleCE.arData.google_lon = obPos.lng();
			jsGoogleCE.arData.google_scale = jsGoogleCE.map.getZoom();
			jsGoogleCE.bPositionFixed = false;
		}
		else
		{
			jsGoogleCE.arData.google_scale = parseInt(jsGoogleCE.arData.google_scale);
			jsGoogleCE.bPositionFixed = true;
		}
		
		//alert(2);

		jsGoogleCE.setControlValue('google_lat', jsGoogleCE.arData.google_lat);
		jsGoogleCE.setControlValue('google_lon', jsGoogleCE.arData.google_lon);
		jsGoogleCE.setControlValue('google_scale', jsGoogleCE.arData.google_scale);

		jsGoogleCE.currentView = BX.message('google_current_view');
		
		jsGoogleCE.setControlValue('google_view', BX.message('google_MAP_VIEW_' + jsGoogleCE.map.getMapTypeId().toUpperCase()));
		
		BX('bx_restore_position').onclick = jsGoogleCE.restorePositionValues;
		BX('bx_google_map_controls').style.visibility = 'visible';
		BX('bx_google_map_address_search').style.visibility = 'visible';
	},
	
	__getPositionValues: function()
	{
		if (jsGoogleCE.bPositionFixed)
			return;
	
		var obPos = jsGoogleCE.map.getCenter();
		jsGoogleCE.arData.google_lat = obPos.lat();
		jsGoogleCE.arData.google_lon = obPos.lng();
		jsGoogleCE.arData.google_scale = jsGoogleCE.map.getZoom();
		
		jsGoogleCE.setControlValue('google_lat', jsGoogleCE.arData.google_lat);
		jsGoogleCE.setControlValue('google_lon', jsGoogleCE.arData.google_lon);
		jsGoogleCE.setControlValue('google_scale', jsGoogleCE.arData.google_scale);
		
		jsGoogleCE.currentView = jsGoogleCE.map.getMapTypeId().toUpperCase();
		
		jsGoogleCE.setControlValue('google_view', jsGoogleCE.currentView);
	},
	
	restorePositionValues: function(e)
	{
		BX.PreventDefault(e);
	
		if (jsGoogleCE.currentView && google.maps.MapTypeId[jsGoogleCE.currentView])
			jsGoogleCE.map.setMapTypeId(google.maps.MapTypeId[jsGoogleCE.currentView]);
		
		jsGoogleCE.map.setZoom(jsGoogleCE.arData.google_scale);
		jsGoogleCE.map.panTo(new google.maps.LatLng(jsGoogleCE.arData.google_lat, jsGoogleCE.arData.google_lon));
	},
	
	setFixedFlag: function(value)
	{
		jsGoogleCE.bPositionFixed = value;
		if (!value)
			jsGoogleCE.__getPositionValues();
	},
	
	setControlValue: function(control, value)
	{
		var obControl = jsGoogleCE.obForm['bx_' + control];
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
	
		for (var i = 0, len = jsGoogleCE.__arValidKeys.length; i < len; i++)
		{
			if (jsGoogleCE.__arValidKeys[i] == key)
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
				if (jsGoogleCE.__checkValidKey(i))
				{
					++cnt;
					str += jsGoogleCE.__serialize(i) + jsGoogleCE.__serialize(obj[i]);
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
		if (!jsGoogleCE.map) 
			return false;
	
		window.jsGoogleCEOpener_search.saveData(jsGoogleCE.__serialize(jsGoogleCE.arData), jsGoogleCE.currentView);
		
		return false;
	}
}

var jsGoogleCESearch = {
	bInited: false,

	map: null,
	geocoder: null,
	obInput: null,
	timerID: null,
	timerDelay: 1000,
	
	arSearchResults: [],
	
	obOut: null,
	
	__init: function(input)
	{
		if (jsGoogleCESearch.bInited) return;
		
		jsGoogleCESearch.map = jsGoogleCE.map;
		jsGoogleCESearch.obInput = input;
		
		input.form.onsubmit = function() {jsGoogleCESearch.doSearch(); return false;}
		
		input.onfocus = jsGoogleCESearch.showResults;
		input.onblur = jsGoogleCESearch.hideResults;
		
		jsGoogleCESearch.bInited = true;
	},
	
	setTypingStarted: function(input)
	{
		if (!jsGoogleCESearch.bInited)
			jsGoogleCESearch.__init(input);

		jsGoogleCESearch.hideResults();
			
		if (null != jsGoogleCESearch.timerID)
			clearTimeout(jsGoogleCESearch.timerID);
	
		jsGoogleCESearch.timerID = setTimeout(jsGoogleCESearch.doSearch, jsGoogleCESearch.timerDelay);
	},
	
	doSearch: function()
	{
		var value = BX.util.trim(jsGoogleCESearch.obInput.value);
		if (value.length > 1)
		{
			if (null == jsGoogleCESearch.geocoder)
				jsGoogleCESearch.geocoder = new google.maps.Geocoder();
			
			jsGoogleCESearch.geocoder.geocode({
				address: value, 
				language: BX.message('LANGUAGE_ID')
			}, jsGoogleCESearch.__searchResultsLoad);
		}
	},
	
	handleError: function()
	{
		alert(jsGoogleCE.jsMess.mess_error);
	},
	
	setOutputCoordinates: function()
	{
		var obPos = BX.pos(jsGoogleCESearch.obInput);
		jsGoogleCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
		jsGoogleCESearch.obOut.style.left = obPos.left + 'px';		
	},

	__generateOutput: function()
	{			
		jsGoogleCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsGoogleCESearch.obOut.className = 'bx-google-address-search-results';
		jsGoogleCESearch.setOutputCoordinates();
		jsGoogleCESearch.obOut.style.zIndex = parseInt(BX.WindowManager.Get().zIndex) + 200;
	},

	__searchResultsLoad: function(obResult, status)
	{
		var _this = jsGoogleCESearch;
		
		if (status != google.maps.GeocoderStatus.OK && status != google.maps.GeocoderStatus.ZERO_RESULTS)
		{
			_this.handleError({message: status});
			return;
		}
		
		if (!obResult)
		{
			_this.handleError();
		}
		else
		{
			if (null == _this.obOut)
				_this.__generateOutput();
			
			_this.obOut.innerHTML = '';
			_this.clearSearchResults();
			
			var len = 0;
			if (status == google.maps.GeocoderStatus.OK)
			{
				len = obResult.length;
				var obList = null;
			}
			
			if (len > 0) 
			{
				for (var i = 0; i < len; i++)
				{
					_this.arSearchResults[i] = obResult[i].geometry.location;
					
					var lnk_text = '';
					for (var j = 0; j < obResult[i].address_components.length; j++)
					{
						lnk_text += (lnk_text.length > 0 ? ', ' : '') + obResult[i].address_components[j].long_name;
					}
					
					_this.obOut.appendChild(BX.create('LI', {
						attrs: {className: i == 0 ? 'bx-google-first' : ''},
						children: [
							BX.create('A', {
								attrs: {href: "javascript:void(0)"},
								props: {BXSearchIndex: i},
								events: {click: _this.__showSearchResult},
								children: [
									BX.create('SPAN', {
										text: lnk_text
									})
								]
							})
						]
					}));
				}
			} 
			else 
			{
				_this.obOut.innerHTML = '<li class="bx-google-notfound">' + BX.message('google_nothing_found') + '</li>';
 			}
			
			_this.showResults();
		}
		
		//_this.map.redraw();
	},
	
	__showSearchResult: function(e)
	{
		if (null !== this.BXSearchIndex)
		{
			jsGoogleCESearch.map.panTo(jsGoogleCESearch.arSearchResults[this.BXSearchIndex]);
		}
		
		return BX.PreventDefault(e);
	},
	
	showResults: function()
	{
		if (null != jsGoogleCESearch.obOut)
		{
			jsGoogleCESearch.setOutputCoordinates();
			jsGoogleCESearch.obOut.style.display = 'block';
		}
	},

	hideResults: function()
	{
		if (null != jsGoogleCESearch.obOut)
		{
			setTimeout("jsGoogleCESearch.obOut.style.display = 'none'", 300);
		}
	},
	
	clearSearchResults: function()
	{
		for (var i = 0; i < jsGoogleCESearch.arSearchResults.length; i++)
		{
			delete jsGoogleCESearch.arSearchResults[i];
		}

		jsGoogleCESearch.arSearchResults = [];
	},
	
	clear: function()
	{
		if (!jsGoogleCESearch.bInited)
			return;
			
		jsGoogleCESearch.bInited = false;
		if (null != jsGoogleCESearch.obOut)
		{
			jsGoogleCESearch.obOut.parentNode.removeChild(jsGoogleCESearch.obOut);
			jsGoogleCESearch.obOut = null;
		}
		
		jsGoogleCESearch.arSearchResults = [];
		jsGoogleCESearch.map = null;
		jsGoogleCESearch.geocoder = null;
		jsGoogleCESearch.obInput = null;
		jsGoogleCESearch.timerID = null;
	}
}
