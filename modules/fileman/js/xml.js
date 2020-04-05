function BXXML()
{
	var pObj = this;
	var pXML;

//	try
	{
		if(window.XMLHttpRequest)
			pXML = new XMLHttpRequest();
		else
		{
			try{pXML = new ActiveXObject("Msxml2.XMLHTTP");}catch(e){}
			if(!pXML)
				pXML = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
//	catch (e){};

	if(!pXML)
	{
		alert('XMLHttp object is not found.');
		return false;
	}
	this.pXML = pXML;
	return true;
}

BXXML.prototype.Load = function(sUrl, arParams, pAsyncFunction)
{
	this.DOMDocument = false;
	var pObj = this;
	var pXML = this.pXML;
	if(pAsyncFunction)
	{
		pXML.open("POST", sUrl, true);
		pXML.onreadystatechange = function()
		{
			if(pXML.readyState == 4)
			{
				//alert(pXML.responseText);
				pObj.DOMDocument = pXML.responseXML ;
				//alert(pXML.responseXML.XML);
				pAsyncFunction(pObj);
			}
		}
		pXML.send(null);
		return true;
	}

	try
	{
		pXML.open("POST", sUrl, false);
		pXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

		if(arParams)
			pXML.send(BXPHPVal(arParams));
		else
			pXML.send(null);
	}
	catch (e)
	{
		return false;
	}

	//alert(pXML+'/'+pXML.status+'/'+pXML.readyState+'/'+pXML.responseXML);
	if((pXML.status == 200 || (pXML.status == 0 && pXML.readyState==4)) && pXML.responseXML)
	{
		this.DOMDocument = pXML.responseXML;
		return true;
	}

	return false;
}

BXXML.prototype.Unserialize = function()
{
	var arRes = false;
	if(this.DOMDocument)
	{
		var oRootNodes = this.selectNodes("/params/variable");
		var oElement;
		for(var i=0; i<oRootNodes.length; i++)
		{
			oElement = oRootNodes[i];
			eval('arRes = '+oElement.getAttribute("value"));
			break;
		}
	}
	return arRes;
}

BXXML.prototype.selectNodes = function(xPath, oNode)
{
	if(this.DOMDocument.createNSResolver)
	{
		//http://kb.mozillazine.org/XMLHttpRequest
		var oNodeTemp = false;
		var arNodes = [];
		var result = this.DOMDocument.evaluate(
			xPath,
			(oNode?oNode:this.DOMDocument),
			this.DOMDocument.createNSResolver(this.DOMDocument.documentElement),
			0,
			null
			);

		if(result)
		{
	 		while((oNodeTemp = result.iterateNext()))
	 			arNodes.push(oNodeTemp);
		}

		return arNodes;
	}

	return (oNode?oNode:this.DOMDocument).selectNodes(xPath);
}

/*
FCKXml.prototype.SelectNodes = function(xpath, contextNode)
{

	var xPathResult = this.DOMDocument.evaluate( xpath, contextNode ? contextNode : this.DOMDocument, this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
	if(xPathResult)
	{
		var oNode = xPathResult.iterateNext() ;
 		while(oNode)
 		{
 			aNodeArray[aNodeArray.length] = oNode;
 			oNode = xPathResult.iterateNext();
 		}
	}
	return aNodeArray ;
}

FCKXml.prototype.SelectSingleNode = function(xpath, contextNode)
{
	var xPathResult = this.DOMDocument.evaluate( xpath, contextNode ? contextNode : this.DOMDocument, this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), 9, null);
	if(xPathResult && xPathResult.singleNodeValue)
		return xPathResult.singleNodeValue;
	else
		return null;
}

BXXML.prototype.SelectSingleNode = function( xpath, contextNode)
{
	if (contextNode)
		return contextNode.selectSingleNode( xpath ) ;
	else
		return this.DOMDocument.selectSingleNode( xpath ) ;
}
*/
