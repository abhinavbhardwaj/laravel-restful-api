<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
//        return response()->json(
//                          $this->getJsonMessage($exception), 
//                          $this->getExceptionHTTPStatusCode($exception)
//                        ); 
    }
      
    protected function getJsonMessage($exception){
        // You may add in the code, but it's duplication
        $message = (!empty($exception->getMessage())) ? $exception->getMessage() : "Error occurred";
        return array('error'=>$message);
    }

    protected function getExceptionHTTPStatusCode($exception){
        // Not all Exceptions have a http status code
        // We will give Error 500 if none found
        return method_exists($exception, 'getStatusCode') ? 
                         $exception->getStatusCode() : 500;
    }
}
