<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Advertisement;
use App\Models\Work;
use Illuminate\Support\Carbon;

class CheckWorkPlanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $trees=Advertisement::where('status','pin')->where( 'updated_at', '<', Carbon::now()->subDays(10))->get();
        for($i=0;$i<count($trees);$i++){
            $trees[$i]->volunteer_id=null;
            $trees[$i]->status="wait";
            $trees[$i]->save();
        }
        $works=Work::where('status','pin')->where( 'updated_at', '<', Carbon::now()->subDays(10))->get();
        for($i=0;$i<count($works);$i++){
            $works[$i]->volunteer_id=null;
            $works[$i]->status="wait";
            $works[$i]->save();
        }
        return $next($request);
    }
}
