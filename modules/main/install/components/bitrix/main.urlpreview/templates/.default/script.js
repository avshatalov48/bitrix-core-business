var BXUrlPreview = function(element)
{
	this.element = element;
	this.inputElement = null;
	this.id = this.element.id;
	this.carouselElement = null;
	this.carouselImages = [];
	this.currentImageId = null;
	this.init();
};

BXUrlPreview.prototype.init = function()
{
	this.inputElement = this.element.querySelector('.urlpreview__ufvalue');
	this.initCarousel();
	this.bindEventHandlers();

	if (
		BX.type.isDomNode(this.inputElement)
		&& this.inputElement.form
	)
	{
		BX.addCustomEvent(this.inputElement.form, 'onAutoSaveRestoreFinished', function(ob, data) {
			if (
				this.inputElement.form == ob.FORM
				&& BX.type.isNotEmptyString(data[this.inputElement.name])
			)
			{
				this.attachUrlPreview({
					id: data[this.inputElement.name]
				});
			}
		}.bind(this));
	}
};

BXUrlPreview.prototype.detachUrlPreview = function()
{
	if(this.inputElement)
	{
		this.inputElement.value = '';
	}

	this.element.style.display='none';
};

/**
 * @param params. Object with properties:
 * <li>url: URL of the resource
 * <li>id: ID of the preview, already cached in database
 */
BXUrlPreview.prototype.attachUrlPreview = function(params)
{
	var ufIdInput, ufId;

	if(this.element.style.display !== 'none')
	{
		return;
	}

	ufId = this.element.getAttribute('data-field-id');
	requestParams = {
		'action': 'attachUrlPreview',
		'userFieldId': ufId,
		'elementId': this.id,
		'sessid': BX.bitrix_sessid()
	};

	if(params.hasOwnProperty('url'))
		requestParams.url = params.url;
	else if(params.hasOwnProperty('id'))
		requestParams.id = params.id;
	else
		return;

	BX.ajax({
		url: '/bitrix/components/bitrix/main.urlpreview/ajax.php',
		method: 'POST',
		data: requestParams,
		onsuccess: function(data)
		{
			if(data.length > 0)
			{
				var tempDiv = document.createElement('div');
				var oldStyles = this.element.style.cssText;
				tempDiv.innerHTML = data;
				var newElement = tempDiv.firstElementChild;

				this.element.parentNode.replaceChild(newElement, this.element);
				this.element = newElement;
				this.element.style.cssText = oldStyles;
				this.element.style.removeProperty('display');
				this.init();
			}
		}.bind(this)
	});
};

BXUrlPreview.prototype.bindEventHandlers = function()
{
	var _this = this;
	var detachElement = this.element.querySelector('.urlpreview__detach');
	if(detachElement)
	{
		detachElement.addEventListener('click', _this.detachUrlPreview.bind(_this));
	}

	var switchableElement = this.element.querySelector('.urlpreview__container-switchable');
	if(switchableElement)
	{
		switchableElement.addEventListener('click', BXUrlPreview.showEmbed);
	}

	if(this.carouselElement)
	{
		var prevElement = this.carouselElement.querySelector('.urlpreview__button-prev');
		var nextElement = this.carouselElement.querySelector('.urlpreview__button-next');
		if(prevElement)
			prevElement.addEventListener('click', _this.previousImage.bind(_this));
		if(nextElement)
			nextElement.addEventListener('click', _this.nextImage.bind(_this));
	}
};

BXUrlPreview.prototype.initCarousel = function()
{
	var carouselElement;
	var carouselImages;
	var i;
	var imageId;
	if(carouselElement = this.element.querySelector('.urlpreview__carousel'))
	{
		this.carouselElement = carouselElement;
		carouselImages = carouselElement.querySelectorAll('.urlpreview__image');
		for(i = 0; i < carouselImages.length; i++)
		{
			carouselImages[i].dataset.imageId = i;
			this.carouselImages[i] = carouselImages[i];
		}
		imageId = this.element.dataset.imageId ? parseInt(this.element.dataset.imageId) : 0;
		if(this.carouselImages.length > 0)
		{
			this.setCarouselImage(imageId);
			this.carouselElement.style.removeProperty('display');
		}
	}
};

BXUrlPreview.prototype.setCarouselImage = function(imageId)
{
	var imageUrl;
	var imgElement;
	var ufValue;
	if(!(imageId >= 0 && imageId <= this.carouselImages.length-1))
		return null;

	this.carouselImages.map(function(imageElement)
	{
		imageElement.style.display = 'none';
	});
	this.carouselImages[imageId].style.removeProperty('display');
	if(imgElement = this.carouselImages[imageId].querySelector('img'))
	{
		imageUrl = imgElement.getAttribute('src');
		if(this.inputElement)
		{
			ufValue = this.inputElement.value.split(';');
			this.inputElement.value = ufValue[0] + ';' + imageUrl;
		}
	}
	this.currentImageId = imageId;
};

BXUrlPreview.prototype.nextImage = function()
{
	var imageId = this.currentImageId + 1;
	if(imageId > this.carouselImages.length - 1)
		imageId = 0;
	this.setCarouselImage(imageId);
};

BXUrlPreview.prototype.previousImage = function()
{
	var imageId = this.currentImageId - 1;
	if(imageId < 0)
		imageId = this.carouselImages.length - 1;
	this.setCarouselImage(imageId);
};

BXUrlPreview.showEmbed = function()
{
	if(BX.hasClass(this, 'urlpreview__container-hide-embed'))
	{
		BX.addClass(this, 'urlpreview__container-hide-image');
		BX.removeClass(this, 'urlpreview__container-hide-embed');
		var playerNode = BX.findChildByClassName(this, 'video-js');
		if(playerNode)
		{
			if(BX.getClass('BX.Fileman.PlayerManager'))
			{
				var player = BX.Fileman.PlayerManager.getPlayerById(playerNode.getAttribute('id'));
				if(player)
				{
					player.play();
				}
			}
		}
		else
		{
			var iframe = BX.findChildByClassName(this, 'urlpreview-iframe-html-embed');
			if(iframe)
			{
				BXUrlPreview.adjustFrameHeight(iframe, 5);
			}
		}
	}
};

BXUrlPreview.bindEmbedHandler = function()
{
	var switchableElements = document.querySelectorAll('.urlpreview__container-switchable');
	var i;
	for(i = 0; i < switchableElements.length; i++)
	{
		switchableElements.item(i).addEventListener('click', BXUrlPreview.showEmbed);
	}
};

BXUrlPreview.adjustFrameHeight = function(iframe, counter)
{
	if(BX.hasClass(iframe, 'urlpreview-iframe-html-embed-adjusted'))
	{
		return;
	}
	counter = counter || 0;
	if(counter > 10)
	{
		return;
	}
	var addToHeight = 50;
	if(iframe.contentWindow.document.body.scrollHeight > iframe.height)
	{
		iframe.height = iframe.contentWindow.document.body.scrollHeight + addToHeight + "px";
		BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
		return;
	}
	var videos = iframe.contentWindow.document.getElementsByTagName('video');
	if(videos[0])
	{
		iframe.height = iframe.contentWindow.document.body.scrollHeight + addToHeight + "px";
		BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
		return;
	}
	else
	{
		var iframes = iframe.contentWindow.document.getElementsByTagName('iframe');
		var height = 0;
		for(var i = 0; i < iframes.length; i++)
		{
			if(iframes[i] && iframes[i].height > 0)
			{
				height = parseInt(iframes[i].height);
			}
			else if (iframes[i] && iframes[i].style.height)
			{
				height = parseInt(iframes[i].style.height);
			}
			if(height !== 0)
			{
				iframe.height = height + addToHeight + 'px';
				BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
			}
		}
		if(height === 0)
		{
			setTimeout(function()
			{
				counter++;
				BXUrlPreview.adjustFrameHeight(iframe, counter);
			}, 500);
		}
	}
};
