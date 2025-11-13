<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        return view('procurement.index');
    }
}