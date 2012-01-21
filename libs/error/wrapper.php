<?php

class Error extends Exception {
	const
		FILE_TOO_LARGE = 10,
		NOT_AN_IMAGE = 20,
		ALREADY_EXISTS = 30,
		MISSING_INPUT = 420,
		INCORRECT_INPUT = 430;

	public function __construct($message = '', $code = 0, Exception $previous = null) {

		if (is_int($message) && (empty($code) || !is_int($code))) {
			parent::__construct('', $message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}
}
