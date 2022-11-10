<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Magento\Framework\Logger\Handler\Base;

class LoggerHandler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/katanapim/system.log';
}
