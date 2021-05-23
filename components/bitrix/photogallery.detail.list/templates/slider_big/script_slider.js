function GetImageWindowSize()
{
	var windowSize = jsUtils.GetWindowSize();
	return {'width' : parseInt(windowSize.innerWidth - 20),
			'height' : parseInt(windowSize.innerHeight - 20 - 110),
			'top' : parseInt(windowSize.scrollTop) + 10};
}

var SlideSlider = false;
var player = false;

function __show_slider(active, url, result)
{
	active = parseInt(active);
	var res = 0;
	if (active > 0)
	{
		for (var ii = 0; ii < result['elements'].length; ii++)
		{
			if (result['elements'][ii]['id'] == active)
			{
				res = ii;
				break;
			}
		}
	}
	res = ((res > 0 ? res : 0) + parseInt(result['start_number']));

	var speed_mixer = new BPCMixer(
		BX('bx_slider_mixers_border'),
		BX('bx_slider_mixers_cursor'),
		5,
		{
			events : {
				BeforeSetCursor : function()
				{
					arguments = arguments[0];
					setTimeout(new Function("BX('bx_slider_speed').innerHTML = '" + arguments[1] + "'"), 10);
					window.__photo_params['speed'] = arguments[1];
					if (window.player && window.player.params['period'])
						window.player.params['period'] = (window.__photo_params['speed'] + (window.__photo_params['effects'] ? 1.5 : 0));
					if (window['jsUserOptions'])
					{
						if(!jsUserOptions.options)
							jsUserOptions.options = new Object();
						jsUserOptions.options['photogallery.slide.speed'] = ['photogallery', 'slide', 'speed', arguments[1], false];
						jsUserOptions.SendData(null);
					}
				}
			}
		});
	BX('bx_slider_mixers_plus').onclick = function() {
		window.__photo_params['speed']++;
		speed_mixer.SetCursor(window.__photo_params['speed']);}
	BX('bx_slider_mixers_minus').onclick = function() {
		window.__photo_params['speed']--;
		speed_mixer.SetCursor(window.__photo_params['speed']);}

	if (!SlideSlider)
	{
		SlideSlider = new BPCSlider(
			result['elements'],
			res,
			result['elements_count'],
			result['start_number'],
			'');
		if (url.length > 0)
		{
			SlideSlider.oSource.url = url;
		}
		SlideSlider.params = {'diff_size' : false};
		/**
			CreateItem - create picture
		*/
		SlideSlider.CreateItem = function(item_id)
		{
			var koeff = Math.min(
				this.item_params['width'] / this.oSource.Data[item_id]['width'],
				this.item_params['height'] / this.oSource.Data[item_id]['height']);
			var res = {
				'width' : this.oSource.Data[item_id]['width'],
				'height' : this.oSource.Data[item_id]['height']};
			if (koeff < 1)
			{
				res['width'] = parseInt(res['width'] * koeff);
				res['height'] = parseInt(res['height'] * koeff);
			}

			var image = new Image();
			__this_slider = this;
			image.id = 'image_' + item_id;
			image.onload = function(){
//				try
				{
					var iNumber = parseInt(this.id.replace('image_', ''));
					__this_slider.oSource.Data[iNumber]['loaded'] = true;
					__this_slider.OnAfterItemLoad(this);
				}
//				catch (e) {}
			}

			image.style.width = res['width'] + 'px';
			image.style.height = res['height'] + 'px';
			image.style.visibility = 'hidden';
			SlideSlider.oImageOuterBox.title = image.title = image.alt = BX.util.htmlspecialcharsback(this.oSource.Data[item_id]['title']);

			image.src = this.oSource.Data[item_id]['src'];
			return image;
		}

		SlideSlider.OnAfterItemLoad = function(image)
		{
			var iWidthDiff = parseInt(image.style.width.replace('px', '')) + this.params['diff_size']['width'];
			var iHeightDiff = (parseInt(image.style.height.replace('px' , '')) + this.params['diff_size']['height']);
			var item_id = image.id.replace('image_', '');
			var div = document.createElement('div');
			div.className = "bx-slider-image-container";
			div.id = item_id;
			var styles = {
				'overflow' : 'hidden',
				'width' : (iWidthDiff < oPhotoObjects['min_slider_width'] ?
					((oPhotoObjects['min_slider_width'] - this.params['diff_size']['width'] ) + 'px') :
					image.style.width),
				'height' : (iHeightDiff < oPhotoObjects['min_slider_height'] ?
					((oPhotoObjects['min_slider_height'] - this.params['diff_size']['height']) + 'px') :
					image.style.height)};
			for (var ii in styles)
				div.style[ii] = styles[ii];

			if (iWidthDiff < oPhotoObjects['min_slider_width'] || iHeightDiff < oPhotoObjects['min_slider_height'])
			{
				var div_inner = div.appendChild(document.createElement('div'));
				div_inner.style.visibility = 'hidden';
				image.style.visibility = 'visible';
				if (iWidthDiff < oPhotoObjects['min_slider_width'])
				{
					var tmp = oPhotoObjects['min_slider_width'] - iWidthDiff;
					div_inner.style.paddingRight = div_inner.style.paddingLeft = Math.ceil(tmp / 2) + 'px';
					iWidthDiff = oPhotoObjects['min_slider_width'];
				}
				if (iHeightDiff < oPhotoObjects['min_slider_height'])
				{
					var tmp = oPhotoObjects['min_slider_height'] - iHeightDiff;
					div_inner.style.paddingBotton = div_inner.style.paddingTop = Math.ceil(tmp / 2) + 'px';;
					iHeightDiff = oPhotoObjects['min_slider_height'];
				}
				div_inner.appendChild(image);
			}
			else
			{
				div.appendChild(image);
			}

			this.oImageBox.appendChild(div);

			try {
				var res = this.oImageBox.lastChild.previousSibling;
				while (res)
				{
					this.oImageBox.removeChild(res);
					res = this.oImageBox.lastChild.previousSibling;
				}
			} catch(e) {}
			BX('bx_slider_container_header').style.visibility = 'visible';
			window.location.hash = 'photo' + this.oSource.Data[item_id]['id'];
			if (this.params['time']['resize'] > 0)
			{
				var params = jsUtilsPhoto.GetElementParams(this.oImageOuterBox);
				var wDiff = params['width'] - iWidthDiff;
				var hDiff = params['height'] - iHeightDiff;

				if (wDiff != 0 || hDiff != 0)
				{
					new jsUtilsEffect.Scale(
						this.oImageOuterBox,
						false,
						{
							scaleXTo: (wDiff != 0 ? (iWidthDiff / params['width']) : 1.0),
							scaleYTo: (hDiff != 0 ? (iHeightDiff / params['height']) : 1.0),
							events: {
								BeforeSetDimensions: function(obj, args)
								{
									if (args[1]['height'])
									{
										var div = this.element.parentNode;
										if (!this.originParams['int_parent_top'])
										{
											this.originParams['int_parent_top'] = parseInt(div.style.top);
											this.originParams['int_height'] = parseInt(this.originParams['height']);
										}
										div.style.top = (this.originParams['int_parent_top'] +
											parseInt((this.originParams['int_height'] - parseInt(args[1]['height'])) / 2)) + 'px';
									}
								}
							},
							duration: this.params['time']['resize']}
						);
				}
				__this_slider = this;
				setTimeout(new Function("__this_slider.ShowItemDetails(" + div.id + ");"), this.params['time']['resize'] * 1000);
			}
			else
			{
				this.oImageOuterBox.style.width = iWidthDiff + 'px';
				this.oImageOuterBox.style.height = iHeightDiff + 'px';
				this.oImageOuterBox.parentNode.style.top = (this.params['size']['y'] - parseInt(iHeightDiff / 2)) + 'px';
				this.ShowItemDetails(div.id);
			}

			this.oImageDataBox.style.width = iWidthDiff + 'px';
			this.PreloadItems(div.id);
			return true;
		}

		SlideSlider.ShowItemDetails = function(item_id)
		{
			if (!this.oImageBox.firstChild || !this.oImageBox.firstChild.firstChild)
				return false;
			this.oImageBox.style.visibility = 'visible';
			this.oLoadBox.style.visibility = 'hidden';
			var template = window.__photo_params['template']
			var template_additional = window.__photo_params['template_additional'];

			var template_vars = {
				title : /\#title\#/gi,
				rating : /\#rating\#/gi,
				shows : /\#shows\#/gi,
				comments : /\#comments\#/gi,
				description : /\#description\#/gi,
				url: /\#url\#/gi};

			for (var key in template_vars)
			{
				var replacement = (this.oSource.Data[item_id][key] ? this.oSource.Data[item_id][key] : '');
				replacement = ((replacement + '') == "0" ? '' : replacement);
				template = template.replace(template_vars[key], replacement);
				template_additional = template_additional.replace(template_vars[key], replacement);
			}


			var tt = this.oImageDataBox.getElementsByTagName('div');
			var bFounded = false;
			for (var ii = 0; ii < tt.length; ii++)
			{
				if (tt[ii].id == 'bx_caption')
				{
					tt[ii].innerHTML = template;
					if (bFounded) { break ;}
					bFounded = true;
				}
				if (tt[ii].id == 'bx_caption_additional')
				{
					tt[ii].innerHTML = template_additional;
					if (bFounded) { break ;}
					bFounded = true;
				}
			}


			if (BX('element_number'))
				BX('element_number').innerHTML = item_id;
			if (this.params['time']['data'] <= 0)
			{
				this.oImageBox.firstChild.firstChild.style.visibility = 'visible';
				this.oImageDataBox.style.display = 'block';
			}
			else
			{
				new jsUtilsEffect.Transparency(this.oImageBox.firstChild.firstChild, {'duration' : this.params['time']['data'] * 0.3});
				new jsUtilsEffect.Untwist(this.oImageDataBox, {'duration' : this.params['time']['data']});
			}

			if (BX('bx_slider_datacontainer').style.display == 'none')
				BX('bx_slider_datacontainer').style.display = 'block';
			this.oNavNext.style.height = this.oNavPrev.style.height = this.oImageOuterBox.style.height;

			var __this = this;
			setTimeout(function() {
				__this.status = 'ready';
				__this.oNavNext.style.display = __this.oNavPrev.style.display = 'block';
			}, (this.params['time']['data'] > 0 ? this.params['time']['data'] * 1000 : 100));
		}

		SlideSlider.ShowItem = function(item_id, number)
		{
			if (this.status == 'inprogress')
				return false;
			this.status = 'inprogress';
			// hide image info
			this.oImageBox.style.visibility = 'hidden';
			this.oNavPrev.style.display = this.oNavNext.style.display = 'none';
			if (this.params['time']['resize'] > 0)
			{
				this.oLoadBox.style.visibility = 'visible';
//				try	{
					var oChildNodes = this.oImageBox.childNodes;
					if (oChildNodes && oChildNodes.length > 0)
					{
						for (var jj = 0; jj < oChildNodes.length; jj++)
							this.oImageBox.removeChild(oChildNodes[jj]);
					}
//				} catch(e) {}
			}
			if (this.params['time']['data'] > 0)
			{
				this.oImageDataBox.style.display = 'none';
			}

			this.CreateItem(item_id);

			return true;
		}
	}

	SlideSlider.active = res;
	SlideSlider.oLoadBox = BX('bx_slider_content_loading');
	SlideSlider.oImageOuterBox = BX('bx_slider_container_outer');
	SlideSlider.oImageBox = BX('bx_slider_content_item');
	SlideSlider.oImageDataBox = BX('bx_slider_datacontainer_outer');
	SlideSlider.oNavPrev = BX('bx_slider_nav_prev');
	SlideSlider.oNavNext = BX('bx_slider_nav_next');

	SlideSlider.params['diff_size'] = {
		'width' : (SlideSlider.oImageOuterBox.offsetWidth - SlideSlider.oImageBox.offsetWidth),
		'height' : 37};
	SlideSlider.params['size'] = {
		'x' : 'center',
		'y' : (parseInt(SlideSlider.oImageOuterBox.parentNode.style.top) + parseInt(SlideSlider.oImageOuterBox.offsetHeight / 2))};

	var ImageRectangle = GetImageWindowSize();
	SlideSlider.params['time'] = {
		'resize' : (window.__photo_params['effects'] ? 0.5 : 0),
		'data' : (window.__photo_params['effects'] ? 0.8 : 0)};
	SlideSlider.item_params = {
		'width' : (ImageRectangle['width'] - SlideSlider.params['diff_size']['width']),
		'height' : (ImageRectangle['height'] - SlideSlider.params['diff_size']['height'])};
	SlideSlider.item_params['width'] = (SlideSlider.item_params['width'] < oPhotoObjects['min_slider_width'] ? oPhotoObjects['min_slider_width'] : SlideSlider.item_params['width']);
	SlideSlider.item_params['height'] = (SlideSlider.item_params['height'] < oPhotoObjects['min_slider_height'] ? oPhotoObjects['min_slider_height'] : SlideSlider.item_params['height']);

	SlideSlider.ShowSlider();

	BX('element_count').innerHTML = result['elements_count'];

	BPCPlayer.prototype.OnStopPlay = function()
	{
		if (BX('bx_slider_nav_pause'))
			BX('bx_slider_nav_pause').id = 'bx_slider_nav_play';
	}
	BPCPlayer.prototype.OnStartPlay = function()
	{
		if (BX('bx_slider_nav_play'))
			BX('bx_slider_nav_play').id = 'bx_slider_nav_pause';
	}

	window.player = new BPCPlayer(SlideSlider);

	if (player)
	{
		player.params = {
			'period' : (window.__photo_params['speed'] + (window.__photo_params['effects'] ? 1.5 : 0)),
			'status' : 'paused'};
		window.__checkKeyPress = function(e)
		{
			if (SlideSlider && SlideSlider.status != 'inprogress')
			{
				__this_player = player;
				player.checkKeyPress(e);
			}
		}
		jsUtils.addEvent(document, "keypress", __checkKeyPress);
	}
	else
	{
		BX("bx_slider_content_item").innerHTML = '<div class="error">Error. <a href="' +
			window.location.href + '">Refresh</a>. </div>';
	}
}

bPhotoSliderLoad = true;