<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        return view('master-data.customers.index');
    }

    public function ajax()
    {
        $customers = Customer::query();

        if ($search = request('search.value')) {
            $customers->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $customers->where('status', $filterStatus);
        }

        if ($filterType = request('filter_type')) {
            $customers->where('type', $filterType);
        }

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function ($customer) {
                return view('master-data.customers.action', compact('customer'))->render();
            })
            ->editColumn('type', function ($customer) {
                return $customer->type === 'company'
                    ? '<span class="badge bg-info">Perusahaan</span>'
                    : '<span class="badge bg-secondary">Individu</span>';
            })
            ->editColumn('status', function ($customer) {
                return match ($customer->status) {
                    'active'    => '<span class="badge bg-success">Aktif</span>',
                    'inactive'  => '<span class="badge bg-warning">Nonaktif</span>',
                    'blacklist' => '<span class="badge bg-danger">Blacklist</span>',
                    default     => $customer->status,
                };
            })
            ->editColumn('credit_limit', fn($c) => number_format($c->credit_limit, 2))
            ->editColumn('balance', fn($c) => number_format($c->balance, 2))
            ->editColumn('created_at', fn($c) => $c->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'type', 'status'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255|unique:customers,email',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:100',
            'credit_limit'   => 'nullable|numeric|min:0',
            'type'           => 'required|in:company,individual',
            'status'         => 'required|in:active,inactive,blacklist',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('CUST', 'customers');
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

            $customer = Customer::create($validated);

            ActivityLogger::log(
                'Customer',
                'create',
                "Pelanggan {$customer->name} berhasil dibuat",
                'customer',
                $customer->id,
                $customer->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.customers.index')
                ->with('success', 'Pelanggan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['quotations', 'salesOrders', 'salesInvoices', 'payments', 'interactions']);

        $quotations     = Quotation::where('customer_id', $customer->id)->latest()->take(10)->get();
        $salesOrders    = SalesOrder::where('customer_id', $customer->id)->latest()->take(10)->get();
        $salesInvoices  = SalesInvoice::where('customer_id', $customer->id)->latest()->take(10)->get();
        $payments       = Payment::where('customer_id', $customer->id)->latest()->take(10)->get();
        $interactions   = CustomerInteraction::where('customer_id', $customer->id)->latest()->take(20)->get();

        return view('master-data.customers.show', compact(
            'customer',
            'quotations',
            'salesOrders',
            'salesInvoices',
            'payments',
            'interactions'
        ));
    }

    public function edit(Customer $customer)
    {
        return view('master-data.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:100',
            'credit_limit'   => 'nullable|numeric|min:0',
            'type'           => 'required|in:company,individual',
            'status'         => 'required|in:active,inactive,blacklist',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
            $customer->update($validated);

            ActivityLogger::log(
                'Customer',
                'update',
                "Pelanggan {$customer->name} berhasil diperbarui",
                'customer',
                $customer->id,
                $customer->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.customers.index')
                ->with('success', 'Pelanggan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        DB::beginTransaction();
        try {
            $customerName = $customer->name;
            $customer->delete();

            ActivityLogger::log(
                'Customer',
                'delete',
                "Pelanggan {$customerName} berhasil dihapus",
                'customer',
                $customer->id
            );

            DB::commit();

            return redirect()->route('master-data.customers.index')
                ->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }
}
