<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        Announcement::create([
            'type' => 'announcement',
            'message' => $request->message,
            'is_active' => $request->status === 'active',
            'status' => $request->status === 'active' ? 'active' : 'inactive',
            'performed_by' => Auth::user()->first_name . ' ' . Auth::user()->surname,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $announcement->update([
            'message' => $request->message,
            'is_active' => $request->status === 'active',
            'status' => $request->status === 'active' ? 'active' : 'inactive',
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement deleted successfully.');
    }

    public function toggleStatus(Announcement $announcement)
    {
        $newStatus = !$announcement->is_active;
        $announcement->update([
            'is_active' => $newStatus,
            'status' => $newStatus ? 'active' : 'inactive',
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}
