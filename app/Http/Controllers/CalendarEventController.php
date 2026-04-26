<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarEventController extends Controller
{
    public function index(): View
    {
        $events = CalendarEvent::visibleFor(auth()->user())->latest()->get();

        return view('calendar.index', compact('events'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'all_day' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['all_day'] = $request->boolean('all_day');
        $validated['color'] = $validated['color'] ?? '#2563eb';

        CalendarEvent::create($validated);

        return redirect()->route('calendar.index')->with('success', 'Evenement cree.');
    }

    public function edit(CalendarEvent $calendarEvent): View
    {
        $this->authorize('update', $calendarEvent);

        return view('calendar.edit', compact('calendarEvent'));
    }

    public function update(Request $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $this->authorize('update', $calendarEvent);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'all_day' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['all_day'] = $request->boolean('all_day');
        $validated['color'] = $validated['color'] ?? $calendarEvent->color;

        $calendarEvent->update($validated);

        return redirect()->route('calendar.index')->with('success', 'Evenement mis a jour.');
    }

    public function destroy(CalendarEvent $calendarEvent): RedirectResponse
    {
        $this->authorize('delete', $calendarEvent);

        $calendarEvent->delete();

        return redirect()->route('calendar.index')->with('success', 'Evenement supprime.');
    }
}
