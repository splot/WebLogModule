<?php
namespace Splot\WebLogModule;

use Splot\Foundation\Debug\Logger;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

class SplotWebLogModule extends AbstractModule
{

	public function boot() {
		/*
		 * EVENT LISTENERS
		 */
		$this->container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) {
			if (Logger::isEnabled()) {
				$log = \dump(Logger::getLog(), true);
				$event->getResponse()->alterPart('</body>', $log .'</body>');
			}
		}, -99999);
	}

}