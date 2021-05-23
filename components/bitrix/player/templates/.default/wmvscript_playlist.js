function showWMVPlayer(contID, config, playlistConfig)
{	
	playlistConfig = playlistConfig || {};
	playlistConfig.format = playlistConfig.format || 'xspf';
	playlistConfig.size = playlistConfig.size || 180;
	playlistConfig.path = playlistConfig.path || '';
	playlistConfig.position = playlistConfig.position || 'right';
	var oPlaylist = new BXSilverlightPlaylist(contID, config, playlistConfig);
	oPlaylist.Init();
}

function BXSilverlightPlaylist(contID, config, playlistConfig)
{	
	this.currentTrack = 0;
	this.repeat = config.repeat || false;	
	this.playerConfig = config;
	this.playerCont = document.getElementById(contID);
	this.contId = contID;
	this.path = playlistConfig.path;
	this.format = playlistConfig.format;
	this.position = playlistConfig.position;
	this.size = parseInt(playlistConfig.size);
	this.image_height = parseInt(playlistConfig.image_height) || 48;
	this.image_width = parseInt(playlistConfig.image_width) || 64;
	this.width = config.width;
	this.height = config.height;
	this.uniqueId = Math.round(Math.random() * 10000);

	if (this.position == 'right')
		config.width -= this.size;
	else
		config.height -= this.size;
	this.Items = [];
}

BXSilverlightPlaylist.prototype.Init = function()
{
	this.playerCont.innerHTML = JSMESS.PlayListError;
	this.importXML();
};


BXSilverlightPlaylist.prototype.BuildContainer = function()
{
	var contTable = document.createElement("TABLE");
	this.playerCont.innerHTML = '';
	this.playerCont.parentNode.insertBefore(contTable, this.playerCont);
	contTable.cellPadding = 0; contTable.cellSpacing = 2;
	contTable.style.width = this.width + "px";
	contTable.style.height = this.height + "px";
	var r, c;
	r = contTable.insertRow(-1);
	if (this.position == 'right')
	{
		c = r.insertCell(-1);
		c.style.height = this.height + "px";
		c.style.width = this.playerConfig.width + "px";
		c.appendChild(this.playerCont);
		c = r.insertCell(-1);
		c.style.width = this.size + "px";
		this.pDiv = c.appendChild(document.createElement("DIV"));
		this.pDiv.style.height = this.height + "px";
		this.pDiv.className = 'bxwmvplaylist';
	}
	else
	{
		c = r.insertCell(-1);
		c.style.height = this.playerConfig.height + "px";
		c.style.width = this.width + "px";
		c.appendChild(this.playerCont);
		r = contTable.insertRow(-1);
		c = r.insertCell(-1);
		c.style.height = this.size + "px";
		this.pDiv = c.appendChild(document.createElement("DIV"));
		this.pDiv.style.height = this.size + "px";
		this.pDiv.style.width = "100%";
		this.pDiv.className = 'bxwmvplaylist';
	}
};

BXSilverlightPlaylist.prototype.importXML = function()
{
	try{
	var xmlHttp;
	if (window.XMLHttpRequest)
		xmlHttp = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
 	if (!xmlHttp)
		return;
	var _this = this;
	xmlHttp.onreadystatechange =  function()
	{
		if(xmlHttp.readyState == 4)
		{
			if(xmlHttp.status == 200)
			{
				if (!xmlHttp.responseXML)
					return;
				_this.xmlDoc = xmlHttp.responseXML.documentElement;
				_this.OnLoadXML();
			}
		}
	};
	xmlHttp.open("GET", this.path, true);
	xmlHttp.send(null);
	}catch(e){}
};

BXSilverlightPlaylist.prototype.OnLoadXML = function()
{
	if (!this.xmlDoc)
		return;
	this.BuildContainer(); // build playlist table...
	switch(this.format.toLowerCase())
	{
		case 'asx':
			this.FetchTags_ASX();
			break;
		case 'atom':
			this.FetchTags_ATOM();
			break;
		case 'rss':
			this.FetchTags_RSS();
			break;
		case 'xspf':
			this.FetchTags_XSPF();
			break;
	}
	this.Play(0, this.playerConfig.autostart);
	this.ShowTags();
};

BXSilverlightPlaylist.prototype.FetchTags_ASX = function()
{
	var tagList = this.xmlDoc.getElementsByTagName("entry");
	for (var i = 0, l = tagList.length; i < l; i++)
	{
		this.Items.push({
			file : this.getAttribute(tagList[i], "ref", "href"),
			title : this.getAttribute(tagList[i], "title"),
			author : this.getAttribute(tagList[i], "author"),
			link : this.getAttribute(tagList[i], "moreinfo", "href"),
			image : this.getValueByAttrVal(tagList[i], "param", "name", "image", "value"),
			duration : ''
		});
	}
};


BXSilverlightPlaylist.prototype.FetchTags_ATOM = function()
{
	var tagList = this.xmlDoc.getElementsByTagName("entry");
	for (var i = 0, l = tagList.length; i < l; i++)
	{
		this.Items.push({
			file : this.getTagByAttrVal(tagList[i], "link", "rel", "enclosure"),
			title : this.getTagValue(tagList[i], "title"),
			author : this.getTagValue(tagList[i], "name"),
			link : this.getTagValue(tagList[i], "description"),
			image : this.getTagByAttrVal(tagList[i], "link", "rel", "image"),
			duration : ''
		});
	}
};

BXSilverlightPlaylist.prototype.FetchTags_RSS = function()
{
	var tagList = this.xmlDoc.getElementsByTagName("item");
	for (var i = 0, l = tagList.length; i < l; i++)
	{
		this.Items.push({
			file : this.getAttribute(tagList[i], "media:content", "url"),
			title : this.getTagValue(tagList[i], "title"),
			author : this.getTagByAttrVal(tagList[i], "media:credit", "role", "author"),
			link : this.getTagValue(tagList[i], "link"),
			image : this.getAttribute(tagList[i], "media:thumbnail", "url"),
			duration : ''
		});
	}
};

BXSilverlightPlaylist.prototype.FetchTags_XSPF = function()
{
	var tagList = this.xmlDoc.getElementsByTagName("track");
	for (var i = 0, l = tagList.length; i < l; i++)
	{
		this.Items.push({
			file : this.getTagValue(tagList[i], "location"),
			title : this.getTagValue(tagList[i], "title"),
			author : this.getTagValue(tagList[i], "creator"),
			link : this.getTagValue(tagList[i], "link"),
			image : this.getTagValue(tagList[i], "image"),
			duration : this.getTagValue(tagList[i], "duration")
		});
	}
};

BXSilverlightPlaylist.prototype.getAttribute = function(oTag, tag, attr)
{
	try{
	var nodes = this.getNodes(oTag, tag);
	for (var i = 0, l = nodes.length; i < l; i++)
	{
		if (!attr)
			return nodes[i].firstChild.nodeValue;
		return nodes[i].getAttribute(attr);
	}
	}catch(e){}
	return '';
};

BXSilverlightPlaylist.prototype.getTagValue = function(oTag, tag)
{
	try{
	var nodes = this.getNodes(oTag, tag);
	return bxhtmlspecialchars(nodes[0].firstChild.nodeValue);
	}catch(e){}
	return '';
};

BXSilverlightPlaylist.prototype.getNodes = function(oTag, tag)
{
	if ((tag.indexOf(":") > -1) && (!window.ActiveXObject))
		tag = tag.substring(tag.indexOf(":") + 1, tag.length);
	return oTag.getElementsByTagName(tag);
};

BXSilverlightPlaylist.prototype.getValueByAttrVal = function(oTag, tag, attr, attrval, att)
{
	try{
	var nodes = this.getNodes(oTag, tag);
	for (var j = 0; j < nodes.length; j++)
		if (nodes[j].getAttribute(attr) == attrval)
			return nodes[j].getAttribute(att);
	}catch(e){}
	return '';
};


BXSilverlightPlaylist.prototype.getTagByAttrVal = function(oTag, tag, attr, attrval)
{
	try{
	var nodes = this.getNodes(oTag, tag);
	for (var j = 0; j < nodes.length; j++)
		if (nodes[j].getAttribute(attr) == attrval)
			return nodes[j].firstChild.data;
	}catch(e){}
	return '';
};

BXSilverlightPlaylist.prototype.ShowTags = function()
{
	var _this = this;
	window['bx_play_' + this.uniqueId] = function(i) {_this.Play(i, true);};
	window.bx_img_onload = function(img){img.style.display = 'block';};
	window.bx_img_onerror = function(img)
	{
		setTimeout(function()
		{
			try{
				var td = img.parentNode;
				var tr = td.parentNode;
				tr.removeChild(td);
				tr.cells[0].colSpan = 2;
			}catch(e){}
		}, 10)
	};
	var html = '<table cellPadding="1" width="100%">';
	for (var i = 0, l = this.Items.length; i < l; i++)
	{
		html += '<tr><td><table id="bx_playlist_item_' + i + '" width="100%" onclick="window.bx_play_' + this.uniqueId + '(' + i + ')"';
 		html += 'class="playlistlo" onmouseover="this.className = \'playlistitem playlistitemover\';" onmouseout="this.className = \'playlistitem\';"';
 		html += 'title="' + JSMESS.ClickToPLay + '">';
		html += '<tr><td valign="top" width="' + this.image_width + '">';
		html += '<img style="display: none;" width="' + this.image_width + '" height="' + this.image_height + '" border="1" src="' + this.Items[i].image + '" onerror="bx_img_onerror(this);" onload="bx_img_onload(this);"/></td>';
		html += '<td valign="top"><p>';
		if (this.Items[i].title.length > 0)
			html += '<b>' + this.Items[i].title + '</b><br>';
		if (this.Items[i].author.length > 0)
			html += this.Items[i].author + '<br>';
		if (this.Items[i].duration.length > 0)
			html += ' <i>' + this.Items[i].duration + '</i>';
		if (this.Items[i].link.length > 0)
			html += '<a href="' + this.Items[i].link + '" target="_blank">' + JSMESS.Link + '</a>';
		html += '</p></td></tr></table></td></tr>';
	}
	html += '</table>';
	this.pDiv.innerHTML = html;
};

BXSilverlightPlaylist.prototype.Play = function(ind, start)
{
	if (!this.Items[ind])
		return;
	var _this  = this;
	this.currentTrack = ind;
	this.playerConfig.file = this.Items[ind].file;
	this.playerConfig.image = this.Items[ind].image;
	this.playerConfig.link = this.Items[ind].link;
	this.playerConfig.autostart = start || false;
	
	if (!this.player)
	{		
		this.player = new jeroenwijering.Player(this.playerCont, '/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml',  this.playerConfig);
			
		// auto play next
		setTimeout(  
			function()
			{
				_this.player.addListener('STATE', 
					function(ost,nst) 
					{
						if(_this.Items.length > _this.currentTrack+1)
							var nextInd = _this.currentTrack+1;
						else if(_this.repeat) //auto repeat
							var nextInd = 0;
						else 
							return;
						
						if(nst=="Completed") 													
							BX('bx_playlist_item_' + nextInd).click(); 
					}
				);	
			}, 1500
		);

		return;
	}	

	this.player.sendEvent('LOAD', this.playerConfig.file);	
	setTimeout(function(){_this.player.sendEvent('PLAY');}, 10);
};

function bxhtmlspecialchars(str)
{
	if(typeof(str)!='string')
		return str;
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return str;
}