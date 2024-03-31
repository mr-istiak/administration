<?php

namespace Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RoleController extends Controller
{
    private $accesses;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function create()
    {
        return (new ModelController($this->request))->create()->with([
            'accesses' => $this->getAccesses(),
            'roles' => Role::all(['id', 'name'])->toArray()
        ]);
    }

    public function edit($id)
    {
        return (new ModelController($this->request))->edit($this->request, $id)->with([
            'accesses' => $this->getAccesses(),
            'roles' => Role::all(['id', 'name'])->toArray(),
            'current' => (int) $id
        ]);
    }

    public function update($user)
    {
        Role::user($user)->change((int) $this->request->input('role'));
        return back()->with('success.roleChanged', 'Role changed successfully to ' . Role::where('id', $this->request->input('role'))->get('name')->first()->name);
    }

    private function getAccesses()
    {
        $accessPoints = [];
        foreach (Route::getMiddleware() as $key => $value) {
            $value = explode('\\', $value);
            $middlewareClass = array_pop($value);
            $accessPoints["middleware:$key"] =  array_pop($value) . '/' . $middlewareClass;
        }
        collect(Route::getRoutes()->getRoutesByName())
            ->each(function($value, $key) use(&$accessPoints) {
                $value = explode('.', $key);
                $key = $value[0];
                array_shift($value);
                $value = join('.', $value);
                $accessPoints["route:$key"] = isset($accessPoints["route:$key"]) ? $accessPoints["route:$key"] . ',' . strtoupper($value) : strtoupper($value);
            });
        return $accessPoints;
    }
}
