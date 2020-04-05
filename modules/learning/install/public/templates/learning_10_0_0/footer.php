<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?IncludeTemplateLangFile(__FILE__);?>

<? if (defined("BX_AUTH_FORM") && BX_AUTH_FORM === true): ?>
	</td>
</tr>
</table>
<?endif?>

		</td>
	</tr>
	<tr>
		<td id="footer">
			<div id="footer-inner">
				<span id="footer-text" onclick="scroll(0,0);"><?=GetMessage("LEARNING_TEMPLATE_TOP")?><span id="footer-text-arrow"></span></span>
			</div>
		</td>
	</tr>
</table>
</body>
</html>