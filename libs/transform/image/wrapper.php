<?php

class Transform_Image
{
	protected static $worker_name;

	public static function get_worker($path) {
		$name = self::get_worker_name();

		return new $name($path);
	}

	protected static function get_worker_name() {
		if (empty(self::$worker_name)) {
			if (!class_exists('Imagick', false)) {
				self::$worker_name = 'Transform_Image_Gd';
			} else {
				self::$worker_name = 'Transform_Image_Imagick';
			}
		}

		return self::$worker_name;
	}
}
