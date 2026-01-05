<style>
    .rte-toolbar {
        display: flex;
        gap: 4px;
        padding: 4px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-bottom: none;
        border-radius: 4px 4px 0 0;
    }
    .rte-toolbar button {
        padding: 4px 8px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
        transition: all 0.2s;
    }
    .rte-toolbar button:hover {
        background: #e5e7eb;
    }
    .rte-toolbar button.active {
        background: #3b82f6;
        color: white;
        border-color: #2563eb;
    }
    .rte-editor {
        min-height: 60px;
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 0 0 4px 4px;
        background: white;
        outline: none;
    }
    .rte-editor:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .rte-container {
        width: 100%;
    }
</style>

<input type="hidden" name="audit_finding_id" x-model="selectedId">
<input type="hidden" name="action" value="update_auditee_action">
<input type="hidden" name="pic" value="{{ auth()->user()->id }}">
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">
<div @if ($readonly) class="opacity-70 pointer-events-none select-none" @endif
    x-data="{ whyCount: 1, correctiveCount: 1, preventiveCount: 1 }">
    <div class="gap-4 my-2">
        <!-- LEFT: 5 WHY -->
        <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-4">
            <h5 class="font-semibold text-gray-700">AUDITEE</h5>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-semibold text-medium text-gray-700">Issue Causes (5 Why)</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="whyCount > 1 && whyCount--"
                            class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                            title="Remove Why Row">
                            -
                        </button>
                        <button type="button" @click="whyCount < 5 && whyCount++"
                            class="bg-green-500 hover:bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                            title="Add Why Row">
                            +
                        </button>
                    </div>
                </div>

                <template x-for="i in whyCount" :key="i">
                    <div class="mt-2 space-y-2">
                        <div class="mt-4 p-4 border-l-4 border-blue-500 bg-blue-50 rounded">
                            <h6 class="font-semibold text-blue-900 mb-3">WHY-<span x-text="i"></span></h6>
                            <div class="flex flex-col space-y-1">
                                <label class="text-gray-700">Why (Mengapa):</label>
                                <div class="rte-container">
                                    <div class="rte-toolbar">
                                        <button type="button" onclick="formatText(this, 'bold')" title="Bold"><b>B</b></button>
                                        <button type="button" onclick="formatText(this, 'italic')" title="Italic"><i>I</i></button>
                                        <button type="button" onclick="formatText(this, 'underline')" title="Underline"><u>U</u></button>
                                    </div>
                                    <div class="rte-editor" contenteditable="true" 
                                        :data-field="'why_' + i + '_mengapa'"
                                        @input="updateHiddenField($event.target)"
                                        x-html="form['why_'+i+'_mengapa'] || ''"></div>
                                    <textarea :name="'why_' + i + '_mengapa'" class="hidden" 
                                        x-model="form['why_'+i+'_mengapa']"></textarea>
                                </div>

                                <label class="text-gray-700 mt-2">Cause (Karena):</label>
                                <div class="rte-container">
                                    <div class="rte-toolbar">
                                        <button type="button" onclick="formatText(this, 'bold')" title="Bold"><b>B</b></button>
                                        <button type="button" onclick="formatText(this, 'italic')" title="Italic"><i>I</i></button>
                                        <button type="button" onclick="formatText(this, 'underline')" title="Underline"><u>U</u></button>
                                    </div>
                                    <div class="rte-editor" contenteditable="true" 
                                        :data-field="'cause_' + i + '_karena'"
                                        @input="updateHiddenField($event.target)"
                                        x-html="form['cause_'+i+'_karena'] || ''"></div>
                                    <textarea :name="'cause_' + i + '_karena'" class="hidden" 
                                        x-model="form['cause_'+i+'_karena']"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="mt-4">
                    <label class="font-semibold text-gray-900">Root Cause <span class="text-red-500">*</span></label>
                    <div class="rte-container">
                        <div class="rte-toolbar">
                            <button type="button" onclick="formatText(this, 'bold')" title="Bold"><b>B</b></button>
                            <button type="button" onclick="formatText(this, 'italic')" title="Italic"><i>I</i></button>
                            <button type="button" onclick="formatText(this, 'underline')" title="Underline"><u>U</u></button>
                        </div>
                        <div class="rte-editor" contenteditable="true" 
                            data-field="root_cause"
                            @input="updateHiddenField($event.target)"
                            x-html="form.root_cause || ''"></div>
                        <textarea name="root_cause" class="hidden" x-model="form.root_cause" required></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- RIGHT COLUMN: Corrective + Preventive + Yokoten -->
        <div class="space-y-6">

            <!-- Corrective + Preventive -->
            <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6 mt-4">
                <table class="w-full border border-gray-200 text-sm mt-2">

                    <tr class="bg-gray-100 font-semibold text-center">
                        <td class="border border-gray-200 p-1 w-8">No</td>
                        <td class="border border-gray-200 p-1">Activity</td>
                        <td class="border border-gray-200 p-1 w-32">PIC</td>
                        <td class="border border-gray-200 p-1 w-20">Planning</td>
                        <td class="border border-gray-200 p-1 w-20">Actual</td>
                    </tr>

                    <!-- Corrective -->
                    <tr>
                        <td colspan="4" class="border border-gray-200 p-1 font-semibold">Corrective Action</td>
                        <td class="border border-gray-200 p-1 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @click="correctiveCount > 1 && correctiveCount--"
                                    class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                                    title="Remove Corrective Row">
                                    -
                                </button>
                                <button type="button" @click="correctiveCount < 10 && correctiveCount++"
                                    class="bg-green-500 hover:bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                                    title="Add Corrective Row">
                                    +
                                </button>
                            </div>
                        </td>
                    </tr>

                    <template x-for="i in correctiveCount" :key="i">
                        <tr class="corrective-row">
                            <td class="border border-gray-200 text-center" x-text="i"></td>
                            <td class="border border-gray-200 p-1">
                                <div class="rte-toolbar" style="margin-bottom: 2px;">
                                    <button type="button" onclick="formatText(this, 'bold')" title="Bold" style="padding: 2px 6px; font-size: 12px;"><b>B</b></button>
                                    <button type="button" onclick="formatText(this, 'italic')" title="Italic" style="padding: 2px 6px; font-size: 12px;"><i>I</i></button>
                                    <button type="button" onclick="formatText(this, 'underline')" title="Underline" style="padding: 2px 6px; font-size: 12px;"><u>U</u></button>
                                </div>
                                <div class="rte-editor" contenteditable="true" style="min-height: 38px; border-radius: 4px;"
                                    :data-field="'corrective_' + i + '_activity'"
                                    @input="updateHiddenField($event.target)"
                                    x-html="form['corrective_'+i+'_activity'] || ''"></div>
                                <textarea :name="'corrective_' + i + '_activity'" class="hidden" 
                                    x-model="form['corrective_'+i+'_activity']"></textarea>
                            </td>
                            <td class="border border-gray-200 w-32 p-1">
                                <div class="rte-toolbar" style="margin-bottom: 2px;">
                                    <button type="button" onclick="formatText(this, 'bold')" title="Bold" style="padding: 2px 6px; font-size: 12px;"><b>B</b></button>
                                    <button type="button" onclick="formatText(this, 'italic')" title="Italic" style="padding: 2px 6px; font-size: 12px;"><i>I</i></button>
                                    <button type="button" onclick="formatText(this, 'underline')" title="Underline" style="padding: 2px 6px; font-size: 12px;"><u>U</u></button>
                                </div>
                                <div class="rte-editor" contenteditable="true" style="min-height: 38px; border-radius: 4px;"
                                    :data-field="'corrective_' + i + '_pic'"
                                    @input="updateHiddenField($event.target)"
                                    x-html="form['corrective_'+i+'_pic'] || ''"></div>
                                <textarea :name="'corrective_' + i + '_pic'" class="hidden" 
                                    x-model="form['corrective_'+i+'_pic']"></textarea>
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" :name="'corrective_' + i + '_planning'"
                                    class="w-full p-1 border-none" x-model="form['corrective_'+i+'_planning']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="text" :name="'corrective_' + i + '_actual'"
                                    class="w-full p-1 border-none" x-model="form['corrective_'+i+'_actual']"
                                    placeholder="dd/mm/yyyy or -">
                            </td>
                        </tr>
                    </template>

                    <!-- Preventive -->
                    <tr>
                        <td colspan="4" class="border border-gray-200 p-1 font-semibold">Preventive Action</td>
                        <td class="border border-gray-200 p-1 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @click="preventiveCount > 1 && preventiveCount--"
                                    class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                                    title="Remove Preventive Row">
                                    -
                                </button>
                                <button type="button" @click="preventiveCount < 10 && preventiveCount++"
                                    class="bg-green-500 hover:bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"
                                    title="Add Preventive Row">
                                    +
                                </button>
                            </div>
                        </td>
                    </tr>

                    <template x-for="i in preventiveCount" :key="i">
                        <tr class="preventive-row">
                            <td class="border border-gray-200 text-center" x-text="i"></td>
                            <td class="border border-gray-200 p-1">
                                <div class="rte-toolbar" style="margin-bottom: 2px;">
                                    <button type="button" onclick="formatText(this, 'bold')" title="Bold" style="padding: 2px 6px; font-size: 12px;"><b>B</b></button>
                                    <button type="button" onclick="formatText(this, 'italic')" title="Italic" style="padding: 2px 6px; font-size: 12px;"><i>I</i></button>
                                    <button type="button" onclick="formatText(this, 'underline')" title="Underline" style="padding: 2px 6px; font-size: 12px;"><u>U</u></button>
                                </div>
                                <div class="rte-editor" contenteditable="true" style="min-height: 38px; border-radius: 4px;"
                                    :data-field="'preventive_' + i + '_activity'"
                                    @input="updateHiddenField($event.target)"
                                    x-html="form['preventive_'+i+'_activity'] || ''"></div>
                                <textarea :name="'preventive_' + i + '_activity'" class="hidden" 
                                    x-model="form['preventive_'+i+'_activity']"></textarea>
                            </td>
                            <td class="border border-gray-200 w-32 p-1">
                                <div class="rte-toolbar" style="margin-bottom: 2px;">
                                    <button type="button" onclick="formatText(this, 'bold')" title="Bold" style="padding: 2px 6px; font-size: 12px;"><b>B</b></button>
                                    <button type="button" onclick="formatText(this, 'italic')" title="Italic" style="padding: 2px 6px; font-size: 12px;"><i>I</i></button>
                                    <button type="button" onclick="formatText(this, 'underline')" title="Underline" style="padding: 2px 6px; font-size: 12px;"><u>U</u></button>
                                </div>
                                <div class="rte-editor" contenteditable="true" style="min-height: 38px; border-radius: 4px;"
                                    :data-field="'preventive_' + i + '_pic'"
                                    @input="updateHiddenField($event.target)"
                                    x-html="form['preventive_'+i+'_pic'] || ''"></div>
                                <textarea :name="'preventive_' + i + '_pic'" class="hidden" 
                                    x-model="form['preventive_'+i+'_pic']"></textarea>
                            </td>
                            <td class="border border-gray-200">
                                <input type="date" :name="'preventive_' + i + '_planning'"
                                    class="w-full p-1 border-none" x-model="form['preventive_'+i+'_planning']">
                            </td>
                            <td class="border border-gray-200">
                                <input type="text" :name="'preventive_' + i + '_actual'"
                                    class="w-full p-1 border-none" x-model="form['preventive_'+i+'_actual']"
                                    placeholder="dd/mm/yyyy or -">
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
                    <label class="font-semibold text-gray-900">Please Specify: <span
                            class="text-danger">*</span></label>
                    <div class="rte-container">
                        <div class="rte-toolbar">
                            <button type="button" onclick="formatText(this, 'bold')" title="Bold"><b>B</b></button>
                            <button type="button" onclick="formatText(this, 'italic')" title="Italic"><i>I</i></button>
                            <button type="button" onclick="formatText(this, 'underline')" title="Underline"><u>U</u></button>
                        </div>
                        <div class="rte-editor" contenteditable="true" style="min-height: 96px;"
                            data-field="yokoten_area"
                            @input="updateHiddenField($event.target)"
                            x-html="form.yokoten_area || ''"></div>
                        <textarea name="yokoten_area" class="hidden" x-model="form.yokoten_area" 
                            :required="form.yokoten == 1"></textarea>
                    </div>
                </div>

                @php
                    $action = $finding?->auditeeAction;
                    $actionId = $action?->id ?? 'null';
                @endphp

                {{-- ATTACHMENT SECTION --}}
                <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">
                    <div class="font-semibold text-lg text-gray-700">Attachments</div>

                    {{-- Tips Alert --}}
                    <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2">
                        <i class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                            <p class="text-xs text-yellow-700 leading-relaxed">
                                Only <strong>PDF, PNG, JPG, and JPEG</strong> files are allowed.
                                Maximum total file size is <strong>20 MB</strong>.
                            </p>
                        </div>
                    </div>

                    <div>
                        <!-- Preview containers -->
                        <div id="previewImageContainer2" class="mt-2 flex flex-wrap gap-2"></div>
                        <div id="previewFileContainer2" class="mt-2 flex flex-col gap-1"></div>

                        <!-- Attachment button -->
                        <div class="relative inline-block">
                            <button id="attachBtn2" type="button"
                                class="flex items-center gap-2 px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none"
                                aria-haspopup="true" aria-expanded="false" title="Attach files">
                                <i data-feather="paperclip" class="w-4 h-4"></i>
                                <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                            </button>

                            <!-- Menu -->
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
                            </div>
                        </div>

                        <!-- Hidden file inputs -->
                        <input type="file" id="photoInput2" name="attachments[]" accept="image/*" multiple
                            class="hidden">
                        <input type="file" id="fileInput2" name="attachments[]" accept=".pdf" multiple
                            class="hidden">

                        <!-- ✅ Error message container for attachments -->
                        <div id="attachmentErrorContainer"
                            class="hidden mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded-r">
                            <div class="flex items-start">
                                <i data-feather="alert-circle"
                                    class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
                                <div id="attachmentErrorMessage" class="text-sm text-red-700"></div>
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
                        <button type="button"
                            class="px-3 py-1 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
                            @click="window.confirmApprove()">
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
</div>
<!-- Rich Text Editor Script -->
<script>
    // Format text function for rich text editor
    function formatText(button, command) {
        // Prevent form submission
        event.preventDefault();
        
        // Get the editor div (next sibling after toolbar)
        const toolbar = button.parentElement;
        const editor = toolbar.nextElementSibling;
        
        // Focus on editor
        editor.focus();
        
        // Execute formatting command
        document.execCommand(command, false, null);
        
        // Update button state
        updateToolbarState(toolbar, editor);
        
        // Trigger input event to sync with Alpine.js
        editor.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Update toolbar button states based on current selection
    function updateToolbarState(toolbar, editor) {
        const buttons = toolbar.querySelectorAll('button');
        buttons.forEach(button => {
            const command = button.getAttribute('onclick').match(/'([^']+)'/)[1];
            if (document.queryCommandState(command)) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
    
    // Alpine.js global function to update hidden field
    document.addEventListener('alpine:init', () => {
        Alpine.magic('updateHiddenField', () => {
            return (editor) => {
                const fieldName = editor.getAttribute('data-field');
                const value = editor.innerHTML;
                
                // Update form object in Alpine
                const component = Alpine.$data(editor.closest('[x-data]'));
                if (component && component.form) {
                    component.form[fieldName] = value;
                }
            };
        });
    });
    
    // Update hidden field function (fallback)
    window.updateHiddenField = function(editor) {
        const fieldName = editor.getAttribute('data-field');
        const value = editor.innerHTML;
        
        // Find and update hidden textarea
        const textarea = editor.parentElement.querySelector('textarea[name="' + fieldName + '"]');
        if (textarea) {
            textarea.value = value;
        }
        
        // Update Alpine.js form data
        const component = editor.closest('[x-data]');
        if (component && component.__x) {
            const data = component.__x.$data;
            if (data.form) {
                data.form[fieldName] = value;
            }
        }
    };
</script>

<!-- Attachment Upload Handle Script: trigger inputs, preview, count, click-outside -->
<script>
    const attachBtn2 = document.getElementById('attachBtn2');
    const attachMenu2 = document.getElementById('attachMenu2');
    const attachImages2 = document.getElementById('attachImages2');
    const attachDocs2 = document.getElementById('attachDocs2');
    const attachCount2 = document.getElementById('attachCount2');

    const photoInput2 = document.getElementById('photoInput2');
    const fileInput2 = document.getElementById('fileInput2');

    const previewImageContainer2 = document.getElementById('previewImageContainer2');
    const previewFileContainer2 = document.getElementById('previewFileContainer2');

    // 🔹 Store files accumulated from multiple selections
    let accumulatedPhotoFiles = [];
    let accumulatedFileFiles = [];

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
        accumulatedPhotoFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "relative";

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = "w-24 h-24 object-cover border rounded";

            const btn = document.createElement('button');
            btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
            btn.className = "absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs";
            btn.onclick = (e) => {
                e.preventDefault();
                accumulatedPhotoFiles.splice(index, 1);
                updatefileInput2(photoInput2, accumulatedPhotoFiles);
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
        accumulatedFileFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "flex items-center gap-2 text-sm border p-2 rounded";

            const icon = document.createElement('i');
            icon.setAttribute('data-feather', 'file-text');

            const name = document.createElement('span');
            name.textContent = file.name;

            const btn = document.createElement('button');
            btn.innerHTML = '<i data-feather="x" class="w-3 h-3"></i>';
            btn.className = "ml-auto bg-red-600 text-white rounded-full p-1 text-xs";
            btn.onclick = (e) => {
                e.preventDefault();
                accumulatedFileFiles.splice(index, 1);
                updatefileInput2(fileInput2, accumulatedFileFiles);
                displayFiles2();
                updateAttachCount2();
            };

            wrapper.append(icon, name, btn);
            previewFileContainer2.appendChild(wrapper);
            feather.replace();
        });
    }

    // 🔹 Event Listener Input - ACCUMULATE files (don't replace)
    photoInput2.addEventListener('change', (e) => {
        // Add new files to accumulated list
        const newFiles = Array.from(photoInput2.files);
        newFiles.forEach(file => {
            // Check if file already exists to avoid duplicates
            const isDuplicate = accumulatedPhotoFiles.some(f => f.name === file.name && f.size === file
                .size);
            if (!isDuplicate) {
                accumulatedPhotoFiles.push(file);
            }
        });

        // Update input with accumulated files
        updatefileInput2(photoInput2, accumulatedPhotoFiles);
        displayImages2();
        updateAttachCount2();
    });

    fileInput2.addEventListener('change', (e) => {
        // Add new files to accumulated list
        const newFiles = Array.from(fileInput2.files);
        newFiles.forEach(file => {
            // Check if file already exists to avoid duplicates
            const isDuplicate = accumulatedFileFiles.some(f => f.name === file.name && f.size === file
                .size);
            if (!isDuplicate) {
                accumulatedFileFiles.push(file);
            }
        });

        // Update input with accumulated files
        updatefileInput2(fileInput2, accumulatedFileFiles);
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
</script>

{{-- Store data auditee action handler --}}
<script>
    window.confirmApprove = async function() {
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
            await window.updateAuditeeAction(true);
        }
    };

    window.updateAuditeeAction = async function(isApprove = false) {

        // ✅ 1. Hapus error messages lama
        const errorContainer = document.getElementById('attachmentErrorContainer');
        const errorMessage = document.getElementById('attachmentErrorMessage');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
        }
        if (errorMessage) {
            errorMessage.innerHTML = '';
        }

        // ✅ 2. Function untuk tampilkan error di field attachment
        function showAttachmentError(message) {
            if (errorContainer && errorMessage) {
                errorMessage.innerHTML = message;
                errorContainer.classList.remove('hidden');

                // Re-render feather icons
                feather.replace();

                // Scroll to error
                errorContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

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

        // ✅ 2.5 VALIDASI CORRECTIVE ACTION - jika Activity diisi, maka PIC, Planning, Actual harus diisi
        const correctiveRows = document.querySelectorAll('.corrective-row');
        correctiveRows.forEach((row, index) => {
            const i = index + 1;
            const activity = document.querySelector(`textarea[name="corrective_${i}_activity"]`)?.value?.trim() || '';
            const pic = document.querySelector(`textarea[name="corrective_${i}_pic"]`)?.value?.trim() || '';
            const planning = document.querySelector(`input[name="corrective_${i}_planning"]`)?.value?.trim() || '';
            const actual = document.querySelector(`input[name="corrective_${i}_actual"]`)?.value?.trim() || '';

            if (activity) {
                if (!pic) err.push(`❌ Corrective Action Row ${i}: If Activity is filled, PIC must also be filled.`);
                if (!planning) err.push(`❌ Corrective Action Row ${i}: If Activity is filled, Planning date must also be filled.`);
                if (!actual) err.push(`❌ Corrective Action Row ${i}: If Activity is filled, Actual field must also be filled.`);
            }
        });

        // ✅ 2.6 VALIDASI PREVENTIVE ACTION - jika Activity diisi, maka PIC, Planning, Actual harus diisi
        const preventiveRows = document.querySelectorAll('.preventive-row');
        preventiveRows.forEach((row, index) => {
            const i = index + 1;
            const activity = document.querySelector(`textarea[name="preventive_${i}_activity"]`)?.value?.trim() || '';
            const pic = document.querySelector(`textarea[name="preventive_${i}_pic"]`)?.value?.trim() || '';
            const planning = document.querySelector(`input[name="preventive_${i}_planning"]`)?.value?.trim() || '';
            const actual = document.querySelector(`input[name="preventive_${i}_actual"]`)?.value?.trim() || '';

            if (activity) {
                if (!pic) err.push(`❌ Preventive Action Row ${i}: If Activity is filled, PIC must also be filled.`);
                if (!planning) err.push(`❌ Preventive Action Row ${i}: If Activity is filled, Planning date must also be filled.`);
                if (!actual) err.push(`❌ Preventive Action Row ${i}: If Activity is filled, Actual field must also be filled.`);
            }
        });

        // ✅ 3. VALIDASI TOTAL FILE SIZE (CLIENT-SIDE)
        const photoInput2 = document.getElementById('photoInput2');
        const fileInput2 = document.getElementById('fileInput2');

        let totalSize = 0;
        let fileDetails = [];

        // Hitung total size dari photos (images)
        if (photoInput2 && photoInput2.files) {
            Array.from(photoInput2.files).forEach(file => {
                totalSize += file.size;
                fileDetails.push({
                    name: file.name,
                    size: file.size,
                    type: 'image'
                });
            });
        }

        // Hitung total size dari files (PDF)
        if (fileInput2 && fileInput2.files) {
            Array.from(fileInput2.files).forEach(file => {
                totalSize += file.size;
                fileDetails.push({
                    name: file.name,
                    size: file.size,
                    type: 'pdf'
                });
            });
        }

        // Convert ke MB untuk display
        const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);

        console.log(`📊 Total file size: ${totalSize} bytes (${totalSizeMB} MB)`);
        console.log('Files:', fileDetails);

        // ✅ 4. CHECK jika melebihi 20MB - TAMPILKAN DI FIELD (BUKAN SWEETALERT)
        if (totalSize > 20 * 1024 * 1024) { // 20MB in bytes
            const errorHtml = `
                <p class="font-semibold mb-1">Total file size exceeds 20MB</p>
                <p>Current total size: <strong>${totalSizeMB} MB</strong></p>
                <p>
                    Please compress your PDF files and reupload it.
                </p>
            `;
            showAttachmentError(errorHtml);
            return; // ⛔ STOP submit
        }

        // ✅ 5. CHECK individual file size - TAMPILKAN DI FIELD (BUKAN SWEETALERT)
        let individualErrors = [];

        // Check individual image files (max 3MB)
        if (photoInput2 && photoInput2.files) {
            Array.from(photoInput2.files).forEach(file => {
                if (file.size > 3 * 1024 * 1024) { // 3MB
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    individualErrors.push(
                        `🖼️ Image "${file.name}" is ${sizeMB}MB. Maximum is 3MB per image.`);
                }
            });
        }

        // Check individual PDF files (max 10MB)
        if (fileInput2 && fileInput2.files) {
            Array.from(fileInput2.files).forEach(file => {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    individualErrors.push(`📄 PDF "${file.name}" is ${sizeMB}MB. Maximum is 10MB per PDF.`);
                }
            });
        }

        if (individualErrors.length > 0) {
            const errorHtml = `
                <p class="font-semibold mb-2">❌ Individual file size limit exceeded</p>
                <ul class="list-disc list-inside space-y-1">
                    ${individualErrors.map(e => `<li>${e}</li>`).join('')}
                </ul>
            `;
            showAttachmentError(errorHtml);
            return; // ⛔ STOP submit
        }

        // ✅ 6. Check other validation errors (tetap di SweetAlert - bukan file attachment)
        if (err.length > 0) {
            await Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: err.join("<br>"),
            });
            return; // ⛔ STOP submit
        }

        // -----------------------------
        // PREPARE FORM DATA
        // -----------------------------
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData();

        formData.append('_token', token);
        formData.append('_method', 'POST');
        formData.append('action', 'update_auditee_action');

        if (isApprove) {
            formData.append('approve_ldr_spv', 1);
        }

        const findingId = document.querySelector('input[name="audit_finding_id"]')?.value;
        formData.append('audit_finding_id', findingId);
        formData.append('pic', document.querySelector('input[name="pic"]')?.value);

        // 5 WHY - ambil dari textarea dengan nama dinamis (dynamic count)
        for (let i = 1; i <= 5; i++) {
            const whyTextarea = document.querySelector(`textarea[name="why_${i}_mengapa"]`);
            const causeTextarea = document.querySelector(`textarea[name="cause_${i}_karena"]`);

            // Only append if textarea exists (for dynamic rows)
            if (whyTextarea || causeTextarea) {
                formData.append(`why_${i}_mengapa`, whyTextarea?.value || '');
                formData.append(`cause_${i}_karena`, causeTextarea?.value || '');
            }
        }

        formData.append('root_cause', document.querySelector('textarea[x-model="form.root_cause"]')?.value || '');

        // Corrective Action - get all existing rows dynamically
        let correctiveRowsSubmit = document.querySelectorAll('.corrective-row');
        correctiveRowsSubmit.forEach((row, index) => {
            const i = index + 1;
            const activity = document.querySelector(`textarea[name="corrective_${i}_activity"]`)?.value ||
                '';
            const pic = document.querySelector(`textarea[name="corrective_${i}_pic"]`)?.value || '';
            const planning = document.querySelector(`input[name="corrective_${i}_planning"]`)?.value || '';
            const actual = document.querySelector(`input[name="corrective_${i}_actual"]`)?.value || '';

            formData.append(`corrective_${i}_activity`, activity);
            formData.append(`corrective_${i}_pic`, pic);
            formData.append(`corrective_${i}_planning`, planning);
            formData.append(`corrective_${i}_actual`, actual);
        });

        // Preventive Action - get all existing rows dynamically
        let preventiveRowsSubmit = document.querySelectorAll('.preventive-row');
        preventiveRowsSubmit.forEach((row, index) => {
            const i = index + 1;
            const activity = document.querySelector(`textarea[name="preventive_${i}_activity"]`)?.value ||
                '';
            const pic = document.querySelector(`textarea[name="preventive_${i}_pic"]`)?.value || '';
            const planning = document.querySelector(`input[name="preventive_${i}_planning"]`)?.value || '';
            const actual = document.querySelector(`input[name="preventive_${i}_actual"]`)?.value || '';

            formData.append(`preventive_${i}_activity`, activity);
            formData.append(`preventive_${i}_pic`, pic);
            formData.append(`preventive_${i}_planning`, planning);
            formData.append(`preventive_${i}_actual`, actual);
        });

        // Yokoten
        const yokoten = document.querySelector('input[name="yokoten"]:checked');
        formData.append('yokoten', yokoten ? yokoten.value : 0);
        formData.append('yokoten_area', document.querySelector('textarea[name="yokoten_area"]')?.value || '');

        // Attachments
        Array.from(photoInput2?.files || []).forEach(file => formData.append('attachments[]', file));
        Array.from(fileInput2?.files || []).forEach(file => formData.append('attachments[]', file));

        // Log formData untuk debug
        console.log('📋 FormData entries:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(`  ${key}: File(${value.name}, ${value.size} bytes)`);
            } else {
                console.log(`  ${key}: ${value}`);
            }
        }

        // -----------------------------
        // SUBMIT
        // -----------------------------
        try {
            console.log('📤 Submitting auditee action...');
            console.log('Finding ID:', findingId);
            console.log('Files to upload:', fileDetails);

            const res = await fetch("{{ route('ftpp.auditee-action.store', ['id' => $finding->id]) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            console.log('📥 Response status:', res.status);
            console.log('📥 Response headers:', res.headers.get('content-type'));

            // ✅ Check if response is JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await res.text();
                console.error('❌ Non-JSON response received:', textResponse.substring(0, 500));

                await Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    html: 'Server returned an invalid response. Please check the console for details or contact administrator.<br><small>Response type: ' +
                        (contentType || 'unknown') + '</small>'
                });
                return;
            }

            const result = await res.json();
            console.log('📦 Response data:', result);
            console.log('📦 Full response:', JSON.stringify(result, null, 2));

            if (res.ok && result.success) {
                console.log('✅ Success:', result);
                window.location.href = "{{ route('ftpp.index') }}";
            } else {
                // ✅ Log errors lebih detail
                if (result.errors) {
                    console.error('❌ Validation errors:', result.errors);
                }

                // ✅ Jika ada error dari server tentang file size, tampilkan di field
                if (result.message && result.message.includes('file size')) {
                    showAttachmentError(result.message);
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        html: result.message || "Unknown error"
                    });
                }
            }
        } catch (err) {
            console.error('Network or parsing error:', err);
            await Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                html: 'Failed to submit data. Please check your connection and try again.<br><small>Error: ' +
                    err.message + '</small>'
            });
        }
    }
</script>
