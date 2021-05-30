<?php
/**
 * 
 * @fileName helpers.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2021
 * @version 1.0.1 2021.05.29
 * */

if (! function_exists('secure_asset')) {
  /**
   * Generate an asset path for the application.
   *
   * @param  string  $path
   * @return string
   */
  function secure_asset($path)
  {
    return asset($path, true);
  }
}

if (! function_exists('secure_url')) {
  /**
   * Generate a HTTPS url for the application.
   *
   * @param  string  $path
   * @param  mixed   $parameters
   * @return string
   */
  function secure_url($path, $parameters = [])
  {
    return url($path, $parameters, true);
  }
}

if (! function_exists('session')) {
  /**
   * Get / set the specified session value.
   *
   * If an array is passed as the key, we will assume you want to set an array of values.
   *
   * @param  array|string  $key
   * @param  mixed  $default
   * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
   */
  function session($key = null, $default = null)
  {
    if (is_null($key)) {
      return app('session');
    }

    if (is_array($key)) {
      return app('session')->put($key);
    }

    return app('session')->get($key, $default);
  }
}

if (! function_exists('today')) {
  /**
   * Create a new Carbon instance for the current date.
   *
   * @param  \DateTimeZone|string|null $tz
   * @return \Illuminate\Support\Carbon
   */
  function today($tz = null)
  {
    return Carbon::today($tz);
  }
}

/**
 * Get the configuration path.
 *
 * @param string $path
 * @return string
 */
if (!function_exists('config_path')) {
  function config_path ($path = '') {
    $path = $path ? '/'.$path : $path;
    $configPath = app()->basePath('config') . $path;
    return realpath($configPath) ? 
      $configPath :
      (realpath(__DIR__ . '/../config'.$path) ?: $configPath);
  }
}
/**
 * 将ipv4地址转换成int型:处理低版本php无ip2long函数方法
 * @param $ip  ip地址
 * @return number 返回数值
 */
if (!function_exists('ip2int')) {
  function ip2int($ip){
    @list($t1, $t2, $t3, $t4) = explode('.', trim($ip));
    $ip2int = ($t1<<24 | $t2 << 16 | $t3 << 8 | $t4);
    return (0 < $ip2int) ? $ip2int : ((0 != $ip2int) ? $ip2int += 4294967296 : $ip2int);
  }
}

/**
 * 将十进制转为ipv4
 * @param $long  十进制整数
 * @return string   ipv4格式的字符串
 * */
if (!function_exists('int2ip')) {
  function int2ip ($long) {
    $ipArr = array(0 => floor($long / 0x1000000));
    $ipVint = $long-($ipArr[0]*0x1000000); // for clarity
    $ipArr[1] = ($ipVint & 0xFF0000)  >> 16;
    $ipArr[2] = ($ipVint & 0xFF00  )  >> 8;
    $ipArr[3] =  $ipVint & 0xFF;
    $ipDotted = implode('.', $ipArr);
    return $ipDotted;
  }
}

/**
 * 将ipv6格式转化为十进制数
 * @param $ipv6  ip地址
 * @return number 返回十进制数值
 */
if (!function_exists('ip2int_v6')) {
  function ip2int_v6($ipv6){
    $ip_n = inet_pton($ipv6);

    $bits = 15;
    $ipv6long = null;

    while ($bits >= 0) {
      $bin = sprintf("%08b",(ord($ip_n[$bits])));
      $ipv6long = $bin.$ipv6long;
      $bits--;
    }
    $res=gmp_strval(gmp_init($ipv6long,2),10);
    return $res;
  }
}

/**
 * 将十进制数转化为ipv6格式
 * @param $ipv6long biglong 十进制数
 * @return  string  返回ipv6格式的字符串
 * */
if (!function_exists('int2ip_v6')) {
  function int2ip_v6($ipv6long){
    $bin = gmp_strval($ipv6long,16);
    $bits = 0;
    $ipv6 = null;
    while ($bits <= 7) {
      $ipv6 .= substr($bin,($bits*4),4).":";
      $bits++;
    }
    return inet_ntop(inet_pton(substr($ipv6,0,-1)));
  }
}

/**
 * 字符串转码 - 自动判断
 *
 * @param string  $string      需要转换的字符串
 * @param string  $origin      原字符类型
 * @param string  $convert     需要转转换的字符类型
 * 
 * @return string
 * */
if (!function_exists('mb_iconv')) {
  function mb_iconv ($string, $origin, $convert) {
    $origin = strtoupper($origin);
    $encoding = array('UTF-8', 'GBK', 'GB2312', 'BIG5');
    if (!in_array($origin, $encoding)) $encoding[] = $origin;
    $mbencode = mb_detect_encoding($string, $encoding);
    if ($origin == $mbencode) {
      return mb_convert_encoding($string, $convert, $origin);
    }
    elseif ($convert != $mbencode) {
      return mb_convert_encoding($string, $convert, $mbencode);
    }
    return $string;
  }
}

/**
 * 处理数组空元素
 * */
if (!function_exists('array_trim')) {
  function array_trim($array) {
    return array_filter( array_map('trim', $array) );
  }
}

/**
 * 结构化输入数组追加默认构造
 * */
if (!function_exists('array_default_assign')) {
  function array_default_assign (array $struct, array $data) {
    foreach ($struct as $k => $v) {
      if (!isset($data[$k])) $data[$k] = $v;
    }
    return $data;
  }
}

/**
 * 结构化输入数组
 * */
if (!function_exists('array_struct')) {
  function array_struct (array $struct, array $data, $assign = false) {
    $tmp = [];
    foreach ($struct as $v) {
      if (isset($data[$v])) $tmp[$v] = $data[$v];
    }
    if (!$tmp && $assign) {
      $_struct = [];
      foreach ($struct as $v) $_struct[$v] = null;
      $tmp = array_default_assign($_struct, $data);
    }
    return $tmp;
  }
}

/**
 * 获取json配置文件
 * */
if (!function_exists('config_get_json')) {
  function config_get_json ($json_file, $coverArray = true) {
    $json_file = config_path($json_file.'.json');
    if (!is_file($json_file)) return NULL;
    return json_decode(file_get_contents($json_file), $coverArray);
  }
}

/***
 * 打印调试堆栈-不触发Exception
 * */
if (!function_exists('print_stack_trace')) {
  function print_stack_trace() {
    $array =debug_backtrace();
    //print_r($array);//信息很齐全
    unset($array[0]);
    $data = [];
    foreach($array as $row)
    {
      $data[] = array_get($row, 'file').
        ':'.array_get($row, 'line').
        '行,调用方法:'.array_get($row, 'function');
    }
    return $data;
  }
}

/** Json数据格式化
 * @param  Mixed  $data   数据
 * @param  String $indent 缩进字符，默认4个空格
 * @return JSON
 */
if (!function_exists('json_format')) {
  function json_format($data, $indent=null){
    // 对数组中每个元素递归进行urlencode操作，保护中文字符
    function jsonFormatProtect(&$val){
      if($val!==true && $val!==false && $val!==null){
        $val = urlencode($val);
      }
    }
    array_walk_recursive($data, 'jsonFormatProtect');

    // json encode
    $data = json_encode($data);

    // 将urlencode的内容进行urldecode
    $data = urldecode($data);

    // 缩进处理
    $ret = '';
    $pos = 0;
    $length = strlen($data);
    $indent = isset($indent)? $indent : '    ';
    $newline = "\n";
    $prevchar = '';
    $outofquotes = true;

    for($i=0; $i<=$length; $i++){
      $char = substr($data, $i, 1);

      if($char=='"' && $prevchar!='\\'){
        $outofquotes = !$outofquotes;
      }elseif(($char=='}' || $char==']') && $outofquotes){
        $ret .= $newline;
        $pos --;
        for($j=0; $j<$pos; $j++){
          $ret .= $indent;
        }
      }

      $ret .= $char;

      if(($char==',' || $char=='{' || $char=='[') && $outofquotes){
        $ret .= $newline;
        if($char=='{' || $char=='['){
          $pos ++;
        }

        for($j=0; $j<$pos; $j++){
          $ret .= $indent;
        }
      }

      $prevchar = $char;
    }

    return $ret;
  }
}

/**
 * 发送内部请求
 *
 * */
if (!function_exists('call_api')) {
  function call_api ($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = NULL) {
    $request = Illuminate\Http\Request::create(
      $uri, $method, $parameters,
      $cookies, $files, $server, $content
    );

    $response = app()->prepareResponse(app()->handle($request))->getContent();
    $json = json_decode($response);
    return (!$json) ? 
      $response :
      (
        (200 != $json->code) ? false : $json->result
      );
  }
}

/**
 * 合并字段模糊检索数据库
 * */
if (!function_exists('concat_search')) {
  function concat_search ($selector,array $concat, $query) {
    $concat = array_map(function ($val) {
      return "IFNULL({$val}, '')";
    }, $concat);

    return $selector->whereRaw(
      'CONCAT('.implode(',', $concat).') LIKE ?', '%'.$query.'%'
    );
  }
}

if (!function_exists('libxml_display_error')) {
  function libxml_display_error($error) { 
    $return = "<br/>\n"; 
    switch ($error->level) { 
    case LIBXML_ERR_WARNING: 
      $return .= "<b>Warning $error->code</b>: "; 
      break; 
    case LIBXML_ERR_ERROR: 
      $return .= "<b>Error $error->code</b>: "; 
      break; 
    case LIBXML_ERR_FATAL: 
      $return .= "<b>Fatal Error $error->code</b>: "; 
      break; 
    } 
    $return .= trim($error->message); 
    if ($error->file) { 
      $return .= " in <b>$error->file</b>"; 
    } 
    $return .= " on line <b>$error->line</b>\n"; 

    return $return; 
  } 
}

if (!function_exists('libxml_display_errors')) {
  function libxml_display_errors() { 
    $errors = libxml_get_errors();
    $errors = array_map('libxml_display_error', $errors);
    libxml_clear_errors(); 
    return implode('', $errors);
  }
}

if (!function_exists('xsd_validate')) {
  function xsd_validate ($xml, $xsd) {
    libxml_use_internal_errors(FALSE);
    $_doc = new DOMDocument();
    $_doc->load($xml);
    if (!$_doc->schemaValidate($xsd)) {
      throw new Exception(libxml_display_errors());
    }
    return true;
  }
}

if (!function_exists('is_assoc_array')) {
  function is_assoc_array (array $arr) {
    return array_keys($arr) === range(0, count($arr) - 1); 
  }
}

// 获取客户端IP
if (!function_exists('get_client_ip')) {
  function get_client_ip() {
    $getServer = function ($name) {
      $name = strtoupper($name);
      if (!isset($_SERVER[$name])) return false;
      return $_SERVER[$name]; 
    };

    if ($ip = $getServer('HTTP_X_REAL_IP'))   return $ip;
    if ($ip = $getServer('HTTP_REAL_IP'))     return $ip;
    if ($ip = $getServer('HTTP_REMOTE_ADDR')) return $ip;
    if ($ip = $getServer('HTTP_CLIENT_IP'))   return $ip;
    if ($ip = $getServer('REMOTE_ADDR'))      return $ip;
    if ($ip = $getServer('HTTP_X_FORWARDED_FOR')) return $ip;
    return $ip = "0.0.0.0";
  }
}

// 获取url 
if (!function_exists('get_host_url')) {
  function get_host_url ($uri = false) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return "$protocol$_SERVER[HTTP_HOST]".($uri ? $_SERVER['REQUEST_URI'] : '');
  }
}

/**
 * 默认初始化分页数据
 *
 * @param int   $current    当前页数
 * @param int   $per_page   每页显示数
 * @param int   $total      数据总数
 *
 * @return array
 * */
if (!function_exists('paginate')) {
  function paginate ($current, $per_page, $total) {
    $page = new \Lex\Paginator(intval($current), intval($per_page), intval($total)); 
    return $page->toArray([
      'page_count' => 'pageCount',
      'per_page'  => 'perPage',
      'current'    => 'current',
      'previous'   => 'previous',
      'next' => 'next',
      'last' => 'last',
      'total' => 'total',
    ]);
  }
}

if (!function_exists('getPaginate')) {
  function getPaginate ($selector, $per_count = null) {
    $data = null;
    $current_page = 1;
    $per_page = $per_count ?: request('limit', request('length', 20));
    $total = 0;
    if ($selector instanceof \Illuminate\Database\Eloquent\Builder) {
      $data = (object) $selector->paginate((int) $per_page)
                                ->toArray();
      
    }
    elseif ($selector instanceof \Illuminate\Pagination\LengthAwarePaginator) {
      $data = (object) $selector->toArray();
    }
    elseif (is_array($selector)) {
      $data = (object) $selector->toArray();
    }
    else return false;
    $current_page = $data->current_page;
    $per_page = $data->per_page;
    $total = $data->total;
    $pages = paginate($current_page, $per_page, $total);
    return [$data->data, $pages];
  }
}

if (!function_exists('validate')) {
  function validate ($data, array $rules, array $message = [], array $customAttributes = []) {
    $data = ($data instanceof \Illuminate\Http\Request) ? $data->all() : $data;
    $validator = validator($data, $rules, $message, $customAttributes);
    if ($validator->fails()) {
      throw new \Illuminate\Validation\ValidationException(
        $validator,
        new \Illuminate\Http\JsonResponse($validator->errors()->getMessages(), 422)
      );
    }
    $keys = collect($rules)->keys()->map(function($rule) {
      return Str::contains($rule, '.') ? explode('.', $rule)[0] : $rule;
    })->unique()->toArray();
    return array_struct($keys, $data);
  }
}
