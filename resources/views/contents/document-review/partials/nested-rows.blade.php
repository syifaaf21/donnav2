<table class="table modern-table align-middle text-center table-hover mb-0">
    {{-- <thead class="table-light">
        <tr>
            <th></th>
            <th>#</th>
            <th>Document Name</th>
            <th>Document Number</th>
            <th>Part Number</th>
            <th>File</th>
            <th>Department</th>
            <th>Reminder Date</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Version</th>
            <th>Notes</th>
            <th>Updated By</th>
            <th>Action</th>
        </tr>
    </thead> --}}
    <tbody>
        @php
            $parents = $documents->filter(fn($doc) => is_null($doc->document->parent_id));
        @endphp

        @foreach ($parents as $index => $parent)
            @include('contents.document-review.partials.nested-row-recursive', [
                'mapping' => $parent,
                'documents' => $documents,
                'loopIndex' => 'parent-' . $index,
                'rowNumber' => $loop->iteration
            ])

        @endforeach
    </tbody>
</table>

{{-- Script untuk toggle icon --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".toggle-children").forEach(function(btn) {
            btn.addEventListener("click", function() {
                const icon = this.querySelector("i");
                if (this.getAttribute("aria-expanded") === "true") {
                    icon.classList.remove("bi-dash-square");
                    icon.classList.add("bi-plus-square");
                } else {
                    icon.classList.remove("bi-plus-square");
                    icon.classList.add("bi-dash-square");
                }
            });
        });
    });
</script>
