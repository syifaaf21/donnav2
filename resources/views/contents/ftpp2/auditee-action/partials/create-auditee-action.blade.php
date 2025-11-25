<input type="hidden" name="audit_finding_id" x-model="selectedId">
<input type="hidden" name="action" value="update_auditee_action">
<input type="hidden" name="pic" value="{{ auth()->user()->id }}">
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">
<div @if ($readonly) class="opacity-70 pointer-events-none select-none" @endif>
    <div class="grid grid-cols-1 md:grid-cols-[1fr_1.6fr] gap-4 my-2">
        <!-- LEFT: 5 WHY -->
        <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
            <div class="font-semibold text-lg text-gray-900">AUDITEE</div>

            <div>
                <label class="font-semibold text-gray-900">Issue Causes (5 Why)</label>

                <template x-for="i in 5">
                    <div class="mt-2 space-y-2">
                        <div class="flex flex-col space-y-1">
                            <label class="text-gray-600">Why-<span x-text="i"></span> (Mengapa):</label>
                            <input type="text" name="why[]"
                                class="w-full border-b border-gray-400 p-2 focus:ring-2 focus:ring-blue-400"
                                x-model="form['why_'+i+'_mengapa']">

                            <label class="text-gray-600">Cause (Karena):</label>
                            <input type="text" name="cause[]"
                                class="w-full border-b border-gray-400 p-2 focus:ring-2 focus:ring-blue-400"
                                x-model="form['cause_'+i+'_karena']">
                        </div>
                    </div>
                </template>

                <div class="mt-4">
                    <label class="font-semibold text-gray-900">Root Cause <span class="text-red-500">*</span></label>
                    <textarea name="root_cause" x-model="form.root_cause"
                        class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-blue-400" required></textarea>
                </div>
            </div>
        </div>
        <!-- RIGHT COLUMN: Corrective + Preventive + Yokoten -->
        <div class="space-y-6">

            <!-- Corrective + Preventive -->
            <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                <table class="w-full border border-gray-200 text-sm mt-2">

                    <tr class="bg-gray-100 font-semibold text-center">
                        <td class="border border-gray-200 p-1 w-8">No</td>
                        <td class="border border-gray-200 p-1">Activity</td>
                        <td class="border border-gray-200 p-1 w-20">PIC</td>
                        <td class="border border-gray-200 p-1 w-20">Planning</td>
                        <td class="border border-gray-200 p-1 w-20">Actual</td>
                    </tr>

                    <!-- Corrective -->
                    <tr>
                        <td colspan="5" class="border border-gray-200 p-1 font-semibold">Corrective Action</td>
                    </tr>

                    <template x-for="i in 4">
                        <tr class="corrective-row">
                            <td class="border border-gray-200 text-center" x-text="i"></td>
                            <td class="border border-gray-200">
                                <input type="text" name="activity[]" class="w-full p-1 border-none"
                                    x-model="form['corrective_'+i+'_activity']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="text" name="pic[]" class="w-full p-1 border-none"
                                    x-model="form['corrective_'+i+'_pic']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" name="planning_date[]" class="w-full p-1 border-none"
                                    x-model="form['corrective_'+i+'_planning']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" name="actual_date[]" class="w-full p-1 border-none"
                                    x-model="form['corrective_'+i+'_actual']">
                            </td>
                        </tr>
                    </template>

                    <!-- Preventive -->
                    <tr>
                        <td colspan="5" class="border border-gray-200 p-1 font-semibold">Preventive Action</td>
                    </tr>

                    <template x-for="i in 4">
                        <tr class="preventive-row">
                            <td class="border border-gray-200 text-center" x-text="i"></td>
                            <td class="border border-gray-200">
                                <input type="text" name="activity[]" class="w-full p-1 border-none"
                                    x-model="form['preventive_'+i+'_activity']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="text" name="pic[]" class="w-full p-1 border-none"
                                    x-model="form['preventive_'+i+'_pic']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" name="planning_date[]" class="w-full p-1 border-none"
                                    x-model="form['preventive_'+i+'_planning']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" name="actual_date[]" class="w-full p-1 border-none"
                                    x-model="form['preventive_'+i+'_actual']">
                            </td>
                        </tr>
                    </template>
                </table>
            </div>


            <!-- Yokoten -->
            <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                <div class="font-semibold text-lg text-gray-900">Yokoten</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="font-semibold text-gray-900">Yokoten? <span class="text-danger">*</span></label>
                        <div class="flex gap-6">
                            <label><input type="radio" name="yokoten" value="1" x-model="form.yokoten">
                                Yes</label>
                            <label><input type="radio" name="yokoten" value="0" x-model="form.yokoten">
                                No</label>
                        </div>
                    </div>
                </div>

                <div x-show="form.yokoten == 1">
                    <label class="font-semibold text-gray-900">Please Specify:</label>
                    <textarea name="yokoten_area" x-model="form.yokoten_area" class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
                </div>

                @php
                    $action = $finding?->auditeeAction;
                    $actionId = $action?->id ?? 'null';
                @endphp

                <!-- Attachments -->
                <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                    <div class="font-semibold text-lg text-gray-900">Attachments</div>
                    <p class="text-sm text-gray-400">Only PDF, png, jpg, and jpeg files are allowed.</p>
                    <div>
                        <div id="previewImageContainer2" class="flex flex-wrap gap-2"></div>
                        <div id="previewFileContainer2" class="flex flex-col gap-1"></div>

                        <!-- Attachment button (paperclip) -->
                        <div class="relative inline-block">
                            <!-- Attachment button -->
                            <button id="attachBtn2" type="button"
                                class="items-center gap-2 px-4 py-2 border rounded-lg text-gray-900 hover:bg-gray-100 focus:outline-none"
                                title="Attach files">
                                <i data-feather="paperclip" class="w-4 h-4"></i>
                                <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                            </button>

                            <!-- Attachment Menu -->
                            <div id="attachMenu2"
                                class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow-lg z-20">
                                <button id="attachImages2" type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                    <i data-feather="image" class="w-4 h-4"></i>
                                    <span class="text-sm">Upload Images</span>
                                </button>
                                <button id="attachDocs2" type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                    <i data-feather="file-text" class="w-4 h-4"></i>
                                    <span class="text-sm">Upload Documents</span>
                                </button>
                                <div class="border-t mt-1"></div>
                                <button id="attachBoth2" type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                    <i data-feather="upload" class="w-4 h-4"></i>
                                    <span class="text-sm">Open Combined Picker</span>
                                </button>
                            </div>

                            <!-- Hidden file inputs -->
                            <input type="file" id="photoInput2" name="photos2[]" accept="image/*" multiple
                                class="hidden">
                            <input type="file" id="fileInput2" name="files2[]" accept=".pdf" multiple
                                class="hidden">
                            <input type="file" id="combinedInput2" name="attachments2[]" accept="image/*,.pdf"
                                multiple class="hidden">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Leader/SPV -->
            <div class="p-4 bg-gray-50 border border-gray-300 rounded-md text-center max-w-xs">
                <div>Created</div>

                {{-- Tampilkan stamp jika ldr_spv_signature = 1 --}}
                @if ($action && $action->ldr_spv_signature == 1)
                    <img src="/images/usr-approve.png" class="mx-auto h-24">
                @else
                    {{-- Jika belum approve, tombol tetap muncul --}}
                    <button type="button" class="px-3 py-1 bg-blue-600 text-white text-xs rounded"
                        @click="confirmApprove()">
                        Approve
                    </button>
                @endif

                <div class="mb-1 font-semibold text-gray-900">Leader / SPV</div>

                <input type="text" value="{{ auth()->user()->name }}"
                    class="w-full border border-gray-300 rounded text-center py-1" readonly>
            </div>



        </div>

    </div>
</div>
<!-- Attachment Upload Handle Script: trigger inputs, preview, count, click-outside -->
<script>
    const attachBtn2 = document.getElementById('attachBtn2');
    const attachMenu2 = document.getElementById('attachMenu2');
    const attachImages2 = document.getElementById('attachImages2');
    const attachDocs2 = document.getElementById('attachDocs2');
    const attachBoth2 = document.getElementById('attachBoth2');
    const attachCount2 = document.getElementById('attachCount2');

    const photoInput2 = document.getElementById('photoInput2');
    const fileInput2 = document.getElementById('fileInput2');
    const combinedInput2 = document.getElementById('combinedInput2');

    const previewImageContainer2 = document.getElementById('previewImageContainer2');
    const previewFileContainer2 = document.getElementById('previewFileContainer2');

    // 🔹 Helper update file list setelah dihapus
    function updatefileInput2(input, filesArray2) {
        const dt = new DataTransfer();
        filesArray2.forEach(file2 => dt.items.add(file2));
        input.files = dt.files;
    }

    // 🔹 Update badge total attachment
    function updateAttachCount2() {
        const total = (photoInput2.files?.length || 0) + (fileInput2.files?.length || 0);
        if (total > 0) {
            attachCount2.textContent = total;
            attachCount2.classList.remove('hidden');
        } else {
            attachCount2.classList.add('hidden');
        }
    }

    // 🔹 Preview Image + tombol delete
    function displayImages2() {
        previewImageContainer2.innerHTML = '';
        Array.from(photoInput2.files).forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "relative";

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = "w-24 h-24 object-cover border rounded";

            const btn = document.createElement('button');
            btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
            btn.className = "absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs";
            btn.onclick = () => {
                const newFiles2 = Array.from(photoInput2.files);
                newFiles2.splice(index, 1);
                updatefileInput2(photoInput2, newFiles2);
                displayImages2();
                updateAttachCount2();
            };

            wrapper.appendChild(img);
            wrapper.appendChild(btn);
            previewImageContainer2.appendChild(wrapper);
            feather.replace();
        });
    }

    // 🔹 Preview File + tombol delete
    function displayFiles2() {
        previewFileContainer2.innerHTML = '';
        Array.from(fileInput2.files).forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "flex items-center gap-2 text-sm border p-2 rounded";

            const icon = document.createElement('i');
            icon.setAttribute('data-feather', 'file-text');

            const name = document.createElement('span');
            name.textContent = file.name;

            const btn = document.createElement('button');
            btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
            btn.className = "ml-auto bg-red-600 text-white rounded-full p-1 text-xs";
            btn.onclick = () => {
                const newFiles = Array.from(fileInput2.files);
                newFiles.splice(index, 1);
                updatefileInput2(fileInput2, newFiles);
                displayFiles2();
                updateAttachCount2();
            };

            wrapper.append(icon, name, btn);
            previewFileContainer2.appendChild(wrapper);
            feather.replace();
        });
    }

    // 🔹 Event Listener Input
    photoInput2.addEventListener('change', () => {
        displayImages2();
        updateAttachCount2();
    });
    attachBoth2

    fileInput2.addEventListener('change', () => {
        displayFiles2();
        updateAttachCount2();
    });

    // 🔹 Combined Input (Pisahkan otomatis jadi image vs file)
    combinedInput2.addEventListener('change', (e) => {
        const images = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
        const docs = Array.from(e.target.files).filter(f => !f.type.startsWith('image/'));
        updatefileInput2(photoInput2, [...Array.from(photoInput2.files), ...images]);
        updatefileInput2(fileInput2, [...Array.from(fileInput2.files), ...docs]);
        displayImages2();
        displayFiles2();
        updateAttachCount2();
    });

    // 🔹 Toggle menu
    attachBtn2.addEventListener('click', (e) => {
        e.stopPropagation();
        attachMenu2.classList.toggle('hidden');
    });

    document.addEventListener('click', () => attachMenu2.classList.add('hidden'));

    attachImages2.addEventListener('click', () => photoInput2.click());
    attachDocs2.addEventListener('click', () => fileInput2.click());
    attachBoth2.addEventListener('click', () => combinedInput2.click());
</script>

{{-- Store data auditee action handler --}}
<script>
    async function confirmApprove() {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to save this data?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save it!'
        });

        if (result.isConfirmed) {
            // wait for save; updateAuditeeAction will redirect on success
            await updateAuditeeAction(true);
        }
    }

    async function updateAuditeeAction(isApprove = false) {

        // -----------------------------
        // VALIDATION (BLOCK SAVE)
        // -----------------------------

        const auditFindingId = document.querySelector('input[name="audit_finding_id"]')?.value;
        const rootCause = document.querySelector('textarea[x-model="form.root_cause"]')?.value || '';
        const yokotenChosen = document.querySelector('input[name="yokoten"]:checked');
        const yokotenVal = yokotenChosen ? yokotenChosen.value : null;
        const yokotenArea = document.querySelector('textarea[name="yokoten_area"]')?.value || '';

        let err = [];

        if (!auditFindingId) err.push("Audit Finding ID is required.");
        if (!rootCause.trim()) err.push("Root Cause cannot be empty.");
        if (yokotenVal === null) err.push("Yokoten selection is required.");
        if (yokotenVal == "1" && !yokotenArea.trim()) {
            err.push("Yokoten Area must be filled when Yokoten = Yes.");
        }

        if (err.length > 0) {
            await Swal.fire({
                icon: 'error',
                title: 'Required Fields Missing',
                html: err.join("<br>"),
            });
            return; // ⛔ STOP submit
        }
        
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData();

        formData.append('_token', token);
        formData.append('_method', 'POST'); // WAJIB!
        formData.append('action', 'update_auditee_action');

        // Jika ini approve, kasih flag ke backend
        if (isApprove) {
            formData.append('approve_ldr_spv', 1);
        }

        // Get ID Finding
        const findingId = document.querySelector('input[name="audit_finding_id"]')?.value;
        formData.append('audit_finding_id', findingId);

        // Get PIC
        formData.append('pic', document.querySelector('input[name="pic"]')?.value);

        // -----------------------------
        // 5 WHY
        // -----------------------------
        const whyInputs = document.querySelectorAll('input[name="why[]"]');
        const causeInputs = document.querySelectorAll('input[name="cause[]"]');

        for (let i = 0; i < 5; i++) {
            formData.append(`why_${i+1}_mengapa`, whyInputs[i]?.value || '');
            formData.append(`cause_${i+1}_karena`, causeInputs[i]?.value || '');
        }

        // Root cause
        formData.append('root_cause', document.querySelector('textarea[x-model="form.root_cause"]')?.value || '');

        // -----------------------------
        // Corrective Action (1–4)
        // -----------------------------
        document.querySelectorAll('tr.corrective-row').forEach((row, i) => {
            const activity = row.querySelector('input[name="activity[]"]')?.value || '';
            const pic = row.querySelector('input[name="pic[]"]')?.value || '';
            const planning = row.querySelector('input[name="planning_date[]"]')?.value || '';
            const actual = row.querySelector('input[name="actual_date[]"]')?.value || '';

            formData.append(`corrective_${i+1}_activity`, activity);
            formData.append(`corrective_${i+1}_pic`, pic);
            formData.append(`corrective_${i+1}_planning`, planning);
            formData.append(`corrective_${i+1}_actual`, actual);
        });

        // -----------------------------
        // Preventive Action (1–4)
        // -----------------------------
        document.querySelectorAll('tr.preventive-row').forEach((row, i) => {
            const activity = row.querySelector('input[name="activity[]"]')?.value || '';
            const pic = row.querySelector('input[name="pic[]"]')?.value || '';
            const planning = row.querySelector('input[name="planning_date[]"]')?.value || '';
            const actual = row.querySelector('input[name="actual_date[]"]')?.value || '';

            formData.append(`preventive_${i+1}_activity`, activity);
            formData.append(`preventive_${i+1}_pic`, pic);
            formData.append(`preventive_${i+1}_planning`, planning);
            formData.append(`preventive_${i+1}_actual`, actual);
        });

        // -----------------------------
        // Yokoten
        // -----------------------------
        const yokoten = document.querySelector('input[name="yokoten"]:checked');
        formData.append('yokoten', yokoten ? yokoten.value : 0);

        formData.append('yokoten_area', document.querySelector('textarea[name="yokoten_area"]')?.value || '');

        // -----------------------------
        // Attachments
        // -----------------------------
        const photoInput2 = document.getElementById('photoInput2');
        const fileInput2 = document.getElementById('fileInput2');

        Array.from(photoInput2?.files || []).forEach(file => formData.append('attachments[]', file));
        Array.from(fileInput2?.files || []).forEach(file => formData.append('attachments[]', file));

        // -----------------------------
        // Submit
        // -----------------------------
        try {
            const res = await fetch("{{ route('ftpp.auditee-action.store', ['id' => $finding->id]) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const result = await res.json();

            if (res.ok && result.success) {
                console.log(result);
                // Redirect to index after successful save
                window.location.href = "{{ route('ftpp.index') }}";
            } else {
                alert("❌ Failed: " + (result.message || "Unknown error"));
            }
        } catch (err) {
            console.error(err);
            alert("❌ Error: " + err.message);
        }
    }
</script>
