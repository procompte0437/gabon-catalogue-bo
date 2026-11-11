<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'appName' => Setting::appName(),
            'whatsapp' => Setting::whatsappDisplay(),
            'masterCode' => Setting::masterCode(),
        ]);
    }

    public function update(Request $request)
    {
        $request->merge([
            'app_name' => trim((string) $request->input('app_name', '')),
            'whatsapp_display' => trim((string) $request->input('whatsapp_display', '')),
            'master_code' => trim((string) $request->input('master_code', '')),
        ]);

        $rules = [
            'app_name' => ['required', 'string', 'max:40'],
            'whatsapp_display' => ['required', 'string', 'max:30', 'regex:/\d/'],
            'master_code' => ['required', 'regex:/^\d{4}$/'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'string', 'min:4', 'confirmed'];
        }

        $data = $request->validate($rules, [
            'app_name.required' => 'Le nom du catalogue est obligatoire.',
            'app_name.max' => 'Le nom ne peut pas dépasser 40 caractères.',
            'whatsapp_display.required' => 'Le numéro WhatsApp est obligatoire.',
            'whatsapp_display.regex' => 'Le numéro WhatsApp doit contenir des chiffres.',
            'master_code.required' => 'Le mot de passe général est obligatoire.',
            'master_code.regex' => 'Le mot de passe général doit contenir exactement 4 chiffres.',
            'current_password.required' => 'Le mot de passe actuel est obligatoire pour le changer.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 4 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        Setting::put('app_name', $data['app_name']);
        Setting::put('whatsapp_display', $data['whatsapp_display']);
        Setting::put('master_code', $data['master_code']);

        if ($request->filled('password')) {
            $request->user()->update([
                'password' => $request->input('password'),
            ]);
        }

        return back()->with('status', 'Réglages enregistrés.');
    }
}
