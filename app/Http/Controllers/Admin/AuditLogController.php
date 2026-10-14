<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();
        if ($request->filled('q')) $query->where('action', 'like', '%'.$request->string('q').'%');
        if ($request->filled('user_id')) $query->where('user_id', $request->integer('user_id'));
        if ($request->filled('date')) $query->whereDate('created_at', $request->date('date'));
        return view('admin.audit-logs.index', ['logs' => $query->paginate(30)->withQueryString(), 'users' => User::orderBy('name')->get()]);
    }
}
