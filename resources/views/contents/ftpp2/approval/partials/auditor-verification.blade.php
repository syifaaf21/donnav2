@php
    $finding = $findings->first();
    $action = $finding?->auditeeAction;
@endphp

{{-- TANDA TANGAN --}}
<input type="hidden" id="auditee_action_id" name="auditee_action_id" x-model="form.auditee_action_id">
<table class="w-full border border-black text-sm mt-2 text-center">
    <tr>
        <td class="border border-black p-1 font-semibold w-1/2">Effectiveness Verification</td>
        <td class="border border-black p-1 font-semibold w-1/6">Status</td>
        <td class="border border-black p-1 font-semibold w-1/6">Acknowledge</td>
        <td class="border border-black p-1 font-semibold w-1/6">Approve</td>
    </tr>
    <tr>
        <td class="border border-black">
            <textarea name="effectiveness_verification" x-model="form.effectiveness_verification"
                class="w-full h-40 border border-gray-400 rounded p-1"></textarea>
        </td>
        <td class="border border-black p-2">
            <div class="flex flex-col text-md font-bold">
                <span>Status:</span>
                <span
                    :class="{
                        'text-red-500': form.status_id == 7,
                        'text-green-600': form.status_id == 11,
                        'text-yellow-500': form.status_id != 7 && form.status_id != 11
                    }"
                    x-text="form.status_id == 7 ? 'NEED ASSIGN' : (form.status_id == 11 ? 'CLOSE' : (form.status_id == 8 ? 'NEED CHECK' : (form.status_id == 9 ? 'NEED APPROVAL BY AUDITOR' : (form.status_id == 10 ? 'NEED APPROVAL BY LEAD AUDITOR' : ''))))">
                </span>
            </div>
        </td>
        <td class="border border-black p-2">
            <div class="my-4">
                <template
                    x-if="!form.lead_auditor_ack && (userRoles.includes('admin') || userRoles.includes('super admin') || userRoles.includes('lead auditor')) && form.status_id == 10">
                    <div class="flex flex-col gap-3">

                        <!-- VERIFY BUTTON -->
                        <button type="button" onclick="verifyLeadAuditor()"
                            class="flex items-center justify-center gap-2 px-4 py-2
                           bg-blue-600 hover:bg-blue-700 text-white rounded-lg
                           transition-all shadow-sm hover:shadow">

                            <i data-feather="check-circle" class="w-4 h-4"></i>
                            <span class="text-sm">Verify</span>
                        </button>

                        <!-- RETURN BUTTON -->
                        <button type="button" onclick="returnForRevision()"
                            class="flex items-center justify-center gap-2 px-4 py-2
                           bg-red-600 hover:bg-red-700 text-white rounded-lg
                           transition-all shadow-sm hover:shadow">

                            <i data-feather="x-circle" class="w-4 h-4"></i>
                            <span class="text-sm">Return</span>
                        </button>
                    </div>
                </template>

                <template x-if="form.lead_auditor_signature">
                    <img :src="form.lead_auditor_signature_url" class="mx-auto mt-1 h-24 object-contain">
                </template>
            </div>

            <!-- NAME FIELD -->
            <div>
                <span class="text-sm font-semibold my-1">Lead Auditor</span>
                @php
                    $userRoleNames = auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
                    $userIsAdmin = in_array('admin', $userRoleNames) || in_array('super admin', $userRoleNames);
                    $userIsLead = in_array('lead auditor', $userRoleNames);
                @endphp

                <!-- Select visible only to Admin / Super Admin -->
                @if ($userIsAdmin)
                    <div class="mb-2">
                        <label class="block text-xs font-semibold mb-1">Select Lead Auditor</label>
                        <select id="lead_auditor_select" x-model="form.selected_lead_auditor_id"
                            class="w-full border border-gray-400 rounded p-2 text-xs">
                            <option value="">-- Choose Lead Auditor --</option>
                            @foreach ($leadAuditors as $auditor)
                                <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center text-gray-700" x-text="form.auditeeAction?.leadAuditor?.name || '-'">-</div>
                @else
                    @if ($userIsLead)
                        <!-- If current user is Lead Auditor, auto-fill selection via hidden input -->
                        <input type="hidden" id="lead_auditor_select" x-model="form.selected_lead_auditor_id"
                            value="{{ auth()->id() }}">
                        <div class="text-center text-gray-700">{{ auth()->user()->name }}</div>
                    @else
                        <!-- Non-admins see the chosen lead auditor name only -->
                        <div class="text-center text-gray-700" x-text="form.auditeeAction?.leadAuditor?.name || '-'">-</div>
                    @endif
                @endif
            </div>
        </td>

        <td class="border border-black p-2">
            <div class="my-4">
                <template
                    x-if="!form.auditor_verified && (userRoles.includes('auditor') || userRoles.includes('admin')) && form.status_id == 9">
                    <div class="flex flex-col gap-3">

                        <!-- VERIFY BUTTON -->
                        <button type="button" onclick="verifyAuditor()"
                            class="flex items-center justify-center gap-2 px-4 py-2
                           bg-blue-600 hover:bg-blue-700 text-white rounded-lg
                           transition-all shadow-sm hover:shadow">

                            <i data-feather="check-circle" class="w-4 h-4"></i>
                            Verify
                        </button>

                        <!-- RETURN BUTTON -->
                        <button type="button" onclick="returnForRevision()"
                            class="flex items-center justify-center gap-2 px-4 py-2
                           bg-red-600 hover:bg-red-700 text-white rounded-lg
                           transition-all shadow-sm hover:shadow">

                            <i data-feather="x-circle" class="w-4 h-4"></i>
                            Return
                        </button>
                    </div>
                </template>

                <template x-if="form.auditor_signature">
                    <img :src="form.auditor_signature_url" class="mx-auto mt-1 h-24 object-contain">
                </template>
            </div>

            <!-- NAME FIELD -->
            <div>
                <span class="font-semibold my-1">Auditor</span>
                <div class="text-center text-gray-700" x-text="form.auditeeAction?.auditor?.name || '-'">-</div>
            </div>
        </td>

    </tr>
</table>

<script>
    // Ensure SweetAlert2 is loaded; if not, load it dynamically
    async function ensureSwal() {
        if (typeof Swal !== 'undefined') return Promise.resolve();
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 is not loaded. Please ensure it is included in app.blade.php.');
                return reject(new Error('SweetAlert2 not found'));
            }
            s.onload = () => resolve();
            s.onerror = () => reject(new Error('Failed to load SweetAlert2'));
            document.head.appendChild(s);
        });
    }

    async function verifyAuditor() {
        await ensureSwal();
        const auditeeActionId = document.getElementById('auditee_action_id')?.value;
        const alpineEl = document.querySelector('[x-data="ftppApp()"]');
        const alpineComponent = Alpine.$data(alpineEl);

        // Pastikan effectiveness_verification diisi dulu
        const verification = alpineComponent.form.effectiveness_verification?.trim();
        if (!verification) {
            await Swal.fire({
                icon: 'warning',
                title: 'Missing',
                text: 'Please fill in Effectiveness Verification before verifying.'
            });
            return;
        }

        if (!auditeeActionId) {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No auditee action ID found'
            });
            return;
        }

        const confirm = await Swal.fire({
            title: 'Confirm Verify',
            text: 'Are you sure you want to verify this as Auditor?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, verify',
            cancelButtonText: 'Cancel'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/auditor-verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: auditeeActionId,
                    effectiveness_verification: verification
                })
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error('Failed to verify Auditor');
            }

            const data = await res.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Verified',
                    text: 'Status updated to Approved by Auditor',
                    timer: 1200,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data?.message || 'Failed to verify Auditor'
                });
            }

        } catch (error) {
            console.error(error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error verifying Auditor. Check console for details.'
            });
        }
    }

    async function verifyLeadAuditor() {
        await ensureSwal();
        const auditeeActionId = document.getElementById('auditee_action_id')?.value;
        const leadAuditorId = document.getElementById('lead_auditor_select')?.value;

        if (!auditeeActionId) {
            return Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No auditee action ID found'
            });
        }

        if (!leadAuditorId) {
            return Swal.fire({
                icon: 'warning',
                title: 'Missing',
                text: 'Please select a Lead Auditor before verifying.'
            });
        }

        const confirm = await Swal.fire({
            title: 'Confirm Acknowledge',
            text: 'Are you sure you want to acknowledge as Lead Auditor? This will close the finding.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, acknowledge',
            cancelButtonText: 'Cancel'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/lead-auditor-acknowledge`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: auditeeActionId,
                    lead_auditor_id: leadAuditorId
                })
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error('Failed to acknowledge Lead Auditor');
            }

            const data = await res.json();
            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Acknowledged',
                    text: 'Status updated to closed',
                    timer: 1200,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data?.message || 'Failed to acknowledge'
                });
            }

        } catch (error) {
            console.error(error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error acknowledging Lead Auditor. Check console for details.'
            });
        }
    }

    async function returnForRevision() {
        await ensureSwal();
        const auditeeActionId = document.getElementById('auditee_action_id')?.value;
        const alpineEl = document.querySelector('[x-data="ftppApp()"]');
        const alpineComponent = Alpine.$data(alpineEl);

        // Pastikan effectiveness_verification diisi dulu
        const verification = alpineComponent.form.effectiveness_verification?.trim();
        if (!verification) {
            await Swal.fire({
                icon: 'warning',
                title: 'Missing',
                text: 'Please fill in Effectiveness Verification before verifying.'
            });
            return;
        }

        if (!auditeeActionId) {
            return Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No auditee action ID found'
            });
        }

        const confirm = await Swal.fire({
            title: 'Return for Revision',
            text: 'Are you sure you want to return this FTPP for revision?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, return',
            cancelButtonText: 'Cancel'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/auditor-return`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auditee_action_id: auditeeActionId,
                    status_id: 12,
                    effectiveness_verification: verification
                })
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error('Failed to return FTPP for revision');
            }

            const data = await res.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Returned',
                    text: 'FTPP returned to user for revision',
                    timer: 1200,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data?.message || 'Failed to return FTPP'
                });
            }

        } catch (error) {
            console.error(error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error returning FTPP. Check console for details.'
            });
        }
    }
</script>
