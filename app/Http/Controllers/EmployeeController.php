<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkAccess()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index()
    {
        $this->checkAccess();
        
        $employees = Employee::where('role', '!=', 'Admin')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        $this->checkAccess();
        return view('employees.show', compact('employee'));
    }

    public function create()
    {
        $this->checkAccess();
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:20|unique:employees,phone',
            'role' => 'required|in:Manager,Operator,Helper,Finance',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'Active'
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully');
    }

    public function edit(Employee $employee)
    {
        $this->checkAccess();
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->checkAccess();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'required|string|max:20|unique:employees,phone,' . $employee->id,
            'role' => 'required|in:Manager,Operator,Helper,Finance',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Employee updated successfully');
    }
}
