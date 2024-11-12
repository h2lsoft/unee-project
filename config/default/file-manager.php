<?php

return [

	'upload' => [
		'file_max_size' => "20Mo",
		'allowed_ext' => ['filter:image', 'filter:audio', 'filter:video', 'filter:document', 'filter:archive'], // enter nothing for all
		'allowed_mimetype' => [], // enter nothing for all
	],

	'security' => [
		'dir.name.alnum_allowed_chars_additionnal' => ' -_',
		'file.name.alnum_allowed_chars_additionnal' => ' -_',
		'excludes' => ['.htaccess', '.gitignore']
	],

	'filters' => [
		'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
		'audio' => ['mp3', 'ogg', 'wav', 'aac'],
		'video' => ['mp4', 'webm', 'ogg', 'mov', 'avi'],
		'word' => ['doc', 'docx'],
		'excel' => ['xls', 'xlsx'],
		'pdf' => ['pdf'],
		'document' => ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'odf', 'odt'],
		'archive' => ['zip', 'rar', '7zip'],
	],

	'dir' =>
		[
			'permission_creation' => 0770,
			'root' => APP_PATH."/public/".APP_PACKAGE."/www",
			'cache' => APP_PATH."/public/".APP_PACKAGE."/www/.thumbs",
			'forbidden' => []
		]

];
