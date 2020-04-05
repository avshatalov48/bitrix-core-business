var jsGoogleCE = {
	map: null,
	arData: null,
	obForm: null,
	
	currentView: '',
	
	bPositionFixed: true,
	bAddPointMode: false,
	bAddPolyMode: false,
	
	DblClickObserver: null,
	
	__arValidKeys: ['google_lat', 'google_lon', 'google_scale', 'PLACEMARKS', 'LON', 'LAT', 'TEXT'],
	
	__currentPolyLine: null,
	__currentPolyLineObject: null,
	
	init: function() 
	{
		BX.loadCSS('/bitrix/components/bitrix/map.google.system/templates/.default/style.css');
	
		jsGoogleCE.map = GLOBAL_arMapObjects['system_view_edit'];
		
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
		
		jsGoogleCE.setControlValue('google_lat', jsGoogleCE.arData.google_lat);
		jsGoogleCE.setControlValue('google_lon', jsGoogleCE.arData.google_lon);
		jsGoogleCE.setControlValue('google_scale', jsGoogleCE.arData.google_scale);

		jsGoogleCE.currentView = BX.message('google_current_view');
		
		jsGoogleCE.setControlValue('google_view', BX.message('google_MAP_VIEW_' + jsGoogleCE.map.getMapTypeId().toUpperCase()));
		
		if (BX.browser.IsIE())
		{
			// unreally dirty hack....
			var pm = new google.maps.Marker({
				position: new google.maps.LatLng(90, 0),
				map: jsGoogleCE.map
			});
		}
		
		if (jsGoogleCE.arData.PLACEMARKS && jsGoogleCE.arData.PLACEMARKS.length > 0)
		{
			for (var i = 0, len = jsGoogleCE.arData.PLACEMARKS.length; i < len; i++)
			{
				jsGoogleCE.addCustomPoint(jsGoogleCE.arData.PLACEMARKS[i], i);
			}
		}

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

		jsGoogleCE.setControlValue('google_view', BX.message('google_MAP_VIEW_' + jsGoogleCE.currentView));
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
	
	__updatePointPosition: function()
	{
		if (null == this.BX_PLACEMARK_INDEX)
			return;
		
		var obPoint = this.getPosition();
		
		jsGoogleCE.arData.PLACEMARKS[this.BX_PLACEMARK_INDEX].LON = obPoint.lng();
		jsGoogleCE.arData.PLACEMARKS[this.BX_PLACEMARK_INDEX].LAT = obPoint.lat();
	},
	
	addPoint: function()
	{
		//jsUtils.PreventDefault(e);
	
		if (jsGoogleCE.bAddPointMode)
		{
			jsGoogleCE.bAddPointMode = false;
			jsGoogleCE.map.disableDoubleClickZoom = false;
			BX('bx_google_addpoint_link').style.display = 'block';
			BX('bx_google_addpoint_message').style.display = 'none';
			
			if (jsGoogleCE.DblClickObserver)
				google.maps.event.removeListener(jsGoogleCE.DblClickObserver);
		}
		else
		{
			jsGoogleCE.bAddPointMode = true;
			jsGoogleCE.map.disableDoubleClickZoom = true;
			document.getElementById('bx_google_addpoint_link').style.display = 'none';
			document.getElementById('bx_google_addpoint_message').style.display = 'block';
			
			jsGoogleCE.DblClickObserver = google.maps.event.addListener(jsGoogleCE.map, 'dblclick', jsGoogleCE.__addPoint);
		}
	},

	__openBalloon: function(obPlacemark)
	{
		if (null == obPlacemark.infowin)
		{
			obPlacemark.infowin = new google.maps.InfoWindow({
				content: ''
			});
		}
		
		obPlacemark.infowin.setContent('<textarea onblur="jsGoogleCE.__updatePointView(this, \'blur\')" onkeyup="jsGoogleCE.__updatePointView(this, \'keyup\')" id="BX_PLACEMARK_TEXT_' + obPlacemark.BX_PLACEMARK_INDEX + '" rows="4" style="resize:none;">' + BX.util.htmlspecialchars(jsGoogleCE.arData.PLACEMARKS[obPlacemark.BX_PLACEMARK_INDEX].TEXT) + '</textarea>');
		
		obPlacemark.infowin.open(jsGoogleCE.map, obPlacemark);
		
		setTimeout(function() {
			try {
				BX('BX_PLACEMARK_TEXT_' + obPlacemark.BX_PLACEMARK_INDEX).focus();
			} catch(e) {}
		}, 500);
	},
	
	__pointClick: function() 
	{
		jsGoogleCE.__openBalloon(this);
	},
	
	__createPlaceMark: function(arPlacemark, index)
	{
		if (null == jsGoogleCE.arData.PLACEMARKS)
			jsGoogleCE.arData.PLACEMARKS = [];
		
		if (null == index)
		{
			index = jsGoogleCE.arData.PLACEMARKS.length;
			jsGoogleCE.arData.PLACEMARKS[index] = {
				TEXT: arPlacemark.TEXT
			};
		}
		
		if (null != arPlacemark.POS)
		{
			jsGoogleCE.arData.PLACEMARKS[index].LON = arPlacemark.POS.lng();
			jsGoogleCE.arData.PLACEMARKS[index].LAT = arPlacemark.POS.lat();
		}
		else
		{
			jsGoogleCE.arData.PLACEMARKS[index].LAT = arPlacemark.LAT;
			jsGoogleCE.arData.PLACEMARKS[index].LON = arPlacemark.LON;
		}

		var obPointView = jsGoogleCE.__createPointView();
		obPointView.id = 'BX_PLACEMARK_' + index;
		
		var obPoint = new google.maps.LatLng(jsGoogleCE.arData.PLACEMARKS[index].LAT, jsGoogleCE.arData.PLACEMARKS[index].LON);

		var value_view = '';
		if (arPlacemark.TEXT.length > 0)
		{
			var rnpos = arPlacemark.TEXT.indexOf("\n");
			value_view = rnpos <= 0 ? arPlacemark.TEXT : arPlacemark.TEXT.substring(0, rnpos);
			value_view = BX.util.htmlspecialchars(value_view);
		}

		obPointView.BXPlacemark = new google.maps.Marker({
			position: obPoint,
			map: jsGoogleCE.map,
			draggable: true
		});
		
		obPointView.BXPlacemark.BX_PLACEMARK_INDEX = index;
		
		obPointView.BXPlacemark.BXTEXT = '';
		if (null != arPlacemark.TEXT && arPlacemark.TEXT.length > 0)
			obPointView.BXPlacemark.BXTEXT = arPlacemark.TEXT.replace(/\n/g, '<br />');
			
		google.maps.event.addListener(obPointView.BXPlacemark, "click", jsGoogleCE.__pointClick);
		google.maps.event.addListener(obPointView.BXPlacemark, "dragend", jsGoogleCE.__updatePointPosition);

		jsGoogleCE.__updatePointViewText(obPointView, value_view ? value_view : BX.message('google_noname'));
		
		return obPointView.BXPlacemark;
	},
	
	addCustomPoint: function(arPointInfo, index)
	{
		jsGoogleCE.__createPlaceMark({
			TEXT: arPointInfo.TEXT,
			LON: arPointInfo.LON,
			LAT: arPointInfo.LAT
		}, index);
	},
	
	__addPoint: function(obPoint)
	{
		if (!jsGoogleCE.bAddPointMode)
			return;
		
		jsGoogleCE.__openBalloon(jsGoogleCE.__createPlaceMark({
			TEXT: '', POS: obPoint.latLng
		}));
	},
	
	__point_link_hover: function() {this.style.backgroundColor = "#E3E8F7"; this.firstChild.style.display = 'block';},
	__point_link_hout: function() {this.style.backgroundColor = "transparent"; this.firstChild.style.display = 'none';},
	
	__createPointView: function()
	{
		var obView = BX('bx_google_points').appendChild(BX.create('LI', {
			events: {
				mouseover: jsGoogleCE.__point_link_hover,
				mouseout: jsGoogleCE.__point_link_hout
			},
			children: [
				BX.create('A', {
					attrs: {
						href: 'javascript: void(0)',
						className: 'bx-google-delete'
					},
					events: {
						click: jsGoogleCE.__deletePoint
					},
					style: {
						display: 'none'
					}
				}),
				BX.create('A', {
					attributes: {
						href: 'javascript: void(0)',
						className: 'bx-google-point'
					},
					events: {
						click: jsGoogleCE.__openPointBalloonFromView
					},
					html: BX.message('google_noname')
				})
			]
		}));
		
		return obView;
	},
	
	__deletePoint: function(e)
	{
		BX.PreventDefault(e);
	
		var obView = this.parentNode;
		
		jsGoogleCE.arData.PLACEMARKS[obView.BXPlacemark.BX_PLACEMARK_INDEX].DELETED = 1;
		
		if (obView.BXPlacemark.infowin)
			obView.BXPlacemark.infowin = null;
		obView.BXPlacemark.setMap(null);
		
		this.parentNode.parentNode.removeChild(this.parentNode);
	},
	
	__updatePointViewText: function(obPointView, str)
	{
		obPointView.firstChild.nextSibling.innerHTML = str;
        obPointView.firstChild.nextSibling.style.margin = '0 18px 0 0';
	},
	
	__openPointBalloonFromView: function(e)
	{
		BX.PreventDefault(e);
		jsGoogleCE.__openBalloon(this.parentNode.BXPlacemark);
	},
	
	__updatePointView: function(obTextarea, type)
	{
		var value = obTextarea.value;
		var index = obTextarea.id.substring(18)

		jsGoogleCE.arData.PLACEMARKS[index].TEXT = value;
		
		var rnpos = value.indexOf("\n");

		var value_view = '';
		if (value.length > 0)
			value_view = rnpos <= 0 ? value : value.substring(0, rnpos);

		value_view = BX.util.htmlspecialchars(value_view);
			
		var obView = document.getElementById('BX_PLACEMARK_' + index);
		
		jsGoogleCE.__updatePointViewText(obView, value_view ? value_view : BX.message('google_noname'));
		
		if (type == 'blur')
			obView.BXPlacemark.infowin.close();
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
					if (obj.charCodeAt(q) > 2047) offset+=2;
					else if (obj.charCodeAt(q) > 127) offset++;
				}
			}
			
  			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	},
	
	__saveChanges: function()
	{
		if (!jsGoogleCE.map) 
			return false;
			
		jsGoogleCE.bAddPointMode = false;
		
		if (jsGoogleCE.arData['PLACEMARKS'])
		{
			var arNewPlacemarks = [];
		
			for(var i = 0, len = jsGoogleCE.arData.PLACEMARKS.length; i < len; i++)
			{
				if (null == jsGoogleCE.arData.PLACEMARKS[i].DELETED)
					arNewPlacemarks[arNewPlacemarks.length] = jsGoogleCE.arData.PLACEMARKS[i];
			}
			
			jsGoogleCE.arData.PLACEMARKS = arNewPlacemarks;
		}
	
		window.jsGoogleCEOpener.saveData(jsGoogleCE.__serialize(jsGoogleCE.arData), jsGoogleCE.currentView);
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
		var value = jsUtils.trim(jsGoogleCESearch.obInput.value);
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
	
	__generateOutput: function()
	{
		jsGoogleCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsGoogleCESearch.obOut.className = 'bx-google-address-search-results';
		jsGoogleCESearch.obOut.style.display = 'none';
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
        var obPos = jsUtils.GetRealPos(jsGoogleCESearch.obInput);
        jsGoogleCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
        jsGoogleCESearch.obOut.style.left = obPos.left + 'px';
        jsGoogleCESearch.obOut.style.zIndex = parseInt(BX.WindowManager.Get().zIndex) + 200;

        if (BX.findParent(jsGoogleCESearch.obInput, {"tag" : "div", "className" : "bx-core-window bx-core-adm-dialog"}).style.display == 'block')
        {
            if (null != jsGoogleCESearch.obOut)
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

