<?php

namespace Core;

class Request {


	public static function get()
	{
		return App()->request;
	}

	public static function getUrlParameter($parameter)
	{
		return App()->request->attributes->get($parameter);
	}




}