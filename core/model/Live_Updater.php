<?php

namespace Model;

class Live_Updater extends \Core\Entity
{
	public static string $version = '1.2.0'; # do not edit this line


	/**
	 * get all version from package server
	 * @return array
	 */
	public static function getVersions():array {

		$uri = \Core\Config::get('live_updater/url')."/?canal=".\Core\Config::get('live_updater/canal');
		$versions = @file_get_contents($uri);
		$versions = json_decode($versions);

		return $versions;
	}





	
}