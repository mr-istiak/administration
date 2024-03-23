<?php

namespace Administration;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ModelController extends Controller
{
    protected $model;

    public function __construct(Request $request)
    {
        foreach (config('administration.menu.models') as $key => $value) {
            if( explode('.', $request->route()->getName())[0] === str_replace(' ', '-', strtolower($key)) ) return $this->model = $value;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $columns = array_flip(Schema::getColumnListing((new $this->model)->getTable()));
        foreach (config('administration.models')[$this->model]['columns'] as $key => $value) {
            $props = array_flip(explode(',', $value));
            if(isset($props['hidden'])) unset($columns[$key]);
        }
        $options = [
            'new' => isset(config('administration.models')[$this->model]['new']) ? config('administration.models')[$this->model]['new'] : true,
            'edit' => isset(config('administration.models')[$this->model]['edit']) ? config('administration.models')[$this->model]['edit'] : true,
            'delete' => isset(config('administration.models')[$this->model]['delete']) ? config('administration.models')[$this->model]['delete'] : true
        ];
        return Inertia::render('Admin/Model/Index', [
            'columns' => array_keys($columns),
            'data' => $this->model::all(array_keys($columns)),
            'options' => $options
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $name = explode('\\', $this->model);
        $name = array_pop($name);
        $inputs = $this->inputs();

        return Inertia::render(config('administration.models')[$this->model]['view']['create'] ?? 'Admin/Model/Create', compact('inputs', 'name'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : RedirectResponse
    {
        $modelKeyName = explode('\\', $this->model)[array_key_last(explode('\\', $this->model))];
        $model = new $this->model;
        if(class_exists('App\\Http\\Requests\\'.$modelKeyName.'StoreRequest')) {
            $request = ('App\\Http\\Requests\\'.$modelKeyName.'StoreRequest')::createFrom($request);
            $response = $request->validate($request->rules());
        } else {
            $response = $request->validate($this->validations($request, $model));
        }
        $model = $model->create($response);
        if(class_exists('App\\Events\\'.$modelKeyName.'Created')) {
            event(new ('App\\Events\\'.$modelKeyName.'Created')($model));
        }
        return redirect()->route(explode('.', $request->route()->getName())[0].'.index')->with('success.'.$modelKeyName.'Created', $modelKeyName.' created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $modelKeyName = explode('\\', $this->model)[array_key_last(explode('\\', $this->model))];
        return Inertia::render($modelKeyName.'/Show', $this->model::find($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $name = explode('\\', $this->model);
        $name = array_pop($name);
        $inputs = $this->inputs($this->model::find($id));

        return Inertia::render(config('administration.models')[$this->model]['view']['create'] ?? 'Admin/Model/Create', compact('inputs', 'name'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id) : RedirectResponse
    {
        $modelKeyName = explode('\\', $this->model)[array_key_last(explode('\\', $this->model))];
        $model = $this->model::find($id);
        if(class_exists('App\\Http\\Requests\\'.$modelKeyName.'UpdateRequest')) {
            $request = ('App\\Http\\Requests\\'.$modelKeyName.'UpdateRequest')::createFrom($request);
            $response = $request->validate($request->rules());
        } else {
            $response = $request->validate($this->validations($request, $model, true));
        }
        $model = $model->update($response);
        if(class_exists('App\\Events\\'.$modelKeyName.'Updated')) {
            event(new ('App\\Events\\'.$modelKeyName.'Updated')($model));
        }
        return redirect()->route(explode('.', $request->route()->getName())[0].'.index')->with('success.'.$modelKeyName.'Updated', $modelKeyName.' updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id) : RedirectResponse
    {
        $modelKeyName = explode('\\', $this->model)[array_key_last(explode('\\', $this->model))];
        $model = (new $this->model)->find($id);
        $model->delete();
        return back()->with('success.'.$modelKeyName.'Deleted', $modelKeyName.' deleted successfully');
    }

    /**
     * Makes the validation rules on the request data for the given model.
     *
     * @param Request $request The request object
     * @param Model $model The model object
     * @param bool $update Flag indicating if it's an update operation
     * @return array The array of validation rules
     */
    private function validations(Request $request, Model $model, $update = false)
    {
        $validations = [];
        array_map(function ($column) use (&$validations, &$request, $model, $update) {
            if(!isset($request->all()[$column['name']])) return;
            $validation = [];
            if(!$column['nullable']) $validation[] = 'required';
            if(collect(['bigint', 'decimal', 'double', 'integer', 'mediumint', 'smallint', 'tinyint'])->contains($column['type_name'])) $validation[] = 'integer';
            if (collect(['char', 'float', 'ulid', 'varchar'])->contains($column['type_name'])) $validation[] = 'max:255';
            if ('ulid' === $column['type_name']) $validation[] = 'ulid';
            if ('boolean' === $column['type_name']) $validation[] = 'boolean';
            if ('password' === $column['name']) $validation[] = Password::default();
            if ('email' === $column['name']) {
                $validation[] = 'email';
                $validation[] = $update ? 'email' : 'unique:'.$model->getTable().',email';
            }
            $validations[$column['name']] = array_values($validation);
        }, Schema::getColumns($model->getTable()));
        return $validations;
    }

    private function inputs($default = null) {
        $inputs = [];
        array_map(function ($column) use (&$inputs, $default) {
            $attrs = array_flip(explode(',', (config('administration.models')[$this->model]['columns'][$column['name']] ?? '')));
            if(isset($attrs['non-writable'])) return;
            $props = [ 'required' => !$column['nullable'], 'type' => $column['type_name'] ];
            if($default) {
                $props['default'] = $default[$column['name']];
                if(isset($attrs['disabled'])) $props['disabled'] = true;
            }
            $inputs[$column['name']] = $props;
        }, Schema::getColumns((new $this->model)->getTable()));
        if($default) $inputs['id'] = ['required' => false, 'type' => 'hidden', 'default' => $default['id']];
        return $inputs;
    }
}

