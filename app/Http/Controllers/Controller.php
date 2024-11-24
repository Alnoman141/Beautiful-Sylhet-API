<?php

namespace App\Http\Controllers;

abstract class Controller extends \Illuminate\Routing\Controller
{
    protected $lang=1;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if($request->hasHeader('language_id') && $request->header('language_id')!=null){
                $this->lang=$request->header('language_id');
            }
            elseif($request->filled('language_id')){
                $this->lang=$request->language_id;
            }
            return $next($request);
        });
    }
}
