@extends('layouts.app')
@section('title', 'Edit Auditee Action')
@section('subtitle',
    'Edit auditee action for finding #' .
    $finding->registration_number .
    '. Please update the details
    below for the auditee action.')

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
    .rte-editor ul {
        list-style-type: disc;
        padding-left: 24px;
        margin: 8px 0;
    }
    .rte-editor ol {
        list-style-type: decimal;
        padding-left: 24px;
        margin: 8px 0;
    }
    .rte-editor li {
        margin: 4px 0;
    }
    .rte-container {
        width: 100%;
    }
</style>

@section('breadcrumbs')
    <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-folder me-1"></i> FTPP
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-700 font-bold">Edit Auditee Action</li>
        </ol>
    </nav>
@endsection

@section('content')
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
        .rte-editor ul {
            list-style-type: disc;
            padding-left: 24px;
            margin: 8px 0;
        }
        .rte-editor ol {
            list-style-type: decimal;
            padding-left: 24px;
            margin: 8px 0;
        }
        .rte-editor li {
            margin: 4px 0;
        }
        .rte-container {
            width: 100%;
        }
    </style>

    <div x-data="editFtppApp()" x-init="init()" class="px-4">

        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2">
                    <h3 class="fw-bold">Edit Auditee Action</h3>
                    <p class="text-sm" style="font-size: 0.9rem;">
                        Edit auditee action for finding #{{ $finding->registration_number }}.
                        Please update the details below for the auditee action.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-8 shadow w-fit mb-1"
                aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>
                        <a href="{{ route('ftpp.index') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-folder me-1"></i> FTPP
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">Edit Auditee Action</li>
                </ol>
            </nav>
        </div> --}}
        @include('contents.ftpp2.auditee-action.partials.show-audit-finding', [
            'readonly' => true,
        ])

        <div class="space-y-6">
            <form action="{{ route('ftpp.auditee-action.update', $finding->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="audit_finding_id" x-model="selectedId">
                <input type="hidden" name="action" value="update_auditee_action">
                <input type="hidden" name="pic" value="{{ auth()->user()->name }}">
                <input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">

                <div class="gap-4 my-2">
                    <!-- LEFT: 5 WHY -->
                    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6">
                        <h5 class="font-bold">Auditee</h5>

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
                                                    @input="updateHiddenField($event.target)"></div>
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
                                                    @input="updateHiddenField($event.target)"></div>
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
                                        @input="updateHiddenField($event.target)"></div>
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
                                                @input="updateHiddenField($event.target)"></div>
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
                                                @input="updateHiddenField($event.target)"></div>
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
                                                @input="updateHiddenField($event.target)"></div>
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
                                                @input="updateHiddenField($event.target)"></div>
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
                        <div class="bg-white p-6 border border-gray-200 rounded-lg shadow space-y-6 mt-4">
                            <div class="font-semibold text-lg text-gray-900">Yokoten</div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="font-semibold text-gray-900">Yokoten? <span
                                            class="text-danger">*</span></label>
                                    <div class="flex gap-6">
                                        <label><input type="radio" name="yokoten" value="1"
                                                x-model="form.yokoten">
                                            Yes</label>
                                        <label><input type="radio" name="yokoten" value="0"
                                                x-model="form.yokoten">
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
                                        @input="updateHiddenField($event.target)"></div>
                                    <textarea name="yokoten_area" class="hidden" x-model="form.yokoten_area" 
                                        :required="form.yokoten == 1"></textarea>
                                </div>
                            </div>

                            @php
                                $action = $finding?->auditeeAction;
                                $actionId = $action?->id ?? 'null';
                            @endphp

                            <div class="font-semibold text-lg text-gray-800">Attachments</div>

                            {{-- Tips Alert --}}
                            <div class="p-3 rounded-lg border border-yellow-300 bg-yellow-50 flex items-start gap-2">
                                <i class="bi bi-exclamation-circle-fill text-yellow-600 text-lg flex-shrink-0 mt-0.5"></i>
                                <div>
                                    <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                                    <p class="text-xs text-yellow-700 leading-relaxed">
                                        Only <strong>PDF, PNG, JPG, and JPEG</strong> files are allowed.
                                        Maximum total file size is <strong>10 MB</strong>.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="items-center gap-4 mt-4">

                                    <!-- Attachment button -->
                                    <button id="attachBtn2" type="button"
                                        class="items-center gap-2 px-4 py-2 border rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none"
                                        title="Attach files">
                                        <i data-feather="paperclip" class="w-4 h-4"></i>
                                        <span id="attachCount2" class="text-xs text-gray-600 hidden">0</span>
                                    </button>

                                    <!-- Attachment Menu -->
                                    <div id="attachMenu2"
                                        class="hidden absolute mt-12 w-44 bg-white border rounded-xl shadow-lg z-20">
                                        <button id="attachImages2" type="button"
                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                            <i data-feather="image" class="w-4 h-4"></i> Upload Images
                                        </button>
                                        <button id="attachDocs2" type="button"
                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                            <i data-feather="file-text" class="w-4 h-4"></i> Upload Documents
                                        </button>
                                    </div>

                                    <!-- Hidden file inputs -->
                                    <input type="file" id="photoInput2" name="attachments[]" accept="image/*"
                                        multiple class="hidden">
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

                                    {{-- Laravel server-side errors --}}
                                    @error('attachments')
                                        <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded-r">
                                            <div class="flex items-start">
                                                <i data-feather="alert-circle"
                                                    class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5"></i>
                                                <p class="text-sm text-red-700">{!! $message !!}</p>
                                            </div>
                                        </div>
                                    @enderror

                                    {{-- Render existing attachments server-side so users can remove them --}}
                                    @php
                                        $existingFiles =
                                            $finding->auditeeAction?->file ??
                                            ($finding->auditeeAction?->attachments ?? []);
                                    @endphp

                                    <div id="existingFilesContainer" class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($existingFiles as $file)
                                            <div id="existing-file-{{ $file->id }}"
                                                class="relative border rounded p-1">
                                                @if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $file->file_path))
                                                    <img src="{{ asset('storage/' . $file->file_path) }}"
                                                        class="w-24 h-24 object-cover rounded" />
                                                @else
                                                    <div class="flex items-center gap-2 p-2 text-sm">
                                                        <i data-feather="file-text"></i>
                                                        <span>{{ $file->original_name ?? basename($file->file_path) }}</span>
                                                    </div>
                                                @endif

                                                <button type="button" onclick="markRemoveAttachment({{ $file->id }})"
                                                    class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">×</button>

                                                <input type="hidden" id="existing-attachment-input-{{ $file->id }}"
                                                    name="existing_attachments[]" value="{{ $file->id }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- Selected / new previews -->
                                    <div id="previewImageContainer2" class="flex flex-wrap gap-2 mt-2"></div>
                                    <div id="previewFileContainer2" class="flex flex-col gap-1 mt-2"></div>
                                </div>
                            </div>



                        </div>

                        <!-- Attachments -->
                        <div class="bg-white p-6 mt-6 border border-gray-200 rounded-lg shadow space-y-6">

                            <!-- Leader/SPV -->
                            <div class="p-4 bg-gray-50 border border-gray-300 rounded-md text-center max-w-xs">
                                <div>Created</div>

                                {{-- Tampilkan stamp jika ldr_spv_signature = 1 --}}
                                @if ($action && $action->ldr_spv_signature == 1)
                                    <img src="/images/usr-approve.png" class="mx-auto h-24">
                                @endif

                                <div class="mb-1 font-semibold text-gray-800">Leader / SPV</div>

                                <input type="text" value="{{ auth()->user()->name }}"
                                    class="w-full border border-gray-300 rounded text-center py-1" readonly>
                            </div>
                            <div class="mt-4">
                                <button type="submit"
                                    class="px-4 py-2 bg-gradient-to-r from-primaryLight to-primaryDark hover:from-primaryDark hover:to-primaryLight transition-colors text-white rounded">
                                    Save Changes
                                </button>
                            </div>
                        </div>

                    </div>

                </div>


            </form>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function editFtppApp(data) {
            return {
                selectedId: null,
                whyCount: 1,
                correctiveCount: 1,
                preventiveCount: 1,
                form: {
                    status_id: 7,
                    audit_type_id: "",
                    sub_audit_type_id: "",
                    auditor_id: "",
                    created_at: "",
                    due_date: "",
                    registration_number: "",
                    finding_description: "",
                    finding_category_id: "",
                    auditee_ids: "",
                    sub_klausul_id: [],

                    sub_audit: [],
                    auditees: [],
                    sub_klausul: [],
                },

                init() {

                    // Inject data dari Laravel → Alpine
                    this.form = @json($finding);

                    this.selectedId = this.form.id;

                    // convert tanggal
                    this.form.created_at = this.form.created_at?.substring(0, 10);
                    this.form.due_date = this.form.due_date?.substring(0, 10);

                    // tampilkan plant
                    this.form._plant_display = [this.form.department?.name, this.form.process?.name, this.form.product
                            ?.name
                        ]
                        .filter(Boolean)
                        .join(" / ");

                    // Auditee
                    this.form.auditee_ids = (this.form.auditee ?? []).map(a => a.id);

                    this.form._auditee_html = (this.form.auditee ?? [])
                        .map(a => `<span class='bg-blue-100 px-2 py-1 rounded'>${a.name}</span>`)
                        .join("");

                    // ✅ PINDAHKAN semua operasi DOM ke $nextTick
                    this.$nextTick(() => {
                        // Sub Audit
                        this.loadSubAudit();

                        // Sub Klausul
                        this.loadSubKlausul();

                        // ===== RENDER ATTACHMENTS (FINDING) =====
                        let previewImageContainer = document.getElementById('previewImageContainer');
                        let previewFileContainer = document.getElementById('previewFileContainer');

                        if (previewImageContainer && previewFileContainer) {
                            const files = this.form.file ?? [];

                            previewImageContainer.innerHTML = '';
                            previewFileContainer.innerHTML = '';

                            if (files && files.length) {
                                const baseUrl = '/storage/';

                                files.forEach(f => {
                                    const path = f.file_path ?? f.path ?? '';
                                    const fullUrl = baseUrl + path;
                                    const filename = f.original_name ?? path.split('/').pop() ?? '';

                                    if ((path + filename).match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                        // IMAGE THUMBNAIL
                                        const img = document.createElement('img');
                                        img.src = fullUrl;
                                        img.className =
                                            'w-24 h-24 object-cover border rounded cursor-pointer hover:opacity-80 transition';
                                        img.onclick = () => showImagePreviewModal(fullUrl, filename);
                                        previewImageContainer.appendChild(img);
                                    } else if (filename.match(/\.pdf$/i)) {
                                        // PDF CARD
                                        const pdfCard = document.createElement('div');
                                        pdfCard.className =
                                            'border rounded p-3 cursor-pointer hover:bg-gray-50 transition w-28 text-center';
                                        pdfCard.innerHTML = `
                                        <i data-feather="file-text" class="text-red-500 mx-auto" style="width: 40px; height: 40px;"></i>
                                        <span class="text-xs mt-2 block truncate" title="${filename}">${filename}</span>
                                    `;
                                        pdfCard.onclick = () => showFilePreviewModal(fullUrl, filename);
                                        previewFileContainer.appendChild(pdfCard);
                                    } else {
                                        // OTHER FILES
                                        const fileCard = document.createElement('div');
                                        fileCard.className =
                                            'border rounded p-3 cursor-pointer hover:bg-gray-50 transition w-28 text-center';
                                        fileCard.innerHTML = `
                                        <i data-feather="file" class="text-gray-500 mx-auto" style="width: 40px; height: 40px;"></i>
                                        <span class="text-xs mt-2 block truncate" title="${filename}">${filename}</span>
                                    `;
                                        fileCard.onclick = () => showFilePreviewModal(fullUrl, filename);
                                        previewFileContainer.appendChild(fileCard);
                                    }
                                });

                                if (typeof feather !== 'undefined') feather.replace();
                            } else {
                                previewImageContainer.innerHTML =
                                    '<span class="text-gray-400 text-sm">No attachments</span>';
                            }
                        }
                    });

                    // auditee action data
                    const action = this.form.auditeeAction ?? this.form.auditee_action ?? null;

                    console.log('🔍 DEBUG - Finding data:', this.form);
                    console.log('🔍 DEBUG - Auditee Action:', action);

                    if (action) {
                        console.log('✅ Action found! Loading data...');
                        // Root Cause
                        this.form.root_cause = action.root_cause ?? '';

                        // Yokoten
                        this.form.yokoten = action.yokoten ?? '';
                        this.form.yokoten_area = action.yokoten_area ?? '';

                        // ========================
                        // 5 WHY (ambil dari tabel tt_why_causes)
                        // ========================
                        if (action.why_causes?.length) {
                            console.log(`✅ Loading ${action.why_causes.length} Why rows`);
                            this.whyCount = Math.max(action.why_causes.length, 1);
                            action.why_causes.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`why_${i}_mengapa`] = row.why_description || '';
                                this.form[`cause_${i}_karena`] = row.cause_description || '';
                                console.log(`  Why-${i}: "${row.why_description?.substring(0, 30)}"`);
                            });
                        } else {
                            console.log('⚠️ No why_causes data found');
                        }

                        // ========================
                        // CORRECTIVE ACTION
                        // ========================
                        if (action.corrective_actions && Array.isArray(action.corrective_actions)) {
                            console.log(`✅ Loading ${action.corrective_actions.length} Corrective rows`);
                            this.correctiveCount = Math.max(action.corrective_actions.length, 1);
                            action.corrective_actions.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`corrective_${i}_activity`] = row.activity ?? '';
                                this.form[`corrective_${i}_pic`] = row.pic ?? '';
                                this.form[`corrective_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                                this.form[`corrective_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                                console.log(`  Corrective-${i}: "${row.activity?.substring(0, 30)}"`);
                            });
                        } else {
                            console.log('⚠️ No corrective_actions data found');
                        }

                        // ========================
                        // PREVENTIVE ACTION
                        // ========================
                        if (action.preventive_actions && Array.isArray(action.preventive_actions)) {
                            console.log(`✅ Loading ${action.preventive_actions.length} Preventive rows`);
                            this.preventiveCount = Math.max(action.preventive_actions.length, 1);
                            action.preventive_actions.forEach((row, idx) => {
                                const i = idx + 1;
                                this.form[`preventive_${i}_activity`] = row.activity ?? '';
                                this.form[`preventive_${i}_pic`] = row.pic ?? '';
                                this.form[`preventive_${i}_planning`] = row.planning_date?.substring(0, 10) ?? '';
                                this.form[`preventive_${i}_actual`] = row.actual_date?.substring(0, 10) ?? '';
                                console.log(`  Preventive-${i}: "${row.activity?.substring(0, 30)}"`);
                            });
                        } else {
                            console.log('⚠️ No preventive_actions data found');
                        }

                        this.$nextTick(() => {
                            const previewImageContainer2 = document.getElementById('previewImageContainer2');
                            const previewFileContainer2 = document.getElementById('previewFileContainer2');

                            if (!previewImageContainer2 || !previewFileContainer2) {
                                return;
                            }

                            const aa = action ?? this.form.auditeeAction ?? this.form.auditee_action ?? null;
                            const files = aa?.file ?? aa?.attachments ?? [];

                            if (files && files.length) {
                                previewImageContainer2.innerHTML = '';
                                previewFileContainer2.innerHTML = '';

                                const baseUrl = '/storage/';

                                files.forEach(f => {
                                    const fullUrl = baseUrl + (f.file_path ?? f.path ?? '');
                                    const filename = f.original_name ?? (f.file_path ?? f.path ?? '').split(
                                        '/').pop() ?? '';

                                    if ((f.file_path ?? filename).match(
                                            /\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
                                        previewImageContainer2.innerHTML += `
                                        <img src="${fullUrl}" class="w-24 h-24 object-cover border rounded cursor-pointer hover:opacity-80" onclick="window.open('${fullUrl}', '_blank')" />
                                    `;
                                    } else {
                                        previewFileContainer2.innerHTML += `
                                        <a href="${fullUrl}" target="_blank" class="flex gap-2 text-sm border p-2 rounded items-center text-blue-600 hover:underline">
                                            <i data-feather="file-text"></i> ${filename}
                                        </a>
                                    `;
                                    }
                                });

                                if (typeof feather !== 'undefined' && feather.replace) {
                                    feather.replace();
                                }
                            }

                            // ✅ Sync contenteditable divs dengan form data
                            this.syncEditorsWithForm();
                        });
                    }
                },

                loadSubAudit() {
                    let list = @json($subAudit);
                    const subContainer = document.getElementById('subAuditType');

                    if (!subContainer) return; // ✅ Guard jika element tidak ada

                    subContainer.innerHTML = "";

                    if (!list.length) {
                        subContainer.innerHTML = `<small class="text-gray-500">No Sub Audit Type</small>`;
                        return;
                    }

                    list.forEach(s => {
                        subContainer.insertAdjacentHTML('beforeend', `
                    <label>
                        <input type="radio" name="sub_audit_type_id"
                            value="${s.id}"
                            ${s.id === this.form.sub_audit_type_id ? 'checked' : ''}>
                        ${s.name}
                    </label>
                `);
                    });
                },

                loadSubKlausul() {
                    const list = this.form.sub_klausuls ?? [];
                    const container = document.getElementById('selectedSubContainer');

                    if (!container) {
                        console.warn('⚠️ Container selectedSubContainer tidak ditemukan');
                        return;
                    }

                    console.log('🔍 Sub Klausuls data:', list); // ✅ Debug

                    container.innerHTML = "";

                    if (!list.length) {
                        container.innerHTML = `<span class="text-gray-400 text-sm">No clauses</span>`;
                        return;
                    }

                    list.forEach(s => {
                        const code = s.code ?? '';
                        const name = s.name ?? '';

                        container.insertAdjacentHTML('beforeend', `
                        <span class="bg-green-100 px-2 py-1 rounded text-xs mr-1 mb-1 inline-block">
                            ${code}${code && name ? ' - ' : ''}${name}
                        </span>
                    `);
                    });
                },

                syncEditorsWithForm() {
                    console.log('🔄 syncEditorsWithForm() called, this.form:', this.form);
                    const self = this;
                    this.$nextTick(() => {
                        // Delay sedikit untuk memastikan Alpine rendering selesai
                        setTimeout(() => {
                            console.log('🔄 syncEditorsWithForm() executing after $nextTick');
                            let updatedCount = 0;
                            const editors = document.querySelectorAll('.rte-editor[data-field]');
                            console.log(`📊 Found ${editors.length} editors`);
                            
                            editors.forEach(editor => {
                                const fieldName = editor.getAttribute('data-field');
                                const fieldValue = self.form[fieldName];
                                console.log(`  Checking ${fieldName}: value = "${fieldValue ? fieldValue.substring(0, 50) : 'EMPTY'}"`);
                                if (fieldValue) {
                                    editor.innerHTML = fieldValue;
                                    updatedCount++;
                                    console.log(`  ✅ Updated ${fieldName}`);
                                }
                            });
                            console.log(`📊 Total editors updated: ${updatedCount}`);
                        }, 100);
                    });
                }
            }
        }
    </script>
@endpush

@push('scripts')
    <script>
        // Attachment menu + preview logic (copied from create partial)
        (function() {
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

            if (!photoInput2 || !fileInput2) return;

            function updatefileInput2(input, filesArray2) {
                const dt = new DataTransfer();
                filesArray2.forEach(file2 => dt.items.add(file2));
                input.files = dt.files;
            }

            function updateAttachCount2() {
                const total = (photoInput2.files?.length || 0) + (fileInput2.files?.length || 0);
                if (attachCount2) {
                    if (total > 0) {
                        attachCount2.textContent = total;
                        attachCount2.classList.remove('hidden');
                    } else {
                        attachCount2.classList.add('hidden');
                    }
                }
            }

            function displayImages2() {
                if (!previewImageContainer2) return;
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
                    if (typeof feather !== 'undefined' && feather.replace) feather.replace();
                });
            }

            function displayFiles2() {
                if (!previewFileContainer2) return;
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
                    if (typeof feather !== 'undefined' && feather.replace) {
                        feather.replace();
                    }
                });
            }

            photoInput2.addEventListener('change', (e) => {
                // Add new files to accumulated list
                const newFiles = Array.from(photoInput2.files);
                newFiles.forEach(file => {
                    // Check if file already exists to avoid duplicates
                    const isDuplicate = accumulatedPhotoFiles.some(f => f.name === file.name && f.size === file.size);
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
                    const isDuplicate = accumulatedFileFiles.some(f => f.name === file.name && f.size === file.size);
                    if (!isDuplicate) {
                        accumulatedFileFiles.push(file);
                    }
                });

                // Update input with accumulated files
                updatefileInput2(fileInput2, accumulatedFileFiles);
                displayFiles2();
                updateAttachCount2();
            });

            attachBtn2?.addEventListener('click', (e) => {
                e.stopPropagation();
                attachMenu2.classList.toggle('hidden');
            });

            document.addEventListener('click', () => attachMenu2.classList.add('hidden'));

            attachImages2?.addEventListener('click', () => photoInput2.click());
            attachDocs2?.addEventListener('click', () => fileInput2.click());

            // markRemoveAttachment for existing files — confirm then delete via AJAX
            window.markRemoveAttachment = function(id) {
                if (!confirm(
                        'Are you sure you want to delete this attachment? This will remove the file from storage and the database.'
                    )) return;

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(`/ftpp/auditee-action/attachment/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf || '',
                        'Accept': 'application/json',
                    }
                }).then(res => res.json()).then(json => {
                    if (json && json.success) {
                        const el = document.getElementById('existing-file-' + id);
                        if (el) el.remove();
                        const existingInput = document.getElementById('existing-attachment-input-' + id);
                        if (existingInput) existingInput.remove();
                        // optional: show a brief message
                        try {
                            alert('Attachment deleted');
                        } catch (e) {}
                    } else {
                        console.error('Failed to delete attachment', json);
                        alert('Failed to delete attachment');
                    }
                }).catch(err => {
                    console.error('Error deleting attachment', err);
                    alert('Error deleting attachment');
                });
            };
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // ✅ RICH TEXT EDITOR FUNCTIONS
        window.formatText = function(button, command) {
            event.preventDefault();
            const toolbar = button.parentElement;
            const editor = toolbar.nextElementSibling;
            editor.focus();
            document.execCommand(command, false, null);
            editor.dispatchEvent(new Event('input', { bubbles: true }));
            updateToolbarState(editor);
        }

        window.updateHiddenField = function(editor) {
            const fieldName = editor.getAttribute('data-field');
            const value = editor.innerHTML;
            const textarea = editor.parentElement.querySelector('textarea[name="' + fieldName + '"]');
            if (textarea) {
                textarea.value = value;
            }
        }

        window.updateToolbarState = function(editor) {
            const toolbar = editor.previousElementSibling;
            if (!toolbar || !toolbar.classList.contains('rte-toolbar')) return;
            
            const buttons = toolbar.querySelectorAll('button');
            const commands = ['bold', 'italic', 'underline'];
            
            buttons.forEach((btn, idx) => {
                if (idx < 3) {
                    const isActive = document.queryCommandState(commands[idx]);
                    btn.classList.toggle('active', isActive);
                }
            });
        }

        // ✅ VALIDASI CLIENT-SIDE sebelum submit form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector(
                'form[action="{{ route('ftpp.auditee-action.update', $finding->id) }}"]');

            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Stop default submit dulu

                // ✅ 0. SYNC contenteditable divs ke hidden textareas
                document.querySelectorAll('[data-field]').forEach(editor => {
                    if (editor.classList.contains('rte-editor')) {
                        const fieldName = editor.getAttribute('data-field');
                        const value = editor.innerHTML;
                        const textarea = editor.parentElement.querySelector('textarea[name="' + fieldName + '"]');
                        if (textarea) {
                            textarea.value = value;
                        }
                    }
                });

                // ✅ 0.5 VALIDASI CORRECTIVE & PREVENTIVE ACTION - jika Activity diisi, maka PIC, Planning, Actual harus diisi
                let validationErrors = [];

                // Validasi Corrective Action
                const correctiveRows = document.querySelectorAll('.corrective-row');
                correctiveRows.forEach((row, index) => {
                    const i = index + 1;
                    const activity = document.querySelector(`textarea[name="corrective_${i}_activity"]`)?.value?.trim() || '';
                    const pic = document.querySelector(`textarea[name="corrective_${i}_pic"]`)?.value?.trim() || '';
                    const planning = document.querySelector(`input[name="corrective_${i}_planning"]`)?.value?.trim() || '';
                    const actual = document.querySelector(`input[name="corrective_${i}_actual"]`)?.value?.trim() || '';

                    if (activity) {
                        if (!pic) validationErrors.push(`❌ Corrective Action Row ${i}: If Activity is filled, PIC must also be filled.`);
                        if (!planning) validationErrors.push(`❌ Corrective Action Row ${i}: If Activity is filled, Planning date must also be filled.`);
                        if (!actual) validationErrors.push(`❌ Corrective Action Row ${i}: If Activity is filled, Actual field must also be filled.`);
                    }
                });

                // Validasi Preventive Action
                const preventiveRows = document.querySelectorAll('.preventive-row');
                preventiveRows.forEach((row, index) => {
                    const i = index + 1;
                    const activity = document.querySelector(`textarea[name="preventive_${i}_activity"]`)?.value?.trim() || '';
                    const pic = document.querySelector(`textarea[name="preventive_${i}_pic"]`)?.value?.trim() || '';
                    const planning = document.querySelector(`input[name="preventive_${i}_planning"]`)?.value?.trim() || '';
                    const actual = document.querySelector(`input[name="preventive_${i}_actual"]`)?.value?.trim() || '';

                    if (activity) {
                        if (!pic) validationErrors.push(`❌ Preventive Action Row ${i}: If Activity is filled, PIC must also be filled.`);
                        if (!planning) validationErrors.push(`❌ Preventive Action Row ${i}: If Activity is filled, Planning date must also be filled.`);
                        if (!actual) validationErrors.push(`❌ Preventive Action Row ${i}: If Activity is filled, Actual field must also be filled.`);
                    }
                });

                // Jika ada validation errors, tampilkan SweetAlert dan stop submit
                if (validationErrors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: validationErrors.join('<br>')
                    });
                    return; // ⛔ STOP submit
                }

                // ✅ 1. Hapus error lama
                const errorContainer = document.getElementById('attachmentErrorContainer');
                if (errorContainer) {
                    errorContainer.classList.add('hidden');
                }

                // ✅ 2. VALIDASI TOTAL FILE SIZE
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

                // ✅ 3. CHECK jika melebihi 20MB
                if (totalSize > 20 * 1024 * 1024) { // 20MB in bytes
                    showAttachmentError(`

                        <p class="font-semibold mb-1">❌ Total file size exceeds 20MB</p>
                        <p>Current total size: <strong>${totalSizeMB} MB</strong></p>
                        <p>
                            Please compress your PDF files and reupload it.
                        </p>
                    `);
                    return; // ⛔ STOP submit
                }

                // ✅ 4. CHECK individual file size
                let individualErrors = [];

                // Check individual image files (max 3MB)
                if (photoInput2 && photoInput2.files) {
                    Array.from(photoInput2.files).forEach(file => {
                        if (file.size > 3 * 1024 * 1024) { // 3MB
                            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                            individualErrors.push(
                                `🖼️ Image "${file.name}" is ${sizeMB}MB. Maximum is 3MB per image.`
                            );
                        }
                    });
                }

                // Check individual PDF files (max 10MB)
                if (fileInput2 && fileInput2.files) {
                    Array.from(fileInput2.files).forEach(file => {
                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                            individualErrors.push(
                                `📄 PDF "${file.name}" is ${sizeMB}MB. Maximum is 10MB per PDF.`
                            );
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

                // ✅ 5. Jika lolos semua validasi, lanjut submit
                form.submit();
            });

            // ✅ Helper function untuk show error menggunakan container yang sudah ada
            function showAttachmentError(message) {
                const errorContainer = document.getElementById('attachmentErrorContainer');
                const errorMessage = document.getElementById('attachmentErrorMessage');

                if (!errorContainer || !errorMessage) {
                    console.error('❌ Error container not found in DOM');
                    // Fallback: show alert
                    alert(message.replace(/<[^>]*>/g, ''));
                    return;
                }

                // Tampilkan error
                errorMessage.innerHTML = message;
                errorContainer.classList.remove('hidden');

                // Re-render feather icons
                if (typeof feather !== 'undefined' && feather.replace) {
                    feather.replace();
                }

                // Scroll ke error
                errorContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                console.log('✅ Error displayed in container');
            }
        });
    </script>
@endpush
