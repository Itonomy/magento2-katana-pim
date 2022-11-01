<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

class LoggerHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/katanapim/system.log';
}
