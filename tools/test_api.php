<?php

$ch = curl_init();

$data = array(
	'image' => base64_encode(file_get_contents('/tmp/1.jpg')),
//	'category' => array('Фото', 'nsfw'),
	'author' => 'Василий',
	'tag' => array('touhou', 'herpaderpa', 'testinnnng<author>'),
	'source' => '4otaku.ru',
	'format' => 'json'
);

curl_setopt($ch, CURLOPT_URL, "http://4otaku.local/api/create/art");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);

echo $result;
