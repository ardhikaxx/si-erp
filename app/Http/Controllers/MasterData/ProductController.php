<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        return view('master-data.products.index');
    }

    public function ajax()
    {
        $products = Product::with(['category', 'unit']);

        if ($search = request('search.value')) {
            $products->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $products->where('status', $filterStatus);
        }

        if ($filterType = request('filter_type')) {
            $products->where('type', $filterType);
        }

        if ($categoryId = request('filter_category')) {
            $products->where('category_id', $categoryId);
        }

        return DataTables::of($products)
            ->addIndexColumn()
            ->addColumn('action', function ($product) {
                return view('master-data.products.action', compact('product'))->render();
            })
            ->addColumn('category_name', fn($p) => $p->category?->name)
            ->addColumn('unit_name', fn($p) => $p->unit?->name)
            ->editColumn('purchase_price', fn($p) => number_format($p->purchase_price, 2))
            ->editColumn('selling_price', fn($p) => number_format($p->selling_price, 2))
            ->editColumn('current_stock', fn($p) => number_format($p->current_stock, 2))
            ->editColumn('type', function ($product) {
                return $product->type === 'product'
                    ? '<span class="badge bg-primary">Produk</span>'
                    : '<span class="badge bg-info">Jasa</span>';
            })
            ->editColumn('status', function ($product) {
                return match ($product->status) {
                    'active'       => '<span class="badge bg-success">Aktif</span>',
                    'inactive'     => '<span class="badge bg-warning">Nonaktif</span>',
                    'discontinued' => '<span class="badge bg-danger">Discontinued</span>',
                    default        => $product->status,
                };
            })
            ->editColumn('created_at', fn($p) => $p->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'type', 'status'])
            ->make(true);
    }

    public function create()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $units      = ProductUnit::where('is_active', true)->get();
        return view('master-data.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'nullable|string|max:100|unique:products,sku',
            'barcode'        => 'nullable|string|max:100',
            'category_id'    => 'nullable|exists:product_categories,id',
            'unit_id'        => 'nullable|exists:product_units,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'stock_min'      => 'nullable|numeric|min:0',
            'stock_max'      => 'nullable|numeric|min:0',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'description'    => 'nullable|string',
            'type'           => 'required|in:product,service',
            'status'         => 'required|in:active,inactive,discontinued',
        ]);

        DB::beginTransaction();
        try {
            $validated['code']           = DocumentNumber::generateSimple('PROD', 'products');
            $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
            $validated['selling_price']  = $validated['selling_price'] ?? 0;
            $validated['stock_min']      = $validated['stock_min'] ?? 0;
            $validated['stock_max']      = $validated['stock_max'] ?? 0;
            $validated['current_stock']  = 0;

            $product = Product::create($validated);

            ActivityLogger::log(
                'Product',
                'create',
                "Produk {$product->name} berhasil dibuat",
                'product',
                $product->id,
                $product->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.products.index')
                ->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'supplier', 'warehouse']);
        $inventoryMovements = $product->inventoryMovements()
            ->with('warehouse')
            ->latest()
            ->paginate(20);

        return view('master-data.products.show', compact('product', 'inventoryMovements'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $units      = ProductUnit::where('is_active', true)->get();
        return view('master-data.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode'        => 'nullable|string|max:100',
            'category_id'    => 'nullable|exists:product_categories,id',
            'unit_id'        => 'nullable|exists:product_units,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'stock_min'      => 'nullable|numeric|min:0',
            'stock_max'      => 'nullable|numeric|min:0',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'description'    => 'nullable|string',
            'type'           => 'required|in:product,service',
            'status'         => 'required|in:active,inactive,discontinued',
        ]);

        DB::beginTransaction();
        try {
            $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
            $validated['selling_price']  = $validated['selling_price'] ?? 0;
            $validated['stock_min']      = $validated['stock_min'] ?? 0;
            $validated['stock_max']      = $validated['stock_max'] ?? 0;

            $product->update($validated);

            ActivityLogger::log(
                'Product',
                'update',
                "Produk {$product->name} berhasil diperbarui",
                'product',
                $product->id,
                $product->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.products.index')
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $productName = $product->name;
            $product->delete();

            ActivityLogger::log(
                'Product',
                'delete',
                "Produk {$productName} berhasil dihapus",
                'product',
                $product->id
            );

            DB::commit();

            return redirect()->route('master-data.products.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } else {
                $rows = array_map('str_getcsv', file($file->getPathname()));
            }

            $header = array_shift($rows);
            $imported = 0;

            foreach ($rows as $row) {
                $data = array_combine($header, $row);

                $data['code'] = DocumentNumber::generateSimple('PROD', 'products');
                $data['purchase_price'] = $data['purchase_price'] ?? 0;
                $data['selling_price']  = $data['selling_price'] ?? 0;
                $data['stock_min']      = $data['stock_min'] ?? 0;
                $data['stock_max']      = $data['stock_max'] ?? 0;
                $data['current_stock']  = 0;
                $data['type']           = $data['type'] ?? 'product';
                $data['status']         = $data['status'] ?? 'active';

                if (!empty($data['category_name'])) {
                    $category = ProductCategory::firstOrCreate(
                        ['name' => $data['category_name']],
                        ['code' => strtoupper(substr(str_slug($data['category_name']), 0, 10)), 'is_active' => true]
                    );
                    $data['category_id'] = $category->id;
                }

                if (!empty($data['unit_name'])) {
                    $unit = ProductUnit::firstOrCreate(
                        ['name' => $data['unit_name']],
                        ['code' => strtoupper(substr(str_slug($data['unit_name']), 0, 10)), 'is_active' => true]
                    );
                    $data['unit_id'] = $unit->id;
                }

                Product::create($data);
                $imported++;
            }

            ActivityLogger::log(
                'Product',
                'import',
                "{$imported} produk berhasil diimpor",
                'product',
                null,
                ['count' => $imported]
            );

            DB::commit();

            return redirect()->route('master-data.products.index')
                ->with('success', "{$imported} produk berhasil diimpor.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengimpor produk: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $products = Product::with(['category', 'unit'])->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Kode', 'Nama', 'SKU', 'Barcode', 'Kategori', 'Satuan',
            'Harga Beli', 'Harga Jual', 'Stok Min', 'Stok Max', 'Stok Saat Ini',
            'Tipe', 'Status',
        ];

        foreach (range('A', 'M') as $i => $col) {
            $sheet->setCellValue("{$col}1", $headers[$i]);
            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
        }

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue("A{$row}", $product->code);
            $sheet->setCellValue("B{$row}", $product->name);
            $sheet->setCellValue("C{$row}", $product->sku);
            $sheet->setCellValue("D{$row}", $product->barcode);
            $sheet->setCellValue("E{$row}", $product->category?->name);
            $sheet->setCellValue("F{$row}", $product->unit?->name);
            $sheet->setCellValue("G{$row}", $product->purchase_price);
            $sheet->setCellValue("H{$row}", $product->selling_price);
            $sheet->setCellValue("I{$row}", $product->stock_min);
            $sheet->setCellValue("J{$row}", $product->stock_max);
            $sheet->setCellValue("K{$row}", $product->current_stock);
            $sheet->setCellValue("L{$row}", $product->type);
            $sheet->setCellValue("M{$row}", $product->status);
            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'produk_' . now()->format('Ymd_His') . '.xlsx';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition'       => "attachment; filename=\"{$filename}\"",
                'Cache-Control'             => 'max-age=0',
            ]
        );
    }

    public function exportTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'name', 'sku', 'barcode', 'category_name', 'unit_name',
            'purchase_price', 'selling_price', 'stock_min', 'stock_max',
            'type', 'status', 'description',
        ];

        foreach (range('A', 'L') as $i => $col) {
            $sheet->setCellValue("{$col}1", $headers[$i]);
            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
        }

        $sheet->setCellValue('A2', 'Contoh Produk');
        $sheet->setCellValue('E2', 'Kategori Contoh');
        $sheet->setCellValue('F2', 'Unit Contoh');
        $sheet->setCellValue('G2', 10000);
        $sheet->setCellValue('H2', 15000);
        $sheet->setCellValue('J2', 'product');
        $sheet->setCellValue('K2', 'active');

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $filename = 'template_produk.csv';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]
        );
    }
}
