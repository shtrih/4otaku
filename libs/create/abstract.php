<?php

abstract class Create_Abstract extends Action_Abstract
{
	public function __construct($reader = null, $writer = null) {
		parent::__construct($reader, $writer);

		$this->writer->set_redirect();
	}
}
