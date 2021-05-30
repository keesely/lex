<?php
/**
 * 
 * @fileName MongoHandler.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 12/04/2018
 * @version MongoHandler.php 2018.04.12
 * */
namespace Lex\Handler;

use Monolog\Handler\AbstractProcessingHandler as Handler;
use Monolog\Logger;
use Request;
use Illuminate\Support\Facades\DB;

defined('LUMEN_START') || define('LUMEN_START', microtime(true));

class MongoHandler extends Handler {

  private $_mongo;

  public function __construct ($database, $collection, $level = Logger::NOTICE, $bubble = true) {
    $this->_mongo = DB::connection($database)->collection($collection);
    parent::__construct($level, $bubble);
  }

  /** 
   * 日志写入
   * {@inheritDoc}
   */
  protected function write (array $record) {
    $record['timestamp'] = $record['datetime']->setTimezone(new \DateTimeZone('PRC'))->format('c');
    $dur     = number_format(microtime(true) - LUMEN_START, 3);
    $request = request();
    $uri = $request->getRequestUri();
    $method = $request->getMethod();
    $client_ip = $request->getClientIp();
    $data = $request->all();
    $record['response_time'] = $dur;
    $record['request'] = array(
      'uri'       => $uri ?: '-',
      'method'    => $method ?: 'cli',
      'client_ip' => $client_ip ?: '0.0.0.0',
      'data'      => $data ?: [],
    );

    $this->_mongo->insert($record);
    //Search::index($this->_index)->insert(md5(microtime(true) . mt_rand(1000, 9999)), $record);
  }

}

