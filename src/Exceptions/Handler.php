<?php

namespace Lex\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

    protected $_validate = null;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
      $appType = strtoupper(env('APP_TYPE', 'API'));
      if ('API' != $appType) return parent::render($request, $e);

      if ($e instanceof ValidationException && $e->getResponse()) {
        $error = $e->getResponse();
        $this->_validate = $error->original;
        $trans = __('app.http_code.422');
        $msg = $trans != 'app.http_code.422' ? $trans : $e->getMessage();
        return response($this->error($msg, 422, $e), 422);
        //return response($this->error($msg, 422, $e, $request->all()), 422);
      }
      elseif ($e instanceof AuthorizationException) {
        $code = $e->getCode();
        $msg = $e->getMessage() ?: 'Http Request Error';
        return response($this->error($msg, $code, $e), $code);
      }
      elseif ($e instanceof HttpException) {
        $code = $e->getStatusCode();
        $msg = $e->getMessage() ?: 'Http Request Error';
        if (404 == $code) $msg = 'Api result not found!';
        return response($this->error($msg, $code, $e), $code);
      }
      else {
        return response(
          $this->error($e->getMessage(), $e->getCode(), $e, $request), 
          500
        );
      }
    }

    public function error ($msg, $code, $e = NULL, $data = NULL) {
      $return = [
        "code" => $code,
        'msg' => $msg,
      ];
      if ($this->_validate && is_array($this->_validate)) {
        $return['validate'] = $this->_validate;
      }
      if (env('APP_DEBUG', true) && $e) {
        $return['exception'] = explode("\n", $e->getTraceAsString());
        if ($data) $return['params'] = $data;
      }
      return $return;
    }

}
