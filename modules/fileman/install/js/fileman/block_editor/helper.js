function BXBlockEditorHelper()
{

}

BXBlockEditorHelper.prototype.colorHexToRgb = function(color)
{
	if(!color)
	{
		return color;
	}

	var shortFormatRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
	color = color.replace(shortFormatRegex, function(m, r, g, b) {
		return r + r + g + g + b + b;
	});

	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);
	return result ? "rgb(" +
	parseInt(result[1], 16) +
	", " +
	parseInt(result[2], 16) +
	", " +
	parseInt(result[3], 16) +
	")"
		: null;
};
BXBlockEditorHelper.prototype.appendChildNode = function(node, anchorNode, before)
{
	before = before || null;
	if(before)
	{
		anchorNode.parentNode.insertBefore(node, anchorNode);
	}
	else
	{
		var nextSibling = BX.findNextSibling(anchorNode);
		if(nextSibling)
		{
			anchorNode.parentNode.insertBefore(node, nextSibling);
		}
		else
		{
			anchorNode.parentNode.appendChild(node);
		}
	}
};
BXBlockEditorHelper.prototype.colorRgbToHex = function(color)
{
	if(!color || color.substring(0, 1) === '#')
	{
		return color;
	}

	var parsedDigits = /(.*?)rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/.exec(color);
	if(!parsedDigits)
		return null;

	var r = parseInt(parsedDigits[2]);
	var g = parseInt(parsedDigits[3]);
	var b = parseInt(parsedDigits[4]);

	var rgb = b | (g << 8) | (r << 16);
	var rgbString = '000000' + rgb.toString(16);
	rgbString = rgbString.substring(rgbString.length,rgbString.length-6);
	return parsedDigits[1] + '#' + rgbString;
};
BXBlockEditorHelper.prototype.column = function(node, param, value)
{
	var nodeCount = 1;
	var inner = BX.findParent(node, {'className': 'bxBlockInn'}, true);
	if(inner)
	{
		for(var i in inner.childNodes)
		{
			if(inner.childNodes[i] && inner.childNodes[i].nodeName == '#text')
			{
				BX.remove(inner.childNodes[i]);
			}
		}

		if(typeof value !== "undefined")
		{
			var newNode;
			var diffCount = inner.childNodes.length - value;

			if(diffCount > 0)
			{
				// remove last blocks
				while(diffCount > 0)
				{
					BX.remove(inner.childNodes[inner.childNodes.length-1]);
					diffCount--;
				}
			}
			else if(diffCount < 0)
			{
				// clone new blocks
				// remove last blocks
				while(diffCount < 0)
				{
					newNode = BX.clone(inner.childNodes[0]);
					inner.appendChild(newNode);

					diffCount++;
				}
			}

			var width = null;
			if(inner.childNodes.length == 3)
			{
				width = '188';
			}
			else if(inner.childNodes.length == 2)
			{
				width = '282';
			}
			for(var i in inner.childNodes)
			{
				if(inner.childNodes[i] && inner.childNodes[i].setAttribute)
				{
					inner.childNodes[i].setAttribute('width', width);
				}
			}
		}

		nodeCount = inner.childNodes.length;
	}

	return nodeCount.toString();
};
BXBlockEditorHelper.prototype.color = function(node, color, value)
{
	if(typeof(value) !== "undefined")
	{
		var valueHex = value;
		if(value.length > 0)
		{
			value = this.colorHexToRgb(value);
		}

		this.style(node, color, value);
		if(color == 'background-color' && node.tagName == 'TABLE')
		{
			this.attr(node, 'bgcolor', valueHex);
		}
	}

	value = this.style(node, color);

	return this.colorRgbToHex(value);
};
BXBlockEditorHelper.prototype.style = function(node, style, value)
{
	if(!node)
	{
		return;
	}

	if(typeof(value) !== "undefined")
	{
		if(value.length > 0)
			node.style[style] = value;
		else
			node.style[style] = null;
	}

	return node.style[style];
};
BXBlockEditorHelper.prototype.attr = function(node, attr, value)
{
	if(!node)
	{
		return;
	}

	if(typeof(value) !== "undefined")
	{
		if(value !== null && value.length > 0)
		{
			node.setAttribute(attr, value);
		}
		else
		{
			node.removeAttribute(attr);
		}
	}

	return node.getAttribute(attr);
};
BXBlockEditorHelper.prototype.paddings = function(node, param, value)
{
	if(!node)
	{
		return;
	}

	if(typeof(value) !== "undefined")
	{
		if(value == 'Y')
		{
			BX.addClass(node, 'bxBlockPadding');
		}
		else
		{
			BX.removeClass(node, 'bxBlockPadding');
		}
	}

	if(BX.hasClass(node, 'bxBlockPadding'))
	{
		return 'Y';
	}
	else
	{
		return 'N';
	}
};
BXBlockEditorHelper.prototype.each = function(itemList, callback, context)
{
	if(!BX.type.isFunction(callback))
	{
		return;
	}

	if(!itemList || itemList.length == 0)
	{
		return;
	}

	for(var key in itemList)
	{
		if(typeof(itemList[key]) === "undefined")
		{
			return;
		}

		var item = itemList[key];
		var eachContext = context || item;
		callback.apply(eachContext, [item, key]);
	}
};
BXBlockEditorHelper.prototype.imageAutoWidth = function(node, nearTargetNode)
{
	/*
	nearTargetNode = nearTargetNode || node;
	var nodeContainer = BX.findParent(nearTargetNode, {'attribute': 'data-bx-block-editor-place'}, true);
	var rect = nodeContainer.getBoundingClientRect();
	var containerWidth = rect.right - rect.left;

	var imgWidth = 500;
	if(containerWidth > 500)
		imgWidth = 500;
	else if(containerWidth > 346)
		imgWidth = 346;
	else if(containerWidth > 264)
		imgWidth = 264;
	else
		imgWidth = 176;
	*/

	//node.querySelectorAll('img.bxImage')
	var imgList = BX.findChildren(node, {'tag': 'img'}, true);
	this.each(imgList, function(img){
		img.removeAttribute('width');
	});

	var _this = this;
	setTimeout(function(){
		_this.each(imgList, function(img){
			img.setAttribute('width', img.offsetWidth);
		});
	}, 1000);

};
BXBlockEditorHelper.prototype.imageTextAlign = function(node, value)
{
	var nodeOuter = BX.findChild(node, {'className': 'bxBlockOut'}, true);
	var nodeContList = BX.findChildren(nodeOuter, {'className': 'bxBlockContentItemImageText'}, true);
	var isFirstImg = false;
	if(BX.findChild(nodeContList[0], {'className': 'bxBlockContentImage'}, true))
	{
		isFirstImg = true;
	}

	value = value || null;
	if(value)
	{
		var nodeImage = isFirstImg ? nodeContList[0] : nodeContList[1];
		var nodeText = isFirstImg ? nodeContList[1] : nodeContList[0];

		switch (value){
			case 'left':
				nodeText.parentNode.insertBefore(nodeImage, nodeText);
				break;
			case 'right':
				nodeText.parentNode.appendChild(nodeImage);
				break;
		}
	}

	if(isFirstImg)
		result = 'left';
	else
		result = 'right';

	return result;
};
BXBlockEditorHelper.prototype.imageTextPart = function(node, value)
{
	var nodeInn = BX.findChild(node, {'className': 'bxBlockInn'}, true);
	var nodeContList = BX.findChildren(nodeInn, {'className': 'bxBlockContentItemImageText'}, true);
	var isFirstImg = false;
	if(nodeContList && BX.findChild(nodeContList[0], {'className': 'bxBlockContentImage'}, true))
	{
		isFirstImg = true;
	}

	var nodeImage = isFirstImg ? nodeContList[0] : nodeContList[1];
	var nodeText = isFirstImg ? nodeContList[1] : nodeContList[0];

	value = value || null;

	var defaultValue = '1/2';
	var fullWidth = 580;
	var nodeImageWidthDict = {
		'1/4': parseInt(fullWidth/4),
		'1/3': parseInt(fullWidth/3),
		'1/2': parseInt(fullWidth/2),
		'2/3': parseInt(2*fullWidth/3)
	};
	var imgWidth = nodeImageWidthDict[defaultValue];
	if(value)
	{
		imgWidth = nodeImageWidthDict[value];
		nodeText.setAttribute('width', fullWidth - imgWidth);
		nodeImage.setAttribute('width', imgWidth);
	}

	var result = defaultValue;
	imgWidth = parseInt(nodeImage.getAttribute('width'));
	for(var key in nodeImageWidthDict)
	{
		if(imgWidth == nodeImageWidthDict[key])
		{
			result = key;
			break;
		}
	}

	return result;
};
BXBlockEditorHelper.prototype.imageSrc = function (node, value)
{
	if (typeof value !== "undefined")
	{
		if (!value || value == "")
		{
			value = '/bitrix/images/fileman/block_editor/photo-default.png';
			this.attr(node, 'data-bx-editor-def-image', '1');
		}
		else
		{
			this.attr(node, 'data-bx-editor-def-image', '0');
		}

		this.attr(node, 'src', value);
		//this.imageAutoWidth(node.parentNode);
	}

	if(this.attr(node, 'data-bx-editor-def-image') == '1')
	{
		return "";
	}
	else
	{
		return this.attr(node, 'src');
	}
};
BXBlockEditorHelper.prototype.groupImageSrc = function (node, value)
{
	var result = [];
	var itemList;

	var getImageContentNode = function()
	{
		var imageContainerHtml = '<table align="left" border="0" cellpadding="0" cellspacing="0" width="260">'
			+ '<tbody><tr>'
			+ '<td valign="top" class="bxBlockPadding bxBlockContentImage">'
			+ '<a href="#"><img align="left" data-bx-editor-def-image="1" src="/bitrix/images/fileman/block_editor/photo-default.png" class="bxImage"></a></td>'
			+ '</tr>'
			+ '</tbody></table>';

		// equalize the amount of image containers
		if(!this.imageTempContainer)
		{
			this.imageTempContainer = BX.create('DIV');
		}
		BX.adjust(this.imageTempContainer, {'html': imageContainerHtml});

		return BX.findChild(this.imageTempContainer, {});
	};

	if (typeof value !== "undefined")
	{
		var valueList = value.split(',');
		if(!value || value == "")
		{
			valueList = [];
		}

		var imageContainer;
		itemList = BX.findChildren(node, {});
		var diffLength = valueList.length - itemList.length;
		var diffLengthAbs = Math.abs(diffLength);
		var diffDelete = diffLength < 0;

		if (diffLength != 0)
		{
			for (var i = 0; i < diffLengthAbs; i++)
			{
				if (diffDelete)
				{
					BX.remove(itemList.pop());
				}
				else
				{
					imageContainer = getImageContentNode();
					node.appendChild(imageContainer);
				}
			}
		}

		if(!value || value == "")
		{
			for(var i = 1; i <= 2; i++)
			{
				imageContainer = getImageContentNode();
				node.appendChild(imageContainer);
			}
		}
		else
		{
			// set values
			itemList = BX.findChildren(node, {});
			for (var i in itemList)
			{
				var imgNode = BX.findChild(itemList[i], {'tag': 'img'}, true);
				if(!imgNode)
				{
					continue;
				}
				this.attr(imgNode, 'src', valueList[i]);
				this.attr(imgNode, 'data-bx-editor-def-image', '0');
			}
		}

	}

	itemList = BX.findChildren(node, {'tag': 'img'}, true);
	for (var i in itemList)
	{
		if(this.attr(itemList[i], 'data-bx-editor-def-image') == '1')
		{
			continue;
		}

		result.push(this.attr(itemList[i], 'src'));
	}

	return result;
};

BXBlockEditorHelper.prototype.textContent = function(node, value)
{
	if(!node)
	{
		return;
	}

	if(typeof(value) !== "undefined")
	{
		if(value.length > 0)
		{
			node.textContent = value.trim();
		}
		else
		{
			node.textContent = '';
		}
	}

	return node.textContent.trim();
};

BXBlockEditorHelper.prototype.innerHTML = function(node, value)
{
	if(!node)
	{
		return;
	}

	if(typeof(value) !== "undefined")
	{
		if(value.length > 0)
		{
			node.innerHTML = value.trim();
		}
		else
		{
			node.innerHTML = '';
		}
	}

	return node.innerHTML.trim();
};
BXBlockEditorHelper.prototype.htmlEscape = function(str)
{
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
};