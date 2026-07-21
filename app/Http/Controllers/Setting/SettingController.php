<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        $companies = Company::orderBy('name')->get();
        return view('settings.settings.index', compact('settings', 'companies'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'    => 'required|string|max:255',
            'company_id'  => 'nullable|exists:companies,id',
            'timezone'    => 'required|string|max:100',
            'currency'    => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'general', 'description' => "Pengaturan {$key}"]
                );
            }

            ActivityLogger::log(
                'Setting',
                'update',
                'Pengaturan sistem berhasil diperbarui',
                'setting',
                null,
                $validated
            );

            DB::commit();

            return redirect()->route('settings.settings.index')
                ->with('success', 'Pengaturan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }
}
