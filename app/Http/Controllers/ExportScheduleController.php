<?php

namespace App\Http\Controllers;

use App\Models\ExportSchedule;
use Illuminate\Http\Request;

class ExportScheduleController extends Controller
{
    public function index()
    {
        $schedules = ExportSchedule::orderBy('id', 'desc')->get();

        return view('export_schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('export_schedules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'filter_type' => 'required|in:last_month,last_7_days,custom',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
            'frequency'   => 'required|in:every_1_minute,every_10_minutes,hourly,daily',
            'is_active'   => 'nullable|in:on,1,0,true,false',
        ]);

        $data['is_active'] = $request->has('is_active');

        ExportSchedule::create($data);

        return redirect()->route('export-schedules.index')
            ->with('success', 'Export schedule created.');
    }
}
