<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Adds $this->authorize('ability', $model) to every controller.
    use AuthorizesRequests;
}
