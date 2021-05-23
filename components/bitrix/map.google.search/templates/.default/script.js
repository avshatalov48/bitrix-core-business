var JCBXGoogleSearch = function(map_id, obOut, jsMess)
{
	var _this = this;
	
	this.map_id = map_id;
	this.map = GLOBAL_arMapObjects[this.map_id];

	this.obOut = obOut;
	
	if (null == this.map)
		return false;

	this.arSearchResults = [];
	this.jsMess = jsMess;
	
	this.__searchResultsLoad = function(obResult, status)
	{
		if (status != google.maps.GeocoderStatus.OK && status != google.maps.GeocoderStatus.ZERO_RESULTS)
		{
			_this.handleError({message: status});
			return;
		}
		
		if (null == _this.obOut)
			return;

		if (!obResult)
		{
			_this.handleError();
		}
		
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
			obList = document.createElement('UL');
			obList.className = 'bx-google-search-results';
			var str = '';
			str += _this.jsMess.mess_search + ': <b>' + len + '</b> ' + _this.jsMess.mess_found + '.';
			
			for (var i = 0; i < len; i++)
			{
				_this.arSearchResults[i] = new google.maps.Marker({
					position: obResult[i].geometry.location,
					map: _this.map
				});
				
				var text = '';
				for (var j = 0; j < obResult[i].address_components.length; j++)
				{
					text += (text.length > 0 ? ', ' : '') + obResult[i].address_components[j].long_name;
				}
				_this.arSearchResults[i].infowin = new google.maps.InfoWindow({
					content: text
				});

				google.maps.event.addListener(_this.arSearchResults[i], 'click', _this.__pointClick);

				var obListElement = document.createElement('LI');

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				obLink.appendChild(document.createTextNode(text));
				
				obLink.BXSearchIndex = i;
				obLink.onclick = _this.__showSearchResult;
				
				obListElement.appendChild(obLink);
				obList.appendChild(obListElement);
			}
		} 
		else 
		{
			var str = _this.jsMess.mess_search_empty;
		}
		
		_this.obOut.innerHTML = str;
		
		if (null != obList)
		{
			_this.obOut.appendChild(obList);
			_this.BXSearchIndex = 0;
			_this.__showSearchResult(0);
		}
	};
	
	this.__showSearchResult = function(index)
	{
		if (null == index || index.constructor == window.Event);
			index = this.BXSearchIndex;
	
		if (null != index && null != _this.arSearchResults[index])
		{
			_this.__pointClick.apply(_this.arSearchResults[index]);
			_this.map.panTo(_this.arSearchResults[index].getPosition());
		}
	};
	
	this.searchByAddress = function(str)
	{
		//str = jsUtils.trim(str);
		str = str.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');
		if (str.length > 1)
		{
			if (null == this.geocoder)
				this.geocoder = new google.maps.Geocoder();
		
			this.geocoder.geocode({
				address: str, 
				language: BX.message('LANGUAGE_ID')
			}, this.__searchResultsLoad);
		}
	}
}

JCBXGoogleSearch.prototype.__pointClick = function()
{
	if (null != window['__bx_google_infowin_opened_search'])
		window['__bx_google_infowin_opened_search'].close();

	this.infowin.open(this.map, this);
	
	window['__bx_google_infowin_opened_search'] = this.infowin;
}

JCBXGoogleSearch.prototype.handleError = function(error)
{
	alert(this.jsMess.mess_error + (error ? ': ' + error.message : ''));
}

JCBXGoogleSearch.prototype.clearSearchResults = function()
{
	for (var i = 0; i < this.arSearchResults.length; i++)
	{
		this.arSearchResults[i].infowin.close();
		this.arSearchResults[i].setMap(null);
		delete this.arSearchResults[i];
	}

	this.arSearchResults = [];
}
