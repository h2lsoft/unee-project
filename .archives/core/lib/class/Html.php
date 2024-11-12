<?php

namespace Core;

class Html
{
	
	
	public static function Image(string $src="", array $attributes=[]): string
	{
		$attrs = "";
		foreach($attributes as $key => $val)
		{
			$attrs .= " {$key}=\"{$val}\" ";
		}
		
		$str = "<img src=\"{$src}\" {$attrs}>";
		return $str;
	}
	
	public static function Icon(string $content="", array $attributes=[]): string
	{
		$class = "{$content} ".@$attributes['class'];
		
		unset($attributes['class']);
		$attrs = "";
		foreach($attributes as $key => $val)
		{
			$attrs .= " {$key}=\"{$val}\" ";
		}
		
		$str = "<i class=\"{$class}\" {$attrs}></i>";
		return $str;
	}
	
	
	public static function Button(string $name, string $label="", string $type="button", array $attributes=[]): string
	{
		if(empty($label))$label = str_replace('_', ' ', $name);
		$class = @$attributes['class'];
		
		unset($attributes['class']);
		$attrs = "";
		foreach($attributes as $key => $val)
		{
			$attrs .= " {$key}=\"{$val}\" ";
		}
		
		$str = "<button id=\"{$name}\" class=\"btn btn-default {$class}\" type=\"{$type}\" {$attrs}>{$label}</button>";
		return $str;
	}
	
	
	public static function Badge(string $text, string $type='', string $class="", array $attributes=[]):string {
		
		if(empty($type) && in_array($text, ['error', 'fatal', 'critical', 'warning', 'success', 'debug']))
			$type = $text;
		
		if(empty($type))$type = 'text-bg-light';
		elseif($type == 'error' || $type == 'fatal' || $type == 'critical' || $type == 'danger')$type = 'text-bg-danger';
		elseif($type == 'warning')$type = 'text-bg-warning';
		elseif($type == 'success')$type = 'text-bg-success';
		elseif($type == 'debug')$type = 'text-bg-dark';
		elseif($type == 'info')$type = 'text-bg-info';
		
		unset($attributes['class']);
		$attrs = "";
		foreach($attributes as $key => $val)
			$attrs .= " {$key}=\"{$val}\" ";
		
		
		
		$str = "<span class=\"badge {$type} {$class}\" {$attrs}>{$text}</span>";
		return $str;
	}
	
	public static function A(string $text, string $url, array $attributes=[]): string
	{
		$attrs = "";
		foreach($attributes as $key => $val)
			$attrs .= " {$key}=\"{$val}\" ";
		
		$str = "<a href=\"{$url}\" {$attrs}>{$text}</a>";
		return $str;
	}
	
}