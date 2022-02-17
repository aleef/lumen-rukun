<?php
// app/Http/Middleware/Language.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Response;



class ValidateKeyCRM
{
	
	/*=== Handle ===*/
    public function handle($request, Closure $next)
    {
		$header = apache_request_headers();
		
		// cek api key
        if(@$header['x-auth']!=env('API_KEY'))
		{
			
			//response
			$response['status'] = 'error';
			$response['message'] = "Unauthorized Access";
			// return json response
			return Response::json($response);

			//print_r($header);
		
		} else {
			return $next($request);
		}
    }
		
}