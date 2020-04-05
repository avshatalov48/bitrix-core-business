<?php

namespace Bitrix\Sale\Discount\Preset;


final class HtmlHelper
{
	public static function generateSelect($id, array $selectData, $value, array $params = array())
	{
		$html = '<select name="' . $id . '" id="' . $id . '"';
		foreach($params as $param)
		{
			$html .= ' ' . $param;
		}
		$html .= '>';

		foreach($selectData as $key => $val)
		{
			$html .= 
				'<option value="' . htmlspecialcharsbx($key) . '"' . ($value == $key ? ' selected' : '') . '>' . 
					htmlspecialcharsex($val) . 
				'</option>';
		}
		$html .= '</select>';

		return $html;
	}
	
	public static function generateMultipleSelect($id, array $selectData, array $values, array $params = array())
	{
		$html = '<select multiple name="' . $id . '" id="' . $id . '"';
		foreach($params as $param)
		{
			$html .= ' ' . $param;
		}
		$html .= '>';

		foreach($selectData as $key => $val)
		{
			$html .= 
				'<option value="' . htmlspecialcharsbx($key) . '"' . (in_array($key, $values) ? ' selected' : '') . '>' .
					htmlspecialcharsex($val) . 
				'</option>';
		}
		$html .= '</select>';

		return $html;
	}
}