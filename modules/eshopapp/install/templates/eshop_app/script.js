/*function deleteFlyImage(id)
{
	BX.remove(BX(id));
}
function flyImageDetail(img, flyMode)
{
	var element = img;
	var flyImage = element.cloneNode(true);
	flyImage.id = "emp"+Math.floor(Math.random() * (100));
	BX.findParent(element, {tag:"a"}).insertBefore(flyImage, element);
	flyImage.style.position = "absolute";
	flyImage.style.zIndex = "3000";
	BX.addClass(flyImage, "growing");
	if (flyMode == "flyFromLeft")
		flyImage.style.left = document.documentElement.clientWidth - flyImage.getBoundingClientRect().left+'px';
	else
		flyImage.style.left = document.documentElement.clientWidth+'px';
	flyImage.style.top = document.body.scrollTop-50+'px';
	flyImage.width='0';
	flyImage.height='0';

	setTimeout(function(){deleteFlyImage(flyImage.id);}, 2000);
}*/

function addItemToCart(element)
{
	app.onCustomEvent('onItemBuy', {});
	//flyImageDetail(img, flyMode);

	BX.ajax({
		timeout:   30,
		method:   'GET',
		url:       element.href,
		processData: false,
		onsuccess: function(reply){
		},
		onfailure: function(){
		}
	});
	return false;
}

function OpenClose(element)
{
	if (BX.hasClass(element, 'close'))
	{
		BX.addClass(element, 'open');
		BX.removeClass(element, 'close');
	}
	else
	{
		BX.addClass(element, 'close');
		BX.removeClass(element, 'open');
	}
}