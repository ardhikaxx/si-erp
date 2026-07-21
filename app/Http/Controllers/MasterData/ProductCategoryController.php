<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('master-data.product-categories.index');
    }

    public function ajax()
    {
        $categories = ProductCategory::query();

        if ($search = request('search.value')) {
            $categories->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $categories->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('action', function ($category) {
                return view('master-data.product-categories.action', compact('category'))->render();
            })
            ->editColumn('is_active', function ($category) {
                return $category->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($c) => $c->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.product-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:product_categories,code',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $category = ProductCategory::create($validated);

            ActivityLogger::log(
                'ProductCategory',
                'create',
                "Kategori produk {$category->name} berhasil dibuat",
                'product_category',
                $category->id,
                $category->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.product-categories.index')
                ->with('success', 'Kategori produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan kategori produk: ' . $e->getMessage());
        }
    }

    public function show(ProductCategory $productCategory)
    {
        return view('master-data.product-categories.show', compact('productCategory'));
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('master-data.product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => ['required', 'string', 'max:50', Rule::unique('product_categories', 'code')->ignore($productCategory->id)],
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $productCategory->update($validated);

            ActivityLogger::log(
                'ProductCategory',
                'update',
                "Kategori produk {$productCategory->name} berhasil diperbarui",
                'product_category',
                $productCategory->id,
                $productCategory->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.product-categories.index')
                ->with('success', 'Kategori produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui kategori produk: ' . $e->getMessage());
        }
    }

    public function destroy(ProductCategory $productCategory)
    {
        DB::beginTransaction();
        try {
            $categoryName = $productCategory->name;
            $productCategory->delete();

            ActivityLogger::log(
                'ProductCategory',
                'delete',
                "Kategori produk {$categoryName} berhasil dihapus",
                'product_category',
                $productCategory->id
            );

            DB::commit();

            return redirect()->route('master-data.product-categories.index')
                ->with('success', 'Kategori produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus kategori produk: ' . $e->getMessage());
        }
    }
}
