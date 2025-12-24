<!-- filepath: d:\Sanya\donnav2\resources\views\contents\document-review\partials\modal-download-report.blade.php -->
<!-- Modal Download Report -->
<div class="modal fade" id="downloadReportModal" tabindex="-1" aria-labelledby="downloadReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl shadow-lg">
            <div class="modal-header bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-t-2xl border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-download me-3 fs-5"></i>
                    <h5 class="modal-title font-semibold mb-0" id="downloadReportModalLabel">
                        Download Report
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-5">
                <!-- Loading State -->
                <div id="downloadReportLoading" class="text-center py-8">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-gray-600 fw-500">Loading download history...</p>
                </div>

                <!-- Content State -->
                <div id="downloadReportContent" class="hidden">
                    <!-- Info Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark text-blue-600 fs-4 me-3"></i>
                                    <div>
                                        <small class="text-gray-600 d-block">Document Number</small>
                                        <span id="reportDocNumber" class="h6 mb-0 text-gray-900">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-arrow-down-circle text-green-600 fs-4 me-3"></i>
                                    <div>
                                        <small class="text-gray-600 d-block">Total Downloads</small>
                                        <span id="reportTotalDownloads" class="h6 mb-0 text-green-600 fw-bold">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table with Fixed Layout -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover mb-0" style="table-layout: fixed; width: 100%;">
                                {{-- <colgroup>
                                    <col style="width: 60px;">
                                    <col style="width: auto;">
                                    <col style="width: 120px;">
                                </colgroup> --}}
                                <thead class="bg-gray-100" style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase w-16">No</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase">
                                            <i class="bi bi-person me-2"></i>User
                                        </th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase text-center w-32">
                                            <i class="bi bi-download me-2"></i>Count
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="downloadReportTableBody" class="divide-y divide-gray-200">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="downloadReportEmpty" class="hidden text-center py-12">
                        <i class="bi bi-inbox text-6xl text-gray-300 d-block mb-3"></i>
                        <p class="text-gray-500 fw-500">No download history found</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

