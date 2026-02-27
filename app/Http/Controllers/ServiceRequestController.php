<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ServiceRequestController extends Controller
{
    // Показ формы создания заявки (публичная часть)
    public function create()
    {
        return Inertia::render('Welcome');
    }

    // Сохранение новой заявки
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'problem_text' => 'required|string',
        ]);

        ServiceRequest::create($validated);

        // В Inertia флеш-сообщения передаются через сессию, на фронте их можно будет поймать через page.props.flash
        return redirect()->route('home')->with('success', 'Заявка успешно отправлена!');
    }

    // Дашборд: список заявок
    public function index()
    {
        $user = Auth::user();

        // Жадная загрузка мастера, чтобы избежать N+1 запросов
        $query = ServiceRequest::with('master')->latest();

        // Логика видимости: диспетчер видит всё, мастер - только новые и свои
        if ($user->role === 'master') {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'new')
                  ->orWhere('assigned_to', $user->id);
            });
        }

        return Inertia::render('Dashboard', [
            'requests' => $query->get(),
        ]);
    }

    // Взять заявку в работу (КРИТИЧЕСКИЙ УЧАСТОК - защита от Race Condition)
    public function takeToWork(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        if ($user->role !== 'master') {
            return back()->withErrors(['error' => 'Только мастера могут брать заявки в работу.']);
        }

        // DB::transaction возвращает результат замыкания, поэтому return пишем прямо перед ним
        return DB::transaction(function () use ($serviceRequest, $user) {
            // Запрашиваем запись заново с блокировкой (SELECT ... FOR UPDATE)
            // Никакой другой процесс не сможет изменить эту строку, пока транзакция не завершится
            $lockedRequest = ServiceRequest::where('id', $serviceRequest->id)->lockForUpdate()->first();

            // Если кто-то успел забрать заявку до того, как мы получили лок
            if ($lockedRequest->status !== 'new') {
                return back()->withErrors(['conflict' => 'Не успели! Заявка уже взята в работу другим мастером или отменена.']); // Inertia обработает как 422/ошибку валидации
            }

            // Назначаем заявку
            $lockedRequest->update([
                'status' => 'assigned',
                'assigned_to' => $user->id,
            ]);

            return back()->with('success', 'Вы успешно взяли заявку в работу.');
        });
    }

    // Изменение статуса заявки
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,assigned,in_progress,done,canceled',
        ]);

        $user = Auth::user();

        // Базовая защита: мастер не может менять статус чужой заявки (по-хорошему тут нужна Policy)
        if ($user->role === 'master' && $serviceRequest->assigned_to !== $user->id) {
            abort(403, 'Вы не можете изменять статус чужой заявки.');
        }

        $serviceRequest->update(['status' => $validated['status']]);

        return back()->with('success', 'Статус заявки обновлен.');
    }
}