{{-- 5 WHY --}}
<input type="hidden" name="audit_finding_id" x-model="selectedId">
<input type="hidden" name="action" value="save_auditee_action">
<input type="hidden" name="pic" value="{{ auth()->user()->id }}">
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">
<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold">
        <td class="border border-black p-1">AUDITEE</td>
    </tr>
    <tr>
        <td class="border border-black p-1">
            <div class="font-semibold mb-1">Issue Causes (5 Why)</div>
            <template x-for="i in 5">
                <div class="ml-2 mb-1">
                    <label>Why-<span x-text="i"></span> (Mengapa):</label>
                    <input type="text" name="why[]" class="w-full border-b border-gray-400 p-1"
                        x-model="form['why_'+i+'_mengapa']">
                    <label>Cause (Karena):</label>
                    <input type="text" name="cause[]" class="w-full border-b border-gray-400 p-1"
                        x-model="form['cause_'+i+'_karena']">
                </div>
            </template>
            <div class="mt-1">
                Root Cause: <span class="text-danger">*</span>
                <textarea name="root_cause" x-model="form.root_cause" class="w-full border border-gray-400 rounded p-1" required></textarea>
            </div>
        </td>
    </tr>
</table>

{{-- TINDAKAN KOREKSI & PERBAIKAN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr class="bg-gray-100 font-semibold text-center">
        <td class="border border-black p-1 w-8">No</td>
        <td class="border border-black p-1">Activity</td>
        <td class="border border-black p-1 w-32">PIC</td>
        <td class="border border-black p-1 w-28">Planning</td>
        <td class="border border-black p-1 w-28">Actual</td>
    </tr>

    {{-- Koreksi --}}
    <tr>
        <td colspan="5" class="border border-black p-1 font-semibold">Corrective Action</td>
    </tr>
    <template x-for="i in 4">
        <tr class="corrective-row">
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black">
                <input name="activity[]" type="text" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_activity']">
            </td>
            <td class="border border-black">
                <input name="pic[]" type="text" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_pic']">
            </td>
            <td class="border border-black">
                <input name="planning_date[]" type="date" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_planning']">
            </td>
            <td class="border border-black">
                <input name="actual_date[]" type="date" class="w-full border-none p-1"
                    x-model="form['corrective_'+i+'_actual']">
            </td>
        </tr>
    </template>

    {{-- Perbaikan --}}
    <tr>
        <td colspan="5" class="border border-black p-1 font-semibold">Preventive Action</td>
    </tr>
    <template x-for="i in 4">
        <tr class="preventive-row">
            <td class="border border-black text-center" x-text="i"></td>
            <td class="border border-black">
                <input name="activity[]" type="text" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_activity']">
            </td>
            <td class="border border-black">
                <input name="pic[]" type="text" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_pic']">
            </td>
            <td class="border border-black">
                <input name="planning_date[]" type="date" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_planning']">
            </td>
            <td class="border border-black">
                <input name="actual_date[]" type="date" class="w-full border-none p-1"
                    x-model="form['preventive_'+i+'_actual']">
            </td>
        </tr>
    </template>
</table>

{{-- YOKOTEN --}}
<table class="w-full border border-black text-sm mt-2">
    <tr>
        <td class="border border-black p-2 w-2/3">
            <label>Yokoten?<span class="text-danger">*</span></label>
            <label><input type="radio" name="yokoten" value="1" x-model="form.yokoten" required> Yes</label>
            <label><input type="radio" name="yokoten" value="0" x-model="form.yokoten" required> No</label>
        </td>
        <td class="border border-black p-1 font-semibold text-center">Checked</td>
        <td class="border border-black p-1 font-semibold text-center">Created</td>
    </tr>
    <tr>
        <td>
            <label>Please specify:</label>
            <textarea name="yokoten_area" x-show="form.yokoten == 1" x-model="form.yokoten_area"
                class="w-full border border-gray-400 rounded p-2 h-24"></textarea>
        </td>
        @include('contents.ftpp.partials.signature')
    </tr>
</table>

<table>
    <tr>
        <td>
            <!-- Preview containers (sesuaikan posisi di form) -->
            <div id="previewImageContainer2" class="mt-2 flex flex-wrap gap-2"></div>
            <div id="previewFileContainer2" class="mt-2 flex flex-col gap-1"></div>

            <!-- Attachment button (paperclip) -->
            <div class="relative inline-block">
                <button id="attachBtn2" type="button"
                    class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                    aria-haspopup="true" aria-expanded="false" title="Attach files2">
                    <i data-feather="paperclip" class="w-4 h-4"></i>
                    <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                </button>

                <!-- Small menu seperti email (hidden, muncul saat klik) -->
                <div id="attachMenu2" class="hidden absolute left-0 mt-2 w-40 bg-white border rounded shadow z-20">
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
            </div>

            <!-- Hidden file inputs -->
            <input type="file" id="photoInput2" name="photos2[]" accept="image/*" multiple class="hidden">
            <input type="file" id="fileInput2" name="files2[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple
                class="hidden">
            <!-- Optional combined input -->
            <input type="file" id="combinedInput2" name="attachments2[]"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
        </td>
    </tr>
</table>

{{-- button --}}
<div class="flex justify-end mt-2">
    <button type="button" onclick="saveAuditeeAction()"
        class="ml-auto mt-2 bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">Save Auditee
        Action
    </button>
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
    async function saveAuditeeAction() {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('action', 'save_auditee_action');

        // Get audit_finding_id
        const selectedIdEl = document.querySelector('input[name="audit_finding_id"]');
        const selectedId = selectedIdEl ? selectedIdEl.value : '';
        if (!selectedId) {
            alert("Error: ID audit belum dipilih!");
            return;
        }
        formData.append('audit_finding_id', selectedId);
        formData.append('pic', document.querySelector('input[name="pic"]').value || '');

        // Collect 5 WHY inputs
        const whyInputs = document.querySelectorAll('input[name="why[]"]');
        const causeInputs = document.querySelectorAll('input[name="cause[]"]');

        for (let i = 0; i < 5; i++) {
            formData.append(`why_${i+1}_mengapa`, whyInputs[i]?.value || '');
            formData.append(`cause_${i+1}_karena`, causeInputs[i]?.value || '');
        }

        // Root cause
        const rootCauseEl = document.querySelector('textarea[name="root_cause"]');
        formData.append('root_cause', rootCauseEl ? rootCauseEl.value : '');

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

        const yokotenEl = document.querySelector('input[name="yokoten"]:checked');
        const yokotenValue = yokotenEl ? Number(yokotenEl.value) : 0; // konversi ke number
        formData.append('yokoten', yokotenValue);

        const yokotenAreaEl = document.querySelector('textarea[name="yokoten_area"]');
        formData.append('yokoten_area', yokotenAreaEl ? yokotenAreaEl.value : '');

        // Attachments
        const photoInput2 = document.getElementById('photoInput2');
        const fileInput2 = document.getElementById('fileInput2');
        Array.from(photoInput2.files || []).forEach(file => formData.append('attachments[]', file));
        Array.from(fileInput2.files || []).forEach(file => formData.append('attachments[]', file));

        try {
            const res = await fetch("{{ route('ftpp.store') }}", {
                method: "POST",
                body: formData
            });
            const result = await res.json();
            if (res.ok && result.success) {
                alert('✅ Auditee action berhasil disimpan!');
                console.log(result);
            } else {
                alert('❌ Gagal menyimpan data: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error: ' + error.message);
        }
    }

    // save and sign
    async function saveAndSign(auditeeActionId) {
        if (!auditeeActionId) {
            alert('Gagal mendapatkan auditee_action_id!');
            return;
        }

        const deptHead = document.getElementById('dept_head_signature')?.value;
        const ldrSpv = document.getElementById('ldr_spv_signature')?.value;

        if (!deptHead && !ldrSpv) {
            alert('Tanda tangan belum diisi!');
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('auditee_action_id', auditeeActionId);

        let url = '';
        if (ldrSpv) {
            url = "{{ route('ftpp.ldr-spv-sign') }}";
            formData.append('ldr_spv_signature', ldrSpv);
        } else if (deptHead) {
            url = "{{ route('ftpp.dept-head-sign') }}";
            formData.append('dept_head_signature', deptHead);
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (res.ok && result.success) {
                alert('✅ Signature berhasil disimpan!');
            } else {
                alert('❌ Gagal menyimpan signature: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            console.error(err);
            alert('❌ Error: ' + err.message);
        }
    }
</script>
