<?php
namespace Glass;

class DependencyObject {
	private $id;

	public $target;
	public $required;

	public function __construct($resource) {
		$this->id = $resource->id;
		$this->target = $resource->target;
		$this->required = $resource->requirement;
	}

	public function getID() {
		return $this->id;
	}

	public function getTarget() {
		return $this->target;
	}

	public function getRequired() {
		return $this->required;
	}
}
?>
