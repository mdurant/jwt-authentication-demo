<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class CustomizeFormatter{
    public function __invoke(Logger $logger)
    {
        // Define el formato personalizado
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

        // Crea un nuevo LineFormatter con el formato personalizado
        $formatter = new LineFormatter($output, null, true, true);

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);
        }
    }
}
