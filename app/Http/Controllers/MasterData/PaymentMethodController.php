<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('master-data.payment-methods.index');
    }

    public function ajax()
    {
        $methods = PaymentMethod::query();

        if ($search = request('search.value')) {
            $methods->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $methods->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($methods)
            ->addIndexColumn()
            ->addColumn('action', function ($method) {
                return view('master-data.payment-methods.action', compact('method'))->render();
            })
            ->editColumn('is_active', function ($method) {
                return $method->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($m) => $m->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:payment_methods,code',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $method = PaymentMethod::create($validated);

            ActivityLogger::log(
                'PaymentMethod',
                'create',
                "Metode pembayaran {$method->name} berhasil dibuat",
                'payment_method',
                $method->id,
                $method->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan metode pembayaran: ' . $e->getMessage());
        }
    }

    public function show(PaymentMethod $paymentMethod)
    {
        return view('master-data.payment-methods.show', compact('paymentMethod'));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('master-data.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => ['required', 'string', 'max:50', Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id)],
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $paymentMethod->update($validated);

            ActivityLogger::log(
                'PaymentMethod',
                'update',
                "Metode pembayaran {$paymentMethod->name} berhasil diperbarui",
                'payment_method',
                $paymentMethod->id,
                $paymentMethod->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui metode pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        DB::beginTransaction();
        try {
            $methodName = $paymentMethod->name;
            $paymentMethod->delete();

            ActivityLogger::log(
                'PaymentMethod',
                'delete',
                "Metode pembayaran {$methodName} berhasil dihapus",
                'payment_method',
                $paymentMethod->id
            );

            DB::commit();

            return redirect()->route('master-data.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }
}
