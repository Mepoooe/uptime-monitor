<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\CheckDomainJob;
use App\Models\Domain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends BaseController
{
    use AuthorizesRequests;

    public function index(): View
    {
        $domains = auth()->user()
            ->domains()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('domains.index', compact('domains'));
    }

    public function create(): View
    {
        return view('domains.create', [
            'intervals' => Domain::$intervals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'name' => ['nullable', 'string', 'max:255'],
            'check_method' => ['required', 'in:GET,HEAD'],
            'check_interval' => ['required', 'integer', 'in:'.implode(',', Domain::$intervals)],
            'check_timeout' => ['required', 'integer', 'min:1', 'max:60'],
            'is_active' => ['boolean'],
        ]);

        auth()->user()->domains()->create($data);

        return redirect()->route('domains.index')
            ->with('success', 'Domain added and queued for first check.');
    }

    public function edit(Domain $domain): View
    {
        $this->authorize('update', $domain);

        return view('domains.edit', [
            'domain' => $domain,
            'intervals' => Domain::$intervals,
        ]);
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);

        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'name' => ['nullable', 'string', 'max:255'],
            'check_method' => ['required', 'in:GET,HEAD'],
            'check_interval' => ['required', 'integer', 'in:'.implode(',', Domain::$intervals)],
            'check_timeout' => ['required', 'integer', 'min:1', 'max:60'],
            'is_active' => ['boolean'],
        ]);

        $domain->update($data);

        return redirect()->route('domains.index')
            ->with('success', 'Domain updated.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        $this->authorize('delete', $domain);
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'Domain deleted.');
    }

    public function checkNow(Domain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);
        CheckDomainJob::dispatch($domain->getKey());

        return back()->with('success', 'Check queued.');
    }

    public function logs(Domain $domain): View
    {
        $this->authorize('view', $domain);

        $logs = $domain->checkLogs()->paginate(50);

        return view('domains.logs', compact('domain', 'logs'));
    }
}
