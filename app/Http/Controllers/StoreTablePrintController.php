<?php

namespace App\Http\Controllers;

use App\Models\StoreTable;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StoreTablePrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $tables = StoreTable::query()
            ->with('store')
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->where('is_active', true)
            ->orderBy('store_id')
            ->orderBy('name')
            ->get()
            ->map(function (StoreTable $table) {
                return [
                    'table' => $table,
                    'svg' => QrCode::size(260)->generate($table->menuUrl()),
                ];
            });

        return view('store-tables.print', ['tables' => $tables]);
    }
}
