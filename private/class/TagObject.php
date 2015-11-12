<?php
class TagObject {
	public $id;
	public $name;
	public $base_color;
	public $icon;

	public function __construct($resource) {
		$this->id = $resource->id;
		$this->name = $resource->name;
		$this->base_color = $resource->base_color;
		$this->icon = $resource->icon;
	}

	public function getID() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	//As opposed to getBaseColor()
	public function getColor() {
		return $this->base_color;
	}

	public function getIcon() {
		return $this->icon;
	}
}
?>
