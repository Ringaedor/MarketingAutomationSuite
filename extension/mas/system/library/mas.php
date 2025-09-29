<?php
namespace Opencart\System\Library\Extension\Mas;
require_once DIR_EXTENSION . 'mas/system/library/Mas.php';

class Mas {
	private object $registry;
	private object $mas;

	public function __construct(object $registry) {
		$this->registry = $registry;
		$this->mas = new \Opencart\System\Library\Extension\Mas\Sys\Mas($registry);
		$registry->set('mas', $this->mas);
	}
}