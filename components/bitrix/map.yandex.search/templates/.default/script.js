var JCBXYandexSearch = function(map_id, obOut, jsMess)
{
	this.map_id = map_id;
	this.map = GLOBAL_arMapObjects[this.map_id];

	this.obOut = obOut;
	
	if (null == this.map)
		return false;

	this.arSearchResults = [];
	this.jsMess = jsMess;
};

JCBXYandexSearch.prototype.__searchResultsLoad = function(res)
{
	if (null == this.obOut)
		return;

	this.obOut.innerHTML = '';
	this.clearSearchResults();

	var obList = null,
		len = res.geoObjects.getLength();
	if (len > 0)
	{
		obList = document.createElement('UL');
		obList.className = 'bx-yandex-search-results';
		var str = '';
		str += this.jsMess.mess_search + ': <b>' + len + '</b> ' + this.jsMess.mess_found + '.';

		// i rly dont khow why this doesnt work in one loop ;-(
		for (var i = 0; i < len; i++)
		{
			this.arSearchResults.push(res.geoObjects.get(i));
		}

		for (i = 0; i < this.arSearchResults.length; i++)
		{
			this.map.geoObjects.add(this.arSearchResults[i]);
			var obListElement = obList.appendChild(BX.create('LI', {
				children: [
					BX.create('A', {
						attrs: {
							href: "javascript:void(0)"
						},
						events: {
							click: BX.proxy(this.__showSearchResult, this.arSearchResults[i])
						},
						text: this.arSearchResults[i].properties.get('metaDataProperty').GeocoderMetaData.text
					})
				]
			}));
		}

	}
	else
	{
		var str = this.jsMess.mess_search_empty;
	}

	this.obOut.innerHTML = str;

	if (null != obList)
		this.obOut.appendChild(obList);
};

// called in the context of ymaps.Placement.
JCBXYandexSearch.prototype.__showSearchResult = function()
{
	this.balloon.open();
	this.getMap().panTo(this.geometry.getCoordinates());
};

JCBXYandexSearch.prototype.searchByAddress = function(str)
{
	str = str.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');

	if (str == '')
		return;

	ymaps.geocode(str).then(
		BX.proxy(this.__searchResultsLoad, this),
		this.handleError
	);
}

JCBXYandexSearch.prototype.handleError = function(error)
{
	alert(this.jsMess.mess_error + ': ' + error.message);
}

JCBXYandexSearch.prototype.clearSearchResults = function()
{
	for (var i = 0; i < this.arSearchResults.length; i++)
	{
		this.arSearchResults[i].balloon.close();
		this.map.geoObjects.remove(this.arSearchResults[i]);
		delete this.arSearchResults[i];
	}

	this.arSearchResults = [];
}
