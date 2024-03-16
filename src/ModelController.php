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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = $this->getModel($request);
        $columns = array_flip(Schema::getColumnListing((new $model)->getTable()));
        foreach (config('administration.models')[$model]['columns'] as $key => $value) {
            $props = array_flip(explode(',', $value));
            if(isset($props['hidden'])) unset($columns[$key]);
        }
        $options = [
            'new' => isset(config('administration.models')[$model]['new']) ? config('administration.models')[$model]['new'] : true,
            'edit' => isset(config('administration.models')[$model]['edit']) ? config('administration.models')[$model]['edit'] : true,
            'delete' => isset(config('administration.models')[$model]['delete']) ? config('administration.models')[$model]['delete'] : true
        ];
        return Inertia::render('Admin/Model/Index', [
            'columns' => array_keys($columns),
            'data' => $model::all(array_keys($columns)),
            'options' => $options
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $modelName = $this->getModel($request);
        $name = explode('\\', $modelName);
        $name = array_pop($name);
        $inputs = $this->inputs($modelName);

        return Inertia::render(config('administration.models')[$modelName]['view']['create'] ?? 'Admin/Model/Create', compact('inputs', 'name'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : RedirectResponse
    {
        $modelName = $this->getModel($request);
        $modelKeyName = explode('\\', $modelName)[array_key_last(explode('\\', $modelName))];
        $model = new $modelName;
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
        return redirect()->route(explode('.', $request->route()->getName())[0].'.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $modelName = $this->getModel($request);
        $modelKeyName = explode('\\', $modelName)[array_key_last(explode('\\', $modelName))];
        return Inertia::render($modelKeyName.'/Show', $modelName::find($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $modelName = $this->getModel($request);
        $name = explode('\\', $modelName);
        $name = array_pop($name);
        $inputs = $this->inputs($modelName, $modelName::find($id));

        return Inertia::render(config('administration.models')[$modelName]['view']['create'] ?? 'Admin/Model/Create', compact('inputs', 'name'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id) : RedirectResponse
    {
        $modelName = $this->getModel($request);
        $modelKeyName = explode('\\', $modelName)[array_key_last(explode('\\', $modelName))];
        $model = $modelName::find($id);
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
        return redirect()->route(explode('.', $request->route()->getName())[0].'.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id) : RedirectResponse
    {
        $model = (new ($this->getModel($request)))->find($id);
        $model->delete();
        return back();
    }

    private function getModel(Request $request)
    {
        foreach (config('administration.menu.models') as $key => $value) {
            if( explode('.', $request->route()->getName())[0] === str_replace(' ', '-', strtolower($key)) ) return $value;
        }
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

    private function inputs($modelName, $default = null) {
        $model = new $modelName;
        $inputs = [];
        array_map(function ($column) use (&$inputs, &$modelName, $default) {
            $attrs = array_flip(explode(',', (config('administration.models')[$modelName]['columns'][$column['name']] ?? '')));
            if(isset($attrs['non-writable'])) return;
            $props = [ 'required' => !$column['nullable'], 'type' => $column['type_name'] ];
            if($default) {
                $props['default'] = $default[$column['name']];
                if(isset($attrs['disabled'])) $props['disabled'] = true;
            }
            $inputs[$column['name']] = $props;
        }, Schema::getColumns($model->getTable()));
        if($default) $inputs['id'] = ['required' => false, 'type' => 'hidden', 'default' => $default['id']];
        return $inputs;
    }
}

