<x-filament-panels::page>
    <div 
        x-data="bulkPricingTable(@js($gridData))"
        class="flex flex-col h-[calc(100vh-150px)] w-full gap-4"
        wire:ignore
    >
        <!-- Standard Toolbar -->
        <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-600">Bulk Product Manager</span>
                <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500 font-mono" x-text="rowData.length + ' Records'"></span>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-xs font-mono" :class="{'text-green-600 font-bold': status.includes('SAVED'), 'text-amber-600': status.includes('SYNCING')}">
                    <span x-text="status"></span>
                </div>
            </div>
        </div>

        <!-- The Grid -->
        <!-- Using ag-theme-quartz which is the new modern default -->
        <div 
            id="myGrid" 
            style="height: 75vh; width: 100%;"
            class="ag-theme-quartz rounded-lg shadow-sm border border-gray-200 overflow-hidden"
            x-init="initGrid"
        ></div>
    </div>

    @push('scripts')
        <!-- Load AG Grid v31.0.0 (Stable & Compatible) -->
        <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/dist/ag-grid-community.min.js"></script>
        
        <script>
            // Store definitions injected from PHP
            window.bulkStoreColumns = @json($storeColumns);

            document.addEventListener('alpine:init', () => {
                Alpine.data('bulkPricingTable', (initialData) => ({
                    gridApi: null,
                    status: 'Ready',
                    changesBuffer: [],
                    saveTimeout: null,
                    rowData: initialData,

                    initGrid() {
                        const storeCols = window.bulkStoreColumns || [];
                        
                        // Construct Columns dynamically
                        const columnDefs = [
                            { 
                                headerName: 'Product Info',
                                pinned: 'left',
                                children: [
                                    { 
                                        headerName: 'Category', 
                                        field: 'category_name', 
                                        width: 140, 
                                        filter: true,
                                        pinned: 'left',
                                        cellClass: 'font-bold bg-gray-50'
                                    },
                                    { 
                                        headerName: 'Product (Portion)', 
                                        colId: 'product_full_name', // Virtual column ID
                                        width: 260, 
                                        filter: true, 
                                        pinned: 'left',
                                        valueGetter: params => {
                                            if (!params.data) return '';
                                            return `${params.data.product_name} (${params.data.portion_name})`;
                                        },
                                        cellRenderer: params => {
                                            if (!params.data) return '';
                                            return `
                                                <div class="flex items-center h-full">
                                                    <span class="font-semibold text-gray-900">${params.data.product_name}</span>
                                                    <span class="text-xs text-gray-500 ml-1">(${params.data.portion_name})</span>
                                                </div>
                                            `;
                                        }
                                    },
                                ]
                            },
                            {
                                headerName: 'Store Prices (₺)',
                                children: storeCols.map(store => ({
                                    headerName: store.name,
                                    field: `store_${store.id}`,
                                    width: 110,
                                    editable: true,
                                    type: 'numericColumn',
                                    valueFormatter: p => p.value ? '₺ ' + Number(p.value).toFixed(2) : '-',
                                    cellClass: (params) => {
                                        // Green if price exists, Light Red if missing
                                        return params.value ? 'bg-green-50 text-green-800 font-bold' : 'bg-red-50 text-red-300 italic';
                                    }
                                }))
                            }
                        ];

                        const gridOptions = {
                            rowData: this.rowData,
                            columnDefs: columnDefs,
                            defaultColDef: {
                                sortable: true,
                                resizable: true,
                                filter: true,
                            },
                            rowSelection: 'multiple',
                            rowHeight: 48,
                            headerHeight: 48,
                            
                            onGridReady: (params) => {
                                setTimeout(() => params.api.sizeColumnsToFit(), 100);
                            },
                            onCellValueChanged: (params) => this.handleEdit(params)
                        };

                        const gridDiv = document.getElementById('myGrid');
                        
                        if (typeof agGrid !== 'undefined') {
                            this.gridApi = agGrid.createGrid(gridDiv, gridOptions);
                        } else {
                            console.error('AG Grid library not loaded');
                        }
                    },

                    handleEdit(params) {
                        const newValue = params.newValue;
                        const oldValue = params.oldValue;
                        if (newValue == oldValue) return;

                        this.status = 'Saving...';
                        
                        // Extract Store ID from "store_1"
                        const field = params.colDef.field;
                        const storeId = field.replace('store_', '');

                        this.changesBuffer.push({
                            product_id: params.data.product_id,
                            portion_name: params.data.portion_name,
                            store_id: storeId,
                            field: field,
                            newValue: Number(newValue)
                        });

                        if (this.saveTimeout) clearTimeout(this.saveTimeout);
                        
                        this.saveTimeout = setTimeout(() => {
                            this.$wire.batchUpdate(this.changesBuffer)
                                .then(() => {
                                    this.status = 'All changes saved ✅';
                                    this.changesBuffer = [];
                                    setTimeout(() => this.status = 'Ready', 2000);
                                });
                        }, 800);
                    }
                }));
            });
        </script>

        <!-- AG Grid v31 Styles -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-grid.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-theme-quartz.css" />
    @endpush
</x-filament-panels::page>
