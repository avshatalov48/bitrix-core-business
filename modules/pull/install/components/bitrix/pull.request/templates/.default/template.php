<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
$frame = $this->createFrame("bx-pull-start")->begin("");
?>
	<script type="text/javascript">
		BX.ready(function() { BX.PULL.start(<?=(empty($arResult)? '': CUtil::PhpToJsObject($arResult))?>); });
	</script>
<?
$frame->end();