<?php

\Core\EventManager::on('core.controller.init', function() {

	if(\Model\User::isLogon())
	{
		$f = [];
		$f['last_connection_date'] = now();
		$f['last_connection_ip'] = getVisitorIp();
		\Model\User::update($f, \Model\User::getUID());
	}

});




