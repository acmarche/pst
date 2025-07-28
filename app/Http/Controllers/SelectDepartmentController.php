<?php

namespace App\Http\Controllers;

use App\Repository\UserRepository;
use Illuminate\Http\RedirectResponse;

class SelectDepartmentController extends Controller
{
    public function select(string $department): RedirectResponse
    {
        session()->put(UserRepository::$department_selected_key, $department);

        return redirect('/admin');
    }
}
