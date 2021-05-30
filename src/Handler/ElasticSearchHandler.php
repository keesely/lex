<?php
/**
 * 
 * @fileName ElasticSearchHandler.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 12/04/2018
 * @version ElasticSearchHandler.php 2018.04.12
 * */
namespace Lex\Handler;

use Monolog\Handler\AbstractProcessingHandler as Handler;
use Monolog\Logger;
use Search;

class ElasticSearchHandler extends Handler {

  private $_data, $_hosts;

  private $_index = 'lumen_logs';

  public function __construct ($index, $channel, $level = Logger::NOTICE, $bubble = true) {
    $this->_data['channel'] = $channel;
    $this->_index = $index;
    parent::__construct($level, $bubble);
  }

  /** 
   * 日志写入
   * {@inheritDoc}
   */
  protected function write (array $record) {
    $record['@timestamp'] = $record['datetime']->setTimezone(new \DateTimeZone('PRC'))->format('c');
    $dur     = number_format(microtime(true) - LARAVEL_START, 3);
    $request = request();
    $record['request'] = array(
      'response_time' => $dur,
      'client_ip' => $request->getClientIp(),
      'method'    => $request->getMethod(),
      'requests' => array(
        'uri' => $request->getRequestUri(),
        $request->all(),
      )
    );

    Search::index($this->_index)->insert(md5(microtime(true) . mt_rand(1000, 9999)), $record);
  }
}

