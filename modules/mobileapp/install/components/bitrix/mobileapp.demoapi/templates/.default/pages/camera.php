<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<button class="styled-button" onclick="document.location.reload();"><?=GetMessage("MB_DEMO_RELOAD");?></button>
<button class="styled-button" onclick="BXMobileDemoApi.camera.open(1)"><?=GetMessage("MB_DEMO_TAKE_FROM_GALLERY");?></button>
<button  class="styled-button" onclick="BXMobileDemoApi.camera.open(2)"><?=GetMessage("MB_DEMO_CAMERA_OPEN");?></button>
