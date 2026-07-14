<x-app-layout>
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-download icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    Snelstart Exports
                    <div class="page-title-subheading">
                        Overview of all daily Snelstart exports
                    </div>
                </div>
            </div>
        </div>
    </div>            
    <div class="main-card mb-3 card">
        <div class="card-body">
            <table style="width: 100%;" class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Export Date</th>
                        <th>Filename</th>
                        <th>Bookings</th>
                        <th>Total Price/Day</th>
                        <th>Total Price/Week</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr>
                            <td>{{ $export->export_date->format('d-m-Y') }}</td>
                            <td>{{ $export->filename }}</td>
                            <td>{{ $export->booking_count }}</td>
                            <td>€ {{ number_format($export->total_price, 2, ',', '.') }}</td>
                            <td>€ {{ number_format($export->total_price_per_week, 2, ',', '.') }}</td>
                            <td>{{ $export->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('export.download', $export) }}" class="btn btn-sm btn-primary">
                                    <i class="pe-7s-download"></i> Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No exports found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $exports->links() }}
            </div>
        </div>
    </div>
</div>
</x-app-layout>
