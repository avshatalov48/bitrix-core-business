
function ImgShw(ID, width, height)
{
	var scroll = "no";
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28) scroll = "yes";
	if(height < screen.height-28) top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10) left = Math.floor((screen.width - width)/2-5);
	width = Math.min(width, screen.width-10);	
	height = Math.min(height, screen.height-28);	
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write("<html><head>\n");
	wnd.document.write("<"+"script>\n");
	wnd.document.write("<!--\n");
	wnd.document.write("function KeyPress()\n");
	wnd.document.write("{\n");
	wnd.document.write("	if(window.event.keyCode == 27)\n");
	wnd.document.write("		window.close();\n");
	wnd.document.write("}\n");
	wnd.document.write("//-->\n");
	wnd.document.write("</"+"script>\n");
	wnd.document.write("<title>Image View</title></head>\n");
	wnd.document.write("<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyPress=\"KeyPress()\">\n");
	wnd.document.write("<img src=\""+ID+"\" border=\"0\">");
	wnd.document.write("</body>");
	wnd.document.write("</html>");
	wnd.document.close();
}

function ShowImg(sImgPath, width, height, alt)
{
	var scroll = 'no';
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28)
		scroll = 'yes';
	if(height < screen.height-28)
		top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10)
		left = Math.floor((screen.width - width)/2);
	width = Math.min(width, screen.width-10);	
	height = Math.min(height, screen.height-28);	
	window.open('/bitrix/tools/imagepg.php?alt='+alt+'&img='+sImgPath,'','scrollbars='+scroll+',resizable=yes, width='+width+',height='+height+',left='+left+',top='+top);
}

function LearningInitSpoiler (oHead)
{
	if (typeof oHead != "object" || !oHead)
		return false; 
	var oBody = oHead.nextSibling;

	while (oBody.nodeType != 1)
		oBody=oBody.nextSibling;

	oBody.style.display = (oBody.style.display == 'none' ? '' : 'none'); 
	oHead.className = (oBody.style.display == 'none' ? '' : 'learning-spoiler-head-open'); 
}
