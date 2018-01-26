<?php

namespace App\Http\Middleware;

use Closure;
use Twitter;

class TwitterWrapper
{
   protected $config = [];

   public function __construct()
   {
      $this->setConfig([
         'consumer_key' => env('TWITTER_CONSUMER_KEY'),
         'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
      ]);
   }

   protected function setConfig( $config = [] )
   {
      $this->config = array_replace_recursive( $this->config, $config );
      Twitter::reconfig( $this->config );
   }
 
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authToken = $request->header('authToken'); 
        $authSecret = $request->header('authSecret');  
        
        $this->setConfig([
         'token'     => $authToken,
         'secret'     => $authSecret,
      ]); 
        return $next($request);
    }
}
