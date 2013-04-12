<?php
namespace Splot\WebLogModule;

use Splot\Log\LogContainer;
use Splot\Log\ExportableLogInterface;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

class SplotWebLogModule extends AbstractModule
{

    public function boot() {
        if (LogContainer::isEnabled()) {
            $this->container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) {
                $logs = array();
                foreach(LogContainer::getLogs() as $name => $log) {
                    if (!$log instanceof ExportableLogInterface) {
                        continue;
                    }

                    $logs[$name] = $log->getLog();
                }

                $logsContent = dump($logs, true);
                $event->getResponse()->alterPart('</body>', '<br /><br /><br /><br /><br /><hr /><br /><br />'. $logsContent .'</body>');
            }, -99999);
        }
    }

}