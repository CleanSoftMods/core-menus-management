<?php namespace CleanSoft\Modules\Core\Menu\Http\Requests;

use CleanSoft\Modules\Core\Http\Requests\Request;

class UpdateMenuRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'string|max:255|required',
            'slug' => 'string|max:255|nullable|unique:' . webed_db_prefix() . 'menus,slug,' . request()->route()->parameter('id'),
            'status' => 'required',
            'menu_structure' => 'required',
            'deleted_nodes' => 'required'
        ];
    }
}
