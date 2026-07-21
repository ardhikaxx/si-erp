<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerInteractionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $interactions = CustomerInteraction::with(['customer', 'creator']);

            if ($search = request('search.value')) {
                $interactions->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%");
                });
            }

            if ($filterCustomer = request('filter_customer')) {
                $interactions->where('customer_id', $filterCustomer);
            }

            if ($filterType = request('filter_type')) {
                $interactions->where('type', $filterType);
            }

            return DataTables::of($interactions)
                ->addIndexColumn()
                ->addColumn('action', function ($interaction) {
                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<a href="' . route('crm.interactions.edit', $interaction->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button onclick="deleteConfirm(' . $interaction->id . ', \'Interaksi\')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('customer_id', fn($i) => $i->customer?->name ?? '-')
                ->editColumn('type', function ($interaction) {
                    return match ($interaction->type) {
                        'call'    => '<span class="badge bg-primary">Telepon</span>',
                        'email'   => '<span class="badge bg-info">Email</span>',
                        'meeting' => '<span class="badge bg-warning">Meeting</span>',
                        'visit'   => '<span class="badge bg-success">Kunjungan</span>',
                        'note'    => '<span class="badge bg-secondary">Catatan</span>',
                        'other'   => '<span class="badge bg-dark">Lainnya</span>',
                        default   => $interaction->type,
                    };
                })
                ->editColumn('status', function ($interaction) {
                    return match ($interaction->status) {
                        'planned'  => '<span class="badge bg-info">Direncanakan</span>',
                        'done'     => '<span class="badge bg-success">Selesai</span>',
                        'cancelled'=> '<span class="badge bg-danger">Dibatalkan</span>',
                        default    => $interaction->status,
                    };
                })
                ->editColumn('interaction_date', fn($i) => $i->interaction_date ? $i->interaction_date->format('d/m/Y') : '-')
                ->editColumn('description', fn($i) => strlen($i->description) > 100 ? substr($i->description, 0, 100) . '...' : $i->description)
                ->editColumn('created_at', fn($i) => $i->created_at->format('d/m/Y H:i'))
                ->rawColumns(['action', 'type', 'status'])
                ->make(true);
        }

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        return view('crm.interactions.index', compact('customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $interaction = new CustomerInteraction();
        return view('crm.interactions.form', compact('interaction', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'type'            => 'required|in:call,email,meeting,visit,note,other',
            'description'     => 'nullable|string',
            'interaction_date'=> 'nullable|date',
            'status'          => 'required|in:planned,done,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = auth()->id();

            $interaction = CustomerInteraction::create($validated);

            ActivityLogger::log(
                'CustomerInteraction',
                'create',
                "Interaksi dengan customer {$interaction->customer?->name} berhasil dibuat",
                'customer_interaction',
                $interaction->id,
                $interaction->toArray()
            );

            DB::commit();

            return redirect()->route('crm.interactions.index')
                ->with('success', 'Interaksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan interaksi: ' . $e->getMessage());
        }
    }

    public function edit(CustomerInteraction $interaction)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        return view('crm.interactions.form', compact('interaction', 'customers'));
    }

    public function update(Request $request, CustomerInteraction $interaction)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'type'            => 'required|in:call,email,meeting,visit,note,other',
            'description'     => 'nullable|string',
            'interaction_date'=> 'nullable|date',
            'status'          => 'required|in:planned,done,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $interaction->update($validated);

            ActivityLogger::log(
                'CustomerInteraction',
                'update',
                "Interaksi dengan customer {$interaction->customer?->name} berhasil diperbarui",
                'customer_interaction',
                $interaction->id,
                $interaction->toArray()
            );

            DB::commit();

            return redirect()->route('crm.interactions.index')
                ->with('success', 'Interaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui interaksi: ' . $e->getMessage());
        }
    }

    public function destroy(CustomerInteraction $interaction)
    {
        DB::beginTransaction();
        try {
            $customerName = $interaction->customer?->name ?? '-';
            $interaction->delete();

            ActivityLogger::log(
                'CustomerInteraction',
                'delete',
                "Interaksi dengan customer {$customerName} berhasil dihapus",
                'customer_interaction',
                $interaction->id
            );

            DB::commit();

            return redirect()->route('crm.interactions.index')
                ->with('success', 'Interaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus interaksi: ' . $e->getMessage());
        }
    }
}
